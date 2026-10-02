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
            // A setting that belongs to a capability now merged into the free
            // core has to stay editable even though no add-on is active — its
            // `addon` column still names the add-on it arrived with.
            // (Roadmap 4.0-CORE-19)
            //
            // The tag is asked of JSSTbundle rather than looked up in the
            // availability array directly, because three add-ons tag their rows
            // with something that is not their slug and two of those write
            // `email` — a string that array cannot be allowed to contain, since
            // getPluginPath() would read it as a js-support-ticket-email plugin
            // carrying core's own mail module. settingsTagActive() knows about
            // the aliases without that side effect. (Roadmap 6.5-ECO-01)
            $jsst_tagged = class_exists('JSSTbundle')
                ? JSSTbundle::settingsTagActive($jsst_config->addon)
                : ($jsst_config->addon == '' || in_array($jsst_config->addon, jssupportticket::$_active_addons));
            if($jsst_tagged || JSSTmergedaddon::isMerged($jsst_config->addon)){
                jssupportticket::$jsst_data[0][$jsst_config->configname] = $jsst_config->configvalue;
            }
        }

        jssupportticket::$jsst_data[1] = JSSTincluder::getJSModel('email')->getAllEmailsForCombobox();
        JSSTincluder::getJSModel('banemaillog')->checkbandata();
        return;
    }

    function getConfigurationByFor($jsst_for) {
		if($jsst_for == 'ticketviaemail'){
			$jsst_query = jssupportticket::$_db->prepare("SELECT COUNT(configname) FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = %s", $jsst_for);
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
				JSSTincluder::getJSModel('banemaillog')->checkbandata();
                return;
			}
		}
        $jsst_query = jssupportticket::$_db->prepare("SELECT configname,configvalue
					FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = %s", $jsst_for);
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        foreach ($jsst_data AS $jsst_config) {
            jssupportticket::$jsst_data[0][$jsst_config->configname] = $jsst_config->configvalue;
        }
        JSSTincluder::getJSModel('banemaillog')->checkbandata();
        return;
    }
    function getCountByConfigFor($jsst_for) {
        if (( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff())) {
            $jsst_query = jssupportticket::$_db->prepare("SELECT COUNT(configvalue)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = %s AND configname LIKE '%%staff' AND configvalue = 1 ", $jsst_for);
        }else{
            $jsst_query = jssupportticket::$_db->prepare("SELECT COUNT(configvalue)
                    FROM `" . jssupportticket::$_db->prefix . "js_ticket_config` WHERE configfor = %s AND configname LIKE '%%user' AND configvalue = 1 ", $jsst_for);
        }
        $jsst_data = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_data;
    }

    function storeDesktopNotificationLogo($jsst_filename) {
        jssupportticket::$_db->query(jssupportticket::$_db->prepare("UPDATE `" . jssupportticket::$_db->prefix . "js_ticket_config` SET configvalue = %s WHERE configname = 'logo_for_desktop_notfication_url' ", $jsst_filename));
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
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
            // The role handed to public registrations is a security control, so
            // an unsafe value is refused here as well as being absent from the
            // select. (Roadmap 4.0-CORE-07)
            if ($jsst_key == 'wp_default_role') {
                if (!JSSTregistrationrole::isSafe($jsst_value)) {
                    JSSTmessage::setMessage(
                        sprintf(
                            /* translators: %s: the role that was rejected */
                            esc_html(__('The registration role %s was not saved: registration is open to the public, so it cannot be a role that administers the site, manages users, publishes content or works tickets.', 'js-support-ticket')),
                            esc_html((string) $jsst_value)
                        ),
                        'error'
                    );
                    continue;
                }
            }

            if ($jsst_key == 'system_slug') {
                if(empty($jsst_value)){
                    JSSTmessage::setMessage(esc_html(__('System slug not be empty.', 'js-support-ticket')), 'error');
                    continue;
                }
                $jsst_value = jssupportticketphplib::JSST_str_replace(' ', '-', $jsst_value);
                $jsst_query = jssupportticket::$_db->prepare('SELECT COUNT(ID) FROM `'.jssupportticket::$_db->prefix.'posts` WHERE post_name = %s', $jsst_value);
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

        if (!current_user_can('manage_options')) {
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'delete-support-customimage')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
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
        $jsst_query = jssupportticket::$_db->prepare("UPDATE `".jssupportticket::$_db->prefix."js_ticket_config` SET configvalue = %s WHERE configname = 'ck'", $jsst_key);
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

    /**
     * One setting, by name.
     *
     * Returns null for a name that has no row, and null is indistinguishable
     * from "switched off" at every call site: `getConfigValue('x') == 1` is
     * false whether the setting is off or the name is a typo. That is not
     * hypothetical - `JSSTcapability` asked for `allowguest` and
     * `JSSTprivacy` for `autocleanup_ticket_days`, neither of which this product
     * has ever written, and both read as a settled "no" for as long as they
     * existed: guests refused by the API on a desk that accepts them, and a
     * published privacy policy saying nothing was deleted while the cleanup cron
     * deleted tickets.
     *
     * So a miss says so, once per name, under WP_DEBUG only. It is deliberately
     * not an exception and not a customer-visible warning: a setting this site
     * genuinely has not got yet is normal on an upgrade path, and the cost of
     * being wrong about that must not be a broken screen. Whoever is developing
     * against this sees the name they got wrong; everybody else sees nothing.
     *
     * It catches the shape above and not the other one - a bare
     * `jssupportticket::$_config['name']` guarded by isset(), which is how the
     * autocleanup pair were read. Nothing here can see those; they are an array
     * subscript. Finding those needs a scan of the source against the table, the
     * way JSSTdocs and JSSThooks read the files, and is worth doing separately.
     * (Roadmap 4.0-CORE-13)
     */
    function getConfigValue($jsst_configname){
        $jsst_query = jssupportticket::$_db->prepare("SELECT configvalue FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = %s", $jsst_configname);
        $jsst_configvalue = jssupportticket::$_db->get_var($jsst_query);
        if ($jsst_configvalue === null && defined('WP_DEBUG') && WP_DEBUG) {
            static $jsst_missing = array();
            if (!isset($jsst_missing[$jsst_configname])) {
                $jsst_missing[$jsst_configname] = true;
                /* A null here does not yet mean the name is wrong, and the
                   first cut of this check assumed it did - then said so about
                   `login_link` and `register_link`, which are real settings
                   that happen to be blank. `$wpdb::get_var()` ends in
                   `'' !== $values[$x] ? $values[$x] : null`, so it hands back
                   null for an empty string exactly as it does for no row at
                   all. Which is worth knowing beyond this notice: it is part of
                   why a name nothing writes is invisible here, because there is
                   no return value that means "there is no such setting".
                   So the row is counted before anything is claimed. The extra
                   query costs nothing worth counting - WP_DEBUG only, once per
                   name per request, and only on the null path. */
                $jsst_exists = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                    "SELECT COUNT(1) FROM `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname = %s",
                    $jsst_configname));
                if (!$jsst_exists) {
                    /* phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- WP_DEBUG only, and the point of it. */
                    error_log(sprintf(
                        'JS Help Desk: getConfigValue("%s") has no row in js_ticket_config, so it reads as empty on every site. If a feature depends on it, check the name.',
                        $jsst_configname
                    ));
                }
            }
        }
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
        // A WP_Error when WooCommerce is not loaded and product_cat is not registered.
        if (is_wp_error($jsst_product_categories)) {
            return $jsst_catList;
        }
        foreach ($jsst_product_categories as $jsst_category) {
            $jsst_catList[] = (object) array('id' => $jsst_category->term_id, 'text' => $jsst_category->name);
        }
        return $jsst_catList;
    }

    function getConfigurationByConfigName($jsst_configname) {
        $jsst_query = jssupportticket::$_db->prepare("SELECT configvalue
                  FROM  `".jssupportticket::$_db->prefix."js_ticket_config` WHERE configname =%s", $jsst_configname);
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_result;
    }

    function getCountConfig() {
        $jsst_query = "SELECT COUNT(*)
                  FROM `".jssupportticket::$_db->prefix."js_ticket_config`";
        $jsst_result = jssupportticket::$_db->get_var($jsst_query);
        return $jsst_result;
    }

    public static function normaliseChoiceValues() {
        $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_config';
        $jsst_rules = array(
            // Enabled / Disabled: 0 -> 2 (Disabled).
            array('from' => '0', 'to' => '2', 'keys' => array(
                'banemail_mail_to_admin', 'new_ticket_mail_to_staff_members',
                'ticket_reassign_staff', 'ticket_close_staff', 'ticket_delete_staff',
                'ticket_mark_overdue_admin', 'ticket_mark_overdue_staff',
                'ticket_mark_overdue_user', 'ticket_ban_email_admin', 'ticket_ban_email_staff',
                'ticket_ban_email_user', 'ticket_department_transfer_staff',
                'ticket_reply_ticket_user_staff', 'ticket_response_to_staff_admin',
                'ticket_response_to_staff_staff', 'ticket_response_to_staff_user',
                'ticker_ban_eamil_and_close_ticktet_admin',
                'ticker_ban_eamil_and_close_ticktet_staff',
                'ticker_ban_eamil_and_close_ticktet_user', 'unban_email_admin',
                'unban_email_staff', 'unban_email_user', 'ticket_lock_admin', 'ticket_lock_staff',
                'ticket_lock_user', 'ticket_unlock_admin', 'ticket_unlock_staff',
                'ticket_unlock_user', 'ticket_mark_progress_admin', 'ticket_mark_progress_staff',
                'ticket_mark_progress_user', 'create_user_via_email'
            )),
            // Show / Hide: 2 -> 0 (Hide).
            array('from' => '2', 'to' => '0', 'keys' => array(
                'cplink_openticket_staff', 'cplink_myticket_staff', 'cplink_addrole_staff',
                'cplink_roles_staff', 'cplink_addstaff_staff', 'cplink_staff_staff',
                'cplink_adddepartment_staff', 'cplink_department_staff',
                'cplink_addcategory_staff', 'cplink_category_staff', 'cplink_addkbarticle_staff',
                'cplink_kbarticle_staff', 'cplink_adddownload_staff', 'cplink_download_staff',
                'cplink_addannouncement_staff', 'cplink_announcement_staff', 'cplink_addfaq_staff',
                'cplink_faq_staff', 'cplink_mail_staff', 'cplink_myprofile_staff',
                'cplink_staff_report_staff', 'cplink_department_report_staff',
                'cplink_login_logout_staff', 'cplink_totalcount_staff', 'cplink_ticketstats_staff',
                'cplink_latesttickets_staff', 'cplink_latestdownloads_staff',
                'cplink_latestannouncements_staff', 'cplink_latestkb_staff',
                'cplink_latestfaqs_staff', 'tplink_home_staff', 'tplink_tickets_staff',
                'cplink_downloads_user', 'cplink_announcements_user', 'cplink_faqs_user',
                'cplink_knowledgebase_user', 'cplink_latestdownloads_user',
                'cplink_latestannouncements_user', 'cplink_latestkb_user',
                'cplink_latestfaqs_user'
            )),
            // Ticket-number padding: '' -> 1 (no padding, as before).
            array('from' => '', 'to' => '1', 'keys' => array('padding_zeros_ticketid')),
        );
        $jsst_changed = 0;
        foreach ($jsst_rules as $jsst_rule) {
            $jsst_in = implode(', ', array_fill(0, count($jsst_rule['keys']), '%s'));
            $jsst_args = array_merge(array($jsst_rule['to'], $jsst_rule['from']), $jsst_rule['keys']);
            $jsst_result = jssupportticket::$_db->query(jssupportticket::$_db->prepare(
                "UPDATE `" . $jsst_table . "` SET `configvalue` = %s WHERE `configvalue` = %s AND `configname` IN (" . $jsst_in . ")",
                $jsst_args
            ));
            if ($jsst_result) {
                $jsst_changed += (int) $jsst_result;
            }
        }
        return $jsst_changed;
    }
}

?>
