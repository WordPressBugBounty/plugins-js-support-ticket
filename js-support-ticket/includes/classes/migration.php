<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTmigration')) {
    return;
}

/**
 * Migrating from another help desk. (Roadmap 4.0-DATA-01)
 *
 * The importer this wraps is the most valuable thing in the product and the
 * least safe. It reads every ticket in the source into memory at once, writes
 * them in a single request, and only records what it managed to import once the
 * whole loop has finished — so an import that runs out of time part-way through
 * records nothing, and running it again imports everything a second time. The
 * failure a customer sees is duplicated tickets on the site they were migrating
 * to, which is the worst possible first impression of a product they have just
 * chosen.
 *
 * This class is the safety around that. It does four things the importer cannot
 * do for itself:
 *
 *   - Says what is about to happen, before it happens, counted from the source.
 *   - Gives the run an identity, so progress survives a timeout and a rerun
 *     continues rather than repeating.
 *   - Records every row the run inserts, so it can be taken back out again
 *     without touching anything that was already on the site.
 *   - Checks afterwards that what arrived matches what was promised.
 *
 * The journal is the heart of it. Every insert in this plugin goes through
 * JSSTtable::store(), so recording there catches every row every importer writes
 * — including the three sources here and any added later — without the importers
 * knowing anything about it. Rollback is then exact: it deletes the rows this
 * migration created and nothing else, which is what makes it safe to run on a
 * site that already had tickets of its own.
 */
class JSSTmigration {

    /** Bumped when either table below changes. */
    const SCHEMA_VERSION = '400-DATA01';

    /** Rows written to the journal in one insert. */
    const JOURNAL_CHUNK = 200;

    /**
     * The migration currently recording, or 0.
     *
     * A plain static rather than a lookup, because JSSTtable::store() consults it
     * on every insert the plugin makes, migration or not. It has to cost nothing
     * when nothing is migrating.
     */
    private static $_recording = 0;

    /** Journal rows not yet written, buffered so one insert covers many rows. */
    private static $_buffer = array();

    public static function table() {
        return jssupportticket::$_db->prefix . 'js_ticket_migrations';
    }

    public static function journalTable() {
        return jssupportticket::$_db->prefix . 'js_ticket_migration_journal';
    }

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    public static function ensureSchema() {
        // Both tables: a journal without its migration row cannot be rolled
        // back, and inProgress() asks this on every insert the plugin makes, so
        // the check is memoised per request. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_migration_schema', self::SCHEMA_VERSION,
                array('js_ticket_migrations' => array(), 'js_ticket_migration_journal' => array()))) {
            return;
        }
        $jsst_charset = jssupportticket::$_db->get_charset_collate();

        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . self::table() . "` (
                id int(11) NOT NULL AUTO_INCREMENT,
                token varchar(32) NOT NULL,
                source varchar(30) NOT NULL,
                sourceversion varchar(20) NOT NULL DEFAULT '',
                status varchar(20) NOT NULL DEFAULT 'preview',
                cursorstate longtext,
                counts longtext,
                findings longtext,
                notes text,
                startedby int(11) NOT NULL DEFAULT '0',
                started datetime DEFAULT NULL,
                updated datetime DEFAULT NULL,
                finished datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY jsst_token (token),
                KEY jsst_status (status)
            ) " . $jsst_charset);

        // Deliberately narrow: a row per imported record is a lot of rows, and
        // the only questions ever asked of it are "what did migration N create"
        // and "in what order". Anything else belongs on the migration row.
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . self::journalTable() . "` (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                migrationid int(11) NOT NULL,
                tablename varchar(64) NOT NULL,
                rowid bigint(20) NOT NULL,
                PRIMARY KEY (id),
                KEY jsst_migration (migrationid),
                KEY jsst_table (migrationid, tablename)
            ) " . $jsst_charset);

        update_option('jsst_migration_schema', self::SCHEMA_VERSION, false);
    }

    /* ------------------------------------------------------------------ *
     * Which source, and which version of it
     * ------------------------------------------------------------------ */

    /**
     * The sources this can read, and what is present on this site.
     *
     * Version matters and is reported: these importers were written against a
     * particular table layout, and the commonest cause of a broken import is a
     * source newer than the mapping. Data is looked for whether or not the
     * plugin is active, because a site that has already deactivated its old help
     * desk still has all of its tickets and still wants them.
     */
    public static function sources() {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $jsst_defs = array(
            'supportcandy' => array(
                'id'     => 1,
                'label'  => esc_html(__('SupportCandy', 'js-support-ticket')),
                'plugin' => 'supportcandy/supportcandy.php',
                'probe'  => 'psmsc_tickets',
            ),
            'awesomesupport' => array(
                'id'     => 2,
                'label'  => esc_html(__('Awesome Support', 'js-support-ticket')),
                'plugin' => 'awesome-support/awesome-support.php',
                'probe'  => '', // Post types, not tables of its own.
            ),
            'fluentsupport' => array(
                'id'     => 3,
                'label'  => esc_html(__('Fluent Support', 'js-support-ticket')),
                'plugin' => 'fluent-support/fluent-support.php',
                'probe'  => 'fs_tickets',
            ),
        );

        $jsst_out = array();
        foreach ($jsst_defs as $jsst_key => $jsst_def) {
            $jsst_active = is_plugin_active($jsst_def['plugin']);
            $jsst_hasdata = false;
            if ($jsst_def['probe'] !== '') {
                $jsst_hasdata = self::tableExists(jssupportticket::$_db->prefix . $jsst_def['probe']);
            } else {
                $jsst_hasdata = ((int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                    "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "posts` WHERE post_type = %s",
                    'ticket'
                )) > 0);
            }
            $jsst_out[$jsst_key] = array(
                'id'        => $jsst_def['id'],
                'label'     => $jsst_def['label'],
                'plugin'    => $jsst_def['plugin'],
                'active'    => $jsst_active,
                'present'   => ($jsst_active || $jsst_hasdata),
                'version'   => self::pluginVersion($jsst_def['plugin']),
            );
        }
        return $jsst_out;
    }

    /** The version the source plugin declares, or '' if it is not installed. */
    private static function pluginVersion($jsst_file) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $jsst_path = WP_PLUGIN_DIR . '/' . $jsst_file;
        if (!file_exists($jsst_path)) {
            return '';
        }
        $jsst_data = get_plugin_data($jsst_path, false, false);
        return isset($jsst_data['Version']) ? $jsst_data['Version'] : '';
    }

    public static function tableExists($jsst_table) {
        return (jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) === $jsst_table);
    }

    /** The source key for one of the numeric ids the existing screens use. */
    public static function keyForId($jsst_id) {
        foreach (self::sources() as $jsst_key => $jsst_source) {
            if ((int) $jsst_source['id'] === (int) $jsst_id) {
                return $jsst_key;
            }
        }
        return '';
    }

    /* ------------------------------------------------------------------ *
     * The run itself
     * ------------------------------------------------------------------ */

    /**
     * Begin a migration and return its token.
     *
     * The token is what makes a rerun a continuation rather than a repeat: it is
     * carried on the screen, in the queued job and on every journal row, so a
     * run that stops half way is picked up by its name rather than guessed at.
     */
    public static function start($jsst_source, $jsst_version = '') {
        self::ensureSchema();
        $jsst_token = wp_generate_password(24, false, false);
        $jsst_now = gmdate('Y-m-d H:i:s');
        jssupportticket::$_db->insert(self::table(), array(
            'token'         => $jsst_token,
            'source'        => (string) $jsst_source,
            'sourceversion' => (string) $jsst_version,
            'status'        => 'running',
            'cursorstate'   => wp_json_encode(array()),
            'counts'        => wp_json_encode(array()),
            'startedby'     => get_current_user_id(),
            'started'       => $jsst_now,
            'updated'       => $jsst_now,
        ));
        return $jsst_token;
    }

    public static function get($jsst_token) {
        self::ensureSchema();
        return jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE token = %s",
            (string) $jsst_token
        ));
    }

    /**
     * The most recent run, whatever became of it.
     *
     * `$jsst_exclude` leaves out runs from one source. The screens use it to
     * skip CSV imports: those are a different job with their own screen, and a
     * help-desk migration report that opened showing somebody's spreadsheet
     * would be answering a question nobody asked.
     */
    public static function latest($jsst_exclude = '') {
        self::ensureSchema();
        if ($jsst_exclude !== '') {
            return jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                "SELECT * FROM `" . self::table() . "` WHERE source != %s ORDER BY id DESC LIMIT 1",
                (string) $jsst_exclude
            ));
        }
        return jssupportticket::$_db->get_row(
            "SELECT * FROM `" . self::table() . "` ORDER BY id DESC LIMIT 1"
        );
    }

    /**
     * A run that is still going, if there is one.
     *
     * A rollback counts: it is deleting rows by id from the same tables an
     * import would be writing to, and the two interleaved would be impossible to
     * reason about afterwards.
     */
    public static function inProgress() {
        self::ensureSchema();
        return jssupportticket::$_db->get_row(
            "SELECT * FROM `" . self::table() . "` WHERE status IN ('running', 'rollingback') ORDER BY id DESC LIMIT 1"
        );
    }

    public static function setStatus($jsst_token, $jsst_status, $jsst_notes = '') {
        $jsst_data = array('status' => $jsst_status, 'updated' => gmdate('Y-m-d H:i:s'));
        if ($jsst_notes !== '') {
            $jsst_data['notes'] = substr($jsst_notes, 0, 1000);
        }
        if (in_array($jsst_status, array('complete', 'failed', 'cancelled', 'rolledback'), true)) {
            $jsst_data['finished'] = gmdate('Y-m-d H:i:s');
        }
        jssupportticket::$_db->update(self::table(), $jsst_data, array('token' => (string) $jsst_token));
    }

    /** Where the run has got to, as whatever shape the importer finds useful. */
    public static function cursor($jsst_token) {
        $jsst_row = self::get($jsst_token);
        if (!$jsst_row) {
            return array();
        }
        $jsst_cursor = json_decode($jsst_row->cursorstate, true);
        return is_array($jsst_cursor) ? $jsst_cursor : array();
    }

    public static function setCursor($jsst_token, $jsst_cursor) {
        jssupportticket::$_db->update(self::table(), array(
            'cursorstate' => wp_json_encode($jsst_cursor),
            'updated'     => gmdate('Y-m-d H:i:s'),
        ), array('token' => (string) $jsst_token));
    }

    public static function setCounts($jsst_token, $jsst_counts) {
        jssupportticket::$_db->update(self::table(), array(
            'counts'  => wp_json_encode($jsst_counts),
            'updated' => gmdate('Y-m-d H:i:s'),
        ), array('token' => (string) $jsst_token));
    }

    /**
     * Fold one slice's tallies into the run's.
     *
     * The importers count per run rather than per migration: every slice starts
     * its tallies at zero and writes them over the same option when it finishes,
     * so taking that option as the answer would report the last slice as the
     * whole import.
     *
     * Imported is added up, because a record is imported once: the importers
     * keep a list of what they have already brought across and skip it next
     * time, so no slice can count the same record twice.
     *
     * Skipped and failed are taken at their highest instead. Only tickets are
     * sliced; every other importer runs again in full on each slice and reports
     * the same records as skipped — or, where an add-on is missing, as failed —
     * every time. Adding those would multiply a fixed number of failures by the
     * number of slices and report fifty imagined failures for two real ones. The
     * cost is that genuinely different ticket failures in different slices are
     * reported as the worst slice rather than the sum, which understates rather
     * than invents; what was actually created is in the journal either way.
     */
    public static function mergeCounts($jsst_token, $jsst_slice) {
        $jsst_row = self::get($jsst_token);
        if (!$jsst_row || !is_array($jsst_slice)) {
            return array();
        }
        $jsst_totals = json_decode($jsst_row->counts, true);
        if (!is_array($jsst_totals)) {
            $jsst_totals = array();
        }
        foreach ($jsst_slice as $jsst_entity => $jsst_entry) {
            if (!is_array($jsst_entry)) {
                continue;
            }
            if (!isset($jsst_totals[$jsst_entity])) {
                $jsst_totals[$jsst_entity] = array('imported' => 0, 'skipped' => 0, 'failed' => 0);
            }
            $jsst_totals[$jsst_entity]['imported'] += isset($jsst_entry['imported']) ? (int) $jsst_entry['imported'] : 0;
            foreach (array('skipped', 'failed') as $jsst_field) {
                $jsst_totals[$jsst_entity][$jsst_field] = max(
                    (int) $jsst_totals[$jsst_entity][$jsst_field],
                    isset($jsst_entry[$jsst_field]) ? (int) $jsst_entry[$jsst_field] : 0
                );
            }
        }
        self::setCounts($jsst_token, $jsst_totals);
        return $jsst_totals;
    }

    public static function setFindings($jsst_token, $jsst_findings) {
        jssupportticket::$_db->update(self::table(), array(
            'findings' => wp_json_encode($jsst_findings),
            'updated'  => gmdate('Y-m-d H:i:s'),
        ), array('token' => (string) $jsst_token));
    }

    /* ------------------------------------------------------------------ *
     * The journal
     * ------------------------------------------------------------------ */

    /**
     * Start recording every row the plugin inserts against this migration.
     *
     * Deliberately explicit. Recording is on only for the stretch of work that
     * belongs to a migration, so an agent replying to a ticket while an import
     * runs in the background does not have their reply journalled and taken away
     * by a later rollback.
     */
    public static function startRecording($jsst_token) {
        $jsst_row = self::get($jsst_token);
        if (!$jsst_row) {
            return false;
        }
        self::$_recording = (int) $jsst_row->id;
        return true;
    }

    public static function stopRecording() {
        self::flush();
        self::$_recording = 0;
    }

    public static function isRecording() {
        return (self::$_recording > 0);
    }

    /**
     * Note that a row was created by the running migration.
     *
     * Called from JSSTtable::store() on every insert. Buffered rather than
     * written one row at a time: an import of fifty thousand tickets writes
     * several hundred thousand rows, and a round trip each would cost more than
     * the import itself.
     */
    public static function journal($jsst_tablename, $jsst_rowid) {
        if (self::$_recording <= 0 || !$jsst_rowid) {
            return;
        }
        self::$_buffer[] = array((int) self::$_recording, (string) $jsst_tablename, (int) $jsst_rowid);
        if (count(self::$_buffer) >= self::JOURNAL_CHUNK) {
            self::flush();
        }
    }

    /** Write the buffered journal rows as one statement. */
    public static function flush() {
        if (empty(self::$_buffer)) {
            return;
        }
        $jsst_values = array();
        $jsst_args = array();
        foreach (self::$_buffer as $jsst_entry) {
            $jsst_values[] = '(%d, %s, %d)';
            $jsst_args[] = $jsst_entry[0];
            $jsst_args[] = $jsst_entry[1];
            $jsst_args[] = $jsst_entry[2];
        }
        self::$_buffer = array();
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "INSERT INTO `" . self::journalTable() . "` (migrationid, tablename, rowid) VALUES " . implode(',', $jsst_values),
            $jsst_args
        ));
    }

    /** How many rows this migration created, by table. */
    public static function journalSummary($jsst_token) {
        $jsst_row = self::get($jsst_token);
        if (!$jsst_row) {
            return array();
        }
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT tablename, COUNT(*) AS total FROM `" . self::journalTable() . "`
                WHERE migrationid = %d GROUP BY tablename ORDER BY total DESC",
            (int) $jsst_row->id
        ));
        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_entry) {
            $jsst_out[$jsst_entry->tablename] = (int) $jsst_entry->total;
        }
        return $jsst_out;
    }

    /* ------------------------------------------------------------------ *
     * Taking it back out
     * ------------------------------------------------------------------ */

    /**
     * Delete everything this migration created.
     *
     * Newest row first, so a row that something else points at goes after the
     * thing pointing at it. That ordering is the reason the journal keeps its own
     * auto-increment: it records not just what was created but the order it was
     * created in, and undoing in reverse is the only ordering that is correct
     * without knowing the relationships between twenty-odd tables.
     *
     * Deletes are done by id in batches rather than one statement per row, and
     * the journal rows go only after the data rows they describe — so a rollback
     * that is interrupted can be run again and finishes the job, rather than
     * losing its record of what is left to remove.
     */
    public static function rollback($jsst_token) {
        $jsst_row = self::get($jsst_token);
        if (!$jsst_row) {
            return new WP_Error('jsst_no_migration', esc_html(__('That migration cannot be found.', 'js-support-ticket')));
        }
        self::flush();
        $jsst_removed = 0;

        while (true) {
            $jsst_batch = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
                "SELECT id, tablename, rowid FROM `" . self::journalTable() . "`
                    WHERE migrationid = %d ORDER BY id DESC LIMIT %d",
                (int) $jsst_row->id,
                self::JOURNAL_CHUNK
            ));
            if (empty($jsst_batch)) {
                break;
            }
            // Grouped by table so one DELETE covers a run of rows from the same
            // table, which is what the journal usually holds.
            $jsst_bytable = array();
            $jsst_journalids = array();
            foreach ($jsst_batch as $jsst_entry) {
                $jsst_bytable[$jsst_entry->tablename][] = (int) $jsst_entry->rowid;
                $jsst_journalids[] = (int) $jsst_entry->id;
            }
            foreach ($jsst_bytable as $jsst_tablename => $jsst_ids) {
                if (!self::tableExists($jsst_tablename)) {
                    continue;
                }
                $jsst_removed += (int) jssupportticket::$_db->query(
                    "DELETE FROM `" . esc_sql($jsst_tablename) . "` WHERE id IN (" . implode(',', array_map('intval', $jsst_ids)) . ")"
                );
            }
            jssupportticket::$_db->query(
                "DELETE FROM `" . self::journalTable() . "` WHERE id IN (" . implode(',', array_map('intval', $jsst_journalids)) . ")"
            );
        }

        // The importer's own "already imported" lists have to go too, or the next
        // run would skip everything it has just been asked to bring back.
        self::forgetImportedIds($jsst_row->source);
        self::setStatus($jsst_token, 'rolledback', sprintf(
            /* translators: %d: number of records removed */
            esc_html(__('Rolled back: %d records removed.', 'js-support-ticket')),
            $jsst_removed
        ));
        return $jsst_removed;
    }

    /**
     * Clear the source-id lists the importer uses to skip work it has done.
     *
     * These are the options the importers have always kept. After a rollback the
     * data they refer to is gone, so leaving them would make the next import a
     * no-op that reports everything as skipped — a silent failure that looks like
     * success.
     */
    private static function forgetImportedIds($jsst_source) {
        $jsst_prefixes = array(
            'supportcandy'   => 'js_support_ticket_support_candy_data_',
            'awesomesupport' => 'js_support_ticket_awesome_support_data_',
            'fluentsupport'  => 'js_support_ticket_fluent_support_data_',
        );
        if (!isset($jsst_prefixes[$jsst_source])) {
            return;
        }
        $jsst_names = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
            "SELECT option_name FROM `" . jssupportticket::$_db->prefix . "options` WHERE option_name LIKE %s",
            jssupportticket::$_db->esc_like($jsst_prefixes[$jsst_source]) . '%'
        ));
        foreach ((array) $jsst_names as $jsst_name) {
            delete_option($jsst_name);
        }
    }

}
