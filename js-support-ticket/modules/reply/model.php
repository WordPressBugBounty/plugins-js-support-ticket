<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreplyModel {

    function getReplies($id) {
        if (!is_numeric($id))
            return false;
        // Data

        do_action('reset_jsst_aadon_query');
        do_action('jsst_aadon_getreplies');// to prepare any addon based query (action is defined in two addons)
        $query = "SELECT replies.*,replies.id AS replyid,user.user_email AS useremail,tickets.id ".jssupportticket::$_addon_query['select']."
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS replies
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS tickets ON  replies.ticketid = tickets.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON  replies.uid = user.id
                    ".jssupportticket::$_addon_query['join']."
                    WHERE tickets.id = " . esc_sql($id) . " ORDER By replies.id ASC";
        jssupportticket::$_data[4] = jssupportticket::$_db->get_results($query);
        do_action('reset_jsst_aadon_query');
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        $attachmentmodel = JSSTincluder::getJSModel('attachment');
        foreach (jssupportticket::$_data[4] AS $reply) {
            $reply->attachments = $attachmentmodel->getAttachmentForReply($reply->id, $reply->replyid);
        }
        return;
    }

    function getTicketNameForReplies() {
        $query = "SELECT id, ticketid AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`";
        $list = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $list;
    }

    function getRepliesForForm($id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT replies.*,tickets.id
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS replies
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS tickets ON  replies.ticketid = tickets.id
                        WHERE replies.id = " . esc_sql($id);
            jssupportticket::$_data[0] = jssupportticket::$_db->get_row($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    function storeReplies($data) {
        $checkduplicatereplies = $this->checkIsReplyDuplicate($data);
        if(!$checkduplicatereplies){
            return false;
        }
        //validate reply for break down
        $ticketid   = $data['ticketrandomid'];
        $hash       = $data['hash'];
        $query = "SELECT id FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE ticketid='".esc_sql($ticketid)."'
        AND IF(`hash` is NULL,true,`hash`='".esc_sql($hash)."') ";
        $id = jssupportticket::$_db->get_var($query);
        if($id != $data['ticketid']){
            return;
        }//end

        $ticketviaemailstaffid = 0;
        // set in Email Piping
        if(isset($data['staffid'])){
            $ticketviaemailstaffid = $data['staffid'];
            unset($data['staffid']);
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Reply Ticket');
            if ($allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        // check whether ticket is closed or not incase of ticket viw email
        if(isset($data['ticketviaemail']) && $data['ticketviaemail'] == 1){
            if(jssupportticket::$_config['reply_to_closed_ticket'] != 1){
                $closed = JSSTincluder::getJSModel('ticket')->checkActionStatusSame($data['ticketid'],array('action' => 'closeticket'));
                if($closed == false){
                    JSSTincluder::getJSModel('email')->sendMail(1, 14, $data['ticketid']); // Mailfor, Reply Ticket
                    return;
                }
                // check this ticket is not assign to any one
                if( JSSTincluder::getJSModel('ticket')->isTicketAssigned($data['ticketid']) == false){
                    // if not assigned then assign to me
                    $data['assigntome'] = 1;
                }
            }
        }
        $sendEmail = true;
        $staffid = 0;
        if (!JSSTincluder::getObjectClass('user')->isguest()) {
            //$current_user = get_userdata(JSSTincluder::getObjectClass('user')->uid());
            $currentUserName = JSSTincluder::getObjectClass('user')->fullname();
            if( in_array('agent',jssupportticket::$_active_addons) ){
                //$staffid = JSSTincluder::getJSModel('agent')->getStaffId($current_user->ID);
				$staffid = JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid());
            }
        } else {
            $currentUserName = '';
        }

        if($staffid == 0 && $ticketviaemailstaffid != 0){
            $staffid = $ticketviaemailstaffid;
        }

        //check the assign to me on reply
        if (isset($data['assigntome']) && $data['assigntome'] == 1) {
            JSSTincluder::getJSModel('ticket')->ticketAssignToMe($data['ticketid'], $staffid);
        }
        if(isset($data['ticketviaemail'])){
            if($data['ticketviaemail'] == 1)
                $currentUserName = $data['name'];
        }
        $data['id'] = isset($data['id']) ? $data['id'] : '';
        $data['status'] = isset($data['status']) ? $data['status'] : '';
        $data['closeonreply'] = isset($data['closeonreply']) ? $data['closeonreply'] : '';
        $data['ticketviaemail'] = isset($data['ticketviaemail']) ? $data['ticketviaemail'] : 0;
        $tempmessage = $data['jsticket_message'];
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        if(isset($data['ticketviaemail']) && $data['ticketviaemail'] == 1){
            $data['message'] = $tempmessage;
        }else{
            $data['message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['jsticket_message']);
        }
        if(empty($data['message'])){
            JSSTmessage::setMessage(esc_html(__('Message field cannot be empty', 'js-support-ticket')), 'error');
            return false;
        }
        //check signature
        if (!isset($data['nonesignature'])) {
            if (isset($data['ownsignature']) && $data['ownsignature'] == 1) {
                if (is_admin()) {
                    $data['message'] .= '<br/>' . get_user_meta(JSSTincluder::getObjectClass('user')->uid(), 'jsst_signature', true);
                } elseif(in_array('agent',jssupportticket::$_active_addons)) {
                    $data['message'] .= '<br/>' . JSSTincluder::getJSModel('agent')->getMySignature();
                }
            }
            if (isset($data['departmentsignature']) && $data['departmentsignature'] == 1) {
                $data['message'] .= '<br/>' . JSSTincluder::getJSModel('department')->getSignatureByID($data['departmentid']);
            }
        }

        $data['created'] = date_i18n('Y-m-d H:i:s');
        $data['name'] = $currentUserName;
        $data['staffid'] = $staffid;

        $row = JSSTincluder::getJSTable('replies');

        $data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
        $error = 0;
        if (!$row->bind($data)) {
            $error = 1;
        }
        if (!$row->store()) {
            $error = 1;
        }

        if ($error == 0) {
            $replyid = $row->id;
            //tickets attachments store
            $data['replyattachmentid'] = $replyid;
            JSSTincluder::getJSModel('attachment')->storeAttachments($data);
            //reply stored change action
            if (is_admin()){
                JSSTincluder::getJSModel('ticket')->setStatus(3, $data['ticketid']); // 3 -> waiting for customer reply
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('timetracking')->storeTimeTaken($data,$replyid,1);// to store time for reply 1 is to identfy that current record is reply
                }
            }else {
                if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                    JSSTincluder::getJSModel('ticket')->setStatus(3, $data['ticketid']); // 3 -> waiting for customer reply
                    $data['staffid'] = $staffid;
                    if(in_array('timetracking', jssupportticket::$_active_addons)){
                        JSSTincluder::getJSModel('timetracking')->storeTimeTaken($data,$replyid,1);// to store time for reply 1 is to identfy that current record is reply
                    }

                }else{
                    JSSTincluder::getJSModel('ticket')->setStatus(1, $data['ticketid']); // 1 -> waiting for admin/staff reply
                }
            }
            JSSTincluder::getJSModel('ticket')->updateLastReply($data['ticketid']);
            JSSTmessage::setMessage(esc_html(__('Reply posted', 'js-support-ticket')), 'updated');
            $messagetype = esc_html(esc_html(__('Successfully', 'js-support-ticket')));

            // Reply notification
            if(in_array('notification', jssupportticket::$_active_addons)){
                // Get Ticket Staffid
                $ticketstaffid = JSSTincluder::getJSModel('ticket')->getStaffIdById($data['ticketid']);
                $ticketuid = JSSTincluder::getJSModel('ticket')->getUIdById($data['ticketid']);

                // to admin
                $dataarray = array();
                $dataarray['title'] = esc_html(esc_html(__("Reply posted on ticket","js-support-ticket")));
                $dataarray['body'] =  JSSTincluder::getJSModel('ticket')->getTicketSubjectById($data['ticketid']);

                // To admin
                $devicetoken = JSSTincluder::getJSModel('notification')->checkSubscriptionForAdmin();
                if($devicetoken){
                    $dataarray['link'] = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=".esc_attr($data['ticketid']));
                    $dataarray['devicetoken'] = $devicetoken;
                    $value = jssupportticket::$_config[md5(JSTN)];
                    if($value != ''){
                      do_action('send_push_notification',$dataarray);
                    }else{
                      do_action('resetnotificationvalues');
                    }
                }

                $dataarray['link'] = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', "jssupportticketid"=>$data['ticketid'],'jsstpageid'=>jssupportticket::getPageid()));
                if($ticketuid != 0 && ($ticketuid != JSSTincluder::getObjectClass('user')->uid())){
                    $devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($ticketuid);
                    $dataarray['devicetoken'] = $devicetoken;
                    if($devicetoken != '' && !empty($devicetoken)){
                        $value = jssupportticket::$_config[md5(JSTN)];
                        if($value != ''){
                          do_action('send_push_notification',$dataarray);
                        }else{
                          do_action('resetnotificationvalues');
                        }
                    }
                }

                if($ticketstaffid != 0 && ($ticketuid != $staffid)){
                    $devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($ticketstaffid);
                    $dataarray['devicetoken'] = $devicetoken;
                    if($devicetoken != '' && !empty($devicetoken)){
                        $value = jssupportticket::$_config[md5(JSTN)];
                        if($value != ''){
                          do_action('send_push_notification',$dataarray);
                        }else{
                          do_action('resetnotificationvalues');
                        }
                    }
                }
                if($ticketuid == 0){ // for visitor
                    $tokenarray['emailaddress'] = JSSTincluder::getJSModel('ticket')->getTicketEmailById($data['ticketid']);
                    $tokenarray['trackingid'] = JSSTincluder::getJSModel('ticket')->getTrackingIdById($data['ticketid']);
                    $tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                    $token = wp_json_encode($tokenarray);
                    include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                    $encoder = new JSSTEncoder();
                    $encryptedtext = $encoder->encrypt($token);
                    $dataarray['link'] = jssupportticket::makeUrl(array('jstmod'=>'ticket' ,'task'=>'showticketstatus','action'=>'jstask','token'=>$encryptedtext,'jsstpageid'=>jssupportticket::getPageid()));
                    $notificationid = JSSTincluder::getJSModel('ticket')->getNotificationIdById($data['ticketid']);
                    $devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($notificationid,0);
                    if($devicetoken != '' && !empty($devicetoken)){
                        $value = jssupportticket::$_config[md5(JSTN)];
                        if($value != ''){
                          do_action('send_push_notification',$dataarray);
                        }else{
                          do_action('resetnotificationvalues');
                        }
                    }
                }
            }
            // End notification
        }else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Reply posted', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
        }

        /* for activity log */
        $ticketid = $data['ticketid']; // get the ticket id
        $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $currentUserName = isset($current_user->display_name) ? $current_user->display_name : esc_html(__('Guest', 'js-support-ticket'));
        $eventtype = 'REPLIED_TICKET';
        $message = esc_html(__('Ticket is replied by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
        }

        // Send Emails
        if ($sendEmail == true) {
            if (is_admin()) {
                JSSTincluder::getJSModel('email')->sendMail(1, 4, $ticketid); // Mailfor, Reply Ticket
            } else {
                JSSTincluder::getJSModel('email')->sendMail(1, 5, $ticketid); // Mailfor, Reply Ticket
            }
            $ticketreplyobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE id = " . esc_sql($replyid));
            do_action('jsst-ticketreply', $ticketreplyobject);
        }
        // if Close on reply is cheked
        if ($data['closeonreply'] == 1) {
            JSSTincluder::getJSModel('ticket')->closeTicket($ticketid);
        }

        return;
    }

    function checkIsReplyDuplicate($data){
        if(empty($data)) return false;
        
        $curdate = date_i18n('Y-m-d H:i:s');
        $inquery = '';
        if (isset($data['ticketviaemail']) && $data['ticketviaemail'] == 1) {
            $inquery .= " AND ticketviaemail = 1";
        }
        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid = '" . esc_sql($data['ticketid']) . "' AND uid = '" . esc_sql($data['uid']) . "' ORDER BY created DESC LIMIT 1";
        $query .= $inquery;
        $datetime = jssupportticket::$_db->get_var($query);
        if($datetime){
            $diff = jssupportticketphplib::JSST_strtotime($curdate) - jssupportticketphplib::JSST_strtotime($datetime);
            if($diff <= 7){
                return false;
            }
        }
        return true;
    }

    function getLastReply($ticketid) {
        if (!is_numeric($ticketid))
            return false;
        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid =  " . esc_sql($ticketid) . " ORDER BY created desc";
        $lastreply = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
        }
        return $lastreply;
    }

    function removeTicketReplies($ticketid) {
        if(!is_numeric($ticketid)) return false;
        jssupportticket::$_db->delete(jssupportticket::$_db->prefix . 'js_ticket_replies', array('ticketid' => $ticketid));
        return;
    }

    function getReplyDataByID() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-reply-data-by-id') ) {
            die( 'Security check Failed' );
        }
        $replyid = JSSTrequest::getVar('val');
        if(!is_numeric($replyid)) return false;
        $query = "SELECT reply.id AS replyid, reply.message AS message
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS reply
                    WHERE reply.id =  " . esc_sql($replyid) ;
        $lastreply = jssupportticket::$_db->get_row($query);
        $lastreply->message = jssupportticketphplib::JSST_htmlentities(($lastreply->message));

        return wp_json_encode($lastreply);
    }

    function getAttachmentByReplyId($id){
        if(!is_numeric($id)) return false;
        $query = "SELECT attachment.filename , ticket.attachmentdir
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments` AS attachment
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON ticket.id = attachment.ticketid AND attachment.replyattachmentid = ".esc_sql($id) ;
        $replyattachments = jssupportticket::$_db->get_results($query);
        return $replyattachments;
    }

    function editReply($data) {
        if (empty($data))
            return false;
        $desc = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($data['jsticket_replytext']); // use jsticket_message to avoid conflict

        $row = JSSTincluder::getJSTable('replies');
        if (!$row->update(array('id' => $data['reply-replyid'], 'message' => $desc))) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function storeMergeTicketReplies($reply,$ticketid){
        if(!is_string($reply))
            return false;
        $id          = $ticketid;
        $user_id        = JSSTincluder::getObjectClass('user')->uid();
        $username       = JSSTincluder::getJSModel('jssupportticket')->getUserNameById($user_id);
        $query_array    = array(
            'uid'       => $user_id,
            'ticketid'  => $id,
            'name'      => $username,
            'message'   => $reply,
            'status'    => 1,
            'created'   => date_i18n('Y-m-d H:i:s'),
        );
        jssupportticket::$_db->replace(jssupportticket::$_db->prefix . 'js_ticket_replies', $query_array);
        if (jssupportticket::$_db->last_error == null) {
            JSSTmessage::setMessage(esc_html(__('Reply Has been Posted', 'js-support-ticket')), 'updated');
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        }else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Reply Has Not been Posted', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
        }
    }

    function getTicketLastReplyById($ticketid) {
        if (!is_numeric($ticketid))
            return false;
        $query = "SELECT message FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid =  " . esc_sql($ticketid) . " ORDER BY created desc LIMIT 1";
        $lastreply = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
        }
        return $lastreply;
    }
    function getUserNameFromReplyById($replyid) { // name field value is empty in some old tickets
        if (!is_numeric($replyid))
            return false;
		$name = "";
        $query = "SELECT user.* 
			FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS reply
			JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON reply.uid = user.id
			WHERE reply.id =  " . esc_sql($replyid);
        $replyuser = jssupportticket::$_db->get_row($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
        }
		if(isset($replyuser)){
            $name = $replyuser->name;
			if($name == ""){
				$name = $replyuser->display_name;
			}
			if($name == ""){
				$name = $replyuser->user_nicename;
			}
		}
		return $name;
    }
}

?>
