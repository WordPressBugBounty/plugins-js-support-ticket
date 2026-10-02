<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

if (class_exists('JSSTcannedlibrary')) {
    return;
}

/**
 * Keeping a canned response library worth having. (Roadmap 6.0-KB-02)
 *
 * Canned responses start as a good idea and become a liability by the same
 * route every time: forty of them, nobody remembers which is current, two say
 * opposite things about refunds, and the one everybody actually uses is the
 * fifth in an alphabetical list. Then 6.0-AI-02 made them an AI source, which
 * means the stale ones are not merely unhelpful to agents - they get quoted at
 * customers.
 *
 * So this adds the five things that make a library maintainable rather than
 * merely large, and each is a column plus the rule that goes with it:
 *
 *   **Folders.** Not categories with their own table - a name on the row. A
 *   folder is a label somebody types once, and giving it an id, a screen and a
 *   parent means maintaining a taxonomy for something whose whole job is to be
 *   findable in a dropdown.
 *
 *   **A team.** Zero means everybody. The billing team's refund wording is not
 *   something the onboarding team should be sending, and hiding it is kinder
 *   than trusting everyone to know which is theirs.
 *
 *   **Approval.** A canned response is text that goes to customers under the
 *   company's name, which is exactly the thing 6.0-KB-01 argued should not be
 *   publishable by one person on a Friday afternoon. Unapproved ones are not
 *   quoted by the AI and are marked for agents.
 *
 *   **A language**, detected rather than asked for, so an agent answering in
 *   French is not scrolling past thirty English ones. (Roadmap 6.0-AI-09)
 *
 *   **Use counting.** The only honest way to answer "can we delete this" - and
 *   the number that tells you the one everybody uses is the fifth in the list.
 *
 * ## Everything here is additive and fails open
 *
 * Every column is nullable with no default, and every rule reads a missing
 * value as permissive: an unapproved-because-nobody-ever-approved-it response
 * is treated as approved, a response with no team is everybody's. An upgrade
 * that hid half a desk's canned responses would be indistinguishable from a
 * bug, and would be discovered by an agent mid-reply.
 */
class JSSTcannedlibrary {

    const TABLE          = 'js_ticket_department_message_premade';
    const SCHEMA_VERSION = '6.0.1';
    const OPT_SCHEMA     = 'jsst_canned_library_schema';

    /** Where use counts live: id => count. Small, and not worth a table. */
    const OPT_USES = 'jsst_canned_uses';

    /** How many folders one desk can sensibly have on a dropdown. */
    const MAX_FOLDERS = 60;

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);
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
     * Schema
     * ------------------------------------------------------------------ */

    /**
     * The five columns, each added only if absent.
     *
     * Outside a version guard for the reason KB-01 records: this table belongs
     * to a module that may be installed after core was upgraded, at which point
     * a stored schema version would say "already done" about a table that did
     * not exist when it was written.
     */
    public static function ensureSchema() {
        if (!self::available()) return;

        /* The stored version as well as the column probe. `governed()` looks
           for *one* column, so a later release adding a sixth would find the
           table already governed and never add it - the trap KB-01 hit and
           the reason both classes check both. */
        if (self::governed() && get_option(self::OPT_SCHEMA, '') === self::SCHEMA_VERSION) {
            return;
        }

        if (!class_exists('JSSTschemaguard')) return;

        /* One request migrates and the others leave without recording a version
           they did not write. See `JSSTschemaguard::lock()`: two admin requests
           arriving together each probed this table before either altered it, and
           the loser wrote six `Duplicate column name` errors into the log of a
           site whose schema was already correct. */
        if (!JSSTschemaguard::lock(self::OPT_SCHEMA)) {
            return;
        }

        $jsst_wanted = array(
            'folder'     => "VARCHAR(80) NULL",
            'teamid'     => "BIGINT(20) NULL",
            'approved'   => "TINYINT(1) NULL",
            'approvedby' => "BIGINT(20) NULL",
            'language'   => "VARCHAR(8) NULL",
            /* Which response this one is a translation of. Same shape as
               KB-01's articles: a pointer to the original, and the original
               points at nothing. (Roadmap 6.0-KB-02) */
            'translationof' => "BIGINT(20) NULL",
        );

        JSSTschemaguard::addColumns(self::TABLE, $jsst_wanted);

        delete_transient('jsst_canned_governed');
        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);

        JSSTschemaguard::unlock(self::OPT_SCHEMA);
    }

    /** Have the columns been added on this site yet? */
    public static function governed() {
        $jsst_have = get_transient('jsst_canned_governed');
        if ($jsst_have !== false) return ($jsst_have === 'yes');

        if (!self::available()) {
            set_transient('jsst_canned_governed', 'no', HOUR_IN_SECONDS);
            return false;
        }

        /* Every column this answer speaks for, not just the first one.
           (Roadmap 6.5-ECO-01)

           It asked for `folder` alone and was then used to gate the `approved`
           clause in `JSSTaisources` and in the AI retriever - two columns that
           ship together on a clean upgrade and do not on a site being migrated
           one add-on at a time. A desk with `folder` and without `approved` was
           told it was governed, and every admin page load wrote
           "Unknown column 'approved' in 'where clause'" into the log. */
        $jsst_needed = array('folder', 'approved');
        $jsst_have = 'yes';
        foreach ($jsst_needed as $jsst_col) {
            $jsst_cols = jssupportticket::$_db->get_results(
                "SHOW COLUMNS FROM `" . self::table() . "` LIKE '" . esc_sql($jsst_col) . "'");
            if (empty($jsst_cols)) { $jsst_have = 'no'; break; }
        }
        set_transient('jsst_canned_governed', $jsst_have, HOUR_IN_SECONDS);
        return ($jsst_have === 'yes');
    }

    /* ------------------------------------------------------------------ *
     * Folders
     * ------------------------------------------------------------------ */

    /** Every folder somebody has typed, with how many are in it. */
    public static function folders() {
        if (!self::governed()) return array();

        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT folder, COUNT(*) AS jsst_n FROM `" . self::table() . "`
              WHERE folder IS NOT NULL AND folder <> ''
              GROUP BY folder ORDER BY folder ASC LIMIT " . self::MAX_FOLDERS);

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[(string) $jsst_row->folder] = (int) $jsst_row->jsst_n;
        }
        return $jsst_out;
    }

    /** A folder name, reduced to something worth storing. */
    public static function cleanFolder($jsst_folder) {
        $jsst_folder = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string) $jsst_folder)));
        return jssupportticketphplib::JSST_substr($jsst_folder, 0, 80);
    }

    /* ------------------------------------------------------------------ *
     * Who may use one
     * ------------------------------------------------------------------ */

    /**
     * May this agent use this canned response?
     *
     * Team scoping, and nothing else pretending to be a permission. A response
     * with no team is everybody's, which is what every existing one is - so
     * this only ever narrows a library somebody has deliberately divided up.
     */
    public static function visibleTo($jsst_row, $jsst_staffid) {
        if (!self::governed()) return true;

        $jsst_teamid = is_object($jsst_row)
            ? (isset($jsst_row->teamid) ? (int) $jsst_row->teamid : 0) : (int) $jsst_row;
        if ($jsst_teamid < 1) return true;

        if (!class_exists('JSSTteams')) return true;

        foreach ((array) JSSTteams::teamsFor((int) $jsst_staffid) as $jsst_team) {
            $jsst_id = is_object($jsst_team) ? (int) $jsst_team->id : (int) $jsst_team;
            if ($jsst_id === $jsst_teamid) return true;
        }
        return false;
    }

    /**
     * The SQL half of the same question.
     *
     * Applied in the query the picker runs, so a response an agent may not use
     * does not take a place in a list capped at twenty - AI-02's reason,
     * reached again from a different direction.
     */
    public static function teamClause($jsst_staffid) {
        if (!self::governed() || !class_exists('JSSTteams')) return '';

        $jsst_ids = array();
        foreach ((array) JSSTteams::teamsFor((int) $jsst_staffid) as $jsst_team) {
            $jsst_ids[] = is_object($jsst_team) ? (int) $jsst_team->id : (int) $jsst_team;
        }

        if (empty($jsst_ids)) {
            return '(teamid IS NULL OR teamid = 0)';
        }
        return '(teamid IS NULL OR teamid = 0 OR teamid IN (' . implode(',', array_map('intval', $jsst_ids)) . '))';
    }

    /* ------------------------------------------------------------------ *
     * Approval
     * ------------------------------------------------------------------ */

    /**
     * Has somebody signed this off?
     *
     * NULL is yes, and that is the important half: every canned response that
     * existed before this shipped has no approval recorded, and treating that
     * as "not approved" would take a desk's whole library out of the AI corpus
     * and mark every one of them on the agents' picker overnight.
     */
    public static function approved($jsst_row) {
        if (!self::governed()) return true;

        $jsst_value = is_object($jsst_row)
            ? (isset($jsst_row->approved) ? $jsst_row->approved : null) : null;

        if (!is_object($jsst_row)) {
            $jsst_value = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT approved FROM `" . self::table() . "` WHERE id = %d", (int) $jsst_row));
        }

        if ($jsst_value === null || $jsst_value === '') return true;
        return ((int) $jsst_value === 1);
    }

    /** The clause that says the same thing. */
    public static function approvedClause() {
        if (!self::governed()) return '';
        return '(approved IS NULL OR approved = 1)';
    }

    public static function setApproved($jsst_id, $jsst_yes = true, $jsst_who = 0) {
        if (!self::governed()) return false;

        jssupportticket::$_db->update(self::table(),
            array('approved' => $jsst_yes ? 1 : 0,
                  'approvedby' => $jsst_who > 0 ? (int) $jsst_who : get_current_user_id()),
            array('id' => (int) $jsst_id), array('%d', '%d'), array('%d'));

        do_action('jsst_canned_approval', (int) $jsst_id, (bool) $jsst_yes);
        return true;
    }

    /** Everything waiting for somebody to sign it off. */
    public static function awaitingApproval($jsst_limit = 50) {
        if (!self::governed()) return array();

        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, title, folder, created FROM `" . self::table() . "`
              WHERE approved = 0 ORDER BY id DESC LIMIT %d", (int) $jsst_limit));
    }

    /* ------------------------------------------------------------------ *
     * Language
     * ------------------------------------------------------------------ */

    /**
     * Work out and record what language a response is written in.
     *
     * Detected rather than asked for: an agent writing one has better things to
     * do than tell a form what language they are typing in, and the detector
     * from 6.0-AI-09 is right often enough and honest when it is not.
     */
    public static function detectLanguage($jsst_id, $jsst_text = '') {
        if (!self::governed() || !class_exists('JSSTailanguages')) return '';

        if ($jsst_text === '') {
            $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                "SELECT title, answer FROM `" . self::table() . "` WHERE id = %d", (int) $jsst_id));
            if (!$jsst_row) return '';
            $jsst_text = $jsst_row->title . ' ' . wp_strip_all_tags($jsst_row->answer);
        }

        $jsst_lang = JSSTailanguages::detectCode($jsst_text);
        if ($jsst_lang === 'und') $jsst_lang = '';

        jssupportticket::$_db->update(self::table(), array('language' => $jsst_lang),
            array('id' => (int) $jsst_id), array('%s'), array('%d'));

        return $jsst_lang;
    }

    /* ------------------------------------------------------------------ *
     * Saying the same thing in another language
     * ------------------------------------------------------------------ */

    /**
     * Make a translated copy of a canned response.
     *
     * A **copy**, not a translated field on the original, and that is the
     * decision worth defending. A canned response is edited by whoever is on
     * the desk; storing five languages on one row means one person editing the
     * English quietly leaves four translations claiming something the desk no
     * longer says, with nothing on any screen showing the drift. Separate rows
     * pointing at an original can each be approved, dated and retired on their
     * own - and `translationsOf()` shows a writer that the original changed.
     *
     * Translation is the Copilot's existing action, so it is metered, logged
     * and permission-checked like every other model call rather than being a
     * second path to a vendor. The copy arrives **unapproved**: a machine
     * translation of the wording your company sends customers is exactly the
     * thing somebody should read before it goes out.
     *
     * @return int|WP_Error The new response's id.
     */
    public static function localise($jsst_id, $jsst_language) {
        if (!self::governed()) {
            return new WP_Error('jsst_canned_notready',
                esc_html(__('The canned response library is not set up on this site.', 'js-support-ticket')));
        }
        if (!class_exists('JSSTcopilot')) {
            return new WP_Error('jsst_canned_noengine',
                esc_html(__('No engine on this site can translate.', 'js-support-ticket')));
        }

        $jsst_language = trim(wp_strip_all_tags((string) $jsst_language));
        if ($jsst_language === '') {
            return new WP_Error('jsst_canned_nolanguage',
                esc_html(__('Name a language to translate into.', 'js-support-ticket')));
        }

        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE id = %d", (int) $jsst_id));
        if (!$jsst_row) {
            return new WP_Error('jsst_canned_gone',
                esc_html(__('There is no such canned response.', 'js-support-ticket')));
        }

        /* Translate the original, never a translation of one. Going through a
           second language is how a figure becomes approximate. */
        $jsst_originalid = (isset($jsst_row->translationof) && (int) $jsst_row->translationof > 0)
            ? (int) $jsst_row->translationof : (int) $jsst_row->id;

        if ($jsst_originalid !== (int) $jsst_row->id) {
            $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                "SELECT * FROM `" . self::table() . "` WHERE id = %d", $jsst_originalid));
            if (!$jsst_row) {
                return new WP_Error('jsst_canned_gone',
                    esc_html(__('The original of that response is gone.', 'js-support-ticket')));
            }
        }

        $jsst_result = JSSTcopilot::run('translate', 0, array(
            'language' => $jsst_language,
            'text'     => $jsst_row->title . "\n\n" . $jsst_row->answer,
        ));
        if (is_wp_error($jsst_result)) return $jsst_result;

        $jsst_text = isset($jsst_result['text']) ? trim((string) $jsst_result['text']) : '';
        if ($jsst_text === '') {
            return new WP_Error('jsst_canned_empty',
                esc_html(__('The engine returned nothing to save.', 'js-support-ticket')));
        }

        /* The first line back is the translated title, the rest is the body -
           which is how it was sent. Falling back to the original title rather
           than inventing one keeps a response findable even when the engine
           returned one undivided block. */
        $jsst_parts   = preg_split('/\r?\n\r?\n/', $jsst_text, 2);
        $jsst_title   = trim(wp_strip_all_tags($jsst_parts[0]));
        $jsst_answer  = isset($jsst_parts[1]) ? trim($jsst_parts[1]) : $jsst_text;

        if ($jsst_title === '' || jssupportticketphplib::JSST_strlen($jsst_title) > 125) {
            $jsst_title  = $jsst_row->title;
            $jsst_answer = $jsst_text;
        }

        jssupportticket::$_db->insert(self::table(), array(
            'departmentid'  => $jsst_row->departmentid,
            'title'         => jssupportticketphplib::JSST_substr($jsst_title, 0, 125),
            'answer'        => wp_kses_post($jsst_answer),
            'created'       => current_time('mysql'),
            'updated'       => current_time('mysql'),
            'status'        => 1,
            'folder'        => $jsst_row->folder,
            'teamid'        => $jsst_row->teamid,
            // Unapproved: a machine translation of what you send customers is
            // exactly the thing somebody should read first.
            'approved'      => 0,
            'translationof' => (int) $jsst_row->id,
        ));

        $jsst_new = (int) jssupportticket::$_db->insert_id;
        if ($jsst_new < 1) {
            return new WP_Error('jsst_canned_notsaved',
                esc_html(__('The translation could not be saved.', 'js-support-ticket')));
        }

        self::detectLanguage($jsst_new);
        do_action('jsst_canned_localised', (int) $jsst_row->id, $jsst_new, $jsst_language);
        return $jsst_new;
    }

    /**
     * Every language one response exists in, whichever of them you name.
     *
     * So a writer editing the English can see that the French was made from an
     * older version of it - which is the whole reason to record the link.
     */
    public static function translationsOf($jsst_id) {
        if (!self::governed()) return array();

        $jsst_id = (int) $jsst_id;
        $jsst_root = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT translationof FROM `" . self::table() . "` WHERE id = %d", $jsst_id));
        $jsst_root = ((int) $jsst_root > 0) ? (int) $jsst_root : $jsst_id;

        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, title, language, approved, updated FROM `" . self::table() . "`
              WHERE id = %d OR translationof = %d ORDER BY id ASC",
            $jsst_root, $jsst_root));
    }

    /* ------------------------------------------------------------------ *
     * Which ones actually get used
     * ------------------------------------------------------------------ */

    /**
     * Note that somebody inserted this one.
     *
     * An option rather than a table: this is one integer per canned response on
     * a desk that has a few dozen of them, and a table would mean a join on
     * every listing to show a number that is only ever read on one screen.
     * Bounded to the responses that still exist, so deleting one takes its
     * count with it rather than leaving a row nothing points at.
     */
    public static function used($jsst_id) {
        $jsst_id = (int) $jsst_id;
        if ($jsst_id < 1) return false;

        $jsst_uses = get_option(self::OPT_USES, array());
        if (!is_array($jsst_uses)) $jsst_uses = array();

        $jsst_uses[$jsst_id] = isset($jsst_uses[$jsst_id]) ? ((int) $jsst_uses[$jsst_id]) + 1 : 1;

        /* Trimmed here rather than on a schedule: this runs when an agent picks
           a response, which is the only moment the list can have grown, and it
           costs one query on a table with tens of rows. */
        if (count($jsst_uses) > 200) {
            $jsst_live = jssupportticket::$_db->get_col("SELECT id FROM `" . self::table() . "`");
            $jsst_uses = array_intersect_key($jsst_uses, array_flip(array_map('intval', (array) $jsst_live)));
        }

        update_option(self::OPT_USES, $jsst_uses, false);
        return true;
    }

    public static function uses($jsst_id = 0) {
        $jsst_uses = get_option(self::OPT_USES, array());
        if (!is_array($jsst_uses)) $jsst_uses = array();

        if ((int) $jsst_id > 0) {
            return isset($jsst_uses[(int) $jsst_id]) ? (int) $jsst_uses[(int) $jsst_id] : 0;
        }
        return $jsst_uses;
    }

    /**
     * The library, ranked by how much use it actually gets.
     *
     * The report that answers "can we delete this". Never-used responses are
     * included and counted as zero rather than left out, because they are the
     * whole point of looking.
     */
    public static function report($jsst_limit = 200) {
        if (!self::available()) return array();

        $jsst_uses = self::uses();
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` ORDER BY id DESC LIMIT %d", (int) $jsst_limit));

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_row->jsst_uses = isset($jsst_uses[(int) $jsst_row->id]) ? (int) $jsst_uses[(int) $jsst_row->id] : 0;
            $jsst_out[] = $jsst_row;
        }

        usort($jsst_out, array(__CLASS__, 'byUse'));
        return $jsst_out;
    }

    private static function byUse($jsst_a, $jsst_b) {
        return ($jsst_b->jsst_uses - $jsst_a->jsst_uses);
    }

    /** The numbers over the library screen. */
    public static function summary() {
        $jsst_out = array('total' => 0, 'unused' => 0, 'unapproved' => 0, 'folders' => 0, 'governed' => false);
        if (!self::available()) return $jsst_out;

        $jsst_out['total'] = (int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . self::table() . "`");

        $jsst_uses = self::uses();
        foreach (self::report(500) as $jsst_row) {
            if (empty($jsst_uses[(int) $jsst_row->id])) $jsst_out['unused']++;
        }

        if (!self::governed()) return $jsst_out;
        $jsst_out['governed'] = true;

        $jsst_out['unapproved'] = (int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . self::table() . "` WHERE approved = 0");
        $jsst_out['folders'] = count(self::folders());

        return $jsst_out;
    }
}
