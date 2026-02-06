<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTjssupportticketModel {

    function getControlPanelData() {

        //determine user
        $jsst_user_is = 'unknown';
        if(JSSTincluder::getObjectClass('user')->isguest()){
            $jsst_user_is = 'visitor';
        }else{
            if(in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                $jsst_user_is = 'agent';
            }else{
                $jsst_user_is = 'user';
            }
        }
        //check if any addon is installed
        $jsst_addon_are_installed = !empty(jssupportticket::$_active_addons) ? true : false;

        if( $jsst_user_is == 'agent' ){

            $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
            $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);
            jssupportticket::$jsst_data[0]['user-name'] = JSSTincluder::getJSModel('agent')->getMyName($jsst_staffid);

            $jsst_tickets = $this->getAgentLatestTicketsForCp($jsst_staffid);
            if($jsst_tickets){
                jssupportticket::$jsst_data[0]['agent-tickets'] = $jsst_tickets;
            }

            $jsst_ticketStats = $this->getAgentTicketStats($jsst_staffid);
            if($jsst_ticketStats){
                jssupportticket::$jsst_data[0]['count'] = $jsst_ticketStats;
            }

            //data for graph
            $this->getAgentCpChartData($jsst_staffid);

        }

        if( $jsst_user_is == 'user' ){
            $jsst_uid = JSSTincluder::getObjectClass('user')->uid();

            $jsst_user_name =  '';
            if(is_numeric($jsst_uid) && $jsst_uid > 0){
                $jsst_currentUserName = JSSTincluder::getObjectClass('user')->getUserNameByUid($jsst_uid);
                if ($jsst_currentUserName->display_name){
                    $jsst_user_name =  $jsst_currentUserName->display_name;
                } elseif ($jsst_currentUserName->user_nicename){
                    $jsst_user_name =  $jsst_currentUserName->user_nicename;
                }
            }
            jssupportticket::$jsst_data[0]['user-name'] = $jsst_user_name;

            $jsst_tickets = $this->getUserLatestTicketsForCp($jsst_uid);
            if($jsst_tickets){
                jssupportticket::$jsst_data[0]['user-tickets'] = $jsst_tickets;
            }

            $jsst_ticketStats = $this->getUserTicketStats($jsst_uid);

            if($jsst_ticketStats){
                jssupportticket::$jsst_data[0]['count'] = $jsst_ticketStats;
            }

            //data for graph
            $this->getUserCpChartData($jsst_uid);
        }

        if( ( $jsst_user_is == 'agent' || $jsst_user_is == 'user' || $jsst_user_is == 'visitor' ) && $jsst_addon_are_installed ){

            $jsst_downloads = $this->getLatestDownloadsForCp();
            if($jsst_downloads){
                jssupportticket::$jsst_data[0]['latest-downloads'] = $jsst_downloads;
            }

            $jsst_announcements = $this->getLatestAnnouncementsForCp();
            if($jsst_announcements){
                jssupportticket::$jsst_data[0]['latest-announcements'] = $jsst_announcements;
            }

            $jsst_articles = $this->getLatestArticlesForCp();
            if($jsst_articles){
                jssupportticket::$jsst_data[0]['latest-articles'] = $jsst_articles;
            }

            $jsst_faqs = $this->getLatestFaqsForCp();
            if($jsst_faqs){
                jssupportticket::$jsst_data[0]['latest-faqs'] = $jsst_faqs;
            }
        }
    }

    function getControlPanelDataAdmin(){
        $jsst_curdate = date_i18n('Y-m-d');
        $jsst_cur_datetime = date_i18n('Y-m-d H:i:s');
        $jsst_fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));

        // Section 1: Top Metrics
        $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '".esc_sql($jsst_fromdate)."' AND date(created) <= '".esc_sql($jsst_curdate)."'";
        jssupportticket::$jsst_data['new_tickets'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isanswered != 1 AND status != 5 AND status != 6 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '".esc_sql($jsst_fromdate)."' AND date(created) <= '".esc_sql($jsst_curdate)."'";
        jssupportticket::$jsst_data['pending_tickets'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isanswered = 1 AND status != 5 AND status != 6 AND status != 1 AND date(created) >= '".esc_sql($jsst_fromdate)."' AND date(created) <= '".esc_sql($jsst_curdate)."'";
        jssupportticket::$jsst_data['answered_tickets'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets";
        jssupportticket::$jsst_data['total_tickets'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(closed) >= '".esc_sql($jsst_fromdate)."' AND DATE(closed) <= '".esc_sql($jsst_curdate)."' AND (status = 5 OR status = 6)";
        jssupportticket::$jsst_data['closed_today'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(closed) = '".esc_sql($jsst_curdate)."' AND (status = 5 OR status = 6)";
        jssupportticket::$jsst_data['tickets_closed_today'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '".esc_sql($jsst_curdate)."'";
        jssupportticket::$jsst_data['tickets_created_today'] = jssupportticket::$_db->get_var($jsst_query);

        // Section 2: Overdue Tickets
        if(in_array('overdue', jssupportticket::$_active_addons)) {
            $jsst_query = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isoverdue = 1 AND status != 5 AND status != 6 AND date(created) >= '".esc_sql($jsst_fromdate)."' AND date(created) <= '".esc_sql($jsst_curdate)."'";
            jssupportticket::$jsst_data['overdue_tickets_count'] = jssupportticket::$_db->get_var($jsst_query);
        }
        
        // Section 3: Unassigned Tickets
        $jsst_query = "SELECT ticket.id, ticket.uid, ticket.name, ticket.subject, ticket.status,  ticket.created, priority.priority, priority.prioritycolour FROM ".jssupportticket::$_db->prefix."js_ticket_tickets AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.staffid = 0 AND ticket.status NOT IN (5,6) ORDER BY ticket.created DESC LIMIT 3";
        jssupportticket::$jsst_data['unassigned_tickets'] = jssupportticket::$_db->get_results($jsst_query);

        // Section 4: Ticket Action History
        if(in_array('tickethistory', jssupportticket::$_active_addons)) {
            $jsst_query = "SELECT al.id, al.eventtype, al.message, al.referenceid, tic.ticketid, user.display_name AS name, al.datetime 
                        FROM ".jssupportticket::$_db->prefix."js_ticket_activity_log AS al
                        JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS tic ON al.referenceid = tic.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON  al.uid = user.id
                        LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_staff AS staff ON tic.staffid = staff.id
                        WHERE al.eventfor = 1 AND al.event = 'ticket'
                        ORDER BY al.datetime DESC LIMIT 8";
            jssupportticket::$jsst_data['ticket_action_history'] = jssupportticket::$_db->get_results($jsst_query);
        }

        // Section 5: Ticket Trends (Last 7 Days)
        $jsst_dates = [];
        $jsst_new_tickets_data = [];
        $jsst_pending_tickets_data = [];
        $jsst_answered_tickets_data = [];
        for ($jsst_i = 6; $jsst_i >= 0; $jsst_i--) {
            $jsst_date = gmdate('Y-m-d', strtotime("-$jsst_i days"));
            $jsst_dates[] = $jsst_date;
            $jsst_query_new = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '$jsst_date' AND status != 5 AND status != 6";
            $jsst_new_tickets_data[] = (int) jssupportticket::$_db->get_var($jsst_query_new);
            $jsst_query_pending = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isanswered != 1 AND status != 5 AND status != 6 AND (lastreply != '0000-00-00 00:00:00') AND DATE(created) = '$jsst_date'";
            $jsst_pending_tickets_data[] = (int) jssupportticket::$_db->get_var($jsst_query_pending);
            $jsst_query_answered = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE isanswered = 1 AND status != 5 AND status != 6 AND status != 1 AND DATE(created) = '$jsst_date'";
            $jsst_answered_tickets_data[] = (int) jssupportticket::$_db->get_var($jsst_query_answered);
            $jsst_query_closed = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE status IN (5,6) AND DATE(created) = '$jsst_date'";
            $jsst_closed_tickets_data[] = (int) jssupportticket::$_db->get_var($jsst_query_closed);
        }
        jssupportticket::$jsst_data['ticket_trends']['dates'] = $jsst_dates;
        jssupportticket::$jsst_data['ticket_trends']['new'] = $jsst_new_tickets_data;
        jssupportticket::$jsst_data['ticket_trends']['pending'] = $jsst_pending_tickets_data;
        jssupportticket::$jsst_data['ticket_trends']['answered'] = $jsst_answered_tickets_data;
        jssupportticket::$jsst_data['ticket_trends']['closed'] = $jsst_closed_tickets_data;

        // Section 6: Today's Ticket Distribution (Chart Data)
        $jsst_query_new_today = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '".esc_sql($jsst_curdate)."' AND status = 1";
        $jsst_query_answered_today = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '".esc_sql($jsst_curdate)."' AND isanswered = 1 AND status != 5 AND status != 6 AND status != 1";
        $jsst_query_pending_today = "SELECT COUNT(id) FROM ".jssupportticket::$_db->prefix."js_ticket_tickets WHERE DATE(created) = '".esc_sql($jsst_curdate)."' AND isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND status != 6";
        jssupportticket::$jsst_data['today_distribution']['new'] = (int) jssupportticket::$_db->get_var($jsst_query_new_today);
        jssupportticket::$jsst_data['today_distribution']['answered'] = (int) jssupportticket::$_db->get_var($jsst_query_answered_today);
        jssupportticket::$jsst_data['today_distribution']['pending'] = (int) jssupportticket::$_db->get_var($jsst_query_pending_today);

        // Fetch colors from statuses table (adjust table name if needed)
        $jsst_query_colors = "SELECT id, statusbgcolour FROM ".jssupportticket::$_db->prefix."js_ticket_statuses WHERE id IN (1,2,3,4,5)";
        $jsst_color1s = jssupportticket::$_db->get_results($jsst_query_colors, OBJECT_K);

        // Map colors
        jssupportticket::$jsst_data['today_distribution']['colors'] = [
            'new'      => isset($jsst_color1s[1]) ? esc_attr($jsst_color1s[1]->statusbgcolour) : '#4f46e5',
            'answered' => isset($jsst_color1s[2]) ? esc_attr($jsst_color1s[2]->statusbgcolour) : '#10b981',
            'pending'  => isset($jsst_color1s[3]) ? esc_attr($jsst_color1s[3]->statusbgcolour) : '#f59e0b',
            'closed'  => isset($jsst_color1s[5]) ? esc_attr($jsst_color1s[5]->statusbgcolour) : '#9ca3af',
        ];

        // Section 7: Latest Tickets
        $jsst_query = "SELECT ticket.id, ticket.uid, ticket.name, ticket.subject, ticket.status,  ticket.created, priority.priority, priority.prioritycolour  FROM ".jssupportticket::$_db->prefix."js_ticket_tickets AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
         ORDER BY ticket.created DESC LIMIT 3";
        jssupportticket::$jsst_data['latest_tickets'] = jssupportticket::$_db->get_results($jsst_query);

        // Section : Recently Replied Tickets
        $jsst_query = "SELECT ticket.id, ticket.uid, ticket.name, ticket.subject, ticket.status,  ticket.created, priority.priority, priority.prioritycolour  FROM ".jssupportticket::$_db->prefix."js_ticket_tickets AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.status = 4
        ORDER BY ticket.lastreply DESC LIMIT 3";
        jssupportticket::$jsst_data['recently_replied_tickets'] = jssupportticket::$_db->get_results($jsst_query);

        // Section : Recently Closed Tickets
        $jsst_query = "SELECT ticket.id, ticket.uid, ticket.name, ticket.subject, ticket.status,  ticket.created, priority.priority, priority.prioritycolour  FROM ".jssupportticket::$_db->prefix."js_ticket_tickets AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.status IN (5,6)
        ORDER BY ticket.closed DESC LIMIT 3";
        jssupportticket::$jsst_data['recently_closed_tickets'] = jssupportticket::$_db->get_results($jsst_query);

        // Get department names for latest tickets
        $jsst_query = "SELECT id, departmentname FROM ".jssupportticket::$_db->prefix."js_ticket_departments";
        $jsst_departments = jssupportticket::$_db->get_results($jsst_query);
        $jsst_department_map = [];
        foreach ($jsst_departments as $jsst_dept) {
            $jsst_department_map[$jsst_dept->id] = $jsst_dept->departmentname;
        }
        jssupportticket::$jsst_data['department_map'] = $jsst_department_map;

        // Get status titles for latest tickets
        $jsst_query = "SELECT id, status, statuscolour, statusbgcolour FROM ".jssupportticket::$_db->prefix."js_ticket_statuses";
        $jsst_statuses = jssupportticket::$_db->get_results($jsst_query);
        $jsst_status_map = [];
        foreach ($jsst_statuses as $jsst_status) {
            $jsst_status_map[$jsst_status->id] = $jsst_status;
        }
        jssupportticket::$jsst_data['status_map'] = $jsst_status_map;

        // Section 8: Agent Workload
        if(in_array('agent', jssupportticket::$_active_addons)) {
            $jsst_query = "SELECT s.id, s.uid, CONCAT(s.firstname, ' ', s.lastname) AS agent_name,
                        SUM(CASE WHEN t.status = 1 THEN 1 ELSE 0 END) AS open_tickets,
                        SUM(CASE WHEN t.status = 2 AND t.isanswered = 0 THEN 1 ELSE 0 END) AS pending,
                        SUM(CASE WHEN t.status != 5 AND t.status != 6 THEN 1 ELSE 0 END) AS total_tickets,
                        SUM(CASE WHEN t.status != 1 AND t.status != 5 AND t.status != 6 AND t.isanswered = 1 THEN 1 ELSE 0 END) AS answered,
                        SUM(CASE WHEN t.status = 5 OR t.status = 6 THEN 1 ELSE 0 END) AS closed,
                        SUM(CASE WHEN t.isoverdue = 1 AND t.status != 5 AND t.status != 6 THEN 1 ELSE 0 END) AS overdue
                        FROM ".jssupportticket::$_db->prefix."js_ticket_staff AS s
                        LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON s.id = t.staffid
                        GROUP BY s.id ORDER BY total_tickets DESC LIMIT 5";
            jssupportticket::$jsst_data['agent_workload'] = jssupportticket::$_db->get_results($jsst_query);
        }
        
        // Section 10: Tickets by Status
        $jsst_query = "SELECT s.status, s.statusbgcolour, COUNT(t.id) AS ticket_count
                    FROM ".jssupportticket::$_db->prefix."js_ticket_statuses AS s
                    LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON s.id = t.status
                    GROUP BY s.id ORDER BY ticket_count DESC";

        $jsst_statuses = jssupportticket::$_db->get_results($jsst_query);

        $jsst_labels = [];
        $jsst_data   = [];
        $jsst_color1s = [];

        foreach ($jsst_statuses as $jsst_status) {
            $jsst_labels[] = esc_html(jssupportticket::JSST_getVarValue($jsst_status->status));
            $jsst_data[]   = (int) $jsst_status->ticket_count;
            $jsst_color1s[] = esc_attr($jsst_status->statusbgcolour ?: '#9ca3af'); // fallback gray
        }

        jssupportticket::$jsst_data['tickets_by_status'] = [
            'labels' => $jsst_labels,
            'data'   => $jsst_data,
            'colors' => $jsst_color1s,
        ];

        // Section 11: Tickets by Department
        $jsst_query = "SELECT d.departmentname, COUNT(t.id) AS ticket_count
                    FROM ".jssupportticket::$_db->prefix."js_ticket_departments AS d
                    LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON d.id = t.departmentid
                    GROUP BY d.id ORDER BY ticket_count DESC";

        $jsst_departments = jssupportticket::$_db->get_results($jsst_query);

        $jsst_labels = [];
        $jsst_data   = [];
        $jsst_color1s = [];

        $jsst_available_colors = ['#a855f7', '#10b981', '#d946b1', '#6b7280', '#06b6d4', '#6366f1', '#ec4899'];
        $jsst_color1_index = 0;

        foreach ($jsst_departments as $jsst_department) {
            $jsst_labels[] = esc_html(jssupportticket::JSST_getVarValue($jsst_department->departmentname));
            $jsst_data[]   = (int) $jsst_department->ticket_count;

            // Assign colors round-robin from predefined palette
            $jsst_color1s[] = $jsst_available_colors[$jsst_color1_index % count($jsst_available_colors)];
            $jsst_color1_index++;
        }

        jssupportticket::$jsst_data['tickets_by_department'] = [
            'labels' => $jsst_labels,
            'data'   => $jsst_data,
            'colors' => $jsst_color1s,
        ];
        
        // Section 12: Tickets by Priorities
        $jsst_query = "SELECT p.priority, p.prioritycolour, COUNT(t.id) AS ticket_count
                    FROM ".jssupportticket::$_db->prefix."js_ticket_priorities AS p
                    LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON p.id = t.priorityid
                    GROUP BY p.id ORDER BY ticket_count DESC";

        $jsst_priorities = jssupportticket::$_db->get_results($jsst_query);

        $jsst_labels = [];
        $jsst_data   = [];
        $jsst_color1s = [];

        foreach ($jsst_priorities as $jsst_priority) {
            $jsst_labels[] = esc_html(jssupportticket::JSST_getVarValue($jsst_priority->priority));
            $jsst_data[]   = (int) $jsst_priority->ticket_count;
            $jsst_color1s[] = esc_attr($jsst_priority->prioritycolour ?: '#999999');
        }

        jssupportticket::$jsst_data['tickets_by_priorities'] = [
            'labels' => $jsst_labels,
            'data'   => $jsst_data,
            'colors' => $jsst_color1s,
        ];


// Section 13: Tickets by Products
$jsst_query = "SELECT product.product, COUNT(t.id) AS ticket_count
            FROM ".jssupportticket::$_db->prefix."js_ticket_products AS product
            LEFT JOIN ".jssupportticket::$_db->prefix."js_ticket_tickets AS t ON product.id = t.productid
            GROUP BY product.id ORDER BY ticket_count DESC";

        $jsst_products = jssupportticket::$_db->get_results($jsst_query);

        $jsst_labels = [];
        $jsst_data   = [];
        $jsst_color1s = [];
        $jsst_borderColors = [];

        $jsst_available_colors = ['#6366f1', '#ec4899', '#06b6d4', '#84cc16', '#ef4444', '#f59e0b', '#3b82f6'];
        $jsst_color1_index = 0;

        foreach ($jsst_products as $jsst_product) {
            $jsst_labels[] = esc_html(jssupportticket::JSST_getVarValue($jsst_product->product));
            $jsst_data[]   = (int) $jsst_product->ticket_count;

            $jsst_base = $jsst_available_colors[$jsst_color1_index % count($jsst_available_colors)];
            $jsst_color1s[] = $jsst_base . 'b3';   // ~70% opacity (hex + alpha)
            $jsst_borderColors[] = $jsst_base;    // solid border

            $jsst_color1_index++;
        }

        jssupportticket::$jsst_data['tickets_by_products'] = [
            'labels' => $jsst_labels,
            'data'   => $jsst_data,
            'colors' => $jsst_color1s,
            'border' => $jsst_borderColors,
        ];
        
        // Section 14: List of Saved Replies (Canned Responses)
        if(in_array('cannedresponses', jssupportticket::$_active_addons)) {
            $jsst_query = "SELECT id, title FROM ".jssupportticket::$_db->prefix."js_ticket_department_message_premade LIMIT 6";
            jssupportticket::$jsst_data['saved_replies'] = jssupportticket::$_db->get_results($jsst_query);
        }
        
        // Section 15: Open Tickets by Age
        $jsst_today       = date_i18n('Y-m-d');
        $jsst_yesterday   = date_i18n('Y-m-d', strtotime('-1 day'));
        $jsst_two_days    = date_i18n('Y-m-d', strtotime('-2 days'));
        $jsst_three_days  = date_i18n('Y-m-d', strtotime('-3 days'));
        $jsst_four_days   = date_i18n('Y-m-d', strtotime('-4 days'));
        $jsst_five_days   = date_i18n('Y-m-d', strtotime('-5 days'));
        $jsst_six_days    = date_i18n('Y-m-d', strtotime('-6 days'));

        // Today
        $jsst_query_today = "SELECT COUNT(id) 
            FROM ".jssupportticket::$_db->prefix."js_ticket_tickets 
            WHERE status NOT IN (5,6) AND DATE(created) = '".esc_sql($jsst_today)."'";

        // Yesterday
        $jsst_query_yesterday = "SELECT COUNT(id) 
            FROM ".jssupportticket::$_db->prefix."js_ticket_tickets 
            WHERE status NOT IN (5,6) AND DATE(created) = '".esc_sql($jsst_yesterday)."'";

        // 2 days ago
        $jsst_query_2_days = "SELECT COUNT(id) 
            FROM ".jssupportticket::$_db->prefix."js_ticket_tickets 
            WHERE status NOT IN (5,6) AND DATE(created) = '".esc_sql($jsst_two_days)."'";

        // 3 days ago
        $jsst_query_3_days = "SELECT COUNT(id) 
            FROM ".jssupportticket::$_db->prefix."js_ticket_tickets 
            WHERE status NOT IN (5,6) AND DATE(created) = '".esc_sql($jsst_three_days)."'";

        // 4 days ago
        $jsst_query_4_days = "SELECT COUNT(id) 
            FROM ".jssupportticket::$_db->prefix."js_ticket_tickets 
            WHERE status NOT IN (5,6) AND DATE(created) = '".esc_sql($jsst_four_days)."'";

        // 5 days ago
        $jsst_query_5_days = "SELECT COUNT(id) 
            FROM ".jssupportticket::$_db->prefix."js_ticket_tickets 
            WHERE status NOT IN (5,6) AND DATE(created) = '".esc_sql($jsst_five_days)."'";

        // 6+ days ago
        $jsst_query_6_plus = "SELECT COUNT(id) 
            FROM ".jssupportticket::$_db->prefix."js_ticket_tickets 
            WHERE status NOT IN (5,6) AND DATE(created) <= '".esc_sql($jsst_six_days)."'";

        jssupportticket::$jsst_data['tickets_by_age'] = [
            'today'      => (int) jssupportticket::$_db->get_var($jsst_query_today),
            'yesterday'  => (int) jssupportticket::$_db->get_var($jsst_query_yesterday),
            'two_days'   => (int) jssupportticket::$_db->get_var($jsst_query_2_days),
            'three_days' => (int) jssupportticket::$_db->get_var($jsst_query_3_days),
            'four_days'  => (int) jssupportticket::$_db->get_var($jsst_query_4_days),
            'five_days'  => (int) jssupportticket::$_db->get_var($jsst_query_5_days),
            'six_plus'   => (int) jssupportticket::$_db->get_var($jsst_query_6_plus),
        ];

        // Section 16: Most Active Customers
        $jsst_query = "SELECT t.uid, 
                         COALESCE(u.display_name, '') AS name, 
                         COUNT(t.id) AS ticket_count
                  FROM " . jssupportticket::$_db->prefix . "js_ticket_tickets AS t
                  LEFT JOIN " . jssupportticket::$_db->prefix . "js_ticket_users AS u 
                         ON t.uid = u.id
                  WHERE t.uid != 0
                  GROUP BY t.uid
                  ORDER BY ticket_count DESC
                  LIMIT 4";

        jssupportticket::$jsst_data['most_active_customers'] = jssupportticket::$_db->get_results($jsst_query);
        
        // Section 17: Active Timers
        if(in_array('timetracking', jssupportticket::$_active_addons)) {
            $jsst_query = "SELECT t.id, t.ticketid, t.name, t.subject, st.usertime, st.created FROM ".jssupportticket::$_db->prefix."js_ticket_tickets AS t 
                    JOIN ".jssupportticket::$_db->prefix."js_ticket_staff_time AS st ON t.id = st.ticketid 
                    WHERE st.status = 1 ORDER BY st.created DESC LIMIT 5";
            jssupportticket::$jsst_data['active_timers'] = jssupportticket::$_db->get_results($jsst_query);
        }

        $jsst_default_options = [
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

        jssupportticket::$jsst_data['jssupportticket_admin_charts_visibility'] = get_option('jssupportticket_admin_charts_visibility', $jsst_default_options);

        jssupportticket::$jsst_data['update_avaliable_for_addons'] = $this->showUpdateAvaliableAlert();
    }

    function showUpdateAvaliableAlert(){
        require_once JSST_PLUGIN_PATH.'includes/addon-updater/jsstupdater.php';
        $jsst_JS_SUPPORTTICKETUpdater  = new JS_SUPPORTTICKETUpdater();
        $jsst_cdnversiondata = $jsst_JS_SUPPORTTICKETUpdater->getPluginVersionDataFromCDN();
        $jsst_not_installed = array();

        $jsst_jssupportticket_addons = $this->getJSSTAddonsArray();
        $jsst_installed_plugins = get_plugins();
        $jsst_count = 0;
        foreach ($jsst_jssupportticket_addons as $jsst_key1 => $jsst_value1) {
            $jsst_matched = 0;
            $jsst_version = "";
            foreach ($jsst_installed_plugins as $jsst_name => $jsst_value) {
                $jsst_install_plugin_name = str_replace(".php","",basename($jsst_name));
                if($jsst_key1 == $jsst_install_plugin_name){
                    $jsst_matched = 1;
                    $jsst_version = $jsst_value["Version"];
                    $jsst_install_plugin_matched_name = $jsst_install_plugin_name;
                }
            }
            if($jsst_matched == 1){ //installed
                $jsst_name = $jsst_key1;
                $jsst_title = $jsst_value1['title'];
                $jsst_img = str_replace("js-support-ticket-", "", $jsst_key1).'.png';
                $jsst_cdnavailableversion = "";
                foreach ($jsst_cdnversiondata as $jsst_cdnname => $jsst_cdnversion) {
                    $jsst_install_plugin_name_simple = str_replace("-", "", $jsst_install_plugin_matched_name);
                    if($jsst_cdnname == str_replace("-", "", $jsst_install_plugin_matched_name)){
                        if($jsst_cdnversion > $jsst_version){ // new version available
                            $jsst_count++;
                        }
                    }    
                }
            }
        }
        return $jsst_count;
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

    function getAgentLatestTicketsForCp($jsst_staffid){
        if(!is_numeric($jsst_staffid)){
            return false;
        }

        $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
        if($jsst_allowed == true){
            $jsst_agent_conditions = "1 = 1";
        }else{
            $jsst_agent_conditions = "ticket.staffid = $jsst_staffid OR ticket.departmentid IN ( SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($jsst_staffid)." )";
        }

        //latest tickets with latest reply
        $jsst_query = "SELECT DISTINCT ticket.*,
            department.departmentname AS departmentname,
            priority.priority AS priority,
            priority.prioritycolour AS prioritycolour,
            staff.photo AS staffphoto,
            staff.id AS staffid,
            assignstaff.firstname AS staffname,
            status.status AS statustitle,
            status.statuscolour,
            status.statusbgcolour,
            r.name AS last_reply_name,
            r.uid AS last_reply_uid,
            r.created AS last_reply_created
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
            LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
            LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
            LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.uid = ticket.uid
            LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS assignstaff ON ticket.staffid = assignstaff.id
            JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON status.id = ticket.status
            LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS r 
                ON r.id = (
                    SELECT r2.id 
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS r2 
                    WHERE r2.ticketid = ticket.id 
                    ORDER BY r2.created DESC 
                    LIMIT 1
                )
            WHERE (".esc_sql($jsst_agent_conditions).") 
            ORDER BY ticket.created DESC 
            LIMIT 3";
        
        $jsst_tickets = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_tickets;
    }

    function getAgentTicketStats($jsst_staffid){
        if(!is_numeric($jsst_staffid)){
            return false;
        }

        $jsst_result = array();

        $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
        if($jsst_allowed == true){
            $jsst_agent_conditions = "1 = 1";
        }else{
            $jsst_agent_conditions = "ticket.staffid = $jsst_staffid OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($jsst_staffid).")";
        }

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($jsst_agent_conditions).") AND (ticket.status != 5 AND ticket.status !=6) ";
        $jsst_result['openticket'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($jsst_agent_conditions).") AND ticket.isanswered = 1 AND ticket.status != 5 AND ticket.status != 1 ";
        $jsst_result['answeredticket'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($jsst_agent_conditions).") AND ticket.isanswered != 1 AND ticket.status != 5 AND ticket.status != 1 AND (lastreply != '0000-00-00 00:00:00') ";
        $jsst_result['pendingticket'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($jsst_agent_conditions).") AND (ticket.status = 5 OR ticket.status = 6) ";
        $jsst_result['closedticket'] = jssupportticket::$_db->get_var($jsst_query);


        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($jsst_agent_conditions).") AND ticket.isoverdue = 1 ";
        $jsst_result['overdue'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE (".esc_sql($jsst_agent_conditions).")  ";
        $jsst_result['allticket'] = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_result;
    }

    function getAgentCpChartData($jsst_staffid){
        if(!is_numeric($jsst_staffid) || jssupportticket::$_config['cplink_ticketstats_staff'] != 1){
            return false;
        }

        $jsst_curdate  = date_i18n('Y-m-d');
        $jsst_fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -7 days")); // last 7 days

        // Chart header
        jssupportticket::$jsst_data['stack_chart_horizontal']['title'] =
            "['Date','". esc_html(__('Overdue','js-support-ticket'))."','". esc_html(__('Pending','js-support-ticket'))."','". esc_html(__('Answered','js-support-ticket'))."','". esc_html(__('New','js-support-ticket'))."']";

        $jsst_rows = [];

        // Loop each day in range
        $jsst_period = new DatePeriod(
            new DateTime($jsst_fromdate),
            new DateInterval('P1D'),
            (new DateTime($jsst_curdate))->modify('+1 day')
        );

        foreach ($jsst_period as $jsst_dateObj) {
            $jsst_day = $jsst_dateObj->format('Y-m-d');

            // Count tickets for this day
            $jsst_overdue = jssupportticket::$_db->get_var("
                SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`
                WHERE isoverdue = 1 AND status != 5 AND status != 6 AND DATE(created) = '".esc_sql($jsst_day)."'
            ");

            $jsst_pending = jssupportticket::$_db->get_var("
                SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`
                WHERE isanswered != 1 AND status != 5 AND status != 6 AND (lastreply != '0000-00-00 00:00:00') AND DATE(created) = '".esc_sql($jsst_day)."'
            ");

            $jsst_answered = jssupportticket::$_db->get_var("
                SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`
                WHERE isanswered = 1 AND status NOT IN (1,5,6) AND DATE(created) = '".esc_sql($jsst_day)."'
            ");

            $jsst_new = jssupportticket::$_db->get_var("
                SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`
                WHERE status = 1 AND DATE(created) = '".esc_sql($jsst_day)."'
            ");

            $jsst_rows[] = "['{$jsst_day}', ".intval($jsst_overdue).", ".intval($jsst_pending).", ".intval($jsst_answered).", ".intval($jsst_new)."]";
        }

        jssupportticket::$jsst_data['stack_chart_horizontal']['data'] = implode(",", $jsst_rows);
    }

    function getUserCpChartData($jsst_uid){
        if(!is_numeric($jsst_uid)){
            return false;
        }

        $jsst_curdate  = date_i18n('Y-m-d');
        $jsst_fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -7 days")); // last 7 days

        // Chart header
        jssupportticket::$jsst_data['stack_chart_horizontal']['title'] =
            "['Date','". esc_html(__('Overdue','js-support-ticket'))."','". esc_html(__('Pending','js-support-ticket'))."','". esc_html(__('Answered','js-support-ticket'))."','". esc_html(__('New','js-support-ticket'))."']";

        $jsst_rows = [];

        // Loop each day in range
        $jsst_period = new DatePeriod(
            new DateTime($jsst_fromdate),
            new DateInterval('P1D'),
            (new DateTime($jsst_curdate))->modify('+1 day')
        );

        foreach ($jsst_period as $jsst_dateObj) {
            $jsst_day = $jsst_dateObj->format('Y-m-d');

            // Count tickets for this day
            $jsst_overdue = jssupportticket::$_db->get_var("
                SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`
                WHERE uid = ".esc_sql($jsst_uid)." AND isoverdue = 1 AND status != 5 AND status != 6 AND DATE(created) = '".esc_sql($jsst_day)."'
            ");

            $jsst_pending = jssupportticket::$_db->get_var("
                SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`
                WHERE uid = ".esc_sql($jsst_uid)." AND isanswered != 1 AND status != 5 AND status != 6 AND (lastreply != '0000-00-00 00:00:00') AND DATE(created) = '".esc_sql($jsst_day)."'
            ");

            $jsst_answered = jssupportticket::$_db->get_var("
                SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`
                WHERE uid = ".esc_sql($jsst_uid)." AND isanswered = 1 AND status NOT IN (1,5,6) AND DATE(created) = '".esc_sql($jsst_day)."'
            ");

            $jsst_new = jssupportticket::$_db->get_var("
                SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`
                WHERE uid = ".esc_sql($jsst_uid)." AND status = 1 AND DATE(created) = '".esc_sql($jsst_day)."'
            ");

            $jsst_rows[] = "['{$jsst_day}', ".intval($jsst_overdue).", ".intval($jsst_pending).", ".intval($jsst_answered).", ".intval($jsst_new)."]";
        }

        jssupportticket::$jsst_data['stack_chart_horizontal']['data'] = implode(",", $jsst_rows);
    }

    function getUserLatestTicketsForCp($jsst_uid){
        if(!is_numeric($jsst_uid)){
            return false;
        }

        do_action('jsst_addon_user_cp_tickets');

        $jsst_query = "SELECT ticket.*,
            department.departmentname AS departmentname,
            priority.priority AS priority,
            priority.prioritycolour AS prioritycolour,
            status.status AS statustitle,
            status.statuscolour,
            status.statusbgcolour,
            r.name AS last_reply_name,
            r.uid AS last_reply_uid,
            r.created AS last_reply_created
            ".jssupportticket::$_addon_query['select']."
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority 
            ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department 
            ON ticket.departmentid = department.id
        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status 
            ON status.id = ticket.status
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS r 
            ON r.id = (
                SELECT r2.id 
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS r2 
                WHERE r2.ticketid = ticket.id 
                ORDER BY r2.created DESC 
                LIMIT 1
            )
        ".jssupportticket::$_addon_query['join']."
        WHERE ticket.uid = " . esc_sql($jsst_uid) . "
        ORDER BY ticket.created DESC 
        LIMIT 3";

        $jsst_tickets = jssupportticket::$_db->get_results($jsst_query);

        do_action('jsst_reset_aadon_query');

        return $jsst_tickets;
    }

    function getUserTicketStats($jsst_uid){
        if(!is_numeric($jsst_uid)){
            return false;
        }

        $jsst_result = array();

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        WHERE ticket.uid = ".esc_sql($jsst_uid)." AND (ticket.status != 5 AND ticket.status != 6)";
        $jsst_result['openticket'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.uid = ".esc_sql($jsst_uid)." AND ticket.status = 4 ";
        $jsst_result['answeredticket'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.uid = ".esc_sql($jsst_uid)." AND (ticket.status = 5 OR ticket.status = 6)";
        $jsst_result['closedticket'] = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(ticket.id)
        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
        WHERE ticket.uid = ".esc_sql($jsst_uid);
        $jsst_result['allticket'] = jssupportticket::$_db->get_var($jsst_query);

        return $jsst_result;
    }

    function getLatestDownloadsForCp(){
        if( in_array('download', jssupportticket::$_active_addons) ){
            $jsst_query = "SELECT download.title, download.id AS downloadid
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_downloads` AS download
            WHERE download.status = 1 ORDER BY download.created DESC LIMIT 4";
            return jssupportticket::$_db->get_results($jsst_query);
        }
        return false;
    }

    function getLatestAnnouncementsForCp(){
        if( in_array('announcement', jssupportticket::$_active_addons) ){
            $jsst_query = "SELECT announcement.id, announcement.title
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_announcements` AS announcement
            WHERE announcement.status = 1 ORDER BY announcement.created DESC LIMIT 4";
            return jssupportticket::$_db->get_results($jsst_query);
        }
        return false;
    }


    function getLatestArticlesForCp(){
        if( in_array('knowledgebase', jssupportticket::$_active_addons) ){
            $jsst_query = "SELECT article.subject,article.content, article.id AS articleid
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_articles` AS article
            WHERE article.status = 1 ORDER BY article.created DESC LIMIT 4";
            return jssupportticket::$_db->get_results($jsst_query);
        }
        return false;
    }

    function getLatestFaqsForCp(){
        if( in_array('faq', jssupportticket::$_active_addons) ){
            $jsst_query = "SELECT faq.id, faq.subject, faq.content
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_faqs` AS faq
            WHERE faq.status = 1 ORDER BY faq.created DESC LIMIT 4";
            return jssupportticket::$_db->get_results($jsst_query);
        }
        return false;
    }


    function getStaffControlPanelData() {

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` ";
        $jsst_allticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00')";
        $jsst_openticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5";
        $jsst_closeticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1";
        $jsst_answeredticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5";
        $jsst_overdueticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00')";
        $jsst_pendingticket = jssupportticket::$_db->get_var($jsst_query);

        jssupportticket::$jsst_data['ticket_total']['allticket'] = $jsst_allticket;
        jssupportticket::$jsst_data['ticket_total']['openticket'] = $jsst_openticket;
        jssupportticket::$jsst_data['ticket_total']['closeticket'] = $jsst_closeticket;
        jssupportticket::$jsst_data['ticket_total']['answeredticket'] = $jsst_answeredticket;
        jssupportticket::$jsst_data['ticket_total']['overdueticket'] = $jsst_overdueticket;
        jssupportticket::$jsst_data['ticket_total']['pendingticket'] = $jsst_pendingticket;

        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets`";
        jssupportticket::$jsst_data['total_tickets']['total_ticket'] = jssupportticket::$_db->get_var($jsst_query);
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_departments`";
        jssupportticket::$jsst_data['total_tickets']['total_department'] = jssupportticket::$_db->get_var($jsst_query);

        if(in_array('agent', jssupportticket::$_active_addons)){
            $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_staff`";
            jssupportticket::$jsst_data['total_tickets']['total_staff'] = jssupportticket::$_db->get_var($jsst_query);
        }else{
            jssupportticket::$jsst_data['total_tickets']['total_staff'] = 0;
        }
        if(in_array('feedback', jssupportticket::$_active_addons)){
            $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_feedbacks`";
            jssupportticket::$jsst_data['total_tickets']['total_feedback'] = jssupportticket::$_db->get_var($jsst_query);
        }else{
            jssupportticket::$jsst_data['total_tickets']['total_feedback'] = 0;
        }
    }

    function makeDir($jsst_path) {
        if (empty($jsst_path)) {
            return;
        }

        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        // Replaced file_exists() with $jsst_wp_filesystem->exists()
        if (!$jsst_wp_filesystem->exists($jsst_path)) {
            // Replaced mkdir() with $jsst_wp_filesystem->mkdir()
            // 0755 is the standard permission for WordPress directories
            $jsst_wp_filesystem->mkdir($jsst_path, 0755);

            $jsst_index_file = $jsst_path . '/index.html';
            
            // Replaced fopen() and fclose() with $jsst_wp_filesystem->put_contents()
            // We pass an empty string to create a blank index.html file
            $jsst_wp_filesystem->put_contents($jsst_index_file, '', FS_CHMOD_FILE);
        }
    }

    function checkExtension($jsst_filename) {
        $jsst_i = strrpos($jsst_filename, ".");
        if (!$jsst_i)
            return 'N';
        $jsst_l = jssupportticketphplib::JSST_strlen($jsst_filename) - $jsst_i;
        $jsst_ext = jssupportticketphplib::JSST_substr($jsst_filename, $jsst_i + 1, $jsst_l);
        $jsst_extensions = jssupportticketphplib::JSST_explode(",", jssupportticket::$_config['file_extension']);
        $jsst_match = 'N';
        foreach ($jsst_extensions as $jsst_extension) {
            if (strtolower($jsst_extension) == jssupportticketphplib::JSST_strtolower($jsst_ext)) {
                $jsst_match = 'Y';
                break;
            }
        }
        return $jsst_match;
    }

    function getColorCode($jsst_filestring, $jsst_color1No) {
        if (strstr($jsst_filestring, '$jsst_color1' . $jsst_color1No)) {
            $jsst_path1 = jssupportticketphplib::JSST_strpos($jsst_filestring, '$jsst_color1' . $jsst_color1No);
            $jsst_path1 = jssupportticketphplib::JSST_strpos($jsst_filestring, '#', $jsst_path1);
            $jsst_path2 = jssupportticketphplib::JSST_strpos($jsst_filestring, ';', $jsst_path1);
            $jsst_color1code = jssupportticketphplib::JSST_substr($jsst_filestring, $jsst_path1, $jsst_path2 - $jsst_path1 - 1);
            return $jsst_color1code;
        }
    }

    //translation code
    function getListTranslations() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'get-list-translations')) {
            die('Security check Failed');
        }
        
        $jsst_result = array('error' => false);

        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            do_action('jssupportticket_load_wp_file');
        }
        WP_Filesystem();
        $jsst_wp_filesystem = $wp_filesystem;

        $jsst_path = WP_LANG_DIR;
        
        // Replaced is_dir() with $jsst_wp_filesystem->is_dir()
        if (!$jsst_wp_filesystem->is_dir($jsst_path)) {
            $this->makeDir($jsst_path);
        } else {
            $jsst_path = WP_LANG_DIR . '/plugins/';
            if (!$jsst_wp_filesystem->is_dir($jsst_path)) {
                $this->makeDir($jsst_path);
            }
        }

        // Replaced is_writeable() with $jsst_wp_filesystem->is_writable()
        if (!$jsst_wp_filesystem->is_writable($jsst_path)) {
            $jsst_result['error'] = esc_html(__('Dir is not writable', 'js-support-ticket')) . ' ' . $jsst_path;
        } else {
            if ($this->isConnected()) {
                $jsst_url = "https://jshelpdesk.com/translations/api/1.0/index.php";
                $jsst_post_data = array(
                    'product'        => 'js-support-ticket-wp',
                    'domain'         => get_site_url(),
                    'producttype'    => jssupportticket::$_config['producttype'],
                    'productcode'    => 'jsticket',
                    'productversion' => jssupportticket::$_config['productversion'],
                    'JVERSION'       => get_bloginfo('version'),
                    'method'         => 'getTranslations'
                );

                $jsst_response = wp_remote_post($jsst_url, array('body' => $jsst_post_data, 'timeout' => 45, 'sslverify' => false));
                
                if (!is_wp_error($jsst_response) && $jsst_response['response']['code'] == 200 && isset($jsst_response['body'])) {
                    $jsst_call_result = $jsst_response['body'];
                } else {
                    $jsst_call_result = false;
                    $jsst_error = is_wp_error($jsst_response) ? $jsst_response->get_error_message() : $jsst_response['response']['message'];
                }

                $jsst_result['data'] = jssupportticketphplib::JSST_htmlentities($jsst_call_result);
                if (!$jsst_call_result) {
                    $jsst_result['error'] = $jsst_error;
                }
            } else {
                $jsst_result['error'] = esc_html(__('Unable to connect to the server', 'js-support-ticket'));
            }
        }

        return wp_json_encode($jsst_result);
    }

    function makeLanguageCode($jsst_lang_name){
        $jsst_langarray = wp_get_installed_translations('core');
        $jsst_langarray = isset($jsst_langarray['default']) ? $jsst_langarray['default'] : array();
        $jsst_match = false;
        if(array_key_exists($jsst_lang_name, $jsst_langarray)){
            $jsst_lang_name = $jsst_lang_name;
            $jsst_match = true;
        }else{
            $jsst_m_lang = '';
            foreach($jsst_langarray AS $jsst_k => $jsst_v){
                if($jsst_lang_name[0].$jsst_lang_name[1] == $jsst_k[0].$jsst_k[1]){
                    $jsst_m_lang .= $jsst_k.', ';
                }
            }

            if($jsst_m_lang != ''){
                $jsst_m_lang = jssupportticketphplib::JSST_substr($jsst_m_lang, 0,strlen($jsst_m_lang) - 2);
                $jsst_lang_name = $jsst_m_lang;
                $jsst_match = 2;
            }else{
                $jsst_lang_name = $jsst_lang_name;
                $jsst_match = false;
            }
        }

        return array('match' => $jsst_match , 'lang_name' => $jsst_lang_name);
    }

    function validateAndShowDownloadFileName() {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'validate-and-show-download-filename')) {
            die('Security check Failed');
        }

        $jsst_lang_name = JSSTrequest::getVar('langname');
        if ($jsst_lang_name == '') {
            return '';
        }

        $jsst_result = array();
        $jsst_f_result = $this->makeLanguageCode($jsst_lang_name);
        $jsst_path = WP_LANG_DIR . '/plugins/';
        $jsst_result['error'] = false;

        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        if ($jsst_f_result['match'] === false) {
            $jsst_result['error'] = $jsst_lang_name . ' ' . esc_html(__('Language is not installed', 'js-support-ticket'));
        } 
        // Replaced is_writeable($jsst_path) with $jsst_wp_filesystem->is_writable($jsst_path)
        elseif (!$jsst_wp_filesystem->is_writable($jsst_path)) {
            $jsst_result['error'] = $jsst_lang_name . ' ' . esc_html(__('Language directory is not writable', 'js-support-ticket')) . ': ' . $jsst_path;
        } else {
            $jsst_input_html = '<input id="languagecode" class="text_area" type="text" value="' . esc_attr($jsst_lang_name) . '" name="languagecode">';
            if ($jsst_f_result['match'] === 2) {
                $jsst_input_html .= '<div id="js-emessage-wrapper-other" style="display:block;margin:20px 0px 20px;">';
                $jsst_input_html .= esc_html(__('Required language is not installed but similar language like', 'js-support-ticket')) . ': "<b>' . esc_html($jsst_f_result['lang_name']) . '</b>" ' . esc_html(__('is found in your system', 'js-support-ticket'));
                $jsst_input_html .= '</div>';
            }
            $jsst_result['input'] = jssupportticketphplib::JSST_htmlentities($jsst_input_html);
            $jsst_result['path'] = esc_html(__('Language code', 'js-support-ticket'));
        }

        return wp_json_encode($jsst_result);
    }

    function getLanguageTranslation() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'get-language-translation')) {
            die('Security check Failed');
        }
        
        $jsst_lang_name = JSSTrequest::getVar('langname');
        $jsst_language_code = JSSTrequest::getVar('filename');

        $jsst_result = array();
        $jsst_result['error'] = false;
        
        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        $jsst_path = WP_LANG_DIR . '/plugins/';

        // FIX: Replaced is_dir() and mkdir()
        if (!$jsst_wp_filesystem->is_dir($jsst_path)) {
            $jsst_wp_filesystem->mkdir($jsst_path, 0755);
        }

        if ($jsst_lang_name == '' || $jsst_language_code == '') {
            $jsst_result['error'] = esc_html(__('Empty values', 'js-support-ticket'));
            return wp_json_encode($jsst_result);
        }

        $jsst_final_path = $jsst_path . 'js-support-ticket-' . $jsst_language_code . '.po';

        $jsst_langarray = wp_get_installed_translations('core');
        $jsst_langarray = $jsst_langarray['default'];

        if (!array_key_exists($jsst_language_code, $jsst_langarray)) {
            $jsst_result['error'] = $jsst_lang_name . ' ' . esc_html(__('Language is not installed', 'js-support-ticket'));
            return wp_json_encode($jsst_result);
        }
        
        // Replaced is_writeable() for the directory
        if (!$jsst_wp_filesystem->is_writable($jsst_path)) {
            $jsst_result['error'] = $jsst_lang_name . ' ' . esc_html(__('Language directory is not writable', 'js-support-ticket')) . ': ' . $jsst_path;
            return wp_json_encode($jsst_result);
        }

        // Replaced file_exists() and touch()
        if (!$jsst_wp_filesystem->exists($jsst_final_path)) {
            $jsst_wp_filesystem->put_contents($jsst_final_path, '', FS_CHMOD_FILE);
        }

        // Replaced is_writeable() for the specific file
        if (!$jsst_wp_filesystem->is_writable($jsst_final_path)) {
            $jsst_result['error'] = esc_html(__('File is not writable', 'js-support-ticket')) . ': ' . $jsst_final_path;
        } else {
            if ($this->isConnected()) {
                $jsst_url = "https://jshelpdesk.com/translations/api/1.0/index.php";
                $jsst_post_data = array(
                    'product'        => 'js-support-ticket-wp',
                    'domain'         => get_site_url(),
                    'producttype'    => jssupportticket::$_config['producttype'],
                    'productcode'    => 'jsticket',
                    'productversion' => jssupportticket::$_config['productversion'],
                    'JVERSION'       => get_bloginfo('version'),
                    'translationcode' => $jsst_lang_name,
                    'method'         => 'getTranslationFile'
                );

                $jsst_response = wp_remote_post($jsst_url, array('body' => $jsst_post_data, 'timeout' => 7, 'sslverify' => false));
                
                if (!is_wp_error($jsst_response) && $jsst_response['response']['code'] == 200 && isset($jsst_response['body'])) {
                    $jsst_response_body = json_decode($jsst_response['body'], true);
                    if ($jsst_response_body && isset($jsst_response_body['file'])) {
                        $jsst_ret = $this->writeLanguageFile($jsst_final_path, $jsst_response_body['file']);
                        $jsst_result['data'] = esc_html(__('File successfully downloaded', 'js-support-ticket'));
                    } else {
                        $jsst_result['error'] = esc_html(__('Invalid response from server', 'js-support-ticket'));
                    }
                } else {
                    $jsst_result['error'] = is_wp_error($jsst_response) ? $jsst_response->get_error_message() : $jsst_response['response']['message'];
                }
            } else {
                $jsst_result['error'] = esc_html(__('Unable to connect to the server', 'js-support-ticket'));
            }
        }

        return wp_json_encode($jsst_result);
    }

    function writeLanguageFile($jsst_path, $jsst_url) {
        $jsst_result = true;
        
        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_wp_filesystem = $wp_filesystem;

        // 1. Download the file to a temporary location
        do_action('jssupportticket_load_wp_admin_file');
        $jsst_tmpfile = download_url($jsst_url);

        if (is_wp_error($jsst_tmpfile)) {
            return false; // Handle download failure
        }

        // 2. Use WP_Filesystem to move/copy the file
        // Replaces copy() and handles permissions automatically
        $jsst_copy_result = $jsst_wp_filesystem->copy($jsst_tmpfile, $jsst_path, true, FS_CHMOD_FILE);

        // 3. Cleanup the temporary file
        if ($jsst_wp_filesystem->exists($jsst_tmpfile)) {
            $jsst_wp_filesystem->delete($jsst_tmpfile); 
        }

        if (!$jsst_copy_result) {
            return false;
        }

        // 4. Convert PO to MO
        $this->phpmo_convert($jsst_path);
        
        return $jsst_result;
    }

    /**
     * Check if the server has an active internet connection.
     * Uses the WordPress HTTP API instead of direct sockets.
     */
    function isConnected() {
        // Ensure WordPress HTTP API is available
        if ( ! function_exists( 'wp_remote_head' ) ) {
            return false;
        }
        $jsst_response = wp_remote_head(
            'https://www.google.com',
            array(
                'timeout'     => 5,
                'redirection' => 0,
                'sslverify'   => true,
            )
        );
        if ( is_wp_error( $jsst_response ) ) {
            return false;
        }
        return true;
    }

    function phpmo_convert($jsst_input, $jsst_output = false) {
        if ( !$jsst_output )
            $jsst_output = jssupportticketphplib::JSST_str_replace( '.po', '.mo', $jsst_input );
        $jsst_hash = $this->phpmo_parse_po_file( $jsst_input );
        if ( $jsst_hash === false ) {
            return false;
        } else {
            $this->phpmo_write_mo_file( $jsst_hash, $jsst_output );
            return true;
        }
    }

    function phpmo_clean_helper($jsst_x) {
        if (is_array($jsst_x)) {
            foreach ($jsst_x as $jsst_k => $jsst_v) {
                $jsst_x[$jsst_k] = $this->phpmo_clean_helper($jsst_v);
            }
        } else {
            if ($jsst_x[0] == '"')
                $jsst_x = jssupportticketphplib::JSST_substr($jsst_x, 1, -1);
            $jsst_x = jssupportticketphplib::JSST_str_replace("\"\n\"", '', $jsst_x);
            $jsst_x = jssupportticketphplib::JSST_str_replace('$', '\\$', $jsst_x);
        }
        return $jsst_x;
    }
    /* Parse gettext .po files. */
    /* @link http://www.gnu.org/software/gettext/manual/gettext.html#PO-Files */
    function phpmo_parse_po_file($jsst_in) {
    if (!file_exists($jsst_in)){ return false; }
    $jsst_ids = array();
    $jsst_strings = array();
    $jsst_language = array();
    $jsst_lines = file($jsst_in);
    foreach ($jsst_lines as $jsst_line_num => $jsst_line) {
        if (strstr($jsst_line, 'msgid')){
			//$jsst_endpos = strrchr($jsst_line, '"');
			$jsst_endpos = strrpos($jsst_line, '"',7);
			if($jsst_endpos > 7){ // to avoid msgid ""
				$jsst_id = jssupportticketphplib::JSST_substr($jsst_line, 7, $jsst_endpos-7);
				$jsst_ids[] = $jsst_id;
			}
        }elseif(strstr($jsst_line, 'msgstr')){
			//$jsst_endpos = strrchr($jsst_line, '"');
			$jsst_endpos = strrpos($jsst_line, '"',8);
			if($jsst_endpos > 8){ // to avoid msgstr ""
				$jsst_string = jssupportticketphplib::JSST_substr($jsst_line, 8, $jsst_endpos-8);
				$jsst_strings[] = array($jsst_string);
			}
        }else{}
    }
    for ($jsst_i=0; $jsst_i<count($jsst_ids); $jsst_i++){
        //Shoaib
        if(isset($jsst_ids[$jsst_i]) && isset($jsst_strings[$jsst_i])){
            /*if($jsst_entry['msgstr'][0] == '""'){
                continue;
            }*/
            $jsst_language[$jsst_ids[$jsst_i]] = array('msgid' => $jsst_ids[$jsst_i], 'msgstr' =>$jsst_strings[$jsst_i]);
        }
    }
    return $jsst_language;
    }
    /* Write a GNU gettext style machine object. */
    /* @link http://www.gnu.org/software/gettext/manual/gettext.html#MO-Files */
    function phpmo_write_mo_file($jsst_hash, $jsst_out) {
        // sort by msgid
        ksort($jsst_hash, SORT_STRING);
        // our mo file data
        $jsst_mo = '';
        // header data
        $jsst_offsets = array ();
        $jsst_ids = '';
        $jsst_strings = '';
        foreach ($jsst_hash as $jsst_entry) {
            $jsst_id = $jsst_entry['msgid'];
            $jsst_str = implode("\x00", $jsst_entry['msgstr']);
            // keep track of offsets
            $jsst_offsets[] = array (
                            jssupportticketphplib::JSST_strlen($jsst_ids), jssupportticketphplib::JSST_strlen($jsst_id), jssupportticketphplib::JSST_strlen($jsst_strings), jssupportticketphplib::JSST_strlen($jsst_str)
                            );
            // plural msgids are not stored (?)
            $jsst_ids .= $jsst_id . "\x00";
            $jsst_strings .= $jsst_str . "\x00";
        }
        // keys start after the header (7 words) + index tables ($#hash * 4 words)
        $jsst_key_start = 7 * 4 + sizeof($jsst_hash) * 4 * 4;
        // values start right after the keys
        $jsst_value_start = $jsst_key_start +strlen($jsst_ids);
        // first all key offsets, then all value offsets
        $jsst_key_offsets = array ();
        $jsst_value_offsets = array ();
        // calculate
        foreach ($jsst_offsets as $jsst_v) {
            list ($jsst_o1, $jsst_l1, $jsst_o2, $jsst_l2) = $jsst_v;
            $jsst_key_offsets[] = $jsst_l1;
            $jsst_key_offsets[] = $jsst_o1 + $jsst_key_start;
            $jsst_value_offsets[] = $jsst_l2;
            $jsst_value_offsets[] = $jsst_o2 + $jsst_value_start;
        }
        $jsst_offsets = array_merge($jsst_key_offsets, $jsst_value_offsets);
        // write header
        $jsst_mo .= pack('Iiiiiii', 0x950412de, // magic number
        0, // version
        sizeof($jsst_hash), // number of entries in the catalog
        7 * 4, // key index offset
        7 * 4 + sizeof($jsst_hash) * 8, // value index offset,
        0, // hashtable size (unused, thus 0)
        $jsst_key_start // hashtable offset
        );
        // offsets
        foreach ($jsst_offsets as $jsst_offset)
            $jsst_mo .= pack('i', $jsst_offset);
        // ids
        $jsst_mo .= $jsst_ids;
        // strings
        $jsst_mo .= $jsst_strings;
        file_put_contents($jsst_out, $jsst_mo);
    }

    function stripslashesFull($jsst_input){// testing this function/.
        if (is_array($jsst_input)) {
            $jsst_input = array_map(array($this,'stripslashesFull'), $jsst_input);
        } elseif (is_object($jsst_input)) {
            $jsst_vars = get_object_vars($jsst_input);
            foreach ($jsst_vars as $jsst_k=>$jsst_v) {
                $jsst_input->{$jsst_k} = $this->stripslashesFull($jsst_v);
            }
        } else {
            $jsst_input = jssupportticketphplib::JSST_stripslashes($jsst_input);
        }
        return $jsst_input;
    }

    function getUserNameById($jsst_id){
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT user_nicename AS name FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` WHERE id = ".esc_sql($jsst_id);
        $jsst_username = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_username;
    }

    function getusersearchajax() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-usersearch-ajax') ) {
            die( 'Security check Failed' );
        }
        $jsst_username = JSSTrequest::getVar('username');
        $jsst_name = JSSTrequest::getVar('name');
        $jsst_emailaddress = JSSTrequest::getVar('emailaddress');
        $jsst_canloadresult = false;
        $jsst_query = "SELECT DISTINCT user.id AS userid, user.name AS username, user.user_email AS useremail, user.display_name AS userdisplayname
                    FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user ";
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        $jsst_query .= " WHERE NOT EXISTS( SELECT staff.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff WHERE user.id = staff.uid) ";
                    }else{
                        $jsst_query .= " WHERE 1 = 1 "; // to handle filter cases
                    }
        if (jssupportticketphplib::JSST_strlen($jsst_name) > 0) {
            $jsst_query .= " AND user.display_name LIKE '%".esc_sql($jsst_name)."%'";
            $jsst_canloadresult = true;
        }
        if (jssupportticketphplib::JSST_strlen($jsst_emailaddress) > 0) {
            $jsst_query .= " AND user.user_email LIKE '%".esc_sql($jsst_emailaddress)."%'";
            $jsst_canloadresult = true;
        }
        if (jssupportticketphplib::JSST_strlen($jsst_username) > 0) {
            $jsst_query .= " AND user.name LIKE '%".esc_sql($jsst_username)."%'";
            $jsst_canloadresult = true;
        }
        if($jsst_canloadresult){
            $jsst_users = jssupportticket::$_db->get_results($jsst_query);
            if(!empty($jsst_users)){
                $jsst_result ='
                <div class="js-ticket-table-wrp">
                    <div class="js-ticket-table-header">
                        <div class="js-ticket-table-header-col js-tkt-tbl-uid">'. esc_html(__('User ID', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-unm">'. esc_html(__('User Name', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-eml">'. esc_html(__('Email Address', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-nam">'. esc_html(__('Name', 'js-support-ticket')).'</div>
                    </div>
                    <div class="js-ticket-table-body">';
                        foreach($jsst_users AS $jsst_user){
                            $jsst_result .='
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-table-body-col js-tkt-tbl-uid">
                                    <span class="js-ticket-display-block">'. esc_html(__('User ID','js-support-ticket')).'</span>'.$jsst_user->userid.'
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-unm">
                                    <span class="js-ticket-display-block">'. esc_html(__('User Name','js-support-ticket')).':</span>
                                    '.esc_html($jsst_user->username).'
                                    </a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-eml">
                                    <span class="js-ticket-display-block">'. esc_html(__('Email','js-support-ticket')).':</span>
                                    <span class="js-ticket-title"><a href="#" class="js-userpopup-link" data-id="'.esc_attr($jsst_user->userid).'" data-email="'.esc_attr($jsst_user->useremail).'" data-username="'.esc_attr($jsst_user->username).'" data-name="'.esc_attr($jsst_user->userdisplayname).'">
                                        '. esc_html($jsst_user->useremail) .'
                                        </a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-nam">
                                    <span class="js-ticket-display-block">'. esc_html(__('Name','js-support-ticket')).':</span>
                                    '.esc_attr($jsst_user->userdisplayname).'
                                </div>
                            </div>';
                        }
                $jsst_result .='</div>';
            }else{
                $jsst_result= JSSTlayout::getNoRecordFound();
            }
        }else{ // reset button
            //$jsst_result ='<div class="js-staff-searc-desc">'. esc_html(__('Use search feature to select the user','js-support-ticket')).'</div>';
            $jsst_result = $this->getuserlistajax(0);
        }

        return $jsst_result;
    }



    function getuserlistajax($jsst_ajaxCall = 1){
        if ($jsst_ajaxCall == 1) {
            $jsst_nonce = JSSTrequest::getVar('_wpnonce');
            if (! wp_verify_nonce( $jsst_nonce, 'get-user-list-ajax') ) {
                die( 'Security check Failed' );
            }
        }
        $jsst_userlimit = JSSTrequest::getVar('userlimit',null,0);
        $jsst_maxrecorded = 4;
        $jsst_query = "SELECT DISTINCT COUNT(user.id)
                    FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user 
					WHERE user.status = 1 ";
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        $jsst_query .= " AND NOT EXISTS( SELECT staff.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff WHERE user.id = staff.uid) ";
                    }

        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        $jsst_limit = $jsst_userlimit * $jsst_maxrecorded;
        if($jsst_limit >= $jsst_total){
            $jsst_limit = 0;
        }
        $jsst_query = "SELECT DISTINCT user.id AS userid, user.name AS username, user.user_email AS useremail,
                    user.display_name AS userdisplayname
                    FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user 
					WHERE user.status = 1";
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        $jsst_query .= " AND NOT EXISTS( SELECT staff.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff WHERE user.id = staff.uid) ";
                    }
                    $jsst_query .= " LIMIT $jsst_limit, $jsst_maxrecorded";
        $jsst_users = jssupportticket::$_db->get_results($jsst_query);
        $jsst_html = $this->makeUserList($jsst_users,$jsst_total,$jsst_maxrecorded,$jsst_userlimit);
        return $jsst_html;

    }


    function makeUserList($jsst_users,$jsst_total,$jsst_maxrecorded,$jsst_userlimit){
        $jsst_html = '';
        if(!empty($jsst_users)){
            if(is_array($jsst_users)){
                $jsst_html ='
                <div class="js-ticket-table-wrp">
                    <div class="js-ticket-table-header">
                        <div class="js-ticket-table-header-col js-tkt-tbl-uid">'. esc_html(__('User ID', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-unm">'. esc_html(__('User Name', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-eml">'. esc_html(__('Email Address', 'js-support-ticket')).'</div>
                        <div class="js-ticket-table-header-col js-tkt-tbl-nam">'. esc_html(__('Name', 'js-support-ticket')).'</div>
                    </div>
                    <div class="js-ticket-table-body">';
                        foreach($jsst_users AS $jsst_user){
                            $jsst_html .='
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-table-body-col js-tkt-tbl-uid">
                                    <span class="js-ticket-display-block">'. esc_html(__('User ID','js-support-ticket')).'</span>'.esc_html($jsst_user->userid).'
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-unm">
                                    <span class="js-ticket-display-block">'. esc_html(__('User Name','js-support-ticket')).':</span>
                                    '.esc_html($jsst_user->username).'
                                    </a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-eml">
                                    <span class="js-ticket-display-block">'. esc_html(__('Email','js-support-ticket')).':</span>
                                    <span class="js-ticket-title"><a href="#" class="js-userpopup-link" data-id="'.esc_attr($jsst_user->userid).'" data-email="'.esc_attr($jsst_user->useremail).'" data-username="'.esc_attr($jsst_user->username).'" data-name="'.esc_attr($jsst_user->userdisplayname).'">
                                    '.esc_html($jsst_user->useremail).'
                                    </a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-tkt-tbl-nam">
                                    <span class="js-ticket-display-block">'. esc_html(__('Name','js-support-ticket')).':</span>
                                    '.esc_html($jsst_user->userdisplayname).'
                                </div>
                            </div>';
                        }
                $jsst_html .='</div>';
            }
            $jsst_num_of_pages = ceil($jsst_total / $jsst_maxrecorded);
            $jsst_num_of_pages = ($jsst_num_of_pages > 0) ? ceil($jsst_num_of_pages) : floor($jsst_num_of_pages);
            if($jsst_num_of_pages > 0){
                $jsst_page_html = '';
                $jsst_prev = $jsst_userlimit;
                if($jsst_prev > 0){
                    $jsst_page_html .= '<a class="jsst_userlink" href="#" onclick="updateuserlist('.esc_js(($jsst_prev - 1)).');">'. esc_html(__('Previous','js-support-ticket')).'</a>';
                }
                for($jsst_i = 0; $jsst_i < $jsst_num_of_pages; $jsst_i++){
                    if($jsst_i == $jsst_userlimit)
                        $jsst_page_html .= '<span class="jsst_userlink selected" >'.($jsst_i + 1).'</span>';
                    else
                        $jsst_page_html .= '<a class="jsst_userlink" href="#" onclick="updateuserlist('.esc_js($jsst_i).');">'.esc_js(($jsst_i + 1)).'</a>';

                }
                $jsst_next = $jsst_userlimit + 1;
                if($jsst_next < $jsst_num_of_pages){
                    $jsst_page_html .= '<a class="jsst_userlink" href="#" onclick="updateuserlist('.esc_js($jsst_next).');">'. esc_html(__('Next','js-support-ticket')).'</a>';
                }
                if($jsst_page_html != ''){
                    $jsst_html .= '<div class="jsst_userpages">'.wp_kses($jsst_page_html, JSST_ALLOWED_TAGS).'</div>';
                }
            }

        }else{
            $jsst_html = JSSTlayout::getNoRecordFound();
        }
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
        die();
        return $jsst_html;
    }

    function storeOrderingFromPage($jsst_data) {//
        if (empty($jsst_data)) {
            return false;
        }
        $jsst_sorted_array = array();
        jssupportticketphplib::JSST_parse_str($jsst_data['fields_ordering_new'],$jsst_sorted_array);
        $jsst_sorted_array = reset($jsst_sorted_array);
        if(!empty($jsst_sorted_array)){

            if($jsst_data['ordering_for'] == 'department'){
                $jsst_row = JSSTincluder::getJSTable('departments');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'priority'){
                $jsst_row = JSSTincluder::getJSTable('priorities');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'status'){
                $jsst_row = JSSTincluder::getJSTable('statuses');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'product'){
                $jsst_row = JSSTincluder::getJSTable('products');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'fieldsordering'){
                $jsst_row = JSSTincluder::getJSTable('fieldsordering');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'announcement'){
                $jsst_row = JSSTincluder::getJSTable('announcement');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'faq'){
                $jsst_row = JSSTincluder::getJSTable('faq');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'helptopic'){
                $jsst_row = JSSTincluder::getJSTable('helptopic');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'article'){
                $jsst_row = JSSTincluder::getJSTable('articles');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'download'){
                $jsst_row = JSSTincluder::getJSTable('download');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'fieldordering'){
                $jsst_row = JSSTincluder::getJSTable('fieldsordering');
                $jsst_ordering_coloumn = 'ordering';
            }elseif($jsst_data['ordering_for'] == 'multiform'){
                $jsst_row = JSSTincluder::getJSTable('multiform');
                $jsst_ordering_coloumn = 'ordering';
            }

            $jsst_page_multiplier = 1;
            if($jsst_data['pagenum_for_ordering'] > 1){
                $jsst_page_multiplier = ($jsst_data['pagenum_for_ordering'] - 1) * jssupportticket::$_config['pagination_default_page_size'] + 1;
            }
            for ($jsst_i=0; $jsst_i < count($jsst_sorted_array) ; $jsst_i++) {
                $jsst_row->update(array('id' => $jsst_sorted_array[$jsst_i], $jsst_ordering_coloumn => $jsst_page_multiplier + $jsst_i));
            }
        }
        JSSTmessage::setMessage(esc_html(__('Ordering updated', 'js-support-ticket')), 'updated');
        return ;
    }

    function updateDate($jsst_addon_name,$jsst_plugin_version){
        return JSSTincluder::getJSModel('premiumplugin')->verfifyAddonActivation($jsst_addon_name);
    }

    function getAddonSqlForActivation($jsst_addon_name,$jsst_addon_version){
        return JSSTincluder::getJSModel('premiumplugin')->verifyAddonSqlFile($jsst_addon_name,$jsst_addon_version);
    }

    function installPluginFromAjax(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'install-plugin-ajax') ) {
             die( 'Security check Failed' ); 
        }
        if(current_user_can( 'install_plugins' )){
            $jsst_pluginslug = JSSTrequest::getVar('pluginslug');
            if(file_exists(plugins_url($jsst_pluginslug . '/' . $jsst_pluginslug . '.php'))){
                return false;
            }
            if($jsst_pluginslug != ""){
                do_action('jssupportticket_load_wp_plugin_file');
                do_action('jssupportticket_load_wp_upgrader');
                do_action('jssupportticket_load_wp_ajax_upgrader_skin');
                do_action('jssupportticket_load_wp_plugin_upgrader');

                // Get Plugin Info
                $jsst_api = plugins_api( 'plugin_information',
                    array(
                        'slug' => $jsst_pluginslug,
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
                $jsst_skin     = new WP_Ajax_Upgrader_Skin();
                $jsst_upgrader = new Plugin_Upgrader( $jsst_skin );
                $jsst_upgrader->install( $jsst_api->download_link );
                if(file_exists(plugins_url($jsst_pluginslug . '/' . $jsst_pluginslug . '.php'))){
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
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'activate-plugin-ajax') ) {
             die( 'Security check Failed' ); 
        }
        if(current_user_can( 'activate_plugins')){
            $jsst_pluginslug = JSSTrequest::getVar('pluginslug');
            do_action('jssupportticket_load_wp_plugin_file');
            if(file_exists(plugins_url($jsst_pluginslug . '/' . $jsst_pluginslug . '.php'))){
                $jsst_isactivate = is_plugin_active($jsst_pluginslug.'/'.$jsst_pluginslug.'.php');
                if($jsst_isactivate){
                    return false;
                }
                if($jsst_pluginslug != ""){
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
                    activate_plugin( $jsst_pluginslug.'/'.$jsst_pluginslug.'.php' );
                    // $jsst_isactivate = $this->run_activate_plugin( $jsst_pluginslug.'/'.$jsst_pluginslug.'.php' );
                    $jsst_isactivate = is_plugin_active($jsst_pluginslug.'/'.$jsst_pluginslug.'.php');
                    if($jsst_isactivate){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function getJSSTDateFormat(){
        $jsst_dateformat = jssupportticket::$_config['date_format'];
        if ($jsst_dateformat == 'm/d/Y' || $jsst_dateformat == 'd/m/y' || $jsst_dateformat == 'm/d/y' || $jsst_dateformat == 'd/m/Y') {
            $jsst_dash = '/';
        } else {
            $jsst_dash = '-';
        }
        $jsst_firstdash = jssupportticketphplib::JSST_strpos($jsst_dateformat, $jsst_dash, 0);
        $jsst_firstvalue = jssupportticketphplib::JSST_substr($jsst_dateformat, 0, $jsst_firstdash);
        $jsst_firstdash = $jsst_firstdash + 1;
        $jsst_seconddash = jssupportticketphplib::JSST_strpos($jsst_dateformat, $jsst_dash, $jsst_firstdash);
        $jsst_secondvalue = jssupportticketphplib::JSST_substr($jsst_dateformat, $jsst_firstdash, $jsst_seconddash - $jsst_firstdash);
        $jsst_seconddash = $jsst_seconddash + 1;
        $jsst_thirdvalue = jssupportticketphplib::JSST_substr($jsst_dateformat, $jsst_seconddash, jssupportticketphplib::JSST_strlen($jsst_dateformat) - $jsst_seconddash);
        $jsst_js_dateformat = '%' . $jsst_firstvalue . $jsst_dash . '%' . $jsst_secondvalue . $jsst_dash . '%' . $jsst_thirdvalue;
        $jsst_js_scriptdateformat = $jsst_firstvalue . $jsst_dash . $jsst_secondvalue . $jsst_dash . $jsst_thirdvalue;
        $jsst_js_scriptdateformat = jssupportticketphplib::JSST_str_replace('Y', 'yy', $jsst_js_scriptdateformat);
        $jsst_js_scriptdateformat = jssupportticketphplib::JSST_str_replace('m', 'mm', $jsst_js_scriptdateformat);
        $jsst_js_scriptdateformat = jssupportticketphplib::JSST_str_replace('d', 'dd', $jsst_js_scriptdateformat);
        return $jsst_js_scriptdateformat;
    }

    function getAddonTransationKey($jsst_option_name){
        $jsst_query = "SELECT `option_value` FROM " . jssupportticket::$_wpprefixforuser . "options WHERE option_name = '".esc_sql($jsst_option_name)."'";
        $jsst_transactionKey = jssupportticket::$_db->get_var($jsst_query);
		if($jsst_transactionKey == ""){
			$jsst_transactionKey = get_option($jsst_option_name);
		}
        return $jsst_transactionKey;
    }

    function getInstalledTranslationKey(){
        do_action('jssupportticket_load_wp_translation_install');
        $jsst_activated_lang = get_option('WPLANG','en_US');
        $jsst_install_lang_name = wp_get_available_translations();
        if(isset($jsst_install_lang_name[$jsst_activated_lang])){
            $jsst_lang_name = $this->makeLanguageCode($jsst_activated_lang);
            $jsst_install_lang_name = $jsst_install_lang_name[$jsst_activated_lang]['english_name'];
            if($jsst_activated_lang == "" || $jsst_activated_lang == 'en_US'){
                update_option( 'jshd_tran_lang_exists', false);
                return false;
            }else{
                // $jsst_path = JSST_PLUGIN_PATH.'languages';
                $jsst_path = WP_LANG_DIR . '/plugins/';
                $jsst_final_path = $jsst_path.'/js-support-ticket-'.$jsst_activated_lang.'.po';
                if(file_exists($jsst_final_path)){
                    update_option( 'jshd_tran_lang_exists', false);
                    return false;
                }
                if(get_option( 'jshd_tran_lang_exists', '') != ''){
                    $jsst_session = json_decode(get_option( 'jshd_tran_lang_exists', ''));
                    if($jsst_session->code == $jsst_activated_lang){
                        return get_option( 'jshd_tran_lang_exists');
                    }
                }
                $jsst_url = "https://jshelpdesk.com/translations/api/1.0/index.php";
                $jsst_post_data['product'] ='js-support-ticket-wp';
                $jsst_post_data['domain'] = get_site_url();
                $jsst_post_data['producttype'] = jssupportticket::$_config['producttype'];
                $jsst_post_data['productcode'] = 'jsticket';
                $jsst_post_data['productversion'] = jssupportticket::$_config['productversion'];
                $jsst_post_data['JVERSION'] = get_bloginfo('version');
                $jsst_post_data['translationcode'] = $jsst_activated_lang;
                $jsst_post_data['method'] = 'getTranslationFile';

                $jsst_response = wp_remote_post( $jsst_url, array('body' => $jsst_post_data,'timeout'=>7,'sslverify'=>false));
                if( !is_wp_error($jsst_response) && $jsst_response['response']['code'] == 200 && isset($jsst_response['body']) ){
                    $jsst_result = $jsst_response['body'];
                }else{
                    $jsst_result = false;
                    if(!is_wp_error($jsst_response)){
                       $jsst_error = $jsst_response['response']['message'];
                    }else{
                        $jsst_error = $jsst_response->get_error_message();
                    }
                }
                if($jsst_result){
                    $jsst_array = json_decode($jsst_result, true);
                }else{
                    $jsst_array = array();
                }
                if(is_array($jsst_array) && isset($jsst_array['file'])){
                    $jsst_jshd_tran_lang_exists = array("code" => $jsst_activated_lang, "lang_fullname" => $jsst_install_lang_name , "name" => $jsst_lang_name);
                    $jsst_jshd_tran_lang_exists = wp_json_encode($jsst_jshd_tran_lang_exists);
                    update_option( 'jshd_tran_lang_exists', $jsst_jshd_tran_lang_exists);
                    return $jsst_jshd_tran_lang_exists;
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
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'hide-popup-from-admin') ) {
            die( 'Security check Failed' );
        }
        update_option( 'jsst_hide_jsstadmin_top_banner', 1 );
    }
    function getWPUidById($jsst_id){
        if(!is_numeric($jsst_id)){
            return false;
        }

        $jsst_query = "SELECT user.wpuid
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user 
                    WHERE id = ".esc_sql($jsst_id);
        $jsst_wpuid = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_wpuid;
    }

    function reviewBoxAction(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'review-box-action') ) {
            die( 'Security check Failed' );
        }
        $jsst_days = JSSTrequest::getVar('days');
        if($jsst_days == -1) {
            add_option("jssupportticket_hide_review_box", "1");
        } else {
			//jssupportticketphplib::JSST_strtotime not work porperly
            //$jsst_date = gmdate("Y-m-d", jssupportticketphplib::JSST_strtotime("+".$jsst_days." days"));
			$jsst_date = gmdate("Y-m-d", strtotime("+".$jsst_days." days"));
            update_option("jssupportticket_show_review_box_after", $jsst_date);
        }
        return true;
    }

    function getShortCodeData(){
        if( in_array('multiform', jssupportticket::$_active_addons) ){
            $jsst_query = "SELECT multiform.id, multiform.title, department.departmentname FROM `" . jssupportticket::$_db->prefix . "js_ticket_multiform` AS multiform
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON multiform.departmentid = department.id WHERE multiform.status = 1 ORDER BY multiform.id ASC";
            jssupportticket::$jsst_data[0]['multiforms'] = jssupportticket::$_db->get_results($jsst_query);
        }
        return true;
    }

    function checkIfMainCssFileIsEnqued(){
        global $wp_styles;
        if (!in_array('jssupportticket-main-css',$wp_styles->queue)) {
            wp_enqueue_style('jssupportticket-main-css', JSST_PLUGIN_URL . 'includes/css/style.css', array(), jssupportticket::$_config['productversion']);
            // responsive style sheets
            wp_enqueue_style('jssupportticket-tablet-css', JSST_PLUGIN_URL . 'includes/css/style_tablet.css', array(), jssupportticket::$_config['productversion'], '(min-width: 668px) and (max-width: 782px)');
            wp_enqueue_style('jssupportticket-mobile-css', JSST_PLUGIN_URL . 'includes/css/style_mobile.css', array(), jssupportticket::$_config['productversion'], '(min-width: 481px) and (max-width: 667px)');
            wp_enqueue_style('jssupportticket-oldmobile-css', JSST_PLUGIN_URL . 'includes/css/style_oldmobile.css', array(), jssupportticket::$_config['productversion'], '(max-width: 480px)');
            //wp_enqueue_style('jssupportticket-main-css');
            if(is_rtl()){
                //wp_register_style('jssupportticket-main-css-rtl', JSST_PLUGIN_URL . 'includes/css/stylertl.css');
                wp_enqueue_style('jssupportticket-main-css-rtl', JSST_PLUGIN_URL . 'includes/css/stylertl.css', array(), jssupportticket::$_config['productversion']);
                //wp_enqueue_style('jssupportticket-main-css-rtl');
            }
            $jsst_color1 = require_once(JSST_PLUGIN_PATH . 'includes/css/style.php');
            // wp_enqueue_style('jssupportticket-color-css', JSST_PLUGIN_URL . 'includes/css/color.css');
        }
        return true;
    }

    function updateColorFile(){
        // require_once(JSST_PLUGIN_PATH . 'includes/css/style.php');
    }

    function getSiteUrl(){
        $jsst_site_url = site_url();
        $jsst_site_url = jssupportticketphplib::JSST_str_replace("https://","",$jsst_site_url);
        $jsst_site_url = jssupportticketphplib::JSST_str_replace("http://","",$jsst_site_url);
        return $jsst_site_url;
    }

    function getNetworkSiteUrl(){
        $jsst_network_site_url = network_site_url();
        $jsst_network_site_url = jssupportticketphplib::JSST_str_replace("https://","",$jsst_network_site_url);
        $jsst_network_site_url = jssupportticketphplib::JSST_str_replace("http://","",$jsst_network_site_url);
        return $jsst_network_site_url;
    }

    function addMissingUsers($jsst_show_message = 1){
        $jsst_missingUser = 0;
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
        foreach ($jsst_missingUsers as $jsst_missingUser) {
            $jsst_query = "SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` WHERE wpuid = " . esc_sql($jsst_missingUser);
            $jsst_total = jssupportticket::$_db->get_var($jsst_query);
            if ($jsst_total == 0) {
                $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "users` WHERE id = " . esc_sql($jsst_missingUser);
                $jsst_user = jssupportticket::$_db->get_row($jsst_query);                
                if (isset($jsst_user)) {
                    $jsst_row = JSSTincluder::getJSTable('users');
                    $jsst_data['wpuid'] = $jsst_user->ID;
                    $jsst_data['name'] = $jsst_user->display_name;
                    $jsst_data['display_name'] = $jsst_user->display_name;
                    $jsst_data['user_nicename'] = $jsst_user->user_nicename;
                    $jsst_data['user_email'] = $jsst_user->user_email;
                    $jsst_data['issocial'] = 0;
                    $jsst_data['socialid'] = null;
                    $jsst_data['status'] = 1;
                    $jsst_data['created'] = date_i18n('Y-m-d H:i:s');
                    $jsst_row->bind($jsst_data);
                    $jsst_row->store();
                    $jsst_missingUser = 1;
                }
            }
        }
        if ($jsst_show_message == 1) {
            if ($jsst_missingUser == 1) {
                JSSTmessage::setMessage(esc_html(__('Missing user(s) added successfully!', 'js-support-ticket')), 'updated');
            } else {
                JSSTmessage::setMessage(esc_html(__('No missing user found!', 'js-support-ticket')), 'error');
            }
        }
        return;
    }

    function jsstremovetags($jsst_message){
        if(jssupportticketphplib::JSST_strpos($jsst_message, '<script>') !== false || jssupportticketphplib::JSST_strpos($jsst_message, '</script>') !== false){ // check and remove script tag from the message
            $jsst_message = jssupportticketphplib::JSST_str_replace('<script>','&lt;script&gt;', $jsst_message);
            $jsst_message = jssupportticketphplib::JSST_str_replace('</script>','&lt;/script&gt;', $jsst_message);
        }
        return $jsst_message;
    }

    function getSanitizedEditorData($jsst_data){
       $jsst_data = wp_filter_post_kses(wpautop($jsst_data));
       return $jsst_data;
    }

    function getEncriptedSiteLink(){
        $jsst_siteLink = get_option('jsst_encripted_site_link');
        if ($jsst_siteLink == '') {
            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
            $jsst_encoder = new JSSTEncoder();
            $jsst_siteLink = $jsst_encoder->encrypt(get_site_url());
            update_option('jsst_encripted_site_link', $jsst_siteLink);
        }
       return $jsst_siteLink;
    }

    function checkJSSTAddoneInfo($jsst_name){
        $jsst_slug = $jsst_name.'/'.$jsst_name.'.php';
        if(file_exists(WP_PLUGIN_DIR . '/'.$jsst_slug) && is_plugin_active($jsst_slug)){
            $jsst_status = __("Activated","js-support-ticket");
            $jsst_action = __("Deactivate","js-support-ticket");
            $jsst_actionClass = 'jsst-admin-adons-status-Deactive';
            $jsst_url = "plugins.php?s=".$jsst_name."&plugin_status=active";
            $jsst_disabled = "disabled";
            $jsst_class = "js-btn-activated";
            $jsst_availability = "-1";
            $jsst_version = "";
        }else if(file_exists(WP_PLUGIN_DIR . '/'.$jsst_slug) && !is_plugin_active($jsst_slug)){
            $jsst_status = __("Deactivated","js-support-ticket");
            $jsst_action = __("Activate","js-support-ticket");
            $jsst_actionClass = 'jsst-admin-adons-status-Active';
            $jsst_url = "plugins.php?s=".$jsst_name."&plugin_status=inactive";
            $jsst_disabled = "";
            $jsst_class = "js-btn-green js-btn-active-now";
            $jsst_availability = "1";
            $jsst_version = "";
        }else if(!file_exists(WP_PLUGIN_DIR . '/'.$jsst_slug)){
            $jsst_status = __("Not Installed","js-support-ticket");
            $jsst_action = __("Install Now","js-support-ticket");
            $jsst_actionClass = 'jsst-admin-adons-status-Install';
            $jsst_url = admin_url("admin.php?page=premiumplugin&mjslay=step1");
            $jsst_disabled = "";
            $jsst_class = "js-btn-install-now";
            $jsst_availability = "0";
            $jsst_version = "---";
        }
        return array("status" => $jsst_status, "action" => $jsst_action, "url" => $jsst_url, "disabled" => $jsst_disabled, "class" => $jsst_class, "availability" => $jsst_availability, "actionClass" => $jsst_actionClass, "version" => $jsst_version);
    }

    function JSSTdownloadandinstalladdonfromAjax(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'download-and-install-addon') ) {
            die( 'Security check Failed' );
        }

        $jsst_key = JSSTrequest::getVar('dataFor');
        $jsst_installedversion = JSSTrequest::getVar('currentVersion');
        $jsst_newversion = JSSTrequest::getVar('cdnVersion');
        $jsst_addon_json_array = array();

        if($jsst_key != ''){
            $jsst_addon_json_array[] = str_replace('js-support-ticket-', '', $jsst_key);
            $jsst_plugin_slug = str_replace('js-support-ticket-', '', $jsst_key);
        }
        $jsst_token = get_option('transaction_key_for_'.$jsst_key);
        $jsst_result = array();
        $jsst_result['error'] = false;
        if($jsst_token == ''){
            $jsst_result['error'] = esc_html(__('Addon Installation Failed','js-support-ticket'));
            $jsst_result = wp_json_encode($jsst_result);
            return $jsst_result;
        }
        $jsst_site_url = site_url();
        if($jsst_site_url != ''){
            $jsst_site_url = str_replace("https://","",$jsst_site_url);
            $jsst_site_url = str_replace("http://","",$jsst_site_url);
        }
        $jsst_url = 'https://jshelpdesk.com/setup/index.php?token='.$jsst_token.'&productcode='. wp_json_encode($jsst_addon_json_array).'&domain='.$jsst_site_url;
        // verify token
        $jsst_verifytransactionkey = $this->verifytransactionkey($jsst_token, $jsst_url);
        if($jsst_verifytransactionkey['status'] == 0){
            $jsst_result['error'] = $jsst_verifytransactionkey['message'];
            $jsst_result = wp_json_encode($jsst_result);
            return $jsst_result;
        }
        $jsst_install_count = 0;

        $jsst_installed = $this->install_plugin($jsst_url);
        if ( !is_wp_error( $jsst_installed ) && $jsst_installed ) {
            // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.
            if(strstr($jsst_key, 'js-support-ticket-')){
                update_option('transaction_key_for_'.$jsst_key,$jsst_token);
            }

            if(strstr($jsst_key, 'js-support-ticket-')){
                $jsst_activate = activate_plugin( $jsst_key.'/'.$jsst_key.'.php' );
                $jsst_install_count++;
            }

            // run update sql
            if ($jsst_installedversion != $jsst_newversion) {
                $jsst_optionname = 'jsst-addon-'. $jsst_plugin_slug .'s-version';
                update_option($jsst_optionname, $jsst_newversion);
                $jsst_plugin_path = WP_CONTENT_DIR;
                $jsst_plugin_path = $jsst_plugin_path.'/plugins/'.$jsst_key.'/includes';
                if(is_dir($jsst_plugin_path . '/sql/') && is_readable($jsst_plugin_path . '/sql/')){
                    if($jsst_installedversion != ''){
                        $jsst_installedversion = str_replace('.','', $jsst_installedversion);
                    }
                    if($jsst_newversion != ''){
                        $jsst_newversion = str_replace('.','', $jsst_newversion);
                    }
                    JSSTincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromUpdateDir($jsst_installedversion,$jsst_newversion,$jsst_plugin_path . '/sql/');
                    $jsst_updatesdir = $jsst_plugin_path.'/sql/';
                    if(preg_match('/js-support-ticket-[a-zA-Z]+/', $jsst_updatesdir)){
                        $this->jsstRemoveAddonUpdatesFolder($jsst_updatesdir);
                    }
                }else{
                    JSSTincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromLive($jsst_installedversion,$jsst_newversion,$jsst_plugin_slug);
                }
            }

        }else{
            $jsst_result['error'] = esc_html(__('Addon Installation Failed','js-support-ticket'));
            $jsst_result = wp_json_encode($jsst_result);
            return $jsst_result;
        }

        $jsst_result['success'] = esc_html(__('Addon Installed Successfully','js-support-ticket'));
        $jsst_result = wp_json_encode($jsst_result);
        return $jsst_result;
    }

    function install_plugin( $jsst_plugin_zip ) {

        do_action('jssupportticket_load_wp_admin_file');
        WP_Filesystem();

        $jsst_tmpfile = download_url( $jsst_plugin_zip);

        if ( !is_wp_error( $jsst_tmpfile ) && $jsst_tmpfile ) {
            $jsst_plugin_path = WP_CONTENT_DIR;
            $jsst_plugin_path = $jsst_plugin_path.'/plugins/';
            $jsst_path = JSST_PLUGIN_PATH.'addon.zip';

            copy( $jsst_tmpfile, $jsst_path );

            $jsst_unzipfile = unzip_file( $jsst_path, $jsst_plugin_path);

            if ( file_exists( $jsst_path ) ) {
                wp_delete_file( $jsst_path ); // must unlink afterwards
            }
            if ( file_exists( $jsst_tmpfile ) ) {
                wp_delete_file( $jsst_tmpfile ); // must unlink afterwards
            }

            if ( is_wp_error( $jsst_unzipfile ) ) {
                $jsst_result['error'] = esc_html(__('Addon installation failed','js-support-ticket')).'.';
                $jsst_result['error'] .= " ".esc_html(jssupportticket::JSST_getVarValue($jsst_unzipfile->get_error_message()));
                $jsst_result = wp_json_encode($jsst_result);
                return $jsst_result;
            } else {
                return true;
            }
        }else{
            $jsst_error_string = $jsst_tmpfile->get_error_message();
            $jsst_result['error'] = esc_html(__('Addon Installation Failed, File download error','js-support-ticket')).'!'.$jsst_error_string;
            $jsst_result = wp_json_encode($jsst_result);
            return $jsst_result;
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
        $jsst_JS_SUPPORTTICKETUpdater  = new JS_SUPPORTTICKETUpdater();
        $jsst_cdnversiondata = $jsst_JS_SUPPORTTICKETUpdater->getPluginVersionDataFromCDN();

        $jsst_jssupportticket_addons = $this->getJSSTAddonsArray();

        $jsst_installed_plugins = get_plugins();
        $jsst_need_to_update = array();
        $jsst_site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        $jsst_status_prefix = 'key_status_for_js-support-ticket_';
        $jsst_final_addon_json_array = array();
        foreach ($jsst_jssupportticket_addons as $jsst_key1 => $jsst_value1) {
            $jsst_matched = 0;
            $jsst_version = "";
            foreach ($jsst_installed_plugins as $jsst_name => $jsst_value) {
                $jsst_install_plugin_name = jssupportticketphplib::JSST_str_replace(".php","",jssupportticketphplib::JSST_basename($jsst_name));
                if($jsst_key1 == $jsst_install_plugin_name){
                    $jsst_matched = 1;
                    $jsst_version = $jsst_value["Version"];
                    $jsst_install_plugin_matched_name = $jsst_install_plugin_name;
                }
            }
            if($jsst_matched == 1){ //installed
                $jsst_name = $jsst_key1;
                $jsst_title = $jsst_value1['title'];
                $jsst_cdnavailableversion = "";
                foreach ($jsst_cdnversiondata as $jsst_cdnname => $jsst_cdnversion) {
                    $jsst_addon_json_array = array();
                    $jsst_addon_json_final_array = array();
                    $jsst_install_plugin_name_simple = jssupportticketphplib::JSST_str_replace("-", "", $jsst_install_plugin_matched_name);
                    if($jsst_cdnname == jssupportticketphplib::JSST_str_replace("-", "", $jsst_install_plugin_matched_name)){
                        if($jsst_cdnversion > $jsst_version){ // new version available
                            $jsst_status = 'update_available';
                            $jsst_cdnavailableversion = $jsst_cdnversion;
                            $jsst_plugin_slug = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_name);
                            // get key status from local
                            $jsst_token = get_option('transaction_key_for_'.esc_attr($jsst_name));
                            $jsst_key_local_status = get_option($jsst_status_prefix . $jsst_token);
                            if($jsst_key_local_status == 1){
                                $jsst_addon_json_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_name);
                                $jsst_url = 'https://jshelpdesk.com/setup/index.php?token='.esc_attr($jsst_token).'&productcode='. wp_json_encode($jsst_addon_json_array).'&domain='.$jsst_site_url;
                                // verify token
                                $jsst_verifytransactionkey = $this->verifytransactionkey($jsst_token, $jsst_url);
                                
                                if($jsst_verifytransactionkey['status'] == 1){
                                    $jsst_final_addon_json_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_name);
                                    $jsst_addon_json_final_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_name);
                                    $jsst_need_to_update[] = array("name" => $jsst_name, "current_version" => $jsst_version, "available_version" => $jsst_cdnavailableversion, "plugin_slug" => $jsst_plugin_slug );
                                    $jsst_final_url = 'https://jshelpdesk.com/setup/index.php?token='.esc_attr($jsst_token).'&productcode='. wp_json_encode($jsst_final_addon_json_array).'&domain='.$jsst_site_url;
                                }
                            }
                        }
                    }    
                }
            }
        }
        $jsst_token = "";
        if(!empty($jsst_need_to_update)){
            $jsst_installed = $this->install_plugin($jsst_final_url);
            if ( !is_wp_error( $jsst_installed ) && $jsst_installed ) {
                // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.

                // run update sql
                foreach($jsst_need_to_update AS $jsst_update){
                    $jsst_installedversion = $jsst_update["current_version"];
                    $jsst_newversion = $jsst_update["available_version"];
                    $jsst_plugin_slug = $jsst_update["plugin_slug"];
                    $jsst_key = $jsst_update["name"];
                    if ($jsst_installedversion != $jsst_newversion) {
                        $jsst_optionname = 'jsst-addon-'. $jsst_plugin_slug .'s-version';
                        update_option($jsst_optionname, $jsst_newversion);
                        $jsst_plugin_path = WP_CONTENT_DIR;
                        $jsst_plugin_path = $jsst_plugin_path.'/plugins/'.$jsst_key.'/includes';
                        if(is_dir($jsst_plugin_path . '/sql/') && is_readable($jsst_plugin_path . '/sql/')){
                            if($jsst_installedversion != ''){
                                $jsst_installedversion = str_replace('.','', $jsst_installedversion);
                            }
                            if($jsst_newversion != ''){
                                $jsst_newversion = str_replace('.','', $jsst_newversion);
                            }
                            JSSTincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromUpdateDir($jsst_installedversion,$jsst_newversion,$jsst_plugin_path . '/sql/');
                            $jsst_updatesdir = $jsst_plugin_path.'/sql/';
                            if(preg_match('/js-support-ticket-[a-zA-Z]+/', $jsst_updatesdir)){
                                $this->jsstRemoveAddonUpdatesFolder($jsst_updatesdir);
                            }
                        }else{
                            JSSTincluder::getJSModel('premiumplugin')->getAddonUpdateSqlFromLive($jsst_installedversion,$jsst_newversion,$jsst_plugin_slug);
                        }
                    }
                }

            }else{
                return;
            }
        }
        return;
    }

    function verifytransactionkey($jsst_transactionkey, $jsst_url){
        $jsst_message = 1;
        if($jsst_transactionkey != ''){
            $jsst_response = wp_remote_post( $jsst_url );
            if( !is_wp_error($jsst_response) && $jsst_response['response']['code'] == 200 && isset($jsst_response['body']) ){
                $jsst_result = $jsst_response['body'];
                $jsst_result = json_decode($jsst_result,true);
                if(is_array($jsst_result) && isset($jsst_result[0]) && $jsst_result[0] == 0){
                    $jsst_result['status'] = 0;
                } else{
                    $jsst_result['status'] = 1;
                }
            }else{
                $jsst_result = false;
                if(!is_wp_error($jsst_response)){
                   $jsst_error = $jsst_response['response']['message'];
                }else{
                    $jsst_error = $jsst_response->get_error_message();
                }
            }
            if(is_array($jsst_result) && isset($jsst_result['status']) && $jsst_result['status'] == 1 ){ // means everthing ok
                $jsst_message = 1;
            }else{
                if(isset($jsst_result[0]) && $jsst_result[0] == 0){
                    $jsst_error = $jsst_result[1];
                }elseif(isset($jsst_result['error']) && $jsst_result['error'] != ''){
                    $jsst_error = $jsst_result['error'];
                }
                $jsst_message = 0;
            }
        }else{
            $jsst_message = 0;
            $jsst_error = esc_html(__('Please insert activation key to proceed','js-support-ticket')).'!';
        }
        $jsst_array['data'] = array();
        if ($jsst_message == 0) {
            $jsst_array['status'] = 0;
            $jsst_array['message'] = $jsst_error;
        } else {
            $jsst_array['status'] = 1;
            $jsst_array['message'] = 'success';
        }
        return $jsst_array;
    }

    function jsstRemoveAddonUpdatesFolder($jsst_dir) {
        if (empty($jsst_dir)) return;

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
        if ($jsst_wp_filesystem->exists($jsst_dir)) {
            /**
             * The second parameter 'true' makes the delete recursive.
             * This single line replaces glob(), is_dir(), is_file(), and rmdir().
             */
            $jsst_wp_filesystem->delete($jsst_dir, true);
        }
    }

    function generateIndexFile($jsst_file_directory) {
        global $wp_filesystem;

        // Initialize the WP_Filesystem
        if (!is_a($wp_filesystem, 'WP_Filesystem_Base')) {
            require_once ABSPATH . 'wp-admin/includes/file.php'; // Include WP Filesystem functions
            $jsst_creds = request_filesystem_credentials(site_url());

            if (!WP_Filesystem($jsst_creds)) {
                wp_die('Could not initialize the filesystem.');
            }
        }
        $jsst_wp_filesystem = $wp_filesystem;

        // Get the uploads directory path
        $jsst_uploads_dir = wp_upload_dir();
        $jsst_uploads_path = $jsst_uploads_dir['basedir'];

        // Normalize the paths to ensure consistency
        $jsst_file_directory = rtrim($jsst_file_directory, '/');
        $jsst_uploads_path = rtrim($jsst_uploads_path, '/');

        // Check if the given directory is within the uploads directory
        if (jssupportticketphplib::JSST_strpos($jsst_file_directory, $jsst_uploads_path) === 0) {
            // Start from the given directory and move up to the uploads directory
            $jsst_current_dir = $jsst_file_directory;
            while ($jsst_current_dir !== $jsst_uploads_path) {
                // Path to the index.php file in the current directory
                $jsst_index_file = $jsst_current_dir . '/index.html';

                // Create the index.php file if it does not exist
                if (!$jsst_wp_filesystem->exists($jsst_index_file)) {
                    $jsst_wp_filesystem->put_contents($jsst_index_file, '', FS_CHMOD_FILE); // FS_CHMOD_FILE ensures correct file permissions
                }

                // Move up to the parent directory
                $jsst_current_dir = dirname($jsst_current_dir);
            }

            // Finally, check and create the index.php file in the uploads directory
            $jsst_uploads_index_file = $jsst_uploads_path . '/index.html';
            if (!$jsst_wp_filesystem->exists($jsst_uploads_index_file)) {
                // $jsst_wp_filesystem->put_contents($jsst_uploads_index_file, '', FS_CHMOD_FILE);
            }
        }

        return;
    }

    function jsst_check_license_status() {
        // Get all distinct transaction keys
        $jsst_query = "
            SELECT DISTINCT option_value 
            FROM `" . jssupportticket::$_db->prefix . "options`
            WHERE option_name LIKE 'transaction_key_for_js-support-ticket%'
        ";
        $jsst_transaction_keys = jssupportticket::$_db->get_col($jsst_query);

        if (empty($jsst_transaction_keys)) return;

        $jsst_status_prefix = 'key_status_for_js-support-ticket_';
        $jsst_site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        $jsst_show_key_expiry_msg = 0;

        foreach ($jsst_transaction_keys as $jsst_key) {
            // Build query string for GET request
            $jsst_query_args = [
                'token'   => $jsst_key,
                'domain'  => $jsst_site_url,
                'request' => 'keyexpirycheck'
            ];

            $jsst_url = add_query_arg($jsst_query_args, 'https://jshelpdesk.com/setup/index.php');

            // Perform GET request
            $jsst_response = wp_remote_get($jsst_url, [ 'timeout' => 15 ]);

            if (is_wp_error($jsst_response)) {
                continue; // Skip on error
            }

            $jsst_body = wp_remote_retrieve_body($jsst_response);
            $jsst_data = json_decode($jsst_body, true);

            if (!is_array($jsst_data) || !isset($jsst_data['status'])) {
                continue; // Invalid response
            }

            // Save status
            update_option($jsst_status_prefix . $jsst_key, $jsst_data['status'], false);

            // Save expiry date if available
            if ($jsst_data['status'] == 1 && !empty($jsst_data['expirydate'])) {
                if (strtotime(current_time('mysql')) > strtotime($jsst_data['expirydate'])) {
                    $jsst_show_key_expiry_msg = 1;
                }
            } else {
                $jsst_show_key_expiry_msg = 1;
            }
        }

        update_option('jsst_show_key_expiry_msg', $jsst_show_key_expiry_msg, false);
    }
    
    function jsst_get_theme_colors() {
        require_once(JSST_PLUGIN_PATH . 'includes/css/style.php');
        // Use a static variable to cache the colors after the first run.
        static $jsst_color1s = null;

        // If we already have the colors, return them immediately.
        if ($jsst_color1s !== null) {
            return $jsst_color1s;
        }

        // --- If colors are not cached, generate them now ---
        $jsst_default_colors = [
            'color1' => '#4f46e5',
            'color2' => '#2b2b2b',
            'color3' => '#f8f8f8',
            'color4' => '#636363',
            'color5' => '#d1d1d1',
            'color6' => '#e7e7e7',
            'color7' => '#ffffff',
            'color8' => '#2DA1CB',
            'color9' => '#000000'
        ];
        
        $jsst_saved_colors_json = get_option("jsst_set_theme_colors");
        $jsst_saved_colors = json_decode($jsst_saved_colors_json, true);

        $jsst_final_colors = is_array($jsst_saved_colors) && !empty($jsst_saved_colors)
            ? array_merge($jsst_default_colors, $jsst_saved_colors)
            : $jsst_default_colors;
        
        // Sanitize before and after the filter
        foreach ($jsst_final_colors as $jsst_key => $jsst_value) {
            $jsst_final_colors[$jsst_key] = jsst_validate_css_color($jsst_value); // Assumes jsst_validate_css_color() exists
        }
        $jsst_final_colors = apply_filters('cm_theme_colors', $jsst_final_colors, 'js-support-ticket');
        foreach ($jsst_final_colors as $jsst_key => $jsst_value) {
            $jsst_final_colors[$jsst_key] = jsst_validate_css_color($jsst_value);
        }
        
        // Cache the final, sanitized colors in our static variable
        $jsst_color1s = $jsst_final_colors;
        
        // Also update the class property if you still need it for other reasons
        jssupportticket::$jsst_colors = $jsst_color1s;

        return $jsst_color1s;
    }

}

?>
