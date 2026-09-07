<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTmigrationvalidator')) {
    return;
}

/**
 * Checking an import after it has finished. (Roadmap 4.0-DATA-01)
 *
 * The importer reports what it believes it did. This checks the site to see
 * whether that is true, which is a different question and the only one worth
 * answering — the decision these findings feed is whether the old help desk can
 * be deleted, and that decision cannot be taken on the word of the process whose
 * work is being judged.
 *
 * Six checks, each answering something somebody would otherwise find out months
 * later: did as much arrive as was promised, does a sample of it read correctly,
 * are the attachment files actually on disk, did the customers come across as
 * customers, are the dates real, and do the tickets point at statuses that
 * exist.
 *
 * Every finding is one of three levels. `bad` means data is missing or wrong and
 * the old help desk should not be deleted. `warn` means something is worth
 * looking at but may be perfectly correct for this site. `ok` means checked and
 * fine — reported rather than omitted, because a list that only shows problems
 * cannot be distinguished from a list that failed to run.
 */
class JSSTmigrationvalidator {

    /** Tickets read back in the sample check. */
    const SAMPLE = 20;

    /** Attachment rows whose files are looked for. */
    const FILE_SAMPLE = 50;

    /**
     * Run every check for one finished migration.
     *
     * @return array findings of level, label, detail
     */
    public static function validate($jsst_token, $jsst_source) {
        $jsst_migration = JSSTmigration::get($jsst_token);
        if (!$jsst_migration) {
            return array(self::finding('bad',
                __('Checks could not run', 'js-support-ticket'),
                __('That migration cannot be found, so nothing could be checked against it.', 'js-support-ticket')
            ));
        }

        $jsst_journal = JSSTmigration::journalSummary($jsst_token);
        $jsst_id = (int) $jsst_migration->id;
        $jsst_findings = array();
        $jsst_findings[] = self::checkCounts($jsst_token, $jsst_source, $jsst_journal);
        $jsst_findings[] = self::checkSample($jsst_id);
        $jsst_findings[] = self::checkAttachmentFiles($jsst_id);
        $jsst_findings[] = self::checkCustomers($jsst_id);
        $jsst_findings[] = self::checkDates($jsst_id);
        $jsst_findings[] = self::checkStatuses($jsst_id);

        return apply_filters('jsst_migration_findings', array_values(array_filter($jsst_findings)), $jsst_token, $jsst_source);
    }

    /* ------------------------------------------------------------------ *
     * The checks
     * ------------------------------------------------------------------ */

    /**
     * Did as many tickets arrive as the source said it had?
     *
     * Counted from the journal rather than from the tickets table, which on a
     * site that was already running holds tickets this import had nothing to do
     * with. Fewer than expected is the finding that matters; more is reported as
     * a note, because the commonest cause is a second run that was meant to
     * resume and instead added to the total.
     */
    private static function checkCounts($jsst_token, $jsst_source, $jsst_journal) {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_tickets';
        $jsst_created = isset($jsst_journal[$jsst_table]) ? (int) $jsst_journal[$jsst_table] : 0;

        $jsst_cursor = JSSTmigration::cursor($jsst_token);
        $jsst_expected = isset($jsst_cursor['expected_tickets']) ? (int) $jsst_cursor['expected_tickets'] : 0;
        if ($jsst_expected < 1) {
            // No count was taken when the run started, so there is nothing to
            // compare against. Saying so beats inventing a comparison.
            return self::finding('warn',
                __('Ticket count', 'js-support-ticket'),
                sprintf(
                    /* translators: %s: number of tickets created */
                    __('%s tickets were created. The source was not counted when this run started, so there is nothing to compare that against.', 'js-support-ticket'),
                    number_format_i18n($jsst_created)
                )
            );
        }

        if ($jsst_created >= $jsst_expected) {
            return self::finding($jsst_created > $jsst_expected ? 'warn' : 'ok',
                __('Ticket count', 'js-support-ticket'),
                sprintf(
                    /* translators: 1: tickets created, 2: tickets counted in the source */
                    __('%1$s tickets created against %2$s counted in the source.', 'js-support-ticket'),
                    number_format_i18n($jsst_created),
                    number_format_i18n($jsst_expected)
                ) . ($jsst_created > $jsst_expected
                    ? ' ' . __('More arrived than the source held, which usually means this ran twice.', 'js-support-ticket')
                    : '')
            );
        }

        return self::finding('bad',
            __('Ticket count', 'js-support-ticket'),
            sprintf(
                /* translators: 1: tickets created, 2: tickets counted in the source, 3: the shortfall */
                __('%1$s tickets created against %2$s counted in the source — %3$s did not arrive. Do not delete the old help desk.', 'js-support-ticket'),
                number_format_i18n($jsst_created),
                number_format_i18n($jsst_expected),
                number_format_i18n($jsst_expected - $jsst_created)
            )
        );
    }

    /**
     * Read some of them back.
     *
     * A count can be right while the rows are empty — a mapping that put the
     * message in the wrong column produces exactly that. This opens a sample and
     * checks the two fields no ticket can usefully be missing.
     */
    private static function checkSample($jsst_id) {
        $jsst_ids = self::journalIds($jsst_id, 'js_ticket_tickets', self::SAMPLE);
        if (empty($jsst_ids)) {
            return self::finding('warn',
                __('Sample check', 'js-support-ticket'),
                __('No tickets were created by this run, so none could be read back.', 'js-support-ticket')
            );
        }
        $jsst_empty = (int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`
                WHERE id IN (" . implode(',', $jsst_ids) . ")
                AND (subject IS NULL OR subject = '' OR message IS NULL OR message = '')"
        );
        if ($jsst_empty > 0) {
            return self::finding('bad',
                __('Sample check', 'js-support-ticket'),
                sprintf(
                    /* translators: 1: tickets with empty fields, 2: tickets sampled */
                    __('%1$s of %2$s sampled tickets have no subject or no message. The source may be a version this mapping does not read.', 'js-support-ticket'),
                    number_format_i18n($jsst_empty),
                    number_format_i18n(count($jsst_ids))
                )
            );
        }
        return self::finding('ok',
            __('Sample check', 'js-support-ticket'),
            sprintf(
                /* translators: %s: number of tickets read back */
                __('%s imported tickets were read back and all have a subject and a message.', 'js-support-ticket'),
                number_format_i18n(count($jsst_ids))
            )
        );
    }

    /**
     * Are the attachment files actually there?
     *
     * The row being imported says nothing about whether the file came with it.
     * This is the check that most often finds something, because attachments
     * live outside the database and a migration that copied rows without copying
     * the uploads directory looks completely successful until somebody clicks
     * one.
     */
    private static function checkAttachmentFiles($jsst_id) {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_attachments';
        if (!JSSTmigration::tableExists($jsst_table)) {
            return null;
        }
        $jsst_ids = self::journalIds($jsst_id, 'js_ticket_attachments', self::FILE_SAMPLE);
        if (empty($jsst_ids)) {
            return self::finding('ok',
                __('Attachment files', 'js-support-ticket'),
                __('This import created no attachments, so there are no files to look for.', 'js-support-ticket')
            );
        }

        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT id, ticketid, filename FROM `" . $jsst_table . "` WHERE id IN (" . implode(',', $jsst_ids) . ")"
        );
        $jsst_uploads = wp_upload_dir();
        $jsst_base = trailingslashit($jsst_uploads['basedir']) . jssupportticket::$_config['data_directory'] . '/attachmentdata/ticket/';

        $jsst_missing = 0;
        foreach ((array) $jsst_rows as $jsst_row) {
            if ((string) $jsst_row->filename === '') {
                continue;
            }
            $jsst_path = $jsst_base . $jsst_row->ticketid . '/' . $jsst_row->filename;
            if (!file_exists($jsst_path)) {
                $jsst_missing++;
            }
        }
        if ($jsst_missing > 0) {
            return self::finding('bad',
                __('Attachment files', 'js-support-ticket'),
                sprintf(
                    /* translators: 1: files not found, 2: attachments checked */
                    __('%1$s of %2$s checked attachments have no file on disk. The rows came across but the files did not — copy the old uploads directory before deleting anything.', 'js-support-ticket'),
                    number_format_i18n($jsst_missing),
                    number_format_i18n(count((array) $jsst_rows))
                )
            );
        }
        return self::finding('ok',
            __('Attachment files', 'js-support-ticket'),
            sprintf(
                /* translators: %s: number of attachments checked */
                __('%s imported attachments were checked and every file is on disk.', 'js-support-ticket'),
                number_format_i18n(count((array) $jsst_rows))
            )
        );
    }

    /**
     * Did the tickets land against real customers?
     *
     * A ticket with no user behind it still works — that is what an emailed
     * ticket from a stranger looks like — so this is a warning rather than a
     * failure. It matters because a customer who cannot see their own history
     * after a migration will say so, loudly, and it is much cheaper to know now.
     */
    private static function checkCustomers($jsst_id) {
        $jsst_ids = self::journalIds($jsst_id, 'js_ticket_tickets', 0);
        if (empty($jsst_ids)) {
            return null;
        }
        $jsst_orphans = (int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`
                WHERE id IN (" . implode(',', $jsst_ids) . ") AND (uid IS NULL OR uid = 0)"
        );
        if ($jsst_orphans > 0) {
            return self::finding('warn',
                __('Customer mapping', 'js-support-ticket'),
                sprintf(
                    /* translators: 1: tickets with no account, 2: tickets imported */
                    __('%1$s of %2$s imported tickets are not attached to a WordPress account. Those customers cannot see their own history until an account with the same address exists.', 'js-support-ticket'),
                    number_format_i18n($jsst_orphans),
                    number_format_i18n(count($jsst_ids))
                )
            );
        }
        return self::finding('ok',
            __('Customer mapping', 'js-support-ticket'),
            __('Every imported ticket is attached to a customer account.', 'js-support-ticket')
        );
    }

    /**
     * Are the dates real?
     *
     * A date that did not convert lands as the zero date, which sorts to the
     * beginning of time and quietly ruins every report that groups by month.
     */
    private static function checkDates($jsst_id) {
        $jsst_ids = self::journalIds($jsst_id, 'js_ticket_tickets', 0);
        if (empty($jsst_ids)) {
            return null;
        }
        $jsst_bad = (int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`
                WHERE id IN (" . implode(',', $jsst_ids) . ")
                AND (created IS NULL OR created = '0000-00-00 00:00:00')"
        );
        if ($jsst_bad > 0) {
            return self::finding('bad',
                __('Dates', 'js-support-ticket'),
                sprintf(
                    /* translators: 1: tickets with no date, 2: tickets imported */
                    __('%1$s of %2$s imported tickets have no created date. Reports group by date, so those tickets will not appear where they belong.', 'js-support-ticket'),
                    number_format_i18n($jsst_bad),
                    number_format_i18n(count($jsst_ids))
                )
            );
        }
        return self::finding('ok',
            __('Dates', 'js-support-ticket'),
            __('Every imported ticket has a created date.', 'js-support-ticket')
        );
    }

    /**
     * Do the tickets point at statuses that exist?
     *
     * A status id that was not mapped leaves a ticket in a state no screen has a
     * name for, which shows up as a blank column and a ticket that appears in no
     * queue at all.
     */
    private static function checkStatuses($jsst_id) {
        $jsst_ids = self::journalIds($jsst_id, 'js_ticket_tickets', 0);
        if (empty($jsst_ids)) {
            return null;
        }
        $jsst_p = jssupportticket::$_db->prefix;
        $jsst_orphans = (int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . $jsst_p . "js_ticket_tickets` t
                LEFT JOIN `" . $jsst_p . "js_ticket_statuses` s ON s.id = t.status
                WHERE t.id IN (" . implode(',', $jsst_ids) . ") AND s.id IS NULL"
        );
        if ($jsst_orphans > 0) {
            return self::finding('bad',
                __('Ticket statuses', 'js-support-ticket'),
                sprintf(
                    /* translators: 1: tickets with an unknown status, 2: tickets imported */
                    __('%1$s of %2$s imported tickets have a status that does not exist here. They will not appear in any queue until the status is created or the tickets are moved.', 'js-support-ticket'),
                    number_format_i18n($jsst_orphans),
                    number_format_i18n(count($jsst_ids))
                )
            );
        }
        return self::finding('ok',
            __('Ticket statuses', 'js-support-ticket'),
            __('Every imported ticket has a status this site knows about.', 'js-support-ticket')
        );
    }

    /* ------------------------------------------------------------------ *
     * Helpers
     * ------------------------------------------------------------------ */

    /**
     * The ids this migration created in one table.
     *
     * Read from the journal, which is the only record of what belonged to this
     * run — a query against the table itself cannot tell an imported ticket from
     * one an agent raised while the import was running.
     */
    private static function journalIds($jsst_id, $jsst_shortname, $jsst_limit) {
        $jsst_table = jssupportticket::$_db->prefix . $jsst_shortname;
        // Scoped to this migration as well as this table: the journal holds every
        // run the site has ever done, and checking one import against another
        // one's rows would be worse than not checking at all.
        $jsst_sql = "SELECT rowid FROM `" . JSSTmigration::journalTable() . "`
                        WHERE migrationid = %d AND tablename = %s ORDER BY id DESC";
        if ($jsst_limit > 0) {
            $jsst_sql .= ' LIMIT ' . (int) $jsst_limit;
        }
        $jsst_ids = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare($jsst_sql, (int) $jsst_id, $jsst_table));
        return array_map('intval', (array) $jsst_ids);
    }

    private static function finding($jsst_level, $jsst_label, $jsst_detail) {
        return array(
            'level'  => $jsst_level,
            'label'  => esc_html($jsst_label),
            'detail' => esc_html($jsst_detail),
        );
    }

}
