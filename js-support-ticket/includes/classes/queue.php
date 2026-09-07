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
    const SCHEMA_VERSION = '400';

    /** A view name longer than this is truncated rather than refused. */
    const MAX_NAME = 60;

    /** How many views one agent may keep. Enough for a working set, not a filing system. */
    const MAX_VIEWS = 30;

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
        // Only the table this method owns. The indexes it adds to
        // js_ticket_tickets are left out on purpose: that table is the largest on
        // the site and an ALTER that cannot complete must not be retried on every
        // request. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_queue_schema', self::SCHEMA_VERSION,
                array('js_ticket_saved_views' => array()))) {
            return;
        }
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_saved_views';

        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    uid int(11) NOT NULL,
                    name varchar(60) NOT NULL,
                    filters text,
                    created datetime DEFAULT NULL,
                    updated datetime DEFAULT NULL,
                    PRIMARY KEY (id),
                    KEY jsst_uid (uid)
                ) " . $jsst_charset);

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
        'status', 'tagid', 'orderid', 'eddorderid',
    );

    /**
     * The filter keys a saved view stores.
     *
     * Only the queue's own filters. Custom-field values are left out on purpose:
     * they belong to a ticket form that can be edited or deleted after the view
     * is saved, and a view that quietly filters on a field that no longer exists
     * is worse than one that does not filter on it at all.
     */
    public static function viewKeys() {
        return array(
            'list', 'keywords', 'subject', 'name', 'email', 'phone', 'ticketid',
            'datestart', 'dateend', 'priority', 'departmentid', 'helptopicid',
            'productid', 'staffid', 'status', 'tagid', 'orderid', 'eddorderid',
            'sortby',
        );
    }

    /**
     * Who owns the views on this screen.
     *
     * Views are per-person: an agent's working set is their own, and sharing
     * one with a team needs a permission model that basic saved views do not
     * have. Team and shared views stay in Pro.
     */
    private static function owner() {
        return (int) get_current_user_id();
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
        $jsst_filters = array();
        foreach (self::viewKeys() as $jsst_key) {
            $jsst_value = JSSTrequest::getVar($jsst_key, 'post', '');
            if (is_array($jsst_value)) {
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
     * The current user's views, newest name-ordered first.
     */
    public static function getViews() {
        self::ensureSchema();
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, name, filters FROM `" . jssupportticket::$_db->prefix . "js_ticket_saved_views`
                WHERE uid = %d ORDER BY name ASC",
            self::owner()
        ));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * Views shaped for a combobox.
     */
    public static function getViewsForCombobox() {
        $jsst_options = array();
        foreach (self::getViews() as $jsst_view) {
            $jsst_options[] = (object) array('id' => $jsst_view->id, 'text' => $jsst_view->name);
        }
        return $jsst_options;
    }

    /**
     * One view's stored filters, or an empty array when it is not this user's.
     *
     * The ownership check is here rather than at the call site so no caller can
     * forget it: a view id is a plain integer in a form, and reading another
     * agent's saved filters must not be a matter of typing a different number.
     */
    public static function getViewFilters($jsst_id) {
        if (!is_numeric($jsst_id) || (int) $jsst_id <= 0) {
            return array();
        }
        self::ensureSchema();
        $jsst_filters = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT filters FROM `" . jssupportticket::$_db->prefix . "js_ticket_saved_views`
                WHERE id = %d AND uid = %d",
            (int) $jsst_id,
            self::owner()
        ));
        if ($jsst_filters === null) {
            return array();
        }
        $jsst_decoded = json_decode($jsst_filters, true);
        if (!is_array($jsst_decoded)) {
            return array();
        }
        // Only keys a view is allowed to carry reach the query builder, whatever
        // is in the row.
        $jsst_allowed = array_flip(self::viewKeys());
        return array_intersect_key($jsst_decoded, $jsst_allowed);
    }

    /**
     * Store a view under this name, replacing one of the same name.
     *
     * Saving over an existing name updates it rather than making a second view
     * that is impossible to tell apart in the list.
     *
     * @return true|string true, or a message saying why nothing was saved.
     */
    public static function saveView($jsst_name, $jsst_filters) {
        self::ensureSchema();
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
                array('filters' => $jsst_json, 'updated' => $jsst_now),
                array('id' => (int) $jsst_existing, 'uid' => $jsst_uid),
                array('%s', '%s'),
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
                'uid'     => $jsst_uid,
                'name'    => $jsst_name,
                'filters' => $jsst_json,
                'created' => $jsst_now,
                'updated' => $jsst_now,
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
        return true;
    }

    /**
     * Delete one of the current user's views.
     */
    public static function deleteView($jsst_id) {
        if (!is_numeric($jsst_id) || (int) $jsst_id <= 0) {
            return false;
        }
        self::ensureSchema();
        jssupportticket::$_db->delete(
            jssupportticket::$_db->prefix . 'js_ticket_saved_views',
            array('id' => (int) $jsst_id, 'uid' => self::owner()),
            array('%d', '%d')
        );
        return true;
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
