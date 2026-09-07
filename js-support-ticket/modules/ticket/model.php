<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTticketModel {

    private $jsst_ticketid;

    function getTicketsForAdmin($jsst_lst=null) {
        $this->getOrdering();
        // Filter
        $jsst_search_userfields = JSSTincluder::getObjectClass('customfields')->adminFieldsForSearch(1);
        $jsst_subject = jssupportticket::$_search['ticket']['subject'];
        $jsst_name = jssupportticket::$_search['ticket']['name'];
        $jsst_phone = jssupportticket::$_search['ticket']['phone'];
        $jsst_email = jssupportticket::$_search['ticket']['email'];
        $jsst_ticketid = jssupportticket::$_search['ticket']['ticketid'];
        $jsst_datestart = jssupportticket::$_search['ticket']['datestart'];
        $jsst_dateend = jssupportticket::$_search['ticket']['dateend'];
        $jsst_orderid = jssupportticket::$_search['ticket']['orderid'];
        $jsst_eddorderid = jssupportticket::$_search['ticket']['eddorderid'];
        $jsst_priority = jssupportticket::$_search['ticket']['priority'];
        $jsst_departmentid = jssupportticket::$_search['ticket']['departmentid'];
        $jsst_helptopicid = jssupportticket::$_search['ticket']['helptopicid'];
        $jsst_productid = jssupportticket::$_search['ticket']['productid'];
        $jsst_staffid = jssupportticket::$_search['ticket']['staffid'];
        $jsst_status = jssupportticket::$_search['ticket']['status'];
        $jsst_sortby = jssupportticket::$_search['ticket']['sortby'];
        $jsst_keywords = jssupportticket::$_search['ticket']['keywords']; // Roadmap 4.0-CORE-18

        if (!empty($jsst_search_userfields)) {
            foreach ($jsst_search_userfields as $jsst_uf) {
                $jsst_value_array[$jsst_uf->field] = jssupportticket::$_search['jsst_ticket_custom_field'][$jsst_uf->field];
            }
        }
        $jsst_inquery = '';
        $jsst_inquery_args = array();
        if($jsst_lst != null){
            jssupportticket::$_search['ticket']['list'] = $jsst_lst;
        }
        // Normalised on the way in, not on the way to the cookie: list 2 is the
        // retired Answered tab and has to keep working from old links and saved
        // views, but the stored value is shared with the front-end queue, which
        // numbers its tabs differently.
        $jsst_list = JSSTqueue::normalizeList(jssupportticket::$_search['ticket']['list']);
        // The tab clauses live on JSSTqueue so the queue, the counts below and
        // anything else that asks "which tickets are in this tab?" cannot drift
        // apart. Waiting on Agent and Waiting on Customer are new in 4.0.
        // (Roadmap 4.0-CORE-18)
        $jsst_inquery .= JSSTqueue::listClause($jsst_list);

        if ($jsst_datestart != null){
            $jsst_inquery .= " AND %s <= DATE(ticket.created)";
            $jsst_inquery_args[] = $jsst_datestart;
        }
        if ($jsst_dateend != null){
            $jsst_inquery .= " AND %s >= DATE(ticket.created)";
            $jsst_inquery_args[] = $jsst_dateend;
        }
        if ($jsst_ticketid != null){
            $jsst_inquery .= " AND ticket.ticketid LIKE %s";
            $jsst_inquery_args[] = '%'.$jsst_ticketid.'%';
        }
        if ($jsst_subject != null){
            $jsst_inquery .= " AND ticket.subject LIKE %s";
            $jsst_inquery_args[] = '%'.$jsst_subject.'%';
        }
        if ($jsst_name != null){
            $jsst_inquery .= " AND ticket.name LIKE %s";
            $jsst_inquery_args[] = '%'.$jsst_name.'%';
        }
        if ($jsst_phone != null){
            $jsst_inquery .= " AND ticket.phone LIKE %s";
            $jsst_inquery_args[] = '%'.$jsst_phone.'%';
        }
        if ($jsst_email != null){
            $jsst_inquery .= " AND ticket.email LIKE %s";
            $jsst_inquery_args[] = '%'.$jsst_email.'%';
        }

        // Added is_numeric checks for IDs
        if ($jsst_priority != null && is_numeric($jsst_priority)){
            $jsst_inquery .= " AND ticket.priorityid = %d";
            $jsst_inquery_args[] = $jsst_priority;
        }
        if ($jsst_departmentid != null && is_numeric($jsst_departmentid)){
            $jsst_inquery .= " AND ticket.departmentid = %d";
            $jsst_inquery_args[] = $jsst_departmentid;
        }
        if ($jsst_helptopicid != null && is_numeric($jsst_helptopicid)){
            $jsst_inquery .= " AND ticket.helptopicid = %d";
            $jsst_inquery_args[] = $jsst_helptopicid;
        }
        if ($jsst_productid != null && is_numeric($jsst_productid)){
            $jsst_inquery .= " AND ticket.productid = %d";
            $jsst_inquery_args[] = $jsst_productid;
        }
        if ($jsst_staffid != null && is_numeric($jsst_staffid)){
            $jsst_inquery .= " AND ticket.staffid = %d";
            $jsst_inquery_args[] = $jsst_staffid;
        }

        if ($jsst_orderid != null && is_numeric($jsst_orderid)){
            $jsst_inquery .= " AND ticket.wcorderid = %d";
            $jsst_inquery_args[] = $jsst_orderid;
        }

        if ($jsst_eddorderid != null && is_numeric($jsst_eddorderid)){
            $jsst_inquery .= " AND ticket.eddorderid = %d";
            $jsst_inquery_args[] = $jsst_eddorderid;
        }

        if ($jsst_status != null && is_numeric($jsst_status)){
            $jsst_inquery .= " AND ticket.status = %d";
            $jsst_inquery_args[] = $jsst_status;
        }

        // Keyword search over the ticket and its replies. Appended to the same
        // clause the tab and the ordinary filters build, so it narrows whatever
        // is already on screen instead of replacing it, and so the count query
        // and the data query below see exactly the same conditions.
        // (Roadmap 4.0-CORE-18)
        $jsst_searchfilter = JSSTqueue::searchFilter($jsst_keywords);
        if ($jsst_searchfilter['where'] !== '') {
            $jsst_inquery .= $jsst_searchfilter['where'];
            foreach ($jsst_searchfilter['args'] AS $jsst_searcharg) {
                $jsst_inquery_args[] = $jsst_searcharg;
            }
        }

        $jsst_valarray = array();
        if (!empty($jsst_search_userfields)) {
            foreach ($jsst_search_userfields as $jsst_uf) {
                if (JSSTrequest::getVar('pagenum', 'get', null) != null) {
                    $jsst_valarray[$jsst_uf->field] = $jsst_value_array[$jsst_uf->field];
                }else{
                    $jsst_valarray[$jsst_uf->field] = JSSTrequest::getVar($jsst_uf->field, 'post');
                }
                if (isset($jsst_valarray[$jsst_uf->field]) && $jsst_valarray[$jsst_uf->field] != null) {
                    switch ($jsst_uf->userfieldtype) {
                        case 'text':
                            $jsst_inquery .= ' AND ticket.params REGEXP %s';
                            $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '.*"';
                            break;
                        case 'email':
                            $jsst_inquery .= ' AND ticket.params REGEXP %s';
                            $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '.*"';
                            break;
                        case 'file':
                            $jsst_inquery .= ' AND ticket.params REGEXP %s';
                            $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '.*"';
                            break;
                        case 'combo':
                            $jsst_inquery .= ' AND ticket.params LIKE %s';
                            $jsst_inquery_args[] = '%"' . $jsst_uf->field . '":"' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '"%';
                            break;
                        case 'depandant_field':
                            $jsst_inquery .= ' AND ticket.params LIKE %s';
                            $jsst_inquery_args[] = '%"' . $jsst_uf->field . '":"' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '"%';
                            break;
                        case 'radio':
                            $jsst_inquery .= ' AND ticket.params LIKE %s';
                            $jsst_inquery_args[] = '%"' . $jsst_uf->field . '":"' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '"%';
                            break;
                        case 'checkbox':
                            $jsst_finalvalue = '';
                            foreach((array) $jsst_valarray[$jsst_uf->field] AS $jsst_value){
                                if($jsst_value != null){
                                    $jsst_finalvalue .= $jsst_value.'.*';
                                }
                            }
                            if($jsst_finalvalue != ''){
                                $jsst_inquery .= ' AND ticket.params REGEXP %s';
                                $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars($jsst_finalvalue) . '.*"';
                            }
                            break;
                        case 'date':
                            $jsst_inquery .= ' AND ticket.params LIKE %s';
                            $jsst_inquery_args[] = '%"' . $jsst_uf->field . '":"' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '"%';
                            break;
                        case 'textarea':
                            $jsst_inquery .= ' AND ticket.params REGEXP %s';
                            $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '.*"';
                            break;
                        case 'multiple':
                            $jsst_finalvalue = '';
                            foreach($jsst_valarray[$jsst_uf->field] AS $jsst_value){
                                if($jsst_value != null){
                                    $jsst_finalvalue .= $jsst_value.'.*';
                                }
                            }
                            if($jsst_finalvalue !=''){
                                $jsst_inquery .= ' AND ticket.params REGEXP %s';
                                $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*'.htmlspecialchars($jsst_finalvalue).'.*"';
                            }
                            break;
                    }
                    jssupportticket::$jsst_data['filter']['params'] = $jsst_valarray;
                }
            }
        }
        //end

        jssupportticket::$jsst_data['filter']['subject'] = $jsst_subject;
        jssupportticket::$jsst_data['filter']['ticketid'] = $jsst_ticketid;
        jssupportticket::$jsst_data['filter']['name'] = $jsst_name;
        jssupportticket::$jsst_data['filter']['phone'] = $jsst_phone;
        jssupportticket::$jsst_data['filter']['email'] = $jsst_email;
        jssupportticket::$jsst_data['filter']['datestart'] = $jsst_datestart;
        jssupportticket::$jsst_data['filter']['dateend'] = $jsst_dateend;
        jssupportticket::$jsst_data['filter']['priority'] = $jsst_priority;
        jssupportticket::$jsst_data['filter']['departmentid'] = $jsst_departmentid;
        jssupportticket::$jsst_data['filter']['helptopicid'] = $jsst_helptopicid;
        jssupportticket::$jsst_data['filter']['productid'] = $jsst_productid;
        jssupportticket::$jsst_data['filter']['staffid'] = $jsst_staffid;
        jssupportticket::$jsst_data['filter']['sortby'] = $jsst_sortby;
        jssupportticket::$jsst_data['filter']['orderid'] = $jsst_orderid;
        jssupportticket::$jsst_data['filter']['eddorderid'] = $jsst_eddorderid;
        jssupportticket::$jsst_data['filter']['status'] = $jsst_status;
        // Keeps the tag control showing what the queue is actually filtered by.
        // (Roadmap 4.0-CORE-17)
        jssupportticket::$jsst_data['filter']['tagid'] = jssupportticket::$_search['ticket']['tagid'];
        // Same for the search box and the saved-view control, so a reload or a
        // page of results shows what the queue is actually constrained by.
        // (Roadmap 4.0-CORE-18)
        jssupportticket::$jsst_data['filter']['keywords'] = $jsst_keywords;
        jssupportticket::$jsst_data['filter']['viewid'] = jssupportticket::$_search['ticket']['viewid'];

        $jsst_userquery = '';
        $jsst_userquery_args = array();
        $jsst_uid = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('uid'));
        if($jsst_uid != null && is_numeric($jsst_uid)){
            $jsst_userquery = ' AND ticket.uid = %d';
            $jsst_userquery_args[] = $jsst_uid;
        }

        if (!empty($jsst_inquery_args)) {
            $jsst_inquery = jssupportticket::$_db->prepare($jsst_inquery, $jsst_inquery_args);
        }
        if (!empty($jsst_userquery_args)) {
            $jsst_userquery = jssupportticket::$_db->prepare($jsst_userquery, $jsst_userquery_args);
        }

        // Tag filter. Prepared here so the same join and clause go to the count
        // and to the data query, and the two can never disagree about how many
        // tickets match. (Roadmap 4.0-CORE-17)
        $jsst_tagmodel = JSSTincluder::getJSModel('tag');
        $jsst_tagfilter = $jsst_tagmodel->queueFilter(jssupportticket::$_search['ticket']['tagid']);
        $jsst_tagwhere = '';
        if ($jsst_tagfilter['where'] !== '') {
            $jsst_tagwhere = jssupportticket::$_db->prepare($jsst_tagfilter['where'], $jsst_tagfilter['args']);
        }

        // Pagination
        $jsst_query = "SELECT COUNT(ticket.id) "
                . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                . $jsst_tagfilter['join']
                . "WHERE 1 = 1";
        $jsst_query .= $jsst_inquery.$jsst_userquery.$jsst_tagwhere;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        /*
          list variable detail
          1=>For open ticket
          2=>For answered  ticket
          3=>For overdue ticket
          4=>For Closed tickets
          5=>For mytickets tickets
         */
        jssupportticket::$jsst_data['list'] = $jsst_list; // assign for reference
        // Data
        do_action('jsst_addon_staff_admin_tickets');
        $jsst_query = "SELECT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour,status.status AS statustitle,status.statuscolour,status.statusbgcolour, product.product AS producttitle ".jssupportticket::$_addon_query['select']."
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ON ticket.productid = product.id
                    ".jssupportticket::$_addon_query['join']."
                    " . $jsst_tagfilter['join'] . "
                    WHERE 1 = 1";

        $jsst_query .= $jsst_inquery.$jsst_userquery.$jsst_tagwhere;
        $jsst_query .= " ORDER BY " . jssupportticket::$_ordering . " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);

        // The tags for this page of the queue, in one query. (Roadmap 4.0-CORE-17)
        $jsst_pageids = array();
        if (is_array(jssupportticket::$jsst_data[0])) {
            foreach (jssupportticket::$jsst_data[0] AS $jsst_pagerow) {
                $jsst_pageids[] = $jsst_pagerow->id;
            }
        }
        jssupportticket::$jsst_data['ticket_tags'] = $jsst_tagmodel->getTagsForTickets($jsst_pageids);
        do_action('jsst_reset_aadon_query');
        // check email is bane
        if(JSSTmergedaddon::featureEnabled('banemail')){
            // The block list is core data now, and this model reads the table
            // directly rather than through the module that owns it - so on a
            // site that never had the legacy addon nothing has created it yet
            // and the first ticket list reports it as missing. ensureSchema()
            // does nothing while a legacy addon still owns the table, and
            // needsRun() memoises, so the repeat calls below cost one option
            // read between them. (Roadmap 4.0-CORE-19)
            JSSTmergedaddon::ensureSchema('banemail');
            if (isset(jssupportticket::$jsst_data[0]->email)){
                $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = %s";
                $jsst_query = jssupportticket::$_db->prepare($jsst_query, ' ' . jssupportticket::$jsst_data[0]->email);
            }
            jssupportticket::$jsst_data[7] = jssupportticket::$_db->get_var($jsst_query);
        }else{
            jssupportticket::$jsst_data[7] = 0;
        }
        //Hook action
        do_action('jsst-ticketbeforelisting', jssupportticket::$jsst_data[0]);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        // The number on every tab, in one pass over the table.
        //
        // This used to be one COUNT query per tab, each joining departments and
        // priorities that no tab actually filters on. Adding Waiting on Agent
        // and Waiting on Customer would have made that seven scans to draw one
        // row of tabs. Counting with a CASE per tab is one scan for all of them,
        // and the conditions come from JSSTqueue, so a tab and the number on it
        // are the same definition. (Roadmap 4.0-CORE-18, 4.0-PERF-01)
        $jsst_countselect = array();
        foreach (JSSTqueue::countClauses() AS $jsst_countalias => $jsst_countclause) {
            $jsst_countselect[] = "SUM(CASE WHEN " . $jsst_countclause . " THEN 1 ELSE 0 END) AS " . $jsst_countalias;
        }
        $jsst_query = "SELECT " . implode(', ', $jsst_countselect)
                . " FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                . "WHERE 1 = 1" . $jsst_userquery;
        $jsst_counts = jssupportticket::$_db->get_row($jsst_query);
        foreach (array_keys(JSSTqueue::countClauses()) AS $jsst_countalias) {
            // SUM() over no rows is NULL, so an empty queue reads 0 rather than
            // an empty tab label.
            jssupportticket::$jsst_data['count'][$jsst_countalias] = (isset($jsst_counts->$jsst_countalias)) ? (int) $jsst_counts->$jsst_countalias : 0;
        }
        return;
    }

    function getOrdering() {
        $jsst_sort = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['sortby'] : '';
        if ($jsst_sort == '') {
            $jsst_list = jssupportticket::$_config['tickets_ordering'];
            // default sort by
            $jsst_sortbyconfig = jssupportticket::$_config['tickets_sorting'];
            if($jsst_sortbyconfig == 1){
                $jsst_sortbyconfig = "asc";
            }else{
                $jsst_sortbyconfig = "desc";
            }
            $jsst_sort = 'status';
            if($jsst_list == 2)
                $jsst_sort = 'created';
            $jsst_sort = $jsst_sort.$jsst_sortbyconfig;
        }
        $this->getTicketListOrdering($jsst_sort);
        $this->getTicketListSorting($jsst_sort);
    }

    function combineOrSingleSearch() {
        $jsst_ticketkeys = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['ticketkeys'] : false;
        $jsst_inquery = '';
        $jsst_inquery_args = array();
        if ($jsst_ticketkeys) {
            if (jssupportticketphplib::JSST_strpos($jsst_ticketkeys, '@') && jssupportticketphplib::JSST_strpos($jsst_ticketkeys, '.')){
                $jsst_inquery = " AND ticket.email LIKE %s";
                $jsst_inquery_args[] = '%'.$jsst_ticketkeys.'%';
            }else{
                $jsst_inquery = " AND (ticket.ticketid = %s OR ticket.subject LIKE %s)";
                $jsst_inquery_args[] = $jsst_ticketkeys;
                $jsst_inquery_args[] = '%'.$jsst_ticketkeys.'%';
            }
            jssupportticket::$jsst_data['filter']['ticketsearchkeys'] = $jsst_ticketkeys;
        }else {
            $jsst_search_userfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
            $jsst_ticketid = JSSTrequest::getVar('jsst-ticket', 'post');

            $jsst_from = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['name'] : '';
            $jsst_phone = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['phone'] : '';
            $jsst_email = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['email'] : '';
            $jsst_departmentid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['departmentid'] : '';
            $jsst_helptopicid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['helptopicid'] : '';
            $jsst_productid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['productid'] : '';
            $jsst_priorityid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['priority'] : '';
            $jsst_subject = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['subject'] : '';
            $jsst_datestart = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['datestart'] : '';
            $jsst_dateend = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['dateend'] : '';
            $jsst_orderid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['orderid'] : '';
            $jsst_eddorderid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['eddorderid'] : '';
            $jsst_staffid = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['staffid'] : '';
            $jsst_status = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['status'] : '';
            $jsst_sortby = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['sortby'] : '';
            $jsst_assignedtome = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['assignedtome'] : '';

            if (!empty($jsst_search_userfields)) {
                foreach ($jsst_search_userfields as $jsst_uf) {
                    $jsst_value_array[$jsst_uf->field] = isset(jssupportticket::$_search['jsst_ticket_custom_field']) ? jssupportticket::$_search['jsst_ticket_custom_field'][$jsst_uf->field] : '';
                }
            }

            if ($jsst_ticketid != null) {
                $jsst_inquery .= " AND ticket.ticketid LIKE %s";
                $jsst_inquery_args[] = $jsst_ticketid;
                jssupportticket::$jsst_data['filter']['ticketid'] = $jsst_ticketid;
            }
            if ($jsst_from != null) {
                $jsst_inquery .= " AND ticket.name LIKE %s";
                $jsst_inquery_args[] = '%'.$jsst_from.'%';
                jssupportticket::$jsst_data['filter']['from'] = $jsst_from;
            }
            if ($jsst_phone != null) {
                $jsst_inquery .= " AND ticket.phone LIKE %s";
                $jsst_inquery_args[] = '%'.$jsst_phone.'%';
                jssupportticket::$jsst_data['filter']['phone'] = $jsst_phone;
            }
            if ($jsst_email != null) {
                $jsst_inquery .= " AND ticket.email LIKE %s";
                $jsst_inquery_args[] = $jsst_email;
                jssupportticket::$jsst_data['filter']['email'] = $jsst_email;
            }
            if ($jsst_departmentid != null && is_numeric($jsst_departmentid)) {
                $jsst_inquery .= " AND ticket.departmentid = %d";
                $jsst_inquery_args[] = $jsst_departmentid;
                jssupportticket::$jsst_data['filter']['departmentid'] = $jsst_departmentid;
            }
            if ($jsst_helptopicid != null && is_numeric($jsst_helptopicid)) {
                $jsst_inquery .= " AND ticket.helptopicid = %d";
                $jsst_inquery_args[] = $jsst_helptopicid;
                jssupportticket::$jsst_data['filter']['helptopicid'] = $jsst_helptopicid;
            }
            if ($jsst_productid != null && is_numeric($jsst_productid)) {
                $jsst_inquery .= " AND ticket.productid = %d";
                $jsst_inquery_args[] = $jsst_productid;
                jssupportticket::$jsst_data['filter']['productid'] = $jsst_productid;
            }
            if ($jsst_priorityid != null && is_numeric($jsst_priorityid)) {
                $jsst_inquery .= " AND ticket.priorityid = %d";
                $jsst_inquery_args[] = $jsst_priorityid;
                jssupportticket::$jsst_data['filter']['priorityid'] = $jsst_priorityid;
            }
            if(in_array('agent', jssupportticket::$_active_addons)){
                if ($jsst_staffid != null && is_numeric($jsst_staffid)) {
                    $jsst_inquery .= " AND ticket.staffid = %d";
                    $jsst_inquery_args[] = $jsst_staffid;
                    jssupportticket::$jsst_data['filter']['staffid'] = $jsst_staffid;
                }
            }

            if ($jsst_subject != null) {
                $jsst_inquery .= " AND ticket.subject LIKE %s";
                $jsst_inquery_args[] = '%'.$jsst_subject.'%';
                jssupportticket::$jsst_data['filter']['subject'] = $jsst_subject;
            }
            if ($jsst_datestart != null) {
                $jsst_inquery .= " AND %s <= DATE(ticket.created)";
                $jsst_inquery_args[] = $jsst_datestart;
                jssupportticket::$jsst_data['filter']['datestart'] = $jsst_datestart;
            }
            if ($jsst_dateend != null) {
                $jsst_inquery .= " AND %s >= DATE(ticket.created)";
                $jsst_inquery_args[] = $jsst_dateend;
                jssupportticket::$jsst_data['filter']['dateend'] = $jsst_dateend;
            }

            if ($jsst_orderid != null && is_numeric($jsst_orderid)) {
                $jsst_inquery .= " AND ticket.wcorderid = %d";
                $jsst_inquery_args[] = $jsst_orderid;
                jssupportticket::$jsst_data['filter']['orderid'] = $jsst_orderid;
            }

            if ($jsst_eddorderid != null && is_numeric($jsst_eddorderid)) {
                $jsst_inquery .= " AND ticket.eddorderid = %d";
                $jsst_inquery_args[] = $jsst_eddorderid;
                jssupportticket::$jsst_data['filter']['eddorderid'] = $jsst_eddorderid;
            }

            if ($jsst_assignedtome != null) {
                if(in_array('agent',jssupportticket::$_active_addons)){
                    $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
                    $jsst_stfid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);
                    if(is_numeric($jsst_stfid)){
                        $jsst_inquery .= " AND ticket.staffid = %d";
                        $jsst_inquery_args[] = $jsst_stfid;
                        jssupportticket::$jsst_data['filter']['assignedtome'] = $jsst_assignedtome;
                    }
                }
            }
            if ($jsst_status != null && is_numeric($jsst_status)) {
                $jsst_inquery .= " AND ticket.status = %d";
                $jsst_inquery_args[] = $jsst_status;
                jssupportticket::$jsst_data['filter']['status'] = $jsst_status;
            }
            //Custom field search


            //start
            $jsst_data = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
            $jsst_valarray = array();
            if (!empty($jsst_data)) {
                foreach ($jsst_data as $jsst_uf) {
                    if (JSSTrequest::getVar('pagenum', 'get', null) != null) {
                        $jsst_valarray[$jsst_uf->field] = $jsst_value_array[$jsst_uf->field];
                    }else{
                        $jsst_valarray[$jsst_uf->field] = JSSTrequest::getVar($jsst_uf->field, 'post');
                    }
                    if (isset($jsst_valarray[$jsst_uf->field]) && $jsst_valarray[$jsst_uf->field] != null) {
                        switch ($jsst_uf->userfieldtype) {
                            case 'text':
                            case 'email':
                                $jsst_inquery .= ' AND ticket.params REGEXP %s';
                                $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '.*"';
                                break;
                            case 'combo':
                                $jsst_inquery .= ' AND ticket.params LIKE %s';
                                $jsst_inquery_args[] = '%"' . $jsst_uf->field . '":"' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '"%';
                                break;
                            case 'depandant_field':
                                $jsst_inquery .= ' AND ticket.params LIKE %s';
                                $jsst_inquery_args[] = '%"' . $jsst_uf->field . '":"' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '"%';
                                break;
                            case 'radio':
                                $jsst_inquery .= ' AND ticket.params LIKE %s';
                                $jsst_inquery_args[] = '%"' . $jsst_uf->field . '":"' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '"%';
                                break;
                            case 'checkbox':
                                $jsst_finalvalue = '';
                                foreach($jsst_valarray[$jsst_uf->field] AS $jsst_value){
                                    $jsst_finalvalue .= $jsst_value.'.*';
                                }
                                $jsst_inquery .= ' AND ticket.params REGEXP %s';
                                $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars($jsst_finalvalue) . '.*"';
                                break;
                            case 'date':
                                $jsst_inquery .= ' AND ticket.params LIKE %s';
                                $jsst_inquery_args[] = '%"' . $jsst_uf->field . '":"' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '"%';
                                break;
                            case 'textarea':
                                $jsst_inquery .= ' AND ticket.params REGEXP %s';
                                $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*' . jssupportticketphplib::JSST_htmlspecialchars($jsst_valarray[$jsst_uf->field]) . '.*"';
                                break;
                            case 'multiple':
                                $jsst_finalvalue = '';
                                foreach($jsst_valarray[$jsst_uf->field] AS $jsst_value){
                                    if($jsst_value != null){
                                        $jsst_finalvalue .= $jsst_value.'.*';
                                    }
                                }
                                if($jsst_finalvalue !=''){
                                    $jsst_inquery .= ' AND ticket.params REGEXP %s';
                                    $jsst_inquery_args[] = '"' . $jsst_uf->field . '":"[^"]*'.htmlspecialchars($jsst_finalvalue).'.*"';
                                }
                                break;
                        }
                        jssupportticket::$jsst_data['filter']['params'] = $jsst_valarray;
                    }
                }
            }
            //end

            if ($jsst_inquery == '')
                jssupportticket::$jsst_data['filter']['combinesearch'] = false;
            else
                jssupportticket::$jsst_data['filter']['combinesearch'] = true;
        }
        if (!empty($jsst_inquery_args)) {
            $jsst_inquery = jssupportticket::$_db->prepare($jsst_inquery, $jsst_inquery_args);
        }
        return $jsst_inquery;
    }

    function getMyTickets($jsst_lst=null) {
        $this->getOrdering();
        // Filter
        /*
          list variable detail
          1=>For open ticket
          2=>For closed ticket
          3=>For open answered ticket
          4=>For all my tickets
         */
        $jsst_inquery = $this->combineOrSingleSearch();
        if($jsst_lst != null){
            jssupportticket::$_search['ticket']['list'] = $jsst_lst;
        }
        $jsst_list = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['list'] : 1;
        jssupportticket::$jsst_data['list'] = $jsst_list; // assign for reference
        switch ($jsst_list) {
            // Ticket Default Status
            // 0 -> New Ticket
            // 1 -> Waiting admin/staff reply
            // 2 -> in progress
            // 3 -> waiting for customer reply
            // 4 -> close ticket
           case 1:$jsst_inquery .= " AND (ticket.status != 5 AND ticket.status != 6)";
                break;
            case 2:$jsst_inquery .= " AND (ticket.status = 5 OR ticket.status = 6) ";
                break;
            case 3:$jsst_inquery .= " AND ticket.status = 4 ";
                break;
            case 4:$jsst_inquery .= " ";
                break;
            case 5:$jsst_inquery .= " AND ticket.isoverdue = 1 AND ticket.status != 5 AND ticket.status != 6 ";
                break;
        }

        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
        if ($jsst_uid && is_numeric($jsst_uid)) {
            // Pagination
            $jsst_query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                        WHERE ticket.uid = %d";
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_uid);
            $jsst_query .= $jsst_inquery;
            $jsst_total = jssupportticket::$_db->get_var($jsst_query);
            jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total,'myticket');

            // Data
            do_action('jsst_addon_user_my_tickets');

            $jsst_query = "SELECT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour, status.status AS statustitle, status.statuscolour, status.statusbgcolour, product.product AS producttitle ".jssupportticket::$_addon_query['select']."
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        ".jssupportticket::$_addon_query['join']."
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ON ticket.productid = product.id";
            $jsst_query .= jssupportticket::$_db->prepare(" WHERE ticket.uid = %d", $jsst_uid) . $jsst_inquery;
            $jsst_query .= " ORDER BY " . jssupportticket::$_ordering . " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
            jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
            do_action('jsst_reset_aadon_query');
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
            // if(jssupportticket::$_config['count_on_myticket'] == 1){
                $jsst_query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE ticket.uid = %d AND (ticket.status != 5 AND ticket.status != 6)";
                jssupportticket::$jsst_data['count']['openticket'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_uid));

                $jsst_query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE ticket.uid = %d AND ticket.status = 4 ";
                jssupportticket::$jsst_data['count']['answeredticket'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_uid));

                $jsst_query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE ticket.uid = %d AND (ticket.status = 5 OR ticket.status = 6)";
                jssupportticket::$jsst_data['count']['closedticket'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_uid));

                $jsst_query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE ticket.uid = %d";
                jssupportticket::$jsst_data['count']['allticket'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_uid));
            // }
        }
        return;
    }

    function getStaffTickets($jsst_lst=null) {
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

        $jsst_inquery = $this->combineOrSingleSearch();
        if($jsst_lst != null) {
            jssupportticket::$_search['ticket']['list'] = $jsst_lst;
        }
        $jsst_list = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['list'] : 1; // assign for reference
        jssupportticket::$jsst_data['list'] = $jsst_list;
        switch ($jsst_list) {
            // Ticket Default Status
            // 1 -> Open Ticket
            // 2 -> Waiting admin/staff reply
            // 3 -> in progress
            // 4 -> waiting for customer reply
            // 5 -> close ticket
            case 1:$jsst_inquery .= " AND (ticket.status != 5 AND ticket.status != 6)";
                break;
            case 2:$jsst_inquery .= " AND (ticket.status = 5 OR ticket.status = 6) ";
                break;
            case 3:$jsst_inquery .= " AND ticket.status = 4 ";
                break;
            case 4:$jsst_inquery .= " ";
                break;
            case 5:$jsst_inquery .= " AND ticket.isoverdue = 1 AND ticket.status != 5 AND ticket.status != 6 ";
                break;
        }

        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
        if ($jsst_uid == 0 || !is_numeric($jsst_uid))
            return false;
        $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);

        //to handle all tickets permissoin
        $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
        if($jsst_allowed == true){
            $jsst_agent_conditions = "1 = 1";
        }else{
            if(is_numeric($jsst_staffid)) {
                $jsst_agent_conditions = "ticket.staffid = ".(int)($jsst_staffid)." OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = " .intval($jsst_staffid).")";
            } else {
                return false;
            }
        }
        //show specific user's tickets
        $jsst_userquery = "";
        $jsst_uid = JSSTrequest::getVar('uid');
        if(is_numeric($jsst_uid) && $jsst_uid > 0){
            $jsst_userquery .= jssupportticket::$_db->prepare(" AND ticket.uid = %d", $jsst_uid);
        }
        // Tag filter for the agent queue. Prepared once so the count and the data
        // query cannot disagree about how many tickets match.
        // (Roadmap 4.0-CORE-17)
        $jsst_searchtagid = isset(jssupportticket::$_search['ticket']['tagid']) ? jssupportticket::$_search['ticket']['tagid'] : '';
        $jsst_tagmodel = JSSTincluder::getJSModel('tag');
        $jsst_tagfilter = $jsst_tagmodel->queueFilter($jsst_searchtagid);
        $jsst_tagwhere = '';
        if ($jsst_tagfilter['where'] !== '') {
            $jsst_tagwhere = jssupportticket::$_db->prepare($jsst_tagfilter['where'], $jsst_tagfilter['args']);
        }
        jssupportticket::$jsst_data['filter']['tagid'] = $jsst_searchtagid;

        // Pagination
        $jsst_query = "SELECT COUNT(DISTINCT ticket.id)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    " . $jsst_tagfilter['join'] . "
                    WHERE (".esc_sql($jsst_agent_conditions).") ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= $jsst_userquery;
        $jsst_query .= $jsst_tagwhere;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total,'myticket');

        // Data
        do_action('jsst_addon_staff_my_tickets');
        $jsst_query = "SELECT DISTINCT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour,assignstaff.photo AS staffphoto,assignstaff.id AS staffid, assignstaff.firstname AS staffname, status.status AS statustitle, status.statusbgcolour, status.statuscolour, product.product AS producttitle ".jssupportticket::$_addon_query['select']."
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ON ticket.productid = product.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS assignstaff ON ticket.staffid = assignstaff.id
                    ".jssupportticket::$_addon_query['join']."
                    " . $jsst_tagfilter['join'] . "
                    WHERE (".esc_sql($jsst_agent_conditions).") " . $jsst_inquery . $jsst_userquery . $jsst_tagwhere;
        $jsst_query .= " ORDER BY " . jssupportticket::$_ordering . " LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        do_action('jsst_reset_aadon_query');
        // Tags for this page of the queue, in one query. (Roadmap 4.0-CORE-17)
        $jsst_pageids = array();
        if (is_array(jssupportticket::$jsst_data[0])) {
            foreach (jssupportticket::$jsst_data[0] AS $jsst_row) {
                $jsst_pageids[] = $jsst_row->id;
            }
        }
        jssupportticket::$jsst_data['ticket_tags'] = $jsst_tagmodel->getTagsForTickets($jsst_pageids);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        // if(jssupportticket::$_config['count_on_myticket'] == 1){
            $jsst_query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($jsst_agent_conditions).") AND (ticket.status != 5 AND ticket.status !=6) ".$jsst_userquery;
            jssupportticket::$jsst_data['count']['openticket'] = jssupportticket::$_db->get_var($jsst_query);

            $jsst_query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($jsst_agent_conditions).") AND ticket.status = 4 ".$jsst_userquery;
            jssupportticket::$jsst_data['count']['answeredticket'] = jssupportticket::$_db->get_var($jsst_query);;

            $jsst_query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($jsst_agent_conditions).") AND (ticket.status = 5 OR ticket.status = 6) ".$jsst_userquery;
            jssupportticket::$jsst_data['count']['closedticket'] = jssupportticket::$_db->get_var($jsst_query);;


            $jsst_query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($jsst_agent_conditions).") AND ticket.isoverdue = 1 AND ticket.status != 5 AND ticket.status != 6 ".$jsst_userquery;
            jssupportticket::$jsst_data['count']['overdue'] = jssupportticket::$_db->get_var($jsst_query);

            $jsst_query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE (".esc_sql($jsst_agent_conditions).")  ".$jsst_userquery;
            jssupportticket::$jsst_data['count']['allticket'] = jssupportticket::$_db->get_var($jsst_query);
        // }
        return;
    }

    function getTicketsForForm($jsst_id,$jsst_formid='') {
        if (!isset($jsst_formid) || $jsst_formid == '' || !is_numeric($jsst_formid)) {
           $jsst_formid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
        }
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            // Editing an existing ticket is an admin/agent action, not a customer self-service one.
            if (!JSSTroles::canEditTicketContent()) {
                return false;
            }
            $jsst_query = "SELECT ticket.*,department.departmentname AS departmentname ,priority.priority AS priority,priority.prioritycolour AS prioritycolour,user.name AS user_login, product.product AS producttitle
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        LEFT JOIN `".jssupportticket::$_wpprefixforuser."js_ticket_users` AS user ON user.id = ticket.uid
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ON ticket.productid = product.id
                        WHERE ticket.id = %d";
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_id);
            jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }else{
                if(!empty(jssupportticket::$jsst_data[0])){
                    //to store hash value of id against old tickets
                    if( jssupportticket::$jsst_data[0]->hash == null ){
                        $jsst_hash = $this->generateHash($jsst_id);
                        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET `hash`=%s WHERE id=%d";
                        $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_hash, $jsst_id);
                        jssupportticket::$_db->query($jsst_query);
                    } //end
                }
            }
            // The row is empty when the id names a ticket that is gone; keep the
            // form id we were called with rather than reading a property off null.
            if (isset(jssupportticket::$jsst_data[0]->multiformid)) {
                $jsst_formid = jssupportticket::$jsst_data[0]->multiformid;
            }
        }
        jssupportticket::$jsst_data['formid'] = $jsst_formid;
        JSSTincluder::getJSModel('attachment')->getAttachmentForForm($jsst_id);
        JSSTincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(1,$jsst_formid);
        return;
    }

    function getTicketForDetail($jsst_id) {
        if (!is_numeric($jsst_id)){
            return $jsst_id;
        }
        if (in_array('agent', jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff']) { //staff
            if(current_user_can('jsst_support_ticket')){
                jssupportticket::$jsst_data['permission_granted'] = true;
                $jsst_user_id = JSSTincluder::getObjectClass('user')->uid();
                if(is_numeric($jsst_user_id)){
                    $jsst_transient_key = "ticket_time_start_".$jsst_id."_".$jsst_user_id;
                    $jsst_transient_data = gmdate("Y-m-d H:i:s");
                    set_transient($jsst_transient_key, $jsst_transient_data, DAY_IN_SECONDS);
                }
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    jssupportticket::$jsst_data['time_taken'] = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($jsst_id);
                }
            }else{
                jssupportticket::$jsst_data['permission_granted'] = $this->validateTicketDetailForStaff($jsst_id);
                if (jssupportticket::$jsst_data['permission_granted']) { // validation passed
                    if(in_array('timetracking', jssupportticket::$_active_addons)){
                        $jsst_user_id = JSSTincluder::getObjectClass('user')->uid();
                        if(is_numeric($jsst_user_id)){
                            $jsst_transient_key = "ticket_time_start_".$jsst_id."_".$jsst_user_id;
                            $jsst_transient_data = gmdate("Y-m-d H:i:s");
                            set_transient($jsst_transient_key, $jsst_transient_data, DAY_IN_SECONDS);
                        }
                        jssupportticket::$jsst_data['time_taken'] = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($jsst_id);
                    }
                }
            }

        } else { // user
            if(current_user_can('jsst_support_ticket') || current_user_can('jsst_support_ticket_tickets')){
                jssupportticket::$jsst_data['permission_granted'] = true;
                if(in_array('timetracking', jssupportticket::$_active_addons)){
                    $jsst_user_id = JSSTincluder::getObjectClass('user')->uid();
                    if(is_numeric($jsst_user_id)){
                        $jsst_transient_key = "ticket_time_start_".$jsst_id."_".$jsst_user_id;
                        $jsst_transient_data = gmdate("Y-m-d H:i:s");
                        set_transient($jsst_transient_key, $jsst_transient_data, DAY_IN_SECONDS);
                    }
                    jssupportticket::$jsst_data['time_taken'] = JSSTincluder::getJSModel('timetracking')->getTimeTakenByTicketId($jsst_id);
                }
            }
            elseif (!JSSTincluder::getObjectClass('user')->isguest())
                jssupportticket::$jsst_data['permission_granted'] = $this->validateTicketDetailForUser($jsst_id);
            else
                jssupportticket::$jsst_data['permission_granted'] = $this->validateTicketDetailForVisitor($jsst_id);
        }
        if (!jssupportticket::$jsst_data['permission_granted']) { // validation failed
            return;
        }

        do_action('jsst_ticket_detail_query');// TO HANDLE ALL THE QUERIES OF ADDONS

        $jsst_query = "SELECT ticket.*,priority.priority AS priority,priority.prioritycolour AS prioritycolour,department.departmentname AS departmentname,status.status AS statustitle,status.statuscolour,status.statusbgcolour, product.product AS producttitle
                     ".jssupportticket::$_addon_query['select']."
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_products` AS product ON ticket.productid = product.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    ".jssupportticket::$_addon_query['join']."
                    WHERE ticket.id = %d";
        $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_id);
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
        do_action('jsst_reset_aadon_query');
        // check email is ban
        if(JSSTmergedaddon::featureEnabled('banemail') && !empty(jssupportticket::$jsst_data[0]->email)){
            // Read straight from the table; see getTicketsForAdmin().
            JSSTmergedaddon::ensureSchema('banemail');
            $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = %s";
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, jssupportticket::$jsst_data[0]->email);
            jssupportticket::$jsst_data[7] = jssupportticket::$_db->get_var($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
            }
        }else{
            jssupportticket::$jsst_data[7] = 0;
        }
        if(JSSTmergedaddon::featureEnabled('note')){
            JSSTincluder::getJSModel('note')->getNotes($jsst_id);
        }
        // The ticket's tags. (Roadmap 4.0-CORE-17)
        jssupportticket::$jsst_data['tags'] = JSSTincluder::getJSModel('tag')->getTicketTags($jsst_id);
        JSSTincluder::getJSModel('reply')->getReplies($jsst_id);
        jssupportticket::$jsst_data['ticket_attachment'] = JSSTincluder::getJSModel('attachment')->getAttachmentForReply($jsst_id, 0);
        $this->getTicketHistory($jsst_id);

        if(jssupportticket::$jsst_data[0]->uid > 0){

            //count all ticket of user
            $jsst_query = "SELECT COUNT(id) FROM `" .jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE `uid` = %d";
            jssupportticket::$jsst_data['nticket'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, jssupportticket::$jsst_data[0]->uid));

            //get user tickets for right widget
            $jsst_inquery = " WHERE ticket.id != %d AND ticket.uid = %d";
            $jsst_inquery_args = array($jsst_id, jssupportticket::$jsst_data[0]->uid);
            if(!is_admin() && in_array('agent', jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff']){
                $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
                if($jsst_allowed != true){
                    $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid());
                    if(is_numeric($jsst_staffid)){
                        $jsst_inquery .= " AND (ticket.staffid = %d OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = %d))";
                        $jsst_inquery_args[] = $jsst_staffid;
                        $jsst_inquery_args[] = $jsst_staffid;
                    }
                }
            }
            $jsst_inquery = jssupportticket::$_db->prepare($jsst_inquery, $jsst_inquery_args);
            $jsst_query = "SELECT ticket.id,ticket.subject,ticket.status,ticket.lock,ticket.isoverdue,ticket.multiformid,priority.priority AS priority,priority.prioritycolour AS prioritycolour,department.departmentname AS departmentname,status.status AS statustitle,status.statuscolour,status.statusbgcolour
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id";
            $jsst_query .= $jsst_inquery . " LIMIT 3 ";
            jssupportticket::$jsst_data['usertickets'] = jssupportticket::$_db->get_results($jsst_query);
        }
        //Hooks
        do_action('jsst-ticketbeforeview', jssupportticket::$jsst_data);

        return;
    }

    function getTicketToken($jsst_id) {
        if (!is_numeric($jsst_id)){
            return $jsst_id;
        }
        $jsst_token = "";
        $jsst_query = "SELECT ticket.token
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    WHERE ticket.id = %d";
        $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_id);
        $jsst_token = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_token;
    }

    function validateUserForTicket($jsst_id) {
        if (!is_numeric($jsst_id)) return false;
        if (!JSSTincluder::getObjectClass('user')->isguest()) {

        } else {
            jssupportticket::$jsst_data['permission_granted'] = $this->checkTokenForTicketDetail($jsst_id);
        }
        return;
    }

    function getRandomTicketId() {
        $jsst_match = '';
        $jsst_customticketno = '';
        $jsst_count = 0;
        //$jsst_match = 'Y';
        do {
            $jsst_count++;
            $jsst_ticketid = "";
            $jsst_length = 9;
            $jsst_sequence = jssupportticket::$_config['ticketid_sequence'];
            if($jsst_sequence == 1){
                $jsst_possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
                // we refer to the length of $jsst_possible a few times, so let's grab it now
                $jsst_maxlength = jssupportticketphplib::JSST_strlen($jsst_possible);
                if ($jsst_length > $jsst_maxlength) { // check for length overflow and truncate if necessary
                    $jsst_length = $jsst_maxlength;
                }
                // set up a counter for how many characters are in the ticketid so far
                $jsst_i = 0;
                // add random characters to $jsst_password until $jsst_length is reached
                while ($jsst_i < $jsst_length) {
                    // pick a random character from the possible ones
                    $jsst_char = jssupportticketphplib::JSST_substr($jsst_possible, wp_rand(0, $jsst_maxlength - 1), 1);
                    if (!strstr($jsst_ticketid, $jsst_char)) {
                        if ($jsst_i == 0) {
                            if (ctype_alpha($jsst_char)) {
                                $jsst_ticketid .= $jsst_char;
                                $jsst_i++;
                            }
                        } else {
                            $jsst_ticketid .= $jsst_char;
                            $jsst_i++;
                        }
                    }
                }
            }else{ // Sequential ticketid
                if($jsst_ticketid == ""){
                    $jsst_ticketid = 0; // by default its set to zero
                }
                //$jsst_maxquery = "SELECT max(convert(ticketid, SIGNED INTEGER)) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`";
                $jsst_maxquery = "SELECT max(customticketno) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`";
                $jsst_maxticketid = jssupportticket::$_db->get_var($jsst_maxquery);
                if(is_numeric($jsst_maxticketid)){
                    $jsst_ticketid = $jsst_maxticketid + $jsst_count;
                }else{
                    $jsst_ticketid = $jsst_ticketid + $jsst_count;
                }
                $jsst_customticketno = $jsst_ticketid;
                $jsst_padding_zeros = JSSTincluder::getJSModel('configuration')->getConfigValue('padding_zeros_ticketid');

                $jsst_idlen = jssupportticketphplib::JSST_strlen($jsst_ticketid);
                while ($jsst_idlen < $jsst_padding_zeros) {
                    $jsst_ticketid = "0".$jsst_ticketid;
                    $jsst_idlen = jssupportticketphplib::JSST_strlen($jsst_ticketid);
                }
            }
            $jsst_prefix = "";
            $jsst_suffix = "";          
            $jsst_prefix = JSSTincluder::getJSModel('configuration')->getConfigValue('prefix_ticketid');
            $jsst_suffix = JSSTincluder::getJSModel('configuration')->getConfigValue('suffix_ticketid');
            $jsst_prefix = jssupportticketphplib::JSST_trim($jsst_prefix);
            $jsst_suffix = jssupportticketphplib::JSST_trim($jsst_suffix);
            if($jsst_prefix) $jsst_ticketid = $jsst_prefix . $jsst_ticketid;
            if($jsst_suffix) $jsst_ticketid = $jsst_ticketid . $jsst_suffix;
            
            $jsst_query = "SELECT count(ticketid) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE ticketid = '%d'";
            $jsst_row = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_ticketid));
            if($jsst_row > 0)
                $jsst_match = 'Y';
            else
                $jsst_match = 'N';
            /*
            $jsst_rows = jssupportticket::$_db->get_results($jsst_query);
                foreach ($jsst_rows as $jsst_row) {
                    if ($jsst_ticketid == $jsst_row->ticketid)
                        $jsst_match = 'Y';
                    else
                        $jsst_match = 'N';
                }
             */   
        }while ($jsst_match == 'Y');
        $jsst_result = array();
        $jsst_result['ticketid'] = $jsst_ticketid;
        $jsst_result['customticketno'] = $jsst_customticketno;
        return $jsst_result;
    }

    function countTicket($jsst_emailorid) {
        if (is_numeric($jsst_emailorid)) { // its UserID
            $jsst_query = jssupportticket::$_db->prepare("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = %d", $jsst_emailorid);
        } else { // its EmailAddress
            $jsst_query = jssupportticket::$_db->prepare("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = %s", $jsst_emailorid);
        }
        $jsst_counts = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_counts;
    }

    function getUnresolvedAdminTicketsCount() {
        $jsst_counts = jssupportticket::$_db->get_var("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket WHERE ticket.status != 4 AND ticket.status != 5 AND ticket.status != 6");
        return $jsst_counts;
    }

    function countOpenTicket($jsst_emailorid) {
        if (is_numeric($jsst_emailorid)) { // its UserID
            $jsst_query = jssupportticket::$_db->prepare("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE uid = %d AND status != 5", $jsst_emailorid);
        } else { // its EmailAddress
            $jsst_query = jssupportticket::$_db->prepare("SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = %s AND status != 5", $jsst_emailorid);
        }
        $jsst_counts = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_counts;
    }

    function checkBannedEmail($jsst_emailaddress) {
        if(!JSSTmergedaddon::featureEnabled('banemail')){
            return true;
        }
        // One implementation, so the domain rule applies wherever a sender is
        // checked. (Roadmap 4.0-CORE-12)
        if (JSSTincluder::getJSModel('banemail')->isEmailBan($jsst_emailaddress)) {
            $jsst_data['loggeremail'] = $jsst_emailaddress;
            $jsst_data['title'] = esc_html(__('Ban Email', 'js-support-ticket'));
            $jsst_data['log'] = esc_html(__('Ban Email Try To Create Ticket', 'js-support-ticket'));
            $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $jsst_currentUserName = $jsst_current_user->display_name;
            $jsst_data['logger'] = $jsst_currentUserName;
            $jsst_data['ipaddress'] = $this->getIpAddress();
            // Blocking without a record of what was blocked is not much use to an
            // administrator, so the log is core too. (Roadmap 4.0-CORE-12)
            JSSTincluder::getJSModel('banemaillog')->storebanemaillog($jsst_data);
            JSSTmessage::setMessage(esc_html(__('Banned email cannot create ticket', 'js-support-ticket')), 'error');
            return false;
        }
        return true;
    }

    function getIpAddress() {
        //if client use the direct ip
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $jsst_ip = jssupportticket::JSST_sanitizeData($_SERVER['HTTP_CLIENT_IP']); // JSST_sanitizeData() function uses wordpress santize functions
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $jsst_ip = jssupportticket::JSST_sanitizeData($_SERVER['HTTP_X_FORWARDED_FOR']); // JSST_sanitizeData() function uses wordpress santize functions
        } else {
            $jsst_ip = jssupportticket::JSST_sanitizeData($_SERVER['REMOTE_ADDR']); // JSST_sanitizeData() function uses wordpress santize functions
        }
        return $jsst_ip;
    }

    function ticketValidate($jsst_emailaddress) {
        //check the banned user / email
        if(JSSTmergedaddon::featureEnabled('banemail')){
            if (!$this->checkBannedEmail($jsst_emailaddress)) {
                return false;
            }
        }
        if(JSSTmergedaddon::featureEnabled('maxticket')){
            //check the Maximum Tickets
            if (!JSSTincluder::getJSModel('maxticket')->checkMaxTickets($jsst_emailaddress)) {
                return false;
            }

            //check the Maximum Open Tickets

            if (!JSSTincluder::getJSModel('maxticket')->checkMaxOpenTickets($jsst_emailaddress)) {
                return false;
            }
        }

        return true;
    }

    function captchaValidate() {
        // Rate limit first: it costs nothing and it is the check that stops a
        // flood, whether or not the flood can pass verification.
        // (Roadmap 4.0-SEC-01)
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            if (!JSSTratelimit::check('ticket')) {
                JSSTmessage::setMessage(JSSTratelimit::message(), 'error');
                return false;
            }
            if (jssupportticket::$_config['show_captcha_on_visitor_from_ticket'] == 1) {
                $jsst_verification = JSSTincluder::getObjectClass('verification');
                if (!$jsst_verification->verify('ticket')) {
                    JSSTmessage::setMessage($jsst_verification->lastError(), 'error');
                    return false;
                }
            }
        }
    return true;
    }

    function storeTickets($jsst_data) {
        // Normalise the keys the rest of this method reads unconditionally. A
        // front-end form that omits any of them (a custom template, a page
        // builder that drops hidden inputs, e-mail piping) used to produce PHP
        // notices and an unpredictable result. (Roadmap 3.2-CORE-03)
        $jsst_data['id'] = isset($jsst_data['id']) ? $jsst_data['id'] : '';
        $jsst_data['uid'] = isset($jsst_data['uid']) ? $jsst_data['uid'] : 0;
        $jsst_data['subject'] = isset($jsst_data['subject']) ? $jsst_data['subject'] : '';
        if (!isset($jsst_data['multiformid']) || $jsst_data['multiformid'] === '') {
            $jsst_data['multiformid'] = $this->getDefaultMultiFormId();
        }

        // The subject is required on every form. Checking it here, with its own
        // message, stops a missing subject from being reported as a duplicate.
        if (trim((string) $jsst_data['subject']) === '') {
            JSSTmessage::setMessage(esc_html(__('Subject cannot be empty', 'js-support-ticket')), 'error');
            return false;
        }

        if (isset($jsst_data['email'])) {
            $jsst_checkduplicatetk = $this->checkIsTicketDuplicate($jsst_data['subject'],$jsst_data['email']);
            if(!$jsst_checkduplicatetk){
                // Previously this returned false with no message, so the form
                // came back blank and the customer had no idea why.
                JSSTmessage::setMessage(esc_html(__('That ticket looks like a duplicate of one you just submitted. Please check your tickets before sending it again.', 'js-support-ticket')), 'error');
                return false;
            }
        }
        // Topic form rules. Ordered deliberately: an explicit choice wins, then
        // the topic fills what was left blank, and only then does department
        // auto-assign have anything to do. Running server-side means a ticket
        // that arrives by e-mail piping, by import or from a form whose
        // JavaScript never ran is routed the same way as one typed into the
        // browser. (Roadmap 4.0-CORE-20)
        //
        // The method check is the merge contract, not defensive habit: on a site
        // still running the stand-alone Help Topic add-on, getJSModel('helptopic')
        // is the add-on's class, which has no form rules. Core stands down and
        // the routing behaves exactly as that add-on always did.
        // (Roadmap 4.0-CORE-19)
        if (JSSTmergedaddon::featureEnabled('helptopic')) {
            $jsst_topicmodel = JSSTincluder::getJSModel('helptopic');
            if (method_exists($jsst_topicmodel, 'applyFormRules')) {
                $jsst_data = $jsst_topicmodel->applyFormRules($jsst_data);
            }
        }

        if(isset($jsst_data['departmentid']) && $jsst_data['departmentid'] == ''){
            // auto assign
            $jsst_data['departmentid'] = JSSTincluder::getJSModel('department')->getDepartmentIDForAutoAssign();
        }

        if (!is_admin() && ( !isset($jsst_data['ticketviaemail']) || $jsst_data['ticketviaemail'] != 1) ) { //if not admin or Email Piping
            if (!$this->captchaValidate()) {
                //JSSTmessage::setMessage(esc_html(__('Incorrect Captcha code', 'js-support-ticket')), 'error');
                return false;
            }
            $jsst_email = isset($jsst_data['email']) ? $jsst_data['email'] : '';
            if (!$this->ticketValidate($jsst_email)) {
                // This used to return the literal 3, which the caller compared
                // loosely against false and therefore read as success - and 3 is
                // also a perfectly valid ticket id, so a banned sender or a
                // customer over their ticket limit was redirected to ticket 3's
                // page as though their ticket had been created. The validators
                // set their own error message. (Roadmap 3.2-CORE-03)
                return false;
            }
        }

        //paid support validation
        if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce')){
            //ignore if admin or agent or visitor
            if(!JSSTincluder::getObjectClass('user')->isguest() && !is_admin() && !(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())){
                $jsst_paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->wpuid(),$jsst_data['paidsupportid']);
                if(empty($jsst_paidsupport)){
                    JSSTmessage::setMessage(esc_html(__('Please select paid support item', 'js-support-ticket')), 'error');
                    return false;
                }
            }
        }

        $jsst_data['ticketviaemail'] = isset($jsst_data['ticketviaemail']) ? $jsst_data['ticketviaemail'] : 0;
        if($jsst_data['ticketviaemail'] != 1){ // do not check in ticket via email case
            //envato purchase code validation
            if(in_array('envatovalidation', jssupportticket::$_active_addons)){
                $jsst_code = isset($jsst_data['envatopurchasecode']) ? $jsst_data['envatopurchasecode'] : '';
                $jsst_pcode = isset($jsst_data['prev_envatopurchasecode']) ? $jsst_data['prev_envatopurchasecode'] : '';
                $jsst_required = JSSTincluder::getJSModel('configuration')->getConfigValue('envato_license_required');
                if($jsst_required != 1 && empty($jsst_code) && !empty($jsst_pcode)){
                    $jsst_envatoData = '';
                }
                if( (!empty($jsst_code) && (empty($jsst_pcode) || $jsst_pcode!=$jsst_code)) || ($jsst_required==1 && (empty($jsst_pcode) || $jsst_pcode!=$jsst_code)) ){
                    $jsst_res = JSSTincluder::getJSModel('envatovalidation')->validatePurchaseCode($jsst_code);
                    if(!$jsst_res){
                        JSSTmessage::setMessage(esc_html(__('No purchase found with that code', 'js-support-ticket')), 'error');
                        return false;
                    }else{
                        $jsst_envatoData = wp_json_encode($jsst_res);
                    }
                }
            }
        }

        // edd license
        if($jsst_data['ticketviaemail'] != 1){ // do not check in ticket via email case
            if(in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                if(jssupportticket::$_config['verify_license_on_ticket_creation'] == 1){
                    if(isset($jsst_data['eddlicensekey'])){
                        if($jsst_data['eddlicensekey'] == ''){
                            JSSTmessage::setMessage(esc_html(__('Provide a valid license key to create a ticket.', 'js-support-ticket')), 'error');
                            return false;
                        }else{
                            $jsst_l_result = JSSTincluder::getJSModel('easydigitaldownloads')->getEDDLicenseVerification($jsst_data['eddlicensekey']);
                            if($jsst_l_result == 'expired'){
                                JSSTmessage::setMessage(esc_html(__('Your license has expired.', 'js-support-ticket')), 'error');
                                return false;
                            }elseif($jsst_l_result == 'inactive'){
                                JSSTmessage::setMessage(esc_html(__('Your license is not active, activate your license.', 'js-support-ticket')), 'error');
                                return false;
                            }
                        }
                    }
                }
            }
        }

        $jsst_sendEmail = true;
        $jsst_isedit = false;
        $jsst_existing_attachmentdir = '';
        $jsst_existing_status = 0;
        if (isset($jsst_data['id']) && is_numeric($jsst_data['id'])) {
            $jsst_isedit = true;
            $jsst_sendEmail = false;
            $jsst_updated = date_i18n('Y-m-d H:i:s');
            $jsst_created = $jsst_data['created'];
            if (isset($jsst_data['isoverdue']) &&  $jsst_data['isoverdue'] == 1) {// for edit case to change the overdue if criteria is passed
                $jsst_curdate = date_i18n('Y-m-d H:i:s');
                if (date_i18n('Y-m-d',strtotime($jsst_data['duedate'])) > date_i18n('Y-m-d',strtotime($jsst_curdate))){
                    $jsst_data['isoverdue'] = 0;
                }else{
                    $jsst_query = "SELECT ticket.duedate FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` AS ticket WHERE ticket.id = %d";
                    $jsst_duedate = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_data['id']));
                    if(date_i18n('Y-m-d',strtotime($jsst_data['duedate'])) != date_i18n('Y-m-d',strtotime($jsst_duedate))){
                        JSSTticketModel::setMessage(esc_html(__('Due date error is not valid','js-support-ticket')),'error');
                        return; //Due Date must be greater then current date
                    }
                }
            }
            //to check hash and keep server-side attachment folder for edit case
            $jsst_query = "SELECT hash,uid,attachmentdir,status FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id=%d";
            $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare($jsst_query, $jsst_data['id']));
            if(empty($jsst_row)){
                return false;
            }
            $jsst_edituid = $jsst_row->uid;
            $jsst_existing_attachmentdir = isset($jsst_row->attachmentdir) ? $jsst_row->attachmentdir : '';
            $jsst_existing_status = isset($jsst_row->status) ? (int) $jsst_row->status : 0;
            /* Asked of the guard, which owns the naming rule, rather than
               matched here against a shape that only one era of tickets had. */
            $jsst_dirok = class_exists('JSSTattachmentguard')
                    ? JSSTattachmentguard::isValidFolderName($jsst_existing_attachmentdir)
                    : (preg_match('/^[A-Za-z0-9]{7,64}$/', (string) $jsst_existing_attachmentdir) === 1);
            if($jsst_existing_attachmentdir == '' || !$jsst_dirok){
                JSSTmessage::setMessage(esc_html(__('Invalid attachment folder', 'js-support-ticket')), 'error');
                return false;
            }
            if( $jsst_row->hash != $this->generateHash($jsst_data['id']) ){
                return false;
            }//end
        } else {
            $jsst_idresult = $this->getRandomTicketId();
            $jsst_data['ticketid'] = $jsst_idresult['ticketid'];
            $jsst_data['token'] = $this->generateTicketToken();
            $jsst_data['customticketno'] = $jsst_idresult['customticketno'];

            $jsst_created = date_i18n('Y-m-d H:i:s');
            $jsst_updated = '';
        }

        // Do not trust attachmentdir from POST. It is a filesystem folder name and must stay server-controlled.
        if($jsst_isedit == true){
            $jsst_data['attachmentdir'] = $jsst_existing_attachmentdir;
        }else{
            $jsst_data['attachmentdir'] = $this->getRandomFolderName();
        }

        if(isset($jsst_data['assigntome']) && $jsst_data['assigntome'] == 1){
            if (in_array('agent',jssupportticket::$_active_addons)) {
                $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
                if(is_numeric($jsst_uid)){
                    $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);
                    $jsst_data['staffid'] = $jsst_staffid;
                }
            }
        }else{
            $jsst_data['staffid'] = isset($jsst_data['staffid']) ? $jsst_data['staffid'] : '';
        }
        if (!isset($jsst_data['status']) || $jsst_data['status'] == 0) {
            $jsst_data['status'] = ($jsst_isedit == true && $jsst_existing_status > 0)
                ? $jsst_existing_status
                : 1;
        }
        $jsst_data['duedate'] = !empty($jsst_data['duedate']) ? date_i18n('Y-m-d',strtotime($jsst_data['duedate']))  : '';
        $jsst_data['lastreply'] = isset($jsst_data['lastreply']) ? $jsst_data['lastreply'] : '';
        if (isset($jsst_data['jsticket_message'])) {
            $jsst_data['message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($jsst_data['jsticket_message']); // use jsticket_message to avoid conflict
            $jsst_jsticket_message = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($jsst_data['message']);
            $jsst_jsticket_message = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_jsticket_message);
        }
        //check if message field is set as required or not
        $jsst_isRequired = JSSTincluder::getJSmodel('fieldordering')->checkIsFieldRequired('issuesummary',$jsst_data['multiformid']);
        if(empty($jsst_data['message']) && $jsst_isRequired == 1){
            JSSTmessage::setMessage(esc_html(__('Message field cannot be empty', 'js-support-ticket')), 'error');
            return false;
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions
        if(isset($jsst_envatoData)){
            $jsst_data['envatodata'] = $jsst_envatoData;
        }
        //custom field code start
        $jsst_customflagforadd = false;
        $jsst_customflagfordelete = false;
        $jsst_custom_field_namesforadd = array();
        $jsst_custom_field_namesfordelete = array();
        //if(!isset($jsst_data['multiformid'])) $jsst_data['multiformid'] = ""; may a fix
        $jsst_userfield = JSSTincluder::getJSModel('fieldordering')->getUserfieldsfor(1,$jsst_data['multiformid']);
        $jsst_params = array();
        $jsst_maxfilesizeallowed = jssupportticket::$_config['file_maximum_size'];
        foreach ($jsst_userfield AS $jsst_ufobj) {
            $jsst_vardata = '';
            if($jsst_ufobj->userfieldtype == 'file'){
                if(isset($jsst_data[$jsst_ufobj->field.'_1']) && $jsst_data[$jsst_ufobj->field.'_1']== 0){
                    $jsst_vardata = $jsst_data[$jsst_ufobj->field.'_2'];
                }
                $jsst_customflagforadd=true;
                $jsst_custom_field_namesforadd[]=$jsst_ufobj->field;
            }else if($jsst_ufobj->userfieldtype == 'date'){
                //gmdate makes error
                $jsst_vardata = isset($jsst_data[$jsst_ufobj->field]) ? gmdate("Y-m-d", jssupportticketphplib::JSST_strtotime($jsst_data[$jsst_ufobj->field])) : '';
            }else{
                $jsst_vardata = isset($jsst_data[$jsst_ufobj->field]) ? $jsst_data[$jsst_ufobj->field] : '';
            }
            if(isset($jsst_data[$jsst_ufobj->field.'_1']) && $jsst_data[$jsst_ufobj->field.'_1'] == 1){
                $jsst_customflagfordelete = true;
                $jsst_custom_field_namesfordelete[]= $jsst_data[$jsst_ufobj->field.'_2'];
            }
            if($jsst_vardata != ''){

                if(is_array($jsst_vardata)){
                    $jsst_vardata = implode(', ', array_filter($jsst_vardata));
                }
                $jsst_params[$jsst_ufobj->field] = jssupportticketphplib::JSST_htmlentities($jsst_vardata);
            }
        }
        if($jsst_data['id'] != ''){
            if(is_numeric($jsst_data['id'])){
                $jsst_query = "SELECT params FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
                $jsst_oParams = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_data['id']));

                if(!empty($jsst_oParams)){
                    $jsst_oParams = json_decode($jsst_oParams,true);
                    $jsst_unpublihsedFields = JSSTincluder::getJSModel('fieldordering')->getUserUnpublishFieldsfor(1);
                    foreach($jsst_unpublihsedFields AS $jsst_field){
                        if(isset($jsst_oParams[$jsst_field->field])){
                            $jsst_params[$jsst_field->field] = $jsst_oParams[$jsst_field->field];
                        }
                    }
                }
            }
        }
        $jsst_params = html_entity_decode(wp_json_encode($jsst_params, JSON_UNESCAPED_UNICODE));
        $jsst_data['params'] = $jsst_params;
        //custom field code end

    if (!empty($jsst_jsticket_message)) {
            $jsst_data['message'] = $jsst_jsticket_message;
        }
        $jsst_data['created'] = $jsst_created;
        $jsst_data['updated'] = $jsst_updated;

        if($jsst_data['uid'] == 0 && isset($_SESSION['js-support-ticket']['notificationid'])){
            $jsst_data['notificationid'] = jssupportticket::JSST_sanitizeData($_SESSION['js-support-ticket']['notificationid']); // JSST_sanitizeData() function uses wordpress santize functions
        }
        if(isset($jsst_data['id']) && is_numeric($jsst_data['id'])){
           $jsst_data['uid'] = $jsst_edituid;
        }
        $jsst_sendnotification = false;
        $jsst_ticketid = 0;
        // Snapshot the fields the timeline reports on, so an edit can be logged
        // as "what changed" rather than "something changed".
        // (Roadmap 4.0-CORE-01)
        $jsst_fields_before = array();
        if ($jsst_isedit == true) {
            $jsst_fields_before = $this->getTimelineFieldValues($jsst_data['id']);
        }
        $jsst_row = JSSTincluder::getJSTable('tickets');
        // this line make problem with custom field data (latin words)
        //$jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 1) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
            JSSTmessage::setMessage(esc_html(__('Ticket has not been created', 'js-support-ticket')), 'error');
        } else {
            $jsst_ticketid = $jsst_row->id;
            $jsst_sendnotification = true;
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));

            //update hash value against ticket
            $jsst_hash = $this->generateHash($jsst_ticketid);
            $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET `hash`=%s WHERE id=%d";
            jssupportticket::$_db->query(jssupportticket::$_db->prepare($jsst_query, $jsst_hash, $jsst_ticketid));

            // Storing Attachments
            $jsst_data['ticketid'] = $jsst_ticketid;
            if($jsst_data['ticketviaemail'] != 1){ // since ticket via emial attacments are handled saprately
               JSSTincluder::getJSModel('attachment')->storeAttachments($jsst_data);
               JSSTmessage::setMessage(esc_html(__('Ticket created', 'js-support-ticket')), 'updated');

               //removing custom field attachments
                if($jsst_customflagfordelete == true){
                    foreach ($jsst_custom_field_namesfordelete as $jsst_key) {
                       $jsst_res = $this->removeFileCustom($jsst_ticketid,$jsst_key);
                    }
                }
                //storing custom field attachments
                if($jsst_customflagforadd == true){
                    foreach ($jsst_custom_field_namesforadd as $jsst_key) {
                        // A custom file field that the customer left empty is
                        // simply absent from $_FILES on some server
                        // configurations. (Roadmap 3.2-CORE-03)
                        if (isset($_FILES[$jsst_key]) && !empty($_FILES[$jsst_key]['size'])) { // logo
                           $jsst_res = $this->uploadFileCustom($jsst_ticketid,$jsst_key);
                        }
                    }
                }

                //update paid support item tickets
                if(isset($jsst_paidsupport)){
                    $jsst_paidsupport = $jsst_paidsupport[0];
                    $jsst_res = JSSTincluder::getJSModel('paidsupport')->recordTicket($jsst_paidsupport->itemid, $jsst_ticketid);
                    if($jsst_res){
                        $jsst_t = JSSTincluder::getJSTable('tickets');
                        if($jsst_t->bind(array('id'=>$jsst_ticketid,'paidsupportitemid'=>$jsst_paidsupport->itemid))){
                            $jsst_t->store();
                        }
                    }
                }

            }
        }
        // Only announce a ticket that actually exists. Firing this on the error
        // path passed an undefined id to every listening add-on.
        // (Roadmap 3.2-CORE-03)
        if ($jsst_error != 1) {
            do_action('jsst_after_ticket_create',$jsst_data,$jsst_ticketid);
        }


        /* Push Notification */
        if($jsst_data['id'] == '' && $jsst_sendnotification == true && in_array('notification', jssupportticket::$_active_addons)){
            $jsst_dataarray = array();
            $jsst_dataarray['title'] = $jsst_data['subject'];
            $jsst_dataarray['body'] = esc_html(__("Created","js-support-ticket"));

            //send notification to admin
            $jsst_devicetoken = JSSTincluder::getJSModel('notification')->checkSubscriptionForAdmin();
            if($jsst_devicetoken){
                $jsst_dataarray['link'] = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=".$jsst_ticketid);
                $jsst_dataarray['devicetoken'] = $jsst_devicetoken;
                $jsst_value = jssupportticket::$_config[md5(JSTN)];
                if($jsst_value != ''){
                  do_action('jsst_send_push_notification',$jsst_dataarray);
                }else{
                  do_action('jsst_resetnotificationvalues');
                }
            }

            $jsst_dataarray['link'] = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', "jssupportticketid"=>$jsst_ticketid,'jsstpageid'=>jssupportticket::getPageid()));
            // for department staff
            if(!empty($jsst_data['departmentid']) && is_numeric($jsst_data['departmentid'])){
                JSSTincluder::getJSModel('notification')->sendNotificationToDepartment($jsst_data['departmentid'],$jsst_dataarray);
            }
            // for all
            if(isset($jsst_data['departmentid']) && $jsst_data['departmentid'] == ''){
                JSSTincluder::getJSModel('notification')->sendNotificationToAllStaff($jsst_dataarray);
            }

            // send notification to uid(ticket create for)
            if($jsst_data['uid'] > 0 && is_numeric($jsst_data['uid']) && ($jsst_data['uid'] != JSSTincluder::getObjectClass('user')->uid())){
                $jsst_devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($jsst_data['uid']);
                $jsst_dataarray['devicetoken'] = $jsst_devicetoken;
                if($jsst_devicetoken != '' && !empty($jsst_devicetoken)){
                    $jsst_value = jssupportticket::$_config[md5(JSTN)];
                    if($jsst_value != ''){
                      do_action('jsst_send_push_notification',$jsst_dataarray);
                    }else{
                      do_action('jsst_resetnotificationvalues');
                    }
                }
            }else if($jsst_data['uid'] == 0 && isset($jsst_data['notificationid']) && $jsst_data['notificationid'] != ""){ //visitor
                $jsst_tokenarray['emailaddress'] = $jsst_data['email'];
                $jsst_tokenarray['trackingid'] = $jsst_data['ticketid'];
                $jsst_tokenarray['sitelink']=JSSTincluder::getJSModel('jssupportticket')->getEncriptedSiteLink();
                $jsst_token = wp_json_encode($jsst_tokenarray);
                include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
                $jsst_encoder = new JSSTEncoder();
                $jsst_encryptedtext = $jsst_encoder->encrypt($jsst_token);
                $jsst_dataarray['link'] = jssupportticket::makeUrl(array('jstmod'=>'ticket' ,'task'=>'showticketstatus','action'=>'jstask','token'=>$jsst_encryptedtext,'jsstpageid'=>jssupportticket::getPageid()));
                $jsst_devicetoken = JSSTincluder::getJSModel('notification')->getUserDeviceToken($jsst_data['notificationid'],0);
                $jsst_dataarray['devicetoken'] = $jsst_devicetoken;
                if($jsst_devicetoken != '' && !empty($jsst_devicetoken)){
                    $jsst_value = jssupportticket::$_config[md5(JSTN)];
                    if($jsst_value != ''){
                      do_action('jsst_send_push_notification',$jsst_dataarray);
                    }else{
                      do_action('jsst_resetnotificationvalues');
                    }
                }
            }

        }


        /* for activity log */
        if (!JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $jsst_currentUserName = $jsst_current_user->display_name;
        }else{
            $jsst_currentUserName = esc_html(__('Guest','js-support-ticket'));
        }
        $jsst_eventtype = esc_html(__('New Ticket', 'js-support-ticket'));
        if (isset($jsst_data['id']) && is_numeric($jsst_data['id'])) {
            $jsst_message = esc_html(__('Ticket is updated by', 'js-support-ticket')) . " ( " . $jsst_currentUserName . " ) ";
        } else {
            $jsst_message = esc_html(__('Ticket is created by', 'js-support-ticket')) . " ( " . $jsst_currentUserName . " ) ";
        }
        if(JSSTmergedaddon::featureEnabled('tickethistory')){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
        }
        // One extra event per changed field on an edit. Only core records diffs;
        // the stand-alone add-on has no columns for them.
        // (Roadmap 4.0-CORE-01, 4.0-CORE-19)
        if ($jsst_isedit == true && $jsst_error != 1 && JSSTmergedaddon::coreOwns('tickethistory')) {
            $jsst_fields_after = $this->getTimelineFieldValues($jsst_ticketid);
            JSSTincluder::getJSModel('tickethistory')->logFieldChanges(
                $jsst_ticketid,
                $jsst_fields_before,
                $jsst_fields_after,
                $this->getTimelineFieldLabels()
            );
        }

        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 1, $jsst_ticketid); // Mailfor, Create Ticket, Ticketid
            //For Hook
            $jsst_ticketobject = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d", $jsst_ticketid));
            do_action('jsst-ticketcreate', $jsst_ticketobject);
        }
        /* to store internal notes */
        if(JSSTmergedaddon::featureEnabled('note')){
            if (isset($jsst_data['internalnote']) && $jsst_data['internalnote'] != '') {
                JSSTincluder::getJSModel('note')->storeTicketInternalNote($jsst_data, $jsst_data['internalnote']);
            }
        }
        /* agent auto assign */
        if ($jsst_error != 1) {
            do_action('jsst-agentautoassign', $jsst_ticketid);
        }
        // 0 when the insert failed, so the caller can tell success from failure
        // without a sentinel value. (Roadmap 3.2-CORE-03)
        return $jsst_error == 1 ? false : $jsst_ticketid;
    }

    function uploadFileCustom($jsst_id,$jsst_field){
        if(is_numeric($jsst_id))
            JSSTincluder::getObjectClass('uploads')->storeTicketCustomUploadFile($jsst_id,$jsst_field);
    }

    function storeUploadFieldValueInParams($jsst_ticketid,$jsst_filename,$jsst_field){
        if(!is_numeric($jsst_ticketid)) return false;
        $jsst_query = "SELECT params FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id = %d";
        $jsst_params = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_ticketid));
        $jsst_decoded_params = json_decode($jsst_params,true);
        $jsst_decoded_params[$jsst_field] = $jsst_filename;
        $jsst_encoded_params = wp_json_encode($jsst_decoded_params, JSON_UNESCAPED_UNICODE);
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET params = %s WHERE id = %d";
        jssupportticket::$_db->query(jssupportticket::$_db->prepare($jsst_query, $jsst_encoded_params, $jsst_ticketid));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function removeTicket($jsst_id) {
        $jsst_sendEmail = true;
        if (!is_numeric($jsst_id))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        }
        if ($this->canRemoveTicket($jsst_id)) {
            jssupportticket::$jsst_data['ticketid'] = $this->getTrackingIdById($jsst_id);
            jssupportticket::$jsst_data['ticketemail'] = $this->getTicketEmailById($jsst_id);
            jssupportticket::$jsst_data['staffid'] = $this->getStaffIdById($jsst_id);
            jssupportticket::$jsst_data['ticketsubject'] = $this->getTicketSubjectById($jsst_id);
            // delete attachments
            $this->removeTicketAttachmentsByTicketid($jsst_id);

            $jsst_row = JSSTincluder::getJSTable('tickets');
            if ($jsst_row->delete($jsst_id)) {
                $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
                JSSTmessage::setMessage(esc_html(__('Ticket has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Ticket has not been deleted', 'js-support-ticket')), 'error');
                $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
                $jsst_sendEmail = false;
            }

            // Send Emails
            if ($jsst_sendEmail == true) {
                JSSTincluder::getJSModel('email')->sendMail(1, 3); // Mailfor, Delete Ticket
                $jsst_ticketobject = (object) array('ticketid' => jssupportticket::$jsst_data['ticketid'], 'ticketemail' => jssupportticket::$jsst_data['ticketemail']);
                do_action('jsst-ticketdelete', $jsst_ticketobject);
            }
            if(JSSTmergedaddon::featureEnabled('note')){
                // delete internal notes
                JSSTincluder::getJSModel('note')->removeTicketInternalNote($jsst_id);
            }
            // Tag links go with the ticket, or the join table keeps rows pointing
            // at nothing. (Roadmap 4.0-CORE-17)
            JSSTincluder::getJSModel('tag')->removeTicketTags($jsst_id);
            // delete replies
            JSSTincluder::getJSModel('reply')->removeTicketReplies($jsst_id);
        } elseif (JSSTincluder::getObjectClass('user')->uid() != 0) { // Not visitor {
            JSSTmessage::setMessage(esc_html(__('Ticket','js-support-ticket')).' '. esc_html(__('in use cannot be deleted', 'js-support-ticket')), 'error');
        }

        return;
    }

    function removeEnforceTicket($jsst_id) {
        if (!current_user_can('manage_options') || !is_numeric($jsst_id)) { //only admin can change it.
            return false;
        }
        $jsst_sendEmail = true;
        if (!is_numeric($jsst_id))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        }

        jssupportticket::$jsst_data['ticketid'] = $this->getTrackingIdById($jsst_id);
        jssupportticket::$jsst_data['ticketemail'] = $this->getTicketEmailById($jsst_id);
        jssupportticket::$jsst_data['staffid'] = $this->getStaffIdById($jsst_id);
        jssupportticket::$jsst_data['ticketsubject'] = $this->getTicketSubjectById($jsst_id);
        // delete attachments
        $this->removeTicketAttachmentsByTicketid($jsst_id);

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->delete($jsst_id)) {
        // delete attachments
        //$this->removeTicketAttachmentsByTicketid($jsst_id);
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
            JSSTmessage::setMessage(esc_html(__('Ticket has been deleted', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Ticket has not been deleted', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
        }

        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 3); // Mailfor, Delete Ticket
            $jsst_ticketobject = (object) array('ticketid' => jssupportticket::$jsst_data['ticketid'], 'ticketemail' => jssupportticket::$jsst_data['ticketemail']);
            do_action('jsst-ticketdelete', $jsst_ticketobject);
        }
        if(JSSTmergedaddon::featureEnabled('note')){
            // delete internal notes
            JSSTincluder::getJSModel('note')->removeTicketInternalNote($jsst_id);
        }
        // Tag links go with the ticket here as well. (Roadmap 4.0-CORE-17)
        JSSTincluder::getJSModel('tag')->removeTicketTags($jsst_id);
        // delete replies
        JSSTincluder::getJSModel('reply')->removeTicketReplies($jsst_id);

        return;
    }

    private function removeTicketAttachmentsByTicketid($jsst_id) {

        if (!is_numeric($jsst_id)) return false;

        // --- INITIALIZE WP_FILESYSTEM ---
        global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
            WP_Filesystem();
        }
        $jsst_wp_filesystem = $wp_filesystem;

        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_maindir = wp_upload_dir();
        $jsst_mainpath = $jsst_maindir['basedir'] . '/' . $jsst_datadirectory . '/attachmentdata';

        // Using prepare with your specific prefix format
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT attachmentdir FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
            $jsst_id
        );
        $jsst_foldername = jssupportticket::$_db->get_var($jsst_query);

        if (!empty($jsst_foldername)) {
            $jsst_folder_path = $jsst_mainpath . '/ticket/' . $jsst_foldername;

            // Use WP_Filesystem methods instead of file_exists, glob, unlink, and rmdir
            if ($jsst_wp_filesystem->exists($jsst_folder_path)) {
                // 'true' makes the delete recursive, clearing all files and the folder safely
                $jsst_wp_filesystem->delete($jsst_folder_path, true);

                // Secure DELETE query
                $jsst_delete_query = jssupportticket::$_db->prepare(
                    "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments` WHERE ticketid = %d",
                    $jsst_id
                );
                jssupportticket::$_db->query($jsst_delete_query);
            }
        }
    }

    private function canRemoveTicket($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        if (!$this->canUserPerformThisAction($jsst_id)) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed','js-support-ticket')), 'error');
            return false;
        }
        $jsst_query = "SELECT (
                    (SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid = %d) ";
                    $jsst_query_args = array($jsst_id);
                    if(JSSTmergedaddon::featureEnabled('note')){
                        $jsst_query .= " +(SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_notes` WHERE ticketid = %d) ";
                        $jsst_query_args[] = $jsst_id;
                    }
                    $jsst_query .= "
                    ) AS total";
        $jsst_result = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_query_args));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($jsst_result == 0)
            return true;
        else
            return false;
    }

    function canUserPerformThisAction($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        /* This used to open with `if (!is_admin())`, which skipped every check
           below for anybody who had reached a wp-admin screen. Being on an
           admin screen is not a permission: a user holding only
           jsst_support_ticket_tickets — the help-desk agent role on a site
           without the Agents add-on — reaches the admin Tickets list, and so
           could delete other people's tickets. The question is what the user
           may do, not which screen they are on. (Roadmap 4.0-SEC-04) */
        if (JSSTroles::canManageHelpDesk()) {
            return true;
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
            if ($jsst_allowed == true) {
                return true;
            }
        }
        // Everybody else acts on their own tickets only.
        $jsst_ticketUid = $this->getTicketUidById($jsst_id);
        $jsst_currentuserid = JSSTincluder::getObjectClass('user')->uid();
        if ($jsst_currentuserid != $jsst_ticketUid){
            return false;
        }
        return true;
    }

    function getTicketUidById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
        $jsst_uid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_uid;
    }

    function getTicketSubjectById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT subject FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
        $jsst_subject = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_subject;
    }

    function getTrackingIdById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT ticketid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
        $jsst_ticketid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_ticketid;
    }

    function getTicketEmailById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT email FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
        $jsst_ticketemail = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_ticketemail;
    }

    function getStaffIdById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT staffid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
        $jsst_staffid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_staffid;
    }

    function setStatus($jsst_status, $jsst_ticketid) {
        // 0 -> New Ticket
        // 1 -> Waiting admin/staff reply
        // 2 -> in progress
        // 3 -> waiting for customer reply
        // 4 -> close ticket
        if (!is_numeric($jsst_status))
            return false;
        if (!is_numeric($jsst_ticketid))
            return false;
        $jsst_row = JSSTincluder::getJSTable('tickets');
        if (!$jsst_row->update(array('id' => $jsst_ticketid, 'status' => $jsst_status))) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getLastReply($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT reply.message FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS reply WHERE reply.ticketid = %d ORDER BY reply.created DESC LIMIT 1";
        $jsst_message =jssupportticket::$_db->query(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        return $jsst_message;
    }

    function updateLastReply($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_date = date_i18n('Y-m-d H:i:s');
        $jsst_isanswered = " , isanswered = 0 ";
        if ( is_admin() || ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) ) {
            $jsst_isanswered = " , isanswered = 1 ";
        }
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET lastreply = %s " . $jsst_isanswered . " WHERE id = %d";
        jssupportticket::$_db->query(jssupportticket::$_db->prepare($jsst_query, $jsst_date, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function closeTicket($jsst_id ,$jsst_cron_flag = 0) { // second parameter is for crown call(when crown job is executed to hanled close ticket configuration)
        if (!is_numeric($jsst_id))
            return false;
        if($jsst_cron_flag == 0){
            //Check if its allowed to close ticket
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Close Ticket');
                if ($jsst_allowed != true) {
                    JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                    return;
                }
            } else {
                if(!JSSTroles::canChangeTicketState()){
                    // in case of user check for ticket owner
                    $jsst_current_uid = JSSTincluder::getObjectClass('user')->uid();
                    $jsst_ticket_uid = $this->getUIdById($jsst_id);
                    if ($jsst_current_uid != $jsst_ticket_uid) {
                        JSSTmessage::setMessage(esc_html(__('You are not allowed','js-support-ticket')), 'error');
                        return;
                    }
                }
            }
        }
        if (!$this->checkActionStatusSame($jsst_id, array('action' => 'closeticket'))) {
            JSSTmessage::setMessage(esc_html(__('Ticket already closed', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_sendEmail = true;
        $jsst_date = date_i18n('Y-m-d H:i:s');
        if($jsst_cron_flag == 0){
            $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user id
            $jsst_closedby = isset($jsst_current_user->display_name) ? $jsst_current_user->id : -1;
        }else{
            $jsst_closedby = 0;
        }

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->update(array('id' => $jsst_id, 'status' => 5, 'closed' => $jsst_date, 'closedby' => $jsst_closedby, 'isoverdue' => 0))) {

            JSSTmessage::setMessage(esc_html(__('Ticket has been closed', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Ticket has not been closed', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
        }

        /* for activity log */
        $jsst_ticketid = $jsst_id; // get the ticket id
        if($jsst_cron_flag == 0){
            $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $jsst_currentUserName = isset($jsst_current_user->display_name) ? $jsst_current_user->display_name : esc_html(__('Guest', 'js-support-ticket'));
        }else{
            $jsst_currentUserName = esc_html(__('System', 'js-support-ticket'));
        }
        $jsst_eventtype = esc_html(__('Close Ticket', 'js-support-ticket'));
        $jsst_message = esc_html(__('Ticket is closed by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        if(JSSTmergedaddon::featureEnabled('tickethistory')){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
        }

        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 2, $jsst_ticketid); // Mailfor, Close Ticket, Ticketid
            $jsst_ticketobject = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d", $jsst_ticketid));
            do_action('jsst-ticketclose', $jsst_ticketobject);
        }
        // on ticket close make remove credentails data and show messsage on retrive.
        if(in_array('privatecredentials',jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('privatecredentials')->deleteCredentialsOnCloseTicket($jsst_ticketid);
        }
        return;
    }

    function getTicketListOrdering($jsst_sort) {
        switch ($jsst_sort) {
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
                $jsst_sortbyconfig = jssupportticket::$_config['tickets_sorting'];
                if($jsst_sortbyconfig == 1){
                    $jsst_sortbyconfig = "ASC";
                }else{
                    $jsst_sortbyconfig = "DESC";
                }
                jssupportticket::$_ordering = "ticket.id $jsst_sortbyconfig";
            break;
        }
        return;
    }

    function getSortArg($jsst_type, $jsst_sort) {
        $jsst_mat = array();
        if (preg_match("/(\w+)(asc|desc)/i", $jsst_sort, $jsst_mat)) {
            if ($jsst_type == $jsst_mat[1]) {
                return ( $jsst_mat[2] == "asc" ) ? "{$jsst_type}desc" : "{$jsst_type}asc";
            } else {
                return $jsst_type . $jsst_mat[2];
            }
        }
        $jsst_sortlink = "id";
        // default sorting
        $jsst_sortbyconfig = jssupportticket::$_config['tickets_sorting'];
        if($jsst_sortbyconfig == 1){
            $jsst_sortbyconfig = "asc";
        }else{
            $jsst_sortbyconfig = "desc";
        }
        $jsst_sortlink = $jsst_sortlink.$jsst_sortbyconfig;

        return $jsst_sortlink;
    }

    function getTicketListSorting($jsst_sort) {
        jssupportticket::$_sortlinks['subject'] = $this->getSortArg("subject", $jsst_sort);
        jssupportticket::$_sortlinks['priority'] = $this->getSortArg("priority", $jsst_sort);
        jssupportticket::$_sortlinks['ticketid'] = $this->getSortArg("ticketid", $jsst_sort);
        jssupportticket::$_sortlinks['isanswered'] = $this->getSortArg("isanswered", $jsst_sort);
        jssupportticket::$_sortlinks['status'] = $this->getSortArg("status", $jsst_sort);
        jssupportticket::$_sortlinks['created'] = $this->getSortArg("created", $jsst_sort);
        return;
    }

    /**
     * The ticket fields the activity timeline reports diffs for, resolved to the
     * names an agent recognises rather than raw ids. (Roadmap 4.0-CORE-01)
     */
    function getTimelineFieldValues($jsst_id) {
        if (!is_numeric($jsst_id)) {
            return array();
        }
        // Only core tables are joined here. The staff table belongs to the agent
        // add-on and does not exist on a free install, and assignment changes are
        // already logged by the transfer and reassign paths.
        $jsst_query = "SELECT ticket.subject, ticket.duedate,
                    status.status AS statusname, priority.priority AS priorityname,
                    department.departmentname AS departmentname
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                WHERE ticket.id = %d";
        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (empty($jsst_row)) {
            return array();
        }
        return array(
            'subject'    => (string) $jsst_row->subject,
            'status'     => (string) $jsst_row->statusname,
            'priority'   => (string) $jsst_row->priorityname,
            'department' => (string) $jsst_row->departmentname,
            'duedate'    => (string) $jsst_row->duedate,
        );
    }

    /**
     * Labels for the fields above.
     */
    function getTimelineFieldLabels() {
        return array(
            'subject'    => esc_html(__('Subject', 'js-support-ticket')),
            'status'     => esc_html(__('Status', 'js-support-ticket')),
            'priority'   => esc_html(__('Priority', 'js-support-ticket')),
            'department' => esc_html(__('Department', 'js-support-ticket')),
            'duedate'    => esc_html(__('Due date', 'js-support-ticket')),
        );
    }

    private function getTicketHistory($jsst_id) {
        if(!JSSTmergedaddon::featureEnabled('tickethistory')){
            jssupportticket::$jsst_data[5] = array();
            jssupportticket::$jsst_data['history_filters'] = array('eventtype' => array(), 'source' => array());
            return;
        }
        if(!is_numeric($jsst_id)) return false;
        // Core owns the timeline in 4.0. The model resolves to the stand-alone
        // add-on while that add-on is active, which does not expose the actor,
        // source or field-diff columns — so read those only when core is
        // serving the feature. (Roadmap 4.0-CORE-01, 4.0-CORE-19)
        if(JSSTmergedaddon::coreOwns('tickethistory')){
            $jsst_filters = array(
                'eventtype' => JSSTrequest::getVar('historyeventtype'),
                'source'    => JSSTrequest::getVar('historysource'),
            );
            $jsst_model = JSSTincluder::getJSModel('tickethistory');
            jssupportticket::$jsst_data[5] = $jsst_model->getTicketTimeline($jsst_id, $jsst_filters);
            jssupportticket::$jsst_data['history_filters'] = $jsst_model->getTimelineFilterValues($jsst_id);
            return;
        }
        $jsst_query = "SELECT al.id,al.message,al.datetime,al.uid
        from `" . jssupportticket::$_db->prefix . "js_ticket_activity_log`  AS al
        join `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS tic on al.referenceid=tic.id
        where al.referenceid=%d AND al.eventfor=1 ORDER BY al.datetime DESC ";
        jssupportticket::$jsst_data[5] = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        jssupportticket::$jsst_data['history_filters'] = array('eventtype' => array(), 'source' => array());
    }

    function tickChangeStatus($jsst_data) {
        $jsst_ticketid = $jsst_data['ticketid'];
        if (!is_numeric($jsst_data['status']))
            return false;
        if (!is_numeric($jsst_ticketid))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Change Ticket Status');
            if ($jsst_allow != true) {
                JSSTmessage::setMessage(esc_html(__('Your are not allowed', 'js-support-ticket')), 'updated');
                return;
            }
        } elseif (!JSSTroles::canChangeTicketState()) {
            $jsst_owns_ticket = (!JSSTincluder::getObjectClass('user')->isguest()) ? $this->validateTicketDetailForUser($jsst_ticketid) : $this->validateTicketDetailForVisitor($jsst_ticketid);
            if (!$jsst_owns_ticket) {
                JSSTmessage::setMessage(esc_html(__('Your are not allowed', 'js-support-ticket')), 'error');
                return;
            }
        }
        $jsst_date = date_i18n('Y-m-d H:i:s');

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->update(array('id' => $jsst_ticketid, 'status' => $jsst_data['status'], 'updated' => $jsst_date))) {
            JSSTmessage::setMessage(esc_html(__('The status has been changed', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The status has not been changed', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
        }

        /* for activity log */
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = $jsst_current_user->display_name;
        $jsst_eventtype = esc_html(__('Ticket status change', 'js-support-ticket'));
        $jsst_message = esc_html(__('The status is changed by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        if(JSSTmergedaddon::featureEnabled('tickethistory')){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
        }
        return;
    }

    function tickDepartmentTransfer($jsst_data) {
        $jsst_ticketid = $jsst_data['ticketid'];
        if (!is_numeric($jsst_ticketid) || !is_numeric($jsst_data['departmentid']))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Department Transfer');
            if ($jsst_allow != true) {
                JSSTmessage::setMessage(esc_html(__('Your are not allowed', 'js-support-ticket')), 'updated');
                return;
            }
        } elseif (!JSSTroles::canChangeTicketState()) {
            JSSTmessage::setMessage(esc_html(__('Your are not allowed', 'js-support-ticket')), 'error');
            return;
        }
        $jsst_sendEmail = true;
        $jsst_date = date_i18n('Y-m-d H:i:s');

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->update(array('id' => $jsst_ticketid, 'departmentid' => $jsst_data['departmentid'], 'updated' => $jsst_date))) {
            JSSTmessage::setMessage(esc_html(__('The department has been transferred', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The department has not been transferred', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
        }

        /* for activity log */
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = $jsst_current_user->display_name;
        $jsst_eventtype = esc_html(__('Ticket department transfer', 'js-support-ticket'));
        $jsst_message = esc_html(__('The department is transferred by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        // Records the reason with the event when the agent gave one.
        // (Roadmap 4.0-CORE-05)
        JSSTticketaction::audit($jsst_ticketid, $jsst_eventtype, $jsst_message, $jsst_messagetype, JSSTticketaction::reason($jsst_data));

        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 12, $jsst_ticketid); // Mailfor, Department Ticket, Ticketid
            $jsst_ticketobject = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d", $jsst_ticketid));
            do_action('jsst-ticketclose', $jsst_ticketobject);
        }

        /* to store internal notes FOR department transfer  */
        if (isset($jsst_data['departmenttranfernote']) && $jsst_data['departmenttranfernote'] != '') {
            JSSTincluder::getJSModel('note')->storeTicketInternalNote($jsst_data, $jsst_data['departmenttranfernote']);
        }
        return;
    }

    function assignTicketToStaff($jsst_data) {
        $jsst_ticketid = $jsst_data['ticketid'];
        if (!is_numeric($jsst_ticketid) || !is_numeric($jsst_data['staffid']))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Assign Ticket To Agent');
            if ($jsst_allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!JSSTroles::canChangeTicketState()) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return;
        }
        $jsst_sendEmail = true;
        $jsst_date = date_i18n('Y-m-d H:i:s');

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->update(array('id' => $jsst_ticketid, 'staffid' => $jsst_data['staffid'], 'updated' => $jsst_date))) {
            JSSTmessage::setMessage(esc_html(__('Assigned to agent', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Not assigned to agent', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
        }

        /* for activity log */
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = isset($jsst_current_user->display_name) ? $jsst_current_user->display_name : esc_html(__('Guest', 'js-support-ticket'));
        $jsst_eventtype = esc_html(__('Assign Ticket To Agent', 'js-support-ticket'));
        $jsst_message = esc_html(__('Ticket is assigned to agent by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        if(JSSTmergedaddon::featureEnabled('tickethistory')){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
        }

        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 13, $jsst_ticketid); // Mailfor, Assign Ticket, Ticketid
            $jsst_ticketobject = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d", $jsst_ticketid));
            do_action('jsst-ticketclose', $jsst_ticketobject);
        }

        /* to store internal notes FOR department transfer  */
        if(JSSTmergedaddon::featureEnabled('note')){
            if (isset($jsst_data['assignnote']) && $jsst_data['assignnote'] != '') {
                JSSTincluder::getJSModel('note')->storeTicketInternalNote($jsst_data, $jsst_data['assignnote']);
            }
        }
        return;
    }

    function changeTicketPriority($jsst_id, $jsst_priorityid) {
        // Request authenticity is checked by the callers — changepriority() and
        // actionticket() both verify the same action-ticket-<id> nonce before
        // calling, and the bulk action verifies its own single nonce for the
        // whole selection. Re-checking a per-ticket nonce here made this method
        // unusable from any batch. (Roadmap 4.0-CORE-05)
        if (!is_numeric($jsst_id))
            return false;
        if (!is_numeric($jsst_priorityid))
            return false;
        if (!$this->checkActionStatusSame($jsst_id, array('action' => 'priority', 'id' => $jsst_priorityid))) {
            JSSTmessage::setMessage(esc_html(__('Ticket already have same priority', 'js-support-ticket')), 'error');
            return;
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Change Ticket Priority');
            if ($jsst_allow == 0) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!JSSTroles::canChangeTicketState()) {
            $jsst_owns_ticket = (!JSSTincluder::getObjectClass('user')->isguest()) ? $this->validateTicketDetailForUser($jsst_id) : $this->validateTicketDetailForVisitor($jsst_id);
            if (!$jsst_owns_ticket) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        }
        $jsst_sendEmail = true;
        $jsst_date = date_i18n('Y-m-d H:i:s');

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->update(array('id' => $jsst_id, 'priorityid' => $jsst_priorityid, 'updated' => $jsst_date))) {
            JSSTmessage::setMessage(esc_html(__('Priority has been changed', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('Priority has not been changed', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
        }

        /* for activity log */
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = $jsst_current_user->display_name;
        $jsst_eventtype = esc_html(__('Change Priority', 'js-support-ticket'));
        $jsst_message = esc_html(__('Ticket Priority Is Changed By', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        if(JSSTmergedaddon::featureEnabled('tickethistory')){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_id, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
        }
        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 11, $jsst_id, 'js_ticket_tickets'); // Mailfor, Ban email, Ticketid
        }
        return;
    }

    function banEmail($jsst_data) {
        if(!JSSTmergedaddon::featureEnabled('banemail') || !is_numeric($jsst_data['ticketid'])) {
            return false;
        }
        $jsst_ticketid = $jsst_data['ticketid'];
        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
        if(in_array('agent',jssupportticket::$_active_addons)){
            $jsst_staffid = JSSTincluder::getJSModel('agent')->getstaffid($jsst_uid);
        }else{
            $jsst_staffid = '';
        }
        if (!is_numeric($jsst_ticketid))
            return false;

        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ban Email And Close Ticket');
            if ($jsst_allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!current_user_can('manage_options')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return;
        }

        $jsst_email = self::getTicketEmailById($jsst_ticketid);
        if (!$this->checkActionStatusSame($jsst_ticketid, array('action' => 'banemail', 'email' => $jsst_email))) {
            JSSTmessage::setMessage(esc_html(__('Email already banned', 'js-support-ticket')), 'error');
            return;
        }

        $jsst_sendEmail = true;
        $jsst_data = array(
            'email' => $jsst_email,
            'submitter' => $jsst_staffid,
            'uid' => $jsst_uid,
            'created' => date_i18n('Y-m-d H:i:s')
        );

        $jsst_row = JSSTincluder::getJSTable('banemail');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }
        if ($jsst_error == 0) {

            JSSTmessage::setMessage(esc_html(__('The email has been banned', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The email has not been banned', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
        }

        /* for activity log */
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = $jsst_current_user->display_name;
        $jsst_eventtype = esc_html(__('Ban Email', 'js-support-ticket'));
        $jsst_message = esc_html(__('Email is banned by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        if(JSSTmergedaddon::featureEnabled('tickethistory')){
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
        }

        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(2, 1, $jsst_ticketid, 'js_ticket_tickets'); // Mailfor, Ban email, Ticketid
            $jsst_ticketobject = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d", $jsst_ticketid));
            do_action('jsst-ticketclose', $jsst_ticketobject);
        }
        return;
    }

    function sendFeedbackMailByTicketid($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid))
            return false;

        $jsst_date = date_i18n('Y-m-d H:i:s');

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->update(array('id' => $jsst_ticketid, 'feedbackemail' => 1))) {
            JSSTincluder::getJSModel('email')->sendMail(1, 15, $jsst_ticketid); // Mailfor, feedback for Ticket, Ticketid
        }
        return;
    }

    function banEmailAndCloseTicket($jsst_data) {
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ban Email And Close Ticket');
            if ($jsst_allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!current_user_can('manage_options')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return;
        }
        self::banEmail($jsst_data);
        self::closeTicket($jsst_data['ticketid']);
        return;
    }

    /* check can a ticket be opened with in the given days */

    function checkCanReopenTicket($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid))
            return false;
        $jsst_lastreply = JSSTincluder::getJSModel('reply')->getLastReply($jsst_ticketid);
        if (!$jsst_lastreply)
            $jsst_lastreply = date_i18n('Y-m-d H:i:s');
        $jsst_days = jssupportticket::$_config['reopen_ticket_within_days'];
        $jsst_date = gmdate("Y-m-d H:i:s", jssupportticketphplib::JSST_strtotime(gmdate("Y-m-d H:i:s", jssupportticketphplib::JSST_strtotime($jsst_lastreply)) . " +" . esc_html($jsst_days) . " day"));
        if ($jsst_date < date_i18n('Y-m-d H:i:s'))
            return false;
        else
            return true;
    }

    function reopenTicket($jsst_data) {
        $jsst_ticketid = $jsst_data['ticketid'];
        $jsst_lastreply = isset($jsst_data['lastreplydate']) ? $jsst_data['lastreplydate'] : '';
        if (!is_numeric($jsst_ticketid))
            return false;
        //check the permission to reopen ticket
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Reopen Ticket');
            if ($jsst_allowed != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } else {
            if(!JSSTroles::canChangeTicketState()){
                // in case of user check for ticket owner
                $jsst_current_uid = JSSTincluder::getObjectClass('user')->uid();
                $jsst_ticket_uid = JSSTincluder::getJSModel('ticket')->getUIdById($jsst_ticketid);
                if ($jsst_current_uid != $jsst_ticket_uid) {
                    /* Refused silently until now: the page simply came back with
                       the ticket still closed and nothing said why, so it read
                       as a broken button rather than a refusal. closeTicket()
                       has said this all along; the two now behave alike. */
                    JSSTmessage::setMessage(esc_html(__('You are not allowed','js-support-ticket')), 'error');
                    return;
                }
            }
        }
        /* check can a ticket be opened with in the given days */
        if ($this->checkCanReopenTicket($jsst_ticketid)) {
            $jsst_sendEmail = true;
            $jsst_date = date_i18n('Y-m-d H:i:s');

            $jsst_row = JSSTincluder::getJSTable('tickets');
            if ($jsst_row->update(array('id' => $jsst_ticketid, 'status' =>1, 'updated' => $jsst_date))) {
                JSSTmessage::setMessage(esc_html(__('The ticket has been reopened', 'js-support-ticket')), 'updated');
                $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('The ticket has not been reopened', 'js-support-ticket')), 'error');
                $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
                $jsst_sendEmail = false;
            }

            /* for activity log */
            $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $jsst_currentUserName = isset($jsst_current_user->display_name) ? $jsst_current_user->display_name : esc_html(__('Guest', 'js-support-ticket'));
            $jsst_eventtype = esc_html(__('Reopen Ticket', 'js-support-ticket'));
            $jsst_message = esc_html(__('The ticket is reopened by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
            if(JSSTmergedaddon::featureEnabled('tickethistory')){
                JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
            }
            /*
              // Send Emails
              if ($jsst_sendEmail == true) {
              JSSTincluder::getJSModel('email')->sendMail(1, 2, $jsst_ticketid); // Mailfor, Close Ticket, Ticketid
              $jsst_ticketobject = jssupportticket::$_db->get_row("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = " . (int)($jsst_ticketid));
              do_action('jsst-ticketclose', $jsst_ticketobject);
              }
             */
        } else {
            JSSTmessage::setMessage(esc_html(__('The ticket reopens time limit end', 'js-support-ticket')), 'error');
        }


        return;
    }

    private function canUnbanEmail($jsst_email) {
        // Read straight from the table; see getTicketsForAdmin(). Guarding here
        // also covers the DELETE in unbanEmail(), which only runs once this has
        // returned true.
        JSSTmergedaddon::ensureSchema('banemail');
        $jsst_query = " SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = %s ";
        $jsst_result = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_email));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        if ($jsst_result > 0)
            return true;
        else
            return false;
    }

    function unbanEmail($jsst_data) {
        $jsst_ticketid = $jsst_data['ticketid'];
        if (!is_numeric($jsst_ticketid))
            return false;
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Unban Email');
            if ($jsst_allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!current_user_can('manage_options')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return;
        }
        $jsst_email = self::getTicketEmailById($jsst_ticketid);
        if ($this->canUnbanEmail($jsst_email)) {
            $jsst_sendEmail = true;
            $jsst_date = date_i18n('Y-m-d H:i:s');
            $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE email = %s ";
            jssupportticket::$_db->query(jssupportticket::$_db->prepare($jsst_query, $jsst_email . ' '));
            if (jssupportticket::$_db->last_error == null) {
                JSSTmessage::setMessage(esc_html(__('Email has been unbanned', 'js-support-ticket')), 'updated');
                $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
                JSSTmessage::setMessage(esc_html(__('Email has not been unbanned', 'js-support-ticket')), 'error');
                $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
                $jsst_sendEmail = false;
            }

            /* for activity log */
            $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
            $jsst_currentUserName = $jsst_current_user->display_name;
            $jsst_eventtype = esc_html(__('Unbanned Email', 'js-support-ticket'));
            $jsst_message = esc_html(__('Email is unbanned by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
            if(JSSTmergedaddon::featureEnabled('tickethistory')){
                JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
            }

            // Send Emails
            if ($jsst_sendEmail == true) {
                JSSTincluder::getJSModel('email')->sendMail(2, 2, $jsst_ticketid, 'js_ticket_tickets'); // Mailfor, Unban Ticket, Ticketid
                $jsst_ticketobject = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %s", $jsst_ticketid));
                do_action('jsst-ticketclose', $jsst_ticketobject);
            }
        } else {
            JSSTmessage::setMessage(esc_html(__('Email cannot be unbanned', 'js-support-ticket')), 'error');
        }

        return;
    }

    function markTicketInProgress($jsst_data) {
        $jsst_ticketid = $jsst_data['ticketid'];
        if (!is_numeric($jsst_ticketid))
            return false;
        if (!$this->checkActionStatusSame($jsst_ticketid, array('action' => 'markinprogress'))) {
            JSSTmessage::setMessage(esc_html(__('Ticket already marked in progress', 'js-support-ticket')), 'error');
            return;
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allow = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Mark In Progress');
            if ($jsst_allow != true) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                return;
            }
        } elseif (!JSSTroles::canChangeTicketState()) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return;
        }
        $jsst_date = date_i18n('Y-m-d H:i:s');
        $jsst_sendEmail = true;

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->update(array('id' => $jsst_ticketid, 'status' => 3, 'updated' => $jsst_date))) {
            JSSTmessage::setMessage(esc_html(__('The ticket has been marked as in progress', 'js-support-ticket')), 'updated');
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(esc_html(__('The ticket has not been marked as in progress', 'js-support-ticket')), 'error');
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
        }

        /* for activity log */
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = $jsst_current_user->display_name;
        $jsst_eventtype = esc_html(__('In Progress Ticket', 'js-support-ticket'));
        $jsst_message = esc_html(__('The ticket is marked as in progress by', 'js-support-ticket')) . " ( " . esc_html($jsst_currentUserName) . " ) ";
        // Records the reason with the event when the agent gave one.
        // (Roadmap 4.0-CORE-05)
        JSSTticketaction::audit($jsst_ticketid, $jsst_eventtype, $jsst_message, $jsst_messagetype, JSSTticketaction::reason($jsst_data));

        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, 9, $jsst_ticketid, 'js_ticket_tickets'); // Mailfor, Unban Ticket, Ticketid
            $jsst_ticketobject = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare("SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d", $jsst_ticketid));
            do_action('jsst-ticketclose', $jsst_ticketobject);
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
            $jsst_intrval_string = " date(DATE_ADD(closed,INTERVAL " . (int)jssupportticket::$_config['feedback_email_delay']." DAY)) < '".gmdate("Y-m-d")."'";
        }else{
            $jsst_intrval_string = " DATE_ADD(closed,INTERVAL " .(int) jssupportticket::$_config['feedback_email_delay'] . " HOUR) < '".date_i18n("Y-m-d H:i:s")."'";
        }
        // select closed ticket
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE ".$jsst_intrval_string." AND status = 5 AND (feedbackemail != 1  OR feedbackemail IS NULL) AND closed IS NOT NULL";
        $jsst_ticketids = jssupportticket::$_db->get_results($jsst_query);
        if(!empty($jsst_ticketids)){
            foreach ($jsst_ticketids as $jsst_key) {
                if(is_numeric($jsst_key->id)){
                    JSSTincluder::getJSModel('ticket')->sendFeedbackMailByTicketid($jsst_key->id);
                }
            }
        }
        return;
    }

    function removeFileCustom($jsst_id,$jsst_key){
        if(!is_numeric($jsst_id)) return false;
        $jsst_filename = jssupportticketphplib::JSST_str_replace(' ', '_', $jsst_key);
        $jsst_filename = jssupportticketphplib::JSST_clean_file_path($jsst_filename);
        $jsst_maindir = wp_upload_dir();
        $jsst_basedir = $jsst_maindir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        $jsst_path = $jsst_basedir . '/' . $jsst_datadirectory. '/attachmentdata/ticket';

        $jsst_query = "SELECT attachmentdir FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE id = %d";
        $jsst_foldername = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        $jsst_userpath = $jsst_path . '/' . $jsst_foldername.'/'.$jsst_filename;
        if ( file_exists( $jsst_userpath ) ) {
            wp_delete_file($jsst_userpath);
        }
        return ;
    }

    function getTicketidForVisitor($jsst_token) {

        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
        $jsst_encoder = new JSSTEncoder();
        $jsst_decryptedtext = $jsst_encoder->decrypt($jsst_token);
        $jsst_array = json_decode($jsst_decryptedtext, true);
        $jsst_emailaddress = $jsst_array['emailaddress'];
        $jsst_trackingid = $jsst_array['trackingid'];
        if (isset($jsst_array['sitelink']) && $jsst_array['sitelink'] != '') {
            $jsst_siteLink = $jsst_array['sitelink'];
            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
            $jsst_encoder = new JSSTEncoder();
            $jsst_savedSiteLink = get_option('jsst_encripted_site_link');
            $jsst_decryptedSiteLink = $jsst_encoder->decrypt($jsst_siteLink);
            $jsst_decryptedSavedSiteLink = $jsst_encoder->decrypt($jsst_savedSiteLink);
            if ($jsst_decryptedSiteLink != $jsst_decryptedSavedSiteLink) {
                return false;
            }
        }
        if($jsst_emailaddress == '' && $jsst_trackingid == ''){
            return false;
        }
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = %s AND ticketid = %s";
        $jsst_ticketid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_emailaddress, $jsst_trackingid));
        return $jsst_ticketid;
    }

    function getTicketidForVisitorUsingToken($jsst_token) {
        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
        $jsst_encoder = new JSSTEncoder();
        $jsst_decryptedtext = $jsst_encoder->decrypt($jsst_token);
        $jsst_array = json_decode($jsst_decryptedtext, true);
        $jsst_token = $jsst_array['token'];
        if (isset($jsst_array['sitelink']) && $jsst_array['sitelink'] != '') {
            $jsst_siteLink = $jsst_array['sitelink'];
            $jsst_savedSiteLink = get_option('jsst_encripted_site_link');
            $jsst_decryptedSiteLink = $jsst_encoder->decrypt($jsst_siteLink);
            $jsst_decryptedSavedSiteLink = $jsst_encoder->decrypt($jsst_savedSiteLink);
            if ($jsst_decryptedSiteLink != $jsst_decryptedSavedSiteLink) {
                return false;
            }
        }
        if($jsst_token == '' ){
            return false;
        }
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE token = %s";
        $jsst_ticketid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_token));
        return $jsst_ticketid;
    }

    function createTokenByEmailAndTrackingId($jsst_emailaddress, $jsst_trackingid) {
        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
        $jsst_encoder = new JSSTEncoder();
        $jsst_token = $jsst_encoder->encrypt(wp_json_encode(array('emailaddress' => $jsst_emailaddress, 'trackingid' => $jsst_trackingid)));
        return $jsst_token;
    }

    function getTokenByEmailAndTrackingId($jsst_emailaddress, $jsst_trackingid) {
        $jsst_query = "SELECT token FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = %s AND ticketid = %s";
        $jsst_token = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_emailaddress, $jsst_trackingid));
        return $jsst_token;
    }

    function validateTicketDetailForStaff($jsst_ticketid) {
        if(!in_array('agent', jssupportticket::$_active_addons)){
            return false;
        }
        if (!is_numeric($jsst_ticketid))
            return false;
        $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('All Tickets');
        if($jsst_allowed == true){
            return true;
        }
        // check in assign department
        $jsst_c_uid = JSSTincluder::getObjectClass('user')->uid();
        $jsst_query = "SELECT ticket.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
            JOIN `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept ON ticket.departmentid = dept.departmentid
            JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON dept.staffid = staff.id AND staff.uid = %d
            WHERE ticket.id = %d";
        $jsst_id = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_c_uid, $jsst_ticketid));

        if ($jsst_id) {
            return true;
        } else {
            // check in assign ticket
            $jsst_query = "SELECT ticket.id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON ticket.staffid = staff.id AND staff.uid = %d";
            $jsst_query .= " WHERE ticket.id = %d";
            $jsst_id = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_c_uid, $jsst_ticketid));
            if ($jsst_id)
                return true;
            else
                return false;
        }
    }

    function totalTicket() {
        $jsst_query = "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets`";
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_total;
    }

    function validateTicketDetailForUser($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
        $jsst_uid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));

        if ($jsst_uid == JSSTincluder::getObjectClass('user')->uid()) {
            return true;
        }elseif($jsst_uid != '') {
            jssupportticket::$jsst_data['error_message'] = 2;// to prompt user that he can not view this ticket.
            return;
        }else {
            return false;
        }
    }

    function validateTicketDetailForVisitor($jsst_id) {
        if(!is_numeric($jsst_id)) return false;
        if (!isset($_COOKIE['js-support-ticket-token-tkstatus'])) {
            return false;
        }
        $jsst_token = jssupportticket::JSST_sanitizeData($_COOKIE['js-support-ticket-token-tkstatus']); // JSST_sanitizeData() function uses wordpress santize functions
        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
        $jsst_encoder = new JSSTEncoder();
        $jsst_decryptedtext = $jsst_encoder->decrypt($jsst_token);
        $jsst_array = json_decode($jsst_decryptedtext, true);
        if (!empty($jsst_array['token'])) {
            $jsst_token = $jsst_array['token'];
            $jsst_query = jssupportticket::$_db->prepare("SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE token = %s", $jsst_token);
        } else {
            $jsst_emailaddress = $jsst_array['emailaddress'];
            $jsst_trackingid = $jsst_array['trackingid'];
            $jsst_query = jssupportticket::$_db->prepare("SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE email = %s AND ticketid = %s", $jsst_emailaddress, $jsst_trackingid);
        }
        $jsst_ticketid = jssupportticket::$_db->get_var($jsst_query);

        if ($jsst_ticketid == $jsst_id) {
            return true;
        } else {
            $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
            $jsst_ticketid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
            if($jsst_ticketid > 0){
                jssupportticket::$jsst_data['error_message'] = 1;// to prompt user to login
            }
            jssupportticket::$jsst_data['error_message'] = 1;
            return false;
        }
    }

    function checkActionStatusSame($jsst_id, $jsst_array) {
        switch ($jsst_array['action']) {
            case 'priority':
                if(!is_numeric($jsst_id)) return false;
                if(!is_numeric($jsst_array['id'])) return false;
                $jsst_result = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d AND priorityid = %d', $jsst_id, $jsst_array['id']));
                break;
            case 'markoverdue':
                if(!is_numeric($jsst_id)) return false;
                $jsst_result = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d AND isoverdue = 1', $jsst_id));
                break;
            case 'markinprogress':
                if(!is_numeric($jsst_id)) return false;
                $jsst_result = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d AND status = 3', $jsst_id));
                break;
            case 'closeticket':
                if(!is_numeric($jsst_id)) return false;
                $jsst_result = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d AND status = 5', $jsst_id));
                break;
            case 'banemail':
                // Read straight from the table; see getTicketsForAdmin().
                JSSTmergedaddon::ensureSchema('banemail');
                $jsst_result = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_email_banlist` WHERE email = %s', $jsst_array['email']));
                break;
        }
        if ($jsst_result > 0) {
            return false;
        } else {
            return true;
        }
    }

    function ticketAssignToMe($jsst_ticketid, $jsst_staffid) {
        if (!is_numeric($jsst_ticketid))
            return false;
        if (!is_numeric($jsst_staffid))
            return false;
        $jsst_row = JSSTincluder::getJSTable('tickets');
        $jsst_row->update(array('id' => $jsst_ticketid, 'staffid' => $jsst_staffid));

        return true;
    }

    function isTicketAssigned($jsst_ticketid){
        if (! in_array('agent',jssupportticket::$_active_addons)) {
            return false;
        }
        if (!is_numeric($jsst_ticketid))
            return false;
        $jsst_query = "SELECT staffid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id=%d";
        $jsst_staffid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_ticketid));
        if($jsst_staffid > 0)
            return true;
        return false;
    }

    function getMyTicketInfo_Widget($jsst_maxrecord){
        if(!is_numeric($jsst_maxrecord)) return false;
        if(!JSSTincluder::getObjectClass('user')->isguest()){
            $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
                // Data
            $jsst_query = "SELECT DISTINCT ticket.id,ticket.subject,ticket.status,ticket.name,priority.priority AS priority,priority.prioritycolour AS prioritycolour
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        WHERE ticket.uid = %d AND (ticket.status = 1 OR ticket.status = 2) ORDER BY ticket.status DESC LIMIT %d";
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_uid, $jsst_maxrecord);

            if(in_array('agent',jssupportticket::$_active_addons)){
                $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId($jsst_uid);
                if($jsst_staffid){
                    // Data
                    $jsst_query = "SELECT DISTINCT ticket.id,ticket.subject,ticket.status,ticket.name,priority.priority AS priority,priority.prioritycolour AS prioritycolour
                                FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_staff` AS staff ON staff.uid = ticket.uid
                                WHERE (ticket.staffid = %d OR ticket.departmentid IN (SELECT dept.departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept WHERE dept.staffid = %d)) AND (ticket.status = 1 OR ticket.status = 2) ORDER BY ticket.status DESC LIMIT %d";
                    $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_staffid, $jsst_staffid, $jsst_maxrecord);
                }
            }
            if(isset($jsst_query)){
                jssupportticket::$jsst_data['widget_myticket'] = jssupportticket::$_db->get_results($jsst_query);
                if (jssupportticket::$_db->last_error != null) {
                    JSSTincluder::getJSModel('systemerror')->addSystemError();
                }
            }else{
                jssupportticket::$jsst_data['widget_myticket'] = false;
            }
        }else{
            jssupportticket::$jsst_data['widget_myticket'] = false;
        }
        return;
    }

    function getLatestTicketForDashboard(){
        $jsst_query = "SELECT ticket.id,ticket.subject,ticket.name,priority.priority,priority.prioritycolour
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON priority.id = ticket.priorityid
                    ORDER BY ticket.status ASC, ticket.created DESC LIMIT 0, 5";
        $jsst_tickets = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_tickets;
    }
    function getAttachmentByTicketId($jsst_id){
        if(!is_numeric($jsst_id)) return false;
        //if not admin and agent
        // check for ticket owner only in case of user
        if(!current_user_can('manage_options') && !(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())){
            // in case of user check for ticket owner
            if (!JSSTincluder::getObjectClass('user')->isguest()) {
                $jsst_current_uid = JSSTincluder::getObjectClass('user')->uid();
                $jsst_ticket_uid = JSSTincluder::getJSModel('ticket')->getUIdById($jsst_id);
                if ($jsst_current_uid != $jsst_ticket_uid) {
                    return;
                }
            } else {
                if (!$this->validateTicketDetailForVisitor($jsst_id)) {
                    return;
                }
            }
            
        }
        $jsst_query = "SELECT attachment.filename , ticket.attachmentdir
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_attachments` AS attachment
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket ON ticket.id = attachment.ticketid AND ticket.id =%d AND attachment.replyattachmentid = 0 ";
        $jsst_attachments = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        return $jsst_attachments;
    }

    function getTotalStatsForDashboard(){
        $jsst_curdate = date_i18n('Y-m-d');
        $jsst_fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));

        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE status = 1 AND (lastreply IS NULL OR lastreply = '0000-00-00 00:00:00') AND date(created) >= %s AND date(created) <= %s";
        $jsst_result['open'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_fromdate, $jsst_curdate));
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 1 AND date(created) >= %s AND date(created) <= %s";
        $jsst_result['answered'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_fromdate, $jsst_curdate));
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND date(created) >= %s AND date(created) <= %s";
        $jsst_result['overdue'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_fromdate, $jsst_curdate));
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND (lastreply IS NOT NULL AND lastreply != '0000-00-00 00:00:00') AND date(created) >= %s AND date(created) <= %s";
        $jsst_result['pending'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_fromdate, $jsst_curdate));

        return $jsst_result;
    }

    /**
     * A directory name for a new ticket's attachments.
     *
     * Delegates to the attachment guard, which uses the platform's random
     * source. The old implementation below it built seven letters with no
     * repeated character — a far smaller space than it looks, and the only thing
     * standing between a stranger with one leaked URL and every other file on
     * the ticket. Existing tickets keep the name already stored on their row, so
     * nothing on disk moves. (Roadmap 4.0-SEC-03)
     */
    function getRandomFolderName() {
        if (class_exists('JSSTattachmentguard')) {
            return JSSTattachmentguard::randomFolderName();
        }
        $jsst_foldername = "";
        $jsst_length = 7;
        $jsst_possible = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
        // we refer to the length of $jsst_possible a few times, so let's grab it now
        $jsst_maxlength = jssupportticketphplib::JSST_strlen($jsst_possible);
        if ($jsst_length > $jsst_maxlength) { // check for length overflow and truncate if necessary
            $jsst_length = $jsst_maxlength;
        }
        // set up a counter for how many characters are in the ticketid so far
        $jsst_i = 0;
        // add random characters to $jsst_password until $jsst_length is reached
        while ($jsst_i < $jsst_length) {
            // pick a random character from the possible ones
            $jsst_char = jssupportticketphplib::JSST_substr($jsst_possible, wp_rand(0, $jsst_maxlength - 1), 1);
            if (!strstr($jsst_foldername, $jsst_char)) {
                if ($jsst_i == 0) {
                    if (ctype_alpha($jsst_char)) {
                        $jsst_foldername .= $jsst_char;
                        $jsst_i++;
                    }
                } else {
                    $jsst_foldername .= $jsst_char;
                    $jsst_i++;
                }
            }
        }
        return $jsst_foldername;
    }

    static function generateHash($jsst_id){
        if(!is_numeric($jsst_id))
            return null;
        return jssupportticketphplib::JSST_safe_encoding(wp_json_encode(base64_encode($jsst_id)));
    }

    function generateTicketToken(){
        $jsst_match = '';
        $jsst_count = 0;
        do {
            $jsst_count++;
            $jsst_token = "";
            $jsst_length = wp_rand(9,15);
            $jsst_possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            // we refer to the length of $jsst_possible a few times, so let's grab it now
            $jsst_maxlength = jssupportticketphplib::JSST_strlen($jsst_possible);
            if ($jsst_length > $jsst_maxlength) { // check for length overflow and truncate if necessary
                $jsst_length = $jsst_maxlength;
            }
            $jsst_i = 0;
            // add random characters to $jsst_password until $jsst_length is reached
            while ($jsst_i < $jsst_length) {
                // pick a random character from the possible ones
                $jsst_char = jssupportticketphplib::JSST_substr($jsst_possible, wp_rand(0, $jsst_maxlength - 1), 1);
                if (!jssupportticketphplib::JSST_strstr($jsst_token, $jsst_char)) {
                    if ($jsst_i == 0) {
                        if (ctype_alpha($jsst_char)) {
                            $jsst_token .= $jsst_char;
                            $jsst_i++;
                        }
                    } else {
                        $jsst_token .= $jsst_char;
                        $jsst_i++;
                    }
                }
            }
            $jsst_token = hash("sha256", $jsst_token);
            
            $jsst_query = "SELECT count(token) FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE token = %s";
            $jsst_row = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_token));
            if($jsst_row > 0)
                $jsst_match = 'Y';
            else
                $jsst_match = 'N';
        }while ($jsst_match == 'Y');

        return $jsst_token;

    }

    function getUIdById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
        $jsst_ticketuid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_ticketuid;
    }

    function getNotificationIdById($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "SELECT notificationid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d";
        $jsst_notificationid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_notificationid;
    }

    function getAdminTicketSearchFormData($jsst_search_userfields){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'my-ticket') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_userfields = JSSTincluder::getObjectClass('customfields')->adminFieldsForSearch(1);
        $jsst_search_array['subject'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('subject' , ''));
        $jsst_search_array['name'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('name' , ''));
        $jsst_search_array['email'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('email' , ''));
        $jsst_search_array['phone'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('phone' , ''));
        $jsst_search_array['ticketid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('ticketid' , ''));
        $jsst_search_array['datestart'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('datestart' , ''));
        $jsst_search_array['dateend'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('dateend' , ''));
        $jsst_search_array['orderid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('orderid' , ''));
        $jsst_search_array['eddorderid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('eddorderid', ''));
        $jsst_search_array['priority'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('priority' , ''));
        $jsst_search_array['departmentid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('departmentid' , ''));
        $jsst_search_array['tagid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('tagid' , '')); // Roadmap 4.0-CORE-17
        $jsst_search_array['helptopicid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('helptopicid' , ''));
        $jsst_search_array['productid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('productid' , ''));
        $jsst_search_array['list'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('list', null ,1));
        $jsst_search_array['staffid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('staffid' , ''));
        $jsst_search_array['status'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('status' , ''));
        $jsst_search_array['sortby'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('sortby' , ''));
        $jsst_search_array['keywords'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('keywords' , '')); // Roadmap 4.0-CORE-18
        $jsst_search_array['search_from_ticket'] = 1;
        if (!empty($jsst_search_userfields)) {
            foreach ($jsst_search_userfields as $jsst_uf) {
                $jsst_search_array['jsst_ticket_custom_field'][$jsst_uf->field] = JSSTrequest::getVar($jsst_uf->field, 'post');
            }
        }

        // A saved view replaces the filters rather than adding to them.
        // (Roadmap 4.0-CORE-18)
        //
        // Choosing a view is one control on the same form as every other filter,
        // so the request carries both what the agent picked and whatever the
        // form happened to still be showing. The view is what they asked for, so
        // it wins outright: every filter it does not set is cleared. Merging the
        // two instead would mean a view showed a different queue depending on
        // what the screen looked like when it was chosen.
        $jsst_viewid = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('viewid' , ''));
        if ($jsst_viewid !== '' && is_numeric($jsst_viewid)) {
            $jsst_viewfilters = JSSTqueue::getViewFilters($jsst_viewid);
            if (!empty($jsst_viewfilters)) {
                // The tab is the one exception to the view winning outright.
                // Choosing a view clears the list field (see the script on the
                // ticket list), so the view opens on its own tab; clicking a tab
                // sets it, and then the tab is the newer instruction and keeps
                // the view's other filters. Without this an agent could never
                // look at the closed tickets inside a saved view.
                $jsst_postedlist = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('list', null, ''));
                foreach (JSSTqueue::viewKeys() as $jsst_key) {
                    $jsst_search_array[$jsst_key] = isset($jsst_viewfilters[$jsst_key]) ? $jsst_viewfilters[$jsst_key] : '';
                }
                if ($jsst_postedlist !== '') {
                    $jsst_search_array['list'] = $jsst_postedlist;
                }
                // A view saved without a tab of its own opens on Open rather
                // than on nothing.
                if ($jsst_search_array['list'] === '') {
                    $jsst_search_array['list'] = JSSTqueue::LIST_OPEN;
                }
                $jsst_search_array['viewid'] = (int) $jsst_viewid;
            }
        }
        if (!isset($jsst_search_array['viewid'])) {
            $jsst_search_array['viewid'] = '';
        }
        return $jsst_search_array;
    }

    function getFrontSideTicketSearchFormData($jsst_search_userfields){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'my-ticket') ) {
            die( 'Security check Failed' );
        }$jsst_search_array = array();
        $jsst_search_userfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
        $jsst_search_array['subject'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-subject' , ''));
        $jsst_search_array['name'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-from' , ''));
        $jsst_search_array['email'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-email' , ''));
        $jsst_search_array['phone'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-phone' , ''));
        $jsst_search_array['ticketid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-ticket' , ''));
        $jsst_search_array['datestart'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-datestart' , ''));
        $jsst_search_array['dateend'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-dateend' , ''));
        $jsst_search_array['orderid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-orderid' , ''));
        $jsst_search_array['eddorderid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-eddorderid', ''));
        $jsst_search_array['priority'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-priorityid' , ''));
        $jsst_search_array['departmentid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-departmentid' , ''));
        $jsst_search_array['tagid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-tagid' , '')); // Roadmap 4.0-CORE-17
        $jsst_search_array['helptopicid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-helptopicid' , ''));
        $jsst_search_array['productid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-productid' , ''));
        $jsst_search_array['list'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('list', null ,1));
        $jsst_search_array['assignedtome'] = JSSTrequest::getVar('assignedtome', 'post');
        $jsst_search_array['staffid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('staffid' , ''));
        $jsst_search_array['status'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-status' , ''));
        $jsst_search_array['sortby'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('sortby' , ''));
        $jsst_search_array['ticketkeys'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('jsst-ticketsearchkeys', 'post')));
        $jsst_search_array['search_from_ticket'] = 1;
        if (!empty($jsst_search_userfields)) {
            foreach ($jsst_search_userfields as $jsst_uf) {
                $jsst_search_array['jsst_ticket_custom_field'][$jsst_uf->field] = JSSTrequest::getVar($jsst_uf->field, 'post');
            }
        }
        return $jsst_search_array;
    }

    function getCookiesSavedSearchDataTicket($jsst_search_userfields){
        $jsst_search_array = array();
        $jsst_ticket_search_cookie_data = '';
        if(isset($_COOKIE['jsst_ticket_search_data'])){
            $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
            $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
        }
        if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_ticket']) && $jsst_ticket_search_cookie_data['search_from_ticket'] == 1){
            $jsst_search_array['subject'] = $jsst_ticket_search_cookie_data['subject'];
            $jsst_search_array['name'] = $jsst_ticket_search_cookie_data['name'];
            $jsst_search_array['email'] = $jsst_ticket_search_cookie_data['email'];
            $jsst_search_array['phone'] = $jsst_ticket_search_cookie_data['phone'];
            $jsst_search_array['ticketid'] = $jsst_ticket_search_cookie_data['ticketid'];
            $jsst_search_array['datestart'] = $jsst_ticket_search_cookie_data['datestart'];
            $jsst_search_array['dateend'] = $jsst_ticket_search_cookie_data['dateend'];
            $jsst_search_array['orderid'] = $jsst_ticket_search_cookie_data['orderid'];
            $jsst_search_array['eddorderid'] = $jsst_ticket_search_cookie_data['eddorderid'];
            $jsst_search_array['priority'] = $jsst_ticket_search_cookie_data['priority'];
            $jsst_search_array['departmentid'] = $jsst_ticket_search_cookie_data['departmentid'];
            $jsst_search_array['tagid'] = isset($jsst_ticket_search_cookie_data['tagid']) ? $jsst_ticket_search_cookie_data['tagid'] : null;
            $jsst_search_array['helptopicid'] = $jsst_ticket_search_cookie_data['helptopicid'];
            $jsst_search_array['productid'] = $jsst_ticket_search_cookie_data['productid'];
            $jsst_search_array['staffid'] = $jsst_ticket_search_cookie_data['staffid'];
            $jsst_search_array['status'] = $jsst_ticket_search_cookie_data['status'];
            $jsst_search_array['sortby'] = $jsst_ticket_search_cookie_data['sortby'];
            $jsst_search_array['list'] = $jsst_ticket_search_cookie_data['list'];
            $jsst_search_array['assignedtome'] = isset($jsst_ticket_search_cookie_data['assignedtome']) ? $jsst_ticket_search_cookie_data['assignedtome'] : null;
            $jsst_search_array['ticketkeys'] = isset($jsst_ticket_search_cookie_data['ticketkeys']) ? $jsst_ticket_search_cookie_data['ticketkeys'] : false;
            // Roadmap 4.0-CORE-18. isset-guarded like the keys added before it:
            // the cookie may have been written by an earlier version and must not
            // become a notice on the first page of results after an upgrade.
            $jsst_search_array['keywords'] = isset($jsst_ticket_search_cookie_data['keywords']) ? $jsst_ticket_search_cookie_data['keywords'] : null;
            $jsst_search_array['viewid'] = isset($jsst_ticket_search_cookie_data['viewid']) ? $jsst_ticket_search_cookie_data['viewid'] : null;
            if (!empty($jsst_search_userfields)) {
                foreach ($jsst_search_userfields as $jsst_uf) {
                    $jsst_search_array['jsst_ticket_custom_field'][$jsst_uf->field] = (isset($jsst_ticket_search_cookie_data['jsst_ticket_custom_field'][$jsst_uf->field]) && $jsst_ticket_search_cookie_data['jsst_ticket_custom_field'][$jsst_uf->field] != '') ? $jsst_ticket_search_cookie_data['jsst_ticket_custom_field'][$jsst_uf->field] : null;
                }
            }
        }

        return $jsst_search_array;
    }

    function setSearchVariableForTicket($jsst_search_array,$jsst_search_userfields){

        jssupportticket::$_search['ticket']['subject'] = isset($jsst_search_array['subject']) ? $jsst_search_array['subject'] : null;
        jssupportticket::$_search['ticket']['name'] = isset($jsst_search_array['name']) ? $jsst_search_array['name'] : null;
        jssupportticket::$_search['ticket']['phone'] = isset($jsst_search_array['phone']) ? $jsst_search_array['phone'] : null;
        jssupportticket::$_search['ticket']['email'] = isset($jsst_search_array['email']) ? $jsst_search_array['email'] : null;
        jssupportticket::$_search['ticket']['ticketid'] = isset($jsst_search_array['ticketid']) ? $jsst_search_array['ticketid'] : null;
        jssupportticket::$_search['ticket']['datestart'] = isset($jsst_search_array['datestart']) ? $jsst_search_array['datestart'] : null;
        jssupportticket::$_search['ticket']['dateend'] = isset($jsst_search_array['dateend']) ? $jsst_search_array['dateend'] : null;
        jssupportticket::$_search['ticket']['orderid'] = isset($jsst_search_array['orderid']) ? $jsst_search_array['orderid'] : null;
        jssupportticket::$_search['ticket']['eddorderid'] = isset($jsst_search_array['eddorderid']) ? $jsst_search_array['eddorderid'] : null;
        jssupportticket::$_search['ticket']['priority'] = isset($jsst_search_array['priority']) ? $jsst_search_array['priority'] : null;
        jssupportticket::$_search['ticket']['departmentid'] = isset($jsst_search_array['departmentid']) ? $jsst_search_array['departmentid'] : null;
        jssupportticket::$_search['ticket']['helptopicid'] = isset($jsst_search_array['helptopicid']) ? $jsst_search_array['helptopicid'] : null;
        jssupportticket::$_search['ticket']['tagid'] = isset($jsst_search_array['tagid']) ? $jsst_search_array['tagid'] : null;
        jssupportticket::$_search['ticket']['productid'] = isset($jsst_search_array['productid']) ? $jsst_search_array['productid'] : null;
        jssupportticket::$_search['ticket']['staffid'] = isset($jsst_search_array['staffid']) ? $jsst_search_array['staffid'] : null;
        jssupportticket::$_search['ticket']['status'] = isset($jsst_search_array['status']) ? $jsst_search_array['status'] : null;
        jssupportticket::$_search['ticket']['sortby'] = isset($jsst_search_array['sortby']) ? $jsst_search_array['sortby'] : null;
        jssupportticket::$_search['ticket']['list'] = isset($jsst_search_array['list']) ? $jsst_search_array['list'] : 1;
        // frontend
        jssupportticket::$_search['ticket']['assignedtome'] = isset($jsst_search_array['assignedtome']) ? $jsst_search_array['assignedtome'] : null;
        jssupportticket::$_search['ticket']['ticketkeys'] = isset($jsst_search_array['ticketkeys']) ? $jsst_search_array['ticketkeys'] : false;
        // Queue keyword search, and which saved view produced this state.
        // (Roadmap 4.0-CORE-18)
        jssupportticket::$_search['ticket']['keywords'] = isset($jsst_search_array['keywords']) ? $jsst_search_array['keywords'] : null;
        jssupportticket::$_search['ticket']['viewid'] = isset($jsst_search_array['viewid']) ? $jsst_search_array['viewid'] : null;
        if (!empty($jsst_search_userfields)) {
            foreach ($jsst_search_userfields as $jsst_uf) {
                jssupportticket::$_search['jsst_ticket_custom_field'][$jsst_uf->field] = isset($jsst_search_array['jsst_ticket_custom_field'][$jsst_uf->field]) ? $jsst_search_array['jsst_ticket_custom_field'][$jsst_uf->field] : null;
            }
        }
    }
    function checkIsTicketDuplicate($jsst_subject,$jsst_email){
        if(empty($jsst_subject)) return false;
        if(empty($jsst_email)) return true;

        $jsst_curdate = date_i18n('Y-m-d H:i:s');
        $jsst_query = 'SELECT created FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE email = %s AND subject = %s ORDER BY created DESC LIMIT 1';
        $jsst_datetime = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_email, $jsst_subject));
        if($jsst_datetime){
            $jsst_diff = jssupportticketphplib::JSST_strtotime($jsst_curdate) - jssupportticketphplib::JSST_strtotime($jsst_datetime);
            if($jsst_diff <= 15){
                return false;
            }
        }
        return true;
    }
    function getDefaultMultiFormId(){
        $jsst_query = "SHOW TABLES LIKE '%js_ticket_multiform%'";
        $jsst_count = jssupportticket::$_db->query($jsst_query);
        if ($jsst_count == 1) {
            $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_multiform` WHERE is_default = 1 ";
            $jsst_id = jssupportticket::$_db->get_row($jsst_query);
            if(isset($jsst_id)) {
                return $jsst_id->id;
            }
        }
        return 1;
    }

    /**
     * A fresh nonce for the *new* ticket form. (Roadmap 3.2-CORE-03)
     *
     * The form's nonce is part of its action URL, so on a site with full-page
     * caching every visitor is served the same nonce until the cache is purged,
     * and once it expires every guest submission fails. The form asks for a
     * fresh one over admin-ajax, which caching plugins do not cache.
     *
     * Deliberately limited to the create-ticket action: it takes no input and
     * cannot be used to mint a nonce for editing an existing ticket.
     */
    function refreshTicketFormNonce(){
        return wp_create_nonce('save-ticket-');
    }

    function isFieldRequired(){
        $jsst_field = JSSTrequest::getVar('field');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'is-field-required-'.$jsst_field) ) {
            die( 'Security check Failed' );
        }
        $jsst_query = "SELECT required  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE  field =%s";
        return jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_field));
    }

    function getClosedBy($jsst_id){
        if(!is_numeric($jsst_id)) return false;
        if ($jsst_id == 0) {
            $jsst_closedBy = esc_html(__('System', 'js-support-ticket'));
        } else if($jsst_id == -1){
            $jsst_closedBy = esc_html(__('Guest', 'js-support-ticket'));
        } else {
            $jsst_query = "SELECT display_name AS name FROM `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` WHERE id = %d";
            $jsst_closedBy = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        }
        return $jsst_closedBy;
    }

    function checkAIReplyTicketsBySubject() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'check-smart-reply')) {
            die('Security check Failed');
        }

        // --- SECURITY & PERMISSION FIX ---
        $is_admin = current_user_can('manage_options');
        $has_access = false;

        if ($is_admin) {
            $has_access = true;
        } else {
            // Check if user is an agent
            if (in_array('agent', jssupportticket::$_active_addons)) {
                $agent_model = JSSTincluder::getJSModel('agent');
                if ($agent_model && method_exists($agent_model, 'isUserStaff') && $agent_model->isUserStaff()) {
                    // Check specific permission for agents
                    if (JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Use AI Powered Reply Feature')) {
                        $has_access = true;
                    }
                }
            }
        }

        // If the user is neither an admin nor an authorized agent, block access immediately
        if (!$has_access) {
            return json_encode([]); 
        }
        // --- END PERMISSION FIX ---

        // Explicitly cast to integer to kill SQL Injection payloads
        $jsst_id = absint(JSSTrequest::getVar('ticketId')); 
        $jsst_subject = sanitize_text_field(JSSTrequest::getVar('ticketSubject'));

        $jsst_agentquery = "";
        if (in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $jsst_allowed = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Limit AI Replies to Agent-Assigned Tickets');
            if ($jsst_allowed) {
                $jsst_staffid = absint(JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid()));
                $jsst_agentquery = " AND (t.staffid = %d OR t.departmentid IN (
                    SELECT dept.departmentid
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` AS dept
                    WHERE dept.staffid = %d)) ";
                $jsst_agentquery = jssupportticket::$_db->prepare($jsst_agentquery, $jsst_staffid, $jsst_staffid);
            }
        }

        $jsst_min_relevance = 1.5; // Minimum relevance score to consider

        // Get current ticket's message (for reply-based matching)
        $jsst_query = "
            SELECT ticket.message, ticket.uid
            FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
            WHERE ticket.id = %d";
        $jsst_ticket_data = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare($jsst_query, $jsst_id));
        
        if (!$jsst_ticket_data) return json_encode([]);

        $jsst_message = wp_strip_all_tags($jsst_ticket_data->message);

        // Break the subject and message into words for partial matching
        $jsst_subject_words = array_filter(jssupportticketphplib::JSST_explode(' ', jssupportticketphplib::JSST_trim($jsst_subject)));
        $jsst_subject_word_count = count($jsst_subject_words);

        // Weighted scoring query with exact match detection
        $jsst_query = "
            SELECT
                t_scores.id,
                t_scores.ticketid,
                t_scores.subject,
                t_scores.message,
                t_scores.created,
                t_scores.subject_score,
                t_scores.message_score,
                (t_scores.subject_score + t_scores.message_score) AS total_relevance,
                t_scores.is_exact_subject_match,
                t_scores.is_exact_message_match
            FROM (
                SELECT
                    t.id,
                    t.ticketid,
                    t.subject,
                    t.message,
                    t.created,
                    3 * IFNULL(MATCH(t.subject) AGAINST(%s IN NATURAL LANGUAGE MODE), 0) AS subject_score,
                    1 * IFNULL(MATCH(t.message) AGAINST(%s IN NATURAL LANGUAGE MODE), 0) AS message_score,
                    t.subject LIKE %s AS is_exact_subject_match,
                    t.message LIKE %s AS is_exact_message_match
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` t
                WHERE t.id != %d
                " . $jsst_agentquery . "
            ) AS t_scores
            HAVING total_relevance > %f
            ORDER BY total_relevance DESC LIMIT 50";
        $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_subject, $jsst_message, '%'.$jsst_subject.'%', '%'.$jsst_message.'%', $jsst_id, $jsst_min_relevance);

        $jsst_tickets = jssupportticket::$_db->get_results($jsst_query);

        // Final result formatting
        $jsst_results = [];
        if (!empty($jsst_tickets)) {
            // Compute custom_score and find max
            $jsst_highest_score = 0;
            foreach ($jsst_tickets as &$jsst_ticket) {
                $jsst_custom_score = 0;
                
                // Exact matches get highest priority
                if ($jsst_ticket->is_exact_subject_match) {
                    $jsst_custom_score += ($jsst_subject_word_count * 10) + 4;
                } elseif ($jsst_ticket->is_exact_message_match) {
                    $jsst_custom_score += ($jsst_subject_word_count * 10) + 0;
                } elseif ($jsst_subject_word_count > 1) {
                    // Partial word combination matching in subject
                    for ($jsst_i = 0; $jsst_i < $jsst_subject_word_count - 1; $jsst_i++) {
                        $jsst_wordCombination = $jsst_subject_words[$jsst_i] . ' ' . ($jsst_subject_words[$jsst_i + 1] ?? '');
                        if (stripos($jsst_ticket->subject, $jsst_wordCombination) !== false) {
                            $jsst_custom_score += 10;
                        }
                    }
                }
                
                $jsst_ticket->custom_score = $jsst_custom_score;
                if ($jsst_ticket->custom_score > $jsst_highest_score) {
                    $jsst_highest_score = $jsst_ticket->custom_score;
                }
            }
            unset($jsst_ticket);

            // Sort tickets by custom_score and total_relevance
            usort($jsst_tickets, function ($jsst_a, $jsst_b) {
                if ($jsst_a->custom_score === $jsst_b->custom_score) {
                    return $jsst_b->total_relevance <=> $jsst_a->total_relevance;
                }
                return $jsst_b->custom_score <=> $jsst_a->custom_score;
            });

            // Apply threshold like before, but considering both custom_score and total_relevance
            $jsst_filtered_tickets = [];
            $jsst_threshold_percentage = 30; // 30% threshold
            
            // Calculate threshold values only if highest_custom_score is not zero to avoid division by zero
            $jsst_custom_score_threshold_value = ($jsst_highest_score > 0) ? ($jsst_threshold_percentage / 100) * $jsst_highest_score : 0;
            $jsst_highest_total_relevance = 0;
            foreach ($jsst_tickets as $jsst_tkt) {
                if ($jsst_tkt->total_relevance > $jsst_highest_total_relevance) {
                    $jsst_highest_total_relevance = $jsst_tkt->total_relevance;
                }
            }
            $jsst_total_relevance_threshold_value = ($jsst_highest_total_relevance > 0) ? ($jsst_threshold_percentage / 100) * $jsst_highest_total_relevance : 0;
            foreach ($jsst_tickets as $jsst_index => $jsst_ticket) {
                // Always keep the top result after sorting by custom_score
                if ($jsst_index === 0) {
                    $jsst_filtered_tickets[$jsst_ticket->id] = $jsst_ticket;
                    continue;
                }

                // Condition 1: Check if custom_score is above its threshold
                $jsst_is_custom_score_above_threshold = ($jsst_ticket->custom_score > 0 && $jsst_ticket->custom_score >= $jsst_custom_score_threshold_value);

                // Condition 2: Check if total_relevance is above its threshold
                $jsst_is_total_relevance_above_threshold = $jsst_ticket->total_relevance >= $jsst_total_relevance_threshold_value;

                // Condition 3: Handle cases where both scores are very low (similar to original code)
                // If custom_score is 0, total_relevance must meet the minimum relevance.
                // This prevents purely NLP-driven low-relevance results if no custom score is found.
                $jsst_is_scores_too_low = ($jsst_ticket->custom_score == 0 && $jsst_ticket->total_relevance < $jsst_min_relevance);

                if ($jsst_is_scores_too_low) {
                    continue;
                }

                if ($jsst_is_custom_score_above_threshold || $jsst_is_total_relevance_above_threshold) {
                     // Ensure uniqueness by post id, keeping the highest custom_score and then the highest total_relevance
                    if (
                        !isset($jsst_filtered_tickets[$jsst_ticket->id]) ||
                        $jsst_ticket->custom_score > $jsst_filtered_tickets[$jsst_ticket->id]->custom_score ||
                        ($jsst_ticket->custom_score === $jsst_filtered_tickets[$jsst_ticket->id]->custom_score && $jsst_ticket->total_relevance > $jsst_filtered_tickets[$jsst_ticket->id]->total_relevance)
                    ) {
                        $jsst_filtered_tickets[$jsst_ticket->id] = $jsst_ticket;
                    }
                }
            }

            $jsst_tickets = array_values($jsst_filtered_tickets);

            foreach ($jsst_tickets as $jsst_ticket) {
                $jsst_results[] = [
                    'id' => $jsst_ticket->id,
                    'text' => $jsst_ticket->subject,
                    'message' => wp_strip_all_tags($jsst_ticket->message),
                    'ticketid' => $jsst_ticket->ticketid,
                    'relevance' => $jsst_ticket->total_relevance,
                    'custom_score' => $jsst_ticket->custom_score
                ];
            }
        }

        return json_encode($jsst_results);
    }

    // ==========================================
    // 2. ADVANCED NLP SEARCH LOGIC (Front-End AJAX)
    // ==========================================
    /**
     * Suggested answers for the ticket form, before a ticket is created.
     *
     * Core keeps a deliberately small free search over content the help desk
     * already holds. The Instant Resolve addon replaces it wholesale through
     * the filter below - adding indexed documentation, resolved tickets and the
     * AI layer - so exactly one retriever decides what a customer is shown and
     * what the AI is later allowed to answer from. Two implementations is how
     * you end up showing somebody three relevant articles and then telling them
     * there is nothing on the topic.
     */
    public function getInstantResolveSearch() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'get-instantresolve-search')) {
            die('Security check Failed');
        }

        if (isset(jssupportticket::$_config['instantresolve_enable'])
            && jssupportticket::$_config['instantresolve_enable'] != 1) {
            echo wp_json_encode(array());
            wp_die();
        }

        $jsst_subject = sanitize_text_field(JSSTrequest::getVar('subject'));
        $jsst_message = sanitize_textarea_field(JSSTrequest::getVar('message'));

        $jsst_text = trim(preg_replace('/\s+/', ' ', $jsst_subject . ' ' . wp_strip_all_tags($jsst_message)));

        $jsst_min = isset(jssupportticket::$_config['instantresolve_min_chars'])
            ? intval(jssupportticket::$_config['instantresolve_min_chars']) : 15;

        if (jssupportticketphplib::JSST_strlen($jsst_text) < $jsst_min) {
            echo wp_json_encode(array());
            wp_die();
        }

        // Retrieval is a database query; the written AI answer is a charged
        // generation. The form asks for the answer only once the customer stops
        // typing, so a request made mid-sentence must not pay for one.
        $jsst_summary = (intval(JSSTrequest::getVar('summary')) === 1);

        // Out of answer budget: fall back to the links rather than to nothing.
        // The throttle exists to protect the expensive half, and a customer who
        // shares an office address with a heavy user should still get the
        // suggestions that cost a query.
        if ($jsst_summary && !$this->checkInstantResolveRate(true)) {
            $jsst_summary = false;
        }

        // A summary request performs retrieval too, so it is counted here as
        // well - the two buckets are not alternatives.
        if (!$this->checkInstantResolveRate(false)) {
            echo wp_json_encode(array());
            wp_die();
        }

        $jsst_opts = array('summary' => $jsst_summary);

        // Null, not an empty array, means "nobody handled this" - an addon that
        // legitimately found nothing must be able to say so without core
        // second-guessing it and running its own search on top.
        $jsst_results = apply_filters('jsst_instantresolve_search_results', null, $jsst_text, $jsst_opts);

        if (!is_array($jsst_results)) {
            $jsst_results = $this->getBasicFixSuggestions($jsst_text);
        }

        echo wp_json_encode($jsst_results);
        wp_die();
    }

    /**
     * Throttle the suggestion endpoint.
     *
     * This is reachable by guests, and the ticket form calls it while somebody
     * types, so in normal use the only thing bounding it is an 800ms debounce
     * in the browser - which a client that is not the browser simply ignores.
     * The nonce does not help either: a guest can load the form and read one.
     *
     * The two request kinds are counted separately because they cost different
     * things. A links request is a FULLTEXT query across the configured
     * sources; a summary request is additionally a charged model generation,
     * so it gets a much smaller budget over a much longer window.
     *
     * Limits are deliberately generous. Logged-in visitors are keyed by user
     * id, but guests can only be keyed by address, and behind a shared proxy or
     * a company NAT that is one key for the whole building - so the ceiling has
     * to sit far above what a room full of people writing tickets can reach.
     *
     * @param bool $jsst_summary Count against the answer budget, not the search one.
     * @return bool True when the request may proceed.
     */
    private function checkInstantResolveRate($jsst_summary) {
        $jsst_uid = get_current_user_id();

        if ($jsst_uid > 0) {
            $jsst_who = 'u' . $jsst_uid;
        } else {
            // REMOTE_ADDR only. X-Forwarded-For and friends are supplied by the
            // caller, so honouring them would let anyone clear their own
            // counter by changing a header - which is worse than no limit,
            // because it looks like one is in place.
            $jsst_ip  = isset($_SERVER['REMOTE_ADDR'])
                ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
            $jsst_who = 'g' . md5($jsst_ip);
        }

        $jsst_kind   = $jsst_summary ? 'ai' : 'search';
        $jsst_limit  = $jsst_summary ? 12 : 40;
        $jsst_window = $jsst_summary ? 300 : 60;

        $jsst_limit  = intval(apply_filters('jsst_instantresolve_rate_limit', $jsst_limit, $jsst_kind));
        $jsst_window = intval(apply_filters('jsst_instantresolve_rate_window', $jsst_window, $jsst_kind));

        // A filter may switch the limit off outright; a nonsensical window
        // must not silently become a one-second lockout.
        if ($jsst_limit < 1 || $jsst_window < 1) return true;

        $jsst_key = 'jsst_ir_rate_' . $jsst_kind . '_' . $jsst_who;
        $jsst_now = time();

        // A fixed window, not a rolling expiry. Re-setting the transient with
        // the full window on every request would keep pushing the reset out, so
        // a customer who hit the ceiling while genuinely working would stay
        // locked out until they stopped typing for the whole window.
        $jsst_bucket = get_transient($jsst_key);
        if (!is_array($jsst_bucket)
            || !isset($jsst_bucket['start'], $jsst_bucket['count'])
            || ($jsst_now - intval($jsst_bucket['start'])) >= $jsst_window) {
            $jsst_bucket = array('start' => $jsst_now, 'count' => 0);
        }

        if (intval($jsst_bucket['count']) >= $jsst_limit) return false;

        $jsst_bucket['count'] = intval($jsst_bucket['count']) + 1;

        // Expire with the window it represents, plus a second, so the row
        // cannot disappear a tick before the window has actually closed.
        $jsst_ttl = $jsst_window - ($jsst_now - intval($jsst_bucket['start'])) + 1;
        set_transient($jsst_key, $jsst_bucket, $jsst_ttl);

        return true;
    }

    /**
     * The database-backed sources the free-tier suggestion search reads, in the
     * order they are ranked. WordPress posts are not here: they are searched
     * through WP_Query and own no plugin table.
     *
     * Shared by the search itself and by repairSuggestionIndexes(), so a source
     * can never be searched with one column list and indexed with another.
     */
    private static function instantResolveSources() {
        return array(
            'kb' => array(
                'addon'   => 'knowledgebase',
                'table'   => 'js_ticket_articles',
                'title'   => 'subject',
                'body'    => 'content',
                'where'   => 'status = 1',
                'guest'   => 'visible <> 2',
                'route'   => array('knowledgebase', 'articledetails'),
            ),
            'faq' => array(
                'addon'   => 'faq',
                'table'   => 'js_ticket_faqs',
                'title'   => 'subject',
                'body'    => 'content',
                'where'   => 'status = 1',
                'guest'   => 'visible <> 2',
                'route'   => array('faq', 'faqdetails'),
            ),
            // Canned Responses is part of the free core (JSSTmergedaddon), so it
            // names no owning addon - like the WordPress posts source below.
            'canned' => array(
                'addon'   => null,
                'table'   => 'js_ticket_department_message_premade',
                'title'   => 'title',
                'body'    => 'answer',
                'where'   => '',
                'guest'   => '',
                'route'   => null,
            ),
        );
    }

    /** Whether one column carries a FULLTEXT index of its own, asked fresh. */
    private function hasFulltextIndexOn($jsst_table, $jsst_column) {
        $jsst_rows = jssupportticket::$_db->get_results(
            "SHOW INDEX FROM `" . $jsst_table . "` WHERE Index_type = 'FULLTEXT'"
        );
        $jsst_byname = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_byname[$jsst_row->Key_name][] = $jsst_row->Column_name;
        }
        foreach ($jsst_byname as $jsst_cols) {
            if ($jsst_cols === array($jsst_column)) return true;
        }
        return false;
    }

    /** Transient name holding the index verdict for one table and column pair. */
    private static function suggestionIndexKey($jsst_table, $jsst_title, $jsst_body) {
        return 'jsst_ir_ftidx_' . md5($jsst_table . '|' . $jsst_title . '|' . $jsst_body);
    }

    /**
     * Create the per-column FULLTEXT indexes the suggestion query needs, on any
     * source table that is missing them.
     *
     * The ranking weights title above body, so it matches the two columns
     * separately, and MySQL serves MATCH(col) only from an index whose column
     * list is exactly that column. Every table involved ships a *composite*
     * index instead - core's canned responses, and the knowledgebase and FAQ
     * add-ons, whose fresh-install schema comes from the licence server and so
     * cannot be corrected from here at all. Without this repair those sources
     * are not merely unranked, they are skipped outright by
     * hasSuggestionIndexes(), and a clean install can only ever suggest
     * WordPress posts.
     *
     * Called once per plugin version from admin_init - never from the search
     * itself, which runs on a debounce while a customer types and must not
     * build an index. Failures are hidden and logged: a server that cannot
     * build a FULLTEXT index leaves the source skipped, which is the same
     * behaviour as before.
     *
     * @return array Table name => list of indexes created, for the caller's log.
     */
    public function repairSuggestionIndexes() {
        $jsst_created = array();

        foreach (self::instantResolveSources() as $jsst_key => $jsst_def) {
            $jsst_full = jssupportticket::$_db->prefix . $jsst_def['table'];
            if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . esc_sql($jsst_full) . "'") != $jsst_full) {
                continue;
            }

            // Group each FULLTEXT index by name so a composite can be told from
            // a single: only an index whose whole column list is the one column
            // counts as present.
            $jsst_rows = jssupportticket::$_db->get_results(
                "SHOW INDEX FROM `" . $jsst_full . "` WHERE Index_type = 'FULLTEXT'"
            );
            $jsst_byname = array();
            foreach ((array) $jsst_rows as $jsst_row) {
                $jsst_byname[$jsst_row->Key_name][] = $jsst_row->Column_name;
            }

            foreach (array($jsst_def['title'], $jsst_def['body']) as $jsst_column) {
                $jsst_have = false;
                foreach ($jsst_byname as $jsst_cols) {
                    if ($jsst_cols === array($jsst_column)) $jsst_have = true;
                }
                if ($jsst_have) continue;

                // ft_<column>, which is what the add-on update files and the
                // canned-responses schema already create; an install that took
                // one of those paths is left alone by the check above.
                $jsst_name = 'ft_' . $jsst_column;
                if (isset($jsst_byname[$jsst_name])) continue;   // same name, other columns

                jssupportticket::$_db->hide_errors();
                jssupportticket::$_db->query('ALTER TABLE `' . $jsst_full . '` ADD FULLTEXT `'
                    . $jsst_name . '` (`' . $jsst_column . '`)');
                $jsst_error = jssupportticket::$_db->last_error;
                jssupportticket::$_db->show_errors();

                if ($jsst_error != null) {
                    // Two admin requests can reach this at once - a page load
                    // and the heartbeat, say - and the one that loses the race
                    // is told "Duplicate key name" for an index that is now
                    // there. Ask the table rather than the error: the index
                    // existing is the outcome either way, and the log is for
                    // the case where it really is not.
                    if (!$this->hasFulltextIndexOn($jsst_full, $jsst_column)) {
                        error_log(sprintf(
                            'JS Help Desk: could not add FULLTEXT index %s on %s, instant-resolve source "%s" stays disabled: %s',
                            $jsst_name, $jsst_full, $jsst_key, $jsst_error
                        ));
                    }
                    continue;
                }
                $jsst_created[$jsst_full][] = $jsst_name;
            }

            // The verdict is cached for five minutes; drop it so a source
            // repaired here is searchable on the next keystroke rather than
            // after the cache happens to expire.
            delete_transient(self::suggestionIndexKey($jsst_full, $jsst_def['title'], $jsst_def['body']));
        }

        return $jsst_created;
    }

    /**
     * Whether a table carries a single-column FULLTEXT index on both columns.
     *
     * Cached for a few minutes because the answer changes only when an addon is
     * installed or updated, while the question is asked on every search. The
     * cache is short rather than permanent so an index created later is picked
     * up on its own, without an admin knowing to clear anything.
     *
     * @return bool True when MATCH() can be used on both columns.
     */
    private function hasSuggestionIndexes($jsst_table, $jsst_title, $jsst_body) {
        $jsst_key    = self::suggestionIndexKey($jsst_table, $jsst_title, $jsst_body);
        $jsst_cached = get_transient($jsst_key);
        if ($jsst_cached !== false) return ($jsst_cached === 'yes');

        $jsst_rows = jssupportticket::$_db->get_results(
            "SHOW INDEX FROM `" . $jsst_table . "` WHERE Index_type = 'FULLTEXT'"
        );

        // Group the columns of each index, then look for one made of exactly the
        // title and one made of exactly the body. A combined (title, body) index
        // satisfies neither, which is the whole reason this check exists.
        $jsst_byname = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_byname[$jsst_row->Key_name][] = $jsst_row->Column_name;
        }

        $jsst_has_title = false;
        $jsst_has_body  = false;
        foreach ($jsst_byname as $jsst_cols) {
            if ($jsst_cols === array($jsst_title)) $jsst_has_title = true;
            if ($jsst_cols === array($jsst_body))  $jsst_has_body  = true;
        }

        $jsst_ok = ($jsst_has_title && $jsst_has_body);
        set_transient($jsst_key, $jsst_ok ? 'yes' : 'no', 5 * MINUTE_IN_SECONDS);

        return $jsst_ok;
    }

    /**
     * Free-tier suggestions: knowledgebase, FAQs, canned responses and site
     * content, ranked by FULLTEXT relevance with a bonus for a subject match.
     *
     * Intentionally modest. There is no grounding gate and no AI here, because
     * a suggestion the customer can ignore does not need one - anything that
     * feeds text to a model belongs in the addon, behind its gates.
     */
    private function getBasicFixSuggestions($jsst_text) {
        $jsst_is_guest = JSSTincluder::getObjectClass('user')->isguest();
        $jsst_results  = array();

        $jsst_enabled = isset(jssupportticket::$_config['instantresolve_sources'])
            ? json_decode(jssupportticket::$_config['instantresolve_sources'], true)
            : null;
        if (!is_array($jsst_enabled) || empty($jsst_enabled)) {
            $jsst_enabled = array('kb', 'faq', 'canned', 'posts');
        }

        $jsst_limit = isset(jssupportticket::$_config['instantresolve_max_results'])
            ? intval(jssupportticket::$_config['instantresolve_max_results']) : 5;
        if ($jsst_limit < 1 || $jsst_limit > 10) $jsst_limit = 5;

        // Subject matches count for more than body matches, and an exact phrase
        // in the subject outranks everything.
        $jsst_w_title = 3;
        $jsst_w_body  = 1;
        $jsst_w_exact = 10;

        $jsst_tables = self::instantResolveSources();

        // The canned responses table is core data now, and this method reads it
        // directly rather than through the module that owns it - so on a site
        // that never had the legacy addon nothing would have created it yet.
        // ensureSchema() costs one option read once the schema is current, and
        // does nothing at all while a legacy addon owns the table.
        if (in_array('canned', $jsst_enabled, true)) {
            JSSTmergedaddon::ensureSchema('cannedresponses');
        }

        foreach ($jsst_tables as $jsst_key => $jsst_def) {
            if (!in_array($jsst_key, $jsst_enabled, true)) continue;
            // A source with no 'addon' is core's own and always available. The
            // rest go through featureEnabled(), which is the replacement for a
            // bare $_active_addons lookup: it answers yes for a capability core
            // has absorbed as well as for a legacy addon that is still active.
            if ($jsst_def['addon'] !== null && !JSSTmergedaddon::featureEnabled($jsst_def['addon'])) continue;

            $jsst_full = jssupportticket::$_db->prefix . $jsst_def['table'];
            if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . esc_sql($jsst_full) . "'") != $jsst_full) {
                continue;
            }

            // MySQL resolves MATCH(col) only against an index whose column list
            // is exactly that column, so a table without both per-column indexes
            // makes the query below fail rather than return nothing. Checking
            // first keeps that out of the error log: this runs on every keystroke
            // once a customer starts typing, and the alternative was one logged
            // database error per source per search, forever.
            if (!$this->hasSuggestionIndexes($jsst_full, $jsst_def['title'], $jsst_def['body'])) {
                continue;
            }

            $jsst_where = array();
            if ($jsst_def['where'] !== '') $jsst_where[] = $jsst_def['where'];
            if ($jsst_is_guest && $jsst_def['guest'] !== '') $jsst_where[] = $jsst_def['guest'];

            $jsst_sql = "SELECT id, `" . $jsst_def['title'] . "` AS title,
                    SUBSTRING(`" . $jsst_def['body'] . "`, 1, 200) AS excerpt,
                    ( (" . $jsst_w_title . " * IFNULL(MATCH(`" . $jsst_def['title'] . "`) AGAINST (%s IN NATURAL LANGUAGE MODE), 0)) +
                      (" . $jsst_w_body . " * IFNULL(MATCH(`" . $jsst_def['body'] . "`) AGAINST (%s IN NATURAL LANGUAGE MODE), 0)) +
                      (CASE WHEN `" . $jsst_def['title'] . "` LIKE %s THEN " . $jsst_w_exact . " ELSE 0 END)
                    ) AS total_relevance
                FROM `" . $jsst_full . "`"
                . (!empty($jsst_where) ? ' WHERE ' . implode(' AND ', $jsst_where) : '')
                . " HAVING total_relevance > 0 ORDER BY total_relevance DESC LIMIT 3";

            $jsst_rows = jssupportticket::$_db->get_results(
                jssupportticket::$_db->prepare($jsst_sql, $jsst_text, $jsst_text, '%' . $jsst_text . '%')
            );

            /*
             * A broken source is skipped rather than allowed to take the whole
             * panel down with it - but it is no longer skipped silently. This
             * guard hid a hard database error for as long as it existed: an
             * InnoDB FULLTEXT index whose relevance evaluates to an
             * out-of-range DOUBLE makes the ranking arithmetic raise MySQL
             * error 1690, which arrived here looking exactly like "nothing
             * matched". The panel simply stayed empty and there was nothing
             * anywhere to say why.
             *
             * Logged once per source per hour, because this endpoint runs on
             * a debounce while somebody types and an unthrottled log line
             * would fill the file in a minute.
             */
            $jsst_failed = (jssupportticket::$_db->last_error != null || !is_array($jsst_rows));
            if ($jsst_failed) {
                $jsst_seen = 'jsst_ir_srcfail_' . md5($jsst_full);
                if (!get_transient($jsst_seen)) {
                    set_transient($jsst_seen, 1, HOUR_IN_SECONDS);
                    error_log(sprintf(
                        'JS Help Desk: instant-resolve source "%s" (%s) failed and was skipped: %s',
                        $jsst_key,
                        $jsst_full,
                        jssupportticket::$_db->last_error != null
                            ? jssupportticket::$_db->last_error
                            : 'query returned no result set'
                    ));
                }
                continue;
            }

            foreach ($jsst_rows as $jsst_row) {
                $jsst_row->content_type = $jsst_key;
                $jsst_row->thumbnail    = '';
                $jsst_row->timestamp    = null;
                $jsst_row->url          = '';

                if ($jsst_def['route'] !== null && method_exists('jssupportticket', 'makeUrl')) {
                    $jsst_row->url = jssupportticket::makeUrl(array(
                        'jstmod'            => $jsst_def['route'][0],
                        'jstlay'            => $jsst_def['route'][1],
                        'jssupportticketid' => intval($jsst_row->id),
                        'jsstpageid'        => jssupportticket::getPageid(),
                    ));
                }

                $jsst_row->excerpt = wp_strip_all_tags((string) $jsst_row->excerpt);
                $jsst_results[]    = $jsst_row;
            }
        }

        // Published WP content, through WP_Query rather than a FULLTEXT index:
        // wp_posts is shared with every other plugin on the site and indexing
        // it is not this plugin's decision to make.
        if (in_array('posts', $jsst_enabled, true)) {
            $jsst_query = new WP_Query(array(
                's'                      => $jsst_text,
                'post_type'              => array('post', 'page'),
                'post_status'            => 'publish',
                'posts_per_page'         => 3,
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ));

            foreach ($jsst_query->posts as $jsst_post) {
                if (post_password_required($jsst_post)) continue;

                $jsst_results[] = (object) array(
                    'id'              => $jsst_post->ID,
                    'title'           => get_the_title($jsst_post),
                    'excerpt'         => wp_html_excerpt(wp_strip_all_tags($jsst_post->post_content), 200, '…'),
                    'url'             => get_permalink($jsst_post),
                    'thumbnail'       => '',
                    'timestamp'       => null,
                    'content_type'    => 'posts',
                    // WP_Query exposes no comparable relevance number, so these
                    // sit below any FULLTEXT hit that scored at all.
                    'total_relevance' => 0.5,
                );
            }
            wp_reset_postdata();
        }

        usort($jsst_results, function ($jsst_a, $jsst_b) {
            $jsst_x = isset($jsst_a->total_relevance) ? $jsst_a->total_relevance : 0;
            $jsst_y = isset($jsst_b->total_relevance) ? $jsst_b->total_relevance : 0;
            if ($jsst_x == $jsst_y) return 0;
            return ($jsst_x > $jsst_y) ? -1 : 1;
        });

        return array_slice($jsst_results, 0, $jsst_limit);
    }
    
}
?>
