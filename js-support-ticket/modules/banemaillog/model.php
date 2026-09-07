<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Blocked-sender log — part of the free core. (Roadmap 4.0-CORE-12)
 *
 * The add-on's model with prepared statements and its own schema guard. Blocking
 * a sender is useless if nobody can see what was blocked, so the log now follows
 * the block list into core instead of disappearing the moment the stand-alone Ban
 * Email add-on is deactivated. (Roadmap 4.0-CORE-19)
 *
 * Same js_ticket_banlist_log table and columns as the add-on, so an existing log
 * carries over with no migration; JSSTincluder::getPluginPath() resolves this
 * module to core whether or not the add-on is installed.
 */
class JSSTbanemaillogModel {

    /** Bumped when the table layout below changes. */
    const SCHEMA_VERSION = '400';

    /**
     * Create the log table if this site never had the add-on.
     */
    public static function ensureSchema() {
        // Nothing here is added by ALTER, so the table's existence is the whole
        // check — an uninstalled add-on can take it away. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_banemaillog_schema', self::SCHEMA_VERSION,
                array('js_ticket_banlist_log' => array()))) {
            return;
        }
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_banlist_log';
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    loggeremail varchar(255) DEFAULT NULL,
                    title varchar(255) DEFAULT NULL,
                    log text,
                    logger varchar(255) DEFAULT NULL,
                    ipaddress varchar(64) DEFAULT NULL,
                    created datetime DEFAULT NULL,
                    PRIMARY KEY (id)
                ) " . $jsst_charset);

        // The screen orders by created and filters on the address.
        $jsst_indexes = jssupportticket::$_db->get_col('SHOW INDEX FROM `' . $jsst_table . '`', 2);
        if (!is_array($jsst_indexes)) {
            $jsst_indexes = array();
        }
        if (!in_array('jsst_created', $jsst_indexes, true)) {
            jssupportticket::$_db->hide_errors();
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD INDEX `jsst_created` (`created`)');
            jssupportticket::$_db->show_errors();
        }

        update_option('jsst_banemaillog_schema', self::SCHEMA_VERSION, false);
    }

    function getBanEmailLogs() {
        self::ensureSchema();
        // Filter
        $jsst_loggeremail = isset(jssupportticket::$_search['banemail']['loggeremail']) ? jssupportticket::$_search['banemail']['loggeremail'] : '';
        $jsst_inquery = '';
        $jsst_args = array();

        if ($jsst_loggeremail != null) {
            $jsst_inquery .= " WHERE banemaillog.loggeremail LIKE %s";
            $jsst_args[] = '%' . jssupportticket::$_db->esc_like($jsst_loggeremail) . '%';
        }

        jssupportticket::$jsst_data['filter']['loggeremail'] = $jsst_loggeremail;

        // Pagination
        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_banlist_log` AS  banemaillog ";
        $jsst_query .= $jsst_inquery;
        if (!empty($jsst_args)) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_args);
        }
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        // Data
        $jsst_query = "SELECT banemaillog.*
					FROM`" . jssupportticket::$_db->prefix . "js_ticket_banlist_log` AS banemaillog
					";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY banemaillog.created DESC  LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        if (!empty($jsst_args)) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_args);
        }
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    /**
     * The log screen's search box. (Roadmap 4.0-CORE-12)
     */
    function getAdminSearchFormDataBanEmailLog(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'ban-email-log') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['loggeremail'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('loggeremail')));
        $jsst_search_array['search_from_banemaillog'] = 1;
        return $jsst_search_array;
    }

    function storebanemaillog($jsst_data) {
        self::ensureSchema();
        $jsst_data['id'] = isset($jsst_data['id']) ? $jsst_data['id'] : '';
        if (!$jsst_data['id'])
            $jsst_data['created'] = date_i18n('Y-m-d H:i:s');
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);
        $jsst_row = JSSTincluder::getJSTable('banemaillog');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 1) {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
        }
        return;
    }

    /**
     * Kept because the add-on's model had it and core calls it while loading the
     * configuration screen. It was already an empty licence check there, and core
     * has no licence to check, so it stays a no-op rather than a missing method.
     */
    function checkbandata() {
        return;
    }

    function removeBanEmailLog($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        if (!current_user_can('manage_options')) { //only admin can clear the log.
            return false;
        }
        // JSSTtable::delete() records its own failure, so nothing to add here.
        $jsst_row = JSSTincluder::getJSTable('banemaillog');
        $jsst_row->delete($jsst_id);
        return;
    }

}

?>
