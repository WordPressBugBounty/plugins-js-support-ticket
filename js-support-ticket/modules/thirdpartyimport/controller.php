<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTthirdpartyimportController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'thirdpartyimport');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_importdata':
                    $jsst_selected_plugin = absint( JSSTrequest::getVar('selected_plugin', '', 0) );
                    jssupportticket::$jsst_data['count_for'] = $jsst_selected_plugin;
                    if($jsst_selected_plugin == 1){
                        // prepare data for supportcandy plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getSupportCandyDataStats($jsst_selected_plugin);
                    } elseif($jsst_selected_plugin == 2){
                        // prepare data for awesome Awesome Support plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getAwesomeSupportStats($jsst_selected_plugin);
                    } elseif($jsst_selected_plugin == 3){
                        // prepare data for Fluent Support plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getFluentSupportDataStats($jsst_selected_plugin);
                    } 
                    // no plugin selected
                    break;
                case 'admin_importresult':
                    break;
                /* The counted preview, and the report on a finished run. These
                   sit beside the original Import Data screen rather than
                   replacing it: that screen still works, and a site part way
                   through an import on it should not find it gone.
                   (Roadmap 4.0-DATA-01) */
                case 'admin_migrationpreview':
                    jssupportticket::$jsst_data['sources'] = JSSTmigration::sources();
                    jssupportticket::$jsst_data['inprogress'] = JSSTmigration::inProgress();
                    jssupportticket::$jsst_data['latest'] = JSSTmigration::latest('csv');
                    jssupportticket::$jsst_data['source'] = '';
                    jssupportticket::$jsst_data['report'] = null;
                    $jsst_source = sanitize_key(JSSTrequest::getVar('source', '', ''));
                    if ($jsst_source !== '') {
                        jssupportticket::$jsst_data['source'] = $jsst_source;
                        jssupportticket::$jsst_data['report'] = JSSTmigrationpreview::report($jsst_source);
                    }
                    break;
                case 'admin_migrationresult':
                    $jsst_token = sanitize_text_field(JSSTrequest::getVar('token', '', ''));
                    $jsst_run = ($jsst_token !== '') ? JSSTmigration::get($jsst_token) : JSSTmigration::latest('csv');
                    jssupportticket::$jsst_data['run'] = $jsst_run;
                    jssupportticket::$jsst_data['counts'] = array();
                    jssupportticket::$jsst_data['journal'] = array();
                    jssupportticket::$jsst_data['findings'] = array();
                    if ($jsst_run) {
                        // Both columns are null on a run that has not reached the
                        // point of writing them.
                        $jsst_counts = json_decode((string) $jsst_run->counts, true);
                        $jsst_findings = json_decode((string) $jsst_run->findings, true);
                        jssupportticket::$jsst_data['counts'] = is_array($jsst_counts) ? $jsst_counts : array();
                        jssupportticket::$jsst_data['findings'] = is_array($jsst_findings) ? $jsst_findings : array();
                        jssupportticket::$jsst_data['journal'] = JSSTmigration::journalSummary($jsst_run->token);
                    }
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'thirdpartyimport');
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

    function importPluginData() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'importPluginData') ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_selected_plugin = JSSTrequest::getVar('selected_plugin', '', 0);
        jssupportticket::$jsst_data['count_for'] = $jsst_selected_plugin;
        if($jsst_selected_plugin == 1){
            JSSTincluder::getJSModel('thirdpartyimport')->importSupportCandyData();
        } elseif($jsst_selected_plugin == 2){
            JSSTincluder::getJSModel('thirdpartyimport')->importAwesomeSupportData();
        } elseif($jsst_selected_plugin == 3){
            JSSTincluder::getJSModel('thirdpartyimport')->importFluentSupportData();
        }
        $jsst_url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult&selected_plugin=".$jsst_selected_plugin);
        wp_safe_redirect($jsst_url);
        exit;
    }

    function getSupportCandyDataStats() {
        $jsst_id = JSSTrequest::getVar('statusid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        JSSTincluder::getJSModel('thirdpartyimport')->getSupportCandyDataStats( absint( $jsst_id ) );
        $jsst_url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult");
        wp_safe_redirect($jsst_url);
        exit;
    }

    function getFluentSupportStats() {
        $jsst_id = JSSTrequest::getVar('statusid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        JSSTincluder::getJSModel('thirdpartyimport')->getFluentSupportStats( absint( $jsst_id ) );
        $jsst_url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult");
        wp_safe_redirect($jsst_url);
        exit;
    }

    /* ------------------------------------------------------------------ *
     * The migration (Roadmap 4.0-DATA-01)
     * ------------------------------------------------------------------ */

    /**
     * Run an import inside a migration.
     *
     * The importers themselves are the ones that have always been here — this
     * does not reimplement them. What it adds is the record around them: a
     * migration row to run under, the source count taken *before* anything is
     * written so the checks afterwards have something to compare against, and
     * the journal switched on for exactly the stretch of work that belongs to
     * this import and off again the moment it ends, however it ends.
     */
    function startmigration() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-migration-start')) {
            die('Security check Failed');
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_source = sanitize_key(JSSTrequest::getVar('source', '', ''));
        $jsst_url = admin_url('admin.php?page=thirdpartyimport&jstlay=migrationpreview');

        $jsst_sources = JSSTmigration::sources();
        if (!isset($jsst_sources[$jsst_source]) || empty($jsst_sources[$jsst_source]['present'])) {
            JSSTmessage::setMessage(esc_html(__('Nothing from that help desk was found on this site.', 'js-support-ticket')), 'error', 'migration');
            self::goTo($jsst_url);
        }
        /* Two imports at once would interleave their journals and make either
           one impossible to roll back on its own. */
        if (JSSTmigration::inProgress()) {
            JSSTmessage::setMessage(esc_html(__('An import is already running. Wait for it to finish, or roll it back, before starting another.', 'js-support-ticket')), 'error', 'migration');
            self::goTo($jsst_url);
        }

        $jsst_report = JSSTmigrationpreview::report($jsst_source);
        if (is_wp_error($jsst_report)) {
            JSSTmessage::setMessage(esc_html($jsst_report->get_error_message()), 'error', 'migration');
            self::goTo($jsst_url);
        }

        $jsst_token = JSSTmigration::start($jsst_source, $jsst_sources[$jsst_source]['version']);
        // Counted from the source now, because after the import the source may
        // have been deleted and there would be nothing left to check against.
        JSSTmigration::setCursor($jsst_token, array('expected_tickets' => self::expectedTickets($jsst_report)));

        // Cleared first: the importers write their tallies over this option, and
        // a leftover set from an earlier run would be read as this one's.
        update_option('jsst_import_counts', array());

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        JSSTmigration::startRecording($jsst_token);
        try {
            $jsst_model = JSSTincluder::getJSModel('thirdpartyimport');
            switch ($jsst_source) {
                case 'supportcandy':
                    $jsst_model->importSupportCandyData();
                    break;
                case 'awesomesupport':
                    $jsst_model->importAwesomeSupportData();
                    break;
                case 'fluentsupport':
                    $jsst_model->importFluentSupportData();
                    break;
            }
        } catch (Exception $jsst_e) {
            JSSTmigration::stopRecording();
            JSSTmigration::setStatus($jsst_token, 'failed', $jsst_e->getMessage());
            JSSTmessage::setMessage(esc_html($jsst_e->getMessage()), 'error', 'migration');
            self::goTo(admin_url('admin.php?page=thirdpartyimport&jstlay=migrationresult&token=' . rawurlencode($jsst_token)));
        }
        JSSTmigration::stopRecording();

        $jsst_counts = get_option('jsst_import_counts');
        JSSTmigration::setCounts($jsst_token, is_array($jsst_counts) ? $jsst_counts : array());
        JSSTmigration::setStatus($jsst_token, 'complete');
        // Run after the status is set, so a check that reads the row sees a
        // finished run rather than one still marked as going.
        JSSTmigration::setFindings($jsst_token, JSSTmigrationvalidator::validate($jsst_token, $jsst_source));

        self::goTo(admin_url('admin.php?page=thirdpartyimport&jstlay=migrationresult&token=' . rawurlencode($jsst_token)));
    }

    /**
     * Take a migration back out.
     *
     * Refuses a CSV import's token: those are undone from their own screen,
     * which is where somebody who ran one would look for the button.
     */
    function rollbackmigration() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-migration-rollback')) {
            die('Security check Failed');
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_token = sanitize_text_field(JSSTrequest::getVar('token', '', ''));
        $jsst_run = JSSTmigration::get($jsst_token);
        $jsst_url = admin_url('admin.php?page=thirdpartyimport&jstlay=migrationresult&token=' . rawurlencode($jsst_token));

        if (!$jsst_run || $jsst_run->source === 'csv') {
            JSSTmessage::setMessage(esc_html(__('That import cannot be found.', 'js-support-ticket')), 'error', 'migration');
            self::goTo(admin_url('admin.php?page=thirdpartyimport&jstlay=migrationpreview'));
        }

        JSSTmigration::setStatus($jsst_token, 'rollingback');
        $jsst_removed = JSSTmigration::rollback($jsst_token);
        if (is_wp_error($jsst_removed)) {
            JSSTmigration::setStatus($jsst_token, 'complete');
            JSSTmessage::setMessage(esc_html($jsst_removed->get_error_message()), 'error', 'migration');
            self::goTo($jsst_url);
        }
        JSSTmessage::setMessage(sprintf(
            /* translators: %s: number of records removed */
            esc_html(__('The import was taken back out. %s records were removed.', 'js-support-ticket')),
            number_format_i18n((int) $jsst_removed)
        ), 'updated');
        self::goTo($jsst_url);
    }

    /**
     * How many tickets the source says it holds.
     *
     * Read off the preview's own rows rather than counted again here, so the
     * number the checks compare against is the number somebody was shown on the
     * screen where they pressed start.
     */
    private static function expectedTickets($jsst_report) {
        $jsst_wanted = esc_html(__('Tickets', 'js-support-ticket'));
        foreach ($jsst_report['entities'] as $jsst_entity) {
            if ($jsst_entity['destination'] === $jsst_wanted && !empty($jsst_entity['countable'])) {
                return (int) $jsst_entity['count'];
            }
        }
        return 0;
    }

    private static function goTo($jsst_url) {
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_thirdpartyimportController = new JSSTthirdpartyimportController();
?>
