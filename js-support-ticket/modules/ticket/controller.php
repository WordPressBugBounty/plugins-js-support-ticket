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
                    $jsst_formid = JSSTrequest::getVar('formid');
					
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
                    jssupportticket::$jsst_data['permission_granted'] = true;

                    if (jssupportticket::$jsst_data['permission_granted']) {
                        JSSTincluder::getJSModel('ticket')->getTicketsForForm($jsst_id,$jsst_formid);

                        if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce') && !is_admin() && !JSSTincluder::getObjectClass('user')->isguest()){
                            $jsst_selected = false;
                            $jsst_paidsupportid = JSSTrequest::getVar('paidsupportid',null,0);
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
                    $jsst_id = JSSTrequest::getVar('jssupportticketid');
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
        JSSTincluder::getJSModel('ticket')->closeTicket($jsst_id);
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
        JSSTincluder::getJSModel('ticket')->lockTicket($jsst_id);
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
        JSSTincluder::getJSModel('ticket')->unLockTicket($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_id));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function saveticket() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-ticket-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        $jsst_result = JSSTincluder::getJSModel('ticket')->storeTickets($jsst_data);
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
                    if(in_array('actions',jssupportticket::$_active_addons)){
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
        JSSTincluder::getJSModel('ticket')->removeTicket($jsst_id);
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
        $jsst_id = JSSTrequest::getVar('ticketid');
        $jsst_priorityid = JSSTrequest::getVar('priority');
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
        $jsst_data['ticketid'] = $jsst_ticketid;
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
                if(in_array('actions', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('actions')->lockTicket($jsst_data['ticketid']);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 5: /* Unlock ticket */
                if(in_array('actions', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('actions')->unLockTicket($jsst_data['ticketid']);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 6: /* Banned Email */
                if(in_array('banemail', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('ticket')->banEmail($jsst_data);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
                break;
            case 7: /* Unban Email */
                if(in_array('banemail', jssupportticket::$_active_addons)){
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
                if(in_array('actions', jssupportticket::$_active_addons)){
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

    static function showticketstatus() {
        $jsst_token = JSSTrequest::getVar('token');
        if ($jsst_token == null) { // in case it come from ticket status form
            $jsst_nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $jsst_nonce, 'show-ticket-status') ) {
                //die( 'Security check Failed' );
            }
            $jsst_emailaddress = JSSTrequest::getVar('email');
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
        $jsst_id = JSSTrequest::getVar('id');
        JSSTincluder::getJSModel('attachment')->getAllDownloads();
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>'$jsst_id','jsstpageid'=>jssupportticket::getPageid()));
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
        $jsst_id = JSSTrequest::getVar('id');
        JSSTincluder::getJSModel('attachment')->getDownloadAttachmentById($jsst_id);
    }


    function downloadbyname(){
        $jsst_name = JSSTrequest::getVar('name');
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_name = jssupportticketphplib::JSST_clean_file_path($jsst_name);
        JSSTincluder::getJSModel('attachment')->getDownloadAttachmentByName($jsst_name,$jsst_id);
    }

    function mergeticket() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'merge-ticket') ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('mergeticket')->storeMergeTicket($jsst_data);
        if(is_admin()){
             $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" .esc_attr($jsst_data['secondaryticket']));
        }else if( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_data['secondaryticket']));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }
}
$jsst_ticketController = new JSSTticketController();
?>
