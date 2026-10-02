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
if (class_exists('JSSTnotifications')) {
    return;
}

/**
 * Being told what happened. (Roadmap 4.5-UX-02)
 *
 * This product has three ways of telling somebody something and no way of
 * asking what happened while you were out. E-mail templates fire from inside
 * the code that changes a ticket, the Desktop Notification add-on polls for
 * new tickets, and the rest is a number on a menu. None of them can answer
 * "what needs me" - which is the question, and the reason every other help
 * desk has a bell in the corner.
 *
 * Two ideas make this small enough to be right:
 *
 *   It reads the event stream.   4.5-ARCH-03 already announces everything that
 *                                happens to a ticket, with a documented
 *                                payload and a version. Notifications are a
 *                                subscriber, not a second set of hooks
 *                                scattered through the code that changes
 *                                things. Anything that ever emits an event -
 *                                a screen, an import, piping, an add-on -
 *                                notifies for free.
 *   It asks who is on the ticket. 4.5-FE-08 already knows: the assignee, the
 *                                watchers, the people named in a note, the
 *                                person asked to approve something. Working
 *                                out recipients per event type would be a
 *                                second answer to a question already answered.
 *
 * What a notification is, precisely: a row addressed to one person, carrying
 * what happened, where it happened, and what they can do about it without
 * going anywhere. Actionable is the difference between a bell that is useful
 * and a bell that is a list of things to go and look at.
 *
 * Quiet hours suppress interruption and never information. Outside somebody's
 * hours the in-app list still fills - it is a list you visit, and holding
 * things back from it would mean an agent arriving in the morning to a bell
 * that says nothing happened - while e-mail and push are held and gathered
 * into a digest. That distinction is the whole of the feature: a notification
 * system that goes quiet by dropping things is one nobody trusts, and one that
 * pushes at midnight is one everybody turns off.
 *
 * The Desktop Notification add-on is not switched off, replaced or touched. It
 * is retired by this being better, and a site running both simply gets both
 * until they turn it off themselves - the same courtesy every other add-on
 * this release supersedes has been given.
 */
class JSSTnotifications {

    /** Bumped when the table below changes. */
    const SCHEMA_VERSION = '450-UX02';

    /* ---------------------------------------------------------------------
     * What kinds of thing there are to be told
     *
     * Four categories rather than seventeen event types. Somebody setting
     * preferences is answering "how much do you want to be bothered", and a
     * grid of seventeen rows is a grid nobody fills in.
     * ------------------------------------------------------------------ */

    /** It is mine: assigned to me, I was named, I was asked to approve. */
    const CAT_MINE = 'mine';

    /** Something I follow changed. */
    const CAT_WATCHED = 'watched';

    /** A promise is about to be broken, or has been. */
    const CAT_ESCALATION = 'escalation';

    /** The software itself has something to say. */
    const CAT_SYSTEM = 'system';

    /* ---------------------------------------------------------------------
     * How somebody can be told
     * ------------------------------------------------------------------ */

    /** The list on the Notifications screen. Never suppressed. */
    const CHANNEL_INAPP = 'inapp';

    /** An e-mail per notification, or gathered into a digest. */
    const CHANNEL_EMAIL = 'email';

    /** A browser notification through the push service. */
    const CHANNEL_PUSH = 'push';

    /* ---------------------------------------------------------------------
     * Digests
     * ------------------------------------------------------------------ */

    const DIGEST_OFF    = 'off';
    const DIGEST_HOURLY = 'hourly';
    const DIGEST_DAILY  = 'daily';

    /** Where preferences live, keyed by staff id. */
    const OPT_PREFS = 'jsst_notification_prefs';

    /** The hook the digest runs on. */
    const DIGEST_HOOK = 'jsst_notification_digest';

    /** When each person last had a digest, keyed by staff id. */
    const OPT_DIGEST_SENT = 'jsst_notification_digest_sent';

    /** Rows older than this are cleared out. */
    const KEEP = 2592000;

    /** Preferences read this request. */
    private static $jsst_prefs = null;

    /* =====================================================================
     * Schema
     * ================================================================== */

    public static function ensureSchema() {
        if (!JSSTschemaguard::needsRun('jsst_notifications_schema', self::SCHEMA_VERSION, array(
                'js_ticket_notifications' => array('staffid', 'category', 'eventname', 'ticketid',
                    'title', 'body', 'url', 'actions', 'seen', 'held', 'created'),
            ))) {
            return;
        }
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `"
            . jssupportticket::$_db->prefix . "js_ticket_notifications` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    staffid int(11) NOT NULL,
                    wpuid bigint(20) NOT NULL DEFAULT 0,
                    category varchar(20) NOT NULL DEFAULT 'watched',
                    eventname varchar(60) NOT NULL DEFAULT '',
                    ticketid int(11) NOT NULL DEFAULT 0,
                    title varchar(255) NOT NULL DEFAULT '',
                    body text,
                    url varchar(255) NOT NULL DEFAULT '',
                    actions text,
                    seen tinyint(1) NOT NULL DEFAULT 0,
                    held tinyint(1) NOT NULL DEFAULT 0,
                    created datetime DEFAULT NULL,
                    PRIMARY KEY (id),
                    KEY jsst_staff (staffid),
                    KEY jsst_seen (seen),
                    KEY jsst_held (held)
                ) " . $jsst_charset);
        update_option('jsst_notifications_schema', self::SCHEMA_VERSION, false);
    }

    /* =====================================================================
     * Preferences
     * ================================================================== */

    /** What everybody gets until they say otherwise. */
    public static function defaults() {
        return array(
            /* In-app on for everything, because the list is the product and a
               list somebody has opted into is a list that starts empty and
               teaches them it is broken. E-mail on for what is theirs only:
               being e-mailed about every ticket you follow is how people build
               a filter that deletes us. */
            'channels' => array(
                self::CAT_MINE       => array(self::CHANNEL_INAPP => 1, self::CHANNEL_EMAIL => 1, self::CHANNEL_PUSH => 1),
                self::CAT_WATCHED    => array(self::CHANNEL_INAPP => 1, self::CHANNEL_EMAIL => 0, self::CHANNEL_PUSH => 0),
                self::CAT_ESCALATION => array(self::CHANNEL_INAPP => 1, self::CHANNEL_EMAIL => 1, self::CHANNEL_PUSH => 1),
                self::CAT_SYSTEM     => array(self::CHANNEL_INAPP => 1, self::CHANNEL_EMAIL => 0, self::CHANNEL_PUSH => 0),
            ),
            /* Off by default. Somebody who has not set working hours would
               otherwise be quiet for ever, and a feature that silently stops
               telling people things is the worst possible default. */
            'quiet'  => 0,
            'digest' => self::DIGEST_OFF,
        );
    }

    /** Every stored preference, keyed by staff id. */
    public static function allPrefs() {
        if (self::$jsst_prefs !== null) {
            return self::$jsst_prefs;
        }
        $jsst_prefs = get_option(self::OPT_PREFS, array());
        self::$jsst_prefs = is_array($jsst_prefs) ? $jsst_prefs : array();
        return self::$jsst_prefs;
    }

    /** One person's, complete. */
    public static function prefs($jsst_staffid) {
        $jsst_all = self::allPrefs();
        $jsst_staffid = (int) $jsst_staffid;
        $jsst_mine = isset($jsst_all[$jsst_staffid]) && is_array($jsst_all[$jsst_staffid])
            ? $jsst_all[$jsst_staffid] : array();
        $jsst_defaults = self::defaults();
        $jsst_out = $jsst_defaults;
        foreach ($jsst_defaults['channels'] as $jsst_cat => $jsst_channels) {
            foreach ($jsst_channels as $jsst_channel => $jsst_on) {
                if (isset($jsst_mine['channels'][$jsst_cat][$jsst_channel])) {
                    $jsst_out['channels'][$jsst_cat][$jsst_channel] = !empty($jsst_mine['channels'][$jsst_cat][$jsst_channel]) ? 1 : 0;
                }
            }
        }
        if (isset($jsst_mine['quiet'])) {
            $jsst_out['quiet'] = !empty($jsst_mine['quiet']) ? 1 : 0;
        }
        if (isset($jsst_mine['digest']) && in_array($jsst_mine['digest'],
                array(self::DIGEST_OFF, self::DIGEST_HOURLY, self::DIGEST_DAILY), true)) {
            $jsst_out['digest'] = $jsst_mine['digest'];
        }
        return $jsst_out;
    }

    /** Store one person's. */
    public static function savePrefs($jsst_staffid, $jsst_prefs) {
        $jsst_staffid = (int) $jsst_staffid;
        if ($jsst_staffid <= 0) {
            return esc_html(__('That is not an agent.', 'js-support-ticket'));
        }
        $jsst_all = self::allPrefs();
        $jsst_clean = array('channels' => array(), 'quiet' => 0, 'digest' => self::DIGEST_OFF);
        foreach (self::categories() as $jsst_cat => $jsst_label) {
            foreach (self::channels() as $jsst_channel => $jsst_clabel) {
                $jsst_clean['channels'][$jsst_cat][$jsst_channel] =
                    !empty($jsst_prefs['channels'][$jsst_cat][$jsst_channel]) ? 1 : 0;
            }
            /* In-app is not a choice. The list is where a notification lives;
               turning it off would mean a bell that lies about what happened,
               and the honest way to be told less is to switch off the noisy
               channels. */
            $jsst_clean['channels'][$jsst_cat][self::CHANNEL_INAPP] = 1;
        }
        $jsst_clean['quiet'] = !empty($jsst_prefs['quiet']) ? 1 : 0;
        if (isset($jsst_prefs['digest']) && in_array($jsst_prefs['digest'],
                array(self::DIGEST_OFF, self::DIGEST_HOURLY, self::DIGEST_DAILY), true)) {
            $jsst_clean['digest'] = $jsst_prefs['digest'];
        }
        $jsst_all[$jsst_staffid] = $jsst_clean;
        update_option(self::OPT_PREFS, $jsst_all, false);
        self::$jsst_prefs = $jsst_all;
        return true;
    }

    /** The four categories, as a person reads them. */
    public static function categories() {
        return array(
            self::CAT_MINE       => __('Mine — assigned to me, I was named, I was asked to approve', 'js-support-ticket'),
            self::CAT_WATCHED    => __('Tickets I follow', 'js-support-ticket'),
            self::CAT_ESCALATION => __('About to be late, or already late', 'js-support-ticket'),
            self::CAT_SYSTEM     => __('The help desk itself', 'js-support-ticket'),
        );
    }

    /** The three channels. */
    public static function channels() {
        return array(
            self::CHANNEL_INAPP => __('Here', 'js-support-ticket'),
            self::CHANNEL_EMAIL => __('E-mail', 'js-support-ticket'),
            self::CHANNEL_PUSH  => __('Browser', 'js-support-ticket'),
        );
    }

    /* =====================================================================
     * Listening
     * ================================================================== */

    /**
     * One event, fanned out to everybody it concerns.
     *
     * Recipients come from who is on the ticket rather than from a rule per
     * event type, because 4.5-FE-08 already answers that and two answers to
     * one question is how somebody stops being told about a ticket they are
     * watching.
     */
    public static function onEvent($jsst_envelope) {
        if (!is_array($jsst_envelope) || empty($jsst_envelope['name'])) {
            return;
        }
        $jsst_name = $jsst_envelope['name'];
        $jsst_payload = isset($jsst_envelope['payload']) && is_array($jsst_envelope['payload'])
            ? $jsst_envelope['payload'] : array();
        $jsst_ticketid = isset($jsst_payload['ticket_id']) ? (int) $jsst_payload['ticket_id'] : 0;
        $jsst_by = isset($jsst_envelope['actor']['staffid']) ? (int) $jsst_envelope['actor']['staffid'] : 0;

        $jsst_map = self::eventMap();
        if (!isset($jsst_map[$jsst_name])) {
            return;
        }
        $jsst_spec = $jsst_map[$jsst_name];
        $jsst_ticket = ($jsst_ticketid > 0) ? self::ticket($jsst_ticketid) : false;
        if ($jsst_ticketid > 0 && !$jsst_ticket) {
            return;
        }

        /* Whoever it is assigned to, and everybody following it. Both come
           from what the ticket says now, not from the payload, so a
           notification is never addressed on stale information. */
        $jsst_watchers = ($jsst_ticketid > 0 && class_exists('JSSTcollab'))
            ? JSSTcollab::watcherIds($jsst_ticketid) : array();
        $jsst_assignee = $jsst_ticket ? (int) $jsst_ticket->staffid : 0;
        $jsst_recipients = array();
        if ($jsst_assignee > 0) {
            $jsst_recipients[$jsst_assignee] = self::CAT_MINE;
        }
        foreach ($jsst_watchers as $jsst_watcher) {
            if (!isset($jsst_recipients[$jsst_watcher])) {
                $jsst_recipients[$jsst_watcher] = self::CAT_WATCHED;
            }
        }
        /* An assignment tells the person who just got it, and that is "mine"
           however they came to be on the list. */
        if ($jsst_name === JSSTevents::ASSIGNMENT_CHANGED && !empty($jsst_payload['staffid'])) {
            $jsst_recipients[(int) $jsst_payload['staffid']] = self::CAT_MINE;
        }
        if ($jsst_spec['category'] === self::CAT_ESCALATION) {
            foreach ($jsst_recipients as $jsst_who => $jsst_cat) {
                $jsst_recipients[$jsst_who] = self::CAT_ESCALATION;
            }
        }

        foreach ($jsst_recipients as $jsst_staffid => $jsst_category) {
            /* Nobody is told about their own action. The single most common
               way a notification system gets muted is telling people what they
               just did. */
            if ((int) $jsst_staffid === $jsst_by) {
                continue;
            }
            self::add(array(
                'staffid'   => (int) $jsst_staffid,
                'category'  => $jsst_category,
                'eventname' => $jsst_name,
                'ticketid'  => $jsst_ticketid,
                'title'     => self::title($jsst_spec, $jsst_ticket, $jsst_envelope),
                'body'      => self::body($jsst_spec, $jsst_ticket, $jsst_envelope),
                'actions'   => self::actionsFor($jsst_name, $jsst_ticketid, (int) $jsst_staffid),
            ));
        }

        /* Mentions are addressed to one person and are always theirs, whatever
           else they are on the ticket. Read from the records 4.5-FE-08 wrote a
           moment ago rather than parsed again here. */
        if ($jsst_name === JSSTevents::NOTE_ADDED && class_exists('JSSTcollab')) {
            $jsst_noteid = isset($jsst_payload['note_id']) ? (int) $jsst_payload['note_id'] : 0;
            foreach (self::mentionedOn($jsst_ticketid, $jsst_noteid) as $jsst_mentioned) {
                if ((int) $jsst_mentioned === $jsst_by) {
                    continue;
                }
                self::add(array(
                    'staffid'   => (int) $jsst_mentioned,
                    'category'  => self::CAT_MINE,
                    'eventname' => 'collab.mentioned',
                    'ticketid'  => $jsst_ticketid,
                    'title'     => esc_html(__('You were asked by name', 'js-support-ticket')),
                    'body'      => $jsst_ticket ? $jsst_ticket->subject : '',
                    'actions'   => self::actionsFor('collab.mentioned', $jsst_ticketid, (int) $jsst_mentioned),
                ));
            }
        }
    }

    /** Which events are worth telling somebody about, and how to say it. */
    public static function eventMap() {
        $jsst_map = array(
            JSSTevents::REPLY_ADDED => array(
                'category' => self::CAT_WATCHED,
                'title'    => __('Somebody answered', 'js-support-ticket'),
            ),
            JSSTevents::NOTE_ADDED => array(
                'category' => self::CAT_WATCHED,
                'title'    => __('An internal note was written', 'js-support-ticket'),
            ),
            JSSTevents::ASSIGNMENT_CHANGED => array(
                'category' => self::CAT_MINE,
                'title'    => __('A ticket changed hands', 'js-support-ticket'),
            ),
            JSSTevents::STATUS_CHANGED => array(
                'category' => self::CAT_WATCHED,
                'title'    => __('The status changed', 'js-support-ticket'),
            ),
            JSSTevents::PRIORITY_CHANGED => array(
                'category' => self::CAT_WATCHED,
                'title'    => __('The priority changed', 'js-support-ticket'),
            ),
            JSSTevents::DEPARTMENT_CHANGED => array(
                'category' => self::CAT_WATCHED,
                'title'    => __('It moved department', 'js-support-ticket'),
            ),
            JSSTevents::TICKET_CLOSED => array(
                'category' => self::CAT_WATCHED,
                'title'    => __('It was closed', 'js-support-ticket'),
            ),
            JSSTevents::TICKET_REOPENED => array(
                'category' => self::CAT_WATCHED,
                'title'    => __('It was reopened', 'js-support-ticket'),
            ),
            JSSTevents::SLA_WARNING => array(
                'category' => self::CAT_ESCALATION,
                'title'    => __('This is about to be late', 'js-support-ticket'),
            ),
            JSSTevents::SLA_BREACHED => array(
                'category' => self::CAT_ESCALATION,
                'title'    => __('This is late', 'js-support-ticket'),
            ),
            JSSTevents::FEEDBACK_RECEIVED => array(
                'category' => self::CAT_MINE,
                'title'    => __('A customer left feedback', 'js-support-ticket'),
            ),
        );
        return apply_filters('jsst_notification_events', $jsst_map);
    }

    /** What a notification offers to do without leaving the list. */
    public static function actionsFor($jsst_name, $jsst_ticketid, $jsst_staffid) {
        $jsst_actions = array();
        if ($jsst_ticketid > 0) {
            $jsst_actions[] = array('key' => 'open', 'label' => __('Open it', 'js-support-ticket'));
            /* Taking a ticket from the list is the one action worth having:
               the common case of being told about an unassigned ticket is
               wanting it. Everything else is a decision that needs the ticket
               in front of you. */
            if ($jsst_name === JSSTevents::SLA_WARNING || $jsst_name === JSSTevents::SLA_BREACHED
                    || $jsst_name === JSSTevents::ASSIGNMENT_CHANGED) {
                $jsst_actions[] = array('key' => 'take', 'label' => __('Assign it to me', 'js-support-ticket'));
            }
        }
        return apply_filters('jsst_notification_actions', $jsst_actions, $jsst_name, $jsst_ticketid, $jsst_staffid);
    }

    /** Who was named in one note. */
    private static function mentionedOn($jsst_ticketid, $jsst_noteid) {
        if (!class_exists('JSSTcollab') || $jsst_ticketid <= 0) {
            return array();
        }
        $jsst_rows = jssupportticket::$_db->get_col(jssupportticket::$_db->prepare(
            'SELECT staffid FROM `' . jssupportticket::$_db->prefix . 'js_ticket_mentions` '
            . 'WHERE ticketid = %d AND sourcetype = %s AND sourceid = %d',
            (int) $jsst_ticketid, 'note', (int) $jsst_noteid
        ));
        return is_array($jsst_rows) ? array_map('intval', $jsst_rows) : array();
    }

    /** The headline for one event. */
    private static function title($jsst_spec, $jsst_ticket, $jsst_envelope) {
        return isset($jsst_spec['title']) ? $jsst_spec['title'] : $jsst_envelope['name'];
    }

    /** The line under it: which ticket, and who did it. */
    private static function body($jsst_spec, $jsst_ticket, $jsst_envelope) {
        $jsst_who = isset($jsst_envelope['actor']['display']) ? $jsst_envelope['actor']['display'] : '';
        $jsst_subject = $jsst_ticket ? $jsst_ticket->subject : '';
        if ($jsst_who !== '' && $jsst_subject !== '') {
            /* translators: 1: a ticket subject, 2: who did it */
            return sprintf(esc_html(__('%1$s — by %2$s', 'js-support-ticket')), $jsst_subject, $jsst_who);
        }
        return $jsst_subject;
    }

    /** One ticket row, for addressing and titling. */
    private static function ticket($jsst_ticketid) {
        return jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT id, ticketid, subject, staffid, departmentid FROM `'
            . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d', (int) $jsst_ticketid
        ));
    }

    /* =====================================================================
     * Writing one
     * ================================================================== */

    /**
     * Add a notification for one person.
     *
     * Held rather than sent where the person is inside their quiet hours - and
     * held means the e-mail and the push wait for the digest, never that the
     * row is missing from the list. That is the distinction the whole feature
     * rests on: quiet hours suppress interruption, not information.
     */
    public static function add($jsst_row) {
        self::ensureSchema();
        $jsst_staffid = isset($jsst_row['staffid']) ? (int) $jsst_row['staffid'] : 0;
        if ($jsst_staffid <= 0) {
            return false;
        }
        $jsst_category = isset($jsst_row['category']) ? $jsst_row['category'] : self::CAT_WATCHED;
        $jsst_prefs = self::prefs($jsst_staffid);
        $jsst_quiet = self::isQuiet($jsst_staffid);
        $jsst_ticketid = isset($jsst_row['ticketid']) ? (int) $jsst_row['ticketid'] : 0;

        jssupportticket::$_db->insert(jssupportticket::$_db->prefix . 'js_ticket_notifications', array(
            'staffid'   => $jsst_staffid,
            'wpuid'     => class_exists('JSSTavailability') ? JSSTavailability::wpuidOf($jsst_staffid) : 0,
            'category'  => $jsst_category,
            'eventname' => isset($jsst_row['eventname']) ? $jsst_row['eventname'] : '',
            'ticketid'  => $jsst_ticketid,
            'title'     => isset($jsst_row['title']) ? mb_substr((string) $jsst_row['title'], 0, 250) : '',
            'body'      => isset($jsst_row['body']) ? $jsst_row['body'] : '',
            'url'       => self::urlFor($jsst_ticketid, $jsst_staffid),
            'actions'   => wp_json_encode(isset($jsst_row['actions']) ? $jsst_row['actions'] : array()),
            'seen'      => 0,
            'held'      => $jsst_quiet ? 1 : 0,
            'created'   => current_time('mysql'),
        ));
        $jsst_id = (int) jssupportticket::$_db->insert_id;

        if (!$jsst_quiet) {
            if (!empty($jsst_prefs['channels'][$jsst_category][self::CHANNEL_EMAIL])) {
                self::sendEmail($jsst_staffid, array($jsst_id));
            }
            if (!empty($jsst_prefs['channels'][$jsst_category][self::CHANNEL_PUSH])
                    && class_exists('JSSTwebpush')) {
                JSSTwebpush::send($jsst_staffid, array(
                    'title' => isset($jsst_row['title']) ? $jsst_row['title'] : '',
                    'body'  => isset($jsst_row['body']) ? $jsst_row['body'] : '',
                    'url'   => self::urlFor($jsst_ticketid),
                ));
            }
        }
        return $jsst_id;
    }

    /** Where a notification points. */
    public static function urlFor($jsst_ticketid, $jsst_staffid = 0) {
        if ((int) $jsst_ticketid <= 0) {
            return '';
        }
        if (class_exists('JSSTnavigation')) {
            /* The desk the RECIPIENT works at, not wp-admin for everybody.

               This was `SHELL_BACKEND` outright, so every notification linked
               into wp-admin however the reader got there - an agent reading
               their notifications in the portal was thrown out of it by the
               one button on the row. Worse for an agent restricted to the
               front end by a visibility record: the link goes somewhere they
               may not be at all.

               `allowedShells()` is the existing answer to "which desks may
               this person use", and the backend is preferred only when it is
               one of them. */
            $jsst_shell = JSSTworkspace::SHELL_BACKEND;
            if ((int) $jsst_staffid > 0 && class_exists('JSSTworkspace')) {
                $jsst_allowed = JSSTworkspace::allowedShells(array(
                    'actor' => self::actorFor((int) $jsst_staffid),
                ));
                if (!empty($jsst_allowed) && !in_array(JSSTworkspace::SHELL_BACKEND, $jsst_allowed, true)) {
                    $jsst_shell = $jsst_allowed[0];
                }
            }
            return JSSTnavigation::ticketUrl((int) $jsst_ticketid, $jsst_shell);
        }
        return admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_ticketid);
    }

    /** The actor for one staff member, for questions asked on their behalf. */
    private static function actorFor($jsst_staffid) {
        $jsst_wpuid = class_exists('JSSTavailability') ? (int) JSSTavailability::wpuidOf((int) $jsst_staffid) : 0;
        return JSSTcapability::actor($jsst_wpuid > 0 ? $jsst_wpuid : null);
    }

    /* =====================================================================
     * Quiet hours
     * ================================================================== */

    /**
     * Is this person off the clock?
     *
     * Their working hours, from 4.5-FE-06, rather than a second window set
     * here. Somebody who has already told the desk when they work should not
     * have to tell it again in different words, and two windows that can
     * disagree is a support conversation about which one won.
     */
    public static function isQuiet($jsst_staffid) {
        $jsst_prefs = self::prefs($jsst_staffid);
        if (empty($jsst_prefs['quiet']) || !class_exists('JSSTavailability')) {
            return false;
        }
        /* Somebody on leave is quiet whatever the hour, because the point of
           booking leave is not being interrupted. */
        if (JSSTavailability::isAbsent($jsst_staffid)) {
            return true;
        }
        return !JSSTavailability::isWorkingNow($jsst_staffid);
    }

    /* =====================================================================
     * Reading
     * ================================================================== */

    /** This person's notifications, newest first. */
    public static function forAgent($jsst_staffid, $jsst_limit = 30, $jsst_unseenonly = false) {
        self::ensureSchema();
        $jsst_where = jssupportticket::$_db->prepare('WHERE staffid = %d', (int) $jsst_staffid);
        if ($jsst_unseenonly) {
            $jsst_where .= ' AND seen = 0';
        }
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_notifications` '
            . $jsst_where . ' ORDER BY created DESC, id DESC LIMIT %d', max(1, (int) $jsst_limit)
        ));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /** How many are unread, for the badge. */
    public static function unseen($jsst_staffid) {
        self::ensureSchema();
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_notifications` '
            . 'WHERE staffid = %d AND seen = 0', (int) $jsst_staffid
        ));
    }

    /** Mark read. Only ever your own. */
    public static function markSeen($jsst_staffid, $jsst_id = 0) {
        self::ensureSchema();
        $jsst_where = array('staffid' => (int) $jsst_staffid);
        if ((int) $jsst_id > 0) {
            $jsst_where['id'] = (int) $jsst_id;
        }
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_notifications',
            array('seen' => 1), $jsst_where);
        return true;
    }

    /** The actions on one notification, decoded. */
    public static function actionsOn($jsst_row) {
        if (!is_object($jsst_row) || empty($jsst_row->actions)) {
            return array();
        }
        $jsst_actions = json_decode($jsst_row->actions, true);
        return is_array($jsst_actions) ? $jsst_actions : array();
    }

    /* =====================================================================
     * Digests
     * ================================================================== */

    /**
     * Send what was held while people were off the clock.
     *
     * One e-mail per person containing everything held, rather than the pile
     * of individual e-mails they were spared at the time. Run hourly; each
     * person's own setting decides whether their turn has come.
     */
    public static function runDigests($jsst_now = null) {
        self::ensureSchema();
        $jsst_now = ($jsst_now === null) ? time() : (int) $jsst_now;
        $jsst_sent = array('people' => 0, 'notifications' => 0);
        $jsst_staff = jssupportticket::$_db->get_col(
            'SELECT DISTINCT staffid FROM `' . jssupportticket::$_db->prefix
            . 'js_ticket_notifications` WHERE held = 1'
        );
        foreach ((array) $jsst_staff as $jsst_staffid) {
            $jsst_staffid = (int) $jsst_staffid;
            $jsst_prefs = self::prefs($jsst_staffid);
            if ($jsst_prefs['digest'] === self::DIGEST_OFF) {
                /* No digest asked for: the held rows are released so they stop
                   being held for ever, and the list has had them all along. */
                self::release($jsst_staffid);
                continue;
            }
            /* Still off the clock: leave them held. Sending the digest into
               the quiet hours it exists to protect would be the one mistake
               this feature cannot afford. */
            if (self::isQuiet($jsst_staffid)) {
                continue;
            }
            if ($jsst_prefs['digest'] === self::DIGEST_DAILY && !self::dailyDue($jsst_staffid, $jsst_now)) {
                continue;
            }
            $jsst_held = self::heldFor($jsst_staffid);
            if (empty($jsst_held)) {
                continue;
            }
            self::sendEmail($jsst_staffid, wp_list_pluck($jsst_held, 'id'), true);
            self::release($jsst_staffid);
            $jsst_sent['people']++;
            $jsst_sent['notifications'] += count($jsst_held);
        }
        return $jsst_sent;
    }

    /** What is waiting for one person. */
    public static function heldFor($jsst_staffid) {
        self::ensureSchema();
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_notifications` '
            . 'WHERE staffid = %d AND held = 1 ORDER BY created ASC', (int) $jsst_staffid
        ));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /** Stop holding them. */
    private static function release($jsst_staffid) {
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_notifications',
            array('held' => 0), array('staffid' => (int) $jsst_staffid, 'held' => 1));
    }

    /**
     * Has a day passed since this person's last digest?
     *
     * All of them in one option keyed by staff id, rather than an option per
     * agent. A per-agent name grows the options table by one row for every
     * person who ever receives a digest and cannot be removed by an uninstall
     * that deletes options by name - so the rows would outlive the plugin.
     */
    private static function dailyDue($jsst_staffid, $jsst_now) {
        $jsst_staffid = (int) $jsst_staffid;
        $jsst_sent = get_option(self::OPT_DIGEST_SENT, array());
        $jsst_sent = is_array($jsst_sent) ? $jsst_sent : array();
        $jsst_last = isset($jsst_sent[$jsst_staffid]) ? (int) $jsst_sent[$jsst_staffid] : 0;
        if (($jsst_now - $jsst_last) < DAY_IN_SECONDS) {
            return false;
        }
        $jsst_sent[$jsst_staffid] = $jsst_now;
        update_option(self::OPT_DIGEST_SENT, $jsst_sent, false);
        return true;
    }

    /**
     * The e-mail itself.
     *
     * Deliberately plain and sent through wp_mail rather than through the
     * template manager: these are one line each and a person gets several a
     * day, and dressing them in the customer-facing template would make an
     * internal note look like something the customer was sent.
     */
    public static function sendEmail($jsst_staffid, $jsst_ids, $jsst_isdigest = false) {
        if (empty($jsst_ids)) {
            return false;
        }
        $jsst_wpuid = class_exists('JSSTavailability') ? JSSTavailability::wpuidOf($jsst_staffid) : 0;
        $jsst_user = ($jsst_wpuid > 0) ? get_userdata($jsst_wpuid) : false;
        if (!$jsst_user || empty($jsst_user->user_email)) {
            return false;
        }
        $jsst_ids = array_map('intval', (array) $jsst_ids);
        $jsst_rows = jssupportticket::$_db->get_results(
            'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_notifications` '
            . 'WHERE id IN (' . implode(',', $jsst_ids) . ') ORDER BY created ASC'
        );
        if (empty($jsst_rows)) {
            return false;
        }
        $jsst_lines = array();
        foreach ($jsst_rows as $jsst_row) {
            $jsst_lines[] = '- ' . $jsst_row->title . ($jsst_row->body !== '' ? ': ' . $jsst_row->body : '')
                . ($jsst_row->url !== '' ? "\n  " . $jsst_row->url : '');
        }
        $jsst_subject = $jsst_isdigest
            ? sprintf(
                /* translators: %d: how many things happened */
                _n('%d thing happened while you were away', '%d things happened while you were away', count($jsst_rows), 'js-support-ticket'),
                count($jsst_rows))
            : $jsst_rows[0]->title;
        return wp_mail($jsst_user->user_email, $jsst_subject, implode("\n\n", $jsst_lines));
    }

    /** Clear out anything old enough not to matter. */
    public static function prune() {
        self::ensureSchema();
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            'DELETE FROM `' . jssupportticket::$_db->prefix . 'js_ticket_notifications` '
            . 'WHERE seen = 1 AND created < %s',
            gmdate('Y-m-d H:i:s', time() - self::KEEP)
        ));
    }

    /** Forget anything read this request. */
    public static function flush() {
        self::$jsst_prefs = null;
    }
}
