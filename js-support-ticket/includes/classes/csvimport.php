<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTcsvimport')) {
    return;
}

/**
 * Tickets in from a CSV file. (Roadmap 4.0-DATA-02)
 *
 * The export has been able to get data out since 4.0-CORE-10; this is the other
 * half, and the reason it is worth having is not really the feature. "Can I get
 * my data in and out" is asked before anyone buys, and the honest answer has to
 * be a format somebody can look at, not an invitation to send us a spreadsheet
 * and hope.
 *
 * So the format is the deliverable. It is declared once, in columns() below, and
 * everything else is generated from that declaration: the documentation on the
 * screen, the downloadable template, the header matching, and the validation.
 * There is no second copy of the format to fall out of step with this one — a
 * column added here appears in the docs and the template without anybody
 * remembering to update them.
 *
 * Two decisions shape the rest:
 *
 * A file is checked completely before a single row is written. An importer that
 * writes as it reads leaves a spreadsheet half in on the first bad row, and the
 * person holding it then has to work out which half. Every problem is collected
 * with its line number and shown together, and nothing is created until somebody
 * has read that list and said go.
 *
 * The import runs as a migration. Not a metaphor — it opens a real one, so every
 * row it creates is journalled through the same choke point the help-desk
 * importers use, and the existing report and rollback screens work on it without
 * knowing a CSV was involved. A file imported by mistake comes back out in one
 * action. (Roadmap 4.0-DATA-01)
 */
class JSSTcsvimport {

    /** Rows one file may carry. Beyond this it is a database job, not a spreadsheet. */
    const MAX_ROWS = 5000;

    /** How long a checked file waits for somebody to confirm it. */
    const HOLD = 3600;

    /* ------------------------------------------------------------------ *
     * The format
     * ------------------------------------------------------------------ */

    /**
     * The documented import format.
     *
     * `key` is what a header must say. `also` lists the export's own English
     * column headings, so a file this plugin exported can be sent back in
     * without being edited first — the export writes headings for people to
     * read, and those are translated, which is exactly why the format itself is
     * keyed on something that never changes.
     */
    public static function columns() {
        $jsst_columns = array(
            'subject' => array(
                'label'    => esc_html(__('Subject', 'js-support-ticket')),
                'also'     => array('subject'),
                'required' => true,
                'notes'    => esc_html(__('The ticket title. Required, and the one column with no sensible default.', 'js-support-ticket')),
                'example'  => 'Printer will not connect over wifi',
            ),
            'message' => array(
                'label'    => esc_html(__('Message', 'js-support-ticket')),
                'also'     => array('message'),
                'required' => true,
                'notes'    => esc_html(__('The first message on the ticket, as plain text. Line breaks inside a quoted cell are kept.', 'js-support-ticket')),
                'example'  => "It worked yesterday and stopped after the update.",
            ),
            'email' => array(
                'label'    => esc_html(__('Requester Email', 'js-support-ticket')),
                'also'     => array('requester email', 'email'),
                'required' => true,
                'notes'    => esc_html(__('Who raised it. If an account with this address exists the ticket is attached to it; if not the ticket is created against the address alone, exactly as an emailed ticket from a stranger would be. No account is created.', 'js-support-ticket')),
                'example'  => 'sam@example.com',
            ),
            'name' => array(
                'label'    => esc_html(__('Requester Name', 'js-support-ticket')),
                'also'     => array('requester name', 'name'),
                'required' => false,
                'notes'    => esc_html(__('Shown on the ticket. Taken from the matched account when left empty.', 'js-support-ticket')),
                'example'  => 'Sam Okafor',
            ),
            'status' => array(
                'label'    => esc_html(__('Status', 'js-support-ticket')),
                'also'     => array('status'),
                'required' => false,
                'notes'    => esc_html(__('Matched against your ticket statuses by name, ignoring case. Left empty, the ticket comes in as new.', 'js-support-ticket')),
                'example'  => 'Open',
            ),
            'priority' => array(
                'label'    => esc_html(__('Priority', 'js-support-ticket')),
                'also'     => array('priority'),
                'required' => false,
                'notes'    => esc_html(__('Matched against your priorities by name, ignoring case. Left empty, your default priority is used.', 'js-support-ticket')),
                'example'  => 'Medium',
            ),
            'department' => array(
                'label'    => esc_html(__('Department', 'js-support-ticket')),
                'also'     => array('department'),
                'required' => false,
                'notes'    => esc_html(__('Matched against your departments by name, ignoring case. Left empty, your default department is used.', 'js-support-ticket')),
                'example'  => 'Support',
            ),
            'created' => array(
                'label'    => esc_html(__('Created', 'js-support-ticket')),
                'also'     => array('created'),
                'required' => false,
                'notes'    => esc_html(__('When the ticket was raised, as YYYY-MM-DD or YYYY-MM-DD HH:MM:SS. Left empty, the time of the import is used — which is worth filling in, because reports read this column.', 'js-support-ticket')),
                'example'  => '2026-03-14 09:20:00',
            ),
            'phone' => array(
                'label'    => esc_html(__('Requester Phone', 'js-support-ticket')),
                'also'     => array('requester phone', 'phone'),
                'required' => false,
                'notes'    => esc_html(__('Optional, stored as written.', 'js-support-ticket')),
                'example'  => '',
            ),
        );
        return apply_filters('jsst_csv_import_columns', $jsst_columns);
    }

    /**
     * What the format does not carry, said out loud.
     *
     * The export writes reply and attachment counts rather than the replies
     * themselves, so a file that came out of it cannot put them back. Somebody
     * about to move a help desk needs to know that before they start, not after.
     */
    public static function limitations() {
        return array(
            esc_html(__('Replies are not imported. A row becomes a ticket with its first message and nothing else, so an exported file put back in returns the tickets without their conversations.', 'js-support-ticket')),
            esc_html(__('Attachments are not imported. Files are not in the CSV and cannot be, so any attachment count in an exported file is ignored.', 'js-support-ticket')),
            esc_html(__('Accounts are never created. A row whose address has no account becomes a ticket against that address.', 'js-support-ticket')),
            esc_html(__('Your own reference numbers are not carried. A ticket here has a generated reference and a numeric ticket number, and no field to hold somebody else\'s — a column that quietly went nowhere would be worse than not offering one.', 'js-support-ticket')),
            esc_html(__('Nothing is emailed. Importing a thousand tickets does not notify a thousand customers about them.', 'js-support-ticket')),
            esc_html(__('Moving from another help desk? Use Import Data instead — it brings replies, attachments and customers across. This is for spreadsheets.', 'js-support-ticket')),
        );
    }

    /** The template file, generated from the format so it cannot disagree with it. */
    public static function template() {
        $jsst_header = array();
        $jsst_example = array();
        foreach (self::columns() as $jsst_key => $jsst_column) {
            $jsst_header[] = $jsst_key;
            $jsst_example[] = $jsst_column['example'];
        }
        return array($jsst_header, $jsst_example);
    }

    /* ------------------------------------------------------------------ *
     * Reading a file
     * ------------------------------------------------------------------ */

    /**
     * Read and check a file without writing anything.
     *
     * Returns the rows it would create and every problem it found, each with the
     * line number it is on — a spreadsheet is edited by line number, so an error
     * that does not carry one is an error somebody has to go hunting for.
     */
    public static function check($jsst_path) {
        if (!is_readable($jsst_path)) {
            return new WP_Error('jsst_csv_unreadable', esc_html(__('That file could not be read.', 'js-support-ticket')));
        }
        $jsst_handle = fopen($jsst_path, 'r');
        if (!$jsst_handle) {
            return new WP_Error('jsst_csv_unreadable', esc_html(__('That file could not be opened.', 'js-support-ticket')));
        }

        $jsst_headers = fgetcsv($jsst_handle);
        if (!$jsst_headers) {
            fclose($jsst_handle);
            return new WP_Error('jsst_csv_empty', esc_html(__('That file has no header row.', 'js-support-ticket')));
        }
        // A file saved by a spreadsheet often opens with a byte order mark, which
        // otherwise turns the first column name into something that matches
        // nothing and produces "subject is missing" on a file that has one.
        if (isset($jsst_headers[0])) {
            $jsst_headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $jsst_headers[0]);
        }

        $jsst_map = self::mapHeaders($jsst_headers);
        if (is_wp_error($jsst_map)) {
            fclose($jsst_handle);
            return $jsst_map;
        }

        $jsst_lookups = self::lookups();
        $jsst_rows = array();
        $jsst_problems = array();
        $jsst_line = 1;

        while (($jsst_cells = fgetcsv($jsst_handle)) !== false) {
            $jsst_line++;
            // A trailing newline reads as one empty cell; not an error, just the
            // end of the file.
            if (count($jsst_cells) === 1 && trim((string) $jsst_cells[0]) === '') {
                continue;
            }
            if (count($jsst_rows) >= self::MAX_ROWS) {
                $jsst_problems[] = array('line' => $jsst_line, 'why' => sprintf(
                    /* translators: %s: the largest number of rows one file may carry */
                    __('This file has more than %s rows. Split it and import the parts.', 'js-support-ticket'),
                    number_format_i18n(self::MAX_ROWS)
                ));
                break;
            }

            $jsst_row = self::readRow($jsst_cells, $jsst_map, $jsst_lookups, $jsst_line, $jsst_problems);
            if ($jsst_row !== null) {
                $jsst_rows[] = $jsst_row;
            }
        }
        fclose($jsst_handle);

        if (empty($jsst_rows) && empty($jsst_problems)) {
            return new WP_Error('jsst_csv_norows', esc_html(__('That file has a header row and nothing under it.', 'js-support-ticket')));
        }
        return array('rows' => $jsst_rows, 'problems' => $jsst_problems);
    }

    /**
     * Work out which column is which.
     *
     * Matched on the format's own key first, then on the export's English
     * headings, and case and surrounding spaces are ignored throughout — a
     * header row typed by a person is not going to match on the first try
     * otherwise, and refusing it teaches nothing.
     */
    private static function mapHeaders($jsst_headers) {
        $jsst_columns = self::columns();
        $jsst_map = array();
        foreach ($jsst_headers as $jsst_index => $jsst_heading) {
            $jsst_heading = strtolower(trim((string) $jsst_heading));
            if ($jsst_heading === '') {
                continue;
            }
            foreach ($jsst_columns as $jsst_key => $jsst_column) {
                $jsst_accepts = array_map('strtolower', array_merge(array($jsst_key), $jsst_column['also'], array($jsst_column['label'])));
                if (in_array($jsst_heading, $jsst_accepts, true)) {
                    $jsst_map[$jsst_key] = $jsst_index;
                    break;
                }
            }
        }

        $jsst_missing = array();
        foreach ($jsst_columns as $jsst_key => $jsst_column) {
            if (!empty($jsst_column['required']) && !isset($jsst_map[$jsst_key])) {
                $jsst_missing[] = $jsst_key;
            }
        }
        if (!empty($jsst_missing)) {
            return new WP_Error('jsst_csv_headers', sprintf(
                /* translators: %s: comma separated list of column names */
                __('The file is missing these required columns: %s. Download the template to see the format.', 'js-support-ticket'),
                implode(', ', $jsst_missing)
            ));
        }
        return $jsst_map;
    }

    /** One line, checked. Returns the row to create, or null if it cannot be. */
    private static function readRow($jsst_cells, $jsst_map, $jsst_lookups, $jsst_line, &$jsst_problems) {
        $jsst_get = function ($jsst_key) use ($jsst_cells, $jsst_map) {
            if (!isset($jsst_map[$jsst_key]) || !isset($jsst_cells[$jsst_map[$jsst_key]])) {
                return '';
            }
            return trim((string) $jsst_cells[$jsst_map[$jsst_key]]);
        };

        $jsst_subject = $jsst_get('subject');
        $jsst_message = $jsst_get('message');
        $jsst_email   = $jsst_get('email');
        $jsst_bad = false;

        foreach (array('subject' => $jsst_subject, 'message' => $jsst_message, 'email' => $jsst_email) as $jsst_key => $jsst_value) {
            if ($jsst_value === '') {
                $jsst_problems[] = array('line' => $jsst_line, 'why' => sprintf(
                    /* translators: %s: the name of a required column */
                    __('%s is empty, and it is required.', 'js-support-ticket'),
                    $jsst_key
                ));
                $jsst_bad = true;
            }
        }
        if ($jsst_email !== '' && !is_email($jsst_email)) {
            $jsst_problems[] = array('line' => $jsst_line, 'why' => sprintf(
                /* translators: %s: the address as written in the file */
                __('"%s" is not an email address.', 'js-support-ticket'),
                $jsst_email
            ));
            $jsst_bad = true;
        }

        /* A name that matches nothing is a problem rather than a silent default.
           Importing five hundred tickets into the wrong department because a
           spreadsheet said "Suport" is the kind of mistake that is only noticed
           much later. */
        $jsst_resolved = array();
        foreach (array('status', 'priority', 'department') as $jsst_field) {
            $jsst_value = $jsst_get($jsst_field);
            if ($jsst_value === '') {
                $jsst_resolved[$jsst_field] = 0;
                continue;
            }
            $jsst_key = strtolower($jsst_value);
            if (!isset($jsst_lookups[$jsst_field][$jsst_key])) {
                $jsst_problems[] = array('line' => $jsst_line, 'why' => sprintf(
                    /* translators: 1: the value in the file, 2: the column it is in */
                    __('"%1$s" is not one of your %2$s names.', 'js-support-ticket'),
                    $jsst_value,
                    $jsst_field
                ));
                $jsst_bad = true;
                continue;
            }
            $jsst_resolved[$jsst_field] = (int) $jsst_lookups[$jsst_field][$jsst_key];
        }

        $jsst_created = $jsst_get('created');
        if ($jsst_created !== '') {
            $jsst_stamp = strtotime($jsst_created);
            if ($jsst_stamp === false) {
                $jsst_problems[] = array('line' => $jsst_line, 'why' => sprintf(
                    /* translators: %s: the date as written in the file */
                    __('"%s" is not a date this can read. Use YYYY-MM-DD or YYYY-MM-DD HH:MM:SS.', 'js-support-ticket'),
                    $jsst_created
                ));
                $jsst_bad = true;
            } else {
                $jsst_created = gmdate('Y-m-d H:i:s', $jsst_stamp);
            }
        }

        if ($jsst_bad) {
            return null;
        }
        return array(
            'line'       => $jsst_line,
            'subject'    => $jsst_subject,
            'message'    => $jsst_message,
            'email'      => $jsst_email,
            'name'       => $jsst_get('name'),
            'phone'      => $jsst_get('phone'),
            'created'    => ($jsst_created !== '') ? $jsst_created : current_time('mysql'),
            'status'     => $jsst_resolved['status'],
            'priority'   => $jsst_resolved['priority'],
            'department' => $jsst_resolved['department'],
        );
    }

    /**
     * The names a file may refer to, lowercased, read once for the whole file.
     *
     * Per row this would be three queries times five thousand rows, which is the
     * difference between an import that finishes and one that times out.
     */
    private static function lookups() {
        $jsst_prefix = jssupportticket::$_db->prefix . 'js_ticket_';
        $jsst_lookups = array('status' => array(), 'priority' => array(), 'department' => array());

        foreach (array(
            'status'     => array('statuses', 'status'),
            'priority'   => array('priorities', 'priority'),
            'department' => array('departments', 'departmentname'),
        ) as $jsst_field => $jsst_source) {
            $jsst_rows = jssupportticket::$_db->get_results(
                "SELECT id, `" . esc_sql($jsst_source[1]) . "` AS title FROM `" . $jsst_prefix . esc_sql($jsst_source[0]) . "`"
            );
            foreach ((array) $jsst_rows as $jsst_row) {
                $jsst_lookups[$jsst_field][strtolower(trim($jsst_row->title))] = (int) $jsst_row->id;
            }
        }
        return $jsst_lookups;
    }

    /* ------------------------------------------------------------------ *
     * Holding a checked file
     * ------------------------------------------------------------------ */

    /**
     * Keep a checked file until somebody confirms it.
     *
     * In a transient rather than on disk: it expires on its own, so a file
     * somebody uploaded, thought better of and closed the tab on does not sit in
     * the uploads directory with a customer's tickets in it forever.
     */
    public static function hold($jsst_result) {
        $jsst_token = wp_generate_password(16, false, false);
        set_transient('jsst_csv_' . $jsst_token, $jsst_result, self::HOLD);
        return $jsst_token;
    }

    public static function held($jsst_token) {
        $jsst_result = get_transient('jsst_csv_' . sanitize_text_field($jsst_token));
        return is_array($jsst_result) ? $jsst_result : null;
    }

    public static function release($jsst_token) {
        delete_transient('jsst_csv_' . sanitize_text_field($jsst_token));
    }

    /* ------------------------------------------------------------------ *
     * Writing it
     * ------------------------------------------------------------------ */

    /**
     * Create a ticket for every row that passed.
     *
     * Wrapped in a migration so the rows are journalled and the existing report
     * and rollback screens work on the result. Recording is switched off again
     * whatever happens, because leaving it on would attribute every subsequent
     * insert on the site — an agent's reply, a new ticket from a customer — to
     * this import, and a later rollback would take them away.
     */
    public static function import($jsst_rows) {
        if (!current_user_can('manage_options')) {
            return new WP_Error('jsst_csv_denied', esc_html(__('You do not have permission to import.', 'js-support-ticket')));
        }
        if (empty($jsst_rows)) {
            return new WP_Error('jsst_csv_norows', esc_html(__('There is nothing to import.', 'js-support-ticket')));
        }

        $jsst_token = JSSTmigration::start('csv', '');
        JSSTmigration::startRecording($jsst_token);

        $jsst_made = 0;
        $jsst_failed = 0;
        try {
            foreach ($jsst_rows as $jsst_row) {
                if (self::createTicket($jsst_row)) {
                    $jsst_made++;
                } else {
                    $jsst_failed++;
                }
            }
        } catch (Exception $jsst_e) {
            JSSTmigration::stopRecording();
            JSSTmigration::setStatus($jsst_token, 'failed', $jsst_e->getMessage());
            return new WP_Error('jsst_csv_failed', $jsst_e->getMessage());
        }
        JSSTmigration::stopRecording();

        JSSTmigration::setCounts($jsst_token, array(
            'ticket' => array('imported' => $jsst_made, 'skipped' => 0, 'failed' => $jsst_failed),
        ));
        JSSTmigration::setStatus($jsst_token, 'complete');
        return array('token' => $jsst_token, 'made' => $jsst_made, 'failed' => $jsst_failed);
    }

    /** One row as a ticket. */
    private static function createTicket($jsst_row) {
        $jsst_prefix = jssupportticket::$_db->prefix . 'js_ticket_';

        // An address with an account behind it becomes that customer's ticket;
        // one without becomes a ticket against the address, which is what an
        // emailed ticket from a stranger already does.
        $jsst_user = get_user_by('email', $jsst_row['email']);

        $jsst_name = ($jsst_row['name'] !== '') ? $jsst_row['name'] : ($jsst_user ? $jsst_user->display_name : $jsst_row['email']);

        $jsst_uid = self::requesterUid($jsst_user, $jsst_name);

        /* The reference a customer quotes, from the plugin's own generator rather
           than a counter of our own: a site may be set to random or sequential
           ids, with its own prefix, suffix and padding, and an importer that
           invented its own numbering would produce tickets that do not look like
           the ones beside them. */
        $jsst_ids = JSSTincluder::getJSModel('ticket')->getRandomTicketId();

        $jsst_data = array(
            'id'                => '',
            'uid'               => $jsst_uid,
            'ticketid'          => $jsst_ids['ticketid'],
            'departmentid'      => $jsst_row['department'] > 0 ? $jsst_row['department'] : self::defaultId('departments'),
            'priorityid'        => $jsst_row['priority'] > 0 ? $jsst_row['priority'] : self::defaultId('priorities'),
            'staffid'           => 0,
            'email'             => $jsst_row['email'],
            'name'              => $jsst_name,
            'subject'           => $jsst_row['subject'],
            'message'           => $jsst_row['message'],
            'helptopicid'       => 0,
            'multiformid'       => 1,
            'phone'             => $jsst_row['phone'],
            'phoneext'          => '',
            'status'            => $jsst_row['status'] > 0 ? $jsst_row['status'] : 1,
            'isoverdue'         => 0,
            'isanswered'        => 0,
            'duedate'           => '0000-00-00 00:00:00',
            'reopened'          => '0000-00-00 00:00:00',
            'closed'            => '0000-00-00 00:00:00',
            'closedby'          => 0,
            'lastreply'         => $jsst_row['created'],
            'created'           => $jsst_row['created'],
            'updated'           => $jsst_row['created'],
            'lock'              => 0,
            'ticketviaemail'    => 0,
            'ticketviaemail_id' => 0,
            'attachmentdir'     => JSSTincluder::getJSModel('ticket')->getRandomFolderName(),
            'feedbackemail'     => 0,
            'mergestatus'       => 0,
            'mergewith'         => 0,
            'mergenote'         => '',
            'mergedate'         => '0000-00-00 00:00:00',
            'multimergeparams'  => '',
            'mergeuid'          => 0,
            'params'            => '',
            'hash'              => '',
            'notificationid'    => 0,
            'customticketno'    => $jsst_ids['customticketno'],
        );

        $jsst_table = JSSTincluder::getJSTable('tickets');
        if (!$jsst_table->bind($jsst_data)) {
            return false;
        }
        if (!$jsst_table->store()) {
            return false;
        }

        // The hash is what a customer's own link to the ticket is built from, so
        // an imported ticket without one is a ticket nobody can be sent to.
        $jsst_hash = JSSTincluder::getJSModel('ticket')->generateHash($jsst_table->id);
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . $jsst_prefix . "tickets` SET `hash` = %s WHERE id = %d",
            $jsst_hash,
            (int) $jsst_table->id
        ));
        return true;
    }

    private static function requesterUid($jsst_user, $jsst_name) {
        if (empty($jsst_user) || (int) $jsst_user->ID <= 0) {
            return 0;
        }
        $jsst_wpuid = (int) $jsst_user->ID;

        $jsst_uid = (int) JSSTincluder::getObjectClass('user')->getUserIDByWPUid($jsst_wpuid);
        if ($jsst_uid > 0) {
            return $jsst_uid;
        }

        $jsst_table = JSSTincluder::getJSTable('users');
        $jsst_data = array(
            'id'            => '',
            'wpuid'         => $jsst_wpuid,
            'name'          => $jsst_name,
            'display_name'  => $jsst_user->display_name,
            'user_nicename' => $jsst_user->user_nicename,
            'user_email'    => $jsst_user->user_email,
            'status'        => 1,
            'issocial'      => 0,
            'socialid'      => '',
            'created'       => date_i18n('Y-m-d H:i:s'),
            'autogenerated' => 1,
        );
        if (!$jsst_table->bind($jsst_data) || !$jsst_table->store()) {
            return 0;
        }
        return (int) $jsst_table->id;
    }

    /**
     * What a row that names no department or priority gets.
     *
     * The one the site marked as its default, which is the answer the rest of
     * the plugin would give; the lowest id only as a fallback for a site that
     * has never set one.
     */
    private static function defaultId($jsst_table) {
        $jsst_name = jssupportticket::$_db->prefix . 'js_ticket_' . esc_sql($jsst_table);
        $jsst_id = (int) jssupportticket::$_db->get_var("SELECT id FROM `" . $jsst_name . "` WHERE isdefault = 1 ORDER BY id ASC LIMIT 1");
        if ($jsst_id > 0) {
            return $jsst_id;
        }
        $jsst_id = (int) jssupportticket::$_db->get_var("SELECT MIN(id) FROM `" . $jsst_name . "`");
        return ($jsst_id > 0) ? $jsst_id : 1;
    }

}
