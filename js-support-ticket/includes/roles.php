<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Versioned, idempotent role & capability management. (Roadmap 3.2-CORE-01)
 *
 * Releases up to 3.1.7 granted `jsst_support_ticket_tickets` to the WordPress
 * Contributor role on every activation, which silently gave every Contributor on
 * the site agent-level access to every ticket and customer email address. They
 * also created the custom agent role with add_role(), which does nothing when
 * the role already exists, so capability corrections never reached sites that
 * had been upgraded rather than freshly installed.
 *
 * This class replaces both behaviours with one reconciliation pass that is safe
 * to run repeatedly and runs on activation and on every upgrade.
 */
class JSSTroles {

    /**
     * Bump whenever getCanonicalRoles() or the migration steps change, so the
     * reconciliation runs once more on every existing site.
     */
    const ROLE_VERSION = 7;

    const AGENT_ROLE       = 'js_support_ticket_admin_agent';
    const LIGHT_AGENT_ROLE = 'js_support_ticket_light_agent';
    const CAP_ADMIN        = 'jsst_support_ticket';
    const CAP_TICKETS      = 'jsst_support_ticket_tickets';
    const CAP_REPLY        = 'jsst_support_ticket_reply';
    const CAP_NOTE         = 'jsst_support_ticket_note';
    /* Moving a ticket through its life — status, close, reopen, priority,
       department, assignment. What separates somebody who works the queue from
       somebody who can only read it and leave notes. */
    const CAP_STATE        = 'jsst_support_ticket_state';
    /* Amending what has already been written — the ticket itself, or a reply
       after it was sent. Separate from CAP_STATE because moving a ticket through
       the queue and rewriting its content are different kinds of trust: the
       first is visible in the history, the second changes the record. */
    const CAP_EDIT         = 'jsst_support_ticket_edit';
    /* Authoring the knowledge base and the FAQ. Agent work rather than
       administration, for the same reason canned responses are: it is writing
       the answer you are about to give for the twentieth time. */
    const CAP_KB           = 'jsst_support_ticket_kb';
    /* Destroying a ticket is not queue work: it is irreversible and it removes
       the record of what a customer was told, so it is granted to nobody and a
       site administrator adds it to a role deliberately. Listed in
       getPluginCapabilities() so uninstall can still clean it up wherever it was
       granted. */
    const CAP_DELETE       = 'jsst_support_ticket_delete';
    /* Merging is queue work, and it is reversible - every merge can be unmerged,
       and the source ticket keeps every reply, note and attachment it had. It
       does three things the Agent role can already do by hand (close the source,
       post a reply on each side, relate the two), so the Agent role holds it and
       the Light Agent role does not: somebody who may not answer the customer
       should not be posting the reply a merge writes, or closing the ticket it
       closes. (Roadmap 4.0-CORE-04) */
    const CAP_MERGE        = 'jsst_support_ticket_merge';

    const OPT_VERSION   = 'jsst_role_version';

    /** Per-request cache of the explicit agent list. null = not yet resolved. */
    private static $jsst_agent_ids = null;

    /** Cache for agentAddonGoverns(), keyed by user id. */
    private static $jsst_addon_governs = array();

    /** Cache of add-on task answers, keyed by "<user id>|<task name>". */
    private static $jsst_task_answers = array();

    /**
     * The canonical role definition. This is the single source of truth: the
     * reconciler adds every capability listed here and removes every plugin
     * capability that is not listed, so a role cannot drift.
     */
    public static function getCanonicalRoles() {
        return array(
            /* The slug is unchanged on purpose. Renaming it would orphan every
               user already assigned to it, and the display name is the part that
               was misleading: "(admin)" suggested administration powers this
               role has never had — it sees one menu item and no settings. */
            self::AGENT_ROLE => array(
                'name' => __('Help Desk Agent', 'js-support-ticket'),
                'caps' => array(
                    'read'            => true,
                    self::CAP_TICKETS => true,
                    self::CAP_REPLY   => true,
                    self::CAP_NOTE    => true,
                    self::CAP_STATE   => true,
                    self::CAP_EDIT    => true,
                    self::CAP_KB      => true,
                    self::CAP_MERGE   => true,
                ),
            ),
            /* Reads the queue and writes internal notes, never replies to a
               customer. The seat you give the developer who has to look at the
               bug but must not answer the person who reported it.
               Deliberately gets neither CAP_EDIT nor CAP_KB: somebody who may
               not answer the customer has no business rewriting the customer's
               ticket or publishing to the knowledge base. */
            self::LIGHT_AGENT_ROLE => array(
                'name' => __('Help Desk Light Agent', 'js-support-ticket'),
                'caps' => array(
                    'read'            => true,
                    self::CAP_TICKETS => true,
                    self::CAP_NOTE    => true,
                ),
            ),
        );
    }

    /**
     * Capabilities owned by this plugin. Only these are ever added or removed
     * from a role, so a role keeps any capability another plugin granted it.
     */
    public static function getPluginCapabilities() {
        return array(
            self::CAP_ADMIN,
            self::CAP_TICKETS,
            self::CAP_REPLY,
            self::CAP_NOTE,
            self::CAP_STATE,
            self::CAP_EDIT,
            self::CAP_KB,
            self::CAP_DELETE,
            self::CAP_MERGE,
        );
    }

    /**
     * May this user answer the customer on a ticket?
     *
     * Help-desk administrators always may. Otherwise it takes the reply
     * capability — which the Agent role has and the Light Agent role does not.
     */
    public static function canReplyPublicly() {
        return self::canManageHelpDesk() || current_user_can(self::CAP_REPLY);
    }

    /**
     * May this user move a ticket through its life — change its status, close
     * or reopen it, set its priority, transfer it, assign it?
     *
     * An agent who can read and answer a ticket but cannot close it is not
     * really working the queue, so the Agent role holds this and the Light Agent
     * role does not. Blocking a sender is deliberately not part of it: that is
     * moderation with consequences beyond the ticket, and stays with the
     * administrators. (Roadmap 4.0-SEC-04)
     */
    public static function canChangeTicketState() {
        return self::canManageHelpDesk() || current_user_can(self::CAP_STATE);
    }

    /**
     * May this user write an internal note? Both agent tiers may.
     */
    public static function canWriteInternalNote() {
        return self::canManageHelpDesk() || current_user_can(self::CAP_NOTE);
    }

    /**
     * Is this user's access governed by the Agents add-on rather than by a
     * WordPress role?
     *
     * True only when the add-on is active *and* the user is on its staff list.
     * A site running the add-on can still have users who are not agents in it,
     * and those users fall back to their role like anywhere else.
     */
    public static function agentAddonGoverns() {
        /* Resolved once per user. isUserStaff() is two uncached queries every
           time it is asked, and these checks run inside the reply loop on the
           ticket detail screen — a long thread would otherwise cost a pair of
           queries per reply. Keyed by user id rather than cached outright
           because the current user can change inside one request: cron, the
           REST API and email piping all call wp_set_current_user(), and a
           permission answer left over from the previous user is a bug. */
        $jsst_uid = (int) get_current_user_id();
        if (isset(self::$jsst_addon_governs[$jsst_uid])) {
            return self::$jsst_addon_governs[$jsst_uid];
        }
        self::$jsst_addon_governs[$jsst_uid] = false;
        if (!in_array('agent', jssupportticket::$_active_addons)) {
            return self::$jsst_addon_governs[$jsst_uid];
        }
        $jsst_agent = JSSTincluder::getJSModel('agent');
        self::$jsst_addon_governs[$jsst_uid] = ($jsst_agent && method_exists($jsst_agent, 'isUserStaff') && $jsst_agent->isUserStaff());
        return self::$jsst_addon_governs[$jsst_uid];
    }

    /**
     * Answer a permission that both systems can speak to, with the add-on
     * winning wherever it applies.
     *
     * The two permission systems overlap: a user can hold the Help Desk Agent
     * role *and* have a row on the Agents screen, and the two can disagree. The
     * add-on is the finer-grained of the two and the one an administrator
     * configures per agent, so when it governs a user it answers alone — a
     * capability coming from the WordPress role must not quietly re-grant
     * something that was switched off agent by agent.
     *
     * Off the add-on, or for a user it does not govern, the role capability is
     * the whole answer. Help-desk administrators short-circuit both.
     *
     * @param string $jsst_task     The add-on's task name.
     * @param string $jsst_cap      The capability the role uses for the same thing.
     */
    private static function resolveAgainstAddon($jsst_task, $jsst_cap) {
        if (self::canManageHelpDesk()) {
            return true;
        }
        if (!self::agentAddonGoverns()) {
            return current_user_can($jsst_cap);
        }
        // Same reason as the cache above: called per reply on a long thread.
        $jsst_key = ((int) get_current_user_id()) . '|' . $jsst_task;
        if (!isset(self::$jsst_task_answers[$jsst_key])) {
            self::$jsst_task_answers[$jsst_key] =
                (JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_task) == true);
        }
        return self::$jsst_task_answers[$jsst_key];
    }

    /**
     * May this user merge one ticket into another?
     *
     * Routed through the same resolver as every other agent permission, so the
     * Agents add-on's per-agent "Ticket Merge" tick decides it on a site that
     * runs the add-on, and the capability decides it on a site that does not.
     * The merge add-on used to ask the add-on's ACL directly, which meant this
     * capability was never consulted at all. (Roadmap 4.0-CORE-04)
     */
    public static function canMergeTickets() {
        return self::resolveAgainstAddon('Ticket Merge', self::CAP_MERGE);
    }

    /**
     * May this user amend a ticket's own content, or a reply already sent?
     */
    public static function canEditTicketContent() {
        return self::resolveAgainstAddon('Edit Ticket', self::CAP_EDIT);
    }

    /**
     * May this user amend a reply that has already gone out?
     *
     * Separate from canEditTicketContent() only because the add-on asks a
     * separate question about it; both ride CAP_EDIT on the role side.
     */
    public static function canEditReply() {
        return self::resolveAgainstAddon('Edit Reply', self::CAP_EDIT);
    }

    /**
     * May this user write or remove a knowledge base article or FAQ entry?
     */
    public static function canAuthorKnowledge($jsst_task = 'Edit Knowledge Base') {
        return self::resolveAgainstAddon($jsst_task, self::CAP_KB);
    }

    /**
     * WordPress roles that must never receive a help-desk capability implicitly.
     * Contributor is listed because releases up to 3.1.7 granted it CAP_TICKETS
     * on activation.
     */
    public static function getRolesToRevoke() {
        return array('contributor');
    }

    /**
     * Run the reconciliation if it has not already run for ROLE_VERSION.
     *
     * @param bool $jsst_force Re-run even when the stored version is current.
     * @return bool True when a pass ran, false when it was already up to date.
     */
    public static function reconcile($jsst_force = false) {
        $jsst_installed = (int) get_option(self::OPT_VERSION, 0);
        if (!$jsst_force && $jsst_installed >= self::ROLE_VERSION) {
            return false;
        }

        self::reconcileAdministrator();
        /* Before the canonical roles are applied, so that an upgrading site
           keeps exactly the access it already had. */
        if ($jsst_installed > 0 && $jsst_installed < 4) {
            self::grantWorkerCapsToExistingTicketRoles();
        }
        self::reconcileCustomRoles();
        self::revokeImplicitGrants();

        update_option(self::OPT_VERSION, self::ROLE_VERSION, false);

        return true;
    }

    /**
     * The administrator role keeps both capabilities. add_cap() is idempotent.
     */
    private static function reconcileAdministrator() {
        $jsst_role = get_role('administrator');
        if (!$jsst_role) {
            return;
        }
        foreach (self::getPluginCapabilities() as $jsst_cap) {
            if (!$jsst_role->has_cap($jsst_cap)) {
                $jsst_role->add_cap($jsst_cap);
            }
        }
    }

    /**
     * Change a role's display name without touching anything else.
     *
     * add_role() ignores a role that already exists, so an upgraded site would
     * keep the old label for ever — and the old label, "JS Help Desk agent
     * (admin)", claimed administration powers the role has never had. Rewriting
     * the stored name is the only way to correct it: remove_role()/add_role()
     * would unassign every user who holds it. The slug and the capabilities are
     * untouched, so nobody's access changes. (Roadmap 4.0-SEC-04)
     */
    private static function renameRole($jsst_slug, $jsst_name) {
        $jsst_roles = function_exists('wp_roles') ? wp_roles() : null;
        if (!$jsst_roles || !isset($jsst_roles->roles[$jsst_slug])) {
            return;
        }
        if ($jsst_roles->roles[$jsst_slug]['name'] === $jsst_name) {
            return;
        }
        $jsst_roles->roles[$jsst_slug]['name'] = $jsst_name;
        $jsst_roles->role_names[$jsst_slug] = $jsst_name;
        update_option($jsst_roles->role_key, $jsst_roles->roles);
    }

    /**
     * Keep an upgrading site working exactly as it did. (Roadmap 4.0-SEC-04)
     *
     * Until this release one capability, CAP_TICKETS, covered reading the queue,
     * answering customers and writing notes all at once. Splitting the last two
     * into capabilities of their own would quietly take them away from everyone
     * already holding it — including roles a site administrator granted it to by
     * hand, which this plugin does not manage and must not silently downgrade.
     *
     * So every role that already has CAP_TICKETS is given the two new
     * capabilities once, on the upgrade to ROLE_VERSION 4. Nobody's access
     * changes. Only the Light Agent role, which is new and has no existing
     * users, ships without the reply capability.
     *
     * Nothing like this exists for CAP_EDIT or CAP_KB, and nothing should. This
     * function preserves access that a split would otherwise have taken away;
     * those two are genuinely new powers that no role ever held, so handing
     * them to every existing CAP_TICKETS holder would be an escalation rather
     * than a preservation. Role holders get them from getCanonicalRoles(),
     * which is the whole of the upgrade path.
     */
    private static function grantWorkerCapsToExistingTicketRoles() {
        $jsst_roles = function_exists('wp_roles') ? wp_roles() : null;
        if (!$jsst_roles || empty($jsst_roles->roles)) {
            return;
        }
        foreach (array_keys($jsst_roles->roles) as $jsst_slug) {
            if ($jsst_slug === self::LIGHT_AGENT_ROLE) {
                continue;
            }
            $jsst_role = get_role($jsst_slug);
            if (!$jsst_role || !$jsst_role->has_cap(self::CAP_TICKETS)) {
                continue;
            }
            foreach (array(self::CAP_REPLY, self::CAP_NOTE) as $jsst_cap) {
                if (!$jsst_role->has_cap($jsst_cap)) {
                    $jsst_role->add_cap($jsst_cap);
                }
            }
        }
    }

    /**
     * Create the custom roles, and bring already-existing ones back in line.
     *
     * add_role() returns null and changes nothing when the role exists, which is
     * why upgraded sites never received capability corrections. Reconciling the
     * capabilities explicitly fixes that without destroying user assignments,
     * which remove_role()/add_role() would do.
     */
    private static function reconcileCustomRoles() {
        foreach (self::getCanonicalRoles() as $jsst_slug => $jsst_definition) {
            $jsst_role = get_role($jsst_slug);
            if (!$jsst_role) {
                add_role($jsst_slug, $jsst_definition['name'], $jsst_definition['caps']);
                continue;
            }
            self::renameRole($jsst_slug, $jsst_definition['name']);
            foreach ($jsst_definition['caps'] as $jsst_cap => $jsst_grant) {
                if ($jsst_grant && !$jsst_role->has_cap($jsst_cap)) {
                    $jsst_role->add_cap($jsst_cap);
                }
            }
            // Remove plugin capabilities that are no longer part of the role.
            foreach (self::getPluginCapabilities() as $jsst_cap) {
                if (!isset($jsst_definition['caps'][$jsst_cap]) && $jsst_role->has_cap($jsst_cap)) {
                    $jsst_role->remove_cap($jsst_cap);
                }
            }
        }
    }

    /**
     * Take CAP_TICKETS off the WordPress roles that used to receive it wholesale.
     *
     * Nobody who should have help-desk access loses it: grantAgentCapability()
     * gives it to whoever is on the agent list, on every request.
     */
    private static function revokeImplicitGrants() {
        foreach (self::getRolesToRevoke() as $jsst_slug) {
            $jsst_role = get_role($jsst_slug);
            if ($jsst_role && $jsst_role->has_cap(self::CAP_TICKETS)) {
                $jsst_role->remove_cap(self::CAP_TICKETS);
            }
        }
        self::removeStoredPerUserGrants();
    }

    /**
     * Strip any copy of the capability stored directly on a user.
     *
     * Access is derived from the agent list now, so a stored copy is at best
     * redundant and at worst a way for someone removed from the Agents screen to
     * keep reading tickets. Users who are still agents lose nothing: the runtime
     * filter grants them the capability again on the very next request.
     */
    private static function removeStoredPerUserGrants() {
        $jsst_db = jssupportticket::$_db;
        $jsst_metakey = $jsst_db->get_blog_prefix() . 'capabilities';

        // Ask only for the users who actually carry the capability, rather than
        // walking every user: an installed base includes sites with very large
        // user tables.
        $jsst_uids = $jsst_db->get_col(
            $jsst_db->prepare(
                "SELECT user_id FROM {$jsst_db->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",
                $jsst_metakey,
                '%' . $jsst_db->esc_like(self::CAP_TICKETS) . '%'
            )
        );
        if (empty($jsst_uids)) {
            return;
        }
        foreach ($jsst_uids as $jsst_uid) {
            $jsst_user = get_userdata((int) $jsst_uid);
            // Only a personal grant is removed. A capability the user gets from
            // their role lives in the role, not here, and is left alone.
            if ($jsst_user && isset($jsst_user->caps[self::CAP_TICKETS])) {
                $jsst_user->remove_cap(self::CAP_TICKETS);
            }
        }
    }

    /**
     * Grant the help-desk capability to whoever is currently an agent.
     * (Roadmap 3.2-CORE-01)
     *
     * Before 3.2 an agent who happened to hold the Contributor role got this
     * capability from the role itself - which is precisely the escalation this
     * release removes. Taking the role grant away without replacing it would
     * break every agent added after the upgrade, because the Agents screen has
     * never granted WordPress capabilities.
     *
     * Deriving it here keeps one source of truth: the agent list. Add someone on
     * the Agents screen and they can work tickets on their next request; remove
     * them and the access is gone just as fast.
     */
    public static function grantAgentCapability($jsst_allcaps, $jsst_caps, $jsst_args, $jsst_user) {
        /* All three working capabilities, not just CAP_TICKETS. Somebody on the
           Agents screen has always been able to answer customers and write
           notes; splitting those out into their own capabilities must not take
           that away from the add-on's agents, who are granted their access from
           this list rather than from a WordPress role. The add-on's own
           per-agent permissions still decide what they may do beyond this.
           (Roadmap 4.0-SEC-04)

           CAP_STATE, CAP_EDIT and CAP_KB are deliberately absent. The add-on
           asks its own question about each of them, agent by agent, and
           resolveAgainstAddon() lets it answer alone for anybody on this list.
           Granting the capability here would hand it back to every agent and
           make those per-agent settings unenforceable. */
        $jsst_worker_caps = array(self::CAP_TICKETS, self::CAP_REPLY, self::CAP_NOTE);
        if (!is_array($jsst_caps)) {
            return $jsst_allcaps;
        }
        $jsst_wanted = array_intersect($jsst_worker_caps, $jsst_caps);
        if (empty($jsst_wanted)) {
            return $jsst_allcaps;
        }
        // Nothing to do when a role has already granted everything being tested.
        $jsst_missing = array();
        foreach ($jsst_wanted as $jsst_cap) {
            if (empty($jsst_allcaps[$jsst_cap])) {
                $jsst_missing[] = $jsst_cap;
            }
        }
        if (empty($jsst_missing) || empty($jsst_user->ID)) {
            return $jsst_allcaps;
        }
        if (in_array((int) $jsst_user->ID, self::getExplicitAgentUserIds(), true)) {
            foreach ($jsst_missing as $jsst_cap) {
                $jsst_allcaps[$jsst_cap] = true;
            }
        }
        return $jsst_allcaps;
    }

    /**
     * May this user administer the help desk — act on any ticket rather than
     * only their own? (Roadmap 4.0-SEC-04)
     *
     * The two help-desk capabilities are not interchangeable. CAP_ADMIN is the
     * one every administration screen is gated on; CAP_TICKETS only says "this
     * person answers tickets" and is what the agent role and the Agents add-on
     * hand out. Code that needs to know whether somebody may act on a ticket
     * that is not theirs must ask this, and never `is_admin()` — that reports
     * which screen the request is on, not what the person is allowed to do.
     */
    public static function canManageHelpDesk() {
        return current_user_can('manage_options') || current_user_can(self::CAP_ADMIN);
    }

    /**
     * WordPress user IDs that a site administrator explicitly added as agents.
     *
     * The agent list lives in the Agents add-on table. When the add-on has never
     * been installed there is no explicit agent list, so nobody is granted
     * anything.
     *
     * `js_ticket_staff.uid` is a `js_ticket_users.id` - the plugin's own user
     * table - and NOT a `wp_users.ID`; the add-on's own permission check joins
     * it that way, as does JSSTagentaccess::agents(). Reading it as a WordPress
     * id grants the working capabilities to whichever account happens to sit at
     * that number, and leaves the real agent with nothing. The two id spaces
     * only line up on a site where every user arrived through the help desk,
     * which is exactly what makes assuming it dangerous. Map through the user
     * table instead. (Roadmap 4.0-SEC-04)
     *
     * @return int[]
     */
    public static function getExplicitAgentUserIds() {
        // Resolved once per request. grantAgentCapability() runs on every
        // capability check, so this must not be a query each time.
        if (self::$jsst_agent_ids !== null) {
            return self::$jsst_agent_ids;
        }
        self::$jsst_agent_ids = array();

        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_staff = $jsst_prefix . 'js_ticket_staff';
        $jsst_users = $jsst_prefix . 'js_ticket_users';
        foreach (array($jsst_staff, $jsst_users) as $jsst_table) {
            $jsst_exists = jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)
            );
            if (!$jsst_exists) {
                // Without both tables there is no way to turn the agent list
                // into WordPress accounts. Grant nobody rather than fall back
                // to the raw uid: this list hands out capabilities, so guessing
                // wrong here is an escalation, not a missing row on a screen.
                return self::$jsst_agent_ids;
            }
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names are built from the wpdb prefix.
        $jsst_wpuids = jssupportticket::$_db->get_col(
            "SELECT pluginuser.wpuid FROM `{$jsst_staff}` AS staff"
            . " INNER JOIN `{$jsst_users}` AS pluginuser ON pluginuser.id = staff.uid"
            . " WHERE staff.uid > 0 AND pluginuser.wpuid > 0 AND staff.status = 1"
        );
        if (empty($jsst_wpuids)) {
            return self::$jsst_agent_ids;
        }
        self::$jsst_agent_ids = array_values(array_unique(array_map('intval', $jsst_wpuids)));
        return self::$jsst_agent_ids;
    }

    /** Forget the cached agent list, and everything derived from it. */
    public static function flushAgentCache() {
        self::$jsst_agent_ids = null;
        self::$jsst_addon_governs = array();
        self::$jsst_task_answers = array();
    }

    /**
     * Remove every plugin capability from every role, and the custom roles
     * themselves. Used by uninstall. (Roadmap 3.2-CORE-02)
     */
    public static function removeAll() {
        global $wp_roles;
        if (!isset($wp_roles) || !is_object($wp_roles)) {
            return;
        }
        foreach (array_keys($wp_roles->roles) as $jsst_slug) {
            $jsst_role = get_role($jsst_slug);
            if (!$jsst_role) {
                continue;
            }
            foreach (self::getPluginCapabilities() as $jsst_cap) {
                if ($jsst_role->has_cap($jsst_cap)) {
                    $jsst_role->remove_cap($jsst_cap);
                }
            }
        }
        foreach (array_keys(self::getCanonicalRoles()) as $jsst_slug) {
            if (get_role($jsst_slug)) {
                remove_role($jsst_slug);
            }
        }
        delete_option(self::OPT_VERSION);
    }

}

?>
