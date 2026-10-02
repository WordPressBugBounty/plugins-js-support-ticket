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
if (class_exists('JSSTevents')) {
    return;
}

/**
 * Versioned domain events. (Roadmap 4.5-ARCH-03)
 *
 * Add-ons have always found out that something happened by being in the right
 * place when it did: a hook fired mid-function with whatever local variables
 * were to hand, a model patched from outside, a query filtered in flight. That
 * works exactly until the function changes shape, at which point every add-on
 * that reached into it breaks, and nobody finds out until a customer does.
 *
 * An event is the opposite deal. It says what happened in the past tense, with
 * a documented payload and a version number, and promises that the payload will
 * keep the same meaning for as long as that version exists. Subscribers get a
 * contract instead of an invitation to reach inside.
 *
 * Three things ride on this contract, and all of them arrive after 4.5:
 * automation rules and webhooks (v5.0), the notification centre, and reporting.
 * Defining it late would mean rewriting all three, which is why it is here now,
 * before there is anything subscribing.
 *
 * How it is used:
 *
 *     JSSTevents::emit(JSSTevents::TICKET_CREATED, array(
 *         'ticket_id' => 41, 'subject' => 'Card declined', ...
 *     ));
 *
 *     JSSTevents::listen(JSSTevents::TICKET_CREATED, 'my_handler');
 *     function my_handler($jsst_event) { $jsst_event['payload']['ticket_id']; }
 *
 * Every event also fires the WordPress action `jsst_event`, so one subscriber
 * can watch everything - which is what a webhook dispatcher wants.
 *
 * What this class deliberately does not do is deliver anything. Handlers run
 * in-process, on the request that emitted; anything that needs to survive a
 * failure queues itself through JSSTjobs from inside its handler. Retry and
 * delivery guarantees are v5.0's problem, and building half of them here would
 * be the wrong half.
 */
class JSSTevents {

    /* ---------------------------------------------------------------------
     * Event names. Past tense, always: an event is a fact, not a request.
     * ------------------------------------------------------------------ */

    const TICKET_CREATED      = 'ticket.created';
    /** Somebody said yes, or no, to being contacted. (Roadmap 5.5-SEC-02) */
    const CONSENT_RECORDED    = 'consent.recorded';
    const REPLY_ADDED         = 'ticket.reply_added';
    const NOTE_ADDED          = 'ticket.note_added';
    const ASSIGNMENT_CHANGED  = 'ticket.assignment_changed';
    const STATUS_CHANGED      = 'ticket.status_changed';
    const PRIORITY_CHANGED    = 'ticket.priority_changed';
    const DEPARTMENT_CHANGED  = 'ticket.department_changed';
    const TICKET_CLOSED       = 'ticket.closed';
    const TICKET_REOPENED     = 'ticket.reopened';
    const TICKET_MERGED       = 'ticket.merged';
    const TICKET_DELETED      = 'ticket.deleted';
    const SLA_WARNING         = 'sla.warning';
    const SLA_BREACHED        = 'sla.breached';
    const FEEDBACK_RECEIVED   = 'feedback.received';
    const AI_ANSWER_PROPOSED  = 'ai.answer_proposed';
    const AI_ANSWER_SENT      = 'ai.answer_sent';

    /** Where the ring buffer of recent events is kept. */
    const OPT_RECENT = 'jsst_recent_events';

    /** How many events the buffer keeps. Small on purpose — it is a window. */
    const RECENT_LIMIT = 50;

    /** Events emitted this request, oldest first. Read by the inspector. */
    private static $jsst_emitted = array();

    /** Guards against an event handler emitting its way into a loop. */
    private static $jsst_depth = 0;

    /** How deep the chain may go before it is treated as a loop. */
    const MAX_DEPTH = 8;

    /* =====================================================================
     * The catalogue
     * ================================================================== */

    /**
     * Every event, its current version, and what its payload carries.
     *
     *   version   Bumped only when the meaning of an existing key changes or a
     *             key is removed. Adding a key is not a version bump: a
     *             subscriber reading keys it knows is unaffected by new ones,
     *             and treating additions as breaking would mean nobody could
     *             ever add anything.
     *   required  Keys every emission must carry. Emitting without them is a
     *             programming error and is refused, loudly under WP_DEBUG and
     *             quietly otherwise — a missing key would otherwise reach
     *             subscribers as a silent null.
     *   optional  Keys a subscriber may find. Documentation, not enforcement.
     *   legacy    The pre-4.5 hook this event replaces, fired alongside it for
     *             compatibility. Named here rather than at the call site so
     *             that the day it is retired there is one list to read.
     */
    public static function catalogue() {
        static $jsst_catalogue = null;
        if ($jsst_catalogue !== null) {
            return $jsst_catalogue;
        }
        $jsst_catalogue = array(
            self::TICKET_CREATED => array(
                'version'  => 1,
                'label'    => __('A ticket was raised', 'js-support-ticket'),
                'required' => array('ticket_id'),
                'optional' => array('ticketid', 'subject', 'departmentid', 'priorityid', 'helptopicid', 'productid', 'uid', 'email', 'source'),
                'legacy'   => 'jsst-ticketcreate',
            ),
            self::CONSENT_RECORDED => array(
                'version'  => 1,
                'label'    => __('Somebody answered a marketing consent question', 'js-support-ticket'),
                'required' => array('consent_id', 'email', 'granted'),
                'optional' => array('name', 'source', 'statement'),
                'legacy'   => '',
            ),
            self::REPLY_ADDED => array(
                'version'  => 1,
                'label'    => __('Somebody answered', 'js-support-ticket'),
                'required' => array('ticket_id', 'reply_id'),
                'optional' => array('author_kind', 'author_uid', 'is_customer', 'attachments', 'source'),
                'legacy'   => 'jsst-ticketreply',
            ),
            self::NOTE_ADDED => array(
                'version'  => 1,
                'label'    => __('An internal note was written', 'js-support-ticket'),
                'required' => array('ticket_id', 'note_id'),
                'optional' => array('author_uid', 'restricted'),
                'legacy'   => '',
            ),
            self::ASSIGNMENT_CHANGED => array(
                'version'  => 1,
                'label'    => __('A ticket changed hands', 'js-support-ticket'),
                'required' => array('ticket_id', 'staffid'),
                'optional' => array('previous_staffid', 'reason', 'automatic'),
                'legacy'   => 'jsst-assignticket',
            ),
            self::STATUS_CHANGED => array(
                'version'  => 1,
                'label'    => __('A ticket changed status', 'js-support-ticket'),
                'required' => array('ticket_id', 'status'),
                'optional' => array('previous_status', 'reason'),
                'legacy'   => 'jsst-ticketstatuschange',
            ),
            self::PRIORITY_CHANGED => array(
                'version'  => 1,
                'label'    => __('A ticket changed priority', 'js-support-ticket'),
                'required' => array('ticket_id', 'priorityid'),
                'optional' => array('previous_priorityid', 'reason'),
                'legacy'   => '',
            ),
            self::DEPARTMENT_CHANGED => array(
                'version'  => 1,
                'label'    => __('A ticket moved department', 'js-support-ticket'),
                'required' => array('ticket_id', 'departmentid'),
                'optional' => array('previous_departmentid', 'reason'),
                'legacy'   => '',
            ),
            self::TICKET_CLOSED => array(
                'version'  => 1,
                'label'    => __('A ticket was closed', 'js-support-ticket'),
                'required' => array('ticket_id'),
                'optional' => array('closed_by', 'reason', 'automatic'),
                'legacy'   => 'jsst-ticketclose',
            ),
            self::TICKET_REOPENED => array(
                'version'  => 1,
                'label'    => __('A ticket was reopened', 'js-support-ticket'),
                'required' => array('ticket_id'),
                'optional' => array('reason', 'reopened_by'),
                'legacy'   => '',
            ),
            self::TICKET_MERGED => array(
                'version'  => 1,
                'label'    => __('Tickets were merged', 'js-support-ticket'),
                'required' => array('ticket_id', 'merged_into'),
                'optional' => array('note'),
                'legacy'   => '',
            ),
            self::TICKET_DELETED => array(
                'version'  => 1,
                'label'    => __('A ticket was deleted', 'js-support-ticket'),
                'required' => array('ticket_id'),
                'optional' => array('ticketid', 'subject', 'uid'),
                'legacy'   => 'jsst-ticketdelete',
            ),
            self::SLA_WARNING => array(
                'version'  => 1,
                'label'    => __('A ticket is running out of time', 'js-support-ticket'),
                'required' => array('ticket_id', 'due'),
                'optional' => array('policy', 'target', 'minutes_left'),
                'legacy'   => '',
            ),
            self::SLA_BREACHED => array(
                'version'  => 1,
                'label'    => __('A ticket missed its target', 'js-support-ticket'),
                'required' => array('ticket_id', 'due'),
                'optional' => array('policy', 'target', 'minutes_over'),
                'legacy'   => '',
            ),
            self::FEEDBACK_RECEIVED => array(
                'version'  => 1,
                'label'    => __('A customer rated the answer', 'js-support-ticket'),
                'required' => array('ticket_id', 'rating'),
                'optional' => array('comment', 'uid'),
                'legacy'   => '',
            ),
            self::AI_ANSWER_PROPOSED => array(
                'version'  => 1,
                'label'    => __('The AI drafted an answer', 'js-support-ticket'),
                'required' => array('ticket_id'),
                'optional' => array('provider', 'model', 'confidence', 'citations', 'tokens'),
                'legacy'   => '',
            ),
            self::AI_ANSWER_SENT => array(
                'version'  => 1,
                'label'    => __('An AI answer went to the customer', 'js-support-ticket'),
                'required' => array('ticket_id'),
                'optional' => array('reply_id', 'provider', 'model', 'edited_by_agent', 'confidence'),
                'legacy'   => '',
            ),
        );
        /* Modules register their own events here. A module that emits an event
           it has not registered is refused, for the same reason an unknown
           capability action is refused: an undocumented contract is not one. */
        $jsst_catalogue = apply_filters('jsst_event_catalogue', $jsst_catalogue);
        return $jsst_catalogue;
    }

    /** One event's definition with defaults filled in, or false if unknown. */
    public static function definition($jsst_name) {
        $jsst_catalogue = self::catalogue();
        if (!isset($jsst_catalogue[$jsst_name]) || !is_array($jsst_catalogue[$jsst_name])) {
            return false;
        }
        return array_merge(
            array('version' => 1, 'label' => $jsst_name, 'required' => array(), 'optional' => array(), 'legacy' => ''),
            $jsst_catalogue[$jsst_name]
        );
    }

    /* =====================================================================
     * Emitting
     * ================================================================== */

    /**
     * Announce that something happened.
     *
     * @param string $jsst_name    An event name constant.
     * @param array  $jsst_payload The event's own data.
     * @param array  $jsst_context Optional overrides: 'actor' (a resolved actor
     *                             array), 'source' (what triggered it).
     * @return array|false The envelope that was fired, or false if it was not.
     */
    public static function emit($jsst_name, $jsst_payload = array(), $jsst_context = array()) {
        $jsst_def = self::definition($jsst_name);
        if ($jsst_def === false) {
            self::complain(sprintf('JS Help Desk: refused to emit unknown event "%s".', $jsst_name));
            return false;
        }
        if (!is_array($jsst_payload)) {
            $jsst_payload = array();
        }
        foreach ($jsst_def['required'] as $jsst_key) {
            if (!array_key_exists($jsst_key, $jsst_payload)) {
                self::complain(sprintf('JS Help Desk: event "%s" was emitted without its required "%s".', $jsst_name, $jsst_key));
                return false;
            }
        }
        /* A handler that emits, whose handler emits, is a loop that ends in a
           white screen and a mailbox full of notifications. Eight is deep
           enough for any legitimate chain - close triggers feedback triggers a
           notification - and shallow enough to stop a runaway on the request
           that started it rather than on the customer's next page load. */
        if (self::$jsst_depth >= self::MAX_DEPTH) {
            self::complain(sprintf('JS Help Desk: event "%s" was not emitted — events are nested %d deep, which is a loop.', $jsst_name, self::$jsst_depth));
            return false;
        }

        $jsst_actor = isset($jsst_context['actor']) && is_array($jsst_context['actor'])
            ? $jsst_context['actor']
            : (class_exists('JSSTcapability') ? JSSTcapability::actor() : array('kind' => 'unknown', 'wpuid' => (int) get_current_user_id()));

        $jsst_envelope = array(
            'event'    => $jsst_name,
            'version'  => (int) $jsst_def['version'],
            'id'       => self::eventId(),
            'occurred' => gmdate('Y-m-d H:i:s'),
            'site'     => (int) get_current_blog_id(),
            'source'   => isset($jsst_context['source']) ? sanitize_key($jsst_context['source']) : self::source(),
            'actor'    => array(
                'kind'    => isset($jsst_actor['kind']) ? $jsst_actor['kind'] : 'unknown',
                'wpuid'   => isset($jsst_actor['wpuid']) ? (int) $jsst_actor['wpuid'] : 0,
                'uid'     => isset($jsst_actor['uid']) ? (int) $jsst_actor['uid'] : 0,
                'staffid' => isset($jsst_actor['staffid']) ? (int) $jsst_actor['staffid'] : 0,
                'display' => isset($jsst_actor['display']) ? $jsst_actor['display'] : '',
            ),
            'payload'  => $jsst_payload,
        );

        self::$jsst_depth++;
        try {
            self::remember($jsst_envelope);
            /* Named first, so a subscriber to one event runs before the
               catch-all dispatcher that will eventually POST it somewhere. */
            do_action('jsst_event_' . $jsst_name, $jsst_envelope);
            do_action('jsst_event', $jsst_envelope);
            self::fireLegacy($jsst_def, $jsst_envelope);
        } finally {
            self::$jsst_depth--;
            if (self::$jsst_depth < 0) {
                self::$jsst_depth = 0;
            }
        }
        return $jsst_envelope;
    }

    /**
     * Fire the pre-4.5 hook this event replaces.
     *
     * Add-ons in the wild are hooked to these names and will be for as long as
     * legacy add-ons are supported — at least two majors, per the migration
     * commitment. They are fired with the envelope, not with the old argument
     * list, because the old lists were inconsistent (an id here, a whole object
     * there) and reproducing them exactly would mean keeping the old call sites
     * as well. An add-on that needs the old shape gets it from the payload.
     */
    private static function fireLegacy($jsst_def, $jsst_envelope) {
        if (empty($jsst_def['legacy'])) {
            return;
        }
        /* Filterable so a site can switch the compatibility hooks off once it
           knows nothing is listening — on a busy help desk this is two extra
           hook dispatches per ticket action, which is nothing, but the switch
           is what makes the eventual retirement testable. */
        if (!apply_filters('jsst_event_fire_legacy_hooks', true, $jsst_def['legacy'], $jsst_envelope)) {
            return;
        }
        do_action($jsst_def['legacy'] . '_event', $jsst_envelope);
    }

    /**
     * Subscribe. A thin wrapper over add_action, worth having because it is the
     * documented way in and because it keeps the hook naming in one place.
     *
     * @param string   $jsst_name     Event name, or '' for every event.
     * @param callable $jsst_callback Receives the envelope.
     */
    public static function listen($jsst_name, $jsst_callback, $jsst_priority = 10) {
        $jsst_hook = ($jsst_name === '') ? 'jsst_event' : 'jsst_event_' . $jsst_name;
        add_action($jsst_hook, $jsst_callback, (int) $jsst_priority, 1);
    }

    /* =====================================================================
     * The recent window
     * ================================================================== */

    /**
     * Keep the last few events so that "did anything happen when I pressed
     * that?" has an answer.
     *
     * Written to an option rather than a table: fifty rows of small arrays is
     * one autoloaded-sized option, and a table for a debugging window would be
     * a schema migration for every site to carry the retention question. It is
     * deliberately not autoloaded — nothing on a normal request reads it.
     */
    private static function remember($jsst_envelope) {
        self::$jsst_emitted[] = $jsst_envelope;
        if (!apply_filters('jsst_event_record_recent', true, $jsst_envelope)) {
            return;
        }
        $jsst_recent = get_option(self::OPT_RECENT, array());
        if (!is_array($jsst_recent)) {
            $jsst_recent = array();
        }
        /* The payload is trimmed to scalars. A subscriber may add anything to
           an event, including an object with a database handle inside it, and
           serialising that into an option is how a site ends up with an option
           it cannot read back. */
        $jsst_slim = $jsst_envelope;
        $jsst_slim['payload'] = array();
        foreach ($jsst_envelope['payload'] as $jsst_key => $jsst_value) {
            if (is_scalar($jsst_value) || $jsst_value === null) {
                $jsst_slim['payload'][$jsst_key] = $jsst_value;
            } elseif (is_array($jsst_value)) {
                $jsst_slim['payload'][$jsst_key] = wp_json_encode(array_filter($jsst_value, 'is_scalar'));
            }
        }
        $jsst_recent[] = $jsst_slim;
        if (count($jsst_recent) > self::RECENT_LIMIT) {
            $jsst_recent = array_slice($jsst_recent, -self::RECENT_LIMIT);
        }
        update_option(self::OPT_RECENT, $jsst_recent, false);
    }

    /** The recent window, newest first. */
    public static function recent($jsst_limit = 20) {
        $jsst_recent = get_option(self::OPT_RECENT, array());
        if (!is_array($jsst_recent)) {
            return array();
        }
        $jsst_recent = array_reverse($jsst_recent);
        return array_slice($jsst_recent, 0, max(1, (int) $jsst_limit));
    }

    /** Events emitted on this request, in order. */
    public static function emittedThisRequest() {
        return self::$jsst_emitted;
    }

    /** Empty the recent window. */
    public static function forget() {
        delete_option(self::OPT_RECENT);
        self::$jsst_emitted = array();
    }

    /* =====================================================================
     * Small shared pieces
     * ================================================================== */

    /**
     * An id for one emission, unique enough to match a webhook delivery back to
     * the event that caused it once v5.0 starts delivering them.
     */
    private static function eventId() {
        if (function_exists('wp_generate_uuid4')) {
            return wp_generate_uuid4();
        }
        return uniqid('jsst_', true);
    }

    /** What kind of request emitted this. */
    private static function source() {
        if (class_exists('JSSTcapability') && JSSTcapability::isSystem()) {
            return 'automation';
        }
        if (defined('DOING_CRON') && DOING_CRON) {
            return 'cron';
        }
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return 'rest';
        }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return 'ajax';
        }
        if (defined('WP_CLI') && WP_CLI) {
            return 'cli';
        }
        return is_admin() ? 'admin' : 'frontend';
    }

    /**
     * Say that an emission was refused.
     *
     * Loud where a developer is looking, silent in production: a malformed
     * event is a bug in the code that emitted it, and it must not turn into a
     * warning on a customer's screen half way through raising a ticket.
     */
    private static function complain($jsst_message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
            trigger_error(esc_html($jsst_message), E_USER_NOTICE);
        }
    }
}
