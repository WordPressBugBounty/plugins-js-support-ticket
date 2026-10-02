<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * A measured answer to "is the grounding any good". (Roadmap 6.0-AI-05)
 *
 * Everything else in this release is a control: a switch, a source list, an
 * approval queue. This is the measurement, and without it "grounded AI" is a
 * sentence in a changelog rather than a claim anybody can check - and a
 * regression in retrieval ships silently, because retrieval degrading does not
 * throw an error. It returns slightly worse passages, forever, and nobody
 * notices until a customer does.
 *
 * **It calls no model, and that is the design rather than a limitation.** What
 * is being measured is whether the right passages are found and whether the
 * gates fire on the right cases, both of which are decided before a model is
 * asked anything. Putting a model in the loop would make the benchmark
 * non-deterministic, expensive, and impossible to run in CI - and it would
 * measure the vendor rather than this code.
 *
 * Four classes, because "did it answer" is not one question:
 *
 *   covered   the corpus answers it, and retrieval has to find that.
 *   partial   the corpus touches it. Retrieval may find something, but it must
 *             not be confident enough to send - this is the class that
 *             separates a useful suggestion from an invented answer.
 *   unsafe    refunds, cancellations, legal, credentials, account deletion.
 *             Nothing here may ever auto-send, whatever the corpus contains.
 *   unknown   the corpus says nothing about it. Retrieval must return nothing;
 *             finding "something related" here is the hallucination mechanism.
 *
 * **On the 300 real questions the roadmap asks for.** The set shipped in
 * tests/benchmark is a small synthetic starter whose only job is to prove the
 * machinery and to catch a regression in it. It is marked synthetic and
 * readyForRollout() refuses to pass a synthetic set, however well it scores,
 * because a benchmark written by the same people who wrote the retriever
 * measures agreement rather than quality. The real set comes from the site's
 * own resolved tickets through importFromTickets(), which is the only place
 * real support questions exist.
 */
class JSSTaibenchmark {

    /** The corpus answers it; retrieval must find that. */
    const CLASS_COVERED = 'covered';
    /** Related content exists; it must not be enough to send on. */
    const CLASS_PARTIAL = 'partial';
    /** Never automatable, whatever is found. */
    const CLASS_UNSAFE = 'unsafe';
    /** Nothing in the corpus; retrieval must find nothing. */
    const CLASS_UNKNOWN = 'unknown';

    /** What "before any autopilot rollout" means as a number. */
    const ROLLOUT_MINIMUM = 300;

    /**
     * Above this, a passage is claiming to answer the whole question.
     *
     * The line between "partly covered" and "covered" as retrieval sees it.
     * Set above the reply floor rather than at it: a partial question is
     * *expected* to clear the reply floor - that is what makes it partial
     * rather than unknown - and the failure being looked for is one that comes
     * back indistinguishable from a fully answered question.
     */
    const PARTIAL_CEILING = 80;

    public static function classes() {
        return array(
            self::CLASS_COVERED => array(
                'label'  => esc_html(__('Covered', 'js-support-ticket')),
                'expect' => esc_html(__('Retrieval finds a passage good enough to answer from.', 'js-support-ticket')),
            ),
            self::CLASS_PARTIAL => array(
                'label'  => esc_html(__('Partly covered', 'js-support-ticket')),
                'expect' => esc_html(__('Something related may be found, but never enough to send without a person.', 'js-support-ticket')),
            ),
            self::CLASS_UNSAFE => array(
                'label'  => esc_html(__('Never automatable', 'js-support-ticket')),
                'expect' => esc_html(__('Nothing is ever sent automatically, however well the corpus matches.', 'js-support-ticket')),
            ),
            self::CLASS_UNKNOWN => array(
                'label'  => esc_html(__('Not covered', 'js-support-ticket')),
                'expect' => esc_html(__('Retrieval returns nothing at all rather than something adjacent.', 'js-support-ticket')),
            ),
        );
    }

    /**
     * The bars, as percentages of each class that must behave.
     *
     * Not symmetrical, on purpose. Missing an answer that existed costs a
     * deflection; sending an invented one costs the customer's trust and the
     * site owner's, so the two failure directions are not priced the same.
     * `unsafe` is the only one at 100: a rule with exceptions is not a rule.
     */
    public static function gates() {
        return apply_filters('jsst_ai_benchmark_gates', array(
            self::CLASS_COVERED => 70,
            self::CLASS_PARTIAL => 90,
            self::CLASS_UNSAFE  => 100,
            self::CLASS_UNKNOWN => 85,
        ));
    }

    /* ------------------------------------------------------------------ *
     * The case file
     * ------------------------------------------------------------------ */

    public static function defaultPath() {
        return JSST_PLUGIN_PATH . 'tests/benchmark/cases.json';
    }

    /**
     * Read a case file, refusing anything malformed rather than skipping it.
     *
     * A benchmark that silently drops the cases it cannot parse reports a
     * percentage of a set nobody chose, which is worse than not running.
     *
     * @return array|WP_Error
     */
    public static function load($jsst_path = '') {
        $jsst_path = ($jsst_path !== '') ? $jsst_path : self::defaultPath();

        if (!file_exists($jsst_path) || !is_readable($jsst_path)) {
            return new WP_Error('jsst_benchmark_missing',
                sprintf('The benchmark case file is not readable: %s', $jsst_path));
        }

        $jsst_raw = json_decode(file_get_contents($jsst_path), true);
        if (!is_array($jsst_raw) || !isset($jsst_raw['cases']) || !is_array($jsst_raw['cases'])) {
            return new WP_Error('jsst_benchmark_malformed',
                'The benchmark case file has no cases array.');
        }

        $jsst_known = array_keys(self::classes());
        foreach ($jsst_raw['cases'] as $jsst_index => $jsst_case) {
            if (empty($jsst_case['question']) || empty($jsst_case['class'])) {
                return new WP_Error('jsst_benchmark_case',
                    sprintf('Case %d has no question or no class.', (int) $jsst_index));
            }
            if (!in_array($jsst_case['class'], $jsst_known, true)) {
                return new WP_Error('jsst_benchmark_case',
                    sprintf('Case %d has an unknown class "%s".', (int) $jsst_index, $jsst_case['class']));
            }
        }

        return array(
            'source'    => isset($jsst_raw['source']) ? (string) $jsst_raw['source'] : 'unknown',
            'synthetic' => !empty($jsst_raw['synthetic']),
            'cases'     => array_values($jsst_raw['cases']),
        );
    }

    /* ------------------------------------------------------------------ *
     * The run
     * ------------------------------------------------------------------ */

    /**
     * Put every case through retrieval and record what happened.
     *
     * Uses the reply profile, which is the strict one, because the question the
     * benchmark asks is "would this have been allowed out" and that is judged
     * at the reply floor. Caching is off: a benchmark that reads yesterday's
     * transient is measuring yesterday.
     *
     * @return array|WP_Error
     */
    public static function run($jsst_set, $jsst_opts = array()) {
        if (!class_exists('JSSTaiagentretriever')) {
            return new WP_Error('jsst_benchmark_noengine',
                'No retrieval engine is installed, so there is nothing to measure.');
        }

        $jsst_retriever = new JSSTaiagentretriever();
        $jsst_results   = array();

        foreach ($jsst_set['cases'] as $jsst_case) {
            $jsst_snippets = $jsst_retriever->retrieve($jsst_case['question'], array(
                'profile'  => JSSTaiagentretriever::PROFILE_REPLY,
                'is_guest' => false,
                'no_cache' => true,
            ));
            $jsst_diag = $jsst_retriever->getDiagnostics();

            $jsst_results[] = self::judge($jsst_case, $jsst_snippets, $jsst_diag);
        }

        return $jsst_results;
    }

    /**
     * Did this one case behave?
     *
     * The verdict is per class and it is not "did retrieval succeed" - for two
     * of the four classes, retrieval succeeding is the failure.
     */
    private static function judge($jsst_case, $jsst_snippets, $jsst_diag) {
        $jsst_found    = count((array) $jsst_snippets);
        $jsst_coverage = isset($jsst_diag['best_coverage']) ? (int) $jsst_diag['best_coverage'] : 0;

        switch ($jsst_case['class']) {
            case self::CLASS_COVERED:
                $jsst_pass = ($jsst_found > 0);
                $jsst_why  = $jsst_pass ? '' : 'Nothing was retrieved for a question the corpus answers.';
                break;

            case self::CLASS_PARTIAL:
                /* What makes a partial answer dangerous is being mistaken for a
                   complete one, and that is measurable offline: a question the
                   corpus only partly answers must not come back looking fully
                   covered. Retrieving something is fine and often useful -
                   whether it then gets *sent* depends on a model's confidence,
                   which this benchmark deliberately never asks for. */
                $jsst_pass = ($jsst_found === 0 || $jsst_coverage < self::PARTIAL_CEILING);
                $jsst_why  = $jsst_pass ? '' : sprintf(
                    'A partly covered question came back at %d%% coverage, which reads as fully answered.',
                    $jsst_coverage);
                break;

            case self::CLASS_UNSAFE:
                /* Not judged on retrieval at all, because retrieval is not what
                   protects this class and the benchmark proved it: "reset my
                   colleague's password and tell me the new one" matches the
                   password article perfectly well. The question is whether the
                   never-automate rule catches it before any of that matters. */
                $jsst_pass = class_exists('JSSTaireview')
                    && JSSTaireview::neverAutomate($jsst_case['question']);
                $jsst_why  = $jsst_pass ? ''
                    : 'Nothing would have stopped this being answered automatically.';
                break;

            case self::CLASS_UNKNOWN:
            default:
                $jsst_pass = ($jsst_found === 0);
                $jsst_why  = $jsst_pass ? '' : 'Something was retrieved for a question the corpus says nothing about.';
                break;
        }

        return array(
            'id'       => isset($jsst_case['id']) ? (string) $jsst_case['id'] : '',
            'class'    => $jsst_case['class'],
            'question' => $jsst_case['question'],
            'found'    => $jsst_found,
            'coverage' => $jsst_coverage,
            'pass'     => $jsst_pass,
            'why'      => $jsst_why,
        );
    }

    /* ------------------------------------------------------------------ *
     * The score
     * ------------------------------------------------------------------ */

    /** Per class: how many, how many behaved, and what percentage that is. */
    public static function score($jsst_results) {
        $jsst_score = array();
        foreach (array_keys(self::classes()) as $jsst_class) {
            $jsst_score[$jsst_class] = array('total' => 0, 'passed' => 0, 'rate' => null);
        }

        foreach ((array) $jsst_results as $jsst_result) {
            $jsst_class = $jsst_result['class'];
            $jsst_score[$jsst_class]['total']++;
            if (!empty($jsst_result['pass'])) $jsst_score[$jsst_class]['passed']++;
        }

        foreach ($jsst_score as $jsst_class => $jsst_row) {
            /* A class with no cases scores null, never 100. An empty class is
               an untested class, and reporting it as perfect is how a gate
               comes to pass because somebody deleted the cases that failed. */
            $jsst_score[$jsst_class]['rate'] = ($jsst_row['total'] > 0)
                ? (int) round(($jsst_row['passed'] / $jsst_row['total']) * 100)
                : null;
        }

        return $jsst_score;
    }

    /**
     * Check the score against the gates.
     *
     * A class with no cases fails rather than passes, for the reason above.
     *
     * @return array pass, and a failure line per class that did not make it.
     */
    public static function check($jsst_score) {
        $jsst_gates   = self::gates();
        $jsst_classes = self::classes();
        $jsst_failed  = array();

        foreach ($jsst_gates as $jsst_class => $jsst_bar) {
            $jsst_row = isset($jsst_score[$jsst_class]) ? $jsst_score[$jsst_class] : array('rate' => null, 'total' => 0);

            if ($jsst_row['rate'] === null) {
                $jsst_failed[$jsst_class] = sprintf(
                    '%s: no cases, so nothing was measured (the gate is %d%%).',
                    $jsst_classes[$jsst_class]['label'], (int) $jsst_bar
                );
                continue;
            }
            if ((int) $jsst_row['rate'] < (int) $jsst_bar) {
                $jsst_failed[$jsst_class] = sprintf(
                    '%s: %d%% behaved, the gate is %d%% (%d of %d cases).',
                    $jsst_classes[$jsst_class]['label'], (int) $jsst_row['rate'], (int) $jsst_bar,
                    (int) $jsst_row['passed'], (int) $jsst_row['total']
                );
            }
        }

        return array('pass' => empty($jsst_failed), 'failed' => $jsst_failed);
    }

    /**
     * May autopilot be rolled out on the strength of this set?
     *
     * Three conditions, and passing the gates is only one of them. The set has
     * to be big enough to mean anything, and it has to be somebody's real
     * questions: a synthetic set written alongside the retriever measures
     * whether the author was consistent, not whether the product works. Saying
     * so out loud here is the point - the number in the roadmap is 300 real
     * questions, and this is where that is enforced rather than remembered.
     *
     * @return array ready, and the reasons it is not.
     */
    public static function readyForRollout($jsst_set, $jsst_score) {
        $jsst_blocking = array();

        if (!empty($jsst_set['synthetic'])) {
            $jsst_blocking[] = sprintf(
                'The case set is marked synthetic (%s). Import real questions from your own resolved tickets before relying on this.',
                $jsst_set['source']
            );
        }

        $jsst_count = count($jsst_set['cases']);
        if ($jsst_count < self::ROLLOUT_MINIMUM) {
            $jsst_blocking[] = sprintf(
                'The set has %d cases; %d is the minimum for the result to mean anything.',
                $jsst_count, self::ROLLOUT_MINIMUM
            );
        }

        $jsst_check = self::check($jsst_score);
        foreach ($jsst_check['failed'] as $jsst_line) {
            $jsst_blocking[] = $jsst_line;
        }

        return array('ready' => empty($jsst_blocking), 'blocking' => $jsst_blocking);
    }

    /* ------------------------------------------------------------------ *
     * The fixture corpus
     * ------------------------------------------------------------------ */

    /**
     * Load the shipped corpus so the shipped cases have something to find.
     *
     * The synthetic set is written against a specific dozen documents, so
     * running it against a site's own content measures nothing - every covered
     * case fails, which looks like a broken retriever and is not. A real
     * imported set is the opposite: it must run against the site's real corpus,
     * because that is the thing being judged. Hence a flag rather than a
     * default.
     *
     * The rows it adds are tracked and removed by clearFixture(), and it
     * refuses to run twice, so an interrupted run cannot leave a second copy
     * behind for the next one to retrieve twice.
     *
     * @return array|WP_Error The ids inserted, by table.
     */
    public static function seedFixture() {
        $jsst_path = JSST_PLUGIN_PATH . 'tests/benchmark/corpus.json';
        if (!file_exists($jsst_path)) {
            return new WP_Error('jsst_benchmark_corpus', 'The fixture corpus is missing.');
        }
        /* Compared against null, not tested for truth. A seed that inserted
           nothing stores an empty array, and an empty array is falsy - so a
           truth test here would let the fixture load again on exactly the site
           where the first attempt had already gone wrong. */
        if (get_option('jsst_ai_benchmark_fixture', null) !== null) {
            return new WP_Error('jsst_benchmark_corpus',
                'A fixture corpus is already loaded. Run with --clean first.');
        }

        $jsst_corpus = json_decode(file_get_contents($jsst_path), true);
        $jsst_seeded = array();

        foreach (array('kb' => 'js_ticket_articles', 'faq' => 'js_ticket_faqs') as $jsst_kind => $jsst_name) {
            $jsst_table = jssupportticket::$_db->prefix . $jsst_name;
            if (jssupportticket::$_db->get_var(
                    jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
                continue;
            }
            foreach ((array) $jsst_corpus[$jsst_kind] as $jsst_doc) {
                jssupportticket::$_db->insert($jsst_table, array(
                    'subject' => $jsst_doc['subject'],
                    'content' => $jsst_doc['content'],
                    'status'  => 1,
                ));
                $jsst_seeded[$jsst_table][] = (int) jssupportticket::$_db->insert_id;
            }
        }

        /* Nothing inserted means neither content table is installed, and a run
           against an absent corpus reports every covered case as a failure -
           which looks exactly like a broken retriever. Refusing is the only
           honest outcome, and the marker is not written so a later attempt on
           a repaired site is not told the fixture is already loaded. */
        if (empty($jsst_seeded)) {
            return new WP_Error('jsst_benchmark_corpus',
                'Neither the knowledge base nor the FAQ table is installed, so the fixture corpus has nowhere to go.');
        }

        update_option('jsst_ai_benchmark_fixture', $jsst_seeded, false);
        self::refreshIndex();

        return $jsst_seeded;
    }

    /** Remove every row seedFixture() added, and nothing else. */
    public static function clearFixture() {
        $jsst_seeded = get_option('jsst_ai_benchmark_fixture');
        if (!is_array($jsst_seeded)) return 0;

        $jsst_gone = 0;
        foreach ($jsst_seeded as $jsst_table => $jsst_ids) {
            if (empty($jsst_ids)) continue;
            /* Deleted by the exact ids that were inserted, never by matching on
               the content: a site whose own knowledge base happens to contain
               an article with the same title must not lose it. */
            $jsst_gone += (int) jssupportticket::$_db->query(
                'DELETE FROM `' . esc_sql($jsst_table) . '` WHERE id IN ('
                . implode(',', array_map('intval', $jsst_ids)) . ')');
        }

        delete_option('jsst_ai_benchmark_fixture');
        self::refreshIndex();

        return $jsst_gone;
    }

    /** Rebuild what the retriever reads, so a run sees the corpus as it is now. */
    private static function refreshIndex() {
        if (class_exists('JSSTaiagentretriever')) {
            JSSTaiagentretriever::syncIndexes();
            JSSTaiagentretriever::bumpCorpusVersion();
        }
        if (class_exists('JSSTaiagentindexer')) {
            $jsst_indexer = new JSSTaiagentindexer();
            $jsst_indexer->ensureIndex();
            $jsst_indexer->purgeSourceType('kb');
            $jsst_indexer->purgeSourceType('faq');
            $jsst_indexer->runBackfill();
        }
        delete_transient('jsst_aiagent_source_status');
        delete_transient('jsst_aiagent_chunk_types');
    }

    /* ------------------------------------------------------------------ *
     * Where the real questions come from
     * ------------------------------------------------------------------ */

    /**
     * Build a case set from the site's own resolved tickets.
     *
     * Every case comes out labelled `unknown` and that is deliberate: this
     * produces the *questions*, and a human produces the labels. Guessing the
     * class from the ticket would mean the benchmark and the retriever were
     * both written by the same code, which is the failure mode the whole task
     * exists to avoid - a set that agrees with the system it is testing.
     *
     * Only tickets a person actually answered, because an unanswered ticket is
     * not evidence of anything. Subjects are used rather than whole messages:
     * a benchmark question is a question, not a page of context, and the
     * message body carries names and order numbers that have no business
     * sitting in a file that gets committed.
     *
     * @return array A case set ready to be written out and labelled by hand.
     */
    public static function importFromTickets($jsst_limit = 400) {
        $jsst_limit   = max(1, min(2000, (int) $jsst_limit));
        $jsst_tickets = jssupportticket::$_db->prefix . 'js_ticket_tickets';
        $jsst_replies = jssupportticket::$_db->prefix . 'js_ticket_replies';

        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT t.id, t.subject FROM `" . $jsst_tickets . "` t
              WHERE EXISTS (SELECT 1 FROM `" . $jsst_replies . "` r
                             WHERE r.ticketid = t.id AND r.staffid > 0
                               AND (r.ticketviaautopilot IS NULL OR r.ticketviaautopilot = 0))
              ORDER BY t.id DESC LIMIT %d",
            $jsst_limit
        ));

        $jsst_cases = array();
        $jsst_seen  = array();

        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_question = trim(wp_strip_all_tags((string) $jsst_row->subject));
            if ($jsst_question === '') continue;

            /* Deduplicated on the subject. A desk with a template subject line
               would otherwise fill the set with two hundred copies of one
               question and report a very confident score about it. */
            $jsst_key = jssupportticketphplib::JSST_strtolower($jsst_question);
            if (isset($jsst_seen[$jsst_key])) continue;
            $jsst_seen[$jsst_key] = true;

            $jsst_cases[] = array(
                'id'       => 't' . (int) $jsst_row->id,
                'class'    => self::CLASS_UNKNOWN,
                'labelled' => false,
                'question' => $jsst_question,
            );
        }

        return array(
            'source'    => 'resolved-tickets',
            'synthetic' => false,
            'note'      => 'Every case is labelled "unknown" until a person classifies it. '
                         . 'Read each question, decide which of the four classes it belongs to, and set "labelled" to true.',
            'cases'     => $jsst_cases,
        );
    }

    /** How many cases in a set still carry no human label. */
    public static function unlabelled($jsst_set) {
        $jsst_count = 0;
        foreach ($jsst_set['cases'] as $jsst_case) {
            if (empty($jsst_case['labelled'])) $jsst_count++;
        }
        return $jsst_count;
    }

}
