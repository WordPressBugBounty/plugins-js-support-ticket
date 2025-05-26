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
        if (self::canaddfile($layout)) {
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

    function canaddfile($layout) {
        $nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if(!is_admin() && jssupportticketphplib::JSST_strpos($layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    static function changeorder() {
        $id = JSSTrequest::getVar('fieldorderingid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-order-'.$id) ) {
            die( 'Security check Failed' );
        }
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
        $id = JSSTrequest::getVar('fieldorderingid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-publish-status-'.$id) ) {
            die( 'Security check Failed' );
        }
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
        $id = JSSTrequest::getVar('fieldorderingid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-visitor-publish-status-'.$id) ) {
            die( 'Security check Failed' );
        }
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
        $id = JSSTrequest::getVar('fieldorderingid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-required-status-'.$id) ) {
            die( 'Security check Failed' );
        }
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
        // Validate ID: Ensure it's a numeric value to prevent injection
        $id = JSSTrequest::getVar('id');
        if (!empty($id) && (!is_numeric($id) || intval($id) < 0)) {
            return false;
        }

        // Validate Nonce
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($nonce, 'save-userfeild-' . $id)) {
            die('Security check Failed');
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }

        // Retrieve and Sanitize Input Data
        $data = JSSTrequest::get('post');
        if (!is_array($data)) {
            return false; // Ensure data is an array
        }
        array_walk_recursive($data, function (&$item) {
            $item = sanitize_text_field($item);
        });

        // Validate fieldfor parameter
        $fieldfor = JSSTrequest::getVar('fieldfor');
        if (empty($fieldfor)) {
            $fieldfor = jssupportticket::$_data['fieldfor'];
        }
        $fieldfor = sanitize_text_field($fieldfor); // Prevent malicious input

        // Validate formid parameter
        $formid = JSSTrequest::getVar('formid');
        $formid = sanitize_text_field($formid);

        // Store the sanitized user field using prepared statements
        JSSTincluder::getJSModel('fieldordering')->storeUserField($data);

        // Redirect securely
        if (is_admin()) {
            $url = admin_url("admin.php?page=fieldordering&fieldfor=" . urlencode($fieldfor) . "&formid=" . urlencode($formid));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod' => 'fieldordering', 'jstlay' => 'userfeilds'));
        }

        wp_safe_redirect($url);
        exit;
    }

    static function savefeild() {
        $id = JSSTrequest::getVar('id');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-feild-'.$id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
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
        $id = JSSTrequest::getVar('jssupportticketid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'remove-userfeild-'.$id) ) {
            die( 'Security check Failed' );
        }
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
