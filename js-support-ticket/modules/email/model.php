<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTemailModel {
    /*
      $jsst_mailfor
      For which purpose you want to send mail
      1 => Ticket

      $jsst_action
      For which action of $jsst_mailfor you want to send the mail
      1 => New Ticket Create
      2 => Close Ticket
      3 => Delete Ticket
      4 => Reply Ticket (Admin/Staff Member)
      5 => Reply Ticket (Ticket member)
      6 => Lock Ticket

      $jsst_id
      id required when recever emailaddress is stored in record
     */

    function sendMail($jsst_mailfor, $jsst_action, $jsst_id = null, $jsst_tablename = null) {
        if (!is_numeric($jsst_mailfor))
            return false;
        if (!is_numeric($jsst_action))
            return false;
        if ($jsst_id != null)
            if (!is_numeric($jsst_id))
                return false;
        $jsst_pageid = jssupportticket::getPageid();
		$jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
		$jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
		
        switch ($jsst_mailfor) {
            case 1: // Mail For Tickets
                switch ($jsst_action) {
                    case 1: // New Ticket Created
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        if (isset($jsst_ticketRecord->name) && isset($jsst_ticketRecord->subject) && isset($jsst_ticketRecord->ticketid) && isset($jsst_ticketRecord->email)) {
                        $Username = $jsst_ticketRecord->name;
                        $Subject = $jsst_ticketRecord->subject;
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $Email = $jsst_ticketRecord->email;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Message = $jsst_ticketRecord->message;
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{MESSAGE}' => $Message,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );

                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;

                        // New ticket mail to admin
                        if(jssupportticket::$_config['new_ticket_mail_to_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','ticket-new-admin' , $jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $this->getTemplateForEmail('ticket-new-admin', $jsst_ticketRecord->multiformid);
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###admin####" />';
                            $jsst_msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###admin#### ></span>';
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'ticket-new-admin');
                        }
                        //Check to send email to department
                        $jsst_query = "SELECT dept.sendmail, email.email AS emailaddress
                                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept ON dept.id = ticket.departmentid
                                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_email` AS email ON email.id = dept.emailid
                                    WHERE ticket.id = ".esc_sql($jsst_id);
                        $jsst_dept_result = jssupportticket::$_db->get_row($jsst_query);
                        if($jsst_dept_result){
                            if(isset($jsst_dept_result->sendmail) && $jsst_dept_result->sendmail == 1){
                                $jsst_deptemail = $jsst_dept_result->emailaddress;
                                $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','ticket-new-admin' , $jsst_deptemail ,'', $jsst_ticketRecord->multiformid);
                                if($jsst_template == '' && empty($jsst_template)){
                                    $jsst_template = $this->getTemplateForEmail('ticket-new-admin', $jsst_ticketRecord->multiformid);
                                }

                                $jsst_msgSubject = $jsst_template->subject;
                                $jsst_msgBody = $jsst_template->body;

                                $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
                                $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                                $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###admin####" />';
                                $jsst_msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###admin#### ></span>';
                                $jsst_attachments = '';
                                $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                                $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                                $this->sendEmail($jsst_deptemail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'ticket-new-admin');
                            }
                        }
                        // New ticket mail to User
                        $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','ticket-new' , $jsst_ticketRecord->email , $jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                        if($jsst_template == '' && empty($jsst_template)){
                            $jsst_template = $this->getTemplateForEmail('ticket-new', $jsst_ticketRecord->multiformid);
                        }
                        //Parsing template
                        $jsst_msgSubject = $jsst_template->subject;
                        $jsst_msgBody = $jsst_template->body;
                        //token encrption
                        $jsst_tokenarray['emailaddress']=$Email;
                        $jsst_tokenarray['trackingid']=$TrackingId;
                        $jsst_tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                        $jsst_token = wp_json_encode($jsst_tokenarray);
                        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                        $jsst_encoder = new JSSTEncoder();
                        $jsst_encryptedtext = $jsst_encoder->encrypt($jsst_token);
                        // end token encryotion
                        $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'showticketstatus','action'=>'jstask','token'=>$jsst_encryptedtext,'jsstpageid'=>jssupportticket::getPageid())));
                        $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                        $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                        $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                        $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###user####" />';
                        $jsst_msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###user#### ></span>';
                        $jsst_attachments = '';
                        $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);

                        //New ticket mail to staff member
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['new_ticket_mail_to_staff_members'] == 1) {
                            // Get All Staff member of the department of Current Ticket
                            if ( in_array('agentautoassign',jssupportticket::$_active_addons) && isset(jssupportticket::$_config['department_email_on_ticket_create']) && jssupportticket::$_config['department_email_on_ticket_create'] == 2) {
                                $jsst_agentmembers = JSSTincluder::getJSModel('agentautoassign')->getAllStaffMemberByDepId($jsst_ticketRecord->departmentid);
                            }
                            else{
                                $jsst_agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByDepId($jsst_ticketRecord->departmentid);
                            }
                            if(is_array($jsst_agentmembers) && !empty($jsst_agentmembers)){
                                foreach ($jsst_agentmembers AS $jsst_agent) {
                                    if($jsst_agent->canemail == 1){
                                        $jsst_staffuid = $jsst_agent->staffuid;
                                        $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','ticket-staff' , $jsst_agent->email , $jsst_staffuid, $jsst_ticketRecord->multiformid);
                                        if($jsst_template == '' && empty($jsst_template)){
                                            $jsst_template = $this->getTemplateForEmail('ticket-staff', $jsst_ticketRecord->multiformid);
                                        }

                                        $jsst_msgSubject = $jsst_template->subject;
                                        $jsst_msgBody = $jsst_template->body;
                                        $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));
                                        $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                                        $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                                        $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                                        $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###" />';
                                        $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                        $jsst_msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                        $jsst_attachments = '';
                                        $this->sendEmail($jsst_agent->email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'ticket-staff');
                                    }
                                }
                            }
                        }
                        }
                        break;
                    case 2: // Close Ticket
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $Username = $jsst_ticketRecord->name;
                        $Subject = $jsst_ticketRecord->subject;
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $Email = $jsst_ticketRecord->email;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Message = $jsst_ticketRecord->message;
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{MESSAGE}' => $Message,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')

                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('close-tk', $jsst_ticketRecord->multiformid);
                        // Close ticket mail to admin
                        if (jssupportticket::$_config['ticket_close_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','close-tk' , $jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }

                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_matcharray['{FEEDBACKURL}'] = ' ';
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'close-tk-admin');
                        }
                        // Close ticket mail to staff member
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_close_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','close-tk' , $jsst_agentEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }

                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_matcharray['{FEEDBACKURL}'] = ' ';
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'close-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_close_user'] == 1) {
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));
                            $jsst_tokenarray['emailaddress']=$Email;
                            $jsst_tokenarray['trackingid']=$TrackingId;
                            $jsst_token = wp_json_encode($jsst_tokenarray);
                            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                            $jsst_encoder = new JSSTEncoder();
                            $jsst_encryptedtext = $jsst_encoder->encrypt($jsst_token);
                            if(in_array('feedback', jssupportticket::$_active_addons)){
                                $jsst_flink = "<a href=" . esc_url(jssupportticket::makeUrl(array('jstmod'=>'feedback', 'task'=>'showfeedbackform','action'=>'jstask','token'=>$jsst_encryptedtext,'jsstpageid'=>jssupportticket::getPageid()))) . ">". esc_html(__('Click here to give us feedback','js-support-ticket'))." </a>";
                            }else{
                                $jsst_flink = " ";
                            }

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','close-tk' , $Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_matcharray['{FEEDBACKURL}'] = $jsst_flink;
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 3: // Delete Ticket
                        $TrackingId = jssupportticket::$jsst_data['ticketid'];
                        $Email = jssupportticket::$jsst_data['ticketemail'];
                        $Subject = jssupportticket::$jsst_data['ticketsubject'];
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{TRACKINGID}' => $TrackingId,
                            '{SUBJECT}' => $Subject,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $jsst_object = $this->getSenderEmailAndName(null);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('delete-tk');
                        // Delete ticket mail to admin
                        if (jssupportticket::$_config['ticket_delete_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','delete-tk' , $jsst_adminEmail ,'');
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'delete-tk-admin');
                        }
                        // Delete ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_delete_staff'] == 1) {
                            $jsst_agent_id = jssupportticket::$jsst_data['staffid'];
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_agent_id);
                            if( ! empty($jsst_agentEmail)){
                                $jsst_staffuid = $this->getStaffUidByStaffId(jssupportticket::$jsst_data['staffid']);
                                $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','delete-tk' , $jsst_agentEmail ,$jsst_staffuid);
                                if($jsst_template == '' && empty($jsst_template)){
                                    $jsst_template = $jsst_defaulttemplate;
                                }
                                $jsst_msgSubject = $jsst_template->subject;
                                $jsst_msgBody = $jsst_template->body;
                                $jsst_attachments = '';
                                $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                                $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                                $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'delete-tk-staff');
                            }
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_delete_user'] == 1) {
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','delete-tk' , $Email , '');
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 4: // Reply Ticket (Admin/Staff Member)
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $Username = $jsst_ticketRecord->name;
                        $Subject = $jsst_ticketRecord->subject;
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $Message = $this->getLatestReplyByTicketId($jsst_id);
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{MESSAGE}' => $Message,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('reply-tk');
                        // Reply ticket mail to admin
                        if (jssupportticket::$_config['ticket_response_to_staff_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' , $jsst_adminEmail , '', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }

                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###admin####" />';
                            $jsst_attachments = '';
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'reply-tk-admin');
                        }
                        // Reply ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_response_to_staff_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' , $jsst_agentEmail , $jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                            $jsst_attachments = '';
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'reply-tk-staff');
                        }
                        // New ticket mail to User
                        $jsst_template = $this->getTemplateForEmail('responce-tk');
                        if (jssupportticket::$_config['ticket_response_to_staff_user'] == 1) {
                            //token encrption
                            $jsst_tokenarray['emailaddress']=$Email;
                            $jsst_tokenarray['trackingid']=$TrackingId;
                            $jsst_tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                            $jsst_token = wp_json_encode($jsst_tokenarray);
                            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                            $jsst_encoder = new JSSTEncoder();
                            $jsst_encryptedtext = $jsst_encoder->encrypt($jsst_token);
                            // end token encryotion
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'showticketstatus','action'=>'jstask','token'=>$jsst_encryptedtext,'jsstpageid'=>jssupportticket::getPageid())));
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' , $Email , $jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###user####" />';
                            $jsst_attachments = '';
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 5: // Reply Ticket (Ticket Member)
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $Username = $jsst_ticketRecord->name;
                        $Subject = $jsst_ticketRecord->subject;
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $Message = $this->getLatestReplyByTicketId($jsst_id);
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{MESSAGE}' => $Message,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('reply-tk');
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_reply_ticket_user_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###admin####" />';
                            $jsst_attachments = '';
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'reply-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_reply_ticket_user_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' ,$jsst_adminEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }

                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                            $jsst_attachments = '';
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'reply-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_reply_ticket_user_user'] == 1) {
                            //token encrption
                            $jsst_tokenarray['emailaddress']=$Email;
                            $jsst_tokenarray['trackingid']=$TrackingId;
                            $jsst_tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                            $jsst_token = wp_json_encode($jsst_tokenarray);
                            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                            $jsst_encoder = new JSSTEncoder();
                            $jsst_encryptedtext = $jsst_encoder->encrypt($jsst_token);
                            // end token encryotion
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket' ,'task'=>'showticketstatus','action'=>'jstask','token'=>$jsst_encryptedtext,'jsstpageid'=>jssupportticket::getPageid())));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }

                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###user####" />';
                            $jsst_attachments = '';
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 6: // Lock Ticket
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $Username = $jsst_ticketRecord->name;
                        $Subject = $jsst_ticketRecord->subject;
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('lock-tk', $jsst_ticketRecord->multiformid);
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_lock_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','lock-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'lock-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_lock_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','lock-tk' ,$jsst_agentEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'lock-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_lock_user'] == 1) {
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','lock-tk' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 7: // Unlock Ticket
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $Username = $jsst_ticketRecord->name;
                        $Subject = $jsst_ticketRecord->subject;
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('unlock-tk', $jsst_ticketRecord->multiformid);
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_unlock_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unlock-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                            $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'unlock-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_unlock_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unlock-tk' ,$jsst_agentEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                            $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'unlock-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_unlock_user'] == 1) {
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unlock-tk' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 8: // Markoverdue Ticket
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $Subject = $jsst_ticketRecord->subject;
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{SUBJECT}' => $Subject,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('moverdue-tk', $jsst_ticketRecord->multiformid);
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_mark_overdue_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'moverdue-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_mark_overdue_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$jsst_adminEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'moverdue-tk-staff');
                            // Get All Staff member of the department of Current Ticket
                            $jsst_agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByDepId($jsst_ticketRecord->departmentid);
                            if(is_array($jsst_agentmembers) && !empty($jsst_agentmembers)){
                                foreach ($jsst_agentmembers AS $jsst_agent) {
                                    if($jsst_agent->canemail == 1){
                                        $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$jsst_agent->email ,$jsst_agent->staffuid, $jsst_ticketRecord->multiformid);
                                        if($jsst_template == '' && empty($jsst_template)){
                                            $jsst_template = $jsst_defaulttemplate;
                                        }
                                        $jsst_msgSubject = $jsst_template->subject;
                                        $jsst_msgBody = $jsst_template->body;
                                        $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                        $jsst_msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                        $jsst_attachments = '';
                                        $this->sendEmail($jsst_agent->email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                                    }
                                }
                            }
                            // send email to staff memebers with all ticket permissions
                            if( !is_numeric($jsst_ticketRecord->staffid) && !is_numeric($jsst_ticketRecord->departmentid)){
                                if( in_array('agent',jssupportticket::$_active_addons)){
                                    $jsst_agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByAllTicketPermission();
                                    if(is_array($jsst_agentmembers) && !empty($jsst_agentmembers)){
                                        foreach ($jsst_agentmembers AS $jsst_agent) {
                                            if($jsst_agent->canemail == 1){
                                                $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$jsst_agent->email,$jsst_agent->uid, $jsst_ticketRecord->multiformid);
                                                if($jsst_template == '' && empty($jsst_template)){
                                                    $jsst_template = $jsst_defaulttemplate;
                                                }
                                                $jsst_msgSubject = $jsst_template->subject;
                                                $jsst_msgBody = $jsst_template->body;
                                                $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                                $jsst_msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                                $jsst_attachments = '';

                                                $this->sendEmail($jsst_agent->email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_mark_overdue_user'] == 1) {
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 9: // Mark in progress Ticket
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $Subject = $jsst_ticketRecord->subject;
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{SUBJECT}' => $Subject,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('minprogress-tk', $jsst_ticketRecord->multiformid);
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_mark_progress_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','minprogress-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'minprogress-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_mark_progress_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','minprogress-tk'
                             ,$jsst_agentEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'minprogress-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_mark_progress_user'] == 1) {
                            $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));

                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','minprogress-tk' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 10: // Ban email and close Ticket
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $Subject = $jsst_ticketRecord->subject;
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{EMAIL_ADDRESS}' => $Email,
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('banemailcloseticket-tk', $jsst_ticketRecord->multiformid);

                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticker_ban_eamil_and_close_ticktet_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemailcloseticket-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'banemailcloseticket-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticker_ban_eamil_and_close_ticktet_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemailcloseticket-tk' ,$jsst_adminEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'banemailcloseticket-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticker_ban_eamil_and_close_ticktet_user'] == 1) {
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemailcloseticket-tk' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 11: // Priority change ticket
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $Subject = $jsst_ticketRecord->subject;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $Priority = JSSTincluder::getJSModel('priority')->getPriorityById($jsst_ticketRecord->priorityid);
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{PRIORITY_TITLE}' => $Priority,
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('prtrans-tk', $jsst_ticketRecord->multiformid);

                        // New ticket mail to admin
						$jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','prtrans-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
						if($jsst_template == '' && empty($jsst_template)){
							$jsst_template = $jsst_defaulttemplate;
						}
                        if (jssupportticket::$_config['ticket_priority_admin'] == 1) {
                            $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
                            $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'prtrans-tk-admin');
                        }
                        $jsst_msgSubject = $jsst_template->subject;
                        $jsst_msgBody = $jsst_template->body;
                        $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));
                        $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_priority_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','prtrans-tk' ,$jsst_agentEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'prtrans-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_priority_user'] == 1) {
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','prtrans-tk' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 12: // DEPARTMENT TRANSFER
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $Subject = $jsst_ticketRecord->subject;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $Department = JSSTincluder::getJSModel('department')->getDepartmentById($jsst_ticketRecord->departmentid);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT_TITLE}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('deptrans-tk', $jsst_ticketRecord->multiformid);
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_department_transfer_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'deptrans-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_department_transfer_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$jsst_agentEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'deptrans-tk-staff');

                            // send email to all staff memebers of current ticket department
                            // Get All Staff member of the department of Current Ticket
                            $jsst_agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByDepId($jsst_ticketRecord->departmentid);
                            if(is_array($jsst_agentmembers) && !empty($jsst_agentmembers)){
                                foreach ($jsst_agentmembers AS $jsst_agent) {
                                    if($jsst_agent->canemail == 1){
                                        $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$jsst_agent->email ,$jsst_agent->staffuid, $jsst_ticketRecord->multiformid);
                                        if($jsst_template == '' && empty($jsst_template)){
                                            $jsst_template = $jsst_defaulttemplate;
                                        }
                                        $jsst_msgSubject = $jsst_template->subject;
                                        $jsst_msgBody = $jsst_template->body;
                                        $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                                        $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                                        $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                        $jsst_msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                        $jsst_attachments = '';
                                        $this->sendEmail($jsst_agent->email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                                    }
                                }
                            }
                            // send email to staff memebers with all ticket permissions
                            if( !is_numeric($jsst_ticketRecord->staffid) && !is_numeric($jsst_ticketRecord->departmentid)){
                                if( in_array('agent',jssupportticket::$_active_addons) ){
                                    $jsst_agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByAllTicketPermission();
                                    if(is_array($jsst_agentmembers) && !empty($jsst_agentmembers)){
                                        foreach ($jsst_agentmembers AS $jsst_agent) {
                                            if($jsst_agent->canemail == 1){
                                                $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$jsst_agent->email ,$jsst_agent->uid, $jsst_ticketRecord->multiformid);
                                                if($jsst_template == '' && empty($jsst_template)){
                                                    $jsst_template = $jsst_defaulttemplate;
                                                }
                                                $jsst_msgSubject = $jsst_template->subject;
                                                $jsst_msgBody = $jsst_template->body;
                                                $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                                                $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                                                $jsst_msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                                $jsst_msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                                $jsst_attachments = '';
                                                $this->sendEmail($jsst_agent->email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_department_transfer_user'] == 1) {
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$Email,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 13: // REASSIGN TICKET TO STAFF
                        if(! in_array('agent',jssupportticket::$_active_addons) ){
                            return;
                        }
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $DepName = $jsst_ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $jsst_ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $jsst_ticketRecord->email;
                        $Subject = $jsst_ticketRecord->subject;
                        $Staff = JSSTincluder::getJSModel('agent')->getMyName($jsst_ticketRecord->staffid);
                        $jsst_ticketHistory = $this->getTicketReplyHistory($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $Staff,
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('reassign-tk', $jsst_ticketRecord->multiformid);
                        // New ticket mail to admin
                        $jsst_link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_id));
                        $jsst_matcharray['{TICKETURL}'] = $jsst_link;
			            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
			            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                        if (jssupportticket::$_config['ticket_reassign_admin'] == 1) {

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reassign-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'reassign-tk-admin');
                        }

                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $Staff,
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{TICKET_HISTORY}' => $jsst_ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_id,'jsstpageid'=>jssupportticket::getPageid())));
                        $jsst_matcharray['{TICKETURL}'] = $jsst_link;
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_reassign_staff'] == 1) {
                            $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                            $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reassign-tk' ,$jsst_adminEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }

                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'reassign-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_reassign_user'] == 1) {
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reassign-tk' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 14: // Reply to closed ticket for Email Piping
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $Subject = $jsst_ticketRecord->subject;
                        $Email = $jsst_ticketRecord->email;
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{SUBJECT}' => $Subject,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('mail-rpy-closed', $jsst_ticketRecord->multiformid);
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_reply_closed_ticket_user'] == 1) {
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','mail-rpy-closed' ,$Email ,$jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 15: // Send feedback email to user
                        if(!in_array('feedback', jssupportticket::$_active_addons)){
                            break;
                        }
                        $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $Subject = $jsst_ticketRecord->subject;
                        $Email = $jsst_ticketRecord->email;
                        $TrackingId = $jsst_ticketRecord->ticketid;
                        $jsst_close_date = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticketRecord->closed));
                        $jsst_username = $jsst_ticketRecord->name;
                        $jsst_tokenarray['emailaddress']=$Email;
                        $jsst_tokenarray['trackingid']=$TrackingId;
                        $jsst_tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                        $jsst_token = wp_json_encode($jsst_tokenarray);
                        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                        $jsst_encoder = new JSSTEncoder();
                        $jsst_encryptedtext = $jsst_encoder->encrypt($jsst_token);
                        $jsst_link = "<a href=" . esc_url(jssupportticket::makeUrl(array('jstmod'=>'feedback', 'task'=>'showfeedbackform','action'=>'jstask','token'=>$jsst_encryptedtext,'jsstpageid'=>jssupportticket::getPageid()))) . ">";
                        $jsst_linkclosing = "</a>";
                        $jsst_tracking_url = "<a href=" . esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'showticketstatus','action'=>'jstask','token'=>$jsst_encryptedtext,'jsstpageid'=>jssupportticket::getPageid()))) . ">" . $TrackingId . "</a>";
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $jsst_username,
                            '{USER_NAME}' => $jsst_username,
                            '{TICKET_SUBJECT}' => $Subject,
                            '{TRACKING_ID}' => $jsst_tracking_url,
                            '{CLOSE_DATE}' => $jsst_close_date,
                            '{LINK}' => $jsst_link,
                            '{/LINK}' => $jsst_linkclosing,
                            '{DEPARTMENT}' => $jsst_ticketRecord->departmentname,
                            '{PRIORITY}' => $jsst_ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $jsst_fvalue = '';
                        if(!empty($jsst_ticketRecord->params)){
                            $jsst_data = json_decode($jsst_ticketRecord->params,true);
                        }
                        $jsst_fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($jsst_data) && is_array($jsst_data)){
                            foreach ($jsst_fields as $jsst_field) {
                                if($jsst_field->userfieldtype != 'file'){
                                    $jsst_fvalue = '';
                                    if(array_key_exists($jsst_field->field, $jsst_data)){
                                        $jsst_fvalue = $jsst_data[$jsst_field->field];
                                    }
                                    $jsst_matcharray['{'.$jsst_field->field.'}'] = $jsst_fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('mail-feedback', $jsst_ticketRecord->multiformid);
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_feedback_user'] == 1) {
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','mail-feedback' ,$Email ,$jsst_ticketRecord->uid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                }
                break;
            case 2: // Ban Email
                switch ($jsst_action) {
                    case 1: // Ban Email
                        if ($jsst_tablename != null)
                            $jsst_banemailRecord = $this->getRecordByTablenameAndId($jsst_tablename, $jsst_id);
                        else
                            $jsst_banemailRecord = $this->getRecordByTablenameAndId('js_ticket_email_banlist', $jsst_id);
                        $Email = $jsst_banemailRecord->email;
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{EMAIL_ADDRESS}' => $Email,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $jsst_object = $this->getDefaultSenderEmailAndName();
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('banemail-tk');

                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_ban_email_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemail-tk' ,$jsst_adminEmail ,'');
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'banemail-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_ban_email_staff'] == 1) {
                            if ($jsst_tablename != null){
                                $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_banemailRecord->staffid);
                                $jsst_staffuid = $this->getStaffUidByStaffId($jsst_banemailRecord->staffid);
                            }else{
                                $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_banemailRecord->submitter);
                                $jsst_staffuid = $this->getStaffUidByStaffId($jsst_banemailRecord->submitter);
                            }

                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemail-tk' ,$jsst_agentEmail ,$jsst_staffuid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'banemail-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_ban_email_user'] == 1) {
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemail-tk' ,$Email ,'');
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';

                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                    case 2: // Unban Email
                        if ($jsst_tablename != null)
                            $jsst_ticketRecord = $this->getRecordByTablenameAndId($jsst_tablename, $jsst_id);
                        else
                            $jsst_ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $jsst_id);
                        $Email = $jsst_ticketRecord->email;
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{EMAIL_ADDRESS}' => $Email,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $jsst_object = $this->getSenderEmailAndName($jsst_id);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('unbanemail-tk');

                        // New ticket mail to admin
                        if (jssupportticket::$_config['unban_email_admin'] == 1) {
                            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unbanemail-tk' ,$jsst_adminEmail ,'', $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'unbanemail-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['unban_email_staff'] == 1) {
                            if ($jsst_tablename != null){
                                $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->staffid);
                                $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->staffid);
                            }else{
                                $jsst_agentEmail = $this->getStaffEmailAddressByStaffId($jsst_ticketRecord->submitter);
                                $jsst_staffuid = $this->getStaffUidByStaffId($jsst_ticketRecord->submitter);
                            }
                            $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unbanemail-tk' ,$jsst_agentEmail ,$jsst_staffuid, $jsst_ticketRecord->multiformid);
                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($jsst_agentEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'unbanemail-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['unban_email_user'] == 1) {
                            if ($jsst_tablename != null){
                                $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unbanemail-tk' , $Email, '', $jsst_ticketRecord->multiformid);
                            }else{
                                $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unbanemail-tk' ,$jsst_ticketRecord->email , $jsst_ticketRecord->uid, $jsst_ticketRecord->multiformid);
                            }

                            if($jsst_template == '' && empty($jsst_template)){
                                $jsst_template = $jsst_defaulttemplate;
                            }
                            $jsst_msgSubject = $jsst_template->subject;
                            $jsst_msgBody = $jsst_template->body;
                            $jsst_attachments = '';
                            $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                            $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                            $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        }
                        break;
                }
                break;
            case 3: // Sending email alerts on mail system
                if(!in_array('mail', jssupportticket::$_active_addons)){ // if mail addon is not installed
                    break;
                }
                switch ($jsst_action) {
                    case 1: // Store message
                        $jsst_mailRecord = $this->getMailRecordById($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $jsst_mailRecord->sendername,
                            '{SUBJECT}' => $jsst_mailRecord->subject,
                            '{MESSAGE}' => $jsst_mailRecord->message,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $jsst_object = $this->getSenderEmailAndName(null);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('mail-new');
                        $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','mail-new' ,'' ,$jsst_mailRecord->staffuid);
                        if($jsst_template == '' && empty($jsst_template)){
                            $jsst_template = $jsst_defaulttemplate;
                        }
                        $jsst_msgSubject = $jsst_template->subject;
                        $jsst_msgBody = $jsst_template->body;

                        $Email = $jsst_mailRecord->receveremail;
                        $jsst_attachments = '';
                        $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                        $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                        $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'mail-new');
                        break;
                    case 2: // Store reply
                        $jsst_mailRecord = $this->getMailRecordById($jsst_id, 1);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $jsst_mailRecord->sendername,
                            '{SUBJECT}' => $jsst_mailRecord->subject,
                            '{MESSAGE}' => $jsst_mailRecord->message,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $jsst_object = $this->getSenderEmailAndName(null);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('mail-rpy');
                        $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','mail-rpy' ,'' ,$jsst_mailRecord->staffuid);
                        if($jsst_template == '' && empty($jsst_template)){
                            $jsst_template = $jsst_defaulttemplate;
                        }
                        $jsst_msgSubject = $jsst_template->subject;
                        $jsst_msgBody = $jsst_template->body;
                        $Email = $jsst_mailRecord->receveremail;
                        $jsst_attachments = '';
                        $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                        $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                        $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'mail-rpy');
                        break;
                }
                break;
            case 4: // gdpr data erase or delte.
                switch ($jsst_action) {
                    case 1: // erase data email
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => jssupportticket::$jsst_data['mail_data']['name'],
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $jsst_object = $this->getSenderEmailAndName(null);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('delete-user-data');
                        $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','delete-user-data' ,jssupportticket::$jsst_data['mail_data']['email'] , '');
                        if($jsst_template == '' && empty($jsst_template)){
                            $jsst_template = $jsst_defaulttemplate;
                        }

                        $jsst_msgSubject = $jsst_template->subject;
                        $jsst_msgBody = $jsst_template->body;
                        $Email = jssupportticket::$jsst_data['mail_data']['email'];
                        $jsst_attachments = '';
                        $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                        $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                        $this->sendEmail($Email, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action);
                        break;
                }
                break;
            case 5: // agent emails
                switch ($jsst_action) {
                    case 1: // new agent
                        $jsst_staffname = JSSTincluder::getJSModel('agent')->getMyName($jsst_id);
                        $jsst_matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $jsst_staffname,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $jsst_object = $this->getSenderEmailAndName(null);
                        $jsst_senderEmail = $jsst_object->email;
                        $jsst_senderName = $jsst_object->name;
                        $jsst_defaulttemplate = $this->getTemplateForEmail('staff-new');

                        $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
                        $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);
                        $jsst_template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','staff-new' , $jsst_adminEmail , '');
                        if($jsst_template == '' && empty($jsst_template)){
                            $jsst_template = $jsst_defaulttemplate;
                        }
                        $jsst_msgSubject = $jsst_template->subject;
                        $jsst_msgBody = $jsst_template->body;
                        $jsst_attachments = '';
                        $this->replaceMatches($jsst_msgSubject, $jsst_matcharray);
                        $this->replaceMatches($jsst_msgBody, $jsst_matcharray);
                        $this->sendEmail($jsst_adminEmail, $jsst_msgSubject, $jsst_msgBody, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, 'staff-new');
                        break;
                }
                break;
        }
    }


    function getMailRecordById($jsst_id, $jsst_replyto = null) { // this function will not be called if the mail addon is not installed
        if (!is_numeric($jsst_id))
            return false;
        if ($jsst_replyto == null) {
            $jsst_query = "SELECT mail.subject,mail.message,CONCAT(staff.firstname,' ',staff.lastname) AS sendername, staff.uid as staffuid
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_mail` AS mail
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.id = mail.fromid
                        WHERE mail.id = " . esc_sql($jsst_id);
        } else {
            $jsst_query = "SELECT mail.subject,reply.message,CONCAT(staff.firstname,' ',staff.lastname) AS sendername, staff.uid as staffuid
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_mail` AS reply
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff_mail` AS mail ON mail.id = reply.replytoid
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.id = reply.fromid
                        WHERE reply.id = " . esc_sql($jsst_id);
        }
        $jsst_result = jssupportticket::$_db->get_row($jsst_query);
            $jsst_query = "SELECT staff.email
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_mail` AS mail
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.id = mail.toid
                        WHERE mail.id = " . esc_sql($jsst_id);
        $jsst_email = jssupportticket::$_db->get_var($jsst_query);
        $jsst_result->receveremail = $jsst_email;
        return $jsst_result;
    }

    private function getStaffEmailAddressByStaffId($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT staff.email
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff
                    WHERE staff.id = " . esc_sql($jsst_id);
        $jsst_emailaddress = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_emailaddress;
    }

    private function getStaffUidByStaffId($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT staff.uid
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff
                    WHERE staff.id = " . esc_sql($jsst_id);
        $jsst_emailaddress = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_emailaddress;
    }

    private function getLatestReplyByTicketId($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT reply.message FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS reply WHERE reply.ticketid = " . esc_sql($jsst_id) . " ORDER BY reply.created DESC LIMIT 1";
        $jsst_message = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_message;
    }

    private function replaceMatches(&$jsst_string, $jsst_matcharray) {
        foreach ($jsst_matcharray AS $jsst_find => $jsst_replace) {
            $jsst_string = jssupportticketphplib::JSST_str_replace($jsst_find, $jsst_replace, $jsst_string);
        }
    }

    function sendEmail($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, $jsst_actionfor='') {

        if( (is_array($jsst_recevierEmail) && empty($jsst_recevierEmail)) || (!is_array($jsst_recevierEmail) && jssupportticketphplib::JSST_trim($jsst_recevierEmail) == '') ){ // avoid the case of trying to send email to empty email.
            return;
        }

        $jsst_enablesmtp = $this->checkSMTPEnableOrDisable($jsst_senderEmail);
        if ($jsst_enablesmtp) {
            $this->sendSMTPmail($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, $jsst_actionfor);
        }else{
            $this->sendEmailDefault($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, $jsst_actionfor);
        }

    }

    private function sendEmailDefault($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, $jsst_actionfor) {
        $jsst_senderName = jssupportticket::$_config['title']; // site name
        /*
          $jsst_attachments = array( WP_CONTENT_DIR . '/uploads/file_to_attach.zip' );
          $jsst_headers = 'From: My Name <myname@example.com>' . "\r\n";
          wp_mail('test@example.org', 'subject', 'message', $jsst_headers, $jsst_attachments );

          $jsst_action
          For which action of $jsst_mailfor you want to send the mail
          1 => New Ticket Create
          2 => Close Ticket
          3 => Delete Ticket
          4 => Reply Ticket (Admin/Staff Member)
          5 => Reply Ticket (Ticket member)
         */
        switch ($jsst_action) {
            case 1:
                do_action('jsst-beforeemailticketcreate', $jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail);
                break;
            case 2:
                do_action('jsst-beforeemailticketreply', $jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail);
                break;
            case 3:
                do_action('jsst-beforeemailticketclose', $jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail);
                break;
            case 4:
                do_action('jsst-beforeemailticketdelete', $jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail);
                break;
        }
        if (!$jsst_senderName)
            $jsst_senderName = jssupportticket::$_config['title'];
        $jsst_headers[] = 'From: ' . $jsst_senderName . ' <' . $jsst_senderEmail . '>' . "\r\n";
        $jsst_headers = apply_filters('jsst_emailcc_send_email_to_cc' , $jsst_headers , $jsst_actionfor); // eg $jsst_actionfor = ticket-new
        add_filter('wp_mail_content_type', array($this,'jsst_set_html_content_type'));
        // $jsst_body = jssupportticketphplib::JSST_preg_replace('/\r?\n|\r/', '<br/>', $jsst_body);
        // $jsst_body = jssupportticketphplib::JSST_str_replace(array("\r\n", "\r", "\n"), "<br/>", $jsst_body);
        // $jsst_body = nl2br($jsst_body);
		if($jsst_recevierEmail){
			if(!wp_mail($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_headers, $jsst_attachments)){
				if($GLOBALS['phpmailer']->ErrorInfo)
					JSSTincluder::getJSModel('systemerror')->addSystemError($GLOBALS['phpmailer']->ErrorInfo);
			}
		}else{
			JSSTincluder::getJSModel('systemerror')->addSystemError("No recipient email for ".$jsst_subject);
		}
    }

    function jsst_set_html_content_type() {
        return 'text/html';
    }

    private function sendSMTPmail($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, $jsst_actionfor){
        do_action('jsst_aadon_send_smtp_mail',$jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail, $jsst_senderName, $jsst_attachments, $jsst_action, $jsst_actionfor);
    }

    private function getSenderEmailAndName($jsst_id) {
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT email.email,email.name
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON department.id = ticket.departmentid
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ON email.id = department.emailid
                        WHERE ticket.id = " . esc_sql($jsst_id);
            $jsst_email = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
        } else {
            $jsst_email = '';
        }
        if (empty($jsst_email)) {
            $jsst_email = $this->getDefaultSenderEmailAndName();
        }
        return $jsst_email;
    }

    private function getDefaultSenderEmailAndName() {
        $jsst_emailid = jssupportticket::$_config['default_alert_email'];
        if(!is_numeric($jsst_emailid)) return false;
        $jsst_query = "SELECT email,name FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE id = " . esc_sql($jsst_emailid);
        $jsst_email = jssupportticket::$_db->get_row($jsst_query);
        return $jsst_email;
    }

    private function getTemplateForEmail($jsst_templatefor, $jsst_multiformid = '') {
        $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` WHERE templatefor = '" . esc_sql($jsst_templatefor) . "'";

        // If multiformid is provided
        if (!empty($jsst_multiformid)) {
            $jsst_query .= " AND multiformid = " . esc_sql($jsst_multiformid);
            $jsst_template = jssupportticket::$_db->get_row($jsst_query);

            // If no form-specific template is found, fallback to default
            if (empty($jsst_template)) {
                $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` 
                          WHERE templatefor = '" . esc_sql($jsst_templatefor) . "'
                          AND (multiformid IS NULL OR multiformid = '')";
                $jsst_template = jssupportticket::$_db->get_row($jsst_query);
            }
        } else {
            // No multiformid passed — get default template
            $jsst_query .= " AND (multiformid IS NULL OR multiformid = '')";
            $jsst_template = jssupportticket::$_db->get_row($jsst_query);
        }

        // Handle DB error
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }

        return $jsst_template;
    }

    private function getRecordByTablenameAndId($jsst_tablename, $jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        switch($jsst_tablename){
            case 'js_ticket_tickets':
                do_action('jsst_get_mail_table_record_query');// to prepare any addon based query
                $jsst_query = "SELECT ticket.*,department.departmentname,priority.priority ".jssupportticket::$_addon_query['select']
                    . " FROM `" . jssupportticket::$_db->prefix . $jsst_tablename . "` AS ticket "
                    . " LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON department.id = ticket.departmentid "
                    . jssupportticket::$_addon_query['join']
                    . " LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON priority.id = ticket.priorityid "
                    . " WHERE ticket.id = " . esc_sql($jsst_id);
                do_action('jsst_reset_aadon_query');
            break;
            default:
                $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . $jsst_tablename . "` WHERE id = " . esc_sql($jsst_id);
            break;
        }
        $jsst_record = jssupportticket::$_db->get_row($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_record;
    }

    function getEmails() {
        // Filter
        $jsst_email = jssupportticket::$_search['email']['email'];
        $jsst_inquery = '';
        if ($jsst_email != null)
            $jsst_inquery .= " WHERE email.email LIKE '%".esc_sql($jsst_email)."%'";

        jssupportticket::$jsst_data['filter']['email'] = $jsst_email;

        // Pagination
        $jsst_query = "SELECT COUNT(email.id)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        // Data
        $jsst_query = " SELECT email.id, email.email, email.autoresponse, email.created, email.updated,email.status
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY email.email DESC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        jssupportticket::$jsst_data['email'] = $jsst_email;
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getAllEmailsForCombobox() {
        $jsst_query = "SELECT id AS id, email AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE status = 1 AND autoresponse = 1";
        $jsst_emails = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_emails;
    }

    function getEmailForForm($jsst_id) {
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT email.id, email.email, email.autoresponse, email.created, email.updated,email.status,email.smtpemailauth,email.smtphosttype,email.smtphost,email.smtpauthencation,email.name,email.password,email.smtpsecure,email.mailport
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email
                        WHERE email.id = " . esc_sql($jsst_id);
            jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
            if(isset(jssupportticket::$jsst_data[0]->password) && jssupportticket::$jsst_data[0]->password != ''){
                jssupportticket::$jsst_data[0]->password = jssupportticketphplib::JSST_safe_decoding(jssupportticket::$jsst_data[0]->password);
            }
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    function storeEmail($jsst_data) {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        if(!$jsst_data['id'])
        if($this->checkAlreadyExist($jsst_data['email'])){
            JSSTmessage::setMessage(esc_html(__('Email Already Exist', 'js-support-ticket')), 'error');
            return;
        }
        if ($jsst_data['id'])
            $jsst_data['updated'] = date_i18n('Y-m-d H:i:s');
        else{
            $jsst_data['updated'] = date_i18n('Y-m-d H:i:s');
            $jsst_data['created'] = date_i18n('Y-m-d H:i:s');
        }
        if(isset($jsst_data['password']) && $jsst_data['password'] != ''){
            $jsst_data['password'] = jssupportticketphplib::JSST_safe_encoding($jsst_data['password']);
        }

        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions

        $jsst_row = JSSTincluder::getJSTable('email');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 0) {
            JSSTmessage::setMessage(esc_html(__('The email has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The email has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    function checkAlreadyExist($jsst_email){
        $jsst_query = "SELECT COUNT(id) FROM`" . jssupportticket::$_db->prefix . "js_ticket_email`  WHERE email = '".esc_sql($jsst_email)."'";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if($jsst_result > 0)
            return true;
        else
            return false;
    }

    function removeEmail($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        if ($this->canRemoveEmail($jsst_id)) {
            $jsst_row = JSSTincluder::getJSTable('email');
            if ($jsst_row->delete($jsst_id)) {
                JSSTmessage::setMessage(esc_html(__('The email has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('The email has not been deleted', 'js-support-ticket')), 'error');
            }
        } else {
            JSSTmessage::setMessage(esc_html(__('Email','js-support-ticket')).' '. esc_html(__('in use cannot deleted', 'js-support-ticket')), 'error');
        }
        return;
    }

    private function canRemoveEmail($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT (
                        (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE emailid = " . esc_sql($jsst_id) . ")
                        + (SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configname = 'default_alert_email' AND configvalue = " . esc_sql($jsst_id) . ")
                        + (SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configname = 'default_admin_email' AND configvalue = " . esc_sql($jsst_id) . ")
                        ) AS total";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($jsst_result == 0)
            return true;
        else
            return false;
    }

    function getEmailForDepartment() {
        $jsst_query = "SELECT id, email AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_email`";
        $jsst_emails = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_emails;
    }

    function getEmailById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT email  FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE id = " . esc_sql($jsst_id);
        $jsst_email = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_email;
    }

    function checkSMTPEnableOrDisable($jsst_senderemail){
        if(!in_array('smtp', jssupportticket::$_active_addons)){
            return false;
        }
        if(!is_string($jsst_senderemail))
            return false;
        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE email = '".esc_sql($jsst_senderemail). "' AND smtpemailauth = 1"; // 1 For smtp 0 for default
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        if($jsst_total > 0){
            return true;
        }else{
            return false;
        }
    }

    function getSMTPEmailConfig($jsst_senderemail){
        $jsst_query = "SELECT * FROM  `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE email = '".esc_sql($jsst_senderemail)."'";
        $jsst_emailconfig = jssupportticket::$_db->get_row($jsst_query);
        return $jsst_emailconfig;
    }

    function sendTestEmail(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'send-test-email') ) {
            die( 'Security check Failed' );
        }
        $jsst_hosttype = JSSTrequest::getVar('hosttype');
        $jsst_hostname = JSSTrequest::getVar('hostname');
        $jsst_ssl = JSSTrequest::getVar('ssl');
        $jsst_hostportnumber = JSSTrequest::getVar('hostportnumber');
        $jsst_emailaddress = JSSTrequest::getVar('emailaddress');
        $jsst_password = JSSTrequest::getVar('password');
        $jsst_smtpauthencation = JSSTrequest::getVar('smtpauthencation');

        require_once ABSPATH . WPINC . '/class-phpmailer.php';
        require_once ABSPATH . WPINC . '/class-smtp.php';
        $jsst_mail = new PHPMailer(true);
        try {

            $jsst_mail->isSMTP();
            $jsst_mail->Host = $jsst_hostname;
            //$jsst_mail->Host = 'smtp1.example.com;
            $jsst_mail->SMTPAuth = $jsst_smtpauthencation;
            $jsst_mail->Username = $jsst_emailaddress;
            $jsst_mail->Password = $jsst_password;
            if($jsst_ssl == 0){
                $jsst_mail->SMTPSecure = 'ssl';
            }else{
                $jsst_mail->SMTPSecure = 'tls';
            }
            $jsst_mail->Port = $jsst_hostportnumber;
            //Recipients
            $jsst_mail->setFrom($jsst_emailaddress, jssupportticket::$_config['title']);
            $jsst_adminEmailid = jssupportticket::$_config['default_admin_email'];
            $jsst_adminEmail = $this->getEmailById($jsst_adminEmailid);

            $jsst_mail->addAddress($jsst_adminEmail,'Administrator');

            $jsst_mail->isHTML(true);
            $jsst_mail->Subject = 'SMTP Test email From :'.site_url();
            $jsst_mail->Body    = 'This is body text for SMTP test email from :'.site_url();
            $jsst_mail->send();
            $jsst_error['text'] = 'Test email has been sent on : '. $jsst_adminEmail;
            $jsst_error['type'] = 0;
        } catch (Exception $jsst_e) {
            $jsst_error['text'] = 'Message could not be sent. Mailer Error: '. $jsst_mail->ErrorInfo;
            $jsst_error['type'] = 1;
        }
        return wp_json_encode($jsst_error);;

    }

    function getAdminSearchFormDataEmails(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'emails') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['email'] = JSSTrequest::getVar('email');
        $jsst_search_array['search_from_email'] = 1;
        return $jsst_search_array;
    }

    private function getTicketReplyHistory($jsst_id) {
        $jsst_html = '';
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT replies.*,replies.id AS replyid,tickets.id 
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS replies
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS tickets ON  replies.ticketid = tickets.id
                    WHERE tickets.id = " . esc_sql($jsst_id) . " ORDER By replies.id DESC";
            $jsst_replies = jssupportticket::$_db->get_results($jsst_query);
            foreach ($jsst_replies as $jsst_key => $jsst_reply) {
                if ($jsst_key == 0) {
                    $jsst_html .= '<div style="float:left;width:100%;padding:15px 0;border-bottom:1px solid #ebecec;margin-bottom:20px;">
                                <div style="font-weight:bold;font-size:18px;margin-bottom:5px;color:#4b4b4d;">'. esc_html(__('Ticket History','js-support-ticket')).'</div>';
                }
                $jsst_html .= '<div style="float:left;width:100%;padding:10px 15px;border:1px solid #ebecec;background:#f8fafc;box-sizing:border-box;margin:10px 0;">
                            <div style="float:left;width:100%;margin:10px 0;">
                                <span style="float:left;width:auto;display:inline-block;color:#4b4b4d;font-size:14px;font-weight: 600;">'. esc_html(__('Reply By','js-support-ticket')).':&nbsp;</span>
                                <span style="float:left;width:auto;display:inline-block;color:#727376;">'.esc_html($jsst_reply->name).'</span>
                            </div>
                            <div style="float:left;width:100%;margin:10px 0 0;">
                                <span style="float:left;width:auto;display:inline-block;color:#4b4b4d;font-size:14px;font-weight: 600;">'. esc_html(__('Date','js-support-ticket')).':&nbsp;</span>
                                <span style="float:left;width:auto;display:inline-block;color:#727376;">'.esc_html($jsst_reply->created).'</span>
                            </div>
                            <div style="float:left;width:100%;">
                                <span style="float:left;width:auto;display:inline-block;color:#727376;">'.esc_html($jsst_reply->message).'</span>
                            </div>
                        </div>';
            }
            if (isset($jsst_html)) {
                $jsst_html .= '</div>';
            }
            
        }
        return $jsst_html;
    }
}

?>
