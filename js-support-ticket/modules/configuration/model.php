<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTconfigurationModel {

    function getConfigurations() {
        $query = "SELECT configname,configvalue,addon
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` ";//WHERE configfor != 'ticketviaemail'";
        $data = jssupportticket::$_db->get_results($query);

        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        foreach ($data AS $config) {
            if($config->addon == '' ||  in_array($config->addon, jssupportticket::$_active_addons)){
                jssupportticket::$_data[0][$config->configname] = $config->configvalue;
            }
        }

        jssupportticket::$_data[1] = JSSTincluder::getJSModel('email')->getAllEmailsForCombobox();
        if(in_array('banemail', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('banemaillog')->checkbandata();
        }
        return;
    }

    function getConfigurationByFor($for) {
		if($for == 'ticketviaemail'){
			$query = "SELECT COUNT(configname) FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = '".esc_sql($for)."'";
			$count = jssupportticket::$_db->get_var($query);
			if($count < 5){
				$query = "SELECT configname,configvalue
							FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` ";
				$data = jssupportticket::$_db->get_results($query);
				if (jssupportticket::$_db->last_error != null) {
					JSSTincluder::getJSModel('systemerror')->addSystemError();
				}
				foreach ($data AS $config) {
					jssupportticket::$_data[0][$config->configname] = $config->configvalue;
				}
				if(in_array('banemail', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('banemaillog')->checkbandata();
                }
                return;
			}
		}
        $query = "SELECT configname,configvalue
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = '".esc_sql($for)."'";
        $data = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        foreach ($data AS $config) {
            jssupportticket::$_data[0][$config->configname] = $config->configvalue;
        }
        if(in_array('banemail', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('banemaillog')->checkbandata();
        }
        return;
    }
    function getCountByConfigFor($for) {
        if (( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())) {
            $query = "SELECT COUNT(configvalue)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = '".esc_sql($for). "' AND configname LIKE '%staff' AND configvalue = 1 " ;
        }else{
            $query = "SELECT COUNT(configvalue)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = '".esc_sql($for) . "' AND configname LIKE '%user' AND configvalue = 1 " ;
        }
        $data = jssupportticket::$_db->get_var($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $data;
    }

    function storeDesktopNotificationLogo($filename) {
        jssupportticket::$_db->query("UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_config` SET configvalue = '" . esc_sql($filename) . "' WHERE configname = 'logo_for_desktop_notfication_url' ");
    }

    function deleteDesktopNotificationsLogo() {
        $datadirectory = jssupportticket::$_config['data_directory'];

        $maindir = wp_upload_dir();
        $path = $maindir['basedir'];
        $path = $path .'/'.$datadirectory;

        $file_name = JSSTincluder::getJSModel('configuration')->getConfigValue('logo_for_desktop_notfication_url');

        $path = $path . '/attachmentdata/';
        $dsk_logo_file =  $path.$file_name;
        if($file_name != ''){
            if ( file_exists( $dsk_logo_file ) ) {
                wp_delete_file($dsk_logo_file);
            }
        }
    }


    function storeConfiguration($data) {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-configuration') ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $data = jssupportticket::JSST_sanitizeData($data); // JSST_sanitizeData() function uses wordpress santize functions
        // handle editor text for offline message after sanitizing all data
        if (isset($data['offline_message'])) {
            $data['offline_message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['offline_message']);
            $data['offline_message'] = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($data['offline_message']);
            $data['offline_message'] = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data['offline_message']);
        }
        // handle editor text for new ticket message after sanitizing all data
        if (isset($data['new_ticket_message'])) {
            $data['new_ticket_message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['new_ticket_message']);
            $data['new_ticket_message'] = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($data['new_ticket_message']);
            $data['new_ticket_message'] = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data['new_ticket_message']);
        }
        // handle editor text for visitor message after sanitizing all data
        if (isset($data['visitor_message'])) {
            $data['visitor_message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['visitor_message']);
            $data['visitor_message'] = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($data['visitor_message']);
            $data['visitor_message'] = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data['visitor_message']);
        }
        // handle editor text for feedback thanks message after sanitizing all data
        if (isset($data['feedback_thanks_message'])) {
            $data['feedback_thanks_message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['feedback_thanks_message']);
            $data['feedback_thanks_message'] = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($data['feedback_thanks_message']);
            $data['feedback_thanks_message'] = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($data['feedback_thanks_message']);
        }
        $notsave = false;
        $updateColors = false;
        foreach ($data AS $key => $value) {
            $query = true;
            
            if ($key == 'screentag_position') {
                if ($value != jssupportticket::$_config['screentag_position']) {
                    $updateColors = true;
                }
            }

            if ($key == 'pagination_default_page_size') {
                if ($value < 3) {
                    JSSTmessage::setMessage(esc_html(__('Pagination default page size not saved', 'js-support-ticket')), 'error');
                    continue;
                }
            }

            if($key == 'del_logo_for_desktop_notfication' && $value == 1){
                $this->deleteDesktopNotificationsLogo();
                $key = 'logo_for_desktop_notfication_url';
                $value = '';
            }


            if ($key == 'data_directory') {
                $data_directory = $value;
                if(empty($data_directory)){
                    JSSTmessage::setMessage(esc_html(__('Data directory cannot empty.', 'js-support-ticket')), 'error');
                    continue;
                }
                if(strpos($data_directory, '/') !== false){
                    JSSTmessage::setMessage(esc_html(__('Data directory is not proper.', 'js-support-ticket')) , 'error');
                    continue;
                }
                $path = JSST_PLUGIN_PATH.'/'.$data_directory;
                if ( ! file_exists($path)) {
                   mkdir($path, 0755);
                }
                if( ! is_writeable($path)){
                    JSSTmessage::setMessage(esc_html(__('Data directory is not writable.', 'js-support-ticket')), 'error');
                    continue;
                }
            }
            if ($key == 'system_slug') {
                if(empty($value)){
                    JSSTmessage::setMessage(esc_html(__('System slug not be empty.', 'js-support-ticket')), 'error');
                    continue;
                }
                $value = jssupportticketphplib::JSST_str_replace(' ', '-', $value);
                $query = 'SELECT COUNT(ID) FROM `'.jssupportticket::$_db->prefix.'posts` WHERE post_name = "'.esc_sql($value).'"';
                $countslug = jssupportticket::$_db->get_var($query);
                if($countslug >= 1){
                    JSSTmessage::setMessage(esc_html(__('System slug is conflicted with post or page slug.', 'js-support-ticket')), 'error');
                    continue;
                }
            }
            jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_config', array('configvalue' => $value), array('configname' => $key));
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                $notsave = true;
            }
        }
        if ($notsave == false) {
            JSSTmessage::setMessage(esc_html(__('The configuration has been stored', 'js-support-ticket')), 'updated');
            // if($data['tve_enabled'] == 1){
            //     //JSSTincluder::getJSController('emailpiping')->registerReadEmails();
            // }
        } else {
            JSSTmessage::setMessage(esc_html(__('The configuration not has been stored', 'js-support-ticket')), 'error');
        }
        if ($updateColors == true) {
            JSSTincluder::getJSModel('jssupportticket')->updateColorFile();
        }
        update_option('rewrite_rules', '');

        if (isset($_FILES['logo_for_desktop_notfication'])) { // upload image for desktop notifications
            JSSTincluder::getObjectClass('uploads')->uploadDesktopNotificationLogo();
        }
        if (isset($_FILES['support_custom_img'])) { // upload image for custom image
            $this->storeSupportCustomImage();
        }
        return;
    }

    function storeSupportCustomImage() {
        if (!function_exists('wp_handle_upload')) {
            do_action('jssupportticket_load_wp_file');
        }
        $maindir = wp_upload_dir();
        $basedir = $maindir['basedir'];
        $datadirectory = jssupportticket::$_config['data_directory'];
        
        $path = $basedir . '/' . $datadirectory;
        if (!file_exists($path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($path);
        }
        $isupload = false;
        $path = $path . '/supportImg';
        if (!file_exists($path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($path);
        }
        
        if ($_FILES['support_custom_img']['size'] > 0) {
            $file_name = jssupportticketphplib::JSST_str_replace(' ', '_', sanitize_file_name($_FILES['support_custom_img']['name']));
            $file_tmp = jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['tmp_name']); // actual location // JSST_sanitizeData() function uses wordpress santize functions

            $userpath = $path;
            $isupload = true;
        }
        if ($isupload) {
            $this->uploadfor = 'supportcustomlogo';
            // Register our path override.
            add_filter( 'upload_dir', array($this,'jssupportticket_upload_custom_logo'));
            // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
            $result = array();
            $file = array(
                'name' => sanitize_file_name($_FILES['support_custom_img']['name']),
                'type' => jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['type']),
                'tmp_name' => jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['tmp_name']),
                'error' => jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['error']),
                'size' => jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['size']),
            ); // JSST_sanitizeData() function uses wordpress santize functions
            $result = wp_handle_upload($file, array('test_form' => false));
            if ( $result && ! isset( $result['error'] ) ) {
                $this->setSupportCustomImage($file_name, $userpath);
            }
            // Set everything back to normal.
            remove_filter( 'upload_dir', array($this,'jssupportticket_upload_custom_logo'));
        }
    }

    function jssupportticket_upload_custom_logo( $dir ) {
        if($this->uploadfor == 'supportcustomlogo'){
            $datadirectory = jssupportticket::$_config['data_directory'];
            $path = $datadirectory . '/supportImg';
            $array = array(
                'path'   => $dir['basedir'] . '/' . $path,
                'url'    => $dir['baseurl'] . '/' . $path,
                'subdir' => '/'. $path,
            ) + $dir;
            return $array;
        }else{
            return $dir;
        }
    }

    function setSupportCustomImage($filename, $userpath){
        $query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'support_custom_img'";
        $key = jssupportticket::$_db->get_var($query);
        if ($key) {
            $unlinkPath = $userpath.'/'.$key;
            if (is_file($unlinkPath)) {
                wp_delete_file($unlinkPath);
            }
        }
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_config', array('configvalue' => $filename), array('configname' => 'support_custom_img'));
    }

    function deleteSupportCustomImage(){

        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-support-customimage') ) {
            die( 'Security check Failed' );
        }
        $maindir = wp_upload_dir();
        $basedir = $maindir['basedir'];
        $datadirectory = jssupportticket::$_config['data_directory'];
        $path = $basedir . '/' . $datadirectory;
        $path = $path . '/supportImg';

        $query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'support_custom_img'";
        $key = jssupportticket::$_db->get_var($query);
        if ($key) {
            $unlinkPath = $path.'/'.$key;
            if (is_file($unlinkPath)) {
                wp_delete_file($unlinkPath);
            }
        }
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_config', array('configvalue' => 0), array('configname' => 'support_custom_img'));
        return 'success';
    }

    function getEmailReadTime() {
        $time = null;
        $query = "SELECT config.configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` AS config WHERE config.configname = 'lastEmailReadingTime'";
        $time = jssupportticket::$_db->get_var($query);
        return $time;
    }

    function setEmailReadTime($time) {
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_config', array('configvalue' => $time), array('configname' => 'lastEmailReadingTime'));
    }

    function getConfiguration() {
        do_action('jssupportticket_load_wp_plugin_file');
        // check for plugin using plugin name
        if (is_plugin_active('js-support-ticket/js-support-ticket.php')) {
            //plugin is activated
            $query = "SELECT config.* FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` AS config WHERE config.configfor != 'ticketviaemail'";
            $config = jssupportticket::$_db->get_results($query);
            foreach ($config as $conf) {
                jssupportticket::$_config[$conf->configname] = $conf->configvalue;
            }
            jssupportticket::$_config['config_count'] = COUNT($config);
        }
    }

    function getCheckCronKey() {
        $query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'ck'";
        $key = jssupportticket::$_db->get_var($query);
        if ($key && $key != '')
            return true;
        else
            return false;
    }

    function genearateCronKey() {
        $key = jssupportticketphplib::JSST_md5(gmdate('Y-m-d'));
        $query = "UPDATE `".jssupportticket::$_db->prefix."js_ticket_config` SET configvalue = '".esc_sql($key)."' WHERE configname = 'ck'" ;
        jssupportticket::$_db->query($query);
        return true;
    }

    function getCronKey($passkey) {
        if ($passkey == jssupportticketphplib::JSST_md5(gmdate('Y-m-d'))) {
            $query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'ck'";
            $key = jssupportticket::$_db->get_var($query);
            return $key;
        }
        else
            return false;
    }

    function getConfigValue($configname){
        $query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = '".esc_sql($configname)."'";
        $configvalue = jssupportticket::$_db->get_var($query);
        return $configvalue;
    }

    function getPageList() {
        $query = "SELECT ID AS id, post_title AS text FROM `" . jssupportticket::$_db->prefix . "posts` WHERE post_type = 'page' AND post_status = 'publish' ";
        $emails = jssupportticket::$_db->get_results($query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $emails;
    }

    function getWooCommerceCategoryList() {
        $orderby = 'term_id';
        $order = 'desc';
        $hide_empty = false ;
        $cat_args = array(
            'orderby'    => $orderby,
            'order'      => $order,
            'hide_empty' => $hide_empty,
        );
        $product_categories = get_terms( 'product_cat', $cat_args );
        $catList = array();
        foreach ($product_categories as $category) {
            $catList[] = (object) array('id' => $category->term_id, 'text' => $category->name);
        }
        return $catList;
    }

    function getConfigurationByConfigName($configname) {
        $query = "SELECT configvalue
                  FROM  `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname ='" . esc_sql($configname) . "'";
        $result = jssupportticket::$_db->get_var($query);
        return $result;
    }
    function getCountConfig() {
        $query = "SELECT COUNT(*)
                  FROM `".jssupportticket::$_db->prefix."js_ticket_config`";
        $result = jssupportticket::$_db->get_var($query);
        return $result;
    }
}

?>
