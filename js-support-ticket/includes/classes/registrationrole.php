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
if (class_exists('JSSTregistrationrole')) {
    return;
}


/**
 * Which WordPress role a self-registered customer may be given.
 * (Roadmap 4.0-CORE-07, 4.0-SEC-01)
 *
 * The setting behind this used to sit behind the User Options add-on. Making it
 * free means every administrator can now reach it, so it has to be safe by
 * construction rather than safe by being hidden: the portal registration form is
 * open to the public, so whatever role this names is handed to anyone who signs
 * up.
 *
 * A role is offered only when it holds none of the capabilities below. That rules
 * out Administrator and Editor outright, and it also rules out any custom role a
 * site has built on top of them — checking capabilities rather than role names is
 * what makes this hold on sites with roles this code has never heard of.
 *
 * Enforced in three places, because any one of them alone is not enough:
 *   - the settings screen only offers safe roles;
 *   - the save path re-checks, because a select can be edited in the browser;
 *   - registration re-checks, because the row may predate this rule or have been
 *     written by an older version, an import or a direct database edit.
 */
class JSSTregistrationrole {

    /** The role used whenever the configured one is missing or unsafe. */
    const FALLBACK = 'subscriber';

    /**
     * Capabilities that make a role unfit for public self-registration.
     *
     * Administering the site, managing users, publishing or editing other
     * people's content, posting unfiltered HTML, or holding the plugin's own
     * agent capability.
     */
    public static function denyCapabilities() {
        return apply_filters('jsst_registration_role_deny_caps', array(
            'manage_options',
            'edit_users',
            'create_users',
            'delete_users',
            'promote_users',
            'list_users',
            'remove_users',
            'edit_theme_options',
            'switch_themes',
            'install_plugins',
            'activate_plugins',
            'edit_plugins',
            'install_themes',
            'edit_themes',
            'edit_files',
            'unfiltered_upload',
            'unfiltered_html',
            'edit_others_posts',
            'edit_others_pages',
            'publish_posts',
            'publish_pages',
            'delete_others_posts',
            'moderate_comments',
            'manage_categories',
            'import',
            'export',
            'update_core',
            'update_plugins',
            'edit_dashboard',
            // The plugin's own agent capability: a customer must never register
            // straight into access to everyone else's tickets. (Roadmap 3.2-CORE-01)
            'jsst_support_ticket',
        ));
    }

    /**
     * Is this role safe to hand to anyone who registers?
     */
    public static function isSafe($jsst_role) {
        $jsst_role = (string) $jsst_role;
        if ($jsst_role === '') {
            return false;
        }
        // Multisite super admins are not a role, but the string would pass a
        // name check, so reject anything that is not a real registered role.
        $jsst_roleobject = get_role($jsst_role);
        if (empty($jsst_roleobject)) {
            return false;
        }
        $jsst_caps = is_array($jsst_roleobject->capabilities) ? $jsst_roleobject->capabilities : array();
        foreach (self::denyCapabilities() as $jsst_cap) {
            if (!empty($jsst_caps[$jsst_cap])) {
                return false;
            }
        }
        return true;
    }

    /**
     * The roles the settings screen may offer, as id/text objects.
     *
     * Never empty: if a site has stripped or renamed every low-privilege role,
     * the fallback is still offered so the setting has a valid value.
     */
    public static function options() {
        global $wp_roles;
        $jsst_options = array();
        if (isset($wp_roles) && is_object($wp_roles)) {
            foreach ($wp_roles->get_names() as $jsst_key => $jsst_label) {
                if (self::isSafe($jsst_key)) {
                    $jsst_options[] = (object) array('id' => $jsst_key, 'text' => $jsst_label);
                }
            }
        }
        if (empty($jsst_options)) {
            $jsst_options[] = (object) array('id' => self::FALLBACK, 'text' => esc_html(__('Subscriber', 'js-support-ticket')));
        }
        return $jsst_options;
    }

    /**
     * The roles this site offers but which are refused, so the settings screen
     * can say why they are missing instead of leaving an administrator hunting.
     */
    public static function refusedNames() {
        global $wp_roles;
        $jsst_names = array();
        if (isset($wp_roles) && is_object($wp_roles)) {
            foreach ($wp_roles->get_names() as $jsst_key => $jsst_label) {
                if (!self::isSafe($jsst_key)) {
                    $jsst_names[] = $jsst_label;
                }
            }
        }
        return $jsst_names;
    }

    /**
     * The role to actually use: the configured one when it is safe, the fallback
     * otherwise.
     */
    public static function sanitize($jsst_role) {
        // '0' is what the pre-4.0 default looked like when nothing was chosen.
        if ($jsst_role === 0 || $jsst_role === '0' || $jsst_role === '' || $jsst_role === null) {
            return self::FALLBACK;
        }
        if (self::isSafe($jsst_role)) {
            return (string) $jsst_role;
        }
        return self::FALLBACK;
    }

    /**
     * The configured registration role, already made safe.
     */
    public static function configured() {
        $jsst_role = isset(jssupportticket::$_config['wp_default_role']) ? jssupportticket::$_config['wp_default_role'] : '';
        return self::sanitize($jsst_role);
    }

}
