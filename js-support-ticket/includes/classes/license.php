<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the plugin bootstrap with include_once, which deduplicates by
 * resolved path - so anything reaching this file by a second spelling of the
 * same path redeclares the class and takes the site down with a fatal.
 * Returning early costs nothing and makes the file safe to include however
 * many times and by whatever route.
 */
if (class_exists('JSSTlicense')) {
    return;
}

/**
 * The client half of the licence server. (Roadmap 6.5-ECO-02)
 *
 * ---------------------------------------------------------------------------
 * WHAT CHANGED, AND WHY THIS IS NOT THE OLD CLIENT
 * ---------------------------------------------------------------------------
 *
 * The old arrangement was one key per add-on. Thirty-odd keys, each stored as
 * `transaction_key_for_js-support-ticket<slug>`, each checked separately
 * against `jshelpdesk.com/setup/index.php`. A customer with the Professional
 * tier held a drawer full of them and had no way to tell which one had lapsed.
 *
 * 5.0.0 sells nine bundles under one plan, and the licence server issues ONE
 * key that grants a list of product slugs. So this class holds one key, asks
 * one question, and gets one answer covering every bundle on the site.
 *
 * Those options are read once, on the first 5.0.0 admin load, to carry the
 * customer's key across to the new licence server - see adoptLegacyKey() - and
 * never deleted. 5.0.0 itself never calls jshelpdesk.com/setup/; that stays
 * live for sites still on 4.0.0 and earlier.
 *
 * ---------------------------------------------------------------------------
 * THE PRODUCT SLUG IS THE PLUGIN DIRECTORY NAME
 * ---------------------------------------------------------------------------
 *
 * `js-support-ticket-agents`, not `agents`. That is what the licence server's
 * catalogue holds, and it is the one identifier that cannot drift: a plugin
 * cannot report a directory name other than its own. Everything here derives
 * it rather than storing it.
 *
 * ---------------------------------------------------------------------------
 * THREE RULES THIS CLASS KEEPS
 * ---------------------------------------------------------------------------
 *
 * **It never stops the site working.** Every network call can fail, and when
 * one does the last known answer stands. A help desk does not go dark because
 * a licence server was slow.
 *
 * **A lapsed licence is told what exists.** The server reports the newest
 * version to an expired licence and withholds only the file. So the update
 * screen can say "8.2.0 is available, renew to install it" rather than going
 * quiet and looking broken, and this class passes that through rather than
 * hiding it.
 *
 * **It asks rarely.** An update check is the expensive request on the server's
 * side and there is one per site per twelve hours, cached in a transient.
 * WordPress asks for update information far more often than that.
 */
class JSSTlicense {

    /** Where the licence server lives, unless the site says otherwise. */
    /* jshelpdesk/v1 from 5.0.0 on, and permanent from then: this string ships
       in every copy of the plugin, so changing it later would strand every
       site that never updates. The server also answers wpwhub/v1, the name it
       was built with, but nothing should be sent there. */
    const SERVER = 'https://jshelpdesk.com/wp-json/jshelpdesk/v1';

    /** The key itself. One per site, covering every bundle. */
    const OPT_KEY = 'jsst_license_key';

    /** Everything the server last told us: status, expiry, what it grants. */
    const OPT_STATE = 'jsst_license_state';

    /** The short-lived token activation hands back. Cheaper than the key. */
    const OPT_TOKEN = 'jsst_license_token';

    /** The update report, cached. */
    const TRANSIENT_UPDATES = 'jsst_license_updates';

    /** How long that report stands before it is asked for again. */
    const UPDATE_TTL = 43200;

    /** The scheduled re-check. */
    const CRON = 'jsst_license_refresh';

    /** Every network call gives up after this. */
    const TIMEOUT = 15;

    /** Where a customer renews. Every notice that asks them to points here. */
    const ACCOUNT = 'https://jshelpdesk.com/my-account/licenses/';

    /** How long a site whose licence server has gone quiet is still trusted. */
    const GRACE_DAYS = 14;

    /** How far out the renewal reminder starts. */
    const EXPIRY_WARN_DAYS = 30;

    /** Inside this, the reminder can no longer be dismissed. */
    const EXPIRY_FINAL_DAYS = 7;

    /** How long a dismissed reminder stays dismissed. */
    const SNOOZE_DAYS = 7;

    /** User meta prefix holding that dismissal. */
    const META_SNOOZE = 'jsst_license_snoozed_';

    /** When a 4.0.0 key was carried across, or 'none' once there was nothing to carry. */
    const OPT_ADOPTED = 'jsst_license_adopted';

    /**
     * The licence server's signed statement that this site activated this key.
     *
     * Kept as the server sent it - {alg, kid, payload, signature} - because the
     * add-ons check the signature themselves, on this site, against the public
     * key they ship (their includes/licence-gate.php). Without one that checks
     * out for this site, this key and that add-on, an add-on stays off: that is
     * what stops a copied add-on folder working on a site nobody licensed.
     *
     * It is kept when the licence lapses, on purpose - the published promise is
     * that add-ons keep running where they are installed - and dropped when the
     * site leaves the licence: released from here, removed from the account
     * with no slot to come back to, or the licence withdrawn. (30 September 2026)
     */
    const OPT_RECEIPT = 'jsst_license_receipt';

    /**
     * Refusals that mean this site is no longer on the licence, so the receipt
     * goes. Expired and suspended are not here: those are about the term, and a
     * lapsed licence keeps its add-ons running. Neither is anything transient -
     * a busy server or a network failure never costs a site its add-ons.
     */
    const RECEIPT_DROPPED_BY = array(
        'license_not_found',
        'license_revoked',
        'no_production_slots',
        'no_staging_slots',
        'no_development_slots',
    );

    /**
     * Hooks.
     *
     * Registered from the bootstrap beside the other engines. The update
     * filters run whether or not there is a key, because "you have no licence"
     * is an answer an update screen should be able to give.
     */
    public static function registerHooks() {
        add_filter('site_transient_update_plugins', array(__CLASS__, 'injectUpdates'));
        add_filter('plugins_api', array(__CLASS__, 'pluginDetails'), 10, 3);

        /* A newer build that cannot be installed still has to be visible.
           after_plugin_row_* fires for every plugin row, including the ones
           WordPress has been told are current - which is where a lapsed
           licence's rows deliberately sit. */
        add_action('admin_init', array(__CLASS__, 'registerRowNotices'));

        /* The licence screen is the one place somebody has to go looking. A
           lapsed licence quietly withholding updates has to come to them. */
        add_action('admin_notices', array(__CLASS__, 'adminNotice'));
        add_action('admin_init', array(__CLASS__, 'maybeSnooze'));
        add_action('admin_init', array(__CLASS__, 'redirectRetired'));
        add_action('admin_init', array(__CLASS__, 'adoptLegacyKey'));

        /* The License & Add-ons page's buttons, answered without a page load. */
        add_action('wp_ajax_jsst_lp_key', array(__CLASS__, 'ajaxKey'));
        add_action('wp_ajax_jsst_lp_addon', array(__CLASS__, 'ajaxAddon'));
        add_filter('submenu_file', array(__CLASS__, 'menuHighlight'));

        add_action(self::CRON, array(__CLASS__, 'refresh'));

        if (!wp_next_scheduled(self::CRON)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'twicedaily', self::CRON);
        }

        /* A bundle switched on or off changes what this site is entitled to
           report, so the cached answer stops being true the moment it happens. */
        add_action('activated_plugin', array(__CLASS__, 'forget'));
        add_action('deactivated_plugin', array(__CLASS__, 'forget'));

        /* An update changes the version this site reports, and the cached
           report would otherwise go on offering the build just installed. */
        add_action('upgrader_process_complete', array(__CLASS__, 'forget'));
    }

    /**
     * The licence server's address.
     *
     * Overridable, and it has to be: nothing about this can be tested without
     * pointing it at a server that is not the live one. wp-config wins over the
     * filter so that a staging site cannot have its endpoint moved by a plugin.
     */
    public static function server() {
        if (defined('JSST_LICENSE_SERVER') && JSST_LICENSE_SERVER) {
            return untrailingslashit(JSST_LICENSE_SERVER);
        }
        return untrailingslashit(apply_filters('jsst_license_server', self::SERVER));
    }

    /** The stored key, or ''. */
    public static function key() {
        return (string) get_option(self::OPT_KEY, '');
    }

    /** Whether this site has been given a key at all. */
    public static function hasKey() {
        return '' !== trim(self::key());
    }

    /**
     * What the server last said.
     *
     * Always an array, so every caller can read it without checking first.
     */
    public static function state() {
        $jsst_state = get_option(self::OPT_STATE, array());
        if (!is_array($jsst_state)) {
            $jsst_state = array();
        }
        return $jsst_state + array(
            'status'     => 'unknown',
            'expires_at' => '',
            'updates'    => false,
            'products'   => array(),
            'kind'       => '',
            'plan'       => '',
            'slots'      => array(),
            'checked'    => 0,
            'failed'     => 0,
            'reason'     => '',
            'message'    => '',
            'legacy_tier' => '',
            'offer'      => array(),
        );
    }

    /**
     * The renewal offer the licence server made this carried-over customer,
     * or array() (1 October 2026). Pro is free until 'from', the end of the
     * period they paid for; from then it renews at 'price_text' instead of
     * 'regular_text' - a permanent loyalty price. Shown on the upgrade
     * screens and the Dashboard by JSSTupgradeassistant::offerNotice().
     *
     * @return array kind, price_text, regular_text, percent, from (Y-m-d), manage_url
     */
    public static function offer() {
        $jsst_offer = self::state()['offer'];
        if (!is_array($jsst_offer) || empty($jsst_offer['from']) || empty($jsst_offer['price_text']) || empty($jsst_offer['regular_text'])) {
            return array();
        }
        return $jsst_offer;
    }

    /** Only the fields the banner uses, each one plain text. */
    private static function cleanOffer($jsst_offer) {
        $jsst_from = isset($jsst_offer['from']) ? (string) $jsst_offer['from'] : '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $jsst_from)) {
            return array();
        }
        $jsst_url = isset($jsst_offer['manage_url']) ? esc_url_raw((string) $jsst_offer['manage_url']) : '';
        return array(
            'kind'         => isset($jsst_offer['kind']) ? sanitize_key((string) $jsst_offer['kind']) : 'loyalty',
            'price_text'   => isset($jsst_offer['price_text']) ? sanitize_text_field((string) $jsst_offer['price_text']) : '',
            'regular_text' => isset($jsst_offer['regular_text']) ? sanitize_text_field((string) $jsst_offer['regular_text']) : '',
            'percent'      => isset($jsst_offer['percent']) ? (int) $jsst_offer['percent'] : 0,
            'from'         => substr($jsst_from, 0, 10),
            'manage_url'   => 0 === strpos($jsst_url, 'https://') || 0 === strpos($jsst_url, 'http://localhost') ? $jsst_url : '',
        );
    }

    /** The day an offer starts, in the site's date format. */
    public static function offerDate($jsst_offer) {
        return self::niceDate(isset($jsst_offer['from']) ? $jsst_offer['from'] : '');
    }

    /**
     * The plan this licence had before 5.0.0, as the licence server recorded it
     * when the old licences were carried over: basic, standard, professional,
     * addons (bought add-on by add-on), unknown - or '' for a licence bought
     * since. (1 October 2026)
     */
    public static function legacyTier() {
        $jsst_state = self::state();
        return (string) $jsst_state['legacy_tier'];
    }

    /**
     * The old plan's name when the carried-over licence now includes all nine
     * add-ons although that plan was not Professional - the customer who got
     * everything in Pro at no extra charge, and should be told so. '' otherwise.
     */
    public static function freeUpgradeFrom() {
        $jsst_names = array(
            'basic'    => __('Basic', 'js-support-ticket'),
            'standard' => __('Standard', 'js-support-ticket'),
            'addons'   => __('individual add-ons', 'js-support-ticket'),
        );
        $jsst_tier = self::legacyTier();

        if (!isset($jsst_names[$jsst_tier]) || !self::hasKey() || !self::isActive() || !class_exists('JSSTbundle')) {
            return '';
        }

        foreach (array_keys(JSSTbundle::bundles()) as $jsst_bundle) {
            if (!self::grants($jsst_bundle)) {
                return '';
            }
        }

        return $jsst_names[$jsst_tier];
    }

    /**
     * Until when the carried-over upgrade is free: the end of the period the
     * customer already paid for. From the next renewal they pay the 5.0 price
     * (decided 1 October 2026), so every place that says "at no extra charge"
     * says until when. '' when the licence has no end date.
     */
    public static function freeUpgradeUntil() {
        return self::niceDate(self::state()['expires_at']);
    }

    /** Whether the licence is current, as far as this site last knew. */
    public static function isActive() {
        $jsst_state = self::state();
        return 'active' === $jsst_state['status'];
    }

    /**
     * Is this bundle covered?
     *
     * Asked of what the server granted, not of what is installed. A bundle can
     * be installed and unlicensed, which is the case this answers.
     */
    public static function grants($jsst_slug) {
        $jsst_state = self::state();
        return in_array(self::productSlug($jsst_slug), (array) $jsst_state['products'], true);
    }

    /** Bundle slug to the product slug the licence server knows. */
    public static function productSlug($jsst_slug) {
        return 'js-support-ticket-' . $jsst_slug;
    }

    /**
     * Every bundle installed on this site, as product slug to version.
     *
     * Read from the plugin files rather than from a stored list, for the same
     * reason JSSTbundle reads the directory: what a site is running is a fact
     * about the filesystem, and a manifest can be out of date the moment a
     * release lands.
     *
     * Installed, not active. A bundle that is present but switched off is
     * still a thing WordPress will offer an update for, and withholding that
     * would make the Plugins screen lie.
     */
    public static function installed() {
        $jsst_found = array();

        if (!class_exists('JSSTbundle')) {
            return $jsst_found;
        }

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach (array_keys(JSSTbundle::bundles()) as $jsst_slug) {
            $jsst_file = WP_PLUGIN_DIR . '/js-support-ticket-' . $jsst_slug . '/js-support-ticket-' . $jsst_slug . '.php';

            if (!file_exists($jsst_file)) {
                continue;
            }

            $jsst_data = get_plugin_data($jsst_file, false, false);
            $jsst_found[self::productSlug($jsst_slug)] = isset($jsst_data['Version']) ? (string) $jsst_data['Version'] : '0.0.0';
        }

        return $jsst_found;
    }

    /**
     * This site's address, exactly as WordPress reports it.
     *
     * Sent unaltered. The server normalises it into an identity and a hash,
     * and two different spellings of the same site must not become two
     * activations - so the one thing this must not do is tidy it up first.
     */
    public static function siteUrl() {
        return (string) get_site_url();
    }

    /**
     * The help desk's own version, as a number the server can compare.
     *
     * Read from the plugin header rather than from
     * `jssupportticket::$_currentversion`, which is a schema code - '500' -
     * and not a version. The server compares `core_version` against a
     * release's `requires_core` floor, and '500' against '5.0' compares as
     * enormously newer, which would wave through exactly the build this floor
     * exists to withhold.
     *
     * Static because get_plugin_data() reads and parses a file, and this is
     * asked for on every update check.
     */
    public static function coreVersion() {
        static $jsst_version = null;

        if (null !== $jsst_version) {
            return $jsst_version;
        }

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $jsst_file = WP_PLUGIN_DIR . '/js-support-ticket/js-support-ticket.php';
        $jsst_data = file_exists($jsst_file) ? get_plugin_data($jsst_file, false, false) : array();
        $jsst_version = isset($jsst_data['Version']) ? (string) $jsst_data['Version'] : '0.0.0';

        return $jsst_version;
    }

    /**
     * What this site is, for the activation record.
     *
     * Enough to answer "which of my sites is this?" on the customer's own
     * licence page, and nothing that is not already in a user agent.
     */
    public static function environment() {
        return array(
            'wp_version'     => get_bloginfo('version'),
            'php_version'    => PHP_VERSION,
            'plugin_version' => self::coreVersion(),
            'locale'         => get_locale(),
            'multisite'      => is_multisite() ? '1' : '0',
        );
    }

    /**
     * Whether this site should ask to be recorded as staging.
     *
     * Only ever a suggestion: the server decides, and a non-routable address
     * is development whatever anybody claims. Sending it means a customer's
     * staging copy does not spend a production slot by accident, which is the
     * single most common support ticket a site-limited licence produces.
     */
    public static function declaredKind() {
        $jsst_kind = '';

        if (defined('WP_ENVIRONMENT_TYPE')) {
            $jsst_env = (string) WP_ENVIRONMENT_TYPE;
            if (in_array($jsst_env, array('staging', 'development', 'local'), true)) {
                $jsst_kind = 'staging';
            }
        } elseif (function_exists('wp_get_environment_type')) {
            $jsst_env = wp_get_environment_type();
            if (in_array($jsst_env, array('staging', 'development', 'local'), true)) {
                $jsst_kind = 'staging';
            }
        }

        return apply_filters('jsst_license_kind', $jsst_kind);
    }

    /**
     * One request to the licence server.
     *
     * Returns the decoded body on success, or a WP_Error. Never throws, never
     * echoes, and never lets a slow server hold a page open longer than
     * TIMEOUT - the whole point being that a licence check is not allowed to
     * be the reason somebody's admin screen hangs.
     *
     * @param string $jsst_path Endpoint, e.g. '/activate'.
     * @param array  $jsst_body Request body.
     * @return array|WP_Error
     */
    private static function post($jsst_path, $jsst_body) {
        $jsst_response = wp_remote_post(
            self::server() . $jsst_path,
            array(
                'timeout'     => self::TIMEOUT,
                'redirection' => 2,
                'headers'     => array(
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ),
                'body'        => wp_json_encode($jsst_body),
            )
        );

        if (is_wp_error($jsst_response)) {
            self::noteFailure();

            return $jsst_response;
        }

        $jsst_code = (int) wp_remote_retrieve_response_code($jsst_response);
        $jsst_data = json_decode((string) wp_remote_retrieve_body($jsst_response), true);

        if (!is_array($jsst_data)) {
            return new WP_Error(
                'jsst_license_unreadable',
                /* translators: %d: HTTP status code */
                sprintf(__('The license server answered %d with something that was not JSON.', 'js-support-ticket'), $jsst_code)
            );
        }

        /*
         * A refusal is an answer, not a failure. 403 "no slots left" and 404
         * "no such key" are both things the customer needs to read, so they
         * come back as data. Only a 5xx is treated as the server being broken.
         */
        if ($jsst_code >= 500) {
            self::noteFailure();

            return new WP_Error(
                'jsst_license_server_error',
                /* translators: %d: HTTP status code */
                sprintf(__('The license server answered %d. Nothing has changed here; it will be asked again later.', 'js-support-ticket'), $jsst_code)
            );
        }

        /* Anything the server answered - including a refusal - means it is
           reachable, so whatever run of failures there was has ended. */
        self::clearFailure();

        $jsst_data['http_status'] = $jsst_code;

        return $jsst_data;
    }

    /**
     * Claim a slot for this site.
     *
     * @param string $jsst_key The key the customer pasted in.
     * @return true|WP_Error
     */
    public static function activate($jsst_key) {
        $jsst_key = trim((string) $jsst_key);

        if ('' === $jsst_key) {
            return new WP_Error('jsst_license_empty', __('Enter your license key.', 'js-support-ticket'));
        }

        $jsst_body = array(
            'license_key' => $jsst_key,
            'site_url'    => self::siteUrl(),
            'products'    => array_keys(self::installed()),
            'environment' => self::environment(),
        );

        $jsst_kind = self::declaredKind();

        if ('' !== $jsst_kind) {
            $jsst_body['kind'] = $jsst_kind;
        }

        $jsst_result = self::post('/activate', $jsst_body);

        if (is_wp_error($jsst_result)) {
            return $jsst_result;
        }

        if (empty($jsst_result['ok'])) {
            /* The key is still stored on most refusals. "No slots left" and
               "expired" are about this site or this term, not about the key
               being wrong, and making the customer paste it again to go and
               free a slot somewhere else would be unkind.

               A key the server has never heard of is the exception. That is a
               typo, and a typo must not be allowed to evict the working key
               this site already had - the customer would then have two things
               to fix instead of one. */
            $jsst_unknown = isset($jsst_result['reason']) && 'license_not_found' === $jsst_result['reason'];

            /* Nor may any refused key evict one that is WORKING here. Typing an
               old expired or revoked key over the current one used to replace
               it, and the site lost its updates over a paste. (Installation
               test, 26 September 2026.) */
            if (self::hasKey() && ($jsst_unknown || self::isActive())) {
                /* Nothing is written. The licence this site already holds is
                   untouched - still active, still granting its add-ons - and
                   the controller puts the refusal on screen as a notice. The
                   customer sees "that key is not on file" above a licence that
                   is plainly still working, which is the truth. */
                return new WP_Error(
                    'jsst_license_refused',
                    self::reasonText($jsst_result),
                    array('reason' => $jsst_result['reason'])
                );
            }

            update_option(self::OPT_KEY, $jsst_key, false);
            self::remember($jsst_result);

            return new WP_Error(
                'jsst_license_refused',
                self::reasonText($jsst_result),
                array('reason' => isset($jsst_result['reason']) ? $jsst_result['reason'] : '')
            );
        }

        update_option(self::OPT_KEY, $jsst_key, false);
        self::remember($jsst_result);
        delete_transient(self::TRANSIENT_UPDATES);

        return true;
    }

    /**
     * The activation token, if it has life left in it; '' otherwise.
     *
     * A minute's margin, because it is about to be sent to the server and
     * must not die on the way.
     */
    private static function token() {
        $jsst_token = get_option(self::OPT_TOKEN, array());

        if (!is_array($jsst_token) || empty($jsst_token['token'])) {
            return '';
        }

        return (int) $jsst_token['expires'] > time() + MINUTE_IN_SECONDS ? (string) $jsst_token['token'] : '';
    }

    /**
     * Fetch, check and install one bundle this licence grants and the site lacks.
     *
     * Replaces the old three-step installer, which sent the key to
     * jshelpdesk.com/setup/, received one zip holding every add-on asked for,
     * and unpacked it straight into wp-content/plugins with no check on what
     * had arrived. A key issued by the new licence server is not in the tables
     * /setup/ reads, so for every 5.0.0 customer that installer simply said no.
     *
     * Here, one add-on at a time:
     *   - the file is asked for with the activation token, never the key: the
     *     server hands first installs only to a site that holds a slot;
     *   - what arrives is checked against the SHA-256 the server published
     *     before anything is unpacked;
     *   - WordPress's own Plugin_Upgrader does the unpacking, so filesystem
     *     credentials, maintenance mode and the upgrader hooks all behave as
     *     they do for any plugin;
     *   - it is then activated, which runs the bundle's own set-up.
     *
     * @param string $jsst_product Product slug, e.g. js-support-ticket-reporting.
     * @return array|WP_Error name, version, activated, message.
     */
    public static function install($jsst_product) {
        $jsst_product = sanitize_key((string) $jsst_product);
        $jsst_slug    = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_product);
        $jsst_name    = self::bundleName($jsst_slug);
        $jsst_file    = $jsst_product . '/' . $jsst_product . '.php';

        if (!class_exists('JSSTbundle') || false === JSSTbundle::bundle($jsst_slug) || 0 !== strpos($jsst_product, 'js-support-ticket-')) {
            return new WP_Error('jsst_install_unknown', __('That is not a JS Help Desk add-on.', 'js-support-ticket'));
        }

        if (file_exists(WP_PLUGIN_DIR . '/' . $jsst_product)) {
            return new WP_Error('jsst_install_present', sprintf(
                /* translators: %s: add-on name */
                __('%s is already on this site.', 'js-support-ticket'),
                $jsst_name
            ));
        }

        if (!self::hasKey()) {
            return new WP_Error('jsst_license_empty', __('Add your license key on the License screen first.', 'js-support-ticket'));
        }

        /* Asked twice at most. A token can expire between the page loading and
           the button being pressed; re-activating mints a fresh one, and a
           second refusal is then a real answer rather than a stale token. */
        $jsst_result = null;

        for ($jsst_try = 0; $jsst_try < 2; $jsst_try++) {
            if ('' === self::token()) {
                $jsst_refreshed = self::refresh();

                if (is_wp_error($jsst_refreshed)) {
                    return $jsst_refreshed;
                }
            }

            if ('' === self::token()) {
                return new WP_Error('jsst_license_inactive', '' !== (string) self::state()['message']
                    ? (string) self::state()['message']
                    : __('This site is not activated on your license. Use Check again on the License screen.', 'js-support-ticket'));
            }

            $jsst_result = self::post('/install', array(
                'token'        => self::token(),
                'site_url'     => self::siteUrl(),
                'product'      => $jsst_product,
                'channel'      => 'stable',
                'core_version' => self::coreVersion(),
            ));

            if (is_wp_error($jsst_result)) {
                return $jsst_result;
            }

            if (empty($jsst_result['ok']) && isset($jsst_result['reason']) && 'invalid_token' === $jsst_result['reason']) {
                delete_option(self::OPT_TOKEN);
                continue;
            }

            break;
        }

        if (empty($jsst_result['ok'])) {
            return new WP_Error('jsst_install_refused', self::reasonText($jsst_result), array(
                'reason' => isset($jsst_result['reason']) ? (string) $jsst_result['reason'] : '',
            ));
        }

        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $jsst_zip = download_url((string) $jsst_result['package'], 300);

        if (is_wp_error($jsst_zip)) {
            return new WP_Error('jsst_install_download', sprintf(
                /* translators: 1: add-on name, 2: error message */
                __('%1$s could not be downloaded: %2$s', 'js-support-ticket'),
                $jsst_name,
                $jsst_zip->get_error_message()
            ));
        }

        $jsst_expected = isset($jsst_result['sha256']) ? strtolower((string) $jsst_result['sha256']) : '';

        if ('' !== $jsst_expected && !hash_equals($jsst_expected, (string) hash_file('sha256', $jsst_zip))) {
            wp_delete_file($jsst_zip);

            return new WP_Error('jsst_install_checksum', sprintf(
                /* translators: %s: add-on name */
                __('%s arrived damaged - it does not match the checksum the license server published - so it was not installed. Try again in a few minutes.', 'js-support-ticket'),
                $jsst_name
            ));
        }

        $jsst_skin     = new Automatic_Upgrader_Skin();
        $jsst_upgrader = new Plugin_Upgrader($jsst_skin);
        $jsst_done     = $jsst_upgrader->install($jsst_zip, array('clear_update_cache' => true));

        /* Given a local file, the upgrader leaves it where it is. */
        if (file_exists($jsst_zip)) {
            wp_delete_file($jsst_zip);
        }

        if (is_wp_error($jsst_done)) {
            return new WP_Error('jsst_install_failed', sprintf(
                /* translators: 1: add-on name, 2: error message */
                __('%1$s could not be installed: %2$s', 'js-support-ticket'),
                $jsst_name,
                $jsst_done->get_error_message()
            ));
        }

        if (true !== $jsst_done || !file_exists(WP_PLUGIN_DIR . '/' . $jsst_file)) {
            $jsst_errors = $jsst_skin->get_errors();

            return new WP_Error('jsst_install_failed', sprintf(
                /* translators: 1: add-on name, 2: error message */
                __('%1$s could not be installed: %2$s', 'js-support-ticket'),
                $jsst_name,
                (is_wp_error($jsst_errors) && $jsst_errors->has_errors())
                    ? $jsst_errors->get_error_message()
                    : __('WordPress could not write to the plugins folder. Download the add-on from your account on jshelpdesk.com and upload it under Plugins > Add New.', 'js-support-ticket')
            ));
        }

        self::forget();

        $jsst_active = activate_plugin($jsst_file);

        return array(
            'name'      => $jsst_name,
            'version'   => isset($jsst_result['version']) ? (string) $jsst_result['version'] : '',
            'activated' => !is_wp_error($jsst_active),
            'message'   => is_wp_error($jsst_active) ? $jsst_active->get_error_message() : '',
        );
    }

    /**
     * Give the slot back.
     *
     * The key is kept. Deactivating is about this site, and a customer moving
     * a licence to a new domain should not have to go and find the key again.
     *
     * @return true|WP_Error
     */
    public static function deactivate() {
        if (!self::hasKey()) {
            return new WP_Error('jsst_license_empty', __('There is no license key on this site.', 'js-support-ticket'));
        }

        $jsst_result = self::post(
            '/deactivate',
            array(
                'license_key' => self::key(),
                'site_url'    => self::siteUrl(),
            )
        );

        /*
         * A failure here still clears the local state, deliberately. The site
         * the customer is standing on is the one they have decided to stop
         * using; leaving it looking activated because a request timed out
         * means the next thing they try is to paste the key somewhere else and
         * be told there are no slots. The server reconciles on the next
         * activation, and an orphaned slot can be released from the account
         * page - which is exactly what that button on /my-account/ is for.
         */
        delete_option(self::OPT_TOKEN);
        /* Released means the add-ons stop here too. Otherwise a key could be
           activated, released, and activated somewhere else, over and over,
           with every site it ever touched still running its add-ons. */
        delete_option(self::OPT_RECEIPT);
        delete_transient(self::TRANSIENT_UPDATES);
        update_option(self::OPT_STATE, array('status' => 'inactive', 'checked' => time()), false);

        if (is_wp_error($jsst_result)) {
            return $jsst_result;
        }

        return true;
    }

    /**
     * Ask the server where things stand, and cache what it says.
     *
     * Runs on the scheduled event. Also the thing to call after anything that
     * might have changed the answer.
     *
     * @return true|WP_Error
     */
    public static function refresh() {
        if (!self::hasKey()) {
            return new WP_Error('jsst_license_empty', __('There is no license key on this site.', 'js-support-ticket'));
        }

        /* Re-activating is the honest request here: it reports the products
           this site is actually running, refreshes the token, and is a no-op on
           the server when the site is already on the licence. */
        $jsst_result = self::post(
            '/activate',
            array(
                'license_key' => self::key(),
                'site_url'    => self::siteUrl(),
                'products'    => array_keys(self::installed()),
                'environment' => self::environment(),
            )
        );

        if (is_wp_error($jsst_result)) {
            return $jsst_result;
        }

        self::remember($jsst_result);

        return true;
    }

    /**
     * Keep what the server said.
     *
     * One place, so every caller stores the same shape and the admin screen
     * only ever has to read one option.
     *
     * @param array $jsst_result Decoded response.
     */
    private static function remember($jsst_result) {
        $jsst_license = isset($jsst_result['license']) && is_array($jsst_result['license'])
            ? $jsst_result['license']
            : array();

        $jsst_ok = !empty($jsst_result['ok']);

        $jsst_state = array(
            'status'     => isset($jsst_license['status']) ? (string) $jsst_license['status'] : 'unknown',
            'expires_at' => isset($jsst_license['expires_at']) ? (string) $jsst_license['expires_at'] : '',
            'updates'    => !empty($jsst_license['updates']),
            'products'   => isset($jsst_result['products']) ? (array) $jsst_result['products'] : array(),
            'kind'       => isset($jsst_result['activation']['kind']) ? (string) $jsst_result['activation']['kind'] : '',
            'plan'       => isset($jsst_license['plan']) ? (string) $jsst_license['plan'] : '',
            'slots'      => isset($jsst_result['slots']) && is_array($jsst_result['slots']) ? $jsst_result['slots'] : array(),
            'checked'    => time(),
            'reason'     => isset($jsst_result['reason']) ? (string) $jsst_result['reason'] : '',
            /* reasonText() always returns something, because a refusal with no
               reason still has to say so. On a success there is nothing to
               report, and storing its fallback would leave the licence screen
               announcing a refusal underneath an active licence. */
            'message'    => $jsst_ok ? '' : self::reasonText($jsst_result),
            /* The pre-5.0.0 plan, sent by the server for a carried-over
               licence (legacyTier()). Kept from before when an answer omits it. */
            'legacy_tier' => isset($jsst_license['legacy_tier']) ? sanitize_key((string) $jsst_license['legacy_tier']) : '',
            // The loyalty renewal price for a carried-over licence (offer()).
            'offer'      => isset($jsst_license['offer']) && is_array($jsst_license['offer']) ? self::cleanOffer($jsst_license['offer']) : array(),
        );

        /* A refusal carries no licence block, and overwriting a known-good
           product list with an empty one would switch every bundle off over a
           network hiccup. Keep what was last known and record the refusal.

           The same goes for the plan and the slot counts: an older server that
           sends neither is not telling us they are gone. */
        $jsst_previous = self::state();

        if (!$jsst_ok && array() === $jsst_state['products']) {
            $jsst_state['products'] = (array) $jsst_previous['products'];
        }

        if ('' === $jsst_state['plan']) {
            $jsst_state['plan'] = (string) $jsst_previous['plan'];
        }

        if (array() === $jsst_state['slots']) {
            $jsst_state['slots'] = (array) $jsst_previous['slots'];
        }

        /* Only a refusal keeps the old value: a success always says it when
           there is one, so a success without it is a licence bought since -
           a newer key replacing a carried-over one must not inherit its plan. */
        if (!$jsst_ok && '' === $jsst_state['legacy_tier']) {
            $jsst_state['legacy_tier'] = (string) $jsst_previous['legacy_tier'];
        }
        if (!$jsst_ok && array() === $jsst_state['offer']) {
            $jsst_state['offer'] = (array) $jsst_previous['offer'];
        }

        update_option(self::OPT_STATE, $jsst_state, false);

        /* The signed statement, kept for the add-ons to check (OPT_RECEIPT). A
           success replaces it, so a plan that grew brings its new add-ons with
           it; a refusal that says this site has left the licence takes it away;
           every other refusal - expired, suspended, busy - leaves it alone. */
        if ($jsst_ok && isset($jsst_result['signed']) && is_array($jsst_result['signed'])
            && !empty($jsst_result['signed']['payload']) && !empty($jsst_result['signed']['signature'])) {
            update_option(self::OPT_RECEIPT, array(
                'alg'       => isset($jsst_result['signed']['alg']) ? (string) $jsst_result['signed']['alg'] : '',
                'kid'       => isset($jsst_result['signed']['kid']) ? (string) $jsst_result['signed']['kid'] : '',
                'payload'   => (string) $jsst_result['signed']['payload'],
                'signature' => (string) $jsst_result['signed']['signature'],
                'received'  => time(),
            ), false);
        } elseif (!$jsst_ok && in_array($jsst_state['reason'], self::RECEIPT_DROPPED_BY, true)) {
            delete_option(self::OPT_RECEIPT);
        }

        if (!empty($jsst_result['token'])) {
            update_option(
                self::OPT_TOKEN,
                array(
                    'token'   => (string) $jsst_result['token'],
                    'expires' => time() + (int) (isset($jsst_result['token_ttl']) ? $jsst_result['token_ttl'] : 0),
                ),
                false
            );
        }
    }

    /**
     * A refusal, in words a customer can act on.
     *
     * The server sends a reason code and usually a message. The codes are
     * translated here rather than shown raw, because "no_production_slots" is
     * not something to put in front of somebody.
     *
     * @param array $jsst_result Decoded response.
     */
    private static function reasonText($jsst_result) {
        $jsst_reason = isset($jsst_result['reason']) ? (string) $jsst_result['reason'] : '';

        $jsst_known = array(
            'license_not_found'        => __('That license key is not on file. Check it against the one in your account.', 'js-support-ticket'),
            'license_expired'          => __('This license has expired. Your add-ons keep running; renew to receive updates again and to add new sites.', 'js-support-ticket'),
            'license_revoked'          => __('This license has been withdrawn. Get in touch and we will sort it out.', 'js-support-ticket'),
            'license_suspended'        => __('This license is suspended. Get in touch and we will sort it out.', 'js-support-ticket'),
            'no_production_slots'      => __('Every site on this license is in use. Release one from the Licenses tab of your account, or move up a plan.', 'js-support-ticket'),
            'no_staging_slots'         => __('Every staging slot on this license is in use. Release one from the Licenses tab of your account.', 'js-support-ticket'),
            'no_development_slots'     => __('Every development slot on this license is in use. Release one from the Licenses tab of your account.', 'js-support-ticket'),
            'invalid_site'             => __('The license server could not read this site address.', 'js-support-ticket'),
            'invalid_token'            => __('This site needs activating again. Use Check again on the License screen.', 'js-support-ticket'),
            'not_entitled'             => __('This add-on is not included in your license.', 'js-support-ticket'),
            'not_published'            => __('This add-on is not available to download yet. Try again later, or contact support.', 'js-support-ticket'),
            'invalid_product'          => __('The license server did not recognise that add-on.', 'js-support-ticket'),
            'rate_limited'             => __('The license server is busy. Try again in a minute.', 'js-support-ticket'),
        );

        if ('requires_core' === $jsst_reason && !empty($jsst_result['requires_core'])) {
            return sprintf(
                /* translators: %s: help desk version needed */
                __('This add-on needs JS Help Desk %s or newer. Update the help desk first, then install it.', 'js-support-ticket'),
                (string) $jsst_result['requires_core']
            );
        }

        if (isset($jsst_known[$jsst_reason])) {
            return $jsst_known[$jsst_reason];
        }

        if (!empty($jsst_result['message'])) {
            return (string) $jsst_result['message'];
        }

        return __('The license server refused, and did not say why.', 'js-support-ticket');
    }

    /**
     * The update report, asked for at most twice a day.
     *
     * Cached whether or not it found anything: a site with no updates must not
     * ask again on the next page load, and a site with no licence must not ask
     * at all.
     *
     * @param bool $jsst_force Ignore the cache.
     * @return array
     */
    public static function updates($jsst_force = false) {
        $jsst_installed = self::installed();

        if (array() === $jsst_installed) {
            return array();
        }

        if (!$jsst_force) {
            $jsst_cached = get_transient(self::TRANSIENT_UPDATES);

            /* The report is kept for twelve hours; the download tickets in it
               live for fifteen minutes. A report whose tickets have died still
               says "update now", and WordPress then fails the download with a
               message that points at nothing. So once a ticket in it is dead,
               or about to be, the report is asked for again - which happens
               only while an installable update is actually waiting. */
            if (is_array($jsst_cached) && !self::ticketsExpired($jsst_cached)) {
                return $jsst_cached;
            }
        }

        if (!self::hasKey()) {
            /* No key is a settled answer, and caching it is what stops an
               unlicensed site asking on every admin page load. */
            set_transient(self::TRANSIENT_UPDATES, array(), self::UPDATE_TTL);
            return array();
        }

        $jsst_body = array(
            'site_url'     => self::siteUrl(),
            'products'     => $jsst_installed,
            'channel'      => 'stable',
            'core_version' => self::coreVersion(),
        );

        /* The token is cheaper for the server to verify than the key, and it
           is what activation handed us for exactly this. Fall back to the key
           when it has expired, which re-mints one. */
        $jsst_token = get_option(self::OPT_TOKEN, array());

        if (is_array($jsst_token) && !empty($jsst_token['token']) && (int) $jsst_token['expires'] > time()) {
            $jsst_body['token'] = (string) $jsst_token['token'];
        } else {
            $jsst_body['license_key'] = self::key();
        }

        $jsst_result = self::post('/update-check', $jsst_body);

        if (is_wp_error($jsst_result)) {
            /* Cache the failure briefly. A licence server that is down must not
               be asked again by every admin page load, and it must not stop
               WordPress reporting updates for everything else either. */
            set_transient(self::TRANSIENT_UPDATES, array(), 15 * MINUTE_IN_SECONDS);
            return array();
        }

        $jsst_updates = isset($jsst_result['updates']) && is_array($jsst_result['updates'])
            ? $jsst_result['updates']
            : array();

        if (isset($jsst_result['license']) && is_array($jsst_result['license'])) {
            $jsst_state = self::state();
            $jsst_state['status']     = isset($jsst_result['license']['status']) ? (string) $jsst_result['license']['status'] : $jsst_state['status'];
            $jsst_state['expires_at'] = isset($jsst_result['license']['expires_at']) ? (string) $jsst_result['license']['expires_at'] : $jsst_state['expires_at'];
            $jsst_state['updates']    = !empty($jsst_result['license']['updates']);
            $jsst_state['checked']    = time();
            update_option(self::OPT_STATE, $jsst_state, false);
        }

        set_transient(self::TRANSIENT_UPDATES, $jsst_updates, self::UPDATE_TTL);

        return $jsst_updates;
    }

    /** Throw away the cached answer, so the next question is asked for real. */
    public static function forget() {
        delete_transient(self::TRANSIENT_UPDATES);
        self::$jsst_now = null;
    }

    /**
     * Put our updates into the list WordPress is about to show.
     *
     * WordPress builds this transient often. `updates()` is cached, so being
     * called often is cheap; what is not allowed is turning every build of it
     * into an HTTP request.
     *
     * @param mixed $jsst_transient The update_plugins transient.
     * @return mixed
     */
    public static function injectUpdates($jsst_transient) {
        if (!is_object($jsst_transient)) {
            return $jsst_transient;
        }

        $jsst_updates = self::updates();

        if (array() === $jsst_updates) {
            return $jsst_transient;
        }

        foreach ($jsst_updates as $jsst_product => $jsst_entry) {
            $jsst_slug = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', (string) $jsst_product);
            $jsst_file = 'js-support-ticket-' . $jsst_slug . '/js-support-ticket-' . $jsst_slug . '.php';

            if (!file_exists(WP_PLUGIN_DIR . '/' . $jsst_file)) {
                continue;
            }

            $jsst_item = (object) array(
                'id'            => $jsst_product,
                'slug'          => $jsst_product,
                'plugin'        => $jsst_file,
                'new_version'   => isset($jsst_entry['version']) ? (string) $jsst_entry['version'] : '',
                'url'           => 'https://jshelpdesk.com/add-ons/' . $jsst_slug . '/',
                'package'       => isset($jsst_entry['package']) ? (string) $jsst_entry['package'] : '',
                'requires'      => isset($jsst_entry['requires_wp']) ? (string) $jsst_entry['requires_wp'] : '',
                'requires_php'  => isset($jsst_entry['requires_php']) ? (string) $jsst_entry['requires_php'] : '',
                'tested'        => self::testedFor(isset($jsst_entry['tested_wp']) ? (string) $jsst_entry['tested_wp'] : ''),
            );

            /* Shown under the plugin on Dashboard > Updates, where every other
               update looks the same as this one. */
            if (!empty($jsst_entry['security'])) {
                $jsst_item->upgrade_notice = __('Security update. Install it as soon as you can.', 'js-support-ticket');
            }

            if (empty($jsst_entry['update_available']) || !self::stillNewer((string) $jsst_product, $jsst_entry)) {
                /* Up to date. Saying so explicitly is what stops WordPress
                   offering the version already installed. */
                $jsst_transient->no_update[$jsst_file] = $jsst_item;
                continue;
            }

            /*
             * There IS something newer but no file came with it: the licence
             * has lapsed, or the build needs a newer help desk than this site
             * is running. Reported rather than hidden, with no package, so the
             * Plugins screen says a version exists and the row cannot be
             * updated - which is the truth, and better than going quiet.
             */
            if ('' === $jsst_item->package) {
                $jsst_transient->no_update[$jsst_file] = $jsst_item;
                continue;
            }

            $jsst_transient->response[$jsst_file] = $jsst_item;
        }

        return $jsst_transient;
    }

    /**
     * The "View details" panel.
     *
     * Without this WordPress asks wordpress.org about a plugin it has never
     * heard of and shows the customer an error where the changelog should be.
     *
     * @param mixed  $jsst_result The default result.
     * @param string $jsst_action What was asked.
     * @param object $jsst_args   Arguments, including the slug.
     * @return mixed
     */
    public static function pluginDetails($jsst_result, $jsst_action, $jsst_args) {
        if ('plugin_information' !== $jsst_action || empty($jsst_args->slug)) {
            return $jsst_result;
        }

        $jsst_product = (string) $jsst_args->slug;

        if (0 !== strpos($jsst_product, 'js-support-ticket-')) {
            return $jsst_result;
        }

        $jsst_updates = self::updates();

        if (!isset($jsst_updates[$jsst_product])) {
            return $jsst_result;
        }

        $jsst_entry = $jsst_updates[$jsst_product];
        $jsst_slug  = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_product);
        $jsst_meta  = class_exists('JSSTbundle') ? JSSTbundle::bundle($jsst_slug) : false;

        return (object) array(
            'name'          => $jsst_meta ? 'JS Help Desk ' . $jsst_meta['label'] : $jsst_product,
            'slug'          => $jsst_product,
            'version'       => isset($jsst_entry['version']) ? (string) $jsst_entry['version'] : '',
            'requires'      => isset($jsst_entry['requires_wp']) ? (string) $jsst_entry['requires_wp'] : '',
            'requires_php'  => isset($jsst_entry['requires_php']) ? (string) $jsst_entry['requires_php'] : '',
            'tested'        => self::testedFor(isset($jsst_entry['tested_wp']) ? (string) $jsst_entry['tested_wp'] : ''),
            'last_updated'  => isset($jsst_entry['released_at']) ? (string) $jsst_entry['released_at'] : '',
            'homepage'      => 'https://jshelpdesk.com/add-ons/' . $jsst_slug . '/',
            'download_link' => isset($jsst_entry['package']) ? (string) $jsst_entry['package'] : '',
            'sections'      => array(
                'description' => $jsst_meta ? esc_html($jsst_meta['summary']) : '',
                'changelog'   => isset($jsst_entry['changelog']) ? wp_kses_post($jsst_entry['changelog']) : '',
            ),
        );
    }

    /**
     * One row notice per bundle whose newer build is being withheld.
     *
     * Registered on admin_init rather than at hook time because the plugin
     * file names are only known once the update report has been read, and
     * reading it during registerHooks() would put an HTTP request on the front
     * end of every request on the site.
     */
    public static function registerRowNotices() {
        foreach (self::withheld() as $jsst_file => $jsst_entry) {
            add_action('after_plugin_row_' . $jsst_file, array(__CLASS__, 'rowNotice'), 10, 3);
        }

        foreach (self::securityPending() as $jsst_file => $jsst_fix) {
            if ('' === $jsst_fix['why']) {
                add_action('in_plugin_update_message-' . $jsst_file, array(__CLASS__, 'securityRowMessage'), 10, 2);
            }
        }
    }

    /**
     * Bundles where something newer exists that this site may not have.
     *
     * Two reasons, and they are different sentences to the customer: the
     * licence has lapsed, or the build wants a newer help desk than this site
     * is running. Both are cases where going quiet would look like the product
     * was finished rather than that the customer needs to do something.
     *
     * @return array file => entry
     */
    public static function withheld() {
        $jsst_withheld = array();

        foreach (self::updates() as $jsst_product => $jsst_entry) {
            if (empty($jsst_entry['update_available']) || !empty($jsst_entry['package'])
                || !self::stillNewer((string) $jsst_product, $jsst_entry)) {
                continue;
            }

            $jsst_slug = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', (string) $jsst_product);
            $jsst_file = 'js-support-ticket-' . $jsst_slug . '/js-support-ticket-' . $jsst_slug . '.php';

            if (file_exists(WP_PLUGIN_DIR . '/' . $jsst_file)) {
                $jsst_withheld[$jsst_file] = $jsst_entry;
            }
        }

        return $jsst_withheld;
    }

    /**
     * The row itself.
     *
     * @param string $jsst_file   Plugin file.
     * @param array  $jsst_data   Plugin header data.
     * @param string $jsst_status Plugin status.
     */
    public static function rowNotice($jsst_file, $jsst_data = array(), $jsst_status = '') {
        $jsst_withheld = self::withheld();

        if (!isset($jsst_withheld[$jsst_file])) {
            return;
        }

        $jsst_entry = $jsst_withheld[$jsst_file];

        $jsst_core_block = !empty($jsst_entry['blocked_by']) && 'requires_core' === $jsst_entry['blocked_by'];

        if ($jsst_core_block) {
            $jsst_message = sprintf(
                /* translators: 1: add-on version, 2: required help desk version, 3: installed help desk version */
                esc_html__('Version %1$s is available, and needs JS Help Desk %2$s or newer. This site is running %3$s, so it is not being offered - installing it would break the add-on.', 'js-support-ticket'),
                esc_html((string) $jsst_entry['version']),
                esc_html((string) $jsst_entry['requires_core']),
                esc_html(isset($jsst_entry['core_installed']) ? (string) $jsst_entry['core_installed'] : self::coreVersion())
            );
        } else {
            $jsst_message = sprintf(
                /* translators: 1: add-on version, 2: opening link tag, 3: closing link tag */
                esc_html__('Version %1$s is available. Your license is not current, so it cannot be installed from here - %2$srenew it%3$s and it will appear as an ordinary update. The add-on you have keeps running either way.', 'js-support-ticket'),
                esc_html((string) $jsst_entry['version']),
                '<a href="' . esc_url(self::ACCOUNT) . '" target="_blank" rel="noopener">',
                '</a>'
            );
        }

        if (!empty($jsst_entry['security'])) {
            $jsst_message = '<strong>' . esc_html__('Security fix.', 'js-support-ticket') . '</strong> ' . $jsst_message;
        }

        /*
         * Red when the licence is the reason. A version the customer has paid
         * for and cannot have is a different thing from one their help desk is
         * too old to run: the first is lost money and is worth the colour, the
         * second is a compatibility note and amber is right for it - unless it
         * is a security fix, which is red whatever is in the way.
         */
        $jsst_class = ($jsst_core_block && empty($jsst_entry['security'])) ? 'notice-warning' : 'notice-error';

        printf(
            '<tr class="plugin-update-tr active"><td colspan="4" class="plugin-update colspanchange">'
            . '<div class="update-message notice inline %s notice-alt"><p>%s</p></div></td></tr>',
            esc_attr($jsst_class),
            wp_kses_post($jsst_message)
        );
    }

    /** Versions installed right now, read once per request. */
    private static $jsst_now = null;

    /** installed(), cached for the request - several notices ask per page. */
    private static function versionsNow() {
        if (null === self::$jsst_now) {
            self::$jsst_now = self::installed();
        }

        return self::$jsst_now;
    }

    /**
     * Compare two versions the way a person would.
     *
     * version_compare() on its own ranks 1.1.0 above 1.1, which would tell a
     * site it is behind a release it already has. Missing segments are padded
     * before comparing; a pre-release suffix is left to version_compare, which
     * already ranks 1.2.0-beta.1 below 1.2.0.
     */
    private static function compareVersions($jsst_a, $jsst_b) {
        $jsst_pad = function ($jsst_v) {
            $jsst_v     = ltrim(trim((string) $jsst_v), 'vV');
            $jsst_split = preg_split('/(?=[-+])/', $jsst_v, 2);
            $jsst_core  = explode('.', (string) $jsst_split[0]);

            while (count($jsst_core) < 3) {
                $jsst_core[] = '0';
            }

            return implode('.', $jsst_core) . (isset($jsst_split[1]) ? $jsst_split[1] : '');
        };

        return version_compare($jsst_pad($jsst_a), $jsst_pad($jsst_b));
    }

    /**
     * Whether a cached report still describes something newer than what is
     * installed now.
     *
     * The report is kept for twelve hours. An add-on updated in the meantime
     * must stop being offered the version it is already running - otherwise
     * the Plugins screen offers 1.1.0 to a site on 1.1.0 and a security banner
     * outlives the fix it was asking for.
     */
    private static function stillNewer($jsst_product, $jsst_entry) {
        $jsst_now = self::versionsNow();

        if (!isset($jsst_now[$jsst_product]) || empty($jsst_entry['version'])) {
            return false;
        }

        return self::compareVersions((string) $jsst_entry['version'], (string) $jsst_now[$jsst_product]) > 0;
    }

    /** A bundle's name as the customer bought it, from its slug. */
    private static function bundleName($jsst_slug) {
        $jsst_meta = class_exists('JSSTbundle') ? JSSTbundle::bundle($jsst_slug) : false;

        return ($jsst_meta && isset($jsst_meta['label'])) ? (string) $jsst_meta['label'] : (string) $jsst_slug;
    }

    /**
     * Where one installed bundle stands against the newest release.
     *
     * The single answer the licence screen, the banner and the Plugins rows
     * all read, so they cannot disagree about whether something is waiting.
     *
     * state is one of:
     *   current - nothing newer (or the report is stale and the site caught up)
     *   ready   - newer, and this site may install it now
     *   core    - newer, but it needs a newer JS Help Desk first
     *   license - newer, withheld because the licence is not current
     *   unknown - the server has said nothing about this bundle
     *
     * @param string $jsst_product Product slug, e.g. js-support-ticket-agents.
     * @return array|null Null when the bundle is not installed.
     */
    public static function versionState($jsst_product) {
        $jsst_now = self::versionsNow();

        if (!isset($jsst_now[$jsst_product])) {
            return null;
        }

        $jsst_result = array(
            'installed'         => (string) $jsst_now[$jsst_product],
            'latest'            => '',
            'state'             => 'unknown',
            'security'          => false,
            'security_versions' => array(),
            'requires_core'     => '',
        );

        $jsst_updates = self::hasKey() ? self::updates() : array();

        if (!isset($jsst_updates[$jsst_product])) {
            return $jsst_result;
        }

        $jsst_entry            = $jsst_updates[$jsst_product];
        $jsst_result['latest'] = isset($jsst_entry['version']) ? (string) $jsst_entry['version'] : '';

        if (empty($jsst_entry['update_available']) || !self::stillNewer($jsst_product, $jsst_entry)) {
            $jsst_result['state'] = 'current';

            /* Caught up since the report was made: the newest known version is
               the one running, not the one the report named. */
            if ('' === $jsst_result['latest'] || self::compareVersions($jsst_result['installed'], $jsst_result['latest']) > 0) {
                $jsst_result['latest'] = $jsst_result['installed'];
            }

            return $jsst_result;
        }

        $jsst_result['security']          = !empty($jsst_entry['security']);
        $jsst_result['security_versions'] = isset($jsst_entry['security_versions']) ? (array) $jsst_entry['security_versions'] : array();
        $jsst_result['requires_core']     = isset($jsst_entry['requires_core']) ? (string) $jsst_entry['requires_core'] : '';

        if (!empty($jsst_entry['package'])) {
            $jsst_result['state'] = 'ready';
        } elseif (!empty($jsst_entry['blocked_by']) && 'requires_core' === $jsst_entry['blocked_by']) {
            $jsst_result['state'] = 'core';
        } else {
            $jsst_result['state'] = 'license';
        }

        return $jsst_result;
    }

    /**
     * Installed bundles with a security fix they do not have yet.
     *
     * @return array[] Keyed by plugin file: name, version, why ('' ready,
     *                 'core' or 'license'), requires_core.
     */
    public static function securityPending() {
        $jsst_pending = array();

        foreach (array_keys(self::versionsNow()) as $jsst_product) {
            $jsst_state = self::versionState($jsst_product);

            if (null === $jsst_state || !$jsst_state['security'] || !in_array($jsst_state['state'], array('ready', 'core', 'license'), true)) {
                continue;
            }

            $jsst_slug = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', (string) $jsst_product);

            $jsst_pending['js-support-ticket-' . $jsst_slug . '/js-support-ticket-' . $jsst_slug . '.php'] = array(
                'name'          => self::bundleName($jsst_slug),
                'version'       => $jsst_state['latest'],
                'why'           => 'ready' === $jsst_state['state'] ? '' : $jsst_state['state'],
                'requires_core' => $jsst_state['requires_core'],
            );
        }

        return $jsst_pending;
    }

    /**
     * One line added to WordPress's own "new version available" row.
     *
     * Core draws that row and it is amber for every update alike; a security
     * fix sitting in it looks exactly like a changelog tidy-up. This says
     * which one it is.
     *
     * @param array  $jsst_plugin_data Plugin header data.
     * @param object $jsst_response    The update entry.
     */
    public static function securityRowMessage($jsst_plugin_data, $jsst_response) {
        echo ' <strong style="color:#b32d2e">' . esc_html__('This is a security update. Install it as soon as you can.', 'js-support-ticket') . '</strong>';
    }

    /**
     * "Tested up to", in the form WordPress compares.
     *
     * A release says it was tested up to 7.1, meaning the 7.1 branch. WordPress
     * compares that against 7.1.2 as a version, finds it lower, and prints
     * "Compatibility with WordPress 7.1.2: Not tested" beside every update.
     * wordpress.org avoids this by reporting the branch's current point
     * release, so the same is done here: a major.minor that matches the
     * running branch becomes the running version.
     *
     * @param string $jsst_tested As published, e.g. "7.1".
     */
    private static function testedFor($jsst_tested) {
        $jsst_running = (string) get_bloginfo('version');

        if (1 === preg_match('/^\d+\.\d+$/', $jsst_tested) && 0 === strpos($jsst_running, $jsst_tested . '.')) {
            return $jsst_running;
        }

        return $jsst_tested;
    }

    /**
     * Whether any download ticket in a cached report has died, or is about to.
     *
     * A minute's margin, because a ticket that is valid when the Plugins screen
     * renders must still be valid when somebody clicks the button on it.
     *
     * @param array $jsst_report Cached update report.
     */
    private static function ticketsExpired($jsst_report) {
        foreach ($jsst_report as $jsst_entry) {
            if (!is_array($jsst_entry) || empty($jsst_entry['package'])) {
                continue;
            }

            $jsst_expires = isset($jsst_entry['package_expires']) ? (int) $jsst_entry['package_expires'] : 0;

            if ($jsst_expires > 0 && $jsst_expires < time() + MINUTE_IN_SECONDS) {
                return true;
            }
        }

        return false;
    }

    /**
     * Days until the term ends, or null when nothing ends.
     *
     * Null is the honest answer for a perpetual licence and for a site that
     * has never been told an expiry, and both must read as "no reminder" rather
     * than as "expires today".
     */
    public static function daysUntilExpiry() {
        $jsst_expires = (string) self::state()['expires_at'];

        if ('' === $jsst_expires) {
            return null;
        }

        $jsst_time = strtotime($jsst_expires);

        if (false === $jsst_time) {
            return null;
        }

        return (int) ceil(($jsst_time - time()) / DAY_IN_SECONDS);
    }

    /**
     * Days left before an unreachable server stops being forgiven.
     *
     * Counts from the first failure that followed a successful check, so the
     * window cannot be used to run paid bundles by pasting a key while offline.
     * Zero when the server is answering, which is the ordinary case.
     */
    public static function graceRemaining() {
        $jsst_state  = self::state();
        $jsst_failed = (int) $jsst_state['failed'];

        if ($jsst_failed <= 0 || (int) $jsst_state['checked'] <= 0) {
            return 0;
        }

        $jsst_ends = $jsst_failed + (self::GRACE_DAYS * DAY_IN_SECONDS);

        return max(0, (int) ceil(($jsst_ends - time()) / DAY_IN_SECONDS));
    }

    /** The first failure timestamp, set once and left alone until one succeeds. */
    private static function noteFailure() {
        $jsst_state = self::state();

        if ((int) $jsst_state['failed'] > 0) {
            return;
        }

        $jsst_state['failed'] = time();
        update_option(self::OPT_STATE, $jsst_state, false);
    }

    /** The server answered, so whatever run of failures there was is over. */
    private static function clearFailure() {
        $jsst_state = self::state();

        if ((int) $jsst_state['failed'] <= 0) {
            return;
        }

        $jsst_state['failed'] = 0;
        update_option(self::OPT_STATE, $jsst_state, false);
    }

    /**
     * The names of bundles holding a version this site may not install.
     *
     * Names rather than slugs, because the banner is read by whoever runs the
     * site and "servicelevels" is not what they bought.
     *
     * @return string[]
     */
    private static function withheldNames() {
        $jsst_names = array();

        foreach (array_keys(self::withheld()) as $jsst_file) {
            $jsst_names[] = self::bundleName(jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', dirname($jsst_file)));
        }

        sort($jsst_names);

        return $jsst_names;
    }

    /** A date a customer can read, in whatever format the site is set to. */
    public static function niceDate($jsst_when) {
        $jsst_time = strtotime((string) $jsst_when);

        return false === $jsst_time ? '' : date_i18n((string) get_option('date_format'), $jsst_time);
    }

    /**
     * What, if anything, this site needs telling about its licence.
     *
     * One place decides, so the banner, its severity and whether it can be
     * dismissed cannot disagree with each other.
     *
     * 'urgent' is reserved for the case where something is actually being
     * withheld right now. A licence that merely ends soon is a reminder, and
     * dressing a reminder in red is how people learn to ignore red.
     *
     * @return array|null level, key, head, body; or null when all is well.
     */
    public static function notice() {
        $jsst_state  = self::state();
        $jsst_status = (string) $jsst_state['status'];

        /* Installed, and nobody has ever given this site a key. Since the
           licence gate (30 September 2026) such add-ons do not run - each one
           says so in its own notice - so this one does not repeat it; an old
           add-on build without the gate still runs, and this covers that. */
        if (!self::hasKey()) {
            $jsst_count = count(self::installed());

            if ($jsst_count < 1 || (class_exists('JSSTlicencegate') && array() !== JSSTlicencegate::gated())) {
                return null;
            }

            return array(
                'level' => 'warn',
                'key'   => 'nokey',
                'head'  => sprintf(
                    /* translators: %s: how many add-ons are installed */
                    _n('%s JS Help Desk add-on is installed without a license', '%s JS Help Desk add-ons are installed without a license', $jsst_count, 'js-support-ticket'),
                    number_format_i18n($jsst_count)
                ),
                'body'  => esc_html__('This site is not receiving updates or fixes for them, because nothing here says which license they belong to.', 'js-support-ticket'),
            );
        }

        /*
         * Why the term is not current, in a phrase that finishes "Your license
         * ...". Only expired and suspended are TERM problems - a revoked key or
         * one the server has never heard of is a different conversation, and
         * telling that customer their licence "ended on" a date would send
         * them to renew something that renewing will not fix.
         */
        $jsst_term_lapsed = in_array($jsst_status, array('expired', 'suspended'), true);
        $jsst_ended       = self::niceDate($jsst_state['expires_at']);

        if ('suspended' === $jsst_status) {
            $jsst_why = esc_html__('is suspended while a renewal payment is retried', 'js-support-ticket');
        } elseif ('' !== $jsst_ended) {
            /* translators: %s: date */
            $jsst_why = sprintf(esc_html__('ended on %s', 'js-support-ticket'), esc_html($jsst_ended));
        } else {
            $jsst_why = esc_html__('has ended', 'js-support-ticket');
        }

        /* The case worth shouting about: there is a newer build sitting on the
           server, this site is entitled to none of it, and staying quiet would
           look like the add-ons had simply stopped being developed. */
        $jsst_fixes = self::securityPending();

        /* Worse than an ordinary withheld update: the build this site cannot
           have closes a hole in the one it is running. */
        if ($jsst_term_lapsed) {
            $jsst_exposed = array();

            foreach ($jsst_fixes as $jsst_fix) {
                if ('license' === $jsst_fix['why']) {
                    $jsst_exposed[] = $jsst_fix['name'];
                }
            }

            if (array() !== $jsst_exposed) {
                return array(
                    'level' => 'urgent',
                    'key'   => '',
                    'head'  => sprintf(
                        /* translators: %s: add-on names */
                        _n('A security fix for %s is being withheld', 'Security fixes for %s are being withheld', count($jsst_exposed), 'js-support-ticket'),
                        implode(', ', $jsst_exposed)
                    ),
                    'body'  => sprintf(
                        /* translators: %s: why the licence is not current, e.g. "ended on <date>" */
                        esc_html(_n(
                            'Your license %s, so it cannot be installed on this site. Renew and it arrives as an ordinary update.',
                            'Your license %s, so they cannot be installed on this site. Renew and they arrive as ordinary updates.',
                            count($jsst_exposed),
                            'js-support-ticket'
                        )),
                        $jsst_why
                    ),
                );
            }
        }

        /* A security fix this site could have and does not. The licence is
           fine; the site is simply behind on the one kind of update that
           should not wait. */
        if ('active' === $jsst_status && array() !== $jsst_fixes) {
            $jsst_ready = array();
            $jsst_parts = array();
            $jsst_all   = array();

            foreach ($jsst_fixes as $jsst_fix) {
                $jsst_all[] = $jsst_fix['name'];

                if ('' === $jsst_fix['why']) {
                    $jsst_ready[] = $jsst_fix['name'] . ' ' . $jsst_fix['version'];
                } elseif ('core' === $jsst_fix['why']) {
                    $jsst_parts[] = sprintf(
                        /* translators: 1: add-on name, 2: its version, 3: help desk version it needs */
                        esc_html__('%1$s %2$s needs JS Help Desk %3$s or newer, so update the help desk first.', 'js-support-ticket'),
                        esc_html($jsst_fix['name']),
                        esc_html($jsst_fix['version']),
                        esc_html($jsst_fix['requires_core'])
                    );
                }
            }

            if (array() !== $jsst_ready) {
                array_unshift($jsst_parts, sprintf(
                    /* translators: %s: add-on names and versions */
                    esc_html(_n(
                        '%s includes a security fix and is ready to install.',
                        '%s include security fixes and are ready to install.',
                        count($jsst_ready),
                        'js-support-ticket'
                    )),
                    esc_html(implode(', ', $jsst_ready))
                ));
            }

            if (array() !== $jsst_parts) {
                return array(
                    'level' => 'urgent',
                    'key'   => '',
                    'head'  => sprintf(
                        /* translators: %s: add-on names */
                        _n('Security update available for %s', 'Security updates available for %s', count($jsst_all), 'js-support-ticket'),
                        implode(', ', $jsst_all)
                    ),
                    'body'  => implode(' ', $jsst_parts),
                    'cta'   => array(self_admin_url('update-core.php'), __('Go to updates', 'js-support-ticket')),
                );
            }
        }

        $jsst_names = $jsst_term_lapsed ? self::withheldNames() : array();

        if (array() !== $jsst_names) {
            return array(
                'level' => 'urgent',
                'key'   => '',
                'head'  => sprintf(
                    /* translators: %s: how many add-ons have an update waiting */
                    _n('%s JS Help Desk add-on update is being withheld', '%s JS Help Desk add-on updates are being withheld', count($jsst_names), 'js-support-ticket'),
                    number_format_i18n(count($jsst_names))
                ),
                'body'  => sprintf(
                    /* translators: 1: why the licence is not current, e.g. "ended on <date>", 2: the add-ons affected */
                    esc_html__('Your license %1$s, so the newer versions of %2$s cannot be installed on this site. What you have keeps running and nothing has been deleted, but it has stopped receiving fixes. Renew and they arrive as ordinary updates.', 'js-support-ticket'),
                    $jsst_why,
                    esc_html(implode(', ', $jsst_names))
                ),
            );
        }

        /*
         * The server has not been reachable for at least a day.
         *
         * A day, because one failed request is weather: the next admin page
         * load retries within a quarter of an hour, and a banner that comes and
         * goes with every blip teaches people to ignore it. After a day it is
         * a real problem - usually a firewall or a host blocking outgoing
         * requests - and every other thing this could say is stale.
         *
         * What it does NOT say is that anything will be switched off. The site
         * keeps running on the last answer it was given; that is the whole
         * point of recording the answer. What stops is anything new arriving.
         */
        $jsst_failed = (int) $jsst_state['failed'];

        if ($jsst_failed > 0 && (time() - $jsst_failed) >= DAY_IN_SECONDS) {
            $jsst_since = (int) floor((time() - $jsst_failed) / DAY_IN_SECONDS);

            return array(
                'level' => 'warn',
                /* Past the grace window it can no longer be put away. */
                'key'   => self::graceRemaining() > 0 ? 'unreachable' : '',
                'head'  => __('JS Help Desk cannot reach the license server', 'js-support-ticket'),
                'body'  => sprintf(
                    /* translators: 1: date of the first failure, 2: days since */
                    _n(
                        'Nothing has got through since %1$s (%2$s day). Your add-ons keep running on the last answer the server gave, but no updates can arrive until it is reachable again. This is usually a firewall or the host blocking outgoing requests to jshelpdesk.com.',
                        'Nothing has got through since %1$s (%2$s days). Your add-ons keep running on the last answer the server gave, but no updates can arrive until it is reachable again. This is usually a firewall or the host blocking outgoing requests to jshelpdesk.com.',
                        $jsst_since,
                        'js-support-ticket'
                    ),
                    esc_html(self::niceDate('@' . $jsst_failed)),
                    number_format_i18n($jsst_since)
                ),
            );
        }

        if ($jsst_term_lapsed) {
            return array(
                'level' => 'warn',
                'key'   => 'lapsed',
                'head'  => 'suspended' === $jsst_status
                    ? __('Your JS Help Desk license is suspended', 'js-support-ticket')
                    : __('Your JS Help Desk license has ended', 'js-support-ticket'),
                'body'  => sprintf(
                    /* translators: %s: why the licence is not current, e.g. "ended on <date>" */
                    esc_html__('It %s. Your add-ons keep running and nothing has been deleted. Until it is renewed this site receives no add-on updates, and the license cannot be added to another site.', 'js-support-ticket'),
                    $jsst_why
                ),
            );
        }

        /* Released on purpose - the customer moved the licence, or is taking
           this site down. Worth one line, and worth being able to put away. */
        if ('inactive' === $jsst_status) {
            return array(
                'level' => 'warn',
                'key'   => 'released',
                'head'  => __('This site is not using its JS Help Desk license', 'js-support-ticket'),
                'body'  => esc_html__('The license was released from this site. The add-ons keep running, but they receive no updates here until the license is activated again.', 'js-support-ticket'),
            );
        }

        /* Anything else that is not active is a refusal: a revoked key, one the
           server does not recognise, a site it could not read. The server's
           own reason, already put into words, is the most useful thing to
           show. With no reason on file there is nothing honest to add. */
        if ('active' !== $jsst_status) {
            $jsst_said = (string) $jsst_state['message'];

            if ('' === $jsst_said) {
                return null;
            }

            $jsst_reason = (string) ($jsst_state['reason'] ?? '');

            /* An expired key is not a rejected site: the licence is fine, its
               term ran out, and renewing is exactly the fix - so it says that
               and offers it. (Installation test, 26 September 2026: this read
               as an error and had no Renew button.) */
            if ('license_expired' === $jsst_reason) {
                return array(
                    'level' => 'warn',
                    'key'   => 'expired-key',
                    'head'  => __('Your JS Help Desk license has expired', 'js-support-ticket'),
                    'body'  => esc_html($jsst_said),
                    'cta'   => array(self::ACCOUNT, __('Renew license', 'js-support-ticket')),
                );
            }

            /* No slot free: the fix is in the account, so point there. */
            if (in_array($jsst_reason, array('no_production_slots', 'no_staging_slots', 'no_development_slots'), true)) {
                return array(
                    'level' => 'warn',
                    'key'   => 'no-slot',
                    'head'  => __('This license has no free site for this one', 'js-support-ticket'),
                    'body'  => esc_html($jsst_said),
                    'cta'   => array(self::ACCOUNT, __('Manage sites in your account', 'js-support-ticket')),
                );
            }

            return array(
                'level' => 'warn',
                'key'   => 'refused',
                'head'  => __('The license server did not accept this site', 'js-support-ticket'),
                'body'  => esc_html($jsst_said),
            );
        }

        /* Current, and ending soon enough to be worth saying so. */
        $jsst_days = self::daysUntilExpiry();

        if (null === $jsst_days || $jsst_days > self::EXPIRY_WARN_DAYS) {
            return null;
        }

        return array(
            'level' => 'warn',
            /* Inside the last week the reminder stops being dismissible, so a
               dismissal in week four cannot hide the last warning. */
            'key'   => $jsst_days > self::EXPIRY_FINAL_DAYS ? 'expiring' : '',
            'head'  => $jsst_days > 0
                ? sprintf(
                    /* translators: %s: days remaining */
                    _n('Your JS Help Desk license ends in %s day', 'Your JS Help Desk license ends in %s days', $jsst_days, 'js-support-ticket'),
                    number_format_i18n($jsst_days)
                )
                : __('Your JS Help Desk license ends today', 'js-support-ticket'),
            'body'  => sprintf(
                /* translators: %s: expiry date */
                esc_html__('It runs until %s. Everything keeps working after that, but add-on updates stop. Renew before then and they carry on without a gap.', 'js-support-ticket'),
                esc_html($jsst_ended)
            ),
        );
    }

    /**
     * Whether renewing is the thing this site should be offered.
     *
     * True for a term that has lapsed or is about to. False for a refusal,
     * which renewing does not fix, and for a licence with months left on it.
     */
    public static function shouldRenew() {
        if (!self::hasKey()) {
            return false;
        }

        $jsst_status = (string) self::state()['status'];

        if (in_array($jsst_status, array('expired', 'suspended'), true)) {
            return true;
        }

        $jsst_days = self::daysUntilExpiry();

        return 'active' === $jsst_status && null !== $jsst_days && $jsst_days <= self::EXPIRY_WARN_DAYS;
    }

    /**
     * Screens a non-urgent notice is allowed to appear on.
     *
     * An urgent one ignores this and shows everywhere, which is the point of
     * it. A reminder does not follow somebody into Settings or the post editor.
     */
    public static function noticeScreen() {
        if (!function_exists('get_current_screen')) {
            return false;
        }

        $jsst_screen = get_current_screen();

        if (!$jsst_screen) {
            return false;
        }

        if (in_array($jsst_screen->id, array('dashboard', 'plugins', 'update-core'), true)) {
            return true;
        }

        return false !== strpos((string) $jsst_screen->id, 'jssupportticket');
    }

    /** Whether this admin has put this particular reminder away for now. */
    public static function snoozed($jsst_key) {
        if ('' === $jsst_key) {
            return false;
        }

        return (int) get_user_meta(get_current_user_id(), self::META_SNOOZE . $jsst_key, true) > time();
    }

    /**
     * Send the retired licence screens' addresses to what replaced them.
     *
     * The old "JS Help Desk Pro" screen, the old Update Key screen and the old
     * three-step installer are gone, but their addresses are in bookmarks, in
     * old support replies and in the dashboard of any site not yet updated.
     *
     * On admin_init, because the layouts themselves are drawn after WordPress
     * has sent the admin header, when a redirect is no longer possible - the
     * controller can only print a notice by then.
     */
    public static function redirectRetired() {
        if (!isset($_GET['page'], $_GET['jstlay']) || wp_doing_ajax()) {
            return;
        }

        $jsst_page = sanitize_key(wp_unslash($_GET['page']));
        $jsst_lay  = sanitize_key(wp_unslash($_GET['jstlay']));
        $jsst_map  = array(
            /* Install Add-ons is part of the License & Add-ons page since
               2 October 2026. */
            'jssupportticket' => array('pro' => 'license', 'addonstatus' => 'license', 'installaddons' => 'license'),
            'premiumplugin'   => array(
                'step1'     => 'license',
                'step2'     => 'license',
                'step3'     => 'license',
                'updatekey' => 'license',
            ),
        );

        if (isset($jsst_map[$jsst_page][$jsst_lay])) {
            wp_safe_redirect(admin_url('admin.php?page=jssupportticket&jstlay=' . $jsst_map[$jsst_page][$jsst_lay]));
            exit;
        }
    }

    /**
     * Carry a 4.0.0 site across to the new licence server.
     *
     * Up to 4.0.0 the help desk stored, once per add-on, a token that
     * jshelpdesk.com/setup/ issued when the customer pasted their key:
     * transaction_key_for_js-support-ticket-<add-on>. The token is that key,
     * encrypted, and the licence server imported the keys - 2,233 of 2,553
     * kept exactly the key the customer has - so for nearly every customer the
     * key is already on this site, in a form only the server can read.
     *
     * So the tokens go to /adopt, which reads the key out on the server - the
     * secret never ships in this plugin - activates exactly as /activate would,
     * and hands the key back. The key is what is stored from then on; the old
     * tokens are left where they are and never read again.
     *
     * Once, on the first admin page load after the update, by somebody who
     * could manage the licence anyway. Most-used token first - one key bought
     * for the Professional tier is stored under every add-on it covered - and
     * at most eight, well inside the server's failure throttle. A token the
     * server does not recognise is skipped. If the server cannot be reached, or
     * is too old to know /adopt, nothing is decided and a later load asks again.
     */
    public static function adoptLegacyKey() {
        if (self::hasKey() || wp_doing_ajax() || !current_user_can('manage_options')) {
            return;
        }

        if ('' !== (string) get_option(self::OPT_ADOPTED, '')) {
            return;
        }

        global $wpdb;

        $jsst_tokens = $wpdb->get_col($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options}
              WHERE option_name LIKE %s AND option_value <> ''
              GROUP BY option_value
              ORDER BY COUNT(*) DESC
              LIMIT 8",
            $wpdb->esc_like('transaction_key_for_js-support-ticket') . '%'
        ));
        $jsst_tokens = array_values(array_filter(array_map('trim', (array) $jsst_tokens)));

        if (array() === $jsst_tokens) {
            update_option(self::OPT_ADOPTED, 'none', false);
            return;
        }

        foreach ($jsst_tokens as $jsst_token) {
            $jsst_body = array(
                'token'       => $jsst_token,
                'site_url'    => self::siteUrl(),
                'products'    => array_keys(self::installed()),
                'environment' => self::environment(),
            );

            if ('' !== self::declaredKind()) {
                $jsst_body['kind'] = self::declaredKind();
            }

            $jsst_result = self::post('/adopt', $jsst_body);

            if (is_wp_error($jsst_result)) {
                return;
            }

            $jsst_reason = isset($jsst_result['reason']) ? (string) $jsst_result['reason'] : '';

            /* Busy, or a server from before /adopt existed: WordPress answers an
               unknown route with its own error code and no reason of ours. */
            if ('rate_limited' === $jsst_reason || (isset($jsst_result['code']) && 'rest_no_route' === $jsst_result['code'])) {
                return;
            }

            if (empty($jsst_result['license_key'])) {
                continue;
            }

            update_option(self::OPT_KEY, (string) $jsst_result['license_key'], false);
            self::remember($jsst_result);
            delete_transient(self::TRANSIENT_UPDATES);
            update_option(self::OPT_ADOPTED, (string) time(), false);

            /* /adopt answers with the key but no signed statement, so ask
               /activate for one now - with the key just stored - rather than
               leave the add-ons this upgrade installs waiting for the daily
               re-check before they will run. */
            if (!empty($jsst_result['ok'])) {
                self::refresh();
            }

            if (class_exists('JSSTmessage')) {
                JSSTmessage::setMessage(esc_html(!empty($jsst_result['ok'])
                    ? __('Your license key was carried over from the previous version of JS Help Desk. This site now gets its updates from the new license server.', 'js-support-ticket')
                    : __('Your license key was carried over from the previous version of JS Help Desk. The License screen says what it needs next.', 'js-support-ticket')
                ), !empty($jsst_result['ok']) ? 'updated' : 'error');
            }

            return;
        }

        update_option(self::OPT_ADOPTED, 'none', false);
    }

    /**
     * Put a reminder away for a week.
     *
     * Per user rather than per site: one administrator deciding they have read
     * it should not hide it from the next one.
     */
    public static function maybeSnooze() {
        if (!isset($_GET['jsst_lic_snooze']) || !current_user_can('activate_plugins')) {
            return;
        }

        $jsst_key = sanitize_key(wp_unslash($_GET['jsst_lic_snooze']));

        if ('' === $jsst_key) {
            return;
        }

        check_admin_referer('jsst-lic-snooze-' . $jsst_key);

        $jsst_until = time() + (self::SNOOZE_DAYS * DAY_IN_SECONDS);

        /* The loyalty offer (JSSTupgradeassistant::offerNotice()): put away
           until 30 days before its date, shown once more then, and after that
           "Got it" is final - the date has nearly come. */
        if ('offer' === $jsst_key) {
            $jsst_offer = self::offer();
            $jsst_from  = $jsst_offer ? strtotime($jsst_offer['from'] . ' 00:00:00 UTC') : 0;
            if ($jsst_from) {
                $jsst_remind = $jsst_from - 30 * DAY_IN_SECONDS;
                $jsst_until  = $jsst_remind > time() ? $jsst_remind : $jsst_from + DAY_IN_SECONDS;
            }
        }

        update_user_meta(
            get_current_user_id(),
            self::META_SNOOZE . $jsst_key,
            $jsst_until
        );

        wp_safe_redirect(remove_query_arg(array('jsst_lic_snooze', '_wpnonce')));
        exit;
    }

    /**
     * The banner.
     *
     * Shown to whoever could actually act on it. An author looking at their
     * posts can do nothing about a licence and should not be told about one.
     */
    public static function adminNotice() {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        /* The licence screen says all of this itself, in the page, next to
           the thing that fixes it. A banner above it pointing back at the
           same screen is the same message twice. */
        if (isset($_GET['page'], $_GET['jstlay'])
            && 'jssupportticket' === sanitize_key(wp_unslash($_GET['page']))
            && 'license' === sanitize_key(wp_unslash($_GET['jstlay']))) {
            return;
        }

        $jsst_notice = self::notice();

        if (null === $jsst_notice) {
            return;
        }

        $jsst_urgent = 'urgent' === $jsst_notice['level'];

        if (!$jsst_urgent && (!self::noticeScreen() || self::snoozed($jsst_notice['key']))) {
            return;
        }

        /* Inline, because this appears on screens where the plugin's own
           stylesheet is not loaded - the Plugins list, Updates, the WordPress
           dashboard. Small enough that a separate file would cost more. */
        echo '<style>.jsst-license-notice .jsst-license-head{margin:.6em 0 .2em;font-size:14px;font-weight:600;}'
            . '.jsst-license-notice .jsst-license-act{margin:.8em 0 .8em;}'
            . '.jsst-license-notice .jsst-license-act .button{margin-right:8px;}'
            . '.jsst-license-notice.jsst-license-urgent{border-left-width:4px;background:#fcf0f1;}'
            . '.jsst-license-notice.jsst-license-urgent .jsst-license-head{color:#8a1f11;}</style>';

        /* Renew is offered only where renewing is the fix. A refused key or
           an unreachable server is not solved by paying again, and a button
           that says so would be selling the wrong remedy. */
        $jsst_actions = '';

        /* The action is a place to go. On the page it points to, it is a
           button that reloads the page the customer is already reading. */
        $jsst_here = function_exists('get_current_screen') && get_current_screen()
            && false !== strpos((string) get_current_screen()->id, 'update-core');

        if (!empty($jsst_notice['cta']) && !$jsst_here) {
            $jsst_actions .= sprintf(
                '<a class="button button-primary" href="%1$s">%2$s</a>',
                esc_url($jsst_notice['cta'][0]),
                esc_html($jsst_notice['cta'][1])
            );
        }

        if (self::shouldRenew()) {
            $jsst_actions .= sprintf(
                '<a class="button %1$s" href="%2$s" target="_blank" rel="noopener">%3$s</a>',
                ($jsst_urgent && empty($jsst_notice['cta'])) ? 'button-primary' : '',
                esc_url(self::ACCOUNT),
                esc_html__('Renew license', 'js-support-ticket')
            );
        }

        $jsst_actions .= sprintf(
            '<a class="button" href="%1$s">%2$s</a>',
            esc_url(admin_url('admin.php?page=jssupportticket&jstlay=license')),
            self::hasKey() ? esc_html__('License & Add-ons', 'js-support-ticket') : esc_html__('Add license key', 'js-support-ticket')
        );

        if (!$jsst_urgent && '' !== $jsst_notice['key']) {
            $jsst_actions .= sprintf(
                ' <a href="%1$s">%2$s</a>',
                esc_url(wp_nonce_url(
                    add_query_arg('jsst_lic_snooze', $jsst_notice['key']),
                    'jsst-lic-snooze-' . $jsst_notice['key']
                )),
                esc_html__('Remind me next week', 'js-support-ticket')
            );
        }

        printf(
            '<div class="notice %1$s jsst-license-notice %2$s">'
            . '<p class="jsst-license-head">%3$s</p><p>%4$s</p><p class="jsst-license-act">%5$s</p></div>',
            $jsst_urgent ? 'notice-error' : 'notice-warning',
            $jsst_urgent ? 'jsst-license-urgent' : '',
            esc_html($jsst_notice['head']),
            wp_kses_post($jsst_notice['body']),
            wp_kses_post($jsst_actions)
        );
    }

    /* ------------------------------------------------------------------ *
     * The License & Add-ons page, without page loads (2 October 2026)
     *
     * The same verbs as the controller's tasks - savelicensekey,
     * rechecklicense, releaselicense, installaddons, activateaddon - answered
     * as JSON, so the page can show each step as it happens. The tasks stay
     * for a browser without JavaScript.
     * ------------------------------------------------------------------ */

    /**
     * WordPress's Help Desk menu: mark License & Add-ons, not Dashboard, as
     * the current entry while it is open. Both are page=jssupportticket.
     */
    public static function menuHighlight($jsst_file) {
        if (isset($_GET['page'], $_GET['jstlay']) && 'jssupportticket' === sanitize_key(wp_unslash($_GET['page']))
            && in_array(sanitize_key(wp_unslash($_GET['jstlay'])), array('license', 'legacy'), true)) {
            return 'admin.php?page=jssupportticket&jstlay=license';
        }
        return $jsst_file;
    }

    /** Activate, re-check or release the key. */
    public static function ajaxKey() {
        check_ajax_referer('jsst-lp', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You are not allowed to do that.', 'js-support-ticket')), 403);
        }

        $jsst_do = isset($_POST['do']) ? sanitize_key(wp_unslash($_POST['do'])) : '';

        if ('activate' === $jsst_do) {
            $jsst_key  = isset($_POST['key']) ? trim(sanitize_text_field(wp_unslash($_POST['key']))) : '';
            $jsst_done = self::activate($jsst_key);

            if (is_wp_error($jsst_done)) {
                wp_send_json_error(array('message' => $jsst_done->get_error_message()));
            }

            $jsst_granted = count((array) self::state()['products']);
            wp_send_json_success(array('message' => sprintf(
                /* translators: %s: how many add-ons the licence grants */
                _n('Activated. %s add-on is included in your license.', 'Activated. %s add-ons are included in your license.', $jsst_granted, 'js-support-ticket'),
                number_format_i18n($jsst_granted)
            )));
        }

        if ('check' === $jsst_do) {
            $jsst_done = self::refresh();

            if (is_wp_error($jsst_done)) {
                wp_send_json_error(array('message' => $jsst_done->get_error_message()));
            }
            if (self::isActive()) {
                wp_send_json_success(array('message' => __('Checked. The license is in order.', 'js-support-ticket')));
            }

            $jsst_message = (string) self::state()['message'];
            wp_send_json_error(array('message' => '' !== $jsst_message
                ? $jsst_message
                : __('Checked, and the license is not currently valid for this site.', 'js-support-ticket')));
        }

        if ('release' === $jsst_do) {
            $jsst_done = self::deactivate();

            if (is_wp_error($jsst_done)) {
                wp_send_json_error(array('message' => $jsst_done->get_error_message()));
            }

            wp_send_json_success(array('message' => __('The license has been released from this site and can now be used on another. Nothing was removed, so putting the key back puts everything back.', 'js-support-ticket')));
        }

        wp_send_json_error(array('message' => __('Unknown request.', 'js-support-ticket')), 400);
    }

    /**
     * Install, switch on or update one add-on.
     *
     * One add-on per request, so the page can say which one it is working on
     * and a slow host times out on one download rather than on nine.
     */
    public static function ajaxAddon() {
        check_ajax_referer('jsst-lp', 'nonce');

        $jsst_do      = isset($_POST['do']) ? sanitize_key(wp_unslash($_POST['do'])) : '';
        $jsst_product = isset($_POST['product']) ? sanitize_key(wp_unslash($_POST['product'])) : '';
        $jsst_slug    = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_product);
        $jsst_file    = $jsst_product . '/' . $jsst_product . '.php';
        $jsst_caps    = array('install' => 'install_plugins', 'activate' => 'activate_plugins', 'update' => 'update_plugins');

        if (!isset($jsst_caps[$jsst_do])) {
            wp_send_json_error(array('message' => __('Unknown request.', 'js-support-ticket')), 400);
        }
        if (!current_user_can($jsst_caps[$jsst_do])) {
            wp_send_json_error(array('message' => __('You are not allowed to do that.', 'js-support-ticket')), 403);
        }
        if (!class_exists('JSSTbundle') || 0 !== strpos($jsst_product, 'js-support-ticket-') || false === JSSTbundle::bundle($jsst_slug)) {
            wp_send_json_error(array('message' => __('That is not a JS Help Desk add-on.', 'js-support-ticket')));
        }

        /* Nine downloads in a row can outlast a 30-second limit on a slow host. */
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ('install' === $jsst_do) {
            $jsst_done = self::install($jsst_product);

            if (is_wp_error($jsst_done)) {
                wp_send_json_error(array('message' => $jsst_done->get_error_message()));
            }
            if (!$jsst_done['activated']) {
                wp_send_json_error(array('state' => 'off', 'message' => sprintf(
                    /* translators: 1: add-on name, 2: error message */
                    __('%1$s is installed but could not be switched on: %2$s', 'js-support-ticket'),
                    $jsst_done['name'],
                    $jsst_done['message']
                )));
            }
            wp_send_json_success(array('version' => $jsst_done['version']));
        }

        if (!file_exists(WP_PLUGIN_DIR . '/' . $jsst_file)) {
            wp_send_json_error(array('message' => __('That add-on is not installed on this site.', 'js-support-ticket')));
        }

        if ('activate' === $jsst_do) {
            $jsst_result = activate_plugin($jsst_file);

            if (is_wp_error($jsst_result)) {
                wp_send_json_error(array('message' => $jsst_result->get_error_message()));
            }
            self::forget();
            $jsst_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $jsst_file, false, false);
            wp_send_json_success(array('version' => isset($jsst_data['Version']) ? (string) $jsst_data['Version'] : ''));
        }

        /* Update, the way WordPress's own Update now button does it:
           bulk_upgrade() keeps the plugin switched on, where upgrade() switches
           it off and leaves turning it back on to another page load. */
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $jsst_was_on   = is_plugin_active($jsst_file);
        $jsst_skin     = new WP_Ajax_Upgrader_Skin();
        $jsst_upgrader = new Plugin_Upgrader($jsst_skin);
        $jsst_result   = $jsst_upgrader->bulk_upgrade(array($jsst_file));
        $jsst_one      = (is_array($jsst_result) && isset($jsst_result[$jsst_file])) ? $jsst_result[$jsst_file] : $jsst_result;

        if (is_wp_error($jsst_one) || empty($jsst_one) || $jsst_skin->get_errors()->has_errors()) {
            $jsst_error = is_wp_error($jsst_one) ? $jsst_one : $jsst_skin->get_errors();
            wp_send_json_error(array('message' => $jsst_error->has_errors()
                ? $jsst_error->get_error_message()
                : __('WordPress could not update it. Try again from the Plugins screen.', 'js-support-ticket')));
        }

        if ($jsst_was_on && !is_plugin_active($jsst_file)) {
            activate_plugin($jsst_file);
        }
        self::forget();
        $jsst_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $jsst_file, false, false);
        wp_send_json_success(array('version' => isset($jsst_data['Version']) ? (string) $jsst_data['Version'] : ''));
    }
}
