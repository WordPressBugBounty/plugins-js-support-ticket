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
if (class_exists('JSSTticketaction')) {
    return;
}


/**
 * Shared helpers for ticket actions. (Roadmap 4.0-CORE-05)
 *
 * These live here rather than on JSSTactionsModel on purpose: on a site that
 * still has the stand-alone Ticket Actions add-on active, JSSTactionsModel is
 * the add-on's class and knows nothing about reasons or bulk actions. Core code
 * calls this class, which always exists on both paths, so a legacy add-on can
 * never turn a core call site into a fatal error. (Roadmap 4.0-CORE-19)
 */
class JSSTticketaction {

    /**
     * The bulk actions offered on the ticket list, and the permission each needs.
     */
    public static function bulkActions() {
        return apply_filters('jsst_bulk_actions', array(
            'lock'       => array('label' => esc_html(__('Lock', 'js-support-ticket')),                'permission' => 'Lock Ticket'),
            'unlock'     => array('label' => esc_html(__('Unlock', 'js-support-ticket')),              'permission' => 'Lock Ticket'),
            'inprogress' => array('label' => esc_html(__('Mark in progress', 'js-support-ticket')),     'permission' => 'Mark In Progress'),
            'close'      => array('label' => esc_html(__('Close', 'js-support-ticket')),               'permission' => 'Close Ticket'),
            'reopen'     => array('label' => esc_html(__('Reopen', 'js-support-ticket')),              'permission' => 'Reopen Ticket'),
            'priority'   => array('label' => esc_html(__('Change priority', 'js-support-ticket')),      'permission' => 'Change Ticket Priority'),
            'department' => array('label' => esc_html(__('Transfer department', 'js-support-ticket')),   'permission' => 'Transfer Department'),
        ));
    }

    /**
     * May the current user run this action?
     *
     * An administrator always may. An agent needs the matching permission when
     * the Agents add-on is managing permissions. Anyone else may not.
     */
    public static function canRun($jsst_permission) {
        if (current_user_can('manage_options')) {
            return true;
        }
        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
            if ($jsst_permission === '') {
                return true;
            }
            return JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_permission) == true;
        }
        /* Core capability model. Every bulk action this plugin ships moves a
           ticket through its life, which is exactly what CAP_STATE covers — so
           an agent who may close one ticket may close fifty. Without this the
           bulk bar rendered empty for agents and the feature was administrator
           only. An action a third party adds through the jsst_bulk_actions
           filter is deliberately not assumed to be covered: it has to name a
           permission this list knows. (Roadmap 4.0-SEC-04) */
        if (in_array($jsst_permission, self::stateBulkPermissions(), true)) {
            return JSSTroles::canChangeTicketState();
        }
        return false;
    }

    /**
     * The permission names, used by the actions above, that the core state
     * capability covers.
     */
    private static function stateBulkPermissions() {
        return array(
            'Lock Ticket',
            'Mark In Progress',
            'Close Ticket',
            'Reopen Ticket',
            'Change Ticket Priority',
            'Transfer Department',
        );
    }

    /**
     * The reason an agent typed, cleaned up for storage.
     *
     * Optional everywhere: an action is never blocked for want of a reason.
     */
    public static function reason($jsst_data = array()) {
        if (isset($jsst_data['actionreason'])) {
            $jsst_reason = $jsst_data['actionreason'];
        } else {
            // getVar()'s third argument is the default value, not the method —
            // passing 'post' there made an action taken without a reason record
            // the literal word "post" as its reason. (Roadmap 4.0-CORE-05)
            $jsst_reason = JSSTrequest::getVar('actionreason', 'post', '');
        }
        $jsst_reason = trim(wp_strip_all_tags((string) $jsst_reason));
        if (jssupportticketphplib::JSST_strlen($jsst_reason) > 500) {
            $jsst_reason = jssupportticketphplib::JSST_substr($jsst_reason, 0, 500);
        }
        return $jsst_reason;
    }

    /**
     * Record an action on the activity timeline, with the reason when one was
     * given. (Roadmap 4.0-CORE-01, 4.0-CORE-05)
     */
    public static function audit($jsst_ticketid, $jsst_eventtype, $jsst_message, $jsst_messagetype, $jsst_reason = '') {
        if (!JSSTmergedaddon::featureEnabled('tickethistory')) {
            return;
        }
        if ($jsst_reason !== '') {
            /* translators: %s: the reason an agent gave for an action */
            $jsst_message .= ' — ' . sprintf(esc_html(__('Reason: %s', 'js-support-ticket')), esc_html($jsst_reason));
        }
        JSSTincluder::getJSModel('tickethistory')->addActivityLog($jsst_ticketid, 1, $jsst_eventtype, $jsst_message, $jsst_messagetype);
    }

    /**
     * A list of ticket ids — array or comma-separated string — reduced to
     * distinct positive numbers that actually exist.
     */
    public static function parseIds($jsst_raw) {
        $jsst_ids = array();
        $jsst_pieces = is_array($jsst_raw) ? $jsst_raw : explode(',', (string) $jsst_raw);
        foreach ($jsst_pieces as $jsst_piece) {
            $jsst_piece = trim((string) $jsst_piece);
            if ($jsst_piece === '' || !is_numeric($jsst_piece)) {
                continue;
            }
            $jsst_piece = (int) $jsst_piece;
            if ($jsst_piece > 0) {
                $jsst_ids[$jsst_piece] = $jsst_piece;
            }
        }
        if (empty($jsst_ids)) {
            return array();
        }
        $jsst_placeholders = implode(',', array_fill(0, count($jsst_ids), '%d'));
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tickets` WHERE id IN (" . $jsst_placeholders . ")",
            array_values($jsst_ids)
        );
        $jsst_found = jssupportticket::$_db->get_col($jsst_query);
        return is_array($jsst_found) ? array_map('intval', $jsst_found) : array();
    }

}
