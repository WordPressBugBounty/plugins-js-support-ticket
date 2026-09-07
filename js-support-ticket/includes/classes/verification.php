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
if (class_exists('JSSTverification')) {
    return;
}


/**
 * Human verification for public forms. (Roadmap 4.0-SEC-01)
 *
 * One pluggable provider interface instead of a hard-coded choice between
 * Google reCAPTCHA and a distorted arithmetic question:
 *
 *   turnstile     Cloudflare Turnstile, invisible in the common case
 *   hcaptcha      hCaptcha
 *   recaptcha_v3  reCAPTCHA v3, score mode with a configurable threshold
 *   recaptcha_v2  reCAPTCHA v2 checkbox, kept for sites already using it
 *   builtin       self-hosted: honeypot + submission timing + proof of work.
 *                 No third-party service, no account, no image to read, and
 *                 nothing to solve for a real customer.
 *   none          verification off
 *
 * Every provider is verified on the server. The built-in provider degrades to
 * the legacy arithmetic question inside <noscript>, so a visitor with
 * JavaScript disabled can still file a ticket.
 *
 * Sites that were using reCAPTCHA before 4.0 keep using it: the provider is
 * derived from the old captcha_selection / recaptcha_version settings until an
 * administrator picks one explicitly.
 */
class JSSTverification {

    /** Signed-token lifetime, and the replay window. */
    const TOKEN_TTL = 10800;

    /** Where the HMAC key comes from. No new secret to store. */
    private static function secret() {
        return wp_salt('jsst-verification');
    }

    public static function providers() {
        return array('none', 'builtin', 'turnstile', 'hcaptcha', 'recaptcha_v3', 'recaptcha_v2');
    }

    /**
     * The provider in force, falling back to the pre-4.0 settings.
     */
    public static function provider() {
        $jsst_provider = isset(jssupportticket::$_config['captcha_provider'])
            ? trim((string) jssupportticket::$_config['captcha_provider'])
            : '';
        if ($jsst_provider === '' || !in_array($jsst_provider, self::providers(), true)) {
            // Derived, not guessed: whatever the site was already doing.
            $jsst_selection = isset(jssupportticket::$_config['captcha_selection']) ? jssupportticket::$_config['captcha_selection'] : 2;
            if ($jsst_selection == 1) {
                $jsst_version = isset(jssupportticket::$_config['recaptcha_version']) ? jssupportticket::$_config['recaptcha_version'] : 1;
                $jsst_provider = ($jsst_version == 2) ? 'recaptcha_v3' : 'recaptcha_v2';
            } else {
                $jsst_provider = 'builtin';
            }
        }
        // A remote provider with no keys entered would lock the form. Fall back
        // to the self-hosted check instead of blocking every submission.
        if (self::isRemote($jsst_provider) && !self::isConfigured($jsst_provider)) {
            $jsst_provider = 'builtin';
        }
        return apply_filters('jsst_verification_provider', $jsst_provider);
    }

    public static function isRemote($jsst_provider) {
        return in_array($jsst_provider, array('turnstile', 'hcaptcha', 'recaptcha_v3', 'recaptcha_v2'), true);
    }

    /**
     * Site key / secret key pair for a remote provider.
     */
    public static function keys($jsst_provider) {
        $jsst_config = jssupportticket::$_config;
        switch ($jsst_provider) {
            case 'turnstile':
                return array(
                    'site'   => isset($jsst_config['captcha_turnstile_sitekey']) ? $jsst_config['captcha_turnstile_sitekey'] : '',
                    'secret' => isset($jsst_config['captcha_turnstile_secret']) ? $jsst_config['captcha_turnstile_secret'] : '',
                );
            case 'hcaptcha':
                return array(
                    'site'   => isset($jsst_config['captcha_hcaptcha_sitekey']) ? $jsst_config['captcha_hcaptcha_sitekey'] : '',
                    'secret' => isset($jsst_config['captcha_hcaptcha_secret']) ? $jsst_config['captcha_hcaptcha_secret'] : '',
                );
            case 'recaptcha_v3':
                return array(
                    'site'   => isset($jsst_config['captcha_recaptcha3_sitekey']) ? $jsst_config['captcha_recaptcha3_sitekey'] : '',
                    'secret' => isset($jsst_config['captcha_recaptcha3_secret']) ? $jsst_config['captcha_recaptcha3_secret'] : '',
                );
            case 'recaptcha_v2':
                return array(
                    'site'   => isset($jsst_config['recaptcha_publickey']) ? $jsst_config['recaptcha_publickey'] : '',
                    'secret' => isset($jsst_config['recaptcha_privatekey']) ? $jsst_config['recaptcha_privatekey'] : '',
                );
        }
        return array('site' => '', 'secret' => '');
    }

    public static function isConfigured($jsst_provider) {
        if (!self::isRemote($jsst_provider)) {
            return true;
        }
        $jsst_keys = self::keys($jsst_provider);
        return $jsst_keys['site'] != '' && $jsst_keys['secret'] != '';
    }

    private static function configInt($jsst_name, $jsst_default) {
        if (!isset(jssupportticket::$_config[$jsst_name]) || jssupportticket::$_config[$jsst_name] === '') {
            return $jsst_default;
        }
        return (int) jssupportticket::$_config[$jsst_name];
    }

    /**
     * Proof-of-work difficulty, in leading zero bits. Kept a multiple of four so
     * the check is a hex-prefix comparison on both sides.
     */
    private static function powBits() {
        $jsst_bits = self::configInt('captcha_pow_bits', 12);
        if ($jsst_bits < 0) {
            $jsst_bits = 0;
        }
        if ($jsst_bits > 24) {
            $jsst_bits = 24;
        }
        return $jsst_bits - ($jsst_bits % 4);
    }

    private static function minSeconds() {
        $jsst_seconds = self::configInt('captcha_min_submit_seconds', 3);
        return ($jsst_seconds < 0) ? 0 : $jsst_seconds;
    }

    private static function failOpen() {
        return self::configInt('captcha_fail_open', 1) == 1;
    }

    /* ------------------------------------------------------------------ *
     * Rendering
     * ------------------------------------------------------------------ */

    /**
     * Register the provider's script. Safe to call more than once per request.
     */
    public function scripts() {
        $jsst_provider = self::provider();
        $jsst_keys = self::keys($jsst_provider);
        $jsst_version = isset(jssupportticket::$_config['productversion']) ? jssupportticket::$_config['productversion'] : '400';
        switch ($jsst_provider) {
            case 'turnstile':
                wp_enqueue_script('jsst-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), $jsst_version, true);
                break;
            case 'hcaptcha':
                wp_enqueue_script('jsst-hcaptcha', 'https://js.hcaptcha.com/1/api.js', array(), $jsst_version, true);
                break;
            case 'recaptcha_v3':
                wp_enqueue_script('jsst-recaptcha3', 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode($jsst_keys['site']), array(), $jsst_version, true);
                break;
            case 'recaptcha_v2':
                wp_enqueue_script('jsst-recaptcha2', 'https://www.google.com/recaptcha/api.js', array(), $jsst_version, true);
                break;
            case 'builtin':
                wp_enqueue_script('jsst-verification', JSST_PLUGIN_URL . 'includes/js/verification.js', array(), $jsst_version, true);
                break;
        }
    }

    /**
     * A fresh signed challenge. Carries the issue time and the form context, so
     * a token minted for one form cannot be replayed on another.
     */
    private function issueToken($jsst_context) {
        $jsst_payload = wp_json_encode(array(
            't' => time(),
            'c' => (string) $jsst_context,
            'n' => wp_generate_password(16, false, false),
        ));
        $jsst_encoded = rtrim(strtr(base64_encode($jsst_payload), '+/', '-_'), '=');
        $jsst_signature = hash_hmac('sha256', $jsst_encoded, self::secret());
        return $jsst_encoded . '.' . $jsst_signature;
    }

    /**
     * Whether the provider actually puts something on screen for the visitor to
     * read or solve. The built-in check is silent: its status line hides itself
     * as soon as the browser has done the work, so a form that printed a
     * "Security check" heading for it was labelling an empty box. The heading
     * for the no-JavaScript fallback is emitted inside the <noscript> block by
     * builtinField() instead. (Roadmap 4.0-SEC-01)
     */
    public static function hasVisibleField() {
        return !in_array(self::provider(), array('none', 'builtin'), true);
    }

    /**
     * The markup for a form. $jsst_context is 'ticket', 'register', and so on.
     */
    public function field($jsst_context = 'ticket') {
        $jsst_provider = self::provider();
        if ($jsst_provider === 'none') {
            return '';
        }
        $this->scripts();
        $jsst_keys = self::keys($jsst_provider);
        $jsst_out = '<div class="jsst-verification jsst-verification-' . esc_attr($jsst_provider) . '">';
        switch ($jsst_provider) {
            case 'turnstile':
                $jsst_out .= '<div class="cf-turnstile" data-sitekey="' . esc_attr($jsst_keys['site']) . '"></div>';
                break;
            case 'hcaptcha':
                $jsst_out .= '<div class="h-captcha" data-sitekey="' . esc_attr($jsst_keys['site']) . '"></div>';
                break;
            case 'recaptcha_v3':
                // Filled in by the provider script on submit; see recaptchaV3Script().
                $jsst_out .= '<input type="hidden" name="jsst_recaptcha3_token" class="jsst-recaptcha3-token" value="" />';
                $jsst_out .= '<div class="jsst-verification-notice">' . esc_html(__('This form is protected by reCAPTCHA.', 'js-support-ticket')) . '</div>';
                break;
            case 'recaptcha_v2':
                $jsst_out .= '<div class="g-recaptcha" data-sitekey="' . esc_attr($jsst_keys['site']) . '"></div>';
                break;
            case 'builtin':
                $jsst_out .= $this->builtinField($jsst_context);
                break;
        }
        $jsst_out .= '</div>';
        return $jsst_out;
    }

    /**
     * Honeypot + timing + proof of work, plus the arithmetic question as the
     * no-JavaScript path.
     */
    private function builtinField($jsst_context) {
        $jsst_token = $this->issueToken($jsst_context);
        $jsst_bits = self::powBits();
        $jsst_out = '';
        // Honeypot. Positioned off-screen rather than display:none, and hidden
        // from assistive technology, so a real visitor never meets it.
        $jsst_out .= '<div class="jsst-verification-hp" aria-hidden="true">';
        $jsst_out .= '<label for="jsst_contact_url">' . esc_html(__('Leave this field empty', 'js-support-ticket')) . '</label>';
        $jsst_out .= '<input type="text" name="jsst_contact_url" id="jsst_contact_url" value="" tabindex="-1" autocomplete="off" />';
        $jsst_out .= '</div>';
        $jsst_out .= '<input type="hidden" name="jsst_vtoken" class="jsst-vtoken" value="' . esc_attr($jsst_token) . '" />';
        $jsst_out .= '<input type="hidden" name="jsst_vpow" class="jsst-vpow" value="" />';
        $jsst_out .= '<div class="jsst-verification-work" data-token="' . esc_attr($jsst_token) . '" data-bits="' . esc_attr($jsst_bits) . '">';
        $jsst_out .= '<span class="jsst-verification-status" role="status">' . esc_html(__('Checking your browser…', 'js-support-ticket')) . '</span>';
        $jsst_out .= '</div>';
        // No JavaScript: the arithmetic question, which is verified through the
        // existing session-backed check.
        $jsst_out .= '<noscript>';
        // Without JavaScript the arithmetic question is the only thing on
        // screen, and it does need a label. The form omits its own heading for
        // this provider, so carry one here.
        $jsst_out .= '<div class="js-ticket-from-field-title jsst-verification-noscript-title">' . esc_html(__('Security check', 'js-support-ticket')) . '</div>';
        $jsst_captcha = new JSSTcaptcha;
        $jsst_out .= $jsst_captcha->getCaptchaForForm();
        $jsst_out .= '</noscript>';
        return $jsst_out;
    }

    /**
     * reCAPTCHA v3 has to run at submit time to produce a token. Emitted once,
     * after the form, by the templates that use it.
     */
    public function recaptchaV3Script($jsst_form_id, $jsst_action = 'jsst_submit') {
        if (self::provider() !== 'recaptcha_v3') {
            return '';
        }
        $jsst_keys = self::keys('recaptcha_v3');
        $jsst_out = '<script>';
        $jsst_out .= '(function(){var f=document.getElementById(' . wp_json_encode($jsst_form_id) . ');if(!f||!window.grecaptcha)return;';
        $jsst_out .= 'var sent=false;f.addEventListener("submit",function(e){if(sent)return;e.preventDefault();';
        $jsst_out .= 'grecaptcha.ready(function(){grecaptcha.execute(' . wp_json_encode($jsst_keys['site']) . ',{action:' . wp_json_encode($jsst_action) . '}).then(function(t){';
        $jsst_out .= 'var i=f.querySelector(".jsst-recaptcha3-token");if(i){i.value=t;}sent=true;';
        $jsst_out .= 'if(typeof f.requestSubmit==="function"){f.requestSubmit();}else{f.submit();}});});});})();';
        $jsst_out .= '</script>';
        return $jsst_out;
    }

    /* ------------------------------------------------------------------ *
     * Verification
     * ------------------------------------------------------------------ */

    private $jsst_error = '';

    public function lastError() {
        if ($this->jsst_error != '') {
            return $this->jsst_error;
        }
        return esc_html(__('We could not confirm that this form was submitted by a person. Please reload the page and try again.', 'js-support-ticket'));
    }

    /**
     * True when the submission passes. Sets lastError() when it does not.
     */
    public function verify($jsst_context = 'ticket') {
        $this->jsst_error = '';
        $jsst_provider = self::provider();
        if ($jsst_provider === 'none') {
            return true;
        }
        if ($jsst_provider === 'builtin') {
            return $this->verifyBuiltin($jsst_context);
        }
        return $this->verifyRemote($jsst_provider);
    }

    private function verifyBuiltin($jsst_context) {
        // getVar()'s third argument is the default value, not the method. These
        // read the empty string when the field is absent or blank; passing
        // 'post' there made an empty honeypot — which is every real browser —
        // read back as the word "post" and rejected every submission, and made
        // a missing token unparseable instead of falling through to the
        // no-JavaScript question. (Roadmap 4.0-SEC-01)

        // 1. Honeypot. A real browser leaves it empty.
        $jsst_honeypot = JSSTrequest::getVar('jsst_contact_url', 'post', '');
        if (trim((string) $jsst_honeypot) !== '') {
            $this->jsst_error = esc_html(__('This submission looks automated and was rejected.', 'js-support-ticket'));
            return false;
        }

        $jsst_token = (string) JSSTrequest::getVar('jsst_vtoken', 'post', '');
        $jsst_pow = (string) JSSTrequest::getVar('jsst_vpow', 'post', '');

        // No signed token at all: the only remaining honest path is the
        // no-JavaScript arithmetic question.
        if ($jsst_token === '') {
            $jsst_captcha = new JSSTcaptcha;
            if ($jsst_captcha->checkCaptchaUserForm() == 1) {
                return true;
            }
            $this->jsst_error = esc_html(__('The verification question was not answered correctly. Please try again.', 'js-support-ticket'));
            return false;
        }

        // 2. Signature and age.
        $jsst_parts = explode('.', $jsst_token);
        if (count($jsst_parts) !== 2) {
            $this->jsst_error = esc_html(__('The verification token was malformed. Please reload the page and try again.', 'js-support-ticket'));
            return false;
        }
        list($jsst_encoded, $jsst_signature) = $jsst_parts;
        $jsst_expected = hash_hmac('sha256', $jsst_encoded, self::secret());
        if (!hash_equals($jsst_expected, $jsst_signature)) {
            $this->jsst_error = esc_html(__('The verification token was not valid. Please reload the page and try again.', 'js-support-ticket'));
            return false;
        }
        $jsst_payload = json_decode(base64_decode(strtr($jsst_encoded, '-_', '+/')), true);
        if (!is_array($jsst_payload) || !isset($jsst_payload['t'])) {
            $this->jsst_error = esc_html(__('The verification token was not valid. Please reload the page and try again.', 'js-support-ticket'));
            return false;
        }
        $jsst_age = time() - (int) $jsst_payload['t'];
        if ($jsst_age > self::TOKEN_TTL) {
            $this->jsst_error = esc_html(__('This form was open for too long. Please reload the page and submit again.', 'js-support-ticket'));
            return false;
        }
        if ($jsst_age < self::minSeconds()) {
            $this->jsst_error = esc_html(__('That was submitted faster than a form can be filled in. Please try again.', 'js-support-ticket'));
            return false;
        }
        if (isset($jsst_payload['c']) && $jsst_payload['c'] !== (string) $jsst_context) {
            $this->jsst_error = esc_html(__('The verification token belongs to a different form. Please reload the page and try again.', 'js-support-ticket'));
            return false;
        }

        // 3. Single use.
        $jsst_replaykey = 'jsst_vt_' . md5($jsst_signature);
        if (get_transient($jsst_replaykey)) {
            $this->jsst_error = esc_html(__('This form has already been submitted. Please reload the page if you need to send another.', 'js-support-ticket'));
            return false;
        }

        // 4. Proof of work.
        $jsst_bits = self::powBits();
        if ($jsst_bits > 0) {
            if ($jsst_pow === '' || !$this->checkProofOfWork($jsst_encoded, $jsst_pow, $jsst_bits)) {
                $this->jsst_error = esc_html(__('Your browser did not finish the automated security check. Please reload the page and try again.', 'js-support-ticket'));
                return false;
            }
        }

        set_transient($jsst_replaykey, 1, self::TOKEN_TTL);
        return true;
    }

    /**
     * sha256(payload:nonce) must start with $jsst_bits zero bits.
     */
    private function checkProofOfWork($jsst_payload, $jsst_nonce, $jsst_bits) {
        if (!preg_match('/^[0-9]{1,20}$/', (string) $jsst_nonce)) {
            return false;
        }
        $jsst_digest = hash('sha256', $jsst_payload . ':' . $jsst_nonce);
        $jsst_zeros = (int) ($jsst_bits / 4);
        return substr($jsst_digest, 0, $jsst_zeros) === str_repeat('0', $jsst_zeros);
    }

    private function verifyRemote($jsst_provider) {
        // Same argument order as verifyBuiltin(): 'post' is the method, '' the
        // default. An absent provider response has to read as empty so the
        // "no response" branch below can report it. (Roadmap 4.0-SEC-01)
        $jsst_keys = self::keys($jsst_provider);
        switch ($jsst_provider) {
            case 'turnstile':
                $jsst_endpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
                $jsst_response = JSSTrequest::getVar('cf-turnstile-response', 'post', '');
                break;
            case 'hcaptcha':
                $jsst_endpoint = 'https://api.hcaptcha.com/siteverify';
                $jsst_response = JSSTrequest::getVar('h-captcha-response', 'post', '');
                break;
            case 'recaptcha_v3':
                $jsst_endpoint = 'https://www.google.com/recaptcha/api/siteverify';
                $jsst_response = JSSTrequest::getVar('jsst_recaptcha3_token', 'post', '');
                break;
            default:
                $jsst_endpoint = 'https://www.google.com/recaptcha/api/siteverify';
                $jsst_response = JSSTrequest::getVar('g-recaptcha-response', 'post', '');
                break;
        }

        if ($jsst_response == '') {
            // The widget never loaded — a blocked CDN, an ad blocker, a mobile
            // browser that gave up. Say so instead of "incorrect captcha".
            $this->jsst_error = esc_html(__('The security check did not load or was not completed. Please reload the page and try again.', 'js-support-ticket'));
            return false;
        }

        $jsst_result = wp_remote_post($jsst_endpoint, array(
            'body' => array(
                'secret'   => $jsst_keys['secret'],
                'response' => $jsst_response,
                'remoteip' => self::clientIp(),
            ),
            'timeout'   => 7,
            'sslverify' => true,
        ));

        if (is_wp_error($jsst_result) || wp_remote_retrieve_response_code($jsst_result) != 200) {
            // The verification service is unreachable. Blocking every customer
            // because a third party is down is worse than letting the
            // submission through, so fail open by default and record why.
            JSSTincluder::getJSModel('systemerror')->addSystemError('Verification provider unreachable: ' . $jsst_provider);
            if (self::failOpen()) {
                return true;
            }
            $this->jsst_error = esc_html(__('The security check could not be completed right now. Please try again in a moment.', 'js-support-ticket'));
            return false;
        }

        $jsst_body = json_decode(wp_remote_retrieve_body($jsst_result), true);
        if (!is_array($jsst_body) || empty($jsst_body['success'])) {
            $this->jsst_error = esc_html(__('The security check did not pass. Please reload the page and try again.', 'js-support-ticket'));
            return false;
        }

        // Score-based providers: a pass is a score above the threshold.
        if ($jsst_provider === 'recaptcha_v3' && isset($jsst_body['score'])) {
            $jsst_threshold = isset(jssupportticket::$_config['captcha_score_threshold'])
                ? (float) jssupportticket::$_config['captcha_score_threshold']
                : 0.5;
            if ($jsst_threshold <= 0 || $jsst_threshold > 1) {
                $jsst_threshold = 0.5;
            }
            if ((float) $jsst_body['score'] < $jsst_threshold) {
                $this->jsst_error = esc_html(__('This submission was scored as automated traffic. Please try again, or contact us by e-mail.', 'js-support-ticket'));
                return false;
            }
        }

        return true;
    }

    public static function clientIp() {
        $jsst_ip = isset($_SERVER['REMOTE_ADDR']) ? jssupportticket::JSST_sanitizeData($_SERVER['REMOTE_ADDR']) : '';
        return (string) $jsst_ip;
    }

}
