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
if (class_exists('JSSTmailhealth')) {
    return;
}


/**
 * Email health. (Roadmap 4.0-OPS-01)
 *
 * Undelivered e-mail is the most common support complaint about a help desk and
 * the hardest to diagnose, because everything looks fine from the inside: the
 * ticket saved, the notification "sent", and the customer heard nothing. This
 * gathers the handful of facts that actually explain it — who is sending the
 * mail, what address it claims to be from, whether the last send worked, and
 * whether the cron that drives piping is running at all — and puts them on one
 * screen.
 *
 * It deliberately does not send mail itself. Competing with the dedicated SMTP
 * plugins is a maintenance burden with no upside: they already do it better, and
 * a site that has one installed should be using it. What this does instead is
 * notice which one is in charge and say so, and tell an administrator with none
 * what their options are.
 */
class JSSTmailhealth {

    /** Where the last delivery outcome is recorded. */
    const LAST_RESULT_OPTION = 'jsst_mail_last_result';

    /**
     * Mail plugins worth naming, as plugin file => label.
     *
     * Matched against the active plugin list. The point is not to be exhaustive
     * — an unknown plugin still shows up as "something is filtering wp_mail" via
     * detectProvider() — but to name the common ones so the screen can say
     * "FluentSMTP is handling your mail" rather than "something is".
     */
    public static function knownProviders() {
        return apply_filters('jsst_mail_known_providers', array(
            'wp-mail-smtp/wp_mail_smtp.php'            => 'WP Mail SMTP',
            'fluent-smtp/fluent-smtp.php'              => 'FluentSMTP',
            'post-smtp/postman-smtp.php'               => 'Post SMTP',
            'easy-wp-smtp/easy-wp-smtp.php'            => 'Easy WP SMTP',
            'wp-ses/wp-ses.php'                        => 'WP Offload SES',
            'gmail-smtp/main.php'                      => 'Gmail SMTP',
            'sendgrid-email-delivery-simplified/wpsendgrid.php' => 'SendGrid',
            'mailgun/mailgun.php'                      => 'Mailgun',
            'mailpoet/mailpoet.php'                    => 'MailPoet',
            'sib-plugin/sendinblue.php'                => 'Brevo (Sendinblue)',
            'wp-smtp/wp-smtp.php'                      => 'WP SMTP',
        ));
    }

    /**
     * Which plugins are active on this site.
     *
     * Wrapped so the detection below can be exercised without WordPress.
     */
    private static function activePlugins() {
        $jsst_active = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $jsst_network = (array) get_site_option('active_sitewide_plugins', array());
            $jsst_active = array_merge($jsst_active, array_keys($jsst_network));
        }
        return $jsst_active;
    }

    /**
     * Who is actually going to send the next e-mail.
     *
     * Three answers, in descending order of confidence:
     *
     *   'plugin'  a mail plugin we can name is active.
     *   'filter'  something is hooked to phpmailer_init or pre_wp_mail, so mail
     *             is being intercepted, but we cannot say by what. A theme, a
     *             host's mu-plugin, or a plugin not in the list above.
     *   'phpmail' nothing is intercepting, so PHP's mail() will be used. This is
     *             the case that quietly fails on most modern hosting.
     */
    public static function detectProvider() {
        $jsst_active = self::activePlugins();
        foreach (self::knownProviders() as $jsst_file => $jsst_label) {
            if (in_array($jsst_file, $jsst_active, true)) {
                return array('type' => 'plugin', 'label' => $jsst_label, 'plugin' => $jsst_file);
            }
        }
        if (has_filter('pre_wp_mail') || has_action('phpmailer_init')) {
            return array(
                'type'  => 'filter',
                'label' => esc_html(__('An unidentified plugin or host configuration', 'js-support-ticket')),
                'plugin' => '',
            );
        }
        return array(
            'type'  => 'phpmail',
            'label' => esc_html(__('PHP mail() — no SMTP service', 'js-support-ticket')),
            'plugin' => '',
        );
    }

    /**
     * The host part of an e-mail address, lower-cased.
     */
    public static function domainOf($jsst_email) {
        $jsst_email = trim((string) $jsst_email);
        $jsst_at = strrpos($jsst_email, '@');
        if ($jsst_at === false) {
            return '';
        }
        return strtolower(substr($jsst_email, $jsst_at + 1));
    }

    /**
     * Mailbox providers that publish a strict DMARC policy.
     *
     * Sending "From: someone@gmail.com" through your own server is not a
     * misconfiguration that might work — Gmail and Yahoo publish p=reject, so
     * receivers are being told to throw the message away. This is the single
     * most common cause of a help desk whose mail silently vanishes.
     */
    private static function strictDmarcDomains() {
        return array(
            'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.uk', 'ymail.com',
            'aol.com', 'hotmail.com', 'outlook.com', 'live.com', 'msn.com',
            'icloud.com', 'me.com', 'mac.com', 'gmx.com', 'gmx.net', 'mail.ru',
        );
    }

    /**
     * Compare the address the help desk sends as against the site's own domain.
     *
     * @return array status => ok|warning|critical, plus the two domains and a
     *               message explaining what to do about it.
     */
    public static function checkSenderAlignment($jsst_from_email, $jsst_site_host) {
        $jsst_from_domain = self::domainOf($jsst_from_email);
        $jsst_site_domain = strtolower(preg_replace('/^www\./', '', (string) $jsst_site_host));

        if ($jsst_from_email === '' || $jsst_from_domain === '') {
            return array(
                'status'      => 'critical',
                'from_domain' => '',
                'site_domain' => $jsst_site_domain,
                'message'     => esc_html(__('No sender address is configured, so notifications fall back to whatever WordPress decides. Set one on the Configurations screen.', 'js-support-ticket')),
            );
        }

        if (in_array($jsst_from_domain, self::strictDmarcDomains(), true)) {
            return array(
                'status'      => 'critical',
                'from_domain' => $jsst_from_domain,
                'site_domain' => $jsst_site_domain,
                'message'     => sprintf(
                    /* translators: %s: the sender e-mail domain, e.g. gmail.com */
                    esc_html(__('Mail is sent as %s, and that provider tells receiving servers to reject anything sent on its behalf from elsewhere. Notifications from this site will be discarded rather than delivered. Send from an address on your own domain instead.', 'js-support-ticket')),
                    esc_html($jsst_from_domain)
                ),
            );
        }

        // A subdomain of the site domain is fine — mail.example.com sending for
        // example.com is a normal, alignable setup.
        $jsst_aligned = ($jsst_from_domain === $jsst_site_domain)
                || ($jsst_site_domain !== '' && substr($jsst_from_domain, -strlen('.' . $jsst_site_domain)) === '.' . $jsst_site_domain);

        if (!$jsst_aligned) {
            return array(
                'status'      => 'warning',
                'from_domain' => $jsst_from_domain,
                'site_domain' => $jsst_site_domain,
                'message'     => sprintf(
                    /* translators: 1: sender e-mail domain, 2: the site's domain */
                    esc_html(__('Mail is sent as %1$s but this site is %2$s. That only works if %1$s publishes SPF and DKIM records naming whatever sends your mail; otherwise notifications land in spam. Either send from %2$s or add those records.', 'js-support-ticket')),
                    esc_html($jsst_from_domain),
                    esc_html($jsst_site_domain)
                ),
            );
        }

        return array(
            'status'      => 'ok',
            'from_domain' => $jsst_from_domain,
            'site_domain' => $jsst_site_domain,
            'message'     => esc_html(__('The sender address is on this site\'s own domain, which is what receiving servers expect.', 'js-support-ticket')),
        );
    }

    /**
     * Record how the last send went.
     *
     * Called from the mail path on both outcomes, so the screen can answer "did
     * the last one work?" without an administrator having to reproduce it.
     */
    public static function recordResult($jsst_ok, $jsst_to = '', $jsst_error = '') {
        update_option(self::LAST_RESULT_OPTION, array(
            'ok'    => $jsst_ok ? 1 : 0,
            'to'    => is_email($jsst_to) ? $jsst_to : '',
            'error' => is_string($jsst_error) ? substr(wp_strip_all_tags($jsst_error), 0, 500) : '',
            'time'  => time(),
        ), false);
    }

    /**
     * The last recorded send, or an empty array when nothing has been sent yet.
     */
    public static function lastResult() {
        $jsst_result = get_option(self::LAST_RESULT_OPTION, array());
        return is_array($jsst_result) ? $jsst_result : array();
    }

    /**
     * The state of the scheduled work that e-mail depends on.
     *
     * Piping and the status/overdue sweep both run on WP-Cron, which only fires
     * when the site is visited unless a real cron calls wp-cron.php. A site with
     * DISABLE_WP_CRON set and no system cron looks completely healthy right up
     * until somebody asks why no ticket has arrived by e-mail for a week.
     */
    public static function cronStatus() {
        $jsst_hooks = array(
            'jssupporticket_ticketviaemail'    => esc_html(__('Fetch tickets and replies from the mailbox', 'js-support-ticket')),
            'jssupporticket_updateticketstatus' => esc_html(__('Update ticket statuses and overdue flags', 'js-support-ticket')),
        );
        $jsst_out = array(
            'disabled' => (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON),
            'hooks'    => array(),
        );
        foreach ($jsst_hooks as $jsst_hook => $jsst_label) {
            $jsst_next = wp_next_scheduled($jsst_hook);
            $jsst_out['hooks'][$jsst_hook] = array(
                'label' => $jsst_label,
                'next'  => $jsst_next ? (int) $jsst_next : 0,
                // Overdue by more than an hour past its slot means the schedule
                // exists but nothing is running it.
                'stalled' => ($jsst_next && $jsst_next < (time() - HOUR_IN_SECONDS)),
            );
        }
        return $jsst_out;
    }

    /**
     * Send a test message and say plainly what happened.
     *
     * Uses wp_mail so it travels the same path a real notification does — a test
     * that bypassed the site's mail plugin would prove nothing. PHPMailer's own
     * error is captured, because "it failed" without the reason is exactly the
     * dead end this screen exists to remove.
     */
    public static function sendTest($jsst_to, $jsst_from_email, $jsst_from_name) {
        if (!is_email($jsst_to)) {
            return array('ok' => false, 'message' => esc_html(__('That is not a valid e-mail address.', 'js-support-ticket')));
        }
        $jsst_error = '';
        $jsst_capture = function($jsst_wp_error) use (&$jsst_error) {
            if (is_wp_error($jsst_wp_error)) {
                $jsst_error = $jsst_wp_error->get_error_message();
            }
        };
        add_action('wp_mail_failed', $jsst_capture);

        $jsst_headers = array();
        if (is_email($jsst_from_email)) {
            $jsst_headers[] = 'From: ' . $jsst_from_name . ' <' . $jsst_from_email . '>';
        }
        $jsst_sent = wp_mail(
            $jsst_to,
            esc_html(__('JS Help Desk test message', 'js-support-ticket')),
            esc_html(__('This is a test message from the Email Health screen. If you are reading it, notifications from this site can reach this address.', 'js-support-ticket')),
            $jsst_headers
        );
        remove_action('wp_mail_failed', $jsst_capture);

        if ($jsst_error === '' && !$jsst_sent && isset($GLOBALS['phpmailer']) && !empty($GLOBALS['phpmailer']->ErrorInfo)) {
            $jsst_error = $GLOBALS['phpmailer']->ErrorInfo;
        }
        self::recordResult($jsst_sent, $jsst_to, $jsst_error);

        if ($jsst_sent) {
            return array('ok' => true, 'message' => sprintf(
                /* translators: %s: the address the test was sent to */
                esc_html(__('The test message was handed to your mail service for %s. If it does not arrive, the problem is after this site — check the service\'s own logs and the sender-domain advice above.', 'js-support-ticket')),
                esc_html($jsst_to)
            ));
        }
        return array('ok' => false, 'message' => $jsst_error !== ''
                ? sprintf(
                    /* translators: %s: the error the mail service reported */
                    esc_html(__('The message was refused: %s', 'js-support-ticket')),
                    esc_html($jsst_error)
                )
                : esc_html(__('The message was refused and no reason was reported. That usually means PHP mail() is being used and the server has no mail transport.', 'js-support-ticket')));
    }

    /**
     * What the last mailbox collections did, or nothing at all.
     *
     * The other half of e-mail health: this screen has always been able to say
     * that mail leaves the site, and never whether any arrives. The log is
     * written by the piping add-on and kept by core, so it reads back even after
     * the add-on is switched off — which is when somebody usually asks.
     */
    public static function pipingSummary() {
        if (!class_exists('JSSTpipinglog')) {
            return array();
        }
        $jsst_active = in_array('emailpiping', jssupportticket::$_active_addons);
        $jsst_summary = JSSTpipinglog::summary();
        if (!$jsst_active && $jsst_summary['state'] === 'never') {
            return array();
        }
        $jsst_summary['addon_active'] = $jsst_active;
        return $jsst_summary;
    }

    /**
     * Everything the screen needs, in one call.
     */
    public static function report($jsst_from_email, $jsst_from_name) {
        $jsst_host = wp_parse_url(home_url(), PHP_URL_HOST);
        return array(
            'provider'  => self::detectProvider(),
            'alignment' => self::checkSenderAlignment($jsst_from_email, (string) $jsst_host),
            'last'      => self::lastResult(),
            'cron'      => self::cronStatus(),
            'from'      => array('email' => $jsst_from_email, 'name' => $jsst_from_name),
            // Mail coming in, not going out. Only offered when the piping
            // add-on is active or has left a history behind, because a site
            // that never collects mail should not be shown a card about it.
            'piping'    => self::pipingSummary(),
            // The stand-alone SMTP add-on is being retired in favour of the
            // dedicated mail plugins. (Roadmap 4.0-OPS-01)
            'legacy_smtp' => in_array('smtp', jssupportticket::$_active_addons),
        );
    }

}
