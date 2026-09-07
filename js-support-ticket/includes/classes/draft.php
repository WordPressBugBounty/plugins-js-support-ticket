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
if (class_exists('JSSTdraft')) {
    return;
}

/**
 * Reply drafts. (Roadmap 4.0-UX-04)
 *
 * Losing a long reply is the failure people remember about a help desk, and it
 * happens for dull reasons: a refresh, a session timeout, a laptop lid. Two
 * layers, because they fail differently:
 *
 *   - The browser keeps a copy as you type. Instant, costs nothing, and survives
 *     a crash or a stray back button.
 *   - The server keeps one too, every so often. Slower and less frequent, but it
 *     is the only one that survives the browser itself — a different machine, a
 *     cleared cache, a colleague picking the ticket up.
 *
 * Recovery is always offered, never automatic. Silently pasting old text into a
 * composer is how somebody sends a half-finished reply they had already
 * abandoned, so a restored draft is something an agent chooses.
 *
 * Drafts are per agent as well as per ticket: two people drafting on the same
 * ticket must never see each other's words appear in their own box.
 */
class JSSTdraft {

    /** Anything longer than this is not a reply, it is a paste accident. */
    const MAX_BYTES = 120000;

    /** A draft nobody came back to stops being useful. */
    const MAX_AGE_DAYS = 30;

    public static function registerHooks() {
        add_action('wp_ajax_jsst_save_draft', array(__CLASS__, 'ajaxSave'));
        add_action('wp_ajax_jsst_discard_draft', array(__CLASS__, 'ajaxDiscard'));
    }

    /**
     * Where one agent's draft for one ticket in one mode lives.
     *
     * Stored in user meta rather than a table of its own: it is per-user
     * scratch data with no reporting value, WordPress already cleans it up when
     * the user is deleted, and it needs no migration.
     */
    public static function metaKey($jsst_ticketid, $jsst_mode) {
        $jsst_mode = ($jsst_mode === 'internal') ? 'internal' : 'public';
        return 'jsst_draft_' . (int) $jsst_ticketid . '_' . $jsst_mode;
    }

    /**
     * Store a draft. An empty body discards instead, so clearing the composer
     * does not leave a stale draft waiting to be offered back.
     */
    public static function save($jsst_userid, $jsst_ticketid, $jsst_mode, $jsst_body) {
        $jsst_userid = (int) $jsst_userid;
        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_userid <= 0 || $jsst_ticketid <= 0) {
            return false;
        }
        $jsst_body = (string) $jsst_body;
        if (strlen($jsst_body) > self::MAX_BYTES) {
            $jsst_body = substr($jsst_body, 0, self::MAX_BYTES);
        }
        if (trim(wp_strip_all_tags($jsst_body)) === '') {
            return self::discard($jsst_userid, $jsst_ticketid, $jsst_mode);
        }
        update_user_meta($jsst_userid, self::metaKey($jsst_ticketid, $jsst_mode), array(
            'body'  => $jsst_body,
            'saved' => time(),
        ));
        return true;
    }

    /**
     * The stored draft, or an empty array when there is none or it has expired.
     */
    public static function get($jsst_userid, $jsst_ticketid, $jsst_mode) {
        $jsst_userid = (int) $jsst_userid;
        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_userid <= 0 || $jsst_ticketid <= 0) {
            return array();
        }
        $jsst_draft = get_user_meta($jsst_userid, self::metaKey($jsst_ticketid, $jsst_mode), true);
        if (!is_array($jsst_draft) || empty($jsst_draft['body'])) {
            return array();
        }
        $jsst_saved = isset($jsst_draft['saved']) ? (int) $jsst_draft['saved'] : 0;
        if ($jsst_saved > 0 && $jsst_saved < (time() - (self::MAX_AGE_DAYS * DAY_IN_SECONDS))) {
            // Old enough that offering it back would be confusing rather than
            // helpful. Clear it on the way past.
            self::discard($jsst_userid, $jsst_ticketid, $jsst_mode);
            return array();
        }
        return $jsst_draft;
    }

    public static function discard($jsst_userid, $jsst_ticketid, $jsst_mode) {
        delete_user_meta((int) $jsst_userid, self::metaKey($jsst_ticketid, $jsst_mode));
        return true;
    }

    /**
     * Drop both drafts for a ticket once something has actually been posted.
     *
     * Called from the reply and note paths. Without it an agent posts a reply
     * and is then offered the same text back as a "recovered draft", which
     * reads like the reply failed to send.
     */
    public static function discardForTicket($jsst_userid, $jsst_ticketid) {
        self::discard($jsst_userid, $jsst_ticketid, 'public');
        self::discard($jsst_userid, $jsst_ticketid, 'internal');
        return true;
    }

    /** May this user draft on this ticket at all? */
    private static function mayDraft() {
        if (current_user_can('manage_options')) {
            return true;
        }
        return (in_array('agent', jssupportticket::$_active_addons)
                && JSSTincluder::getJSModel('agent')->isUserStaff());
    }

    /**
     * Autosave endpoint.
     *
     * Its own action rather than the shared ajax dispatcher, which pushes every
     * response through wp_kses — fine for HTML fragments, destructive for JSON.
     */
    public static function ajaxSave() {
        check_ajax_referer('jsst-draft', '_wpnonce');
        if (!self::mayDraft()) {
            wp_send_json_error(array('message' => esc_html(__('Not allowed', 'js-support-ticket'))), 403);
        }
        $jsst_ticketid = absint(JSSTrequest::getVar('ticketid', 'post', 0));
        $jsst_mode = JSSTrequest::getVar('mode', 'post', 'public');
        // Not sanitised as text: this is editor HTML and wp_kses_post is what
        // decides which of it may live, exactly as a posted reply would be.
        $jsst_body = isset($_POST['body']) ? wp_kses_post(wp_unslash($_POST['body'])) : '';
        self::save(get_current_user_id(), $jsst_ticketid, $jsst_mode, $jsst_body);
        wp_send_json_success(array('saved' => time()));
    }

    public static function ajaxDiscard() {
        check_ajax_referer('jsst-draft', '_wpnonce');
        if (!self::mayDraft()) {
            wp_send_json_error(array('message' => esc_html(__('Not allowed', 'js-support-ticket'))), 403);
        }
        $jsst_ticketid = absint(JSSTrequest::getVar('ticketid', 'post', 0));
        $jsst_mode = JSSTrequest::getVar('mode', 'post', 'public');
        self::discard(get_current_user_id(), $jsst_ticketid, $jsst_mode);
        wp_send_json_success();
    }

}
