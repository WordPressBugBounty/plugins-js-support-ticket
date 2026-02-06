<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTthirdpartyimportController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'thirdpartyimport');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_importdata':
                    $jsst_selected_plugin = JSSTrequest::getVar('selected_plugin', '', 0);
                    jssupportticket::$jsst_data['count_for'] = $jsst_selected_plugin;
                    if($jsst_selected_plugin == 1){
                        // prepare data for supportcandy plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getSupportCandyDataStats($jsst_selected_plugin);
                    } elseif($jsst_selected_plugin == 2){
                        // prepare data for awesome Awesome Support plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getAwesomeSupportStats($jsst_selected_plugin);
                    } elseif($jsst_selected_plugin == 3){
                        // prepare data for Fluent Support plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getFluentSupportDataStats($jsst_selected_plugin);
                    } 
                    // no plugin selected
                    break;
                case 'admin_importresult':
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'thirdpartyimport');
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

    function importPluginData() {
        // $jsst_id = JSSTrequest::getVar('id');
        // $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        // if (! wp_verify_nonce( $jsst_nonce, 'save-status-'.$jsst_id) ) {
        //     die( 'Security check Failed' );
        // }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_selected_plugin = JSSTrequest::getVar('selected_plugin', '', 0);
        jssupportticket::$jsst_data['count_for'] = $jsst_selected_plugin;
        if($jsst_selected_plugin == 1){
            JSSTincluder::getJSModel('thirdpartyimport')->importSupportCandyData();
        } elseif($jsst_selected_plugin == 2){
            JSSTincluder::getJSModel('thirdpartyimport')->importAwesomeSupportData();
        } elseif($jsst_selected_plugin == 3){
            JSSTincluder::getJSModel('thirdpartyimport')->importFluentSupportData();
        }
        $jsst_url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult&selected_plugin=".$jsst_selected_plugin);
        wp_safe_redirect($jsst_url);
        exit;
    }

    function getSupportCandyDataStats() {
        $jsst_id = JSSTrequest::getVar('statusid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('thirdpartyimport')->getSupportCandyDataStats($jsst_id);
        $jsst_url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult");
        wp_safe_redirect($jsst_url);
        exit;
    }

    function getFluentSupportStats() {
        $jsst_id = JSSTrequest::getVar('statusid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('thirdpartyimport')->getFluentSupportStats($jsst_id);
        $jsst_url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult");
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_thirdpartyimportController = new JSSTthirdpartyimportController();
?>
