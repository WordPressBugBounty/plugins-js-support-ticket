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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        JSSTincluder::getJSModel('actions')->lockTicket( absint( $jsst_id ) );
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        JSSTincluder::getJSModel('actions')->unLockTicket( absint( $jsst_id ) );
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
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        $jsst_data = JSSTrequest::get('post');
        /* SECURITY (reported 28 September 2026, CVSS 5.4): the nonce is checked
           against the id that is WRITTEN, read once from the same place
           storeTickets() reads it. It used to be getVar('id'), which prefers
           the query string - so "?id=" in the URL made the check
           'save-ticket-' (the new-ticket nonce, which anyone can obtain) while
           the ticket edited was whatever id the POST body named. An id that is
           present but not a plain number is refused, not defaulted. */
        $jsst_id = isset($jsst_data['id']) ? trim((string) $jsst_data['id']) : '';
        if ($jsst_id !== '' && !ctype_digit($jsst_id)) {
            wp_die(esc_html__('Security check failed.', 'js-support-ticket'), esc_html__('Security Error', 'js-support-ticket'), array('response' => 403));
        }
        $jsst_data['id'] = $jsst_id;
        /* Set by email piping, which calls the model directly. From a browser
           it would skip the captcha and the banned-sender / ticket-limit
           checks in storeTickets(). */
        unset($jsst_data['ticketviaemail']);
        /* Whose ticket it is comes from the session, not the form. The form's
           hidden `uid` let a signed-in customer file a ticket in another
           customer's name. Administrators and agents still choose the customer
           when they open a ticket on someone's behalf; an edit keeps the stored
           owner regardless (storeTickets). */
        if (!current_user_can('manage_options')
                && !(in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())) {
            $jsst_data['uid'] = (int) JSSTincluder::getObjectClass('user')->uid();
        }
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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

    /**
     * Everything the collaboration panel does. (Roadmap 4.5-FE-08)
     *
     * One task rather than six, because they are six buttons on one panel
     * about one ticket, and six nonces on one screen is six ways to get the
     * spelling wrong. Each branch checks the permission that governs it - and
     * they are existing permissions: following a ticket is seeing it, naming a
     * secondary agent is assignment, approving a reply is replying.
     */
    static function savecollab() {
        $jsst_ticketid = absint( JSSTrequest::getVar('ticketid', 'post', 0) );
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-collab-' . $jsst_ticketid) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!class_exists('JSSTcollab') || $jsst_ticketid <= 0) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_actor = JSSTcapability::actor();
        $jsst_me = isset($jsst_actor['staffid']) ? (int) $jsst_actor['staffid'] : 0;
        $jsst_isadmin = (isset($jsst_actor['kind']) && $jsst_actor['kind'] === JSSTcapability::ACTOR_ADMIN);
        if (!JSSTcapability::can(JSSTcapability::TICKET_VIEW, array('ticket' => $jsst_ticketid), $jsst_actor)) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_result = true;

        if (JSSTrequest::getVar('collabfollow', 'post', '') !== '') {
            /* Following and unfollowing yourself needs nothing beyond being
               able to see the ticket, which was checked above. */
            if ($jsst_me > 0) {
                JSSTcollab::isWatching($jsst_ticketid, $jsst_me)
                    ? JSSTcollab::removeWatcher($jsst_ticketid, $jsst_me)
                    : JSSTcollab::addWatcher($jsst_ticketid, $jsst_me, JSSTcollab::ROLE_WATCHER, JSSTcollab::SOURCE_MANUAL, $jsst_me);
                JSSTmessage::setMessage(esc_html(__('Saved.', 'js-support-ticket')), 'updated');
            }
        } elseif (JSSTrequest::getVar('collabadd', 'post', '') !== '') {
            $jsst_who = absint( JSSTrequest::getVar('collabstaffid', 'post', 0) );
            $jsst_role = (JSSTrequest::getVar('collabrole', 'post', '') === JSSTcollab::ROLE_SECONDARY)
                ? JSSTcollab::ROLE_SECONDARY : JSSTcollab::ROLE_WATCHER;
            /* Putting somebody else on a ticket as a second pair of hands is
               assignment by another name, so it is governed by assignment. */
            if ($jsst_role === JSSTcollab::ROLE_SECONDARY
                    && !JSSTcapability::can(JSSTcapability::TICKET_ASSIGN, array('ticket' => $jsst_ticketid), $jsst_actor)) {
                $jsst_result = esc_html(__('You may not put somebody else on this ticket.', 'js-support-ticket'));
            } elseif ($jsst_who > 0) {
                $jsst_result = JSSTcollab::addWatcher($jsst_ticketid, $jsst_who, $jsst_role, JSSTcollab::SOURCE_MANUAL, $jsst_me);
                if ($jsst_result === true) {
                    JSSTmessage::setMessage(esc_html(__('Added.', 'js-support-ticket')), 'updated');
                    /* Tell them. (Roadmap 4.5-FE-08)

                       `addWatcher()` writes a row and raises no event, so being
                       put on a ticket used to reach the person only when
                       something ELSE happened on it afterwards - and on a quiet
                       ticket, never. The agent was on it and had no way to know.

                       Sent here rather than from `addWatcher()` because this is
                       the only place a PERSON does it: the automatic sources
                       (answering, noting, being mentioned) already notify
                       through their own events, and moving it down would tell
                       everybody they had been added every time they replied.

                       Not sent to yourself - nobody is told what they just did,
                       which is the rule the rest of this system follows. */
                    if (class_exists('JSSTnotifications') && $jsst_who !== $jsst_me) {
                        JSSTnotifications::add(array(
                            'staffid'   => $jsst_who,
                            'category'  => JSSTnotifications::CAT_WATCHED,
                            'eventname' => 'collab.watcher_added',
                            'ticketid'  => $jsst_ticketid,
                            'title'     => ($jsst_role === JSSTcollab::ROLE_SECONDARY)
                                ? esc_html(__('You were asked to work a ticket', 'js-support-ticket'))
                                : esc_html(__('You were added to a ticket', 'js-support-ticket')),
                            'body'      => ($jsst_role === JSSTcollab::ROLE_SECONDARY)
                                ? esc_html(__('Somebody put you on this ticket as a second pair of hands. It is not assigned to you.', 'js-support-ticket'))
                                : esc_html(__('Somebody added you to this ticket so you are kept informed. It is not assigned to you.', 'js-support-ticket')),
                            'actions'   => JSSTnotifications::actionsFor('collab.watcher_added', $jsst_ticketid, $jsst_who),
                        ));
                    }
                }
            }
        } elseif (JSSTrequest::getVar('collabremove', 'post', '') !== '') {
            $jsst_who = absint( JSSTrequest::getVar('collabremove', 'post', 0) );
            /* Taking yourself off needs nothing; taking somebody else off is
               the same decision as putting them on. */
            if ($jsst_who !== $jsst_me
                    && !JSSTcapability::can(JSSTcapability::TICKET_ASSIGN, array('ticket' => $jsst_ticketid), $jsst_actor)) {
                $jsst_result = esc_html(__('You may not take somebody else off this ticket.', 'js-support-ticket'));
            } else {
                JSSTcollab::removeWatcher($jsst_ticketid, $jsst_who);
                JSSTmessage::setMessage(esc_html(__('Removed.', 'js-support-ticket')), 'updated');
            }
        } elseif (JSSTrequest::getVar('collabshare', 'post', '') !== '') {
            if (!JSSTcapability::can(JSSTcapability::TICKET_REPLY, array('ticket' => $jsst_ticketid), $jsst_actor)) {
                $jsst_result = esc_html(__('You may not write a reply on this ticket.', 'js-support-ticket'));
            } else {
                $jsst_shared = JSSTcollab::shareDraft($jsst_ticketid, $jsst_me,
                    JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData(JSSTrequest::getVar('collabbody', 'post', '')),
                    'reply', absint( JSSTrequest::getVar('collabapprover', 'post', 0) ));
                if (is_numeric($jsst_shared)) {
                    $jsst_approver = absint( JSSTrequest::getVar('collabapprover', 'post', 0) );
                    JSSTmessage::setMessage($jsst_approver > 0
                        ? esc_html(__('Sent for approval. Nothing has gone to the customer.', 'js-support-ticket'))
                        : esc_html(__('Shared with the desk. Nothing has gone to the customer.', 'js-support-ticket')), 'updated');
                    /* Tell the approver. (Roadmap 4.5-FE-08)

                       `shareDraft()` writes the row and raises no event, so
                       "Sent for approval" told the SENDER something had
                       happened and told the approver nothing at all. The draft
                       then sat in `js_ticket_collab_drafts` with nobody
                       waiting on it, which is the worst shape for a queue that
                       holds up a customer reply.

                       CAT_MINE, not CAT_WATCHED: this is not news about a
                       ticket somebody follows, it is a job that will not move
                       until this person does something. */
                    if (class_exists('JSSTnotifications') && $jsst_approver > 0 && $jsst_approver !== $jsst_me) {
                        JSSTnotifications::add(array(
                            'staffid'   => $jsst_approver,
                            'category'  => JSSTnotifications::CAT_MINE,
                            'eventname' => 'collab.approval_requested',
                            'ticketid'  => $jsst_ticketid,
                            'title'     => esc_html(__('A reply is waiting on you', 'js-support-ticket')),
                            'body'      => esc_html(__('Somebody wrote a reply and asked you to look at it before it goes. Nothing has gone to the customer.', 'js-support-ticket')),
                            'actions'   => JSSTnotifications::actionsFor('collab.approval_requested', $jsst_ticketid, $jsst_approver),
                        ));
                    }
                } else {
                    $jsst_result = $jsst_shared;
                }
            }
        } elseif (JSSTrequest::getVar('collabdecide', 'post', '') !== '') {
            $jsst_draftid = absint( JSSTrequest::getVar('collabdraft', 'post', 0) );
            $jsst_approve = (JSSTrequest::getVar('collabdecide', 'post', '') === 'approve');
            $jsst_result = JSSTcollab::decide($jsst_draftid, $jsst_approve, $jsst_me,
                JSSTrequest::getVar('collabdecidenote', 'post', ''));
            if ($jsst_result === true) {
                JSSTmessage::setMessage($jsst_approve
                    ? esc_html(__('Approved. It still has to be sent by whoever wrote it.', 'js-support-ticket'))
                    : esc_html(__('Sent back with your note.', 'js-support-ticket')), 'updated');
                /* And tell the person who wrote it. (Roadmap 4.5-FE-08)

                   Approving deliberately does not send - the author still has
                   to - so a decision nobody is told about stops the reply
                   dead: the approver believes they have cleared it and the
                   author never learns it was cleared. Read back from the draft
                   rather than trusted from the request, because the author id
                   is not in the form. */
                $jsst_decided = JSSTcollab::draft($jsst_draftid);
                $jsst_author = $jsst_decided ? (int) $jsst_decided->authorid : 0;
                if (class_exists('JSSTnotifications') && $jsst_author > 0 && $jsst_author !== $jsst_me) {
                    JSSTnotifications::add(array(
                        'staffid'   => $jsst_author,
                        'category'  => JSSTnotifications::CAT_MINE,
                        'eventname' => 'collab.approval_decided',
                        'ticketid'  => $jsst_ticketid,
                        'title'     => $jsst_approve
                            ? esc_html(__('Your reply was approved', 'js-support-ticket'))
                            : esc_html(__('Your reply was sent back', 'js-support-ticket')),
                        'body'      => $jsst_approve
                            ? esc_html(__('It is cleared to go. It still has to be sent by you - approving does not send it.', 'js-support-ticket'))
                            : esc_html(__('It was not approved. Open the ticket to read the note that came back with it.', 'js-support-ticket')),
                        'actions'   => JSSTnotifications::actionsFor('collab.approval_decided', $jsst_ticketid, $jsst_author),
                    ));
                }
            }
        } elseif (JSSTrequest::getVar('collabdiscard', 'post', '') !== '') {
            $jsst_result = JSSTcollab::discardDraft(absint( JSSTrequest::getVar('collabdiscard', 'post', 0) ),
                $jsst_me, $jsst_isadmin);
            if ($jsst_result === true) {
                JSSTmessage::setMessage(esc_html(__('Discarded.', 'js-support-ticket')), 'updated');
            }
        }
        if ($jsst_result !== true) {
            JSSTmessage::setMessage($jsst_result, 'error');
        }
        if (is_admin()) {
            $jsst_url = admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . $jsst_ticketid);
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'ticketdetail', 'jssupportticketid' => $jsst_ticketid));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deleteticket() {
        $jsst_id = JSSTrequest::getVar('ticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-ticket-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        /* Who may delete this ticket, asked here because this is the door.
           (Roadmap 4.0-SEC-04)

           There was no answer at this door at all: a nonce, and then the
           deletion. `removeTicket()` does ask - but only inside
           `in_array('agent', $_active_addons) && isUserStaff()`, so on a desk
           without the Agents add-on, or for anybody holding a help desk role
           without a `js_ticket_staff` row, the check was skipped and the ticket
           went. A nonce proves the request came from our own form; it says
           nothing about whether the person is allowed to make it, and hiding
           the button is presentation rather than enforcement.

           TICKET_DELETE is scoped, so the ticket is named: on a desk running
           the add-on the answer varies by department and by who holds the
           ticket. The model's own check stays where it is - two doors on one
           room is the arrangement this codebase uses deliberately. */
        if (class_exists('JSSTcapability')
                && !JSSTcapability::can(JSSTcapability::TICKET_DELETE, array('ticket' => absint($jsst_id)))) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed to delete this ticket', 'js-support-ticket')), 'error', 'agent-permissions');
            wp_safe_redirect(is_admin()
                ? admin_url('admin.php?page=ticket&jstlay=tickets')
                : jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'myticket')));
            exit;
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
            die(esc_html__( 'Invalid ticket ID', 'js-support-ticket' ));
        }
        $jsst_id = absint($jsst_id); // Ensure positive integer

        // Validate Nonce
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'enforce-delete-ticket-' . $jsst_id)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }

        // Only allow admins to delete any ticket
        if (!current_user_can('manage_options')) {
            die(esc_html__( 'You do not have permission to delete this ticket', 'js-support-ticket' ));
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
                /* Guarded like 6 and 7, which is where this was inconsistent:
                   those two refuse when the ban list is not part of this site
                   and this one ran regardless, so a POST could still ban an
                   address on a desk that has no banning. */
                if(JSSTmergedaddon::featureEnabled('banemail')){
                    JSSTincluder::getJSModel('ticket')->banEmailAndCloseTicket($jsst_data);
                    $jsst_url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['ticketid']);
                }
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_data = JSSTrequest::get('post');
        if (!JSSTmergedaddon::coreOwns('actions')) {
            JSSTmessage::setMessage(esc_html(__('Bulk actions are not available while the Ticket Actions add-on is active.', 'js-support-ticket')), 'error');
        } else {
            JSSTincluder::getJSModel('actions')->runBulkAction($jsst_data);
        }
        /* Back to the queue that ran it. An agent applying a bulk action from
           the front-end desk used to land on the customer's "my tickets" page,
           which is not their queue and does not show what they just did.
           (Roadmap 4.5-UX-01) */
        wp_safe_redirect(self::queueUrl());
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        /* Asked of the capability service rather than of the Agents model.
           JSSTticketaction::canRun() answers from whichever of the two
           permission systems it happens to find first, which is the split this
           release exists to close - and a saved view is a queue, so the
           question is whether this person may open one. (Roadmap 4.5-UX-01) */
        if (!JSSTcapability::can(JSSTcapability::QUEUE_VIEW)) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed to do this', 'js-support-ticket')), 'error', 'agent-permissions');
            wp_safe_redirect(self::queueUrl());
            exit;
        }
        /* Sharing is a checkbox on the save form. JSSTqueue::saveView() asks
           again whether this person may share, and quietly saves it privately
           if not - the view is theirs either way. (Roadmap 4.5-UX-01) */
        $jsst_share = JSSTrequest::getVar('viewshared', 'post', '');
        $jsst_result = JSSTqueue::saveView(
            JSSTrequest::getVar('viewname', 'post', ''),
            JSSTqueue::filtersFromRequest(),
            ($jsst_share !== '' && $jsst_share !== '0') ? JSSTqueue::VIEW_SHARED : JSSTqueue::VIEW_PRIVATE
        );
        if ($jsst_result === true) {
            JSSTmessage::setMessage(esc_html(__('View saved', 'js-support-ticket')), 'updated');
        } else {
            JSSTmessage::setMessage($jsst_result, 'error');
        }
        wp_safe_redirect(self::queueUrl());
        exit;
    }

    /**
     * Delete one saved view. (Roadmap 4.0-CORE-18)
     *
     * JSSTqueue::deleteView() scopes the delete to the current user, so a view
     * id belonging to somebody else matches nothing and is reported as such.
     */
    static function deletequeueview() {
        $jsst_viewid = absint( JSSTrequest::getVar('viewid') );
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-delete-queue-view-'.$jsst_viewid) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (JSSTqueue::deleteView($jsst_viewid)) {
            JSSTmessage::setMessage(esc_html(__('View deleted', 'js-support-ticket')), 'updated');
        } else {
            JSSTmessage::setMessage(esc_html(__('This view could not be deleted', 'js-support-ticket')), 'error');
        }
        wp_safe_redirect(self::queueUrl());
        exit;
    }

    /**
     * Remember which columns this agent wants in their queue. (Roadmap 4.5-UX-01)
     *
     * Per person, not per site. The listing configuration an administrator
     * maintains stays the default for everybody who has not chosen; this only
     * ever narrows or widens one agent's own view of the same queue, so it
     * needs no permission beyond being able to open a queue at all.
     */
    static function savequeuecolumns() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-queue-columns') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!JSSTcapability::can(JSSTcapability::QUEUE_VIEW)) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed to do this', 'js-support-ticket')), 'error', 'agent-permissions');
            wp_safe_redirect(self::queueUrl());
            exit;
        }
        $jsst_columns = JSSTrequest::getVar('queuecolumns', 'post', array());
        if (JSSTrequest::getVar('resetcolumns', 'post', '') !== '') {
            JSSTqueueengine::resetColumns(get_current_user_id());
            JSSTmessage::setMessage(esc_html(__('Columns reset to this site\'s defaults', 'js-support-ticket')), 'updated');
        } else {
            $jsst_result = JSSTqueueengine::saveColumns(get_current_user_id(), (array) $jsst_columns);
            if ($jsst_result === true) {
                JSSTmessage::setMessage(esc_html(__('Columns saved', 'js-support-ticket')), 'updated');
            } else {
                JSSTmessage::setMessage($jsst_result, 'error');
            }
        }
        wp_safe_redirect(self::queueUrl());
        exit;
    }

    /**
     * Back to the queue the request came from. (Roadmap 4.5-UX-01)
     *
     * The saved-view and column tasks are reachable from both desks now, and
     * every one of them used to redirect to wp-admin regardless - which for an
     * agent working in the portal meant saving a view and landing on a screen
     * they may not be allowed to open at all.
     */
    private static function queueUrl() {
        if (is_admin()) {
            return admin_url('admin.php?page=ticket');
        }
        /* An agent's queue on the front end is the agent desk; a customer's is
           their own ticket list. Sending a customer to the agent desk would be
           a page they cannot open, and sending an agent to the customer list
           would not show them what they just did. */
        $jsst_isagent = in_array('agent', jssupportticket::$_active_addons)
            && JSSTincluder::getJSModel('agent')->isUserStaff();
        return $jsst_isagent
            ? jssupportticket::makeUrl(array('jstmod' => 'agent', 'jstlay' => 'staffmyticket'))
            : jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'myticket'));
    }

    /**
     * Undo one merge. Provided by the Merge Ticket add-on.
     */
    function unmergeticket() {
        $jsst_sourceid = absint( JSSTrequest::getVar('sourceticket') );
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'unmerge-ticket-'.$jsst_sourceid) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
