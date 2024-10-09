<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTgdprModel {

	function getGDPRFeilds(){
		$query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = 3 ORDER BY ordering ";
		jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
		if (jssupportticket::$_db->last_error != null) {
		    JSSTincluder::getJSModel('systemerror')->addSystemError();
		}
	}

	function getEraseDataRequests(){
		$query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests`";
		jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
		if (jssupportticket::$_db->last_error != null) {
		    JSSTincluder::getJSModel('systemerror')->addSystemError();
		}

        $email = jssupportticket::$_search['gdpr']['email'];
        $email = jssupportticket::parseSpaces($email);
        $inquery = '';
        if ($email != null)
            $inquery .= " WHERE user.user_email = '".esc_sql($email)."'";

        jssupportticket::$_data['filter']['email'] = $email;

        // Pagination
        $query = "SELECT COUNT(request.id)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` AS request
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON user.id = request.uid
                    ";
        $query .= $inquery;
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        // Data
        $query = "SELECT request.*, user.user_email
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` AS request
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON user.id = request.uid
                    ";
        $query .= $inquery;
        $query .= " ORDER BY request.created DESC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
	}

    function getUserEraseDataRequest(){
        $uid = JSSTincluder::getObjectClass('user')->uid();
        if($uid == 0){
            return;
        }
        $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` WHERE uid = ".esc_sql($uid);
        jssupportticket::$_data[0] = jssupportticket::$_db->get_row($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
    }

    function storeUserEraseRequest($data){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-usereraserequest') ) {
            die( 'Security check Failed' );
        }
    	if (!$data['id']) { //new
    	    $data['created'] = date_i18n('Y-m-d H:i:s');
            $data['uid'] = JSSTincluder::getObjectClass('user')->uid();
            $data['status'] = 1;
    	}
    	$data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
    	$data['message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['message']);
    	$row = JSSTincluder::getJSTable('erasedatarequests');
    	$data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
    	$error = 0;
    	if (!$row->bind($data)) {
            $error = 1;
    	}
    	if (!$row->store()) {
            $error = 1;
    	}

    	if ($error == 0) {
    	    JSSTmessage::setMessage(esc_html(__('Erasing data request has been stored', 'js-support-ticket')), 'updated');
    	} else {
    	    JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
    	    JSSTmessage::setMessage(esc_html(__('Failed while storing', 'js-support-ticket')), 'error');
    	}
        return;
    }

    function deleteUserEraseRequest($id){
        if(!is_numeric($id)){
            return false;
        }
        if($this->checkCanDelete($id)){
            $row = JSSTincluder::getJSTable('erasedatarequests');
            if ($row->delete($id)) {
                JSSTmessage::setMessage(esc_html(__('Erase data request withdrawn', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Failed while performing action', 'js-support-ticket')), 'error');
            }
        }
        return;
    }

    function checkCanDelete($id){

        if(current_user_can('manage_options')){ // allow admin to delete ??
            return true;
        }

        $uid = JSSTincluder::getObjectClass('user')->uid();
        $query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` WHERE id = ".esc_sql($id);
        $db_uid = jssupportticket::$_db->get_var($query);
        if( $db_uid == $uid){
            return true;
        }else{
            return false;
        }
    }

    private function getUserDetailReportByUserId( $uid = 0){
        $curdate = JSSTrequest::getVar('date_start', 'get');
        $fromdate = JSSTrequest::getVar('date_end', 'get');
        if($uid == 0 || $uid == ''){
            $id = JSSTrequest::getVar('uid', 'get');
        }else{
            $id = $uid;
            $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = ".esc_sql($id) ." ORDER BY created ASC LIMIT 1";
            $curdate = jssupportticket::$_db->get_var($query);

            $fromdate = date_i18n('Y-m-d h:i:s');
        }

        if( empty($curdate) OR empty($fromdate))
            return null;
        if(! is_numeric($id))
            return null;

        $result['curdate'] = $curdate;
        $result['fromdate'] = $fromdate;
        $result['id'] = $id;

        //Query to get Data
        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00' OR lastreply = '') AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $result['openticket'] = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $result['closeticket'] = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $result['answeredticket'] = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $result['overdueticket'] = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00' AND lastreply != '') AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $result['pendingticket'] = jssupportticket::$_db->get_results($query);
        //user detail
        $query = "SELECT user.display_name,user.user_email,user.user_nicename,user.id,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0  AND (lastreply = '0000-00-00 00:00:00' OR lastreply = '') AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND isoverdue = 1 AND (lastreply != '0000-00-00 00:00:00' AND lastreply != '') AND created >= '" . esc_sql($curdate) . "' AND created <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS pendingticket
                    FROM `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user
                    WHERE user.id = ".esc_sql($id);
        $user = jssupportticket::$_db->get_row($query);
        $result['users'] = $user;
        //Tickets
        do_action('jsstFeedbackQueryStaff');// to prepare any addon based query
        $query = "SELECT ticket.*,priority.priority, priority.prioritycolour ". jssupportticket::$_addon_query['select'] ."
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    ". jssupportticket::$_addon_query['join'] . "
                    WHERE uid = ".esc_sql($id)." AND ticket.created >= '" . esc_sql($curdate) . "' AND ticket.created <= '" . esc_sql($fromdate) . "' ";

        $result['tickets'] = jssupportticket::$_db->get_results($query);


        do_action('reset_jsst_aadon_query');
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach ($result['tickets'] as $ticket) {
                 $ticket->time = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($ticket->id);
            }
        }

        return $result;
    }

    function setUserExportByuid($uid = 0){
        $tb = "\t";
        $nl = "\n";
        $result = $this->getUserDetailReportByUserId($uid);

        if(empty($result))
            return '';

        $fromdate = date_i18n('Y-m-d',strtotime($result['curdate']));
        $todate = date_i18n('Y-m-d',strtotime($result['fromdate']));

        $data = esc_html(__('User Report', 'js-support-ticket')).' '. esc_html(__('From', 'js-support-ticket')).' '.$fromdate.' - '.$todate.$nl.$nl;

        // By 1 month
        $data .= esc_html(__('Ticket status by days', 'js-support-ticket')).$nl.$nl;
        $data .= esc_html(__('Date', 'js-support-ticket')).$tb. esc_html(__('New', 'js-support-ticket')).$tb. esc_html(__('Answered', 'js-support-ticket')).$tb. esc_html(__('Closed', 'js-support-ticket')).$tb. esc_html(__('Pending', 'js-support-ticket')).$tb. esc_html(__('Overdue', 'js-support-ticket')).$nl;
        while (strtotime($fromdate) <= jssupportticketphplib::JSST_strtotime($todate)) {
            $openticket = 0;
            $closeticket = 0;
            $answeredticket = 0;
            $overdueticket = 0;
            $pendingticket = 0;
            foreach ($result['openticket'] as $ticket) {
                $ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created));
                if($ticket_date == $fromdate)
                    $openticket += 1;
            }
            foreach ($result['closeticket'] as $ticket) {
                $ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created));
                if($ticket_date == $fromdate)
                    $closeticket += 1;
            }
            foreach ($result['answeredticket'] as $ticket) {
                $ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created));
                if($ticket_date == $fromdate)
                    $answeredticket += 1;
            }
            foreach ($result['overdueticket'] as $ticket) {
                $ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created));
                if($ticket_date == $fromdate)
                    $overdueticket += 1;
            }
            foreach ($result['pendingticket'] as $ticket) {
                $ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created));
                if($ticket_date == $fromdate)
                    $pendingticket += 1;
            }
            $data .= '"'.$fromdate.'"'.$tb.'"'.$openticket.'"'.$tb.'"'.$answeredticket.'"'.$tb.'"'.$closeticket.'"'.$tb.'"'.$pendingticket.'"'.$tb.'"'.$overdueticket.'"'.$nl;
            $fromdate = date_i18n("Y-m-d", jssupportticketphplib::JSST_strtotime("+1 day", jssupportticketphplib::JSST_strtotime($fromdate)));
        }
        $data .= $nl.$nl.$nl;
        // END By 1 month

        // by staffs
        $data .= esc_html(__('Users Tickets', 'js-support-ticket')).$nl.$nl;
        if(!empty($result['users'])){
            $data .= esc_html(__('Name', 'js-support-ticket')).$tb. esc_html(__('Username', 'js-support-ticket')).$tb. esc_html(__('Email', 'js-support-ticket')).$tb. esc_html(__('New', 'js-support-ticket')).$tb. esc_html(__('Answered', 'js-support-ticket')).$tb. esc_html(__('Closed', 'js-support-ticket')).$tb. esc_html(__('Pending', 'js-support-ticket')).$tb. esc_html(__('Overdue', 'js-support-ticket')).$nl;
            $key = $result['users'];
            $agentname = $key->display_name;
            $username = $key->user_nicename;
            $email = $key->user_email;

            $data .= '"'.$agentname.'"'.$tb.'"'.$username.'"'.$tb.'"'.$email.'"'.$tb.'"'.$key->openticket.'"'.$tb.'"'.$key->answeredticket.'"'.$tb.'"'.$key->closeticket.'"'.$tb.'"'.$key->pendingticket.'"'.$tb.'"'.$key->overdueticket.'"'.$nl;

            $data .= $nl.$nl.$nl;
        }

        // by priorits tickets
        $data .= esc_html(__('Tickets', 'js-support-ticket')).$nl.$nl;
        if(!empty($result['tickets'])){
            $data .= esc_html(__('Subject', 'js-support-ticket')).$tb. esc_html(__('Status', 'js-support-ticket')).$tb. esc_html(__('Priority', 'js-support-ticket')).$tb. esc_html(__('Created', 'js-support-ticket'));

             if(in_array('feedback', jssupportticket::$_active_addons)){
                $data .= $tb. esc_html(__('Rating', 'js-support-ticket'));
            }
            if(in_array('timetracking', jssupportticket::$_active_addons)){
                $data .= $tb. esc_html(__('Time', 'js-support-ticket'));
            }
            $data .= $nl;
            $status = '';
            foreach ($result['tickets'] as $ticket) {
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    $hours = floor($ticket->time / 3600);
                    $mins = floor($ticket->time / 60);
                    $mins = floor($mins % 60);
                    $secs = floor($ticket->time % 60);
                    $time = sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
                }
                switch($ticket->status){
                    case 0:
                        $status = esc_html(__('New','js-support-ticket'));
                        if($ticket->isoverdue == 1)
                            $status = esc_html(__('Overdue','js-support-ticket'));
                    break;
                    case 1:
                        $status = esc_html(__('Pending','js-support-ticket'));
                        if($ticket->isoverdue == 1)
                            $status = esc_html(__('Overdue','js-support-ticket'));
                    break;
                    case 2:
                        $status = esc_html(__('In Progress','js-support-ticket'));
                        if($ticket->isoverdue == 1)
                            $status = esc_html(__('Overdue','js-support-ticket'));
                    break;
                    case 3:
                        $status = esc_html(__('Answered','js-support-ticket'));
                        if($ticket->isoverdue == 1)
                            $status = esc_html(__('Overdue','js-support-ticket'));
                    break;
                    case 4:
                        $status = esc_html(__('Closed','js-support-ticket'));
                    break;
                    case 5:
                        $status = esc_html(__('Merged','js-support-ticket'));
                    break;
                }
                $created = date_i18n('Y-m-d',strtotime($ticket->created));
                $data .= '"'.$ticket->subject.'"'.$tb.'"'.$status.'"'.$tb.'"'.$ticket->priority.'"'.$tb.'"'.$created.'"';

                if(in_array('feedback', jssupportticket::$_active_addons)){
                    $data .= $tb.'"'.$ticket->rating.'"';
                }
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    $data .= $tb.'"'.$time.'"';
                }
                $data .= $nl;
            }
            $data .= $nl.$nl.$nl;
        }
        return $data;
    }

    function anonymizeUserData($uid){
        if(!is_numeric($uid) || $uid == 0){
            return false;
        }
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = ".esc_sql($uid);
        $uids = jssupportticket::$_db->get_results($query);

        foreach ($uids as $ticket) { // erase tickets data
            // ticket data
            $row = JSSTincluder::getJSTable('tickets');
            $row->update(array('id' => $ticket->id, 'email'=>'---', 'subject'=>'---', 'message'=>'---', 'phone'=>'', 'phoneext'=>'', 'params' => ''));

            // erase replies data
            $query = "SELECT replies.id AS replyid
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS replies
                        WHERE replies.ticketid = ".esc_sql($ticket->id);
            $replies = jssupportticket::$_db->get_results($query);
            foreach ($replies as $reply) {
                $row = JSSTincluder::getJSTable('replies');
                $row->update(array('id' => $reply->replyid, 'message' => '---'));
            }

            // erase internal note data
            if(in_array('note', jssupportticket::$_active_addons)){
                $query = "SELECT notes.id AS noteid
                            FROM `" . jssupportticket::$_db->prefix . "js_ticket_notes` AS notes
                            WHERE notes.ticketid = ".esc_sql($ticket->id);
                $notes = jssupportticket::$_db->get_results($query);
                foreach ($notes as $note) {
                    $row = JSSTincluder::getJSTable('note');
                    $row->update(array('id' => $note->noteid, 'title' => '---', 'note' => '---'));
                }
            }
            //activity log for ticket
            if(in_array('tickethistory', jssupportticket::$_active_addons)){
                $query = "DELETE
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_activity_log`
                        WHERE eventfor = 1 AND referenceid = ".esc_sql($ticket->id);
                jssupportticket::$_db->query($query);

            }
            // private credentails for ticket
            if(in_array('privatecredentials',jssupportticket::$_active_addons)){
                JSSTincluder::getJSModel('privatecredentials')->deleteCredentialsOnCloseTicket($ticket->id);
            }
            // ticket attachments.
            $datadirectory = jssupportticket::$_config['data_directory'];
            $maindir = wp_upload_dir();
            $mainpath = $maindir['basedir'];
            $mainpath = $mainpath .'/'.$datadirectory;
            $mainpath = $mainpath . '/attachmentdata';
            $query = "SELECT ticket.attachmentdir
                        FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                        WHERE ticket.id = ".esc_sql($ticket->id);
            $foldername = jssupportticket::$_db->get_var($query);
            if(!empty($foldername)){
                $folder = $mainpath . '/ticket/'.$foldername;
                if(file_exists($folder)){
                    $path = $mainpath . '/ticket/'.$foldername.'/*.*';
                    $files = glob($path);
                    array_map('unlink', $files);//deleting files
                    rmdir($folder);
                }
            }
            $query = "DELETE FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` WHERE ticketid = ".esc_sql($ticket->id);
            jssupportticket::$_db->query($query);
        }
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` SET status = 2 WHERE uid = ".esc_sql($uid);
        jssupportticket::$_db->query($query);
        JSSTmessage::setMessage(esc_html(__('User identifying data erased', 'js-support-ticket')), 'updated');
        $user_data = get_user_by('ID',$uid);
        $email = $user_data->user_email;
        $name = $user_data->display_name;
        jssupportticket::$_data['mail_data']['email'] = $email;
        jssupportticket::$_data['mail_data']['name'] = $name;
        JSSTincluder::getJSModel('email')->sendMail(4, 1); // Mailfor, Delete Ticket
        return;
    }

    function deleteUserData($uid){
        if(!is_numeric($uid) || $uid == 0){
            return false;
        }
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = ".esc_sql($uid);
        $uids = jssupportticket::$_db->get_results($query);

        foreach ($uids as $ticket) { // erase tickets data
            // ticket data

            $row = JSSTincluder::getJSTable('tickets');
            $row->delete($ticket->id);

            if(in_array('note', jssupportticket::$_active_addons)){
                // delete internal notes
                JSSTincluder::getJSModel('note')->removeTicketInternalNote($id);
            }
            // delete replies
            JSSTincluder::getJSModel('reply')->removeTicketReplies($id);

            // private credentails for ticket
            if(in_array('privatecredentials',jssupportticket::$_active_addons)){
                JSSTincluder::getJSModel('privatecredentials')->deleteCredentialsOnCloseTicket($ticket->id);
            }
            // ticket attachments.
            $datadirectory = jssupportticket::$_config['data_directory'];
            $maindir = wp_upload_dir();
            $mainpath = $maindir['basedir'];
            $mainpath = $mainpath .'/'.$datadirectory;
            $mainpath = $mainpath . '/attachmentdata';
            $query = "SELECT ticket.attachmentdir
                        FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                        WHERE ticket.id = ".esc_sql($ticket->id);
            $foldername = jssupportticket::$_db->get_var($query);
            if(!empty($foldername)){
                $folder = $mainpath . '/ticket/'.$foldername;
                if(file_exists($folder)){
                    $path = $mainpath . '/ticket/'.$foldername.'/*.*';
                    $files = glob($path);
                    array_map('unlink', $files);//deleting files
                    rmdir($folder);
                }
            }
            $query = "DELETE FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` WHERE ticketid = ".esc_sql($ticket->id);
            jssupportticket::$_db->query($query);
        }
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` SET status = 3 WHERE uid = ".esc_sql($uid);
        jssupportticket::$_db->query($query);

        $user_data = get_user_by('ID',$uid);

        JSSTmessage::setMessage(esc_html(__('User data Deleted', 'js-support-ticket')), 'updated');
        $user_data = get_user_by('ID',$uid);
        $email = $user_data->user_email;
        $name = $user_data->display_name;
        jssupportticket::$_data['mail_data']['email'] = $email;
        jssupportticket::$_data['mail_data']['name'] = $name;
        JSSTincluder::getJSModel('email')->sendMail(4, 1); // Mailfor, Delete Ticket
    }

    function getAdminSearchFormDataGDPR(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'erase-data-requests') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['email'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('email')));
        $jsst_search_array['search_from_gdpr'] = 1;
        return $jsst_search_array;
    }
}
?>
