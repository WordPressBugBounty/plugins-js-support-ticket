<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Blocked senders controller — part of the free core. (Roadmap 4.0-CORE-12)
 *
 * The add-on's controller unchanged: same layouts, tasks and nonces.
 * (Roadmap 4.0-CORE-19)
 */
class JSSTbanemailController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'banemails');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_banemails':
                    JSSTincluder::getJSModel('banemail')->getBanEmails();
                    break;

                case 'admin_addbanemail':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid');
                    JSSTincluder::getJSModel('banemail')->getBanEmailForForm($jsst_id);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'banemail');
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

    static function savebanemail() {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-ban-email-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('banemail')->storeBanEmail($jsst_data);
        $jsst_url = admin_url("admin.php?page=banemail&jstlay=banemails");
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletebanemail() {
        $jsst_id = JSSTrequest::getVar('banemailid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-banemail-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('banemail')->removeBanEmail($jsst_id);
        $jsst_url = admin_url("admin.php?page=banemail&jstlay=banemails");
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_banemailController = new JSSTbanemailController();
