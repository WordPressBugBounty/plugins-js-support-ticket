<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTemailtemplateController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'emailtemplates');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_emailtemplates':
                    $tempfor = JSSTrequest::getVar('for', null, 'tk-nw');
                    jssupportticket::$_data[1] = $tempfor;
                    JSSTincluder::getJSModel('emailtemplate')->getTemplate($tempfor);
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'emailtemplate');
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

    static function saveemailtemplate() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-email-template') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        if($data['callfor'] == 'Mulitlanguage'){
            if($data['lang_id'] == '' || $data['subject'] == '' || $data['body'] == ''){
                JSSTmessage::setMessage(esc_html(__('Required Fields are not filled', 'js-support-ticket')), 'error');
            }else{
                JSSTincluder::getJSModel('multilanguageemailtemplates')->storeMultiLanguageEmailTemplate($data);
            }
        }else{
            JSSTincluder::getJSModel('emailtemplate')->storeEmailTemplate($data);
        }
        $url = admin_url("admin.php?page=emailtemplate&for=" . JSSTrequest::getVar('for'));
        wp_redirect($url);
        exit;
    }

}

$emailtemplateController = new JSSTemailtemplateController();
?>
