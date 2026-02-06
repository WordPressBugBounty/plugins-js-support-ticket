<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class JSSTpremiumpluginController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_module = "premiumplugin";
        if ($this->canAddLayout()) {
            $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'step1');
            JSSTincluder::getJSModel('jssupportticket')->jsst_check_license_status();
            switch ($jsst_layout) {
                case 'admin_step1':
                    jssupportticket::$jsst_data['versioncode'] = JSSTincluder::getJSModel('configuration')->getConfigurationByConfigName('versioncode');
                    jssupportticket::$jsst_data['productcode'] = JSSTincluder::getJSModel('configuration')->getConfigurationByConfigName('productcode');
                    jssupportticket::$jsst_data['producttype'] = JSSTincluder::getJSModel('configuration')->getConfigurationByConfigName('producttype');
                break;
                case 'admin_step2':
                    break;
                case 'admin_step3':
                    break;
                case 'admin_addonfeatures':
                    break;
                case 'admin_updatekey':
                    jssupportticket::$jsst_data['token'] = JSSTrequest::getVar('token');
                    jssupportticket::$jsst_data['extra_addons'] = JSSTrequest::getVar('extraaddons');
                    jssupportticket::$jsst_data['allowed_addons'] = JSSTrequest::getVar('allowedaddons');
                    jssupportticket::$jsst_data['unused_keys'] = JSSTincluder::getJSModel('premiumplugin')->jssupportticket_count_unused_keys();
                    break;
                case 'admin_missingaddon':
                    break;
                case 'missingaddon':
                    break;
                default:
                    exit;
            }
            $jsst_module =  'premiumplugin';
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    function canAddLayout() {
        if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket')
            return false;
        elseif (isset($_GET['action']) && $_GET['action'] == 'jstask')
            return false;
        else
            return true;
    }

    function verifytransactionkey(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'verify-transaction-key') ) {
            die( 'Security check Failed' );
        }
        $jsst_post_data['transactionkey'] = JSSTrequest::getVar('transactionkey','','');
        if($jsst_post_data['transactionkey'] != ''){


            $jsst_post_data['domain'] = site_url();
            $jsst_post_data['step'] = 'one';
            $jsst_post_data['myown'] = 1;

            $jsst_url = 'https://jshelpdesk.com/setup/index.php';

            $jsst_response = wp_remote_post( $jsst_url, array('body' => $jsst_post_data,'timeout'=>7,'sslverify'=>false));
            if( !is_wp_error($jsst_response) && $jsst_response['response']['code'] == 200 && isset($jsst_response['body']) ){
                $jsst_result = $jsst_response['body'];
                $jsst_result = json_decode($jsst_result,true);

            }else{
                $jsst_result = false;
                if(!is_wp_error($jsst_response)){
                   $jsst_error = $jsst_response['response']['message'];
               }else{
                    $jsst_error = $jsst_response->get_error_message();
               }
            }
            if(is_array($jsst_result) && isset($jsst_result['status']) && $jsst_result['status'] == 1 ){ // means everthing ok
                $jsst_resultaddon = wp_json_encode($jsst_result);
                $jsst_resultaddon = jssupportticketphplib::JSST_safe_encoding( $jsst_resultaddon );
                // jssupportticketphplib::JSST_setcookie('jsst_addon_install_data' , $jsst_resultaddon);
                // jssupportticketphplib::JSST_setcookie('jsst_addon_install_actual_transaction_key' , $jsst_post_data['transactionkey']);
                $jsst_result['actual_transaction_key'] = $jsst_post_data['transactionkey'];
                // in case of session not working
                add_option('jsst_addon_install_data',wp_json_encode($jsst_result));
                $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=step2");
                wp_safe_redirect($jsst_url);
                return;
            }else{
                if(isset($jsst_result[0]) && $jsst_result[0] == 0){
                    $jsst_error = $jsst_result[1];
                }elseif(isset($jsst_result['error']) && $jsst_result['error'] != ''){
                    $jsst_error = $jsst_result['error'];
                }
            }
        }else{
            $jsst_error = esc_html(__('Please insert activation key to proceed','js-support-ticket')).'!';
        }
        $jsst_array['data'] = array();
        $jsst_array['status'] = 0;
        $jsst_array['message'] = $jsst_error;
        $jsst_array['transactionkey'] = $jsst_post_data['transactionkey'];
        $jsst_array = wp_json_encode( $jsst_array );
        $jsst_array = jssupportticketphplib::JSST_safe_encoding($jsst_array);
        jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, COOKIEPATH);
        if ( SITECOOKIEPATH != COOKIEPATH ){
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, SITECOOKIEPATH);
        }
        $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
        wp_safe_redirect($jsst_url);
        return;
    }

    function updatetransactionkey(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'update-transaction-key') ) {
            die( 'Security check Failed' );
        }

        $jsst_post_data = JSSTrequest::get('post');
        $jsst_addons_array = $jsst_post_data;
        if(isset($jsst_addons_array['transactionkey'])){
            unset($jsst_addons_array['transactionkey']);
        }
        $jsst_addon_json_array = array();
        $jsst_addon_name = '';
        foreach ($jsst_addons_array as $jsst_key => $jsst_value) {
            $jsst_addon_json_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_key);
            $jsst_addon_name = $jsst_key;
        }

        if (empty($jsst_addon_json_array)) {
            JSSTmessage::setMessage(esc_html(__("Please select at least one addon!", "js-support-ticket")),'error');
            $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=updatekey");
            wp_safe_redirect($jsst_url);
            return;
        }

        $jsst_token = $jsst_post_data['transactionkey'];
        if($jsst_token != ''){
            $jsst_site_url = JSSTincluder::getJSModel('jssupportticket')->getSiteUrl();
            $jsst_post_data['transactionkey'] = $jsst_token;
            $jsst_post_data['domain'] = $jsst_site_url;
            $jsst_post_data['step'] = 'one';
            $jsst_post_data['myown'] = 1;

            $jsst_url = 'https://jshelpdesk.com/setup/index.php';

            $jsst_response = wp_remote_post( $jsst_url, array('body' => $jsst_post_data,'timeout'=>7,'sslverify'=>false));
            if( !is_wp_error($jsst_response) && $jsst_response['response']['code'] == 200 && isset($jsst_response['body']) ){
                $jsst_result = $jsst_response['body'];
                $jsst_result = json_decode($jsst_result,true);
            } else {
                $jsst_result = false;
                if(!is_wp_error($jsst_response)){
                   $jsst_error = $jsst_response['response']['message'];
                }else{
                    $jsst_error = $jsst_response->get_error_message();
                }
            }
            if(is_array($jsst_result) && isset($jsst_result['status']) && $jsst_result['status'] == 1 ){ // means everthing ok
                $jsst_extra_addons = [];
                $jsst_allowed_addons = [];
                foreach ($jsst_addons_array as $jsst_key => $jsst_value) {
                    if (!array_key_exists($jsst_key, $jsst_result['data'])) {
                        $jsst_extra_addons[] = $jsst_key;
                    } else {
                        $jsst_allowed_addons[] = $jsst_key;
                    }
                }
                if (!empty($jsst_extra_addons)) {
                    $jsst_extraaddons = wp_json_encode($jsst_extra_addons);
                    $jsst_allowedaddons = wp_json_encode($jsst_allowed_addons);
                    $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=updatekey&token=".$jsst_token."&extraaddons=".$jsst_extraaddons."&allowedaddons=".$jsst_allowedaddons);
                    wp_safe_redirect($jsst_url);
                    return;
                }

                require_once JSST_PLUGIN_PATH.'includes/addon-updater/jsstupdater.php';
                $jsst_JS_SUPPORTTICKETUpdater  = new JS_SUPPORTTICKETUpdater();
                $jsst_token_key = $jsst_JS_SUPPORTTICKETUpdater->jsstGetTokenFromTransactionKey( $jsst_token,$jsst_addon_name);

                $jsst_url = 'https://jshelpdesk.com/setup/index.php?token='.esc_attr($jsst_token_key).'&productcode='. wp_json_encode($jsst_addon_json_array).'&domain='. $jsst_site_url;
                $jsst_verifytransactionkey = JSSTincluder::getJSModel('jssupportticket')->verifytransactionkey($jsst_token_key, $jsst_url);
                if($jsst_verifytransactionkey['status'] == 0){
                    JSSTmessage::setMessage(esc_html($jsst_verifytransactionkey['message']),'error');
                    $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=updatekey");
                    wp_safe_redirect($jsst_url);
                    return;
                }
                $jsst_install_count = 0;

                $jsst_installed = $this->install_plugin($jsst_url);
                if ( !is_wp_error( $jsst_installed ) && $jsst_installed ) {
                    // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.
                    foreach ($jsst_post_data as $jsst_key => $jsst_value) {
                        if(strstr($jsst_key, 'js-support-ticket-')){
                            update_option('transaction_key_for_'.$jsst_key,$jsst_token_key);
                        }
                    }

                    foreach ($jsst_post_data as $jsst_key => $jsst_value) {
                        if(strstr($jsst_key, 'js-support-ticket-')){
                            $jsst_activate = activate_plugin( $jsst_key.'/'.$jsst_key.'.php' );
                            $jsst_install_count++;
                        }
                    }
                    JSSTmessage::setMessage(esc_html(__('Addon(s) Installed successfully!', 'js-support-ticket')),'updated');
                }else{
                    JSSTmessage::setMessage(esc_html(__('Addon(s) Installation Failed', 'js-support-ticket')),'error');
                }
            }else{
                if(isset($jsst_result[0]) && $jsst_result[0] == 0){
                    $jsst_error = $jsst_result[1];
                }elseif(isset($jsst_result['error']) && $jsst_result['error'] != ''){
                    $jsst_error = $jsst_result['error'];
                }
                JSSTmessage::setMessage(esc_html($jsst_error),'error');
            }
        }else{
            JSSTmessage::setMessage(esc_html(__('Please insert activation key to proceed', 'js-support-ticket')),'error');
        }
        $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=updatekey");
        wp_safe_redirect($jsst_url);
        return;
    }

    function jssupportticket_remove_unused_keys() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-transaction-key') ) {
            die( 'Security check Failed' );
        }

        $jsst_deleted = JSSTincluder::getJSModel('premiumplugin')->jssupportticket_remove_unused_keys();

        if ($jsst_deleted === 0) {
            JSSTmessage::setMessage(esc_html(__('No unused keys were found.', 'js-support-ticket')),'error');
        } elseif ($jsst_deleted === 1) {
            JSSTmessage::setMessage(esc_html($jsst_deleted . ' ' . __('unused key has been deleted successfully!', 'js-support-ticket')),'updated');
        } else {
            JSSTmessage::setMessage(esc_html($jsst_deleted . ' ' . __('unused keys have been deleted successfully!', 'js-support-ticket')),'updated');
        }
        $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=updatekey");
        wp_safe_redirect($jsst_url);
        return;
    }

    function downloadandinstalladdons(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'download-and-install-addons') ) {
            die( 'Security check Failed' );
        }
        $jsst_post_data = JSSTrequest::get('post');

        $jsst_addons_array = $jsst_post_data;
        if(isset($jsst_addons_array['token'])){
            unset($jsst_addons_array['token']);
        }
        $jsst_addon_json_array = array();

        foreach ($jsst_addons_array as $jsst_key => $jsst_value) {
            $jsst_addon_json_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_key);
        }

        $jsst_token = $jsst_post_data['token'];
        if($jsst_token == ''){
            $jsst_array['data'] = array();
            $jsst_array['status'] = 0;
            $jsst_array['message'] = esc_html(__('Addon Installation Failed','js-support-ticket')).'!';
            $jsst_array['transactionkey'] = $jsst_post_data['transactionkey'];
            $jsst_array = wp_json_encode( $jsst_array );
            $jsst_array = jssupportticketphplib::JSST_safe_encoding($jsst_array);
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, SITECOOKIEPATH);
            }
            $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
            wp_safe_redirect($jsst_url);
            exit;
        }
        $jsst_site_url = site_url();
		$jsst_site_url = jssupportticketphplib::JSST_str_replace("https://","",$jsst_site_url);
        $jsst_site_url = jssupportticketphplib::JSST_str_replace("http://","",$jsst_site_url);
        $jsst_url = 'https://jshelpdesk.com/setup/index.php?token='.esc_attr($jsst_token).'&productcode='. wp_json_encode($jsst_addon_json_array).'&domain='. $jsst_site_url;

        $jsst_install_count = 0;

        $jsst_installed = $this->install_plugin($jsst_url);
        if ( !is_wp_error( $jsst_installed ) && $jsst_installed ) {
            // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.
            foreach ($jsst_post_data as $jsst_key => $jsst_value) {
                if(strstr($jsst_key, 'js-support-ticket-')){
                    update_option('transaction_key_for_'.$jsst_key,$jsst_token);
                }
            }

            foreach ($jsst_post_data as $jsst_key => $jsst_value) {
                if(strstr($jsst_key, 'js-support-ticket-')){
                    $jsst_activate = activate_plugin( $jsst_key.'/'.$jsst_key.'.php' );
                    $jsst_install_count++;
                }
            }

        }else{
            $jsst_array['data'] = array();
            $jsst_array['status'] = 0;
            $jsst_array['message'] = esc_html(__('Addon Installation Failed','js-support-ticket')).'!';
            $jsst_array['transactionkey'] = $jsst_post_data['transactionkey'];
            $jsst_array = wp_json_encode( $jsst_array );
            $jsst_array = jssupportticketphplib::JSST_safe_encoding($jsst_array);
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, SITECOOKIEPATH);
            }

            $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
            wp_safe_redirect($jsst_url);
            exit;
        }
        $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=step3");
        wp_safe_redirect($jsst_url);
    }

    function install_plugin( $jsst_plugin_zip ) {

        do_action('jssupportticket_load_wp_admin_file');
        WP_Filesystem();

        $jsst_tmpfile = download_url( $jsst_plugin_zip);

        if ( !is_wp_error( $jsst_tmpfile ) && $jsst_tmpfile ) {
            $jsst_plugin_path = WP_CONTENT_DIR;
            $jsst_plugin_path = $jsst_plugin_path.'/plugins/';
            $jsst_path = JSST_PLUGIN_PATH.'addon.zip';

            copy( $jsst_tmpfile, $jsst_path );

            $jsst_unzipfile = unzip_file( $jsst_path, $jsst_plugin_path);

            if ( file_exists( $jsst_path ) ) {
                wp_delete_file( $jsst_path ); // must unlink afterwards
            }
            if ( file_exists( $jsst_tmpfile ) ) {
                wp_delete_file( $jsst_tmpfile ); // must unlink afterwards
            }

            if ( is_wp_error( $jsst_unzipfile ) ) {
                $jsst_array['data'] = array();
                $jsst_array['status'] = 0;
                $jsst_array['message'] = esc_html(__('Addon installation failed, Directory permission error','js-support-ticket')).'!';
                $jsst_array['transactionkey'] = $jsst_post_data['transactionkey'];
                $jsst_array = wp_json_encode( $jsst_array );
                $jsst_array = jssupportticketphplib::JSST_safe_encoding($jsst_array);
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, COOKIEPATH);
                if ( SITECOOKIEPATH != COOKIEPATH ){
                    jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, SITECOOKIEPATH);
                }

                $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
                wp_safe_redirect($jsst_url);
                exit;
            } else {
                return true;
            }
        }else{
            $jsst_array['data'] = array();
            $jsst_array['status'] = 0;
            $jsst_error_string = $jsst_tmpfile->get_error_message();
            $jsst_array['message'] = esc_html(__('Addon Installation Failed, File download error','js-support-ticket')).'! '.$jsst_error_string;
            $jsst_array['transactionkey'] = $jsst_post_data['transactionkey'];
            $jsst_array = wp_json_encode( $jsst_array );
            $jsst_array = jssupportticketphplib::JSST_safe_encoding($jsst_array);
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $jsst_array , 0, SITECOOKIEPATH);
            }
            $jsst_url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
            wp_safe_redirect($jsst_url);
            exit;
        }
    }
}
$JSSTpremiumpluginController = new JSSTpremiumpluginController();
?>
