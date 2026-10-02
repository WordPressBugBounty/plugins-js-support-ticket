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
if (class_exists('JSSTworkspace')) {
    return;
}

/**
 * The agent workspace, described once and rendered twice. (Roadmap 4.5-FE-01)
 *
 * There are two agent workspaces in this product. One is the backend desk in
 * wp-admin; the other is the front-end portal an agent signs into when they
 * have no business in wp-admin at all. They are supposed to be the same desk
 * seen from two places. They are not, and the reason is that nothing has ever
 * written down what the desk *is* — each screen was extended on its own, by
 * whoever needed the next thing, and the difference between them is whatever
 * happened not to be done twice.
 *
 * The differences are not opinions. The front-end queue numbers its tabs 1, 2,
 * 3, 5, 4 against a constant set where 2 means Answered and 4 means Closed, so
 * the same tab number means a different queue depending on which workspace you
 * are in. The backend queue searches every part of a ticket through one box;
 * the front-end one has its own field-by-field search. Saved views, bulk
 * actions, draft autosave and keyboard shortcuts exist on one side and not the
 * other. Three separate functions build the three ticket lists.
 *
 * This class is the written-down version. It holds two things:
 *
 *   The descriptors. What the tabs are, what the scopes are, which buttons a
 *   ticket offers this actor, which of them work in bulk, what the keyboard
 *   shortcuts are, where a draft is kept. Every one is derived from the
 *   application layer - JSSTcapability decides, JSSTticketquery reads,
 *   JSSTticketservice writes - so a shell that renders from these descriptors
 *   is not implementing the feature at all. It is displaying an answer it was
 *   given, which is the only arrangement in which two shells cannot drift.
 *
 *   The parity matrix. One row per capability the roadmap requires of an agent
 *   desk, and for each row a probe of each shell's actual source.

 *   What counts as "the shared layer" for that probe is worth stating, because
 *   it is narrower than "core code": JSSTcapability, JSSTticketquery,
 *   JSSTticketservice, JSSTqueueengine, JSSTdraft and this class. What they
 *   have in common is that every one of them takes the actor as an argument and
 *   answers for any workspace. A core helper that builds a clause or stores a
 *   row - JSSTqueue is the obvious one - is shared code and is not the shared
 *   layer: a shell calling it directly is still assembling its own queue out of
 *   parts, which is precisely how the two came to differ. The probe
 *   answers one of three things: this shell reaches the feature through the
 *   shared layer, this shell has its own copy of it, or this shell does not
 *   have it. "Its own copy" is not a failure of the product - the feature works
 *   - but it is a failure of parity, because a private implementation is
 *   exactly the thing that drifts. Naming the two states separately is what
 *   makes the matrix useful rather than a wall of red: it distinguishes work
 *   that has to be built from work that has to be moved.
 *
 * The matrix is run, not maintained. It reads the source of the screens it
 * describes, through JSSTincluder::getPluginPath(), so it keeps answering
 * correctly after a module moves from a legacy add-on into the Pro companion,
 * and a row cannot report green because somebody remembered to tick it.
 */
class JSSTworkspace {

    /* ---------------------------------------------------------------------
     * The two shells
     * ------------------------------------------------------------------ */

    const SHELL_BACKEND  = 'backend';
    const SHELL_FRONTEND = 'frontend';

    /* ---------------------------------------------------------------------
     * What a probe can conclude
     * ------------------------------------------------------------------ */

    /** The shell reaches this feature through the shared application layer. */
    const PARITY_SHARED  = 'shared';
    /** The shell has the feature, implemented privately. Works; will drift. */
    const PARITY_OWN     = 'own';
    /** The shell does not have the feature at all. */
    const PARITY_MISSING = 'missing';
    /** This shell is not installed on this site, so there is nothing to test. */
    const PARITY_NOSHELL = 'noshell';
    /** The module this feature belongs to is not on this site. */
    const PARITY_NA      = 'na';

    /** Source files already read this request, keyed by absolute path. */
    private static $jsst_sources = array();

    /** The matrix, once run. */
    private static $jsst_parity = null;

    /* =====================================================================
     * The shells
     * ================================================================== */

    /**
     * The two workspaces, and how to find each one's source.
     *
     * The front-end shell belongs to the agent module, which may be a legacy
     * add-on, may be a Pro module, and may not be installed. It is looked up by
     * slug rather than by path for exactly that reason: getPluginPath() already
     * knows which of the three is true, and asking it is how this class goes on
     * being right after PRO-02 moves the module.
     */
    public static function shells() {
        return array(
            self::SHELL_BACKEND => array(
                'label'   => __('Backend agent', 'js-support-ticket'),
                'summary' => __('The desk in wp-admin.', 'js-support-ticket'),
                'module'  => '',
            ),
            self::SHELL_FRONTEND => array(
                'label'   => __('Frontend agent', 'js-support-ticket'),
                'summary' => __('The desk an agent reaches without wp-admin.', 'js-support-ticket'),
                /* No agent module, no front-end desk - and no parity question
                   either. A site running one workspace is not a site where the
                   two disagree. */
                'module'  => 'agent',
            ),
        );
    }

    /**
     * Which desks this person may work at. (Roadmap 4.5-FE-11)
     *
     * Two questions, and only both together give the answer: is the desk
     * installed on this site at all, and is this person allowed at it. The
     * setting lives on the agent's visibility record, because "which desk"
     * and "how much of the queue" are the same question asked one level apart
     * and an administrator deciding one is already thinking about the other.
     *
     * Everybody who is not a governed agent gets both, and that is deliberate
     * rather than an omission: an administrator locked out of the help desk in
     * wp-admin by a setting on an agent screen would have no way back in.
     *
     * @return array shell keys.
     */
    public static function allowedShells($jsst_options = array()) {
        $jsst_actor = self::actor($jsst_options);
        $jsst_installed = array();
        foreach (array_keys(self::shells()) as $jsst_shell) {
            if (self::shellAvailable($jsst_shell)) {
                $jsst_installed[] = $jsst_shell;
            }
        }
        if (!class_exists('JSSTvisibility') || !JSSTvisibility::governs($jsst_actor)) {
            return $jsst_installed;
        }
        $jsst_record = JSSTvisibility::forAgent($jsst_actor['staffid']);
        $jsst_wanted = isset($jsst_record['workspaces']) ? $jsst_record['workspaces'] : JSSTvisibility::WORKSPACE_BOTH;
        $jsst_allowed = $jsst_installed;
        if ($jsst_wanted === JSSTvisibility::WORKSPACE_BACKEND) {
            $jsst_allowed = array_values(array_intersect($jsst_installed, array(self::SHELL_BACKEND)));
        } elseif ($jsst_wanted === JSSTvisibility::WORKSPACE_FRONTEND) {
            $jsst_allowed = array_values(array_intersect($jsst_installed, array(self::SHELL_FRONTEND)));
        }
        /* Never all the way to nothing. This setting is for choosing between
           two desks, not for removing somebody from the help desk - and the
           way it would otherwise happen is quiet and delayed: an administrator
           puts the contractors on the front-end desk in March, the Agents
           module is deactivated in July, and a group of people who were
           working yesterday can now reach neither desk with nothing to say
           why. Where the chosen desk is gone, the one that is left is better
           than none. Taking access away is what the scopes above are for, and
           they say so on their own screen. */
        return empty($jsst_allowed) ? $jsst_installed : $jsst_allowed;
    }

    /**
     * May this person work at this desk?
     *
     * The question every enforcement point asks. Hiding the menu is not the
     * enforcement and never was - it is what stops somebody being shown a door
     * they cannot open. The enforcement is that the screens behind it are not
     * registered and the tasks behind those refuse.
     */
    public static function mayUse($jsst_shell, $jsst_options = array()) {
        return in_array($jsst_shell, self::allowedShells($jsst_options), true);
    }

    /** Is this shell present on this site at all? */
    public static function shellAvailable($jsst_shell) {
        $jsst_shells = self::shells();
        if (!isset($jsst_shells[$jsst_shell])) {
            return false;
        }
        $jsst_module = $jsst_shells[$jsst_shell]['module'];
        if ($jsst_module === '') {
            return true;
        }
        return in_array($jsst_module, jssupportticket::$_active_addons);
    }

    /**
     * Which files make up each screen of each shell.
     *
     * A screen is its controller and its template together, because the two
     * halves of one screen are where a feature can be half-implemented: a
     * controller that fetches something no template renders, or a template
     * whose form posts to a task that was never written. The probe searches
     * both and does not care which half it finds the answer in.
     */
    public static function screens() {
        return array(
            self::SHELL_BACKEND => array(
                'queue'  => array(
                    array('module' => 'ticket', 'type' => 'controller'),
                    array('module' => 'ticket', 'type' => 'file', 'tpl' => 'admin_tickets'),
                ),
                'detail' => array(
                    array('module' => 'ticket', 'type' => 'controller'),
                    array('module' => 'ticket', 'type' => 'file', 'tpl' => 'admin_ticketdetail'),
                ),
                'create' => array(
                    array('module' => 'ticket', 'type' => 'controller'),
                    array('module' => 'ticket', 'type' => 'file', 'tpl' => 'admin_addticket'),
                ),
                /* The desk home and the customer list are core module screens
                   in both workspaces - one template each per shell, wrapped
                   around a body that is printed once. The chrome is what is
                   probed here, because the chrome is the part each shell owns
                   and therefore the part that can quietly stop rendering the
                   shared screen. (Roadmap 4.5-FE-02) */
                'home' => array(
                    array('module' => 'jssupportticket', 'type' => 'controller'),
                    array('module' => 'jssupportticket', 'type' => 'file', 'tpl' => 'admin_workspacehome'),
                ),
                'people' => array(
                    array('module' => 'jssupportticket', 'type' => 'file', 'tpl' => 'admin_customers'),
                ),
            ),
            self::SHELL_FRONTEND => array(
                'queue'  => array(
                    array('module' => 'agent', 'type' => 'controller'),
                    array('module' => 'agent', 'type' => 'file', 'tpl' => 'staffmyticket'),
                ),
                /* The front-end detail page is core's, not the add-on's: one
                   template serves the customer and the agent, branching on
                   whether the reader is staff. That is why the front-end desk
                   has a ticket page at all without the add-on shipping one, and
                   why the agent half of it is so much harder to see. */
                'detail' => array(
                    array('module' => 'ticket', 'type' => 'controller'),
                    array('module' => 'ticket', 'type' => 'file', 'tpl' => 'ticketdetail'),
                ),
                'create' => array(
                    array('module' => 'agent', 'type' => 'controller'),
                    array('module' => 'agent', 'type' => 'file', 'tpl' => 'staffaddticket'),
                ),
                'home' => array(
                    array('module' => 'jssupportticket', 'type' => 'controller'),
                    array('module' => 'jssupportticket', 'type' => 'file', 'tpl' => 'workspacehome'),
                ),
                'people' => array(
                    array('module' => 'jssupportticket', 'type' => 'file', 'tpl' => 'customers'),
                ),
            ),
        );
    }

    /* =====================================================================
     * The descriptors - what both shells render from
     * ================================================================== */

    /**
     * Every verb the desk has, in the order a toolbar shows them.
     *
     * One row per command, and the row says everything a shell needs: which
     * permission it passes through, which application-layer command performs
     * it, whether it takes a value, and whether a batch of tickets can be put
     * through it. A shell that renders this list has the same toolbar as the
     * other shell by construction, and a module that adds a verb adds it here
     * rather than to one template.
     *
     * 'bulk' is not a judgement made here - it is JSSTticketservice::bulk()'s
     * own list of commands it will run. Marking a verb bulkable that the
     * service refuses is how a screen offers a button that always fails.
     */
    public static function commands() {
        static $jsst_commands = null;
        if ($jsst_commands !== null) {
            return $jsst_commands;
        }
        $jsst_commands = array(
            'reply' => array(
                'label'   => __('Reply', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_REPLY,
                'command' => 'reply',
                'input'   => 'composer',
                'bulk'    => false,
            ),
            'note' => array(
                'label'   => __('Internal note', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_NOTE,
                'command' => 'note',
                'input'   => 'composer',
                'bulk'    => false,
            ),
            'status' => array(
                'label'   => __('Status', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_STATUS,
                'command' => 'status',
                'input'   => 'choice',
                'bulk'    => true,
            ),
            'priority' => array(
                'label'   => __('Priority', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_PRIORITY,
                'command' => 'priority',
                'input'   => 'choice',
                'bulk'    => true,
            ),
            'assign' => array(
                'label'   => __('Assign', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_ASSIGN,
                'command' => 'assign',
                'input'   => 'choice',
                'bulk'    => true,
            ),
            'transfer' => array(
                'label'   => __('Transfer', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_TRANSFER,
                'command' => 'transfer',
                'input'   => 'choice',
                'bulk'    => true,
            ),
            'progress' => array(
                'label'   => __('Mark in progress', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_PROGRESS,
                'command' => 'progress',
                'input'   => 'none',
                'bulk'    => true,
            ),
            'close' => array(
                'label'   => __('Close', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_CLOSE,
                'command' => 'close',
                'input'   => 'none',
                'bulk'    => true,
            ),
            'reopen' => array(
                'label'   => __('Reopen', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_REOPEN,
                'command' => 'reopen',
                'input'   => 'none',
                'bulk'    => true,
            ),
            'merge' => array(
                'label'   => __('Merge', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_MERGE,
                'command' => 'merge',
                'input'   => 'ticket',
                'bulk'    => false,
                'module'  => 'mergeticket',
            ),
            'delete' => array(
                'label'   => __('Delete', 'js-support-ticket'),
                'action'  => JSSTcapability::TICKET_DELETE,
                'command' => 'delete',
                'input'   => 'none',
                'bulk'    => true,
                /* Last in the list and last on the toolbar. The order here is
                   the order rendered, and a destructive verb next to Close is
                   a support ticket waiting to be raised. */
            ),
        );
        return apply_filters('jsst_workspace_commands', $jsst_commands);
    }

    /**
     * The buttons this actor may press on this ticket.
     *
     * The permission answers come from JSSTticketquery::allowedActions(), which
     * asks the capability service once per scoped action - so the toolbar and
     * the command that runs when it is pressed are answering the same question
     * from the same place. A shell that decides its own buttons and then calls
     * the service gets the enforcement right and the display wrong, which is
     * the failure that looks like a bug in the button.
     *
     * @param int   $jsst_ticketid
     * @param array $jsst_options 'actor', 'all' to include refused verbs.
     * @return array key => descriptor, each carrying 'allowed'.
     */
    public static function toolbar($jsst_ticketid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_all = !empty($jsst_options['all']);
        $jsst_can = JSSTticketquery::allowedActions($jsst_ticketid, $jsst_actor);
        $jsst_out = array();
        foreach (self::commands() as $jsst_key => $jsst_command) {
            if (!self::moduleAvailable($jsst_command)) {
                continue;
            }
            $jsst_action = $jsst_command['action'];
            $jsst_allowed = isset($jsst_can[$jsst_action])
                ? (bool) $jsst_can[$jsst_action]
                : JSSTcapability::can($jsst_action, array('ticket' => $jsst_ticketid), $jsst_actor);
            if (!$jsst_allowed && !$jsst_all) {
                continue;
            }
            $jsst_command['key'] = $jsst_key;
            $jsst_command['allowed'] = $jsst_allowed;
            $jsst_out[$jsst_key] = $jsst_command;
        }
        return $jsst_out;
    }

    /**
     * The verbs that may be applied to a selection.
     *
     * Answered without a ticket, because a queue offering bulk actions has not
     * chosen the tickets yet. A scoped action asked with no subject means "could
     * this actor do this to some ticket", which is the right question for a
     * toolbar and the wrong one for a command - and is why JSSTticketservice
     * asks again, per ticket, when the batch actually runs.
     */
    public static function bulkActions($jsst_options = array()) {
        $jsst_actor = self::actor($jsst_options);
        $jsst_out = array();
        foreach (self::commands() as $jsst_key => $jsst_command) {
            if (empty($jsst_command['bulk']) || !self::moduleAvailable($jsst_command)) {
                continue;
            }
            if (!JSSTcapability::can($jsst_command['action'], array(), $jsst_actor)) {
                continue;
            }
            $jsst_command['key'] = $jsst_key;
            $jsst_command['allowed'] = true;
            $jsst_out[$jsst_key] = $jsst_command;
        }
        return $jsst_out;
    }

    /**
     * The bulk actions a queue can actually run, for this actor.
     * (Roadmap 4.5-UX-01)
     *
     * Not the same list as bulkActions() above, and the difference is worth
     * being precise about. That one is the verbs the application layer's
     * batch runner accepts. This one is the verbs the queue screens run, which
     * still go through the actions model - it carries the lock and unlock
     * pair the service has no command for, and it records a reason against
     * every ticket, which nothing else does.
     *
     * What changes here is who decides. The actions model asked
     * JSSTticketaction::canRun(), which answers from whichever of the two
     * permission systems it reaches first; every entry is now put to the
     * capability service instead, with the actor as an argument. The same
     * question is asked again per ticket when the batch runs - a bulk action
     * that checks once and then loops is how an agent closes a department they
     * cannot see.
     *
     * @return array key => label, ready for a combobox.
     */
    public static function queueBulk($jsst_options = array()) {
        if (!class_exists('JSSTticketaction') || !JSSTmergedaddon::coreOwns('actions')) {
            return array();
        }
        $jsst_actor = self::actor($jsst_options);
        $jsst_out = array();
        foreach (JSSTticketaction::bulkActions() as $jsst_key => $jsst_action) {
            $jsst_capability = self::bulkCapability($jsst_key);
            /* An action a third party added through the filter names no
               capability this class knows, and is deliberately not assumed to
               be covered by one - the same stance the actions model takes.
               It is offered only where the old check would also have offered
               it, so nothing a site relies on disappears. */
            if ($jsst_capability === '') {
                if (JSSTticketaction::canRun($jsst_action['permission'])) {
                    $jsst_out[$jsst_key] = $jsst_action['label'];
                }
                continue;
            }
            if (JSSTcapability::can($jsst_capability, array(), $jsst_actor)) {
                $jsst_out[$jsst_key] = $jsst_action['label'];
            }
        }
        return $jsst_out;
    }

    /**
     * Which permission one of those bulk actions passes through.
     *
     * A map rather than a naming convention, because the runner's keys are its
     * own and predate the action catalogue. '' means this class does not know
     * the action - see queueBulk() for what happens then.
     */
    public static function bulkCapability($jsst_key) {
        $jsst_map = apply_filters('jsst_workspace_bulk_capabilities', array(
            'lock'       => JSSTcapability::TICKET_LOCK,
            'unlock'     => JSSTcapability::TICKET_LOCK,
            'inprogress' => JSSTcapability::TICKET_PROGRESS,
            'close'      => JSSTcapability::TICKET_CLOSE,
            'reopen'     => JSSTcapability::TICKET_REOPEN,
            'priority'   => JSSTcapability::TICKET_PRIORITY,
            'department' => JSSTcapability::TICKET_TRANSFER,
        ));
        return isset($jsst_map[$jsst_key]) ? $jsst_map[$jsst_key] : '';
    }

    /**
     * The queue tabs, with this actor's counts on them.
     *
     * JSSTqueue owns what the tabs are and JSSTticketquery owns what is in
     * them; this puts the two together so that neither shell has to, and - the
     * point of the exercise - so that a tab number means the same queue in both.
     * It does not today: the front-end queue numbers its own tabs, and tab 2
     * there is Closed while LIST_ANSWERED is 2 everywhere else in the plugin.
     * A shell rendering from here cannot make that mistake, because it never
     * writes a number down.
     */
    public static function tabs($jsst_options = array()) {
        $jsst_actor = self::actor($jsst_options);
        $jsst_counts = JSSTticketquery::counts(array('actor' => $jsst_actor));
        $jsst_out = array();
        foreach (JSSTqueue::tabs() as $jsst_list => $jsst_tab) {
            /* A tab whose data is written by a module that is not installed
               would read zero for ever, which reads as "nothing is overdue"
               rather than as "nothing is measuring that". */
            if (!empty($jsst_tab['addon']) && !in_array($jsst_tab['addon'], jssupportticket::$_active_addons)) {
                continue;
            }
            $jsst_tab['list'] = (int) $jsst_list;
            $jsst_tab['total'] = (isset($jsst_tab['count']) && isset($jsst_counts[$jsst_tab['count']]))
                ? (int) $jsst_counts[$jsst_tab['count']] : 0;
            $jsst_out[(int) $jsst_list] = $jsst_tab;
        }
        return $jsst_out;
    }

    /**
     * Whose work this is: mine, nobody's, my team's, everyone's.
     *
     * Expressed as filters rather than as queries, so that a scope composes
     * with a tab and with a search instead of replacing them - "unassigned"
     * and "overdue" are two answers to two different questions and an agent
     * wants both at once. The staff filter takes -1 for "me" so that a caller
     * never has to know its own staff id, and 0 for "nobody", which is the
     * distinction the front-end desk has no way of drawing today.
     *
     * The team scope is only offered where a team means something: an agent
     * scoped to their departments has one, an agent who can see everything is
     * already looking at it, and an agent scoped to their own assignments has
     * no team by definition.
     */
    public static function scopes($jsst_options = array()) {
        /* The inbox scopes come with Agents & Teams, and the gate is here
           rather than in each queue screen because a scope is applied from the
           request as well as drawn from this list: JSSTqueue::applyScope() and
           JSSTqueueengine::state() both look a `scope` key up in what this
           returns, so a desk without the module was hiding the buttons in the
           template while still honouring `&scope=mine` typed into the address.
           An empty list is the one answer all three readers already handle.
           (Roadmap 4.5-FE-02) */
        if (!in_array('agent', jssupportticket::$_active_addons)) {
            return array();
        }
        $jsst_actor = self::actor($jsst_options);
        /* 'control' names the form field a queue screen sets to express this
           scope. It is here rather than in each template because the two desks
           were deciding it separately, and a descriptor that has to be read
           differently on each desk is not a shared descriptor. */
        $jsst_out = array(
            'mine' => array(
                'label'   => __('Assigned to me', 'js-support-ticket'),
                'filters' => array('staffid' => -1),
                'control' => 'staffid',
            ),
            'unassigned' => array(
                'label'   => __('Unassigned', 'js-support-ticket'),
                'filters' => array('staffid' => 0),
                'control' => 'staffid',
            ),
        );
        /* The tickets this agent was put on. (Roadmap 4.5-FE-08)

           Being added under "Who else is on this" wrote a watcher row that
           nothing read: no notification, no list, no filter. An agent made a
           second pair of hands on a ticket had no way to find it. This is the
           scope that answers it, and it lives here rather than in a template
           because both desks read this list - so the portal queue and the
           admin queue get it from one place.

           Offered only to somebody who is on the agent list, because the
           watcher table is keyed by staff id and a scope that can only ever
           return nothing is worse than no scope. Not gated on being a watcher
           of anything right now: an empty "Following" is a true answer, and
           the button disappearing the moment the last ticket is closed would
           read as a fault. */
        if (class_exists('JSSTcollab') && (int) $jsst_actor['staffid'] > 0) {
            $jsst_out['following'] = array(
                'label'   => __('I am on it', 'js-support-ticket'),
                'filters' => array('following' => 1),
                'control' => 'following',
            );
        }
        /* The team queue, where a team is a real thing rather than a stand-in
           for a department. Until teams existed this scope had no filter of
           its own and meant "everything my scope clause already lets me see",
           which on a site where three teams share Billing is not a team queue
           at all - it is the department's, with a misleading name.

           An agent on no team is not offered it. Falling back to the
           department would put the old, wrong answer behind the right label,
           and the two scopes either side of it already cover that case.
           (Roadmap 4.5-FE-05) */
        if (class_exists('JSSTteams') && JSSTteams::hasTeam($jsst_actor['staffid'])) {
            $jsst_teams = JSSTteams::teamsFor($jsst_actor['staffid']);
            $jsst_first = key($jsst_teams);
            $jsst_out['team'] = array(
                'label'   => (count($jsst_teams) === 1)
                    ? $jsst_teams[$jsst_first]->name
                    : __('My teams', 'js-support-ticket'),
                'filters' => array('staffids' => JSSTteams::colleaguesOf($jsst_actor['staffid'])),
                /* The queue screens express this as a team id rather than as a
                   list of agents, because their filter controls take one value
                   and the models expand it. Somebody on two teams gets the
                   first; the list above is the complete answer for anything
                   rendering from the descriptors directly. */
                'control' => 'teamid',
                'value'   => (int) $jsst_first,
            );
        }
        $jsst_out['all'] = array(
            'label'   => __('Everything I can see', 'js-support-ticket'),
            'filters' => array(),
            'control' => 'staffid',
        );
        if (!empty($jsst_options['counts'])) {
            foreach ($jsst_out as $jsst_key => $jsst_scope) {
                $jsst_page = JSSTticketquery::queues(array_merge(
                    $jsst_scope['filters'],
                    array('actor' => $jsst_actor, 'limit' => 1)
                ));
                $jsst_out[$jsst_key]['total'] = empty($jsst_page['ok']) ? 0 : (int) $jsst_page['total'];
            }
        }
        return $jsst_out;
    }

    /** The saved views this person has, whichever shell they are in. */
    public static function views() {
        return class_exists('JSSTqueue') ? JSSTqueue::getViews() : array();
    }

    /**
     * The keyboard shortcuts, defined once for both shells.
     *
     * Muscle memory does not know which workspace it is in. An agent who
     * learns r for reply in wp-admin and finds it does nothing in the portal
     * has been given two products, so the map lives here and each shell binds
     * what it is given rather than choosing its own keys.
     *
     * Every entry names a command from commands() or a navigation target; a
     * shell binds a key only when the matching verb is on the toolbar, so a
     * shortcut can never do something the button would have refused.
     */
    public static function shortcuts() {
        return apply_filters('jsst_workspace_shortcuts', array(
            'r' => array('kind' => 'command', 'target' => 'reply',    'label' => __('Reply', 'js-support-ticket')),
            'n' => array('kind' => 'command', 'target' => 'note',     'label' => __('Internal note', 'js-support-ticket')),
            'a' => array('kind' => 'command', 'target' => 'assign',   'label' => __('Assign', 'js-support-ticket')),
            't' => array('kind' => 'command', 'target' => 'transfer', 'label' => __('Transfer', 'js-support-ticket')),
            'c' => array('kind' => 'command', 'target' => 'close',    'label' => __('Close', 'js-support-ticket')),
            'o' => array('kind' => 'command', 'target' => 'reopen',   'label' => __('Reopen', 'js-support-ticket')),
            'p' => array('kind' => 'command', 'target' => 'priority', 'label' => __('Priority', 'js-support-ticket')),
            'j' => array('kind' => 'move',    'target' => 'next',     'label' => __('Next ticket', 'js-support-ticket')),
            'k' => array('kind' => 'move',    'target' => 'previous', 'label' => __('Previous ticket', 'js-support-ticket')),
            '/' => array('kind' => 'focus',   'target' => 'search',   'label' => __('Search', 'js-support-ticket')),
            '?' => array('kind' => 'help',    'target' => 'shortcuts','label' => __('Show shortcuts', 'js-support-ticket')),
        ));
    }

    /**
     * Articles this ticket is probably about.
     *
     * The knowledge base has always been a place an agent goes. What a desk
     * needs is the opposite: the two or three articles that answer this ticket,
     * beside the composer, before the agent writes the same answer by hand for
     * the fortieth time. That is one query, and having it in the layer rather
     * than in a template is what lets both shells show it and lets the answer
     * be the same in both.
     *
     * Deliberately filterable before it does any work of its own. The Instant
     * Resolve module already carries a proper retrieval stack - chunking,
     * ranking, several sources pooled into one MATCH - and where that module is
     * running it should answer this, not a second and worse implementation
     * living here. The query below is the fallback for a site with the
     * knowledge base and nothing else.
     *
     * @param int   $jsst_ticketid
     * @param array $jsst_options 'actor', 'limit'.
     * @return array of id, subject, category, visible, score.
     */
    public static function suggestions($jsst_ticketid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_limit = isset($jsst_options['limit']) ? max(1, min(10, (int) $jsst_options['limit'])) : 3;

        $jsst_suggestions = apply_filters('jsst_workspace_suggestions', null, $jsst_ticketid, $jsst_actor, $jsst_limit);
        if (is_array($jsst_suggestions)) {
            return $jsst_suggestions;
        }
        if (!in_array('knowledgebase', jssupportticket::$_active_addons)) {
            return array();
        }
        /* Two permissions, both of them real. Reading the knowledge base is
           its own action, and a suggestion is drawn from a ticket - so an
           actor who may not see the ticket must not learn what it is about
           from the articles offered against it. */
        if (!JSSTcapability::can(JSSTcapability::KB_VIEW, array(), $jsst_actor)
            || !JSSTcapability::can(JSSTcapability::TICKET_VIEW, array('ticket' => $jsst_ticketid), $jsst_actor)) {
            return array();
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_ticket = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT subject, message FROM `' . $jsst_prefix . 'js_ticket_tickets` WHERE id = %d',
            $jsst_ticketid
        ));
        if (!$jsst_ticket) {
            return array();
        }
        /* The subject and the first part of the message, with the markup taken
           out. Whole ticket bodies make poor search terms - a signature, a
           quoted thread and three paragraphs of context drown the one sentence
           that says what is wrong - and a very long term string is also how a
           MATCH stops using the index. */
        $jsst_terms = trim(wp_strip_all_tags((string) $jsst_ticket->subject . ' '
            . jssupportticketphplib::JSST_substr((string) $jsst_ticket->message, 0, 400)));
        if ($jsst_terms === '') {
            return array();
        }
        $jsst_select = 'SELECT article.id, article.subject, article.visible, category.name AS category, '
            . 'MATCH(article.subject, article.content) AGAINST (%s) AS score '
            . 'FROM `' . $jsst_prefix . 'js_ticket_articles` AS article '
            . 'LEFT JOIN `' . $jsst_prefix . 'js_ticket_categories` AS category ON article.categoryid = category.id '
            . 'WHERE article.status = 1 AND MATCH(article.subject, article.content) AGAINST (%s) '
            . 'ORDER BY score DESC LIMIT %d';

        /* The full-text index arrived in a schema upgrade, so a site that has
           not run it has the column and not the index and MATCH is a hard
           error. Suppressing the error and falling back to a subject match is
           the difference between a ticket page that shows no suggestions and a
           ticket page that shows a database error to a customer. */
        $jsst_suppress = jssupportticket::$_db->suppress_errors(true);
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            $jsst_select, $jsst_terms, $jsst_terms, $jsst_limit
        ));
        $jsst_failed = (jssupportticket::$_db->last_error != null);
        jssupportticket::$_db->suppress_errors($jsst_suppress);

        if ($jsst_failed) {
            $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
                'SELECT article.id, article.subject, article.visible, category.name AS category, 0 AS score '
                . 'FROM `' . $jsst_prefix . 'js_ticket_articles` AS article '
                . 'LEFT JOIN `' . $jsst_prefix . 'js_ticket_categories` AS category ON article.categoryid = category.id '
                . 'WHERE article.status = 1 AND article.subject LIKE %s '
                . 'ORDER BY article.views DESC LIMIT %d',
                '%' . jssupportticket::$_db->esc_like(jssupportticketphplib::JSST_substr((string) $jsst_ticket->subject, 0, 60)) . '%',
                $jsst_limit
            ));
        }
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * The two things an agent can be part-way through writing on a ticket.
     *
     * Kept apart on purpose - a half-written internal note restored into the
     * public reply box is the worst outcome this feature can produce, and it
     * is JSSTdraft that already keeps them apart. This only names them, so
     * that a shell asks for the mode it is rendering rather than passing a
     * string it made up.
     */
    public static function draftModes() {
        return array(
            'reply' => __('Reply to the customer', 'js-support-ticket'),
            'note'  => __('Internal note', 'js-support-ticket'),
        );
    }

    /* =====================================================================
     * The parity matrix
     * ================================================================== */

    /**
     * Every capability an agent desk is required to have, and where each shell
     * stands on it.
     *
     * The list is FE-01's, one row per item, and it does not grow on its own -
     * a row here is a promise that both workspaces will have the thing, so
     * adding one is a decision rather than a note.
     *
     * Each row carries:
     *
     *   group    Heading on the screen.
     *   label    What the capability is, as an agent would describe it.
     *   detail   Why it matters, or what goes wrong without it.
     *   screen   Which screen of each shell to look in.
     *   action   The capability action it must pass through. Checked against
     *            the catalogue: a feature enforced by no action at all is the
     *            exit gate's "enforced only in the UI".
     *   service  The application-layer call that performs it, checked with
     *            is_callable. A row whose service has gone missing fails for
     *            both shells at once, which is the correct answer - neither
     *            shell can be right about a layer that is not there.
     *   shared   Needles that prove the shell reaches the shared layer.
     *            Specific to the feature, and deliberately so. JSSTqueueengine
     *            ::state() returns tabs, scopes, filters, columns, views and
     *            bulk actions together, so a shell that calls it once would
     *            have satisfied a needle naming it for all six - and six rows
     *            would have gone green off one call, whether or not anything
     *            was rendered from five of them. A needle has to be the thing
     *            the shell can only have written because it renders that
     *            feature: the call that answers only this question, or the
     *            markup only this feature emits.
     *   own      Needles that prove the shell has its own implementation.
     *            Per-shell where the two spell it differently.
     *   module   The module this belongs to; absent from the site means the
     *            row is not applicable rather than failed.
     */
    public static function features() {
        $jsst_features = array(

            /* ---- The queue -------------------------------------------- */

            'queue.list' => array(
                'group'   => 'queues',
                'label'   => __('One queue, scoped once', 'js-support-ticket'),
                'detail'  => __('The list an agent sees and the ticket they may open are the same answer to the same question. Where the two are written separately, the gap between them is a ticket missing from the queue that still opens when its number is typed into the address bar.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTticketquery::queues',
                'shared'  => array('JSSTqueueengine::results', 'JSSTticketquery::queues'),
                'own'     => array(
                    self::SHELL_BACKEND  => array('getTicketsForAdmin'),
                    self::SHELL_FRONTEND => array('getStaffTickets'),
                ),
            ),
            'queue.tabs' => array(
                'group'   => 'queues',
                'label'   => __('The same tabs, meaning the same things', 'js-support-ticket'),
                'detail'  => __('The front-end queue numbers its own tabs, and its tab 2 is Closed while every other part of the plugin reads 2 as Answered. A bookmark, a saved view or a support instruction therefore lands somewhere different depending on which desk it is opened in.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTworkspace::tabs',
                'shared'  => array('JSSTqueueengine::state', 'JSSTworkspace::tabs'),
                'own'     => array(
                    self::SHELL_BACKEND  => array('JSSTqueue::tabs'),
                    self::SHELL_FRONTEND => array('data-tab-number'),
                ),
            ),
            'queue.scopes' => array(
                'group'   => 'queues',
                'label'   => __('Assigned to me, unassigned, my team', 'js-support-ticket'),
                'detail'  => __('An agent starting their day asks three questions in order: what is mine, what has nobody picked up, and what is my team carrying. A staff dropdown answers the first and can be made to answer the second by knowing which entry means nobody.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTworkspace::scopes',
                'shared'  => array('jsst-queue-scope'),
                'own'     => array('JSSTworkspace::scopes', 'getStaffForCombobox'),
            ),
            'queue.search' => array(
                'group'   => 'queues',
                'label'   => __('One search across everything a ticket is made of', 'js-support-ticket'),
                'detail'  => __('The backend has a single box that searches subject, message, replies, customer and reference. The front-end desk has a field-by-field search instead, so the same words find different tickets in the two workspaces.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTqueue::searchFilter',
                'shared'  => array('JSSTqueueengine::filtersFromRequest', 'JSSTticketquery::queues'),
                'own'     => array(
                    self::SHELL_BACKEND  => array('keywords'),
                    self::SHELL_FRONTEND => array('combinesearch'),
                ),
            ),
            'queue.filters' => array(
                'group'   => 'queues',
                'label'   => __('Filters', 'js-support-ticket'),
                'detail'  => __('Department, priority, status, product, help topic and date. Both desks have them; each builds its own field list, and they are not the same list.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTqueueengine::filters',
                'shared'  => array('JSSTqueueengine::filters'),
                'own'     => array('jsst_search_field_array'),
            ),
            'queue.views' => array(
                'group'   => 'queues',
                'label'   => __('Saved views', 'js-support-ticket'),
                'detail'  => __('A filter combination an agent uses every morning, kept by name. The views themselves are stored per person and are already shared between workspaces; only the backend offers any way to use them.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTworkspace::views',
                'shared'  => array('JSSTworkspace::views', 'jsst-queue-views'),
                'own'     => array('JSSTqueue::getViewsForCombobox', 'savedview'),
            ),
            'queue.bulk' => array(
                'group'   => 'queues',
                'label'   => __('Bulk actions', 'js-support-ticket'),
                'detail'  => __('Close, reopen, reassign or transfer a selection. The backend has them and decides who may run them without asking the capability service; the front-end desk has none.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::TICKET_STATUS,
                'service' => 'JSSTworkspace::queueBulk',
                'shared'  => array('JSSTworkspace::queueBulk', 'jsst-queue-bulk'),
                'own'     => array('JSSTticketaction::bulkActions'),
            ),

            'queue.columns' => array(
                'group'   => 'queues',
                'label'   => __('Columns each agent chooses', 'js-support-ticket'),
                'detail'  => __('Which columns a queue shows has been a site-wide setting an administrator makes once for everybody. A person working a billing queue and a person working a technical one want different columns, and neither of them is the administrator.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTqueueengine::visibleColumns',
                'shared'  => array('JSSTqueueengine::visibleColumns', 'queuecolumns[]'),
                'own'     => array('getFieldsForListing'),
            ),

            /* ---- Working a ticket -------------------------------------- */

            'ticket.create' => array(
                'group'   => 'ticket',
                'label'   => __('Raise a ticket for a customer', 'js-support-ticket'),
                'detail'  => __('An agent taking a request by telephone raises it as the customer, not as themselves. It is a permission of its own, and neither desk asks for it by name yet.', 'js-support-ticket'),
                'screen'  => 'create',
                'action'  => JSSTcapability::TICKET_CREATE_FOR,
                'service' => 'JSSTticketservice::create',
                'shared'  => array('JSSTticketservice::create', 'TICKET_CREATE_FOR'),
                'own'     => array('getTicketsForForm'),
            ),
            'ticket.reply' => array(
                'group'   => 'ticket',
                'label'   => __('Answer the customer, with attachments', 'js-support-ticket'),
                'detail'  => __('The verb the whole product exists for. Both desks have it and both post to the same task, which is why they behave alike today - and why moving one of them onto the service without the other would be the first real divergence.', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_REPLY,
                'service' => 'JSSTticketservice::reply',
                'shared'  => array('JSSTticketservice::reply'),
                'own'     => array('savereply'),
            ),
            'ticket.note' => array(
                'group'   => 'ticket',
                'label'   => __('Internal notes', 'js-support-ticket'),
                'detail'  => __('Written for colleagues and never sent. The permission is separate from replying, because an agent who may talk to the team is not necessarily one who may talk to the customer.', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_NOTE,
                'service' => 'JSSTticketservice::note',
                'shared'  => array('JSSTticketservice::note'),
                'own'     => array('savenote'),
            ),
            'ticket.timeline' => array(
                'group'   => 'ticket',
                'label'   => __('The timeline', 'js-support-ticket'),
                'detail'  => __('Replies, notes and everything that happened to the ticket, in one order. What an agent reads before answering, and the thing a reduced ticket page is usually reduced by.', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_VIEW,
                'service' => 'JSSTticketquery::detail',
                'shared'  => array('JSSTticketquery::detail'),
                'own'     => array('tickethistory'),
            ),
            'ticket.status' => array(
                'group'   => 'ticket',
                'label'   => __('Status', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_STATUS,
                'service' => 'JSSTticketservice::status',
                'shared'  => array('JSSTticketservice::status'),
                'own'     => array('changestatus'),
            ),
            'ticket.priority' => array(
                'group'   => 'ticket',
                'label'   => __('Priority', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_PRIORITY,
                'service' => 'JSSTticketservice::priority',
                'shared'  => array('JSSTticketservice::priority'),
                'own'     => array('changepriority'),
            ),
            'ticket.assign' => array(
                'group'   => 'ticket',
                'label'   => __('Assign to an agent', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_ASSIGN,
                'service' => 'JSSTticketservice::assign',
                'shared'  => array('JSSTticketservice::assign'),
                'own'     => array('assigntickettostaff'),
            ),
            'ticket.transfer' => array(
                'group'   => 'ticket',
                'label'   => __('Transfer to another department', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_TRANSFER,
                'service' => 'JSSTticketservice::transfer',
                'shared'  => array('JSSTticketservice::transfer'),
                'own'     => array('transferdepartment'),
            ),
            'ticket.close' => array(
                'group'   => 'ticket',
                'label'   => __('Close and reopen', 'js-support-ticket'),
                'detail'  => __('Two verbs rather than one status change, because closing sends mail, stops timers and is what a customer is told happened.', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_CLOSE,
                'service' => 'JSSTticketservice::close',
                'shared'  => array('JSSTticketservice::close'),
                'own'     => array('closeticket', 'reopenticket', 'actionticket'),
            ),
            'ticket.merge' => array(
                'group'   => 'ticket',
                'label'   => __('Merge duplicates', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_MERGE,
                'service' => 'JSSTticketservice::merge',
                'shared'  => array('JSSTticketservice::merge'),
                'own'     => array('mergeticket'),
                'module'  => 'mergeticket',
            ),

            /* ---- Help while writing ------------------------------------ */

            'assist.canned' => array(
                'group'   => 'assist',
                'label'   => __('Canned replies', 'js-support-ticket'),
                'detail'  => __('Both desks offer them. Neither asks the capability service whether this agent may use them, so the answer is whatever each template decided.', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::CANNED_VIEW,
                'service' => 'JSSTcapability::can',
                'shared'  => array('CANNED_VIEW'),
                'own'     => array('cannedresponses'),
            ),
            'assist.knowledge' => array(
                'group'   => 'assist',
                'label'   => __('Knowledge suggestions', 'js-support-ticket'),
                'detail'  => __('Articles the ticket is probably about, offered beside the composer. Neither desk has this: the knowledge base is a place an agent goes rather than something the ticket brings to them.', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::KB_VIEW,
                'service' => 'JSSTworkspace::suggestions',
                'shared'  => array('JSSTworkspace::suggestions'),
                'own'     => array('knowledgebase'),
                'module'  => 'knowledgebase',
            ),

            /* ---- The desk itself --------------------------------------- */

            'desk.navigation' => array(
                'group'   => 'desk',
                'label'   => __('One set of places to go', 'js-support-ticket'),
                'detail'  => __('Home, queues, the ticket, customers, knowledge, reports and notifications. The backend desk has whatever is left of the administration menu after the refusals are removed; the portal has the control panel\'s grid of every layout in the plugin. Neither is a description of a working day, and they are not the same list.', 'js-support-ticket'),
                'screen'  => 'queue',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTnavigation::menu',
                'shared'  => array('JSSTnavigation::renderNav'),
                'own'     => array(
                    self::SHELL_BACKEND  => array('jsstadminsidemenu', 'jsstagentsidemenu'),
                    self::SHELL_FRONTEND => array('js-ticket-menu-links'),
                ),
            ),
            'desk.home' => array(
                'group'   => 'desk',
                'label'   => __('A home to start the day on', 'js-support-ticket'),
                'detail'  => __('Personal workload, what has breached and what is about to, what nobody has picked up, where this agent was named, what they left half-written and what happened while they were away. Assembled today by opening the queue and sorting it four ways.', 'js-support-ticket'),
                'screen'  => 'home',
                'action'  => JSSTcapability::QUEUE_VIEW,
                'service' => 'JSSTnavigation::home',
                'shared'  => array('JSSTnavigation::renderHome'),
                'own'     => array('getControlPanelData', 'stack_chart_horizontal'),
            ),
            'desk.customers' => array(
                'group'   => 'desk',
                'label'   => __('Customers and companies', 'js-support-ticket'),
                'detail'  => __('Who has written in and what they asked before, scoped exactly as the queue is. The only customer list either desk had was the user report, which is a report - it answers how many, not who.', 'js-support-ticket'),
                'screen'  => 'people',
                'action'  => JSSTcapability::CUSTOMER_VIEW,
                'service' => 'JSSTticketquery::customers',
                'shared'  => array('customerspanel'),
                'own'     => array('userreport', 'getUsers'),
            ),
            'desk.notifications' => array(
                'group'   => 'desk',
                'label'   => __('A notification centre', 'js-support-ticket'),
                'detail'  => __('Assignments, mentions, escalations, replies and approvals in one place, with preferences and quiet hours. Neither desk has one: there are three separate notification paths and none of them is a list an agent can read. Described here so the shape of the desk is complete; it is 4.5-UX-02\'s to build.', 'js-support-ticket'),
                'screen'  => 'home',
                'action'  => '',
                'service' => '',
                'shared'  => array('JSSTnotifications'),
                'own'     => array('notificationcenter'),
            ),

            /* ---- The composer and the keyboard ------------------------- */

            'composer.draft' => array(
                'group'   => 'composer',
                'label'   => __('Draft autosave', 'js-support-ticket'),
                'detail'  => __('A long reply survives a refresh, an expired session and a closed laptop. The store is core and is already shared; only the backend composer uses it, so the same agent loses the same reply depending on which desk they wrote it in.', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_REPLY,
                'service' => 'JSSTdraft::save',
                'shared'  => array('JSSTdraft'),
                'own'     => array(),
            ),
            'composer.shortcuts' => array(
                'group'   => 'composer',
                'label'   => __('Keyboard shortcuts', 'js-support-ticket'),
                'detail'  => __('Muscle memory does not know which workspace it is in. The backend binds keys; the front-end desk binds none.', 'js-support-ticket'),
                'screen'  => 'detail',
                'action'  => JSSTcapability::TICKET_VIEW,
                'service' => 'JSSTworkspace::shortcuts',
                'shared'  => array('JSSTworkspace::shortcuts'),
                'own'     => array('jsst-shortcut'),
            ),
        );
        return apply_filters('jsst_workspace_features', $jsst_features);
    }

    /** The group headings, in the order the screen shows them. */
    public static function groups() {
        return array(
            'queues'   => __('The queue', 'js-support-ticket'),
            'ticket'   => __('Working a ticket', 'js-support-ticket'),
            'assist'   => __('Help while writing', 'js-support-ticket'),
            'desk'     => __('The desk itself', 'js-support-ticket'),
            'composer' => __('The composer and the keyboard', 'js-support-ticket'),
        );
    }

    /**
     * Run the matrix.
     *
     * Nothing is cached between requests. The screen that shows this is opened
     * by a developer who has just changed one of the files being read, and a
     * parity report that is a day old is worse than none - it would report the
     * work as undone after it was done, which is how a person learns to stop
     * believing a report.
     */
    public static function parity() {
        if (self::$jsst_parity !== null) {
            return self::$jsst_parity;
        }
        $jsst_shells = self::shells();
        $jsst_actions = JSSTcapability::actions();
        $jsst_rows = array();
        $jsst_totals = array();
        foreach (array_keys($jsst_shells) as $jsst_shell) {
            $jsst_totals[$jsst_shell] = array(
                self::PARITY_SHARED => 0, self::PARITY_OWN => 0, self::PARITY_MISSING => 0,
                self::PARITY_NA => 0, self::PARITY_NOSHELL => 0,
            );
        }

        foreach (self::features() as $jsst_key => $jsst_feature) {
            $jsst_row = array_merge(array(
                'key' => $jsst_key, 'group' => 'queues', 'label' => $jsst_key, 'detail' => '',
                'screen' => 'detail', 'action' => '', 'service' => '', 'shared' => array(),
                'own' => array(), 'module' => '',
            ), $jsst_feature);

            /* The two checks that are about the layer rather than about either
               shell. A feature with no capability action behind it is enforced
               by whatever each screen decided, which is the thing the release
               exit gate forbids; a feature whose service has gone means both
               shells are answering on their own whatever they appear to do. */
            $jsst_row['enforced'] = ($jsst_row['action'] !== '' && isset($jsst_actions[$jsst_row['action']]));
            $jsst_row['layer'] = ($jsst_row['service'] !== '' && self::serviceExists($jsst_row['service']));

            $jsst_row['cells'] = array();
            foreach (array_keys($jsst_shells) as $jsst_shell) {
                $jsst_cell = self::probe($jsst_shell, $jsst_row);
                $jsst_row['cells'][$jsst_shell] = $jsst_cell;
                $jsst_totals[$jsst_shell][$jsst_cell['state']]++;
            }

            /* Parity is a property of the pair, not of either cell, and there
               are four ways a pair can stand. Both through the layer is done.
               Both with their own copy is at parity in the only sense a
               customer can see and at none of the sense this task is about -
               it works today and will drift, so it is its own verdict rather
               than a pass. Neither having it at all is also matched, and
               calling that drift would be absurd: nothing is drifting, the
               capability simply is not built. Anything else is the case this
               screen exists for - one desk has it and the other does not. */
            $jsst_states = array();
            foreach ($jsst_row['cells'] as $jsst_cell) {
                if ($jsst_cell['state'] === self::PARITY_NA || $jsst_cell['state'] === self::PARITY_NOSHELL) {
                    continue;
                }
                $jsst_states[] = $jsst_cell['state'];
            }
            /* How many desks there are to compare matters as much as what they
               say. A site with one workspace has no parity question at all, and
               reporting its one implementation as "will drift" would be telling
               an administrator to worry about a disagreement that cannot
               happen here. Counted before the states are collapsed, because
               two desks that answer identically must not look like one. */
            $jsst_tested = count($jsst_states);
            $jsst_states = array_values(array_unique($jsst_states));
            $jsst_row['applicable'] = !empty($jsst_states);
            $jsst_row['matched'] = (count($jsst_states) === 1);
            $jsst_row['shells_tested'] = $jsst_tested;
            if (!$jsst_row['applicable']) {
                $jsst_row['verdict'] = self::PARITY_NA;
            } elseif ($jsst_row['matched'] && $jsst_states[0] === self::PARITY_SHARED
                && $jsst_row['enforced'] && $jsst_row['layer']) {
                $jsst_row['verdict'] = 'done';
            } elseif ($jsst_row['matched'] && $jsst_states[0] === self::PARITY_MISSING) {
                $jsst_row['verdict'] = 'absent';
            } elseif ($jsst_tested < 2) {
                $jsst_row['verdict'] = 'single';
            } elseif ($jsst_row['matched'] && $jsst_states[0] === self::PARITY_OWN) {
                $jsst_row['verdict'] = 'drifting';
            } else {
                $jsst_row['verdict'] = 'gap';
            }
            $jsst_row['done'] = ($jsst_row['verdict'] === 'done');
            $jsst_rows[$jsst_key] = $jsst_row;
        }

        self::$jsst_parity = array(
            'shells' => $jsst_shells,
            'groups' => self::groups(),
            'rows'   => $jsst_rows,
            'totals' => $jsst_totals,
        );
        return self::$jsst_parity;
    }

    /**
     * A one-line answer, for the System Status page and the release gate.
     *
     * 'done' counts only rows where both shells go through the layer, the
     * capability action exists and the service is callable. Anything less is
     * outstanding, however well it happens to work - split into the three ways
     * of being outstanding, because they are three different pieces of work:
     * 'drifting' is code to move, 'absent' is a capability to build, and 'gaps'
     * is the pair actually disagreeing.
     */
    public static function summary() {
        $jsst_report = self::parity();
        $jsst_out = array('total' => 0, 'done' => 0, 'drifting' => 0, 'absent' => 0, 'gaps' => 0,
            'single' => 0, 'unenforced' => array());
        foreach ($jsst_report['rows'] as $jsst_row) {
            if (empty($jsst_row['applicable'])) {
                continue;
            }
            $jsst_out['total']++;
            switch ($jsst_row['verdict']) {
                case 'done':     $jsst_out['done']++;     break;
                case 'drifting': $jsst_out['drifting']++; break;
                case 'absent':   $jsst_out['absent']++;   break;
                case 'single':   $jsst_out['single']++;   break;
                default:         $jsst_out['gaps']++;     break;
            }
            if (!$jsst_row['enforced']) {
                $jsst_out['unenforced'][] = $jsst_row['label'];
            }
        }
        return $jsst_out;
    }

    /* =====================================================================
     * The probe
     * ================================================================== */

    /**
     * What one shell does about one feature.
     *
     * Reads the shell's own source rather than asking it anything. A screen
     * cannot be interrogated at runtime without loading it, loading it runs its
     * controller, and running a controller to find out whether it has a feature
     * is how a diagnostic page starts creating tickets.
     */
    private static function probe($jsst_shell, $jsst_row) {
        if (!self::shellAvailable($jsst_shell)) {
            return array(
                'state'  => self::PARITY_NOSHELL,
                'note'   => __('This workspace is not installed on this site.', 'js-support-ticket'),
                'files'  => array(),
            );
        }
        if ($jsst_row['module'] !== '' && !in_array($jsst_row['module'], jssupportticket::$_active_addons)) {
            return array(
                'state'  => self::PARITY_NA,
                'note'   => __('The module this belongs to is not on this site.', 'js-support-ticket'),
                'files'  => array(),
            );
        }
        $jsst_screens = self::screens();
        $jsst_files = isset($jsst_screens[$jsst_shell][$jsst_row['screen']])
            ? $jsst_screens[$jsst_shell][$jsst_row['screen']] : array();
        $jsst_source = '';
        $jsst_read = array();
        foreach ($jsst_files as $jsst_file) {
            $jsst_path = JSSTincluder::getPluginPath(
                $jsst_file['module'],
                $jsst_file['type'],
                isset($jsst_file['tpl']) ? $jsst_file['tpl'] : ''
            );
            $jsst_text = self::source($jsst_path);
            if ($jsst_text === false) {
                continue;
            }
            $jsst_read[] = $jsst_path;
            $jsst_source .= $jsst_text;
        }
        if (empty($jsst_read)) {
            return array(
                'state'  => self::PARITY_MISSING,
                'note'   => __('This workspace has no such screen.', 'js-support-ticket'),
                'files'  => array(),
            );
        }
        if (self::found($jsst_source, self::needles($jsst_row['shared'], $jsst_shell))) {
            return array(
                'state'  => self::PARITY_SHARED,
                'note'   => __('Reached through the shared layer.', 'js-support-ticket'),
                'files'  => $jsst_read,
            );
        }
        if (self::found($jsst_source, self::needles($jsst_row['own'], $jsst_shell))) {
            return array(
                'state'  => self::PARITY_OWN,
                'note'   => __('Implemented in this workspace on its own.', 'js-support-ticket'),
                'files'  => $jsst_read,
            );
        }
        return array(
            'state'  => self::PARITY_MISSING,
            'note'   => __('Not in this workspace.', 'js-support-ticket'),
            'files'  => $jsst_read,
        );
    }

    /**
     * The needles for one shell.
     *
     * A row gives either one list for both shells or a list per shell, because
     * the two spell the same feature differently often enough that forcing one
     * list would mean writing needles loose enough to match anything.
     */
    private static function needles($jsst_needles, $jsst_shell) {
        if (!is_array($jsst_needles)) {
            return array();
        }
        if (isset($jsst_needles[$jsst_shell]) && is_array($jsst_needles[$jsst_shell])) {
            return $jsst_needles[$jsst_shell];
        }
        foreach (array_keys($jsst_needles) as $jsst_index) {
            /* A per-shell list keyed by the other shell's name only. Returning
               the whole array here would test this shell against the other
               shell's needles, which is how a matrix reports a feature present
               in a file that does not have it. */
            if (!is_int($jsst_index)) {
                return array();
            }
        }
        return $jsst_needles;
    }

    /** Does the source contain any of these? */
    private static function found($jsst_source, $jsst_needles) {
        foreach ($jsst_needles as $jsst_needle) {
            if ($jsst_needle !== '' && stripos($jsst_source, $jsst_needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /** One source file, read once per request. False if it is not there. */
    private static function source($jsst_path) {
        if ($jsst_path === '' || !is_string($jsst_path)) {
            return false;
        }
        if (array_key_exists($jsst_path, self::$jsst_sources)) {
            return self::$jsst_sources[$jsst_path];
        }
        self::$jsst_sources[$jsst_path] = file_exists($jsst_path)
            ? (string) file_get_contents($jsst_path) : false;
        return self::$jsst_sources[$jsst_path];
    }

    /** Is 'Class::method' something that can actually be called? */
    private static function serviceExists($jsst_service) {
        $jsst_parts = explode('::', $jsst_service);
        if (count($jsst_parts) !== 2) {
            return false;
        }
        return class_exists($jsst_parts[0]) && method_exists($jsst_parts[0], $jsst_parts[1]);
    }

    /* =====================================================================
     * Shared pieces
     * ================================================================== */

    /** Whoever the caller says is at the desk, or whoever is on the request. */
    private static function actor($jsst_options) {
        if (isset($jsst_options['actor']) && is_array($jsst_options['actor'])) {
            return $jsst_options['actor'];
        }
        if (isset($jsst_options['wpuid'])) {
            return JSSTcapability::actor((int) $jsst_options['wpuid']);
        }
        return JSSTcapability::actor();
    }

    /** Is the module a command belongs to available here? */
    private static function moduleAvailable($jsst_command) {
        if (empty($jsst_command['module'])) {
            return true;
        }
        return in_array($jsst_command['module'], jssupportticket::$_active_addons);
    }
}
