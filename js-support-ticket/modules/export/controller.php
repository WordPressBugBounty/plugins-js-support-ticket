<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Export controller — part of the free core. (Roadmap 4.0-CORE-10)
 *
 * Same layouts, task names and nonces as the stand-alone Export add-on, so the
 * buttons already on the reports screens keep working. The ticket export streams
 * real CSV; the report-summary exports are still served by the add-on when it is
 * present and are being moved onto the same writer separately (4.0-CORE-10b).
 */
class JSSTexportController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        JSSTincluder::getJSModel('export');
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'export');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_export':
                case 'export':
                    jssupportticket::$jsst_data['permission_granted'] = JSSTexportModel::canExport();
                    if (!jssupportticket::$jsst_data['permission_granted']) {
                        JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
                    }
                    break;
                // The other half of data portability: a documented format for
                // getting tickets back in. (Roadmap 4.0-DATA-02)
                case 'admin_csvimport':
                    jssupportticket::$jsst_data['columns'] = JSSTcsvimport::columns();
                    jssupportticket::$jsst_data['limitations'] = JSSTcsvimport::limitations();
                    jssupportticket::$jsst_data['checked'] = null;
                    jssupportticket::$jsst_data['done'] = null;
                    $jsst_held = sanitize_text_field(JSSTrequest::getVar('held', '', ''));
                    if ($jsst_held !== '') {
                        jssupportticket::$jsst_data['checked'] = JSSTcsvimport::held($jsst_held);
                        jssupportticket::$jsst_data['heldtoken'] = $jsst_held;
                    }
                    /* A finished import comes back to this screen rather than
                       going somewhere else to be read about. What it created and
                       the one button that takes it back out belong next to the
                       format it came from, not on a report about migrations from
                       other help desks. */
                    $jsst_donetoken = sanitize_text_field(JSSTrequest::getVar('done', '', ''));
                    if ($jsst_donetoken !== '') {
                        $jsst_run = JSSTmigration::get($jsst_donetoken);
                        if ($jsst_run && $jsst_run->source === 'csv') {
                            $jsst_counts = json_decode($jsst_run->counts, true);
                            jssupportticket::$jsst_data['done'] = array(
                                'token'    => $jsst_donetoken,
                                'status'   => $jsst_run->status,
                                'made'     => isset($jsst_counts['ticket']['imported']) ? (int) $jsst_counts['ticket']['imported'] : 0,
                                'failed'   => isset($jsst_counts['ticket']['failed']) ? (int) $jsst_counts['ticket']['failed'] : 0,
                                'journal'  => JSSTmigration::journalSummary($jsst_donetoken),
                                'notes'    => $jsst_run->notes,
                            );
                        }
                    }
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'export');
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
                if(!is_admin() && strpos($jsst_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    /**
     * Stream the filtered tickets as CSV. (Roadmap 4.0-CORE-10)
     *
     * Nothing is buffered: the headers go out, then rows are written in batches
     * until the filter is exhausted. An export of 50,000 tickets costs the same
     * memory as one of 50.
     */
    static function getticketsexport() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'get-tickets-export') ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_model = JSSTincluder::getJSModel('export');
        if (!JSSTexportModel::canExport()) {
            wp_die( esc_html__( 'You are not allowed', 'js-support-ticket' ) );
        }
        $jsst_filters = $jsst_model->readFilters();

        // A filter that matches nothing is a mistake worth reporting, rather than
        // a file with only a header row.
        if ($jsst_model->countTickets($jsst_filters) === 0) {
            JSSTmessage::setMessage(esc_html(__('No tickets match those filters, so there was nothing to export.', 'js-support-ticket')), 'error');
            $jsst_url = is_admin()
                ? admin_url('admin.php?page=export')
                : jssupportticket::makeUrl(array('jstmod' => 'export', 'jstlay' => 'export'));
            wp_safe_redirect($jsst_url);
            exit;
        }

        // Long exports must not die halfway through a download.
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        /* The format is the caller's, and the writers take the same rows -
           so this is a choice of writer and nothing else. An unavailable
           format falls back to CSV inside writerFor() rather than failing:
           somebody who asked for a spreadsheet on a host with no zip extension
           should get their data, and the screen has already told them why the
           option is not on offer. (Roadmap 5.0-ANA-01) */
        $jsst_format = sanitize_key(JSSTrequest::getVar('exportformat', 'post', 'csv'));
        $jsst_anonymise = (JSSTrequest::getVar('exportanonymise', 'post', '') !== '');

        /* Asked to leave the customers out, and unable to. (Roadmap 6.5-DATA-05)
         *
         * The pseudonyms are `JSSTexports::anonymiseRow()`, which lives in the
         * Reporting & Compliance bundle, so on a desk that does not have it
         * installed this request cannot be honoured. It used to be skipped -
         * `if ($jsst_anonymise && class_exists('JSSTexports'))` - and the export
         * then ran with every name, login, address and telephone in it. The
         * filename was built from `$jsst_anonymise` alone with no such guard, so
         * what came back was a file called `...-anonymised.csv` containing the
         * customers in full. Somebody sending that to an analyst has been told
         * twice that it is safe.
         *
         * So this refuses instead of falling back, and that is the opposite of
         * what `writerFor()` does one line below on purpose: a spreadsheet
         * falling back to CSV loses a convenience, and an anonymised export
         * falling back to an identifying one loses the entire reason the box was
         * ticked. A fallback is only ever right when the lesser thing is still
         * the thing that was asked for. */
        if ($jsst_anonymise && !class_exists('JSSTexports')) {
            JSSTmessage::setMessage(esc_html(__('This export was not run. Leaving the customers out needs the Reporting & Compliance add-on, which is not active on this site — and an export that quietly kept them in would be worse than none. Activate it, or clear that box to export with the customers named.', 'js-support-ticket')), 'error');
            $jsst_url = is_admin()
                ? admin_url('admin.php?page=export')
                : jssupportticket::makeUrl(array('jstmod' => 'export', 'jstlay' => 'export'));
            wp_safe_redirect($jsst_url);
            exit;
        }

        $jsst_writer = class_exists('JSSTexports')
            ? JSSTexports::writerFor($jsst_format) : new JSSTcsvwriter();
        /* Unguarded: the refusal above is what makes the class certain here, and
           re-testing for it would put the silent-skip path back. */
        if ($jsst_anonymise) {
            JSSTexports::anonymiseFrom();
        }
        /* A printed export keeps the eight columns a report is read by. Twenty
           columns on a sheet of paper gives each of them about seven characters
           and every value becomes an ellipsis. (Roadmap 5.0-ANA-01) */
        if ($jsst_format === 'pdf' && class_exists('JSSTexports')) {
            JSSTexports::slimFrom();
        }
        $jsst_name = $jsst_model->ticketFilename($jsst_filters) . ($jsst_anonymise ? '-anonymised' : '');
        $jsst_writer->start($jsst_name);
        if (method_exists($jsst_writer, 'title')) {
            $jsst_writer->title($jsst_name);
        }
        $jsst_model->streamTickets($jsst_filters, $jsst_writer);
        $jsst_writer->finish();
    }


    /* ------------------------------------------------------------------ *
     * CSV import (Roadmap 4.0-DATA-02)
     * ------------------------------------------------------------------ */

    /**
     * Hand over a template file built from the format itself.
     *
     * Generated rather than stored, so it can never describe a format the
     * importer no longer reads.
     */
    function downloadcsvtemplate() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-csv-template')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        list($jsst_header, $jsst_example) = JSSTcsvimport::template();
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="js-help-desk-ticket-import-template.csv"');
        $jsst_out = fopen('php://output', 'w');
        // Escape `''` for RFC 4180, and named rather than defaulted: PHP 8.4
        // deprecates the implicit default. Matches JSSTcsvwriter::row().
        fputcsv($jsst_out, $jsst_header, ',', '"', '');
        fputcsv($jsst_out, $jsst_example, ',', '"', '');
        fclose($jsst_out); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- php:// stream, not a file: WP_Filesystem has no API for stream wrappers
        exit;
    }

    /**
     * Read an uploaded file and report on it, writing nothing.
     *
     * The upload is read from its temporary path and never moved into the
     * uploads directory: the file holds customers' tickets, it is only needed
     * for the seconds it takes to check it, and a copy left in a web-served
     * folder is a copy somebody has to remember to delete.
     */
    function checkcsvfile() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-csv-check')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_url = admin_url('admin.php?page=export&jstlay=csvimport');

        if (!isset($_FILES['csvfile']) || !isset($_FILES['csvfile']['tmp_name']) || $_FILES['csvfile']['tmp_name'] === '') {
            JSSTmessage::setMessage(esc_html(__('No file was chosen.', 'js-support-ticket')), 'error', 'csv-import');
            self::goTo($jsst_url);
        }
        if (!empty($_FILES['csvfile']['error'])) {
            // The commonest of these by far is a file larger than the server
            // allows, which says nothing about the file itself.
            JSSTmessage::setMessage(esc_html(__('The upload did not complete. If the file is large, the server upload limit is the usual reason.', 'js-support-ticket')), 'error', 'csv-import');
            self::goTo($jsst_url);
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- a path from PHP's own upload handling, used only to read.
        $jsst_path = $_FILES['csvfile']['tmp_name'];
        if (!is_uploaded_file($jsst_path)) {
            JSSTmessage::setMessage(esc_html(__('That was not an uploaded file.', 'js-support-ticket')), 'error', 'csv-import');
            self::goTo($jsst_url);
        }

        $jsst_result = JSSTcsvimport::check($jsst_path);
        if (is_wp_error($jsst_result)) {
            JSSTmessage::setMessage(esc_html($jsst_result->get_error_message()), 'error', 'csv-import');
            self::goTo($jsst_url);
        }
        $jsst_token = JSSTcsvimport::hold($jsst_result);
        self::goTo($jsst_url . '&held=' . rawurlencode($jsst_token));
    }

    /**
     * Create the tickets from a file that has already been checked.
     *
     * Works from what was held rather than re-reading an upload, so what is
     * imported is exactly what was shown — there is no second parse between the
     * report somebody approved and the rows that get written.
     */
    function runcsvimport() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-csv-import')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_url = admin_url('admin.php?page=export&jstlay=csvimport');
        $jsst_token = sanitize_text_field(JSSTrequest::getVar('held', '', ''));
        $jsst_held = JSSTcsvimport::held($jsst_token);
        if (!$jsst_held) {
            JSSTmessage::setMessage(esc_html(__('That file is no longer waiting to be imported. Upload it again.', 'js-support-ticket')), 'error', 'csv-import');
            self::goTo($jsst_url);
        }

        $jsst_done = JSSTcsvimport::import($jsst_held['rows']);
        if (is_wp_error($jsst_done)) {
            JSSTmessage::setMessage(esc_html($jsst_done->get_error_message()), 'error', 'csv-import');
            self::goTo($jsst_url);
        }
        JSSTcsvimport::release($jsst_token);

        JSSTmessage::setMessage(sprintf(
            /* translators: %s: number of tickets created */
            esc_html(__('%s tickets were created.', 'js-support-ticket')),
            number_format_i18n((int) $jsst_done['made'])
        ), 'updated');
        self::goTo($jsst_url . '&done=' . rawurlencode($jsst_done['token']));
    }

    /**
     * Take an import back out.
     *
     * The rollback is the migration's own — it deletes exactly the rows the
     * journal recorded, newest first — so this is a thin wrapper whose only job
     * is to refuse tokens that are not a CSV import's. Without that check the
     * URL would undo a help-desk migration from this screen.
     */
    function rollbackcsvimport() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-csv-rollback')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_url = admin_url('admin.php?page=export&jstlay=csvimport');
        $jsst_token = sanitize_text_field(JSSTrequest::getVar('done', '', ''));
        $jsst_run = JSSTmigration::get($jsst_token);
        if (!$jsst_run || $jsst_run->source !== 'csv') {
            JSSTmessage::setMessage(esc_html(__('That import cannot be found.', 'js-support-ticket')), 'error', 'csv-import');
            self::goTo($jsst_url);
        }

        $jsst_removed = JSSTmigration::rollback($jsst_token);
        if (is_wp_error($jsst_removed)) {
            JSSTmessage::setMessage(esc_html($jsst_removed->get_error_message()), 'error', 'csv-import');
            self::goTo($jsst_url);
        }
        JSSTmessage::setMessage(sprintf(
            /* translators: %s: number of records removed */
            esc_html(__('The import was taken back out. %s records were removed.', 'js-support-ticket')),
            number_format_i18n((int) $jsst_removed)
        ), 'updated');
        self::goTo($jsst_url);
    }

    private static function goTo($jsst_url) {
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_exportController = new JSSTexportController();
