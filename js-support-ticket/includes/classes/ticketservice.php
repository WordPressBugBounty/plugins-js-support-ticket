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
if (class_exists('JSSTticketservice')) {
    return;
}

/**
 * Ticket commands — the write half of the application layer.
 * (Roadmap 4.5-ARCH-02)
 *
 * Everything that changes a ticket goes through one of these. Not because the
 * models underneath are wrong, but because the same six things have to happen
 * around every change and, until now, happened in some order or other at each
 * of the places that could make one: the admin screen, the front-end portal,
 * email piping, cron, a bulk action, an add-on.
 *
 * A command does the six in the same order every time:
 *
 *   1. Read the ticket, and refuse a command against one that does not exist.
 *   2. Ask the capability service, with the actor as an argument rather than
 *      as an assumption. This is the enforcement point: a screen may still hide
 *      a button, but hiding is not enforcing, and every write is checked here.
 *   3. Record what the ticket looked like before.
 *   4. Hand the work to the model that already knows how to do it. The models
 *      are not reimplemented — they carry years of behaviour, email templates
 *      and add-on hooks, and reproducing that would be how this release breaks
 *      working sites.
 *   5. Read the ticket back and confirm the change actually landed. The models
 *      return nothing and report failure by setting a message on a screen, so
 *      "did that work?" has to be answered by looking.
 *   6. Emit the domain event, once, with the before and after.
 *
 * Step six is why the frontend workspace can be built without touching the
 * notification, automation or reporting code: they subscribe to the event, not
 * to the screen that caused it.
 *
 * Every command returns either an array describing what happened, or a
 * WP_Error. Nothing echoes, nothing redirects, nothing sets a message: a
 * command that writes to the screen cannot be called by cron, by REST, or by
 * the other workspace, which is the whole problem being fixed.
 *
 * One honest limit while the models are still where the work happens. Each of
 * them re-checks the permission itself, against whoever is signed in on this
 * request — that is the check this release is replacing, and it stays for now
 * as a second lock on the same door. It means a command run on behalf of an
 * actor who is not the current user passes this class's check and can still be
 * refused by the model underneath. That is the safe direction to fail, and it
 * is why acting for another user is confined to the inspector, which asks
 * rather than does. Automation is the exception that already worked: the close
 * path takes a flag for it, and this class sets that flag from the actor.
 * Moving the model checks out is the tail of this task, and it happens one
 * command at a time as each screen starts calling this class instead of the
 * model.
 */
class JSSTticketservice {

    /** The id storeTickets() announced, for the length of one create(). */
    private static $jsst_created = 0;

    /**
     * Remember the id of a ticket just created.
     *
     * Public only because WordPress has to be able to call it; it is hooked and
     * unhooked around one model call and means nothing outside that window.
     */
    public static function captureCreatedId($jsst_data, $jsst_ticketid) {
        self::$jsst_created = (int) $jsst_ticketid;
    }

    /* =====================================================================
     * Commands
     * ================================================================== */

    /**
     * Raise a ticket.
     *
     * @param array $jsst_data  The submitted form data, as the model expects it.
     * @param array $jsst_options 'actor' => actor array, 'source' => string.
     * @return array|WP_Error
     */
    public static function create($jsst_data, $jsst_options = array()) {
        $jsst_actor = self::actor($jsst_options);
        $jsst_onbehalf = !empty($jsst_data['onbehalf']) || !empty($jsst_options['on_behalf']);
        $jsst_action = $jsst_onbehalf ? JSSTcapability::TICKET_CREATE_FOR : JSSTcapability::TICKET_CREATE;

        $jsst_allowed = JSSTcapability::assert($jsst_action, array(), $jsst_actor);
        if (is_wp_error($jsst_allowed)) {
            return $jsst_allowed;
        }
        /* storeTickets() returns nothing — it is written to end in a redirect.
           It does announce the new id on jsst_after_ticket_create, and only on
           the success path, so listening for one request is how the command
           learns what it created. The highest id is the fallback for a site
           where something has unhooked that action; it is right on a
           single-threaded request and wrong under concurrency, which is why it
           is second rather than first. */
        $jsst_before = self::maxTicketId();
        self::$jsst_created = 0;
        add_action('jsst_after_ticket_create', array(__CLASS__, 'captureCreatedId'), 1, 2);
        try {
            $jsst_returned = JSSTincluder::getJSModel('ticket')->storeTickets($jsst_data);
        } finally {
            remove_action('jsst_after_ticket_create', array(__CLASS__, 'captureCreatedId'), 1);
        }
        /* A resent identical request creates nothing and announces nothing:
           storeTickets() hands back the ticket the first one created
           (JSSTsubmitguard). That is the answer, not a failure. */
        if (self::$jsst_created === 0 && is_numeric($jsst_returned) && (int) $jsst_returned > 0 && (int) $jsst_returned <= $jsst_before) {
            return self::unchanged((int) $jsst_returned, 'created');
        }
        $jsst_ticketid = (self::$jsst_created > 0) ? self::$jsst_created : self::maxTicketId();
        if ($jsst_ticketid <= 0 || $jsst_ticketid === $jsst_before) {
            return new WP_Error('jsst_ticket_not_created',
                esc_html(__('The ticket was not saved.', 'js-support-ticket')));
        }
        /* The event was emitted by storeTickets(), which is where every door
           into this desk passes - the form and e-mail piping never come
           through here at all. Emitting a second one would give an API-created
           ticket two `ticket.created` events and everything downstream of them
           twice: two SLA clock starts, two webhook deliveries, one assignment
           undone by another. So this reads what was announced rather than
           announcing it again. (Roadmap 4.5-ARCH-03) */
        $jsst_event = false;
        foreach (array_reverse(JSSTevents::emittedThisRequest()) as $jsst_seen) {
            if (isset($jsst_seen['event'], $jsst_seen['payload']['ticket_id'])
                    && $jsst_seen['event'] === JSSTevents::TICKET_CREATED
                    && (int) $jsst_seen['payload']['ticket_id'] === $jsst_ticketid) {
                $jsst_event = $jsst_seen;
                break;
            }
        }

        return self::done($jsst_ticketid, 'created', $jsst_event);
    }

    /**
     * Answer the customer.
     *
     * @param int   $jsst_ticketid
     * @param array $jsst_data The reply form data, as JSSTreplyModel expects it.
     */
    public static function reply($jsst_ticketid, $jsst_data, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_REPLY, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        /* The model this delegates to was written for the reply form and still
           reads what that form posts: the ticket's own reference and hash, to
           prove whoever is replying is entitled to, and the body under
           `jsticket_message`. A caller in code has a ticket id and a string —
           an automation rule, the REST API, an inbound webhook — so the halves
           the form would have supplied are filled in here, once, instead of in
           each of them. Without this storeReplies() returns at its ownership
           check and writes nothing at all, silently.
           (Roadmap 5.0-AUT-01, 5.0-API-01, 5.0-API-03) */
        $jsst_row = self::ticket($jsst_ticketid, true);
        if (!$jsst_row) {
            return new WP_Error('jsst_reply_no_ticket', esc_html(__('That ticket no longer exists.', 'js-support-ticket')));
        }
        if (!isset($jsst_data['ticketid'])) {
            $jsst_data['ticketid'] = $jsst_ticketid;
        }
        if (!isset($jsst_data['ticketrandomid'])) {
            $jsst_data['ticketrandomid'] = $jsst_row->ticketid;
        }
        if (!isset($jsst_data['hash'])) {
            $jsst_data['hash'] = isset($jsst_row->hash) ? $jsst_row->hash : '';
        }
        if (!isset($jsst_data['jsticket_message'])) {
            $jsst_data['jsticket_message'] = isset($jsst_data['message']) ? $jsst_data['message'] : '';
        }
        /* The duplicate guard in the model asks "did this same account reply in
           the last seven seconds", so it needs an account. Zero is the honest
           answer for a reply the desk itself wrote: it belongs to no customer's
           login, and two automations answering the same ticket a second apart
           are then still both recorded, which is what the audit needs. */
        if (!isset($jsst_data['uid'])) {
            $jsst_data['uid'] = 0;
        }
        $jsst_before = self::lastReplyId($jsst_ticketid);
        JSSTincluder::getJSModel('reply')->storeReplies($jsst_data);
        $jsst_after = self::lastReplyId($jsst_ticketid);
        if ($jsst_after === $jsst_before) {
            return new WP_Error('jsst_reply_not_saved',
                esc_html(__('The reply was not saved.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::REPLY_ADDED, array(
            'ticket_id'   => $jsst_ticketid,
            'reply_id'    => $jsst_after,
            'author_kind' => $jsst_actor['kind'],
            'author_uid'  => (int) $jsst_actor['uid'],
            'is_customer' => in_array($jsst_actor['kind'], array(JSSTcapability::ACTOR_CUSTOMER, JSSTcapability::ACTOR_GUEST), true),
        ), $jsst_options);

        return self::done($jsst_ticketid, 'replied', $jsst_event, array('reply_id' => $jsst_after));
    }

    /** Write an internal note. */
    public static function note($jsst_ticketid, $jsst_data, $jsst_note, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_NOTE, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        if (!JSSTmergedaddon::featureEnabled('note')) {
            return new WP_Error('jsst_notes_off',
                esc_html(__('Internal notes are switched off on this site.', 'js-support-ticket')));
        }
        $jsst_data['ticketid'] = $jsst_ticketid;
        $jsst_before = self::lastNoteId($jsst_ticketid);
        JSSTincluder::getJSModel('note')->storeTicketInternalNote($jsst_data, $jsst_note);
        $jsst_after = self::lastNoteId($jsst_ticketid);
        if ($jsst_after === $jsst_before) {
            return new WP_Error('jsst_note_not_saved',
                esc_html(__('The note was not saved.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::NOTE_ADDED, array(
            'ticket_id'  => $jsst_ticketid,
            'note_id'    => $jsst_after,
            'author_uid' => (int) $jsst_actor['uid'],
        ), $jsst_options);

        return self::done($jsst_ticketid, 'noted', $jsst_event, array('note_id' => $jsst_after));
    }

    /** Assign, reassign or unassign. */
    public static function assign($jsst_ticketid, $jsst_staffid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_staffid = (int) $jsst_staffid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_ASSIGN, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        $jsst_ticket = self::ticket($jsst_ticketid);
        $jsst_previous = (int) $jsst_ticket->staffid;
        if ($jsst_previous === $jsst_staffid) {
            return self::unchanged($jsst_ticketid, 'assigned');
        }
        $jsst_data = array_merge(
            isset($jsst_options['data']) && is_array($jsst_options['data']) ? $jsst_options['data'] : array(),
            array('ticketid' => $jsst_ticketid, 'staffid' => $jsst_staffid)
        );
        JSSTincluder::getJSModel('ticket')->assignTicketToStaff($jsst_data);
        $jsst_now = self::ticket($jsst_ticketid, true);
        if (!$jsst_now || (int) $jsst_now->staffid !== $jsst_staffid) {
            return new WP_Error('jsst_assign_failed',
                esc_html(__('The ticket was not reassigned.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::ASSIGNMENT_CHANGED, array(
            'ticket_id'        => $jsst_ticketid,
            'staffid'          => $jsst_staffid,
            'previous_staffid' => $jsst_previous,
            'reason'           => JSSTticketaction::reason($jsst_data),
            'automatic'        => JSSTcapability::isSystem(),
        ), $jsst_options);

        return self::done($jsst_ticketid, 'assigned', $jsst_event, array(
            'staffid' => $jsst_staffid, 'previous_staffid' => $jsst_previous,
        ));
    }

    /** Move a ticket to another department. */
    public static function transfer($jsst_ticketid, $jsst_departmentid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_departmentid = (int) $jsst_departmentid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_TRANSFER, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        $jsst_ticket = self::ticket($jsst_ticketid);
        $jsst_previous = (int) $jsst_ticket->departmentid;
        if ($jsst_previous === $jsst_departmentid) {
            return self::unchanged($jsst_ticketid, 'transferred');
        }
        $jsst_data = array_merge(
            isset($jsst_options['data']) && is_array($jsst_options['data']) ? $jsst_options['data'] : array(),
            array('ticketid' => $jsst_ticketid, 'departmentid' => $jsst_departmentid)
        );
        JSSTincluder::getJSModel('ticket')->tickDepartmentTransfer($jsst_data);
        $jsst_now = self::ticket($jsst_ticketid, true);
        if (!$jsst_now || (int) $jsst_now->departmentid !== $jsst_departmentid) {
            return new WP_Error('jsst_transfer_failed',
                esc_html(__('The ticket was not transferred.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::DEPARTMENT_CHANGED, array(
            'ticket_id'             => $jsst_ticketid,
            'departmentid'          => $jsst_departmentid,
            'previous_departmentid' => $jsst_previous,
            'reason'                => JSSTticketaction::reason($jsst_data),
        ), $jsst_options);

        return self::done($jsst_ticketid, 'transferred', $jsst_event, array(
            'departmentid' => $jsst_departmentid, 'previous_departmentid' => $jsst_previous,
        ));
    }

    /** Set the status directly. */
    public static function status($jsst_ticketid, $jsst_status, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_status = (int) $jsst_status;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_STATUS, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        $jsst_ticket = self::ticket($jsst_ticketid);
        $jsst_previous = (int) $jsst_ticket->status;
        if ($jsst_previous === $jsst_status) {
            return self::unchanged($jsst_ticketid, 'status');
        }
        $jsst_data = array_merge(
            isset($jsst_options['data']) && is_array($jsst_options['data']) ? $jsst_options['data'] : array(),
            array('ticketid' => $jsst_ticketid, 'statusid' => $jsst_status, 'status' => $jsst_status)
        );
        JSSTincluder::getJSModel('ticket')->tickChangeStatus($jsst_data);
        $jsst_now = self::ticket($jsst_ticketid, true);
        if (!$jsst_now || (int) $jsst_now->status !== $jsst_status) {
            return new WP_Error('jsst_status_failed',
                esc_html(__('The status was not changed.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::STATUS_CHANGED, array(
            'ticket_id'       => $jsst_ticketid,
            'status'          => $jsst_status,
            'previous_status' => $jsst_previous,
            'reason'          => JSSTticketaction::reason($jsst_data),
        ), $jsst_options);

        return self::done($jsst_ticketid, 'status', $jsst_event, array(
            'status' => $jsst_status, 'previous_status' => $jsst_previous,
        ));
    }

    /** Change the priority. */
    public static function priority($jsst_ticketid, $jsst_priorityid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_priorityid = (int) $jsst_priorityid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_PRIORITY, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        $jsst_ticket = self::ticket($jsst_ticketid);
        $jsst_previous = (int) $jsst_ticket->priorityid;
        if ($jsst_previous === $jsst_priorityid) {
            return self::unchanged($jsst_ticketid, 'priority');
        }
        JSSTincluder::getJSModel('ticket')->changeTicketPriority($jsst_ticketid, $jsst_priorityid);
        $jsst_now = self::ticket($jsst_ticketid, true);
        if (!$jsst_now || (int) $jsst_now->priorityid !== $jsst_priorityid) {
            return new WP_Error('jsst_priority_failed',
                esc_html(__('The priority was not changed.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::PRIORITY_CHANGED, array(
            'ticket_id'           => $jsst_ticketid,
            'priorityid'          => $jsst_priorityid,
            'previous_priorityid' => $jsst_previous,
        ), $jsst_options);

        return self::done($jsst_ticketid, 'priority', $jsst_event, array(
            'priorityid' => $jsst_priorityid, 'previous_priorityid' => $jsst_previous,
        ));
    }

    /** Close a ticket. */
    public static function close($jsst_ticketid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_CLOSE, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        $jsst_ticket = self::ticket($jsst_ticketid);
        if (self::isClosed((int) $jsst_ticket->status)) {
            return self::unchanged($jsst_ticketid, 'closed');
        }
        /* The second argument is the model's cron flag: it suppresses the
           "closed by" attribution and the customer email that a person pressing
           Close is expected to trigger. Automation passes 1 for exactly that
           reason, which is now a property of the actor rather than of which
           file happened to call. */
        JSSTincluder::getJSModel('ticket')->closeTicket($jsst_ticketid, JSSTcapability::isSystem() ? 1 : 0);
        $jsst_now = self::ticket($jsst_ticketid, true);
        if (!$jsst_now || !self::isClosed((int) $jsst_now->status)) {
            return new WP_Error('jsst_close_failed',
                esc_html(__('The ticket was not closed.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::TICKET_CLOSED, array(
            'ticket_id' => $jsst_ticketid,
            'closed_by' => (int) $jsst_actor['uid'],
            'reason'    => JSSTticketaction::reason(isset($jsst_options['data']) ? $jsst_options['data'] : array()),
            'automatic' => JSSTcapability::isSystem(),
        ), $jsst_options);

        return self::done($jsst_ticketid, 'closed', $jsst_event);
    }

    /** Reopen a closed ticket. */
    public static function reopen($jsst_ticketid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_REOPEN, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        $jsst_ticket = self::ticket($jsst_ticketid);
        if (!self::isClosed((int) $jsst_ticket->status)) {
            return self::unchanged($jsst_ticketid, 'reopened');
        }
        $jsst_data = array_merge(
            isset($jsst_options['data']) && is_array($jsst_options['data']) ? $jsst_options['data'] : array(),
            array('ticketid' => $jsst_ticketid)
        );
        JSSTincluder::getJSModel('ticket')->reopenTicket($jsst_data);
        $jsst_now = self::ticket($jsst_ticketid, true);
        if (!$jsst_now || self::isClosed((int) $jsst_now->status)) {
            return new WP_Error('jsst_reopen_failed',
                esc_html(__('The ticket was not reopened.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::TICKET_REOPENED, array(
            'ticket_id'   => $jsst_ticketid,
            'reopened_by' => (int) $jsst_actor['uid'],
            'reason'      => JSSTticketaction::reason($jsst_data),
        ), $jsst_options);

        return self::done($jsst_ticketid, 'reopened', $jsst_event);
    }

    /** Mark a ticket as being worked on. */
    public static function progress($jsst_ticketid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_PROGRESS, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        $jsst_previous = (int) self::ticket($jsst_ticketid)->status;
        $jsst_data = array_merge(
            isset($jsst_options['data']) && is_array($jsst_options['data']) ? $jsst_options['data'] : array(),
            array('ticketid' => $jsst_ticketid)
        );
        JSSTincluder::getJSModel('ticket')->markTicketInProgress($jsst_data);
        $jsst_now = self::ticket($jsst_ticketid, true);
        if (!$jsst_now || (int) $jsst_now->status === $jsst_previous) {
            return self::unchanged($jsst_ticketid, 'progress');
        }
        $jsst_event = JSSTevents::emit(JSSTevents::STATUS_CHANGED, array(
            'ticket_id'       => $jsst_ticketid,
            'status'          => (int) $jsst_now->status,
            'previous_status' => $jsst_previous,
            'reason'          => JSSTticketaction::reason($jsst_data),
        ), $jsst_options);

        return self::done($jsst_ticketid, 'progress', $jsst_event);
    }

    /**
     * Merge one ticket into another.
     *
     * Merging lives in the Merge Ticket add-on, so this command checks that it
     * is there rather than assuming. The capability question is asked about
     * both tickets: merging reads one and writes the other, and an agent who
     * may touch only one of them may not do it.
     */
    public static function merge($jsst_sourceid, $jsst_primaryid, $jsst_options = array()) {
        $jsst_sourceid = (int) $jsst_sourceid;
        $jsst_primaryid = (int) $jsst_primaryid;
        $jsst_actor = self::actor($jsst_options);
        foreach (array($jsst_sourceid, $jsst_primaryid) as $jsst_id) {
            $jsst_guard = self::guard(JSSTcapability::TICKET_MERGE, $jsst_id, $jsst_actor, $jsst_options);
            if (is_wp_error($jsst_guard)) {
                return $jsst_guard;
            }
        }
        if ($jsst_sourceid === $jsst_primaryid) {
            return new WP_Error('jsst_merge_same',
                esc_html(__('A ticket cannot be merged into itself.', 'js-support-ticket')));
        }
        if (!JSSTmergedaddon::featureEnabled('mergeticket')) {
            return new WP_Error('jsst_merge_unavailable',
                esc_html(__('Merging is not available on this site.', 'js-support-ticket')));
        }
        $jsst_data = array_merge(
            isset($jsst_options['data']) && is_array($jsst_options['data']) ? $jsst_options['data'] : array(),
            array('ticketid' => $jsst_sourceid, 'mergewith' => $jsst_primaryid)
        );
        JSSTincluder::getJSModel('mergeticket')->storeMergeTicket($jsst_data);
        $jsst_now = self::ticket($jsst_sourceid, true);
        if (!$jsst_now || (int) $jsst_now->mergestatus !== 1) {
            return new WP_Error('jsst_merge_failed',
                esc_html(__('The tickets were not merged.', 'js-support-ticket')));
        }
        $jsst_event = JSSTevents::emit(JSSTevents::TICKET_MERGED, array(
            'ticket_id'   => $jsst_sourceid,
            'merged_into' => $jsst_primaryid,
            'note'        => isset($jsst_data['mergenote']) ? (string) $jsst_data['mergenote'] : '',
        ), $jsst_options);

        return self::done($jsst_sourceid, 'merged', $jsst_event, array('merged_into' => $jsst_primaryid));
    }

    /** Delete a ticket and everything attached to it. */
    public static function delete($jsst_ticketid, $jsst_options = array()) {
        $jsst_ticketid = (int) $jsst_ticketid;
        $jsst_actor = self::actor($jsst_options);
        $jsst_guard = self::guard(JSSTcapability::TICKET_DELETE, $jsst_ticketid, $jsst_actor, $jsst_options);
        if (is_wp_error($jsst_guard)) {
            return $jsst_guard;
        }
        $jsst_ticket = self::ticket($jsst_ticketid);
        $jsst_snapshot = array(
            'ticket_id' => $jsst_ticketid,
            'ticketid'  => $jsst_ticket->ticketid,
            'subject'   => $jsst_ticket->subject,
            'uid'       => (int) $jsst_ticket->uid,
        );
        JSSTincluder::getJSModel('ticket')->removeTicket($jsst_ticketid);
        if (self::ticket($jsst_ticketid, true)) {
            return new WP_Error('jsst_delete_failed',
                esc_html(__('The ticket was not deleted.', 'js-support-ticket')));
        }
        /* Emitted after the row is gone, which is the point: a subscriber must
           not be handed an id it can still read, or half of them will write the
           deletion into a record that no longer has anything to point at. */
        $jsst_event = JSSTevents::emit(JSSTevents::TICKET_DELETED, $jsst_snapshot, $jsst_options);

        return self::done($jsst_ticketid, 'deleted', $jsst_event);
    }

    /* =====================================================================
     * Batches
     * ================================================================== */

    /**
     * Run one command over many tickets, with the permission asked per ticket.
     *
     * A bulk action that checks the permission once and then loops is how an
     * agent closes a department they cannot see. Every ticket is its own
     * question, and one refusal does not stop the rest — the caller gets a
     * count of each so the screen can say "9 closed, 1 was not yours".
     *
     * @param string $jsst_command  A method name on this class.
     * @param int[]  $jsst_ids
     * @param array  $jsst_args     Extra arguments after the ticket id.
     * @return array done, refused, failed, results
     */
    public static function bulk($jsst_command, $jsst_ids, $jsst_args = array(), $jsst_options = array()) {
        $jsst_allowed = array('close', 'reopen', 'progress', 'assign', 'transfer', 'status', 'priority', 'delete');
        if (!in_array($jsst_command, $jsst_allowed, true)) {
            return array('done' => 0, 'refused' => 0, 'failed' => 0, 'results' => array(),
                'error' => new WP_Error('jsst_unknown_command',
                    esc_html(__('That is not a bulk action.', 'js-support-ticket'))));
        }
        $jsst_out = array('done' => 0, 'refused' => 0, 'failed' => 0, 'results' => array());
        foreach (JSSTticketaction::parseIds($jsst_ids) as $jsst_id) {
            $jsst_call = array_merge(array($jsst_id), array_values($jsst_args), array($jsst_options));
            $jsst_result = call_user_func_array(array(__CLASS__, $jsst_command), $jsst_call);
            $jsst_out['results'][$jsst_id] = $jsst_result;
            if (is_wp_error($jsst_result)) {
                if (strpos($jsst_result->get_error_code(), 'jsst_forbidden') === 0) {
                    $jsst_out['refused']++;
                } else {
                    $jsst_out['failed']++;
                }
                continue;
            }
            $jsst_out['done']++;
        }
        return $jsst_out;
    }

    /* =====================================================================
     * Shared pieces
     * ================================================================== */

    /**
     * The two checks every subject-scoped command starts with: the ticket has
     * to exist, and the actor has to be allowed to do this to it.
     *
     * @return true|WP_Error
     */
    private static function guard($jsst_action, $jsst_ticketid, $jsst_actor, $jsst_options) {
        if ($jsst_ticketid <= 0 || !self::ticket($jsst_ticketid)) {
            return new WP_Error('jsst_ticket_missing',
                esc_html(__('That ticket does not exist.', 'js-support-ticket')),
                array('status' => 404));
        }
        $jsst_subject = array('ticket' => $jsst_ticketid);
        if (isset($jsst_options['token'])) {
            $jsst_subject['token'] = $jsst_options['token'];
        }
        return JSSTcapability::assert($jsst_action, $jsst_subject, $jsst_actor);
    }

    /** Whoever the caller says is acting, or whoever is on the request. */
    private static function actor($jsst_options) {
        if (isset($jsst_options['actor']) && is_array($jsst_options['actor'])) {
            return $jsst_options['actor'];
        }
        if (isset($jsst_options['wpuid'])) {
            return JSSTcapability::actor((int) $jsst_options['wpuid']);
        }
        return JSSTcapability::actor();
    }

    /**
     * A ticket row, cached for the command's own use.
     *
     * $jsst_fresh forces a re-read: the "before" and "after" of a command must
     * not be the same cached row, or every command would report that nothing
     * changed.
     */
    private static function ticket($jsst_ticketid, $jsst_fresh = false) {
        static $jsst_rows = array();
        $jsst_ticketid = (int) $jsst_ticketid;
        if ($jsst_ticketid <= 0) {
            return false;
        }
        if (!$jsst_fresh && array_key_exists($jsst_ticketid, $jsst_rows)) {
            return $jsst_rows[$jsst_ticketid];
        }
        $jsst_row = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE id = %d',
            $jsst_ticketid
        ));
        $jsst_rows[$jsst_ticketid] = $jsst_row ? $jsst_row : false;
        if ($jsst_fresh) {
            // The capability service holds its own copy for scope checks.
            JSSTcapability::flush();
        }
        return $jsst_rows[$jsst_ticketid];
    }

    /** Statuses that mean the ticket is finished. */
    private static function isClosed($jsst_status) {
        return ((int) $jsst_status === 5 || (int) $jsst_status === 6);
    }

    private static function maxTicketId() {
        return (int) jssupportticket::$_db->get_var(
            'SELECT MAX(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets`'
        );
    }

    private static function lastReplyId($jsst_ticketid) {
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT MAX(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_replies` WHERE ticketid = %d',
            (int) $jsst_ticketid
        ));
    }

    private static function lastNoteId($jsst_ticketid) {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_notes';
        $jsst_exists = jssupportticket::$_db->get_var(
            jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)
        );
        if (!$jsst_exists) {
            return 0;
        }
        return (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT MAX(id) FROM `' . $jsst_table . '` WHERE ticketid = %d',
            (int) $jsst_ticketid
        ));
    }

    /** What a command returns when it did something. */
    private static function done($jsst_ticketid, $jsst_what, $jsst_event, $jsst_extra = array()) {
        return array_merge(array(
            'ok'        => true,
            'changed'   => true,
            'ticket_id' => (int) $jsst_ticketid,
            'action'    => $jsst_what,
            'event'     => is_array($jsst_event) ? $jsst_event['event'] : '',
            'event_id'  => is_array($jsst_event) ? $jsst_event['id'] : '',
        ), $jsst_extra);
    }

    /**
     * What a command returns when the ticket was already in the state asked
     * for. Not an error — pressing Close on a closed ticket is a no-op, and
     * treating it as a failure makes every bulk action report false alarms —
     * but no event is emitted, because nothing happened.
     */
    private static function unchanged($jsst_ticketid, $jsst_what) {
        return array(
            'ok'        => true,
            'changed'   => false,
            'ticket_id' => (int) $jsst_ticketid,
            'action'    => $jsst_what,
            'event'     => '',
            'event_id'  => '',
        );
    }
}
