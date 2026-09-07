<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTstorageengine')) {
    return;
}

/**
 * Moving this plugin's tables from MyISAM to InnoDB. (Roadmap 4.0-PERF-03)
 *
 * Twenty-one of this plugin's tables are still MyISAM, which is a storage engine
 * with no transactions, no crash recovery and a lock that covers the whole table
 * rather than the row being written. Every one of those costs something real
 * here: the job queue could not use a transaction to claim work and had to
 * invent a claim token instead (4.0-PERF-02), a migration cannot be wrapped so
 * that a failure leaves nothing behind and had to be journalled row by row
 * instead (4.0-DATA-01), and a power cut during a busy hour leaves a MyISAM
 * table needing a repair that nobody is watching for. Table-level locking is
 * also what makes a hundred-thousand-ticket site slow in a way no index fixes:
 * one long write blocks every read behind it.
 *
 * The conversion itself is one statement per table. What makes it a feature
 * rather than a line in an upgrade script is everything around that statement:
 *
 *   - It says what it will do first, per table, with the size of each one, so
 *     the person running it can pick a quiet hour for the big ones.
 *   - It refuses tables it can see would fail, with the reason, rather than
 *     stopping half way through the list on an error nobody expected.
 *   - It converts one table per run and remembers where it got to, so a host
 *     that kills long requests cannot leave the job in an unknown state.
 *   - It records what each table was before, so going back is exact rather than
 *     a guess.
 *
 * Refusing early is the part that matters most. ALTER TABLE rewrites the whole
 * table, and the two ways it fails on a real site are both knowable in advance:
 * an index wider than InnoDB's key prefix limit, and a FULLTEXT index on a
 * server too old to carry one on InnoDB. Finding either of those on table
 * nineteen of twenty-one, after an hour of rewriting, is the outcome this class
 * exists to prevent.
 *
 * What it does not do is convert anything on its own. This runs when somebody
 * asks for it, because rewriting every table in a help desk is not something a
 * plugin update should do to a site while its owner is asleep.
 */
class JSSTstorageengine {

    /** Where every table here is going. */
    const TARGET = 'InnoDB';

    /** Bumped when the shape of the stored state below changes. */
    const STATE_VERSION = '400-PERF03';

    /** The state of the current or last run. */
    const STATE_KEY = 'jsst_engine_state';

    /**
     * InnoDB's index key prefix limit, in bytes, on the oldest layout.
     *
     * REDUNDANT and COMPACT rows cap an index at 767 bytes; DYNAMIC and
     * COMPRESSED raise it to 3072. A site whose server offers the larger limit
     * gets the larger limit — the point of checking is to refuse what would
     * fail, not to refuse what merely looks big.
     */
    const KEY_PREFIX_SMALL = 767;
    const KEY_PREFIX_LARGE = 3072;

    /** InnoDB gained FULLTEXT here; MariaDB 10.x is above it on this comparison. */
    const FULLTEXT_SINCE = '5.6';

    /** Cached for the request, because the plan reads it once per table. */
    private static $_limit = null;

    /* ------------------------------------------------------------------ *
     * What is on the site
     * ------------------------------------------------------------------ */

    /**
     * Every table this plugin owns, with the engine it is on.
     *
     * Found by name rather than from a list, so a table an add-on created is
     * converted with the rest instead of being the one MyISAM table left behind
     * holding a lock. information_schema is asked once: SHOW TABLE STATUS per
     * table would be twenty-one round trips to answer one question.
     */
    public static function tables() {
        $jsst_like = jssupportticket::$_db->esc_like(jssupportticket::$_db->prefix . 'js_ticket_') . '%';
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT TABLE_NAME AS tablename, ENGINE AS engine, TABLE_ROWS AS rowcount,
                    (DATA_LENGTH + INDEX_LENGTH) AS bytes
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE %s
                ORDER BY TABLE_NAME",
            $jsst_like
        ));
        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            // A view has a null engine and cannot be altered; nothing here
            // creates one, but a site that has wrapped a table in one must not
            // have this fail on it.
            if ($jsst_row->engine === null) {
                continue;
            }
            $jsst_out[$jsst_row->tablename] = array(
                'table'  => $jsst_row->tablename,
                'engine' => $jsst_row->engine,
                'rows'   => (int) $jsst_row->rowcount,
                'bytes'  => (int) $jsst_row->bytes,
            );
        }
        return $jsst_out;
    }

    /** Is InnoDB something this server can actually be asked for? */
    public static function targetAvailable() {
        $jsst_support = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT SUPPORT FROM information_schema.ENGINES WHERE ENGINE = %s",
            self::TARGET
        ));
        return in_array(strtoupper((string) $jsst_support), array('YES', 'DEFAULT'), true);
    }

    /**
     * How wide an index InnoDB will accept here.
     *
     * Read from the server rather than assumed from its version, because the
     * larger limit depends on the row format a site is configured for and not
     * only on what its version is capable of.
     */
    private static function keyPrefixLimit() {
        if (self::$_limit !== null) {
            return self::$_limit;
        }
        // SHOW VARIABLES rather than @@name: a variable that does not exist on
        // this version returns nothing here and an error there.
        $jsst_format = jssupportticket::$_db->get_var("SHOW VARIABLES LIKE 'innodb_default_row_format'", 1);
        $jsst_large = jssupportticket::$_db->get_var("SHOW VARIABLES LIKE 'innodb_large_prefix'", 1);
        $jsst_big = (in_array(strtolower((string) $jsst_format), array('dynamic', 'compressed'), true)
            || in_array(strtoupper((string) $jsst_large), array('ON', '1'), true));
        self::$_limit = $jsst_big ? self::KEY_PREFIX_LARGE : self::KEY_PREFIX_SMALL;
        return self::$_limit;
    }

    /**
     * The reason this table cannot be converted, or '' if it can.
     *
     * Both checks are for failures that only appear once ALTER TABLE has already
     * rewritten the data. Reported per table so the answer is "this index on
     * this table", which somebody can act on, rather than "it failed".
     */
    private static function blocker($jsst_table) {
        $jsst_widest = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT s.INDEX_NAME AS indexname,
                    SUM(COALESCE(s.SUB_PART, c.CHARACTER_OCTET_LENGTH, 8)) AS bytes
                FROM information_schema.STATISTICS s
                JOIN information_schema.COLUMNS c
                     ON c.TABLE_SCHEMA = s.TABLE_SCHEMA
                    AND c.TABLE_NAME = s.TABLE_NAME
                    AND c.COLUMN_NAME = s.COLUMN_NAME
                WHERE s.TABLE_SCHEMA = DATABASE() AND s.TABLE_NAME = %s AND s.INDEX_TYPE = 'BTREE'
                GROUP BY s.INDEX_NAME
                ORDER BY bytes DESC LIMIT 1",
            $jsst_table
        ));
        $jsst_limit = self::keyPrefixLimit();
        if ($jsst_widest && (int) $jsst_widest->bytes > $jsst_limit) {
            return sprintf(
                /* translators: 1: index name, 2: width of the index in bytes, 3: the limit in bytes */
                esc_html(__('The index %1$s is %2$s bytes wide and InnoDB accepts %3$s here. Shorten the columns it covers, or index a prefix of them, and preview again.', 'js-support-ticket')),
                $jsst_widest->indexname,
                number_format_i18n((int) $jsst_widest->bytes),
                number_format_i18n($jsst_limit)
            );
        }

        $jsst_fulltext = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(DISTINCT INDEX_NAME) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_TYPE = 'FULLTEXT'",
            $jsst_table
        ));
        if ($jsst_fulltext > 0 && !self::fulltextSupported()) {
            return esc_html(__('This table has a FULLTEXT index and this database is too old to carry one on InnoDB. Upgrade the database, or drop the index, and preview again.', 'js-support-ticket'));
        }
        return '';
    }

    private static function fulltextSupported() {
        $jsst_version = method_exists(jssupportticket::$_db, 'db_version') ? jssupportticket::$_db->db_version() : '';
        if ($jsst_version === '') {
            return false; // Unknown means unsafe: refusing is recoverable, failing mid-rewrite is not.
        }
        return version_compare($jsst_version, self::FULLTEXT_SINCE, '>=');
    }

    /* ------------------------------------------------------------------ *
     * The preview
     * ------------------------------------------------------------------ */

    /**
     * What conversion would do, table by table.
     *
     * Everything the screen shows comes from here, including the reasons a table
     * is being left alone. Sizes are carried because they are the whole of the
     * advice this can honestly give about timing: ALTER TABLE holds a lock for
     * as long as it takes to rewrite the table, and only the size says whether
     * that is a blink or a lunch break.
     */
    public static function plan() {
        if (!self::targetAvailable()) {
            return new WP_Error('jsst_no_innodb', esc_html(sprintf(
                /* translators: %s: the name of a database storage engine */
                __('This database does not offer %s, so nothing can be converted.', 'js-support-ticket'),
                self::TARGET
            )));
        }

        $jsst_out = array(
            'converted' => array(),
            'pending'   => array(),
            'blocked'   => array(),
            'bytes'     => 0,
            'largest'   => 0,
        );
        foreach (self::tables() as $jsst_name => $jsst_table) {
            if (strcasecmp($jsst_table['engine'], self::TARGET) === 0) {
                $jsst_out['converted'][$jsst_name] = $jsst_table;
                continue;
            }
            $jsst_blocker = self::blocker($jsst_name);
            if ($jsst_blocker !== '') {
                $jsst_table['reason'] = $jsst_blocker;
                $jsst_out['blocked'][$jsst_name] = $jsst_table;
                continue;
            }
            $jsst_out['pending'][$jsst_name] = $jsst_table;
            $jsst_out['bytes'] += $jsst_table['bytes'];
            $jsst_out['largest'] = max($jsst_out['largest'], $jsst_table['bytes']);
        }
        return $jsst_out;
    }

    /* ------------------------------------------------------------------ *
     * The run
     * ------------------------------------------------------------------ */

    /**
     * The current run, always in one shape.
     *
     * Kept in one option rather than a table of its own: a run is at most a few
     * dozen table names, it is read only by the screen that started it, and a
     * feature whose job is to fix the storage layer should not need a new table
     * in the storage layer to do it.
     */
    public static function state() {
        $jsst_state = get_option(self::STATE_KEY, array());
        if (!is_array($jsst_state) || !isset($jsst_state['version']) || $jsst_state['version'] !== self::STATE_VERSION) {
            // An older shape is history, not state. Reading it as if it were
            // current is how a resume converts the wrong list.
            return self::blankState();
        }
        return array_merge(self::blankState(), $jsst_state);
    }

    private static function blankState() {
        return array(
            'version'   => self::STATE_VERSION,
            'status'    => 'idle',       // idle | running | complete | cancelled
            'direction' => 'convert',    // convert | revert
            'queue'     => array(),      // tables still to do, in order
            'done'      => array(),      // table => the engine it was on before
            'failed'    => array(),      // table => why it stopped
            'total'     => 0,
            'started'   => 0,
            'updated'   => 0,
            'finished'  => 0,
        );
    }

    private static function save($jsst_state) {
        $jsst_state['updated'] = time();
        // Never autoloaded: this is read by one admin screen and one job, and
        // loading it into every front-end request would be a cost paid by every
        // visitor for a maintenance task that runs once.
        update_option(self::STATE_KEY, $jsst_state, false);
        return $jsst_state;
    }

    public static function isRunning() {
        $jsst_state = self::state();
        return ($jsst_state['status'] === 'running' && !empty($jsst_state['queue']));
    }

    /**
     * Start converting everything the plan says can be converted.
     *
     * The queue is fixed at the moment it starts rather than recomputed each
     * step: a run that is asked to do a list should do that list, and a table
     * created while it works belongs to the next run, not this one.
     */
    public static function begin() {
        $jsst_plan = self::plan();
        if (is_wp_error($jsst_plan)) {
            return $jsst_plan;
        }
        if (empty($jsst_plan['pending'])) {
            return new WP_Error('jsst_nothing_to_do', esc_html(sprintf(
                /* translators: %s: the name of a database storage engine */
                __('Every table is already on %s.', 'js-support-ticket'),
                self::TARGET
            )));
        }
        if (self::isRunning()) {
            return new WP_Error('jsst_already_running', esc_html(__('A conversion is already running.', 'js-support-ticket')));
        }
        // Smallest first. The early tables are the ones that prove the run works
        // on this server, and proving it on a table that rewrites in a
        // millisecond is better than proving it on the largest one there is.
        $jsst_queue = $jsst_plan['pending'];
        uasort($jsst_queue, array(__CLASS__, 'bySize'));

        $jsst_state = self::blankState();
        $jsst_state['status'] = 'running';
        $jsst_state['direction'] = 'convert';
        $jsst_state['queue'] = array_keys($jsst_queue);
        $jsst_state['total'] = count($jsst_queue);
        $jsst_state['started'] = time();
        return self::save($jsst_state);
    }

    /**
     * Put the tables this converted back on the engine they were on.
     *
     * The rollback the roadmap asks for, and it is only possible because the
     * previous engine of each table was written down as it went. It is offered
     * for the case where something else on the site turns out to depend on
     * MyISAM behaviour — the data itself is identical either way, so this is
     * about undoing a decision rather than recovering from damage.
     */
    public static function beginRevert() {
        $jsst_state = self::state();
        if (self::isRunning()) {
            return new WP_Error('jsst_already_running', esc_html(__('A conversion is already running. Stop it before going back.', 'js-support-ticket')));
        }
        if (empty($jsst_state['done'])) {
            return new WP_Error('jsst_nothing_to_revert', esc_html(__('Nothing has been converted, so there is nothing to put back.', 'js-support-ticket')));
        }
        $jsst_revert = self::blankState();
        $jsst_revert['status'] = 'running';
        $jsst_revert['direction'] = 'revert';
        // Newest first, undoing in the order it was done.
        $jsst_revert['queue'] = array_reverse(array_keys($jsst_state['done']));
        $jsst_revert['done'] = $jsst_state['done'];
        $jsst_revert['total'] = count($jsst_revert['queue']);
        $jsst_revert['started'] = time();
        return self::save($jsst_revert);
    }

    public static function cancel() {
        $jsst_state = self::state();
        if ($jsst_state['status'] !== 'running') {
            return $jsst_state;
        }
        $jsst_state['status'] = 'cancelled';
        $jsst_state['queue'] = array();
        $jsst_state['finished'] = time();
        return self::save($jsst_state);
    }

    /**
     * Convert one table, and say whether there is more.
     *
     * One table per step because a table is the smallest unit ALTER TABLE has:
     * it either rewrites the whole thing or it does not, and there is no cursor
     * to save half way through one. That is the honest granularity, and it is
     * why the plan reports sizes — resumability between tables is no comfort if
     * a single table is too big to rewrite inside the host's limit.
     *
     * A table that fails is recorded and stepped over rather than retried
     * forever. The rest of the list is still worth converting, and the reason is
     * on the screen afterwards.
     */
    public static function step() {
        $jsst_state = self::state();
        if ($jsst_state['status'] !== 'running') {
            return false;
        }
        if (empty($jsst_state['queue'])) {
            return self::finish($jsst_state);
        }

        $jsst_table = array_shift($jsst_state['queue']);
        $jsst_reverting = ($jsst_state['direction'] === 'revert');
        $jsst_target = $jsst_reverting
            ? (isset($jsst_state['done'][$jsst_table]) ? $jsst_state['done'][$jsst_table] : 'MyISAM')
            : self::TARGET;

        $jsst_result = self::alter($jsst_table, $jsst_target);
        if (is_wp_error($jsst_result)) {
            $jsst_state['failed'][$jsst_table] = $jsst_result->get_error_message();
        } elseif ($jsst_reverting) {
            unset($jsst_state['done'][$jsst_table]);
        } else {
            $jsst_state['done'][$jsst_table] = $jsst_result;
        }

        if (empty($jsst_state['queue'])) {
            return self::finish($jsst_state);
        }
        self::save($jsst_state);
        return true;
    }

    private static function finish($jsst_state) {
        $jsst_state['status'] = 'complete';
        $jsst_state['queue'] = array();
        $jsst_state['finished'] = time();
        self::save($jsst_state);
        return false;
    }

    /**
     * The statement itself, and the engine the table was on before it ran.
     *
     * Read back from information_schema afterwards rather than trusted: a
     * statement that reports no error but leaves the table on the engine it
     * started on — which is what a server with the target engine disabled at
     * runtime does — must be a failure here, not a success that quietly
     * converted nothing.
     */
    private static function alter($jsst_table, $jsst_target) {
        $jsst_tables = self::tables();
        if (!isset($jsst_tables[$jsst_table])) {
            return new WP_Error('jsst_no_table', esc_html(__('That table is no longer on this site.', 'js-support-ticket')));
        }
        $jsst_was = $jsst_tables[$jsst_table]['engine'];
        if (strcasecmp($jsst_was, $jsst_target) === 0) {
            return $jsst_was; // Already there. Rewriting it again would cost the same as converting it.
        }
        if (strcasecmp($jsst_target, self::TARGET) === 0) {
            $jsst_blocker = self::blocker($jsst_table);
            if ($jsst_blocker !== '') {
                return new WP_Error('jsst_blocked', $jsst_blocker);
            }
        }

        // Errors are wanted as a value, not as a notice printed into the middle
        // of an admin page or swallowed by a screen that shows nothing.
        $jsst_hide = jssupportticket::$_db->hide_errors();
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- the table name comes from information_schema and the engine from a constant; neither can be parameterised.
        jssupportticket::$_db->query("ALTER TABLE `" . esc_sql($jsst_table) . "` ENGINE = " . esc_sql($jsst_target));
        $jsst_error = jssupportticket::$_db->last_error;
        jssupportticket::$_db->show_errors($jsst_hide);

        $jsst_now = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s",
            $jsst_table
        ));
        if (strcasecmp((string) $jsst_now, $jsst_target) !== 0) {
            return new WP_Error('jsst_alter_failed', ($jsst_error !== '')
                ? substr($jsst_error, 0, 300)
                : esc_html(__('The database reported no error but left the table on the engine it started on.', 'js-support-ticket')));
        }
        return $jsst_was;
    }

    /** Smallest first, so a run proves itself on a cheap table. */
    public static function bySize($jsst_a, $jsst_b) {
        if ($jsst_a['bytes'] === $jsst_b['bytes']) {
            return 0;
        }
        return ($jsst_a['bytes'] < $jsst_b['bytes']) ? -1 : 1;
    }

    /* ------------------------------------------------------------------ *
     * Reporting
     * ------------------------------------------------------------------ */

    /**
     * A summary for the System Status page. (Roadmap 4.0-OPS-02)
     *
     * Deliberately cheap — engine counts only, no per-index inspection — because
     * it is read on every load of a page that is opened when something is
     * already wrong.
     */
    public static function summary() {
        $jsst_tables = self::tables();
        $jsst_legacy = array();
        foreach ($jsst_tables as $jsst_name => $jsst_table) {
            if (strcasecmp($jsst_table['engine'], self::TARGET) !== 0) {
                $jsst_legacy[$jsst_name] = $jsst_table['engine'];
            }
        }
        $jsst_state = self::state();
        return array(
            'total'     => count($jsst_tables),
            'legacy'    => count($jsst_legacy),
            'engines'   => $jsst_legacy,
            'running'   => self::isRunning(),
            'remaining' => count($jsst_state['queue']),
            'status'    => $jsst_state['status'],
        );
    }

}
