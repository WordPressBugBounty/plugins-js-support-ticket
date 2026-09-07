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
        );
        return $jsst_tables;
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
