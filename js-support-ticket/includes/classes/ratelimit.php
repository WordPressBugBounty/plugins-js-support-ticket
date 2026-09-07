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
if (class_exists('JSSTratelimit')) {
    return;
}


/**
 * Submission rate limits for public forms. (Roadmap 4.0-SEC-01)
 *
 * Counts attempts per subject (an IP address, an e-mail address) inside a
 * rolling window using transients, so it works on any host with no schema and
 * no cron. Verification proves a person is submitting; this bounds how often
 * one person — or one script that got past verification — may submit.
 *
 * Logged-in agents and administrators are never limited.
 */
class JSSTratelimit {

    private static function enabled() {
        if (!isset(jssupportticket::$_config['submission_rate_limit'])) {
            return true;
        }
        return jssupportticket::$_config['submission_rate_limit'] == 1;
    }

    private static function limit() {
        $jsst_max = isset(jssupportticket::$_config['submission_rate_limit_max'])
            ? (int) jssupportticket::$_config['submission_rate_limit_max'] : 5;
        return ($jsst_max > 0) ? $jsst_max : 5;
    }

    private static function window() {
        $jsst_window = isset(jssupportticket::$_config['submission_rate_limit_window'])
            ? (int) jssupportticket::$_config['submission_rate_limit_window'] : 600;
        return ($jsst_window > 0) ? $jsst_window : 600;
    }

    private static function exempt() {
        if (current_user_can('manage_options')) {
            return true;
        }
        if (in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            return true;
        }
        return false;
    }

    private static function key($jsst_action, $jsst_subject) {
        return 'jsst_rl_' . md5($jsst_action . '|' . strtolower(trim((string) $jsst_subject)));
    }

    /**
     * Record one attempt and report whether the subject is now over the limit.
     *
     * Returns true when the submission may proceed.
     */
    public static function check($jsst_action, $jsst_subject = '') {
        if (!self::enabled() || self::exempt()) {
            return true;
        }
        if ($jsst_subject === '') {
            $jsst_subject = JSSTverification::clientIp();
        }
        if ($jsst_subject === '') {
            return true;
        }
        $jsst_key = self::key($jsst_action, $jsst_subject);
        $jsst_count = (int) get_transient($jsst_key);
        if ($jsst_count >= self::limit()) {
            return false;
        }
        set_transient($jsst_key, $jsst_count + 1, self::window());
        return true;
    }

    /**
     * The message to show when check() said no.
     */
    public static function message() {
        $jsst_minutes = (int) ceil(self::window() / 60);
        return sprintf(
            /* translators: 1: number of submissions, 2: number of minutes */
            esc_html(_n(
                'You have reached the limit of %1$d submission in %2$d minutes. Please wait a little before submitting again.',
                'You have reached the limit of %1$d submissions in %2$d minutes. Please wait a little before submitting again.',
                self::limit(),
                'js-support-ticket'
            )),
            self::limit(),
            $jsst_minutes
        );
    }

}
