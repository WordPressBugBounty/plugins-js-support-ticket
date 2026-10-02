<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTjssupportticketController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'controlpanel');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_controlpanel':
                    /* The administrator's control panel, and it says so itself
                       rather than relying on the page it sits on. (Roadmap
                       4.0-SEC-04)

                       Until 6.5 this layout was protected only by the fact that
                       `page=jssupportticket` was registered under an
                       administrator capability - so the guard was in the menu
                       registration, a file away, and nothing here said so. The
                       moment that slug was opened to agents, so that the desk's
                       own Customers and Notifications screens could be reached
                       at all, this became the default layout an agent would
                       land on. Every other settings screen on this page already
                       asks `manage_options` for itself; this one now does too.

                       They are sent to the tickets list rather than to the
                       workspace home, and deliberately: the workspace home is
                       gated on QUEUE_VIEW and refuses through leaveTheDesk(),
                       which lands back here - a redirect loop for exactly the
                       agent who holds the fewest capabilities. `page=ticket` is
                       registered for every agent role and gates on nothing
                       further. */
                    if (!current_user_can('manage_options')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=ticket'));
                    }
			        include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
			        JSSTupdates::checkUpdates();
                    JSSTincluder::getJSModel('jssupportticket')->getControlPanelDataAdmin();
                    break;
                case 'controlpanel':
                    JSSTincluder::getJSModel('jssupportticket')->getControlPanelData();
                    include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
                    JSSTupdates::checkUpdates('500');
                    JSSTincluder::getJSModel('jssupportticket')->updateColorFile();
                    //JSSTincluder::getJSModel('jssupportticket')->getStaffControlPanelData();
                    break;
                // Who can do what, derived rather than described.
                // (Roadmap 4.0-SEC-04)
                case 'admin_agentaccess':
                    // Guarded because a bootstrap that has lost this include
                    // should cost one screen, not the whole site.
                    jssupportticket::$jsst_data['agentaccess'] = class_exists('JSSTagentaccess') ? JSSTagentaccess::report() : array();
                    break;
                /* Why one person may or may not do one thing, with the
                   reasoning the software actually used. Administrators only —
                   it enumerates who holds what. (Roadmap 4.5-ARCH-04) */
                case 'admin_permissioninspector':
                    if (!current_user_can('manage_options') || !class_exists('JSSTpermissioninspector')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    $jsst_inspectuser = (int) JSSTrequest::getVar('wpuid', '', 0);
                    $jsst_inspectref = trim((string) JSSTrequest::getVar('ticketref', '', ''));
                    $jsst_inspectticket = ($jsst_inspectref !== '') ? JSSTpermissioninspector::resolveTicket($jsst_inspectref) : 0;
                    jssupportticket::$jsst_data['inspectorpeople'] = JSSTpermissioninspector::people();
                    jssupportticket::$jsst_data['inspectorasked'] = $jsst_inspectuser;
                    jssupportticket::$jsst_data['inspectorref'] = $jsst_inspectref;
                    jssupportticket::$jsst_data['inspectornotfound'] = ($jsst_inspectref !== '' && $jsst_inspectticket === 0);
                    jssupportticket::$jsst_data['inspectorreport'] = JSSTpermissioninspector::report($jsst_inspectuser, $jsst_inspectticket);
                    break;
                /* The authorisation matrix. manage_options and nothing less:
                   it names every entry point together with what does and does
                   not guard it, which is a review document for the site owner
                   and a shopping list for anybody else. (Roadmap 5.0-SEC-01) */
                case 'admin_security':
                    if (!current_user_can('manage_options') || !class_exists('JSSTauthmatrix')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    $jsst_sckind = sanitize_text_field( JSSTrequest::getVar('sckind', '', '') );
                    $jsst_scgrade = sanitize_key( JSSTrequest::getVar('scgrade', '', '') );
                    $jsst_scsearch = sanitize_text_field( JSSTrequest::getVar('scsearch', '', '') );
                    $jsst_screport = JSSTauthmatrix::report();
                    jssupportticket::$jsst_data['scrows'] = JSSTauthmatrix::rows(array(
                        'kind' => $jsst_sckind,
                        'grade' => in_array($jsst_scgrade, array('pass', 'warn', 'fail', 'na'), true) ? $jsst_scgrade : '',
                        'search' => $jsst_scsearch,
                    ));
                    jssupportticket::$jsst_data['sctotals'] = $jsst_screport['totals'];
                    jssupportticket::$jsst_data['scchecks'] = $jsst_screport['checks'];
                    jssupportticket::$jsst_data['scnotes'] = $jsst_screport['notes'];
                    jssupportticket::$jsst_data['scwhen'] = $jsst_screport['when'];
                    jssupportticket::$jsst_data['sckinds'] = JSSTauthmatrix::kinds();
                    jssupportticket::$jsst_data['sckind'] = $jsst_sckind;
                    jssupportticket::$jsst_data['scgrade'] = $jsst_scgrade;
                    jssupportticket::$jsst_data['scsearch'] = $jsst_scsearch;
                    break;
                /* The developer reference: every hook this product fires, the
                   event contract, the schema history and the guards that repair
                   it. Administrators only - it names file paths.
                   (Roadmap 4.0-DATA-03) */
                case 'admin_developers':
                    if (!current_user_can('manage_options') || !class_exists('JSSThooks')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    $jsst_dvkind = sanitize_key( JSSTrequest::getVar('dvkind', '', '') );
                    $jsst_dvwhere = sanitize_text_field( JSSTrequest::getVar('dvwhere', '', '') );
                    $jsst_dvsearch = sanitize_text_field( JSSTrequest::getVar('dvsearch', '', '') );
                    $jsst_dvscope = (JSSTrequest::getVar('dvscope', '', 'ours') === 'all') ? 'all' : 'ours';
                    jssupportticket::$jsst_data['dvsummary'] = JSSThooks::summary();
                    jssupportticket::$jsst_data['dvhooks'] = JSSThooks::hooks(array(
                        'kind' => in_array($jsst_dvkind, array('action', 'filter'), true) ? $jsst_dvkind : '',
                        'where' => $jsst_dvwhere,
                        'search' => $jsst_dvsearch,
                        'scope' => $jsst_dvscope,
                    ));
                    jssupportticket::$jsst_data['dvtotal'] = count(JSSThooks::hooks(array('scope' => $jsst_dvscope)));
                    jssupportticket::$jsst_data['dvscope'] = $jsst_dvscope;
                    jssupportticket::$jsst_data['dvkind'] = $jsst_dvkind;
                    jssupportticket::$jsst_data['dvwhere'] = $jsst_dvwhere;
                    jssupportticket::$jsst_data['dvsearch'] = $jsst_dvsearch;
                    jssupportticket::$jsst_data['dvevents'] = JSSThooks::events();
                    jssupportticket::$jsst_data['dvplaces'] = JSSThooks::places();
                    jssupportticket::$jsst_data['dvschema'] = JSSThooks::schemaHistory();
                    jssupportticket::$jsst_data['dvguards'] = JSSThooks::guards();
                    jssupportticket::$jsst_data['dvapi'] = class_exists('JSSTrestapi') ? JSSTrestapi::summary() : array();
                    break;
                /* Retiring Internal Mail. Administrators only: it reads
                   everybody's messages and writes notes on tickets.
                   (Roadmap 4.5-FE-12) */
                case 'admin_internalmail':
                    if (!current_user_can('manage_options') || !class_exists('JSSTinternalmail')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    jssupportticket::$jsst_data['imsurvey'] = JSSTinternalmail::survey();
                    jssupportticket::$jsst_data['impreview'] = JSSTinternalmail::preview(50);
                    jssupportticket::$jsst_data['imreplacements'] = JSSTinternalmail::replacements();
                    break;
                /* Everything that happened that concerns this person, with
                   the preferences that decide how they are told. Their own
                   only - a notification is addressed to one person and there
                   is no view of somebody else's. (Roadmap 4.5-UX-02) */
                case 'admin_notifications':
                case 'notifications':
                    if (!class_exists('JSSTnotifications')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    $jsst_nfactor = JSSTcapability::actor();
                    $jsst_nfme = (int) $jsst_nfactor['staffid'];
                    jssupportticket::$jsst_data['nfme'] = $jsst_nfme;
                    jssupportticket::$jsst_data['nfrows'] = ($jsst_nfme > 0)
                        ? JSSTnotifications::forAgent($jsst_nfme, 40) : array();
                    jssupportticket::$jsst_data['nfprefs'] = ($jsst_nfme > 0)
                        ? JSSTnotifications::prefs($jsst_nfme) : JSSTnotifications::defaults();
                    jssupportticket::$jsst_data['nfcategories'] = JSSTnotifications::categories();
                    jssupportticket::$jsst_data['nfchannels'] = JSSTnotifications::channels();
                    jssupportticket::$jsst_data['nfheld'] = ($jsst_nfme > 0)
                        ? count(JSSTnotifications::heldFor($jsst_nfme)) : 0;
                    /* The subscription script only where push can actually
                       work, so a site on plain HTTP never loads code whose
                       only possible outcome is an error in the console. */
                    if (class_exists('JSSTwebpush') && JSSTwebpush::available()
                            && JSSTwebpush::unavailableReason() === '') {
                        wp_enqueue_script('jsst-push', JSST_PLUGIN_URL . 'includes/js/push.js', array(), '4.5', true);
                        wp_localize_script('jsst-push', 'jsstPush', array(
                            'ajaxurl'     => admin_url('admin-ajax.php'),
                            'done'        => esc_html(__('This browser is registered. You can close the tab.', 'js-support-ticket')),
                            'failed'      => esc_html(__('That did not work. The browser or its push service refused.', 'js-support-ticket')),
                            'refused'     => esc_html(__('You said no to notifications. Your browser will not ask again until you change it in its own settings.', 'js-support-ticket')),
                            'unsupported' => esc_html(__('This browser does not do push notifications.', 'js-support-ticket')),
                        ));
                    }
                    jssupportticket::$jsst_data['nfpush'] = class_exists('JSSTwebpush')
                        ? array(
                            'available' => JSSTwebpush::available(),
                            'reason'    => JSSTwebpush::unavailableReason(),
                            'key'       => JSSTwebpush::publicKey(),
                            'browsers'  => ($jsst_nfme > 0) ? count(JSSTwebpush::subscriptions($jsst_nfme)) : 0,
                        )
                        : false;
                    break;
                /* The desk home: personal workload, what is late, what nobody
                   has picked up, where this agent was named, what they left
                   unfinished and what happened while they were away. Rendered
                   by JSSTnavigation in both workspaces, so this case has
                   nothing to prepare - only somebody to turn away.
                   (Roadmap 4.5-FE-02) */
                case 'admin_workspacehome':
                case 'workspacehome':
                    if (!class_exists('JSSTnavigation')
                            || !JSSTcapability::can(JSSTcapability::QUEUE_VIEW)
                            || !self::atThisDesk($jsst_layout)) {
                        self::leaveTheDesk();
                    }
                    break;
                /* The people behind the tickets, and the companies they write
                   in from. Gated on customer.view rather than on the queue,
                   because "works tickets but may not browse customers" is a
                   real arrangement on a desk with contractors on it.
                   (Roadmap 4.5-FE-02) */
                case 'admin_customers':
                case 'customers':
                    if (!class_exists('JSSTnavigation')
                            || !JSSTcapability::can(JSSTcapability::CUSTOMER_VIEW)
                            || !self::atThisDesk($jsst_layout)) {
                        self::leaveTheDesk();
                    }
                    $jsst_customersearch = trim((string) JSSTrequest::getVar('search', null, ''));
                    $jsst_customercompany = trim((string) JSSTrequest::getVar('company', null, ''));
                    $jsst_customerstart = absint(JSSTrequest::getVar('start', null, 0));
                    $jsst_customerwho = absint(JSSTrequest::getVar('customer', null, 0));
                    jssupportticket::$jsst_data['customersearch'] = $jsst_customersearch;
                    jssupportticket::$jsst_data['customercompany'] = $jsst_customercompany;
                    jssupportticket::$jsst_data['customerstart'] = $jsst_customerstart;
                    jssupportticket::$jsst_data['customerlist'] = JSSTticketquery::customers(array(
                        'search'  => $jsst_customersearch,
                        'company' => $jsst_customercompany,
                        'limit'   => 25,
                        'offset'  => $jsst_customerstart,
                    ));
                    /* The company rail is the same rows grouped one level up,
                       and it is not filtered by the company already chosen -
                       an agent inside one company still wants to see the way
                       back out to the others. */
                    jssupportticket::$jsst_data['customercompanies'] = JSSTticketquery::companies(array(
                        'search' => $jsst_customersearch,
                        'limit'  => 12,
                    ));
                    jssupportticket::$jsst_data['customerwho'] = $jsst_customerwho;
                    jssupportticket::$jsst_data['customerhistory'] = ($jsst_customerwho > 0)
                        ? JSSTticketquery::customerHistory($jsst_customerwho, array('limit' => 25))
                        : array();
                    break;
                /* Retiring the MailChimp add-on: what a newsletter checkbox
                   should always have produced. (Roadmap 5.5-SEC-02) */
                case 'admin_marketing':
                    if (!current_user_can('manage_options') || !class_exists('JSSTconsent')) {
                        self::leaveTheDesk();
                    }
                    JSSTconsent::ensureSchema();
                    jssupportticket::$jsst_data['consentsurvey'] = JSSTconsent::survey();
                    jssupportticket::$jsst_data['consentsettings'] = JSSTconsent::settings();
                    jssupportticket::$jsst_data['consenthistory'] = JSSTconsent::history('', 40);
                    break;
                /* The companies behind the customers, as records. Administrators
                   only, and not because the list is secret: a company record
                   decides which of their colleagues' tickets a supervisor may
                   read, so editing one widens somebody's access.
                   (Roadmap 5.5-COM-06) */
                case 'admin_companies':
                    if (!class_exists('JSSTcompanies')
                            || !JSSTcapability::can(JSSTcapability::COMPANY_MANAGE)) {
                        self::leaveTheDesk();
                    }
                    JSSTcompanies::ensureSchema();
                    jssupportticket::$jsst_data['companies'] = JSSTcompanies::all();
                    /* Every company's numbers, for the list. Counted rather
                       than stored, the same way the analytics are: a total in
                       a column of its own goes wrong the first time somebody
                       is moved between companies and nobody finds out. */
                    jssupportticket::$jsst_data['companytotals'] = array();
                    foreach (jssupportticket::$jsst_data['companies'] AS $jsst_cid => $jsst_crow) {
                        jssupportticket::$jsst_data['companytotals'][$jsst_cid] = JSSTcompanies::summary($jsst_cid);
                    }
                    /* The domains an administrator most likely wants to claim:
                       the derived companies from the Customers screen, minus
                       the ones already written down and the mail providers that
                       can never be claimed. It is a suggestion list, not a
                       migration - nothing here creates anything. */
                    jssupportticket::$jsst_data['companysuggest'] = self::companySuggestions();
                    break;
                /* One company: its record, the people named on it and its
                   account numbers. Same guard as the list. */
                case 'admin_addcompany':
                    if (!class_exists('JSSTcompanies')
                            || !JSSTcapability::can(JSSTcapability::COMPANY_MANAGE)) {
                        self::leaveTheDesk();
                    }
                    JSSTcompanies::ensureSchema();
                    $jsst_companyid = absint(JSSTrequest::getVar('companyid', null, 0));
                    jssupportticket::$jsst_data['companyedit'] = ($jsst_companyid > 0)
                        ? JSSTcompanies::get($jsst_companyid) : false;
                    jssupportticket::$jsst_data['companypeople'] = ($jsst_companyid > 0)
                        ? JSSTcompanies::people($jsst_companyid) : array();
                    jssupportticket::$jsst_data['companysummary'] = ($jsst_companyid > 0)
                        ? JSSTcompanies::summary($jsst_companyid) : false;
                    break;
                /* Where the two agent workspaces actually stand against each
                   other, run against their own source rather than against a
                   list somebody keeps up to date. (Roadmap 4.5-FE-01) */
                case 'admin_workspaceparity':
                    if (!current_user_can('manage_options') || !class_exists('JSSTworkspace')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    jssupportticket::$jsst_data['parity'] = JSSTworkspace::parity();
                    jssupportticket::$jsst_data['paritysummary'] = JSSTworkspace::summary();
                    jssupportticket::$jsst_data['parityshortcuts'] = JSSTworkspace::shortcuts();
                    break;
                // (Roadmap 4.0-OPS-02)
                case 'admin_systemstatus':
                    jssupportticket::$jsst_data['systemstatus'] = class_exists('JSSTsystemstatus') ? JSSTsystemstatus::report() : array();
                    break;
                // Storage engine conversion. (Roadmap 4.0-PERF-03)
                case 'admin_storageengine':
                    jssupportticket::$jsst_data['storageplan'] = class_exists('JSSTstorageengine') ? JSSTstorageengine::plan() : new WP_Error('jsst_missing', esc_html(__('Not available.', 'js-support-ticket')));
                    jssupportticket::$jsst_data['storagestate'] = class_exists('JSSTstorageengine') ? JSSTstorageengine::state() : array();
                    jssupportticket::$jsst_data['storagesummary'] = class_exists('JSSTstorageengine') ? JSSTstorageengine::summary() : array();
                    break;
                // The diagnostic pages every error message links into.
                // (Roadmap 4.0-OPS-03)
                case 'admin_diagnostics':
                    jssupportticket::$jsst_data['docgroups'] = class_exists('JSSTdocs') ? JSSTdocs::grouped() : array();
                    jssupportticket::$jsst_data['doctopic'] = sanitize_key(JSSTrequest::getVar('topic', '', ''));
                    break;
                case 'admin_shortcodes':
                    JSSTincluder::getJSModel('jssupportticket')->getShortCodeData();
                    break;
                case 'admin_aboutus':
                    break;
                /* This site's languages, installed from jshelpdesk.com, and the
                   files to download. (1 Oct 2026) */
                case 'admin_translations':
                    if (!current_user_can('install_languages') || !class_exists('JSSTtranslations')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    jssupportticket::$jsst_data['translations'] = JSSTtranslations::rows();
                    break;
                /* License & Add-ons: the key, the sites it covers, and the
                   nine add-ons with one action each. Administrators only - it
                   shows the key. It replaced the License and Install Add-ons
                   screens on 2 October 2026; the address is the License
                   screen's, which every licence notice and every add-on's
                   licence gate already link to. (Roadmap 6.5-ECO-02) */
                case 'admin_license':
                    if (!current_user_can('manage_options') || !class_exists('JSSTlicense')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    jssupportticket::$jsst_data['licstate'] = JSSTlicense::state();
                    jssupportticket::$jsst_data['lickey'] = JSSTlicense::key();
                    jssupportticket::$jsst_data['licmasked'] = class_exists('JSSTpro') ? JSSTpro::maskedKey() : '';
                    jssupportticket::$jsst_data['instrows'] = self::installRows();
                    break;
                /* Install Add-ons is part of License & Add-ons.
                   JSSTlicense::redirectRetired() sends the address on at
                   admin_init; this is the fallback. */
                case 'admin_installaddons':
                    JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket&jstlay=license'));
                    return;
                /* The old "JS Help Desk Pro" screen. It described a Pro companion
                   5.0.0 does not ship and duplicated the License screen, so it
                   is gone; the address stays so a bookmark or a link in an old
                   support reply lands somewhere useful. */
                case 'admin_pro':
                    /* JSSTlicense::redirectRetired() sends this address on at
                       admin_init; this only runs if that did not, and must not
                       go on to look for a template that no longer exists. */
                    JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket&jstlay=license'));
                    return;
                /* What a customer already owns, and how it moves onto Pro.
                   Administrators only. (Roadmap 4.5-PRO-02) */
                case 'admin_legacy':
                    if (!current_user_can('manage_options') || !class_exists('JSSTlegacy')) {
                        JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket'));
                    }
                    /* Re-read on every visit rather than once on upgrade: a key
                       entered five minutes ago on the old Addons screen should
                       be reflected here, and the cost is a handful of option
                       reads on a page nobody opens twice a day. */
                    JSSTlegacy::reconcile();
                    $jsst_legacyslug = sanitize_key(JSSTrequest::getVar('slug', '', ''));
                    jssupportticket::$jsst_data['legacyrows'] = JSSTlegacy::inventory();
                    jssupportticket::$jsst_data['legacyasked'] = $jsst_legacyslug;
                    jssupportticket::$jsst_data['legacypreview'] = ($jsst_legacyslug !== '') ? JSSTlegacy::preview($jsst_legacyslug) : false;
                    jssupportticket::$jsst_data['legacyends'] = JSSTlegacy::SUPPORT_ENDS;
                    jssupportticket::$jsst_data['legacydays'] = JSSTlegacy::supportRemaining();
                    if (is_array(jssupportticket::$jsst_data['legacypreview'])) {
                        jssupportticket::$jsst_data['legacypreview']['permissions'] = JSSTlegacy::changesPermissions($jsst_legacyslug);
                    }
                    break;
                /* Addons Status installed and updated the old add-ons through
                   jshelpdesk.com/setup/. JSSTlicense::redirectRetired() sends the
                   address to Install Add-ons at admin_init; this is the fallback. */
                case 'admin_addonstatus':
                    JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket&jstlay=license'));
                    return;
                case 'admin_help':
                    break;
                case 'login':
                    break;
                case 'userregister':
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'jssupportticket');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }




    /**
     * The working week as the form posts it. (Roadmap 4.5-FE-06)
     *
     * Same shape a team calendar posts in, and cleaned by the same code, so
     * the two can be compared and fallen back to without conversion. A day
     * whose box is unticked simply is not here.
     */
    private static function postedHours() {
        $jsst_hours = array();
        $jsst_open = JSSTrequest::getVar('open', 'post', array());
        $jsst_close = JSSTrequest::getVar('close', 'post', array());
        foreach ((array) JSSTrequest::getVar('workday', 'post', array()) as $jsst_day => $jsst_on) {
            $jsst_day = (int) $jsst_day;
            $jsst_hours[$jsst_day] = array(
                'open'  => isset($jsst_open[$jsst_day]) ? sanitize_text_field($jsst_open[$jsst_day]) : '',
                'close' => isset($jsst_close[$jsst_day]) ? sanitize_text_field($jsst_close[$jsst_day]) : '',
            );
        }
        return $jsst_hours;
    }

    /**
     * Archive or migrate the internal mailbox. (Roadmap 4.5-FE-12)
     *
     * Administrators only. Nothing here deletes a message, drops the add-on's
     * table or switches the add-on off - the mailbox is exactly where it was
     * afterwards, which is what makes the migration something somebody can
     * risk running.
     */
    /** What the newsletter checkbox says, and where the consent goes. */
    static function savemarketing() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce', 'post');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-marketing') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTconsent')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        JSSTconsent::saveSettings(array(
            'statement' => JSSTrequest::getVar('statement', 'post', ''),
            'recipe'    => JSSTrequest::getVar('recipe', 'post', ''),
            'listid'    => JSSTrequest::getVar('listid', 'post', ''),
        ));
        JSSTmessage::setMessage(esc_html__('Saved. Consents already recorded keep the wording that was actually shown to the person who agreed to it — changing the sentence here does not rewrite what anybody consented to.', 'js-support-ticket'), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=marketing'));
        exit;
    }

    /* =====================================================================
     * Companies (Roadmap 5.5-COM-06)
     * ================================================================== */

    /**
     * The domains worth writing down, offered rather than created.
     *
     * v4.5's derived company list is exactly the raw material for this screen:
     * it already knows which domains write in most. What it must never become
     * is a migration - a button making one company per domain would produce
     * four thousand records on a site with eleven companies, and every one of
     * them would then have to be deleted by hand. So the suggestion is a row
     * with a name box beside it, and the administrator types the company's
     * actual name.
     */
    private static function companySuggestions() {
        if (!class_exists('JSSTticketquery') || !class_exists('JSSTcompanies')) {
            return array();
        }
        $jsst_derived = JSSTticketquery::companies(array('limit' => 30));
        if (empty($jsst_derived['ok']) || empty($jsst_derived['rows'])) {
            return array();
        }
        $jsst_public = JSSTcompanies::publicDomains();
        $jsst_claimed = array();
        foreach (JSSTcompanies::all() AS $jsst_row) {
            foreach (JSSTcompanies::domainsOf($jsst_row) AS $jsst_domain) {
                $jsst_claimed[] = $jsst_domain;
            }
        }
        $jsst_out = array();
        foreach ($jsst_derived['rows'] AS $jsst_row) {
            $jsst_domain = strtolower((string) $jsst_row->company);
            if ($jsst_domain === '' || in_array($jsst_domain, $jsst_public, true)
                    || in_array($jsst_domain, $jsst_claimed, true)) {
                continue;
            }
            $jsst_out[] = $jsst_row;
            if (count($jsst_out) >= 8) {
                break;
            }
        }
        return $jsst_out;
    }

    /** Create or change one company. */
    static function savecompany() {
        self::companyGuard('jsst-company');
        $jsst_result = JSSTcompanies::save(array(
            'id'            => JSSTrequest::getVar('companyid', 'post', 0),
            'name'          => JSSTrequest::getVar('coname', 'post', ''),
            'domains'       => JSSTrequest::getVar('codomains', 'post', ''),
            'tier'          => JSSTrequest::getVar('cotier', 'post', ''),
            'contractref'   => JSSTrequest::getVar('coref', 'post', ''),
            'contractstart' => JSSTrequest::getVar('costart', 'post', ''),
            'contractend'   => JSSTrequest::getVar('coend', 'post', ''),
            'notes'         => JSSTrequest::getVar('conotes', 'post', ''),
            'status'        => (JSSTrequest::getVar('costatus', 'post', '') !== '')
                                ? JSSTcompanies::STATUS_ACTIVE : JSSTcompanies::STATUS_ARCHIVED,
        ));
        if (is_string($jsst_result)) {
            JSSTmessage::setMessage($jsst_result, 'error');
            self::companyGoTo(absint(JSSTrequest::getVar('companyid', 'post', 0)), true);
        }
        JSSTmessage::setMessage(esc_html__('Saved. Nothing was written on to any ticket — a ticket\'s company is answered by asking this record about the address, so it follows the record.', 'js-support-ticket'), 'updated');
        self::companyGoTo((int) $jsst_result);
    }

    /** Delete one, and the memberships that pointed at it. */
    static function deletecompany() {
        self::companyGuard('jsst-company-delete', '');
        JSSTcompanies::delete(absint(JSSTrequest::getVar('companyid', '', 0)));
        JSSTmessage::setMessage(esc_html__('Deleted. Their tickets are untouched and are now ordinary tickets again.', 'js-support-ticket'), 'updated');
        self::companyGoTo(0);
    }

    /** Name somebody on a company, or change what they are to it. */
    static function savecompanyperson() {
        self::companyGuard('jsst-company-person');
        $jsst_companyid = absint(JSSTrequest::getVar('companyid', 'post', 0));
        $jsst_result = JSSTcompanies::addPerson(
            $jsst_companyid,
            JSSTrequest::getVar('coemail', 'post', ''),
            (JSSTrequest::getVar('corole', 'post', '') === JSSTcompanies::ROLE_SUPERVISOR)
                ? JSSTcompanies::ROLE_SUPERVISOR : JSSTcompanies::ROLE_CONTACT);
        JSSTmessage::setMessage(
            is_string($jsst_result) ? $jsst_result : esc_html__('Named. Somebody named individually belongs to this company whatever their address ends in.', 'js-support-ticket'),
            is_string($jsst_result) ? 'error' : 'updated');
        self::companyGoTo($jsst_companyid);
    }

    /** Stop naming somebody. A matching domain may still reach them. */
    static function removecompanyperson() {
        self::companyGuard('jsst-company-person-remove', '');
        $jsst_companyid = absint(JSSTrequest::getVar('companyid', '', 0));
        JSSTcompanies::removePerson($jsst_companyid, JSSTrequest::getVar('coemail', '', ''));
        JSSTmessage::setMessage(esc_html__('Removed. If their address is on one of this company\'s domains they are still in it — take the domain off too, or name them on the company they actually belong to.', 'js-support-ticket'), 'updated');
        self::companyGoTo($jsst_companyid);
    }

    /** One nonce, one permission, one class_exists — for all four actions. */
    private static function companyGuard($jsst_action, $jsst_method = 'post') {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce', $jsst_method);
        if (! wp_verify_nonce( $jsst_nonce, $jsst_action) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!class_exists('JSSTcompanies')
                || !JSSTcapability::can(JSSTcapability::COMPANY_MANAGE)) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
    }

    /* A company that exists opens in its own form; otherwise the list, or the
       empty form again when a new one could not be saved. */
    private static function companyGoTo($jsst_companyid, $jsst_backtoform = false) {
        if ((int) $jsst_companyid > 0) {
            $jsst_url = admin_url('admin.php?page=jssupportticket&jstlay=addcompany&companyid=' . (int) $jsst_companyid);
        } elseif ($jsst_backtoform) {
            $jsst_url = admin_url('admin.php?page=jssupportticket&jstlay=addcompany');
        } else {
            $jsst_url = admin_url('admin.php?page=jssupportticket&jstlay=companies');
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function saveinternalmail() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-internalmail') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTinternalmail')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        if (JSSTrequest::getVar('imarchive', 'post', '') !== '') {
            $jsst_csv = JSSTinternalmail::archive();
            nocache_headers();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="internal-mail-archive-' . gmdate('Y-m-d') . '.csv"');
            echo $jsst_csv; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a CSV download, not markup
            exit;
        }
        if (JSSTrequest::getVar('immigrate', 'post', '') !== '') {
            $jsst_result = JSSTinternalmail::migrate();
            JSSTmessage::setMessage(sprintf(
                /* translators: 1: messages moved, 2: already done, 3: naming no ticket, 4: failed */
                esc_html(__('%1$d moved onto their tickets, %2$d already done, %3$d name no ticket and were left alone, %4$d failed. Nothing was deleted.', 'js-support-ticket')),
                $jsst_result['moved'], $jsst_result['skipped'], $jsst_result['unmatched'], $jsst_result['failed']
            ), 'updated');
        }
        wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=internalmail'));
        exit;
    }


    /**
     * Read, act on, or change the settings for your own notifications.
     * (Roadmap 4.5-UX-02)
     *
     * Everything here is about the person doing it and nobody else, which is
     * why the only permission check is that they have an agent record at all:
     * there is no way to address, read or clear somebody else's.
     */
    static function savenotifications() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-notifications') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!class_exists('JSSTnotifications')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_actor = JSSTcapability::actor();
        $jsst_me = (int) $jsst_actor['staffid'];
        if ($jsst_me <= 0) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        // Back to the screen the form was posted from: an agent on the
        // front-end desk stays on the front end.
        $jsst_back = is_admin()
            ? admin_url('admin.php?page=jssupportticket&jstlay=notifications')
            : jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'notifications', 'jsstpageid' => jssupportticket::getPageid()));

        if (JSSTrequest::getVar('nfprefs', 'post', '') !== '') {
            JSSTnotifications::savePrefs($jsst_me, array(
                'channels' => (array) JSSTrequest::getVar('channels', 'post', array()),
                'quiet'    => (JSSTrequest::getVar('quiet', 'post', '') !== '') ? 1 : 0,
                'digest'   => sanitize_key(JSSTrequest::getVar('digest', 'post', 'off')),
            ));
            JSSTmessage::setMessage(esc_html(__('Saved.', 'js-support-ticket')), 'updated');
        } elseif (JSSTrequest::getVar('nfseen', 'post', '') !== '') {
            JSSTnotifications::markSeen($jsst_me, absint( JSSTrequest::getVar('nfseen', 'post', 0) ));
        } elseif (JSSTrequest::getVar('nfseenall', 'post', '') !== '') {
            JSSTnotifications::markSeen($jsst_me);
            JSSTmessage::setMessage(esc_html(__('All marked as read.', 'js-support-ticket')), 'updated');
        } elseif (JSSTrequest::getVar('nftake', 'post', '') !== '') {
            /* The one action worth doing from a list. It goes through the
               ticket service like every other assignment, so it is refused if
               it should be refused and it lands on the timeline. */
            $jsst_ticketid = absint( JSSTrequest::getVar('nftake', 'post', 0) );
            $jsst_result = JSSTticketservice::assign($jsst_ticketid, $jsst_me);
            if (is_wp_error($jsst_result)) {
                JSSTmessage::setMessage($jsst_result->get_error_message(), 'error');
            } else {
                JSSTmessage::setMessage(esc_html(__('It is yours.', 'js-support-ticket')), 'updated');
            }
        }
        wp_safe_redirect($jsst_back);
        exit;
    }


    /**
     * A posted textarea, with its line breaks still in it.
     *
     * JSSTrequest::getVar() sanitizes with sanitize_text_field(), which
     * collapses every run of whitespace - newlines included - into a single
     * space. That is right for a name and wrong for the two fields here, where
     * a line break is the separator: a holiday list flattened into one line
     * reads as one date with an enormous label, and it does so silently. So
     * these two are read from the request directly and sanitized with the
     * function meant for a textarea. Both callers sit behind the nonce and the
     * capability check at the top of savesla().
     *
     * @param string $jsst_field The field name.
     * @param array  $jsst_path  Keys to walk for a field posted as an array.
     */
    private static function postedLines($jsst_field, $jsst_path = array()) {
        return JSSTrequest::getLines($jsst_field, $jsst_path);
    }




    /**
     * Read the source again. (Roadmap 4.0-DATA-03)
     *
     * The index rebuilds itself when the plugin version changes, which covers
     * every ordinary upgrade. This button is for the one case that does not:
     * somebody editing a file on a running site, which is exactly what a person
     * on this screen is doing.
     */
    static function refreshhooks() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-hooks') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options') || !class_exists('JSSThooks')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_index = JSSThooks::index(true);
        JSSTmessage::setMessage(sprintf(
            /* translators: 1: number of files, 2: number of hooks. */
            esc_html(__('Read %1$d files and found %2$d hooks.', 'js-support-ticket')),
            (int) $jsst_index['files'], (int) ($jsst_index['actions'] + $jsst_index['filters'])), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=developers'));
        exit;
    }


















    /**
     * Read the source again, for the matrix. (Roadmap 5.0-SEC-01)
     *
     * Same reasoning as refreshhooks(): the scan re-runs by itself when the
     * plugin version or the grading changes, and this button exists for
     * somebody who has just edited a handler on a running site and wants to
     * see whether the guard they added is one the report can see.
     */
    static function refreshmatrix() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-authmatrix-refresh') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTauthmatrix')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_report = JSSTauthmatrix::refresh();
        JSSTmessage::setMessage(sprintf(
            /* translators: 1: number of entry points, 2: how many need reading, 3: how many are failing. */
            esc_html(__('Read %1$d ways in: %2$d to read by hand, %3$d failing.', 'js-support-ticket')),
            (int) $jsst_report['totals']['total'], (int) $jsst_report['totals']['warn'],
            (int) $jsst_report['totals']['fail']), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=security'));
        exit;
    }








    function canaddfile($jsst_layout) {
        $jsst_nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $jsst_nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if(!is_admin() && jssupportticketphplib::JSST_strpos($jsst_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    /**
     * Is this person allowed at the desk this layout belongs to?
     * (Roadmap 4.5-FE-02, 4.5-FE-11)
     *
     * The workspace preference is enforced at every door into a desk, and these
     * two screens are new doors. Only the front-end spelling of the layout is
     * asked about: the wp-admin side is already refused a page earlier, by
     * mainmenu() declining to register the page at all.
     */
    private static function atThisDesk($jsst_layout) {
        if (jssupportticketphplib::JSST_strpos($jsst_layout, 'admin_') === 0) {
            return true;
        }
        return (!class_exists('JSSTworkspace') || JSSTworkspace::mayUse(JSSTworkspace::SHELL_FRONTEND));
    }

    /**
     * Send somebody who does not belong here back where they came from.
     *
     * A redirect rather than an empty screen, and to the control panel rather
     * than to the queue: a customer who follows a colleague's link to the desk
     * home should land somewhere that makes sense to them, not on a page that
     * tells them what they may not do.
     */
    private static function leaveTheDesk() {
        /* Where somebody goes when a screen refuses them. In wp-admin that used
           to be `page=jssupportticket` unconditionally, which is the
           administrator's control panel - so an agent refused by one screen was
           sent to another that refuses them, and now that the slug is open to
           agents it would be sent straight back here. The tickets list is the
           one screen every agent role can reach. (Roadmap 4.0-SEC-04) */
        $jsst_url = is_admin()
            ? admin_url('admin.php?page=' . (current_user_can('manage_options') ? 'jssupportticket' : 'ticket'))
            : jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel'));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function addmissingusers() {
        if(!is_admin())
            return false;
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'add-missing-users') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        JSSTincluder::getJSModel('jssupportticket')->addMissingUsers();
        $jsst_url = admin_url("admin.php?page=jssupportticket");
        wp_safe_redirect($jsst_url);
        exit;
    }

    function saveordering(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-ordering') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_post = JSSTrequest::get('post');

        JSSTincluder::getJSModel('jssupportticket')->storeOrderingFromPage($jsst_post);
        if($jsst_post['ordering_for'] == 'department'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=department&jstlay=departments");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments'));
            }
        }elseif($jsst_post['ordering_for'] == 'priority'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=priority&jstlay=priorities");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'priority', 'jstlay'=>'priorities'));
            }
        }elseif($jsst_post['ordering_for'] == 'status'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=status&jstlay=statuses");
            }
        }elseif($jsst_post['ordering_for'] == 'product'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=product&jstlay=products");
            }
        }elseif($jsst_post['ordering_for'] == 'fieldordering'){
            $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
            if($jsst_fieldfor == ''){
                $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
            }
            $jsst_formid = JSSTrequest::getVar('formid');
            if($jsst_formid == ''){
                $jsst_formid = jssupportticket::$jsst_data['formid'];
            }
            $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        }elseif($jsst_post['ordering_for'] == 'announcement'){
            if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=announcement&jstlay=announcements");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'staffannouncements'));
        }
        }elseif($jsst_post['ordering_for'] == 'article'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=knowledgebase&jstlay=listarticles");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'stafflistarticles'));
            }
        }elseif($jsst_post['ordering_for'] == 'download'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=download&jstlay=downloads");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'staffdownloads'));
            }
        }elseif($jsst_post['ordering_for'] == 'faq'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=faq&jstlay=faqs");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'stafffaqs'));
            }
        }elseif($jsst_post['ordering_for'] == 'helptopic'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=helptopic&jstlay=helptopics");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics'));
            }
        }elseif($jsst_post['ordering_for'] == 'multiform'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=multiform&jstlay=forms");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'multiform', 'jstlay'=>'staffmultiform'));
            }
        }

        wp_safe_redirect($jsst_url);
        exit;
    }

    /**
     * Hand over the redacted diagnostic file. (Roadmap 4.0-OPS-02)
     *
     * Administrators only, and redacted by JSSTsystemstatus before it is
     * written - this is meant to be attached to a support ticket by somebody who
     * will not read it first.
     */
    static function downloaddebugbundle() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-debug-bundle') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTsystemstatus')) {
            wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=systemstatus'));
            exit;
        }
        $jsst_body = JSSTsystemstatus::debugBundle();
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="js-help-desk-status-' . gmdate('Ymd-His') . '.json"');
        header('Content-Length: ' . strlen($jsst_body));
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON built by wp_json_encode() and already redacted.
        echo $jsst_body;
        exit;
    }

    /* ------------------------------------------------------------------ *
     * Storage engine conversion (Roadmap 4.0-PERF-03)
     * ------------------------------------------------------------------ */

    /**
     * How long one request spends converting before handing back.
     *
     * A table is rewritten whole or not at all, so this is a floor rather than a
     * ceiling — the budget is checked between tables, and the table that runs
     * over it still finishes. Kept well inside a default max_execution_time so
     * the screen comes back and says what happened instead of the browser
     * showing a timeout and nobody knowing how far it got.
     */
    const STORAGE_BUDGET = 20;

    function startstorageconversion() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-storage-start')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_revert = (JSSTrequest::getVar('direction', '', '') === 'revert');
        $jsst_begun = $jsst_revert ? JSSTstorageengine::beginRevert() : JSSTstorageengine::begin();
        if (is_wp_error($jsst_begun)) {
            JSSTmessage::setMessage(esc_html($jsst_begun->get_error_message()), 'error', 'storage-engine');
            self::storageGoTo();
        }
        self::runStorageBudget();
    }

    /** Carry on where the last request stopped. */
    function continuestorageconversion() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-storage-continue')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        if (!JSSTstorageengine::isRunning()) {
            JSSTmessage::setMessage(esc_html(__('There is no conversion to continue.', 'js-support-ticket')), 'error', 'storage-engine');
            self::storageGoTo();
        }
        self::runStorageBudget();
    }

    function cancelstorageconversion() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-storage-cancel')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        JSSTstorageengine::cancel();
        /* Deliberately not "nothing was changed": the tables converted before
           the cancel are still converted, and saying otherwise would send
           somebody looking for a problem that is not there. */
        JSSTmessage::setMessage(esc_html(__('The conversion was stopped. Tables already converted stay converted — start again to finish the rest, or revert to put them back.', 'js-support-ticket')), 'updated', 'storage-engine');
        self::storageGoTo();
    }

    /** Convert until the budget runs out, then report where it got to. */
    private static function runStorageBudget() {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        $jsst_until = time() + self::STORAGE_BUDGET;
        $jsst_more = true;
        while ($jsst_more && time() < $jsst_until) {
            $jsst_more = JSSTstorageengine::step();
        }

        $jsst_state = JSSTstorageengine::state();
        if ($jsst_more) {
            JSSTmessage::setMessage(sprintf(
                /* translators: %s: number of tables still to convert */
                esc_html(__('Still going — %s tables left. Press Continue to carry on; nothing is lost by stopping here.', 'js-support-ticket')),
                number_format_i18n(count($jsst_state['queue']))
            ), 'updated', 'storage-engine');
            self::storageGoTo();
        }

        if (!empty($jsst_state['failed'])) {
            JSSTmessage::setMessage(sprintf(
                /* translators: %s: number of tables that could not be converted */
                esc_html(__('Finished, but %s tables could not be converted. Each one and its reason is listed below.', 'js-support-ticket')),
                number_format_i18n(count($jsst_state['failed']))
            ), 'error', 'storage-engine');
            self::storageGoTo();
        }
        JSSTmessage::setMessage(esc_html(__('Finished. Every table that could be converted has been.', 'js-support-ticket')), 'updated');
        self::storageGoTo();
    }

    private static function storageGoTo() {
        wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=storageengine'));
        exit;
    }

    /* ------------------------------------------------------------------ *
     * JS Help Desk Pro (Roadmap 4.5-PRO-01)
     * ------------------------------------------------------------------ */

    /** Register a licence key against this site. */
    /* ------------------------------------------------------------------ *
     * The licence (Roadmap 6.5-ECO-02)
     *
     * Three verbs, and they talk to JSSTlicense rather than JSSTpro: the key
     * belongs to the licence client now, and JSSTpro has stopped being one.
     * ------------------------------------------------------------------ */

    /** Activate, or replace, the key on this site. */
    function savelicensekey() {
        self::licenseGuard('jsst-lic-save');
        $jsst_key = trim((string) JSSTrequest::getVar('licensekey', 'post', ''));
        $jsst_done = JSSTlicense::activate($jsst_key);

        if (is_wp_error($jsst_done)) {
            JSSTmessage::setMessage(esc_html($jsst_done->get_error_message()), 'error');
            self::licenseGoTo();
        }

        $jsst_granted = count((array) JSSTlicense::state()['products']);
        JSSTmessage::setMessage(sprintf(
            /* translators: %s: how many add-ons the licence grants */
            esc_html(_n('Licensed. %s add-on is covered by this key.', 'Licensed. %s add-ons are covered by this key.', $jsst_granted, 'js-support-ticket')),
            number_format_i18n($jsst_granted)
        ), 'updated');
        self::licenseGoTo();
    }

    /**
     * Release the licence from this site.
     *
     * A GET with a nonce rather than a form, because it is one button and there
     * is nothing to type. What it must not be is a link somebody can be tricked
     * into following, which is what the nonce is for.
     */
    function releaselicense() {
        self::licenseGuard('jsst-lic-release', 'get');
        JSSTlicense::deactivate();
        /* Said plainly: "deactivated" reads as though something broke. The seat
           is what came back; every setting is still here. */
        JSSTmessage::setMessage(esc_html(__('The license has been released from this site and can now be used on another. Nothing was removed, so putting the key back puts everything back.', 'js-support-ticket')), 'updated');
        self::licenseGoTo();
    }

    /** Ask the licence server now, rather than waiting for the daily check. */
    function rechecklicense() {
        self::licenseGuard('jsst-lic-recheck', 'get');
        $jsst_done = JSSTlicense::refresh();

        if (is_wp_error($jsst_done)) {
            JSSTmessage::setMessage(esc_html($jsst_done->get_error_message()), 'error');
            self::licenseGoTo();
        }

        if (JSSTlicense::isActive()) {
            JSSTmessage::setMessage(esc_html(__('Checked. The license is in order.', 'js-support-ticket')), 'updated');
            self::licenseGoTo();
        }

        $jsst_message = (string) JSSTlicense::state()['message'];
        JSSTmessage::setMessage('' !== $jsst_message
            ? esc_html($jsst_message)
            : esc_html(__('Checked, and the license is not currently valid for this site.', 'js-support-ticket')), 'error');
        self::licenseGoTo();
    }

    /** Every licence action starts here: a nonce, and an administrator. */
    private static function licenseGuard($jsst_action, $jsst_method = 'post') {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce', $jsst_method), $jsst_action)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTlicense')) {
            wp_safe_redirect(admin_url('admin.php?page=jssupportticket'));
            exit;
        }
    }

    private static function licenseGoTo() {
        wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=license'));
        exit;
    }

    /* ------------------------------------------------------------------ *
     * Install Add-ons, on the new licence (Roadmap 6.5-ECO-02)
     * ------------------------------------------------------------------ */

    /**
     * Install what was ticked, one add-on at a time.
     *
     * One message per add-on, because a batch of nine can partly succeed and
     * "some failed" is no use to anybody - the customer needs to know which,
     * and why, to know what to do next.
     */
    function installaddons() {
        self::installGuard('jsst-install-addons', 'install_plugins');

        $jsst_known  = self::installRows();
        $jsst_raw    = isset($_POST['products']) ? (array) wp_unslash($_POST['products']) : array();
        $jsst_asked  = array();

        foreach ($jsst_raw as $jsst_product) {
            $jsst_product = sanitize_key((string) $jsst_product);

            /* Only a row the screen offered: granted, and not already here. */
            if (isset($jsst_known[$jsst_product]) && $jsst_known[$jsst_product]['granted'] && !$jsst_known[$jsst_product]['installed']) {
                $jsst_asked[$jsst_product] = true;
            }
        }

        if (array() === $jsst_asked) {
            JSSTmessage::setMessage(esc_html(__('Tick at least one add-on to install.', 'js-support-ticket')), 'error');
            self::installGoTo();
        }

        /* Nine downloads can outlast a 30-second limit on a slow host. */
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }

        foreach (array_keys($jsst_asked) as $jsst_product) {
            $jsst_done = JSSTlicense::install($jsst_product);

            if (is_wp_error($jsst_done)) {
                JSSTmessage::setMessage(esc_html($jsst_done->get_error_message()), 'error');
                continue;
            }

            if ($jsst_done['activated']) {
                JSSTmessage::setMessage(esc_html(sprintf(
                    /* translators: 1: add-on name, 2: version */
                    __('%1$s %2$s is installed and switched on.', 'js-support-ticket'),
                    $jsst_done['name'],
                    $jsst_done['version']
                )), 'updated');
            } else {
                JSSTmessage::setMessage(esc_html(sprintf(
                    /* translators: 1: add-on name, 2: error message */
                    __('%1$s is installed but could not be switched on: %2$s', 'js-support-ticket'),
                    $jsst_done['name'],
                    $jsst_done['message']
                )), 'error');
            }
        }

        self::installGoTo();
    }

    /**
     * Switch on one add-on that is installed but inactive.
     *
     * The nonce carries the product, so a link minted for one add-on cannot be
     * replayed to switch on another.
     */
    function activateaddon() {
        $jsst_product = sanitize_key((string) JSSTrequest::getVar('product', 'get', ''));
        self::installGuard('jsst-activate-addon_' . $jsst_product, 'activate_plugins', 'get');

        $jsst_known = self::installRows();

        if (!isset($jsst_known[$jsst_product]) || !$jsst_known[$jsst_product]['installed']) {
            JSSTmessage::setMessage(esc_html(__('That add-on is not installed on this site.', 'js-support-ticket')), 'error');
            self::installGoTo();
        }

        $jsst_result = activate_plugin($jsst_known[$jsst_product]['file']);

        if (is_wp_error($jsst_result)) {
            JSSTmessage::setMessage(esc_html(sprintf(
                /* translators: 1: add-on name, 2: error message */
                __('%1$s could not be switched on: %2$s', 'js-support-ticket'),
                $jsst_known[$jsst_product]['name'],
                $jsst_result->get_error_message()
            )), 'error');
        } else {
            JSSTmessage::setMessage(esc_html(sprintf(
                /* translators: %s: add-on name */
                __('%s is switched on.', 'js-support-ticket'),
                $jsst_known[$jsst_product]['name']
            )), 'updated');
        }

        self::installGoTo();
    }

    /**
     * The nonce, then the capability. Installing code on a site is a stronger
     * right than managing the help desk, so it is WordPress's own capability
     * that is asked for, not the plugin's.
     */
    private static function installGuard($jsst_action, $jsst_cap, $jsst_method = 'post') {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce', $jsst_method), $jsst_action)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can($jsst_cap) || !class_exists('JSSTlicense')) {
            wp_safe_redirect(admin_url('admin.php?page=jssupportticket'));
            exit;
        }
    }

    private static function installGoTo() {
        wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=license'));
        exit;
    }

    /**
     * Every bundle, with what the licence says and what the site has.
     *
     * From JSSTbundle, like the License screen, so the two cannot list
     * different sets.
     *
     * @return array product => name, summary, file, granted, installed, active, version
     */
    private static function installRows() {
        $jsst_out = array();

        if (!class_exists('JSSTbundle') || !class_exists('JSSTlicense')) {
            return $jsst_out;
        }

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $jsst_state     = JSSTlicense::state();
        $jsst_granted   = (array) $jsst_state['products'];
        $jsst_installed = JSSTlicense::installed();

        foreach (JSSTbundle::bundles() as $jsst_slug => $jsst_bundle) {
            $jsst_product = 0 === strpos($jsst_slug, 'js-support-ticket-') ? $jsst_slug : 'js-support-ticket-' . $jsst_slug;
            $jsst_file    = $jsst_product . '/' . $jsst_product . '.php';

            $jsst_out[$jsst_product] = array(
                'name'      => isset($jsst_bundle['label']) ? (string) $jsst_bundle['label'] : $jsst_product,
                'summary'   => isset($jsst_bundle['summary']) ? (string) $jsst_bundle['summary'] : '',
                'file'      => $jsst_file,
                'granted'   => in_array($jsst_product, $jsst_granted, true),
                'installed' => isset($jsst_installed[$jsst_product]),
                'active'    => isset($jsst_installed[$jsst_product]) && is_plugin_active($jsst_file),
                'version'   => isset($jsst_installed[$jsst_product]) ? (string) $jsst_installed[$jsst_product] : '',
            );
        }

        return $jsst_out;
    }

    /* ------------------------------------------------------------------ *
     * The legacy compatibility programme (Roadmap 4.5-PRO-02)
     * ------------------------------------------------------------------ */

    /**
     * Move one module from its legacy add-on onto Pro.
     *
     * The nonce carries the slug, so a token minted for "move Feedback" cannot
     * be replayed as "move Agents" — which of the two is being confirmed is the
     * whole of what the confirmation is about.
     */
    function migratelegacy() {
        $jsst_slug = sanitize_key(JSSTrequest::getVar('slug', 'get', ''));
        self::legacyGuard('jsst-legacy-migrate-' . $jsst_slug);

        $jsst_done = JSSTlegacy::migrate($jsst_slug);
        if (is_wp_error($jsst_done)) {
            JSSTmessage::setMessage(esc_html($jsst_done->get_error_message()), 'error');
            self::legacyGoTo();
        }

        /* Deliberately leads with the settings rather than with the move. The
           move is what was asked for; the settings surviving it is the thing the
           customer was actually worried about. */
        JSSTmessage::setMessage(sprintf(
            /* translators: %s: number of settings carried across */
            esc_html(_n('Moved. Your %s setting was carried across unchanged, and the add-on has been deactivated rather than deleted — nothing was removed from your site.',
                        'Moved. All %s of your settings were carried across unchanged, and the add-on has been deactivated rather than deleted — nothing was removed from your site.',
                        (int) $jsst_done['settings'], 'js-support-ticket')),
            number_format_i18n($jsst_done['settings'])
        ), 'updated');

        if (!empty($jsst_done['drift'])) {
            JSSTmessage::setMessage(sprintf(
                /* translators: %s: number of permission changes */
                esc_html(_n('The move changed %s permission. It is listed below — please check it before carrying on.',
                            'The move changed %s permissions. They are listed below — please check them before carrying on.',
                            count($jsst_done['drift']), 'js-support-ticket')),
                number_format_i18n(count($jsst_done['drift']))
            ), 'error');
        }
        self::legacyGoTo();
    }

    /** Put a migration back, exactly as it was. */
    function rollbacklegacy() {
        $jsst_token = sanitize_text_field(JSSTrequest::getVar('token', 'get', ''));
        self::legacyGuard('jsst-legacy-rollback-' . $jsst_token);

        $jsst_done = JSSTlegacy::rollback($jsst_token);
        if (is_wp_error($jsst_done)) {
            JSSTmessage::setMessage(esc_html($jsst_done->get_error_message()), 'error');
            self::legacyGoTo();
        }
        JSSTmessage::setMessage(esc_html(__('Put back. The add-on is active again and its settings are exactly as they were before the move.', 'js-support-ticket')), 'updated');
        self::legacyGoTo();
    }

    private static function legacyGuard($jsst_action) {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce', 'get'), $jsst_action)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTlegacy')) {
            wp_safe_redirect(admin_url('admin.php?page=jssupportticket'));
            exit;
        }
    }

    private static function legacyGoTo() {
        wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=legacy'));
        exit;
    }
}

$jsst_controlpanelController = new JSSTjssupportticketController();
?>
