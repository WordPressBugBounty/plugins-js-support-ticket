<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTticketController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        if (is_admin()) {
            $defaultlayout = "tickets";
        } else
            $defaultlayout = "myticket";
        $layout = JSSTrequest::getLayout('jstlay', null, $defaultlayout);
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_tickets':
                    $list = JSSTrequest::getVar('list');
                    JSSTincluder::getJSModel('ticket')->getTicketsForAdmin($list);
                    //JSSTincluder::getJSModel('emailpiping')->readEmails();
                    break;
                case 'admin_addticket':
                case 'addticket':

                    $id = JSSTrequest::getVar('jssupportticketid','',null);
                    $formid = JSSTrequest::getVar('formid');
					
                    if($formid == null){
                        $formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
                    }
                    // below code to is hanlde parameters for easy digital downloads and woocommerce
                    if($id != null && jssupportticketphplib::JSST_strstr($id, '_')){
                        $id_array = jssupportticketphplib::JSST_explode('_', $id);
                        if($id_array[1] == 10){// tikcet id
                            $id = $id_array[0];
                        }elseif($id_array[1] == 11){ // edd order id
                            $id = NULL;
                            jssupportticket::$_data['edd_order_id'] = $id_array[0];
                        }else{
                            $id = NULL;
                        }
                    }
                    jssupportticket::$_data['permission_granted'] = true;

                    if (jssupportticket::$_data['permission_granted']) {
                        JSSTincluder::getJSModel('ticket')->getTicketsForForm($id,$formid);

                        if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce') && !is_admin() && !JSSTincluder::getObjectClass('user')->isguest()){
                            $selected = false;
                            $paidsupportid = JSSTrequest::getVar('paidsupportid',null,0);
                            if($paidsupportid){
                                //$paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->uid(), $paidsupportid);
								$paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->wpuid(), $paidsupportid);
                                if($paidsupport){
                                    jssupportticket::$_data['paidsupport'] = $paidsupport[0];
                                    $selected = true;
                                }
                            }
                            if(!$selected){
                                //$paidsupportitems = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->uid());
								$paidsupportitems = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->wpuid());
                                if(count($paidsupportitems) == 1){
                                    jssupportticket::$_data['paidsupport'] = $paidsupportitems[0];
                                }else{
                                    jssupportticket::$_data['paidsupportitems'] = $paidsupportitems;
                                }
                            }
                        }

                    }
                    // $layout = apply_filters( 'jsst_agent_add_ticket_redirect', $layout );
                    // if($layout == 'staffaddticket' && in_array('agent',jssupportticket::$_active_addons)){
                    //     $per_task = ($id == null) ? 'Add Ticket' : 'Edit Ticket';
                    //     jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($per_task);
                    // }
                    JSSTincluder::getJSModel('jssupportticket')->updateColorFile();
                    break;
                case 'admin_ticketdetail':
                case 'ticketdetail':
                    $id = JSSTrequest::getVar('jssupportticketid');
                    jssupportticket::$_data['permission_granted'] = true;
                    jssupportticket::$_data['user_staff'] = false;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        jssupportticket::$_data['user_staff'] = true;
                        jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Ticket');
                    }
                    if (jssupportticket::$_data['permission_granted']) {
                        JSSTincluder::getJSModel('ticket')->getTicketForDetail($id);
                        //check if envato license support has expired
                        if(in_array('envatovalidation', jssupportticket::$_active_addons) && !empty(jssupportticket::$_data[0]->envatodata)){
                            $envlicense = json_decode(jssupportticket::$_data[0]->envatodata, true);
                            if(!empty($envlicense['supporteduntil']) && date_i18n('Y-m-d') > date_i18n('Y-m-d',strtotime($envlicense['supporteduntil']))){
                                JSSTmessage::setMessage(esc_html(__('Support for this Envato license has expired', 'js-support-ticket')), 'error');
                            }
                            jssupportticket::$_data[0]->envatodata = $envlicense;
                        }
                    }
                    break;
                case 'myticket':
                    $list = JSSTrequest::getVar('list');
                    JSSTincluder::getJSModel('ticket')->getMyTickets($list);
                    break;
                case 'ticketstatus':
                    break;
                case 'visitormessagepage':
                    break;
                default:
                    exit;

            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'ticket');
            JSSTincluder::include_file($layout, $module);
        }
    }

    function canaddfile() {
        $nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'jstask')
                return false;
            else
                return true;
        }
    }

    function closeticket() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'close-ticket') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('ticketid');
        JSSTincluder::getJSModel('ticket')->closeTicket($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=tickets");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id));
        }
        wp_redirect($url);
        exit;
    }

    function lockticket() {
        $id = JSSTrequest::getVar('ticketid');
        JSSTincluder::getJSModel('ticket')->lockTicket($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$id));
        }
        wp_redirect($url);
        exit;
    }

    function unlockticket() {
        $id = JSSTrequest::getVar('ticketid');
        JSSTincluder::getJSModel('ticket')->unLockTicket($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$id));
        }
        wp_redirect($url);
        exit;
    }

    static function saveticket() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-ticket') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        $result = JSSTincluder::getJSModel('ticket')->storeTickets($data);
        if (is_admin()) {
            if($result == false){
                $url = admin_url("admin.php?page=ticket&jstlay=addticket");
				if(in_array('multiform', jssupportticket::$_active_addons)){
					$formid = $data['multiformid'];
					$url = admin_url("admin.php?page=ticket&jstlay=addticket&formid=".esc_attr($formid));
				}	
            }else{
                $url = admin_url("admin.php?page=ticket&jstlay=tickets");
            }
        } else {
            if (JSSTincluder::getObjectClass('user')->uid() == 0) { // visitor
                if ($result == false) { // error on captcha or ticket validation
                    $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket'));
					if(in_array('multiform', jssupportticket::$_active_addons)){
						$formid = $data['multiformid'];
						$url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket', 'formid'=> $formid));
					}	
                } else { // all things perfect
                    if(in_array('actions',jssupportticket::$_active_addons)){
                        $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'visitormessagepage'));
                    }else{
                        $url = jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'controlpanel'));
                    }
                }
            } else {
                if ($result == false) { // error on captcha or ticket validation
                    $addticket = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffaddticket' : 'addticket';
                    $module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
                    $url = jssupportticket::makeUrl(array('jstmod'=>$module1, 'jstlay'=>$addticket));
					if(in_array('multiform', jssupportticket::$_active_addons)){
						$formid = $data['multiformid'];
						$url = jssupportticket::makeUrl(array('jstmod'=>$module1, 'jstlay'=>$addticket, 'formid'=> $formid));
					}	
                } else {
                    $myticket = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'staffmyticket' : 'myticket';
                    $module1 = ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ? 'agent' : 'ticket';
                    $url = jssupportticket::makeUrl(array('jstmod'=>$module1, 'jstlay'=>$myticket));
                }
            }
        }
        if($result == false){
            JSSTformfield::setFormData($data);
        }
        wp_redirect($url);
        exit;
    }

    static function transferdepartment() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'transfer-department') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('ticket')->tickDepartmentTransfer($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$data['ticketid']));
        }
        wp_redirect($url);
        exit;
    }

    static function assigntickettostaff() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'assign-ticket-to-staff') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('ticket')->assignTicketToStaff($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$data['ticketid']));
        }
        wp_redirect($url);
        exit;
    }

    static function deleteticket() {
        $id = JSSTrequest::getVar('ticketid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-ticket') ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('ticket')->removeTicket($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=tickets");
        } elseif ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $url = jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffmyticket'));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket'));
        }
        wp_redirect($url);
        exit;
    }

    static function enforcedeleteticket() {
        $id = JSSTrequest::getVar('ticketid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'enforce-delete-ticket') ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('ticket')->removeEnforceTicket($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=tickets");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket'));
        }
        wp_redirect($url);
        exit;
    }

    static function changepriority() {
        $id = JSSTrequest::getVar('ticketid');
        $priorityid = JSSTrequest::getVar('priority');
        JSSTincluder::getJSModel('ticket')->changeTicketPriority($id, $priorityid);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$id));
        }
        wp_redirect($url);
        exit;
    }

    static function reopenticket() { // for user
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'reopen-ticket') ) {
            die( 'Security check Failed' );
        }
        $ticketid = JSSTrequest::getVar('ticketid');
        $data['ticketid'] = $ticketid;
        JSSTincluder::getJSModel('ticket')->reopenTicket($data);
        $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket" . esc_attr($url));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$data['ticketid']));
        }
        wp_redirect($url);
        exit;
    }

    static function actionticket() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'action-ticket') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        /* to handle actions */
        switch ($data['actionid']) {
            case 1: /* Change Priority Ticket */
                JSSTincluder::getJSModel('ticket')->changeTicketPriority($data['ticketid'], $data['priority']);
                $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                break;
            case 2: /* close ticket */
                JSSTincluder::getJSModel('ticket')->closeTicket($data['ticketid']);
                $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                break;
            case 3: /* Reopen Ticket */
                JSSTincluder::getJSModel('ticket')->reopenTicket($data);
                $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                break;
            case 4: /* Lock Ticket */
                if(in_array('actions', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('actions')->lockTicket($data['ticketid']);
                    $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                }
                break;
            case 5: /* Unlock ticket */
                if(in_array('actions', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('actions')->unLockTicket($data['ticketid']);
                    $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                }
                break;
            case 6: /* Banned Email */
                if(in_array('banemail', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('ticket')->banEmail($data);
                    $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                }
                break;
            case 7: /* Unban Email */
                if(in_array('banemail', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('ticket')->unbanEmail($data);
                    $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                }
                break;
            case 8: /* Mark over due */
                if(in_array('overdue', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('overdue')->markOverDueTicket($data);
                    $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                }
                break;
            case 9: /* In Progress */
                if(in_array('actions', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('ticket')->markTicketInProgress($data);
                    $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                }
                break;
            case 10: /* ban Email & close ticket */
                JSSTincluder::getJSModel('ticket')->banEmailAndCloseTicket($data);
                $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                break;
            case 11: /* unMark over due */
                if(in_array('overdue', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('overdue')->unMarkOverDueTicket($data);;
                    $url = "&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['ticketid']);
                }
                break;
        }

        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket" . $url);
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$data['ticketid']));
        }
        wp_redirect($url);
        exit;
    }

    static function showticketstatus() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'show-ticket-status') ) {
            //die( 'Security check Failed' );
        }
        $token = JSSTrequest::getVar('token');
        if ($token == null) { // in case it come from ticket status form
            $emailaddress = JSSTrequest::getVar('email');
            $trackingid = JSSTrequest::getVar('ticketid');
            $token = JSSTincluder::getJSModel('ticket')->createTokenByEmailAndTrackingId($emailaddress, $trackingid);
        }
        jssupportticketphplib::JSST_setcookie('js-support-ticket-token-tkstatus',$token ,0, COOKIEPATH);
        if ( SITECOOKIEPATH != COOKIEPATH ){
            jssupportticketphplib::JSST_setcookie('js-support-ticket-token-tkstatus',$token ,0, SITECOOKIEPATH);
        }
        $ticketid = JSSTincluder::getJSModel('ticket')->getTicketidForVisitor($token);
        if ($ticketid) {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$ticketid));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus'));
            JSSTmessage::setMessage(esc_html(__('Record not found', 'js-support-ticket')), 'error');
        }
        wp_redirect($url);
        exit;
    }

    static function downloadall() {
        $id = JSSTrequest::getVar('id');
        JSSTincluder::getJSModel('attachment')->getAllDownloads();
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>'$id','jsstpageid'=>jssupportticket::getPageid()));
        }
        wp_redirect($url);
        exit;
    }
    static function downloadallforreply() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'download-all-for-reply') ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('attachment')->getAllReplyDownloads();
        if (is_admin()) {
          $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail");
          } else {
          $url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>'$id','jsstpageid'=>jssupportticket::getPageid()));
          }
          wp_redirect($url);
          exit;
    }

    function downloadbyid(){
        $id = JSSTrequest::getVar('id');
        JSSTincluder::getJSModel('attachment')->getDownloadAttachmentById($id);
    }


    function downloadbyname(){
        $name = JSSTrequest::getVar('name');
        $id = JSSTrequest::getVar('id');
        JSSTincluder::getJSModel('attachment')->getDownloadAttachmentByName($name,$id);
    }

    function mergeticket() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'merge-ticket') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('mergeticket')->storeMergeTicket($data);
        if(is_admin()){
             $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" .esc_attr($data['secondaryticket']));
        }else if( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$data['secondaryticket']));
        }
        wp_redirect($url);
        exit;
    }
}
$ticketController = new JSSTticketController();
?>
