<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTfieldorderingController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'fieldordering');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_fieldordering':
                    $fieldfor = JSSTrequest::getVar('fieldfor',null,1);
                    $formid = JSSTrequest::getVar('formid');
                    jssupportticket::$_data['fieldfor'] = $fieldfor;
                    if ($fieldfor != 1) {
                        jssupportticket::$_data['formid'] = 1;
                    }
                    else{
                        jssupportticket::$_data['formid'] = $formid;
                        do_action('jsst_multiform_name_for_list' , $formid);
                    }
                    JSSTincluder::getJSModel('fieldordering')->getFieldOrderingForList($fieldfor);
                    break;
                case 'admin_adduserfeild':
                    $id = JSSTrequest::getVar('jssupportticketid');
                    $fieldfor = JSSTrequest::getVar('fieldfor');
                    if($fieldfor == ''){
                        $fieldfor = jssupportticket::$_data['fieldfor'];
                    }else{
                        jssupportticket::$_data['fieldfor'] = $fieldfor;
                    }
                    // formid
                    if ($fieldfor != 1) {
                        jssupportticket::$_data['formid'] = 1;
                    }
                    else{
                        $formid = JSSTrequest::getVar('formid');
                        jssupportticket::$_data['formid'] = $formid;
                        do_action('jsst_multiform_name_for_list' , $formid);
                    }
                    // 
                    JSSTincluder::getJSModel('fieldordering')->getUserFieldbyId($id,1);
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'fieldordering');
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

    static function changeorder() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-order') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('fieldorderingid');
        $fieldfor = JSSTrequest::getVar('fieldfor');
        if($fieldfor == ''){
            $fieldfor = jssupportticket::$_data['fieldfor'];
        }
        $formid = JSSTrequest::getVar('formid');
        $action = JSSTrequest::getVar('order');
        JSSTincluder::getJSModel('fieldordering')->changeOrder($id, $action);
        $url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($fieldfor)."&formid=".esc_attr($formid));
        wp_redirect($url);
        exit;
    }

    static function changepublishstatus() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-publish-status') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('fieldorderingid');
        $fieldfor = JSSTrequest::getVar('fieldfor');
        if($fieldfor == ''){
            $fieldfor = jssupportticket::$_data['fieldfor'];
        }
        $formid = JSSTrequest::getVar('formid');
        $status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changePublishStatus($id, $status);
        $url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($fieldfor)."&formid=".esc_attr($formid));
        wp_redirect($url);
        exit;
    }

    static function changevisitorpublishstatus() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-visitor-publish-status') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('fieldorderingid');
        $fieldfor = JSSTrequest::getVar('fieldfor');
        if($fieldfor == ''){
            $fieldfor = jssupportticket::$_data['fieldfor'];
        }
        $formid = JSSTrequest::getVar('formid');
        $status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changeVisitorPublishStatus($id, $status);
        $url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($fieldfor)."&formid=".esc_attr($formid));
        wp_redirect($url);
        exit;
    }

    static function changerequiredstatus() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-required-status') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('fieldorderingid');
        $fieldfor = JSSTrequest::getVar('fieldfor');
        if($fieldfor == ''){
            $fieldfor = jssupportticket::$_data['fieldfor'];
        }
        $formid = JSSTrequest::getVar('formid');
        $status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changeRequiredStatus($id, $status);
        $url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($fieldfor)."&formid=".esc_attr($formid));
        wp_redirect($url);
        exit;
    }

    static function saveuserfeild() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-userfeild') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');

        $fieldfor = JSSTrequest::getVar('fieldfor');
        if($fieldfor == ''){
            $fieldfor = jssupportticket::$_data['fieldfor'];
        }
        $formid = JSSTrequest::getVar('formid');
        JSSTincluder::getJSModel('fieldordering')->storeUserField($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=fieldordering&fieldfor=".esc_attr($fieldfor)."&formid=".esc_attr($formid));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'fieldordering', 'jstlay'=>'userfeilds'));
        }
        wp_redirect($url);
        exit;
    }

    static function savefeild() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-feild') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        $fieldfor = JSSTrequest::getVar('fieldfor');
        if($fieldfor == ''){
            $fieldfor = jssupportticket::$_data['fieldfor'];
        }
        $formid = JSSTrequest::getVar('formid');
        JSSTincluder::getJSModel('fieldordering')->updateField($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=fieldordering&fieldfor=".esc_attr($fieldfor)."&formid=".esc_attr($formid));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'fieldordering', 'jstlay'=>'userfeilds'));
        }
        wp_redirect($url);
        exit;
    }

    static function removeuserfeild() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'remove-userfeild') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('jssupportticketid');
        $fieldfor = JSSTrequest::getVar('fieldfor');
        if($fieldfor == ''){
            $fieldfor = jssupportticket::$_data['fieldfor'];
        }
        $formid = JSSTrequest::getVar('formid');
        JSSTincluder::getJSModel('fieldordering')->deleteUserField($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=fieldordering&fieldfor=".esc_attr($fieldfor)."&formid=".esc_attr($formid));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'fieldordering', 'jstlay'=>'userfeilds'));
        }
        wp_redirect($url);
        exit;
    }

}

$fieldorderingController = new JSSTfieldorderingController();
?>
