<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Ticket actions — part of the free core. (Roadmap 4.0-CORE-05)
 *
 * Loaded only when the stand-alone Ticket Actions add-on is inactive:
 * JSSTincluder::getPluginPath() resolves the 'actions' module to the add-on
 * directory while that add-on is active, so the add-on stays authoritative and
 * nothing is registered or rendered twice. Lock state lives in the `lock` column
 * on js_ticket_tickets either way, so nothing changes for an existing site.
 * (Roadmap 4.0-CORE-19)
 *
 * Lock, unlock, in-progress, priority change, department transfer and the
 * print-friendly view are all ordinary agent controls. What 4.0 adds over the
 * add-on:
 *   - a reason can be given for an action, and it is recorded on the timeline
 *   - bulk actions, so one reason and one audit entry cover a whole selection
 */
class JSSTactionsModel {

    /** @see JSSTticketaction::bulkActions() */
    public static function bulkActions() {
        return JSSTticketaction::bulkActions();
    }

    /** @see JSSTticketaction::canRun() */
    public static function canRun($jsst_permission) {
        return JSSTticketaction::canRun($jsst_permission);
    }

    /** @see JSSTticketaction::reason() */
    public static function reason($jsst_data = array()) {
        return JSSTticketaction::reason($jsst_data);
    }

    /** @see JSSTticketaction::audit() */
    public static function audit($jsst_ticketid, $jsst_eventtype, $jsst_message, $jsst_messagetype, $jsst_reason = '') {
        JSSTticketaction::audit($jsst_ticketid, $jsst_eventtype, $jsst_message, $jsst_messagetype, $jsst_reason);
    }

    /** @see JSSTticketaction::parseIds() */
    public static function parseIds($jsst_raw) {
        return JSSTticketaction::parseIds($jsst_raw);
    }

    /**
     * lock = 0 means unlocked, lock = 1 means locked.
     */
    function lockTicket($jsst_id, $jsst_data = array()) {
        return $this->setLock($jsst_id, 1, $jsst_data);
    }

    function unLockTicket($jsst_id, $jsst_data = array()) {
        return $this->setLock($jsst_id, 0, $jsst_data);
    }

    /**
     * One implementation for both directions: the only differences are the
     * column value, the message and which e-mail template fires.
     */
    private function setLock($jsst_id, $jsst_lock, $jsst_data = array()) {
        if (!is_numeric($jsst_id))
            return false;
        if (!self::canRun('Lock Ticket')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return false;
        }
        $jsst_lock = ($jsst_lock == 1) ? 1 : 0;
        $jsst_sendEmail = true;
        $jsst_reason = self::reason($jsst_data);

        $jsst_row = JSSTincluder::getJSTable('tickets');
        if ($jsst_row->update(array('id' => $jsst_id, 'lock' => $jsst_lock))) {
            JSSTmessage::setMessage(
                $jsst_lock ? esc_html(__('The ticket has been locked', 'js-support-ticket'))
                           : esc_html(__('The ticket has been unlocked', 'js-support-ticket')),
                'updated'
            );
            $jsst_messagetype = esc_html(__('Successfully', 'js-support-ticket'));
            $jsst_ok = true;
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError(); // if there is an error add it to system errorrs
            JSSTmessage::setMessage(
                $jsst_lock ? esc_html(__('The ticket has not been locked', 'js-support-ticket'))
                           : esc_html(__('The ticket has not been unlocked', 'js-support-ticket')),
                'error'
            );
            $jsst_messagetype = esc_html(__('Error', 'js-support-ticket'));
            $jsst_sendEmail = false;
            $jsst_ok = false;
        }

        /* for activity log */
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser(); // to get current user name
        $jsst_currentUserName = isset($jsst_current_user->display_name) ? $jsst_current_user->display_name : '';
        $jsst_eventtype = $jsst_lock ? esc_html(__('Lock Ticket', 'js-support-ticket')) : esc_html(__('Unlock Ticket', 'js-support-ticket'));
        $jsst_message = ($jsst_lock ? esc_html(__('The ticket is locked by', 'js-support-ticket')) : esc_html(__('The ticket is unlocked by', 'js-support-ticket')))
            . " ( " . esc_html($jsst_currentUserName) . " ) ";
        self::audit($jsst_id, $jsst_eventtype, $jsst_message, $jsst_messagetype, $jsst_reason);

        // Send Emails
        if ($jsst_sendEmail == true) {
            JSSTincluder::getJSModel('email')->sendMail(1, $jsst_lock ? 6 : 7, $jsst_id); // Mailfor, Lock/Unlock Ticket, Ticketid
            $jsst_ticketobject = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
                "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id = %d",
                $jsst_id
            ));
            do_action('jsst-ticketclose', $jsst_ticketobject);
        }
        return $jsst_ok;
    }

    /* ------------------------------------------------------------------ *
     * Bulk actions (Roadmap 4.0-CORE-05)
     * ------------------------------------------------------------------ */

    /**
     * Apply one action to a selection of tickets.
     *
     * Every ticket is handled through the same single-ticket path that the ticket
     * detail screen uses, so a bulk action can never do something a single action
     * would refuse. One reason covers the selection and is recorded against each
     * ticket.
     *
     * Returns array('done' => int, 'skipped' => int).
     */
    function runBulkAction($jsst_data) {
        $jsst_result = array('done' => 0, 'skipped' => 0);
        $jsst_action = isset($jsst_data['bulkaction']) ? sanitize_key($jsst_data['bulkaction']) : '';
        $jsst_actions = self::bulkActions();
        if ($jsst_action === '' || !isset($jsst_actions[$jsst_action])) {
            JSSTmessage::setMessage(esc_html(__('Choose an action to apply.', 'js-support-ticket')), 'error');
            return $jsst_result;
        }
        if (!self::canRun($jsst_actions[$jsst_action]['permission'])) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed', 'js-support-ticket')), 'error', 'agent-permissions');
            return $jsst_result;
        }

        $jsst_ids = self::parseIds(isset($jsst_data['bulkticketids']) ? $jsst_data['bulkticketids'] : '');
        if (empty($jsst_ids)) {
            JSSTmessage::setMessage(esc_html(__('Select at least one ticket.', 'js-support-ticket')), 'error');
            return $jsst_result;
        }

        // Actions that need a value: refuse before touching anything rather than
        // half-applying the selection.
        $jsst_priority = isset($jsst_data['bulkpriorityid']) ? $jsst_data['bulkpriorityid'] : '';
        $jsst_department = isset($jsst_data['bulkdepartmentid']) ? $jsst_data['bulkdepartmentid'] : '';
        if ($jsst_action === 'priority' && !is_numeric($jsst_priority)) {
            JSSTmessage::setMessage(esc_html(__('Choose the priority to set.', 'js-support-ticket')), 'error');
            return $jsst_result;
        }
        if ($jsst_action === 'department' && !is_numeric($jsst_department)) {
            JSSTmessage::setMessage(esc_html(__('Choose the department to transfer to.', 'js-support-ticket')), 'error');
            return $jsst_result;
        }

        $jsst_reason = self::reason($jsst_data);
        $jsst_ticketmodel = JSSTincluder::getJSModel('ticket');

        // Each ticket runs the ordinary single-ticket path, which sets its own
        // message; one summary is printed at the end instead.
        JSSTmessage::mute();
        foreach ($jsst_ids AS $jsst_id) {
            $jsst_done = false;
            switch ($jsst_action) {
                case 'lock':
                    $jsst_done = $this->lockTicket($jsst_id, array('actionreason' => $jsst_reason));
                    break;
                case 'unlock':
                    $jsst_done = $this->unLockTicket($jsst_id, array('actionreason' => $jsst_reason));
                    break;
                case 'inprogress':
                    $jsst_ticketmodel->markTicketInProgress(array('ticketid' => $jsst_id, 'actionreason' => $jsst_reason));
                    $jsst_done = true;
                    break;
                case 'close':
                    $jsst_ticketmodel->closeTicket($jsst_id);
                    self::audit(
                        $jsst_id,
                        esc_html(__('Close Ticket', 'js-support-ticket')),
                        esc_html(__('Closed as part of a bulk action', 'js-support-ticket')),
                        esc_html(__('Successfully', 'js-support-ticket')),
                        $jsst_reason
                    );
                    $jsst_done = true;
                    break;
                case 'reopen':
                    $jsst_ticketmodel->reopenTicket(array('ticketid' => $jsst_id));
                    self::audit(
                        $jsst_id,
                        esc_html(__('Reopen Ticket', 'js-support-ticket')),
                        esc_html(__('Reopened as part of a bulk action', 'js-support-ticket')),
                        esc_html(__('Successfully', 'js-support-ticket')),
                        $jsst_reason
                    );
                    $jsst_done = true;
                    break;
                case 'priority':
                    $jsst_ticketmodel->changeTicketPriority($jsst_id, $jsst_priority);
                    self::audit(
                        $jsst_id,
                        esc_html(__('Ticket priority change', 'js-support-ticket')),
                        esc_html(__('Priority changed as part of a bulk action', 'js-support-ticket')),
                        esc_html(__('Successfully', 'js-support-ticket')),
                        $jsst_reason
                    );
                    $jsst_done = true;
                    break;
                case 'department':
                    $jsst_ticketmodel->tickDepartmentTransfer(array(
                        'ticketid' => $jsst_id,
                        'departmentid' => $jsst_department,
                        'actionreason' => $jsst_reason,
                    ));
                    $jsst_done = true;
                    break;
            }
            if ($jsst_done) {
                $jsst_result['done']++;
            } else {
                $jsst_result['skipped']++;
            }
        }

        JSSTmessage::unmute();
        JSSTmessage::setMessage(
            sprintf(
                /* translators: 1: number of tickets, 2: name of the action */
                esc_html(_n('%1$d ticket updated: %2$s.', '%1$d tickets updated: %2$s.', $jsst_result['done'], 'js-support-ticket')),
                $jsst_result['done'],
                $jsst_actions[$jsst_action]['label']
            ),
            'updated'
        );
        if ($jsst_result['skipped'] > 0) {
            JSSTmessage::setMessage(
                sprintf(
                    /* translators: %d: number of tickets the bulk action could not update. */
                    esc_html(_n('%d ticket could not be updated.', '%d tickets could not be updated.', $jsst_result['skipped'], 'js-support-ticket')),
                    $jsst_result['skipped']
                ),
                'error'
            );
        }
        return $jsst_result;
    }

}
