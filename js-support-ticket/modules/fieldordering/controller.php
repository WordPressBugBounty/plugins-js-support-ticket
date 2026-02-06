<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTfieldorderingController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'fieldordering');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_fieldordering':
                    $jsst_fieldfor = JSSTrequest::getVar('fieldfor',null,1);
                    $jsst_formid = JSSTrequest::getVar('formid');
                    jssupportticket::$jsst_data['fieldfor'] = $jsst_fieldfor;
                    if ($jsst_fieldfor != 1) {
                        jssupportticket::$jsst_data['formid'] = 1;
                    }
                    else{
                        jssupportticket::$jsst_data['formid'] = $jsst_formid;
                        do_action('jsst_multiform_name_for_list' , $jsst_formid);
                    }
                    JSSTincluder::getJSModel('fieldordering')->getFieldOrderingForList($jsst_fieldfor);
                    break;
                case 'admin_adduserfeild':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid');
                    $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
                    if($jsst_fieldfor == ''){
                        $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
                    }else{
                        jssupportticket::$jsst_data['fieldfor'] = $jsst_fieldfor;
                    }
                    // formid
                    if ($jsst_fieldfor != 1) {
                        jssupportticket::$jsst_data['formid'] = 1;
                    }
                    else{
                        $jsst_formid = JSSTrequest::getVar('formid');
                        jssupportticket::$jsst_data['formid'] = $jsst_formid;
                        do_action('jsst_multiform_name_for_list' , $jsst_formid);
                    }
                    // 
                    JSSTincluder::getJSModel('fieldordering')->getUserFieldbyId($jsst_id,1);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'fieldordering');
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

    static function changeorder() {
        $jsst_id = JSSTrequest::getVar('fieldorderingid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-order-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        if($jsst_fieldfor == ''){
            $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
        }
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_action = JSSTrequest::getVar('order');
        JSSTincluder::getJSModel('fieldordering')->changeOrder($jsst_id, $jsst_action);
        $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changepublishstatus() {
        $jsst_id = JSSTrequest::getVar('fieldorderingid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-publish-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        if($jsst_fieldfor == ''){
            $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
        }
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changePublishStatus($jsst_id, $jsst_status);
        $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changevisitorpublishstatus() {
        $jsst_id = JSSTrequest::getVar('fieldorderingid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-visitor-publish-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        if($jsst_fieldfor == ''){
            $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
        }
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changeVisitorPublishStatus($jsst_id, $jsst_status);
        $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changerequiredstatus() {
        $jsst_id = JSSTrequest::getVar('fieldorderingid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-required-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        if($jsst_fieldfor == ''){
            $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
        }
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changeRequiredStatus($jsst_id, $jsst_status);
        $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function saveuserfeild() {
        // Validate ID: Ensure it's a numeric value to prevent injection
        $jsst_id = JSSTrequest::getVar('id');
        if (!empty($jsst_id) && (!is_numeric($jsst_id) || intval($jsst_id) < 0)) {
            return false;
        }

        // Validate Nonce
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'save-userfeild-' . $jsst_id)) {
            die('Security check Failed');
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }

        // Retrieve and Sanitize Input Data
        $jsst_data = JSSTrequest::get('post');
        if (!is_array($jsst_data)) {
            return false; // Ensure data is an array
        }
        array_walk_recursive($jsst_data, function (&$jsst_item) {
            $jsst_item = sanitize_text_field($jsst_item);
        });

        // Validate fieldfor parameter
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        if (empty($jsst_fieldfor)) {
            $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
        }
        $jsst_fieldfor = sanitize_text_field($jsst_fieldfor); // Prevent malicious input

        // Validate formid parameter
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_formid = sanitize_text_field($jsst_formid);

        // Store the sanitized user field using prepared statements
        JSSTincluder::getJSModel('fieldordering')->storeUserField($jsst_data);

        // Redirect securely
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=fieldordering&fieldfor=" . urlencode($jsst_fieldfor) . "&formid=" . urlencode($jsst_formid));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod' => 'fieldordering', 'jstlay' => 'userfeilds'));
        }

        wp_safe_redirect($jsst_url);
        exit;
    }

    static function savefeild() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-feild-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        if($jsst_fieldfor == ''){
            $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
        }
        $jsst_formid = JSSTrequest::getVar('formid');
        JSSTincluder::getJSModel('fieldordering')->updateField($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'fieldordering', 'jstlay'=>'userfeilds'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function removeuserfeild() {
        $jsst_id = JSSTrequest::getVar('jssupportticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'remove-userfeild-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
        if($jsst_fieldfor == ''){
            $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
        }
        $jsst_formid = JSSTrequest::getVar('formid');
        JSSTincluder::getJSModel('fieldordering')->deleteUserField($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'fieldordering', 'jstlay'=>'userfeilds'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_fieldorderingController = new JSSTfieldorderingController();
?>
