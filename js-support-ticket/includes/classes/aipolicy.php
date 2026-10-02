<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTaipolicy')) {
    return;
}

/**
 * The one switchboard every AI surface in this product asks before it does
 * anything. (Roadmap 6.0-AI-01, 4.0-AI-04, 6.0-AI-03)
 *
 * Before this existed a site owner who wanted to know "is this plugin sending my
 * customers' tickets anywhere?" had to answer it four times over — once for
 * Instant Resolve's autopilot, once for its deflection summaries, once for the
 * Copilot, once for the Zywrap screens — and each of those had its own switch in
 * its own vocabulary. Four switches is not a control, it is four chances to
 * leave one on.
 *
 * So there is one master switch and three lanes underneath it, and the lanes are
 * not features. They are the three genuinely different answers to "what left the
 * server":
 *
 *   onsite  Nothing left. Search over the site's own content, suggested
 *           articles, past replies matched by subject. No model, no network,
 *           no third party, no per-request cost. This is what a site running
 *           with every other lane off still gets.
 *   local   A model on infrastructure the site owner controls — Ollama,
 *           LM Studio, vLLM, llama.cpp, anything speaking the OpenAI chat
 *           completions shape. The ticket text leaves the WordPress process
 *           but not the customer's own network. (Roadmap 6.0-AI-08)
 *   hosted  A model somebody else runs. Zywrap, or a provider the site owner
 *           brought their own key for. The ticket text leaves the building.
 *
 * A privacy question, a compliance question and a cost question all resolve to
 * which of those three is switched on, which is why they are the axis rather
 * than "summaries" and "autopilot" and "deflection".
 *
 * **This class can only ever refuse.** allows() answering true does not mean an
 * engine is configured, a licence is present, a corpus is indexed or an agent
 * has permission — every one of those is still asked by whoever owns it. The
 * one thing that must never happen is the reverse: a lane switched off and
 * something still going out on it. So every caller asks here first and nothing
 * here can be persuaded to say yes on a technicality.
 */
class JSSTaipolicy {

    /* ------------------------------------------------------------------ *
     * The lanes
     * ------------------------------------------------------------------ */

    const LANE_ONSITE = 'onsite';
    const LANE_LOCAL  = 'local';
    const LANE_HOSTED = 'hosted';

    /** The master kill switch, and the three lanes under it. */
    const OPT_MASTER = 'jsst_ai_master';
    const OPT_LANES  = 'jsst_ai_lanes';

    /** How hard to scrub outbound text. See redactionModes(). */
    const OPT_REDACT = 'jsst_ai_redact';

    /** Switch flips and refusals, so "it stopped working" has an answer. */
    const OPT_JOURNAL   = 'jsst_ai_journal';
    const JOURNAL_LIMIT = 60;

    /**
     * The three lanes, in the order a person reads them: what stays here, what
     * stays on your own hardware, what goes to somebody else.
     *
     * `default` is what a site that has never opened the screen runs. onsite and
     * hosted are on because that is what every existing install already does
     * today and a silent feature removal on upgrade is worse than the setting it
     * would be protecting. local is off because there is nothing to talk to
     * until somebody types an endpoint, and a lane that is on but unreachable
     * reports as broken rather than as unconfigured.
     */
    public static function lanes() {
        $jsst_lanes = array(
            self::LANE_ONSITE => array(
                'label'   => esc_html(__('On-site answers', 'js-support-ticket')),
                'blurb'   => esc_html(__('Search over your own knowledge base, FAQs, canned responses, posts and past replies.', 'js-support-ticket')),
                'leaves'  => esc_html(__('Nothing leaves this server.', 'js-support-ticket')),
                'default' => 1,
            ),
            self::LANE_LOCAL => array(
                'label'   => esc_html(__('Local model', 'js-support-ticket')),
                'blurb'   => esc_html(__('A model you run yourself — Ollama, LM Studio, vLLM or anything that speaks the same shape.', 'js-support-ticket')),
                'leaves'  => esc_html(__('Ticket text reaches the endpoint you configured, and nowhere else.', 'js-support-ticket')),
                'default' => 0,
            ),
            self::LANE_HOSTED => array(
                'label'   => esc_html(__('Hosted model', 'js-support-ticket')),
                'blurb'   => esc_html(__('Zywrap, or another provider you brought your own key for.', 'js-support-ticket')),
                'leaves'  => esc_html(__('Ticket text is sent to that provider over HTTPS.', 'js-support-ticket')),
                'default' => 1,
            ),
        );
        return apply_filters('jsst_ai_lanes_catalogue', $jsst_lanes);
    }

    /** Is a lane id one this knows? Anything else is refused, never guessed at. */
    public static function isLane($jsst_lane) {
        $jsst_lanes = self::lanes();
        return isset($jsst_lanes[(string) $jsst_lane]);
    }

    /* ------------------------------------------------------------------ *
     * Asking
     * ------------------------------------------------------------------ */

    /**
     * The master switch. Off means off: no lane is open, no engine is asked, no
     * button is drawn, and the answer does not depend on how the question was
     * phrased or which surface asked it.
     */
    public static function enabled() {
        return ((int) get_option(self::OPT_MASTER, 1) === 1);
    }

    /** The stored lane switches, filled in from the catalogue's defaults. */
    public static function laneStates() {
        $jsst_stored = get_option(self::OPT_LANES, array());
        if (!is_array($jsst_stored)) {
            $jsst_stored = array();
        }
        $jsst_states = array();
        foreach (self::lanes() as $jsst_id => $jsst_def) {
            $jsst_states[$jsst_id] = isset($jsst_stored[$jsst_id])
                ? ((int) $jsst_stored[$jsst_id] === 1 ? 1 : 0)
                : (int) $jsst_def['default'];
        }
        return $jsst_states;
    }

    /**
     * May work go out on this lane?
     *
     * The one call every AI entry point in the product makes. An unknown lane id
     * is refused rather than treated as a new lane — a typo in a caller must not
     * become an open door.
     */
    public static function allows($jsst_lane) {
        if (!self::enabled()) {
            return false;
        }
        if (!self::isLane($jsst_lane)) {
            return false;
        }
        $jsst_states = self::laneStates();
        return ($jsst_states[(string) $jsst_lane] === 1);
    }

    /**
     * Why not, in the shape the rest of the plugin reports refusals in.
     *
     * Same envelope as JSSTcapability::explain() and JSSTavailability::available()
     * — state, reason, detail — so a screen can render any of them with one
     * partial, and only the first applicable reason is given, in the order a
     * person would say them.
     */
    public static function explain($jsst_lane) {
        if (!self::enabled()) {
            return array(
                'state'  => 'off',
                'reason' => 'master',
                'detail' => esc_html(__('AI is switched off for this site.', 'js-support-ticket')),
            );
        }
        if (!self::isLane($jsst_lane)) {
            return array(
                'state'  => 'off',
                'reason' => 'unknown',
                'detail' => esc_html(__('That is not a lane this site knows about.', 'js-support-ticket')),
            );
        }
        $jsst_lanes = self::lanes();
        if (!self::allows($jsst_lane)) {
            return array(
                'state'  => 'off',
                'reason' => 'lane',
                'detail' => sprintf(
                    /* translators: %s: the name of an AI lane, for example "Hosted model" */
                    esc_html(__('%s is switched off for this site.', 'js-support-ticket')),
                    $jsst_lanes[$jsst_lane]['label']
                ),
            );
        }
        return array(
            'state'  => 'ok',
            'reason' => '',
            'detail' => $jsst_lanes[$jsst_lane]['leaves'],
        );
    }

    /**
     * Is an on-site AI feature available and allowed?
     *
     * The one call the ticket templates make, and it answers two questions that
     * have to be asked together: does this site have the capability at all
     * (core's own, or a legacy add-on's), and is the on-site lane open.
     *
     * Written as one function rather than left as two conditions in the markup
     * because it appears nine times across the two ticket screens, and nine
     * copies of a two-part condition is nine chances for the next person to
     * copy only the half they noticed.
     *
     * The availability half is asked of JSSTmergedaddon, which is the right
     * question for a capability core has absorbed: yes for every site, whether
     * or not the old add-on is still installed.
     */
    public static function onsiteFeature($jsst_slug) {
        if (!self::allows(self::LANE_ONSITE)) {
            return false;
        }
        if (class_exists('JSSTmergedaddon')) {
            return JSSTmergedaddon::featureEnabled($jsst_slug);
        }
        return (is_array(jssupportticket::$_active_addons)
            && in_array($jsst_slug, jssupportticket::$_active_addons, true));
    }

    /* ------------------------------------------------------------------ *
     * Which lane an engine runs in
     * ------------------------------------------------------------------ */

    /**
     * The lane an engine belongs to.
     *
     * Read from the engine registry rather than kept as a second list here, so
     * an engine added through the jsst_ai_engines filter cannot end up in a lane
     * nobody declared. An engine this has never heard of is treated as hosted,
     * which is the strict reading: assume the text leaves the building unless
     * something says otherwise.
     */
    public static function laneOf($jsst_engineid) {
        if (class_exists('JSSTaiengine')) {
            $jsst_engines = JSSTaiengine::engines();
            if (isset($jsst_engines[$jsst_engineid]['lane'])
                && self::isLane($jsst_engines[$jsst_engineid]['lane'])) {
                return $jsst_engines[$jsst_engineid]['lane'];
            }
        }
        return self::LANE_HOSTED;
    }

    /** May this engine be asked at all? */
    public static function allowsEngine($jsst_engineid) {
        return self::allows(self::laneOf($jsst_engineid));
    }

    /* ------------------------------------------------------------------ *
     * What gets sent
     * ------------------------------------------------------------------ */

    /**
     * How hard to scrub text on its way out.
     *
     * Credentials come out at every level and are not a mode, because a stored
     * credential in a prompt is not a privacy preference — it is the thing
     * 5.5-SEC-01 spent a release making impossible. The modes above that trade
     * usefulness for exposure, and the trade is real in both directions: strip
     * the customer's e-mail address and the model can no longer notice that the
     * address in the ticket body is not the one on the account.
     */
    public static function redactionModes() {
        return array(
            'credentials' => array(
                'label' => esc_html(__('Credentials only', 'js-support-ticket')),
                'blurb' => esc_html(__('Stored credentials and anything shaped like an API key or password never leave.', 'js-support-ticket')),
            ),
            'contacts' => array(
                'label' => esc_html(__('Credentials and contact details', 'js-support-ticket')),
                'blurb' => esc_html(__('Also masks e-mail addresses, telephone numbers and IP addresses.', 'js-support-ticket')),
            ),
            'strict' => array(
                'label' => esc_html(__('Strict', 'js-support-ticket')),
                'blurb' => esc_html(__('Also masks card-shaped numbers and long digit runs. Safest, and the most likely to lose a detail the answer needed.', 'js-support-ticket')),
            ),
        );
    }

    public static function redactionMode() {
        $jsst_mode = (string) get_option(self::OPT_REDACT, 'credentials');
        $jsst_modes = self::redactionModes();
        return isset($jsst_modes[$jsst_mode]) ? $jsst_mode : 'credentials';
    }

    /**
     * Scrub text about to be sent to a model.
     *
     * Applied on the local and hosted lanes both. Local is somebody's own
     * hardware, not somebody's own eyes: the endpoint has a log file, that log
     * file gets backed up, and a stored database password in it is a stored
     * database password in a backup.
     *
     * Ordered longest-pattern-first on purpose. A credential value that happens
     * to contain an e-mail address must be caught as a credential, not left
     * half-masked by the contact rule having got there first.
     */
    public static function redact($jsst_text, $jsst_force = null) {
        $jsst_text = (string) $jsst_text;
        if ($jsst_text === '') {
            return $jsst_text;
        }

        /* Stored credentials are already excluded structurally rather than by
           scrubbing: they live in their own encrypted table and every context
           builder in the product reads tickets and replies, so a vault row has
           no route into a prompt at all. (Roadmap 5.5-SEC-01) What the rules
           below catch is the other case - a customer typing a password into the
           ticket body, where it is ordinary text nobody has classified. The
           seam is here so a vault that later learns to recognise its own values
           in prose can be asked without every caller changing. */
        if (class_exists('JSSTcredentialvault') && method_exists('JSSTcredentialvault', 'scrubText')) {
            $jsst_text = JSSTcredentialvault::scrubText($jsst_text);
        }

        /* Key-shaped strings, whatever the site stores. Written as one pass over
           a small set of prefixes rather than a general "long random string"
           rule, because a general rule eats order numbers and licence keys —
           exactly the things a support answer is usually about. */
        $jsst_text = preg_replace(
            '/\b(sk-[A-Za-z0-9_\-]{16,}|ghp_[A-Za-z0-9]{20,}|xox[baprs]-[A-Za-z0-9\-]{10,}|AKIA[0-9A-Z]{16}|AIza[0-9A-Za-z_\-]{30,})\b/',
            '[redacted key]',
            $jsst_text
        );

        /* A labelled secret: "password: hunter2", "api key = abc123". The label
           is kept and only the value goes, so the model can still see that a
           password was discussed and answer about it.

           The value is matched as non-space-non-punctuation rather than \S+,
           because \S+ swallows the sentence's own full stop or comma - and a
           paragraph whose punctuation has been eaten reads to a model as one
           run-on sentence, which is a needless way to make the answer worse. */
        $jsst_text = preg_replace(
            '/\b(pass(?:word|phrase)?|secret|api[ _-]?key|token|licen[cs]e[ _-]?key)\b(\s*[:=]\s*)[^\s,;.]+/i',
            '$1$2[redacted]',
            $jsst_text
        );

        /* The site's setting, unless a caller asks for a harder one.
           That override exists because the setting answers "how much do we
           scrub before sending this to somebody else's server", and a caller
           keeping text in a table on this site for six months is asking a
           different question with a different conservative answer. It only ever
           goes up: a caller cannot ask for less scrubbing than the site chose.
           (Roadmap 6.0-AI-12) */
        $jsst_mode  = self::redactionMode();
        $jsst_order = array('credentials' => 1, 'contacts' => 2, 'strict' => 3);
        if ($jsst_force !== null && isset($jsst_order[$jsst_force])
            && $jsst_order[$jsst_force] > (isset($jsst_order[$jsst_mode]) ? $jsst_order[$jsst_mode] : 0)) {
            $jsst_mode = $jsst_force;
        }

        if ($jsst_mode === 'contacts' || $jsst_mode === 'strict') {
            $jsst_text = preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', '[redacted e-mail]', $jsst_text);
            $jsst_text = preg_replace('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', '[redacted ip]', $jsst_text);
            $jsst_text = preg_replace('/\+?\d[\d\s\-().]{8,}\d/', '[redacted number]', $jsst_text);
        }

        if ($jsst_mode === 'strict') {
            $jsst_text = preg_replace('/\b(?:\d[ \-]?){13,19}\b/', '[redacted card]', $jsst_text);
            $jsst_text = preg_replace('/\b\d{9,}\b/', '[redacted digits]', $jsst_text);
        }

        return apply_filters('jsst_ai_redact', $jsst_text, $jsst_mode);
    }

    /* ------------------------------------------------------------------ *
     * What people are told
     * ------------------------------------------------------------------ */

    /**
     * The sentence shown to the site owner beside any control that will send
     * something. Says the lane, the engine and the scrubbing in one line,
     * because three separate reassurances read as marketing and one specific
     * sentence reads as a fact.
     */
    public static function notice() {
        if (!self::enabled()) {
            return esc_html(__('AI is switched off for this site. Nothing is sent anywhere.', 'js-support-ticket'));
        }
        $jsst_states = self::laneStates();
        if (empty($jsst_states[self::LANE_LOCAL]) && empty($jsst_states[self::LANE_HOSTED])) {
            return esc_html(__('Only on-site answers are switched on. No ticket text leaves this server.', 'js-support-ticket'));
        }
        $jsst_engine = class_exists('JSSTaiengine') ? JSSTaiengine::currentLabel() : '';
        if ($jsst_engine === '') {
            return esc_html(__('No answer engine is set up yet, so nothing is being sent.', 'js-support-ticket'));
        }
        return sprintf(
            /* translators: %s: the name of the configured AI engine, for example "Zywrap" */
            esc_html(__('Ticket text an agent or a rule asks about is sent to %s. Stored credentials are removed first.', 'js-support-ticket')),
            $jsst_engine
        );
    }

    /**
     * The sentence a customer sees on an answer a model wrote.
     *
     * Filterable and deliberately plain. (Roadmap 6.0-AI-10) A site may reword
     * it; a site may not remove it, which is why nothing reads an empty string
     * back as "say nothing".
     */
    public static function disclosure() {
        $jsst_text = (string) apply_filters(
            'jsst_ai_disclosure',
            __('This answer was written automatically from our documentation. Reply to this ticket and a person will pick it up.', 'js-support-ticket')
        );
        if (trim($jsst_text) === '') {
            $jsst_text = __('This answer was written automatically. Reply and a person will pick it up.', 'js-support-ticket');
        }
        return $jsst_text;
    }

    /* ------------------------------------------------------------------ *
     * Changing it
     * ------------------------------------------------------------------ */

    /**
     * Flip the master switch, and say so in the journal.
     *
     * The journal entry is the point. Somebody turns AI off during an incident
     * and the drafts stop appearing; a week later nobody remembers doing it and
     * the plugin gets the blame. One line saying who and when settles it.
     */
    public static function setMaster($jsst_on) {
        $jsst_on = $jsst_on ? 1 : 0;
        $jsst_was = self::enabled() ? 1 : 0;
        update_option(self::OPT_MASTER, $jsst_on, false);
        if ($jsst_was !== $jsst_on) {
            self::record($jsst_on ? 'master-on' : 'master-off', '');
        }
        return $jsst_on;
    }

    /** Flip one lane. An unknown lane is ignored rather than created. */
    public static function setLane($jsst_lane, $jsst_on) {
        if (!self::isLane($jsst_lane)) {
            return false;
        }
        $jsst_states = self::laneStates();
        $jsst_on = $jsst_on ? 1 : 0;
        if ($jsst_states[$jsst_lane] !== $jsst_on) {
            self::record($jsst_on ? 'lane-on' : 'lane-off', (string) $jsst_lane);
        }
        $jsst_states[$jsst_lane] = $jsst_on;
        update_option(self::OPT_LANES, $jsst_states, false);
        return true;
    }

    public static function setRedactionMode($jsst_mode) {
        $jsst_modes = self::redactionModes();
        if (!isset($jsst_modes[$jsst_mode])) {
            return false;
        }
        if (self::redactionMode() !== $jsst_mode) {
            self::record('redaction', (string) $jsst_mode);
        }
        update_option(self::OPT_REDACT, $jsst_mode, false);
        return true;
    }

    /* ------------------------------------------------------------------ *
     * The journal
     * ------------------------------------------------------------------ */

    /**
     * One line per change of policy, newest first.
     *
     * Not a usage log — that is the audit screen's job and it reads the engines'
     * own records. This is only the settings: what was switched, by whom, when.
     * It holds no ticket text and no prompt, so it can be shown to anybody who
     * can already read the settings screen.
     */
    public static function record($jsst_what, $jsst_subject) {
        $jsst_journal = get_option(self::OPT_JOURNAL, array());
        if (!is_array($jsst_journal)) {
            $jsst_journal = array();
        }
        array_unshift($jsst_journal, array(
            'what'    => (string) $jsst_what,
            'subject' => (string) $jsst_subject,
            'user'    => (int) get_current_user_id(),
            'when'    => time(),
        ));
        update_option(self::OPT_JOURNAL, array_slice($jsst_journal, 0, self::JOURNAL_LIMIT), false);
    }

    public static function journal() {
        $jsst_journal = get_option(self::OPT_JOURNAL, array());
        return is_array($jsst_journal) ? $jsst_journal : array();
    }

    /** The journal line as a sentence, for the audit screen. */
    public static function describe($jsst_entry) {
        $jsst_lanes = self::lanes();
        $jsst_subject = isset($jsst_entry['subject']) ? (string) $jsst_entry['subject'] : '';
        $jsst_name = isset($jsst_lanes[$jsst_subject]) ? $jsst_lanes[$jsst_subject]['label'] : $jsst_subject;

        switch (isset($jsst_entry['what']) ? $jsst_entry['what'] : '') {
            case 'master-on':
                return esc_html(__('AI switched on for the site', 'js-support-ticket'));
            case 'master-off':
                return esc_html(__('AI switched off for the site', 'js-support-ticket'));
            case 'lane-on':
                /* translators: %s: the name of an AI lane */
                return sprintf(esc_html(__('%s switched on', 'js-support-ticket')), $jsst_name);
            case 'lane-off':
                /* translators: %s: the name of an AI lane */
                return sprintf(esc_html(__('%s switched off', 'js-support-ticket')), $jsst_name);
            case 'redaction':
                $jsst_modes = self::redactionModes();
                $jsst_label = isset($jsst_modes[$jsst_subject]) ? $jsst_modes[$jsst_subject]['label'] : $jsst_subject;
                /* translators: %s: the name of a redaction level */
                return sprintf(esc_html(__('Redaction set to %s', 'js-support-ticket')), $jsst_label);
            case 'engine':
                /* translators: %s: the name of an AI engine */
                return sprintf(esc_html(__('Answer engine set to %s', 'js-support-ticket')), $jsst_subject);
            /* Which content the AI may answer from is a policy change in the
               same sense a lane is, so it is journalled beside them rather than
               in a log of its own. (Roadmap 6.0-AI-02) */
            case 'sources':
                /* translators: %s: a comma-separated list of approved content sources */
                return sprintf(esc_html(__('Approved sources set to %s', 'js-support-ticket')), $jsst_subject);
        }
        return esc_html(__('Setting changed', 'js-support-ticket'));
    }

}
