<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * These classes are loaded from the plugin bootstrap with include_once. That
 * normally guarantees one declaration, but it deduplicates by resolved path, so
 * anything that reaches this file by a second spelling of the same path - or any
 * route that runs the bootstrap twice - redeclares the class and takes the whole
 * site down with a fatal. Returning early costs nothing and makes the file safe
 * to include however many times and by whatever route. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTpresence')) {
    return;
}

/**
 * Who else is on this ticket. (Roadmap 4.0-UX-05)
 *
 * Two agents answering the same ticket is a small embarrassment for the team and
 * a confusing one for the customer, who gets two replies saying different
 * things. Nothing in the product currently says anybody else is there.
 *
 * Two signals, because they answer different questions:
 *
 *   - Who is looking at this ticket right now, and whether they are typing. That
 *     is a warning before the work is done.
 *   - Whether a reply landed while this agent was writing. That is a warning
 *     after the fact, and the more important of the two, because it catches the
 *     case where the other person arrived, replied and left inside the gap
 *     between two heartbeats.
 *
 * State lives in a transient per ticket rather than a table: it is worthless
 * thirty seconds after it is written, nobody reports on it, and it should
 * disappear on its own if the site goes quiet. An entry that stops being
 * refreshed simply expires.
 */
class JSSTpresence {

    /** How long an entry counts as current, in seconds. */
    const TTL = 75;

    /** How long the whole record lives if nobody refreshes it. */
    const TRANSIENT_TTL = 300;

    /** Ceiling on how many agents are tracked on one ticket. */
    const MAX_TRACKED = 20;

    public static function registerHooks() {
        add_action('wp_ajax_jsst_presence', array(__CLASS__, 'ajaxHeartbeat'));
    }

    private static function key($jsst_ticketid) {
        return 'jsst_presence_' . (int) $jsst_ticketid;
    }

    /**
     * Everyone recorded on a ticket, with anything stale already dropped.
     *
     * Pruning on read rather than on a schedule: the only moment the answer
     * matters is when somebody asks for it, and a transient that nobody reads
     * expires on its own.
     */
    public static function active($jsst_ticketid, $jsst_excludeuser = 0) {
        $jsst_all = get_transient(self::key($jsst_ticketid));
        if (!is_array($jsst_all)) {
            return array();
        }
        $jsst_cutoff = time() - self::TTL;
        $jsst_out = array();
        foreach ($jsst_all as $jsst_uid => $jsst_entry) {
            if (!is_array($jsst_entry) || empty($jsst_entry['seen']) || $jsst_entry['seen'] < $jsst_cutoff) {
                continue;
            }
            if ((int) $jsst_uid === (int) $jsst_excludeuser) {
                continue;
            }
            $jsst_out[(int) $jsst_uid] = $jsst_entry;
        }
        return $jsst_out;
    }

    /**
     * Record that this user is here, and say who else is.
     *
     * @param string $jsst_state 'viewing' or 'replying'
     */
    public static function touch($jsst_ticketid, $jsst_userid, $jsst_state = 'viewing') {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_userid = (int) $jsst_userid;
        if ($jsst_ticketid <= 0 || $jsst_userid <= 0) {
            return array();
        }
        $jsst_all = get_transient(self::key($jsst_ticketid));
        if (!is_array($jsst_all)) {
            $jsst_all = array();
        }
        $jsst_cutoff = time() - self::TTL;
        foreach ($jsst_all as $jsst_uid => $jsst_entry) {
            if (!is_array($jsst_entry) || empty($jsst_entry['seen']) || $jsst_entry['seen'] < $jsst_cutoff) {
                unset($jsst_all[$jsst_uid]);
            }
        }
        $jsst_user = get_userdata($jsst_userid);
        $jsst_all[$jsst_userid] = array(
            'name'  => $jsst_user ? $jsst_user->display_name : esc_html(__('Someone', 'js-support-ticket')),
            'state' => ($jsst_state === 'replying') ? 'replying' : 'viewing',
            'seen'  => time(),
        );
        // A ticket with more than a roomful of agents on it is a runaway, not a
        // collision. Keep the most recent and stop.
        if (count($jsst_all) > self::MAX_TRACKED) {
            uasort($jsst_all, function ($jsst_a, $jsst_b) {
                return $jsst_b['seen'] - $jsst_a['seen'];
            });
            $jsst_all = array_slice($jsst_all, 0, self::MAX_TRACKED, true);
        }
        set_transient(self::key($jsst_ticketid), $jsst_all, self::TRANSIENT_TTL);
        return self::active($jsst_ticketid, $jsst_userid);
    }

    /**
     * Drop this user from a ticket, when they navigate away.
     */
    public static function leave($jsst_ticketid, $jsst_userid) {
        $jsst_all = get_transient(self::key($jsst_ticketid));
        if (!is_array($jsst_all)) {
            return;
        }
        unset($jsst_all[(int) $jsst_userid]);
        if (empty($jsst_all)) {
            delete_transient(self::key($jsst_ticketid));
            return;
        }
        set_transient(self::key($jsst_ticketid), $jsst_all, self::TRANSIENT_TTL);
    }

    /**
     * The id of the most recent reply on a ticket.
     *
     * The composer records this when the page loads; if it has moved by the time
     * the agent is still typing, somebody else has answered.
     */
    public static function latestReplyId($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid)) {
            return 0;
        }
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT MAX(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_replies` WHERE ticketid = %d",
            (int) $jsst_ticketid
        ));
    }

    /**
     * May this user be told who else is on a ticket?
     *
     * Public because the ticket screen asks the same question before it prints
     * the bar at all: a customer or a subscriber who happens to reach this
     * template should not be given a heartbeat that can only answer 403.
     */
    public static function mayWatch() {
        if (current_user_can('manage_options')) {
            return true;
        }
        return (in_array('agent', jssupportticket::$_active_addons)
                && JSSTincluder::getJSModel('agent')->isUserStaff());
    }

    /**
     * The heartbeat.
     *
     * Its own action rather than the shared ajax dispatcher, which pushes every
     * response through wp_kses - fine for HTML, destructive for JSON.
     */
    public static function ajaxHeartbeat() {
        check_ajax_referer('jsst-presence', '_wpnonce');
        if (!self::mayWatch()) {
            wp_send_json_error(array('message' => esc_html(__('Not allowed', 'js-support-ticket'))), 403);
        }
        $jsst_ticketid = absint(JSSTrequest::getVar('ticketid', 'post', 0));
        if ($jsst_ticketid <= 0) {
            wp_send_json_error(array('message' => esc_html(__('No ticket', 'js-support-ticket'))), 400);
        }
        $jsst_state = JSSTrequest::getVar('state', 'post', 'viewing');
        if (JSSTrequest::getVar('leaving', 'post', '') === '1') {
            self::leave($jsst_ticketid, get_current_user_id());
            wp_send_json_success(array('others' => array()));
        }
        $jsst_others = self::touch($jsst_ticketid, get_current_user_id(), $jsst_state);

        $jsst_list = array();
        foreach ($jsst_others as $jsst_entry) {
            $jsst_list[] = array(
                'name'  => $jsst_entry['name'],
                'state' => $jsst_entry['state'],
            );
        }
        wp_send_json_success(array(
            'others'      => $jsst_list,
            'latestReply' => self::latestReplyId($jsst_ticketid),
        ));
    }

}
