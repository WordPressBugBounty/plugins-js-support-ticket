<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Blocked senders — part of the free core. (Roadmap 4.0-CORE-12)
 *
 * The add-on's model with prepared statements, validation on what may be added,
 * and the domain blocking the roadmap row asks for: an entry may be one address
 * or a whole domain, and a domain entry blocks every address at it.
 *
 * Loaded only when the stand-alone Ban Email add-on is inactive:
 * JSSTincluder::getPluginPath() resolves the 'banemail' module to the add-on
 * directory while that add-on is active. The js_ticket_email_banlist table keeps
 * its name and layout, so an existing block list carries over. (Roadmap 4.0-CORE-19)
 *
 * Wildcards and regular expressions, imported lists, automation and reputation
 * signals stay Pro. The ban log does not: JSSTbanemaillogModel serves it from
 * core, so js_ticket_banlist_log keeps being written and the log screen keeps
 * working with the add-on deactivated. (Roadmap 4.0-CORE-19)
 */
class JSSTbanemailModel {

    /** Bumped when the table layout below changes. */
    const SCHEMA_VERSION = '400';

    /**
     * Create the block list table if this site never had the add-on.
     */
    public static function ensureSchema() {
        // Nothing here is added by ALTER, so the table's existence is the whole
        // check — an uninstalled add-on can take it away. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_banemail_schema', self::SCHEMA_VERSION,
                array('js_ticket_email_banlist' => array()))) {
            return;
        }
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_email_banlist';
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    email varchar(255) DEFAULT NULL,
                    submitter varchar(126) DEFAULT NULL,
                    uid int(11) NOT NULL,
                    created datetime DEFAULT NULL,
                    PRIMARY KEY (id)
                ) " . $jsst_charset);

        // Every submission checks this list. (Roadmap 4.0-PERF-01)
        $jsst_indexes = jssupportticket::$_db->get_col('SHOW INDEX FROM `' . $jsst_table . '`', 2);
        if (!is_array($jsst_indexes)) {
            $jsst_indexes = array();
        }
        if (!in_array('jsst_email', $jsst_indexes, true)) {
            jssupportticket::$_db->hide_errors();
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD INDEX `jsst_email` (`email`)');
            jssupportticket::$_db->show_errors();
        }

        update_option('jsst_banemail_schema', self::SCHEMA_VERSION, false);
    }

    /**
     * What an entry means: one address, or a whole domain.
     *
     * A value with a local part is an address. A bare domain, with or without a
     * leading @, blocks everything at that domain. Anything else is not a usable
     * entry. (Roadmap 4.0-CORE-12)
     *
     * @return array('type' => 'email'|'domain'|'', 'value' => string)
     */
    public static function classifyEntry($jsst_value) {
        $jsst_value = strtolower(trim((string) $jsst_value));
        $jsst_value = preg_replace('/\s+/', '', $jsst_value);
        if ($jsst_value === '') {
            return array('type' => '', 'value' => '');
        }
        if (strpos($jsst_value, '@') === 0) {
            $jsst_domain = substr($jsst_value, 1);
            return self::isDomain($jsst_domain)
                ? array('type' => 'domain', 'value' => '@' . $jsst_domain)
                : array('type' => '', 'value' => '');
        }
        if (strpos($jsst_value, '@') !== false) {
            return is_email($jsst_value)
                ? array('type' => 'email', 'value' => $jsst_value)
                : array('type' => '', 'value' => '');
        }
        // No @ at all: treat as a domain, stored with the @ so the intent is
        // obvious on the list screen.
        return self::isDomain($jsst_value)
            ? array('type' => 'domain', 'value' => '@' . $jsst_value)
            : array('type' => '', 'value' => '');
    }

    /**
     * Does this look like a hostname with a public suffix?
     */
    public static function isDomain($jsst_value) {
        $jsst_value = (string) $jsst_value;
        if ($jsst_value === '' || strpos($jsst_value, '.') === false) {
            return false;
        }
        return (bool) preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))*\.[a-z]{2,63}$/', $jsst_value);
    }

    /**
     * The domain part of an address, lower-cased.
     */
    public static function domainOf($jsst_emailaddress) {
        $jsst_at = strrpos((string) $jsst_emailaddress, '@');
        if ($jsst_at === false) {
            return '';
        }
        return strtolower(substr($jsst_emailaddress, $jsst_at + 1));
    }

    function getbanemails() {
        // Filter
        self::ensureSchema();
        $jsst_email = isset(jssupportticket::$_search['banemail']['email']) ? jssupportticket::$_search['banemail']['email'] : '';
        $jsst_inquery = '';
        $jsst_args = array();

        if ($jsst_email != null) {
            $jsst_inquery .= " WHERE banemail.email LIKE %s";
            $jsst_args[] = '%' . jssupportticket::$_db->esc_like($jsst_email) . '%';
        }

        jssupportticket::$jsst_data['filter']['email'] = $jsst_email;

        // Pagination
        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` AS  banemail";
        $jsst_query .= $jsst_inquery;
        if (!empty($jsst_args)) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_args);
        }
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        // Data
        do_action('jsstBanEmails');// to prepare any addon based query
        $jsst_query = "SELECT banemail.* ". jssupportticket::$_addon_query['select'] ." ,user.user_nicename
					FROM`" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` AS banemail
                    ". jssupportticket::$_addon_query['join'] . "
					LEFT JOIN `" . jssupportticket::$_wpprefixforuser . "js_ticket_users` AS user ON banemail.uid = user.id";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY banemail.created DESC ,banemail.email ASC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        if (!empty($jsst_args)) {
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_args);
        }
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        do_action('jsst_reset_aadon_query');
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    /**
     * The block list screen's search box. (Roadmap 4.0-CORE-12)
     */
    function getAdminSearchFormDataBanEmail(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'banemail') ) {
            die( 'Security check Failed' );
        }
        $jsst_search_array = array();
        $jsst_search_array['email'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('email')));
        $jsst_search_array['search_from_banemail'] = 1;
        return $jsst_search_array;
    }

    function getBanEmailForForm($jsst_id) {
        if ($jsst_id) {
            if (!is_numeric($jsst_id))
                return false;
            $jsst_query = "SELECT banemail.*
						FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` AS banemail
						WHERE banemail.id = %d";
            $jsst_query = jssupportticket::$_db->prepare($jsst_query, $jsst_id);
            jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_row($jsst_query);
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            }
        }
        return;
    }

    function storeBanEmail($jsst_data) {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        self::ensureSchema();
        $jsst_data['id'] = isset($jsst_data['id']) ? $jsst_data['id'] : '';
        if (!$jsst_data['id'])
            $jsst_data['created'] = date_i18n('Y-m-d H:i:s'); // new
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);

        // An entry that is neither an address nor a domain would sit in the list
        // blocking nothing, which reads as the block having silently failed.
        $jsst_entry = self::classifyEntry(isset($jsst_data['email']) ? $jsst_data['email'] : '');
        if ($jsst_entry['type'] === '') {
            JSSTmessage::setMessage(esc_html(__('Enter an e-mail address, or a domain to block every address at it.', 'js-support-ticket')), 'error');
            return false;
        }
        $jsst_data['email'] = $jsst_entry['value'];

        // The same entry twice would make the list confusing and the count wrong.
        $jsst_existing = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE LOWER(email) = %s",
            $jsst_entry['value']
        ));
        if ($jsst_existing && (string) $jsst_existing !== (string) $jsst_data['id']) {
            JSSTmessage::setMessage(esc_html(__('That address or domain is already blocked.', 'js-support-ticket')), 'error');
            return false;
        }

        $jsst_data['uid'] = JSSTincluder::getObjectClass('user')->uid();

        $jsst_row = JSSTincluder::getJSTable('banemail');

        $jsst_data = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data);// remove slashes with quotes.
        $jsst_error = 0;
        if (!$jsst_row->bind($jsst_data)) {
            $jsst_error = 1;
        }
        if (!$jsst_row->store()) {
            $jsst_error = 1;
        }

        if ($jsst_error == 0) {
            JSSTmessage::setMessage(__('Ban email has been stored', 'js-support-ticket'), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(__('Ban email has not been stored', 'js-support-ticket'), 'error');
        }
        return;
    }

    function removeBanEmail($jsst_id) {
        if (!is_numeric($jsst_id))
            return false;
        if (!current_user_can('manage_options')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return false;
        }
        if ($this->canRemoveBanEmail($jsst_id)) {
            $jsst_row = JSSTincluder::getJSTable('banemail');
            if ($jsst_row->delete($jsst_id)) {
                JSSTmessage::setMessage(__('Ban email has been deleted', 'js-support-ticket'), 'updated');
            } else {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                JSSTmessage::setMessage(__('Ban email has not been deleted', 'js-support-ticket'), 'error');
            }
        } else {
            JSSTmessage::setMessage(__('Ban email does not exist', 'js-support-ticket'), 'error');
        }
        return;
    }

    private function canRemoveBanEmail($jsst_id) {
        // The add-on returned true unconditionally, so deleting a row that was
        // already gone reported success.
        $jsst_exists = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE id = %d",
            $jsst_id
        ));
        return !empty($jsst_exists);
    }

    /**
     * Is this address blocked, either in its own right or by its domain?
     *
     * The add-on compared the address only, so blocking a throwaway domain meant
     * adding every address by hand. (Roadmap 4.0-CORE-12)
     */
    function isEmailBan($jsst_emailaddress) {
        $jsst_emailaddress = strtolower(trim((string) $jsst_emailaddress));
        if ($jsst_emailaddress === '') {
            return false;
        }
        self::ensureSchema();
        $jsst_domain = self::domainOf($jsst_emailaddress);
        if ($jsst_domain === '') {
            // Not an address at all; only an exact match could block it.
            $jsst_query = jssupportticket::$_db->prepare(
                "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist` WHERE LOWER(email) = %s",
                $jsst_emailaddress
            );
        } else {
            $jsst_query = jssupportticket::$_db->prepare(
                "SELECT COUNT(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_email_banlist`
                    WHERE LOWER(email) IN (%s, %s, %s)",
                $jsst_emailaddress,
                '@' . $jsst_domain,
                $jsst_domain
            );
        }
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return ($jsst_result > 0);
    }

}

?>
