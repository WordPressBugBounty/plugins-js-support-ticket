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
if (class_exists('JSSTnavigation')) {
    return;
}

/**
 * The desk, as a place you can move around in. (Roadmap 4.5-FE-02)
 *
 * Both agent workspaces in this product are pages you arrive at, not a desk you
 * work in. The backend agent gets whatever is left of the administration menu
 * after the screens they may not open are removed; the front-end agent gets the
 * control panel's grid of links, which is a list of every layout the plugin has
 * rather than a description of a working day. Neither has a home - somewhere
 * that answers "what needs me this morning" - and the answer is currently
 * assembled by an agent opening the queue and sorting it four different ways.
 *
 * This class is the navigation written down: seven destinations, in the order
 * an agent uses them, each one gated on the permission it actually needs and
 * each one knowing its own address in both workspaces. A shell renders the list
 * it is given, which is the same arrangement the rest of 4.5 uses and for the
 * same reason - two shells that each decide their own menu are two shells that
 * offer different things, and the difference is never noticed until somebody
 * asks why the portal has no reports.
 *
 * And it is the home screen itself, because a home is not a screen so much as
 * six questions:
 *
 *   What is mine, and how much of it is there.
 *   What have I already missed, and what am I about to miss.
 *   What has nobody picked up.
 *   Where has somebody asked for me by name.
 *   What did I start writing and not finish.
 *   What happened while I was away.
 *
 * Every one of them is answered through the shared layer - JSSTticketquery for
 * anything ticket-shaped, JSSTdraft for the drafts, JSSTcapability for whether
 * this person may be told at all - so the home screen is a rendering of answers
 * rather than a seventh place where the rules get written down again.
 *
 * What this is not: a notification centre. Notifications are 4.5-UX-02's work
 * and they are a different thing - events pushed at a person, with read state,
 * preferences and quiet hours. The destination is described here so the desk's
 * shape is complete and so the gap is visible, and it is not offered as a link
 * until there is something behind it.
 */
class JSSTnavigation {

    /* ---------------------------------------------------------------------
     * The destinations
     * ------------------------------------------------------------------ */

    const HOME          = 'home';
    const QUEUES        = 'queues';
    const TICKET        = 'ticket';
    const CUSTOMERS     = 'customers';
    const KNOWLEDGE     = 'knowledge';
    const REPORTS       = 'reports';
    const NOTIFICATIONS = 'notifications';

    /** Live chat, when its add-on is installed. (Roadmap 6.0-CH-01) */
    const CHAT          = 'chat';

    /* ---------------------------------------------------------------------
     * The home panels
     * ------------------------------------------------------------------ */

    const PANEL_WORKLOAD   = 'workload';
    const PANEL_RISK       = 'risk';
    const PANEL_UNASSIGNED = 'unassigned';
    const PANEL_MENTIONS   = 'mentions';
    const PANEL_DRAFTS     = 'drafts';
    const PANEL_APPROVALS  = 'approvals';
    const PANEL_ACTIVITY   = 'activity';

    /** How many rows a panel shows before it becomes a queue link instead. */
    const ROWS = 5;

    /** How far ahead "about to breach" looks. */
    const AT_RISK_HOURS = 24;

    /* =====================================================================
     * Where we are
     * ================================================================== */

    /**
     * Which desk is being rendered.
     *
     * is_admin() and nothing else: the two shells are precisely "in wp-admin"
     * and "not in wp-admin", and a workspace preference does not change which
     * one the current request is - only which ones this agent is allowed at,
     * which is JSSTworkspace's question and is asked separately.
     */
    public static function shell() {
        return is_admin() ? JSSTworkspace::SHELL_BACKEND : JSSTworkspace::SHELL_FRONTEND;
    }

    /**
     * The seven destinations, in the order a working day uses them.
     *
     * Each row carries its own address in both workspaces rather than a single
     * path with a rule for rewriting it, because the two shells do not disagree
     * about spelling - they disagree about which module owns the screen. The
     * front-end knowledge base is the knowledge add-on's staff listing; the
     * backend one is a WordPress admin page. There is no rewrite that turns one
     * into the other, and pretending otherwise is how a menu ends up linking an
     * agent to a page that refuses them.
     *
     *   action    The capability this destination needs. An empty one is open
     *             to anybody who reached the desk at all.
     *   module    The add-on or Pro module it lives in; absent from the site
     *             means the destination is not there rather than refused.
     *   routes    shell => query arguments.
     *   gate      An extra check named in gate(), for a destination whose
     *             screen is guarded by something older and narrower than the
     *             capability service. Reading the knowledge base is open to
     *             everybody, but the agent article list is not.
     *   context   Needs a ticket to point at, so it appears only when there is
     *             one - a "Ticket" tab pointing at nothing is furniture.
     *   owner     A roadmap id, when the destination is described here but not
     *             built yet. Never linked; named on the desk-shape screen so
     *             the gap is a known one rather than an oversight.
     */
    public static function sections() {
        $jsst_sections = array(
            self::HOME => array(
                'label'   => __('Home', 'js-support-ticket'),
                'summary' => __('What needs you, in the order it needs you.', 'js-support-ticket'),
                'action'  => '',
                'module'  => '',
                'routes'  => array(
                    JSSTworkspace::SHELL_BACKEND  => array('page' => 'jssupportticket', 'jstlay' => 'workspacehome'),
                    JSSTworkspace::SHELL_FRONTEND => array('jstmod' => 'jssupportticket', 'jstlay' => 'workspacehome'),
                ),
            ),
            /* The chat console, which is where an agent answers a conversation
               rather than a ticket. Gated on the add-on and on the reply
               capability - the same permission the ajax door asks, so a
               destination somebody can reach is one they can use.
               (Roadmap 6.0-CH-01) */
            self::CHAT => array(
                'label'   => __('Chat', 'js-support-ticket'),
                'summary' => __('People waiting to talk to somebody, and your own chats with something new in them.', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_REPLY,
                'module'  => 'livechat',
                'routes'  => array(
                    JSSTworkspace::SHELL_BACKEND  => array('page' => 'livechat'),
                    JSSTworkspace::SHELL_FRONTEND => array('jstmod' => 'livechat', 'jstlay' => 'staffchat'),
                ),
            ),
            self::QUEUES => array(
                'label'   => __('Queues', 'js-support-ticket'),
                'summary' => __('Every ticket you can see, filtered how you like it.', 'js-support-ticket'),
                'action'  => JSSTcapability::QUEUE_VIEW,
                'module'  => '',
                'routes'  => array(
                    JSSTworkspace::SHELL_BACKEND  => array('page' => 'ticket'),
                    JSSTworkspace::SHELL_FRONTEND => array('jstmod' => 'agent', 'jstlay' => 'staffmyticket'),
                ),
            ),
            self::TICKET => array(
                'label'   => __('Ticket', 'js-support-ticket'),
                'summary' => __('The ticket you are working on.', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_VIEW,
                'module'  => '',
                'context' => 'jssupportticketid',
                'routes'  => array(
                    JSSTworkspace::SHELL_BACKEND  => array('page' => 'ticket', 'jstlay' => 'ticketdetail'),
                    JSSTworkspace::SHELL_FRONTEND => array('jstmod' => 'ticket', 'jstlay' => 'ticketdetail'),
                ),
            ),
            self::CUSTOMERS => array(
                'label'   => __('Customers', 'js-support-ticket'),
                'summary' => __('Who has been writing in, and what they have asked before.', 'js-support-ticket'),
                'action'  => JSSTcapability::CUSTOMER_VIEW,
                'module'  => '',
                'routes'  => array(
                    JSSTworkspace::SHELL_BACKEND  => array('page' => 'jssupportticket', 'jstlay' => 'customers'),
                    JSSTworkspace::SHELL_FRONTEND => array('jstmod' => 'jssupportticket', 'jstlay' => 'customers'),
                ),
            ),
            self::KNOWLEDGE => array(
                'label'   => __('Knowledge', 'js-support-ticket'),
                'summary' => __('The articles you answer out of.', 'js-support-ticket'),
                'action'  => JSSTcapability::KB_VIEW,
                'gate'    => 'knowledge',
                'module'  => 'knowledgebase',
                'routes'  => array(
                    JSSTworkspace::SHELL_BACKEND  => array('page' => 'knowledgebase'),
                    JSSTworkspace::SHELL_FRONTEND => array('jstmod' => 'knowledgebase', 'jstlay' => 'stafflistarticles'),
                ),
            ),
            self::REPORTS => array(
                'label'   => __('Reports', 'js-support-ticket'),
                'summary' => __('How the desk is doing.', 'js-support-ticket'),
                'action'  => JSSTcapability::REPORT_VIEW,
                'module'  => '',
                'routes'  => array(
                    JSSTworkspace::SHELL_BACKEND  => array('page' => 'reports', 'jstlay' => 'overallreport'),
                    JSSTworkspace::SHELL_FRONTEND => array('jstmod' => 'reports', 'jstlay' => 'staffreports'),
                ),
            ),
            self::NOTIFICATIONS => array(
                'label'   => __('Notifications', 'js-support-ticket'),
                'summary' => __('Assignments, mentions, escalations and replies, in one place.', 'js-support-ticket'),
                'action'  => '',
                'module'  => '',
                /* The owner key is gone because the thing it was waiting for
                   is built. It was named here and refused a link for two
                   releases so the shape of the desk stayed honest about the
                   hole; this is what filling one looks like.
                   (Roadmap 4.5-FE-02, 4.5-UX-02) */
                'routes'  => array(
                    JSSTworkspace::SHELL_BACKEND  => array('page' => 'jssupportticket', 'jstlay' => 'notifications'),
                    JSSTworkspace::SHELL_FRONTEND => array('jstmod' => 'jssupportticket', 'jstlay' => 'notifications'),
                ),
            ),
        );
        return apply_filters('jsst_navigation_sections', $jsst_sections);
    }

    /**
     * One destination's address in one workspace.
     *
     * Returns an empty string where the destination has no address in that
     * shell, which a caller must treat as "do not offer this" rather than as a
     * link to nowhere - an anchor with an empty href reloads the page an agent
     * is already on and looks exactly like a screen that failed to load.
     */
    public static function url($jsst_key, $jsst_shell = '', $jsst_args = array()) {
        $jsst_sections = self::sections();
        if (!isset($jsst_sections[$jsst_key])) {
            return '';
        }
        $jsst_shell = ($jsst_shell !== '') ? $jsst_shell : self::shell();
        $jsst_route = isset($jsst_sections[$jsst_key]['routes'][$jsst_shell])
            ? $jsst_sections[$jsst_key]['routes'][$jsst_shell] : array();
        if (empty($jsst_route)) {
            return '';
        }
        $jsst_route = array_merge($jsst_route, is_array($jsst_args) ? $jsst_args : array());
        if ($jsst_shell === JSSTworkspace::SHELL_BACKEND) {
            return admin_url('admin.php?' . http_build_query($jsst_route));
        }
        return jssupportticket::makeUrl($jsst_route);
    }

    /**
     * The menu this person gets in this workspace.
     *
     * Everything that is not theirs is left out rather than shown disabled. A
     * greyed-out menu entry is an invitation to ask why, and the answer -
     * "because of a permission you cannot see, set by somebody you would have
     * to e-mail" - is not one the menu can give. The screens themselves refuse
     * independently; this is presentation, not enforcement, which is the same
     * division the workspace preference draws in 4.5-FE-11.
     *
     * @param array $jsst_options
     *   shell    string  Which desk. Defaults to the one being rendered.
     *   actor    array   Ask on somebody else's behalf.
     *   ticketid int     The ticket in context, for the Ticket destination.
     *   all      bool    Include destinations this actor cannot reach, each
     *                    carrying the reason. For the desk-shape screen.
     */
    public static function menu($jsst_options = array()) {
        $jsst_actor = self::actor($jsst_options);
        $jsst_shell = isset($jsst_options['shell']) ? (string) $jsst_options['shell'] : self::shell();
        $jsst_all = !empty($jsst_options['all']);
        $jsst_ticketid = isset($jsst_options['ticketid']) ? (int) $jsst_options['ticketid'] : self::contextTicket();
        $jsst_current = self::current($jsst_shell);

        $jsst_out = array();
        foreach (self::sections() as $jsst_key => $jsst_section) {
            $jsst_row = array_merge(array(
                'key'       => $jsst_key,
                'available' => true,
                'reason'    => '',
                'url'       => '',
                'current'   => ($jsst_current === $jsst_key),
                'count'     => null,
            ), $jsst_section);

            if (!empty($jsst_section['owner'])) {
                $jsst_row['available'] = false;
                /* translators: %s: a roadmap task id, e.g. 4.5-UX-02 */
                $jsst_row['reason'] = sprintf(__('Not built yet - %s.', 'js-support-ticket'), $jsst_section['owner']);
            } elseif (!empty($jsst_section['module']) && !in_array($jsst_section['module'], jssupportticket::$_active_addons)) {
                $jsst_row['available'] = false;
                $jsst_row['reason'] = __('The module this lives in is not on this site.', 'js-support-ticket');
            } elseif (empty($jsst_section['routes'][$jsst_shell])) {
                $jsst_row['available'] = false;
                $jsst_row['reason'] = __('This workspace has no screen for it.', 'js-support-ticket');
            } elseif ((!empty($jsst_section['action'])
                        && !JSSTcapability::can($jsst_section['action'], array(), $jsst_actor))
                    || (!empty($jsst_section['gate']) && !self::gate($jsst_section['gate'], $jsst_actor))) {
                $jsst_row['available'] = false;
                $jsst_row['reason'] = __('Your permissions do not include it.', 'js-support-ticket');
            } elseif (!empty($jsst_section['context'])) {
                if ($jsst_ticketid <= 0) {
                    $jsst_row['available'] = false;
                    $jsst_row['reason'] = __('Shown while a ticket is open.', 'js-support-ticket');
                } else {
                    $jsst_row['url'] = self::url($jsst_key, $jsst_shell,
                        array($jsst_section['context'] => $jsst_ticketid));
                }
            }
            if ($jsst_row['available'] && $jsst_row['url'] === '') {
                $jsst_row['url'] = self::url($jsst_key, $jsst_shell);
            }
            /* The one number worth carrying in a menu: how much is waiting on
               this agent rather than on a customer. The counts are already
               cached against the event stamp, so this costs a lookup rather
               than a scan. (Roadmap 4.5-UX-01) */
            if ($jsst_row['available'] && $jsst_key === self::NOTIFICATIONS
                    && class_exists('JSSTnotifications') && (int) $jsst_actor['staffid'] > 0) {
                $jsst_unseen = JSSTnotifications::unseen((int) $jsst_actor['staffid']);
                if ($jsst_unseen > 0) {
                    $jsst_row['count'] = $jsst_unseen;
                }
            }
            /* And how many people are waiting to talk, for the same reason.
               (Roadmap 6.0-CH-01)

               A live chat has no other way of reaching anybody: nothing
               subscribes to `jsst_chat_message`, no mail is sent and no push
               goes out, so before this the only way to learn a visitor was
               waiting was to already be looking at the Chat screen. An agent
               working a ticket two screens away had no idea. A number in the
               menu they can see from every desk screen is the cheapest honest
               answer to that.

               `waitingCount()` rather than `count(waiting())`, which would run
               a `SELECT *` for up to fifty conversation records on every menu
               render, on every screen. */
            if ($jsst_row['available'] && $jsst_key === self::CHAT
                    && class_exists('JSSTlivechat') && method_exists('JSSTlivechat', 'waitingCount')) {
                $jsst_chatwaiting = JSSTlivechat::waitingCount();

                /* And the other half of it: a conversation this agent is already
                   holding that has said something since they last looked.
                   (Roadmap 6.0-CH-01)

                   `waitingCount()` stops the moment somebody claims a chat, so
                   until now an agent who took one, answered, and moved to a
                   ticket had no signal at all when the customer replied - the
                   console only polls while it is the screen you are on, and
                   nothing else in the product knows a chat message happened.
                   The two numbers are added rather than shown apart because
                   they mean the same thing to the person reading the menu:
                   somebody is waiting for you on the Chat screen.

                   Guarded on the method as well as the class, so a desk running
                   an older Experience bundle beside a newer core keeps the
                   waiting count rather than fataling on a method that is not
                   there yet. */
                if (method_exists('JSSTlivechat', 'unreadCount') && (int) $jsst_actor['staffid'] > 0) {
                    $jsst_chatwaiting += JSSTlivechat::unreadCount((int) $jsst_actor['staffid']);
                }

                if ($jsst_chatwaiting > 0) {
                    $jsst_row['count'] = $jsst_chatwaiting;
                }
            }
            if ($jsst_row['available'] && $jsst_key === self::QUEUES && class_exists('JSSTqueueengine')) {
                $jsst_counts = JSSTqueueengine::counts(array('actor' => $jsst_actor));
                if (isset($jsst_counts['waitingagentticket'])) {
                    $jsst_row['count'] = (int) $jsst_counts['waitingagentticket'];
                }
            }
            if ($jsst_row['available'] || $jsst_all) {
                $jsst_out[$jsst_key] = $jsst_row;
            }
        }
        return $jsst_out;
    }

    /**
     * Which destination the current request is at.
     *
     * Matched on the route arguments rather than on a layout name, because the
     * same layout name means different things in the two shells and because a
     * destination is its whole address: page=jssupportticket alone is the
     * dashboard, and page=jssupportticket&jstlay=customers is somewhere else
     * entirely. The most specific match wins for that reason.
     */
    public static function current($jsst_shell = '') {
        $jsst_shell = ($jsst_shell !== '') ? $jsst_shell : self::shell();
        $jsst_best = '';
        $jsst_score = 0;
        foreach (self::sections() as $jsst_key => $jsst_section) {
            if (empty($jsst_section['routes'][$jsst_shell])) {
                continue;
            }
            $jsst_matched = 0;
            foreach ($jsst_section['routes'][$jsst_shell] as $jsst_arg => $jsst_value) {
                if ((string) JSSTrequest::getVar($jsst_arg, null, '') !== (string) $jsst_value) {
                    $jsst_matched = 0;
                    break;
                }
                $jsst_matched++;
            }
            if ($jsst_matched > $jsst_score) {
                $jsst_score = $jsst_matched;
                $jsst_best = $jsst_key;
            }
        }
        return $jsst_best;
    }

    /** The ticket the current request is about, if it is about one. */
    private static function contextTicket() {
        $jsst_id = JSSTrequest::getVar('jssupportticketid', null, 0);
        return is_numeric($jsst_id) ? (int) $jsst_id : 0;
    }

    /* =====================================================================
     * Home
     * ================================================================== */

    /**
     * The six questions a home screen answers, described before they are asked.
     *
     * Kept apart from the answers so a shell can lay the home out - decide the
     * order, the headings, which two go side by side - without running any of
     * the queries, and so this list is readable as what the product claims a
     * home is.
     */
    public static function panels() {
        $jsst_panels = array(
            self::PANEL_WORKLOAD => array(
                'label'  => __('Your workload', 'js-support-ticket'),
                'detail' => __('Open tickets assigned to you, the ones waiting longest first.', 'js-support-ticket'),
                'action' => JSSTcapability::QUEUE_VIEW,
                'kind'   => 'tickets',
                'empty'  => __('Nothing is assigned to you.', 'js-support-ticket'),
            ),
            self::PANEL_RISK => array(
                'label'  => __('Breached and at risk', 'js-support-ticket'),
                'detail' => __('Past its due date, or due within the day. Late ones first, because they are already somebody\'s complaint.', 'js-support-ticket'),
                'action' => JSSTcapability::QUEUE_VIEW,
                'kind'   => 'tickets',
                'empty'  => __('Nothing is late or close to it.', 'js-support-ticket'),
            ),
            self::PANEL_UNASSIGNED => array(
                'label'  => __('Waiting for somebody', 'js-support-ticket'),
                'detail' => __('Open tickets nobody has picked up, oldest first.', 'js-support-ticket'),
                'action' => JSSTcapability::QUEUE_VIEW,
                'kind'   => 'tickets',
                'empty'  => __('Everything you can see has an agent.', 'js-support-ticket'),
            ),
            self::PANEL_MENTIONS => array(
                'label'  => __('Where you were named', 'js-support-ticket'),
                'detail' => __('Internal notes on your tickets that write your name after an @.', 'js-support-ticket'),
                'action' => JSSTcapability::TICKET_NOTE,
                'kind'   => 'notes',
                'empty'  => __('Nobody has asked for you by name lately.', 'js-support-ticket'),
            ),
            self::PANEL_DRAFTS => array(
                'label'  => __('Unfinished replies', 'js-support-ticket'),
                'detail' => __('What you started writing and did not send.', 'js-support-ticket'),
                'action' => JSSTcapability::TICKET_REPLY,
                'kind'   => 'drafts',
                'empty'  => __('No drafts waiting.', 'js-support-ticket'),
            ),
            /* Replies somebody sent YOU to look at before they go. Distinct
               from the panel above it, which is your own unsent writing:
               `JSSTcollab::awaitingApproval()` existed and nothing called it,
               so a draft sent for approval sat in the table and the approver
               was never told and had nowhere to find it. Rendered as `notes`
               because the useful line is who wrote it and what it says, which
               is the shape that renderer already has. (Roadmap 4.5-FE-08) */
            self::PANEL_APPROVALS => array(
                'label'  => __('Waiting on your say-so', 'js-support-ticket'),
                'detail' => __('Replies somebody wrote and sent to you to approve. Nothing has gone to the customer.', 'js-support-ticket'),
                'action' => JSSTcapability::TICKET_REPLY,
                'kind'   => 'notes',
                'empty'  => __('Nobody is waiting on you.', 'js-support-ticket'),
            ),
            self::PANEL_ACTIVITY => array(
                'label'  => __('What happened lately', 'js-support-ticket'),
                'detail' => __('The last events on the tickets you can see - including the ones you were not looking at.', 'js-support-ticket'),
                'action' => JSSTcapability::QUEUE_VIEW,
                'kind'   => 'events',
                'empty'  => __('Nothing has been recorded yet.', 'js-support-ticket'),
            ),
        );
        foreach ($jsst_panels as $jsst_key => $jsst_panel) {
            $jsst_panels[$jsst_key]['key'] = $jsst_key;
        }
        return apply_filters('jsst_navigation_panels', $jsst_panels);
    }

    /**
     * The home screen, answered.
     *
     * Every panel that this actor is not allowed to be shown comes back empty
     * with the reason on it rather than being dropped: a home with three panels
     * on one agent's screen and six on another's, with nothing saying why, is
     * the sort of difference that gets reported as a broken page.
     */
    public static function home($jsst_options = array()) {
        $jsst_actor = self::actor($jsst_options);
        $jsst_shell = isset($jsst_options['shell']) ? (string) $jsst_options['shell'] : self::shell();
        $jsst_out = array();
        foreach (self::panels() as $jsst_key => $jsst_panel) {
            $jsst_row = array_merge($jsst_panel, array(
                'count'   => 0,
                'rows'    => array(),
                'url'     => '',
                'allowed' => true,
                'reason'  => '',
            ));
            if (!empty($jsst_panel['action'])
                    && !JSSTcapability::can($jsst_panel['action'], array(), $jsst_actor)) {
                $jsst_row['allowed'] = false;
                $jsst_row['reason'] = __('Your permissions do not include this.', 'js-support-ticket');
                $jsst_out[$jsst_key] = $jsst_row;
                continue;
            }
            switch ($jsst_key) {
                case self::PANEL_WORKLOAD:
                    $jsst_row = array_merge($jsst_row, self::panelQueue($jsst_actor, $jsst_shell,
                        array('staffid' => -1), 'mine'));
                    break;
                case self::PANEL_UNASSIGNED:
                    $jsst_row = array_merge($jsst_row, self::panelQueue($jsst_actor, $jsst_shell,
                        array('staffid' => 0, 'orderby' => 'created', 'order' => 'ASC'), 'unassigned'));
                    break;
                case self::PANEL_RISK:
                    $jsst_row = array_merge($jsst_row, self::panelRisk($jsst_actor, $jsst_shell));
                    break;
                case self::PANEL_MENTIONS:
                    $jsst_row = array_merge($jsst_row, self::panelMentions($jsst_actor, $jsst_shell));
                    break;
                case self::PANEL_DRAFTS:
                    $jsst_row = array_merge($jsst_row, self::panelDrafts($jsst_actor, $jsst_shell));
                    break;
                case self::PANEL_APPROVALS:
                    $jsst_row = array_merge($jsst_row, self::panelApprovals($jsst_actor, $jsst_shell));
                    break;
                case self::PANEL_ACTIVITY:
                    $jsst_row = array_merge($jsst_row, self::panelActivity($jsst_actor, $jsst_shell));
                    break;
            }
            $jsst_out[$jsst_key] = $jsst_row;
        }
        return $jsst_out;
    }

    /**
     * A panel that is a queue with the filters already set.
     *
     * The panel and the link under it are built from the same filters, so "see
     * all fourteen" cannot land on a list of eleven. The scope key is what the
     * queue screens understand from a link, and it is the same key the inbox
     * buttons use.
     */
    private static function panelQueue($jsst_actor, $jsst_shell, $jsst_filters, $jsst_scope) {
        $jsst_page = JSSTticketquery::queues(array_merge($jsst_filters, array(
            'actor' => $jsst_actor,
            'list'  => JSSTqueue::LIST_OPEN,
            'limit' => self::ROWS,
        )));
        return array(
            'count' => empty($jsst_page['ok']) ? 0 : (int) $jsst_page['total'],
            'rows'  => self::ticketRows(empty($jsst_page['ok']) ? array() : $jsst_page['rows'], $jsst_shell),
            'url'   => self::url(self::QUEUES, $jsst_shell,
                array('scope' => $jsst_scope, 'list' => JSSTqueue::LIST_OPEN)),
        );
    }

    /**
     * Already late, then about to be.
     *
     * Two queries because they are two questions, and shown in one panel
     * because an agent triaging their morning is doing one thing. The late ones
     * come first and fill the panel; the at-risk ones only take the space left,
     * so a desk with fourteen breaches never shows a ticket that is merely due
     * this afternoon.
     *
     * The link goes to the Overdue tab, and only where the Overdue module is
     * installed to write the flag that tab reads. Without it the panel still
     * works - the due date is in the ticket table either way - but there is no
     * queue to send anybody to, and a link to a tab that reads zero for ever is
     * worse than no link.
     */
    private static function panelRisk($jsst_actor, $jsst_shell) {
        $jsst_late = JSSTticketquery::queues(array(
            'actor'    => $jsst_actor,
            'list'     => JSSTqueue::LIST_OPEN,
            'breached' => 1,
            'orderby'  => 'duedate',
            'order'    => 'ASC',
            'limit'    => self::ROWS,
        ));
        $jsst_soon = JSSTticketquery::queues(array(
            'actor'     => $jsst_actor,
            'list'      => JSSTqueue::LIST_OPEN,
            'duewithin' => self::AT_RISK_HOURS,
            'orderby'   => 'duedate',
            'order'     => 'ASC',
            'limit'     => self::ROWS,
        ));
        $jsst_latecount = empty($jsst_late['ok']) ? 0 : (int) $jsst_late['total'];
        $jsst_sooncount = empty($jsst_soon['ok']) ? 0 : (int) $jsst_soon['total'];

        $jsst_rows = self::ticketRows(empty($jsst_late['ok']) ? array() : $jsst_late['rows'], $jsst_shell, true);
        $jsst_space = self::ROWS - count($jsst_rows);
        if ($jsst_space > 0 && !empty($jsst_soon['ok'])) {
            $jsst_rows = array_merge($jsst_rows,
                self::ticketRows(array_slice($jsst_soon['rows'], 0, $jsst_space), $jsst_shell, false));
        }
        return array(
            'count' => $jsst_latecount + $jsst_sooncount,
            'meta'  => array('breached' => $jsst_latecount, 'atrisk' => $jsst_sooncount),
            'rows'  => $jsst_rows,
            'url'   => in_array('overdue', jssupportticket::$_active_addons)
                ? self::url(self::QUEUES, $jsst_shell, array('list' => JSSTqueue::LIST_OVERDUE)) : '',
        );
    }

    /** Notes that named this agent, newest first. */
    private static function panelMentions($jsst_actor, $jsst_shell) {
        $jsst_notes = JSSTticketquery::mentions(array('actor' => $jsst_actor, 'limit' => self::ROWS));
        $jsst_rows = array();
        foreach ($jsst_notes as $jsst_note) {
            $jsst_rows[] = array(
                'ticketid'  => (int) $jsst_note->ticketid,
                'reference' => $jsst_note->reference,
                'subject'   => $jsst_note->subject,
                'author'    => $jsst_note->authorname,
                'when'      => $jsst_note->created,
                /* Trimmed here rather than in the template so both shells cut
                   it at the same place and neither has to know that a note is
                   editor HTML. */
                'excerpt'   => wp_trim_words(wp_strip_all_tags((string) $jsst_note->note), 24),
                'url'       => self::ticketUrl((int) $jsst_note->ticketid, $jsst_shell),
            );
        }
        /* The count is how many are UNREAD, not how many rows fitted. It read
           `count($jsst_rows)`, which is capped at ROWS - so an agent with
           eleven unanswered mentions saw a 5, and the number stayed 5 however
           many they read, because reading one only let the sixth into the
           list. `unreadMentions()` was written for exactly this and had no
           caller. Falls back to the row tally where the add-on is absent, so
           the panel still says something true. (Roadmap 4.5-FE-08) */
        $jsst_count = (class_exists('JSSTcollab') && (int) $jsst_actor['staffid'] > 0)
            ? JSSTcollab::unreadMentions((int) $jsst_actor['staffid'])
            : count($jsst_rows);
        return array('count' => $jsst_count, 'rows' => $jsst_rows);
    }

    /**
     * Replies waiting on this agent's approval.
     *
     * The ticket is read back through `JSSTticketquery::detail()` for the same
     * reason the drafts panel does it: a draft carries a ticket id and no idea
     * whether the approver may still see that ticket. detail() refuses when
     * they may not, and the row is dropped rather than leaking a subject.
     */
    private static function panelApprovals($jsst_actor, $jsst_shell) {
        $jsst_me = (int) $jsst_actor['staffid'];
        if (!class_exists('JSSTcollab') || $jsst_me <= 0) {
            return array('count' => 0, 'rows' => array());
        }
        $jsst_rows = array();
        foreach (JSSTcollab::awaitingApproval($jsst_me, self::ROWS) as $jsst_draft) {
            $jsst_detail = JSSTticketquery::detail((int) $jsst_draft->ticketid, array('actor' => $jsst_actor));
            if (is_wp_error($jsst_detail) || empty($jsst_detail['ok'])) {
                continue;
            }
            $jsst_rows[] = array(
                'ticketid'  => (int) $jsst_draft->ticketid,
                'reference' => $jsst_draft->reference,
                'subject'   => $jsst_draft->subject,
                'author'    => $jsst_draft->authorname,
                'when'      => $jsst_draft->created,
                'excerpt'   => wp_trim_words(wp_strip_all_tags((string) $jsst_draft->body), 24),
                'url'       => self::ticketUrl((int) $jsst_draft->ticketid, $jsst_shell),
            );
        }
        return array('count' => count($jsst_rows), 'rows' => $jsst_rows);
    }

    /**
     * Drafts, with the ticket each one belongs to.
     *
     * Read back through JSSTticketquery::detail() rather than joined to the
     * ticket table, because a draft is user meta and has no idea whether the
     * agent may still see the ticket: they may have been moved out of the
     * department since they started writing. detail() refuses in that case and
     * the draft is left out, which is the right answer even though the text is
     * still theirs.
     */
    private static function panelDrafts($jsst_actor, $jsst_shell) {
        if (!class_exists('JSSTdraft')) {
            return array('count' => 0, 'rows' => array());
        }
        $jsst_drafts = JSSTdraft::forUser((int) $jsst_actor['wpuid'], self::ROWS);
        $jsst_rows = array();
        foreach ($jsst_drafts as $jsst_draft) {
            $jsst_detail = JSSTticketquery::detail($jsst_draft['ticketid'], array('actor' => $jsst_actor));
            if (is_wp_error($jsst_detail) || empty($jsst_detail['ok'])) {
                continue;
            }
            $jsst_rows[] = array(
                'ticketid'  => (int) $jsst_draft['ticketid'],
                'reference' => $jsst_detail['ticket']->ticketid,
                'subject'   => $jsst_detail['ticket']->subject,
                'mode'      => $jsst_draft['mode'],
                'when'      => $jsst_draft['saved'] > 0 ? gmdate('Y-m-d H:i:s', $jsst_draft['saved']) : '',
                'excerpt'   => wp_trim_words(wp_strip_all_tags((string) $jsst_draft['body']), 24),
                'url'       => self::ticketUrl((int) $jsst_draft['ticketid'], $jsst_shell),
            );
        }
        return array('count' => count($jsst_rows), 'rows' => $jsst_rows);
    }

    /** The activity feed, scoped to what this agent may see. */
    private static function panelActivity($jsst_actor, $jsst_shell) {
        $jsst_events = JSSTticketquery::activity(array('actor' => $jsst_actor, 'limit' => self::ROWS));
        $jsst_rows = array();
        foreach ($jsst_events as $jsst_event) {
            $jsst_rows[] = array(
                'ticketid'  => (int) $jsst_event->ticketid,
                'reference' => $jsst_event->reference,
                'subject'   => $jsst_event->subject,
                'what'      => $jsst_event->eventtype,
                'who'       => $jsst_event->actorname,
                'when'      => $jsst_event->datetime,
                'message'   => wp_trim_words(wp_strip_all_tags((string) $jsst_event->message), 20),
                'url'       => self::ticketUrl((int) $jsst_event->ticketid, $jsst_shell),
            );
        }
        return array('count' => count($jsst_rows), 'rows' => $jsst_rows);
    }

    /** Ticket rows, in the one shape every panel renders. */
    private static function ticketRows($jsst_rows, $jsst_shell, $jsst_late = null) {
        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[] = array(
                'ticketid'  => (int) $jsst_row->id,
                'reference' => $jsst_row->ticketid,
                'subject'   => $jsst_row->subject,
                'customer'  => isset($jsst_row->name) ? $jsst_row->name : '',
                'when'      => isset($jsst_row->updated) ? $jsst_row->updated : '',
                'duedate'   => isset($jsst_row->duedate) ? $jsst_row->duedate : '',
                'late'      => $jsst_late,
                'url'       => self::ticketUrl((int) $jsst_row->id, $jsst_shell),
            );
        }
        return $jsst_out;
    }

    /** One ticket's address in one workspace. */
    public static function ticketUrl($jsst_ticketid, $jsst_shell = '') {
        return self::url(self::TICKET, $jsst_shell, array('jssupportticketid' => (int) $jsst_ticketid));
    }

    /* =====================================================================
     * Rendering, once, for both shells
     * ================================================================== */

    /**
     * The navigation strip.
     *
     * Printed by this class rather than described to two templates, and that is
     * a deliberate exception to how the rest of the descriptors work. A menu is
     * small, it is identical in both workspaces, and the two things a shell
     * would otherwise decide for itself - which entries and in what order - are
     * exactly what this task exists to make the same. The classes are plain and
     * unprefixed by shell so one rule set styles both.
     */
    public static function renderNav($jsst_options = array()) {
        $jsst_menu = self::menu($jsst_options);
        if (empty($jsst_menu)) {
            return;
        }
        ?>
        <nav class="jsst-nav" aria-label="<?php echo esc_attr(__('Workspace', 'js-support-ticket')); ?>">
            <ul class="jsst-nav-list">
                <?php foreach ($jsst_menu as $jsst_item) { ?>
                    <li class="jsst-nav-item <?php echo $jsst_item['current'] ? 'jsst-nav-on' : ''; ?>">
                        <a href="<?php echo esc_url($jsst_item['url']); ?>" title="<?php echo esc_attr($jsst_item['summary']); ?>">
                            <span class="jsst-nav-label"><?php echo esc_html($jsst_item['label']); ?></span>
                            <?php if ($jsst_item['count'] !== null && $jsst_item['count'] > 0) { ?>
                                <span class="jsst-nav-count"><?php echo esc_html($jsst_item['count']); ?></span>
                            <?php } ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </nav>
        <?php
    }

    /**
     * The home screen's panels.
     *
     * Same argument as the navigation strip: the value of a home is that both
     * desks show the same one, and two templates rendering six panels each is
     * two chances to show five.
     */
    public static function renderHome($jsst_options = array()) {
        $jsst_shell = isset($jsst_options['shell']) ? (string) $jsst_options['shell'] : self::shell();
        $jsst_panels = self::home($jsst_options);
        ?>
        <div class="jsst-home">
            <?php foreach ($jsst_panels as $jsst_panel) { ?>
                <section class="jsst-home-panel jsst-home-<?php echo esc_attr($jsst_panel['key']); ?>">
                    <header class="jsst-home-head">
                        <h2 class="jsst-home-title">
                            <?php echo esc_html($jsst_panel['label']); ?>
                            <?php if ($jsst_panel['allowed'] && $jsst_panel['count'] > 0) { ?>
                                <span class="jsst-home-count"><?php echo esc_html($jsst_panel['count']); ?></span>
                            <?php } ?>
                        </h2>
                        <p class="jsst-home-detail"><?php echo esc_html($jsst_panel['detail']); ?></p>
                    </header>
                    <?php
                    if (!$jsst_panel['allowed']) {
                        echo '<p class="jsst-home-empty">' . esc_html($jsst_panel['reason']) . '</p>';
                    } elseif (empty($jsst_panel['rows'])) {
                        echo '<p class="jsst-home-empty">' . esc_html($jsst_panel['empty']) . '</p>';
                    } else {
                        self::renderRows($jsst_panel);
                    }
                    if (!empty($jsst_panel['url']) && $jsst_panel['count'] > count($jsst_panel['rows'])) {
                        ?>
                        <a class="jsst-home-more" href="<?php echo esc_url($jsst_panel['url']); ?>">
                            <?php
                            echo esc_html(sprintf(
                                /* translators: %d: how many tickets are in the queue behind this panel */
                                _n('See all %d', 'See all %d', (int) $jsst_panel['count'], 'js-support-ticket'),
                                (int) $jsst_panel['count']
                            ));
                            ?>
                        </a>
                        <?php
                    }
                    ?>
                </section>
            <?php } ?>
        </div>
        <?php
        unset($jsst_shell);
    }

    /** One panel's rows, in the shape its kind calls for. */
    private static function renderRows($jsst_panel) {
        $jsst_dateformat = isset(jssupportticket::$_config['date_format'])
            ? jssupportticket::$_config['date_format'] : 'Y-m-d';
        ?>
        <ul class="jsst-home-rows">
            <?php foreach ($jsst_panel['rows'] as $jsst_row) { ?>
                <li class="jsst-home-row <?php echo (isset($jsst_row['late']) && $jsst_row['late']) ? 'jsst-home-late' : ''; ?>">
                    <a class="jsst-home-subject" href="<?php echo esc_url($jsst_row['url']); ?>">
                        <span class="jsst-home-ref"><?php echo esc_html($jsst_row['reference']); ?></span>
                        <?php echo esc_html($jsst_row['subject']); ?>
                    </a>
                    <span class="jsst-home-meta">
                        <?php
                        switch ($jsst_panel['kind']) {
                            case 'tickets':
                                if (!empty($jsst_row['customer'])) {
                                    echo esc_html($jsst_row['customer']) . ' &middot; ';
                                }
                                if (isset($jsst_row['late']) && $jsst_row['late'] !== null && !empty($jsst_row['duedate'])) {
                                    echo esc_html($jsst_row['late']
                                        ? __('Late since', 'js-support-ticket')
                                        : __('Due', 'js-support-ticket')) . ' ';
                                    echo esc_html(date_i18n($jsst_dateformat,
                                        jssupportticketphplib::JSST_strtotime($jsst_row['duedate'])));
                                } elseif (!empty($jsst_row['when'])) {
                                    echo esc_html(date_i18n($jsst_dateformat,
                                        jssupportticketphplib::JSST_strtotime($jsst_row['when'])));
                                }
                                break;
                            case 'notes':
                                if (!empty($jsst_row['author'])) {
                                    echo esc_html($jsst_row['author']) . ' &middot; ';
                                }
                                echo esc_html($jsst_row['excerpt']);
                                break;
                            case 'drafts':
                                echo esc_html($jsst_row['mode'] === 'internal'
                                    ? __('Internal note', 'js-support-ticket')
                                    : __('Reply', 'js-support-ticket')) . ' &middot; ';
                                echo esc_html($jsst_row['excerpt']);
                                break;
                            case 'events':
                                if (!empty($jsst_row['what'])) {
                                    echo esc_html($jsst_row['what']) . ' &middot; ';
                                }
                                echo esc_html($jsst_row['message']);
                                break;
                        }
                        ?>
                    </span>
                </li>
            <?php } ?>
        </ul>
        <?php
    }

    /* =====================================================================
     * Shared pieces
     * ================================================================== */

    /**
     * The extra checks, for screens the capability service does not yet own.
     *
     * One entry today, and it earns its place: KB_VIEW means "may read the
     * knowledge base", which customers and guests may do, while the screen this
     * destination points at is the agent article list and asks JSSTroles the
     * older question. Offering the link on the capability alone would put a
     * refusal one click from the menu, which is the one thing a menu must not
     * do. Anything added here is a note that the screen has not been brought
     * onto the service yet, not a second permission system.
     *
     * Only ever asked about the current user: the JSSTroles helpers read the
     * signed-in person and have no actor argument, so an answer for anybody
     * else would be this person's answer wearing somebody else's name.
     */
    private static function gate($jsst_gate, $jsst_actor) {
        if ((int) $jsst_actor['wpuid'] !== (int) get_current_user_id()) {
            return true;
        }
        switch ($jsst_gate) {
            case 'knowledge':
                return (class_exists('JSSTroles') && JSSTroles::canAuthorKnowledge('View Knowledge Base'));
        }
        return true;
    }

    /** Whoever the caller says is looking, or whoever is on the request. */
    private static function actor($jsst_options) {
        if (isset($jsst_options['actor']) && is_array($jsst_options['actor'])) {
            return $jsst_options['actor'];
        }
        if (isset($jsst_options['wpuid'])) {
            return JSSTcapability::actor((int) $jsst_options['wpuid']);
        }
        return JSSTcapability::actor();
    }
}
