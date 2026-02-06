<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpostinstallationController {

    function __construct() {

        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'wellcomepage');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if($this->canaddfile($jsst_layout)){
            switch ($jsst_layout) {
                case 'admin_quickconfig':
                    JSSTincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_wellcomepage':
                break;
                case 'admin_stepone':
                    JSSTincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_steptwo':
                    JSSTincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_stepthree':
                    if(!in_array('feedback', jssupportticket::$_active_addons)){// to hanle show hide of feed back settings.
                        $jsst_layout = 'admin_settingcomplete';
                    }
                    JSSTincluder::getJSModel('postinstallation')->getConfigurationValues();
                break;
                case 'admin_themedemodata':
                    jssupportticket::$jsst_data['flag'] = JSSTrequest::getVar('flag');
                break;
                case 'admin_translationoption':
                    jssupportticket::$jsst_data[0]['jstran'] = JSSTincluder::getJSModel('jssupportticket')->getInstalledTranslationKey();
                    if(!jssupportticket::$jsst_data[0]['jstran']){
                        if(!in_array('feedback', jssupportticket::$_active_addons)){// to handle show hide of feed back settings.
                            $jsst_layout = 'admin_settingcomplete';
                        }else{
                            $jsst_layout = 'admin_stepthree';
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
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'postinstallation');
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

    function save(){
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save') ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        if($jsst_data['step'] != 'translationoption'){
            $jsst_result = JSSTincluder::getJSModel('postinstallation')->storeConfigurations($jsst_data);
        }
        $jsst_url = admin_url("admin.php?page=postinstallation&jstlay=steptwo");
        if($jsst_data['step'] == 2){
            $jsst_url = admin_url("admin.php?page=postinstallation&jstlay=translationoption");
        }
        if($jsst_data['step'] == 'translationoption'){
            $jsst_url = admin_url("admin.php?page=postinstallation&jstlay=stepthree");
        }
        if($jsst_data['step'] == 3){
            $jsst_url = admin_url("admin.php?page=postinstallation&jstlay=stepfour");
        }

        wp_safe_redirect($jsst_url);
        exit();
    }

    function savesampledata(){
        $jsst_data = JSSTrequest::get('post');
        $jsst_sampledata = $jsst_data['sampledata'];
        $jsst_jsmenu = $jsst_data['jsmenu'];
        $jsst_empmenu = $jsst_data['empmenu'];
        $jsst_url = admin_url("admin.php?page=jslearnmanager");
        $jsst_result = JSSTincluder::getJSModel('postinstallation')->installSampleData($jsst_sampledata);
        wp_safe_redirect($jsst_url);
        exit();
    }
}
$JSSTpostinstallationController = new JSSTpostinstallationController();
?>
