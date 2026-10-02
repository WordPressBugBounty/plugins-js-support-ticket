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
        $jsst_filters['customfields'] = self::fieldModeFrom($jsst_data);
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

    /* ------------------------------------------------------------------ *
     * The answers people actually filled in
     *
     * A ticket's custom answers live in `js_ticket_tickets.params`, a JSON map
     * of field key to value, and which questions were asked depends on which
     * form the ticket was raised on - `js_ticket_fieldsordering` holds one row
     * per field per form, and a field key (`ufield_<id>`) is unique to its row,
     * so the same question on two forms is two different keys.
     *
     * The add-on's answer to that was a "Single / Multiple Header" setting: in
     * multiple mode it printed a fresh header above every ticket, naming that
     * ticket's own fields. That is not a spreadsheet. Excel, Calc, Sheets and
     * every CSV reader take line one as the header and read every later header
     * line as data, so the file could not be sorted, filtered or pivoted, and
     * this plugin's own importer could not read it back. It was a stack of tiny
     * files sharing one name.
     *
     * The rewrite dropped the setting, correctly, but dropped the custom fields
     * with it - so the export stopped carrying the answers altogether, which on
     * a site running Multiple Ticket Forms is most of what anybody wanted from
     * it. What replaces both is one header wide enough for every form in the
     * export, with a blank where a form never asked that question, and a Form
     * column so a reader can still tell where a row came from.
     * (Roadmap 4.0-CORE-10, 5.0-ANA-01, 5.0-FORM-01)
     * ------------------------------------------------------------------ */

    /** One column per distinct field name, merging the same question across forms. */
    const FIELDS_MERGED = 'merged';
    /** One column per field per form, for an export that has to keep them apart. */
    const FIELDS_EXACT = 'exact';
    /** The twenty fixed columns and nothing else. */
    const FIELDS_NONE = 'none';

    public static function fieldModes() {
        return array(
            self::FIELDS_MERGED => __('One column per question, merging the same question across forms', 'js-support-ticket'),
            self::FIELDS_EXACT  => __('One column per question per form, kept apart', 'js-support-ticket'),
            self::FIELDS_NONE   => __('Leave custom fields out', 'js-support-ticket'),
        );
    }

    /**
     * The mode a submitted export form is asking for.
     *
     * Two shapes are read, because two screens post here. The one this plugin
     * draws now sends `customfieldsui` to say so, and then a ticked
     * `customfieldson` means "include them" - the layout choice beside it is
     * only rendered where it could change anything, so its absence means the
     * default rather than nothing. Anything else - the older select, the
     * front-end screen, a saved schedule, somebody's own form - is read exactly
     * as it always was. (Roadmap 6.5-DATA-05)
     */
    public static function fieldModeFrom($jsst_data) {
        if (empty($jsst_data['customfieldsui'])) {
            return self::fieldMode(isset($jsst_data['customfields']) ? $jsst_data['customfields'] : '');
        }
        if (empty($jsst_data['customfieldson'])) {
            return self::FIELDS_NONE;
        }
        $jsst_choice = isset($jsst_data['customfields']) ? $jsst_data['customfields'] : '';
        /* "Leave them out" cannot arrive down this path - the tick above is how
           that is said now - so a posted `none` is ignored rather than obeyed. */
        return ($jsst_choice === self::FIELDS_EXACT) ? self::FIELDS_EXACT : self::FIELDS_MERGED;
    }

    /**
     * The names of the questions this desk added, for showing the reader.
     *
     * A label that says "your own questions" names a category and leaves the
     * reader to work out what is in it. Their own words do not: somebody who
     * sees "Order number", "Site URL" knows at once what the tick will add to
     * the file, because they wrote those. Distinct and ordered so the list is
     * stable between page loads.
     */
    public static function questionNames($jsst_limit = 3) {
        $jsst_rows = jssupportticket::$_db->get_col(
            "SELECT DISTINCT TRIM(fieldtitle) FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering`
              WHERE isuserfield = 1 AND published = 1 AND fieldfor = 1
                AND userfieldtype != 'termsandconditions' AND TRIM(fieldtitle) != ''
              ORDER BY multiformid ASC, ordering ASC, id ASC");
        $jsst_rows = array_values((array) $jsst_rows);
        return array('names' => array_slice($jsst_rows, 0, (int) $jsst_limit), 'total' => count($jsst_rows));
    }

    /**
     * Question names that more than one form asks.
     *
     * This is the whole of the difference between the two layouts: where no
     * name is shared, they produce the same file column for column, and asking
     * somebody to choose between them is asking a question with one answer. So
     * the screen only offers the choice when this returns something, and names
     * what it found rather than describing columns in the abstract.
     *
     * Asked of every form on the site rather than of the export's own filters,
     * because the filters are chosen on the same screen and are not known when
     * it is drawn. The worst case is the honest one to show.
     */
    public static function clashingNames() {
        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT LOWER(TRIM(fieldtitle)) AS jsst_name, COUNT(DISTINCT multiformid) AS jsst_forms
               FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering`
              WHERE isuserfield = 1 AND published = 1 AND fieldfor = 1
                AND userfieldtype != 'termsandconditions' AND TRIM(fieldtitle) != ''
              GROUP BY jsst_name HAVING jsst_forms > 1
              ORDER BY jsst_name ASC");
        $jsst_out = array();
        foreach ((array) $jsst_rows AS $jsst_row) {
            $jsst_out[] = $jsst_row->jsst_name;
        }
        return $jsst_out;
    }

    public static function fieldMode($jsst_value) {
        $jsst_value = sanitize_key((string) $jsst_value);
        return array_key_exists($jsst_value, self::fieldModes()) ? $jsst_value : self::FIELDS_MERGED;
    }

    /**
     * The forms the matched tickets were actually raised on.
     *
     * The union is built from these rather than from every form on the site,
     * because a desk with forty forms of ten questions would otherwise get four
     * hundred columns on every export and thirty-nine forty-firsts of them
     * empty. Narrowing the export to one form therefore narrows the file to
     * that form's questions, with no second setting to find.
     */
    public function formsIn($jsst_filters) {
        $jsst_clause = $this->buildWhere($jsst_filters);
        $jsst_query = "SELECT DISTINCT ticket.multiformid FROM `" . jssupportticket::$_db->prefix
            . "js_ticket_tickets` AS ticket WHERE " . $jsst_clause['sql'];
        if (!empty($jsst_clause['args'])) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_clause['args']);
        }
        $jsst_forms = array();
        foreach ((array) jssupportticket::$_db->get_col($jsst_query) AS $jsst_id) {
            $jsst_forms[] = (int) $jsst_id;
        }
        return $jsst_forms;
    }

    /** Form id to name, for the Form column and for telling two questions apart. */
    public function formTitles() {
        static $jsst_titles = null;
        if ($jsst_titles !== null) {
            return $jsst_titles;
        }
        $jsst_titles = array();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_multiform';
        /* A site without the Multiple Ticket Forms add-on has no table here and
           one form nobody named, which is why this answers with an empty map
           rather than failing - the Form column then reads as the default. */
        if (jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return $jsst_titles;
        }
        foreach ((array) jssupportticket::$_db->get_results('SELECT id, title FROM `' . $jsst_table . '`') AS $jsst_row) {
            $jsst_titles[(int) $jsst_row->id] = $jsst_row->title;
        }
        return $jsst_titles;
    }

    /**
     * The custom columns this export needs, in the order a reader expects them.
     *
     * Merged by name by default: "Order number" asked on three forms is one
     * column, because that is the question somebody is reporting on and three
     * columns called the same thing help nobody. Merging on the name rather
     * than on the key is deliberate - the keys differ per form, so nothing else
     * could join them - and the exact mode is there for an export that must
     * keep a form's answers provably its own.
     *
     * @return array each entry: label, keys (the field keys that feed it), type.
     */
    public function customColumns($jsst_filters) {
        $jsst_mode = isset($jsst_filters['customfields'])
            ? self::fieldMode($jsst_filters['customfields']) : self::FIELDS_MERGED;
        if ($jsst_mode === self::FIELDS_NONE) {
            return array();
        }
        $jsst_forms = $this->formsIn($jsst_filters);
        if (empty($jsst_forms)) {
            return array();
        }
        $jsst_in = implode(',', array_map('intval', $jsst_forms));
        /* Published user fields only, and never the terms-and-conditions tick -
           it is a consent box on the form rather than a piece of ticket data,
           and every other screen already leaves it out. */
        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT field, fieldtitle, userfieldtype, multiformid, ordering
               FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering`
              WHERE isuserfield = 1 AND published = 1 AND fieldfor = 1
                AND userfieldtype != 'termsandconditions'
                AND multiformid IN (" . $jsst_in . ")
              ORDER BY multiformid ASC, ordering ASC, id ASC");

        $jsst_titles = $this->formTitles();
        $jsst_columns = array();
        $jsst_seen = array();
        foreach ((array) $jsst_rows AS $jsst_row) {
            $jsst_name = trim((string) $jsst_row->fieldtitle);
            if ($jsst_name === '') {
                $jsst_name = $jsst_row->field;
            }
            $jsst_group = ($jsst_mode === self::FIELDS_MERGED)
                ? jssupportticketphplib::JSST_strtolower(preg_replace('/\s+/', ' ', $jsst_name))
                : $jsst_row->field;
            if (isset($jsst_columns[$jsst_group])) {
                $jsst_columns[$jsst_group]['keys'][] = $jsst_row->field;
                continue;
            }
            $jsst_label = $jsst_name;
            /* In exact mode two forms asking the same question would otherwise
               give two columns with the same heading and no way to tell which
               is which, so the form's name is added to both. */
            if ($jsst_mode === self::FIELDS_EXACT && isset($jsst_seen[$jsst_name])) {
                $jsst_label = $jsst_name . ' (' . $this->formName($jsst_row->multiformid, $jsst_titles) . ')';
                $jsst_columns[$jsst_seen[$jsst_name]]['label'] = $jsst_columns[$jsst_seen[$jsst_name]]['name']
                    . ' (' . $this->formName($jsst_columns[$jsst_seen[$jsst_name]]['form'], $jsst_titles) . ')';
            } else {
                $jsst_seen[$jsst_name] = $jsst_group;
            }
            $jsst_columns[$jsst_group] = array(
                'label' => $jsst_label,
                'name'  => $jsst_name,
                'form'  => (int) $jsst_row->multiformid,
                'type'  => $jsst_row->userfieldtype,
                'keys'  => array($jsst_row->field),
            );
        }
        return array_values($jsst_columns);
    }

    /**
     * What to call a form in the file.
     *
     * The site's own default is "Default form", which is what a desk with one
     * form has and never named. Anything else with no name is a form that has
     * been deleted since its tickets were raised, and the id is what somebody
     * needs to chase it - never another "Default form", because this name is
     * also what tells two columns headed with the same question apart, and two
     * identical names would leave them indistinguishable.
     */
    private function formName($jsst_formid, $jsst_titles = null) {
        $jsst_titles = ($jsst_titles === null) ? $this->formTitles() : $jsst_titles;
        $jsst_formid = (int) $jsst_formid;
        if (isset($jsst_titles[$jsst_formid]) && $jsst_titles[$jsst_formid] !== '') {
            return $jsst_titles[$jsst_formid];
        }
        if ($jsst_formid === (int) JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId()) {
            return esc_html(__('Default form', 'js-support-ticket'));
        }
        /* translators: %d is a ticket form's id. */
        return sprintf(esc_html(__('Form #%d', 'js-support-ticket')), $jsst_formid);
    }

    /**
     * One custom answer, as a cell rather than as markup.
     *
     * showCustomFields() is the screen's version of this and returns HTML - a
     * download link for a file field - which is the right answer on a page and
     * the wrong one in a spreadsheet. A date is rendered in the site's format
     * for the same reason every other date in this file is, and a multiple
     * choice arrives as an array or as a joined string depending on the field,
     * so both are handled.
     */
    public static function customValue($jsst_answers, $jsst_column) {
        foreach ($jsst_column['keys'] AS $jsst_key) {
            if (!isset($jsst_answers[$jsst_key]) || $jsst_answers[$jsst_key] === '' || $jsst_answers[$jsst_key] === null) {
                continue;
            }
            $jsst_value = $jsst_answers[$jsst_key];
            if (is_array($jsst_value)) {
                return implode(', ', array_map('strval', $jsst_value));
            }
            $jsst_value = (string) $jsst_value;
            if ($jsst_column['type'] === 'date') {
                if (jssupportticketphplib::JSST_strpos($jsst_value, '1970') !== false) {
                    return '';
                }
                $jsst_time = jssupportticketphplib::JSST_strtotime($jsst_value);
                return $jsst_time ? date_i18n(jssupportticket::$_config['date_format'], $jsst_time) : $jsst_value;
            }
            return $jsst_value;
        }
        return '';
    }

    /** A ticket's stored answers, as an array. */
    public static function answersOf($jsst_ticket) {
        if (!isset($jsst_ticket->params) || $jsst_ticket->params === '') {
            return array();
        }
        $jsst_answers = json_decode($jsst_ticket->params, true);
        return is_array($jsst_answers) ? $jsst_answers : array();
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
                    ticket.params,
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
     *
     * The custom columns are appended rather than woven in, and that is load
     * bearing: JSSTexports::anonymiseRow() overwrites cells 14 to 16 and the
     * PDF export keeps a fixed list of indexes, so anything inserted before
     * them would silently anonymise the wrong column.
     *
     * @param array $jsst_custom what customColumns() worked out, or none.
     */
    public function ticketColumns($jsst_custom = array()) {
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
        if (!empty($jsst_custom)) {
            /* Which form a row came from, once, in a column - which is the only
               thing the repeated header ever really told anybody. */
            $jsst_columns[] = esc_html(__('Form', 'js-support-ticket'));
            foreach ($jsst_custom AS $jsst_column) {
                $jsst_columns[] = esc_html($jsst_column['label']);
            }
        }
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
    public function ticketRow($jsst_ticket, $jsst_counts = array(), $jsst_custom = array()) {
        $jsst_row = array(
            $jsst_ticket->ticketid,
            /* Zero is the absence of a number, not a number. `customticketno`
               is an INT column that counts tickets up from one, and a row that
               never went through that counter - anything seeded, imported, or
               raised before the setting was turned on - holds 0. The old test
               was `!= ''`, and '0' is not '', so every one of those tickets
               exported its reference as a literal 0 rather than falling back to
               the ticket id. On the PDF that is one of only eight columns, so a
               third of what identifies a row was the same digit all the way
               down. (Roadmap 6.5-DATA-04) */
            ((string) $jsst_ticket->customticketno !== '' && (int) $jsst_ticket->customticketno > 0)
                ? $jsst_ticket->customticketno : $jsst_ticket->ticketid,
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
        if (!empty($jsst_custom)) {
            $jsst_row[] = $this->formName($jsst_ticket->multiformid);
            $jsst_answers = self::answersOf($jsst_ticket);
            foreach ($jsst_custom AS $jsst_column) {
                /* Blank where this ticket's form never asked the question. A
                   blank cell in a column that exists is a thing a spreadsheet
                   can sort, filter and total; a row of its own shape is not. */
                $jsst_row[] = self::customValue($jsst_answers, $jsst_column);
            }
        }
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
        /* Worked out once, before a row is written: the header has to be as
           wide as the widest form in the export, and every row after it has to
           agree with that header. */
        $jsst_custom = $this->customColumns($jsst_filters);
        $jsst_writer->row($this->ticketColumns($jsst_custom));
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
                    isset($jsst_counts[(int) $jsst_ticket->id]) ? $jsst_counts[(int) $jsst_ticket->id] : array(),
                    $jsst_custom
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
