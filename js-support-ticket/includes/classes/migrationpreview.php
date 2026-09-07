<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap with include_once, which deduplicates by resolved
 * path. Any route reaching this file by a second spelling would redeclare the
 * class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTmigrationpreview')) {
    return;
}

/**
 * What an import would do, counted before it does it. (Roadmap 4.0-DATA-01)
 *
 * The count the old screen showed came from the importer, which could only say
 * how many records it had found once it was already running. This counts the
 * source directly and answers the question somebody actually has before they
 * start: how much is there, where does each kind of thing land, and — the part
 * that matters — what will be dropped on the floor because the add-on that would
 * hold it is not active here.
 *
 * That last list is the reason this exists. Everything else can be discovered by
 * running the import and looking; a record with nowhere to land is discovered
 * months later, after the old help desk has been deleted, by a customer asking
 * where their attachment went.
 *
 * Every count is guarded by a table-exists check rather than assumed. These
 * importers were written against particular versions of three other plugins, and
 * a source one version newer or older is missing tables this would otherwise
 * fatal on — a preview that takes the site down is worse than no preview. A
 * table that is not there is reported as "not present" rather than as zero,
 * because those two mean very different things to somebody deciding whether the
 * import is safe to run.
 */
class JSSTmigrationpreview {

    /**
     * The report for one source.
     *
     * @return array|WP_Error label, version, total, entities[], unsupported[], warnings[]
     */
    public static function report($jsst_source) {
        $jsst_sources = JSSTmigration::sources();
        if (!isset($jsst_sources[$jsst_source])) {
            return new WP_Error('jsst_unknown_source', esc_html(__('That source is not one this can read.', 'js-support-ticket')));
        }
        $jsst_meta = $jsst_sources[$jsst_source];
        if (empty($jsst_meta['present'])) {
            return new WP_Error('jsst_source_absent', esc_html(__('Nothing from that help desk was found on this site.', 'js-support-ticket')));
        }

        switch ($jsst_source) {
            case 'supportcandy':
                $jsst_report = self::supportCandy();
                break;
            case 'fluentsupport':
                $jsst_report = self::fluentSupport();
                break;
            case 'awesomesupport':
                $jsst_report = self::awesomeSupport();
                break;
            default:
                return new WP_Error('jsst_unknown_source', esc_html(__('That source is not one this can read.', 'js-support-ticket')));
        }

        $jsst_report['label'] = $jsst_meta['label'];
        $jsst_report['version'] = $jsst_meta['version'];
        $jsst_report['total'] = 0;
        foreach ($jsst_report['entities'] as $jsst_entity) {
            if (!empty($jsst_entity['countable'])) {
                $jsst_report['total'] += (int) $jsst_entity['count'];
            }
        }

        /* Said on every source, because it is true of every source and it is the
           thing people most often assume the opposite of. */
        if (empty($jsst_meta['active'])) {
            $jsst_report['warnings'][] = esc_html(__('The source plugin is switched off, but its data is still here and will be read. It does not need to be reactivated.', 'js-support-ticket'));
        }
        $jsst_report['warnings'][] = esc_html(__('Take a database backup first. The import can be rolled back exactly, but a backup covers everything a rollback is not designed for.', 'js-support-ticket'));

        return apply_filters('jsst_migration_preview', $jsst_report, $jsst_source);
    }

    /* ------------------------------------------------------------------ *
     * The sources
     * ------------------------------------------------------------------ */

    private static function supportCandy() {
        $jsst_p = jssupportticket::$_db->prefix;
        $jsst_entities = array(
            self::entity(__('Tickets', 'js-support-ticket'), $jsst_p . 'psmsc_tickets', __('Tickets', 'js-support-ticket')),
            self::entity(__('Replies', 'js-support-ticket'), $jsst_p . 'psmsc_threads', __('Ticket replies', 'js-support-ticket')),
            self::entity(__('Customers', 'js-support-ticket'), $jsst_p . 'psmsc_customers', __('Customers', 'js-support-ticket')),
            self::entity(__('Agents', 'js-support-ticket'), $jsst_p . 'psmsc_agents', __('Agents', 'js-support-ticket')),
            self::entity(__('Categories', 'js-support-ticket'), $jsst_p . 'psmsc_categories', __('Departments', 'js-support-ticket')),
            self::entity(__('Priorities', 'js-support-ticket'), $jsst_p . 'psmsc_priorities', __('Priorities', 'js-support-ticket')),
            self::entity(__('Statuses', 'js-support-ticket'), $jsst_p . 'psmsc_statuses', __('Ticket statuses', 'js-support-ticket')),
            self::entity(__('Custom fields', 'js-support-ticket'), $jsst_p . 'psmsc_custom_fields', __('Ticket fields', 'js-support-ticket')),
            self::entity(__('Attachments', 'js-support-ticket'), $jsst_p . 'psmsc_attachments', __('Attachments', 'js-support-ticket')),
        );
        return self::assemble($jsst_entities, array(
            'note'         => array(__('Private notes', 'js-support-ticket'), $jsst_p . 'psmsc_threads', "type = 3"),
            'timetracking' => array(__('Time logs', 'js-support-ticket'), $jsst_p . 'psmsc_time_logs', ''),
        ));
    }

    private static function fluentSupport() {
        $jsst_p = jssupportticket::$_db->prefix;
        $jsst_entities = array(
            self::entity(__('Tickets', 'js-support-ticket'), $jsst_p . 'fs_tickets', __('Tickets', 'js-support-ticket')),
            self::entity(__('Conversations', 'js-support-ticket'), $jsst_p . 'fs_conversations', __('Ticket replies', 'js-support-ticket')),
            self::entity(__('Customers', 'js-support-ticket'), $jsst_p . 'fs_customers', __('Customers', 'js-support-ticket')),
            self::entity(__('Agents', 'js-support-ticket'), $jsst_p . 'fs_persons', __('Agents', 'js-support-ticket')),
            self::entity(__('Mailboxes', 'js-support-ticket'), $jsst_p . 'fs_mailboxes', __('Departments', 'js-support-ticket')),
            self::entity(__('Products', 'js-support-ticket'), $jsst_p . 'fs_products', __('Products', 'js-support-ticket')),
            self::entity(__('Attachments', 'js-support-ticket'), $jsst_p . 'fs_attachments', __('Attachments', 'js-support-ticket')),
        );
        return self::assemble($jsst_entities, array(
            'timetracking' => array(__('Activity records', 'js-support-ticket'), $jsst_p . 'fs_activities', ''),
        ));
    }

    /**
     * Awesome Support keeps everything as posts, which is why its counts are
     * shaped differently from the other two and why it has no table to probe.
     */
    private static function awesomeSupport() {
        $jsst_p = jssupportticket::$_db->prefix;
        $jsst_entities = array(
            self::postEntity(__('Tickets', 'js-support-ticket'), 'ticket', __('Tickets', 'js-support-ticket')),
            self::postEntity(__('Replies', 'js-support-ticket'), 'ticket_reply', __('Ticket replies', 'js-support-ticket')),
            self::termEntity(__('Departments', 'js-support-ticket'), 'department', __('Departments', 'js-support-ticket')),
            self::termEntity(__('Priorities', 'js-support-ticket'), 'ticket_priority', __('Priorities', 'js-support-ticket')),
            self::termEntity(__('Statuses', 'js-support-ticket'), 'ticket_status', __('Ticket statuses', 'js-support-ticket')),
        );
        $jsst_report = self::assemble($jsst_entities, array(
            'note' => array(__('Private notes', 'js-support-ticket'), $jsst_p . 'posts', "post_type = 'ticket_note'"),
        ));
        $jsst_report['warnings'][] = esc_html(__('Awesome Support stores tickets as WordPress posts. Its posts are read but not removed, so the two sets exist side by side until you delete the old plugin\'s data yourself.', 'js-support-ticket'));
        return $jsst_report;
    }

    /* ------------------------------------------------------------------ *
     * Counting
     * ------------------------------------------------------------------ */

    /** One row of the report, counted from a table that may not be there. */
    private static function entity($jsst_label, $jsst_table, $jsst_destination, $jsst_where = '') {
        $jsst_exists = JSSTmigration::tableExists($jsst_table);
        return array(
            'label'       => esc_html($jsst_label),
            'destination' => esc_html($jsst_destination),
            'countable'   => $jsst_exists,
            'count'       => $jsst_exists ? self::countRows($jsst_table, $jsst_where) : 0,
        );
    }

    private static function postEntity($jsst_label, $jsst_posttype, $jsst_destination) {
        $jsst_count = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "posts` WHERE post_type = %s AND post_status != 'auto-draft'",
            $jsst_posttype
        ));
        return array(
            'label'       => esc_html($jsst_label),
            'destination' => esc_html($jsst_destination),
            'countable'   => true,
            'count'       => $jsst_count,
        );
    }

    private static function termEntity($jsst_label, $jsst_taxonomy, $jsst_destination) {
        $jsst_count = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT COUNT(*) FROM `" . jssupportticket::$_db->prefix . "term_taxonomy` WHERE taxonomy = %s",
            $jsst_taxonomy
        ));
        return array(
            'label'       => esc_html($jsst_label),
            'destination' => esc_html($jsst_destination),
            'countable'   => true,
            'count'       => $jsst_count,
        );
    }

    private static function countRows($jsst_table, $jsst_where = '') {
        $jsst_sql = "SELECT COUNT(*) FROM `" . esc_sql($jsst_table) . "`";
        if ($jsst_where !== '') {
            // Fixed strings written above, never anything from a request.
            $jsst_sql .= ' WHERE ' . $jsst_where;
        }
        return (int) jssupportticket::$_db->get_var($jsst_sql);
    }

    /* ------------------------------------------------------------------ *
     * What would be dropped
     * ------------------------------------------------------------------ */

    /**
     * Assemble the report, and work out what has nowhere to land.
     *
     * A kind of record is only listed as unsupported when there is actually some
     * of it in the source. Telling somebody they will lose time logs they never
     * kept is noise, and noise in this list is how the real entry in it gets
     * skipped over.
     */
    private static function assemble($jsst_entities, $jsst_gated) {
        $jsst_unsupported = array();
        foreach ($jsst_gated as $jsst_feature => $jsst_spec) {
            list($jsst_label, $jsst_table, $jsst_where) = $jsst_spec;
            if (!JSSTmigration::tableExists($jsst_table)) {
                continue;
            }
            $jsst_count = self::countRows($jsst_table, $jsst_where);
            if ($jsst_count < 1) {
                continue;
            }
            if (self::featureAvailable($jsst_feature)) {
                continue;
            }
            $jsst_unsupported[] = array(
                'label'    => esc_html($jsst_label),
                'count'    => $jsst_count,
                'fallback' => sprintf(
                    /* translators: %s: the name of the add-on that would hold this data */
                    esc_html(__('Not imported — the %s capability is not active on this site.', 'js-support-ticket')),
                    esc_html($jsst_feature)
                ),
            );
        }

        return array(
            'entities'    => $jsst_entities,
            'unsupported' => $jsst_unsupported,
            'warnings'    => array(),
        );
    }

    /**
     * Is the capability that would hold this data available here?
     *
     * Asked of the merged-add-on register first, because in 4.0 several of these
     * moved into the free core and are available whether or not the stand-alone
     * add-on is installed. Falling back to the active add-on list keeps this
     * right on a site still running the old separate plugins.
     */
    private static function featureAvailable($jsst_feature) {
        if (class_exists('JSSTmergedaddon') && method_exists('JSSTmergedaddon', 'featureEnabled')) {
            if (JSSTmergedaddon::featureEnabled($jsst_feature)) {
                return true;
            }
        }
        return in_array($jsst_feature, (array) jssupportticket::$_active_addons, true);
    }

}
