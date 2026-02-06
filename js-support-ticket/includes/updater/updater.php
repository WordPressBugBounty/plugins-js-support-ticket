<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

do_action('jssupportticket_load_wp_plugin_file');
// check for plugin using plugin name
if (is_plugin_active('js-support-ticket/js-support-ticket.php')) {
	$jsst_query = "SELECT * FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'versioncode' OR configname = 'last_version' OR configname = 'last_step_updater'";
	$jsst_result = jssupportticket::$_db->get_results($jsst_query);
	$jsst_config = array();
	foreach($jsst_result AS $jsst_rs){
		$jsst_config[$jsst_rs->configname] = $jsst_rs->configvalue;
	}
	$jsst_config['versioncode'] = jssupportticketphplib::JSST_str_replace('.', '', $jsst_config['versioncode']);	
	if(!empty($jsst_config['last_version']) && $jsst_config['last_version'] != '' && $jsst_config['last_version'] < $jsst_config['versioncode']){
		$jsst_last_version = $jsst_config['last_version'] + 1; // files execute from the next version
		$jsst_currentversion = $jsst_config['versioncode'];
		for($jsst_i = $jsst_last_version; $jsst_i <= $jsst_currentversion; $jsst_i++){
			$jsst_path = JSST_PLUGIN_PATH.'includes/updater/files/'.$jsst_i.'.php';
			if(file_exists($jsst_path)){
				include_once($jsst_path);
			}
		}
	}
	$jsst_mainfile = JSST_PLUGIN_PATH.'js-support-ticket.php';
	$jsst_contents = file_get_contents($jsst_mainfile);
	$jsst_contents = jssupportticketphplib::JSST_str_replace("include_once 'includes/updater/updater.php';", '', $jsst_contents);
	file_put_contents($jsst_mainfile, $jsst_contents);

	function JSST_recursiveremove($jsst_dir) {
	    if (empty($jsst_dir)) return;

	    // --- INITIALIZE WP_FILESYSTEM ---
	    
	    global $wp_filesystem;
        if (!function_exists('wp_handle_upload')) {
        	do_action('jssupportticket_load_wp_file');
    	}
    	if ( ! WP_Filesystem() ) {
            return false;
        }
    	$jsst_wp_filesystem = $wp_filesystem;

	    // Use WP_Filesystem exists() and delete()
	    if ($jsst_wp_filesystem->exists($jsst_dir)) {
	        /**
	         * The second parameter 'true' makes the delete recursive.
	         * This replaces glob(), is_dir(), is_file(), and rmdir().
	         */
	        $jsst_wp_filesystem->delete($jsst_dir, true);
	    }
	}            	
	$jsst_dir = JSST_PLUGIN_PATH.'includes/updater';
	JSST_recursiveremove($jsst_dir);

}



?>
