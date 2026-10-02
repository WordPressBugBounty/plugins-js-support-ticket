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
if (class_exists('JSSTmodule')) {
    return;
}

/**
 * One module bootstrap, instead of twenty-seven. (Roadmap 4.5-ARCH-05)
 *
 * Every add-on directory carries its own copy of the same three files: a plugin
 * bootstrap that registers the activation, deactivation and multisite hooks, an
 * activation class that installs the schema, and an update check that compares a
 * version option against a class constant. Nine and a half thousand lines of it
 * across twenty-seven directories, and almost none of it is about the module it
 * belongs to.
 *
 * The roadmap's reason for consolidating is that every duplicated bootstrap is a
 * place upgrades fail. That is not a prediction. Here is what the copies
 * actually contain today:
 *
 *     public function jstsupportitkcetaddon_activate_hook() {
 *         ...
 *         if (function_exists('is_multisite') && is_multisite() && $jsst_network_wide) {
 *             // create the tables on every blog in the network
 *         } else {
 *             // create them on this one
 *         }
 *     }
 *
 * WordPress passes $network_wide to a registered activation callback as its
 * first argument. The method declares no parameters, so $jsst_network_wide is an
 * undefined variable, which is null, which is false. The multisite branch has
 * never run. Twenty-four of the twenty-seven add-ons have it, and the one that
 * declares the parameter correctly - WooCommerce - is the exception that shows
 * the rest were copied from a version that did too, before somebody removed it.
 *
 * What that means on a real site: a network-activated add-on creates its tables
 * on whichever blog happened to be current when the activation ran, and on no
 * other. Every other site in the network runs the add-on against tables that do
 * not exist. It is invisible on a single-site install, which is where it was
 * tested, and it has been copied twenty-four times.
 *
 * So this class is that bootstrap written once, correctly, and taking the
 * argument. A module adopting it replaces about a hundred and fifty lines with
 * one call:
 *
 *     JSSTmodule::adopt(array(
 *         'slug'    => 'faq',
 *         'file'    => __FILE__,
 *         'version' => '1.2.2',
 *         'tables'  => array('js_ticket_faqs'),
 *         'schema'  => __DIR__ . '/includes/install.php',
 *     ));
 *
 * The legacy add-ons are deliberately not converted. Two reasons, and both
 * matter more than the tidiness would. Their deactivation behaviour is load
 * bearing: JSSTlegacy's migration is built around the fact that deactivating one
 * zeroes its settings, and changing that under the migration would break the
 * thing that protects customers during it. And they are being retired rather
 * than maintained - rewriting the bootstrap of twenty-seven plugins that
 * customers are running, to remove code that is about to be removed with them,
 * is risk spent on something already scheduled for deletion. What core can do
 * without touching them is notice the bug on a site suffering from it, which
 * health() below does. (Roadmap 4.5-PRO-02)
 */
class JSSTmodule {

    /** Schema versions applied per module, as slug => version. */
    const OPTION_SCHEMA = 'jsst_module_schema';

    /** The one settings namespace for modules that do not use js_ticket_config. */
    const OPTION_SETTINGS = 'jsst_module_settings';

    /** Everything registered through adopt(), as slug => declaration. */
    private static $jsst_registered = array();

    /**
     * Register a module and wire every hook its bootstrap used to wire itself.
     *
     * @param array $jsst_module
     *   slug     Required. The module slug, which is also its config tag and the
     *            name JSSTincluder resolves.
     *   file     Required. The plugin file, for the activation hooks.
     *   version  Required. Bumped when the schema changes.
     *   tables   Optional. Table names, without the WordPress prefix, to drop
     *            when a multisite blog is deleted. Never dropped on deactivate
     *            or on delete: see the note in the companion.
     *   schema   Optional. Path to a file returning version => array of SQL.
     *   boot     Optional. A callable run on plugins_loaded when the module is
     *            active - where it registers its own hooks.
     */
    public static function adopt($jsst_module) {
        if (empty($jsst_module['slug']) || empty($jsst_module['file'])) {
            return;
        }
        $jsst_module = array_merge(array(
            'version' => '1.0.0',
            'tables'  => array(),
            'schema'  => '',
            'boot'    => null,
        ), $jsst_module);

        self::$jsst_registered[$jsst_module['slug']] = $jsst_module;

        register_activation_hook($jsst_module['file'], array(__CLASS__, 'activated'));
        register_deactivation_hook($jsst_module['file'], array(__CLASS__, 'deactivated'));

        /* A new site on a network gets the same treatment activation gives, for
           the same reason: it is an activation, it just happened later. Both
           spellings, because the modern hook only exists from WordPress 5.1 and
           this plugin supports older. */
        if (version_compare(get_bloginfo('version'), '5.1', '>=')) {
            add_action('wp_insert_site', array(__CLASS__, 'newSite'));
        } else {
            add_action('wpmu_new_blog', array(__CLASS__, 'newBlog'), 10, 6);
        }
        add_filter('wpmu_drop_tables', array(__CLASS__, 'dropTables'));

        /* Registered once, however many modules adopt. Whether it fires at all
           depends on when adopt() was called: a module declared from inside
           plugins_loaded — which is where the Pro companion declares its whole
           set — is adopting a hook that is already running, and WordPress makes
           no dependable promise about a callback added at the priority
           currently executing. So boot() is also safe to call directly, and the
           companion does; the guard inside it is what makes both routes end in
           exactly one boot. */
        if (!self::$jsst_hooked) {
            self::$jsst_hooked = true;
            add_action('plugins_loaded', array(__CLASS__, 'boot'));
        }
    }

    /** Whether the plugins_loaded handler has been registered. */
    private static $jsst_hooked = false;

    /** Whether boot() has already run this request. */
    private static $jsst_booted = false;

    /** Everything adopted so far. */
    public static function registered() {
        return self::$jsst_registered;
    }

    /* ------------------------------------------------------------------ *
     * Activation
     * ------------------------------------------------------------------ */

    /**
     * The activation hook, taking the argument the copies forgot.
     *
     * $jsst_network_wide is what WordPress passes and what decides whether this
     * is one site or all of them. Declaring it is the whole fix; every copy of
     * this in the add-on directories branches on a variable it never receives.
     */
    public static function activated($jsst_network_wide = false) {
        self::eachBlog(array(__CLASS__, 'install'), $jsst_network_wide);
    }

    /**
     * Deactivation.
     *
     * Deliberately does nothing to the module's settings. The legacy add-ons
     * zero every one of theirs here and stash them in an option for a later
     * reactivation, which loses data outright when two add-ons share a settings
     * tag and are switched off one after the other. A module that is off is a
     * module whose settings are not being read; there is nothing to hide and
     * nothing to zero. (Roadmap 4.5-PRO-02)
     */
    public static function deactivated($jsst_network_wide = false) {
        do_action('jsst_module_deactivated', self::$jsst_registered);
    }

    public static function newSite($jsst_site) {
        $jsst_blogid = is_object($jsst_site) && isset($jsst_site->blog_id) ? (int) $jsst_site->blog_id : 0;
        if ($jsst_blogid <= 0) {
            return;
        }
        switch_to_blog($jsst_blogid);
        self::install();
        restore_current_blog();
    }

    public static function newBlog($jsst_blogid, $jsst_userid = 0, $jsst_domain = '', $jsst_path = '', $jsst_siteid = 0, $jsst_meta = array()) {
        switch_to_blog((int) $jsst_blogid);
        self::install();
        restore_current_blog();
    }

    /**
     * Tables to remove when a whole site is deleted from a network.
     *
     * The only place a module's tables are ever dropped, and the only one where
     * dropping them is right: the site they belonged to no longer exists.
     */
    public static function dropTables($jsst_tables) {
        foreach (self::$jsst_registered as $jsst_module) {
            foreach ((array) $jsst_module['tables'] as $jsst_table) {
                $jsst_tables[] = jssupportticket::$_db->prefix . $jsst_table;
            }
        }
        return $jsst_tables;
    }

    /**
     * Run a callable once per site, correctly on a network and on a single site.
     *
     * The loop the add-ons each carry a copy of, with the two things their
     * copies get wrong: it is told whether the activation was network-wide
     * rather than guessing, and it restores the current blog on every iteration
     * rather than leaving the switch in place for whatever runs next.
     */
    public static function eachBlog($jsst_callback, $jsst_network_wide = false) {
        if (!$jsst_network_wide || !function_exists('is_multisite') || !is_multisite()) {
            call_user_func($jsst_callback);
            return;
        }
        /* jssupportticket::$_db rather than a global $wpdb, which is the same
           object and the wrong spelling of it everywhere else in this plugin. */
        $jsst_db = jssupportticket::$_db;
        foreach ((array) $jsst_db->get_col("SELECT blog_id FROM `" . $jsst_db->blogs . "`") as $jsst_blogid) {
            switch_to_blog((int) $jsst_blogid);
            call_user_func($jsst_callback);
            restore_current_blog();
        }
    }

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    /**
     * Bring every adopted module's schema up to date on the current blog.
     *
     * Also called from boot() on every request, because an activation hook that
     * never fired - files replaced over FTP, a deployment, a site restored from
     * a backup - is exactly the case where a site quietly runs a version behind
     * and nobody finds out until a query fails. The steady-state cost is one
     * option read and a string comparison.
     */
    public static function install() {
        $jsst_applied = get_option(self::OPTION_SCHEMA, array());
        if (!is_array($jsst_applied)) {
            $jsst_applied = array();
        }
        $jsst_changed = false;

        foreach (self::$jsst_registered as $jsst_slug => $jsst_module) {
            if ($jsst_module['schema'] === '' || !file_exists($jsst_module['schema'])) {
                continue;
            }
            $jsst_steps = include $jsst_module['schema'];
            if (!is_array($jsst_steps)) {
                continue;
            }
            /* Applied in version order, and only the steps this site has not
               had. The whole history is kept rather than the current shape
               alone: a fresh install needs all of it and an upgrade from any
               earlier version needs the part it missed, so one file answers
               both by being replayed from wherever the site actually is. */
            uksort($jsst_steps, 'version_compare');
            $jsst_at = isset($jsst_applied[$jsst_slug]) ? (string) $jsst_applied[$jsst_slug] : '0';

            foreach ($jsst_steps as $jsst_version => $jsst_statements) {
                if (version_compare((string) $jsst_version, $jsst_at, '<=')) {
                    continue;
                }
                if (!function_exists('dbDelta')) {
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                }
                foreach ((array) $jsst_statements as $jsst_sql) {
                    /* dbDelta rather than a bare query: it is the only thing in
                       WordPress that adds a column to a table that already
                       exists without being told whether it is there, which is
                       what an upgrade step almost always is. */
                    dbDelta($jsst_sql);
                }
                $jsst_at = (string) $jsst_version;
            }
            if (!isset($jsst_applied[$jsst_slug]) || $jsst_applied[$jsst_slug] !== $jsst_at) {
                $jsst_applied[$jsst_slug] = $jsst_at;
                $jsst_changed = true;
            }
        }
        if ($jsst_changed) {
            update_option(self::OPTION_SCHEMA, $jsst_applied, false);
        }
    }

    /**
     * Load every adopted module that should be running.
     *
     * A module carried by the Pro companion is loaded only when JSSTpro says it
     * is running — licensed, chosen, and not still coming from a legacy add-on.
     * A module that is its own plugin has already been decided by WordPress
     * having activated it. (Roadmap 4.5-PRO-01)
     */
    public static function boot() {
        /* Once per request, whichever route got here. Running the schema pass
           twice would cost a little; calling a module's boot callback twice
           would register every one of its hooks twice, which is a ticket
           notified about twice and an e-mail sent twice. */
        if (self::$jsst_booted) {
            return;
        }
        self::$jsst_booted = true;
        self::install();
        foreach (self::$jsst_registered as $jsst_slug => $jsst_module) {
            if (class_exists('JSSTpro') && JSSTpro::module($jsst_slug) !== false && !JSSTpro::running($jsst_slug)) {
                continue;
            }
            if (is_callable($jsst_module['boot'])) {
                call_user_func($jsst_module['boot'], $jsst_slug);
            }
        }
    }

    /* ------------------------------------------------------------------ *
     * Settings
     * ------------------------------------------------------------------ */

    /**
     * One module setting.
     *
     * Reads js_ticket_config first, because that is where every existing
     * module's settings are and where the configuration screen edits them —
     * moving them somewhere new would mean two places to look and a migration
     * that buys nothing. The option store below it is for a module with settings
     * that are not user-editable configuration: internal state, a cursor, a last
     * run. Both are namespaced by slug, which is what makes a module's settings
     * something that can be backed up, previewed and rolled back as a unit.
     */
    public static function setting($jsst_slug, $jsst_key, $jsst_default = '') {
        $jsst_value = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT configvalue FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configname = %s AND addon = %s",
            $jsst_key,
            self::configTag($jsst_slug)
        ));
        if ($jsst_value !== null) {
            return $jsst_value;
        }
        $jsst_all = get_option(self::OPTION_SETTINGS, array());
        if (!is_array($jsst_all) || !isset($jsst_all[$jsst_slug]) || !is_array($jsst_all[$jsst_slug])) {
            return $jsst_default;
        }
        return array_key_exists($jsst_key, $jsst_all[$jsst_slug]) ? $jsst_all[$jsst_slug][$jsst_key] : $jsst_default;
    }

    /**
     * Write a module setting to the option store.
     *
     * Deliberately never writes js_ticket_config: those rows belong to the
     * configuration screen, which validates them, records them and knows how to
     * render each one. A module writing them behind its back would produce
     * settings the screen cannot show and cannot correct.
     */
    public static function setSetting($jsst_slug, $jsst_key, $jsst_value) {
        $jsst_all = get_option(self::OPTION_SETTINGS, array());
        if (!is_array($jsst_all)) {
            $jsst_all = array();
        }
        if (!isset($jsst_all[$jsst_slug]) || !is_array($jsst_all[$jsst_slug])) {
            $jsst_all[$jsst_slug] = array();
        }
        $jsst_all[$jsst_slug][$jsst_key] = $jsst_value;
        update_option(self::OPTION_SETTINGS, $jsst_all, false);
    }

    /**
     * The tag a module's settings rows carry.
     *
     * Almost always the slug. JSSTlegacy knows the three that are not, and is
     * asked rather than second-guessed so that there is one list of exceptions
     * rather than two. (Roadmap 4.5-PRO-02)
     */
    public static function configTag($jsst_slug) {
        if (class_exists('JSSTlegacy')) {
            $jsst_footprint = JSSTlegacy::footprint($jsst_slug);
            return $jsst_footprint['configtag'];
        }
        return $jsst_slug;
    }

    /* ------------------------------------------------------------------ *
     * Noticing the bug we cannot fix from here
     * ------------------------------------------------------------------ */

    /**
     * Legacy add-ons that are network-activated and missing their tables here.
     *
     * The bug described at the top of this file cannot be fixed without editing
     * twenty-four plugins, and PRO-02 is retiring them instead. What core can do
     * is stop it being invisible: on a multisite where an add-on was network
     * activated, check whether the tables it should have created on *this* blog
     * are actually here, and say so if they are not.
     *
     * Single-site installs return an empty array immediately and pay nothing —
     * the bug cannot occur there, which is exactly why it survived so long.
     *
     * @return array of array(slug, label, table)
     */
    public static function health() {
        if (!function_exists('is_multisite') || !is_multisite() || !class_exists('JSSTpro')) {
            return array();
        }
        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $jsst_missing = array();
        foreach (JSSTpro::modules() as $jsst_slug => $jsst_module) {
            if (!JSSTpro::legacyActive($jsst_slug)) {
                continue;
            }
            $jsst_plugin = class_exists('JSSTlegacy') ? JSSTlegacy::pluginFile($jsst_slug) : '';
            if ($jsst_plugin === '' || !is_plugin_active_for_network($jsst_plugin)) {
                continue;
            }
            foreach (self::legacyTables($jsst_slug) as $jsst_table) {
                $jsst_full = jssupportticket::$_db->prefix . $jsst_table;
                $jsst_found = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_full));
                if ($jsst_found !== $jsst_full) {
                    $jsst_missing[] = array(
                        'slug'  => $jsst_slug,
                        'label' => $jsst_module['label'],
                        'table' => $jsst_full,
                    );
                }
            }
        }
        return $jsst_missing;
    }

    /**
     * The tables a legacy add-on owns.
     *
     * Only the ones worth checking: a module with no table of its own cannot
     * suffer the bug, and guessing at table names would produce a screen full of
     * false alarms. Extendable, because the add-ons are not going to tell us.
     */
    private static function legacyTables($jsst_slug) {
        $jsst_known = array(
            'faq'                => array('js_ticket_faqs'),
            'knowledgebase'      => array('js_ticket_articles', 'js_ticket_categories'),
            'download'           => array('js_ticket_downloads'),
            'announcement'       => array('js_ticket_announcements'),
            'feedback'           => array('js_ticket_feedback'),
            'agent'              => array('js_ticket_staff', 'js_ticket_acl_roles'),
            'timetracking'       => array('js_ticket_time_tracking'),
            'privatecredentials' => array('js_ticket_private_credentials'),
            'multiform'          => array('js_ticket_forms'),
            'mail'               => array('js_ticket_mails'),
            'paidsupport'        => array('js_ticket_transactions'),
        );
        $jsst_tables = isset($jsst_known[$jsst_slug]) ? $jsst_known[$jsst_slug] : array();
        return apply_filters('jsst_module_legacy_tables', $jsst_tables, $jsst_slug);
    }
}
