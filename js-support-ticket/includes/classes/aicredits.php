<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The Zywrap credits a paid licence adds every month. (5.0.4)
 *
 * A Pro, Business or Agency licence funds one Zywrap account with a monthly
 * allowance: Zywrap refills it at the start of each month and spends it before
 * the account's own credits. jshelpdesk.com decides the amount and tells
 * Zywrap which account; this side only links the account this site is
 * connected to and shows what the licence server says. Nothing here can raise
 * the allowance: the server reads the plan from the licence itself.
 *
 * The answer is kept for twelve hours, so the screens that show it ask the
 * licence server at most twice a day; connecting or disconnecting asks again
 * at once.
 */
class JSSTaicredits {

    /** What the licence server last said. Not autoloaded. */
    const OPT = 'jsst_ai_credits';

    const TTL = 43200;

    /** After a failed ask, try again in an hour rather than on every page. */
    const RETRY = 3600;

    /** The Zywrap account this site is connected to, when Zywrap has named it. */
    public static function accountId() {
        $jsst_account = get_option('jsst_zywrap_account');
        return (is_array($jsst_account) && !empty($jsst_account['id'])) ? (string) $jsst_account['id'] : '';
    }

    /**
     * What this licence includes and where it goes, or null when the site has
     * no active licence.
     *
     * @return array|null monthly, plan, status, plan_remaining, renews_at, linked_site
     */
    public static function state($jsst_force = false) {
        if (!class_exists('JSSTlicense') || !JSSTlicense::isActive()) {
            return null;
        }
        $jsst_saved = get_option(self::OPT);
        if (!$jsst_force && is_array($jsst_saved) && isset($jsst_saved['checked'])
                && (int) $jsst_saved['checked'] > time() - self::TTL) {
            return $jsst_saved;
        }

        $jsst_account = self::accountId();
        if ($jsst_account === '' && JSSTaiengine::apiKey('zywrap') !== '') {
            $jsst_account = self::learnAccountId();
        }

        /* Only asks. The account says which one it is, so a site sharing the
           account another site claimed with sees the credits as its own. */
        return self::remember(JSSTlicense::askServer('/ai/status',
            $jsst_account !== '' ? array('account_id' => $jsst_account) : array()), $jsst_saved);
    }

    /**
     * The administrator pressed "Claim": this licence's monthly credits go to
     * the account this site is connected to.
     *
     * @return array|null The new state, or null when it could not be claimed.
     */
    public static function claim() {
        if (!class_exists('JSSTlicense') || !JSSTlicense::isActive()) {
            return null;
        }
        $jsst_account = self::accountId();
        if ($jsst_account === '' && JSSTaiengine::apiKey('zywrap') !== '') {
            $jsst_account = self::learnAccountId();
        }
        if ($jsst_account === '') {
            return null;
        }
        $jsst_state = self::remember(JSSTlicense::askServer('/ai/link', array('account_id' => $jsst_account)), get_option(self::OPT));
        return (is_array($jsst_state) && in_array($jsst_state['status'], array('active', 'pending'), true)) ? $jsst_state : null;
    }

    /** Keep what the licence server answered, or try again in an hour. */
    private static function remember($jsst_result, $jsst_saved) {
        if (is_wp_error($jsst_result) || empty($jsst_result['ok'])) {
            if (is_array($jsst_saved)) {
                $jsst_saved['checked'] = time() - self::TTL + self::RETRY;
                update_option(self::OPT, $jsst_saved, false);
                return $jsst_saved;
            }
            update_option(self::OPT, array('monthly' => 0, 'status' => 'unknown', 'checked' => time() - self::TTL + self::RETRY), false);
            return null;
        }

        $jsst_state = array(
            'monthly'        => isset($jsst_result['monthly_credits']) ? max(0, (int) $jsst_result['monthly_credits']) : 0,
            'plan'           => isset($jsst_result['plan']) ? sanitize_key((string) $jsst_result['plan']) : '',
            'status'         => isset($jsst_result['status']) ? sanitize_key((string) $jsst_result['status']) : 'none',
            'plan_remaining' => (isset($jsst_result['plan_remaining']) && is_numeric($jsst_result['plan_remaining'])) ? max(0, (int) $jsst_result['plan_remaining']) : null,
            'renews_at'      => isset($jsst_result['renews_at']) ? sanitize_text_field((string) $jsst_result['renews_at']) : '',
            'linked_site'    => isset($jsst_result['linked_site']) ? sanitize_text_field((string) $jsst_result['linked_site']) : '',
            'checked'        => time(),
        );
        /* jshelpdesk.com learns the balance only when it syncs with Zywrap, once
           a day; a Zywrap reply since then is newer. Within one month at the
           same amount the balance only goes down, so the lower figure is the
           current one. A new month or a new amount takes the server's. (5.0.4) */
        if (is_array($jsst_saved) && $jsst_state['status'] === 'active'
                && isset($jsst_saved['status'], $jsst_saved['renews_at'], $jsst_saved['monthly'])
                && $jsst_saved['status'] === 'active'
                && $jsst_saved['renews_at'] === $jsst_state['renews_at']
                && (int) $jsst_saved['monthly'] === $jsst_state['monthly']
                && isset($jsst_saved['plan_remaining']) && $jsst_saved['plan_remaining'] !== null
                && $jsst_state['plan_remaining'] !== null) {
            $jsst_state['plan_remaining'] = min($jsst_state['plan_remaining'], (int) $jsst_saved['plan_remaining']);
        }
        update_option(self::OPT, $jsst_state, false);
        return $jsst_state;
    }

    /** Right after connecting a Zywrap account: ask again now. */
    public static function link() {
        return self::state(true);
    }

    /** After disconnecting: what was said no longer applies to this site. */
    public static function forget() {
        delete_option(self::OPT);
    }

    /**
     * The last answer, without asking the server: for code that runs on every
     * AI request, which must not wait on jshelpdesk.com.
     */
    public static function cached() {
        if (!class_exists('JSSTlicense') || !JSSTlicense::isActive()) {
            return null;
        }
        $jsst_saved = get_option(self::OPT);
        return (is_array($jsst_saved) && isset($jsst_saved['monthly'])) ? $jsst_saved : null;
    }

    /**
     * One sentence for the Zywrap card, or '' when this licence includes
     * nothing. 'warn' is set when the credits are not reaching the account.
     *
     * @return array text, warn, claim (show the Claim button)
     */
    public static function describe($jsst_state, $jsst_connected) {
        if (!is_array($jsst_state) || empty($jsst_state['monthly'])) {
            return array('text' => '', 'warn' => false, 'claim' => false);
        }
        $jsst_label  = self::planLabel($jsst_state);
        $jsst_amount = number_format_i18n((int) $jsst_state['monthly']);
        if (!$jsst_connected) {
            return array('warn' => false, 'claim' => false, 'text' => sprintf(
                /* translators: 1: licence plan name such as Pro, 2: number of credits */
                __('Your %1$s licence adds %2$s Zywrap credits every month once you connect an account.', 'js-support-ticket'),
                $jsst_label, $jsst_amount));
        }
        if (in_array($jsst_state['status'], array('none', 'cancelled'), true)) {
            return array('warn' => false, 'claim' => true, 'text' => sprintf(
                /* translators: 1: licence plan name such as Pro, 2: number of credits */
                __('Your %1$s licence includes %2$s Zywrap credits every month.', 'js-support-ticket'),
                $jsst_label, $jsst_amount));
        }
        if ($jsst_state['status'] === 'elsewhere') {
            return array('warn' => true, 'claim' => false, 'text' => sprintf(
                /* translators: %s: the address of another site using this licence */
                __('This licence\'s monthly credits go to the Zywrap account connected on %s.', 'js-support-ticket'),
                $jsst_state['linked_site'] !== '' ? $jsst_state['linked_site'] : '?'));
        }
        $jsst_text = sprintf(
            /* translators: 1: licence plan name such as Pro, 2: number of credits */
            __('Your %1$s licence adds %2$s credits to this account every month.', 'js-support-ticket'),
            $jsst_label, $jsst_amount);
        if ($jsst_state['status'] === 'active') {
            $jsst_renews = ($jsst_state['renews_at'] !== '') ? strtotime($jsst_state['renews_at']) : false;
            if ($jsst_state['plan_remaining'] !== null && $jsst_renews) {
                $jsst_text .= ' ' . sprintf(
                    /* translators: 1: number of credits, 2: a date */
                    __('%1$s left this month. They refill on %2$s.', 'js-support-ticket'),
                    number_format_i18n((int) $jsst_state['plan_remaining']),
                    date_i18n(get_option('date_format'), $jsst_renews));
            }
            return array('text' => $jsst_text, 'warn' => false, 'claim' => false);
        }
        return array('warn' => true, 'claim' => false, 'text' => $jsst_text . ' '
            . __('Waiting for Zywrap to add them. This is checked again twice a day.', 'js-support-ticket'));
    }

    /** Credits a month this licence includes; 0 when none or not known. */
    public static function monthly() {
        $jsst_state = self::state();
        return is_array($jsst_state) ? (int) $jsst_state['monthly'] : 0;
    }

    /**
     * The plan balance a Zywrap reply reports, so the screens are current
     * between the twice-daily asks.
     */
    public static function noteRemaining($jsst_remaining) {
        $jsst_saved = get_option(self::OPT);
        if (!is_array($jsst_saved) || !is_numeric($jsst_remaining)) {
            return;
        }
        $jsst_saved['plan_remaining'] = max(0, (int) $jsst_remaining);
        update_option(self::OPT, $jsst_saved, false);
    }

    /** The plan's name as the licence screen shows it, e.g. "Pro". */
    public static function planLabel($jsst_state) {
        $jsst_slug = (is_array($jsst_state) && !empty($jsst_state['plan'])) ? $jsst_state['plan'] : '';
        if ($jsst_slug !== '' && class_exists('JSSTplans')) {
            $jsst_plan = JSSTplans::plan($jsst_slug);
            if (is_array($jsst_plan) && !empty($jsst_plan['label'])) {
                return (string) $jsst_plan['label'];
            }
        }
        return ucfirst($jsst_slug);
    }

    /**
     * A key typed by hand: ask Zywrap which account it belongs to, and keep
     * the answer beside the rest of the account summary.
     */
    private static function learnAccountId() {
        $jsst_response = wp_remote_post('https://api.zywrap.com/v1/key/check', array(
            'timeout'     => 15,
            'redirection' => 0,
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => wp_json_encode(array('apiKey' => JSSTaiengine::apiKey('zywrap'))),
        ));
        if (is_wp_error($jsst_response) || (int) wp_remote_retrieve_response_code($jsst_response) !== 200) {
            return '';
        }
        $jsst_json = json_decode((string) wp_remote_retrieve_body($jsst_response), true);
        if (!is_array($jsst_json) || empty($jsst_json['account']['id']) || !is_string($jsst_json['account']['id'])) {
            return '';
        }
        $jsst_id = sanitize_text_field($jsst_json['account']['id']);
        $jsst_account = get_option('jsst_zywrap_account');
        if (!is_array($jsst_account)) {
            $jsst_account = array('email' => '', 'key_name' => '', 'connected_at' => 0);
        }
        $jsst_account['id'] = $jsst_id;
        update_option('jsst_zywrap_account', $jsst_account, false);
        return $jsst_id;
    }
}
