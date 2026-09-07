<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Retention cleanup — part of the free core. (Roadmap 4.0-CORE-13)
 *
 * This is the only feature in the product that deletes a customer's tickets and
 * files permanently, on a schedule, with nobody watching. Everything here is built
 * around that: one method decides what is eligible, and both the preview and the
 * deletion use it, so what the dry run shows is by construction what the run
 * would remove.
 *
 * What 4.0 adds over the add-on:
 *   - a dry run, which reports what would go and frees nothing;
 *   - exclusions, so departments and priorities can be kept out of retention;
 *   - a storage-savings report, so the attachment purge can be justified;
 *   - a record of every run on the activity timeline.
 *
 * Loaded only when the stand-alone Auto Cleanup add-on is inactive:
 * JSSTincluder::getPluginPath() resolves the 'autocleanup' module to the add-on
 * directory while that add-on is active. The three settings keep their names.
 * (Roadmap 4.0-CORE-19)
 *
 * Archive tiers, legal hold and deletion approvals stay Pro.
 */
class JSSTautocleanupModel {

    /** Tickets or attachments handled per run. */
    const BATCH = 100;

    /** A ticket is only ever eligible once closed. 5 is Closed, 6 is Merged. */
    private static function closedStatuses() {
        return array(5, 6);
    }

    /**
     * Months after closing before a ticket is eligible. 0 means never.
     */
    public static function ticketInterval() {
        $jsst_value = isset(jssupportticket::$_config['autocleanup_ticket_interval']) ? jssupportticket::$_config['autocleanup_ticket_interval'] : 0;
        return is_numeric($jsst_value) ? (int) $jsst_value : 0;
    }

    /**
     * Months after closing before a ticket's attachments are eligible. 0 means
     * never.
     */
    public static function attachmentInterval() {
        $jsst_value = isset(jssupportticket::$_config['autocleanup_attachment_interval']) ? jssupportticket::$_config['autocleanup_attachment_interval'] : 0;
        return is_numeric($jsst_value) ? (int) $jsst_value : 0;
    }

    /**
     * The cutoff for an interval in months, in site time.
     */
    public static function cutoff($jsst_months) {
        $jsst_months = (int) $jsst_months;
        if ($jsst_months <= 0) {
            return '';
        }
        return date_i18n('Y-m-d H:i:s', jssupportticketphplib::JSST_strtotime('now -' . $jsst_months . ' months'));
    }

    /* ------------------------------------------------------------------ *
     * Exclusions (Roadmap 4.0-CORE-13)
     * ------------------------------------------------------------------ */

    /**
     * Departments kept out of retention, as ids.
     */
    public static function excludedDepartments() {
        return self::idList('autocleanup_exclude_departments');
    }

    /**
     * Priorities kept out of retention, as ids.
     */
    public static function excludedPriorities() {
        return self::idList('autocleanup_exclude_priorities');
    }

    /**
     * A stored id list, reduced to distinct positive integers.
     *
     * The settings screen picks these by name and posts a JSON array. A value
     * saved before that was a comma-separated list typed by hand, and is still
     * read here rather than migrated - both forms mean the same ids, and an
     * exclusion silently reading as empty is a deletion.
     */
    private static function idList($jsst_name) {
        $jsst_raw = isset(jssupportticket::$_config[$jsst_name]) ? jssupportticket::$_config[$jsst_name] : '';
        $jsst_decoded = json_decode((string) $jsst_raw, true);
        $jsst_pieces = is_array($jsst_decoded) ? $jsst_decoded : explode(',', (string) $jsst_raw);
        $jsst_ids = array();
        foreach ($jsst_pieces as $jsst_piece) {
            if (!is_scalar($jsst_piece)) {
                continue;
            }
            $jsst_piece = trim((string) $jsst_piece);
            if ($jsst_piece !== '' && is_numeric($jsst_piece) && (int) $jsst_piece > 0) {
                $jsst_ids[(int) $jsst_piece] = (int) $jsst_piece;
            }
        }
        return array_values($jsst_ids);
    }

    /**
     * The WHERE fragment shared by the preview and the deletion.
     *
     * One implementation on purpose: if the preview and the run could disagree,
     * the dry run would be worse than no dry run at all.
     */
    private function eligibilityClause($jsst_cutoff, $jsst_alias = 'ticket') {
        $jsst_closed = self::closedStatuses();
        $jsst_where = array(
            $jsst_alias . '.status IN (' . implode(',', array_map('intval', $jsst_closed)) . ')',
            $jsst_alias . '.closed IS NOT NULL',
            $jsst_alias . ".closed <> '0000-00-00 00:00:00'",
            $jsst_alias . '.closed <= %s',
        );
        $jsst_args = array($jsst_cutoff);

        $jsst_departments = self::excludedDepartments();
        if (!empty($jsst_departments)) {
            $jsst_where[] = $jsst_alias . '.departmentid NOT IN (' . implode(',', array_fill(0, count($jsst_departments), '%d')) . ')';
            $jsst_args = array_merge($jsst_args, $jsst_departments);
        }
        $jsst_priorities = self::excludedPriorities();
        if (!empty($jsst_priorities)) {
            $jsst_where[] = $jsst_alias . '.priorityid NOT IN (' . implode(',', array_fill(0, count($jsst_priorities), '%d')) . ')';
            $jsst_args = array_merge($jsst_args, $jsst_priorities);
        }
        return array('sql' => implode(' AND ', $jsst_where), 'args' => $jsst_args);
    }

    /* ------------------------------------------------------------------ *
     * The dry run (Roadmap 4.0-CORE-13)
     * ------------------------------------------------------------------ */

    /**
     * What a run would do, without doing any of it.
     *
     * Returns the ticket and attachment counts, the bytes the attachment purge
     * would free, the cutoff dates, the exclusions in force, and a sample of the
     * tickets that would go so an administrator can sanity-check the rule before
     * trusting it.
     */
    public function preview($jsst_samplesize = 10) {
        $jsst_report = array(
            'tickets'            => 0,
            'attachments'        => 0,
            'bytes'              => 0,
            'ticket_cutoff'      => '',
            'attachment_cutoff'  => '',
            'ticket_interval'    => self::ticketInterval(),
            'attachment_interval'=> self::attachmentInterval(),
            'excluded_departments' => self::excludedDepartments(),
            'excluded_priorities'  => self::excludedPriorities(),
            'sample'             => array(),
            'enabled'            => (self::ticketInterval() > 0 || self::attachmentInterval() > 0),
        );

        $jsst_ticketcutoff = self::cutoff(self::ticketInterval());
        if ($jsst_ticketcutoff !== '') {
            $jsst_report['ticket_cutoff'] = $jsst_ticketcutoff;
            $jsst_clause = $this->eligibilityClause($jsst_ticketcutoff);
            $jsst_report['tickets'] = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    WHERE " . $jsst_clause['sql'],
                $jsst_clause['args']
            ));
            $jsst_samplesize = (int) $jsst_samplesize;
            if ($jsst_samplesize > 0) {
                $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
                    "SELECT ticket.id, ticket.ticketid, ticket.subject, ticket.closed
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        WHERE " . $jsst_clause['sql'] . "
                        ORDER BY ticket.closed ASC
                        LIMIT %d",
                    array_merge($jsst_clause['args'], array($jsst_samplesize))
                ));
                $jsst_report['sample'] = is_array($jsst_rows) ? $jsst_rows : array();
            }
        }

        $jsst_attachmentcutoff = self::cutoff(self::attachmentInterval());
        if ($jsst_attachmentcutoff !== '') {
            $jsst_report['attachment_cutoff'] = $jsst_attachmentcutoff;
            $jsst_clause = $this->eligibilityClause($jsst_attachmentcutoff);
            $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                // filesize is stored in kilobytes, not bytes: uploads.php divides
                // the uploaded size by 1024 before saving it. Reporting the raw
                // column as bytes would understate the saving by 1024 times.
                "SELECT COUNT(a.id) AS total, COALESCE(SUM(a.filesize), 0) * 1024 AS bytes
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments` AS a
                    INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON a.ticketid = ticket.id
                    WHERE (a.deleted = 0 OR a.deleted IS NULL) AND " . $jsst_clause['sql'],
                $jsst_clause['args']
            ));
            if (!empty($jsst_row)) {
                $jsst_report['attachments'] = (int) $jsst_row->total;
                $jsst_report['bytes'] = (int) $jsst_row->bytes;
            }
        }

        return $jsst_report;
    }

    /**
     * A stored file size in bytes.
     *
     * The column holds kilobytes, as a varchar, and can be fractional.
     */
    public static function sizeToBytes($jsst_size) {
        $jsst_size = (float) $jsst_size;
        if ($jsst_size <= 0) {
            return 0;
        }
        return (int) round($jsst_size * 1024);
    }

    /**
     * The storage-savings figure, as a human-readable size.
     */
    public static function formatBytes($jsst_bytes) {
        $jsst_bytes = (float) $jsst_bytes;
        if ($jsst_bytes <= 0) {
            return '0 B';
        }
        $jsst_units = array('B', 'KB', 'MB', 'GB', 'TB');
        $jsst_power = (int) floor(log($jsst_bytes, 1024));
        if ($jsst_power >= count($jsst_units)) {
            $jsst_power = count($jsst_units) - 1;
        }
        $jsst_value = $jsst_bytes / pow(1024, $jsst_power);
        return round($jsst_value, ($jsst_power === 0) ? 0 : 1) . ' ' . $jsst_units[$jsst_power];
    }

    /* ------------------------------------------------------------------ *
     * The run
     * ------------------------------------------------------------------ */

    /**
     * Run the cleanup. Called from cron, and from the settings screen.
     *
     * @param bool $jsst_dryrun When true, nothing is deleted and the report is
     *                          returned instead.
     */
    function executeCleanupRoutines($jsst_dryrun = false) {
        if ($jsst_dryrun) {
            return $this->preview();
        }
        $jsst_result = array('attachments' => 0, 'bytes' => 0, 'tickets' => 0);
        $jsst_result['attachments'] = $this->purgeOldAttachments($jsst_bytes);
        $jsst_result['bytes'] = (int) $jsst_bytes;
        $jsst_result['tickets'] = $this->purgeOldTickets();

        if ($jsst_result['attachments'] > 0 || $jsst_result['tickets'] > 0) {
            self::recordRun($jsst_result);
        }
        return $jsst_result;
    }

    /**
     * Note what a run removed, so a deletion is never invisible after the fact.
     */
    private static function recordRun($jsst_result) {
        $jsst_message = sprintf(
            /* translators: 1: ticket count, 2: attachment count, 3: storage freed */
            esc_html(__('Retention cleanup removed %1$d tickets and %2$d attachments, freeing %3$s.', 'js-support-ticket')),
            (int) $jsst_result['tickets'],
            (int) $jsst_result['attachments'],
            self::formatBytes($jsst_result['bytes'])
        );
        $jsst_log = get_option('jsst_autocleanup_last_run', array());
        if (!is_array($jsst_log)) {
            $jsst_log = array();
        }
        $jsst_log = array(
            'when'        => date_i18n('Y-m-d H:i:s'),
            'tickets'     => (int) $jsst_result['tickets'],
            'attachments' => (int) $jsst_result['attachments'],
            'bytes'       => (int) $jsst_result['bytes'],
            'message'     => $jsst_message,
        );
        update_option('jsst_autocleanup_last_run', $jsst_log, false);
    }

    /**
     * What the last run did, for the settings screen.
     */
    public static function lastRun() {
        $jsst_log = get_option('jsst_autocleanup_last_run', array());
        return is_array($jsst_log) ? $jsst_log : array();
    }

    /**
     * Delete the files attached to long-closed tickets, and mark them deleted.
     *
     * @param int $jsst_bytes Set to the number of bytes freed.
     * @return int Attachments processed.
     */
    private function purgeOldAttachments(&$jsst_bytes = 0) {
        $jsst_bytes = 0;
        $jsst_cutoff = self::cutoff(self::attachmentInterval());
        if ($jsst_cutoff === '') {
            return 0;
        }
        $jsst_clause = $this->eligibilityClause($jsst_cutoff);

        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }

        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_maindir = wp_upload_dir();
        $jsst_base_path = $jsst_maindir['basedir'] . '/' . $jsst_datadirectory . '/attachmentdata/ticket/';
        $jsst_done = 0;

        // Ticket attachments.
        $jsst_attachments = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT a.id, a.filename, a.filesize, ticket.attachmentdir
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments` AS a
                INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON a.ticketid = ticket.id
                WHERE (a.deleted = 0 OR a.deleted IS NULL) AND " . $jsst_clause['sql'] . "
                LIMIT %d",
            array_merge($jsst_clause['args'], array(self::BATCH))
        ));
        if (is_array($jsst_attachments)) {
            foreach ($jsst_attachments as $jsst_attachment) {
                if ($this->deleteFile($jsst_base_path, $jsst_attachment->attachmentdir, $jsst_attachment->filename)) {
                    $jsst_bytes += self::sizeToBytes($jsst_attachment->filesize);
                }
                jssupportticket::$_db->query(jssupportticket::$_db->prepare(
                    "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_attachments` SET deleted = 1 WHERE id = %d",
                    $jsst_attachment->id
                ));
                $jsst_done++;
            }
        }

        // Note attachments, when notes exist at all.
        if (JSSTmergedaddon::featureEnabled('note')) {
            $jsst_notes = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
                "SELECT n.id, n.filename, n.filesize, ticket.attachmentdir
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_notes` AS n
                    INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON n.ticketid = ticket.id
                    WHERE n.filename <> '' AND (n.filedeleted = 0 OR n.filedeleted IS NULL) AND " . $jsst_clause['sql'] . "
                    LIMIT %d",
                array_merge($jsst_clause['args'], array(self::BATCH))
            ));
            if (is_array($jsst_notes)) {
                foreach ($jsst_notes as $jsst_note) {
                    if ($this->deleteFile($jsst_base_path, $jsst_note->attachmentdir, $jsst_note->filename)) {
                        $jsst_bytes += self::sizeToBytes($jsst_note->filesize);
                    }
                    jssupportticket::$_db->query(jssupportticket::$_db->prepare(
                        "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_notes` SET filedeleted = 1 WHERE id = %d",
                        $jsst_note->id
                    ));
                    $jsst_done++;
                }
            }
        }

        return $jsst_done;
    }

    /**
     * Delete one attachment file, refusing to step outside the attachment folder.
     */
    private function deleteFile($jsst_base_path, $jsst_folder, $jsst_filename) {
        global $wp_filesystem;
        $jsst_folder = jssupportticketphplib::JSST_basename((string) $jsst_folder);
        $jsst_filename = jssupportticketphplib::JSST_basename((string) $jsst_filename);
        if ($jsst_folder === '' || $jsst_filename === '') {
            return false;
        }
        $jsst_path = $jsst_base_path . $jsst_folder . '/' . $jsst_filename;
        if (empty($wp_filesystem) || !$wp_filesystem->exists($jsst_path)) {
            return false;
        }
        return (bool) $wp_filesystem->delete($jsst_path);
    }

    /**
     * Delete long-closed tickets, through the ordinary ticket deletion so that
     * replies, notes, attachments and history go with them.
     *
     * @return int Tickets deleted.
     */
    private function purgeOldTickets() {
        $jsst_cutoff = self::cutoff(self::ticketInterval());
        if ($jsst_cutoff === '') {
            return 0;
        }
        $jsst_clause = $this->eligibilityClause($jsst_cutoff);
        $jsst_ids = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
            "SELECT ticket.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                WHERE " . $jsst_clause['sql'] . "
                ORDER BY ticket.closed ASC
                LIMIT %d",
            array_merge($jsst_clause['args'], array(self::BATCH))
        ));
        if (empty($jsst_ids)) {
            return 0;
        }
        $jsst_ticketmodel = JSSTincluder::getJSModel('ticket');
        $jsst_done = 0;
        foreach ($jsst_ids as $jsst_id) {
            $jsst_ticketmodel->removeEnforceTicket((int) $jsst_id);
            $jsst_done++;
        }
        return $jsst_done;
    }

}
