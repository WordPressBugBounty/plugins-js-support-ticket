<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTconfigurationModel {

    function getConfigurations() {
        $jsst_query = "SELECT configname,configvalue,addon
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` ";//WHERE configfor != 'ticketviaemail'";
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);

        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        foreach ($jsst_data AS $jsst_config) {
            if($jsst_config->addon == '' ||  in_array($jsst_config->addon, jssupportticket::$_active_addons)){
                jssupportticket::$jsst_data[0][$jsst_config->configname] = $jsst_config->configvalue;
            }
        }

        jssupportticket::$jsst_data[1] = JSSTincluder::getJSModel('email')->getAllEmailsForCombobox();
        if(in_array('banemail', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('banemaillog')->checkbandata();
        }
        return;
    }

    function getConfigurationByFor($jsst_for) {
		if($jsst_for == 'ticketviaemail'){
			$jsst_query = "SELECT COUNT(configname) FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = '".esc_sql($jsst_for)."'";
			$jsst_count = jssupportticket::$_db->get_var($jsst_query);
			if($jsst_count < 5){
				$jsst_query = "SELECT configname,configvalue
							FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` ";
				$jsst_data = jssupportticket::$_db->get_results($jsst_query);
				if (jssupportticket::$_db->last_error != null) {
					JSSTincluder::getJSModel('systemerror')->addSystemError();
				}
				foreach ($jsst_data AS $jsst_config) {
					jssupportticket::$jsst_data[0][$jsst_config->configname] = $jsst_config->configvalue;
				}
				if(in_array('banemail', jssupportticket::$_active_addons)){
                    JSSTincluder::getJSModel('banemaillog')->checkbandata();
                }
                return;
			}
		}
        $jsst_query = "SELECT configname,configvalue
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = '".esc_sql($jsst_for)."'";
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        foreach ($jsst_data AS $jsst_config) {
            jssupportticket::$jsst_data[0][$jsst_config->configname] = $jsst_config->configvalue;
        }
        if(in_array('banemail', jssupportticket::$_active_addons)){
            JSSTincluder::getJSModel('banemaillog')->checkbandata();
        }
        return;
    }
    function getCountByConfigFor($jsst_for) {
        if (( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())) {
            $jsst_query = "SELECT COUNT(configvalue)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = '".esc_sql($jsst_for). "' AND configname LIKE '%staff' AND configvalue = 1 " ;
        }else{
            $jsst_query = "SELECT COUNT(configvalue)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = '".esc_sql($jsst_for) . "' AND configname LIKE '%user' AND configvalue = 1 " ;
        }
        $jsst_data = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_data;
    }

    function storeDesktopNotificationLogo($jsst_filename) {
        jssupportticket::$_db->query("UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_config` SET configvalue = '" . esc_sql($jsst_filename) . "' WHERE configname = 'logo_for_desktop_notfication_url' ");
    }

    function deleteDesktopNotificationsLogo() {
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];

        $jsst_maindir = wp_upload_dir();
        $jsst_path = $jsst_maindir['basedir'];
        $jsst_path = $jsst_path .'/'.$jsst_datadirectory;

        $jsst_file_name = JSSTincluder::getJSModel('configuration')->getConfigValue('logo_for_desktop_notfication_url');

        $jsst_path = $jsst_path . '/attachmentdata/';
        $jsst_dsk_logo_file =  $jsst_path.$jsst_file_name;
        if($jsst_file_name != ''){
            if ( file_exists( $jsst_dsk_logo_file ) ) {
                wp_delete_file($jsst_dsk_logo_file);
            }
        }
    }


    function storeConfiguration($jsst_data) {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-configuration') ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data); // JSST_sanitizeData() function uses wordpress santize functions
        // handle editor text for offline message after sanitizing all data
        if (isset($jsst_data['offline_message'])) {
            $jsst_data['offline_message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData(wp_unslash($_POST['offline_message'] ?? ''));
            $jsst_data['offline_message'] = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($jsst_data['offline_message']);
            $jsst_data['offline_message'] = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data['offline_message']);
        }
        // handle editor text for new ticket message after sanitizing all data
        if (isset($jsst_data['new_ticket_message'])) {
            $jsst_data['new_ticket_message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData(wp_unslash($_POST['new_ticket_message'] ?? ''));
            $jsst_data['new_ticket_message'] = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($jsst_data['new_ticket_message']);
            $jsst_data['new_ticket_message'] = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data['new_ticket_message']);
        }
        // handle editor text for visitor message after sanitizing all data
        if (isset($jsst_data['visitor_message'])) {
            $jsst_data['visitor_message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData(wp_unslash($_POST['visitor_message'] ?? ''));
            $jsst_data['visitor_message'] = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($jsst_data['visitor_message']);
            $jsst_data['visitor_message'] = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data['visitor_message']);
        }
        // handle editor text for feedback thanks message after sanitizing all data
        if (isset($jsst_data['feedback_thanks_message'])) {
            $jsst_data['feedback_thanks_message'] = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData(wp_unslash($_POST['feedback_thanks_message'] ?? ''));
            $jsst_data['feedback_thanks_message'] = JSSTincluder::getJSModel('jssupportticket')->jsstremovetags($jsst_data['feedback_thanks_message']);
            $jsst_data['feedback_thanks_message'] = JSSTincluder::getJSmodel('jssupportticket')->stripslashesFull($jsst_data['feedback_thanks_message']);
        }
        $jsst_notsave = false;
        $jsst_updateColors = false;
        foreach ($jsst_data AS $jsst_key => $jsst_value) {
            $jsst_query = true;
            
            if ($jsst_key == 'screentag_position') {
                if ($jsst_value != jssupportticket::$_config['screentag_position']) {
                    $jsst_updateColors = true;
                }
            }

            if ($jsst_key == 'pagination_default_page_size') {
                if ($jsst_value < 3) {
                    JSSTmessage::setMessage(esc_html(__('Pagination default page size not saved', 'js-support-ticket')), 'error');
                    continue;
                }
            }

            if($jsst_key == 'del_logo_for_desktop_notfication' && $jsst_value == 1){
                $this->deleteDesktopNotificationsLogo();
                $jsst_key = 'logo_for_desktop_notfication_url';
                $jsst_value = '';
            }


            if ($jsst_key == 'data_directory') {
                $jsst_data_directory = $jsst_value;
                if (empty($jsst_data_directory)) {
                    JSSTmessage::setMessage(esc_html(__('Data directory cannot empty.', 'js-support-ticket')), 'error');
                    continue;
                }
                if (jssupportticketphplib::JSST_strpos($jsst_data_directory, '/') !== false) {
                    JSSTmessage::setMessage(esc_html(__('Data directory is not proper.', 'js-support-ticket')), 'error');
                    continue;
                }

                // --- INITIALIZE WP_FILESYSTEM ---
                global $wp_filesystem;
                if (!function_exists('wp_handle_upload')) {
                    do_action('jssupportticket_load_wp_file');
                }
                if ( ! WP_Filesystem() ) {
                    return false;
                }
                $jsst_wp_filesystem = $wp_filesystem;

                $jsst_path = JSST_PLUGIN_PATH . '/' . $jsst_data_directory;

                // Replaced file_exists() and mkdir() with WP_Filesystem methods
                if (!$jsst_wp_filesystem->exists($jsst_path)) {
                    // 0755 is the standard permission for WordPress directories
                    $jsst_wp_filesystem->mkdir($jsst_path, 0755); 
                }

                // Replaced is_writeable() with $jsst_wp_filesystem->is_writable()
                if (!$jsst_wp_filesystem->is_writable($jsst_path)) {
                    JSSTmessage::setMessage(esc_html(__('Data directory is not writable.', 'js-support-ticket')), 'error');
                    continue;
                }
            }
            if ($jsst_key == 'system_slug') {
                if(empty($jsst_value)){
                    JSSTmessage::setMessage(esc_html(__('System slug not be empty.', 'js-support-ticket')), 'error');
                    continue;
                }
                $jsst_value = jssupportticketphplib::JSST_str_replace(' ', '-', $jsst_value);
                $jsst_query = 'SELECT COUNT(ID) FROM `'.jssupportticket::$_db->prefix.'posts` WHERE post_name = "'.esc_sql($jsst_value).'"';
                $jsst_countslug = jssupportticket::$_db->get_var($jsst_query);
                if($jsst_countslug >= 1){
                    JSSTmessage::setMessage(esc_html(__('System slug is conflicted with post or page slug.', 'js-support-ticket')), 'error');
                    continue;
                }
            }
            jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_config', array('configvalue' => $jsst_value), array('configname' => $jsst_key));
            if (jssupportticket::$_db->last_error != null) {
                JSSTincluder::getJSModel('systemerror')->addSystemError();
                $jsst_notsave = true;
            }
        }
        if ($jsst_notsave == false) {
            JSSTmessage::setMessage(esc_html(__('The configuration has been stored', 'js-support-ticket')), 'updated');
            // if($jsst_data['tve_enabled'] == 1){
            //     //JSSTincluder::getJSController('emailpiping')->registerReadEmails();
            // }
        } else {
            JSSTmessage::setMessage(esc_html(__('The configuration not has been stored', 'js-support-ticket')), 'error');
        }
        if ($jsst_updateColors == true) {
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
        if ( ! WP_Filesystem() ) {
            return false;
        }
        $jsst_maindir = wp_upload_dir();
        $jsst_basedir = $jsst_maindir['basedir'];
        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
        
        $jsst_path = $jsst_basedir . '/' . $jsst_datadirectory;
        if (!file_exists($jsst_path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        }
        $jsst_isupload = false;
        $jsst_path = $jsst_path . '/supportImg';
        if (!file_exists($jsst_path)) { // create user directory
            JSSTincluder::getJSModel('jssupportticket')->makeDir($jsst_path);
        }
        
        if ($_FILES['support_custom_img']['size'] > 0) {
            $jsst_file_name = jssupportticketphplib::JSST_str_replace(' ', '_', sanitize_file_name($_FILES['support_custom_img']['name']));
            $jsst_file_tmp = jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['tmp_name']); // actual location // JSST_sanitizeData() function uses wordpress santize functions

            $jsst_userpath = $jsst_path;
            $jsst_isupload = true;
        }
        if ($jsst_isupload) {
            $this->uploadfor = 'supportcustomlogo';
            // Register our path override.
            add_filter( 'upload_dir', array($this,'jssupportticket_upload_custom_logo'));
            // Do our thing. WordPress will move the file to 'uploads/mycustomdir'.
            $jsst_result = array();
            $jsst_file = array(
                'name' => sanitize_file_name($_FILES['support_custom_img']['name']),
                'type' => jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['type']),
                'tmp_name' => jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['tmp_name']),
                'error' => jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['error']),
                'size' => jssupportticket::JSST_sanitizeData($_FILES['support_custom_img']['size']),
            ); // JSST_sanitizeData() function uses wordpress santize functions
            $jsst_result = wp_handle_upload($jsst_file, array('test_form' => false));
            if ( $jsst_result && ! isset( $jsst_result['error'] ) ) {
                $this->setSupportCustomImage($jsst_file_name, $jsst_userpath);
            }
            // Set everything back to normal.
            remove_filter( 'upload_dir', array($this,'jssupportticket_upload_custom_logo'));
        }
    }

    function jssupportticket_upload_custom_logo( $jsst_dir ) {
        if($this->uploadfor == 'supportcustomlogo'){
            $jsst_datadirectory = jssupportticket::$_config['data_directory'];
            $jsst_path = $jsst_datadirectory . '/supportImg';
            $jsst_array = array(
                'path'   => $jsst_dir['basedir'] . '/' . $jsst_path,
                'url'    => $jsst_dir['baseurl'] . '/' . $jsst_path,
                'subdir' => '/'. $jsst_path,
            ) + $jsst_dir;
            return $jsst_array;
        }else{
            return $jsst_dir;
        }
    }

    function setSupportCustomImage($jsst_filename, $jsst_userpath){
        $jsst_query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'support_custom_img'";
        $jsst_key = jssupportticket::$_db->get_var($jsst_query);
        if ($jsst_key) {
            $jsst_unlinkPath = $jsst_userpath.'/'.$jsst_key;
            if (is_file($jsst_unlinkPath)) {
                wp_delete_file($jsst_unlinkPath);
            }
        }
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_config', array('configvalue' => $jsst_filename), array('configname' => 'support_custom_img'));
    }

    function deleteSupportCustomImage() {

        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'delete-support-customimage')) {
            die('Security check Failed');
        }

        $jsst_maindir = wp_upload_dir();
        $jsst_basedir = trailingslashit($jsst_maindir['basedir']);
        $jsst_datadirectory = isset(jssupportticket::$_config['data_directory']) ? sanitize_text_field(jssupportticket::$_config['data_directory']) : '';
        $jsst_path = $jsst_basedir . trailingslashit($jsst_datadirectory) . 'supportImg/';

        $jsst_query = "SELECT configvalue FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configname = 'support_custom_img'";
        $jsst_key = jssupportticket::$_db->get_var($jsst_query);

        if ($jsst_key) {
            $jsst_key = sanitize_file_name($jsst_key); // Sanitize filename
            $jsst_unlinkPath = realpath($jsst_path . $jsst_key); // Get absolute path

            // Ensure the file is within the allowed directory
            if ($jsst_unlinkPath && jssupportticketphplib::JSST_strpos($jsst_unlinkPath, realpath($jsst_path)) === 0 && is_file($jsst_unlinkPath)) {
                wp_delete_file($jsst_unlinkPath);
            }
        }

        // Update database to remove reference
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_config', array('configvalue' => 0), array('configname' => 'support_custom_img'));

        return 'success';
    }

    function getEmailReadTime() {
        $jsst_time = null;
        $jsst_query = "SELECT config.configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` AS config WHERE config.configname = 'lastEmailReadingTime'";
        $jsst_time = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_time;
    }

    function setEmailReadTime($jsst_time) {
        jssupportticket::$_db->update(jssupportticket::$_db->prefix . 'js_ticket_config', array('configvalue' => $jsst_time), array('configname' => 'lastEmailReadingTime'));
    }

    function getConfiguration() {
        do_action('jssupportticket_load_wp_plugin_file');
        // check for plugin using plugin name
        if (is_plugin_active('js-support-ticket/js-support-ticket.php')) {
            //plugin is activated
            $jsst_query = "SELECT config.* FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` AS config WHERE config.configfor != 'ticketviaemail'";
            $jsst_config = jssupportticket::$_db->get_results($jsst_query);
            foreach ($jsst_config as $jsst_conf) {
                jssupportticket::$_config[$jsst_conf->configname] = $jsst_conf->configvalue;
            }
            jssupportticket::$_config['config_count'] = COUNT($jsst_config);
        }
    }

    function getCheckCronKey() {
        $jsst_query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'ck'";
        $jsst_key = jssupportticket::$_db->get_var($jsst_query);
        if ($jsst_key && $jsst_key != '')
            return true;
        else
            return false;
    }

    function genearateCronKey() {
        $jsst_key = jssupportticketphplib::JSST_md5(gmdate('Y-m-d'));
        $jsst_query = "UPDATE `".jssupportticket::$_db->prefix."js_ticket_config` SET configvalue = '".esc_sql($jsst_key)."' WHERE configname = 'ck'" ;
        jssupportticket::$_db->query($jsst_query);
        return true;
    }

    function getCronKey($jsst_passkey) {
        if ($jsst_passkey == jssupportticketphplib::JSST_md5(gmdate('Y-m-d'))) {
            $jsst_query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = 'ck'";
            $jsst_key = jssupportticket::$_db->get_var($jsst_query);
            return $jsst_key;
        }
        else
            return false;
    }

    function getConfigValue($jsst_configname){
        $jsst_query = "SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = '".esc_sql($jsst_configname)."'";
        $jsst_configvalue = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_configvalue;
    }

    function getPageList() {
        $jsst_query = "SELECT ID AS id, post_title AS text FROM `" . jssupportticket::$_db->prefix . "posts` WHERE post_type = 'page' AND post_status = 'publish' ";
        $jsst_emails = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_emails;
    }

    function getWooCommerceCategoryList() {
        $jsst_orderby = 'term_id';
        $jsst_order = 'desc';
        $jsst_hide_empty = false ;
        $jsst_cat_args = array(
            'orderby'    => $jsst_orderby,
            'order'      => $jsst_order,
            'hide_empty' => $jsst_hide_empty,
        );
        $jsst_cat_args['taxonomy'] = 'product_cat';
        $jsst_product_categories = get_terms( $jsst_cat_args );
        $jsst_catList = array();
        foreach ($jsst_product_categories as $jsst_category) {
            $jsst_catList[] = (object) array('id' => $jsst_category->term_id, 'text' => $jsst_category->name);
        }
        return $jsst_catList;
    }

    function getConfigurationByConfigName($jsst_configname) {
        $jsst_query = "SELECT configvalue
                  FROM  `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname ='" . esc_sql($jsst_configname) . "'";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_result;
    }

    function getCountConfig() {
        $jsst_query = "SELECT COUNT(*)
                  FROM `".jssupportticket::$_db->prefix."js_ticket_config`";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_result;
    }

    function storeAutoUpdateConfig() {

        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_configvalue = JSSTrequest::getVar('jsst_addons_auto_update','','');

        if (!is_numeric($jsst_configvalue)) { //can only have numric value
            return false;
        }

        $jsst_error = false;
        $jsst_query = "UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_config` SET `configvalue` = ".esc_sql($jsst_configvalue)." WHERE `configname`= 'jsst_addons_auto_update'";
        if (false === jssupportticket::$_db->query($jsst_query)) {
            $jsst_error = true;
        }

        if ($jsst_error) {
            JSSTmessage::setMessage(esc_html(__('Something went wrong!', 'js-support-ticket')), 'error');
            return WPJOBPORTAL_SAVE_ERROR;
        } else {
            JSSTmessage::setMessage(esc_html(__('The setting has been stored.', 'js-support-ticket')), 'updated');
        }
        return;
    }
}

?>
