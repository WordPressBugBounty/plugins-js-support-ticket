<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Blocked-sender log controller — part of the free core. (Roadmap 4.0-CORE-12)
 *
 * The add-on's controller unchanged: same layouts, tasks and nonces.
 * (Roadmap 4.0-CORE-19)
 */
class JSSTbanemaillogController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'banemaillogs');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_banemaillogs':
                    JSSTincluder::getJSModel('banemaillog')->getBanEmailLogs();
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'banemaillog');
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
                if(!is_admin() && strpos($jsst_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    static function savebanemaillog() {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('banemaillog')->storebanemaillog($jsst_data);
        $jsst_url = admin_url("admin.php?page=banemaillog&jstlay=banemaillogs");
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletebanemaillog() {
        $jsst_id = JSSTrequest::getVar('banemaillogid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-banemaillog-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('banemaillog')->removeBanEmailLog($jsst_id);
        $jsst_url = admin_url("admin.php?page=banemaillog&jstlay=banemaillogs");
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_banemaillogController = new JSSTbanemaillogController();
?>
