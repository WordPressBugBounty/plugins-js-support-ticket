<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTthirdpartyimportModel {

    // supportcandy import data

    private $jsst_support_candy_users_array = array();
    
    private $jsst_support_candy_ticket_custom_fields = array();
    private $jsst_sc_ticket_custom_fields = array();
    private $jsst_as_ticket_custom_fields = array();
    private $jsst_fc_ticket_cf = array();


    private $jsst_support_candy_user_ids = array();
    private $jsst_support_candy_agent_ids = array();
    private $jsst_support_candy_department_ids = array();
    private $jsst_support_candy_agent_role_ids = array();
    private $jsst_support_candy_ticket_ids = array();
    private $jsst_support_candy_status_ids = array();
    private $jsst_support_candy_priority_ids = array();
    private $jsst_support_candy_premade_ids = array();


    private $jsst_awesome_support_user_ids = array();
    private $jsst_awesome_support_agent_ids = array();
    private $jsst_awesome_support_department_ids = array();
    private $jsst_awesome_support_ticket_ids = array();
    private $jsst_awesome_support_status_ids = array();
    private $jsst_awesome_support_priority_ids = array();
    private $jsst_awesome_support_premade_ids = array();


    private $jsst_fluent_support_user_ids = array();
    private $jsst_fluent_support_agent_ids = array();
    private $jsst_fluent_support_ticket_ids = array();
    private $jsst_fluent_support_priority_ids = array();
    private $jsst_fluent_support_premade_ids = array();



    private $_params_flag;
    private $_params_string;



    // values for counts
    private $jsst_support_candy_import_count = [];
    private $jsst_awesome_support_import_count = [];
    private $jsst_fluent_support_import_count = [];

    function __construct() {
        $this->_params_flag = 0;
        $this->jsst_support_candy_import_count = [
            'user' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'agent_role' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'agent' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'department' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'priority' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'canned response' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'status' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'field' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'ticket' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ]
        ];
        $this->jsst_awesome_support_import_count = [
            'user' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'agent' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'department' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'priority' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'canned response' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'status' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'product' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'faq' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'field' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'ticket' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ]
        ];
        $this->jsst_fluent_support_import_count = [
            'user' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'agent' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'priority' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'canned response' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'product' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'field' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ],
            'ticket' => [
                'imported' => 0,
                'skipped'  => 0,
                'failed'   => 0,
            ]
        ];
    }


    function importSupportCandyData() {
        // Only for development – remove before pushing to production
        // $this->deletesupportcandyimporteddata();

        // Reset previously imported IDs from options
        // update_option('js_support_ticket_support_candy_data_statuses', '');
        // update_option('js_support_ticket_support_candy_data_priorities', '');
        // update_option('js_support_ticket_support_candy_data_users', '');
        // update_option('js_support_ticket_support_candy_data_departments', '');
        // update_option('js_support_ticket_support_candy_data_premades', '');
        // update_option('js_support_ticket_support_candy_data_agents', '');
        // update_option('js_support_ticket_support_candy_data_agent_roles', '');
        // update_option('js_support_ticket_support_candy_data_tickets', '');
        
        // Prepare filesystem and create necessary directories
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        $jsst_filesystem = new WP_Filesystem_Direct(true);
        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = $jsst_upload_path . "/" . $jsst_datadirectory;

        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }
        $jsst_path .= '/attachmentdata';
        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }
        $jsst_path .= '/ticket';
        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }

        // Optional: Import theme (disabled by default)
        // $this->importSupportCandyTheme();

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_customers'")) {
            $this->importSupportCandyUsers();
        }

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_agents'")) {
            $this->importSupportCandyAgentsRoles();
            $this->importSupportCandyAgents();
        }

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_categories'")) {
            $this->importSupportCandyDepartments();
        }

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_priorities'")) {
            $this->importSupportCandyPriorities();
        }

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_canned_reply'")) {
            $this->importSupportCandyPremades();
        }

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_statuses'")) {
            $this->importSupportCandyStatus();
        }

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_tickets'")) {
            $this->importSupportCandyTicketFields();
            $this->getSupportCandyTickets($this->jsst_sc_ticket_custom_fields);
        }

        update_option('jsst_import_counts',$this->jsst_support_candy_import_count);
        return;
    }

    private function importSupportCandyTheme() {
        $jsst_supportcandy_settings = get_option( 'wpsc-ap-general' );
        $jsst_helpdesk_settings = get_option('jsst_set_theme_colors');
        $jsst_data = json_decode($jsst_helpdesk_settings, true);
        $jsst_data['color1'] = $jsst_supportcandy_settings['primary-color'];
        $jsst_data['color4'] = $jsst_supportcandy_settings['main-text-color'];
        // store help desk settings
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);
        update_option('jsst_set_theme_colors', wp_json_encode($jsst_data));
    }

    private function getSupportCandyTickets($jsst_sc_ticket_custom_fields) {
        // Check if tickets already processed for import
        $jsst_imported_tickets = array();
        $jsst_imported_tickets_json = get_option('js_support_ticket_support_candy_data_tickets');
        if (!empty($jsst_imported_tickets_json)) {
            $jsst_imported_tickets = json_decode($jsst_imported_tickets_json, true);
        }

        $jsst_query = "SELECT tickets.*, replies.body AS reply_message, replies.type, replies.id AS replyid
                  FROM `" . jssupportticket::$_db->prefix . "psmsc_tickets` AS tickets
                  JOIN `" . jssupportticket::$_db->prefix . "psmsc_threads` AS replies ON replies.ticket = tickets.id 
                  WHERE replies.type = 'report' AND tickets.is_active != 0
                  ORDER BY tickets.id ASC";
        
        $jsst_tickets = jssupportticket::$_db->get_results($jsst_query);

        $jsst_general_options = get_option("wpsc-gs-general");
        $jsst_after_customer_reply = $jsst_general_options['ticket-status-after-customer-reply'];
        $jsst_after_agent_reply = $jsst_general_options['ticket-status-after-agent-reply'];
        $jsst_close_ticket_status = $jsst_general_options['close-ticket-status'];

        foreach ($jsst_tickets as $jsst_ticket) {
            // Skip if ticket already imported
            if (!empty($jsst_imported_tickets) && in_array($jsst_ticket->id, $jsst_imported_tickets)) {
                $this->jsst_support_candy_import_count['ticket']['skipped'] += 1;
                continue;
            }

            $jsst_attachmentdir = JSSTincluder::getJSModel('ticket')->getRandomFolderName();
            // Map custom fields
            $jsst_params = array();
            $jsst_eddorderid = '';
            $jsst_eddproductid = '';
            $jsst_wcproductid = '';
            $jsst_wcorderid = '';
            foreach ($jsst_sc_ticket_custom_fields as $jsst_sc_ticket_custom_field) {
                $jsst_field_name = $jsst_sc_ticket_custom_field["name"];
                $jsst_vardata = "";

                if ($jsst_ticket->$jsst_field_name) {
                    if ($jsst_sc_ticket_custom_field["type"] == "cf_edd_order") {
                        $jsst_vardata = '';
                        $jsst_eddorderid = $jsst_ticket->$jsst_field_name;
                    } elseif ($jsst_sc_ticket_custom_field["type"] == "cf_edd_product") {
                        $jsst_vardata = '';
                        $jsst_eddproductid = $jsst_ticket->$jsst_field_name;
                    } elseif ($jsst_sc_ticket_custom_field["type"] == "cf_woo_order") {
                        $jsst_vardata = '';
                        $jsst_wcorderid = $jsst_ticket->$jsst_field_name;
                    } elseif ($jsst_sc_ticket_custom_field["type"] == "cf_woo_product") {
                        $jsst_vardata = '';
                        $jsst_wcproductid = $jsst_ticket->$jsst_field_name;
                    } elseif ($jsst_sc_ticket_custom_field["type"] == "date") {
                        $jsst_vardata = gmdate("Y-m-d", jssupportticketphplib::JSST_strtotime($jsst_ticket->$jsst_field_name));
                    } elseif ($jsst_sc_ticket_custom_field["type"] == "file") {
                        $jsst_vardata = $jsst_ticket->$jsst_field_name;
                        $jsst_vardata = $this->getSupportCandyCustomFieldAttachments($jsst_ticket->id, $jsst_vardata, $jsst_attachmentdir);
                    } elseif (in_array(strtolower($jsst_sc_ticket_custom_field["type"]), ['multiple', 'checkbox', 'combo', 'radio'])) {
                        $jsst_field_ids = explode('|', $jsst_ticket->$jsst_field_name);

                        // Sanitize and cast to integers
                        $jsst_field_ids = array_map('intval', array_filter($jsst_field_ids));

                        // Check if we have valid IDs
                        if (!empty($jsst_field_ids)) {
                            $jsst_placeholders = implode(',', $jsst_field_ids);
                            $jsst_query = "SELECT name FROM `" . jssupportticket::$_db->prefix . "psmsc_options` WHERE id IN ($jsst_placeholders)";
                            $jsst_names = jssupportticket::$_db->get_col($jsst_query);

                            // Combine names into comma-separated string
                            $jsst_vardata = !empty($jsst_names) ? implode(', ', $jsst_names) : '';
                        } else {
                            $jsst_vardata = '';
                        }
                    } else {
                        $jsst_vardata = $jsst_ticket->$jsst_field_name;
                    }

                    if ($jsst_vardata != '') {
                        if (is_array($jsst_vardata)) {
                            $jsst_vardata = implode(', ', array_filter($jsst_vardata));
                        }
                        $jsst_params[$jsst_sc_ticket_custom_field["jshd_filedorderingfield"]] = jssupportticketphplib::JSST_htmlentities($jsst_vardata);
                    }
                }
            }
            $jsst_ticketparams = html_entity_decode(wp_json_encode($jsst_params, JSON_UNESCAPED_UNICODE));

            // Get linked data
            $jsst_userinfo = $this->getSupportCandyTicketCustomerInfo($jsst_ticket->customer);
            $jsst_agentid = $this->getTicketAgentIdBySupportCandy($jsst_ticket->assigned_agent);
            $jsst_departmentid = $this->getTicketDepartmentIdBySupportCandy($jsst_ticket->category);
            $jsst_priorityid = $this->getTicketPriorityIdBySupportCandy($jsst_ticket->priority);

            $jsst_idresult = JSSTincluder::getJSModel('ticket')->getRandomTicketId();
            $jsst_ticketid = $jsst_idresult['ticketid'];
            $jsst_customticketno = $jsst_idresult['customticketno'];

            // Determine ticket status
            $jsst_ticket_status = 1;
            if ($jsst_ticket->status == 1) $jsst_ticket_status = 1;
            elseif ($jsst_ticket->status == $jsst_after_customer_reply) $jsst_ticket_status = 2;
            elseif ($jsst_ticket->status == $jsst_after_agent_reply) $jsst_ticket_status = 4;
            elseif ($jsst_ticket->status == $jsst_close_ticket_status) $jsst_ticket_status = 5;
            else $jsst_ticket_status = $this->getTicketStatusIdBySupportCandy($jsst_ticket->status);

            $jsst_isanswered = ($jsst_ticket_status == 4) ? 1 : 0;

            $jsst_ticket_closed = "0000-00-00 00:00:00";
            if (!empty($jsst_ticket->date_closed) && $jsst_ticket->date_closed != '0000-00-00 00:00:00') {
                $jsst_ticket_status = 5;
                $jsst_ticket_closed = $jsst_ticket->date_closed;
            }
            // Ticket Default Status
            // 1 -> New Ticket
            // 2 -> Waiting admin/staff reply
            // 3 -> in progress
            // 4 -> waiting for customer reply
            // 5 -> close ticket

            $jsst_newTicketData = [
                'id' => "",
                'uid' => $jsst_userinfo["jshd_uid"],
                'ticketid' => $jsst_ticketid,
                'departmentid' => $jsst_departmentid,
                'priorityid' => $jsst_priorityid,
                'staffid' => $jsst_agentid,
                'email' => $jsst_userinfo["customer_email"],
                'name' => $jsst_userinfo["customer_name"],
                'subject' => $jsst_ticket->subject,
                'message' => $jsst_ticket->reply_message,
                'helptopicid' => 0,
                'multiformid' => 1,
                'phone' => "",
                'phoneext' => "",
                'status' => $jsst_ticket_status,
                'isoverdue' => "0",
                'isanswered' => $jsst_isanswered,
                'duedate' => "0000-00-00 00:00:00",
                'reopened' => "0000-00-00 00:00:00",
                'closed' => $jsst_ticket_closed,
                'closedby' => "0",
                'lastreply' => $jsst_ticket->last_reply_on,
                'created' => $jsst_ticket->date_created,
                'updated' => $jsst_ticket->date_updated,
                'lock' => "0",
                'ticketviaemail' => "0",
                'ticketviaemail_id' => "0",
                'attachmentdir' => $jsst_attachmentdir,
                'feedbackemail' => "0",
                'mergestatus' => "0",
                'mergewith' => "0",
                'mergenote' => "",
                'mergedate' => "0000-00-00 00:00:00",
                'multimergeparams' => "",
                'mergeuid' => "0",
                'params' => $jsst_ticketparams,
                'hash' => "",
                'notificationid' => "0",
                'wcorderid' => $jsst_wcorderid,
                'wcitemid' => "0",
                'wcproductid' => $jsst_wcproductid,
                'eddorderid' => $jsst_eddorderid,
                'eddproductid' => $jsst_eddproductid,
                'eddlicensekey' => "",
                'envatodata' => "",
                'paidsupportitemid' => "0",
                'customticketno' => $jsst_customticketno
            ];

            $jsst_row = JSSTincluder::getJSTable('tickets');
            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_newTicketData)) $jsst_error = 1;
            if (!$jsst_row->store()) $jsst_error = 1;

            if ($jsst_error == 1) {
                $this->jsst_support_candy_import_count['ticket']['failed'] += 1;
            } else {
                $this->jsst_support_candy_ticket_ids[] = $jsst_ticket->id;
                $this->jsst_support_candy_import_count['ticket']['imported'] += 1;

                $jsst_jshd_ticketid = $jsst_row->id;
                $jsst_hash = JSSTincluder::getJSModel('ticket')->generateHash($jsst_jshd_ticketid);
                $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET `hash`='" . esc_sql($jsst_hash) . "' WHERE id=" . esc_sql($jsst_jshd_ticketid);
                jssupportticket::$_db->query($jsst_query);

                if(in_array('note', jssupportticket::$_active_addons)){
                    $this->getSupportCandyTicketNotes($jsst_jshd_ticketid, $jsst_ticket->id, $jsst_attachmentdir);
                }
                $this->getSupportCandyTicketReplies($jsst_jshd_ticketid, $jsst_ticket->id, $jsst_attachmentdir);
                $this->getSupportCandyTicketAttachments($jsst_jshd_ticketid, "", $jsst_ticket->replyid, $jsst_attachmentdir);

                if (!empty($jsst_ticket->pc_data) && in_array('privatecredentials', jssupportticket::$_active_addons)) {
                    $this->getSupportCandyTicketPrivateCredentials($jsst_jshd_ticketid, $jsst_userinfo["jshd_uid"], $jsst_ticket->pc_data);
                }

                if (in_array('tickethistory', jssupportticket::$_active_addons)) {
                    $this->getSupportCandyTicketActivityLog($jsst_jshd_ticketid, $jsst_ticket->id);
                }

                if (in_array('timetracking', jssupportticket::$_active_addons)) {
                    $this->getSupportCandyTicketStaffTime($jsst_jshd_ticketid, $jsst_ticket->id);
                }
            }
        }

        if (!empty($this->jsst_support_candy_ticket_ids)) {
            update_option('js_support_ticket_support_candy_data_tickets', wp_json_encode($this->jsst_support_candy_ticket_ids));
        }
    }

    private function getSupportCandyTicketNotes($jsst_jshd_ticket_id, $jsst_sc_ticket_id, $jsst_attachmentdir){
        $jsst_query = "
            SELECT thread.*
                FROM `" . jssupportticket::$_db->prefix . "psmsc_threads` AS thread
                WHERE thread.ticket = " . (int)$jsst_sc_ticket_id . "
                AND thread.type = 'note'
                ORDER BY thread.id ASC";
                    
        $jsst_threads = jssupportticket::$_db->get_results($jsst_query);
        foreach($jsst_threads AS $jsst_thread){
            $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` WHERE wpuid = ".$jsst_thread->customer;
            $jsst_agentid = $jsst_jshd_user_id = jssupportticket::$_db->get_var($jsst_query);
            $jsst_filename = $this->getSupportCandyNoteAttachments($jsst_sc_ticket_id, $jsst_thread->attachments, $jsst_attachmentdir);

            $jsst_replyData = [
                "id" => "",
                "ticketid" => $jsst_jshd_ticket_id,
                "staffid" => $jsst_agentid,
                "title" => jssupportticketphplib::JSST_strip_tags($jsst_thread->body),
                "note" => $jsst_thread->body,
                "status" => "1",
                "created" => $jsst_thread->date_created,
                "filename" => $jsst_filename,
                "filesize" => 5334
            ];
            $jsst_row = JSSTincluder::getJSTable('note');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_replyData);// remove slashes with quotes.
            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_data)) {
                $jsst_error = 1;
            }
            if (!$jsst_row->store()) {
                $jsst_error = 1;
            }
            $jsst_jshd_ticket_note_id = $jsst_row->id;
        }
    }

    private function getSupportCandyTicketReplies($jsst_jshd_ticket_id, $jsst_sc_ticket_id, $jsst_attachmentdir){
        $jsst_query = "SELECT thread.*
                    FROM `" . jssupportticket::$_db->prefix . "psmsc_threads` AS thread
                    WHERE thread.ticket = " . (int)$jsst_sc_ticket_id . "
                    AND thread.type = 'reply'
                    ORDER BY thread.id ASC";
                    
        $jsst_threads = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_threads)) return;

        foreach ($jsst_threads as $jsst_thread) {
            $jsst_userinfo = $this->getSupportCandyTicketCustomerInfo($jsst_thread->customer);

            $jsst_replyData = [
                "id" => "",
                "uid" => isset($jsst_userinfo["jshd_uid"]) ? $jsst_userinfo["jshd_uid"] : 0,
                "ticketid" => $jsst_jshd_ticket_id,
                "name" => isset($jsst_userinfo["customer_name"]) ? $jsst_userinfo["customer_name"] : __('Guest', 'js-support-ticket'),
                "message" => $jsst_thread->body,
                "staffid" => "",
                "rating" => "",
                "status" => "1",
                "created" => $jsst_thread->date_created,
                "ticketviaemail" => "",
                "viewed_by" => "",
                "viewed_on" => $jsst_thread->seen
            ];

            $jsst_row = JSSTincluder::getJSTable('replies');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_replyData);

            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_data)) {
                $jsst_error = 1;
            }
            if (!$jsst_row->store()) {
                $jsst_error = 1;
            }

            $jsst_jshd_ticket_reply_id = $jsst_row->id;

            if (!empty($jsst_jshd_ticket_reply_id)) {
                $this->getSupportCandyTicketAttachments($jsst_jshd_ticket_id, $jsst_jshd_ticket_reply_id, $jsst_thread->id, $jsst_attachmentdir);
            }
        }
    }

    private function getSupportCandyNoteAttachments($jsst_ticket_id, $jsst_attachments, $jsst_attachmentdir){
        // Split by pipe
        $jsst_parts = explode('|', $jsst_attachments);

        // Get the first numeric value
        $jsst_attachment_id = isset($jsst_parts[0]) ? intval($jsst_parts[0]) : null;

        if (empty($jsst_attachment_id)) return;

        $jsst_query = "
        SELECT attachment.*
            FROM `" . jssupportticket::$_db->prefix . "psmsc_attachments` AS attachment
            WHERE attachment.id = " . (int)$jsst_attachment_id;
                    
        $jsst_attachment = jssupportticket::$_db->get_row($jsst_query);

        if (empty($jsst_attachment)) return;

        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

        $jsst_filesystem = new WP_Filesystem_Direct(true);
        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = trailingslashit($jsst_upload_path) . $jsst_datadirectory . "/attachmentdata/ticket/" . sanitize_file_name($jsst_attachmentdir);

        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }
        $jsst_safe_filename = sanitize_file_name($jsst_attachment->name);
        $jsst_source = $jsst_upload_path . $jsst_attachment->file_path;
        $jsst_destination = $jsst_path . "/" . $jsst_safe_filename;

        if (!file_exists($jsst_source)) {
            // Debug code should not normally be used in production.
            return '';
        }

        $jsst_result = $jsst_filesystem->copy($jsst_source, $jsst_destination, true);
        if (!$jsst_result) {
            // Debug code should not normally be used in production.
            return '';
        }
        return $jsst_attachment->name;
        
    }

    private function getSupportCandyCustomFieldAttachments($jsst_ticket_id, $jsst_field_id, $jsst_attachmentdir){
        $jsst_query = "SELECT attachment.*
                    FROM `" . jssupportticket::$_db->prefix . "psmsc_attachments` AS attachment
                    WHERE attachment.id = " . (int)$jsst_field_id;
                    
        $jsst_attachment = jssupportticket::$_db->get_row($jsst_query);

        if (empty($jsst_attachment)) return;

        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

        $jsst_filesystem = new WP_Filesystem_Direct(true);
        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = trailingslashit($jsst_upload_path) . $jsst_datadirectory . "/attachmentdata/ticket/" . sanitize_file_name($jsst_attachmentdir);

        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }
        $jsst_safe_filename = sanitize_file_name($jsst_attachment->name);
        $jsst_source = $jsst_upload_path . $jsst_attachment->file_path;
        $jsst_destination = $jsst_path . "/" . $jsst_safe_filename;

        if (!file_exists($jsst_source)) {
            // Debug code should not normally be used in production.
            return '';
        }

        $jsst_result = $jsst_filesystem->copy($jsst_source, $jsst_destination, true);
        if (!$jsst_result) {
            // Debug code should not normally be used in production.
            return '';
        }
        return $jsst_attachment->name;
        
    }

    private function getSupportCandyTicketAttachments($jsst_jshd_ticket_id, $jsst_jshd_ticket_reply_id, $jsst_sc_ticket_reply_id, $jsst_attachmentdir){
        $jsst_query = "SELECT attachment.*
                    FROM `" . jssupportticket::$_db->prefix . "psmsc_attachments` AS attachment
                    WHERE attachment.source_id = " . (int)$jsst_sc_ticket_reply_id . "
                    ORDER BY attachment.id ASC";
                    
        $jsst_attachments = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_attachments)) return;

        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

        $jsst_filesystem = new WP_Filesystem_Direct(true);
        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = trailingslashit($jsst_upload_path) . $jsst_datadirectory . "/attachmentdata/ticket/" . sanitize_file_name($jsst_attachmentdir);

        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }

        foreach ($jsst_attachments as $jsst_attachment) {
            $jsst_safe_filename = sanitize_file_name($jsst_attachment->name);
            $jsst_source = $jsst_upload_path . $jsst_attachment->file_path;
            $jsst_destination = $jsst_path . "/" . $jsst_safe_filename;

            $jsst_attachmentData = [
                "id" => "",
                "ticketid" => $jsst_jshd_ticket_id,
                "replyattachmentid" => $jsst_jshd_ticket_reply_id,
                "filesize" => "", // Optionally: filesize($jsst_source)
                "filename" => $jsst_safe_filename,
                "filekey" => "",
                "deleted" => "",
                "status" => "1",
                "created" => $jsst_attachment->date_created
            ];

            $jsst_row = JSSTincluder::getJSTable('attachments');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_attachmentData);

            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_data)) {
                $jsst_error = 1;
            }
            if (!$jsst_row->store()) {
                $jsst_error = 1;
            }

            if (!file_exists($jsst_source)) {
                // Debug code should not normally be used in production.
                continue;
            }

            $jsst_result = $jsst_filesystem->copy($jsst_source, $jsst_destination, true);
            if (!$jsst_result) {
                // Debug code should not normally be used in production.
            }
        }
    }

    private function getSupportCandyTicketPrivateCredentials($jsst_jshd_ticket_id, $jsst_jshd_ticket_uid, $jsst_pc_data) {
        $jsst_decoded_data = json_decode($jsst_pc_data, true);

        if (empty($jsst_decoded_data) || !isset($jsst_decoded_data['data'], $jsst_decoded_data['secure_key'], $jsst_decoded_data['secure_iv'])) {
            return; // Invalid or incomplete data
        }

        $jsst_privateCredentials = $jsst_decoded_data['data'];
        $jsst_secure_key = base64_decode($jsst_decoded_data['secure_key']);
        $jsst_secure_iv = base64_decode($jsst_decoded_data['secure_iv']);
        $jsst_cipher = 'AES-128-CBC';

        foreach ($jsst_privateCredentials as $jsst_privateCredential) {
            if (empty($jsst_privateCredential['data']) || !is_array($jsst_privateCredential['data'])) {
                continue;
            }

            $jsst_pc_data_info = '';

            foreach ($jsst_privateCredential['data'] as $jsst_entry) {
                if (!isset($jsst_entry['label'], $jsst_entry['value'])) continue;

                $jsst_decrypted_value = openssl_decrypt(
                    base64_decode($jsst_entry['value']),
                    $jsst_cipher,
                    $jsst_secure_key,
                    0,
                    $jsst_secure_iv
                );

                $jsst_label = sanitize_text_field($jsst_entry['label']);
                $jsst_value = sanitize_text_field($jsst_decrypted_value);

                $jsst_pc_data_info .= "$jsst_label : $jsst_value , ";
            }

            $jsst_pc_array = [
                'credentialtype' => sanitize_text_field($jsst_privateCredential['title']),
                'username'       => '',
                'password'       => '',
                'info'           => rtrim($jsst_pc_data_info, ' , ')
            ];

            $jsst_data = [
                'id'        => '',
                'uid'       => intval($jsst_jshd_ticket_uid),
                'ticketid'  => intval($jsst_jshd_ticket_id),
                'status'    => 1,
                'created'   => current_time('mysql'),
            ];

            // Clean and encode credential info
            $jsst_encoded = wp_json_encode(array_filter($jsst_pc_array));
            $jsst_safe_encoded = jssupportticketphplib::JSST_safe_encoding($jsst_encoded);
            $jsst_data['data'] = JSSTincluder::getObjectClass('privatecredentials')->encrypt($jsst_safe_encoded);

            // Insert record
            if ($jsst_data['ticketid'] > 0 && $jsst_data['uid'] > 0) {
                $jsst_row = JSSTincluder::getJSTable('privatecredentials');
                if ($jsst_row->bind($jsst_data)) {
                    $jsst_row->store(); // Failure silently ignored here; consider logging
                }
            }
        }
    }

    private function getSupportCandyTicketActivityLog($jsst_jshd_ticket_id, $jsst_sc_ticket_id) {
        $jsst_sc_ticket_id = intval($jsst_sc_ticket_id);
        $jsst_jshd_ticket_id = intval($jsst_jshd_ticket_id);

        if ($jsst_sc_ticket_id <= 0 || $jsst_jshd_ticket_id <= 0) return;

        $jsst_query = "
            SELECT * FROM `" . jssupportticket::$_db->prefix . "psmsc_threads`
            WHERE (type = 'log' OR type = 'reply' OR type = 'note') AND ticket = ".$jsst_sc_ticket_id." ORDER BY date_created DESC ";

        $jsst_threads = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_threads)) return;

        foreach ($jsst_threads as $jsst_thread) {
            $jsst_ticketid = $jsst_jshd_ticket_id;

            // Get user information
            $jsst_userinfo = $this->getSupportCandyTicketCustomerInfo($jsst_thread->customer);
            $jsst_currentUserName = !empty($jsst_userinfo['customer_name']) 
                ? esc_html($jsst_userinfo['customer_name']) 
                : esc_html(__('Guest', 'js-support-ticket'));

            $jsst_messagetype = __('Successfully', 'js-support-ticket');
            $jsst_eventtype = '';
            $jsst_message = '';

            if ($jsst_thread->type === 'log') {
                $jsst_body = json_decode($jsst_thread->body);
                if (!empty($jsst_body) && isset($jsst_body->slug)) {
                    switch ($jsst_body->slug) {
                        case 'assigned_agent':
                            $jsst_eventtype = __('Assign ticket to agent', 'js-support-ticket');
                            $jsst_message = __('Ticket is assigned to agent by', 'js-support-ticket') . " ( $jsst_currentUserName )";
                            break;
                        case 'status':
                            if ($jsst_thread->customer == 0) {
                                continue 2;
                            }
                            $jsst_eventtype = __('Ticket status change', 'js-support-ticket');
                            $jsst_message = __('The status is changed by', 'js-support-ticket') . " ( $jsst_currentUserName )";
                            break;
                        case 'priority':
                            $jsst_eventtype = __('Change Priority', 'js-support-ticket');
                            $jsst_message = __('Ticket priority is changed by', 'js-support-ticket') . " ( $jsst_currentUserName )";
                            break;
                        case 'category':
                            $jsst_eventtype = __('Ticket department transfer', 'js-support-ticket');
                            $jsst_message = __('The department is transferred by', 'js-support-ticket') . " ( $jsst_currentUserName )";
                            break;
                        case 'subject':
                        case 'customer':
                            // Optionally handle or skip
                            break;
                    }
                }
            } elseif ($jsst_thread->type === 'reply') {
                $jsst_eventtype = __('REPLIED_TICKET', 'js-support-ticket');
                $jsst_message = __('Ticket is replied by', 'js-support-ticket') . " ( $jsst_currentUserName )";
            } elseif ($jsst_thread->type === 'note') {
                $jsst_eventtype = __('Post Internal Note', 'js-support-ticket');
                $jsst_message = __('The internal note is posted by', 'js-support-ticket') . " ( $jsst_currentUserName )";
            }

            if (!empty($jsst_eventtype) && !empty($jsst_message)) {
                JSSTincluder::getJSModel('tickethistory')->addActivityLog(
                    $jsst_ticketid, 1, esc_html($jsst_eventtype), esc_html($jsst_message), esc_html($jsst_messagetype)
                );
            }
        }
    }

    private function getSupportCandyTicketStaffTime($jsst_jshd_ticket_id, $jsst_sc_ticket_id) {
        $jsst_sc_ticket_id = intval($jsst_sc_ticket_id);
        $jsst_jshd_ticket_id = intval($jsst_jshd_ticket_id);
        if ($jsst_sc_ticket_id <= 0 || $jsst_jshd_ticket_id <= 0) return;

        // Get all timer logs for the given SupportCandy ticket
        $jsst_query = "
            SELECT * FROM `" . jssupportticket::$_db->prefix . "psmsc_timer_logs`
            WHERE ticket = ".$jsst_sc_ticket_id;
        $jsst_timers = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_timers)) return;

        foreach ($jsst_timers as $jsst_timer) {
            // Get HelpDesk staff ID from SupportCandy agent ID
            $jsst_staffid = $this->getJshdAgentIdByScAgentId($jsst_timer->log_by);
            if (empty($jsst_staffid)) continue;

            $jsst_created = $jsst_timer->date_started;

            // Handle and validate interval string
            try {
                $jsst_interval = new DateInterval($jsst_timer->time_spent);
            } catch (Exception $jsst_e) {
                continue; // skip invalid time format
            }

            $jsst_timer_seconds = ($jsst_interval->d * 86400) + ($jsst_interval->h * 3600) + ($jsst_interval->i * 60) + $jsst_interval->s;
            if ($jsst_timer_seconds <= 0) continue;

            // Conflict detection
            $jsst_created_dt = new DateTime($jsst_created);
            $jsst_now = new DateTime();
            $jsst_interval_to_now = $jsst_created_dt->diff($jsst_now);
            $jsst_systemtime = ($jsst_interval_to_now->days * 86400) + ($jsst_interval_to_now->h * 3600) + ($jsst_interval_to_now->i * 60) + $jsst_interval_to_now->s;

            $jsst_conflict = ($jsst_timer_seconds > $jsst_systemtime) ? 1 : 0;

            // Prepare data
            $jsst_data = [
                'staffid' => $jsst_staffid,
                'ticketid' => $jsst_jshd_ticket_id,
                'referencefor' => 1,
                'referenceid' => 0,
                'usertime' => $jsst_timer_seconds,
                'systemtime' => $jsst_systemtime,
                'conflict' => $jsst_conflict,
                'description' => $jsst_timer->description,
                'timer_edit_desc' => $jsst_timer->description,
                'status' => 1,
                'created' => $jsst_created
            ];

            $jsst_row = JSSTincluder::getJSTable('timetracking');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);

            if (!$jsst_row->bind($jsst_data) || !$jsst_row->store()) {
                // optionally log or count the failure
                continue;
            }
        }
    }

    private function getSupportCandyTicketCustomerInfo($jsst_customerId) {
        // Sanitize and validate customer ID
        $jsst_customerId = intval($jsst_customerId);
        if ($jsst_customerId <= 0) {
            return [
                "jshd_uid" => "",
                "customer_name" => "",
                "customer_email" => ""
            ];
        }

        // Prepare secure query
        $jsst_query = "
            SELECT customer.name, customer.email, user.id AS jshd_uid
            FROM `" . jssupportticket::$_db->prefix . "psmsc_customers` AS customer
            INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user
                ON user.wpuid = customer.user
            WHERE customer.id = " . esc_sql($jsst_customerId) . "
            LIMIT 1
        ";

        $jsst_data = jssupportticket::$_db->get_row($jsst_query);

        return [
            "jshd_uid"       => $jsst_data->jshd_uid ?? "",
            "customer_name"  => $jsst_data->name ?? "",
            "customer_email" => $jsst_data->email ?? ""
        ];
    }

    private function getJshdAgentIdByScAgentId($jsst_sc_agent_id) {
        // Sanitize and validate input
        $jsst_sc_agent_id = intval($jsst_sc_agent_id);
        if ($jsst_sc_agent_id <= 0) return null;

        // Secure SQL query using prepare()
        $jsst_query = "
            SELECT agent.*
            FROM `" . jssupportticket::$_db->prefix . "psmsc_agents` AS sc_agent
            INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user
                ON user.wpuid = sc_agent.user
            INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS agent
                ON agent.uid = user.id
            WHERE sc_agent.id = " . esc_sql($jsst_sc_agent_id) . "
            LIMIT 1
        ";

        $jsst_jshd_agent = jssupportticket::$_db->get_row($jsst_query);

        return $jsst_jshd_agent ?: null;
    }

    private function getTicketAgentIdBySupportCandy($jsst_customerId) {
        // Validate customer ID
        $jsst_customerId = intval($jsst_customerId);
        if ($jsst_customerId <= 0) {
            return null;
        }

        // Get mapped user info
        $jsst_jshd_user = $this->getSupportCandyTicketCustomerInfo($jsst_customerId);
        if (empty($jsst_jshd_user['jshd_uid'])) {
            return null;
        }

        $jsst_uid = intval($jsst_jshd_user['jshd_uid']);

        // Securely query agent by UID
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` WHERE uid = ".$jsst_uid;
            $jsst_jshd_agent_id = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_jshd_agent_id ? (int)$jsst_jshd_agent_id : null;
    }
    
    private function getTicketDepartmentIdBySupportCandy($jsst_categoryId) {
        // Validate and sanitize category ID
        $jsst_categoryId = intval($jsst_categoryId);
        if ($jsst_categoryId <= 0) return null;

        // Get department (category) name from old table
        $jsst_query = "
            SELECT name FROM `" . jssupportticket::$_db->prefix . "psmsc_categories` WHERE id = ".$jsst_categoryId;
        $jsst_category_name = jssupportticket::$_db->get_var($jsst_query);

        if (empty($jsst_category_name)) return null;

        // Match department by name (case-insensitive)
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE LOWER(departmentname) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_category_name)))."'";
        $jsst_jshd_department_id = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_jshd_department_id ? (int)$jsst_jshd_department_id : null;
    }

    private function getTicketStatusIdBySupportCandy($jsst_statusId) {
        // Sanitize and validate input
        $jsst_statusId = intval($jsst_statusId);
        if ($jsst_statusId <= 0) return null;

        // Get status name from source table
        $jsst_query = "
            SELECT name FROM `" . jssupportticket::$_db->prefix . "psmsc_statuses` WHERE id = ".$jsst_statusId;
        $jsst_status_name = jssupportticket::$_db->get_var($jsst_query);

        if (empty($jsst_status_name)) return null;

        // Find matching status in destination table
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` WHERE LOWER(status) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_status_name)))."'";
        $jsst_jshd_status_id = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_jshd_status_id ? (int)$jsst_jshd_status_id : null;
    }

    private function getTicketPriorityIdBySupportCandy($jsst_priorityId) {
        // Sanitize and validate input
        $jsst_priorityId = intval($jsst_priorityId);
        if ($jsst_priorityId <= 0) return null;

        // Fetch priority from source table
        $jsst_query = "
            SELECT name
            FROM `" . jssupportticket::$_db->prefix . "psmsc_priorities` 
            WHERE id = ".$jsst_priorityId;
        $jsst_priority_name = jssupportticket::$_db->get_var($jsst_query);

        if (empty($jsst_priority_name)) return null;

        // Find corresponding priority in destination table
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` 
                WHERE LOWER(priority) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_priority_name)))."'";
            $jsst_jshd_priority_id = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_jshd_priority_id ? (int)$jsst_jshd_priority_id : null;
    }

    private function getAgentRoleIdBySupportCandy($jsst_roleId) {
        // Get stored agent roles
        $jsst_roles = get_option('wpsc-agent-roles', array());

        // Get role label for the given role ID
        $jsst_role_label = isset($jsst_roles[$jsst_roleId]['label']) ? jssupportticketphplib::JSST_trim($jsst_roles[$jsst_roleId]['label']) : '';

        if (!empty($jsst_role_label)) {
            // Prepare and execute safe SQL query
            $jsst_query = "SELECT id
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_roles` 
                WHERE LOWER(name) = '" . jssupportticketphplib::JSST_strtolower(esc_sql($jsst_role_label)) . "'";
            $jsst_jshd_roleid = jssupportticket::$_db->get_var($jsst_query);

            return $jsst_jshd_roleid ? (int)$jsst_jshd_roleid : null;
        }

        return null;
    }

    private function importSupportCandyUsers() {
        // check if user already processed for import
        $jsst_imported_users = array();
        $jsst_imported_users_json = get_option('js_support_ticket_support_candy_data_users');
        if(!empty($jsst_imported_users_json)){
            $jsst_imported_users = json_decode($jsst_imported_users_json,true);
        }

        // Fetch all customers
        $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "psmsc_customers`";
        $jsst_customers = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_customers)) return;

        foreach ($jsst_customers as $jsst_customer) {
            $jsst_customer_id = intval($jsst_customer->id);
            $jsst_wpuid       = intval($jsst_customer->user);
            $jsst_name        = sanitize_text_field($jsst_customer->name ?? '');
            $jsst_email       = sanitize_email($jsst_customer->email ?? '');

            // Skip if already imported
            if (in_array($jsst_customer_id, $jsst_imported_users, true)) {
                $this->jsst_support_candy_import_count['user']['skipped']++;
                continue;
            }

            // Check if user already exists
            $jsst_user_query = "SELECT user.*
                       FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user
                       WHERE user.wpuid = ".$jsst_wpuid;
            $jsst_existing_user = jssupportticket::$_db->get_row($jsst_user_query);

            if ($jsst_existing_user) {
                $this->jsst_support_candy_import_count['user']['skipped']++;
                continue;
            }

            // Prepare data for new user
            $jsst_row = JSSTincluder::getJSTable('users');
            $jsst_data = [
                'id'            => '',
                'wpuid'         => $jsst_wpuid,
                'name'          => $jsst_name,
                'display_name'  => $jsst_name,
                'user_email'    => $jsst_email,
                'status'        => 1,
                'issocial'      => 0,
                'socialid'      => null,
                'autogenerated' => 0,
            ];

            // Attempt to save the new user
            $jsst_row->bind($jsst_data);
            if (!$jsst_row->store()) {
                $this->jsst_support_candy_import_count['user']['failed']++;
                continue;
            }

            // Store successful import info
            $this->jsst_support_candy_users_array[$jsst_customer_id] = $jsst_row->id;
            $this->jsst_support_candy_user_ids[] = $jsst_customer_id;
            $this->jsst_support_candy_import_count['user']['imported']++;
        }

        // Save list of imported user IDs
        if (!empty($this->jsst_support_candy_user_ids)) {
            update_option('js_support_ticket_support_candy_data_users', wp_json_encode(array_unique(array_merge($jsst_imported_users, $this->jsst_support_candy_user_ids))));
        }
    }

    private function importSupportCandyTicketFields() {
        // Get all ticket-related custom fields
        $jsst_query = "
            SELECT * FROM `" . jssupportticket::$_db->prefix . "psmsc_custom_fields`
            WHERE slug LIKE 'cust_%' AND type LIKE 'cf_%'
            AND field = 'ticket';";
        $jsst_custom_fields = jssupportticket::$_db->get_results($jsst_query);

        if (!$jsst_custom_fields) return;

        // Get visibility settings
        $jsst_ticket_field_options = get_option("wpsc-tff");

        $this->jsst_sc_ticket_custom_fields = [];
        $this->sc_ticket_custom_fields_custom = [];

        foreach ($jsst_custom_fields as $jsst_custom_field) {
            $jsst_slug = esc_sql($jsst_custom_field->slug);

            // Map field types
            switch ($jsst_custom_field->type) {
                case "cf_textfield":
                case "cf_number":
                case "cf_url":
                case "cf_time":
                    $jsst_fieldtype = "text"; break;
                case "cf_multi_select":
                    $jsst_fieldtype = "multiple"; break;
                case "cf_single_select":
                    $jsst_fieldtype = "combo"; break;
                case "cf_radio_button":
                    $jsst_fieldtype = "radio"; break;
                case "cf_checkbox":
                    $jsst_fieldtype = "checkbox"; break;
                case "cf_textarea":
                    $jsst_fieldtype = "textarea"; break;
                case "cf_date":
                case "cf_datetime":
                    $jsst_fieldtype = "date"; break;
                case "cf_email":
                    $jsst_fieldtype = "email"; break;
                case "cf_file_attachment_multiple":
                case "cf_file_attachment_single":
                    $jsst_fieldtype = "file"; break;
                case "cf_edd_order":
                    $this->jsst_sc_ticket_custom_fields[] = [
                        "name" => $jsst_slug,
                        "type" => 'cf_edd_order',
                        "jshd_filedorderingid" => '',
                        "jshd_filedorderingfield" => '',
                    ];
                    $this->jsst_support_candy_import_count['field']['skipped'] += 1;
                    continue 2; break;
                case "cf_edd_product":
                    $this->jsst_sc_ticket_custom_fields[] = [
                        "name" => $jsst_slug,
                        "type" => 'cf_edd_product',
                        "jshd_filedorderingid" => '',
                        "jshd_filedorderingfield" => '',
                    ];
                    $this->jsst_support_candy_import_count['field']['skipped'] += 1;
                    continue 2; break;
                case "cf_woo_order":
                    $this->jsst_sc_ticket_custom_fields[] = [
                        "name" => $jsst_slug,
                        "type" => 'cf_woo_order',
                        "jshd_filedorderingid" => '',
                        "jshd_filedorderingfield" => '',
                    ];
                    $this->jsst_support_candy_import_count['field']['skipped'] += 1;
                    continue 2; break;
                case "cf_woo_product":
                    $this->jsst_sc_ticket_custom_fields[] = [
                        "name" => $jsst_slug,
                        "type" => 'cf_woo_product',
                        "jshd_filedorderingid" => '',
                        "jshd_filedorderingfield" => '',
                    ];
                    $this->jsst_support_candy_import_count['field']['skipped'] += 1;
                    continue 2; break;
                default:
                    $jsst_fieldtype = "text"; break;
            }

            $jsst_query = "SELECT id,field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE isuserfield = 1 AND LOWER(fieldtitle) ='".esc_sql(jssupportticketphplib::JSST_strtolower($jsst_custom_field->name))."' AND userfieldtype ='".esc_sql($jsst_fieldtype)."' AND fieldfor = 1";
            $jsst_field_record = jssupportticket::$_db->get_row($jsst_query);

            if(!empty($jsst_field_record)){ // this will make sure
                $this->jsst_support_candy_import_count['field']['skipped'] += 1;
                continue;
            }
            // Load options for select-type fields
            $jsst_option_values = [];

            $jsst_table = jssupportticket::$_db->prefix . "psmsc_tickets";
            $jsst_column = $jsst_custom_field->slug;

            // Get all columns from the table only once
            static $jsst_existing_columns = null;

            if ($jsst_existing_columns === null) {
                $jsst_existing_columns = jssupportticket::$_db->get_col("SHOW COLUMNS FROM `$jsst_table`");
            }

            // Check if the column exists
            if (in_array($jsst_column, $jsst_existing_columns)) {
                $jsst_query = "SELECT `" . $jsst_column . "` FROM `" . $jsst_table . "`";
                $jsst_field = jssupportticket::$_db->get_row($jsst_query);
            } else {
                $jsst_field = null; // Column doesn't exist
            }
            if(isset($jsst_field)){ // field in the ticket table
                $jsst_query = "SELECT name FROM `" . jssupportticket::$_db->prefix . "psmsc_options` WHERE custom_field = ".$jsst_custom_field->id." ORDER BY load_order;";
                $jsst_field_options = jssupportticket::$_db->get_results($jsst_query);
                if ($jsst_field_options) {
                    foreach ($jsst_field_options as $jsst_field_option) {
                        $jsst_option_values[] = $jsst_field_option->name;
                    }
                }
            }

            // Build visibility data
            $jsst_visibledata = [
                "visibleLogic" => [],
                "visibleParent" => [],
                "visibleValue" => [],
                "visibleCondition" => [],
            ];

            $jsst_defaultvalue_input = "";
            $jsst_defaultvalue_select = "";
            if($jsst_fieldtype == "combo" || $jsst_fieldtype == "radio" || $jsst_fieldtype == "multiple" || $jsst_fieldtype == "checkbox" || $jsst_fieldtype == "depandant_field") {

                $jsst_field_ids = explode('|', $jsst_custom_field->default_value);

                // Sanitize and cast to integers
                $jsst_field_id = isset($jsst_field_ids[0]) ? intval($jsst_field_ids[0]) : null;

                // Check if we have valid IDs
                if (!empty($jsst_field_id)) {
                    $jsst_query = "SELECT name FROM `" . jssupportticket::$_db->prefix . "psmsc_options` WHERE id = " . $jsst_field_id;
                    $jsst_name = jssupportticket::$_db->get_col($jsst_query);

                    // Combine names into comma-separated string
                    $jsst_vardata = !empty($jsst_name) ? implode(', ', $jsst_name) : '';
                } else {
                    $jsst_vardata = '';
                }

                $jsst_defaultvalue_select = $jsst_vardata;
            } else {
                $jsst_defaultvalue_input = $jsst_custom_field->default_value;
            }

            // Prepare field data for import
            $jsst_fieldOrderingData = [
                "id" => "",
                "field" => $jsst_slug,
                "fieldtitle" => $jsst_custom_field->name,
                "ordering" => "",
                "section" => "10",
                "placeholder" => $jsst_custom_field->placeholder_text,
                "description" => $jsst_custom_field->extra_info,
                "fieldfor" => "1",
                "published" => "1",
                "sys" => "0",
                "cannotunpublish" => "0",
                "required" => "0",
                "size" => "100",
                "maxlength" => $jsst_custom_field->char_limit,
                "cols" => "",
                "rows" => "",
                "isuserfield" => "1",
                "userfieldtype" => $jsst_fieldtype,
                "depandant_field" => "",
                "visible_field" => "",
                "showonlisting" => "0",
                "cannotshowonlisting" => "0",
                "search_user" => "0",
                "search_admin" => "0",
                "cannotsearch" => "0",
                "isvisitorpublished" => "1",
                "search_visitor" => "0",
                "multiformid" => "1",
                "userfieldparams" => "",
                "visibleparams" => "",
                "values" => $jsst_option_values,
                "visibleParent" => $jsst_visibledata["visibleParent"],
                "visibleValue" => $jsst_visibledata["visibleValue"],
                "visibleCondition" => $jsst_visibledata["visibleCondition"],
                "visibleLogic" => $jsst_visibledata["visibleLogic"],
                "readonly" => 0,
                "adminonly" => 0,
                "defaultvalue_select" => $jsst_defaultvalue_select,
                "defaultvalue_input" => $jsst_defaultvalue_input,
            ];

            // Store field in SupportCandy
            $jsst_record_saved = JSSTincluder::getJSModel('fieldordering')->storeUserField($jsst_fieldOrderingData);

            if ($jsst_record_saved == 1) {
                $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` ORDER BY id DESC LIMIT 1";
                    $jsst_latest_record = jssupportticket::$_db->get_row($jsst_query);

                $this->jsst_sc_ticket_custom_fields[] = [
                    "name" => $jsst_slug,
                    "type" => $jsst_fieldtype,
                    "jshd_filedorderingid" => $jsst_latest_record->id,
                    "jshd_filedorderingfield" => $jsst_latest_record->field,
                ];
                $this->sc_ticket_custom_fields_custom[$jsst_custom_field->slug] = $jsst_latest_record->field;
                
                $this->jsst_support_candy_import_count['field']['imported'] += 1;
            } else {
                $this->jsst_support_candy_import_count['field']['failed'] += 1;
                // Optionally log: error_log("Failed to import field: $jsst_slug");
            }
        }

        foreach ($jsst_custom_fields as $jsst_custom_field) {
            $jsst_slug = $jsst_custom_field->slug;
            if (!empty($jsst_ticket_field_options[$jsst_slug]['visibility'])) {
                $jsst_visibility_conditions = json_decode($jsst_ticket_field_options[$jsst_slug]['visibility']);
                $jsst_field = $this->getTicketCustomFieldId($jsst_custom_field->name);
                $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field = '".esc_sql($jsst_field)."' LIMIT 1";
                $jsst_jshd_field = jssupportticket::$_db->get_row($jsst_query);
                if (empty($jsst_jshd_field)) {
                    continue;
                }

                // Build visibility data
                $jsst_visibledata = [
                    "visibleLogic" => [],
                    "visibleParent" => [],
                    "visibleValue" => [],
                    "visibleCondition" => [],
                ];
                if ($jsst_visibility_conditions) {
                    foreach ($jsst_visibility_conditions as $jsst_visibility_condition) {
                        $jsst_visibleLogic = 'AND';
                        foreach ($jsst_visibility_condition as $jsst_groupIndex => $jsst_group) {
                            $jsst_fieldtype = '';
                            if ($jsst_group->slug == 'usergroups' || $jsst_group->slug == 'description' || $jsst_group->slug == 'assigned_agent') {
                                continue;
                            }
                            if ($jsst_group->slug == 'priority') {
                                $jsst_item_key = 'priority';
                                $jsst_value = $this->getTicketPriorityIdBySupportCandy($jsst_group->operand_val_1);
                                $jsst_fieldtype = 'priority';
                            } elseif ($jsst_group->slug == 'category') {
                                $jsst_item_key = 'department';
                                $jsst_value = $this->getTicketDepartmentIdBySupportCandy($jsst_group->operand_val_1);
                                $jsst_fieldtype = 'department';
                            } elseif ($jsst_group->slug == 'subject') {
                                $jsst_item_key = 'subject';
                                $jsst_value = $jsst_group->operand_val_1;
                                $jsst_fieldtype = 'subject';
                            } else {
                                // $jsst_item_key = $jsst_group->slug;
                                $jsst_item_key = $this->sc_ticket_custom_fields_custom[$jsst_group->slug];
                                $jsst_fieldtype = $this->checkTypeOfTheField($jsst_item_key);
                                if ($jsst_fieldtype == 'textarea') {
                                    continue;
                                } elseif ($this->sc_ticket_custom_fields_custom[$jsst_custom_field->slug] == $jsst_item_key) {
                                    continue;
                                }

                                if (in_array(strtolower($jsst_fieldtype), ['multiple', 'checkbox', 'combo', 'radio'])) {
                                    $jsst_field_ids = explode('|', $jsst_group->operand_val_1[0]);

                                    // Sanitize and cast to integers
                                    $jsst_field_ids = array_map('intval', array_filter($jsst_field_ids));

                                    // Check if we have valid IDs
                                    if (!empty($jsst_field_ids)) {
                                        $jsst_placeholders = implode(',', $jsst_field_ids);
                                        $jsst_query = "SELECT name FROM `" . jssupportticket::$_db->prefix . "psmsc_options` WHERE id IN ($jsst_placeholders)";
                                        $jsst_names = jssupportticket::$_db->get_col($jsst_query);

                                        // Combine names into comma-separated string
                                        $jsst_value = !empty($jsst_names) ? implode(', ', $jsst_names) : '';
                                    } else {
                                        $jsst_value = '';
                                    }
                                } else {
                                    $jsst_value = $jsst_group->operand_val_1;
                                }
                            }
                            if ($jsst_custom_field->slug == 'priority') {
                                $jsst_slug = 'priority';
                            } elseif ($jsst_custom_field->slug == 'category') {
                                $jsst_slug = 'department';
                            } elseif ($jsst_custom_field->slug == 'subject') {
                                $jsst_slug = 'subject';
                            } else {
                                $jsst_slug = $this->sc_ticket_custom_fields_custom[$jsst_custom_field->slug];
                            }
                            
                            $jsst_visibledata["visibleParentField"][] = $jsst_slug;
                            $jsst_visibledata["visibleParent"][] = $jsst_item_key;
                            $jsst_visibledata["visibleCondition"][] = $this->mapOperatorToConditionCode($jsst_group->operator, $jsst_fieldtype);
                            $jsst_visibledata["visibleValue"][] = $jsst_value;
                            $jsst_visibledata["visibleLogic"][] = $jsst_visibleLogic;
                            $jsst_visibleLogic = 'OR';
                        }
                        // remove default value in case of visiblity
                        $jsst_jshd_field->defaultvalue = '';

                    }
                }

                $jsst_option_values = [];
                if(isset($jsst_jshd_field->userfieldparams)){
                    $jsst_options = json_decode($jsst_jshd_field->userfieldparams, true);
                    foreach($jsst_options as $jsst_key => $jsst_value){
                        $jsst_option_values[] = $jsst_value;
                    }
                }
                
                // Prepare field data for import

                $jsst_fieldOrderingData = [
                    "id" => $jsst_jshd_field->id,
                    "field" => $jsst_jshd_field->field,
                    "fieldtitle" => $jsst_jshd_field->fieldtitle,
                    "ordering" => $jsst_jshd_field->ordering,
                    "section" => $jsst_jshd_field->section,
                    "placeholder" => $jsst_jshd_field->placeholder,
                    "description" => $jsst_jshd_field->description,
                    "fieldfor" => $jsst_jshd_field->fieldfor,
                    "published" => $jsst_jshd_field->published,
                    "sys" => $jsst_jshd_field->sys,
                    "cannotunpublish" => $jsst_jshd_field->cannotunpublish,
                    "required" => $jsst_jshd_field->required,
                    "size" => $jsst_jshd_field->size,
                    "maxlength" => $jsst_jshd_field->maxlength,
                    "cols" => $jsst_jshd_field->cols,
                    "rows" => $jsst_jshd_field->rows,
                    "isuserfield" => $jsst_jshd_field->isuserfield,
                    "userfieldtype" => $jsst_jshd_field->userfieldtype,
                    "depandant_field" => $jsst_jshd_field->depandant_field,
                    "visible_field" => $jsst_jshd_field->visible_field,
                    "showonlisting" => $jsst_jshd_field->showonlisting,
                    "cannotshowonlisting" => $jsst_jshd_field->cannotshowonlisting,
                    "search_user" => $jsst_jshd_field->search_user,
                    "search_admin" => $jsst_jshd_field->search_admin,
                    "cannotsearch" => $jsst_jshd_field->cannotsearch,
                    "isvisitorpublished" => $jsst_jshd_field->isvisitorpublished,
                    "search_visitor" => $jsst_jshd_field->search_visitor,
                    "multiformid" => $jsst_jshd_field->multiformid,
                    "userfieldparams" => $jsst_jshd_field->userfieldparams,
                    "visibleparams" => $jsst_jshd_field->visibleparams,
                    "readonly" => $jsst_jshd_field->readonly,
                    "adminonly" => $jsst_jshd_field->adminonly,
                    "defaultvalue" => $jsst_jshd_field->defaultvalue,
                    "defaultvalue_select" => $jsst_jshd_field->defaultvalue,
                    "defaultvalue_input" => $jsst_jshd_field->defaultvalue,
                    "values" => $jsst_option_values,
                    "visibleParent" => $jsst_visibledata["visibleParent"],
                    "visibleValue" => $jsst_visibledata["visibleValue"],
                    "visibleCondition" => $jsst_visibledata["visibleCondition"],
                    "visibleLogic" => $jsst_visibledata["visibleLogic"],
                ];

                // Store field in SupportCandy
                $jsst_record_saved = JSSTincluder::getJSModel('fieldordering')->storeUserField($jsst_fieldOrderingData);
            }
        }
    }

    private function mapOperatorToConditionCode($jsst_operator, $jsst_type) {
        $jsst_operator = strtoupper(jssupportticketphplib::JSST_trim($jsst_operator));
        $jsst_isComplex = false;

        if (!empty($jsst_type)) {
            $jsst_complexTypes = ['combo', 'checkbox', 'radio', 'multiple','priority','department'];
            $jsst_isComplex = !in_array($jsst_type, $jsst_complexTypes);
        }

        switch ($jsst_operator) {
            case '=':
            case 'LIKE':
            case 'IN':
                return $jsst_isComplex ? "2" : "1";

            case 'NOT IN':
                return $jsst_isComplex ? "3" : "0";

            default:
                return $jsst_isComplex ? "3" : "0";
        }
    }

    // clean
    private function importSupportCandyAgents() {
        // check if user already processed for import
        $jsst_imported_agents = array();
        $jsst_imported_agent_json = get_option('js_support_ticket_support_candy_data_agents');
        if(!empty($jsst_imported_agents_json)){
            $jsst_imported_agents = json_decode($jsst_imported_agents_json,true);
        }
        $jsst_query = "SELECT agent.*
                    FROM `" . jssupportticket::$_db->prefix . "psmsc_agents` AS agent;";
        $jsst_agents = jssupportticket::$_db->get_results($jsst_query);
        $jsst_total_agents = count($jsst_agents);

        if($jsst_agents){
            foreach($jsst_agents AS $jsst_agent){
                // Failed if addon not installed
                if (!in_array('agent', jssupportticket::$_active_addons) ) {
                    $this->jsst_support_candy_import_count['agent']['failed']++;
                    continue;
                }
                $jsst_wpuid = (int) $jsst_agent->user;
                // Skip if already imported
                if (in_array($jsst_wpuid, $jsst_imported_agents, true)) {
                    $this->jsst_support_candy_import_count['agent']['skipped']++;
                    continue;
                }
                $jsst_name = $jsst_agent->name;

                $jsst_query = "
                    SELECT user.*
                        FROM `" . jssupportticket::$_db->prefix . "users` AS user
                        WHERE user.id = " . $jsst_wpuid;
                $jsst_wpuser = jssupportticket::$_db->get_row($jsst_query);

                if(!$jsst_wpuser){
                    $this->jsst_support_candy_import_count['agent']['failed'] += 1;
                    continue;
                }
                $jsst_js_user = JSSTincluder::getObjectClass('user')->getjssupportticketuidbyuserid($jsst_wpuid);
                if (!empty($jsst_js_user) && isset($jsst_js_user[0]->id)) {
                    $jsst_js_uid = (int)$jsst_js_user[0]->id;
                } else {
                    $this->jsst_support_candy_import_count['agent']['failed']++;
                    continue;
                }

                $jsst_query = "
                    SELECT staff.*
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff
                            WHERE staff.uid = " . $jsst_js_uid;
                $jsst_staff = jssupportticket::$_db->get_row($jsst_query);

                if (!$jsst_staff) {
                    
                    $jsst_timestamp = date_i18n('Y-m-d H:i:s');

                    $jsst_data = [
                        'id'           => '',
                        'uid'          => $jsst_js_uid,
                        'groupid'      => '',
                        'roleid'       => $this->getAgentRoleIdBySupportCandy($jsst_agent->role),
                        'departmentid' => '',
                        'firstname'    => $jsst_name,
                        'lastname'     => '',
                        'username'     => $jsst_wpuser->user_login,
                        'email'        => $jsst_wpuser->user_email,
                        'signature'    => '',
                        'isadmin'      => '',
                        'status'       => $jsst_agent->is_active,
                        'updated'      => $jsst_timestamp,
                        'created'      => $jsst_timestamp
                    ];

                    $jsst_saved = JSSTincluder::getJSModel('agent')->storeStaff($jsst_data);

                    $this->jsst_support_candy_import_count['agent']['imported'] += 1;
                    $this->jsst_support_candy_agent_ids[] = $jsst_wpuid;
                } else {
                    $this->jsst_support_candy_import_count['agent']['skipped'] += 1;
                }
            }
            // Save list of imported agent IDs
            if (!empty($this->jsst_support_candy_agent_ids)) {
                update_option('js_support_ticket_support_candy_data_agents', wp_json_encode(array_unique(array_merge($jsst_imported_agents, $this->jsst_support_candy_agent_ids))));
            }
        }
    }

    private function importSupportCandyAgentsRoles() {
        // check if role already processed for import
        $jsst_imported_agent_roles = array();
        $jsst_imported_agent_role_json = get_option('js_support_ticket_support_candy_data_agent_roles');
        if(!empty($jsst_imported_agent_roles_json)){
            $jsst_imported_agent_roles = json_decode($jsst_imported_agent_roles_json,true);
        }

        $jsst_wpsc_agent_roles = get_option('wpsc-agent-roles', []);

        // Mapping role labels to permission keys
        $jsst_permissionMap = [
            'View Credentials'         => ['view-pc-unassigned', 'view-pc-assigned-me', 'view-pc-assigned-others'],
            'Edit Credentials'         => ['modify-pc-unassigned', 'modify-pc-assigned-me', 'modify-pc-assigned-others'],
            'Delete Credentials'       => ['delete-pc-unassigned', 'delete-pc-assigned-me', 'delete-pc-assigned-others'],
            'View Ticket'              => ['view-unassigned', 'view-assigned-me', 'view-assigned-others'],
            'Reply Ticket'             => ['reply-unassigned', 'reply-assigned-me', 'reply-assigned-others'],
            'Post Internal Note'       => ['pn-unassigned', 'pn-assigned-me', 'pn-assigned-others'],
            'Assign Ticket To Agent'   => ['aa-unassigned', 'aa-assigned-me', 'aa-assigned-others'],
            'Change Ticket Status'     => ['cs-unassigned', 'cs-assigned-me', 'cs-assigned-others'],
            'Delete Ticket'            => ['dtt-unassigned', 'dtt-assigned-me', 'dtt-assigned-others'],
            'Add Ticket'               => ['create-as'],
            'View Agent Reports'       => ['view-reports'],
            'View Department Reports'  => ['view-reports'],
            'Edit Own Time'            => ['modify-timer-log']
        ];

        // Pre-fetch permission IDs for all labels
        $jsst_permissionIds = [];
        foreach ($jsst_permissionMap as $jsst_label => $_) {
            if (in_array('agent', jssupportticket::$_active_addons) ) {
                $jsst_escapedLabel = esc_sql($jsst_label);
                $jsst_sql = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_permissions` WHERE permission = '{$jsst_escapedLabel}' LIMIT 1";
                $jsst_permissionIds[$jsst_label] = (int) jssupportticket::$_db->get_var($jsst_sql);
            }
        }

        foreach ($jsst_wpsc_agent_roles as $jsst_role) {
            // Failed if addon not installed
            if (!in_array('agent', jssupportticket::$_active_addons) ) {
                $this->jsst_support_candy_import_count['agent_role']['failed']++;
                continue;
            }
            // Skip if already imported
            if (in_array($jsst_role['label'], $jsst_imported_agent_roles, true)) {
                $this->jsst_support_candy_import_count['agent_role']['skipped']++;
                continue;
            }
            $jsst_query = "SELECT count(id)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_roles` WHERE name ='".esc_sql($jsst_role['label'])."'";
            $jsst_agent_role = jssupportticket::$_db->get_var($jsst_query);

            if($jsst_agent_role == 0){
                $jsst_output = [];
                $jsst_caps = $jsst_role['caps'] ?? [];

                foreach ($jsst_permissionMap as $jsst_label => $jsst_permissions) {
                    foreach ($jsst_permissions as $jsst_perm) {
                        if (!empty($jsst_caps[$jsst_perm])) {
                            // Assign permission ID for this label if exists
                            if (!empty($jsst_permissionIds[$jsst_label])) {
                                $jsst_output[$jsst_label] = $jsst_permissionIds[$jsst_label];
                            }
                            break; // Stop checking other permissions for this label
                        }
                    }
                }

                $jsst_data = [
                    'name'          => $jsst_role['label'],
                    'roleperdata'   => $jsst_output,
                    'id'            => '',
                    'created'       => '',
                    'updated'       => '',
                    'action'        => 'role_saverole',
                    'form_request'  => 'jssupportticket',
                    'save'          => 'Save Role',
                ];

                // save role and role permissions
                JSSTincluder::getJSModel('role')->storeRole($jsst_data);
                $this->jsst_support_candy_import_count['agent_role']['imported'] += 1;
                $this->jsst_support_candy_agent_role_ids[] = $jsst_role['label'];
            } else {
                $this->jsst_support_candy_import_count['agent_role']['skipped'] += 1;
            }
        }
        // Save list of imported agent_role IDs
        if (!empty($this->jsst_support_candy_agent_role_ids)) {
            update_option('js_support_ticket_support_candy_data_agent_roles', wp_json_encode(array_unique(array_merge($jsst_imported_agent_roles, $this->jsst_support_candy_agent_role_ids))));
        }

    }

    private function importSupportCandyDepartments() {
        // check if department already processed for import
        $jsst_imported_departments = array();
        $jsst_imported_departments_json = get_option('js_support_ticket_support_candy_data_departments');
        if(!empty($jsst_imported_departments_json)){
            $jsst_imported_departments = json_decode($jsst_imported_departments_json,true);
        }
        $jsst_query = "SELECT category.* FROM `" . jssupportticket::$_db->prefix . "psmsc_categories` AS category;";
        $jsst_categories = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_categories)) return;

        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(dept.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS dept
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);
        $jsst_now = date_i18n('Y-m-d H:i:s');

        foreach ($jsst_categories as $jsst_category) {
            // Skip if already imported
            if (in_array($jsst_category->id, $jsst_imported_departments, true)) {
                $this->jsst_support_candy_import_count['department']['skipped']++;
                continue;
            }
            $jsst_name = jssupportticketphplib::JSST_trim($jsst_category->name);
            $jsst_lower_name = jssupportticketphplib::JSST_strtolower($jsst_name);

            // Check if department already exists
            $jsst_check_query = "
                SELECT department.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                WHERE LOWER(department.departmentname) = '".esc_sql($jsst_name)."'
            ";
            $jsst_existing = jssupportticket::$_db->get_row($jsst_check_query);

            if (!$jsst_existing) {
                $jsst_row = JSSTincluder::getJSTable('departments');

                $jsst_data = [
                    'id'              => '',
                    'emailid'         => '1',
                    'departmentname'  => $jsst_name,
                    'ordering'        => $jsst_ordering,
                    'status'          => '1',
                    'isdefault'       => '0',
                    'ispublic'        => '1',
                    'updated'         => $jsst_now,
                    'created'         => $jsst_now
                ];

                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
                $jsst_row->bind($jsst_data);

                if (!$jsst_row->store()) {
                    $this->jsst_support_candy_import_count['department']['failed'] += 1;
                } else {
                    $this->jsst_support_candy_department_ids[] = $jsst_category->id;
                    $this->jsst_support_candy_import_count['department']['imported'] += 1;
                }

                $jsst_ordering++;
            } else {
                $this->jsst_support_candy_import_count['department']['skipped'] += 1;
            }
        }
        // Save list of imported department IDs
        if (!empty($this->jsst_support_candy_department_ids)) {
            update_option('js_support_ticket_support_candy_data_departments', wp_json_encode(array_unique(array_merge($jsst_imported_departments, $this->jsst_support_candy_department_ids))));
        }
    }

    private function importSupportCandyPriorities() {
        // check if priority already processed for import
        $jsst_imported_priorities = array();
        $jsst_imported_priorities_json = get_option('js_support_ticket_support_candy_data_priorities');
        if(!empty($jsst_imported_priorities_json)){
            $jsst_imported_priorities = json_decode($jsst_imported_priorities_json,true);
        }

        $jsst_query = "SELECT priority.* FROM `" . jssupportticket::$_db->prefix . "psmsc_priorities` AS priority;";
        $jsst_priorities = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_priorities)) return;

        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(priority.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);

        foreach ($jsst_priorities as $jsst_priority) {
            // Skip if already imported
            if (in_array($jsst_priority->id, $jsst_imported_priorities, true)) {
                $this->jsst_support_candy_import_count['priority']['skipped']++;
                continue;
            }

            $jsst_name = jssupportticketphplib::JSST_trim(jssupportticketphplib::JSST_strtolower($jsst_priority->name));

            // Check if this priority already exists in JS Support Ticket
            $jsst_check_query = "
                SELECT priority.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority
                WHERE LOWER(priority.priority) = '" . esc_sql($jsst_name) . "'
                LIMIT 1
            ";
            $jsst_jshd_priority = jssupportticket::$_db->get_row($jsst_check_query);

            if (!$jsst_jshd_priority) {
                $jsst_row = JSSTincluder::getJSTable('priorities');

                $jsst_data = [
                    'id'               => '',
                    'priority'         => $jsst_priority->name,
                    'prioritycolour'   => $jsst_priority->color,
                    'priorityurgency'  => '',
                    'overduetypeid'    => 1,
                    'overdueinterval'  => 7,
                    'ordering'         => $jsst_ordering,
                    'status'           => '1',
                    'isdefault'        => '0',
                    'ispublic'         => '1'
                ];

                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
                $jsst_row->bind($jsst_data);

                if (!$jsst_row->store()) {
                    $this->jsst_support_candy_import_count['priority']['failed'] += 1;
                } else {
                    $this->jsst_support_candy_priority_ids[] = $jsst_priority->id;
                    $this->jsst_support_candy_import_count['priority']['imported'] += 1;
                }

                $jsst_ordering++;
            } else {
                $this->jsst_support_candy_import_count['priority']['skipped'] += 1;
            }
        }
        // Save list of imported priority IDs
        if (!empty($this->jsst_support_candy_priority_ids)) {
            update_option('js_support_ticket_support_candy_data_priorities', wp_json_encode(array_unique(array_merge($jsst_imported_priorities, $this->jsst_support_candy_priority_ids))));
        }
    }

    private function importSupportCandyPremades() {
        // check if premade already processed for import
        $jsst_imported_premades = array();
        $jsst_imported_premades_json = get_option('js_support_ticket_support_candy_data_premades');
        if(!empty($jsst_imported_premades_json)){
            $jsst_imported_premades = json_decode($jsst_imported_premades_json,true);
        }
        $jsst_query = "
            SELECT canned_reply.*
            FROM `" . jssupportticket::$_db->prefix . "psmsc_canned_reply` AS canned_reply
        ";
        $jsst_canned_replies = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_canned_replies)) return;

        foreach ($jsst_canned_replies as $jsst_canned_reply) {
            $jsst_title = jssupportticketphplib::JSST_trim(jssupportticketphplib::JSST_strtolower($jsst_canned_reply->title));
            // Failed if addon not installed
            if (!in_array('cannedresponses', jssupportticket::$_active_addons) ) {
                $this->jsst_support_candy_import_count['canned response']['failed']++;
                continue;
            }
            // Skip if already imported
            if (in_array($jsst_canned_reply->id, $jsst_imported_premades, true)) {
                $this->jsst_support_candy_import_count['canned response']['skipped']++;
                continue;
            }
            // Check if this priority already exists in JS Support Ticket
            $jsst_check_query = "
                SELECT premade.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` AS premade
                WHERE LOWER(premade.title) = '" . esc_sql($jsst_title) . "'
                LIMIT 1
            ";
            $jsst_jshd_canned_reply = jssupportticket::$_db->get_row($jsst_check_query);

            if (!$jsst_jshd_canned_reply) {

                $jsst_departmentid = '';

                // Try to match category to department
                if (!empty($jsst_canned_reply->categories)) {
                    $jsst_category_query = "
                        SELECT category.name
                        FROM `" . jssupportticket::$_db->prefix . "psmsc_categories` AS category
                        WHERE category.id = " . esc_sql($jsst_canned_reply->categories) . "
                    ";
                    $jsst_category = jssupportticket::$_db->get_row($jsst_category_query);

                    if ($jsst_category) {
                        $jsst_department_query = "
                            SELECT department.id
                            FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                            WHERE LOWER(department.departmentname) = '" . jssupportticketphplib::JSST_strtolower(esc_sql($jsst_category->name)) . "'
                            LIMIT 1
                        ";
                        $jsst_department = jssupportticket::$_db->get_row($jsst_department_query);
                        if ($jsst_department) {
                            $jsst_departmentid = $jsst_department->id;
                        }
                    }
                }

                // If no matching department found, use default
                if (empty($jsst_departmentid)) {
                    $jsst_departmentid = JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
                }

                // Prepare canned response data
                $jsst_row = JSSTincluder::getJSTable('cannedresponses');
                $jsst_updated = date_i18n('Y-m-d H:i:s');

                $jsst_data = [
                    'id'          => '',
                    'departmentid'=> $jsst_departmentid,
                    'title'       => $jsst_canned_reply->title,
                    'answer'      => $jsst_canned_reply->body,
                    'status'      => '1',
                    'updated'     => $jsst_updated,
                    'created'     => $jsst_canned_reply->date_created
                ];

                $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);
                $jsst_data['answer'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($jsst_data['answer']);
                $jsst_data = JSSTincluder::getJSModel('jssupportticket')->stripslashesFull($jsst_data);

                $jsst_row->bind($jsst_data);
                if (!$jsst_row->store()) {
                    $this->jsst_support_candy_import_count['canned response']['failed'] += 1;
                } else {
                    $this->jsst_support_candy_premade_ids[] = $jsst_canned_reply->id;
                    $this->jsst_support_candy_import_count['canned response']['imported'] += 1;
                }
            } else {
                $this->jsst_support_candy_import_count['canned response']['skipped'] += 1;
            }
        }

        // Save list of imported premade IDs
        if (!empty($this->jsst_support_candy_premade_ids)) {
            update_option('js_support_ticket_support_candy_data_premades', wp_json_encode(array_unique(array_merge($jsst_imported_premades, $this->jsst_support_candy_premade_ids))));
        }
    }

    private function importSupportCandyStatus() {
        // Load previously imported statuses
        $jsst_imported_statuses = [];
        $jsst_imported_statuses_json = get_option('js_support_ticket_support_candy_data_statuses');
        if (!empty($jsst_imported_statuses_json)) {
            $jsst_imported_statuses = json_decode($jsst_imported_statuses_json, true);
        }

        // Get SupportCandy statuses (excluding system/default ones)
        $jsst_query = "
            SELECT status.*
            FROM `" . jssupportticket::$_db->prefix . "psmsc_statuses` AS status
            WHERE status.id > 4
        ";
        $jsst_statuses = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_statuses)) return;

        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(status.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);

        // Build array of existing JS statuses (cleaned)
        $jsst_query = "
            SELECT status.status
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status
        ";
        $jsst_jsstatuses = jssupportticket::$_db->get_results($jsst_query);
        $jsst_existing_status_names = array_map(function($jsst_status) {
            return $this->cleanStringForCompare($jsst_status->status);
        }, $jsst_jsstatuses);

        foreach ($jsst_statuses as $jsst_status) {
            $jsst_name = $jsst_status->name;
            $jsst_compare_name = $this->cleanStringForCompare($jsst_name);

            // Skip if name already exists
            if (in_array($jsst_compare_name, $jsst_existing_status_names)) {
                $this->jsst_support_candy_import_count['status']['skipped'] += 1;
                continue;
            }

            // Skip if already imported
            if (in_array($jsst_status->id, $jsst_imported_statuses)) {
                $this->jsst_support_candy_import_count['status']['skipped'] += 1;
                continue;
            }

            // Prepare new status data
            $jsst_row = JSSTincluder::getJSTable('statuses');
            $jsst_data = [
                'id'             => '',
                'status'         => $jsst_name,
                'statuscolour'   => $jsst_status->color,
                'statusbgcolour' => $jsst_status->bg_color,
                'sys'            => '0',
                'ordering'       => $jsst_ordering
            ];

            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
            $jsst_row->bind($jsst_data);

            if (!$jsst_row->store()) {
                $this->jsst_support_candy_import_count['status']['failed'] += 1;
            } else {
                $this->jsst_support_candy_status_ids[] = $jsst_status->id;
                $this->jsst_support_candy_import_count['status']['imported'] += 1;
                $jsst_ordering++;
            }
        }

        // Save updated list of imported statuses
        if (!empty($this->jsst_support_candy_status_ids)) {
            update_option('js_support_ticket_support_candy_data_statuses', wp_json_encode($this->jsst_support_candy_status_ids));
        }
    }

    private function cleanStringForCompare($jsst_string) {
        if (!is_string($jsst_string) || $jsst_string === '') {
            return $jsst_string;
        }

        // Remove spaces, dashes, and underscores
        $jsst_string = jssupportticketphplib::JSST_str_replace([' ', '-', '_'], '', $jsst_string);

        // Convert to lowercase
        return jssupportticketphplib::JSST_strtolower($jsst_string);
    }

    function getSupportCandyDataStats($jsst_count_for) {
        // Only support SupportCandy (count_for = 1)
        if ($jsst_count_for != 1) return;

        // Check if SupportCandy is active
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (!is_plugin_active('supportcandy/supportcandy.php')) {
            return new WP_Error('jsst_inactive', 'SupportCandy is not active.');
        }

        $jsst_entity_counts = [];

        // Users
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_customers'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "psmsc_customers`";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['user'] = $jsst_count;
        }

        // Agent Roles
        $jsst_agent_roles = get_option('wpsc-agent-roles', []);
        if (!empty($jsst_agent_roles)) {
            $jsst_entity_counts['agent role'] = count($jsst_agent_roles);
        }

        // Agents
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_agents'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "psmsc_agents`";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['agent'] = $jsst_count;
        }

        // Departments
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_categories'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "psmsc_categories`";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['department'] = $jsst_count;
        }

        // Priorities
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_priorities'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "psmsc_priorities`";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['priority'] = $jsst_count;
        }

        // Canned Responses
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_canned_reply'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "psmsc_canned_reply`";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['canned response'] = $jsst_count;
        }

        // Statuses
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_statuses'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "psmsc_statuses` WHERE id > 4";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['status'] = $jsst_count;
        }

        // Custom Ticket Fields
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_custom_fields'")) {
            $jsst_query = "SELECT COUNT(*) 
                      FROM `" . jssupportticket::$_db->prefix . "psmsc_custom_fields`
                      WHERE `slug` LIKE 'cust_%' AND `type` LIKE 'cf_%' AND `field` = 'ticket'";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['field'] = $jsst_count;
        }

        // Tickets with type 'report'
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "psmsc_tickets'")) {
            $jsst_query = "SELECT COUNT(DISTINCT t.id)
                      FROM `" . jssupportticket::$_db->prefix . "psmsc_tickets` AS t
                      INNER JOIN `" . jssupportticket::$_db->prefix . "psmsc_threads` AS r ON r.ticket = t.id
                      WHERE r.type = 'report'  AND t.is_active != 0";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['ticket'] = $jsst_count;
        }

        jssupportticket::$jsst_data['entity_counts'] = $jsst_entity_counts;
    }


    //================
    // --------------
    //////////////////
    // Awesome Support
    //////////////////
    // ---------------
    //================

    function importAwesomeSupportData() {
        // Only for development – remove before pushing to production
        // $this->deletesupportcandyimporteddata();

        // Reset previously imported IDs from options
        update_option('js_support_ticket_awesome_support_data_statuses', '');
        update_option('js_support_ticket_awesome_support_data_priorities', '');
        update_option('js_support_ticket_awesome_support_data_users', '');
        update_option('js_support_ticket_awesome_support_data_departments', '');
        update_option('js_support_ticket_awesome_support_data_premades', '');
        update_option('js_support_ticket_awesome_support_data_agents', '');
        update_option('js_support_ticket_awesome_support_data_tickets', '');
        update_option('js_support_ticket_awesome_support_data_faqs', '');
        update_option('js_support_ticket_awesome_support_data_products', '');
        
        // Prepare filesystem and create necessary directories
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        $jsst_filesystem = new WP_Filesystem_Direct(true);
        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = $jsst_upload_path . "/" . $jsst_datadirectory;

        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }
        $jsst_path .= '/attachmentdata';
        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }
        $jsst_path .= '/ticket';
        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }

        $this->importAwesomeSupportUsers();
        $this->importAwesomeSupportAgents();
        if (taxonomy_exists('department')) {
            $this->importAwesomeSupportDepartments();
        }
        $this->importAwesomeSupportPriorities();
        $this->importAwesomeSupportPremades();
        $this->importAwesomeSupportStatus();
        $this->importAwesomeSupportProducts();
        $this->importAwesomeSupportTicketFields();
        $this->getAwesomeSupportTickets($this->jsst_as_ticket_custom_fields);
        if(in_array('faq', jssupportticket::$_active_addons)){
            $this->importAwesomeSupportFaqs();
        }

        update_option('jsst_import_counts',$this->jsst_awesome_support_import_count);

        return;
    }

    function getAwesomeSupportStats($jsst_count_for) {
        // Only support Awesome Support (count_for = 2)
        if ($jsst_count_for != 2) return;

        // Check if Awesome Support is active
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (!is_plugin_active('awesome-support/awesome-support.php')) {
            return new WP_Error('jsst_inactive', 'Awesome Support is not active.');
        }

        $jsst_entity_counts = [];

        // Users
        $jsst_missingUser = 0;
        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "users`";
        $jsst_users = jssupportticket::$_db->get_var($jsst_query);
        if ($jsst_users > 0) $jsst_entity_counts['user'] = $jsst_users;

        // Agents
        $jsst_agents = get_users([
            'role' => 'wpas_agent',
        ]);
        $jsst_count = count($jsst_agents);
        if ($jsst_count > 0) $jsst_entity_counts['agent'] = $jsst_count;

        // Departments
        $jsst_departments = get_terms([
            'taxonomy'   => 'department',
            'hide_empty' => false,
            'fields'     => 'ids'
        ]);
        $jsst_count = is_array($jsst_departments) ? count($jsst_departments) : 0;
        if ($jsst_count > 0) $jsst_entity_counts['department'] = $jsst_count;

        // Priorities
        $jsst_priorities = get_terms([
            'taxonomy'   => 'ticket_priority',
            'hide_empty' => false,
            'fields'     => 'ids'
        ]);
        $jsst_count = is_array($jsst_priorities) ? count($jsst_priorities) : 0;
        if ($jsst_count > 0) $jsst_entity_counts['priority'] = $jsst_count;

        // Canned Responses
        $jsst_count = post_type_exists( 'canned-response' ) ? $this->getPostConutByType( 'canned-response' ) : 0;
        if ($jsst_count > 0) $jsst_entity_counts['canned response'] = $jsst_count;

        // Statuses
        $jsst_count = post_type_exists( 'wpass_status' ) ? $this->getPostConutByType( 'wpass_status' ) : 0;
        if ($jsst_count > 0) $jsst_entity_counts['status'] = $jsst_count;

        // Products
        $jsst_products = get_terms([
            'taxonomy'   => 'product',
            'hide_empty' => false,
            'fields'     => 'ids'
        ]);
        $jsst_count = is_array($jsst_products) ? count($jsst_products) : 0;
        if ($jsst_count > 0) $jsst_entity_counts['product'] = $jsst_count;

        // Faqs
        $jsst_count = post_type_exists( 'faq' ) ? $this->getPostConutByType( 'faq' ) : 0;
        if ($jsst_count > 0) $jsst_entity_counts['faq'] = $jsst_count;

        // Custom Ticket Fields
        $jsst_custom_fields = get_option("wpas_custom_fields");

        if (!empty($jsst_custom_fields)) {
            $jsst_count = is_array($jsst_custom_fields) ? count($jsst_custom_fields) : 0;
            if ($jsst_count > 0) $jsst_entity_counts['field'] = $jsst_count;
        }

        // Tickets 
        $jsst_tickets = wpas_get_tickets('any');
        $jsst_count = count($jsst_tickets);

        if ($jsst_count > 0) $jsst_entity_counts['ticket'] = $jsst_count;

        jssupportticket::$jsst_data['entity_counts'] = $jsst_entity_counts;
    }

    // delete data only for development

    function deletesupportcandyimporteddata(){

        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE id > 27;";
        jssupportticket::$_db->query($jsst_query);

        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` SET `visible_field`='' ";
        jssupportticket::$_db->query($jsst_query);

        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`;";
        jssupportticket::$_db->query($jsst_query);

        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_time`;";
        jssupportticket::$_db->query($jsst_query);

        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies`;";
        jssupportticket::$_db->query($jsst_query);
        
        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments`;";
        jssupportticket::$_db->query($jsst_query);
        
        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`;";
        jssupportticket::$_db->query($jsst_query);
        
        if (in_array('agent', jssupportticket::$_active_addons)) {
            $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff`;";
            jssupportticket::$_db->query($jsst_query);
            $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_roles` WHERE id > 1;";
            jssupportticket::$_db->query($jsst_query);
        }
        
        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_users`;";
        jssupportticket::$_db->query($jsst_query);
        
        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade`;";
        jssupportticket::$_db->query($jsst_query);

    }

    private function importAwesomeSupportAgents() {
        // check if user already processed for import
        $jsst_imported_agents = array();
        $jsst_imported_agent_json = get_option('js_support_ticket_awesome_support_data_agents');
        if(!empty($jsst_imported_agents_json)){
            $jsst_imported_agents = json_decode($jsst_imported_agents_json,true);
        }
        $jsst_agents = get_users([
            'role' => 'wpas_agent',
        ]);
        $jsst_total_agents = count($jsst_agents);

        if($jsst_agents){
            if (in_array('agent', jssupportticket::$_active_addons) ) {
                $jsst_roleid = $this->getAgentRoleIdByAwesomeSupport();
            }
            foreach($jsst_agents AS $jsst_agent){
                // Failed if addon not installed
                if (!in_array('agent', jssupportticket::$_active_addons) ) {
                    $this->jsst_awesome_support_import_count['agent']['failed']++;
                    continue;
                }
                $jsst_wpuid = (int) $jsst_agent->data->ID;
                // Skip if already imported
                if (in_array($jsst_wpuid, $jsst_imported_agents, true)) {
                    $this->jsst_awesome_support_import_count['agent']['skipped']++;
                    continue;
                }
                $jsst_name = $jsst_agent->data->display_name;

                $jsst_query = "
                    SELECT user.*
                        FROM `" . jssupportticket::$_db->prefix . "users` AS user
                        WHERE user.id = " . $jsst_wpuid;
                $jsst_wpuser = jssupportticket::$_db->get_row($jsst_query);

                if(!$jsst_wpuser){
                    $this->jsst_awesome_support_import_count['agent']['failed'] += 1;
                    continue;
                }
                $jsst_js_user = JSSTincluder::getObjectClass('user')->getjssupportticketuidbyuserid($jsst_wpuid);
                if (!empty($jsst_js_user) && isset($jsst_js_user[0]->id)) {
                    $jsst_js_uid = (int)$jsst_js_user[0]->id;
                } else {
                    $this->jsst_awesome_support_import_count['agent']['failed']++;
                    continue;
                }

                $jsst_query = "
                    SELECT staff.*
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff
                            WHERE staff.uid = " . $jsst_js_uid;
                $jsst_staff = jssupportticket::$_db->get_row($jsst_query);

                if (!$jsst_staff) {
                    
                    $jsst_timestamp = date_i18n('Y-m-d H:i:s');

                    $jsst_data = [
                        'id'           => '',
                        'uid'          => $jsst_js_uid,
                        'groupid'      => '',
                        'roleid'       =>  $jsst_roleid,
                        'departmentid' => '',
                        'firstname'    => $jsst_name,
                        'lastname'     => '',
                        'username'     => $jsst_wpuser->user_login,
                        'email'        => $jsst_wpuser->user_email,
                        'signature'    => '',
                        'isadmin'      => '',
                        'status'       => 1,
                        'updated'      => $jsst_timestamp,
                        'created'      => $jsst_timestamp
                    ];

                    $jsst_saved = JSSTincluder::getJSModel('agent')->storeStaff($jsst_data);

                    $this->jsst_awesome_support_import_count['agent']['imported'] += 1;
                    $this->jsst_awesome_support_agent_ids[] = $jsst_wpuid;
                } else {
                    $this->jsst_awesome_support_import_count['agent']['skipped'] += 1;
                }
            }
            // Save list of imported agent IDs
            if (!empty($this->jsst_awesome_support_agent_ids)) {
                update_option('js_support_ticket_awesome_support_data_agents', wp_json_encode(array_unique(array_merge($jsst_imported_agents, $this->jsst_awesome_support_agent_ids))));
            }
        }
    }

    private function getAgentRoleIdByAwesomeSupport() {

        $jsst_data['id'] = '';
        $jsst_data['name'] = 'AS Support Agent';
        $jsst_data['status'] = 1;
        $jsst_data['created'] = date_i18n('Y-m-d H:i:s');

        $jsst_row = JSSTincluder::getJSTable('acl_roles');
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }
        if (empty($jsst_error)) {
            return $jsst_row->id;
        }

        return null;
    }

    private function importAwesomeSupportUsers() {
        // check if user already processed for import
        $jsst_imported_users = array();
        $jsst_imported_users_json = get_option('js_support_ticket_awesome_support_data_users');
        if(!empty($jsst_imported_users_json)){
            $jsst_imported_users = json_decode($jsst_imported_users_json,true);
        }

        // Fetch all customers
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "users`";
        $jsst_users = jssupportticket::$_db->get_results($jsst_query);
        $jsst_wpUsers = array();
        $jsst_jsstUsers = array();
        foreach ($jsst_users as $jsst_key => $jsst_user) {
            $jsst_wpUsers[] = $jsst_user->id;
        }
        $jsst_query = " SELECT wpuid FROM `" . jssupportticket::$_db->prefix . "js_ticket_users`";
        $jsst_users = jssupportticket::$_db->get_results($jsst_query);
        foreach ($jsst_users as $jsst_key => $jsst_user) {
            $jsst_jsstUsers[] = $jsst_user->wpuid;
        }

        $jsst_missingUsers = array_diff($jsst_wpUsers,$jsst_jsstUsers);

        if (empty($jsst_missingUsers)) return;

        foreach ($jsst_missingUsers as $jsst_missingUser) {
            $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "users` WHERE id = " . esc_sql($jsst_missingUser);
            $jsst_customer = jssupportticket::$_db->get_row($jsst_query);

            $jsst_customer_id = intval($jsst_customer->ID);
            $jsst_wpuid       = intval($jsst_customer->ID);
            $jsst_name        = sanitize_text_field($jsst_customer->display_name ?? '');
            $jsst_email       = sanitize_email($jsst_customer->user_email ?? '');

            // Skip if already imported
            if (in_array($jsst_customer_id, $jsst_imported_users, true)) {
                $this->jsst_awesome_support_import_count['user']['skipped']++;
                continue;
            }   

            // Prepare data for new user
            $jsst_row = JSSTincluder::getJSTable('users');
            $jsst_data = [
                'id'            => '',
                'wpuid'         => $jsst_wpuid,
                'name'          => $jsst_name,
                'display_name'  => '',
                'user_email'    => $jsst_email,
                'status'        => 1,
                'issocial'      => 0,
                'socialid'      => null,
                'autogenerated' => 0,
            ];

            // Attempt to save the new user
            $jsst_row->bind($jsst_data);
            if (!$jsst_row->store()) {
                $this->jsst_awesome_support_import_count['user']['failed']++;
                continue;
            }

            // Store successful import info
            $this->awesome_support_users_array[$jsst_customer_id] = $jsst_row->wpuid;
            $this->jsst_awesome_support_user_ids[] = $jsst_customer_id;
            $this->jsst_awesome_support_import_count['user']['imported']++;
        }

        // Save list of imported user IDs
        if (!empty($this->jsst_awesome_support_user_ids)) {
            update_option('js_support_ticket_awesome_support_data_users', wp_json_encode(array_unique(array_merge($jsst_imported_users, $this->jsst_awesome_support_user_ids))));
        }
    }

    private function importAwesomeSupportDepartments() {
        // check if department already processed for import
        $jsst_imported_departments = array();
        $jsst_imported_departments_json = get_option('js_support_ticket_awesome_support_data_departments');
        if(!empty($jsst_imported_departments_json)){
            $jsst_imported_departments = json_decode($jsst_imported_departments_json,true);
        }

        if (!taxonomy_exists('department')) {
            return;
        }
        $jsst_departments = get_terms([
            'taxonomy'   => 'department',
            'hide_empty' => false,
        ]);

        if (empty($jsst_departments)) return;

        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(dept.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS dept
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);
        $jsst_now = date_i18n('Y-m-d H:i:s');

        foreach($jsst_departments AS $jsst_department){
            // Skip if already imported
            if (in_array($jsst_department->id, $jsst_imported_departments, true)) {
                $this->jsst_awesome_support_import_count['department']['skipped']++;
                continue;
            }

            $jsst_name = jssupportticketphplib::JSST_trim($jsst_department->name);
            $jsst_lower_name = jssupportticketphplib::JSST_strtolower($jsst_name);

            // Check if department already exists
            $jsst_check_query = "
                SELECT department.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                WHERE LOWER(department.departmentname) = '". esc_sql($jsst_name) ."'";
            $jsst_existing = jssupportticket::$_db->get_row($jsst_check_query);

            if (!$jsst_existing) { // not exists
                $jsst_row = JSSTincluder::getJSTable('departments');

                $jsst_updated = date_i18n('Y-m-d H:i:s');
                $jsst_created = date_i18n('Y-m-d H:i:s');

                $jsst_data = [
                    'id'              => '',
                    'emailid'         => '1',
                    'departmentname'  => $jsst_name,
                    'ordering'        => $jsst_ordering,
                    'status'          => '1',
                    'isdefault'       => '0',
                    'ispublic'        => '1',
                    'updated'         => $jsst_now,
                    'created'         => $jsst_now
                ];

                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
                $jsst_row->bind($jsst_data);

                if (!$jsst_row->store()) {
                    $this->jsst_awesome_support_import_count['department']['failed'] += 1;
                } else {
                    $this->jsst_awesome_support_department_ids[] = $jsst_department->id;
                    $this->jsst_awesome_support_import_count['department']['imported'] += 1;
                }

                $jsst_ordering++;
            } else {
                $this->jsst_awesome_support_import_count['department']['skipped'] += 1;
            }
        }
        // Save list of imported department IDs
        if (!empty($this->jsst_awesome_support_department_ids)) {
            update_option('js_support_ticket_awesome_support_data_departments', wp_json_encode(array_unique(array_merge($jsst_imported_departments, $this->jsst_awesome_support_department_ids))));
        }
    }
    
    private function importAwesomeSupportPriorities() {
        // check if priority already processed for import
        $jsst_imported_priorities = array();
        $jsst_imported_priorities_json = get_option('js_support_ticket_awesome_support_data_priorities');
        if(!empty($jsst_imported_priorities_json)){
            $jsst_imported_priorities = json_decode($jsst_imported_priorities_json,true);
        }

        $jsst_priorities = get_terms([
            'taxonomy'   => 'ticket_priority',
            'hide_empty' => false,
        ]);

        if (is_wp_error($jsst_priorities) || empty($jsst_priorities)) return;

        foreach ($jsst_priorities as $jsst_key => $jsst_priority) {
            $jsst_meta = get_term_meta($jsst_priority->term_id);
            $jsst_priorities[$jsst_key]->meta = $jsst_meta;
        }
        
        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(priority.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);

        foreach ($jsst_priorities AS $jsst_priority) {
            // Skip if already imported
            if (in_array($jsst_priority->id, $jsst_imported_priorities, true)) {
                $this->jsst_awesome_support_import_count['priority']['skipped']++;
                continue;
            }

            $jsst_name = jssupportticketphplib::JSST_trim(jssupportticketphplib::JSST_strtolower($jsst_priority->name));

            // Check if this priority already exists in JS Support Ticket
            $jsst_check_query = "
                SELECT priority.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority
                WHERE LOWER(priority.priority) = '" . esc_sql($jsst_name) . "'
                LIMIT 1
            ";
            $jsst_jshd_priority = jssupportticket::$_db->get_row($jsst_check_query);

            if (!$jsst_jshd_priority) {
                $jsst_row = JSSTincluder::getJSTable('priorities');

                $jsst_color1 = "#5e8f5b"; // default color
                if (!empty($jsst_priority->meta['color'][0])) {
                    $jsst_color1 = $jsst_priority->meta['color'][0];
                }
                
                $jsst_data = [
                    'id'               => '',
                    'priority'         => $jsst_priority->name,
                    'prioritycolour'   => $jsst_color1,
                    'priorityurgency'  => '',
                    'overduetypeid'    => 1,
                    'overdueinterval'  => 7,
                    'ordering'         => $jsst_ordering,
                    'status'           => '1',
                    'isdefault'        => '0',
                    'ispublic'         => '1'
                ];

                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
                $jsst_row->bind($jsst_data);

                if (!$jsst_row->store()) {
                    $this->jsst_awesome_support_import_count['priority']['failed'] += 1;
                } else {
                    $this->jsst_awesome_support_priority_ids[] = $jsst_priority->id;
                    $this->jsst_awesome_support_import_count['priority']['imported'] += 1;
                }

                $jsst_ordering++;
            } else {
                $this->jsst_awesome_support_import_count['priority']['skipped'] += 1;
            }
        }
        // Save list of imported priority IDs
        if (!empty($this->jsst_awesome_support_priority_ids)) {
            update_option('js_support_ticket_awesome_support_data_priorities', wp_json_encode(array_unique(array_merge($jsst_imported_priorities, $this->jsst_awesome_support_priority_ids))));
        }
    }

    private function importAwesomeSupportStatus() {
        // Load previously imported statuses
        $jsst_imported_statuses = [];
        $jsst_imported_statuses_json = get_option('js_support_ticket_awesome_support_data_statuses');
        if (!empty($jsst_imported_statuses_json)) {
            $jsst_imported_statuses = json_decode($jsst_imported_statuses_json, true);
        }

        // Get SupportCandy statuses (excluding system/default ones)
        $jsst_statuses = get_posts( [
            'post_type'      => 'wpass_status',
            'post_status'    => 'any', // includes all except 'auto-draft'
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'numberposts'    => -1, // get all
            'exclude'        => get_posts([
                'post_type'   => 'wpass_status',
                'post_status'=> 'auto-draft',
                'fields'      => 'ids',
            ]),
        ] );

        if (empty($jsst_statuses)) return;

        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(status.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);

        // Build array of existing JS statuses (cleaned)
        $jsst_query = "
            SELECT status.status
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status
        ";
        $jsst_jsstatuses = jssupportticket::$_db->get_results($jsst_query);
        $jsst_existing_status_names = array_map(function($jsst_status) {
            return $this->cleanStringForCompare($jsst_status->status);
        }, $jsst_jsstatuses);

        foreach ($jsst_statuses as $jsst_status) {
            $jsst_name = $jsst_status->post_title;
            $jsst_compare_name = $this->cleanStringForCompare($jsst_name);

            // Skip if name already exists
            if (in_array($jsst_compare_name, $jsst_existing_status_names)) {
                $this->jsst_awesome_support_import_count['status']['skipped'] += 1;
                continue;
            }

            // Skip if already imported
            if (in_array($jsst_status->id, $jsst_imported_statuses)) {
                $this->jsst_awesome_support_import_count['status']['skipped'] += 1;
                continue;
            }

            $jsst_post_meta = get_post_meta($jsst_status->ID);
            $jsst_bgcolor = "#5e8f5b"; // default color
            if (!empty($jsst_post_meta['status_color'][0])) {
                $jsst_bgcolor = $jsst_post_meta['status_color'][0];
            }

            // Prepare new status data
            $jsst_row = JSSTincluder::getJSTable('statuses');
            $jsst_data = [
                'id'             => '',
                'status'         => $jsst_name,
                'statuscolour'   => '#FFF',
                'statusbgcolour' => $jsst_bgcolor,
                'sys'            => '0',
                'ordering'       => $jsst_ordering
            ];

            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
            $jsst_row->bind($jsst_data);

            if (!$jsst_row->store()) {
                $this->jsst_awesome_support_import_count['status']['failed'] += 1;
            } else {
                $this->jsst_awesome_support_status_ids[] = $jsst_status->id;
                $this->jsst_awesome_support_import_count['status']['imported'] += 1;
                $jsst_ordering++;
            }
        }

        // Save updated list of imported statuses
        if (!empty($this->jsst_awesome_support_status_ids)) {
            update_option('js_support_ticket_awesome_support_data_statuses', wp_json_encode($this->jsst_awesome_support_status_ids));
        }
    }

    private function importAwesomeSupportPremades() {
        // check if premade already processed for import
        $jsst_imported_premades = array();
        $jsst_imported_premades_json = get_option('js_support_ticket_awesome_support_data_premades');
        if(!empty($jsst_imported_premades_json)){
            $jsst_imported_premades = json_decode($jsst_imported_premades_json,true);
        }

        // Get SupportCandy statuses (excluding system/default ones)
        $jsst_canned_replies = get_posts( [
            'post_type'      => 'canned-response',
            'post_status'    => 'any', // includes all except 'auto-draft'
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'numberposts'    => -1, // get all
            'exclude'        => get_posts([
                'post_type'   => 'canned-response',
                'post_status'=> 'auto-draft',
                'fields'      => 'ids',
            ]),
        ] );

        if (empty($jsst_canned_replies)) return;

        foreach ($jsst_canned_replies as $jsst_canned_reply) {
            $jsst_title = jssupportticketphplib::JSST_trim(jssupportticketphplib::JSST_strtolower($jsst_canned_reply->post_title));
            // Failed if addon not installed
            if (!in_array('cannedresponses', jssupportticket::$_active_addons) ) {
                $this->jsst_awesome_support_import_count['canned response']['failed']++;
                continue;
            }
            // Skip if already imported
            if (in_array($jsst_canned_reply->id, $jsst_imported_premades, true)) {
                $this->jsst_awesome_support_import_count['canned response']['skipped']++;
                continue;
            }
            // Check if this premade already exists in JS Support Ticket
            $jsst_check_query = "
                SELECT premade.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` AS premade
                WHERE LOWER(premade.title) = '" . esc_sql($jsst_title) . "'
                LIMIT 1
            ";
            $jsst_jshd_canned_reply = jssupportticket::$_db->get_row($jsst_check_query);

            if (!$jsst_jshd_canned_reply) {
            
                $jsst_departmentid = JSSTincluder::getJSModel('department')->getDefaultDepartmentID();
                // Prepare canned response data
                $jsst_row = JSSTincluder::getJSTable('cannedresponses');
                $jsst_updated = date_i18n('Y-m-d H:i:s');

                $jsst_data = [
                    'id'          => '',
                    'departmentid'=> $jsst_departmentid,
                    'title'       => $jsst_canned_reply->post_title,
                    'answer'      => $jsst_canned_reply->post_content,
                    'status'      => '1',
                    'updated'     => $jsst_updated,
                    'created'     => $jsst_canned_reply->post_date
                ];

                $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);
                $jsst_data = JSSTincluder::getJSModel('jssupportticket')->stripslashesFull($jsst_data);

                $jsst_row->bind($jsst_data);
                if (!$jsst_row->store()) {
                    $this->jsst_awesome_support_import_count['canned response']['failed'] += 1;
                } else {
                    $this->jsst_awesome_support_premade_ids[] = $jsst_canned_reply->id;
                    $this->jsst_awesome_support_import_count['canned response']['imported'] += 1;
                }
            } else {
                $this->jsst_awesome_support_import_count['canned response']['skipped'] += 1;
            }
        }

        // Save list of imported premade IDs
        if (!empty($this->jsst_awesome_support_premade_ids)) {
            update_option('js_support_ticket_awesome_support_data_premades', wp_json_encode(array_unique(array_merge($jsst_imported_premades, $this->jsst_awesome_support_premade_ids))));
        }
    }

    private function importAwesomeSupportProducts(){
        // check if product already processed for import
        $jsst_imported_products = array();
        $jsst_imported_products_json = get_option('js_support_ticket_awesome_support_data_products');
        if(!empty($jsst_imported_products_json)){
            $jsst_imported_products = json_decode($jsst_imported_products_json,true);
        }

        $jsst_products = get_terms([
            'taxonomy'   => 'product',
            'hide_empty' => false,
        ]);

        if (is_wp_error($jsst_products) || empty($jsst_products)) return;
        
        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(product.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);

        foreach($jsst_products AS $jsst_product){
            // Skip if already imported
            if (in_array($jsst_product->term_id, $jsst_imported_products, true)) {
                $this->jsst_awesome_support_import_count['product']['skipped']++;
                continue;
            }

            $jsst_name = jssupportticketphplib::JSST_trim(jssupportticketphplib::JSST_strtolower($jsst_product->name));

            // Check if this product already exists in JS Support Ticket
            $jsst_check_query = "
                SELECT product.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product
                WHERE LOWER(product.product) = '".esc_sql($jsst_name) ."'
                LIMIT 1
            ";
            $jsst_jshd_product = jssupportticket::$_db->get_row($jsst_check_query);

            if(!$jsst_jshd_product){
                $jsst_row = JSSTincluder::getJSTable('products');

                $jsst_color1 = "#5e8f5b"; // default color
                if (!empty($jsst_product->meta['color'][0])) {
                    $jsst_color1 = $jsst_product->meta['color'][0];
                }
                
                $jsst_data = [
                    'id'               => '',
                    'product'         => $jsst_product->name,
                    'status'           => '1',
                    'ordering'         => $jsst_ordering
                ];

                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
                $jsst_row->bind($jsst_data);

                if (!$jsst_row->store()) {
                    $this->jsst_awesome_support_import_count['product']['failed'] += 1;
                } else {
                    $this->awesome_support_product_ids[] = $jsst_product->term_id;
                    $this->jsst_awesome_support_import_count['product']['imported'] += 1;
                }

                $jsst_ordering++;
            } else {
                $this->jsst_awesome_support_import_count['product']['skipped'] += 1;
            }
        }
        // Save list of imported product IDs
        if (!empty($this->awesome_support_product_ids)) {
            update_option('js_support_ticket_awesome_support_data_products', wp_json_encode(array_unique(array_merge($jsst_imported_products, $this->awesome_support_product_ids))));
        }
    }


    // 
    // ticket
    // 
    function getAwesomeSupportTickets($jsst_as_ticket_custom_fields) {
        // Check if tickets already processed for import
        $jsst_imported_tickets = array();
        $jsst_imported_tickets_json = get_option('js_support_ticket_awesome_support_data_tickets');
        if (!empty($jsst_imported_tickets_json)) {
            $jsst_imported_tickets = json_decode($jsst_imported_tickets_json, true);
        }

        $jsst_tickets = wpas_get_tickets('any');

        $jsst_new_tickets = array();
        foreach($jsst_tickets AS $jsst_ticket){
            // Skip if ticket already imported
            if (!empty($jsst_imported_tickets) && in_array($jsst_ticket->id, $jsst_imported_tickets)) {
                $this->jsst_awesome_support_import_count['ticket']['skipped'] += 1;
                continue;
            }

            // Map custom fields
            $jsst_params = array();
            foreach ($jsst_as_ticket_custom_fields as $jsst_as_ticket_custom_field) {
                $jsst_field_name = $jsst_as_ticket_custom_field["name"];
                $jsst_custom_text = get_post_meta($jsst_ticket->ID, '_wpas_'.$jsst_field_name, true);
                $jsst_vardata = "";
                
                if ($jsst_custom_text) {
                    if ($jsst_as_ticket_custom_field["type"] == "date") {
                        $jsst_vardata = gmdate("Y-m-d", jssupportticketphplib::JSST_strtotime($jsst_custom_text));
                    } else {
                        $jsst_vardata = $jsst_custom_text;
                    }

                    if ($jsst_vardata != '') {
                        if (is_array($jsst_vardata)) {
                            $jsst_vardata = implode(', ', array_filter($jsst_vardata));
                        }
                        $jsst_params[$jsst_as_ticket_custom_field["jshd_filedorderingfield"]] = jssupportticketphplib::JSST_htmlentities($jsst_vardata);
                    }
                }
            }
            $jsst_ticketparams = html_entity_decode(wp_json_encode($jsst_params, JSON_UNESCAPED_UNICODE));
            $jsst_post_meta = get_post_meta($jsst_ticket->ID);
            
            
            $jsst_assign_to = "";
            if(isset($jsst_post_meta["_wpas_assignee"][0])) $jsst_assign_to = $jsst_post_meta["_wpas_assignee"][0];
            
            $jsst_userinfo = $this->getAwesomeSupportTicketCustomerInfo($jsst_ticket->post_author);
            
            $jsst_departmentid = $this->getTicketDepartmentIdByAwesomeSupport($jsst_ticket->ID);
            $jsst_priorityid = $this->getTicketPriorityIdByAwesomeSupport($jsst_ticket->ID);
            $jsst_productid = $this->getTicketProductIdByAwesomeSupport($jsst_ticket->ID);
            $jsst_agentid = $this->getTicketAgentIdByAwesomeSupport($jsst_ticket->ID);

            //get user fields
            $jsst_idresult = JSSTincluder::getJSModel('ticket')->getRandomTicketId();
            $jsst_ticketid = $jsst_idresult['ticketid'];
            $jsst_customticketno = $jsst_idresult['customticketno'];

            $jsst_attachmentdir = JSSTincluder::getJSModel('ticket')->getRandomFolderName();
            $jsst_ticket_status = 1;

            $jsst_custom_statuses = get_posts( [
                'post_type'      => 'wpass_status',
                'post_status'    => 'any', // includes all except 'auto-draft'
                'orderby'        => 'ID',
                'order'          => 'ASC',
                'numberposts'    => -1, // get all
                'exclude'        => get_posts([
                    'post_type'   => 'wpass_status',
                    'post_status'=> 'auto-draft',
                    'fields'      => 'ids',
                ]),
            ] );
            $jsst_is_custom_status = 0;
            foreach ($jsst_custom_statuses as $jsst_custom_statuse) {
                if ($jsst_ticket->post_status == $jsst_custom_statuse->post_name) {
                    $jsst_is_custom_status = 1;
                    continue; // stop cheacking further
                }
            }
            
            if(!empty($jsst_is_custom_status)) {
                $jsst_ticket_status = $this->getTicketStatusIdByAwesomeSupport($jsst_ticket->post_status);
            } else {
                if($jsst_post_meta["_wpas_status"][0] == "open"){
                    if(isset($jsst_post_meta["_wpas_last_reply_date"][0])){
                        $jsst_ticket_status = 1;
                    
                        if($jsst_post_meta["_wpas_is_waiting_client_reply"][0] == "1"){ // 1 means waiting agent reply
                            $jsst_ticket_status = 2;
                        }else{
                            $jsst_ticket_status = 4;
                        }
                    }else{
                        $jsst_ticket_status = 1;
                    }
                }
            }

            $jsst_ticket_closed = "0000-00-00 00:00:00";

            if(isset($jsst_post_meta["_ticket_closed_on"][0])){
                if($jsst_post_meta["_ticket_closed_on"][0] == "closed"){
                    $jsst_ticket_status = 5;
                    if(isset($jsst_post_meta["_ticket_closed_on"][0])) {
                        $jsst_ticket_closed = $jsst_post_meta["_ticket_closed_on"][0];
                    }
                }
            }
            $jsst_lastreply = "0000-00-00 00:00:00";
            if(isset($jsst_post_meta["_wpas_last_reply_date"][0])){
                $jsst_lastreply = $jsst_post_meta["_wpas_last_reply_date"][0];
            }
            
            $jsst_isanswered = 0;
            if($jsst_ticket_status == 4) $jsst_isanswered = 1;
            
            $jsst_ticket_closedby = "";
            // Ticket Default Status
            // 1 -> New Ticket
            // 2 -> Waiting admin/staff reply
            // 3 -> in progress
            // 4 -> waiting for customer reply
            // 5 -> close ticket
    
            $jsst_newTicketData = [
                'id' => "",
                'uid' => $jsst_userinfo["jshd_uid"],
                'ticketid' => $jsst_ticketid,
                'departmentid' => $jsst_departmentid,
                'priorityid' => $jsst_priorityid,
                'productid' => $jsst_productid,
                'staffid' => $jsst_agentid,
                'email' => $jsst_userinfo["customer_email"],
                'name' => $jsst_userinfo["customer_name"],
                'subject' => $jsst_ticket->post_title,
                'message' => $jsst_ticket->post_content,
                'helptopicid' => 0,
                'multiformid' => 1,
                'phone' => "",
                'phoneext' => "",
                'status' => $jsst_ticket_status,
                'isoverdue' => "0",
                'isanswered' => $jsst_isanswered,
                'duedate' => "0000-00-00 00:00:00",
                'reopened' => "0000-00-00 00:00:00",
                'closed' => $jsst_ticket_closed,
                'closedby' => $jsst_ticket_closedby,
                'lastreply' => $jsst_lastreply,
                'created' => $jsst_ticket->post_date,
                'updated' => $jsst_ticket->post_modified,
                'lock' => "0",
                'ticketviaemail' => "0",
                'ticketviaemail_id' => "0",
                'attachmentdir' => $jsst_attachmentdir,
                'feedbackemail' => "0",
                'mergestatus' => "0",
                'mergewith' => "0",
                'mergenote' => "",
                'mergedate' => "0000-00-00 00:00:00",
                'multimergeparams' => "",
                'mergeuid' => "0",
                'params' => $jsst_ticketparams,
                'hash' => "",
                'notificationid' => "0",
                'wcorderid' => "0",
                'wcitemid' => "0",
                'wcproductid' => "0",
                'eddorderid' => "0",
                'eddproductid' => "0",
                'eddlicensekey' => "",
                'envatodata' => "",
                'paidsupportitemid' => "0",
                'customticketno' => $jsst_customticketno
            ];

            $jsst_row = JSSTincluder::getJSTable('tickets');
            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_newTicketData)) $jsst_error = 1;
            if (!$jsst_row->store()) $jsst_error = 1;

            if ($jsst_error == 1) {
                $this->jsst_awesome_support_import_count['ticket']['failed'] += 1;
            } else {
                $this->jsst_awesome_support_ticket_ids[] = $jsst_ticket->id;
                $this->jsst_awesome_support_import_count['ticket']['imported'] += 1;

                $jsst_jshd_ticketid = $jsst_row->id;

                //update hash value against ticket
                $jsst_hash = JSSTincluder::getJSModel('ticket')->generateHash($jsst_jshd_ticketid);
                $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET `hash`='" . esc_sql($jsst_hash) . "' WHERE id=" . esc_sql($jsst_jshd_ticketid);
                jssupportticket::$_db->query($jsst_query);
                
                $this->getAwesomeSupportTicketReplies($jsst_jshd_ticketid, $jsst_ticket->ID, $jsst_attachmentdir);
                $this->getAwesomeSupportTicketAttachments($jsst_jshd_ticketid, "", $jsst_ticket->ID, "", $jsst_attachmentdir);


                if (in_array('privatecredentials', jssupportticket::$_active_addons)) {
                    $this->getAwesomeSupportTicketPrivateCredentials($jsst_jshd_ticketid, $jsst_userinfo["jshd_uid"], $jsst_ticket->ID);
                }

                if (in_array('tickethistory', jssupportticket::$_active_addons)) {
                    $this->getAwesomeSupportTicketActivityLog($jsst_jshd_ticketid, $jsst_ticket->ID);
                }
            }
            
        }
        if (!empty($this->jsst_awesome_support_ticket_ids)) {
            update_option('js_support_ticket_awesome_support_data_tickets', wp_json_encode($this->jsst_awesome_support_ticket_ids));
        }
        
    }

    private function importAwesomeSupportTicketFields() {
        // Get all ticket-related custom fields
        $jsst_custom_fields = get_option("wpas_custom_fields");

        if (!$jsst_custom_fields) return;

        $this->jsst_as_ticket_custom_fields = [];

        foreach ($jsst_custom_fields as $jsst_custom_field) {
            // Map field types
            switch ($jsst_custom_field["field_type"]){
                case "text":
                    $jsst_fieldtype = "text"; break;
                case "url":
                    $jsst_fieldtype = "text"; break;
                case "email":
                    $jsst_fieldtype = "email"; break;
                case "number":
                    $jsst_fieldtype = "text"; break;
                case "date-field":
                    $jsst_fieldtype = "date"; break;
                case "password":
                    $jsst_fieldtype = "text"; break;
                case "upload":
                    $jsst_fieldtype = "file"; break;
                case "select":
                    $jsst_fieldtype = "combo"; break;
                case "radio":
                    $jsst_fieldtype = "radio"; break;
                case "checkbox":
                    $jsst_fieldtype = "checkbox"; break;
                case "textarea":
                    $jsst_fieldtype = "textarea"; break;
                case "wysiwyg":
                    $jsst_fieldtype = "wysiwyg"; break;
                default:
                    $jsst_fieldtype = "text"; break;
            }

            // Load options for select-type fields
            $jsst_option_values = [];
            if(!empty($jsst_custom_field['options'])){ // field in the ticket table
                $jsst_field_options = $jsst_custom_field['options'];
                if ($jsst_field_options) {
                    foreach ($jsst_field_options as $jsst_key => $jsst_field_option) {
                        $jsst_option_values[] = $jsst_key;
                    }
                }
            }

            // Build visibility data
            $jsst_visibledata = [
                "visibleLogic" => [],
                "visibleParent" => [],
                "visibleValue" => [],
                "visibleCondition" => [],
            ];

            $jsst_defaultvalue_input = "";
            $jsst_defaultvalue_select = "";
            if($jsst_fieldtype == "combo" || $jsst_fieldtype == "radio" || $jsst_fieldtype == "checkbox") {
                $jsst_defaultvalue_select = $jsst_custom_field['default'];
            } else {
                $jsst_defaultvalue_input = $jsst_custom_field['default'];
            }

            // Prepare field data for import
            $jsst_fieldOrderingData = [
                "id" => "",
                // "field" => $jsst_slug,
                "field" => $jsst_custom_field['name'],
                "fieldtitle" => $jsst_custom_field['title'],
                "ordering" => "",
                "section" => "10",
                "fieldfor" => "1",
                "published" => "1",
                "sys" => "0",
                "cannotunpublish" => "0",
                "required" => $jsst_custom_field['required'],
                "size" => "100",
                "cols" => "",
                "rows" => "",
                "isuserfield" => "1",
                "userfieldtype" => $jsst_fieldtype,
                "depandant_field" => "",
                "visible_field" => "",
                "showonlisting" => "0",
                "cannotshowonlisting" => "0",
                "search_user" => "0",
                "cannotsearch" => "0",
                "isvisitorpublished" => "1",
                "search_visitor" => "0",
                "userfieldparams" => "",
                "multiformid" => "1",
                "visibleparams" => "",
                "values" => $jsst_option_values,
                "visibleParent" => $jsst_visibledata["visibleParent"],
                "visibleValue" => $jsst_visibledata["visibleValue"],
                "visibleCondition" => $jsst_visibledata["visibleCondition"],
                "visibleLogic" => $jsst_visibledata["visibleLogic"],
                "placeholder" => $jsst_custom_field['placeholder'],
                "description" => $jsst_custom_field['desc'],
                "defaultvalue" => $jsst_custom_field['default'],
                "defaultvalue_select" => $jsst_defaultvalue_select,
                "defaultvalue_input" => $jsst_defaultvalue_input,
                "readonly" => $jsst_custom_field['readonly'],
            ];

            // Store field in SupportCandy
            $jsst_record_saved = JSSTincluder::getJSModel('fieldordering')->storeUserField($jsst_fieldOrderingData);

            if ($jsst_record_saved == 1) {
                $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` ORDER BY id DESC LIMIT 1";
                $jsst_latest_record = jssupportticket::$_db->get_row($jsst_query);

                $this->jsst_as_ticket_custom_fields[] = [
                    "name" => $jsst_custom_field['name'],
                    "type" => $jsst_fieldtype,
                    "jshd_filedorderingid" => $jsst_latest_record->id,
                    "jshd_filedorderingfield" => $jsst_latest_record->field,
                ];
                $this->jsst_awesome_support_import_count['field']['imported'] += 1;
            } else {
                $this->jsst_awesome_support_import_count['field']['failed'] += 1;
            }
        }
    }

    private function getAwesomeSupportTicketReplies($jsst_jshd_ticket_id, $jsst_ast_ticket_id, $jsst_attachmentdir){
        $jsst_query = "SELECT post.*
                    FROM `" . jssupportticket::$_db->prefix . "posts` AS post
                    WHERE post.post_parent = ".$jsst_ast_ticket_id."
                    AND post.post_type = 'ticket_reply'
                    ORDER BY post.id ASC";
                    
        $jsst_posts = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_posts)) return;


        foreach($jsst_posts AS $jsst_post){
            $jsst_userinfo = $this->getAwesomeSupportTicketCustomerInfo($jsst_post->post_author);
            $jsst_uid = $jsst_userinfo["jshd_uid"];
            $jsst_name = $jsst_userinfo["customer_name"];

            $jsst_status = get_post_meta( $jsst_post->ID, "custom_reply_status", true );

            if( $jsst_status == "" || $jsst_status == "public" ){
                $jsst_replyData = [
                    "id" => "",
                    "uid" => $jsst_uid,
                    "ticketid" => $jsst_jshd_ticket_id,
                    "name" => $jsst_name,
                    "message" => $jsst_post->post_content,
                    "staffid" => "",
                    "rating" => "",
                    "status" => "1",
                    "created" => $jsst_post->post_date,
                    "ticketviaemail" => "",
                    "viewed_by" => "",
                    "viewed_on" => ""
                ];
                $jsst_row = JSSTincluder::getJSTable('replies');
                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_replyData);// remove slashes with quotes.
                $jsst_error = 0;
                if (!$jsst_row->bind($jsst_data)) {
                    $jsst_error = 1;
                }
                if (!$jsst_row->store()) {
                    $jsst_error = 1;
                }

                $jsst_jshd_ticket_reply_id = $jsst_row->id;

                if (!empty($jsst_jshd_ticket_reply_id)) {
                    $this->getAwesomeSupportTicketAttachments($jsst_jshd_ticket_id, $jsst_jshd_ticket_reply_id, $jsst_ast_ticket_id, $jsst_post->ID, $jsst_attachmentdir);
                }

                if (in_array('timetracking', jssupportticket::$_active_addons)) {
                    $this->getAwesomeSupportTicketStaffTime($jsst_jshd_ticket_id, $jsst_ast_ticket_id, $jsst_jshd_ticket_reply_id, $jsst_post->ID);
                }
            } else {
                $jsst_filename = $this->getAwesomeSupportNoteAttachments($jsst_jshd_ticket_id, $jsst_post->ID, $jsst_attachmentdir);

                $jsst_replyData = [
                    "id" => "",
                    "ticketid" => $jsst_jshd_ticket_id,
                    "staffid" => $jsst_uid,
                    "title" => jssupportticketphplib::JSST_strip_tags($jsst_post->post_content),
                    "note" => $jsst_post->post_content,
                    "status" => "1",
                    "created" => $jsst_post->post_date,
                    "filename" => $jsst_filename,
                    "filesize" => 5334
                ];
                $jsst_row = JSSTincluder::getJSTable('note');
                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_replyData);// remove slashes with quotes.
                $jsst_error = 0;
                if (!$jsst_row->bind($jsst_data)) {
                    $jsst_error = 1;
                }
                if (!$jsst_row->store()) {
                    $jsst_error = 1;
                }
            } 
            
        }
    }

    private function getAwesomeSupportNoteAttachments($jsst_jshd_ticket_id, $jsst_as_ticket_reply_id, $jsst_attachmentdir){
        // Using prepare for secure query
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT post.*
                FROM `" . jssupportticket::$_db->prefix . "posts` AS post
                WHERE post.post_parent = %d
                AND post.post_type = 'attachment'
                ORDER BY post.id ASC",
            $jsst_as_ticket_reply_id
        );
                        
        $jsst_attachment = jssupportticket::$_db->get_row($jsst_query);

        if (empty($jsst_attachment)) return;

        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        $jsst_filesystem = $jsst_wp_filesystem;

        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = trailingslashit($jsst_upload_path) . $jsst_datadirectory . "/attachmentdata/ticket/" . sanitize_file_name($jsst_attachmentdir);

        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }

        $jsst_safe_filename = sanitize_file_name($jsst_attachment->title);
        $jsst_source = $jsst_attachment->file_path;
        $jsst_destination = $jsst_path . "/" . $jsst_safe_filename;
        
        $jsst_destination_new_name = $jsst_path . "/" . $jsst_attachment->title;

        // Use $jsst_filesystem->exists instead of file_exists
        if (!$jsst_filesystem->exists($jsst_source)) {
            return '';
        }

        $jsst_result = $jsst_filesystem->copy($jsst_source, $jsst_destination, true);
        if (!$jsst_result) {
            return '';
        }

        // Use $jsst_filesystem->move instead of rename()
        $jsst_filesystem->move($jsst_destination, $jsst_destination_new_name, true);

        return $jsst_safe_filename;
    }

    private function getAwesomeSupportTicketAttachments($jsst_jshd_ticket_id, $jsst_jshd_ticket_reply_id, $jsst_ast_ticket_id, $jsst_as_ticket_reply_id, $jsst_attachmentdir){
        $jsst_as_ticket_reply_id = intval($jsst_as_ticket_reply_id);

        if ($jsst_as_ticket_reply_id <= 0) return;


        $jsst_query = "SELECT post.*
                    FROM `" . jssupportticket::$_db->prefix . "posts` AS post
                    WHERE post.post_parent = ".$jsst_as_ticket_reply_id."
                    AND post.post_type = 'attachment'
                    ORDER BY post.id ASC";
                    
        $jsst_posts = jssupportticket::$_db->get_results($jsst_query);


        foreach($jsst_posts AS $jsst_post){
            $jsst_post_meta = get_post_meta($jsst_post->ID);
            if(isset($jsst_post_meta["_wp_attachment_metadata"][0])){
                $jsst_attachment = unserialize($jsst_post_meta["_wp_attachment_metadata"][0]);
                $jsst_file_name = basename($jsst_attachment["file"]);         
                
            
                $jsst_attachmentData = [
                    "id" => "",
                    "ticketid" => $jsst_jshd_ticket_id,
                    "replyattachmentid" => $jsst_jshd_ticket_reply_id,
                    "filesize" => "",
                    "filename" => $jsst_file_name,
                    "filekey" => "",
                    "deleted" => "",
                    "status" => "1",
                    "created" => $jsst_post->post_date
                ];
                $jsst_row = JSSTincluder::getJSTable('attachments');
                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_attachmentData);// remove slashes with quotes.
                $jsst_error = 0;
                if (!$jsst_row->bind($jsst_data)) {
                    $jsst_error = 1;
                }
                if (!$jsst_row->store()) {
                    $jsst_error = 1;
                }
                require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
                require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
                $jsst_filesystem = new WP_Filesystem_Direct( true );
                $jsst_upload_dir = wp_upload_dir();
                $jsst_upload_path = $jsst_upload_dir['basedir'];         // Server path to the uploads directory
                $jsst_datadirectory = jssupportticket::$_config['data_directory'];
                $jsst_path = $jsst_upload_path."/".$jsst_datadirectory."/attachmentdata/ticket/".$jsst_attachmentdir;
                if(!$jsst_filesystem->exists($jsst_path)){
                    wp_mkdir_p($jsst_path);
                }
                $jsst_source = $jsst_upload_path . "/" . $jsst_attachment["file"]; // full path to original
                if (!file_exists($jsst_source)) {
                    $jsst_path_info = pathinfo($jsst_source);

                    // Get directory and base filename (without extension)
                    $jsst_directory = $jsst_path_info['dirname'];
                    $jsst_filename = $jsst_path_info['filename']; // e.g., 01_5
                    $jsst_extension = $jsst_path_info['extension']; // e.g., jpg

                    // Desired sizes to check
                    $jsst_sizes = ['100x100', '150x150', '300x300', '600x337', '768x431', '300x168'];
                    $jsst_resized_file = '';

                    foreach ($jsst_sizes as $jsst_size) {
                        $jsst_resized_path = $jsst_directory . '/' . $jsst_filename . '-' . $jsst_size . '.' . $jsst_extension;
                        if (file_exists($jsst_resized_path)) {
                            $jsst_resized_file = $jsst_resized_path;
                            break;
                        }
                    }

                    // Fallback to original if no resized version found
                    if (!$jsst_resized_file && file_exists($jsst_source)) {
                        $jsst_resized_file = $jsst_source;
                    }
                } else {
                    $jsst_resized_file = $jsst_source;
                }
                $jsst_destination = $jsst_path."/".$jsst_file_name;
                
                $jsst_result = $jsst_filesystem->move($jsst_resized_file, $jsst_destination, true);
            }
            
        }
    }

    private function getAwesomeSupportTicketCustomerInfo($jsst_customerId){
        // Sanitize and validate customer ID
        $jsst_customerId = intval($jsst_customerId);
        if ($jsst_customerId <= 0) {
            return [
                "jshd_uid" => "",
                "customer_name" => "",
                "customer_email" => ""
            ];
        }

        // Prepare secure query
        $jsst_query = "
            SELECT customer.name, customer.user_email, customer.id AS jshd_uid
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` AS customer
            WHERE customer.wpuid = ". esc_sql($jsst_customerId) ."
            LIMIT 1
        ";
        $jsst_data = jssupportticket::$_db->get_row($jsst_query);

        return [
            "jshd_uid"       => $jsst_data->jshd_uid ?? "",
            "customer_name"  => $jsst_data->name ?? "",
            "customer_email" => $jsst_data->email ?? ""
        ];
    }

    private function getAwesomeSupportTicketPrivateCredentials($jsst_jshd_ticket_id, $jsst_jshd_ticket_uid, $jsst_post_id) {
        $jsst_jshd_ticket_uid = 1;
        // Get private credentials if they exist.
        if( get_post_meta( $jsst_post_id, '_wpas_pc_credentials', true ) ) {
            $jsst_credentials = get_post_meta( $jsst_post_id, '_wpas_pc_credentials', true );
            
            $jsst_encryption_key = get_post_meta( $jsst_post_id, '_wpas_pc_encryption_key', true );

            foreach( $jsst_credentials as $jsst_key => $jsst_value ) {
                $jsst_system   = $this->decrypt( $jsst_value[ "system" ], $jsst_encryption_key );
                $jsst_username = $this->decrypt( $jsst_value[ "username" ], $jsst_encryption_key );
                $jsst_password = $this->decrypt( $jsst_value[ "password" ], $jsst_encryption_key );
                $jsst_url      = $this->decrypt( $jsst_value[ "url" ], $jsst_encryption_key );
                $jsst_note     = $this->decrypt( $jsst_value[ "note" ], $jsst_encryption_key );

                $jsst_pc_array = [
                    'credentialtype' => sanitize_text_field($jsst_system),
                    'username'       => $jsst_username,
                    'password'       => $jsst_password,
                    'info'           => $jsst_note
                ];

                $jsst_data = [
                    'id'        => '',
                    'uid'       => intval($jsst_jshd_ticket_uid),
                    'ticketid'  => intval($jsst_jshd_ticket_id),
                    'status'    => 1,
                    'created'   => current_time('mysql'),
                ];

                // Clean and encode credential info
                $jsst_encoded = wp_json_encode(array_filter($jsst_pc_array));
                $jsst_safe_encoded = jssupportticketphplib::JSST_safe_encoding($jsst_encoded);
                $jsst_data['data'] = JSSTincluder::getObjectClass('privatecredentials')->encrypt($jsst_safe_encoded);

                // Insert record
                if ($jsst_data['ticketid'] > 0 && $jsst_data['uid'] > 0) {
                    $jsst_row = JSSTincluder::getJSTable('privatecredentials');
                    if ($jsst_row->bind($jsst_data)) {
                        $jsst_row->store(); // Failure silently ignored here; consider logging
                    }
                }
            }
        }
    }

    private function decrypt( $jsst_message, $jsst_key, $jsst_encoded = true ) {
        $jsst_method = 'aes-256-ctr';

        if ( $jsst_message == '' ) {
            return '';
        }

        if ( $jsst_encoded ) {
            $jsst_message = base64_decode( $jsst_message, true );
            if ( $jsst_message === false ) {
                return false;
            }
        }

        $jsst_nonceSize  = openssl_cipher_iv_length( $jsst_method );
        $jsst_nonce      = mb_substr( $jsst_message, 0, $jsst_nonceSize, '8bit' );
        $jsst_ciphertext = mb_substr( $jsst_message, $jsst_nonceSize, null, '8bit' );

        $jsst_plaintext = '';

        try {
            $jsst_plaintext = openssl_decrypt( $jsst_ciphertext, $jsst_method, $jsst_key, OPENSSL_RAW_DATA, $jsst_nonce );
        } catch ( Exception $jsst_e ) {
            return false;
        }

        return $jsst_plaintext;

    }

    private function getAwesomeSupportTicketActivityLog($jsst_jshd_ticket_id, $jsst_sc_ticket_id) {
        $jsst_sc_ticket_id = intval($jsst_sc_ticket_id);
        $jsst_jshd_ticket_id = intval($jsst_jshd_ticket_id);

        if ($jsst_sc_ticket_id <= 0 || $jsst_jshd_ticket_id <= 0) return;

        $jsst_threads = get_posts( [
            'post_parent'    => $jsst_sc_ticket_id,
            'post_type'      => apply_filters( 'wpas_replies_post_type', array(
                                'ticket_history',
                                'ticket_reply',
                                'ticket_log'
                             ) ),
            'post_status'    => 'any',
            'orderby'        => 'ID',
            'order'          => 'DESC',
            'numberposts'    => -1, // get all
            'exclude'        => get_posts([
                'post_type'  => 'wpass_status',
                'post_status'=> 'auto-draft',
                'fields'     => 'ids',
            ]),
        ]);

        if (empty($jsst_threads)) return;

        foreach ($jsst_threads as $jsst_thread) {
            $jsst_ticketid = $jsst_jshd_ticket_id;

            // Get user information
            $jsst_userinfo = $this->getAwesomeSupportTicketCustomerInfo($jsst_thread->post_author);
            $jsst_currentUserName = !empty($jsst_userinfo['customer_name']) 
                ? esc_html($jsst_userinfo['customer_name']) 
                : esc_html(__('Guest', 'js-support-ticket'));

            $jsst_messagetype = __('Successfully', 'js-support-ticket');
            $jsst_eventtype = '';
            $jsst_message = '';

            if ($jsst_thread->post_type == 'ticket_history') {
                $jsst_eventtype = jssupportticketphplib::JSST_strip_tags($jsst_thread->post_content);
                $jsst_messageWithBreaks = jssupportticketphplib::JSST_preg_replace('/<\/[^>]+>/', "$0 ", $jsst_thread->post_content);
                $jsst_message = jssupportticketphplib::JSST_strip_tags($jsst_messageWithBreaks) . " " . __('by', 'js-support-ticket') . " ( $jsst_currentUserName )";
            } elseif ($jsst_thread->post_type == 'ticket_reply') {
                $jsst_eventtype = __('REPLIED_TICKET', 'js-support-ticket');
                $jsst_message = __('Ticket is replied by', 'js-support-ticket') . " ( $jsst_currentUserName )";
            }

            if (!empty($jsst_eventtype) && !empty($jsst_message)) {
                JSSTincluder::getJSModel('tickethistory')->addActivityLog(
                    $jsst_ticketid, 1, esc_html($jsst_eventtype), esc_html($jsst_message), esc_html($jsst_messagetype)
                );
            }
        }
    }

    private function getAwesomeSupportTicketStaffTime($jsst_jshd_ticket_id, $jsst_ast_ticket_id, $jsst_jshd_reply_id, $jsst_as_reply_id) {
        $jsst_as_reply_id = intval($jsst_as_reply_id);
        $jsst_jshd_ticket_id = intval($jsst_jshd_ticket_id);
        if ($jsst_as_reply_id <= 0 || $jsst_jshd_ticket_id <= 0) return;

        // Get all timer logs for the given Awesome Support ticket
        
        $jsst_query = new WP_Query( array(
            'post_type' => 'trackedtimes',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ) );

        $jsst_time_ids = wp_list_pluck( $jsst_query->posts, 'ID' );
        $jsst_duplicate_occurs = false;

        foreach( $jsst_time_ids as $jsst_id ) {
            $jsst_tracked_time = get_post_meta( $jsst_id, 'as_time_tracking_entry' );

            if( !empty( $jsst_tracked_time ) ) {
                if( ( $jsst_ast_ticket_id == $jsst_tracked_time[0]['ticket_id'] ) && ( $jsst_as_reply_id == $jsst_tracked_time[0]['ticket_reply'] ) ) {

                    // Get HelpDesk staff ID from SupportCandy agent ID
                    // $jsst_staffid = $this->getJshdAgentIdByScAgentId($jsst_timer->log_by);
                    // if (empty($jsst_staffid)) continue;

                    $jsst_created = $jsst_tracked_time[0]['start_date_time'];

                    // Handle and validate interval string
                    

                    $jsst_timer_minutes = $jsst_tracked_time[0]['individual_time'];
                    if ($jsst_timer_minutes <= 0) continue;

                    $jsst_timer_seconds = $jsst_timer_minutes * 60;

                    // Conflict detection
                    $jsst_created_dt = new DateTime($jsst_created);
                    $jsst_now = new DateTime();
                    $jsst_interval_to_now = $jsst_created_dt->diff($jsst_now);
                    $jsst_systemtime = ($jsst_interval_to_now->days * 86400) + ($jsst_interval_to_now->h * 3600) + ($jsst_interval_to_now->i * 60) + $jsst_interval_to_now->s;

                    $jsst_conflict = ($jsst_timer_seconds > $jsst_systemtime) ? 1 : 0;

                    // Prepare data
                    $jsst_data = [
                        'staffid' => '',
                        'ticketid' => $jsst_jshd_ticket_id,
                        'referencefor' => 1,
                        'referenceid' => $jsst_jshd_reply_id,
                        'usertime' => $jsst_timer_seconds,
                        'systemtime' => $jsst_systemtime,
                        'conflict' => $jsst_conflict,
                        'description' => '',
                        'timer_edit_desc' => '',
                        'status' => 1,
                        'created' => $jsst_created
                    ];

                    $jsst_row = JSSTincluder::getJSTable('timetracking');
                    $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);

                    if (!$jsst_row->bind($jsst_data) || !$jsst_row->store()) {
                        // optionally log or count the failure
                        continue;
                    }
                }
            }
        }
        return;
    }
    
    private function getTicketDepartmentIdByAwesomeSupport($jsst_ticketId){
        // Validate and sanitize ticket ID
        $jsst_ticketId = intval($jsst_ticketId);
        if ($jsst_ticketId <= 0) return null;

        // Fetch department from source table
        $jsst_departmet_term = wp_get_object_terms($jsst_ticketId, 'department');

        if (is_wp_error($jsst_departmet_term) || empty($jsst_departmet_term[0]->name)) return null;

        // Find corresponding department in destination table

        $jsst_name = $jsst_departmet_term[0]->name;
        
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments`
                WHERE LOWER(departmentname) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_name)))."'";
        $jsst_jshd_department_id = jssupportticket::$_db->get_var($jsst_query);
        
        return $jsst_jshd_department_id ? (int)$jsst_jshd_department_id : null;
    }

    private function getTicketPriorityIdByAwesomeSupport($jsst_ticketId){
        // Sanitize and validate input
        $jsst_ticketId = intval($jsst_ticketId);
        if ($jsst_ticketId <= 0) return null;

        // Fetch priority from source table
        $jsst_priority_term = wp_get_object_terms($jsst_ticketId, 'ticket_priority');

        if (is_wp_error($jsst_priority_term) || empty($jsst_priority_term[0]->name)) return null;

        // Find corresponding priority in destination table
        
        $jsst_name = $jsst_priority_term[0]->name;
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`
                WHERE LOWER(priority) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_name)))."'";;
        $jsst_jshd_priority_id = jssupportticket::$_db->get_var($jsst_query);
        
        return $jsst_jshd_priority_id ? (int)$jsst_jshd_priority_id : null;
    }

    private function getTicketStatusIdByAwesomeSupport($jsst_ticket_status) {
        $jsst_custom_status = wpas_get_post_status();
        if (empty($jsst_ticket_status)) return null;

        if (empty($jsst_custom_status[$jsst_ticket_status])) return null;

        // Find matching status in destination table
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_statuses` WHERE LOWER(status) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_custom_status[$jsst_ticket_status])))."'";
        $jsst_jshd_status_id = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_jshd_status_id ? (int)$jsst_jshd_status_id : null;
    }

    private function getTicketAgentIdByAwesomeSupport($jsst_ticketId){
        // Sanitize and validate input
        $jsst_ticketId = intval($jsst_ticketId);
        if ($jsst_ticketId <= 0) return null;

        // Fetch product from source table
        $jsst_assigned_agent = get_post_meta( $jsst_ticketId, '_wpas_assignee', true );

        if (is_wp_error($jsst_assigned_agent) || empty($jsst_assigned_agent)) return null;

        $jsst_js_user = JSSTincluder::getObjectClass('user')->getjssupportticketuidbyuserid($jsst_assigned_agent);
        if (!empty($jsst_js_user) && isset($jsst_js_user[0]->id)) {
            $jsst_js_uid = (int)$jsst_js_user[0]->id;
        } else {
            return;
        }
        
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` WHERE uid = ".$jsst_js_uid;
        $jsst_jshd_agent_id = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_jshd_agent_id ? (int)$jsst_jshd_agent_id : null;
    }

    private function getTicketProductIdByAwesomeSupport($jsst_ticketId){
        // Sanitize and validate input
        $jsst_ticketId = intval($jsst_ticketId);
        if ($jsst_ticketId <= 0) return null;

        // Fetch product from source table
        $jsst_product_term = wp_get_object_terms($jsst_ticketId, 'product');

        if (is_wp_error($jsst_product_term) || empty($jsst_product_term[0]->name)) return null;

        // Find corresponding product in destination table
        
        $jsst_name = $jsst_product_term[0]->name;
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_products`
                WHERE LOWER(product) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_name)))."'";
        $jsst_jshd_product_id = jssupportticket::$_db->get_var($jsst_query);
        
        return $jsst_jshd_product_id ? (int)$jsst_jshd_product_id : null;
    }

    private function importAwesomeSupportFaqs(){
        // Load previously imported faqs
        $jsst_imported_faqs = [];
        $jsst_imported_faqs_json = get_option('js_support_ticket_awesome_support_data_faqs');
        if (!empty($jsst_imported_faqs_json)) {
            $jsst_imported_faqs = json_decode($jsst_imported_faqs_json, true);
        }

        // Get SupportCandy faqs (excluding system/default ones)
        $jsst_faqs = get_posts( [
            'post_type'      => 'faq',
            'post_status'    => 'any', // includes all except 'auto-draft'
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'numberposts'    => -1, // get all
            'exclude'        => get_posts([
                'post_type'   => 'wpass_status',
                'post_status'=> 'auto-draft',
                'fields'      => 'ids',
            ]),
        ] );

        if (empty($jsst_faqs)) return;

        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(faq.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_faqs` AS faq
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);

        // Build array of existing JS faqs (cleaned)
        $jsst_query = "
            SELECT faq.subject
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_faqs` AS faq
        ";
        $jsst_jsfaqs = jssupportticket::$_db->get_results($jsst_query);
        $jsst_existing_faq_names = array_map(function($jsst_faq) {
            return $this->cleanStringForCompare($jsst_faq->subject);
        }, $jsst_jsfaqs);

        foreach ($jsst_faqs as $jsst_faq) {
            $jsst_name = $jsst_faq->post_title;
            $jsst_compare_name = $this->cleanStringForCompare($jsst_name);

            // Skip if name already exists
            if (in_array($jsst_compare_name, $jsst_existing_faq_names)) {
                $this->jsst_awesome_support_import_count['faq']['skipped'] += 1;
                continue;
            }

            // Skip if already imported
            if (in_array($jsst_faq->ID, $jsst_imported_faqs)) {
                $this->jsst_awesome_support_import_count['faq']['skipped'] += 1;
                continue;
            }

            $jsst_taxonomies = get_object_taxonomies('faq');
            $jsst_terms = get_the_terms($jsst_faq->ID, $jsst_taxonomies[0]);
            if(in_array('knowledgebase', jssupportticket::$_active_addons)) {
                $jsst_categoryid = $this->getFaqCategoryIdByAwesomeSupport($jsst_terms[0]->name);
            } else {
                $jsst_categoryid = '';
            }

            // Prepare new faq data
            $jsst_row = JSSTincluder::getJSTable('faq');
            $jsst_data = [
                'id'            => '',
                'categoryid'    => $jsst_categoryid,
                'staffid'       => 0,
                'subject'       => $jsst_faq->post_title,
                'content'       => $jsst_faq->post_content,
                'views'         => 0,
                'ordering'      => $jsst_ordering,
                'created'       => $jsst_faq->post_date,
                'status'        => 1,
                'visible'       => 0,
            ];

            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
            $jsst_row->bind($jsst_data);

            if (!$jsst_row->store()) {
                $this->jsst_awesome_support_import_count['faq']['failed'] += 1;
            } else {
                $this->awesome_support_faq_ids[] = $jsst_faq->id;
                $this->jsst_awesome_support_import_count['faq']['imported'] += 1;
                $jsst_ordering++;
            }
        }

        // Save updated list of imported faqs
        if (!empty($this->awesome_support_faq_ids)) {
            update_option('js_support_ticket_awesome_support_data_faqs', wp_json_encode($this->awesome_support_faq_ids));
        }
    }

    private function getFaqCategoryIdByAwesomeSupport($jsst_name){
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_categories`
                WHERE LOWER(name) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_name)))."'";
        $jsst_jshd_category_id = jssupportticket::$_db->get_var($jsst_query);
        if (empty($jsst_jshd_category_id)) {

            $jsst_data['id'] = '';
            $jsst_data['name'] = $jsst_name;
            $jsst_data['created'] = date_i18n('Y-m-d H:i:s');

            $jsst_kb = '0';
            $jsst_downloads = '0';
            $jsst_announcement = '0';
            $jsst_faqs = '1';

            $jsst_data['kb'] = $jsst_kb;
            $jsst_data['downloads'] = $jsst_downloads;
            $jsst_data['announcement'] = $jsst_announcement;
            $jsst_data['faqs'] = $jsst_faqs;
            $jsst_data['staffid'] = 0;
            $jsst_data['status'] = 1;

            $jsst_row = JSSTincluder::getJSTable('categories');

            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_data)) {
                $jsst_error = 1;
            }
            if (!$jsst_row->store()) {
                $jsst_error = 1;
            }
            if (empty($jsst_error)) {
                $jsst_jshd_category_id = $jsst_row->id;
            }
        }
        
        return $jsst_jshd_category_id ? (int)$jsst_jshd_category_id : null;
    }

    private function getPostConutByType ( $jsst_post_type ) {
        $jsst_counts = wp_count_posts( $jsst_post_type );
        return isset( $jsst_counts->publish ) ? (int) $jsst_counts->publish : 0;
    }
    
    //================
    // --------------
    //////////////////
    // Fluent Support
    //////////////////
    // ---------------
    //================

    function importFluentSupportData() {
        // Only for development – remove before pushing to production
        // $this->deletesupportcandyimporteddata();

        // Reset previously imported IDs from options
        update_option('js_support_ticket_fluent_support_data_priorities', '');
        update_option('js_support_ticket_fluent_support_data_users', '');
        update_option('js_support_ticket_fluent_support_data_premades', '');
        update_option('js_support_ticket_fluent_support_data_agents', '');
        update_option('js_support_ticket_fluent_support_data_products', '');
        update_option('js_support_ticket_fluent_support_data_tickets', '');
        
        // Prepare filesystem and create necessary directories
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        $jsst_filesystem = new WP_Filesystem_Direct(true);
        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = $jsst_upload_path . "/" . $jsst_datadirectory;

        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }
        $jsst_path .= '/attachmentdata';
        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }
        $jsst_path .= '/ticket';
        if (!$jsst_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_persons'")) {
            $this->importFluentSupportUsers();
        }

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_persons'")) {
            $this->importFluentSupportAgents();
        }

        $this->importFluentSupportPriorities();

        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_saved_replies'")) {
            $this->importFluentSupportPremades();
        }
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_products'")) {
            $this->importFluentSupportProducts();
        }
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_meta'")) {
            $this->importFluentSupportTicketFields();
        }
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_tickets'")) {
            $this->getFluentSupportTickets( );
        }

        update_option('jsst_import_counts',$this->jsst_fluent_support_import_count);
        return;
    }

    private function getFluentSupportTickets() {
        // Check if tickets already processed for import
        $jsst_imported_tickets = array();
        $jsst_imported_tickets_json = get_option('js_support_ticket_fluent_support_data_tickets');
        if (!empty($jsst_imported_tickets_json)) {
            $jsst_imported_tickets = json_decode($jsst_imported_tickets_json, true);
        }

        $jsst_query = "SELECT tickets.*
                FROM `" . jssupportticket::$_db->prefix . "fs_tickets` AS tickets
                ORDER BY tickets.id ASC";
        
        $jsst_tickets = jssupportticket::$_db->get_results($jsst_query);
        
        $jsst_new_tickets = array();
        foreach ($jsst_tickets as $jsst_ticket) {
            // Skip if ticket already imported
            if (!empty($jsst_imported_tickets) && in_array($jsst_ticket->id, $jsst_imported_tickets)) {
                $this->jsst_fluent_support_import_count['ticket']['skipped'] += 1;
                continue;
            }

            // Map custom fields
            $jsst_params = array();
            $jsst_query = "SELECT meta.*
                        FROM `" . jssupportticket::$_db->prefix . "fs_meta` AS meta
                        WHERE object_type = 'ticket_meta'
                        AND object_id = ".$jsst_ticket->id.";";
            $jsst_tickets_meta = jssupportticket::$_db->get_results($jsst_query);
            foreach($jsst_tickets_meta as $jsst_ticket_meta){
                foreach ($this->jsst_fc_ticket_cf as $jsst_fs_ticket_custom_field => $jsst_js_ticket_custom_field) {
                    if($jsst_ticket_meta->key == $jsst_fs_ticket_custom_field){
                        $jsst_custom_field_value = "";
                        $jsst_custom_field_value = $jsst_ticket_meta->value;
                        $jsst_custom_field_value = jssupportticketphplib::JSST_str_replace("|","",$jsst_custom_field_value);
                        $jsst_custom_field_value = jssupportticketphplib::JSST_str_replace("|","",$jsst_custom_field_value);
                        $jsst_vardata = "";
                        
                        $jsst_fieldtype = $this->checkTypeOfTheField($jsst_fs_ticket_custom_field);
                        if($jsst_fieldtype == "date"){
                            $jsst_vardata = gmdate("Y-m-d", jssupportticketphplib::JSST_strtotime($jsst_custom_field_value));
                        }else{
                            $jsst_vardata = $jsst_custom_field_value;
                        }
                        if($jsst_vardata != ''){
                            if(is_array($jsst_vardata)){
                                $jsst_vardata = implode(', ', array_filter($jsst_vardata));
                            }
                            $jsst_params[$jsst_js_ticket_custom_field] = jssupportticketphplib::JSST_htmlentities($jsst_vardata);
                        }
                    }
                }
            }
            $jsst_ticketparams = html_entity_decode(wp_json_encode($jsst_params, JSON_UNESCAPED_UNICODE));

            // Get linked data
            $jsst_userinfo = $this->getFluentSupportTicketCustomerInfo($jsst_ticket->customer_id);
            $jsst_agentid = $this->getTicketAgentIdByFluentSupport($jsst_ticket->agent_id);
            $jsst_productid = $this->getTicketProductIdByFluentSupport($jsst_ticket->product_id);
            $jsst_priorityid = $this->getTicketPriorityIdByFluentSupport($jsst_ticket->client_priority);

            $jsst_idresult = JSSTincluder::getJSModel('ticket')->getRandomTicketId();
            $jsst_ticketid = $jsst_idresult['ticketid'];
            $jsst_customticketno = $jsst_idresult['customticketno'];
            $jsst_attachmentdir = JSSTincluder::getJSModel('ticket')->getRandomFolderName();

            // Determine ticket status
            $jsst_ticket_status = 1;
            if($jsst_ticket->status == "new") $jsst_ticket_status = 1;
            elseif($jsst_ticket->status == "active"){
                $jsst_ticket_status = 2;
                if(jssupportticketphplib::JSST_strtotime($jsst_ticket->last_agent_response) == jssupportticketphplib::JSST_strtotime($jsst_ticket->waiting_since)){
                    $jsst_ticket_status = 4;
                }
                if(jssupportticketphplib::JSST_strtotime($jsst_ticket->last_customer_response) == jssupportticketphplib::JSST_strtotime($jsst_ticket->waiting_since)){
                    $jsst_ticket_status = 2;
                }
            }elseif($jsst_ticket->status == "closed") $jsst_ticket_status = 5;

            $jsst_isanswered = ($jsst_ticket_status == 4) ? 1 : 0;

            $jsst_ticket_closed = "0000-00-00 00:00:00";
            $jsst_ticket_closedby = "";
            if ($jsst_ticket->resolved_at && $jsst_ticket->resolved_at != '0000-00-00 00:00:00' && $jsst_ticket->closed_by) {
                $jsst_ticket_closed = $jsst_ticket->resolved_at;
                $jsst_ticket_closedby =$jsst_ticket->closed_by;
            }
            // Ticket Default Status
            // 1 -> New Ticket
            // 2 -> Waiting admin/staff reply
            // 3 -> in progress
            // 4 -> waiting for customer reply
            // 5 -> close ticket

            $jsst_newTicketData = [
                'id' => "",
                'uid' => $jsst_userinfo["jshd_uid"],
                'ticketid' => $jsst_ticketid,
                'productid' => $jsst_productid,
                'priorityid' => $jsst_priorityid,
                'staffid' => $jsst_agentid,
                'email' => $jsst_userinfo["customer_email"],
                'name' => $jsst_userinfo["customer_name"],
                'subject' => $jsst_ticket->title,
                'message' => $jsst_ticket->content,
                'helptopicid' => 0,
                'multiformid' => 1,
                'phone' => "",
                'phoneext' => "",
                'status' => $jsst_ticket_status,
                'isoverdue' => "0",
                'isanswered' => $jsst_isanswered,
                'duedate' => "0000-00-00 00:00:00",
                'reopened' => "0000-00-00 00:00:00",
                'closed' => $jsst_ticket_closed,
                'closedby' => $jsst_ticket_closedby,
                'lastreply' => $jsst_ticket->waiting_since,
                'created' => $jsst_ticket->created_at,
                'updated' => $jsst_ticket->updated_at,
                'lock' => "0",
                'ticketviaemail' => "0",
                'ticketviaemail_id' => "0",
                'attachmentdir' => $jsst_attachmentdir,
                'feedbackemail' => "0",
                'mergestatus' => "0",
                'mergewith' => "0",
                'mergenote' => "",
                'mergedate' => "0000-00-00 00:00:00",
                'multimergeparams' => "",
                'mergeuid' => "0",
                'params' => $jsst_ticketparams,
                'hash' => "",
                'notificationid' => "0",
                'wcorderid' => "0",
                'wcitemid' => "0",
                'wcproductid' => "0",
                'eddorderid' => "0",
                'eddproductid' => "0",
                'eddlicensekey' => "",
                'envatodata' => "",
                'paidsupportitemid' => "0",
                'customticketno' => $jsst_customticketno
            ];

            $jsst_row = JSSTincluder::getJSTable('tickets');
            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_newTicketData)) $jsst_error = 1;
            if (!$jsst_row->store()) $jsst_error = 1;

            if ($jsst_error == 1) {
                $this->jsst_fluent_support_import_count['ticket']['failed'] += 1;
            } else {
                $this->jsst_fluent_support_ticket_ids[] = $jsst_ticket->id;
                $this->jsst_fluent_support_import_count['ticket']['imported'] += 1;

                $jsst_jshd_ticketid = $jsst_row->id;
                $jsst_hash = JSSTincluder::getJSModel('ticket')->generateHash($jsst_jshd_ticketid);
                $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET `hash`='" . esc_sql($jsst_hash) . "' WHERE id=" . esc_sql($jsst_jshd_ticketid);
                jssupportticket::$_db->query($jsst_query);

                if(in_array('note', jssupportticket::$_active_addons)){
                    $this->getFluentSupportTicketNotes($jsst_jshd_ticketid, $jsst_ticket->id, $jsst_attachmentdir);
                }
                $this->getFluentSupportTicketReplies($jsst_jshd_ticketid, $jsst_ticket->id, $jsst_attachmentdir);
                $this->getFluentSupportTicketAttachments($jsst_jshd_ticketid, $jsst_ticket->id, $jsst_attachmentdir);

                if (in_array('tickethistory', jssupportticket::$_active_addons)) {
                    $this->getFluentSupportTicketActivityLog($jsst_jshd_ticketid, $jsst_ticket->id);
                }

                if (in_array('timetracking', jssupportticket::$_active_addons) && jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_time_tracks'")) {
                    $this->getFluentSupportTicketStaffTime($jsst_jshd_ticketid, $jsst_ticket->id);
                }
            }
        }

        if (!empty($this->jsst_fluent_support_ticket_ids)) {
            update_option('js_support_ticket_fluent_support_data_tickets', wp_json_encode($this->jsst_fluent_support_ticket_ids));
        }
    }

    private function importFluentSupportTicketFields() {
        // Get all ticket-related custom fields
        $jsst_query = "
            SELECT * FROM `" . jssupportticket::$_db->prefix . "fs_meta`
            WHERE object_type = 'option' AND `key` = '_ticket_custom_fields';";
        $jsst_custom_fields_serializeed = jssupportticket::$_db->get_row($jsst_query);

        if (!$jsst_custom_fields_serializeed) return;

        $jsst_custom_fields = unserialize($jsst_custom_fields_serializeed->value);
        

        if (!$jsst_custom_fields) return;

        $this->jsst_fc_ticket_cf = [];


        foreach ($jsst_custom_fields as $jsst_custom_field) {
            // Map field types
            switch ($jsst_custom_field["type"]){
                case "text":
                    $jsst_fieldtype = "text"; break;
                case "select-one":
                    $jsst_fieldtype = "combo"; break;
                case "radio":
                    $jsst_fieldtype = "radio"; break;
                case "checkbox":
                    $jsst_fieldtype = "checkbox"; break;
                case "textarea":
                    $jsst_fieldtype = "textarea"; break;
                case "number":
                    $jsst_fieldtype = "text"; break;
                default:
                    $jsst_fieldtype = "text"; break;
            }

            $jsst_query = "SELECT id,field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE isuserfield = 1 AND LOWER(fieldtitle) ='".esc_sql(jssupportticketphplib::JSST_strtolower($jsst_custom_field['label']))."' AND userfieldtype ='".esc_sql($jsst_fieldtype)."' AND fieldfor = 1";
            $jsst_field_record = jssupportticket::$_db->get_row($jsst_query);

            if(!empty($jsst_field_record)){ // this will make sure
                $this->jsst_fluent_support_import_count['field']['skipped'] += 1;
                continue;
            }

            // Load options for select-type fields
            $jsst_option_values = [];
            if(isset($jsst_custom_field["options"])){
                foreach($jsst_custom_field["options"] as $jsst_key => $jsst_value){
                    $jsst_option_values[] = $jsst_value;
                }
            }
            // required
            $jsst_required = 0;
            if(isset($jsst_custom_field["required"])){
                if($jsst_custom_field["required"] == "yes") $jsst_required = 1;
            }
            // admin olny
            $jsst_adminonly = 0;
            if(isset($jsst_custom_field["admin_only"])){
                if($jsst_custom_field["admin_only"] == "yes") $jsst_adminonly = 1;
            }
            // placeholder
            $jsst_placeholder = '';
            if(isset($jsst_custom_field["placeholder"])){
                $jsst_placeholder = $jsst_custom_field["placeholder"];
            }

            // Build visibility data
            $jsst_visibledata = [
                "visibleLogic" => [],
                "visibleParent" => [],
                "visibleValue" => [],
                "visibleCondition" => [],
            ];

            // Prepare field data for import
            $jsst_fieldOrderingData = [
                "id" => "",
                "field" => $jsst_custom_field["slug"],
                "fieldtitle" => $jsst_custom_field['label'],
                "ordering" => "",
                "section" => "10",
                "fieldfor" => "1",
                "published" => "1",
                "sys" => "0",
                "cannotunpublish" => "0",
                "required" => $jsst_required,
                "size" => "100",
                "maxlength" => "255",
                "cols" => "",
                "rows" => "",
                "isuserfield" => "1",
                "userfieldtype" => $jsst_fieldtype,
                "depandant_field" => "",
                "visible_field" => "",
                "showonlisting" => "0",
                "cannotshowonlisting" => "0",
                "search_user" => "0",
                "cannotsearch" => "0",
                "isvisitorpublished" => "1",
                "search_visitor" => "0",
                "userfieldparams" => "",
                "multiformid" => "1",
                "visibleparams" => "",
                "values" => $jsst_option_values,
                "visibleParent" => $jsst_visibledata["visibleParent"],
                "visibleValue" => $jsst_visibledata["visibleValue"],
                "visibleCondition" => $jsst_visibledata["visibleCondition"],
                "visibleLogic" => $jsst_visibledata["visibleLogic"],
                "placeholder" => $jsst_placeholder,
                "description" => '',
                "defaultvalue" => '',
                "readonly" => '',
                "adminonly" => $jsst_adminonly,
            ];

            // Store field in SupportCandy
            $jsst_record_saved = JSSTincluder::getJSModel('fieldordering')->storeUserField($jsst_fieldOrderingData);

            if ($jsst_record_saved == 1) {
                $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` ORDER BY id DESC LIMIT 1";
                $jsst_latest_record = jssupportticket::$_db->get_row($jsst_query);
                $this->jsst_fc_ticket_cf[$jsst_custom_field["slug"]] = $jsst_latest_record->field;

                $this->jsst_fluent_support_import_count['field']['imported'] += 1;
            } else {
                $this->jsst_fluent_support_import_count['field']['failed'] += 1;
            }
        }

        foreach ($jsst_custom_fields as $jsst_custom_field) {
            $jsst_field = $this->getTicketCustomFieldId($jsst_custom_field['label']);
            $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE field = '".esc_sql($jsst_field)."' LIMIT 1";
            $jsst_jshd_field = jssupportticket::$_db->get_row($jsst_query);
            if (empty($jsst_jshd_field)) {
                continue;
            }
            

            // Build visibility data
            $jsst_visibledata = [
                "visibleLogic" => [],
                "visibleParent" => [],
                "visibleValue" => [],
                "visibleCondition" => [],
            ];

            
            if (!empty($jsst_custom_field['conditions'])) {
                $jsst_visibleLogic = 'AND';
                if(isset($jsst_custom_field["match_type"]) && $jsst_custom_field["match_type"] == 'any'){
                    $jsst_visibleLogic = 'OR';
                }

                foreach ($jsst_custom_field['conditions'] as $jsst_groupIndex => $jsst_group) {
                    $jsst_fieldtype = '';
                    if ($jsst_group['item_key'] == 'ticket_content' || $jsst_group['item_key'] == 'ticket_product_id') {
                        continue;
                    }
                    if ($jsst_group['item_key'] == 'ticket_client_priority') {
                        $jsst_item_key = 'priority';
                        $jsst_group['value'];
                        $jsst_value = $this->getTicketPriorityIdByFluentSupport($jsst_group['value']);
                        $jsst_fieldtype = 'priority';
                    } elseif ($jsst_group['item_key'] == 'ticket_title') {
                        $jsst_item_key = 'subject';
                        $jsst_value = $jsst_group['value'];
                        $jsst_fieldtype = 'subject';
                    } else {
                        // $jsst_item_key = $jsst_group['item_key'];
                        if (empty($jsst_group['item_key'])) {
                            continue;
                        }
                        $jsst_item_key = $this->jsst_fc_ticket_cf[$jsst_group['item_key']];
                        $jsst_fieldtype = $this->checkTypeOfTheField($jsst_item_key);
                        if ($jsst_fieldtype == 'textarea') {
                            continue;
                        }
                        $jsst_value = $jsst_group['value'];
                    }
                    if ($jsst_custom_field["slug"] == 'ticket_client_priority') {
                        $jsst_slug = 'priority';
                    } elseif ($jsst_custom_field["slug"] == 'ticket_title') {
                        $jsst_slug = 'subject';
                    } else {
                        $jsst_slug = $this->jsst_fc_ticket_cf[$jsst_custom_field["slug"]];
                    }
                    
                    $jsst_visibledata["visibleParentField"][] = $jsst_slug;
                    $jsst_visibledata["visibleParent"][] = $jsst_item_key;
                    $jsst_visibledata["visibleCondition"][] = $this->mapOperatorToConditionCodeForFluentSupport($jsst_group['operator'], $jsst_fieldtype);
                    $jsst_visibledata["visibleValue"][] = $jsst_value;
                    $jsst_visibledata["visibleLogic"][] = $jsst_visibleLogic;
                }
            }

            $jsst_option_values = [];
            if(isset($jsst_custom_field["options"])){
                foreach($jsst_custom_field["options"] as $jsst_key => $jsst_value){
                    $jsst_option_values[] = $jsst_value;
                }
            }

            // Prepare field data for import

            $jsst_fieldOrderingData = [
                "id" => $jsst_jshd_field->id,
                "field" => $jsst_jshd_field->field,
                "fieldtitle" => $jsst_jshd_field->fieldtitle,
                "ordering" => $jsst_jshd_field->ordering,
                "section" => $jsst_jshd_field->section,
                "placeholder" => $jsst_jshd_field->placeholder,
                "description" => $jsst_jshd_field->description,
                "fieldfor" => $jsst_jshd_field->fieldfor,
                "published" => $jsst_jshd_field->published,
                "sys" => $jsst_jshd_field->sys,
                "cannotunpublish" => $jsst_jshd_field->cannotunpublish,
                "required" => $jsst_jshd_field->required,
                "size" => $jsst_jshd_field->size,
                "maxlength" => $jsst_jshd_field->maxlength,
                "cols" => $jsst_jshd_field->cols,
                "rows" => $jsst_jshd_field->rows,
                "isuserfield" => $jsst_jshd_field->isuserfield,
                "userfieldtype" => $jsst_jshd_field->userfieldtype,
                "depandant_field" => $jsst_jshd_field->depandant_field,
                "visible_field" => $jsst_jshd_field->visible_field,
                "showonlisting" => $jsst_jshd_field->showonlisting,
                "cannotshowonlisting" => $jsst_jshd_field->cannotshowonlisting,
                "search_user" => $jsst_jshd_field->search_user,
                "search_admin" => $jsst_jshd_field->search_admin,
                "cannotsearch" => $jsst_jshd_field->cannotsearch,
                "isvisitorpublished" => $jsst_jshd_field->isvisitorpublished,
                "search_visitor" => $jsst_jshd_field->search_visitor,
                "multiformid" => $jsst_jshd_field->multiformid,
                "userfieldparams" => $jsst_jshd_field->userfieldparams,
                "visibleparams" => $jsst_jshd_field->visibleparams,
                "readonly" => $jsst_jshd_field->readonly,
                "adminonly" => $jsst_jshd_field->adminonly,
                "defaultvalue" => $jsst_jshd_field->defaultvalue,
                "values" => $jsst_option_values,
                "visibleParent" => $jsst_visibledata["visibleParent"],
                "visibleValue" => $jsst_visibledata["visibleValue"],
                "visibleCondition" => $jsst_visibledata["visibleCondition"],
                "visibleLogic" => $jsst_visibledata["visibleLogic"],
            ];

            // Store field in SupportCandy
            $jsst_record_saved = JSSTincluder::getJSModel('fieldordering')->storeUserField($jsst_fieldOrderingData);
        }
    }

    private function checkTypeOfTheField($jsst_field){
        
        $jsst_query = "
            SELECT userfieldtype FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering`
                WHERE LOWER(field) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_field)))."'";
        $jsst_userfieldtype = jssupportticket::$_db->get_var($jsst_query);
        
        return $jsst_userfieldtype ? $jsst_userfieldtype : null;
    }

    private function getTicketCustomFieldId($jsst_fieldtitle){
        
        $jsst_query = "
            SELECT field FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering`
                WHERE LOWER(fieldtitle) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_fieldtitle)))."'";
        $jsst_jshd_field_id = jssupportticket::$_db->get_var($jsst_query);
        
        return $jsst_jshd_field_id ? $jsst_jshd_field_id : null;
    }

    private function mapOperatorToConditionCodeForFluentSupport ($jsst_operator, $jsst_type) {
        $jsst_operator = strtoupper(jssupportticketphplib::JSST_trim($jsst_operator));
        $jsst_isComplex = false;

        if (!empty($jsst_type)) {
            $jsst_complexTypes = ['combo', 'checkbox', 'radio', 'multiple','priority'];
            $jsst_isComplex = !in_array($jsst_type, $jsst_complexTypes);
        }

        switch (strtoupper($jsst_operator)) {
            case '=':
            case 'CONTAINS':
                return $jsst_isComplex ? "2" : "1";
            case '!=':
            case 'NOT_CONTAINS':
                return $jsst_isComplex ? "3" : "0";
            default:
                return $jsst_isComplex ? "4" : "0"; // Fallback or unsupported operator
        }
    }

    private function getFluentSupportTicketNotes($jsst_jshd_ticket_id, $jsst_fs_ticket_id, $jsst_attachmentdir){
        $jsst_query = "SELECT conversation.*
                    FROM `" . jssupportticket::$_db->prefix . "fs_conversations` AS conversation
                    WHERE conversation.ticket_id = ".$jsst_fs_ticket_id."
                    AND conversation.conversation_type = 'note'
                    ORDER BY conversation.id ASC";
                    
        $jsst_conversations = jssupportticket::$_db->get_results($jsst_query);
        foreach($jsst_conversations AS $jsst_conversation){

            $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` WHERE wpuid = ".$jsst_conversation->person_id;
            $jsst_agentid = $jsst_jshd_user_id = jssupportticket::$_db->get_var($jsst_query);
            $jsst_filename = $this->getFluentSupportNoteAttachments($jsst_fs_ticket_id, $jsst_conversation->id, $jsst_attachmentdir);

            $jsst_replyData = [
                "id" => "",
                "ticketid" => $jsst_jshd_ticket_id,
                "staffid" => $jsst_agentid,
                "title" => jssupportticketphplib::JSST_strip_tags($jsst_conversation->content),
                "note" => $jsst_conversation->content,
                "status" => "1",
                "created" => $jsst_conversation->created_at,
                "filename" => $jsst_filename,
                "filesize" => 5334
            ];
            $jsst_row = JSSTincluder::getJSTable('note');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_replyData);// remove slashes with quotes.
            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_data)) {
                $jsst_error = 1;
            }
            if (!$jsst_row->store()) {
                $jsst_error = 1;
            }
            $jsst_jshd_ticket_note_id = $jsst_row->id;
        }
    }

    private function getFluentSupportTicketReplies($jsst_jshd_ticket_id, $jsst_fs_ticket_id, $jsst_attachmentdir){
        $jsst_query = "SELECT conversation.*
                    FROM `" . jssupportticket::$_db->prefix . "fs_conversations` AS conversation
                    WHERE conversation.ticket_id = ".$jsst_fs_ticket_id."
                    AND conversation.conversation_type = 'response'
                    ORDER BY conversation.id ASC";
                    
        $jsst_conversations = jssupportticket::$_db->get_results($jsst_query);
        foreach($jsst_conversations AS $jsst_conversation){
            $jsst_userinfo = $this->getFluentSupportTicketCustomerInfo($jsst_conversation->person_id);
            $jsst_uid = $jsst_userinfo["jshd_uid"];
            $jsst_name = $jsst_userinfo["customer_name"];
            if(empty($jsst_userinfo["jshd_uid"])){

                $jsst_agentid = $this->getTicketAgentIDByFluentSupport($jsst_conversation->person_id);
                if($jsst_agentid){
                    $jsst_query = "SELECT agent.*
                                FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS agent
                                WHERE agent.id = ".$jsst_agentid.";";
                                
                    $jsst_agent = jssupportticket::$_db->get_row($jsst_query);
                    $jsst_uid = $jsst_agent->uid;
                    $jsst_name = $jsst_agent->firstname;
                    if($jsst_agent->lastname) $jsst_name = $jsst_name. " ". $jsst_agent->lastname;
                }
            }

            $jsst_replyData = [
                "id" => "",
                "uid" => $jsst_uid,
                "ticketid" => $jsst_jshd_ticket_id,
                "name" => $jsst_name,
                "message" => $jsst_conversation->content,
                "staffid" => "",
                "rating" => "",
                "status" => "1",
                "created" => $jsst_conversation->created_at,
                "ticketviaemail" => "",
                "viewed_by" => "",
                "viewed_on" => ""
            ];
            $jsst_row = JSSTincluder::getJSTable('replies');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_replyData);// remove slashes with quotes.
            $jsst_error = 0;
            if (!$jsst_row->bind($jsst_data)) {
                $jsst_error = 1;
            }
            if (!$jsst_row->store()) {
                $jsst_error = 1;
            }
            $jsst_jshd_ticket_reply_id = $jsst_row->id;
            $this->getFluentSupportReplyAttachments($jsst_jshd_ticket_id, $jsst_jshd_ticket_reply_id, $jsst_fs_ticket_id, $jsst_conversation->id, $jsst_attachmentdir);
        }
    }

    private function getFluentSupportNoteAttachments($jsst_fs_ticket_id, $jsst_fs_ticket_reply_id, $jsst_attachmentdir){
        // Using prepare for secure query with your prefix format
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT attachment.*
                FROM `" . jssupportticket::$_db->prefix . "fs_attachments` AS attachment
                WHERE attachment.ticket_id = %d AND attachment.conversation_id = %d
                ORDER BY attachment.id ASC",
            $jsst_fs_ticket_id,
            $jsst_fs_ticket_reply_id
        );
                    
        $jsst_attachment = jssupportticket::$_db->get_row($jsst_query);

        if (empty($jsst_attachment)) return;

        // --- INITIALIZE WP_FILESYSTEM ---
        // Use your specific hook to include file.php
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = trailingslashit($jsst_upload_path) . $jsst_datadirectory . "/attachmentdata/ticket/" . sanitize_file_name($jsst_attachmentdir);

        // Use $jsst_wp_filesystem methods instead of direct calls
        if (!$jsst_wp_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }

        $jsst_safe_filename = sanitize_file_name($jsst_attachment->title);
        $jsst_source = $jsst_attachment->file_path;
        $jsst_destination = $jsst_path . "/" . $jsst_safe_filename;
        $jsst_destination_new_name = $jsst_path . "/" . $jsst_attachment->title;

        // Use $jsst_wp_filesystem->exists instead of file_exists
        if (!$jsst_wp_filesystem->exists($jsst_source)) {
            return '';
        }

        // Use global $wp_filesystem for copy
        $jsst_result = $jsst_wp_filesystem->copy($jsst_source, $jsst_destination, true);
        if (!$jsst_result) {
            return '';
        }

        // Replaced rename() with $jsst_wp_filesystem->move()
        $jsst_wp_filesystem->move($jsst_destination, $jsst_destination_new_name, true);

        return $jsst_safe_filename;
    }

    private function getFluentSupportReplyAttachments($jsst_jshd_ticket_id, $jsst_jshd_ticket_reply_id, $jsst_fs_ticket_id, $jsst_fs_ticket_reply_id, $jsst_attachmentdir){
        // Use prepare for SQL security with your prefix format
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT attachment.*
                FROM `" . jssupportticket::$_db->prefix . "fs_attachments` AS attachment
                WHERE attachment.ticket_id = %d AND attachment.conversation_id = %d
                ORDER BY attachment.id ASC",
            $jsst_fs_ticket_id,
            $jsst_fs_ticket_reply_id
        );
                    
        $jsst_attachments = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_attachments)) return;

        // --- INITIALIZE WP_FILESYSTEM ---
        // Using your custom action to load file.php
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = trailingslashit($jsst_upload_path) . $jsst_datadirectory . "/attachmentdata/ticket/" . sanitize_file_name($jsst_attachmentdir);

        // Use $jsst_wp_filesystem->exists instead of $jsst_filesystem->exists
        if (!$jsst_wp_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }

        foreach ($jsst_attachments as $jsst_attachment) {
            $jsst_safe_filename = sanitize_file_name($jsst_attachment->title);
            $jsst_source = $jsst_attachment->file_path;
            $jsst_destination = $jsst_path . "/" . $jsst_safe_filename;
            $jsst_destination_new_name = $jsst_path . "/" . $jsst_attachment->title;

            // Replaced filesize($jsst_source) with $jsst_wp_filesystem->size($jsst_source)
            $jsst_file_size = $jsst_wp_filesystem->exists($jsst_source) ? $jsst_wp_filesystem->size($jsst_source) : "";

            $jsst_attachmentData = [
                "id" => "",
                "ticketid" => $jsst_jshd_ticket_id,
                "replyattachmentid" => $jsst_jshd_ticket_reply_id,
                "filesize" => $jsst_file_size,
                "filename" => $jsst_safe_filename,
                "filekey" => "",
                "deleted" => "",
                "status" => "1",
                "created" => $jsst_attachment->created_at
            ];

            $jsst_row = JSSTincluder::getJSTable('attachments');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_attachmentData);

            if (!$jsst_row->bind($jsst_data)) {
                continue; // Skip if binding fails
            }
            if (!$jsst_row->store()) {
                continue; // Skip if storing fails
            }

            // Use $jsst_wp_filesystem->exists instead of file_exists
            if (!$jsst_wp_filesystem->exists($jsst_source)) {
                continue;
            }

            // Use global $wp_filesystem for copy and move
            $jsst_result = $jsst_wp_filesystem->copy($jsst_source, $jsst_destination, true);
            if (!$jsst_result) {
                continue;
            }

            // Replaced rename() with move()
            $jsst_wp_filesystem->move($jsst_destination, $jsst_destination_new_name, true);         
        }
    }

    private function getFluentSupportTicketAttachments($jsst_jshd_ticket_id, $jsst_fs_ticket_id, $jsst_attachmentdir){
        // Use prepare for SQL security
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT attachment.*
                FROM `" . jssupportticket::$_db->prefix . "fs_attachments` AS attachment
                WHERE attachment.ticket_id = %d AND attachment.conversation_id IS NULL
                ORDER BY attachment.id ASC",
            $jsst_fs_ticket_id
        );
                    
        $jsst_attachments = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_attachments)) return;

        // --- INITIALIZE WP_FILESYSTEM ---
        // Using your custom action to load file.php
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        $jsst_upload_dir = wp_upload_dir();
        $jsst_upload_path = $jsst_upload_dir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = trailingslashit($jsst_upload_path) . $jsst_datadirectory . "/attachmentdata/ticket/" . sanitize_file_name($jsst_attachmentdir);

        // Use $jsst_wp_filesystem->exists instead of $jsst_filesystem->exists
        if (!$jsst_wp_filesystem->exists($jsst_path)) {
            wp_mkdir_p($jsst_path);
        }

        foreach ($jsst_attachments as $jsst_attachment) {
            $jsst_safe_filename = sanitize_file_name($jsst_attachment->title);
            $jsst_source = $jsst_attachment->file_path;
            $jsst_destination = $jsst_path . "/" . $jsst_safe_filename;
            $jsst_destination_new_name = $jsst_path . "/" . $jsst_attachment->title;

            // Use $jsst_wp_filesystem->size for compliance
            $jsst_file_size = $jsst_wp_filesystem->exists($jsst_source) ? $jsst_wp_filesystem->size($jsst_source) : "";

            $jsst_attachmentData = [
                "id" => "",
                "ticketid" => $jsst_jshd_ticket_id,
                "replyattachmentid" => 0,
                "filesize" => $jsst_file_size,
                "filename" => $jsst_safe_filename,
                "filekey" => "",
                "deleted" => "",
                "status" => "1",
                "created" => $jsst_attachment->created_at
            ];

            $jsst_row = JSSTincluder::getJSTable('attachments');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_attachmentData);

            if (!$jsst_row->bind($jsst_data)) {
                continue;
            }
            if (!$jsst_row->store()) {
                continue;
            }

            // Use $jsst_wp_filesystem->exists instead of file_exists
            if (!$jsst_wp_filesystem->exists($jsst_source)) {
                continue;
            }

            // Use global $wp_filesystem for copy
            $jsst_result = $jsst_wp_filesystem->copy($jsst_source, $jsst_destination, true);
            if (!$jsst_result) {
                continue;
            }

            // Replaced rename() with $jsst_wp_filesystem->move()
            $jsst_wp_filesystem->move($jsst_destination, $jsst_destination_new_name, true);         
        }
    }

    private function getFluentSupportTicketActivityLog($jsst_jshd_ticket_id, $jsst_fs_ticket_id) {
        $jsst_fs_ticket_id = intval($jsst_fs_ticket_id);
        $jsst_jshd_ticket_id = intval($jsst_jshd_ticket_id);

        if ($jsst_fs_ticket_id <= 0 || $jsst_jshd_ticket_id <= 0) return;

        $jsst_query = "
            SELECT * FROM `" . jssupportticket::$_db->prefix . "fs_activities`
            WHERE  object_type = 'ticket' AND object_id = ".$jsst_fs_ticket_id."
            ORDER BY id DESC";

        $jsst_threads = jssupportticket::$_db->get_results($jsst_query);
        if (empty($jsst_threads)) return;

        foreach ($jsst_threads as $jsst_thread) {
            $jsst_ticketid = $jsst_jshd_ticket_id;

            // Get user information
            $jsst_userinfo = $this->getFluentSupportTicketCustomerInfo($jsst_thread->person_id);
            $jsst_currentUserName = !empty($jsst_userinfo['customer_name']) 
                ? esc_html($jsst_userinfo['customer_name']) 
                : esc_html(__('Guest', 'js-support-ticket'));

            $jsst_messagetype = __('Successfully', 'js-support-ticket');
            $jsst_eventtype = jssupportticketphplib::JSST_str_replace("fluent_support/","",$jsst_thread->event_type);
            $jsst_message = jssupportticketphplib::JSST_strip_tags($jsst_thread->description);
            

            if (!empty($jsst_eventtype) && !empty($jsst_message)) {
                JSSTincluder::getJSModel('tickethistory')->addActivityLog(
                    $jsst_ticketid, 1, esc_html($jsst_eventtype), esc_html($jsst_message), esc_html($jsst_messagetype)
                );
            }
        }
    }

    private function getFluentSupportTicketStaffTime($jsst_jshd_ticket_id, $jsst_fs_ticket_id) {
        $jsst_fs_ticket_id = intval($jsst_fs_ticket_id);
        $jsst_jshd_ticket_id = intval($jsst_jshd_ticket_id);
        if ($jsst_fs_ticket_id <= 0 || $jsst_jshd_ticket_id <= 0) return;

        // Get all timer logs for the given SupportCandy ticket
        $jsst_query = "
            SELECT * FROM `" . jssupportticket::$_db->prefix . "fs_time_tracks`
            WHERE ticket_id = ".$jsst_fs_ticket_id;
        $jsst_timers = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_timers)) return;

        foreach ($jsst_timers as $jsst_timer) {
            // Get HelpDesk staff ID from FluentSupport agent ID
            $jsst_staffid = $this->getJshdAgentIdByFSAgentId($jsst_timer->agent_id);

            $jsst_created = $jsst_timer->created_at;

            $jsst_timer_minutes = $jsst_timer->working_minutes;
            if ($jsst_timer_minutes <= 0) continue;

            $jsst_timer_seconds = $jsst_timer_minutes * 60;
            // Conflict detection
            $jsst_created_dt = new DateTime($jsst_created);
            $jsst_now = new DateTime();
            $jsst_interval_to_now = $jsst_created_dt->diff($jsst_now);
            $jsst_systemtime = ($jsst_interval_to_now->days * 86400) + ($jsst_interval_to_now->h * 3600) + ($jsst_interval_to_now->i * 60) + $jsst_interval_to_now->s;

            $jsst_conflict = ($jsst_timer_seconds > $jsst_systemtime) ? 1 : 0;

            // Prepare data
            $jsst_data = [
                'staffid' => $jsst_staffid,
                'ticketid' => $jsst_jshd_ticket_id,
                'referencefor' => 1,
                'referenceid' => 0,
                'usertime' => $jsst_timer_seconds,
                'systemtime' => $jsst_systemtime,
                'conflict' => $jsst_conflict,
                'description' => $jsst_timer->message,
                'timer_edit_desc' => $jsst_timer->message,
                'status' => 1,
                'created' => $jsst_created
            ];

            $jsst_row = JSSTincluder::getJSTable('timetracking');
            $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);

            if (!$jsst_row->bind($jsst_data) || !$jsst_row->store()) {
                // optionally log or count the failure
                continue;
            }
        }
    }

    private function getJshdAgentIdByFSAgentId($jsst_fs_agent_id) {
        // Sanitize and validate input
        $jsst_fs_agent_id = intval($jsst_fs_agent_id);
        if ($jsst_fs_agent_id <= 0) return null;

        // Secure SQL query using prepare()
        $jsst_query = "
            SELECT agent.*
            FROM `" . jssupportticket::$_db->prefix . "fs_persons` AS fs_agent
            INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user
                ON user.wpuid = fs_agent.user_id
            INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS agent
                ON agent.uid = user.id
            WHERE fs_agent.person_type = 'agent' AND fs_agent.id = " . esc_sql($jsst_fs_agent_id) . "
            LIMIT 1
        ";

        $jsst_jshd_agent = jssupportticket::$_db->get_row($jsst_query);

        return $jsst_jshd_agent ?: null;
    }

    private function getFluentSupportTicketCustomerInfo($jsst_customerId) {
        // Sanitize and validate customer ID
        $jsst_customerId = intval($jsst_customerId);
        if ($jsst_customerId <= 0) {
            return [
                "jshd_uid" => "",
                "customer_name" => "",
                "customer_email" => ""
            ];
        }

        // Prepare secure query
        $jsst_query = "
            SELECT CONCAT(customer.first_name, ' ', customer.last_name) AS name, customer.email, user.id AS jshd_uid
            FROM `" . jssupportticket::$_db->prefix . "fs_persons` AS customer
            INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user
                ON user.wpuid = customer.user_id
            WHERE customer.id = " . esc_sql($jsst_customerId) . "
            AND customer.person_type = 'customer'
            LIMIT 1
        ";

        $jsst_data = jssupportticket::$_db->get_row($jsst_query);

        return [
            "jshd_uid"       => $jsst_data->jshd_uid ?? "",
            "customer_name"  => $jsst_data->name ?? "",
            "customer_email" => $jsst_data->email ?? ""
        ];
    }

    private function getTicketAgentIdByFluentSupport($jsst_customerId) {
        // Validate customer ID
        $jsst_customerId = intval($jsst_customerId);
        if ($jsst_customerId <= 0) {
            return null;
        }

        // Get mapped user info
        $jsst_query = "SELECT agent.id
                    FROM `" . jssupportticket::$_db->prefix . "fs_persons` AS person
            INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user
                ON user.wpuid = person.user_id
            INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS agent
                ON agent.uid = user.id
                    WHERE person.id = " . esc_sql($jsst_customerId) . "
                    AND person.person_type = 'agent';";
        $jsst_jshd_agent = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_jshd_agent ?: null;
    }

    private function getTicketProductIdByFluentSupport($jsst_productId){
        // Sanitize and validate input
        $jsst_productId = intval($jsst_productId);
        if ($jsst_productId <= 0) return null;

        // Fetch product from source table
        $jsst_query = "
            SELECT title
            FROM `" . jssupportticket::$_db->prefix . "fs_products` 
            WHERE id = ".$jsst_productId;
        $jsst_product_name = jssupportticket::$_db->get_var($jsst_query);

        if (empty($jsst_product_name)) return null;

        // Find corresponding product in destination table
        
        $jsst_name = $jsst_product_name;
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_products`
                WHERE LOWER(product) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_name)))."'";;
        $jsst_jshd_product_id = jssupportticket::$_db->get_var($jsst_query);
        
        return $jsst_jshd_product_id ? (int)$jsst_jshd_product_id : null;
    }

    private function getTicketPriorityIdByFluentSupport($jsst_prioritName) {
        
        // Find corresponding priority in destination table
        $jsst_query = "
            SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` 
                WHERE LOWER(priority) = '".jssupportticketphplib::JSST_strtolower(jssupportticketphplib::JSST_trim(esc_sql($jsst_prioritName)))."'";
        $jsst_jshd_priority_id = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_jshd_priority_id ? (int)$jsst_jshd_priority_id : null;
    }

    private function importFluentSupportUsers() {
        // check if user already processed for import
        $jsst_imported_users = array();
        $jsst_imported_users_json = get_option('js_support_ticket_fluent_support_data_users');
        if(!empty($jsst_imported_users_json)){
            $jsst_imported_users = json_decode($jsst_imported_users_json,true);
        }

        // Fetch all customers
        $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "fs_persons` WHERE person_type = 'customer' OR person_type = 'agent'";
        $jsst_customers = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_customers)) return;

        foreach ($jsst_customers as $jsst_customer) {
            if(!empty($jsst_customer->user_id)){
                $jsst_wpuid = intval($jsst_customer->user_id);
            }else{
                $jsst_query = "SELECT user.ID
                    FROM `" . jssupportticket::$_db->prefix . "users` AS user
                    WHERE user.user_email = '".esc_sql($jsst_customer->email)."'";
                $jsst_user = jssupportticket::$_db->get_row($jsst_query);
                if($jsst_user) $jsst_wpuid = intval($jsst_user->ID);
            }
            if (empty($jsst_wpuid)) {
                $this->jsst_fluent_support_import_count['user']['skipped']++;
                continue;
            }
            $jsst_customer_id = intval($jsst_customer->id);
            $jsst_name        = sanitize_text_field($jsst_customer->first_name ?? '');
            if($jsst_customer->last_name) $jsst_name = $jsst_name." ".sanitize_text_field($jsst_customer->last_name);
            $jsst_email       = sanitize_email($jsst_customer->email ?? '');

            // Skip if already imported
            if (in_array($jsst_customer_id, $jsst_imported_users, true)) {
                $this->jsst_fluent_support_import_count['user']['skipped']++;
                continue;
            }

            // Check if user already exists
            $jsst_user_query = "SELECT user.*
                       FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user
                       WHERE user.wpuid = ".$jsst_wpuid;
            $jsst_existing_user = jssupportticket::$_db->get_row($jsst_user_query);

            if ($jsst_existing_user) {
                $this->jsst_fluent_support_import_count['user']['skipped']++;
                continue;
            }

            // Prepare data for new user
            $jsst_row = JSSTincluder::getJSTable('users');
            $jsst_data = [
                'id'            => '',
                'wpuid'         => $jsst_wpuid,
                'name'          => $jsst_name,
                'display_name'  => $jsst_name,
                'user_email'    => $jsst_email,
                'status'        => 1,
                'issocial'      => 0,
                'socialid'      => null,
                'autogenerated' => 0,
            ];

            // Attempt to save the new user
            $jsst_row->bind($jsst_data);
            if (!$jsst_row->store()) {
                $this->jsst_fluent_support_import_count['user']['failed']++;
                continue;
            }

            // Store successful import info
            $this->fluent_support_users_array[$jsst_customer_id] = $jsst_row->id;
            $this->jsst_fluent_support_user_ids[] = $jsst_customer_id;
            $this->jsst_fluent_support_import_count['user']['imported']++;
        }

        // Save list of imported user IDs
        if (!empty($this->jsst_fluent_support_user_ids)) {
            update_option('js_support_ticket_fluent_support_data_users', wp_json_encode(array_unique(array_merge($jsst_imported_users, $this->jsst_fluent_support_user_ids))));
        }
    }

    private function importFluentSupportAgents() {
        // check if user already processed for import
        $jsst_imported_agents = array();
        $jsst_imported_agent_json = get_option('js_support_ticket_fluent_support_data_agents');
        if(!empty($jsst_imported_agents_json)){
            $jsst_imported_agents = json_decode($jsst_imported_agents_json,true);
        }
        $jsst_query = "
            SELECT agent.*
            FROM `" . jssupportticket::$_db->prefix . "fs_persons` AS agent
            WHERE agent.person_type = 'agent';";
        $jsst_agents = jssupportticket::$_db->get_results($jsst_query);

        if($jsst_agents){
            foreach($jsst_agents AS $jsst_agent){
                // Failed if addon not installed
                if (!in_array('agent', jssupportticket::$_active_addons) ) {
                    $this->jsst_fluent_support_import_count['agent']['failed']++;
                    continue;
                }
                $jsst_wpuid = (int) $jsst_agent->user_id;
                // Skip if already imported
                if (in_array($jsst_wpuid, $jsst_imported_agents, true)) {
                    $this->jsst_fluent_support_import_count['agent']['skipped']++;
                    continue;
                }
                $jsst_first_name = $jsst_agent->first_name;
                $jsst_last_name = $jsst_agent->last_name;
                if($jsst_agent->status == "active") $jsst_agent_status = 1; else $jsst_agent_status = 0;

                $jsst_query = "SELECT user.*
                            FROM `" . jssupportticket::$_db->prefix . "users` AS user
                            WHERE user.id = " . $jsst_wpuid;
                $jsst_wpuser = jssupportticket::$_db->get_row($jsst_query);

                if(!$jsst_wpuser){
                    $this->jsst_fluent_support_import_count['agent']['failed'] += 1;
                    continue;
                }
                $jsst_js_user = JSSTincluder::getObjectClass('user')->getjssupportticketuidbyuserid($jsst_wpuid);
                if (!empty($jsst_js_user) && isset($jsst_js_user[0]->id)) {
                    $jsst_js_uid = (int)$jsst_js_user[0]->id;
                } else {
                    $this->jsst_fluent_support_import_count['agent']['failed']++;
                    continue;
                }

                $jsst_query = "SELECT staff.*
                            FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff
                            WHERE staff.uid = " . $jsst_js_uid;
                $jsst_staff = jssupportticket::$_db->get_row($jsst_query);

                if (!$jsst_staff) {
                    $jsst_roleid = $this->getAgentRoleIdByFluentSupport($jsst_wpuid);

                    $jsst_timestamp = date_i18n('Y-m-d H:i:s');

                    $jsst_data = [
                        'id'           => '',
                        'uid'          => $jsst_js_uid,
                        'groupid'      => '',
                        'roleid'       => $jsst_roleid,
                        'departmentid' => '',
                        'firstname'    => $jsst_first_name,
                        'lastname'     => $jsst_last_name,
                        'username'     => $jsst_wpuser->user_login,
                        'email'        => $jsst_wpuser->user_email,
                        'signature'    => '',
                        'isadmin'      => '',
                        'status'       => $jsst_agent_status,
                        'updated'      => $jsst_timestamp,
                        'created'      => $jsst_timestamp
                    ];

                    JSSTincluder::getJSModel('agent')->storeStaff($jsst_data);

                    $this->jsst_fluent_support_import_count['agent']['imported'] += 1;
                    $this->jsst_fluent_support_agent_ids[] = $jsst_wpuid;

                } else {
                    $this->jsst_fluent_support_import_count['agent']['skipped'] += 1;
                }
            }
            // Save list of imported agent IDs
            if (!empty($this->jsst_fluent_support_agent_ids)) {
                update_option('js_support_ticket_fluent_support_data_agents', wp_json_encode(array_unique(array_merge($jsst_imported_agents, $this->jsst_fluent_support_agent_ids))));
            }
        }
    }

    private function getAgentRoleIdByFluentSupport($jsst_id) {
        $jsst_capabilities = get_user_meta($jsst_id, jssupportticket::$_db->prefix . 'capabilities', true);
        $jsst_isAdmin = !empty($jsst_capabilities['administrator']);
        $jsst_output = [];

        // Define capability-to-permission mappings
        $jsst_capabilityPermissions = [
            'fst_manage_saved_replies' => [
                'Add Canned Response' => 75,
                'Edit Canned Response' => 76,
                'View Canned Response' => 77,
                'Delete Canned Response' => 78,
            ],
            'fst_view_all_reports' => [
                'View Agent Reports' => 59,
                'View Department Reports' => 60,
            ],
            'fst_sensitive_data' => [
                'View Credentials' => 67,
                'Delete Credentials' => 68,
                'Edit Credentials' => 69,
                'Add Credentials' => 70,
            ],
            'fst_delete_tickets' => [
                'Delete Ticket' => 12,
            ],
            'fst_assign_agents' => [
                'Assign Ticket To Agent' => 6,
            ],
            'fst_merge_tickets' => [
                'Ticket Merge' => 66,
            ],
            'fst_manage_unassigned_tickets' => [
                'All Tickets' => 61,
            ],
            'fst_manage_own_tickets' => [
                'Add Ticket' => 1,
                'Edit Ticket' => 2,
                'Close Ticket' => 3,
                'Reopen Ticket' => 4,
                'Reply Ticket' => 5,
                'Assign Ticket To Agent' => 6,
                'Ticket Department Transfer' => 7,
                'Mark Overdue' => 8,
                'Mark In Progress' => 9,
                'Change Ticket Priority' => 10,
                'Unban Email' => 11,
                'Delete Ticket' => 12,
                'Ban Email And Close Ticket' => 13,
                'Lock Ticket' => 14,
                'Attachment' => 15,
                'Post Internal Note' => 16,
                'Duedate Ticket' => 53,
                'View Ticket' => 54,
                'Release Ticket' => 55,
                'New Ticket Notification' => 56,
                'Allow Mail System' => 57,
                'Print Ticket' => 58,
                'Mark Non Premium' => 79,
                'Link To Paid Support' => 80,
                'Export Ticket' => 81,
                'Change Ticket Status' => 82,
                'Edit Own Time' => 62,
            ],
        ];

        // Loop through capabilities and add matching permissions
        foreach ($jsst_capabilityPermissions as $jsst_capKey => $jsst_permissions) {
            if (!empty($jsst_capabilities[$jsst_capKey]) || $jsst_isAdmin) {
                $jsst_output = array_merge($jsst_output, $jsst_permissions);
            }
        }

        $jsst_name = 'Fluent Support Agent ' . $jsst_id;

        $jsst_data = [
            'name'          => $jsst_name,
            'roleperdata'   => $jsst_output,
            'id'            => '',
            'created'       => '',
            'updated'       => '',
            'action'        => 'role_saverole',
            'form_request'  => 'jssupportticket',
            'save'          => 'Save Role',
        ];

        // Save the role and permissions
        JSSTincluder::getJSModel('role')->storeRole($jsst_data);

        // Retrieve role ID
        $jsst_query = 'SELECT id FROM `' . jssupportticket::$_db->prefix . 'js_ticket_acl_roles` WHERE name = "' . esc_sql($jsst_name) . '"';
        $jsst_id = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_id;
    }

    private function importFluentSupportProducts(){
        // check if product already processed for import
        $jsst_imported_products = array();
        $jsst_imported_products_json = get_option('js_support_ticket_fluent_support_data_products');
        if(!empty($jsst_imported_products_json)){
            $jsst_imported_products = json_decode($jsst_imported_products_json,true);
        }

        $jsst_query = "SELECT product.* FROM `" . jssupportticket::$_db->prefix . "fs_products` AS product;";
        $jsst_products = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_products)) return;
        
        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(product.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);

        foreach($jsst_products AS $jsst_product){
            // Skip if already imported
            if (in_array($jsst_product->id, $jsst_imported_products, true)) {
                $this->jsst_fluent_support_import_count['product']['skipped']++;
                continue;
            }

            $jsst_name = jssupportticketphplib::JSST_trim(jssupportticketphplib::JSST_strtolower($jsst_product->title));

            // Check if this product already exists in JS Support Ticket
            $jsst_check_query = "
                SELECT product.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product
                WHERE LOWER(product.product) = '".esc_sql($jsst_name) ."'
                LIMIT 1
            ";
            $jsst_jshd_product = jssupportticket::$_db->get_row($jsst_check_query);

            if(!$jsst_jshd_product){
                $jsst_row = JSSTincluder::getJSTable('products');
                
                $jsst_data = [
                    'id'               => '',
                    'product'         => $jsst_name,
                    'status'           => '1',
                    'ordering'         => $jsst_ordering
                ];

                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
                $jsst_row->bind($jsst_data);

                if (!$jsst_row->store()) {
                    $this->jsst_fluent_support_import_count['product']['failed'] += 1;
                } else {
                    $this->fluent_support_product_ids[] = $jsst_product->id;
                    $this->jsst_fluent_support_import_count['product']['imported'] += 1;
                }

                $jsst_ordering++;
            } else {
                $this->jsst_fluent_support_import_count['product']['skipped'] += 1;
            }
        }
        // Save list of imported product IDs
        if (!empty($this->fluent_support_product_ids)) {
            update_option('js_support_ticket_fluent_support_data_products', wp_json_encode(array_unique(array_merge($jsst_imported_products, $this->fluent_support_product_ids))));
        }
    }
    
    private function importFluentSupportPriorities() {
        // check if priority already processed for import
        $jsst_imported_priorities = array();
        $jsst_imported_priorities_json = get_option('js_support_ticket_fluent_support_data_priorities');
        if(!empty($jsst_imported_priorities_json)){
            $jsst_imported_priorities = json_decode($jsst_imported_priorities_json,true);
        }
        $jsst_priorities = array('Normal' => '#00a32a', 'Medium' => '#a5b2bd', 'Critical' => '#f06060');

        if (empty($jsst_priorities)) return;

        // Get highest current ordering value
        $jsst_query = "
            SELECT MAX(priority.ordering)
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority
        ";
        $jsst_ordering = (int) jssupportticket::$_db->get_var($jsst_query);

        foreach ($jsst_priorities as $jsst_key => $jsst_priority) {
            // Skip if already imported
            if (in_array($jsst_priority, $jsst_imported_priorities, true)) {
                $this->jsst_fluent_support_import_count['priority']['skipped']++;
                continue;
            }
            $jsst_name = jssupportticketphplib::JSST_trim(jssupportticketphplib::JSST_strtolower($jsst_key));

            // Check if this priority already exists in JS Support Ticket
            $jsst_check_query = "
                SELECT priority.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority
                WHERE LOWER(priority.priority) = '" . esc_sql($jsst_name) . "'
                LIMIT 1
            ";
            $jsst_jshd_priority = jssupportticket::$_db->get_row($jsst_check_query);

            if (!$jsst_jshd_priority) {
                $jsst_row = JSSTincluder::getJSTable('priorities');

                $jsst_data = [
                    'id'               => '',
                    'priority'         => $jsst_name,
                    'prioritycolour'   => $jsst_priority,
                    'priorityurgency'  => '',
                    'overduetypeid'    => 1,
                    'overdueinterval'  => 7,
                    'ordering'         => $jsst_ordering,
                    'status'           => '1',
                    'isdefault'        => '0',
                    'ispublic'         => '1'
                ];

                $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
                $jsst_row->bind($jsst_data);

                if (!$jsst_row->store()) {
                    $this->jsst_fluent_support_import_count['priority']['failed'] += 1;
                } else {
                    $this->jsst_fluent_support_priority_ids[] = $jsst_priority;
                    $this->jsst_fluent_support_import_count['priority']['imported'] += 1;
                }

                $jsst_ordering++;
            } else {
                $this->jsst_fluent_support_import_count['priority']['skipped'] += 1;
            }
        }
        // Save list of imported priority IDs
        if (!empty($this->jsst_fluent_support_priority_ids)) {
            update_option('js_support_ticket_fluent_support_data_priorities', wp_json_encode(array_unique(array_merge($jsst_imported_priorities, $this->jsst_fluent_support_priority_ids))));
        }
    }

    private function importFluentSupportPremades() {
        // check if premade already processed for import
        $jsst_imported_premades = array();
        $jsst_imported_premades_json = get_option('js_support_ticket_fluent_support_data_premades');
        if(!empty($jsst_imported_premades_json)){
            $jsst_imported_premades = json_decode($jsst_imported_premades_json,true);
        }
        $jsst_query = "
            SELECT canned_reply.*
            FROM `" . jssupportticket::$_db->prefix . "fs_saved_replies` AS canned_reply
        ";
        $jsst_canned_replies = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_canned_replies)) return;

        foreach ($jsst_canned_replies as $jsst_canned_reply) {
            $jsst_title = jssupportticketphplib::JSST_trim(jssupportticketphplib::JSST_strtolower($jsst_canned_reply->title));
            // Failed if addon not installed
            if (!in_array('cannedresponses', jssupportticket::$_active_addons) ) {
                $this->jsst_fluent_support_import_count['canned response']['failed']++;
                continue;
            }
            // Skip if already imported
            if (in_array($jsst_canned_reply->id, $jsst_imported_premades, true)) {
                $this->jsst_fluent_support_import_count['canned response']['skipped']++;
                continue;
            }
            // Skip if no department id
            if (empty($jsst_departmentid)) {
                $this->jsst_fluent_support_import_count['canned response']['skipped']++;
                continue;
            }
            // Check if this premade already exists in JS Support Ticket
            $jsst_check_query = "
                SELECT premade.*
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_department_message_premade` AS premade
                WHERE LOWER(premade.title) = '" . esc_sql($jsst_title) . "'
                LIMIT 1
            ";
            $jsst_jshd_canned_reply = jssupportticket::$_db->get_row($jsst_check_query);

            if (!$jsst_jshd_canned_reply) {
                $jsst_departmentid = '';
                // Step 1: Get default department
                $jsst_department_query = "
                    SELECT department.id 
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                    WHERE department.isdefault = 1
                    LIMIT 1
                ";
                $jsst_department = jssupportticket::$_db->get_row($jsst_department_query);

                // Step 2: If no default found, get the first department
                if (!$jsst_department) {
                    $jsst_department_query = "
                        SELECT department.id 
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department
                        ORDER BY department.id ASC
                        LIMIT 1
                    ";
                    $jsst_department = jssupportticket::$_db->get_row($jsst_department_query);
                }

                // Step 3: If still no department found, insert 'Support' and get its ID
                if (!$jsst_department) {
                    $jsst_row = JSSTincluder::getJSTable('departments');

                        $jsst_updated = date_i18n('Y-m-d H:i:s');
                        $jsst_created = date_i18n('Y-m-d H:i:s');

                        $jsst_data = [
                            'id'              => '',
                            'emailid'         => '1',
                            'departmentname'  => 'Support',
                            'ordering'        => 0,
                            'status'          => '1',
                            'isdefault'       => '0',
                            'ispublic'        => '1',
                            'updated'         => $jsst_updated,
                            'created'         => $jsst_created
                        ];

                        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);
                        $jsst_row->bind($jsst_data);

                        if ($jsst_row->store()) {
                            $jsst_departmentid = $jsst_row->id;
                        }
                } else {
                    $jsst_departmentid = $jsst_department->id;
                }

                // Prepare canned response data
                $jsst_row = JSSTincluder::getJSTable('cannedresponses');
                $jsst_updated = date_i18n('Y-m-d H:i:s');

                $jsst_data = [
                    'id'          => '',
                    'departmentid'=> $jsst_departmentid,
                    'title'       => $jsst_canned_reply->title,
                    'answer'      => $jsst_canned_reply->content,
                    'status'      => '1',
                    'updated'     => $jsst_updated,
                    'created'     => $jsst_canned_reply->created_at
                ];

                $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);
                $jsst_data['answer'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($jsst_data['answer']);
                $jsst_data = JSSTincluder::getJSModel('jssupportticket')->stripslashesFull($jsst_data);

                $jsst_row->bind($jsst_data);
                if (!$jsst_row->store()) {
                    $this->jsst_fluent_support_import_count['canned response']['failed'] += 1;
                } else {
                    $this->jsst_fluent_support_premade_ids[] = $jsst_canned_reply->id;
                    $this->jsst_fluent_support_import_count['canned response']['imported'] += 1;
                }
            } else {
                $this->jsst_fluent_support_import_count['canned response']['skipped'] += 1;
            }
        }

        // Save list of imported premade IDs
        if (!empty($this->jsst_fluent_support_premade_ids)) {
            update_option('js_support_ticket_fluent_support_data_premades', wp_json_encode(array_unique(array_merge($jsst_imported_premades, $this->jsst_fluent_support_premade_ids))));
        }
    }

    function getFluentSupportDataStats($jsst_count_for) {
        // Only FluentSupport (count_for = 1)
        if ($jsst_count_for != 3) return;

        // Check if FluentSupport is active
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (!is_plugin_active('fluent-support/fluent-support.php')) {
            return new WP_Error('jsst_inactive', 'FluentSupport is not active.');
        }

        $jsst_entity_counts = [];

        // Users
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_persons'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "fs_persons` WHERE person_type = 'customer' OR person_type = 'agent'";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['user'] = $jsst_count;
        }

        // Agents
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_persons'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "fs_persons` AS agent
            WHERE agent.person_type = 'agent';";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['agent'] = $jsst_count;
        }

        // Priorities
        $jsst_entity_counts['priority'] = 3;

        // Canned Responses
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_saved_replies'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "fs_saved_replies`";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['canned response'] = $jsst_count;
        }

        // Products
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_products'")) {
            $jsst_query = "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "fs_products`";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['product'] = $jsst_count;
        }

        // Custom Ticket Fields
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_meta'")) {
            $jsst_query = "
            SELECT * FROM `" . jssupportticket::$_db->prefix . "fs_meta`
            WHERE object_type = 'option' AND `key` = '_ticket_custom_fields';";
            $jsst_custom_fields_serializeed = jssupportticket::$_db->get_row($jsst_query);
            if (!empty($jsst_custom_fields_serializeed)) {
                $jsst_custom_fields = unserialize($jsst_custom_fields_serializeed->value);
                $jsst_count = count($jsst_custom_fields);
                if ($jsst_count > 0) $jsst_entity_counts['field'] = $jsst_count;
            }
        }

        // Tickets with type 'report'
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . jssupportticket::$_db->prefix . "fs_tickets'")) {
            $jsst_query = "SELECT COUNT(DISTINCT tickets.id)
                FROM `" . jssupportticket::$_db->prefix . "fs_tickets` AS tickets";
            $jsst_count = (int) jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_count > 0) $jsst_entity_counts['ticket'] = $jsst_count;
        }

        jssupportticket::$jsst_data['entity_counts'] = $jsst_entity_counts;
    }
}

?>
