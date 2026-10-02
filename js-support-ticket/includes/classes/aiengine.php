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
 * authenticate, how to shape a request, and how to read the answer. Three ship:
 *
 *   zywrap     Our own hosted engine. The default because it is the only one
 *              that works without the site owner opening an account somewhere
 *              else first — a key comes with the licence and there is nothing
 *              to configure. Hosted lane.
 *   anthropic  Bring your own key. Nothing of ours stands between the site and
 *              the provider: their server, their key, their bill, their data
 *              processing agreement. Hosted lane.
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

    /** Seconds to wait for a hosted reply before giving up on it. */
    const TIMEOUT = 90;

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
                'blurb'         => esc_html(__('Our own engine. Comes with your licence — no account to open and no key to find.', 'js-support-ticket')),
                'recommended'   => true,
                'endpoint'      => 'https://api.zywrap.com/v1/proxy',
                'models'        => array(),
                'default_model' => '',
                'keyhint'       => esc_html(__('Issued with your JS Help Desk licence.', 'js-support-ticket')),
                'ask'           => array(__CLASS__, 'askZywrap'),
            ),
            'anthropic' => array(
                'label'    => 'Anthropic',
                'lane'     => 'hosted',
                'blurb'    => esc_html(__('Bring your own key. The request goes from your server to Anthropic — nothing of ours is in the middle.', 'js-support-ticket')),
                'endpoint' => 'https://api.anthropic.com/v1/messages',
                'models'   => array(
                    /* The default first: with no model saved the list shows its
                       first entry, and saving untouched stores it. */
                    'claude-opus-5'    => 'Claude Opus 5',
                    'claude-opus-5-5'  => 'Claude Opus 5.5',
                    'claude-sonnet-5'  => 'Claude Sonnet 5',
                    'claude-haiku-4-5' => 'Claude Haiku 4.5',
                ),
                'default_model' => 'claude-opus-5',
                'build'         => array(__CLASS__, 'buildAnthropic'),
                'read'          => array(__CLASS__, 'readAnthropic'),
                'keyhint'       => esc_html(__('Starts with sk-ant-. Created at console.anthropic.com.', 'js-support-ticket')),
            ),
            'local' => array(
                'label'    => esc_html(__('Local model', 'js-support-ticket')),
                'lane'     => 'local',
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
        $jsst_model = trim((string) get_option(self::OPT_MODEL, ''));
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
     */
    public static function validLocalEndpoint($jsst_url) {
        $jsst_url = trim((string) $jsst_url);
        if ($jsst_url === '') {
            return true;
        }
        $jsst_scheme = wp_parse_url($jsst_url, PHP_URL_SCHEME);
        $jsst_host   = wp_parse_url($jsst_url, PHP_URL_HOST);
        return (in_array($jsst_scheme, array('http', 'https'), true) && !empty($jsst_host));
    }

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
        }

        /* The context is only built where the meter exists: it names that
           class's constants, and an add-on running against a core without it
           should lose the accounting, not the feature. */
        if (class_exists('JSSTaiusage')) {
            return JSSTaiusage::meter(
                self::meterContext($jsst_current, $jsst_call),
                function () use ($jsst_current, $jsst_def, $jsst_call) {
                    return self::send($jsst_current, $jsst_def, $jsst_call);
                }
            );
        }
        return self::send($jsst_current, $jsst_def, $jsst_call);
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

        $jsst_request = call_user_func(
            $jsst_def['build'],
            $jsst_call,
            self::model($jsst_current['key']),
            self::apiKey($jsst_current['key'])
        );
        if (is_wp_error($jsst_request)) {
            return $jsst_request;
        }

        $jsst_response = wp_remote_post($jsst_endpoint, array(
            'headers'   => $jsst_request['headers'],
            'body'      => wp_json_encode($jsst_request['body']),
            'timeout'   => empty($jsst_def['nokey']) ? self::TIMEOUT : self::localTimeout(),
            'sslverify' => true,
        ));

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

        $jsst_code = (int) wp_remote_retrieve_response_code($jsst_response);
        $jsst_body = json_decode(wp_remote_retrieve_body($jsst_response), true);
        if (!is_array($jsst_body)) {
            return new WP_Error('jsst_ai_unreadable', esc_html(__('The AI provider sent something this could not read.', 'js-support-ticket')));
        }
        if ($jsst_code !== 200) {
            return self::httpError($jsst_code, $jsst_body);
        }
        return call_user_func($jsst_def['read'], $jsst_body);
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
        switch ($jsst_code) {
            case 401:
            case 403:
                return new WP_Error('jsst_ai_auth', esc_html(__('The AI provider rejected the key. Check it on the AI Agent screen.', 'js-support-ticket')));
            case 404:
                return new WP_Error('jsst_ai_model', esc_html(__('The provider does not offer the model this is set to use. Pick another on the AI Agent screen.', 'js-support-ticket')));
            case 429:
                return new WP_Error('jsst_ai_ratelimited', esc_html(__('The AI provider is rate limiting this site. Wait a moment and try again.', 'js-support-ticket')));
            case 529:
            case 500:
            case 502:
            case 503:
                return new WP_Error('jsst_ai_overloaded', esc_html(__('The AI provider is unavailable right now. Nothing was changed — try again shortly.', 'js-support-ticket')));
        }
        return new WP_Error('jsst_ai_failed', sprintf(
            /* translators: 1: HTTP status code, 2: the provider's own error message */
            esc_html(__('The AI provider refused the request (%1$d). %2$s', 'js-support-ticket')),
            $jsst_code,
            esc_html($jsst_detail)
        ));
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

        $jsst_output = $jsst_model->callZywrapEngine($jsst_key, array(
            'wrapper_code' => $jsst_wrapper,
            'prompt'       => $jsst_prompt,
        ));

        if (empty($jsst_output)) {
            // The reason, when the engine gave one; it is also in System Errors.
            $jsst_reason = (isset($jsst_model->jsst_lasterror) && $jsst_model->jsst_lasterror !== '')
                ? $jsst_model->jsst_lasterror
                : __('The Zywrap engine returned nothing. Details are under System > System Errors.', 'js-support-ticket');
            return new WP_Error('jsst_ai_zywrap_empty', esc_html($jsst_reason));
        }

        return array(
            'text'      => is_string($jsst_output) ? trim($jsst_output) : '',
            'truncated' => false,
            'model'     => 'zywrap',
            // The proxy meters its own usage and reports it on its dashboard;
            // reporting a guess here would put two different numbers on two
            // screens of the same product.
            'intokens'  => 0,
            'outtokens' => 0,
        );
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
        $jsst_output = array('effort' => isset($jsst_call['effort']) ? $jsst_call['effort'] : 'low');
        if (!empty($jsst_call['schema'])) {
            $jsst_output['format'] = array(
                'type'   => 'json_schema',
                'schema' => $jsst_call['schema'],
            );
        }

        $jsst_body = array(
            'model'         => $jsst_model,
            'max_tokens'    => isset($jsst_call['maxtokens']) ? (int) $jsst_call['maxtokens'] : 8000,
            'system'        => $jsst_call['system'],
            'output_config' => $jsst_output,
            'messages'      => array(
                array('role' => 'user', 'content' => $jsst_call['prompt']),
            ),
        );

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
        if (apply_filters('jsst_ai_use_fallbacks', apply_filters('jsst_copilot_use_fallbacks', true))) {
            $jsst_body['fallbacks'] = 'default';
            $jsst_headers['anthropic-beta'] = 'server-side-fallback-2026-07-01';
        }

        return array('headers' => $jsst_headers, 'body' => $jsst_body);
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
        $jsst_stop = isset($jsst_body['stop_reason']) ? $jsst_body['stop_reason'] : '';
        if ($jsst_stop === 'refusal') {
            return new WP_Error('jsst_ai_refused', esc_html(__('The model declined to answer this one. Nothing was written.', 'js-support-ticket')));
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
            return new WP_Error('jsst_ai_empty', esc_html(__('The model returned nothing. Try again, or try a shorter ticket.', 'js-support-ticket')));
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
