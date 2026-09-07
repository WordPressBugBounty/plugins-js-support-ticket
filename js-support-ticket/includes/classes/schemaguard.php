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
