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
        if($jsst_error == null) $jsst_error = jssupportticket::$_db->last_error;
        $jsst_query_array = array('error' => $jsst_error,
            'uid' => JSSTincluder::getObjectClass('user')->uid(),
            'isview' => 0,
            'created' => date_i18n('Y-m-d H:i:s')
        );
        jssupportticket::$_db->replace(jssupportticket::$_db->prefix . 'js_ticket_system_errors', $jsst_query_array);
        // if (jssupportticket::$_db->last_error != null) {
        //     $this->addSystemError();
        // }
        return;
    }

    function updateIsView($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        $jsst_query = "UPDATE " . jssupportticket::$_db->prefix . "`js_ticket_system_errors` set isview = 1 WHERE id = " . esc_sql($jsst_id);
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
