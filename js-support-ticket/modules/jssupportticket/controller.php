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
			        include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
			        JSSTupdates::checkUpdates();
                    JSSTincluder::getJSModel('jssupportticket')->getControlPanelDataAdmin();
                    break;
                case 'controlpanel':
                    JSSTincluder::getJSModel('jssupportticket')->getControlPanelData();
                    include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
                    JSSTupdates::checkUpdates('317');
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
                case 'admin_addonstatus':
                    JSSTincluder::getJSModel('jssupportticket')->jsst_check_license_status();
                    break;
                case 'admin_help':
                    break;
                case 'admin_translations':
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

    static function addmissingusers() {
        if(!is_admin())
            return false;
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'add-missing-users') ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('jssupportticket')->addMissingUsers();
        $jsst_url = admin_url("admin.php?page=jssupportticket");
        wp_safe_redirect($jsst_url);
        exit;
    }

    function saveordering(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-ordering') ) {
            die( 'Security check Failed' );
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
                $jsst_url = admin_url("admin.php?page=multiform&jstlay=multiform");
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
            die( 'Security check Failed' );
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
            die('Security check Failed');
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
            die('Security check Failed');
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
            die('Security check Failed');
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
}

$jsst_controlpanelController = new JSSTjssupportticketController();
?>
