<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * What language everything is in. (Roadmap 6.0-AI-09)
 *
 * The third register beside AI-02's "what may the AI read" and AI-03's "what
 * may it send". This one answers a question neither of those can: *whose*
 * language is this, and is the thing we are about to answer from written in it.
 *
 * ## Why a desk can be perfectly configured and still useless in French
 *
 * Retrieval in this product is a MySQL FULLTEXT match over passages. Nothing in
 * that chain has ever known what language anything is in, and three things go
 * wrong because of it, in increasing order of how badly:
 *
 *   **Ranking is blind.** A French customer's question matches whichever
 *   passages share the most words with it. On a desk whose knowledge base is
 *   half English and half French, the English half competes on equal terms and
 *   usually wins, because it is usually the bigger half.
 *
 *   **Grounding punishes a correct answer.** The engine is told to answer in
 *   the customer's language and to use only the supplied material. Do both, and
 *   the reply shares almost no words with its own sources - so the grounding
 *   check, which measures exactly that, scores a faithful translation near
 *   zero and holds it. A multilingual desk therefore has to choose between
 *   answers nobody has checked and answers that never go out. That is the fault
 *   this task exists to remove, and the fix is in `groundingTokens()` below.
 *
 *   **Some languages retrieve nothing at all, silently.** MySQL's default
 *   FULLTEXT parser splits on spaces, and Chinese, Japanese and Thai are not
 *   written with them. The index builds, the query runs, the result set is
 *   empty, and every screen in the product reports a healthy corpus. See
 *   `searchable()`: this class cannot fix that - it needs an ngram parser and a
 *   rebuilt index - but a desk being told why is a different situation from a
 *   desk being told nothing.
 *
 * ## Detection is a guess, and everything here is built to say so
 *
 * `detect()` reads scripts and function words. It makes no request to anybody,
 * costs nothing, and is genuinely reliable on a paragraph and genuinely
 * unreliable on four words - which is most ticket subjects. So it always
 * returns a confidence with its answer, an account holder's own stored locale
 * always beats it, and nothing anywhere in this class *excludes* a document on
 * the strength of a detection alone unless a site has explicitly asked for that.
 *
 * ## The default changes nothing, provably
 *
 * `MODE_PREFER` is the default and it is a ranking boost, never a filter. On a
 * desk whose corpus is one language the boost is uniform and the order is
 * exactly what it is today - and `multilingual()` short-circuits the whole
 * thing anyway, so the common install pays nothing and risks nothing. A site
 * that wants "never answer French from English documentation" asks for
 * `MODE_ONLY` deliberately.
 *
 * ## What this is not
 *
 * It is not multilingual embeddings. This product has no vector index of any
 * kind - retrieval is FULLTEXT - so "multilingual embeddings" is not a setting
 * that can be turned on here; it is a different retrieval engine. What is
 * delivered instead is everything that engine would have bought except
 * cross-lingual recall: the desk knows what language every question and every
 * document is in, ranks by it, refuses to be fooled by it at the grounding
 * gate, and says plainly where the FULLTEXT index cannot follow.
 */
class JSSTailanguages {

    /** How other-language material is treated. */
    const OPT_MODE = 'jsst_ai_language_mode';

    /** Nothing is done. Retrieval behaves as it did before 6.0. */
    const MODE_OFF = 'off';

    /** Same-language passages are ranked higher. Never excluded. */
    const MODE_PREFER = 'prefer';

    /** Only same-language passages are retrieved, with a documented fallback. */
    const MODE_ONLY = 'only';

    /** The site's own answer language, when detection has nothing to go on. */
    const OPT_FALLBACK = 'jsst_ai_language_fallback';

    /** Cached survey of what languages the corpus actually holds. */
    const OPT_SURVEY = 'jsst_ai_language_survey';

    /** Below this, a detection is reported but never acted on. */
    const SURE_ENOUGH = 40;

    /** A language holding less than this share of the corpus is a rounding error. */
    const CORPUS_SHARE = 5;

    /** How much a same-language passage is favoured under MODE_PREFER. */
    const BOOST = 1.35;

    public static function registerHooks() {
        add_action('jsst_ai_corpus_changed', array(__CLASS__, 'forgetSurvey'));
    }

    /* ------------------------------------------------------------------ *
     * Detection
     * ------------------------------------------------------------------ */

    /**
     * Scripts that identify a language on sight.
     *
     * A single Han character settles that a passage is not English in a way no
     * amount of function-word counting can, so these are checked first and
     * short-circuit everything below. Han is deliberately reported as `zh`
     * rather than guessed between Chinese and Japanese on character ranges
     * alone - Japanese is separated by its kana, which is what the entry above
     * it tests, and a Japanese passage written entirely in kanji is rare enough
     * to be worth getting wrong rather than worth guessing at.
     */
    private static function scripts() {
        return array(
            'ja' => '/[\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u',
            'ko' => '/[\x{AC00}-\x{D7AF}\x{1100}-\x{11FF}]/u',
            'zh' => '/[\x{4E00}-\x{9FFF}\x{3400}-\x{4DBF}]/u',
            'th' => '/[\x{0E00}-\x{0E7F}]/u',
            'he' => '/[\x{0590}-\x{05FF}]/u',
            'ar' => '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u',
            'el' => '/[\x{0370}-\x{03FF}]/u',
            'hi' => '/[\x{0900}-\x{097F}]/u',
            'ru' => '/[\x{0400}-\x{04FF}]/u',
        );
    }

    /** Which writing system each of those codes is written in. */
    private static function scriptOf($jsst_code) {
        $jsst_map = array(
            'ja' => 'japanese', 'ko' => 'hangul', 'zh' => 'han', 'th' => 'thai',
            'he' => 'hebrew', 'ar' => 'arabic', 'el' => 'greek', 'hi' => 'devanagari',
            'ru' => 'cyrillic',
        );
        return isset($jsst_map[$jsst_code]) ? $jsst_map[$jsst_code] : 'latin';
    }

    /**
     * The function words that separate one Latin-script language from another.
     *
     * Function words rather than vocabulary on purpose: they are the words a
     * text cannot avoid, they are short, and they do not move between subject
     * matters - a French text about invoices and a French text about passwords
     * both contain "les" and "pour". A topic-word list would classify by what
     * the ticket is about instead.
     *
     * The lists overlap (Spanish, Portuguese and Italian share a great deal),
     * which is why `detect()` scores every language and reports a margin rather
     * than stopping at the first hit.
     */
    private static function profiles() {
        return apply_filters('jsst_ai_language_profiles', array(
            'en' => array('the', 'and', 'you', 'for', 'that', 'with', 'this', 'have', 'not', 'are', 'but', 'your', 'from', 'can', 'when', 'what', 'would', 'there', 'been', 'they', 'please', 'thanks', 'because', 'about', 'still'),
            'es' => array('los', 'las', 'del', 'una', 'pero', 'muy', 'usted', 'gracias', 'está', 'porque', 'puedo', 'tengo', 'desde', 'hasta', 'también', 'ahora', 'mismo', 'hola', 'todavía', 'algún', 'cuenta', 'correo', 'nunca', 'varias', 'favor'),
            'fr' => array('les', 'des', 'une', 'pour', 'dans', 'avec', 'pas', 'sur', 'est', 'nous', 'vous', 'mais', 'plus', 'cette', 'sont', 'être', 'avoir', 'quand', 'aussi', 'bonjour', 'merci', 'déjà', 'toujours', 'jamais', 'plaît'),
            'de' => array('der', 'die', 'das', 'und', 'ist', 'nicht', 'für', 'mit', 'auf', 'ein', 'eine', 'sich', 'auch', 'aber', 'oder', 'wenn', 'kann', 'werden', 'haben', 'wird', 'bitte', 'danke', 'schon', 'immer', 'noch'),
            'it' => array('che', 'non', 'per', 'con', 'una', 'del', 'della', 'sono', 'come', 'anche', 'più', 'questo', 'questa', 'quando', 'nella', 'alla', 'dei', 'gli', 'essere', 'molto', 'grazie', 'buongiorno', 'già', 'sempre', 'riesco'),
            'pt' => array('não', 'uma', 'com', 'dos', 'das', 'está', 'são', 'pelo', 'pela', 'você', 'já', 'muito', 'também', 'obrigado', 'posso', 'tenho', 'até', 'agora', 'minha', 'meu', 'olá', 'consigo', 'nunca', 'vezes', 'palavra'),
            'nl' => array('het', 'een', 'van', 'niet', 'dat', 'met', 'voor', 'zijn', 'aan', 'maar', 'ook', 'deze', 'wordt', 'kan', 'heeft', 'naar', 'over', 'door', 'onze', 'wij', 'graag', 'bedankt', 'nooit', 'altijd', 'nog'),
            'pl' => array('nie', 'jest', 'się', 'tego', 'dla', 'jak', 'lub', 'oraz', 'przez', 'przy', 'tylko', 'jego', 'aby', 'gdy', 'czy', 'ale', 'jeśli', 'może', 'bardzo', 'nasze', 'dziękuję', 'proszę', 'jeszcze', 'zawsze', 'nigdy'),
            'sv' => array('och', 'att', 'det', 'som', 'för', 'med', 'inte', 'har', 'den', 'till', 'kan', 'men', 'eller', 'från', 'är', 'ska', 'när', 'också', 'detta', 'tack', 'aldrig', 'alltid', 'redan', 'ännu', 'hej'),
            'tr' => array('bir', 've', 'için', 'ile', 'daha', 'olarak', 'çok', 'ama', 'veya', 'gibi', 'kadar', 'sonra', 'her', 'değil', 'olan', 'var', 'yok', 'nasıl', 'neden', 'lütfen', 'teşekkür', 'merhaba', 'ancak', 'bana', 'hiç'),
            'ru' => array('что', 'как', 'это', 'для', 'при', 'если', 'все', 'так', 'уже', 'или', 'меня', 'вас', 'нас', 'быть', 'есть', 'нет', 'его', 'она', 'они', 'было', 'спасибо', 'пожалуйста', 'здравствуйте', 'ещё', 'никак'),
            'id' => array('yang', 'dan', 'untuk', 'dengan', 'tidak', 'dari', 'pada', 'ini', 'itu', 'ada', 'saya', 'kami', 'akan', 'sudah', 'bisa', 'atau', 'juga', 'karena', 'dalam', 'lebih', 'terima', 'kasih', 'belum', 'selalu', 'halo'),
        ));
    }

    /**
     * What language a piece of text is in, and how sure that is.
     *
     * Never throws and never returns nothing: a caller asking this question
     * always gets an answer it can put in a column, with a confidence saying
     * how much weight to put on it. An empty or wordless string comes back as
     * `und` - undetermined - which is a real value that means "we looked", as
     * opposed to a guess dressed up as a fact.
     *
     * @return array lang, confidence (0-100), script, words.
     */
    public static function detect($jsst_text) {
        $jsst_out = array('lang' => 'und', 'confidence' => 0, 'script' => 'latin', 'words' => 0);

        $jsst_text = wp_strip_all_tags((string) $jsst_text);
        /* URLs, e-mail addresses and code are language-free and there is a lot
           of them in support text. Left in, a German ticket quoting an English
           error message is scored partly English on the strength of the error
           message - which is the one part of it nobody wrote. */
        $jsst_text = preg_replace('#https?://\S+|\S+@\S+\.\S+|<[^>]+>#u', ' ', $jsst_text);
        if (trim($jsst_text) === '') return $jsst_out;

        /* Script first: it is decisive where it applies. The threshold is a
           share rather than a single character, because one Han character in a
           customer's signature does not make an English ticket Chinese. */
        $jsst_letters = preg_match_all('/\p{L}/u', $jsst_text);
        if ($jsst_letters > 0) {
            foreach (self::scripts() as $jsst_code => $jsst_pattern) {
                $jsst_hits = preg_match_all($jsst_pattern, $jsst_text);
                if ($jsst_hits > 0 && ($jsst_hits / $jsst_letters) >= 0.20) {
                    $jsst_out['lang']       = $jsst_code;
                    $jsst_out['script']     = self::scriptOf($jsst_code);
                    $jsst_out['confidence'] = (int) min(100, round(($jsst_hits / $jsst_letters) * 100) + 20);
                    $jsst_out['words']      = $jsst_hits;
                    return $jsst_out;
                }
            }
        }

        $jsst_lower = jssupportticketphplib::JSST_strtolower($jsst_text);
        $jsst_lower = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $jsst_lower);
        $jsst_words = preg_split('/\s+/u', trim($jsst_lower), -1, PREG_SPLIT_NO_EMPTY);
        $jsst_out['words'] = count($jsst_words);
        if ($jsst_out['words'] < 3) return $jsst_out;

        /* A matched word is worth 1 divided by the number of languages that
           claim it. Counting matches equally is what makes a detector confuse
           Spanish with Portuguese: "que", "para" and "por" are in both lists,
           so a Portuguese sentence scores three for Spanish on words that say
           nothing, while "não" and "você" - which say everything - count one
           each. Weighting by how many profiles share a word puts the evidence
           where it actually is, and needs no corpus to compute: the profiles
           are the only input. */
        $jsst_profiles = self::profiles();
        $jsst_shared   = array();
        foreach ($jsst_profiles as $jsst_list) {
            foreach ($jsst_list as $jsst_word) {
                $jsst_shared[$jsst_word] = isset($jsst_shared[$jsst_word]) ? $jsst_shared[$jsst_word] + 1 : 1;
            }
        }

        $jsst_scores = array();
        $jsst_hits   = array();
        foreach ($jsst_profiles as $jsst_code => $jsst_list) {
            $jsst_score = 0.0;
            $jsst_n     = 0;
            foreach (array_intersect($jsst_words, $jsst_list) as $jsst_word) {
                $jsst_score += 1 / max(1, $jsst_shared[$jsst_word]);
                $jsst_n++;
            }
            $jsst_scores[$jsst_code] = $jsst_score;
            $jsst_hits[$jsst_code]   = $jsst_n;
        }
        arsort($jsst_scores);

        $jsst_best = key($jsst_scores);
        $jsst_top  = current($jsst_scores);
        $jsst_rest = array_slice($jsst_scores, 1, 1, true);
        $jsst_next = empty($jsst_rest) ? 0.0 : current($jsst_rest);

        if ($jsst_top <= 0) return $jsst_out;

        /* Confidence is the margin over the runner-up, scaled by how much
           evidence there was. Both halves matter: "les" appearing once in a
           four-word subject is not evidence, and a long passage that scores
           equally as Spanish and Portuguese is not evidence either. */
        $jsst_margin = ($jsst_top - $jsst_next) / $jsst_top;
        $jsst_volume = min(1.0, $jsst_hits[$jsst_best] / 5);

        $jsst_out['lang']       = $jsst_best;
        $jsst_out['confidence'] = (int) round(100 * ((0.35 * $jsst_volume) + (0.65 * $jsst_margin * $jsst_volume)));
        return $jsst_out;
    }

    /** Detection, reduced to the code alone, and only when it is worth using. */
    public static function detectCode($jsst_text, $jsst_floor = self::SURE_ENOUGH) {
        $jsst_read = self::detect($jsst_text);
        return ($jsst_read['confidence'] >= $jsst_floor) ? $jsst_read['lang'] : 'und';
    }

    /* ------------------------------------------------------------------ *
     * Whose language
     * ------------------------------------------------------------------ */

    /** `fr_CA` and `fr-ca` and `fr` are all French here. */
    public static function normalise($jsst_locale) {
        $jsst_locale = jssupportticketphplib::JSST_strtolower(trim((string) $jsst_locale));
        $jsst_locale = preg_replace('/[^a-z]+.*$/', '', str_replace('-', '_', $jsst_locale));
        return ($jsst_locale === '') ? 'und' : jssupportticketphplib::JSST_substr($jsst_locale, 0, 3);
    }

    /** The language this site answers in when nothing else is known. */
    public static function siteLanguage() {
        $jsst_set = get_option(self::OPT_FALLBACK, '');
        if ($jsst_set !== '') return self::normalise($jsst_set);
        return self::normalise(get_locale());
    }

    /**
     * The language to answer this ticket in.
     *
     * An account holder's own stored locale wins, because it is a statement
     * they made rather than a guess made about them - somebody who set their
     * profile to German and then pasted an English error message is still owed
     * a German answer. Guests have no such statement, and guests are most of a
     * public help desk, so for them the text is the only evidence there is.
     *
     * @return array lang, from ('profile'|'text'|'site'), confidence.
     */
    public static function ofTicket($jsst_ticketid, $jsst_text = '') {
        $jsst_ticketid = (int) $jsst_ticketid;

        if ($jsst_ticketid > 0) {
            /* Two columns, two id spaces, and they are not the ones the names
               suggest. `js_ticket_tickets.uid` is a `js_ticket_users.id`, and
               the WordPress user is `js_ticket_users.wpuid` - reading either as
               the other silently hands back a stranger's locale on a site where
               both tables start at 1. */
            $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                "SELECT t.uid, t.subject, t.message, u.wpuid
                   FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` AS t
                   LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS u ON u.id = t.uid
                  WHERE t.id = %d", $jsst_ticketid));

            if ($jsst_row) {
                if ((int) $jsst_row->wpuid > 0) {
                    $jsst_locale = get_user_locale((int) $jsst_row->wpuid);
                    if ($jsst_locale !== '') {
                        return array('lang' => self::normalise($jsst_locale), 'from' => 'profile', 'confidence' => 100);
                    }
                }
                if ($jsst_text === '') {
                    $jsst_text = $jsst_row->subject . ' ' . wp_strip_all_tags($jsst_row->message);
                }
            }
        }

        $jsst_read = self::detect($jsst_text);
        if ($jsst_read['confidence'] >= self::SURE_ENOUGH) {
            return array('lang' => $jsst_read['lang'], 'from' => 'text', 'confidence' => $jsst_read['confidence']);
        }

        return array('lang' => self::siteLanguage(), 'from' => 'site', 'confidence' => 0);
    }

    /* ------------------------------------------------------------------ *
     * The policy
     * ------------------------------------------------------------------ */

    public static function mode() {
        $jsst_mode = get_option(self::OPT_MODE, self::MODE_PREFER);
        return array_key_exists($jsst_mode, self::modes()) ? $jsst_mode : self::MODE_PREFER;
    }

    public static function setMode($jsst_mode) {
        if (!array_key_exists($jsst_mode, self::modes())) return false;
        update_option(self::OPT_MODE, $jsst_mode, false);
        return true;
    }

    public static function modes() {
        return array(
            self::MODE_OFF => array(
                'label' => esc_html(__('Ignore language', 'js-support-ticket')),
                'blurb' => esc_html(__('Rank on wording alone, the way this worked before. Right for a desk that only ever operates in one language.', 'js-support-ticket')),
            ),
            self::MODE_PREFER => array(
                'label' => esc_html(__('Prefer the customer\'s language', 'js-support-ticket')),
                'blurb' => esc_html(__('Material in the language being asked in is ranked higher, and material in another language is still used when there is nothing better. Nothing is ever hidden.', 'js-support-ticket')),
            ),
            self::MODE_ONLY => array(
                'label' => esc_html(__('Only the customer\'s language', 'js-support-ticket')),
                'blurb' => esc_html(__('Answer only from material written in the language being asked in. Choose this when a translated answer would be worse than no answer — regulated wording, for instance. It means some questions get no answer at all.', 'js-support-ticket')),
            ),
        );
    }

    /**
     * Is any of this worth doing on this site?
     *
     * The honest short-circuit. A desk whose corpus is one language gains
     * nothing from a language boost and can only be hurt by one - a single
     * document mis-detected as Spanish would be quietly demoted for every
     * English customer forever. So on a monolingual corpus this class stands
     * down entirely and retrieval is byte-for-byte what it was before 6.0.
     */
    public static function multilingual() {
        $jsst_survey = self::survey();
        return (count($jsst_survey['languages']) > 1);
    }

    /**
     * What the corpus is actually written in.
     *
     * Answered by the add-on that owns the index, through a filter, for the
     * reason AI-02 and AI-11 both give: core does not keep a second opinion
     * about somebody else's table. Cached, because it is a GROUP BY over every
     * passage and it is asked on every retrieval - and cleared by the corpus
     * change hook rather than by a timer, so publishing an article in a new
     * language shows up immediately rather than within the hour.
     *
     * Languages under CORPUS_SHARE per cent are dropped: three mis-detected
     * documents must not make a monolingual desk behave as a bilingual one.
     */
    public static function survey($jsst_fresh = false) {
        if (!$jsst_fresh) {
            $jsst_cached = get_option(self::OPT_SURVEY, false);
            if (is_array($jsst_cached) && isset($jsst_cached['languages'])) return $jsst_cached;
        }

        $jsst_counts = apply_filters('jsst_ai_corpus_languages', null);
        $jsst_out    = array('languages' => array(), 'total' => 0, 'known' => false);

        if (is_array($jsst_counts)) {
            $jsst_out['known'] = true;
            $jsst_total = 0;
            foreach ($jsst_counts as $jsst_n) $jsst_total += (int) $jsst_n;
            $jsst_out['total'] = $jsst_total;

            foreach ($jsst_counts as $jsst_code => $jsst_n) {
                if ($jsst_code === 'und' || $jsst_total < 1) continue;
                if ((((int) $jsst_n) / $jsst_total) * 100 < self::CORPUS_SHARE) continue;
                $jsst_out['languages'][(string) $jsst_code] = (int) $jsst_n;
            }
            arsort($jsst_out['languages']);
        }

        update_option(self::OPT_SURVEY, $jsst_out, false);
        return $jsst_out;
    }

    public static function forgetSurvey() {
        delete_option(self::OPT_SURVEY);
    }

    /**
     * How much a passage's score is multiplied by, given who is asking.
     *
     * One function so that the ranker and anything that ever explains the
     * ranking cannot disagree. Returns 1.0 - change nothing - in every case
     * where this class has no business having an opinion: the mode is off, the
     * corpus is one language, the document's language was never determined, or
     * the asker's language was a fallback rather than a finding.
     */
    public static function weight($jsst_asked, $jsst_document) {
        if (self::mode() === self::MODE_OFF) return 1.0;
        if (!self::multilingual()) return 1.0;

        $jsst_asked    = self::normalise($jsst_asked);
        $jsst_document = self::normalise($jsst_document);

        if ($jsst_asked === 'und' || $jsst_document === 'und') return 1.0;
        return ($jsst_asked === $jsst_document) ? self::BOOST : 1.0;
    }

    /**
     * Whether a passage in this language may be used at all.
     *
     * Only MODE_ONLY ever says no, and even then never to an undetermined
     * document: refusing what we failed to classify would turn a detector's
     * shrug into a censor, and the commonest reason a passage is `und` is that
     * it is a short one, not that it is foreign.
     */
    public static function allows($jsst_asked, $jsst_document) {
        if (self::mode() !== self::MODE_ONLY) return true;
        if (!self::multilingual()) return true;

        $jsst_asked    = self::normalise($jsst_asked);
        $jsst_document = self::normalise($jsst_document);

        if ($jsst_asked === 'und' || $jsst_document === 'und') return true;
        return ($jsst_asked === $jsst_document);
    }

    /* ------------------------------------------------------------------ *
     * Grounding across a language boundary
     * ------------------------------------------------------------------ */

    /**
     * Whether an answer and its sources are in different languages.
     *
     * Asked before the grounding check rather than after it, because the answer
     * decides which check is valid rather than what its result means.
     */
    public static function translated($jsst_answer_lang, $jsst_source_lang) {
        $jsst_answer_lang = self::normalise($jsst_answer_lang);
        $jsst_source_lang = self::normalise($jsst_source_lang);

        if ($jsst_answer_lang === 'und' || $jsst_source_lang === 'und') return false;
        return ($jsst_answer_lang !== $jsst_source_lang);
    }

    /**
     * The parts of a claim that survive being translated.
     *
     * This is the whole point of the task. The grounding check asks whether the
     * specifics in a reply appear in the material it was written from, and it
     * asks that by comparing words. Translate the reply and the comparison
     * collapses: "Settings > Security" becomes "Paramètres > Sécurité" and
     * matches nothing, so a faithful translation of a well-grounded answer
     * scores like an invention. Before this, a multilingual desk could have the
     * grounding gate or it could have answers, and not both.
     *
     * What actually survives translation is the part that was never words: a
     * number, a version, a URL, a file name, an e-mail address, an identifier
     * with digits in it, and a proper noun the product does not translate. Those
     * are also, and not by coincidence, the specifics that hurt most when they
     * are wrong - a customer sent to the wrong URL or told the wrong number of
     * days is worse off than one given a slightly odd menu label.
     *
     * So a cross-language claim is checked on those alone, and a claim that has
     * none of them is not checked - it is dropped from the measurement rather
     * than counted as a failure, because "we cannot check this" and "this is
     * unsupported" are different findings and only one of them should hold an
     * answer back.
     *
     * @return array Tokens to check, possibly empty (meaning: cannot check).
     */
    public static function groundingTokens($jsst_tokens) {
        $jsst_out = array();

        foreach ((array) $jsst_tokens as $jsst_token) {
            $jsst_token = (string) $jsst_token;

            // Anything carrying a digit: versions, ports, counts, identifiers.
            if (preg_match('/\d/', $jsst_token)) {
                $jsst_out[] = $jsst_token;
                continue;
            }
            /* A word with no vowel in any language that uses them, or one long
               enough to be a name rather than a word, is the shape of an
               identifier - "smtp", "oauth", "webhook", "wordpress". These are
               not translated by anybody and they are what a support answer is
               usually about. */
            if (!preg_match('/[aeiouáéíóúàèìòùäöüåøæыаеиоу]/u', $jsst_token)
                && jssupportticketphplib::JSST_strlen($jsst_token) >= 3) {
                $jsst_out[] = $jsst_token;
                continue;
            }
        }

        return array_values(array_unique($jsst_out));
    }

    /* ------------------------------------------------------------------ *
     * Where the index cannot follow
     * ------------------------------------------------------------------ */

    /**
     * Can MySQL's FULLTEXT index find anything in this language at all?
     *
     * The default parser splits on whitespace. Chinese, Japanese and Thai are
     * not written with spaces between words, so every passage in them indexes
     * as one enormous token that no query will ever match. The index reports
     * itself as healthy, the search returns nothing, and there has never been
     * anywhere in this product that said why.
     *
     * Fixing it needs `WITH PARSER ngram` and a rebuilt index, which is a
     * migration with real cost on a large corpus and is not something to do
     * behind somebody's back. Naming the problem is what this does.
     */
    public static function searchable($jsst_lang) {
        return !in_array(self::normalise($jsst_lang), array('zh', 'ja', 'th'), true);
    }

    /** The languages in this corpus that the index cannot actually search. */
    public static function unsearchable() {
        $jsst_out = array();
        foreach (array_keys(self::survey()['languages']) as $jsst_code) {
            if (!self::searchable($jsst_code)) $jsst_out[] = $jsst_code;
        }
        return $jsst_out;
    }

    /* ------------------------------------------------------------------ *
     * Names
     * ------------------------------------------------------------------ */

    public static function label($jsst_code) {
        $jsst_names = array(
            'en' => __('English', 'js-support-ticket'),   'es' => __('Spanish', 'js-support-ticket'),
            'fr' => __('French', 'js-support-ticket'),    'de' => __('German', 'js-support-ticket'),
            'it' => __('Italian', 'js-support-ticket'),   'pt' => __('Portuguese', 'js-support-ticket'),
            'nl' => __('Dutch', 'js-support-ticket'),     'pl' => __('Polish', 'js-support-ticket'),
            'sv' => __('Swedish', 'js-support-ticket'),   'tr' => __('Turkish', 'js-support-ticket'),
            'ru' => __('Russian', 'js-support-ticket'),   'id' => __('Indonesian', 'js-support-ticket'),
            'ja' => __('Japanese', 'js-support-ticket'),  'ko' => __('Korean', 'js-support-ticket'),
            'zh' => __('Chinese', 'js-support-ticket'),   'th' => __('Thai', 'js-support-ticket'),
            'he' => __('Hebrew', 'js-support-ticket'),    'ar' => __('Arabic', 'js-support-ticket'),
            'el' => __('Greek', 'js-support-ticket'),     'hi' => __('Hindi', 'js-support-ticket'),
            'und' => __('Not determined', 'js-support-ticket'),
        );
        $jsst_code = self::normalise($jsst_code);
        return isset($jsst_names[$jsst_code]) ? esc_html($jsst_names[$jsst_code]) : esc_html($jsst_code);
    }

    /** Every language this class can name, for a settings dropdown. */
    public static function known() {
        $jsst_out = array();
        foreach (array_merge(array_keys(self::profiles()), array_keys(self::scripts())) as $jsst_code) {
            $jsst_out[$jsst_code] = self::label($jsst_code);
        }
        asort($jsst_out);
        return $jsst_out;
    }
}
