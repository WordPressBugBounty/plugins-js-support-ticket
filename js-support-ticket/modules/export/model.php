<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Ticket export — part of the free core. (Roadmap 4.0-CORE-10)
 *
 * Not a port. The add-on built a tab-separated payload by string concatenation,
 * wrapping values in quotes without escaping the quotes, tabs or newlines inside
 * them, held the whole report in memory, and then passed it through wp_kses()
 * before printing. This writes real CSV through JSSTcsvwriter, one row at a time,
 * in batches, with every filter parameterised.
 *
 * Loaded only when the stand-alone Export add-on is inactive:
 * JSSTincluder::getPluginPath() resolves the 'export' module to the add-on
 * directory while that add-on is active. (Roadmap 4.0-CORE-19)
 *
 * Scheduled exports, XLSX/PDF, audit packages and anonymisation stay Pro.
 */
class JSSTexportModel {

    /** Tickets read per batch, so a large export is bounded in memory. */
    const BATCH = 200;

    /**
     * The filters the export accepts, as column => request key.
     *
     * The same set the add-on offered, but validated rather than interpolated.
     */
    public static function filterFields() {
        return array(
            'departmentid' => 'departmentid',
            'staffid'      => 'staffid',
            'priorityid'   => 'priorityid',
            'uid'          => 'uid',
            'status'       => 'ticketstatus',
        );
    }

    /**
     * Read the filters from a request payload into a validated array.
     *
     * Anything non-numeric is dropped rather than passed on, and the dates must
     * look like dates.
     */
    public function readFilters($jsst_data = null) {
        if (!is_array($jsst_data)) {
            $jsst_data = JSSTrequest::get('post');
        }
        $jsst_filters = array();
        if (!is_array($jsst_data)) {
            return $jsst_filters;
        }
        foreach (self::filterFields() AS $jsst_column => $jsst_key) {
            if (isset($jsst_data[$jsst_key]) && $jsst_data[$jsst_key] !== '' && is_numeric($jsst_data[$jsst_key])) {
                $jsst_filters[$jsst_column] = (int) $jsst_data[$jsst_key];
            }
        }
        foreach (array('startdate', 'enddate') AS $jsst_key) {
            if (isset($jsst_data[$jsst_key]) && $jsst_data[$jsst_key] !== '') {
                $jsst_date = self::normaliseDate($jsst_data[$jsst_key]);
                if ($jsst_date !== '') {
                    $jsst_filters[$jsst_key] = $jsst_date;
                }
            }
        }
        if (isset($jsst_data['isoverdue']) && $jsst_data['isoverdue'] !== '') {
            $jsst_filters['isoverdue'] = ($jsst_data['isoverdue'] == 1) ? 1 : 0;
        }
        if (isset($jsst_data['multiformid']) && is_numeric($jsst_data['multiformid'])) {
            $jsst_filters['multiformid'] = (int) $jsst_data['multiformid'];
        }
        return $jsst_filters;
    }

    /**
     * A Y-m-d date, or '' when the value is not one.
     */
    public static function normaliseDate($jsst_value) {
        $jsst_value = trim((string) $jsst_value);
        if ($jsst_value === '') {
            return '';
        }
        $jsst_time = jssupportticketphplib::JSST_strtotime($jsst_value);
        if (!$jsst_time) {
            return '';
        }
        // Site time, not UTC. Tickets are written with date_i18n(), so their
        // created dates are in the site's own timezone; formatting a filter date
        // with gmdate() moved it a day whenever the site is not on UTC, and the
        // export then silently missed a day's tickets.
        return date_i18n('Y-m-d', $jsst_time);
    }

    /**
     * Turn the validated filters into a WHERE fragment and its arguments.
     */
    private function buildWhere($jsst_filters) {
        $jsst_where = array('1 = 1');
        $jsst_args = array();
        foreach (self::filterFields() AS $jsst_column => $jsst_key) {
            if (isset($jsst_filters[$jsst_column])) {
                $jsst_where[] = 'ticket.' . $jsst_column . ' = %d';
                $jsst_args[] = $jsst_filters[$jsst_column];
            }
        }
        if (isset($jsst_filters['startdate'])) {
            $jsst_where[] = 'DATE(ticket.created) >= %s';
            $jsst_args[] = $jsst_filters['startdate'];
        }
        if (isset($jsst_filters['enddate'])) {
            $jsst_where[] = 'DATE(ticket.created) <= %s';
            $jsst_args[] = $jsst_filters['enddate'];
        }
        if (isset($jsst_filters['isoverdue'])) {
            if ($jsst_filters['isoverdue'] == 1) {
                $jsst_where[] = 'ticket.isoverdue = 1';
            } else {
                $jsst_where[] = 'ticket.isoverdue <> 1';
            }
        }
        if (isset($jsst_filters['multiformid'])) {
            $jsst_where[] = 'ticket.multiformid = %d';
            $jsst_args[] = $jsst_filters['multiformid'];
        }
        return array('sql' => implode(' AND ', $jsst_where), 'args' => $jsst_args);
    }

    /**
     * How many tickets the current filters match. Shown before the download so an
     * administrator knows whether the filter did what they meant.
     */
    public function countTickets($jsst_filters) {
        $jsst_clause = $this->buildWhere($jsst_filters);
        $jsst_query = "SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE " . $jsst_clause['sql'];
        if (!empty($jsst_clause['args'])) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_clause['args']);
        }
        return (int) jssupportticket::$_db->get_var($jsst_query);
    }

    /**
     * One batch of tickets for the export, oldest first so the file is stable
     * across batches.
     */
    public function getTicketBatch($jsst_filters, $jsst_offset, $jsst_limit) {
        $jsst_clause = $this->buildWhere($jsst_filters);
        $jsst_query = "SELECT ticket.id, ticket.ticketid, ticket.customticketno, ticket.subject, ticket.message,
                    ticket.isanswered, ticket.isoverdue, ticket.created, ticket.updated, ticket.duedate,
                    ticket.closed, ticket.lastreply, ticket.name, ticket.email, ticket.phone, ticket.multiformid,
                    department.departmentname AS departmentname,
                    priority.priority AS priority,
                    status.status AS statustitle,
                    user.name AS user_login
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                LEFT JOIN `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user ON user.id = ticket.uid
                WHERE " . $jsst_clause['sql'] . "
                ORDER BY ticket.id ASC
                LIMIT %d, %d";
        $jsst_query = jssupportticket::$_db->prepare(
            $jsst_query,
            array_merge($jsst_clause['args'], array((int) $jsst_offset, (int) $jsst_limit))
        );
        $jsst_rows = jssupportticket::$_db->get_results($jsst_query);
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * The column headings, in the same order as ticketRow() below.
     */
    public function ticketColumns() {
        $jsst_columns = array(
            esc_html(__('Ticket ID', 'js-support-ticket')),
            esc_html(__('Reference', 'js-support-ticket')),
            esc_html(__('Subject', 'js-support-ticket')),
            esc_html(__('Message', 'js-support-ticket')),
            esc_html(__('Status', 'js-support-ticket')),
            esc_html(__('Priority', 'js-support-ticket')),
            esc_html(__('Department', 'js-support-ticket')),
            esc_html(__('Answered', 'js-support-ticket')),
            esc_html(__('Overdue', 'js-support-ticket')),
            esc_html(__('Created', 'js-support-ticket')),
            esc_html(__('Updated', 'js-support-ticket')),
            esc_html(__('Due Date', 'js-support-ticket')),
            esc_html(__('Closed Date', 'js-support-ticket')),
            esc_html(__('Last Reply', 'js-support-ticket')),
            esc_html(__('Requester User Name', 'js-support-ticket')),
            esc_html(__('Requester Name', 'js-support-ticket')),
            esc_html(__('Requester Email', 'js-support-ticket')),
            esc_html(__('Requester Phone', 'js-support-ticket')),
            esc_html(__('Replies', 'js-support-ticket')),
            esc_html(__('Attachments', 'js-support-ticket')),
        );
        return apply_filters('jsst_export_ticket_columns', $jsst_columns);
    }

    /**
     * One ticket as a row of cells, in ticketColumns() order.
     *
     * Reply and attachment counts rather than one column per reply: the add-on
     * widened every row to the widest ticket in the export, which produced files
     * with hundreds of mostly empty columns. The conversation itself belongs in a
     * per-ticket export, not in a queue-wide one.
     */
    public function ticketRow($jsst_ticket, $jsst_counts = array()) {
        $jsst_row = array(
            $jsst_ticket->ticketid,
            ($jsst_ticket->customticketno != '') ? $jsst_ticket->customticketno : $jsst_ticket->ticketid,
            $jsst_ticket->subject,
            $jsst_ticket->message,
            $jsst_ticket->statustitle,
            $jsst_ticket->priority,
            $jsst_ticket->departmentname,
            ($jsst_ticket->isanswered == 1) ? esc_html(__('Yes', 'js-support-ticket')) : esc_html(__('No', 'js-support-ticket')),
            ($jsst_ticket->isoverdue == 1) ? esc_html(__('Yes', 'js-support-ticket')) : esc_html(__('No', 'js-support-ticket')),
            self::exportDate($jsst_ticket->created),
            self::exportDate($jsst_ticket->updated),
            self::exportDate($jsst_ticket->duedate),
            self::exportDate($jsst_ticket->closed),
            self::exportDate($jsst_ticket->lastreply),
            $jsst_ticket->user_login,
            $jsst_ticket->name,
            $jsst_ticket->email,
            $jsst_ticket->phone,
            isset($jsst_counts['replies']) ? (int) $jsst_counts['replies'] : 0,
            isset($jsst_counts['attachments']) ? (int) $jsst_counts['attachments'] : 0,
        );
        return apply_filters('jsst_export_ticket_row', $jsst_row, $jsst_ticket);
    }

    /**
     * A date for a spreadsheet, or '' for the zero dates the schema still holds.
     */
    public static function exportDate($jsst_value) {
        $jsst_value = (string) $jsst_value;
        if ($jsst_value === '' || $jsst_value === '0000-00-00 00:00:00' || $jsst_value === '0000-00-00') {
            return '';
        }
        $jsst_time = jssupportticketphplib::JSST_strtotime($jsst_value);
        if (!$jsst_time) {
            return '';
        }
        // Same reason as normaliseDate(): the stored value is already site time,
        // so re-rendering it as UTC would shift every timestamp in the export.
        return date_i18n('Y-m-d H:i:s', $jsst_time);
    }

    /**
     * Reply and attachment counts for a batch, in two queries rather than two per
     * ticket. (Roadmap 4.0-PERF-01)
     */
    public function getCountsForBatch($jsst_ids) {
        $jsst_counts = array();
        if (empty($jsst_ids)) {
            return $jsst_counts;
        }
        $jsst_placeholders = implode(',', array_fill(0, count($jsst_ids), '%d'));
        $jsst_pairs = array(
            'replies'     => array('table' => 'js_ticket_replies', 'column' => 'ticketid'),
            'attachments' => array('table' => 'js_ticket_attachments', 'column' => 'ticketid'),
        );
        foreach ($jsst_pairs AS $jsst_key => $jsst_pair) {
            $jsst_query = jssupportticket::$_db->prepare(
                "SELECT " . $jsst_pair['column'] . " AS ticketid, COUNT(id) AS total
                    FROM `" . jssupportticket::$_db->prefix . $jsst_pair['table'] . "`
                    WHERE " . $jsst_pair['column'] . " IN (" . $jsst_placeholders . ")
                    GROUP BY " . $jsst_pair['column'],
                $jsst_ids
            );
            $jsst_rows = jssupportticket::$_db->get_results($jsst_query);
            if (!is_array($jsst_rows)) {
                continue;
            }
            foreach ($jsst_rows AS $jsst_row) {
                $jsst_counts[(int) $jsst_row->ticketid][$jsst_key] = (int) $jsst_row->total;
            }
        }
        return $jsst_counts;
    }

    /**
     * Stream the filtered tickets as CSV.
     *
     * @param array           $jsst_filters From readFilters().
     * @param JSSTcsvwriter   $jsst_writer  Already started.
     * @return int Number of ticket rows written.
     */
    public function streamTickets($jsst_filters, $jsst_writer) {
        $jsst_writer->row($this->ticketColumns());
        $jsst_offset = 0;
        $jsst_written = 0;
        do {
            $jsst_batch = $this->getTicketBatch($jsst_filters, $jsst_offset, self::BATCH);
            if (empty($jsst_batch)) {
                break;
            }
            $jsst_ids = array();
            foreach ($jsst_batch AS $jsst_ticket) {
                $jsst_ids[] = (int) $jsst_ticket->id;
            }
            $jsst_counts = $this->getCountsForBatch($jsst_ids);
            foreach ($jsst_batch AS $jsst_ticket) {
                $jsst_writer->row($this->ticketRow(
                    $jsst_ticket,
                    isset($jsst_counts[(int) $jsst_ticket->id]) ? $jsst_counts[(int) $jsst_ticket->id] : array()
                ));
                $jsst_written++;
            }
            $jsst_offset += self::BATCH;
        } while (count($jsst_batch) === self::BATCH);
        return $jsst_written;
    }

    /**
     * May the current user export?
     */
    public static function canExport() {
        if (current_user_can('manage_options')) {
            return true;
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            return JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Export Ticket') == true;
        }
        return false;
    }

    /**
     * The file name for a ticket export, carrying the filter dates so two
     * downloads are told apart.
     */
    public function ticketFilename($jsst_filters) {
        $jsst_name = 'tickets';
        if (isset($jsst_filters['startdate'])) {
            $jsst_name .= '-from-' . $jsst_filters['startdate'];
        }
        if (isset($jsst_filters['enddate'])) {
            $jsst_name .= '-to-' . $jsst_filters['enddate'];
        }
        return $jsst_name . '-' . date_i18n('Ymd-His');
    }

}
