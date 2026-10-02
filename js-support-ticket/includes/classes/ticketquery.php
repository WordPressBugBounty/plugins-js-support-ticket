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
if (class_exists('JSSTticketquery')) {
    return;
}

/**
 * Ticket queries — the read half of the application layer.
 * (Roadmap 4.5-ARCH-02)
 *
 * The queue has been implemented three times in this product: once for the
 * admin list, once for "my tickets", once for the agent's front-end list. They
 * are three different functions with three different scope rules, which is why
 * a ticket can be missing from one list and present in another, and why the
 * only way to know what an agent can see is to sign in as them.
 *
 * This is one query, scoped in one place. The scope comes from the capability
 * service, not from a rule written here, so the list an agent sees and the
 * single ticket they can open are the same answer to the same question. That
 * equality is the whole point: where the list rule and the detail rule are
 * written separately, the gap between them is an IDOR, and this product has
 * had that gap since it shipped.
 *
 * The three shapes both workspaces need:
 *
 *   queues()          A page of tickets, filtered, sorted and scoped.
 *   detail()          One ticket, or a WP_Error if this actor may not see it.
 *   customerHistory() What else this person has raised.
 *
 * Everything returns plain data. No template is included, no global is set, no
 * message is queued — a query that writes to jssupportticket::$jsst_data cannot
 * be called twice on one page, which is exactly what a workspace home screen
 * showing four counts needs to do.
 */
class JSSTticketquery {

    /** The most rows one call will return, whatever it is asked for. */
    const MAX_LIMIT = 200;

    /**
     * A page of tickets this actor is allowed to see.
     *
     * @param array $jsst_args
     *   list          int    A JSSTqueue tab constant. Default: all.
     *   search        string Free text, run through JSSTqueue::searchFilter().
     *   departmentid  int
     *   priorityid    int
     *   staffid       int    0 means unassigned, -1 means "assigned to me".
     *   status        int
     *   productid     int
     *   helptopicid   int
     *   datestart     string Y-m-d
     *   dateend       string Y-m-d
     *   breached      bool   Past its due date, or flagged overdue.
     *   duewithin     int    Due inside this many hours and not yet late.
     *   orderby       string One of the sortable columns. Default: updated.
     *   order         string ASC or DESC.
     *   limit         int
     *   offset        int
     *   actor         array  Ask on somebody else's behalf.
     * @return array rows, total, limit, offset, scope
     */
    public static function queues($jsst_args = array()) {
        $jsst_actor = self::actor($jsst_args);
        $jsst_prefix = jssupportticket::$_db->prefix;

        /* Refused rather than returned empty. "You may not open the queue" and
           "the queue is empty" look identical to a caller that only gets rows,
           and a portal that shows an empty queue to somebody who should have
           been sent to sign in is the sort of bug nobody reports. */
        if (!JSSTcapability::can(JSSTcapability::QUEUE_VIEW, array(), $jsst_actor)
            && $jsst_actor['kind'] !== JSSTcapability::ACTOR_CUSTOMER) {
            return self::refused(JSSTcapability::assert(JSSTcapability::QUEUE_VIEW, array(), $jsst_actor));
        }

        $jsst_where = ' WHERE 1 = 1 ';
        $jsst_where .= JSSTqueue::listClause(JSSTqueue::normalizeList(isset($jsst_args['list']) ? (int) $jsst_args['list'] : JSSTqueue::LIST_ALL));
        $jsst_where .= JSSTcapability::ticketScopeClause('ticket', $jsst_actor);
        $jsst_where .= self::filterClause($jsst_args, $jsst_actor);

        if (!empty($jsst_args['search'])) {
            /* searchFilter() hands back the fragment and its arguments
               separately and leaves the preparing to the caller, so that a
               query assembling several fragments prepares once rather than
               nesting prepare() calls — which is how a literal %s ends up in
               somebody's ticket subject. */
            $jsst_search = JSSTqueue::searchFilter($jsst_args['search']);
            if (is_array($jsst_search) && !empty($jsst_search['where'])) {
                $jsst_where .= jssupportticket::$_db->prepare($jsst_search['where'], $jsst_search['args']);
            }
        }

        /* Merged-away tickets stay in the queue, under Closed.

           They used to be filtered out here, on the grounds that a merged
           ticket is part of the ticket it was merged into and listing both is
           how an agent answers the copy nobody is reading. That risk is real
           and it is already covered without this clause: a merge sets the
           source to status 6, `Close Due To Merge` (JSSTmergeticketModel::
           STATUS_MERGED), and every open, waiting, overdue and answered tab
           excludes 5 and 6 by status alone. So the only tabs this clause could
           ever change were Closed and All - the two places where hiding the
           ticket is not protection, it is a ticket a customer can see in their
           own list vanishing from the agent's.

           It also has to be the same answer on both sides of this class.
           `counts()` reads the same rule, and the front-end queue builds its
           own list SQL while taking its numbers from here - so a filter applied
           in one and not the other showed a Closed tab listing two tickets
           under a tab that said one.

           `activity()`, `mentions()` and `peopleWhere()` go on excluding them:
           those are feeds and workload, not the ticket register, and a merged
           ticket genuinely has no further activity of its own. */

        $jsst_limit = self::limit($jsst_args);
        $jsst_offset = isset($jsst_args['offset']) ? max(0, (int) $jsst_args['offset']) : 0;
        $jsst_order = self::orderClause($jsst_args);

        $jsst_select = 'SELECT ticket.id, ticket.ticketid, ticket.subject, ticket.status, ticket.priorityid, '
            . 'ticket.departmentid, ticket.staffid, ticket.uid, ticket.email, ticket.name, ticket.created, '
            . 'ticket.updated, ticket.lastreply, ticket.isanswered, ticket.isoverdue, ticket.duedate, ticket.`lock` '
            . 'FROM `' . $jsst_prefix . 'js_ticket_tickets` AS ticket ';

        $jsst_rows = jssupportticket::$_db->get_results(
            $jsst_select . $jsst_where . $jsst_order
            . ' LIMIT ' . (int) $jsst_limit . ' OFFSET ' . (int) $jsst_offset
        );
        $jsst_total = (int) jssupportticket::$_db->get_var(
            'SELECT COUNT(ticket.id) FROM `' . $jsst_prefix . 'js_ticket_tickets` AS ticket ' . $jsst_where
        );

        return array(
            'ok'     => true,
            'rows'   => is_array($jsst_rows) ? $jsst_rows : array(),
            'total'  => $jsst_total,
            'limit'  => $jsst_limit,
            'offset' => $jsst_offset,
            'scope'  => $jsst_actor['scope'],
        );
    }

    /**
     * The count behind each queue tab, for this actor.
     *
     * One query, not one per tab: a workspace home screen shows five or six of
     * these at once, and six COUNT(*) over the ticket table is the difference
     * between a page that loads and a page an agent learns to avoid.
     */
    public static function counts($jsst_args = array()) {
        $jsst_actor = self::actor($jsst_args);
        if (!JSSTcapability::can(JSSTcapability::QUEUE_VIEW, array(), $jsst_actor)
            && $jsst_actor['kind'] !== JSSTcapability::ACTOR_CUSTOMER) {
            return array();
        }
        /* No merge filter, so that every tab number counts what its tab lists.
           See the note in queues() for why a merged ticket belongs under
           Closed rather than nowhere. */
        $jsst_where = ' WHERE 1 = 1 '
            . JSSTcapability::ticketScopeClause('ticket', $jsst_actor)
            . self::filterClause($jsst_args, $jsst_actor);

        $jsst_selects = array();
        foreach (JSSTqueue::countClauses() as $jsst_alias => $jsst_clause) {
            /* The aliases and the conditions both come from JSSTqueue, which
               builds them from a fixed map rather than from the filterable tab
               list — nothing here is pasted in from a request or a filter. */
            $jsst_selects[] = 'SUM(CASE WHEN ' . $jsst_clause . ' THEN 1 ELSE 0 END) AS `' . $jsst_alias . '`';
        }
        if (empty($jsst_selects)) {
            return array();
        }
        $jsst_row = jssupportticket::$_db->get_row(
            'SELECT ' . implode(', ', $jsst_selects)
            . ' FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` AS ticket ' . $jsst_where,
            ARRAY_A
        );
        return is_array($jsst_row) ? array_map('intval', $jsst_row) : array();
    }

    /**
     * One ticket, if this actor may see it.
     *
     * @param int   $jsst_ticketid
     * @param array $jsst_args 'actor', and 'token' for a guest arriving with one.
     * @return array|WP_Error
     */
    public static function detail($jsst_ticketid, $jsst_args = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_args);
        $jsst_subject = array('ticket' => $jsst_ticketid);
        if (isset($jsst_args['token'])) {
            $jsst_subject['token'] = $jsst_args['token'];
        }
        $jsst_allowed = JSSTcapability::assert(JSSTcapability::TICKET_VIEW, $jsst_subject, $jsst_actor);
        if (is_wp_error($jsst_allowed)) {
            return $jsst_allowed;
        }
        $jsst_ticket = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d',
            $jsst_ticketid
        ));
        if (!$jsst_ticket) {
            return new WP_Error('jsst_ticket_missing',
                esc_html(__('That ticket does not exist.', 'js-support-ticket')), array('status' => 404));
        }
        return array(
            'ok'      => true,
            'ticket'  => $jsst_ticket,
            /* What the shell may offer on this ticket, answered once here
               rather than by each template asking a different question. This is
               the parity contract in one array: two workspaces rendering from
               the same map cannot drift, because there is nothing to drift. */
            'can'     => self::allowedActions($jsst_ticketid, $jsst_actor, $jsst_subject),
            'actor'   => array('kind' => $jsst_actor['kind'], 'staffid' => (int) $jsst_actor['staffid']),
        );
    }

    /**
     * Which of the ticket actions this actor may take on this ticket.
     *
     * Returned as action => bool for every subject-scoped action in the
     * catalogue, so a shell renders its toolbar from data instead of from a
     * list of if statements that the other shell will get slightly wrong.
     */
    public static function allowedActions($jsst_ticketid, $jsst_actor = null, $jsst_subject = null) {
        $jsst_actor = is_array($jsst_actor) ? $jsst_actor : JSSTcapability::actor($jsst_actor);
        if (!is_array($jsst_subject)) {
            $jsst_subject = array('ticket' => (int) $jsst_ticketid);
        }
        $jsst_can = array();
        foreach (JSSTcapability::actions() as $jsst_action => $jsst_def) {
            if (empty($jsst_def['scoped'])) {
                continue;
            }
            $jsst_can[$jsst_action] = JSSTcapability::can($jsst_action, $jsst_subject, $jsst_actor);
        }
        return $jsst_can;
    }

    /**
     * What else this customer has raised.
     *
     * Scoped twice on purpose. The actor's own scope decides which of the
     * customer's tickets they may see at all — an agent scoped to Billing does
     * not get the customer's Sales history just because they opened one of
     * their tickets. Seeing the customer record itself is a separate permission
     * again, which is what makes "this agent works tickets but may not browse
     * customers" expressible.
     *
     * @param int $jsst_uid The plugin's user id, not a WordPress one.
     */
    public static function customerHistory($jsst_uid, $jsst_args = array()) {
        $jsst_uid = (int) $jsst_uid;
        $jsst_actor = self::actor($jsst_args);
        if ($jsst_uid <= 0) {
            return array('ok' => true, 'rows' => array(), 'total' => 0);
        }
        $jsst_own = ((int) $jsst_actor['uid'] === $jsst_uid);
        if (!$jsst_own && !JSSTcapability::can(JSSTcapability::CUSTOMER_VIEW, array(), $jsst_actor)) {
            return self::refused(JSSTcapability::assert(JSSTcapability::CUSTOMER_VIEW, array(), $jsst_actor));
        }
        $jsst_where = jssupportticket::$_db->prepare(' WHERE ticket.uid = %d ', $jsst_uid)
            . JSSTcapability::ticketScopeClause('ticket', $jsst_actor);
        $jsst_limit = self::limit($jsst_args);
        $jsst_rows = jssupportticket::$_db->get_results(
            'SELECT ticket.id, ticket.ticketid, ticket.subject, ticket.status, ticket.priorityid, '
            . 'ticket.departmentid, ticket.staffid, ticket.created, ticket.updated '
            . 'FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` AS ticket '
            . $jsst_where . ' ORDER BY ticket.created DESC LIMIT ' . (int) $jsst_limit
        );
        $jsst_total = (int) jssupportticket::$_db->get_var(
            'SELECT COUNT(ticket.id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` AS ticket ' . $jsst_where
        );
        return array(
            'ok'    => true,
            'rows'  => is_array($jsst_rows) ? $jsst_rows : array(),
            'total' => $jsst_total,
        );
    }

    /* =====================================================================
     * What a desk home is made of (Roadmap 4.5-FE-02)
     * ================================================================== */

    /**
     * What has happened lately on the tickets this actor can see.
     *
     * The activity log has only ever been read one ticket at a time: you open a
     * ticket and read what happened to it. An agent arriving in the morning is
     * asking the opposite question - what happened while I was away, on
     * anything of mine - and until now there was no way to ask it. It is the
     * same table read the other way round, joined to the tickets so the scope
     * clause every queue passes through applies here too. An activity feed that
     * skipped it would be the first screen in this product to tell a
     * department-scoped agent what is going on in a department they may not see.
     *
     * It selects nothing but the columns the pre-4.0 add-on layout also has.
     * The 4.0 columns - source, fieldname, oldvalue, newvalue - are repaired
     * into the table on demand, and a home screen is a bad place to find out
     * that the repair has not run yet.
     */
    public static function activity($jsst_args = array()) {
        $jsst_actor = self::actor($jsst_args);
        if (!JSSTcapability::can(JSSTcapability::QUEUE_VIEW, array(), $jsst_actor)) {
            return array();
        }
        $jsst_model = JSSTincluder::getJSModel('tickethistory');
        if (is_object($jsst_model) && method_exists($jsst_model, 'ensureSchema')) {
            $jsst_model->ensureSchema();
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_where = ' WHERE al.eventfor = 1 '
            . JSSTcapability::ticketScopeClause('ticket', $jsst_actor)
            . ' AND (ticket.mergestatus IS NULL OR ticket.mergestatus != 1) ';
        $jsst_rows = jssupportticket::$_db->get_results(
            'SELECT al.id, al.message, al.datetime, al.eventtype, al.uid, '
            . 'ticket.id AS ticketid, ticket.ticketid AS reference, ticket.subject, '
            . 'actor.name AS actorname '
            . 'FROM `' . $jsst_prefix . 'js_ticket_activity_log` AS al '
            . 'INNER JOIN `' . $jsst_prefix . 'js_ticket_tickets` AS ticket ON ticket.id = al.referenceid '
            . 'LEFT JOIN `' . $jsst_prefix . 'js_ticket_users` AS actor ON actor.id = al.uid '
            . $jsst_where
            . ' ORDER BY al.datetime DESC, al.id DESC LIMIT ' . (int) self::limit($jsst_args)
        );
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * Internal notes that name this person. (Roadmap 4.5-FE-02)
     *
     * Agents have been writing "@someone, can you look at this" in internal
     * notes for as long as internal notes have existed, and nothing has ever
     * read it: the person named finds out when they happen to open the ticket.
     * This reads what they already write - the note text, for this agent's own
     * WordPress handles - so the home screen can show it today.
     *
     * It is deliberately a search rather than a store. A real mention is a
     * recorded thing with a notification, a read state and a watcher list, and
     * that is 4.5-FE-08's work; inventing half of it here would leave FE-08
     * migrating a store that was never written properly. What this cannot do is
     * exactly what tells the two apart: it cannot know that a mention has been
     * read, and it only finds people whose handle was typed correctly.
     *
     * Notes the agent wrote themselves are excluded - quoting your own name is
     * not somebody asking you for something - and the search is bounded by age
     * so it stays a LIKE over recent notes rather than over every note the site
     * has ever had.
     */
    public static function mentions($jsst_args = array()) {
        $jsst_actor = self::actor($jsst_args);
        /* Recorded mentions first. 4.5-FE-08 writes a row when somebody is
           named, which is exact; the search below is what this had to do
           before that existed and is kept for notes written then. A desk that
           upgrades mid-week should not lose the mentions from Monday.
           (Roadmap 4.5-FE-08) */
        if (class_exists('JSSTcollab') && (int) $jsst_actor['staffid'] > 0) {
            $jsst_recorded = JSSTcollab::mentionsFor((int) $jsst_actor['staffid'], self::limit($jsst_args));
            if (!empty($jsst_recorded)) {
                $jsst_out = array();
                foreach ($jsst_recorded as $jsst_row) {
                    $jsst_out[] = (object) array(
                        'id'         => $jsst_row->id,
                        'note'       => $jsst_row->excerpt,
                        'created'    => $jsst_row->created,
                        'staffid'    => $jsst_row->byid,
                        'ticketid'   => $jsst_row->ticketid,
                        'reference'  => $jsst_row->reference,
                        'subject'    => $jsst_row->subject,
                        'authorname' => $jsst_row->byname,
                    );
                }
                return $jsst_out;
            }
        }
        /* Notes are internal, so being allowed to write one is the closest
           thing this product has to being allowed to read them. An actor who
           may not is not somebody the desk should be showing note text to. */
        if (!JSSTcapability::can(JSSTcapability::TICKET_NOTE, array(), $jsst_actor)) {
            return array();
        }
        $jsst_handles = self::handles($jsst_actor);
        if (empty($jsst_handles)) {
            return array();
        }
        $jsst_days = isset($jsst_args['days']) ? max(1, (int) $jsst_args['days']) : 60;
        $jsst_prefix = jssupportticket::$_db->prefix;

        $jsst_likes = array();
        $jsst_params = array();
        foreach ($jsst_handles as $jsst_handle) {
            $jsst_likes[] = 'note.note LIKE %s';
            $jsst_params[] = '%' . jssupportticket::$_db->esc_like('@' . $jsst_handle) . '%';
        }
        $jsst_params[] = gmdate('Y-m-d H:i:s', strtotime(current_time('mysql')) - ($jsst_days * DAY_IN_SECONDS));
        $jsst_where = jssupportticket::$_db->prepare(
            ' WHERE (' . implode(' OR ', $jsst_likes) . ') AND note.created >= %s ',
            $jsst_params
        );
        if ((int) $jsst_actor['uid'] > 0) {
            $jsst_where .= jssupportticket::$_db->prepare(
                ' AND (note.staffid IS NULL OR note.staffid != %d) ', (int) $jsst_actor['uid']);
        }
        $jsst_where .= JSSTcapability::ticketScopeClause('ticket', $jsst_actor)
            . ' AND (ticket.mergestatus IS NULL OR ticket.mergestatus != 1) ';

        $jsst_rows = jssupportticket::$_db->get_results(
            'SELECT note.id, note.note, note.created, note.staffid, '
            . 'ticket.id AS ticketid, ticket.ticketid AS reference, ticket.subject, '
            . 'author.name AS authorname '
            . 'FROM `' . $jsst_prefix . 'js_ticket_notes` AS note '
            . 'INNER JOIN `' . $jsst_prefix . 'js_ticket_tickets` AS ticket ON ticket.id = note.ticketid '
            . 'LEFT JOIN `' . $jsst_prefix . 'js_ticket_users` AS author ON author.id = note.staffid '
            . $jsst_where
            . ' ORDER BY note.created DESC, note.id DESC LIMIT ' . (int) self::limit($jsst_args)
        );
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * The handles an agent would be written down as in a note.
     *
     * Both the login and the display name, because the person typing the note
     * is looking at whichever one their screen shows them. A one-character
     * handle is dropped: "@a" matches half the notes on the site.
     */
    private static function handles($jsst_actor) {
        $jsst_out = array();
        $jsst_user = ((int) $jsst_actor['wpuid'] > 0) ? get_userdata((int) $jsst_actor['wpuid']) : false;
        if ($jsst_user) {
            $jsst_out[] = $jsst_user->user_login;
            $jsst_out[] = $jsst_user->display_name;
            $jsst_out[] = $jsst_user->user_nicename;
        }
        if (!empty($jsst_actor['display'])) {
            $jsst_out[] = $jsst_actor['display'];
        }
        $jsst_clean = array();
        foreach ($jsst_out as $jsst_handle) {
            $jsst_handle = trim((string) $jsst_handle);
            if (strlen($jsst_handle) > 1 && !in_array($jsst_handle, $jsst_clean, true)) {
                $jsst_clean[] = $jsst_handle;
            }
        }
        return $jsst_clean;
    }

    /**
     * The people behind the tickets this actor can see. (Roadmap 4.5-FE-02)
     *
     * A customer here is an e-mail address, and that is not a shortcut: a guest
     * ticket has no user account behind it at all, so the address is the only
     * identifier every ticket carries. Grouping on the account id instead would
     * put every guest on the site into one row called "0".
     *
     * Scoped like a queue, because it is one. A customer list assembled from
     * tickets an agent may not open would tell them who is complaining and how
     * often, which is the reporting half of the same disclosure that
     * 4.5-FE-04's visibility settings exist to prevent.
     */
    public static function customers($jsst_args = array()) {
        $jsst_actor = self::actor($jsst_args);
        if (!JSSTcapability::can(JSSTcapability::CUSTOMER_VIEW, array(), $jsst_actor)) {
            return self::refused(JSSTcapability::assert(JSSTcapability::CUSTOMER_VIEW, array(), $jsst_actor));
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_where = self::peopleWhere($jsst_args, $jsst_actor);
        $jsst_limit = self::limit($jsst_args);
        $jsst_offset = isset($jsst_args['offset']) ? max(0, (int) $jsst_args['offset']) : 0;
        /* The open condition comes from the queue's own map rather than from a
           status number written here: "open" has to mean on this screen what it
           means on the tab beside it. */
        $jsst_clauses = JSSTqueue::countClauses();

        $jsst_rows = jssupportticket::$_db->get_results(
            'SELECT ticket.email, MAX(ticket.uid) AS uid, MAX(ticket.name) AS name, '
            . 'COUNT(ticket.id) AS tickets, '
            . 'SUM(CASE WHEN ' . $jsst_clauses['openticket'] . ' THEN 1 ELSE 0 END) AS openticket, '
            // An untouched ticket holds 0000-00-00 in updated and lastreply.
            . 'MAX(GREATEST(ticket.created, COALESCE(ticket.updated, ticket.created), COALESCE(ticket.lastreply, ticket.created))) AS lastactivity '
            . 'FROM `' . $jsst_prefix . 'js_ticket_tickets` AS ticket '
            . $jsst_where
            . ' GROUP BY ticket.email ORDER BY lastactivity DESC '
            . ' LIMIT ' . (int) $jsst_limit . ' OFFSET ' . (int) $jsst_offset
        );
        $jsst_total = (int) jssupportticket::$_db->get_var(
            'SELECT COUNT(DISTINCT ticket.email) FROM `' . $jsst_prefix . 'js_ticket_tickets` AS ticket ' . $jsst_where
        );
        return array(
            'ok'     => true,
            'rows'   => is_array($jsst_rows) ? $jsst_rows : array(),
            'total'  => $jsst_total,
            'limit'  => $jsst_limit,
            'offset' => $jsst_offset,
        );
    }

    /**
     * The companies behind those people. (Roadmap 4.5-FE-02)
     *
     * Derived from the e-mail domain, and nothing else. This product has no
     * company record - no table, no field, nothing an administrator can edit -
     * so a company here is "everybody writing in from acme.example", which is
     * the grouping an agent already makes in their head when three tickets
     * arrive from the same customer's colleagues.
     *
     * Stated plainly because the derivation has a known edge: everybody on a
     * public mail provider lands in one enormous "gmail.com", and two brands of
     * the same firm land in two rows. A real company entity - with its own
     * name, its own contacts and its own SLA - is a schema change and belongs
     * with the customer work in a later release, not smuggled in behind a
     * SUBSTRING_INDEX.
     */
    public static function companies($jsst_args = array()) {
        $jsst_actor = self::actor($jsst_args);
        if (!JSSTcapability::can(JSSTcapability::CUSTOMER_VIEW, array(), $jsst_actor)) {
            return self::refused(JSSTcapability::assert(JSSTcapability::CUSTOMER_VIEW, array(), $jsst_actor));
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_where = self::peopleWhere($jsst_args, $jsst_actor);
        $jsst_limit = self::limit($jsst_args);
        $jsst_clauses = JSSTqueue::countClauses();

        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT LOWER(SUBSTRING_INDEX(ticket.email, '@', -1)) AS company, "
            . 'COUNT(DISTINCT ticket.email) AS people, COUNT(ticket.id) AS tickets, '
            . 'SUM(CASE WHEN ' . $jsst_clauses['openticket'] . ' THEN 1 ELSE 0 END) AS openticket, '
            . 'MAX(GREATEST(ticket.created, COALESCE(ticket.updated, ticket.created), COALESCE(ticket.lastreply, ticket.created))) AS lastactivity '
            . 'FROM `' . $jsst_prefix . 'js_ticket_tickets` AS ticket '
            . $jsst_where
            . ' GROUP BY company ORDER BY tickets DESC, lastactivity DESC '
            . ' LIMIT ' . (int) $jsst_limit
        );
        return array(
            'ok'   => true,
            'rows' => is_array($jsst_rows) ? $jsst_rows : array(),
        );
    }

    /**
     * The WHERE both people lists share: this actor's scope, a search, and one
     * company when the customer list is being read inside one.
     */
    private static function peopleWhere($jsst_args, $jsst_actor) {
        $jsst_where = " WHERE ticket.email IS NOT NULL AND ticket.email != '' "
            . JSSTcapability::ticketScopeClause('ticket', $jsst_actor)
            . ' AND (ticket.mergestatus IS NULL OR ticket.mergestatus != 1) ';
        if (!empty($jsst_args['search'])) {
            $jsst_term = '%' . jssupportticket::$_db->esc_like(trim(wp_strip_all_tags((string) $jsst_args['search']))) . '%';
            $jsst_where .= jssupportticket::$_db->prepare(
                ' AND (ticket.email LIKE %s OR ticket.name LIKE %s) ', $jsst_term, $jsst_term);
        }
        if (!empty($jsst_args['company'])) {
            $jsst_pick = trim((string) $jsst_args['company']);
            /* Two kinds of company can be chosen on that screen, and they
               narrow the list differently. `co:<id>` is a record - its people,
               its domains, and the people another company has named taken back
               out - and anything else is still the bare e-mail domain v4.5
               grouped by. Written as one argument rather than two because every
               link, every hidden field and the paging on that screen already
               carry this one. (Roadmap 5.5-COM-06) */
            if (strpos($jsst_pick, 'co:') === 0 && class_exists('JSSTcompanies')) {
                $jsst_company = JSSTcompanies::get((int) substr($jsst_pick, 3));
                $jsst_where .= $jsst_company
                    ? ' AND ' . JSSTcompanies::ticketClause($jsst_company, 'ticket') . ' '
                    : ' AND 1 = 0 ';
            } else {
                $jsst_where .= jssupportticket::$_db->prepare(
                    " AND LOWER(SUBSTRING_INDEX(ticket.email, '@', -1)) = %s ",
                    strtolower($jsst_pick));
            }
        }
        return $jsst_where;
    }

    /* =====================================================================
     * Building the query
     * ================================================================== */

    /**
     * The filters, each prepared, each ignored when absent.
     *
     * Every value is cast or run through wpdb::prepare here rather than trusted
     * from the caller. A query layer that trusts its arguments is one careless
     * controller away from being the injection point for both workspaces at
     * once.
     */
    private static function filterClause($jsst_args, $jsst_actor) {
        $jsst_where = '';
        $jsst_ints = array(
            /* One customer's tickets, as a filter rather than as a separate
               query. Both queues have a "show me this person's tickets" link
               that used to append its own clause, which meant the tab counts
               beside the list were counted without it and claimed numbers the
               list could not show. (Roadmap 4.5-UX-01) */
            'uid'          => 'ticket.uid',
            'departmentid' => 'ticket.departmentid',
            'priorityid'   => 'ticket.priorityid',
            'helptopicid'  => 'ticket.helptopicid',
            'productid'    => 'ticket.productid',
            'status'       => 'ticket.status',
        );
        foreach ($jsst_ints as $jsst_key => $jsst_column) {
            if (isset($jsst_args[$jsst_key]) && $jsst_args[$jsst_key] !== '' && is_numeric($jsst_args[$jsst_key])) {
                $jsst_where .= ' AND ' . $jsst_column . ' = ' . (int) $jsst_args[$jsst_key] . ' ';
            }
        }
        /* A list of agents rather than one. The team queue asks for it - "who
           on my team is carrying this" is a question about several people -
           and every id is cast before it reaches the clause, because a filter
           that takes a list from a request is the one place an int cast is
           easy to forget. An empty list after casting narrows to nothing
           rather than widening to everything, which is the safe direction for
           a filter that failed to parse. (Roadmap 4.5-FE-05) */
        /* The tickets this agent is ON - following, or put there as a second
           pair of hands. (Roadmap 4.5-FE-08)

           `JSSTcollab::watchedBy()` existed and nothing called it, so being
           added to a ticket produced a row in `js_ticket_watchers` that no
           screen ever read: the agent was told nothing and the ticket appeared
           in no list. This is the read side.

           A subquery rather than a join, because the queue's COUNT and its
           SELECT share this WHERE and a join would multiply rows in one of
           them. Scope still applies - it is ANDed with
           `ticketScopeClause()` above, so being on a ticket does not let
           anybody see one their scope does not already reach. That is
           deliberate: access is decided by Visibility Rules, and a watcher
           list is not an access list.

           Not being on the agent list narrows to nothing rather than widening
           to everything, the same safe direction `staffids` takes. */
        if (!empty($jsst_args['following'])) {
            $jsst_mestaff = (int) $jsst_actor['staffid'];
            $jsst_where .= ($jsst_mestaff > 0 && class_exists('JSSTcollab'))
                ? ' AND ticket.id IN (SELECT w.ticketid FROM `' . jssupportticket::$_db->prefix
                    . 'js_ticket_watchers` AS w WHERE w.staffid = ' . $jsst_mestaff . ') '
                : ' AND 1 = 0 ';
        }
        if (isset($jsst_args['staffids'])) {
            $jsst_staffids = array();
            foreach ((array) $jsst_args['staffids'] as $jsst_one) {
                if (is_numeric($jsst_one) && (int) $jsst_one > 0) {
                    $jsst_staffids[] = (int) $jsst_one;
                }
            }
            $jsst_where .= !empty($jsst_staffids)
                ? ' AND ticket.staffid IN (' . implode(',', $jsst_staffids) . ') '
                : ' AND 1 = 0 ';
        }
        if (isset($jsst_args['staffid']) && $jsst_args['staffid'] !== '' && is_numeric($jsst_args['staffid'])) {
            $jsst_staffid = (int) $jsst_args['staffid'];
            $jsst_mine = ($jsst_staffid === -1);
            if ($jsst_mine) {
                // "Mine", without the caller needing to know its own staff id.
                $jsst_staffid = (int) $jsst_actor['staffid'];
            }
            if ($jsst_mine && $jsst_staffid <= 0) {
                /* Asking for "assigned to me" as somebody who is not on the
                   agent list. Nothing is assigned to them - they are not
                   assignable - and the honest answer is an empty list. Falling
                   through to the zero branch below would have answered with the
                   unassigned queue instead, which is not merely wrong but
                   confidently wrong: an administrator's desk home would show
                   every unclaimed ticket in the site under the heading "your
                   workload". (Roadmap 4.5-FE-02) */
                $jsst_where .= ' AND 1 = 0 ';
            } else {
                $jsst_where .= ($jsst_staffid > 0)
                    ? ' AND ticket.staffid = ' . $jsst_staffid . ' '
                    : ' AND (ticket.staffid IS NULL OR ticket.staffid = 0) ';
            }
        }
        /* Past its due date, or about to be. Two filters rather than one
           because they are two questions - what have I already missed, and
           what will I miss this afternoon - and a home screen shows them side
           by side. (Roadmap 4.5-FE-02)

           `breached` reads the due date itself as well as the overdue flag.
           The flag is written by the Overdue module's scheduled run, so on a
           site without that module - or between two runs of it - a ticket can
           be hours past its date with the flag still clear, and a panel that
           trusted the flag alone would report nothing wrong. Whether the
           ticket is still open is the tab's business, not this filter's, so a
           caller wanting open breaches asks for both. */
        if (!empty($jsst_args['breached'])) {
            $jsst_where .= jssupportticket::$_db->prepare(
                " AND (ticket.isoverdue = 1 OR (ticket.duedate IS NOT NULL"
                . " AND ticket.duedate != '0000-00-00 00:00:00' AND ticket.duedate < %s)) ",
                current_time('mysql')
            );
        }
        if (isset($jsst_args['duewithin']) && is_numeric($jsst_args['duewithin'])) {
            $jsst_hours = max(1, (int) $jsst_args['duewithin']);
            $jsst_now = current_time('mysql');
            $jsst_where .= jssupportticket::$_db->prepare(
                " AND ticket.duedate IS NOT NULL AND ticket.duedate != '0000-00-00 00:00:00'"
                . ' AND ticket.duedate >= %s AND ticket.duedate <= %s ',
                $jsst_now,
                gmdate('Y-m-d H:i:s', strtotime($jsst_now) + ($jsst_hours * HOUR_IN_SECONDS))
            );
        }
        /* Everything that changed since a moment. The one filter a synchronising
           client needs and the only one that is about `updated` rather than
           `created`: an integration polling every minute asks "what have you
           touched since I last looked", and answering that with the raised date
           would miss every reply on an old ticket. Added for the REST API
           (5.0-API-01), put here rather than in it, because a filter that lives
           in one caller is a filter the other callers will each rewrite. */
        if (!empty($jsst_args['since'])) {
            $jsst_since = strtotime((string) $jsst_args['since']);
            if ($jsst_since !== false) {
                $jsst_where .= jssupportticket::$_db->prepare(
                    ' AND (ticket.updated >= %s OR ticket.created >= %s) ',
                    gmdate('Y-m-d H:i:s', $jsst_since), gmdate('Y-m-d H:i:s', $jsst_since));
            }
        }
        foreach (array('datestart' => '>=', 'dateend' => '<=') as $jsst_key => $jsst_operator) {
            if (empty($jsst_args[$jsst_key])) {
                continue;
            }
            $jsst_date = self::date($jsst_args[$jsst_key], ($jsst_key === 'dateend'));
            if ($jsst_date !== '') {
                $jsst_where .= jssupportticket::$_db->prepare(' AND ticket.created ' . $jsst_operator . ' %s ', $jsst_date);
            }
        }
        return $jsst_where;
    }

    /** A date filter, normalised, or '' if it was not one. */
    private static function date($jsst_value, $jsst_endofday) {
        $jsst_time = strtotime((string) $jsst_value);
        if ($jsst_time === false) {
            return '';
        }
        return gmdate('Y-m-d', $jsst_time) . ($jsst_endofday ? ' 23:59:59' : ' 00:00:00');
    }

    /**
     * ORDER BY, from a fixed list.
     *
     * A sort column arriving from a request is a column name going into SQL,
     * so it is matched against this map and never concatenated. An unknown one
     * sorts by last activity, which is what a queue wants anyway.
     */
    private static function orderClause($jsst_args) {
        $jsst_columns = array(
            'id'       => 'ticket.id',
            'ticketid' => 'ticket.ticketid',
            'subject'  => 'ticket.subject',
            'status'   => 'ticket.status',
            'priority' => 'ticket.priorityid',
            'created'  => 'ticket.created',
            'updated'  => 'ticket.updated',
            'lastreply'=> 'ticket.lastreply',
            'duedate'  => 'ticket.duedate',
        );
        $jsst_key = isset($jsst_args['orderby']) ? (string) $jsst_args['orderby'] : 'updated';
        $jsst_column = isset($jsst_columns[$jsst_key]) ? $jsst_columns[$jsst_key] : 'ticket.updated';
        $jsst_dir = (isset($jsst_args['order']) && strtoupper((string) $jsst_args['order']) === 'ASC') ? 'ASC' : 'DESC';
        return ' ORDER BY ' . $jsst_column . ' ' . $jsst_dir . ', ticket.id ' . $jsst_dir . ' ';
    }

    /** A page size the caller asked for, within what the server will do. */
    private static function limit($jsst_args) {
        $jsst_limit = isset($jsst_args['limit']) ? (int) $jsst_args['limit'] : 25;
        if ($jsst_limit < 1) {
            $jsst_limit = 25;
        }
        return min($jsst_limit, self::MAX_LIMIT);
    }

    /** Whoever the caller says is asking, or whoever is on the request. */
    private static function actor($jsst_args) {
        if (isset($jsst_args['actor']) && is_array($jsst_args['actor'])) {
            return $jsst_args['actor'];
        }
        if (isset($jsst_args['wpuid'])) {
            return JSSTcapability::actor((int) $jsst_args['wpuid']);
        }
        return JSSTcapability::actor();
    }

    /** The shape a refused query returns, carrying the reason with it. */
    private static function refused($jsst_error) {
        return array(
            'ok'    => false,
            'error' => $jsst_error,
            'rows'  => array(),
            'total' => 0,
        );
    }
}
