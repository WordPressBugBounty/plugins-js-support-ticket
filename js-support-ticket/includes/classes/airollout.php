<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The register of who automatic answers may go to. (Roadmap 6.0-AI-06)
 *
 * Three registers already stand between a customer's question and an automatic
 * reply, and each answers a different question:
 *
 *   JSSTaipolicy   may this help desk use a model at all, and down which lane
 *   JSSTaisources  what may it read when it does
 *   JSSTaireview   what may be sent, and what can be taken back afterwards
 *
 * This is the fourth, and it answers the one an administrator actually asks on
 * the day they switch automation on: *who does this happen to, how often, and
 * how do I stop it*. Departments and audiences are the segments; the reply cap
 * and the send delay are the rate; the pause switch and the steps in reversal()
 * are the way out. Rolling out to one department for a fortnight is the whole
 * adoption story for this feature, and it cannot be told by a master switch.
 *
 * Like the other three, this class can only ever refuse. eligible() returning
 * ok means nothing here has excluded that ticket; it does not mean an engine is
 * installed, configured, licensed, or that anything relevant will be found.
 *
 * ## Why these settings are core's, and stored as an option
 *
 * They were `aiagent_autopilot_*` rows in `js_ticket_config`, seeded and owned
 * by the AI Agent add-on - and that add-on's deactivation hook zeroes every one
 * of its own config rows. So a site that deactivated the add-on for ten minutes
 * lost its blocked-address list, its department restriction and its reply cap,
 * and got the shipped defaults back when it returned. A safety list that
 * evaporates when a plugin is toggled is worse than no safety list, because
 * nobody is told. They are also rules core enforces - JSSTaireview::record() is
 * what decides a send - so they are core's settings and they live in a core
 * option, with migrate() adopting whatever the site had configured, once.
 *
 * ## Two switches, on purpose
 *
 * `enabled` is the configuration: this site uses automatic answers. paused() is
 * the incident switch: stop now, change nothing. They are separate so that
 * stopping is one click that can be undone by one click - a person who switches
 * off `enabled` under pressure has to remember what the departments, cap and
 * delay were before they can put it back.
 *
 * Both stop the whole thing, generation included, rather than only the sending.
 * A send delay of half an hour is supported, so an answer written before a pause
 * can arrive at the decision after it - that one is held for a person rather
 * than dropped, and resuming does not release it, because a person is what
 * pausing asked for.
 */
class JSSTairollout {

    /** The rollout rules, as one array. */
    const OPT_RULES = 'jsst_ai_rollout';

    /**
     * The incident switch, deliberately its own option.
     *
     * Pausing has to be a single small write that cannot be lost inside a
     * settings form save, and has to be readable by the cron worker without
     * loading anything else. A key inside OPT_RULES would be both, one save of
     * an unrelated field away from being cleared.
     */
    const OPT_PAUSED = 'jsst_ai_autopilot_paused';

    /** Set once the add-on's config rows have been adopted. */
    const OPT_MIGRATED = 'jsst_ai_rollout_adopted';

    const AUDIENCE_GUEST = 'guest';
    const AUDIENCE_USER  = 'logged_in';

    /** What happens to an answer that may not be sent. */
    const FALLBACK_DRAFT = 'draft_reply';
    const FALLBACK_NONE  = 'nothing';

    /**
     * Shorter than this is not a question.
     *
     * A constant rather than a setting: it is not a policy anybody tunes, and
     * the benchmark has to be able to ask for the same number the live path
     * uses. It was buried in the engine as a literal 15.
     */
    const MIN_QUESTION = 15;

    /** Recent tickets sampled by coverage(). */
    const SAMPLE = 50;

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'migrate'), 4);
    }

    /* ------------------------------------------------------------------ *
     * The rules
     * ------------------------------------------------------------------ */

    /**
     * The shipped defaults.
     *
     * Automation is off and every audience is included: the first is because
     * nothing that writes to customers may arrive switched on, and the second
     * because a default that silently excluded guests would read as a broken
     * feature to the site that only has guests.
     */
    public static function defaults() {
        return array(
            'enabled'       => false,
            'threshold'     => 85,
            'maxreplies'    => 2,
            'delay'         => 0,
            'departments'   => array(),
            'audiences'     => array(self::AUDIENCE_GUEST, self::AUDIENCE_USER),
            'blocked'       => array(),
            'fallback'      => self::FALLBACK_DRAFT,
            'followups'     => true,
        );
    }

    /** The rules as configured, defaults filled in for anything missing. */
    public static function rules() {
        $jsst_saved = get_option(self::OPT_RULES, null);

        if (!is_array($jsst_saved)) {
            /* Adopt on first read rather than waiting for admin_init. The first
               thing to ask for these rules on an upgrading site is usually the
               cron worker answering a ticket, hours before anybody opens an
               admin page - and reading defaults there would silently switch a
               working rollout off until somebody logged in. */
            self::migrate();
            $jsst_saved = get_option(self::OPT_RULES, null);
            if (!is_array($jsst_saved)) {
                $jsst_saved = array();
            }
        }
        return self::clean(array_merge(self::defaults(), $jsst_saved));
    }

    /** One rule, by name. */
    public static function rule($jsst_key) {
        $jsst_rules = self::rules();
        return isset($jsst_rules[$jsst_key]) ? $jsst_rules[$jsst_key] : null;
    }

    /**
     * Write the rules, and journal what changed.
     *
     * Everything is validated here rather than at the form, because the CLI,
     * the setup wizard and a filter all reach this and only one of them is a
     * form. Anything unrecognised falls back to the default rather than being
     * stored: an unknown fallback mode or a delay that is not on the list would
     * otherwise sit in the option looking configured and behaving as neither.
     */
    public static function setRules($jsst_new) {
        if (!is_array($jsst_new)) return false;

        $jsst_before = self::rules();
        $jsst_rules  = self::clean(array_merge($jsst_before, $jsst_new));

        update_option(self::OPT_RULES, $jsst_rules, false);

        if (class_exists('JSSTaipolicy')) {
            if ($jsst_before['enabled'] !== $jsst_rules['enabled']) {
                JSSTaipolicy::record($jsst_rules['enabled'] ? 'autopilot-on' : 'autopilot-off', 'autopilot');
            }
            if ($jsst_before != $jsst_rules) {
                JSSTaipolicy::record('autopilot-rules', 'autopilot');
            }
        }
        return true;
    }

    /**
     * Force every rule into a shape the enforcement code can trust.
     *
     * Runs on read as well as on write. A site that had this option written by
     * an older release, a filter or a hand-edited row still gets a usable set
     * rather than a fatal somewhere under cron.
     */
    private static function clean($jsst_rules) {
        $jsst_out = self::defaults();

        $jsst_out['enabled']   = !empty($jsst_rules['enabled']);
        $jsst_out['followups'] = !empty($jsst_rules['followups']);

        /* A floor of zero would send everything, so it is not offered: the
           lowest meaningful setting is 1 and the highest is 100. */
        $jsst_out['threshold'] = isset($jsst_rules['threshold'])
            ? max(1, min(100, (int) $jsst_rules['threshold'])) : 85;

        /* Zero replies is a real choice - it means "propose, never send" - and
           is kept rather than normalised away. */
        $jsst_out['maxreplies'] = isset($jsst_rules['maxreplies'])
            ? max(0, min(20, (int) $jsst_rules['maxreplies'])) : 2;

        $jsst_delay = isset($jsst_rules['delay']) ? (int) $jsst_rules['delay'] : 0;
        $jsst_out['delay'] = array_key_exists($jsst_delay, self::delays()) ? $jsst_delay : 0;

        $jsst_out['departments'] = array();
        if (isset($jsst_rules['departments']) && is_array($jsst_rules['departments'])) {
            foreach ($jsst_rules['departments'] as $jsst_dept) {
                $jsst_dept = (int) $jsst_dept;
                if ($jsst_dept > 0 && !in_array($jsst_dept, $jsst_out['departments'], true)) {
                    $jsst_out['departments'][] = $jsst_dept;
                }
            }
        }

        $jsst_out['audiences'] = array();
        if (isset($jsst_rules['audiences']) && is_array($jsst_rules['audiences'])) {
            foreach ($jsst_rules['audiences'] as $jsst_who) {
                if (array_key_exists($jsst_who, self::audiences())
                    && !in_array($jsst_who, $jsst_out['audiences'], true)) {
                    $jsst_out['audiences'][] = $jsst_who;
                }
            }
        }

        $jsst_out['blocked'] = array();
        if (isset($jsst_rules['blocked'])) {
            foreach (self::split($jsst_rules['blocked']) as $jsst_entry) {
                if (!in_array($jsst_entry, $jsst_out['blocked'], true)) {
                    $jsst_out['blocked'][] = $jsst_entry;
                }
            }
        }

        $jsst_out['fallback'] = (isset($jsst_rules['fallback']) && $jsst_rules['fallback'] === self::FALLBACK_NONE)
            ? self::FALLBACK_NONE : self::FALLBACK_DRAFT;

        return $jsst_out;
    }

    /**
     * A list of addresses, however it was typed or however it arrived.
     *
     * Split on any whitespace as well as commas and semicolons, and that is not
     * tidiness: the field is a textarea, and by the time a value has been
     * through the request sanitiser its newlines are spaces - so splitting on
     * newlines alone turned "one address per line" into a single entry
     * containing all of them, which matched nothing and said nothing. No
     * address or domain contains whitespace, so there is nothing to lose.
     */
    private static function split($jsst_value) {
        if (!is_array($jsst_value)) {
            $jsst_value = preg_split('/[\s,;]+/', (string) $jsst_value);
        }
        $jsst_out = array();
        foreach ((array) $jsst_value as $jsst_item) {
            $jsst_item = jssupportticketphplib::JSST_strtolower(trim((string) $jsst_item));
            if ($jsst_item !== '') $jsst_out[] = $jsst_item;
        }
        return $jsst_out;
    }

    /* ------------------------------------------------------------------ *
     * Label maps, so the form and the enforcement cannot disagree
     * ------------------------------------------------------------------ */

    /** Minutes => label. The keys are the only delays that may be stored. */
    public static function delays() {
        return array(
            0  => esc_html(__('Straight away', 'js-support-ticket')),
            1  => esc_html(__('After 1 minute', 'js-support-ticket')),
            2  => esc_html(__('After 2 minutes', 'js-support-ticket')),
            5  => esc_html(__('After 5 minutes', 'js-support-ticket')),
            10 => esc_html(__('After 10 minutes', 'js-support-ticket')),
            15 => esc_html(__('After 15 minutes', 'js-support-ticket')),
            30 => esc_html(__('After 30 minutes', 'js-support-ticket')),
        );
    }

    public static function audiences() {
        return array(
            self::AUDIENCE_USER => array(
                'label' => esc_html(__('Signed-in customers', 'js-support-ticket')),
                'blurb' => esc_html(__('Tickets raised by somebody with an account on this site.', 'js-support-ticket')),
            ),
            self::AUDIENCE_GUEST => array(
                'label' => esc_html(__('Guests', 'js-support-ticket')),
                'blurb' => esc_html(__('Tickets raised without signing in, identified only by an e-mail address.', 'js-support-ticket')),
            ),
        );
    }

    public static function fallbacks() {
        return array(
            self::FALLBACK_DRAFT => array(
                'label' => esc_html(__('Leave a draft on the ticket', 'js-support-ticket')),
                'blurb' => esc_html(__('The agent opening the ticket finds the proposed answer already written, and sends it, edits it or deletes it. Recommended: it is the fastest path to a real answer.', 'js-support-ticket')),
            ),
            self::FALLBACK_NONE => array(
                'label' => esc_html(__('Nothing on the ticket', 'js-support-ticket')),
                'blurb' => esc_html(__('The proposal is kept on the Approvals screen only. Choose this where agents should not see a machine-written draft while they work.', 'js-support-ticket')),
            ),
        );
    }

    /* ------------------------------------------------------------------ *
     * The switches
     * ------------------------------------------------------------------ */

    /** Is automatic answering configured on? */
    public static function on() {
        return (bool) self::rule('enabled');
    }

    public static function paused() {
        return (bool) get_option(self::OPT_PAUSED, false);
    }

    /**
     * Stop, or start again.
     *
     * Journalled through the policy's own journal rather than a second one, so
     * the Audit screen tells one story: who switched what, in one list.
     */
    public static function pause($jsst_stop = true) {
        $jsst_stop = (bool) $jsst_stop;
        if (self::paused() === $jsst_stop) return true;

        update_option(self::OPT_PAUSED, $jsst_stop, false);
        if (class_exists('JSSTaipolicy')) {
            JSSTaipolicy::record($jsst_stop ? 'autopilot-paused' : 'autopilot-resumed', 'autopilot');
        }
        return true;
    }

    /**
     * Is anything actually going to be sent right now?
     *
     * All three have to be true, and they are asked in this order because that
     * is the order somebody debugging asks them: is AI on at all, is autopilot
     * configured on, is it paused.
     */
    public static function live() {
        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled()) return false;
        if (!self::on()) return false;
        return !self::paused();
    }

    /** Why not, in the state/reason/detail shape the other registers use. */
    public static function explain() {
        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled()) {
            return array(
                'state'  => 'off',
                'reason' => 'master',
                'detail' => esc_html(__('AI is switched off for this site, so nothing is answered automatically.', 'js-support-ticket')),
            );
        }
        if (!self::on()) {
            return array(
                'state'  => 'off',
                'reason' => 'off',
                'detail' => esc_html(__('Automatic answering is off, so no answers are written for new tickets. Agents can still ask for a draft inside a ticket.', 'js-support-ticket')),
            );
        }
        if (self::paused()) {
            return array(
                'state'  => 'paused',
                'reason' => 'paused',
                'detail' => esc_html(__('Paused. Nothing is written for new tickets and nothing is sent. Anything that was already in flight is held for approval.', 'js-support-ticket')),
            );
        }
        return array(
            'state'  => 'ok',
            'reason' => '',
            'detail' => esc_html(__('Answers clearing the confidence floor are sent to the audiences below.', 'js-support-ticket')),
        );
    }

    public static function threshold()  { return (int) self::rule('threshold'); }
    public static function maxReplies() { return (int) self::rule('maxreplies'); }
    public static function fallback()   { return (string) self::rule('fallback'); }
    public static function followups()  { return (bool) self::rule('followups'); }
    public static function delayMinutes() { return (int) self::rule('delay'); }

    /**
     * The delay in seconds, as the scheduler wants it.
     *
     * Zero minutes is five seconds rather than nought, and that is deliberate:
     * "straight away" has to mean out of the request, or a retrieval and a
     * hosted generation sit inside the POST that saves the ticket with the
     * customer watching a blank page. Five seconds out is indistinguishable
     * from immediate to them and returns the form at once.
     */
    public static function delaySeconds() {
        $jsst_minutes = self::delayMinutes();
        return ($jsst_minutes > 0) ? ($jsst_minutes * 60) : 5;
    }

    /* ------------------------------------------------------------------ *
     * The rules, applied
     * ------------------------------------------------------------------ */

    /**
     * Is this address on the blocked list?
     *
     * The old rule was a substring test over the whole address, which is wrong
     * in both directions: `co` blocked every .com customer on the site, and
     * `example.com` also blocked `example.com.attacker.net`. An entry that
     * looks like an address is matched exactly; one that looks like a domain
     * matches that domain and its subdomains; and a bare word with no dot and
     * no @ keeps the substring behaviour, because that is unambiguously what
     * somebody typing `spammer` meant, and quietly narrowing a safety list on
     * upgrade is the one direction this must never move in.
     */
    public static function blockedEmail($jsst_email) {
        $jsst_email = jssupportticketphplib::JSST_strtolower(trim((string) $jsst_email));
        if ($jsst_email === '') return false;

        $jsst_at     = strrpos($jsst_email, '@');
        $jsst_domain = ($jsst_at === false) ? '' : substr($jsst_email, $jsst_at + 1);

        foreach ((array) self::rule('blocked') as $jsst_entry) {
            if (strpos($jsst_entry, '@') === 0) {
                if (self::domainMatch($jsst_domain, substr($jsst_entry, 1))) return true;
            } elseif (strpos($jsst_entry, '@') !== false) {
                if ($jsst_entry === $jsst_email) return true;
            } elseif (strpos($jsst_entry, '.') !== false) {
                if (self::domainMatch($jsst_domain, $jsst_entry)) return true;
            } elseif (strpos($jsst_email, $jsst_entry) !== false) {
                return true;
            }
        }
        return false;
    }

    /** The domain itself, or anything under it. Never a bare substring. */
    private static function domainMatch($jsst_domain, $jsst_rule) {
        $jsst_rule = trim($jsst_rule, '.');
        if ($jsst_domain === '' || $jsst_rule === '') return false;
        if ($jsst_domain === $jsst_rule) return true;
        return (substr($jsst_domain, -(strlen($jsst_rule) + 1)) === '.' . $jsst_rule);
    }

    /** How each blocked entry will be read, for the screen to show. */
    public static function describeBlocked($jsst_entry) {
        $jsst_entry = trim((string) $jsst_entry);
        if (strpos($jsst_entry, '@') === 0) {
            return esc_html(__('this domain and its subdomains', 'js-support-ticket'));
        }
        if (strpos($jsst_entry, '@') !== false) {
            return esc_html(__('this address exactly', 'js-support-ticket'));
        }
        if (strpos($jsst_entry, '.') !== false) {
            return esc_html(__('this domain and its subdomains', 'js-support-ticket'));
        }
        return esc_html(__('any address containing this word', 'js-support-ticket'));
    }

    /**
     * How many answers have already gone out on this ticket?
     *
     * Counted from core's own answer record rather than the add-on's decision
     * log, and that is a deliberate change of meaning: an answer a person
     * approved was still an answer the customer received, and the cap exists to
     * stop a ticket filling with them. The old counter only saw the ones the
     * engine sent by itself, so an approval queue could put four more on top of
     * a cap of two without noticing.
     */
    public static function sentCount($jsst_ticketid) {
        if (!class_exists('JSSTaireview') || !JSSTaireview::available()) return 0;

        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(id) FROM `" . JSSTaireview::table() . "`
              WHERE ticketid = %d AND state = %s",
            (int) $jsst_ticketid, JSSTaireview::STATE_SENT));
    }

    /** Has this ticket had its allowance? */
    public static function capReached($jsst_ticketid) {
        return (self::sentCount($jsst_ticketid) >= self::maxReplies());
    }

    /**
     * May this ticket be answered automatically?
     *
     * The one call. It was five checks spread across three methods of the
     * add-on's engine - so a site had the rules only while a paid add-on was
     * installed, the benchmark could not ask what they were, and the log said
     * "safety rules triggered" whichever of them had fired. Every refusal now
     * names itself, which is what the coverage preview on the screen counts.
     *
     * The order is cheapest and most decisive first, and the database is only
     * touched by the last check.
     *
     * @param array $jsst_ticket ticketid, subject, message, email, uid, departmentid.
     * @return array state ok|off, reason, detail.
     */
    public static function eligible($jsst_ticket) {
        $jsst_state = self::explain();
        if ($jsst_state['state'] !== 'ok') {
            return array('state' => 'off', 'reason' => $jsst_state['reason'], 'detail' => $jsst_state['detail']);
        }
        return self::matchesRules($jsst_ticket);
    }

    /**
     * The rules on their own, with the switches left out.
     *
     * Split from eligible() so that the coverage preview can answer the
     * question somebody editing this screen is actually asking - "what would
     * these rules cover if I switched them on" - rather than the one the
     * switch answers. A site setting its departments up before going live would
     * otherwise read 0% covered for the single reason that it is not live yet,
     * which is exactly the information it already has.
     */
    public static function matchesRules($jsst_ticket) {
        if (!is_array($jsst_ticket)) $jsst_ticket = array();

        $jsst_rules   = self::rules();
        $jsst_subject = isset($jsst_ticket['subject']) ? (string) $jsst_ticket['subject'] : '';
        $jsst_message = isset($jsst_ticket['message']) ? wp_strip_all_tags((string) $jsst_ticket['message']) : '';

        /* The ticket's own uid, never the current session: under cron there is
           no session, and every ticket would read as a guest one. */
        $jsst_guest = empty($jsst_ticket['uid']);
        $jsst_who   = $jsst_guest ? self::AUDIENCE_GUEST : self::AUDIENCE_USER;

        if (!in_array($jsst_who, $jsst_rules['audiences'], true)) {
            $jsst_labels = self::audiences();
            return array('state' => 'off', 'reason' => 'audience', 'detail' => sprintf(
                /* translators: %s: an audience name, for example "Guests" */
                esc_html(__('%s are not included in this rollout.', 'js-support-ticket')),
                $jsst_labels[$jsst_who]['label']));
        }

        if (!empty($jsst_rules['departments'])) {
            $jsst_dept = isset($jsst_ticket['departmentid']) ? (int) $jsst_ticket['departmentid'] : 0;
            if (!in_array($jsst_dept, $jsst_rules['departments'], true)) {
                return array('state' => 'off', 'reason' => 'department',
                             'detail' => esc_html(__('This department is not included in the rollout.', 'js-support-ticket')));
            }
        }

        if (self::blockedEmail(isset($jsst_ticket['email']) ? $jsst_ticket['email'] : '')) {
            return array('state' => 'off', 'reason' => 'blocked',
                         'detail' => esc_html(__('This customer is on the never-answer list.', 'js-support-ticket')));
        }

        if (jssupportticketphplib::JSST_strlen(trim($jsst_message)) < self::MIN_QUESTION) {
            return array('state' => 'off', 'reason' => 'short',
                         'detail' => esc_html(__('Too short to contain a question.', 'js-support-ticket')));
        }

        /* The subject as well as the message: "Refund" over a polite paragraph
           is still a refund. (Roadmap 6.0-AI-05) */
        if (class_exists('JSSTaireview') && JSSTaireview::neverAutomate($jsst_subject . ' ' . $jsst_message)) {
            return array('state' => 'off', 'reason' => 'never',
                         'detail' => esc_html(__('This kind of question is never answered automatically.', 'js-support-ticket')));
        }

        $jsst_ticketid = isset($jsst_ticket['ticketid']) ? (int) $jsst_ticket['ticketid'] : 0;
        if ($jsst_ticketid > 0 && self::capReached($jsst_ticketid)) {
            return array('state' => 'off', 'reason' => 'cap', 'detail' => sprintf(
                /* translators: %d: the configured maximum number of automatic replies */
                esc_html(_n('This ticket has already had its %d automatic answer.',
                            'This ticket has already had its %d automatic answers.',
                            self::maxReplies(), 'js-support-ticket')),
                self::maxReplies()));
        }

        return array('state' => 'ok', 'reason' => '',
                     'detail' => esc_html(__('Nothing in the rollout rules excludes this ticket.', 'js-support-ticket')));
    }

    /* ------------------------------------------------------------------ *
     * What the rules actually cover
     * ------------------------------------------------------------------ */

    /**
     * Run the rules over the tickets this site really receives.
     *
     * A settings screen showing four switches tells an administrator what they
     * chose; it does not tell them what they chose *means*, and the gap between
     * the two is where a rollout to "one department" turns out to be ninety per
     * cent of the desk. So the screen counts: of the last fifty tickets, how
     * many these rules would have let through, and which rule stopped the rest.
     *
     * The reply cap is deliberately left out of the sample - it is a property
     * of a ticket's history rather than of the rules being edited, and counting
     * it would report a rollout as narrower than it is for a reason that has
     * nothing to do with the settings on the screen.
     */
    public static function coverage($jsst_limit = self::SAMPLE) {
        $jsst_out = array('sampled' => 0, 'eligible' => 0, 'reasons' => array());

        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_tickets';
        if (jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return $jsst_out;
        }

        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, subject, message, email, uid, departmentid
               FROM `" . $jsst_table . "` ORDER BY id DESC LIMIT %d", max(1, (int) $jsst_limit)));

        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out['sampled']++;
            $jsst_verdict = self::matchesRules(array(
                'ticketid'     => 0, // the cap is history, not a rule being edited
                'subject'      => $jsst_row->subject,
                'message'      => $jsst_row->message,
                'email'        => $jsst_row->email,
                'uid'          => $jsst_row->uid,
                'departmentid' => $jsst_row->departmentid,
            ));

            if ($jsst_verdict['state'] === 'ok') {
                $jsst_out['eligible']++;
                continue;
            }
            $jsst_key = $jsst_verdict['reason'];
            if (!isset($jsst_out['reasons'][$jsst_key])) {
                $jsst_out['reasons'][$jsst_key] = array('count' => 0, 'detail' => $jsst_verdict['detail']);
            }
            $jsst_out['reasons'][$jsst_key]['count']++;
        }

        /* Sorted on the count explicitly rather than with arsort(), which on
           an array of arrays compares them element by element and happens to
           be right only because 'count' is the first key. */
        uasort($jsst_out['reasons'], array(__CLASS__, 'byCount'));
        return $jsst_out;
    }

    /** Most frequent refusal first. */
    private static function byCount($jsst_a, $jsst_b) {
        return ((int) $jsst_b['count'] - (int) $jsst_a['count']);
    }

    /* ------------------------------------------------------------------ *
     * The way out
     * ------------------------------------------------------------------ */

    /**
     * The reversal path, as data.
     *
     * The roadmap asks for a *documented* reversal path, and a document that
     * lives somewhere else is a document that stops matching the product. This
     * is the documentation: the screen renders it, and a test asserts that
     * every step it names is a thing this release can actually do. Ordered from
     * the fastest to the most thorough, because the order somebody needs them
     * in is the order the trouble escalates.
     *
     * `undone` is the honest half - what each step does not fix - and the last
     * line of it is the one that matters: an e-mail that has been read cannot
     * be unsent, and no switch on this screen pretends otherwise.
     */
    public static function reversal() {
        return array(
            array(
                'key'    => 'pause',
                'title'  => esc_html(__('Pause automatic answers', 'js-support-ticket')),
                'detail' => esc_html(__('One click, on this screen. Nothing is written and nothing is sent, and anything already in flight is held for a person instead. Your departments, audiences and limits are untouched, so resuming restores exactly what was running.', 'js-support-ticket')),
                'undone' => esc_html(__('Answers already sent are not affected.', 'js-support-ticket')),
            ),
            array(
                'key'    => 'approve',
                'title'  => esc_html(__('Require a person to approve every answer', 'js-support-ticket')),
                'detail' => esc_html(__('On the Approvals screen, set this site to ask a person first. Unlike pausing, automation keeps working — answers are still written, they just stop at a human. This is the setting to leave in place while you investigate, and the one to run for a fortnight before going live.', 'js-support-ticket')),
                'undone' => esc_html(__('Answers already sent are not affected.', 'js-support-ticket')),
            ),
            array(
                'key'    => 'retract',
                'title'  => esc_html(__('Withdraw an answer that went out', 'js-support-ticket')),
                'detail' => esc_html(__('Every automatic answer is listed on the Approvals screen with a Withdraw button. The reply is struck through on the ticket, the ticket is reopened if our answer closed it, and the record of what was sent is kept.', 'js-support-ticket')),
                'undone' => esc_html(__('The customer already has the e-mail. Withdrawing marks the record; it does not unsend anything, so a correction is usually worth sending too.', 'js-support-ticket')),
            ),
            array(
                'key'    => 'master',
                'title'  => esc_html(__('Switch AI off for the whole site', 'js-support-ticket')),
                'detail' => esc_html(__('The master switch on AI Agent Settings. Nothing in this plugin talks to a model, on any lane, until it is switched back on — including agent-facing drafts and suggestions.', 'js-support-ticket')),
                'undone' => esc_html(__('Answers already sent are not affected.', 'js-support-ticket')),
            ),
        );
    }

    /* ------------------------------------------------------------------ *
     * Adoption
     * ------------------------------------------------------------------ */

    /**
     * Take over the settings the add-on used to own.
     *
     * Once, guarded by its own marker, and the guard matters more than usual
     * here: the add-on's activation re-seeds its config rows with the shipped
     * defaults, so a second run after a reactivation would overwrite a site's
     * real rollout with defaults - the exact loss this move exists to stop.
     *
     * The legacy rows are left where they are rather than deleted. Nothing
     * reads them any more, they are re-created by the add-on's activation
     * whatever we do, and a row that turns out to have been somebody's only
     * record of a blocked-address list is not something to delete on an upgrade
     * nobody asked for.
     */
    public static function migrate() {
        if (get_option(self::OPT_MIGRATED)) return false;

        $jsst_config = jssupportticket::$_config;
        $jsst_new    = array();

        if (isset($jsst_config['aiagent_autopilot_enable'])) {
            $jsst_new['enabled'] = ((int) $jsst_config['aiagent_autopilot_enable'] === 1);
        }
        if (isset($jsst_config['aiagent_autopilot_min_confidence'])) {
            $jsst_new['threshold'] = (int) $jsst_config['aiagent_autopilot_min_confidence'];
        }
        if (isset($jsst_config['aiagent_autopilot_max_replies'])) {
            $jsst_new['maxreplies'] = (int) $jsst_config['aiagent_autopilot_max_replies'];
        }
        if (isset($jsst_config['aiagent_autopilot_delay'])) {
            $jsst_new['delay'] = (int) $jsst_config['aiagent_autopilot_delay'];
        }
        if (isset($jsst_config['aiagent_autopilot_fallback'])) {
            $jsst_new['fallback'] = (string) $jsst_config['aiagent_autopilot_fallback'];
        }
        if (isset($jsst_config['aiagent_autopilot_blacklist_emails'])) {
            $jsst_new['blocked'] = (string) $jsst_config['aiagent_autopilot_blacklist_emails'];
        }

        $jsst_depts = isset($jsst_config['aiagent_autopilot_target_departments'])
            ? json_decode((string) $jsst_config['aiagent_autopilot_target_departments'], true) : null;
        if (is_array($jsst_depts)) {
            $jsst_new['departments'] = $jsst_depts;
        }

        /* An empty audience list is not "nothing configured": it is a site that
           excluded everybody, and reading it as the default would switch the
           rollout back on for every customer they had removed. Only a value
           that is not a list at all falls through to the default. */
        $jsst_audience = isset($jsst_config['aiagent_autopilot_target_users'])
            ? json_decode((string) $jsst_config['aiagent_autopilot_target_users'], true) : null;
        if (is_array($jsst_audience)) {
            $jsst_new['audiences'] = $jsst_audience;
        }

        /* Written even when there was nothing to adopt, so that a site which
           never had the add-on stops re-reading the config table for the rest
           of its life. */
        update_option(self::OPT_RULES, self::clean(array_merge(self::defaults(), $jsst_new)), false);
        update_option(self::OPT_MIGRATED, 1, false);
        return true;
    }
}
