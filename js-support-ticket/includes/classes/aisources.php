<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The register of what the AI is allowed to read. (Roadmap 6.0-AI-02)
 *
 * JSSTaipolicy answers "may this help desk use AI at all". This class answers
 * the other half of the same question — "and what may it answer from" — and the
 * two are deliberately separate objects. A lane is about where text goes; a
 * source is about where text comes from, and grounding quality is decided
 * entirely by the second. It is also the honest answer to the only question a
 * prospective customer ever asks about this feature: will it invent things
 * about my product. It cannot invent from content it was never allowed to see.
 *
 * Governance has two levels because sites need both:
 *
 *   The source type — knowledge base, FAQs, canned responses, WordPress posts,
 *   resolved tickets, scraped documentation. Approving a type is the coarse
 *   switch, and it already existed as the `instantresolve_sources` config row.
 *   That row is still the storage, deliberately: half a dozen readers across
 *   core and the add-on already consult it, and inventing a second option to
 *   mean the same thing is how two screens end up disagreeing about what the
 *   AI can see. This class is the only writer.
 *
 *   The individual document — one article, one FAQ, one page. Stored as
 *   exceptions in `js_ticket_ai_sources`, never as a full list: a site with
 *   four thousand posts must not need four thousand rows to say "all of them".
 *   Each type carries a mode saying which way its exceptions run — everything
 *   except what I excluded, or nothing except what I approved.
 *
 * Like the policy, this class can only ever refuse. eligible() returning true
 * means nothing has excluded that document; it does not mean the document
 * exists, is indexed, is any good, or that retrieval will find it.
 *
 * Enforcement is at retrieval, not at indexing, and that is on purpose. If the
 * index only held approved documents then approving one more would leave it
 * unfindable until the next backfill finished — an admin ticking a box and
 * watching nothing happen for twenty minutes. The index holds everything its
 * type allows; the filter is applied to every query that reads it.
 */
class JSSTaisources {

    /**
     * Per-document exceptions. Everything else is derived or configured.
     *
     * Named for what it holds - rules - rather than for sources, because the
     * add-on owns a table of crawl feeds and a screen called Content Sources,
     * and two tables a letter apart in the same `js_ticket_ai_` family is how
     * somebody debugging at 4am reads the wrong one.
     */
    const TABLE          = 'js_ticket_ai_rules';
    const SCHEMA_VERSION = '6.0.0';
    const OPT_SCHEMA     = 'jsst_ai_rules_schema';

    /** type => 'all'|'pick'. */
    const OPT_MODES = 'jsst_ai_source_modes';
    /** type => unix time of the last manual re-sync. */
    const OPT_SYNC = 'jsst_ai_source_sync';

    /**
     * Which source types are approved.
     *
     * A core option rather than a row in `js_ticket_config`, and that is the
     * point: the list used to live at `instantresolve_sources`, a row named
     * after an add-on, read by core, and editable from an add-on's settings tab
     * - so core's answer to "what may the AI read" was stored under somebody
     * else's name and could be written from two screens. It is core's decision,
     * so it is core's option, and this class is its only writer.
     *
     * The legacy row is still read once, by migrate(), so no site re-picks its
     * sources. It is not written back: one writer, one home.
     */
    const OPT_TYPES = 'jsst_ai_sources';

    /** The row the list lived in before 6.0-AI-02. Read once, never written. */
    const CFG_TYPES = 'instantresolve_sources';

    /** Everything the type holds, minus anything explicitly excluded. */
    const MODE_ALL = 'all';
    /** Nothing at all, except what has been explicitly approved. */
    const MODE_PICK = 'pick';

    const RULE_DENY  = 0;
    const RULE_ALLOW = 1;

    /** Rules memoised per request; every retrieval asks for them. */
    private static $jsst_rules = null;

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    /**
     * Self-healing rather than activation-only, for the reason the job queue
     * documents: a plugin updated in place never runs its activation hook, and
     * a governance table that is not there fails open — every document eligible,
     * silently, on exactly the sites that upgraded rather than installed fresh.
     */
    public static function ensureSchema() {
        if (!class_exists('JSSTschemaguard')) return;
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION,
                array(self::TABLE => array('sourcetype', 'sourceid', 'verdict')))) {
            return;
        }

        $jsst_table   = self::table();
        $jsst_charset = jssupportticket::$_db->get_charset_collate();

        /* One row per exception, and the unique key is the point of the table:
           a document is allowed or denied, never both, and a double-click on
           the same button must not leave two contradictory rows behind. */
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                sourcetype varchar(20) NOT NULL DEFAULT '',
                sourceid bigint(20) NOT NULL DEFAULT '0',
                verdict tinyint(1) NOT NULL DEFAULT '0',
                note varchar(255) NOT NULL DEFAULT '',
                setby bigint(20) NOT NULL DEFAULT '0',
                updated datetime DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY jsst_doc (sourcetype, sourceid),
                KEY jsst_type (sourcetype, verdict)
            ) " . $jsst_charset);

        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);
        self::$jsst_rules = null;
    }

    public static function table() {
        return jssupportticket::$_db->prefix . self::TABLE;
    }

    /**
     * Is there a rules table to read? A missing one means no exceptions.
     *
     * ensureSchema() records the version once it has created the table, so
     * that record is trusted first: this is asked on every suggestion search
     * while a customer types, and SHOW TABLES there was a schema query per
     * request. Only a site whose record is missing or older asks the database.
     */
    public static function available() {
        if (!isset(jssupportticket::$_db) || !is_object(jssupportticket::$_db)) return false;
        if (get_option(self::OPT_SCHEMA) === self::SCHEMA_VERSION) return true;
        $jsst_table = self::table();
        return (jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)
        ) === $jsst_table);
    }

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);
        add_action('admin_init', array(__CLASS__, 'migrateLegacyConfig'), 3);
    }

    /**
     * Rename the four core-owned settings that were named after Instant Resolve.
     *
     * `instantresolve_enable`, `_min_chars`, `_max_results` and `_analytics`
     * configure core's own suggestion search - they run with no add-on
     * installed at all - and they were named after an add-on that no longer
     * exists under that name. The rows are renamed rather than duplicated, so
     * there is never a moment where two rows configure one feature.
     *
     * It lives on this class, next to migrate(), because it is the same job:
     * this is where core takes over settings that used to be spelled with an
     * add-on's name. Self-healing on admin_init for the reason every schema
     * guard is - a plugin updated in place never runs its activation hook, and
     * a site that upgraded rather than installed would otherwise read defaults
     * for four settings it had deliberately changed.
     */
    public static function migrateLegacyConfig() {
        if (get_option('jsst_ai_config_renamed')) {
            return;
        }

        $jsst_names = array('enable', 'min_chars', 'max_results', 'analytics');
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_config';

        foreach ($jsst_names as $jsst_name) {
            $jsst_old = 'instantresolve_' . $jsst_name;
            $jsst_new = 'aiagent_' . $jsst_name;

            $jsst_have_old = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT COUNT(*) FROM `" . $jsst_table . "` WHERE configname = %s", $jsst_old));
            $jsst_have_new = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT COUNT(*) FROM `" . $jsst_table . "` WHERE configname = %s", $jsst_new));

            // Never rename onto a row that is already there: on a site where
            // activation seeded the new name first, the old row is the stale
            // one and is simply dropped.
            if ($jsst_have_old && !$jsst_have_new) {
                /* configfor moves with the name - it is what the Configurations
                   screen groups the field by, and a row still tagged with the
                   old product renders on a tab that no longer exists. addon
                   stays NULL: these four are core's, and tagging them to the
                   add-on would hand them to its deactivation sweep. */
                jssupportticket::$_db->query(jssupportticket::$_db->prepare(
                    "UPDATE `" . $jsst_table . "`
                        SET configname = %s, configfor = 'aiagent', addon = NULL
                      WHERE configname = %s",
                    $jsst_new, $jsst_old));
            } elseif ($jsst_have_old) {
                jssupportticket::$_db->query(jssupportticket::$_db->prepare(
                    "DELETE FROM `" . $jsst_table . "` WHERE configname = %s", $jsst_old));
            }
        }

        update_option('jsst_ai_config_renamed', time(), false);
    }

    /* ------------------------------------------------------------------ *
     * The catalogue
     * ------------------------------------------------------------------ */

    /**
     * Every kind of content the AI can be pointed at.
     *
     * This is the same list the add-on's retriever and core's free suggestion
     * path each carry their own copy of, and those copies are not being merged
     * here — they describe how to *search* a source and this describes how to
     * *govern* one, which is a different set of columns and a different owner.
     * What both halves must agree on is the key, so the keys are the keys those
     * two already use and no others.
     *
     * 'owner' names the capability that owns the content, or null when core or
     * the add-on itself does. It is asked through JSSTmergedaddon so a
     * capability absorbed into free core in 4.0 does not read as missing.
     *
     * 'wp' marks the one source that is not a plugin table. Its rows live in
     * wp_posts and its where clause is not ours to change.
     */
    public static function types() {
        $jsst_types = array(
            'kb' => array(
                'label'  => esc_html(__('Knowledge Base', 'js-support-ticket')),
                'blurb'  => esc_html(__('Published articles. The most authoritative thing most desks have, and the first place an answer should come from.', 'js-support-ticket')),
                'gloss'  => esc_html(__('Published help articles', 'js-support-ticket')),
                'owner'  => 'knowledgebase',
                'table'  => 'js_ticket_articles',
                'idcol'  => 'id',
                'title'  => 'subject',
                /* Published, and not written for agents only. The second half
                   is added only where the column exists, so a site mid-upgrade
                   gets a less governed corpus rather than a SQL error.
                   (Roadmap 6.0-KB-01) */
                'where'  => 'status = 1'
                            . ((class_exists('JSSTkbgovernance') && JSSTkbgovernance::governed())
                                ? " AND (audience IS NULL OR audience <> 'staff')" : ''),
                'wp'     => false,
                'manage' => 'admin.php?page=knowledgebase',
                'needs'  => esc_html(__('Knowledge Base', 'js-support-ticket')),
            ),
            'faq' => array(
                'label'  => esc_html(__('FAQs', 'js-support-ticket')),
                'blurb'  => esc_html(__('Short answers to recurring questions. Usually the best-worded content on the site, and it retrieves well for that reason.', 'js-support-ticket')),
                'gloss'  => esc_html(__('Short answers to common questions', 'js-support-ticket')),
                'owner'  => 'faq',
                'table'  => 'js_ticket_faqs',
                'idcol'  => 'id',
                'title'  => 'subject',
                'where'  => 'status = 1',
                'wp'     => false,
                'manage' => 'admin.php?page=faq',
                'needs'  => esc_html(__('FAQ', 'js-support-ticket')),
            ),
            'canned' => array(
                'label'  => esc_html(__('Canned responses', 'js-support-ticket')),
                'blurb'  => esc_html(__('Written for agents to send, not for customers to read. Worth going through one by one: an internal note or a pricing exception in here becomes something the AI will repeat.', 'js-support-ticket')),
                'gloss'  => esc_html(__('Written for agents, not customers', 'js-support-ticket')),
                'owner'  => 'cannedresponses',
                'table'  => 'js_ticket_department_message_premade',
                'idcol'  => 'id',
                'title'  => 'title',
                /* Enabled ones only. This was empty, which meant a canned
                   response an administrator had switched off was still
                   retrievable and still quotable - and switching one off is
                   exactly what somebody does with a policy that has changed.
                   The whole point of this source is that it is written to be
                   sent, so a retired one reads as authoritative right up until
                   a customer acts on it.

                   NULL is allowed through, and that is not tidiness. This
                   column has no default, so a row created before the form grew
                   its Active/Disabled control - or by an import - has no status
                   at all, and NULL means "nobody ever chose" rather than
                   "somebody switched this off". Excluding it would take canned
                   responses out of the corpus on upgrade, silently, on exactly
                   the oldest sites. (Roadmap 6.0-KB-02) */
                /* The approval half is added only when the column exists. A
                   site mid-upgrade would otherwise get a SQL error on every
                   retrieval rather than a slightly less governed corpus, and of
                   the two that is plainly the worse failure. */
                'where'  => '(status = 1 OR status IS NULL)'
                            . ((class_exists('JSSTcannedlibrary') && JSSTcannedlibrary::governed())
                                ? ' AND (approved IS NULL OR approved = 1)' : ''),
                'wp'     => false,
                'manage' => 'admin.php?page=cannedresponses',
                'needs'  => '',
            ),
            'posts' => array(
                'label'  => esc_html(__('WordPress posts and pages', 'js-support-ticket')),
                'blurb'  => esc_html(__('Everything published on the site, which on most sites includes marketing copy the AI should never quote as documentation. This is the source that most often wants a short approved list rather than the lot.', 'js-support-ticket')),
                'gloss'  => esc_html(__('Everything published on the site', 'js-support-ticket')),
                'owner'  => null,
                'table'  => '',
                'idcol'  => 'ID',
                'title'  => 'post_title',
                'where'  => '',
                'wp'     => true,
                'manage' => 'edit.php',
                'needs'  => '',
            ),
            'scraped' => array(
                'label'  => esc_html(__('Indexed documentation', 'js-support-ticket')),
                'blurb'  => esc_html(__('Pages fetched from documentation sites and video descriptions. What gets crawled is set under Content Sources; what may be quoted is set here.', 'js-support-ticket')),
                'gloss'  => esc_html(__('Pages fetched by the crawler', 'js-support-ticket')),
                'owner'  => 'aiagent',
                'table'  => 'js_ticket_instantfix_data',
                'idcol'  => 'id',
                'title'  => 'title',
                'where'  => '',
                'wp'     => false,
                'manage' => 'admin.php?page=aiagent&jstlay=aiagent_feeds',
                'needs'  => esc_html(__('AI Agent add-on', 'js-support-ticket')),
            ),
            'tickets' => array(
                'label'  => esc_html(__('Resolved tickets', 'js-support-ticket')),
                'blurb'  => esc_html(__('Answers agents have already written. The richest source a desk has and the riskiest: it is the one place customer detail can be quoted back to a different customer.', 'js-support-ticket')),
                'gloss'  => esc_html(__('Answers agents already wrote', 'js-support-ticket')),
                'owner'  => 'aiagent',
                'table'  => 'js_ticket_tickets',
                'idcol'  => 'id',
                'title'  => 'subject',
                'where'  => '',
                'wp'     => false,
                'manage' => 'admin.php?page=tickets',
                'needs'  => esc_html(__('AI Agent add-on', 'js-support-ticket')),
            ),
        );

        return apply_filters('jsst_ai_source_types', $jsst_types);
    }

    public static function type($jsst_key) {
        $jsst_types = self::types();
        return isset($jsst_types[$jsst_key]) ? $jsst_types[$jsst_key] : false;
    }

    public static function isType($jsst_key) {
        return (self::type($jsst_key) !== false);
    }

    /** A source type's full table name, or '' for the WordPress one. */
    private static function tableFor($jsst_def) {
        if (!empty($jsst_def['wp'])) return jssupportticket::$_db->posts;
        if ($jsst_def['table'] === '') return '';
        return jssupportticket::$_db->prefix . $jsst_def['table'];
    }

    /**
     * The where clause that decides which rows of a type are documents at all.
     *
     * Kept beside the catalogue rather than in it for the WordPress source,
     * whose clause has to name the post types the index actually reads — and
     * that list is a filter the site can change, so it cannot be a constant.
     */
    private static function whereFor($jsst_key, $jsst_def) {
        /* Resolved tickets are not every ticket. Retrieval only ever quotes one
           that an agent has actually answered, so the governed population has to
           be the same set - a screen counting four thousand tickets of which
           retrieval can reach two hundred is a screen that teaches somebody the
           wrong thing about their own desk. The clause needs the table prefix,
           which is why it is built here rather than sitting in the catalogue. */
        if ($jsst_key === 'tickets') {
            $jsst_replies = jssupportticket::$_db->prefix . 'js_ticket_replies';
            $jsst_tickets = jssupportticket::$_db->prefix . 'js_ticket_tickets';
            return "EXISTS (SELECT 1 FROM `" . $jsst_replies . "` r WHERE r.ticketid = `"
                 . $jsst_tickets . "`.`id` AND r.staffid > 0)";
        }
        if (empty($jsst_def['wp'])) {
            return $jsst_def['where'];
        }
        $jsst_kinds = apply_filters('jsst_aiagent_post_types', array('post', 'page'));
        $jsst_in    = array();
        foreach ((array) $jsst_kinds as $jsst_kind) $jsst_in[] = "'" . esc_sql($jsst_kind) . "'";
        if (empty($jsst_in)) $jsst_in[] = "'post'";

        // post_password is checked here for the same reason the indexer checks
        // it: a page behind a password is content the site has decided not to
        // show, and answering from it hands out what the password was for.
        return "post_status = 'publish' AND post_type IN (" . implode(',', $jsst_in) . ") AND post_password = ''";
    }

    /* ------------------------------------------------------------------ *
     * Level one: which types are approved
     * ------------------------------------------------------------------ */

    /**
     * Approved source types.
     *
     * The default when nothing has been saved is the four core-owned sources,
     * matching what the add-on and the free suggestion path each fall back to.
     * Scraped documentation and resolved tickets are deliberately not in it:
     * both are opt-in, and resolved tickets in particular should be a decision
     * somebody made rather than a default they inherited.
     */
    public static function approvedTypes() {
        $jsst_raw = get_option(self::OPT_TYPES, null);

        if (!is_array($jsst_raw)) {
            $jsst_raw = self::migrate();
        }

        /* An empty list is honoured, and only a value that is not a list at all
           falls back. Every earlier reader of this setting treated `[]` as "not
           configured" and handed back the four defaults, which meant a site that
           deliberately unticked every source got all four of them back - a
           governance screen that cannot express "none" is a governance screen
           that fails open on the one setting somebody would only ever reach for
           in an incident. */
        if (!is_array($jsst_raw)) {
            /* Only the defaults this site can actually read. A source whose
               add-on is missing approved by default is "approved but empty",
               which looks exactly like a broken engine. */
            $jsst_raw = array_values(array_filter(
                array('kb', 'faq', 'canned', 'posts'),
                array(__CLASS__, 'present')));
        }

        // A key that is no longer in the catalogue is dropped rather than
        // carried: it can only come from an older release or a hand-edited row,
        // and letting it through would put a type nothing can describe into
        // every SQL filter built below.
        $jsst_out = array();
        foreach ($jsst_raw as $jsst_key) {
            if (self::isType($jsst_key)) $jsst_out[] = $jsst_key;
        }
        return $jsst_out;
    }

    public static function isApproved($jsst_key) {
        return in_array($jsst_key, self::approvedTypes(), true);
    }

    /**
     * Write the approved list back to the config row every other reader uses.
     *
     * Writes the row directly rather than through the configuration screen's
     * save path, which expects a whole form. jssupportticket::$_config is
     * updated in place as well, so anything asking later in this same request
     * sees the new answer rather than the one the request booted with.
     */
    public static function setApprovedTypes($jsst_keys) {
        $jsst_clean = array();
        foreach ((array) $jsst_keys as $jsst_key) {
            $jsst_key = sanitize_key($jsst_key);
            if (self::isType($jsst_key) && !in_array($jsst_key, $jsst_clean, true)) {
                $jsst_clean[] = $jsst_key;
            }
        }

        /* Autoload off: this is read by retrieval and by one settings screen,
           neither of which is every page load on the front end. */
        update_option(self::OPT_TYPES, $jsst_clean, false);

        /* The legacy row is kept in step for as long as it exists, because an
           add-on built against a core older than this one still reads it, and a
           site running that combination should not have two different ideas of
           what the AI may see. Nothing in this release reads it back. */
        if (isset(jssupportticket::$_config[self::CFG_TYPES])) {
            $jsst_value = wp_json_encode($jsst_clean);
            jssupportticket::$_db->update(
                jssupportticket::$_db->prefix . 'js_ticket_config',
                array('configvalue' => $jsst_value),
                array('configname' => self::CFG_TYPES)
            );
            jssupportticket::$_config[self::CFG_TYPES] = $jsst_value;
        }

        self::forget();
        return $jsst_clean;
    }

    /**
     * Adopt the choice a site made before the list had a home of its own.
     *
     * Runs once: the option is written even when the legacy row says nothing,
     * so a site that never configured it does not re-read the config table on
     * every retrieval for the rest of its life. Returns null when there was
     * nothing to adopt, which is the caller's signal to use the defaults.
     */
    private static function migrate() {
        $jsst_legacy = isset(jssupportticket::$_config[self::CFG_TYPES])
            ? json_decode(jssupportticket::$_config[self::CFG_TYPES], true)
            : null;

        if (!is_array($jsst_legacy)) {
            return null;
        }

        $jsst_clean = array();
        foreach ($jsst_legacy as $jsst_key) {
            if (self::isType($jsst_key) && !in_array($jsst_key, $jsst_clean, true)) {
                $jsst_clean[] = $jsst_key;
            }
        }
        update_option(self::OPT_TYPES, $jsst_clean, false);
        return $jsst_clean;
    }

    /* ------------------------------------------------------------------ *
     * Level two: which documents within a type
     * ------------------------------------------------------------------ */

    /** How a type's exceptions run: 'all' minus denials, or 'pick' plus approvals. */
    public static function mode($jsst_key) {
        $jsst_modes = get_option(self::OPT_MODES, array());
        if (!is_array($jsst_modes)) $jsst_modes = array();
        return (isset($jsst_modes[$jsst_key]) && $jsst_modes[$jsst_key] === self::MODE_PICK)
            ? self::MODE_PICK : self::MODE_ALL;
    }

    public static function setMode($jsst_key, $jsst_mode) {
        if (!self::isType($jsst_key)) return false;
        $jsst_mode = ($jsst_mode === self::MODE_PICK) ? self::MODE_PICK : self::MODE_ALL;

        $jsst_modes = get_option(self::OPT_MODES, array());
        if (!is_array($jsst_modes)) $jsst_modes = array();
        if (isset($jsst_modes[$jsst_key]) && $jsst_modes[$jsst_key] === $jsst_mode) return true;

        $jsst_modes[$jsst_key] = $jsst_mode;
        update_option(self::OPT_MODES, $jsst_modes, false);
        self::forget();
        return true;
    }

    public static function modes() {
        return array(
            self::MODE_ALL => array(
                'label' => esc_html(__('Everything, except what I exclude', 'js-support-ticket')),
                'short' => esc_html(__('All except excluded', 'js-support-ticket')),
                'blurb' => esc_html(__('New content is answerable the moment it is published. The right choice for a knowledge base written to be read by customers.', 'js-support-ticket')),
            ),
            self::MODE_PICK => array(
                'label' => esc_html(__('Only what I approve', 'js-support-ticket')),
                'short' => esc_html(__('Only approved', 'js-support-ticket')),
                'blurb' => esc_html(__('Nothing is answerable until somebody says so, and anything published after today stays invisible until it is approved. The right choice for posts and pages, and for resolved tickets.', 'js-support-ticket')),
            ),
        );
    }

    /**
     * Every exception, as type => id => verdict.
     *
     * One query, memoised for the request. Retrieval asks this on every search
     * and the answer is small — it holds exceptions, not documents.
     */
    public static function rules($jsst_force = false) {
        if (self::$jsst_rules !== null && !$jsst_force) return self::$jsst_rules;

        self::$jsst_rules = array();
        if (!self::available()) return self::$jsst_rules;

        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT sourcetype, sourceid, verdict FROM `" . self::table() . "`"
        );
        foreach ((array) $jsst_rows as $jsst_row) {
            self::$jsst_rules[(string) $jsst_row->sourcetype][(int) $jsst_row->sourceid] = (int) $jsst_row->verdict;
        }
        return self::$jsst_rules;
    }

    /** The exceptions for one type, as id => verdict. */
    public static function rulesFor($jsst_key) {
        $jsst_rules = self::rules();
        return isset($jsst_rules[$jsst_key]) ? $jsst_rules[$jsst_key] : array();
    }

    /** Ids of one verdict within a type. */
    public static function idsWithVerdict($jsst_key, $jsst_verdict) {
        $jsst_out = array();
        foreach (self::rulesFor($jsst_key) as $jsst_id => $jsst_value) {
            if ((int) $jsst_value === (int) $jsst_verdict) $jsst_out[] = (int) $jsst_id;
        }
        return $jsst_out;
    }

    /**
     * Record one document's verdict.
     *
     * Written as a delete-then-insert rather than an upsert because the two
     * verdicts are not an update of one another in any meaningful sense — a
     * document going from denied to approved is a new decision by a different
     * person at a different time, and the row should say so.
     */
    public static function setRule($jsst_key, $jsst_id, $jsst_verdict, $jsst_note = '') {
        if (!self::isType($jsst_key) || !self::available()) return false;
        $jsst_id = (int) $jsst_id;
        if ($jsst_id < 1) return false;

        jssupportticket::$_db->delete(self::table(),
            array('sourcetype' => $jsst_key, 'sourceid' => $jsst_id));

        jssupportticket::$_db->insert(self::table(), array(
            'sourcetype' => $jsst_key,
            'sourceid'   => $jsst_id,
            'verdict'    => ((int) $jsst_verdict === self::RULE_ALLOW) ? self::RULE_ALLOW : self::RULE_DENY,
            'note'       => substr((string) $jsst_note, 0, 255),
            'setby'      => get_current_user_id(),
            'updated'    => current_time('Y-m-d H:i:s'),
        ));

        self::forget();
        return true;
    }

    /** Drop one document's exception, returning it to whatever its type's mode says. */
    public static function clearRule($jsst_key, $jsst_id) {
        if (!self::available()) return false;
        jssupportticket::$_db->delete(self::table(),
            array('sourcetype' => $jsst_key, 'sourceid' => (int) $jsst_id));
        self::forget();
        return true;
    }

    /** Drop every exception a type holds. */
    public static function clearType($jsst_key) {
        if (!self::available()) return 0;
        $jsst_gone = jssupportticket::$_db->delete(self::table(), array('sourcetype' => $jsst_key));
        self::forget();
        return (int) $jsst_gone;
    }

    /* ------------------------------------------------------------------ *
     * The question everything else asks
     * ------------------------------------------------------------------ */

    /**
     * May this document be used to answer a question?
     *
     * The order is type, then exception, then mode — type first for the same
     * reason the policy checks the lane first: a site that switched a whole
     * source off should get the same answer whether or not somebody once
     * approved a document inside it.
     */
    public static function eligible($jsst_key, $jsst_id) {
        if (!self::isApproved($jsst_key)) return false;

        /* An approved source can still hold a document nobody has signed off.
           Governance is asked after the type and before the per-document rule,
           because approving one article individually is a statement about which
           article - not a statement that a draft may be quoted at a customer.
           (Roadmap 6.0-KB-01) */
        if ($jsst_key === 'kb' && class_exists('JSSTkbgovernance')
            && !JSSTkbgovernance::citable($jsst_id)) {
            return false;
        }

        /* An article written for the people who answer tickets, not for the
           people who file them. Every path that answers from this corpus
           answers a customer, so "agents only" means "never cited" - an
           internal runbook must not be repeated at somebody by a machine.
           (Roadmap 6.0-KB-01) */
        if ($jsst_key === 'kb' && class_exists('JSSTkbgovernance')
            && !JSSTkbgovernance::citableToCustomer($jsst_id)) {
            return false;
        }

        /* And the same question for canned responses, which need no register of
           their own - they have one column and it already means this. It is
           asked here as well as in the clause above because the chunk index
           holds content that was indexed while it was enabled: a response
           switched off this morning is still sitting in that index, and only
           this guard catches it before the next rebuild. (Roadmap 6.0-KB-02) */
        if ($jsst_key === 'canned' && !self::cannedEnabled($jsst_id)) {
            return false;
        }

        /* And whether anybody signed it off. A canned response is text that
           goes out under the company's name, so an unapproved one is exactly
           what should not be quoted at a customer by a machine.
           (Roadmap 6.0-KB-02) */
        if ($jsst_key === 'canned' && class_exists('JSSTcannedlibrary')
            && !JSSTcannedlibrary::approved($jsst_id)) {
            return false;
        }

        $jsst_rules = self::rulesFor($jsst_key);
        $jsst_id    = (int) $jsst_id;

        if (isset($jsst_rules[$jsst_id])) {
            return ((int) $jsst_rules[$jsst_id] === self::RULE_ALLOW);
        }
        return (self::mode($jsst_key) === self::MODE_ALL);
    }

    /**
     * Is this canned response switched on?
     *
     * A missing row answers true, for the reason `JSSTkbgovernance::citable()`
     * gives: whether a document exists is a different question from whether it
     * is governed, and answering "no" here would turn every unknown id into a
     * governance refusal that the console then explains wrongly.
     */
    public static function cannedEnabled($jsst_id) {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_department_message_premade';
        if (jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                'SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return true;
        }

        $jsst_status = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT status FROM `" . $jsst_table . "` WHERE id = %d", (int) $jsst_id));

        if ($jsst_status === null) return true;
        return ((int) $jsst_status === 1);
    }

    /**
     * Why a document is or is not eligible, in the state/reason shape the rest
     * of the product's explain() methods use. For the console, which has to say
     * *why* rather than just refuse.
     */
    public static function explain($jsst_key, $jsst_id) {
        $jsst_def = self::type($jsst_key);
        $jsst_label = $jsst_def ? $jsst_def['label'] : $jsst_key;

        if (!self::isType($jsst_key)) {
            return array('state' => false, 'reason' => 'unknown_source',
                'detail' => esc_html(__('Not a source this site knows about.', 'js-support-ticket')));
        }
        if (!self::isApproved($jsst_key)) {
            return array('state' => false, 'reason' => 'type_not_approved',
                'detail' => sprintf(
                    /* translators: %s: the name of a content source, for example "FAQs" */
                    esc_html(__('%s is not an approved source on this site.', 'js-support-ticket')), $jsst_label));
        }

        if ($jsst_key === 'kb' && class_exists('JSSTkbgovernance')
            && !JSSTkbgovernance::citable($jsst_id)) {
            return array('state' => false, 'reason' => 'not_governed',
                'detail' => JSSTkbgovernance::whyNotCitable($jsst_id));
        }

        if ($jsst_key === 'kb' && class_exists('JSSTkbgovernance')
            && !JSSTkbgovernance::citableToCustomer($jsst_id)) {
            return array('state' => false, 'reason' => 'staff_only',
                'detail' => esc_html(__('This article is for agents only, so it is never quoted at a customer.', 'js-support-ticket')));
        }

        if ($jsst_key === 'canned' && !self::cannedEnabled($jsst_id)) {
            return array('state' => false, 'reason' => 'not_enabled',
                'detail' => esc_html(__('This canned response is switched off, so nothing may answer from it.', 'js-support-ticket')));
        }

        if ($jsst_key === 'canned' && class_exists('JSSTcannedlibrary')
            && !JSSTcannedlibrary::approved($jsst_id)) {
            return array('state' => false, 'reason' => 'not_approved',
                'detail' => esc_html(__('Nobody has approved this canned response, so nothing may answer from it.', 'js-support-ticket')));
        }

        $jsst_rules = self::rulesFor($jsst_key);
        $jsst_id    = (int) $jsst_id;

        if (isset($jsst_rules[$jsst_id])) {
            if ((int) $jsst_rules[$jsst_id] === self::RULE_ALLOW) {
                return array('state' => true, 'reason' => 'approved',
                    'detail' => esc_html(__('Approved individually.', 'js-support-ticket')));
            }
            return array('state' => false, 'reason' => 'excluded',
                'detail' => esc_html(__('Excluded individually.', 'js-support-ticket')));
        }

        if (self::mode($jsst_key) === self::MODE_PICK) {
            return array('state' => false, 'reason' => 'not_picked',
                'detail' => sprintf(
                    /* translators: %s: the name of a content source, for example "WordPress posts and pages" */
                    esc_html(__('%s only answers from documents that have been approved, and this one has not.', 'js-support-ticket')), $jsst_label));
        }

        return array('state' => true, 'reason' => 'included',
            'detail' => esc_html(__('Included with the rest of its source.', 'js-support-ticket')));
    }

    /* ------------------------------------------------------------------ *
     * Pushing the same decision into SQL
     * ------------------------------------------------------------------ */

    /**
     * The filter as a SQL fragment, for a query over one source's own table.
     *
     * Returns '' when nothing is excluded, ' AND 1 = 0' when nothing can match,
     * and otherwise an IN or NOT IN list. Applied in the query rather than to
     * its results because a retrieval pool is a LIMIT: filtering afterwards
     * lets excluded documents take the places of the ones that should have been
     * returned, and a site that excluded its ten marketing pages would get ten
     * fewer answers rather than the next ten.
     *
     * The values interpolated here are integers cast from our own table and a
     * column name from the catalogue above, never anything a request supplied,
     * which is why this can return a literal fragment for a caller to paste into
     * a prepared statement.
     *
     * @param string $jsst_key    Source type.
     * @param string $jsst_column The id column, already safe to interpolate.
     */
    public static function sqlFilter($jsst_key, $jsst_column) {
        if (!self::isApproved($jsst_key)) return ' AND 1 = 0';

        $jsst_column = preg_replace('/[^A-Za-z0-9_]/', '', (string) $jsst_column);
        if ($jsst_column === '') return '';

        if (self::mode($jsst_key) === self::MODE_PICK) {
            $jsst_ids = self::idsWithVerdict($jsst_key, self::RULE_ALLOW);
            if (empty($jsst_ids)) return ' AND 1 = 0';
            return ' AND `' . $jsst_column . '` IN (' . implode(',', array_map('intval', $jsst_ids)) . ')';
        }

        $jsst_ids = self::idsWithVerdict($jsst_key, self::RULE_DENY);
        if (empty($jsst_ids)) return '';
        return ' AND `' . $jsst_column . '` NOT IN (' . implode(',', array_map('intval', $jsst_ids)) . ')';
    }

    /**
     * The same filter for the unified chunk index, where one query covers
     * several types at once.
     *
     * Replaces the plain `source_type IN (...)` clause rather than sitting
     * beside it, because each type carries its own mode and its own exceptions
     * and there is no way to express that as one list. A type whose filter
     * admits nothing is left out of the clause entirely instead of contributing
     * an always-false branch.
     *
     * @param array $jsst_types Types the caller intends to search.
     * @return string A bracketed clause, or '1 = 0' when nothing is searchable.
     */
    public static function chunkFilter($jsst_types, $jsst_typecol = 'source_type', $jsst_idcol = 'source_id') {
        $jsst_typecol = preg_replace('/[^A-Za-z0-9_]/', '', (string) $jsst_typecol);
        $jsst_idcol   = preg_replace('/[^A-Za-z0-9_]/', '', (string) $jsst_idcol);
        $jsst_parts   = array();

        foreach ((array) $jsst_types as $jsst_key) {
            if (!self::isType($jsst_key) || !self::isApproved($jsst_key)) continue;

            $jsst_clause = "`" . $jsst_typecol . "` = '" . esc_sql($jsst_key) . "'";

            if (self::mode($jsst_key) === self::MODE_PICK) {
                $jsst_ids = self::idsWithVerdict($jsst_key, self::RULE_ALLOW);
                if (empty($jsst_ids)) continue;
                $jsst_clause .= " AND `" . $jsst_idcol . "` IN (" . implode(',', array_map('intval', $jsst_ids)) . ")";
            } else {
                $jsst_ids = self::idsWithVerdict($jsst_key, self::RULE_DENY);
                if (!empty($jsst_ids)) {
                    $jsst_clause .= " AND `" . $jsst_idcol . "` NOT IN (" . implode(',', array_map('intval', $jsst_ids)) . ")";
                }
            }
            $jsst_parts[] = '(' . $jsst_clause . ')';
        }

        if (empty($jsst_parts)) return '1 = 0';
        return '(' . implode(' OR ', $jsst_parts) . ')';
    }

    /* ------------------------------------------------------------------ *
     * Health, counts and re-sync
     * ------------------------------------------------------------------ */

    /**
     * Is the capability owning a type's content available?
     *
     * The same test the add-on's retriever makes, and made the same way: a
     * capability merged into free core has no plugin in the active list, so
     * asking that list alone reports content missing while the table sits there
     * full. Guarded so this class still answers on a site whose core predates
     * the merged-addon register.
     */
    /** Whether the add-on that owns this source is running on this site. */
    public static function present($jsst_key) {
        $jsst_def = self::type($jsst_key);
        return $jsst_def ? self::ownerAvailable($jsst_def['owner']) : false;
    }

    private static function ownerAvailable($jsst_owner) {
        if ($jsst_owner === null) return true;
        if (in_array($jsst_owner, (array) jssupportticket::$_active_addons, true)) return true;
        return class_exists('JSSTmergedaddon')
            && method_exists('JSSTmergedaddon', 'featureEnabled')
            && JSSTmergedaddon::featureEnabled($jsst_owner);
    }

    /**
     * Everything the governance screen shows, one row per source type.
     *
     * Health is 'unknown' until something can actually be counted, for the same
     * reason connector health is: a green tick meaning "nobody checked" is the
     * failure the screen exists to prevent. A source that is off reads 'off'
     * rather than 'bad' — switching it off was a decision, not a fault.
     *
     * @param bool $jsst_force Skip the short-lived cache.
     */
    public static function state($jsst_force = false) {
        $jsst_cached = $jsst_force ? false : get_transient('jsst_ai_sources_state');
        if (is_array($jsst_cached)) return $jsst_cached;

        $jsst_sync  = get_option(self::OPT_SYNC, array());
        if (!is_array($jsst_sync)) $jsst_sync = array();
        $jsst_chunks = self::chunkCounts();
        $jsst_out    = array();

        foreach (self::types() as $jsst_key => $jsst_def) {
            $jsst_row = array(
                'key'       => $jsst_key,
                'label'     => $jsst_def['label'],
                'blurb'     => $jsst_def['blurb'],
                'gloss'     => isset($jsst_def['gloss']) ? $jsst_def['gloss'] : '',
                'owner'     => $jsst_def['owner'],
                'needs'     => $jsst_def['needs'],
                'manage'    => $jsst_def['manage'],
                'present'   => self::ownerAvailable($jsst_def['owner']),
                'approved'  => self::isApproved($jsst_key),
                'mode'      => self::mode($jsst_key),
                'documents' => null,
                'allowed'   => count(self::idsWithVerdict($jsst_key, self::RULE_ALLOW)),
                'excluded'  => count(self::idsWithVerdict($jsst_key, self::RULE_DENY)),
                'eligible'  => null,
                'indexed'   => isset($jsst_chunks[$jsst_key]) ? (int) $jsst_chunks[$jsst_key] : 0,
                'synced'    => isset($jsst_sync[$jsst_key]) ? (int) $jsst_sync[$jsst_key] : 0,
                'health'    => 'unknown',
                'reason'    => '',
            );

            $jsst_table = self::tableFor($jsst_def);
            if ($jsst_table !== '' && jssupportticket::$_db->get_var(
                    jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) === $jsst_table) {
                $jsst_where = self::whereFor($jsst_key, $jsst_def);
                $jsst_row['documents'] = (int) jssupportticket::$_db->get_var(
                    "SELECT COUNT(*) FROM `" . $jsst_table . "`" . ($jsst_where !== '' ? ' WHERE ' . $jsst_where : '')
                );

                /* Counted with the same fragment retrieval uses, rather than
                   worked out from the numbers above. The two would agree today
                   and drift the first time a mode grew a third option, and a
                   governance screen whose count disagrees with what the AI can
                   see is worse than no count. */
                $jsst_filter = self::sqlFilter($jsst_key, $jsst_def['idcol']);
                if ($jsst_filter === ' AND 1 = 0') {
                    $jsst_row['eligible'] = 0;
                } else {
                    $jsst_row['eligible'] = (int) jssupportticket::$_db->get_var(
                        "SELECT COUNT(*) FROM `" . $jsst_table . "` WHERE "
                        . ($jsst_where !== '' ? $jsst_where : '1 = 1') . $jsst_filter
                    );
                }
            } else {
                $jsst_row['table_missing'] = true;
            }

            $jsst_out[$jsst_key] = self::verdict($jsst_row);
        }

        set_transient('jsst_ai_sources_state', $jsst_out, 5 * MINUTE_IN_SECONDS);
        return $jsst_out;
    }

    /**
     * One row's health, decided once so the screen never has to.
     *
     * The order matters and reads down the page: off before broken, because a
     * source somebody switched off is not a fault to be reported; broken before
     * empty, because a missing table is why it is empty; and "approved but
     * nothing is eligible" is its own warning rather than an ok with a zero
     * next to it — that is the state where somebody thinks a source is working
     * and it is answering nothing.
     */
    private static function verdict($jsst_row) {
        if (!$jsst_row['approved']) {
            $jsst_row['health'] = 'off';
            $jsst_row['reason'] = esc_html(__('Not an approved source. Nothing here can be quoted or shown.', 'js-support-ticket'));
            return $jsst_row;
        }
        if (!$jsst_row['present']) {
            $jsst_row['health'] = 'bad';
            $jsst_row['reason'] = $jsst_row['needs'] !== ''
                ? sprintf(
                    /* translators: %s: the name of the add-on that owns this content */
                    esc_html(__('Approved, but %s is not available on this site.', 'js-support-ticket')), $jsst_row['needs'])
                : esc_html(__('Approved, but the feature that owns this content is not available.', 'js-support-ticket'));
            return $jsst_row;
        }
        if (!empty($jsst_row['table_missing'])) {
            $jsst_row['health'] = 'bad';
            $jsst_row['reason'] = esc_html(__('Approved, but the table this content lives in is not installed.', 'js-support-ticket'));
            return $jsst_row;
        }
        if ((int) $jsst_row['documents'] === 0) {
            $jsst_row['health'] = 'warn';
            $jsst_row['reason'] = esc_html(__('Approved, but there is nothing published in it yet.', 'js-support-ticket'));
            return $jsst_row;
        }
        if ((int) $jsst_row['eligible'] === 0) {
            $jsst_row['health'] = 'warn';
            $jsst_row['reason'] = ($jsst_row['mode'] === self::MODE_PICK)
                ? esc_html(__('Approved, but no document in it has been approved yet — so it answers nothing.', 'js-support-ticket'))
                : esc_html(__('Approved, but every document in it has been excluded — so it answers nothing.', 'js-support-ticket'));
            return $jsst_row;
        }

        $jsst_row['health'] = 'ok';
        $jsst_row['reason'] = ($jsst_row['excluded'] > 0 || $jsst_row['mode'] === self::MODE_PICK)
            ? esc_html(__('In use, with the exceptions below applied.', 'js-support-ticket'))
            : esc_html(__('In use.', 'js-support-ticket'));
        return $jsst_row;
    }

    /** Chunks per type from the add-on's index, or an empty list without it. */
    private static function chunkCounts() {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_ai_passages';
        if (jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return array();
        }
        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT source_type, COUNT(*) AS jsst_total FROM `" . $jsst_table . "` GROUP BY source_type"
        );
        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[(string) $jsst_row->source_type] = (int) $jsst_row->jsst_total;
        }
        return $jsst_out;
    }

    /**
     * Documents of one type, with the verdict already resolved for each.
     *
     * Paged and searchable because the whole point of the screen is a site with
     * four thousand posts. The verdict is worked out here rather than in the
     * template so the list and retrieval can never disagree about a row.
     *
     * @param array $jsst_args page, per, search.
     */
    public static function documents($jsst_key, $jsst_args = array()) {
        $jsst_def = self::type($jsst_key);
        $jsst_out = array('rows' => array(), 'total' => 0, 'page' => 1, 'pages' => 1, 'per' => 20);
        if (!$jsst_def) return $jsst_out;

        $jsst_table = self::tableFor($jsst_def);
        if ($jsst_table === '' || jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) !== $jsst_table) {
            return $jsst_out;
        }

        $jsst_per  = isset($jsst_args['per']) ? max(5, min(100, (int) $jsst_args['per'])) : 20;
        $jsst_page = isset($jsst_args['page']) ? max(1, (int) $jsst_args['page']) : 1;
        $jsst_find = isset($jsst_args['search']) ? trim((string) $jsst_args['search']) : '';

        $jsst_where = array();
        $jsst_base  = self::whereFor($jsst_key, $jsst_def);
        if ($jsst_base !== '') $jsst_where[] = $jsst_base;

        $jsst_args_sql = array();
        if ($jsst_find !== '') {
            $jsst_where[]     = '`' . $jsst_def['title'] . '` LIKE %s';
            $jsst_args_sql[]  = '%' . jssupportticket::$_db->esc_like($jsst_find) . '%';
        }
        $jsst_clause = empty($jsst_where) ? '' : ' WHERE ' . implode(' AND ', $jsst_where);

        $jsst_count_sql = "SELECT COUNT(*) FROM `" . $jsst_table . "`" . $jsst_clause;
        $jsst_out['total'] = (int) (empty($jsst_args_sql)
            ? jssupportticket::$_db->get_var($jsst_count_sql)
            : jssupportticket::$_db->get_var(jssupportticket::$_db->prepare($jsst_count_sql, $jsst_args_sql)));

        $jsst_out['per']   = $jsst_per;
        $jsst_out['pages'] = max(1, (int) ceil($jsst_out['total'] / $jsst_per));
        $jsst_page         = min($jsst_page, $jsst_out['pages']);
        $jsst_out['page']  = $jsst_page;

        $jsst_sql = "SELECT `" . $jsst_def['idcol'] . "` AS jsst_id, `" . $jsst_def['title'] . "` AS jsst_title
                     FROM `" . $jsst_table . "`" . $jsst_clause
                   . " ORDER BY `" . $jsst_def['idcol'] . "` DESC LIMIT " . (int) $jsst_per
                   . " OFFSET " . (int) (($jsst_page - 1) * $jsst_per);

        $jsst_rows = empty($jsst_args_sql)
            ? jssupportticket::$_db->get_results($jsst_sql)
            : jssupportticket::$_db->get_results(jssupportticket::$_db->prepare($jsst_sql, $jsst_args_sql));

        $jsst_rules = self::rulesFor($jsst_key);
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_id = (int) $jsst_row->jsst_id;
            $jsst_out['rows'][] = array(
                'id'       => $jsst_id,
                'title'    => (string) $jsst_row->jsst_title,
                'rule'     => isset($jsst_rules[$jsst_id]) ? (int) $jsst_rules[$jsst_id] : null,
                'eligible' => self::eligible($jsst_key, $jsst_id),
            );
        }
        return $jsst_out;
    }

    /**
     * Re-read one source from scratch.
     *
     * Governance itself needs no re-sync — the filter is applied at query time,
     * so a rule takes effect on the next search whatever the index holds. What
     * does need it is the derived copy: an article edited by SQL, a scrape that
     * half-finished, a table restored from a dump. This drops that type's chunks
     * and lets the add-on's backfill build them again, and where the add-on is
     * absent it does the one thing that still applies — invalidates the caches
     * so the next search reads the content as it is now.
     */
    public static function resync($jsst_key) {
        if (!self::isType($jsst_key)) return false;

        $jsst_done = false;
        if (class_exists('JSSTaiagentindexer')
            && in_array($jsst_key, JSSTaiagentindexer::indexedSourceTypes(), true)) {
            /* Resolved tickets are deliberately not in that list - they are
               queried live rather than chunked - so asking for a rebuild on
               their behalf would walk the whole corpus to change nothing. */
            $jsst_indexer = new JSSTaiagentindexer();
            $jsst_indexer->purgeSourceType($jsst_key);
            $jsst_indexer->scheduleRebuild();
            $jsst_done = true;
        }
        if (class_exists('JSSTaiagentretriever')) {
            JSSTaiagentretriever::bumpCorpusVersion();
        }

        $jsst_sync = get_option(self::OPT_SYNC, array());
        if (!is_array($jsst_sync)) $jsst_sync = array();
        $jsst_sync[$jsst_key] = time();
        update_option(self::OPT_SYNC, $jsst_sync, false);

        self::forget();
        return $jsst_done;
    }

    /**
     * Drop everything derived from the rules.
     *
     * Called after every write. The retrieval cache is keyed on a corpus
     * version rather than on the rules, so without the bump a search made
     * before an exclusion keeps being served from the transient for an hour —
     * which is an admin excluding a document, testing it, and watching it come
     * back anyway.
     */
    public static function forget() {
        self::$jsst_rules = null;
        delete_transient('jsst_ai_sources_state');
        delete_transient('jsst_aiagent_source_status');
        delete_transient('jsst_aiagent_chunk_types');
        if (class_exists('JSSTaiagentretriever')) {
            JSSTaiagentretriever::bumpCorpusVersion();
        }
    }

    /* ------------------------------------------------------------------ *
     * The console
     * ------------------------------------------------------------------ */

    /**
     * What a question would retrieve, and what governance kept out of it.
     *
     * Drives the add-on's retriever when it is installed, because that is the
     * engine a real ticket goes through and showing anything else would be a
     * console that agrees with itself and disagrees with the product. Without
     * the add-on it reports the eligibility picture alone rather than inventing
     * a second retrieval engine to have something to display — "no engine is
     * installed" is a true answer and a made-up ranking is not.
     *
     * @return array ran, question, snippets, diagnostics, sources, engine.
     */
    public static function console($jsst_question, $jsst_profile = 'reply') {
        $jsst_out = array(
            'ran'         => false,
            'question'    => (string) $jsst_question,
            'profile'     => ($jsst_profile === 'deflect') ? 'deflect' : 'reply',
            'snippets'    => array(),
            'diagnostics' => array(),
            'engine'      => '',
            'note'        => '',
        );

        if (trim((string) $jsst_question) === '') return $jsst_out;
        $jsst_out['ran'] = true;

        if (!class_exists('JSSTaiagentretriever')) {
            /* Without the add-on the ticket form runs core's own search, so
               that is what is tested: the same results, words and limits a
               customer typing this question would get. */
            $jsst_out['free'] = JSSTincluder::getJSModel('ticket')->previewBasicSuggestions($jsst_question);
            return $jsst_out;
        }

        $jsst_retriever = new JSSTaiagentretriever();
        $jsst_out['engine'] = esc_html(__('AI Agent retrieval', 'js-support-ticket'));

        /* no_cache because the console exists to answer "what happens now",
           and an answer assembled before the last rule change is the one
           question it must never answer. */
        $jsst_out['snippets'] = $jsst_retriever->retrieve($jsst_question, array(
            'profile'  => $jsst_out['profile'],
            'no_cache' => true,
            'is_guest' => false,
            // Gather without the rules and apply them afterwards, so the count
            // of what they kept out is a real number rather than a blank.
            'explain'  => true,
        ));
        $jsst_out['diagnostics'] = $jsst_retriever->getDiagnostics();

        return $jsst_out;
    }

}
