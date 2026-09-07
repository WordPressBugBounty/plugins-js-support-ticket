<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTticketController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        if (is_admin()) {
            $jsst_defaultlayout = "tickets";
        } else
            $jsst_defaultlayout = "myticket";
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, $jsst_defaultlayout);
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_tickets':
                    $jsst_list = JSSTrequest::getVar('list');
                    JSSTincluder::getJSModel('ticket')->getTicketsForAdmin($jsst_list);
                    //JSSTincluder::getJSModel('emailpiping')->readEmails();
                    break;
                case 'admin_addticket':
                case 'addticket':

                    $jsst_id = JSSTrequest::getVar('jssupportticketid','',null);
                    $jsst_formid = absint( JSSTrequest::getVar('formid') );
					
                    if($jsst_formid == null){
                        $jsst_formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
                    }
                    // below code to is hanlde parameters for easy digital downloads and woocommerce
                    if($jsst_id != null && jssupportticketphplib::JSST_strstr($jsst_id, '_')){
                        $jsst_id_array = jssupportticketphplib::JSST_explode('_', $jsst_id);
                        if($jsst_id_array[1] == 10){// tikcet id
                            $jsst_id = $jsst_id_array[0];
                        }elseif($jsst_id_array[1] == 11){ // edd order id
                            $jsst_id = NULL;
                            jssupportticket::$jsst_data['edd_order_id'] = $jsst_id_array[0];
                        }else{
                            $jsst_id = NULL;
                        }
                    }
                    // Creating a new ticket is open to everyone; editing an existing one is admin/agent-with-permission only.
                    if ($jsst_id == null) {
                        jssupportticket::$jsst_data['permission_granted'] = true;
                    } else {
                        jssupportticket::$jsst_data['permission_granted'] = JSSTroles::canEditTicketContent();
                    }

                    if (!jssupportticket::$jsst_data['permission_granted']) {
                        JSSTmessage::setMessage(esc_html(__('You are not allowed to edit this ticket', 'js-support-ticket')), 'error', 'agent-permissions');
                    }

                    if (jssupportticket::$jsst_data['permission_granted']) {
                        JSSTincluder::getJSModel('ticket')->getTicketsForForm($jsst_id,$jsst_formid);

                        if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce') && !is_admin() && !JSSTincluder::getObjectClass('user')->isguest()){
                            $jsst_selected = false;
                            $jsst_paidsupportid = absint( JSSTrequest::getVar('paidsupportid',null,0) );
                            if($jsst_paidsupportid){
                                //$jsst_paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->uid(), $jsst_paidsupportid);
								$jsst_paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->wpuid(), $jsst_paidsupportid);
                                if($jsst_paidsupport){
                                    jssupportticket::$jsst_data['paidsupport'] = $jsst_paidsupport[0];
                                    $jsst_selected = true;
                                }
                            }
                            if(!$jsst_selected){
                                //$jsst_paidsupportitems = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->uid());
								$jsst_paidsupportitems = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->wpuid());
                                if(count($jsst_paidsupportitems) == 1){
                                    jssupportticket::$jsst_data['paidsupport'] = $jsst_paidsupportitems[0];
                                }else{
                                    jssupportticket::$jsst_data['paidsupportitems'] = $jsst_paidsupportitems;
                                }
                            }
                        }

                    }
                    // $jsst_layout = apply_filters( 'jsst_agent_add_ticket_redirect', $jsst_layout );
                    // if($jsst_layout == 'staffaddticket' && in_array('agent',jssupportticket::$_active_addons)){
                    //     $jsst_per_task = ($jsst_id == null) ? 'Add Ticket' : 'Edit Ticket';
                    //     jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_per_task);
                    // }
                    JSSTincluder::getJSModel('jssupportticket')->updateColorFile();
                    break;
                case 'admin_ticketdetail':
                case 'ticketdetail':
                    $jsst_id = absint( JSSTrequest::getVar('jssupportticketid') );
                    jssupportticket::$jsst_data['permission_granted'] = true;
                    jssupportticket::$jsst_data['user_staff'] = false;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        jssupportticket::$jsst_data['user_staff'] = true;
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Ticket');
                    }
                    if (jssupportticket::$jsst_data['permission_granted']) {
                        JSSTincluder::getJSModel('ticket')->getTicketForDetail($jsst_id);
                        //check if envato license support has expired
                        if(in_array('envatovalidation', jssupportticket::$_active_addons) && !empty(jssupportticket::$jsst_data[0]->envatodata)){
                            $jsst_envlicense = json_decode(jssupportticket::$jsst_data[0]->envatodata, true);
                            if(!empty($jsst_envlicense['supporteduntil']) && date_i18n('Y-m-d') > date_i18n('Y-m-d',strtotime($jsst_envlicense['supporteduntil']))){
                                JSSTmessage::setMessage(esc_html(__('Support for this Envato license has expired', 'js-support-ticket')), 'error');
                            }
                            jssupportticket::$jsst_data[0]->envatodata = $jsst_envlicense;
                        }
                    }
                    break;
                case 'myticket':
                    $jsst_list = JSSTrequest::getVar('list');
                    JSSTincluder::getJSModel('ticket')->getMyTickets($jsst_list);
                    break;
                case 'ticketstatus':
                    break;
                case 'visitormessagepage':
                    break;
                default:
                    exit;

            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'ticket');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    function canaddfile($jsst_layout) {
        $jsst_nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $jsst_nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if(!is_admin() && jssupportticketphplib::JSST_strpos($jsst_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    function closeticket() {
        $jsst_id = JSSTrequest::getVar('ticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'close-ticket-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('ticket')->closeTicket( absint( $jsst_id ) );
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=tickets");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    function lockticket() {
        $jsst_id = JSSTrequest::getVar('ticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'lock-ticket-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        JSSTincluder::getJSModel('ticket')->lockTicket( absint( $jsst_id ) );
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_id));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    function unlockticket() {
        $jsst_id = JSSTrequest::getVar('ticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'unlock-ticket-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        JSSTincluder::getJSModel('ticket')->unLockTicket( absint( $jsst_id ) );
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_id));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    /**
     * The URL of the "add ticket" form the current user came from, so a failed
     * submission can return them to it. Mirrors the failure redirects below.
     * (Roadmap 3.2-CORE-03)
     */
    static function getAddTicketUrl($jsst_data) {
        $jsst_formid = isset($jsst_data['multiformid']) ? $jsst_data['multiformid'] : null;
        $jsst_hasmultiform = in_array('multiform', jssupportticket::$_active_addons) && $jsst_formid !== null && $jsst_formid !== '';

        if (is_admin()) {
            $jsst_url = "admin.php?page=ticket&jstlay=addticket";
            if ($jsst_hasmultiform) {
                $jsst_url .= "&formid=" . rawurlencode($jsst_formid);
            }
            return admin_url($jsst_url);
        }

        $jsst_isstaff = in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff();
        $jsst_args = array(
            'jstmod' => $jsst_isstaff ? 'agent' : 'ticket',
            'jstlay' => $jsst_isstaff ? 'staffaddticket' : 'addticket',
        );
        if ($jsst_hasmultiform) {
            $jsst_args['formid'] = $jsst_formid;
        }
        return jssupportticket::makeUrl($jsst_args);
    }

    static function saveticket() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        $jsst_data = JSSTrequest::get('post');
        // The redirects below read multiformid unconditionally. A custom
        // template or a page builder that strips hidden inputs used to make that
        // a PHP notice on every failed submission. (Roadmap 3.2-CORE-03)
        if (!isset($jsst_data['multiformid']) || $jsst_data['multiformid'] === '') {
            $jsst_data['multiformid'] = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
        }
        if (! wp_verify_nonce( $jsst_nonce, 'save-ticket-'.$jsst_id) ) {
            // The nonce lives in the form's action URL, so a page served from a
            // full-page cache (or simply left open for a day) submits an expired
            // one. Ending in a blank "Security check Failed" screen lost the
            // customer and the message they had typed; send them back to the
            // form with their input and an explanation instead. The ticket is
            // still not stored. (Roadmap 3.2-CORE-03)
            JSSTmessage::setMessage(esc_html(__('Your session expired before the ticket was sent. Please check the details below and submit again.', 'js-support-ticket')), 'error');
            JSSTformfield::setFormData($jsst_data);
            wp_safe_redirect(self::getAddTicketUrl($jsst_data));
            exit;
        }
        $jsst_result = JSSTincluder::getJSModel('ticket')->storeTickets($jsst_data);
        // A ticket id is a positive integer. Anything else (false, 0, null)
        // means the ticket was not stored.
        $jsst_result = (is_numeric($jsst_result) && (int) $jsst_result > 0) ? (int) $jsst_result : false;
        if (is_admin()) {
            if($jsst_result == false){
                $jsst_url = admin_url("admin.php?page=ticket&jstlay=addticket");
				if(in_array('multiform', jssupportticket::$_active_addons)){
					$jsst_formid = $jsst_data['multiformid'];
					$jsst_url = admin_url("admin.php?page=ticket&jstlay=addticket&formid=".esc_attr($jsst_formid));
				}	
            }else{
                $jsst_url = admin_url("admin.php?page=ticket&jstlay=tickets");
            }
        } else {
            if (JSSTincluder::getObjectClass('user')->uid() == 0) { // visitor
                if ($jsst_result == false) { // error on captcha or ticket validation
                    $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket'));
					if(in_array('multiform', jssupportticket::$_active_addons)){
						$jsst_formid = $jsst_data['multiformid'];
						$jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket', 'formid'=> $jsst_formid));
					}	
                } else { // all things perfect
                    if(JSSTmergedaddon::featureEnabled('actions')){
                        $jsst_ticketid = $jsst_result;
                        $jsst_token = JSSTincluder::getJSModel('ticket')->getTicketToken($jsst_ticketid);
                        $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'visitormessagepage', 'jssupportticketid'=>$jsst_token));
                    }else{
                        $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'controlpanel'));
                    }
                }
            } else {
                if ($jsst_result == false) { // error on captcha or ticket validation
                    $jsst_addticket = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffaddticket' : 'addticket';
                    $jsst_module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
                    $jsst_url = jssupportticket::makeUrl(array('jstmod'=>$jsst_module1, 'jstlay'=>$jsst_addticket));
					if(in_array('multiform', jssupportticket::$_active_addons)){
						$jsst_formid = $jsst_data['multiformid'];
						$jsst_url = jssupportticket::makeUrl(array('jstmod'=>$jsst_module1, 'jstlay'=>$jsst_addticket, 'formid'=> $jsst_formid));
					}	
                } else {
                    $jsst_myticket = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket' : 'myticket';
                    $jsst_module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
                    $jsst_url = jssupportticket::makeUrl(array('jstmod'=>$jsst_module1, 'jstlay'=>$jsst_myticket));
                }
            }
        }
        if($jsst_result == false){
            JSSTformfield::setFormData($jsst_data);
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changestatus() {
        $jsst_data = JSSTrequest::get('post');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-status-'.$jsst_data['ticketid']) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('ticket')->tickChangeStatus($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_data['ticketid']));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function transferdepartment() {
        $jsst_data = JSSTrequest::get('post');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'transfer-department-'.$jsst_data['ticketid']) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('ticket')->tickDepartmentTransfer($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_data['ticketid']));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function assigntickettostaff() {
        $jsst_data = JSSTrequest::get('post');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'assign-ticket-to-staff-'.$jsst_data['ticketid']) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('ticket')->assignTicketToStaff($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_data['ticketid']));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deleteticket() {
        $jsst_id = JSSTrequest::getVar('ticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-ticket-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('ticket')->removeTicket( absint( $jsst_id ) );
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=tickets");
        } elseif ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffmyticket'));
        } elseif (JSSTincluder::getObjectClass('user')->uid() == 0) { // visitor
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_id));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function enforcedeleteticket() {
        // Sanitize and validate ticket ID
        $jsst_id = JSSTrequest::getVar('ticketid');
        if (!is_numeric($jsst_id) || intval($jsst_id) <= 0) {
            die('Invalid ticket ID');
        }
        $jsst_id = absint($jsst_id); // Ensure positive integer

        // Validate Nonce
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'enforce-delete-ticket-' . $jsst_id)) {
            die('Security check Failed');
        }

        // Only allow admins to delete any ticket
        if (!current_user_can('manage_options')) {
            die('You do not have permission to delete this ticket');
        }

        // Delete the ticket securely
        JSSTincluder::getJSModel('ticket')->removeEnforceTicket($jsst_id);

        // Redirect securely
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=tickets");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'myticket'));
        }
        
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changepriority() {
        $jsst_id         = absint(JSSTrequest::getVar('ticketid'));
        $jsst_priorityid = absint(JSSTrequest::getVar('priority'));
        $jsst_nonce      = JSSTrequest::getVar('_wpnonce');

        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Change Ticket Priority');
            if ($jsst_allow == 0) {
                wp_die( esc_html__( 'You are not allowed', 'js-support-ticket' ) );
            }
        }

        if ( ! is_user_logged_in() ) {
            wp_die( esc_html__( 'You must be logged in.', 'js-support-ticket' ), esc_html__( 'Access Denied', 'js-support-ticket' ), array( 'response' => 403 ) );
        }

        if ( ! wp_verify_nonce( $jsst_nonce, 'action-ticket-' . $jsst_id ) ) {
            wp_die( esc_html__( 'Security check failed.', 'js-support-ticket' ), esc_html__( 'Security Error', 'js-support-ticket' ), array( 'response' => 403 ) );
        }
        if (!is_numeric($jsst_id)){
            wp_die( esc_html__( 'You are not allowed', 'js-support-ticket' ) );
        }
        if (!is_numeric($jsst_priorityid)){
            wp_die( esc_html__( 'You are not allowed', 'js-support-ticket' ) );
        }

        JSSTincluder::getJSModel('ticket')->changeTicketPriority($jsst_id, $jsst_priorityid);

        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_id));
        }

        wp_safe_redirect($jsst_url);
        exit;
    }

    static function reopenticket() { // for user
        $jsst_ticketid = JSSTrequest::getVar('ticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'reopen-ticket-'.$jsst_ticketid) ) {
            die( 'Security check Failed' );
        }
        $jsst_data['ticketid'] = absint( $jsst_ticketid );
        JSSTincluder::getJSModel('ticket')->reopenTicket($jsst_data);
        $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket" . esc_attr($jsst_url));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_data['ticketid']));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function actionticket() {
        $jsst_data = JSSTrequest::get('post');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'action-ticket-'.$jsst_data['ticketid']) ) {
            die( 'Security check Failed' );
        }
        /* to handle actions */
        switch ($jsst_data['actionid']) {
            case 1: /* Change Priority Ticket */
                JSSTincluder::getJSModel('ticket')->changeTicketPriority($jsst_data['ticketid'], $jsst_data['priority']);
                $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                break;
            case 2: /* close ticket */
                JSSTincluder::getJSModel('ticket')->closeTicket($jsst_data['ticketid']);
                $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                break;
            case 3: /* Reopen Ticket */
                JSSTincluder::getJSModel('ticket')->reopenTicket($jsst_data);
                $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                break;
            case 4: /* Lock Ticket */
                if(JSSTmergedaddon::featureEnabled('actions')){
                    JSSTincluder::getJSModel('actions')->lockTicket($jsst_data['ticketid']);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 5: /* Unlock ticket */
                if(JSSTmergedaddon::featureEnabled('actions')){
                    JSSTincluder::getJSModel('actions')->unLockTicket($jsst_data['ticketid']);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 6: /* Banned Email */
                if(JSSTmergedaddon::featureEnabled('banemail')){
                    JSSTincluder::getJSModel('ticket')->banEmail($jsst_data);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 7: /* Unban Email */
                if(JSSTmergedaddon::featureEnabled('banemail')){
                    JSSTincluder::getJSModel('ticket')->unbanEmail($jsst_data);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 8: /* Mark over due */
                if(in_array('overdue', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('overdue')->markOverDueTicket($jsst_data);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 9: /* In Progress */
                if(JSSTmergedaddon::featureEnabled('actions')){
                    JSSTincluder::getJSModel('ticket')->markTicketInProgress($jsst_data);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 10: /* ban Email & close ticket */
                JSSTincluder::getJSModel('ticket')->banEmailAndCloseTicket($jsst_data);
                $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                break;
            case 11: /* unMark over due */
                if(in_array('overdue', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('overdue')->unMarkOverDueTicket($jsst_data);;
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
        }

        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket" . $jsst_url);
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_data['ticketid']));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    /**
     * Replace a ticket's tags with what was submitted. (Roadmap 4.0-CORE-17)
     *
     * Tagging is an agent-side classification tool, so it needs the same
     * permission as editing the ticket. Customers never reach this task.
     */
    static function savetickettags() {
        $jsst_id = absint(JSSTrequest::getVar('ticketid'));
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');

        if (!is_user_logged_in()) {
            wp_die(esc_html__('You must be logged in.', 'js-support-ticket'), esc_html__('Access Denied', 'js-support-ticket'), array('response' => 403));
        }
        if (!wp_verify_nonce($jsst_nonce, 'ticket-tags-' . $jsst_id)) {
            wp_die(esc_html__('Security check failed.', 'js-support-ticket'), esc_html__('Security Error', 'js-support-ticket'), array('response' => 403));
        }
        if ($jsst_id <= 0) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        /* Tagging is queue organisation — the same family as priority and
           department — so it rides on the state capability rather than earning
           a fifth one of its own. Administrators and add-on agents are
           unaffected. (Roadmap 4.0-SEC-04) */
        $jsst_allowed = JSSTroles::canChangeTicketState();
        if (!$jsst_allowed && in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = (JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Ticket') == 1);
        }
        if (!$jsst_allowed) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_changed = JSSTincluder::getJSModel('tag')->setTicketTags($jsst_id, JSSTrequest::getVar('tickettags', ''));
        if (empty($jsst_changed['added']) && empty($jsst_changed['removed'])) {
            JSSTmessage::setMessage(esc_html(__('Tags are unchanged', 'js-support-ticket')), 'updated');
        } else {
            JSSTmessage::setMessage(esc_html(__('Tags have been saved', 'js-support-ticket')), 'updated');
        }

        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . $jsst_id);
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'ticketdetail', 'jssupportticketid' => $jsst_id));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function showticketstatus() {
        $jsst_token = JSSTrequest::getVar('token');
        if ($jsst_token == null) { // in case it come from ticket status form
            $jsst_nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $jsst_nonce, 'show-ticket-status') ) {
                //die( 'Security check Failed' );
            }
            $jsst_emailaddress = sanitize_email( JSSTrequest::getVar('email') );
            $jsst_trackingid = JSSTrequest::getVar('ticketid');
            $jsst_tickettoken = JSSTrequest::getVar('tickettoken');
            if(!empty($jsst_emailaddress) AND !empty($jsst_trackingid)){
                $jsst_token = JSSTincluder::getJSModel('ticket')->getTokenByEmailAndTrackingId($jsst_emailaddress, $jsst_trackingid);
            }else if(!empty($jsst_tickettoken)){
                $jsst_token = $jsst_tickettoken;
            }
            if($jsst_token){
                include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                $jsst_encoder = new JSSTEncoder();
                $jsst_token = $jsst_encoder->encrypt(wp_json_encode(array('token' => $jsst_token, 'sitelink' => get_option('jsst_encripted_site_link'))));
                jssupportticketphplib::JSST_setcookie('js-support-ticket-token-tkstatus',$jsst_token ,0, COOKIEPATH);
                if ( SITECOOKIEPATH != COOKIEPATH ){
                    jssupportticketphplib::JSST_setcookie('js-support-ticket-token-tkstatus',$jsst_token ,0, SITECOOKIEPATH);
                }
                $jsst_ticketid = JSSTincluder::getJSModel('ticket')->getTicketidForVisitorUsingToken($jsst_token);
                if ($jsst_ticketid) {
                    $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_ticketid));
                } else {
                    $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus'));
                    JSSTmessage::setMessage(esc_html(__('Record not found', 'js-support-ticket')), 'error');
                }
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus'));
                JSSTmessage::setMessage(esc_html(__('Record not found', 'js-support-ticket')), 'error');
            }
        } else {
            jssupportticketphplib::JSST_setcookie('js-support-ticket-token-tkstatus',$jsst_token ,0, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('js-support-ticket-token-tkstatus',$jsst_token ,0, SITECOOKIEPATH);
            }
            $jsst_ticketid = JSSTincluder::getJSModel('ticket')->getTicketidForVisitor($jsst_token);
            if ($jsst_ticketid) {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_ticketid));
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus'));
                JSSTmessage::setMessage(esc_html(__('Record not found', 'js-support-ticket')), 'error');
            }
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function downloadall() {
        $jsst_id         = absint( JSSTrequest::getVar( 'id' ) );
        $jsst_downloadid = absint( JSSTrequest::getVar('downloadid') );
        $jsst_nonce      = JSSTrequest::getVar('_wpnonce');

        if ( ! wp_verify_nonce( $jsst_nonce, 'download-all-' . $jsst_downloadid ) ) {
            wp_die( esc_html__( 'Security check failed.', 'js-support-ticket' ), esc_html__( 'Security Error', 'js-support-ticket' ), array( 'response' => 403 ) );
        }

        JSSTincluder::getJSModel('attachment')->getAllDownloads();

        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid()));
        }
        
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function downloadallforreply() {
        $jsst_downloadid = JSSTrequest::getVar('downloadid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'download-all-for-reply-'.$jsst_downloadid) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('attachment')->getAllReplyDownloads();
        if (is_admin()) {
          $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail");
          } else {
          $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>'$jsst_id','jsstpageid'=>jssupportticket::getPageid()));
          }
          wp_safe_redirect($jsst_url);
          exit;
    }

    function downloadbyid(){
        $jsst_id = absint( JSSTrequest::getVar('id') );
        JSSTincluder::getJSModel('attachment')->getDownloadAttachmentById($jsst_id);
    }

    /**
     * Show an attachment in the page rather than downloading it.
     *
     * The same permission check as downloadbyid — it is the same method — but
     * images come back inline so a thumbnail or a lightbox can use it. This
     * exists because attachments used to be linked straight into the uploads
     * directory, where no permission check runs at all. (Roadmap 4.0-SEC-03)
     */
    function viewattachment(){
        $jsst_id = absint( JSSTrequest::getVar('id') );
        JSSTincluder::getJSModel('attachment')->getDownloadAttachmentById($jsst_id, true);
    }


    function downloadbyname(){
        $jsst_name = JSSTrequest::getVar('name');
        $jsst_id = absint( JSSTrequest::getVar('id') );
        $jsst_name = jssupportticketphplib::JSST_clean_file_path($jsst_name);
        JSSTincluder::getJSModel('attachment')->getDownloadAttachmentByName($jsst_name,$jsst_id);
    }

    function mergeticket() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'merge-ticket') ) {
            die( 'Security check Failed' );
        }
        if (!in_array('mergeticket', jssupportticket::$_active_addons)) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('mergeticket')->storeMergeTicket($jsst_data);
        // After a merge the ticket worth looking at is the canonical one. With a
        // multi-select merge there is no single source to return to anyway.
        // (Roadmap 4.0-CORE-04)
        $jsst_landon = isset($jsst_data['primaryticket']) ? absint($jsst_data['primaryticket']) : 0;
        if ($jsst_landon === 0) {
            $jsst_landon = absint(JSSTrequest::getVar('secondaryticket'));
        }
        if(is_admin()){
             $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . $jsst_landon);
        }else{
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_landon));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    /**
     * Apply one action to a selection of tickets. (Roadmap 4.0-CORE-05)
     *
     * One nonce covers the whole selection; each ticket then runs the ordinary
     * single-ticket path, which enforces its own permission checks.
     */
    static function bulkaction() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-bulk-action') ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        if (!JSSTmergedaddon::coreOwns('actions')) {
            JSSTmessage::setMessage(esc_html(__('Bulk actions are not available while the Ticket Actions add-on is active.', 'js-support-ticket')), 'error');
        } else {
            JSSTincluder::getJSModel('actions')->runBulkAction($jsst_data);
        }
        if (is_admin()) {
            $jsst_url = admin_url('admin.php?page=ticket');
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    /**
     * Save the queue as it currently stands under a name. (Roadmap 4.0-CORE-18)
     *
     * The save form carries a hidden copy of every filter the queue is showing,
     * which is what gets stored — so the view is the queue the agent is looking
     * at, whether that came from the filter controls, from the search box or
     * from another saved view. It cannot read the parsed search state instead:
     * this runs on init, and the admin search state is not built until
     * admin_init.
     */
    static function savequeueview() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-queue-view') ) {
            die( 'Security check Failed' );
        }
        if (!JSSTticketaction::canRun('View Ticket')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed to do this', 'js-support-ticket')), 'error', 'agent-permissions');
            wp_safe_redirect(admin_url('admin.php?page=ticket'));
            exit;
        }
        $jsst_result = JSSTqueue::saveView(
            JSSTrequest::getVar('viewname', 'post', ''),
            JSSTqueue::filtersFromRequest()
        );
        if ($jsst_result === true) {
            JSSTmessage::setMessage(esc_html(__('View saved', 'js-support-ticket')), 'updated');
        } else {
            JSSTmessage::setMessage($jsst_result, 'error');
        }
        wp_safe_redirect(admin_url('admin.php?page=ticket'));
        exit;
    }

    /**
     * Delete one saved view. (Roadmap 4.0-CORE-18)
     *
     * JSSTqueue::deleteView() scopes the delete to the current user, so a view
     * id belonging to somebody else matches nothing.
     */
    static function deletequeueview() {
        $jsst_viewid = absint( JSSTrequest::getVar('viewid') );
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-delete-queue-view-'.$jsst_viewid) ) {
            die( 'Security check Failed' );
        }
        JSSTqueue::deleteView($jsst_viewid);
        JSSTmessage::setMessage(esc_html(__('View deleted', 'js-support-ticket')), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=ticket'));
        exit;
    }

    /**
     * Undo one merge. Provided by the Merge Ticket add-on.
     */
    function unmergeticket() {
        $jsst_sourceid = absint( JSSTrequest::getVar('sourceticket') );
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'unmerge-ticket-'.$jsst_sourceid) ) {
            die( 'Security check Failed' );
        }
        if (!in_array('mergeticket', jssupportticket::$_active_addons)) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_primaryid = absint( JSSTrequest::getVar('primaryticket') );
        JSSTincluder::getJSModel('mergeticket')->unmergeTicket(array(
            'sourceticket'  => $jsst_sourceid,
            'primaryticket' => $jsst_primaryid,
        ));
        if(is_admin()){
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . $jsst_primaryid);
        }else{
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_primaryid));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }
}
$jsst_ticketController = new JSSTticketController();
?>
