<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * These classes are loaded from the plugin bootstrap with include_once. That
 * normally guarantees one declaration, but it deduplicates by resolved path, so
 * anything that reaches this file by a second spelling of the same path - or any
 * route that runs the bootstrap twice - redeclares the class and takes the whole
 * site down with a fatal. Returning early costs nothing and makes the file safe
 * to include however many times and by whatever route. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTpro')) {
    return;
}

/**
 * JS Help Desk Pro: one companion, one licence, one screen. (Roadmap 4.5-PRO-01)
 *
 * Until now a customer who wanted a working help desk bought a plugin and then
 * assembled it out of up to thirty-eight separate add-on plugins, each with its
 * own directory, its own bootstrap, its own activation, its own update check and
 * its own licence key pasted into its own box. Every one of those is a place an
 * upgrade can fail and a place a renewal can be missed, and the sum of them is
 * not a product — it is a parts bin.
 *
 * This class is the replacement. It holds three things and nothing else:
 *
 *  - The manifest. What "Pro" contains, as data rather than as a marketing page
 *    that drifts from what actually ships. Everything that needs to know what is
 *    in Pro — the screen below, the Free-vs-Pro matrix, the plan structure —
 *    reads it from here, so there is exactly one list to keep honest.
 *  - The licence. One key for the whole of Pro, checked once, against the same
 *    server the per-add-on keys were checked against. What it buys is an
 *    entitlement, not a download: a licensed site gets every module, because
 *    withholding features across bundles is the packaging problem itself.
 *  - The switch. Which modules this particular site wants running, in one
 *    settings namespace, changed from one screen.
 *
 * What it deliberately does not hold is the module code. The companion plugin
 * carries that, and core resolves into it — so core stays free, stays complete
 * on its own, and never needs to know how a module is implemented in order to
 * say whether it is on.
 *
 * The precedence, which matters more than anything else here: a legacy
 * stand-alone add-on that is still active always wins. A site that has been
 * running js-support-ticket-faq for four years keeps running it, keeps its data
 * and keeps its behaviour, and this class stands down for that module entirely.
 * Migrating those sites onto the companion is its own piece of work with its own
 * preview and rollback (Roadmap 4.5-PRO-02); nothing here may pre-empt it, and
 * in particular nothing here may leave a site with two implementations of one
 * feature both registering hooks.
 */
class JSSTpro {

    /** The one licence record. Everything about entitlement lives in it. */
    const OPTION_LICENCE = 'jsst_pro_licence';

    /**
     * The one settings namespace for which modules this site runs.
     *
     * Deliberately not 'jsst_pro_modules', which is the name of the filter that
     * extends the manifest — two different things wearing one name is how
     * somebody ends up filtering the wrong one.
     */
    const OPTION_MODULES = 'jsst_pro_module_state';

    /** The licensing server, the same one the per-add-on keys spoke to. */
    /*
     * The old licence endpoint is gone, not merely unused.
     *
     * It answered GET /setup/index.php?request=activate&token=... and returned
     * one yes-or-no for the single Pro companion. The 5.0.0 server is REST at
     * jshelpdesk/v1 and answers with a list of product slugs one key grants, which
     * is a different question with a different shape of answer.
     *
     * Leaving the constant behind would have left a live-looking URL for the
     * next person to wire something up to. JSSTlicense::server() is the only
     * address this plugin has now, and it is overridable for development.
     */

    /** The companion plugin's directory name, under WP_PLUGIN_DIR. */
    const COMPANION = 'js-support-ticket-pro';

    /**
     * The manifest slug of the Pro plugin itself.
     *
     * Every other row in the manifest is a feature that ships in an add-on of
     * its own; this one is the plugin that carries the features which had no
     * add-on to go back to - the API, webhooks, the work board, branding,
     * retention, analytics and the exports. It is listed because a customer
     * deciding what to buy needs to see it, and it is special-cased in exactly
     * one place, inventory(), where "your existing add-on owns this" would
     * otherwise be said about the Pro plugin itself. (Roadmap 4.5-PRO-01)
     */
    const MODULE_PRO = 'pro';

    /**
     * How long a site keeps working after the licence server stops answering.
     *
     * This is not generosity, it is correctness. A licence check is a network
     * call to somebody else's server, and networks fail: DNS goes, a firewall
     * rule changes, the server has a bad afternoon. If an unreachable server
     * counted as "not licensed", then the first thing a customer would notice
     * about our outage is their help desk quietly taking itself apart —
     * assignment rules gone, e-mail piping stopped, tickets arriving nowhere.
     * A failed check therefore changes nothing at all until it has been failing
     * for a fortnight, by which time it is a real problem rather than a blip and
     * the screen has been saying so for thirteen days.
     */
    const GRACE_DAYS = 14;

    /**
     * How often entitlement is re-checked. Daily is frequent enough to notice a
     * cancellation and rare enough that thirty thousand sites are not a load
     * problem.
     */
    const REFRESH_INTERVAL = DAY_IN_SECONDS;

    /**
     * The add-on slugs that were active as plugin directories in their own
     * right, captured before augment() added the Pro modules to the same list.
     *
     * Once augment() has run, jssupportticket::$_active_addons answers "is this
     * feature available?", which is what almost every caller in the plugin
     * actually wants. But two questions still need the original list: which
     * directory a module's files live in, and which legacy add-ons a migration
     * would have to deal with. Both would get the wrong answer from the
     * augmented array — it would send them looking for a plugin directory that
     * was never installed — so the original is kept here rather than recomputed,
     * because recomputing it after augmentation is impossible.
     */
    private static $jsst_legacy = null;

    /* ------------------------------------------------------------------ *
     * The manifest
     * ------------------------------------------------------------------ */

    /**
     * Every module Pro contains.
     *
     * group:   which heading it sits under on the screen.
     * label:   its name, as a customer would say it.
     * summary: one sentence, in the same voice as the rest of the product. This
     *          is what the Free-vs-Pro matrix will be generated from, so it has
     *          to describe the capability rather than sell it.
     *
     * The key is the module slug, which is also the legacy add-on's directory
     * suffix and the name getPluginPath() resolves — those three being the same
     * string is what lets a site move between the add-on and the companion
     * without anything else in the plugin noticing.
     *
     * Capabilities absorbed into the free core in 4.0 are deliberately absent:
     * they are free, JSSTmergedaddon owns them, and listing them here would put
     * a licence in front of something a customer already has.
     */
    private static $jsst_modules = array(

        /* Agents and the work of running a desk. */
        'agent' => array(
            'group'   => 'agents',
            'label'   => 'Agents',
            'summary' => 'Agents, agent roles, departments they can reach, per-agent permissions, teams and who can see what - and the collaboration that goes with them: watchers and secondary agents, notes restricted to named people, shared drafts and reply approvals, mentions and a handoff summary.',
        ),
        'mergeticket' => array(
            'group'   => 'agents',
            'label'   => 'Merge Tickets',
            'summary' => 'Fold a duplicate ticket into the one that is really being worked, keeping both conversations.',
        ),
        'timetracking' => array(
            'group'   => 'agents',
            'label'   => 'Time Tracking',
            'summary' => 'Time spent on a ticket, recorded by hand or by timer, and reportable per agent.',
        ),
        'privatecredentials' => array(
            'group'   => 'agents',
            'label'   => 'Private Credentials',
            'summary' => 'Logins a customer sends you, held apart from the conversation and shown only to agents.',
        ),

        /* Work that happens without anybody pressing anything. */
        'agentautoassign' => array(
            'group'   => 'automation',
            'label'   => 'Automatic Assignment',
            'summary' => 'Route an arriving ticket to an agent by department, load or round robin instead of by hand.',
        ),
        'autoclose' => array(
            'group'   => 'automation',
            'label'   => 'Ticket Auto Close and Automation',
            'summary' => 'Close tickets that have gone quiet, after a period you set, with warning first - and the general form of that: rules that act on the desk on their own, recipes for the common ones, approvals before anything reaches a customer, and tickets raised on a cadence.',
        ),
        'overdue' => array(
            'group'   => 'automation',
            'label'   => 'Overdue Tickets and Service Levels',
            'summary' => 'Due dates, overdue marking and escalation for tickets that have waited too long - and what that grew into: response and resolution targets counted in the desk\'s own working week, with warnings, escalation, tiers and an attainment report.',
        ),

        /* Everything paid that had no add-on of its own to go back to. */
        'pro' => array(
            'group'   => 'automation',
            'label'   => 'JS Help Desk Pro',
            'summary' => 'The REST API and its published documentation, webhooks in both directions with a delivery log, the background work board, branding and the installable portal, retention with legal holds and approved deletions, and analytics with scheduled XLSX and PDF exports.',
        ),

        /* Mail, in and out. */
        'emailpiping' => array(
            'group'   => 'email',
            'label'   => 'Email Piping',
            'summary' => 'Turn an inbox into the help desk: mail in becomes tickets and replies, over IMAP.',
        ),
        'emailcc' => array(
            'group'   => 'email',
            'label'   => 'Email CC',
            'summary' => 'Copy other people into a ticket and let them answer into it from their own mail.',
        ),
        'smtp' => array(
            'group'   => 'email',
            'label'   => 'SMTP',
            'summary' => 'Send through a real mail server rather than whatever the host provides.',
        ),
        'multilanguageemailtemplates' => array(
            'group'   => 'email',
            'label'   => 'Multilingual Email Templates',
            'summary' => 'One template per language, chosen by the language the customer is using.',
        ),

        /* What a customer can answer for themselves. */
        'knowledgebase' => array(
            'group'   => 'knowledge',
            'label'   => 'Knowledge Base',
            'summary' => 'Categorised articles, searchable, with agent-only and customer-facing visibility.',
        ),
        'faq' => array(
            'group'   => 'knowledge',
            'label'   => 'FAQ',
            'summary' => 'Short questions and answers, grouped, in front of the ticket form.',
        ),
        'download' => array(
            'group'   => 'knowledge',
            'label'   => 'Downloads',
            'summary' => 'Files customers can fetch for themselves, with a download record.',
        ),

        /* Answers proposed by software rather than written by an agent. */
        /* AI Powered Reply is listed as free rather than removed. (Roadmap
           6.0-AI-01) It was never a model - it searches the desk's own past
           replies - and core has carried the same two functions for releases,
           so it moved into the free core rather than into the AI Agent's
           licence. A site that bought it keeps everything it had; a site that
           did not now has it. Leaving the row here, marked free, is how a
           customer reading this table learns that. */
        'aipoweredreply' => array(
            'group'   => 'ai',
            'label'   => 'Similar past replies',
            'summary' => 'Offer an agent the replies your desk already wrote to tickets like this one. Free, and it sends nothing anywhere.',
        ),
        /* Its own plugin since 6.0-AI-02: `js-support-ticket-aiagent`, which
           replaced Instant Resolve outright rather than renaming it. */
        'aiagent' => array(
            'group'   => 'ai',
            'label'   => 'AI Agent',
            'summary' => 'Answer from your own documentation - suggestions while the customer types, grounded replies after, and automatic answers when you trust them.',
        ),

        /* Money: who has bought what, and what support that entitles them to. */
        'woocommerce' => array(
            'group'   => 'commerce',
            'label'   => 'WooCommerce',
            'summary' => 'Orders and products on the ticket, and support scoped to what the customer actually bought.',
        ),
        'easydigitaldownloads' => array(
            'group'   => 'commerce',
            'label'   => 'Easy Digital Downloads',
            'summary' => 'The same, for stores running Easy Digital Downloads.',
        ),
        'envatovalidation' => array(
            'group'   => 'commerce',
            'label'   => 'Envato Purchase Validation',
            'summary' => 'Check an Envato purchase code before the ticket is accepted.',
        ),
        'paidsupport' => array(
            'group'   => 'commerce',
            'label'   => 'Paid Support',
            'summary' => 'Charge for support: per ticket, by credit, or by plan.',
        ),

        /* What the customer sees and hears. */
        'feedback' => array(
            'group'   => 'experience',
            'label'   => 'Feedback',
            'summary' => 'Ask the customer how it went when a ticket closes, and report on the answers.',
        ),
        'multiform' => array(
            'group'   => 'experience',
            'label'   => 'Multiple Ticket Forms',
            'summary' => 'More than one ticket form, each with its own fields, for different kinds of request.',
        ),
        'announcement' => array(
            'group'   => 'experience',
            'label'   => 'Announcements',
            'summary' => 'Say something to everybody — an outage, a release — without answering it thirty times.',
        ),
        'widgets' => array(
            'group'   => 'experience',
            'label'   => 'Front-end Widgets',
            'summary' => 'Ticket form, ticket list and knowledge base as widgets and blocks anywhere on the site.',
        ),
        'notification' => array(
            'group'   => 'experience',
            'label'   => 'Desktop Notifications',
            'summary' => 'Browser notifications for agents. Superseded by the notification centre in a later release.',
        ),
        'mail' => array(
            'group'   => 'experience',
            'label'   => 'Internal Mail',
            'summary' => 'Messages between agents inside the help desk. Being replaced by ticket collaboration.',
        ),

        /* Everything else that talks to something outside. */
        'mailchimp' => array(
            'group'   => 'integrations',
            'label'   => 'Mailchimp',
            'summary' => 'Subscribe a customer to a Mailchimp list from the ticket form.',
        ),
    );

    /**
     * The headings, in the order the screen shows them. Most desks configure
     * top to bottom on the first afternoon, so the order is roughly the order
     * the decisions get made in: who answers, then what answers itself, then
     * how mail gets in, and so on out to the integrations nobody sets up on
     * day one.
     */
    private static $jsst_groups = array(
        'agents'       => 'Agents & Teams',
        'automation'   => 'Automation',
        'email'        => 'Email',
        'knowledge'    => 'Knowledge & Self-Service',
        'ai'           => 'AI',
        'commerce'     => 'Commerce',
        'experience'   => 'Customer Experience',
        'integrations' => 'Integrations',
    );

    /**
     * The manifest, filterable so a module can add itself without this file
     * being edited — which is what the companion does for anything shipped
     * after this release.
     */
    public static function modules() {
        return apply_filters('jsst_pro_modules', self::$jsst_modules);
    }

    public static function groups() {
        return apply_filters('jsst_pro_module_groups', self::$jsst_groups);
    }

    /**
     * One module's manifest entry, or false. Callers test the false rather than
     * indexing blind, because a slug can arrive from a request.
     */
    public static function module($jsst_slug) {
        $jsst_modules = self::modules();
        return isset($jsst_modules[$jsst_slug]) ? $jsst_modules[$jsst_slug] : false;
    }

    /* ------------------------------------------------------------------ *
     * The licence
     * ------------------------------------------------------------------ */

    /**
     * The licence record, always the same shape whether or not there is one.
     *
     * key:      what was entered. Never displayed in full; see maskedKey().
     * status:   'none', 'active', 'expired' or 'invalid' — what the server last
     *           said, not what is true this second. state() combines it with the
     *           clock and the grace window to get that.
     * plan:     the plan name the server reported, for display only. Nothing is
     *           gated on it: every paid plan gets every module, and the plans
     *           differ by site count, support and AI allowance instead.
     * sites:    how many sites the licence covers, and how many are using it.
     * expires:  the licence's own expiry, as a Y-m-d string or ''.
     * checked:  when the server last answered, as a timestamp. 0 means never.
     * failed:   when the server first stopped answering, as a timestamp. 0 means
     *           it is answering. This is what the grace window is measured from,
     *           and it is deliberately the *first* failure rather than the last:
     *           measuring from the last would restart the fortnight on every
     *           retry, and the grace would never end.
     * message:  the server's own words when something went wrong, shown as-is
     *           because a licence problem is usually a billing problem and our
     *           paraphrase of it helps nobody.
     */
    /* ---------------------------------------------------------------------
     * LICENSING NOW BELONGS TO JSSTlicense.
     *
     * This class kept two jobs that have nothing to do with each other: it was
     * the module registry AND the licence client. The registry is still here -
     * modules(), modulePath(), augment(), running(), inventory() and the legacy
     * bridge are what the rest of the plugin calls it for, thirty of the
     * fifty-eight call sites.
     *
     * The licence half is gone. It spoke the old protocol -
     * GET /setup/index.php?request=activate&token=... - which the 5.0.0 licence
     * server does not answer; that server is REST, at jshelpdesk/v1, and one key
     * grants a list of product slugs rather than unlocking a single companion.
     *
     * The methods below stay because fifty-eight call sites and an admin screen
     * use them, but they are now thin readings of JSSTlicense. Nothing here
     * talks to a server. Deleting them instead would have meant editing every
     * caller in the same change as swapping the protocol, which is two risky
     * things at once.
     * ------------------------------------------------------------------ */

    /**
     * The licence, in the shape this class has always returned it.
     *
     * JSSTlicense stores what the server said; this translates it to the older
     * vocabulary the admin screen and the call sites expect.
     */
    public static function licence() {
        if (!class_exists('JSSTlicense')) {
            return array('key' => '', 'status' => 'none', 'plan' => '', 'sites' => 0,
                'used' => 0, 'expires' => '', 'checked' => 0, 'failed' => 0);
        }

        $jsst_state = JSSTlicense::state();
        $jsst_key   = JSSTlicense::key();

        return array(
            'key'     => $jsst_key,
            'status'  => '' === $jsst_key ? 'none' : (string) $jsst_state['status'],
            'plan'    => (string) ($jsst_state['plan'] ?? ''),
            'sites'   => (int) ($jsst_state['sites_allowed'] ?? 0),
            'used'    => (int) ($jsst_state['sites_used'] ?? 0),
            'expires' => (string) $jsst_state['expires_at'],
            'checked' => (int) $jsst_state['checked'],
            'failed'  => (int) ($jsst_state['failed'] ?? 0),
        );
    }

    private static function storeLicence($jsst_licence) {
        update_option(self::OPTION_LICENCE, $jsst_licence, false);
    }

    /**
     * The licence key with everything but its last four characters replaced.
     *
     * A licence key is a credential. It is also the single thing a customer most
     * needs to recognise on this screen — "is that the right key?" — and the
     * last four characters answer that without putting a working key on a screen
     * that gets screenshotted into support tickets.
     */
    /** The key with everything but its tail hidden. */
    public static function maskedKey() {
        if (!class_exists('JSSTlicense')) {
            return '';
        }

        $jsst_key = JSSTlicense::key();

        if ('' === $jsst_key) {
            return '';
        }

        $jsst_tail = substr($jsst_key, -4);

        return str_repeat('&bull;', max(4, strlen($jsst_key) - 4)) . $jsst_tail;
    }

    /**
     * What the licence is actually worth right now.
     *
     * Returns one of:
     *   'none'    — no key has ever been entered.
     *   'active'  — checked, valid, not expired.
     *   'grace'   — was valid, the server has stopped answering, and we are
     *               still inside the window where that is our problem rather
     *               than the customer's. Everything keeps working.
     *   'expired' — the licence itself ran out, or the grace window did.
     *   'invalid' — the server rejected the key.
     *
     * The clock is checked here rather than trusted from the stored status,
     * because a licence that was active when it was last checked expires at
     * midnight whether or not anything ran.
     */
    /** Where this site stands: none, active, expired, invalid or grace. */
    public static function state() {
        if (!class_exists('JSSTlicense')) {
            return 'none';
        }

        $jsst_key = JSSTlicense::key();

        if ('' === $jsst_key) {
            return 'none';
        }

        $jsst_status = (string) JSSTlicense::state()['status'];

        if ('active' === $jsst_status) {
            return 'active';
        }

        if ('invalid' === $jsst_status || 'revoked' === $jsst_status) {
            return 'invalid';
        }

        /* Unreachable server on a site that was working is what the grace
           window is for, and JSSTlicense records it the same way. */
        return self::graceRemaining() > 0 ? 'grace' : 'expired';
    }

    /**
     * Is this site entitled to Pro? The only question the rest of the plugin
     * asks about licensing.
     */
    public static function licensed() {
        $jsst_state = self::state();
        return ($jsst_state === 'active' || $jsst_state === 'grace');
    }

    /**
     * How many days of grace are left, for the warning on the screen. Zero when
     * there is no grace running.
     */
    /**
     * Days left before an unreachable server stops being forgiven.
     *
     * The window exists for a site that WAS licensed and lost its connection,
     * never as a way to run paid modules by typing something into the box while
     * offline - so it counts from the last failure, and only when there was a
     * successful check before it.
     */
    public static function graceRemaining() {
        /* The window used to be computed here from a 'failed' timestamp that
           nothing ever wrote, so it was always zero and an outage read as an
           expired licence on the first failed check. JSSTlicense now records
           the failure and owns the arithmetic; this just asks it. */
        return class_exists('JSSTlicense') ? JSSTlicense::graceRemaining() : 0;
    }

    /**
     * Register a key against this site.
     *
     * @param  string $jsst_key
     * @return true|WP_Error
     */
    /** Activate a key. The conversation belongs to JSSTlicense. */
    public static function activate($jsst_key) {
        if (!class_exists('JSSTlicense')) {
            return new WP_Error('jsst_pro_noclient', esc_html(__('The licence client is unavailable.', 'js-support-ticket')));
        }

        return JSSTlicense::activate($jsst_key);
    }

    /**
     * Release the licence from this site so it can be used on another.
     *
     * The modules a site had switched on are deliberately left alone. Somebody
     * moving a licence between a staging site and a live one, or renewing a day
     * late, should not come back to a help desk that has forgotten which half of
     * itself was running.
     */
    /** Release this site from the licence. */
    public static function deactivate() {
        if (class_exists('JSSTlicense')) {
            JSSTlicense::deactivate();
        }
    }

    /**
     * Re-check entitlement. Called daily from cron, and by hand from the screen.
     *
     * @param bool $jsst_force Ignore the interval. The button on the screen sets
     *                         this; the cron does not.
     */
    /**
     * Re-ask the server.
     *
     * @param bool $jsst_force Ignored: JSSTlicense decides its own cadence, and
     *                         a caller asking twice in a minute is not a reason
     *                         to ask the server twice.
     */
    public static function refresh($jsst_force = false) {
        if (!class_exists('JSSTlicense')) {
            return false;
        }

        return JSSTlicense::refresh();
    }

    /**
     * Fold a server answer into the licence record.
     *
     * The server has grown its vocabulary over the years and speaks slightly
     * differently to each request type — 'verfication_status' to an activation,
     * 'status' to an expiry check, and the misspelling is the wire format so it
     * stays. Everything optional is read defensively: a server that has not been
     * taught to send a plan name must leave a site licensed, not unlicensed.
     */
    private static function applyAnswer(&$jsst_licence, $jsst_answer) {
        $jsst_ok = false;
        if (isset($jsst_answer['verfication_status'])) {
            $jsst_ok = ((int) $jsst_answer['verfication_status'] === 1);
        } elseif (isset($jsst_answer['status'])) {
            $jsst_ok = ((int) $jsst_answer['status'] === 1);
        }

        $jsst_licence['status'] = $jsst_ok ? 'active' : 'invalid';
        $jsst_licence['message'] = isset($jsst_answer['error']) ? (string) $jsst_answer['error'] : '';

        if (isset($jsst_answer['expirydate']) && $jsst_answer['expirydate'] !== '') {
            $jsst_licence['expires'] = gmdate('Y-m-d', strtotime((string) $jsst_answer['expirydate']));
            if ($jsst_ok && strtotime($jsst_licence['expires'] . ' 23:59:59') < time()) {
                $jsst_licence['status'] = 'expired';
            }
        }
        if (isset($jsst_answer['plan'])) {
            $jsst_licence['plan'] = sanitize_text_field((string) $jsst_answer['plan']);
        }
        if (isset($jsst_answer['sites'])) {
            $jsst_licence['sites'] = (int) $jsst_answer['sites'];
        }
        if (isset($jsst_answer['sites_used'])) {
            $jsst_licence['used'] = (int) $jsst_answer['sites_used'];
        }
    }

    /**
     * One request to the licensing server.
     *
     * @return array|WP_Error The decoded answer, or the reason there isn't one.
     */

    /* ------------------------------------------------------------------ *
     * The switch
     * ------------------------------------------------------------------ */

    /**
     * Which modules this site wants running, as slug => true.
     *
     * A slug that is not in the manifest is dropped on read rather than on
     * write, so a module removed from a future release stops being offered
     * without anybody having to clean up after it, and a module that comes back
     * finds its setting still there.
     */
    public static function chosen() {
        $jsst_stored = get_option(self::OPTION_MODULES, null);
        if (!is_array($jsst_stored)) {
            /* Nothing has been chosen yet. A site that has just licensed Pro
               wants Pro, not an empty list it has to tick twenty-seven boxes to
               fill in — "all modules enabled from a single screen" starts with
               them enabled. */
            return array_fill_keys(array_keys(self::modules()), true);
        }
        $jsst_chosen = array();
        foreach (self::modules() as $jsst_slug => $jsst_ignored) {
            $jsst_chosen[$jsst_slug] = !empty($jsst_stored[$jsst_slug]);
        }
        return $jsst_chosen;
    }

    /** Has this site asked for this module? Says nothing about entitlement. */
    public static function chose($jsst_slug) {
        $jsst_chosen = self::chosen();
        return !empty($jsst_chosen[$jsst_slug]);
    }

    /**
     * Record the whole set at once.
     *
     * The screen posts every module every time, so this takes the complete set
     * rather than a change: a checkbox that has been unticked posts nothing at
     * all, and a save that only looked at what arrived could never turn anything
     * off.
     *
     * @param array $jsst_slugs The slugs that should be on.
     */
    public static function choose($jsst_slugs) {
        $jsst_slugs = is_array($jsst_slugs) ? $jsst_slugs : array();
        $jsst_store = array();
        foreach (self::modules() as $jsst_slug => $jsst_ignored) {
            $jsst_store[$jsst_slug] = in_array($jsst_slug, $jsst_slugs, true);
        }
        update_option(self::OPTION_MODULES, $jsst_store, false);
    }

    /**
     * Turn one module on or off, leaving the rest alone.
     *
     * The screen posts the whole set and uses choose(); this is for the paths
     * that change exactly one module and must not disturb the others — a
     * migration switching on what it is about to take over, and its undo
     * putting that back. Reading the current set and writing it whole is what
     * keeps the stored value complete rather than accumulating a partial map.
     */
    public static function setChosen($jsst_slug, $jsst_on) {
        if (self::module($jsst_slug) === false) {
            return;
        }
        $jsst_chosen = self::chosen();
        $jsst_chosen[$jsst_slug] = (bool) $jsst_on;
        self::choose(array_keys(array_filter($jsst_chosen)));
    }

    /* ------------------------------------------------------------------ *
     * What is actually running
     * ------------------------------------------------------------------ */

    /**
     * Is the legacy stand-alone add-on for this module active as a plugin in its
     * own right?
     *
     * Asked against the list captured before augmentation, for the reason given
     * on $jsst_legacy: after augmentation the live array cannot tell a module
     * that has a directory from one that only has an entitlement.
     */
    public static function legacyActive($jsst_slug) {
        $jsst_legacy = (self::$jsst_legacy === null)
            ? (is_array(jssupportticket::$_active_addons) ? jssupportticket::$_active_addons : array())
            : self::$jsst_legacy;
        return in_array($jsst_slug, $jsst_legacy, true);
    }

    /** Every legacy add-on still installed as its own plugin. */
    public static function legacyAddons() {
        $jsst_legacy = (self::$jsst_legacy === null)
            ? (is_array(jssupportticket::$_active_addons) ? jssupportticket::$_active_addons : array())
            : self::$jsst_legacy;
        $jsst_found = array();
        foreach (self::modules() as $jsst_slug => $jsst_ignored) {
            if (in_array($jsst_slug, $jsst_legacy, true)) {
                $jsst_found[] = $jsst_slug;
            }
        }
        return $jsst_found;
    }

    /** Is the companion plugin installed and active? */
    public static function companionActive() {
        $jsst_legacy = (self::$jsst_legacy === null)
            ? (is_array(jssupportticket::$_active_addons) ? jssupportticket::$_active_addons : array())
            : self::$jsst_legacy;
        return in_array('pro', $jsst_legacy, true);
    }

    /** Where the companion's files are. */
    public static function companionPath() {
        return WP_PLUGIN_DIR . '/' . self::COMPANION . '/';
    }

    /**
     * Does the companion actually carry this module's code?
     *
     * Checked against the filesystem rather than against the manifest, because
     * the manifest says what Pro *is* and the companion says what this copy of
     * it *ships*. Those differ during a release, and resolving a module to a
     * directory that is not there is a fatal, not a missing feature.
     */
    public static function provides($jsst_slug) {
        if (!self::companionActive() || self::module($jsst_slug) === false) {
            return false;
        }
        return is_dir(self::companionPath() . 'modules/' . $jsst_slug);
    }

    /**
     * Is this site entitled to this particular module?
     *
     * A union, never an intersection. The Pro licence entitles a site to all of
     * them; a valid key for the module's own legacy add-on entitles it to that
     * one, whether or not a Pro key was ever entered. A customer who bought
     * Agents outright in 2021 keeps Agents — consolidating how we sell things
     * must never be the reason somebody loses something they have already paid
     * for. (Roadmap 4.5-PRO-02)
     *
     * Guarded on the class rather than assumed, because entitlement is asked on
     * every request through running() and a bootstrap that has lost one include
     * should cost a screen rather than the site.
     */
    /**
     * May this site run the module?
     *
     * Two independent grounds, and legacy is a floor rather than a ceiling: a
     * lifetime key bought under the old packaging keeps entitling what it
     * always did, whether or not a current licence is also present.
     */
    public static function entitled($jsst_slug) {
        if (class_exists('JSSTlicense') && JSSTlicense::grants($jsst_slug)) {
            return true;
        }

        if (self::licensed()) {
            return true;
        }

        return class_exists('JSSTlegacy') && JSSTlegacy::entitled($jsst_slug);
    }

    /**
     * Should this module be running, from the companion, right now?
     *
     * All four have to hold: the legacy add-on is not the one providing it, the
     * companion has the code, the site asked for it, and something entitles the
     * site to it.
     */
    public static function running($jsst_slug) {
        return !self::legacyActive($jsst_slug)
            && self::provides($jsst_slug)
            && self::chose($jsst_slug)
            && self::entitled($jsst_slug);
    }

    /**
     * Where a running Pro module's file lives, or '' if this module is not one.
     *
     * getPluginPath() asks this before anything else, so the companion's copy of
     * a module wins over core's fallback — and loses to a legacy add-on, because
     * running() has already said no in that case.
     *
     * The layout inside the companion is the core layout, not the add-on layout:
     * modules/<slug>/model.php rather than module/model.php. That is deliberate.
     * Thirty-eight add-ons each having their own directory shape is one of the
     * things this consolidation exists to end, and the companion is a
     * modular monolith, so it is shaped like the monolith.
     */
    public static function modulePath($jsst_slug, $jsst_type, $jsst_file_name = '') {
        if (!self::running($jsst_slug)) {
            return '';
        }
        $jsst_path = self::companionPath();
        switch ($jsst_type) {
            case 'file':
                $jsst_file = ($jsst_file_name !== '')
                    ? $jsst_path . 'modules/' . $jsst_slug . '/tpls/' . $jsst_file_name . '.php'
                    : $jsst_path . 'modules/' . $jsst_slug . '/controller.php';
                break;
            case 'model':
                $jsst_file = $jsst_path . 'modules/' . $jsst_slug . '/model.php';
                break;
            case 'class':
                $jsst_file = $jsst_path . 'includes/classes/' . $jsst_slug . '.php';
                break;
            case 'controller':
                $jsst_file = $jsst_path . 'modules/' . $jsst_slug . '/controller.php';
                break;
            case 'table':
                $jsst_file = $jsst_path . 'includes/tables/' . $jsst_slug . '.php';
                break;
            default:
                return '';
        }
        /* Returned whether or not the file is there. provides() has already
           established that the companion owns this module, and a module is owned
           whole — its model, its templates, its table class and its controller
           all come from one place or none of them do. Handing back core's copy
           of one file because the companion happened not to ship it is how two
           implementations of the same feature end up half-loaded together, which
           is a far worse failure than the missing file this would paper over. */
        return $jsst_file;
    }

    /**
     * Make every running Pro module answer to the availability checks that are
     * already written all over this plugin.
     *
     * There are several hundred `in_array('faq', jssupportticket::$_active_addons)`
     * tests in core, in the menus and in the templates, and every one of them
     * means "is this feature available?". Rewriting them all to ask a new
     * question would be a very large change with a very large number of places
     * to get one wrong, and a module missed would be a feature that silently
     * does not appear. Answering the question they already ask is the same
     * outcome with none of that risk: a licensed, enabled, provided module joins
     * the list, and every gate in the plugin starts letting it through at once.
     *
     * Called from the bootstrap immediately after the list is built, and before
     * anything has had a chance to read it.
     */
    public static function augment() {
        if (self::$jsst_legacy !== null) {
            return;
        }
        self::$jsst_legacy = is_array(jssupportticket::$_active_addons) ? jssupportticket::$_active_addons : array();
        if (!self::companionActive()) {
            return;
        }
        $jsst_available = self::$jsst_legacy;
        foreach (self::modules() as $jsst_slug => $jsst_ignored) {
            if (!in_array($jsst_slug, $jsst_available, true) && self::running($jsst_slug)) {
                $jsst_available[] = $jsst_slug;
            }
        }
        jssupportticket::$_active_addons = $jsst_available;

        /* Three of the add-ons tag their settings rows with something other than
           their slug, and the configuration screen decides what to show by
           looking those tags up. Those aliases used to be pushed into the array
           above, and that was a latent fatal rather than a convenience: two of
           the three tag their rows `email`, and this array is also what
           getPluginPath() reads as "there is a js-support-ticket-<slug>
           directory carrying this module" - so the alias sent core's own
           modules/email into a plugin directory that has never existed, and the
           next getJSModel('email') fataled. It never fired because the companion
           ships no module directories, so running() answers false for all of
           them and the alias list comes back empty; a bundle carrying Email CC
           would have woken it immediately.

           The tags are answered by JSSTbundle::settingsTagActive() now, which
           the configuration model asks directly, and this array is left meaning
           exactly one thing. (Roadmap 4.5-PRO-02, 6.5-ECO-01) */
    }

    /**
     * Every module with everything the screen needs to draw one row.
     *
     * The reason a module is not running matters more than the fact — "you have
     * not licensed this", "this is still coming from your old add-on" and "this
     * version of the companion does not carry it yet" all look identical as an
     * unticked box, and each wants a completely different thing done about it.
     *
     * @return array group key => array of rows
     */
    public static function inventory() {
        $jsst_rows = array();

        foreach (self::modules() as $jsst_slug => $jsst_module) {
            /* Every module ships in exactly one plugin, and the plugin is what
               the module's slug names - so "is it running?" is "is that plugin
               active?", asked once here. It used to be a four-way question,
               because a module could arrive either from its own add-on or from
               a companion carrying many; nothing carries many any more, so the
               three other answers were three ways of saying the same thing.
               (Roadmap 4.5-PRO-01) */
            $jsst_active = self::legacyActive($jsst_slug);
            $jsst_licensed = self::entitled($jsst_slug);

            /* Status first, in one ladder, and the note derived from it.
               These used to be decided together, and two ladders that have to
               agree eventually will not. */
            if ($jsst_active) {
                /* The Pro plugin's own row reads as running rather than as
                   "your existing add-on is in charge of it", which is what
                   every other row here means. */
                $jsst_status = ($jsst_slug === self::MODULE_PRO) ? 'on' : 'legacy';
            } elseif (!$jsst_licensed) {
                $jsst_status = 'unlicensed';
            } else {
                $jsst_status = 'missing';
            }

            switch ($jsst_status) {
                case 'legacy':
                    $jsst_note = __('Running from the add-on that ships it, which stays in charge of it. Nothing here changes that.', 'js-support-ticket');
                    break;
                case 'unlicensed':
                    $jsst_note = __('Included with Pro. Add your licence key above, then install the plugin that ships it.', 'js-support-ticket');
                    break;
                case 'missing':
                    /* Entitled, and not here. Naming the licence rather than
                       the missing plugin was the old failure of this branch:
                       the one thing the reader can act on is installing it. */
                    $jsst_note = __('Covered by your licence. Install and activate this plugin to run it.', 'js-support-ticket');
                    break;
                default:
                    /* Only worth a note when the entitlement came from somewhere
                       the reader may not remember: it answers "why is this one
                       available when I have not bought Pro?", and somebody who
                       does not know they still hold that key cannot make an
                       informed decision about renewing. Said only where it is
                       true - a plugin that is running without any entitlement
                       we can see is not evidence of a key somebody has
                       forgotten. (Roadmap 4.5-PRO-02) */
                    $jsst_note = (self::licensed() || !$jsst_licensed)
                        ? ''
                        : __('Covered by the licence key you already hold for this add-on.', 'js-support-ticket');
            }

            $jsst_rows[$jsst_module['group']][] = array(
                'slug'      => $jsst_slug,
                'label'     => $jsst_module['label'],
                'summary'   => $jsst_module['summary'],
                'status'    => $jsst_status,
                'note'      => $jsst_note,
                'chosen'    => $jsst_active,
                /* Never settable from here any more, and the ticked box is a
                   statement rather than a control. Switching a module on is
                   activating the plugin that ships it, which is done in
                   WordPress's own plugin screen - and a second switch beside it
                   could only ever disagree with that one. Every module is still
                   listed, because a customer deciding whether to buy needs to
                   see what they would get. (Roadmap 4.5-PRO-01) */
                'settable'  => false,
            );
        }
        return $jsst_rows;
    }

    /**
     * One line for the top of the screen and for System Status: how much of Pro
     * is actually running.
     */
    public static function summary() {
        $jsst_on = 0;
        $jsst_legacy = 0;
        foreach (array_keys(self::modules()) as $jsst_slug) {
            if (!self::legacyActive($jsst_slug)) {
                continue;
            }
            if ($jsst_slug === self::MODULE_PRO) {
                $jsst_on++;
            } else {
                $jsst_legacy++;
            }
        }
        return array(
            'total'  => count(self::modules()),
            'on'     => $jsst_on,
            'legacy' => $jsst_legacy,
            'state'  => self::state(),
        );
    }

    /**
     * The daily entitlement re-check. Registered from the bootstrap.
     *
     * Hooked to the schedule the plugin already runs rather than a new one: a
     * site with WP-Cron disabled and no replacement has bigger problems than a
     * stale licence, and adding a twelfth scheduled event to work around it
     * would not fix any of them.
     */
    public static function registerHooks() {
        /* Nothing to hook. This rode jsst_process_transation_key_status, the
           4.0.0 daily key check, which 5.0.0 no longer schedules; the licence
           is refreshed on JSSTlicense's own schedule. Kept because the
           bootstrap calls it. */
    }
}
