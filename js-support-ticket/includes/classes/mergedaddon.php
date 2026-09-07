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
if (class_exists('JSSTmergedaddon')) {
    return;
}


/**
 * Compatibility layer for add-ons whose capability has been absorbed into the
 * free core. (Roadmap 4.0-CORE-19)
 *
 * The contract, for every merged capability:
 *
 *  - Core owns the feature outright, on every site. A legacy add-on that is
 *    still active does not change which code runs: JSSTincluder::getPluginPath()
 *    resolves a merged module to core's file whenever core has one, and
 *    suppressLegacyHooks() takes the add-on's callbacks back off the hooks that
 *    core now serves. An old site therefore runs the current code, not the code
 *    it happened to buy two years ago.
 *  - The add-on's files are still used for anything core does not ship — a
 *    screen, a table class or a template that only ever existed in the add-on
 *    resolves to the add-on exactly as before, so a Pro-only extra (the ban
 *    log, for instance) keeps working.
 *  - Nothing is registered twice, nothing is rendered twice, no hook fires
 *    twice: core registers its own copy precisely because the add-on's has been
 *    removed.
 *  - Both paths read and write the same tables and the same config rows, so
 *    existing data keeps working with no migration and no re-entry.
 *  - Deleting the legacy add-on runs its own uninstall.php, which drops tables
 *    that are now core data. Core snapshots those tables before the uninstall
 *    and restores them afterwards, so the delete cannot destroy the feature.
 *  - Core never emits an error or a warning about a legacy add-on. The only
 *    user-visible output is one dismissible notice saying the add-on is now
 *    part of the free core and can be deactivated.
 */
class JSSTmergedaddon {

    /**
     * Merged capabilities: add-on slug => description of the merge.
     *
     * tables:  tables the legacy uninstall.php drops that are now core data.
     * options: wp_options rows the legacy uninstall.php removes.
     * label:   human name used in the "now included" notice.
     * version: core version code that absorbed the capability.
     */
    private static $jsst_merged = array(
        'tickethistory' => array(
            'label'   => 'Ticket History',
            'version' => '400',
            'tables'  => array('js_ticket_activity_log'),
            'options' => array('jsst-addon-tickethistory-active-state', 'jsst-addon-tickethistory-version'),
        ),
        'note' => array(
            'label'   => 'Private Note',
            'version' => '400',
            'tables'  => array('js_ticket_notes'),
            'options' => array('jsst-addon-note-active-state', 'jsst-addon-note-version'),
        ),
        'cannedresponses' => array(
            'label'   => 'Canned Responses',
            'version' => '400',
            'tables'  => array('js_ticket_department_message_premade'),
            'options' => array('jsst-addon-cannedresponses-active-state', 'jsst-addon-premaderesponses-version'),
        ),
        'helptopic' => array(
            'label'   => 'Help Topic',
            'version' => '400',
            'tables'  => array('js_ticket_help_topics'),
            'options' => array('jsst-addon-helptopic-active-state', 'jsst-addon-helptopic-version'),
        ),
        // Settings and a cron only; the data it removes lives in the ticket tables.
        'autocleanup' => array(
            'label'   => 'Auto Cleanup',
            'version' => '400',
            'tables'  => array(),
            'options' => array('jsst-addon-autocleanup-active-state', 'jsst-addon-autocleanup-version'),
        ),
        // Both tables are core data now — the block list and the log of what it
        // stopped — so both are protected from the legacy uninstall.
        'banemail' => array(
            'label'   => 'Ban Email',
            'version' => '400',
            'tables'  => array('js_ticket_email_banlist', 'js_ticket_banlist_log'),
            'options' => array('jsst-addon-banemail-active-state', 'jsst-addon-banemail-version'),
        ),
        // Two capacity checks over the tickets table; no storage of its own.
        'maxticket' => array(
            'label'   => 'Max Tickets',
            'version' => '400',
            'tables'  => array(),
            'options' => array('jsst-addon-maxticket-active-state', 'jsst-addon-maxticket-version'),
        ),
        // The add-on stored nothing; it built report payloads on demand.
        'export' => array(
            'label'   => 'Export',
            'version' => '400',
            'tables'  => array(),
            'options' => array('jsst-addon-export-active-state', 'jsst-addon-export-version'),
        ),
        // Two wp_add_dashboard_widget calls over model methods that were already
        // in core. No tables, no screens of its own.
        'dashboardwidgets' => array(
            'label'   => 'Dashboard Widgets',
            'version' => '400',
            'tables'  => array(),
            'options' => array('jsst-addon-dashboardwidgets-active-state', 'jsst-addon-dashboardwidgets-version'),
        ),
        // A pure licence gate: the add-on had no tables, no screens and an empty
        // model. All it did was scope two settings — the registration role and the
        // registration CAPTCHA — so that core hid them without it.
        'useroptions' => array(
            'label'   => 'User Options',
            'version' => '400',
            'tables'  => array(),
            'options' => array('jsst-addon-useroptions-active-state', 'jsst-addon-useroptions-version'),
        ),
        // Lock state is a column on js_ticket_tickets, so this add-on has no
        // table of its own either.
        'actions' => array(
            'label'   => 'Ticket Actions',
            'version' => '400',
            'tables'  => array(),
            'options' => array('jsst-addon-actions-active-state', 'jsst-addon-actions-version'),
        ),
    );

    /**
     * The merged map, filterable so a module can register its own merge without
     * editing this file.
     */
    public static function map() {
        return apply_filters('jsst_merged_addons', self::$jsst_merged);
    }

    /**
     * Is this capability part of the free core now?
     */
    public static function isMerged($jsst_slug) {
        $jsst_map = self::map();
        return isset($jsst_map[$jsst_slug]);
    }

    /**
     * Sub-modules that belong to a merged capability.
     *
     * A sub-module is not an add-on in its own right — it never appears in
     * jssupportticket::$_active_addons — so isMerged() cannot answer for it,
     * and JSSTincluder::getPluginPath() would otherwise send it to the add-on
     * directory, or to the premium up-sell page once the add-on is deactivated.
     * Core owning the parent capability means core owns these too.
     * (Roadmap 4.0-CORE-19)
     */
    private static $jsst_merged_submodules = array(
        'banemaillog'   => 'banemail',
        'email_banlist' => 'banemail',
    );

    /**
     * Which merged capability owns this module — itself when the module is the
     * merged add-on, its parent when the module is one of that add-on's
     * sub-modules, and '' when core does not own it at all.
     */
    public static function mergedOwner($jsst_module) {
        if (self::isMerged($jsst_module)) {
            return $jsst_module;
        }
        if (isset(self::$jsst_merged_submodules[$jsst_module])) {
            $jsst_parent = self::$jsst_merged_submodules[$jsst_module];
            if (self::isMerged($jsst_parent)) {
                return $jsst_parent;
            }
        }
        return '';
    }

    /**
     * Is the legacy standalone add-on still active on this site?
     */
    public static function legacyActive($jsst_slug) {
        if (!is_array(jssupportticket::$_active_addons)) {
            return false;
        }
        return in_array($jsst_slug, jssupportticket::$_active_addons, true);
    }

    /**
     * Should core provide this capability itself?
     *
     * Always, once the capability is merged. Until 4.0 this stood down while the
     * legacy add-on was active and the add-on's files won — which meant a site
     * that had bought the add-on years ago kept running that old code and never
     * received a fix made in core. The add-on is now overridden instead: its
     * files lose to core's in JSSTincluder::getPluginPath() and its callbacks
     * are removed by suppressLegacyHooks(), so core is free to register.
     *
     * legacyActive() is still the right test for something that exists *only*
     * in the add-on. coreOwns() answers a different question: who serves the
     * merged capability.
     */
    public static function coreOwns($jsst_slug) {
        return self::isMerged($jsst_slug);
    }

    /**
     * Is the capability available at all, from either side?
     *
     * This is the replacement for the old
     *   in_array('<slug>', jssupportticket::$_active_addons)
     * availability checks. For a merged capability the answer is always yes:
     * free installs get core's implementation, existing installs keep the
     * add-on's.
     */
    public static function featureEnabled($jsst_slug) {
        return self::isMerged($jsst_slug) || self::legacyActive($jsst_slug);
    }

    /**
     * Make sure a merged capability's tables exist before code outside its own
     * module reads them.
     *
     * Every merged module self-heals on its own read and write paths, but a
     * screen that queries one of these tables directly — the admin dashboard
     * queries the activity log and the canned responses — never goes through
     * the module and so never triggers that. On a site where the tables were
     * never created this is the difference between a populated panel and a
     * database error in the log.
     *
     * Nothing happens while the legacy add-on is active: the add-on owns the
     * table then, its own activation created it, and its model has no
     * ensureSchema() at all — which is what method_exists guards. ensureSchema()
     * itself returns once JSSTschemaguard confirms the table matches, so the
     * steady-state cost here is one option read plus one SHOW COLUMNS per
     * request — not per call. (Roadmap 4.0-CORE-19)
     */
    public static function ensureSchema($jsst_slug) {
        if (!self::coreOwns($jsst_slug)) {
            return;
        }
        $jsst_model = JSSTincluder::getJSModel($jsst_slug);
        if (method_exists($jsst_model, 'ensureSchema')) {
            $jsst_model->ensureSchema();
        }
    }

    /**
     * WordPress hooks on which a merged add-on's callbacks are taken down.
     *
     * Anything named jsst* is a plugin-internal integration point and is handled
     * by the prefix rule in suppressLegacyHooks(); this list is only for the
     * WordPress hooks the merged add-ons use to serve their capability.
     * Deliberately short: plugins_loaded (licence and update checks),
     * wp_insert_site, wpmu_new_blog and wpmu_drop_tables are NOT here, because
     * they are the add-on's own housekeeping and core does not replace them.
     */
    private static $jsst_suppress_wp_hooks = array(
        'init', 'admin_init', 'wp_dashboard_setup', 'cron_schedules',
    );

    /** Callback identity => the file that defines it. Built once per request. */
    private static $jsst_callback_files = array();

    /** Whether the sweep has already run in this request. */
    private static $jsst_suppressed = false;

    /**
     * Take a still-active legacy add-on back off the hooks core now serves.
     *
     * Add-on plugin directories sort before the core plugin in active_plugins
     * ("js-support-ticket-x/" < "js-support-ticket/", because "-" is 0x2D and
     * "/" is 0x2F), so by the time this file is loaded the add-on's constructor
     * has already run and its add_action() calls are in $wp_filter. They cannot
     * be prevented, only removed — which is what this does, before init fires.
     *
     * A callback is the add-on's if the file that *defines* it lives inside the
     * add-on's directory. That is asked by reflection rather than by matching
     * class names, so it stays correct across add-on versions, and the answer is
     * memoised per class so the second pass costs almost nothing.
     *
     * Only two kinds of hook are swept: anything whose name starts with "jsst"
     * (every one of those is a plugin integration point that core re-registers
     * for itself in jssupportticket::registeractions()), and the short list in
     * $jsst_suppress_wp_hooks. Everything else the add-on hooks is left alone.
     */
    public static function suppressLegacyHooks() {
        $jsst_dirs = array();
        foreach (self::map() as $jsst_slug => $jsst_meta) {
            if (self::legacyActive($jsst_slug)) {
                $jsst_dirs[] = wp_normalize_path(WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_slug . '/');
            }
        }
        if (empty($jsst_dirs)) {
            return;
        }
        global $wp_filter;
        if (!is_array($wp_filter)) {
            return;
        }
        $jsst_hooks = apply_filters('jsst_merged_addon_suppressed_hooks', self::$jsst_suppress_wp_hooks);
        $jsst_drop = array();
        foreach ($wp_filter as $jsst_hook => $jsst_registered) {
            if (stripos($jsst_hook, 'jsst') !== 0 && !in_array($jsst_hook, $jsst_hooks, true)) {
                continue;
            }
            $jsst_callbacks = is_object($jsst_registered) && isset($jsst_registered->callbacks)
                ? $jsst_registered->callbacks
                : (is_array($jsst_registered) ? $jsst_registered : array());
            foreach ($jsst_callbacks as $jsst_priority => $jsst_set) {
                if (!is_array($jsst_set)) {
                    continue;
                }
                foreach ($jsst_set as $jsst_entry) {
                    if (!isset($jsst_entry['function'])) {
                        continue;
                    }
                    $jsst_file = self::callbackFile($jsst_entry['function']);
                    if ($jsst_file === '') {
                        continue;
                    }
                    foreach ($jsst_dirs as $jsst_dir) {
                        if (strpos($jsst_file, $jsst_dir) === 0) {
                            // Collected, not removed here: removing inside the
                            // walk would rewrite the array being iterated.
                            $jsst_drop[] = array($jsst_hook, $jsst_entry['function'], $jsst_priority);
                            break;
                        }
                    }
                }
            }
        }
        foreach ($jsst_drop as $jsst_one) {
            remove_filter($jsst_one[0], $jsst_one[1], $jsst_one[2]);
            do_action('jsst_merged_addon_hook_suppressed', $jsst_one[0], $jsst_one[2]);
        }
        self::$jsst_suppressed = true;
    }

    /**
     * The file a callback is defined in, or '' when it cannot be determined.
     */
    private static function callbackFile($jsst_callback) {
        $jsst_key = '';
        if (is_string($jsst_callback)) {
            $jsst_key = $jsst_callback;
        } elseif (is_array($jsst_callback) && count($jsst_callback) === 2) {
            $jsst_class = is_object($jsst_callback[0]) ? get_class($jsst_callback[0]) : (string) $jsst_callback[0];
            $jsst_key = $jsst_class . '::' . $jsst_callback[1];
        } elseif (is_object($jsst_callback)) {
            $jsst_key = 'obj:' . spl_object_hash($jsst_callback);
        }
        if ($jsst_key !== '' && isset(self::$jsst_callback_files[$jsst_key])) {
            return self::$jsst_callback_files[$jsst_key];
        }
        $jsst_file = '';
        try {
            if (is_string($jsst_callback) && strpos($jsst_callback, '::') !== false) {
                list($jsst_class, $jsst_method) = explode('::', $jsst_callback, 2);
                if (method_exists($jsst_class, $jsst_method)) {
                    $jsst_ref = new ReflectionMethod($jsst_class, $jsst_method);
                    $jsst_file = (string) $jsst_ref->getFileName();
                }
            } elseif (is_string($jsst_callback)) {
                if (function_exists($jsst_callback)) {
                    $jsst_ref = new ReflectionFunction($jsst_callback);
                    $jsst_file = (string) $jsst_ref->getFileName();
                }
            } elseif (is_array($jsst_callback) && count($jsst_callback) === 2) {
                $jsst_class = is_object($jsst_callback[0]) ? get_class($jsst_callback[0]) : (string) $jsst_callback[0];
                if (method_exists($jsst_class, $jsst_callback[1])) {
                    $jsst_ref = new ReflectionMethod($jsst_class, $jsst_callback[1]);
                    $jsst_file = (string) $jsst_ref->getFileName();
                }
            } elseif ($jsst_callback instanceof Closure || is_object($jsst_callback)) {
                $jsst_ref = ($jsst_callback instanceof Closure)
                    ? new ReflectionFunction($jsst_callback)
                    : new ReflectionMethod($jsst_callback, '__invoke');
                $jsst_file = (string) $jsst_ref->getFileName();
            }
        } catch (Exception $jsst_e) {
            $jsst_file = '';
        } catch (Error $jsst_e) {
            $jsst_file = '';
        }
        $jsst_file = $jsst_file === '' ? '' : wp_normalize_path($jsst_file);
        if ($jsst_key !== '') {
            self::$jsst_callback_files[$jsst_key] = $jsst_file;
        }
        return $jsst_file;
    }

    /**
     * Register the compatibility hooks. Called once from the plugin bootstrap.
     */
    public static function register() {
        // Now, because the add-ons are already loaded and their callbacks are
        // already registered; and once more at the end of plugins_loaded, which
        // is the last moment before init and catches an add-on that loaded after
        // core or added hooks from its own plugins_loaded callback.
        self::suppressLegacyHooks();
        add_action('plugins_loaded', array(__CLASS__, 'suppressLegacyHooks'), PHP_INT_MAX);
        add_action('pre_uninstall_plugin', array(__CLASS__, 'snapshotBeforeUninstall'), 10, 1);
        add_action('deleted_plugin', array(__CLASS__, 'restoreAfterDelete'), 10, 2);
        if (is_admin()) {
            add_action('admin_notices', array(__CLASS__, 'legacyAddonNotice'));
            add_action('wp_ajax_jsst_dismiss_merged_notice', array(__CLASS__, 'dismissNotice'));
        }
    }

    /**
     * Map a plugin basename back to an add-on slug.
     */
    private static function slugFromPlugin($jsst_plugin) {
        $jsst_dir = jssupportticketphplib::JSST_dirname((string) $jsst_plugin);
        if ($jsst_dir === '' || $jsst_dir === '.') {
            $jsst_dir = pathinfo((string) $jsst_plugin, PATHINFO_FILENAME);
        }
        if (strpos($jsst_dir, 'js-support-ticket-') !== 0) {
            return '';
        }
        return jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_dir);
    }

    /**
     * Copy the tables a merged add-on's uninstall.php is about to drop.
     *
     * Runs immediately before WordPress includes the add-on's uninstall.php.
     */
    public static function snapshotBeforeUninstall($jsst_plugin) {
        $jsst_slug = self::slugFromPlugin($jsst_plugin);
        if ($jsst_slug === '' || !self::isMerged($jsst_slug)) {
            return;
        }
        $jsst_map = self::map();
        $jsst_saved = array();
        foreach ($jsst_map[$jsst_slug]['tables'] as $jsst_table) {
            $jsst_live = jssupportticket::$_db->prefix . $jsst_table;
            $jsst_copy = jssupportticket::$_db->prefix . 'jsst_keep_' . $jsst_table;
            $jsst_exists = jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_live)
            );
            if ($jsst_exists != $jsst_live) {
                continue;
            }
            jssupportticket::$_db->query('DROP TABLE IF EXISTS `' . $jsst_copy . '`');
            jssupportticket::$_db->query('CREATE TABLE `' . $jsst_copy . '` LIKE `' . $jsst_live . '`');
            jssupportticket::$_db->query('INSERT INTO `' . $jsst_copy . '` SELECT * FROM `' . $jsst_live . '`');
            if (jssupportticket::$_db->last_error == null) {
                $jsst_saved[] = $jsst_table;
            }
        }
        if (!empty($jsst_saved)) {
            update_option('jsst_merged_addon_snapshot_' . $jsst_slug, $jsst_saved, false);
        }
    }

    /**
     * Put the snapshotted data back after the add-on has been deleted.
     */
    public static function restoreAfterDelete($jsst_plugin, $jsst_deleted) {
        if (!$jsst_deleted) {
            return;
        }
        $jsst_slug = self::slugFromPlugin($jsst_plugin);
        if ($jsst_slug === '' || !self::isMerged($jsst_slug)) {
            return;
        }
        $jsst_saved = get_option('jsst_merged_addon_snapshot_' . $jsst_slug);
        if (empty($jsst_saved) || !is_array($jsst_saved)) {
            return;
        }
        $jsst_restored = false;
        foreach ($jsst_saved as $jsst_table) {
            $jsst_live = jssupportticket::$_db->prefix . $jsst_table;
            $jsst_copy = jssupportticket::$_db->prefix . 'jsst_keep_' . $jsst_table;
            $jsst_exists = jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_copy)
            );
            if ($jsst_exists != $jsst_copy) {
                continue;
            }
            // Only restore what the uninstall actually removed; never overwrite
            // a live table that survived.
            $jsst_livexists = jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_live)
            );
            if ($jsst_livexists == $jsst_live) {
                jssupportticket::$_db->query('DROP TABLE IF EXISTS `' . $jsst_copy . '`');
                continue;
            }
            jssupportticket::$_db->query('RENAME TABLE `' . $jsst_copy . '` TO `' . $jsst_live . '`');
            if (jssupportticket::$_db->last_error == null) {
                $jsst_restored = true;
            }
        }
        delete_option('jsst_merged_addon_snapshot_' . $jsst_slug);
        if ($jsst_restored) {
            update_option('jsst_merged_addon_restored_' . $jsst_slug, 1, false);
        }
    }

    /**
     * Legacy add-ons that are active but no longer needed.
     */
    public static function redundant() {
        $jsst_list = array();
        foreach (self::map() as $jsst_slug => $jsst_info) {
            if (self::legacyActive($jsst_slug)) {
                $jsst_list[$jsst_slug] = $jsst_info['label'];
            }
        }
        return $jsst_list;
    }

    /**
     * One dismissible notice, never an error: the capability ships in core now,
     * the add-on may be deactivated, and nothing breaks either way.
     */
    public static function legacyAddonNotice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (get_option('jsst_merged_addon_notice_dismissed')) {
            return;
        }
        $jsst_redundant = self::redundant();
        if (empty($jsst_redundant)) {
            return;
        }
        $jsst_names = implode(', ', array_map('esc_html', $jsst_redundant));
        echo '<div class="notice notice-info is-dismissible jsst-merged-addon-notice" data-nonce="' . esc_attr(wp_create_nonce('jsst_merged_notice')) . '"><p>';
        echo esc_html(__('JS Help Desk 4.0 includes these features in the free core:', 'js-support-ticket'));
        echo ' <strong>' . wp_kses($jsst_names, JSST_ALLOWED_TAGS) . '</strong>. ';
        echo esc_html(__('The matching add-ons are still active and still in charge, so nothing has changed on this site. You can deactivate them whenever you like — your existing data stays in place and the built-in version takes over.', 'js-support-ticket'));
        echo '</p></div>';
        echo '<script>(function(){var n=document.querySelector(".jsst-merged-addon-notice");if(!n)return;n.addEventListener("click",function(e){if(!e.target.classList.contains("notice-dismiss"))return;var x=new XMLHttpRequest();x.open("POST",ajaxurl);x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");x.send("action=jsst_dismiss_merged_notice&_wpnonce="+encodeURIComponent(n.getAttribute("data-nonce")));});})();</script>';
    }

    public static function dismissNotice() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error();
        }
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst_merged_notice')) {
            wp_send_json_error();
        }
        update_option('jsst_merged_addon_notice_dismissed', 1, false);
        wp_send_json_success();
    }

}
