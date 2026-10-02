<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Deactivation and uninstall behaviour. (Roadmap 3.2-CORE-02)
 *
 * The contract:
 *
 *   Deactivation  - never destroys data. Scheduled events are cleared and the
 *                   control-panel page is unpublished so the front end stops
 *                   rendering a broken shortcode. Roles and capabilities are
 *                   left in place so reactivating restores the site unchanged.
 *
 *   Uninstall     - governed by the `data_retention_on_uninstall` setting:
 *                     'preserve' (default) keeps every table, upload and option,
 *                                and only removes the plugin's roles and
 *                                capabilities, which are meaningless without it.
 *                     'delete'   drops the plugin tables, deletes the plugin
 *                                options, removes the roles and capabilities and
 *                                deletes the uploaded attachment directory.
 *
 * Preserve is the default because destroying a site's ticket history on an
 * accidental uninstall is unrecoverable, while leftover data is not.
 */
class JSSTdeactivation {

    const RETENTION_SETTING = 'data_retention_on_uninstall';
    const RETENTION_PRESERVE = 'preserve';
    const RETENTION_DELETE = 'delete';

    static function jssupportticket_deactivate() {
        wp_clear_scheduled_hook('jsst_process_transation_key_status');
        wp_clear_scheduled_hook('jssupporticket_updateticketstatus');
        wp_clear_scheduled_hook('jssupporticket_ticketviaemail');
        wp_clear_scheduled_hook('jsst_auto_update_addons');
        wp_clear_scheduled_hook('jsst_delete_expire_session_data');
        wp_clear_scheduled_hook('jsst_daily_autocleanup_cron'); // Roadmap 4.0-CORE-13
        wp_clear_scheduled_hook('jsst_translations_daily');
        $jsst_id = jssupportticket::getPageid();
        jssupportticket::$_db->get_var(jssupportticket::$_db->prepare("UPDATE `" . jssupportticket::$_db->prefix . "posts` SET post_status = 'draft' WHERE ID = %d", $jsst_id));

        // Deactivation is reversible, so roles and capabilities stay untouched.
        // Removing `jsst_support_ticket` from Administrator here (as releases up
        // to 3.1.7 did) locked administrators out of the plugin after a
        // deactivate/reactivate cycle, because activation only re-added it when
        // the role reconciliation happened to run. (Roadmap 3.2-CORE-02)
    }

    /**
     * The plugin tables. Used by uninstall and by multisite site deletion.
     */
    static function jssupportticket_tables_to_drop() {
        global $wpdb;
        $jsst_tables = array(
           $wpdb->prefix."js_ticket_fieldsordering",
           $wpdb->prefix."js_ticket_faqs",
           $wpdb->prefix."js_ticket_departments",
           $wpdb->prefix."js_ticket_attachments",
           $wpdb->prefix."js_ticket_config",
           $wpdb->prefix."js_ticket_email",
           $wpdb->prefix."js_ticket_emailtemplates",
           $wpdb->prefix."js_ticket_priorities",
           $wpdb->prefix."js_ticket_statuses",
           $wpdb->prefix."js_ticket_products",
           $wpdb->prefix."js_ticket_replies",
           $wpdb->prefix."js_ticket_system_errors",
           $wpdb->prefix."js_ticket_tickets",
           $wpdb->prefix."js_ticket_erasedatarequests",
           $wpdb->prefix."js_ticket_users",
           $wpdb->prefix."js_ticket_multiform",
           $wpdb->prefix."js_ticket_slug",
           $wpdb->prefix."js_ticket_jshdsessiondata",
           $wpdb->prefix."js_ticket_zywrap_categories",
           $wpdb->prefix."js_ticket_zywrap_ai_models",
           $wpdb->prefix."js_ticket_zywrap_languages",
           $wpdb->prefix."js_ticket_zywrap_use_cases",
           $wpdb->prefix."js_ticket_zywrap_wrappers",
           $wpdb->prefix."js_ticket_zywrap_block_templates",
           $wpdb->prefix."js_ticket_zywrap_settings",
           $wpdb->prefix."js_ticket_zywrap_usage_logs",
           // Capabilities merged into the free core in 4.0 own their tables now,
           // so uninstalling in delete mode has to clear them too.
           // (Roadmap 4.0-CORE-01, 4.0-CORE-02)
           $wpdb->prefix."js_ticket_activity_log",
           $wpdb->prefix."js_ticket_notes",
           $wpdb->prefix."js_ticket_department_message_premade",
           $wpdb->prefix."js_ticket_help_topics",
           $wpdb->prefix."js_ticket_email_banlist",
           // Core does not write the ban log — that feature is Pro — but the
           // data belongs to the help desk, so deleting the plugin with data
           // removal has to clear it. (Roadmap 4.0-CORE-12)
           $wpdb->prefix."js_ticket_banlist_log",
           // Tags are new in 4.0 and belong to core outright. (Roadmap 4.0-CORE-17)
           $wpdb->prefix."js_ticket_ticket_tags",
           $wpdb->prefix."js_ticket_tags",
           // So are saved queue views. (Roadmap 4.0-CORE-18)
           $wpdb->prefix."js_ticket_saved_views",
           // Everything 4.5 added. These are as much the customer's data as a
           // ticket is - a watcher list says who was working on what, a
           // notification row carries a ticket's subject, and a push
           // subscription is an endpoint somebody's browser handed us - so
           // uninstalling in delete mode has to clear them too. Missing from
           // this list they would survive the plugin being deleted, which is
           // the one thing the retention setting promises will not happen.
           // (Roadmap 4.5-FE-05, 4.5-FE-08, 4.5-UX-02)
           $wpdb->prefix."js_ticket_teams",
           $wpdb->prefix."js_ticket_team_members",
           $wpdb->prefix."js_ticket_watchers",
           $wpdb->prefix."js_ticket_mentions",
           $wpdb->prefix."js_ticket_collab_drafts",
           $wpdb->prefix."js_ticket_notifications",
           $wpdb->prefix."js_ticket_push_subscriptions",
           // These tables ship with addons now, and each addon's own uninstall
           // drops what it owns. They stay on this list as well, because the
           // two uninstalls answer different questions: an addon being deleted
           // takes its own tables, and the whole desk being deleted in delete
           // mode takes everything the desk ever stored - including the tables
           // of an addon whose files are still sitting in the plugins folder.
           // DROP IF EXISTS, so whichever runs first is right.
           //
           // And everything 5.0 added. Same reasoning as the 4.5 block above,
           // and the same mistake avoided: an SLA clock carries a ticket's
           // history, a survey carries what a customer wrote about somebody by
           // name, a time entry says what was billed for, and a retention hold
           // records why a ticket was kept out of a deletion. Left off this
           // list they would all survive an uninstall that was asked to delete
           // everything, which is the one thing the retention setting promises
           // will not happen.
           // (Roadmap 5.0-SLA-01, 5.0-AUT-01, 5.0-AUT-04, 5.0-API-02,
           //  5.0-API-04, 5.0-ANA-02, 5.0-ANA-03, 5.0-ANA-04)
           $wpdb->prefix."js_ticket_sla_policies",
           $wpdb->prefix."js_ticket_sla_clocks",
           $wpdb->prefix."js_ticket_workflows",
           $wpdb->prefix."js_ticket_workflow_runs",
           $wpdb->prefix."js_ticket_recurring",
           $wpdb->prefix."js_ticket_webhooks",
           $wpdb->prefix."js_ticket_webhook_deliveries",
           $wpdb->prefix."js_ticket_jobs",
           $wpdb->prefix."js_ticket_time_entries",
           $wpdb->prefix."js_ticket_satisfaction",
           $wpdb->prefix."js_ticket_retention_holds",
           $wpdb->prefix."js_ticket_retention_runs",
           // And 5.5. A company record names people by e-mail address and says
           // which of them may read a colleague's ticket, which is personal
           // data about a customer twice over; a connector log holds what this
           // desk said to somebody else's server. Same reasoning as the two
           // blocks above.
           // (Roadmap 5.5-COM-06, 5.5-CH-04, 5.5-COM-05)
           $wpdb->prefix."js_ticket_companies",
           $wpdb->prefix."js_ticket_company_people",
           $wpdb->prefix."js_ticket_connector_log",
           $wpdb->prefix."js_ticket_entitlements",
           $wpdb->prefix."js_ticket_entitlement_ledger",
           // A consent record is what somebody agreed to and when. It is
           // personal data, and it goes with everything else in delete mode.
           // (Roadmap 5.5-SEC-02)
           $wpdb->prefix."js_ticket_marketing_consent",
           // And 6.0. The source register holds one row per document somebody
           // decided the AI may or may not quote. It is a record of a human
           // judgement rather than derived state, so nothing rebuilds it and it
           // has to go with the rest in delete mode. (Roadmap 6.0-AI-02)
           $wpdb->prefix."js_ticket_ai_rules",
           // Every answer the AI proposed and what a person decided about it.
           // A record of human judgement, not derived state. (Roadmap 6.0-AI-03)
           $wpdb->prefix."js_ticket_ai_answers",
        );

        // Plus every table that actually exists under the plugin's prefix. The
        // list above is written by hand and the add-ons outgrew it: on a full
        // 5.0.0 install 38 of 91 tables were missing from it (staff, articles,
        // categories, the AI and chat tables...), so "delete" left them all
        // behind. The explicit list stays for readability; this makes it
        // complete. $wpdb->prefix is per site, so on multisite this only ever
        // sees the current site's tables.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $jsst_found = $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($wpdb->prefix . 'js_ticket_') . '%'));
        return array_values(array_unique(array_merge($jsst_tables, (array) $jsst_found)));
    }

    /**
     * WordPress options the plugin owns. Removed only in delete mode.
     */
    static function jssupportticket_options_to_delete() {
        return array(
            'jssupportticket',
            'jssupportticket_do_activation_redirect',
            'jsst_currentversion',
            // Which release's update SQL has been applied. Must go with the
            // tables in delete mode, or a later reinstall would skip the file
            // that creates them again.
            'jsst_sql_applied',
            'jsst_show_key_expiry_msg',
            'jsst_role_version',
            '_wpjshd_session_',
            // Schema markers and merge bookkeeping added in 4.0.
            // (Roadmap 4.0-CORE-01, 4.0-CORE-02, 4.0-CORE-19)
            'jsst_tickethistory_schema',
            'jsst_note_schema',
            'jsst_cannedresponses_schema',
            'jsst_helptopic_schema',
            'jsst_emailcc_schema',
            'jsst_banemail_schema',
            'jsst_tag_schema',
            'jsst_queue_schema',
            'jsst_attachment_guard',
            'jsst_mail_last_result',
            // The mailbox collection log. Kept by core rather than by the piping
            // add-on, so it is core's to clean up. (Roadmap 4.0-OPS-01)
            'jsst_piping_log',
            'jsst_setup_dismissed',
            'jsst_autocleanup_last_run',
            'jssupportticket_admin_charts_visibility',
            'jsst_merged_addon_notice_dismissed',
            'jsst_merged_addon_snapshot_tickethistory',
            'jsst_merged_addon_snapshot_note',
            'jsst_merged_addon_snapshot_cannedresponses',
            'jsst_merged_addon_snapshot_helptopic',
            'jsst_merged_addon_snapshot_emailcc',
            'jsst_merged_addon_restored_tickethistory',
            'jsst_merged_addon_restored_note',
            'jsst_merged_addon_restored_cannedresponses',
            'jsst_merged_addon_restored_helptopic',
            'jsst_merged_addon_restored_emailcc',
            // 4.5's settings and schema markers. The markers have to go with
            // the tables in delete mode for the same reason jsst_sql_applied
            // does: a marker left behind tells a later reinstall the table it
            // names already exists, and the repair that would have created it
            // never runs. (Roadmap 4.5-FE-04 onwards)
            'jsst_teams_schema',
            'jsst_collab_schema',
            'jsst_notifications_schema',
            'jsst_webpush_schema',
            'jsst_visibility_agents',
            'jsst_visibility_default',
            'jsst_visibility_roles',
            'jsst_availability_agents',
            'jsst_availability_default',
            'jsst_workload',
            'jsst_notification_prefs',
            'jsst_notification_digest_sent',
            'jsst_webpush_keys',
            'jsst_role_templates',
            'jsst_brand',
            'jsst_internalmail_migrated',
            'jsst_queue_columns',
            'jsst_recent_events',
            // 5.0's settings and schema markers, listed as they are written
            // rather than at the end of the release: the 4.5 block above was
            // assembled afterwards and every one of them had been missed, which
            // left push endpoints and notification history behind on an
            // uninstall that was asked to delete everything.
            // (Roadmap 5.0-SLA-01 onwards)
            'jsst_sla_schema',
            'jsst_sla_settings',
            'jsst_sla_tiers',
            'jsst_sla_migrated_policies',
            'jsst_sla_overdue_snapshot',
            'jsst_workflow_schema',
            'jsst_workflow_settings',
            'jsst_routing',
            'jsst_routing_pointer',
            'jsst_routing_log',
            'jsst_recurring_schema',
            'jsst_restapi_settings',
            'jsst_webhooks_schema',
            'jsst_webhooks_settings',
            'jsst_webhook_site_secret',
            'jsst_hooks_index',
            'jsst_form_logic',
            'jsst_form_validation',
            'jsst_form_versions',
            // The export screen keeps its schedules and its record of the
            // files it produced in options rather than a table of its own, so
            // they are cleared here; the files themselves live under the data
            // directory, which jssupportticket_delete_data_directory() already
            // removes wholesale. (Roadmap 5.0-ANA-01)
            'jsst_export_schedules',
            'jsst_export_files',
            // The authorisation matrix's cached scan. (Roadmap 5.0-SEC-01)
            'jsst_authmatrix',
            // Time worked: how it is counted, what each person is expected to
            // bill and what each customer agreed to. The entries themselves
            // are a table and are dropped with the others. (Roadmap 5.0-ANA-02)
            'jsst_time_settings',
            'jsst_time_targets',
            'jsst_time_budgets',
            'jsst_time_adopted',
            'jsst_time_schema',
            // Satisfaction: how people are asked, and the flag that stops a
            // fallen score being announced every hour. (Roadmap 5.0-ANA-03)
            'jsst_satisfaction_settings',
            'jsst_satisfaction_schema',
            'jsst_satisfaction_alerted',
            // Retention governance. The holds and the proposals are tables and
            // are dropped with the others. (Roadmap 5.0-ANA-04)
            'jsst_retention_settings',
            'jsst_retention_schema',
            // Analytics keeps no table: only the digest's cadence and the mark
            // that stops a missed cron sending two. (Roadmap 5.0-ANA-05)
            'jsst_analytics_settings',
            'jsst_analytics_digest_sent',
            // 5.5. The connector secrets are the reason this block matters more
            // than the ones above it: leaving them behind leaves live API keys
            // for somebody else's service in the database of a site whose owner
            // asked for everything to be deleted.
            // (Roadmap 5.5-COM-06, 5.5-CH-04, 5.5-CH-02, 5.5-COM-05)
            'jsst_companies_schema',
            'jsst_connectors_schema',
            'jsst_connector_settings',
            'jsst_connector_secrets',
            'jsst_connector_health',
            'jsst_entitlements_schema',
            'jsst_entitlement_settings',
            'jsst_entitlement_overrides',
            'jsst_entitlement_refusals',
            'jsst_chat_routes',
            'jsst_chat_people',
            'jsst_consent_schema',
            'jsst_consent_settings',
            'jsst_templatelocale_schema',
            // 6.0. The engine keys belong on this list for exactly the reason
            // the connector secrets above it do - they are somebody's paid API
            // credentials, and leaving them in the options table of a site whose
            // owner asked for everything to be deleted is the worst thing this
            // file can get wrong. The Copilot's older option names are here too
            // because JSSTaiengine still falls back to reading them, so deleting
            // only the new ones would leave a working key behind under the name
            // it had before the unification.
            // (Roadmap 6.0-AI-01, 6.0-AI-02, 6.0-AI-08)
            'jsst_ai_master',
            'jsst_ai_lanes',
            'jsst_ai_redact',
            'jsst_ai_journal',
            'jsst_ai_engine',
            'jsst_ai_model',
            'jsst_ai_key_zywrap',
            'jsst_ai_key_anthropic',
            'jsst_ai_key_local',
            'jsst_ai_local_endpoint',
            'jsst_ai_local_model',
            'jsst_ai_local_timeout',
            'jsst_copilot_api_key',
            'jsst_copilot_provider',
            'jsst_copilot_model',
            'jsst_copilot_language',
            'jsst_copilot_log',
            'jsst_copilot_batches',
            'jsst_zywrap_api_key',
            // And the source register's bookkeeping. The schema marker goes
            // with its table for the reason the 4.5 block sets out: a marker
            // left behind tells a later reinstall the table already exists.
            'jsst_ai_sources',
            'jsst_ai_rules_schema',
            'jsst_ai_answers_schema',
            'jsst_ai_approval',
            'jsst_ai_never_automate',
            'jsst_ai_benchmark_fixture',
            'jsst_ai_config_renamed',
            'jsst_aiagent_adopted',
            'jsst_ai_source_modes',
            'jsst_ai_source_sync',
        );
    }

    /**
     * Read the retention setting straight from the config table.
     *
     * Uninstall runs in a bare WordPress bootstrap where the plugin's own
     * classes and cached config are unavailable, so this reads the row directly
     * and falls back to preserving data whenever the answer is not a clear
     * "delete".
     *
     * @return string self::RETENTION_PRESERVE or self::RETENTION_DELETE
     */
    static function jssupportticket_get_retention_mode() {
        global $wpdb;
        $jsst_table = $wpdb->prefix . 'js_ticket_config';
        $jsst_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $jsst_table));
        if (!$jsst_exists) {
            return self::RETENTION_PRESERVE;
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is built from the wpdb prefix.
        $jsst_value = $wpdb->get_var($wpdb->prepare("SELECT configvalue FROM `{$jsst_table}` WHERE configname = %s", self::RETENTION_SETTING));
        return ($jsst_value === self::RETENTION_DELETE) ? self::RETENTION_DELETE : self::RETENTION_PRESERVE;
    }

    /**
     * Everything that must happen on uninstall for the current blog.
     *
     * Roles and capabilities are always removed: they are the plugin's own and
     * leaving them behind is what caused stale-capability support tickets and
     * failed reinstalls. Tables, options and uploaded files follow the setting.
     */
    static function jssupportticket_uninstall_site($jsst_mode) {
        global $wpdb;

        self::jssupportticket_remove_roles_and_caps();

        if ($jsst_mode !== self::RETENTION_DELETE) {
            return;
        }

        $jsst_datadirectory = self::jssupportticket_get_data_directory();

        foreach (self::jssupportticket_tables_to_drop() as $jsst_tablename) {
            $jsst_tablename = esc_sql($jsst_tablename);
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is built from the wpdb prefix.
            $wpdb->query("DROP TABLE IF EXISTS `{$jsst_tablename}`");
        }

        foreach (self::jssupportticket_options_to_delete() as $jsst_option) {
            delete_option($jsst_option);
        }

        // Every option the plugin and its add-ons created, not only the ones
        // listed: 28 were left behind by the list alone, among them the
        // licence key, its state and token, so a "delete everything" uninstall
        // came back already licensed on reinstall. Also 4.0.0's stored
        // installer tokens and the plugin's transients.
        //
        // `jsst_` alone missed the add-ons' own rows, which are spelled with a
        // hyphen (jsst-addon-<name>-version / -active-state), the licence
        // state row, and the widget, review and post-installation settings -
        // 76 rows on a full install - so each spelling is named here.
        $jsst_patterns = array(
            'jsst_',
            'jsst-addon-',
            'jsstnotification_',
            'JSSTSocialLogin',
            'jssupportticket_',
            'jssupport_',
            'widget_jsst',
            'transaction_key_for_js-support-ticket',
            'key_status_for_js-support-ticket',
            '_transient_jsst_',
            '_transient_timeout_jsst_',
        );
        $jsst_where = implode(' OR ', array_fill(0, count($jsst_patterns), 'option_name LIKE %s'));
        $jsst_likes = array();
        foreach ($jsst_patterns as $jsst_pattern) {
            $jsst_likes[] = $wpdb->esc_like($jsst_pattern) . '%';
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- the placeholders are generated above, one per pattern.
        $jsst_names = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE " . $jsst_where,
            $jsst_likes
        ));
        foreach ((array) $jsst_names as $jsst_option) {
            delete_option($jsst_option);
        }

        self::jssupportticket_delete_data_directory($jsst_datadirectory);
    }

    /**
     * Remove the plugin's roles and capabilities without touching capabilities
     * that belong to other plugins.
     */
    static function jssupportticket_remove_roles_and_caps() {
        if (!function_exists('get_role')) {
            return;
        }
        $jsst_rolesfile = plugin_dir_path(__FILE__) . 'roles.php';
        if (file_exists($jsst_rolesfile)) {
            include_once $jsst_rolesfile;
        }
        if (class_exists('JSSTroles')) {
            JSSTroles::removeAll();
            return;
        }
        // Fallback for a bootstrap where the class could not be loaded.
        global $wp_roles;
        if (isset($wp_roles) && is_object($wp_roles)) {
            foreach (array_keys($wp_roles->roles) as $jsst_slug) {
                $jsst_role = get_role($jsst_slug);
                if (!$jsst_role) {
                    continue;
                }
                $jsst_role->remove_cap('jsst_support_ticket');
                $jsst_role->remove_cap('jsst_support_ticket_tickets');
                $jsst_role->remove_cap('jsst_support_ticket_reply');
                $jsst_role->remove_cap('jsst_support_ticket_note');
                $jsst_role->remove_cap('jsst_support_ticket_state');
                $jsst_role->remove_cap('jsst_support_ticket_edit');
                $jsst_role->remove_cap('jsst_support_ticket_kb');
                $jsst_role->remove_cap('jsst_support_ticket_delete');
                $jsst_role->remove_cap('jsst_support_ticket_merge');
            }
        }
        if (get_role('js_support_ticket_admin_agent')) {
            remove_role('js_support_ticket_admin_agent');
        }
        if (get_role('js_support_ticket_light_agent')) {
            remove_role('js_support_ticket_light_agent');
        }
    }

    /**
     * The upload sub-directory holding ticket attachments, read directly from
     * the config table because the plugin is not bootstrapped during uninstall.
     */
    static function jssupportticket_get_data_directory() {
        global $wpdb;
        $jsst_table = $wpdb->prefix . 'js_ticket_config';
        $jsst_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $jsst_table));
        if (!$jsst_exists) {
            return '';
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is built from the wpdb prefix.
        $jsst_value = $wpdb->get_var($wpdb->prepare("SELECT configvalue FROM `{$jsst_table}` WHERE configname = %s", 'data_directory'));
        return is_string($jsst_value) ? $jsst_value : '';
    }

    /**
     * Delete the plugin's upload directory. Refuses anything that is not a
     * single directory name directly under the uploads base directory, so a
     * corrupted setting can never point the deletion somewhere else.
     */
    static function jssupportticket_delete_data_directory($jsst_datadirectory) {
        if (empty($jsst_datadirectory) || !is_string($jsst_datadirectory)) {
            return;
        }
        if (preg_match('/^[A-Za-z0-9_-]+$/', $jsst_datadirectory) !== 1) {
            return;
        }
        $jsst_uploads = wp_upload_dir();
        if (empty($jsst_uploads['basedir'])) {
            return;
        }
        $jsst_base = realpath($jsst_uploads['basedir']);
        $jsst_target = realpath(trailingslashit($jsst_uploads['basedir']) . $jsst_datadirectory);
        if (!$jsst_base || !$jsst_target) {
            return;
        }
        // Never delete outside the uploads directory, and never the base itself.
        if ($jsst_target === $jsst_base || strpos($jsst_target, $jsst_base . DIRECTORY_SEPARATOR) !== 0) {
            return;
        }

        global $wp_filesystem;
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!WP_Filesystem()) {
            return;
        }
        $wp_filesystem->delete($jsst_target, true);
    }

}

?>
