<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * These classes are loaded from the plugin bootstrap with include_once. That
 * normally guarantees one declaration, but it deduplicates by resolved path, so
 * anything that reaches this file by a second spelling of the same path - or any
 * route that runs the bootstrap twice - redeclares the class and takes the whole
 * site down with a fatal. Returning early costs nothing and makes the file safe
 * to include however many times and by whatever route. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTqueue')) {
    return;
}


/**
 * Core queue capabilities. (Roadmap 4.0-CORE-18)
 *
 * Three things live here, because all three are the same question asked in
 * different ways — "which tickets am I looking at?":
 *
 *  - Keyword search over everything a ticket is made of, including the replies.
 *  - The queue tabs, including Waiting on Agent and Waiting on Customer, which
 *    split the open queue by who owes the next move.
 *  - Saved views: a named filter set an agent can come back to.
 *
 * The clauses are returned rather than applied, so the caller keeps control of
 * its own argument order and the same clause can go to both the count query and
 * the data query. Two queries that disagree about how many tickets match is the
 * failure mode this exists to prevent.
 *
 * This is a class rather than a module because the queue is shared ground: the
 * ticket model, the ticket controller and the list template all need it, and
 * none of them owns it.
 */
class JSSTqueue {

    /** Bumped when the saved-views table below changes. */
    const SCHEMA_VERSION = '450';

    /* How far a saved view reaches. Private is what every existing view is and
       what a new one is unless somebody says otherwise; shared puts it in front
       of every agent who can open the queue.

       There is deliberately no team level here. A team is a real thing with
       leads, members and a fallback, and it arrives with the teams work rather
       than being approximated now by "people who share a department with me" -
       an approximation that would have to be unpicked the moment teams exist. */
    const VIEW_PRIVATE = 0;
    const VIEW_SHARED  = 1;

    /** A view name longer than this is truncated rather than refused. */
    const MAX_NAME = 60;

    /** How many views one agent may keep. Enough for a working set, not a filing system. */
    const MAX_VIEWS = 30;

    /**
     * Where a view keeps its custom-field values, and the name the save forms
     * post them under.
     */
    const CUSTOM_KEY = 'customfields';

    /**
     * How many words a keyword search is cut down to.
     *
     * Every extra word is another OR-group over six columns, so the cost of a
     * search grows with what was typed. Anything past this is dropped rather
     * than allowed to turn a paste into a table scan per word.
     */
    const MAX_TERMS = 5;

    /* The queue tabs. 1-5 are the tabs this product has always had and their
       numbers are in saved links and in the search cookie, so they keep their
       meaning exactly. 6 and 7 are new in 4.0. */
    const LIST_OPEN              = 1;
    const LIST_ANSWERED          = 2;
    const LIST_OVERDUE           = 3;
    const LIST_CLOSED            = 4;
    const LIST_ALL               = 5;
    const LIST_WAITING_AGENT     = 6;
    const LIST_WAITING_CUSTOMER  = 7;

    /**
     * Create the saved-views table.
     *
     * Self-healing in the same way as the other 4.0 tables: called from the read
     * and write paths, guarded by an option so the check is one get_option on
     * the hot path, and never dependent on a particular upgrade route having
     * been taken.
     */
    public static function ensureSchema() {
        /* Only the table this method owns, and the one column of it the repair
           below can add. `visibility` has to be named: without it the guard
           asks no more than "does the table exist", and a saved-views table
           restored in the 4.0 layout with the option still reading 450 would
           never be repaired - which is the precise drift JSSTschemaguard was
           written for, and here it would mean every read of the table failing
           on Unknown column 'visibility'.

           The indexes this method adds to js_ticket_tickets are left out on
           purpose: that table is the largest on the site and an ALTER that
           cannot complete must not be retried on every request.
           (see JSSTschemaguard, Roadmap 4.5-UX-01) */
        if (!JSSTschemaguard::needsRun('jsst_queue_schema', self::SCHEMA_VERSION,
                array('js_ticket_saved_views' => array('visibility')))) {
            return;
        }
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_saved_views';

        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    uid int(11) NOT NULL,
                    name varchar(60) NOT NULL,
                    filters text,
                    visibility tinyint(1) NOT NULL DEFAULT 0,
                    created datetime DEFAULT NULL,
                    updated datetime DEFAULT NULL,
                    PRIMARY KEY (id),
                    KEY jsst_uid (uid),
                    KEY jsst_visibility (visibility)
                ) " . $jsst_charset);

        /* A site that already has the table from 4.0 has it without the
           visibility column, and CREATE TABLE IF NOT EXISTS will not add one.
           Checked by column name rather than by schema version, because the
           version option is the thing most likely to be wrong on a site that
           has been restored from a partial backup - and adding a column that
           is already there is the one failure this has to survive quietly.
           (Roadmap 4.5-UX-01) */
        $jsst_columns = jssupportticket::$_db->get_col('SHOW COLUMNS FROM `' . $jsst_table . '`', 0);
        if (is_array($jsst_columns) && !in_array('visibility', $jsst_columns, true)) {
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD `visibility` tinyint(1) NOT NULL DEFAULT 0');
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD INDEX `jsst_visibility` (`visibility`)');
        }

        // The queue tabs all filter on status, and the two new ones filter on
        // status together with isanswered. Added here rather than in the install
        // SQL because js_ticket_tickets predates every version that ships this
        // file and its layout differs between installs. Each index is checked
        // first, so a site that already has it is left alone.
        // (Roadmap 4.0-PERF-01)
        $jsst_tickets = jssupportticket::$_db->prefix . 'js_ticket_tickets';
        $jsst_indexes = jssupportticket::$_db->get_col('SHOW INDEX FROM `' . $jsst_tickets . '`', 2);
        if (!is_array($jsst_indexes)) {
            $jsst_indexes = array();
        }
        $jsst_wanted = array(
            'jsst_queue_state' => '(`status`, `isanswered`)',
            'jsst_ticketid'    => '(`ticketid`)',
            'jsst_email'       => '(`email`)',
        );
        foreach ($jsst_wanted as $jsst_name => $jsst_columns) {
            if (!in_array($jsst_name, $jsst_indexes, true)) {
                jssupportticket::$_db->query('ALTER TABLE `' . $jsst_tickets . '` ADD INDEX `' . $jsst_name . '` ' . $jsst_columns);
            }
        }

        update_option('jsst_queue_schema', self::SCHEMA_VERSION, false);
    }

    /**
     * The queue tabs, in the order they are shown.
     *
     * Keyed by the list number that has always identified each tab, so an old
     * bookmark or a saved cookie still lands on the tab it named.
     */
    public static function tabs() {
        $jsst_tabs = array(
            self::LIST_OPEN => array(
                'label' => esc_html(__('Open', 'js-support-ticket')),
                'title' => esc_html(__('Open Tickets', 'js-support-ticket')),
                'count' => 'openticket',
                'class' => 'js-ticket-green',
                'fill'  => 'js-ticket-open',
            ),
            self::LIST_WAITING_AGENT => array(
                'label' => esc_html(__('Waiting on Agent', 'js-support-ticket')),
                'title' => esc_html(__('Open tickets whose last word came from the customer', 'js-support-ticket')),
                'count' => 'waitingagentticket',
                'class' => 'js-ticket-purple',
                'fill'  => 'js-ticket-waitingagent',
            ),
            self::LIST_WAITING_CUSTOMER => array(
                'label' => esc_html(__('Waiting on Customer', 'js-support-ticket')),
                'title' => esc_html(__('Open tickets whose last word came from an agent', 'js-support-ticket')),
                'count' => 'waitingcustomerticket',
                'class' => 'js-ticket-teal',
                'fill'  => 'js-ticket-waitingcustomer',
            ),
            /* LIST_ANSWERED has no tab of its own. Its clause is Waiting on
               Customer's with `status != 1` added, so the two listed the same
               tickets on any site where nothing is ever reopened, and differed
               only in whether a reopened ticket showed up — two tabs, two
               counts, one queue. Waiting on Customer is the one kept: it pairs
               with Waiting on Agent and the pair covers the open queue exactly.

               The constant, its clause and its count all stay. Old links, saved
               views and the stored search still carry list 2, the front-end
               control panel and the reports still read the `answeredticket`
               count, and normalizeList() sends anything arriving on list 2 to
               the tab that replaced it. */
            self::LIST_OVERDUE => array(
                'label' => esc_html(__('Overdue', 'js-support-ticket')),
                'title' => esc_html(__('Overdue Tickets', 'js-support-ticket')),
                'count' => 'overdueticket',
                'class' => 'js-ticket-orange',
                'fill'  => 'js-ticket-overdue',
                // The overdue flag is set by the Overdue add-on; without it the
                // column is never written and the tab would always read zero.
                'addon' => 'overdue',
            ),
            self::LIST_CLOSED => array(
                'label' => esc_html(__('Closed', 'js-support-ticket')),
                'title' => esc_html(__('closed ticket', 'js-support-ticket')),
                'count' => 'closedticket',
                'class' => 'js-ticket-red',
                'fill'  => 'js-ticket-close',
            ),
            self::LIST_ALL => array(
                'label' => esc_html(__('All Tickets', 'js-support-ticket')),
                'title' => esc_html(__('All Tickets', 'js-support-ticket')),
                'count' => 'allticket',
                'class' => 'js-ticket-blue',
                'fill'  => 'js-ticket-allticket',
            ),
        );
        return apply_filters('jsst_queue_tabs', $jsst_tabs);
    }

    /**
     * Translate a stored or bookmarked list number to the tab that serves it.
     *
     * Only the retired Answered tab is remapped, and only here: list 2 lives in
     * bookmarks, in saved views and in the search cookie on every site that has
     * ever run this plugin, so it has to keep landing somewhere sensible rather
     * than falling through to All Tickets. Everything else is passed through
     * untouched — an unknown number keeps whatever listClause() already does
     * with it.
     *
     * Call this where a list number is used, never where one is stored. The
     * admin queue and the front-end queue share one search slot but number
     * their tabs differently, so rewriting the stored value would change what
     * the front end shows.
     */
    public static function normalizeList($jsst_list) {
        if ((int) $jsst_list === self::LIST_ANSWERED) {
            return self::LIST_WAITING_CUSTOMER;
        }
        return $jsst_list;
    }

    /**
     * The WHERE fragment for one queue tab.
     *
     * Waiting on Agent and Waiting on Customer read isanswered rather than a
     * status id. isanswered is already maintained on every reply — set when an
     * agent or administrator posts, cleared when the customer does — so the two
     * queues are correct on a site that has renamed, reordered or added its own
     * statuses, and correct on day one with no backfill.
     */
    public static function listClause($jsst_list) {
        $jsst_open = " AND ticket.status != 5 AND ticket.status != 6";
        switch ((int) $jsst_list) {
            case self::LIST_OPEN:
                return $jsst_open;
            case self::LIST_ANSWERED:
                return " AND ticket.isanswered = 1" . $jsst_open . " AND ticket.status != 1";
            case self::LIST_OVERDUE:
                return " AND ticket.isoverdue = 1" . $jsst_open;
            case self::LIST_CLOSED:
                return " AND (ticket.status = 5 OR ticket.status = 6) ";
            case self::LIST_WAITING_AGENT:
                return " AND ticket.isanswered = 0" . $jsst_open;
            case self::LIST_WAITING_CUSTOMER:
                return " AND ticket.isanswered = 1" . $jsst_open;
            case self::LIST_ALL:
            default:
                return '';
        }
    }

    /**
     * The count each tab shows, as an alias and the condition behind it.
     *
     * Deliberately not built from tabs(): that list is filterable, and an alias
     * from a third party would be pasted straight into a SELECT. This map is
     * fixed, and every condition comes from listClause(), so a tab and its own
     * count can never mean two different things.
     */
    public static function countClauses() {
        $jsst_clauses = array();
        $jsst_counts = array(
            'openticket'             => self::LIST_OPEN,
            'answeredticket'         => self::LIST_ANSWERED,
            'overdueticket'          => self::LIST_OVERDUE,
            'closedticket'           => self::LIST_CLOSED,
            'waitingagentticket'     => self::LIST_WAITING_AGENT,
            'waitingcustomerticket'  => self::LIST_WAITING_CUSTOMER,
            'allticket'              => self::LIST_ALL,
        );
        foreach ($jsst_counts as $jsst_alias => $jsst_list) {
            $jsst_clause = trim(self::listClause($jsst_list));
            // listClause() returns a WHERE fragment; a CASE wants a bare
            // condition. All tickets have no condition at all.
            $jsst_clause = preg_replace('/^AND\s+/i', '', $jsst_clause);
            $jsst_clauses[$jsst_alias] = ($jsst_clause === '') ? '1' : '(' . $jsst_clause . ')';
        }
        return $jsst_clauses;
    }

    /**
     * Split what an agent typed into search terms.
     *
     * Quoted runs are kept whole, so "payment failed" can be searched as a
     * phrase; everything else splits on whitespace.
     */
    public static function parseTerms($jsst_keywords) {
        $jsst_keywords = trim(wp_strip_all_tags((string) $jsst_keywords));
        if ($jsst_keywords === '') {
            return array();
        }
        $jsst_terms = array();
        if (preg_match_all('/"([^"]+)"|(\S+)/u', $jsst_keywords, $jsst_matches, PREG_SET_ORDER)) {
            foreach ($jsst_matches as $jsst_match) {
                $jsst_term = ($jsst_match[1] !== '') ? $jsst_match[1] : (isset($jsst_match[2]) ? $jsst_match[2] : '');
                $jsst_term = trim($jsst_term);
                if ($jsst_term === '') {
                    continue;
                }
                $jsst_terms[] = $jsst_term;
                if (count($jsst_terms) >= self::MAX_TERMS) {
                    break;
                }
            }
        }
        return $jsst_terms;
    }

    /**
     * The WHERE fragment and arguments for a keyword search.
     *
     * Every term must appear somewhere in the ticket, but not all in the same
     * place: "refund invoice" finds a ticket whose subject says refund and whose
     * third reply says invoice. That is what an agent means when they type two
     * words, and matching only within one column is the reason people give up on
     * a search box.
     *
     * Deliberately LIKE rather than MATCH ... AGAINST. A full-text index ignores
     * words shorter than the server's minimum token length (four characters by
     * default on MySQL, three on InnoDB), so searching a real error code or a
     * short product name silently returns nothing, and boolean mode turns a
     * stray + or - in what an agent typed into a different search than they
     * asked for. Substring matching has neither failure. The queue tabs and the
     * ordinary filters narrow the set first, and the search is offered on the
     * queue rather than across the whole database.
     *
     * Returned rather than applied so the caller controls its own argument
     * order and the identical clause reaches the count and the data query.
     */
    public static function searchFilter($jsst_keywords) {
        $jsst_empty = array('where' => '', 'args' => array());
        $jsst_terms = self::parseTerms($jsst_keywords);
        if (empty($jsst_terms)) {
            return $jsst_empty;
        }
        $jsst_replies = jssupportticket::$_db->prefix . 'js_ticket_replies';
        $jsst_where = '';
        $jsst_args = array();
        foreach ($jsst_terms as $jsst_term) {
            $jsst_like = '%' . jssupportticket::$_db->esc_like($jsst_term) . '%';
            $jsst_where .= " AND ("
                    . "ticket.ticketid LIKE %s"
                    . " OR ticket.subject LIKE %s"
                    . " OR ticket.message LIKE %s"
                    . " OR ticket.name LIKE %s"
                    . " OR ticket.email LIKE %s"
                    . " OR EXISTS (SELECT 1 FROM `" . $jsst_replies . "` AS kwreply"
                    . " WHERE kwreply.ticketid = ticket.id AND kwreply.message LIKE %s)"
                    . ")";
            // Six placeholders, one term.
            $jsst_args[] = $jsst_like;
            $jsst_args[] = $jsst_like;
            $jsst_args[] = $jsst_like;
            $jsst_args[] = $jsst_like;
            $jsst_args[] = $jsst_like;
            $jsst_args[] = $jsst_like;
        }
        return array('where' => $jsst_where, 'args' => $jsst_args);
    }

    /**
     * The view filters that name a row in another table, so a zero is "none"
     * rather than a value worth storing.
     */
    private static $jsst_idkeys = array(
        'priority', 'departmentid', 'helptopicid', 'productid', 'staffid',
        'status', 'tagid', 'orderid', 'eddorderid', 'companyid', 'teamid',
    );

    /**
     * The queue's own filter keys a saved view stores.
     *
     * Custom-field values are stored beside these, under CUSTOM_KEY, because
     * they are keyed by field name rather than by a fixed list. See
     * customFieldTypes() for how a field that has since been deleted is kept
     * out of the query.
     */
    public static function viewKeys() {
        return array(
            'list', 'keywords', 'subject', 'name', 'email', 'phone', 'ticketid',
            'datestart', 'dateend', 'priority', 'departmentid', 'helptopicid',
            'productid', 'staffid', 'status', 'tagid', 'orderid', 'eddorderid',
            'companyid', 'teamid', 'sortby',
        );
    }

    /**
     * Who owns the views on this screen.
     */
    private static function owner() {
        return (int) get_current_user_id();
    }

    /**
     * May this person put a view in front of the rest of the desk?
     *
     * Sharing is an agent's act, not a customer's, and it is asked of the
     * capability service rather than decided here - "can open the queue" is
     * already the line between somebody who works tickets and somebody who
     * raises them, and inventing a second line for saved views would be a
     * permission nobody knows exists. (Roadmap 4.5-UX-01)
     */
    public static function canShare($jsst_who = null) {
        /* Shared and team views belong to Agents & Teams, so presence of the
           module is asked before the capability is. Without it the desk has
           personal views and nothing else, and the checkbox, the save and the
           visibility clause all have to agree about that - gating only the
           checkbox would leave a posted visibility still saving a shared view
           and other people's shared views still listed. (Roadmap 4.5-UX-01) */
        if (!in_array('agent', jssupportticket::$_active_addons)) {
            return false;
        }
        if (!class_exists('JSSTcapability')) {
            return current_user_can('manage_options');
        }
        $jsst_actor = is_array($jsst_who) ? $jsst_who : JSSTcapability::actor($jsst_who);
        if ($jsst_actor['kind'] !== JSSTcapability::ACTOR_AGENT
            && $jsst_actor['kind'] !== JSSTcapability::ACTOR_ADMIN) {
            return false;
        }
        return JSSTcapability::can(JSSTcapability::QUEUE_VIEW, array(), $jsst_actor);
    }

    /**
     * The SQL fragment for "views this person is allowed to see".
     *
     * Their own, whatever its visibility, plus everybody's shared ones if they
     * are somebody who may see shared views at all.
     *
     * Worth being clear about what a shared view can and cannot do, because it
     * looks alarming and is not: a view is a set of filters, and the filters go
     * through the same query layer as everything else, which applies this
     * reader's own scope clause on top. A shared view naming a department the
     * reader cannot reach therefore shows them an empty list, never somebody
     * else's tickets. Sharing a view shares a question, not an answer.
     */
    private static function visibleClause($jsst_uid) {
        $jsst_clause = jssupportticket::$_db->prepare(' (uid = %d', $jsst_uid);
        if (self::canShare()) {
            $jsst_clause .= jssupportticket::$_db->prepare(' OR visibility = %d', self::VIEW_SHARED);
        }
        return $jsst_clause . ') ';
    }

    /**
     * A view name as it will be shown: trimmed, collapsed, and capped.
     */
    public static function cleanName($jsst_name) {
        $jsst_name = trim(wp_strip_all_tags((string) $jsst_name));
        $jsst_name = preg_replace('/\s+/u', ' ', $jsst_name);
        if (jssupportticketphplib::JSST_strlen($jsst_name) > self::MAX_NAME) {
            $jsst_name = trim(jssupportticketphplib::JSST_substr($jsst_name, 0, self::MAX_NAME));
        }
        return $jsst_name;
    }

    /**
     * Reduce a request to the filters a view stores.
     *
     * The save form carries a hidden copy of every filter the queue is showing,
     * and this reads them back. It deliberately does not read the parsed search
     * state: the form handler that runs a task is on init, and the admin search
     * state is not built until admin_init, so at the moment a view is saved that
     * state does not exist yet.
     */
    public static function filtersFromRequest() {
        $jsst_values = array();
        foreach (self::viewKeys() as $jsst_key) {
            $jsst_values[$jsst_key] = JSSTrequest::getVar($jsst_key, 'post', '');
        }
        $jsst_custom = JSSTrequest::getVar(self::CUSTOM_KEY, 'post', array());
        return self::cleanFilters($jsst_values, is_array($jsst_custom) ? $jsst_custom : array());
    }

    /**
     * Could the queue on screen be saved as a view? (Roadmap 4.5-UX-01)
     *
     * Asked of the search state the queue was built from, with the same rule
     * saving applies, so the save strip is offered after a search that has
     * something to keep and not on the unfiltered queue, where saving could
     * only be refused.
     */
    public static function canSaveCurrentSearch() {
        $jsst_state = isset(jssupportticket::$_search['ticket']) && is_array(jssupportticket::$_search['ticket'])
            ? jssupportticket::$_search['ticket'] : array();
        $jsst_custom = isset(jssupportticket::$_search['jsst_ticket_custom_field']) && is_array(jssupportticket::$_search['jsst_ticket_custom_field'])
            ? jssupportticket::$_search['jsst_ticket_custom_field'] : array();
        return !empty(self::cleanFilters($jsst_state, $jsst_custom));
    }

    /**
     * Reduce raw filter values to what a view stores, or to nothing when none
     * of them narrows the queue.
     */
    private static function cleanFilters($jsst_values, $jsst_custom) {
        $jsst_filters = array();
        foreach (self::viewKeys() as $jsst_key) {
            $jsst_value = isset($jsst_values[$jsst_key]) ? $jsst_values[$jsst_key] : '';
            if (is_array($jsst_value) || $jsst_value === null) {
                continue;
            }
            $jsst_value = trim(wp_strip_all_tags((string) $jsst_value));
            if ($jsst_value === '') {
                continue;
            }
            // "Nothing selected" is an empty option on every filter control, but
            // a custom template can post a zero id instead. Storing that would
            // save a view filtered to department 0, which matches no ticket and
            // looks like the view is broken.
            if (in_array($jsst_key, self::$jsst_idkeys, true) && (int) $jsst_value <= 0) {
                continue;
            }
            $jsst_filters[$jsst_key] = $jsst_value;
        }
        $jsst_customfilters = self::cleanCustomFilters($jsst_custom);
        if (!empty($jsst_customfilters)) {
            $jsst_filters[self::CUSTOM_KEY] = $jsst_customfilters;
        }
        // A tab and a sort order are not filters worth naming: every queue is on
        // some tab in some order, so a view of nothing else would be the default
        // queue with a name on it. They are stored, but only alongside something
        // that actually narrows the list.
        $jsst_narrowing = array_diff_key($jsst_filters, array('list' => 1, 'sortby' => 1));
        if (empty($jsst_narrowing)) {
            return array();
        }
        return $jsst_filters;
    }

    /**
     * Custom-field values worth storing, keyed by field name.
     *
     * Only fields that exist and are searchable today are kept. A tick-box or
     * multi-select field keeps its list of values; every other type is one
     * value, which is what the queue query compares it as.
     */
    private static function cleanCustomFilters($jsst_custom) {
        $jsst_clean = array();
        if (!is_array($jsst_custom)) {
            return $jsst_clean;
        }
        $jsst_types = self::customFieldTypes();
        foreach ($jsst_custom as $jsst_field => $jsst_value) {
            if (!isset($jsst_types[$jsst_field])) {
                continue;
            }
            $jsst_list = array();
            foreach ((array) $jsst_value as $jsst_one) {
                if (is_array($jsst_one) || $jsst_one === null) {
                    continue;
                }
                $jsst_one = trim(wp_strip_all_tags((string) $jsst_one));
                if ($jsst_one !== '') {
                    $jsst_list[] = $jsst_one;
                }
            }
            if (empty($jsst_list)) {
                continue;
            }
            $jsst_clean[$jsst_field] = in_array($jsst_types[$jsst_field], array('checkbox', 'multiple'), true)
                ? array_values(array_unique($jsst_list)) : $jsst_list[0];
        }
        return $jsst_clean;
    }

    /**
     * The ticket custom fields either desk offers as a filter, field => type.
     *
     * Both lists, because the backend desk and the front-end one each read
     * their own and a view saved on one is offered on the other.
     */
    private static function customFieldTypes() {
        static $jsst_types = null;
        if ($jsst_types !== null) {
            return $jsst_types;
        }
        $jsst_types = array();
        $jsst_customfields = JSSTincluder::getObjectClass('customfields');
        foreach (array($jsst_customfields->adminFieldsForSearch(1), $jsst_customfields->userFieldsForSearch(1)) as $jsst_list) {
            foreach ((array) $jsst_list as $jsst_field) {
                $jsst_types[$jsst_field->field] = $jsst_field->userfieldtype;
            }
        }
        return $jsst_types;
    }

    /**
     * The custom-field names the save forms copy into a view, for their script.
     */
    public static function customFieldNames() {
        return array_keys(self::customFieldTypes());
    }

    /**
     * The current user's views, newest name-ordered first.
     */
    public static function getViews() {
        self::ensureSchema();
        $jsst_uid = self::owner();
        /* Ordered by whose it is before what it is called, so an agent's own
           working set stays at the top of the list however many the rest of the
           desk has published. (Roadmap 4.5-UX-01) */
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, name, filters, visibility, uid, (uid = %d) AS mine
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_saved_views`
                WHERE " . self::visibleClause($jsst_uid) . " ORDER BY mine DESC, name ASC",
            $jsst_uid
        ));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * Views shaped for a combobox.
     */
    public static function getViewsForCombobox() {
        $jsst_options = array();
        foreach (self::getViews() as $jsst_view) {
            /* Somebody else's view says so in the option text. Two agents can
               name a view "Mine" and a combobox with two entries reading Mine
               is worse than useless. (Roadmap 4.5-UX-01) */
            $jsst_text = $jsst_view->name;
            if (empty($jsst_view->mine)) {
                $jsst_text = sprintf(
                    /* translators: %s: the name of a saved view somebody else shared */
                    esc_html(__('%s (shared)', 'js-support-ticket')),
                    $jsst_view->name
                );
            }
            $jsst_options[] = (object) array('id' => $jsst_view->id, 'text' => $jsst_text);
        }
        return $jsst_options;
    }

    /**
     * One view's stored filters, or an empty array when it is not this user's.
     *
     * The ownership check is here rather than at the call site so no caller can
     * forget it: a view id is a plain integer in a form, and reading another
     * agent's private filters must not be a matter of typing a different
     * number. A view they shared deliberately is a different matter, and is
     * the one case this now allows. (Roadmap 4.5-UX-01)
     */
    public static function getViewFilters($jsst_id) {
        if (!is_numeric($jsst_id) || (int) $jsst_id <= 0) {
            return array();
        }
        self::ensureSchema();
        $jsst_uid = self::owner();
        $jsst_filters = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT filters FROM `" . jssupportticket::$_db->prefix . "js_ticket_saved_views`
                WHERE id = %d AND " . self::visibleClause($jsst_uid),
            (int) $jsst_id
        ));
        if ($jsst_filters === null) {
            return array();
        }
        $jsst_decoded = json_decode($jsst_filters, true);
        if (!is_array($jsst_decoded)) {
            return array();
        }
        // Only keys a view is allowed to carry reach the query builder, whatever
        // is in the row, and only custom fields that still exist.
        $jsst_allowed = array_flip(self::viewKeys());
        $jsst_allowed[self::CUSTOM_KEY] = 1;
        $jsst_decoded = array_intersect_key($jsst_decoded, $jsst_allowed);
        if (isset($jsst_decoded[self::CUSTOM_KEY])) {
            $jsst_decoded[self::CUSTOM_KEY] = self::cleanCustomFilters($jsst_decoded[self::CUSTOM_KEY]);
        }
        return $jsst_decoded;
    }

    /**
     * Apply the chosen saved view to a search state. (Roadmap 4.5-UX-01)
     *
     * A saved view replaces the filters rather than adding to them. Choosing
     * one is a control on the same form as every other filter, so the request
     * carries both what the agent picked and whatever the form happened to
     * still be showing. The view is what they asked for, so it wins outright
     * and every filter it does not set is cleared - merging the two would mean
     * a view showed a different queue depending on what the screen looked like
     * when it was chosen.
     *
     * The tab is the one exception. Choosing a view clears the list field, so
     * the view opens on its own tab; clicking a tab sets it, and then the tab
     * is the newer instruction and keeps the view's other filters. Without that
     * an agent could never look at the closed tickets inside a saved view.
     *
     * Lifted out of the admin search-state builder and put here so the
     * front-end desk can have saved views at all. It was twenty-five lines
     * inside one of two functions that build the same thing, which is how the
     * front-end queue came to be the only agent screen in the product with no
     * way to save a search.
     */
    public static function applyView($jsst_search_array) {
        if (!is_array($jsst_search_array)) {
            $jsst_search_array = array();
        }
        $jsst_viewid = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('viewid', ''));
        if ($jsst_viewid !== '' && is_numeric($jsst_viewid)) {
            $jsst_viewfilters = self::getViewFilters($jsst_viewid);
            if (!empty($jsst_viewfilters)) {
                $jsst_postedlist = jssupportticketphplib::JSST_trim(JSSTrequest::getVar('list', null, ''));
                foreach (self::viewKeys() as $jsst_key) {
                    $jsst_search_array[$jsst_key] = isset($jsst_viewfilters[$jsst_key]) ? $jsst_viewfilters[$jsst_key] : '';
                }
                // Custom fields are replaced the same way. Only the fields this
                // desk searches on are in the array, so a stored field this desk
                // does not offer, or one deleted since, is simply not applied.
                if (isset($jsst_search_array['jsst_ticket_custom_field']) && is_array($jsst_search_array['jsst_ticket_custom_field'])) {
                    $jsst_storedcustom = isset($jsst_viewfilters[self::CUSTOM_KEY]) ? $jsst_viewfilters[self::CUSTOM_KEY] : array();
                    foreach (array_keys($jsst_search_array['jsst_ticket_custom_field']) as $jsst_field) {
                        $jsst_search_array['jsst_ticket_custom_field'][$jsst_field] = isset($jsst_storedcustom[$jsst_field]) ? $jsst_storedcustom[$jsst_field] : null;
                    }
                }
                if ($jsst_postedlist !== '') {
                    $jsst_search_array['list'] = $jsst_postedlist;
                }
                // A view saved without a tab of its own opens on Open rather
                // than on nothing.
                if ($jsst_search_array['list'] === '') {
                    $jsst_search_array['list'] = self::LIST_OPEN;
                }
                $jsst_search_array['viewid'] = (int) $jsst_viewid;
            }
        }
        if (!isset($jsst_search_array['viewid'])) {
            $jsst_search_array['viewid'] = '';
        }
        return $jsst_search_array;
    }

    /**
     * Apply an inbox named in the address. (Roadmap 4.5-FE-02)
     *
     * The three inboxes - mine, unassigned, my team - have only ever been
     * buttons that post the queue's own form. That is fine while you are
     * standing on the queue and useless anywhere else: the desk home wants to
     * say "these four are unassigned, here are the rest", and a link cannot
     * press a button. This reads a scope key off the request and writes it into
     * the search state as the field the queue models already understand, so a
     * link, a bookmark and the button all end up in the same place.
     *
     * The scope names and what each one filters on come from
     * JSSTworkspace::scopes(), never from anything in the URL: an unknown key
     * is ignored rather than guessed at, and "assigned to me" resolves to this
     * actor's own staff id here rather than being carried in the address, where
     * it would be one edited digit away from reading somebody else's inbox.
     *
     * Both controls are cleared before one is set. An agent filter and a team
     * filter both applied is an AND nobody asked for - the queue screens learnt
     * that when the team inbox arrived, and a link has the same problem.
     */
    public static function applyScope($jsst_search_array) {
        if (!is_array($jsst_search_array)) {
            $jsst_search_array = array();
        }
        $jsst_key = sanitize_key((string) JSSTrequest::getVar('scope', null, ''));
        if ($jsst_key === '' || !class_exists('JSSTworkspace')) {
            return $jsst_search_array;
        }
        $jsst_scopes = JSSTworkspace::scopes();
        if (!isset($jsst_scopes[$jsst_key])) {
            return $jsst_search_array;
        }
        $jsst_scope = $jsst_scopes[$jsst_key];
        $jsst_control = isset($jsst_scope['control']) ? $jsst_scope['control'] : 'staffid';
        $jsst_search_array['staffid'] = '';
        $jsst_search_array['teamid'] = '';
        if ($jsst_control === 'teamid') {
            $jsst_search_array['teamid'] = isset($jsst_scope['value']) ? (int) $jsst_scope['value'] : '';
        } elseif (isset($jsst_scope['filters']['staffid'])) {
            $jsst_staffid = (int) $jsst_scope['filters']['staffid'];
            if ($jsst_staffid === -1) {
                $jsst_actor = JSSTcapability::actor();
                /* Somebody who is not on the agent list has nothing assigned to
                   them, and -1 is the value that says so: the queue models
                   compare it with an id and match nothing. Leaving the zero
                   here would have handed them the unassigned queue under the
                   label "assigned to me". (Roadmap 4.5-FE-02) */
                $jsst_staffid = ((int) $jsst_actor['staffid'] > 0) ? (int) $jsst_actor['staffid'] : -1;
            }
            /* Zero is not "no filter" here, it is the unassigned inbox, and
               both queue models read it that way. */
            $jsst_search_array['staffid'] = $jsst_staffid;
        }
        return $jsst_search_array;
    }

    /**
     * Store a view under this name, replacing one of the same name.
     *
     * Saving over an existing name updates it rather than making a second view
     * that is impossible to tell apart in the list.
     *
     * @return true|string true, or a message saying why nothing was saved.
     */
    public static function saveView($jsst_name, $jsst_filters, $jsst_visibility = self::VIEW_PRIVATE) {
        self::ensureSchema();
        /* Asked once, here, rather than trusted from the form. A visibility
           arriving as 1 from a request made by somebody who may not share is
           the whole of the attack, and refusing the share while still saving
           the view privately is the right answer to it - the agent gets their
           view, the desk does not get their filters. (Roadmap 4.5-UX-01) */
        $jsst_visibility = ((int) $jsst_visibility === self::VIEW_SHARED && self::canShare())
            ? self::VIEW_SHARED : self::VIEW_PRIVATE;
        $jsst_name = self::cleanName($jsst_name);
        if ($jsst_name === '') {
            return esc_html(__('Give the view a name before saving it.', 'js-support-ticket'));
        }
        if (empty($jsst_filters)) {
            return esc_html(__('Search or filter the queue first — there is nothing to save yet.', 'js-support-ticket'));
        }
        $jsst_uid = self::owner();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_saved_views';
        $jsst_now = date_i18n('Y-m-d H:i:s');
        $jsst_json = wp_json_encode($jsst_filters);

        $jsst_existing = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT id FROM `" . $jsst_table . "` WHERE uid = %d AND name = %s",
            $jsst_uid,
            $jsst_name
        ));
        if ($jsst_existing) {
            jssupportticket::$_db->update(
                $jsst_table,
                array('filters' => $jsst_json, 'visibility' => $jsst_visibility, 'updated' => $jsst_now),
                array('id' => (int) $jsst_existing, 'uid' => $jsst_uid),
                array('%s', '%d', '%s'),
                array('%d', '%d')
            );
            return true;
        }

        $jsst_count = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(id) FROM `" . $jsst_table . "` WHERE uid = %d",
            $jsst_uid
        ));
        if ($jsst_count >= self::MAX_VIEWS) {
            return sprintf(
                /* translators: %d: how many saved views one agent may keep */
                esc_html(__('You already have %d saved views. Delete one before saving another.', 'js-support-ticket')),
                self::MAX_VIEWS
            );
        }
        jssupportticket::$_db->insert(
            $jsst_table,
            array(
                'uid'        => $jsst_uid,
                'name'       => $jsst_name,
                'filters'    => $jsst_json,
                'visibility' => $jsst_visibility,
                'created'    => $jsst_now,
                'updated'    => $jsst_now,
            ),
            array('%d', '%s', '%s', '%d', '%s', '%s')
        );
        return true;
    }

    /**
     * Delete a view.
     *
     * The owner's own, always. Anybody else's only for an administrator, and
     * only a shared one: a private view is somebody's working set and there is
     * no administrative reason to reach into it, while a shared one is on
     * everybody's screen and somebody has to be able to take it down.
     * (Roadmap 4.5-UX-01)
     */
    public static function deleteView($jsst_id) {
        if (!is_numeric($jsst_id) || (int) $jsst_id <= 0) {
            return false;
        }
        self::ensureSchema();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_saved_views';
        $jsst_deleted = jssupportticket::$_db->delete(
            $jsst_table,
            array('id' => (int) $jsst_id, 'uid' => self::owner()),
            array('%d', '%d')
        );
        if ($jsst_deleted) {
            return true;
        }
        if (current_user_can('manage_options')) {
            $jsst_deleted = jssupportticket::$_db->delete(
                $jsst_table,
                array('id' => (int) $jsst_id, 'visibility' => self::VIEW_SHARED),
                array('%d', '%d')
            );
        }
        return !empty($jsst_deleted);
    }

    /**
     * May the current user delete this view, as getViews() returned it?
     *
     * The same rule deleteView() enforces, asked before a Delete link is drawn,
     * so nobody is offered a delete that would match nothing.
     */
    public static function canDeleteView($jsst_view) {
        if (!is_object($jsst_view)) {
            return false;
        }
        if (!empty($jsst_view->mine)) {
            return true;
        }
        return current_user_can('manage_options') && (int) $jsst_view->visibility === self::VIEW_SHARED;
    }

    /**
     * Remove every view belonging to a WordPress user.
     *
     * Called when the user is deleted, so the table does not keep rows owned by
     * nobody.
     */
    public static function removeUserViews($jsst_wpuid) {
        if (!is_numeric($jsst_wpuid)) {
            return false;
        }
        jssupportticket::$_db->delete(
            jssupportticket::$_db->prefix . 'js_ticket_saved_views',
            array('uid' => (int) $jsst_wpuid),
            array('%d')
        );
        return true;
    }

}
