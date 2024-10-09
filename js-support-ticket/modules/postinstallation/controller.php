<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpostinstallationController {

    function __construct() {

        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'stepone');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if($this->canaddfile()){
            switch ($layout) {
                case 'admin_quickconfig':
                    JSSTincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_stepone':
                    JSSTincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_steptwo':
                    JSSTincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_stepthree':
                    if(!in_array('feedback', jssupportticket::$_active_addons)){// to hanle show hide of feed back settings.
                        $layout = 'admin_settingcomplete';
                    }
                    JSSTincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_themedemodata':
                    jssupportticket::$_data['flag'] = JSSTrequest::getVar('flag');
                break;
                case 'admin_translationoption':
                    jssupportticket::$_data[0]['jstran'] = JSSTincluder::getJSModel('jssupportticket')->getInstalledTranslationKey();
                    if(!jssupportticket::$_data[0]['jstran']){
                        if(!in_array('feedback', jssupportticket::$_active_addons)){// to handle show hide of feed back settings.
                            $layout = 'admin_settingcomplete';
                        }else{
                            $layout = 'admin_stepthree';
                        }
                    }
                break;
                case 'admin_settingcomplete':
                    break;
                case 'admin_stepfour':
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'postinstallation');
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

    function save(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        if($data['step'] != 'translationoption'){
            $result = JSSTincluder::getJSModel('postinstallation')->storeConfigurations($data);
        }
        $url = admin_url("admin.php?page=postinstallation&jstlay=steptwo");
        if($data['step'] == 2){
            $url = admin_url("admin.php?page=postinstallation&jstlay=translationoption");
        }
        if($data['step'] == 'translationoption'){
            $url = admin_url("admin.php?page=postinstallation&jstlay=stepthree");
        }
        if($data['step'] == 3){
            $url = admin_url("admin.php?page=postinstallation&jstlay=stepfour");
        }

        wp_redirect($url);
        exit();
    }

    function savesampledata(){
        $data = JSSTrequest::get('post');
        $sampledata = $data['sampledata'];
        $jsmenu = $data['jsmenu'];
        $empmenu = $data['empmenu'];
        $url = admin_url("admin.php?page=jslearnmanager");
        $result = JSSTincluder::getJSModel('postinstallation')->installSampleData($sampledata);
        wp_redirect($url);
        exit();
    }
}
$JSSTpostinstallationController = new JSSTpostinstallationController();
?>
