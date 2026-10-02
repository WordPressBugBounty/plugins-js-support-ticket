<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * What your customers asked that nothing could answer. (Roadmap 6.0-AI-12)
 *
 * Every other AI screen in this product is about governing what the machine may
 * do with the content that exists. This one is about the content that does not,
 * and it is the only one that makes the rest get better over time: an answer
 * engine is exactly as good as the documentation behind it, and the fastest way
 * to find out what is missing from that documentation is to notice what it kept
 * failing to answer.
 *
 * ## Five signals, and why each is a gap rather than a fault
 *
 *   `search`   somebody typing a ticket got no suggestions at all. The purest
 *              signal there is: a real question, asked in their own words,
 *              before they had given up and filed.
 *   `nokb`     autopilot looked for something to ground an answer in and found
 *              nothing, so it never asked the model.
 *   `weak`     an answer was written but rested on very little, so it was held.
 *   `rejected` a person read the answer and threw it away.
 *   `handoff`  the customer read an automatic answer and asked for a human.
 *
 * The first two mean "we have nothing on this". The last three mean "we have
 * something and it is not good enough", which is a different piece of work and
 * is why the kind is kept on every row rather than flattened into a count.
 *
 * ## Clustering is crude on purpose, and says so on the screen
 *
 * Twenty ways of asking one question have to become one recommendation or the
 * list is unreadable. The grouping is shared significant words measured against
 * the shorter question - the same measure JSSTaireview::agreement() uses, for
 * the same reason: an embedding similarity nobody can check would ask for
 * exactly the trust this screen exists to earn. A person can read two grouped
 * questions and tell instantly whether the grouping was right.
 *
 * ## The list has to be able to shrink
 *
 * A recommendation list that only grows is a to-do list nobody finishes. So a
 * cluster drops off on its own once retrieval starts finding something for it -
 * checked by asking the real retriever, not by anybody ticking a box - and can
 * be dismissed by hand for the questions that are never going to have an
 * article.
 */
class JSSTaigaps {

    const TABLE          = 'js_ticket_ai_gaps';
    const SCHEMA_VERSION = '6.0.0';
    const OPT_SCHEMA     = 'jsst_ai_gaps_schema';

    /** signature => when it was dismissed. */
    const OPT_DISMISSED = 'jsst_ai_gaps_dismissed';

    const KIND_SEARCH   = 'search';
    const KIND_NOCONTEXT = 'nokb';
    const KIND_WEAK     = 'weak';
    const KIND_REJECTED = 'rejected';
    const KIND_HANDOFF  = 'handoff';

    /** Questions read when clustering. Enough for a busy quarter, bounded. */
    const SAMPLE = 400;

    /** Rows kept. */
    const KEEP_DAYS = 180;

    /** Two questions join a cluster above this share of shared words. */
    const JOIN_AT = 0.5;

    /** A cluster is only worth an article once this many people have asked. */
    const WORTH_WRITING = 2;

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);
        add_action('jsst_ai_gaps_prune', array(__CLASS__, 'prune'));
        // On init, not here: scheduling reads the translated schedule labels.
        add_action('init', array(__CLASS__, 'scheduleprune'));
    }

    public static function scheduleprune() {
        if (!wp_next_scheduled('jsst_ai_gaps_prune')) {
            wp_schedule_event(time() + (2 * HOUR_IN_SECONDS), 'daily', 'jsst_ai_gaps_prune');
        }
    }

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    public static function ensureSchema() {
        if (!class_exists('JSSTschemaguard')) return;
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION,
                array(self::TABLE => array('kind', 'question', 'ticketid')))) {
            return;
        }

        $jsst_table   = self::table();
        $jsst_charset = jssupportticket::$_db->get_charset_collate();

        /* `question` is the customer's own words and is stored redacted. It has
           to be their words rather than a normalised key, because the whole
           value of this screen is a writer reading how the question was really
           asked - a list of stemmed tokens tells somebody nothing about what to
           write. `fingerprint` is the normalised form, and it exists so that a
           question asked twice in a minute is not two rows. */
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                created datetime DEFAULT NULL,
                kind varchar(20) NOT NULL DEFAULT 'search',
                question varchar(500) NOT NULL DEFAULT '',
                fingerprint char(32) NOT NULL DEFAULT '',
                ticketid bigint(20) NOT NULL DEFAULT '0',
                coverage tinyint(4) DEFAULT NULL,
                PRIMARY KEY (id),
                KEY jsst_when (created),
                KEY jsst_kind (kind, created),
                KEY jsst_print (fingerprint)
            ) " . $jsst_charset);

        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);
    }

    public static function table() {
        return jssupportticket::$_db->prefix . self::TABLE;
    }

    public static function available() {
        if (!isset(jssupportticket::$_db) || !is_object(jssupportticket::$_db)) return false;
        $jsst_table = self::table();
        return (jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)
        ) === $jsst_table);
    }

    public static function prune() {
        if (!self::available()) return 0;
        return (int) jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "DELETE FROM `" . self::table() . "` WHERE created < %s",
            gmdate('Y-m-d H:i:s', time() - (self::KEEP_DAYS * DAY_IN_SECONDS))));
    }

    public static function kinds() {
        return array(
            self::KIND_SEARCH => array(
                'label' => esc_html(__('Nothing was suggested', 'js-support-ticket')),
                'blurb' => esc_html(__('Somebody typed this into the ticket form and the search came back empty.', 'js-support-ticket')),
                'missing' => true,
            ),
            self::KIND_NOCONTEXT => array(
                'label' => esc_html(__('Nothing to answer from', 'js-support-ticket')),
                'blurb' => esc_html(__('The AI looked for something to ground an answer in and found nothing, so it never asked the model.', 'js-support-ticket')),
                'missing' => true,
            ),
            self::KIND_WEAK => array(
                'label' => esc_html(__('Answered from very little', 'js-support-ticket')),
                'blurb' => esc_html(__('An answer was written but rested on so little of your content that it was held for a person.', 'js-support-ticket')),
                'missing' => false,
            ),
            self::KIND_REJECTED => array(
                'label' => esc_html(__('The answer was thrown away', 'js-support-ticket')),
                'blurb' => esc_html(__('A person read what the AI proposed and rejected or withdrew it.', 'js-support-ticket')),
                'missing' => false,
            ),
            self::KIND_HANDOFF => array(
                'label' => esc_html(__('The customer asked for a person', 'js-support-ticket')),
                'blurb' => esc_html(__('They read an automatic answer and pressed the button asking for a human.', 'js-support-ticket')),
                'missing' => false,
            ),
        );
    }

    public static function kindLabel($jsst_kind) {
        $jsst_kinds = self::kinds();
        return isset($jsst_kinds[$jsst_kind]) ? $jsst_kinds[$jsst_kind]['label'] : $jsst_kind;
    }

    /* ------------------------------------------------------------------ *
     * Collecting
     * ------------------------------------------------------------------ */

    /**
     * Write down one question nothing could answer.
     *
     * Called from wherever the failure is noticed rather than from one place,
     * because the five failures happen in five different parts of the product
     * and none of them can see the others. Every caller passes the customer's
     * own words; everything else about storing them is decided here.
     *
     * Three things happen to the text before it is stored: it is capped, it is
     * scrubbed through the same redaction the outbound prompts get, and it is
     * fingerprinted so the same question arriving twice in a minute - which the
     * ticket form does by design as somebody types - is one row.
     */
    public static function record($jsst_kind, $jsst_question, $jsst_args = array()) {
        if (!self::available()) return 0;
        if (!array_key_exists($jsst_kind, self::kinds())) return 0;

        /* The analytics switch governs this too. It is the setting a site turns
           off when it does not want customer text kept, and a screen built out
           of customer text has to be the first thing that honours it. */
        if (isset(jssupportticket::$_config['aiagent_analytics'])
            && jssupportticket::$_config['aiagent_analytics'] != 1) {
            return 0;
        }

        $jsst_question = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $jsst_question)));
        if ($jsst_question === '') return 0;

        /* Redacted before it is stored, not before it is shown. A card number
           in a support question would otherwise sit in this table for six
           months waiting for somebody to look at the screen.

           Scrubbed at the strictest level whatever the site chose, which is the
           one place in the product that overrides that setting. The setting
           governs how much is removed before text is handed to somebody else's
           server, and it is a real trade-off there - scrub too hard and the
           answer loses the order number it was about. Here there is no
           trade-off to make: nothing about writing a knowledge base article
           needs the customer's card number, so keeping one for six months buys
           nothing and risks everything. */
        if (class_exists('JSSTaipolicy')) {
            $jsst_question = JSSTaipolicy::redact($jsst_question, 'strict');
        }
        $jsst_question = jssupportticketphplib::JSST_substr($jsst_question, 0, 500);

        $jsst_print = md5(self::normalise($jsst_question));
        $jsst_seen  = 'jsst_ai_gap_' . $jsst_print;
        if (get_transient($jsst_seen)) return 0;
        set_transient($jsst_seen, 1, MINUTE_IN_SECONDS);

        jssupportticket::$_db->insert(self::table(), array(
            'created'     => current_time('Y-m-d H:i:s'),
            'kind'        => $jsst_kind,
            'question'    => $jsst_question,
            'fingerprint' => $jsst_print,
            'ticketid'    => isset($jsst_args['ticket']) ? (int) $jsst_args['ticket'] : 0,
            'coverage'    => isset($jsst_args['coverage']) && $jsst_args['coverage'] !== null
                ? max(0, min(100, (int) $jsst_args['coverage'])) : null,
        ));

        return (int) jssupportticket::$_db->insert_id;
    }

    /**
     * Record a gap when all the caller has is a ticket id.
     *
     * Three of the five signals are noticed long after the question was asked -
     * a person rejecting an answer, a customer pressing "give me a human" - and
     * none of those places is holding the question any more. Reading it back
     * from the ticket keeps the caller a one-liner and keeps the decision about
     * *which* text counts as the question in one place.
     */
    public static function recordForTicket($jsst_kind, $jsst_ticketid, $jsst_args = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_ticketid <= 0) return 0;

        $jsst_ticket = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT subject, message FROM `" . jssupportticket::$_db->prefix
            . "js_ticket_tickets` WHERE id = %d", $jsst_ticketid));
        if (!$jsst_ticket) return 0;

        /* Subject and message together, the same text the retriever searches
           on, so a gap recorded here clusters with one recorded at the form. */
        $jsst_question = trim($jsst_ticket->subject . ' ' . wp_strip_all_tags($jsst_ticket->message));

        $jsst_args['ticket'] = $jsst_ticketid;
        return self::record($jsst_kind, $jsst_question, $jsst_args);
    }

    /** Lower case, no punctuation, single spaces - for the fingerprint only. */
    private static function normalise($jsst_text) {
        $jsst_text = jssupportticketphplib::JSST_strtolower((string) $jsst_text);
        $jsst_text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $jsst_text);
        return trim(preg_replace('/\s+/', ' ', $jsst_text));
    }

    /**
     * The words worth grouping on.
     *
     * Short words and the commonest question-shaped filler carry no topic, and
     * leaving them in makes every question about "how do I" cluster together -
     * which is one enormous useless group rather than twenty useful ones.
     */
    public static function terms($jsst_text) {
        $jsst_stop = apply_filters('jsst_ai_gap_stopwords', array(
            'how', 'the', 'and', 'for', 'you', 'your', 'can', 'with', 'this', 'that',
            'from', 'what', 'when', 'where', 'why', 'does', 'not', 'have', 'has',
            'are', 'was', 'were', 'will', 'would', 'could', 'should', 'please',
            'help', 'need', 'want', 'get', 'got', 'any', 'all', 'but', 'out',
            'been', 'there', 'they', 'them', 'about', 'into', 'than', 'then',
        ));

        /* What the scrubbing left behind is not a topic. A question that had a
           card number in it comes out of record() as "...my card [redacted
           number]...", and without this the suggested title is "Charged twice
           card number" - which names the redaction rather than the subject, and
           groups every scrubbed question with every other one. Removed here
           rather than added to the stop list above, because "number" on its own
           is a perfectly good topic word: it is the placeholder that is noise,
           not the word inside it. */
        $jsst_text = preg_replace('/\[redacted[^\]]*\]/i', ' ', (string) $jsst_text);

        $jsst_out = array();
        foreach (explode(' ', self::normalise($jsst_text)) as $jsst_word) {
            if (jssupportticketphplib::JSST_strlen($jsst_word) < 4) continue;
            if (in_array($jsst_word, $jsst_stop, true)) continue;
            if (!in_array($jsst_word, $jsst_out, true)) $jsst_out[] = $jsst_word;
        }
        return $jsst_out;
    }

    /**
     * How alike two questions are, as a share of the shorter one's words.
     *
     * Measured against the shorter deliberately, the same way AI-04's agreement
     * does: "export tickets" and "how do I export all of my tickets to a csv
     * file" are the same question, and dividing by the longer would say they
     * are barely related.
     */
    public static function likeness($jsst_a, $jsst_b) {
        $jsst_x = self::terms($jsst_a);
        $jsst_y = self::terms($jsst_b);
        $jsst_shorter = min(count($jsst_x), count($jsst_y));
        if ($jsst_shorter === 0) return 0.0;

        return (count(array_intersect($jsst_x, $jsst_y)) / $jsst_shorter);
    }

    /* ------------------------------------------------------------------ *
     * Clustering
     * ------------------------------------------------------------------ */

    /**
     * The questions, grouped into things somebody could write one article about.
     *
     * A single greedy pass, newest first: each question joins the first cluster
     * it is alike enough to, or starts one. Not the best clustering available
     * and not trying to be - it is the one whose output a person can check by
     * reading two rows, which on a screen asking somebody to spend an afternoon
     * writing is worth more than a better number they have to take on faith.
     */
    public static function clusters($jsst_args = array()) {
        if (!self::available()) return array();

        $jsst_days = isset($jsst_args['days']) ? max(1, (int) $jsst_args['days']) : 90;
        $jsst_kind = isset($jsst_args['kind']) ? (string) $jsst_args['kind'] : '';

        $jsst_where = jssupportticket::$_db->prepare('created >= %s',
            gmdate('Y-m-d H:i:s', time() - ($jsst_days * DAY_IN_SECONDS)));
        if ($jsst_kind !== '' && array_key_exists($jsst_kind, self::kinds())) {
            $jsst_where .= jssupportticket::$_db->prepare(' AND kind = %s', $jsst_kind);
        }

        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE " . $jsst_where
            . " ORDER BY id DESC LIMIT %d", self::SAMPLE));

        $jsst_clusters = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_joined = false;

            foreach ($jsst_clusters as &$jsst_cluster) {
                if (self::likeness($jsst_cluster['lead'], $jsst_row->question) >= self::JOIN_AT) {
                    $jsst_cluster['rows'][] = $jsst_row;
                    $jsst_joined = true;
                    break;
                }
            }
            unset($jsst_cluster);

            if (!$jsst_joined) {
                $jsst_clusters[] = array('lead' => $jsst_row->question, 'rows' => array($jsst_row));
            }
        }

        return self::summarise($jsst_clusters);
    }

    /** Turn the raw groups into what a screen reads. */
    private static function summarise($jsst_clusters) {
        $jsst_dismissed = self::dismissals();
        $jsst_out = array();

        foreach ($jsst_clusters as $jsst_cluster) {
            $jsst_signature = self::signature($jsst_cluster['rows']);
            $jsst_kinds = array();
            $jsst_tickets = array();
            $jsst_questions = array();
            $jsst_last = 0;

            foreach ($jsst_cluster['rows'] as $jsst_row) {
                $jsst_kinds[$jsst_row->kind] = isset($jsst_kinds[$jsst_row->kind]) ? $jsst_kinds[$jsst_row->kind] + 1 : 1;
                if ((int) $jsst_row->ticketid > 0) $jsst_tickets[(int) $jsst_row->ticketid] = true;
                if (!in_array($jsst_row->question, $jsst_questions, true)) {
                    $jsst_questions[] = $jsst_row->question;
                }
                $jsst_when = strtotime($jsst_row->created);
                if ($jsst_when > $jsst_last) $jsst_last = $jsst_when;
            }

            arsort($jsst_kinds);

            $jsst_out[] = array(
                'signature' => $jsst_signature,
                'lead'      => $jsst_cluster['lead'],
                'asked'     => count($jsst_cluster['rows']),
                'distinct'  => count($jsst_questions),
                'questions' => $jsst_questions,
                'tickets'   => array_keys($jsst_tickets),
                'kinds'     => $jsst_kinds,
                'kind'      => key($jsst_kinds),
                'terms'     => self::terms($jsst_cluster['lead']),
                'last'      => $jsst_last,
                'dismissed' => isset($jsst_dismissed[$jsst_signature]),
                'worth'     => (count($jsst_cluster['rows']) >= self::WORTH_WRITING),
            );
        }

        /* Most asked first, and the most recent breaking the tie: the question
           twelve people asked last week is worth more of somebody's afternoon
           than the one twelve people asked in March. */
        usort($jsst_out, array(__CLASS__, 'byDemand'));
        return $jsst_out;
    }

    private static function byDemand($jsst_a, $jsst_b) {
        if ($jsst_a['asked'] !== $jsst_b['asked']) {
            return ($jsst_b['asked'] - $jsst_a['asked']);
        }
        return ($jsst_b['last'] - $jsst_a['last']);
    }

    /**
     * A stable name for a cluster.
     *
     * Its three commonest significant words, sorted. It has to survive new
     * questions joining the cluster - a signature that changed every time
     * somebody asked again would lose the dismissal a moment after it was made
     * - so it is built from the words the members share rather than from the
     * lead question or from any row's id.
     */
    public static function signature($jsst_rows) {
        $jsst_counts = array();
        foreach ($jsst_rows as $jsst_row) {
            foreach (self::terms($jsst_row->question) as $jsst_term) {
                $jsst_counts[$jsst_term] = isset($jsst_counts[$jsst_term]) ? $jsst_counts[$jsst_term] + 1 : 1;
            }
        }
        arsort($jsst_counts);
        $jsst_top = array_slice(array_keys($jsst_counts), 0, 3);
        sort($jsst_top);

        return ($jsst_top === array()) ? 'unnamed' : md5(implode(' ', $jsst_top));
    }

    /* ------------------------------------------------------------------ *
     * What to do about one
     * ------------------------------------------------------------------ */

    /**
     * The brief for a writer.
     *
     * A title they will rewrite and a list of the real questions, in the words
     * they were really asked. That list is the actual deliverable: the title is
     * a guess this code has no business being confident about, and the
     * questions are evidence.
     */
    public static function brief($jsst_cluster) {
        $jsst_terms = isset($jsst_cluster['terms']) ? $jsst_cluster['terms'] : array();
        $jsst_title = empty($jsst_terms)
            ? esc_html(__('An article your customers are asking for', 'js-support-ticket'))
            : ucfirst(implode(' ', array_slice($jsst_terms, 0, 4)));

        $jsst_lines = array();
        $jsst_lines[] = esc_html(__('Questions this article should answer:', 'js-support-ticket'));
        foreach ((array) $jsst_cluster['questions'] as $jsst_question) {
            $jsst_lines[] = '- ' . $jsst_question;
        }
        if (!empty($jsst_cluster['tickets'])) {
            $jsst_lines[] = '';
            $jsst_lines[] = sprintf(
                /* translators: %s: a comma separated list of ticket numbers */
                esc_html(__('Raised on tickets: %s', 'js-support-ticket')),
                '#' . implode(', #', array_slice($jsst_cluster['tickets'], 0, 20)));
        }

        return array('title' => $jsst_title, 'body' => implode("\n", $jsst_lines));
    }

    /**
     * Would retrieval find something for this now?
     *
     * Asked of the real retriever rather than tracked by a flag, so a cluster
     * closes itself the moment somebody publishes the article - including when
     * they wrote it without ever visiting this screen. A list that only shrinks
     * when somebody remembers to tick something is a list that never shrinks.
     */
    public static function covered($jsst_cluster) {
        if (!class_exists('JSSTaisources')) return false;
        $jsst_lead = isset($jsst_cluster['lead']) ? $jsst_cluster['lead'] : '';
        if ($jsst_lead === '') return false;

        $jsst_console = JSSTaisources::console($jsst_lead, 'reply');
        return !empty($jsst_console['snippets']);
    }

    /**
     * Where a writer goes to write it.
     *
     * The knowledge base if that add-on is present, the FAQ screen otherwise,
     * and nothing at all when neither is - a button that lands on a page a site
     * does not have is worse than no button.
     */
    public static function authorUrl($jsst_cluster, $jsst_where = '') {
        $jsst_brief = self::brief($jsst_cluster);
        /* Raw, not rawurlencode()d: add_query_arg() encodes what it is given,
           so pre-encoding it here puts "How%20do%20I" in the subject box. */
        $jsst_args  = array('jsst_gap' => $jsst_brief['title']);

        $jsst_have_kb  = in_array('knowledgebase', jssupportticket::$_active_addons);
        $jsst_have_faq = in_array('faq', jssupportticket::$_active_addons);

        if ($jsst_where === 'faq' && $jsst_have_faq) {
            return add_query_arg($jsst_args, admin_url('admin.php?page=faq&jstlay=addfaq'));
        }
        if ($jsst_where === '' && $jsst_have_kb) {
            return add_query_arg($jsst_args, admin_url('admin.php?page=knowledgebase&jstlay=addarticle'));
        }
        if ($jsst_where === '' && $jsst_have_faq) {
            return add_query_arg($jsst_args, admin_url('admin.php?page=faq&jstlay=addfaq'));
        }
        return '';
    }

    /**
     * The suggested title, read back on the form the writer landed on.
     *
     * The other half of authorUrl(). Kept here rather than repeated in the two
     * add-on templates that use it so there is one decision about what the
     * argument is called and one place that sanitises it - the value is a query
     * string somebody can type, so it is treated as hostile even though the
     * link that normally carries it was built two functions further up.
     *
     * Returns an empty string when there is no hand-off in progress, which is
     * every other time either of those forms is opened.
     */
    public static function handoffSubject() {
        if (empty($_GET['jsst_gap'])) return '';

        $jsst_title = sanitize_text_field(wp_unslash($_GET['jsst_gap']));
        $jsst_title = trim(preg_replace('/\s+/', ' ', $jsst_title));

        return jssupportticketphplib::JSST_substr($jsst_title, 0, 200);
    }

    /* ------------------------------------------------------------------ *
     * Dismissal
     * ------------------------------------------------------------------ */

    public static function dismissals() {
        $jsst_saved = get_option(self::OPT_DISMISSED, array());
        return is_array($jsst_saved) ? $jsst_saved : array();
    }

    /**
     * Put one out of sight.
     *
     * For the questions that are never going to have an article - somebody's
     * order number, a competitor's product, a question that only ever gets
     * asked once. Kept as a signature rather than deleting the rows, because
     * the rows are the evidence and the next person may disagree.
     */
    public static function dismiss($jsst_signature, $jsst_hide = true) {
        $jsst_saved = self::dismissals();

        if ($jsst_hide) {
            $jsst_saved[(string) $jsst_signature] = time();
        } else {
            unset($jsst_saved[(string) $jsst_signature]);
        }

        /* Bounded to the most recent, because dismissals are cheap to make and
           nobody ever tidies them; an option that grows for the life of a site
           is one that is eventually measured in megabytes. Losing the oldest
           means an ancient dismissal can resurface, which is the harmless
           direction - it reappears on a screen rather than hiding something. */
        if (count($jsst_saved) > 300) {
            arsort($jsst_saved);
            $jsst_saved = array_slice($jsst_saved, 0, 300, true);
        }
        update_option(self::OPT_DISMISSED, $jsst_saved, false);
        return true;
    }

    /* ------------------------------------------------------------------ *
     * Headline
     * ------------------------------------------------------------------ */

    /** The numbers over the list. */
    public static function stats($jsst_days = 90) {
        $jsst_out = array('questions' => 0, 'missing' => 0, 'kinds' => array());
        if (!self::available()) return $jsst_out;

        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT kind, COUNT(id) AS jsst_total FROM `" . self::table() . "`
              WHERE created >= %s GROUP BY kind",
            gmdate('Y-m-d H:i:s', time() - (max(1, (int) $jsst_days) * DAY_IN_SECONDS))));

        $jsst_kinds = self::kinds();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_total = (int) $jsst_row->jsst_total;
            $jsst_out['questions'] += $jsst_total;
            $jsst_out['kinds'][(string) $jsst_row->kind] = $jsst_total;

            if (!empty($jsst_kinds[$jsst_row->kind]['missing'])) {
                $jsst_out['missing'] += $jsst_total;
            }
        }
        return $jsst_out;
    }
}
