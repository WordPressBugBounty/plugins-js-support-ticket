<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * One-time repair of a settings table that lost its primary key.
 *
 * A customer site upgraded 3.1.7 → 4.0.0 → 5.0.0 → 5.0.1 had
 * `js_ticket_config` without `PRIMARY KEY (configname)` - nobody knows why;
 * an old release or a migration tool. Activation's CREATE TABLE IF NOT EXISTS
 * never fixes an existing table, and without the key every REPLACE INTO in the
 * update files appends a row. getInstalledVersion() then read the oldest
 * `versioncode` (2.9.9), so every admin request re-ran every update file from
 * 3.0.0: 280,371 settings rows for 289 names, 63 copies of the same FULLTEXT
 * index on the tickets table, and a minute per admin page.
 *
 * This puts such a table back the way activation defines it, keeping exactly
 * the values the site uses today - getConfigurations() lets the LAST row of a
 * name win, so the last row of each name is the one kept - and removes the
 * duplicate FULLTEXT indexes the re-runs piled up. The customer only clicks
 * Update; nothing here asks them to do anything.
 *
 * Runs once per release (option `jsst_config_repaired`), for an administrator
 * in wp-admin or in the plugin's own update and activation, never for a
 * visitor and never on an AJAX request from the front. A failure leaves the
 * original table untouched and waits an hour before trying again, so it can
 * never become the next loop.
 */
class JSSTconfigrepair {

    /** Release code the check last completed for. */
    const DONE_OPTION = 'jsst_config_repaired';

    /** Held while a repair runs; older than LOCK_SECONDS counts as abandoned. */
    const LOCK_OPTION = 'jsst_config_repair_lock';
    const LOCK_SECONDS = 600;

    /** Set after a failed attempt so the next try is an hour away, not a request away. */
    const WAIT_TRANSIENT = 'jsst_config_repair_wait';

    /** The damaged table kept for support, and when; dropped after BACKUP_DAYS. */
    const BACKUP_OPTION = 'jsst_config_backup';
    const BACKUP_DAYS = 30;

    /**
     * The table prefix to work on. Null means the site's own. Only a test sets
     * it, to run the repair against copies instead of the live tables.
     */
    public static $jsst_prefix = null;

    /**
     * FULLTEXT indexes the plugin creates, by table: name => columns in order.
     * A copy MySQL named `<name>_<n>` with the same columns is a duplicate made
     * by an update file running again, and is the only kind ever dropped.
     */
    private static function ownFulltext() {
        return array(
            'js_ticket_tickets' => array(
                'subject'    => array('subject'),
                'message'    => array('message'),
                'jsst_ir_ft' => array('subject', 'message'),
            ),
            'js_ticket_replies' => array(
                'message'    => array('message'),
            ),
        );
    }

    private static function prefix() {
        return (self::$jsst_prefix !== null) ? self::$jsst_prefix : jssupportticket::$_db->prefix;
    }

    /**
     * The admin entry point: once per release, administrators only.
     *
     * @param bool $jsst_allow_ajax true only for the plugin's own updater,
     *                              which WordPress may run inside an AJAX request.
     */
    public static function maybeRun($jsst_allow_ajax = false) {
        if ((!$jsst_allow_ajax && wp_doing_ajax()) || !current_user_can('manage_options')) {
            return;
        }
        self::dropOldBackup();
        if ((string) get_option(self::DONE_OPTION) === (string) jssupportticket::$_currentversion) {
            return;
        }
        self::run();
    }

    /** Check, and repair what is broken. True when the site is healthy afterwards. */
    public static function run() {
        if (get_transient(self::WAIT_TRANSIENT)) {
            return false;
        }
        if (!self::lock()) {
            return false;
        }
        $jsst_ok = true;
        try {
            if (self::configDamaged()) {
                $jsst_ok = self::rebuildConfig();
            }
            if ($jsst_ok) {
                self::dedupeFulltext();
            }
        } finally {
            self::unlock();
        }
        if ($jsst_ok) {
            if (self::$jsst_prefix === null) {
                update_option(self::DONE_OPTION, (string) jssupportticket::$_currentversion, true);
            }
        } else {
            set_transient(self::WAIT_TRANSIENT, 1, HOUR_IN_SECONDS);
        }
        return $jsst_ok;
    }

    /** No primary key, or more rows than setting names. */
    public static function configDamaged() {
        $jsst_db = jssupportticket::$_db;
        $jsst_table = self::prefix() . 'js_ticket_config';
        if (!self::tableExists($jsst_table)) {
            return false;
        }
        $jsst_pk = $jsst_db->get_results("SHOW KEYS FROM `" . $jsst_table . "` WHERE Key_name = 'PRIMARY'");
        if (empty($jsst_pk)) {
            return true;
        }
        $jsst_rows = (int) $jsst_db->get_var("SELECT COUNT(*) FROM `" . $jsst_table . "`");
        $jsst_names = (int) $jsst_db->get_var("SELECT COUNT(DISTINCT configname) FROM `" . $jsst_table . "`");
        return $jsst_rows > $jsst_names;
    }

    /**
     * Build a clean copy beside the damaged table, then swap the two in one
     * RENAME. Pure SQL: 280,000 rows never pass through PHP.
     */
    public static function rebuildConfig() {
        $jsst_db = jssupportticket::$_db;
        $jsst_table = self::prefix() . 'js_ticket_config';
        $jsst_new = $jsst_table . '_new';
        $jsst_backup = $jsst_table . '_bak_' . gmdate('Ymd');
        if (self::tableExists($jsst_backup)) {
            $jsst_backup .= '_' . gmdate('His');
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        $jsst_before = (int) $jsst_db->get_var("SELECT COUNT(*) FROM `" . $jsst_table . "`");
        $jsst_names  = (int) $jsst_db->get_var("SELECT COUNT(DISTINCT configname) FROM `" . $jsst_table . "`");

        // The new table in the old one's character set, so every stored value fits.
        $jsst_collation = (string) $jsst_db->get_var($jsst_db->prepare(
            "SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s",
            $jsst_table));
        if (!preg_match('/^([a-z0-9]+)_[a-z0-9_]+$/', $jsst_collation, $jsst_m)) {
            $jsst_collation = '';
        }
        $jsst_charset = $jsst_collation !== '' ? ' DEFAULT CHARSET=' . $jsst_m[1] . ' COLLATE=' . $jsst_collation : ' DEFAULT CHARSET=utf8';

        $jsst_db->query("DROP TABLE IF EXISTS `" . $jsst_new . "`");
        // The definition activation uses (includes/activation.php, runSQL()).
        $jsst_db->query("CREATE TABLE `" . $jsst_new . "` (
                      `configname` varchar(100) NOT NULL DEFAULT '',
                      `configvalue` text NOT NULL DEFAULT '',
                      `configfor` varchar(50) DEFAULT NULL,
                      `addon` varchar(100) DEFAULT NULL,
                      PRIMARY KEY (`configname`),
                      FULLTEXT KEY `config_name` (`configname`),
                      FULLTEXT KEY `config_for` (`configfor`)
                    ) ENGINE=InnoDB" . $jsst_charset);
        if ($jsst_db->last_error || !self::tableExists($jsst_new)) {
            return self::fail('could not create the new settings table: ' . $jsst_db->last_error, $jsst_new, false);
        }

        /* Number the rows in the order they are stored. InnoDB keeps a table
           with no primary key in insertion order, which is the order
           getConfigurations() reads, so the highest number per name is the row
           the site uses today. */
        $jsst_columns = $jsst_db->get_col("SHOW COLUMNS FROM `" . $jsst_table . "`");
        $jsst_added = false;
        if (!in_array('jsst_tmp_id', $jsst_columns, true)) {
            $jsst_db->query("ALTER TABLE `" . $jsst_table . "` ADD COLUMN `jsst_tmp_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE FIRST");
            if ($jsst_db->last_error) {
                return self::fail('could not number the settings rows: ' . $jsst_db->last_error, $jsst_new, false);
            }
            $jsst_added = true;
        }

        $jsst_addon = in_array('addon', $jsst_columns, true) ? 'o.`addon`' : 'NULL';
        $jsst_for   = in_array('configfor', $jsst_columns, true) ? 'o.`configfor`' : 'NULL';
        $jsst_db->query("INSERT INTO `" . $jsst_new . "` (`configname`, `configvalue`, `configfor`, `addon`)
                SELECT o.`configname`, COALESCE(o.`configvalue`, ''), " . $jsst_for . ", " . $jsst_addon . "
                  FROM `" . $jsst_table . "` o
                  JOIN (SELECT MAX(`jsst_tmp_id`) AS id FROM `" . $jsst_table . "` GROUP BY `configname`) k
                    ON k.id = o.`jsst_tmp_id`");
        if ($jsst_db->last_error) {
            return self::fail('could not copy the settings: ' . $jsst_db->last_error, $jsst_new, $jsst_added);
        }
        $jsst_after = (int) $jsst_db->get_var("SELECT COUNT(*) FROM `" . $jsst_new . "`");
        if ($jsst_after !== $jsst_names || $jsst_after < 1) {
            return self::fail(sprintf('copied %d settings for %d names', $jsst_after, $jsst_names), $jsst_new, $jsst_added);
        }

        /* The version rows say what really ran: the highest version ever
           recorded, or the last update file applied when that is higher. Not
           this release - a site whose later update files never ran still gets
           them on the next request, exactly once. */
        $jsst_version = self::highestVersion($jsst_table);
        if ($jsst_version > 0) {
            $jsst_dotted = implode('.', str_split((string) $jsst_version));
            foreach (array('versioncode' => $jsst_dotted, 'productversion' => (string) $jsst_version) as $jsst_name => $jsst_value) {
                $jsst_db->query($jsst_db->prepare(
                    "INSERT INTO `" . $jsst_new . "` (`configname`, `configvalue`, `configfor`) VALUES (%s, %s, 'default')
                     ON DUPLICATE KEY UPDATE `configvalue` = VALUES(`configvalue`)", $jsst_name, $jsst_value));
            }
        }

        $jsst_db->query("RENAME TABLE `" . $jsst_table . "` TO `" . $jsst_backup . "`, `" . $jsst_new . "` TO `" . $jsst_table . "`");
        if ($jsst_db->last_error || !self::tableExists($jsst_backup)) {
            return self::fail('could not swap the tables: ' . $jsst_db->last_error, $jsst_new, $jsst_added);
        }

        if (self::$jsst_prefix === null) {
            update_option(self::BACKUP_OPTION, array('table' => $jsst_backup, 'time' => time()), false);
        }
        error_log(sprintf(
            'JS Help Desk: settings table repaired: %d rows before, %d after (%d setting names). The old table is kept as %s for %d days.',
            $jsst_before, (int) $jsst_db->get_var("SELECT COUNT(*) FROM `" . $jsst_table . "`"), $jsst_names, $jsst_backup, self::BACKUP_DAYS));
        return true;
    }

    /**
     * Highest version the damaged table or the update marker records, as a
     * release code (502). The same rule JSSTupdates::getInstalledVersion() uses.
     */
    private static function highestVersion($jsst_table) {
        $jsst_best = 0;
        foreach ((array) jssupportticket::$_db->get_col(
                "SELECT DISTINCT configvalue FROM `" . $jsst_table . "` WHERE configname IN ('versioncode', 'productversion')") as $jsst_value) {
            $jsst_code = (int) preg_replace('/[^0-9]/', '', (string) $jsst_value);
            if ($jsst_code > $jsst_best) {
                $jsst_best = $jsst_code;
            }
        }
        $jsst_applied = (int) get_option('jsst_sql_applied', 0);
        return max($jsst_best, $jsst_applied);
    }

    /** Leave the original as it was, say why, and let run() wait an hour. */
    private static function fail($jsst_why, $jsst_new, $jsst_drop_column) {
        $jsst_db = jssupportticket::$_db;
        $jsst_table = self::prefix() . 'js_ticket_config';
        $jsst_db->query("DROP TABLE IF EXISTS `" . $jsst_new . "`");
        if ($jsst_drop_column) {
            $jsst_db->query("ALTER TABLE `" . $jsst_table . "` DROP COLUMN `jsst_tmp_id`");
        }
        error_log('JS Help Desk: settings table repair stopped, nothing was changed: ' . $jsst_why);
        return false;
    }

    /**
     * Drop the FULLTEXT copies MySQL named `subject_2`, `message_3` ... when an
     * update file added the same index again. Only an index that is FULLTEXT,
     * named `<ours>_<number>` and on exactly the same columns as ours goes;
     * indexes an add-on or the site owner made are never touched. Ours are then
     * created if one is missing - `jsst_ir_ft` may never have fitted under the
     * 64-key limit.
     */
    public static function dedupeFulltext() {
        $jsst_db = jssupportticket::$_db;
        foreach (self::ownFulltext() as $jsst_base => $jsst_keep) {
            $jsst_table = self::prefix() . $jsst_base;
            if (!self::tableExists($jsst_table)) {
                continue;
            }
            $jsst_have = array();
            foreach ((array) $jsst_db->get_results("SHOW INDEX FROM `" . $jsst_table . "`") as $jsst_row) {
                if (strtoupper((string) $jsst_row->Index_type) !== 'FULLTEXT') {
                    continue;
                }
                $jsst_have[$jsst_row->Key_name][(int) $jsst_row->Seq_in_index] = $jsst_row->Column_name;
            }
            $jsst_drop = array();
            foreach ($jsst_have as $jsst_name => $jsst_cols) {
                ksort($jsst_cols);
                $jsst_cols = array_values($jsst_cols);
                if (!preg_match('/^(subject|message|jsst_ir_ft)_[0-9]+$/', $jsst_name, $jsst_m)) {
                    continue;
                }
                /* Same columns as any of ours, not only the one it is named
                   after: an unnamed ADD FULLTEXT (subject, message) in an old
                   update file is named after its first column, subject_N. */
                if (in_array($jsst_cols, array_values($jsst_keep), true)) {
                    $jsst_drop[] = $jsst_name;
                }
            }
            if (!empty($jsst_drop)) {
                if (function_exists('set_time_limit')) {
                    @set_time_limit(300);
                }
                $jsst_db->query("ALTER TABLE `" . $jsst_table . "` " . implode(', ', array_map(function ($jsst_n) {
                    return 'DROP INDEX `' . $jsst_n . '`';
                }, $jsst_drop)));
                error_log(sprintf('JS Help Desk: removed %d duplicate search indexes from %s.', count($jsst_drop), $jsst_table));
            }
            foreach ($jsst_keep as $jsst_name => $jsst_cols) {
                if (isset($jsst_have[$jsst_name])) {
                    continue;
                }
                $jsst_db->query("ALTER TABLE `" . $jsst_table . "` ADD FULLTEXT `" . $jsst_name . "` (`" . implode('`, `', $jsst_cols) . "`)");
            }
        }
    }

    /** The kept copy of the damaged table goes after BACKUP_DAYS. */
    public static function dropOldBackup() {
        $jsst_backup = get_option(self::BACKUP_OPTION);
        if (!is_array($jsst_backup) || empty($jsst_backup['table']) || empty($jsst_backup['time'])) {
            return;
        }
        if (time() - (int) $jsst_backup['time'] < self::BACKUP_DAYS * DAY_IN_SECONDS) {
            return;
        }
        $jsst_table = (string) $jsst_backup['table'];
        if (preg_match('/^' . preg_quote(jssupportticket::$_db->prefix, '/') . 'js_ticket_config_bak_[0-9]{8}(_[0-9]{6})?$/', $jsst_table)) {
            jssupportticket::$_db->query("DROP TABLE IF EXISTS `" . $jsst_table . "`");
        }
        delete_option(self::BACKUP_OPTION);
    }

    private static function tableExists($jsst_table) {
        return jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) === $jsst_table;
    }

    private static function lock() {
        if (add_option(self::LOCK_OPTION, time(), '', 'no')) {
            return true;
        }
        if (time() - (int) get_option(self::LOCK_OPTION, 0) > self::LOCK_SECONDS) {
            update_option(self::LOCK_OPTION, time(), false);
            return true;
        }
        return false;
    }

    private static function unlock() {
        delete_option(self::LOCK_OPTION);
    }
}
