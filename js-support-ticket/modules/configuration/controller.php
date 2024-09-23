<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTconfigurationController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'configurations');
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
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'configuration');
            JSSTincluder::include_file($layout, $module);
        }
    }

    function canaddfile() {
        if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket')
            return false;
        elseif (isset($_GET['action']) && $_GET['action'] == 'jstask')
            return false;
        else
            return true;
    }

    static function saveconfiguration() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-configuration') ) {
            die( 'Security check Failed' );
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
