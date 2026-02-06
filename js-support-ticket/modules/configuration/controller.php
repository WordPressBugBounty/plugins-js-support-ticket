<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTconfigurationController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'configurations');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_configurations':
                    $jsst_jsstconfigid = JSSTrequest::getVar('jsstconfigid');
                    if (isset($jsst_jsstconfigid)) {
                        jssupportticket::$jsst_data['jsstconfigid'] = $jsst_jsstconfigid;
                    }
                    $jsst_ck = JSSTincluder::getJSModel('configuration')->getCheckCronKey();
                    if ($jsst_ck == false) {
                        JSSTincluder::getJSModel('configuration')->genearateCronKey();
                    }
                    JSSTincluder::getJSModel('configuration')->getConfigurations();
                    break;
                case 'admin_cronjoburl':
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'configuration');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    function canaddfile($jsst_layout) {
        $jsst_nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $jsst_nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if(!is_admin() && jssupportticketphplib::JSST_strpos($jsst_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    static function saveconfiguration() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-configuration') ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('configuration')->storeConfiguration($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=configuration&jstlay=configurations");
        }
        if(isset($jsst_data['call_from']) && $jsst_data['call_from'] == 'notification' && is_admin()){
            $jsst_url = admin_url("admin.php?page=web-notification-setting");    
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    // function to handle auto update configuration
    function saveautoupdateconfiguration() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst_configuration_nonce') ) {
             die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_result = JSSTincluder::getJSModel('configuration')->storeAutoUpdateConfig();
        $jsst_url = esc_url_raw(admin_url("admin.php?page=jssupportticket&jstlay=addonstatus"));
        wp_safe_redirect($jsst_url);
        die();
    }

}

$jsst_configurationController = new JSSTconfigurationController();
?>
