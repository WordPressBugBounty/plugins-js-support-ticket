<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTcopilotprovider')) {
    return;
}

/**
 * Where the AI Copilot's text actually comes from. (Roadmap 4.0-AI-01)
 *
 * The Copilot is free, and free here means something specific: this plugin does
 * not pay for anybody's inference and does not stand between a site and its
 * model provider. The site owner brings their own key, the request goes from
 * their server to the provider they chose, and the answer comes back. There is
 * no account to open with us, no quota to buy from us, and no proxy of ours in
 * the middle holding their customers' tickets.
 *
 * That is the whole reason this class exists separately from the actions above
 * it. An action says what it wants written; this says how to ask for it. Keeping
 * them apart is what makes "bring your own key" true rather than aspirational —
 * a provider is a definition, not a branch in the middle of the prompt builder,
 * and a site that needs a different one adds it through the filter below instead
 * of waiting for us to ship it.
 *
 * Requests go out over wp_remote_post rather than a vendored SDK. That is a
 * deliberate constraint of shipping to WordPress: this plugin is installed from
 * a zip alongside dozens of others, there is no composer autoloader to rely on,
 * and a vendored client library is the classic way for two plugins to load two
 * versions of the same namespace and take a site down. Every other outbound call
 * in this plugin goes through the same WordPress HTTP layer, which also means a
 * site's existing proxy, timeout and TLS configuration applies here too.
 */
class JSSTcopilotprovider {

    /** Which provider a site has configured, its key, and the model to ask for. */
    const OPT_PROVIDER = 'jsst_copilot_provider';
    const OPT_KEY      = 'jsst_copilot_api_key';
    const OPT_MODEL    = 'jsst_copilot_model';

    /** Seconds to wait for a reply before giving up on it. */
    const TIMEOUT = 90;

    /* ------------------------------------------------------------------ *
     * Who can be asked
     * ------------------------------------------------------------------ */

    /**
     * The providers this knows how to talk to.
     *
     * One is implemented and tested. The filter is not a placeholder for
     * ambition — it is the seam that makes the promise above keepable, so a site
     * with a provider of its own (an internal gateway, a regional endpoint, a
     * model this has never heard of) can be served without a fork. A provider is
     * four things: where to post, how to authenticate, how to shape a request,
     * and how to read the answer.
     */
    public static function providers() {
        $jsst_providers = array(
            'anthropic' => array(
                'label'    => 'Anthropic',
                'endpoint' => 'https://api.anthropic.com/v1/messages',
                'models'   => array(
                    'claude-opus-5'    => 'Claude Opus 5',
                    'claude-sonnet-5'  => 'Claude Sonnet 5',
                    'claude-haiku-4-5' => 'Claude Haiku 4.5',
                ),
                'default_model' => 'claude-opus-5',
                'build'         => array(__CLASS__, 'buildAnthropic'),
                'read'          => array(__CLASS__, 'readAnthropic'),
                'keyhint'       => esc_html(__('Starts with sk-ant-. Created at console.anthropic.com.', 'js-support-ticket')),
            ),
        );
        return apply_filters('jsst_copilot_providers', $jsst_providers);
    }

    public static function current() {
        $jsst_providers = self::providers();
        $jsst_key = (string) get_option(self::OPT_PROVIDER, 'anthropic');
        if (!isset($jsst_providers[$jsst_key])) {
            $jsst_key = key($jsst_providers);
        }
        return array('key' => $jsst_key, 'def' => $jsst_providers[$jsst_key]);
    }

    /**
     * The model to ask for.
     *
     * Falls back to the provider's default rather than to whatever was saved
     * last: a site that switches provider has a model name in the option that
     * means nothing to the new one, and sending it produces a 404 that reads
     * like a broken plugin rather than a stale setting.
     */
    public static function model() {
        $jsst_current = self::current();
        $jsst_model = trim((string) get_option(self::OPT_MODEL, ''));
        if ($jsst_model !== '' && isset($jsst_current['def']['models'][$jsst_model])) {
            return $jsst_model;
        }
        return $jsst_current['def']['default_model'];
    }

    /** Is there a key to use? Checked before any Copilot control is drawn. */
    public static function configured() {
        return (trim((string) get_option(self::OPT_KEY, '')) !== '');
    }

    /**
     * The key, read only at the moment of use.
     *
     * Never returned to a screen, never put in the debug bundle, never
     * journalled. The System Status redaction (4.0-OPS-02) catches it by name
     * as well, but the rule here is simpler: nothing reads this except the
     * request builder.
     */
    private static function apiKey() {
        return trim((string) get_option(self::OPT_KEY, ''));
    }

    /* ------------------------------------------------------------------ *
     * Asking
     * ------------------------------------------------------------------ */

    /**
     * Run one Copilot action against the configured provider.
     *
     * @param array $jsst_call system, prompt, maxtokens, effort and an optional json schema.
     * @return array|WP_Error text plus whatever the provider reported about the run.
     */
    public static function ask($jsst_call) {
        if (!self::configured()) {
            return new WP_Error('jsst_copilot_nokey', esc_html(__('No AI key is set up yet. Add one on the AI Copilot screen.', 'js-support-ticket')));
        }
        $jsst_current = self::current();
        $jsst_def = $jsst_current['def'];

        $jsst_request = call_user_func($jsst_def['build'], $jsst_call, self::model(), self::apiKey());
        if (is_wp_error($jsst_request)) {
            return $jsst_request;
        }

        $jsst_response = wp_remote_post($jsst_def['endpoint'], array(
            'headers'   => $jsst_request['headers'],
            'body'      => wp_json_encode($jsst_request['body']),
            'timeout'   => self::TIMEOUT,
            'sslverify' => true,
        ));

        if (is_wp_error($jsst_response)) {
            // The site could not reach the provider at all. Worth saying plainly:
            // on shared hosting this is almost always outbound HTTPS being
            // blocked, not anything to do with the key.
            return new WP_Error('jsst_copilot_unreachable', sprintf(
                /* translators: %s: the network error the request failed with */
                esc_html(__('Could not reach the AI provider: %s', 'js-support-ticket')),
                $jsst_response->get_error_message()
            ));
        }

        $jsst_code = (int) wp_remote_retrieve_response_code($jsst_response);
        $jsst_body = json_decode(wp_remote_retrieve_body($jsst_response), true);
        if (!is_array($jsst_body)) {
            return new WP_Error('jsst_copilot_unreadable', esc_html(__('The AI provider sent something this could not read.', 'js-support-ticket')));
        }
        if ($jsst_code !== 200) {
            return self::httpError($jsst_code, $jsst_body);
        }
        return call_user_func($jsst_def['read'], $jsst_body);
    }

    /**
     * Turn a provider's error into something an agent can act on.
     *
     * The status code is the useful part and the provider's own message is
     * usually written for a developer, so the code decides what is said and the
     * message is kept only where it adds something.
     */
    private static function httpError($jsst_code, $jsst_body) {
        $jsst_detail = '';
        if (isset($jsst_body['error']['message'])) {
            $jsst_detail = (string) $jsst_body['error']['message'];
        }
        switch ($jsst_code) {
            case 401:
            case 403:
                return new WP_Error('jsst_copilot_auth', esc_html(__('The AI provider rejected the key. Check it on the AI Copilot screen.', 'js-support-ticket')));
            case 404:
                return new WP_Error('jsst_copilot_model', esc_html(__('The provider does not offer the model this is set to use. Pick another on the AI Copilot screen.', 'js-support-ticket')));
            case 429:
                return new WP_Error('jsst_copilot_ratelimited', esc_html(__('The AI provider is rate limiting this site. Wait a moment and try again.', 'js-support-ticket')));
            case 529:
            case 500:
            case 502:
            case 503:
                return new WP_Error('jsst_copilot_overloaded', esc_html(__('The AI provider is unavailable right now. Nothing was changed — try again shortly.', 'js-support-ticket')));
        }
        return new WP_Error('jsst_copilot_failed', sprintf(
            /* translators: 1: HTTP status code, 2: the provider's own error message */
            esc_html(__('The AI provider refused the request (%1$d). %2$s', 'js-support-ticket')),
            $jsst_code,
            esc_html($jsst_detail)
        ));
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
        if (apply_filters('jsst_copilot_use_fallbacks', true)) {
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
            return new WP_Error('jsst_copilot_refused', esc_html(__('The model declined to answer this one. Nothing was written.', 'js-support-ticket')));
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
            return new WP_Error('jsst_copilot_empty', esc_html(__('The model returned nothing. Try again, or try a shorter ticket.', 'js-support-ticket')));
        }

        return array(
            'text'      => $jsst_text,
            'truncated' => ($jsst_stop === 'max_tokens'),
            'model'     => isset($jsst_body['model']) ? (string) $jsst_body['model'] : '',
            'intokens'  => isset($jsst_body['usage']['input_tokens']) ? (int) $jsst_body['usage']['input_tokens'] : 0,
            'outtokens' => isset($jsst_body['usage']['output_tokens']) ? (int) $jsst_body['usage']['output_tokens'] : 0,
        );
    }

}
