<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTincluder {

    function __construct() {

    }

    /*
     * Includes files
     */

    public static function include_file($jsst_filename, $jsst_module_name = null) {
        $allowed_modules = array(
            'activitylog','attachment','configuration','department','email','emailtemplate','fieldordering','gdpr','jssupportticket','postinstallation','premiumplugin','priority','product','reply','reports','slug','status','systemerror','themes','thirdpartyimport','ticket','actions','agent','role','roleaccessdepartments','rolepermissions','useraccessdepartments','userpermissions','agentautoassign','aipoweredreply','announcement','autoclose','banemail','banemaillog','cannedresponses','dashboardwidgets','download','zywrap','easydigitaldownloads','emailcc','emailpiping','envatovalidation','export','faq','feedback','helptopic','knowledgebase','mail','mailchimp','maxticket','mergeticket','multiform','multilanguageemailtemplates','note','notification','overdue','paidsupport','privatecredentials','smtp','sociallogin','themes','tickethistory','timetracking','useroptions','widgets','woocommerce','downloadattachment','articleattachmet','instantresolve','copilot','aiagent','livechat',
            /* JS Help Desk Pro, which carries the 4.5 and 5.0 features that had
               no addon of their own to go back to. The rest of those features
               ship in addons that are already on this list: service levels in
               overdue, automation in autoclose, collaboration in agent.

               A module name that is not on this list is refused here, which is
               the door every admin page goes through - so a screen whose slug
               is missing renders as a blank page with nothing anywhere saying
               why. (Roadmap 4.5-PRO-01) */
            'pro',

            /* The nine bundles. (Roadmap 6.5-ECO-01)

               Only four of them own screens - the ten that came out of the Pro
               companion, which never had a module each and had to be split
               somewhere. The other five carry their merged add-ons' modules
               unchanged, so their screens are still reached by the slugs already
               on this list: page=overdue, page=timetracking, page=paidsupport
               and the rest keep the addresses they have always had.

               All nine are listed anyway. A bundle that grows a screen later
               and is not on this list renders it as a blank page with nothing
               anywhere saying why, and that failure has cost this codebase a
               debugging session before. */
            'agents', 'servicelevels', 'emailsuite', 'knowledge',
            'experience', 'commerce', 'integrations', 'reporting'
        );

        if (
            null === $jsst_module_name &&
            ! in_array( $jsst_filename, $allowed_modules, true )
        ) {
            return;
        }
        $jsst_filename = jssupportticketphplib::JSST_clean_file_path($jsst_filename);
        $jsst_module_name = jssupportticketphplib::JSST_clean_file_path($jsst_module_name);
        if ($jsst_module_name != null) {
            $jsst_file_path = JSSTincluder::getPluginPath($jsst_module_name,'file',$jsst_filename);
            if (file_exists(JSST_PLUGIN_PATH . 'includes/css/inc-css/' . $jsst_module_name . '-' . $jsst_filename . '.css.php')) {
                require_once(JSST_PLUGIN_PATH . 'includes/css/inc-css/' . $jsst_module_name . '-' . $jsst_filename . '.css.php');
            }
            //include_once $jsst_path . 'modules/' . $jsst_module_name . '/tpls/' . $jsst_filename . '.php';
			if (locate_template('js-support-ticket/' . $jsst_module_name . '-' . $jsst_filename . '.php', 1, 1)) {
			   return;
			}

            if(file_exists($jsst_file_path)){
                include_once $jsst_file_path; //
            }else{
                $jsst_file_path = JSSTincluder::getPluginPath('premiumplugin','file','missingaddon');
                include_once $jsst_file_path; //
            }
        } else {
            $jsst_file_path = JSSTincluder::getPluginPath($jsst_filename,'file');
            if(file_exists($jsst_file_path)){
                include_once $jsst_file_path; //
            }else{
                $jsst_file_path = JSSTincluder::getPluginPath('premiumplugin','file');
                include_once $jsst_file_path; //
            }
        }
        return;
    }

    /**
     * Is there actually a screen behind this module and layout?
     *
     * Asked wherever a link to one is drawn, and answered by resolving the file
     * exactly as include_file() will resolve it a moment later - the same
     * Pro-first, bundle-next, add-on-last order through getPluginPath(), and the
     * same theme override ahead of all of it. A second opinion about where a
     * screen lives is a menu that disagrees with the router, which is the bug
     * this is here to end rather than a new place to introduce it.
     *
     * What it replaces at those call sites is
     * `in_array('<addon>', jssupportticket::$_active_addons)`. That is a true
     * answer to a different question. Several add-ons in that array are
     * model-only plugins - overdue, autoclose, timetracking, paidsupport,
     * privatecredentials, multilanguageemailtemplates - that never shipped a
     * wp-admin screen at all; the screens the menu names are 5.0 and 5.5
     * features written into the bundles. On a site running the old add-on and
     * not the new bundle the array says "overdue is active", which it is - it
     * still marks tickets overdue - while the Service Levels screen it was
     * being read as a proxy for is not there.
     *
     * And what the customer got was a white page, because every controller in
     * this product answers a layout it does not know with `default: exit;` -
     * core's, the bundles', and all twenty-seven add-ons' already in the field,
     * which is why this has to be fixed on the drawing side. A module with no
     * controller at all is the same failure one step earlier: include_file()
     * falls through to the premium-addons controller, and `admin_sla` is not
     * one of its layouts either. Both halves are checked here for that reason.
     * (Roadmap 6.5-ECO-01)
     *
     * $jsst_layout is the layout as it appears in the link - `sla`, not
     * `admin_sla`. The `admin_` prefix is applied here on the same condition
     * JSSTrequest::getLayout() applies it, so that caller and router are asking
     * about one file. Pass no layout to ask only whether the module has a
     * controller.
     */
    public static function screenExists($jsst_module, $jsst_layout = '') {
        $jsst_controller = JSSTincluder::getPluginPath($jsst_module, 'file');
        if ($jsst_controller == '' || !file_exists($jsst_controller)) {
            return false;
        }
        if ($jsst_layout == '') {
            return true;
        }
        if (is_admin()) {
            $jsst_layout = 'admin_' . $jsst_layout;
        }
        /* Looked up, not loaded: locate_template()'s second and third arguments
           are $load and $require_once, and include_file() passes 1 to both. */
        if (locate_template('js-support-ticket/' . $jsst_module . '-' . $jsst_layout . '.php', false, false)) {
            return true;
        }
        $jsst_tpl = JSSTincluder::getPluginPath($jsst_module, 'file', $jsst_layout);
        return ($jsst_tpl != '' && file_exists($jsst_tpl));
    }

    /*
     * Static function to handle the page slugs
     */

    public static function include_slug($jsst_page_slug) {
        include_once JSST_PLUGIN_PATH . 'modules/js-support-ticket-controller.php';
    }

    /*
     * Static function for the model object
     */

    public static function getJSModel($jsst_modelname) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_modelname,'model');
        if ($jsst_file_path !== '' && file_exists($jsst_file_path)) {
            include_once $jsst_file_path;
        }
        $jsst_classname = "JSST" . $jsst_modelname . 'Model';
        if (class_exists($jsst_classname)) {
            return new $jsst_classname();
        }
        /* Where else this module's model could be, asked only because the first
           answer was wrong. (Roadmap 6.5-ECO-01)
         *
         * `getPluginPath()` sends a bundled module to its bundle, and that is
         * the right answer on every ordinary request. It is not the right answer
         * during `activate_plugin()`: WordPress includes the plugin being
         * activated *before* it writes the new `active_plugins`, so for the
         * length of that one request a bundle can read as inactive while the
         * stand-alone add-on it replaces is still loading and still asking for
         * its own model. Resolution then falls through to core, which does not
         * ship a paid add-on's model, and the request died with
         * `Class "JSSTpaidsupportModel" not found` - on the Plugins screen,
         * mid-install, which is the worst possible moment.
         *
         * So the two places it can honestly be are tried in turn before giving
         * up: the stand-alone add-on's own directory, then the bundle that
         * carries it.
         */
        $jsst_tries = array(
            WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_modelname . '/module/model.php',
        );
        if (class_exists('JSSTbundle')) {
            $jsst_owner = JSSTbundle::owner($jsst_modelname);
            if ($jsst_owner !== '') {
                $jsst_tries[] = WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_owner
                    . '/modules/' . $jsst_modelname . '/model.php';
            }
        }
        foreach ($jsst_tries as $jsst_try) {
            if (file_exists($jsst_try)) {
                include_once $jsst_try;
                if (class_exists($jsst_classname)) {
                    return new $jsst_classname();
                }
            }
        }
        /* Still nothing. A model that is not on this site is a missing feature,
           not a reason to end the request - the caller is usually asking it for
           a count or a list to draw a screen with. An object that answers null
           to everything lets that screen come up empty, which is what a site
           without the add-on should look like anyway. */
        if (!class_exists('JSSTlegacyshimbase')) {
            return null;
        }
        return new JSSTlegacyshimbase();
    }



    /**
     * Send an old address to the screen that answers it now.
     *
     * The same headers-already-sent problem `refuse()` solves, and deliberately
     * not the same message. A retired address is not a refusal: the reader has
     * done nothing wrong and there is somewhere for them to go, so telling them
     * they are "not allowed" would be both untrue and a dead end. Bookmarks and
     * links in old support replies are exactly the traffic these addresses
     * exist to catch. (Roadmap 6.5-ECO-01)
     */
    public static function moved($jsst_url) {
        if (!headers_sent()) {
            wp_safe_redirect($jsst_url);
            exit;
        }
        /* Admin screens draw their controller after WordPress has printed the
           page head, so the header redirect above is usually too late. Forward
           in the browser instead; the link stays for anyone without script. */
        echo '<div class="notice notice-warning"><p>'
            . esc_html(__('This screen has moved.', 'js-support-ticket'))
            . ' <a href="' . esc_url($jsst_url) . '">'
            . esc_html(__('Open it here.', 'js-support-ticket'))
            . '</a></p></div>';
        echo '<script>window.location.replace(' . wp_json_encode(esc_url_raw($jsst_url)) . ');</script>';
        exit;
    }


    /**
     * Take every `jsst*` hook registration that cannot be called back off.
     *
     * A callback naming a method that does not exist has exactly one possible
     * behaviour: the first time anything fires that hook, PHP raises
     * `call_user_func_array(): Argument #1 ($callback) must be a valid
     * callback` and the request dies. It cannot do anything else. So removing
     * it costs nothing and is the whole of the fix.
     *
     * ## Why this is needed at all
     *
     * The old WooCommerce add-on registers three methods that were never
     * written - `jsst_ticket_save_wc_fields`, `jsst_show_order_ticket_detail`
     * and its admin twin. Nothing fired those hooks, so for years the dead
     * registrations sat in `$wp_filter` doing nothing. This release made core
     * emit `jsst_after_ticket_details`, and every ticket detail on a site with
     * that add-on and WooCommerce installed became a fatal - on the page a
     * customer opens most.
     *
     * The third is still armed: nothing emits `jsst_ticket_store` today, which
     * is exactly what was true of the other two last release. Fixing the two
     * that went off and leaving the one that has not is not a fix, it is a
     * postponement, so this sweeps them all.
     *
     * ## Why not suppress the add-on instead
     *
     * That was the first idea and it is wrong. Twenty-five superseded add-ons
     * on a site like this still hold twenty-eight `jsst*` hooks between them -
     * the Agents add-on alone holds ten - and the bundles that replace them
     * stood down, so their own boots never registered anything in their place.
     * Stripping the add-on's hooks would take working features away to remove
     * three broken registrations. Only the registrations that cannot possibly
     * work are removed, and every callable one is left exactly where it is.
     *
     * ## When
     *
     * `init` at PHP_INT_MAX: add-ons register these inside their own `init`
     * callbacks - the WooCommerce one is in a closure hooked at the default
     * priority - so anything earlier would sweep before they arrive. What is
     * removed is recorded for the System Status screen, because an add-on with
     * a dead callback is broken whether or not core is now stepping around it,
     * and silently papering over it helps nobody. (Roadmap 5.5-COM-01)
     */
    const OPT_DEAD_HOOKS = 'jsst_dead_hook_callbacks';

    public static function pruneDeadHooks() {
        global $wp_filter;
        if (!is_array($wp_filter) && !($wp_filter instanceof ArrayAccess)) {
            return array();
        }
        $jsst_removed = array();
        foreach ($wp_filter as $jsst_hook => $jsst_reg) {
            if (stripos($jsst_hook, 'jsst') !== 0) {
                continue;   /* this plugin's own hooks only - not WordPress's */
            }
            if (!is_object($jsst_reg) || !isset($jsst_reg->callbacks)) {
                continue;
            }
            $jsst_drop = array();
            foreach ($jsst_reg->callbacks as $jsst_priority => $jsst_set) {
                if (!is_array($jsst_set)) continue;
                foreach ($jsst_set as $jsst_entry) {
                    if (!isset($jsst_entry['function'])) continue;
                    /* is_callable() answers true for a class with __call(), so
                       an add-on routing through a magic method keeps its
                       registration. Only what PHP itself would refuse to
                       invoke is taken off. */
                    if (!is_callable($jsst_entry['function'])) {
                        $jsst_drop[] = array($jsst_entry['function'], $jsst_priority);
                    }
                }
            }
            foreach ($jsst_drop as $jsst_one) {
                remove_action($jsst_hook, $jsst_one[0], $jsst_one[1]);
                $jsst_removed[] = $jsst_hook . ' -> ' . self::describeCallback($jsst_one[0]);
            }
        }
        if ($jsst_removed) {
            update_option(self::OPT_DEAD_HOOKS, $jsst_removed, false);
        } elseif (get_option(self::OPT_DEAD_HOOKS, null) !== null) {
            delete_option(self::OPT_DEAD_HOOKS);
        }
        return $jsst_removed;
    }

    /** A dead callback, named so somebody can find the add-on it came from. */
    private static function describeCallback($jsst_callback) {
        if (is_string($jsst_callback)) {
            return $jsst_callback . '()';
        }
        if (is_array($jsst_callback) && count($jsst_callback) === 2) {
            $jsst_class = is_object($jsst_callback[0]) ? get_class($jsst_callback[0]) : (string) $jsst_callback[0];
            return $jsst_class . '::' . (string) $jsst_callback[1] . '()';
        }
        return 'closure';
    }

    /** What the last sweep removed, for the System Status screen. */
    public static function deadHooks() {
        $jsst_out = get_option(self::OPT_DEAD_HOOKS, array());
        return is_array($jsst_out) ? $jsst_out : array();
    }

    /**
     * Fire an action after dropping callbacks that cannot be called.
     *
     * The sweep above runs once on `init` and catches everything registered by
     * then. This is the backstop for the two actions this release invented, in
     * case something registers against them later than that - a fatal on the
     * ticket detail is worth paying one `is_callable()` per callback to avoid.
     */
    public static function prunedAction($jsst_hook, $jsst_arg = null) {
        global $wp_filter;
        if (isset($wp_filter[$jsst_hook]) && is_object($wp_filter[$jsst_hook])
                && isset($wp_filter[$jsst_hook]->callbacks)) {
            $jsst_drop = array();
            foreach ($wp_filter[$jsst_hook]->callbacks as $jsst_priority => $jsst_set) {
                if (!is_array($jsst_set)) continue;
                foreach ($jsst_set as $jsst_entry) {
                    if (isset($jsst_entry['function']) && !is_callable($jsst_entry['function'])) {
                        $jsst_drop[] = array($jsst_entry['function'], $jsst_priority);
                    }
                }
            }
            foreach ($jsst_drop as $jsst_one) {
                remove_action($jsst_hook, $jsst_one[0], $jsst_one[1]);
            }
        }
        do_action($jsst_hook, $jsst_arg);
    }

    /**
     * Turn somebody away from an admin screen without a page of PHP warnings.
     *
     * A module controller runs from inside the admin page callback, which is
     * after `admin-header.php` has been written out - so a plain
     * `wp_safe_redirect()` there is two `header()` calls against a response
     * that has already begun. PHP reports that as two "headers already sent"
     * warnings naming `pluggable.php`, and WordPress renders nothing else into
     * the response, so the reader gets two warnings and a blank page. Neither
     * names the screen or the reason, which is how a missing class came to be
     * reported as a header error on the Inbox and Languages screens.
     *
     * The redirect is still the right answer whenever it can still be sent.
     * When it cannot, this says so on the page instead of leaving a warning to
     * be read as the refusal.
     *
     * Here rather than copied into each controller because fifteen of them
     * need it and three had already grown their own. One copy is one wording,
     * and one place to fix it. (Roadmap 6.5-ECO-01)
     */
    public static function refuse($jsst_url) {
        if (!headers_sent()) {
            wp_safe_redirect($jsst_url);
            exit;
        }
        echo '<div class="notice notice-error"><p>'
            . esc_html(__('You are not allowed to open this screen.', 'js-support-ticket'))
            . ' <a href="' . esc_url($jsst_url) . '">'
            . esc_html(__('Go back to the help desk.', 'js-support-ticket'))
            . '</a></p></div>';
        exit;
    }

    /**
     * End a file download with a real 404.
     *
     * A download that is refused and a download of something that does not
     * exist answer the same way - the same status and the same page - so the
     * response cannot be used to find out which ids exist or belong to somebody
     * else. The theme's 404 template used to be included on its own, which sent
     * it with a 200 status, and the one thing that told the two cases apart
     * was the size of the body.
     */
    public static function notFound() {
        status_header(404);
        nocache_headers();
        $jsst_template = get_query_template('404');
        if ($jsst_template !== '') {
            include $jsst_template;
            exit;
        }
        wp_die(
            esc_html__('The file you asked for could not be found.', 'js-support-ticket'),
            esc_html__('Not Found', 'js-support-ticket'),
            array('response' => 404)
        );
    }

    /*
     * Static function for the classes objects
     */

    public static function getObjectClass($jsst_classname) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_classname,'class');

        include_once $jsst_file_path;
        $jsst_classname = 'JSST'.$jsst_classname;
        $jsst_obj = new $jsst_classname();
        return $jsst_obj;
    }

    public static function getClassesInclude($jsst_classname) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_classname,'class');
        include_once $jsst_file_path;
    }

    /*
     * Static function for the controller object
     */

    public static function getJSController($jsst_controllername) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_controllername,'controller');

        include_once $jsst_file_path;
        $jsst_classname = "JSST".$jsst_controllername . "Controller";
        $jsst_obj = new $jsst_classname();
        return $jsst_obj;
    }

    /*
     * Static function for the Table Class Object
     */

    public static function getJSTable($jsst_tableclass) {
        $jsst_file_path = JSSTincluder::getPluginPath($jsst_tableclass,'table');
        require_once JSST_PLUGIN_PATH . 'includes/tables/table.php';
        include_once $jsst_file_path;
        $jsst_classname = "JSST" . $jsst_tableclass . 'Table';
        $jsst_obj = new $jsst_classname();
        return $jsst_obj;
    }

    /*
     *  Identify file path to include or require this fucntion helps to accommodate addon calls
     */

    public static function getPluginPath($jsst_module,$jsst_type,$jsst_file_name = '') {
        $jsst_module = jssupportticketphplib::JSST_clean_file_path($jsst_module);
        $jsst_file_name = jssupportticketphplib::JSST_clean_file_path($jsst_file_name);

        /* A module the Pro companion is running is resolved into the companion,
           and asked before anything else here. It has to be first for two
           reasons. A Pro module is added to $_active_addons so that the
           availability checks written throughout the plugin let it through
           (see JSSTpro::augment()), and the branch below reads that same array
           as "there is a js-support-ticket-<slug> directory" — which for a Pro
           module there is not. And a module that a legacy add-on still owns must
           keep coming from the add-on, which is already settled here, because
           JSSTpro::modulePath() answers '' for it. (Roadmap 4.5-PRO-01) */
        if (class_exists('JSSTpro')) {
            $jsst_pro_file_path = JSSTpro::modulePath($jsst_module, $jsst_type, $jsst_file_name);
            if ($jsst_pro_file_path !== '') {
                return $jsst_pro_file_path;
            }
        }

        /* A module one of the nine bundles carries is resolved into that
           bundle, asked here for exactly the reasons the Pro branch above is
           asked here: a bundled module is added to $_active_addons so the
           availability checks written throughout the plugin let it through, and
           the branch below would then read that same array as "there is a
           js-support-ticket-<slug> directory" - which for a bundled module there
           is not.

           And it is asked *before* the stand-alone add-on branch on purpose:
           an installed bundle supersedes the add-on it replaces, so a site
           mid-upgrade with both is served entirely by the newer one. That is
           settled inside JSSTbundle::running() rather than here, and this
           branch's position is what makes it hold even for an old add-on that
           has never heard of bundles and does not stand down on its own.
           (Roadmap 6.5-ECO-01) */
        if (class_exists('JSSTbundle')) {
            $jsst_bundle_file_path = JSSTbundle::modulePath($jsst_module, $jsst_type, $jsst_file_name);
            if ($jsst_bundle_file_path !== '') {
                return $jsst_bundle_file_path;
            }
        }

        $jsst_addons_secondry = array('articles','articleattachmet','banemaillog','downloadattachment','roleaccessdepartments','rolepermissions','useraccessdepartments','userpermissions', 'role', 'acl_roles', 'acl_role_access_departments', 'acl_role_permissions', 'categories' ,'email_banlist', 'acl_user_access_departments','articles_attachments','email_banlist','acl_user_permissions', 'facebook', 'linkedin','socialUser');
		$jsst_new_addon_entry = "";
		$jsst_new_addon_entry = apply_filters('jsst_ticket_include_thirdparty_addon_in_array',$jsst_addons_secondry);
		if($jsst_new_addon_entry){
			$jsst_addons_secondry[] = $jsst_new_addon_entry;
		}
		$jsst_new_addon_layoutname = "";
		$jsst_new_addon_layoutname = apply_filters('jsst_ticket_include_thirdparty_addon_layoutname',false);

        // A merged capability's own module, and any sub-module of one, is served
        // from core whenever core ships the file. (Roadmap 4.0-CORE-19)
        //
        // A shared module is served the same way and for a different reason.
        // (Roadmap 6.0-AI-02) The branch below reads "there is a
        // js-support-ticket-<module> directory" as "the add-on owns this
        // module", which is true of every add-on written before 6.0 and false
        // of the AI Agent: core ships that module's controller, its model and
        // four of its six screens, and the add-on ships the engine behind the
        // other two. Without this the add-on's directory would win the whole
        // module the moment it was activated, and core's own AI screens - the
        // switchboard, the source register, the audit log - would resolve to
        // files that are not there.
        //
        // Deliberately NOT expressed as a merge. A merged capability is one core
        // owns outright and offers free, which brings the "now part of the free
        // core" notice, hook suppression and the uninstall snapshot with it.
        // None of that is true here: this is a paid add-on that happens to share
        // a menu with core. The only thing being borrowed is the file
        // resolution.
        $jsst_core_owns_file = false;
        $jsst_shared = in_array($jsst_module, self::sharedModules(), true);
        if ($jsst_shared || (class_exists('JSSTmergedaddon') && JSSTmergedaddon::mergedOwner($jsst_module) != '')) {
            $jsst_core_file = JSSTincluder::corePath($jsst_module, $jsst_type, $jsst_file_name);
            $jsst_core_owns_file = ($jsst_core_file != '' && file_exists($jsst_core_file));
        }

        if(in_array($jsst_module, jssupportticket::$_active_addons) && !$jsst_core_owns_file){
            $jsst_path = WP_PLUGIN_DIR.'/'.'js-support-ticket-'.$jsst_module.'/';
            switch ($jsst_type) {
                case 'file':
                    if($jsst_file_name != ''){
                        $jsst_file_path = $jsst_path . 'module/tpls/' . $jsst_file_name . '.php';
                    }else{
                        $jsst_file_path = $jsst_path . 'module/controller.php';
                    }
                    break;
                case 'model':
                    $jsst_file_path = $jsst_path . 'module/model.php';
                    break;
                case 'class':
                    $jsst_file_path = $jsst_path . 'classes/' . $jsst_module . '.php';
                    break;
                case 'controller':
                    $jsst_file_path = $jsst_path . 'module/controller.php';
                    break;
                case 'table':
                    $jsst_file_path = $jsst_path . 'includes/' . $jsst_module . '-table.php';
                    break;
            }


        }elseif(in_array($jsst_module, $jsst_addons_secondry) && !$jsst_core_owns_file){ // to handle the case of modules that are submodules for some addon
            $jsst_parent_module = '';
            switch ($jsst_module) {// to identify addon for submodules.
                case 'articles':
                case 'articleattachmet':
                case 'articles_attachments':
                case 'categories':
                    $jsst_parent_module = 'knowledgebase';
                    break;
                case 'banemaillog':
                case 'email_banlist':
                case 'email_banlist':
                    $jsst_parent_module = 'banemail';
                    break;
                case 'downloadattachment':
                    $jsst_parent_module = 'download';
                    break;
                case 'roleaccessdepartments':
                case 'rolepermissions':
                case 'useraccessdepartments':
                case 'userpermissions':
                case 'role':
                case 'acl_roles':
                case 'acl_role_access_departments':
                case 'acl_user_access_departments':
                case 'acl_role_permissions':
                case 'acl_user_permissions':
                    $jsst_parent_module = 'agent';
                    break;
                case 'facebook':
                case 'linkedin':
                case 'socialUser':
                    $jsst_parent_module = 'sociallogin';
                    break;
                case $jsst_new_addon_entry:
                    $jsst_parent_module = $jsst_new_addon_layoutname;
            }

            $jsst_path = WP_PLUGIN_DIR.'/'.'js-support-ticket-'.$jsst_parent_module.'/';
            if(in_array($jsst_parent_module, jssupportticket::$_active_addons)){
                switch ($jsst_type) {
                    case 'file':
                        if($jsst_file_name != ''){
                            $jsst_file_path = $jsst_path . $jsst_module.'/tpls/' . $jsst_file_name . '.php';
                        }else{
                            $jsst_file_path = $jsst_path . $jsst_module.'/controller.php';
                        }
                        break;
                    case 'model':
                        $jsst_file_path = $jsst_path . $jsst_module.'/model.php';
                        break;

                    case 'class':
                        $jsst_file_path = $jsst_path . 'classes/' . $jsst_module . '.php';
                        break;
                    case 'controller':
                        $jsst_file_path = $jsst_path . $jsst_module.'/controller.php';
                        break;
                    case 'table':
                        $jsst_file_path = $jsst_path . 'includes/' . $jsst_module . '-table.php';
                        break;
                }
            }elseif($jsst_type === 'model'){
                /* No file. The missing-add-on screen below is the answer to a
                   page request; given to a model lookup it was included as the
                   model, and including the premium-plugin controller renders
                   that screen - so asking whether a customer may edit a reply
                   printed "Page Not Found !!" into the middle of the ticket.
                   getJSModel() answers a missing model with its silent shim. */
                $jsst_file_path = '';
            }else{
                $jsst_file_path = JSSTincluder::getPluginPath('premiumplugin','file');
            }
        }else{
            $jsst_file_path = JSSTincluder::corePath($jsst_module, $jsst_type, $jsst_file_name);
        }
        return $jsst_file_path;
    }

    /*
     * Where core keeps a module's own file, whatever else is installed beside it.
     * Split out of getPluginPath() so the merged-add-on check above can ask
     * whether core ships a given file without duplicating these paths.
     * (Roadmap 4.0-CORE-19)
     */

    /**
     * Modules core and an add-on both put files into.
     *
     * Core wins for any file core ships; anything else falls through to the
     * add-on exactly as it always did. Keep this list short - a module in it is
     * a module two codebases have to agree about, and the only reason to accept
     * that cost is a feature whose free half and paid half are genuinely one
     * screen group. (Roadmap 6.0-AI-02)
     */
    public static function sharedModules() {
        /* `multiform` joined `aiagent` in 6.5 for the same reason: core ships
           the controller and the Forms screen, and the Customer Experience
           add-on ships the screen that adds and renames a form. Resolution is
           file by file, so each half comes from wherever it actually lives.
           (Roadmap 6.5-FORM-02) */
        return apply_filters('jsst_shared_modules', array('aiagent', 'multiform'));
    }

    public static function corePath($jsst_module, $jsst_type, $jsst_file_name = '') {
        $jsst_path = JSST_PLUGIN_PATH;
        $jsst_file_path = '';
        switch ($jsst_type) {
            case 'file':
                if($jsst_file_name != ''){
                    $jsst_file_path = $jsst_path . 'modules/' . $jsst_module . '/tpls/' . $jsst_file_name . '.php';
                }else{
                    $jsst_file_path = $jsst_path . 'modules/' . $jsst_module . '/controller.php';
                }
                break;
            case 'model':
                $jsst_file_path = $jsst_path . 'modules/' . $jsst_module . '/model.php';
                break;
            case 'class':
                $jsst_file_path = $jsst_path . 'includes/classes/' . $jsst_module . '.php';
                break;
            case 'controller':
                $jsst_file_path = $jsst_path . 'modules/' . $jsst_module . '/controller.php';
                break;
            case 'table':
                $jsst_file_path = $jsst_path . 'includes/tables/' . $jsst_module . '.php';
                break;
        }
        return $jsst_file_path;
    }

}

$jsst_includer = new JSSTincluder();
?>
