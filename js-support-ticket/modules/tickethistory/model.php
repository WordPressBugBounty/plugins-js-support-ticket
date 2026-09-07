<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Ticket history / activity timeline — part of the free core. (Roadmap 4.0-CORE-01)
 *
 * This file is only loaded when the stand-alone Ticket History add-on is NOT
 * active: JSSTincluder::getPluginPath() resolves the 'tickethistory' module to
 * the add-on directory whenever that add-on is active. Both implementations use
 * the same js_ticket_activity_log table, so a site that deactivates the add-on
 * keeps every event it has already recorded. (Roadmap 4.0-CORE-19)
 *
 * Immutable audit export and long-term retention stay in Pro.
 */
class JSSTtickethistoryModel {

    /** Bumped when the table layout below changes. */
    const SCHEMA_VERSION = '400-CORE01';

    /**
     * Create or upgrade the activity log table.
     *
     * Self-healing on purpose: the table may have been created years ago by the
     * add-on, by an import, or not at all on a fresh free install. Running from
     * the read and write paths means core never depends on a particular upgrade
     * route having been taken — and the check is against the table's own columns,
     * not only the stored schema version, so a table that drifts back to an older
     * layout after the version was recorded is still repaired.
     */
    public static function ensureSchema() {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_activity_log';
        // Columns 4.0 adds to whatever layout is already there. Each one is
        // checked first so an existing add-on table is upgraded in place and
        // never reports an error.
        $jsst_wanted = array(
            'source'    => "ADD `source` varchar(32) DEFAULT NULL",
            'fieldname' => "ADD `fieldname` varchar(191) DEFAULT NULL",
            'oldvalue'  => "ADD `oldvalue` text",
            'newvalue'  => "ADD `newvalue` text",
        );

        // The stored version is only a hint: this table is the one that proved
        // why. It was dropped and recreated in the pre-4.0 layout with the
        // option still reading 400, and every timeline read then died on
        // Unknown column 'al.source' for good. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_tickethistory_schema', self::SCHEMA_VERSION,
                array('js_ticket_activity_log' => array_keys($jsst_wanted)))) {
            return;
        }

        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        // Same layout as includes/updates/sql/400.sql, so a table created here
        // and a table created by the upgrade file are the same table.
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_table . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    uid int(11) DEFAULT NULL,
                    referenceid int(11) DEFAULT NULL,
                    level int(2) DEFAULT NULL,
                    eventfor int(2) DEFAULT NULL,
                    event varchar(255) DEFAULT NULL,
                    eventtype varchar(255) DEFAULT NULL,
                    message text,
                    messagetype varchar(255) DEFAULT NULL,
                    datetime timestamp NULL DEFAULT NULL,
                    source varchar(32) DEFAULT NULL,
                    fieldname varchar(191) DEFAULT NULL,
                    oldvalue text,
                    newvalue text,
                    PRIMARY KEY (id),
                    KEY jsst_reference (referenceid, eventfor),
                    KEY jsst_datetime (datetime)
                ) " . $jsst_charset);

        // The table may have just been created with the full layout, or may be
        // an older one that still needs each column added.
        $jsst_columns = jssupportticket::$_db->get_col('SHOW COLUMNS FROM `' . $jsst_table . '`', 0);
        if (!is_array($jsst_columns)) {
            $jsst_columns = array();
        }
        foreach ($jsst_wanted as $jsst_column => $jsst_clause) {
            if (!in_array($jsst_column, $jsst_columns, true)) {
                jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ' . $jsst_clause);
            }
        }

        // Timeline reads always filter on referenceid + eventfor and order by
        // datetime. (Roadmap 4.0-PERF-01)
        $jsst_indexes = jssupportticket::$_db->get_col('SHOW INDEX FROM `' . $jsst_table . '`', 2);
        if (!is_array($jsst_indexes)) {
            $jsst_indexes = array();
        }
        if (!in_array('jsst_reference', $jsst_indexes, true)) {
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD INDEX `jsst_reference` (`referenceid`, `eventfor`)');
        }
        if (!in_array('jsst_datetime', $jsst_indexes, true)) {
            jssupportticket::$_db->query('ALTER TABLE `' . $jsst_table . '` ADD INDEX `jsst_datetime` (`datetime`)');
        }

        update_option('jsst_tickethistory_schema', self::SCHEMA_VERSION, false);
    }

    /**
     * Where the event came from, so the timeline can say "by e-mail" instead of
     * attributing a piped reply to whoever happened to run the cron.
     */
    public static function detectSource() {
        $jsst_source = 'portal';
        if (defined('WP_CLI') && WP_CLI) {
            $jsst_source = 'cli';
        } elseif (function_exists('wp_doing_cron') && wp_doing_cron()) {
            $jsst_source = 'cron';
        } elseif (defined('REST_REQUEST') && REST_REQUEST) {
            $jsst_source = 'api';
        } elseif (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
            $jsst_source = 'ajax';
        } elseif (is_admin()) {
            $jsst_source = 'admin';
        }
        return apply_filters('jsst_activity_log_source', $jsst_source);
    }

    /**
     * Record one event.
     *
     * Signature-compatible with the add-on so every existing call site keeps
     * working unchanged.
     */
    function addActivityLog($jsst_referenceid, $jsst_eventfor, $jsst_eventtype, $jsst_message, $jsst_messagetype) {
        return $this->addEvent(array(
            'referenceid'  => $jsst_referenceid,
            'eventfor'     => $jsst_eventfor,
            'eventtype'    => $jsst_eventtype,
            'message'      => $jsst_message,
            'messagetype'  => $jsst_messagetype,
        ));
    }

    /**
     * Record one event, with the 4.0 fields.
     *
     * Accepts: referenceid, eventfor, eventtype, message, messagetype, source,
     * fieldname, oldvalue, newvalue, uid.
     */
    function addEvent($jsst_args) {
        self::ensureSchema();
        $jsst_referenceid = isset($jsst_args['referenceid']) ? $jsst_args['referenceid'] : 0;
        if (!is_numeric($jsst_referenceid)) {
            return false;
        }
        $jsst_eventfor = isset($jsst_args['eventfor']) ? (int) $jsst_args['eventfor'] : 1;
        $jsst_event = '';
        switch ($jsst_eventfor) {
            case 1:
                $jsst_event = __('Ticket', 'js-support-ticket');
                break;
        }
        $jsst_uid = isset($jsst_args['uid'])
            ? (int) $jsst_args['uid']
            : (int) JSSTincluder::getObjectClass('user')->uid();
        // level 3 marks an action taken from the backend, 1 everything else —
        // kept as the add-on wrote it so old and new rows read the same.
        $jsst_level = is_admin() ? 3 : 1;

        $jsst_row = array(
            'uid'         => $jsst_uid,
            'referenceid' => $jsst_referenceid,
            'level'       => $jsst_level,
            'eventfor'    => $jsst_eventfor,
            'event'       => $jsst_event,
            'eventtype'   => isset($jsst_args['eventtype']) ? $jsst_args['eventtype'] : '',
            'message'     => isset($jsst_args['message']) ? $jsst_args['message'] : '',
            'messagetype' => isset($jsst_args['messagetype']) ? $jsst_args['messagetype'] : '',
            'datetime'    => date_i18n('Y-m-d H:i:s'),
            'source'      => isset($jsst_args['source']) ? $jsst_args['source'] : self::detectSource(),
            'fieldname'   => isset($jsst_args['fieldname']) ? $jsst_args['fieldname'] : null,
            'oldvalue'    => isset($jsst_args['oldvalue']) ? $jsst_args['oldvalue'] : null,
            'newvalue'    => isset($jsst_args['newvalue']) ? $jsst_args['newvalue'] : null,
        );
        jssupportticket::$_db->insert(jssupportticket::$_db->prefix . 'js_ticket_activity_log', $jsst_row);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            return false;
        }
        do_action('jsst_activity_logged', $jsst_row);
        return true;
    }

    /**
     * Record one event per changed field, so the timeline can show what the
     * value was and what it became.
     *
     * $jsst_before and $jsst_after are field => value maps; $jsst_labels maps a
     * field name to the label an agent recognises.
     */
    function logFieldChanges($jsst_referenceid, $jsst_before, $jsst_after, $jsst_labels = array(), $jsst_eventfor = 1) {
        if (!is_array($jsst_before) || !is_array($jsst_after)) {
            return false;
        }
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser();
        $jsst_actor = isset($jsst_current_user->display_name) ? $jsst_current_user->display_name : '';
        $jsst_logged = 0;
        foreach ($jsst_after as $jsst_field => $jsst_new) {
            $jsst_old = isset($jsst_before[$jsst_field]) ? $jsst_before[$jsst_field] : '';
            if ((string) $jsst_old === (string) $jsst_new) {
                continue;
            }
            $jsst_label = isset($jsst_labels[$jsst_field]) ? $jsst_labels[$jsst_field] : $jsst_field;
            $jsst_message = sprintf(
                /* translators: 1: field label, 2: previous value, 3: new value, 4: user name */
                esc_html(__('%1$s changed from "%2$s" to "%3$s" by ( %4$s )', 'js-support-ticket')),
                esc_html($jsst_label),
                esc_html((string) $jsst_old),
                esc_html((string) $jsst_new),
                esc_html($jsst_actor)
            );
            $jsst_done = $this->addEvent(array(
                'referenceid' => $jsst_referenceid,
                'eventfor'    => $jsst_eventfor,
                'eventtype'   => esc_html(__('Field change', 'js-support-ticket')),
                'message'     => $jsst_message,
                'messagetype' => esc_html(__('Successfully', 'js-support-ticket')),
                'fieldname'   => $jsst_label,
                'oldvalue'    => (string) $jsst_old,
                'newvalue'    => (string) $jsst_new,
            ));
            if ($jsst_done) {
                $jsst_logged++;
            }
        }
        return $jsst_logged;
    }

    /**
     * The event stream for one ticket, newest first, with actor and source.
     *
     * $jsst_filters accepts 'eventtype', 'source' and 'uid'.
     */
    function getTicketTimeline($jsst_ticketid, $jsst_filters = array(), $jsst_eventfor = 1) {
        self::ensureSchema();
        if (!is_numeric($jsst_ticketid)) {
            return array();
        }
        $jsst_where = array('al.referenceid = %d', 'al.eventfor = %d');
        $jsst_params = array($jsst_ticketid, $jsst_eventfor);
        if (!empty($jsst_filters['eventtype'])) {
            $jsst_where[] = 'al.eventtype = %s';
            $jsst_params[] = $jsst_filters['eventtype'];
        }
        if (!empty($jsst_filters['source'])) {
            $jsst_where[] = 'al.source = %s';
            $jsst_params[] = $jsst_filters['source'];
        }
        if (!empty($jsst_filters['uid'])) {
            $jsst_where[] = 'al.uid = %d';
            $jsst_params[] = (int) $jsst_filters['uid'];
        }
        $jsst_query = "SELECT al.id, al.message, al.datetime, al.uid, al.eventtype, al.messagetype,
                        al.source, al.fieldname, al.oldvalue, al.newvalue, user.display_name AS actorname
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_activity_log` AS al
                    LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_users` AS user ON al.uid = user.id
                    WHERE " . implode(' AND ', $jsst_where) . "
                    ORDER BY al.datetime DESC, al.id DESC";
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare($jsst_query, $jsst_params));
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            return array();
        }
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * Distinct event types and sources present on one ticket, for the timeline
     * filter controls.
     */
    function getTimelineFilterValues($jsst_ticketid, $jsst_eventfor = 1) {
        self::ensureSchema();
        $jsst_out = array('eventtype' => array(), 'source' => array());
        if (!is_numeric($jsst_ticketid)) {
            return $jsst_out;
        }
        $jsst_query = jssupportticket::$_db->prepare(
            "SELECT DISTINCT al.eventtype, al.source
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_activity_log` AS al
                WHERE al.referenceid = %d AND al.eventfor = %d",
            $jsst_ticketid,
            $jsst_eventfor
        );
        $jsst_rows = jssupportticket::$_db->get_results($jsst_query);
        if (!is_array($jsst_rows)) {
            return $jsst_out;
        }
        foreach ($jsst_rows as $jsst_row) {
            if ($jsst_row->eventtype != '' && !in_array($jsst_row->eventtype, $jsst_out['eventtype'], true)) {
                $jsst_out['eventtype'][] = $jsst_row->eventtype;
            }
            if ($jsst_row->source != '' && !in_array($jsst_row->source, $jsst_out['source'], true)) {
                $jsst_out['source'][] = $jsst_row->source;
            }
        }
        sort($jsst_out['eventtype']);
        sort($jsst_out['source']);
        return $jsst_out;
    }

    /**
     * Paginated log listing, kept for parity with the add-on's model.
     */
    function getactivitylogs() {
        self::ensureSchema();
        $jsst_event = JSSTrequest::getVar('event');
        $jsst_inquery = '';
        if ($jsst_event != null) {
            $jsst_inquery .= jssupportticket::$_db->prepare(" WHERE activitylog.event LIKE %s", '%' . jssupportticket::$_db->esc_like($jsst_event) . '%');
        }

        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_activity_log` AS activitylog";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total);

        $jsst_query = "SELECT activitylog.* FROM `" . jssupportticket::$_db->prefix . "js_ticket_activity_log` AS activitylog ";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY activitylog.id DESC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    /**
     * Remove the log rows belonging to deleted tickets.
     */
    function deleteActivityLog($jsst_referenceid, $jsst_eventfor = 1) {
        if (!is_numeric($jsst_referenceid)) {
            return false;
        }
        jssupportticket::$_db->delete(
            jssupportticket::$_db->prefix . 'js_ticket_activity_log',
            array('referenceid' => $jsst_referenceid, 'eventfor' => $jsst_eventfor),
            array('%d', '%d')
        );
        return jssupportticket::$_db->last_error == null;
    }

}
