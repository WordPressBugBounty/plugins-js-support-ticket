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
if (class_exists('JSSTplans')) {
    return;
}

/**
 * What the plans are, and what separates them. (Roadmap 4.5-PRO-03)
 *
 * The old packaging asked a buyer to understand three bundles - Basic, Standard,
 * Professional - laid across thirty-eight add-ons, and then to work out which
 * combination contained the four things they actually needed. That is not a
 * pricing page, it is a puzzle, and the commonest way to solve it is to close
 * the tab.
 *
 * The replacement has one rule, and this class exists mainly to make that rule
 * enforceable rather than merely advertised:
 *
 *     Every paid plan contains every Pro module.
 *
 * Plans differ by how many sites they cover, what support comes with them, and
 * how much vendor-funded AI is included. Nothing else. There is deliberately no
 * per-plan module list here, and there is no way to write one: entitlement is
 * asked as "is this site licensed?" and never as "is this module in this plan?",
 * so a future attempt to withhold a feature from a cheaper plan would have to
 * add the concept back rather than fill in a column.
 *
 * The retired tiers are still recognised, because customers still hold them. A
 * Basic, Standard or Professional licence maps onto Pro - not onto a reduced
 * version of it - which is what "retire the feature tiers" has to mean if it is
 * not to be a euphemism for taking something away. A Professional customer's
 * renewal buys them the same modules it always did; a Basic customer's renewal
 * buys them considerably more.
 *
 * The comparison matrix is generated rather than written. Its rows come from the
 * two manifests this plugin already keeps - what was absorbed into the free core
 * (JSSTmergedaddon) and what Pro contains (JSSTpro) - so a module cannot ship
 * without appearing in the matrix, and cannot appear in the matrix without
 * shipping. A hand-written comparison table is a document that starts drifting
 * from the product the day it is published, and every contradiction a customer
 * finds in one costs more trust than the table ever earned.
 */
class JSSTplans {

    /**
     * The plans.
     *
     * price:   annual, in US dollars, as an integer. Displayed with a currency
     *          this plugin does not attempt to localise - a price is a fact
     *          about our billing, not about the reader's location, and a
     *          converted figure that is not what the checkout charges is worse
     *          than an unconverted one.
     * sites:   how many WordPress installations the licence covers.
     * support: the support level, as something a buyer can act on rather than a
     *          tier name they have to look up.
     * ai:      the included monthly vendor-funded AI allowance, in requests, on
     *          top of the customer's own key. Zero on Free, which is not a
     *          limitation: the Copilot works fully on Free with the customer's
     *          own provider key, and always will. The allowance is the thing we
     *          pay for, so it is the thing that scales with the plan.
     * legacy:  retired tier names that map onto this plan.
     */
    private static $jsst_plans = array(
        'free' => array(
            'label'   => 'Free',
            'price'   => 0,
            'sites'   => 1,
            'support' => 'Community forum',
            'ai'      => 0,
            'legacy'  => array(),
        ),
        'pro' => array(
            'label'   => 'Pro',
            'price'   => 99,
            'sites'   => 1,
            'support' => 'Email support',
            'ai'      => 250,
            /* Every retired tier lands here rather than being spread across the
               paid plans by what it used to cost. The tiers differed by which
               features they unlocked, and nothing differs by that any more, so
               there is no honest way to map them onto a site count they never
               specified. Mapping them all to the entry paid plan and letting
               every one of them keep every module is the reading that cannot
               leave anybody worse off than they were. */
            'legacy'  => array('basic', 'standard', 'professional', 'bundle', 'membership'),
        ),
        'business' => array(
            'label'   => 'Business',
            'price'   => 199,
            'sites'   => 5,
            'support' => 'Priority email support',
            'ai'      => 1000,
            'legacy'  => array(),
        ),
        'agency' => array(
            'label'   => 'Agency',
            'price'   => 349,
            'sites'   => 25,
            'support' => 'Priority support and onboarding',
            'ai'      => 4000,
            'legacy'  => array('agency-lifetime', 'lifetime'),
        ),
    );

    /** The plans, filterable so a plan can be added without editing this file. */
    public static function plans() {
        return apply_filters('jsst_plans', self::$jsst_plans);
    }

    /** The paid ones, in price order, for anywhere that is selling rather than reporting. */
    public static function paid() {
        $jsst_paid = self::plans();
        unset($jsst_paid['free']);
        return $jsst_paid;
    }

    /** One plan, or false. Callers test the false; a key can arrive from a server. */
    public static function plan($jsst_key) {
        $jsst_plans = self::plans();
        return isset($jsst_plans[$jsst_key]) ? $jsst_plans[$jsst_key] : false;
    }

    /**
     * Turn whatever the licence server called this licence into a plan key.
     *
     * The server has been describing licences for years and has called the same
     * thing several different things in that time - tier names, bundle names,
     * capitalised, hyphenated, with and without the product name in front. All
     * of them are matched loosely, because the alternative is a customer whose
     * licence is perfectly valid seeing "Free" on this screen because our own
     * server used a word this function had not been taught.
     *
     * Anything unrecognised on a licensed site becomes 'pro' rather than 'free'.
     * That is the safe direction: a licensed site being shown the entry paid
     * plan understates what they have, while showing them Free would tell a
     * paying customer they had not paid.
     */
    public static function normalise($jsst_name, $jsst_licensed = true) {
        $jsst_key = strtolower(trim((string) $jsst_name));
        $jsst_key = str_replace(array(' ', '_'), '-', $jsst_key);
        $jsst_key = str_replace(array('js-help-desk-', 'jshelpdesk-', 'helpdesk-'), '', $jsst_key);

        if ($jsst_key === '') {
            return $jsst_licensed ? 'pro' : 'free';
        }
        foreach (self::plans() as $jsst_plankey => $jsst_plan) {
            if ($jsst_key === $jsst_plankey || in_array($jsst_key, $jsst_plan['legacy'], true)) {
                return $jsst_plankey;
            }
        }
        /* A partial match last, and only against the plan keys — 'pro-annual'
           and 'business-5-site' are the shapes a billing system produces, and
           refusing to recognise them would be pedantry at the customer's
           expense. Checked after the exact pass so that a legacy name
           containing a plan key cannot be claimed by the wrong plan. */
        foreach (array_keys(self::plans()) as $jsst_plankey) {
            if ($jsst_plankey !== 'free' && strpos($jsst_key, $jsst_plankey) !== false) {
                return $jsst_plankey;
            }
        }
        return $jsst_licensed ? 'pro' : 'free';
    }

    /**
     * Which plan this site is actually on.
     *
     * Derived rather than stored, from the licence JSSTpro holds. Storing it
     * would make it a third thing that can be out of date, and the answer is
     * only ever wanted on a screen.
     */
    public static function current() {
        if (!class_exists('JSSTpro') || !JSSTpro::licensed()) {
            return 'free';
        }
        $jsst_licence = JSSTpro::licence();
        return self::normalise($jsst_licence['plan'], true);
    }

    /** The current plan's record, always a real one. */
    public static function currentPlan() {
        $jsst_plan = self::plan(self::current());
        return ($jsst_plan !== false) ? $jsst_plan : self::plan('free');
    }

    /**
     * How many sites this licence covers, and how many are using it.
     *
     * The server's own numbers win where it has given them: it is the only thing
     * that can see the other sites. The plan's figure is the fallback, for a
     * licence activated before the server started reporting seats.
     */
    public static function sites() {
        $jsst_plan = self::currentPlan();
        $jsst_allowed = (int) $jsst_plan['sites'];
        $jsst_used = 1;

        if (class_exists('JSSTpro')) {
            $jsst_licence = JSSTpro::licence();
            if ((int) $jsst_licence['sites'] > 0) {
                $jsst_allowed = (int) $jsst_licence['sites'];
            }
            if ((int) $jsst_licence['used'] > 0) {
                $jsst_used = (int) $jsst_licence['used'];
            }
        }
        return array('allowed' => $jsst_allowed, 'used' => $jsst_used);
    }

    /**
     * Is this licence on more sites than it covers?
     *
     * Reported, never enforced here. Switching a customer's help desk off
     * because their agency put the licence on a sixth site is a support ticket
     * we would deserve and a churn event we would not survive twice; the seat
     * count is a billing conversation, and this returns the fact so a screen can
     * start one politely.
     */
    public static function overSites() {
        $jsst_sites = self::sites();
        return ($jsst_sites['used'] > $jsst_sites['allowed']);
    }

    /**
     * The plan a site would need for the number of sites it is actually using.
     * Used to make the "you are over" message actionable rather than merely true.
     */
    public static function planForSites($jsst_count) {
        $jsst_best = false;
        foreach (self::paid() as $jsst_key => $jsst_plan) {
            if ($jsst_plan['sites'] >= $jsst_count) {
                if ($jsst_best === false || $jsst_plan['price'] < self::plan($jsst_best)['price']) {
                    $jsst_best = $jsst_key;
                }
            }
        }
        return $jsst_best;
    }

    /* ------------------------------------------------------------------ *
     * The comparison matrix
     * ------------------------------------------------------------------ */

    /**
     * Free against Pro, generated from what the software actually contains.
     *
     * Both halves come from a manifest rather than from a list written for this
     * table. The free half is JSSTmergedaddon's map, which is the record of what
     * stopped being an add-on and became part of the free core — it exists
     * because the code needs it, so it cannot quietly stop being true. The paid
     * half is JSSTpro's manifest, for the same reason.
     *
     * The consequence worth stating: this table cannot flatter us. It has no
     * room for a feature we have not shipped and no way to omit one we have.
     *
     * @return array of array(label, group, free, pro)
     */
    public static function matrix() {
        $jsst_rows = array();
        $jsst_seen = array();

        if (class_exists('JSSTmergedaddon')) {
            foreach (JSSTmergedaddon::map() as $jsst_slug => $jsst_merged) {
                $jsst_seen[$jsst_slug] = true;
                $jsst_rows[] = array(
                    'slug'  => $jsst_slug,
                    'label' => $jsst_merged['label'],
                    'group' => 'core',
                    'free'  => true,
                    'pro'   => true,
                );
            }
        }
        if (class_exists('JSSTpro')) {
            foreach (JSSTpro::modules() as $jsst_slug => $jsst_module) {
                /* A capability that has been absorbed into the free core is
                   still a row in JSSTpro's manifest, because that manifest is
                   also what reports whether the old plugin is installed on this
                   site. Listing it in both halves would put the same feature in
                   the table twice with contradictory ticks - free above, paid
                   below - which is worse than either answer on its own. The
                   free half wins, because that is the one that is true.
                   (Found merging AI Powered Reply, Roadmap 6.0-AI-01.) */
                if (isset($jsst_seen[$jsst_slug])) {
                    continue;
                }
                $jsst_rows[] = array(
                    'slug'  => $jsst_slug,
                    'label' => $jsst_module['label'],
                    'group' => $jsst_module['group'],
                    'free'  => false,
                    'pro'   => true,
                );
            }
        }
        return apply_filters('jsst_plan_matrix', $jsst_rows);
    }

    /**
     * The matrix grouped for display, free first.
     *
     * The free rows are shown as one group under their own heading rather than
     * mixed in, because the single most useful thing this table can tell a
     * reader is how much they get without paying anything — and a reader
     * scanning a column of ticks for the one row that differs will not find it.
     */
    public static function groupedMatrix() {
        $jsst_groups = array('core' => __('Included free', 'js-support-ticket'));
        if (class_exists('JSSTpro')) {
            foreach (JSSTpro::groups() as $jsst_key => $jsst_label) {
                $jsst_groups[$jsst_key] = $jsst_label;
            }
        }
        $jsst_grouped = array();
        foreach (self::matrix() as $jsst_row) {
            $jsst_grouped[$jsst_row['group']][] = $jsst_row;
        }

        $jsst_ordered = array();
        foreach ($jsst_groups as $jsst_key => $jsst_label) {
            if (!empty($jsst_grouped[$jsst_key])) {
                $jsst_ordered[$jsst_key] = array('label' => $jsst_label, 'rows' => $jsst_grouped[$jsst_key]);
            }
        }
        return $jsst_ordered;
    }

    /* =====================================================================
     * What we promise about the price
     * ---------------------------------------------------------------------
     * The guarantees and the launch offer. (Roadmap 4.5-MKT-03, 4.5-MKT-04)
     *
     * These are commercial facts rather than product facts, and they are kept
     * here for the same reason the matrix is generated rather than typed: a
     * promise that lives only on a marketing page is a promise the product
     * cannot be checked against. Somebody looking at the screen where their
     * licence is entered can read what renewing costs and what happens to the
     * tier they already hold, without leaving wp-admin to find out.
     *
     * Everything here is filterable, because the numbers belong to whoever
     * sells this and will change without the code changing.
     * ================================================================== */

    /**
     * The four commercial guarantees. (Roadmap 4.5-MKT-03)
     *
     * Written as short claims rather than paragraphs of terms, because each of
     * them has to be checkable against what the code actually does:
     *
     * - the renewal price is the list price, and it is shown beside the offer
     *   price wherever the offer is shown - see offer();
     * - the founding price lock is a promise about renewals rather than a
     *   discount, so it is stated as a term and not as a number;
     * - grandfathering is not really a promise here at all, it is behaviour:
     *   normalise() maps every retired tier onto Pro, and JSSTpro::entitled()
     *   is a union, so an old add-on key keeps entitling a site to that add-on
     *   whether or not a Pro key is ever entered (Roadmap 4.5-PRO-02);
     * - lifetime licences are closed to new sales and honoured where they were
     *   sold, which is why 'lifetime' and 'agency-lifetime' are still in the
     *   plan table above rather than deleted from it.
     */
    private static $jsst_terms = array(
        array(
            'key'   => 'renewal',
            'label' => 'The renewal price is the price',
            'text'  => 'A licence renews at the list price shown above. Where a first-year offer applies, the renewal price is displayed beside it before you buy, not afterwards.',
        ),
        array(
            'key'   => 'pricelock',
            'label' => 'Founding price lock',
            'text'  => 'Customers who move to unified Pro during its first year keep their renewal price for as long as the licence stays active, even when list prices rise.',
        ),
        array(
            'key'   => 'grandfathered',
            'label' => 'Every existing licence is equal or better',
            'text'  => 'A Basic, Standard or Professional licence becomes Pro at renewal and keeps every module it ever had; and every add-on key you already hold keeps entitling you to that add-on, whether or not you buy Pro. Consolidating how we sell things is not a reason for anybody to lose what they have paid for.',
        ),
        array(
            'key'   => 'lifetime',
            'label' => 'No new lifetime licences',
            'text'  => 'Lifetime licences are no longer sold. Every lifetime licence already sold is honoured in full: a promise made once is not withdrawn because it turned out to be expensive.',
        ),
    );

    /** The commercial guarantees, filterable. (Roadmap 4.5-MKT-03) */
    public static function terms() {
        return apply_filters('jsst_plan_terms', self::$jsst_terms);
    }

    /**
     * The launch offer, or false when there is not one running.
     * (Roadmap 4.5-MKT-04)
     *
     * `ends` is what makes this an offer rather than urgency. An empty date
     * means there is no offer and a past date means it is over; in both cases
     * every screen and the feed go quiet and show plain prices. There is
     * deliberately no way to express "25% off, ending soon, for ever", which is
     * the thing the roadmap asked this not to become - a permanent countdown
     * teaches customers to wait for the next one and costs more than it makes.
     *
     * The renewal price is computed rather than stated: it is the plan's list
     * price, which is the only number a renewal can honestly be quoted at.
     *
     * @return array|false
     */
    public static function offer() {
        $jsst_offer = apply_filters('jsst_plan_offer', array(
            'percent' => 25,
            'ends'    => '',
            'label'   => 'Launch offer',
            'who'     => 'Free users and anybody moving from another help desk, on a first year.',
            'code'    => '',
        ));

        if (!is_array($jsst_offer) || empty($jsst_offer['ends'])) {
            return false;
        }

        $jsst_percent = (int) $jsst_offer['percent'];
        if ($jsst_percent < 1 || $jsst_percent > 90) {
            /* A discount outside this range is a mistake somewhere - a fraction
               written as a percentage, or the other way about - and a price
               quoted from it would be worse than quoting no price at all. */
            return false;
        }

        $jsst_ends = strtotime($jsst_offer['ends'] . ' 23:59:59');
        if (!$jsst_ends || $jsst_ends < current_time('timestamp')) {
            return false;
        }

        $jsst_offer['percent'] = $jsst_percent;
        $jsst_offer['prices'] = array();
        foreach (self::paid() as $jsst_key => $jsst_plan) {
            $jsst_list = (int) $jsst_plan['price'];
            $jsst_offer['prices'][$jsst_key] = array(
                'label'     => $jsst_plan['label'],
                'firstyear' => (int) round($jsst_list * (100 - $jsst_percent) / 100),
                'renewal'   => $jsst_list,
            );
        }
        return $jsst_offer;
    }

    /* =====================================================================
     * Publishing it
     *
     * The matrix is generated from the manifest, which is what stops it
     * flattering us or going stale - but a table that only exists inside
     * somebody's wp-admin is not published. These turn it into the artefact a
     * website can actually take: paste one of them onto a page, or point the
     * site at the endpoint and let it fetch the current answer for ever.
     *
     * What this deliberately does not do is write to anybody's website. A
     * plugin that edits pages on a marketing site is a plugin nobody installs
     * twice. The last step stays with a person; what is removed is the
     * retyping, which is where drift comes from. (Roadmap 4.5-MKT-02)
     * ================================================================== */

    /** The AJAX action the matrix is served on. */
    const FEED = 'jsst_plan_matrix';

    public static function registerHooks() {
        add_action('wp_ajax_' . self::FEED, array(__CLASS__, 'serve'));
        add_action('wp_ajax_nopriv_' . self::FEED, array(__CLASS__, 'serve'));
    }

    /** Where a website can fetch it. */
    public static function feedUrl() {
        return add_query_arg('action', self::FEED, admin_url('admin-ajax.php'));
    }

    /**
     * Serve the matrix as JSON.
     *
     * Public and cross-origin readable, which is a deliberate decision rather
     * than an oversight: this is the list of features in a product, it is on
     * the pricing page already, and it contains nothing about the site serving
     * it - no tickets, no customers, no licence key, not even which plan this
     * site is on. Requiring a key to read a public feature list would only
     * mean the marketing site could not use it.
     */
    public static function serve() {
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo wp_json_encode(self::export('array'));
        exit;
    }

    /**
     * The matrix in a shape something else can use.
     *
     * @param string $jsst_format array, json, markdown or html
     */
    public static function export($jsst_format = 'array') {
        $jsst_counts = self::counts();
        $jsst_data = array(
            'generated' => gmdate('c'),
            'version'   => isset(jssupportticket::$_config['versioncode']) ? jssupportticket::$_config['versioncode'] : '',
            'counts'    => $jsst_counts,
            'groups'    => array(),
        );
        foreach (self::groupedMatrix() as $jsst_key => $jsst_group) {
            $jsst_rows = array();
            foreach ($jsst_group['rows'] as $jsst_row) {
                $jsst_rows[] = array(
                    'slug'  => $jsst_row['slug'],
                    'label' => $jsst_row['label'],
                    'free'  => !empty($jsst_row['free']),
                    'pro'   => !empty($jsst_row['pro']),
                );
            }
            $jsst_data['groups'][] = array('key' => $jsst_key, 'label' => $jsst_group['label'], 'rows' => $jsst_rows);
        }
        /* The commercial half. A website taking this feed gets the guarantees
           and, while one is running, the offer with its renewal price - so the
           pricing page cannot show an offer whose terms have changed here.
           (Roadmap 4.5-MKT-03, 4.5-MKT-04) */
        $jsst_data['terms'] = self::terms();
        $jsst_offer = self::offer();
        if ($jsst_offer !== false) {
            $jsst_data['offer'] = $jsst_offer;
        }

        if ($jsst_format === 'array') {
            return $jsst_data;
        }
        if ($jsst_format === 'json') {
            return wp_json_encode($jsst_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        if ($jsst_format === 'markdown') {
            return self::asMarkdown($jsst_data);
        }
        return self::asHtml($jsst_data);
    }

    /** For a documentation site or a README. */
    private static function asMarkdown($jsst_data) {
        $jsst_out = '| ' . __('Feature', 'js-support-ticket') . ' | ' . __('Free', 'js-support-ticket')
            . ' | ' . __('Pro', 'js-support-ticket') . " |\n|---|:--:|:--:|\n";
        foreach ($jsst_data['groups'] as $jsst_group) {
            $jsst_out .= '| **' . $jsst_group['label'] . "** | | |\n";
            foreach ($jsst_group['rows'] as $jsst_row) {
                $jsst_out .= '| ' . $jsst_row['label'] . ' | ' . ($jsst_row['free'] ? 'yes' : '—')
                    . ' | ' . ($jsst_row['pro'] ? 'yes' : '—') . " |\n";
            }
        }
        return $jsst_out;
    }

    /** For pasting straight onto a page. */
    private static function asHtml($jsst_data) {
        $jsst_out = '<table class="jsst-plan-matrix">' . "\n" . '<thead><tr><th>'
            . esc_html(__('Feature', 'js-support-ticket')) . '</th><th>'
            . esc_html(__('Free', 'js-support-ticket')) . '</th><th>'
            . esc_html(__('Pro', 'js-support-ticket')) . '</th></tr></thead>' . "\n" . '<tbody>' . "\n";
        foreach ($jsst_data['groups'] as $jsst_group) {
            $jsst_out .= '<tr class="jsst-plan-group"><th colspan="3">' . esc_html($jsst_group['label']) . '</th></tr>' . "\n";
            foreach ($jsst_group['rows'] as $jsst_row) {
                $jsst_out .= '<tr><td>' . esc_html($jsst_row['label']) . '</td><td>'
                    . ($jsst_row['free'] ? '&#10003;' : '&mdash;') . '</td><td>'
                    . ($jsst_row['pro'] ? '&#10003;' : '&mdash;') . '</td></tr>' . "\n";
            }
        }
        return $jsst_out . '</tbody>' . "\n" . '</table>' . "\n";
    }

    /** How many features each side of the matrix has, for the line above it. */
    public static function counts() {
        $jsst_counts = array('free' => 0, 'pro' => 0);
        foreach (self::matrix() as $jsst_row) {
            if (!empty($jsst_row['free'])) {
                $jsst_counts['free']++;
            }
            if (!empty($jsst_row['pro'])) {
                $jsst_counts['pro']++;
            }
        }
        return $jsst_counts;
    }
}
