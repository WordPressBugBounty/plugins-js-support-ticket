<?php

/*
  Plugin Name: JS Help Desk – AI-Powered Support & Ticketing System
  Plugin URI: https://www.jshelpdesk.com
  Description: JS Help Desk is a trusted open source ticket system. JS Help Desk is a simple, easy to use, web-based customer support system. User can create ticket from front-end. JS Help Desk comes packed with lot features than most of the expensive(and complex) support ticket system on market. JS Help Desk provide you best industry help desk system.
  Author: JS Help Desk
  Version: 5.0.1
  Requires at least: 5.5
  Requires PHP: 7.4
  Text Domain: js-support-ticket
  Domain Path: /languages
  License: GPLv3
  Author URI: https://www.jshelpdesk.com
 */

if (!defined('ABSPATH'))
    die('Restricted Access');

class jssupportticket {


    /** Bumped when the desk-destination slug list in JSSTslugModel grows. */
    const DESK_SLUGS_VERSION = '650-desk-1';
    /** Marker for jsstNormaliseConfigChoices(); bump if its key lists grow. */
    const CONFIG_CHOICES_VERSION = '500-cfg-1';
    public static $_path;
    public static $_pluginpath;
    public static $jsst_data; /* data[0] for list , data[1] for total paginition ,data[2] userfieldsforview , data[3] userfield for form , data[4] for reply , data[5] for ticket history  , data[6] for internal notes  , data[7] for ban email  , data['ticket_attachment'] for attachment */
    public static $_pageid;
    public static $_db;
    public static $_config;
    public static $_sorton;
    public static $_sortorder;
    public static $_ordering;
    public static $_sortlinks;
    public static $_msg;
    public static $_wpprefixforuser;
    public static $jsst_colors;
    public static $_active_addons;
    public static $_addon_query;
    public static $_currentversion;
    public static $_search;
    public static $_captcha;
    public static $_jshdsession;


    function __construct() {
        // php 8.1 issues
        require_once 'includes/jssupportticketphplib.php';
        // to check what addons are active and create an array.
        $jsst_plugin_array = get_option('active_plugins');
        $jsst_addon_array = array();
        foreach ($jsst_plugin_array as $jsst_key => $jsst_value) {
            $jsst_plugin_name = pathinfo($jsst_value, PATHINFO_FILENAME);
            if(strstr($jsst_plugin_name, 'js-support-ticket-')){
                $jsst_addon_slug = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_plugin_name);
                /* An add-on may decline to run on this site - see
                   JSSTbundle::pluginActive(), which asks the same question. */
                if (apply_filters('jsst_bundle_enabled', true, $jsst_addon_slug)) {
                    $jsst_addon_array[] = $jsst_addon_slug;
                }
            }
        }
        self::$_active_addons = $jsst_addon_array;
        // above code is its right place

        /* Pro's modules join that list before anything is given a chance to read
           it, which is why this one class is pulled in here rather than from
           includes() with the rest of them: includes() itself asks the list what
           is active on its very first line, and a module that arrived a moment
           later would have been missed by it. JSSTpro::augment() depends on
           nothing but the options table, so it is safe this early.
           (Roadmap 4.5-PRO-01) */
        include_once __DIR__ . '/includes/classes/pro.php';
        /* The legacy programme comes with it and not later, because the two
           answer one question between them: a module is entitled by a Pro
           licence *or* by the add-on key the customer already holds, and
           augment() below asks that question about every module. Loaded here it
           gets both halves; loaded from includes() it would get the first half
           only, and a site running on nothing but its old keys would come up
           with every module switched off. Neither class touches the database at
           this point — both read options and the filesystem — which is what
           makes it safe this far ahead of $_db being set.
           (Roadmap 4.5-PRO-02) */
        include_once __DIR__ . '/includes/classes/legacy.php';
        /* Surviving a legacy add-on's uninstall.php, shared by the merged-core
           path and the bundle path so the two cannot drift - which they did,
           and a site lost twenty-two tables to the difference. Loaded here
           because both callers below register hooks that use it; it touches no
           database at include time. (Roadmap 6.5-ECO-02) */
        include_once __DIR__ . '/includes/classes/addonsnapshot.php';
        JSSTpro::augment();
        /* The nine bundles, immediately after Pro and for the same reason: the
           list below has to be complete before anything reads it, and a module
           that arrived a moment later would have been missed by every
           availability check in the plugin. Order matters between these two -
           JSSTbundle treats whatever Pro has already provided as taken, so that
           a module cannot be served from a bundle and the companion at once -
           and neither class touches the database, which is what makes both safe
           this far ahead of $_db being set. (Roadmap 6.5-ECO-01) */
        include_once __DIR__ . '/includes/classes/bundle.php';
        JSSTbundle::augment();

        /* The licence client. It answers "is this bundle paid for?", which is
           what JSSTpro and JSSTbundle ask while they decide what this site may
           run, so it has to exist before either is consulted. It reads options
           and the filesystem only - no database - which is what makes it safe
           this far ahead of $_db being set. (Roadmap 6.5-ECO-02) */
        include_once __DIR__ . '/includes/classes/license.php';
        // Finishing the move from the old add-ons to the bundles. (29 Sep 2026)
        include_once __DIR__ . '/includes/classes/upgradeassistant.php';
        // Installs this site's languages from jshelpdesk.com. (1 Oct 2026)
        include_once __DIR__ . '/includes/classes/translations.php';

        self::includes();
        self::jsstLoadWpCoreFiles();
        self::registeractions();
        JSSTmergedaddon::register(); // Roadmap 4.0-CORE-19
        JSSTpro::registerHooks();    // Roadmap 4.5-PRO-01
        JSSTlegacy::registerShims(); // Roadmap 4.5-PRO-02
        JSSTbundle::registerHooks(); // Roadmap 6.5-ECO-01
        JSSTlicense::registerHooks(); // Roadmap 6.5-ECO-02
        JSSTupgradeassistant::registerHooks();
        JSSTtranslations::registerHooks();
        JSSTprivacy::register();     // Roadmap 4.0-SEC-02
        JSSTdraft::registerHooks();  // Roadmap 4.0-UX-04
        JSSTpresence::registerHooks(); // Roadmap 4.0-UX-05
        JSSTcompanies::registerHooks(); // Roadmap 5.5-COM-06
        JSSTconsent::registerHooks();   // Roadmap 5.5-SEC-02
        JSSTaisources::registerHooks(); // Roadmap 6.0-AI-02
        JSSTailanguages::registerHooks(); // Roadmap 6.0-AI-09
        JSSTkbgovernance::registerHooks(); // Roadmap 6.0-KB-01
        JSSTloginredirect::registerHooks(); // Roadmap 4.5-FE-02
        /* Defuse hook registrations that cannot be called, once per request,
           after every add-on has had its turn on `init`. See
           JSSTincluder::pruneDeadHooks() - an add-on naming a method it never
           wrote is a fatal waiting for core to fire that hook, and this release
           fired one. (Roadmap 5.5-COM-01) */
        add_action('init', array('JSSTincluder', 'pruneDeadHooks'), PHP_INT_MAX);
        JSSTaitriage::registerHooks();  // Roadmap 6.0-AI-13
        JSSTcannedlibrary::registerHooks(); // Roadmap 6.0-KB-02
        JSSTaireview::registerHooks();  // Roadmap 6.0-AI-03
        JSSTairollout::registerHooks(); // Roadmap 6.0-AI-06
        JSSTaiusage::registerHooks();   // Roadmap 6.0-AI-07
        JSSTaigaps::registerHooks();    // Roadmap 6.0-AI-12
        // Core owns the widgets now; the legacy add-on's copies are unhooked by
        // suppressLegacyHooks() above, so nothing renders twice.
        // (Roadmap 4.0-CORE-09, 4.0-CORE-19)
        if (is_admin() && JSSTmergedaddon::coreOwns('dashboardwidgets')) {
            JSSTcoredashboardwidgets::register();
        }
        self::$_path = plugin_dir_path(__FILE__);
        self::$_pluginpath = plugins_url('/', __FILE__);
        self::$jsst_data = array();
        self::$_search = array();
        self::$_captcha = array();
        self::$_currentversion = '501';
        self::$_addon_query = array('select'=>'','join'=>'','where'=>'');
        self::$_jshdsession = JSSTincluder::getObjectClass('wphdsession');
        global $wpdb;
        self::$_db = $wpdb;
        if(is_multisite()) {
            self::$_wpprefixforuser = $wpdb->base_prefix;
        }else{
            self::$_wpprefixforuser = self::$_db->prefix;
        }
        add_filter('cron_schedules',array($this,'jssupportticket_customschedules'));
        add_filter('the_content', array($this, 'checkRequest'));
        JSSTincluder::getJSModel('configuration')->getConfiguration();
        register_activation_hook(__FILE__, array($this, 'jssupportticket_activate'));
        register_deactivation_hook(__FILE__, array($this, 'jssupportticket_deactivate'));
        if(version_compare(get_bloginfo('version'),'5.1', '>=')){ //for wp version >= 5.1
            add_action('wp_insert_site', array($this, 'jssupportticket_new_site')); //when new site is added in multisite
        }else{ //for wp version < 5.1
            add_action('wpmu_new_blog', array($this, 'jssupportticket_new_blog'), 10, 6);
        }
        add_filter('wpmu_drop_tables', array($this, 'jssupportticket_delete_site')); //when site is deleted in multisite

        // add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
        add_action('jssupporticket_updateticketstatus', array($this,'updateticketstatus'));
        if(JSSTmergedaddon::featureEnabled('actions')){
            add_action('template_redirect', array($this, 'printTicket'), 5); // Only for the print ticket in wordpress
        }
        add_action('admin_init', array($this, 'jssupportticket_activation_redirect'));
        add_action( 'wp_footer', array($this,'checkScreenTag') );
        add_action( 'jsst_resetnotificationvalues', array($this, 'jsst_resetnotificationvalues'));
        //for style sheets
        add_action('wp_head', array($this,'jsst_register_plugin_styles'));
        add_action('admin_enqueue_scripts', array($this,'jsst_admin_register_plugin_styles') );
        add_action('jsst_reset_aadon_query', array($this,'jsst_reset_aadon_query') );
        
        add_action('jssupporticket_ticketviaemail', array($this,'ticketviaemail'));// this also handles ticket over due and ticket feedback
        /* Catch an existing site up with the desk destinations added since its
           slug table was seeded. Guarded on an option so the ordinary request
           costs one option read; when it does add a row it flushes the rewrite
           rules, because `paramregister.php` builds them from that table and a
           row nothing has flushed for is a route WordPress does not know.
           (Roadmap 4.5-FE-02) */
        add_action('init', array($this, 'jsstEnsureDeskSlugs'), 1);
        add_action('init', array($this, 'jsstNormaliseConfigChoices'), 1);
        add_action('init', array($this,'jsst_handle_public_cronjob'));
        add_action('admin_init', array($this,'jsst_handle_search_form_data'));
        add_action('admin_init', array($this,'jsst_handle_delete_cookies'));
        add_action('init', array($this,'jsst_handle_search_form_data'));
        add_action( 'jsst_delete_expire_session_data', array($this , 'jshd_delete_expire_session_data') );
        add_filter('safe_style_css', array($this,'jsjp_safe_style_css'));
        if( !wp_next_scheduled( 'jsst_delete_expire_session_data' ) ) {
            // Schedule the event
            wp_schedule_event( time(), 'daily', 'jsst_delete_expire_session_data' );
        }
        /* 4.0.0 and earlier checked their per-add-on keys against
           jshelpdesk.com/setup/ every day and auto-updated old add-ons from
           jshelpdesk.com/appsys/. 5.0.0 speaks only to the new licence server,
           through JSSTlicense, which schedules its own check. A site arriving
           from 4.0.0 has both old jobs cleared, and the old expiry flag with
           them, rather than left firing into code that is no longer here.
           (Roadmap 6.5-ECO-02) */
        foreach (array('jsst_process_transation_key_status', 'jsst_auto_update_addons') as $jsst_retired_job) {
            if (wp_next_scheduled($jsst_retired_job)) {
                wp_clear_scheduled_hook($jsst_retired_job);
            }
        }
        if (false !== get_option('jsst_show_key_expiry_msg', false)) {
            delete_option('jsst_show_key_expiry_msg');
        }
        /* And telling the people who are on it. Ordered after the follower
           hook on purpose: a notification is addressed to whoever is watching,
           and somebody added by this very event should be told about it.
           (Roadmap 4.5-UX-02) */
        add_action('jsst_event', array($this, 'jsst_notify'), 20);
        if (class_exists('JSSTblocks')) {
            JSSTblocks::registerHooks();
        }
        /* The Free/Pro matrix, fetchable so a website can show the generated
           answer rather than a retyped copy of it. (Roadmap 4.5-MKT-02) */
        if (class_exists('JSSTplans')) {
            JSSTplans::registerHooks();
        }
        /* The developer reference and the authorisation matrix are the two
           things core still publishes about itself.

           The subscribers that used to be listed here now register themselves
           from their own addons, and the order between them still holds because
           each one sets its own priority on jsst_event rather than relying on
           the order WordPress loads plugins in: SLA clocks at 5 so everything
           after them sees a ticket whose promises are up to date, the follower
           at 10, notifications at 20, the rules engine at 30 so an automation's
           writes come after the people watching have been told about the human
           action that started it, routing at 40 so a rule that assigns
           deliberately beats the automatic choice, and the webhook dispatcher
           last at 50 because what it sends out should describe the desk after
           everything local has finished happening. */
        if (class_exists('JSSThooks')) {
            JSSThooks::registerHooks();
        }
        /* The authorisation matrix publishes itself as a file the same way the
           developer reference does, and behind the same capability: it names
           every entry point together with what guards each one, which is a
           review document for the site owner and a shopping list for anybody
           else. (Roadmap 5.0-SEC-01) */
        if (class_exists('JSSTauthmatrix')) {
            JSSTauthmatrix::registerHooks();
        }
        add_action(JSSTnotifications::DIGEST_HOOK, array($this, 'jsst_run_notification_digests'));
        if (!wp_next_scheduled(JSSTnotifications::DIGEST_HOOK)) {
            wp_schedule_event(time(), 'hourly', JSSTnotifications::DIGEST_HOOK);
        }
        add_action( 'upgrader_process_complete', array($this , 'jssupportticket_upgrade_completed'), 10, 2 );
        // The database upgrade must not depend on that hook alone: see
        // jssupportticket_maybe_upgrade_database().
        add_action( 'admin_init', array($this , 'jssupportticket_maybe_upgrade_database'), 5 );
        // If seo plugin is activated
        if (is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ){
            add_filter( 'aioseo_disable_shortcode_parsing', '__return_true' );
        }
        // Roles and capabilities are reconciled from a canonical definition on
        // activation and on every upgrade. reconcile() is idempotent and costs a
        // single option read once the site is up to date. (Roadmap 3.2-CORE-01)
        add_action('admin_init', array($this, 'jsst_reconcile_roles'), 1);
        // Free-tier suggestion search: the ranking matches subject and body
        // separately, which MySQL serves only from per-column FULLTEXT indexes,
        // while every source table ships a composite one. Repaired from the
        // admin side once per version - the search itself runs while a customer
        // types and must never build an index.
        add_action('admin_init', array($this, 'jsst_repair_suggestion_indexes'));
        // Help-desk access for agents is derived from the agent list rather than
        // stored on the user, so adding or removing an agent takes effect at
        // once and an ex-agent cannot keep a stale capability. (Roadmap 3.2-CORE-01)
        add_filter('user_has_cap', array('JSSTroles', 'grantAgentCapability'), 10, 4);
    }

    function jssupportticket_upgrade_completed( $jsst_upgrader_object, $jsst_options ) {
        // The path to our plugin's main file
        $jsst_our_plugin = plugin_basename( __FILE__ );
        // If an update has taken place and the updated type is plugins and the plugins element exists
        if( $jsst_options['action'] == 'update' && $jsst_options['type'] == 'plugin' && isset( $jsst_options['plugins'] ) ) {
            // Iterate through the plugins being updated and check if ours is there
            foreach( $jsst_options['plugins'] as $jsst_plugin ) {
                if( $jsst_plugin == $jsst_our_plugin ) {
                    // restore colors data
                    // require_once(JSST_PLUGIN_PATH . 'includes/css/style.php');
                    // restore colors data end
                    update_option('jsst_currentversion', self::$_currentversion);
                    include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
                    JSSTupdates::checkUpdates(self::$_currentversion);
                    JSSTincluder::getJSModel('jssupportticket')->updateColorFile();
                }
            }
        }
    }

    /**
     * Bring the database up to this version on the first admin page load after
     * the code changed, however it changed.
     *
     * upgrader_process_complete is not enough on its own. During a WordPress
     * update the handler that runs is the OLD version's, and an update by zip
     * upload reports "install", not "update", so ours skips it. Until an
     * administrator happened to open the JS Help Desk dashboard, a 4.0.0
     * database ran under 5.0.0 code (installation test, 26 September 2026).
     *
     * One autoloaded option read per admin request; the updater runs only when
     * the stored version is not this one, and the option is written once the
     * database really is at this version.
     */
    function jssupportticket_maybe_upgrade_database() {
        if ((string) get_option('jsst_currentversion') === (string) self::$_currentversion) {
            return;
        }
        if (wp_doing_ajax() || !current_user_can('manage_options')) {
            return;
        }
        include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
        JSSTupdates::checkUpdates();
        if ((string) JSSTupdates::getInstalledVersion() === (string) self::$_currentversion) {
            update_option('jsst_currentversion', self::$_currentversion);
        }
    }

    function jssupportticket_customschedules($jsst_schedules){
        $jsst_schedules['halfhour'] = array(
           'interval' => 1800,
           'display'=> 'Half hour'
        );
       return $jsst_schedules;
    }

    function jssupportticket_activate($jsst_network_wide = false) {
        include_once 'includes/activation.php';
        if(function_exists('is_multisite') && is_multisite() && $jsst_network_wide){
            global $wpdb;
            $jsst_blogs = jssupportticket::$_db->get_col("SELECT blog_id FROM " . jssupportticket::$_db->base_prefix . "blogs");
            foreach($jsst_blogs as $jsst_blog_id){
                switch_to_blog( $jsst_blog_id );
                JSSTactivation::jssupportticket_activate();
                restore_current_blog();
            }
        }else{
            JSSTactivation::jssupportticket_activate();
        }
        wp_schedule_event(time(), 'daily', 'jssupporticket_updateticketstatus');
        add_option('jssupportticket_do_activation_redirect', true);
        wp_schedule_event(time(), 'halfhour', 'jssupporticket_ticketviaemail');// this also handles ticket overdue (bcz of hors configuration)
    }

    function jssupportticket_new_site($jsst_new_site){
        $jsst_pluginname = plugin_basename(__FILE__);
        if(is_plugin_active_for_network($jsst_pluginname)){
            include_once 'includes/activation.php';
            switch_to_blog($jsst_new_site->blog_id);
            JSSTactivation::jssupportticket_activate();
            restore_current_blog();
        }
    }

    function jssupportticket_new_blog($jsst_blog_id, $jsst_user_id, $jsst_domain, $jsst_path, $jsst_site_id, $jsst_meta){
        $jsst_pluginname = plugin_basename(__FILE__);
        if(is_plugin_active_for_network($jsst_pluginname)){
            include_once 'includes/activation.php';
            switch_to_blog($jsst_blog_id);
            JSSTactivation::jssupportticket_activate();
            restore_current_blog();
        }
    }

    function jssupportticket_delete_site($jsst_tables){
        include_once 'includes/deactivation.php';
        $jsst_tablestodrop = JSSTdeactivation::jssupportticket_tables_to_drop();
        foreach($jsst_tablestodrop as $jsst_tablename){
            $jsst_tables[] = $jsst_tablename;
        }
        return $jsst_tables;
    }

    function jssupportticket_activation_redirect(){
        if (get_option('jssupportticket_do_activation_redirect')) {
            delete_option('jssupportticket_do_activation_redirect');
            // 1. Perform the safe redirect
            wp_safe_redirect( admin_url( 'admin.php?page=postinstallation&jstlay=wellcomepage' ) );

            // 2. Terminate the script immediately after
            exit;
        }
    }

    /**
     * Once per marker: rewrite configuration values the Configurations screen
     * cannot display into its own "off" value. Behaviour-neutral; see
     * JSSTconfigurationModel::normaliseChoiceValues().
     */
    function jsstNormaliseConfigChoices() {
        if (get_option('jsst_config_choices_normalised') === self::CONFIG_CHOICES_VERSION) {
            return;
        }
        if (!class_exists('JSSTconfigurationModel')) {
            JSSTincluder::getJSModel('configuration');
        }
        if (method_exists('JSSTconfigurationModel', 'normaliseChoiceValues')) {
            JSSTconfigurationModel::normaliseChoiceValues();
            update_option('jsst_config_choices_normalised', self::CONFIG_CHOICES_VERSION, false);
        }
    }

    /** See the init hook above. Runs once per version, then never again. */
    function jsstEnsureDeskSlugs() {
        /* Its own marker rather than the plugin version: this list changes
           when a desk destination is added, which is not every release, and
           tying it to the version would re-run the check on every update for
           no reason. Bump the constant when the list below grows. */
        if (get_option('jsst_desk_slugs_seeded') === self::DESK_SLUGS_VERSION) {
            return;
        }
        if (!class_exists('JSSTslugModel')) {
            JSSTincluder::getJSModel('slug');
        }
        $jsst_added = method_exists('JSSTslugModel', 'ensureDeskSlugs')
            ? JSSTslugModel::ensureDeskSlugs() : 0;
        update_option('jsst_desk_slugs_seeded', self::DESK_SLUGS_VERSION, false);
        if ($jsst_added) {
            /* Only when something was actually added: flushing rewrite rules is
               not free, and doing it on a request that changed nothing is the
               kind of thing that shows up as "the site is slow after updating". */
            flush_rewrite_rules(false);
        }
    }

    function jsst_handle_public_cronjob(){
        $jsst_action = JSSTrequest::getVar('jsstcron','get',null);
        if ($jsst_action) {
            switch ($jsst_action) {
                case 'ticketviaemail':
                    do_action('jssupporticket_ticketviaemail');
                    break;
                case 'updateticketstatus':
                    do_action('jssupporticket_updateticketstatus');
                    break;
            }
            exit();
        }
    }

    function jsjp_safe_style_css(){
        $jsst_styles[] = 'display';
        $jsst_styles[] = 'color';
        $jsst_styles[] = 'width';
        $jsst_styles[] = 'max-width';
        $jsst_styles[] = 'min-width';
        $jsst_styles[] = 'height';
        $jsst_styles[] = 'min-height';
        $jsst_styles[] = 'max-height';
        $jsst_styles[] = 'background-color';
        $jsst_styles[] = 'border';
        $jsst_styles[] = 'border-bottom';
        $jsst_styles[] = 'border-top';
        $jsst_styles[] = 'border-left';
        $jsst_styles[] = 'border-right';
        $jsst_styles[] = 'border-color';
        $jsst_styles[] = 'border-radius';
        $jsst_styles[] = 'padding';
        $jsst_styles[] = 'padding-top';
        $jsst_styles[] = 'padding-bottom';
        $jsst_styles[] = 'padding-left';
        $jsst_styles[] = 'padding-right';
        $jsst_styles[] = 'margin';
        $jsst_styles[] = 'margin-top';
        $jsst_styles[] = 'margin-bottom';
        $jsst_styles[] = 'margin-left';
        $jsst_styles[] = 'margin-right';
        $jsst_styles[] = 'background';
        $jsst_styles[] = 'font-weight';
        $jsst_styles[] = 'font-size';
        $jsst_styles[] = 'text-align';
        $jsst_styles[] = 'text-decoration';
        $jsst_styles[] = 'text-transform';
        $jsst_styles[] = 'line-height';
        $jsst_styles[] = 'visibility';
        $jsst_styles[] = 'cellspacing';
        $jsst_styles[] = 'data-id';
        $jsst_styles[] = 'cursor';
        $jsst_styles[] = 'vertical-align';
        $jsst_styles[] = 'float';
        $jsst_styles[] = 'position';
        $jsst_styles[] = 'left';
        $jsst_styles[] = 'right';
        $jsst_styles[] = 'bottom';
        $jsst_styles[] = 'top';
        $jsst_styles[] = 'z-index';
        $jsst_styles[] = 'overflow';
        return $jsst_styles;
    }

    function jsst_handle_search_form_data(){

        $jsst_isadmin = is_admin();
        $jsst_jstlay = '';
        if(isset($_REQUEST['jstlay'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData( wp_unslash( $_REQUEST['jstlay'] ?? '' ) ); // JSST_sanitizeData() function uses wordpress santize functions
        }elseif(isset($_REQUEST['page'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData($_REQUEST['page']); // JSST_sanitizeData() function uses wordpress santize functions
        }elseif(isset($_REQUEST['jshdlay'])){
            $jsst_jstlay = jssupportticket::JSST_sanitizeData($_REQUEST['jshdlay']); // JSST_sanitizeData() function uses wordpress santize functions
        }
        $jsst_callfrom = 3;
        if(isset($_REQUEST['JSST_form_search']) && $_REQUEST['JSST_form_search'] == 'JSST_SEARCH'){
            $jsst_callfrom = 1;
        }elseif(JSSTrequest::getVar('pagenum', 'get', null) != null){
            $jsst_callfrom = 2;
        }

        $jsst_setcookies = false;
        $jsst_ticket_search_cookie_data = '';
        $jsst_search_array = array();
        switch($jsst_jstlay){
            case 'tickets':
            case 'myticket':
            case 'ticket':
            case 'staffmyticket':
                if( in_array('agent',jssupportticket::$_active_addons) ){
                    $jsst_agent = JSSTincluder::getJSModel('agent')->isUserStaff();
                }else{
                    $jsst_agent = false;
                }
                if(is_admin() || $jsst_agent){
                    $jsst_search_userfields = JSSTincluder::getObjectClass('customfields')->adminFieldsForSearch(1);
                } else {
                    $jsst_search_userfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
                }
                if($jsst_callfrom == 1){
                    if(is_admin()){
                        $jsst_search_array = JSSTincluder::getJSModel('ticket')->getAdminTicketSearchFormData($jsst_search_userfields);
                    }else{
                        $jsst_search_array = JSSTincluder::getJSModel('ticket')->getFrontSideTicketSearchFormData($jsst_search_userfields);
                    }
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    $jsst_search_array = JSSTincluder::getJSModel('ticket')->getCookiesSavedSearchDataTicket($jsst_search_userfields);
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                JSSTincluder::getJSModel('ticket')->setSearchVariableForTicket($jsst_search_array,$jsst_search_userfields);
            break;
            case 'departments':
            case 'department':
                $jsst_deptname = (is_admin()) ? 'departmentname' : 'jsst-dept';
                if($jsst_callfrom == 1){
                    $jsst_search_array = JSSTincluder::getJSModel('department')->getAdminDepartmentSearchFormData();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_department'])){
                        $jsst_search_array['departmentname'] = $jsst_ticket_search_cookie_data['departmentname'];
                        $jsst_search_array['pagesize'] = $jsst_ticket_search_cookie_data['pagesize'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                // Departments
                jssupportticket::$_search['department']['departmentname'] = isset($jsst_search_array['departmentname']) ? $jsst_search_array['departmentname'] : null;
                jssupportticket::$_search['department']['pagesize'] = isset($jsst_search_array['pagesize']) ? $jsst_search_array['pagesize'] : null;
            break;
            case 'erasedatarequests':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_search_array = JSSTincluder::getJSModel('gdpr')->getAdminSearchFormDataGDPR();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_gdpr'])){
                        $jsst_search_array['email'] = $jsst_ticket_search_cookie_data['email'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                // gdpr
                jssupportticket::$_search['gdpr']['email'] = isset($jsst_search_array['email']) ? $jsst_search_array['email'] : null;
            break;
            // Blocked senders and their log are core, so the search that drives
            // both screens is handled here. It used to live on the add-on's
            // init hook, which a merged add-on no longer gets to keep.
            // (Roadmap 4.0-CORE-12)
            case 'banemail':
            case 'banemails':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_search_array = JSSTincluder::getJSModel('banemail')->getAdminSearchFormDataBanEmail();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_banemail'])){
                        $jsst_search_array['email'] = $jsst_ticket_search_cookie_data['email'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                jssupportticket::$_search['banemail']['email'] = isset($jsst_search_array['email']) ? $jsst_search_array['email'] : null;
            break;
            case 'banemaillog':
            case 'banemaillogs':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_search_array = JSSTincluder::getJSModel('banemaillog')->getAdminSearchFormDataBanEmailLog();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_banemaillog'])){
                        $jsst_search_array['loggeremail'] = $jsst_ticket_search_cookie_data['loggeremail'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                jssupportticket::$_search['banemail']['loggeremail'] = isset($jsst_search_array['loggeremail']) ? $jsst_search_array['loggeremail'] : null;
            break;
            case 'priorities':
            case 'priority':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_search_array = JSSTincluder::getJSModel('priority')->getAdminSearchFormDataPriority();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_priority'])){
                        $jsst_search_array['title'] = $jsst_ticket_search_cookie_data['title'];
                        $jsst_search_array['pagesize'] = $jsst_ticket_search_cookie_data['pagesize'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                // priority
                jssupportticket::$_search['priority']['title'] = isset($jsst_search_array['title']) ? $jsst_search_array['title'] : null;
                jssupportticket::$_search['priority']['pagesize'] = isset($jsst_search_array['pagesize']) ? $jsst_search_array['pagesize'] : null;
            break;
            case 'statuses':
            case 'status':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_search_array = JSSTincluder::getJSModel('status')->getAdminSearchFormDataStatus();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_status'])){
                        $jsst_search_array['title'] = $jsst_ticket_search_cookie_data['title'];
                        $jsst_search_array['pagesize'] = $jsst_ticket_search_cookie_data['pagesize'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                // status
                jssupportticket::$_search['status']['status'] = isset($jsst_search_array['title']) ? $jsst_search_array['title'] : null;
                jssupportticket::$_search['status']['pagesize'] = isset($jsst_search_array['pagesize']) ? $jsst_search_array['pagesize'] : null;
            break;
            case 'products':
            case 'product':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_search_array = JSSTincluder::getJSModel('product')->getAdminSearchFormDataProduct();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_product'])){
                        $jsst_search_array['title'] = $jsst_ticket_search_cookie_data['title'];
                        $jsst_search_array['pagesize'] = $jsst_ticket_search_cookie_data['pagesize'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                // product
                jssupportticket::$_search['product']['product'] = isset($jsst_search_array['title']) ? $jsst_search_array['title'] : null;
                jssupportticket::$_search['product']['pagesize'] = isset($jsst_search_array['pagesize']) ? $jsst_search_array['pagesize'] : null;
            break;
            case 'slug':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_search_array = JSSTincluder::getJSModel('slug')->getAdminSearchFormDataSlug();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_slug'])){
                        $jsst_search_array['slug'] = $jsst_ticket_search_cookie_data['slug'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                // system emails
                jssupportticket::$_search['slug']['slug'] = isset($jsst_search_array['slug']) ? $jsst_search_array['slug'] : null;
            break;
            case 'emails':
            case 'email':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_search_array = JSSTincluder::getJSModel('email')->getAdminSearchFormDataEmails();
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if($jsst_ticket_search_cookie_data != '' && isset($jsst_ticket_search_cookie_data['search_from_email'])){
                        $jsst_search_array['email'] = $jsst_ticket_search_cookie_data['email'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                // system emails
                jssupportticket::$_search['email']['email'] = isset($jsst_search_array['email']) ? $jsst_search_array['email'] : null;
            break;
            case 'departmentreport':
            case 'userreport':
            case 'staffreport':
            case 'departmentdetailreport':
            case 'userdetailreport':
            case 'stafftimereport':
                if($jsst_callfrom == 1 && is_admin()){
                    $jsst_nonce = JSSTrequest::getVar('_wpnonce');
                    if (! wp_verify_nonce( $jsst_nonce, 'reports') ) {
                        die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
                    }                    
                    $jsst_search_array['date_start'] = JSSTrequest::getVar('date_start');
                    $jsst_search_array['date_end'] = JSSTrequest::getVar('date_end');
                    $jsst_search_array['uid'] = JSSTrequest::getVar('uid');
                    $jsst_search_array['search_from_reports'] = 1;
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2 && is_admin()){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if(!empty($jsst_ticket_search_cookie_data) && isset($jsst_ticket_search_cookie_data['search_from_reports'])){
                        $jsst_search_array['date_start'] = $jsst_ticket_search_cookie_data['date_start'];
                        $jsst_search_array['date_end'] = $jsst_ticket_search_cookie_data['date_end'];
                        $jsst_search_array['uid'] = $jsst_ticket_search_cookie_data['uid'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                jssupportticket::$_search['report']['date_start'] = isset($jsst_search_array['date_start']) ? $jsst_search_array['date_start'] : null;
                jssupportticket::$_search['report']['date_end'] = isset($jsst_search_array['date_end']) ? $jsst_search_array['date_end'] : null;
                jssupportticket::$_search['report']['uid'] = isset($jsst_search_array['uid']) ? $jsst_search_array['uid'] : null;
            break;
            case 'staffreports':
                if($jsst_callfrom == 1){
                    $jsst_search_array['jsst-date-start'] = JSSTrequest::getVar('jsst-date-start');
                    $jsst_search_array['jsst-date-end'] = JSSTrequest::getVar('jsst-date-end');
                    $jsst_search_array['search_from_reports_staff'] = 1;
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if(!empty($jsst_ticket_search_cookie_data) && isset($jsst_ticket_search_cookie_data['search_from_reports_staff'])){
                        $jsst_search_array['jsst-date-start'] = $jsst_ticket_search_cookie_data['jsst-date-start'];
                        $jsst_search_array['jsst-date-end'] = $jsst_ticket_search_cookie_data['jsst-date-end'];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                jssupportticket::$_search['report']['jsst-date-start'] = isset($jsst_search_array['jsst-date-start']) ? $jsst_search_array['jsst-date-start'] : null;
                jssupportticket::$_search['report']['jsst-date-end'] = isset($jsst_search_array['jsst-date-end']) ? $jsst_search_array['jsst-date-end'] : null;
            break;
            case 'admin_staffdetailreport':
            case 'staffdetailreport':
                $jsst_start_date = is_admin() ? 'date_start' : 'jsst-date-start';
                $jsst_end_date = is_admin() ? 'date_end' : 'jsst-date-end';
                if($jsst_callfrom == 1){
                    $jsst_nonce = JSSTrequest::getVar('_wpnonce');
                    if (! wp_verify_nonce( $jsst_nonce, 'staff-detail-report') ) {
                        die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
                    }        
                    $jsst_search_array[$jsst_start_date] = JSSTrequest::getVar($jsst_start_date);
                    $jsst_search_array[$jsst_end_date] = JSSTrequest::getVar($jsst_end_date);
                    $jsst_search_array['search_from_reports_detail'] = 1;
                    $jsst_setcookies = true;
                }elseif($jsst_callfrom == 2){
                    if(isset($_COOKIE['jsst_ticket_search_data'])){
                        $jsst_ticket_search_cookie_data = jssupportticket::JSST_sanitizeData($_COOKIE['jsst_ticket_search_data']); // JSST_sanitizeData() function uses wordpress santize functions
                        $jsst_ticket_search_cookie_data = json_decode( jssupportticketphplib::JSST_safe_decoding($jsst_ticket_search_cookie_data) , true );
                    }
                    if(!empty($jsst_ticket_search_cookie_data) && isset($jsst_ticket_search_cookie_data['search_from_reports_detail'])){
                        $jsst_search_array[$jsst_start_date] = $jsst_ticket_search_cookie_data[$jsst_start_date];
                        $jsst_search_array[$jsst_end_date] = $jsst_ticket_search_cookie_data[$jsst_end_date];
                    }
                }else{
                    jssupportticket::removeusersearchcookies();
                }
                jssupportticket::$_search['report'][$jsst_start_date] = isset($jsst_search_array[$jsst_start_date]) ? $jsst_search_array[$jsst_start_date] : null;
                jssupportticket::$_search['report'][$jsst_end_date] = isset($jsst_search_array[$jsst_end_date]) ? $jsst_search_array[$jsst_end_date] : null;
            break;
            case 'ticketdetail':
                $jsst_ticketid = JSSTrequest::getVar('jssupportticketid');
                if (in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) { //staff
                    if(current_user_can('jsst_support_ticket')){
                        $jsst_timecookies['ticket_time_start'][$jsst_ticketid] = gmdate("Y-m-d H:i:s");
                    }else{
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('ticket')->validateTicketDetailForStaff($jsst_ticketid);
                        if (jssupportticket::$jsst_data['permission_granted']) { // validation passed
                            if(in_array('timetracking', jssupportticket::$_active_addons)){
                                $jsst_timecookies['ticket_time_start'][$jsst_ticketid] = gmdate("Y-m-d H:i:s");
                            }
                        }
                    }
                } else { // user
                    if(current_user_can('jsst_support_ticket') || current_user_can('jsst_support_ticket_tickets')){
                        if(in_array('timetracking', jssupportticket::$_active_addons)){
                            $jsst_timecookies['ticket_time_start'][$jsst_ticketid] = gmdate("Y-m-d H:i:s");
                        }
                    }
                }
                if(isset($jsst_timecookies['ticket_time_start'][$jsst_ticketid])){
                    $jsst_user_id = JSSTincluder::getObjectClass('user')->uid();
                    $jsst_val = 'ticket_time_start_'.$jsst_ticketid.'_'.$jsst_user_id;
                    set_transient($jsst_val, $jsst_timecookies['ticket_time_start'][$jsst_ticketid], DAY_IN_SECONDS);
                    if ( SITECOOKIEPATH != COOKIEPATH ){
                        jssupportticketphplib::JSST_setcookie('jshelpdesk-timetack' , $jsst_timecookies['ticket_time_start'][$jsst_ticketid] , 0, SITECOOKIEPATH);
                    }
                }
            break;
        }

        if($jsst_setcookies){
            jssupportticket::setusersearchcookies($jsst_setcookies,$jsst_search_array);
        }
    }

    /**
     * Reconcile roles and capabilities when the stored role version is behind.
     * (Roadmap 3.2-CORE-01)
     */
    function jsst_reconcile_roles() {
        include_once JSST_PLUGIN_PATH . 'includes/roles.php';
        JSSTroles::reconcile();
    }

    /**
     * Add any missing FULLTEXT index on the instant-resolve source tables and
     * record which sources the search can use. One option read once the site is
     * up to date; the work itself runs when the plugin version or the set of
     * active add-ons changes, because that is when a source table can appear
     * (the knowledgebase add-on activated), disappear, or arrive without its
     * indexes. Keyed on the version alone, a source added by an add-on
     * activated later stayed unsearchable until the next plugin update.
     */
    function jsst_repair_suggestion_indexes() {
        $jsst_addons = (array) jssupportticket::$_active_addons;
        sort($jsst_addons);
        $jsst_version = (string) jssupportticket::$_currentversion . '|' . implode(',', $jsst_addons);
        if (get_option('jsst_ir_index_repair') === $jsst_version) {
            return;
        }
        // Claimed before the work, not after: an admin page load and the
        // heartbeat arrive together often enough that both would otherwise
        // start building the same index. A run that fails leaves the sources
        // skipped exactly as they were, and is retried on the next version.
        update_option('jsst_ir_index_repair', $jsst_version, false);
        JSSTincluder::getJSModel('ticket')->repairSuggestionIndexes();
    }

    function jsst_handle_delete_cookies(){

        if(isset($_COOKIE['jsst_addon_return_data'])){
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , '' , time() - 3600, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , '' , time() - 3600, SITECOOKIEPATH);
            }
        }

        if(isset($_COOKIE['jsst_addon_install_data'])){
            jssupportticketphplib::JSST_setcookie('jsst_addon_install_data' , '' , time() - 3600);
        }
    }

    public static function removeusersearchcookies(){
        if(isset($_COOKIE['jsst_ticket_search_data'])){
            jssupportticketphplib::JSST_setcookie('jsst_ticket_search_data' , '' , time() - 3600 , COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('jsst_ticket_search_data' , '' , time() - 3600 , SITECOOKIEPATH);
            }
        }
    }

    public static function setusersearchcookies($jsst_cookiesval, $jsst_search_array){
        if(!$jsst_cookiesval)
            return false;
        $jsst_data = wp_json_encode( $jsst_search_array );
        $jsst_data = jssupportticketphplib::JSST_safe_encoding($jsst_data);
        jssupportticketphplib::JSST_setcookie("jsst_ticket_search_data" , $jsst_data , 0 , COOKIEPATH);
        if ( SITECOOKIEPATH != COOKIEPATH ){
            jssupportticketphplib::JSST_setcookie('jsst_ticket_search_data' , $jsst_data , 0 , SITECOOKIEPATH);
        }
    }

    function jshd_delete_expire_session_data(){
        jssupportticket::$_db->query( 
            jssupportticket::$_db->prepare( 
                "DELETE FROM " . jssupportticket::$_db->prefix . "js_ticket_jshdsessiondata WHERE sessionexpire < %d", 
                time() 
            ) 
        );
    }


    /**
     * Tell everybody the event concerns. (Roadmap 4.5-UX-02)
     */
    function jsst_notify($jsst_envelope) {
        if (class_exists('JSSTnotifications')) {
            JSSTnotifications::onEvent($jsst_envelope);
        }
    }

    /**
     * Send the digests that are due, and clear out what has been read.
     * (Roadmap 4.5-UX-02)
     */
    function jsst_run_notification_digests() {
        if (class_exists('JSSTnotifications')) {
            JSSTnotifications::runDigests();
            JSSTnotifications::prune();
        }
    }



    /*
     * Update Ticket status every day schedule in the cron job
     */

    function updateticketstatus() {
        JSSTincluder::getJSModel('ticket')->updateTicketStatusCron();
        if(in_array('overdue', jssupportticket::$_active_addons)){ // markticket overdue if duedate is passed.
            //JSSTincluder::getJSModel('overdue')->markTicketOverdueCron(); //old code may need to remove
            JSSTincluder::getJSModel('overdue')->updateTicketStatusToOverDueCron();
        }
    }

    /*
     * Email Piping every hourly schedule in the cron job
     */

     function printTicket() {
        $jsst_layout = JSSTrequest::getVar('jstlay');
        if ($jsst_layout == 'printticket') {
            $jsst_ticketid = JSSTrequest::getVar('jssupportticketid');
            if(in_array('agent', jssupportticket::$_active_addons)){
                jssupportticket::$jsst_data['user_staff'] = JSSTincluder::getJSModel('agent')->isUserStaff();
            }else{
                jssupportticket::$jsst_data['user_staff'] = false;
            }

            JSSTincluder::getJSModel('ticket')->getTicketForDetail($jsst_ticketid);
            jssupportticket::addStyleSheets();
            jssupportticket::jsst_register_plugin_styles();
            jssupportticket::$jsst_data['print'] = 1; //print flag to handle appearnce
            JSSTincluder::include_file('ticketdetail', 'ticket');
            exit();
        }
    }

    function jssupportticket_deactivate($jsst_network_wide = false) {
        include_once 'includes/deactivation.php';
        if(function_exists('is_multisite') && is_multisite() && $jsst_network_wide){
            global $wpdb;
            $jsst_blogs = jssupportticket::$_db->get_col("SELECT blog_id FROM " . jssupportticket::$_db->base_prefix . "blogs");
            foreach($jsst_blogs as $jsst_blog_id){
                switch_to_blog( $jsst_blog_id );
                JSSTdeactivation::jssupportticket_deactivate();
                restore_current_blog();
            }
        }else{
            JSSTdeactivation::jssupportticket_deactivate();
        }
    }

    function jsst_login_redirect( $jsst_redirect_to, $jsst_request, $jsst_user ) {
        //is there a user to check?
        global $user;
        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            //check for admins
            if ( in_array( 'administrator', $user->roles ) ) {
                // redirect them to the default place
                return $jsst_redirect_to;
            } else {
                $jsst_redirecturl = JSSTrequest::getVar('redirect_to');
                if(jssupportticket::$_config['login_redirect'] == 1 && $jsst_redirecturl == null){
                    $jsst_pageid = jssupportticket::getPageid();
                    $jsst_link = "index.php?page_id=".$jsst_pageid;
                    return $jsst_link;
                }elseif($jsst_redirecturl != null){
                    return $jsst_redirecturl;
                }else{
                    return home_url();
                }
            }
        } else {
            return $jsst_redirect_to;
        }
    }

    function jsst_resetnotificationvalues(){ // config and key values empty
        // $jsst_query = "UPDATE `".jssupportticket::$_db->prefix."js_ticket_config` SET configvalue = '' WHERE configfor = 'firebase'";
        // $jsst_value = jssupportticket::$_db->get_var($jsst_query);
    }

    /**
     * Forget every cached answer about the shape of the database.
     * (Roadmap 6.5-ECO-01)
     *
     * `JSSTcannedlibrary::governed()` and `JSSTkbgovernance::governed()` each
     * cache a `SHOW COLUMNS` answer for an hour, which is right on a running
     * desk and wrong in the one window where it matters: installing, activating
     * or deactivating a plugin is exactly when a table's shape changes, and an
     * hour of remembering the old answer is an hour of SQL errors in the log on
     * every page load.
     *
     * Seen on a desk migrating from the forty stand-alone add-ons to the nine
     * bundles: reinstalling an old add-on recreated its table at the old
     * schema, the cached "yes, this desk is governed" outlived it, and every
     * admin page then asked for a column that was no longer there.
     */
    public static function forgetSchemaCache() {
        delete_transient('jsst_canned_governed');
        delete_transient('jsst_kb_governed');
    }

    function registeractions() {
        /* Any of the three moments a table's shape can change under us. */
        add_action('activated_plugin', array(__CLASS__, 'forgetSchemaCache'));
        add_action('deactivated_plugin', array(__CLASS__, 'forgetSchemaCache'));
        add_action('upgrader_process_complete', array(__CLASS__, 'forgetSchemaCache'));
        // Deleting a customer must take their internal notes with them. The
        // stand-alone Private Note add-on adds this join itself, so core only
        // does it when core is the side serving notes — never both.
        // (Roadmap 4.0-CORE-02, 4.0-CORE-19)
        if (JSSTmergedaddon::coreOwns('note')) {
            add_action('jsst_addon_deletequery_for_user', array($this, 'jsst_delete_user_ticket_related_notes'));
        }
        // Tag links are core-only, so this join always comes from here.
        // (Roadmap 4.0-CORE-17)
        add_action('jsst_addon_deletequery_for_user', array($this, 'jsst_delete_user_ticket_tags'));
        // The canned-response library screens keep their filter across pages via
        // a saved search. The add-on hooks its own copy of this handler, so core
        // only registers when core is serving the feature.
        // (Roadmap 4.0-CORE-03, 4.0-CORE-19)
        if (JSSTmergedaddon::coreOwns('cannedresponses')) {
            add_action('init', array($this, 'jsst_handle_cannedresponse_search_form_data'));
            add_action('admin_init', array($this, 'jsst_handle_cannedresponse_search_form_data'));
        }
        // Topic name on ticket queries, and the topic list's saved search. The
        // Help Topic add-on registers the same five hooks from its own main file,
        // so core registers only when core is serving the feature — otherwise the
        // same join would be added twice and every ticket query would fail.
        // (Roadmap 4.0-CORE-06, 4.0-CORE-19)
        // Cc/Bcc headers on outgoing mail, and the CC list's saved search. The
        // Email CC add-on filters on its own, so core only registers when core is
        // serving the feature — otherwise every notification would be copied
        // twice. (Roadmap 4.0-CORE-08, 4.0-CORE-19)
        // Retention cleanup. Only when core owns it: the add-on schedules the same
        // hook, and two schedulers would delete two batches per tick.
        // (Roadmap 4.0-CORE-13, 4.0-CORE-19)
        if (JSSTmergedaddon::coreOwns('autocleanup')) {
            add_filter('cron_schedules', array($this, 'jsst_autocleanup_intervals'));
            add_action('init', array($this, 'jsst_autocleanup_schedule'));
            add_action('jsst_daily_autocleanup_cron', array($this, 'jsst_autocleanup_run'));
        }
        /* Email CC is an add-on feature again. The add-on hooks
           jsst_emailcc_send_email_to_cc and jsst_emailcc_send_smtp_email_to_cc
           from its own plugin file, so core registers nothing for it. */
        if (JSSTmergedaddon::coreOwns('helptopic')) {
            add_action('jsst_get_mail_table_record_query', array($this, 'jsst_helptopic_list_query'));
            add_action('jsst_addon_staff_admin_tickets', array($this, 'jsst_helptopic_list_query'));
            add_action('jsst_addon_staff_my_tickets', array($this, 'jsst_helptopic_list_query'));
            add_action('jsst_addon_user_my_tickets', array($this, 'jsst_helptopic_list_query'));
            add_action('jsst_ticket_detail_query', array($this, 'jsst_helptopic_detail_query'));
            add_action('init', array($this, 'jsst_handle_helptopic_search_form_data'));
            add_action('admin_init', array($this, 'jsst_handle_helptopic_search_form_data'));
        }
        //Extra Hooks
        //add_filter( 'login_redirect', array($this,'jsst_login_redirect'), 10, 3 );
        //Ticket Action Hooks
        add_action('jsst-ticketcreate', array($this, 'ticketcreate'), 10, 1);
        add_action('jsst-ticketreply', array($this, 'ticketreply'), 10, 1);
        add_action('jsst-ticketclose', array($this, 'ticketclose'), 10, 1);
        add_action('jsst-ticketdelete', array($this, 'ticketdelete'), 10, 1);
        add_action('jsst-ticketbeforelisting', array($this, 'ticketbeforelisting'), 10, 1);
        add_action('jsst-ticketbeforeview', array($this, 'ticketbeforeview'), 10, 1);
        //Email Hooks
        add_action('jsst-beforeemailticketcreate', array($this, 'beforeemailticketcreate'), 10, 4);
        add_action('jsst-beforeemailticketreply', array($this, 'beforeemailticketreply'), 10, 4);
        add_action('jsst-beforeemailticketclose', array($this, 'beforeemailticketclose'), 10, 4);
        add_action('jsst-beforeemailticketdelete', array($this, 'beforeemailticketdelete'), 10, 4);
    }

    /**
     * The weekly and monthly intervals retention can be scheduled on.
     * (Roadmap 4.0-CORE-13)
     */
    function jsst_autocleanup_intervals($jsst_schedules) {
        /* Translated only once translations may load - see
           JSSTjobs::addInterval(). (2 Oct 2026) */
        $jsst_now = did_action('after_setup_theme');
        if (!isset($jsst_schedules['weekly'])) {
            $jsst_schedules['weekly'] = array('interval' => 604800, 'display' => $jsst_now ? esc_html(__('Once Weekly', 'js-support-ticket')) : 'Once Weekly');
        }
        if (!isset($jsst_schedules['monthly'])) {
            $jsst_schedules['monthly'] = array('interval' => 2635200, 'display' => $jsst_now ? esc_html(__('Once Monthly', 'js-support-ticket')) : 'Once Monthly');
        }
        return $jsst_schedules;
    }

    /**
     * Keep the retention schedule in step with the configured frequency, and
     * unschedule it entirely when both intervals are Never — a cron that wakes up
     * to do nothing is a cron nobody notices is misconfigured.
     * (Roadmap 4.0-CORE-13)
     */
    function jsst_autocleanup_schedule() {
        $jsst_wanted = isset(jssupportticket::$_config['autocleanup_cron_frequency']) ? jssupportticket::$_config['autocleanup_cron_frequency'] : 'daily';
        if (!in_array($jsst_wanted, array('daily', 'weekly', 'monthly'), true)) {
            $jsst_wanted = 'daily';
        }
        // Through the includer, not the class name directly: this runs on init,
        // before anything else has loaded the module's model file.
        $jsst_cleanup = JSSTincluder::getJSModel('autocleanup');
        $jsst_enabled = ($jsst_cleanup->ticketInterval() > 0 || $jsst_cleanup->attachmentInterval() > 0);
        $jsst_scheduled = wp_next_scheduled('jsst_daily_autocleanup_cron');

        if (!$jsst_enabled) {
            if ($jsst_scheduled) {
                wp_clear_scheduled_hook('jsst_daily_autocleanup_cron');
            }
            return;
        }
        if ($jsst_scheduled && wp_get_schedule('jsst_daily_autocleanup_cron') !== $jsst_wanted) {
            wp_clear_scheduled_hook('jsst_daily_autocleanup_cron');
            $jsst_scheduled = false;
        }
        if (!$jsst_scheduled) {
            wp_schedule_event(time(), $jsst_wanted, 'jsst_daily_autocleanup_cron');
        }
    }

    /**
     * The scheduled retention run. (Roadmap 4.0-CORE-13)
     */
    function jsst_autocleanup_run() {
        JSSTincluder::getJSModel('autocleanup')->executeCleanupRoutines();
    }

    /**
     * Topic name on ticket list and mail queries. (Roadmap 4.0-CORE-06)
     */
    function jsst_helptopic_list_query() {
        JSSTincluder::getJSModel('helptopic')->ticketListQuery();
    }

    /**
     * Topic name on the ticket detail query. (Roadmap 4.0-CORE-06)
     */
    function jsst_helptopic_detail_query() {
        JSSTincluder::getJSModel('helptopic')->ticketDetailQuery();
    }

    /**
     * Read the topic list filter from the submitted form or the saved search
     * cookie. (Roadmap 4.0-CORE-06)
     */
    function jsst_handle_helptopic_search_form_data() {
        JSSTincluder::getJSModel('helptopic')->handleSearchFormData();
    }

    /**
     * Read the canned-response library filter from the submitted form or the
     * saved search cookie. (Roadmap 4.0-CORE-03)
     */
    function jsst_handle_cannedresponse_search_form_data() {
        JSSTincluder::getJSModel('cannedresponses')->handleSearchFormData();
    }

    /**
     * Add the notes table to the "delete everything belonging to this customer"
     * query. Identical to what the Private Note add-on contributes, so the
     * result is the same whichever side owns notes. (Roadmap 4.0-CORE-02)
     */
    function jsst_delete_user_ticket_related_notes() {
        jssupportticket::$_addon_query['select'] .= " ,note";
        jssupportticket::$_addon_query['join'] .= " LEFT JOIN `". jssupportticket::$_db->prefix ."js_ticket_notes` AS note ON note.ticketid = ticket.id ";
    }

    /**
     * Tag links belong to the ticket, so they have to leave with it when a
     * customer's records are erased. (Roadmap 4.0-CORE-17)
     */
    function jsst_delete_user_ticket_tags() {
        jssupportticket::$_addon_query['select'] .= " ,tickettagmap";
        jssupportticket::$_addon_query['join'] .= " LEFT JOIN `". jssupportticket::$_db->prefix ."js_ticket_ticket_tags` AS tickettagmap ON tickettagmap.ticketid = ticket.id ";
    }

    //Funtions for Ticket Hooks
    function ticketcreate($jsst_ticketobject) {
        return $jsst_ticketobject;
    }

    function ticketreply($jsst_ticketobject) {
        return $jsst_ticketobject;
    }

    function ticketclose($jsst_ticketobject) {
        return $jsst_ticketobject;
    }

    function ticketdelete($jsst_ticketobject) {
        return $jsst_ticketobject;
    }

    function ticketbeforelisting($jsst_ticketobject) {
        return $jsst_ticketobject;
    }

    function ticketbeforeview($jsst_ticketobject) {
        return $jsst_ticketobject;
    }

    //Funtion for Email Hooks
    function beforeemailticketcreate($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail) {
        return;
    }

    function beforeemailticketdelete($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail) {
        return;
    }

    function beforeemailticketreply($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail) {
        return;
    }

    function beforeemailticketclose($jsst_recevierEmail, $jsst_subject, $jsst_body, $jsst_senderEmail) {
        return;
    }

    /*
     * Include the required files
     */

    function jsstLoadWpCoreFiles() {
        add_action('jssupportticket_load_wp_plugin_file', array($this,'jssupportticket_load_wp_plugin_file') );
        add_action('jssupportticket_load_wp_admin_file', array($this,'jssupportticket_load_wp_admin_file') );
        add_action('jssupportticket_load_wp_file', array($this,'jssupportticket_load_wp_file') );
        add_action('jssupportticket_load_wp_pcl_zip', array($this,'jssupportticket_load_wp_pcl_zip') );
        add_action('jssupportticket_load_wp_upgrader', array($this,'jssupportticket_load_wp_upgrader') );
        add_action('jssupportticket_load_wp_ajax_upgrader_skin', array($this,'jssupportticket_load_wp_ajax_upgrader_skin') );
        add_action('jssupportticket_load_wp_plugin_upgrader', array($this,'jssupportticket_load_wp_plugin_upgrader') );
        add_action('jssupportticket_load_wp_translation_install', array($this,'jssupportticket_load_wp_translation_install') );
        add_action('jssupportticket_load_phpass', array($this,'jssupportticket_load_phpass') );
    }
    function includes() {
        if (is_admin()) {
            include_once 'includes/jssupportticketadmin.php';
            include_once __DIR__ . '/includes/classes/jsstadminreviewbox.php';
        }
        if(in_array('widgets', jssupportticket::$_active_addons)){
            include_once 'includes/pageswidget.php';
        }

        include_once 'includes/captcha.php';
        include_once 'includes/recaptchalib.php';
        // Decides whether a self-healing table actually needs repairing. Loaded
        // before anything that owns one, and unconditionally, because every
        // ensureSchema() in the plugin asks it first.
        include_once __DIR__ . '/includes/classes/schemaguard.php';
        // Compatibility layer for capabilities absorbed into the free core.
        // Loaded unconditionally because its checks decide whether core or a
        // still-active legacy add-on owns a feature. (Roadmap 4.0-CORE-19)
        include_once __DIR__ . '/includes/classes/mergedaddon.php';
        // Human verification and submission rate limits. Both are called
        // statically from models, templates and the settings screen, so they are
        // loaded here rather than through getObjectClass(). (Roadmap 4.0-SEC-01)
        include_once __DIR__ . '/includes/classes/verification.php';
        include_once __DIR__ . '/includes/classes/ratelimit.php';
        // Shared ticket-action helpers. Called statically from core models and
        // templates, and deliberately not on JSSTactionsModel — that class
        // belongs to the legacy add-on on sites that still run it.
        // (Roadmap 4.0-CORE-05, 4.0-CORE-19)
        include_once __DIR__ . '/includes/classes/ticketaction.php';
        // Ticket links that outlive the request that wrote them: a link stored in
        // a reply has to resolve to the admin screen or the front-end screen
        // depending on who is reading it. Called statically from the merge
        // add-on's model and from both ticket detail templates.
        // (Roadmap 4.0-CORE-01)
        include_once __DIR__ . '/includes/classes/ticketlink.php';
        // Queue search, the queue tabs and saved views. Called statically from
        // the ticket model, the ticket controller and the list template, none of
        // which owns it. (Roadmap 4.0-CORE-18)
        include_once __DIR__ . '/includes/classes/queue.php';
        // Attachment storage, validation and serving protection. Called
        // statically from the upload path, the attachment model and activation.
        // (Roadmap 4.0-SEC-03)
        include_once __DIR__ . '/includes/classes/attachmentguard.php';
        // Email delivery diagnostics. Called from the email model on every send
        // and from the Email Health screen. (Roadmap 4.0-OPS-01)
        include_once __DIR__ . '/includes/classes/mailhealth.php';
        // What the last mailbox collection did. Written by the piping add-on
        // while it runs, read by the Email Health screen. Loaded here rather
        // than by the add-on so the record survives the add-on being switched
        // off. (Roadmap 4.0-OPS-01)
        include_once __DIR__ . '/includes/classes/pipinglog.php';
        // The activation checklist. Read by the screen and by the side menu.
        // (Roadmap 4.0-UX-01)
        include_once __DIR__ . '/includes/classes/setup.php';
        // WordPress privacy tools: export, erase and the retention statement.
        // (Roadmap 4.0-SEC-02)
        include_once __DIR__ . '/includes/classes/privacy.php';
        /* The v4.5 application layer, in dependency order.

           The capability service is the one place that answers "may this
           happen?", for every workspace and for any actor — not only the one
           signed in. Loaded before anything that could ask it, and outside
           is_admin() because the front-end portal, the REST surface and every
           automation ask it as often as the admin screens do.
           (Roadmap 4.5-ARCH-01)

           Then the events, which the commands emit; then the commands and
           queries themselves, which are what both workspaces are built on.
           (Roadmap 4.5-ARCH-02, 4.5-ARCH-03) */
        include_once __DIR__ . '/includes/classes/capability.php';
        include_once __DIR__ . '/includes/classes/events.php';
        include_once __DIR__ . '/includes/classes/ticketservice.php';
        include_once __DIR__ . '/includes/classes/ticketquery.php';
        /* Who the customer works for, as a record rather than as the part of
           their address after the @. v4.5 grouped the Customers screen by
           domain and said in the code that a real company - with its own name,
           contacts, contract and SLA - was a schema change for a later release;
           this is it. Loaded beside the queries because the capability service
           asks it whether an actor is a supervisor, and that question is asked
           on the front-end portal as often as in wp-admin. (Roadmap 5.5-COM-06) */
        include_once __DIR__ . '/includes/classes/companies.php';
        /* Retiring the MailChimp add-on: the consent it should always have
           recorded, the event anything can subscribe to, and the two recipes
           that carry it onward. Loaded outside is_admin() because the checkbox
           it answers is on the front-end registration form.
           (Roadmap 5.5-SEC-02) */
        include_once __DIR__ . '/includes/classes/consent.php';
        /* The agent desk itself, written down once: what the tabs are, what the
           scopes are, which buttons a ticket offers, which keys do what - and
           the parity matrix that reads both shells and reports which of them
           still has its own copy of any of it. Loaded outside is_admin()
           because the front-end desk is half of what it describes.
           (Roadmap 4.5-FE-01) */
        include_once __DIR__ . '/includes/classes/workspace.php';
        /* The queue engine: the filter set, the columns, the cached tab counts
           and the one call that assembles a whole queue. Above the desk rather
           than inside either shell, because the two queues in this product
           diverged by each assembling their own. (Roadmap 4.5-UX-01) */
        include_once __DIR__ . '/includes/classes/queueengine.php';
        /* Its hooks were written and never registered, so the cached tab
           counts were never invalidated by anything that happened to a ticket
           and lagged the list by up to COUNTS_TTL - most visibly "Open 4" over
           an empty list after tickets were deleted. JSSTevents is loaded above. */
        JSSTqueueengine::registerHooks();
        /* The shape of the desk: seven destinations, the home screen behind the
           first of them, and the navigation both shells render. Loaded outside
           is_admin() for the same reason the workspace is - half of what it
           describes is the portal. (Roadmap 4.5-FE-02) */
        include_once __DIR__ . '/includes/classes/navigation.php';
        /* Being told what happened: one list, fed from the event stream, with
           preferences, quiet hours and digests. The list itself is free and
           cannot be switched off - a list that has been told not to mention
           things lies about what happened - while browser push, which is a
           channel rather than the record, ships with the notification addon
           and is offered only where that is running. (Roadmap 4.5-UX-02) */
        include_once __DIR__ . '/includes/classes/notifications.php';
        /* Retiring the Internal Mail add-on: what is in its mailbox, what can
           be moved onto the tickets it is about, and an archive of everything.
           Admin only - it is one screen and it reads another add-on's table.
           (Roadmap 4.5-FE-12) */
        if (is_admin()) {
            include_once __DIR__ . '/includes/classes/internalmail.php';
        }
        /* The portal as blocks, each one a thin wrapper around the shortcode
           that already renders it. Loaded outside is_admin() because a block
           renders on the front end. (Roadmap 4.0-UX-07) */
        include_once __DIR__ . '/includes/classes/blocks.php';
        /* And the retirement of the sidebar widgets those blocks replace.
           Admin only: it is a report on one screen. (Roadmap 4.0-UX-07) */
        if (is_admin()) {
            include_once __DIR__ . '/includes/classes/widgetretirement.php';
            /* And its row on the Plugins screen. The report lives on one screen;
               this is the line next to the Deactivate link, which is where the
               decision is actually taken. (Roadmap 4.0-UX-07) */
            JSSTwidgetretirement::register();
        }
        /* What used to be the v5.0 layer now arrives as addons, and core
           carries none of it. Service levels, automation, the platform API and
           the rest each ship as their own plugin the way every other paid
           capability in this product does - installed, activated and updated
           on their own - and each one registers its own hooks from its own
           bootstrap. Everything in core that asks them a question asks
           class_exists() first, so a desk running none of them behaves exactly
           as it did before they existed.
           (Roadmap 5.0-SLA-01, 5.0-AUT-01, 5.0-API-01) */
        /* What this product offers a developer: every action and filter it
           fires, read out of its own source, beside the event contract and the
           schema history. Admin only - it reads several hundred files and is
           one screen. (Roadmap 4.0-DATA-03) */
        if (is_admin()) {
            include_once __DIR__ . '/includes/classes/hooks.php';
        }
        /* And the authorization matrix that reads this plugin's own source and
           reports, action by action, what checks each one actually performs.
           Admin only for the same reason, and because it reads files.
           (Roadmap 5.0-SEC-01) */
        if (is_admin()) {
            include_once __DIR__ . '/includes/classes/authmatrix.php';
        }
        /* The one module bootstrap: activation, schema, multisite and the
           settings namespace, written once instead of once per add-on.
           (Roadmap 4.5-ARCH-05) */
        include_once __DIR__ . '/includes/classes/module.php';
        /* What the plans are and what separates them. Loaded here rather than
           beside JSSTpro in the constructor because nothing about entitlement
           depends on it — a plan is how a licence is described, never what it
           permits, which is the whole point of the packaging change.
           (Roadmap 4.5-PRO-03) */
        include_once __DIR__ . '/includes/classes/plans.php';
        // Effective agent access, for auditing. (Roadmap 4.0-SEC-04)
        include_once __DIR__ . '/includes/classes/agentaccess.php';
        /* Why one person may or may not do one thing. Admin only: it is one
           screen, and it enumerates who holds what. (Roadmap 4.5-ARCH-04) */
        if (is_admin()) {
            include_once __DIR__ . '/includes/classes/permissioninspector.php';
        }
        // Which left menu each admin screen gets. (Roadmap 4.0-SEC-04)
        include_once __DIR__ . '/includes/classes/jsstsidemenu.php';
        // Diagnostics and the redacted debug bundle. (Roadmap 4.0-OPS-02)
        include_once __DIR__ . '/includes/classes/systemstatus.php';
        // Reply drafts, saved as an agent types. (Roadmap 4.0-UX-04)
        include_once __DIR__ . '/includes/classes/draft.php';
        // Who else is on this ticket, and whether a reply landed while this
        // agent was writing. (Roadmap 4.0-UX-05)
        include_once __DIR__ . '/includes/classes/presence.php';
        // Which role a self-registered customer may be given. Free from 4.0, so
        // it is validated rather than hidden. (Roadmap 4.0-CORE-07)
        include_once __DIR__ . '/includes/classes/registrationrole.php';
        // Streaming CSV output, used by the export module. (Roadmap 4.0-CORE-10)
        include_once __DIR__ . '/includes/classes/csvwriter.php';
        // The background queue. Loaded and registered on every request because
        // the runner is a cron hook and the shutdown safety net has to be in
        // place wherever work was queued from — a job enqueued by a front-end
        // ticket submission is drained by whichever request comes next.
        // (Roadmap 4.0-PERF-02)
        include_once __DIR__ . '/includes/classes/jobs.php';
        JSSTjobs::registerHooks();
        // The migration record, its journal and rollback. Loaded on every
        // request rather than in admin only, because JSSTtable::store() asks it
        // whether a migration is recording on every insert the plugin makes —
        // including a ticket raised on the front end. (Roadmap 4.0-DATA-01)
        include_once __DIR__ . '/includes/classes/migration.php';
        // What an import would do, counted before it runs, and the checks that
        // run after it. Admin only — both are read by the migration screens and
        // by nothing else. (Roadmap 4.0-DATA-01)
        if (is_admin()) {
            include_once __DIR__ . '/includes/classes/migrationpreview.php';
            include_once __DIR__ . '/includes/classes/migrationvalidator.php';
            // Converting the plugin's tables to InnoDB, a table at a time.
            // Admin only: it is a maintenance screen and a System Status
            // summary, and nothing on the front end asks it anything.
            // (Roadmap 4.0-PERF-03)
            include_once __DIR__ . '/includes/classes/storageengine.php';
        }
        // The documented CSV import format, read by the import screen, the
        // template download and the importer. Loaded beside the migration it
        // runs inside rather than under is_admin(), because the export module
        // that carries it answers to the front end too and a task reaching it
        // there would find the class missing. (Roadmap 4.0-DATA-02)
        include_once __DIR__ . '/includes/classes/csvimport.php';
        /* The AI switchboard, the engine registry behind it, and the Copilot
           that was the first thing to use one. Loaded outside is_admin()
           because all three answer from wherever a reply is written, not only
           from a settings screen - and the policy in particular has to be
           askable by the front-end desk, the e-mail piping path and the cron
           that runs automatic replies. (Roadmap 6.0-AI-01, 4.0-AI-01)

           Order matters: the engine registry reads lanes out of the policy and
           the Copilot asks the registry, so they load outermost first. */
        include_once __DIR__ . '/includes/classes/aipolicy.php';
        /* What the AI is allowed to read, as opposed to what it is allowed to
           do. Loaded beside the policy and outside is_admin() because the
           filter it builds is applied by every retrieval, and retrieval happens
           on the ticket form, in the e-mail piping path and under cron far more
           often than it happens on a settings screen. (Roadmap 6.0-AI-02) */
        include_once __DIR__ . '/includes/classes/aisources.php';
        /* What language everything is in - the question neither the source
           register above nor the review register below can answer. Loaded
           beside them and outside is_admin() for the same reason: it is asked
           on every retrieval, and retrieval happens on the ticket form, in the
           e-mail piping path and under cron. (Roadmap 6.0-AI-09) */
        include_once __DIR__ . '/includes/classes/ailanguages.php';
        /* Whether a document is fit to be answered from at all - the state
           it is in, who is answerable for it, and whether anybody has read it
           lately. Loaded beside the source and language registers and outside
           is_admin() for the same reason as both: the clause it builds is
           applied by every retrieval, and retrieval happens on the ticket form,
           in chat and under cron. (Roadmap 6.0-KB-01) */
        include_once __DIR__ . '/includes/classes/kbgovernance.php';
        /* Where somebody lands after logging in, which is a question the page
           that offers them the login link cannot answer - at that moment the
           person reading it is a guest. Loaded outside is_admin() because
           `login_redirect` fires from wp-login.php. (Roadmap 4.5-FE-02) */
        include_once __DIR__ . '/includes/classes/loginredirect.php';
        /* What may be sent, and what can be taken back once it has been.
           Loaded outside is_admin() with the other two: the engine records a
           proposed answer from wherever it runs, which is usually cron.
           (Roadmap 6.0-AI-03) */
        include_once __DIR__ . '/includes/classes/aireview.php';
        /* Who automatic answers may go to, how often and how fast - and the one
           documented way to stop them. Loaded with the other three and outside
           is_admin() because the rules are enforced where the answer is written,
           which is cron, and because the pause switch has to be readable from
           there. It loads after the review register: the reply cap counts rows
           in that class's table. (Roadmap 6.0-AI-06) */
        include_once __DIR__ . '/includes/classes/airollout.php';
        /* What every model call costs and the caps that stop it costing more.
           Loaded before the engine, because the engine asks it before making a
           request and records against it afterwards - and outside is_admin()
           for the same reason as the rest: the spending happens under cron.
           (Roadmap 6.0-AI-07) */
        include_once __DIR__ . '/includes/classes/aiusage.php';
        /* What your customers asked that nothing could answer. Loaded outside
           is_admin() with the other registers, and for the plainest reason of
           all: four of its five signals are noticed on a customer's own
           request - the search that came back empty as they typed, the
           autopilot run under cron that found nothing to answer from, the
           handoff link clicked from an e-mail - and a gap nobody was loaded to
           write down is a gap that never happened. (Roadmap 6.0-AI-12) */
        include_once __DIR__ . '/includes/classes/aigaps.php';
        /* The setup checklist. It reads the five registers above and stores
           almost nothing of its own, so it loads after all of them - and not on
           a customer's request, which has no reason to ask how far the setup
           has got. The CLI is included for the same reason the benchmark is:
           the test suite is not wp-admin, and a class nothing can load is a
           class nothing can test. (Roadmap 6.0-AI-11) */
        if (is_admin() || (defined('WP_CLI') && WP_CLI) || PHP_SAPI === 'cli') {
            include_once __DIR__ . '/includes/classes/aisetup.php';
        }
        /* The AI Agent's menu and tab map: five groups over the AI screens.
           Admin only, like the setup checklist it reads. */
        if (is_admin() || (defined('WP_CLI') && WP_CLI) || PHP_SAPI === 'cli') {
            include_once __DIR__ . '/includes/classes/ainav.php';
        }
        /* The grounding benchmark. Admin and CLI only - nothing on a customer
           request has any reason to load it. (Roadmap 6.0-AI-05) */
        if (is_admin() || (defined('WP_CLI') && WP_CLI) || PHP_SAPI === 'cli') {
            include_once __DIR__ . '/includes/classes/aibenchmark.php';
        }
        include_once __DIR__ . '/includes/classes/aiengine.php';
        include_once __DIR__ . '/includes/classes/copilot.php';
        /* Routing and mood, read before anybody opens the ticket. Loaded
           outside is_admin() because its one automatic path runs under cron and
           its hook is on ticket creation, which is a customer's request.
           (Roadmap 6.0-AI-13) */
        include_once __DIR__ . '/includes/classes/aitriage.php';
        /* One customer however they reached you, and the per-channel refusal
           that goes with having more than one way in. Loaded outside is_admin()
           because the refusal is asked where an answer is written, which is
           cron and the chat door. (Roadmap 6.0-CH-02) */
        include_once __DIR__ . '/includes/classes/channels.php';
        /* Keeping the canned response library worth having - folders, teams,
           approval, language and what actually gets used. Loaded outside
           is_admin() because the approval rule is asked by the AI source
           register, which runs wherever retrieval does. (Roadmap 6.0-KB-02) */
        include_once __DIR__ . '/includes/classes/cannedlibrary.php';
        /* The ticket form: its questions, the conditions between them and what
           each answer has to look like. Loaded outside is_admin() because the
           server-side half of a condition has to be in place on the request
           that saves a ticket, which is a customer's post.

           The file stands down if the Customer Experience add-on has already
           declared the class - add-ons load first, so on a site still running
           that add-on at 1.2.0 this changes nothing at all. registerHooks() is
           called only where core's copy is the one that loaded, because the
           add-on's bootstrap calls it for its own. (Roadmap 6.5-FORM-02) */
        if (!class_exists('JSSTforms')) {
            include_once __DIR__ . '/includes/classes/forms.php';
            if (class_exists('JSSTforms')) {
                JSSTforms::registerHooks();
            }
        }
        /* The per-field visibility rules, moved into the Forms screen's own
           store. Included whichever copy of JSSTforms won above, because the
           migration decides for itself whether that copy can read what it would
           write and stands down when it cannot. (Roadmap 6.5-FORM-04) */
        include_once __DIR__ . '/includes/classes/formlogicmigration.php';
        if (is_admin() && class_exists('JSSTformlogicmigration')) {
            JSSTformlogicmigration::registerHooks();
        }
        /* The article importer used to be included here and is not any more.
           It moved into the Knowledge Base add-on in 6.5, because core never
           called it: an importer for knowledge base articles is only reachable
           from the knowledge base's own screens, and core ships no
           knowledgebase module at all. Every call site already asked
           `class_exists()` first, so a desk without that add-on is unchanged -
           and one with it gets the importer from the plugin whose feature it
           is. (Roadmap 6.5-ECO-01, 6.0-KB-01) */
        // The diagnostics catalogue every error message links into.
        // (Roadmap 4.0-OPS-03)
        include_once __DIR__ . '/includes/classes/docs.php';
        /* The two WordPress dashboard widgets, and the dashboard report range.
           (Roadmap 4.0-CORE-09)

           This is the one merged capability that is not a module: every other one
           lives in modules/<slug>/model.php and is resolved — to core's file first,
           since 2026-08-27 — by getPluginPath(). This one is included straight from
           the bootstrap, so it carries its own class name discipline instead: it
           declares JSSTcoredashboardwidgets, because the stand-alone add-on already
           holds JSSTDashboardwidgets (class names are case-insensitive) by the time
           core loads. See the header of includes/classes/dashboardwidgets.php.
           (Roadmap 4.0-CORE-19) */
        if (is_admin() && JSSTmergedaddon::coreOwns('dashboardwidgets')) {
            include_once __DIR__ . '/includes/classes/dashboardwidgets.php';
        }
        include_once 'includes/layout.php';
        include_once 'includes/pagination.php';
        include_once 'includes/includer.php';
        include_once 'includes/formfield.php';
        include_once 'includes/request.php';
        include_once 'includes/breadcrumbs.php';
        include_once 'includes/formhandler.php';
        include_once 'includes/shortcodes.php';
        include_once 'includes/paramregister.php';

        include_once 'includes/message.php';
        include_once 'includes/ajax.php';
        include_once 'includes/jsst-hooks.php';
        include_once 'includes/roles.php';
        require_once 'includes/constants.php';
        //include_once 'includes/addon-updater/jsstupdater.php';
    }

    /*
     * Localization
     */

    // public function load_plugin_textdomain() {
        // load_plugin_textdomain('js-support-ticket', false, jssupportticketphplib::JSST_dirname(plugin_basename(__FILE__)) . '/languages/');
        //if(!load_plugin_textdomain('js-support-ticket')){
            // load_plugin_textdomain('js-support-ticket', false, jssupportticketphplib::JSST_dirname(plugin_basename(__FILE__)) . '/languages/');
        /*}else{
            load_plugin_textdomain('js-support-ticket');
        }*/
    // }

    /*
     * Check the current request and handle according to it
     */

    function checkRequest($jsst_content) {
        return $jsst_content;
    }

    /**
     * Cache-busting version for a stylesheet or script the plugin ships.
     *
     * Everything used to be enqueued as ?ver=<productversion>, which is '400'
     * for the whole of 4.0 and does not change when a file is edited. A browser
     * that has the old copy therefore keeps it — through a plugin update, and
     * through any fix to the CSS — until the visitor happens to hard-reload.
     * Appending the file's modification time makes the URL change exactly when
     * the file does, which is the only thing the cache should be keyed on.
     *
     * Falls back to the product version alone if the file cannot be stat'ed, so
     * a packaging quirk can never stop a stylesheet loading.
     */
    public static function assetVersion($jsst_relative_path) {
        /* The configuration is read from the database, and there are moments
           when it has not been - a fresh install before activation finishes, a
           site whose config table is being repaired, the test suite. The
           version this class was built as is the right answer then: it changes
           when the files change, which is all a cache key has to do. */
        $jsst_version = isset(jssupportticket::$_config['productversion'])
            ? jssupportticket::$_config['productversion']
            : jssupportticket::$_currentversion;
        $jsst_file = JSST_PLUGIN_PATH . ltrim($jsst_relative_path, '/');
        if (!file_exists($jsst_file)) {
            return $jsst_version;
        }
        $jsst_mtime = filemtime($jsst_file);
        if (!$jsst_mtime) {
            return $jsst_version;
        }
        return $jsst_version . '.' . $jsst_mtime;
    }

    /*
     * function for the Style Sheets
     */

    static function addStyleSheets() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('commonjs',JSST_PLUGIN_URL.'includes/js/common.js', array(), jssupportticket::assetVersion('includes/js/common.js'), true);
        wp_enqueue_script('responsivetablejs',JSST_PLUGIN_URL.'includes/js/responsivetable.js', array(), jssupportticket::assetVersion('includes/js/responsivetable.js'), true);
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jsst-formvalidator',JSST_PLUGIN_URL.'includes/js/jquery.form-validator.js', array(), jssupportticket::assetVersion('includes/js/jquery.form-validator.js'), true);
        wp_enqueue_script( 'js-support-ticket-main-js', JSST_PLUGIN_URL . 'includes/js/common.js', array( 'jquery' ), jssupportticket::assetVersion('includes/js/common.js'), true );
        /* For jsstDropOverdue() in common.js: the report charts leave out the
           Overdue series on a desk without the Service Levels pack. */
        wp_add_inline_script('js-support-ticket-main-js', 'var jsstOverdue = ' . wp_json_encode(array(
            'off'   => !in_array('overdue', jssupportticket::$_active_addons),
            'label' => esc_html(__('Overdue', 'js-support-ticket')),
        )) . ';', 'before');
        if(in_array('notification', jssupportticket::$_active_addons)){
            wp_localize_script('commonjs', 'common', array('apiKey_firebase' => jssupportticket::$_config['apiKey_firebase'],'authDomain_firebase'=> jssupportticket::$_config['authDomain_firebase'],'databaseURL_firebase'=>jssupportticket::$_config['databaseURL_firebase'], 'projectId_firebase' => jssupportticket::$_config['projectId_firebase'], 'storageBucket_firebase' => jssupportticket::$_config['storageBucket_firebase'], 'messagingSenderId_firebase' => jssupportticket::$_config['messagingSenderId_firebase']));
        }
        //to localize validation error messages
        $jsst_js = '
        jQuery.formUtils.LANG = {
            errorTitle: "'. esc_html(__("Form submission failed!",'js-support-ticket')).'",
            requiredFields: "'. esc_html(__("You have not answered all required fields",'js-support-ticket')).'",
            badTime: "'. esc_html(__("You have not given a correct time",'js-support-ticket')).'",
            badEmail: "'. esc_html(__("You have not given a correct e-mail address",'js-support-ticket')).'",
            badTelephone: "'. esc_html(__("You have not given a correct phone number",'js-support-ticket')).'",
            badSecurityAnswer: "'. esc_html(__("You have not given a correct answer to the security question",'js-support-ticket')).'",
            badDate: "'. esc_html(__("You have not given a correct date",'js-support-ticket')).'",
            lengthBadStart: "'. esc_html(__("The input value must be between ",'js-support-ticket')).'",
            lengthBadEnd: "'. esc_html(__(" characters",'js-support-ticket')).'",
            lengthTooLongStart: "'. esc_html(__("The input value is longer than ",'js-support-ticket')).'",
            lengthTooShortStart: "'. esc_html(__("The input value is shorter than ",'js-support-ticket')).'",
            notConfirmed: "'. esc_html(__("Input values could not be confirmed",'js-support-ticket')).'",
            badDomain: "'. esc_html(__("Incorrect domain value",'js-support-ticket')).'",
            badUrl: "'. esc_html(__("The input value is not a correct URL",'js-support-ticket')).'",
            badCustomVal: "'. esc_html(__("The input value is incorrect",'js-support-ticket')).'",
            badInt: "'. esc_html(__("The input value was not a correct number",'js-support-ticket')).'",
            badSecurityNumber: "'. esc_html(__("Your social security number was incorrect",'js-support-ticket')).'",
            badUKVatAnswer: "'. esc_html(__("Incorrect UK VAT Number",'js-support-ticket')).'",
            badStrength: "'. esc_html(__("The password isn't strong enough",'js-support-ticket')).'",
            badNumberOfSelectedOptionsStart: "'. esc_html(__("You have to choose at least ",'js-support-ticket')).'",
            badNumberOfSelectedOptionsEnd: "'. esc_html(__(" answers",'js-support-ticket')).'",
            badAlphaNumeric: "'. esc_html(__("The input value can only contain alphanumeric characters ",'js-support-ticket')).'",
            badAlphaNumericExtra: "'. esc_html(__(" and ",'js-support-ticket')).'",
            wrongFileSize: "'. esc_html(__("The file you are trying to upload is too large",'js-support-ticket')).'",
            wrongFileType: "'. esc_html(__("The file you are trying to upload is of the wrong type",'js-support-ticket')).'",
            groupCheckedRangeStart: "'. esc_html(__("Please choose between ",'js-support-ticket')).'",
            groupCheckedTooFewStart: "'. esc_html(__("Please choose at least ",'js-support-ticket')).'",
            groupCheckedTooManyStart: "'. esc_html(__("Please choose a maximum of ",'js-support-ticket')).'",
            groupCheckedEnd: "'. esc_html(__(" item(s)",'js-support-ticket')).'",
            badCreditCard: "'. esc_html(__("The credit card number is not correct",'js-support-ticket')).'",
            badCVV: "'. esc_html(__("The CVV number was not correct",'js-support-ticket')).'"
        };
        ';
        wp_add_inline_script('jsst-formvalidator',$jsst_js);
    }

    public static function jsst_register_plugin_styles(){
        global $wp_styles;
        if (!isset($wp_styles->queue)) {
            wp_enqueue_style('jssupportticket-main-css', JSST_PLUGIN_URL . 'includes/css/style.css', array(), jssupportticket::assetVersion('includes/css/style.css'));
            // responsive style sheets
            wp_enqueue_style('jssupportticket-tablet-css', JSST_PLUGIN_URL . 'includes/css/style_tablet.css', array(), jssupportticket::assetVersion('includes/css/style_tablet.css'), '(min-width: 668px) and (max-width: 782px)');
            wp_enqueue_style('jssupportticket-mobile-css', JSST_PLUGIN_URL . 'includes/css/style_mobile.css', array(), jssupportticket::assetVersion('includes/css/style_mobile.css'), '(min-width: 481px) and (max-width: 667px)');
            wp_enqueue_style('jssupportticket-oldmobile-css', JSST_PLUGIN_URL . 'includes/css/style_oldmobile.css', array(), jssupportticket::assetVersion('includes/css/style_oldmobile.css'), '(max-width: 480px)');
            //wp_enqueue_style('jssupportticket-main-css');
            if(is_rtl()){
                //wp_register_style('jssupportticket-main-css-rtl', JSST_PLUGIN_URL . 'includes/css/stylertl.css');
                wp_enqueue_style('jssupportticket-main-css-rtl', JSST_PLUGIN_URL . 'includes/css/stylertl.css', array(), jssupportticket::assetVersion('includes/css/stylertl.css'));
                //wp_enqueue_style('jssupportticket-main-css-rtl');
            }
            $jsst_color1 = require_once(JSST_PLUGIN_PATH . 'includes/css/style.php');
            // wp_enqueue_style('jssupportticket-color-css', JSST_PLUGIN_URL . 'includes/css/color.css');
        } else {    
            JSSTincluder::getJSModel('jssupportticket')->checkIfMainCssFileIsEnqued();
        }
    }

    public static function jsst_admin_register_plugin_styles() {
        $jsst_page = JSSTrequest::getVar('page');
        /* Every page this plugin serves, not a list somebody remembers to extend.
           (Roadmap 6.5-ECO-01)

           This gated the Tailwind bundle, and Tailwind ships a preflight reset - it
           restyles headings, tables, buttons and form controls. So a screen on this
           list and a screen off it render the *same markup* differently, which is
           exactly the "this page does not look like the others" report that is easy
           to chase into the stylesheet and never find, because the stylesheet is
           identical and the reset underneath it is not.

           Twenty-five slugs were missing: every one of the nine bundles, the Pro
           companion, and a dozen add-ons that grew a screen after this array was
           written - Service Levels (`overdue`) and Retention (`reporting`) among
           them. Rather than add twenty-five names and leave the twenty-sixth to be
           reported by a customer, the list is now built from what the product
           actually serves: the availability array, which every add-on and bundle
           already joins, plus core's own screens and the bundle slugs. */
        $jsst_plugin_pages = array_values(array_unique(array_merge(
            array(
                'jssupportticket','slug','ticket','fieldordering','configuration','priority','status',
                'thirdpartyimport','product','department','themes','reports','email','systemerror',
                'emailtemplate','userfeild','cannedresponses','role','banemail',
                'banemaillog','export','postinstallation','premiumplugin','shortcodes','help',
                'helptopic','gdpr','copilot','pro',
            ),
            (array) jssupportticket::$_active_addons,
            class_exists('JSSTbundle') ? array_keys(JSSTbundle::bundles()) : array()
        )));
        wp_register_style('jsticket-bootstrapcss', JSST_PLUGIN_URL . 'includes/css/bootstrap.min.css', array(), jssupportticket::assetVersion('includes/css/bootstrap.min.css'));
        wp_register_style('jsticket-admincss', JSST_PLUGIN_URL . 'includes/css/admincss.css', array(), jssupportticket::assetVersion('includes/css/admincss.css'));
        // Only enqueue Tailwind if the current page is part of your plugin
        if (in_array($jsst_page, $jsst_plugin_pages)) {
            wp_enqueue_script('jsticket-tailwind', JSST_PLUGIN_URL . 'includes/js/tailwind.js', array(), '3.4.4', false);
        }
        wp_enqueue_style('jsticket-admincss');
        if(is_rtl()){
            wp_register_style('jsticket-admincss-rtl', JSST_PLUGIN_URL . 'includes/css/admincssrtl.css', array(), jssupportticket::assetVersion('includes/css/admincssrtl.css'));
            wp_enqueue_style('jsticket-admincss-rtl');
        }
    }

    /*
     * function to get the pageid from the wpoptions
     */

    public static function getPageid() {
        /* A real page id, not merely "something was set". The guard used to be
           `!= ''`, and on PHP 8 that is true for 0 - where on PHP 7 it was
           false - so a stored zero was returned as though it were a page and
           the two fallbacks below were never reached.

           Zero is not a hypothetical. `JSSTformhandler::checkDeleteRequest()`
           calls `setPageID(absint(...))` on every `action=jstask` request, so
           any task whose URL carries no `page_id` - which is most of the
           link-style and save-style tasks - poisoned this for the rest of the
           request. What it broke was every address built afterwards:
           `makeUrl()` asks here when `get_the_permalink()` has nothing to give,
           got 0, fell through to `site_url('/')`, and produced a slug hung off
           the site root. Saving a queue view on the front-end desk redirected
           to /<prefix>staff-my-tickets, which is not a page on any site.

           `(int) ... > 0` is the honest test and leaves every legitimate caller
           alone: the shortcodes pass `get_the_ID()`, which is either a positive
           id or false, and false took the fallback before and still does. */
        if((int) jssupportticket::$_pageid > 0){
            return jssupportticket::$_pageid;
        }else{
            $jsst_pageid = JSSTrequest::getVar('page_id','GET');
            if($jsst_pageid){
                return $jsst_pageid;
            }else{ // in case of categories popup
                $jsst_query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'default_pageid'";
                $jsst_pageid = jssupportticket::$_db->get_var($jsst_query);
                return $jsst_pageid;
            }
        }
    }

    public static function setPageID($jsst_id) {
        jssupportticket::$_pageid = $jsst_id;
        return;
    }

    /*
     * function to parse the spaces in given string
     */

    public static function parseSpaces($jsst_string) {
        return jssupportticketphplib::JSST_str_replace('%20',' ',$jsst_string);
    }

    static function checkScreenTag(){
        if(!is_admin()){
            if (jssupportticket::$_config['support_screentag'] == 1) { // we need to show the support ticket tag
                if (jssupportticket::$_config['support_custom_img'] == '0') {
                    $jsst_img_scr = JSST_PLUGIN_URL.'includes/images/support.png';
                } else {
                    $jsst_maindir = wp_upload_dir();
                    $jsst_basedir = $jsst_maindir['baseurl'];
                    $jsst_datadirectory = jssupportticket::$_config['data_directory'];
                    $jsst_img_scr = $jsst_basedir . '/' . $jsst_datadirectory.'/supportImg/'.jssupportticket::$_config['support_custom_img'];
                }
                if (isset(jssupportticket::$_config['support_custom_txt']) && jssupportticket::$_config['support_custom_txt'] != '') {
                    $jsst_support_txt = jssupportticket::$_config['support_custom_txt'];
                } else {
                    $jsst_support_txt = "Support";
                }
                $jsst_location = 'left';
                $jsst_borderradius = '0px 8px 8px 0px';
                $jsst_padding = '5px 10px 5px 20px';
                switch (jssupportticket::$_config['screentag_position']) {
                    case 1: // Top left
                        $jsst_top = "30px";
                        $jsst_left = "0px";
                        $jsst_right = "auto";
                        $jsst_bottom = "auto";
                    break;
                    case 2: // Top right
                        $jsst_top = "30px";
                        $jsst_left = "auto";
                        $jsst_right = "0px";
                        $jsst_bottom = "auto";
                        $jsst_location = 'right';
                        $jsst_borderradius = '8px 0px 0px 8px';
                        $jsst_padding = '5px 20px 5px 10px';
                    break;
                    case 3: // middle left
                        $jsst_top = "48%";
                        $jsst_left = "0px";
                        $jsst_right = "auto";
                        $jsst_bottom = "auto";
                    break;
                    case 4: // middle right
                        $jsst_top = "48%";
                        $jsst_left = "auto";
                        $jsst_right = "0px";
                        $jsst_bottom = "auto";
                        $jsst_location = 'right';
                        $jsst_borderradius = '8px 0px 0px 8px';
                        $jsst_padding = '5px 20px 5px 10px';
                    break;
                    case 5: // bottom left
                        $jsst_top = "auto";
                        $jsst_left = "0px";
                        $jsst_right = "auto";
                        $jsst_bottom = "30px";
                    break;
                    case 6: // bottom right
                        $jsst_top = "auto";
                        $jsst_left = "auto";
                        $jsst_right = "0px";
                        $jsst_bottom = "30px";
                        $jsst_location = 'right';
                        $jsst_borderradius = '8px 0px 0px 8px';
                        $jsst_padding = '5px 20px 5px 10px';
                    break;
                }
                // $jsst_html = '<style type="text/css">
                //             div#js-ticket_screentag{opacity:0;position:fixed;top:'.$jsst_top.';left:'.$jsst_left.';right:'.$jsst_right.';bottom:'.$jsst_bottom.';padding:'.$jsst_padding.';background:rgba(18, 17, 17, 0.5);z-index:9999;border-radius:'.$jsst_borderradius.';}
                //             div#js-ticket_screentag img.js-ticket_screentag_image{margin-'.$jsst_location.':10px;display:inline-block;}
                //             div#js-ticket_screentag a.js-ticket_screentag_anchor{color:#ffffff;text-decoration:none;}
                //             div#js-ticket_screentag span.text{display:inline-block;font-family:sans-serif;font-size:15px;}
                //         </style>';

                $jsst_html ='
                        <div id="js-ticket_screentag">
                        <a class="js-ticket_screentag_anchor" href="' . esc_url(site_url('?page_id=' . jssupportticket::$_config['default_pageid'])) . '">';
                if($jsst_location == 'right'){
                    $jsst_html .= '<img class="js-ticket_screentag_image" alt="screen tag" src="'.esc_url($jsst_img_scr).'" /><span class="text">'.esc_html($jsst_support_txt).'</span>';
                }else{
                    $jsst_html .= '<span class="text">'.esc_html($jsst_support_txt).'</span><img class="js-ticket_screentag_image" alt="screen tag" src="'.esc_url($jsst_img_scr).'" />';
                }
                $jsst_html .= '</a>
                        </div>';
                        $jsst_jssupportticket_js = '
                            jQuery(document).ready(function(){
                                jQuery("div#js-ticket_screentag").css("'.$jsst_location.'","-"+(jQuery("div#js-ticket_screentag span.text").width() + 25)+"px");
                                jQuery("div#js-ticket_screentag").css("opacity",1);
                                jQuery("div#js-ticket_screentag").hover(
                                    function(){
                                        jQuery(this).animate({'.$jsst_location.': "+="+(jQuery("div#js-ticket_screentag span.text").width() + 25)}, 1000);
                                    },
                                    function(){
                                        jQuery(this).animate({'.$jsst_location.': "-="+(jQuery("div#js-ticket_screentag span.text").width() + 25)}, 1000);
                                    }
                                );
                            });';
                        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
                echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
            }
        }
    }

    public static function JSST_getVarValue($jsst_text_string) {
        $jsst_translations = get_translations_for_domain('js-support-ticket');
        $jsst_translation  = $jsst_translations->translate( $jsst_text_string );
        return esc_html($jsst_translation);
    }

    static function JSST_sanitizeData($jsst_data){
        if($jsst_data == null){
            return $jsst_data;
        }
        if(is_array($jsst_data)){
            return map_deep( $jsst_data, 'sanitize_text_field' );
        }else{
            return sanitize_text_field( $jsst_data );
        }
    }

    static function makeUrl($jsst_args = array()){
        global $wp_rewrite;

        $jsst_pageid = JSSTrequest::getVar('jsstpageid');
        if(is_numeric($jsst_pageid)){
            $jsst_permalink = get_the_permalink($jsst_pageid);
        }else{
            if(isset($jsst_args['jsstpageid']) && is_numeric($jsst_args['jsstpageid'])){
                $jsst_permalink = get_the_permalink($jsst_args['jsstpageid']);
            }else{
                $jsst_permalink = get_the_permalink();
            }
        }

        /* Nothing in the loop to build an address out of.

           Every line below this one treats $jsst_permalink as the page the desk
           is on, and `get_the_permalink()` answers that only where WordPress has
           a post in hand. On wp-login.php, in a cron run, in a REST request or
           on any other request with no queried object it answers `false`, and
           `false` went straight through: the parse produced an empty base, the
           slug was appended to nothing, and what came back was a root-relative
           `/staff-my-tickets`. A browser resolves that against the host, so on
           any site in a subdirectory - which is every development copy of this
           plugin and a good share of the installs - the customer was sent to a
           URL outside WordPress altogether.

           Where this showed was the login redirect: `JSSTloginredirect::resolve()`
           runs on `login_redirect`, which fires from wp-login.php, so it asked
           for the address of My Tickets at the one moment this function could
           not give it one. (Roadmap 4.5-FE-02)

           The desk's own page is the honest answer to "which page is this" when
           WordPress has not named one - it is the page the shortcode lives on
           and the page every one of these addresses is a path under. Asked only
           as a fallback, so a request that does have a page in the loop still
           builds its links against that page and not against this one. */
        if (empty($jsst_permalink)) {
            $jsst_fallback_pageid = jssupportticket::getPageid();
            if (is_numeric($jsst_fallback_pageid) && (int) $jsst_fallback_pageid > 0) {
                $jsst_permalink = get_the_permalink((int) $jsst_fallback_pageid);
            }
            /* Still nothing - no control panel page recorded, or it has been
               deleted. site_url() is at least on the right host and in the
               right directory, which a bare `/slug` is not. */
            if (empty($jsst_permalink)) {
                $jsst_permalink = site_url('/');
            }
        }

        if (!$wp_rewrite->using_permalinks() || is_feed()){
            if(!strstr($jsst_permalink, 'page_id') && !strstr($jsst_permalink, '?p=')){
                $jsst_page['page_id'] = get_option('page_on_front');
                $jsst_args = $jsst_page + $jsst_args;
            }
            $jsst_redirect_url = add_query_arg($jsst_args,$jsst_permalink);
            return $jsst_redirect_url;
        }

        if(isset($jsst_args['jstmod']) && isset($jsst_args['jstlay'])){
            // Get the original query parts
            $jsst_redirect = wp_parse_url($jsst_permalink);
            if (!isset($jsst_redirect['query']))
                $jsst_redirect['query'] = '';

            if(strstr($jsst_permalink, '?')){ // if variable exist
                $jsst_redirect_array = jssupportticketphplib::JSST_explode('?', $jsst_permalink);
                $_redirect = $jsst_redirect_array[0];
            }else{
                $_redirect = $jsst_permalink;
            }

            if($_redirect[strlen($_redirect) - 1] == '/'){
                $_redirect = jssupportticketphplib::JSST_substr($_redirect, 0, jssupportticketphplib::JSST_strlen($_redirect) - 1);
            }


            // If is layout
            $jsst_changename = false;
            if(file_exists(WP_PLUGIN_DIR.'/js-jobs/js-jobs.php')){
                $jsst_changename = true;
            }
            if(file_exists(WP_PLUGIN_DIR.'/js-vehicle-manager/js-vehicle-manager.php')){
                $jsst_changename = true;
            }
            if (isset($jsst_args['jstlay'])) {
                /* switch ($jsst_args['jstlay']) {
                    case 'ticketdetail':$jsst_layout = 'ticket';break;
                    case 'staffaddticket':$jsst_layout = 'staff-add-ticket';break;
                    case 'rolepermission':$jsst_layout = 'role-permission';break;
                    case 'addannouncement':$jsst_layout = 'add-announcement';break;
                    case 'adddepartment':$jsst_layout = 'add-department';break;
                    case 'adddownload':$jsst_layout = 'add-download';break;
                    case 'addfaq':$jsst_layout = 'add-faq';break;
                    case 'faqdetails':$jsst_layout = 'faq';break;
                    case 'addarticle':$jsst_layout = 'add-article';break;
                    case 'addcategory':$jsst_layout = 'add-category';break;
                    case 'userknowledgebasearticles':$jsst_layout = 'kb-articles';break;
                    case 'articledetails':$jsst_layout = 'kb-article';break;
                    case 'addrole':$jsst_layout = 'add-role';break;
                    case 'addstaff':$jsst_layout = 'add-staff';break;
                    case 'staffpermissions':$jsst_layout = 'staff-permissions';break;
                    case 'myticket':$jsst_layout = 'my-tickets';break;
                    case 'staffmyticket':$jsst_layout = 'staff-my-tickets';break;
                    case 'userknowledgebase':$jsst_layout = 'knowledgebase';break;
                    case 'stafflistcategories':$jsst_layout = 'staff-categories';break;
                    case 'stafflistarticles':$jsst_layout = 'staff-kb-articles';break;
                    case 'staffannouncements':$jsst_layout = 'staff-announcements';break;
                    case 'staffdownloads':$jsst_layout = 'staff-downloads';break;
                    case 'stafffaqs':$jsst_layout = 'staff-faqs';break;
                    case 'addticket':$jsst_layout = 'add-ticket';break;
                    case 'ticketstatus':$jsst_layout = 'ticket-status';break;
                    case 'controlpanel':$jsst_layout = 'control-panel';break;
                    case 'staffdetailreport':$jsst_layout = 'staff-report';break;
                    case 'staffreports':$jsst_layout = 'staff-reports';break;
                    case 'departmentreports':$jsst_layout = 'department-reports';break;
                    case 'announcementdetails':$jsst_layout = 'announcement';break;
                    case 'formfeedback':$jsst_layout = 'feed-back';break;
                    case 'feedbacks':$jsst_layout = 'staff-feedbacks';break;
                    case 'visitormessagepage':$jsst_layout = 'visitor-message';break;
                    case 'addhelptopic':$jsst_layout = 'add-help-topic';break;
                    case 'agenthelptopics':$jsst_layout = 'agent-help-topics';break;
                    case 'addcannedresponse':$jsst_layout = 'add-canned-response';break;
                    case 'agentcannedresponses':$jsst_layout = 'agent-canned-responses';break;
                    case 'adderasedatarequest':$jsst_layout = 'gdpr-data-compliance-actions';break;
                    case 'printticket':
                    $jsst_layout = 'print-ticket';
                    break;
                    case 'myprofile':
                        $jsst_layout = ($jsst_changename === true) ? 'ticket-my-profile' : 'my-profile';
                    break;
                    case 'login':
                        $jsst_layout = ($jsst_changename === true) ? 'ticket-login' : 'login';
                    break;
                    case 'userregister':
                        $jsst_layout = ($jsst_changename === true) ? 'ticket-user-register' : 'userregister';
                    break;
                    case 'formmessage':
                        $jsst_layout = ($jsst_changename === true) ? 'ticket-add-message' : 'add-message';
                    break;
                    case 'message':
                        $jsst_layout = ($jsst_changename === true) ? 'ticket-message' : 'message';
                    break;
                    case 'inbox':
                        $jsst_layout = ($jsst_changename === true) ? 'ticket-message-inbox' : 'message-inbox';
                    break;
                    case 'outbox':
                        $jsst_layout = ($jsst_changename === true) ? 'ticket-message-outbox' : 'message-outbox';
                    break;
                    default:$jsst_layout = $jsst_args['jstlay'];break;
                } */

                $jsst_layout = '';
                $jsst_layout = JSSTincluder::getJSModel('slug')->getSlugFromFileName($jsst_args['jstlay'],$jsst_args['jstmod']);
                global $wp_rewrite;
                $jsst_slug_prefix = JSSTincluder::getJSModel('configuration')->getConfigValue('home_slug_prefix');
                if(is_home() || is_front_page()){
                    if($_redirect == site_url()){
                        $jsst_layout = $jsst_slug_prefix.$jsst_layout;
                    }
                }else{
                    if($_redirect == site_url()){
                        $jsst_layout = $jsst_slug_prefix.$jsst_layout;
                    }
                }
                $_redirect .= '/' . $jsst_layout;
            }
            // If is list
            if (isset($jsst_args['list'])) {
                $_redirect .= '/' . $jsst_args['list'];
            }
            // If is sortby
            if (isset($jsst_args['sortby'])) {
                $_redirect .= '/' . $jsst_args['sortby'];
            }
            // If is jssupportticket_ticketid
            if (isset($jsst_args['jssupportticketid'])) {
                $_redirect .= '/' . $jsst_args['jssupportticketid'];
                if($jsst_args['jstlay'] == 'addticket'){
                    $_redirect .= '_10';// 10 for ticket id
                }
            }

            if (isset($jsst_args['edd_order_id'])) {
                $_redirect .= '/' . $jsst_args['edd_order_id'].'_11';// 11 for easy digital downloads id
            }

            if (isset($jsst_args['uid'])) {
                $_redirect .= '/' . $jsst_args['uid'].'_12';// 12 for user id
            }

            if (isset($jsst_args['paidsupportid'])) {
                $_redirect .= '/' . $jsst_args['paidsupportid'].'_13';// 13 for paid support id
            }
            if (isset($jsst_args['formid'])){
                $_redirect .= '/' . $jsst_args['formid'].'_15';// 15 multi form id
            }


            if (isset($jsst_args['jsst-id'])){
                $_redirect .= '/' . $jsst_args['jsst-id'];
            }
            if (isset($jsst_args['jsst-date-start'])){
                $_redirect .= '/date-start:' . $jsst_args['jsst-date-start'];
            }
            if (isset($jsst_args['jsst-date-end'])){
                $_redirect .= '/date-end:' . $jsst_args['jsst-date-end'];
            }
            if (isset($jsst_args['js_redirecturl'])){
                $_redirect .= '/?js_redirecturl=' . $jsst_args['js_redirecturl'];
            }
            if (isset($jsst_args['token'])){
                $_redirect .= '/?token=' . $jsst_args['token'];
            }
            if (isset($jsst_args['successflag'])){
                $_redirect .= '/?successflag=' . $jsst_args['successflag'];
            }
            /* Which starting point a role form was asked to fill itself in
               from. (Roadmap 4.5-FE-03)
               This branch expresses a known list of arguments in the path and
               silently drops anything it has not been told about, so the
               front-end "Start from" picker built every one of its links
               without the one argument that distinguishes them: on a site with
               pretty permalinks all of the presets pointed at a bare
               `/add-role/` and clicking any of them reloaded the same empty
               form. The wp-admin twin was unaffected, its links being plain
               query strings, which is why this only ever showed on the desk.
               `add_query_arg` rather than another `'/?' .` line, because by
               here the path may already carry a query string from one of the
               three above and a second `?` is not a query string. */
            if (isset($jsst_args['preset'])){
                $_redirect = add_query_arg('preset', $jsst_args['preset'], $_redirect);
            }
            return $_redirect;
        }else{ // incase of form
            $jsst_redirect_url = add_query_arg($jsst_args,$jsst_permalink);
            return $jsst_redirect_url;
        }
    }

    function jsst_reset_aadon_query(){
        jssupportticket::$_addon_query = array('select'=>'','join'=>'','where'=>'');
    }

    function jssupportticket_load_wp_plugin_file() {
        // $jsst_wp_admin_url = admin_url('includes/plugin.php');
        // $jsst_wp_admin_path = str_replace(site_url('/'), ABSPATH, $jsst_wp_admin_url);
        // if(jssupportticketphplib::JSST_strpos($jsst_wp_admin_path, "http") !== false) {
            $jsst_wp_admin_path = ABSPATH . 'wp-admin/includes/plugin.php';
        // }
        require_once($jsst_wp_admin_path);
    }

    function jssupportticket_load_wp_admin_file() {
        // $jsst_wp_admin_url = admin_url('includes/admin.php');
        // $jsst_wp_admin_path = str_replace(site_url('/'), ABSPATH, $jsst_wp_admin_url);
        // if(jssupportticketphplib::JSST_strpos($jsst_wp_admin_path, "http") !== false) {
            $jsst_wp_admin_path = ABSPATH . 'wp-admin/includes/admin.php';
        // }
        require_once($jsst_wp_admin_path);
    }

    function jssupportticket_load_wp_file() {
        // $jsst_wp_admin_url = admin_url('includes/file.php');
        // $jsst_wp_admin_path = str_replace(site_url('/'), ABSPATH, $jsst_wp_admin_url);
        // if(jssupportticketphplib::JSST_strpos($jsst_wp_admin_path, "http") !== false) {
            $jsst_wp_admin_path = ABSPATH . 'wp-admin/includes/file.php';
        // }
        require_once($jsst_wp_admin_path);
    }

    function jssupportticket_load_wp_pcl_zip() {
        // $jsst_wp_admin_url = admin_url('includes/class-pclzip.php');
        // $jsst_wp_admin_path = str_replace(site_url('/'), ABSPATH, $jsst_wp_admin_url);
        // if(jssupportticketphplib::JSST_strpos($jsst_wp_admin_path, "http") !== false) {
            $jsst_wp_admin_path = ABSPATH . 'wp-admin/includes/class-pclzip.php';
        // }
        require_once($jsst_wp_admin_path);
    }

    function jssupportticket_load_wp_ajax_upgrader_skin() {
        // $jsst_wp_admin_url = admin_url('includes/class-wp-ajax-upgrader-skin.php');
        // $jsst_wp_admin_path = str_replace(site_url('/'), ABSPATH, $jsst_wp_admin_url);
        // if(jssupportticketphplib::JSST_strpos($jsst_wp_admin_path, "http") !== false) {
            $jsst_wp_admin_path = ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
        // }
        require_once($jsst_wp_admin_path);
    }

    function jssupportticket_load_wp_upgrader() {
        // $jsst_wp_admin_url = admin_url('includes/class-wp-upgrader.php');
        // $jsst_wp_admin_path = str_replace(site_url('/'), ABSPATH, $jsst_wp_admin_url);
        // if(jssupportticketphplib::JSST_strpos($jsst_wp_admin_path, "http") !== false) {
            $jsst_wp_admin_path = ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        // }
        require_once($jsst_wp_admin_path);
    }

    function jssupportticket_load_wp_plugin_upgrader() {
        // $jsst_wp_admin_url = admin_url('includes/class-plugin-upgrader.php');
        // $jsst_wp_admin_path = str_replace(site_url('/'), ABSPATH, $jsst_wp_admin_url);
        // if(jssupportticketphplib::JSST_strpos($jsst_wp_admin_path, "http") !== false) {
            $jsst_wp_admin_path = ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
        // }
        require_once($jsst_wp_admin_path);
    }

    function jssupportticket_load_wp_translation_install() {
        // $jsst_wp_admin_url = admin_url('includes/translation-install.php');
        // $jsst_wp_admin_path = str_replace(site_url('/'), ABSPATH, $jsst_wp_admin_url);
        // if(jssupportticketphplib::JSST_strpos($jsst_wp_admin_path, "http") !== false) {
            $jsst_wp_admin_path = ABSPATH . 'wp-admin/includes/translation-install.php';
        // }
        require_once($jsst_wp_admin_path);
    }

    function jssupportticket_load_phpass() {
        /**
         * Safely include the PasswordHash class.
         * WPINC is a core WordPress constant that points to the 'wp-includes' folder.
         * This remains compatible with security plugins that rename paths.
         */
        $jsst_wp_site_path = ABSPATH . WPINC . '/class-phpass.php';

        if (file_exists($jsst_wp_site_path)) {
            require_once($jsst_wp_site_path);
        } else {
            // Fallback for extreme cases where WPINC might not be defined or path is non-standard
            require_once(ABSPATH . 'wp-includes/class-phpass.php');
        }
    }


    function ticketviaemail() {// this funtion also handles ticket overdue bcz of hours confiuration
/*
        $jsst_today = gmdate('Y-m-d');
        $jsst_f = fopen(JSST_PLUGIN_PATH .  'mylogone.txt', 'a') or exit("Can't open $jsst_lfile!");
        $jsst_time = gmdate('H:i:s');
        $jsst_message = ' main function call cron '.$jsst_time;
        fwrite($jsst_f, "$jsst_time ($jsst_script_name) $jsst_message\n");
*/
        if(in_array('overdue', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('overdue')->updateTicketStatusToOverDueCron();// this funtions handles the overdue of tickets by cron
        }
        if(in_array('feedback', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('ticket')->sendFeedbackMail();// this funtions handles the the feedback email
        }
        if(in_array('emailpiping', jssupportticket::$_active_addons)){
            /*
             * One collection, not two. registerReadEmails() arranges for the
             * mailbox to be read at shutdown, after the response has been
             * flushed — that is what lets the browser-triggered
             * ?jsstcron=ticketviaemail URL answer immediately instead of holding
             * the connection open for the length of an IMAP session. Calling the
             * model here as well ran the whole collection a second time in the
             * same request: every mailbox opened twice, and on a slow mailbox
             * the run took twice as long for nothing. The messages themselves
             * are marked read by the first pass, so the duplicate found nothing
             * and stayed invisible.
             */
            JSSTincluder::getJSController('emailpiping')->registerReadEmails();
        }
/*
        $jsst_time = gmdate('H:i:s');
        $jsst_message = ' after ticketviaemail controller call cron '.$jsst_time;
        fwrite($jsst_f, "$jsst_time ($jsst_script_name) $jsst_message\n");
*/
    }
}

add_action('init', 'jsst_custom_init_session', 1);
function jsst_custom_init_session() {
    wp_enqueue_script("jquery");
    jssupportticket::addStyleSheets();
    // jsst_subscribe_notifications();
}

// add the filter
$jsst_jssupportticket = new jssupportticket();

add_filter( 'login_form_middle', 'jsstAddLostPasswordLink' );
function jsstAddLostPasswordLink($jsst_content) {
   return $jsst_content.'
   <a href="'.site_url().'/wp-login.php?action=lostpassword">'. esc_html(__('Lost your password','js-support-ticket')) .'?</a>';
}

add_filter( 'login_form_middle', 'jsstAddRegisterLink' );
function jsstAddRegisterLink($jsst_content) {
    if(get_option('users_can_register')){
        $jsst_registerval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_register_link');
        $jsst_registerlink = JSSTincluder::getJSModel('configuration')->getConfigValue('register_link');
        if($jsst_registerval == 3){
            $jsst_content .= ' <a href="'.esc_url(wp_registration_url()).'">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
        }else if($jsst_registerval == 2 && $jsst_registerlink != ""){
            $jsst_content .= ' <a href="'.esc_url($jsst_registerlink).'">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
        }else{
            $jsst_content .= ' <a href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket','jstlay'=>'userregister'))).'">'. esc_html(__('Register','js-support-ticket')) .'</a>';
        }
    }
    return $jsst_content;
}

add_action('wp_ajax_save_dashboard_preferences', 'jssupportticket_save_dashboard_preferences');
function jssupportticket_save_dashboard_preferences() {
    check_ajax_referer('jssupportticket_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Access Denied.']);
    }

    $jsst_preferences = filter_input(INPUT_POST, 'preferences', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    if (!is_array($jsst_preferences)) {
        wp_send_json_error(['message' => 'Invalid preferences data.']);
    }

    $jsst_clean_preferences = [];
    foreach ($jsst_preferences as $jsst_key => $jsst_value) {
        $jsst_clean_preferences[$jsst_key] = filter_var($jsst_value, FILTER_VALIDATE_BOOLEAN);
    }
    
    update_option('jssupportticket_admin_charts_visibility', $jsst_clean_preferences);
    wp_send_json_success(['message' => 'Preferences saved successfully.']);
}

/* jsst_addon_update_date_failed used to be answered with die(). Old add-ons
   fire it when updateDate() says their key was refused by jshelpdesk.com/setup/;
   in 5.0.0 updateDate() asks nobody and always says yes, so there is nothing to
   answer and nothing to kill the request over. */

add_filter('style_loader_tag', 'jsstW3cValidation', 10, 2);
add_filter('script_loader_tag', 'jsstW3cValidation', 10, 2);
function jsstW3cValidation($jsst_tag, $jsst_handle) {
    return jssupportticketphplib::JSST_preg_replace( "/type=['\"]text\/(javascript|css)['\"]/", '', $jsst_tag );
}

/* The old add-on updater (includes/addon-updater/) is gone in 5.0.0. It asked
   jshelpdesk.com/setup/ and /appsys/ about the per-add-on keys of 4.0.0 and
   earlier; add-on updates now come from the new licence server, through
   JSSTlicense. */

//$jsst_jssupportticket = new jssupportticket();
if(is_file('includes/updater/updater.php')){
    
}
// file for admin review
if(is_admin() && is_file('includes/classes/jsstadminreviewbox.php')){
    include_once __DIR__ . '/includes/classes/jsstadminreviewbox.php';
}

 //do_action('edd_purchase_history_header_after');

 //do_action( 'edd_purchase_history_row_end', $jsst_payment->ID, $jsst_payment->payment_meta );

function jsst_get_avatar($jsst_uid, $jsst_class = '') {
    // Default avatar image URL
    $jsst_defaultImage = JSST_PLUGIN_URL . '/includes/images/user.png';

    // Ensure the UID is valid and numeric
    if (!is_numeric($jsst_uid) || !$jsst_uid) {
        return '<img alt="' . esc_html(__('image', 'js-support-ticket')) . '" src="' . esc_url($jsst_defaultImage) . '" class="' . esc_attr($jsst_class) . '" />';
    }

    // in case if user is agent
    if ( in_array('agent',jssupportticket::$_active_addons)) {
        $jsst_query = "
        SELECT id, photo FROM `" . jssupportticket::$_db->prefix."js_ticket_staff` AS staff WHERE staff.uid = ".intval($jsst_uid);
        $jsst_staff_data = jssupportticket::$_db->get_row($jsst_query);
        if (!empty($jsst_staff_data->photo)) {
            $jsst_maindir = wp_upload_dir();
            $jsst_path = $jsst_maindir['baseurl'];

            $jsst_imageurl = $jsst_path."/".jssupportticket::$_config['data_directory']."/staffdata/staff_".$jsst_staff_data->id."/".$jsst_staff_data->photo;

            return '<img alt="' . esc_html(__('image', 'js-support-ticket')) . '" src="' . esc_url($jsst_imageurl) . '" class="' . esc_attr($jsst_class) . '" />';
        }
    }
    $jsst_uid = JSSTincluder::getJSModel('jssupportticket')->getWPUidById($jsst_uid);

    // Get the avatar URL
    if(jssupportticket::$_config['show_avatar'] == 1){
        $jsst_avatar_url = get_avatar_url($jsst_uid, array('size' => 96));
    } else {
        $jsst_avatar_url = "";
    }

    // Check if the avatar URL is valid
    if (!empty($jsst_avatar_url) && @getimagesize($jsst_avatar_url)) {
        // Use WordPress's get_avatar function to generate the avatar HTML
        return get_avatar($jsst_uid, 96, '', '', array('class' => $jsst_class));
    } else {
        // Fallback to the default image if the avatar URL is invalid
        return '<img alt="' . esc_html(__('image', 'js-support-ticket')) . '" src="' . esc_url($jsst_defaultImage) . '" class="' . esc_attr($jsst_class) . '" />';
    }
}

function JSSTCheckPluginInfo($jsst_slug){
    if(file_exists(WP_PLUGIN_DIR . '/'.$jsst_slug) && is_plugin_active($jsst_slug)){
        $jsst_text = esc_html(__("Activated","js-support-ticket"));
        $jsst_disabled = "disabled";
        $jsst_class = "js-btn-activated";
        $jsst_availability = "-1";
    }else if(file_exists(WP_PLUGIN_DIR . '/'.$jsst_slug) && !is_plugin_active($jsst_slug)){
        $jsst_text = esc_html(__("Active Now","js-support-ticket"));
        $jsst_disabled = "";
        $jsst_class = "js-btn-green js-btn-active-now";
        $jsst_availability = "1";
    }else if(!file_exists(WP_PLUGIN_DIR . '/'.$jsst_slug)){
        $jsst_text = esc_html(__("Install Now","js-support-ticket"));
        $jsst_disabled = "";
        $jsst_class = "js-btn-install-now";
        $jsst_availability = "0";
    }
    return array("text" => $jsst_text, "disabled" => $jsst_disabled, "class" => $jsst_class, "availability" => $jsst_availability);
}

// =========================================================================
// ADD SETTINGS LINK TO PLUGIN PAGE
// =========================================================================
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'jsst_add_plugin_action_links');

function jsst_add_plugin_action_links($links) {
    /* Named for what it does rather than for who supplies it. (Roadmap
       6.0-AI-01) The link said "Zywrap AI" and went to that engine's dashboard,
       which was accurate while Zywrap was the only engine and is misleading now
       that a site can be running its own local model - and it is the plugins
       list, so it is the first thing a new administrator reads about this. */
    $settings_url = admin_url('admin.php?page=configuration');
    $aiagent_url = admin_url('admin.php?page=aiagent');

    $settings_link = '<a href="' . esc_url($settings_url) . '" style="font-weight: 600; color: #2563eb;">' . esc_html__('Settings', 'js-support-ticket') . '</a>';

    $aiagent_link = '<a href="' . esc_url($aiagent_url) . '" style="font-weight: 600; color: #2563eb;">' . esc_html__('AI Agent', 'js-support-ticket') . '</a>';

    // Added to the beginning of the array so they appear first.
    array_unshift($links, $settings_link);
    array_unshift($links, $aiagent_link);

    /* License & Add-ons, first: on the Plugins screen that is what an
       administrator who has just bought a plan is looking for. (2 Oct 2026) */
    if (current_user_can('manage_options')) {
        array_unshift($links, '<a href="' . esc_url(admin_url('admin.php?page=jssupportticket&jstlay=license')) . '" style="font-weight: 600; color: #2563eb;">' . esc_html__('License & Add-ons', 'js-support-ticket') . '</a>');
    }

    return $links;
}

// =========================================================================
// LOAD PLUGIN TRANSLATIONS (AUTO-FALLBACK TO PLUGIN LANGUAGES FOLDER)
// =========================================================================
/* At init, not plugins_loaded. Since WordPress 6.7 this only records where the
   files are; registering it earlier meant anything asking for one of our strings
   before the theme was set up raised "Translation loading for the
   js-support-ticket domain was triggered too early" (reported on activation,
   30 Sep 2026). Priority 0, so the strings are ready for the rest of init. */
add_action('init', 'jsst_load_plugin_textdomain', 0);

function jsst_load_plugin_textdomain() {
    $domain = 'js-support-ticket'; // Your exact text domain
    
    // Path to your plugin's languages folder
    $languages_path = dirname(plugin_basename(__FILE__)) . '/languages/';
    
    // WordPress will check the global WP languages folder first.
    // If not found, it will automatically look inside your plugin's $languages_path.
    load_plugin_textdomain($domain, false, $languages_path);
}

?>
