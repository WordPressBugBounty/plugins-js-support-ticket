<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTjssupportticketModel {

    function getControlPanelData() {

        //determine user
        $user_is = 'unknown';
        if(JSSTincluder::getObjectClass('user')->isguest()){
            $user_is = 'visitor';
        }else{
            if(in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                $user_is = 'agent';
            }else{
                $user_is = 'user';
            }
        }
        //check if any addon is installed
        $addon_are_installed = !empty(jssupportticket::$_active_addons) ? true : false;

        if( $user_is == 'agent' ){

            $uid = JSSTincluder::getObjectClass('user')->uid();
            $staffid = JSSTincluder::getJSModel('agent')->getStaffId($uid);

            $tickets = $this->getAgentLatestTicketsForCp($staffid);
            if($tickets){
                jssupportticket::$_data[0]['agent-tickets'] = $tickets;
            }

            $ticketStats = $this->getAgentTicketStats($staffid);
            if($ticketStats){
                jssupportticket::$_data[0]['count'] = $ticketStats;
            }

            //data for graph
            $this->getAgentCpChartData($staffid);

        }

        if( $user_is == 'user' ){
            $uid = JSSTincluder::getObjectClass('user')->uid();

            $tickets = $this->getUserLatestTicketsForCp($uid);
            if($tickets){
                jssupportticket::$_data[0]['user-tickets'] = $tickets;
            }

            $ticketStats = $this->getUserTicketStats($uid);

            if($ticketStats){
                jssupportticket::$_data[0]['count'] = $ticketStats;
            }
        }

        if( ( $user_is == 'user' || $user_is == 'visitor' ) && $addon_are_installed ){

            $downloads = $this->getLatestDownloadsForCp();
            if($downloads){
                jssupportticket::$_data[0]['latest-downloads'] = $downloads;
            }

            $announcements = $this->getLatestAnnouncementsForCp();
            if($announcements){
                jssupportticket::$_data[0]['latest-announcements'] = $announcements;
            }

            $articles = $this->getLatestArticlesForCp();
            if($articles){
                jssupportticket::$_data[0]['latest-articles'] = $articles;
            }

            $faqs = $this->getLatestFaqsForCp();
            if($faqs){
                jssupportticket::$_data[0]['latest-faqs'] = $faqs;
            }
        }
    }

    function getControlPanelDataAdmin(){
        $curdate = date_i18n('Y-m-d');
        $cur_datetime = date_i18n('Y-m-d H:i:s');
        $fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));

        // Section 1: Top Metrics
        $query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."'";
        jssupportticket::$_data['new_tickets'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isanswered != 1 AND status != 5 AND status != 6 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."'";
        jssupportticket::$_data['pending_tickets'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isanswered = 1 AND status != 5 AND status != 6 AND status != 1 AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."'";
        jssupportticket::$_data['answered_tickets'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets";
        jssupportticket::$_data['total_tickets'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(closed) = '".esc_sql($curdate)."' AND (status = 5 OR status = 6)";
        jssupportticket::$_data['tickets_closed_today'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '".esc_sql($curdate)."'";
        jssupportticket::$_data['tickets_created_today'] = jssupportticket::$_db->get_var($query);

        // Section 2: Overdue Tickets
        if(in_array('overdue', jssupportticket::$_active_addons)) {
            $query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isoverdue = 1 AND status != 5 AND status != 6 AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."'";
            jssupportticket::$_data['overdue_tickets_count'] = jssupportticket::$_db->get_var($query);
        }
        
        // Section 3: Unassigned Tickets
        $query = "SELECT id, subject, created FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE staffid = 0 AND status NOT IN (5,6) ORDER BY created DESC LIMIT 4";
        jssupportticket::$_data['unassigned_tickets'] = jssupportticket::$_db->get_results($query);

        // Section 4: Ticket Action History
        if(in_array('tickethistory', jssupportticket::$_active_addons)) {
            $query = "SELECT al.id, al.eventtype, al.message, al.referenceid, tic.ticketid, CONCAT(staff.firstname, ' ', staff.lastname) AS agent_name, al.datetime 
                        FROM ".jssupportticket::$_db->prefix."js_ticket_activity_log AS al
                        JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS tic ON al.referenceid = tic.id
                        LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_staff AS staff ON tic.staffid = staff.id
                        WHERE al.eventfor = 1 AND al.event = 'ticket'
                        ORDER BY al.datetime DESC LIMIT 3";
            jssupportticket::$_data['ticket_action_history'] = jssupportticket::$_db->get_results($query);
        }

        // Section 5: Ticket Trends (Last 7 Days)
        $dates = [];
        $new_tickets_data = [];
        $pending_tickets_data = [];
        $answered_tickets_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = $date;
            $query_new = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '$date' AND status != 5 AND status != 6";
            $new_tickets_data[] = (int) jssupportticket::$_db->get_var($query_new);
            $query_pending = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isanswered != 1 AND status != 5 AND status != 6 AND (lastreply != '0000-00-00 00:00:00') AND DATE(created) = '$date'";
            $pending_tickets_data[] = (int) jssupportticket::$_db->get_var($query_pending);
            $query_answered = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isanswered = 1 AND status != 5 AND status != 6 AND status != 1 AND DATE(created) = '$date'";
            $answered_tickets_data[] = (int) jssupportticket::$_db->get_var($query_answered);
        }
        jssupportticket::$_data['ticket_trends']['dates'] = $dates;
        jssupportticket::$_data['ticket_trends']['new'] = $new_tickets_data;
        jssupportticket::$_data['ticket_trends']['pending'] = $pending_tickets_data;
        jssupportticket::$_data['ticket_trends']['answered'] = $answered_tickets_data;

        // Section 6: Today's Ticket Distribution (Chart Data)
        $query_new_today = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '".esc_sql($curdate)."' AND status = 1";
        $query_answered_today = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '".esc_sql($curdate)."' AND isanswered = 1 AND status != 5 AND status != 6 AND status != 1";
        $query_pending_today = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '".esc_sql($curdate)."' AND isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND status != 6";
        jssupportticket::$_data['today_distribution']['new'] = (int) jssupportticket::$_db->get_var($query_new_today);
        jssupportticket::$_data['today_distribution']['answered'] = (int) jssupportticket::$_db->get_var($query_answered_today);
        jssupportticket::$_data['today_distribution']['pending'] = (int) jssupportticket::$_db->get_var($query_pending_today);

        // Section 7: Latest Tickets
        $query = "SELECT id, ticketid, subject, status, departmentid, lastreply FROM ".jssupportticket::$_db->prefix."js_ticket_tickets ORDER BY created DESC LIMIT 5";
        jssupportticket::$_data['latest_tickets'] = jssupportticket::$_db->get_results($query);


        // Get department names for latest tickets
        $query = "SELECT id, departmentname FROM ".jssupportticket::$_db->prefix."js_ticket_departments";
        $departments = jssupportticket::$_db->get_results($query);
        $department_map = [];
        foreach ($departments as $dept) {
            $department_map[$dept->id] = $dept->departmentname;
        }
        jssupportticket::$_data['department_map'] = $department_map;

        // Get status titles for latest tickets
        $query = "SELECT id, status, statuscolour, statusbgcolour FROM ".jssupportticket::$_db->prefix."js_ticket_statuses";
        $statuses = jssupportticket::$_db->get_results($query);
        $status_map = [];
        foreach ($statuses as $status) {
            $status_map[$status->id] = $status;
        }
        jssupportticket::$_data['status_map'] = $status_map;

        // Section 8: Agent Workload
        if(in_array('agent', jssupportticket::$_active_addons)) {
            $query = "SELECT s.id, CONCAT(s.firstname, ' ', s.lastname) AS agent_name,
                        SUM(CASE WHEN t.status = 1 THEN 1 ELSE 0 END) AS open_tickets,
                        SUM(CASE WHEN t.status = 2 AND t.isanswered = 0 THEN 1 ELSE 0 END) AS awaiting_customer,
                        SUM(CASE WHEN t.status != 5 AND t.status != 6 THEN 1 ELSE 0 END) AS total_tickets,
                        SUM(CASE WHEN t.status != 1 AND t.status != 5 AND t.status != 6 AND t.isanswered = 1 THEN 1 ELSE 0 END) AS awaiting_agent
                        FROM ".jssupportticket::$_db->prefix."js_ticket_staff AS s
                        LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON s.id = t.staffid
                        GROUP BY s.id ORDER BY total_tickets DESC LIMIT 5";
            jssupportticket::$_data['agent_workload'] = jssupportticket::$_db->get_results($query);
        }
        
        // Section 10: Tickets by Status
        $query = "SELECT s.status, s.statusbgcolour, COUNT(t.id) AS ticket_count
                    FROM ".jssupportticket::$_db->prefix."js_ticket_statuses AS s
                    LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON s.id = t.status
                    GROUP BY s.id ORDER BY ticket_count DESC";
        jssupportticket::$_data['tickets_by_status'] = jssupportticket::$_db->get_results($query);

        // Section 11: Tickets by Department
        $query = "SELECT d.departmentname, COUNT(t.id) AS ticket_count
                    FROM ".jssupportticket::$_db->prefix."js_ticket_departments AS d
                    LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON d.id = t.departmentid
                    GROUP BY d.id ORDER BY ticket_count DESC";
        jssupportticket::$_data['tickets_by_department'] = jssupportticket::$_db->get_results($query);
        // Dynamic color list for departments
       jssupportticket::$_data['department_colors'] = ['#a855f7', '#10b981', '#d946b1', '#6b7280', '#06b6d4', '#6366f1', '#ec4899'];
        
        // Section 12: Tickets by Priorities
        $query = "SELECT p.priority, p.prioritycolour, COUNT(t.id) AS ticket_count
                    FROM ".jssupportticket::$_db->prefix."js_ticket_priorities AS p
                    LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON p.id = t.priorityid
                    GROUP BY p.id ORDER BY ticket_count DESC";
        jssupportticket::$_data['tickets_by_priorities'] = jssupportticket::$_db->get_results($query);

        // Section 13: Tickets by Products
        $query = "SELECT product.product, COUNT(t.id) AS ticket_count
                    FROM ".jssupportticket::$_db->prefix."js_ticket_products AS product
                    LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON product.id = t.productid
                    GROUP BY product.id ORDER BY ticket_count DESC";
        jssupportticket::$_data['tickets_by_products'] = jssupportticket::$_db->get_results($query);
        // Dynamic color list for products
        jssupportticket::$_data['product_colors'] = ['#6366f1', '#ec4899', '#06b6d4', '#84cc16', '#ef4444', '#f59e0b', '#3b82f6'];
        
        // Section 14: List of Saved Replies (Canned Responses)
        if(in_array('cannedresponses', jssupportticket::$_active_addons)) {
            $query = "SELECT id, title FROM ".jssupportticket::$_db->prefix."js_ticket_department_message_premade LIMIT 5";
            jssupportticket::$_data['saved_replies'] = jssupportticket::$_db->get_results($query);
        }
        
        // Section 15: Open Tickets by Age
        $date_1_day_ago = date_i18n('Y-m-d H:i:s', strtotime('-1 day'));
        $date_3_days_ago = date_i18n('Y-m-d H:i:s', strtotime('-3 days'));
        $date_7_days_ago = date_i18n('Y-m-d H:i:s', strtotime('-7 days'));
        
        $query_lt_1_day = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE status NOT IN (5,6) AND created > '".esc_sql($date_1_day_ago)."'";
        $query_1_3_days = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE status NOT IN (5,6) AND created <= '".esc_sql($date_1_day_ago)."' AND created > '".esc_sql($date_3_days_ago)."'";
        $query_3_7_days = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE status NOT IN (5,6) AND created <= '".esc_sql($date_3_days_ago)."' AND created > '".esc_sql($date_7_days_ago)."'";
        $query_gt_7_days = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE status NOT IN (5,6) AND created <= '".esc_sql($date_7_days_ago)."'";
        
        jssupportticket::$_data['tickets_by_age']['lt_1_day'] = jssupportticket::$_db->get_var($query_lt_1_day);
        jssupportticket::$_data['tickets_by_age']['1_3_days'] = jssupportticket::$_db->get_var($query_1_3_days);
        jssupportticket::$_data['tickets_by_age']['3_7_days'] = jssupportticket::$_db->get_var($query_3_7_days);
        jssupportticket::$_data['tickets_by_age']['gt_7_days'] = jssupportticket::$_db->get_var($query_gt_7_days);

        // Section 16: Most Active Customers
        $query = "SELECT uid, 
                 MAX(CASE WHEN name != '' THEN name END) AS name, 
                 COUNT(id) AS ticket_count
        FROM " . jssupportticket::$_db->prefix . "js_ticket_tickets
        WHERE uid != 0
        GROUP BY uid
        ORDER BY ticket_count DESC
        LIMIT 4";
        jssupportticket::$_data['most_active_customers'] = jssupportticket::$_db->get_results($query);
        
        // Section 17: Active Timers
        if(in_array('timetracking', jssupportticket::$_active_addons)) {
            $query = "SELECT t.id, t.ticketid, t.subject, st.usertime, st.created FROM ".jssupportticket::$_db->prefix."js_ticket_tickets AS t 
                        JOIN ".jssupportticket::$_db->prefix."js_ticket_staff_time AS st ON t.id = st.ticketid 
                        WHERE st.status = 1 ORDER BY st.created DESC LIMIT 3";
            jssupportticket::$_data['active_timers'] = jssupportticket::$_db->get_results($query);
        }

        $default_options = [
            'unassigned_ticket' => true,
            'ticket_trends' => true,
            'today_distribution' => true,
            'tickets_by_status' => true,
            'tickets_by_priorities' => true,
            'tickets_by_department' => true,
            'tickets_by_products' => true,
            'latest_tickets' => true,
            'agent_workload' => true,
            'ticket_history' => true,
            'overdue_ticket' => true,
            'canned_responses' => true,
            'tickets_by_age' => true,
            'active_customers' => true,
            'active_timer' => true,
            'available_addons' => true,
            'installation_guide' => true,
        ];

        jssupportticket::$_data['jssupportticket_admin_charts_visibility'] = get_option('jssupportticket_admin_charts_visibility', $default_options);

        jssupportticket::$_data['update_avaliable_for_addons'] = $this->showUpdateAvaliableAlert();
    }

    function showUpdateAvaliableAlert(){
        require_once JSST_PLUGIN_PATH.'includes/addon-updater/jsstupdater.php';
        $JS_SUPPORTTICKETUpdater  = new JS_SUPPORTTICKETUpdater();
        $cdnversiondata = $JS_SUPPORTTICKETUpdater->getPluginVersionDataFromCDN();
        $not_installed = array();

        $jssupportticket_addons = $this->getJSSTAddonsArray();
        $installed_plugins = get_plugins();
        $count = 0;
        foreach ($jssupportticket_addons as $key1 => $value1) {
            $matched = 0;
            $version = "";
            foreach ($installed_plugins as $name => $value) {
                $install_plugin_name = str_replace(".php","",basename($name));
                if($key1 == $install_plugin_name){
                    $matched = 1;
                    $version = $value["Version"];
                    $install_plugin_matched_name = $install_plugin_name;
                }
            }
            if($matched == 1){ //installed
                $name = $key1;
                $title = $value1['title'];
                $img = str_replace("js-support-ticket-", "", $key1).'.png';
                $cdnavailableversion = "";
                foreach ($cdnversiondata as $cdnname => $cdnversion) {
                    $install_plugin_name_simple = str_replace("-", "", $install_plugin_matched_name);
                    if($cdnname == str_replace("-", "", $install_plugin_matched_name)){
                        if($cdnversion > $version){ // new version available
                            $count++;
                        }
                    }    
                }
            }
        }
        return $count;
    }

    function getJSSTAddonsArray(){
        return array(
            'js-support-ticket-aipoweredreply' => array('title' => esc_html(__('AI Powered Reply','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-actions' => array('title' => esc_html(__('Ticket Actions','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-agent' => array('title' => esc_html(__('Agents','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-autoclose' => array('title' => esc_html(__('Ticket Auto Close','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-faq' => array('title' => esc_html(__('FAQs','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-helptopic' => array('title' => esc_html(__('Help Topic','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-maxticket' => array('title' => esc_html(__('Max Tickets','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-overdue' => array('title' => esc_html(__('Ticket Overdue','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-smtp' => array('title' => esc_html(__('SMTP','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-tickethistory' => array('title' => esc_html(__('Ticket History','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-useroptions' => array('title' => esc_html(__('User Options','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-mailchimp' => array('title' => esc_html(__('Mailchimp','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-export' => array('title' => esc_html(__('Export','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-announcement' => array('title' => esc_html(__('Announcements','js-support-ticket')), 'price' => 0, 'status' => 1),   
            'js-support-ticket-mail' => array('title' => esc_html(__('Internal Mail','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-note' => array('title' => esc_html(__('Private Note','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-cannedresponses' => array('title' => esc_html(__('Canned Response','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-woocommerce' => array('title' => esc_html(__('WooCommerce','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-privatecredentials'=> array('title' => esc_html(__('Private Credentials','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-envatovalidation' => array('title' => esc_html(__('Envato Validation','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-emailcc' => array('title' => esc_html(__('Email CC','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-feedback' => array('title' => esc_html(__('Feedback','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-knowledgebase' => array('title' => esc_html(__('Knowledge Base','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-mergeticket' => array('title' => esc_html(__('Merge Tickets','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-emailpiping' => array('title' => esc_html(__('Email Piping','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-timetracking' => array('title' => esc_html(__('Time Tracking','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-banemail' => array('title' => esc_html(__('Ban Email','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-notification' => array('title' => esc_html(__('Desktop Notification','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-download' => array('title' => esc_html(__('Downloads','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-agentautoassign' => array('title' => esc_html(__('Agent Auto Assign','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-multiform' => array('title' => esc_html(__('Multiform','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-dashboardwidgets' => array('title' => esc_html(__('Admin Widgets','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-widgets' => array('title' => esc_html(__('Front-End Widgets','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-paidsupport'  => array('title' => esc_html(__('Paid Support','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-easydigitaldownloads' => array('title' => esc_html(__('Easy Digital Downloads','js-support-ticket')), 'price' => 0, 'status' => 1),
            'js-support-ticket-multilanguageemailtemplates'  => array('title' => esc_html(__('Multi Language Email Templates','js-support-ticket')), 'price' => 0, 'status' => 1),
        );
    }
    function getAgentLatestTicketsForCp($staffid){
        if(!is_numeric($staffid)){
            return false;
        }

        $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
        if($allowed == true){
            $agent_conditions = "1 = 1";
        }else{
            $agent_conditions = "ticket.staffid = $staffid OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($staffid).")";
        }

        //latest tickets
        $query = "SELECT DISTINCT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,
        priority.prioritycolour AS prioritycolour,staff.photo AS staffphoto,staff.id AS staffid,
        assignstaff.firstname AS staffname,status.status AS statustitle, status.statuscolour, status.statusbgcolour
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.uid = ticket.uid
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS assignstaff ON ticket.staffid = assignstaff.id
        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON status.id = ticket.status
        WHERE (".esc_sql($agent_conditions).") ORDER BY ticket.created DESC LIMIT 3 ";
        $tickets = jssupportticket::$_db->get_results($query);
        return $tickets;
    }

    function getAgentTicketStats($staffid){
        if(!is_numeric($staffid)){
            return false;
        }

        $result = array();

        $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
        if($allowed == true){
            $agent_conditions = "1 = 1";
        }else{
            $agent_conditions = "ticket.staffid = $staffid OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($staffid).")";
        }

        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($agent_conditions).") AND (ticket.status != 5 AND ticket.status !=6) ";
        $result['openticket'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($agent_conditions).") AND ticket.isanswered = 1 AND ticket.status != 5 AND ticket.status != 1 ";
        $result['answeredticket'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($agent_conditions).") AND (ticket.status = 5 OR ticket.status = 6) ";
        $result['closedticket'] = jssupportticket::$_db->get_var($query);


        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($agent_conditions).") AND ticket.isoverdue = 1 ";
        $result['overdue'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($agent_conditions).")  ";
        $result['allticket'] = jssupportticket::$_db->get_var($query);

        return $result;
    }

    function getAgentCpChartData($staffid){
        if(!is_numeric($staffid) || jssupportticket::$_config['cplink_ticketstats_staff'] != 1){
            return false;
        }

        $curdate = date_i18n('Y-m-d');
        $fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));

        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."' ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $openticket_pr = jssupportticket::$_db->get_results($query);

        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`";
        $allticket_pr = jssupportticket::$_db->get_var($query);

        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."') AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $answeredticket_pr = jssupportticket::$_db->get_results($query);
        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND isoverdue = 1 AND status != 5 AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."') AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $overdueticket_pr = jssupportticket::$_db->get_results($query);
        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id  AND isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."') AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $pendingticket_pr = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['stack_chart_horizontal']['title'] = "['". esc_html(__('Priority','js-support-ticket'))."','". esc_html(__('Overdue','js-support-ticket'))."','". esc_html(__('Pending','js-support-ticket'))."','". esc_html(__('Answered','js-support-ticket'))."','". esc_html(__('New','js-support-ticket'))."']";
        jssupportticket::$_data['stack_chart_horizontal']['data'] = "";

        foreach($overdueticket_pr AS $index => $pr){
            jssupportticket::$_data['stack_chart_horizontal']['data'] .= "[";
            jssupportticket::$_data['stack_chart_horizontal']['data'] .= "'".jssupportticket::JSST_getVarValue($pr->priority)."',";
            jssupportticket::$_data['stack_chart_horizontal']['data'] .= $overdueticket_pr[$index]->totalticket.",";
            jssupportticket::$_data['stack_chart_horizontal']['data'] .= $pendingticket_pr[$index]->totalticket.",";
            jssupportticket::$_data['stack_chart_horizontal']['data'] .= $answeredticket_pr[$index]->totalticket.",";
            jssupportticket::$_data['stack_chart_horizontal']['data'] .= $openticket_pr[$index]->totalticket.",";
            jssupportticket::$_data['stack_chart_horizontal']['data'] .= "],";
        }
    }

    function getUserLatestTicketsForCp($uid){
        if(!is_numeric($uid)){
            return false;
        }
        do_action('jsst_addon_user_cp_tickets');
        $query = "SELECT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour,status.status AS statustitle, status.statuscolour, status.statusbgcolour ".jssupportticket::$_addon_query['select']."
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON     ticket.departmentid = department.id
        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON status.id = ticket.status
        ".jssupportticket::$_addon_query['join'];
        $query .= " WHERE ticket.uid = " . esc_sql($uid);
        $query .= " ORDER BY ticket.created DESC LIMIT 3";
        $tickets = jssupportticket::$_db->get_results($query);
        do_action('reset_jsst_aadon_query');
        return $tickets;
    }

    function getUserTicketStats($uid){
        if(!is_numeric($uid)){
            return false;
        }

        $result = array();

        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE ticket.uid = ".esc_sql($uid)." AND (ticket.status != 5 AND ticket.status != 6)";
        $result['openticket'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.uid = ".esc_sql($uid)." AND ticket.status = 4 ";
        $result['answeredticket'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.uid = ".esc_sql($uid)." AND (ticket.status = 5 OR ticket.status = 6)";
        $result['closedticket'] = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.uid = ".esc_sql($uid);
        $result['allticket'] = jssupportticket::$_db->get_var($query);

        return $result;
    }

    function getLatestDownloadsForCp(){
        if( in_array('download', jssupportticket::$_active_addons) ){
            $query = "SELECT download.title, download.id AS downloadid
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_downloads` AS download
            WHERE download.status = 1 ORDER BY download.created DESC LIMIT 4";
            return jssupportticket::$_db->get_results($query);
        }
        return false;
    }

    function getLatestAnnouncementsForCp(){
        if( in_array('announcement', jssupportticket::$_active_addons) ){
            $query = "SELECT announcement.id, announcement.title
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_announcements` AS announcement
            WHERE announcement.status = 1 ORDER BY announcement.created DESC LIMIT 4";
            return jssupportticket::$_db->get_results($query);
        }
        return false;
    }


    function getLatestArticlesForCp(){
        if( in_array('knowledgebase', jssupportticket::$_active_addons) ){
            $query = "SELECT article.subject,article.content, article.id AS articleid
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_articles` AS article
            WHERE article.status = 1 ORDER BY article.created DESC LIMIT 4";
            return jssupportticket::$_db->get_results($query);
        }
        return false;
    }

    function getLatestFaqsForCp(){
        if( in_array('faq', jssupportticket::$_active_addons) ){
            $query = "SELECT faq.id, faq.subject, faq.content
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_faqs` AS faq
            WHERE faq.status = 1 ORDER BY faq.created DESC LIMIT 4";
            return jssupportticket::$_db->get_results($query);
        }
        return false;
    }


    function getStaffControlPanelData() {

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` ";
        $allticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00')";
        $openticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5";
        $closeticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1";
        $answeredticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5";
        $overdueticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00')";
        $pendingticket = jssupportticket::$_db->get_var($query);

        jssupportticket::$_data['ticket_total']['allticket'] = $allticket;
        jssupportticket::$_data['ticket_total']['openticket'] = $openticket;
        jssupportticket::$_data['ticket_total']['closeticket'] = $closeticket;
        jssupportticket::$_data['ticket_total']['answeredticket'] = $answeredticket;
        jssupportticket::$_data['ticket_total']['overdueticket'] = $overdueticket;
        jssupportticket::$_data['ticket_total']['pendingticket'] = $pendingticket;

        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`";
        jssupportticket::$_data['total_tickets']['total_ticket'] = jssupportticket::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_departments`";
        jssupportticket::$_data['total_tickets']['total_department'] = jssupportticket::$_db->get_var($query);

        if(in_array('agent', jssupportticket::$_active_addons)){
            $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_staff`";
            jssupportticket::$_data['total_tickets']['total_staff'] = jssupportticket::$_db->get_var($query);
        }else{
            jssupportticket::$_data['total_tickets']['total_staff'] = 0;
        }
        if(in_array('feedback', jssupportticket::$_active_addons)){
            $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_feedbacks`";
            jssupportticket::$_data['total_tickets']['total_feedback'] = jssupportticket::$_db->get_var($query);
        }else{
            jssupportticket::$_data['total_tickets']['total_feedback'] = 0;
        }
    }

    function makeDir($path) {
        if (!file_exists($path)) { // create directory
            mkdir($path, 0755);
            $ourFileName = $path . '/index.html';
            $ourFileHandle = fopen($ourFileName, 'w') or die(esc_html(__('Cannot open file', 'js-support-ticket')));
            fclose($ourFileHandle);
        }
    }

    function checkExtension($filename) {
        $i = strrpos($filename, ".");
        if (!$i)
            return 'N';
        $l = jssupportticketphplib::JSST_strlen($filename) - $i;
        $ext = jssupportticketphplib::JSST_substr($filename, $i + 1, $l);
        $extensions = jssupportticketphplib::JSST_explode(",", jssupportticket::$_config['file_extension']);
        $match = 'N';
        foreach ($extensions as $extension) {
            if (strtolower($extension) == jssupportticketphplib::JSST_strtolower($ext)) {
                $match = 'Y';
                break;
            }
        }
        return $match;
    }

    function getColorCode($filestring, $colorNo) {
        if (strstr($filestring, '$color' . $colorNo)) {
            $path1 = jssupportticketphplib::JSST_strpos($filestring, '$color' . $colorNo);
            $path1 = jssupportticketphplib::JSST_strpos($filestring, '#', $path1);
            $path2 = jssupportticketphplib::JSST_strpos($filestring, ';', $path1);
            $colorcode = jssupportticketphplib::JSST_substr($filestring, $path1, $path2 - $path1 - 1);
            return $colorcode;
        }
    }

    //translation code
    function getListTranslations() {
        if(!current_user_can('manage_options')){
            return false;
        }
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-list-translations') ) {
            die( 'Security check Failed' );
        }
        $result = array();
        $result['error'] = false;

        // $path = JSST_PLUGIN_PATH.'languages';

        $path = WP_LANG_DIR;
        if(!is_dir($path)){
            $this->makeDir($path);
        }else{
            $path = WP_LANG_DIR . '/plugins/';
            if(!is_dir($path)){
                $this->makeDir($path);
            }
        }

        if( ! is_writeable($path)){
            $result['error'] = esc_html(__('Dir is not writable','js-support-ticket')).' '.$path;

        }else{

            if($this->isConnected()){

                $url = "https://jshelpdesk.com/translations/api/1.0/index.php";
                $post_data['product'] ='js-support-ticket-wp';
                $post_data['domain'] = get_site_url();
                $post_data['producttype'] = jssupportticket::$_config['producttype'];
                $post_data['productcode'] = 'jsticket';
                $post_data['productversion'] = jssupportticket::$_config['productversion'];
                $post_data['JVERSION'] = get_bloginfo('version');
                $post_data['method'] = 'getTranslations';

                $response = wp_remote_post( $url, array('body' => $post_data,'timeout'=>45,'sslverify'=>false));
                if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                    $call_result = $response['body'];
                }else{
                    $call_result = false;
                    if(!is_wp_error($response)){
                       $error = $response['response']['message'];
                    }else{
                        $error = $response->get_error_message();
                    }
                }

                $result['data'] = jssupportticketphplib::JSST_htmlentities($call_result);
                if(!$call_result){
                    $result['error'] = $error;
                }

            }else{
                $result['error'] = esc_html(__('Unable to connect to the server','js-support-ticket'));
            }
        }

        $result = wp_json_encode($result);

        return $result;
    }

    function makeLanguageCode($lang_name){
        $langarray = wp_get_installed_translations('core');
        $langarray = isset($langarray['default']) ? $langarray['default'] : array();
        $match = false;
        if(array_key_exists($lang_name, $langarray)){
            $lang_name = $lang_name;
            $match = true;
        }else{
            $m_lang = '';
            foreach($langarray AS $k => $v){
                if($lang_name[0].$lang_name[1] == $k[0].$k[1]){
                    $m_lang .= $k.', ';
                }
            }

            if($m_lang != ''){
                $m_lang = jssupportticketphplib::JSST_substr($m_lang, 0,strlen($m_lang) - 2);
                $lang_name = $m_lang;
                $match = 2;
            }else{
                $lang_name = $lang_name;
                $match = false;
            }
        }

        return array('match' => $match , 'lang_name' => $lang_name);
    }

    function validateAndShowDownloadFileName( ){
        if(!current_user_can('manage_options')){
            return false;
        }
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'validate-and-show-download-filename') ) {
            die( 'Security check Failed' );
        }
        $lang_name = JSSTrequest::getVar('langname');
        if($lang_name == '') return '';
        $result = array();
        $f_result = $this->makeLanguageCode($lang_name);
        // $path = JSST_PLUGIN_PATH.'languages';
        $path = WP_LANG_DIR . '/plugins/';
        $result['error'] = false;
        if($f_result['match'] === false){
            $result['error'] = $lang_name. ' ' . esc_html(__('Language is not installed','js-support-ticket'));
        }elseif( ! is_writeable($path)){
            $result['error'] = $lang_name. ' ' . esc_html(__('Language directory is not writable','js-support-ticket')).': '.$path;
        }else{
            $result['input'] = '<input id="languagecode" class="text_area" type="text" value="'.esc_attr($lang_name).'" name="languagecode">';
            if($f_result['match'] === 2){
                $result['input'] .= '<div id="js-emessage-wrapper-other" style="display:block;margin:20px 0px 20px;">';
                $result['input'] .= esc_html(__('Required language is not installed but similar language like','js-support-ticket')).': "<b>'.$f_result['lang_name'].'</b>" '. esc_html(__('is found in your system','js-support-ticket'));
                $result['input'] .= '</div>';

            }
            $result['input'] = jssupportticketphplib::JSST_htmlentities($result['input']);
            $result['path'] = esc_html(__('Language code','js-support-ticket'));
        }
        $result = wp_json_encode($result);
        return $result;
    }

    function getLanguageTranslation(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-language-translation') ) {
            die( 'Security check Failed' );
        }
        $lang_name = JSSTrequest::getVar('langname');
        $language_code = JSSTrequest::getVar('filename');

        $result = array();
        $result['error'] = false;
        // $path = JSST_PLUGIN_PATH.'languages';
        $path = WP_LANG_DIR . '/plugins/';
        if(!is_dir($path)){
            mkdir($path);
        }

        if($lang_name == '' || $language_code == ''){
            $result['error'] = esc_html(__('Empty values','js-support-ticket'));
            return wp_json_encode($result);
        }

        $final_path = $path.'/js-support-ticket-'.$language_code.'.po';


        $langarray = wp_get_installed_translations('core');
        $langarray = $langarray['default'];

        if(!array_key_exists($language_code, $langarray)){
            $result['error'] = $lang_name. ' ' . esc_html(__('Language is not installed','js-support-ticket'));
            return wp_json_encode($result);
        }elseif( ! is_writeable($path)){
            $result['error'] = $lang_name. ' ' . esc_html(__('Language directory is not writable','js-support-ticket')).': '.$path;
            return wp_json_encode($result);
        }

        if( ! file_exists($final_path)){
            touch($final_path);
        }

        if( ! is_writeable($final_path)){
            $result['error'] = esc_html(__('File is not writable','js-support-ticket')).': '.$final_path;
        }else{

            if($this->isConnected()){

                $url = "https://jshelpdesk.com/translations/api/1.0/index.php";
                $post_data['product'] ='js-support-ticket-wp';
                $post_data['domain'] = get_site_url();
                $post_data['producttype'] = jssupportticket::$_config['producttype'];
                $post_data['productcode'] = 'jsticket';
                $post_data['productversion'] = jssupportticket::$_config['productversion'];
                $post_data['JVERSION'] = get_bloginfo('version');
                $post_data['translationcode'] = $lang_name;
                $post_data['method'] = 'getTranslationFile';

                $response = wp_remote_post( $url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
                if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                    $result = $response['body'];
                }else{
                    $result = false;
                    if(!is_wp_error($response)){
                       $error = $response['response']['message'];
                    }else{
                        $error = $response->get_error_message();
                    }
                }
                if($result){
                    $result = json_decode($result, true);
                    $ret = $this->writeLanguageFile( $final_path , $result['file']);
                }else{
                    $result = array();
                }

                /*
                if($ret != false){
                    $url = "https://jshelpdesk.com/translations/api/1.0/index.php";
                    $post_data['product'] ='js-support-ticket-wp';
                    $post_data['domain'] = get_site_url();
                    $post_data['producttype'] = jssupportticket::$_config['producttype'];
                    $post_data['productcode'] = 'jsticket';
                    $post_data['productversion'] = jssupportticket::$_config['productversion'];
                    $post_data['JVERSION'] = get_bloginfo('version');
                    $post_data['folder'] = $array['foldername'];

                    $response = wp_remote_post( $url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
                    if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                        $result_call = $response['body'];
                    }else{
                        $result_call = false;
                        if(!is_wp_error($response)){
                           $error = $response['response']['message'];
                        }else{
                            $error = $response->get_error_message();
                        }
                    }
                    if($result_call){
                        $response = $result_call;
                    }else{
                        $response = $result_call;
                    }

                }
                */  
                $result['data'] = esc_html(__('File successfully downloaded','js-support-ticket'));
            }else{
                $result['error'] = esc_html(__('Unable to connect to the server','js-support-ticket'));
            }
        }

        $result = wp_json_encode($result);

        return $result;

    }

    function writeLanguageFile( $path , $url ){
        $result = true;
        do_action('jssupportticket_load_wp_admin_file');
        $tmpfile = download_url( $url);
        copy( $tmpfile, $path );
        if ( file_exists( $tmpfile ) ) {
            wp_delete_file( $tmpfile ); // must unlink afterwards
        }
        //make mo for po file
        $this->phpmo_convert($path);
        return $result;
    }

    function isConnected(){

        $connected = @fsockopen("www.google.com", 80);
        if ($connected){
            $is_conn = true; //action when connected
            fclose($connected);
        }else{
            $is_conn = false; //action in connection failure
        }
        return $is_conn;
    }

    function phpmo_convert($input, $output = false) {
        if ( !$output )
            $output = jssupportticketphplib::JSST_str_replace( '.po', '.mo', $input );
        $hash = $this->phpmo_parse_po_file( $input );
        if ( $hash === false ) {
            return false;
        } else {
            $this->phpmo_write_mo_file( $hash, $output );
            return true;
        }
    }

    function phpmo_clean_helper($x) {
        if (is_array($x)) {
            foreach ($x as $k => $v) {
                $x[$k] = $this->phpmo_clean_helper($v);
            }
        } else {
            if ($x[0] == '"')
                $x = jssupportticketphplib::JSST_substr($x, 1, -1);
            $x = jssupportticketphplib::JSST_str_replace("\"\n\"", '', $x);
            $x = jssupportticketphplib::JSST_str_replace('$', '\\$', $x);
        }
        return $x;
    }
    /* Parse gettext .po files. */
    /* @link http://www.gnu.org/software/gettext/manual/gettext.html#PO-Files */
    function phpmo_parse_po_file($in) {
    if (!file_exists($in)){ return false; }
    $ids = array();
    $strings = array();
    $language = array();
    $lines = file($in);
    foreach ($lines as $line_num => $line) {
        if (strstr($line, 'msgid')){
			//$endpos = strrchr($line, '"');
			$endpos = strrpos($line, '"',7);
			if($endpos > 7){ // to avoid msgid ""
				$id = jssupportticketphplib::JSST_substr($line, 7, $endpos-7);
				$ids[] = $id;
			}
        }elseif(strstr($line, 'msgstr')){
			//$endpos = strrchr($line, '"');
			$endpos = strrpos($line, '"',8);
			if($endpos > 8){ // to avoid msgstr ""
				$string = jssupportticketphplib::JSST_substr($line, 8, $endpos-8);
				$strings[] = array($string);
			}
        }else{}
    }
    for ($i=0; $i<count($ids); $i++){
        //Shoaib
        if(isset($ids[$i]) && isset($strings[$i])){
            /*if($entry['msgstr'][0] == '""'){
                continue;
            }*/
            $language[$ids[$i]] = array('msgid' => $ids[$i], 'msgstr' =>$strings[$i]);
        }
    }
    return $language;
    }
    /* Write a GNU gettext style machine object. */
    /* @link http://www.gnu.org/software/gettext/manual/gettext.html#MO-Files */
    function phpmo_write_mo_file($hash, $out) {
        // sort by msgid
        ksort($hash, SORT_STRING);
        // our mo file data
        $mo = '';
        // header data
        $offsets = array ();
        $ids = '';
        $strings = '';
        foreach ($hash as $entry) {
            $id = $entry['msgid'];
            $str = implode("\x00", $entry['msgstr']);
            // keep track of offsets
            $offsets[] = array (
                            jssupportticketphplib::JSST_strlen($ids), jssupportticketphplib::JSST_strlen($id), jssupportticketphplib::JSST_strlen($strings), jssupportticketphplib::JSST_strlen($str)
                            );
            // plural msgids are not stored (?)
            $ids .= $id . "\x00";
            $strings .= $str . "\x00";
        }
        // keys start after the header (7 words) + index tables ($#hash * 4 words)
        $key_start = 7 * 4 + sizeof($hash) * 4 * 4;
        // values start right after the keys
        $value_start = $key_start +strlen($ids);
        // first all key offsets, then all value offsets
        $key_offsets = array ();
        $value_offsets = array ();
        // calculate
        foreach ($offsets as $v) {
            list ($o1, $l1, $o2, $l2) = $v;
            $key_offsets[] = $l1;
            $key_offsets[] = $o1 + $key_start;
            $value_offsets[] = $l2;
            $value_offsets[] = $o2 + $value_start;
        }
        $offsets = array_merge($key_offsets, $value_offsets);
        // write header
        $mo .= pack('Iiiiiii', 0x950412de, // magic number
        0, // version
        sizeof($hash), // number of entries in the catalog
        7 * 4, // key index offset
        7 * 4 + sizeof($hash) * 8, // value index offset,
        0, // hashtable size (unused, thus 0)
        $key_start // hashtable offset
        );
        // offsets
        foreach ($offsets as $offset)
            $mo .= pack('i', $offset);
        // ids
        $mo .= $ids;
        // strings
        $mo .= $strings;
        file_put_contents($out, $mo);
    }

    function stripslashesFull($input){// testing this function/.
        if (is_array($input)) {
            $input = array_map(array($this,'stripslashesFull'), $input);
        } elseif (is_object($input)) {
            $vars = get_object_vars($input);
            foreach ($vars as $k=>$v) {
                $input->{$k} = $this->stripslashesFull($v);
            }
        } else {
            $input = jssupportticketphplib::JSST_stripslashes($input);
        }
        return $input;
    }

    function getUserNameById($id){
        if (!is_numeric($id))
            return false;
        $query = "SELECT user_nicename AS name FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` WHERE id = ".esc_sql($id);
        $username = jssupportticket::$_db->get_var($query);
        return $username;
    }

    function getusersearchajax() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'get-usersearch-ajax') ) {
            die( 'Security check Failed' );
        }
        $username = JSSTrequest::getVar('username');
        $name = JSSTrequest::getVar('name');
        $emailaddress = JSSTrequest::getVar('emailaddress');
        $canloadresult = false;
        $query = "SELECT DISTINCT user.id AS userid, user.name AS username, user.user_email AS useremail, user.display_name AS userdisplayname
                    FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user ";
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        $query .= " WHERE NOT EXISTS( SELECT staff.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff WHERE user.id = staff.uid) ";
                    }else{
                        $query .= " WHERE 1 = 1 "; // to handle filter cases
                    }
        if (jssupportticketphplib::JSST_strlen($name) > 0) {
            $query .= " AND user.display_name LIKE '%".esc_sql($name)."%'";
            $canloadresult = true;
        }
        if (jssupportticketphplib::JSST_strlen($emailaddress) > 0) {
            $query .= " AND user.user_email LIKE '%".esc_sql($emailaddress)."%'";
            $canloadresult = true;
        }
        if (jssupportticketphplib::JSST_strlen($username) > 0) {
            $query .= " AND user.name LIKE '%".esc_sql($username)."%'";
            $canloadresult = true;
        }
        if($canloadresult){
            $users = jssupportticket::$_db->get_results($query);
            if(!empty($users)){
                $result ='
                <div class="js-ticket-table-wrp">
                    <div class="js-ticket-table-header">
                        <div class="js-ticket-table-header-col js-tkt-tbl-uid">'. esc_html(__('User ID', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-unm">'. esc_html(__('User Name', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-eml">'. esc_html(__('Email Address', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-nam">'. esc_html(__('Name', 'js-support-ticket')).'</div>
                    </div>
                    <div class="js-ticket-table-body">';
                        foreach($users AS $user){
                            $result .='
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-table-body-col js-tkt-tbl-uid">
                                    <span class="js-ticket-display-block">'. esc_html(__('User ID','js-support-ticket')).'</span>'.$user->userid.'
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-unm">
                                    <span class="js-ticket-display-block">'. esc_html(__('User Name','js-support-ticket')).':</span>
                                    '.esc_html($user->username).'
                                    </a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-eml">
                                    <span class="js-ticket-display-block">'. esc_html(__('Email','js-support-ticket')).':</span>
                                    <span class="js-ticket-title"><a href="#" class="js-userpopup-link" data-id="'.esc_attr($user->userid).'" data-email="'.esc_attr($user->useremail).'" data-username="'.esc_attr($user->username).'" data-name="'.esc_attr($user->userdisplayname).'">
                                        '. esc_html($user->useremail) .'
                                        </a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-nam">
                                    <span class="js-ticket-display-block">'. esc_html(__('Name','js-support-ticket')).':</span>
                                    '.esc_attr($user->userdisplayname).'
                                </div>
                            </div>';
                        }
                $result .='</div>';
            }else{
                $result= JSSTlayout::getNoRecordFound();
            }
        }else{ // reset button
            //$result ='<div class="js-staff-searc-desc">'. esc_html(__('Use search feature to select the user','js-support-ticket')).'</div>';
            $result = $this->getuserlistajax(0);
        }

        return $result;
    }



    function getuserlistajax($ajaxCall = 1){
        if ($ajaxCall == 1) {
            $nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $nonce, 'get-user-list-ajax') ) {
                die( 'Security check Failed' );
            }
        }
        $userlimit = JSSTrequest::getVar('userlimit',null,0);
        $maxrecorded = 4;
        $query = "SELECT DISTINCT COUNT(user.id)
                    FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user 
					WHERE user.status = 1 ";
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        $query .= " AND NOT EXISTS( SELECT staff.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff WHERE user.id = staff.uid) ";
                    }

        $total = jssupportticket::$_db->get_var($query);
        $limit = $userlimit * $maxrecorded;
        if($limit >= $total){
            $limit = 0;
        }
        $query = "SELECT DISTINCT user.id AS userid, user.name AS username, user.user_email AS useremail,
                    user.display_name AS userdisplayname
                    FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user 
					WHERE user.status = 1";
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        $query .= " AND NOT EXISTS( SELECT staff.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff WHERE user.id = staff.uid) ";
                    }
                    $query .= " LIMIT $limit, $maxrecorded";
        $users = jssupportticket::$_db->get_results($query);
        $html = $this->makeUserList($users,$total,$maxrecorded,$userlimit);
        return $html;

    }


    function makeUserList($users,$total,$maxrecorded,$userlimit){
        $html = '';
        if(!empty($users)){
            if(is_array($users)){
                $html ='
                <div class="js-ticket-table-wrp">
                    <div class="js-ticket-table-header">
                        <div class="js-ticket-table-header-col js-tkt-tbl-uid">'. esc_html(__('User ID', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-unm">'. esc_html(__('User Name', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-eml">'. esc_html(__('Email Address', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-nam">'. esc_html(__('Name', 'js-support-ticket')).'</div>
                    </div>
                    <div class="js-ticket-table-body">';
                        foreach($users AS $user){
                            $html .='
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-table-body-col js-tkt-tbl-uid">
                                    <span class="js-ticket-display-block">'. esc_html(__('User ID','js-support-ticket')).'</span>'.esc_html($user->userid).'
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-unm">
                                    <span class="js-ticket-display-block">'. esc_html(__('User Name','js-support-ticket')).':</span>
                                    '.esc_html($user->username).'
                                    </a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-eml">
                                    <span class="js-ticket-display-block">'. esc_html(__('Email','js-support-ticket')).':</span>
                                    <span class="js-ticket-title"><a href="#" class="js-userpopup-link" data-id="'.esc_attr($user->userid).'" data-email="'.esc_attr($user->useremail).'" data-username="'.esc_attr($user->username).'" data-name="'.esc_attr($user->userdisplayname).'">
                                    '.esc_html($user->useremail).'
                                    </a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-nam">
                                    <span class="js-ticket-display-block">'. esc_html(__('Name','js-support-ticket')).':</span>
                                    '.esc_html($user->userdisplayname).'
                                </div>
                            </div>';
                        }
                $html .='</div>';
            }
            $num_of_pages = ceil($total / $maxrecorded);
            $num_of_pages = ($num_of_pages > 0) ? ceil($num_of_pages) : floor($num_of_pages);
            if($num_of_pages > 0){
                $page_html = '';
                $prev = $userlimit;
                if($prev > 0){
                    $page_html .= '<a class="jsst_userlink" href="#" onclick="updateuserlist('.esc_js(($prev - 1)).');">'. esc_html(__('Previous','js-support-ticket')).'</a>';
                }
                for($i = 0; $i < $num_of_pages; $i++){
                    if($i == $userlimit)
                        $page_html .= '<span class="jsst_userlink selected" >'.($i + 1).'</span>';
                    else
                        $page_html .= '<a class="jsst_userlink" href="#" onclick="updateuserlist('.esc_js($i).');">'.esc_js(($i + 1)).'</a>';

                }
                $next = $userlimit + 1;
                if($next < $num_of_pages){
                    $page_html .= '<a class="jsst_userlink" href="#" onclick="updateuserlist('.esc_js($next).');">'. esc_html(__('Next','js-support-ticket')).'</a>';
                }
                if($page_html != ''){
                    $html .= '<div class="jsst_userpages">'.wp_kses($page_html, JSST_ALLOWED_TAGS).'</div>';
                }
            }

        }else{
            $html = JSSTlayout::getNoRecordFound();
        }
        echo wp_kses($html, JSST_ALLOWED_TAGS);
        die();
        return $html;
    }

    function storeOrderingFromPage($data) {//
        if (empty($data)) {
            return false;
        }
        $sorted_array = array();
        jssupportticketphplib::JSST_parse_str($data['fields_ordering_new'],$sorted_array);
        $sorted_array = reset($sorted_array);
        if(!empty($sorted_array)){

            if($data['ordering_for'] == 'department'){
                $row = JSSTincluder::getJSTable('departments');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'priority'){
                $row = JSSTincluder::getJSTable('priorities');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'status'){
                $row = JSSTincluder::getJSTable('statuses');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'product'){
                $row = JSSTincluder::getJSTable('products');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'fieldsordering'){
                $row = JSSTincluder::getJSTable('fieldsordering');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'announcement'){
                $row = JSSTincluder::getJSTable('announcement');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'faq'){
                $row = JSSTincluder::getJSTable('faq');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'helptopic'){
                $row = JSSTincluder::getJSTable('helptopic');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'article'){
                $row = JSSTincluder::getJSTable('articles');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'download'){
                $row = JSSTincluder::getJSTable('download');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'fieldordering'){
                $row = JSSTincluder::getJSTable('fieldsordering');
                $ordering_coloumn = 'ordering';
            }elseif($data['ordering_for'] == 'multiform'){
                $row = JSSTincluder::getJSTable('multiform');
                $ordering_coloumn = 'ordering';
            }

            $page_multiplier = 1;
            if($data['pagenum_for_ordering'] > 1){
                $page_multiplier = ($data['pagenum_for_ordering'] - 1) * jssupportticket::$_config['pagination_default_page_size'] + 1;
            }
            for ($i=0; $i < count($sorted_array) ; $i++) {
                $row->update(array('id' => $sorted_array[$i], $ordering_coloumn => $page_multiplier + $i));
            }
        }
        JSSTmessage::setMessage(esc_html(__('Ordering updated', 'js-support-ticket')), 'updated');
        return ;
    }

    function updateDate($addon_name,$plugin_version){
        return JSSTincluder::getJSModel('premiumplugin')->verfifyAddonActivation($addon_name);
    }

    function getAddonSqlForActivation($addon_name,$addon_version){
        return JSSTincluder::getJSModel('premiumplugin')->verifyAddonSqlFile($addon_name,$addon_version);
    }

    function installPluginFromAjax(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'install-plugin-ajax') ) {
             die( 'Security check Failed' ); 
        }
        if(current_user_can( 'install_plugins' )){
            $pluginslug = JSSTrequest::getVar('pluginslug');
            if(file_exists(plugins_url($pluginslug . '/' . $pluginslug . '.php'))){
                return false;
            }
            if($pluginslug != ""){
                do_action('jssupportticket_load_wp_plugin_file');
                do_action('jssupportticket_load_wp_upgrader');
                do_action('jssupportticket_load_wp_ajax_upgrader_skin');
                do_action('jssupportticket_load_wp_plugin_upgrader');

                // Get Plugin Info
                $api = plugins_api( 'plugin_information',
                    array(
                        'slug' => $pluginslug,
                        'fields' => array(
                            'short_description' => false,
                            'sections' => false,
                            'requires' => false,
                            'rating' => false,
                            'ratings' => false,
                            'downloaded' => false,
                            'last_updated' => false,
                            'added' => false,
                            'tags' => false,
                            'compatibility' => false,
                            'homepage' => false,
                            'donate_link' => false,
                        ),
                    )
                );
                $skin     = new WP_Ajax_Upgrader_Skin();
                $upgrader = new Plugin_Upgrader( $skin );
                $upgrader->install( $api->download_link );
                if(file_exists(plugins_url($pluginslug . '/' . $pluginslug . '.php'))){
                    return true;
                }
            }
        }
        return false;
    }

    function activatePluginFromAjax(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'activate-plugin-ajax') ) {
             die( 'Security check Failed' ); 
        }
        if(current_user_can( 'activate_plugins')){
            $pluginslug = JSSTrequest::getVar('pluginslug');
            do_action('jssupportticket_load_wp_plugin_file');
            if(file_exists(plugins_url($pluginslug . '/' . $pluginslug . '.php'))){
                $isactivate = is_plugin_active($pluginslug.'/'.$pluginslug.'.php');
                if($isactivate){
                    return false;
                }
                if($pluginslug != ""){
                    if(!defined( 'WP_ADMIN')){
                        define( 'WP_ADMIN', TRUE );
                    }
                    // define( 'WP_NETWORK_ADMIN', TRUE ); // Need for Multisite
                    if(!defined( 'WP_USER_ADMIN')){
                        define( 'WP_USER_ADMIN', TRUE );
                    }

                    ob_get_clean();
                    do_action('jssupportticket_load_wp_admin_file');
                    do_action('jssupportticket_load_wp_plugin_file');
                    activate_plugin( $pluginslug.'/'.$pluginslug.'.php' );
                    // $isactivate = $this->run_activate_plugin( $pluginslug.'/'.$pluginslug.'.php' );
                    $isactivate = is_plugin_active($pluginslug.'/'.$pluginslug.'.php');
                    if($isactivate){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function getJSSTDateFormat(){
        $dateformat = jssupportticket::$_config['date_format'];
        if ($dateformat == 'm/d/Y' || $dateformat == 'd/m/y' || $dateformat == 'm/d/y' || $dateformat == 'd/m/Y') {
            $dash = '/';
        } else {
            $dash = '-';
        }
        $firstdash = jssupportticketphplib::JSST_strpos($dateformat, $dash, 0);
        $firstvalue = jssupportticketphplib::JSST_substr($dateformat, 0, $firstdash);
        $firstdash = $firstdash + 1;
        $seconddash = jssupportticketphplib::JSST_strpos($dateformat, $dash, $firstdash);
        $secondvalue = jssupportticketphplib::JSST_substr($dateformat, $firstdash, $seconddash - $firstdash);
        $seconddash = $seconddash + 1;
        $thirdvalue = jssupportticketphplib::JSST_substr($dateformat, $seconddash, jssupportticketphplib::JSST_strlen($dateformat) - $seconddash);
        $js_dateformat = '%' . $firstvalue . $dash . '%' . $secondvalue . $dash . '%' . $thirdvalue;
        $js_scriptdateformat = $firstvalue . $dash . $secondvalue . $dash . $thirdvalue;
        $js_scriptdateformat = jssupportticketphplib::JSST_str_replace('Y', 'yy', $js_scriptdateformat);
        $js_scriptdateformat = jssupportticketphplib::JSST_str_replace('m', 'mm', $js_scriptdateformat);
        $js_scriptdateformat = jssupportticketphplib::JSST_str_replace('d', 'dd', $js_scriptdateformat);
        return $js_scriptdateformat;
    }

    function getAddonTransationKey($option_name){
        $query = "SELECT `option_value` FROM " . jssupportticket::$_wpprefixforuser . "options WHERE option_name = '".esc_sql($option_name)."'";
        $transactionKey = jssupportticket::$_db->get_var($query);
		if($transactionKey == ""){
			$transactionKey = get_option($option_name);
		}
        return $transactionKey;
    }

    function getInstalledTranslationKey(){
        do_action('jssupportticket_load_wp_translation_install');
        $activated_lang = get_option('WPLANG','en_US');
        $install_lang_name = wp_get_available_translations();
        if(isset($install_lang_name[$activated_lang])){
            $lang_name = $this->makeLanguageCode($activated_lang);
            $install_lang_name = $install_lang_name[$activated_lang]['english_name'];
            if($activated_lang == "" || $activated_lang == 'en_US'){
                update_option( 'jshd_tran_lang_exists', false);
                return false;
            }else{
                // $path = JSST_PLUGIN_PATH.'languages';
                $path = WP_LANG_DIR . '/plugins/';
                $final_path = $path.'/js-support-ticket-'.$activated_lang.'.po';
                if(file_exists($final_path)){
                    update_option( 'jshd_tran_lang_exists', false);
                    return false;
                }
                if(get_option( 'jshd_tran_lang_exists', '') != ''){
                    $session = json_decode(get_option( 'jshd_tran_lang_exists', ''));
                    if($session->code == $activated_lang){
                        return get_option( 'jshd_tran_lang_exists');
                    }
                }
                $url = "https://jshelpdesk.com/translations/api/1.0/index.php";
                $post_data['product'] ='js-support-ticket-wp';
                $post_data['domain'] = get_site_url();
                $post_data['producttype'] = jssupportticket::$_config['producttype'];
                $post_data['productcode'] = 'jsticket';
                $post_data['productversion'] = jssupportticket::$_config['productversion'];
                $post_data['JVERSION'] = get_bloginfo('version');
                $post_data['translationcode'] = $activated_lang;
                $post_data['method'] = 'getTranslationFile';

                $response = wp_remote_post( $url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
                if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                    $result = $response['body'];
                }else{
                    $result = false;
                    if(!is_wp_error($response)){
                       $error = $response['response']['message'];
                    }else{
                        $error = $response->get_error_message();
                    }
                }
                if($result){
                    $array = json_decode($result, true);
                }else{
                    $array = array();
                }
                if(is_array($array) && isset($array['file'])){
                    $jshd_tran_lang_exists = array("code" => $activated_lang, "lang_fullname" => $install_lang_name , "name" => $lang_name);
                    $jshd_tran_lang_exists = wp_json_encode($jshd_tran_lang_exists);
                    update_option( 'jshd_tran_lang_exists', $jshd_tran_lang_exists);
                    return $jshd_tran_lang_exists;
                }else{
                    update_option( 'jshd_tran_lang_exists', false);
                    return false;
                }
            }
        }
        return false;
    }

    function hidePopupFromAdmin(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'hide-popup-from-admin') ) {
            die( 'Security check Failed' );
        }
        update_option( 'jsst_hide_jsstadmin_top_banner', 1 );
    }
    function getWPUidById($id){
        if(!is_numeric($id)){
            return false;
        }

        $query = "SELECT user.wpuid
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user 
                    WHERE id = ".esc_sql($id);
        $wpuid = jssupportticket::$_db->get_var($query);
        return $wpuid;
    }

    function reviewBoxAction(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'review-box-action') ) {
            die( 'Security check Failed' );
        }
        $days = JSSTrequest::getVar('days');
        if($days == -1) {
            add_option("jssupportticket_hide_review_box", "1");
        } else {
			//jssupportticketphplib::JSST_strtotime not work porperly
            //$date = gmdate("Y-m-d", jssupportticketphplib::JSST_strtotime("+".$days." days"));
			$date = gmdate("Y-m-d", strtotime("+".$days." days"));
            update_option("jssupportticket_show_review_box_after", $date);
        }
        return true;
    }

    function getShortCodeData(){
        if( in_array('multiform', jssupportticket::$_active_addons) ){
            $query = "SELECT multiform.id, multiform.title, department.departmentname FROM `" . jssupportticket::$_db->prefix . "js_ticket_multiform` AS multiform
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON multiform.departmentid = department.id WHERE multiform.status = 1 ORDER BY multiform.id ASC";
            jssupportticket::$_data[0]['multiforms'] = jssupportticket::$_db->get_results($query);
        }
        return true;
    }

    function checkIfMainCssFileIsEnqued(){
        global $wp_styles;
        if (!in_array('jssupportticket-main-css',$wp_styles->queue)) {
            wp_enqueue_style('jssupportticket-main-css', JSST_PLUGIN_URL . 'includes/css/style.css');
            // responsive style sheets
            wp_enqueue_style('jssupportticket-tablet-css', JSST_PLUGIN_URL . 'includes/css/style_tablet.css',array(),'','(min-width: 668px) and (max-width: 782px)');
            wp_enqueue_style('jssupportticket-mobile-css', JSST_PLUGIN_URL . 'includes/css/style_mobile.css',array(),'','(min-width: 481px) and (max-width: 667px)');
            wp_enqueue_style('jssupportticket-oldmobile-css', JSST_PLUGIN_URL . 'includes/css/style_oldmobile.css',array(),'','(max-width: 480px)');
            //wp_enqueue_style('jssupportticket-main-css');
            if(is_rtl()){
                //wp_register_style('jssupportticket-main-css-rtl', JSST_PLUGIN_URL . 'includes/css/stylertl.css');
                wp_enqueue_style('jssupportticket-main-css-rtl', JSST_PLUGIN_URL . 'includes/css/stylertl.css');
                //wp_enqueue_style('jssupportticket-main-css-rtl');
            }
            $color = require_once(JSST_PLUGIN_PATH . 'includes/css/style.php');
            wp_enqueue_style('jssupportticket-color-css', JSST_PLUGIN_URL . 'includes/css/color.css');
        }
        return true;
    }

    function updateColorFile(){
        require(JSST_PLUGIN_PATH . 'includes/css/style.php');
    }

    function getSiteUrl(){
        $site_url = site_url();
        $site_url = jssupportticketphplib::JSST_str_replace("https://","",$site_url);
        $site_url = jssupportticketphplib::JSST_str_replace("http://","",$site_url);
        return $site_url;
    }

    function getNetworkSiteUrl(){
        $network_site_url = network_site_url();
        $network_site_url = jssupportticketphplib::JSST_str_replace("https://","",$network_site_url);
        $network_site_url = jssupportticketphplib::JSST_str_replace("http://","",$network_site_url);
        return $network_site_url;
    }

    function addMissingUsers($show_message = 1){
        $missingUser = 0;
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "users`";
        $users = jssupportticket::$_db->get_results($query);
        $wpUsers = array();
        $jsstUsers = array();
        foreach ($users as $key => $user) {
            $wpUsers[] = $user->id;
        }
        $query = " SELECT wpuid FROM `" . jssupportticket::$_db->prefix . "js_ticket_users`";
        $users = jssupportticket::$_db->get_results($query);
        foreach ($users as $key => $user) {
            $jsstUsers[] = $user->wpuid;
        }

        $missingUsers = array_diff($wpUsers,$jsstUsers);
        foreach ($missingUsers as $missingUser) {
            $query = "SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` WHERE wpuid = " . esc_sql($missingUser);
            $total = jssupportticket::$_db->get_var($query);
            if ($total == 0) {
                $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "users` WHERE id = " . esc_sql($missingUser);
                $user = jssupportticket::$_db->get_row($query);                
                if (isset($user)) {
                    $row = JSSTincluder::getJSTable('users');
                    $data['wpuid'] = $user->ID;
                    $data['name'] = $user->display_name;
                    $data['display_name'] = $user->display_name;
                    $data['user_nicename'] = $user->user_nicename;
                    $data['user_email'] = $user->user_email;
                    $data['issocial'] = 0;
                    $data['socialid'] = null;
                    $data['status'] = 1;
                    $data['created'] = date_i18n('Y-m-d H:i:s');
                    $row->bind($data);
                    $row->store();
                    $missingUser = 1;
                }
            }
        }
        if ($show_message == 1) {
            if ($missingUser == 1) {
                JSSTmessage::setMessage(esc_html(__('Missing user(s) added successfully!', 'js-support-ticket')), 'updated');
            } else {
                JSSTmessage::setMessage(esc_html(__('No missing user found!', 'js-support-ticket')), 'error');
            }
        }
        return;
    }

    function jsstremovetags($message){
        if(jssupportticketphplib::JSST_strpos($message, '<script>') !== false || jssupportticketphplib::JSST_strpos($message, '</script>') !== false){ // check and remove script tag from the message
            $message = jssupportticketphplib::JSST_str_replace('<script>','&lt;script&gt;', $message);
            $message = jssupportticketphplib::JSST_str_replace('</script>','&lt;/script&gt;', $message);
        }
        return $message;
    }

    function getSanitizedEditorData($data){
       $data = wp_filter_post_kses(wpautop($data));
       return $data;
    }

    function getEncriptedSiteLink(){
        $siteLink = get_option('jsst_encripted_site_link');
        if ($siteLink == '') {
            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
            $encoder = new JSSTEncoder();
            $siteLink = $encoder->encrypt(get_site_url());
            update_option('jsst_encripted_site_link', $siteLink);
        }
       return $siteLink;
    }

    function checkJSSTAddoneInfo($name){
        $slug = $name.'/'.$name.'.php';
        if(file_exists(WP_PLUGIN_DIR . '/'.$slug) && is_plugin_active($slug)){
            $status = __("Activated","js-support-ticket");
            $action = __("Deactivate","js-support-ticket");
            $actionClass = 'jsst-admin-adons-status-Deactive';
            $url = "plugins.php?s=".$name."&plugin_status=active";
            $disabled = "disabled";
            $class = "js-btn-activated";
            $availability = "-1";
            $version = "";
        }else if(file_exists(WP_PLUGIN_DIR . '/'.$slug) && !is_plugin_active($slug)){
            $status = __("Deactivated","js-support-ticket");
            $action = __("Activate","js-support-ticket");
            $actionClass = 'jsst-admin-adons-status-Active';
            $url = "plugins.php?s=".$name."&plugin_status=inactive";
            $disabled = "";
            $class = "js-btn-green js-btn-active-now";
            $availability = "1";
            $version = "";
        }else if(!file_exists(WP_PLUGIN_DIR . '/'.$slug)){
            $status = __("Not Installed","js-support-ticket");
            $action = __("Install Now","js-support-ticket");
            $actionClass = 'jsst-admin-adons-status-Install';
            $url = admin_url("admin.php?page=premiumplugin&mjslay=step1");
            $disabled = "";
            $class = "js-btn-install-now";
            $availability = "0";
            $version = "---";
        }
        return array("status" => $status, "action" => $action, "url" => $url, "disabled" => $disabled, "class" => $class, "availability" => $availability, "actionClass" => $actionClass, "version" => $version);
    }

    function JSSTdownloadandinstalladdonfromAjax(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'download-and-install-addon') ) {
            die( 'Security check Failed' );
        }

        $key = JSSTrequest::getVar('dataFor');
        $installedversion = JSSTrequest::getVar('currentVersion');
        $newversion = JSSTrequest::getVar('cdnVersion');
        $addon_json_array = array();

        if($key != ''){
            $addon_json_array[] = str_replace('js-support-ticket-', '', $key);
            $plugin_slug = str_replace('js-support-ticket-', '', $key);
        }
        $token = get_option('transaction_key_for_'.$key);
        $result = array();
        $result['error'] = false;
        if($token == ''){
            $result['error'] = esc_html(__('Addon Installation Failed','js-support-ticket'));
            $result = wp_json_encode($result);
            return $result;
        }
        $site_url = site_url();
        if($site_url != ''){
            $site_url = str_replace("https://","",$site_url);
            $site_url = str_replace("http://","",$site_url);
        }
        $url = 'https://jshelpdesk.com/setup/index.php?token='.$token.'&productcode='. wp_json_encode($addon_json_array).'&domain='.$site_url;
        // verify token
        $verifytransactionkey = $this->verifytransactionkey($token, $url);
        if($verifytransactionkey['status'] == 0){
            $result['error'] = $verifytransactionkey['message'];
            $result = wp_json_encode($result);
            return $result;
        }
        $install_count = 0;

        $installed = $this->install_plugin($url);
        if ( !is_wp_error( $installed ) && $installed ) {
            // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.
            if(strstr($key, 'js-support-ticket-')){
                update_option('transaction_key_for_'.$key,$token);
            }

            if(strstr($key, 'js-support-ticket-')){
                $activate = activate_plugin( $key.'/'.$key.'.php' );
                $install_count++;
            }

            // run update sql
            if ($installedversion != $newversion) {
                $optionname = 'jsst-addon-'. $plugin_slug .'s-version';
                update_option($optionname, $newversion);
                $plugin_path = WP_CONTENT_DIR;
                $plugin_path = $plugin_path.'/plugins/'.$key.'/includes';
                if(is_dir($plugin_path . '/sql/') && is_readable($plugin_path . '/sql/')){
                    if($installedversion != ''){
                        $installedversion = str_replace('.','', $installedversion);
                    }
                    if($newversion != ''){
                        $newversion = str_replace('.','', $newversion);
                    }
                    JSSTincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromUpdateDir($installedversion,$newversion,$plugin_path . '/sql/');
                    $updatesdir = $plugin_path.'/sql/';
                    if(preg_match('/js-support-ticket-[a-zA-Z]+/', $updatesdir)){
                        $this->jsstRemoveAddonUpdatesFolder($updatesdir);
                    }
                }else{
                    JSSTincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromLive($installedversion,$newversion,$plugin_slug);
                }
            }

        }else{
            $result['error'] = esc_html(__('Addon Installation Failed','js-support-ticket'));
            $result = wp_json_encode($result);
            return $result;
        }

        $result['success'] = esc_html(__('Addon Installed Successfully','js-support-ticket'));
        $result = wp_json_encode($result);
        return $result;
    }

    function install_plugin( $plugin_zip ) {

        do_action('jssupportticket_load_wp_admin_file');
        WP_Filesystem();

        $tmpfile = download_url( $plugin_zip);

        if ( !is_wp_error( $tmpfile ) && $tmpfile ) {
            $plugin_path = WP_CONTENT_DIR;
            $plugin_path = $plugin_path.'/plugins/';
            $path = JSST_PLUGIN_PATH.'addon.zip';

            copy( $tmpfile, $path );

            $unzipfile = unzip_file( $path, $plugin_path);

            if ( file_exists( $path ) ) {
                wp_delete_file( $path ); // must unlink afterwards
            }
            if ( file_exists( $tmpfile ) ) {
                wp_delete_file( $tmpfile ); // must unlink afterwards
            }

            if ( is_wp_error( $unzipfile ) ) {
                $result['error'] = esc_html(__('Addon installation failed','js-support-ticket')).'.';
                $result['error'] .= " ".esc_html(jssupportticket::JSST_getVarValue($unzipfile->get_error_message()));
                $result = wp_json_encode($result);
                return $result;
            } else {
                return true;
            }
        }else{
            $error_string = $tmpfile->get_error_message();
            $result['error'] = esc_html(__('Addon Installation Failed, File download error','js-support-ticket')).'!'.$error_string;
            $result = wp_json_encode($result);
            return $result;
        }
    }

    function JSSTAddonsAutoUpdate(){
        /*
            code for auto update check from configuration
        */

        $jsst_addons_auto_update = JSSTincluder::getJSModel('configuration')->getConfigValue('jsst_addons_auto_update');
        if( $jsst_addons_auto_update != 1){
            return;
        }
        
        require_once JSST_PLUGIN_PATH.'includes/addon-updater/jsstupdater.php';
        $JS_SUPPORTTICKETUpdater  = new JS_SUPPORTTICKETUpdater();
        $cdnversiondata = $JS_SUPPORTTICKETUpdater->getPluginVersionDataFromCDN();

        $jssupportticket_addons = $this->getJSSTAddonsArray();

        $installed_plugins = get_plugins();
        $need_to_update = array();
        $site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        $status_prefix = 'key_status_for_js-support-ticket_';
        $final_addon_json_array = array();
        foreach ($jssupportticket_addons as $key1 => $value1) {
            $matched = 0;
            $version = "";
            foreach ($installed_plugins as $name => $value) {
                $install_plugin_name = jssupportticketphplib::JSST_str_replace(".php","",jssupportticketphplib::JSST_basename($name));
                if($key1 == $install_plugin_name){
                    $matched = 1;
                    $version = $value["Version"];
                    $install_plugin_matched_name = $install_plugin_name;
                }
            }
            if($matched == 1){ //installed
                $name = $key1;
                $title = $value1['title'];
                $cdnavailableversion = "";
                foreach ($cdnversiondata as $cdnname => $cdnversion) {
                    $addon_json_array = array();
                    $addon_json_final_array = array();
                    $install_plugin_name_simple = jssupportticketphplib::JSST_str_replace("-", "", $install_plugin_matched_name);
                    if($cdnname == jssupportticketphplib::JSST_str_replace("-", "", $install_plugin_matched_name)){
                        if($cdnversion > $version){ // new version available
                            $status = 'update_available';
                            $cdnavailableversion = $cdnversion;
                            $plugin_slug = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $name);
                            // get key status from local
                            $token = get_option('transaction_key_for_'.esc_attr($name));
                            $key_local_status = get_option($status_prefix . $token);
                            if($key_local_status == 1){
                                $addon_json_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $name);
                                $url = 'https://jshelpdesk.com/setup/index.php?token='.esc_attr($token).'&productcode='. wp_json_encode($addon_json_array).'&domain='.$site_url;
                                // verify token
                                $verifytransactionkey = $this->verifytransactionkey($token, $url);
                                
                                if($verifytransactionkey['status'] == 1){
                                    $final_addon_json_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $name);
                                    $addon_json_final_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $name);
                                    $need_to_update[] = array("name" => $name, "current_version" => $version, "available_version" => $cdnavailableversion, "plugin_slug" => $plugin_slug );
                                    $final_url = 'https://jshelpdesk.com/setup/index.php?token='.esc_attr($token).'&productcode='. wp_json_encode($final_addon_json_array).'&domain='.$site_url;
                                }
                            }
                        }
                    }    
                }
            }
        }
        $token = "";
        if(!empty($need_to_update)){
            $installed = $this->install_plugin($final_url);
            if ( !is_wp_error( $installed ) && $installed ) {
                // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.

                // run update sql
                foreach($need_to_update AS $update){
                    $installedversion = $update["current_version"];
                    $newversion = $update["available_version"];
                    $plugin_slug = $update["plugin_slug"];
                    $key = $update["name"];
                    if ($installedversion != $newversion) {
                        $optionname = 'jsst-addon-'. $plugin_slug .'s-version';
                        update_option($optionname, $newversion);
                        $plugin_path = WP_CONTENT_DIR;
                        $plugin_path = $plugin_path.'/plugins/'.$key.'/includes';
                        if(is_dir($plugin_path . '/sql/') && is_readable($plugin_path . '/sql/')){
                            if($installedversion != ''){
                                $installedversion = str_replace('.','', $installedversion);
                            }
                            if($newversion != ''){
                                $newversion = str_replace('.','', $newversion);
                            }
                            JSSTincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromUpdateDir($installedversion,$newversion,$plugin_path . '/sql/');
                            $updatesdir = $plugin_path.'/sql/';
                            if(preg_match('/js-support-ticket-[a-zA-Z]+/', $updatesdir)){
                                $this->jsstRemoveAddonUpdatesFolder($updatesdir);
                            }
                        }else{
                            JSSTincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromLive($installedversion,$newversion,$plugin_slug);
                        }
                    }
                }

            }else{
                return;
            }
        }
        return;
    }

    function verifytransactionkey($transactionkey, $url){
        $message = 1;
        if($transactionkey != ''){
            $response = wp_remote_post( $url );
            if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                $result = $response['body'];
                $result = json_decode($result,true);
                if(is_array($result) && isset($result[0]) && $result[0] == 0){
                    $result['status'] = 0;
                } else{
                    $result['status'] = 1;
                }
            }else{
                $result = false;
                if(!is_wp_error($response)){
                   $error = $response['response']['message'];
                }else{
                    $error = $response->get_error_message();
                }
            }
            if(is_array($result) && isset($result['status']) && $result['status'] == 1 ){ // means everthing ok
                $message = 1;
            }else{
                if(isset($result[0]) && $result[0] == 0){
                    $error = $result[1];
                }elseif(isset($result['error']) && $result['error'] != ''){
                    $error = $result['error'];
                }
                $message = 0;
            }
        }else{
            $message = 0;
            $error = esc_html(__('Please insert activation key to proceed','js-support-ticket')).'!';
        }
        $array['data'] = array();
        if ($message == 0) {
            $array['status'] = 0;
            $array['message'] = $error;
        } else {
            $array['status'] = 1;
            $array['message'] = 'success';
        }
        return $array;
    }

    function jsstRemoveAddonUpdatesFolder($dir)
    {
        $structure = glob(rtrim($dir, "/") . '/*');
        if (is_array($structure)) {
            foreach ($structure as $file) {
                if (is_dir($file)) {
                    $this->jsstRemoveAddonUpdatesFolder($file);
                } elseif (is_file($file)) {
                    wp_delete_file($file);
                }
            }
        }
        @rmdir($dir);
    }

    function generateIndexFile($file_directory) {
        global $wp_filesystem;

        // Initialize the WP_Filesystem
        if (!is_a($wp_filesystem, 'WP_Filesystem_Base')) {
            require_once ABSPATH . 'wp-admin/includes/file.php'; // Include WP Filesystem functions
            $creds = request_filesystem_credentials(site_url());

            if (!WP_Filesystem($creds)) {
                wp_die('Could not initialize the filesystem.');
            }
        }

        // Get the uploads directory path
        $uploads_dir = wp_upload_dir();
        $uploads_path = $uploads_dir['basedir'];

        // Normalize the paths to ensure consistency
        $file_directory = rtrim($file_directory, '/');
        $uploads_path = rtrim($uploads_path, '/');

        // Check if the given directory is within the uploads directory
        if (jssupportticketphplib::JSST_strpos($file_directory, $uploads_path) === 0) {
            // Start from the given directory and move up to the uploads directory
            $current_dir = $file_directory;
            while ($current_dir !== $uploads_path) {
                // Path to the index.php file in the current directory
                $index_file = $current_dir . '/index.html';

                // Create the index.php file if it does not exist
                if (!$wp_filesystem->exists($index_file)) {
                    $wp_filesystem->put_contents($index_file, '', FS_CHMOD_FILE); // FS_CHMOD_FILE ensures correct file permissions
                }

                // Move up to the parent directory
                $current_dir = dirname($current_dir);
            }

            // Finally, check and create the index.php file in the uploads directory
            $uploads_index_file = $uploads_path . '/index.html';
            if (!$wp_filesystem->exists($uploads_index_file)) {
                // $wp_filesystem->put_contents($uploads_index_file, '', FS_CHMOD_FILE);
            }
        }

        return;
    }

    function jsst_check_license_status() {
        // Get all distinct transaction keys
        $query = "
            SELECT DISTINCT option_value 
            FROM `" . jssupportticket::$_db->prefix . "options`
            WHERE option_name LIKE 'transaction_key_for_js-support-ticket%'
        ";
        $transaction_keys = jssupportticket::$_db->get_col($query);

        if (empty($transaction_keys)) return;

        $status_prefix = 'key_status_for_js-support-ticket_';
        $site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        $show_key_expiry_msg = 0;

        foreach ($transaction_keys as $key) {
            // Build query string for GET request
            $query_args = [
                'token'   => $key,
                'domain'  => $site_url,
                'request' => 'keyexpirycheck'
            ];

            $url = add_query_arg($query_args, 'https://jshelpdesk.com/setup/index.php');

            // Perform GET request
            $response = wp_remote_get($url, [ 'timeout' => 15 ]);

            if (is_wp_error($response)) {
                continue; // Skip on error
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (!is_array($data) || !isset($data['status'])) {
                continue; // Invalid response
            }

            // Save status
            update_option($status_prefix . $key, $data['status'], false);

            // Save expiry date if available
            if ($data['status'] == 1 && !empty($data['expirydate'])) {
                if (strtotime(current_time('mysql')) > strtotime($data['expirydate'])) {
                    $show_key_expiry_msg = 1;
                }
            } else {
                $show_key_expiry_msg = 1;
            }
        }

        update_option('jsst_show_key_expiry_msg', $show_key_expiry_msg, false);
    }

}

?>
