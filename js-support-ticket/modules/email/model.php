<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTemailModel {
    /*
      $mailfor
      For which purpose you want to send mail
      1 => Ticket

      $action
      For which action of $mailfor you want to send the mail
      1 => New Ticket Create
      2 => Close Ticket
      3 => Delete Ticket
      4 => Reply Ticket (Admin/Staff Member)
      5 => Reply Ticket (Ticket member)
      6 => Lock Ticket

      $id
      id required when recever emailaddress is stored in record
     */

    function sendMail($mailfor, $action, $id = null, $tablename = null) {
        if (!is_numeric($mailfor))
            return false;
        if (!is_numeric($action))
            return false;
        if ($id != null)
            if (!is_numeric($id))
                return false;
        $pageid = jssupportticket::getPageid();
		$adminEmailid = jssupportticket::$_config['default_admin_email'];
		$adminEmail = $this->getEmailById($adminEmailid);
		
        switch ($mailfor) {
            case 1: // Mail For Tickets
                switch ($action) {
                    case 1: // New Ticket Created
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        if (isset($ticketRecord->name) && isset($ticketRecord->subject) && isset($ticketRecord->ticketid) && isset($ticketRecord->email)) {
                        $Username = $ticketRecord->name;
                        $Subject = $ticketRecord->subject;
                        $TrackingId = $ticketRecord->ticketid;
                        $Email = $ticketRecord->email;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Message = $ticketRecord->message;
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{MESSAGE}' => $Message,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );

                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;

                        // New ticket mail to admin
                        if(jssupportticket::$_config['new_ticket_mail_to_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','ticket-new-admin' , $adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $this->getTemplateForEmail('ticket-new-admin');
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
                            $matcharray['{TICKETURL}'] = $link;
                            $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###admin####" />';
                            $msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###admin#### ></span>';
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'ticket-new-admin');
                        }
                        //Check to send email to department
                        $query = "SELECT dept.sendmail, email.email AS emailaddress
                                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept ON dept.id = ticket.departmentid
                                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_email` AS email ON email.id = dept.emailid
                                    WHERE ticket.id = ".esc_sql($id);
                        $dept_result = jssupportticket::$_db->get_row($query);
                        if($dept_result){
                            if(isset($dept_result->sendmail) && $dept_result->sendmail == 1){
                                $deptemail = $dept_result->emailaddress;
                                $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','ticket-new-admin' , $deptemail ,'');
                                if($template == '' && empty($template)){
                                    $template = $this->getTemplateForEmail('ticket-new-admin');
                                }

                                $msgSubject = $template->subject;
                                $msgBody = $template->body;

                                $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
                                $matcharray['{TICKETURL}'] = $link;
                                $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###admin####" />';
                                $msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###admin#### ></span>';
                                $attachments = '';
                                $this->replaceMatches($msgSubject, $matcharray);
                                $this->replaceMatches($msgBody, $matcharray);
                                $this->sendEmail($deptemail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'ticket-new-admin');
                            }
                        }
                        // New ticket mail to User
                        $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','ticket-new' , $ticketRecord->email , $ticketRecord->uid);
                        if($template == '' && empty($template)){
                            $template = $this->getTemplateForEmail('ticket-new');
                        }
                        //Parsing template
                        $msgSubject = $template->subject;
                        $msgBody = $template->body;
                        //token encrption
                        $tokenarray['emailaddress']=$Email;
                        $tokenarray['trackingid']=$TrackingId;
                        $tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                        $token = wp_json_encode($tokenarray);
                        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                        $encoder = new JSSTEncoder();
                        $encryptedtext = $encoder->encrypt($token);
                        // end token encryotion
                        $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'showticketstatus','action'=>'jstask','token'=>$encryptedtext,'jsstpageid'=>jssupportticket::getPageid())));
                        $matcharray['{TICKETURL}'] = $link;
                        $this->replaceMatches($msgSubject, $matcharray);
                        $this->replaceMatches($msgBody, $matcharray);
                        $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###user####" />';
                        $msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###user#### ></span>';
                        $attachments = '';
                        $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);

                        //New ticket mail to staff member
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['new_ticket_mail_to_staff_members'] == 1) {
                            // Get All Staff member of the department of Current Ticket
                            if ( in_array('agentautoassign',jssupportticket::$_active_addons) && isset(jssupportticket::$_config['department_email_on_ticket_create']) && jssupportticket::$_config['department_email_on_ticket_create'] == 2) {
                                $agentmembers = JSSTincluder::getJSModel('agentautoassign')->getAllStaffMemberByDepId($ticketRecord->departmentid);
                            }
                            else{
                                $agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByDepId($ticketRecord->departmentid);
                            }
                            if(is_array($agentmembers) && !empty($agentmembers)){
                                foreach ($agentmembers AS $agent) {
                                    if($agent->canemail == 1){
                                        $staffuid = $agent->staffuid;
                                        $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','ticket-staff' , $agent->email , $staffuid);
                                        if($template == '' && empty($template)){
                                            $template = $this->getTemplateForEmail('ticket-staff');
                                        }

                                        $msgSubject = $template->subject;
                                        $msgBody = $template->body;
                                        $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));
                                        $matcharray['{TICKETURL}'] = $link;
                                        $this->replaceMatches($msgSubject, $matcharray);
                                        $this->replaceMatches($msgBody, $matcharray);
                                        $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###" />';
                                        $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                        $msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                        $attachments = '';
                                        $this->sendEmail($agent->email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'ticket-staff');
                                    }
                                }
                            }
                        }
                        }
                        break;
                    case 2: // Close Ticket
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $Username = $ticketRecord->name;
                        $Subject = $ticketRecord->subject;
                        $TrackingId = $ticketRecord->ticketid;
                        $Email = $ticketRecord->email;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Message = $ticketRecord->message;
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{MESSAGE}' => $Message,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')

                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('close-tk');
                        // Close ticket mail to admin
                        if (jssupportticket::$_config['ticket_close_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','close-tk' , $adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }

                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
                            $matcharray['{TICKETURL}'] = $link;
                            $matcharray['{FEEDBACKURL}'] = ' ';
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'close-tk-admin');
                        }
                        // Close ticket mail to staff member
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_close_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','close-tk' , $agentEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }

                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));
                            $matcharray['{TICKETURL}'] = $link;
                            $matcharray['{FEEDBACKURL}'] = ' ';
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'close-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_close_user'] == 1) {
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));
                            $tokenarray['emailaddress']=$Email;
                            $tokenarray['trackingid']=$TrackingId;
                            $token = wp_json_encode($tokenarray);
                            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                            $encoder = new JSSTEncoder();
                            $encryptedtext = $encoder->encrypt($token);
                            if(in_array('feedback', jssupportticket::$_active_addons)){
                                $flink = "<a href=" . esc_url(jssupportticket::makeUrl(array('jstmod'=>'feedback', 'task'=>'showfeedbackform','action'=>'jstask','token'=>$encryptedtext,'jsstpageid'=>jssupportticket::getPageid()))) . ">". esc_html(__('Click here to give us feedback','js-support-ticket'))." </a>";
                            }else{
                                $flink = " ";
                            }

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','close-tk' , $Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }

                            $matcharray['{TICKETURL}'] = $link;
                            $matcharray['{FEEDBACKURL}'] = $flink;
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 3: // Delete Ticket
                        $TrackingId = jssupportticket::$_data['ticketid'];
                        $Email = jssupportticket::$_data['ticketemail'];
                        $Subject = jssupportticket::$_data['ticketsubject'];
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{TRACKINGID}' => $TrackingId,
                            '{SUBJECT}' => $Subject,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $object = $this->getSenderEmailAndName(null);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('delete-tk');
                        // Delete ticket mail to admin
                        if (jssupportticket::$_config['ticket_delete_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','delete-tk' , $adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'delete-tk-admin');
                        }
                        // Delete ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_delete_staff'] == 1) {
                            $agent_id = jssupportticket::$_data['staffid'];
                            $agentEmail = $this->getStaffEmailAddressByStaffId($agent_id);
                            if( ! empty($agentEmail)){
                                $staffuid = $this->getStaffUidByStaffId(jssupportticket::$_data['staffid']);
                                $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','delete-tk' , $agentEmail ,$staffuid);
                                if($template == '' && empty($template)){
                                    $template = $defaulttemplate;
                                }
                                $msgSubject = $template->subject;
                                $msgBody = $template->body;
                                $attachments = '';
                                $this->replaceMatches($msgSubject, $matcharray);
                                $this->replaceMatches($msgBody, $matcharray);
                                $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'delete-tk-staff');
                            }
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_delete_user'] == 1) {
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','delete-tk' , $Email , '');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 4: // Reply Ticket (Admin/Staff Member)
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $Username = $ticketRecord->name;
                        $Subject = $ticketRecord->subject;
                        $TrackingId = $ticketRecord->ticketid;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $Message = $this->getLatestReplyByTicketId($id);
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{MESSAGE}' => $Message,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('reply-tk');
                        // Reply ticket mail to admin
                        if (jssupportticket::$_config['ticket_response_to_staff_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
                            $matcharray['{TICKETURL}'] = $link;

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' , $adminEmail , '');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }

                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###admin####" />';
                            $attachments = '';
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'reply-tk-admin');
                        }
                        // Reply ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_response_to_staff_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' , $agentEmail , $staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }

                            $matcharray['{TICKETURL}'] = $link;
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                            $attachments = '';
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'reply-tk-staff');
                        }
                        // New ticket mail to User
                        $template = $this->getTemplateForEmail('responce-tk');
                        if (jssupportticket::$_config['ticket_response_to_staff_user'] == 1) {
                            //token encrption
                            $tokenarray['emailaddress']=$Email;
                            $tokenarray['trackingid']=$TrackingId;
                            $tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                            $token = wp_json_encode($tokenarray);
                            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                            $encoder = new JSSTEncoder();
                            $encryptedtext = $encoder->encrypt($token);
                            // end token encryotion
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'showticketstatus','action'=>'jstask','token'=>$encryptedtext,'jsstpageid'=>jssupportticket::getPageid())));
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' , $Email , $ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $matcharray['{TICKETURL}'] = $link;
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###user####" />';
                            $attachments = '';
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 5: // Reply Ticket (Ticket Member)
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $Username = $ticketRecord->name;
                        $Subject = $ticketRecord->subject;
                        $TrackingId = $ticketRecord->ticketid;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $Message = $this->getLatestReplyByTicketId($id);
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{MESSAGE}' => $Message,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('reply-tk');
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_reply_ticket_user_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
                            $matcharray['{TICKETURL}'] = $link;

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###admin####" />';
                            $attachments = '';
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'reply-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_reply_ticket_user_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));
                            $matcharray['{TICKETURL}'] = $link;
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' ,$adminEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }

                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                            $attachments = '';
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'reply-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_reply_ticket_user_user'] == 1) {
                            //token encrption
                            $tokenarray['emailaddress']=$Email;
                            $tokenarray['trackingid']=$TrackingId;
                            $tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                            $token = wp_json_encode($tokenarray);
                            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                            $encoder = new JSSTEncoder();
                            $encryptedtext = $encoder->encrypt($token);
                            // end token encryotion
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket' ,'task'=>'showticketstatus','action'=>'jstask','token'=>$encryptedtext,'jsstpageid'=>jssupportticket::getPageid())));
                            $matcharray['{TICKETURL}'] = $link;
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reply-tk' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }

                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###user####" />';
                            $attachments = '';
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 6: // Lock Ticket
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $Username = $ticketRecord->name;
                        $Subject = $ticketRecord->subject;
                        $TrackingId = $ticketRecord->ticketid;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('lock-tk');
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_lock_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
                            $matcharray['{TICKETURL}'] = $link;
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','lock-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'lock-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_lock_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));

                            $matcharray['{TICKETURL}'] = $link;
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','lock-tk' ,$agentEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'lock-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_lock_user'] == 1) {
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));

                            $matcharray['{TICKETURL}'] = $link;
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','lock-tk' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 7: // Unlock Ticket
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $Username = $ticketRecord->name;
                        $Subject = $ticketRecord->subject;
                        $TrackingId = $ticketRecord->ticketid;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => $Username,
                            '{SUBJECT}' => $Subject,
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{EMAIL}' => $Email,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('unlock-tk');
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_unlock_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));

                            $matcharray['{TICKETURL}'] = $link;
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unlock-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                            $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'unlock-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_unlock_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));

                            $matcharray['{TICKETURL}'] = $link;
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unlock-tk' ,$agentEmail ,$staffuid);
                            if($template == '' && empty($template)){
                            $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'unlock-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_unlock_user'] == 1) {
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));

                            $matcharray['{TICKETURL}'] = $link;
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unlock-tk' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 8: // Markoverdue Ticket
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $TrackingId = $ticketRecord->ticketid;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $Subject = $ticketRecord->subject;
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{SUBJECT}' => $Subject,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('moverdue-tk');
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_mark_overdue_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));

                            $matcharray['{TICKETURL}'] = $link;
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'moverdue-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_mark_overdue_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));

                            $matcharray['{TICKETURL}'] = $link;
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$adminEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'moverdue-tk-staff');
                            // Get All Staff member of the department of Current Ticket
                            $agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByDepId($ticketRecord->departmentid);
                            if(is_array($agentmembers) && !empty($agentmembers)){
                                foreach ($agentmembers AS $agent) {
                                    if($agent->canemail == 1){
                                        $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$agent->email ,$agent->staffuid);
                                        if($template == '' && empty($template)){
                                            $template = $defaulttemplate;
                                        }
                                        $msgSubject = $template->subject;
                                        $msgBody = $template->body;
                                        $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                        $msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                        $attachments = '';
                                        $this->sendEmail($agent->email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                                    }
                                }
                            }
                            // send email to staff memebers with all ticket permissions
                            if( !is_numeric($ticketRecord->staffid) && !is_numeric($ticketRecord->departmentid)){
                                if( in_array('agent',jssupportticket::$_active_addons)){
                                    $agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByAllTicketPermission();
                                    if(is_array($agentmembers) && !empty($agentmembers)){
                                        foreach ($agentmembers AS $agent) {
                                            if($agent->canemail == 1){
                                                $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$agent->email,$agent->uid);
                                                if($template == '' && empty($template)){
                                                    $template = $defaulttemplate;
                                                }
                                                $msgSubject = $template->subject;
                                                $msgBody = $template->body;
                                                $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                                $msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                                $attachments = '';

                                                $this->sendEmail($agent->email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_mark_overdue_user'] == 1) {
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));
                            $matcharray['{TICKETURL}'] = $link;
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','moverdue-tk' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 9: // Mark in progress Ticket
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $TrackingId = $ticketRecord->ticketid;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $Subject = $ticketRecord->subject;
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{TRACKINGID}' => $TrackingId,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{SUBJECT}' => $Subject,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('minprogress-tk');
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_mark_progress_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));

                            $matcharray['{TICKETURL}'] = $link;

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','minprogress-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'minprogress-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_mark_progress_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));

                            $matcharray['{TICKETURL}'] = $link;
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','minprogress-tk'
                             ,$agentEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'minprogress-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_mark_progress_user'] == 1) {
                            $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));

                            $matcharray['{TICKETURL}'] = $link;
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','minprogress-tk' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 10: // Ban email and close Ticket
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $TrackingId = $ticketRecord->ticketid;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $Subject = $ticketRecord->subject;
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{EMAIL_ADDRESS}' => $Email,
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('banemailcloseticket-tk');

                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticker_ban_eamil_and_close_ticktet_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemailcloseticket-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'banemailcloseticket-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticker_ban_eamil_and_close_ticktet_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemailcloseticket-tk' ,$adminEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'banemailcloseticket-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticker_ban_eamil_and_close_ticktet_user'] == 1) {
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemailcloseticket-tk' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 11: // Priority change ticket
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $TrackingId = $ticketRecord->ticketid;
                        $Subject = $ticketRecord->subject;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $Priority = JSSTincluder::getJSModel('priority')->getPriorityById($ticketRecord->priorityid);
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{PRIORITY_TITLE}' => $Priority,
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('prtrans-tk');

                        // New ticket mail to admin
						$template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','prtrans-tk' ,$adminEmail ,'');
						if($template == '' && empty($template)){
							$template = $defaulttemplate;
						}
                        if (jssupportticket::$_config['ticket_priority_admin'] == 1) {
                            $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
                            $matcharray['{TICKETURL}'] = $link;
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'prtrans-tk-admin');
                        }
                        $msgSubject = $template->subject;
                        $msgBody = $template->body;
                        $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));
                        $matcharray['{TICKETURL}'] = $link;
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_priority_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','prtrans-tk' ,$agentEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'prtrans-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_priority_user'] == 1) {
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','prtrans-tk' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 12: // DEPARTMENT TRANSFER
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $TrackingId = $ticketRecord->ticketid;
                        $Subject = $ticketRecord->subject;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $Department = JSSTincluder::getJSModel('department')->getDepartmentById($ticketRecord->departmentid);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT_TITLE}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('deptrans-tk');
                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_department_transfer_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'deptrans-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_department_transfer_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$agentEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'deptrans-tk-staff');

                            // send email to all staff memebers of current ticket department
                            // Get All Staff member of the department of Current Ticket
                            $agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByDepId($ticketRecord->departmentid);
                            if(is_array($agentmembers) && !empty($agentmembers)){
                                foreach ($agentmembers AS $agent) {
                                    if($agent->canemail == 1){
                                        $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$agent->email ,$agent->staffuid);
                                        if($template == '' && empty($template)){
                                            $template = $defaulttemplate;
                                        }
                                        $msgSubject = $template->subject;
                                        $msgBody = $template->body;
                                        $this->replaceMatches($msgSubject, $matcharray);
                                        $this->replaceMatches($msgBody, $matcharray);
                                        $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                        $msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                        $attachments = '';
                                        $this->sendEmail($agent->email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                                    }
                                }
                            }
                            // send email to staff memebers with all ticket permissions
                            if( !is_numeric($ticketRecord->staffid) && !is_numeric($ticketRecord->departmentid)){
                                if( in_array('agent',jssupportticket::$_active_addons) ){
                                    $agentmembers = JSSTincluder::getJSModel('agent')->getAllStaffMemberByAllTicketPermission();
                                    if(is_array($agentmembers) && !empty($agentmembers)){
                                        foreach ($agentmembers AS $agent) {
                                            if($agent->canemail == 1){
                                                $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$agent->email ,$agent->uid);
                                                if($template == '' && empty($template)){
                                                    $template = $defaulttemplate;
                                                }
                                                $msgSubject = $template->subject;
                                                $msgBody = $template->body;
                                                $this->replaceMatches($msgSubject, $matcharray);
                                                $this->replaceMatches($msgBody, $matcharray);
                                                $msgBody .= '<input type="hidden" name="ticketid:' . esc_attr($TrackingId) . '###staff####" />';
                                                $msgBody .= '<span style="display:none;" ticketid:' . esc_attr($TrackingId) . '###staff#### ></span>';
                                                $attachments = '';
                                                $this->sendEmail($agent->email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_department_transfer_user'] == 1) {
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','deptrans-tk' ,$Email,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 13: // REASSIGN TICKET TO STAFF
                        if(! in_array('agent',jssupportticket::$_active_addons) ){
                            return;
                        }
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $TrackingId = $ticketRecord->ticketid;
                        $DepName = $ticketRecord->departmentname;
                        if(in_array('helptopic', jssupportticket::$_active_addons)){
                            $HelptopicName = $ticketRecord->topic;
                        }else{
                            $HelptopicName = '';
                        }
                        $Email = $ticketRecord->email;
                        $Subject = $ticketRecord->subject;
                        $Staff = JSSTincluder::getJSModel('agent')->getMyName($ticketRecord->staffid);
                        $ticketHistory = $this->getTicketReplyHistory($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $Staff,
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('reassign-tk');
                        // New ticket mail to admin
                        $link = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($id));
                        $matcharray['{TICKETURL}'] = $link;
			$adminEmailid = jssupportticket::$_config['default_admin_email'];
			$adminEmail = $this->getEmailById($adminEmailid);
                        if (jssupportticket::$_config['ticket_reassign_admin'] == 1) {

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reassign-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'reassign-tk-admin');
                        }

                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $Staff,
                            '{SUBJECT}' => $Subject,
                            '{HELP_TOPIC}' => $HelptopicName,
                            '{TRACKINGID}' => $TrackingId,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{TICKET_HISTORY}' => $ticketHistory,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $link = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail','jssupportticketid'=>$id,'jsstpageid'=>jssupportticket::getPageid())));
                        $matcharray['{TICKETURL}'] = $link;
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_reassign_staff'] == 1) {
                            $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                            $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reassign-tk' ,$adminEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }

                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'reassign-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_reassign_user'] == 1) {
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','reassign-tk' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 14: // Reply to closed ticket for Email Piping
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $Subject = $ticketRecord->subject;
                        $Email = $ticketRecord->email;
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{SUBJECT}' => $Subject,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('mail-rpy-closed');
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_reply_closed_ticket_user'] == 1) {
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','mail-rpy-closed' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 15: // Send feedback email to user
                        if(!in_array('feedback', jssupportticket::$_active_addons)){
                            break;
                        }
                        $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $Subject = $ticketRecord->subject;
                        $Email = $ticketRecord->email;
                        $TrackingId = $ticketRecord->ticketid;
                        $close_date = date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticketRecord->closed));
                        $username = $ticketRecord->name;
                        $tokenarray['emailaddress']=$Email;
                        $tokenarray['trackingid']=$TrackingId;
                        $tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                        $token = wp_json_encode($tokenarray);
                        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                        $encoder = new JSSTEncoder();
                        $encryptedtext = $encoder->encrypt($token);
                        $link = "<a href=" . esc_url(jssupportticket::makeUrl(array('jstmod'=>'feedback', 'task'=>'showfeedbackform','action'=>'jstask','token'=>$encryptedtext,'jsstpageid'=>jssupportticket::getPageid()))) . ">";
                        $linkclosing = "</a>";
                        $tracking_url = "<a href=" . esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'showticketstatus','action'=>'jstask','token'=>$encryptedtext,'jsstpageid'=>jssupportticket::getPageid()))) . ">" . $TrackingId . "</a>";
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USER_NAME}' => $username,
                            '{TICKET_SUBJECT}' => $Subject,
                            '{TRACKING_ID}' => $tracking_url,
                            '{CLOSE_DATE}' => $close_date,
                            '{LINK}' => $link,
                            '{/LINK}' => $linkclosing,
                            '{DEPARTMENT}' => $ticketRecord->departmentname,
                            '{PRIORITY}' => $ticketRecord->priority,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        // code for handling custom fields start
                        $fvalue = '';
                        if(!empty($ticketRecord->params)){
                            $data = json_decode($ticketRecord->params,true);
                        }
                        $fields = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1);
                        if( isset($data) && is_array($data)){
                            foreach ($fields as $field) {
                                if($field->userfieldtype != 'file'){
                                    $fvalue = '';
                                    if(array_key_exists($field->field, $data)){
                                        $fvalue = $data[$field->field];
                                    }
                                    $matcharray['{'.$field->field.'}'] = $fvalue;// match array new index for custom field
                                }
                            }
                        }
                        // code for handling custom fields end
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('mail-feedback');
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_feedback_user'] == 1) {
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','mail-feedback' ,$Email ,$ticketRecord->uid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                }
                break;
            case 2: // Ban Email
                switch ($action) {
                    case 1: // Ban Email
                        if ($tablename != null)
                            $banemailRecord = $this->getRecordByTablenameAndId($tablename, $id);
                        else
                            $banemailRecord = $this->getRecordByTablenameAndId('js_ticket_email_banlist', $id);
                        $Email = $banemailRecord->email;
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{EMAIL_ADDRESS}' => $Email,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $object = $this->getDefaultSenderEmailAndName();
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('banemail-tk');

                        // New ticket mail to admin
                        if (jssupportticket::$_config['ticket_ban_email_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemail-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'banemail-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['ticket_ban_email_staff'] == 1) {
                            if ($tablename != null){
                                $agentEmail = $this->getStaffEmailAddressByStaffId($banemailRecord->staffid);
                                $staffuid = $this->getStaffUidByStaffId($banemailRecord->staffid);
                            }else{
                                $agentEmail = $this->getStaffEmailAddressByStaffId($banemailRecord->submitter);
                                $staffuid = $this->getStaffUidByStaffId($banemailRecord->submitter);
                            }

                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemail-tk' ,$agentEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'banemail-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['ticket_ban_email_user'] == 1) {
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','banemail-tk' ,$Email ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';

                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                    case 2: // Unban Email
                        if ($tablename != null)
                            $ticketRecord = $this->getRecordByTablenameAndId($tablename, $id);
                        else
                            $ticketRecord = $this->getRecordByTablenameAndId('js_ticket_tickets', $id);
                        $Email = $ticketRecord->email;
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{EMAIL_ADDRESS}' => $Email,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $object = $this->getSenderEmailAndName($id);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('unbanemail-tk');

                        // New ticket mail to admin
                        if (jssupportticket::$_config['unban_email_admin'] == 1) {
                            $adminEmailid = jssupportticket::$_config['default_admin_email'];
                            $adminEmail = $this->getEmailById($adminEmailid);
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unbanemail-tk' ,$adminEmail ,'');
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'unbanemail-tk-admin');
                        }
                        // New ticket mail to staff
                        if ( in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['unban_email_staff'] == 1) {
                            if ($tablename != null){
                                $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->staffid);
                                $staffuid = $this->getStaffUidByStaffId($ticketRecord->staffid);
                            }else{
                                $agentEmail = $this->getStaffEmailAddressByStaffId($ticketRecord->submitter);
                                $staffuid = $this->getStaffUidByStaffId($ticketRecord->submitter);
                            }
                            $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unbanemail-tk' ,$agentEmail ,$staffuid);
                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($agentEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'unbanemail-tk-staff');
                        }
                        // New ticket mail to User
                        if (jssupportticket::$_config['unban_email_user'] == 1) {
                            if ($tablename != null){
                                $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unbanemail-tk' , $Email, '');
                            }else{
                                $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','unbanemail-tk' ,$ticketRecord->email , $ticketRecord->uid);
                            }

                            if($template == '' && empty($template)){
                                $template = $defaulttemplate;
                            }
                            $msgSubject = $template->subject;
                            $msgBody = $template->body;
                            $attachments = '';
                            $this->replaceMatches($msgSubject, $matcharray);
                            $this->replaceMatches($msgBody, $matcharray);
                            $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        }
                        break;
                }
                break;
            case 3: // Sending email alerts on mail system
                if(!in_array('mail', jssupportticket::$_active_addons)){ // if mail addon is not installed
                    break;
                }
                switch ($action) {
                    case 1: // Store message
                        $mailRecord = $this->getMailRecordById($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $mailRecord->sendername,
                            '{SUBJECT}' => $mailRecord->subject,
                            '{MESSAGE}' => $mailRecord->message,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $object = $this->getSenderEmailAndName(null);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('mail-new');
                        $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','mail-new' ,'' ,$mailRecord->staffuid);
                        if($template == '' && empty($template)){
                            $template = $defaulttemplate;
                        }
                        $msgSubject = $template->subject;
                        $msgBody = $template->body;

                        $Email = $mailRecord->receveremail;
                        $attachments = '';
                        $this->replaceMatches($msgSubject, $matcharray);
                        $this->replaceMatches($msgBody, $matcharray);
                        $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'mail-new');
                        break;
                    case 2: // Store reply
                        $mailRecord = $this->getMailRecordById($id, 1);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $mailRecord->sendername,
                            '{SUBJECT}' => $mailRecord->subject,
                            '{MESSAGE}' => $mailRecord->message,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $object = $this->getSenderEmailAndName(null);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('mail-rpy');
                        $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','mail-rpy' ,'' ,$mailRecord->staffuid);
                        if($template == '' && empty($template)){
                            $template = $defaulttemplate;
                        }
                        $msgSubject = $template->subject;
                        $msgBody = $template->body;
                        $Email = $mailRecord->receveremail;
                        $attachments = '';
                        $this->replaceMatches($msgSubject, $matcharray);
                        $this->replaceMatches($msgBody, $matcharray);
                        $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'mail-rpy');
                        break;
                }
                break;
            case 4: // gdpr data erase or delte.
                switch ($action) {
                    case 1: // erase data email
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{USERNAME}' => jssupportticket::$_data['mail_data']['name'],
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $object = $this->getSenderEmailAndName(null);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('delete-user-data');
                        $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','delete-user-data' ,jssupportticket::$_data['mail_data']['email'] , '');
                        if($template == '' && empty($template)){
                            $template = $defaulttemplate;
                        }

                        $msgSubject = $template->subject;
                        $msgBody = $template->body;
                        $Email = jssupportticket::$_data['mail_data']['email'];
                        $attachments = '';
                        $this->replaceMatches($msgSubject, $matcharray);
                        $this->replaceMatches($msgBody, $matcharray);
                        $this->sendEmail($Email, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action);
                        break;
                }
                break;
            case 5: // agent emails
                switch ($action) {
                    case 1: // new agent
                        $staffname = JSSTincluder::getJSModel('agent')->getMyName($id);
                        $matcharray = array(
                            '{SITETITLE}' => jssupportticket::$_config['title'],
                            '{AGENT_NAME}' => $staffname,
                            '{CURRENT_YEAR}' => gmdate('Y')
                        );
                        $object = $this->getSenderEmailAndName(null);
                        $senderEmail = $object->email;
                        $senderName = $object->name;
                        $defaulttemplate = $this->getTemplateForEmail('staff-new');

                        $adminEmailid = jssupportticket::$_config['default_admin_email'];
                        $adminEmail = $this->getEmailById($adminEmailid);
                        $template = apply_filters( 'jsst_get_email_template_by_user_defined_language','','staff-new' , $adminEmail , '');
                        if($template == '' && empty($template)){
                            $template = $defaulttemplate;
                        }
                        $msgSubject = $template->subject;
                        $msgBody = $template->body;
                        $attachments = '';
                        $this->replaceMatches($msgSubject, $matcharray);
                        $this->replaceMatches($msgBody, $matcharray);
                        $this->sendEmail($adminEmail, $msgSubject, $msgBody, $senderEmail, $senderName, $attachments, $action, 'staff-new');
                        break;
                }
                break;
        }
    }


    function getMailRecordById($id, $replyto = null) { // this function will not be called if the mail addon is not installed
        if (!is_numeric($id))
            return false;
        if ($replyto == null) {
            $query = "SELECT mail.subject,mail.message,CONCAT(staff.firstname,' ',staff.lastname) AS sendername, staff.uid as staffuid
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_mail` AS mail
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.id = mail.fromid
                        WHERE mail.id = " . esc_sql($id);
        } else {
            $query = "SELECT mail.subject,reply.message,CONCAT(staff.firstname,' ',staff.lastname) AS sendername, staff.uid as staffuid
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_mail` AS reply
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff_mail` AS mail ON mail.id = reply.replytoid
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.id = reply.fromid
                        WHERE reply.id = " . esc_sql($id);
        }
        $result = jssupportticket::$_db->get_row($query);
            $query = "SELECT staff.email
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_mail` AS mail
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.id = mail.toid
                        WHERE mail.id = " . esc_sql($id);
        $email = jssupportticket::$_db->get_var($query);
        $result->receveremail = $email;
        return $result;
    }

    private function getStaffEmailAddressByStaffId($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT staff.email
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff
                    WHERE staff.id = " . esc_sql($id);
        $emailaddress = jssupportticket::$_db->get_var($query);
        return $emailaddress;
    }

    private function getStaffUidByStaffId($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT staff.uid
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff
                    WHERE staff.id = " . esc_sql($id);
        $emailaddress = jssupportticket::$_db->get_var($query);
        return $emailaddress;
    }

    private function getLatestReplyByTicketId($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT reply.message FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS reply WHERE reply.ticketid = " . esc_sql($id) . " ORDER BY reply.created DESC LIMIT 1";
        $message = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $message;
    }

    private function replaceMatches(&$string, $matcharray) {
        foreach ($matcharray AS $find => $replace) {
            $string = jssupportticketphplib::JSST_str_replace($find, $replace, $string);
        }
    }

    function sendEmail($recevierEmail, $subject, $body, $senderEmail, $senderName, $attachments, $action, $actionfor='') {

        if( (is_array($recevierEmail) && empty($recevierEmail)) || (!is_array($recevierEmail) && jssupportticketphplib::JSST_trim($recevierEmail) == '') ){ // avoid the case of trying to send email to empty email.
            return;
        }

        $enablesmtp = $this->checkSMTPEnableOrDisable($senderEmail);
        if ($enablesmtp) {
            $this->sendSMTPmail($recevierEmail, $subject, $body, $senderEmail, $senderName, $attachments, $action, $actionfor);
        }else{
            $this->sendEmailDefault($recevierEmail, $subject, $body, $senderEmail, $senderName, $attachments, $action, $actionfor);
        }

    }

    private function sendEmailDefault($recevierEmail, $subject, $body, $senderEmail, $senderName, $attachments, $action, $actionfor) {
	$senderName = jssupportticket::$_config['title']; // site name
        /*
          $attachments = array( WP_CONTENT_DIR . '/uploads/file_to_attach.zip' );
          $headers = 'From: My Name <myname@example.com>' . "\r\n";
          wp_mail('test@example.org', 'subject', 'message', $headers, $attachments );

          $action
          For which action of $mailfor you want to send the mail
          1 => New Ticket Create
          2 => Close Ticket
          3 => Delete Ticket
          4 => Reply Ticket (Admin/Staff Member)
          5 => Reply Ticket (Ticket member)
         */
        switch ($action) {
            case 1:
                do_action('jsst-beforeemailticketcreate', $recevierEmail, $subject, $body, $senderEmail);
                break;
            case 2:
                do_action('jsst-beforeemailticketreply', $recevierEmail, $subject, $body, $senderEmail);
                break;
            case 3:
                do_action('jsst-beforeemailticketclose', $recevierEmail, $subject, $body, $senderEmail);
                break;
            case 4:
                do_action('jsst-beforeemailticketdelete', $recevierEmail, $subject, $body, $senderEmail);
                break;
        }
        if (!$senderName)
            $senderName = jssupportticket::$_config['title'];
        $headers[] = 'From: ' . $senderName . ' <' . $senderEmail . '>' . "\r\n";
        $headers = apply_filters('jsst_emailcc_send_email_to_cc' , $headers , $actionfor); // eg $actionfor = ticket-new
        add_filter('wp_mail_content_type', array($this,'jsst_set_html_content_type'));
        // $body = jssupportticketphplib::JSST_preg_replace('/\r?\n|\r/', '<br/>', $body);
        // $body = jssupportticketphplib::JSST_str_replace(array("\r\n", "\r", "\n"), "<br/>", $body);
        // $body = nl2br($body);
		if($recevierEmail){
			if(!wp_mail($recevierEmail, $subject, $body, $headers, $attachments)){
				if($GLOBALS['phpmailer']->ErrorInfo)
					JSSTincluder::getJSModel('systemerror')->addSystemError($GLOBALS['phpmailer']->ErrorInfo);
			}
		}else{
			JSSTincluder::getJSModel('systemerror')->addSystemError("No recipient email for ".$subject);
		}
    }

    function jsst_set_html_content_type() {
        return 'text/html';
    }

    private function sendSMTPmail($recevierEmail, $subject, $body, $senderEmail, $senderName, $attachments, $action, $actionfor){
        do_action('jsst_aadon_send_smtp_mail',$recevierEmail, $subject, $body, $senderEmail, $senderName, $attachments, $action, $actionfor);
    }

    private function getSenderEmailAndName($id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT email.email,email.name
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON department.id = ticket.departmentid
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ON email.id = department.emailid
                        WHERE ticket.id = " . esc_sql($id);
            $email = jssupportticket::$_db->get_row($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
        } else {
            $email = '';
        }
        if (empty($email)) {
            $email = $this->getDefaultSenderEmailAndName();
        }
        return $email;
    }

    private function getDefaultSenderEmailAndName() {
        $emailid = jssupportticket::$_config['default_alert_email'];
        $query = "SELECT email,name FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE id = " . esc_sql($emailid);
        $email = jssupportticket::$_db->get_row($query);
        return $email;
    }

    private function getTemplateForEmail($templatefor) {
        $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_emailtemplates` WHERE templatefor = '" . esc_sql($templatefor) . "'";
        $template = jssupportticket::$_db->get_row($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $template;
    }

    private function getRecordByTablenameAndId($tablename, $id) {
        if (!is_numeric($id))
            return false;
        switch($tablename){
            case 'js_ticket_tickets':
                do_action('get_mail_table_record_query');// to prepare any addon based query
                $query = "SELECT ticket.*,department.departmentname,priority.priority ".jssupportticket::$_addon_query['select']
                    . " FROM `" . jssupportticket::$_db->prefix . $tablename . "` AS ticket "
                    . " LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON department.id = ticket.departmentid "
                    . jssupportticket::$_addon_query['join']
                    . " LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON priority.id = ticket.priorityid "
                    . " WHERE ticket.id = " . esc_sql($id);
                do_action('reset_jsst_aadon_query');
            break;
            default:
                $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . $tablename . "` WHERE id = " . esc_sql($id);
            break;
        }
        $record = jssupportticket::$_db->get_row($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $record;
    }

    function getEmails() {
        // Filter
        $email = jssupportticket::$_search['email']['email'];
        $inquery = '';
        if ($email != null)
            $inquery .= " WHERE email.email LIKE '%".esc_sql($email)."%'";

        jssupportticket::$_data['filter']['email'] = $email;

        // Pagination
        $query = "SELECT COUNT(email.id)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ";
        $query .= $inquery;
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        // Data
        $query = " SELECT email.id, email.email, email.autoresponse, email.created, email.updated,email.status
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email ";
        $query .= $inquery;
        $query .= " ORDER BY email.email DESC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['email'] = $email;
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getAllEmailsForCombobox() {
        $query = "SELECT id AS id, email AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE status = 1 AND autoresponse = 1";
        $emails = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $emails;
    }

    function getEmailForForm($id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT email.id, email.email, email.autoresponse, email.created, email.updated,email.status,email.smtpemailauth,email.smtphosttype,email.smtphost,email.smtpauthencation,email.name,email.password,email.smtpsecure,email.mailport
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` AS email
                        WHERE email.id = " . esc_sql($id);
            jssupportticket::$_data[0] = jssupportticket::$_db->get_row($query);
            if(isset(jssupportticket::$_data[0]->password) && jssupportticket::$_data[0]->password != ''){
                jssupportticket::$_data[0]->password = jssupportticketphplib::JSST_safe_decoding(jssupportticket::$_data[0]->password);
            }
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    function storeEmail($data) {
        if(!$data['id'])
        if($this->checkAlreadyExist($data['email'])){
            JSSTmessage::setMessage(esc_html(__('Email Already Exist', 'js-support-ticket')), 'error');
            return;
        }
        if ($data['id'])
            $data['updated'] = date_i18n('Y-m-d H:i:s');
        else{
            $data['updated'] = date_i18n('Y-m-d H:i:s');
            $data['created'] = date_i18n('Y-m-d H:i:s');
        }
        if(isset($data['password']) && $data['password'] != ''){
            $data['password'] = jssupportticketphplib::JSST_safe_encoding($data['password']);
        }

        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions

        $row = JSSTincluder::getJSTable('email');

        $data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
        $error = 0;
        if (!$row->bind($data)) {
            $error = 1;
        }
        if (!$row->store()) {
            $error = 1;
        }

        if ($error == 0) {
            JSSTmessage::setMessage(esc_html(__('The email has been stored', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The email has not been stored', 'js-support-ticket')), 'error');
        }
        return;
    }

    function checkAlreadyExist($email){
        $query = "SELECT COUNT(id) FROM`" . jssupportticket::$_db->prefix . "js_ticket_email`  WHERE email = '".esc_sql($email)."'";
        $result = jssupportticket::$_db->get_var($query);
        if($result > 0)
            return true;
        else
            return false;
    }

    function removeEmail($id) {
        if (!is_numeric($id))
            return false;
        if ($this->canRemoveEmail($id)) {
            $row = JSSTincluder::getJSTable('email');
            if ($row->delete($id)) {
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

    private function canRemoveEmail($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT (
                        (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE emailid = " . esc_sql($id) . ")
                        + (SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configname = 'default_alert_email' AND configvalue = " . esc_sql($id) . ")
                        + (SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configname = 'default_admin_email' AND configvalue = " . esc_sql($id) . ")
                        ) AS total";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($result == 0)
            return true;
        else
            return false;
    }

    function getEmailForDepartment() {
        $query = "SELECT id, email AS text FROM `" . jssupportticket::$_db->prefix . "js_ticket_email`";
        $emails = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $emails;
    }

    function getEmailById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT email  FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE id = " . esc_sql($id);
        $email = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $email;
    }

    function checkSMTPEnableOrDisable($senderemail){
        if(!in_array('smtp', jssupportticket::$_active_addons)){
            return false;
        }
        if(!is_string($senderemail))
            return false;
        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE email = '".esc_sql($senderemail). "' AND smtpemailauth = 1"; // 1 For smtp 0 for default
        $total = jssupportticket::$_db->get_var($query);
        if($total > 0){
            return true;
        }else{
            return false;
        }
    }

    function getSMTPEmailConfig($senderemail){
        $query = "SELECT * FROM  `" . jssupportticket::$_db->prefix . "js_ticket_email` WHERE email = '".esc_sql($senderemail)."'";
        $emailconfig = jssupportticket::$_db->get_row($query);
        return $emailconfig;
    }

    function sendTestEmail(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'send-test-email') ) {
            die( 'Security check Failed' );
        }
        $hosttype = JSSTrequest::getVar('hosttype');
        $hostname = JSSTrequest::getVar('hostname');
        $ssl = JSSTrequest::getVar('ssl');
        $hostportnumber = JSSTrequest::getVar('hostportnumber');
        $emailaddress = JSSTrequest::getVar('emailaddress');
        $password = JSSTrequest::getVar('password');
        $smtpauthencation = JSSTrequest::getVar('smtpauthencation');

        require_once ABSPATH . WPINC . '/class-phpmailer.php';
        require_once ABSPATH . WPINC . '/class-smtp.php';
        $mail = new PHPMailer(true);
        try {

            $mail->isSMTP();
            $mail->Host = $hostname;
            //$mail->Host = 'smtp1.example.com;
            $mail->SMTPAuth = $smtpauthencation;
            $mail->Username = $emailaddress;
            $mail->Password = $password;
            if($ssl == 0){
                $mail->SMTPSecure = 'ssl';
            }else{
                $mail->SMTPSecure = 'tls';
            }
            $mail->Port = $hostportnumber;
            //Recipients
            $mail->setFrom($emailaddress, jssupportticket::$_config['title']);
            $adminEmailid = jssupportticket::$_config['default_admin_email'];
            $adminEmail = $this->getEmailById($adminEmailid);

            $mail->addAddress($adminEmail,'Administrator');

            $mail->isHTML(true);
            $mail->Subject = 'SMTP Test email From :'.site_url();
            $mail->Body    = 'This is body text for SMTP test email from :'.site_url();
            $mail->send();
            $error['text'] = 'Test email has been sent on : '. $adminEmail;
            $error['type'] = 0;
        } catch (Exception $e) {
            $error['text'] = 'Message could not be sent. Mailer Error: '. $mail->ErrorInfo;
            $error['type'] = 1;
        }
        return wp_json_encode($error);;

    }

    function getAdminSearchFormDataEmails(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'emails') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['email'] = JSSTrequest::getVar('email');
        $jsst_search_array['search_from_email'] = 1;
        return $jsst_search_array;
    }

    private function getTicketReplyHistory($id) {
        $html = '';
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT replies.*,replies.id AS replyid,tickets.id 
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS replies
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS tickets ON  replies.ticketid = tickets.id
                    WHERE tickets.id = " . esc_sql($id) . " ORDER By replies.id DESC";
            $replies = jssupportticket::$_db->get_results($query);
            foreach ($replies as $key => $reply) {
                if ($key == 0) {
                    $html .= '<div style="float:left;width:100%;padding:15px 0;border-bottom:1px solid #ebecec;margin-bottom:20px;">
                                <div style="font-weight:bold;font-size:18px;margin-bottom:5px;color:#4b4b4d;">'. esc_html(__('Ticket History','js-support-ticket')).'</div>';
                }
                $html .= '<div style="float:left;width:100%;padding:10px 15px;border:1px solid #ebecec;background:#f8fafc;box-sizing:border-box;margin:10px 0;">
                            <div style="float:left;width:100%;margin:10px 0;">
                                <span style="float:left;width:auto;display:inline-block;color:#4b4b4d;font-size:14px;font-weight: 600;">'. esc_html(__('Reply By','js-support-ticket')).':&nbsp;</span>
                                <span style="float:left;width:auto;display:inline-block;color:#727376;">'.esc_html($reply->name).'</span>
                            </div>
                            <div style="float:left;width:100%;margin:10px 0 0;">
                                <span style="float:left;width:auto;display:inline-block;color:#4b4b4d;font-size:14px;font-weight: 600;">'. esc_html(__('Date','js-support-ticket')).':&nbsp;</span>
                                <span style="float:left;width:auto;display:inline-block;color:#727376;">'.esc_html($reply->created).'</span>
                            </div>
                            <div style="float:left;width:100%;">
                                <span style="float:left;width:auto;display:inline-block;color:#727376;">'.esc_html($reply->message).'</span>
                            </div>
                        </div>';
            }
            if (isset($html)) {
                $html .= '</div>';
            }
            
        }
        return $html;
    }
}

?>
