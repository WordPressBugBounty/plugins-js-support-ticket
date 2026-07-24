<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTsystemerrorModel {

    function getSystemErrors() {
        $jsst_inquery = '';
        // Pagination
        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_system_errors`";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        // Data
        $jsst_query = " SELECT systemerror.*
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_system_errors` AS systemerror ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY systemerror.created DESC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            $this->addSystemError();
        }
        return;
    }

    function addSystemError($jsst_error = null) {
        // 1. Always gather request context URL safely
        $jsst_error_data = array(
            'url' => isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : 'Unknown URL'
        );

        // 2. Differentiate between an explicit custom error string and an internal DB error
        if ($jsst_error !== null && $jsst_error !== '') {
            
            // Custom string error provided
            $jsst_error_data['error'] = $jsst_error;

        } else {
            // Internal database error: gather context signals
            $jsst_error_msg    = jssupportticket::$_db->last_error;
            $jsst_failed_query = jssupportticket::$_db->last_query;

            // Generate a mid-level execution trace (up to 5 levels deep)
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- intentional: builds the call path stored in the plugin's own system-error log (admin-only), not debug output; DEBUG_BACKTRACE_IGNORE_ARGS avoids capturing argument values.
            $jsst_raw_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
            $jsst_trace_path    = array();

            foreach ($jsst_raw_backtrace as $jsst_trace) {
                $jsst_func = isset($jsst_trace['function']) ? $jsst_trace['function'] : 'Unknown';
                $jsst_file = isset($jsst_trace['file']) ? basename($jsst_trace['file']) : 'Unknown';

                // Skip logging this tracking function itself
                if ($jsst_func !== 'addSystemError') {
                    $jsst_trace_path[] = "$jsst_func ($jsst_file)";
                }
            }

            $jsst_called_by_path = implode(' <- ', $jsst_trace_path);

            $jsst_error_data['error'] = $jsst_error_msg ? $jsst_error_msg : 'Unknown DB Error';
            $jsst_error_data['query'] = $jsst_failed_query ? $jsst_failed_query : 'No query recorded';
            $jsst_error_data['path']  = $jsst_called_by_path;
        }

        // 3. Construct payload matching Help Desk internal architecture
        $jsst_query_array = array(
            'error'   => wp_json_encode($jsst_error_data),
            'uid'     => JSSTincluder::getObjectClass('user')->uid(),
            'isview'  => 0,
            'created' => date_i18n('Y-m-d H:i:s')
        );

        // 4. Execute atomic database update/replace routine
        jssupportticket::$_db->replace(jssupportticket::$_db->prefix . 'js_ticket_system_errors', $jsst_query_array);

        // 5. Clear state parameters to break diagnostic persistence issues
        jssupportticket::$_db->last_error = '';
        jssupportticket::$_db->last_query = '';

        return;
    }

    function updateIsView($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = jssupportticket::$_db->prepare("UPDATE " . jssupportticket::$_db->prefix . "`js_ticket_system_errors` set isview = 1 WHERE id = %d", $jsst_id);
        jssupportticket::$_db->Query($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            $this->addSystemError();
        }
    }

    function removeSystemError($jsst_id) {
        if ($jsst_id == 'all') {
            $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_system_errors` ";
            jssupportticket::$_db->query($jsst_query);
            JSSTmessage::setMessage(esc_html(__('System error has been deleted', 'js-support-ticket')), 'updated');
        }else{
            if (!is_numeric($jsst_id)){
                return false;
            }
            $jsst_row = JSSTincluder::getJSTable('system_errors');
            if ($jsst_row->delete($jsst_id)) {
                JSSTmessage::setMessage(esc_html(__('System error has been deleted', 'js-support-ticket')), 'updated');
            } else {
                JSSTmessage::setMessage(esc_html(__('System error has not been deleted', 'js-support-ticket')), 'error');
            }
        }
        return;
    }

}

?>
