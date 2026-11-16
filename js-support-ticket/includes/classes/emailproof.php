<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

if (class_exists('JSSTemailproof')) {
    return;
}

/**
 * Whether an account has proven that its e-mail address is its own. (5.0.6)
 *
 * Several things the help desk gives an account follow from its e-mail
 * address rather than from the account itself: the tickets that address sent
 * as a guest before the account existed, and a company supervisor's right to
 * read colleagues' tickets. An address is something anybody can type - into a
 * sign-up form, or into a shop's "account details" page that changes it
 * without asking - so none of those may follow from the address alone. They
 * follow from an address the account has *proven*:
 *
 *  - an administrator created the account (they vouch for the address);
 *  - the person set their password from WordPress's e-mailed reset link;
 *  - the person opened the confirmation link this class e-mails; or
 *  - the site says so through the `jsst_email_verified` filter (for sign-up
 *    flows that verify addresses themselves).
 *
 * What is stored is the address that was proven, not a yes/no: change the
 * address and the account is unproven again until the new one is confirmed.
 *
 * Reported by Patchstack against 5.0.5: the plugin's own sign-up form set the
 * visitor's chosen password and logged them in at once, so "registered" never
 * meant "owns the mailbox", yet the account inherited every guest ticket sent
 * from the address it typed.
 */
class JSSTemailproof {

    const META = 'jsst_verified_email';
    const LINK_TTL = 172800;      // two days
    const RESEND_WAIT = 300;      // one link per five minutes per account

    public static function registerHooks() {
        // An account an administrator creates carries the address they gave it.
        add_action('edit_user_created_user', array(__CLASS__, 'markVerified'));
        // Setting a password from the e-mailed reset link proves the mailbox.
        add_action('after_password_reset', array(__CLASS__, 'onPasswordReset'), 10, 1);
        // Sites whose own sign-up verifies addresses can say so. The old filter
        // name is still honoured.
        add_action('user_register', array(__CLASS__, 'onRegister'), 20);
        add_action('init', array(__CLASS__, 'handleRequest'), 5);
    }

    /** Has this account proven the address it has now? */
    public static function isVerified($jsst_wpuid) {
        $jsst_wpuid = (int) $jsst_wpuid;
        if ($jsst_wpuid <= 0) {
            return false;
        }
        $jsst_user = get_userdata($jsst_wpuid);
        if (!$jsst_user || !is_email($jsst_user->user_email)) {
            return false;
        }
        $jsst_proven = strtolower(trim((string) get_user_meta($jsst_wpuid, self::META, true)));
        $jsst_verified = ($jsst_proven !== '' && $jsst_proven === strtolower(trim($jsst_user->user_email)));
        return (bool) apply_filters('jsst_email_verified', $jsst_verified, $jsst_wpuid, $jsst_user->user_email);
    }

    /** Is there an account with this address that has proven it? */
    public static function isVerifiedEmail($jsst_email) {
        $jsst_email = trim((string) $jsst_email);
        if (!is_email($jsst_email)) {
            return false;
        }
        $jsst_user = get_user_by('email', $jsst_email);
        return $jsst_user ? self::isVerified($jsst_user->ID) : false;
    }

    /**
     * The help desk user (js_ticket_users.id) whose account has proven this
     * address, or 0. For anything that hands an account what arrives from an
     * address - a piped e-mail, say - so that typing somebody's address at
     * sign-up does not make their mail yours. Unproven: treat the sender as
     * a guest; the tickets join the account once the address is proven.
     */
    public static function provenUid($jsst_email) {
        $jsst_email = trim((string) $jsst_email);
        if (!is_email($jsst_email)) {
            return 0;
        }
        $jsst_user = get_user_by('email', $jsst_email);
        if (!$jsst_user || !self::isVerified($jsst_user->ID)) {
            return 0;
        }
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` WHERE wpuid = %d ORDER BY id LIMIT 1",
            (int) $jsst_user->ID));
    }

    /** Record the account's current address as proven, and give it its guest tickets. */
    public static function markVerified($jsst_wpuid) {
        $jsst_wpuid = (int) $jsst_wpuid;
        $jsst_user = get_userdata($jsst_wpuid);
        if (!$jsst_user || !is_email($jsst_user->user_email)) {
            return false;
        }
        update_user_meta($jsst_wpuid, self::META, strtolower(trim($jsst_user->user_email)));
        // The pre-5.0.6 marker, set without any proof; never read again.
        delete_user_meta($jsst_wpuid, 'jsst_link_guest_tickets');
        self::linkGuestTickets($jsst_wpuid, $jsst_user->user_email);
        // A newsletter "yes" given at sign-up counts from now - see jsst-hooks.php.
        $jsst_pending = get_user_meta($jsst_wpuid, 'jsst_pending_consent', true);
        if (is_array($jsst_pending) && class_exists('JSSTconsent')) {
            delete_user_meta($jsst_wpuid, 'jsst_pending_consent');
            JSSTconsent::onRegistration($jsst_user->user_email, isset($jsst_pending['name']) ? (string) $jsst_pending['name'] : '', true);
        }
        return true;
    }

    public static function onPasswordReset($jsst_user) {
        if (is_object($jsst_user) && !empty($jsst_user->ID)) {
            self::markVerified($jsst_user->ID);
        }
    }

    public static function onRegister($jsst_wpuid) {
        if (apply_filters('jsst_link_guest_tickets_on_register', false, $jsst_wpuid)) {
            self::markVerified($jsst_wpuid);
        }
    }

    /**
     * Attach the tickets this address sent as a guest to the account.
     *
     * Only ever called with an address the account has proven. "My Tickets"
     * lists by account, so without this a customer who wrote in as a guest
     * and then registered saw an empty list.
     */
    private static function linkGuestTickets($jsst_wpuid, $jsst_email) {
        if (!class_exists('JSSTcapability')) {
            return;
        }
        $jsst_uid = JSSTcapability::pluginUserId($jsst_wpuid);
        if ($jsst_uid <= 0 || !is_email($jsst_email)) {
            return;
        }
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_tickets` SET uid = %d WHERE (uid = 0 OR uid IS NULL) AND email = %s",
            $jsst_uid, $jsst_email
        ));
    }

    /* ------------------------------------------------------------------ *
     * The confirmation link
     * ------------------------------------------------------------------ */

    private static function token($jsst_wpuid, $jsst_email, $jsst_expires) {
        return hash_hmac('sha256', 'jsst-verify-email|' . (int) $jsst_wpuid . '|' . strtolower(trim($jsst_email)) . '|' . (int) $jsst_expires, wp_salt('auth'));
    }

    /**
     * E-mail the account a link that proves its address.
     *
     * @return true|string true when sent, otherwise why not.
     */
    public static function sendLink($jsst_wpuid) {
        $jsst_wpuid = (int) $jsst_wpuid;
        $jsst_user = get_userdata($jsst_wpuid);
        if (!$jsst_user || !is_email($jsst_user->user_email)) {
            return __('This account has no e-mail address to confirm.', 'js-support-ticket');
        }
        if (self::isVerified($jsst_wpuid)) {
            return true;
        }
        $jsst_waitkey = 'jsst_vemail_wait_' . $jsst_wpuid;
        if (get_transient($jsst_waitkey)) {
            return __('A confirmation link was sent a few minutes ago. Please check your inbox, or try again later.', 'js-support-ticket');
        }
        set_transient($jsst_waitkey, 1, self::RESEND_WAIT);

        $jsst_expires = time() + self::LINK_TTL;
        $jsst_link = add_query_arg(array(
            'jsst_verify_email' => 1,
            'u' => $jsst_wpuid,
            'e' => $jsst_expires,
            't' => self::token($jsst_wpuid, $jsst_user->user_email, $jsst_expires),
        ), home_url('/'));
        $jsst_site = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $jsst_subject = sprintf(
            /* translators: %s: site name */
            __('[%s] Confirm your e-mail address', 'js-support-ticket'), $jsst_site);
        $jsst_body = sprintf(
            /* translators: 1: site name, 2: confirmation link */
            __("Please confirm that this e-mail address belongs to your account at %1\$s by opening this link:\n\n%2\$s\n\nOnce it is confirmed, any support tickets you sent from this address before you had an account are added to your account. The link works for two days. If you did not ask for this, you can ignore this message.", 'js-support-ticket'),
            $jsst_site, $jsst_link);
        wp_mail($jsst_user->user_email, $jsst_subject, $jsst_body);
        return true;
    }

    /**
     * The two requests this class answers: opening a confirmation link, and a
     * signed-in customer asking for one.
     */
    public static function handleRequest() {
        if (isset($_GET['jsst_verify_email'], $_GET['u'], $_GET['e'], $_GET['t'])) {
            $jsst_wpuid = absint($_GET['u']);
            $jsst_expires = absint($_GET['e']);
            $jsst_token = sanitize_text_field(wp_unslash($_GET['t']));
            $jsst_user = get_userdata($jsst_wpuid);
            $jsst_ok = $jsst_user && $jsst_expires >= time()
                && hash_equals(self::token($jsst_wpuid, $jsst_user->user_email, $jsst_expires), $jsst_token);
            if ($jsst_ok) {
                self::markVerified($jsst_wpuid);
                JSSTmessage::setMessage(esc_html(__('Thank you - your e-mail address is confirmed.', 'js-support-ticket')), 'updated');
            } else {
                JSSTmessage::setMessage(esc_html(__('That confirmation link is not valid or has expired. Sign in and ask for a new one on My Tickets.', 'js-support-ticket')), 'error');
            }
            wp_safe_redirect(self::myTicketsUrl());
            exit;
        }
        if (isset($_POST['jsst_send_email_proof'], $_POST['_wpnonce']) && is_user_logged_in()
                && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'jsst-send-email-proof')) {
            $jsst_sent = self::sendLink(get_current_user_id());
            if ($jsst_sent === true) {
                JSSTmessage::setMessage(esc_html(__('We have e-mailed you a link to confirm your address.', 'js-support-ticket')), 'updated');
            } else {
                JSSTmessage::setMessage(esc_html($jsst_sent), 'error');
            }
            wp_safe_redirect(self::myTicketsUrl());
            exit;
        }
    }

    private static function myTicketsUrl() {
        return jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'myticket', 'jsstpageid' => jssupportticket::getPageid()));
    }

    /**
     * The notice on My Tickets for a customer whose address is not confirmed:
     * what confirming does, and a button that sends the link. Shown to every
     * unconfirmed customer, not only to those with guest tickets waiting, so
     * the page does not tell anybody whether an address has written in before.
     */
    public static function notice() {
        if (!is_user_logged_in() || self::isVerified(get_current_user_id())) {
            return '';
        }
        $jsst_actor = class_exists('JSSTcapability') ? JSSTcapability::actor() : array();
        if (!empty($jsst_actor['kind']) && $jsst_actor['kind'] !== JSSTcapability::ACTOR_CUSTOMER) {
            return '';
        }
        return '<div class="jsst-email-proof"><form method="post" action="' . esc_url(self::myTicketsUrl()) . '">'
            . '<span>' . esc_html(__('Tickets you sent before you had an account are added once you confirm your e-mail address.', 'js-support-ticket')) . '</span> '
            . '<input type="hidden" name="jsst_send_email_proof" value="1" />'
            . wp_nonce_field('jsst-send-email-proof', '_wpnonce', true, false)
            . '<button type="submit" class="js-ticket-btn">' . esc_html(__('Send confirmation link', 'js-support-ticket')) . '</button>'
            . '</form></div>';
    }
}
