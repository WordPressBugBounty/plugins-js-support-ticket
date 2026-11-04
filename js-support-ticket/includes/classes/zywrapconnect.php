<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * A Zywrap account from the Engine & key tab, with no key to copy. (5.0.4)
 *
 * Getting Zywrap working used to mean leaving wp-admin, signing up, finding the
 * API keys page, making a key and pasting it back - and every one of those steps
 * is a place somebody stops. This is Zywrap's own WordPress connect protocol, the
 * one its plugin (1.4.0) uses, in its two forms:
 *
 *   In wp-admin   The administrator gives an e-mail address and ticks the
 *                 consent box; Zywrap e-mails a 6-digit code, and the code is
 *                 traded for this site's key. An existing account is signed in
 *                 the same way.
 *   In a browser  For Google or GitHub sign-in, and for accounts with
 *                 two-factor sign-in, which an e-mailed code must not get round.
 *                 The browser goes to zywrap.com and comes back to the settings
 *                 screen with a one-time code.
 *
 * Both are PKCE. The verifier is made here, kept here in a transient for this
 * administrator, and only ever sent server to server - so a code seen in a
 * browser history or a proxy log is no use to anybody else. Nothing is sent to
 * Zywrap until an administrator presses the button.
 */
class JSSTzywrapconnect {

    /** What the connected account looked like when it connected. Not autoloaded. */
    const OPT_ACCOUNT = 'jsst_zywrap_account';

    /** The nonce both AJAX steps carry. */
    const NONCE = 'jsst-aiagent-zywrap';

    /** One pending sign-up and one pending browser connect per administrator. */
    const SIGNUP_PREFIX  = 'jsst_zywrap_signup_';
    const CONNECT_PREFIX = 'jsst_zywrap_connect_';
    const PENDING_TTL    = 600;

    const API_URL = 'https://api.zywrap.com';
    const APP_URL = 'https://zywrap.com';

    public static function registerHooks() {
        add_action('wp_ajax_jsst_zywrap_signup_start', array(__CLASS__, 'ajaxSignupStart'));
        add_action('wp_ajax_jsst_zywrap_signup_verify', array(__CLASS__, 'ajaxSignupVerify'));
        // Before anything is drawn, so the code can be taken out of the address bar.
        add_action('admin_init', array(__CLASS__, 'handleReturn'));
    }

    /* ------------------------------------------------------------------ *
     * What the screen shows
     * ------------------------------------------------------------------ */

    /** The connected account (email, key_name, credits, connected_at), or null. */
    public static function account() {
        $jsst_account = get_option(self::OPT_ACCOUNT);
        return is_array($jsst_account) ? $jsst_account : null;
    }

    /**
     * The models Zywrap can run a request on, code => name.
     *
     * From the catalogue the Zywrap module syncs, so the list is Zywrap's own
     * rather than one written here that goes stale.
     */
    public static function models() {
        $jsst_models = array();
        $jsst_zywrap = JSSTincluder::getJSModel('zywrap');
        if ($jsst_zywrap && method_exists($jsst_zywrap, 'getDynamicModels')) {
            foreach ((array) $jsst_zywrap->getDynamicModels() as $jsst_row) {
                if (isset($jsst_row->code) && (string) $jsst_row->code !== '') {
                    $jsst_models[(string) $jsst_row->code] = (string) $jsst_row->name;
                }
            }
        }
        return $jsst_models;
    }

    /**
     * The site as Zywrap knows it. site_url(), not home_url(): wp-admin lives
     * under site_url(), and Zywrap only hands codes back to that site's wp-admin.
     */
    public static function siteUrl() {
        return untrailingslashit(site_url());
    }

    /** Where zywrap.com sends the browser back to. No #anchor: Zywrap adds to the query. */
    public static function returnUrl() {
        return admin_url('admin.php?page=aiagent&jstlay=aiagent_settings');
    }

    private static function settingsUrl() {
        return self::returnUrl() . '#AIEngine';
    }

    /* ------------------------------------------------------------------ *
     * Connecting in a browser
     * ------------------------------------------------------------------ */

    /**
     * The zywrap.com address that starts a browser connect.
     *
     * The state and the verifier stay here, for this administrator and ten
     * minutes; only the S256 challenge goes in the link. The caller has already
     * checked the nonce and that this is an administrator.
     */
    public static function browserUrl() {
        $jsst_verifier = self::base64url(random_bytes(32));
        $jsst_state    = self::base64url(random_bytes(24));
        set_transient(self::CONNECT_PREFIX . get_current_user_id(),
            array('state' => $jsst_state, 'verifier' => $jsst_verifier), self::PENDING_TTL);

        return add_query_arg(array_map('rawurlencode', array(
            'site'             => self::siteUrl(),
            'site_name'        => wp_strip_all_tags(wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)),
            'return'           => self::returnUrl(),
            'state'            => $jsst_state,
            'challenge'        => self::challenge($jsst_verifier),
            'challenge_method' => 'S256',
        )), self::APP_URL . '/connect/wordpress');
    }

    /**
     * The browser coming back from zywrap.com, with a code to redeem or with
     * the person having cancelled. Runs on admin_init.
     */
    public static function handleReturn() {
        if (wp_doing_ajax() || JSSTrequest::getVar('page', 'get', '') !== 'aiagent') {
            return;
        }
        $jsst_code  = (string) JSSTrequest::getVar('zywrap_code', 'get', '');
        $jsst_error = (string) JSSTrequest::getVar('zywrap_error', 'get', '');
        if ($jsst_code === '' && $jsst_error === '') {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }

        /* Single use whatever happens next: a refresh, or the same address from
           the history, must not get a second try. */
        $jsst_pendingkey = self::CONNECT_PREFIX . get_current_user_id();
        $jsst_pending = get_transient($jsst_pendingkey);
        delete_transient($jsst_pendingkey);

        $jsst_state = (string) JSSTrequest::getVar('state', 'get', '');
        if (!is_array($jsst_pending) || empty($jsst_pending['state']) || empty($jsst_pending['verifier'])
                || $jsst_state === '' || !hash_equals((string) $jsst_pending['state'], $jsst_state)) {
            self::finish('expired');
        }
        if ($jsst_error !== '') {
            self::finish('cancelled');
        }
        // The code is a JWT: letters, digits, '-', '_' and '.'. Anything else is not Zywrap's.
        if (!preg_match('/^[A-Za-z0-9._-]{20,4096}$/', $jsst_code)) {
            self::finish('invalid');
        }

        $jsst_result = self::post('/v1/connect/wordpress/exchange', array(
            'code'         => $jsst_code,
            'codeVerifier' => $jsst_pending['verifier'],
            'siteUrl'      => self::siteUrl(),
        ));
        if (is_wp_error($jsst_result)) {
            self::log('network', $jsst_result->get_error_message());
            self::finish('network');
        }
        list($jsst_status, $jsst_json) = $jsst_result;
        if ($jsst_status !== 200 || empty($jsst_json['apiKey']) || !is_string($jsst_json['apiKey'])) {
            $jsst_why = !empty($jsst_json['code']) ? (string) $jsst_json['code'] : 'server';
            self::log($jsst_why, 'HTTP ' . $jsst_status);
            self::finish($jsst_why, $jsst_json);
        }

        $jsst_email = self::save($jsst_json);
        JSSTmessage::setMessage(esc_html(self::connectedMessage($jsst_email)), 'updated');
        wp_safe_redirect(self::settingsUrl());
        exit;
    }

    /**
     * Back to a clean settings address, so the code never stays in the address
     * bar or in the history entry for the page somebody ends up on.
     */
    private static function finish($jsst_code, $jsst_json = array()) {
        JSSTmessage::setMessage(esc_html(self::messageFor($jsst_code, $jsst_json)), 'error');
        wp_safe_redirect(self::settingsUrl());
        exit;
    }

    /* ------------------------------------------------------------------ *
     * Signing up in wp-admin
     * ------------------------------------------------------------------ */

    /**
     * Step 1: Zywrap e-mails a 6-digit code.
     *
     * Sends the e-mail address, the optional name and this site's address -
     * and only once the administrator has ticked the consent box.
     */
    public static function ajaxSignupStart() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce', 'post', ''), self::NONCE)) {
            wp_send_json_error(array('code' => 'forbidden', 'message' => __('Security check Failed', 'js-support-ticket')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('code' => 'forbidden', 'message' => __('You are not allowed', 'js-support-ticket')), 403);
        }

        $jsst_email  = sanitize_email((string) JSSTrequest::getVar('email', 'post', ''));
        $jsst_name   = sanitize_text_field((string) JSSTrequest::getVar('name', 'post', ''));
        $jsst_accept = ((string) JSSTrequest::getVar('accept', 'post', '') === '1');

        if (!is_email($jsst_email)) {
            self::fail('invalid_email');
        }
        if (!$jsst_accept) {
            self::fail('terms_required');
        }

        $jsst_verifier = self::base64url(random_bytes(32));
        $jsst_result = self::post('/v1/connect/wordpress/signup/start', array(
            'email'               => $jsst_email,
            'name'                => $jsst_name,
            'siteUrl'             => self::siteUrl(),
            'codeChallenge'       => self::challenge($jsst_verifier),
            'codeChallengeMethod' => 'S256',
            'acceptTerms'         => true,
        ));
        if (is_wp_error($jsst_result)) {
            self::log('network', $jsst_result->get_error_message());
            self::fail('network');
        }
        list($jsst_status, $jsst_json) = $jsst_result;
        if ($jsst_status !== 200 || empty($jsst_json['flowId']) || !is_string($jsst_json['flowId'])) {
            $jsst_why = !empty($jsst_json['code']) ? (string) $jsst_json['code'] : 'server';
            self::log($jsst_why, 'HTTP ' . $jsst_status);
            self::fail($jsst_why, $jsst_json);
        }

        // A second "Send a new code" replaces the first: only the latest code counts.
        set_transient(self::SIGNUP_PREFIX . get_current_user_id(), array(
            'flow_id'  => sanitize_text_field($jsst_json['flowId']),
            'verifier' => $jsst_verifier,
        ), self::PENDING_TTL);

        wp_send_json_success(array(
            'email'           => $jsst_email,
            'existingAccount' => !empty($jsst_json['existingAccount']),
        ));
    }

    /**
     * Step 2: the code from the e-mail, traded for this site's key.
     *
     * A wrong code keeps the sign-up so the next try can be made - Zywrap
     * counts the tries. Anything that ends the code on Zywrap's side ends it
     * here too.
     */
    public static function ajaxSignupVerify() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce', 'post', ''), self::NONCE)) {
            wp_send_json_error(array('code' => 'forbidden', 'message' => __('Security check Failed', 'js-support-ticket')), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('code' => 'forbidden', 'message' => __('You are not allowed', 'js-support-ticket')), 403);
        }

        $jsst_pendingkey = self::SIGNUP_PREFIX . get_current_user_id();
        $jsst_pending = get_transient($jsst_pendingkey);
        if (!is_array($jsst_pending) || empty($jsst_pending['flow_id']) || empty($jsst_pending['verifier'])) {
            self::fail('expired_code');
        }
        $jsst_code = preg_replace('/\D+/', '', (string) JSSTrequest::getVar('code', 'post', ''));
        if (strlen($jsst_code) !== 6) {
            self::fail('invalid_code');
        }

        $jsst_result = self::post('/v1/connect/wordpress/signup/verify', array(
            'flowId'       => $jsst_pending['flow_id'],
            'code'         => $jsst_code,
            'codeVerifier' => $jsst_pending['verifier'],
            'siteUrl'      => self::siteUrl(),
        ));
        if (is_wp_error($jsst_result)) {
            self::log('network', $jsst_result->get_error_message());
            self::fail('network');
        }
        list($jsst_status, $jsst_json) = $jsst_result;
        if ($jsst_status !== 200 || empty($jsst_json['apiKey']) || !is_string($jsst_json['apiKey'])) {
            $jsst_why = !empty($jsst_json['code']) ? (string) $jsst_json['code'] : 'server';
            if ($jsst_why !== 'wrong_code') {
                delete_transient($jsst_pendingkey);
            }
            self::log($jsst_why, 'HTTP ' . $jsst_status);
            self::fail($jsst_why, $jsst_json);
        }

        delete_transient($jsst_pendingkey);
        $jsst_email = self::save($jsst_json);
        // Shown on the reload that follows.
        JSSTmessage::setMessage(esc_html(self::connectedMessage($jsst_email)), 'updated');
        wp_send_json_success(array('createdAccount' => !empty($jsst_json['createdAccount'])));
    }

    /**
     * Refuse one AJAX step, in words the administrator can act on.
     *
     * Zywrap's own sentence is used when it sent one: it is written for the
     * person and usually says how many tries are left already, so the count is
     * only added when it does not.
     */
    private static function fail($jsst_code, $jsst_json = array()) {
        $jsst_left = (is_array($jsst_json) && isset($jsst_json['attemptsLeft']) && is_numeric($jsst_json['attemptsLeft']))
            ? (int) $jsst_json['attemptsLeft'] : null;
        $jsst_message = self::messageFor($jsst_code, $jsst_json);
        if ($jsst_left !== null && strpos($jsst_message, (string) $jsst_left) === false) {
            $jsst_message .= ' ' . sprintf(
                /* translators: %d: how many more times a code may be tried */
                _n('%d try left.', '%d tries left.', $jsst_left, 'js-support-ticket'), $jsst_left);
        }
        wp_send_json_error(array(
            'code'         => sanitize_key($jsst_code),
            'message'      => $jsst_message,
            'attemptsLeft' => $jsst_left,
        ));
    }

    /* ------------------------------------------------------------------ *
     * Keeping it, and letting it go
     * ------------------------------------------------------------------ */

    /**
     * Keep what /signup/verify or /exchange sent back.
     *
     * The key goes through the engine registry, so the AI Agent, the Zywrap
     * module and every other reader find the same one. The summary is only for
     * saying who is connected; nothing decides anything on it.
     *
     * @return string the account's e-mail address, for the message
     */
    private static function save($jsst_json) {
        JSSTaiengine::saveKey('zywrap', sanitize_text_field($jsst_json['apiKey']));

        $jsst_email = isset($jsst_json['account']['email']) ? sanitize_email((string) $jsst_json['account']['email']) : '';
        update_option(self::OPT_ACCOUNT, array(
            // Zywrap's account ID: what a licence's monthly credits are linked to.
            'id'           => (isset($jsst_json['account']['id']) && is_string($jsst_json['account']['id'])) ? sanitize_text_field($jsst_json['account']['id']) : '',
            'email'        => $jsst_email,
            'key_name'     => isset($jsst_json['keyName']) ? sanitize_text_field((string) $jsst_json['keyName']) : '',
            'credits'      => (isset($jsst_json['credits']) && is_numeric($jsst_json['credits'])) ? (int) $jsst_json['credits'] : null,
            'connected_at' => time(),
        ), false);

        /* Zywrap becomes the engine only where nobody has chosen one - here, or
           on the Copilot screen of a site upgraded from 5.5. A choice somebody
           made is never changed by connecting an account. */
        if ((string) get_option(JSSTaiengine::OPT_ENGINE, '') === ''
                && (string) get_option('jsst_copilot_provider', '') === '') {
            update_option(JSSTaiengine::OPT_ENGINE, 'zywrap', false);
        }

        if (class_exists('JSSTaipolicy')) {
            JSSTaipolicy::record('zywrap-connect', $jsst_email);
        }
        // Ask again now, so a paid licence's "Claim" shows at once.
        if (class_exists('JSSTaicredits')) {
            JSSTaicredits::link();
        }
        return $jsst_email;
    }

    /**
     * Forget the key and the account on this site.
     *
     * The key stays valid on Zywrap until it is revoked there; the screen says
     * so. Every row the key has ever been kept in is cleared, because apiKey()
     * falls back to the older ones and a site that still found a key there
     * would still be connected after saying it was not.
     */
    public static function disconnect() {
        $jsst_account = self::account();
        JSSTaiengine::forgetKey('zywrap');
        delete_option(self::OPT_ACCOUNT);
        if (class_exists('JSSTaicredits')) {
            JSSTaicredits::forget();
        }
        if (!empty(jssupportticket::$_config['zywrap_api_key'])) {
            jssupportticket::$_db->update(
                jssupportticket::$_db->prefix . 'js_ticket_config',
                array('configvalue' => ''),
                array('configname' => 'zywrap_api_key'));
            jssupportticket::$_config['zywrap_api_key'] = '';
        }
        if (class_exists('JSSTaipolicy')) {
            JSSTaipolicy::record('zywrap-disconnect', ($jsst_account && !empty($jsst_account['email'])) ? (string) $jsst_account['email'] : '');
        }
    }

    /* ------------------------------------------------------------------ *
     * Plumbing
     * ------------------------------------------------------------------ */

    /** POST to a Zywrap connect endpoint: array(status, decoded body) or a WP_Error. */
    private static function post($jsst_path, $jsst_body) {
        /* No redirects: these carry the verifier, and the API never answers
           with one. */
        $jsst_response = wp_remote_post(self::API_URL . $jsst_path, array(
            'timeout'     => 20,
            'sslverify'   => true,
            'redirection' => 0,
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => wp_json_encode($jsst_body),
        ));
        if (is_wp_error($jsst_response)) {
            return $jsst_response;
        }
        $jsst_json = json_decode(wp_remote_retrieve_body($jsst_response), true);
        return array((int) wp_remote_retrieve_response_code($jsst_response), is_array($jsst_json) ? $jsst_json : array());
    }

    /** Under System Errors, without the address, the code or anything Zywrap sent back. */
    private static function log($jsst_code, $jsst_detail = '') {
        JSSTincluder::getJSModel('systemerror')->addSystemError(
            'Zywrap connect failed: ' . sanitize_key($jsst_code) . ($jsst_detail !== '' ? ' (' . $jsst_detail . ')' : ''));
    }

    private static function base64url($jsst_bytes) {
        return rtrim(strtr(base64_encode($jsst_bytes), '+/', '-_'), '=');
    }

    private static function challenge($jsst_verifier) {
        return self::base64url(hash('sha256', $jsst_verifier, true));
    }

    private static function connectedMessage($jsst_email) {
        return ($jsst_email !== '')
            /* translators: %s: the e-mail address of the Zywrap account */
            ? sprintf(__('Connected to Zywrap as %s.', 'js-support-ticket'), $jsst_email)
            : __('Connected to Zywrap.', 'js-support-ticket');
    }

    /** Zywrap's own message when it gave one, otherwise ours for the code. */
    private static function messageFor($jsst_code, $jsst_json = array()) {
        if (is_array($jsst_json) && !empty($jsst_json['message']) && is_string($jsst_json['message'])) {
            return sanitize_text_field($jsst_json['message']);
        }
        return self::errorMessage($jsst_code);
    }

    /** The sentence for a failed sign-up or connect, by Zywrap's reason code. */
    public static function errorMessage($jsst_code) {
        $jsst_messages = array(
            'wrong_code'          => __('That code is not right. Check the e-mail and try again.', 'js-support-ticket'),
            'invalid_code'        => __('Enter the 6-digit code from the e-mail.', 'js-support-ticket'),
            'expired_code'        => __('That code has expired. Ask for a new one.', 'js-support-ticket'),
            'code_unavailable'    => __('That code can no longer be used. Ask for a new one.', 'js-support-ticket'),
            'rate_limited'        => __('Too many attempts. Wait a few minutes and try again.', 'js-support-ticket'),
            'key_limit_reached'   => __('Your Zywrap account already has as many API keys as it can. Revoke one on zywrap.com, then connect again.', 'js-support-ticket'),
            'account_blocked'     => __('This Zywrap account is blocked. Contact Zywrap support.', 'js-support-ticket'),
            'use_browser_connect' => __('This account uses two-factor sign-in. Use "Connect with zywrap.com instead" below.', 'js-support-ticket'),
            'network'             => __('This site could not reach Zywrap. Check that it can make outgoing HTTPS requests, then try again.', 'js-support-ticket'),
            'invalid_email'       => __('Enter a valid e-mail address.', 'js-support-ticket'),
            'terms_required'      => __('Tick the box to agree to the Zywrap Terms of Service and Privacy Policy.', 'js-support-ticket'),
            'expired'             => __('That connect link expired or was already used. Connect again to start over.', 'js-support-ticket'),
            'cancelled'           => __('Connecting was cancelled on zywrap.com. Nothing was changed.', 'js-support-ticket'),
            'invalid'             => __('zywrap.com sent back something this could not read. Nothing was changed.', 'js-support-ticket'),
            'server'              => __('Connecting to Zywrap did not finish. Nothing was changed; try again.', 'js-support-ticket'),
        );
        return isset($jsst_messages[$jsst_code]) ? $jsst_messages[$jsst_code] : $jsst_messages['server'];
    }

}
