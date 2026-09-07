<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Canned responses controller — part of the free core. (Roadmap 4.0-CORE-03)
 *
 * Same layouts, tasks and nonce names as the stand-alone add-on, so existing
 * bookmarks, menu links and forms keep working whichever side is serving the
 * feature. (Roadmap 4.0-CORE-19)
 */
class JSSTcannedresponsesController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'premademessages');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_premademessages':
                case 'agentcannedresponses':
                    jssupportticket::$jsst_data['permission_granted'] = true;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Canned Response');
                    }
                    if (jssupportticket::$jsst_data['permission_granted']) {
                        JSSTincluder::getJSModel('cannedresponses')->getPremadeMessages();
                    }
                    break;
                case 'admin_addpremademessage':
                case 'addcannedresponse':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid');
                    jssupportticket::$jsst_data['permission_granted'] = true;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        $jsst_per_task = ($jsst_id == null) ? 'Add Canned Response' : 'Edit Canned Response';
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_per_task);
                    }
                    if (jssupportticket::$jsst_data['permission_granted']) {
                        JSSTincluder::getJSModel('cannedresponses')->getPremadeMessageForForm($jsst_id);
                    }
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'cannedresponses');
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

    static function savepremademessage() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-premade-message-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('cannedresponses')->storePreMadeMessage($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=cannedresponses&jstlay=premademessages");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'agentcannedresponses'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletepremademessage() {
        $jsst_id = JSSTrequest::getVar('premademessageid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-premademessage-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('cannedresponses')->removePreMadeMessage($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=cannedresponses&jstlay=premademessages");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'agentcannedresponses'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changestatus() {
        $jsst_id = JSSTrequest::getVar('premadeid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('cannedresponses')->changeStatus($jsst_id);
        $jsst_url = admin_url("admin.php?page=cannedresponses&jstlay=premademessages");
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_cannedresponsesController = new JSSTcannedresponsesController();
?>
