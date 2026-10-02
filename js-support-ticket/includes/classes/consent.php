<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap, which deduplicates by resolved path.
 * (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTconsent')) {
    return;
}

/**
 * Retiring the MailChimp add-on. (Roadmap 5.5-SEC-02)
 *
 * The add-on is one checkbox on the registration form and one HTTP request to
 * one company's API. The roadmap's judgement is that "a one-checkbox marketing
 * add-on is not worth an independent maintenance and consent-compliance
 * lifecycle", and the second half of that sentence is the part that matters.
 * What that checkbox actually produces is a **consent**: a person saying yes,
 * at a moment, having been shown a particular sentence. Consent is a record you
 * have to be able to produce years later, and it was never being kept at all -
 * the add-on made an API call and threw the answer away. A support desk asked
 * to prove that somebody opted in had nothing whatsoever to show.
 *
 * So the checkbox stays and the add-on goes, and what replaces it is the two
 * things a checkbox like that should always have produced:
 *
 *  - **A consent record**, in this plugin, alongside the tickets - which is
 *    where the rest of that person's data already is, and therefore where the
 *    export and erasure requests already look.
 *  - **An event**, so anything can act on it. Webhooks (5.0-API-02) can deliver
 *    it, automation (5.0-AUT-01) can act on it, and a connector (5.5-CH-04) can
 *    pass it on. That is what the roadmap means by "consent-aware webhook and
 *    automation connectors": one fact, announced once, and every marketing tool
 *    on earth subscribes to it instead of each needing an add-on.
 *
 * Two **recipes** ship for the two cases people actually have. FluentCRM,
 * because it is a WordPress plugin and can simply be called. And Mailchimp,
 * because the people running the add-on today should not lose their
 * integration: the same API key and list id keep working, from here, so the
 * add-on can be switched off with nothing to reconfigure.
 *
 * As with every retirement in this programme: nothing is deleted, the add-on is
 * not switched off, and a site that ignores this keeps working exactly as it
 * does today.
 */
class JSSTconsent {

    /** Bumped when the table below changes shape. */
    const SCHEMA_VERSION = '550-SEC02';

    const OPT_SCHEMA = 'jsst_consent_schema';
    const OPT_SETTINGS = 'jsst_consent_settings';

    /** Where consent was given. */
    const SOURCE_REGISTRATION = 'registration';
    const SOURCE_TICKET = 'ticket';
    const SOURCE_MANUAL = 'manual';

    /* =====================================================================
     * Schema
     * ================================================================== */

    public static function ensureSchema() {
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION, array(
                'js_ticket_marketing_consent' => array('uid', 'email', 'name', 'granted',
                    'source', 'statement', 'created'),
            ))) {
            return;
        }
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `"
            . jssupportticket::$_db->prefix . "js_ticket_marketing_consent` (
                id int(11) NOT NULL AUTO_INCREMENT,
                uid int(11) NOT NULL DEFAULT 0,
                email varchar(190) NOT NULL DEFAULT '',
                name varchar(190) NOT NULL DEFAULT '',
                granted tinyint(1) NOT NULL DEFAULT 1,
                source varchar(20) NOT NULL DEFAULT '',
                statement text,
                created datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY jsst_email (email),
                KEY jsst_created (created)
            ) " . $jsst_charset);
        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);
    }

    /* =====================================================================
     * Settings
     * ================================================================== */

    public static function settings() {
        $jsst_stored = get_option(self::OPT_SETTINGS, array());
        if (!is_array($jsst_stored)) {
            $jsst_stored = array();
        }
        return array_merge(array(
            /* The sentence beside the checkbox. Kept because it is the thing
               the person actually agreed to, and a consent record that does
               not say what was agreed to is not a record of anything. */
            'statement' => __('Yes, send me occasional product news by e-mail. I can unsubscribe at any time.', 'js-support-ticket'),
            /* Which recipe carries the consent onward. Nothing by default -
               recording the consent and announcing it are useful on their own,
               and a desk that has not chosen a marketing tool should not have
               one chosen for it. See the override below for the one case where
               that default would break something. */
            'recipe'    => self::defaultRecipe(),
            /* FluentCRM's list, where that is the recipe. */
            'listid'    => '',
        ), $jsst_stored);
    }

    /**
     * What a desk that has never opened the Marketing screen should do.
     *
     * Nothing, except on the one site where nothing would be a regression: a
     * desk already running the MailChimp add-on with a key configured has
     * subscribers arriving today, and an upgrade that quietly stopped them
     * would break the rule every retirement in this product is written to -
     * that a site which ignores the new screen keeps working exactly as it did.
     * So on those desks the Mailchimp recipe is the default until somebody
     * chooses otherwise, and it uses the same key and list the add-on was
     * already configured with.
     *
     * Read rather than written: nothing is stored until an administrator saves
     * the screen, so this cannot strand a desk on a choice it never made.
     */
    private static function defaultRecipe() {
        return (self::mailchimpKey() !== '') ? 'mailchimp' : '';
    }

    public static function saveSettings($jsst_values) {
        $jsst_recipe = isset($jsst_values['recipe']) ? sanitize_key($jsst_values['recipe']) : '';
        if (!in_array($jsst_recipe, array('', 'fluentcrm', 'mailchimp'), true)) {
            $jsst_recipe = '';
        }
        update_option(self::OPT_SETTINGS, array(
            'statement' => mb_substr(trim(wp_strip_all_tags((string) (isset($jsst_values['statement']) ? $jsst_values['statement'] : ''))), 0, 500),
            'recipe'    => $jsst_recipe,
            'listid'    => mb_substr(trim(wp_strip_all_tags((string) (isset($jsst_values['listid']) ? $jsst_values['listid'] : ''))), 0, 190),
        ), false);
        return true;
    }

    /* =====================================================================
     * Recording
     * ================================================================== */

    /**
     * Somebody said yes, or no.
     *
     * Written first and announced second, in that order and never the other
     * way round: a subscriber that fails must not be able to lose the record
     * that the consent was given, and a record that exists before anybody acts
     * on it is a record that survives whatever the acting does.
     *
     * A withdrawal is a row too, not a deletion. "They opted out on the 4th" is
     * a fact somebody may have to prove exactly as much as the opting in.
     */
    public static function record($jsst_email, $jsst_name = '', $jsst_granted = true, $jsst_source = self::SOURCE_REGISTRATION) {
        self::ensureSchema();
        $jsst_email = strtolower(trim((string) $jsst_email));
        if ($jsst_email === '' || !is_email($jsst_email)) {
            return false;
        }
        $jsst_settings = self::settings();
        jssupportticket::$_db->insert(jssupportticket::$_db->prefix . 'js_ticket_marketing_consent', array(
            'uid'       => self::uidOf($jsst_email),
            'email'     => $jsst_email,
            'name'      => mb_substr(trim(wp_strip_all_tags((string) $jsst_name)), 0, 190),
            'granted'   => $jsst_granted ? 1 : 0,
            'source'    => (string) $jsst_source,
            /* The sentence as it stood at the moment they agreed to it, not as
               it stands today. An administrator who rewrites the wording next
               year must not silently rewrite what everybody consented to. */
            'statement' => (string) $jsst_settings['statement'],
            'created'   => current_time('mysql'),
        ));
        $jsst_id = (int) jssupportticket::$_db->insert_id;

        if (class_exists('JSSTevents')) {
            JSSTevents::emit(JSSTevents::CONSENT_RECORDED, array(
                'consent_id' => $jsst_id,
                'email'      => $jsst_email,
                'name'       => $jsst_name,
                'granted'    => $jsst_granted ? 1 : 0,
                'source'     => $jsst_source,
                'statement'  => $jsst_settings['statement'],
            ));
        }
        self::runRecipe($jsst_email, $jsst_name, $jsst_granted);
        return $jsst_id;
    }

    /** Whether this address has consented, according to its latest row. */
    public static function granted($jsst_email) {
        self::ensureSchema();
        $jsst_latest = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT granted FROM `' . jssupportticket::$_db->prefix
            . 'js_ticket_marketing_consent` WHERE email = %s ORDER BY id DESC LIMIT 1',
            strtolower(trim((string) $jsst_email))));
        return ($jsst_latest !== null && (int) $jsst_latest === 1);
    }

    /** The consent history, newest first. */
    public static function history($jsst_email = '', $jsst_limit = 100) {
        self::ensureSchema();
        $jsst_where = '';
        if ($jsst_email !== '') {
            $jsst_where = jssupportticket::$_db->prepare(' WHERE email = %s ', strtolower(trim($jsst_email)));
        }
        return jssupportticket::$_db->get_results(
            'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_marketing_consent` '
            . $jsst_where . ' ORDER BY id DESC LIMIT ' . (int) $jsst_limit);
    }

    private static function uidOf($jsst_email) {
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT id FROM `' . jssupportticket::$_wpprefixforuser . 'js_ticket_users` WHERE user_email = %s LIMIT 1',
            $jsst_email));
    }

    /* =====================================================================
     * The recipes
     * ================================================================== */

    /** Which recipes this site could actually run right now. */
    public static function recipes() {
        return array(
            '' => array(
                'label'     => __('Nothing — just record it and announce it', 'js-support-ticket'),
                'available' => true,
                'note'      => __('The consent is written down and the event is fired. Anything subscribing — a webhook, an automation rule, a connector — can carry it wherever you want it to go.', 'js-support-ticket'),
            ),
            'fluentcrm' => array(
                'label'     => __('FluentCRM', 'js-support-ticket'),
                'available' => (defined('FLUENTCRM') || function_exists('FluentCrmApi')),
                'note'      => __('Adds the person as a subscriber, with the list you name below. It is a plugin on this site, so there is no API key and nothing to go wrong at somebody else\'s end.', 'js-support-ticket'),
            ),
            'mailchimp' => array(
                'label'     => __('Mailchimp', 'js-support-ticket'),
                'available' => (self::mailchimpKey() !== ''),
                'note'      => __('Uses the API key and list id the Mailchimp add-on was already configured with, so switching that add-on off changes nothing for your subscribers. Double opt-in is honoured exactly as it was.', 'js-support-ticket'),
            ),
        );
    }

    /** Carry the consent onward, where a recipe has been chosen. */
    private static function runRecipe($jsst_email, $jsst_name, $jsst_granted) {
        if (!$jsst_granted) {
            /* Nothing is pushed on a withdrawal. Removing somebody from a list
               they may have joined by five other routes is not this plugin's
               decision to take, and the event says what happened for anybody
               whose decision it is. */
            return;
        }
        $jsst_settings = self::settings();
        if ($jsst_settings['recipe'] === 'fluentcrm') {
            self::toFluentCrm($jsst_email, $jsst_name, $jsst_settings['listid']);
        } elseif ($jsst_settings['recipe'] === 'mailchimp') {
            self::toMailchimp($jsst_email, $jsst_name);
        }
    }

    /** FluentCRM, called directly because it is a plugin on this site. */
    private static function toFluentCrm($jsst_email, $jsst_name, $jsst_listid) {
        if (!function_exists('FluentCrmApi')) {
            return false;
        }
        $jsst_parts = explode(' ', trim((string) $jsst_name), 2);
        $jsst_contact = array(
            'email'      => $jsst_email,
            'first_name' => isset($jsst_parts[0]) ? $jsst_parts[0] : '',
            'last_name'  => isset($jsst_parts[1]) ? $jsst_parts[1] : '',
            'status'     => 'subscribed',
        );
        if (trim((string) $jsst_listid) !== '') {
            $jsst_contact['lists'] = array_map('trim', explode(',', (string) $jsst_listid));
        }
        try {
            FluentCrmApi('contacts')->createOrUpdate($jsst_contact);
        } catch (Exception $jsst_e) {
            return false;
        }
        return true;
    }

    /**
     * Mailchimp, over the same API the add-on used and with the same settings.
     *
     * Deliberately identical in behaviour, including the double opt-in, so this
     * is a move rather than a change: somebody switching the add-on off gets
     * the same result from the same list with the same confirmation e-mail.
     */
    private static function toMailchimp($jsst_email, $jsst_name) {
        $jsst_apikey = self::mailchimpKey();
        $jsst_listid = (string) JSSTincluder::getJSModel('configuration')->getConfigValue('mailchimp_list_id');
        if ($jsst_apikey === '' || $jsst_listid === '') {
            return false;
        }
        $jsst_optin = (int) JSSTincluder::getJSModel('configuration')->getConfigValue('mailchimp_double_optin');
        $jsst_datacenter = preg_replace('/.*-/', '', $jsst_apikey);
        $jsst_parts = explode(' ', trim((string) $jsst_name), 2);

        $jsst_answer = wp_remote_post(
            'https://' . $jsst_datacenter . '.api.mailchimp.com/3.0/lists/' . rawurlencode($jsst_listid) . '/members',
            array(
                'timeout' => 20,
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode('user:' . $jsst_apikey),
                    'Content-Type'  => 'application/json',
                ),
                'body' => wp_json_encode(array(
                    'email_address' => $jsst_email,
                    'merge_fields'  => array(
                        'FNAME' => isset($jsst_parts[0]) ? $jsst_parts[0] : '',
                        'LNAME' => isset($jsst_parts[1]) ? $jsst_parts[1] : '',
                    ),
                    'status' => ($jsst_optin === 1) ? 'pending' : 'subscribed',
                )),
            ));
        return (!is_wp_error($jsst_answer) && (int) wp_remote_retrieve_response_code($jsst_answer) < 300);
    }

    private static function mailchimpKey() {
        if (!class_exists('JSSTincluder')) {
            return '';
        }
        return (string) JSSTincluder::getJSModel('configuration')->getConfigValue('mailchimp_api_key');
    }

    /* =====================================================================
     * The retirement report
     * ================================================================== */

    /** What this site has, and what is replacing it. */
    public static function survey() {
        self::ensureSchema();
        $jsst_settings = self::settings();
        return array(
            'addon'      => in_array('mailchimp', jssupportticket::$_active_addons),
            'configured' => (self::mailchimpKey() !== ''),
            'recorded'   => (int) jssupportticket::$_db->get_var(
                'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_marketing_consent`'),
            'granted'    => (int) jssupportticket::$_db->get_var(
                'SELECT COUNT(DISTINCT email) FROM `' . jssupportticket::$_db->prefix
                . 'js_ticket_marketing_consent` WHERE granted = 1'),
            'recipe'     => $jsst_settings['recipe'],
            'recipes'    => self::recipes(),
        );
    }

    /* =====================================================================
     * Hooks
     * ================================================================== */

    /**
     * Where the checkbox is now read.
     *
     * Core reads it rather than the add-on, so the box on the registration form
     * keeps working whether the add-on is installed or not - and a desk that
     * deactivates it loses the API call and keeps the consent, which is the
     * right way round.
     */
    public static function onRegistration($jsst_email, $jsst_name, $jsst_ticked) {
        self::record($jsst_email, $jsst_name, (bool) $jsst_ticked, self::SOURCE_REGISTRATION);
    }

    /** One consent as personal data, for the export request. */
    public static function exportFor($jsst_email) {
        $jsst_out = array();
        foreach (self::history($jsst_email, 50) AS $jsst_row) {
            $jsst_out[] = array(
                'group_id'    => 'jsst-consent',
                'group_label' => esc_html(__('Marketing consent', 'js-support-ticket')),
                'item_id'     => 'jsst-consent-' . (int) $jsst_row->id,
                'data'        => array(
                    array('name' => esc_html(__('Answer', 'js-support-ticket')),
                          'value' => ((int) $jsst_row->granted === 1)
                                        ? esc_html(__('Yes', 'js-support-ticket'))
                                        : esc_html(__('No', 'js-support-ticket'))),
                    array('name' => esc_html(__('What was asked', 'js-support-ticket')), 'value' => $jsst_row->statement),
                    array('name' => esc_html(__('Where', 'js-support-ticket')), 'value' => $jsst_row->source),
                    array('name' => esc_html(__('When', 'js-support-ticket')), 'value' => $jsst_row->created),
                ),
            );
        }
        return $jsst_out;
    }

    public static function registerHooks() {
        add_filter('jsst_privacy_export', array(__CLASS__, 'onPrivacyExport'), 10, 2);
    }

    /** Consent rows added to whatever the privacy exporter already found. */
    public static function onPrivacyExport($jsst_export, $jsst_email) {
        if (!is_array($jsst_export)) {
            return $jsst_export;
        }
        return array_merge($jsst_export, self::exportFor($jsst_email));
    }
}
