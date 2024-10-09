<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

do_action('jssupportticket_load_wp_plugin_file');
// check for plugin using plugin name
if (is_plugin_active('js-support-ticket/js-support-ticket.php')) {
	$query = "SELECT * FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'versioncode' OR configname = 'last_version' OR configname = 'last_step_updater'";
	$result = jssupportticket::$_db->get_results($query);
	$config = array();
	foreach($result AS $rs){
		$config[$rs->configname] = $rs->configvalue;
	}
	$config['versioncode'] = jssupportticketphplib::JSST_str_replace('.', '', $config['versioncode']);	
	if(!empty($config['last_version']) && $config['last_version'] != '' && $config['last_version'] < $config['versioncode']){
		$last_version = $config['last_version'] + 1; // files execute from the next version
		$currentversion = $config['versioncode'];
		for($i = $last_version; $i <= $currentversion; $i++){
			$path = JSST_PLUGIN_PATH.'includes/updater/files/'.$i.'.php';
			if(file_exists($path)){
				include_once($path);
			}
		}
	}
	$mainfile = JSST_PLUGIN_PATH.'js-support-ticket.php';
	$contents = file_get_contents($mainfile);
	$contents = jssupportticketphplib::JSST_str_replace("include_once 'includes/updater/updater.php';", '', $contents);
	file_put_contents($mainfile, $contents);

	function JSST_recursiveremove($dir) {
		$structure = glob(rtrim($dir, "/").'/*');
		if (is_array($structure)) {
			foreach($structure as $file) {
				if (is_dir($file)) JSST_recursiveremove($file);
				elseif (is_file($file)) wp_delete_file($file);
			}
		}
		rmdir($dir);
	}            	
	$dir = JSST_PLUGIN_PATH.'includes/updater';
	JSST_recursiveremove($dir);

}



?>
