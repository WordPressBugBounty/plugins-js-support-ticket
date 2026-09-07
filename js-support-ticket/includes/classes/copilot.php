<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTcopilot')) {
    return;
}

/**
 * The AI Copilot. (Roadmap 4.0-AI-01)
 *
 * Four things an agent can ask for while reading a ticket: what happened,
 * what it says in another language, what the useful details are, and a first
 * draft of a reply. Each one is a button somebody presses.
 *
 * That last part is the design, not an implementation detail. There is no hook
 * here, nothing on ticket creation, nothing on reply, nothing on a schedule —
 * every route into this class starts with an agent clicking something, and the
 * only bulk path is one where the agent picked the tickets first. A help desk
 * that quietly posted a customer's message to a model the moment it arrived
 * would be doing something its owner never agreed to and its customers were
 * never told about; the cost of that is not the tokens, it is that nobody can
 * say afterwards what left the site. Here they can: the log below records every
 * run, and nothing reaches it that an agent did not ask for.
 *
 * The output is never sent anywhere either. Every action returns text to the
 * screen the agent is looking at, and the agent decides what to do with it. A
 * drafted reply is a draft — this does not post it, and deliberately has no
 * ability to.
 */
class JSSTcopilot {

    /** How many runs are kept for the usage log. */
    const LOG_LIMIT = 50;

    const OPT_LOG = 'jsst_copilot_log';

    /** Characters of ticket thread sent with a request. */
    const CONTEXT_LIMIT = 24000;

    /* ------------------------------------------------------------------ *
     * What it can do
     * ------------------------------------------------------------------ */

    /**
     * The four actions, and how each one asks.
     *
     * Prompts are written plainly and kept short. Two instructions appear in all
     * of them and are the only ones that are not obvious: work from the ticket
     * rather than from general knowledge, and say when something is missing
     * instead of filling the gap. Both exist because the failure that matters
     * here is not a clumsy summary — it is a confident sentence about this
     * customer's account that nobody in the thread ever said.
     */
    public static function actions() {
        $jsst_actions = array(
            'summarize' => array(
                'label'     => esc_html(__('Summarize', 'js-support-ticket')),
                'title'     => esc_html(__('Catch up on this ticket', 'js-support-ticket')),
                'effort'    => 'low',
                'maxtokens' => 6000,
                'system'    => 'You are helping a support agent pick up a ticket they have not read before. '
                             . 'Summarise the conversation: what the customer is trying to do, what has already been tried, and what is still outstanding. '
                             . 'Keep it to a short paragraph or a few lines. '
                             . 'Use only what the ticket says. If something important was never stated, say so rather than assuming it.',
            ),
            'translate' => array(
                'label'     => esc_html(__('Translate', 'js-support-ticket')),
                'title'     => esc_html(__('Read this ticket in another language', 'js-support-ticket')),
                'effort'    => 'low',
                'maxtokens' => 10000,
                'needs'     => 'language',
                'system'    => 'Translate the support ticket below into {{language}}. '
                             . 'Translate what is written; do not summarise it, answer it, or comment on it. '
                             . 'Leave product names, error messages, code, log lines and URLs exactly as they are.',
            ),
            'extract' => array(
                'label'     => esc_html(__('Extract details', 'js-support-ticket')),
                'title'     => esc_html(__('Pull out the facts worth having', 'js-support-ticket')),
                'effort'    => 'low',
                'maxtokens' => 6000,
                'system'    => 'Read the support ticket below and pull out the details an agent would need. '
                             . 'Use only what the ticket says. Leave a field empty rather than guessing at it, '
                             . 'and list anything important the customer has not told us under what is missing.',
                // Fields rather than prose, so the screen can lay the answer out
                // and an empty field reads as "not stated" instead of vanishing
                // into a paragraph.
                'schema'    => array(
                    'type'       => 'object',
                    'properties' => array(
                        'problem'     => array('type' => 'string', 'description' => 'What the customer says is wrong, in one sentence.'),
                        'product'     => array('type' => 'string', 'description' => 'Product, plugin or service named, with a version if one is given. Empty if none.'),
                        'environment' => array('type' => 'string', 'description' => 'Platform, browser, server or versions mentioned. Empty if none.'),
                        'tried'       => array('type' => 'array', 'items' => array('type' => 'string'), 'description' => 'What has already been tried, one per entry.'),
                        'wants'       => array('type' => 'string', 'description' => 'What the customer is asking us to do.'),
                        'missing'     => array('type' => 'array', 'items' => array('type' => 'string'), 'description' => 'Details an agent would need that the ticket does not give.'),
                    ),
                    'required'   => array('problem', 'product', 'environment', 'tried', 'wants', 'missing'),
                    'additionalProperties' => false,
                ),
            ),
            'draft' => array(
                'label'     => esc_html(__('Draft a reply', 'js-support-ticket')),
                'title'     => esc_html(__('Write a first draft for you to edit', 'js-support-ticket')),
                // The one action with judgement in it: it has to decide what to
                // say, not just restate what is there.
                'effort'    => 'medium',
                'maxtokens' => 8000,
                'system'    => 'Write a first draft of a support reply to the customer, for an agent to edit before sending. '
                             . 'Address what they actually asked, in the same language they wrote in, in a plain and friendly register. '
                             . 'Use only what the ticket says. Where a fact is needed that the thread does not give, write a short bracketed placeholder such as [order number] '
                             . 'rather than inventing one — an agent can fill those in, but cannot tell an invented detail from a real one. '
                             . 'If the right reply is a question, ask it. Write the message only: no subject line, no signature, no notes about the draft.',
            ),
        );
        return apply_filters('jsst_copilot_actions', $jsst_actions);
    }

    /* ------------------------------------------------------------------ *
     * Who can press the buttons
     * ------------------------------------------------------------------ */

    /**
     * Anyone who works tickets here.
     *
     * The capability rather than the role, so this follows whatever a site has
     * done with its agent roles instead of second-guessing it, and so an
     * administrator — who holds the same capability — is covered by one check.
     * (Roadmap 4.0-SEC-04)
     */
    public static function mayUse() {
        return self::mayUseAs(get_current_user_id());
    }

    /**
     * The same question asked about somebody who is not here.
     *
     * A bulk run is queued by an agent and executed minutes later by cron, where
     * there is no logged-in user at all. Checking the capability of the agent
     * who asked — rather than trusting that the job exists, or logging somebody
     * in to run it — keeps the permission attached to the person, so an agent
     * whose access is removed between pressing the button and the batch reaching
     * their tickets does not get the answers anyway.
     */
    public static function mayUseAs($jsst_userid) {
        $jsst_userid = (int) $jsst_userid;
        if ($jsst_userid <= 0) {
            return false;
        }
        if (class_exists('JSSTroles')) {
            return user_can($jsst_userid, JSSTroles::CAP_TICKETS);
        }
        return user_can($jsst_userid, 'manage_options');
    }

    /** Ready to use: somebody who may, and a key to use. */
    public static function available() {
        return (self::mayUse() && JSSTcopilotprovider::configured());
    }

    /* ------------------------------------------------------------------ *
     * What gets sent
     * ------------------------------------------------------------------ */

    /**
     * The ticket, as text.
     *
     * Read straight from the tables rather than through the ticket model,
     * because what leaves the site should be something a person can read in one
     * place and check — this function is the honest answer to "what exactly do
     * you send?", and it is short enough to be read in full.
     *
     * Markup and shortcodes come out: they are noise to a model and cost the
     * site owner money by the token. Long threads are trimmed from the top,
     * keeping the opening message and the most recent exchanges, which is what
     * every one of these actions is actually about.
     */
    public static function context($jsst_ticketid) {
        $jsst_prefix = jssupportticket::$_db->prefix . 'js_ticket_';
        $jsst_ticket = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT id, subject, message, created FROM `" . $jsst_prefix . "tickets` WHERE id = %d",
            (int) $jsst_ticketid
        ));
        if (!$jsst_ticket) {
            return new WP_Error('jsst_copilot_noticket', esc_html(__('That ticket cannot be found.', 'js-support-ticket')));
        }

        $jsst_lines = array();
        $jsst_lines[] = 'Subject: ' . self::plain($jsst_ticket->subject);
        $jsst_lines[] = '';
        $jsst_lines[] = 'Customer wrote:';
        $jsst_lines[] = self::plain($jsst_ticket->message);

        // A reply carries the id of the agent who wrote it, or zero when the
        // customer did — which is the only thing here that distinguishes the
        // two sides of the conversation, and getting it backwards would have
        // the model answering the agent instead of the customer.
        $jsst_replies = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT message, staffid, created FROM `" . $jsst_prefix . "replies`
                WHERE ticketid = %d ORDER BY id ASC",
            (int) $jsst_ticket->id
        ));
        foreach ((array) $jsst_replies as $jsst_reply) {
            $jsst_text = self::plain($jsst_reply->message);
            if ($jsst_text === '') {
                continue;
            }
            $jsst_lines[] = '';
            $jsst_lines[] = (((int) $jsst_reply->staffid > 0) ? 'Agent replied:' : 'Customer replied:');
            $jsst_lines[] = $jsst_text;
        }

        $jsst_thread = implode("\n", $jsst_lines);
        if (strlen($jsst_thread) > self::CONTEXT_LIMIT) {
            // Keep the beginning, which says what the ticket is about, and the
            // end, which is where the conversation actually is.
            $jsst_head = substr($jsst_thread, 0, (int) floor(self::CONTEXT_LIMIT * 0.3));
            $jsst_tail = substr($jsst_thread, -1 * (int) floor(self::CONTEXT_LIMIT * 0.7));
            $jsst_thread = $jsst_head . "\n\n[...older messages left out...]\n\n" . $jsst_tail;
        }
        return $jsst_thread;
    }

    /** Readable text out of stored ticket HTML. */
    private static function plain($jsst_value) {
        $jsst_value = strip_shortcodes((string) $jsst_value);
        $jsst_value = wp_strip_all_tags($jsst_value);
        $jsst_value = html_entity_decode($jsst_value, ENT_QUOTES, 'UTF-8');
        return trim(preg_replace("/\n{3,}/", "\n\n", $jsst_value));
    }

    /* ------------------------------------------------------------------ *
     * Running one
     * ------------------------------------------------------------------ */

    /**
     * Run an action against a ticket and return what came back.
     *
     * Every caller — the ticket screen, the bulk job — comes through here, so
     * the capability check, the log entry and the shape of the answer are
     * written once.
     */
    public static function run($jsst_actionkey, $jsst_ticketid, $jsst_options = array(), $jsst_asuser = 0) {
        // Zero means "whoever is making this request", which is every path
        // except the queued batch — that one names the agent who asked.
        $jsst_actor = ((int) $jsst_asuser > 0) ? (int) $jsst_asuser : get_current_user_id();
        if (!self::mayUseAs($jsst_actor)) {
            return new WP_Error('jsst_copilot_denied', esc_html(__('You do not have permission to use the AI Copilot.', 'js-support-ticket')));
        }
        $jsst_actions = self::actions();
        if (!isset($jsst_actions[$jsst_actionkey])) {
            return new WP_Error('jsst_copilot_noaction', esc_html(__('That is not an action this can run.', 'js-support-ticket')));
        }
        $jsst_action = $jsst_actions[$jsst_actionkey];

        $jsst_context = self::context($jsst_ticketid);
        if (is_wp_error($jsst_context)) {
            return $jsst_context;
        }

        $jsst_system = $jsst_action['system'];
        if (isset($jsst_action['needs']) && $jsst_action['needs'] === 'language') {
            $jsst_language = isset($jsst_options['language']) ? trim((string) $jsst_options['language']) : '';
            if ($jsst_language === '') {
                $jsst_language = self::defaultLanguage();
            }
            $jsst_system = str_replace('{{language}}', $jsst_language, $jsst_system);
        }

        $jsst_result = JSSTcopilotprovider::ask(array(
            'system'    => $jsst_system,
            'prompt'    => $jsst_context,
            'effort'    => $jsst_action['effort'],
            'maxtokens' => $jsst_action['maxtokens'],
            'schema'    => isset($jsst_action['schema']) ? $jsst_action['schema'] : null,
        ));

        self::log($jsst_actionkey, $jsst_ticketid, $jsst_result, $jsst_actor);
        if (is_wp_error($jsst_result)) {
            return $jsst_result;
        }

        $jsst_result['action'] = $jsst_actionkey;
        $jsst_result['ticket'] = (int) $jsst_ticketid;
        // Structured actions come back as json text; decoded here so the screen
        // renders fields rather than printing a brace-covered blob at somebody.
        if (!empty($jsst_action['schema'])) {
            $jsst_fields = json_decode($jsst_result['text'], true);
            $jsst_result['fields'] = is_array($jsst_fields) ? $jsst_fields : array();
        }
        return $jsst_result;
    }

    /** The language Translate uses when the agent has not picked one. */
    public static function defaultLanguage() {
        $jsst_language = trim((string) get_option('jsst_copilot_language', ''));
        return ($jsst_language !== '') ? $jsst_language : 'English';
    }

    /* ------------------------------------------------------------------ *
     * Several at once
     * ------------------------------------------------------------------ */

    /**
     * Summarise a set of tickets the agent picked from the list.
     *
     * The one action worth having in bulk: reading twenty tickets to work out
     * which need attention is the job, and it is the job whether or not there is
     * an AI. Still explicit — the agent selects the rows and presses the button —
     * but it is the shape closest to something running on its own, so it is the
     * one with a ceiling on it. A run is capped, the tickets are fixed when it
     * starts, and the count is on the button before it is pressed, because the
     * bill lands on the site owner and a mis-click across a filtered list of
     * every open ticket should not be able to cost them a day's budget.
     *
     * Handed to the queue because each ticket is a separate request to somebody
     * else's server and twenty of them will not finish inside one page load.
     * (Roadmap 4.0-PERF-02)
     */
    const BULK_LIMIT = 25;
    const BULK_SLICE = 3;
    const OPT_BATCHES = 'jsst_copilot_batches';

    public static function startBatch($jsst_ticketids, $jsst_action = 'summarize') {
        if (!self::mayUse()) {
            return new WP_Error('jsst_copilot_denied', esc_html(__('You do not have permission to use the AI Copilot.', 'js-support-ticket')));
        }
        $jsst_ids = array_values(array_unique(array_filter(array_map('absint', (array) $jsst_ticketids))));
        if (empty($jsst_ids)) {
            return new WP_Error('jsst_copilot_notickets', esc_html(__('No tickets were selected.', 'js-support-ticket')));
        }
        if (count($jsst_ids) > self::BULK_LIMIT) {
            return new WP_Error('jsst_copilot_toomany', sprintf(
                /* translators: %d: the largest number of tickets one run may cover */
                esc_html(__('That is more than one run may cover. Select %d tickets or fewer.', 'js-support-ticket')),
                self::BULK_LIMIT
            ));
        }

        $jsst_token = wp_generate_password(16, false, false);
        $jsst_batches = self::batches();
        $jsst_batches[$jsst_token] = array(
            'token'   => $jsst_token,
            'action'  => (string) $jsst_action,
            'queue'   => $jsst_ids,
            'total'   => count($jsst_ids),
            'results' => array(),
            'owner'   => get_current_user_id(),
            'started' => time(),
            'updated' => time(),
        );
        self::saveBatches($jsst_batches);
        return $jsst_token;
    }

    /**
     * Do the next few tickets of a batch, and say whether more remain.
     *
     * A ticket that fails keeps its error in the results rather than stopping
     * the run: one ticket the model choked on should not cost the agent the
     * other nineteen, and the reason is on the screen next to the ticket it
     * belongs to.
     */
    public static function stepBatch($jsst_token) {
        $jsst_batches = self::batches();
        if (!isset($jsst_batches[$jsst_token])) {
            return false;
        }
        $jsst_batch = $jsst_batches[$jsst_token];
        if (empty($jsst_batch['queue'])) {
            return false;
        }

        for ($jsst_i = 0; $jsst_i < self::BULK_SLICE && !empty($jsst_batch['queue']); $jsst_i++) {
            $jsst_ticketid = (int) array_shift($jsst_batch['queue']);
            $jsst_result = self::run($jsst_batch['action'], $jsst_ticketid, array(), (int) $jsst_batch['owner']);
            $jsst_batch['results'][] = array(
                'ticket' => $jsst_ticketid,
                'ok'     => !is_wp_error($jsst_result),
                'text'   => is_wp_error($jsst_result) ? $jsst_result->get_error_message() : $jsst_result['text'],
            );
        }
        $jsst_batch['updated'] = time();
        $jsst_batches[$jsst_token] = $jsst_batch;
        self::saveBatches($jsst_batches);
        return !empty($jsst_batch['queue']);
    }

    public static function batch($jsst_token) {
        $jsst_batches = self::batches();
        return isset($jsst_batches[$jsst_token]) ? $jsst_batches[$jsst_token] : null;
    }

    private static function batches() {
        $jsst_batches = get_option(self::OPT_BATCHES, array());
        return is_array($jsst_batches) ? $jsst_batches : array();
    }

    /** Finished batches are read once and then are just clutter in an option. */
    private static function saveBatches($jsst_batches) {
        $jsst_cutoff = time() - DAY_IN_SECONDS;
        foreach ($jsst_batches as $jsst_token => $jsst_batch) {
            if (empty($jsst_batch['queue']) && (int) $jsst_batch['updated'] < $jsst_cutoff) {
                unset($jsst_batches[$jsst_token]);
            }
        }
        update_option(self::OPT_BATCHES, $jsst_batches, false);
    }

    /* ------------------------------------------------------------------ *
     * The record
     * ------------------------------------------------------------------ */

    /**
     * Note that a run happened.
     *
     * The point of this is not analytics. It is that a site owner can answer two
     * questions without taking anybody's word for it: what has been sent to the
     * model, and what it is costing. Failures are recorded too — a key that
     * stopped working is a run that happened, and it is the entry somebody needs
     * to see when the button "does nothing".
     *
     * Ticket ids and token counts, never ticket content: this log is read on a
     * screen and included in no export, and a copy of what was sent is the one
     * thing that would make it worth stealing.
     */
    private static function log($jsst_actionkey, $jsst_ticketid, $jsst_result, $jsst_actor = 0) {
        $jsst_log = get_option(self::OPT_LOG, array());
        if (!is_array($jsst_log)) {
            $jsst_log = array();
        }
        array_unshift($jsst_log, array(
            'action'    => (string) $jsst_actionkey,
            'ticket'    => (int) $jsst_ticketid,
            'user'      => ((int) $jsst_actor > 0) ? (int) $jsst_actor : get_current_user_id(),
            'when'      => time(),
            'ok'        => !is_wp_error($jsst_result),
            'error'     => is_wp_error($jsst_result) ? $jsst_result->get_error_message() : '',
            'model'     => is_wp_error($jsst_result) ? '' : $jsst_result['model'],
            'intokens'  => is_wp_error($jsst_result) ? 0 : $jsst_result['intokens'],
            'outtokens' => is_wp_error($jsst_result) ? 0 : $jsst_result['outtokens'],
        ));
        update_option(self::OPT_LOG, array_slice($jsst_log, 0, self::LOG_LIMIT), false);
    }

    /** The log, newest first, for the screen. */
    public static function recentRuns() {
        $jsst_log = get_option(self::OPT_LOG, array());
        return is_array($jsst_log) ? $jsst_log : array();
    }

    /** What the log adds up to, for the line above it. */
    public static function usage() {
        $jsst_totals = array('runs' => 0, 'failed' => 0, 'intokens' => 0, 'outtokens' => 0);
        foreach (self::recentRuns() as $jsst_entry) {
            $jsst_totals['runs']++;
            if (empty($jsst_entry['ok'])) {
                $jsst_totals['failed']++;
            }
            $jsst_totals['intokens'] += (int) $jsst_entry['intokens'];
            $jsst_totals['outtokens'] += (int) $jsst_entry['outtokens'];
        }
        return $jsst_totals;
    }

}
