<?php

/*
  Plugin Name: JS Help Desk – AI-Powered Support & Ticketing System
  Plugin URI: https://www.jshelpdesk.com
  Description: JS Help Desk is a trusted open source ticket system. JS Help Desk is a simple, easy to use, web-based customer support system. User can create ticket from front-end. JS Help Desk comes packed with lot features than most of the expensive(and complex) support ticket system on market. JS Help Desk provide you best industry help desk system.
  Author: JS Help Desk
  Version: 4.0.0
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
                $jsst_addon_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_plugin_name);
            }
        }
        self::$_active_addons = $jsst_addon_array;
        // above code is its right place



        self::includes();
        self::jsstLoadWpCoreFiles();
        self::registeractions();
        JSSTmergedaddon::register(); // Roadmap 4.0-CORE-19
        JSSTprivacy::register();     // Roadmap 4.0-SEC-02
        JSSTdraft::registerHooks();  // Roadmap 4.0-UX-04
        JSSTpresence::registerHooks(); // Roadmap 4.0-UX-05
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
        self::$_currentversion = '400';
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
        add_action( 'jsst_process_transation_key_status', array($this , 'jshd_process_transation_key_status') );
        if( !wp_next_scheduled( 'jsst_process_transation_key_status' ) ) {
            // Schedule the event
            wp_schedule_event( time(), 'daily', 'jsst_process_transation_key_status' );
        }
        add_action( 'jsst_auto_update_addons', array($this , 'jshd_auto_update_addons') );
        if( !wp_next_scheduled( 'jsst_auto_update_addons' ) ) {
            // Schedule the event
            wp_schedule_event( time(), 'daily', 'jsst_auto_update_addons' );
        }
        add_action( 'upgrader_process_complete', array($this , 'jssupportticket_upgrade_completed'), 10, 2 );
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
        add_action('admin_notices', array($this , 'jsst_show_expiry_error_notice') );
        /* remove this in the 3.0.3 */
        add_action('admin_notices', array($this , 'jsst_show_addon_update_global_notice') );
        /* remove this in the 3.0.3 */
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
                    JSSTupdates::checkUpdates('400');
                    JSSTincluder::getJSModel('jssupportticket')->updateColorFile();
                    JSSTincluder::getJSModel('jssupportticket')->jsst_check_license_status();
                    JSSTincluder::getJSModel('jssupportticket')->JSSTAddonsAutoUpdate();
                }
            }
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
                        die( 'Security check Failed' );
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
                        die( 'Security check Failed' );
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
                        $jsst_timecookies['ticket_time_start'][$jsst_ticketid] = gmdate("Y-m-d h:i:s");
                    }else{
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('ticket')->validateTicketDetailForStaff($jsst_ticketid);
                        if (jssupportticket::$jsst_data['permission_granted']) { // validation passed
                            if(in_array('timetracking', jssupportticket::$_active_addons)){
                                $jsst_timecookies['ticket_time_start'][$jsst_ticketid] = gmdate("Y-m-d h:i:s");
                            }
                        }
                    }
                } else { // user
                    if(current_user_can('jsst_support_ticket') || current_user_can('jsst_support_ticket_tickets')){
                        if(in_array('timetracking', jssupportticket::$_active_addons)){
                            $jsst_timecookies['ticket_time_start'][$jsst_ticketid] = gmdate("Y-m-d h:i:s");
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
     * Add any missing per-column FULLTEXT index on the instant-resolve source
     * tables. One option read once the site is up to date; the work itself runs
     * once per plugin version, because that is when a shipped schema or a newly
     * activated add-on can have introduced a table without them.
     */
    function jsst_repair_suggestion_indexes() {
        $jsst_version = (string) jssupportticket::$_currentversion;
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

    function jsst_show_expiry_error_notice() {
        // Check if the option is set and equals '1'
        if (get_option('jsst_show_key_expiry_msg') == '1') {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html(__('Your JS Support Ticket license key has expired or is invalid. Please update it to continue receiving support and updates.', 'js-support-ticket')); ?></p>
            </div>
            <?php
        }
    }

    function jsst_show_addon_update_global_notice() {
        $update_avaliable_for_addons = JSSTincluder::getJSModel('jssupportticket')->showUpdateAvaliableAlert();

        // Safety checks
        if (!current_user_can('manage_options')) {
            return;
        }

        if (empty($update_avaliable_for_addons)) {
            return;
        }

        ?>
        <div class="jsst-admin-alert notice notice-warning">
            <div class="jsst-alert-content">

                <div class="jsst-alert-icon">⚠️</div>

                <div class="jsst-alert-text">
                    <h2>
                        <?php echo esc_html__('Action Required: Addon Updates Needed', 'js-support-ticket'); ?>
                    </h2>

                    <p>
                        <?php echo esc_html__(
                            'Major changes have been introduced. All JS Support Ticket addons must be updated immediately to prevent system errors and broken functionality.',
                            'js-support-ticket'
                        ); ?>
                    </p>
                </div>

                <div class="jsst-alert-action">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=addonstatus')); ?>"
                       class="jsst-alert-btn">
                        <?php echo esc_html__('Update All Addons Now', 'js-support-ticket'); ?>
                    </a>
                </div>

            </div>
        </div>
        <?php
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

    function jshd_process_transation_key_status(){
        JSSTincluder::getJSModel('jssupportticket')->jsst_check_license_status();
    }

    function jshd_auto_update_addons() {
        JSSTincluder::getJSModel('jssupportticket')->jsst_check_license_status();
        JSSTincluder::getJSModel('jssupportticket')->JSSTAddonsAutoUpdate();
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

    function registeractions() {
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
        if (!isset($jsst_schedules['weekly'])) {
            $jsst_schedules['weekly'] = array('interval' => 604800, 'display' => esc_html(__('Once Weekly', 'js-support-ticket')));
        }
        if (!isset($jsst_schedules['monthly'])) {
            $jsst_schedules['monthly'] = array('interval' => 2635200, 'display' => esc_html(__('Once Monthly', 'js-support-ticket')));
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
        // Effective agent access, for auditing. (Roadmap 4.0-SEC-04)
        include_once __DIR__ . '/includes/classes/agentaccess.php';
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
        // The AI Copilot and the provider it talks to. Loaded outside is_admin()
        // because the copilot runs against tickets from wherever a reply is
        // written, not only from its settings screen. (Roadmap 4.0-AI-01)
        include_once __DIR__ . '/includes/classes/copilotprovider.php';
        include_once __DIR__ . '/includes/classes/copilot.php';
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
        $jsst_version = jssupportticket::$_config['productversion'];
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
        // List of all your plugin pages
        $jsst_plugin_pages = array(
            'jssupportticket','slug','ticket','fieldordering','agent','configuration','priority','status','thirdpartyimport','product','department','themes','reports','announcement','knowledgebase','email','systemerror','emailtemplate','translations','userfeild','cannedresponses','role','mail','banemail','banemaillog','emailpiping','export','feedback','postinstallation','faq','emailcc','agentautoassign','multiform','download','premiumplugin','shortcodes','help','helptopic','gdpr','copilot');
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
        if(jssupportticket::$_pageid != ''){
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

add_action( 'jsst_addon_update_date_failed', 'jsstaddonUpdateDateFailed' );
function jsstaddonUpdateDateFailed(){
    die();
}

add_filter('style_loader_tag', 'jsstW3cValidation', 10, 2);
add_filter('script_loader_tag', 'jsstW3cValidation', 10, 2);
function jsstW3cValidation($jsst_tag, $jsst_handle) {
    return jssupportticketphplib::JSST_preg_replace( "/type=['\"]text\/(javascript|css)['\"]/", '', $jsst_tag );
}

if(!empty(jssupportticket::$_active_addons)){
    require_once 'includes/addon-updater/jsstupdater.php';
    $jsst_JS_SUPPORTTICKETUpdater  = new JS_SUPPORTTICKETUpdater();
}

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
    // 1. Define the exact URL to your Zywrap Global Settings page
    $settings_url = admin_url('admin.php?page=configuration');
    $zywrap_url = admin_url('admin.php?page=zywrap&jstlay=zywrap');
    
    // 2. Create the HTML link safely
    $settings_link = '<a href="' . esc_url($settings_url) . '" style="font-weight: 600; color: #2563eb;">' . esc_html__('Settings', 'js-support-ticket') . '</a>';
    
    $zywrap_link = '<a href="' . esc_url($zywrap_url) . '" style="font-weight: 600; color: #2563eb;">' . esc_html__('Zywrap AI', 'js-support-ticket') . '</a>';

    // 3. Add it to the beginning of the array so it appears first
    array_unshift($links, $settings_link);
    array_unshift($links, $zywrap_link);
    
    return $links;
}

// =========================================================================
// LOAD PLUGIN TRANSLATIONS (AUTO-FALLBACK TO PLUGIN LANGUAGES FOLDER)
// =========================================================================
add_action('plugins_loaded', 'jsst_load_plugin_textdomain');

function jsst_load_plugin_textdomain() {
    $domain = 'js-support-ticket'; // Your exact text domain
    
    // Path to your plugin's languages folder
    $languages_path = dirname(plugin_basename(__FILE__)) . '/languages/';
    
    // WordPress will check the global WP languages folder first.
    // If not found, it will automatically look inside your plugin's $languages_path.
    load_plugin_textdomain($domain, false, $languages_path);
}

?>
