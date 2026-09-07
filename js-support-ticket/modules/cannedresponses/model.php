<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Canned responses — a shared reply library in the free core. (Roadmap 4.0-CORE-03)
 *
 * Loaded only when the stand-alone Canned Responses add-on is inactive:
 * JSSTincluder::getPluginPath() resolves the 'cannedresponses' module to the
 * add-on directory while that add-on is active, so the add-on stays
 * authoritative and nothing is registered or rendered twice. Both sides read and
 * write the same js_ticket_department_message_premade table, so existing
 * responses keep working with no migration. (Roadmap 4.0-CORE-19)
 *
 * What 4.0 adds over the add-on is placeholder support: a response may contain
 * {customer_name} and friends, resolved against the ticket at the moment an
 * agent inserts it.
 *
 * Folders, teams, permissions, approval, usage analytics and AI rewrite stay Pro.
 */
class JSSTcannedresponsesModel {

    /** Bumped when the table layout below changes. */
    const SCHEMA_VERSION = '400';

    /**
     * Create the table if this site never had the add-on, and add whatever an
     * older add-on layout is missing. Each index is checked first, so this cannot
     * fail on a table that already has it.
     */
    public static function ensureSchema() {
        // Existence only: the indexes below are deliberately not part of the
        // check, because a server that cannot build a FULLTEXT index would then
        // ask for a repair on every request. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_cannedresponses_schema', self::SCHEMA_VERSION,
                array('js_ticket_department_message_premade' => array()))) {
            return;
        }
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_department_message_premade';
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    departmentid varchar(45) DEFAULT NULL,
                    title varchar(125) DEFAULT NULL,
                    answer text,
                    created datetime NOT NULL,
                    updated datetime DEFAULT NULL,
                    status tinyint(1) DEFAULT NULL,
                    PRIMARY KEY (id)
                ) " . $jsst_charset);

        // Instant Answers searches this table full-text, and the library is read
        // by department and status. (Roadmap 4.0-PERF-01)
        $jsst_indexes = jssupportticket::$_db->get_col('SHOW INDEX FROM `' . $jsst_table . '`', 2);
        if (!is_array($jsst_indexes)) {
            $jsst_indexes = array();
        }
        $jsst_wanted = array(
            'jsst_ir_ft'      => "ADD FULLTEXT `jsst_ir_ft` (`title`, `answer`)",
            'ft_title'        => "ADD FULLTEXT `ft_title` (`title`)",
            'ft_answer'       => "ADD FULLTEXT `ft_answer` (`answer`)",
            'jsst_department' => "ADD INDEX `jsst_department` (`departmentid`, `status`)",
        );
        foreach ($jsst_wanted as $jsst_index => $jsst_clause) {
            if (!in_array($jsst_index, $jsst_indexes, true)) {
                // A full-text index is unavailable on some older MyISAM/InnoDB
                // combinations; a failure here must not surface to the user.
                jssupportticket::$_db->hide_errors();
                jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ' . $jsst_clause);
                jssupportticket::$_db->show_errors();
            }
        }

        update_option('jsst_cannedresponses_schema', self::SCHEMA_VERSION, false);
    }

    /* ------------------------------------------------------------------ *
     * Placeholders (Roadmap 4.0-CORE-03)
     * ------------------------------------------------------------------ */

    /**
     * The placeholders a canned response may contain, with a short description
     * for the reference list on the edit screen.
     */
    public static function placeholders() {
        return apply_filters('jsst_cannedresponse_placeholders', array(
            '{customer_name}'  => esc_html(__('The name on the ticket', 'js-support-ticket')),
            '{customer_email}' => esc_html(__('The e-mail address on the ticket', 'js-support-ticket')),
            '{agent_name}'     => esc_html(__('The name of the agent inserting the reply', 'js-support-ticket')),
            '{ticket_id}'      => esc_html(__('The ticket reference shown to the customer', 'js-support-ticket')),
            '{ticket_subject}' => esc_html(__('The ticket subject', 'js-support-ticket')),
            '{ticket_status}'  => esc_html(__('The current status', 'js-support-ticket')),
            '{ticket_url}'     => esc_html(__('A link to the ticket in the customer portal', 'js-support-ticket')),
            '{department}'     => esc_html(__('The department the ticket belongs to', 'js-support-ticket')),
            '{site_name}'      => esc_html(__('Your site title', 'js-support-ticket')),
        ));
    }

    /**
     * The values for one ticket. An unknown ticket yields empty strings rather
     * than leaving raw {tokens} in the reply.
     */
    public function placeholderValues($jsst_ticketid = 0) {
        $jsst_values = array(
            '{customer_name}'  => '',
            '{customer_email}' => '',
            '{agent_name}'     => '',
            '{ticket_id}'      => '',
            '{ticket_subject}' => '',
            '{ticket_status}'  => '',
            '{ticket_url}'     => '',
            '{department}'     => '',
            '{site_name}'      => get_bloginfo('name'),
        );

        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser();
        if (isset($jsst_current_user->display_name)) {
            $jsst_values['{agent_name}'] = $jsst_current_user->display_name;
        }

        if (is_numeric($jsst_ticketid) && $jsst_ticketid > 0) {
            $jsst_query = jssupportticket::$_db->prepare(
                "SELECT ticket.name, ticket.email, ticket.ticketid, ticket.customticketno, ticket.subject, ticket.token,
                        status.status AS statusname, department.departmentname AS departmentname
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    WHERE ticket.id = %d",
                $jsst_ticketid
            );
            $jsst_ticket = jssupportticket::$_db->get_row($jsst_query);
            if (!empty($jsst_ticket)) {
                $jsst_values['{customer_name}']  = (string) $jsst_ticket->name;
                $jsst_values['{customer_email}'] = (string) $jsst_ticket->email;
                $jsst_values['{ticket_id}']      = (string) $jsst_ticket->ticketid;
                $jsst_values['{ticket_subject}'] = (string) $jsst_ticket->subject;
                $jsst_values['{ticket_status}']  = (string) $jsst_ticket->statusname;
                $jsst_values['{department}']     = (string) $jsst_ticket->departmentname;
                $jsst_values['{ticket_url}']     = jssupportticket::makeUrl(array(
                    'jstmod' => 'ticket',
                    'jstlay' => 'ticketdetail',
                    'jssupportticketid' => $jsst_ticketid,
                ));
            }
        }

        return apply_filters('jsst_cannedresponse_placeholder_values', $jsst_values, $jsst_ticketid);
    }

    /**
     * Replace every placeholder in a response body.
     */
    public function renderPlaceholders($jsst_text, $jsst_ticketid = 0) {
        if ($jsst_text === '' || strpos((string) $jsst_text, '{') === false) {
            return $jsst_text;
        }
        $jsst_values = $this->placeholderValues($jsst_ticketid);
        return str_replace(array_keys($jsst_values), array_values($jsst_values), $jsst_text);
    }

    /* ------------------------------------------------------------------ *
     * Library
     * ------------------------------------------------------------------ */

    function getPremadeMessages() {
        self::ensureSchema();
        // Filter
        $jsst_title = isset(jssupportticket::$_search['cannedresponses']) ? jssupportticket::$_search['cannedresponses']['title'] : '';
        $jsst_statusid = isset(jssupportticket::$_search['cannedresponses']) ? jssupportticket::$_search['cannedresponses']['status'] : '';
        $jsst_departmentid = isset(jssupportticket::$_search['cannedresponses']) ? jssupportticket::$_search['cannedresponses']['departmentid'] : '';

        $jsst_where = array();
        $jsst_params = array();
        if ($jsst_title != null) {
            $jsst_where[] = "premade.title LIKE %s";
            $jsst_params[] = '%' . jssupportticket::$_db->esc_like($jsst_title) . '%';
        }
        if (is_numeric($jsst_departmentid) && $jsst_departmentid > 0) {
            $jsst_where[] = "premade.departmentid = %d";
            $jsst_params[] = $jsst_departmentid;
        }
        if (is_numeric($jsst_statusid) && $jsst_statusid >= 0) {
            $jsst_where[] = "premade.status = %d";
            $jsst_params[] = $jsst_statusid;
        }
        $jsst_inquery = empty($jsst_where) ? '' : ' WHERE ' . implode(' AND ', $jsst_where);

        jssupportticket::$jsst_data['filter']['title'] = $jsst_title;
        jssupportticket::$jsst_data['filter']['status'] = $jsst_statusid;
        jssupportticket::$jsst_data['filter']['departmentid'] = $jsst_departmentid;

        // Pagination
        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` AS premade" . $jsst_inquery;
        if (!empty($jsst_params)) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_params);
        }
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total,'premademessages');

        // Data
        $jsst_query = "SELECT premade.*,department.departmentname AS departmentname
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` AS premade
					JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON premade.departmentid = department.id";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY premade.status ASC,premade.title ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        if (!empty($jsst_params)) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_params);
        }
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getPremadeMessageForForm($jsst_id) {
        self::ensureSchema();
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = jssupportticket::$_db->prepare(
                "SELECT premade.*,department.departmentname AS departmentname
								FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` AS premade
								JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON premade.departmentid = department.id
								WHERE premade.id = %d",
                $jsst_id
            );
            jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    function storePreMadeMessage($jsst_data) {
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_per_task = empty($jsst_data['id']) ? 'Add Canned Response' : 'Edit Canned Response';
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_per_task);
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!JSSTroles::canReplyPublicly()) {
            /* Authoring the shared library of customer-facing replies goes with
               being allowed to send them: agents yes, light agents no. */
            return false;
        }
        self::ensureSchema();
        $jsst_data['id'] = isset($jsst_data['id']) ? $jsst_data['id'] : '';
        if ($jsst_data['id']) {
            $jsst_data['updated'] = date_i18n('Y-m-d H:i:s');
        } else {
            $jsst_data['created'] = date_i18n('Y-m-d H:i:s');
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);
        $jsst_data['answer'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData(isset($_POST['answer']) ? wp_unslash($_POST['answer']) : '');

        $jsst_row = JSSTincluder::getJSTable('cannedresponses');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 0) {
            do_action('jsst_after_save_cannedresponse', $jsst_row->id);
            JSSTmessage::setMessage(esc_html(__('Canned response message has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Canned response message has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    function removePreMadeMessage($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;

        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Canned Response');
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!JSSTroles::canReplyPublicly()) {
            /* Authoring the shared library of customer-facing replies goes with
               being allowed to send them: agents yes, light agents no. */
            return false;
        }

        $jsst_row = JSSTincluder::getJSTable('cannedresponses');
        if ($jsst_row->delete($jsst_id)) {
            do_action('jsst_after_save_cannedresponse', $jsst_id);
            JSSTmessage::setMessage(esc_html(__('Premade department message has been deleted', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            JSSTmessage::setMessage(esc_html(__('Premade department message has not been deleted', 'js-support-ticket')), 'error');
        }
        return;
    }

    function getPreMadeMessageForCombobox() {
        self::ensureSchema();
        $jsst_query = "SELECT id, title  AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` WHERE status = 1";
        $jsst_query .= " ORDER BY title ASC ";
        $jsst_list = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_list;
    }

    /**
     * The body an agent asked for, with placeholders already resolved against
     * the ticket they are replying to. (Roadmap 4.0-CORE-03)
     */
    function getpremadeajax() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-premade-ajax') ) {
            die( 'Security check Failed' );
        }
        /* Only someone who can work tickets may read the reply library — which
           now includes agents holding the core capability, not just
           administrators. Without this an agent could see the "insert canned
           response" control and get nothing back from it. (Roadmap 4.0-SEC-04) */
        $jsst_allowed = current_user_can(JSSTroles::CAP_TICKETS)
            || JSSTroles::canManageHelpDesk()
            || ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff() );
        if (!$jsst_allowed) {
            return '';
        }
        $jsst_premadeid = JSSTrequest::getVar('val');
        if (!$jsst_premadeid || !is_numeric($jsst_premadeid)) {
            return '';
        }
        $jsst_premade = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT answer FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` WHERE status = 1 AND id = %d",
            $jsst_premadeid
        ));
        if ($jsst_premade === null) {
            return '';
        }
        $jsst_ticketid = JSSTrequest::getVar('ticketid');
        $jsst_ticketid = is_numeric($jsst_ticketid) ? (int) $jsst_ticketid : 0;
        return $this->renderPlaceholders($jsst_premade, $jsst_ticketid);
    }

    function changeStatus($jsst_id) {

        if (!is_numeric($jsst_id))
            return false;

        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Canned Response');
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!JSSTroles::canReplyPublicly()) {
            /* Authoring the shared library of customer-facing replies goes with
               being allowed to send them: agents yes, light agents no. */
            return false;
        }

        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT status FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` WHERE id = %d",
            $jsst_id
        );
        $jsst_status = jssupportticket::$_db->get_var($jsst_query);
        $jsst_status = 1 - $jsst_status;

        $jsst_row = JSSTincluder::getJSTable('cannedresponses');
        if ($jsst_row->update(array('id' => $jsst_id, 'status' => $jsst_status))) {
            do_action('jsst_after_save_cannedresponse', $jsst_id);
            JSSTmessage::setMessage(esc_html(__('Canned Response','js-support-ticket')).' '.esc_html(__('status has been changed', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Canned Response','js-support-ticket')).' '.esc_html(__('status has not been changed', 'js-support-ticket')), 'error');
        }
        return;
    }

    /**
     * The search state for the library screens, read from the submitted form or
     * from the saved search cookie.
     *
     * Registered by the plugin bootstrap only when core owns the feature: the
     * add-on hooks its own copy of this. (Roadmap 4.0-CORE-19)
     */
    public static function handleSearchFormData() {
        $jsst_jstlay = '';
        if(isset($_REQUEST['jstlay'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData($_REQUEST['jstlay']);
        }elseif(isset($_REQUEST['page'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData($_REQUEST['page']);
        }elseif(isset($_REQUEST['jshdlay'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData($_REQUEST['jshdlay']);
        }
        if (!in_array($jsst_jstlay, array('premademessages', 'cannedresponses', 'agentcannedresponses'), true)) {
            return;
        }
        $jsst_callfrom = 0;
        if(isset($_REQUEST['JSST_form_search']) && $_REQUEST['JSST_form_search'] == 'JSST_SEARCH'){
            $jsst_callfrom = 1;
        }elseif(JSSTrequest::getVar('pagenum', 'get', null) != null){
            $jsst_callfrom = 2;
        }
        $jsst_setcookies = false;
        $jsst_ticket_search_cookie_data = '';
        $jsst_search_array = array();
        if($jsst_callfrom == 1){
            $jsst_nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $jsst_nonce, 'canned-responses') ) {
                die( 'Security check Failed' );
            }
            $jsst_search_array['title'] = JSSTrequest::getVar('title');
            $jsst_search_array['status'] = JSSTrequest::getVar('status');
            $jsst_search_array['departmentid'] = JSSTrequest::getVar('departmentid');
            $jsst_search_array['search_from_premade'] = 1;
            $jsst_setcookies = true;
        }elseif($jsst_callfrom == 2){
            if(isset($_COOKIE['jsst_ticket_search_data'])){
                $jsst_ticket_search_cookie_data = $_COOKIE['jsst_ticket_search_data'];
                $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
            }
            if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_premade'])){
                $jsst_search_array['title'] = $jsst_ticket_search_cookie_data['title'];
                $jsst_search_array['status'] = $jsst_ticket_search_cookie_data['status'];
                $jsst_search_array['departmentid'] = $jsst_ticket_search_cookie_data['departmentid'];
            }
        }else{
            $jsst_search_array = array();
            jssupportticket::removeusersearchcookies();
        }
        jssupportticket::$_search['cannedresponses']['title'] = isset($jsst_search_array['title']) ? $jsst_search_array['title'] : null;
        jssupportticket::$_search['cannedresponses']['status'] = isset($jsst_search_array['status']) ? $jsst_search_array['status'] : null;
        jssupportticket::$_search['cannedresponses']['departmentid'] = isset($jsst_search_array['departmentid']) ? $jsst_search_array['departmentid'] : null;

        if($jsst_setcookies){
            jssupportticket::setusersearchcookies($jsst_setcookies,$jsst_search_array);
        }
    }

}
