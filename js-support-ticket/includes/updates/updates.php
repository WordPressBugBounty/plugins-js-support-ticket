<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTupdates {

    static function checkUpdates($jsst_cversion=null) {
        if (is_null($jsst_cversion)) {
            $jsst_cversion = jssupportticket::$_currentversion;
        }
        $jsst_installedversion = JSSTupdates::getInstalledVersion();
        if ($jsst_installedversion != $jsst_cversion) {
			//UPDATE the last_version of the plugin
			$jsst_query = "REPLACE INTO `".jssupportticket::$_db->prefix."js_ticket_config` (`configname`, `configvalue`, `configfor`) VALUES ('last_version','','default');";
			jssupportticket::$_db->query($jsst_query); //old actual
			/*jssupportticket::$_db->show_errors(false);
			@jssupportticket::$_db->query($jsst_query);			*/
			$jsst_query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname='versioncode'";
			$jsst_versioncode = jssupportticket::$_db->get_var($jsst_query);
			$jsst_versioncode = jssupportticketphplib::JSST_str_replace('.','',$jsst_versioncode);
			$jsst_query = "UPDATE `".jssupportticket::$_db->prefix."js_ticket_config` SET configvalue = '".esc_sql($jsst_versioncode)."' WHERE configname = 'last_version';";
			jssupportticket::$_db->query($jsst_query);
            $jsst_from = $jsst_installedversion + 1;
            $jsst_to = $jsst_cversion;
            // --- INITIALIZE WP_FILESYSTEM ---
            global $wp_filesystem;
            if (!function_exists('wp_handle_upload')) {
                do_action('jssupportticket_load_wp_file');
            }
            if ( ! WP_Filesystem() ) {
                return false;
            }
            $jsst_wp_filesystem = $wp_filesystem;

            for ($jsst_i = $jsst_from; $jsst_i <= $jsst_to; $jsst_i++) {
                $jsst_installfile = JSST_PLUGIN_PATH . 'includes/updates/sql/' . $jsst_i . '.sql';

                // Use $jsst_wp_filesystem->exists instead of file_exists
                if ($jsst_wp_filesystem->exists($jsst_installfile)) {
                    // FIX: Use $jsst_wp_filesystem->get_contents instead of fopen/fread
                    $jsst_file_content = $jsst_wp_filesystem->get_contents($jsst_installfile);

                    if ($jsst_file_content !== false) {
                        // Split queries by semicolon (;) followed by a newline or end of string
                        // This is more efficient than reading line-by-line in PHP
                        $jsst_queries = preg_split("/;(?=\s*$|[\r\n])/m", $jsst_file_content);

                        foreach ($jsst_queries as $jsst_query) {
                            $jsst_query = jssupportticketphplib::JSST_trim($jsst_query);

                            // Replace the prefix placeholder
                            $jsst_query = jssupportticketphplib::JSST_str_replace("#__", jssupportticket::$_db->prefix, $jsst_query);

                            if (!empty($jsst_query)) {
                                jssupportticket::$_db->query($jsst_query);
                            }
                        }
                    }
                }
            }
        }
    }

    static function getInstalledVersion() {
        $jsst_query = "SELECT configvalue FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configname = 'versioncode'";
        $jsst_version = jssupportticket::$_db->get_var($jsst_query);
        if (!$jsst_version)
            $jsst_version = '102';
        else
            $jsst_version = jssupportticketphplib::JSST_str_replace('.', '', $jsst_version);
        return $jsst_version;
    }

}

?>
