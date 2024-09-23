<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreportsModel {

    function getOverallReportData(){

        //Overall Data by status
        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` ";
        $allticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status != 4 AND status != 5";
        $openticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE (status = 4 OR status = 5)";
        $closeticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 5 AND status != 0";
        $answeredticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4";
        $overdueticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00')";
        $pendingticket = jssupportticket::$_db->get_var($query);

        jssupportticket::$_data['ticket_total']['allticket'] = $allticket;
        jssupportticket::$_data['ticket_total']['openticket'] = $openticket;
        jssupportticket::$_data['ticket_total']['closeticket'] = $closeticket;
        jssupportticket::$_data['ticket_total']['answeredticket'] = $answeredticket;
        jssupportticket::$_data['ticket_total']['overdueticket'] = $overdueticket;
        jssupportticket::$_data['ticket_total']['pendingticket'] = $pendingticket;

        jssupportticket::$_data['status_chart'] = "['". esc_html(__('New','js-support-ticket'))."',$openticket],['". esc_html(__('Answered','js-support-ticket'))."',$answeredticket],['". esc_html(__('Overdue','js-support-ticket'))."',$overdueticket],['". esc_html(__('Pending','js-support-ticket'))."',$pendingticket]";
        $total = $openticket + $closeticket + $answeredticket + $overdueticket + $pendingticket;
        jssupportticket::$_data['bar_chart'] = "
        ['". esc_html(__('New','js-support-ticket'))."',$openticket,'#FF9900'],
        ['". esc_html(__('Answered','js-support-ticket'))."',$answeredticket,'#179650'],
        ['". esc_html(__('Closed','js-support-ticket'))."',$closeticket,'#5F3BBB'],
        ['". esc_html(__('Pending','js-support-ticket'))."',$pendingticket,'#D98E11'],
        ['". esc_html(__('Overdue','js-support-ticket'))."',$overdueticket,'#DB624C']
        ";

        $query = "SELECT dept.departmentname,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE departmentid = dept.id) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept";
        $department = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['pie3d_chart1'] = "";
        foreach($department AS $dept){
            jssupportticket::$_data['pie3d_chart1'] .= "['".jssupportticket::JSST_getVarValue($dept->departmentname)."',$dept->totalticket],";
        }

        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $department = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['pie3d_chart2'] = "";
        foreach($department AS $dept){
            jssupportticket::$_data['pie3d_chart2'] .= "['".jssupportticket::JSST_getVarValue($dept->priority)."',$dept->totalticket],";
        }
        if(in_array('emailpiping', jssupportticket::$_active_addons)){
            $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE ticketviaemail = 1";
            $ticketviaemail = jssupportticket::$_db->get_var($query);
            $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_replies` WHERE ticketviaemail = 1";
            $replyviaemail = jssupportticket::$_db->get_var($query);
        }else{
            $ticketviaemail = '';
            $replyviaemail = '';
        }
        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE ticketviaemail = 0";
        $directticket = jssupportticket::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_replies` WHERE ticketviaemail = 0";
        $directreply = jssupportticket::$_db->get_var($query);

        jssupportticket::$_data['stack_data'] = "['". esc_html(__('Tickets','js-support-ticket'))."',$directticket,$ticketviaemail,''],['". esc_html(__('Replies','js-support-ticket'))."',$directreply,$replyviaemail,'']";

        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND status = 0 AND (lastreply = '0000-00-00 00:00:00') ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $openticket_pr = jssupportticket::$_db->get_results($query);
        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND isanswered = 1 AND status != 4 AND status != 0 ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $answeredticket_pr = jssupportticket::$_db->get_results($query);
        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND isoverdue = 1 AND status != 4 ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $overdueticket_pr = jssupportticket::$_db->get_results($query);
        $query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $pendingticket_pr = jssupportticket::$_db->get_results($query);
        // jssupportticket::$_data['stack_chart_horizontal']['title'] = "['". esc_html(__('Tickets','js-support-ticket'))."',";
        // jssupportticket::$_data['stack_chart_horizontal']['data'] = "['". esc_html(__('Overdue','js-support-ticket'))."',";
        // foreach($overdueticket_pr AS $pr){
        //     jssupportticket::$_data['stack_chart_horizontal']['title'] .= "'".$pr->priority."',";
        //     jssupportticket::$_data['stack_chart_horizontal']['data'] .= $pr->totalticket.",";
        // }
        // jssupportticket::$_data['stack_chart_horizontal']['title'] .= "]";
        // jssupportticket::$_data['stack_chart_horizontal']['data'] .= "],['". esc_html(__('Pending','js-support-ticket'))."',";

        // foreach($pendingticket_pr AS $pr){
        //     jssupportticket::$_data['stack_chart_horizontal']['data'] .= $pr->totalticket.",";
        // }

        // jssupportticket::$_data['stack_chart_horizontal']['data'] .= "],['". esc_html(__('Answered','js-support-ticket'))."',";

        // foreach($answeredticket_pr AS $pr){
        //     jssupportticket::$_data['stack_chart_horizontal']['data'] .= $pr->totalticket.",";
        // }

        // jssupportticket::$_data['stack_chart_horizontal']['data'] .= "],['". esc_html(__('New','js-support-ticket'))."',";

        // foreach($openticket_pr AS $pr){
        //     jssupportticket::$_data['stack_chart_horizontal']['data'] .= $pr->totalticket.",";
        // }

        // jssupportticket::$_data['stack_chart_horizontal']['data'] .= "]";
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

        if(in_array('agent',jssupportticket::$_active_addons)){
            $query = "SELECT staff.firstname,staff.lastname,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE staffid = staff.id) AS totalticket
                        FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff";
            $agenttickets = jssupportticket::$_db->get_results($query);
            jssupportticket::$_data['slice_chart'] = '';
            if(!empty($agenttickets))
            foreach($agenttickets AS $ticket){
                $agentname = $ticket->firstname;
                if(!empty($ticket->lastname)){
                    $agentname .= ' '.$ticket->lastname;
                }
                jssupportticket::$_data['slice_chart'] .= "['".$agentname."',$ticket->totalticket],";
            }
        }

        //To show priority colors on chart
        $query = "SELECT prioritycolour FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` ORDER BY priority ";
        $jsonColorList = "[";
        foreach(jssupportticket::$_db->get_results($query) as $priority){
            $jsonColorList.= "'".$priority->prioritycolour."',";
        }
        $jsonColorList .= "]";
        jssupportticket::$_data['priorityColorList'] = $jsonColorList;
        //end priority colors
    }

    function getDepartmentReportsFE(){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        $uid = JSSTincluder::getObjectClass('user')->uid();
        $staffid = JSSTincluder::getJSModel('agent')->getStaffId($uid);
        $query = "SELECT dept.departmentname,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE departmentid = dept.id ) AS totalticket
            FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept
            JOIN `".jssupportticket::$_db->prefix."js_ticket_acl_user_access_departments` AS acl ON acl.departmentid = dept.id
            WHERE acl.staffid = ".esc_sql($staffid)." AND dept.status=1";

        $department = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['pie3d_chart1'] = "";
        $i = 0;
        foreach($department AS $dept){
            if($dept->totalticket == 0)
                $i += 1;
            jssupportticket::$_data['pie3d_chart1'] .= "['".$dept->departmentname."',$dept->totalticket],";
        }

        if(count($department) == $i)
            jssupportticket::$_data['pie3d_chart1'] = '';

        // pagination
        $query = "SELECT count(dept.id)
            FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept
            JOIN `".jssupportticket::$_db->prefix."js_ticket_acl_user_access_departments` AS acl ON acl.departmentid = dept.id
            WHERE acl.staffid = ".esc_sql($staffid)." AND dept.status=1";
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        $query = "SELECT dept.departmentname,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND departmentid = dept.id) AS openticket,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE status = 4 AND departmentid = dept.id) AS closeticket,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND departmentid = dept.id) AS answeredticket,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND departmentid = dept.id) AS overdueticket,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND departmentid = dept.id) AS pendingticket
            FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept
            JOIN `".jssupportticket::$_db->prefix."js_ticket_acl_user_access_departments` AS acl ON acl.departmentid = dept.id
            WHERE acl.staffid = ".esc_sql($staffid)." AND dept.status=1";
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $departments = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['departments_report'] = $departments;

        return;
    }

    function getStaffReports(){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        $date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';

        if(isset($date_start) && $date_start != ""){
            $date_start = date_i18n('Y-m-d',strtotime($date_start));
        }
        if(isset($date_end) && $date_end != ""){
            $date_end = date_i18n('Y-m-d',strtotime($date_end));
        }
        if($date_start > $date_end){
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }

        $uid = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['uid'] : '';

        //Line Chart Data
        $curdate = ($date_start != null) ? date_i18n('Y-m-d',strtotime($date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $dates = '';
        $fromdate = ($date_end != null) ? date_i18n('Y-m-d',strtotime($date_end)) : date_i18n('Y-m-d');
        jssupportticket::$_data['filter']['date_start'] = $curdate;
        jssupportticket::$_data['filter']['date_end'] = $fromdate;
        jssupportticket::$_data['filter']['uid'] = $uid;
        // forexport
        $_SESSION['forexport']['curdate'] = $curdate;
        $_SESSION['forexport']['fromdate'] = $fromdate;
        $_SESSION['forexport']['uid'] = $uid;

        $staffid = JSSTincluder::getJSModel('agent')->getStaffId($uid);
        jssupportticket::$_data['filter']['staffname'] = JSSTincluder::getJSModel('agent')->getMyName($staffid);
        $nextdate = $fromdate;
        //Query to get Data
        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` ";
        if($uid) $query .= " WHERE staffid = ".esc_sql($staffid);
        $allticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND staffid = ".esc_sql($staffid);
        $openticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND staffid = ".esc_sql($staffid);
        $closeticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND staffid = ".esc_sql($staffid);
        $answeredticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND staffid = ".esc_sql($staffid);
        $overdueticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND staffid = ".esc_sql($staffid);
        $pendingticket = jssupportticket::$_db->get_results($query);

        $date_openticket = array();
        $date_closeticket = array();
        $date_answeredticket = array();
        $date_overdueticket = array();
        $date_pendingticket = array();
        foreach ($openticket AS $ticket) {
            if (!isset($date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($closeticket AS $ticket) {
            if (!isset($date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($answeredticket AS $ticket) {
            if (!isset($date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($overdueticket AS $ticket) {
            if (!isset($date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($pendingticket AS $ticket) {
            if (!isset($date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $json_array = "";
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $overdue_ticket = 0;
        $pending_ticket = 0;

        do{
            $year = date_i18n('Y',strtotime($nextdate));
            $month = date_i18n('m',strtotime($nextdate));
            $month = $month - 1; //js month are 0 based
            $day = date_i18n('d',strtotime($nextdate));
            $openticket_tmp = isset($date_openticket[$nextdate]) ? $date_openticket[$nextdate]  : 0;
            $closeticket_tmp = isset($date_closeticket[$nextdate]) ? $date_closeticket[$nextdate] : 0;
            $answeredticket_tmp = isset($date_answeredticket[$nextdate]) ? $date_answeredticket[$nextdate] : 0;
            $overdueticket_tmp = isset($date_overdueticket[$nextdate]) ? $date_overdueticket[$nextdate] : 0;
            $pendingticket_tmp = isset($date_pendingticket[$nextdate]) ? $date_pendingticket[$nextdate] : 0;
            $json_array .= "[new Date($year,$month,$day),$openticket_tmp,$answeredticket_tmp,$pendingticket_tmp,$overdueticket_tmp,$closeticket_tmp],";
            $open_ticket += $openticket_tmp;
            $close_ticket += $closeticket_tmp;
            $answered_ticket += $answeredticket_tmp;
            $overdue_ticket += $overdueticket_tmp;
            $pending_ticket += $pendingticket_tmp;
            if($nextdate == $curdate){
                break;
            }
                $nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($nextdate . " -1 days"));
        }while($nextdate != $curdate);

        jssupportticket::$_data['ticket_total']['allticket'] = $allticket;
        jssupportticket::$_data['ticket_total']['openticket'] = $open_ticket;
        jssupportticket::$_data['ticket_total']['closeticket'] = $close_ticket;
        jssupportticket::$_data['ticket_total']['answeredticket'] = $answered_ticket;
        jssupportticket::$_data['ticket_total']['overdueticket'] = $overdue_ticket;
        jssupportticket::$_data['ticket_total']['pendingticket'] = $pending_ticket;

        jssupportticket::$_data['line_chart_json_array'] = $json_array;

        // Pagination
        $query = "SELECT count(staff.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
                    JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid";
        if($uid) $query .= ' WHERE staff.uid = '.esc_sql($uid);
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total , 'staffreports');

        $query = "SELECT staff.photo,staff.id,staff.firstname,staff.lastname,staff.username,staff.email,user.display_name,user.user_email,user.user_nicename,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS pendingticket  ";
                    if(in_array('feedback', jssupportticket::$_active_addons)){
                        $query .=    ",(SELECT AVG(feed.rating) FROM `" . jssupportticket::$_db->prefix . "js_ticket_feedbacks` AS feed JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON ticket.id= feed.ticketid WHERE date(feed.created) >= '" . esc_sql($curdate) . "' AND date(feed.created) <= '" . esc_sql($fromdate) . "' AND ticket.staffid = staff.id) AS avragerating ";
                    }
                    $query .=  "FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
                    JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid";
        if($uid) $query .= ' WHERE staff.uid = '.esc_sql($uid);
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $agents = jssupportticket::$_db->get_results($query);
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach ($agents as $agent) {
                $agent->time = JSSTincluder::getJSModel('timetracking')->getAverageTimeByStaffId($agent->id);// time 0 contains avergage time in seconds and 1 contains wheter it is conflicted or not
            }
        }
        jssupportticket::$_data['staffs_report'] = $agents;
        return;
    }

    function getDepartmentReports(){
        $date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';
        if(isset($date_start) && $date_start != ""){
            $date_start = date_i18n('Y-m-d',strtotime($date_start));
        }
        if(isset($date_end) && $date_end != ""){
            $date_end = date_i18n('Y-m-d',strtotime($date_end));
        }
        if($date_start > $date_end){
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }

        //Line Chart Data
        $curdate = ($date_start != null) ? date_i18n('Y-m-d',strtotime($date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $dates = '';
        $fromdate = ($date_end != null) ? date_i18n('Y-m-d',strtotime($date_end)) : date_i18n('Y-m-d');
        jssupportticket::$_data['filter']['date_start'] = $curdate;
        jssupportticket::$_data['filter']['date_end'] = $fromdate;
        // forexport
        $_SESSION['forexport']['curdate'] = $curdate;
        $_SESSION['forexport']['fromdate'] = $fromdate;

        $nextdate = $fromdate;
        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        $openticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        $closeticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        $answeredticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        $overdueticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        $pendingticket = jssupportticket::$_db->get_results($query);

        $date_openticket = array();
        $date_closeticket = array();
        $date_answeredticket = array();
        $date_overdueticket = array();
        $date_pendingticket = array();
        foreach ($openticket AS $ticket) {
            if (!isset($date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($closeticket AS $ticket) {
            if (!isset($date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($answeredticket AS $ticket) {
            if (!isset($date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($overdueticket AS $ticket) {
            if (!isset($date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($pendingticket AS $ticket) {
            if (!isset($date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $json_array = "";
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $overdue_ticket = 0;
        $pending_ticket = 0;

        do{
            $year = date_i18n('Y',strtotime($nextdate));
            $month = date_i18n('m',strtotime($nextdate));
            $month = $month - 1; //js month are 0 based
            $day = date_i18n('d',strtotime($nextdate));
            $openticket_tmp = isset($date_openticket[$nextdate]) ? $date_openticket[$nextdate]  : 0;
            $closeticket_tmp = isset($date_closeticket[$nextdate]) ? $date_closeticket[$nextdate] : 0;
            $answeredticket_tmp = isset($date_answeredticket[$nextdate]) ? $date_answeredticket[$nextdate] : 0;
            $overdueticket_tmp = isset($date_overdueticket[$nextdate]) ? $date_overdueticket[$nextdate] : 0;
            $pendingticket_tmp = isset($date_pendingticket[$nextdate]) ? $date_pendingticket[$nextdate] : 0;
            $json_array .= "[new Date($year,$month,$day),$openticket_tmp,$answeredticket_tmp,$pendingticket_tmp,$overdueticket_tmp,$closeticket_tmp],";
            $open_ticket += $openticket_tmp;
            $close_ticket += $closeticket_tmp;
            $answered_ticket += $answeredticket_tmp;
            $overdue_ticket += $overdueticket_tmp;
            $pending_ticket += $pendingticket_tmp;
             if($nextdate == $curdate){
                break;
            }
            $nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($nextdate . " -1 days"));
        }while($nextdate != $curdate);

        jssupportticket::$_data['ticket_total']['openticket'] = $open_ticket;
        jssupportticket::$_data['ticket_total']['closeticket'] = $close_ticket;
        jssupportticket::$_data['ticket_total']['answeredticket'] = $answered_ticket;
        jssupportticket::$_data['ticket_total']['overdueticket'] = $overdue_ticket;
        jssupportticket::$_data['ticket_total']['pendingticket'] = $pending_ticket;

        jssupportticket::$_data['line_chart_json_array'] = $json_array;

        // Pagination
        $query = "SELECT count(department.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS department
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_email` AS email ON department.emailid = email.id";
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        $query = "SELECT department.id,department.departmentname,email.email,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE departmentid = department.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS pendingticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS department
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_email` AS email ON department.emailid = email.id";
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $depatments = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['depatments_report'] =$depatments;
        return;
    }

    function getStaffReportsFE(){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        $date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['jsst-date-start'] : '';
        $date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['jsst-date-end'] : '';
        if(isset($date_start) && $date_start != ""){
            $date_start = date_i18n('Y-m-d',strtotime($date_start));
        }
        if(isset($date_end) && $date_end != ""){
            $date_end = date_i18n('Y-m-d',strtotime($date_end));
        }

        if($date_start > $date_end){
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }

        $uid = JSSTincluder::getObjectClass('user')->uid();

        //Line Chart Data
        $curdate = ($date_start != null) ? date_i18n('Y-m-d',strtotime($date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $dates = '';
        $fromdate = ($date_end != null) ? date_i18n('Y-m-d',strtotime($date_end)) : date_i18n('Y-m-d');
        jssupportticket::$_data['filter']['jsst-date-start'] = $curdate;
        jssupportticket::$_data['filter']['jsst-date-end'] = $fromdate;

        $staffid = JSSTincluder::getJSModel('agent')->getStaffId($uid);

        jssupportticket::$_data['filter']['staffname'] = JSSTincluder::getJSModel('agent')->getMyName($staffid);
        $nextdate = $fromdate;
        // find my depats
        $query = "SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ". esc_sql($staffid);
        $data = jssupportticket::$_db->get_results($query);
        $my_depts = '';
        foreach ($data as $key => $value) {
            if($my_depts)
                $my_depts .= ',';
            $my_depts .= $value->departmentid;
        }
        // get mytickets, or all tickets with my depatments
        if($my_depts)
            $dep_query = " AND (ticket.staffid = $staffid OR ticket.departmentid IN ($my_depts)) ";
        else
            $dep_query = " AND ( ticket.staffid = $staffid ) ";
        //Query to get Data
        $query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status = 0 AND (ticket.lastreply = '0000-00-00 00:00:00') AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "'";
        $query .= $dep_query;
        $openticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status = 4 AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "'";
        $query .= $dep_query;
        $closeticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isanswered = 1 AND ticket.status != 4 AND ticket.status != 0 AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "'";
        $query .= $dep_query;
        $answeredticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isoverdue = 1 AND ticket.status != 4 AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "'";
        $query .= $dep_query;
        $overdueticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isanswered != 1 AND ticket.status != 4 AND (ticket.lastreply != '0000-00-00 00:00:00') AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "'";
        $query .= $dep_query;
        $pendingticket = jssupportticket::$_db->get_results($query);

        $date_openticket = array();
        $date_closeticket = array();
        $date_answeredticket = array();
        $date_overdueticket = array();
        $date_pendingticket = array();
        foreach ($openticket AS $ticket) {
            if (!isset($date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($closeticket AS $ticket) {
            if (!isset($date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($answeredticket AS $ticket) {
            if (!isset($date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($overdueticket AS $ticket) {
            if (!isset($date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($pendingticket AS $ticket) {
            if (!isset($date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $json_array = "";
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $overdue_ticket = 0;
        $pending_ticket = 0;

        do{
            $year = date_i18n('Y',strtotime($nextdate));
            $month = date_i18n('m',strtotime($nextdate));
            $month = $month - 1; //js month are 0 based
            $day = date_i18n('d',strtotime($nextdate));
            $openticket_tmp = isset($date_openticket[$nextdate]) ? $date_openticket[$nextdate]  : 0;
            $closeticket_tmp = isset($date_closeticket[$nextdate]) ? $date_closeticket[$nextdate] : 0;
            $answeredticket_tmp = isset($date_answeredticket[$nextdate]) ? $date_answeredticket[$nextdate] : 0;
            $overdueticket_tmp = isset($date_overdueticket[$nextdate]) ? $date_overdueticket[$nextdate] : 0;
            $pendingticket_tmp = isset($date_pendingticket[$nextdate]) ? $date_pendingticket[$nextdate] : 0;
            $json_array .= "[new Date($year,$month,$day),$openticket_tmp,$answeredticket_tmp,$pendingticket_tmp,$overdueticket_tmp,$closeticket_tmp],";
            $open_ticket += $openticket_tmp;
            $close_ticket += $closeticket_tmp;
            $answered_ticket += $answeredticket_tmp;
            $overdue_ticket += $overdueticket_tmp;
            $pending_ticket += $pendingticket_tmp;
             if($nextdate == $curdate){
                break;
            }
            $nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($nextdate . " -1 days"));
        }while($nextdate != $curdate);

        jssupportticket::$_data['ticket_total']['openticket'] = $open_ticket;
        jssupportticket::$_data['ticket_total']['closeticket'] = $close_ticket;
        jssupportticket::$_data['ticket_total']['answeredticket'] = $answered_ticket;
        jssupportticket::$_data['ticket_total']['overdueticket'] = $overdue_ticket;
        jssupportticket::$_data['ticket_total']['pendingticket'] = $pending_ticket;

        jssupportticket::$_data['line_chart_json_array'] = $json_array;

        // Pagination staffs listing
        $query = "SELECT COUNT(DISTINCT staff.id)
            FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
            JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid
            LEFT JOIN `".jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dep ON dep.staffid = staff.id ";
        $query .= " WHERE (staff.id = ".esc_sql($staffid)." OR dep.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($staffid)."))";
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);
        // data
        $query = "SELECT DISTINCT staff.photo,staff.id,staff.firstname,staff.lastname,staff.username,staff.email,user.display_name,user.user_email,user.user_nicename,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status = 0 AND (ticket.lastreply = '0000-00-00 00:00:00') AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' AND ticket.staffid = staff.id) AS openticket,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status = 4 AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' AND ticket.staffid = staff.id) AS closeticket,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isanswered = 1 AND ticket.status != 4 AND ticket.status != 0 AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' AND ticket.staffid = staff.id) AS answeredticket,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isoverdue = 1 AND ticket.status != 4 AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' AND ticket.staffid = staff.id) AS overdueticket,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isanswered != 1 AND ticket.status != 4 AND (ticket.lastreply != '0000-00-00 00:00:00') AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' AND ticket.staffid = staff.id) AS pendingticket
            FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
            JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid
            LEFT JOIN `".jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dep ON dep.staffid = staff.id";
        $query .= " WHERE (staff.id = ".esc_sql($staffid)." OR dep.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($staffid)."))";
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $agents = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['staffs_report'] = $agents;
        return;
    }

    function isValidStaffid($staffid){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return false;
        }

        if( ! is_numeric($staffid))
            return false;
        $uid = JSSTincluder::getObjectClass('user')->uid();
        $id = JSSTincluder::getJSModel('agent')->getStaffId($uid);
        if( $id == $staffid )
            return true;
        $query = "SELECT staff.id AS staffid
            FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
            JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid
            JOIN `".jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dep ON dep.staffid = staff.id ";
        $query .= " WHERE (dep.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($id)."))";
        $result = jssupportticket::$_db->get_results($query);
        foreach ($result as $agent) {
            if($agent->staffid == $staffid)
                return true;
        }
        return false;
    }

    function getUserReports(){
        $date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';
        if(isset($date_start) && $date_start != ""){
            $date_start = date_i18n('Y-m-d',strtotime($date_start));
        }
        if(isset($date_end) && $date_end != ""){
            $date_end = date_i18n('Y-m-d',strtotime($date_end));
        }
        if($date_start > $date_end){
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }
        $uid = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['uid'] : '';

        //Line Chart Data
        $curdate = ($date_start != null) ? date_i18n('Y-m-d',strtotime($date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $dates = '';
        $fromdate = ($date_end != null) ? date_i18n('Y-m-d',strtotime($date_end)) : date_i18n('Y-m-d');
        jssupportticket::$_data['filter']['date_start'] = $curdate;
        jssupportticket::$_data['filter']['date_end'] = $fromdate;
        jssupportticket::$_data['filter']['uid'] = $uid;

        // forexport
        $_SESSION['forexport']['curdate'] = $curdate;
        $_SESSION['forexport']['fromdate'] = $fromdate;
        $_SESSION['forexport']['uid'] = $uid;

        jssupportticket::$_data['filter']['username'] = JSSTincluder::getJSModel('jssupportticket')->getUserNameById($uid);
        $nextdate = $fromdate;
        //Query to get Data
        $query = "SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` ";
        if($uid) $query .= " WHERE  uid = ".esc_sql($uid);
        $allticket = jssupportticket::$_db->get_var($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0  AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND uid = ".esc_sql($uid);
        $openticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND uid = ".esc_sql($uid);
        $closeticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND uid = ".esc_sql($uid);
        $answeredticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND uid = ". esc_sql($uid);
        $overdueticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($uid) $query .= " AND uid = ".esc_sql($uid);
        $pendingticket = jssupportticket::$_db->get_results($query);

        $date_openticket = array();
        $date_closeticket = array();
        $date_answeredticket = array();
        $date_overdueticket = array();
        $date_pendingticket = array();
        foreach ($openticket AS $ticket) {
            if (!isset($date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($closeticket AS $ticket) {
            if (!isset($date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($answeredticket AS $ticket) {
            if (!isset($date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($overdueticket AS $ticket) {
            if (!isset($date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($pendingticket AS $ticket) {
            if (!isset($date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $overdue_ticket = 0;
        $pending_ticket = 0;
        $json_array = "";
        do{
            $year = date_i18n('Y',strtotime($nextdate));
            $month = date_i18n('m',strtotime($nextdate));
            $month = $month - 1; //js month are 0 based
            $day = date_i18n('d',strtotime($nextdate));
            $openticket_tmp = isset($date_openticket[$nextdate]) ? $date_openticket[$nextdate]  : 0;
            $closeticket_tmp = isset($date_closeticket[$nextdate]) ? $date_closeticket[$nextdate] : 0;
            $answeredticket_tmp = isset($date_answeredticket[$nextdate]) ? $date_answeredticket[$nextdate] : 0;
            $overdueticket_tmp = isset($date_overdueticket[$nextdate]) ? $date_overdueticket[$nextdate] : 0;
            $pendingticket_tmp = isset($date_pendingticket[$nextdate]) ? $date_pendingticket[$nextdate] : 0;
            $json_array .= "[new Date($year,$month,$day),$openticket_tmp,$answeredticket_tmp,$pendingticket_tmp,$overdueticket_tmp,$closeticket_tmp],";
            $open_ticket += $openticket_tmp;
            $close_ticket += $closeticket_tmp;
            $answered_ticket += $answeredticket_tmp;
            $overdue_ticket += $overdueticket_tmp;
            $pending_ticket += $pendingticket_tmp;
             if($nextdate == $curdate){
                break;
            }
            $nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($nextdate . " -1 days"));
        }while($nextdate != $curdate);

        jssupportticket::$_data['ticket_total']['allticket'] = $allticket;
        jssupportticket::$_data['ticket_total']['openticket'] = $open_ticket;
        jssupportticket::$_data['ticket_total']['closeticket'] = $close_ticket;
        jssupportticket::$_data['ticket_total']['answeredticket'] = $answered_ticket;
        jssupportticket::$_data['ticket_total']['overdueticket'] = $overdue_ticket;
        jssupportticket::$_data['ticket_total']['pendingticket'] = $pending_ticket;

        jssupportticket::$_data['line_chart_json_array'] = $json_array;

        // Pagination
        $query = "SELECT COUNT(user.id)
                    FROM `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user
                    WHERE  ";
                    if(in_array('agent', jssupportticket::$_active_addons)){
                        $query .=" NOT EXISTS (SELECT id FROM `".jssupportticket::$_db->prefix."js_ticket_staff` WHERE uid = user.id) AND  ";
                    }
                    $query .=" NOT EXISTS (SELECT umeta_id FROM `".jssupportticket::$_wpprefixforuser."usermeta` WHERE user_id = user.id AND meta_value LIKE '%administrator%')";
        if($uid) $query .= " AND user.id = ".esc_sql($uid);
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        $query = "SELECT user.display_name,user.user_email,user.user_nicename,user.id,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = user.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS pendingticket
                    FROM `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user
                    WHERE  ";
                    if(in_array('agent', jssupportticket::$_active_addons)){
                        $query .=" NOT EXISTS (SELECT id FROM `".jssupportticket::$_db->prefix."js_ticket_staff` WHERE uid = user.id) AND  ";
                    }
                    $query .=" NOT EXISTS (SELECT umeta_id FROM `".jssupportticket::$_wpprefixforuser."usermeta` WHERE user_id = user.id AND meta_value LIKE '%administrator%')";
        if($uid) $query .= " AND user.id = ".esc_sql($uid);
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $users = jssupportticket::$_db->get_results($query);
        jssupportticket::$_data['users_report'] =$users;
        return;
    }

    function getStaffDetailReportByStaffId($id){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        if(!is_numeric($id)) return false;

        if( ! is_admin()){
            $result = $this->isValidStaffid( $id );
            if( $result == false)
                return false;
        }

        $start_date = is_admin() ? 'date_start' : 'jsst-date-start';
        $end_date = is_admin() ? 'date_end' : 'jsst-date-end';
        if(isset($date_start) && $date_start != ""){
            $date_start = date_i18n('Y-m-d',strtotime($date_start));
        }
        if(isset($date_end) && $date_end != ""){
            $date_end = date_i18n('Y-m-d',strtotime($date_end));
        }
        $date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report'][$start_date] : '';
        $date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report'][$end_date] : '';
        if(isset($date_start) && $date_start != ""){
            $date_start = date_i18n('Y-m-d',strtotime($date_start));
        }
        if(isset($date_end) && $date_end != ""){
            $date_end = date_i18n('Y-m-d',strtotime($date_end));
        }
        if($date_start > $date_end){
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }

        //Line Chart Data
        $curdate = ($date_start != null) ? date_i18n('Y-m-d',strtotime($date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $fromdate = ($date_end != null) ? date_i18n('Y-m-d',strtotime($date_end)) : date_i18n('Y-m-d');
        jssupportticket::$_data['filter'][$start_date] = $curdate;
        jssupportticket::$_data['filter'][$end_date] = $fromdate;
        jssupportticket::$_data['filter']['uid'] = $id;

        $nextdate = $fromdate;

        //Query to get Data
        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND staffid = ".esc_sql($id);
        $openticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND staffid = ".esc_sql($id);
        $closeticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND staffid = ".esc_sql($id);
        $answeredticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND staffid = ".esc_sql($id);
        $overdueticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND staffid = ".esc_sql($id);
        $pendingticket = jssupportticket::$_db->get_results($query);

        $date_openticket = array();
        $date_closeticket = array();
        $date_answeredticket = array();
        $date_overdueticket = array();
        $date_pendingticket = array();
        foreach ($openticket AS $ticket) {
            if (!isset($date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($closeticket AS $ticket) {
            if (!isset($date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($answeredticket AS $ticket) {
            if (!isset($date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($overdueticket AS $ticket) {
            if (!isset($date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($pendingticket AS $ticket) {
            if (!isset($date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $overdue_ticket = 0;
        $pending_ticket = 0;
        $json_array = "";
        do{
            $year = date_i18n('Y',strtotime($nextdate));
            $month = date_i18n('m',strtotime($nextdate));
            $month = $month - 1; //js month are 0 based
            $day = date_i18n('d',strtotime($nextdate));
            $openticket_tmp = isset($date_openticket[$nextdate]) ? $date_openticket[$nextdate]  : 0;
            $closeticket_tmp = isset($date_closeticket[$nextdate]) ? $date_closeticket[$nextdate] : 0;
            $answeredticket_tmp = isset($date_answeredticket[$nextdate]) ? $date_answeredticket[$nextdate] : 0;
            $overdueticket_tmp = isset($date_overdueticket[$nextdate]) ? $date_overdueticket[$nextdate] : 0;
            $pendingticket_tmp = isset($date_pendingticket[$nextdate]) ? $date_pendingticket[$nextdate] : 0;
            $json_array .= "[new Date($year,$month,$day),$openticket_tmp,$answeredticket_tmp,$pendingticket_tmp,$overdueticket_tmp,$closeticket_tmp],";
            $open_ticket += $openticket_tmp;
            $close_ticket += $closeticket_tmp;
            $answered_ticket += $answeredticket_tmp;
            $overdue_ticket += $overdueticket_tmp;
            $pending_ticket += $pendingticket_tmp;
             if($nextdate == $curdate){
                break;
            }
            $nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($nextdate . " -1 days"));
        }while($nextdate != $curdate);

        jssupportticket::$_data['line_chart_json_array'] = $json_array;

        $query = "SELECT staff.photo,staff.id,staff.firstname,staff.lastname,staff.username,staff.email,user.display_name,user.user_email,user.user_nicename,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND staffid = staff.id) AS pendingticket   ";
                    if(in_array('feedback', jssupportticket::$_active_addons)){
                        $query .=    ",(SELECT AVG(feed.rating) FROM `" . jssupportticket::$_db->prefix . "js_ticket_feedbacks` AS feed JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON ticket.id= feed.ticketid WHERE date(feed.created) >= '" . esc_sql($curdate) . "' AND date(feed.created) <= '" . esc_sql($fromdate) . "' AND ticket.staffid = staff.id) AS avragerating ";
                    }
                    $query .=  "FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
                    JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid
                    WHERE staff.id = ".esc_sql($id);

        $agent = jssupportticket::$_db->get_row($query);
        if(!empty($agent)){
            if(in_array('timetracking', jssupportticket::$_active_addons)){
                $agent->time = JSSTincluder::getJSModel('timetracking')->getAverageTimeByStaffId($agent->id);// time 0 contains avergage time in seconds and 1 contains wheter it is conflicted or not
            }
        }

        jssupportticket::$_data['staff_report'] =$agent;
        // ticket ids for staff member on which he replied but are not assigned to him
        $ticketid_string = '';
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            $query = "SELECT DISTINCT(ticketid) AS ticketid
                        FROM `".jssupportticket::$_db->prefix."js_ticket_staff_time`
                        WHERE staffid = ".esc_sql($id);
            $all_tickets = jssupportticket::$_db->get_results($query);
            $comma = '';
            foreach ($all_tickets as $ticket) {
                $ticketid_string .= $comma.$ticket->ticketid;
                $comma = ', ';
            }
        }

        if($ticketid_string == ''){
            $q_strig = "(staffid = ".esc_sql($id).")";
        }else{
            $q_strig = "(staffid = ".esc_sql($id)." OR ticket.id IN (".esc_sql($ticketid_string)."))";
        }

        // Pagination
        $query = "SELECT COUNT(ticket.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    WHERE ".esc_sql($q_strig)." AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' ";
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total,'staffdetailreport');

        //Tickets
        do_action('jsstFeedbackQueryStaff');
        $query = "SELECT ticket.*,priority.priority, priority.prioritycolour ".jssupportticket::$_addon_query['select']."
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    ".jssupportticket::$_addon_query['join']."
                    WHERE ".esc_sql($q_strig)." AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' ";
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        do_action('reset_jsst_aadon_query');
        jssupportticket::$_data['staff_tickets'] = jssupportticket::$_db->get_results($query);

        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach (jssupportticket::$_data['staff_tickets'] as $ticket) {
                 //$ticket->time = JSSTincluder::getJSModel('agent')->getTimeTakenByTicketId($ticket->id);
                 $ticket->time = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketIdAndStaffid($ticket->id,$id);// second parameter is staff id
            }
        }
        return;
    }

    function getDepartmentDetailReportByDepartmentId($id){
        if(!is_numeric($id)) return false;

        $start_date ='date_start';
        $end_date ='date_end';

        $date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';
        if(isset($date_start) && $date_start != ""){
            $date_start = date_i18n('Y-m-d',strtotime($date_start));
        }
        if(isset($date_end) && $date_end != ""){
            $date_end = date_i18n('Y-m-d',strtotime($date_end));
        }
        if($date_start > $date_end){
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }

        //Line Chart Data
        $curdate = ($date_start != null) ? date_i18n('Y-m-d',strtotime($date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $fromdate = ($date_end != null) ? date_i18n('Y-m-d',strtotime($date_end)) : date_i18n('Y-m-d');
        jssupportticket::$_data['filter'][$start_date] = $curdate;
        jssupportticket::$_data['filter'][$end_date] = $fromdate;
        jssupportticket::$_data['filter']['id'] = $id;

        $nextdate = $fromdate;

        //Query to get Data
        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND departmentid = ".esc_sql($id);
        $openticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND departmentid = ".esc_sql($id);
        $closeticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND departmentid = ".esc_sql($id);
        $answeredticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND departmentid = ".esc_sql($id);
        $overdueticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND departmentid = ".esc_sql($id);
        $pendingticket = jssupportticket::$_db->get_results($query);

        $date_openticket = array();
        $date_closeticket = array();
        $date_answeredticket = array();
        $date_overdueticket = array();
        $date_pendingticket = array();
        foreach ($openticket AS $ticket) {
            if (!isset($date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($closeticket AS $ticket) {
            if (!isset($date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($answeredticket AS $ticket) {
            if (!isset($date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($overdueticket AS $ticket) {
            if (!isset($date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($pendingticket AS $ticket) {
            if (!isset($date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $overdue_ticket = 0;
        $pending_ticket = 0;
        $json_array = "";
        do{
            $year = date_i18n('Y',strtotime($nextdate));
            $month = date_i18n('m',strtotime($nextdate));
            $month = $month - 1; //js month are 0 based
            $day = date_i18n('d',strtotime($nextdate));
            $openticket_tmp = isset($date_openticket[$nextdate]) ? $date_openticket[$nextdate]  : 0;
            $closeticket_tmp = isset($date_closeticket[$nextdate]) ? $date_closeticket[$nextdate] : 0;
            $answeredticket_tmp = isset($date_answeredticket[$nextdate]) ? $date_answeredticket[$nextdate] : 0;
            $overdueticket_tmp = isset($date_overdueticket[$nextdate]) ? $date_overdueticket[$nextdate] : 0;
            $pendingticket_tmp = isset($date_pendingticket[$nextdate]) ? $date_pendingticket[$nextdate] : 0;
            $json_array .= "[new Date($year,$month,$day),$openticket_tmp,$answeredticket_tmp,$pendingticket_tmp,$overdueticket_tmp,$closeticket_tmp],";
            $open_ticket += $openticket_tmp;
            $close_ticket += $closeticket_tmp;
            $answered_ticket += $answeredticket_tmp;
            $overdue_ticket += $overdueticket_tmp;
            $pending_ticket += $pendingticket_tmp;
             if($nextdate == $curdate){
                break;
            }
            $nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($nextdate . " -1 days"));
        }while($nextdate != $curdate);

        jssupportticket::$_data['line_chart_json_array'] = $json_array;


        // Pagination
        $query = "SELECT count(ticket.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_departments` AS department ON department.id = ticket.departmentid WHERE department.id = ".esc_sql($id);
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        $query = "SELECT department.id,department.departmentname,email.email,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE departmentid = department.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND departmentid = department.id) AS pendingticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS department
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_email` AS email ON department.emailid = email.id
                    WHERE department.id = ".esc_sql($id);
        //$query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        // No need of pagination because query return only single record
        $depatments = jssupportticket::$_db->get_row($query);
        jssupportticket::$_data['depatments_report'] =$depatments;

        //Tickets
        do_action('jsstFeedbackQueryStaff');
        $query = "SELECT ticket.*,priority.priority, priority.prioritycolour ".jssupportticket::$_addon_query['select']."
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    ".jssupportticket::$_addon_query['join']."
                    WHERE departmentid = ".esc_sql($id)." AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' ";
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        do_action('reset_jsst_aadon_query');

        jssupportticket::$_data['department_tickets'] = jssupportticket::$_db->get_results($query);
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach (jssupportticket::$_data['department_tickets'] as $ticket) {
                 $ticket->time = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($ticket->id);
            }
        }
    }


    function getStaffDetailReportByUserId($id){
        if(!is_numeric($id)) return false;

        $date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';
        if(isset($date_start) && $date_start != ""){
            $date_start = date_i18n('Y-m-d',strtotime($date_start));
        }
        if(isset($date_end) && $date_end != ""){
            $date_end = date_i18n('Y-m-d',strtotime($date_end));
        }
        if($date_start > $date_end){
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }
        //Line Chart Data
        $curdate = ($date_start != null) ? date_i18n('Y-m-d',strtotime($date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $fromdate = ($date_end != null) ? date_i18n('Y-m-d',strtotime($date_end)) : date_i18n('Y-m-d');
        jssupportticket::$_data['filter']['date_start'] = $curdate;
        jssupportticket::$_data['filter']['date_end'] = $fromdate;
        jssupportticket::$_data['filter']['uid'] = $id;
        $nextdate = $fromdate;

        // forexport
        $_SESSION['forexport']['curdate'] = $curdate;
        $_SESSION['forexport']['fromdate'] = $fromdate;
        $_SESSION['forexport']['id'] = $id;


        //Query to get Data
        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $openticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $closeticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $answeredticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $overdueticket = jssupportticket::$_db->get_results($query);

        $query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "'";
        if($id) $query .= " AND uid = ".esc_sql($id);
        $pendingticket = jssupportticket::$_db->get_results($query);

        $date_openticket = array();
        $date_closeticket = array();
        $date_answeredticket = array();
        $date_overdueticket = array();
        $date_pendingticket = array();
        foreach ($openticket AS $ticket) {
            if (!isset($date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($closeticket AS $ticket) {
            if (!isset($date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($answeredticket AS $ticket) {
            if (!isset($date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($overdueticket AS $ticket) {
            if (!isset($date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        foreach ($pendingticket AS $ticket) {
            if (!isset($date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + 1;
        }
        $open_ticket = 0;
        $close_ticket = 0;
        $answered_ticket = 0;
        $overdue_ticket = 0;
        $pending_ticket = 0;
        $json_array = "";
        do{
            $year = date_i18n('Y',strtotime($nextdate));
            $month = date_i18n('m',strtotime($nextdate));
            $month = $month - 1; //js month are 0 based
            $day = date_i18n('d',strtotime($nextdate));
            $openticket_tmp = isset($date_openticket[$nextdate]) ? $date_openticket[$nextdate]  : 0;
            $closeticket_tmp = isset($date_closeticket[$nextdate]) ? $date_closeticket[$nextdate] : 0;
            $answeredticket_tmp = isset($date_answeredticket[$nextdate]) ? $date_answeredticket[$nextdate] : 0;
            $overdueticket_tmp = isset($date_overdueticket[$nextdate]) ? $date_overdueticket[$nextdate] : 0;
            $pendingticket_tmp = isset($date_pendingticket[$nextdate]) ? $date_pendingticket[$nextdate] : 0;
            $json_array .= "[new Date($year,$month,$day),$openticket_tmp,$answeredticket_tmp,$pendingticket_tmp,$overdueticket_tmp,$closeticket_tmp],";
            $open_ticket += $openticket_tmp;
            $close_ticket += $closeticket_tmp;
            $answered_ticket += $answeredticket_tmp;
            $overdue_ticket += $overdueticket_tmp;
            $pending_ticket += $pendingticket_tmp;
             if($nextdate == $curdate){
                break;
            }
            $nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($nextdate . " -1 days"));
        }while($nextdate != $curdate);

        jssupportticket::$_data['line_chart_json_array'] = $json_array;

        $query = "SELECT user.display_name,user.user_email,user.user_nicename,user.id,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = user.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 0  AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND isoverdue = 1 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($curdate) . "' AND date(created) <= '" . esc_sql($fromdate) . "' AND uid = user.id) AS pendingticket
                    FROM `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user
                    WHERE user.id = ".esc_sql($id);
        $agent = jssupportticket::$_db->get_row($query);
        jssupportticket::$_data['user_report'] =$agent;
        // Pagination
        $query = "SELECT COUNT(ticket.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    WHERE uid = ".esc_sql($id)." AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' ";
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data['total'] = $total;
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);
        //Tickets
        do_action('jsstFeedbackQueryStaff');
        $query = "SELECT ticket.*,priority.priority, priority.prioritycolour ".jssupportticket::$_addon_query['select']."
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    ".jssupportticket::$_addon_query['join']."
                    WHERE uid = ".esc_sql($id)." AND date(ticket.created) >= '" . esc_sql($curdate) . "' AND date(ticket.created) <= '" . esc_sql($fromdate) . "' ";
        $query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        do_action('reset_jsst_aadon_query');
        jssupportticket::$_data['user_tickets'] = jssupportticket::$_db->get_results($query);
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach (jssupportticket::$_data['user_tickets'] as $ticket) {
                 $ticket->time = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($ticket->id);
            }
        }
        return;
    }

    function getStaffTimingReportById($id){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        if(!is_numeric($id)) return false;

        $start_date ='date_start';
        $end_date ='date_end';

        $date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report'][$start_date] : '';
        $date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report'][$end_date] : '';
        if($date_start > $date_end){
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }

        //Line Chart Data
        $curdate = ($date_start != null) ? date_i18n('Y-m-d',strtotime($date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $fromdate = ($date_end != null) ? date_i18n('Y-m-d',strtotime($date_end)) : date_i18n('Y-m-d');
        jssupportticket::$_data['filter'][$start_date] = $curdate;
        jssupportticket::$_data['filter'][$end_date] = $fromdate;
        jssupportticket::$_data['filter']['id'] = $id;

        $nextdate = $fromdate;

        //Query to get Data
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            $query = "SELECT created,usertime FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_time` ";
            $query .= " WHERE staffid = ".esc_sql($id);
            $openticket = jssupportticket::$_db->get_results($query);
        }else{
            $openticket = array();
        }

        $date_openticket = array();
        foreach ($openticket AS $ticket) {
            if (!isset($date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))]))
                $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = 0;
            $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] = $date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($ticket->created))] + $ticket->usertime;
        }
        $open_ticket = 0;
        $json_array = "";
        do{
            $year = date_i18n('Y',strtotime($nextdate));
            $month = date_i18n('m',strtotime($nextdate));
            $month = $month - 1; //js month are 0 based
            $day = date_i18n('d',strtotime($nextdate));
            if(isset($date_openticket[$nextdate])){

                $mins = floor($date_openticket[$nextdate] / 60);
                $openticket_tmp =  $mins;
            }else{
                $openticket_tmp =  0;
            }
            $json_array .= '[new Date('.$year.','.$month.','.$day.'),'.$openticket_tmp.'],';
            $nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($nextdate . " -1 days"));
        }while($nextdate != $curdate);
        jssupportticket::$_data['line_chart_json_array'] = $json_array;
        jssupportticket::$_data[0]['staffname'] = JSSTincluder::getJSModel('agent')->getMyName($id);

    }


}
?>
