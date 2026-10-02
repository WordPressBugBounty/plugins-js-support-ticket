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
            /* Triage and sentiment differ from the four above in one way that
               matters: they are worth having before anybody has read the
               ticket. That is why JSSTaitriage exists to run them without a
               button - and why they are still ordinary Copilot actions here,
               so an agent can ask for either on a ticket the automatic path
               never touched. (Roadmap 6.0-AI-13) */
            'triage' => array(
                'label'     => esc_html(__('Suggest routing', 'js-support-ticket')),
                'title'     => esc_html(__('Where this probably belongs, and how urgent it looks', 'js-support-ticket')),
                'effort'    => 'low',
                'maxtokens' => 4000,
                'needs'     => 'departments',
                'system'    => 'You are triaging a support ticket for a help desk. '
                             . 'Say which of the listed departments it belongs to and how urgent it looks, and give a one-line reason. '
                             . 'Choose a department only from the list given; if none of them fits, leave it empty rather than inventing one. '
                             . 'Judge urgency on what the ticket describes - somebody blocked or losing money is urgent, a question is not - '
                             . 'and not on how forcefully it is written, because the two are frequently opposite.',
                'schema'    => array(
                    'type'       => 'object',
                    'properties' => array(
                        'department' => array('type' => 'string', 'description' => 'Exactly one department name from the list given, or empty if none fits.'),
                        'urgency'    => array('type' => 'string', 'description' => 'One of: low, normal, high, urgent.'),
                        'reason'     => array('type' => 'string', 'description' => 'One line saying why, quoting the ticket rather than characterising it.'),
                    ),
                    'required'   => array('department', 'urgency', 'reason'),
                    'additionalProperties' => false,
                ),
            ),
            'sentiment' => array(
                'label'     => esc_html(__('Read the mood', 'js-support-ticket')),
                'title'     => esc_html(__('How this customer sounds, and whether they are about to give up', 'js-support-ticket')),
                'effort'    => 'low',
                'maxtokens' => 3000,
                'system'    => 'Read the support ticket below and judge how the customer sounds. '
                             . 'Score from -100 (furious, or about to leave) through 0 (neutral) to +100 (delighted). '
                             . 'Judge the whole thread, weighting the most recent message most - a ticket that started badly and has been resolved is not a negative one. '
                             . 'Politeness is not contentment and bluntness is not anger; several cultures write support requests in a register that reads as curt in English. '
                             . 'Quote the words you scored on so a person can disagree with you.',
                'schema'    => array(
                    'type'       => 'object',
                    'properties' => array(
                        'score'    => array('type' => 'integer', 'description' => 'From -100 to 100.'),
                        'label'    => array('type' => 'string', 'description' => 'One of: angry, unhappy, neutral, pleased, delighted.'),
                        'atrisk'   => array('type' => 'boolean', 'description' => 'True only if the customer has said or strongly implied they may stop using the product.'),
                        'evidence' => array('type' => 'string', 'description' => 'The customer\'s own words that this was judged on.'),
                    ),
                    'required'   => array('score', 'label', 'atrisk', 'evidence'),
                    'additionalProperties' => false,
                ),
            ),
            /* Rewriting a canned response, not a ticket. The only action whose
               subject is the library rather than a conversation, which is why
               it takes its text from the caller - see run()'s $jsst_options
               handling. (Roadmap 6.0-KB-02) */
            'rewrite' => array(
                'label'     => esc_html(__('Improve the wording', 'js-support-ticket')),
                'title'     => esc_html(__('Tighten a canned response without changing what it says', 'js-support-ticket')),
                'effort'    => 'low',
                'maxtokens' => 4000,
                'needs'     => 'text',
                'system'    => 'Rewrite the support message below so it reads better: plainer, warmer, and shorter where it can be. '
                             . 'Do NOT change what it says. Every step, condition, figure, deadline and policy must survive exactly as written - '
                             . 'this text goes to customers and a rewrite that quietly changes a refund window is worse than clumsy wording. '
                             . 'Leave every {placeholder} exactly as it is, spelled the same way, or it will stop being filled in. '
                             . 'Return the rewritten message only, with no commentary.',
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

    /**
     * Ready to use: somebody who may, an engine set up, and a lane open to run
     * it in.
     *
     * usable() rather than configured() since 6.0-AI-01, so the site's AI
     * switches actually reach this. A stored key on a site that has switched
     * hosted models off is not availability - it is a button that would draw
     * itself and then refuse. (Roadmap 4.0-AI-04)
     */
    public static function available() {
        return (self::mayUse() && JSSTaiengine::usable());
    }

    /**
     * The buttons on the admin ticket page.
     *
     * Prints nothing unless it would work: somebody who may use it, and an
     * engine that can answer. Each button asks for one action on this ticket
     * and shows the text here; a draft goes into the reply box only when the
     * agent presses "Use in reply", and is never sent from here.
     */
    public static function renderPanel($jsst_ticketid) {
        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_ticketid < 1 || !class_exists('JSSTaiengine') || !self::available()) return;

        $jsst_buttons = array(
            'summarize' => __('Summarize', 'js-support-ticket'),
            'extract'   => __('Key details', 'js-support-ticket'),
            'translate' => __('Translate', 'js-support-ticket'),
            'draft'     => __('Draft a reply', 'js-support-ticket'),
        );
        ?>
        <div class="jsst-copilot" id="jsst-copilot" data-ticket="<?php echo esc_attr($jsst_ticketid); ?>">
            <div class="jsst-copilot-head">
                <strong><?php echo esc_html__('AI Copilot', 'js-support-ticket'); ?></strong>
                <span class="jsst-copilot-note"><?php echo esc_html__('Only you see the result. Nothing is sent to the customer.', 'js-support-ticket'); ?></span>
            </div>
            <div class="jsst-copilot-actions">
                <?php foreach ($jsst_buttons as $jsst_key => $jsst_label) { ?>
                    <button type="button" class="button" data-jsst-copilot="<?php echo esc_attr($jsst_key); ?>"><?php echo esc_html($jsst_label); ?></button>
                <?php } ?>
                <label class="jsst-copilot-lang"><?php echo esc_html__('into', 'js-support-ticket'); ?>
                    <input type="text" size="10" data-jsst-copilot-lang value="<?php echo esc_attr(self::defaultLanguage()); ?>" />
                </label>
            </div>
            <div class="jsst-copilot-out" hidden>
                <div class="jsst-copilot-text"></div>
                <button type="button" class="button button-primary jsst-copilot-use" hidden><?php echo esc_html__('Use in reply', 'js-support-ticket'); ?></button>
            </div>
        </div>
        <style>
            .jsst-copilot{border:1px solid #dcdcde;border-radius:6px;padding:10px 12px;margin:0 0 14px;background:#fff}
            .jsst-copilot-head{display:flex;flex-wrap:wrap;gap:8px;align-items:baseline;margin-bottom:8px}
            .jsst-copilot-note{color:#646970;font-size:12px}
            .jsst-copilot-actions{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
            .jsst-copilot-lang{font-size:12px;color:#646970}
            .jsst-copilot-out{margin-top:10px;border-top:1px solid #f0f0f1;padding-top:10px}
            .jsst-copilot-text{white-space:pre-wrap;margin-bottom:8px}
            .jsst-copilot-text dt{font-weight:600;margin-top:6px}
            .jsst-copilot-text dd{margin:0 0 0 12px}
        </style>
        <script type="text/javascript">
        (function () {
            var box = document.getElementById('jsst-copilot');
            if (!box) return;
            var out = box.querySelector('.jsst-copilot-out');
            var text = box.querySelector('.jsst-copilot-text');
            var use = box.querySelector('.jsst-copilot-use');
            var lang = box.querySelector('[data-jsst-copilot-lang]');
            var draft = '';

            function show(node, isDraft) {
                text.innerHTML = '';
                text.appendChild(node);
                out.hidden = false;
                use.hidden = !isDraft;
            }
            function words(msg) {
                var p = document.createElement('p');
                p.textContent = msg;
                return p;
            }
            function fields(obj) {
                var dl = document.createElement('dl');
                Object.keys(obj || {}).forEach(function (k) {
                    var v = obj[k];
                    if (Array.isArray(v)) v = v.join(', ');
                    if (v === '' || v === null || v === undefined) return;
                    var dt = document.createElement('dt');
                    dt.textContent = k.charAt(0).toUpperCase() + k.slice(1);
                    var dd = document.createElement('dd');
                    dd.textContent = String(v);
                    dl.appendChild(dt);
                    dl.appendChild(dd);
                });
                return dl;
            }

            box.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-jsst-copilot]');
                if (!btn) return;
                var action = btn.getAttribute('data-jsst-copilot');
                var all = box.querySelectorAll('[data-jsst-copilot]');
                Array.prototype.forEach.call(all, function (b) { b.disabled = true; });
                show(words('<?php echo esc_js(__('Working…', 'js-support-ticket')); ?>'), false);

                var body = new URLSearchParams();
                body.append('action', 'jsticket_ajax');
                body.append('jstmod', 'copilot');
                body.append('task', 'copilotRun');
                body.append('copilotaction', action);
                body.append('ticketid', box.getAttribute('data-ticket'));
                body.append('language', lang ? lang.value : '');
                body.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('jsst-copilot')); ?>');

                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    method: 'POST', credentials: 'same-origin', body: body
                }).then(function (r) { return r.json(); }).then(function (res) {
                    if (!res || !res.success) {
                        show(words((res && res.data && res.data.message) || '<?php echo esc_js(__('That did not work. Try again.', 'js-support-ticket')); ?>'), false);
                        return;
                    }
                    var d = res.data;
                    draft = (action === 'draft') ? (d.text || '') : '';
                    var node = (d.fields && Object.keys(d.fields).length) ? fields(d.fields) : words(d.text || '');
                    if (d.truncated) {
                        var wrap = document.createElement('div');
                        wrap.appendChild(node);
                        wrap.appendChild(words('<?php echo esc_js(__('This stopped early and may be incomplete.', 'js-support-ticket')); ?>'));
                        node = wrap;
                    }
                    show(node, action === 'draft');
                }).catch(function () {
                    show(words('<?php echo esc_js(__('That did not work. Try again.', 'js-support-ticket')); ?>'), false);
                }).then(function () {
                    Array.prototype.forEach.call(all, function (b) { b.disabled = false; });
                });
            });

            use.addEventListener('click', function () {
                if (!draft) return;
                var ed = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('jsticket_message') : null;
                var html = draft.split(/\n{2,}/).map(function (para) {
                    var p = document.createElement('p');
                    p.textContent = para;
                    return p.outerHTML.replace(/\n/g, '<br>');
                }).join('');
                if (ed && !ed.isHidden()) {
                    ed.setContent(html);
                    ed.focus();
                } else {
                    var ta = document.getElementById('jsticket_message');
                    if (ta) { ta.value = draft; ta.focus(); }
                }
            });
        })();
        </script>
        <?php
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
        /* An automatic answer is posted with no staff id, so by that rule
           alone it read as "Customer replied:" - and the mood reading quoted
           the bot's own "pass it to a person" link back as the customer's
           words. Such replies are named for what they are, and the note the
           review class appends under them (disclosure, sources, handoff link)
           is dropped, since it is not part of the conversation. */
        $jsst_hasaiflag = (bool) jssupportticket::$_db->get_var(
            "SHOW COLUMNS FROM `" . $jsst_prefix . "replies` LIKE 'ticketviaautopilot'");
        $jsst_replies = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT message, staffid, created"
                . ($jsst_hasaiflag ? ", ticketviaautopilot" : ", 0 AS ticketviaautopilot") . "
                FROM `" . $jsst_prefix . "replies`
                WHERE ticketid = %d ORDER BY id ASC",
            (int) $jsst_ticket->id
        ));
        foreach ((array) $jsst_replies as $jsst_reply) {
            $jsst_html = preg_replace('#<div class="jsst-ai-note">.*?</div>\s*$#s', '', (string) $jsst_reply->message);
            $jsst_text = self::plain($jsst_html);
            if ($jsst_text === '') {
                continue;
            }
            $jsst_lines[] = '';
            if (!empty($jsst_reply->ticketviaautopilot)) {
                $jsst_lines[] = 'Automatic AI answer (not written by the customer or an agent):';
            } else {
                $jsst_lines[] = (((int) $jsst_reply->staffid > 0) ? 'Agent replied:' : 'Customer replied:');
            }
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
        // One conversion for every AI surface; keeps paragraph and list breaks.
        return JSSTaiengine::plainText($jsst_value);
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

        /* An action pointed at supplied text rather than at a ticket.
           Any action may be, not just the one that requires it: `rewrite` has
           no ticket by nature, and `translate` is used both ways - on a ticket
           an agent is reading, and on a canned response somebody is localising
           (6.0-KB-02). So the text is what decides, and the ticket context
           below is built only when there is none. */
        $jsst_supplied = isset($jsst_options['text']) ? trim(wp_strip_all_tags((string) $jsst_options['text'])) : '';

        if (isset($jsst_action['needs']) && $jsst_action['needs'] === 'text' && $jsst_supplied === '') {
            return new WP_Error('jsst_copilot_notext',
                esc_html(__('There is no text to work on.', 'js-support-ticket')));
        }

        if ($jsst_supplied !== '') {
            /* The language placeholder is substituted here as well as below,
               because this path returns before reaching that code - and a
               prompt that still says {{language}} asks the model to translate
               into a literal pair of braces. */
            $jsst_system = $jsst_action['system'];
            if (isset($jsst_action['needs']) && $jsst_action['needs'] === 'language') {
                $jsst_language = isset($jsst_options['language']) ? trim((string) $jsst_options['language']) : '';
                if ($jsst_language === '') $jsst_language = self::defaultLanguage();
                $jsst_system = str_replace('{{language}}', $jsst_language, $jsst_system);
            }

            $jsst_result = JSSTaiengine::ask(array(
                'system'    => $jsst_system,
                'prompt'    => jssupportticketphplib::JSST_substr($jsst_supplied, 0, self::CONTEXT_LIMIT),
                'effort'    => $jsst_action['effort'],
                'maxtokens' => $jsst_action['maxtokens'],
                'feature'   => 'copilot',
                'ticket'    => 0,
            ));

            self::log($jsst_actionkey, 0, $jsst_result, $jsst_actor);
            if (is_wp_error($jsst_result)) return $jsst_result;

            $jsst_result['action'] = $jsst_actionkey;
            $jsst_result['ticket'] = 0;
            return $jsst_result;
        }

        $jsst_context = self::context($jsst_ticketid);
        if (is_wp_error($jsst_context)) {
            return $jsst_context;
        }

        $jsst_system = $jsst_action['system'];

        /* Triage can only choose from departments this desk actually has, so
           the list is appended to the prompt rather than left to the model's
           imagination - a suggestion naming a department that does not exist is
           worse than no suggestion, because somebody has to work out which of
           theirs was meant. (Roadmap 6.0-AI-13) */
        if (isset($jsst_action['needs']) && $jsst_action['needs'] === 'departments') {
            $jsst_names = self::departmentNames();
            if (empty($jsst_names)) {
                return new WP_Error('jsst_copilot_nodepartments',
                    esc_html(__('This desk has no departments to route to.', 'js-support-ticket')));
            }
            $jsst_system .= "\n\nThe departments on this desk are: " . implode('; ', $jsst_names) . '.';
        }

        if (isset($jsst_action['needs']) && $jsst_action['needs'] === 'language') {
            $jsst_language = isset($jsst_options['language']) ? trim((string) $jsst_options['language']) : '';
            if ($jsst_language === '') {
                $jsst_language = self::defaultLanguage();
            }
            $jsst_system = str_replace('{{language}}', $jsst_language, $jsst_system);
        }

        /* feature and ticket are for the meter, and are carried on the call
           rather than set by the engine because only the caller knows which
           part of the product is spending. (Roadmap 6.0-AI-07) */
        $jsst_result = JSSTaiengine::ask(array(
            'system'    => $jsst_system,
            'prompt'    => $jsst_context,
            'effort'    => $jsst_action['effort'],
            'maxtokens' => $jsst_action['maxtokens'],
            'schema'    => isset($jsst_action['schema']) ? $jsst_action['schema'] : null,
            'feature'   => 'copilot',
            'ticket'    => (int) $jsst_ticketid,
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
            $jsst_result['fields'] = self::fieldsFrom($jsst_result['text'], $jsst_action['schema']);
        }
        return $jsst_result;
    }

    /**
     * The fields out of a structured answer, however it arrived.
     *
     * Anthropic and OpenAI enforce the schema, so their text is bare JSON.
     * Zywrap cannot be handed a schema and may wrap the JSON in a code fence
     * or answer as a report ("| **Department** | Technical Support |" or
     * "Urgency: Urgent"). Reading only bare JSON left triage storing nothing
     * on Zywrap, so the fallbacks below recover the same fields by name.
     */
    public static function fieldsFrom($jsst_text, $jsst_schema) {
        $jsst_text = trim((string) $jsst_text);
        $jsst_decoded = json_decode($jsst_text, true);
        if (is_array($jsst_decoded)) {
            return $jsst_decoded;
        }
        /* A JSON object somewhere inside the text - in a fence or after a line
           of preamble. */
        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $jsst_text, $jsst_m)) {
            $jsst_decoded = json_decode($jsst_m[0], true);
            if (is_array($jsst_decoded)) {
                return $jsst_decoded;
            }
        }
        /* A report: find each field by its name as a table cell or a
           "Name: value" line. Only the schema's own keys are looked for. */
        $jsst_props = (isset($jsst_schema['properties']) && is_array($jsst_schema['properties'])) ? $jsst_schema['properties'] : array();
        $jsst_out = array();
        foreach ($jsst_props AS $jsst_key => $jsst_def) {
            $jsst_label = preg_quote($jsst_key, '/');
            $jsst_value = null;
            /* "| **Department** | Technical Support |", also "| One-line reason | ... |" */
            if (preg_match('/^\s*\|\s*[*_ ]*(?:[\w -]*\s)?' . $jsst_label . '[*_ ]*\s*\|\s*(.+?)\s*\|?\s*$/mi', $jsst_text, $jsst_m)) {
                $jsst_value = $jsst_m[1];
            } elseif (preg_match('/^\s*[-*]?\s*[*_]*' . $jsst_label . '[*_]*\s*[:=-]\s*(.+)$/mi', $jsst_text, $jsst_m)) {
                $jsst_value = $jsst_m[1];
            }
            if ($jsst_value === null) {
                continue;
            }
            $jsst_value = trim(preg_replace('/[*_`]+/', '', $jsst_value));
            $jsst_type = isset($jsst_def['type']) ? $jsst_def['type'] : 'string';
            if ($jsst_type === 'integer' || $jsst_type === 'number') {
                if (!preg_match('/-?\d+/', $jsst_value, $jsst_n)) continue;
                $jsst_value = (int) $jsst_n[0];
            } elseif ($jsst_type === 'boolean') {
                $jsst_value = (bool) preg_match('/^(yes|true|y|1)\b/i', $jsst_value);
            }
            $jsst_out[$jsst_key] = $jsst_value;
        }
        return $jsst_out;
    }

    /**
     * Every department a ticket could be routed to.
     *
     * Names rather than ids, because the model is being asked to read a ticket
     * and not to know this desk's primary keys - and because a name it gets
     * slightly wrong can still be matched back, while a wrong integer cannot be
     * told from a right one. (Roadmap 6.0-AI-13)
     */
    public static function departmentNames() {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_departments';
        if (jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                'SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return array();
        }

        $jsst_names = jssupportticket::$_db->get_col(
            "SELECT departmentname FROM `" . $jsst_table . "` WHERE status = 1 ORDER BY id ASC LIMIT 60");

        return array_values(array_filter(array_map('trim', (array) $jsst_names)));
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
