<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class jssupportticketadmin {

    function __construct() {
        add_action('admin_menu', array($this, 'mainmenu'));
        /* The retired field list, sent to the screen that replaced it. On
           admin_init because the module controllers run while the page is being
           drawn, and a redirect from there is a "headers already sent" warning
           and a blank screen rather than a redirect.

           Registered here rather than with the form class's other hooks, and
           that is not tidiness: while the Customer Experience add-on still
           ships its own copy of that class, the add-on's copy is the one that
           loads, and a hook added to core's copy would never be registered on
           any of the sites that most need this redirect. (Roadmap 6.5-FORM-02) */
        add_action('admin_init', array($this, 'retireFieldList'), 1);
        /* The availability address the agent menu used to offer, sent to the
           screen that has always been the real one. Same hook and the same
           reason as above: a redirect out of a module controller runs after
           `admin-header.php` and is a pair of "headers already sent" warnings
           rather than a redirect. (Roadmap 4.5-FE-06) */
        add_action('admin_init', array($this, 'retireDeskAvailability'), 1);
    }

    /**
     * page=fieldordering's listing is now page=multiform's Forms screen.
     *
     * The address is kept rather than dropped - it is in bookmarks, in support
     * threads and in this plugin's own older screenshots. `adduserfeild` is
     * deliberately not caught: that layout is the question editor, which is
     * still that module's and still where both screens have always sent people.
     *
     * Neither is anything but `fieldfor` 1. That parameter is which list you
     * asked for, and only one of them was retired: 1 is the ticket field list
     * the Forms screen replaced, 2 is Feedback Fields, which was never retired
     * and is linked from the side menu under Feedback to this day. Without this
     * check the retirement swallowed that menu item whole - clicking Feedback
     * Fields landed on the ticket Forms screen, with no feedback question
     * anywhere on it and nothing saying what had happened. (Roadmap 6.5-FORM-02)
     */
    function retireFieldList() {
        if (wp_doing_ajax() || JSSTrequest::getVar('page') !== 'fieldordering') {
            return;
        }
        $jsst_layout = (string) JSSTrequest::getVar('jstlay');
        if ($jsst_layout !== '' && $jsst_layout !== 'fieldordering') {
            return;
        }
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        if ($jsst_fieldfor !== '' && $jsst_fieldfor !== null && absint($jsst_fieldfor) !== 1) {
            return;
        }
        $jsst_formid = absint(JSSTrequest::getVar('formid'));
        wp_safe_redirect(admin_url('admin.php?page=multiform&jstlay=forms'
            . ($jsst_formid > 0 ? '&formid=' . $jsst_formid : '')));
        exit;
    }

    /**
     * `page=jssupportticket&jstlay=availability` never existed.
     *
     * The agent left menu linked to it until 6.5 and the jssupportticket
     * controller has no case for it, so it fell through that switch to
     * `default: exit;` and rendered as a blank white page - no message, no
     * menu, nothing anywhere saying why. The screen it meant is the agents
     * add-on's, which has shown an agent their own row since it was written;
     * the menu points there now and this catches the link that does not.
     *
     * Kept rather than dropped for the same reason `fieldordering` is: the
     * address is in bookmarks and in the browser history of every agent who
     * ever clicked it. Where the add-on is absent there is no availability
     * screen to send anybody to, so this leaves the request alone.
     */
    function retireDeskAvailability() {
        if (wp_doing_ajax() || JSSTrequest::getVar('page') !== 'jssupportticket') {
            return;
        }
        if ((string) JSSTrequest::getVar('jstlay') !== 'availability') {
            return;
        }
        /* Asked of the screen rather than of the add-on list, which is what
           the paragraph above already says this does. The two answers differ on
           exactly one kind of site and it is a common one: the old
           Auto-Assign add-on is a model with no availability screen in it - that
           screen is a 4.5 feature written into the Agents add-on - so the list
           says yes and there is still nowhere to send anybody.
           (Roadmap 6.5-ECO-01) */
        if (!JSSTincluder::screenExists('agentautoassign', 'availability')) {
            return;
        }
        wp_safe_redirect(admin_url('admin.php?page=agentautoassign&jstlay=availability'));
        exit;
    }

    function mainmenu() {
        /* An agent an administrator has put on the front-end desk only gets no
           help desk in wp-admin - and gets it by the pages never being
           registered rather than by the menu being hidden. WordPress refuses
           admin.php?page=ticket for a page it has no menu entry for, so this
           one condition is the enforcement for every screen behind it rather
           than a decoration in front of them. The tasks those screens post to
           are refused separately, in the form handler.
           (Roadmap 4.5-FE-11) */
        if (class_exists('JSSTworkspace') && !JSSTworkspace::mayUse(JSSTworkspace::SHELL_BACKEND)) {
            return;
        }
        if (current_user_can('jsst_support_ticket')) {
            $jsst_unresolved_tickets = JSSTincluder::getJSModel('ticket')->getUnresolvedAdminTicketsCount();
            $jsst_count_str = '';
            if ($jsst_unresolved_tickets > 0) {
                $jsst_count_str = ' <span class="update-plugins"><span class="plugin-count">' . $jsst_unresolved_tickets . '</span></span>';
            }
            add_menu_page(esc_html(__('JS Help Desk Control Panel', 'js-support-ticket')), // Page title
                    esc_html(__('Help Desk', 'js-support-ticket')). $jsst_count_str, // menu title
                    'jsst_support_ticket', // capability
                    'jssupportticket', //menu slug
                    array($this, 'showAdminPage'), // function name
                    JSST_PLUGIN_URL.'includes/images/admin_ticket.png',23
            );
            add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Dashboard', 'js-support-ticket')), // Page title
                    esc_html(__('Dashboard', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'jssupportticket', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Slug', 'js-support-ticket')), // Page title
                    esc_html(__('Slug', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'slug', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Tickets', 'js-support-ticket')), // Page title
                    esc_html(__('Tickets', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'ticket', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            /* Create Ticket. (Roadmap 5.1-NAV-04)

               This menu is now five entries: the things somebody wants to
               reach from OUTSIDE the plugin - while writing a post, in
               Plugins, in Settings - because inside the desk the plugin's own
               sidebar is better at everything. It used to be fourteen, nine of
               which were configuration screens you open once, and it read as a
               rival menu rather than as shortcuts.

               Creating a ticket is the most common thing anybody does here and
               it was the one action missing from the list. The other nine moved
               to the hidden bucket rather than being deleted: every page still
               registers, every URL still resolves, and they are all one section
               away in the desk's own menu. */
            add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Create Ticket', 'js-support-ticket')), // Page title
                    esc_html(__('Create Ticket', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'admin.php?page=ticket&jstlay=addticket', //menu slug
                    null // a direct link: the page is Tickets', already registered above
            );
            /* Next after Tickets, because what the form asks is what every
               ticket is made of - it belongs beside them rather than below the
               half-dozen settings screens it used to sit under.
               "Fields" is gone from this slot. It was the entry a desk without
               the Multiform add-on got instead of "Forms", pointing at a
               second, older editor for the same thing - and the two had drifted
               far enough apart that which one you saw decided whether you could
               delete a question. There is one screen now and it is called Forms
               on every desk, and every desk gets it: there is no longer a
               version of it to be missing, and what the add-on adds is the
               register at the top of it. Multiform is the add-on's name, not
               the feature's, and this menu was the last place a customer met
               it. page=fieldordering still resolves, and redirects here.
               (Roadmap 6.5-FORM-02) */
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__("Forms", 'js-support-ticket')), // Page title
                    esc_html(__("Forms", 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'multiform', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            if(in_array('agent', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Agents', 'js-support-ticket')), // Page title
                        esc_html(__('Agents', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'agent', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('agent');
            }
            /* License & Add-ons, for the administrator who looks in
               WordPress's own menu first. A direct link, like Create Ticket:
               the page is the Dashboard's. The "!" is the sidebar's: a key
               that is stored and not active, or add-ons switched off.
               (2 October 2026) */
            if (current_user_can('manage_options') && class_exists('JSSTlicense')) {
                $jsst_lp_mark = ((JSSTlicense::hasKey() && !JSSTlicense::isActive())
                    || (class_exists('JSSTlicencegate') && array() !== JSSTlicencegate::gated()))
                    ? ' <span class="update-plugins"><span class="plugin-count">!</span></span>' : '';
                add_submenu_page('jssupportticket', // parent slug
                        esc_html(__('License & Add-ons', 'js-support-ticket')), // Page title
                        esc_html(__('License & Add-ons', 'js-support-ticket')) . $jsst_lp_mark, // menu title
                        'manage_options', // capability
                        'admin.php?page=jssupportticket&jstlay=license', //menu slug
                        null // a direct link
                );
            }
            add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Configurations', 'js-support-ticket')), // Page title
                    esc_html(__('Configurations', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'configuration', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    /* Off the menu since 6.0-AI-01, still registered because
                       the Prompt Lab lives on it and the engine's own screens
                       stay reachable. Titled for the group it now belongs to,
                       so a browser tab does not name a product the site may not
                       even be using.

                       The parent slug said `jssupportticket` until 6.5, so it
                       was on the menu the whole time this comment said it was
                       not - one word, and the difference between a retired
                       screen and a listed one. */
                    esc_html(__('AI Agent', 'js-support-ticket')), // Page title
                    esc_html(__('AI Agent', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'zywrap', //menu slug
                    array($this, 'showAdminPage') // function name
            ); 
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Priorities', 'js-support-ticket')), // Page title
                    esc_html(__('Priorities', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'priority', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Statuses', 'js-support-ticket')), // Page title
                    esc_html(__('Status', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'status', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Import Data', 'js-support-ticket')), // Page title
                    esc_html(__('Import Data', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'thirdpartyimport', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Products', 'js-support-ticket')), // Page title
                    esc_html(__('product', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'product', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Department', 'js-support-ticket')), // Page title
                    esc_html(__('Departments', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'department', //menu slug
                    array($this, 'showAdminPage') // function name
            );
             add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Themes', 'js-support-ticket')), // Page title
                    esc_html(__('Themes', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'themes', //menu slug
                    array($this, 'showAdminPage') // function name
            );
             /* The plain slug, deliberately. Opening a report rather than the
                page of tiles is settled in the reports controller's default
                layout, not here.

                Carrying the layout in the menu slug - `reports&jstlay=...` -
                looks equivalent and is not: WordPress matches the current
                screen by `$_GET['page']` alone, so `reports` no longer matched
                any visible entry, the bare slug had to be registered again
                under the hidden parent to keep answering, and WordPress then
                resolved the parent of the screen you were looking at to that
                hidden menu - which closed the whole JS Help Desk menu the
                moment you clicked Reports. Translations and Shortcodes get
                away with it because their base page, `jssupportticket`, is the
                top-level menu itself and matches whatever the layout is. */
             add_submenu_page('jssupportticket', // parent slug
                    esc_html(__('Reports', 'js-support-ticket')), // Page title
                    esc_html(__('Reports', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'reports', //menu slug
                    array($this, 'showAdminPage') // function name
            );

              if(in_array('announcement', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Announcements', 'js-support-ticket')), // Page title
                        esc_html(__('Announcements', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'announcement', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('announcement');
            }
            if(in_array('knowledgebase', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Knowledge Base', 'js-support-ticket')), // Page title
                        esc_html(__('Knowledge Base', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'knowledgebase', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('knowledgebase');
            }
            /* The addon screens that came out of core with their features.
               Registered under the hidden parent, not the visible one: the
               plugin's own left menu is where these are reached from, and
               adding more rows to the WordPress submenu would bury the ones
               people use. Where the addon is not installed the slug still
               resolves - to the page that says what it is - so a bookmarked
               link and a menu entry left over from a deactivation both land
               somewhere that explains itself rather than on a blank screen.

               Four slugs rather than one per feature, because each feature
               went to the addon whose grown-up version it is: service levels
               to Ticket Overdue, automation to Auto Close, the hours to Time
               Tracking, and everything with no addon to go back to into JS
               Help Desk Pro. Collaboration is not here at all - it ships in
               the Agents addon and has no screen of its own - and satisfaction
               is not here either, because the Feedback addon already registers
               a page of its own further up this method.

               Time Tracking is on this list for the opposite reason to the
               other three: they had no wp-admin page before and neither did
               it, and a slug with no menu entry is not a screen WordPress will
               open. Left off, the Time link in the plugin's own left menu
               answers "Sorry, you are not allowed to access this page" - which
               reads as a permissions problem and is not one. (Roadmap
               4.5-PRO-01, 5.0-SLA-01, 5.0-AUT-01, 5.0-API-01, 5.0-ANA-02,
                5.0-ANA-04, 5.0-ANA-05, 4.5-FE-08, 4.5-FE-09) */
            $jsst_feature_addons = array(
                'overdue'      => __('Service Levels', 'js-support-ticket'),
                'autoclose'    => __('Automation', 'js-support-ticket'),
                'timetracking' => __('Time', 'js-support-ticket'),
                'pro'          => __('JS Help Desk Pro', 'js-support-ticket'),
                /* Paid Support is here for exactly the reason Time Tracking is:
                   it had no wp-admin page at all, and the balances, the ledger
                   and the adjustments 5.5 gives it need a slug WordPress will
                   actually open. (Roadmap 5.5-COM-04) */
                'paidsupport'  => __('Paid Support', 'js-support-ticket'),
                /* (Roadmap 5.5-SEC-01) Where the encryption key is, who has read
                   what, and what happens if the key is lost. Same reason as the
                   two above it: the add-on had no wp-admin page at all. */
                'privatecredentials' => __('Credentials', 'js-support-ticket'),
                /* (Roadmap 5.5-GLB-01) Which translations exist, which have
                   gone stale, and which have lost a placeholder. */
                'multilanguageemailtemplates' => __('Email Languages', 'js-support-ticket'),
            );
            /* Asked of the filesystem rather than of `$_active_addons`, because
               on this particular list the two questions come apart. Six of the
               seven are add-ons that never shipped a wp-admin screen: the
               screens named here are 5.0 and 5.5 features, written into the
               bundles, and the old add-on is a model and nothing else. So a
               site running the old add-on without the new bundle answered yes
               to "is overdue active" - it is, it still marks tickets overdue -
               and `page=overdue` was registered to a controller that is not
               there. The includer fell through to the premium-addons
               controller, which exits on a layout that is not one of its own,
               and the customer got a white page.

               Registering the missing-addon screen instead is the same answer
               this loop already gives a customer whose add-on is switched off,
               and it is the right one here too: the feature is bought, the code
               for it is not installed, and the page says so. (Roadmap
               6.5-ECO-01) */
            foreach ($jsst_feature_addons as $jsst_addon_slug => $jsst_addon_title) {
                if (JSSTincluder::screenExists($jsst_addon_slug)) {
                    add_submenu_page('jssupportticket_hide', // parent slug
                            esc_html($jsst_addon_title), // Page title
                            esc_html($jsst_addon_title), // menu title
                            'jsst_support_ticket', // capability
                            $jsst_addon_slug, //menu slug
                            array($this, 'showAdminPage') // function name
                    );
                } else {
                    $this->addMissingAddonPage($jsst_addon_slug);
                }
            }
            /* And the bundles that took those screens over. (Roadmap 6.5-ECO-01)

               A bundle is normally invisible to this method: it carries its
               merged add-ons' modules, and those keep the slugs they always had,
               so `page=overdue` and `page=faq` are registered by the loops above
               exactly as they were before the bundle existed. The four bundles
               that took screens out of the Pro companion are the exception,
               because those screens never had a module each - all ten were
               `page=pro&jstlay=<layout>` - so the bundle itself has to be a
               page.

               Registered from `screenBundles()` rather than from a list written
               here, so that the manifest in `JSSTbundle` stays the one place
               that says which bundle draws what. And there is no
               `addMissingAddonPage()` branch: a bundle that is not installed is
               not a missing add-on somebody bought, it is a screen the Pro
               companion is still serving under `page=pro`, which is registered
               above and answers already. */
            if (class_exists('JSSTbundle')) {
                foreach (JSSTbundle::screenBundles() as $jsst_bundle_slug) {
                    $jsst_bundle_row = JSSTbundle::bundle($jsst_bundle_slug);
                    if ($jsst_bundle_row === false) {
                        continue;
                    }
                    add_submenu_page('jssupportticket_hide', // parent slug
                            esc_html($jsst_bundle_row['label']), // Page title
                            esc_html($jsst_bundle_row['label']), // menu title
                            'jsst_support_ticket', // capability
                            $jsst_bundle_slug, //menu slug
                            array($this, 'showAdminPage') // function name
                    );
                }
            }
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Emails', 'js-support-ticket')), // Page title
                    esc_html(__('System Emails', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'email', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('System Error', 'js-support-ticket')), // Page title
                    esc_html(__('System Errors', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'systemerror', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Email Templates', 'js-support-ticket')), // Page title
                    esc_html(__('Email Templates', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'emailtemplate', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('User Fields', 'js-support-ticket')), // Page title
                    esc_html(__('User Fields', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'userfeild', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            if(JSSTmergedaddon::featureEnabled('cannedresponses')){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Canned Responses', 'js-support-ticket')), // Page title
                        esc_html(__('Canned Responses', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'cannedresponses', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('cannedresponses');
            }

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Roles', 'js-support-ticket')), // Page title
                    esc_html(__('Roles', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'role', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            if(in_array('mail', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Mail', 'js-support-ticket')), // Page title
                        esc_html(__('Mail', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'mail', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('mail');
            }

            // The block list and its log are both core from 4.0, so both pages are
            // registered whether or not the add-on is installed.
            // (Roadmap 4.0-CORE-12)
            if(JSSTmergedaddon::featureEnabled('banemail')){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Blocked Senders', 'js-support-ticket')), // Page title
                        esc_html(__('Blocked Senders', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'banemail', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('banemail');
            }
            if(JSSTmergedaddon::featureEnabled('banemail')){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Ban list log', 'js-support-ticket')), // Page title
                        esc_html(__('Ban list log', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'banemaillog', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('banemaillog');
            }
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Field Ordering', 'js-support-ticket')), // Page title
                    esc_html(__('Field Ordering', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'fieldordering', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            if(in_array('emailpiping', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('JS Help Desk', 'js-support-ticket')), // Page title
                        esc_html(__('Email Piping', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'emailpiping', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('emailpiping');
            }


            /* The AI Agent: the overview, the switches and the audit that used
               to be three menus. (Roadmap 6.0-AI-01) The Copilot's own screen is
               registered below it and stays routable — it left the menu when
               these took over its settings, it did not leave the product. */
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('AI Agent', 'js-support-ticket')), // Page title
                    esc_html(__('AI Agent', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'aiagent', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            /* Live chat, when its add-on is installed. (Roadmap 6.0-CH-01)
               Registered under the hidden parent like the AI Agent above it:
               the sidemenu is what links to these, and a second listing in
               WordPress's own menu would put the same screen in two places.
               Registered at all only when the add-on is active, because an
               unregistered page= is refused by WordPress outright - which is
               the enforcement, not merely the hiding. */
            if (in_array('livechat', jssupportticket::$_active_addons)) {
                add_submenu_page('jssupportticket_hide',
                        esc_html(__('Live Chat', 'js-support-ticket')),
                        esc_html(__('Live Chat', 'js-support-ticket')),
                        'jsst_support_ticket',
                        'livechat',
                        array($this, 'showAdminPage')
                );
            }

            /* There is no page=copilot any more. Its screen asked for a
               provider, a key, a model and a language, and four of those five
               fields are now on AI Agent Settings writing the same options -
               two screens writing one store in two vocabularies is how they
               come to disagree, which is the lesson 4.5-FE-03 was merged for.
               The Copilot itself is untouched: its class, its four actions and
               its admin-ajax model all still serve the buttons on a ticket. */

            if(JSSTmergedaddon::featureEnabled('export')){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Export', 'js-support-ticket')), // Page title
                        esc_html(__('Export', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'export', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('export');
            }

            if(in_array('feedback', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Feedback', 'js-support-ticket')), // Page title
                        esc_html(__('Feedback', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'feedback', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('feedback');
            }
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Post Installation', 'js-support-ticket')), // Page title
                    esc_html(__('Post Installation', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'postinstallation', //menu slug
                    array($this, 'showAdminPage') // function name
            );

           if(in_array('faq', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__("FAQs", 'js-support-ticket')), // Page title
                        esc_html(__("FAQs", 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'faq', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('faq');
            }

            if(in_array('emailcc', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__("Email CC", 'js-support-ticket')), // Page title
                        esc_html(__("Email CC", 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'emailcc', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('emailcc');
            }

            if(in_array('agentautoassign', jssupportticket::$_active_addons)){
                /* Registered, not listed. (Roadmap 5.0-AUT-02)

                   The rules this screen edited are Automation rules from 6.5
                   and the screen redirects there, so listing it here would be
                   a second route to a page that only forwards. It stays
                   registered because an address somebody has bookmarked has to
                   keep answering, and an unregistered page does not. */
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__("Agent Auto Assign", 'js-support-ticket')), // Page title
                        esc_html(__("Agent Auto Assign", 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'agentautoassign', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('agentautoassign');
            }


            if(in_array('download', jssupportticket::$_active_addons)){
                add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Downloads', 'js-support-ticket')), // Page title
                    esc_html(__('Downloads', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'download', //menu slug
                    array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('download');
            }

            /* No page registration for the AI Agent add-on. (Roadmap 6.0-AI-02)
               Its two screens are layouts of core's `aiagent` page, which is
               registered below and exists whether or not the add-on does -
               registering a second page under the same slug here would either
               collide with core's or, when the add-on is absent, replace core's
               AI screens with the missing-addon up-sell. */

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Install Add-ons', 'js-support-ticket')), // Page title
                    esc_html(__('Install Add-ons', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'premiumplugin', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Shortcodes', 'js-support-ticket')), // Page title
                    esc_html(__('Shortcodes', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'jssupportticket&jstlay=shortcodes', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Help', 'js-support-ticket')), // Page title
                    esc_html(__('Help', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'jssupportticket&jstlay=help', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            // adddons mpage code.



            // if(in_array('knowledgebase', jssupportticket::$_active_addons)){
            //     add_submenu_page('jssupportticket', // parent slug
            //         esc_html(__('Knowledge Base', 'js-support-ticket')), // Page title
            //         esc_html(__('Knowledge Base', 'js-support-ticket')), // menu title
            //         'jsst_support_ticket', // capability
            //         'knowledgebase', //menu slug
            //         array($this, 'showAdminPage') // function name
            //     );
            // }

            if(JSSTmergedaddon::featureEnabled('helptopic')){
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Ticket Topics', 'js-support-ticket')), // Page title
                        esc_html(__('Ticket Topics', 'js-support-ticket')), // menu title
                        'jsst_support_ticket', // capability
                        'helptopic', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }else{
                $this->addMissingAddonPage('helptopic');
            }

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Themes', 'js-support-ticket')), // Page title
                    esc_html(__('Themes', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'themes', //menu slug
                    array($this, 'showAdminPage') // function name
            );

            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('GDPR', 'js-support-ticket')), // Page title
                    esc_html(__('GDPR', 'js-support-ticket')), // menu title
                    'jsst_support_ticket', // capability
                    'gdpr', //menu slug
                    array($this, 'showAdminPage') // function name
            );

        }else{
            /* The agent menu. Everything above is gated on jsst_support_ticket,
               which agents do not hold, so without this branch they get a single
               unnamed entry and nothing else. (Roadmap 4.0-SEC-04) */
            add_menu_page(esc_html(__('JS Help Desk Control Panel', 'js-support-ticket')), // Page title
                    esc_html(__('JS Help Desk', 'js-support-ticket')), // menu title
                    'jsst_support_ticket_tickets', // capability
                    'ticket', //menu slug
                    array($this, 'showAdminPage'), // function name
                  JSST_PLUGIN_URL.'includes/images/admin_ticket.png', 26
            );
            add_submenu_page('ticket', // parent slug
                    esc_html(__('Tickets', 'js-support-ticket')), // Page title
                    esc_html(__('Tickets', 'js-support-ticket')), // menu title
                    'jsst_support_ticket_tickets', // capability
                    'ticket', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            /* Writing the replies you send over and over is the agent's own
               work, not administration, so the screen belongs on their menu. */
            if (JSSTmergedaddon::featureEnabled('cannedresponses')) {
                add_submenu_page('ticket', // parent slug
                        esc_html(__('Canned Responses', 'js-support-ticket')), // Page title
                        esc_html(__('Canned Responses', 'js-support-ticket')), // menu title
                        'jsst_support_ticket_reply', // capability — authoring the reply library goes with sending replies
                        'cannedresponses', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }
            /* The desk's own screens that live on `page=jssupportticket`.
               (Roadmap 4.5-FE-02, 4.5-UX-02)

               Registered on a phantom parent, so the slug resolves without
               adding a row to the agent's menu - `jssupportticket_hide` is never
               a real page, which is the same trick the administrator branch
               above uses for a dozen screens.

               Without this, three of the destinations `JSSTnavigation` offers an
               agent - the desk home, Customers and Notifications - are all
               `page=jssupportticket&jstlay=...`, a slug registered only under
               `jsst_support_ticket`, which no agent role holds. WordPress
               answers "Sorry, you are not allowed to access this page", which
               reads as a permissions bug and is one: the desk was offering a
               link it had not made reachable.

               The capability is the one the tickets screen already uses, and it
               is the *door* rather than the authority: every layout behind it
               either asks `current_user_can('manage_options')` for itself - all
               the settings and diagnostics screens do - or asks
               `JSSTcapability` for the specific thing it shows, the way
               `admin_customers` asks CUSTOMER_VIEW and `admin_notifications`
               narrows to the reader's own staff id. Opening the door does not
               open any of those. */
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('JS Help Desk', 'js-support-ticket')), // Page title
                    esc_html(__('JS Help Desk', 'js-support-ticket')), // menu title
                    'jsst_support_ticket_tickets', // capability
                    'jssupportticket', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            /* Reports, for the same reason and with the same shape.
               `JSSTcapability::REPORT_VIEW` maps to `jsst_support_ticket_tickets`,
               so every agent role holds it and `JSSTnavigation` puts Reports on
               the agent's menu - pointing at `page=reports&jstlay=overallreport`,
               a slug that until now was registered only under the administrator
               capability. WordPress answered "Sorry, you are not allowed to
               access this page", which is the desk offering a link it had not
               made reachable, exactly as the block above describes.

               The door only. Which reports an agent may read is decided in the
               reports controller, which holds them to the desk-wide summary and
               keeps the per-agent, per-customer and per-department breakdowns
               for administrators. (Roadmap 4.5-FE-02) */
            add_submenu_page('jssupportticket_hide', // parent slug
                    esc_html(__('Reports', 'js-support-ticket')), // Page title
                    esc_html(__('Reports', 'js-support-ticket')), // menu title
                    'jsst_support_ticket_tickets', // capability
                    'reports', //menu slug
                    array($this, 'showAdminPage') // function name
            );
            /* My Availability: "I am away next week" is a thing a person says
               about themselves, and the screen that says it already knows how
               to show one agent their own row - it is the administrator's
               roster screen with everything but you filtered out of it, and
               `agentautoassign`'s controller has done that filtering since
               4.5-FE-06. What was missing was the slug: the agent menu linked
               to `page=jssupportticket&jstlay=availability`, a layout that has
               never existed, and the jssupportticket controller's `default:
               exit;` rendered it as a blank white page. The link now goes to
               the real screen and this registers it. (Roadmap 4.5-FE-06) */
            if (in_array('agentautoassign', jssupportticket::$_active_addons)) {
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('My Hours & Leave', 'js-support-ticket')), // Page title
                        esc_html(__('My Hours & Leave', 'js-support-ticket')), // menu title
                        'jsst_support_ticket_tickets', // capability
                        'agentautoassign', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }
            /* And the chat console, on the capability the navigation entry
               itself declares: answering a chat is answering a customer, so it
               goes with sending replies rather than with administration. */
            if (in_array('livechat', jssupportticket::$_active_addons)) {
                add_submenu_page('jssupportticket_hide', // parent slug
                        esc_html(__('Live Chat', 'js-support-ticket')), // Page title
                        esc_html(__('Live Chat', 'js-support-ticket')), // menu title
                        'jsst_support_ticket_reply', // capability
                        'livechat', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }
            /* The answers an agent gives repeatedly, written down once. Same
               argument as canned responses: writing the knowledge base is the
               agent's own work, not administration of the help desk. */
            if (in_array('knowledgebase', jssupportticket::$_active_addons)) {
                add_submenu_page('ticket', // parent slug
                        esc_html(__('Knowledge Base', 'js-support-ticket')), // Page title
                        esc_html(__('Knowledge Base', 'js-support-ticket')), // menu title
                        'jsst_support_ticket_kb', // capability
                        'knowledgebase', //menu slug
                        array($this, 'showAdminPage') // function name
                );
            }
        }
    }

    function addMissingAddonPage($jsst_module_name){
        add_submenu_page('jssupportticket_hide', // parent slug
                esc_html(__('Premium Addon', 'js-support-ticket')), // Page title
                esc_html(__('Premium Addon', 'js-support-ticket')), // menu title
                'jsst_support_ticket', // capability
                $jsst_module_name, //menu slug
                array($this, 'showMissingAddonPage') // function name
        );
    }

    function showAdminPage() {
        $jsst_page = JSSTrequest::getVar('page');
        JSSTincluder::include_file($jsst_page);
    }

    function showMissingAddonPage() {
        JSSTincluder::include_file('admin_missingaddon','premiumplugin');
    }

}

$jsst_jssupportticketAdmin = new jssupportticketadmin();
?>
