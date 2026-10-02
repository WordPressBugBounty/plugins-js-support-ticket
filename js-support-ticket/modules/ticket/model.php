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
        if ($jsst_staffid !== '' && $jsst_staffid !== null && is_numeric($jsst_staffid)){
            /* Zero means nobody, and nobody is stored two ways. Sites that have
               been through several versions of this plugin have both a literal
               0 and a NULL in this column depending on which code path left the
               ticket unassigned, so a filter written as "= 0" finds some of the
               unassigned queue and not the rest - which is worse than not
               having the filter, because it looks like it worked.
               (Roadmap 4.5-UX-01) */
            if ((int) $jsst_staffid === 0) {
                $jsst_inquery .= " AND (ticket.staffid IS NULL OR ticket.staffid = 0)";
            } else {
                $jsst_inquery .= " AND ticket.staffid = %d";
                $jsst_inquery_args[] = $jsst_staffid;
            }
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
        // The team queue. (Roadmap 4.5-FE-05)
        $jsst_teamid = isset(jssupportticket::$_search['ticket']['teamid']) ? jssupportticket::$_search['ticket']['teamid'] : '';
        jssupportticket::$jsst_data['filter']['teamid'] = $jsst_teamid;
        $jsst_inquery .= $this->teamFilterClause($jsst_teamid);
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

        /* Company filter: the company's named people and its domains, by the
           same rule the company screen counts with. Appended after prepare()
           and beside the tag clause so the count and the page agree.
           (Roadmap 5.5-COM-06) */
        $jsst_companyid = isset(jssupportticket::$_search['ticket']['companyid']) ? jssupportticket::$_search['ticket']['companyid'] : '';
        jssupportticket::$jsst_data['filter']['companyid'] = $jsst_companyid;
        if ($jsst_companyid !== '' && $jsst_companyid !== null && is_numeric($jsst_companyid) && class_exists('JSSTcompanies')) {
            $jsst_company = JSSTcompanies::get((int) $jsst_companyid);
            $jsst_tagwhere .= $jsst_company ? ' AND ' . JSSTcompanies::ticketClause($jsst_company, 'ticket') . ' ' : ' AND 1 = 0 ';
        }

        /* Which tickets this person may see. The backend queue has never had
           this clause, and the front-end agent queue always has - so an agent
           scoped to one department saw that department in the portal and every
           ticket on the site in wp-admin. The two workspaces disagreeing about
           the same agent is the whole failure this release exists to end, and
           between the two answers the narrow one is the correct one: it is
           what the Agents screen was set to.

           An administrator and an agent granted All Tickets both resolve to
           the whole site here, so the clause is empty for them and nothing
           about their queue changes. It is only ever a governed agent who is
           narrowed, and only to what somebody already decided they should see.
           (Roadmap 4.5-FE-04, 4.5-ARCH-01) */
        $jsst_actor = JSSTcapability::actor();
        $jsst_scopeclause = JSSTcapability::ticketScopeClause('ticket', $jsst_actor);

        // Pagination
        $jsst_query = "SELECT COUNT(ticket.id) "
                . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                . $jsst_tagfilter['join']
                . "WHERE 1 = 1" . $jsst_scopeclause;
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
                    WHERE 1 = 1" . $jsst_scopeclause;

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
        /* The number on every tab, counted under this person's own scope and
           the queue's own filters, and cached until something happens to a
           ticket. It was one CASE pass over the whole table - better than the
           seven separate counts it replaced in 4.0, and still a scan on every
           page load, and still counting tickets the list below would not show
           a scoped agent. (Roadmap 4.5-UX-01, 4.5-FE-04, 4.0-PERF-01) */
        $jsst_countfilters = array();
        if ($jsst_uid != null && is_numeric($jsst_uid)) {
            $jsst_countfilters['uid'] = (int) $jsst_uid;
        }
        $jsst_counts = class_exists('JSSTqueueengine')
            ? JSSTqueueengine::counts(array('actor' => $jsst_actor, 'filters' => $jsst_countfilters))
            : array();
        foreach (array_keys(JSSTqueue::countClauses()) AS $jsst_countalias) {
            jssupportticket::$jsst_data['count'][$jsst_countalias] = isset($jsst_counts[$jsst_countalias])
                ? (int) $jsst_counts[$jsst_countalias] : 0;
        }
        return;
    }

    /**
     * The team filter, as a WHERE fragment. (Roadmap 4.5-FE-05)
     *
     * Expanded to the team's members here rather than joined in SQL, because
     * both queue queries already carry four joins and a fifth would change
     * what each of them counts. A team is a handful of people, so the list is
     * short and the expansion is one indexed read.
     *
     * A team id that names no team, or one whose membership is empty, narrows
     * to nothing rather than being ignored. A filter that silently does
     * nothing shows the whole queue under the word "team", which is worse than
     * showing none of it.
     */
    private function teamFilterClause($jsst_teamid) {
        if ($jsst_teamid === null || $jsst_teamid === '' || !is_numeric($jsst_teamid) || (int) $jsst_teamid <= 0) {
            return '';
        }
        if (!class_exists('JSSTteams')) {
            return '';
        }
        $jsst_members = JSSTteams::members((int) $jsst_teamid);
        $jsst_clause = JSSTteams::memberClause('ticket', $jsst_members);
        return ($jsst_clause === '') ? ' AND 1 = 0 ' : ' AND ' . $jsst_clause . ' ';
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
                if ($jsst_staffid !== '' && $jsst_staffid !== null && is_numeric($jsst_staffid)) {
                    /* Zero is nobody, and nobody is stored both as 0 and as
                       NULL depending on which version of this plugin left the
                       ticket unassigned - so the obvious "= 0" finds part of
                       the unassigned queue and looks like it worked.
                       (Roadmap 4.5-UX-01) */
                    if ((int) $jsst_staffid === 0) {
                        $jsst_inquery .= " AND (ticket.staffid IS NULL OR ticket.staffid = 0)";
                    } else {
                        $jsst_inquery .= " AND ticket.staffid = %d";
                        $jsst_inquery_args[] = $jsst_staffid;
                    }
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
            /* Whose tickets: this person's, and for a company supervisor their
               colleagues' as well - the same rule the capability layer applies,
               so a ticket listed here is a ticket that opens.
               (Roadmap 5.5-COM-06) */
            $jsst_owner = jssupportticket::$_db->prepare('ticket.uid = %d', $jsst_uid);
            $jsst_shared = class_exists('JSSTcompanies')
                ? JSSTcompanies::sharedScopeClause('ticket', JSSTcapability::actor()) : '';
            if ($jsst_shared !== '') {
                $jsst_owner = '(' . $jsst_owner . ' OR ' . $jsst_shared . ')';
            }
            jssupportticket::$jsst_data['company_supervisor'] = ($jsst_shared !== '');
            // Pagination
            $jsst_query = "SELECT COUNT(ticket.id)
                        FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                        LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                        JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                        WHERE " . $jsst_owner;
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
            $jsst_query .= " WHERE " . $jsst_owner . $jsst_inquery;
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
                        . "WHERE " . $jsst_owner . " AND (ticket.status != 5 AND ticket.status != 6)";
                jssupportticket::$jsst_data['count']['openticket'] = jssupportticket::$_db->get_var($jsst_query);

                $jsst_query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE " . $jsst_owner . " AND ticket.status = 4 ";
                jssupportticket::$jsst_data['count']['answeredticket'] = jssupportticket::$_db->get_var($jsst_query);

                $jsst_query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE " . $jsst_owner . " AND (ticket.status = 5 OR ticket.status = 6)";
                jssupportticket::$jsst_data['count']['closedticket'] = jssupportticket::$_db->get_var($jsst_query);

                $jsst_query = "SELECT COUNT(ticket.id) "
                        . "FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id "
                        . "LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id "
                        . "WHERE " . $jsst_owner;
                jssupportticket::$jsst_data['count']['allticket'] = jssupportticket::$_db->get_var($jsst_query);
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
        /* The tab clauses come from JSSTqueue, exactly as the backend queue's
           do. They used to be the switch below - five clauses, numbered 1 to 5
           in this function's own order, where 2 was Closed and 3 was In
           Progress. The rest of the plugin numbers the same tabs differently,
           and the number is stored in a search state the two desks share, so
           the same stored value picked a different queue depending on which one
           read it. Normalising here and taking the clause from JSSTqueue means
           there is one definition of every tab and no numbering of our own.
           (Roadmap 4.5-UX-01) */
        $jsst_list = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['list'] : JSSTqueue::LIST_OPEN;
        $jsst_list = JSSTqueue::normalizeList($jsst_list);
        jssupportticket::$jsst_data['list'] = $jsst_list;
        $jsst_inquery .= JSSTqueue::listClause($jsst_list);

        $jsst_uid = JSSTincluder::getObjectClass('user')->uid();
        if ($jsst_uid == 0 || !is_numeric($jsst_uid))
            return false;
        /* Which tickets this agent may see, asked of the capability service
           instead of worked out again here. The rule this replaces was
           "assigned to me, or in one of my departments", written out as a
           subquery; the service's rule is that plus "or I raised it", which is
           the same rule the ticket page itself already enforces. The gap
           between the two was a real one: an agent who raised a ticket could
           open it by typing its number and could not find it in their own
           queue. (Roadmap 4.5-ARCH-02, 4.5-UX-01) */
        $jsst_actor = JSSTcapability::actor();
        $jsst_scopeclause = JSSTcapability::ticketScopeClause('ticket', $jsst_actor);
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
        // The team queue, the same filter the backend queue uses.
        // (Roadmap 4.5-FE-05)
        $jsst_teamid = isset(jssupportticket::$_search['ticket']['teamid']) ? jssupportticket::$_search['ticket']['teamid'] : '';
        jssupportticket::$jsst_data['filter']['teamid'] = $jsst_teamid;
        $jsst_inquery .= $this->teamFilterClause($jsst_teamid);
        // Which saved view produced this state, as on the backend queue, so the
        // view picker keeps its choice and the template can offer Delete view.
        // (Roadmap 4.5-UX-01)
        jssupportticket::$jsst_data['filter']['viewid'] = isset(jssupportticket::$_search['ticket']['viewid']) ? jssupportticket::$_search['ticket']['viewid'] : '';

        // Pagination
        $jsst_query = "SELECT COUNT(DISTINCT ticket.id)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS ticket
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` AS department ON ticket.departmentid = department.id
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_priorities` AS priority ON ticket.priorityid = priority.id
                    JOIN `" . jssupportticket::$_db->prefix . "js_ticket_statuses` AS status ON ticket.status = status.id
                    " . $jsst_tagfilter['join'] . "
                    WHERE 1 = 1 " . $jsst_scopeclause;
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
                    WHERE 1 = 1 " . $jsst_scopeclause . $jsst_inquery . $jsst_userquery . $jsst_tagwhere;
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
        /* The tab numbers, in one cached pass instead of five scans.
           This used to be five COUNT queries, each joining departments and
           priorities that none of them filtered on, run on every page of the
           queue and every tab click - five scans of the ticket table to draw a
           row of numbers, and slower exactly as a help desk succeeds. The
           engine counts every tab in one pass and keeps the answer until
           something actually happens to a ticket.

           The keys are the ones JSSTqueue names, which is what the tab row
           reads now. 'overdue' is kept alongside 'overdueticket' because this
           screen has published that spelling since long before there was a
           canonical one, and a template or add-on still reading it should not
           start showing a blank. (Roadmap 4.5-UX-01, 4.0-PERF-01) */
        $jsst_countfilters = array();
        if (is_numeric($jsst_uid) && $jsst_uid > 0) {
            /* Counted under the same customer filter the list is under, or the
               tabs would advertise numbers the list below cannot show. */
            $jsst_countfilters['uid'] = (int) $jsst_uid;
        }
        $jsst_counts = class_exists('JSSTqueueengine')
            ? JSSTqueueengine::counts(array('actor' => $jsst_actor, 'filters' => $jsst_countfilters))
            : array();
        foreach (array_keys(JSSTqueue::countClauses()) AS $jsst_countalias) {
            jssupportticket::$jsst_data['count'][$jsst_countalias] = isset($jsst_counts[$jsst_countalias])
                ? (int) $jsst_counts[$jsst_countalias] : 0;
        }
        jssupportticket::$jsst_data['count']['overdue'] = jssupportticket::$jsst_data['count']['overdueticket'];
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
            elseif (!JSSTincluder::getObjectClass('user')->isguest()) {
                jssupportticket::$jsst_data['permission_granted'] = $this->validateTicketDetailForUser($jsst_id);
                /* A company supervisor reading a colleague's ticket: shown, but
                   read-only - the template hides reply, close and reopen, and
                   the actions themselves still check the owner. */
                if (!jssupportticket::$jsst_data['permission_granted'] && $this->supervisorReadsTicket($jsst_id)) {
                    unset(jssupportticket::$jsst_data['error_message']);
                    jssupportticket::$jsst_data['permission_granted'] = true;
                    jssupportticket::$jsst_data['company_readonly'] = true;
                }
            }
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

        /* The last word on capacity, and the only one that is not a number in
           the settings table: quotas by plan, product, customer or company,
           with periods, warnings and per-customer overrides. Core asks and does
           not answer - a desk with nothing listening is validated exactly as it
           was. A listener refuses by returning the sentence the customer should
           be shown, so the reason a ticket was turned away is written once, by
           whoever knows it. (Roadmap 5.5-COM-05) */
        $jsst_allowed = apply_filters('jsst_ticket_allowed', true, $jsst_emailaddress, array(
            'productid'   => (int) JSSTrequest::getVar('productid', 'post', 0),
            'departmentid'=> (int) JSSTrequest::getVar('departmentid', 'post', 0),
            'helptopicid' => (int) JSSTrequest::getVar('helptopicid', 'post', 0),
        ));
        if ($jsst_allowed !== true) {
            JSSTmessage::setMessage(
                is_string($jsst_allowed) && $jsst_allowed !== ''
                    ? esc_html($jsst_allowed)
                    : esc_html(__('This account cannot open another ticket at the moment.', 'js-support-ticket')),
                'error');
            return false;
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

        /* Duplicate submissions are caught just before the row is written, by
           JSSTsubmitguard - see there. The old check that stood here (same
           email and subject within 15 seconds) missed a retry after 15 seconds
           and two requests arriving together, and refused an EDIT made in the
           first 15 seconds as a "duplicate". checkIsTicketDuplicate() stays for
           anything outside that still calls it. */
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

        /* The purchase a customer picked, linked to the ticket if it is really
           theirs. Whether they may open the ticket at all is not decided here:
           that is the support credits check on jsst_ticket_allowed above, which
           the Paid Support screen's "require" switch turns on and off - one
           gate, for guests and signed-in customers alike. */
        if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce') && !empty($jsst_data['paidsupportid'])){
            if(!JSSTincluder::getObjectClass('user')->isguest() && !is_admin() && !(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())){
                $jsst_paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(JSSTincluder::getObjectClass('user')->wpuid(),$jsst_data['paidsupportid']);
                if(empty($jsst_paidsupport)){
                    unset($jsst_paidsupport);
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
            /* SECURITY (reported 28 September 2026, CVSS 5.4): who may edit.
               This branch used to authorise nothing - the owner id above was
               read only to be written back, and the one guard was the hash
               below, which is computed from the ticket id with no secret, so
               anyone who knew an id passed it. Any signed-in Subscriber could
               rewrite any ticket: subject, message, status, and the customer's
               name, email and phone.
               Editing a ticket is TICKET_EDIT: administrators, and agents whose
               role and per-agent grants include "Edit Ticket" on a ticket in
               their scope. Customers and guests never edit a ticket (their form
               only ever creates one). Automations running asSystem() pass. */
            $jsst_may_edit = JSSTcapability::assert(JSSTcapability::TICKET_EDIT, array('ticket' => (int) $jsst_data['id']));
            if (true !== $jsst_may_edit) {
                JSSTmessage::setMessage(esc_html(__('You are not allowed to edit this ticket.', 'js-support-ticket')), 'error');
                return false;
            }
            if( !hash_equals((string) $this->generateHash($jsst_data['id']), (string) $jsst_row->hash) ){
                return false;
            }//end
            /* What identifies a ticket to its customer is never taken from a
               form: the tracking id and the token are how a guest proves who
               they are (see getTokenByEmailAndTrackingId), and an edit that
               could set them could hand the ticket to whoever chose them. */
            unset($jsst_data['ticketid'], $jsst_data['token'], $jsst_data['hash'], $jsst_data['customticketno']);
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
        /* Everything the form itself insists on, asked of the server rather
           than of the browser. Until now a required custom field was enforced
           by the page's own validator and nowhere else, so a form posted with
           JavaScript off - or by anything that is not a browser - saved with
           every one of them empty and said nothing.

           A filter rather than a call so that nothing in this model depends on
           the forms class being loaded, and so an add-on can add a rule of its
           own the same way. It is passed the whole submission after
           sanitisation, custom answers included, and returns a sentence to
           refuse with or an empty string to allow. (Roadmap 5.0-FORM-01) */
        $jsst_refusal = apply_filters('jsst_validate_ticket_form', '', $jsst_data);
        if (is_string($jsst_refusal) && $jsst_refusal !== '') {
            JSSTmessage::setMessage(esc_html($jsst_refusal), 'error');
            return false;
        }
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
        /* One ticket per submission (JSSTsubmitguard): the same person sending
           the same subject and message again within ten minutes - a double
           click, a slow upload resent, a refreshed confirmation page - gets
           the ticket the first request created, not a second one. New tickets
           only; an edit is never a duplicate. */
        $jsst_guardkey = '';
        if ($jsst_isedit != true) {
            include_once JSST_PLUGIN_PATH . 'includes/classes/submitguard.php';
            $jsst_guardkey = JSSTsubmitguard::key('ticket', array(
                (int) $jsst_data['uid'],
                isset($jsst_data['email']) ? $jsst_data['email'] : '',
                $jsst_data['subject'],
                isset($jsst_data['message']) ? $jsst_data['message'] : '',
            ));
            $jsst_claim = JSSTsubmitguard::claim($jsst_guardkey);
            if (true !== $jsst_claim) {
                if ((int) $jsst_claim > 0) {
                    JSSTmessage::setMessage(esc_html(__('We already received this ticket, so it was not created twice.', 'js-support-ticket')), 'updated');
                    return (int) $jsst_claim;
                }
                JSSTmessage::setMessage(esc_html(__('This ticket is still being submitted. Please wait a moment and check your tickets before sending it again.', 'js-support-ticket')), 'error');
                return false;
            }
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
        if ('' !== $jsst_guardkey) {
            if ($jsst_error == 1) {
                JSSTsubmitguard::release($jsst_guardkey);
            } else {
                JSSTsubmitguard::done($jsst_guardkey, (int) $jsst_row->id);
            }
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
        /* The ticket exists, so say so. (Roadmap 4.5-ARCH-03)
         *
         * Emitted here rather than in JSSTticketservice::create(), which is
         * where it used to be and where only some tickets go. The service is
         * how the REST API, webhooks and recurring tickets create one; the
         * form, e-mail piping and live chat all call this method directly, so
         * the event that everything else in the product is built on was never
         * announced for the tickets a desk actually receives.
         *
         * What that cost, before this line: SLA clocks are started by
         * JSSTsla::onEvent() on this event and by nothing else, so a ticket
         * raised on the form got no clock until somebody happened to change
         * its priority or department. Assignment (JSSTrouting) and every
         * workflow rule triggered on `ticket.created` had the same blind spot,
         * which is why the auto-assign add-on had to keep its own hook on the
         * line above rather than being a rule like everything else.
         *
         * The service reads what was emitted here rather than emitting again -
         * one ticket, one event, whichever door it came through.
         */
        if ($jsst_error != 1 && class_exists('JSSTevents')) {
            $jsst_fresh = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                'SELECT ticketid, subject, departmentid, priorityid, uid, email
                   FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d', $jsst_ticketid));
            JSSTevents::emit(JSSTevents::TICKET_CREATED, array(
                'ticket_id'    => (int) $jsst_ticketid,
                'ticketid'     => $jsst_fresh ? $jsst_fresh->ticketid : '',
                'subject'      => $jsst_fresh ? $jsst_fresh->subject : '',
                'departmentid' => $jsst_fresh ? (int) $jsst_fresh->departmentid : 0,
                'priorityid'   => $jsst_fresh ? (int) $jsst_fresh->priorityid : 0,
                'uid'          => $jsst_fresh ? (int) $jsst_fresh->uid : 0,
                'email'        => $jsst_fresh ? $jsst_fresh->email : '',
                'source'       => isset(jssupportticket::$jsst_data['jsst_create_source'])
                    ? jssupportticket::$jsst_data['jsst_create_source'] : '',
            ));
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
                /* A deleted ticket was never answered, so its support credit
                   goes back to the customer. */
                if (class_exists('JSSTsupportcredits')) {
                    JSSTsupportcredits::refundTicket($jsst_id, esc_html__('Given back: ticket deleted.', 'js-support-ticket'));
                }
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
            /* A deleted ticket was never answered, so its support credit
               goes back to the customer. */
            if (class_exists('JSSTsupportcredits')) {
                JSSTsupportcredits::refundTicket($jsst_id, esc_html__('Given back: ticket deleted.', 'js-support-ticket'));
            }
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
        /* The desk assigning on its own behalf is not a person, and these two
           checks are about a person. (Roadmap 4.5-ARCH-01)
         *
         * Both ask what the *current user* may do. On the path that matters
         * most for automatic assignment there is no current user at all: a
         * customer submits a ticket from the portal, the rule fires, and the
         * desk gives it to an agent. Asked of a guest, `canChangeTicketState()`
         * is false, so the assignment was refused and the ticket stayed
         * unassigned - silently, because the refusal is a notice nobody sees
         * on a form submission. That was as true of the Agent Auto Assign
         * add-on as of a workflow rule: both call this method.
         *
         * `asSystem()` is the capability service's answer to exactly this, and
         * the guard that let the automation get here has already been passed.
         * Only automation can be inside it: nothing sets it for a request
         * driven by a human. */
        $jsst_bysystem = class_exists('JSSTcapability') && JSSTcapability::isSystem();
        if (!$jsst_bysystem) {
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
        /* Closed, and 5 alone on purpose - this is the one place in the product
           where 6 must NOT be treated as closed, so a sweep that adds it here
           for consistency is making it wrong. 6 is Close Due To Merge: that
           ticket was not resolved, it was folded into another one that is
           probably still open, and asking its customer how satisfied they are
           with the outcome asks about an outcome that has not happened. They
           get the survey for the ticket it was merged into, when that one
           closes. (Roadmap 5.0-ANA-05) */
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

    /**
     * What a visitor's token says, or false if it says nothing we can read.
     *
     * A token arrives from a link in an e-mail, which means it also arrives
     * mistyped, truncated, line-wrapped by a mail client, or years old. For any
     * of those decrypt() answers null or an empty string, json_decode() of that
     * is null, and reading a key straight off it warned three times and then
     * looked a ticket up with two empty strings. Both callers want the same
     * thing from a token - a decoded payload or a clean no - so they ask here
     * rather than each deciding for itself.
     */
    private function readVisitorToken($jsst_token) {
        if (!is_string($jsst_token) || $jsst_token === '') {
            return false;
        }
        include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
        $jsst_encoder = new JSSTEncoder();
        $jsst_decryptedtext = $jsst_encoder->decrypt($jsst_token);
        if (!is_string($jsst_decryptedtext) || $jsst_decryptedtext === '') {
            return false;
        }
        $jsst_array = json_decode($jsst_decryptedtext, true);
        return is_array($jsst_array) ? $jsst_array : false;
    }

    function getTicketidForVisitor($jsst_token) {

        $jsst_array = $this->readVisitorToken($jsst_token);
        if ($jsst_array === false) {
            return false;
        }
        $jsst_emailaddress = isset($jsst_array['emailaddress']) ? $jsst_array['emailaddress'] : '';
        $jsst_trackingid = isset($jsst_array['trackingid']) ? $jsst_array['trackingid'] : '';
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
        $jsst_array = $this->readVisitorToken($jsst_token);
        if ($jsst_array === false) {
            return false;
        }
        $jsst_token = isset($jsst_array['token']) ? $jsst_array['token'] : '';
        if (isset($jsst_array['sitelink']) && $jsst_array['sitelink'] != '') {
            include_once JSST_PLUGIN_PATH . 'includes/encoder.php';
            $jsst_encoder = new JSSTEncoder();
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

    /**
     * Is this ticket outside what the signed-in agent may $jsst_action?
     *
     * Only answers for an agent a visibility rule governs (Agents & Teams:
     * the agent's role or their own "Who can see what" record). For them the
     * capability service decides, the same one that builds their queue, so a
     * ticket missing from the queue cannot be opened, answered or noted by
     * typing its id into the address bar. Everybody else gets false and keeps
     * the checks they had.
     */
    function isOutOfScopeForAgent($jsst_ticketid, $jsst_action = 'ticket.view') {
        if (!class_exists('JSSTcapability') || !class_exists('JSSTvisibility') || !is_numeric($jsst_ticketid)) {
            return false;
        }
        if (!JSSTvisibility::governs(JSSTcapability::actor())) {
            return false;
        }
        return !JSSTcapability::can($jsst_action, array('ticket' => (int) $jsst_ticketid));
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
        /* An agent whose visibility is narrowed ("only tickets assigned to
           them", "their team's") is answered by that rule, not by the
           department check below, which would otherwise open every ticket in
           their departments. */
        if (class_exists('JSSTvisibility') && class_exists('JSSTcapability')
                && JSSTvisibility::governs(JSSTcapability::actor())) {
            return !$this->isOutOfScopeForAgent($jsst_ticketid, JSSTcapability::TICKET_VIEW);
        }
        /* A ticket somebody deliberately put this agent on. (Roadmap 4.5-FE-08)

           This is the THIRD gate that has to know the same fact, and the one
           that actually decides the wp-admin ticket page: the queue clause and
           JSSTcapability's per-ticket check are the other two. Observed with
           all three out of step - agent04 was notified they had been asked to
           work a ticket, the link opened this page, and this hand-written
           check (All Tickets, then the department ACL, then the assignment)
           had never heard of collaborators and answered "No Record Found".

           Asked through JSSTcollab so the answer is the same one the other two
           give; `isInvited()` counts only `source = manual`, so an automatic
           follow can still never become a grant. */
        if (class_exists('JSSTcollab')) {
            $jsst_actor = JSSTcapability::actor();
            if (!empty($jsst_actor['staffid'])
                    && JSSTcollab::isInvited($jsst_ticketid, $jsst_actor['staffid'])) {
                return true;
            }
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

    /**
     * May this signed-in customer *read* the ticket? Their own, or - for a
     * company supervisor - a colleague's. Only for read paths: replying,
     * closing and reopening keep using validateTicketDetailForUser().
     * (Roadmap 5.5-COM-06)
     */
    function validateTicketReadForUser($jsst_id) {
        if ($this->validateTicketDetailForUser($jsst_id)) {
            return true;
        }
        if ($this->supervisorReadsTicket($jsst_id)) {
            unset(jssupportticket::$jsst_data['error_message']);
            return true;
        }
        return false;
    }

    /** Is the current user reading this ticket as their company's supervisor? */
    function supervisorReadsTicket($jsst_id) {
        if (!is_numeric($jsst_id) || !class_exists('JSSTcompanies')) {
            return false;
        }
        $jsst_actor = JSSTcapability::actor();
        if (empty($jsst_actor['email'])) {
            return false;
        }
        $jsst_email = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT email FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d", $jsst_id));
        return ($jsst_email !== null && JSSTcompanies::supervisorReads($jsst_actor['email'], $jsst_email));
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
                /* Both closed statuses. This asked about 5 alone, so a ticket
                   closed by a merge answered "not closed yet" and was closed
                   again - which overwrites status 6 with 5, losing the record
                   that it was closed by a merge rather than by a person, and
                   sends the customer a second closing e-mail for a ticket that
                   closed when it was merged. (Roadmap 5.0-ANA-05) */
                $jsst_result = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d AND (status = 5 OR status = 6)', $jsst_id));
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

    /**
     * Last month's totals for the dashboard. Closed is 5 and 6 here too, so a
     * ticket closed by a merge is not also counted as answered, overdue and
     * pending. (Roadmap 5.0-ANA-05)
     */
    function getTotalStatsForDashboard(){
        $jsst_curdate = date_i18n('Y-m-d');
        $jsst_fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));

        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE status = 1 AND (lastreply IS NULL OR lastreply = '0000-00-00 00:00:00') AND date(created) >= %s AND date(created) <= %s";
        $jsst_result['open'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_fromdate, $jsst_curdate));
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered = 1 AND status != 5 AND status != 6 AND status != 1 AND date(created) >= %s AND date(created) <= %s";
        $jsst_result['answered'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_fromdate, $jsst_curdate));
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isoverdue = 1 AND status != 5 AND status != 6 AND date(created) >= %s AND date(created) <= %s";
        $jsst_result['overdue'] = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_fromdate, $jsst_curdate));
        $jsst_query = "SELECT COUNT(id) FROM `".jssupportticket::$_db->prefix."js_ticket_tickets` WHERE isanswered != 1 AND status != 5 AND status != 6 AND (lastreply IS NOT NULL AND lastreply != '0000-00-00 00:00:00') AND date(created) >= %s AND date(created) <= %s";
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
        $jsst_search_array['teamid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('teamid' , '')); // Roadmap 4.5-FE-05
        $jsst_search_array['companyid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('companyid' , '')); // Roadmap 5.5-COM-06
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

        /* A saved view replaces the filters rather than adding to them, and
           the rule for how is on JSSTqueue so that both desks apply the same
           one. It used to be written out here, inside one of the two functions
           that build a search state - which is why the front-end queue had no
           saved views at all. (Roadmap 4.0-CORE-18, 4.5-UX-01) */
        $jsst_search_array = JSSTqueue::applyView($jsst_search_array);
        return $jsst_search_array;
    }

    function getFrontSideTicketSearchFormData($jsst_search_userfields){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'my-ticket') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
        /* The team queue posts a bare "teamid" from both desks. The front-end
           form prefixes most of its fields with jsst- and this one is not,
           because the scope descriptors name one field for both shells and a
           descriptor that had to know which desk it was on would be back to
           two implementations. (Roadmap 4.5-FE-05) */
        $jsst_search_array['teamid'] = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('teamid' , ''));
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
        /* The same saved-view rule the backend queue uses, from the same
           place. (Roadmap 4.5-UX-01) */
        $jsst_search_array = JSSTqueue::applyView($jsst_search_array);
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
            $jsst_search_array['teamid'] = isset($jsst_ticket_search_cookie_data['teamid']) ? $jsst_ticket_search_cookie_data['teamid'] : null;
            $jsst_search_array['companyid'] = isset($jsst_ticket_search_cookie_data['companyid']) ? $jsst_ticket_search_cookie_data['companyid'] : null;
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

        /* An inbox named in the address, applied here rather than in one of the
           three functions that build a search state, so a link works whether
           the state came from the form, from the cookie or from nothing at all.
           This is what makes the desk home's "see the rest of them" links land
           on the queue they promise. (Roadmap 4.5-FE-02) */
        $jsst_search_array = JSSTqueue::applyScope($jsst_search_array);

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
        jssupportticket::$_search['ticket']['teamid'] = isset($jsst_search_array['teamid']) ? $jsst_search_array['teamid'] : null;
        jssupportticket::$_search['ticket']['companyid'] = isset($jsst_search_array['companyid']) ? $jsst_search_array['companyid'] : null;
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
     *
     * That last sentence was not true until 28 September 2026. saveticket()
     * checked the nonce against the id in the QUERY STRING and edited the id in
     * the POST body, so this nonce plus "?id=" edited any ticket (security
     * report, CVSS 5.4). It now holds for two independent reasons: the nonce is
     * checked against the id actually written, so an edit needs
     * 'save-ticket-<id>', which only the edit form of a permitted user is given;
     * and storeTickets() refuses an edit to anyone without TICKET_EDIT whatever
     * nonce they hold. Staying on nopriv is safe on that basis - guests on a
     * cached page need it to create a ticket.
     */
    function refreshTicketFormNonce(){
        return wp_create_nonce('save-ticket-');
    }

    function isFieldRequired(){
        $jsst_field = JSSTrequest::getVar('field');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'is-field-required-'.$jsst_field) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }

        /* The on-site lane's door on the ajax side. (Roadmap 4.0-AI-04) The
           templates already hide these controls when the lane is shut, but an
           endpoint that is only guarded by the markup that calls it is not
           guarded - the URL survives in a bookmark, a stale tab and anybody's
           browser history. */
        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::allows(JSSTaipolicy::LANE_ONSITE)) {
            wp_send_json_error(array('message' => esc_html(__('Suggestions from past replies are switched off for this site.', 'js-support-ticket'))));
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

        /* What the agent marked, and what they told it to leave alone.
           (Roadmap 6.0-AI-01)
         *
         * Every reply and every ticket carries `aireplymode` - 0 default, 1
         * enable, 2 disable - set from the segmented control on the ticket
         * screen. Until this was ported the control saved and nothing read it:
         * the filter this screen sends ("All Tickets" / "Enable Tickets") was
         * posted and dropped, and a reply an agent had explicitly disabled was
         * still offered as a suggestion. The clauses below are the add-on's
         * own, brought across when AI Powered Reply became part of free core -
         * the search moved and this did not. */
        $jsst_filter = sanitize_text_field((string) JSSTrequest::getVar('filter'));
        $jsst_modeclause = ($jsst_filter === 'marked')
            ? ' AND t.aireplymode = 1 '
            : ' AND (t.aireplymode != 2 OR t.aireplymode IS NULL) ';

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
                    3 * " . self::finiteMatch('t.subject') . " AS subject_score,
                    1 * " . self::finiteMatch('t.message') . " AS message_score,
                    t.subject LIKE %s AS is_exact_subject_match,
                    t.message LIKE %s AS is_exact_message_match
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` t
                WHERE t.id != %d
                " . $jsst_modeclause . $jsst_agentquery . "
            ) AS t_scores
            HAVING total_relevance > %f
            ORDER BY total_relevance DESC LIMIT 50";
        $jsst_query = jssupportticket::$_db->prepare($jsst_query,
            $jsst_subject, $jsst_subject, $jsst_subject,    // finiteMatch() uses its term three times
            $jsst_message, $jsst_message, $jsst_message,
            '%'.$jsst_subject.'%', '%'.$jsst_message.'%', $jsst_id, $jsst_min_relevance);

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

    /**
     * The answers themselves, ranked, in one step. (Roadmap 6.0-AI-01)
     *
     * What an agent wants from this panel is a sentence they can send. What it
     * asked them for was a ticket: pick one from a list of references and
     * subjects, wait, read its replies, then append one - two panels, two
     * filters, two Close buttons and a back-navigation between them, to arrive
     * at the thing they were after all along. A reference number tells nobody
     * anything, so the choosing step was a guess followed by a retreat.
     *
     * So this returns the replies. The ticket each one came from travels with
     * it as context - subject, reference, how close the match was - which is
     * the part of the old first panel that was actually worth reading, and it
     * arrives already attached to an answer rather than in place of one.
     *
     * The matching is `checkAIReplyTicketsBySubject()`, unchanged and still
     * used on its own: this is the same search with the second step folded in,
     * not a second search that could disagree with the first.
     */
    function getAiSuggestedReplies() {
        /* The whole corpus, not just old tickets. (Roadmap 6.0-AI-01, 6.0-AI-02)
         *
         * This panel used to search `js_ticket_tickets` and nothing else, so an
         * agent got suggestions from past conversations while the automatic
         * answer on the same desk was drawing on the knowledge base, the FAQs,
         * the canned responses, the site's pages and the crawled
         * documentation. The two disagreed by construction: the article that
         * answers the question was invisible to the person typing, and visible
         * to the robot.
         *
         * So it asks the same retriever the AI Agent asks, with the same
         * profile, which means the same governance comes with it - only
         * approved sources, only citable articles, the language rules, and the
         * per-document exclusions from Knowledge Sources. A suggestion here is
         * now something the desk would have been willing to say on its own.
         *
         * Nothing leaves the site: retrieval is a search of your own content.
         * The engine is only involved when somebody asks it to write.
         *
         * The old ticket search stays as the fallback for a desk with no AI
         * Agent installed - it is free-core behaviour and must not vanish
         * because a paid add-on is absent. */
        if (class_exists('JSSTaiagentretriever')
                && (!class_exists('JSSTaipolicy') || JSSTaipolicy::allows(JSSTaipolicy::LANE_ONSITE))) {
            $jsst_fromcorpus = $this->aiSuggestionsFromCorpus();
            if ($jsst_fromcorpus !== null) {
                return $jsst_fromcorpus;
            }
        }

        $jsst_matches = json_decode((string) $this->checkAIReplyTicketsBySubject(), true);
        if (!is_array($jsst_matches) || empty($jsst_matches)) {
            return json_encode(array());
        }

        /* The best few, not everything the search would allow. Ten answers is a
           reading task; three or four is a choice. */
        $jsst_matches = array_slice($jsst_matches, 0, 5);
        $jsst_byid = array();
        foreach ($jsst_matches as $jsst_match) {
            $jsst_byid[(int) $jsst_match['id']] = $jsst_match;
        }
        $jsst_ids = implode(',', array_map('absint', array_keys($jsst_byid)));

        /* Agents only, and never a reply somebody switched off. The uid list is
           the same one the single-ticket fetch uses, so the two cannot offer
           different sets. */
        $jsst_uids = JSSTincluder::getJSModel('reply')->get_allowed_support_user_ids();
        if (empty($jsst_uids)) {
            return json_encode(array());
        }
        $jsst_uids_str = implode(',', array_map('absint', $jsst_uids));

        $jsst_replies = jssupportticket::$_db->get_results(
            "SELECT r.id, r.ticketid, r.message, r.created
               FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` AS r
              WHERE r.ticketid IN ($jsst_ids)
                AND r.uid IN ($jsst_uids_str)
                AND (r.aireplymode != 2 OR r.aireplymode IS NULL)
              ORDER BY r.ticketid ASC, r.id DESC");

        /* One per ticket: the last thing an agent said on a matching ticket is
           the answer that closed it, and the four before it are the working
           out. */
        $jsst_seen = array();
        $jsst_out = array();
        foreach ((array) $jsst_replies as $jsst_reply) {
            $jsst_tid = (int) $jsst_reply->ticketid;
            if (isset($jsst_seen[$jsst_tid]) || !isset($jsst_byid[$jsst_tid])) {
                continue;
            }
            $jsst_seen[$jsst_tid] = true;
            $jsst_text = wp_strip_all_tags((string) $jsst_reply->message);
            if (jssupportticketphplib::JSST_trim($jsst_text) === '') {
                continue;
            }
            $jsst_out[] = array(
                'id'        => (int) $jsst_reply->id,
                'text'      => $jsst_reply->message,
                'plain'     => $jsst_text,
                'created'   => $jsst_reply->created,
                'ticket_id' => $jsst_tid,
                'reference' => $jsst_byid[$jsst_tid]['ticketid'],
                'subject'   => $jsst_byid[$jsst_tid]['text'],
                'source'    => esc_html(__('An earlier ticket', 'js-support-ticket')),
                'from'      => 'tickets',
                'url'       => '',
                'relevance' => $jsst_byid[$jsst_tid]['relevance'],
            );
        }
        return json_encode($jsst_out);
    }

    /**
     * Suggestions drawn from every source the AI Agent is allowed to read.
     *
     * Returns encoded JSON, or null when the retriever had nothing to say and
     * the caller should fall back to the ticket search - "no engine" and "no
     * answer" are different, and only the first is a reason to try the older
     * path.
     */
    private function aiSuggestionsFromCorpus() {
        $jsst_id = absint(JSSTrequest::getVar('ticketId'));
        $jsst_subject = sanitize_text_field(JSSTrequest::getVar('ticketSubject'));
        $jsst_ticket = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT subject, message, departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
            $jsst_id));
        if (!$jsst_ticket) {
            return null;
        }
        /* Subject and message together: the subject is what the customer
           called it and the message is what they actually asked, and a
           retriever given only the first is answering a headline. */
        $jsst_text = jssupportticketphplib::JSST_trim(
            ($jsst_subject !== '' ? $jsst_subject : (string) $jsst_ticket->subject)
            . "\n" . wp_strip_all_tags((string) $jsst_ticket->message));
        if ($jsst_text === '') {
            return null;
        }

        $jsst_retriever = new JSSTaiagentretriever();
        $jsst_snippets = $jsst_retriever->retrieve($jsst_text, array(
            'profile'  => JSSTaiagentretriever::PROFILE_REPLY,
            'is_guest' => false,
            /* The ticket's department, which narrows the canned responses in
               the corpus to the ones the picker beside this panel is already
               offering. Without it the two controls in one row answered the
               same desk differently - the select hid another department's
               replies and the AI Agent handed them straight back. A ticket
               with no department set narrows nothing. (Roadmap 4.0-CORE-03) */
            'department' => isset($jsst_ticket->departmentid) ? $jsst_ticket->departmentid : 0,
        ));
        if (empty($jsst_snippets)) {
            return null;
        }

        $jsst_labels = array();
        if (class_exists('JSSTaisources')) {
            foreach (JSSTaisources::types() as $jsst_key => $jsst_def) {
                $jsst_labels[$jsst_key] = $jsst_def['label'];
            }
        }

        $jsst_out = array();
        foreach ($jsst_snippets as $jsst_snippet) {
            $jsst_passage = isset($jsst_snippet['passage']) ? (string) $jsst_snippet['passage'] : '';
            if (jssupportticketphplib::JSST_trim($jsst_passage) === '') {
                continue;
            }
            $jsst_type = isset($jsst_snippet['source_type']) ? (string) $jsst_snippet['source_type'] : '';
            /* The name a person would use, never the key. `source_type` is
               `kb`, `faq`, `canned`; `ref` is `KB-1`, a citation id the audit
               trail needs and an agent has no use for. Showing either in front
               of somebody choosing a sentence is showing them the plumbing. */
            $jsst_label = isset($jsst_labels[$jsst_type]) ? $jsst_labels[$jsst_type] : '';
            if ($jsst_label === '') {
                $jsst_label = esc_html(__('Your content', 'js-support-ticket'));
            }
            $jsst_out[] = array(
                'id'        => isset($jsst_snippet['source_id']) ? $jsst_snippet['source_id'] : 0,
                'text'      => $jsst_passage,
                'plain'     => $jsst_passage,
                'created'   => '',
                'ticket_id' => 0,
                'reference' => '',
                'subject'   => isset($jsst_snippet['title']) ? $jsst_snippet['title'] : '',
                'source'    => $jsst_label,
                'from'      => 'corpus',
                'url'       => isset($jsst_snippet['url']) ? $jsst_snippet['url'] : '',
                'relevance' => isset($jsst_snippet['score']) ? $jsst_snippet['score'] : 0,
            );
        }
        return empty($jsst_out) ? null : json_encode($jsst_out);
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
        if (!wp_verify_nonce($jsst_nonce, 'get-aiagent-search')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }

        if (isset(jssupportticket::$_config['aiagent_enable'])
            && jssupportticket::$_config['aiagent_enable'] != 1) {
            echo wp_json_encode(array());
            wp_die();
        }

        $jsst_subject = sanitize_text_field(JSSTrequest::getVar('subject'));
        $jsst_message = sanitize_textarea_field(JSSTrequest::getVar('message'));

        $jsst_text = trim(preg_replace('/\s+/', ' ', $jsst_subject . ' ' . wp_strip_all_tags($jsst_message)));

        /* The search runs on the subject and the start of the message, not on
           everything: somebody who pastes a log or a long email would otherwise
           send thousands of words into every FULLTEXT query, and the first few
           sentences are what say what the problem is. Cut at a word boundary
           so the last word is not half of one. */
        $jsst_cap = 300;
        if (mb_strlen($jsst_text) > $jsst_cap) {
            $jsst_text = mb_substr($jsst_text, 0, $jsst_cap);
            $jsst_space = strrpos($jsst_text, ' ');
            if ($jsst_space !== false) $jsst_text = substr($jsst_text, 0, $jsst_space);
        }

        $jsst_min = isset(jssupportticket::$_config['aiagent_min_chars'])
            ? intval(jssupportticket::$_config['aiagent_min_chars']) : 15;

        if (jssupportticketphplib::JSST_strlen($jsst_text) < $jsst_min) {
            echo wp_json_encode(array());
            wp_die();
        }

        // Retrieval is a database query; the written AI answer is a charged
        // generation. The form asks for the answer only once the customer stops
        // typing, so a request made mid-sentence must not pay for one.
        $jsst_summary = (intval(JSSTrequest::getVar('summary')) === 1);

        /* Kept before the budget can downgrade it below, because it is also how
           a settled question is told from a half-typed one: the form asks for
           an answer when somebody stops typing, so this being set is the signal
           that what arrived is a real question rather than three words of one.
           (Roadmap 6.0-AI-12) */
        $jsst_settled = $jsst_summary;

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
        $jsst_results = apply_filters('jsst_aiagent_search_results', null, $jsst_text, $jsst_opts);

        if (!is_array($jsst_results)) {
            $jsst_results = $this->getBasicFixSuggestions($jsst_text, $jsst_settled, $jsst_subject);
        }

        /* Canned responses are written for agents to send, not for customers
           to read, and have no page of their own to open: on the customer's
           form "Refund processed" read as if a refund had been made. They are
           listed for people who work tickets only - whichever search produced
           the list - while the AI's written answer can still draw on them where
           Knowledge Sources allows it. */
        if (is_array($jsst_results) && !current_user_can(JSSTroles::CAP_TICKETS)) {
            $jsst_results = $this->customerCannedOnly($jsst_results);
        }

        /* A question somebody finished typing that nothing could answer is the
           purest gap signal in the product - their own words, before they gave
           up and filed a ticket. Recorded only for a settled question, or the
           table would fill with prefixes of one. (Roadmap 6.0-AI-12) */
        if ($jsst_settled && empty($jsst_results) && class_exists('JSSTaigaps')) {
            JSSTaigaps::record(JSSTaigaps::KIND_SEARCH, $jsst_text);
        }

        echo wp_json_encode($jsst_results);
        wp_die();
    }

    /**
     * What a customer may see of the saved replies in a result list.
     *
     * Only the ones somebody ticked "Also suggest this to customers" on, and
     * active; the rest are written to one customer about one ticket and stay
     * agent-only. The ones kept are shown as the reply itself, with {site_name}
     * filled and every other placeholder blank - not renderPlaceholders(),
     * which fills {agent_name} from whoever is logged in, and on this form that
     * is the customer - and more of the text than the 200-character excerpt,
     * because a saved reply has no page of its own to open.
     *
     * The add-on returns arrays keyed 'type'; core's own search returns
     * objects carrying 'content_type'. Both are read.
     */
    private function customerCannedOnly($jsst_results) {
        $jsst_typeof = function ($jsst_item) {
            $jsst_item = (array) $jsst_item;
            return isset($jsst_item['type']) ? $jsst_item['type']
                : (isset($jsst_item['content_type']) ? $jsst_item['content_type'] : '');
        };

        $jsst_ids = array();
        foreach ($jsst_results as $jsst_item) {
            if ($jsst_typeof($jsst_item) === 'canned') $jsst_ids[] = (int) ((array) $jsst_item)['id'];
        }
        $jsst_allowed = (!empty($jsst_ids) && class_exists('JSSTcannedresponsesModel'))
            ? JSSTcannedresponsesModel::customerSuggestable($jsst_ids) : array();

        $jsst_out = array();
        foreach ($jsst_results as $jsst_item) {
            if ($jsst_typeof($jsst_item) !== 'canned') { $jsst_out[] = $jsst_item; continue; }
            $jsst_id = (int) ((array) $jsst_item)['id'];
            if (!isset($jsst_allowed[$jsst_id])) continue;

            $jsst_text = str_replace('{site_name}', get_bloginfo('name'), $jsst_allowed[$jsst_id]);
            $jsst_text = preg_replace('/\{[a-z0-9_]+\}/i', '', $jsst_text);
            $jsst_text = trim(preg_replace('/\s+([,.!?])/', '$1', preg_replace('/\s+/', ' ', wp_strip_all_tags($jsst_text))));
            $jsst_text = wp_html_excerpt($jsst_text, 600, '…');
            if (is_array($jsst_item)) {
                $jsst_item['excerpt'] = $jsst_text;
                unset($jsst_item['matched']);
            } else {
                $jsst_item->excerpt = $jsst_text;
                unset($jsst_item->matched);
            }
            $jsst_out[] = $jsst_item;
        }
        return $jsst_out;
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

        $jsst_limit  = intval(apply_filters('jsst_aiagent_rate_limit', $jsst_limit, $jsst_kind));
        $jsst_window = intval(apply_filters('jsst_aiagent_rate_window', $jsst_window, $jsst_kind));

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

    /** Option listing the sources whose table and indexes the search can use. */
    const SUGGESTION_READY_OPTION = 'jsst_ir_sources';

    /** Each FULLTEXT index on a table, as index name => its column list. */
    private function fulltextIndexes($jsst_table) {
        $jsst_rows = jssupportticket::$_db->get_results(
            "SHOW INDEX FROM `" . $jsst_table . "` WHERE Index_type = 'FULLTEXT'"
        );
        $jsst_byname = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_byname[$jsst_row->Key_name][] = $jsst_row->Column_name;
        }
        return $jsst_byname;
    }

    /**
     * Make every suggestion source searchable, and record which ones are.
     *
     * The search needs three FULLTEXT indexes on each source table:
     *   (title, body)  finds the candidate rows. It is what lets the query read
     *                  only the rows that match instead of scoring every row in
     *                  the table on every search.
     *   (title), (body) rank those candidates, title above body. MySQL serves
     *                  MATCH(col) only from an index whose column list is
     *                  exactly that column.
     * The knowledgebase and FAQ add-ons ship the combined index but not always
     * the single ones, and core's canned responses may have neither, so any
     * that are missing are added here.
     *
     * The sources that end up with all three are stored in
     * SUGGESTION_READY_OPTION, and the search reads only that one autoloaded
     * option. It never checks tables or indexes itself: it runs on a debounce
     * while a customer types, and must neither build an index nor ask the
     * schema on every request.
     *
     * Runs from admin_init whenever the plugin version or the set of active
     * add-ons changes (jsst_repair_suggestion_indexes()), because that is when a
     * source table can appear, disappear, or arrive without its indexes.
     * Failures are logged and leave that source out of the stored list.
     *
     * @return array Table name => list of indexes created, for the caller's log.
     */
    public function repairSuggestionIndexes() {
        // Canned responses are core data; on a site that never had the legacy
        // add-on nothing else would have created the table yet.
        JSSTmergedaddon::ensureSchema('cannedresponses');

        $jsst_created = array();
        $jsst_ready   = array();

        foreach (self::instantResolveSources() as $jsst_key => $jsst_def) {
            $jsst_full = jssupportticket::$_db->prefix . $jsst_def['table'];
            if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . esc_sql($jsst_full) . "'") != $jsst_full) {
                continue;
            }

            // Names follow what the add-on schemas already use, so an install
            // that has them is left alone by the column check below.
            $jsst_needed = array(
                'jsst_ir_ft'               => array($jsst_def['title'], $jsst_def['body']),
                'ft_' . $jsst_def['title'] => array($jsst_def['title']),
                'ft_' . $jsst_def['body']  => array($jsst_def['body']),
            );

            $jsst_byname = $this->fulltextIndexes($jsst_full);
            foreach ($jsst_needed as $jsst_name => $jsst_cols) {
                // Present under any name counts; only the column list matters.
                if (in_array($jsst_cols, $jsst_byname, true)) continue;
                if (isset($jsst_byname[$jsst_name])) continue;   // name taken by other columns

                jssupportticket::$_db->hide_errors();
                jssupportticket::$_db->query('ALTER TABLE `' . $jsst_full . '` ADD FULLTEXT `'
                    . $jsst_name . '` (`' . implode('`, `', $jsst_cols) . '`)');
                $jsst_error = jssupportticket::$_db->last_error;
                jssupportticket::$_db->show_errors();

                // Two admin requests can race here (a page load and the
                // heartbeat); the loser is told "Duplicate key name" for an
                // index that now exists, so the table is asked again below
                // rather than trusting the error.
                if ($jsst_error == null) {
                    $jsst_created[$jsst_full][] = $jsst_name;
                }
            }

            $jsst_byname = $this->fulltextIndexes($jsst_full);
            $jsst_missing = array();
            foreach ($jsst_needed as $jsst_name => $jsst_cols) {
                if (!in_array($jsst_cols, $jsst_byname, true)) $jsst_missing[] = $jsst_name;
            }
            if (empty($jsst_missing)) {
                $jsst_ready[] = $jsst_key;
            } else {
                error_log(sprintf(
                    'JS Help Desk: could not add FULLTEXT index(es) %s on %s, suggestion source "%s" stays disabled.',
                    implode(', ', $jsst_missing), $jsst_full, $jsst_key
                ));
            }
        }

        update_option(self::SUGGESTION_READY_OPTION, $jsst_ready, true);

        return $jsst_created;
    }

    /**
     * A MATCH() relevance that is always safe to do arithmetic on.
     *
     * InnoDB can return +infinity for MATCH() in natural-language mode (seen on
     * MySQL 8.0.33 against a one-row canned responses table). The value prints
     * as 0 and even passes "> 0", but multiplying or adding it raises MySQL
     * error 1690 (DOUBLE value is out of range), which fails the whole query.
     *
     * An infinite score still means the word was found, so it counts as an
     * ordinary hit (1) instead of an overwhelming one: these scores are sorted
     * alongside other sources, and a broken statistic must not outrank every
     * real result. NULL and NaN count as no match.
     *
     * The returned SQL holds three %s placeholders, all for the same search
     * term, so prepare() must be given that term three times.
     *
     * @param string $jsst_column A column reference, already quoted as needed.
     * @return string
     */
    private static function finiteMatch($jsst_column) {
        $jsst_match = 'MATCH(' . $jsst_column . ') AGAINST (%s IN NATURAL LANGUAGE MODE)';
        return '(CASE WHEN ' . $jsst_match . ' >= 1000000 THEN 1'
            . ' WHEN ' . $jsst_match . ' > 0 THEN ' . $jsst_match
            . ' ELSE 0 END)';
    }

    /**
     * Free-tier suggestions: knowledgebase, FAQs, canned responses and site
     * content, ranked by FULLTEXT relevance with a bonus for a subject match.
     *
     * Intentionally modest. There is no grounding gate and no AI here, because
     * a suggestion the customer can ignore does not need one - anything that
     * feeds text to a model belongs in the addon, behind its gates.
     *
     * Built to be cheap enough to run while somebody types: each table source
     * is one indexed query that reads only the rows matching the text, and
     * WordPress posts - which can only be searched by scanning wp_posts - are
     * searched only once the question is settled.
     *
     * Only what it shares with the question counts. Common words ("the",
     * "help", "not working") are taken out before searching, because in
     * natural-language mode any shared word is a match and on a small site
     * nearly every article shares one. What is left must actually overlap a
     * result - one word in its title, or two in its text - and a result far
     * below the best one is dropped, so nothing is shown rather than
     * something unrelated.
     *
     * @param string $jsst_text    The search text, already capped by the caller.
     * @param bool   $jsst_settled The customer finished a field; posts are searched too.
     * @param string $jsst_subject The subject alone, which counts for more than the message.
     */
    private function getBasicFixSuggestions($jsst_text, $jsst_settled = false, $jsst_subject = '') {
        $jsst_is_guest = JSSTincluder::getObjectClass('user')->isguest();
        $jsst_results  = array();

        $jsst_terms = self::suggestionTerms($jsst_text);
        if (empty($jsst_terms)) return array();
        $jsst_query = implode(' ', $jsst_terms);
        $jsst_subjectterms = self::suggestionTerms($jsst_subject);
        $jsst_subjectquery = implode(' ', $jsst_subjectterms);
        $jsst_subjectphrase = trim(preg_replace('/\s+/', ' ', (string) $jsst_subject));

        /* Which sources may be answered from is core's register since
           6.0-AI-02, and it is asked rather than read out of the config row it
           stores itself in - the register drops a key this build cannot
           describe, which is what keeps an old value from reaching the loop
           below. (Roadmap 6.0-AI-02) */
        if (class_exists('JSSTaisources')) {
            $jsst_enabled = JSSTaisources::approvedTypes();
        } else {
            $jsst_enabled = isset(jssupportticket::$_config['aiagent_feeds'])
                ? json_decode(jssupportticket::$_config['aiagent_feeds'], true)
                : null;
            if (!is_array($jsst_enabled) || empty($jsst_enabled)) {
                $jsst_enabled = array('kb', 'faq', 'canned', 'posts');
            }
        }

        $jsst_limit = isset(jssupportticket::$_config['aiagent_max_results'])
            ? intval(jssupportticket::$_config['aiagent_max_results']) : 5;
        if ($jsst_limit < 1 || $jsst_limit > 10) $jsst_limit = 5;

        // Subject matches count for more than body matches, and an exact phrase
        // in the subject outranks everything.
        $jsst_w_title = 3;
        $jsst_w_body  = 1;
        $jsst_w_exact = 10;
        $jsst_w_subject = 4;

        // Which tables exist with the indexes this query needs is worked out by
        // repairSuggestionIndexes() on admin_init, not asked here on every
        // request. Until it has run, no table source is searched.
        $jsst_ready = (array) get_option(self::SUGGESTION_READY_OPTION, array());

        foreach (self::instantResolveSources() as $jsst_key => $jsst_def) {
            if (!in_array($jsst_key, $jsst_enabled, true)) continue;
            if (!in_array($jsst_key, $jsst_ready, true)) continue;
            // A source with no 'addon' is core's own and always available. The
            // rest go through featureEnabled(), which is the replacement for a
            // bare $_active_addons lookup: it answers yes for a capability core
            // has absorbed as well as for a legacy addon that is still active.
            if ($jsst_def['addon'] !== null && !JSSTmergedaddon::featureEnabled($jsst_def['addon'])) continue;

            $jsst_full = jssupportticket::$_db->prefix . $jsst_def['table'];

            // The combined index finds the candidates, so only rows that match
            // the text are read and scored. Without it in the WHERE, every row
            // of the table was scored on every search (EXPLAIN: type ALL).
            $jsst_where = array("MATCH(`" . $jsst_def['title'] . "`, `" . $jsst_def['body'] . "`) AGAINST (%s IN NATURAL LANGUAGE MODE)");
            if ($jsst_def['where'] !== '') $jsst_where[] = $jsst_def['where'];
            if ($jsst_is_guest && $jsst_def['guest'] !== '') $jsst_where[] = $jsst_def['guest'];

            /* Individual documents somebody excluded. Free suggestions are the
               same content shown to the same customer, so a page ruled out for
               the AI must not come back as a suggestion beside the ticket form
               - governance that only half the product honours is worse than
               none, because it reads as if it worked. (Roadmap 6.0-AI-02) */
            if (class_exists('JSSTaisources')) {
                $jsst_govern = ltrim(preg_replace('/^\s*AND\s+/i', '',
                    JSSTaisources::sqlFilter($jsst_key, 'id')));
                if ($jsst_govern !== '') $jsst_where[] = $jsst_govern;
            }

            /* The subject is what the customer chose to call the problem, so
               a title that matches it counts on top of the whole-text match.
               The exact bonus is the subject as typed, not the whole text:
               a title never contains a subject plus three paragraphs. */
            $jsst_args = array($jsst_query, $jsst_query, $jsst_query,    // finiteMatch() uses its term three times
                               $jsst_query, $jsst_query, $jsst_query);
            $jsst_subjectsql = '';
            if ($jsst_subjectquery !== '') {
                $jsst_subjectsql = " + (" . $jsst_w_subject . " * " . self::finiteMatch('`' . $jsst_def['title'] . '`') . ")";
                array_push($jsst_args, $jsst_subjectquery, $jsst_subjectquery, $jsst_subjectquery);
            }
            $jsst_exactsql = '';
            if (mb_strlen($jsst_subjectphrase) >= 4) {
                $jsst_exactsql = " + (CASE WHEN `" . $jsst_def['title'] . "` LIKE %s THEN " . $jsst_w_exact . " ELSE 0 END)";
                $jsst_args[] = '%' . jssupportticket::$_db->esc_like($jsst_subjectphrase) . '%';
            }
            $jsst_args[] = $jsst_query;                                  // the candidate MATCH in the WHERE

            $jsst_sql = "SELECT id, `" . $jsst_def['title'] . "` AS title,
                    SUBSTRING(`" . $jsst_def['body'] . "`, 1, 200) AS excerpt,
                    SUBSTRING(`" . $jsst_def['body'] . "`, 1, 4000) AS haystack,
                    ( (" . $jsst_w_title . " * " . self::finiteMatch('`' . $jsst_def['title'] . '`') . ") +
                      (" . $jsst_w_body . " * " . self::finiteMatch('`' . $jsst_def['body'] . '`') . ")"
                      . $jsst_subjectsql . $jsst_exactsql . "
                    ) AS total_relevance
                FROM `" . $jsst_full . "`"
                . (!empty($jsst_where) ? ' WHERE ' . implode(' AND ', $jsst_where) : '')
                . " HAVING total_relevance > 0 ORDER BY total_relevance DESC LIMIT 3";

            $jsst_rows = jssupportticket::$_db->get_results(
                jssupportticket::$_db->prepare($jsst_sql, $jsst_args)
            );

            /*
             * A broken source is skipped rather than allowed to take the whole
             * panel down with it - but not silently, because a failed query
             * otherwise looks exactly like "nothing matched". (The infinite
             * relevance that used to fail here with MySQL error 1690 is now
             * neutralised by finiteMatch() before any arithmetic.)
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
                /* Shares enough with the question to be about it: one real
                   word in the title, or two in the text. */
                $jsst_overlap = self::suggestionOverlap($jsst_terms, (string) $jsst_row->title, (string) $jsst_row->haystack);
                unset($jsst_row->haystack);
                if ($jsst_overlap['title'] < 1 && $jsst_overlap['all'] < min(2, count($jsst_terms))) continue;
                $jsst_row->matched = $jsst_overlap['words'];

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
        // it is not this plugin's decision to make. Without an index, WP_Query
        // search is a LIKE scan of the site's largest table, so it runs only
        // for a settled question, and on the few most specific words in titles
        // and excerpts - WP_Query requires every term to match, so the whole
        // message as terms (what this used to send) almost never matched
        // anything anyway.
        $jsst_keywords = array();
        if ($jsst_settled && in_array('posts', $jsst_enabled, true)) {
            $jsst_words = array_filter($jsst_terms, function ($jsst_word) {
                return mb_strlen($jsst_word) >= 4;
            });
            // Longer words are the more specific ones ("password" over "help").
            usort($jsst_words, function ($jsst_a, $jsst_b) {
                return mb_strlen($jsst_b) - mb_strlen($jsst_a);
            });
            $jsst_keywords = array_slice($jsst_words, 0, 3);
        }
        if (!empty($jsst_keywords)) {
            $jsst_postargs = array(
                's'                      => implode(' ', $jsst_keywords),
                'search_columns'         => array('post_title', 'post_excerpt'),
                'post_type'              => array('post', 'page'),
                'post_status'            => 'publish',
                'posts_per_page'         => 3,
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            );

            /* Site content is the source most often narrowed to a short
               approved list, because most of what a site publishes is marketing
               copy rather than documentation. An empty approved list has to be
               post__in => array(0) and not an empty array: WP_Query ignores an
               empty post__in, which would turn "nothing is approved" into
               "everything is". (Roadmap 6.0-AI-02) */
            if (class_exists('JSSTaisources')) {
                if (JSSTaisources::mode('posts') === JSSTaisources::MODE_PICK) {
                    $jsst_picked = JSSTaisources::idsWithVerdict('posts', JSSTaisources::RULE_ALLOW);
                    $jsst_postargs['post__in'] = empty($jsst_picked) ? array(0) : $jsst_picked;
                } else {
                    $jsst_barred = JSSTaisources::idsWithVerdict('posts', JSSTaisources::RULE_DENY);
                    if (!empty($jsst_barred)) $jsst_postargs['post__not_in'] = $jsst_barred;
                }
            }

            $jsst_query = new WP_Query($jsst_postargs);

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

        /* A result far below the best one is only there because it shares a
           word; the best one is what the question is about. Posts carry a
           fixed 0.5 and are kept by the word rules above instead. */
        $jsst_top = empty($jsst_results) ? 0 : (float) $jsst_results[0]->total_relevance;
        if ($jsst_top > 1) {
            $jsst_floor = $jsst_top * self::SUGGESTION_FLOOR;
            $jsst_results = array_values(array_filter($jsst_results, function ($jsst_row) use ($jsst_floor) {
                return ($jsst_row->content_type === 'posts') || ((float) $jsst_row->total_relevance >= $jsst_floor);
            }));
        }

        return array_slice($jsst_results, 0, $jsst_limit);
    }

    /** A result must score at least this share of the best one to be shown. */
    const SUGGESTION_FLOOR = 0.35;

    /**
     * Words that say nothing about which article answers a question. Shared by
     * almost every ticket and every article, so matching on them is what made
     * unrelated articles show up.
     */
    private static function suggestionStopwords() {
        return apply_filters('jsst_suggestion_stopwords', array(
            'the','and','for','are','but','not','you','your','yours','our','ours','with','this','that','these','those',
            'have','has','had','was','were','been','being','from','they','them','their','there','here','what','when',
            'where','which','who','whom','why','how','can','cant','cannot','could','would','should','will','wont',
            'does','doesnt','did','didnt','dont','done','doing','its','into','onto','about','after','before','again',
            'also','just','only','very','too','any','all','some','more','most','other','such','than','then','out',
            'off','over','under','still','yet','get','got','getting','gets','make','made','use','used','using',
            'want','wants','need','needs','needed','please','pls','thanks','thank','hello','hi','hey','dear','regards',
            'help','issue','issues','problem','problems','question','questions','ticket','support','working','work',
            'works','worked','anyone','someone','something','anything','nothing','know','try','tried','trying',
            'able','unable','since','while','way','one','two','today','now','time','day','days','im','ive','am',
        ));
    }

    /** The words of some text a search should use: lower case, 3+ letters, no stop words. */
    private static function suggestionTerms($jsst_text) {
        $jsst_words = preg_split('/[^\p{L}\p{N}]+/u', jssupportticketphplib::JSST_strtolower((string) $jsst_text), -1, PREG_SPLIT_NO_EMPTY);
        $jsst_stop = self::suggestionStopwords();
        $jsst_out = array();
        foreach ((array) $jsst_words as $jsst_word) {
            if (mb_strlen($jsst_word) < 3 || in_array($jsst_word, $jsst_stop, true)) continue;
            $jsst_out[$jsst_word] = true;
        }
        return array_slice(array_keys($jsst_out), 0, 30);
    }

    /** How many of the question's words a result contains, in its title and anywhere. */
    private static function suggestionOverlap($jsst_terms, $jsst_title, $jsst_body) {
        $jsst_title = ' ' . implode(' ', self::suggestionTerms($jsst_title)) . ' ';
        $jsst_all = $jsst_title . ' ' . implode(' ', self::suggestionTerms(wp_strip_all_tags($jsst_body))) . ' ';
        $jsst_out = array('title' => 0, 'all' => 0, 'words' => array());
        foreach ($jsst_terms as $jsst_term) {
            /* A word counts if it or its stem appears, so "exports" finds
               "export" and "password" finds "passwords". */
            $jsst_stem = (mb_strlen($jsst_term) > 4) ? preg_replace('/(ing|ed|es|s)$/u', '', $jsst_term) : $jsst_term;
            $jsst_pattern = '/ ' . preg_quote($jsst_stem, '/') . '[\p{L}]{0,3} /u';
            if (preg_match($jsst_pattern, $jsst_title)) $jsst_out['title']++;
            if (preg_match($jsst_pattern, $jsst_all)) {
                $jsst_out['all']++;
                $jsst_out['words'][] = $jsst_term;
            }
        }
        return $jsst_out;
    }

    /**
     * The free search as an administrator sees it, for the test on Knowledge
     * Sources: what a customer typing this would be shown, with the words it
     * searched on and the words each result shared.
     */
    public function previewBasicSuggestions($jsst_question) {
        $jsst_question = trim(preg_replace('/\s+/', ' ', (string) $jsst_question));
        return array(
            'terms'   => self::suggestionTerms($jsst_question),
            'results' => $this->getBasicFixSuggestions($jsst_question, true, $jsst_question),
        );
    }
    
}
?>
