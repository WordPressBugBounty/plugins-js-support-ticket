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
if (class_exists('JSSTpipinglog')) {
    return;
}


/**
 * What happened the last time the mailbox was collected. (Roadmap 4.0-OPS-01)
 *
 * Email piping is the one part of the help desk that runs where nobody is
 * watching: a scheduled request opens a mailbox, reads what is there and marks
 * it read. When it works there is nothing to see, and when it fails there is
 * *also* nothing to see — the mail is gone from the inbox, no ticket exists, and
 * the screen that would explain it shows a list of mailboxes that all look fine.
 * Every question an administrator asks at that point ("is it even running?",
 * "did it connect?", "did it see my message and refuse it?") had no answer
 * anywhere in this plugin.
 *
 * So each collection run writes down the few facts that answer them: when it
 * ran, what started it, which mailboxes it opened, how many messages it found
 * and what became of each one. That is the whole design — it is a record of
 * outcomes, not a debug trace, and it holds nothing that would not already be on
 * the ticket the message became.
 *
 * Three deliberate choices:
 *
 *  - It lives in an option rather than a table. The piping add-on installs its
 *    schema from a file it downloads and then deletes, so a table added there is
 *    the least reliable place in the codebase to put anything; an option also
 *    keeps the history when the add-on is deactivated, which is exactly when
 *    somebody is trying to work out what went wrong.
 *  - It is bounded, at both ends: the last {@see KEEP} runs, each holding at
 *    most {@see MAX_EVENTS} lines. A mailbox with ten thousand messages in it
 *    writes a summary and a handful of lines, never ten thousand.
 *  - It is written incrementally and again from a shutdown handler, because the
 *    failure most worth recording is the one that kills the request. A run that
 *    never reached its end is left saying so rather than vanishing.
 *
 * The add-on calls this when it is present; core reads it for the Email Health
 * screen. Every call site guards with class_exists(), so an older core and a
 * newer add-on (or the reverse) degrade to no log rather than a fatal.
 */
class JSSTpipinglog {

    /** Where the log lives. Never autoloaded — only two screens read it. */
    const OPTION = 'jsst_piping_log';

    /** How many runs are kept. */
    const KEEP = 30;

    /** How many individual message outcomes are recorded per run. */
    const MAX_EVENTS = 40;

    /**
     * How large the whole log may get, serialised.
     *
     * The counts are what matter and they cost nothing; the lines explaining
     * them are what grows. Rather than trimming those to the point of
     * uselessness, older runs are dropped until the log fits — 64KB is far more
     * history than anybody reads and still a row this database will not notice.
     */
    const MAX_BYTES = 65536;

    /** The run being recorded, or null outside a run. */
    private static $jsst_run = null;

    /** True once the shutdown flush is registered for this request. */
    private static $jsst_shutdown = false;

    /**
     * Is logging switched on?
     *
     * On by default and bounded, so there is nothing to turn off for disk or
     * performance reasons. The filter exists for sites that would rather the
     * plugin wrote nothing at all about mail they receive.
     */
    public static function enabled() {
        return (bool) apply_filters('jsst_piping_log_enabled', true);
    }

    /**
     * Begin a collection run.
     *
     * $jsst_trigger says what started it, because "it never runs" and "it runs
     * every time somebody loads a page" are different problems with the same
     * symptom.
     */
    public static function startRun($jsst_trigger = 'cron') {
        if (!self::enabled()) {
            return;
        }
        // A second startRun() inside one request is the double-invocation this
        // path is prone to, not a second run. Keep the first.
        if (self::$jsst_run !== null && empty(self::$jsst_run['finished'])) {
            return;
        }
        self::$jsst_run = array(
            'id'        => (string) time() . '-' . wp_generate_password(6, false, false),
            // The key, not the sentence. A run recorded by cron under one
            // locale and read on screen under another would otherwise show the
            // cron's language back to the reader.
            'trigger'   => self::triggerKey($jsst_trigger),
            'started'   => time(),
            'finished'  => 0,
            'mailboxes' => array(),
            'events'    => array(),
            'totals'    => array(
                'mailboxes' => 0,
                'opened'    => 0,
                'messages'  => 0,
                'tickets'   => 0,
                'replies'   => 0,
                'rejected'  => 0,
                'errors'    => 0,
            ),
        );
        if (!self::$jsst_shutdown) {
            self::$jsst_shutdown = true;
            // Runs after a fatal too, which is the case this exists for: an
            // unreachable mailbox used to take the whole request down and leave
            // no trace of having tried.
            register_shutdown_function(array(__CLASS__, 'abandon'));
        }
        self::flush();
    }

    /**
     * Note that a mailbox is about to be read, and make it the current one.
     *
     * Everything recorded until the next mailbox() belongs to this address.
     */
    public static function mailbox($jsst_address) {
        if (self::$jsst_run === null) {
            return;
        }
        self::$jsst_run['mailboxes'][] = array(
            // Shown in full, unlike the senders below: this is the site's own
            // address, it is listed on the piping screen anyway, and "one of
            // your mailboxes could not be opened" is no use to anybody.
            'address'  => self::clip($jsst_address, 100),
            'state'    => 'opened',
            'reason'   => '',
            'messages' => 0,
            'tickets'  => 0,
            'replies'  => 0,
            'rejected' => 0,
        );
        self::$jsst_run['totals']['mailboxes']++;
        self::flush();
    }

    /**
     * This mailbox was not read, and why — switched off, or configured as an
     * address the desk sends *from*, which would have the plugin answering its
     * own notifications.
     */
    public static function mailboxSkipped($jsst_reason) {
        self::markMailbox('skipped', $jsst_reason);
    }

    /**
     * The mailbox could not be opened. $jsst_error is the server's own words —
     * a wrong password and a blocked port say different things here, and that
     * difference is the entire value of the line.
     */
    public static function mailboxFailed($jsst_error) {
        self::markMailbox('failed', $jsst_error);
        if (self::$jsst_run !== null) {
            self::$jsst_run['totals']['errors']++;
            self::event('error', $jsst_error);
        }
    }

    /** The mailbox opened and held this many unread messages. */
    public static function mailboxOpened($jsst_count) {
        if (self::$jsst_run === null) {
            return;
        }
        $jsst_index = self::currentMailbox();
        if ($jsst_index !== null) {
            self::$jsst_run['mailboxes'][$jsst_index]['state'] = 'opened';
            self::$jsst_run['mailboxes'][$jsst_index]['messages'] = (int) $jsst_count;
        }
        self::$jsst_run['totals']['opened']++;
        self::$jsst_run['totals']['messages'] += (int) $jsst_count;
        self::flush();
    }

    /**
     * What became of one message.
     *
     * $jsst_kind is 'ticket', 'reply', 'rejected' or 'ignored'. The last two are
     * the ones worth having: a message the plugin read and deliberately did
     * nothing with looks identical, from the outside, to one it never saw.
     */
    public static function outcome($jsst_kind, $jsst_from = '', $jsst_detail = '') {
        if (self::$jsst_run === null) {
            return;
        }
        $jsst_index = self::currentMailbox();
        $jsst_counter = array('ticket' => 'tickets', 'reply' => 'replies');
        if (isset($jsst_counter[$jsst_kind])) {
            $jsst_key = $jsst_counter[$jsst_kind];
        } else {
            $jsst_key = 'rejected';
        }
        self::$jsst_run['totals'][$jsst_key]++;
        if ($jsst_index !== null) {
            self::$jsst_run['mailboxes'][$jsst_index][$jsst_key]++;
        }
        // Successes are counted, not narrated — the ticket itself is the record
        // of those. Only the messages that produced nothing get a line, because
        // those are the ones somebody is looking for.
        if ($jsst_key === 'rejected') {
            self::event('warning', self::outcomeLine($jsst_kind, $jsst_from, $jsst_detail));
        }
        /*
         * Deliberately not flushed. A mailbox holding two hundred messages would
         * otherwise write the option two hundred times in one run, and there is
         * nothing to gain for it: the counts reach the database at the end of
         * the mailbox either way, and the shutdown handler covers the case this
         * would be protecting against — a fatal or a timeout part way through.
         */
    }

    /**
     * A line about the run itself, rather than about one message.
     *
     * $jsst_level is 'error', 'warning' or 'info'.
     */
    public static function event($jsst_level, $jsst_message) {
        if (self::$jsst_run === null) {
            return;
        }
        if (count(self::$jsst_run['events']) >= self::MAX_EVENTS) {
            /*
             * An error is never the line that gets dropped. A run that refused
             * forty messages and then could not open the next mailbox would
             * otherwise fill its quota with the forty and lose the one sentence
             * that explains the morning — so an error evicts the oldest line
             * that is not itself an error, and only gives up if every line is.
             */
            if ($jsst_level !== 'error') {
                return;
            }
            $jsst_evicted = false;
            foreach (self::$jsst_run['events'] as $jsst_index => $jsst_existing) {
                if ($jsst_existing['level'] !== 'error') {
                    unset(self::$jsst_run['events'][$jsst_index]);
                    self::$jsst_run['events'] = array_values(self::$jsst_run['events']);
                    $jsst_evicted = true;
                    break;
                }
            }
            if (!$jsst_evicted) {
                return;
            }
        }
        self::$jsst_run['events'][] = array(
            'time'    => time(),
            'level'   => in_array($jsst_level, array('error', 'warning', 'info'), true) ? $jsst_level : 'info',
            'message' => self::clip($jsst_message),
        );
        if ($jsst_level === 'error') {
            self::flush();
        }
    }

    /** The run reached its end. */
    public static function finishRun() {
        if (self::$jsst_run === null) {
            return;
        }
        self::$jsst_run['finished'] = time();
        self::flush();
        self::$jsst_run = null;
    }

    /**
     * The request ended without finishRun().
     *
     * Registered as a shutdown handler, so it also runs after a fatal error.
     * The run is left marked unfinished, which is a finding rather than a gap:
     * it means collection started and something stopped it dead.
     */
    public static function abandon() {
        if (self::$jsst_run === null || !empty(self::$jsst_run['finished'])) {
            return;
        }
        self::event('error', esc_html(__('The collection run stopped before it finished. Something ended the request early — usually a PHP error or a timeout while reading the mailbox.', 'js-support-ticket')));
        self::flush();
        self::$jsst_run = null;
    }

    /**
     * Every recorded run, newest first.
     */
    public static function runs() {
        $jsst_log = get_option(self::OPTION, array());
        return is_array($jsst_log) ? $jsst_log : array();
    }

    /** The most recent run, or an empty array when nothing has run yet. */
    public static function lastRun() {
        $jsst_runs = self::runs();
        return empty($jsst_runs) ? array() : reset($jsst_runs);
    }

    /**
     * The log as the Email Health screen wants it: the last run, a few before
     * it, and one verdict for the card.
     *
     * The verdict is deliberately blunt. Collection that has never run at all is
     * as much of a problem as collection that failed, and both read the same way
     * to somebody whose customer got no answer.
     */
    public static function summary($jsst_limit = 5) {
        $jsst_runs = self::runs();
        $jsst_last = empty($jsst_runs) ? array() : reset($jsst_runs);
        $jsst_state = 'never';
        if (!empty($jsst_last)) {
            $jsst_state = 'ok';
            if (empty($jsst_last['finished'])) {
                $jsst_state = 'incomplete';
            } elseif (!empty($jsst_last['totals']['errors'])) {
                $jsst_state = 'failed';
            }
        }
        return array(
            'state' => $jsst_state,
            'last'  => $jsst_last,
            'runs'  => array_slice($jsst_runs, 0, max(1, (int) $jsst_limit)),
        );
    }

    /** Forget everything. Used by the screen's clear action. */
    public static function clear() {
        self::$jsst_run = null;
        delete_option(self::OPTION);
    }

    /* ------------------------------------------------------------------ *
     * Internals
     * ------------------------------------------------------------------ */

    /**
     * Write the run being recorded into the log.
     *
     * Re-reads first and replaces by id, so a run that is flushed several times
     * updates one entry, and two runs racing each other in different requests
     * cost a line rather than each other's whole history.
     */
    private static function flush() {
        if (self::$jsst_run === null) {
            return;
        }
        $jsst_log = self::runs();
        $jsst_replaced = false;
        foreach ($jsst_log as $jsst_index => $jsst_entry) {
            if (isset($jsst_entry['id']) && $jsst_entry['id'] === self::$jsst_run['id']) {
                $jsst_log[$jsst_index] = self::$jsst_run;
                $jsst_replaced = true;
                break;
            }
        }
        if (!$jsst_replaced) {
            array_unshift($jsst_log, self::$jsst_run);
        }
        if (count($jsst_log) > self::KEEP) {
            $jsst_log = array_slice($jsst_log, 0, self::KEEP);
        }
        // Never at the expense of the run being written: one run always stays,
        // however talkative it turned out to be.
        while (count($jsst_log) > 1 && strlen(serialize($jsst_log)) > self::MAX_BYTES) {
            array_pop($jsst_log);
        }
        update_option(self::OPTION, $jsst_log, false);
    }

    /** Index of the mailbox currently being read, or null. */
    private static function currentMailbox() {
        if (self::$jsst_run === null || empty(self::$jsst_run['mailboxes'])) {
            return null;
        }
        return count(self::$jsst_run['mailboxes']) - 1;
    }

    /** Mark the current mailbox with a state and a reason. */
    private static function markMailbox($jsst_state, $jsst_reason) {
        if (self::$jsst_run === null) {
            return;
        }
        $jsst_index = self::currentMailbox();
        if ($jsst_index === null) {
            return;
        }
        self::$jsst_run['mailboxes'][$jsst_index]['state'] = $jsst_state;
        self::$jsst_run['mailboxes'][$jsst_index]['reason'] = self::clip($jsst_reason);
        self::flush();
    }

    /** One readable line for a message that produced no ticket and no reply. */
    private static function outcomeLine($jsst_kind, $jsst_from, $jsst_detail) {
        $jsst_from = self::maskAddress($jsst_from);
        if ($jsst_detail === '') {
            $jsst_detail = esc_html(__('the message was read but nothing was created from it', 'js-support-ticket'));
        }
        if ($jsst_from === '') {
            return $jsst_detail;
        }
        return sprintf(
            /* translators: 1: a partly hidden email address, 2: the reason nothing was created */
            esc_html(__('From %1$s — %2$s', 'js-support-ticket')),
            $jsst_from,
            $jsst_detail
        );
    }

    /**
     * An address with its local part hidden.
     *
     * Enough to recognise a message you are looking for, not enough to turn the
     * log into a second copy of the customer list. The mailbox addresses belong
     * to the site and are on the piping screen anyway; sender addresses are
     * somebody else's, and this is a diagnostic file, not a ticket.
     */
    public static function maskAddress($jsst_address) {
        $jsst_address = is_string($jsst_address) ? jssupportticketphplib::JSST_trim($jsst_address, " \t\n\r\0\x0B") : '';
        if ($jsst_address === '') {
            return '';
        }
        // Senders arrive as "Name <a@b.c>" as often as bare addresses.
        if (preg_match('/<([^>]+)>/', $jsst_address, $jsst_match)) {
            $jsst_address = $jsst_match[1];
        }
        $jsst_at = strrpos($jsst_address, '@');
        if ($jsst_at === false) {
            return self::clip($jsst_address, 60);
        }
        $jsst_local = substr($jsst_address, 0, $jsst_at);
        $jsst_domain = substr($jsst_address, $jsst_at);
        $jsst_keep = ($jsst_local === '') ? '' : substr($jsst_local, 0, 1);
        return self::clip($jsst_keep . '***' . $jsst_domain, 60);
    }

    /**
     * Plain text, no markup, no runaway server messages.
     *
     * Entities are decoded rather than kept. Callers here follow the plugin's
     * habit of wrapping strings in esc_html() as they are written, but this text
     * is stored and escaped again by the template that prints it — leaving the
     * entities in place would put a literal "PHP&#039;s" on the screen. Stored
     * plain, escaped once, at the point of display.
     */
    private static function clip($jsst_text, $jsst_length = 300) {
        if (!is_string($jsst_text)) {
            $jsst_text = '';
        }
        $jsst_text = wp_specialchars_decode(wp_strip_all_tags($jsst_text), ENT_QUOTES);
        if (strlen($jsst_text) > $jsst_length) {
            $jsst_text = substr($jsst_text, 0, $jsst_length) . '…';
        }
        return $jsst_text;
    }

    /** One of the three triggers this understands. */
    private static function triggerKey($jsst_trigger) {
        return in_array($jsst_trigger, array('cron', 'url', 'manual'), true) ? $jsst_trigger : 'cron';
    }

    /**
     * What started the run, in words the screen can print.
     *
     * Public and called at render time, so the reader gets their own language
     * rather than the one the scheduler happened to run under.
     */
    public static function triggerLabel($jsst_trigger) {
        // Returned plain: the templates escape, and a translation containing an
        // apostrophe would otherwise be escaped twice and reach the screen as
        // "l&#039;URL".
        $jsst_labels = array(
            'cron'   => __('Scheduled', 'js-support-ticket'),
            'url'    => __('Called by URL', 'js-support-ticket'),
            'manual' => __('Started by hand', 'js-support-ticket'),
        );
        $jsst_trigger = self::triggerKey($jsst_trigger);
        return $jsst_labels[$jsst_trigger];
    }
}
