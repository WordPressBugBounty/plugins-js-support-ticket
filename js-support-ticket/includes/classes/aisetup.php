<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * How far this site has got with setting the AI up. (Roadmap 6.0-AI-11)
 *
 * Five registers now stand behind automatic answers - what may run, what it may
 * read, what may be sent, who it happens to, and what it costs - and each of
 * them is right. Together they are six screens and about forty settings, which
 * is a fair description of the problem and a poor description of an afternoon.
 * An administrator who has never configured a retrieval system does not need
 * more control; they need to be told what to do next, and to be stopped from
 * doing the last step first.
 *
 * So this class is a **reading of the other five, not a sixth store**. It owns
 * exactly one piece of state of its own - whether somebody has dismissed it -
 * and every setting it writes goes through the setter that already owns it. A
 * wizard with its own copy of the settings is a wizard that disagrees with the
 * screens a fortnight later.
 *
 * ## Done is derived, never remembered
 *
 * A step is complete because the site's state says so, not because somebody
 * pressed Next. Remove the API key after step one and step one is no longer
 * done. This costs a few queries per page and buys the only property that
 * matters: the checklist cannot claim a site is set up when it is not.
 *
 * ## The steps, and where the roadmap's six went
 *
 * The task asks for: choose sources, test retrieval, preview answers, set
 * thresholds, run shadow mode, activate. Two changes, both deliberate.
 *
 * An `engine` step comes first, because none of the other six mean anything
 * without one and "you have no API key" buried inside a preview that quietly
 * does nothing is exactly the confusion this exists to remove.
 *
 * `test retrieval` and `preview answers` are one step. On one screen you type a
 * question and see both what was found and what would be said about it - and
 * splitting them asks a non-specialist to care about the difference between
 * retrieval and generation before they have seen either work.
 *
 * ## Activation is gated on evidence, not on clicks
 *
 * The last step will not let a site switch on automatic sending until shadow
 * mode has produced real proposals compared against real agent replies. That is
 * the entire difference between a wizard and a form: a form would let somebody
 * reach the end in ninety seconds and start writing to customers from a corpus
 * nobody has checked.
 */
class JSSTaisetup {

    /** Set when an administrator has said they do not want the prompt. */
    const OPT_DISMISSED = 'jsst_ai_setup_dismissed';

    /**
     * What shadow mode has to have produced before going live is offered.
     *
     * Small enough for a quiet desk to reach in a fortnight and large enough
     * that it cannot be reached by accident. Filterable, because a site with
     * six tickets a month has a fair argument and nobody here can make it for
     * them - but it is a deliberate act, not a setting on a screen.
     */
    const NEED_PROPOSED = 20;
    const NEED_PAIRED   = 10;

    /** What check() has already worked out this request. */
    private static $jsst_memo = array();

    public static function proof() {
        return apply_filters('jsst_ai_setup_proof', array(
            'proposed' => self::NEED_PROPOSED,
            'paired'   => self::NEED_PAIRED,
        ));
    }

    /* ------------------------------------------------------------------ *
     * The steps
     * ------------------------------------------------------------------ */

    /**
     * Every step, in order, with what it is for and where the real work is.
     *
     * `done` and `blocked` are filled in by state(); this is the shape.
     */
    public static function steps() {
        return array(
            'engine' => array(
                'title' => esc_html(__('Switch AI on and choose where it runs', 'js-support-ticket')),
                'blurb' => esc_html(__('On your own hardware, on a hosted model with your key, or on the allowance that came with your licence. Nothing else here works until one of them answers.', 'js-support-ticket')),
                'where' => 'aiagent_settings',
                'wherename' => esc_html(__('Settings', 'js-support-ticket')),
            ),
            'sources' => array(
                'title' => esc_html(__('Choose what it may answer from', 'js-support-ticket')),
                'blurb' => self::sourcesBlurb(),
                'where' => 'aiagent_sources',
                'wherename' => esc_html(__('Knowledge Sources', 'js-support-ticket')),
            ),
            'preview' => array(
                'title' => esc_html(__('Ask it something and see what it would say', 'js-support-ticket')),
                'blurb' => esc_html(__('A real question, the passages it finds, the answer it would write and what would have happened to that answer. This is the step to spend time on: everything after it is a number, and this is the thing the numbers are about.', 'js-support-ticket')),
                'where' => '',
                'wherename' => '',
            ),
            'limits' => array(
                'title' => esc_html(__('Set the floor, the reach and the budget', 'js-support-ticket')),
                'blurb' => esc_html(__('How sure it has to be before an answer may go out, which customers and departments are in scope, and what it may spend. The defaults are deliberately cautious; the point is to have looked at them.', 'js-support-ticket')),
                'where' => 'aiagent_autopilot',
                'wherename' => esc_html(__('Automatic Answers', 'js-support-ticket')),
            ),
            'shadow' => array(
                'title' => esc_html(__('Run it on real tickets without sending anything', 'js-support-ticket')),
                'blurb' => esc_html(__('It writes an answer to every ticket that arrives and sends none of them, and each one is put beside what your agent actually replied. A fortnight of this is worth more than any amount of configuring.', 'js-support-ticket')),
                'where' => 'aiagent_shadow',
                'wherename' => esc_html(__('Shadow Mode', 'js-support-ticket')),
            ),
            'live' => array(
                'title' => esc_html(__('Let it answer customers', 'js-support-ticket')),
                'blurb' => esc_html(__('Only once shadow mode has enough to judge by. Everything is reversible from the Autopilot screen, and every answer that goes out can be withdrawn.', 'js-support-ticket')),
                'where' => 'aiagent_autopilot',
                'wherename' => esc_html(__('Automatic Answers', 'js-support-ticket')),
            ),
        );
    }

    /**
     * Step two, naming only the sources this site can actually read.
     *
     * A fixed list promised the Knowledge Base and FAQs to a desk without the
     * add-on that holds them. The ones that need an add-on are still named,
     * once, as coming with one.
     */
    private static function sourcesBlurb() {
        if (!class_exists('JSSTaisources')) {
            return esc_html(__('Each source is approved on its own, down to the individual document. It cannot invent from content it was never allowed to see.', 'js-support-ticket'));
        }
        $jsst_here = array();
        $jsst_addon = array();
        foreach (JSSTaisources::types() as $jsst_key => $jsst_def) {
            if (JSSTaisources::present($jsst_key)) {
                $jsst_here[] = $jsst_def['label'];
            } else {
                $jsst_addon[] = $jsst_def['label'];
            }
        }
        $jsst_out = sprintf(
            /* translators: %s: comma-separated list of content sources */
            __('On this site: %s — each approved on its own, down to the individual document. It cannot invent from content it was never allowed to see.', 'js-support-ticket'),
            implode(', ', $jsst_here));
        if ($jsst_addon) {
            $jsst_out .= ' ' . sprintf(
                /* translators: %s: comma-separated list of content sources */
                __('%s come with add-ons.', 'js-support-ticket'),
                implode(', ', $jsst_addon));
        }
        return esc_html($jsst_out);
    }

    /**
     * Each step, with whether it is done and what is stopping it.
     *
     * Read from the registers every time. `evidence` is the sentence under the
     * step saying what the site actually has - a checklist that only says done
     * or not done makes somebody visit five screens to find out why.
     */
    public static function state() {
        $jsst_steps = self::steps();
        $jsst_out   = array();
        $jsst_prior = true;   // is everything before this step finished?

        foreach ($jsst_steps as $jsst_key => $jsst_step) {
            $jsst_check = self::check($jsst_key);

            $jsst_step['key']      = $jsst_key;
            $jsst_step['done']     = $jsst_check['done'];
            $jsst_step['evidence'] = $jsst_check['evidence'];
            $jsst_step['blocked']  = $jsst_check['blocked'];

            /* A step whose predecessors are unfinished is waiting rather than
               blocked: nothing is wrong with it, it is simply not its turn, and
               saying "blocked" about it would send somebody looking for a fault
               that is one line further up. */
            $jsst_step['waiting'] = (!$jsst_prior && !$jsst_step['done']);

            if (!$jsst_step['done']) $jsst_prior = false;
            $jsst_out[$jsst_key] = $jsst_step;
        }
        return $jsst_out;
    }

    /**
     * One step's verdict.
     *
     * Every branch asks a register rather than an option, so a setting changed
     * anywhere in the product is reflected here without this class knowing
     * where it is kept.
     */
    public static function check($jsst_key) {
        /* Memoised for the request. Later steps ask about earlier ones - live
           asks shadow, shadow asks limits - so without this one screen read
           runs shadowStats() and the sources survey several times over for the
           same answer. A class property rather than a static local, because
           forget() has to be able to clear it. */
        if (isset(self::$jsst_memo[$jsst_key])) return self::$jsst_memo[$jsst_key];

        $jsst_done     = false;
        $jsst_blocked  = '';
        $jsst_evidence = '';

        switch ($jsst_key) {
            case 'engine':
                $jsst_on = class_exists('JSSTaipolicy') && JSSTaipolicy::enabled();
                $jsst_ok = class_exists('JSSTaiengine') && JSSTaiengine::usable();
                $jsst_done = ($jsst_on && $jsst_ok);

                if (!$jsst_on) {
                    $jsst_evidence = esc_html(__('AI is switched off for this site.', 'js-support-ticket'));
                } elseif (!$jsst_ok) {
                    $jsst_evidence = esc_html(__('No engine is set up, or the one chosen has no key and no address.', 'js-support-ticket'));
                } else {
                    $jsst_evidence = sprintf(
                        /* translators: %s: the name of the chosen AI engine */
                        esc_html(__('%s is answering.', 'js-support-ticket')), JSSTaiengine::currentLabel());
                }
                break;

            case 'sources':
                if (!class_exists('JSSTaisources')) break;
                $jsst_types = JSSTaisources::approvedTypes();
                $jsst_ready = self::approvedWithContent();

                $jsst_done = ($jsst_ready > 0);
                if (empty($jsst_types)) {
                    $jsst_evidence = esc_html(__('Nothing is approved, so there is nothing to answer from.', 'js-support-ticket'));
                } elseif ($jsst_ready === 0) {
                    /* Approved and empty is the failure that looks exactly like
                       a broken engine from the outside: no answers, no error. */
                    $jsst_evidence = esc_html(__('Sources are approved but none of them has any content the AI can read yet.', 'js-support-ticket'));
                } else {
                    $jsst_evidence = sprintf(
                        /* translators: %s: how many approved sources have content */
                        esc_html(_n('%s approved source has content to answer from.',
                                    '%s approved sources have content to answer from.', $jsst_ready, 'js-support-ticket')),
                        number_format_i18n($jsst_ready));
                }
                break;

            case 'preview':
                /* Done when a preview has actually been run and something came
                   back. Nothing else on this list can be finished by reading
                   it, and this one should not be either. */
                $jsst_seen = (int) get_option('jsst_ai_setup_previewed', 0);
                $jsst_done = ($jsst_seen > 0);
                $jsst_evidence = $jsst_done
                    ? sprintf(
                        /* translators: %s: a date */
                        esc_html(__('Last tried %s.', 'js-support-ticket')),
                        date_i18n(get_option('date_format'), $jsst_seen))
                    : esc_html(__('Not tried yet.', 'js-support-ticket'));

                if (!self::check('engine')['done']) {
                    $jsst_blocked = esc_html(__('An engine has to be answering first.', 'js-support-ticket'));
                }
                break;

            case 'limits':
                if (!class_exists('JSSTairollout')) break;
                $jsst_rules = JSSTairollout::rules();

                /* Looked at, rather than set to anything in particular: there
                   is no correct floor, and a wizard that insisted on one would
                   be inventing a number and calling it advice. The marker is
                   written when the Autopilot screen is saved. */
                $jsst_done = (bool) get_option('jsst_ai_setup_limits', 0);
                $jsst_evidence = sprintf(
                    /* translators: 1: a confidence percentage, 2: how many audiences are included */
                    esc_html(__('Sends at %1$d%% confidence, to %2$s.', 'js-support-ticket')),
                    (int) $jsst_rules['threshold'],
                    empty($jsst_rules['audiences'])
                        ? esc_html(__('nobody', 'js-support-ticket'))
                        : sprintf(
                            /* translators: %s: how many audiences are included */
                            esc_html(_n('%s audience', '%s audiences', count($jsst_rules['audiences']), 'js-support-ticket')),
                            number_format_i18n(count($jsst_rules['audiences'])))
                );
                if (!self::check('sources')['done']) {
                    $jsst_blocked = esc_html(__('Approve something for it to answer from first.', 'js-support-ticket'));
                }
                break;

            case 'shadow':
                if (!class_exists('JSSTaireview') || !class_exists('JSSTairollout')) break;
                $jsst_stats = JSSTaireview::shadowStats(JSSTairollout::threshold());
                $jsst_need  = self::proof();

                $jsst_done = ($jsst_stats['proposed'] >= $jsst_need['proposed']
                           && $jsst_stats['paired'] >= $jsst_need['paired']);

                $jsst_evidence = sprintf(
                    /* translators: 1: answers written, 2: how many are needed, 3: answers compared with an agent, 4: how many are needed */
                    esc_html(__('%1$s of %2$s answers written, %3$s of %4$s compared with an agent.', 'js-support-ticket')),
                    number_format_i18n($jsst_stats['proposed']), number_format_i18n($jsst_need['proposed']),
                    number_format_i18n($jsst_stats['paired']), number_format_i18n($jsst_need['paired'])
                );

                if (!self::check('limits')['done']) {
                    $jsst_blocked = esc_html(__('Set the limits first, so shadow mode measures the floor you actually mean to use.', 'js-support-ticket'));
                }
                break;

            case 'live':
                if (!class_exists('JSSTairollout')) break;
                $jsst_done = JSSTairollout::live()
                    && class_exists('JSSTaireview') && JSSTaireview::mode() !== JSSTaireview::MODE_NEVER;

                $jsst_evidence = $jsst_done
                    ? esc_html(__('Answers are going out.', 'js-support-ticket'))
                    : esc_html(__('Nothing is sent to a customer without a person.', 'js-support-ticket'));

                if (!self::check('shadow')['done']) {
                    $jsst_blocked = esc_html(__('Shadow mode has not produced enough to judge by yet.', 'js-support-ticket'));
                }
                break;
        }

        self::$jsst_memo[$jsst_key] = array(
            'done' => $jsst_done, 'blocked' => $jsst_blocked, 'evidence' => $jsst_evidence);
        return self::$jsst_memo[$jsst_key];
    }

    /**
     * Forget what was worked out this request.
     *
     * Anything that changes the state and then reads it back needs this - the
     * wizard's own two actions do exactly that, and a checklist still saying
     * "not done" straight after doing it reads as a broken button.
     */
    public static function forget() {
        self::$jsst_memo = array();
        if (class_exists('JSSTaisources')) {
            JSSTaisources::forget();
        }
    }

    /**
     * Approved sources that actually have something in them.
     *
     * Approving a source that is empty is the commonest way a site ends up with
     * a working engine that answers nothing, and it looks identical to a broken
     * one from the outside.
     */
    public static function approvedWithContent() {
        if (!class_exists('JSSTaisources')) return 0;

        $jsst_ready = 0;
        foreach (JSSTaisources::state() as $jsst_row) {
            if (empty($jsst_row['approved']) || empty($jsst_row['present'])) continue;
            if ((int) $jsst_row['documents'] > 0 || (int) $jsst_row['indexed'] > 0) {
                $jsst_ready++;
            }
        }
        return $jsst_ready;
    }

    /* ------------------------------------------------------------------ *
     * Where the site is
     * ------------------------------------------------------------------ */

    /** The first step that is neither done nor waiting on another. */
    public static function next() {
        foreach (self::state() as $jsst_key => $jsst_step) {
            if (!$jsst_step['done']) return $jsst_key;
        }
        return '';
    }

    public static function complete() {
        return (self::next() === '');
    }

    /** How many steps are finished, for the progress line. */
    public static function progress() {
        $jsst_done = 0;
        foreach (self::state() as $jsst_step) {
            if ($jsst_step['done']) $jsst_done++;
        }
        return array('done' => $jsst_done, 'total' => count(self::steps()));
    }

    public static function dismissed() {
        return (bool) get_option(self::OPT_DISMISSED, false);
    }

    /**
     * Stop offering it.
     *
     * Sticky, and it does not un-stick when a step regresses: a site that has
     * decided not to use AI should not be asked again every time somebody
     * changes an unrelated setting. The screen stays reachable from the menu.
     */
    public static function dismiss($jsst_stop = true) {
        update_option(self::OPT_DISMISSED, (bool) $jsst_stop, false);
        return true;
    }

    /**
     * Should the menu carry the badge?
     *
     * Only where there is something to set up and nobody has said no - and not
     * on a site with no engine and no interest, which is most of them.
     */
    public static function nagging() {
        if (self::dismissed()) return false;
        if (!class_exists('JSSTaipolicy') || !JSSTaipolicy::enabled()) return false;
        return !self::complete();
    }

    /* ------------------------------------------------------------------ *
     * The two things the wizard does itself
     * ------------------------------------------------------------------ */

    /**
     * What the engine would say to one question.
     *
     * Core asks; whatever engine is installed answers, through the same filter
     * arrangement the approval queue uses to send. A filter nobody answers
     * returns false and the screen says the preview is unavailable, rather than
     * core inventing a simpler answer chain of its own - a preview built from a
     * different prompt than the live path is a preview of nothing.
     *
     * The verdict is computed here rather than by the engine, because "would
     * this have been sent" is core's question: it is the confidence floor, the
     * never-automate rule and the approval mode, all of which are core's.
     */
    public static function preview($jsst_question, $jsst_remember = true) {
        $jsst_question = trim(wp_strip_all_tags((string) $jsst_question));
        if ($jsst_question === '') return false;

        if (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled()) {
            return array('available' => false,
                         'reason' => esc_html(__('AI is switched off for this site.', 'js-support-ticket')));
        }

        $jsst_answer = apply_filters('jsst_ai_preview_answer', false, $jsst_question);
        if (!is_array($jsst_answer)) {
            return array('available' => false,
                         'reason' => esc_html(__('No engine on this site can compose an answer, so there is nothing to preview. The AI Agent add-on is what writes them.', 'js-support-ticket')));
        }

        /* The wizard's own bookkeeping, and only the wizard's. Live chat runs
           the same chain on every visitor message (6.0-CH-01), and letting that
           tick the "you have previewed an answer" step would mean the setup
           checklist completed itself out of somebody else's traffic - a step
           that says done because a stranger typed something is worse than no
           step at all. */
        if ($jsst_remember) {
            update_option('jsst_ai_setup_previewed', time(), false);
            self::forget();
        }

        $jsst_answer['available'] = true;
        $jsst_answer['question']  = $jsst_question;
        $jsst_answer['verdict']   = self::verdict($jsst_question, $jsst_answer);
        return $jsst_answer;
    }

    /**
     * What would have happened to this answer, in one sentence.
     *
     * Asked in the same order record() asks it, so the wizard cannot say one
     * thing and the live path do another.
     */
    private static function verdict($jsst_question, $jsst_answer) {
        if (empty($jsst_answer['answered'])) {
            return array('state' => 'none',
                         'text' => esc_html(__('Nothing would have been written: this question is not covered by the approved sources.', 'js-support-ticket')));
        }

        if (class_exists('JSSTaireview') && JSSTaireview::neverAutomate($jsst_question)) {
            return array('state' => 'never',
                         'text' => esc_html(__('This kind of question is never answered automatically, whatever the engine thought of its own answer. It would always go to a person.', 'js-support-ticket')));
        }

        if (class_exists('JSSTaireview') && JSSTaireview::mode() === JSSTaireview::MODE_NEVER) {
            return array('state' => 'held',
                         'text' => esc_html(__('This site proposes only, so it would have waited on the Approvals screen.', 'js-support-ticket')));
        }

        $jsst_floor = class_exists('JSSTairollout') ? JSSTairollout::threshold() : 85;
        $jsst_clear = class_exists('JSSTaireview')
            && JSSTaireview::clearsThreshold((int) $jsst_answer['confidence'], $jsst_floor);

        if (!$jsst_clear) {
            return array('state' => 'held', 'text' => sprintf(
                /* translators: 1: the engine's confidence, 2: the configured floor */
                esc_html(__('It would have waited for a person: %1$d%% confident against a floor of %2$d%%.', 'js-support-ticket')),
                (int) $jsst_answer['confidence'], $jsst_floor));
        }

        if (class_exists('JSSTairollout') && !JSSTairollout::live()) {
            return array('state' => 'held', 'text' => sprintf(
                /* translators: %d: the engine's confidence */
                esc_html(__('It cleared the floor at %d%%, but automatic answering is not switched on, so it would have waited for a person.', 'js-support-ticket')),
                (int) $jsst_answer['confidence']));
        }

        return array('state' => 'sent', 'text' => sprintf(
            /* translators: %d: the engine's confidence */
            esc_html(__('This would have been sent to the customer, at %d%% confidence.', 'js-support-ticket')),
            (int) $jsst_answer['confidence']));
    }

    /**
     * Start shadow mode, as one action.
     *
     * The combination is the whole reason this exists: shadow mode is
     * "automatic answering on" **and** "approvals set to propose only", and
     * somebody meeting this feature for the first time has no way of knowing
     * that switching automation on is the safe half of it. Getting it half
     * right - automation on, approvals not changed - is the one mistake here
     * that writes to customers.
     */
    public static function startShadow() {
        if (!class_exists('JSSTaireview') || !class_exists('JSSTairollout')) return false;

        JSSTaireview::setMode(JSSTaireview::MODE_NEVER);
        JSSTairollout::pause(false);
        JSSTairollout::setRules(array('enabled' => true));
        self::forget();

        /* Ordered so the propose-only mode is written first. If the second call
           fails - a filter, a database error - the site is left proposing
           nothing rather than sending unreviewed answers. */
        return (JSSTaireview::mode() === JSSTaireview::MODE_NEVER && JSSTairollout::on());
    }

    /**
     * Let answers reach customers.
     *
     * Refuses unless shadow mode has produced enough to judge by. That refusal
     * is the wizard: without it this is a form with a switch at the bottom, and
     * somebody reaches the bottom in ninety seconds.
     */
    public static function goLive() {
        if (!class_exists('JSSTaireview') || !class_exists('JSSTairollout')) return false;
        if (!self::check('shadow')['done']) return false;

        JSSTaireview::setMode(JSSTaireview::MODE_THRESHOLD);
        JSSTairollout::pause(false);
        JSSTairollout::setRules(array('enabled' => true));
        self::forget();

        if (class_exists('JSSTaipolicy')) {
            JSSTaipolicy::record('autopilot-live', 'autopilot');
        }
        return JSSTairollout::live();
    }
}
