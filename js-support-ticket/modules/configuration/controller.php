<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTconfigurationController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'configurations');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_configurations':
                    $jsstconfigid = JSSTrequest::getVar('jsstconfigid');
                    if (isset($jsstconfigid)) {
                        jssupportticket::$_data['jsstconfigid'] = $jsstconfigid;
                    }
                    $ck = JSSTincluder::getJSModel('configuration')->getCheckCronKey();
                    if ($ck == false) {
                        JSSTincluder::getJSModel('configuration')->genearateCronKey();
                    }
                    JSSTincluder::getJSModel('configuration')->getConfigurations();
                    break;
                case 'admin_cronjoburl':
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'configuration');
            JSSTincluder::include_file($layout, $module);
        }
    }

    function canaddfile() {
        $nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'jstask')
                return false;
            else
                return true;
        }
    }

    static function saveconfiguration() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-configuration') ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('configuration')->storeConfiguration($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=configuration&jstlay=configurations");
        }
        if(isset($data['call_from']) && $data['call_from'] == 'notification' && is_admin()){
            $url = admin_url("admin.php?page=web-notification-setting");    
        }
        wp_redirect($url);
        exit;
    }

}

$configurationController = new JSSTconfigurationController();
?>
