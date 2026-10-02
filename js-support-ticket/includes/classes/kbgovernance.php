<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Whether a document is fit to be answered from. (Roadmap 6.0-KB-01)
 *
 * The fourth register, and the one the other three were waiting for. AI-02 says
 * which *sources* the AI may read and which individual documents are excluded;
 * AI-09 says what language they are in; AI-03 says what may be sent. None of
 * them can answer the question a customer's complaint actually turns on: was
 * that article true when we cited it, and did anybody check?
 *
 * ## Why this is in core rather than in the knowledge base add-on
 *
 * Because the thing that has to consult it is core. `JSSTaisources` builds the
 * clause that decides what retrieval may reach, and a governance rule that
 * lived in an add-on would be a rule the AI could only obey when that add-on
 * happened to be installed - which is exactly backwards, since the whole point
 * is that an ungoverned corpus must not be cited. The add-on owns the columns,
 * the form and the screens; core owns the question "may this be cited, and
 * what did it say at the time".
 *
 * ## Three states, and the existing column is not touched
 *
 * `js_ticket_articles.status` has meant published/not-published since long
 * before this, and half a dozen queries across two plugins read it - including
 * both AI source definitions. So it keeps meaning exactly that, and the finer
 * workflow lives in a new column beside it. `publish()` and `unpublish()` write
 * both, in that order, so a half-failure leaves an article unpublished rather
 * than published-but-not-approved.
 *
 * ## Staleness warns, it does not hide
 *
 * The tempting rule is "never cite an article past its review date". It is also
 * the rule that empties a desk's corpus a year after somebody set it up and
 * left, with nothing on any screen explaining why the AI went quiet - the
 * failure mode AI-12's `guard()` docblock argues against at length. So overdue
 * articles are **ranked down and reported**, and a site that genuinely needs
 * the hard rule - a regulated desk, where a stale answer is worse than no
 * answer - opts into it. Same shape as AI-09's prefer/only, for the same
 * reason.
 *
 * ## Version history exists to answer one question
 *
 * Not to give writers an undo. It is there so that six weeks after an automatic
 * answer quoted article 41, somebody can establish what article 41 said on that
 * day - which is the difference between a citation and a claim. That is why a
 * revision is written on every save regardless of who saved it, why the AI's
 * own citations record a revision id, and why revisions are not editable.
 */
class JSSTkbgovernance {

    const TABLE          = 'js_ticket_kb_revisions';
    const SCHEMA_VERSION = '6.0.2';
    const OPT_SCHEMA     = 'jsst_kb_governance_schema';

    /** How many days an article is trusted for before it wants re-reading. */
    const OPT_REVIEW_EVERY = 'jsst_kb_review_every';

    /** What retrieval does with an article past that date. */
    const OPT_STALE_RULE = 'jsst_kb_stale_rule';

    /** Overdue articles are ranked lower and reported. The default. */
    const STALE_WARN = 'warn';

    /** Overdue articles are not cited at all. For desks where wrong is worse than absent. */
    const STALE_REFUSE = 'refuse';

    /** Nothing is done about staleness. */
    const STALE_IGNORE = 'ignore';

    /** Being written, not fit for anybody. */
    const STATE_DRAFT = 'draft';

    /** Written and waiting for somebody other than its author to agree. */
    const STATE_REVIEW = 'review';

    /** Live, and citable. */
    const STATE_PUBLISHED = 'published';

    /** Withdrawn deliberately - not a draft, and not an accident. */
    const STATE_RETIRED = 'retired';

    /** Default review interval when nobody has chosen one, in days. */
    const REVIEW_DAYS = 365;

    /** How much an overdue article's ranking is cut under STALE_WARN. */
    const STALE_WEIGHT = 0.6;

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);
        // Snapshot on save, wherever the save came from.
        add_action('jsst_after_save_article', array(__CLASS__, 'onArticleSaved'), 5);
        // The Review box on the article form.
        add_action('jsst_after_save_article', array(__CLASS__, 'saveReviewBox'), 10);

        add_action(self::REMIND_HOOK, array(__CLASS__, 'remindOwners'));
        // On init, not here: scheduling reads the translated schedule labels.
        add_action('init', array(__CLASS__, 'scheduleReminder'));
    }

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    /**
     * The revisions table, and the columns the articles table is missing.
     *
     * The columns are added here rather than in the add-on's own SQL because
     * core is what reads them: a site whose knowledge base add-on has not been
     * updated yet must still get a working `citeClause()`, and the alternative
     * is core writing SQL that references columns that may not exist.
     */
    public static function ensureSchema() {
        /* The columns first, and outside the version guard, for two reasons
           that pull the same way.

           A site can install the knowledge base add-on a month after this
           shipped, at which point a stored schema version says "already done"
           about a table that did not exist when the guard was satisfied - so
           `governed()` (is the table governed at all) is checked as well.

           And `governed()` alone is not enough either, which is the trap this
           hit: it probes for *one* column, so a **later** release adding a
           sixth column would find the table already governed and never add it.
           The stored version is compared as well, and any difference re-runs a
           pass that is a no-op per column that already exists. Both checks are
           cheap - a transient and an option - and between them they cover
           "never added" and "added an older set". */
        $jsst_version = get_option(self::OPT_SCHEMA, '');
        if (!self::governed() || $jsst_version !== self::SCHEMA_VERSION) {
            self::addColumns();
        }

        if (!class_exists('JSSTschemaguard')) return;
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION,
                array(self::TABLE => array('articleid', 'content', 'savedby')))) {
            return;
        }

        $jsst_charset = jssupportticket::$_db->get_charset_collate();

        /* A revision is what the article *was*, not a diff. Diffs are smaller
           and are the wrong shape for the one question this exists to answer -
           reconstructing a version by replaying twenty diffs is a thing that
           can go wrong, and "what did it say that day" must not be able to. */
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . self::table() . "` (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                articleid bigint(20) NOT NULL DEFAULT '0',
                subject varchar(255) NOT NULL DEFAULT '',
                content longtext,
                savedby bigint(20) NOT NULL DEFAULT '0',
                note varchar(255) NOT NULL DEFAULT '',
                created datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY jsst_kbrev_article (articleid, id),
                KEY jsst_kbrev_when (created)
            ) " . $jsst_charset);

        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);
    }

    /** The governance columns on the articles table, each added only if absent. */
    public static function addColumns() {
        if (!class_exists('JSSTschemaguard')) return;

        /* No columns means no table: the knowledge base is not installed here
           and there is nothing to govern. Asked before the lock is taken so the
           common case costs one SHOW COLUMNS and not a round trip more. */
        if (!JSSTschemaguard::columns('js_ticket_articles')) {
            return;
        }

        /* One request does this, and the rest of them leave without recording a
           version they did not write - so whichever request loses simply finds
           the work already done next time round. Before this, two admin
           requests arriving together each probed the articles table before
           either altered it, and the loser wrote twelve `Duplicate column name`
           errors into the log of a site whose schema was in fact correct. */
        if (!JSSTschemaguard::lock(self::OPT_SCHEMA)) {
            return;
        }

        $jsst_articles = jssupportticket::$_db->prefix . 'js_ticket_articles';

        $jsst_wanted = array(
            /* The workflow state, beside `status` rather than instead of it -
               status keeps meaning published/not-published for the half-dozen
               queries in two plugins that already read it. */
            'workflow'    => "VARCHAR(20) NOT NULL DEFAULT ''",
            /* Who is answerable for this being right, which is not always who
               typed it. An article whose author left the company still needs
               somebody to ask about it. */
            'ownerid'     => "BIGINT(20) NOT NULL DEFAULT '0'",
            'reviewedon'  => "DATETIME NULL",
            'reviewedby'  => "BIGINT(20) NOT NULL DEFAULT '0'",
            /* Per-article, because a page about a keyboard shortcut and a page
               about tax rates do not go stale at the same speed. Zero means
               "use the site default". */
            'reviewevery' => "INT(11) NOT NULL DEFAULT '0'",
            /* Search-engine controls. `metadesc` and `metakey` already existed;
               these are the three an article page actually needs and could not
               express - a stable address, a canonical when the same article is
               reachable two ways, and a way to keep one out of an index without
               unpublishing it. (Roadmap 6.0-KB-01) */
            'slug'        => "VARCHAR(200) NULL",
            'canonical'   => "VARCHAR(255) NULL",
            'noindex'     => "TINYINT(1) NULL",
            /* Which language it is written in, and which article it is a
               translation of. Two columns rather than a table: a translation
               group is a pointer to the original, and the original points at
               nothing. (Roadmap 6.0-KB-01, 6.0-AI-09) */
            'language'      => "VARCHAR(8) NULL",
            'translationof' => "BIGINT(20) NULL",
            /* Who an article is *for*. `visible` already separates guests from
               signed-in customers; this is the level above both - an article
               written for the people who answer tickets, which a customer must
               never be shown and the AI must never quote at one.
               (Roadmap 6.0-KB-01) */
            'audience'    => "VARCHAR(20) NULL",
            'teamid'      => "BIGINT(20) NULL",
        );

        JSSTschemaguard::addColumns('js_ticket_articles', $jsst_wanted);

        /* Everything that was already published was published by somebody who
           meant it, so it starts as published rather than as a draft - an
           upgrade that turned a site's whole knowledge base into drafts would
           take the AI's corpus away and look exactly like a bug. */
        jssupportticket::$_db->query(
            "UPDATE `" . $jsst_articles . "` SET workflow = CASE WHEN status = 1 THEN '"
            . self::STATE_PUBLISHED . "' ELSE '" . self::STATE_DRAFT . "' END WHERE workflow = ''");

        delete_transient('jsst_kb_governed');

        /* Recorded here rather than only at the end of ensureSchema(), because
           the schemaguard below may return early on a site whose revisions
           table is already correct - and then a later column set would be
           re-attempted on every single request forever. */
        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);

        JSSTschemaguard::unlock(self::OPT_SCHEMA);
    }

    /**
     * @param string $jsst_which '' for the revisions table, 'articles' for the
     *                           articles table this governs.
     */
    public static function table($jsst_which = '') {
        if ($jsst_which === 'articles') {
            return jssupportticket::$_db->prefix . 'js_ticket_articles';
        }
        return jssupportticket::$_db->prefix . self::TABLE;
    }

    public static function available() {
        $jsst_table = self::table();
        return (jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) === $jsst_table);
    }

    /**
     * Has the articles table been given its governance columns yet?
     *
     * Cached, and checked before any clause that names one of them: a site
     * mid-upgrade must get a working query rather than a fatal, which is the
     * same arrangement AI-09's language column uses.
     */
    public static function governed() {
        $jsst_have = get_transient('jsst_kb_governed');
        if ($jsst_have !== false) return ($jsst_have === 'yes');

        /* The table before the column. Most sites do not have the knowledge
           base add-on at all, and asking SHOW COLUMNS of a table that is not
           there is a database error on every retrieval - printed into the log
           of a site where nothing is actually wrong. */
        $jsst_articles = jssupportticket::$_db->prefix . 'js_ticket_articles';
        $jsst_exists = (jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SHOW TABLES LIKE %s', $jsst_articles)) === $jsst_articles);

        /* Both columns, for the reason written out in
           `JSSTcannedlibrary::governed()`: this answer gates the `audience`
           clause as well as the workflow features, and a site migrating one
           add-on at a time can have `workflow` without `audience`. Asking for
           only the first told such a desk it was governed and put an unknown
           column into every retrieval. (Roadmap 6.5-ECO-01) */
        $jsst_have = $jsst_exists ? 'yes' : 'no';
        if ($jsst_exists) {
            foreach (array('workflow', 'audience') as $jsst_col) {
                $jsst_cols = jssupportticket::$_db->get_results(
                    "SHOW COLUMNS FROM `" . esc_sql($jsst_articles) . "` LIKE '" . esc_sql($jsst_col) . "'");
                if (empty($jsst_cols)) { $jsst_have = 'no'; break; }
            }
        }
        set_transient('jsst_kb_governed', $jsst_have, HOUR_IN_SECONDS);
        return ($jsst_have === 'yes');
    }

    /* ------------------------------------------------------------------ *
     * States
     * ------------------------------------------------------------------ */

    public static function states() {
        return array(
            self::STATE_DRAFT => array(
                'label' => esc_html(__('Draft', 'js-support-ticket')),
                'blurb' => esc_html(__('Being written. Not shown to customers and never used to answer anything.', 'js-support-ticket')),
                'live'  => false,
            ),
            self::STATE_REVIEW => array(
                'label' => esc_html(__('Waiting for review', 'js-support-ticket')),
                'blurb' => esc_html(__('Finished, and waiting for somebody other than its author to agree. Still not shown to customers.', 'js-support-ticket')),
                'live'  => false,
            ),
            self::STATE_PUBLISHED => array(
                'label' => esc_html(__('Published', 'js-support-ticket')),
                'blurb' => esc_html(__('Live on the site, and the AI may answer from it and cite it.', 'js-support-ticket')),
                'live'  => true,
            ),
            self::STATE_RETIRED => array(
                'label' => esc_html(__('Retired', 'js-support-ticket')),
                'blurb' => esc_html(__('Deliberately withdrawn. Kept so its history survives, but answered from by nothing.', 'js-support-ticket')),
                'live'  => false,
            ),
        );
    }

    /**
     * The stage to show for an article row.
     *
     * An empty workflow is an article nobody has classified yet; citeClause()
     * answers from it, so it is shown as published while it is active rather
     * than as a draft the AI is in fact quoting.
     */
    public static function stageOf($jsst_row) {
        $jsst_state = isset($jsst_row->workflow) ? (string) $jsst_row->workflow : '';
        if (array_key_exists($jsst_state, self::states())) return $jsst_state;
        return (isset($jsst_row->status) && (int) $jsst_row->status === 1) ? self::STATE_PUBLISHED : self::STATE_DRAFT;
    }

    /**
     * Save the Review box: stage, re-read interval and who it is for.
     *
     * Runs on the article form's own save, after storeArticle() has checked
     * the author may write it, and only when the box was on the form - the
     * same hook fires for deletes and list toggles.
     */
    public static function saveReviewBox($jsst_articleid) {
        if (JSSTrequest::getVar('kbreviewbox', 'post', '') !== '1') return;
        if (!self::governed()) return;
        $jsst_row = self::article($jsst_articleid);
        if (!$jsst_row) return;

        $jsst_every = (int) JSSTrequest::getVar('kbreviewevery', 'post', 0);
        $jsst_every = ($jsst_every > 0) ? min(3650, max(7, $jsst_every)) : 0;
        jssupportticket::$_db->update(self::table('articles'), array('reviewevery' => $jsst_every),
            array('id' => (int) $jsst_articleid), array('%d'), array('%d'));

        $jsst_audience = (string) JSSTrequest::getVar('kbaudience', 'post', self::FOR_EVERYONE);
        if (array_key_exists($jsst_audience, self::audiences())) {
            self::setAudience($jsst_articleid, $jsst_audience);
        }

        $jsst_stage = sanitize_key(JSSTrequest::getVar('kbstage', 'post', ''));
        if (!array_key_exists($jsst_stage, self::states())) return;
        if ((string) $jsst_row->workflow === '' && $jsst_stage === self::stageOf($jsst_row)) {
            /* Only recording what it already was. Going through setState()
               would count saving an old article as reading it, and quietly
               restart its re-read clock. */
            jssupportticket::$_db->update(self::table('articles'), array('workflow' => $jsst_stage),
                array('id' => (int) $jsst_articleid));
        } elseif ($jsst_stage !== (string) $jsst_row->workflow) {
            self::setState($jsst_articleid, $jsst_stage, get_current_user_id());
        }
    }

    public static function stateOf($jsst_articleid) {
        if (!self::governed()) return self::STATE_PUBLISHED;

        $jsst_row = self::article($jsst_articleid);
        if (!$jsst_row) return self::STATE_DRAFT;

        $jsst_state = (string) $jsst_row->workflow;
        return array_key_exists($jsst_state, self::states()) ? $jsst_state : self::STATE_DRAFT;
    }

    /**
     * Move an article to a state, keeping `status` in step.
     *
     * The two are written in a deliberate order. `status` is what every other
     * query in the product reads, so it goes last on the way up and first on
     * the way down: a half-failure then leaves an article invisible rather than
     * live-but-unapproved, and of the two ways to be wrong only one of them
     * shows a customer something nobody signed off.
     */
    public static function setState($jsst_articleid, $jsst_state, $jsst_who = 0) {
        if (!self::governed()) return false;
        if (!array_key_exists($jsst_state, self::states())) return false;

        $jsst_articleid = (int) $jsst_articleid;
        $jsst_articles  = jssupportticket::$_db->prefix . 'js_ticket_articles';
        $jsst_live      = !empty(self::states()[$jsst_state]['live']);

        if (!$jsst_live) {
            jssupportticket::$_db->update($jsst_articles, array('status' => 0),
                array('id' => $jsst_articleid), array('%d'), array('%d'));
        }

        $jsst_set = array('workflow' => $jsst_state);
        if ($jsst_state === self::STATE_PUBLISHED) {
            /* Publishing is a review. Somebody has just read it and said it is
               fit to go out, which is exactly what the freshness clock is
               measuring - not resetting it here would report an article as
               overdue the day it was approved. */
            $jsst_set['reviewedon'] = current_time('Y-m-d H:i:s');
            $jsst_set['reviewedby'] = (int) $jsst_who;
        }

        jssupportticket::$_db->update($jsst_articles, $jsst_set,
            array('id' => $jsst_articleid));

        if ($jsst_live) {
            jssupportticket::$_db->update($jsst_articles, array('status' => 1),
                array('id' => $jsst_articleid), array('%d'), array('%d'));
        }

        do_action('jsst_kb_state_changed', $jsst_articleid, $jsst_state, (int) $jsst_who);
        return true;
    }

    /* ------------------------------------------------------------------ *
     * Freshness
     * ------------------------------------------------------------------ */

    public static function reviewEvery() {
        $jsst_days = (int) get_option(self::OPT_REVIEW_EVERY, self::REVIEW_DAYS);
        return ($jsst_days > 0) ? $jsst_days : self::REVIEW_DAYS;
    }

    public static function staleRule() {
        $jsst_rule = get_option(self::OPT_STALE_RULE, self::STALE_WARN);
        return in_array($jsst_rule, array(self::STALE_WARN, self::STALE_REFUSE, self::STALE_IGNORE), true)
            ? $jsst_rule : self::STALE_WARN;
    }

    /**
     * When this article is next due a read, and whether that is in the past.
     *
     * Counted from the last review, falling back to when it was created -
     * because an article nobody has ever reviewed is the one most worth
     * flagging, and treating "never reviewed" as "reviewed just now" would hide
     * exactly the articles this is for.
     *
     * `reviewed` is when somebody actually read it and is 0 when nobody ever
     * has - kept separate from `from`, the date the clock is counted off,
     * because a screen printing the fallback as "last read" tells somebody an
     * article was checked in 2019 when in fact it was written in 2019 and has
     * never been looked at since. Those are the opposite finding.
     *
     * @return array due (timestamp), overdue (bool), days (int, negative when
     *               overdue), reviewed (timestamp or 0), from (timestamp).
     */
    public static function freshness($jsst_article) {
        $jsst_row = is_object($jsst_article) ? $jsst_article : self::article($jsst_article);
        $jsst_out = array('due' => 0, 'overdue' => false, 'days' => 0, 'reviewed' => 0, 'from' => 0);
        if (!$jsst_row) return $jsst_out;

        $jsst_every = (isset($jsst_row->reviewevery) && (int) $jsst_row->reviewevery > 0)
            ? (int) $jsst_row->reviewevery : self::reviewEvery();

        $jsst_everread = (!empty($jsst_row->reviewedon) && $jsst_row->reviewedon !== '0000-00-00 00:00:00');

        $jsst_from = $jsst_everread
            ? strtotime($jsst_row->reviewedon)
            : (!empty($jsst_row->created) ? strtotime($jsst_row->created) : 0);

        if ($jsst_from < 1) return $jsst_out;

        $jsst_out['from']     = $jsst_from;
        $jsst_out['reviewed'] = $jsst_everread ? $jsst_from : 0;
        $jsst_out['due']      = $jsst_from + ($jsst_every * DAY_IN_SECONDS);
        $jsst_out['overdue']  = ($jsst_out['due'] < time());
        $jsst_out['days']     = (int) floor(($jsst_out['due'] - time()) / DAY_IN_SECONDS);
        return $jsst_out;
    }

    /** Everything past its review date, worst first. */
    public static function overdue($jsst_limit = 100) {
        if (!self::governed()) return array();

        $jsst_articles = jssupportticket::$_db->prefix . 'js_ticket_articles';
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . $jsst_articles . "` WHERE workflow = %s ORDER BY id DESC LIMIT %d",
            self::STATE_PUBLISHED, (int) $jsst_limit * 4));

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_fresh = self::freshness($jsst_row);
            if (empty($jsst_fresh['overdue'])) continue;

            $jsst_row->jsst_freshness = $jsst_fresh;
            $jsst_out[] = $jsst_row;
        }

        usort($jsst_out, array(__CLASS__, 'byStaleness'));
        return array_slice($jsst_out, 0, (int) $jsst_limit);
    }

    private static function byStaleness($jsst_a, $jsst_b) {
        return ($jsst_a->jsst_freshness['days'] - $jsst_b->jsst_freshness['days']);
    }

    /* ------------------------------------------------------------------ *
     * The gate the AI asks
     * ------------------------------------------------------------------ */

    /**
     * The SQL that keeps ungoverned content out of an answer.
     *
     * Handed to `JSSTaisources` to fold into its own clause, so the rule is
     * enforced in the query rather than over its results - AI-02's reason,
     * which applies here twice over: the recall pool is a LIMIT, and a draft
     * that eats a place in it costs a published article its slot.
     *
     * Returns an empty string when there is nothing to say, which every caller
     * treats as "no restriction" rather than as an error.
     */
    public static function citeClause($jsst_alias = '') {
        if (!self::governed()) return '';

        $jsst_col = ($jsst_alias !== '') ? '`' . $jsst_alias . '`.' : '';
        $jsst_where = array();

        /* Drafts, articles waiting for review and retired ones, in one clause.
           An empty workflow is treated as published: it means the backfill has
           not reached that row yet, and refusing what has not been classified
           would take a site's corpus away during its own upgrade. */
        $jsst_where[] = jssupportticket::$_db->prepare(
            "(" . $jsst_col . "workflow = %s OR " . $jsst_col . "workflow = '')",
            self::STATE_PUBLISHED);

        if (self::staleRule() === self::STALE_REFUSE) {
            /* Computed in SQL rather than filtered afterwards. COALESCE because
               reviewevery is 0 for "use the site default" and reviewedon is
               NULL for "never reviewed" - and the second of those has to fall
               back to created, or an unreviewed article is treated as fresh
               forever, which is the opposite of the intent. */
            $jsst_where[] = jssupportticket::$_db->prepare(
                "DATE_ADD(COALESCE(" . $jsst_col . "reviewedon, " . $jsst_col . "created),
                          INTERVAL IF(" . $jsst_col . "reviewevery > 0, " . $jsst_col . "reviewevery, %d) DAY) >= UTC_TIMESTAMP()",
                self::reviewEvery());
        }

        return implode(' AND ', $jsst_where);
    }

    /**
     * May this one article be quoted at a customer?
     *
     * The per-document half of `citeClause()`, and the two must agree - the
     * clause is what keeps drafts out of the recall pool, this is the guard
     * three engines fall back on when they built their own query. AI-02's
     * `eligible()` calls it, which is what makes the rule apply to the free
     * suggestion path and the add-on's retrieval alike rather than only to
     * whichever one somebody remembered.
     *
     * Answers true on a site whose columns have not been added yet, which is
     * the failing-open direction: an upgrade in progress must not take a
     * desk's whole corpus away.
     */
    public static function citable($jsst_articleid) {
        if (!self::governed()) return true;

        /* An article that is not there is not a governance decision. This class
           has an opinion about rows it can see and none at all about anything
           else, and answering "no" for a missing id would make it the thing
           that decides whether a document exists - which is the source
           register's job, and which it already does. Retrieval cannot return a
           row that is absent anyway, so the only effect of refusing here would
           be to turn every "no such article" into a governance refusal on a
           screen that then explains it wrongly. */
        $jsst_row = self::article($jsst_articleid);
        if (!$jsst_row) return true;

        $jsst_state = (string) $jsst_row->workflow;
        if ($jsst_state !== '' && $jsst_state !== self::STATE_PUBLISHED) return false;

        if (self::staleRule() === self::STALE_REFUSE) {
            $jsst_fresh = self::freshness($jsst_row);
            if (!empty($jsst_fresh['overdue'])) return false;
        }

        return true;
    }

    /** Why not, in a sentence the retrieval console can print. */
    public static function whyNotCitable($jsst_articleid) {
        if (!self::governed()) return '';

        $jsst_row = self::article($jsst_articleid);
        if (!$jsst_row) return '';

        $jsst_state = (string) $jsst_row->workflow;
        if ($jsst_state !== '' && $jsst_state !== self::STATE_PUBLISHED) {
            $jsst_states = self::states();
            return sprintf(
                /* translators: %s: an article workflow state, for example "Draft" */
                esc_html(__('Not published: this article is %s.', 'js-support-ticket')),
                isset($jsst_states[$jsst_state]) ? $jsst_states[$jsst_state]['label'] : $jsst_state);
        }

        $jsst_fresh = self::freshness($jsst_row);
        if (self::staleRule() === self::STALE_REFUSE && !empty($jsst_fresh['overdue'])) {
            return sprintf(
                /* translators: %d: how many days past its review date the article is */
                esc_html(__('Overdue for review by %d days, and this site does not answer from articles nobody has checked.', 'js-support-ticket')),
                abs((int) $jsst_fresh['days']));
        }

        return '';
    }

    /**
     * How much an article's ranking is multiplied by, given how stale it is.
     *
     * The warn rule's whole implementation. An overdue article still answers -
     * it is usually still right - but it loses to a fresh one that covers the
     * same question, which over a few months is what moves a desk's answers
     * onto its maintained content without anybody having to do anything.
     */
    public static function weight($jsst_article) {
        if (self::staleRule() !== self::STALE_WARN) return 1.0;

        $jsst_fresh = self::freshness($jsst_article);
        return !empty($jsst_fresh['overdue']) ? self::STALE_WEIGHT : 1.0;
    }

    /* ------------------------------------------------------------------ *
     * Version history
     * ------------------------------------------------------------------ */

    /**
     * Write down what an article says right now.
     *
     * Called before a save overwrites it, so the revision is the *previous*
     * text - which is what somebody asking "what did this say in March" wants.
     * Skipped when nothing changed, because a save that touched only the
     * category should not produce a revision identical to the one before it.
     */
    public static function snapshot($jsst_articleid, $jsst_note = '', $jsst_who = 0) {
        if (!self::available()) return 0;

        $jsst_row = self::article($jsst_articleid);
        if (!$jsst_row) return 0;

        $jsst_last = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT subject, content FROM `" . self::table() . "`
              WHERE articleid = %d ORDER BY id DESC LIMIT 1", (int) $jsst_articleid));

        if ($jsst_last
            && (string) $jsst_last->subject === (string) $jsst_row->subject
            && (string) $jsst_last->content === (string) $jsst_row->content) {
            return 0;
        }

        jssupportticket::$_db->insert(self::table(), array(
            'articleid' => (int) $jsst_articleid,
            'subject'   => (string) $jsst_row->subject,
            'content'   => (string) $jsst_row->content,
            'savedby'   => $jsst_who > 0 ? (int) $jsst_who : get_current_user_id(),
            'note'      => jssupportticketphplib::JSST_substr((string) $jsst_note, 0, 255),
            'created'   => current_time('Y-m-d H:i:s'),
        ));

        return (int) jssupportticket::$_db->insert_id;
    }

    /**
     * The save hook.
     *
     * Fires at priority 5 so the snapshot is taken before anything else reacts
     * to the save. It records the text as it is *now*, which at this point is
     * already the new text - so what this actually preserves is every version
     * from the first save onwards, and the very first version of an article is
     * the one there is no revision for. That is the honest limit of hooking a
     * save rather than owning it, and it is stated here rather than papered
     * over: a citation to an article's first draft resolves to the article
     * itself, which is correct, because nothing has replaced it yet.
     */
    public static function onArticleSaved($jsst_articleid) {
        self::snapshot($jsst_articleid, esc_html(__('Saved', 'js-support-ticket')));
    }

    public static function revisions($jsst_articleid, $jsst_limit = 50) {
        if (!self::available()) return array();

        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, articleid, subject, savedby, note, created
               FROM `" . self::table() . "`
              WHERE articleid = %d ORDER BY id DESC LIMIT %d",
            (int) $jsst_articleid, (int) $jsst_limit));
    }

    /** One revision, in full. */
    public static function revision($jsst_revisionid) {
        if (!self::available()) return null;

        return jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE id = %d", (int) $jsst_revisionid));
    }

    /**
     * What this article said on a given day.
     *
     * The question the whole revisions table exists for. Returns the earliest
     * revision written *after* that moment - because a revision records what
     * the article was replaced with, so the version live at a point in time is
     * the one whose replacement came next. Falls back to the article as it
     * stands, which is right: no later revision means nothing has changed since.
     */
    public static function asOf($jsst_articleid, $jsst_when) {
        $jsst_when = is_numeric($jsst_when) ? (int) $jsst_when : strtotime((string) $jsst_when);
        if ($jsst_when < 1) return null;

        if (self::available()) {
            $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                "SELECT * FROM `" . self::table() . "`
                  WHERE articleid = %d AND created > %s
                  ORDER BY created ASC, id ASC LIMIT 1",
                (int) $jsst_articleid, gmdate('Y-m-d H:i:s', $jsst_when)));

            if ($jsst_row) return $jsst_row;
        }

        return self::article($jsst_articleid);
    }

    /* ------------------------------------------------------------------ *
     * Ownership
     * ------------------------------------------------------------------ */

    /**
     * Who is answerable for this article being right.
     *
     * Not its author. An article whose author left the company still needs
     * somebody to ask about it, and the commonest reason a knowledge base rots
     * is that every page's only named person is somebody who has moved on. The
     * author is kept in `staffid` and is the fallback.
     */
    public static function ownerOf($jsst_articleid) {
        if (!self::governed()) return 0;

        $jsst_row = self::article($jsst_articleid);
        if (!$jsst_row) return 0;

        $jsst_owner = isset($jsst_row->ownerid) ? (int) $jsst_row->ownerid : 0;
        return ($jsst_owner > 0) ? $jsst_owner : (int) $jsst_row->staffid;
    }

    public static function setOwner($jsst_articleid, $jsst_staffid) {
        if (!self::governed()) return false;

        jssupportticket::$_db->update(
            jssupportticket::$_db->prefix . 'js_ticket_articles',
            array('ownerid' => (int) $jsst_staffid),
            array('id' => (int) $jsst_articleid), array('%d'), array('%d'));
        return true;
    }

    /* ------------------------------------------------------------------ *
     * Who an article is for
     * ------------------------------------------------------------------ */

    /** Everybody the site would show it to. The default, and every existing article. */
    const FOR_EVERYONE = '';

    /** Only the people who answer tickets. Never shown to a customer, never quoted at one. */
    const FOR_STAFF = 'staff';

    public static function audiences() {
        return array(
            self::FOR_EVERYONE => array(
                'label' => esc_html(__('Customers and agents', 'js-support-ticket')),
                'blurb' => esc_html(__('Shown in the knowledge base and available to the AI when it answers somebody. Subject to the visibility setting above it.', 'js-support-ticket')),
            ),
            self::FOR_STAFF => array(
                'label' => esc_html(__('Agents only', 'js-support-ticket')),
                'blurb' => esc_html(__('An internal note, runbook or escalation path. Never shown to a customer and never quoted at one — including by the AI, which is the part that is easy to forget.', 'js-support-ticket')),
            ),
        );
    }

    /**
     * Who this article is for.
     *
     * NULL and the empty string both mean everybody, which is every article
     * that existed before this - reading a missing value as "agents only"
     * would take a desk's whole knowledge base off its own website.
     */
    public static function audienceOf($jsst_article) {
        if (!self::governed()) return self::FOR_EVERYONE;

        $jsst_row = is_object($jsst_article) ? $jsst_article : self::article($jsst_article);
        if (!$jsst_row) return self::FOR_EVERYONE;

        $jsst_value = isset($jsst_row->audience) ? (string) $jsst_row->audience : '';
        return ($jsst_value === self::FOR_STAFF) ? self::FOR_STAFF : self::FOR_EVERYONE;
    }

    public static function setAudience($jsst_articleid, $jsst_audience, $jsst_teamid = 0) {
        if (!self::governed()) return false;
        if (!array_key_exists((string) $jsst_audience, self::audiences())) return false;

        jssupportticket::$_db->update(self::table('articles'),
            array('audience' => (string) $jsst_audience, 'teamid' => ((int) $jsst_teamid > 0) ? (int) $jsst_teamid : null),
            array('id' => (int) $jsst_articleid));
        return true;
    }

    /**
     * May this be quoted to a customer?
     *
     * Every path in this product that answers *from* the knowledge base answers
     * a customer - the deflection panel, autopilot, chat. The Copilot is the
     * exception and it reads the ticket rather than the corpus. So "agents
     * only" means "never cited", with no per-caller argument to get wrong, and
     * an internal runbook cannot be repeated at somebody by a machine.
     */
    public static function citableToCustomer($jsst_articleid) {
        return (self::audienceOf($jsst_articleid) !== self::FOR_STAFF);
    }

    /** The clause that says the same thing. */
    public static function audienceClause($jsst_alias = '') {
        if (!self::governed()) return '';

        $jsst_col = ($jsst_alias !== '') ? '`' . $jsst_alias . '`.' : '';
        return jssupportticket::$_db->prepare(
            '(' . $jsst_col . 'audience IS NULL OR ' . $jsst_col . 'audience <> %s)', self::FOR_STAFF);
    }

    /* ------------------------------------------------------------------ *
     * What people search the knowledge base for
     * ------------------------------------------------------------------ */

    /** What people looked for and how often. Terms, not sentences. */
    const OPT_SEARCHES = 'jsst_kb_searches';

    /** How many distinct terms are worth keeping. */
    const MAX_TERMS = 300;

    /**
     * Note that somebody searched the knowledge base.
     *
     * Two different things happen with the two outcomes, and that is the whole
     * design. A search that **found** something is a popularity signal and is
     * counted - it says what your customers come here for. A search that found
     * **nothing** is not analytics, it is a missing article, and it goes to the
     * knowledge-gap register (6.0-AI-12) where it clusters with the same
     * question asked twenty other ways and turns into a brief somebody can
     * write from. Counting a failed search as a popular term would bury the
     * one finding worth having.
     */
    public static function searched($jsst_term, $jsst_found) {
        $jsst_term = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string) $jsst_term)));
        if ($jsst_term === '' || jssupportticketphplib::JSST_strlen($jsst_term) < 3) return false;

        /* The same switch that governs every other store of customer text. A
           search box is customer text like any other. */
        if (isset(jssupportticket::$_config['aiagent_analytics'])
            && jssupportticket::$_config['aiagent_analytics'] != 1) {
            return false;
        }

        if (!$jsst_found) {
            if (class_exists('JSSTaigaps')) {
                JSSTaigaps::record(JSSTaigaps::KIND_SEARCH, $jsst_term);
            }
            return true;
        }

        $jsst_key = jssupportticketphplib::JSST_strtolower(
            jssupportticketphplib::JSST_substr($jsst_term, 0, 80));

        $jsst_terms = get_option(self::OPT_SEARCHES, array());
        if (!is_array($jsst_terms)) $jsst_terms = array();

        $jsst_terms[$jsst_key] = isset($jsst_terms[$jsst_key]) ? ((int) $jsst_terms[$jsst_key]) + 1 : 1;

        /* Bounded, and the least-asked go first. An option that grows for the
           life of a site is one eventually measured in megabytes, and the tail
           of a search log is single hits nobody will ever act on. */
        if (count($jsst_terms) > self::MAX_TERMS) {
            arsort($jsst_terms);
            $jsst_terms = array_slice($jsst_terms, 0, self::MAX_TERMS, true);
        }

        update_option(self::OPT_SEARCHES, $jsst_terms, false);
        return true;
    }

    /** What people search for, most asked first. */
    public static function searchTerms($jsst_limit = 25) {
        $jsst_terms = get_option(self::OPT_SEARCHES, array());
        if (!is_array($jsst_terms)) return array();

        arsort($jsst_terms);
        return array_slice($jsst_terms, 0, (int) $jsst_limit, true);
    }

    /* ------------------------------------------------------------------ *
     * Reminding somebody
     * ------------------------------------------------------------------ */

    /** How often owners are told about their overdue articles. */
    const REMIND_HOOK = 'jsst_kb_freshness_reminder';

    /**
     * Tell each owner what of theirs has gone stale.
     *
     * The overdue list has existed since this class did, and until now nothing
     * told anybody it was there - which makes it a screen people visit once.
     * One e-mail per owner rather than one per article, because five separate
     * messages about five articles is how a useful reminder becomes a filter
     * rule.
     *
     * Sends nothing at all when there is nothing overdue. A weekly e-mail
     * saying "nothing needs you" trains people to delete it unread, and then
     * the one that matters goes with it.
     */
    public static function remindOwners() {
        if (!self::governed()) return 0;

        $jsst_overdue = self::overdue(200);
        if (empty($jsst_overdue)) return 0;

        $jsst_byowner = array();
        foreach ($jsst_overdue as $jsst_row) {
            $jsst_owner = self::ownerOf((int) $jsst_row->id);
            if ($jsst_owner < 1) continue;
            $jsst_byowner[$jsst_owner][] = $jsst_row;
        }

        $jsst_sent = 0;
        foreach ($jsst_byowner as $jsst_staffid => $jsst_rows) {
            $jsst_email = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT email FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff`
                  WHERE id = %d AND status = 1", (int) $jsst_staffid));

            if (!$jsst_email || !is_email($jsst_email)) continue;

            $jsst_lines = array();
            foreach (array_slice($jsst_rows, 0, 20) as $jsst_row) {
                $jsst_lines[] = '- ' . $jsst_row->subject . ' (' . sprintf(
                    /* translators: %d: how many days past its review date */
                    _n('%d day overdue', '%d days overdue', abs((int) $jsst_row->jsst_freshness['days']), 'js-support-ticket'),
                    abs((int) $jsst_row->jsst_freshness['days'])) . ')';
            }

            $jsst_body  = esc_html(__('These knowledge base articles are yours and are past their review date. The AI answers customers from them, so an out-of-date one is repeated rather than merely unread.', 'js-support-ticket'));
            $jsst_body .= "\n\n" . implode("\n", $jsst_lines) . "\n\n";
            $jsst_body .= esc_html(__('Reading one and pressing "Still correct" resets its clock:', 'js-support-ticket')) . "\n";
            $jsst_body .= admin_url('admin.php?page=knowledgebase&jstlay=kbgovernance');

            $jsst_subject = sprintf(
                /* translators: %d: how many of this person's articles are overdue */
                _n('%d of your knowledge base articles needs a read', '%d of your knowledge base articles need a read', count($jsst_rows), 'js-support-ticket'),
                count($jsst_rows));

            if (wp_mail($jsst_email, $jsst_subject, $jsst_body)) $jsst_sent++;
        }

        do_action('jsst_kb_reminded', $jsst_sent);
        return $jsst_sent;
    }

    /* ------------------------------------------------------------------ *
     * Search-engine controls
     * ------------------------------------------------------------------ */

    /**
     * The three SEO fields, cleaned.
     *
     * A slug is stored without being made unique, deliberately. Enforcing
     * uniqueness would mean silently renaming somebody's slug at save time,
     * and a slug that quietly became `refunds-2` is a published URL that
     * stopped working - the collision is worth reporting to a person rather
     * than resolving behind them. `duplicateSlugs()` is what reports it.
     */
    public static function setSeo($jsst_articleid, $jsst_seo) {
        if (!self::governed()) return false;

        $jsst_set = array();
        if (isset($jsst_seo['slug'])) {
            $jsst_set['slug'] = sanitize_title(jssupportticketphplib::JSST_substr((string) $jsst_seo['slug'], 0, 200));
        }
        if (isset($jsst_seo['canonical'])) {
            $jsst_url = esc_url_raw(trim((string) $jsst_seo['canonical']));
            $jsst_set['canonical'] = jssupportticketphplib::JSST_substr($jsst_url, 0, 255);
        }
        if (isset($jsst_seo['noindex'])) {
            $jsst_set['noindex'] = !empty($jsst_seo['noindex']) ? 1 : 0;
        }
        if (empty($jsst_set)) return false;

        jssupportticket::$_db->update(self::table('articles'), $jsst_set,
            array('id' => (int) $jsst_articleid));
        return true;
    }

    /**
     * Slugs more than one article claims.
     *
     * Reported rather than prevented, because two articles with the same slug
     * is a decision somebody made by accident and the fix depends on which of
     * them owns the address - which is not something this can know.
     */
    public static function duplicateSlugs() {
        if (!self::governed()) return array();

        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT slug, COUNT(*) AS jsst_n FROM `" . self::table('articles') . "`
              WHERE slug IS NOT NULL AND slug <> ''
              GROUP BY slug HAVING jsst_n > 1 ORDER BY jsst_n DESC LIMIT 50");

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[(string) $jsst_row->slug] = (int) $jsst_row->jsst_n;
        }
        return $jsst_out;
    }

    /* ------------------------------------------------------------------ *
     * Translations
     * ------------------------------------------------------------------ */

    /**
     * Say that one article is a translation of another.
     *
     * A pointer to the original rather than a group id, so there is always one
     * article that is the source and no way to express a cycle. Refuses to
     * point an article at itself, which is the one input that would make
     * `translations()` recurse forever.
     */
    public static function setTranslationOf($jsst_articleid, $jsst_originalid) {
        if (!self::governed()) return false;

        $jsst_articleid  = (int) $jsst_articleid;
        $jsst_originalid = (int) $jsst_originalid;
        if ($jsst_articleid < 1 || $jsst_articleid === $jsst_originalid) return false;

        /* And the original must not itself be a translation - two hops would
           mean the "original" of an article depends on which end you started
           from. If it is one, point at what it points at. */
        if ($jsst_originalid > 0) {
            $jsst_parent = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT translationof FROM `" . self::table('articles') . "` WHERE id = %d", $jsst_originalid));
            if ((int) $jsst_parent > 0 && (int) $jsst_parent !== $jsst_articleid) {
                $jsst_originalid = (int) $jsst_parent;
            }
        }

        jssupportticket::$_db->update(self::table('articles'),
            array('translationof' => ($jsst_originalid > 0) ? $jsst_originalid : null),
            array('id' => $jsst_articleid));
        return true;
    }

    /**
     * Every version of one article, whichever of them you name.
     *
     * The original plus its translations, in one list, so a writer editing the
     * French one can see that the English changed last week - which is the
     * whole reason to record the relationship at all.
     */
    public static function translations($jsst_articleid) {
        if (!self::governed()) return array();

        $jsst_articleid = (int) $jsst_articleid;
        $jsst_root = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT translationof FROM `" . self::table('articles') . "` WHERE id = %d", $jsst_articleid));

        $jsst_root = ((int) $jsst_root > 0) ? (int) $jsst_root : $jsst_articleid;

        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, subject, language, workflow, reviewedon
               FROM `" . self::table('articles') . "`
              WHERE id = %d OR translationof = %d ORDER BY id ASC",
            $jsst_root, $jsst_root));
    }

    /** Work out and record an article's language, the way AI-09 does elsewhere. */
    public static function detectLanguage($jsst_articleid) {
        if (!self::governed() || !class_exists('JSSTailanguages')) return '';

        $jsst_row = self::article($jsst_articleid);
        if (!$jsst_row) return '';

        $jsst_lang = JSSTailanguages::detectCode(
            $jsst_row->subject . ' ' . wp_strip_all_tags((string) $jsst_row->content));
        if ($jsst_lang === 'und') $jsst_lang = '';

        jssupportticket::$_db->update(self::table('articles'), array('language' => $jsst_lang),
            array('id' => (int) $jsst_articleid), array('%s'), array('%d'));

        return $jsst_lang;
    }

    /* ------------------------------------------------------------------ *
     * What an article is actually doing
     * ------------------------------------------------------------------ */

    /**
     * How much work one article has saved.
     *
     * Read from the two places that already record it rather than from a
     * counter of this class's own: the deflection log (shown / opened / said
     * to have solved it, written by the ticket form) and the AI decision log
     * (which articles an automatic answer was grounded in). Both existed before
     * this and neither was readable per article, which is why "which of these
     * is earning its keep" had no answer.
     *
     * @return array shown, opened, solved, cited.
     */
    public static function attribution($jsst_articleid, $jsst_days = 90) {
        $jsst_out = array('shown' => 0, 'opened' => 0, 'solved' => 0, 'cited' => 0);
        $jsst_articleid = (int) $jsst_articleid;
        if ($jsst_articleid < 1) return $jsst_out;

        $jsst_since = gmdate('Y-m-d H:i:s', time() - (max(1, (int) $jsst_days) * DAY_IN_SECONDS));
        $jsst_deflections = jssupportticket::$_db->prefix . 'js_ticket_ai_deflections';

        if (jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                'SHOW TABLES LIKE %s', $jsst_deflections)) === $jsst_deflections) {

            $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
                "SELECT action, COUNT(*) AS jsst_n FROM `" . $jsst_deflections . "`
                  WHERE source_type = 'kb' AND source_id = %d AND created >= %s
                  GROUP BY action", $jsst_articleid, $jsst_since));

            $jsst_map = array('view' => 'shown', 'click' => 'opened', 'solve' => 'solved');
            foreach ((array) $jsst_rows as $jsst_row) {
                if (isset($jsst_map[$jsst_row->action])) {
                    $jsst_out[$jsst_map[$jsst_row->action]] = (int) $jsst_row->jsst_n;
                }
            }
        }

        $jsst_out['cited'] = self::citations($jsst_articleid, $jsst_days);
        return $jsst_out;
    }

    /**
     * How often an automatic answer was grounded in this article.
     *
     * `kb_sources` is JSON on the decision row rather than a join table, so
     * this is a LIKE over a bounded window. That is honest about what it is:
     * a count for a screen, not a figure to bill anybody on - and it is why
     * the window is capped rather than open-ended.
     */
    public static function citations($jsst_articleid, $jsst_days = 90) {
        $jsst_decisions = jssupportticket::$_db->prefix . 'js_ticket_ai_decisions';
        if (jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                'SHOW TABLES LIKE %s', $jsst_decisions)) !== $jsst_decisions) {
            return 0;
        }

        $jsst_since = gmdate('Y-m-d H:i:s', time() - (max(1, (int) $jsst_days) * DAY_IN_SECONDS));

        /* Matched on the shape the citation is written in - "kb" and the id as
           a JSON value - rather than on the bare number, which would also match
           a coverage score or another source's id that happens to be the same. */
        $jsst_needle = '%"source_type":"kb"%"source_id":' . (int) $jsst_articleid . '%';

        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(*) FROM `" . $jsst_decisions . "`
              WHERE created >= %s AND kb_sources LIKE %s", $jsst_since, $jsst_needle));
    }

    /**
     * The articles doing the most work, and the ones doing none.
     *
     * Both halves matter and the second is the one nobody has: an article that
     * has never been shown, opened or cited in three months is either badly
     * titled or about something nobody asks - and until now the only way to
     * find it was to read all of them.
     */
    public static function impact($jsst_days = 90, $jsst_limit = 100) {
        if (!self::governed()) return array();

        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT id, subject, workflow FROM `" . self::table('articles') . "`
              WHERE workflow = %s OR workflow = '' ORDER BY id DESC LIMIT %d",
            self::STATE_PUBLISHED, (int) $jsst_limit));

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_row->jsst_attribution = self::attribution((int) $jsst_row->id, $jsst_days);
            $jsst_row->jsst_score = $jsst_row->jsst_attribution['solved'] * 3
                                  + $jsst_row->jsst_attribution['cited'] * 2
                                  + $jsst_row->jsst_attribution['opened'];
            $jsst_out[] = $jsst_row;
        }

        usort($jsst_out, array(__CLASS__, 'byImpact'));
        return $jsst_out;
    }

    private static function byImpact($jsst_a, $jsst_b) {
        return ($jsst_b->jsst_score - $jsst_a->jsst_score);
    }

    /* ------------------------------------------------------------------ *
     * The picture
     * ------------------------------------------------------------------ */

    /** The numbers over the governance screen. */
    public static function summary() {
        $jsst_out = array('total' => 0, 'published' => 0, 'draft' => 0, 'review' => 0,
                          'retired' => 0, 'overdue' => 0, 'unowned' => 0, 'governed' => false);

        $jsst_articles = jssupportticket::$_db->prefix . 'js_ticket_articles';
        if (jssupportticket::$_db->get_var("SHOW TABLES LIKE '" . esc_sql($jsst_articles) . "'") != $jsst_articles) {
            return $jsst_out;
        }

        $jsst_out['total'] = (int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . $jsst_articles . "`");

        if (!self::governed()) return $jsst_out;
        $jsst_out['governed'] = true;

        foreach ((array) jssupportticket::$_db->get_results(
                "SELECT workflow, COUNT(*) AS jsst_n FROM `" . $jsst_articles . "` GROUP BY workflow") as $jsst_row) {
            $jsst_key = ($jsst_row->workflow === '') ? self::STATE_PUBLISHED : (string) $jsst_row->workflow;
            if (isset($jsst_out[$jsst_key])) $jsst_out[$jsst_key] += (int) $jsst_row->jsst_n;
        }

        $jsst_out['overdue'] = count(self::overdue(500));
        $jsst_out['unowned'] = (int) jssupportticket::$_db->get_var(
            "SELECT COUNT(*) FROM `" . $jsst_articles . "` WHERE ownerid = 0 AND staffid = 0");

        return $jsst_out;
    }

    /** Weekly, not daily: an article a week overdue is not a daily emergency. */
    public static function scheduleReminder() {
        if (!wp_next_scheduled(self::REMIND_HOOK)) {
            wp_schedule_event(time() + DAY_IN_SECONDS, 'weekly', self::REMIND_HOOK);
        }
    }

    /** One article row, or nothing. */
    private static function article($jsst_articleid) {
        return jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_articles` WHERE id = %d",
            (int) $jsst_articleid));
    }
}
