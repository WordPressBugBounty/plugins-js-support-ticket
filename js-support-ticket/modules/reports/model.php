<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreportsModel {

    function getOverallReportData(){

        //Overall Data by status
        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` ";
        $jsst_allticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status != 5 AND status != 6";
        $jsst_openticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE (status = 5 OR status = 6)";
        $jsst_closeticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 6 AND status != 1";
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

        jssupportticket::$jsst_data['status_chart'] = "['". esc_html(__('New','js-support-ticket'))."',$jsst_openticket],['". esc_html(__('Answered','js-support-ticket'))."',$jsst_answeredticket],['". esc_html(__('Overdue','js-support-ticket'))."',$jsst_overdueticket],['". esc_html(__('Pending','js-support-ticket'))."',$jsst_pendingticket]";
        $jsst_total = $jsst_openticket + $jsst_closeticket + $jsst_answeredticket + $jsst_overdueticket + $jsst_pendingticket;
        jssupportticket::$jsst_data['bar_chart'] = "
        ['". esc_html(__('New','js-support-ticket'))."',$jsst_openticket,'#FF9900'],
        ['". esc_html(__('Answered','js-support-ticket'))."',$jsst_answeredticket,'#179650'],
        ['". esc_html(__('Closed','js-support-ticket'))."',$jsst_closeticket,'#5F3BBB'],
        ['". esc_html(__('Pending','js-support-ticket'))."',$jsst_pendingticket,'#D98E11'],
        ['". esc_html(__('Overdue','js-support-ticket'))."',$jsst_overdueticket,'#DB624C']
        ";

        $jsst_query = "SELECT dept.departmentname,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE departmentid = dept.id) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept";
        $jsst_department = jssupportticket::$_db->get_results($jsst_query);
        jssupportticket::$jsst_data['pie3d_chart1'] = "";
        foreach($jsst_department AS $jsst_dept){
            jssupportticket::$jsst_data['pie3d_chart1'] .= "['".jssupportticket::JSST_getVarValue($jsst_dept->departmentname)."',$jsst_dept->totalticket],";
        }

        $jsst_query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $jsst_department = jssupportticket::$_db->get_results($jsst_query);
        jssupportticket::$jsst_data['pie3d_chart2'] = "";
        foreach($jsst_department AS $jsst_dept){
            jssupportticket::$jsst_data['pie3d_chart2'] .= "['".jssupportticket::JSST_getVarValue($jsst_dept->priority)."',$jsst_dept->totalticket],";
        }
        if(in_array('emailpiping', jssupportticket::$_active_addons)){
            $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE ticketviaemail = 1";
            $jsst_ticketviaemail = jssupportticket::$_db->get_var($jsst_query);
            $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_replies` WHERE ticketviaemail = 1";
            $jsst_replyviaemail = jssupportticket::$_db->get_var($jsst_query);
        }else{
            $jsst_ticketviaemail = '';
            $jsst_replyviaemail = '';
        }
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE ticketviaemail = 0";
        $jsst_directticket = jssupportticket::$_db->get_var($jsst_query);
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_replies` WHERE ticketviaemail = 0";
        $jsst_directreply = jssupportticket::$_db->get_var($jsst_query);

        jssupportticket::$jsst_data['stack_data'] = "['". esc_html(__('Tickets','js-support-ticket'))."',$jsst_directticket,$jsst_ticketviaemail,''],['". esc_html(__('Replies','js-support-ticket'))."',$jsst_directreply,$jsst_replyviaemail,'']";

        $jsst_query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND status = 1 AND (lastreply = '0000-00-00 00:00:00') ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $jsst_openticket_pr = jssupportticket::$_db->get_results($jsst_query);
        $jsst_query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND isanswered = 1 AND status != 5 AND status != 1 ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $jsst_answeredticket_pr = jssupportticket::$_db->get_results($jsst_query);
        $jsst_query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND isoverdue = 1 AND status != 5 ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $jsst_overdueticket_pr = jssupportticket::$_db->get_results($jsst_query);
        $jsst_query = "SELECT priority.priority,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE priorityid = priority.id AND isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') ) AS totalticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ORDER BY priority.priority";
        $jsst_pendingticket_pr = jssupportticket::$_db->get_results($jsst_query);
        // jssupportticket::$jsst_data['stack_chart_horizontal']['title'] = "['". esc_html(__('Tickets','js-support-ticket'))."',";
        // jssupportticket::$jsst_data['stack_chart_horizontal']['data'] = "['". esc_html(__('Overdue','js-support-ticket'))."',";
        // foreach($jsst_overdueticket_pr AS $jsst_pr){
        //     jssupportticket::$jsst_data['stack_chart_horizontal']['title'] .= "'".$jsst_pr->priority."',";
        //     jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= $jsst_pr->totalticket.",";
        // }
        // jssupportticket::$jsst_data['stack_chart_horizontal']['title'] .= "]";
        // jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= "],['". esc_html(__('Pending','js-support-ticket'))."',";

        // foreach($jsst_pendingticket_pr AS $jsst_pr){
        //     jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= $jsst_pr->totalticket.",";
        // }

        // jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= "],['". esc_html(__('Answered','js-support-ticket'))."',";

        // foreach($jsst_answeredticket_pr AS $jsst_pr){
        //     jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= $jsst_pr->totalticket.",";
        // }

        // jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= "],['". esc_html(__('New','js-support-ticket'))."',";

        // foreach($jsst_openticket_pr AS $jsst_pr){
        //     jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= $jsst_pr->totalticket.",";
        // }

        // jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= "]";
        jssupportticket::$jsst_data['stack_chart_horizontal']['title'] = "['". esc_html(__('Priority','js-support-ticket'))."','". esc_html(__('Overdue','js-support-ticket'))."','". esc_html(__('Pending','js-support-ticket'))."','". esc_html(__('Answered','js-support-ticket'))."','". esc_html(__('New','js-support-ticket'))."']";
        jssupportticket::$jsst_data['stack_chart_horizontal']['data'] = "";

        foreach($jsst_overdueticket_pr AS $jsst_index => $jsst_pr){
            jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= "[";
            jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= "'".jssupportticket::JSST_getVarValue($jsst_pr->priority)."',";
            jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= $jsst_overdueticket_pr[$jsst_index]->totalticket.",";
            jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= $jsst_pendingticket_pr[$jsst_index]->totalticket.",";
            jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= $jsst_answeredticket_pr[$jsst_index]->totalticket.",";
            jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= $jsst_openticket_pr[$jsst_index]->totalticket.",";
            jssupportticket::$jsst_data['stack_chart_horizontal']['data'] .= "],";
        }

        if(in_array('agent',jssupportticket::$_active_addons)){
            $jsst_query = "SELECT staff.firstname,staff.lastname,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE staffid = staff.id) AS totalticket
                        FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff";
            $jsst_agenttickets = jssupportticket::$_db->get_results($jsst_query);
            jssupportticket::$jsst_data['slice_chart'] = '';
            if(!empty($jsst_agenttickets))
            foreach($jsst_agenttickets AS $jsst_ticket){
                $jsst_agentname = $jsst_ticket->firstname;
                if(!empty($jsst_ticket->lastname)){
                    $jsst_agentname .= ' '.$jsst_ticket->lastname;
                }
                jssupportticket::$jsst_data['slice_chart'] .= "['".$jsst_agentname."',$jsst_ticket->totalticket],";
            }
        }

        //To show priority colors on chart
        $jsst_query = "SELECT prioritycolour FROM `".jssupportticket::$_db->prefix."js_ticket_priorities` ORDER BY priority ";
        $jsst_jsonColorList = "[";
        foreach(jssupportticket::$_db->get_results($jsst_query) as $jsst_priority){
            $jsst_jsonColorList.= "'".$jsst_priority->prioritycolour."',";
        }
        $jsst_jsonColorList .= "]";
        jssupportticket::$jsst_data['priorityColorList'] = $jsst_jsonColorList;
        //end priority colors
    }

    function getDepartmentReportsFE(){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
        $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);
        $jsst_query = "SELECT dept.departmentname,(SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE departmentid = dept.id ) AS totalticket
            FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept
            JOIN `".jssupportticket::$_db->prefix."js_ticket_acl_user_access_departments` AS acl ON acl.departmentid = dept.id
            WHERE acl.staffid = ".esc_sql($jsst_staffid)." AND dept.status=1";

        $jsst_department = jssupportticket::$_db->get_results($jsst_query);
        jssupportticket::$jsst_data['pie3d_chart1'] = "";
        $jsst_i = 0;
        foreach($jsst_department AS $jsst_dept){
            if($jsst_dept->totalticket == 0)
                $jsst_i += 1;
            jssupportticket::$jsst_data['pie3d_chart1'] .= "['".$jsst_dept->departmentname."',$jsst_dept->totalticket],";
        }

        if(count($jsst_department) == $jsst_i)
            jssupportticket::$jsst_data['pie3d_chart1'] = '';

        // pagination
        $jsst_query = "SELECT count(dept.id)
            FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept
            JOIN `".jssupportticket::$_db->prefix."js_ticket_acl_user_access_departments` AS acl ON acl.departmentid = dept.id
            WHERE acl.staffid = ".esc_sql($jsst_staffid)." AND dept.status=1";
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        $jsst_query = "SELECT dept.departmentname,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND departmentid = dept.id) AS openticket,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE status = 5 AND departmentid = dept.id) AS closeticket,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND departmentid = dept.id) AS answeredticket,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND departmentid = dept.id) AS overdueticket,
            (SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND departmentid = dept.id) AS pendingticket
            FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS dept
            JOIN `".jssupportticket::$_db->prefix."js_ticket_acl_user_access_departments` AS acl ON acl.departmentid = dept.id
            WHERE acl.staffid = ".esc_sql($jsst_staffid)." AND dept.status=1";
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $jsst_departments = jssupportticket::$_db->get_results($jsst_query);
        jssupportticket::$jsst_data['departments_report'] = $jsst_departments;

        return;
    }

    function getStaffReports(){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        $jsst_date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $jsst_date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';

        if(isset($jsst_date_start) && $jsst_date_start != ""){
            $jsst_date_start = date_i18n('Y-m-d',strtotime($jsst_date_start));
        }
        if(isset($jsst_date_end) && $jsst_date_end != ""){
            $jsst_date_end = date_i18n('Y-m-d',strtotime($jsst_date_end));
        }
        if($jsst_date_start > $jsst_date_end){
            $jsst_tmp = $jsst_date_start;
            $jsst_date_start = $jsst_date_end;
            $jsst_date_end = $jsst_tmp;
        }

        $jsst_uid = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['uid'] : '';

        //Line Chart Data
        $jsst_curdate = ($jsst_date_start != null) ? date_i18n('Y-m-d',strtotime($jsst_date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $jsst_dates = '';
        $jsst_fromdate = ($jsst_date_end != null) ? date_i18n('Y-m-d',strtotime($jsst_date_end)) : date_i18n('Y-m-d');
        jssupportticket::$jsst_data['filter']['date_start'] = $jsst_curdate;
        jssupportticket::$jsst_data['filter']['date_end'] = $jsst_fromdate;
        jssupportticket::$jsst_data['filter']['uid'] = $jsst_uid;
        // forexport
        $_SESSION['forexport']['curdate'] = $jsst_curdate;
        $_SESSION['forexport']['fromdate'] = $jsst_fromdate;
        $_SESSION['forexport']['uid'] = $jsst_uid;

        $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);
        jssupportticket::$jsst_data['filter']['staffname'] = JSSTincluder::getJSModel('agent')->getMyName($jsst_staffid);
        $jsst_nextdate = $jsst_fromdate;
        //Query to get Data
        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` ";
        if($jsst_uid) $jsst_query .= " WHERE staffid = ".esc_sql($jsst_staffid);
        $jsst_allticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND staffid = ".esc_sql($jsst_staffid);
        $jsst_openticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND staffid = ".esc_sql($jsst_staffid);
        $jsst_closeticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND staffid = ".esc_sql($jsst_staffid);
        $jsst_answeredticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND staffid = ".esc_sql($jsst_staffid);
        $jsst_overdueticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND staffid = ".esc_sql($jsst_staffid);
        $jsst_pendingticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_date_openticket = array();
        $jsst_date_closeticket = array();
        $jsst_date_answeredticket = array();
        $jsst_date_overdueticket = array();
        $jsst_date_pendingticket = array();
        foreach ($jsst_openticket AS $jsst_ticket) {
            if (!isset($jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_closeticket AS $jsst_ticket) {
            if (!isset($jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_answeredticket AS $jsst_ticket) {
            if (!isset($jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_overdueticket AS $jsst_ticket) {
            if (!isset($jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_pendingticket AS $jsst_ticket) {
            if (!isset($jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_json_array = "";
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_overdue_ticket = 0;
        $jsst_pending_ticket = 0;

        do{
            $jsst_year = date_i18n('Y',strtotime($jsst_nextdate));
            $jsst_month = date_i18n('m',strtotime($jsst_nextdate));
            $jsst_month = $jsst_month - 1; //js month are 0 based
            $jsst_day = date_i18n('d',strtotime($jsst_nextdate));
            $jsst_openticket_tmp = isset($jsst_date_openticket[$jsst_nextdate]) ? $jsst_date_openticket[$jsst_nextdate]  : 0;
            $jsst_closeticket_tmp = isset($jsst_date_closeticket[$jsst_nextdate]) ? $jsst_date_closeticket[$jsst_nextdate] : 0;
            $jsst_answeredticket_tmp = isset($jsst_date_answeredticket[$jsst_nextdate]) ? $jsst_date_answeredticket[$jsst_nextdate] : 0;
            $jsst_overdueticket_tmp = isset($jsst_date_overdueticket[$jsst_nextdate]) ? $jsst_date_overdueticket[$jsst_nextdate] : 0;
            $jsst_pendingticket_tmp = isset($jsst_date_pendingticket[$jsst_nextdate]) ? $jsst_date_pendingticket[$jsst_nextdate] : 0;
            $jsst_json_array .= "[new Date($jsst_year,$jsst_month,$jsst_day),$jsst_openticket_tmp,$jsst_answeredticket_tmp,$jsst_pendingticket_tmp,$jsst_overdueticket_tmp,$jsst_closeticket_tmp],";
            $jsst_open_ticket += $jsst_openticket_tmp;
            $jsst_close_ticket += $jsst_closeticket_tmp;
            $jsst_answered_ticket += $jsst_answeredticket_tmp;
            $jsst_overdue_ticket += $jsst_overdueticket_tmp;
            $jsst_pending_ticket += $jsst_pendingticket_tmp;
            if($jsst_nextdate == $jsst_curdate){
                break;
            }
                $jsst_nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_nextdate . " -1 days"));
        }while($jsst_nextdate != $jsst_curdate);

        jssupportticket::$jsst_data['ticket_total']['allticket'] = $jsst_allticket;
        jssupportticket::$jsst_data['ticket_total']['openticket'] = $jsst_open_ticket;
        jssupportticket::$jsst_data['ticket_total']['closeticket'] = $jsst_close_ticket;
        jssupportticket::$jsst_data['ticket_total']['answeredticket'] = $jsst_answered_ticket;
        jssupportticket::$jsst_data['ticket_total']['overdueticket'] = $jsst_overdue_ticket;
        jssupportticket::$jsst_data['ticket_total']['pendingticket'] = $jsst_pending_ticket;

        jssupportticket::$jsst_data['line_chart_json_array'] = $jsst_json_array;

        // Pagination
        $jsst_query = "SELECT count(staff.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
                    JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid";
        if($jsst_uid) $jsst_query .= ' WHERE staff.uid = '.esc_sql($jsst_uid);
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total , 'staffreports');

        $jsst_query = "SELECT staff.photo,staff.id,staff.firstname,staff.lastname,staff.username,staff.email,user.display_name,user.user_email,user.user_nicename,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS pendingticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS allticket  ";
                    if(in_array('feedback', jssupportticket::$_active_addons)){
                        $jsst_query .=    ",(SELECT AVG(feed.rating) FROM `" . jssupportticket::$_db->prefix . "js_ticket_feedbacks` AS feed JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON ticket.id= feed.ticketid WHERE date(feed.created) >= '" . esc_sql($jsst_curdate) . "' AND date(feed.created) <= '" . esc_sql($jsst_fromdate) . "' AND ticket.staffid = staff.id) AS avragerating ";
                    }
                    $jsst_query .=  "FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
                    JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid";
        if($jsst_uid) $jsst_query .= ' WHERE staff.uid = '.esc_sql($jsst_uid);
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $jsst_agents = jssupportticket::$_db->get_results($jsst_query);
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach ($jsst_agents as $jsst_agent) {
                $jsst_agent->time = JSSTincluder::getJSModel('timetracking')->getAverageTimeByStaffId($jsst_agent->id);// time 0 contains avergage time in seconds and 1 contains wheter it is conflicted or not
            }
        }
        jssupportticket::$jsst_data['staffs_report'] = $jsst_agents;
        return;
    }

    function getDepartmentReports(){
        $jsst_date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $jsst_date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';
        if(isset($jsst_date_start) && $jsst_date_start != ""){
            $jsst_date_start = date_i18n('Y-m-d',strtotime($jsst_date_start));
        }
        if(isset($jsst_date_end) && $jsst_date_end != ""){
            $jsst_date_end = date_i18n('Y-m-d',strtotime($jsst_date_end));
        }
        if($jsst_date_start > $jsst_date_end){
            $jsst_tmp = $jsst_date_start;
            $jsst_date_start = $jsst_date_end;
            $jsst_date_end = $jsst_tmp;
        }

        //Line Chart Data
        $jsst_curdate = ($jsst_date_start != null) ? date_i18n('Y-m-d',strtotime($jsst_date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $jsst_dates = '';
        $jsst_fromdate = ($jsst_date_end != null) ? date_i18n('Y-m-d',strtotime($jsst_date_end)) : date_i18n('Y-m-d');
        jssupportticket::$jsst_data['filter']['date_start'] = $jsst_curdate;
        jssupportticket::$jsst_data['filter']['date_end'] = $jsst_fromdate;
        // forexport
        $_SESSION['forexport']['curdate'] = $jsst_curdate;
        $_SESSION['forexport']['fromdate'] = $jsst_fromdate;

        $jsst_nextdate = $jsst_fromdate;
        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_openticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_closeticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_answeredticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_overdueticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_pendingticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_date_openticket = array();
        $jsst_date_closeticket = array();
        $jsst_date_answeredticket = array();
        $jsst_date_overdueticket = array();
        $jsst_date_pendingticket = array();
        foreach ($jsst_openticket AS $jsst_ticket) {
            if (!isset($jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_closeticket AS $jsst_ticket) {
            if (!isset($jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_answeredticket AS $jsst_ticket) {
            if (!isset($jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_overdueticket AS $jsst_ticket) {
            if (!isset($jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_pendingticket AS $jsst_ticket) {
            if (!isset($jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_json_array = "";
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_overdue_ticket = 0;
        $jsst_pending_ticket = 0;

        do{
            $jsst_year = date_i18n('Y',strtotime($jsst_nextdate));
            $jsst_month = date_i18n('m',strtotime($jsst_nextdate));
            $jsst_month = $jsst_month - 1; //js month are 0 based
            $jsst_day = date_i18n('d',strtotime($jsst_nextdate));
            $jsst_openticket_tmp = isset($jsst_date_openticket[$jsst_nextdate]) ? $jsst_date_openticket[$jsst_nextdate]  : 0;
            $jsst_closeticket_tmp = isset($jsst_date_closeticket[$jsst_nextdate]) ? $jsst_date_closeticket[$jsst_nextdate] : 0;
            $jsst_answeredticket_tmp = isset($jsst_date_answeredticket[$jsst_nextdate]) ? $jsst_date_answeredticket[$jsst_nextdate] : 0;
            $jsst_overdueticket_tmp = isset($jsst_date_overdueticket[$jsst_nextdate]) ? $jsst_date_overdueticket[$jsst_nextdate] : 0;
            $jsst_pendingticket_tmp = isset($jsst_date_pendingticket[$jsst_nextdate]) ? $jsst_date_pendingticket[$jsst_nextdate] : 0;
            $jsst_json_array .= "[new Date($jsst_year,$jsst_month,$jsst_day),$jsst_openticket_tmp,$jsst_answeredticket_tmp,$jsst_pendingticket_tmp,$jsst_overdueticket_tmp,$jsst_closeticket_tmp],";
            $jsst_open_ticket += $jsst_openticket_tmp;
            $jsst_close_ticket += $jsst_closeticket_tmp;
            $jsst_answered_ticket += $jsst_answeredticket_tmp;
            $jsst_overdue_ticket += $jsst_overdueticket_tmp;
            $jsst_pending_ticket += $jsst_pendingticket_tmp;
             if($jsst_nextdate == $jsst_curdate){
                break;
            }
            $jsst_nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_nextdate . " -1 days"));
        }while($jsst_nextdate != $jsst_curdate);

        jssupportticket::$jsst_data['ticket_total']['openticket'] = $jsst_open_ticket;
        jssupportticket::$jsst_data['ticket_total']['closeticket'] = $jsst_close_ticket;
        jssupportticket::$jsst_data['ticket_total']['answeredticket'] = $jsst_answered_ticket;
        jssupportticket::$jsst_data['ticket_total']['overdueticket'] = $jsst_overdue_ticket;
        jssupportticket::$jsst_data['ticket_total']['pendingticket'] = $jsst_pending_ticket;

        jssupportticket::$jsst_data['line_chart_json_array'] = $jsst_json_array;

        // Pagination
        $jsst_query = "SELECT count(department.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS department
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_email` AS email ON department.emailid = email.id";
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        $jsst_query = "SELECT department.id,department.departmentname,email.email,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE departmentid = department.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS pendingticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS department
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_email` AS email ON department.emailid = email.id";
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $jsst_depatments = jssupportticket::$_db->get_results($jsst_query);
        jssupportticket::$jsst_data['depatments_report'] =$jsst_depatments;
        return;
    }

    function getStaffReportsFE(){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        $jsst_date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['jsst-date-start'] : '';
        $jsst_date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['jsst-date-end'] : '';
        if(isset($jsst_date_start) && $jsst_date_start != ""){
            $jsst_date_start = date_i18n('Y-m-d',strtotime($jsst_date_start));
        }
        if(isset($jsst_date_end) && $jsst_date_end != ""){
            $jsst_date_end = date_i18n('Y-m-d',strtotime($jsst_date_end));
        }

        if($jsst_date_start > $jsst_date_end){
            $jsst_tmp = $jsst_date_start;
            $jsst_date_start = $jsst_date_end;
            $jsst_date_end = $jsst_tmp;
        }

        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();

        //Line Chart Data
        $jsst_curdate = ($jsst_date_start != null) ? date_i18n('Y-m-d',strtotime($jsst_date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $jsst_dates = '';
        $jsst_fromdate = ($jsst_date_end != null) ? date_i18n('Y-m-d',strtotime($jsst_date_end)) : date_i18n('Y-m-d');
        jssupportticket::$jsst_data['filter']['jsst-date-start'] = $jsst_curdate;
        jssupportticket::$jsst_data['filter']['jsst-date-end'] = $jsst_fromdate;

        $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);

        jssupportticket::$jsst_data['filter']['staffname'] = JSSTincluder::getJSModel('agent')->getMyName($jsst_staffid);
        $jsst_nextdate = $jsst_fromdate;
        // find my depats
        $jsst_query = "SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ". esc_sql($jsst_staffid);
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        $jsst_my_depts = '';
        foreach ($jsst_data as $jsst_key => $jsst_value) {
            if($jsst_my_depts)
                $jsst_my_depts .= ',';
            $jsst_my_depts .= $jsst_value->departmentid;
        }
        // get mytickets, or all tickets with my depatments
        if($jsst_my_depts)
            $jsst_dep_query = " AND (ticket.staffid = $jsst_staffid OR ticket.departmentid IN ($jsst_my_depts)) ";
        else
            $jsst_dep_query = " AND ( ticket.staffid = $jsst_staffid ) ";
        //Query to get Data
        $jsst_query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status = 1 AND (ticket.lastreply = '0000-00-00 00:00:00') AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_query .= $jsst_dep_query;
        $jsst_openticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status = 5 AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_query .= $jsst_dep_query;
        $jsst_closeticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isanswered = 1 AND ticket.status != 5 AND ticket.status != 1 AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_query .= $jsst_dep_query;
        $jsst_answeredticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isoverdue = 1 AND ticket.status != 5 AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_query .= $jsst_dep_query;
        $jsst_overdueticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT ticket.created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isanswered != 1 AND ticket.status != 5 AND (ticket.lastreply != '0000-00-00 00:00:00') AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "'";
        $jsst_query .= $jsst_dep_query;
        $jsst_pendingticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_date_openticket = array();
        $jsst_date_closeticket = array();
        $jsst_date_answeredticket = array();
        $jsst_date_overdueticket = array();
        $jsst_date_pendingticket = array();
        foreach ($jsst_openticket AS $jsst_ticket) {
            if (!isset($jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_closeticket AS $jsst_ticket) {
            if (!isset($jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_answeredticket AS $jsst_ticket) {
            if (!isset($jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_overdueticket AS $jsst_ticket) {
            if (!isset($jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_pendingticket AS $jsst_ticket) {
            if (!isset($jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_json_array = "";
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_overdue_ticket = 0;
        $jsst_pending_ticket = 0;

        do{
            $jsst_year = date_i18n('Y',strtotime($jsst_nextdate));
            $jsst_month = date_i18n('m',strtotime($jsst_nextdate));
            $jsst_month = $jsst_month - 1; //js month are 0 based
            $jsst_day = date_i18n('d',strtotime($jsst_nextdate));
            $jsst_openticket_tmp = isset($jsst_date_openticket[$jsst_nextdate]) ? $jsst_date_openticket[$jsst_nextdate]  : 0;
            $jsst_closeticket_tmp = isset($jsst_date_closeticket[$jsst_nextdate]) ? $jsst_date_closeticket[$jsst_nextdate] : 0;
            $jsst_answeredticket_tmp = isset($jsst_date_answeredticket[$jsst_nextdate]) ? $jsst_date_answeredticket[$jsst_nextdate] : 0;
            $jsst_overdueticket_tmp = isset($jsst_date_overdueticket[$jsst_nextdate]) ? $jsst_date_overdueticket[$jsst_nextdate] : 0;
            $jsst_pendingticket_tmp = isset($jsst_date_pendingticket[$jsst_nextdate]) ? $jsst_date_pendingticket[$jsst_nextdate] : 0;
            $jsst_json_array .= "[new Date($jsst_year,$jsst_month,$jsst_day),$jsst_openticket_tmp,$jsst_answeredticket_tmp,$jsst_pendingticket_tmp,$jsst_overdueticket_tmp,$jsst_closeticket_tmp],";
            $jsst_open_ticket += $jsst_openticket_tmp;
            $jsst_close_ticket += $jsst_closeticket_tmp;
            $jsst_answered_ticket += $jsst_answeredticket_tmp;
            $jsst_overdue_ticket += $jsst_overdueticket_tmp;
            $jsst_pending_ticket += $jsst_pendingticket_tmp;
             if($jsst_nextdate == $jsst_curdate){
                break;
            }
            $jsst_nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_nextdate . " -1 days"));
        }while($jsst_nextdate != $jsst_curdate);

        jssupportticket::$jsst_data['ticket_total']['openticket'] = $jsst_open_ticket;
        jssupportticket::$jsst_data['ticket_total']['closeticket'] = $jsst_close_ticket;
        jssupportticket::$jsst_data['ticket_total']['answeredticket'] = $jsst_answered_ticket;
        jssupportticket::$jsst_data['ticket_total']['overdueticket'] = $jsst_overdue_ticket;
        jssupportticket::$jsst_data['ticket_total']['pendingticket'] = $jsst_pending_ticket;

        jssupportticket::$jsst_data['line_chart_json_array'] = $jsst_json_array;

        // Pagination staffs listing
        $jsst_query = "SELECT COUNT(DISTINCT staff.id)
            FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
            JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid
            LEFT JOIN `".jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dep ON dep.staffid = staff.id ";
        $jsst_query .= " WHERE (staff.id = ".esc_sql($jsst_staffid)." OR dep.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($jsst_staffid)."))";
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);
        // data
        $jsst_query = "SELECT DISTINCT staff.photo,staff.id,staff.firstname,staff.lastname,staff.username,staff.email,user.display_name,user.user_email,user.user_nicename,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status = 1 AND (ticket.lastreply = '0000-00-00 00:00:00') AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' AND ticket.staffid = staff.id) AS openticket,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status = 5 AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' AND ticket.staffid = staff.id) AS closeticket,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isanswered = 1 AND ticket.status != 5 AND ticket.status != 1 AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' AND ticket.staffid = staff.id) AS answeredticket,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isoverdue = 1 AND ticket.status != 5 AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' AND ticket.staffid = staff.id) AS overdueticket,
            (SELECT COUNT(ticket.id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.isanswered != 1 AND ticket.status != 5 AND (ticket.lastreply != '0000-00-00 00:00:00') AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' AND ticket.staffid = staff.id) AS pendingticket
            FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
            JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid
            LEFT JOIN `".jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dep ON dep.staffid = staff.id";
        $jsst_query .= " WHERE (staff.id = ".esc_sql($jsst_staffid)." OR dep.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($jsst_staffid)."))";
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $jsst_agents = jssupportticket::$_db->get_results($jsst_query);
        jssupportticket::$jsst_data['staffs_report'] = $jsst_agents;
        return;
    }

    function isValidStaffid($jsst_staffid){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return false;
        }

        if( ! is_numeric($jsst_staffid))
            return false;
        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
        $jsst_id = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);
        if( $jsst_id == $jsst_staffid )
            return true;
        $jsst_query = "SELECT staff.id AS staffid
            FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
            JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid
            JOIN `".jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dep ON dep.staffid = staff.id ";
        $jsst_query .= " WHERE (dep.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($jsst_id)."))";
        $jsst_result = jssupportticket::$_db->get_results($jsst_query);
        foreach ($jsst_result as $jsst_agent) {
            if($jsst_agent->staffid == $jsst_staffid)
                return true;
        }
        return false;
    }

    function getUserReports(){
        $jsst_date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $jsst_date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';
        if(isset($jsst_date_start) && $jsst_date_start != ""){
            $jsst_date_start = date_i18n('Y-m-d',strtotime($jsst_date_start));
        }
        if(isset($jsst_date_end) && $jsst_date_end != ""){
            $jsst_date_end = date_i18n('Y-m-d',strtotime($jsst_date_end));
        }
        if($jsst_date_start > $jsst_date_end){
            $jsst_tmp = $jsst_date_start;
            $jsst_date_start = $jsst_date_end;
            $jsst_date_end = $jsst_tmp;
        }
        $jsst_uid = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['uid'] : '';

        //Line Chart Data
        $jsst_curdate = ($jsst_date_start != null) ? date_i18n('Y-m-d',strtotime($jsst_date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $jsst_dates = '';
        $jsst_fromdate = ($jsst_date_end != null) ? date_i18n('Y-m-d',strtotime($jsst_date_end)) : date_i18n('Y-m-d');
        jssupportticket::$jsst_data['filter']['date_start'] = $jsst_curdate;
        jssupportticket::$jsst_data['filter']['date_end'] = $jsst_fromdate;
        jssupportticket::$jsst_data['filter']['uid'] = $jsst_uid;

        // forexport
        $_SESSION['forexport']['curdate'] = $jsst_curdate;
        $_SESSION['forexport']['fromdate'] = $jsst_fromdate;
        $_SESSION['forexport']['uid'] = $jsst_uid;

        jssupportticket::$jsst_data['filter']['username'] = JSSTincluder::getJSModel('jssupportticket')->getUserNameById($jsst_uid);
        $jsst_nextdate = $jsst_fromdate;
        //Query to get Data
        $jsst_query = "SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` ";
        if($jsst_uid) $jsst_query .= " WHERE  uid = ".esc_sql($jsst_uid);
        $jsst_allticket = jssupportticket::$_db->get_var($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1  AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND uid = ".esc_sql($jsst_uid);
        $jsst_openticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND uid = ".esc_sql($jsst_uid);
        $jsst_closeticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND uid = ".esc_sql($jsst_uid);
        $jsst_answeredticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND uid = ". esc_sql($jsst_uid);
        $jsst_overdueticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_uid) $jsst_query .= " AND uid = ".esc_sql($jsst_uid);
        $jsst_pendingticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_date_openticket = array();
        $jsst_date_closeticket = array();
        $jsst_date_answeredticket = array();
        $jsst_date_overdueticket = array();
        $jsst_date_pendingticket = array();
        foreach ($jsst_openticket AS $jsst_ticket) {
            if (!isset($jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_closeticket AS $jsst_ticket) {
            if (!isset($jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_answeredticket AS $jsst_ticket) {
            if (!isset($jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_overdueticket AS $jsst_ticket) {
            if (!isset($jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_pendingticket AS $jsst_ticket) {
            if (!isset($jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_overdue_ticket = 0;
        $jsst_pending_ticket = 0;
        $jsst_json_array = "";
        do{
            $jsst_year = date_i18n('Y',strtotime($jsst_nextdate));
            $jsst_month = date_i18n('m',strtotime($jsst_nextdate));
            $jsst_month = $jsst_month - 1; //js month are 0 based
            $jsst_day = date_i18n('d',strtotime($jsst_nextdate));
            $jsst_openticket_tmp = isset($jsst_date_openticket[$jsst_nextdate]) ? $jsst_date_openticket[$jsst_nextdate]  : 0;
            $jsst_closeticket_tmp = isset($jsst_date_closeticket[$jsst_nextdate]) ? $jsst_date_closeticket[$jsst_nextdate] : 0;
            $jsst_answeredticket_tmp = isset($jsst_date_answeredticket[$jsst_nextdate]) ? $jsst_date_answeredticket[$jsst_nextdate] : 0;
            $jsst_overdueticket_tmp = isset($jsst_date_overdueticket[$jsst_nextdate]) ? $jsst_date_overdueticket[$jsst_nextdate] : 0;
            $jsst_pendingticket_tmp = isset($jsst_date_pendingticket[$jsst_nextdate]) ? $jsst_date_pendingticket[$jsst_nextdate] : 0;
            $jsst_json_array .= "[new Date($jsst_year,$jsst_month,$jsst_day),$jsst_openticket_tmp,$jsst_answeredticket_tmp,$jsst_pendingticket_tmp,$jsst_overdueticket_tmp,$jsst_closeticket_tmp],";
            $jsst_open_ticket += $jsst_openticket_tmp;
            $jsst_close_ticket += $jsst_closeticket_tmp;
            $jsst_answered_ticket += $jsst_answeredticket_tmp;
            $jsst_overdue_ticket += $jsst_overdueticket_tmp;
            $jsst_pending_ticket += $jsst_pendingticket_tmp;
             if($jsst_nextdate == $jsst_curdate){
                break;
            }
            $jsst_nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_nextdate . " -1 days"));
        }while($jsst_nextdate != $jsst_curdate);

        jssupportticket::$jsst_data['ticket_total']['allticket'] = $jsst_allticket;
        jssupportticket::$jsst_data['ticket_total']['openticket'] = $jsst_open_ticket;
        jssupportticket::$jsst_data['ticket_total']['closeticket'] = $jsst_close_ticket;
        jssupportticket::$jsst_data['ticket_total']['answeredticket'] = $jsst_answered_ticket;
        jssupportticket::$jsst_data['ticket_total']['overdueticket'] = $jsst_overdue_ticket;
        jssupportticket::$jsst_data['ticket_total']['pendingticket'] = $jsst_pending_ticket;

        jssupportticket::$jsst_data['line_chart_json_array'] = $jsst_json_array;

        // Pagination
        $jsst_query = "SELECT COUNT(user.id)
                    FROM `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user
                    WHERE user.wpuid != 0 AND ";
                    if(in_array('agent', jssupportticket::$_active_addons)){
                        $jsst_query .=" NOT EXISTS (SELECT id FROM `".jssupportticket::$_db->prefix."js_ticket_staff` WHERE uid = user.id) AND  ";
                    }
                    $jsst_query .=" NOT EXISTS (SELECT umeta_id FROM `".jssupportticket::$_wpprefixforuser."usermeta` WHERE user_id = user.id AND meta_value LIKE '%administrator%')";
        if($jsst_uid) $jsst_query .= " AND user.id = ".esc_sql($jsst_uid);
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        $jsst_query = "SELECT user.display_name,user.user_email,user.user_nicename,user.id,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = user.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS pendingticket
                    FROM `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user
                    WHERE user.wpuid != 0 AND ";
                    if(in_array('agent', jssupportticket::$_active_addons)){
                        $jsst_query .=" NOT EXISTS (SELECT id FROM `".jssupportticket::$_db->prefix."js_ticket_staff` WHERE uid = user.id) AND  ";
                    }
                    $jsst_query .=" NOT EXISTS (SELECT umeta_id FROM `".jssupportticket::$_wpprefixforuser."usermeta` WHERE user_id = user.id AND meta_value LIKE '%administrator%')";
        if($jsst_uid) $jsst_query .= " AND user.id = ".esc_sql($jsst_uid);
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        $jsst_users = jssupportticket::$_db->get_results($jsst_query);
        jssupportticket::$jsst_data['users_report'] =$jsst_users;
        return;
    }

    function getStaffDetailReportByStaffId($jsst_id){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        if(!is_numeric($jsst_id)) return false;

        if( ! is_admin()){
            $jsst_result = $this->isValidStaffid( $jsst_id );
            if( $jsst_result == false)
                return false;
        }

        $jsst_start_date = is_admin() ? 'date_start' : 'jsst-date-start';
        $jsst_end_date = is_admin() ? 'date_end' : 'jsst-date-end';
        if(isset($jsst_date_start) && $jsst_date_start != ""){
            $jsst_date_start = date_i18n('Y-m-d',strtotime($jsst_date_start));
        }
        if(isset($jsst_date_end) && $jsst_date_end != ""){
            $jsst_date_end = date_i18n('Y-m-d',strtotime($jsst_date_end));
        }
        $jsst_date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report'][$jsst_start_date] : '';
        $jsst_date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report'][$jsst_end_date] : '';
        if(isset($jsst_date_start) && $jsst_date_start != ""){
            $jsst_date_start = date_i18n('Y-m-d',strtotime($jsst_date_start));
        }
        if(isset($jsst_date_end) && $jsst_date_end != ""){
            $jsst_date_end = date_i18n('Y-m-d',strtotime($jsst_date_end));
        }
        if($jsst_date_start > $jsst_date_end){
            $jsst_tmp = $jsst_date_start;
            $jsst_date_start = $jsst_date_end;
            $jsst_date_end = $jsst_tmp;
        }

        //Line Chart Data
        $jsst_curdate = ($jsst_date_start != null) ? date_i18n('Y-m-d',strtotime($jsst_date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $jsst_fromdate = ($jsst_date_end != null) ? date_i18n('Y-m-d',strtotime($jsst_date_end)) : date_i18n('Y-m-d');
        jssupportticket::$jsst_data['filter'][$jsst_start_date] = $jsst_curdate;
        jssupportticket::$jsst_data['filter'][$jsst_end_date] = $jsst_fromdate;
        jssupportticket::$jsst_data['filter']['uid'] = $jsst_id;

        $jsst_nextdate = $jsst_fromdate;

        //Query to get Data
        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND staffid = ".esc_sql($jsst_id);
        $jsst_openticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND staffid = ".esc_sql($jsst_id);
        $jsst_closeticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND staffid = ".esc_sql($jsst_id);
        $jsst_answeredticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND staffid = ".esc_sql($jsst_id);
        $jsst_overdueticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND staffid = ".esc_sql($jsst_id);
        $jsst_pendingticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_date_openticket = array();
        $jsst_date_closeticket = array();
        $jsst_date_answeredticket = array();
        $jsst_date_overdueticket = array();
        $jsst_date_pendingticket = array();
        foreach ($jsst_openticket AS $jsst_ticket) {
            if (!isset($jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_closeticket AS $jsst_ticket) {
            if (!isset($jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_answeredticket AS $jsst_ticket) {
            if (!isset($jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_overdueticket AS $jsst_ticket) {
            if (!isset($jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_pendingticket AS $jsst_ticket) {
            if (!isset($jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_overdue_ticket = 0;
        $jsst_pending_ticket = 0;
        $jsst_json_array = "";
        do{
            $jsst_year = date_i18n('Y',strtotime($jsst_nextdate));
            $jsst_month = date_i18n('m',strtotime($jsst_nextdate));
            $jsst_month = $jsst_month - 1; //js month are 0 based
            $jsst_day = date_i18n('d',strtotime($jsst_nextdate));
            $jsst_openticket_tmp = isset($jsst_date_openticket[$jsst_nextdate]) ? $jsst_date_openticket[$jsst_nextdate]  : 0;
            $jsst_closeticket_tmp = isset($jsst_date_closeticket[$jsst_nextdate]) ? $jsst_date_closeticket[$jsst_nextdate] : 0;
            $jsst_answeredticket_tmp = isset($jsst_date_answeredticket[$jsst_nextdate]) ? $jsst_date_answeredticket[$jsst_nextdate] : 0;
            $jsst_overdueticket_tmp = isset($jsst_date_overdueticket[$jsst_nextdate]) ? $jsst_date_overdueticket[$jsst_nextdate] : 0;
            $jsst_pendingticket_tmp = isset($jsst_date_pendingticket[$jsst_nextdate]) ? $jsst_date_pendingticket[$jsst_nextdate] : 0;
            $jsst_json_array .= "[new Date($jsst_year,$jsst_month,$jsst_day),$jsst_openticket_tmp,$jsst_answeredticket_tmp,$jsst_pendingticket_tmp,$jsst_overdueticket_tmp,$jsst_closeticket_tmp],";
            $jsst_open_ticket += $jsst_openticket_tmp;
            $jsst_close_ticket += $jsst_closeticket_tmp;
            $jsst_answered_ticket += $jsst_answeredticket_tmp;
            $jsst_overdue_ticket += $jsst_overdueticket_tmp;
            $jsst_pending_ticket += $jsst_pendingticket_tmp;
             if($jsst_nextdate == $jsst_curdate){
                break;
            }
            $jsst_nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_nextdate . " -1 days"));
        }while($jsst_nextdate != $jsst_curdate);

        jssupportticket::$jsst_data['line_chart_json_array'] = $jsst_json_array;

        $jsst_query = "SELECT staff.photo,staff.id,staff.firstname,staff.lastname,staff.username,staff.email,user.display_name,user.user_email,user.user_nicename,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status !=  5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND staffid = staff.id) AS pendingticket   ";
                    if(in_array('feedback', jssupportticket::$_active_addons)){
                        $jsst_query .=    ",(SELECT AVG(feed.rating) FROM `" . jssupportticket::$_db->prefix . "js_ticket_feedbacks` AS feed JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON ticket.id= feed.ticketid WHERE date(feed.created) >= '" . esc_sql($jsst_curdate) . "' AND date(feed.created) <= '" . esc_sql($jsst_fromdate) . "' AND ticket.staffid = staff.id) AS avragerating ";
                    }
                    $jsst_query .=  "FROM `".jssupportticket::$_db->prefix."js_ticket_staff` AS staff
                    JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = staff.uid
                    WHERE staff.id = ".esc_sql($jsst_id);

        $jsst_agent = jssupportticket::$_db->get_row($jsst_query);
        if(!empty($jsst_agent)){
            if(in_array('timetracking', jssupportticket::$_active_addons)){
                $jsst_agent->time = JSSTincluder::getJSModel('timetracking')->getAverageTimeByStaffId($jsst_agent->id);// time 0 contains avergage time in seconds and 1 contains wheter it is conflicted or not
            }
        }

        jssupportticket::$jsst_data['staff_report'] =$jsst_agent;
        // ticket ids for staff member on which he replied but are not assigned to him
        $jsst_ticketid_string = '';
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            $jsst_query = "SELECT DISTINCT(ticketid) AS ticketid
                        FROM `".jssupportticket::$_db->prefix."js_ticket_staff_time`
                        WHERE staffid = ".esc_sql($jsst_id);
            $jsst_all_tickets = jssupportticket::$_db->get_results($jsst_query);
            $jsst_comma = '';
            foreach ($jsst_all_tickets as $jsst_ticket) {
                $jsst_ticketid_string .= $jsst_comma.$jsst_ticket->ticketid;
                $jsst_comma = ', ';
            }
        }

        if($jsst_ticketid_string == ''){
            $jsst_q_strig = "(staffid = ".esc_sql($jsst_id).")";
        }else{
            $jsst_q_strig = "(staffid = ".esc_sql($jsst_id)." OR ticket.id IN (".esc_sql($jsst_ticketid_string)."))";
        }

        // Pagination
        $jsst_query = "SELECT COUNT(ticket.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    WHERE ".esc_sql($jsst_q_strig)." AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' ";
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total,'staffdetailreport');

        //Tickets
        do_action('jsstFeedbackQueryStaff');
        $jsst_query = "SELECT ticket.*,priority.priority, priority.prioritycolour,status.status AS statustitle,status.statuscolour,status.statusbgcolour ".jssupportticket::$_addon_query['select']."
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    ".jssupportticket::$_addon_query['join']."
                    WHERE ".esc_sql($jsst_q_strig)." AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' ";
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        do_action('jsst_reset_aadon_query');
        jssupportticket::$jsst_data['staff_tickets'] = jssupportticket::$_db->get_results($jsst_query);

        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach (jssupportticket::$jsst_data['staff_tickets'] as $jsst_ticket) {
                 //$jsst_ticket->time = JSSTincluder::getJSModel('agent')->getTimeTakenByTicketId($jsst_ticket->id);
                 $jsst_ticket->time = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketIdAndStaffid($jsst_ticket->id,$jsst_id);// second parameter is staff id
            }
        }
        return;
    }

    function getDepartmentDetailReportByDepartmentId($jsst_id){
        if(!is_numeric($jsst_id)) return false;

        $jsst_start_date ='date_start';
        $jsst_end_date ='date_end';

        $jsst_date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $jsst_date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';
        if(isset($jsst_date_start) && $jsst_date_start != ""){
            $jsst_date_start = date_i18n('Y-m-d',strtotime($jsst_date_start));
        }
        if(isset($jsst_date_end) && $jsst_date_end != ""){
            $jsst_date_end = date_i18n('Y-m-d',strtotime($jsst_date_end));
        }
        if($jsst_date_start > $jsst_date_end){
            $jsst_tmp = $jsst_date_start;
            $jsst_date_start = $jsst_date_end;
            $jsst_date_end = $jsst_tmp;
        }

        //Line Chart Data
        $jsst_curdate = ($jsst_date_start != null) ? date_i18n('Y-m-d',strtotime($jsst_date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $jsst_fromdate = ($jsst_date_end != null) ? date_i18n('Y-m-d',strtotime($jsst_date_end)) : date_i18n('Y-m-d');
        jssupportticket::$jsst_data['filter'][$jsst_start_date] = $jsst_curdate;
        jssupportticket::$jsst_data['filter'][$jsst_end_date] = $jsst_fromdate;
        jssupportticket::$jsst_data['filter']['id'] = $jsst_id;

        $jsst_nextdate = $jsst_fromdate;

        //Query to get Data
        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND departmentid = ".esc_sql($jsst_id);
        $jsst_openticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND departmentid = ".esc_sql($jsst_id);
        $jsst_closeticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND departmentid = ".esc_sql($jsst_id);
        $jsst_answeredticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND departmentid = ".esc_sql($jsst_id);
        $jsst_overdueticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND departmentid = ".esc_sql($jsst_id);
        $jsst_pendingticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_date_openticket = array();
        $jsst_date_closeticket = array();
        $jsst_date_answeredticket = array();
        $jsst_date_overdueticket = array();
        $jsst_date_pendingticket = array();
        foreach ($jsst_openticket AS $jsst_ticket) {
            if (!isset($jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_closeticket AS $jsst_ticket) {
            if (!isset($jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_answeredticket AS $jsst_ticket) {
            if (!isset($jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_overdueticket AS $jsst_ticket) {
            if (!isset($jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_pendingticket AS $jsst_ticket) {
            if (!isset($jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_overdue_ticket = 0;
        $jsst_pending_ticket = 0;
        $jsst_json_array = "";
        do{
            $jsst_year = date_i18n('Y',strtotime($jsst_nextdate));
            $jsst_month = date_i18n('m',strtotime($jsst_nextdate));
            $jsst_month = $jsst_month - 1; //js month are 0 based
            $jsst_day = date_i18n('d',strtotime($jsst_nextdate));
            $jsst_openticket_tmp = isset($jsst_date_openticket[$jsst_nextdate]) ? $jsst_date_openticket[$jsst_nextdate]  : 0;
            $jsst_closeticket_tmp = isset($jsst_date_closeticket[$jsst_nextdate]) ? $jsst_date_closeticket[$jsst_nextdate] : 0;
            $jsst_answeredticket_tmp = isset($jsst_date_answeredticket[$jsst_nextdate]) ? $jsst_date_answeredticket[$jsst_nextdate] : 0;
            $jsst_overdueticket_tmp = isset($jsst_date_overdueticket[$jsst_nextdate]) ? $jsst_date_overdueticket[$jsst_nextdate] : 0;
            $jsst_pendingticket_tmp = isset($jsst_date_pendingticket[$jsst_nextdate]) ? $jsst_date_pendingticket[$jsst_nextdate] : 0;
            $jsst_json_array .= "[new Date($jsst_year,$jsst_month,$jsst_day),$jsst_openticket_tmp,$jsst_answeredticket_tmp,$jsst_pendingticket_tmp,$jsst_overdueticket_tmp,$jsst_closeticket_tmp],";
            $jsst_open_ticket += $jsst_openticket_tmp;
            $jsst_close_ticket += $jsst_closeticket_tmp;
            $jsst_answered_ticket += $jsst_answeredticket_tmp;
            $jsst_overdue_ticket += $jsst_overdueticket_tmp;
            $jsst_pending_ticket += $jsst_pendingticket_tmp;
             if($jsst_nextdate == $jsst_curdate){
                break;
            }
            $jsst_nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_nextdate . " -1 days"));
        }while($jsst_nextdate != $jsst_curdate);

        jssupportticket::$jsst_data['line_chart_json_array'] = $jsst_json_array;


        // Pagination
        $jsst_query = "SELECT count(ticket.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_departments` AS department ON department.id = ticket.departmentid WHERE department.id = ".esc_sql($jsst_id);
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        $jsst_query = "SELECT department.id,department.departmentname,email.email,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE departmentid = department.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND departmentid = department.id) AS pendingticket
                    FROM `".jssupportticket::$_db->prefix."js_ticket_departments` AS department
                    JOIN `".jssupportticket::$_db->prefix."js_ticket_email` AS email ON department.emailid = email.id
                    WHERE department.id = ".esc_sql($jsst_id);
        //$jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        // No need of pagination because query return only single record
        $jsst_depatments = jssupportticket::$_db->get_row($jsst_query);
        jssupportticket::$jsst_data['depatments_report'] =$jsst_depatments;

        //Tickets
        do_action('jsstFeedbackQueryStaff');
        $jsst_query = "SELECT ticket.*,priority.priority, priority.prioritycolour,status.status AS statustitle,status.statuscolour,status.statusbgcolour ".jssupportticket::$_addon_query['select']."
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    ".jssupportticket::$_addon_query['join']."
                    WHERE departmentid = ".esc_sql($jsst_id)." AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' ";
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        do_action('jsst_reset_aadon_query');

        jssupportticket::$jsst_data['department_tickets'] = jssupportticket::$_db->get_results($jsst_query);
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach (jssupportticket::$jsst_data['department_tickets'] as $jsst_ticket) {
                 $jsst_ticket->time = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($jsst_ticket->id);
            }
        }
    }


    function getStaffDetailReportByUserId($jsst_id){
        if(!is_numeric($jsst_id)) return false;

        $jsst_date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_start'] : '';
        $jsst_date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report']['date_end'] : '';
        if(isset($jsst_date_start) && $jsst_date_start != ""){
            $jsst_date_start = date_i18n('Y-m-d',strtotime($jsst_date_start));
        }
        if(isset($jsst_date_end) && $jsst_date_end != ""){
            $jsst_date_end = date_i18n('Y-m-d',strtotime($jsst_date_end));
        }
        if($jsst_date_start > $jsst_date_end){
            $jsst_tmp = $jsst_date_start;
            $jsst_date_start = $jsst_date_end;
            $jsst_date_end = $jsst_tmp;
        }
        //Line Chart Data
        $jsst_curdate = ($jsst_date_start != null) ? date_i18n('Y-m-d',strtotime($jsst_date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $jsst_fromdate = ($jsst_date_end != null) ? date_i18n('Y-m-d',strtotime($jsst_date_end)) : date_i18n('Y-m-d');
        jssupportticket::$jsst_data['filter']['date_start'] = $jsst_curdate;
        jssupportticket::$jsst_data['filter']['date_end'] = $jsst_fromdate;
        jssupportticket::$jsst_data['filter']['uid'] = $jsst_id;
        $jsst_nextdate = $jsst_fromdate;

        // forexport
        $_SESSION['forexport']['curdate'] = $jsst_curdate;
        $_SESSION['forexport']['fromdate'] = $jsst_fromdate;
        $_SESSION['forexport']['id'] = $jsst_id;


        //Query to get Data
        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1 AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_openticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_closeticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_answeredticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_overdueticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_query = "SELECT created FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "'";
        if($jsst_id) $jsst_query .= " AND uid = ".esc_sql($jsst_id);
        $jsst_pendingticket = jssupportticket::$_db->get_results($jsst_query);

        $jsst_date_openticket = array();
        $jsst_date_closeticket = array();
        $jsst_date_answeredticket = array();
        $jsst_date_overdueticket = array();
        $jsst_date_pendingticket = array();
        foreach ($jsst_openticket AS $jsst_ticket) {
            if (!isset($jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_closeticket AS $jsst_ticket) {
            if (!isset($jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_closeticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_answeredticket AS $jsst_ticket) {
            if (!isset($jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_answeredticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_overdueticket AS $jsst_ticket) {
            if (!isset($jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_overdueticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        foreach ($jsst_pendingticket AS $jsst_ticket) {
            if (!isset($jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_pendingticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + 1;
        }
        $jsst_open_ticket = 0;
        $jsst_close_ticket = 0;
        $jsst_answered_ticket = 0;
        $jsst_overdue_ticket = 0;
        $jsst_pending_ticket = 0;
        $jsst_json_array = "";
        do{
            $jsst_year = date_i18n('Y',strtotime($jsst_nextdate));
            $jsst_month = date_i18n('m',strtotime($jsst_nextdate));
            $jsst_month = $jsst_month - 1; //js month are 0 based
            $jsst_day = date_i18n('d',strtotime($jsst_nextdate));
            $jsst_openticket_tmp = isset($jsst_date_openticket[$jsst_nextdate]) ? $jsst_date_openticket[$jsst_nextdate]  : 0;
            $jsst_closeticket_tmp = isset($jsst_date_closeticket[$jsst_nextdate]) ? $jsst_date_closeticket[$jsst_nextdate] : 0;
            $jsst_answeredticket_tmp = isset($jsst_date_answeredticket[$jsst_nextdate]) ? $jsst_date_answeredticket[$jsst_nextdate] : 0;
            $jsst_overdueticket_tmp = isset($jsst_date_overdueticket[$jsst_nextdate]) ? $jsst_date_overdueticket[$jsst_nextdate] : 0;
            $jsst_pendingticket_tmp = isset($jsst_date_pendingticket[$jsst_nextdate]) ? $jsst_date_pendingticket[$jsst_nextdate] : 0;
            $jsst_json_array .= "[new Date($jsst_year,$jsst_month,$jsst_day),$jsst_openticket_tmp,$jsst_answeredticket_tmp,$jsst_pendingticket_tmp,$jsst_overdueticket_tmp,$jsst_closeticket_tmp],";
            $jsst_open_ticket += $jsst_openticket_tmp;
            $jsst_close_ticket += $jsst_closeticket_tmp;
            $jsst_answered_ticket += $jsst_answeredticket_tmp;
            $jsst_overdue_ticket += $jsst_overdueticket_tmp;
            $jsst_pending_ticket += $jsst_pendingticket_tmp;
             if($jsst_nextdate == $jsst_curdate){
                break;
            }
            $jsst_nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_nextdate . " -1 days"));
        }while($jsst_nextdate != $jsst_curdate);

        jssupportticket::$jsst_data['line_chart_json_array'] = $jsst_json_array;

        $jsst_query = "SELECT user.display_name,user.user_email,user.user_nicename,user.id,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = user.id) AS allticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 1  AND (lastreply = '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS openticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE status = 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS closeticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS answeredticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS overdueticket,
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply != '0000-00-00 00:00:00') AND date(created) >= '" . esc_sql($jsst_curdate) . "' AND date(created) <= '" . esc_sql($jsst_fromdate) . "' AND uid = user.id) AS pendingticket
                    FROM `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user
                    WHERE user.id = ".esc_sql($jsst_id);
        $jsst_agent = jssupportticket::$_db->get_row($jsst_query);
        jssupportticket::$jsst_data['user_report'] =$jsst_agent;
        // Pagination
        $jsst_query = "SELECT COUNT(ticket.id)
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    WHERE uid = ".esc_sql($jsst_id)." AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' ";
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);
        //Tickets
        do_action('jsstFeedbackQueryStaff');
        $jsst_query = "SELECT ticket.*,priority.priority, priority.prioritycolour,status.status AS statustitle,status.statuscolour,status.statusbgcolour ".jssupportticket::$_addon_query['select']."
                    FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
                    LEFT JOIN `".jssupportticket::$_db->prefix."js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    ".jssupportticket::$_addon_query['join']."
                    WHERE uid = ".esc_sql($jsst_id)." AND date(ticket.created) >= '" . esc_sql($jsst_curdate) . "' AND date(ticket.created) <= '" . esc_sql($jsst_fromdate) . "' ";
        $jsst_query .= " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        do_action('jsst_reset_aadon_query');
        jssupportticket::$jsst_data['user_tickets'] = jssupportticket::$_db->get_results($jsst_query);
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            foreach (jssupportticket::$jsst_data['user_tickets'] as $jsst_ticket) {
                 $jsst_ticket->time = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($jsst_ticket->id);
            }
        }
        return;
    }

    function getStaffTimingReportById($jsst_id){
        if( !in_array('agent',jssupportticket::$_active_addons) ){
            return;
        }
        if(!is_numeric($jsst_id)) return false;

        $jsst_start_date ='date_start';
        $jsst_end_date ='date_end';

        $jsst_date_start = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report'][$jsst_start_date] : '';
        $jsst_date_end = isset(jssupportticket::$_search['report']) ? jssupportticket::$_search['report'][$jsst_end_date] : '';
        if($jsst_date_start > $jsst_date_end){
            $jsst_tmp = $jsst_date_start;
            $jsst_date_start = $jsst_date_end;
            $jsst_date_end = $jsst_tmp;
        }

        //Line Chart Data
        $jsst_curdate = ($jsst_date_start != null) ? date_i18n('Y-m-d',strtotime($jsst_date_start)) : date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
        $jsst_fromdate = ($jsst_date_end != null) ? date_i18n('Y-m-d',strtotime($jsst_date_end)) : date_i18n('Y-m-d');
        jssupportticket::$jsst_data['filter'][$jsst_start_date] = $jsst_curdate;
        jssupportticket::$jsst_data['filter'][$jsst_end_date] = $jsst_fromdate;
        jssupportticket::$jsst_data['filter']['id'] = $jsst_id;

        $jsst_nextdate = $jsst_fromdate;

        //Query to get Data
        if(in_array('timetracking', jssupportticket::$_active_addons)){
            $jsst_query = "SELECT created,usertime FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff_time` ";
            $jsst_query .= " WHERE staffid = ".esc_sql($jsst_id);
            $jsst_openticket = jssupportticket::$_db->get_results($jsst_query);
        }else{
            $jsst_openticket = array();
        }

        $jsst_date_openticket = array();
        foreach ($jsst_openticket AS $jsst_ticket) {
            if (!isset($jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))]))
                $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = 0;
            $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] = $jsst_date_openticket[date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_ticket->created))] + $jsst_ticket->usertime;
        }
        $jsst_open_ticket = 0;
        $jsst_json_array = "";
        do{
            $jsst_year = date_i18n('Y',strtotime($jsst_nextdate));
            $jsst_month = date_i18n('m',strtotime($jsst_nextdate));
            $jsst_month = $jsst_month - 1; //js month are 0 based
            $jsst_day = date_i18n('d',strtotime($jsst_nextdate));
            if(isset($jsst_date_openticket[$jsst_nextdate])){

                $jsst_mins = floor($jsst_date_openticket[$jsst_nextdate] / 60);
                $jsst_openticket_tmp =  $jsst_mins;
            }else{
                $jsst_openticket_tmp =  0;
            }
            $jsst_json_array .= '[new Date('.$jsst_year.','.$jsst_month.','.$jsst_day.'),'.$jsst_openticket_tmp.'],';
            $jsst_nextdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_nextdate . " -1 days"));
        }while($jsst_nextdate != $jsst_curdate);
        jssupportticket::$jsst_data['line_chart_json_array'] = $jsst_json_array;
        jssupportticket::$jsst_data[0]['staffname'] = JSSTincluder::getJSModel('agent')->getMyName($jsst_id);

    }


}
?>
