<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTaiengine')) {
    return;
}

/**
 * Where the product's AI text actually comes from. (Roadmap 6.0-AI-01)
 *
 * This was JSSTcopilotprovider, and it served one screen. There were two other
 * registries beside it — the Zywrap module knew how to reach Zywrap, and Instant
 * Resolve had a provider factory that offered exactly one provider — so a site
 * owner could configure three engines, get three different answers about which
 * one was in use, and pay two bills. One registry, asked by every surface, is
 * what 6.0-AI-01 means by unification.
 *
 * An engine is five things: which lane it runs in, where to post, how to
 * authenticate, how to shape a request, and how to read the answer. Four ship:
 *
 *   zywrap     Our own hosted engine. The default because it is the only one
 *              that works without the site owner opening an account somewhere
 *              else first — the account is created on the settings screen
 *              itself. Hosted lane.
 *   anthropic  Bring your own key. Nothing of ours stands between the site and
 *              the provider: their server, their key, their bill, their data
 *              processing agreement. Hosted lane.
 *   openai     The same, with OpenAI. (5.0.4) Hosted lane.
 *   local      A model the site owner runs — Ollama, LM Studio, vLLM,
 *              llama.cpp, anything speaking OpenAI chat completions. No key
 *              needed, no per-token cost, nothing leaving the network.
 *              (Roadmap 6.0-AI-08) Local lane.
 *
 * The filter is not a placeholder for ambition — it is the seam that keeps that
 * promise, so a site with an internal gateway, a regional endpoint or a model
 * this has never heard of is served without a fork.
 *
 * Requests go out over wp_remote_post rather than a vendored SDK. That is a
 * deliberate constraint of shipping to WordPress: this plugin is installed from
 * a zip alongside dozens of others, there is no composer autoloader to rely on,
 * and a vendored client library is the classic way for two plugins to load two
 * versions of the same namespace and take a site down. Every other outbound call
 * in this plugin goes through the same WordPress HTTP layer, which also means a
 * site's existing proxy, timeout and TLS configuration applies here too.
 */
class JSSTaiengine {

    /** Which engine a site has configured, and the model to ask it for. */
    const OPT_ENGINE = 'jsst_ai_engine';
    /** The engine to try when the chosen one cannot answer. (Roadmap 6.0-AI-07) */
    const OPT_FALLBACK = 'jsst_ai_fallback';
    const OPT_MODEL  = 'jsst_ai_model';

    /**
     * Keys are stored one per engine, at jsst_ai_key_<id>.
     *
     * One shared key row was how the old class did it, and switching engine then
     * silently sent the previous provider's key to the new one — a 401 that
     * reads as "the plugin is broken". Per engine also means a site can set up
     * a second engine without destroying the first one's configuration.
     */
    const OPT_KEY_PREFIX = 'jsst_ai_key_';

    /** The local endpoint, its model name and how long to wait. */
    const OPT_LOCAL_ENDPOINT = 'jsst_ai_local_endpoint';
    const OPT_LOCAL_MODEL    = 'jsst_ai_local_model';
    const OPT_LOCAL_TIMEOUT  = 'jsst_ai_local_timeout';
    /** Whether that address was found on the internet, and for which address. */
    const OPT_LOCAL_PUBLIC   = 'jsst_ai_local_public';

    /**
     * Seconds to wait for a hosted reply before giving up on it.
     *
     * A person is waiting on almost every call - a button on the ticket
     * screen, a customer on the form - and a minute is already long. Past it
     * the request is not retried either: a timed-out request may still have
     * run and been billed.
     */
    const TIMEOUT = 60;

    /** The longest retry-after this will wait out before asking once more. */
    const RETRY_WAIT_MAX = 10;

    /**
     * Seconds to wait for a local one.
     *
     * Far longer, and not a mistake. A small model on a CPU-only box answers in
     * tens of seconds, and a first request after the model has been evicted from
     * memory can spend most of a minute just loading weights. Timing that out
     * and reporting it as unreachable is how a working local setup gets
     * abandoned as broken.
     */
    const LOCAL_TIMEOUT = 300;

    /* ------------------------------------------------------------------ *
     * Who can be asked
     * ------------------------------------------------------------------ */

    /**
     * Readable plain text from an engine's HTML answer.
     *
     * Paragraphs, headings and line breaks become line breaks and list items
     * become "- " lines, so text shown where HTML cannot be (the chat widget
     * draws with textContent) keeps its shape; entities are decoded so ">" is
     * not shown as "&gt;".
     */
    public static function plainText($jsst_html) {
        $jsst_text = strip_shortcodes((string) $jsst_html);
        $jsst_text = preg_replace('#<\s*li[^>]*>#i', "\n- ", $jsst_text);
        $jsst_text = preg_replace('#<\s*br\s*/?>#i', "\n", $jsst_text);
        $jsst_text = preg_replace('#</\s*(p|div|h[1-6]|li|ul|ol|tr|blockquote)\s*>#i', "\n", $jsst_text);
        $jsst_text = preg_replace('#<\s*(h[1-6]|p|ul|ol|blockquote)[^>]*>#i', "\n", $jsst_text);
        $jsst_text = wp_strip_all_tags($jsst_text);
        $jsst_text = html_entity_decode($jsst_text, ENT_QUOTES, 'UTF-8');
        $jsst_text = preg_replace("/[ \t]+\n/", "\n", $jsst_text);
        return trim(preg_replace("/\n{3,}/", "\n\n", $jsst_text));
    }

    public static function engines() {
        $jsst_engines = array(
            'zywrap' => array(
                'label'         => 'Zywrap',
                'lane'          => 'hosted',
                /* 5.0.4: no licence ever issued a Zywrap key; the account is
                   created or connected on this screen (JSSTzywrapconnect). */
                'blurb'         => esc_html(__('Our own engine. Create a free account right here, with 10,000 credits to start, or connect the one you have.', 'js-support-ticket')),
                'recommended'   => true,
                'endpoint'      => 'https://api.zywrap.com/v1/proxy',
                'models'        => array(),
                'default_model' => '',
                'keyhint'       => esc_html(__('A key from the API keys page of your Zywrap account.', 'js-support-ticket')),
                'ask'           => array(__CLASS__, 'askZywrap'),
            ),
            'anthropic' => array(
                'label'    => 'Anthropic',
                'lane'     => 'hosted',
                'blurb'    => esc_html(__('Bring your own key. The request goes from your server to Anthropic — nothing of ours is in the middle.', 'js-support-ticket')),
                'endpoint' => 'https://api.anthropic.com/v1/messages',
                'models'   => array(
                    /* The default first: with no model saved the list shows its
                       first entry, and saving untouched stores it. The default
                       is the cheapest current model: support answers are short
                       and grounded, and the most expensive tier as the default
                       made every site pay for it without choosing to. The
                       price per million tokens (in / out) is in each label so
                       the choice is an informed one. */
                    'claude-haiku-5-5'  => 'Claude Haiku 5.5 - $0.10 / $0.50 per 1M tokens (recommended)',
                    'claude-sonnet-5-5' => 'Claude Sonnet 5.5 - $2 / $10 per 1M tokens',
                    'claude-opus-5-5'   => 'Claude Opus 5.5 - $4 / $20 per 1M tokens',
                    'claude-opus-5'     => 'Claude Opus 5 - $5 / $25 per 1M tokens',
                    'claude-sonnet-5'   => 'Claude Sonnet 5 - $2 / $10 per 1M tokens',
                    'claude-haiku-4-5'  => 'Claude Haiku 4.5 - $1 / $5 per 1M tokens',
                ),
                'default_model' => 'claude-haiku-5-5',
                'build'         => array(__CLASS__, 'buildAnthropic'),
                'read'          => array(__CLASS__, 'readAnthropic'),
                'keyhint'       => esc_html(__('Starts with sk-ant-. Created at console.anthropic.com.', 'js-support-ticket')),
            ),
            'openai' => array(
                'label'    => 'OpenAI',
                'lane'     => 'hosted',
                'blurb'    => esc_html(__('Bring your own key. The request goes from your server to OpenAI — nothing of ours is in the middle.', 'js-support-ticket')),
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
                'models'   => array(
                    /* As Anthropic's: the default first, the price in the label.
                       All of these take structured output and reasoning effort
                       on Chat Completions. gpt-5.4-nano is left out: OpenAI has
                       deprecated it. (5.0.4) */
                    'gpt-6-luna'    => 'GPT-6 Luna - $0.10 / $0.50 per 1M tokens (recommended)',
                    'gpt-5.6-luna'  => 'GPT-5.6 Luna - $0.20 / $1.20 per 1M tokens',
                    'gpt-5.4-mini'  => 'GPT-5.4 mini - $0.75 / $4.50 per 1M tokens',
                    'gpt-6.1-terra' => 'GPT-6.1 Terra - $1 / $5 per 1M tokens',
                    'gpt-6.1-sol'   => 'GPT-6.1 Sol - $2 / $10 per 1M tokens',
                ),
                'default_model' => 'gpt-6-luna',
                'build'         => array(__CLASS__, 'buildOpenAi'),
                'read'          => array(__CLASS__, 'readOpenAi'),
                'keyhint'       => esc_html(__('Starts with sk-. Created at platform.openai.com.', 'js-support-ticket')),
            ),
            'local' => array(
                'label'    => esc_html(__('Local model', 'js-support-ticket')),
                /* An address on the internet is a hosted service whatever the
                   engine is called, so it runs under the hosted lane's switch. */
                'lane'     => self::localIsPublic() ? 'hosted' : 'local',
                'blurb'    => esc_html(__('A model you run yourself. Nothing leaves your network and there is no per-token cost.', 'js-support-ticket')),
                'endpoint' => array(__CLASS__, 'localEndpoint'),
                'models'   => array(),
                'default_model' => '',
                'build'    => array(__CLASS__, 'buildOpenAiChat'),
                'read'     => array(__CLASS__, 'readOpenAiChat'),
                'keyhint'  => esc_html(__('Usually blank. Fill it only if your endpoint asks for a bearer token.', 'js-support-ticket')),
                'nokey'    => true,
            ),
        );
        return apply_filters('jsst_ai_engines', $jsst_engines);
    }

    /**
     * The engine in use.
     *
     * Falls back to the first one the site has rather than erroring, because a
     * stale option naming an engine a filter used to add must not take the whole
     * ticket screen down with it.
     */
    public static function current() {
        $jsst_engines = self::engines();
        $jsst_key = (string) get_option(self::OPT_ENGINE, '');

        /* Sites upgrading from 5.5 have the Copilot's provider row and no engine
           row. Read it once rather than migrating on activation: an upgrade that
           writes settings is an upgrade that can get them wrong on a site nobody
           is watching. */
        if ($jsst_key === '') {
            $jsst_key = (string) get_option('jsst_copilot_provider', '');
        }
        if ($jsst_key === '') {
            $jsst_key = 'zywrap';
        }
        if (!isset($jsst_engines[$jsst_key])) {
            $jsst_key = key($jsst_engines);
        }
        return array('key' => $jsst_key, 'def' => $jsst_engines[$jsst_key]);
    }

    public static function currentId() {
        $jsst_current = self::current();
        return $jsst_current['key'];
    }

    public static function currentLabel() {
        $jsst_current = self::current();
        return $jsst_current['def']['label'];
    }

    /** The lane the current engine runs in. */
    public static function currentLane() {
        $jsst_current = self::current();
        return isset($jsst_current['def']['lane']) ? $jsst_current['def']['lane'] : 'hosted';
    }

    /**
     * The model to ask for.
     *
     * Falls back to the engine's default rather than to whatever was saved last:
     * a site that switches engine has a model name in the option that means
     * nothing to the new one, and sending it produces a 404 that reads like a
     * broken plugin rather than a stale setting.
     *
     * An engine with an empty models list takes free text, because the name of a
     * model somebody pulled onto their own box is not something this can have a
     * list of.
     */
    public static function model($jsst_id = null) {
        $jsst_current = ($jsst_id === null) ? self::current() : self::byId($jsst_id);
        if (!$jsst_current) {
            return '';
        }
        if ($jsst_current['key'] === 'local') {
            return trim((string) get_option(self::OPT_LOCAL_MODEL, ''));
        }
        /* Zywrap picks its own model. The shared option holds the Anthropic
           one, and reporting that here put an Anthropic model on every Zywrap
           row of the usage meter. */
        if ($jsst_current['key'] === 'zywrap') {
            return 'zywrap';
        }
        /* One row per engine since there are two lists to choose from (5.0.4);
           the shared row before it still answers for a site that saved there.
           A name from another engine's list fails the check below. */
        $jsst_model = trim((string) get_option(self::OPT_MODEL . '_' . $jsst_current['key'], ''));
        if ($jsst_model === '') {
            $jsst_model = trim((string) get_option(self::OPT_MODEL, ''));
        }
        if ($jsst_model === '') {
            $jsst_model = trim((string) get_option('jsst_copilot_model', ''));
        }
        if (empty($jsst_current['def']['models'])) {
            return $jsst_model;
        }
        if ($jsst_model !== '' && isset($jsst_current['def']['models'][$jsst_model])) {
            return $jsst_model;
        }
        return $jsst_current['def']['default_model'];
    }

    /** Where one engine's chosen model is stored. (5.0.4) */
    public static function modelOption($jsst_id) {
        return self::OPT_MODEL . '_' . (string) $jsst_id;
    }

    public static function byId($jsst_id) {
        $jsst_engines = self::engines();
        $jsst_id = (string) $jsst_id;
        return isset($jsst_engines[$jsst_id]) ? array('key' => $jsst_id, 'def' => $jsst_engines[$jsst_id]) : false;
    }

    /* ------------------------------------------------------------------ *
     * Keys and endpoints
     * ------------------------------------------------------------------ */

    /**
     * The key for one engine, read only at the moment of use.
     *
     * Never returned to a screen, never put in the debug bundle, never
     * journalled. The System Status redaction (4.0-OPS-02) catches these by name
     * as well, but the rule here is simpler: nothing reads this except the
     * request builder.
     *
     * Two engines fall back to where their key already lived, so an upgrading
     * site does not have to re-enter a key it has had for a year.
     */
    public static function apiKey($jsst_id) {
        $jsst_id = (string) $jsst_id;
        $jsst_key = trim((string) get_option(self::OPT_KEY_PREFIX . $jsst_id, ''));
        if ($jsst_key !== '') {
            return $jsst_key;
        }
        if ($jsst_id === 'zywrap') {
            if (!empty(jssupportticket::$_config['zywrap_api_key'])) {
                return trim((string) jssupportticket::$_config['zywrap_api_key']);
            }
            return trim((string) get_option('jsst_zywrap_api_key', ''));
        }
        if ($jsst_id === 'anthropic') {
            return trim((string) get_option('jsst_copilot_api_key', ''));
        }
        return '';
    }

    public static function saveKey($jsst_id, $jsst_value) {
        $jsst_id = (string) $jsst_id;
        $jsst_value = trim((string) $jsst_value);

        /* An empty box means "leave it alone", never "delete it". The screen can
           never redisplay a key, so empty is the normal state of that input and
           reading it as a deletion would wipe a working key every time somebody
           saved an unrelated setting on the same form. Forgetting a key is its
           own action. (Same rule as the connector framework, 5.5-CH-04.) */
        if ($jsst_value === '') {
            return false;
        }
        update_option(self::OPT_KEY_PREFIX . $jsst_id, $jsst_value, false);

        /* Zywrap's key has always lived in its own row and the Zywrap module
           still reads it there. Written to both so the two cannot disagree. */
        if ($jsst_id === 'zywrap') {
            update_option('jsst_zywrap_api_key', $jsst_value);
        }
        return true;
    }

    public static function forgetKey($jsst_id) {
        delete_option(self::OPT_KEY_PREFIX . (string) $jsst_id);
        if ($jsst_id === 'zywrap') {
            delete_option('jsst_zywrap_api_key');
        }
        if ($jsst_id === 'anthropic') {
            delete_option('jsst_copilot_api_key');
        }
    }

    /**
     * A key as it may be shown: enough to recognise, not enough to use.
     */
    public static function keyHint($jsst_id) {
        $jsst_key = self::apiKey($jsst_id);
        if ($jsst_key === '') {
            return '';
        }
        $jsst_tail = substr($jsst_key, -4);
        return str_repeat('•', 8) . $jsst_tail;
    }

    /**
     * Where the local model lives.
     *
     * Stored as the base URL a person copies out of their own terminal —
     * http://127.0.0.1:11434 — and the path is appended here, so somebody who
     * pastes the full completions URL and somebody who pastes the base both end
     * up in the same place.
     */
    public static function localEndpoint() {
        $jsst_base = trim((string) get_option(self::OPT_LOCAL_ENDPOINT, ''));
        if ($jsst_base === '') {
            return '';
        }
        /* Asked again on use, not only on save: a value stored before this
           check existed, or written by something other than the form, must
           not reach the metadata service either. No DNS here - this runs on
           every screen that asks whether AI is configured. */
        if (!self::validLocalEndpoint($jsst_base, false)) {
            return '';
        }
        $jsst_base = untrailingslashit($jsst_base);
        if (substr($jsst_base, -17) === '/chat/completions') {
            return $jsst_base;
        }
        if (substr($jsst_base, -3) === '/v1') {
            return $jsst_base . '/chat/completions';
        }
        return $jsst_base . '/v1/chat/completions';
    }

    /**
     * Is this endpoint one we are willing to post to?
     *
     * Deliberately permissive about private addresses — a local model on
     * 127.0.0.1 or 192.168.x.x is the entire point of this lane, so the scraper's
     * SSRF guard (6.0-AI-14) would refuse exactly the configuration being asked
     * for. What is checked instead is that this is an http or https URL an
     * administrator typed on purpose, because the only route to this option is
     * the settings form and the only person who can reach it already holds
     * manage_options.
     *
     * The one private range refused is link-local, with the names cloud hosts
     * give their metadata service. Nobody runs a model there, and it is where
     * a server's own credentials can be read - so the address box must not
     * be a way to make this server fetch them. With $jsst_resolve the name is
     * looked up too, so a hostname pointing at 169.254.169.254 is caught.
     */
    public static function validLocalEndpoint($jsst_url, $jsst_resolve = true) {
        $jsst_url = trim((string) $jsst_url);
        if ($jsst_url === '') {
            return true;
        }
        $jsst_scheme = strtolower((string) wp_parse_url($jsst_url, PHP_URL_SCHEME));
        $jsst_host   = strtolower(trim((string) wp_parse_url($jsst_url, PHP_URL_HOST), '[]'));
        if (!in_array($jsst_scheme, array('http', 'https'), true) || $jsst_host === '') {
            return false;
        }
        $jsst_host = rtrim($jsst_host, '.');
        if (in_array($jsst_host, array('metadata', 'metadata.google.internal', 'metadata.goog', 'instance-data', 'instance-data.ec2.internal'), true)) {
            return false;
        }
        $jsst_addresses = array($jsst_host);
        // http://2852039166/ is 169.254.169.254 written as one number.
        if (ctype_digit($jsst_host) && (float) $jsst_host <= 4294967295) {
            $jsst_addresses[] = long2ip((int) $jsst_host);
        }
        if ($jsst_resolve && !filter_var($jsst_host, FILTER_VALIDATE_IP)) {
            $jsst_found = @gethostbynamel($jsst_host);
            if (is_array($jsst_found)) {
                $jsst_addresses = array_merge($jsst_addresses, $jsst_found);
            }
        }
        foreach ($jsst_addresses as $jsst_address) {
            if (self::isLinkLocal($jsst_address)) {
                return false;
            }
        }
        return true;
    }

    /** 169.254.0.0/16, fe80::/10, AWS's fd00:ec2::254, and v4 inside v6. */
    private static function isLinkLocal($jsst_address) {
        if (filter_var($jsst_address, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        $jsst_packed = @inet_pton($jsst_address);
        if ($jsst_packed === false) {
            return false;
        }
        if (strlen($jsst_packed) === 16) {
            if ($jsst_packed === inet_pton('fd00:ec2::254')) {
                return true;
            }
            if (ord($jsst_packed[0]) === 0xfe && (ord($jsst_packed[1]) & 0xc0) === 0x80) {
                return true;
            }
            // ::ffff:a.b.c.d and ::a.b.c.d carry an IPv4 address in the last four bytes.
            if (substr($jsst_packed, 0, 10) === str_repeat("\0", 10)
                    && in_array(substr($jsst_packed, 10, 2), array("\xff\xff", "\0\0"), true)) {
                $jsst_packed = substr($jsst_packed, 12, 4);
            } else {
                return false;
            }
        }
        return (ord($jsst_packed[0]) === 169 && ord($jsst_packed[1]) === 254);
    }

    /**
     * Is the saved local address somewhere on the internet?
     *
     * The local lane promises that nothing leaves the network, and the address
     * box takes api.openai.com as readily as 127.0.0.1. A public address is
     * therefore run as what it is - a hosted service - and the screen says so.
     *
     * No DNS here: engines() asks this on every request. The settings screen
     * and the save look the name up (checkLocalAddress()) and store the
     * answer. A name nobody has looked up yet counts only by what it shows
     * itself - an IP address, or a name that is local by definition.
     */
    public static function localIsPublic() {
        $jsst_base = trim((string) get_option(self::OPT_LOCAL_ENDPOINT, ''));
        if ($jsst_base === '') {
            return false;
        }
        $jsst_seen = get_option(self::OPT_LOCAL_PUBLIC, array());
        if (is_array($jsst_seen) && isset($jsst_seen['endpoint']) && $jsst_seen['endpoint'] === $jsst_base) {
            return !empty($jsst_seen['public']);
        }
        return (self::addressIsPublic($jsst_base, false) === true);
    }

    /**
     * Look the saved local address up, and store whether it is public.
     *
     * Called from the settings screen and on save only, never per request.
     * The lookup is kept for an hour so the screen does not ask DNS on every
     * visit; a save passes $jsst_fresh, because the address just changed.
     */
    public static function checkLocalAddress($jsst_fresh = false) {
        $jsst_base = trim((string) get_option(self::OPT_LOCAL_ENDPOINT, ''));
        if ($jsst_base === '') {
            delete_option(self::OPT_LOCAL_PUBLIC);
            return false;
        }
        $jsst_cache = 'jsst_ai_local_where_' . md5($jsst_base);
        $jsst_where = $jsst_fresh ? false : get_transient($jsst_cache);
        if ($jsst_where !== 'public' && $jsst_where !== 'private') {
            $jsst_where = (self::addressIsPublic($jsst_base, true) === true) ? 'public' : 'private';
            set_transient($jsst_cache, $jsst_where, HOUR_IN_SECONDS);
        }
        update_option(self::OPT_LOCAL_PUBLIC, array('endpoint' => $jsst_base, 'public' => ($jsst_where === 'public')), false);
        return ($jsst_where === 'public');
    }

    /**
     * true when the address is on the internet, false when it is on this
     * network, null when that cannot be told (a name not looked up, or one
     * that does not resolve). Any one public address makes it public.
     */
    private static function addressIsPublic($jsst_url, $jsst_resolve) {
        $jsst_host = rtrim(strtolower(trim((string) wp_parse_url($jsst_url, PHP_URL_HOST), '[]')), '.');
        if ($jsst_host === '') {
            return null;
        }
        // Local by definition, whatever a resolver says.
        if ($jsst_host === 'localhost' || preg_match('/\.(localhost|local|internal)$/', $jsst_host)) {
            return false;
        }
        $jsst_addresses = array();
        if (filter_var($jsst_host, FILTER_VALIDATE_IP)) {
            $jsst_addresses[] = $jsst_host;
        } elseif (ctype_digit($jsst_host) && (float) $jsst_host <= 4294967295) {
            $jsst_addresses[] = long2ip((int) $jsst_host);
        } elseif ($jsst_resolve) {
            $jsst_found = @gethostbynamel($jsst_host);
            if (is_array($jsst_found)) {
                $jsst_addresses = $jsst_found;
            }
            // A name with no IPv4 address may still have an IPv6 one.
            if (empty($jsst_addresses) && function_exists('dns_get_record')) {
                foreach ((array) @dns_get_record($jsst_host, DNS_AAAA) as $jsst_record) {
                    if (!empty($jsst_record['ipv6'])) {
                        $jsst_addresses[] = $jsst_record['ipv6'];
                    }
                }
            }
        }
        if (empty($jsst_addresses)) {
            return null;
        }
        foreach ($jsst_addresses as $jsst_address) {
            if (self::isPublicAddress($jsst_address)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Not loopback, RFC 1918, CGNAT 100.64/10, link-local, unique-local
     * fc00::/7 or 0/8 - so on the internet. IPv4 inside IPv6 is read as IPv4.
     */
    private static function isPublicAddress($jsst_address) {
        $jsst_packed = @inet_pton((string) $jsst_address);
        if ($jsst_packed === false) {
            return false;
        }
        if (strlen($jsst_packed) === 16) {
            // ::ffff:a.b.c.d and ::a.b.c.d; ::1 and :: land in 0/8 below.
            if (substr($jsst_packed, 0, 10) === str_repeat("\0", 10)
                    && in_array(substr($jsst_packed, 10, 2), array("\xff\xff", "\0\0"), true)) {
                $jsst_packed = substr($jsst_packed, 12, 4);
            } else {
                $jsst_first = ord($jsst_packed[0]);
                if (($jsst_first & 0xfe) === 0xfc) {
                    return false;
                }
                return !($jsst_first === 0xfe && (ord($jsst_packed[1]) & 0xc0) === 0x80);
            }
        }
        $jsst_a = ord($jsst_packed[0]);
        $jsst_b = ord($jsst_packed[1]);
        return !($jsst_a === 0 || $jsst_a === 10 || $jsst_a === 127
            || ($jsst_a === 100 && ($jsst_b & 0xc0) === 64)
            || ($jsst_a === 169 && $jsst_b === 254)
            || ($jsst_a === 172 && ($jsst_b & 0xf0) === 16)
            || ($jsst_a === 192 && $jsst_b === 168));
    }

    /**
     * A page on zywrap.com, tagged so Zywrap can tell the visit came from the
     * help desk. (5.0.4)
     */
    public static function zywrapUrl($jsst_path = '') {
        return add_query_arg(
            array('utm_source' => 'js-help-desk', 'utm_medium' => 'plugin'),
            'https://www.zywrap.com/' . ltrim((string) $jsst_path, '/'));
    }

    /** Set when Zywrap refused a request for want of credits; cleared by the next answer. */
    const OPT_ZYWRAP_NO_CREDITS = 'jsst_zywrap_no_credits';

    public static function localTimeout() {
        $jsst_seconds = (int) get_option(self::OPT_LOCAL_TIMEOUT, self::LOCAL_TIMEOUT);
        if ($jsst_seconds < 10) {
            $jsst_seconds = 10;
        }
        if ($jsst_seconds > 900) {
            $jsst_seconds = 900;
        }
        return $jsst_seconds;
    }

    /** Where to post for one engine, resolving the callable form. */
    public static function endpointFor($jsst_def) {
        if (isset($jsst_def['endpoint']) && is_callable($jsst_def['endpoint'])) {
            return (string) call_user_func($jsst_def['endpoint']);
        }
        return isset($jsst_def['endpoint']) ? (string) $jsst_def['endpoint'] : '';
    }

    /**
     * Is there enough here to ask anything?
     *
     * Not the same question as whether the lane is open — that is the policy's,
     * and it is asked first. This one is only about configuration.
     */
    public static function configured($jsst_id = null) {
        $jsst_current = ($jsst_id === null) ? self::current() : self::byId($jsst_id);
        if (!$jsst_current) {
            return false;
        }
        if (!empty($jsst_current['def']['nokey'])) {
            return (self::endpointFor($jsst_current['def']) !== '' && self::model($jsst_current['key']) !== '');
        }
        return (self::apiKey($jsst_current['key']) !== '');
    }

    /** Ready in every sense: the lane is open and the engine is set up. */
    public static function usable($jsst_id = null) {
        $jsst_current = ($jsst_id === null) ? self::current() : self::byId($jsst_id);
        if (!$jsst_current) {
            return false;
        }
        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::allowsEngine($jsst_current['key'])) {
            return false;
        }
        return self::configured($jsst_current['key']);
    }

    /* ------------------------------------------------------------------ *
     * Asking
     * ------------------------------------------------------------------ */

    /**
     * Run one call against the configured engine.
     *
     * Three gates before a byte goes anywhere, in the order that matters: the
     * lane, the configuration, then the scrub. The lane is first because a site
     * that has switched hosted models off must get the same answer whether or
     * not a key happens to still be stored.
     *
     * @param array $jsst_call system, prompt, maxtokens, effort and an optional json schema.
     * @return array|WP_Error text plus whatever the engine reported about the run.
     */
    public static function ask($jsst_call) {
        $jsst_tried = array();

        /* The engines to try, in order. A fallback exists for two different
           bad days and both are worth having: the provider is down or rate
           limiting, and the budget for the month is spent. In the second case
           falling back to a local model is the whole point - the site keeps
           answering, on hardware it already pays for. (Roadmap 6.0-AI-07) */
        foreach (self::chain() as $jsst_id) {
            if (isset($jsst_tried[$jsst_id])) continue;
            $jsst_tried[$jsst_id] = true;

            $jsst_result = self::askEngine($jsst_id, $jsst_call);
            if (!is_wp_error($jsst_result)) {
                return $jsst_result;
            }

            /* Keep the first refusal to answer with: it is the one about the
               engine the site actually chose, and "the provider rejected the
               key" is more use than the same sentence about the spare. */
            if (!isset($jsst_first)) {
                $jsst_first = $jsst_result;
            }
            if (!self::worthRetrying($jsst_result)) {
                return $jsst_first;
            }
        }

        return isset($jsst_first) ? $jsst_first
            : new WP_Error('jsst_ai_noengine', esc_html(__('No AI engine is set up on this site.', 'js-support-ticket')));
    }

    /**
     * The engine, then the spare.
     *
     * The spare is only offered when it is a different engine that is actually
     * usable: an unusable fallback would turn one honest error into two, and
     * the second one would be about a thing nobody configured.
     */
    public static function chain() {
        $jsst_chain = array(self::currentId());

        $jsst_spare = self::fallbackId();
        if ($jsst_spare !== '' && $jsst_spare !== $jsst_chain[0] && self::usable($jsst_spare)) {
            $jsst_chain[] = $jsst_spare;
        }
        return $jsst_chain;
    }

    /** The engine to try when the chosen one cannot answer. */
    public static function fallbackId() {
        $jsst_id = (string) get_option(self::OPT_FALLBACK, '');
        $jsst_engines = self::engines();
        return isset($jsst_engines[$jsst_id]) ? $jsst_id : '';
    }

    public static function setFallback($jsst_id) {
        $jsst_engines = self::engines();
        $jsst_id = (string) $jsst_id;
        update_option(self::OPT_FALLBACK, isset($jsst_engines[$jsst_id]) ? $jsst_id : '', false);
        return true;
    }

    /**
     * Is this failure one the spare might survive?
     *
     * Deliberately a short list. A rejected key, an unknown model or a refusal
     * from the model itself will fail exactly the same way on the second
     * engine, and asking twice turns one bill into two and one error into a
     * slower error. Only "the provider is not answering" and "we are out of
     * budget on this one" are worth a second engine.
     */
    public static function worthRetrying($jsst_error) {
        if (!is_wp_error($jsst_error)) return false;
        return in_array($jsst_error->get_error_code(), array(
            'jsst_ai_unreachable',
            'jsst_ai_local_unreachable',
            'jsst_ai_ratelimited',
            'jsst_ai_overloaded',
            'jsst_ai_budget',
            'jsst_ai_zywrap_empty',
        ), true);
    }

    /**
     * One engine, asked once.
     *
     * Everything that was ask() before the fallback existed, plus the two ends
     * of the meter: guard() before the request is made, because a budget
     * checked afterwards is a report, and record() after it whether it worked
     * or not, because a request that was charged for and then failed is exactly
     * the spend nobody can otherwise account for. (Roadmap 6.0-AI-07)
     */
    public static function askEngine($jsst_id, $jsst_call) {
        $jsst_current = self::byId($jsst_id);
        if (!$jsst_current) {
            return new WP_Error('jsst_ai_noengine', esc_html(__('That is not an engine this site knows about.', 'js-support-ticket')));
        }
        $jsst_def = $jsst_current['def'];

        if (class_exists('JSSTaipolicy')) {
            $jsst_verdict = JSSTaipolicy::explain(
                isset($jsst_def['lane']) ? $jsst_def['lane'] : 'hosted'
            );
            if ($jsst_verdict['state'] !== 'ok') {
                return new WP_Error('jsst_ai_lane_closed', $jsst_verdict['detail']);
            }
        }

        if (!self::configured($jsst_current['key'])) {
            if (!empty($jsst_def['nokey'])) {
                return new WP_Error('jsst_ai_nolocal', esc_html(__('No local model is set up yet. Add its address and model name on the AI Agent screen.', 'js-support-ticket')));
            }
            return new WP_Error('jsst_ai_nokey', esc_html(__('No AI key is set up yet. Add one on the AI Agent screen.', 'js-support-ticket')));
        }

        /* Everything the site is about to send, scrubbed once, here. Doing it in
           each caller is how one of them ends up not doing it. */
        if (class_exists('JSSTaipolicy')) {
            if (isset($jsst_call['prompt'])) {
                $jsst_call['prompt'] = JSSTaipolicy::redact($jsst_call['prompt']);
            }
            if (isset($jsst_call['system'])) {
                $jsst_call['system'] = JSSTaipolicy::redact($jsst_call['system']);
            }
            // A Zywrap wrapper's own inputs leave the site too.
            if (!empty($jsst_call['zywrap']) && is_array($jsst_call['zywrap'])) {
                $jsst_call['zywrap'] = self::redactValues($jsst_call['zywrap']);
            }
        }

        /* The context is only built where the meter exists: it names that
           class's constants, and an add-on running against a core without it
           should lose the accounting, not the feature. */
        if (class_exists('JSSTaiusage')) {
            return JSSTaiusage::meter(
                self::meterContext($jsst_current, $jsst_call),
                function () use ($jsst_current, $jsst_def, $jsst_call) {
                    return self::whole(self::send($jsst_current, $jsst_def, $jsst_call), $jsst_call);
                }
            );
        }
        return self::whole(self::send($jsst_current, $jsst_def, $jsst_call), $jsst_call);
    }

    /**
     * A cut-off answer, refused when the caller cannot use one.
     *
     * An agent reading a draft can see it stops mid-sentence; an automatic
     * answer sent to a customer, or a summary on the ticket form, cannot be
     * read first. Those callers pass `notruncate` and get an error instead.
     * The tokens travel in the error's data: the cut-off answer was billed.
     */
    private static function whole($jsst_result, $jsst_call) {
        if (is_wp_error($jsst_result) || empty($jsst_call['notruncate']) || empty($jsst_result['truncated'])) {
            return $jsst_result;
        }
        $jsst_usage = array();
        foreach (array('intokens', 'outtokens') as $jsst_key) {
            if (isset($jsst_result[$jsst_key])) {
                $jsst_usage[$jsst_key] = (int) $jsst_result[$jsst_key];
            }
        }
        return new WP_Error('jsst_ai_truncated',
            esc_html(__('The answer was cut off before it finished, so it was not used.', 'js-support-ticket')),
            $jsst_usage);
    }

    /**
     * What the meter is told about this call.
     *
     * `funded` is the field that matters and it is not the same as the lane: a
     * hosted call on the vendor's own engine is drawn from the allowance that
     * came with the licence, and a hosted call on the site's own key is real
     * money leaving their account. One column showing both would be a lie in
     * whichever unit it chose.
     */
    private static function meterContext($jsst_current, $jsst_call) {
        $jsst_lane = isset($jsst_current['def']['lane']) ? $jsst_current['def']['lane'] : 'hosted';

        if ($jsst_lane === 'local') {
            $jsst_funded = JSSTaiusage::FUNDED_LOCAL;
        } elseif ($jsst_current['key'] === 'zywrap') {
            $jsst_funded = JSSTaiusage::FUNDED_PLAN;
        } else {
            $jsst_funded = JSSTaiusage::FUNDED_BYOK;
        }

        return array(
            'engine'  => $jsst_current['key'],
            'model'   => self::model($jsst_current['key']),
            'lane'    => $jsst_lane,
            'funded'  => $jsst_funded,
            'feature' => isset($jsst_call['feature']) ? (string) $jsst_call['feature'] : '',
            'ticket'  => isset($jsst_call['ticket']) ? (int) $jsst_call['ticket'] : 0,
        );
    }

    /** The request itself. Nothing here decides whether it should be made. */
    private static function send($jsst_current, $jsst_def, $jsst_call) {
        /* An engine whose wire format is nothing like a chat completion brings
           its own asker rather than pretending to fit the build/read pair. */
        if (isset($jsst_def['ask']) && is_callable($jsst_def['ask'])) {
            return call_user_func($jsst_def['ask'], $jsst_call, $jsst_current['key']);
        }

        $jsst_endpoint = self::endpointFor($jsst_def);
        if ($jsst_endpoint === '') {
            return new WP_Error('jsst_ai_noendpoint', esc_html(__('That engine has no address to post to.', 'js-support-ticket')));
        }
        // The local address is looked up once more right before use; see validLocalEndpoint().
        if ($jsst_current['key'] === 'local' && !self::validLocalEndpoint($jsst_endpoint)) {
            return new WP_Error('jsst_ai_noendpoint', esc_html(__('The local model address points at a link-local or cloud metadata address, so nothing was sent.', 'js-support-ticket')));
        }

        $jsst_request = call_user_func(
            $jsst_def['build'],
            $jsst_call,
            self::model($jsst_current['key']),
            self::apiKey($jsst_current['key'])
        );
        if (is_wp_error($jsst_request)) {
            return $jsst_request;
        }

        /* No redirects: a provider never answers with one, and following it
           would let the local address bounce this request somewhere the check
           above never saw. */
        $jsst_args = array(
            'headers'     => $jsst_request['headers'],
            'body'        => wp_json_encode($jsst_request['body']),
            'timeout'     => empty($jsst_def['nokey']) ? self::TIMEOUT : self::localTimeout(),
            'sslverify'   => true,
            'redirection' => 0,
        );
        $jsst_response = wp_remote_post($jsst_endpoint, $jsst_args);

        /* One more try when the provider says "busy", after the wait it asks
           for. Not on a transport error: that includes our own timeout, and a
           request we stopped waiting for may still have run and been billed. */
        if (!is_wp_error($jsst_response)) {
            $jsst_wait = self::retryWait($jsst_response);
            if ($jsst_wait !== null) {
                sleep($jsst_wait);
                $jsst_response = wp_remote_post($jsst_endpoint, $jsst_args);
            }
        }

        if (is_wp_error($jsst_response)) {
            if (!empty($jsst_def['nokey'])) {
                // A local endpoint that cannot be reached is almost always the
                // model server not running, or bound to localhost on a different
                // machine from the web server. Saying that is more use than the
                // curl message.
                return new WP_Error('jsst_ai_local_unreachable', sprintf(
                    /* translators: %s: the network error the request failed with */
                    esc_html(__('Could not reach the local model: %s. Check it is running and reachable from this server.', 'js-support-ticket')),
                    $jsst_response->get_error_message()
                ));
            }
            // The site could not reach the provider at all. Worth saying plainly:
            // on shared hosting this is almost always outbound HTTPS being
            // blocked, not anything to do with the key.
            return new WP_Error('jsst_ai_unreachable', sprintf(
                /* translators: %s: the network error the request failed with */
                esc_html(__('Could not reach the AI provider: %s', 'js-support-ticket')),
                $jsst_response->get_error_message()
            ));
        }

        /* The status first. A gateway in front of the provider answers a 502
           or 503 with an HTML page, and reading the body first turned "the
           provider is down" into "unreadable", which the spare engine is never
           tried for. */
        $jsst_code = (int) wp_remote_retrieve_response_code($jsst_response);
        $jsst_body = json_decode(wp_remote_retrieve_body($jsst_response), true);
        if ($jsst_code !== 200) {
            return self::httpError($jsst_code, is_array($jsst_body) ? $jsst_body : array());
        }
        if (!is_array($jsst_body)) {
            return new WP_Error('jsst_ai_unreadable', esc_html(__('The AI provider sent something this could not read.', 'js-support-ticket')));
        }
        return call_user_func($jsst_def['read'], $jsst_body);
    }

    /** 429, 529 and the other 5xx: busy, not broken, so worth one more try. */
    private static function busy($jsst_code) {
        return ($jsst_code === 429 || ($jsst_code >= 500 && $jsst_code <= 599));
    }

    /**
     * Seconds to wait before the one retry, or null for no retry.
     *
     * The provider's retry-after is honoured when it is short. A longer one
     * means the spare engine, if there is one, is the better next step, and a
     * person is waiting either way.
     */
    private static function retryWait($jsst_response) {
        if (!self::busy((int) wp_remote_retrieve_response_code($jsst_response))) {
            return null;
        }
        // An account with no credit left answers 429 too, and waiting changes nothing.
        $jsst_body = json_decode(wp_remote_retrieve_body($jsst_response), true);
        if (is_array($jsst_body) && self::outOfCredit($jsst_body)) {
            return null;
        }
        $jsst_after = trim((string) wp_remote_retrieve_header($jsst_response, 'retry-after'));
        if ($jsst_after === '') {
            return 2;
        }
        if (is_numeric($jsst_after)) {
            $jsst_seconds = (int) ceil((float) $jsst_after);
        } else {
            $jsst_when = strtotime($jsst_after);
            $jsst_seconds = ($jsst_when === false) ? 2 : $jsst_when - time();
        }
        if ($jsst_seconds > self::RETRY_WAIT_MAX) {
            return null;
        }
        return max(1, $jsst_seconds);
    }

    /**
     * Turn an engine's error into something an agent can act on.
     *
     * The status code is the useful part and the provider's own message is
     * usually written for a developer, so the code decides what is said and the
     * message is kept only where it adds something.
     */
    private static function httpError($jsst_code, $jsst_body) {
        $jsst_detail = '';
        if (isset($jsst_body['error']['message'])) {
            $jsst_detail = (string) $jsst_body['error']['message'];
        } elseif (isset($jsst_body['error']) && is_string($jsst_body['error'])) {
            $jsst_detail = (string) $jsst_body['error'];
        }
        /* Not a rate limit, whatever the status says: the account has no credit
           left. The spare engine is tried, as for a spent budget. (5.0.4) */
        if (self::outOfCredit($jsst_body)) {
            return new WP_Error('jsst_ai_budget', esc_html(__('The AI provider account has no credit left. Add credit with the provider, or choose another engine on the AI Agent screen.', 'js-support-ticket')));
        }
        switch ($jsst_code) {
            case 401:
            case 403:
                return new WP_Error('jsst_ai_auth', esc_html(__('The AI provider rejected the key. Check it on the AI Agent screen.', 'js-support-ticket')));
            case 404:
                return new WP_Error('jsst_ai_model', esc_html(__('The provider does not offer the model this is set to use. Pick another on the AI Agent screen.', 'js-support-ticket')));
            case 429:
                return new WP_Error('jsst_ai_ratelimited', esc_html(__('The AI provider is rate limiting this site. Wait a moment and try again.', 'js-support-ticket')));
        }
        // 408 and every 5xx, 529 included: the provider is not answering, so the spare may.
        if ($jsst_code === 408 || self::busy($jsst_code)) {
            return new WP_Error('jsst_ai_overloaded', esc_html(__('The AI provider is unavailable right now. Nothing was changed — try again shortly.', 'js-support-ticket')));
        }
        return new WP_Error('jsst_ai_failed', sprintf(
            /* translators: 1: HTTP status code, 2: the provider's own error message */
            esc_html(__('The AI provider refused the request (%1$d). %2$s', 'js-support-ticket')),
            $jsst_code,
            esc_html($jsst_detail)
        ));
    }

    /** OpenAI's "no credit left", which comes as a 429 like a rate limit. (5.0.4) */
    private static function outOfCredit($jsst_body) {
        foreach (array('code', 'type') as $jsst_field) {
            if (isset($jsst_body['error'][$jsst_field]) && $jsst_body['error'][$jsst_field] === 'insufficient_quota') {
                return true;
            }
        }
        return false;
    }

    /* ------------------------------------------------------------------ *
     * Zywrap
     * ------------------------------------------------------------------ */

    /**
     * Ask the hosted engine.
     *
     * Zywrap answers a server-sent-event stream and takes a wrapper code rather
     * than a system prompt, so it does not fit the build/read pair the other
     * engines share. Rather than bend the shared path around it, the call goes
     * through the Zywrap module's own callZywrapEngine() — code that has been in
     * production against that API and is deliberately left untouched.
     *
     * The wrapper is chosen by whether the caller wants fields or prose, which is
     * the same distinction the schema makes for the others.
     */
    public static function askZywrap($jsst_call, $jsst_id) {
        $jsst_key = self::apiKey($jsst_id);
        $jsst_wrapper = !empty($jsst_call['schema'])
            ? 'csr_ticket_thread_reply_composer_14380d'
            : 'csr_instant_knowledge_answer_base';
        $jsst_wrapper = apply_filters('jsst_ai_zywrap_wrapper', $jsst_wrapper, $jsst_call);

        $jsst_prompt = isset($jsst_call['system']) && $jsst_call['system'] !== ''
            ? $jsst_call['system'] . "\n\n" . $jsst_call['prompt']
            : $jsst_call['prompt'];

        /* Zywrap takes a wrapper code rather than a schema, so the fields the
           caller needs never reached it and it answered in its wrapper's own
           format - a formatted report with a table - which JSSTcopilot could
           not read, so triage stored nothing. The fields are asked for in
           words instead, and JSSTcopilot::fieldsFrom() reads the answer
           tolerantly in case the wrapper decorates it anyway. */
        if (!empty($jsst_call['schema']['properties']) && is_array($jsst_call['schema']['properties'])) {
            $jsst_keys = array();
            foreach ($jsst_call['schema']['properties'] AS $jsst_name => $jsst_def) {
                $jsst_keys[] = '"' . $jsst_name . '"'
                    . (!empty($jsst_def['description']) ? ' (' . $jsst_def['description'] . ')' : '');
            }
            $jsst_prompt .= "\n\nAnswer with ONLY one JSON object and nothing else - no headings, no table, no markdown, no code fence. "
                . 'Its keys must be exactly: ' . implode('; ', $jsst_keys) . '.';
        }

        $jsst_model = JSSTincluder::getJSModel('zywrap');
        if (!$jsst_model || !method_exists($jsst_model, 'callZywrapEngine')) {
            return new WP_Error('jsst_ai_zywrap_missing', esc_html(__('The Zywrap engine is not available on this site.', 'js-support-ticket')));
        }

        /* A caller that knows which Zywrap wrapper fits, and has its inputs,
           says so in 'zywrap': the wrapper code, its variables, and which
           output contract to translate back. The AI Agent's answers use
           instant_knowledge_answer this way - the question and the help
           passages as separate inputs, docs only - instead of everything
           pasted into one prompt for a reply composer to decorate. (Already
           redacted in ask().) */
        $jsst_hint = (!empty($jsst_call['zywrap']) && is_array($jsst_call['zywrap'])) ? $jsst_call['zywrap'] : array();
        $jsst_payload = array('wrapper_code' => $jsst_wrapper, 'prompt' => $jsst_prompt);
        if (!empty($jsst_hint['wrapper'])) {
            $jsst_payload['wrapper_code'] = (string) $jsst_hint['wrapper'];
            $jsst_payload['prompt']       = isset($jsst_hint['prompt']) ? (string) $jsst_hint['prompt'] : '';
            if (!empty($jsst_hint['variables']) && is_array($jsst_hint['variables'])) {
                $jsst_payload['variables'] = $jsst_hint['variables'];
            }
        }
        /* The model behind Zywrap, when the site picked one; otherwise Zywrap
           decides. The Zywrap screen's default model, so one setting covers
           every Zywrap request the product makes. */
        $jsst_zmodel = trim((string) get_option('jsst_zywrap_default_model', ''));
        if ($jsst_zmodel !== '') {
            $jsst_payload['model'] = $jsst_zmodel;
        }

        $jsst_output = $jsst_model->callZywrapEngine($jsst_key, $jsst_payload);

        if (empty($jsst_output)) {
            // The reason, when the engine gave one; it is also in System Errors.
            $jsst_reason = (isset($jsst_model->jsst_lasterror) && $jsst_model->jsst_lasterror !== '')
                ? $jsst_model->jsst_lasterror
                : __('The Zywrap engine returned nothing. Details are under System > System Errors.', 'js-support-ticket');
            $jsst_usage = !empty($jsst_model->jsst_lastusage) ? $jsst_model->jsst_lastusage : array();

            /* Which kind of nothing decides whether the spare engine is tried.
               Not reached, or an answer with nothing in it: the spare may do
               better. Zywrap refusing (no credit, unknown wrapper): mapped by
               its status, so only "busy" is worth a second engine. */
            $jsst_kind = isset($jsst_model->jsst_lastkind) ? $jsst_model->jsst_lastkind : '';
            $jsst_status = isset($jsst_model->jsst_lastcode) ? (int) $jsst_model->jsst_lastcode : 0;
            if ($jsst_kind === 'transport') {
                return new WP_Error('jsst_ai_unreachable', esc_html($jsst_reason), $jsst_usage);
            }
            if ($jsst_kind === 'api') {
                if ($jsst_status === 429) {
                    return new WP_Error('jsst_ai_ratelimited', esc_html($jsst_reason), $jsst_usage);
                }
                if ($jsst_status === 408 || self::busy($jsst_status)) {
                    return new WP_Error('jsst_ai_overloaded', esc_html($jsst_reason), $jsst_usage);
                }
                return new WP_Error('jsst_ai_zywrap_failed', esc_html($jsst_reason), $jsst_usage);
            }
            if ($jsst_kind === 'policy') {
                return new WP_Error('jsst_ai_lane_closed', esc_html($jsst_reason));
            }
            return new WP_Error('jsst_ai_zywrap_empty', esc_html($jsst_reason), $jsst_usage);
        }

        /* Structured wrappers can answer with the fields already decoded. The
           callers read text, so those go back as the JSON they were; anything
           else is not an answer. */
        if (is_array($jsst_output) || is_object($jsst_output)) {
            $jsst_output = wp_json_encode($jsst_output);
        }
        if (!is_string($jsst_output)) {
            return new WP_Error('jsst_ai_unreadable', esc_html(__('The AI provider sent something this could not read.', 'js-support-ticket')));
        }

        if (isset($jsst_hint['contract']) && $jsst_hint['contract'] === 'knowledge_answer') {
            $jsst_output = self::fromKnowledgeAnswer($jsst_output,
                isset($jsst_hint['variables']['documentationSnippets']) ? $jsst_hint['variables']['documentationSnippets'] : array());
        } elseif (isset($jsst_hint['contract']) && $jsst_hint['contract'] === 'plain') {
            // Shown as plain text (Copilot uses textContent): Markdown's marks would show as stars and hashes.
            $jsst_output = preg_replace(array('/^\s{0,3}#{1,6}\s*/m', '/\*\*(.+?)\*\*/s', '/__(.+?)__/s', '/^\s*[-*]\s+/m'), array('', '$1', '$1', '- '), $jsst_output);
            $jsst_output = trim(preg_replace("/\n{3,}/", "\n\n", $jsst_output));
        }

        /* Token counts only when the response carries them. Reporting 0 made
           every call look free: Usage & Cost showed $0 and no budget limit could
           ever trip. Left out, the call is logged as "tokens unknown", which is
           the truth when the proxy does not say. */
        $jsst_out = array(
            'text'      => trim($jsst_output),
            'truncated' => false,
            // The model Zywrap ran it on, for the meter; "zywrap" when it did not say.
            'model'     => (isset($jsst_model->jsst_lastmodel) && $jsst_model->jsst_lastmodel !== '') ? $jsst_model->jsst_lastmodel : 'zywrap',
        );
        if (!empty($jsst_model->jsst_lastusage)) {
            $jsst_out['intokens']  = (int) $jsst_model->jsst_lastusage['intokens'];
            $jsst_out['outtokens'] = (int) $jsst_model->jsst_lastusage['outtokens'];
        }
        return $jsst_out;
    }

    /**
     * instant_knowledge_answer's output, as the answer envelope the AI Agent
     * reads: reply, confidence 0-100, reasoning, sources.
     *
     * Its own verdicts decide the confidence, so its rules hold here too: an
     * answer it calls unsafe to send, or one it says needs a person, can be a
     * draft at most; "insufficient" documentation is a hand-off. Its field
     * names arrive in either case (safeToSend / safetosend), so they are read
     * case-blind. Anything that is not its contract is passed on untouched.
     */
    private static function fromKnowledgeAnswer($jsst_text, $jsst_given = array()) {
        // Decoded as it came (unwrapText() would reduce it to the answer alone), or from inside a code fence.
        $jsst_raw = json_decode(trim((string) $jsst_text), true);
        if (!is_array($jsst_raw) && preg_match('/\{.*\}/s', (string) $jsst_text, $jsst_brace)) {
            $jsst_raw = json_decode($jsst_brace[0], true);
        }
        if (!is_array($jsst_raw)) {
            return $jsst_text;
        }
        /* Keys case-blind and space-blind: the proxy has returned
           "matchedsources", "matchedsource references" and
           "miss inginformation" for the same contract. */
        $jsst_j = array();
        foreach ($jsst_raw as $jsst_k => $jsst_v) {
            $jsst_j[preg_replace('/[^a-z]/', '', strtolower((string) $jsst_k))] = $jsst_v;
        }
        foreach (array_keys($jsst_j) as $jsst_k) {
            if (strpos($jsst_k, 'matchedsource') === 0 && !isset($jsst_j['matchedsources'])) $jsst_j['matchedsources'] = $jsst_j[$jsst_k];
            if (strpos($jsst_k, 'missinginformation') === 0 && !isset($jsst_j['missinginformation'])) $jsst_j['missinginformation'] = $jsst_j[$jsst_k];
        }
        if (!isset($jsst_j['answer'])) {
            return $jsst_text;
        }

        $jsst_words = array('high' => 90, 'medium' => 60, 'low' => 30);
        $jsst_conf  = isset($jsst_j['confidence']) ? strtolower(trim((string) $jsst_j['confidence'])) : '';
        $jsst_conf  = isset($jsst_words[$jsst_conf]) ? $jsst_words[$jsst_conf] : (is_numeric($jsst_conf) ? (int) $jsst_conf : 0);
        $jsst_mode  = isset($jsst_j['sourcemodeused']) ? strtolower((string) $jsst_j['sourcemodeused']) : '';
        if ($jsst_mode === 'insufficient') {
            $jsst_conf = 0;
        } elseif ((isset($jsst_j['safetosend']) && $jsst_j['safetosend'] === false)
                || !empty($jsst_j['needsescalation'])) {
            $jsst_conf = min($jsst_conf, 60);
        }

        // The passages were given as "[KB-n] title: text"; the refs are read back from what it matched.
        $jsst_sources = array();
        foreach ((array) (isset($jsst_j['matchedsources']) ? $jsst_j['matchedsources'] : array()) as $jsst_m) {
            if (preg_match_all('/KB-\d+/', is_scalar($jsst_m) ? (string) $jsst_m : wp_json_encode($jsst_m), $jsst_refs)) {
                $jsst_sources = array_merge($jsst_sources, $jsst_refs[0]);
            }
        }
        $jsst_reasons = array();
        /* The proxy sometimes leaves matchedSources out altogether - the same
           request, twice, carried it once. An answer that names no source may
           still be a good draft, so it cites what it was given; but it never
           goes to a customer on its own word. */
        if (empty($jsst_sources)) {
            foreach ((array) $jsst_given as $jsst_doc) {
                if (preg_match('/^\[(KB-\d+)\]/', (string) $jsst_doc, $jsst_ref)) $jsst_sources[] = $jsst_ref[1];
            }
            $jsst_conf = min($jsst_conf, 60);
            $jsst_reasons[] = 'Zywrap named no source, so this can be a draft only';
        }
        if (!empty($jsst_j['escalationreason'])) $jsst_reasons[] = (string) $jsst_j['escalationreason'];
        if (!empty($jsst_j['missinginformation'])) {
            $jsst_reasons[] = 'Missing: ' . (is_array($jsst_j['missinginformation'])
                ? implode('; ', array_map('strval', array_filter($jsst_j['missinginformation'], 'is_scalar')))
                : (string) $jsst_j['missinginformation']);
        }
        if ($jsst_mode !== '') $jsst_reasons[] = 'Zywrap source mode: ' . $jsst_mode;

        // Plain text in, paragraphs out; the internal refs are not for the customer.
        $jsst_answer = trim(preg_replace('/\s*\(?\[?KB-\d+\]?(?:\s*,\s*\[?KB-\d+\]?)*\)?/', '', (string) $jsst_j['answer']));
        $jsst_paras  = array_filter(array_map('trim', preg_split('/\n{2,}/', $jsst_answer)));
        $jsst_reply  = '';
        foreach ($jsst_paras as $jsst_p) {
            $jsst_reply .= '<p>' . nl2br(esc_html($jsst_p)) . '</p>';
        }

        return wp_json_encode(array(
            'reply'      => $jsst_reply,
            'confidence' => max(0, min(100, (int) $jsst_conf)),
            'reasoning'  => implode(' | ', $jsst_reasons),
            'sources'    => array_values(array_unique($jsst_sources)),
        ));
    }

    /** Every string in a nested array, through the redaction policy. */
    private static function redactValues($jsst_value) {
        if (is_array($jsst_value)) {
            foreach ($jsst_value as $jsst_k => $jsst_v) {
                $jsst_value[$jsst_k] = self::redactValues($jsst_v);
            }
            return $jsst_value;
        }
        return is_string($jsst_value) ? JSSTaipolicy::redact($jsst_value) : $jsst_value;
    }

    /* ------------------------------------------------------------------ *
     * Anthropic
     * ------------------------------------------------------------------ */

    /**
     * Shape one Messages API request.
     *
     * Three choices here are worth stating, because each of them is a
     * behaviour of this model family rather than a preference:
     *
     * Effort is set per action and is low for most of them. These are short,
     * well-specified jobs — condense this thread, put it in that language — and
     * effort is the lever that keeps them cheap and quick. It is the site
     * owner's money either way, which is the argument for spending it carefully
     * rather than maximally.
     *
     * max_tokens covers the reasoning as well as the reply, so it is set well
     * above the length of any answer expected here. Sized to the visible answer
     * alone, a long thread would come back cut off mid-sentence.
     *
     * A schema is sent when the action wants fields rather than prose, which is
     * what makes "extract the details" produce something a screen can lay out
     * instead of a paragraph that merely looks structured.
     */
    public static function buildAnthropic($jsst_call, $jsst_model, $jsst_key) {
        /* Haiku 4.5 rejects `effort`; every other listed model takes it. */
        $jsst_output = (strpos((string) $jsst_model, 'claude-haiku-4') === 0)
            ? array()
            : array('effort' => isset($jsst_call['effort']) ? $jsst_call['effort'] : 'low');
        if (!empty($jsst_call['schema'])) {
            $jsst_output['format'] = array(
                'type'   => 'json_schema',
                'schema' => self::anthropicSchema($jsst_call['schema']),
            );
        }

        $jsst_body = array(
            'model'         => $jsst_model,
            /* Never below 1024: thinking spends from the same allowance, and a
               small cap meant for a one-word answer ran out mid-thought and
               came back empty. */
            'max_tokens'    => max(1024, isset($jsst_call['maxtokens']) ? (int) $jsst_call['maxtokens'] : 8000),
            'system'        => $jsst_call['system'],
            'messages'      => array(
                array('role' => 'user', 'content' => $jsst_call['prompt']),
            ),
        );

        if (!empty($jsst_output)) {
            $jsst_body['output_config'] = $jsst_output;
        }

        $jsst_headers = array(
            'x-api-key'         => $jsst_key,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        );

        /* If the model's safety classifiers decline a request, this asks the
           provider to answer it on another model rather than handing the agent
           a refusal. Support ticket text almost never trips them, so this is
           insurance rather than a load-bearing part of the feature — and it is
           the one part of this request that rides a beta header, so a site that
           would rather not depend on one can switch it off and get a plain
           refusal message instead. */
        // Haiku models have no server-side fallback, so it is not asked for.
        if (strpos((string) $jsst_model, 'claude-haiku') !== 0
                && apply_filters('jsst_ai_use_fallbacks', apply_filters('jsst_copilot_use_fallbacks', true))) {
            $jsst_body['fallbacks'] = 'default';
            $jsst_headers['anthropic-beta'] = 'server-side-fallback-2026-07-01';
        }

        return array('headers' => $jsst_headers, 'body' => $jsst_body);
    }

    /**
     * A schema in the subset Anthropic's structured output accepts.
     *
     * The callers write ordinary JSON Schema, and the API rejects the whole
     * request - a 400, nothing answered - over a single `maxLength`. So the
     * constraints it does not support are dropped (the reader validates what
     * comes back anyway), every object is closed with additionalProperties
     * false, and every property is listed as required, because structured
     * output fills every field or none.
     */
    private static function anthropicSchema($jsst_schema) {
        if (!is_array($jsst_schema)) {
            return $jsst_schema;
        }
        foreach (array('minimum', 'maximum', 'exclusiveMinimum', 'exclusiveMaximum', 'multipleOf',
                       'minLength', 'maxLength', 'pattern', 'maxItems') as $jsst_key) {
            unset($jsst_schema[$jsst_key]);
        }
        if (isset($jsst_schema['minItems']) && (int) $jsst_schema['minItems'] > 1) {
            unset($jsst_schema['minItems']);
        }
        if (isset($jsst_schema['format']) && !in_array($jsst_schema['format'],
                array('date-time', 'time', 'date', 'duration', 'email', 'hostname', 'uri', 'ipv4', 'ipv6', 'uuid'), true)) {
            unset($jsst_schema['format']);
        }

        if (isset($jsst_schema['properties']) && is_array($jsst_schema['properties'])) {
            foreach ($jsst_schema['properties'] as $jsst_name => $jsst_sub) {
                $jsst_schema['properties'][$jsst_name] = self::anthropicSchema($jsst_sub);
            }
        }
        if (isset($jsst_schema['items']) && is_array($jsst_schema['items'])) {
            $jsst_schema['items'] = (array_keys($jsst_schema['items']) === range(0, count($jsst_schema['items']) - 1))
                ? array_map(array(__CLASS__, 'anthropicSchema'), $jsst_schema['items'])
                : self::anthropicSchema($jsst_schema['items']);
        }
        foreach (array('anyOf', 'allOf', '$defs', 'definitions') as $jsst_key) {
            if (isset($jsst_schema[$jsst_key]) && is_array($jsst_schema[$jsst_key])) {
                foreach ($jsst_schema[$jsst_key] as $jsst_index => $jsst_sub) {
                    $jsst_schema[$jsst_key][$jsst_index] = self::anthropicSchema($jsst_sub);
                }
            }
        }

        $jsst_type = isset($jsst_schema['type']) ? $jsst_schema['type'] : '';
        $jsst_isobject = ($jsst_type === 'object' || (is_array($jsst_type) && in_array('object', $jsst_type, true))
            || isset($jsst_schema['properties']));
        if ($jsst_isobject) {
            $jsst_schema['additionalProperties'] = false;
            $jsst_names = (isset($jsst_schema['properties']) && is_array($jsst_schema['properties']))
                ? array_map('strval', array_keys($jsst_schema['properties'])) : array();
            if (empty($jsst_names)) {
                // An empty PHP array would be sent as [] where an object is expected.
                $jsst_schema['properties'] = new stdClass();
            } else {
                $jsst_required = (isset($jsst_schema['required']) && is_array($jsst_schema['required']))
                    ? array_map('strval', $jsst_schema['required']) : array();
                if (array_diff($jsst_names, $jsst_required)) {
                    $jsst_schema['required'] = $jsst_names;
                }
            }
        }
        return $jsst_schema;
    }

    /**
     * Read one Messages API reply.
     *
     * Two things are checked before the text: whether the model declined, and
     * whether the answer was cut short. Both come back as a perfectly successful
     * response, and both produce a wrong or half-finished draft if the content
     * is simply read out — which on a screen that writes replies to customers is
     * exactly the failure worth spending a few lines to avoid.
     */
    public static function readAnthropic($jsst_body) {
        /* The usage goes on the errors too. A refusal or an answer that was
           all thinking is still billed, and an error without its tokens was
           recorded on the meter as costing nothing. */
        $jsst_usage = array();
        if (isset($jsst_body['usage']['input_tokens'])) {
            $jsst_usage['intokens'] = (int) $jsst_body['usage']['input_tokens'];
        }
        if (isset($jsst_body['usage']['output_tokens'])) {
            $jsst_usage['outtokens'] = (int) $jsst_body['usage']['output_tokens'];
        }

        $jsst_stop = isset($jsst_body['stop_reason']) ? $jsst_body['stop_reason'] : '';
        if ($jsst_stop === 'refusal') {
            return new WP_Error('jsst_ai_refused', esc_html(__('The model declined to answer this one. Nothing was written.', 'js-support-ticket')), $jsst_usage);
        }

        $jsst_text = '';
        foreach ((array) (isset($jsst_body['content']) ? $jsst_body['content'] : array()) as $jsst_block) {
            // Reasoning arrives as its own kind of block and is not the answer;
            // only text blocks are.
            if (isset($jsst_block['type']) && $jsst_block['type'] === 'text' && isset($jsst_block['text'])) {
                $jsst_text .= $jsst_block['text'];
            }
        }
        $jsst_text = trim($jsst_text);
        if ($jsst_text === '') {
            return new WP_Error('jsst_ai_empty', esc_html(__('The model returned nothing. Try again, or try a shorter ticket.', 'js-support-ticket')), $jsst_usage);
        }

        return array(
            'text'      => $jsst_text,
            'truncated' => ($jsst_stop === 'max_tokens'),
            'model'     => isset($jsst_body['model']) ? (string) $jsst_body['model'] : '',
            'intokens'  => isset($jsst_body['usage']['input_tokens']) ? (int) $jsst_body['usage']['input_tokens'] : 0,
            'outtokens' => isset($jsst_body['usage']['output_tokens']) ? (int) $jsst_body['usage']['output_tokens'] : 0,
        );
    }

    /* ------------------------------------------------------------------ *
     * OpenAI
     * ------------------------------------------------------------------ */

    /**
     * Shape one Chat Completions request for OpenAI. (5.0.4)
     *
     * Not the local builder, which is written for the runtimes people host
     * themselves. The current OpenAI models reason before they answer, and
     * that changes four things: the instructions go in a `developer` message,
     * the cap is `max_completion_tokens` and includes the reasoning, so it is
     * the visible answer plus room to think (only the tokens used are billed),
     * `temperature` is left at the model's own, and the effort the caller asks
     * for is passed on. Fields come back under a strict schema, so the text
     * is the JSON itself. `store` is off: nothing is kept by OpenAI for later
     * viewing.
     */
    public static function buildOpenAi($jsst_call, $jsst_model, $jsst_key) {
        $jsst_messages = array();
        if (!empty($jsst_call['system'])) {
            $jsst_messages[] = array('role' => 'developer', 'content' => $jsst_call['system']);
        }
        $jsst_messages[] = array('role' => 'user', 'content' => $jsst_call['prompt']);

        // Every listed model takes these three; the callers ask for low or medium.
        $jsst_effort = isset($jsst_call['effort']) ? (string) $jsst_call['effort'] : 'low';
        if (!in_array($jsst_effort, array('low', 'medium', 'high'), true)) {
            $jsst_effort = in_array($jsst_effort, array('xhigh', 'max'), true) ? 'high' : 'low';
        }

        $jsst_visible = isset($jsst_call['maxtokens']) ? max(1, (int) $jsst_call['maxtokens']) : 8000;
        $jsst_body = array(
            'model'                 => $jsst_model,
            'messages'              => $jsst_messages,
            'max_completion_tokens' => min(32000, $jsst_visible + 4000),
            'reasoning_effort'      => $jsst_effort,
            'store'                 => false,
        );
        if (!empty($jsst_call['schema'])) {
            $jsst_body['response_format'] = array(
                'type'        => 'json_schema',
                'json_schema' => array(
                    'name'   => 'answer',
                    'strict' => true,
                    'schema' => self::openaiSchema($jsst_call['schema']),
                ),
            );
        }

        return array(
            'headers' => array(
                'authorization' => 'Bearer ' . trim((string) $jsst_key),
                'content-type'  => 'application/json',
            ),
            'body'    => $jsst_body,
        );
    }

    /**
     * A schema in the subset OpenAI's strict mode accepts.
     *
     * The same rules as Anthropic's - closed objects, every field required -
     * with one format fewer: OpenAI has no `uri`.
     */
    private static function openaiSchema($jsst_schema) {
        return self::withoutUri(self::anthropicSchema($jsst_schema));
    }

    private static function withoutUri($jsst_schema) {
        if (!is_array($jsst_schema)) {
            return $jsst_schema;
        }
        if (isset($jsst_schema['format']) && $jsst_schema['format'] === 'uri') {
            unset($jsst_schema['format']);
        }
        // A schema here is an array with string keys; a list holds schemas.
        foreach ($jsst_schema as $jsst_key => $jsst_sub) {
            if (is_array($jsst_sub) && in_array($jsst_key, array('properties', 'items', 'anyOf', '$defs', 'definitions'), true)) {
                $jsst_schema[$jsst_key] = ($jsst_key === 'items' && !isset($jsst_sub[0]))
                    ? self::withoutUri($jsst_sub)
                    : array_map(array(__CLASS__, 'withoutUri'), $jsst_sub);
            }
        }
        return $jsst_schema;
    }

    /**
     * Read one Chat Completions reply from OpenAI.
     *
     * As with Anthropic, a refusal and a cut-off answer both arrive as a
     * successful response, so they are checked before the text. A reasoning
     * model that spends the whole cap thinking returns no text at all, with
     * finish_reason "length"; that is billed too, so the usage travels on the
     * error.
     */
    public static function readOpenAi($jsst_body) {
        $jsst_usage = array();
        if (isset($jsst_body['usage']['prompt_tokens'])) {
            $jsst_usage['intokens'] = (int) $jsst_body['usage']['prompt_tokens'];
        }
        // Reasoning tokens are part of completion_tokens, and billed as output.
        if (isset($jsst_body['usage']['completion_tokens'])) {
            $jsst_usage['outtokens'] = (int) $jsst_body['usage']['completion_tokens'];
        }

        $jsst_choice = isset($jsst_body['choices'][0]) ? $jsst_body['choices'][0] : array();
        $jsst_finish = isset($jsst_choice['finish_reason']) ? (string) $jsst_choice['finish_reason'] : '';
        if (!empty($jsst_choice['message']['refusal']) || $jsst_finish === 'content_filter') {
            return new WP_Error('jsst_ai_refused', esc_html(__('The model declined to answer this one. Nothing was written.', 'js-support-ticket')), $jsst_usage);
        }

        $jsst_text = isset($jsst_choice['message']['content']) ? trim((string) $jsst_choice['message']['content']) : '';
        if ($jsst_text === '') {
            return new WP_Error('jsst_ai_empty', esc_html(__('The model returned nothing. Try again, or try a shorter ticket.', 'js-support-ticket')), $jsst_usage);
        }

        return array(
            'text'      => $jsst_text,
            'truncated' => ($jsst_finish === 'length'),
            'model'     => isset($jsst_body['model']) ? (string) $jsst_body['model'] : '',
            'intokens'  => isset($jsst_usage['intokens']) ? $jsst_usage['intokens'] : 0,
            'outtokens' => isset($jsst_usage['outtokens']) ? $jsst_usage['outtokens'] : 0,
        );
    }

    /* ------------------------------------------------------------------ *
     * Local models
     * ------------------------------------------------------------------ */

    /**
     * Shape one OpenAI-shaped chat completion. (Roadmap 6.0-AI-08)
     *
     * This shape rather than Ollama's own /api/generate, because every runtime
     * somebody is likely to already have — Ollama, LM Studio, vLLM, llama.cpp's
     * server, LocalAI, text-generation-webui — speaks it, and picking the
     * vendor-specific one would support exactly one of them.
     *
     * The schema is sent as response_format json_object rather than a strict
     * schema: support for structured output varies enormously between local
     * runtimes and a request a runtime does not understand is usually a 400
     * rather than a graceful downgrade. Asking for JSON and validating what
     * comes back is the version that works everywhere.
     *
     * Authorization is sent only when a token is stored. A bare Bearer header
     * with nothing after it makes some gateways answer 401, which reads as a
     * rejected key on a setup that never had one.
     */
    public static function buildOpenAiChat($jsst_call, $jsst_model, $jsst_key) {
        if (trim((string) $jsst_model) === '') {
            return new WP_Error('jsst_ai_nomodel', esc_html(__('No local model name is set. Add the name your server answers to, such as llama3.1:8b.', 'js-support-ticket')));
        }

        $jsst_messages = array();
        if (!empty($jsst_call['system'])) {
            $jsst_messages[] = array('role' => 'system', 'content' => $jsst_call['system']);
        }
        $jsst_messages[] = array('role' => 'user', 'content' => $jsst_call['prompt']);

        $jsst_body = array(
            'model'       => $jsst_model,
            'messages'    => $jsst_messages,
            'max_tokens'  => isset($jsst_call['maxtokens']) ? (int) $jsst_call['maxtokens'] : 2000,
            'temperature' => 0.2,
            'stream'      => false,
        );
        if (!empty($jsst_call['schema'])) {
            $jsst_body['response_format'] = array('type' => 'json_object');
        }

        $jsst_headers = array('content-type' => 'application/json');
        if (trim((string) $jsst_key) !== '') {
            $jsst_headers['authorization'] = 'Bearer ' . trim((string) $jsst_key);
        }

        return array('headers' => $jsst_headers, 'body' => $jsst_body);
    }

    /**
     * Read one OpenAI-shaped chat completion.
     *
     * Local runtimes are less consistent than a hosted API about what they put
     * where, so the two fields that actually matter are read defensively: some
     * return the text under message.content and some under a bare content, and
     * usage is frequently absent entirely rather than zero.
     */
    public static function readOpenAiChat($jsst_body) {
        $jsst_choice = isset($jsst_body['choices'][0]) ? $jsst_body['choices'][0] : array();

        $jsst_text = '';
        if (isset($jsst_choice['message']['content'])) {
            $jsst_text = (string) $jsst_choice['message']['content'];
        } elseif (isset($jsst_choice['text'])) {
            $jsst_text = (string) $jsst_choice['text'];
        }
        $jsst_text = trim($jsst_text);

        if ($jsst_text === '') {
            return new WP_Error('jsst_ai_empty', esc_html(__('The local model returned nothing. Check the model name matches one your server has loaded.', 'js-support-ticket')));
        }

        $jsst_finish = isset($jsst_choice['finish_reason']) ? (string) $jsst_choice['finish_reason'] : '';

        return array(
            'text'      => $jsst_text,
            'truncated' => ($jsst_finish === 'length'),
            'model'     => isset($jsst_body['model']) ? (string) $jsst_body['model'] : '',
            'intokens'  => isset($jsst_body['usage']['prompt_tokens']) ? (int) $jsst_body['usage']['prompt_tokens'] : 0,
            'outtokens' => isset($jsst_body['usage']['completion_tokens']) ? (int) $jsst_body['usage']['completion_tokens'] : 0,
        );
    }

    /**
     * Ask the configured engine to say one word back, for the settings screen.
     *
     * A test that goes through ask() rather than its own request, so a green
     * tick means the path a real reply takes works — including the lane check
     * and the scrub — rather than that a different request to the same host
     * worked. (Same rule as the connector framework's test(), 5.5-CH-04.)
     */
    public static function selfTest() {
        $jsst_started = microtime(true);
        $jsst_answer = self::ask(array(
            'system'    => 'You are a connection test. Answer with one word.',
            'prompt'    => 'Reply with the single word: ready',
            'maxtokens' => 200,
            'effort'    => 'low',
            'feature'   => 'test',
        ));
        $jsst_ms = (int) round((microtime(true) - $jsst_started) * 1000);

        if (is_wp_error($jsst_answer)) {
            return array('ok' => false, 'ms' => $jsst_ms, 'detail' => $jsst_answer->get_error_message());
        }
        return array(
            'ok'     => true,
            'ms'     => $jsst_ms,
            'detail' => wp_trim_words((string) $jsst_answer['text'], 12, '…'),
        );
    }

}
