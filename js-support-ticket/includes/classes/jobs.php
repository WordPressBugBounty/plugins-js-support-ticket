<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap, which deduplicates by resolved path. Any route
 * that reaches this file by a second spelling of the same path would otherwise
 * redeclare the class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTjobs')) {
    return;
}

/**
 * The background job queue. (Roadmap 4.0-PERF-02)
 *
 * Everything slow this plugin does used to happen inside somebody's request.
 * Posting a reply sent the notification before the page came back, so an agent
 * waited on an SMTP handshake. A retention run deleted one batch per cron tick,
 * so a site with fifty thousand expired tickets would take a year to clear them
 * at one batch a day. Collecting mail read the whole mailbox in one go and, on
 * shared hosting, hit the execution limit and left half the messages unread with
 * nothing to say so.
 *
 * This is a queue with the three properties those failures need: work is claimed
 * before it runs so two overlapping cron ticks cannot do it twice, a run stops
 * when it is out of time rather than when it is out of work, and a job that
 * stops early can put the rest of itself back.
 *
 * Not Action Scheduler. That library is the obvious answer and it is a good one,
 * but it is a megabyte and a half of vendored code with its own tables and its
 * own version negotiation between every plugin that bundles it, and the roadmap
 * allows an equivalent. This is the equivalent: one table, one runner, and
 * semantics chosen to fit what 4.0-DATA-01 needs next - resumable batches that
 * are safe to run twice.
 *
 * Two things it deliberately does not do. It does not use transactions, because
 * twenty-one of this plugin's tables are still MyISAM today and there are none
 * to use; the claim token below is what replaces them, and it is why 4.0-PERF-03
 * is the item that follows this one. And it never becomes the only route to
 * work that has to happen: if the queue cannot run, callers fall back to doing
 * the job in the request, because a site with no cron must still send email.
 */
class JSSTjobs {

    /** Bumped when the table layout below changes. */
    const SCHEMA_VERSION = '400-PERF02';

    /** Attempts before a job is given up on and left for somebody to read. */
    const MAX_ATTEMPTS = 3;

    /** How many jobs one claim takes. */
    const CLAIM_BATCH = 10;

    /**
     * How long a claim is honoured before the job is treated as abandoned.
     *
     * A process killed mid-job leaves its row claimed forever otherwise. Five
     * minutes is longer than any single job should take and short enough that a
     * crash does not stall the queue for the rest of the day.
     */
    const STUCK_AFTER = 300;

    /** Completed rows are kept this long, so a run can be looked at afterwards. */
    const KEEP_COMPLETED = 604800;

    /**
     * If the runner has not run in this long, the next page load runs it.
     *
     * The safety net for sites with WP-Cron switched off and no system cron put
     * in its place - a configuration that looks perfectly healthy until somebody
     * asks why no notification has gone out for a week. (Roadmap 4.0-OPS-01)
     */
    const IDLE_KICK = 300;

    const RUNNER_HOOK = 'jsst_run_jobs';
    const LOCK_KEY = 'jsst_jobs_lock';
    const LAST_RUN_KEY = 'jsst_jobs_last_run';

    /** Set when something was queued in this request, so shutdown can kick the runner. */
    private static $_queued_this_request = false;

    /** Cached answer of whether the table is really there. */
    private static $_available = null;

    public static function table() {
        return jssupportticket::$_db->prefix . 'js_ticket_jobs';
    }

    /* ------------------------------------------------------------------ *
     * Schema
     * ------------------------------------------------------------------ */

    /**
     * Create the table if it is not there.
     *
     * Self-healing rather than activation-only: this table is new in 4.0, and an
     * upgrade does not run the activation hook. A site that updates the plugin
     * files in place would otherwise have a queue with nowhere to write.
     */
    public static function ensureSchema() {
        // The queue table is dropped on uninstall like any other, and a job
        // enqueued against a missing one is silently lost. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_jobs_schema', self::SCHEMA_VERSION,
                array('js_ticket_jobs' => array()))) {
            return;
        }
        $jsst_table = self::table();
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                hook varchar(100) NOT NULL,
                args longtext,
                groupname varchar(50) NOT NULL DEFAULT '',
                status varchar(20) NOT NULL DEFAULT 'pending',
                priority tinyint(3) NOT NULL DEFAULT '10',
                scheduled datetime DEFAULT NULL,
                claim varchar(40) NOT NULL DEFAULT '',
                claimed datetime DEFAULT NULL,
                attempts tinyint(3) NOT NULL DEFAULT '0',
                lasterror text,
                created datetime DEFAULT NULL,
                updated datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY jsst_due (status, scheduled, priority),
                KEY jsst_claim (claim),
                KEY jsst_group (groupname, status)
            ) " . $jsst_charset);

        update_option('jsst_jobs_schema', self::SCHEMA_VERSION, false);
        self::$_available = null;
    }

    /**
     * Is there a queue to write to?
     *
     * Checked rather than assumed, because every caller has a working path that
     * does not involve the queue and needs to know to take it. A filter is
     * offered so a site that has been bitten by a background queue somewhere else
     * can switch this one off and keep the old inline behaviour.
     */
    public static function available() {
        if (self::$_available !== null) {
            return self::$_available;
        }
        if (!apply_filters('jsst_jobs_enabled', true)) {
            self::$_available = false;
            return false;
        }
        $jsst_table = self::table();
        $jsst_found = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table));
        self::$_available = ($jsst_found === $jsst_table);
        return self::$_available;
    }

    /* ------------------------------------------------------------------ *
     * Registration
     * ------------------------------------------------------------------ */

    public static function registerHooks() {
        add_filter('cron_schedules', array(__CLASS__, 'addInterval'));
        add_action('init', array(__CLASS__, 'scheduleRunner'));
        add_action(self::RUNNER_HOOK, array(__CLASS__, 'run'));
        // The safety net, and the prompt start after something is queued. Both
        // run at shutdown so they never delay the response.
        add_action('shutdown', array(__CLASS__, 'onShutdown'), 99);
    }

    public static function addInterval($jsst_schedules) {
        if (!isset($jsst_schedules['jsst_minute'])) {
            $jsst_schedules['jsst_minute'] = array(
                'interval' => 60,
                'display'  => esc_html(__('Every minute (JS Help Desk queue)', 'js-support-ticket')),
            );
        }
        return $jsst_schedules;
    }

    public static function scheduleRunner() {
        if (!wp_next_scheduled(self::RUNNER_HOOK)) {
            wp_schedule_event(time(), 'jsst_minute', self::RUNNER_HOOK);
        }
    }

    /* ------------------------------------------------------------------ *
     * Putting work in
     * ------------------------------------------------------------------ */

    /**
     * Queue a job.
     *
     * @param string $jsst_hook  The action fired when it runs, with the arguments as its first parameter.
     * @param array  $jsst_args  Anything json can carry. Ids, not objects - the job may run in another process an hour later.
     * @param string $jsst_group A label for reporting: email, cleanup, piping, import.
     * @param int    $jsst_delay Seconds to wait before it becomes due.
     * @return int The job id, or 0 if it could not be queued.
     */
    public static function enqueue($jsst_hook, $jsst_args = array(), $jsst_group = '', $jsst_delay = 0, $jsst_priority = 10) {
        if (!self::available()) {
            return 0;
        }
        $jsst_now = gmdate('Y-m-d H:i:s');
        $jsst_done = jssupportticket::$_db->insert(self::table(), array(
            'hook'      => (string) $jsst_hook,
            'args'      => wp_json_encode($jsst_args),
            'groupname' => (string) $jsst_group,
            'status'    => 'pending',
            'priority'  => (int) $jsst_priority,
            'scheduled' => gmdate('Y-m-d H:i:s', time() + max(0, (int) $jsst_delay)),
            'attempts'  => 0,
            'created'   => $jsst_now,
            'updated'   => $jsst_now,
        ));
        if (!$jsst_done) {
            return 0;
        }
        self::$_queued_this_request = true;
        return (int) jssupportticket::$_db->insert_id;
    }

    /**
     * Queue a job unless the same one is already waiting.
     *
     * The idempotent enqueue. Retention re-queues itself while work remains, and
     * a cron tick landing on top of that must not start a second drain of the
     * same table; the same is true of a mailbox poll. Compared on hook and
     * arguments, so two exports of different date ranges are still two jobs.
     */
    public static function enqueueOnce($jsst_hook, $jsst_args = array(), $jsst_group = '', $jsst_delay = 0, $jsst_priority = 10) {
        if (!self::available()) {
            return 0;
        }
        $jsst_existing = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT id FROM `" . self::table() . "` WHERE hook = %s AND args = %s AND status IN ('pending','running') LIMIT 1",
            (string) $jsst_hook,
            wp_json_encode($jsst_args)
        ));
        if ($jsst_existing) {
            return (int) $jsst_existing;
        }
        return self::enqueue($jsst_hook, $jsst_args, $jsst_group, $jsst_delay, $jsst_priority);
    }

    /* ------------------------------------------------------------------ *
     * Taking work out
     * ------------------------------------------------------------------ */

    /**
     * How long one run may take.
     *
     * Six-tenths of whatever the host allows, so the runner stops and puts the
     * rest back rather than being killed holding a claim. An unlimited limit
     * (CLI, or a host that reports 0) is treated as thirty seconds, because the
     * point is to finish politely, not to hold the process forever.
     */
    private static function budget() {
        $jsst_limit = (int) ini_get('max_execution_time');
        if ($jsst_limit <= 0) {
            $jsst_limit = 30;
        }
        $jsst_budget = (int) floor($jsst_limit * 0.6);
        return max(5, min(45, $jsst_budget));
    }

    /** Near the memory ceiling, stopping now beats being killed mid-job. */
    private static function outOfMemory() {
        $jsst_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        if ($jsst_limit <= 0) {
            return false;
        }
        return (memory_get_usage(true) > ($jsst_limit * 0.8));
    }

    /**
     * Hand any abandoned claims back.
     *
     * A job whose process died stays 'running' with a claim nobody holds. Its
     * attempt is counted - a job that reliably kills the process must not be
     * retried forever - and then it goes back in the queue.
     */
    private static function releaseStuck() {
        $jsst_now = gmdate('Y-m-d H:i:s');
        $jsst_cutoff = gmdate('Y-m-d H:i:s', time() - self::STUCK_AFTER);
        $jsst_why = 'Abandoned: the process running it did not finish.';

        /* The last attempt is given up on here rather than put back. claim()
           will not take a job that has used its attempts, so a row requeued as
           pending at the cap would sit in the queue forever: never run, never
           failed, and counted as pending on the System Status page for the rest
           of the site's life. A handler that throws ends as failed, and a
           handler that never returns has to end the same way. */
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . self::table() . "`
                SET status = 'failed', claim = '', attempts = attempts + 1,
                    lasterror = %s, updated = %s
                WHERE status = 'running' AND claimed < %s AND attempts + 1 >= %d",
            $jsst_why,
            $jsst_now,
            $jsst_cutoff,
            self::MAX_ATTEMPTS
        ));

        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . self::table() . "`
                SET status = 'pending', claim = '', attempts = attempts + 1,
                    lasterror = %s, updated = %s
                WHERE status = 'running' AND claimed < %s AND attempts + 1 < %d",
            $jsst_why,
            $jsst_now,
            $jsst_cutoff,
            self::MAX_ATTEMPTS
        ));

        /* Rows already left in that state by an earlier release — pending, out
           of attempts, unclaimable. Nothing else will ever look at them again. */
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . self::table() . "`
                SET status = 'failed', updated = %s
                WHERE status = 'pending' AND attempts >= %d",
            $jsst_now,
            self::MAX_ATTEMPTS
        ));
    }

    /**
     * Take ownership of up to CLAIM_BATCH due jobs.
     *
     * One UPDATE stamps the claim, then the rows are read back by it. This is
     * the part that would be a transaction if the tables supported one: a single
     * UPDATE is atomic on its own, so two runners cannot both stamp the same row,
     * and whichever one wins is the one whose token is on it afterwards.
     */
    private static function claim($jsst_token) {
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "UPDATE `" . self::table() . "`
                SET claim = %s, status = 'running', claimed = %s, updated = %s
                WHERE status = 'pending' AND scheduled <= %s AND attempts < %d
                ORDER BY priority ASC, id ASC
                LIMIT %d",
            $jsst_token,
            gmdate('Y-m-d H:i:s'),
            gmdate('Y-m-d H:i:s'),
            gmdate('Y-m-d H:i:s'),
            self::MAX_ATTEMPTS,
            self::CLAIM_BATCH
        ));
        return jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT * FROM `" . self::table() . "` WHERE claim = %s ORDER BY priority ASC, id ASC",
            $jsst_token
        ));
    }

    /**
     * Work the queue until it is empty or the budget is spent.
     *
     * Locked with a transient so two cron ticks - or a cron tick and the
     * page-load safety net - never work at the same time. The lock expires on its
     * own so a crash cannot wedge the queue permanently.
     */
    public static function run($jsst_seconds = 0) {
        if (!self::available()) {
            return 0;
        }
        if (get_transient(self::LOCK_KEY)) {
            return 0;
        }
        set_transient(self::LOCK_KEY, 1, self::STUCK_AFTER);
        update_option(self::LAST_RUN_KEY, time(), false);

        self::releaseStuck();

        $jsst_budget = ($jsst_seconds > 0) ? min((int) $jsst_seconds, self::budget()) : self::budget();
        $jsst_deadline = microtime(true) + $jsst_budget;
        $jsst_ran = 0;

        while (microtime(true) < $jsst_deadline && !self::outOfMemory()) {
            $jsst_token = wp_generate_password(20, false, false);
            $jsst_jobs = self::claim($jsst_token);
            if (empty($jsst_jobs)) {
                break;
            }
            foreach ($jsst_jobs as $jsst_job) {
                if (microtime(true) >= $jsst_deadline || self::outOfMemory()) {
                    // Out of time with jobs still claimed: put the untouched ones
                    // back rather than running them badly.
                    self::release($jsst_job->id);
                    continue;
                }
                self::execute($jsst_job);
                $jsst_ran++;
            }
        }

        self::purgeCompleted();
        delete_transient(self::LOCK_KEY);
        return $jsst_ran;
    }

    /** Put one claimed job back without counting an attempt against it. */
    private static function release($jsst_id) {
        jssupportticket::$_db->update(self::table(),
            array('status' => 'pending', 'claim' => '', 'updated' => gmdate('Y-m-d H:i:s')),
            array('id' => (int) $jsst_id)
        );
    }

    /**
     * Run one job.
     *
     * A handler that throws is retried, twice, and then left as failed with the
     * reason on the row. It is never dropped silently: a notification that was
     * never sent has to be findable afterwards, which is what the System Status
     * page reads. (Roadmap 4.0-OPS-02)
     */
    private static function execute($jsst_job) {
        $jsst_args = json_decode($jsst_job->args, true);
        if (!is_array($jsst_args)) {
            $jsst_args = array();
        }
        try {
            do_action($jsst_job->hook, $jsst_args, $jsst_job);
            jssupportticket::$_db->update(self::table(),
                array('status' => 'complete', 'claim' => '', 'updated' => gmdate('Y-m-d H:i:s')),
                array('id' => (int) $jsst_job->id)
            );
        } catch (Exception $jsst_e) {
            self::fail($jsst_job, $jsst_e->getMessage());
        } catch (Throwable $jsst_e) {
            // PHP 7+ errors - a call to a method on null inside a handler is this,
            // not an Exception, and it must not take the whole runner down.
            self::fail($jsst_job, $jsst_e->getMessage());
        }
    }

    /** Count the attempt, and either queue it again or give up on it. */
    private static function fail($jsst_job, $jsst_message) {
        $jsst_attempts = (int) $jsst_job->attempts + 1;
        $jsst_final = ($jsst_attempts >= self::MAX_ATTEMPTS);
        jssupportticket::$_db->update(self::table(), array(
            'status'    => $jsst_final ? 'failed' : 'pending',
            'claim'     => '',
            'attempts'  => $jsst_attempts,
            'lasterror' => substr((string) $jsst_message, 0, 500),
            // Backing off, so a mailbox that is refusing connections is not
            // hammered once a minute until it starts working.
            'scheduled' => gmdate('Y-m-d H:i:s', time() + (60 * $jsst_attempts * $jsst_attempts)),
            'updated'   => gmdate('Y-m-d H:i:s'),
        ), array('id' => (int) $jsst_job->id));

        if ($jsst_final && class_exists('JSSTincluder')) {
            JSSTincluder::getJSModel('systemerror')->addSystemError(
                'Background job ' . $jsst_job->hook . ' gave up after ' . $jsst_attempts . ' attempts: ' . $jsst_message
            );
        }
    }

    /** Completed rows are history, not queue. Failed ones are kept until read. */
    private static function purgeCompleted() {
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "DELETE FROM `" . self::table() . "` WHERE status = 'complete' AND updated < %s",
            gmdate('Y-m-d H:i:s', time() - self::KEEP_COMPLETED)
        ));
    }

    /* ------------------------------------------------------------------ *
     * Making sure it actually runs
     * ------------------------------------------------------------------ */

    /**
     * At the end of a request: start the queue if something was just put in it,
     * or if nothing has worked it for a while.
     *
     * The second case is the one that matters. WP-Cron only fires when somebody
     * visits the site, and on a site where it has been switched off in
     * wp-config.php without a system cron replacing it, it never fires at all.
     * Working the queue from an ordinary page load means a quiet site is slow to
     * send its notifications rather than silently never sending them.
     */
    public static function onShutdown() {
        if (!self::available()) {
            return;
        }
        if (wp_doing_cron()) {
            return;
        }
        $jsst_last = (int) get_option(self::LAST_RUN_KEY, 0);
        $jsst_idle = ((time() - $jsst_last) > self::IDLE_KICK);
        if (!self::$_queued_this_request && !$jsst_idle) {
            return;
        }
        if (!$jsst_idle && !defined('DISABLE_WP_CRON')) {
            // Cron is alive and something was queued; it will be picked up within
            // the minute. Doing it here as well would only slow this request.
            return;
        }

        // Let go of the visitor first where the server allows it. Without this,
        // shutdown still holds the connection open, and the safety net would be
        // paid for by whoever happened to load the page - which on a slow mail
        // server is a visibly slower site, caused by the thing that was supposed
        // to stop the site being slow.
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // A short budget on this path whatever the host allows. Cron is where a
        // backlog is meant to be worked through; this only has to keep a site
        // with no cron at all moving.
        self::run(5);
    }

    /* ------------------------------------------------------------------ *
     * Reporting
     * ------------------------------------------------------------------ */

    /** Queue depth by status, for the System Status page. (Roadmap 4.0-OPS-02) */
    public static function counts() {
        if (!self::available()) {
            return array();
        }
        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT status, COUNT(*) AS total FROM `" . self::table() . "` GROUP BY status"
        );
        $jsst_out = array('pending' => 0, 'running' => 0, 'complete' => 0, 'failed' => 0);
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_out[$jsst_row->status] = (int) $jsst_row->total;
        }
        return $jsst_out;
    }

    /** The oldest thing still waiting, as a unix time, or 0. */
    public static function oldestPending() {
        if (!self::available()) {
            return 0;
        }
        $jsst_when = jssupportticket::$_db->get_var(
            "SELECT MIN(created) FROM `" . self::table() . "` WHERE status = 'pending'"
        );
        return $jsst_when ? (int) strtotime($jsst_when . ' UTC') : 0;
    }

    /** When the runner last worked, as a unix time, or 0. */
    public static function lastRun() {
        return (int) get_option(self::LAST_RUN_KEY, 0);
    }

    /** The most recent jobs that gave up, for the diagnostics page. */
    public static function recentFailures($jsst_limit = 10) {
        if (!self::available()) {
            return array();
        }
        return (array) jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT hook, groupname, attempts, lasterror, updated FROM `" . self::table() . "`
                WHERE status = 'failed' ORDER BY updated DESC LIMIT %d",
            (int) $jsst_limit
        ));
    }

}
