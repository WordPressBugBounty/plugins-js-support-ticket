<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreplyModel {

    function getReplies($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        // Data

        do_action('jsst_reset_aadon_query');
        do_action('jsst_aadon_getreplies');// to prepare any addon based query (action is defined in two addons)
        $jsst_ordering = jssupportticket::$_config['ticket_replies_ordering'];
        $jsst_ordering = strtoupper(trim($jsst_ordering)); // Normalize input

        // Allow only ASC or DESC
        if (!in_array($jsst_ordering, ['ASC', 'DESC'])) {
            $jsst_ordering = 'ASC'; // default fallback
        }
        $jsst_query = "SELECT replies.*,replies.id AS replyid,user.user_email AS useremail,viewer.display_name AS viewername,tickets.id,tickets.uid AS ticketsuid ".jssupportticket::$_addon_query['select']."
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS replies
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS tickets ON  replies.ticketid = tickets.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON  replies.uid = user.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS viewer ON  replies.viewed_by = viewer.id
                    ".jssupportticket::$_addon_query['join']."
                    WHERE tickets.id = " . esc_sql($jsst_id) . " ORDER By replies.id ".esc_sql($jsst_ordering);
        jssupportticket::$jsst_data[4] = jssupportticket::$_db->get_results($jsst_query);
        do_action('jsst_reset_aadon_query');
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        $jsst_attachmentmodel = JSSTincluder::getJSModel('attachment');
        foreach (jssupportticket::$jsst_data[4] AS $jsst_reply) {
            $jsst_reply->attachments = $jsst_attachmentmodel->getAttachmentForReply($jsst_reply->id, $jsst_reply->replyid);
            $jsst_current_user = JSSTincluder::getObjectClass('user')->uid();
            $jsst_viewed_by = isset($jsst_current_user) ? $jsst_current_user : -1; //-1 for handle visitor case
            $jsst_update_required = false; // Flag to determine if the update is needed

            // Check if the reply has not been viewed
            if (empty($jsst_reply->viewed_by) && empty($jsst_reply->mergemessage)) {

                // If the current user is an admin
                if (is_admin()) {
                    // Admin viewing someone else's reply and it's not staff
                    if ($jsst_reply->uid != $jsst_current_user && empty($jsst_reply->staffid)) {
                        $jsst_update_required = true; // Mark update as required
                    }
                } else { // If the current user is not an admin

                    // Check if the 'agent' addon is active and the user is staff
                    if (in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        // Check if the ticket owner is the reply owner
                        if ($jsst_reply->ticketsuid == $jsst_reply->uid) {
                            $jsst_update_required = true; // Mark update as required
                        }
                    } else { // If the user is not staff or the agent addon is inactive
                        // Check if the ticket owner is not the reply owner
                        if ($jsst_reply->ticketsuid != $jsst_reply->uid) {
                            $jsst_update_required = true; // Mark update as required
                        }
                    }
                }
            }
            // Execute the query if an update is required
            if ($jsst_update_required) {
                $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_replies` SET viewed_by = " . esc_sql($jsst_viewed_by) . ", viewed_on = '" . esc_sql(date_i18n('Y-m-d H:i:s')) . "' WHERE id = " . esc_sql($jsst_reply->replyid);
                jssupportticket::$_db->query($jsst_query);
            }
        }
        return;
    }

    function getTicketNameForReplies() {
        $jsst_query = "SELECT id, ticketid AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`";
        $jsst_list = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_list;
    }

    function getRepliesForForm($jsst_id) {
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT replies.*,tickets.id
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS replies
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS tickets ON  replies.ticketid = tickets.id
                        WHERE replies.id = " . esc_sql($jsst_id);
            jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    function storeReplies($jsst_data) {
        $jsst_checkduplicatereplies = $this->checkIsReplyDuplicate($jsst_data);
        if(!$jsst_checkduplicatereplies){
            return false;
        }
        //validate reply for break down
        $jsst_ticketid   = $jsst_data['ticketrandomid'];
        $jsst_hash       = $jsst_data['hash'];
        $jsst_query = "SELECT id FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE ticketid='".esc_sql($jsst_ticketid)."'
        AND IF(`hash` is NULL,true,`hash`='".esc_sql($jsst_hash)."') ";
        $jsst_id = jssupportticket::$_db->get_var($jsst_query);
        if($jsst_id != $jsst_data['ticketid']){
            return;
        }//end

        $jsst_ticketviaemailstaffid = 0;
        // set in Email Piping
        if(isset($jsst_data['staffid'])){
            $jsst_ticketviaemailstaffid = $jsst_data['staffid'];
            unset($jsst_data['staffid']);
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Reply Ticket');
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        // check whether ticket is closed or not incase of ticket viw email
        if(isset($jsst_data['ticketviaemail']) && $jsst_data['ticketviaemail'] == 1){
            if(jssupportticket::$_config['reply_to_closed_ticket'] != 1){
                $jsst_closed = JSSTincluder::getJSModel('ticket')->checkActionStatusSame($jsst_data['ticketid'],array('action' => 'closeticket'));
                if($jsst_closed == false){
                    JSSTincluder::getJSModel('email')->sendMail(1, 14, $jsst_data['ticketid']); // Mailfor, Reply Ticket
                    return;
                }
                // check this ticket is not assign to any one
                if( JSSTincluder::getJSModel('ticket')->isTicketAssigned($jsst_data['ticketid']) == false){
                    // if not assigned then assign to me
                    $jsst_data['assigntome'] = 1;
                }
            }
        }
        $jsst_sendEmail = true;
        $jsst_staffid = 0;
        if (!JSSTincluder::getObjectClass('user')->isguest()) {
            //$jsst_current_user = get_userdata(JSSTincluder::getObjectClass('user')->uid());
            $jsst_currentUserName = JSSTincluder::getObjectClass('user')->fullname();
            if( in_array('agent',jssupportticket::$_active_addons) ){
                //$jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_current_user->ID);
				$jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid());
            }
        } else {
            $jsst_currentUserName = '';
        }

        if($jsst_staffid == 0 && $jsst_ticketviaemailstaffid != 0){
            $jsst_staffid = $jsst_ticketviaemailstaffid;
        }

        //check the assign to me on reply
        if (isset($jsst_data['assigntome']) && $jsst_data['assigntome'] == 1) {
            JSSTincluder::getJSModel('ticket')->ticketAssignToMe($jsst_data['ticketid'], $jsst_staffid);
        }
        if(isset($jsst_data['ticketviaemail'])){
            if($jsst_data['ticketviaemail'] == 1)
                $jsst_currentUserName = $jsst_data['name'];
        }
        $jsst_data['id'] = isset($jsst_data['id']) ? $jsst_data['id'] : '';
        $jsst_data['status'] = isset($jsst_data['status']) ? $jsst_data['status'] : '';
        $jsst_data['closeonreply'] = isset($jsst_data['closeonreply']) ? $jsst_data['closeonreply'] : '';
        $jsst_data['ticketviaemail'] = isset($jsst_data['ticketviaemail']) ? $jsst_data['ticketviaemail'] : 0;
        $jsst_tempmessage = $jsst_data['jsticket_message'];
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions
        if(isset($jsst_data['ticketviaemail']) && $jsst_data['ticketviaemail'] == 1){
            $jsst_data['message'] = $jsst_tempmessage;
        }else{
            $jsst_data['message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData(wp_unslash($_POST['jsticket_message'] ?? ''));
        }
        if(empty($jsst_data['message'])){
            JSSTmessage::setMessage(esc_html(__('Message field cannot be empty', 'js-support-ticket')), 'error');
            return false;
        }
        //check signature
        if (!isset($jsst_data['nonesignature'])) {
            if (isset($jsst_data['ownsignature']) && $jsst_data['ownsignature'] == 1) {
                if (is_admin()) {
                    $jsst_data['message'] .= '<br/>' . get_user_meta(JSSTincluder::getObjectClass('user')->uid(), 'jsst_signature', true);
                } elseif(in_array('agent',jssupportticket::$_active_addons)) {
                    $jsst_data['message'] .= '<br/>' . JSSTincluder::getJSModel('agent')->getMySignature();
                }
            }
            if (isset($jsst_data['departmentsignature']) && $jsst_data['departmentsignature'] == 1) {
                $jsst_data['message'] .= '<br/>' . JSSTincluder::getJSModel('department')->getSignatureByID($jsst_data['departmentid']);
            }
        }

        $jsst_data['created'] = date_i18n('Y-m-d H:i:s');
        $jsst_data['name'] = $jsst_currentUserName;
        $jsst_data['staffid'] = $jsst_staffid;

        $jsst_row = JSSTincluder::getJSTable('replies');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 0) {
            $jsst_replyid = $jsst_row->id;
            //tickets attachments store
            $jsst_data['replyattachmentid'] = $jsst_replyid;
            JSSTincluder::getJSModel('attachment')->storeAttachments($jsst_data);
            //reply stored change action
            if (is_admin()){
                JSSTincluder::getJSModel('ticket')->setStatus(4, $jsst_data['ticketid']); // 4 -> waiting for customer reply
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('timetracking')->storeTimeTaken($jsst_data,$jsst_replyid,1);// to store time for reply 1 is to identfy that current record is reply
                }
            }else {
                if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                    JSSTincluder::getJSModel('ticket')->setStatus(4, $jsst_data['ticketid']); // 4 -> waiting for customer reply
                    $jsst_data['staffid'] = $jsst_staffid;
                    if(in_array('timetracking', jssupportticket::$_active_addons)){
                        JSSTincluder::getJSModel('timetracking')->storeTimeTaken($jsst_data,$jsst_replyid,1);// to store time for reply 1 is to identfy that current record is reply
                    }

                }else{
                    JSSTincluder::getJSModel('ticket')->setStatus(2, $jsst_data['ticketid']); // 2 -> waiting for admin/staff reply
                }
            }
            JSSTincluder::getJSModel('ticket')->updateLastReply($jsst_data['ticketid']);
            JSSTmessage::setMessage(esc_html(__('Reply posted', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));

            // Reply notification
            if(in_array('notification', jssupportticket::$_active_addons)){
                // Get Ticket Staffid
                $jsst_ticketstaffid = JSSTincluder::getJSModel('ticket')->getStaffIdById($jsst_data['ticketid']);
                $jsst_ticketuid = JSSTincluder::getJSModel('ticket')->getUIdById($jsst_data['ticketid']);

                // to admin
                $jsst_dataarray = array();
                $jsst_dataarray['title'] = esc_html(__("Reply posted on ticket","js-support-ticket"));
                $jsst_dataarray['body'] =  JSSTincluder::getJSModel('ticket')->getTicketSubjectById($jsst_data['ticketid']);

                // To admin
                $jsst_devicetoken = JSSTincluder::getJSModel('notification')->checkSubscriptionForAdmin();
                if($jsst_devicetoken){
                    $jsst_dataarray['link'] = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=".esc_attr($jsst_data['ticketid']));
                    $jsst_dataarray['devicetoken'] = $jsst_devicetoken;
                    $jsst_value = jssupportticket::$_config[md5(JSTN)];
                    if($jsst_value != ''){
                      do_action('jsst_send_push_notification',$jsst_dataarray);
                    }else{
                      do_action('jsst_resetnotificationvalues');
                    }
                }

                $jsst_dataarray['link'] = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', "jssupportticketid"=>$jsst_data['ticketid'],'jsstpageid'=>jssupportticket::getPageid()));
                if($jsst_ticketuid != 0 && ($jsst_ticketuid != JSSTincluder::getObjectClass('user')->uid())){
                    $jsst_devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($jsst_ticketuid);
                    $jsst_dataarray['devicetoken'] = $jsst_devicetoken;
                    if($jsst_devicetoken != '' && !empty($jsst_devicetoken)){
                        $jsst_value = jssupportticket::$_config[md5(JSTN)];
                        if($jsst_value != ''){
                          do_action('jsst_send_push_notification',$jsst_dataarray);
                        }else{
                          do_action('jsst_resetnotificationvalues');
                        }
                    }
                }

                if($jsst_ticketstaffid != 0 && ($jsst_ticketuid != $jsst_staffid)){
                    $jsst_devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($jsst_ticketstaffid);
                    $jsst_dataarray['devicetoken'] = $jsst_devicetoken;
                    if($jsst_devicetoken != '' && !empty($jsst_devicetoken)){
                        $jsst_value = jssupportticket::$_config[md5(JSTN)];
                        if($jsst_value != ''){
                          do_action('jsst_send_push_notification',$jsst_dataarray);
                        }else{
                          do_action('jsst_resetnotificationvalues');
                        }
                    }
                }
                if($jsst_ticketuid == 0){ // for visitor
                    $jsst_tokenarray['emailaddress'] = JSSTincluder::getJSModel('ticket')->getTicketEmailById($jsst_data['ticketid']);
                    $jsst_tokenarray['trackingid'] = JSSTincluder::getJSModel('ticket')->getTrackingIdById($jsst_data['ticketid']);
                    $jsst_tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                    $jsst_token = wp_json_encode($jsst_tokenarray);
                    include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                    $jsst_encoder = new JSSTEncoder();
                    $jsst_encryptedtext = $jsst_encoder->encrypt($jsst_token);
                    $jsst_dataarray['link'] = jssupportticket::makeUrl(array('jstmod'=>'ticket' ,'task'=>'showticketstatus','action'=>'jstask','token'=>$jsst_encryptedtext,'jsstpageid'=>jssupportticket::getPageid()));
                    $jsst_notificationid = JSSTincluder::getJSModel('ticket')->getNotificationIdById($jsst_data['ticketid']);
                    $jsst_devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($jsst_notificationid,0);
                    if($jsst_devicetoken != '' && !empty($jsst_devicetoken)){
                        $jsst_value = jssupportticket::$_config[md5(JSTN)];
                        if($jsst_value != ''){
                          do_action('jsst_send_push_notification',$jsst_dataarray);
                        }else{
                          do_action('jsst_resetnotificationvalues');
                        }
                    }
                }
            }
            // End notification
        }else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Reply posted', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
        }

        /* for activity log */
        $jsst_ticketid = $jsst_data['ticketid']; // get the ticket id
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = isset($jsst_current_user->display_name) ? $jsst_current_user->display_name : esc_html(__('Guest', 'js-support-ticket'));
        $jsst_eventtype = 'REPLIED_TICKET';
        $jsst_message = esc_html(__('Ticket is replied by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
        }

        // Send Emails
        if ($jsst_sendEmail == true) {
            if (is_admin()) {
                JSSTincluder::getJSModel('email')->sendMail(1, 4, $jsst_ticketid); // Mailfor, Reply Ticket
            } else {
                JSSTincluder::getJSModel('email')->sendMail(1, 5, $jsst_ticketid); // Mailfor, Reply Ticket
            }
            $jsst_ticketreplyobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE id = " . esc_sql($jsst_replyid));
            do_action('jsst-ticketreply', $jsst_ticketreplyobject);
        }
        // if Close on reply is cheked
        if ($jsst_data['closeonreply'] == 1) {
            JSSTincluder::getJSModel('ticket')->closeTicket($jsst_ticketid);
        }

        return;
    }

    function checkIsReplyDuplicate($jsst_data){
        if(empty($jsst_data)) return false;
        
        $jsst_curdate = date_i18n('Y-m-d H:i:s');
        $jsst_inquery = '';
        if (isset($jsst_data['ticketviaemail']) && $jsst_data['ticketviaemail'] == 1) {
            $jsst_inquery .= " AND ticketviaemail = 1";
        }
        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid = '" . esc_sql($jsst_data['ticketid']) . "' AND uid = '" . esc_sql($jsst_data['uid']) . "' ORDER BY created DESC LIMIT 1";
        $jsst_query .= $jsst_inquery;
        $jsst_datetime = jssupportticket::$_db->get_var($jsst_query);
        if($jsst_datetime){
            $jsst_diff = jssupportticketphplib::JSST_strtotime($jsst_curdate) - jssupportticketphplib::JSST_strtotime($jsst_datetime);
            if($jsst_diff <= 7){
                return false;
            }
        }
        return true;
    }

    function getLastReply($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid))
            return false;
        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid =  " . esc_sql($jsst_ticketid) . " ORDER BY created desc";
        $jsst_lastreply = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
        }
        return $jsst_lastreply;
    }

    function removeTicketReplies($jsst_ticketid) {
        if(!is_numeric($jsst_ticketid)) return false;
        jssupportticket::$_db->delete(jssupportticket::$_db->prefix . 'js_ticket_replies', array('ticketid' => $jsst_ticketid));
        return;
    }

    function getReplyDataByID() {
        $jsst_replyid = JSSTrequest::getVar('val');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-reply-data-by-id-'.$jsst_replyid) ) {
            die( 'Security check Failed' );
        }
        if(!is_numeric($jsst_replyid)) return false;
        $jsst_query = "SELECT reply.id AS replyid, reply.message AS message
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS reply
                    WHERE reply.id =  " . esc_sql($jsst_replyid) ;
        $jsst_lastreply = jssupportticket::$_db->get_row($jsst_query);
        $jsst_lastreply->message = jssupportticketphplib::JSST_htmlentities(($jsst_lastreply->message));

        return wp_json_encode($jsst_lastreply);
    }

    function getAttachmentByReplyId($jsst_id){
        if(!is_numeric($jsst_id)) return false;
        $jsst_query = "SELECT attachment.filename , ticket.attachmentdir
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments` AS attachment
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON ticket.id = attachment.ticketid AND attachment.replyattachmentid = ".esc_sql($jsst_id) ;
        $jsst_replyattachments = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_replyattachments;
    }

    function editReply($jsst_data) {
        if (empty($jsst_data))
            return false;
        $jsst_desc = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($jsst_data['jsticket_replytext']); // use jsticket_message to avoid conflict

        $jsst_row = JSSTincluder::getJSTable('replies');
        if (!$jsst_row->update(array('id' => $jsst_data['reply-replyid'], 'message' => $jsst_desc))) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function storeMergeTicketReplies($jsst_reply,$jsst_ticketid){
        if(!is_string($jsst_reply))
            return false;
        $jsst_id          = $jsst_ticketid;
        $jsst_user_id        = JSSTincluder::getObjectClass('user')->uid();
        $jsst_username       = JSSTincluder::getJSModel('jssupportticket')->getUserNameById($jsst_user_id);
        $jsst_query_array    = array(
            'uid'       => $jsst_user_id,
            'ticketid'  => $jsst_id,
            'name'      => $jsst_username,
            'message'   => $jsst_reply,
            'status'    => 1,
            'created'   => date_i18n('Y-m-d H:i:s'),
            'mergemessage'   => 1,
        );
        jssupportticket::$_db->replace(jssupportticket::$_db->prefix . 'js_ticket_replies', $jsst_query_array);
        if (jssupportticket::$_db->last_error == null) {
            JSSTmessage::setMessage(esc_html(__('Reply Has been Posted', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        }else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Reply Has Not been Posted', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
        }
    }

    function getTicketLastReplyById($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid))
            return false;
        $jsst_query = "SELECT message FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid =  " . esc_sql($jsst_ticketid) . " ORDER BY created desc LIMIT 1";
        $jsst_lastreply = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
        }
        return $jsst_lastreply;
    }
    function getUserNameFromReplyById($jsst_replyid) { // name field value is empty in some old tickets
        if (!is_numeric($jsst_replyid))
            return false;
		$jsst_name = "";
        $jsst_query = "SELECT user.* 
			FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS reply
			JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON reply.uid = user.id
			WHERE reply.id =  " . esc_sql($jsst_replyid);
        $jsst_replyuser = jssupportticket::$_db->get_row($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
        }
		if(isset($jsst_replyuser)){
            $jsst_name = $jsst_replyuser->name;
			if($jsst_name == ""){
				$jsst_name = $jsst_replyuser->display_name;
			}
			if($jsst_name == ""){
				$jsst_name = $jsst_replyuser->user_nicename;
			}
		}
		return $jsst_name;
    }

    function markedAsAiPoweredReply() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');

        if (!wp_verify_nonce($jsst_nonce, 'ai-powered-reply')) {
            wp_die('Security check failed');
        }

        // Force both status and id to be integers using (int)
        // This is the "Brick Wall" that stops SQL injection for numbers
        $jsst_status = (int) JSSTrequest::getVar('status');
        $jsst_type   = JSSTrequest::getVar('type');
        $jsst_id     = (int) JSSTrequest::getVar('id');

        if ($jsst_id <= 0 || !in_array($jsst_type, ['ticket', 'reply'])) {
            return false;
        }

        $jsst_table = ($jsst_type === 'ticket') ? 'js_ticket_tickets' : 'js_ticket_replies';

        // Since we cast to (int) above, these variables are now 100% safe to concatenate
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "$jsst_table` 
                       SET aireplymode = $jsst_status 
                       WHERE id = $jsst_id";

        $jsst_result = jssupportticket::$_db->query($jsst_query);

        return ($jsst_result !== false);
    }

    function getFilteredReplies() {
        // Verify nonce
        check_ajax_referer('get-filtered-replies', '_wpnonce');

        $jsst_ticket_id = intval(JSSTrequest::getVar('ticket_id'));

        if (!$jsst_ticket_id) {
            wp_send_json_error(['message' => __('Ticket ID is required.', 'js-support-ticket')]);
        }

        $jsst_uids = $this->get_allowed_support_user_ids();
        if (empty($jsst_uids)) {
            wp_send_json_success(['replies' => [], 'count' => 0]);
        }

        $jsst_uids_str = implode(',', array_map('intval', $jsst_uids)); // Ensure integers

        $jsst_query = "
        SELECT r.*
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS r
            WHERE r.ticketid = " . esc_sql($jsst_ticket_id) . "
            AND r.uid IN ($jsst_uids_str)";

        $jsst_query .= " ORDER BY r.created ASC LIMIT 50";
        $jsst_replies = jssupportticket::$_db->get_results($jsst_query);

        if (jssupportticket::$_db->last_error) {
            wp_send_json_error(['message' => __('Database error occurred.', 'js-support-ticket')]);
        }

        $jsst_formatted_replies = [];
        foreach ($jsst_replies as $jsst_reply) {
            $jsst_name = '';
            $jsst_anon_setting = jssupportticket::$_config['anonymous_name_on_ticket_reply'];

            if ($jsst_anon_setting == 1) {
                $jsst_name = jssupportticket::$_config['title'];
            } elseif ($jsst_anon_setting == 2) {
                $jsst_name = JSSTincluder::getJSModel('reply')->getUserNameFromReplyById($jsst_reply->id);
            }

            $jsst_formatted_replies[] = [
                'id'        => $jsst_reply->id,
                'text'      => $jsst_reply->message,
                'name'      => $jsst_name,
                'timestamp' => $jsst_reply->created,
                'isMarked'  => (bool) $jsst_reply->aireplymode
            ];
        }

        wp_send_json_success([
            'replies' => $jsst_formatted_replies,
            'count'   => count($jsst_formatted_replies)
        ]);
    }

    function get_allowed_support_user_ids() {
        $jsst_allowed_uids = [];

        // Get WordPress administrator user IDs
        $jsst_admin_wp_ids = jssupportticket::$_db->get_col(
            "SELECT user_id
            FROM `" . jssupportticket::$_db->prefix . "usermeta`
            WHERE meta_key = '" . jssupportticket::$_db->prefix . "capabilities'
            AND meta_value LIKE '%administrator%'"
        );

        // Convert WP user IDs to JS Support Ticket user IDs
        if (!empty($jsst_admin_wp_ids)) {
            foreach ($jsst_admin_wp_ids as $jsst_wp_id) {
                $jsst_js_user = JSSTincluder::getObjectClass('user')->getjssupportticketuidbyuserid($jsst_wp_id);
                if (!empty($jsst_js_user) && isset($jsst_js_user[0]->id)) {
                    $jsst_allowed_uids[] = (int)$jsst_js_user[0]->id;
                }
            }
        }

        // Add agent user IDs if the 'agent' addon is active
        if (in_array('agent', jssupportticket::$_active_addons)) {
            $jsst_agent_ids = jssupportticket::$_db->get_col(
                "SELECT uid
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff`"
            );
            foreach ($jsst_agent_ids as $jsst_id) {
                $jsst_allowed_uids[] = (int)$jsst_id;
            }
        }

        // Deduplicate and ensure all values are positive integers
        $jsst_allowed_uids = array_unique(array_filter(array_map('intval', $jsst_allowed_uids)));

        // Avoid empty IN() errors
        return !empty($jsst_allowed_uids) ? $jsst_allowed_uids : [0];
    }
}

?>
