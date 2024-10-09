<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class JSSTpremiumpluginController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $module = "premiumplugin";
        if ($this->canAddLayout()) {
            $layout = JSSTrequest::getLayout('jstlay', null, 'step1');
            switch ($layout) {
                case 'admin_step1':
                    jssupportticket::$_data['versioncode'] = JSSTincluder::getJSModel('configuration')->getConfigurationByConfigName('versioncode');
                    jssupportticket::$_data['productcode'] = JSSTincluder::getJSModel('configuration')->getConfigurationByConfigName('productcode');
                    jssupportticket::$_data['producttype'] = JSSTincluder::getJSModel('configuration')->getConfigurationByConfigName('producttype');
                break;
                case 'admin_step2':
                    break;
                case 'admin_step3':
                    break;
                case 'admin_addonfeatures':
                    break;
                case 'admin_missingaddon':
                    break;
                case 'missingaddon':
                    break;
                default:
                    exit;
            }
            $module =  'premiumplugin';
            JSSTincluder::include_file($layout, $module);
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
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'verify-transaction-key') ) {
            die( 'Security check Failed' );
        }
        $post_data['transactionkey'] = JSSTrequest::getVar('transactionkey','','');
        if($post_data['transactionkey'] != ''){


            $post_data['domain'] = site_url();
            $post_data['step'] = 'one';
            $post_data['myown'] = 1;

            $url = 'https://jshelpdesk.com/setup/index.php';

            $response = wp_remote_post( $url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
            if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                $result = $response['body'];
                $result = json_decode($result,true);

            }else{
                $result = false;
                if(!is_wp_error($response)){
                   $error = $response['response']['message'];
               }else{
                    $error = $response->get_error_message();
               }
            }
            if(is_array($result) && isset($result['status']) && $result['status'] == 1 ){ // means everthing ok
                $resultaddon = wp_json_encode($result);
                $resultaddon = jssupportticketphplib::JSST_safe_encoding( $resultaddon );
                // jssupportticketphplib::JSST_setcookie('jsst_addon_install_data' , $resultaddon);
                // jssupportticketphplib::JSST_setcookie('jsst_addon_install_actual_transaction_key' , $post_data['transactionkey']);
                $result['actual_transaction_key'] = $post_data['transactionkey'];
                // in case of session not working
                add_option('jsst_addon_install_data',wp_json_encode($result));
                $url = admin_url("admin.php?page=premiumplugin&jstlay=step2");
                wp_redirect($url);
                return;
            }else{
                if(isset($result[0]) && $result[0] == 0){
                    $error = $result[1];
                }elseif(isset($result['error']) && $result['error'] != ''){
                    $error = $result['error'];
                }
            }
        }else{
            $error = esc_html(__('Please insert activation key to proceed','js-support-ticket')).'!';
        }
        $array['data'] = array();
        $array['status'] = 0;
        $array['message'] = $error;
        $array['transactionkey'] = $post_data['transactionkey'];
        $array = wp_json_encode( $array );
        $array = jssupportticketphplib::JSST_safe_encoding($array);
        jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, COOKIEPATH);
        if ( SITECOOKIEPATH != COOKIEPATH ){
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, SITECOOKIEPATH);
        }
        $url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
        wp_redirect($url);
        return;
    }

    function downloadandinstalladdons(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'download-and-install-addons') ) {
            die( 'Security check Failed' );
        }
        $post_data = JSSTrequest::get('post');

        $addons_array = $post_data;
        if(isset($addons_array['token'])){
            unset($addons_array['token']);
        }
        $addon_json_array = array();

        foreach ($addons_array as $key => $value) {
            $addon_json_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $key);
        }

        $token = $post_data['token'];
        if($token == ''){
            $array['data'] = array();
            $array['status'] = 0;
            $array['message'] = esc_html(__('Addon Installation Failed','js-support-ticket')).'!';
            $array['transactionkey'] = $post_data['transactionkey'];
            $array = wp_json_encode( $array );
            $array = jssupportticketphplib::JSST_safe_encoding($array);
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, SITECOOKIEPATH);
            }
            $url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
            wp_redirect($url);
            exit;
        }
        $site_url = site_url();
		$site_url = jssupportticketphplib::JSST_str_replace("https://","",$site_url);
        $site_url = jssupportticketphplib::JSST_str_replace("http://","",$site_url);
        $url = 'https://jshelpdesk.com/setup/index.php?token='.esc_attr($token).'&productcode='. wp_json_encode($addon_json_array).'&domain='. $site_url;

        $install_count = 0;

        $installed = $this->install_plugin($url);
        if ( !is_wp_error( $installed ) && $installed ) {
            // had to run two seprate loops to save token for all the addons even if some error is triggered by activation.
            foreach ($post_data as $key => $value) {
                if(strstr($key, 'js-support-ticket-')){
                    update_option('transaction_key_for_'.$key,$token);
                }
            }

            foreach ($post_data as $key => $value) {
                if(strstr($key, 'js-support-ticket-')){
                    $activate = activate_plugin( $key.'/'.$key.'.php' );
                    $install_count++;
                }
            }

        }else{
            $array['data'] = array();
            $array['status'] = 0;
            $array['message'] = esc_html(__('Addon Installation Failed','js-support-ticket')).'!';
            $array['transactionkey'] = $post_data['transactionkey'];
            $array = wp_json_encode( $array );
            $array = jssupportticketphplib::JSST_safe_encoding($array);
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, SITECOOKIEPATH);
            }

            $url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
            wp_redirect($url);
            exit;
        }
        $url = admin_url("admin.php?page=premiumplugin&jstlay=step3");
        wp_redirect($url);
    }

    function install_plugin( $plugin_zip ) {

        do_action('jssupportticket_load_wp_admin_file');
        WP_Filesystem();

        $tmpfile = download_url( $plugin_zip);

        if ( !is_wp_error( $tmpfile ) && $tmpfile ) {
            $plugin_path = WP_CONTENT_DIR;
            $plugin_path = $plugin_path.'/plugins/';
            $path = JSST_PLUGIN_PATH.'addon.zip';

            copy( $tmpfile, $path );

            $unzipfile = unzip_file( $path, $plugin_path);

            if ( file_exists( $path ) ) {
                wp_delete_file( $path ); // must unlink afterwards
            }
            if ( file_exists( $tmpfile ) ) {
                wp_delete_file( $tmpfile ); // must unlink afterwards
            }

            if ( is_wp_error( $unzipfile ) ) {
                $array['data'] = array();
                $array['status'] = 0;
                $array['message'] = esc_html(__('Addon installation failed, Directory permission error','js-support-ticket')).'!';
                $array['transactionkey'] = $post_data['transactionkey'];
                $array = wp_json_encode( $array );
                $array = jssupportticketphplib::JSST_safe_encoding($array);
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, COOKIEPATH);
                if ( SITECOOKIEPATH != COOKIEPATH ){
                    jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, SITECOOKIEPATH);
                }

                $url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
                wp_redirect($url);
                exit;
            } else {
                return true;
            }
        }else{
            $array['data'] = array();
            $array['status'] = 0;
            $error_string = $tmpfile->get_error_message();
            $array['message'] = esc_html(__('Addon Installation Failed, File download error','js-support-ticket')).'!'.$error_string;
            $array['transactionkey'] = $post_data['transactionkey'];
            $array = wp_json_encode( $array );
            $array = jssupportticketphplib::JSST_safe_encoding($array);
            jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, COOKIEPATH);
            if ( SITECOOKIEPATH != COOKIEPATH ){
                jssupportticketphplib::JSST_setcookie('jsst_addon_return_data' , $array , 0, SITECOOKIEPATH);
            }
            $url = admin_url("admin.php?page=premiumplugin&jstlay=step1");
            wp_redirect($url);
            exit;
        }
    }
}
$JSSTpremiumpluginController = new JSSTpremiumpluginController();
?>
