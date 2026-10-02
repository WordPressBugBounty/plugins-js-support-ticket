<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * What the AI costs, and the caps that stop it costing more. (Roadmap 6.0-AI-07)
 *
 * AI is the only part of this product with a variable cost per use. Everything
 * else a site switches on is paid for once; this is billed by the token, on
 * somebody else's meter, by code that runs under cron while nobody is watching.
 * A help desk that answers four hundred tickets on a bad Tuesday is a help desk
 * that spent four hundred times whatever one answer costs, and the first anyone
 * knows about it is the provider's invoice.
 *
 * So this class does three things, and the order matters:
 *
 *   It **counts** every model call the product makes, in one table, whichever
 *   feature made it. Before this the accounting was in three places that could
 *   not be added up - Zywrap's own usage table, the Copilot's last-fifty option
 *   log, and the autopilot decision log, which records no tokens at all.
 *
 *   It **refuses** once a cap is reached. guard() is asked before the call, not
 *   after: a budget enforced after the money is spent is a report.
 *
 *   It **says what happened**, on one screen, in money where money is what is
 *   being spent and in requests where the plan allowance is what is being used.
 *
 * ## The three ways a call is paid for, which are not interchangeable
 *
 *   `plan`  - the vendor-funded Zywrap allowance that comes with a licence.
 *             Counted in requests, because that is the unit it is sold in, and
 *             never converted into money: we do not know what it costs us and
 *             the customer is not paying it.
 *   `byok`  - the site's own provider key. This is real money leaving the
 *             customer's account, and it is the only thing the money budgets
 *             can honestly govern.
 *   `local` - a model on the customer's own hardware. Free per call by
 *             definition, so priced at zero rather than left unknown.
 *
 * Showing one number over all three would be a lie in whichever unit it chose.
 *
 * ## Unknown prices are shown as unknown
 *
 * A model that is not in the price book is counted as a call with tokens and
 * **no money**, and the screen says how many of those there were. Treating an
 * unpriced model as free is how a budget silently stops applying the day
 * somebody types a new model name; the call caps still bite, and the screen
 * asks for a price rather than pretending it has one.
 */
class JSSTaiusage {

    const TABLE          = 'js_ticket_ai_usage';
    const SCHEMA_VERSION = '6.0.0';
    const OPT_SCHEMA     = 'jsst_ai_usage_schema';

    /** The caps. */
    const OPT_BUDGET = 'jsst_ai_budget';
    /** model => array(input per million, output per million), in USD. */
    const OPT_PRICES = 'jsst_ai_prices';
    /** Which budget warnings have already been sent, per period. */
    const OPT_ALERTED = 'jsst_ai_budget_alerted';

    /** How a call was paid for. */
    const FUNDED_PLAN  = 'plan';
    const FUNDED_BYOK  = 'byok';
    const FUNDED_LOCAL = 'local';

    /** Warn at this share of a budget, once per period. */
    const WARN_AT = 0.8;

    /** Rows kept. A year of a busy desk, and enough for any month-on-month view. */
    const KEEP_DAYS = 400;

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    public static function ensureSchema() {
        if (!class_exists('JSSTschemaguard')) return;
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION,
                array(self::TABLE => array('engine', 'model', 'funded', 'feature', 'cost')))) {
            return;
        }

        $jsst_table   = self::table();
        $jsst_charset = jssupportticket::$_db->get_charset_collate();

        /* `cost` is decimal rather than float: a column of money added up ten
           thousand times in float arithmetic drifts, and the number people
           check against an invoice is the one place that shows. Six decimal
           places because a single short call can cost less than a hundredth of
           a cent and rounding each row to cents would report a busy month as
           zero.

           `tokensknown` exists because zero tokens and unknown tokens are
           different facts: the streaming Zywrap paths report neither, and a
           screen that shows them as 0 makes a working feature look idle. */
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                created datetime DEFAULT NULL,
                engine varchar(50) NOT NULL DEFAULT '',
                model varchar(100) NOT NULL DEFAULT '',
                lane varchar(20) NOT NULL DEFAULT '',
                funded varchar(10) NOT NULL DEFAULT 'byok',
                feature varchar(40) NOT NULL DEFAULT '',
                ticketid bigint(20) NOT NULL DEFAULT '0',
                userid bigint(20) NOT NULL DEFAULT '0',
                intokens int(11) NOT NULL DEFAULT '0',
                outtokens int(11) NOT NULL DEFAULT '0',
                tokensknown tinyint(1) NOT NULL DEFAULT '1',
                cost decimal(12,6) NOT NULL DEFAULT '0.000000',
                priced tinyint(1) NOT NULL DEFAULT '1',
                ms int(11) NOT NULL DEFAULT '0',
                ok tinyint(1) NOT NULL DEFAULT '1',
                error varchar(255) NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                KEY jsst_when (created),
                KEY jsst_ticket (ticketid),
                KEY jsst_funded (funded, created)
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

    public static function registerHooks() {
        add_action('admin_init', array(__CLASS__, 'ensureSchema'), 2);
        add_action('jsst_ai_usage_prune', array(__CLASS__, 'prune'));
        /* Scheduled on init rather than here. wp_schedule_event() reads the
           schedule list, whose labels are translated, and asking for a
           translation while plugins are still loading makes WordPress 6.7 and
           later complain that the text domain was loaded too early - a notice
           on every page of the site, for a housekeeping job. */
        add_action('init', array(__CLASS__, 'scheduleprune'));
    }

    public static function scheduleprune() {
        if (!wp_next_scheduled('jsst_ai_usage_prune')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'jsst_ai_usage_prune');
        }
    }

    /** Old rows go. The month and year views never read past KEEP_DAYS. */
    public static function prune() {
        if (!self::available()) return 0;
        return (int) jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "DELETE FROM `" . self::table() . "` WHERE created < %s",
            gmdate('Y-m-d H:i:s', time() - (self::KEEP_DAYS * DAY_IN_SECONDS))));
    }

    /* ------------------------------------------------------------------ *
     * The caps
     * ------------------------------------------------------------------ */

    /**
     * No caps at all, which is the honest default.
     *
     * Shipping a number here would either be too low for a busy desk - AI
     * stopping on a Tuesday afternoon for a reason nobody configured - or high
     * enough to be no protection. Zero means no cap, everywhere.
     */
    public static function defaults() {
        return array(
            'daymoney'    => 0.0,   // USD spent on the site's own key, per day
            'daycalls'    => 0,     // model calls per day, any engine
            'monthmoney'  => 0.0,   // USD per calendar month
            'ticketcalls' => 0,     // model calls against one ticket, ever
            'alerts'      => true,  // e-mail the administrator on 80% and 100%
        );
    }

    public static function budget() {
        $jsst_saved = get_option(self::OPT_BUDGET, array());
        if (!is_array($jsst_saved)) $jsst_saved = array();
        return self::cleanBudget(array_merge(self::defaults(), $jsst_saved));
    }

    public static function setBudget($jsst_new) {
        if (!is_array($jsst_new)) return false;
        $jsst_before = self::budget();
        $jsst_clean  = self::cleanBudget(array_merge($jsst_before, $jsst_new));

        update_option(self::OPT_BUDGET, $jsst_clean, false);

        /* A changed cap resets the warnings, because the next warning is about
           the new number. Without this, raising a budget after being warned
           means never being warned again on the way to the higher one. */
        if ($jsst_before != $jsst_clean) {
            delete_option(self::OPT_ALERTED);
            if (class_exists('JSSTaipolicy')) {
                JSSTaipolicy::record('budget', 'budget');
            }
        }
        return true;
    }

    private static function cleanBudget($jsst_budget) {
        return array(
            'daymoney'    => max(0, round((float) (isset($jsst_budget['daymoney']) ? $jsst_budget['daymoney'] : 0), 2)),
            'monthmoney'  => max(0, round((float) (isset($jsst_budget['monthmoney']) ? $jsst_budget['monthmoney'] : 0), 2)),
            'daycalls'    => max(0, (int) (isset($jsst_budget['daycalls']) ? $jsst_budget['daycalls'] : 0)),
            'ticketcalls' => max(0, (int) (isset($jsst_budget['ticketcalls']) ? $jsst_budget['ticketcalls'] : 0)),
            'alerts'      => !empty($jsst_budget['alerts']),
        );
    }

    /* ------------------------------------------------------------------ *
     * The price book
     * ------------------------------------------------------------------ */

    /**
     * What a million tokens costs, in and out, per model.
     *
     * Shipped as a starting point and editable, because these change and this
     * plugin is not going to be right about them forever. A model that is not
     * here is not guessed at: see the class header.
     *
     * Matching is on a prefix, so a dated model id lands on its family without
     * a site having to add every snapshot by hand.
     */
    public static function shippedPrices() {
        return array(
            'claude-opus-5-5'    => array(4.00, 20.00),
            'claude-opus-5'      => array(5.00, 25.00),
            'claude-sonnet-5'    => array(2.00, 10.00),
            'claude-haiku-4-5'   => array(1.00, 5.00),
            'claude-3-5-haiku'   => array(0.80, 4.00),
            'claude-3-5-sonnet'  => array(3.00, 15.00),
            'gpt-4o-mini'        => array(0.15, 0.60),
            'gpt-4o'             => array(2.50, 10.00),
            'gpt-4.1-mini'       => array(0.40, 1.60),
            'gpt-4.1'            => array(2.00, 8.00),
        );
    }

    /** The shipped book with the site's own entries on top. */
    public static function prices() {
        $jsst_own = get_option(self::OPT_PRICES, array());
        if (!is_array($jsst_own)) $jsst_own = array();

        $jsst_clean = array();
        foreach ($jsst_own as $jsst_model => $jsst_pair) {
            if (!is_array($jsst_pair) || count($jsst_pair) < 2) continue;
            $jsst_key = trim((string) $jsst_model);
            if ($jsst_key === '') continue;
            $jsst_clean[$jsst_key] = array(max(0, (float) $jsst_pair[0]), max(0, (float) $jsst_pair[1]));
        }
        return array_merge(self::shippedPrices(), $jsst_clean);
    }

    public static function setPrice($jsst_model, $jsst_in, $jsst_out) {
        $jsst_model = trim((string) $jsst_model);
        if ($jsst_model === '') return false;

        $jsst_own = get_option(self::OPT_PRICES, array());
        if (!is_array($jsst_own)) $jsst_own = array();
        $jsst_own[$jsst_model] = array(max(0, (float) $jsst_in), max(0, (float) $jsst_out));
        update_option(self::OPT_PRICES, $jsst_own, false);
        return true;
    }

    public static function forgetPrice($jsst_model) {
        $jsst_own = get_option(self::OPT_PRICES, array());
        if (!is_array($jsst_own) || !isset($jsst_own[$jsst_model])) return false;
        unset($jsst_own[$jsst_model]);
        update_option(self::OPT_PRICES, $jsst_own, false);
        return true;
    }

    /**
     * The price for a model, or null when nobody has said.
     *
     * Longest prefix wins, so a site that priced `claude-sonnet-5-20260101`
     * exactly is not overruled by the family entry it also matches.
     */
    public static function price($jsst_model) {
        $jsst_model = trim((string) $jsst_model);
        if ($jsst_model === '') return null;

        $jsst_best = null;
        $jsst_len  = -1;
        foreach (self::prices() as $jsst_key => $jsst_pair) {
            if (strpos($jsst_model, $jsst_key) === 0 && strlen($jsst_key) > $jsst_len) {
                $jsst_best = $jsst_pair;
                $jsst_len  = strlen($jsst_key);
            }
        }
        return $jsst_best;
    }

    /**
     * What one call cost, or null when it cannot be said.
     *
     * A local model is zero, not unknown: it runs on hardware the customer
     * already pays for, and reporting it as an unknown cost would put a
     * permanent warning on the one lane that has nothing to warn about.
     */
    public static function estimate($jsst_model, $jsst_in, $jsst_out, $jsst_funded = self::FUNDED_BYOK) {
        if ($jsst_funded === self::FUNDED_LOCAL) return 0.0;
        if ($jsst_funded === self::FUNDED_PLAN)  return 0.0;

        $jsst_price = self::price($jsst_model);
        if ($jsst_price === null) return null;

        return round((((int) $jsst_in / 1000000) * $jsst_price[0])
                   + (((int) $jsst_out / 1000000) * $jsst_price[1]), 6);
    }

    /* ------------------------------------------------------------------ *
     * Asking first
     * ------------------------------------------------------------------ */

    /**
     * May this call be made?
     *
     * Asked before the request goes out, which is the whole point: a budget
     * checked afterwards is a report. The caps are asked cheapest first, and
     * the per-ticket one last because it is the only one that needs a ticket.
     *
     * The plan allowance is deliberately **not** a refusal. It is somebody
     * else's meter and our count of it is an estimate; refusing on it would
     * stop a site that still had credit, which is worse than the overage it
     * would have prevented. It warns, on the screen and by e-mail.
     *
     * @param array $jsst_context feature, ticket, funded.
     * @return array state ok|off, reason, detail.
     */
    public static function guard($jsst_context = array()) {
        $jsst_budget = self::budget();
        $jsst_funded = isset($jsst_context['funded']) ? $jsst_context['funded'] : self::FUNDED_BYOK;

        /* No table is not a reason to refuse. Unlike the review record, where
           failing closed protects a customer, failing closed here would take
           every AI feature off a site whose schema has drifted - a much larger
           harm than an uncounted day, and one nothing else would explain. */
        if (!self::available()) {
            return self::allowed();
        }

        if ($jsst_budget['daycalls'] > 0) {
            $jsst_today = self::spend('day');
            if ($jsst_today['calls'] >= $jsst_budget['daycalls']) {
                return self::refused('daycalls', sprintf(
                    /* translators: %s: the configured number of AI requests per day */
                    esc_html(__('The daily limit of %s AI requests has been reached.', 'js-support-ticket')),
                    number_format_i18n($jsst_budget['daycalls'])));
            }
        }

        /* Money caps govern the site's own key and nothing else. A vendor-funded
           request costs the customer nothing, and a local one costs nothing per
           call, so stopping either on a money budget would refuse work for a
           bill that was never going to arrive. */
        if ($jsst_funded === self::FUNDED_BYOK) {
            if ($jsst_budget['daymoney'] > 0) {
                $jsst_today = self::spend('day');
                if ($jsst_today['cost'] >= $jsst_budget['daymoney']) {
                    return self::refused('daymoney', sprintf(
                        /* translators: %s: the configured daily spend limit */
                        esc_html(__('The daily AI budget of %s has been spent.', 'js-support-ticket')),
                        self::money($jsst_budget['daymoney'])));
                }
            }
            if ($jsst_budget['monthmoney'] > 0) {
                $jsst_month = self::spend('month');
                if ($jsst_month['cost'] >= $jsst_budget['monthmoney']) {
                    return self::refused('monthmoney', sprintf(
                        /* translators: %s: the configured monthly spend limit */
                        esc_html(__('This month\'s AI budget of %s has been spent.', 'js-support-ticket')),
                        self::money($jsst_budget['monthmoney'])));
                }
            }
        }

        $jsst_ticket = isset($jsst_context['ticket']) ? (int) $jsst_context['ticket'] : 0;
        if ($jsst_budget['ticketcalls'] > 0 && $jsst_ticket > 0) {
            if (self::ticketCalls($jsst_ticket) >= $jsst_budget['ticketcalls']) {
                return self::refused('ticketcalls', sprintf(
                    /* translators: %s: the configured number of AI requests per ticket */
                    esc_html(__('This ticket has already used its %s AI requests.', 'js-support-ticket')),
                    number_format_i18n($jsst_budget['ticketcalls'])));
            }
        }

        return self::allowed();
    }

    private static function allowed() {
        return array('state' => 'ok', 'reason' => '', 'detail' => '');
    }

    private static function refused($jsst_reason, $jsst_detail) {
        return array('state' => 'off', 'reason' => $jsst_reason, 'detail' => $jsst_detail);
    }

    /* ------------------------------------------------------------------ *
     * Counting afterwards
     * ------------------------------------------------------------------ */

    /**
     * Write down one model call.
     *
     * Failures are recorded too, and with their tokens: a request that was
     * charged for and then returned an error is exactly the spend somebody
     * cannot otherwise account for, and a log of only the successes makes the
     * invoice look wrong rather than the code.
     *
     * @param array $jsst_context engine, model, lane, funded, feature, ticket, user, ms.
     * @param array $jsst_result  intokens, outtokens, ok, error. Tokens may be
     *                            absent, which is recorded as unknown, not zero.
     */
    public static function record($jsst_context = array(), $jsst_result = array()) {
        if (!self::available()) return 0;

        $jsst_funded = isset($jsst_context['funded']) ? (string) $jsst_context['funded'] : self::FUNDED_BYOK;
        $jsst_model  = isset($jsst_context['model']) ? (string) $jsst_context['model'] : '';
        $jsst_known  = (isset($jsst_result['intokens']) || isset($jsst_result['outtokens']));
        $jsst_in     = isset($jsst_result['intokens']) ? max(0, (int) $jsst_result['intokens']) : 0;
        $jsst_out    = isset($jsst_result['outtokens']) ? max(0, (int) $jsst_result['outtokens']) : 0;

        $jsst_cost = $jsst_known ? self::estimate($jsst_model, $jsst_in, $jsst_out, $jsst_funded) : null;

        jssupportticket::$_db->insert(self::table(), array(
            'created'     => current_time('Y-m-d H:i:s'),
            'engine'      => substr((string) (isset($jsst_context['engine']) ? $jsst_context['engine'] : ''), 0, 50),
            'model'       => substr($jsst_model, 0, 100),
            'lane'        => substr((string) (isset($jsst_context['lane']) ? $jsst_context['lane'] : ''), 0, 20),
            'funded'      => $jsst_funded,
            'feature'     => substr((string) (isset($jsst_context['feature']) ? $jsst_context['feature'] : ''), 0, 40),
            'ticketid'    => isset($jsst_context['ticket']) ? (int) $jsst_context['ticket'] : 0,
            'userid'      => isset($jsst_context['user']) ? (int) $jsst_context['user'] : get_current_user_id(),
            'intokens'    => $jsst_in,
            'outtokens'   => $jsst_out,
            'tokensknown' => $jsst_known ? 1 : 0,
            'cost'        => ($jsst_cost === null) ? 0 : $jsst_cost,
            'priced'      => ($jsst_cost === null) ? 0 : 1,
            'ms'          => isset($jsst_context['ms']) ? (int) $jsst_context['ms'] : 0,
            'ok'          => empty($jsst_result['ok']) ? 0 : 1,
            'error'       => substr((string) (isset($jsst_result['error']) ? $jsst_result['error'] : ''), 0, 255),
        ));

        $jsst_id = (int) jssupportticket::$_db->insert_id;
        self::maybeAlert();
        return $jsst_id;
    }

    /**
     * Guard, run, record - as one call.
     *
     * Exists because three of the paths that talk to a model are not the
     * engine's: the ticket-reply modal, the prompt playground and the engine
     * itself each post to the vendor separately, and a meter that has to be
     * remembered in three places is a meter that eventually is not. Anything
     * that spends money wraps itself in this and cannot be half-metered.
     *
     * The callable is handed nothing and returns whatever it likes; a returned
     * array's intokens/outtokens/ok/error are what gets recorded.
     */
    public static function meter($jsst_context, $jsst_work) {
        $jsst_verdict = self::guard($jsst_context);
        if ($jsst_verdict['state'] !== 'ok') {
            return new WP_Error('jsst_ai_budget', $jsst_verdict['detail'], array('reason' => $jsst_verdict['reason']));
        }

        $jsst_started = microtime(true);
        $jsst_result  = call_user_func($jsst_work);
        $jsst_context['ms'] = (int) round((microtime(true) - $jsst_started) * 1000);

        if (is_wp_error($jsst_result)) {
            self::record($jsst_context, array('ok' => false, 'error' => $jsst_result->get_error_message()));
            return $jsst_result;
        }

        $jsst_row = is_array($jsst_result) ? $jsst_result : array();
        $jsst_row['ok'] = true;
        self::record($jsst_context, $jsst_row);
        return $jsst_result;
    }

    /* ------------------------------------------------------------------ *
     * Reading it back
     * ------------------------------------------------------------------ */

    /** The SQL date floor for a named window, in the site's own time. */
    private static function since($jsst_window) {
        switch ($jsst_window) {
            case 'day':   return current_time('Y-m-d') . ' 00:00:00';
            case 'month': return current_time('Y-m') . '-01 00:00:00';
            case 'year':  return current_time('Y') . '-01-01 00:00:00';
        }
        return '1970-01-01 00:00:00';
    }

    /**
     * What has been spent in a window.
     *
     * `unpriced` is carried beside the money rather than folded into it,
     * because "£4 and nine calls we cannot price" is a different sentence from
     * "£4", and only one of them tells somebody to go and set a price.
     */
    public static function spend($jsst_window = 'month') {
        $jsst_out = array('calls' => 0, 'tokens' => 0, 'cost' => 0.0,
                          'unpriced' => 0, 'failed' => 0, 'plan' => 0);
        if (!self::available()) return $jsst_out;

        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            "SELECT COUNT(id) AS jsst_calls,
                    COALESCE(SUM(intokens + outtokens), 0) AS jsst_tokens,
                    COALESCE(SUM(cost), 0) AS jsst_cost,
                    COALESCE(SUM(CASE WHEN priced = 0 THEN 1 ELSE 0 END), 0) AS jsst_unpriced,
                    COALESCE(SUM(CASE WHEN ok = 0 THEN 1 ELSE 0 END), 0) AS jsst_failed,
                    COALESCE(SUM(CASE WHEN funded = %s THEN 1 ELSE 0 END), 0) AS jsst_plan
               FROM `" . self::table() . "` WHERE created >= %s",
            self::FUNDED_PLAN, self::since($jsst_window)));

        if ($jsst_row) {
            $jsst_out['calls']    = (int) $jsst_row->jsst_calls;
            $jsst_out['tokens']   = (int) $jsst_row->jsst_tokens;
            $jsst_out['cost']     = (float) $jsst_row->jsst_cost;
            $jsst_out['unpriced'] = (int) $jsst_row->jsst_unpriced;
            $jsst_out['failed']   = (int) $jsst_row->jsst_failed;
            $jsst_out['plan']     = (int) $jsst_row->jsst_plan;
        }
        return $jsst_out;
    }

    /** Calls made against one ticket, ever. */
    public static function ticketCalls($jsst_ticketid) {
        if (!self::available()) return 0;
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(id) FROM `" . self::table() . "` WHERE ticketid = %d", (int) $jsst_ticketid));
    }

    /** What one ticket has cost, for the ticket screen and the top-spend list. */
    public static function ticketCost($jsst_ticketid) {
        if (!self::available()) return 0.0;
        return (float) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COALESCE(SUM(cost), 0) FROM `" . self::table() . "` WHERE ticketid = %d",
            (int) $jsst_ticketid));
    }

    /**
     * Spend grouped by one column.
     *
     * Only three columns may be grouped on and they are named here rather than
     * passed through, because the caller is a screen and a screen with a free
     * hand over a column name is one refactor away from being a way to read the
     * table sideways.
     */
    public static function breakdown($jsst_by = 'feature', $jsst_window = 'month', $jsst_limit = 20) {
        $jsst_columns = array('feature' => 'feature', 'model' => 'model',
                              'engine' => 'engine', 'funded' => 'funded');
        if (!isset($jsst_columns[$jsst_by]) || !self::available()) return array();

        $jsst_column = $jsst_columns[$jsst_by];
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT `" . $jsst_column . "` AS jsst_key, COUNT(id) AS jsst_calls,
                    COALESCE(SUM(intokens + outtokens), 0) AS jsst_tokens,
                    COALESCE(SUM(cost), 0) AS jsst_cost,
                    COALESCE(SUM(CASE WHEN priced = 0 THEN 1 ELSE 0 END), 0) AS jsst_unpriced
               FROM `" . self::table() . "`
              WHERE created >= %s
              GROUP BY `" . $jsst_column . "`
              ORDER BY jsst_cost DESC, jsst_calls DESC
              LIMIT %d",
            self::since($jsst_window), max(1, min(100, (int) $jsst_limit))));

        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[] = array(
                'key'      => (string) $jsst_row->jsst_key,
                'calls'    => (int) $jsst_row->jsst_calls,
                'tokens'   => (int) $jsst_row->jsst_tokens,
                'cost'     => (float) $jsst_row->jsst_cost,
                'unpriced' => (int) $jsst_row->jsst_unpriced,
            );
        }
        return $jsst_out;
    }

    /** The most recent calls, for the audit table. */
    public static function recent($jsst_limit = 40) {
        if (!self::available()) return array();
        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` ORDER BY id DESC LIMIT %d",
            max(1, min(200, (int) $jsst_limit))));
    }

    /** The tickets that cost the most this window. */
    public static function topTickets($jsst_window = 'month', $jsst_limit = 10) {
        if (!self::available()) return array();
        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT ticketid, COUNT(id) AS jsst_calls, COALESCE(SUM(cost), 0) AS jsst_cost
               FROM `" . self::table() . "`
              WHERE created >= %s AND ticketid > 0
              GROUP BY ticketid ORDER BY jsst_cost DESC, jsst_calls DESC LIMIT %d",
            self::since($jsst_window), max(1, min(50, (int) $jsst_limit))));
    }

    /* ------------------------------------------------------------------ *
     * The plan allowance
     * ------------------------------------------------------------------ */

    /** Vendor-funded requests included with this licence each month. */
    public static function allowance() {
        if (!class_exists('JSSTplans')) return 0;
        $jsst_plan = JSSTplans::currentPlan();
        return isset($jsst_plan['ai']) ? (int) $jsst_plan['ai'] : 0;
    }

    /** Vendor-funded requests used this month. */
    public static function allowanceUsed() {
        $jsst_month = self::spend('month');
        return (int) $jsst_month['plan'];
    }

    /* ------------------------------------------------------------------ *
     * Warnings
     * ------------------------------------------------------------------ */

    /**
     * Everything currently worth saying, for the screen and the e-mail.
     *
     * Each entry is a level (warn or stop), what it is about, and a sentence.
     * Built in one place so the screen and the e-mail cannot disagree about
     * whether a site is in trouble.
     */
    public static function alerts() {
        $jsst_out    = array();
        $jsst_budget = self::budget();

        // Each window read once. This runs after every model call the product
        // makes, so asking the same question three times is three times as much
        // of it as there needs to be.
        $jsst_day   = self::spend('day');
        $jsst_month = self::spend('month');

        $jsst_checks = array(
            array('key' => 'daymoney',   'cap' => $jsst_budget['daymoney'],
                  'used' => $jsst_day['cost'], 'money' => true,
                  'label' => esc_html(__('daily AI budget', 'js-support-ticket'))),
            array('key' => 'monthmoney', 'cap' => $jsst_budget['monthmoney'],
                  'used' => $jsst_month['cost'], 'money' => true,
                  'label' => esc_html(__('monthly AI budget', 'js-support-ticket'))),
            array('key' => 'daycalls',   'cap' => $jsst_budget['daycalls'],
                  'used' => $jsst_day['calls'], 'money' => false,
                  'label' => esc_html(__('daily request limit', 'js-support-ticket'))),
        );

        foreach ($jsst_checks as $jsst_check) {
            if ($jsst_check['cap'] <= 0) continue;
            $jsst_share = $jsst_check['used'] / $jsst_check['cap'];
            if ($jsst_share < self::WARN_AT) continue;

            $jsst_cap = $jsst_check['money'] ? self::money($jsst_check['cap']) : number_format_i18n($jsst_check['cap']);

            /* A share rather than "$0.02 of $0.02". Two amounts a penny apart
               round to the same string, so the sentence read as "you are at the
               limit" while the badge beside it said "nearly there" - and the
               reader believes whichever one is worse. */
            $jsst_out[] = array(
                'key'   => $jsst_check['key'],
                'level' => ($jsst_share >= 1) ? 'stop' : 'warn',
                'text'  => ($jsst_share >= 1)
                    ? sprintf(
                        /* translators: 1: the name of a budget, 2: the amount it is set to */
                        esc_html(__('The %1$s of %2$s is used up. AI requests are being refused until it resets.', 'js-support-ticket')),
                        $jsst_check['label'], $jsst_cap)
                    : sprintf(
                        /* translators: 1: the name of a budget, 2: the amount it is set to, 3: a percentage */
                        esc_html(__('The %1$s of %2$s is %3$d%% used.', 'js-support-ticket')),
                        $jsst_check['label'], $jsst_cap, (int) floor($jsst_share * 100)),
            );
        }

        /* The allowance warns and never stops - it is somebody else's meter and
           this is our estimate of it. */
        $jsst_allowance = self::allowance();
        if ($jsst_allowance > 0) {
            $jsst_used = (int) $jsst_month['plan'];
            if ($jsst_used >= ($jsst_allowance * self::WARN_AT)) {
                $jsst_out[] = array(
                    'key'   => 'allowance',
                    'level' => 'warn',
                    'text'  => sprintf(
                        /* translators: 1: requests used, 2: the plan's monthly allowance */
                        esc_html(__('%1$s of the %2$s included AI requests on this licence have been used this month. Beyond it, requests need your own provider key.', 'js-support-ticket')),
                        number_format_i18n($jsst_used), number_format_i18n($jsst_allowance)),
                );
            }
        }

        return $jsst_out;
    }

    /**
     * E-mail the administrator, once per period per threshold.
     *
     * The marker is keyed on the period as well as the alert, so a monthly
     * budget warns again next month without anybody clearing anything - and a
     * site that sits at 90% for a fortnight is told once, not every time cron
     * runs. An alert people learn to ignore is not an alert.
     */
    public static function maybeAlert() {
        $jsst_budget = self::budget();
        if (empty($jsst_budget['alerts'])) return 0;

        $jsst_sent  = get_option(self::OPT_ALERTED, array());
        if (!is_array($jsst_sent)) $jsst_sent = array();
        $jsst_fired = 0;

        foreach (self::alerts() as $jsst_alert) {
            $jsst_period = ($jsst_alert['key'] === 'daycalls' || $jsst_alert['key'] === 'daymoney')
                ? current_time('Y-m-d') : current_time('Y-m');
            $jsst_marker = $jsst_alert['key'] . ':' . $jsst_alert['level'] . ':' . $jsst_period;

            if (isset($jsst_sent[$jsst_marker])) continue;
            $jsst_sent[$jsst_marker] = time();

            /* wp_mail is asked for, never required: a site with no working mail
               still gets the warning on the screen, and a failed send must not
               make the alert fire again on the next request. */
            wp_mail(
                get_option('admin_email'),
                sprintf(
                    /* translators: %s: the site name */
                    esc_html(__('[%s] AI budget warning', 'js-support-ticket')),
                    wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)),
                $jsst_alert['text'] . "\n\n" . admin_url('admin.php?page=aiagent&jstlay=aiagent_usage')
            );
            $jsst_fired++;
        }

        /* Trimmed rather than left to grow: one marker per alert per period is
           a few dozen a year, and an option nobody prunes is an option that is
           one day a megabyte. */
        if ($jsst_fired > 0) {
            arsort($jsst_sent);
            update_option(self::OPT_ALERTED, array_slice($jsst_sent, 0, 60, true), false);
        }
        return $jsst_fired;
    }

    /* ------------------------------------------------------------------ *
     * Presentation
     * ------------------------------------------------------------------ */

    /**
     * Money, in US dollars, unconverted.
     *
     * The same decision JSSTplans made about prices: what is being reported is
     * what a provider charges, which is billed in dollars whatever the site's
     * currency is, and a converted figure that does not match the invoice is
     * worse than an unconverted one.
     */
    public static function money($jsst_amount) {
        $jsst_amount = (float) $jsst_amount;
        // Sub-cent totals read as $0.00 and look like nothing happened.
        $jsst_places = ($jsst_amount > 0 && $jsst_amount < 0.01) ? 4 : 2;
        return '$' . number_format_i18n($jsst_amount, $jsst_places);
    }

    /** The features that spend, named for a screen. */
    public static function features() {
        return array(
            'copilot'    => esc_html(__('Copilot actions in a ticket', 'js-support-ticket')),
            'autopilot'  => esc_html(__('Automatic answers', 'js-support-ticket')),
            'draft'      => esc_html(__('Drafts for an agent', 'js-support-ticket')),
            'deflection' => esc_html(__('Answers on the ticket form', 'js-support-ticket')),
            'reply'      => esc_html(__('Reply written from the ticket screen', 'js-support-ticket')),
            'playground' => esc_html(__('Prompt Lab', 'js-support-ticket')),
            'test'       => esc_html(__('Connection tests', 'js-support-ticket')),
        );
    }

    public static function featureLabel($jsst_key) {
        $jsst_features = self::features();
        return isset($jsst_features[$jsst_key]) ? $jsst_features[$jsst_key]
            : ($jsst_key === '' ? esc_html(__('Unnamed', 'js-support-ticket')) : $jsst_key);
    }

    public static function fundedLabel($jsst_key) {
        switch ($jsst_key) {
            case self::FUNDED_PLAN:  return esc_html(__('Included with your licence', 'js-support-ticket'));
            case self::FUNDED_LOCAL: return esc_html(__('Your own hardware', 'js-support-ticket'));
        }
        return esc_html(__('Your own provider key', 'js-support-ticket'));
    }
}
