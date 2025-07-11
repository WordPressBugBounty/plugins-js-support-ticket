<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTthirdpartyimportController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'thirdpartyimport');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($layout)) {
            switch ($layout) {
                case 'admin_importdata':
                    $selected_plugin = JSSTrequest::getVar('selected_plugin', '', 0);
                    jssupportticket::$_data['count_for'] = $selected_plugin;
                    if($selected_plugin == 1){
                        // prepare data for supportcandy plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getSupportCandyDataStats($selected_plugin);
                    } elseif($selected_plugin == 2){
                        // prepare data for awesome Awesome Support plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getAwesomeSupportStats($selected_plugin);
                    } elseif($selected_plugin == 3){
                        // prepare data for Fluent Support plugin
                        JSSTincluder::getJSModel('thirdpartyimport')->getFluentSupportDataStats($selected_plugin);
                    } 
                    // no plugin selected
                    break;
                case 'admin_importresult':
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'thirdpartyimport');
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

    function importPluginData() {
        // $id = JSSTrequest::getVar('id');
        // $nonce = JSSTrequest::getVar('_wpnonce');
        // if (! wp_verify_nonce( $nonce, 'save-status-'.$id) ) {
        //     die( 'Security check Failed' );
        // }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $selected_plugin = JSSTrequest::getVar('selected_plugin', '', 0);
        jssupportticket::$_data['count_for'] = $selected_plugin;
        if($selected_plugin == 1){
            JSSTincluder::getJSModel('thirdpartyimport')->importSupportCandyData();
        } elseif($selected_plugin == 2){
            JSSTincluder::getJSModel('thirdpartyimport')->importAwesomeSupportData();
        } elseif($selected_plugin == 3){
            JSSTincluder::getJSModel('thirdpartyimport')->importFluentSupportData();
        }
        $url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult&selected_plugin=".$selected_plugin);
        wp_redirect($url);
        exit;
    }

    function getSupportCandyDataStats() {
        $id = JSSTrequest::getVar('statusid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-status-'.$id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('thirdpartyimport')->getSupportCandyDataStats($id);
        $url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult");
        wp_redirect($url);
        exit;
    }

    function getFluentSupportStats() {
        $id = JSSTrequest::getVar('statusid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-status-'.$id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('thirdpartyimport')->getFluentSupportStats($id);
        $url = admin_url("admin.php?page=thirdpartyimport&jstlay=importresult");
        wp_redirect($url);
        exit;
    }

}

$thirdpartyimportController = new JSSTthirdpartyimportController();
?>
