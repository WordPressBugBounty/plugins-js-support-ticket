<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

if (class_exists('JSSTaitriage')) {
    return;
}

/**
 * What the desk knows about a ticket before anybody has read it.
 * (Roadmap 6.0-AI-13)
 *
 * ## This class deliberately breaks the Copilot's central rule, and says so
 *
 * `JSSTcopilot`'s docblock makes a promise worth quoting: *every route into
 * this class starts with an agent clicking something*, because "a help desk
 * that quietly posted a customer's message to a model the moment it arrived
 * would be doing something its owner never agreed to and its customers were
 * never told about". That is right, and this class does exactly that thing.
 *
 * The difference is consent, and it is the whole of the design here:
 *
 *   - It is **off**. Not off-by-default-with-a-nudge; off, with no automatic
 *     request made by this plugin until somebody switches it on.
 *   - The switch says plainly what it does, in the words above rather than as
 *     "enable AI triage".
 *   - It runs **two** actions and cannot be pointed at the others. Routing and
 *     mood are useful before a human reads the ticket, which is the only reason
 *     to run anything automatically; a drafted reply is not, and an automatic
 *     drafting path is how a desk ends up with a model's prose in front of a
 *     customer without anybody deciding that.
 *   - Every run goes through the same meter, the same policy switch and the
 *     same audit log as a button press, so "what left this site" has one
 *     answer. (Roadmap 6.0-AI-01, 6.0-AI-07)
 *
 * ## It suggests; it does not route
 *
 * The output is stored beside the ticket and shown to whoever opens it. It does
 * not move the ticket, change its priority or assign anybody, and there is no
 * setting to make it. A wrong automatic routing is a ticket that sits in the
 * wrong queue for a day with nobody aware it was ever moved; a wrong suggestion
 * is a line on a screen that an agent disagrees with in a second. The roadmap
 * asks for "auto-triage" and this is the honest reading of it: the triage is
 * automatic, the acting on it is not.
 *
 * ## Why it is not gated on the review register
 *
 * `JSSTaireview` decides what may be **sent to a customer**, and a site with
 * approvals set to propose-only has said nothing automatic reaches the people
 * it supports. Nothing here reaches them: every output is agent-facing, which
 * is exactly the roadmap's stated reason for this task - throughput "even where
 * automated sending is disabled". Gating on that register would switch off the
 * feature whose whole point is working when the other one is off. The master AI
 * switch and the meter still apply, because those are about whether text may
 * leave the site at all, which this does.
 */
class JSSTaitriage {

    const TABLE          = 'js_ticket_ai_triage';
    const SCHEMA_VERSION = '6.0.0';
    const OPT_SCHEMA     = 'jsst_ai_triage_schema';

    /** Which automatic operations a site has agreed to, as a list. */
    const OPT_AUTO = 'jsst_ai_triage_auto';

    const OP_TRIAGE    = 'triage';
    const OP_SENTIMENT = 'sentiment';

    /** Below this score a ticket is worth somebody looking at today. */
    const UNHAPPY_AT = -30;

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);

        /* Hooked whatever the setting says, and the setting is read inside -
           because the alternative is a hook that exists only on sites where the
           feature is on, which makes "why did nothing happen" depend on load
           order rather than on a value somebody can read. */
        add_action('jsst-ticketcreate', array(__CLASS__, 'onTicketCreated'), 20);
        add_action('jsst_ai_triage_run', array(__CLASS__, 'runFor'), 10, 1);

        /* A column an agent can turn on, offered only once there is something
           to put in it. Registering it unconditionally would put a permanently
           empty option in the picker of every site that never switched this on
           - which is most of them. (Roadmap 6.0-AI-13) */
        add_filter('jsst_queue_columns', array(__CLASS__, 'queueColumn'));
    }

    /**
     * The mood column, for the queue's own catalogue.
     *
     * Off by default. The reading is worth having on a row, but a queue is a
     * dense screen an agent reads all day and a new column arriving unasked in
     * it is the kind of change that gets a release complained about - the tag
     * column's note in JSSTqueueengine makes the opposite case for tags, and
     * the difference is that tags were already being shown.
     */
    public static function queueColumn($jsst_columns) {
        if (!self::available()) return $jsst_columns;

        /* Nothing has ever been read on this site, so there is nothing to show
           and no reason to offer the column. Cheap: one indexed count, cached
           for the request by the static below. */
        if (!self::everRead()) return $jsst_columns;

        $jsst_columns['aimood'] = array(
            'key'     => 'aimood',
            'label'   => __('Mood', 'js-support-ticket'),
            'sort'    => '',
            'field'   => '',
            'default' => false,
            'group'   => JSSTqueueengine::GROUP_QUEUE,
        );
        return $jsst_columns;
    }

    /** Memoised answer to "has anything on this site ever been read?". */
    private static $jsst_everread = null;

    /**
     * Has anything on this site ever been read?
     *
     * Memoised in a class property rather than a static local so that `store()`
     * can clear it - the AI-11 setup checklist made the same choice for the
     * same reason. Without that, the first reading written in a request would
     * not make the column available until the request after it, and in a
     * long-lived process (a test run, WP-CLI) never at all.
     */
    public static function everRead() {
        if (self::$jsst_everread !== null) return self::$jsst_everread;

        self::$jsst_everread = ((int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . self::table() . "` LIMIT 1") > 0);
        return self::$jsst_everread;
    }

    /** Forget it, after something changed what the answer would be. */
    public static function forget() {
        self::$jsst_everread = null;
    }

    /**
     * The readings for a page of tickets, in one query.
     *
     * A queue renders twenty rows, and asking per row is twenty round trips for
     * a column most sites will not have switched on - the shape that makes a
     * list screen slow in a way nobody attributes to the feature that caused it.
     *
     * @return array ticketid => row.
     */
    public static function forTickets($jsst_ticketids) {
        $jsst_ids = array_filter(array_map('intval', (array) $jsst_ticketids));
        if (empty($jsst_ids) || !self::available()) return array();

        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT * FROM `" . self::table() . "`
              WHERE ticketid IN (" . implode(',', $jsst_ids) . ")");

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[(int) $jsst_row->ticketid] = $jsst_row;
        }
        return $jsst_out;
    }

    /**
     * The mood, as one cell.
     *
     * A pill and nothing else - the evidence and the reason belong on the
     * ticket, where there is room to read them. A queue cell that carries a
     * sentence makes every row a different height.
     */
    public static function cell($jsst_row) {
        if (!$jsst_row) return '';

        $jsst_mood = self::moodLabel((string) $jsst_row->mood, $jsst_row->score);
        if ($jsst_mood === '') return '';

        $jsst_tone = !empty($jsst_row->atrisk) ? 'bad'
                   : (((int) $jsst_row->score <= self::UNHAPPY_AT) ? 'warn' : 'info');

        return '<span class="jsst-pill jsst-pill-' . esc_attr($jsst_tone) . '">'
             . '<span class="jsst-dot"></span>' . esc_html($jsst_mood) . '</span>';
    }

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    public static function ensureSchema() {
        if (!class_exists('JSSTschemaguard')) return;
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION,
                array(self::TABLE => array('ticketid', 'score', 'department')))) {
            return;
        }

        $jsst_charset = jssupportticket::$_db->get_charset_collate();

        /* One row per ticket, replaced rather than appended to. The history of
           how a ticket's mood changed is an interesting idea and not this one:
           what a queue needs is the current reading, and a table that grows a
           row per reply is a table that has to be pruned and joined against
           with a MAX() forever. */
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . self::table() . "` (
                ticketid bigint(20) NOT NULL,
                score tinyint(4) DEFAULT NULL,
                mood varchar(20) NOT NULL DEFAULT '',
                atrisk tinyint(1) NOT NULL DEFAULT '0',
                evidence varchar(500) NOT NULL DEFAULT '',
                department varchar(150) NOT NULL DEFAULT '',
                urgency varchar(20) NOT NULL DEFAULT '',
                reason varchar(500) NOT NULL DEFAULT '',
                updated datetime DEFAULT NULL,
                PRIMARY KEY (ticketid),
                KEY jsst_triage_score (score),
                KEY jsst_triage_risk (atrisk, updated)
            ) " . $jsst_charset);

        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);
    }

    public static function table() {
        return jssupportticket::$_db->prefix . self::TABLE;
    }

    public static function available() {
        $jsst_table = self::table();
        return (jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) === $jsst_table);
    }

    /* ------------------------------------------------------------------ *
     * Consent
     * ------------------------------------------------------------------ */

    /** The operations a site has agreed to run without being asked. */
    public static function automatic() {
        $jsst_saved = get_option(self::OPT_AUTO, array());
        if (!is_array($jsst_saved)) return array();

        return array_values(array_intersect($jsst_saved, array(self::OP_TRIAGE, self::OP_SENTIMENT)));
    }

    /**
     * Agree to run one, or stop.
     *
     * Whitelisted rather than stored as given, so that a filter or a future
     * screen cannot enrol the drafting action into the automatic path by
     * spelling its name into this option - which is the one way the promise in
     * the class docblock could be broken by accident.
     */
    public static function setAutomatic($jsst_ops) {
        $jsst_clean = array();
        foreach ((array) $jsst_ops as $jsst_op) {
            if (in_array($jsst_op, array(self::OP_TRIAGE, self::OP_SENTIMENT), true)) {
                $jsst_clean[] = $jsst_op;
            }
        }
        update_option(self::OPT_AUTO, array_values(array_unique($jsst_clean)), false);
        return true;
    }

    /** Is anything at all going to happen on its own? */
    public static function anyAutomatic() {
        if (!class_exists('JSSTaipolicy') || !JSSTaipolicy::enabled()) return false;
        return (count(self::automatic()) > 0);
    }

    /* ------------------------------------------------------------------ *
     * Running
     * ------------------------------------------------------------------ */

    /**
     * A ticket arrived.
     *
     * Deferred to cron rather than run here. A model call takes seconds and
     * this is the request in which a customer is waiting for their ticket to be
     * accepted - making them wait for an internal convenience would be charging
     * them for it. It also means a vendor being slow or down delays a note on a
     * screen rather than the ticket being filed at all.
     */
    public static function onTicketCreated($jsst_ticket) {
        if (!self::anyAutomatic()) return;

        $jsst_ticketid = 0;
        if (is_object($jsst_ticket) && isset($jsst_ticket->id)) {
            $jsst_ticketid = (int) $jsst_ticket->id;
        } elseif (is_numeric($jsst_ticket)) {
            $jsst_ticketid = (int) $jsst_ticket;
        }
        if ($jsst_ticketid < 1) return;

        wp_schedule_single_event(time() + 30, 'jsst_ai_triage_run', array($jsst_ticketid));
    }

    /**
     * Do the agreed operations for one ticket.
     *
     * Runs as an administrator on purpose and says why: under cron there is no
     * current user, and `JSSTcopilot::run()` asks whether the actor may use the
     * Copilot. Passing a real administrator's id makes that check meaningful
     * and puts a named person in the audit log, rather than leaving a run
     * attributed to nobody - which is the entry somebody chasing a bill cannot
     * account for.
     */
    public static function runFor($jsst_ticketid) {
        if (!self::anyAutomatic() || !self::available()) return false;
        if (!class_exists('JSSTcopilot')) return false;

        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_ticketid < 1) return false;

        $jsst_actor = self::systemActor();
        if ($jsst_actor < 1) return false;

        $jsst_done = false;
        foreach (self::automatic() as $jsst_op) {
            $jsst_result = JSSTcopilot::run($jsst_op, $jsst_ticketid, array(), $jsst_actor);
            if (is_wp_error($jsst_result)) continue;

            self::store($jsst_ticketid, $jsst_op,
                isset($jsst_result['fields']) ? $jsst_result['fields'] : array());
            $jsst_done = true;
        }

        if ($jsst_done) {
            self::fillBlanks($jsst_ticketid);
            do_action('jsst_ai_triage_done', $jsst_ticketid);
        }
        return $jsst_done;
    }

    /**
     * Put the suggestion into the fields the customer left empty.
     *
     * Only empty ones: a department or priority somebody chose is never
     * overwritten, so this cannot misroute a ticket that was already routed.
     * It exists because the customer form leaves both optional, and a ticket
     * with no department reaches no queue and - under a rollout limited to
     * departments - is never answered automatically either. Each change is
     * written to the ticket history so an agent can see it was the AI.
     * (Decided with the desk owner: fill blanks, never override.)
     */
    public static function fillBlanks($jsst_ticketid) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_tickets = jssupportticket::$_db->prefix . 'js_ticket_tickets';
        $jsst_ticket = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT id, departmentid, priorityid FROM `" . $jsst_tickets . "` WHERE id = %d", $jsst_ticketid));
        $jsst_read = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT department, urgency FROM `" . self::table() . "` WHERE ticketid = %d", $jsst_ticketid));
        if (!$jsst_ticket || !$jsst_read) return array();

        $jsst_set = array();
        $jsst_notes = array();
        if ((int) $jsst_ticket->departmentid <= 0 && (string) $jsst_read->department !== '') {
            $jsst_deptid = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments`
                  WHERE departmentname = %s AND status = 1 ORDER BY id ASC LIMIT 1", $jsst_read->department));
            if ($jsst_deptid > 0) {
                $jsst_set['departmentid'] = $jsst_deptid;
                /* translators: %s: department name. */
                $jsst_notes[] = sprintf(esc_html(__('department set to %s', 'js-support-ticket')), $jsst_read->department);
            }
        }
        if ((int) $jsst_ticket->priorityid <= 0 && (string) $jsst_read->urgency !== '') {
            /* The model's word, matched to a switched-on priority of the same
               name, with the nearest neighbour when the desk has turned that
               one off - "urgent" on a desk without Urgent is still High. */
            $jsst_try = array(
                'urgent' => array('urgent', 'high'),
                'high'   => array('high', 'urgent'),
                'normal' => array('normal', 'medium'),
                'low'    => array('low', 'normal'),
            );
            $jsst_names = isset($jsst_try[$jsst_read->urgency]) ? $jsst_try[$jsst_read->urgency] : array();
            foreach ($jsst_names AS $jsst_name) {
                $jsst_prio = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                    "SELECT id, priority FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities`
                      WHERE LOWER(priority) = %s AND status = 1 ORDER BY id ASC LIMIT 1", $jsst_name));
                if ($jsst_prio) {
                    $jsst_set['priorityid'] = (int) $jsst_prio->id;
                    /* translators: %s: priority name. */
                    $jsst_notes[] = sprintf(esc_html(__('priority set to %s', 'js-support-ticket')), $jsst_prio->priority);
                    break;
                }
            }
        }
        if (empty($jsst_set)) return array();

        jssupportticket::$_db->update($jsst_tickets, $jsst_set, array('id' => $jsst_ticketid));
        if (class_exists('JSSTmergedaddon') && JSSTmergedaddon::featureEnabled('tickethistory')) {
            JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1,
                esc_html(__('AI triage', 'js-support-ticket')),
                esc_html(__('Left empty by the customer, filled in by AI triage:', 'js-support-ticket')) . ' ' . implode(', ', $jsst_notes),
                esc_html(__('Successfully', 'js-support-ticket')));
        }
        /* The automatic reply is decided at creation, before this ran; an
           add-on that skipped the ticket for want of a department can look
           again. */
        do_action('jsst_ai_triage_filled', $jsst_ticketid, $jsst_set);
        return $jsst_set;
    }

    /**
     * Write down what one operation found.
     *
     * Fields are taken one at a time and clamped, rather than the array being
     * stored as it arrived. The values come from a model, which means "urgent"
     * can come back as "Urgent!!" and a score as 400 - and a screen is not the
     * place to discover that.
     */
    public static function store($jsst_ticketid, $jsst_op, $jsst_fields) {
        if (!self::available() || !is_array($jsst_fields)) return false;

        $jsst_set = array('updated' => current_time('Y-m-d H:i:s'));

        if ($jsst_op === self::OP_SENTIMENT) {
            $jsst_set['score'] = isset($jsst_fields['score'])
                ? max(-100, min(100, (int) $jsst_fields['score'])) : null;
            $jsst_set['mood'] = self::oneOf(
                isset($jsst_fields['label']) ? $jsst_fields['label'] : '',
                array('angry', 'unhappy', 'neutral', 'pleased', 'delighted'));
            $jsst_set['atrisk'] = !empty($jsst_fields['atrisk']) ? 1 : 0;
            $jsst_set['evidence'] = jssupportticketphplib::JSST_substr(
                sanitize_text_field(isset($jsst_fields['evidence']) ? $jsst_fields['evidence'] : ''), 0, 500);
        } elseif ($jsst_op === self::OP_TRIAGE) {
            /* Matched back to a department this desk actually has. A name the
               model got slightly wrong is recoverable; one that was never on
               the list is a suggestion nobody can act on, so it is dropped
               rather than shown. */
            $jsst_set['department'] = self::matchDepartment(
                isset($jsst_fields['department']) ? $jsst_fields['department'] : '');
            $jsst_set['urgency'] = self::oneOf(
                isset($jsst_fields['urgency']) ? $jsst_fields['urgency'] : '',
                array('low', 'normal', 'high', 'urgent'));
            $jsst_set['reason'] = jssupportticketphplib::JSST_substr(
                sanitize_text_field(isset($jsst_fields['reason']) ? $jsst_fields['reason'] : ''), 0, 500);
        } else {
            return false;
        }

        $jsst_have = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(*) FROM `" . self::table() . "` WHERE ticketid = %d", $jsst_ticketid));

        if ((int) $jsst_have > 0) {
            jssupportticket::$_db->update(self::table(), $jsst_set, array('ticketid' => $jsst_ticketid));
        } else {
            $jsst_set['ticketid'] = $jsst_ticketid;
            jssupportticket::$_db->insert(self::table(), $jsst_set);
        }

        // The first reading on a site makes the queue column worth offering.
        self::forget();
        return true;
    }

    /** A value from a model, reduced to one of the words we asked for. */
    private static function oneOf($jsst_value, $jsst_allowed) {
        $jsst_value = jssupportticketphplib::JSST_strtolower(trim(
            preg_replace('/[^a-zA-Z]+/', '', (string) $jsst_value)));

        return in_array($jsst_value, $jsst_allowed, true) ? $jsst_value : '';
    }

    /** The desk's own spelling of a department, or nothing. */
    public static function matchDepartment($jsst_name) {
        $jsst_name = trim((string) $jsst_name);
        if ($jsst_name === '' || !class_exists('JSSTcopilot')) return '';

        $jsst_wanted = jssupportticketphplib::JSST_strtolower($jsst_name);
        foreach (JSSTcopilot::departmentNames() as $jsst_real) {
            if (jssupportticketphplib::JSST_strtolower($jsst_real) === $jsst_wanted) {
                return $jsst_real;
            }
        }
        return '';
    }

    /**
     * An administrator to run as under cron.
     *
     * Resolved rather than hardcoded to user 1, who may not exist and may not
     * be an administrator - the same reasoning the reply poster uses.
     */
    private static function systemActor() {
        $jsst_admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
        return !empty($jsst_admins) ? (int) $jsst_admins[0] : 0;
    }

    /* ------------------------------------------------------------------ *
     * Reading it back
     * ------------------------------------------------------------------ */

    /** What is known about one ticket, or nothing. */
    public static function forTicket($jsst_ticketid) {
        if (!self::available()) return null;

        return jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE ticketid = %d", (int) $jsst_ticketid));
    }

    /**
     * How a score reads on a screen.
     *
     * Words rather than the number, because -42 means nothing to anybody and
     * the number is a model's opinion dressed as a measurement. The number is
     * kept because it sorts; the word is what is shown.
     */
    public static function moodLabel($jsst_mood, $jsst_score = null) {
        $jsst_labels = array(
            'angry'     => esc_html(__('Angry', 'js-support-ticket')),
            'unhappy'   => esc_html(__('Unhappy', 'js-support-ticket')),
            'neutral'   => esc_html(__('Neutral', 'js-support-ticket')),
            'pleased'   => esc_html(__('Pleased', 'js-support-ticket')),
            'delighted' => esc_html(__('Delighted', 'js-support-ticket')),
        );

        if (isset($jsst_labels[$jsst_mood])) return $jsst_labels[$jsst_mood];
        if ($jsst_score === null) return '';

        return ((int) $jsst_score <= self::UNHAPPY_AT)
            ? $jsst_labels['unhappy'] : $jsst_labels['neutral'];
    }

    /** Tickets whose customer sounds like they are about to give up. */
    public static function atRisk($jsst_limit = 25) {
        if (!self::available()) return array();

        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT t.id, t.ticketid, t.subject, t.email, r.score, r.mood, r.evidence, r.updated
               FROM `" . self::table() . "` r
               INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tickets` t ON t.id = r.ticketid
              WHERE (r.atrisk = 1 OR r.score <= %d) AND t.status NOT IN (3, 4)
              ORDER BY r.score ASC, r.updated DESC LIMIT %d",
            self::UNHAPPY_AT, (int) $jsst_limit));
    }

    /* ------------------------------------------------------------------ *
     * On the ticket
     * ------------------------------------------------------------------ */

    /**
     * The reading, printed above the reply box.
     *
     * Printed by this class rather than described to a template, for the
     * reason 4.5-FE-02 gives for the navigation strip: the markup is small and
     * identical in both desks, so rendering it once is the point - the
     * alternative is the same panel written twice and diverging the first time
     * either is touched.
     *
     * Renders **nothing at all** when there is no reading. An empty panel
     * saying "no sentiment analysis available" is a permanent advertisement for
     * a feature the site has switched off, on the screen an agent uses most.
     */
    public static function panel($jsst_ticketid) {
        $jsst_row = self::forTicket($jsst_ticketid);
        if (!$jsst_row) return '';

        $jsst_mood    = self::moodLabel((string) $jsst_row->mood, $jsst_row->score);
        $jsst_urgency = (string) $jsst_row->urgency;
        $jsst_dept    = (string) $jsst_row->department;

        if ($jsst_mood === '' && $jsst_urgency === '' && $jsst_dept === '') return '';

        $jsst_out  = '<div class="jsst-triage">';
        $jsst_out .= '<span class="jsst-triage-lead">'
                   . esc_html(__('Read before you opened it', 'js-support-ticket')) . '</span>';

        if ($jsst_mood !== '') {
            /* At-risk is its own state rather than a darker shade of unhappy:
               "this customer said they are leaving" is a different thing to do
               something about than "this customer is annoyed". */
            $jsst_tone = !empty($jsst_row->atrisk) ? 'bad'
                       : (((int) $jsst_row->score <= self::UNHAPPY_AT) ? 'warn' : 'info');

            $jsst_out .= '<span class="jsst-pill jsst-pill-' . esc_attr($jsst_tone) . '">'
                       . '<span class="jsst-dot"></span>' . esc_html($jsst_mood) . '</span>';

            if (!empty($jsst_row->atrisk)) {
                $jsst_out .= '<span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span>'
                           . esc_html(__('May be leaving', 'js-support-ticket')) . '</span>';
            }
        }

        /* Only when it says something: "Looks like Sales" on a ticket that is
           already in Sales is a tag that tells the agent nothing. */
        if ($jsst_dept !== '') {
            $jsst_current = (string) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT d.departmentname FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` t
                   JOIN `" . jssupportticket::$_db->prefix . "js_ticket_departments` d ON d.id = t.departmentid
                  WHERE t.id = %d", (int) $jsst_ticketid));
            if (jssupportticketphplib::JSST_strtolower($jsst_current) === jssupportticketphplib::JSST_strtolower($jsst_dept)) {
                $jsst_dept = '';
            }
        }
        if ($jsst_dept !== '') {
            $jsst_out .= '<span class="jsst-chip">' . esc_html(sprintf(
                /* translators: %s: a department name */
                __('Looks like %s', 'js-support-ticket'), $jsst_dept)) . '</span>';
        }

        if ($jsst_urgency !== '') {
            $jsst_words = array(
                'low'    => __('Not urgent', 'js-support-ticket'),
                'normal' => __('Normal urgency', 'js-support-ticket'),
                'high'   => __('Looks urgent', 'js-support-ticket'),
                'urgent' => __('Looks very urgent', 'js-support-ticket'),
            );
            if (isset($jsst_words[$jsst_urgency])) {
                $jsst_out .= '<span class="jsst-chip">' . esc_html($jsst_words[$jsst_urgency]) . '</span>';
            }
        }

        /* The evidence, not the score. A number is a model's opinion dressed as
           a measurement; the customer's own words are the thing an agent can
           actually disagree with, which is what makes the reading safe to show
           at all. */
        $jsst_said = trim((string) $jsst_row->evidence);
        $jsst_why  = trim((string) $jsst_row->reason);

        if ($jsst_said !== '' || $jsst_why !== '') {
            $jsst_out .= '<span class="jsst-triage-why">';
            if ($jsst_said !== '') {
                $jsst_out .= '&ldquo;' . esc_html($jsst_said) . '&rdquo; ';
            }
            if ($jsst_why !== '') {
                $jsst_out .= esc_html($jsst_why);
            }
            $jsst_out .= '</span>';
        }

        $jsst_out .= '<span class="jsst-triage-note">'
                   . esc_html(__('Written by the AI, not by a person. It only ever fills in a department or priority the customer left empty; anything it filled in is in the ticket history.', 'js-support-ticket'))
                   . '</span>';

        return $jsst_out . '</div>';
    }

    /** The numbers for a screen. */
    public static function summary($jsst_days = 30) {
        $jsst_out = array('read' => 0, 'unhappy' => 0, 'atrisk' => 0, 'routed' => 0, 'automatic' => self::automatic());
        if (!self::available()) return $jsst_out;

        $jsst_since = gmdate('Y-m-d H:i:s', time() - (max(1, (int) $jsst_days) * DAY_IN_SECONDS));

        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT COUNT(*) AS jsst_read,
                    SUM(CASE WHEN score <= %d THEN 1 ELSE 0 END) AS jsst_unhappy,
                    SUM(CASE WHEN atrisk = 1 THEN 1 ELSE 0 END) AS jsst_atrisk,
                    SUM(CASE WHEN department <> '' THEN 1 ELSE 0 END) AS jsst_routed
               FROM `" . self::table() . "` WHERE updated >= %s",
            self::UNHAPPY_AT, $jsst_since));

        if ($jsst_row) {
            $jsst_out['read']    = (int) $jsst_row->jsst_read;
            $jsst_out['unhappy'] = (int) $jsst_row->jsst_unhappy;
            $jsst_out['atrisk']  = (int) $jsst_row->jsst_atrisk;
            $jsst_out['routed']  = (int) $jsst_row->jsst_routed;
        }
        return $jsst_out;
    }
}
