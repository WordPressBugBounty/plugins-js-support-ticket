<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The check that decides whether an ensureSchema() has work to do.
 *
 * Every self-healing table in the plugin used to answer that question with a
 * stored version option alone:
 *
 *     if (get_option('jsst_<x>_schema') === self::SCHEMA_VERSION) return;
 *
 * The option records what a build *wrote*, which is not the same as what the
 * table *has*. A table dropped and recreated in an older layout — by a legacy
 * add-on's activation, an import, a restore from another site's dump, or an
 * add-on uninstall that took its tables with it — leaves the option still
 * claiming the current schema, and the repair below it never runs again. That
 * is not hypothetical: it is how `js_ticket_activity_log` ended up serving
 * `Unknown column 'al.source'` on every ticket timeline, unfixable by any
 * number of reloads, and how `js_ticket_help_topics` lost `parentid`.
 *
 * So the option is treated as a hint and the table is asked directly. The cost
 * is one `SHOW COLUMNS` per guarded table, once per request — repeat calls
 * inside the same request are answered from the memo, which is what makes this
 * safe to call from a hot path like JSSTmigration::inProgress().
 *
 * Only ever list columns the caller's repair path can actually add. Naming a
 * column that no `ALTER` restores would make needsRun() return true on every
 * request, turning a silent breakage into a permanent one.
 */
class JSSTschemaguard {

    /** option name => already answered in this request. */
    private static $jsst_checked = array();

    /**
     * @param string $jsst_option  Option holding the recorded schema version.
     * @param string $jsst_version The version this build writes.
     * @param array  $jsst_expect  Unprefixed table name => columns the repair
     *                             path can add. An empty array asks only that
     *                             the table exists.
     * @return bool True when the repair path must run.
     */
    public static function needsRun($jsst_option, $jsst_version, $jsst_expect = array()) {
        if (isset(self::$jsst_checked[$jsst_option])) {
            return false;
        }
        self::$jsst_checked[$jsst_option] = true;

        if (get_option($jsst_option) !== $jsst_version) {
            return true;
        }
        foreach ($jsst_expect as $jsst_table => $jsst_columns) {
            $jsst_have = self::columns($jsst_table);
            if (empty($jsst_have)) {
                return true;    // the table is gone
            }
            if (array_diff($jsst_columns, $jsst_have)) {
                return true;    // it drifted back to an older layout
            }
        }
        return false;
    }

    /**
     * The columns of one plugin table, or an empty array when it does not
     * exist. Errors are suppressed because "not there yet" is the normal answer
     * on a fresh install, and it is the caller's CREATE that fixes it. wpdb
     * clears last_error at the start of the next query, so a caller's own error
     * check is unaffected.
     */
    public static function columns($jsst_table) {
        $jsst_suppress = jssupportticket::$_db->suppress_errors(true);
        $jsst_columns = jssupportticket::$_db->get_col(
            'SHOW COLUMNS FROM `' . jssupportticket::$_db->prefix . $jsst_table . '`', 0);
        jssupportticket::$_db->suppress_errors($jsst_suppress);
        return is_array($jsst_columns) ? $jsst_columns : array();
    }

    /**
     * Add every column a table is missing, in one pass, without writing a
     * database error to the log.
     *
     * Every self-healing table in the plugin used to do this inline and all of
     * them did it the same racy way: one `SHOW COLUMNS ... LIKE` per column,
     * then an `ALTER` when the probe came back empty. Two requests arriving
     * together - an admin page and its own heartbeat, which is the ordinary
     * shape of the minute somebody activates nine plugins - both probe before
     * either alters, and the loser's `ALTER` fails with `Duplicate column
     * name`. The schema is correct either way. What is left behind is a
     * screenful of `WordPress database error` in the log of a site where
     * nothing is wrong, which is worse than merely untidy: it is the first
     * thing somebody debugging an unrelated fault will find, and it sends them
     * a day in the wrong direction.
     *
     * So the probe is one `SHOW COLUMNS` for the whole table instead of one per
     * column, and errors are suppressed across the `ALTER`s. The suppression is
     * the part that matters and it is not cosmetic: `ADD COLUMN` here is
     * idempotent in intent, so "it is already there" is this method's success
     * condition, and a success has no business in an error log. wpdb clears
     * last_error at the start of the next query, so a caller checking its own
     * errors afterwards is unaffected.
     *
     * Serialising the callers is `lock()` below. This method stays correct
     * without it - that is the point of doing the work idempotently - but a
     * caller that can take the lock should, because two concurrent `ALTER`s
     * against one table still queue on a metadata lock even when the second is
     * about to be told there was nothing to do.
     *
     * @param string $jsst_table  Unprefixed table name.
     * @param array  $jsst_wanted Column name => the DDL that follows `ADD <name>`.
     * @return array The columns this call actually added, in order.
     */
    public static function addColumns($jsst_table, $jsst_wanted) {
        /* No columns at all means no table. The caller's CREATE owns that case,
           and an ALTER against a table that is not there would log exactly the
           kind of error this method exists to keep out of the log. */
        $jsst_have = self::columns($jsst_table);
        if (empty($jsst_have)) {
            return array();
        }

        $jsst_full  = jssupportticket::$_db->prefix . $jsst_table;
        $jsst_added = array();

        $jsst_suppress = jssupportticket::$_db->suppress_errors(true);
        foreach ($jsst_wanted as $jsst_column => $jsst_ddl) {
            if (in_array($jsst_column, $jsst_have, true)) {
                continue;
            }
            if (jssupportticket::$_db->query('ALTER TABLE `' . $jsst_full . '` ADD `'
                    . $jsst_column . '` ' . $jsst_ddl) !== false) {
                $jsst_added[] = $jsst_column;
            }
        }
        jssupportticket::$_db->suppress_errors($jsst_suppress);

        return $jsst_added;
    }

    /**
     * Take a named lock for the length of one migration, or report that another
     * request already holds it.
     *
     * `addColumns()` makes a lost race quiet; this is what stops the race being
     * run twice in the first place. Concurrent `ALTER`s against one table are
     * not free even when every one of them is idempotent - each takes a metadata
     * lock, and on a desk whose articles or tickets table is big enough to
     * matter the second request spends its whole execution time waiting to be
     * told the work was already done.
     *
     * `GET_LOCK` rather than a transient or an option, because the lock has to
     * be released by a request that dies half way through a migration and a row
     * in wp_options is not. A MySQL lock is scoped to the connection and goes
     * when the connection does, so the worst a fatal can strand is a lock that
     * outlives the request that took it by nothing at all.
     *
     * **It fails open, and that direction is deliberate.** Only a definite `0` -
     * the server saying somebody else holds this - is treated as "do not
     * proceed". An error, a NULL, or a MySQL-compatible backend that has never
     * heard of `GET_LOCK` all answer "carry on", because the alternative is a
     * schema that never migrates on such a site. Failing open costs the racy
     * behaviour this class already tolerates; failing closed would cost the
     * repair itself.
     *
     * A caller that does not get the lock must do nothing at all - not the
     * `ALTER`s, and not the `update_option` that records the version - so that
     * the next request tries again rather than recording work it never did.
     */
    public static function lock($jsst_key) {
        $jsst_suppress = jssupportticket::$_db->suppress_errors(true);
        $jsst_got = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT GET_LOCK(%s, 0)', self::lockName($jsst_key)));
        jssupportticket::$_db->suppress_errors($jsst_suppress);

        return ((string) $jsst_got !== '0');
    }

    /** Give back what lock() took. Harmless on a lock this connection never held. */
    public static function unlock($jsst_key) {
        $jsst_suppress = jssupportticket::$_db->suppress_errors(true);
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            'SELECT RELEASE_LOCK(%s)', self::lockName($jsst_key)));
        jssupportticket::$_db->suppress_errors($jsst_suppress);
    }

    /**
     * A lock name that means this install and no other.
     *
     * `GET_LOCK` names are server-wide rather than per-database and MySQL caps
     * them at 64 characters, so the name is a hash of the database and the table
     * prefix alongside the key. Two WordPress installs sharing a MySQL server
     * must not block each other's upgrades - which is the whole of a staging
     * site next to its live one - and two installs in one database with
     * different prefixes are two installs.
     */
    private static function lockName($jsst_key) {
        $jsst_db = defined('DB_NAME') ? DB_NAME : '';
        return 'jsst_' . md5($jsst_db . '|' . jssupportticket::$_db->prefix . '|' . $jsst_key);
    }

    /**
     * Forget what this request has already checked. For the schema tests, and
     * for anything that recreates a table mid-request.
     */
    public static function reset($jsst_option = '') {
        if ($jsst_option === '') {
            self::$jsst_checked = array();
        } else {
            unset(self::$jsst_checked[$jsst_option]);
        }
    }

}
