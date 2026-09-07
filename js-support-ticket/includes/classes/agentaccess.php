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
if (class_exists('JSSTagentaccess')) {
    return;
}

/**
 * Agent Access. (Roadmap 4.0-SEC-04)
 *
 * Who can see which tickets, and do what to them, is currently the answer to
 * four separate questions on four separate screens: the WordPress role, the
 * help-desk role, the departments an agent is scoped to, and the individual
 * permission overrides on top. Nobody can hold all four in their head, which
 * means nobody actually audits them.
 *
 * This puts the derived answer in one place — the effective permission set, not
 * the ingredients — and, just as importantly, flags the two states that should
 * never exist:
 *
 *   - somebody holding help-desk capability in WordPress who is not on the
 *     Agents list. That is precisely the privilege escalation corrected in
 *     v3.2, and this is what makes the correction verifiable rather than
 *     something an administrator has to take on trust.
 *   - an agent whose WordPress user has been deleted, leaving a row that grants
 *     scope to nobody and quietly confuses every permission check.
 */
class JSSTagentaccess {

    /**
     * Does a table exist? The ACL tables belong to the Agents add-on, so every
     * read here has to survive them being absent.
     */
    private static function tableExists($jsst_table) {
        $jsst_full = jssupportticket::$_db->prefix . $jsst_table;
        return (bool) jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_full)
        );
    }

    /** Is the Agents add-on managing agents on this site? */
    public static function agentsActive() {
        return in_array('agent', jssupportticket::$_active_addons) && self::tableExists('js_ticket_staff');
    }

    /**
     * Every WordPress user holding either help-desk capability.
     *
     * Read from WordPress rather than from the plugin's tables, because the
     * whole point is to catch access that the plugin's tables do not know about.
     */
    public static function capabilityHolders() {
        $jsst_caps = array('jsst_support_ticket', 'jsst_support_ticket_tickets');
        $jsst_holders = array();
        foreach (get_users(array('fields' => array('ID', 'user_login', 'user_email', 'display_name'))) as $jsst_user) {
            $jsst_wpuser = new WP_User($jsst_user->ID);
            $jsst_held = array();
            foreach ($jsst_caps as $jsst_cap) {
                if ($jsst_wpuser->has_cap($jsst_cap)) {
                    $jsst_held[] = $jsst_cap;
                }
            }
            if (empty($jsst_held)) {
                continue;
            }
            $jsst_holders[(int) $jsst_user->ID] = array(
                'wpuid'        => (int) $jsst_user->ID,
                'display_name' => $jsst_user->display_name,
                'user_email'   => $jsst_user->user_email,
                'wp_roles'     => $jsst_wpuser->roles,
                'caps'         => $jsst_held,
                'is_admin'     => $jsst_wpuser->has_cap('manage_options'),
            );
        }
        return $jsst_holders;
    }

    /**
     * The agents on the Agents list, with their help-desk role.
     */
    public static function agents() {
        if (!self::agentsActive()) {
            return array();
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_hasroles = self::tableExists('js_ticket_acl_roles');
        $jsst_hasusers = self::tableExists('js_ticket_users');

        // staff.uid is the plugin's own user id, not a WordPress one - the
        // add-on's permission check joins it against js_ticket_users. Resolving
        // it here is what lets an agent be matched to the WordPress account that
        // actually holds the capability. The two are equal on a site where every
        // user arrived through the help desk, which is exactly what makes
        // assuming it dangerous.
        $jsst_sql = "SELECT staff.id, staff.uid, staff.firstname, staff.lastname, staff.email, staff.status, staff.roleid, staff.departmentid";
        $jsst_sql .= $jsst_hasroles ? ", role.name AS rolename " : ", '' AS rolename ";
        $jsst_sql .= $jsst_hasusers ? ", pluginuser.wpuid AS wpuid " : ", staff.uid AS wpuid ";
        $jsst_sql .= " FROM `" . $jsst_prefix . "js_ticket_staff` AS staff ";
        if ($jsst_hasroles) {
            $jsst_sql .= " LEFT JOIN `" . $jsst_prefix . "js_ticket_acl_roles` AS role ON role.id = staff.roleid ";
        }
        if ($jsst_hasusers) {
            $jsst_sql .= " LEFT JOIN `" . $jsst_prefix . "js_ticket_users` AS pluginuser ON pluginuser.id = staff.uid ";
        }
        $jsst_sql .= " ORDER BY staff.status DESC, staff.firstname ASC";
        $jsst_rows = jssupportticket::$_db->get_results($jsst_sql);
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * The departments one agent may see.
     *
     * Per-agent scope wins where it exists; otherwise the role's scope applies.
     * An empty result means every department, which is how the add-on has always
     * behaved and is worth saying out loud on the screen rather than leaving as
     * a blank cell.
     */
    public static function departmentsFor($jsst_staffid, $jsst_roleid) {
        $jsst_names = array();
        $jsst_ids = array();
        if (self::tableExists('js_ticket_acl_user_access_departments')) {
            $jsst_ids = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
                "SELECT departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_user_access_departments` WHERE staffid = %d",
                (int) $jsst_staffid
            ));
        }
        $jsst_source = 'agent';
        if (empty($jsst_ids) && $jsst_roleid && self::tableExists('js_ticket_acl_role_access_departments')) {
            $jsst_ids = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
                "SELECT departmentid FROM `" . jssupportticket::$_db->prefix . "js_ticket_acl_role_access_departments` WHERE roleid = %d",
                (int) $jsst_roleid
            ));
            $jsst_source = 'role';
        }
        $jsst_ids = array_filter(array_map('intval', (array) $jsst_ids));
        if (empty($jsst_ids)) {
            return array('names' => array(), 'source' => 'all');
        }
        $jsst_placeholders = implode(',', array_fill(0, count($jsst_ids), '%d'));
        $jsst_names = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
            "SELECT departmentname FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` WHERE id IN (" . $jsst_placeholders . ") ORDER BY departmentname ASC",
            array_values($jsst_ids)
        ));
        return array('names' => is_array($jsst_names) ? $jsst_names : array(), 'source' => $jsst_source);
    }

    /**
     * What one agent can actually do.
     *
     * The union of the role's permissions and the agent's own — which is how the
     * permission check itself resolves them, so this reports the same answer the
     * software will give rather than a second opinion about it.
     *
     * The rows carry isgranted and status columns that are deliberately not
     * filtered on, because the add-on's own checkPermissionGrantedForTask() does
     * not filter on them either - it treats the presence of the row as the
     * grant. Filtering here would report less access than the software actually
     * allows, and under-reporting access on an audit screen is the failure that
     * matters. Each permission
     * is tagged with where it came from, because "why can this person close
     * tickets?" is the question an audit is actually asking.
     */
    public static function permissionsFor($jsst_staffid, $jsst_roleid) {
        $jsst_effective = array();
        if (!self::tableExists('js_ticket_acl_permissions')) {
            return $jsst_effective;
        }
        $jsst_prefix = jssupportticket::$_db->prefix;

        if ($jsst_roleid && self::tableExists('js_ticket_acl_role_permissions')) {
            $jsst_rows = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
                "SELECT per.permission FROM `" . $jsst_prefix . "js_ticket_acl_permissions` AS per
                    JOIN `" . $jsst_prefix . "js_ticket_acl_role_permissions` AS rp ON rp.permissionid = per.id
                    WHERE rp.roleid = %d",
                (int) $jsst_roleid
            ));
            foreach ((array) $jsst_rows as $jsst_permission) {
                $jsst_effective[$jsst_permission] = 'role';
            }
        }
        if (self::tableExists('js_ticket_acl_user_permissions')) {
            $jsst_rows = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
                "SELECT per.permission FROM `" . $jsst_prefix . "js_ticket_acl_permissions` AS per
                    JOIN `" . $jsst_prefix . "js_ticket_acl_user_permissions` AS up ON up.permissionid = per.id
                    WHERE up.staffid = %d",
                (int) $jsst_staffid
            ));
            foreach ((array) $jsst_rows as $jsst_permission) {
                // Granted directly as well as by the role: the direct grant is
                // the more specific fact, so it is what gets reported.
                $jsst_effective[$jsst_permission] = 'agent';
            }
        }
        ksort($jsst_effective);
        return $jsst_effective;
    }

    /**
     * The whole picture, one row per person who has any access at all.
     */
    public static function report() {
        $jsst_holders = self::capabilityHolders();
        $jsst_rows = array();
        $jsst_seen_wpuids = array();

        foreach (self::agents() as $jsst_agent) {
            $jsst_wpuid = isset($jsst_agent->wpuid) ? (int) $jsst_agent->wpuid : 0;
            $jsst_holder = isset($jsst_holders[$jsst_wpuid]) ? $jsst_holders[$jsst_wpuid] : null;
            $jsst_seen_wpuids[$jsst_wpuid] = true;
            $jsst_depts = self::departmentsFor($jsst_agent->id, $jsst_agent->roleid);

            $jsst_warnings = array();
            if ($jsst_wpuid <= 0 || !$jsst_holder) {
                $jsst_user = ($jsst_wpuid > 0) ? get_userdata($jsst_wpuid) : false;
                if (!$jsst_user) {
                    $jsst_warnings[] = esc_html(__('This agent has no WordPress user. The row grants access to nobody and should be removed.', 'js-support-ticket'));
                } else {
                    $jsst_warnings[] = esc_html(__('This agent has a WordPress user but no help-desk capability, so they cannot reach the admin screens.', 'js-support-ticket'));
                }
            }

            $jsst_rows[] = array(
                'name'        => trim($jsst_agent->firstname . ' ' . $jsst_agent->lastname),
                'email'       => $jsst_agent->email,
                'is_agent'    => true,
                'active'      => ((int) $jsst_agent->status === 1),
                'workspace'   => ($jsst_holder && in_array('jsst_support_ticket', $jsst_holder['caps'], true))
                        ? esc_html(__('Backend — works tickets in wp-admin', 'js-support-ticket'))
                        : esc_html(__('Front end — works tickets on the portal only', 'js-support-ticket')),
                'wp_roles'    => $jsst_holder ? $jsst_holder['wp_roles'] : array(),
                'hd_role'     => $jsst_agent->rolename ? $jsst_agent->rolename : esc_html(__('No help-desk role', 'js-support-ticket')),
                'departments' => $jsst_depts,
                'permissions' => self::permissionsFor($jsst_agent->id, $jsst_agent->roleid),
                'is_admin'    => $jsst_holder ? $jsst_holder['is_admin'] : false,
                'warnings'    => $jsst_warnings,
            );
        }

        // Anybody holding help-desk capability who is not on the Agents list.
        // This is the check the v3.2 correction exists for.
        foreach ($jsst_holders as $jsst_wpuid => $jsst_holder) {
            if (isset($jsst_seen_wpuids[$jsst_wpuid])) {
                continue;
            }
            $jsst_warnings = array();
            if (!$jsst_holder['is_admin']) {
                $jsst_warnings[] = esc_html(__('This user can reach the help desk but is not on the Agents list. Unless that is deliberate, remove the capability from their WordPress role.', 'js-support-ticket'));
            }
            $jsst_rows[] = array(
                'name'        => $jsst_holder['display_name'],
                'email'       => $jsst_holder['user_email'],
                'is_agent'    => false,
                'active'      => true,
                'workspace'   => in_array('jsst_support_ticket', $jsst_holder['caps'], true)
                        ? esc_html(__('Backend — works tickets in wp-admin', 'js-support-ticket'))
                        : esc_html(__('Front end — works tickets on the portal only', 'js-support-ticket')),
                'wp_roles'    => $jsst_holder['wp_roles'],
                'hd_role'     => $jsst_holder['is_admin']
                        ? esc_html(__('Administrator — every permission', 'js-support-ticket'))
                        : esc_html(__('Not an agent', 'js-support-ticket')),
                'departments' => array('names' => array(), 'source' => 'all'),
                'permissions' => array(),
                'is_admin'    => $jsst_holder['is_admin'],
                'warnings'    => $jsst_warnings,
            );
        }

        return $jsst_rows;
    }

}
