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
if (class_exists('JSSTcapability')) {
    return;
}

/**
 * The Shared Capability Service. (Roadmap 4.5-ARCH-01)
 *
 * Every workspace this product ships — the admin screens, the backend agent
 * pages, the frontend portal, the customer's own ticket list, a guest holding a
 * tracking token, the REST surface and the automations that act with nobody
 * signed in — has until now answered "may this happen?" for itself. Four
 * screens, four answers, and the answers drift: a button hidden on one screen
 * is a working POST on another, which is how a help desk grows an IDOR.
 *
 * This class is the one place that answers. It takes an actor, an action and
 * the thing being acted on, and returns a decision — not a hint for the user
 * interface. Hiding a button is presentation; this is enforcement, and a
 * command that does not pass through here is not enforced at all.
 *
 * Three properties matter more than the rules themselves:
 *
 *   - It answers for *any* actor, not only the signed-in one. Every previous
 *     check in this codebase reads the current user somewhere in its middle,
 *     which makes "what could Sara do with this ticket?" unanswerable without
 *     becoming Sara. The permission inspector (4.5-ARCH-04) exists because this
 *     one takes the actor as an argument.
 *   - It fails closed. An action nobody has described is denied, an actor that
 *     cannot be resolved is a guest, and a subject that cannot be loaded denies
 *     every subject-scoped action against it.
 *   - It explains itself. Every decision carries the ordered trace of the
 *     checks that produced it, so a support conversation about permissions is a
 *     screenshot rather than an afternoon.
 *
 * It deliberately does not replace the two permission systems underneath it —
 * the WordPress capabilities reconciled by JSSTroles, and the per-agent task
 * grants of the Agents add-on. It is the single place that knows how to ask
 * them both, in the right order, for the right actor.
 */
class JSSTcapability {

    /* ---------------------------------------------------------------------
     * Actor kinds
     * ------------------------------------------------------------------ */

    /** Automation, cron, email piping — code acting with no human behind it. */
    const ACTOR_SYSTEM   = 'system';
    /** Holds manage_options or CAP_ADMIN: may act on any ticket on the site. */
    const ACTOR_ADMIN    = 'admin';
    /** On the Agents list, or holding a help-desk working capability. */
    const ACTOR_AGENT    = 'agent';
    /** A signed-in user who is not an agent — the person raising tickets. */
    const ACTOR_CUSTOMER = 'customer';
    /** Nobody is signed in. Reaches a ticket only by tracking id and token. */
    const ACTOR_GUEST    = 'guest';

    /* ---------------------------------------------------------------------
     * Visibility scopes — how much of the queue an actor may see
     * ------------------------------------------------------------------ */

    const SCOPE_ALL        = 'all';
    const SCOPE_DEPARTMENT = 'department';
    const SCOPE_ASSIGNED   = 'assigned';
    const SCOPE_OWN        = 'own';
    const SCOPE_NONE       = 'none';

    /* ---------------------------------------------------------------------
     * Actions. Namespaced by the thing they act on, so a listing is readable
     * and a new module adds a namespace rather than a prefix nobody expects.
     * ------------------------------------------------------------------ */

    const TICKET_VIEW       = 'ticket.view';
    const TICKET_CREATE     = 'ticket.create';
    const TICKET_CREATE_FOR = 'ticket.create_on_behalf';
    const TICKET_REPLY      = 'ticket.reply';
    const TICKET_NOTE       = 'ticket.note';
    const TICKET_EDIT       = 'ticket.edit';
    const REPLY_EDIT        = 'reply.edit';
    const TICKET_STATUS     = 'ticket.status';
    const TICKET_CLOSE      = 'ticket.close';
    const TICKET_REOPEN     = 'ticket.reopen';
    const TICKET_PRIORITY   = 'ticket.priority';
    const TICKET_ASSIGN     = 'ticket.assign';
    const TICKET_TRANSFER   = 'ticket.transfer';
    const TICKET_PROGRESS   = 'ticket.progress';
    const TICKET_LOCK       = 'ticket.lock';
    const TICKET_MERGE      = 'ticket.merge';
    const TICKET_DELETE     = 'ticket.delete';
    const TICKET_EXPORT     = 'ticket.export';
    const TICKET_PRINT      = 'ticket.print';
    const QUEUE_VIEW        = 'queue.view';
    const QUEUE_VIEW_ALL    = 'queue.view_all';
    const CUSTOMER_VIEW     = 'customer.view';
    const COMPANY_MANAGE    = 'company.manage';
    const REPORT_VIEW       = 'report.view';
    const KB_VIEW           = 'kb.view';
    const KB_AUTHOR         = 'kb.author';
    const CANNED_VIEW       = 'canned.view';
    const AI_REPLY          = 'ai.reply';
    const SETTINGS_MANAGE   = 'settings.manage';
    const AGENT_MANAGE      = 'agent.manage';

    /* ---------------------------------------------------------------------
     * Per-request state
     * ------------------------------------------------------------------ */

    /** Resolved actors, keyed by WordPress user id. */
    private static $jsst_actors = array();

    /** Decisions already reached this request, keyed by actor|action|subject. */
    private static $jsst_decisions = array();

    /** Minimal ticket rows, keyed by ticket id. false = looked for, not found. */
    private static $jsst_tickets = array();

    /** Add-on task answers, keyed by "<staff id>|<task>". */
    private static $jsst_tasks = array();

    /** Department scope, keyed by staff id. */
    private static $jsst_departments = array();

    /** Nesting depth of asSystem(). Above zero, the actor is automation. */
    private static $jsst_system_depth = 0;

    /* =====================================================================
     * The action catalogue
     * ================================================================== */

    /**
     * Every action this product can be asked to authorise, and how it is
     * decided. One row per action:
     *
     *   label      Shown in the inspector. Translated at read time.
     *   group      For grouping in the inspector only.
     *   cap        The WordPress capability that grants it, from JSSTroles.
     *              Empty means the action needs no capability of its own.
     *   task       The Agents add-on's task name for the same thing, where the
     *              add-on has one. Empty means the add-on does not govern it,
     *              and the capability answers alone even for its agents.
     *   scoped     Whether the answer depends on *which* ticket. A scoped
     *              action with no ticket in the subject is answered as "could
     *              this actor do this to some ticket", which is what a list
     *              screen needs when deciding whether to offer a bulk action.
     *   customer   What a signed-in non-agent may do: false, true (any), or
     *              'own' (only on a ticket they raised).
     *   guest      What somebody with no account may do, same values. A guest
     *              'own' means holding the ticket's tracking token.
     *   admin_only Administration, never delegated by a working capability.
     *
     * Filterable so a module can register its own actions — a module that adds
     * a verb must add it here rather than inventing a private check, which is
     * the whole point of the service.
     */
    public static function actions() {
        static $jsst_catalogue = null;
        if ($jsst_catalogue !== null) {
            return $jsst_catalogue;
        }
        $jsst_catalogue = array(
            self::TICKET_VIEW => array(
                'label' => __('View a ticket', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_TICKETS, 'task' => 'View Ticket',
                'scoped' => true, 'customer' => 'own', 'guest' => 'own',
            ),
            self::TICKET_CREATE => array(
                'label' => __('Raise a ticket', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => '', 'task' => '',
                'scoped' => false, 'customer' => true, 'guest' => 'config',
            ),
            self::TICKET_CREATE_FOR => array(
                'label' => __('Raise a ticket for somebody else', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_TICKETS, 'task' => 'View Ticket',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_REPLY => array(
                'label' => __('Answer the customer', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_REPLY, 'task' => 'Reply Ticket',
                'scoped' => true, 'customer' => 'own', 'guest' => 'own',
            ),
            self::TICKET_NOTE => array(
                'label' => __('Write an internal note', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_NOTE, 'task' => 'Post Internal Note',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_EDIT => array(
                'label' => __('Amend the ticket itself', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_EDIT, 'task' => 'Edit Ticket',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::REPLY_EDIT => array(
                'label' => __('Amend a reply already sent', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_EDIT, 'task' => 'Edit Reply',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_STATUS => array(
                'label' => __('Change the status', 'js-support-ticket'), 'group' => 'state',
                'cap' => JSSTroles::CAP_STATE, 'task' => 'Change Ticket Status',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_CLOSE => array(
                'label' => __('Close a ticket', 'js-support-ticket'), 'group' => 'state',
                'cap' => JSSTroles::CAP_STATE, 'task' => 'Close Ticket',
                'scoped' => true, 'customer' => 'own', 'guest' => false,
            ),
            self::TICKET_REOPEN => array(
                'label' => __('Reopen a ticket', 'js-support-ticket'), 'group' => 'state',
                'cap' => JSSTroles::CAP_STATE, 'task' => 'Reopen Ticket',
                'scoped' => true, 'customer' => 'own', 'guest' => false,
            ),
            self::TICKET_PRIORITY => array(
                'label' => __('Change the priority', 'js-support-ticket'), 'group' => 'state',
                'cap' => JSSTroles::CAP_STATE, 'task' => 'Change Ticket Priority',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_ASSIGN => array(
                'label' => __('Assign to an agent', 'js-support-ticket'), 'group' => 'state',
                'cap' => JSSTroles::CAP_STATE, 'task' => 'Assign Ticket To Agent',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_TRANSFER => array(
                'label' => __('Transfer to another department', 'js-support-ticket'), 'group' => 'state',
                'cap' => JSSTroles::CAP_STATE, 'task' => 'Ticket Department Transfer',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_PROGRESS => array(
                'label' => __('Mark in progress', 'js-support-ticket'), 'group' => 'state',
                'cap' => JSSTroles::CAP_STATE, 'task' => 'Mark In Progress',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_LOCK => array(
                'label' => __('Lock or unlock a ticket', 'js-support-ticket'), 'group' => 'state',
                'cap' => JSSTroles::CAP_STATE, 'task' => '',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_MERGE => array(
                'label' => __('Merge tickets', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_MERGE, 'task' => 'Ticket Merge',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_DELETE => array(
                'label' => __('Delete a ticket', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_DELETE, 'task' => 'Delete Ticket',
                'scoped' => true, 'customer' => 'own', 'guest' => false,
            ),
            self::TICKET_EXPORT => array(
                'label' => __('Export tickets', 'js-support-ticket'), 'group' => 'queue',
                'cap' => JSSTroles::CAP_TICKETS, 'task' => 'Export Ticket',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            self::TICKET_PRINT => array(
                'label' => __('Print a ticket', 'js-support-ticket'), 'group' => 'ticket',
                'cap' => JSSTroles::CAP_TICKETS, 'task' => 'Print Ticket',
                'scoped' => true, 'customer' => 'own', 'guest' => false,
            ),
            self::QUEUE_VIEW => array(
                'label' => __('Open the queue', 'js-support-ticket'), 'group' => 'queue',
                'cap' => JSSTroles::CAP_TICKETS, 'task' => '',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            self::QUEUE_VIEW_ALL => array(
                'label' => __('See every ticket on the site', 'js-support-ticket'), 'group' => 'queue',
                'cap' => JSSTroles::CAP_ADMIN, 'task' => 'All Tickets',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            self::CUSTOMER_VIEW => array(
                'label' => __('See customer records', 'js-support-ticket'), 'group' => 'people',
                'cap' => JSSTroles::CAP_TICKETS, 'task' => 'View User',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            /* Writing down who a customer works for. Separated from reading
               the customer list because a company record decides what a
               supervisor may read, and handing that to everybody who may open
               the Customers screen would let an agent widen a customer's
               access. (Roadmap 5.5-COM-06) */
            self::COMPANY_MANAGE => array(
                'label' => __('Manage companies', 'js-support-ticket'), 'group' => 'people',
                'cap' => JSSTroles::CAP_ADMIN, 'task' => '',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            self::REPORT_VIEW => array(
                'label' => __('Read reports', 'js-support-ticket'), 'group' => 'people',
                'cap' => JSSTroles::CAP_TICKETS, 'task' => 'View Agent Reports',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            self::KB_VIEW => array(
                'label' => __('Read the knowledge base', 'js-support-ticket'), 'group' => 'knowledge',
                'cap' => '', 'task' => '',
                'scoped' => false, 'customer' => true, 'guest' => true,
            ),
            self::KB_AUTHOR => array(
                'label' => __('Write the knowledge base', 'js-support-ticket'), 'group' => 'knowledge',
                'cap' => JSSTroles::CAP_KB, 'task' => 'Edit Knowledge Base',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            self::CANNED_VIEW => array(
                'label' => __('Use canned replies', 'js-support-ticket'), 'group' => 'knowledge',
                'cap' => JSSTroles::CAP_REPLY, 'task' => 'View Canned Response',
                'scoped' => false, 'customer' => false, 'guest' => false,
            ),
            self::AI_REPLY => array(
                'label' => __('Use AI drafting', 'js-support-ticket'), 'group' => 'knowledge',
                'cap' => JSSTroles::CAP_REPLY, 'task' => 'Use AI Powered Reply Feature',
                'scoped' => true, 'customer' => false, 'guest' => false,
            ),
            self::SETTINGS_MANAGE => array(
                'label' => __('Change help desk settings', 'js-support-ticket'), 'group' => 'admin',
                'cap' => JSSTroles::CAP_ADMIN, 'task' => '',
                'scoped' => false, 'customer' => false, 'guest' => false,
                'admin_only' => true,
            ),
            self::AGENT_MANAGE => array(
                'label' => __('Add, remove and permission agents', 'js-support-ticket'), 'group' => 'admin',
                'cap' => JSSTroles::CAP_ADMIN, 'task' => '',
                'scoped' => false, 'customer' => false, 'guest' => false,
                'admin_only' => true,
            ),
        );
        $jsst_catalogue = apply_filters('jsst_capability_actions', $jsst_catalogue);
        return $jsst_catalogue;
    }

    /** One action's definition, with the defaults filled in. False if unknown. */
    public static function definition($jsst_action) {
        $jsst_actions = self::actions();
        if (!isset($jsst_actions[$jsst_action]) || !is_array($jsst_actions[$jsst_action])) {
            return false;
        }
        /* The action's own name travels with its definition. Everything that
           receives a definition used to have to be handed the name separately,
           and anything that needed both and was given one had to guess.
           (Roadmap 4.5-FE-04) */
        return array_merge(array(
            'label' => $jsst_action, 'group' => 'other', 'cap' => '', 'task' => '',
            'scoped' => false, 'customer' => false, 'guest' => false, 'admin_only' => false,
        ), $jsst_actions[$jsst_action], array('action' => $jsst_action));
    }

    /* =====================================================================
     * Actors
     * ================================================================== */

    /**
     * Everything the service needs to know about one person, resolved once.
     *
     * Pass a WordPress user id to ask about somebody else; pass nothing for
     * whoever is on this request. Zero, or an id with no account behind it, is
     * a guest — never an error, because a guest with a tracking token is a real
     * actor in this product.
     *
     * @param int|null $jsst_wpuid
     * @return array
     */
    public static function actor($jsst_wpuid = null) {
        if ($jsst_wpuid === null) {
            /* Inside asSystem() the current request has no person behind it, so
               the current-user answer would be whoever happened to be signed in
               when the automation was queued — or nobody at all. */
            if (self::$jsst_system_depth > 0) {
                return self::systemActor();
            }
            $jsst_wpuid = (int) get_current_user_id();
        }
        $jsst_wpuid = (int) $jsst_wpuid;
        if (isset(self::$jsst_actors[$jsst_wpuid])) {
            return self::$jsst_actors[$jsst_wpuid];
        }

        $jsst_actor = array(
            'wpuid'      => $jsst_wpuid,
            'uid'        => 0,
            'staffid'    => 0,
            'kind'       => self::ACTOR_GUEST,
            'display'    => esc_html(__('Guest', 'js-support-ticket')),
            'email'      => '',
            'wp_roles'   => array(),
            'caps'       => array(),
            'governed'   => false,
            'active'     => true,
            'departments'=> array(),
            'all_tickets'=> false,
            'scope'      => self::SCOPE_NONE,
        );

        if ($jsst_wpuid > 0) {
            $jsst_user = get_userdata($jsst_wpuid);
            if ($jsst_user && !empty($jsst_user->ID)) {
                $jsst_actor['display']  = $jsst_user->display_name;
                $jsst_actor['email']    = $jsst_user->user_email;
                $jsst_actor['wp_roles'] = is_array($jsst_user->roles) ? array_values($jsst_user->roles) : array();
                $jsst_actor['kind']     = self::ACTOR_CUSTOMER;
                foreach (JSSTroles::getPluginCapabilities() as $jsst_cap) {
                    if (user_can($jsst_wpuid, $jsst_cap)) {
                        $jsst_actor['caps'][] = $jsst_cap;
                    }
                }
                $jsst_actor['uid'] = self::pluginUserId($jsst_wpuid);
                if (user_can($jsst_wpuid, 'manage_options') || user_can($jsst_wpuid, JSSTroles::CAP_ADMIN)) {
                    $jsst_actor['kind'] = self::ACTOR_ADMIN;
                } elseif (!empty($jsst_actor['caps'])) {
                    $jsst_actor['kind'] = self::ACTOR_AGENT;
                }
                $jsst_staff = self::staffRow($jsst_actor['uid']);
                if ($jsst_staff) {
                    $jsst_actor['staffid']  = (int) $jsst_staff->id;
                    $jsst_actor['governed'] = true;
                    $jsst_actor['active']   = ((int) $jsst_staff->status === 1);
                    /* Somebody on the Agents list is an agent even where no
                       WordPress capability says so: JSSTroles grants the three
                       working capabilities from that list at check time, and
                       reading the list directly is what makes this service
                       agree with what the site actually does. */
                    if ($jsst_actor['kind'] === self::ACTOR_CUSTOMER) {
                        $jsst_actor['kind'] = self::ACTOR_AGENT;
                    }
                }
            }
        }

        if ($jsst_actor['kind'] === self::ACTOR_AGENT || $jsst_actor['kind'] === self::ACTOR_ADMIN) {
            /* An agent the add-on does not govern sees the whole queue. That is
               not a decision taken here - it is what the free core has always
               done, because department scoping is the add-on's feature and
               there is nothing else to narrow the queue with. Narrowing it here
               would take away access sites already rely on; the fix is a scope
               setting in the free core, not a silent change of meaning. */
            $jsst_actor['all_tickets'] = ($jsst_actor['kind'] === self::ACTOR_ADMIN)
                ? true
                : (!$jsst_actor['governed'] || self::hasTask($jsst_actor['staffid'], 'All Tickets'));
            if ($jsst_actor['governed']) {
                $jsst_actor['departments'] = self::departmentsFor($jsst_actor['staffid']);
            }
        }
        $jsst_actor['scope'] = self::deriveScope($jsst_actor);

        $jsst_actor = apply_filters('jsst_capability_actor', $jsst_actor, $jsst_wpuid);
        self::$jsst_actors[$jsst_wpuid] = $jsst_actor;
        return $jsst_actor;
    }

    /** The actor automation runs as. Not a user, and never resolvable to one. */
    public static function systemActor() {
        return array(
            'wpuid' => 0, 'uid' => 0, 'staffid' => 0,
            'kind' => self::ACTOR_SYSTEM,
            'display' => esc_html(__('Automation', 'js-support-ticket')),
            'email' => '', 'wp_roles' => array(), 'caps' => array(),
            'governed' => false, 'active' => true, 'departments' => array(),
            'all_tickets' => true, 'scope' => self::SCOPE_ALL,
        );
    }

    /**
     * How much of the queue this actor may see, as one word.
     *
     * Read by the query layer to build a WHERE clause and by the inspector to
     * describe an agent in a sentence. Deliberately derived rather than stored:
     * a stored scope is a fifth place for the answer to drift.
     */
    private static function deriveScope($jsst_actor) {
        if ($jsst_actor['kind'] === self::ACTOR_ADMIN || $jsst_actor['kind'] === self::ACTOR_SYSTEM) {
            return self::SCOPE_ALL;
        }
        if ($jsst_actor['kind'] === self::ACTOR_AGENT) {
            if (!empty($jsst_actor['all_tickets'])) {
                return self::SCOPE_ALL;
            }
            return empty($jsst_actor['departments']) ? self::SCOPE_ASSIGNED : self::SCOPE_DEPARTMENT;
        }
        if ($jsst_actor['kind'] === self::ACTOR_CUSTOMER) {
            return self::SCOPE_OWN;
        }
        return self::SCOPE_NONE;
    }

    /** Convenience: the scope word for an actor. */
    public static function scope($jsst_wpuid = null) {
        $jsst_actor = is_array($jsst_wpuid) ? $jsst_wpuid : self::actor($jsst_wpuid);
        return $jsst_actor['scope'];
    }

    /* =====================================================================
     * The decision
     * ================================================================== */

    /**
     * May this actor do this?
     *
     * @param string     $jsst_action  One of the action constants.
     * @param array      $jsst_subject What is being acted on. 'ticket' => id.
     * @param int|array|null $jsst_who WordPress user id, a resolved actor, or
     *                                 null for whoever is on this request.
     * @return bool
     */
    public static function can($jsst_action, $jsst_subject = array(), $jsst_who = null) {
        $jsst_decision = self::explain($jsst_action, $jsst_subject, $jsst_who);
        return !empty($jsst_decision['allowed']);
    }

    /**
     * The same question, with the reasoning attached. (Roadmap 4.5-ARCH-04)
     *
     * @return array allowed, code, reason, actor, action, subject, trace
     */
    public static function explain($jsst_action, $jsst_subject = array(), $jsst_who = null) {
        $jsst_actor = is_array($jsst_who) ? $jsst_who : self::actor($jsst_who);
        $jsst_subject = is_array($jsst_subject) ? $jsst_subject : array('ticket' => $jsst_subject);
        $jsst_ticketid = isset($jsst_subject['ticket']) ? (int) $jsst_subject['ticket'] : 0;

        /* The token is part of the key, not just its presence. Keying on
           "a token was supplied" made the first correct token answer for every
           later one on the same ticket, so a guest who had once produced a
           valid link was let in with any string at all. It is hashed rather
           than used raw so that a token never sits in a key that could be
           dumped into a log or a debug bundle. */
        $jsst_key = $jsst_actor['kind'] . '#' . (int) $jsst_actor['wpuid'] . '|' . $jsst_action . '|' . $jsst_ticketid
            . '|' . (isset($jsst_subject['token']) ? md5((string) $jsst_subject['token']) : '-');
        if (isset(self::$jsst_decisions[$jsst_key])) {
            return self::$jsst_decisions[$jsst_key];
        }

        $jsst_decision = self::evaluate($jsst_action, $jsst_subject, $jsst_actor, $jsst_ticketid);

        /* The extension point, and the only way a third party may change an
           answer. It runs last so that a module cannot be bypassed by ordering,
           and the trace records that it ran — an answer nobody in this file
           produced is exactly what somebody debugging needs to be told. */
        $jsst_filtered = apply_filters('jsst_capability_decision', $jsst_decision, $jsst_action, $jsst_subject, $jsst_actor);
        if (is_array($jsst_filtered) && isset($jsst_filtered['allowed'])
            && (bool) $jsst_filtered['allowed'] !== (bool) $jsst_decision['allowed']) {
            $jsst_filtered['trace'][] = array(
                'step'   => 'filter',
                'result' => !empty($jsst_filtered['allowed']),
                'detail' => esc_html(__('Changed by the jsst_capability_decision filter — something outside the help desk is deciding this.', 'js-support-ticket')),
            );
            $jsst_filtered['code'] = 'filtered';
            $jsst_decision = $jsst_filtered;
        }

        self::$jsst_decisions[$jsst_key] = $jsst_decision;
        return $jsst_decision;
    }

    /**
     * The rules, in order. Each step appends to the trace before returning, so
     * a decision always says which check ended it.
     */
    private static function evaluate($jsst_action, $jsst_subject, $jsst_actor, $jsst_ticketid) {
        $jsst_trace = array();
        $jsst_def = self::definition($jsst_action);

        if ($jsst_def === false) {
            /* Fail closed. An action nobody described is either a typo in a
               call site or a module that was switched off mid-request; both are
               better as a denial than as a silent allow. */
            return self::deny($jsst_action, $jsst_subject, $jsst_actor, 'unknown_action',
                __('This action is not one the help desk knows about, so it is refused.', 'js-support-ticket'),
                $jsst_trace);
        }

        $jsst_trace[] = array(
            'step' => 'actor', 'result' => true,
            'detail' => sprintf(
                /* translators: 1: the person's name, 2: what kind of actor they are */
                __('%1$s is acting as: %2$s.', 'js-support-ticket'),
                $jsst_actor['display'], self::kindLabel($jsst_actor['kind'])
            ),
        );

        // Automation. Not a permission holder — a trusted caller by construction.
        if ($jsst_actor['kind'] === self::ACTOR_SYSTEM) {
            if (!empty($jsst_def['admin_only'])) {
                return self::deny($jsst_action, $jsst_subject, $jsst_actor, 'system_not_admin',
                    __('Automation may work tickets but may not change settings or agents.', 'js-support-ticket'),
                    $jsst_trace);
            }
            return self::allow($jsst_action, $jsst_subject, $jsst_actor, 'system',
                __('Automation runs with no person behind it and is trusted by the code that started it.', 'js-support-ticket'),
                $jsst_trace);
        }

        // A disabled agent keeps their WordPress role but stops being staff.
        if (empty($jsst_actor['active'])) {
            return self::deny($jsst_action, $jsst_subject, $jsst_actor, 'agent_disabled',
                __('This agent is switched off on the Agents screen.', 'js-support-ticket'),
                $jsst_trace);
        }

        if ($jsst_actor['kind'] === self::ACTOR_ADMIN) {
            $jsst_trace[] = array(
                'step' => 'administrator', 'result' => true,
                'detail' => __('Holds the help desk administration capability, which covers every ticket on the site.', 'js-support-ticket'),
            );
            return self::allow($jsst_action, $jsst_subject, $jsst_actor, 'administrator',
                __('Help desk administrators may do this.', 'js-support-ticket'), $jsst_trace);
        }

        if (!empty($jsst_def['admin_only'])) {
            return self::deny($jsst_action, $jsst_subject, $jsst_actor, 'admin_only',
                __('Only a help desk administrator may do this.', 'js-support-ticket'), $jsst_trace);
        }

        if ($jsst_actor['kind'] === self::ACTOR_GUEST) {
            return self::guestDecision($jsst_action, $jsst_def, $jsst_subject, $jsst_actor, $jsst_ticketid, $jsst_trace);
        }

        if ($jsst_actor['kind'] === self::ACTOR_CUSTOMER) {
            return self::customerDecision($jsst_action, $jsst_def, $jsst_subject, $jsst_actor, $jsst_ticketid, $jsst_trace);
        }

        return self::agentDecision($jsst_action, $jsst_def, $jsst_subject, $jsst_actor, $jsst_ticketid, $jsst_trace);
    }

    /**
     * A guest holds no account, so the only thing that can grant them anything
     * is the ticket's own tracking token — which the caller has to have checked
     * and passed in. It is never read from the request here: a service that
     * reads its own credentials out of $_GET cannot be reasoned about.
     */
    /**
     * Does this site accept tickets from somebody with no account?
     *
     * `visitor_can_create_ticket`, which is the setting the Configuration screen
     * writes, `activation.php` seeds and `modules/ticket/tpls/addticket.php`
     * has always enforced. This asked for `allowguest` instead - a name that
     * appears nowhere else in this product, is written by nothing and has no row
     * in `js_ticket_config` - so `getConfigValue()` returned null, `null == 1`
     * was false, and the answer was "switched off" on every site there has ever
     * been, whatever the setting said.
     *
     * It is not only the Permission Inspector that read it. `JSSTticketservice::create()`
     * and the REST `POST /tickets` endpoint both gate on this action, so on a
     * desk that does accept guest tickets through its own form, the same guest
     * was refused by the API - two doors into one action answering differently,
     * which is exactly the disagreement this service exists to end.
     *
     * Read from the loaded configuration rather than queried: this is asked once
     * per action while a list screen decides which buttons to draw, and the
     * value is already in memory. The query is the fallback for a caller early
     * enough that it is not. (Roadmap 4.5-SEC-04)
     */
    private static function guestTicketsOn() {
        if (isset(jssupportticket::$_config['visitor_can_create_ticket'])) {
            return jssupportticket::$_config['visitor_can_create_ticket'] == 1;
        }
        return JSSTincluder::getJSModel('configuration')->getConfigValue('visitor_can_create_ticket') == 1;
    }

    private static function guestDecision($jsst_action, $jsst_def, $jsst_subject, $jsst_actor, $jsst_ticketid, $jsst_trace) {
        $jsst_allowed = $jsst_def['guest'];
        if ($jsst_allowed === 'config') {
            $jsst_guestok = self::guestTicketsOn();
            $jsst_trace[] = array(
                'step' => 'guest_tickets', 'result' => $jsst_guestok,
                'detail' => $jsst_guestok
                    ? __('Guest tickets are switched on.', 'js-support-ticket')
                    : __('Guest tickets are switched off in the settings.', 'js-support-ticket'),
            );
            return $jsst_guestok
                ? self::allow($jsst_action, $jsst_subject, $jsst_actor, 'guest_config',
                    __('This site accepts tickets from people without an account.', 'js-support-ticket'), $jsst_trace)
                : self::deny($jsst_action, $jsst_subject, $jsst_actor, 'guest_config',
                    __('This site does not accept tickets from people without an account.', 'js-support-ticket'), $jsst_trace);
        }
        if ($jsst_allowed === true) {
            return self::allow($jsst_action, $jsst_subject, $jsst_actor, 'public',
                __('This is public.', 'js-support-ticket'), $jsst_trace);
        }
        if ($jsst_allowed !== 'own') {
            return self::deny($jsst_action, $jsst_subject, $jsst_actor, 'guest',
                __('Nobody may do this without signing in.', 'js-support-ticket'), $jsst_trace);
        }
        // 'own' for a guest means one thing only: they produced the token.
        $jsst_hastoken = !empty($jsst_subject['token']) && $jsst_ticketid > 0
            && self::tokenMatches($jsst_ticketid, (string) $jsst_subject['token']);
        $jsst_trace[] = array(
            'step' => 'tracking_token', 'result' => $jsst_hastoken,
            'detail' => $jsst_hastoken
                ? __('The tracking token for this ticket was supplied and matches.', 'js-support-ticket')
                : __('No matching tracking token was supplied for this ticket.', 'js-support-ticket'),
        );
        return $jsst_hastoken
            ? self::allow($jsst_action, $jsst_subject, $jsst_actor, 'guest_token',
                __('Holding the ticket\'s tracking token is what stands in for signing in.', 'js-support-ticket'), $jsst_trace)
            : self::deny($jsst_action, $jsst_subject, $jsst_actor, 'guest_token',
                __('A guest reaches a ticket only with its tracking token.', 'js-support-ticket'), $jsst_trace);
    }

    /** A signed-in person who is not staff: their own tickets, and nothing else. */
    private static function customerDecision($jsst_action, $jsst_def, $jsst_subject, $jsst_actor, $jsst_ticketid, $jsst_trace) {
        $jsst_allowed = $jsst_def['customer'];
        if ($jsst_allowed === false) {
            return self::deny($jsst_action, $jsst_subject, $jsst_actor, 'customer',
                __('This is agent work, and this person is not an agent.', 'js-support-ticket'), $jsst_trace);
        }
        if ($jsst_allowed === true) {
            return self::allow($jsst_action, $jsst_subject, $jsst_actor, 'customer',
                __('Anybody signed in may do this.', 'js-support-ticket'), $jsst_trace);
        }
        // 'own'
        if ($jsst_ticketid <= 0) {
            /* No ticket named. The honest answer to "may this customer reply?"
               with no ticket in hand is yes — on one of theirs — and a list
               screen asking whether to render the button needs that answer. */
            return self::allow($jsst_action, $jsst_subject, $jsst_actor, 'customer_own',
                __('Allowed on tickets this person raised.', 'js-support-ticket'), $jsst_trace);
        }
        $jsst_owns = self::ownsTicket($jsst_actor, $jsst_ticketid);
        /* A company supervisor may read and print a colleague's ticket, and
           nothing more: every other 'own' action still needs the owner.
           (Roadmap 5.5-COM-06) */
        if (!$jsst_owns && ($jsst_action === self::TICKET_VIEW || $jsst_action === self::TICKET_PRINT)
                && class_exists('JSSTcompanies')) {
            $jsst_ticket = self::ticket($jsst_ticketid);
            if ($jsst_ticket && JSSTcompanies::supervisorReads($jsst_actor['email'], $jsst_ticket->email)) {
                $jsst_trace[] = array('step' => 'ownership', 'result' => true,
                    'detail' => __('This person supervises the company that raised the ticket.', 'js-support-ticket'));
                return self::allow($jsst_action, $jsst_subject, $jsst_actor, 'company_supervisor',
                    __('A company supervisor may read their colleagues\' tickets.', 'js-support-ticket'), $jsst_trace);
            }
        }
        $jsst_trace[] = array(
            'step' => 'ownership', 'result' => $jsst_owns,
            'detail' => $jsst_owns
                ? __('This person raised the ticket.', 'js-support-ticket')
                : __('The ticket belongs to somebody else.', 'js-support-ticket'),
        );
        return $jsst_owns
            ? self::allow($jsst_action, $jsst_subject, $jsst_actor, 'customer_own',
                __('People may act on the tickets they raised.', 'js-support-ticket'), $jsst_trace)
            : self::deny($jsst_action, $jsst_subject, $jsst_actor, 'not_owner',
                __('A customer may only act on their own tickets.', 'js-support-ticket'), $jsst_trace);
    }

    /**
     * Staff. Two gates in order, and both have to pass: the capability gate
     * says what kind of work this agent does, the scope gate says which tickets
     * they do it on. Passing the first and failing the second is the ordinary
     * case — an agent who may close tickets, but not this one.
     */
    private static function agentDecision($jsst_action, $jsst_def, $jsst_subject, $jsst_actor, $jsst_ticketid, $jsst_trace) {
        $jsst_capok = self::agentHoldsCapability($jsst_def, $jsst_actor, $jsst_trace);
        if (!$jsst_capok) {
            return self::deny($jsst_action, $jsst_subject, $jsst_actor, 'no_capability',
                __('This agent has not been given this kind of work.', 'js-support-ticket'), $jsst_trace);
        }
        if (empty($jsst_def['scoped']) || $jsst_ticketid <= 0) {
            return self::allow($jsst_action, $jsst_subject, $jsst_actor, 'agent',
                __('This agent has been given this kind of work.', 'js-support-ticket'), $jsst_trace);
        }
        $jsst_inscope = self::ticketInScope($jsst_actor, $jsst_ticketid, $jsst_trace);
        return $jsst_inscope
            ? self::allow($jsst_action, $jsst_subject, $jsst_actor, 'agent_scope',
                __('The ticket is within this agent\'s scope.', 'js-support-ticket'), $jsst_trace)
            : self::deny($jsst_action, $jsst_subject, $jsst_actor, 'out_of_scope',
                __('The ticket is outside this agent\'s scope — it is neither theirs nor in a department they cover.', 'js-support-ticket'), $jsst_trace);
    }

    /**
     * The capability gate, with the Agents add-on winning wherever it governs.
     *
     * This is JSSTroles::resolveAgainstAddon()'s rule, restated for an actor
     * who is not necessarily the current user: the add-on is the finer-grained
     * system and the one an administrator sets per agent, so where it governs
     * somebody it answers alone. A capability arriving from a WordPress role
     * must never quietly re-grant what was switched off agent by agent.
     *
     * @param array $jsst_trace Appended to by reference.
     */
    private static function agentHoldsCapability($jsst_def, $jsst_actor, &$jsst_trace) {
        /* Customer records and reports can be answered per agent, above both
           permission systems, because they are the two things a team most
           often wants to hand out separately from ticket work - somebody
           producing figures who should not be reading conversations, somebody
           working tickets who should not be browsing the customer list. The
           override is three-valued and only an explicit yes or no is acted on;
           an agent nobody has decided about falls through to the two systems
           exactly as before. (Roadmap 4.5-FE-04) */
        if (class_exists('JSSTvisibility') && isset($jsst_def['action'])) {
            $jsst_override = JSSTvisibility::permission($jsst_def['action'], $jsst_actor);
            if ($jsst_override !== null) {
                $jsst_trace[] = array(
                    'step' => 'visibility', 'result' => (bool) $jsst_override,
                    'detail' => $jsst_override
                        ? __('Granted to this agent on the Visibility screen.', 'js-support-ticket')
                        : __('Withheld from this agent on the Visibility screen.', 'js-support-ticket'),
                );
                return (bool) $jsst_override;
            }
        }
        if ($jsst_def['cap'] === '' && $jsst_def['task'] === '') {
            $jsst_trace[] = array(
                'step' => 'capability', 'result' => true,
                'detail' => __('This action needs no capability of its own.', 'js-support-ticket'),
            );
            return true;
        }
        if (!empty($jsst_actor['governed']) && $jsst_def['task'] !== '') {
            $jsst_has = self::hasTask($jsst_actor['staffid'], $jsst_def['task']);
            $jsst_trace[] = array(
                'step' => 'agent_permission', 'result' => $jsst_has,
                'detail' => sprintf(
                    /* translators: 1: the permission name on the Agents screen, 2: granted or not granted */
                    __('Agent permission "%1$s": %2$s.', 'js-support-ticket'),
                    $jsst_def['task'],
                    $jsst_has ? __('granted', 'js-support-ticket') : __('not granted', 'js-support-ticket')
                ),
            );
            return $jsst_has;
        }
        if (!empty($jsst_actor['governed']) && $jsst_def['task'] === '') {
            /* The add-on has no permission for this action, so it cannot answer.
               Falling through to the capability is the only alternative to
               denying work the add-on never intended to withhold. */
            $jsst_trace[] = array(
                'step' => 'agent_permission', 'result' => true,
                'detail' => __('The Agents screen has no permission covering this, so the WordPress capability decides.', 'js-support-ticket'),
            );
        }
        if ($jsst_def['cap'] === '') {
            return true;
        }
        $jsst_has = user_can($jsst_actor['wpuid'], $jsst_def['cap']);
        $jsst_trace[] = array(
            'step' => 'capability', 'result' => $jsst_has,
            'detail' => sprintf(
                /* translators: 1: a WordPress capability name, 2: held or not held */
                __('WordPress capability %1$s: %2$s.', 'js-support-ticket'),
                $jsst_def['cap'],
                $jsst_has ? __('held', 'js-support-ticket') : __('not held', 'js-support-ticket')
            ),
        );
        return $jsst_has;
    }

    /**
     * Is this ticket inside the agent's scope?
     *
     * The same three ways in that the queue has always used — assigned to me,
     * in a department I cover, or raised by me — expressed once so that the
     * list query and the single-ticket check can never disagree. That
     * disagreement is the IDOR: a ticket missing from an agent's list that
     * still opens when its id is typed into the address bar.
     */
    private static function ticketInScope($jsst_actor, $jsst_ticketid, &$jsst_trace) {
        if (!empty($jsst_actor['all_tickets'])) {
            $jsst_trace[] = array(
                'step' => 'scope', 'result' => true,
                'detail' => __('This agent may see every ticket on the site.', 'js-support-ticket'),
            );
            return true;
        }
        $jsst_ticket = self::ticket($jsst_ticketid);
        if (!$jsst_ticket) {
            $jsst_trace[] = array(
                'step' => 'scope', 'result' => false,
                'detail' => __('That ticket does not exist.', 'js-support-ticket'),
            );
            return false;
        }
        /* A per-agent visibility record answers first, and answers with the
           same rule ticketScopeClause() applies to the list - so a ticket
           missing from an agent's queue is a ticket that will not open either.
           Where no record governs this actor, JSSTvisibility says nothing and
           the original rule below decides. (Roadmap 4.5-FE-04) */
        if (class_exists('JSSTvisibility')) {
            $jsst_visible = JSSTvisibility::allows($jsst_actor, $jsst_ticket);
            if (is_array($jsst_visible)) {
                $jsst_trace[] = array(
                    'step' => 'scope', 'result' => !empty($jsst_visible['in']),
                    'detail' => !empty($jsst_visible['in'])
                        ? sprintf(
                            /* translators: %s: why the ticket is in scope */
                            __('The ticket is %s.', 'js-support-ticket'),
                            implode(__(', and ', 'js-support-ticket'), $jsst_visible['reasons']))
                        : sprintf(
                            /* translators: %s: a sentence describing this agent's visibility settings */
                            __('Outside what this agent is set to see: %s', 'js-support-ticket'),
                            JSSTvisibility::describe($jsst_actor['staffid'])),
                );
                return !empty($jsst_visible['in']);
            }
        }
        $jsst_reasons = array();
        if ($jsst_actor['staffid'] > 0 && (int) $jsst_ticket->staffid === (int) $jsst_actor['staffid']) {
            $jsst_reasons[] = __('assigned to this agent', 'js-support-ticket');
        }
        if (!empty($jsst_actor['departments']) && in_array((int) $jsst_ticket->departmentid, $jsst_actor['departments'], true)) {
            $jsst_reasons[] = __('in a department this agent covers', 'js-support-ticket');
        }
        if ($jsst_actor['uid'] > 0 && (int) $jsst_ticket->uid === (int) $jsst_actor['uid']) {
            $jsst_reasons[] = __('raised by this agent', 'js-support-ticket');
        }
        /* The row-at-a-time twin of the term ticketScopeClause() adds, so the
           queue and the ticket cannot disagree. (Roadmap 4.5-FE-08) */
        if (class_exists('JSSTcollab') && JSSTcollab::isInvited($jsst_ticketid, $jsst_actor['staffid'])) {
            $jsst_reasons[] = __('one this agent was put on by name', 'js-support-ticket');
        }
        $jsst_in = !empty($jsst_reasons);
        $jsst_trace[] = array(
            'step' => 'scope', 'result' => $jsst_in,
            'detail' => $jsst_in
                ? sprintf(
                    /* translators: %s: why the ticket is in scope, e.g. "assigned to this agent" */
                    __('The ticket is %s.', 'js-support-ticket'), implode(__(', and ', 'js-support-ticket'), $jsst_reasons))
                : __('The ticket is not assigned to this agent, not in a department they cover, and not one they raised.', 'js-support-ticket'),
        );
        return $jsst_in;
    }

    /* =====================================================================
     * Enforcement helpers
     * ================================================================== */

    /**
     * The form a command uses: true, or the WP_Error it should return.
     *
     * Commands call this rather than can() so that the refusal carries the
     * reason all the way back to whatever asked — an application layer that
     * answers "no" without saying why produces support tickets of its own.
     *
     * @return true|WP_Error
     */
    public static function assert($jsst_action, $jsst_subject = array(), $jsst_who = null) {
        $jsst_decision = self::explain($jsst_action, $jsst_subject, $jsst_who);
        if (!empty($jsst_decision['allowed'])) {
            return true;
        }
        /* 401 rather than 403 for a guest, because "sign in and this may work"
           and "you may not do this" are different answers, and a portal that
           conflates them sends a customer to support instead of to the login
           form. The actor comes off the decision - $jsst_who may already be a
           resolved actor array, which actor() would not know what to do with. */
        $jsst_kind = isset($jsst_decision['actor']['kind']) ? $jsst_decision['actor']['kind'] : self::ACTOR_GUEST;
        return new WP_Error(
            'jsst_forbidden_' . $jsst_decision['code'],
            $jsst_decision['reason'],
            array('status' => ($jsst_kind === self::ACTOR_GUEST) ? 401 : 403,
                  'action' => $jsst_action, 'trace' => $jsst_decision['trace'])
        );
    }

    /**
     * Run something as automation.
     *
     * Cron, email piping and the background queue act on tickets with nobody
     * signed in — and, worse, sometimes with whoever *was* signed in when the
     * job was queued. Wrapping the work says so explicitly rather than leaving
     * every command to guess. Nested calls are counted, so an automation that
     * calls another does not hand the request back to a user half way through.
     *
     * @param callable $jsst_callback
     * @return mixed Whatever the callback returned.
     */
    public static function asSystem($jsst_callback) {
        self::$jsst_system_depth++;
        try {
            return call_user_func($jsst_callback);
        } finally {
            self::$jsst_system_depth--;
            if (self::$jsst_system_depth < 0) {
                self::$jsst_system_depth = 0;
            }
        }
    }

    /** Is this request currently inside asSystem()? */
    public static function isSystem() {
        return self::$jsst_system_depth > 0;
    }

    /**
     * The WHERE fragment that limits a ticket query to what this actor may see.
     *
     * Returned already parameterised and safe to concatenate: every value in it
     * is an integer this class produced. The query layer (4.5-ARCH-02) uses it
     * so that a list and a detail check are the same rule.
     *
     * @param string $jsst_alias The ticket table's alias in the query.
     * @return string A fragment beginning with AND, or '' for unrestricted.
     */
    public static function ticketScopeClause($jsst_alias = 'ticket', $jsst_who = null) {
        $jsst_actor = is_array($jsst_who) ? $jsst_who : self::actor($jsst_who);
        $jsst_alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $jsst_alias);
        if ($jsst_alias === '') {
            $jsst_alias = 'ticket';
        }
        /* A per-agent visibility record, where one governs this actor, is the
           narrower and therefore the operative answer. It returns '' for
           anybody it has nothing to say about - an administrator, automation,
           a customer, an agent granted All Tickets - and the switch below then
           answers as it always has. (Roadmap 4.5-FE-04) */
        if (class_exists('JSSTvisibility')) {
            $jsst_visibility = JSSTvisibility::clause($jsst_alias, $jsst_actor);
            if ($jsst_visibility !== '') {
                return $jsst_visibility;
            }
        }
        switch ($jsst_actor['scope']) {
            case self::SCOPE_ALL:
                return '';
            case self::SCOPE_OWN:
                if ($jsst_actor['uid'] <= 0) {
                    return ' AND 1 = 0 ';
                }
                /* A company supervisor reads their colleagues' tickets as well
                   as their own. Everybody else - every ordinary customer, and
                   every company contact - is answered exactly as they were
                   before companies existed, because the clause below is '' for
                   all of them. It is an addition to `uid = me` and never a
                   replacement: a supervisor whose own ticket somehow fell
                   outside their company's rule still sees their own ticket.
                   (Roadmap 5.5-COM-06) */
                $jsst_shared = '';
                if (class_exists('JSSTcompanies')) {
                    $jsst_shared = JSSTcompanies::sharedScopeClause($jsst_alias, $jsst_actor);
                }
                if ($jsst_shared !== '') {
                    return ' AND (' . $jsst_alias . '.uid = ' . (int) $jsst_actor['uid']
                        . ' OR ' . $jsst_shared . ') ';
                }
                return ' AND ' . $jsst_alias . '.uid = ' . (int) $jsst_actor['uid'] . ' ';
            case self::SCOPE_DEPARTMENT:
            case self::SCOPE_ASSIGNED:
                $jsst_parts = array();
                if ($jsst_actor['staffid'] > 0) {
                    $jsst_parts[] = $jsst_alias . '.staffid = ' . (int) $jsst_actor['staffid'];
                }
                if (!empty($jsst_actor['departments'])) {
                    $jsst_parts[] = $jsst_alias . '.departmentid IN (' . implode(',', array_map('intval', $jsst_actor['departments'])) . ')';
                }
                if ($jsst_actor['uid'] > 0) {
                    $jsst_parts[] = $jsst_alias . '.uid = ' . (int) $jsst_actor['uid'];
                }
                /* A ticket somebody deliberately put this agent on.
                   (Roadmap 4.5-FE-08)

                   Without this, "Who else is on this" could invite an agent to
                   a ticket they are not allowed to open: the desk notified
                   them, the notification linked them to it, and both shells
                   answered "No Record Found". Observed exactly that - agent04
                   made a second pair of hands on a ticket in a department they
                   do not cover.

                   It is a membership term, not a hole in the scope. It sits
                   beside `staffid = me` because it is the same kind of fact:
                   somebody with TICKET_ASSIGN named this agent on this one
                   ticket, which is what assignment is too. `source = manual`
                   is what keeps it to that - the automatic rows written when
                   an agent answers, notes or is mentioned are consequences of
                   access already held, and must never become a grant of it.

                   A per-agent visibility record still wins: JSSTvisibility
                   returns above this switch, so an administrator who has
                   written an explicit rule for somebody is not overridden by
                   a colleague adding them to a ticket. */
                if (class_exists('JSSTcollab')) {
                    $jsst_oninvite = JSSTcollab::scopeTerm($jsst_alias, $jsst_actor['staffid']);
                    if ($jsst_oninvite !== '') {
                        $jsst_parts[] = $jsst_oninvite;
                    }
                }
                if (empty($jsst_parts)) {
                    return ' AND 1 = 0 ';
                }
                return ' AND (' . implode(' OR ', $jsst_parts) . ') ';
            case self::SCOPE_NONE:
            default:
                /* A guest's ticket list is not a list: they reach one ticket by
                   token, which the caller checks. Refusing the whole query is
                   the only correct answer to "show a guest their queue". */
                return ' AND 1 = 0 ';
        }
    }

    /* =====================================================================
     * Reading the two permission systems
     * ================================================================== */

    /** The plugin's own user id for a WordPress account, or 0. */
    public static function pluginUserId($jsst_wpuid) {
        $jsst_wpuid = (int) $jsst_wpuid;
        if ($jsst_wpuid <= 0 || !self::tableExists('js_ticket_users')) {
            return 0;
        }
        $jsst_uid = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT id FROM `' . jssupportticket::$_db->prefix . 'js_ticket_users` WHERE wpuid = %d',
            $jsst_wpuid
        ));
        return $jsst_uid ? (int) $jsst_uid : 0;
    }

    /** The Agents row for a plugin user id, or false. */
    private static function staffRow($jsst_uid) {
        $jsst_uid = (int) $jsst_uid;
        if ($jsst_uid <= 0 || !self::tableExists('js_ticket_staff')) {
            return false;
        }
        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT id, uid, status, departmentid, roleid FROM `' . jssupportticket::$_db->prefix . 'js_ticket_staff` WHERE uid = %d',
            $jsst_uid
        ));
        return $jsst_row ? $jsst_row : false;
    }

    /**
     * Does this agent hold one of the Agents screen's per-agent permissions?
     *
     * The join is the add-on's own, so the answer here and the answer the
     * add-on gives are the same answer — with the actor as an argument instead
     * of read from the session.
     */
    private static function hasTask($jsst_staffid, $jsst_task) {
        $jsst_staffid = (int) $jsst_staffid;
        if ($jsst_staffid <= 0) {
            return false;
        }
        $jsst_key = $jsst_staffid . '|' . $jsst_task;
        if (isset(self::$jsst_tasks[$jsst_key])) {
            return self::$jsst_tasks[$jsst_key];
        }
        self::$jsst_tasks[$jsst_key] = false;
        if (!self::tableExists('js_ticket_acl_permissions') || !self::tableExists('js_ticket_acl_user_permissions')) {
            return false;
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_count = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT COUNT(userperm.id) FROM `' . $jsst_prefix . 'js_ticket_acl_permissions` AS perm '
            . 'JOIN `' . $jsst_prefix . 'js_ticket_acl_user_permissions` AS userperm ON userperm.permissionid = perm.id '
            . 'WHERE perm.permission = %s AND userperm.staffid = %d',
            $jsst_task, $jsst_staffid
        ));
        self::$jsst_tasks[$jsst_key] = ((int) $jsst_count > 0);
        return self::$jsst_tasks[$jsst_key];
    }

    /** The departments an agent is scoped to. */
    private static function departmentsFor($jsst_staffid) {
        $jsst_staffid = (int) $jsst_staffid;
        if ($jsst_staffid <= 0) {
            return array();
        }
        if (isset(self::$jsst_departments[$jsst_staffid])) {
            return self::$jsst_departments[$jsst_staffid];
        }
        self::$jsst_departments[$jsst_staffid] = array();
        if (!self::tableExists('js_ticket_acl_user_access_departments')) {
            return array();
        }
        $jsst_rows = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
            'SELECT departmentid FROM `' . jssupportticket::$_db->prefix . 'js_ticket_acl_user_access_departments` WHERE staffid = %d',
            $jsst_staffid
        ));
        if (is_array($jsst_rows)) {
            self::$jsst_departments[$jsst_staffid] = array_map('intval', $jsst_rows);
        }
        return self::$jsst_departments[$jsst_staffid];
    }

    /** The columns of a ticket that decide access, and nothing else. */
    public static function ticket($jsst_ticketid) {
        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_ticketid <= 0) {
            return false;
        }
        if (array_key_exists($jsst_ticketid, self::$jsst_tickets)) {
            return self::$jsst_tickets[$jsst_ticketid];
        }
        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT id, uid, staffid, departmentid, status, `lock`, email, ticketid FROM `'
            . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d',
            $jsst_ticketid
        ));
        self::$jsst_tickets[$jsst_ticketid] = $jsst_row ? $jsst_row : false;
        return self::$jsst_tickets[$jsst_ticketid];
    }

    /** Did this person raise this ticket? By plugin user id, or by email. */
    private static function ownsTicket($jsst_actor, $jsst_ticketid) {
        $jsst_ticket = self::ticket($jsst_ticketid);
        if (!$jsst_ticket) {
            return false;
        }
        if ($jsst_actor['uid'] > 0 && (int) $jsst_ticket->uid === (int) $jsst_actor['uid']) {
            return true;
        }
        /* A ticket raised by email before the account existed carries the
           address but no user id. Matching on it is what stops somebody who
           later signs up being locked out of their own history. */
        if ($jsst_actor['email'] !== '' && (int) $jsst_ticket->uid === 0) {
            return (strtolower(trim((string) $jsst_ticket->email)) === strtolower(trim($jsst_actor['email'])));
        }
        return false;
    }

    /** Does this tracking token belong to this ticket? Compared in constant time. */
    private static function tokenMatches($jsst_ticketid, $jsst_token) {
        $jsst_ticket = self::ticket($jsst_ticketid);
        if (!$jsst_ticket) {
            return false;
        }
        $jsst_stored = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT token FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d',
            (int) $jsst_ticketid
        ));
        if (!$jsst_stored) {
            return false;
        }
        return hash_equals((string) $jsst_stored, (string) $jsst_token);
    }

    /** Does a table exist? Add-on tables have to be absent without breaking. */
    private static function tableExists($jsst_table) {
        static $jsst_known = array();
        if (isset($jsst_known[$jsst_table])) {
            return $jsst_known[$jsst_table];
        }
        $jsst_full = jssupportticket::$_db->prefix . $jsst_table;
        $jsst_known[$jsst_table] = (bool) jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_full)
        );
        return $jsst_known[$jsst_table];
    }

    /* =====================================================================
     * Small shared pieces
     * ================================================================== */

    private static function allow($jsst_action, $jsst_subject, $jsst_actor, $jsst_code, $jsst_reason, $jsst_trace) {
        return self::decision(true, $jsst_action, $jsst_subject, $jsst_actor, $jsst_code, $jsst_reason, $jsst_trace);
    }

    private static function deny($jsst_action, $jsst_subject, $jsst_actor, $jsst_code, $jsst_reason, $jsst_trace) {
        return self::decision(false, $jsst_action, $jsst_subject, $jsst_actor, $jsst_code, $jsst_reason, $jsst_trace);
    }

    private static function decision($jsst_allowed, $jsst_action, $jsst_subject, $jsst_actor, $jsst_code, $jsst_reason, $jsst_trace) {
        return array(
            'allowed' => (bool) $jsst_allowed,
            'action'  => $jsst_action,
            'code'    => $jsst_code,
            'reason'  => $jsst_reason,
            'actor'   => $jsst_actor,
            'subject' => $jsst_subject,
            'trace'   => $jsst_trace,
        );
    }

    /** A readable name for an actor kind. */
    public static function kindLabel($jsst_kind) {
        switch ($jsst_kind) {
            case self::ACTOR_SYSTEM:   return esc_html(__('automation', 'js-support-ticket'));
            case self::ACTOR_ADMIN:    return esc_html(__('a help desk administrator', 'js-support-ticket'));
            case self::ACTOR_AGENT:    return esc_html(__('an agent', 'js-support-ticket'));
            case self::ACTOR_CUSTOMER: return esc_html(__('a customer', 'js-support-ticket'));
            case self::ACTOR_GUEST:    return esc_html(__('a guest', 'js-support-ticket'));
        }
        return esc_html($jsst_kind);
    }

    /** A readable name for a visibility scope. */
    public static function scopeLabel($jsst_scope) {
        switch ($jsst_scope) {
            case self::SCOPE_ALL:        return esc_html(__('Every ticket on the site', 'js-support-ticket'));
            case self::SCOPE_DEPARTMENT: return esc_html(__('Their departments, plus anything assigned to them', 'js-support-ticket'));
            case self::SCOPE_ASSIGNED:   return esc_html(__('Only tickets assigned to them', 'js-support-ticket'));
            case self::SCOPE_OWN:        return esc_html(__('Only the tickets they raised', 'js-support-ticket'));
            case self::SCOPE_NONE:
            default:                     return esc_html(__('Nothing without a tracking token', 'js-support-ticket'));
        }
    }

    /**
     * Forget everything resolved this request.
     *
     * The caches are keyed by user, so an actor changing mid-request is already
     * safe — but permissions written during a request (the Agents screen saving
     * a change, a migration granting a capability) leave this class holding the
     * answer from before the write.
     */
    public static function flush() {
        self::$jsst_actors = array();
        self::$jsst_decisions = array();
        self::$jsst_tickets = array();
        self::$jsst_tasks = array();
        self::$jsst_departments = array();
    }
}
