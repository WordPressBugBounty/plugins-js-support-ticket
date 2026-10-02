<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Every answer the AI proposed, what happened to it, and how to take it back.
 * (Roadmap 6.0-AI-03)
 *
 * The third of the three registers. JSSTaipolicy says whether AI may run at
 * all, JSSTaisources says what it may read, and this says what may be *sent* -
 * and, once something has been sent, what can still be done about it.
 *
 * It exists because "never auto-send below the threshold" is only half a
 * promise. The other half is that the half-second after an automatic answer
 * goes out is not the end of the story: somebody has to be able to look at what
 * was sent, see what it was based on, and withdraw it. A confidence score with
 * no undo behind it is a number, not a safeguard.
 *
 * **What reversible honestly means here.** The customer has the e-mail. Nothing
 * can unsend that and this class does not pretend otherwise. Withdrawing an
 * answer marks the reply as withdrawn rather than deleting it, leaves it
 * visible with its correction attached, reopens the ticket if the automatic
 * answer closed it, and records who withdrew it and why. Deleting the reply
 * would leave the ticket disagreeing with the customer's inbox, which is worse
 * than the wrong answer: it makes the desk look like it is hiding something.
 *
 * **Why the queue is core's and not the engine's.** The add-on already keeps a
 * decision log - why it retrieved what it did, which gate stopped it. That is
 * an engine diagnostic and it belongs to the engine. This is the record of an
 * answer and a human judgement on it, which is the thing an auditor asks for,
 * the thing that has to survive the add-on being uninstalled, and the thing a
 * site with no add-on at all will still need the moment anything else in the
 * product proposes text to a customer.
 */
class JSSTaireview {

    const TABLE          = 'js_ticket_ai_answers';
    const SCHEMA_VERSION = '6.0.1';
    const OPT_SCHEMA     = 'jsst_ai_answers_schema';

    /** How much a person has to do before an automatic answer reaches anybody. */
    const OPT_MODE = 'jsst_ai_approval';

    /** Send when the engine's own confidence clears the configured floor. */
    const MODE_THRESHOLD = 'threshold';
    /** Never send without somebody pressing approve, however confident it is. */
    const MODE_ALWAYS = 'always';
    /** Never send at all; propose only. */
    const MODE_NEVER = 'never';

    /** Waiting for a person. */
    const STATE_HELD = 'held';
    /** Sent to the customer. */
    const STATE_SENT = 'sent';
    /** A person said no; nothing was sent. */
    const STATE_REJECTED = 'rejected';
    /** It was sent, and then withdrawn. */
    const STATE_RETRACTED = 'retracted';

    /** Retraction lookups are per ticket view; memoise the request. */
    private static $jsst_retracted = null;

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    public static function ensureSchema() {
        if (!class_exists('JSSTschemaguard')) return;
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION,
                array(self::TABLE => array('ticketid', 'replyid', 'state', 'body', 'agentreply', 'agreement')))) {
            return;
        }

        /* One request migrates; the rest leave without recording a version they
           did not write. See `JSSTschemaguard::lock()` - two admin requests
           arriving together each read this table's columns before either
           altered it, and the loser logged a `Duplicate column name` for every
           shadow column the winner had just added. */
        if (!JSSTschemaguard::lock(self::OPT_SCHEMA)) {
            return;
        }

        $jsst_table   = self::table();
        $jsst_charset = jssupportticket::$_db->get_charset_collate();

        /* `replyid` is 0 until something is actually sent, and it is indexed
           because the ticket timeline asks "was this reply withdrawn" once per
           reply it draws. `body` is kept for a rejected answer as well as a
           sent one: the question an admin asks a week later is "what did it
           want to say", and an audit row without the text cannot answer it. */
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                ticketid bigint(20) NOT NULL DEFAULT '0',
                replyid bigint(20) NOT NULL DEFAULT '0',
                state varchar(20) NOT NULL DEFAULT 'held',
                engine varchar(50) NOT NULL DEFAULT '',
                confidence tinyint(4) NOT NULL DEFAULT '0',
                coverage tinyint(4) DEFAULT NULL,
                overlap tinyint(4) DEFAULT NULL,
                body longtext,
                sources text,
                reason varchar(255) NOT NULL DEFAULT '',
                note varchar(255) NOT NULL DEFAULT '',
                decidedby bigint(20) NOT NULL DEFAULT '0',
                created datetime DEFAULT NULL,
                decided datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY jsst_state (state, created),
                KEY jsst_ticket (ticketid),
                KEY jsst_reply (replyid)
            ) " . $jsst_charset);

        /* The shadow columns arrived a release after the table. CREATE TABLE IF
           NOT EXISTS above does nothing on a site that already has it, so they
           are added here - named in needsRun() as well, which is what makes the
           guard notice a table that drifted back to the older layout.
           (Roadmap 6.0-AI-04) */
        $jsst_add  = array(
            'agentreply' => "bigint(20) NOT NULL DEFAULT '0'",
            'agentbody'  => 'longtext',
            'agreement'  => 'tinyint(4) DEFAULT NULL',
            'outcome'    => "varchar(30) NOT NULL DEFAULT ''",
            'pairedat'   => 'datetime DEFAULT NULL',
        );
        JSSTschemaguard::addColumns(self::TABLE, $jsst_add);

        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);

        JSSTschemaguard::unlock(self::OPT_SCHEMA);
    }

    public static function table() {
        return jssupportticket::$_db->prefix . self::TABLE;
    }

    public static function available() {
        if (!isset(jssupportticket::$_db) || !is_object(jssupportticket::$_db)) return false;
        $jsst_table = self::table();
        return (jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) === $jsst_table);
    }

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);
        /* On init, not on template_redirect: the link is often opened from an
           e-mail by somebody who is not signed in, and it has to work before
           anything decides which template that person is entitled to see.
           (Roadmap 6.0-AI-10) */
        add_action('init', array(__CLASS__, 'handleHandoff'), 20);
    }

    /* ------------------------------------------------------------------ *
     * How much a person has to do
     * ------------------------------------------------------------------ */

    public static function mode() {
        $jsst_mode = (string) get_option(self::OPT_MODE, self::MODE_THRESHOLD);
        return in_array($jsst_mode, array(self::MODE_THRESHOLD, self::MODE_ALWAYS, self::MODE_NEVER), true)
            ? $jsst_mode : self::MODE_THRESHOLD;
    }

    public static function setMode($jsst_mode) {
        if (!in_array($jsst_mode, array(self::MODE_THRESHOLD, self::MODE_ALWAYS, self::MODE_NEVER), true)) {
            return false;
        }
        if (self::mode() !== $jsst_mode && class_exists('JSSTaipolicy')) {
            JSSTaipolicy::record('approval', $jsst_mode);
        }
        update_option(self::OPT_MODE, $jsst_mode, false);
        return true;
    }

    public static function modes() {
        return array(
            self::MODE_THRESHOLD => array(
                'label' => esc_html(__('Send when it is confident enough', 'js-support-ticket')),
                'blurb' => esc_html(__('The engine sends on its own once confidence, citations and grounding all clear their floors. Anything below any of them waits here for a person.', 'js-support-ticket')),
            ),
            self::MODE_ALWAYS => array(
                'label' => esc_html(__('Always ask a person first', 'js-support-ticket')),
                'blurb' => esc_html(__('Nothing reaches a customer until somebody presses approve, however sure the engine is. The right setting for the first few weeks, and for any desk where a wrong answer is expensive.', 'js-support-ticket')),
            ),
            self::MODE_NEVER => array(
                'label' => esc_html(__('Never send, only propose', 'js-support-ticket')),
                'blurb' => esc_html(__('Answers are written and kept here to read, and the approve button is not offered. Use it to judge quality on real tickets without any chance of one going out.', 'js-support-ticket')),
            ),
        );
    }

    /* ------------------------------------------------------------------ *
     * Questions that are never automatable
     * ------------------------------------------------------------------ */

    /** Phrases that make a ticket a person's, whatever the corpus contains. */
    const OPT_NEVER = 'jsst_ai_never_automate';

    /**
     * Is this a question no automatic answer may ever be sent for?
     *
     * Found by the benchmark rather than reasoned about in advance, which is
     * the whole argument for having one. "Reset the password on my colleague's
     * account and tell me the new one" retrieves the password-reset article at
     * middling coverage and reads, to every gate downstream, like a perfectly
     * grounded question - because it is. The article is right; answering it
     * automatically is the problem, and no amount of grounding catches that.
     *
     * So this is a separate refusal that runs before grounding is considered at
     * all, and it lives in core rather than in the engine for two reasons. The
     * engine kept it as a config row, so a site had a safety rule only while a
     * paid add-on was installed. And it has to be askable by something that is
     * not sending anything - the benchmark - or the one class that must never
     * fail cannot be measured.
     *
     * Substring matching, deliberately. A word list would miss "cancel my
     * subscription" written as "cancelling", and this is the wrong place to be
     * clever: a false positive costs one ticket going to a person, which is
     * where it was going anyway before automation existed.
     */
    public static function neverAutomate($jsst_text) {
        $jsst_text = jssupportticketphplib::JSST_strtolower(wp_strip_all_tags((string) $jsst_text));
        if (trim($jsst_text) === '') return false;

        foreach (self::neverAutomateTerms() as $jsst_term) {
            if ($jsst_term !== '' && strpos($jsst_text, $jsst_term) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * The terms, from the site's setting or from the shipped list.
     *
     * The shipped list is money, law, identity and credentials - the four
     * things where being wrong is not a worse answer but a different kind of
     * problem. An empty setting means the shipped list, not "no rule": somebody
     * clearing a box should not silently disarm this.
     */
    public static function neverAutomateTerms() {
        /* Money, law, identity and credentials: the four places where being
           wrong is not a worse answer but a different kind of problem. */
        $jsst_terms = array(
            'refund', 'chargeback', 'charged twice', 'invoice dispute', 'stop charging',
            'cancel', 'cancelling', 'canceling', 'subscription and stop',
            'lawyer', 'solicitor', 'legal action', 'take legal', 'sue ', 'liable', 'liability',
            'delete my account', 'delete my data', 'erase my data', 'close my account',
            'every piece of data', 'gdpr request',
            'password on my', 'password for', 'tell me the new one', 'reset the password on',
            'api key', 'credentials', 'admin access', 'admin credentials',
            'billing address', 'change the card', 'change my card',
        );

        /* A site's own terms are ADDED to that list, never substituted for it.
           A safety list where saving one word silently drops twenty others is a
           trap, and it is the kind that is only discovered by the ticket it
           failed to stop. Anything genuinely unwanted can be taken out through
           the filter below, which is a deliberate act rather than a side effect
           of editing a text box. */
        $jsst_extra = trim((string) get_option(self::OPT_NEVER, ''));

        /* The engine kept this as a config row before 6.0-AI-05, so a desk that
           curated its list there keeps every word of it. */
        if (isset(jssupportticket::$_config['aiagent_autopilot_blacklist_keywords'])) {
            $jsst_extra .= ',' . (string) jssupportticket::$_config['aiagent_autopilot_blacklist_keywords'];
        }

        foreach (explode(',', jssupportticketphplib::JSST_strtolower($jsst_extra)) as $jsst_term) {
            $jsst_term = trim($jsst_term);
            if ($jsst_term !== '') $jsst_terms[] = $jsst_term;
        }

        return apply_filters('jsst_ai_never_automate_terms', array_values(array_unique($jsst_terms)));
    }

    /* ------------------------------------------------------------------ *
     * The decision
     * ------------------------------------------------------------------ */

    /**
     * Record a proposed answer and say what may happen to it.
     *
     * The engine calls this **instead of** deciding for itself, which is the
     * point: "never auto-send below the threshold" has to be one rule in one
     * place, or it is as many rules as there are callers. The engine still owns
     * everything upstream - retrieval, citations, grounding, its own confidence
     * - and hands the result here.
     *
     * @param array $jsst_args ticket, body, question, confidence, threshold,
     *                         sources, coverage, overlap, reason, engine.
     * @return array state and whether the caller may send now.
     */
    public static function record($jsst_args) {
        $jsst_row = array(
            'ticketid'   => isset($jsst_args['ticket']) ? (int) $jsst_args['ticket'] : 0,
            'replyid'    => 0,
            'engine'     => isset($jsst_args['engine']) ? substr((string) $jsst_args['engine'], 0, 50) : '',
            'confidence' => isset($jsst_args['confidence']) ? max(0, min(100, (int) $jsst_args['confidence'])) : 0,
            'coverage'   => isset($jsst_args['coverage']) && $jsst_args['coverage'] !== null ? (int) $jsst_args['coverage'] : null,
            'overlap'    => isset($jsst_args['overlap']) && $jsst_args['overlap'] !== null ? (int) $jsst_args['overlap'] : null,
            'body'       => isset($jsst_args['body']) ? (string) $jsst_args['body'] : '',
            'sources'    => wp_json_encode(isset($jsst_args['sources']) ? (array) $jsst_args['sources'] : array()),
            'reason'     => isset($jsst_args['reason']) ? substr((string) $jsst_args['reason'], 0, 255) : '',
            'created'    => current_time('Y-m-d H:i:s'),
        );

        $jsst_threshold = isset($jsst_args['threshold']) ? (int) $jsst_args['threshold'] : 100;
        $jsst_mode      = self::mode();

        /* Three ways this can be a no, and they are asked in this order so the
           strictest wins. The master switch is asked first even though the
           engine already asked it: by the time an answer exists, minutes may
           have passed under cron, and somebody switching AI off during an
           incident means off now - not off for whatever has not started yet. */
        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled()) {
            $jsst_send = false;
            $jsst_row['reason'] = esc_html(__('AI was switched off before this could be sent.', 'js-support-ticket'));
        } elseif (isset($jsst_args['question']) && self::neverAutomate($jsst_args['question'])) {
            /* Before confidence, before grounding. A question about money, law,
               identity or credentials is a person's whatever the corpus says -
               and the corpus often says something perfectly relevant, which is
               exactly why this cannot be left to the grounding gates. */
            $jsst_send = false;
            $jsst_row['reason'] = esc_html(__('This kind of question is never answered automatically.', 'js-support-ticket'));
        } elseif (class_exists('JSSTairollout') && !JSSTairollout::live()) {
            /* Asked here as well as at the door, for the same reason as the
               master switch above: this is the far end of a cron delay that can
               be half an hour long, and stopping automatic answers has to mean
               the ones already in flight too, or the switch does not do what the
               person pressing it needs it to do. Held, not dropped - the answer
               is still worth a person's glance. It is asked after the
               never-automate rule so that the reason on the record names the
               permanent reason rather than today's one. (Roadmap 6.0-AI-06) */
            $jsst_send = false;
            $jsst_row['reason'] = esc_html(__('Automatic answers are switched off or paused, so this is waiting for a person.', 'js-support-ticket'));
        } elseif (class_exists('JSSTairollout') && $jsst_row['ticketid'] > 0
                  && JSSTairollout::capReached($jsst_row['ticketid'])) {
            /* The cap is counted again at the end because it can be reached
               during the run: an agent approving a held answer while this one
               was being written is the ordinary way it happens. */
            $jsst_send = false;
            $jsst_row['reason'] = esc_html(__('This ticket has had as many automatic answers as the rollout allows.', 'js-support-ticket'));
        } elseif ($jsst_mode === self::MODE_NEVER || $jsst_mode === self::MODE_ALWAYS) {
            $jsst_send = false;
            if ($jsst_row['reason'] === '') {
                $jsst_row['reason'] = ($jsst_mode === self::MODE_NEVER)
                    ? esc_html(__('This site never sends automatic answers.', 'js-support-ticket'))
                    : esc_html(__('Waiting for a person to approve it.', 'js-support-ticket'));
            }
        } else {
            $jsst_send = self::clearsThreshold($jsst_row['confidence'], $jsst_threshold);
        }

        $jsst_row['state'] = $jsst_send ? self::STATE_SENT : self::STATE_HELD;

        if (!self::available()) {
            /* No table is not a reason to send something a person has not seen.
               Failing closed here costs a site its automatic replies until the
               schema repairs itself on the next admin page load; failing open
               would send unreviewed answers on exactly the sites whose schema
               is already in a state nobody has noticed. */
            return array('id' => 0, 'send' => false, 'state' => self::STATE_HELD,
                         'reason' => esc_html(__('The review record is unavailable, so nothing was sent.', 'js-support-ticket')));
        }

        jssupportticket::$_db->insert(self::table(), $jsst_row);

        /* An answer held because it rested on very little of the site's own
           content is a gap in that content, not a fault in the engine. Recorded
           only when there was a question to record and the coverage was
           genuinely thin - a held answer with good coverage is a threshold
           decision and has nothing to do with the documentation.
           (Roadmap 6.0-AI-12) */
        if (!$jsst_send && class_exists('JSSTaigaps') && isset($jsst_args['question'])
            && $jsst_row['coverage'] !== null && $jsst_row['coverage'] < 50) {
            JSSTaigaps::record(JSSTaigaps::KIND_WEAK, $jsst_args['question'], array(
                'ticket' => $jsst_row['ticketid'], 'coverage' => $jsst_row['coverage']));
        }

        return array(
            'id'     => (int) jssupportticket::$_db->insert_id,
            'send'   => $jsst_send,
            'state'  => $jsst_row['state'],
            'reason' => $jsst_row['reason'],
        );
    }

    /**
     * Does this confidence clear the floor?
     *
     * Its own function so that something asking the question without proposing
     * anything - the benchmark, a preview, a report - gets the same answer as
     * the live path rather than a copy of it that drifts. A floor is a floor:
     * the setting reads "minimum confidence", so equal to it passes.
     */
    public static function clearsThreshold($jsst_confidence, $jsst_threshold) {
        return ((int) $jsst_confidence >= (int) $jsst_threshold);
    }

    /** Attach the reply an approved or auto-sent answer became. */
    public static function attachReply($jsst_id, $jsst_replyid) {
        if (!self::available()) return false;
        return (bool) jssupportticket::$_db->update(self::table(),
            array('replyid' => (int) $jsst_replyid),
            array('id' => (int) $jsst_id));
    }

    /** The engine could not send after all; the row must not claim it did. */
    public static function failed($jsst_id, $jsst_reason) {
        if (!self::available()) return false;
        return (bool) jssupportticket::$_db->update(self::table(),
            array('state' => self::STATE_HELD, 'reason' => substr((string) $jsst_reason, 0, 255)),
            array('id' => (int) $jsst_id));
    }

    /* ------------------------------------------------------------------ *
     * What a person does about it
     * ------------------------------------------------------------------ */

    public static function get($jsst_id) {
        if (!self::available()) return false;
        return jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE id = %d", (int) $jsst_id));
    }

    /**
     * Send a held answer.
     *
     * The sending itself is not core's: the engine owns how a reply is written,
     * which display name it carries and which of the customer's notification
     * settings it respects. So core asks, through a filter, and only records a
     * send that something actually reported doing. A filter nobody answered
     * leaves the row held rather than marking it sent - the failure mode of the
     * opposite is a queue that empties itself while the customer hears nothing.
     */
    public static function approve($jsst_id, $jsst_note = '') {
        $jsst_row = self::get($jsst_id);
        if (!$jsst_row || $jsst_row->state !== self::STATE_HELD) {
            return false;
        }
        if (self::mode() === self::MODE_NEVER) {
            return false;
        }
        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled()) {
            return false;
        }

        $jsst_replyid = (int) apply_filters('jsst_ai_send_answer', 0, $jsst_row);
        if ($jsst_replyid < 1) {
            return false;
        }

        jssupportticket::$_db->update(self::table(), array(
            'state'     => self::STATE_SENT,
            'replyid'   => $jsst_replyid,
            'decidedby' => get_current_user_id(),
            'decided'   => current_time('Y-m-d H:i:s'),
            'note'      => substr((string) $jsst_note, 0, 255),
        ), array('id' => (int) $jsst_id));

        self::$jsst_retracted = null;
        return true;
    }

    /** A person said no. Nothing was sent, and the text is kept for the record. */
    public static function reject($jsst_id, $jsst_note = '') {
        $jsst_row = self::get($jsst_id);
        if (!$jsst_row || $jsst_row->state !== self::STATE_HELD) {
            return false;
        }

        jssupportticket::$_db->update(self::table(), array(
            'state'     => self::STATE_REJECTED,
            'decidedby' => get_current_user_id(),
            'decided'   => current_time('Y-m-d H:i:s'),
            'note'      => substr((string) $jsst_note, 0, 255),
        ), array('id' => (int) $jsst_id));

        /* An agent reading a proposed answer and throwing it away is the
           best-informed judgement available that the content behind it was not
           good enough. (Roadmap 6.0-AI-12) */
        if (class_exists('JSSTaigaps')) {
            JSSTaigaps::recordForTicket(JSSTaigaps::KIND_REJECTED, (int) $jsst_row->ticketid);
        }

        return true;
    }

    /**
     * Withdraw an answer that has already gone out.
     *
     * Marks it withdrawn rather than deleting it, for the reason in this
     * class's header: the customer has the e-mail either way, and a ticket that
     * no longer contains what they were sent is a ticket nobody can reason
     * about. The reply stays where it is and the timeline draws it struck
     * through with the correction beside it.
     *
     * Reopening is not optional and not configurable. An automatic answer that
     * closed a ticket and then turned out to be wrong has left a customer with
     * no way back in; that is the failure this whole task exists to prevent.
     */
    public static function retract($jsst_id, $jsst_note = '') {
        $jsst_row = self::get($jsst_id);
        if (!$jsst_row || $jsst_row->state !== self::STATE_SENT) {
            return false;
        }

        jssupportticket::$_db->update(self::table(), array(
            'state'     => self::STATE_RETRACTED,
            'decidedby' => get_current_user_id(),
            'decided'   => current_time('Y-m-d H:i:s'),
            'note'      => substr((string) $jsst_note, 0, 255),
        ), array('id' => (int) $jsst_id));

        self::$jsst_retracted = null;

        self::reopen((int) $jsst_row->ticketid);

        /* Announced so the rest of the product can react - the activity log,
           a webhook, an automation rule that tells an agent to pick the ticket
           up. Fired after the state is written, so anything listening reads the
           withdrawal rather than the send. */
        do_action('jsst_ai_answer_retracted', $jsst_row, $jsst_note);

        return true;
    }

    /**
     * Put a ticket that an automatic answer closed back in front of a person.
     *
     * Only from a finished state. Reopening a ticket an agent has since
     * deliberately moved somewhere else would undo their work in order to fix
     * ours, which is not a trade this class gets to make on their behalf.
     *
     * Routed through JSSTticketservice::reopen() wherever it exists, because
     * reopening is not a status write: it is a status write plus the reopen
     * event, the activity entry and whatever automation listens for it. A
     * second implementation here would produce tickets that were reopened
     * without anything having noticed. The direct write is only the path for a
     * core too old to have the service.
     */
    private static function reopen($jsst_ticketid) {
        $jsst_ticket = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT id, status FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
            $jsst_ticketid));
        if (!$jsst_ticket || !self::isFinished((int) $jsst_ticket->status)) {
            return false;   // nothing to reopen, which is not a failure
        }

        if (class_exists('JSSTticketservice') && method_exists('JSSTticketservice', 'reopen')) {
            /* It can legitimately refuse - the service guards on the reopen
               capability, and whoever is withdrawing may not hold it. The
               refusal is returned rather than worked around, because the
               alternative is this class writing the status itself and quietly
               beating a permission check somebody set on purpose. What must not
               happen is the screen claiming the ticket was reopened when it was
               not, so the caller is told.

               Wrapped, because one of the callers is a link in an e-mail opened
               by a customer. Reopening runs through the ticket model, which
               reaches into whichever add-ons are installed, and a fatal
               anywhere down there would answer that customer with a white page
               instead of the help they asked for. A reopen that failed is worth
               far less than a request that survived. */
            try {
                return !is_wp_error(JSSTticketservice::reopen($jsst_ticketid));
            } catch (Exception $jsst_e) {
                self::note('could not reopen ticket ' . (int) $jsst_ticketid . ': ' . $jsst_e->getMessage());
                return false;
            } catch (Error $jsst_e) {
                self::note('could not reopen ticket ' . (int) $jsst_ticketid . ': ' . $jsst_e->getMessage());
                return false;
            }
        }

        return (bool) jssupportticket::$_db->update(
            jssupportticket::$_db->prefix . 'js_ticket_tickets',
            array('status' => (int) apply_filters('jsst_ai_reopen_status', 1)),
            array('id' => $jsst_ticketid)
        );
    }

    /**
     * Was the ticket still finished after the last withdrawal?
     *
     * Asked by the screen so its message can say what actually happened rather
     * than what usually happens. Reading the ticket back beats trusting a
     * return value: the reopen may have been refused, or something listening to
     * the reopen event may have moved it again.
     */
    public static function stillClosed($jsst_ticketid) {
        $jsst_status = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT status FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
            (int) $jsst_ticketid));
        return ($jsst_status !== null && self::isFinished((int) $jsst_status));
    }

    /**
     * Statuses that mean the ticket is finished.
     *
     * 5 is Closed and 6 is Close Due To Merge, which is the same pair
     * JSSTticketservice and the queue filters use. Filterable because a site can
     * add statuses, but not *configurable* - a wrong answer here means either
     * failing to reopen a closed ticket or reopening one somebody is working.
     */
    private static function isFinished($jsst_status) {
        return in_array((int) $jsst_status,
            (array) apply_filters('jsst_ai_finished_statuses', array(5, 6)), true);
    }

    /* ------------------------------------------------------------------ *
     * Reading it back
     * ------------------------------------------------------------------ */

    /** Answers waiting for a person, oldest first - a queue, not a feed. */
    public static function pending($jsst_limit = 50) {
        if (!self::available()) return array();
        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE state = %s ORDER BY created ASC LIMIT %d",
            self::STATE_HELD, max(1, min(200, (int) $jsst_limit))));
    }

    /** Everything decided, newest first. */
    public static function decided($jsst_limit = 50) {
        if (!self::available()) return array();
        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE state <> %s ORDER BY COALESCE(decided, created) DESC LIMIT %d",
            self::STATE_HELD, max(1, min(200, (int) $jsst_limit))));
    }

    /** How many in each state, for the badge and the metrics. */
    public static function counts() {
        $jsst_out = array(self::STATE_HELD => 0, self::STATE_SENT => 0,
                          self::STATE_REJECTED => 0, self::STATE_RETRACTED => 0);
        if (!self::available()) return $jsst_out;

        foreach ((array) jssupportticket::$_db->get_results(
                "SELECT state, COUNT(*) AS jsst_total FROM `" . self::table() . "` GROUP BY state") as $jsst_row) {
            $jsst_out[(string) $jsst_row->state] = (int) $jsst_row->jsst_total;
        }
        return $jsst_out;
    }

    /**
     * Reply ids that have been withdrawn.
     *
     * One query per request rather than one per reply drawn: a busy ticket has
     * thirty replies on it and the timeline asks about every one of them.
     */
    public static function retractedReplies() {
        if (self::$jsst_retracted !== null) return self::$jsst_retracted;

        self::$jsst_retracted = array();
        if (!self::available()) return self::$jsst_retracted;

        foreach ((array) jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
                "SELECT replyid FROM `" . self::table() . "` WHERE state = %s AND replyid > 0",
                self::STATE_RETRACTED)) as $jsst_replyid) {
            self::$jsst_retracted[(int) $jsst_replyid] = true;
        }
        return self::$jsst_retracted;
    }

    public static function isRetracted($jsst_replyid) {
        $jsst_all = self::retractedReplies();
        return isset($jsst_all[(int) $jsst_replyid]);
    }

    public static function sources($jsst_row) {
        $jsst_raw = is_object($jsst_row) ? $jsst_row->sources : (isset($jsst_row['sources']) ? $jsst_row['sources'] : '');
        $jsst_out = json_decode((string) $jsst_raw, true);
        return is_array($jsst_out) ? $jsst_out : array();
    }

    /* ------------------------------------------------------------------ *
     * Shadow mode (Roadmap 6.0-AI-04)
     * ------------------------------------------------------------------ */

    /**
     * Pair answers that were never sent with what the agent wrote instead.
     *
     * This is the number that sells automatic replies and no other number does:
     * not "the model says it is 90% confident", which is the model marking its
     * own homework, but "on your last two hundred tickets it said roughly what
     * your agent said". A site can watch that figure for a month before letting
     * anything out.
     *
     * Done as a batched sweep rather than a hook on the reply save path. There
     * is no action fired when a reply is stored, and adding one into the hot
     * path that every ticket in the product goes through - to serve a reporting
     * feature - is a poor trade: this is reporting, it can be a minute late, and
     * a sweep that fails costs a row in a table nobody is blocked on.
     *
     * The agent reply taken is the FIRST staff reply after the proposal, not the
     * latest. The latest is a conversation three messages deep about something
     * else; the first is the answer to the question the AI was also answering,
     * which is the only fair comparison.
     *
     * @param int $jsst_limit Rows per pass.
     * @return int How many were paired.
     */
    public static function pairShadows($jsst_limit = 50) {
        if (!self::available()) return 0;

        $jsst_limit = max(1, min(200, (int) $jsst_limit));
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, ticketid, body, created FROM `" . self::table() . "`
              WHERE agentreply = 0 AND state <> %s
              ORDER BY id ASC LIMIT %d",
            self::STATE_SENT, $jsst_limit
        ));

        $jsst_replies = jssupportticket::$_db->prefix . 'js_ticket_replies';
        $jsst_paired  = 0;

        foreach ((array) $jsst_rows as $jsst_row) {
            /* Autopilot's own replies are excluded. Comparing the AI's answer
               against a draft the same engine wrote would measure nothing but
               how repeatable the model is. */
            $jsst_reply = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                "SELECT id, message FROM `" . $jsst_replies . "`
                  WHERE ticketid = %d AND staffid > 0 AND created >= %s
                    AND (ticketviaautopilot IS NULL OR ticketviaautopilot = 0)
                    AND (is_ai_draft IS NULL OR is_ai_draft = 0)
                  ORDER BY created ASC, id ASC LIMIT 1",
                (int) $jsst_row->ticketid, $jsst_row->created
            ));
            if (!$jsst_reply) continue;

            $jsst_agent = wp_strip_all_tags((string) $jsst_reply->message);

            jssupportticket::$_db->update(self::table(), array(
                'agentreply' => (int) $jsst_reply->id,
                'agentbody'  => $jsst_agent,
                'agreement'  => self::agreement((string) $jsst_row->body, $jsst_agent),
                'outcome'    => self::outcomeOf((int) $jsst_row->ticketid),
                'pairedat'   => current_time('Y-m-d H:i:s'),
            ), array('id' => (int) $jsst_row->id));

            $jsst_paired++;
        }

        return $jsst_paired;
    }

    /**
     * How much two answers say the same thing, 0-100.
     *
     * Deliberately crude and deliberately explained on the screen, because the
     * alternative - an embedding similarity a site owner cannot check - would
     * be a number they have to take on faith, which is the thing shadow mode
     * exists to replace. This counts the content words the two answers share,
     * measured against the shorter of the two so that an agent who wrote three
     * paragraphs of pleasantries around the same instruction does not score as
     * a disagreement.
     *
     * Short words are dropped for the same reason the retriever drops them: on
     * "the", "and" and "your" every pair of English sentences agrees.
     */
    public static function agreement($jsst_ai, $jsst_agent) {
        $jsst_a = self::words($jsst_ai);
        $jsst_b = self::words($jsst_agent);

        if (empty($jsst_a) || empty($jsst_b)) {
            return 0;
        }

        $jsst_shared = count(array_intersect($jsst_a, $jsst_b));
        $jsst_floor  = min(count($jsst_a), count($jsst_b));

        return (int) round(($jsst_shared / $jsst_floor) * 100);
    }

    /** Distinct content words, lowercased. */
    private static function words($jsst_text) {
        $jsst_text = jssupportticketphplib::JSST_strtolower(wp_strip_all_tags((string) $jsst_text));
        $jsst_bits = preg_split('/[^a-z0-9]+/', $jsst_text, -1, PREG_SPLIT_NO_EMPTY);

        $jsst_out = array();
        foreach ((array) $jsst_bits as $jsst_word) {
            if (strlen($jsst_word) < 4) continue;   // "the", "your", "and", "have"
            $jsst_out[$jsst_word] = true;
        }
        return array_keys($jsst_out);
    }

    /** Where the ticket ended up, as a status id we can label later. */
    private static function outcomeOf($jsst_ticketid) {
        $jsst_status = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT status FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
            (int) $jsst_ticketid));
        return ($jsst_status === null) ? '' : (string) (int) $jsst_status;
    }

    /**
     * The shadow picture, in the numbers somebody decides on.
     *
     * `wouldhavesent` is the one that matters and it is counted against the
     * threshold the site has set right now, not the one that applied when each
     * answer was written - the question being asked is "what would happen if I
     * switched this on today".
     */
    public static function shadowStats($jsst_threshold) {
        $jsst_out = array(
            'proposed' => 0, 'paired' => 0, 'agreement' => 0,
            'wouldhavesent' => 0, 'agreedwhenconfident' => null, 'threshold' => (int) $jsst_threshold,
        );
        if (!self::available()) return $jsst_out;

        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT COUNT(*) AS proposed,
                    SUM(CASE WHEN agentreply > 0 THEN 1 ELSE 0 END) AS paired,
                    AVG(CASE WHEN agentreply > 0 THEN agreement END) AS agreement,
                    SUM(CASE WHEN confidence >= %d THEN 1 ELSE 0 END) AS wouldhavesent,
                    AVG(CASE WHEN agentreply > 0 AND confidence >= %d THEN agreement END) AS confident
               FROM `" . self::table() . "` WHERE state <> %s",
            (int) $jsst_threshold, (int) $jsst_threshold, self::STATE_SENT
        ));
        if (!$jsst_row) return $jsst_out;

        $jsst_out['proposed']      = (int) $jsst_row->proposed;
        $jsst_out['paired']        = (int) $jsst_row->paired;
        $jsst_out['agreement']     = ($jsst_row->agreement === null) ? 0 : (int) round($jsst_row->agreement);
        $jsst_out['wouldhavesent'] = (int) $jsst_row->wouldhavesent;
        /* Left null rather than zeroed when nothing has cleared the threshold
           yet: "0% agreement" and "no answer has been that confident" are
           different facts and the screen says so. */
        $jsst_out['agreedwhenconfident'] = ($jsst_row->confident === null) ? null : (int) round($jsst_row->confident);

        return $jsst_out;
    }

    /** Paired answers, newest first, for the comparison table. */
    public static function shadows($jsst_limit = 30) {
        if (!self::available()) return array();
        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "`
              WHERE agentreply > 0 AND state <> %s
              ORDER BY pairedat DESC LIMIT %d",
            self::STATE_SENT, max(1, min(200, (int) $jsst_limit))));
    }

    /* ------------------------------------------------------------------ *
     * What the customer sees
     * ------------------------------------------------------------------ */

    /**
     * The "where this came from" block appended to an automatic answer.
     *
     * Citations are validated before an answer is allowed out, but validation
     * the customer cannot see is a promise made to the site owner rather than
     * to the person reading the reply. This is the half that faces them: the
     * pages the answer was written from, linked where a link exists.
     *
     * A source with no URL is still named. Canned responses and past tickets
     * have no page to send anybody to, and "this came from an internal note"
     * is more honest than an answer that appears to rest on nothing.
     */
    public static function citationBlock($jsst_sources) {
        $jsst_sources = (array) $jsst_sources;
        if (empty($jsst_sources) || !apply_filters('jsst_ai_show_citations', true)) {
            return '';
        }

        $jsst_items = array();
        foreach ($jsst_sources as $jsst_source) {
            $jsst_title = isset($jsst_source['title']) ? trim((string) $jsst_source['title']) : '';
            if ($jsst_title === '') continue;

            $jsst_url = isset($jsst_source['url']) ? (string) $jsst_source['url'] : '';
            $jsst_items[] = ($jsst_url !== '')
                ? '<li><a href="' . esc_url($jsst_url) . '">' . esc_html($jsst_title) . '</a></li>'
                : '<li>' . esc_html($jsst_title) . '</li>';
        }

        if (empty($jsst_items)) return '';

        return "\n<div class=\"jsst-ai-citations\">"
             . '<p>' . esc_html(__('This answer was written from:', 'js-support-ticket')) . '</p>'
             . '<ul>' . implode('', $jsst_items) . '</ul>'
             . "</div>\n";
    }

    /**
     * The confidence, said in words a customer can act on. (Roadmap 6.0-AI-10)
     *
     * A percentage is the wrong unit for the reader. "87% confident" invites
     * them to treat it as a probability the answer is correct, which it is not
     * - it is a model's estimate of its own certainty, which is a different
     * and much weaker thing. What a customer needs is whether to act on this
     * or wait for a person, so that is what the sentence says.
     *
     * The bands are deliberately coarse. Three of them, because a reader
     * distinguishing five degrees of machine confidence is a reader being asked
     * to do the desk's job.
     */
    public static function confidenceLanguage($jsst_confidence) {
        $jsst_confidence = (int) $jsst_confidence;

        if ($jsst_confidence >= 85) {
            return esc_html(__('This closely matches something in our documentation.', 'js-support-ticket'));
        }
        if ($jsst_confidence >= 60) {
            return esc_html(__('This is our best match, but it may not cover your exact case.', 'js-support-ticket'));
        }
        return esc_html(__('We are not certain this answers your question.', 'js-support-ticket'));
    }

    /**
     * The block appended to an automatic answer: what wrote it, how sure it is,
     * where it came from, and how to reach a person. (Roadmap 6.0-AI-10)
     *
     * All four together, once, rather than four features that each decided
     * separately whether to appear. A disclosure without a way out is a notice;
     * a way out without a disclosure is a link nobody understands. The handoff
     * is last because it is the thing somebody reaches for after reading the
     * rest and deciding it did not help.
     */
    public static function customerBlock($jsst_row, $jsst_confidence = null, $jsst_sources = array()) {
        if (!apply_filters('jsst_ai_show_disclosure', true)) {
            return self::citationBlock($jsst_sources);
        }

        $jsst_ticketid = is_object($jsst_row) ? (int) $jsst_row->ticketid : (int) $jsst_row;
        if ($jsst_confidence === null && is_object($jsst_row)) {
            $jsst_confidence = (int) $jsst_row->confidence;
        }

        $jsst_html = "\n<div class=\"jsst-ai-note\">"
                   . '<p class="jsst-ai-disclosure">'
                   . esc_html(class_exists('JSSTaipolicy') ? JSSTaipolicy::disclosure() : '')
                   . '</p>';

        if ($jsst_confidence !== null) {
            $jsst_html .= '<p class="jsst-ai-confidence">'
                        . esc_html(self::confidenceLanguage($jsst_confidence)) . '</p>';
        }

        $jsst_html .= self::citationBlock($jsst_sources);

        $jsst_handoff = self::handoffUrl($jsst_ticketid);
        if ($jsst_handoff !== '') {
            $jsst_html .= '<p class="jsst-ai-handoff"><a href="' . esc_url($jsst_handoff) . '">'
                        . esc_html(__('This did not answer my question - pass it to a person', 'js-support-ticket'))
                        . '</a></p>';
        }

        return $jsst_html . "</div>\n";
    }

    /* ------------------------------------------------------------------ *
     * The way out (Roadmap 6.0-AI-10)
     * ------------------------------------------------------------------ */

    /** Query argument carrying a handoff request. */
    const HANDOFF_ARG = 'jsst_ai_handoff';

    /**
     * A one-click link that puts a person on the ticket.
     *
     * Signed rather than nonced, and that is the whole design problem. A WP
     * nonce is tied to a session and expires in a day; the person clicking this
     * is often a guest reading an e-mail three days later, so a nonce would
     * mean "sign in first" - which is precisely the friction the task exists to
     * remove. So the link carries an HMAC over the ticket id and the ticket's
     * own hash, keyed on the site's salt: unguessable, valid as long as the
     * ticket is, and it authorises exactly one thing.
     *
     * That one thing is deliberately small. It does not close, reassign,
     * delete or reply. It marks a ticket as wanting a human, which is a request
     * anybody who can already read the ticket is entitled to make - so the
     * worst case for a leaked link is a ticket flagged for attention.
     */
    public static function handoffUrl($jsst_ticketid) {
        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_ticketid < 1 || !function_exists('add_query_arg')) return '';

        $jsst_token = self::handoffToken($jsst_ticketid);
        if ($jsst_token === '') return '';

        $jsst_base = apply_filters('jsst_ai_handoff_base', home_url('/'), $jsst_ticketid);

        return add_query_arg(array(
            self::HANDOFF_ARG => $jsst_ticketid,
            'jsst_t'          => $jsst_token,
        ), $jsst_base);
    }

    /** The signature, over the ticket id and the ticket's own hash. */
    public static function handoffToken($jsst_ticketid) {
        $jsst_hash = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT hash FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
            (int) $jsst_ticketid));

        // No ticket, no link. Signing an id that does not exist would produce a
        // working token for a ticket created later with the same id.
        if ($jsst_hash === null) return '';

        return wp_hash('jsst-ai-handoff|' . (int) $jsst_ticketid . '|' . $jsst_hash);
    }

    /**
     * Act on a handoff link.
     *
     * Hooked early on the front end. Compared with hash_equals rather than ==,
     * because a token comparison that returns as soon as it finds a difference
     * tells an attacker how much of their guess was right.
     */
    public static function handleHandoff() {
        if (!isset($_GET[self::HANDOFF_ARG])) return;

        $jsst_ticketid = (int) $_GET[self::HANDOFF_ARG];
        $jsst_given    = isset($_GET['jsst_t']) ? sanitize_text_field(wp_unslash($_GET['jsst_t'])) : '';
        $jsst_expected = self::handoffToken($jsst_ticketid);

        if ($jsst_expected === '' || $jsst_given === '' || !hash_equals($jsst_expected, $jsst_given)) {
            /* Said, rather than silently dropping the visitor on the home page
               as though the click had worked. */
            wp_die(
                esc_html(__('This link is no longer valid. Please reply to your ticket instead and a member of the team will pick it up.', 'js-support-ticket')),
                esc_html(__('Link not valid', 'js-support-ticket')),
                array('response' => 200, 'link_url' => esc_url(home_url('/')), 'link_text' => esc_html(__('Back to the site', 'js-support-ticket'))));
        }

        self::requestHuman($jsst_ticketid);

        /* The click used to land on the home page with nothing said, so it read
           as a link that did not work. The owner, signed in, goes to the ticket
           with the confirmation above it; anybody else - usually a customer
           reading the e-mail - is told on a page of its own. */
        $jsst_done = esc_html(__('Thank you - your ticket has been passed to a member of our team. A person will reply on the ticket; you do not need to do anything else.', 'js-support-ticket'));
        $jsst_owner = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT uid FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d", $jsst_ticketid));
        $jsst_me = class_exists('JSSTincluder') ? (int) JSSTincluder::getObjectClass('user')->uid() : 0;
        if ($jsst_owner > 0 && $jsst_me === $jsst_owner && isset($_COOKIE['_wpjshd_session_'])) {
            JSSTmessage::setMessage($jsst_done, 'updated');
            wp_safe_redirect(jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'ticketdetail', 'jssupportticketid' => $jsst_ticketid)));
            exit;
        }
        wp_die($jsst_done, esc_html(__('Passed to a person', 'js-support-ticket')),
            array('response' => 200, 'link_url' => esc_url(home_url('/')), 'link_text' => esc_html(__('Back to the site', 'js-support-ticket'))));
    }

    /**
     * Put the ticket back in front of a person.
     *
     * Reopens it if an automatic answer had closed it, marks the outstanding
     * answers on it as rejected by the customer, and announces itself so
     * routing and notifications can do their part. Idempotent: somebody
     * clicking the link four times because nothing visibly happened must not
     * produce four of anything.
     */
    public static function requestHuman($jsst_ticketid) {
        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_ticketid < 1) return false;

        if (get_transient('jsst_ai_handoff_' . $jsst_ticketid)) {
            return true;
        }
        set_transient('jsst_ai_handoff_' . $jsst_ticketid, 1, 5 * MINUTE_IN_SECONDS);

        /* The strongest signal on the knowledge-gap screen, and the only one a
           customer sends deliberately: they read an automatic answer and asked
           for a person anyway. Recorded inside the idempotency guard above, so
           somebody clicking the link four times because nothing visibly
           happened writes one row rather than four. (Roadmap 6.0-AI-12) */
        if (class_exists('JSSTaigaps')) {
            JSSTaigaps::recordForTicket(JSSTaigaps::KIND_HANDOFF, $jsst_ticketid);
        }

        self::reopen($jsst_ticketid);

        /* Back in the Waiting on Agent queue. An automatic reply marks the
           ticket answered, and this used to leave it there - the customer had
           asked for a person and no queue showed it to one. The history line
           is what an agent opening the ticket reads. */
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_tickets',
            array('isanswered' => 0, 'updated' => current_time('mysql')), array('id' => $jsst_ticketid));
        if (class_exists('JSSTmergedaddon') && JSSTmergedaddon::featureEnabled('tickethistory')) {
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1,
                esc_html(__('Person requested', 'js-support-ticket')),
                esc_html(__('The customer said the automatic answer did not help and asked for a person.', 'js-support-ticket')),
                esc_html(__('Successfully', 'js-support-ticket')));
        }
        if (class_exists('JSSTqueueengine')) {
            JSSTqueueengine::flushCounts();
        }

        if (self::available()) {
            jssupportticket::$_db->query(jssupportticket::$_db->prepare(
                "UPDATE `" . self::table() . "`
                    SET state = %s, note = %s, decided = %s
                  WHERE ticketid = %d AND state = %s",
                self::STATE_REJECTED,
                esc_html(__('The customer asked for a person.', 'js-support-ticket')),
                current_time('Y-m-d H:i:s'), $jsst_ticketid, self::STATE_HELD));
        }

        do_action('jsst_ai_human_requested', $jsst_ticketid);
        return true;
    }

    /** Somewhere for a swallowed failure to be found later. */
    private static function note($jsst_message) {
        if (class_exists('JSSTincluder')) {
            JSSTincluder::getJSModel('systemerror')->addSystemError('AI review - ' . $jsst_message);
        }
    }

    /** How a state reads on a screen. */
    public static function states() {
        return array(
            self::STATE_HELD      => array('label' => esc_html(__('Waiting', 'js-support-ticket')),   'pill' => 'jsst-pill-warn'),
            self::STATE_SENT      => array('label' => esc_html(__('Sent', 'js-support-ticket')),      'pill' => 'jsst-pill-ok'),
            self::STATE_REJECTED  => array('label' => esc_html(__('Rejected', 'js-support-ticket')),  'pill' => 'jsst-pill-off'),
            self::STATE_RETRACTED => array('label' => esc_html(__('Withdrawn', 'js-support-ticket')), 'pill' => 'jsst-pill-bad'),
        );
    }

}
