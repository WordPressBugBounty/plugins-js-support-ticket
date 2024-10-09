<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTticketModel {

    private $ticketid;

    function getTicketsForAdmin($lst=null) {
        $this->getOrdering();
        // Filter
        $search_userfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
        $subject = jssupportticket::$_search['ticket']['subject'];
        $name = jssupportticket::$_search['ticket']['name'];
        $email = jssupportticket::$_search['ticket']['email'];
        $ticketid = jssupportticket::$_search['ticket']['ticketid'];
        $datestart = jssupportticket::$_search['ticket']['datestart'];
        $dateend = jssupportticket::$_search['ticket']['dateend'];
        $orderid = jssupportticket::$_search['ticket']['orderid'];
        $eddorderid = jssupportticket::$_search['ticket']['eddorderid'];
        $priority = jssupportticket::$_search['ticket']['priority'];
        $departmentid = jssupportticket::$_search['ticket']['departmentid'];
        $staffid = jssupportticket::$_search['ticket']['staffid'];
        $sortby = jssupportticket::$_search['ticket']['sortby'];
        if (!empty($search_userfields)) {
            foreach ($search_userfields as $uf) {
                $value_array[$uf->field] = jssupportticket::$_search['jsst_ticket_custom_field'][$uf->field];
            }
        }
        $inquery = '';
        if($lst != null){
            jssupportticket::$_search['ticket']['list'] = $lst;
        }
        $list = jssupportticket::$_search['ticket']['list'];
        switch ($list) {
            // Ticket Default Status
            // 0 -> New Ticket
            // 1 -> Waiting admin/staff reply
            // 2 -> in progress
            // 3 -> waiting for customer reply
            // 4 -> close ticket
            case 1:$inquery .= " AND ticket.status != 4 AND ticket.status != 5";
                break;
            case 2:$inquery .= " AND ticket.isanswered = 1 AND ticket.status != 4 AND ticket.status != 5 AND ticket.status != 0";
                break;
            case 3:$inquery .= " AND ticket.isoverdue = 1 AND ticket.status != 4 AND ticket.status != 5 ";
                break;
            case 4:$inquery .= " AND (ticket.status = 4 OR ticket.status = 5) ";
                break;
            case 5://$inquery .= " AND ticket.uid =" . JSSTincluder::getObjectClass('user')->uid();
                break;
        }

        if ($datestart != null)
            $inquery .= " AND '".esc_sql($datestart)."' <= DATE(ticket.created)";
        if ($dateend != null)
            $inquery .= " AND '".esc_sql($dateend)."' >= DATE(ticket.created)";
        if ($ticketid != null)
            $inquery .= " AND ticket.ticketid LIKE '%".esc_sql($ticketid)."%'";
        if ($subject != null)
            $inquery .= " AND ticket.subject LIKE '%".esc_sql($subject)."%'";
        if ($name != null)
            $inquery .= " AND ticket.name LIKE '%".esc_sql($name)."%'";
        if ($email != null)
            $inquery .= " AND ticket.email LIKE '%".esc_sql($email)."%'";
        if ($priority != null)
            $inquery .= " AND ticket.priorityid = $priority";
        if ($departmentid != null)
            $inquery .= " AND ticket.departmentid = $departmentid";
        if ($staffid != null)
            $inquery .= " AND ticket.staffid = $staffid";

        if ($orderid != null && is_numeric($orderid))
            $inquery .= " AND ticket.wcorderid = $orderid";

        if ($eddorderid != null && is_numeric($eddorderid))
            $inquery .= " AND ticket.eddorderid = $eddorderid";

        $valarray = array();
        if (!empty($search_userfields)) {
            foreach ($search_userfields as $uf) {
                if (JSSTrequest::getVar('pagenum', 'get', null) != null) {
                    $valarray[$uf->field] = $value_array[$uf->field];
                }else{
                    $valarray[$uf->field] = JSSTrequest::getVar($uf->field, 'post');
                }
                if (isset($valarray[$uf->field]) && $valarray[$uf->field] != null) {
                    switch ($uf->userfieldtype) {
                        case 'admin_only':
                            $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '.*"\' ';
                            break;
                        case 'text':
                            $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '.*"\' ';
                            break;
                        case 'email':
                            $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '.*"\' ';
                            break;
                        case 'file':
                            $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '.*"\' ';
                            break;
                        case 'combo':
                            $inquery .= ' AND ticket.params LIKE \'%"' . esc_sql($uf->field) . '":"' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '"%\' ';
                            break;
                        case 'depandant_field':
                            $inquery .= ' AND ticket.params LIKE \'%"' . esc_sql($uf->field) . '":"' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '"%\' ';
                            break;
                        case 'radio':
                            $inquery .= ' AND ticket.params LIKE \'%"' . esc_sql($uf->field) . '":"' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '"%\' ';
                            break;
                        case 'checkbox':
                            $finalvalue = '';
                            foreach($valarray[$uf->field] AS $value){
                                $finalvalue .= $value.'.*';
                            }
                            $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($finalvalue)) . '.*"\' ';
                            break;
                        case 'date':
                            $inquery .= ' AND ticket.params LIKE \'%"' . esc_sql($uf->field) . '":"' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '"%\' ';
                            break;
                        case 'textarea':
                            $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '.*"\' ';
                            break;
                        case 'multiple':
                            $finalvalue = '';
                            foreach($valarray[$uf->field] AS $value){
                                if($value != null){
                                    $finalvalue .= $value.'.*';
                                }
                            }
                            if($finalvalue !=''){
                                $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*'.htmlspecialchars(esc_sql($finalvalue)).'.*"\'';
                            }
                            break;
                    }
                    jssupportticket::$_data['filter']['params'] = $valarray;
                }
            }
        }
        //end

        jssupportticket::$_data['filter']['subject'] = $subject;
        jssupportticket::$_data['filter']['ticketid'] = $ticketid;
        jssupportticket::$_data['filter']['name'] = $name;
        jssupportticket::$_data['filter']['email'] = $email;
        jssupportticket::$_data['filter']['datestart'] = $datestart;
        jssupportticket::$_data['filter']['dateend'] = $dateend;
        jssupportticket::$_data['filter']['priority'] = $priority;
        jssupportticket::$_data['filter']['departmentid'] = $departmentid;
        jssupportticket::$_data['filter']['staffid'] = $staffid;
        jssupportticket::$_data['filter']['sortby'] = $sortby;
        jssupportticket::$_data['filter']['orderid'] = $orderid;
        jssupportticket::$_data['filter']['eddorderid'] = $eddorderid;

        $userquery = '';
        $uid = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('uid'));
        if($uid != null && is_numeric($uid)){
            $userquery = ' AND ticket.uid = '.esc_sql($uid);
        }

        // Pagination
        $query = "SELECT COUNT(ticket.id) "
                . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                . "WHERE 1 = 1";
        $query .= $inquery.$userquery;
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total);

        /*
          list variable detail
          1=>For open ticket
          2=>For answered  ticket
          3=>For overdue ticket
          4=>For Closed tickets
          5=>For mytickets tickets
         */
        jssupportticket::$_data['list'] = $list; // assign for reference
        // Data
        do_action('jsst_addon_staff_admin_tickets');
        $query = "SELECT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour ".jssupportticket::$_addon_query['select']."
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    ".jssupportticket::$_addon_query['join']."
                    WHERE 1 = 1";

        $query .= $inquery.$userquery;
        $query .= " ORDER BY " . jssupportticket::$_ordering . " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        do_action('reset_jsst_aadon_query');
        // check email is bane
        if(in_array('banemail', jssupportticket::$_active_addons)){
            if (isset(jssupportticket::$_data[0]->email))
                $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = ' " . esc_sql(jssupportticket::$_data[0]->email) . "'";
            jssupportticket::$_data[7] = jssupportticket::$_db->get_var($query);
        }else{
            jssupportticket::$_data[7] = 0;
        }
        //Hook action
        do_action('jsst-ticketbeforelisting', jssupportticket::$_data[0]);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if(jssupportticket::$_config['count_on_myticket'] == 1){
            $query = "SELECT COUNT(ticket.id) "
                    . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                    . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                    . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                    . "WHERE (ticket.status != 4 AND ticket.status != 5)".$userquery;
            jssupportticket::$_data['count']['openticket'] = jssupportticket::$_db->get_var($query);;

            $query = "SELECT COUNT(ticket.id) "
                    . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                    . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                    . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                    . "WHERE ticket.isanswered = 1 AND ticket.status != 4 AND ticket.status != 5 AND ticket.status != 0 ".$userquery;
            jssupportticket::$_data['count']['answeredticket'] = jssupportticket::$_db->get_var($query);;

            $query = "SELECT COUNT(ticket.id) "
                    . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                    . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                    . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                    . "WHERE ticket.isoverdue = 1 AND ticket.status != 4 AND ticket.status != 5 ".$userquery;
            jssupportticket::$_data['count']['overdueticket'] = jssupportticket::$_db->get_var($query);;

            $query = "SELECT COUNT(ticket.id) "
                    . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                    . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                    . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                    . "WHERE (ticket.status = 4 OR ticket.status = 5)".$userquery;
            jssupportticket::$_data['count']['closedticket'] = jssupportticket::$_db->get_var($query);;

            $query = "SELECT COUNT(ticket.id) "
                    . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                    . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                    . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                    . "WHERE 1 = 1".$userquery;
            jssupportticket::$_data['count']['allticket'] = jssupportticket::$_db->get_var($query);
        }
        return;
    }

    function getOrdering() {
        $sort = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['sortby'] : '';
        if ($sort == '') {
            $list = jssupportticket::$_config['tickets_ordering'];
            // default sort by
            $sortbyconfig = jssupportticket::$_config['tickets_sorting'];
            if($sortbyconfig == 1){
                $sortbyconfig = "asc";
            }else{
                $sortbyconfig = "desc";
            }
            $sort = 'status';
            if($list == 2)
                $sort = 'created';
            $sort = $sort.$sortbyconfig;
        }
        $this->getTicketListOrdering($sort);
        $this->getTicketListSorting($sort);
    }

    function combineOrSingleSearch() {
        $ticketkeys = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['ticketkeys'] : false;
        $inquery = '';
        if ($ticketkeys) {
            if (strpos($ticketkeys, '@') && jssupportticketphplib::JSST_strpos($ticketkeys, '.')){
                $inquery = " AND ticket.email LIKE '%".esc_sql($ticketkeys)."%'";
            }else{
                $inquery = " AND (ticket.ticketid = '".esc_sql($ticketkeys)."' OR ticket.subject LIKE '%".esc_sql($ticketkeys)."%')";
            }
            jssupportticket::$_data['filter']['ticketsearchkeys'] = $ticketkeys;
        }else {
            $search_userfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
            $ticketid = JSSTrequest::getVar('jsst-ticket', 'post');

            $from = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['name'] : '';
            $email = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['email'] : '';
            $departmentid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['departmentid'] : '';
            $priorityid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['priority'] : '';
            $subject = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['subject'] : '';
            $datestart = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['datestart'] : '';
            $dateend = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['dateend'] : '';
            $orderid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['orderid'] : '';
            $eddorderid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['eddorderid'] : '';
            $staffid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['staffid'] : '';
            $sortby = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['sortby'] : '';
            $assignedtome = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['assignedtome'] : '';

            if (!empty($search_userfields)) {
                foreach ($search_userfields as $uf) {
                    $value_array[$uf->field] = isset(jssupportticket::$_search['jsst_ticket_custom_field']) ? jssupportticket::$_search['jsst_ticket_custom_field'][$uf->field] : '';
                }
            }

            if ($ticketid != null) {
                $inquery .= " AND ticket.ticketid LIKE '".esc_sql($ticketid)."'";
                jssupportticket::$_data['filter']['ticketid'] = $ticketid;
            }
            if ($from != null) {
                $inquery .= " AND ticket.name LIKE '%".esc_sql($from)."%'";
                jssupportticket::$_data['filter']['from'] = $from;
            }
            if ($email != null) {
                $inquery .= " AND ticket.email LIKE '".esc_sql($email)."'";
                jssupportticket::$_data['filter']['email'] = $email;
            }
            if ($departmentid != null) {
                $inquery .= " AND ticket.departmentid = '".esc_sql($departmentid)."'";
                jssupportticket::$_data['filter']['departmentid'] = $departmentid;
            }
            if ($priorityid != null) {
                $inquery .= " AND ticket.priorityid = '".esc_sql($priorityid)."'";
                jssupportticket::$_data['filter']['priorityid'] = $priorityid;
            }
            if(in_array('agent', jssupportticket::$_active_addons)){
                if ($staffid != null) {
                    $inquery .= " AND ticket.staffid = '".esc_sql($staffid)."'";
                    jssupportticket::$_data['filter']['staffid'] = $staffid;
                }
            }

            if ($subject != null) {
                $inquery .= " AND ticket.subject LIKE '%".esc_sql($subject)."%'";
                jssupportticket::$_data['filter']['subject'] = $subject;
            }
            if ($datestart != null) {
                $inquery .= " AND '".esc_sql($datestart)."' <= DATE(ticket.created)";
                jssupportticket::$_data['filter']['datestart'] = $datestart;
            }
            if ($dateend != null) {
                $inquery .= " AND '".esc_sql($dateend)."' >= DATE(ticket.created)";
                jssupportticket::$_data['filter']['dateend'] = $dateend;
            }

            if ($orderid != null && is_numeric($orderid)) {
                $inquery .= " AND ticket.wcorderid = ".esc_sql($orderid);
                jssupportticket::$_data['filter']['orderid'] = $orderid;
            }

            if ($eddorderid != null && is_numeric($eddorderid)) {
                $inquery .= " AND ticket.eddorderid = ".esc_sql($eddorderid);
                jssupportticket::$_data['filter']['eddorderid'] = $eddorderid;
            }

            if ($assignedtome != null) {
                if(in_array('agent',jssupportticket::$_active_addons)){
                    $uid = JSSTincluder::getObjectClass('user')->uid();
                    $stfid = JSSTincluder::getJSModel('agent')->getStaffId($uid);
                    $inquery .= " AND ticket.staffid = '".esc_sql($stfid)."'";
                    jssupportticket::$_data['filter']['assignedtome'] = $assignedtome;
                }
            }
            //Custom field search


            //start
            $data = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
            $valarray = array();
            if (!empty($data)) {
                foreach ($data as $uf) {
                    if (JSSTrequest::getVar('pagenum', 'get', null) != null) {
                        $valarray[$uf->field] = $value_array[$uf->field];
                    }else{
                        $valarray[$uf->field] = JSSTrequest::getVar($uf->field, 'post');
                    }
                    if (isset($valarray[$uf->field]) && $valarray[$uf->field] != null) {
                        switch ($uf->userfieldtype) {
                            case 'text':
                            case 'email':
                                $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '.*"\' ';
                                break;
                            case 'combo':
                                $inquery .= ' AND ticket.params LIKE \'%"' . esc_sql($uf->field) . '":"' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '"%\' ';
                                break;
                            case 'depandant_field':
                                $inquery .= ' AND ticket.params LIKE \'%"' . esc_sql($uf->field) . '":"' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '"%\' ';
                                break;
                            case 'radio':
                                $inquery .= ' AND ticket.params LIKE \'%"' . esc_sql($uf->field) . '":"' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '"%\' ';
                                break;
                            case 'checkbox':
                                $finalvalue = '';
                                foreach($valarray[$uf->field] AS $value){
                                    $finalvalue .= $value.'.*';
                                }
                                $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($finalvalue)) . '.*"\' ';
                                break;
                            case 'date':
                                $inquery .= ' AND ticket.params LIKE \'%"' . esc_sql($uf->field) . '":"' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '"%\' ';
                                break;
                            case 'textarea':
                                $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars(esc_sql($valarray[$uf->field])) . '.*"\' ';
                                break;
                            case 'multiple':
                                $finalvalue = '';
                                foreach($valarray[$uf->field] AS $value){
                                    if($value != null){
                                        $finalvalue .= $value.'.*';
                                    }
                                }
                                if($finalvalue !=''){
                                    $inquery .= ' AND ticket.params REGEXP \'"' . esc_sql($uf->field) . '":"[^"]*'.htmlspecialchars(esc_sql($finalvalue)).'.*"\'';
                                }
                                break;
                        }
                        jssupportticket::$_data['filter']['params'] = $valarray;
                    }
                }
            }
            //end

            if ($inquery == '')
                jssupportticket::$_data['filter']['combinesearch'] = false;
            else
                jssupportticket::$_data['filter']['combinesearch'] = true;
        }
        return $inquery;
    }

    function getMyTickets($lst=null) {
        $this->getOrdering();
        // Filter
        /*
          list variable detail
          1=>For open ticket
          2=>For closed ticket
          3=>For open answered ticket
          4=>For all my tickets
         */
        $inquery = $this->combineOrSingleSearch();
        if($lst != null){
            jssupportticket::$_search['ticket']['list'] = $lst;
        }
        $list = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['list'] : 1;
        jssupportticket::$_data['list'] = $list; // assign for reference
        switch ($list) {
            // Ticket Default Status
            // 0 -> New Ticket
            // 1 -> Waiting admin/staff reply
            // 2 -> in progress
            // 3 -> waiting for customer reply
            // 4 -> close ticket
           case 1:$inquery .= " AND (ticket.status != 4 AND ticket.status != 5)";
                break;
            case 2:$inquery .= " AND (ticket.status = 4 OR ticket.status = 5) ";
                break;
            case 3:$inquery .= " AND ticket.status = 3 ";
                break;
            case 4:$inquery .= " ";
                break;
            case 5:$inquery .= " AND ticket.isoverdue = 1 AND ticket.status != 4 AND ticket.status != 5 ";
                break;
        }

        $uid = JSSTincluder::getObjectClass('user')->uid();
        if ($uid) {
            // Pagination
            $query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE ticket.uid = ".esc_sql($uid);
            $query .= $inquery;
            $total = jssupportticket::$_db->get_var($query);
            jssupportticket::$_data[1] = JSSTpagination::getPagination($total,'myticket');

            // Data
            do_action('jsst_addon_user_my_tickets');

            $query = "SELECT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour ".jssupportticket::$_addon_query['select']."
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        ".jssupportticket::$_addon_query['join']."
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id";
            $query .= " WHERE ticket.uid = ". esc_sql($uid) . $inquery;
            $query .= " ORDER BY " . jssupportticket::$_ordering . " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
            jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
            do_action('reset_jsst_aadon_query');
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            if(jssupportticket::$_config['count_on_myticket'] == 1){
                $query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE ticket.uid = ".esc_sql($uid)." AND (ticket.status != 4 AND ticket.status != 5)";
                jssupportticket::$_data['count']['openticket'] = jssupportticket::$_db->get_var($query);

                $query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE ticket.uid = ". esc_sql($uid) ." AND ticket.status = 3 ";
                jssupportticket::$_data['count']['answeredticket'] = jssupportticket::$_db->get_var($query);

                $query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE ticket.uid = ". esc_sql($uid) ." AND (ticket.status = 4 OR ticket.status = 5)";
                jssupportticket::$_data['count']['closedticket'] = jssupportticket::$_db->get_var($query);

                $query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE ticket.uid = ". esc_sql($uid);
                jssupportticket::$_data['count']['allticket'] = jssupportticket::$_db->get_var($query);
            }
        }
        return;
    }

    function getStaffTickets($lst=null) {
        if (! in_array('agent',jssupportticket::$_active_addons)) {
            return;
        }

        $this->getOrdering();
        // Filter
        /*
          list variable detail
          1=>For open ticket
          2=>For closed ticket
          3=>For open answered ticket
          4=>For all my tickets
         */

        $inquery = $this->combineOrSingleSearch();
        if($lst != null){
            jssupportticket::$_search['ticket']['list'] = $lst;
        }
        $list = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['list'] : 1; // assign for reference
        jssupportticket::$_data['list'] = $list;
        switch ($list) {
            // Ticket Default Status
            // 0 -> Open Ticket
            // 1 -> Waiting admin/staff reply
            // 2 -> in progress
            // 3 -> waiting for customer reply
            // 4 -> close ticket
            case 1:$inquery .= " AND (ticket.status != 4 AND ticket.status != 5)";
                break;
            case 2:$inquery .= " AND (ticket.status = 4 OR ticket.status = 5) ";
                break;
            case 3:$inquery .= " AND ticket.status = 3 ";
                break;
            case 4:$inquery .= " ";
                break;
            case 5:$inquery .= " AND ticket.isoverdue = 1 AND ticket.status != 4 AND ticket.status != 5 ";
                break;
        }

        $uid = JSSTincluder::getObjectClass('user')->uid();
        if ($uid == 0)
            return false;
        $staffid = JSSTincluder::getJSModel('agent')->getStaffId($uid);

        //to handle all tickets permissoin
        $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
        if($allowed == true){
            $agent_conditions = "1 = 1";
        }else{
            $agent_conditions = "ticket.staffid = ".esc_sql($staffid)." OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = " .esc_sql($staffid).")";
        }
        //show specific user's tickets
        $userquery = "";
        $uid = JSSTrequest::getVar('uid');
        if(is_numeric($uid) && $uid > 0){
            $userquery .= " AND ticket.uid = ".esc_sql($uid);
        }
        // Pagination
        $query = "SELECT COUNT(ticket.id)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    WHERE (".esc_sql($agent_conditions).") ";
        $query .= $inquery;
        $query .= $userquery;
        $total = jssupportticket::$_db->get_var($query);
        jssupportticket::$_data[1] = JSSTpagination::getPagination($total,'myticket');

        // Data
        $query = "SELECT DISTINCT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour,assignstaff.photo AS staffphoto,assignstaff.id AS staffid, assignstaff.firstname AS staffname
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS assignstaff ON ticket.staffid = assignstaff.id
                    WHERE (".esc_sql($agent_conditions).") " . $inquery . $userquery;;
        $query .= " ORDER BY " . jssupportticket::$_ordering . " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$_data[0] = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if(jssupportticket::$_config['count_on_myticket'] == 1){
            $query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($agent_conditions).") AND (ticket.status != 4 AND ticket.status !=5) ".$userquery;
            jssupportticket::$_data['count']['openticket'] = jssupportticket::$_db->get_var($query);

            $query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($agent_conditions).") AND ticket.status = 3 ".$userquery;
            jssupportticket::$_data['count']['answeredticket'] = jssupportticket::$_db->get_var($query);;

            $query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($agent_conditions).") AND (ticket.status = 4 OR ticket.status = 5) ".$userquery;
            jssupportticket::$_data['count']['closedticket'] = jssupportticket::$_db->get_var($query);;


            $query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($agent_conditions).") AND ticket.isoverdue = 1 AND ticket.status != 4 AND ticket.status != 5 ".$userquery;
            jssupportticket::$_data['count']['overdue'] = jssupportticket::$_db->get_var($query);

            $query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($agent_conditions).")  ".$userquery;
            jssupportticket::$_data['count']['allticket'] = jssupportticket::$_db->get_var($query);
        }
        return;
    }

    function getTicketsForForm($id,$formid='') {
        if (!isset($formid) || $formid=='') {
           $formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
        }
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour,user.name AS user_login
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        LEFT JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = ticket.uid
                        WHERE ticket.id = " . esc_sql($id);
            jssupportticket::$_data[0] = jssupportticket::$_db->get_row($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }else{
                if(!empty(jssupportticket::$_data[0])){
                    //to store hash value of id against old tickets
                    if( jssupportticket::$_data[0]->hash == null ){
                        $hash = $this->generateHash($id);
                        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET `hash`='".esc_sql($hash)."' WHERE id=".esc_sql($id);
                        jssupportticket::$_db->query($query);
                    } //end
                }
            }
            $formid = jssupportticket::$_data[0]->multiformid;
        }
        jssupportticket::$_data['formid'] = $formid;
        JSSTincluder::getJSModel('attachment')->getAttachmentForForm($id);
        JSSTincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(1,$formid);
        return;
    }

    function getTicketForDetail($id) {
        if (!is_numeric($id)){
            return $id;
        }
        if (in_array('agent', jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff']) { //staff
            if(current_user_can('jsst_support_ticket')){
                jssupportticket::$_data['permission_granted'] = true;
                JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(gmdate("Y-m-d h:i:s"),'','ticket_time_start_',$id);
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    jssupportticket::$_data['time_taken'] = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($id);
                }
            }else{
                jssupportticket::$_data['permission_granted'] = $this->validateTicketDetailForStaff($id);
                if (jssupportticket::$_data['permission_granted']) { // validation passed
                    if(in_array('timetracking', jssupportticket::$_active_addons)){
                        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(gmdate("Y-m-d h:i:s"),'','ticket_time_start_',$id);
                        jssupportticket::$_data['time_taken'] = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($id);
                    }
                }
            }

        } else { // user
            if(current_user_can('jsst_support_ticket') || current_user_can('jsst_support_ticket_tickets')){
                jssupportticket::$_data['permission_granted'] = true;
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(gmdate("Y-m-d h:i:s"),'','ticket_time_start_',$id);
                    jssupportticket::$_data['time_taken'] = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($id);
                }
            }
            elseif (!JSSTincluder::getObjectClass('user')->isguest())
                jssupportticket::$_data['permission_granted'] = $this->validateTicketDetailForUser($id);
            else
                jssupportticket::$_data['permission_granted'] = $this->validateTicketDetailForVisitor($id);
        }
        if (!jssupportticket::$_data['permission_granted']) { // validation failed
            return;
        }

        do_action('ticket_detail_query');// TO HANDLE ALL THE QUERIES OF ADDONS

        $query = "SELECT ticket.*,priority.priority AS priority,priority.prioritycolour AS prioritycolour,department.departmentname AS departmentname
                     ".jssupportticket::$_addon_query['select']."
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    ".jssupportticket::$_addon_query['join']."
                    WHERE ticket.id = " . esc_sql($id);
        jssupportticket::$_data[0] = jssupportticket::$_db->get_row($query);
        do_action('reset_jsst_aadon_query');
        // check email is ban
        if(in_array('banemail', jssupportticket::$_active_addons)){
            $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = '" . esc_sql(jssupportticket::$_data[0]->email) . "'";
            jssupportticket::$_data[7] = jssupportticket::$_db->get_var($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
        }else{
            jssupportticket::$_data[7] = 0;
        }
        if(in_array('note', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('note')->getNotes($id);
        }
        JSSTincluder::getJSModel('reply')->getReplies($id);
        jssupportticket::$_data['ticket_attachment'] = JSSTincluder::getJSModel('attachment')->getAttachmentForReply($id, 0);
        $this->getTicketHistory($id);

        if(jssupportticket::$_data[0]->uid > 0){

            //count all ticket of user
            $query = "SELECT COUNT(id) FROM `" .jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE `uid` = ".esc_sql(jssupportticket::$_data[0]->uid);
            jssupportticket::$_data['nticket'] = jssupportticket::$_db->get_var($query);

            //get user tickets for right widget
            $inquery = " WHERE ticket.id != " . esc_sql($id) . " AND ticket.uid = " . esc_sql(jssupportticket::$_data[0]->uid);
            if(!is_admin() && in_array('agent', jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff']){
                $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
                if($allowed != true){
                    $staffid = JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid());
                    $inquery .= " AND (ticket.staffid = $staffid OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($staffid)."))";
                }
            }
            $query = "SELECT ticket.id,ticket.subject,ticket.status,ticket.lock,ticket.isoverdue,priority.priority AS priority,priority.prioritycolour AS prioritycolour,department.departmentname AS departmentname
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id";
            $query .= $inquery . " LIMIT 3 ";
            jssupportticket::$_data['usertickets'] = jssupportticket::$_db->get_results($query);
        }
        //Hooks
        do_action('jsst-ticketbeforeview', jssupportticket::$_data);

        return;
    }



    function validateUserForTicket($id) {
        if (!JSSTincluder::getObjectClass('user')->isguest()) {

        } else {
            jssupportticket::$_data['permission_granted'] = $this->checkTokenForTicketDetail($id);
        }
        return;
    }

    function getRandomTicketId() {
        $match = '';
        $customticketno = '';
        $count = 0;
        //$match = 'Y';
		do {
            $count++;
            $ticketid = "";
            $length = 9;
            $sequence = jssupportticket::$_config['ticketid_sequence'];
            if($sequence == 1){
                $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
                // we refer to the length of $possible a few times, so let's grab it now
                $maxlength = jssupportticketphplib::JSST_strlen($possible);
                if ($length > $maxlength) { // check for length overflow and truncate if necessary
                    $length = $maxlength;
                }
                // set up a counter for how many characters are in the ticketid so far
                $i = 0;
                // add random characters to $password until $length is reached
                while ($i < $length) {
                    // pick a random character from the possible ones
                    $char = jssupportticketphplib::JSST_substr($possible, wp_rand(0, $maxlength - 1), 1);
                    if (!strstr($ticketid, $char)) {
                        if ($i == 0) {
                            if (ctype_alpha($char)) {
                                $ticketid .= $char;
                                $i++;
                            }
                        } else {
                            $ticketid .= $char;
                            $i++;
                        }
                    }
                }
            }else{ // Sequential ticketid
                if($ticketid == ""){
                    $ticketid = 0; // by default its set to zero
                }
                //$maxquery = "SELECT max(convert(ticketid, SIGNED INTEGER)) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`";
                $maxquery = "SELECT max(customticketno) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`";
                $maxticketid = jssupportticket::$_db->get_var($maxquery);
                if(is_numeric($maxticketid)){
                    $ticketid = $maxticketid + $count;
                }else{
                    $ticketid = $ticketid + $count;
                }
                $customticketno = $ticketid;
                $padding_zeros = JSSTincluder::getJSModel('configuration')->getConfigValue('padding_zeros_ticketid');

                $idlen = jssupportticketphplib::JSST_strlen($ticketid);
                while ($idlen < $padding_zeros) {
                    $ticketid = "0".$ticketid;
                    $idlen = jssupportticketphplib::JSST_strlen($ticketid);
                }
            }
			$prefix = "";
			$suffix = "";			
			$prefix = JSSTincluder::getJSModel('configuration')->getConfigValue('prefix_ticketid');
			$suffix = JSSTincluder::getJSModel('configuration')->getConfigValue('suffix_ticketid');
			$prefix = jssupportticketphplib::JSST_trim($prefix);
			$suffix = jssupportticketphplib::JSST_trim($suffix);
			if($prefix) $ticketid = $prefix . $ticketid;
			if($suffix) $ticketid = $ticketid . $suffix;
			
            $query = "SELECT count(ticketid) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE ticketid = '".esc_sql($ticketid) ."'";
            $row = jssupportticket::$_db->get_var($query);
            if($row > 0)
                $match = 'Y';
            else
                $match = 'N';
            /*
            $rows = jssupportticket::$_db->get_results($query);
                foreach ($rows as $row) {
                    if ($ticketid == $row->ticketid)
                        $match = 'Y';
                    else
                        $match = 'N';
                }
             */   
        }while ($match == 'Y');
        $result = array();
        $result['ticketid'] = $ticketid;
        $result['customticketno'] = $customticketno;
        return $result;
    }

    function countTicket($emailorid) {
        if (is_numeric($emailorid)) { // its UserID
            $counts = jssupportticket::$_db->get_var("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = " . esc_sql($emailorid));
        } else { // its EmailAddress
            $counts = jssupportticket::$_db->get_var("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = '" . esc_sql($emailorid) . "'");
        }
        return $counts;
    }

    function countOpenTicket($emailorid) {
        if (is_numeric($emailorid)) { // its UserID
            $counts = jssupportticket::$_db->get_var("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = " . esc_sql($emailorid) . " AND status != 4");
        } else { // its EmailAddress
            $counts = jssupportticket::$_db->get_var("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = '" . esc_sql($emailorid) . "' AND status != 4");
        }
        return $counts;
    }

    function checkBannedEmail($emailaddress) {
        if(!in_array('banemail', jssupportticket::$_active_addons)){
            return true;
        }
        $counts = jssupportticket::$_db->get_var("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = '" . esc_sql($emailaddress) . "'");
        if ($counts > 0) {
            $data['loggeremail'] = $emailaddress;
            $data['title'] = esc_html(__('Ban Email', 'js-support-ticket'));
            $data['log'] = esc_html(__('Ban email try to create ticket', 'js-support-ticket'));
            $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $currentUserName = $current_user->display_name;
            $data['logger'] = $currentUserName;
            $data['ipaddress'] = $this->getIpAddress();
            JSSTincluder::getJSModel('banemaillog')->storebanemaillog($data);
            JSSTmessage::setMessage(esc_html(__('Banned email cannot create ticket', 'js-support-ticket')), 'error');
            return false;
        }
        return true;
    }

    function getIpAddress() {
        //if client use the direct ip
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = jssupportticket::JSST_sanitizeData($_SERVER['HTTP_CLIENT_IP']); // JSST_sanitizeData() function uses wordpress santize functions
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = jssupportticket::JSST_sanitizeData($_SERVER['HTTP_X_FORWARDED_FOR']); // JSST_sanitizeData() function uses wordpress santize functions
        } else {
            $ip = jssupportticket::JSST_sanitizeData($_SERVER['REMOTE_ADDR']); // JSST_sanitizeData() function uses wordpress santize functions
        }
        return $ip;
    }



    function ticketValidate($emailaddress) {
        //check the banned user / email
        if(in_array('banemail', jssupportticket::$_active_addons)){
            if (!$this->checkBannedEmail($emailaddress)) {
                return false;
            }
        }
        if(in_array('maxticket', jssupportticket::$_active_addons)){
            //check the Maximum Tickets
            if (!JSSTincluder::getJSModel('maxticket')->checkMaxTickets($emailaddress)) {
                return false;
            }

            //check the Maximum Open Tickets

            if (!JSSTincluder::getJSModel('maxticket')->checkMaxOpenTickets($emailaddress)) {
                return false;
            }
        }

        return true;
    }

    function captchaValidate() {
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            if (jssupportticket::$_config['show_captcha_on_visitor_from_ticket'] == 1) {
                if (jssupportticket::$_config['captcha_selection'] == 1) { // Google recaptcha
                    $gresponse = jssupportticket::JSST_sanitizeData($_POST['g-recaptcha-response']); // JSST_sanitizeData() function uses wordpress santize functions
                    $resp = JSSTGoogleRecaptchaHTTPPost(jssupportticket::$_config['recaptcha_privatekey'],$gresponse);

                    if ($resp == true) {
                        return true;
                    } else {
                        # set the error code so that we can display it
                        JSSTmessage::setMessage(esc_html(__('Incorrect Captcha code', 'js-support-ticket')), 'error');
                        return false;
                    }
                } else { // own captcha
                    $captcha = new JSSTcaptcha;
                    $result = $captcha->checkCaptchaUserForm();
                    if ($result == 1) {
                        return true;
                    } else {
                        JSSTmessage::setMessage(esc_html(__('Incorrect Captcha code', 'js-support-ticket')), 'error');
                        return false;
                    }
                }
            }
        }
	return true;
    }

    function storeTickets($data) {

		$checkduplicatetk = $this->checkIsTicketDuplicate($data['subject'],$data['email']);
		if(!$checkduplicatetk){
			return false;
		}
        if($data['departmentid'] == ''){
            // auto assign
            $data['departmentid'] = JSSTincluder::getJSModel('department')->getDepartmentIDForAutoAssign();
        }

        if (!is_admin() && ( !isset($data['ticketviaemail']) || $data['ticketviaemail'] != 1) ) { //if not admin or Email Piping
            if (!$this->captchaValidate()) {
                //JSSTmessage::setMessage(esc_html(__('Incorrect Captcha code', 'js-support-ticket')), 'error');
                return false;
            }
            if (!$this->ticketValidate($data['email'])) {
                return 3;
            }
        }

        //paid support validation
        if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce')){
            //ignore if admin or agent or visitor
            if(!JSSTincluder::getObjectClass('user')->isguest() && !is_admin() && !(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())){
                $paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->wpuid(),$data['paidsupportid']);
                if(empty($paidsupport)){
                    JSSTmessage::setMessage(esc_html(__('Please select paid support item', 'js-support-ticket')), 'error');
                    return false;
                }
            }
        }

        $data['ticketviaemail'] = isset($data['ticketviaemail']) ? $data['ticketviaemail'] : 0;
        if($data['ticketviaemail'] != 1){ // do not check in ticket via email case
            //envato purchase code validation
            if(in_array('envatovalidation', jssupportticket::$_active_addons)){
                $code = $data['envatopurchasecode'];
                $pcode = isset($data['prev_envatopurchasecode']) ? $data['prev_envatopurchasecode'] : '';
                $required = JSSTincluder::getJSModel('configuration')->getConfigValue('envato_license_required');
                if($required!=1 && empty($code) && !empty($pcode)){
                    $envatoData = '';
                }
                if( (!empty($code) && (empty($pcode) || $pcode!=$code)) || ($required==1 && (empty($pcode) || $pcode!=$code)) ){
                    $res = JSSTincluder::getJSModel('envatovalidation')->validatePurchaseCode($data['envatopurchasecode']);
                    if(!$res){
                        JSSTmessage::setMessage(esc_html(__('No purchase found with that code', 'js-support-ticket')), 'error');
                        return false;
                    }else{
                        $envatoData = wp_json_encode($res);
                    }
                }
            }
        }

        // edd license
        if($data['ticketviaemail'] != 1){ // do not check in ticket via email case
            if(in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                if(jssupportticket::$_config['verify_license_on_ticket_creation'] == 1){
                    if(isset($data['eddlicensekey'])){
                        if($data['eddlicensekey'] == ''){
                            JSSTmessage::setMessage(esc_html(__('Provide a valid license key to create a ticket.', 'js-support-ticket')), 'error');
                            return false;
                        }else{
                            $l_result = JSSTincluder::getJSModel('easydigitaldownloads')->getEDDLicenseVerification($data['eddlicensekey']);
                            if($l_result == 'expired'){
                                JSSTmessage::setMessage(esc_html(__('Your license has expired.', 'js-support-ticket')), 'error');
                                return false;
                            }elseif($l_result == 'inactive'){
                                JSSTmessage::setMessage(esc_html(__('Your license is not active, activate your license.', 'js-support-ticket')), 'error');
                                return false;
                            }
                        }
                    }
                }
            }
        }

        $sendEmail = true;
        if ($data['id']) {
            $sendEmail = false;
            $updated = date_i18n('Y-m-d H:i:s');
            $created = $data['created'];
            if (isset($data['isoverdue']) &&  $data['isoverdue'] == 1) {// for edit case to change the overdue if criteria is passed
                $curdate = date_i18n('Y-m-d H:i:s');
                if (date_i18n('Y-m-d',strtotime($data['duedate'])) > date_i18n('Y-m-d',strtotime($curdate))){
                    $data['isoverdue'] = 0;
                }else{
                    $query = "SELECT ticket.duedate FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket WHERE ticket.id = ".esc_sql($data['id']);
                    $duedate = jssupportticket::$_db->get_var($query);
                    if(date_i18n('Y-m-d',strtotime($data['duedate'])) != date_i18n('Y-m-d',strtotime($duedate))){
                        JSSTticketModel::setMessage(esc_html(__('Due date error is not valid','js-support-ticket')),'error');
                        return; //Due Date must be greater then current date
                    }
                }
            }
            //to check hash
            $query = "SELECT hash,uid FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE ticketid='".esc_sql($data['ticketid'])."'";
            $row = jssupportticket::$_db->get_row($query);
            $edituid = $row->uid;
            if( $row->hash != $this->generateHash($data['id']) ){
                return false;
            }//end
        } else {
            $idresult = $this->getRandomTicketId();
            $data['ticketid'] = $idresult['ticketid'];
            $data['customticketno'] = $idresult['customticketno'];

            $data['attachmentdir'] = $this->getRandomFolderName();
            $created = date_i18n('Y-m-d H:i:s');
            $updated = '';
        }
        if(isset($data['assigntome']) && $data['assigntome'] == 1){
            if (in_array('agent',jssupportticket::$_active_addons)) {
                $uid = JSSTincluder::getObjectClass('user')->uid();
                $staffid = JSSTincluder::getJSModel('agent')->getStaffId($uid);
                $data['staffid'] = $staffid;
            }
        }else{
            $data['staffid'] = isset($data['staffid']) ? $data['staffid'] : '';
        }
        $data['status'] = isset($data['status']) ? $data['status'] : '';
        $data['duedate'] = isset($data['duedate']) ? date_i18n('Y-m-d',strtotime($data['duedate']))  : '';
        $data['lastreply'] = isset($data['lastreply']) ? $data['lastreply'] : '';
        $data['message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($data['jsticket_message']); // use jsticket_message to avoid conflict
		$jsticket_message = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($data['message']);
        $jsticket_message = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsticket_message);
        if(empty($data['message'])){
            JSSTmessage::setMessage(esc_html(__('Message field cannot be empty', 'js-support-ticket')), 'error');
            return false;
        }
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        if(isset($envatoData)){
            $data['envatodata'] = $envatoData;
        }
        //custom field code start
        $customflagforadd = false;
        $customflagfordelete = false;
        $custom_field_namesforadd = array();
        $custom_field_namesfordelete = array();
		//if(!isset($data['multiformid'])) $data['multiformid'] = ""; may a fix
        $userfield = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1,$data['multiformid']);
        $params = array();
        $maxfilesizeallowed = jssupportticket::$_config['file_maximum_size'];
        foreach ($userfield AS $ufobj) {
            $vardata = '';
            if($ufobj->userfieldtype == 'file'){
                if(isset($data[$ufobj->field.'_1']) && $data[$ufobj->field.'_1']== 0){
                    $vardata = $data[$ufobj->field.'_2'];
                }
                $customflagforadd=true;
                $custom_field_namesforadd[]=$ufobj->field;
            }else if($ufobj->userfieldtype == 'date'){
                $vardata = isset($data[$ufobj->field]) ? gmdate("Y-m-d", jssupportticketphplib::JSST_strtotime($data[$ufobj->field])) : '';
            }else{
                $vardata = isset($data[$ufobj->field]) ? $data[$ufobj->field] : '';
            }
            if(isset($data[$ufobj->field.'_1']) && $data[$ufobj->field.'_1'] == 1){
                $customflagfordelete = true;
                $custom_field_namesfordelete[]= $data[$ufobj->field.'_2'];
            }
            if($vardata != ''){

                if(is_array($vardata)){
                    $vardata = implode(', ', array_filter($vardata));
                }
                $params[$ufobj->field] = jssupportticketphplib::JSST_htmlentities($vardata);
            }
        }
        if($data['id'] != ''){
            if(is_numeric($data['id'])){
                $query = "SELECT params FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($data['id']);
                $oParams = jssupportticket::$_db->get_var($query);

                if(!empty($oParams)){
                    $oParams = json_decode($oParams,true);
                    $unpublihsedFields = JSSTincluder::getJSModel('fieldordering')->getUserUnpublishFieldsfor(1);
                    foreach($unpublihsedFields AS $field){
                        if(isset($oParams[$field->field])){
                            $params[$field->field] = $oParams[$field->field];
                        }
                    }
                }
            }
        }
        $params = html_entity_decode(wp_json_encode($params, JSON_UNESCAPED_UNICODE));
        $data['params'] = $params;
        //custom field code end

		$data['message'] = $jsticket_message;
        $data['created'] = $created;
        $data['updated'] = $updated;


        if($data['uid'] == 0 && isset($_SESSION['js-support-ticket']['notificationid'])){
            $data['notificationid'] = jssupportticket::JSST_sanitizeData($_SESSION['js-support-ticket']['notificationid']); // JSST_sanitizeData() function uses wordpress santize functions
        }

        if($data['id']){
           $data['uid'] = $edituid;
        }
        $sendnotification = false;
        $row = JSSTincluder::getJSTable('tickets');
		// this line make problem with custom field data (latin words)
        //$data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
        $error = 0;
        if (!$row->bind($data)) {
            $error = 1;
        }
        if (!$row->store()) {
            $error = 1;
        }

        if ($error == 1) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
            JSSTmessage::setMessage(esc_html(__('Ticket has not been created', 'js-support-ticket')), 'error');
        } else {
            $ticketid = $row->id;
            $sendnotification = true;
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));

            //update hash value against ticket
            $hash = $this->generateHash($ticketid);
            $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET `hash`='".esc_sql($hash)."' WHERE id=".esc_sql($ticketid);
            jssupportticket::$_db->query($query);

            // Storing Attachments
			$data['ticketid'] = $ticketid;
			if($data['ticketviaemail'] != 1){ // since ticket via emial attacments are handled saprately
			   JSSTincluder::getJSModel('attachment')->storeAttachments($data);
			   JSSTmessage::setMessage(esc_html(__('Ticket created', 'js-support-ticket')), 'updated');

			   //removing custom field attachments
                if($customflagfordelete == true){
				    foreach ($custom_field_namesfordelete as $key) {
					   $res = $this->removeFileCustom($ticketid,$key);
				    }
	            }
                //storing custom field attachments
                if($customflagforadd == true){
			        foreach ($custom_field_namesforadd as $key) {
                        if ($_FILES[$key]['size'] > 0) { // logo
	                       $res = $this->uploadFileCustom($ticketid,$key);
				        }
				    }
                }

                //update paid support item tickets
                if(isset($paidsupport)){
                    $paidsupport = $paidsupport[0];
                    $res = JSSTincluder::getJSModel('paidsupport')->recordTicket($paidsupport->itemid, $ticketid);
                    if($res){
                        $t = JSSTincluder::getJSTable('tickets');
                        if($t->bind(array('id'=>$ticketid,'paidsupportitemid'=>$paidsupport->itemid))){
                            $t->store();
                        }
                    }
                }

			}
        }
        do_action('jsst_after_ticket_create',$data,$ticketid);
        

        /* Push Notification */
        if($data['id'] == '' && $sendnotification == true && in_array('notification', jssupportticket::$_active_addons)){
            $dataarray = array();
            $dataarray['title'] = $data['subject'];
            $dataarray['body'] = esc_html(__("created","js-support-ticket"));

            //send notification to admin
            $devicetoken = JSSTincluder::getJSModel('notification')->checkSubscriptionForAdmin();
            if($devicetoken){
                $dataarray['link'] = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=".$ticketid);
                $dataarray['devicetoken'] = $devicetoken;
                $value = jssupportticket::$_config[md5(JSTN)];
                if($value != ''){
                  do_action('send_push_notification',$dataarray);
                }else{
                  do_action('resetnotificationvalues');
                }
            }

            $dataarray['link'] = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', "jssupportticketid"=>$ticketid,'jsstpageid'=>jssupportticket::getPageid()));
            // for department staff
            JSSTincluder::getJSModel('notification')->sendNotificationToDepartment($data['departmentid'],$dataarray);
            // for all
            if($data['departmentid'] == ''){
                JSSTincluder::getJSModel('notification')->sendNotificationToAllStaff($dataarray);
            }

            // send notification to uid(ticket create for)
            if($data['uid'] > 0 && is_numeric($data['uid']) && ($data['uid'] != JSSTincluder::getObjectClass('user')->uid())){
                $devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($data['uid']);
                $dataarray['devicetoken'] = $devicetoken;
                if($devicetoken != '' && !empty($devicetoken)){
                    $value = jssupportticket::$_config[md5(JSTN)];
                    if($value != ''){
                      do_action('send_push_notification',$dataarray);
                    }else{
                      do_action('resetnotificationvalues');
                    }
                }
            }else if($data['uid'] == 0 && isset($data['notificationid']) && $data['notificationid'] != ""){ //visitor
                $tokenarray['emailaddress'] = $data['email'];
                $tokenarray['trackingid'] = $data['ticketid'];
                $tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                $token = wp_json_encode($tokenarray);
                include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                $encoder = new JSSTEncoder();
                $encryptedtext = $encoder->encrypt($token);
                $dataarray['link'] = jssupportticket::makeUrl(array('jstmod'=>'ticket' ,'task'=>'showticketstatus','action'=>'jstask','token'=>$encryptedtext,'jsstpageid'=>jssupportticket::getPageid()));
                $devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($data['notificationid'],0);
                $dataarray['devicetoken'] = $devicetoken;
                if($devicetoken != '' && !empty($devicetoken)){
                    $value = jssupportticket::$_config[md5(JSTN)];
                    if($value != ''){
                      do_action('send_push_notification',$dataarray);
                    }else{
                      do_action('resetnotificationvalues');
                    }
                }
            }

        }


        /* for activity log */
        if (!JSSTincluder::getObjectClass('user')->isguest()) {
            $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $currentUserName = $current_user->display_name;
        }else{
            $currentUserName = esc_html(__('Guest','js-support-ticket'));
        }
        $eventtype = esc_html(__('New ticket', 'js-support-ticket'));
        if ($data['id']) {
            $message = esc_html(__('Ticket is updated by', 'js-support-ticket')) . " ( " . $currentUserName . " ) ";
        } else {
            $message = esc_html(__('Ticket is created by', 'js-support-ticket')) . " ( " . $currentUserName . " ) ";
        }
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
        }

        // Send Emails
        if ($sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 1, $ticketid); // Mailfor, Create Ticket, Ticketid
            //For Hook
            $ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($ticketid));
            do_action('jsst-ticketcreate', $ticketobject);
        }
        /* to store internal notes */
        if(in_array('note', jssupportticket::$_active_addons)){
            if (isset($data['internalnote']) && $data['internalnote'] != '') {
                JSSTincluder::getJSModel('note')->storeTicketInternalNote($data, $data['internalnote']);
            }
        }
        /* agent auto assign */
        do_action('jsst-agentautoassign', $ticketid);
        return $ticketid;
    }

    function uploadFileCustom($id,$field){
        JSSTincluder::getObjectClass('uploads')->storeTicketCustomUploadFile($id,$field);
    }

    function storeUploadFieldValueInParams($ticketid,$filename,$field){
        $query = "SELECT params FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id = ".esc_sql($ticketid);
        $params = jssupportticket::$_db->get_var($query);
        $decoded_params = json_decode($params,true);
        $decoded_params[$field] = $filename;
        $encoded_params = wp_json_encode($decoded_params, JSON_UNESCAPED_UNICODE);
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET params = '" . esc_sql($encoded_params) . "' WHERE id = " . esc_sql($ticketid);
        jssupportticket::$_db->query($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function removeTicket($id) {
        $sendEmail = true;
        if (!is_numeric($id))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
            if ($allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }

        if ($this->canRemoveTicket($id)) {
            jssupportticket::$_data['ticketid'] = $this->getTrackingIdById($id);
            jssupportticket::$_data['ticketemail'] = $this->getTicketEmailById($id);
            jssupportticket::$_data['staffid'] = $this->getStaffIdById($id);
            jssupportticket::$_data['ticketsubject'] = $this->getTicketSubjectById($id);
            // delete attachments
            $this->removeTicketAttachmentsByTicketid($id);

            $row = JSSTincluder::getJSTable('tickets');
            if ($row->delete($id)) {
                $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
                JSSTmessage::setMessage(esc_html(__('Ticket has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Ticket has not been deleted', 'js-support-ticket')), 'error');
                $messagetype = esc_html(__('Error', 'js-support-ticket'));
                $sendEmail = false;
            }

            // Send Emails
            if ($sendEmail == true) {
                JSSTincluder::getJSModel('email')->sendMail(1, 3); // Mailfor, Delete Ticket
                $ticketobject = (object) array('ticketid' => jssupportticket::$_data['ticketid'], 'ticketemail' => jssupportticket::$_data['ticketemail']);
                do_action('jsst-ticketdelete', $ticketobject);
            }
            if(in_array('note', jssupportticket::$_active_addons)){
                // delete internal notes
                JSSTincluder::getJSModel('note')->removeTicketInternalNote($id);
            }
            // delete replies
            JSSTincluder::getJSModel('reply')->removeTicketReplies($id);
        } else {
            JSSTmessage::setMessage(esc_html(__('Ticket','js-support-ticket')).' '. esc_html(__('in use cannot be deleted', 'js-support-ticket')), 'error');
        }

        return;
    }

    function removeEnforceTicket($id) {
        $sendEmail = true;
        if (!is_numeric($id))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
            if ($allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }

        jssupportticket::$_data['ticketid'] = $this->getTrackingIdById($id);
        jssupportticket::$_data['ticketemail'] = $this->getTicketEmailById($id);
        jssupportticket::$_data['staffid'] = $this->getStaffIdById($id);
        jssupportticket::$_data['ticketsubject'] = $this->getTicketSubjectById($id);
		// delete attachments
		$this->removeTicketAttachmentsByTicketid($id);

        $row = JSSTincluder::getJSTable('tickets');
        if ($row->delete($id)) {
		// delete attachments
		//$this->removeTicketAttachmentsByTicketid($id);
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
            JSSTmessage::setMessage(esc_html(__('Ticket has been deleted', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Ticket has not been deleted', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
        }

        // Send Emails
        if ($sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 3); // Mailfor, Delete Ticket
            $ticketobject = (object) array('ticketid' => jssupportticket::$_data['ticketid'], 'ticketemail' => jssupportticket::$_data['ticketemail']);
            do_action('jsst-ticketdelete', $ticketobject);
        }
        if(in_array('note', jssupportticket::$_active_addons)){
            // delete internal notes
            JSSTincluder::getJSModel('note')->removeTicketInternalNote($id);
        }
        // delete replies
        JSSTincluder::getJSModel('reply')->removeTicketReplies($id);

        return;
    }

    private function removeTicketAttachmentsByTicketid($id){
		if(!is_numeric($id)) return false;
		$datadirectory = jssupportticket::$_config['data_directory'];
		$maindir = wp_upload_dir();
		$mainpath = $maindir['basedir'];
		$mainpath = $mainpath .'/'.$datadirectory;
		$mainpath = $mainpath . '/attachmentdata';
		$query = "SELECT ticket.attachmentdir
					FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket
					WHERE ticket.id = ".esc_sql($id);
		$foldername = jssupportticket::$_db->get_var($query);
		if(!empty($foldername)){
			$folder = $mainpath . '/ticket/'.$foldername;
            if(file_exists($folder)){
    			$path = $mainpath . '/ticket/'.$foldername.'/*.*';
    			$files = glob($path);
    			array_map('unlink', $files);//deleting files
    			rmdir($folder);
    			$query = "DELETE FROM `".jssupportticket::$_db->prefix."js_ticket_attachments` WHERE ticketid = ".esc_sql($id);
    			jssupportticket::$_db->query($query);
            }
		}
	}

    private function canRemoveTicket($id) {
        if (!is_numeric($id))
            return false;
        if (!$this->canUserPerformThisAction($id)) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed','js-support-ticket')), 'error');
            return false;
        }
        $query = "SELECT (
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid = " . esc_sql($id) . ") ";
                    if(in_array('note', jssupportticket::$_active_addons)){
                        $query .= " +(SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_notes` WHERE ticketid = " . esc_sql($id) . ") ";
                    }
                    $query .= "
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

    function canUserPerformThisAction($id) {
        if (!is_numeric($id))
            return false;
        if (!is_admin()) {
			if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
				$allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
				if ($allowed == true) {
					return true;
				}
			}
            $query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
            $uid = jssupportticket::$_db->get_var($query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            $ticketUid = $this->getTicketUidById($id);
            $currentuserid = JSSTincluder::getObjectClass('user')->uid();
            if ($currentuserid != $ticketUid){
                return false;
            }
        }
        return true;
    }

    function getTicketUidById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
        $uid = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $uid;
    }

    function getTicketSubjectById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT subject FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
        $subject = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $subject;
    }

    function getTrackingIdById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT ticketid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
        $ticketid = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $ticketid;
    }

    function getTicketEmailById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT email FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
        $ticketemail = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $ticketemail;
    }

    function getStaffIdById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT staffid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
        $staffid = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $staffid;
    }

    function setStatus($status, $ticketid) {
        // 0 -> New Ticket
        // 1 -> Waiting admin/staff reply
        // 2 -> in progress
        // 3 -> waiting for customer reply
        // 4 -> close ticket
        if (!is_numeric($status))
            return false;
        if (!is_numeric($ticketid))
            return false;
        $row = JSSTincluder::getJSTable('tickets');
        if (!$row->update(array('id' => $ticketid, 'status' => $status))) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }
    function getLastReply($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT reply.message FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS reply WHERE reply.ticketid = " . esc_sql($id) . " ORDER BY reply.created DESC LIMIT 1";
        $message =jssupportticket::$_db->query($query);
        return $message;
    }
    function updateLastReply($id) {
        if (!is_numeric($id))
            return false;
        $date = date_i18n('Y-m-d H:i:s');
        $isanswered = " , isanswered = 0 ";
        if ( is_admin() || ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ) {
            $isanswered = " , isanswered = 1 ";
        }
        $query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET lastreply = '" . esc_sql($date) . "' " . $isanswered . " WHERE id = " . esc_sql($id);
        jssupportticket::$_db->query($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function closeTicket($id ,$cron_flag = 0) { // second parameter is for crown call(when crown job is executed to hanled close ticket configuration)
        if (!is_numeric($id))
            return false;
        if($cron_flag == 0){
            //Check if its allowed to close ticket
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Close Ticket');
                if ($allowed != true) {
                    JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                    return;
                }
            } else {
                if(!current_user_can('manage_options')){
                    // in case of user check for ticket owner
                    $current_uid = JSSTincluder::getObjectClass('user')->uid();
                    $ticket_uid = JSSTincluder::getJSModel('ticket')->getUIdById($id);
                    if ($current_uid != $ticket_uid) {
                        return;
                    }
                }
            }
        }
        if (!$this->checkActionStatusSame($id, array('action' => 'closeticket'))) {
            JSSTmessage::setMessage(esc_html(__('Ticket already closed', 'js-support-ticket')), 'error');
            return;
        }
        $sendEmail = true;
        $date = date_i18n('Y-m-d H:i:s');
        if($cron_flag == 0){
            $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user id
            $closedby = isset($current_user->display_name) ? $current_user->id : -1;
        }else{
            $closedby = 0;
        }


        $row = JSSTincluder::getJSTable('tickets');
        if ($row->update(array('id' => $id, 'status' => 4, 'closed' => $date, 'closedby' => $closedby, 'isoverdue' => 0))) {

            JSSTmessage::setMessage(esc_html(__('Ticket has been closed', 'js-support-ticket')), 'updated');
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Ticket has not been closed', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
        }

        /* for activity log */
        $ticketid = $id; // get the ticket id
        if($cron_flag == 0){
            $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $currentUserName = isset($current_user->display_name) ? $current_user->display_name : esc_html(__('Guest', 'js-support-ticket'));
        }else{
            $currentUserName = esc_html(__('System', 'js-support-ticket'));
        }
        $eventtype = esc_html(__('Close Ticket', 'js-support-ticket'));
        $message = esc_html(__('Ticket is closed by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
        }

        // Send Emails
        if ($sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 2, $ticketid); // Mailfor, Close Ticket, Ticketid
            $ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($ticketid));
            do_action('jsst-ticketclose', $ticketobject);
        }
        // on ticket close make remove credentails data and show messsage on retrive.
        if(in_array('privatecredentials',jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('privatecredentials')->deleteCredentialsOnCloseTicket($ticketid);
        }
        return;
    }

    function getTicketListOrdering($sort) {
        switch ($sort) {
            case "subjectdesc":
                jssupportticket::$_ordering = "ticket.subject DESC";
                jssupportticket::$_sorton = "subject";
                jssupportticket::$_sortorder = "DESC";
                break;
            case "subjectasc":
                jssupportticket::$_ordering = "ticket.subject ASC";
                jssupportticket::$_sorton = "subject";
                jssupportticket::$_sortorder = "ASC";
                break;
            case "prioritydesc":
                jssupportticket::$_ordering = "priority.ordering DESC";
                jssupportticket::$_sorton = "priority";
                jssupportticket::$_sortorder = "DESC";
                break;
            case "priorityasc":
                jssupportticket::$_ordering = "priority.ordering ASC";
                jssupportticket::$_sorton = "priority";
                jssupportticket::$_sortorder = "ASC";
                break;
            case "ticketiddesc":
                jssupportticket::$_ordering = "ticket.ticketid DESC";
                jssupportticket::$_sorton = "ticketid";
                jssupportticket::$_sortorder = "DESC";
                break;
            case "ticketidasc":
                jssupportticket::$_ordering = "ticket.ticketid ASC";
                jssupportticket::$_sorton = "ticketid";
                jssupportticket::$_sortorder = "ASC";
                break;
            case "isanswereddesc":
                jssupportticket::$_ordering = "ticket.isanswered DESC";
                jssupportticket::$_sorton = "isanswered";
                jssupportticket::$_sortorder = "DESC";
                break;
            case "isansweredasc":
                jssupportticket::$_ordering = "ticket.isanswered ASC";
                jssupportticket::$_sorton = "isanswered";
                jssupportticket::$_sortorder = "ASC";
                break;
            case "statusdesc":
                jssupportticket::$_ordering = "ticket.status DESC";
                jssupportticket::$_sorton = "status";
                jssupportticket::$_sortorder = "DESC";
                break;
            case "statusasc":
                jssupportticket::$_ordering = "ticket.status ASC";
                jssupportticket::$_sorton = "status";
                jssupportticket::$_sortorder = "ASC";
                break;
            case "createddesc":
                jssupportticket::$_ordering = "ticket.created DESC";
                jssupportticket::$_sorton = "created";
                jssupportticket::$_sortorder = "DESC";
                break;
            case "createdasc":
                jssupportticket::$_ordering = "ticket.created ASC";
                jssupportticket::$_sorton = "created";
                jssupportticket::$_sortorder = "ASC";
                break;
            default:
                $sortbyconfig = jssupportticket::$_config['tickets_sorting'];
                if($sortbyconfig == 1){
                    $sortbyconfig = "ASC";
                }else{
                    $sortbyconfig = "DESC";
                }
                jssupportticket::$_ordering = "ticket.id $sortbyconfig";
            break;
        }
        return;
    }

    function getSortArg($type, $sort) {
        $mat = array();
        if (preg_match("/(\w+)(asc|desc)/i", $sort, $mat)) {
            if ($type == $mat[1]) {
                return ( $mat[2] == "asc" ) ? "{$type}desc" : "{$type}asc";
            } else {
                return $type . $mat[2];
            }
        }
        $sortlink = "id";
        // default sorting
        $sortbyconfig = jssupportticket::$_config['tickets_sorting'];
        if($sortbyconfig == 1){
            $sortbyconfig = "asc";
        }else{
            $sortbyconfig = "desc";
        }
        $sortlink = $sortlink.$sortbyconfig;

        return $sortlink;
    }

    function getTicketListSorting($sort) {
        jssupportticket::$_sortlinks['subject'] = $this->getSortArg("subject", $sort);
        jssupportticket::$_sortlinks['priority'] = $this->getSortArg("priority", $sort);
        jssupportticket::$_sortlinks['ticketid'] = $this->getSortArg("ticketid", $sort);
        jssupportticket::$_sortlinks['isanswered'] = $this->getSortArg("isanswered", $sort);
        jssupportticket::$_sortlinks['status'] = $this->getSortArg("status", $sort);
        jssupportticket::$_sortlinks['created'] = $this->getSortArg("created", $sort);
        return;
    }

    private function getTicketHistory($id) {
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            if(!is_numeric($id)) return false;
            $query = "SELECT al.id,al.message,al.datetime,al.uid
            from `" . jssupportticket::$_db->prefix . "js_ticket_activity_log`  AS al
            join `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS tic on al.referenceid=tic.id
            where al.referenceid=" . esc_sql($id) . " AND al.eventfor=1 ORDER BY al.datetime DESC ";
            jssupportticket::$_data[5] = jssupportticket::$_db->get_results($query);
        }else{
            jssupportticket::$_data[5] = array();
        }
    }

    function tickDepartmentTransfer($data) {
        $ticketid = $data['ticketid'];
        if (!is_numeric($ticketid))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Department Transfer');
            if ($allow != true) {
                JSSTmessage::setMessage(esc_html(__('Your are not allowed', 'js-support-ticket')), 'updated');
                return;
            }
        }
        $sendEmail = true;
        $date = date_i18n('Y-m-d H:i:s');

        $row = JSSTincluder::getJSTable('tickets');
        if ($row->update(array('id' => $ticketid, 'departmentid' => $data['departmentid'], 'updated' => $date))) {
            JSSTmessage::setMessage(esc_html(__('The department has been transferred', 'js-support-ticket')), 'updated');
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The department has not been transferred', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
        }

        /* for activity log */
        $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $currentUserName = $current_user->display_name;
        $eventtype = esc_html(__('Ticket department transfer', 'js-support-ticket'));
        $message = esc_html(__('The department is transferred by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
        }

        // Send Emails
        if ($sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 12, $ticketid); // Mailfor, Department Ticket, Ticketid
            $ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($ticketid));
            do_action('jsst-ticketclose', $ticketobject);
        }

        /* to store internal notes FOR department transfer  */
        if (isset($data['departmenttranfernote']) && $data['departmenttranfernote'] != '') {
            JSSTincluder::getJSModel('note')->storeTicketInternalNote($data, $data['departmenttranfernote']);
        }
        return;
    }

    function assignTicketToStaff($data) {
        $ticketid = $data['ticketid'];
        if (!is_numeric($ticketid))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Assign Ticket To Agent');
            if ($allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        $sendEmail = true;
        $date = date_i18n('Y-m-d H:i:s');

        $row = JSSTincluder::getJSTable('tickets');
        if ($row->update(array('id' => $ticketid, 'staffid' => $data['staffid'], 'updated' => $date))) {
            JSSTmessage::setMessage(esc_html(__('Assigned to agent', 'js-support-ticket')), 'updated');
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Not assigned to agent', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
        }

        /* for activity log */
        $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $currentUserName = isset($current_user->display_name) ? $current_user->display_name : esc_html(__('Guest', 'js-support-ticket'));
        $eventtype = esc_html(__('Assign ticket to agent', 'js-support-ticket'));
        $message = esc_html(__('Ticket is assigned to agent by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
        }

        // Send Emails
        if ($sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 13, $ticketid); // Mailfor, Assign Ticket, Ticketid
            $ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($ticketid));
            do_action('jsst-ticketclose', $ticketobject);
        }

        /* to store internal notes FOR department transfer  */
        if(in_array('note', jssupportticket::$_active_addons)){
            if (isset($data['assignnote']) && $data['assignnote'] != '') {
                JSSTincluder::getJSModel('note')->storeTicketInternalNote($data, $data['assignnote']);
            }
        }
        return;
    }

    function changeTicketPriority($id, $priorityid) {
        if (!is_numeric($id))
            return false;
        if (!is_numeric($priorityid))
            return false;
        if (!$this->checkActionStatusSame($id, array('action' => 'priority', 'id' => $priorityid))) {
            JSSTmessage::setMessage(esc_html(__('Ticket already have same priority', 'js-support-ticket')), 'error');
            return;
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Change Ticket Priority');
            if ($allow == 0) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        $sendEmail = true;
        $date = date_i18n('Y-m-d H:i:s');

        $row = JSSTincluder::getJSTable('tickets');
        if ($row->update(array('id' => $id, 'priorityid' => $priorityid, 'updated' => $date))) {
            JSSTmessage::setMessage(esc_html(__('Priority has been changed', 'js-support-ticket')), 'updated');
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Priority has not been changed', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
        }

        /* for activity log */
        $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $currentUserName = $current_user->display_name;
        $eventtype = esc_html(__('Change Priority', 'js-support-ticket'));
        $message = esc_html(__('Ticket priority is changed by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($id, 1, $eventtype, $message, $messagetype);
        }
        // Send Emails
        if ($sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 11, $id, 'js_ticket_tickets'); // Mailfor, Ban email, Ticketid
        }
        return;
    }

    function banEmail($data) {
        if(!in_array('banemail', jssupportticket::$_active_addons)){
            return false;
        }
        $ticketid = $data['ticketid'];
        $uid = JSSTincluder::getObjectClass('user')->uid();
        if(in_array('agent',jssupportticket::$_active_addons)){
            $staffid = JSSTincluder::getJSModel('agent')->getstaffid($uid);
        }else{
            $staffid = '';
        }
        if (!is_numeric($ticketid))
            return false;
        if(!is_admin()){
            if (!is_numeric($staffid))
                return false;
        }

        $email = self::getTicketEmailById($ticketid);
        if (!$this->checkActionStatusSame($ticketid, array('action' => 'banemail', 'email' => $email))) {
            JSSTmessage::setMessage(esc_html(__('Email already banned', 'js-support-ticket')), 'error');
            return;
        }

        $sendEmail = true;
        $data = array(
            'email' => $email,
            'submitter' => $staffid,
            'uid' => $uid,
            'created' => date_i18n('Y-m-d H:i:s')
        );

        $row = JSSTincluder::getJSTable('banemail');

        $data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data);// remove slashes with quotes.
        $error = 0;
        if (!$row->bind($data)) {
            $error = 1;
        }
        if (!$row->store()) {
            $error = 1;
        }
        if ($error == 0) {

            JSSTmessage::setMessage(esc_html(__('The email has been banned', 'js-support-ticket')), 'updated');
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The email has not been banned', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
        }

        /* for activity log */
        $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $currentUserName = $current_user->display_name;
        $eventtype = esc_html(__('Ban Email', 'js-support-ticket'));
        $message = esc_html(__('Email is banned by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
        }

        // Send Emails
        if ($sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(2, 1, $ticketid, 'js_ticket_tickets'); // Mailfor, Ban email, Ticketid
            $ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($ticketid));
            do_action('jsst-ticketclose', $ticketobject);
        }
        return;
    }



    function sendFeedbackMailByTicketid($ticketid) {

        if (!is_numeric($ticketid))
            return false;

        $date = date_i18n('Y-m-d H:i:s');

        $row = JSSTincluder::getJSTable('tickets');
        if ($row->update(array('id' => $ticketid, 'feedbackemail' => 1))) {
            JSSTincluder::getJSModel('email')->sendMail(1, 15, $ticketid); // Mailfor, feedback for Ticket, Ticketid
        }
        return;
    }

    function banEmailAndCloseTicket($data) {
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ban Email And Close Ticket');
            if ($allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        self::banEmail($data);
        self::closeTicket($data['ticketid']);
        return;
    }

    /* check can a ticket be opened with in the given days */

    function checkCanReopenTicket($ticketid) {
        if (!is_numeric($ticketid))
            return false;
        $lastreply = JSSTincluder::getJSModel('reply')->getLastReply($ticketid);
        if (!$lastreply)
            $lastreply = date_i18n('Y-m-d H:i:s');
        $days = jssupportticket::$_config['reopen_ticket_within_days'];
        $date = gmdate("Y-m-d H:i:s", jssupportticketphplib::JSST_strtotime(gmdate("Y-m-d H:i:s", jssupportticketphplib::JSST_strtotime($lastreply)) . " +" . esc_html($days) . " day"));
        if ($date < date_i18n('Y-m-d H:i:s'))
            return false;
        else
            return true;
    }

    function reopenTicket($data) {
        $ticketid = $data['ticketid'];
        $lastreply = isset($data['lastreplydate']) ? $data['lastreplydate'] : '';
        if (!is_numeric($ticketid))
            return false;
        //check the permission to reopen ticket
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Reopen Ticket');
            if ($allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        } else {
            if(!current_user_can('manage_options')){
                // in case of user check for ticket owner
                $current_uid = JSSTincluder::getObjectClass('user')->uid();
                $ticket_uid = JSSTincluder::getJSModel('ticket')->getUIdById($ticketid);
                if ($current_uid != $ticket_uid) {
                    return;
                }
            }
        }
        /* check can a ticket be opened with in the given days */
        if ($this->checkCanReopenTicket($ticketid)) {
            $sendEmail = true;
            $date = date_i18n('Y-m-d H:i:s');

            $row = JSSTincluder::getJSTable('tickets');
            if ($row->update(array('id' => $ticketid, 'status' =>0, 'updated' => $date))) {
                JSSTmessage::setMessage(esc_html(__('The ticket has been reopened', 'js-support-ticket')), 'updated');
                $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('The ticket has not been reopened', 'js-support-ticket')), 'error');
                $messagetype = esc_html(__('Error', 'js-support-ticket'));
                $sendEmail = false;
            }

            /* for activity log */
            $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $currentUserName = isset($current_user->display_name) ? $current_user->display_name : esc_html(__('Guest', 'js-support-ticket'));
            $eventtype = esc_html(__('Reopen Ticket', 'js-support-ticket'));
            $message = esc_html(__('The ticket is reopened by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
            if(in_array('tickethistory', jssupportticket::$_active_addons)){
                JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
            }
            /*
              // Send Emails
              if ($sendEmail == true) {
              JSSTincluder::getJSModel('email')->sendMail(1, 2, $ticketid); // Mailfor, Close Ticket, Ticketid
              $ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($ticketid));
              do_action('jsst-ticketclose', $ticketobject);
              }
             */
        } else {
            JSSTmessage::setMessage(esc_html(__('The ticket reopens time limit end', 'js-support-ticket')), 'error');
        }


        return;
    }

    private function canUnbanEmail($email) {
        $query = " SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = '" . esc_sql($email) . "' ";
        $result = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($result > 0)
            return true;
        else
            return false;
    }

    function unbanEmail($data) {
        $ticketid = $data['ticketid'];
        if (!is_numeric($ticketid))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Unban Email');
            if ($allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        $email = self::getTicketEmailById($ticketid);
        if ($this->canUnbanEmail($email)) {
            $sendEmail = true;
            $date = date_i18n('Y-m-d H:i:s');
            $query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = '" . esc_sql($email) . " ' ";
            jssupportticket::$_db->query($query);
            if (jssupportticket::$_db->last_error == null) {
                JSSTmessage::setMessage(esc_html(__('Email has been unbanned', 'js-support-ticket')), 'updated');
                $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Email has not been unbanned', 'js-support-ticket')), 'error');
                $messagetype = esc_html(__('Error', 'js-support-ticket'));
                $sendEmail = false;
            }

            /* for activity log */
            $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $currentUserName = $current_user->display_name;
            $eventtype = esc_html(__('Unbanned Email', 'js-support-ticket'));
            $message = esc_html(__('Email is unbanned by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
            if(in_array('tickethistory', jssupportticket::$_active_addons)){
                JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
            }

            // Send Emails
            if ($sendEmail == true) {
                JSSTincluder::getJSModel('email')->sendMail(2, 2, $ticketid, 'js_ticket_tickets'); // Mailfor, Unban Ticket, Ticketid
                $ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($ticketid));
                do_action('jsst-ticketclose', $ticketobject);
            }
        } else {
            JSSTmessage::setMessage(esc_html(__('Email cannot be unbanned', 'js-support-ticket')), 'error');
        }

        return;
    }

    function markTicketInProgress($data) {
        $ticketid = $data['ticketid'];
        if (!is_numeric($ticketid))
            return false;
        if (!$this->checkActionStatusSame($ticketid, array('action' => 'markinprogress'))) {
            JSSTmessage::setMessage(esc_html(__('Ticket already marked in progress', 'js-support-ticket')), 'error');
            return;
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Mark In Progress');
            if ($allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        $date = date_i18n('Y-m-d H:i:s');
        $sendEmail = true;

        $row = JSSTincluder::getJSTable('tickets');
        if ($row->update(array('id' => $ticketid, 'status' => 2, 'updated' => $date))) {
            JSSTmessage::setMessage(esc_html(__('The ticket has been marked as in progress', 'js-support-ticket')), 'updated');
            $messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The ticket has not been marked as in progress', 'js-support-ticket')), 'error');
            $messagetype = esc_html(__('Error', 'js-support-ticket'));
            $sendEmail = false;
        }

        /* for activity log */
        $current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $currentUserName = $current_user->display_name;
        $eventtype = esc_html(__('In progress ticket', 'js-support-ticket'));
        $message = esc_html(__('The ticket is marked as in progress by', 'js-support-ticket')) . " ( " . esc_html($currentUserName) . " ) ";
        if(in_array('tickethistory', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($ticketid, 1, $eventtype, $message, $messagetype);
        }

        // Send Emails
        if ($sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 9, $ticketid, 'js_ticket_tickets'); // Mailfor, Unban Ticket, Ticketid
            $ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($ticketid));
            do_action('jsst-ticketclose', $ticketobject);
        }
        return;
    }

    function updateTicketStatusCron() {
        // close ticket
        if(in_array('autoclose', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('autoclose')->autoCloseTicketsCron();
        }

        if(in_array('overdue', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('overdue')->markTicketOverdueCron();
        }
    }

    function sendFeedbackMail() {
        if(!in_array('feedback', jssupportticket::$_active_addons)){
            return;
        }
        if(jssupportticket::$_config['feedback_email_delay_type'] == 1){
            $intrval_string = " date(DATE_ADD(closed,INTERVAL " . (int)jssupportticket::$_config['feedback_email_delay']." DAY)) < '".gmdate("Y-m-d")."'";
        }else{
            $intrval_string = " DATE_ADD(closed,INTERVAL " .(int) jssupportticket::$_config['feedback_email_delay'] . " HOUR) < '".date_i18n("Y-m-d H:i:s")."'";
        }
        // select closed ticket
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE ".$intrval_string." AND status = 4 AND (feedbackemail != 1  OR feedbackemail IS NULL) AND closed IS NOT NULL";
        $ticketids = jssupportticket::$_db->get_results($query);
        if(!empty($ticketids)){
            foreach ($ticketids as $key) {
                if(is_numeric($key->id)){
                    JSSTincluder::getJSModel('ticket')->sendFeedbackMailByTicketid($key->id);
                }
            }
        }
        return;
    }

    function removeFileCustom($id,$key){
        $filename = jssupportticketphplib::JSST_str_replace(' ', '_', $key);
        $maindir = wp_upload_dir();
        $basedir = $maindir['basedir'];
        $datadirectory = jssupportticket::$_config['data_directory'];
        $path = $basedir . '/' . $datadirectory. '/attachmentdata/ticket';

        $query = "SELECT attachmentdir FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id = ".esc_sql($id);
        $foldername = jssupportticket::$_db->get_var($query);
        $userpath = $path . '/' . $foldername.'/'.$filename;
        if ( file_exists( $userpath ) ) {
            wp_delete_file($userpath);
        }
        return ;
    }

    function getTicketidForVisitor($token) {
        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
        $encoder = new JSSTEncoder();
        $decryptedtext = $encoder->decrypt($token);
        $array = json_decode($decryptedtext, true);
        $emailaddress = $array['emailaddress'];
        $trackingid = $array['trackingid'];
        if (isset($array['sitelink']) && $array['sitelink'] != '') {
            $siteLink = $array['sitelink'];
            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
            $encoder = new JSSTEncoder();
            $savedSiteLink = get_option('jsst_encripted_site_link');
            $decryptedSiteLink = $encoder->decrypt($siteLink);
            $decryptedSavedSiteLink = $encoder->decrypt($savedSiteLink);
            if ($decryptedSiteLink != $decryptedSavedSiteLink) {
                return false;
            }
        }
        if($emailaddress == '' && $trackingid == ''){
            return false;
        }
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = '" . esc_sql($emailaddress) . "' AND ticketid = '" . esc_sql($trackingid) . "'";
        $ticketid = jssupportticket::$_db->get_var($query);
        return $ticketid;
    }

    function createTokenByEmailAndTrackingId($emailaddress, $trackingid) {
        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
        $encoder = new JSSTEncoder();
        $token = $encoder->encrypt(wp_json_encode(array('emailaddress' => $emailaddress, 'trackingid' => $trackingid)));
        return $token;
    }

    function validateTicketDetailForStaff($ticketid) {
        if(!in_array('agent', jssupportticket::$_active_addons)){
            return false;
        }
        if (!is_numeric($ticketid))
            return false;
        $allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
        if($allowed == true){
            return true;
        }
        // check in assign department
        $c_uid = JSSTincluder::getObjectClass('user')->uid();
        $query = "SELECT ticket.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
            JOIN `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept ON ticket.departmentid = dept.departmentid
            JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON dept.staffid = staff.id AND staff.uid = " . esc_sql($c_uid) . "
            WHERE ticket.id = " . esc_sql($ticketid);
        $id = jssupportticket::$_db->get_var($query);

        if ($id) {
            return true;
        } else {
            // check in assign ticket
            $query = "SELECT ticket.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON ticket.staffid = staff.id AND staff.uid = " . esc_sql($c_uid);
            $query .= " WHERE ticket.id = ". esc_sql($ticketid);
            $id = jssupportticket::$_db->get_var($query);
            if ($id)
                return true;
            else
                return false;
        }
    }

    function totalTicket() {
        $query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`";
        $total = jssupportticket::$_db->get_var($query);
        return $total;
    }

    function validateTicketDetailForUser($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
        $uid = jssupportticket::$_db->get_var($query);

        if ($uid == JSSTincluder::getObjectClass('user')->uid()) {
            return true;
        }elseif($uid != '') {
            jssupportticket::$_data['error_message'] = 2;// to prompt user that he can not view this ticket.
            return;
        }else {
            return false;
        }
    }

    function validateTicketDetailForVisitor($id) {
        if (!isset($_COOKIE['js-support-ticket-token-tkstatus'])) {
            return false;
        }
        $token = jssupportticket::JSST_sanitizeData($_COOKIE['js-support-ticket-token-tkstatus']); // JSST_sanitizeData() function uses wordpress santize functions
        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
        $encoder = new JSSTEncoder();
        $decryptedtext = $encoder->decrypt($token);
        $array = json_decode($decryptedtext, true);
        $emailaddress = $array['emailaddress'];
        $trackingid = $array['trackingid'];
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = '" . esc_sql($emailaddress) . "' AND ticketid = '" . esc_sql($trackingid) . "'";
        $ticketid = jssupportticket::$_db->get_var($query);

        if ($ticketid == $id) {
            return true;
        } else {
            $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = ".esc_sql($id);
            $ticketid = jssupportticket::$_db->get_var($query);
            if($ticketid > 0){
                jssupportticket::$_data['error_message'] = 1;// to prompt user to login
            }
            jssupportticket::$_data['error_message'] = 1;
            return false;
        }
    }

    function checkActionStatusSame($id, $array) {
        switch ($array['action']) {
            case 'priority':
                if(!is_numeric($id)) return false;
                $result = jssupportticket::$_db->get_var('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = ' . esc_sql($id) . ' AND priorityid = ' . esc_sql($array['id']));
                break;
            case 'markoverdue':
                if(!is_numeric($id)) return false;
                $result = jssupportticket::$_db->get_var('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = ' . esc_sql($id) . ' AND isoverdue = 1');
                break;
            case 'markinprogress':
                if(!is_numeric($id)) return false;
                $result = jssupportticket::$_db->get_var('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = ' . esc_sql($id) . ' AND status = 2');
                break;
            case 'closeticket':
                if(!is_numeric($id)) return false;
                $result = jssupportticket::$_db->get_var('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = ' . esc_sql($id) . ' AND status = 4');
                break;
            case 'banemail':
                $result = jssupportticket::$_db->get_var('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_email_banlist` WHERE email = "' . esc_sql($array['email']) . '"');
                break;
        }
        if ($result > 0) {
            return false;
        } else {
            return true;
        }
    }

    function ticketAssignToMe($ticketid, $staffid) {
        if (!is_numeric($ticketid))
            return false;
        if (!is_numeric($staffid))
            return false;
        $row = JSSTincluder::getJSTable('tickets');
        $row->update(array('id' => $ticketid, 'staffid' => $staffid));

        return true;
    }

    function isTicketAssigned($ticketid){
        if (! in_array('agent',jssupportticket::$_active_addons)) {
            return false;
        }
        if (!is_numeric($ticketid))
            return false;
        $query = "SELECT staffid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id=".esc_sql($ticketid);
        $staffid = jssupportticket::$_db->get_var($query);
        if($staffid > 0)
            return true;
        return false;
    }


    function getMyTicketInfo_Widget($maxrecord){
        if(!is_numeric($maxrecord)) return false;
        if(!JSSTincluder::getObjectClass('user')->isguest()){
            $uid = JSSTincluder::getObjectClass('user')->uid();
                // Data
            $query = "SELECT DISTINCT ticket.id,ticket.subject,ticket.status,ticket.name,priority.priority AS priority,priority.prioritycolour AS prioritycolour
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE ticket.uid = ".esc_sql($uid)." AND (ticket.status = 0 OR ticket.status = 1) ORDER BY ticket.status DESC LIMIT $maxrecord";

            if(in_array('agent',jssupportticket::$_active_addons)){
                $staffid = JSSTincluder::getJSModel('agent')->getStaffId($uid);
                if($staffid){
                    // Data
                    $query = "SELECT DISTINCT ticket.id,ticket.subject,ticket.status,ticket.name,priority.priority AS priority,priority.prioritycolour AS prioritycolour
                                FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                                JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.uid = ticket.uid
                                WHERE (ticket.staffid = ".esc_sql($staffid)." OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = ".esc_sql($staffid).")) AND (ticket.status = 0 OR ticket.status = 1) ORDER BY ticket.status DESC LIMIT $maxrecord";
                }
            }
            if(isset($query)){
                jssupportticket::$_data['widget_myticket'] = jssupportticket::$_db->get_results($query);
                if (jssupportticket::$_db->last_error != null) {
                    JSSTincluder::getJSModel('systemerror')->addSystemError();
                }
            }else{
                jssupportticket::$_data['widget_myticket'] = false;
            }
        }else{
            jssupportticket::$_data['widget_myticket'] = false;
        }
        return;
    }

    function getLatestTicketForDashboard(){
        $query = "SELECT ticket.id,ticket.subject,ticket.name,priority.priority,priority.prioritycolour
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    ORDER BY ticket.status ASC, ticket.created DESC LIMIT 0, 5";
        $tickets = jssupportticket::$_db->get_results($query);
        return $tickets;
    }
    function getAttachmentByTicketId($id){
        if(!is_numeric($id)) return false;
        //if not admin and agent
        if(!current_user_can('manage_options') && !(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())){
            // in case of user check for ticket owner
            if (!JSSTincluder::getObjectClass('user')->isguest()) {
                $current_uid = JSSTincluder::getObjectClass('user')->uid();
                $ticket_uid = JSSTincluder::getJSModel('ticket')->getUIdById($id);
                if ($current_uid != $ticket_uid) {
                    return;
                }
            } else {
                if (!$this->validateTicketDetailForVisitor($id)) {
                    return;
                }
            }
            
        }
        $query = "SELECT attachment.filename , ticket.attachmentdir
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments` AS attachment
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON ticket.id = attachment.ticketid AND ticket.id =".esc_sql($id). " AND attachment.replyattachmentid = 0 ";
        $attachments = jssupportticket::$_db->get_results($query);
        return $attachments;
    }

    function getTotalStatsForDashboard(){
        $curdate = date_i18n('Y-m-d');
        $fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));

        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE status = 0 AND (lastreply = '0000-00-00 00:00:00' OR lastreply = '') AND date(created) >= '".esc_sql($fromdate)."'AND date(created) <= '".esc_sql($curdate)."'";
        $result['open'] = jssupportticket::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered = 1 AND status != 4 AND status != 0 AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."'";
        $result['answered'] = jssupportticket::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isoverdue = 1 AND status != 4 AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."'";
        $result['overdue'] = jssupportticket::$_db->get_var($query);
        $query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered != 1 AND status != 4 AND (lastreply != '0000-00-00 00:00:00' AND lastreply != '') AND date(created) >= '".esc_sql($fromdate)."' AND date(created) <= '".esc_sql($curdate)."'";
        $result['pending'] = jssupportticket::$_db->get_var($query);

        return $result;
    }

    function getRandomFolderName() {
        $foldername = "";
        $length = 7;
        $possible = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
        // we refer to the length of $possible a few times, so let's grab it now
        $maxlength = jssupportticketphplib::JSST_strlen($possible);
        if ($length > $maxlength) { // check for length overflow and truncate if necessary
            $length = $maxlength;
        }
        // set up a counter for how many characters are in the ticketid so far
        $i = 0;
        // add random characters to $password until $length is reached
        while ($i < $length) {
            // pick a random character from the possible ones
            $char = jssupportticketphplib::JSST_substr($possible, wp_rand(0, $maxlength - 1), 1);
            if (!strstr($foldername, $char)) {
                if ($i == 0) {
                    if (ctype_alpha($char)) {
                        $foldername .= $char;
                        $i++;
                    }
                } else {
                    $foldername .= $char;
                    $i++;
                }
            }
        }
        return $foldername;
    }

    static function generateHash($id){
        if(!is_numeric($id))
            return null;
        return jssupportticketphplib::JSST_safe_encoding(wp_json_encode(base64_encode($id)));
    }

    function getUIdById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
        $ticketuid = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $ticketuid;
    }

    function getNotificationIdById($id) {
        if (!is_numeric($id))
            return false;
        $query = "SELECT notificationid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . esc_sql($id);
        $notificationid = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $notificationid;
    }

    function getAdminTicketSearchFormData($search_userfields){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'my-ticket') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $search_userfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
        $jsst_search_array['subject'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('subject' , ''));
        $jsst_search_array['name'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('name' , ''));
        $jsst_search_array['email'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('email' , ''));
        $jsst_search_array['ticketid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('ticketid' , ''));
        $jsst_search_array['datestart'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('datestart' , ''));
        $jsst_search_array['dateend'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('dateend' , ''));
        $jsst_search_array['orderid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('orderid' , ''));
        $jsst_search_array['eddorderid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('eddorderid', ''));
        $jsst_search_array['priority'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('priority' , ''));
        $jsst_search_array['departmentid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('departmentid' , ''));
        $jsst_search_array['list'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('list', null ,1));
        $jsst_search_array['staffid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('staffid' , ''));
        $jsst_search_array['sortby'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('sortby' , ''));
        $jsst_search_array['search_from_ticket'] = 1;
        if (!empty($search_userfields)) {
            foreach ($search_userfields as $uf) {
                $jsst_search_array['jsst_ticket_custom_field'][$uf->field] = JSSTrequest::getVar($uf->field, 'post');
            }
        }
        return $jsst_search_array;
    }

    function getFrontSideTicketSearchFormData($search_userfields){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'my-ticket') ) {
            die( 'Security check Failed' );
        }$jsst_search_array = array();
        $search_userfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
        $jsst_search_array['subject'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-subject' , ''));
        $jsst_search_array['name'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-from' , ''));
        $jsst_search_array['email'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-email' , ''));
        $jsst_search_array['ticketid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-ticket' , ''));
        $jsst_search_array['datestart'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-datestart' , ''));
        $jsst_search_array['dateend'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-dateend' , ''));
        $jsst_search_array['orderid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-orderid' , ''));
        $jsst_search_array['eddorderid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-eddorderid', ''));
        $jsst_search_array['priority'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-priorityid' , ''));
        $jsst_search_array['departmentid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-departmentid' , ''));
        $jsst_search_array['list'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('list', null ,1));
        $jsst_search_array['assignedtome'] = JSSTrequest::getVar('assignedtome', 'post');
        $jsst_search_array['staffid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('staffid' , ''));
        $jsst_search_array['sortby'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('sortby' , ''));
        $jsst_search_array['ticketkeys'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-ticketsearchkeys', 'post')));
        $jsst_search_array['search_from_ticket'] = 1;
        if (!empty($search_userfields)) {
            foreach ($search_userfields as $uf) {
                $jsst_search_array['jsst_ticket_custom_field'][$uf->field] = JSSTrequest::getVar($uf->field, 'post');
            }
        }
        return $jsst_search_array;
    }

    function getCookiesSavedSearchDataTicket($search_userfields){
        $jsst_search_array = array();
        $ticket_search_cookie_data = '';
        if(isset($_COOKIE['jsst_ticket_search_data'])){
            $ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
            $ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($ticket_search_cookie_data) , true );
        }
        if($ticket_search_cookie_data != '' && isset($ticket_search_cookie_data['search_from_ticket']) && $ticket_search_cookie_data['search_from_ticket'] == 1){
            $jsst_search_array['subject'] = $ticket_search_cookie_data['subject'];
            $jsst_search_array['name'] = $ticket_search_cookie_data['name'];
            $jsst_search_array['email'] = $ticket_search_cookie_data['email'];
            $jsst_search_array['ticketid'] = $ticket_search_cookie_data['ticketid'];
            $jsst_search_array['datestart'] = $ticket_search_cookie_data['datestart'];
            $jsst_search_array['dateend'] = $ticket_search_cookie_data['dateend'];
            $jsst_search_array['orderid'] = $ticket_search_cookie_data['orderid'];
            $jsst_search_array['eddorderid'] = $ticket_search_cookie_data['eddorderid'];
            $jsst_search_array['priority'] = $ticket_search_cookie_data['priority'];
            $jsst_search_array['departmentid'] = $ticket_search_cookie_data['departmentid'];
            $jsst_search_array['staffid'] = $ticket_search_cookie_data['staffid'];
            $jsst_search_array['sortby'] = $ticket_search_cookie_data['sortby'];
            $jsst_search_array['list'] = $ticket_search_cookie_data['list'];
            $jsst_search_array['assignedtome'] = isset($ticket_search_cookie_data['assignedtome']) ? $ticket_search_cookie_data['assignedtome'] : null;
            $jsst_search_array['ticketkeys'] = isset($ticket_search_cookie_data['ticketkeys']) ? $ticket_search_cookie_data['ticketkeys'] : false;
            if (!empty($search_userfields)) {
                foreach ($search_userfields as $uf) {
                    $jsst_search_array['jsst_ticket_custom_field'][$uf->field] = (isset($ticket_search_cookie_data['jsst_ticket_custom_field'][$uf->field]) && $ticket_search_cookie_data['jsst_ticket_custom_field'][$uf->field] != '') ? $ticket_search_cookie_data['jsst_ticket_custom_field'][$uf->field] : null;
                }
            }
        }

        return $jsst_search_array;
    }

    function setSearchVariableForTicket($jsst_search_array,$search_userfields){

        jssupportticket::$_search['ticket']['subject'] = isset($jsst_search_array['subject']) ? $jsst_search_array['subject'] : null;
        jssupportticket::$_search['ticket']['name'] = isset($jsst_search_array['name']) ? $jsst_search_array['name'] : null;
        jssupportticket::$_search['ticket']['email'] = isset($jsst_search_array['email']) ? $jsst_search_array['email'] : null;
        jssupportticket::$_search['ticket']['ticketid'] = isset($jsst_search_array['ticketid']) ? $jsst_search_array['ticketid'] : null;
        jssupportticket::$_search['ticket']['datestart'] = isset($jsst_search_array['datestart']) ? $jsst_search_array['datestart'] : null;
        jssupportticket::$_search['ticket']['dateend'] = isset($jsst_search_array['dateend']) ? $jsst_search_array['dateend'] : null;
        jssupportticket::$_search['ticket']['orderid'] = isset($jsst_search_array['orderid']) ? $jsst_search_array['orderid'] : null;
        jssupportticket::$_search['ticket']['eddorderid'] = isset($jsst_search_array['eddorderid']) ? $jsst_search_array['eddorderid'] : null;
        jssupportticket::$_search['ticket']['priority'] = isset($jsst_search_array['priority']) ? $jsst_search_array['priority'] : null;
        jssupportticket::$_search['ticket']['departmentid'] = isset($jsst_search_array['departmentid']) ? $jsst_search_array['departmentid'] : null;
        jssupportticket::$_search['ticket']['staffid'] = isset($jsst_search_array['staffid']) ? $jsst_search_array['staffid'] : null;
        jssupportticket::$_search['ticket']['sortby'] = isset($jsst_search_array['sortby']) ? $jsst_search_array['sortby'] : null;
        jssupportticket::$_search['ticket']['list'] = isset($jsst_search_array['list']) ? $jsst_search_array['list'] : 1;
        // frontend
        jssupportticket::$_search['ticket']['assignedtome'] = isset($jsst_search_array['assignedtome']) ? $jsst_search_array['assignedtome'] : null;
        jssupportticket::$_search['ticket']['ticketkeys'] = isset($jsst_search_array['ticketkeys']) ? $jsst_search_array['ticketkeys'] : false;
        if (!empty($search_userfields)) {
            foreach ($search_userfields as $uf) {
                jssupportticket::$_search['jsst_ticket_custom_field'][$uf->field] = isset($jsst_search_array['jsst_ticket_custom_field'][$uf->field]) ? $jsst_search_array['jsst_ticket_custom_field'][$uf->field] : null;
            }
        }
    }
    function checkIsTicketDuplicate($subject,$email){
        if(empty($subject)) return false;
        if(empty($email)) return false;

        $curdate = date_i18n('Y-m-d H:i:s');
        $query = 'SELECT created FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE email = "' . esc_sql($email) . '" AND subject = "' . esc_sql($subject) . '" ORDER BY created DESC LIMIT 1';
        $datetime = jssupportticket::$_db->get_var($query);
        if($datetime){
            $diff = jssupportticketphplib::JSST_strtotime($curdate) - jssupportticketphplib::JSST_strtotime($datetime);
            if($diff <= 15){
				return false;
            }
        }
        return true;
    }
    function getDefaultMultiFormId(){
        $query = "SHOW TABLES LIKE '%js_ticket_multiform%'";
        $count = jssupportticket::$_db->query($query);
        if ($count == 1) {
            $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_multiform` WHERE is_default = 1 ";
            $id = jssupportticket::$_db->get_row($query);
            if(isset($id)) {
                return $id->id;
            }
        }
        return 1;
    }

    function isFieldRequired(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'is-field-required') ) {
            die( 'Security check Failed' );
        }
        $field = JSSTrequest::getVar('field');
        $query = "SELECT required  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE  field ='".esc_sql($field)."'";
        return jssupportticket::$_db->get_var($query);
    }

    function getClosedBy($id){
        if ($id == 0) {
            $closedBy = esc_html(__('System', 'js-support-ticket'));
        } else if($id == -1){
            $closedBy = esc_html(__('Guest', 'js-support-ticket'));
        } else {
            $query = "SELECT display_name AS name FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` WHERE id = ".esc_sql($id);
            $closedBy = jssupportticket::$_db->get_var($query);
        }
        return $closedBy;
    }
	
}
?>
