<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpremiumpluginModel {

    private static $jsst_server_url = 'https://jshelpdesk.com/setup/index.php';

    function verfifyAddonActivation($jsst_addon_name){
        $jsst_option_name = 'transaction_key_for_js-support-ticket-'.$jsst_addon_name;
        $jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);
        try {
            if (! $jsst_transaction_key ) {
                throw new Exception( 'License key not found' );
            }
            if ( empty( $jsst_transaction_key ) ) {
                throw new Exception( 'License key not found' );
            }
            $jsst_activate_results = $this->activate( array(
                'token'    => $jsst_transaction_key,
                'plugin_slug'    => $jsst_addon_name
            ) );
            if ( false === $jsst_activate_results ) {
                throw new Exception( 'Connection failed to the server' );
            } elseif ( isset( $jsst_activate_results['error_code'] ) ) {
                throw new Exception( $jsst_activate_results['error'] );
            } elseif(isset($jsst_activate_results['verfication_status']) && $jsst_activate_results['verfication_status'] == 1 ){
                return true;
            }
            throw new Exception( 'License could not activate. Please contact support.' );
        } catch ( Exception $jsst_e ) {
            echo '<div class="notice notice-error is-dismissible">
                    <p>'.wp_kses_post($jsst_e->getMessage()).'.</p>
                </div>';
            return false;
        }
    }

    function logAddonDeactivation($jsst_addon_name){
        $jsst_option_name = 'transaction_key_for_js-support-ticket-'.$jsst_addon_name;
        $jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);

        $jsst_activate_results = $this->deactivate( array(
            'token'    => $jsst_transaction_key,
            'plugin_slug'    => $jsst_addon_name
        ) );
    }

    function logAddonDeletion($jsst_addon_name){
        $jsst_option_name = 'transaction_key_for_js-support-ticket-'.$jsst_addon_name;
        $jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);
        $jsst_activate_results = $this->delete( array(
            'token'    => $jsst_transaction_key,
            'plugin_slug'    => $jsst_addon_name
        ) );
    }

    public static function activate( $jsst_args ) {
        $jsst_site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        $jsst_defaults = array(
            'request'  => 'activate',
            'domain' => $jsst_site_url,
            'activation_call' => 1
        );

        $jsst_args    = wp_parse_args( $jsst_defaults, $jsst_args );
        $jsst_request = wp_remote_get( self::$jsst_server_url . '?' . http_build_query( $jsst_args, '', '&' ) );

        if ( is_wp_error( $jsst_request ) ) {
            return wp_json_encode( array( 'error_code' => $jsst_request->get_error_code(), 'error' => $jsst_request->get_error_message() ) );
        }

        if ( wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $jsst_request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $jsst_request ) ) );
        }
        $jsst_response =  wp_remote_retrieve_body( $jsst_request );
        $jsst_response = json_decode($jsst_response,true);
        return $jsst_response;
    }

    /**
     * Attempt t deactivate a license
     */
    public static function deactivate( $jsst_dargs ) {
        $jsst_site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        $jsst_defaults = array(
            'request'  => 'deactivate',
            'domain' => $jsst_site_url
        );

        $jsst_args    = wp_parse_args( $jsst_defaults, $jsst_dargs );
        $jsst_request = wp_remote_get( self::$jsst_server_url . '?' . http_build_query( $jsst_args, '', '&' ) );
        if ( is_wp_error( $jsst_request ) || wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
            return false;
        } else {
            return wp_remote_retrieve_body( $jsst_request );
        }
    }
    /**
     * Attempt t deactivate a license
     */
    public static function delete( $jsst_args ) {
        $jsst_site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        $jsst_defaults = array(
            'request'  => 'delete',
            'domain' => $jsst_site_url,
        );

        $jsst_args    = wp_parse_args( $jsst_defaults, $jsst_args );
        $jsst_request = wp_remote_get( self::$jsst_server_url . '?' . http_build_query( $jsst_args, '', '&' ) );
        if ( is_wp_error( $jsst_request ) || wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
            return false;
        } else {
            return;
        }
    }

    function verifyAddonSqlFile($jsst_addon_name,$jsst_addon_version){
        $jsst_option_name = 'transaction_key_for_js-support-ticket-'.$jsst_addon_name;
        $jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);
        $jsst_network_site_url = JSSTincluder::getJSModel('jssupportticket')->getNetworkSiteUrl();
        $jsst_site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        // $jsst_addonversion = jssupportticketphplib::JSST_str_replace('.', '', $jsst_addon_version);
        $jsst_defaults = array(
            'request'  => 'getactivatesql',
            'domain' => $jsst_network_site_url,
            'subsite' => $jsst_site_url,
            'activation_call' => 1,
            'plugin_slug' => $jsst_addon_name,
            'addonversion' => $jsst_addon_version,
            'token' => $jsst_transaction_key
        );
        $jsst_request = wp_remote_get( self::$jsst_server_url . '?' . http_build_query( $jsst_defaults, '', '&' ) );
        if ( is_wp_error( $jsst_request ) ) {
            return wp_json_encode( array( 'error_code' => $jsst_request->get_error_code(), 'error' => $jsst_request->get_error_message() ) );
        }

        if ( wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $jsst_request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $jsst_request ) ) );
        }

        $jsst_response =  wp_remote_retrieve_body( $jsst_request );
        return $jsst_response;
    }

    function getAddonSqlForUpdation($jsst_plugin_slug,$jsst_installed_version,$jsst_new_version){
        $jsst_option_name = 'transaction_key_for_js-support-ticket-'.$jsst_plugin_slug;
        $jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);
        $jsst_network_site_url = JSSTincluder::getJSModel('jssupportticket')->getNetworkSiteUrl();
        $jsst_site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
        $jsst_defaults = array(
            'request'  => 'getupdatesql',
            'domain' => $jsst_network_site_url,
            'subsite' => $jsst_site_url,
            'activation_call' => 1,
            'plugin_slug' => $jsst_plugin_slug,
            'installedversion' => $jsst_installed_version,
            'newversion' => $jsst_new_version,
            'token' => $jsst_transaction_key
        );

        $jsst_request = wp_remote_get( self::$jsst_server_url . '?' . http_build_query( $jsst_defaults, '', '&' ) );
        if ( is_wp_error( $jsst_request ) ) {
            return wp_json_encode( array( 'error_code' => $jsst_request->get_error_code(), 'error' => $jsst_request->get_error_message() ) );
        }

        if ( wp_remote_retrieve_response_code( $jsst_request ) != 200 ) {
            return wp_json_encode( array( 'error_code' => wp_remote_retrieve_response_code( $jsst_request ), 'error' => 'Error code: ' . wp_remote_retrieve_response_code( $jsst_request ) ) );
        }

        $jsst_response =  wp_remote_retrieve_body( $jsst_request );
        return $jsst_response;
    }

    function getAddonUpdateSqlFromUpdateDir($jsst_installedversion, $jsst_newversion, $jsst_directory) {
        if ($jsst_installedversion != "" && $jsst_newversion != "") {

            // --- INITIALIZE WP_FILESYSTEM ---
            global $wp_filesystem;
            if (!function_exists('wp_handle_upload')) {
                do_action('jssupportticket_load_wp_file');
            }
            if ( ! WP_Filesystem() ) {
                return false;
            }
            $jsst_wp_filesystem = $wp_filesystem;

            for ($jsst_i = ($jsst_installedversion + 1); $jsst_i <= $jsst_newversion; $jsst_i++) {
                $jsst_installfile = $jsst_directory . '/' . $jsst_i . '.sql';

                // Replaced file_exists with $jsst_wp_filesystem->exists
                if ($jsst_wp_filesystem->exists($jsst_installfile)) {
                    
                    // Replaced fopen/fgets/fclose with $jsst_wp_filesystem->get_contents
                    $jsst_file_content = $jsst_wp_filesystem->get_contents($jsst_installfile);

                    if (!empty($jsst_file_content)) {
                        // Split the SQL file into individual queries by semicolon
                        // This handles multi-line queries effectively
                        $jsst_queries = preg_split("/;(?=\s*$|[\r\n])/m", $jsst_file_content);

                        foreach ($jsst_queries as $jsst_query) {
                            $jsst_query = jssupportticketphplib::JSST_trim($jsst_query);
                            
                            // Replace the table prefix placeholder
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

    function getAddonUpdateSqlFromLive($jsst_installedversion,$jsst_newversion,$jsst_plugin_slug){
        if($jsst_installedversion != "" && $jsst_newversion != "" && $jsst_plugin_slug != ""){
            $jsst_addonsql = $this->getAddonSqlForUpdation($jsst_plugin_slug,$jsst_installedversion,$jsst_newversion);
            $jsst_decodedata = json_decode($jsst_addonsql,true);
            $jsst_delimiter = ';';
            if(isset($jsst_decodedata['verfication_status']) && $jsst_decodedata['update_sql'] != ""){
                $jsst_lines = jssupportticketphplib::JSST_explode(PHP_EOL, $jsst_addonsql);
                if(!empty($jsst_lines)){
                    foreach($jsst_lines as $jsst_line){
                        $jsst_query[] = $jsst_line;
                        if (preg_match('~' . preg_quote($jsst_delimiter, '~') . '\s*$~iS', end($jsst_query)) === 1) {
                            $jsst_query = jssupportticketphplib::JSST_trim(implode('', $jsst_query));
                            $jsst_query = jssupportticketphplib::JSST_str_replace("#__", jssupportticket::$_db->prefix, $jsst_query);
                            if (!empty($jsst_query)) {
                                jssupportticket::$_db->query($jsst_query);
                            }
                        }
                        if (is_string($jsst_query) === true) {
                            $jsst_query = array();
                        }
                    }
                }
            }
        }
    }

    function jssupportticket_count_unused_keys() {
        // Get all transaction keys
        $jsst_query = "
            SELECT option_name, option_value 
            FROM `" . jssupportticket::$_db->prefix . "options`
            WHERE option_name LIKE 'transaction_key_for_js-support-ticket%'
        ";
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_results)) {
            return 0;
        }

        $jsst_unused = [];

        foreach ($jsst_results as $jsst_row) {
            $jsst_addon_slug = str_replace('transaction_key_for_', '', $jsst_row->option_name);

            // 🔹 Replace this with your own addon check
            $jsst_is_installed = apply_filters(
                'jssupportticket_is_addon_installed',
                file_exists(WP_PLUGIN_DIR . '/' . $jsst_addon_slug)
            );

            if (!$jsst_is_installed && !empty($jsst_row->option_value)) {
                $jsst_unused[] = $jsst_row->option_value;
            }
        }

        return count(array_unique($jsst_unused));
    }

    function jssupportticket_remove_unused_keys() {
        $jsst_query = "
            SELECT option_name, option_value 
            FROM `" . jssupportticket::$_db->prefix . "options`
            WHERE option_name LIKE 'transaction_key_for_js-support-ticket%'
        ";
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);

        if (empty($jsst_results)) {
            return 0;
        }

        $jsst_deleted_keys = array();

        foreach ($jsst_results as $jsst_row) {
            $jsst_addon_slug = str_replace('transaction_key_for_', '', $jsst_row->option_name);

            // Replace with your own addon check
            $jsst_is_installed = apply_filters(
                'jssupportticket_is_addon_installed',
                file_exists(WP_PLUGIN_DIR . '/' . $jsst_addon_slug)
            );

            if (!$jsst_is_installed) {
                if (delete_option($jsst_row->option_name)) {
                    $jsst_deleted_keys[$jsst_row->option_value] = true; // track by key, not slug
                }
            }
        }

        return count($jsst_deleted_keys); // unique keys removed
    }
}

?>
