<?php

/**
 * JS Support Ticket Uninstall
 *
 * What is removed and what is preserved is governed by the
 * `data_retention_on_uninstall` setting. The contract is documented in the
 * class comment of includes/deactivation.php. (Roadmap 3.2-CORE-02)
 *
 * @author 		Ahmed Bilal
 * @category 	Core
 * @package 	JS Support Ticket/Uninstaller
 * @version     4.0.0
 */
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

global $wpdb;
include_once 'includes/deactivation.php';

$jsst_retention_mode = JSSTdeactivation::jssupportticket_get_retention_mode();

if (function_exists('is_multisite') && is_multisite()) {
    $jsst_blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    foreach ($jsst_blogs as $jsst_blog_id) {
        switch_to_blog($jsst_blog_id);
        // Each site keeps its own setting, so read it per site rather than
        // applying the network-main site's choice everywhere.
        JSSTdeactivation::jssupportticket_uninstall_site(
            JSSTdeactivation::jssupportticket_get_retention_mode()
        );
        restore_current_blog();
    }
} else {
    JSSTdeactivation::jssupportticket_uninstall_site($jsst_retention_mode);
}
