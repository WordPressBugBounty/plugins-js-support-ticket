<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Ticket limits per customer — part of the free core. (Roadmap 4.0-CORE-11)
 *
 * Two capacity checks that run before a ticket is created: a lifetime cap and a
 * cap on how many the customer may have open at once. They sit alongside the
 * per-visitor submission rate limits from 4.0-SEC-01, which bound how *fast*
 * someone can submit rather than how many they may hold.
 *
 * Loaded only when the stand-alone Max Ticket add-on is inactive:
 * JSSTincluder::getPluginPath() resolves the 'maxticket' module to the add-on
 * directory while that add-on is active. The two settings keep their names, so an
 * existing site's numbers carry over. (Roadmap 4.0-CORE-19)
 *
 * Plan, product, customer and company quotas stay Pro.
 */
class JSSTmaxticketModel {

    /**
     * Statuses that do not count towards the open-ticket limit.
     *
     * 5 is Closed and 6 is "Close Due To Merge". The add-on excluded only 5, so
     * merging a customer's duplicates counted against their open allowance and
     * could lock them out of filing anything new. (Roadmap 4.0-CORE-04)
     */
    private static function closedStatuses() {
        return array(5, 6);
    }

    /**
     * The configured lifetime cap, or 0 when it is not set to a usable number.
     */
    private static function limit($jsst_name) {
        if (!isset(jssupportticket::$_config[$jsst_name])) {
            return 0;
        }
        $jsst_value = jssupportticket::$_config[$jsst_name];
        return is_numeric($jsst_value) ? (int) $jsst_value : 0;
    }

    /**
     * Count this customer's tickets, optionally only the open ones.
     *
     * A logged-in customer is counted by account, a guest by the e-mail address
     * on the form.
     */
    private function countFor($jsst_emailaddress, $jsst_openonly = false) {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_tickets';
        $jsst_closed = self::closedStatuses();
        $jsst_placeholders = implode(',', array_fill(0, count($jsst_closed), '%d'));
        $jsst_openclause = $jsst_openonly ? " AND status NOT IN (" . $jsst_placeholders . ")" : '';

        if (!JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_args = array(JSSTincluder::getObjectClass('user')->uid());
            $jsst_query = "SELECT COUNT(id) FROM `" . $jsst_table . "` WHERE uid = %d" . $jsst_openclause;
        } else {
            $jsst_args = array($jsst_emailaddress);
            $jsst_query = "SELECT COUNT(id) FROM `" . $jsst_table . "` WHERE email = %s" . $jsst_openclause;
        }
        if ($jsst_openonly) {
            $jsst_args = array_merge($jsst_args, $jsst_closed);
        }
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_query, $jsst_args));
    }

    /**
     * Has this customer reached the lifetime cap?
     */
    function checkMaxTickets($jsst_emailaddress) {
        $jsst_limit = self::limit('maximum_tickets');
        if ($jsst_limit <= 0) {
            return true; // not configured, so nothing to enforce
        }

        // A long-standing extension point: a filter may replace the counting
        // query outright. Kept because third-party code relies on it, but the
        // replacement is only honoured when it is a string, exactly as before.
        $jsst_counts = null;
        $jsst_newquery = apply_filters('js_support_ticket_max_ticket_query_overwrite', get_current_user_id());
        if ($jsst_newquery && !is_numeric($jsst_newquery)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- supplied by a third-party filter, as in the add-on.
            $jsst_counts = (int) jssupportticket::$_db->get_var($jsst_newquery);
        }
        if ($jsst_counts === null) {
            $jsst_counts = $this->countFor($jsst_emailaddress, false);
        }

        if ($jsst_counts >= $jsst_limit) {
            JSSTmessage::setMessage(
                sprintf(
                    /* translators: %d: the configured maximum */
                    esc_html(__('You have reached the limit of %d tickets on this account.', 'js-support-ticket')),
                    $jsst_limit
                ),
                'error'
            );
            return false;
        }
        return true;
    }

    /**
     * Has this customer reached the open-ticket cap?
     */
    function checkMaxOpenTickets($jsst_emailaddress) {
        $jsst_limit = self::limit('maximum_open_tickets');
        if ($jsst_limit <= 0) {
            return true;
        }
        $jsst_counts = $this->countFor($jsst_emailaddress, true);
        if ($jsst_counts >= $jsst_limit) {
            JSSTmessage::setMessage(
                sprintf(
                    /* translators: %d: the configured maximum */
                    esc_html(__('You already have %d tickets open. Please wait until one is closed before opening another.', 'js-support-ticket')),
                    $jsst_limit
                ),
                'error'
            );
            return false;
        }
        return true;
    }

}
