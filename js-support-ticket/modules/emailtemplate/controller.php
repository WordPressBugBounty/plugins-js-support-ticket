<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTemailtemplateController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'emailtemplates');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_emailtemplates':
                    $jsst_tempfor = JSSTrequest::getVar('for', null, 'tk-nw');
                    $jsst_formid = JSSTrequest::getVar('formid', null, '');
                    $jsst_langcode = JSSTrequest::getVar('langcode', null, '');
                    jssupportticket::$jsst_data[1] = $jsst_tempfor;
                    JSSTincluder::getJSModel('emailtemplate')->getTemplate($jsst_tempfor, $jsst_formid, $jsst_langcode);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'emailtemplate');
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

    static function saveemailtemplate() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-email-template-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        if (!empty($jsst_data['lang_id'])) {
            if($jsst_data['lang_id'] == '' || $jsst_data['subject'] == '' || $jsst_data['body'] == ''){
                JSSTmessage::setMessage(esc_html(__('Required Fields are not filled', 'js-support-ticket')), 'error');
            }else{
                JSSTincluder::getJSModel('multilanguageemailtemplates')->storeMultiLanguageEmailTemplate($jsst_data);
            }
        }else{
            JSSTincluder::getJSModel('emailtemplate')->storeEmailTemplate($jsst_data);
        }
        $jsst_url = admin_url("admin.php?page=emailtemplate&for=" . JSSTrequest::getVar('for'));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function savecustomemailtemplate() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-form-email-template') ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        if (!empty($jsst_data['language_id']) && in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)) {
            JSSTincluder::getJSModel('multilanguageemailtemplates')->storeMultiLanguageEmailTemplate($jsst_data);
        } elseif(in_array('multiform', jssupportticket::$_active_addons)) {
            JSSTincluder::getJSModel('multiform')->storeFormEmailTemplate($jsst_data);
        } else {
            JSSTmessage::setMessage(esc_html(__('Required Field(s) are not filled', 'js-support-ticket')), 'error');
        }
        $jsst_url = admin_url("admin.php?page=emailtemplate&for=" . JSSTrequest::getVar('for'));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deleteformemailtemplate() {
        $jsst_id = JSSTrequest::getVar('templateid');
        $jsst_source = JSSTrequest::getVar('source');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-template-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('emailtemplate')->removeFormEmailTemplate($jsst_id, $jsst_source);
        $jsst_url = admin_url("admin.php?page=emailtemplate&for=" . JSSTrequest::getVar('for'));
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_emailtemplateController = new JSSTemailtemplateController();
?>
