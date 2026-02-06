<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTgdprModel {

	function getGDPRFeilds(){
		$jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = 3 ORDER BY ordering ";
		jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
		if (jssupportticket::$_db->last_error != null) {
		    JSSTincluder::getJSModel('systemerror')->addSystemError();
		}
	}

	function getEraseDataRequests(){
		$jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests`";
		jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
		if (jssupportticket::$_db->last_error != null) {
		    JSSTincluder::getJSModel('systemerror')->addSystemError();
		}

        $jsst_email = jssupportticket::$_search['gdpr']['email'];
        $jsst_email = jssupportticket::parseSpaces($jsst_email);
        $jsst_inquery = '';
        if ($jsst_email != null)
            $jsst_inquery .= " WHERE user.user_email = '".esc_sql($jsst_email)."'";

        jssupportticket::$jsst_data['filter']['email'] = $jsst_email;

        // Pagination
        $jsst_query = "SELECT COUNT(request.id)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` AS request
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON user.id = request.uid
                    ";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        // Data
        $jsst_query = "SELECT request.*, user.user_email
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` AS request
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON user.id = request.uid
                    ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY request.created DESC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
	}

    function getUserEraseDataRequest(){
        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
        if($jsst_uid == 0){
            return;
        }
        $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` WHERE uid = ".esc_sql($jsst_uid);
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
    }

    function storeUserEraseRequest($jsst_data){
        $jsst_id = isset($jsst_data['id']) ? $jsst_data['id'] : '';
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-usereraserequest-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
    	if (!$jsst_data['id']) { //new
    	    $jsst_data['created'] = date_i18n('Y-m-d H:i:s');
            $jsst_data['uid'] = JSSTincluder::getObjectClass('user')->uid();
            $jsst_data['status'] = 1;
    	}
    	$jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions
    	$jsst_data['message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['message']);
    	$jsst_row = JSSTincluder::getJSTable('erasedatarequests');
    	$jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
    	$jsst_error = 0;
    	if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
    	}
    	if (!$jsst_row->store()) {
            $jsst_error = 1;
    	}

    	if ($jsst_error == 0) {
    	    JSSTmessage::setMessage(esc_html(__('Erasing data request has been stored', 'js-support-ticket')), 'updated');
    	} else {
    	    JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
    	    JSSTmessage::setMessage(esc_html(__('Failed while storing', 'js-support-ticket')), 'error');
    	}
        return;
    }

    function deleteUserEraseRequest($jsst_id){
        if(!is_numeric($jsst_id)){
            return false;
        }
        if($this->checkCanDelete($jsst_id)){
            $jsst_row = JSSTincluder::getJSTable('erasedatarequests');
            if ($jsst_row->delete($jsst_id)) {
                JSSTmessage::setMessage(esc_html(__('Erase data request withdrawn', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Failed while performing action', 'js-support-ticket')), 'error');
            }
        }
        return;
    }

    function checkCanDelete($jsst_id){
        if(!is_numeric($jsst_id)) return false;
        if(current_user_can('manage_options')){ // allow admin to delete ??
            return true;
        }

        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
        $jsst_query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` WHERE id = ".esc_sql($jsst_id);
        $jsst_db_uid = jssupportticket::$_db->get_var($jsst_query);
        if( $jsst_db_uid == $jsst_uid){
            return true;
        }else{
            return false;
        }
    }

    private function getUserDetailReportByUserId( $jsst_uid = 0){
        $jsst_curdate = JSSTrequest::getVar('date_start', 'get');
        $jsst_fromdate = JSSTrequest::getVar('date_end', 'get');
        if($jsst_uid == 0 || $jsst_uid == ''){
            $jsst_id = JSSTrequest::getVar('uid', 'get');
        }else{
            $jsst_id = $jsst_uid;
            $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = ".esc_sql($jsst_id) ." ORDER BY created ASC LIMIT 1";
            $jsst_curdate = jssupportticket::$_db->get_var($jsst_query);

            $jsst_fromdate = date_i18n('Y-m-d h:i:s');
        }

        if( empty($jsst_curdate) OR empty($jsst_fromdate))
            return null;
        if(! is_numeric($jsst_id))
            return null;

        $jsst_result['curdate'] = $jsst_curdate;
        $jsst_result['fromdate'] = $jsst_fromdate;
        $jsst_result['id'] = $jsst_id;

        //Query to get Data
        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00' OR lastreply = '') AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_result['openticket'] = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_result['closeticket'] = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_result['answeredticket'] = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_result['overdueticket'] = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00' AND lastreply != '') AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_result['pendingticket'] = jssupportticket::$_db->get_results($jsst_query);
        //user detail
        $jsst_query = "SELECT user.display_name,user.user_email,user.user_nicename,user.id,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1  AND (lastreply = '0000-00-00 00:00:00' OR lastreply = '') AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND isoverdue = 1 AND (lastreply != '0000-00-00 00:00:00' AND lastreply != '') AND created >= '" . esc_sql($jsst_curdate) . "' AND created <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS pendingticket
                    FROM `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user
                    WHERE user.id = ".esc_sql($jsst_id);
        $jsst_user = jssupportticket::$_db->get_row($jsst_query);
        $jsst_result['users'] = $jsst_user;
        //Tickets
        do_action('jsstFeedbackQueryStaff');// to prepare any addon based query
        $jsst_query = "SELECT ticket.*,priority.priority, priority.prioritycolour,status.status AS statustitle ". jssupportticket::$_addon_query['select'] ."
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    ". jssupportticket::$_addon_query['join'] . "
                    WHERE uid = ".esc_sql($jsst_id)." AND ticket.created >= '" . esc_sql($jsst_curdate) . "' AND ticket.created <= '" . esc_sql($jsst_fromdate) . "' ";

        $jsst_result['tickets'] = jssupportticket::$_db->get_results($jsst_query);


        do_action('jsst_reset_aadon_query');
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach ($jsst_result['tickets'] as $jsst_ticket) {
                 $jsst_ticket->time = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($jsst_ticket->id);
            }
        }

        return $jsst_result;
    }

    function setUserExportByuid($jsst_uid = 0){
        $jsst_tb = "\t";
        $jsst_nl = "\n";
        $jsst_result = $this->getUserDetailReportByUserId($jsst_uid);

        if(empty($jsst_result))
            return '';

        $jsst_fromdate = date_i18n('Y-m-d',strtotime($jsst_result['curdate']));
        $jsst_todate = date_i18n('Y-m-d',strtotime($jsst_result['fromdate']));

        $jsst_data = esc_html(__('User Report', 'js-support-ticket')).' '. esc_html(__('From', 'js-support-ticket')).' '.$jsst_fromdate.' - '.$jsst_todate.$jsst_nl.$jsst_nl;

        // By 1 month
        $jsst_data .= esc_html(__('Ticket status by days', 'js-support-ticket')).$jsst_nl.$jsst_nl;
        $jsst_data .= esc_html(__('Date', 'js-support-ticket')).$jsst_tb. esc_html(__('New', 'js-support-ticket')).$jsst_tb. esc_html(__('Answered', 'js-support-ticket')).$jsst_tb. esc_html(__('Closed', 'js-support-ticket')).$jsst_tb. esc_html(__('Pending', 'js-support-ticket')).$jsst_tb. esc_html(__('Overdue', 'js-support-ticket')).$jsst_nl;
        while (strtotime($jsst_fromdate) <= jssupportticketphplib::JSST_strtotime($jsst_todate)) {
            $jsst_openticket = 0;
            $jsst_closeticket = 0;
            $jsst_answeredticket = 0;
            $jsst_overdueticket = 0;
            $jsst_pendingticket = 0;
            foreach ($jsst_result['openticket'] as $jsst_ticket) {
                $jsst_ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created));
                if($jsst_ticket_date == $jsst_fromdate)
                    $jsst_openticket += 1;
            }
            foreach ($jsst_result['closeticket'] as $jsst_ticket) {
                $jsst_ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created));
                if($jsst_ticket_date == $jsst_fromdate)
                    $jsst_closeticket += 1;
            }
            foreach ($jsst_result['answeredticket'] as $jsst_ticket) {
                $jsst_ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created));
                if($jsst_ticket_date == $jsst_fromdate)
                    $jsst_answeredticket += 1;
            }
            foreach ($jsst_result['overdueticket'] as $jsst_ticket) {
                $jsst_ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created));
                if($jsst_ticket_date == $jsst_fromdate)
                    $jsst_overdueticket += 1;
            }
            foreach ($jsst_result['pendingticket'] as $jsst_ticket) {
                $jsst_ticket_date = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created));
                if($jsst_ticket_date == $jsst_fromdate)
                    $jsst_pendingticket += 1;
            }
            $jsst_data .= '"'.$jsst_fromdate.'"'.$jsst_tb.'"'.$jsst_openticket.'"'.$jsst_tb.'"'.$jsst_answeredticket.'"'.$jsst_tb.'"'.$jsst_closeticket.'"'.$jsst_tb.'"'.$jsst_pendingticket.'"'.$jsst_tb.'"'.$jsst_overdueticket.'"'.$jsst_nl;
            $jsst_fromdate = date_i18n("Y-m-d", jssupportticketphplib::JSST_strtotime("+1 day", jssupportticketphplib::JSST_strtotime($jsst_fromdate)));
        }
        $jsst_data .= $jsst_nl.$jsst_nl.$jsst_nl;
        // END By 1 month

        // by staffs
        $jsst_data .= esc_html(__('Users Tickets', 'js-support-ticket')).$jsst_nl.$jsst_nl;
        if(!empty($jsst_result['users'])){
            $jsst_data .= esc_html(__('Name', 'js-support-ticket')).$jsst_tb. esc_html(__('Username', 'js-support-ticket')).$jsst_tb. esc_html(__('Email', 'js-support-ticket')).$jsst_tb. esc_html(__('New', 'js-support-ticket')).$jsst_tb. esc_html(__('Answered', 'js-support-ticket')).$jsst_tb. esc_html(__('Closed', 'js-support-ticket')).$jsst_tb. esc_html(__('Pending', 'js-support-ticket')).$jsst_tb. esc_html(__('Overdue', 'js-support-ticket')).$jsst_nl;
            $jsst_key = $jsst_result['users'];
            $jsst_agentname = $jsst_key->display_name;
            $jsst_username = $jsst_key->user_nicename;
            $jsst_email = $jsst_key->user_email;

            $jsst_data .= '"'.$jsst_agentname.'"'.$jsst_tb.'"'.$jsst_username.'"'.$jsst_tb.'"'.$jsst_email.'"'.$jsst_tb.'"'.$jsst_key->openticket.'"'.$jsst_tb.'"'.$jsst_key->answeredticket.'"'.$jsst_tb.'"'.$jsst_key->closeticket.'"'.$jsst_tb.'"'.$jsst_key->pendingticket.'"'.$jsst_tb.'"'.$jsst_key->overdueticket.'"'.$jsst_nl;

            $jsst_data .= $jsst_nl.$jsst_nl.$jsst_nl;
        }

        // by priorits tickets
        $jsst_data .= esc_html(__('Tickets', 'js-support-ticket')).$jsst_nl.$jsst_nl;
        if(!empty($jsst_result['tickets'])){
            $jsst_data .= esc_html(__('Subject', 'js-support-ticket')).$jsst_tb. esc_html(__('Status', 'js-support-ticket')).$jsst_tb. esc_html(__('Priority', 'js-support-ticket')).$jsst_tb. esc_html(__('Created', 'js-support-ticket'));

             if(in_array('feedback', jssupportticket::$_active_addons)){
                $jsst_data .= $jsst_tb. esc_html(__('Rating', 'js-support-ticket'));
            }
            if(in_array('timetracking', jssupportticket::$_active_addons)){
                $jsst_data .= $jsst_tb. esc_html(__('Time', 'js-support-ticket'));
            }
            $jsst_data .= $jsst_nl;
            $jsst_status = '';
            foreach ($jsst_result['tickets'] as $jsst_ticket) {
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    $jsst_hours = floor($jsst_ticket->time / 3600);
                    $jsst_mins = floor($jsst_ticket->time / 60);
                    $jsst_mins = floor($jsst_mins % 60);
                    $jsst_secs = floor($jsst_ticket->time % 60);
                    $jsst_time = sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                }
                /*switch($jsst_ticket->status){
                    case 0:
                        $jsst_status = esc_html(__('New','js-support-ticket'));
                        if($jsst_ticket->isoverdue == 1)
                            $jsst_status = esc_html(__('Overdue','js-support-ticket'));
                    break;
                    case 1:
                        $jsst_status = esc_html(__('Pending','js-support-ticket'));
                        if($jsst_ticket->isoverdue == 1)
                            $jsst_status = esc_html(__('Overdue','js-support-ticket'));
                    break;
                    case 2:
                        $jsst_status = esc_html(__('In Progress','js-support-ticket'));
                        if($jsst_ticket->isoverdue == 1)
                            $jsst_status = esc_html(__('Overdue','js-support-ticket'));
                    break;
                    case 3:
                        $jsst_status = esc_html(__('Answered','js-support-ticket'));
                        if($jsst_ticket->isoverdue == 1)
                            $jsst_status = esc_html(__('Overdue','js-support-ticket'));
                    break;
                    case 4:
                        $jsst_status = esc_html(__('Closed','js-support-ticket'));
                    break;
                    case 5:
                        $jsst_status = esc_html(__('Merged','js-support-ticket'));
                    break;
                }*/
                if (!in_array($jsst_ticket->status, [5, 6]) && $jsst_ticket->isoverdue == 1) {
                    $jsst_status = __('Overdue', 'js-support-ticket');
                } else {
                    $jsst_status = $jsst_ticket->statustitle;
                }
                $jsst_created = date_i18n('Y-m-d',strtotime($jsst_ticket->created));
                $jsst_data .= '"'.$jsst_ticket->subject.'"'.$jsst_tb.'"'.$jsst_status.'"'.$jsst_tb.'"'.$jsst_ticket->priority.'"'.$jsst_tb.'"'.$jsst_created.'"';

                if(in_array('feedback', jssupportticket::$_active_addons)){
                    $jsst_data .= $jsst_tb.'"'.$jsst_ticket->rating.'"';
                }
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    $jsst_data .= $jsst_tb.'"'.$jsst_time.'"';
                }
                $jsst_data .= $jsst_nl;
            }
            $jsst_data .= $jsst_nl.$jsst_nl.$jsst_nl;
        }
        return $jsst_data;
    }

    function anonymizeUserData($jsst_uid){
        if(!is_numeric($jsst_uid) || $jsst_uid == 0){
            return false;
        }
        global $wpdb, $wp_filesystem; // Use global wpdb and filesystem
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = ".esc_sql($jsst_uid);
        $jsst_uids = jssupportticket::$_db->get_results($jsst_query);

        foreach ($jsst_uids as $jsst_ticket) { 
            // ticket data
            $jsst_row = JSSTincluder::getJSTable('tickets');
            $jsst_row->update(array('id' => $jsst_ticket->id, 'email'=>'---', 'subject'=>'---', 'message'=>'---', 'phone'=>'', 'phoneext'=>'', 'params' => ''));

            // erase replies data
            $jsst_query = "SELECT replies.id AS replyid
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS replies
                        WHERE replies.ticketid = ".esc_sql($jsst_ticket->id);
            $jsst_replies = jssupportticket::$_db->get_results($jsst_query);
            foreach ($jsst_replies as $jsst_reply) {
                $jsst_row = JSSTincluder::getJSTable('replies');
                $jsst_row->update(array('id' => $jsst_reply->replyid, 'message' => '---'));
            }

            // erase internal note data
            if(in_array('note', jssupportticket::$_active_addons)){
                $jsst_query = "SELECT notes.id AS noteid
                            FROM `" . jssupportticket::$_db->prefix . "js_ticket_notes` AS notes
                            WHERE notes.ticketid = ".esc_sql($jsst_ticket->id);
                $jsst_notes = jssupportticket::$_db->get_results($jsst_query);
                foreach ($jsst_notes as $jsst_note) {
                    $jsst_row = JSSTincluder::getJSTable('note');
                    $jsst_row->update(array('id' => $jsst_note->noteid, 'title' => '---', 'note' => '---'));
                }
            }
            //activity log for ticket
            if(in_array('tickethistory', jssupportticket::$_active_addons)){
                $jsst_query = "DELETE
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_activity_log`
                        WHERE eventfor = 1 AND referenceid = ".esc_sql($jsst_ticket->id);
                jssupportticket::$_db->query($jsst_query);

            }
            // private credentails for ticket
            if(in_array('privatecredentials',jssupportticket::$_active_addons)){
                JSSTincluder::getJSModel('privatecredentials')->deleteCredentialsOnCloseTicket($jsst_ticket->id);
            }
            // Ticket attachments section
            $jsst_datadirectory = jssupportticket::$_config['data_directory'];
            $jsst_maindir = wp_upload_dir();
            $jsst_mainpath = $jsst_maindir['basedir'] . '/' . $jsst_datadirectory . '/attachmentdata';

            $jsst_query = "SELECT ticket.attachmentdir FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket WHERE ticket.id = ".esc_sql($jsst_ticket->id);
            $jsst_foldername = jssupportticket::$_db->get_var($jsst_query);

            if(!empty($jsst_foldername)){
                $jsst_folder_path = $jsst_mainpath . '/ticket/' . $jsst_foldername;

                // Use WP_Filesystem instead of file_exists, unlink, and rmdir
                if($jsst_wp_filesystem->exists($jsst_folder_path)){
                    // Setting the second parameter to 'true' deletes the folder and all files inside recursively
                    $jsst_wp_filesystem->delete($jsst_folder_path, true);
                }
            }

            $jsst_query = $wpdb->prepare( "DELETE FROM `" . esc_sql( jssupportticket::$_db->prefix ) . "js_ticket_attachments` WHERE ticketid = %d", $jsst_ticket->id ) ;
            jssupportticket::$_db->query($jsst_query);
        }

        // Use prepare for final status update
        jssupportticket::$_db->update(
            jssupportticket::$_db->prefix . 'js_ticket_erasedatarequests',
            ['status' => 2],
            ['uid' => (int) $jsst_uid]
        );

        JSSTmessage::setMessage(esc_html(__('User identifying data erased', 'js-support-ticket')), 'updated');
        $jsst_user_data = get_user_by('ID',$jsst_uid);
        $jsst_email = $jsst_user_data->user_email;
        $jsst_name = $jsst_user_data->display_name;
        jssupportticket::$jsst_data['mail_data']['email'] = $jsst_email;
        jssupportticket::$jsst_data['mail_data']['name'] = $jsst_name;
        JSSTincluder::getJSModel('email')->sendMail(4, 1); // Mailfor, Delete Ticket
        return;
    }

function deleteUserData($jsst_uid){
    if(!is_numeric($jsst_uid) || $jsst_uid == 0){
        return false;
    }
    $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = ".esc_sql($jsst_uid);
    $jsst_uids = jssupportticket::$_db->get_results($jsst_query);

    foreach ($jsst_uids as $jsst_ticket) { // erase tickets data
        // ticket data

        $jsst_row = JSSTincluder::getJSTable('tickets');
        $jsst_row->delete($jsst_ticket->id);

        if(in_array('note', jssupportticket::$_active_addons)){
            // delete internal notes
            JSSTincluder::getJSModel('note')->removeTicketInternalNote($jsst_ticket->id);
        }
        // delete replies
        JSSTincluder::getJSModel('reply')->removeTicketReplies($jsst_ticket->id);

        // private credentails for ticket
        if(in_array('privatecredentials',jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('privatecredentials')->deleteCredentialsOnCloseTicket($jsst_ticket->id);
        }
        // --- TICKET ATTACHMENTS DELETION ---
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_maindir = wp_upload_dir();
        $jsst_mainpath = $jsst_maindir['basedir'] . '/' . $jsst_datadirectory . '/attachmentdata';

        // Using prepare for secure query with your required prefix format
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT attachmentdir FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
            $jsst_ticket->id
        );
        $jsst_foldername = jssupportticket::$_db->get_var($jsst_query);

        if (!empty($jsst_foldername)) {
            $jsst_folder_path = $jsst_mainpath . '/ticket/' . $jsst_foldername;

            // --- INITIALIZE WP_FILESYSTEM ---
            global $wp_filesystem;
            if (!function_exists('wp_handle_upload')) {
                do_action('jssupportticket_load_wp_file');
            }
            if ( ! WP_Filesystem() ) {
                return false;
            }
            $jsst_wp_filesystem = $wp_filesystem;

            // Use WP_Filesystem exists() and delete()
            if ($jsst_wp_filesystem->exists($jsst_folder_path)) {
                /**
                 * The second parameter 'true' makes the delete recursive.
                 * This single line replaces glob(), array_map('unlink'), and rmdir().
                 */
                $jsst_wp_filesystem->delete($jsst_folder_path, true);
            }
        }
        $jsst_query = "DELETE FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` WHERE ticketid = ".esc_sql($jsst_ticket->id);
        jssupportticket::$_db->query($jsst_query);
    }
    $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_erasedatarequests` SET status = 3 WHERE uid = ".esc_sql($jsst_uid);
    jssupportticket::$_db->query($jsst_query);

    $jsst_user_data = get_user_by('ID',$jsst_uid);

    JSSTmessage::setMessage(esc_html(__('User data Deleted', 'js-support-ticket')), 'updated');
    $jsst_user_data = get_user_by('ID',$jsst_uid);
    $jsst_email = $jsst_user_data->user_email;
    $jsst_name = $jsst_user_data->display_name;
    jssupportticket::$jsst_data['mail_data']['email'] = $jsst_email;
    jssupportticket::$jsst_data['mail_data']['name'] = $jsst_name;
    JSSTincluder::getJSModel('email')->sendMail(4, 1); // Mailfor, Delete Ticket
}

    function getAdminSearchFormDataGDPR(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'erase-data-requests') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['email'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('email')));
        $jsst_search_array['search_from_gdpr'] = 1;
        return $jsst_search_array;
    }
}
?>
