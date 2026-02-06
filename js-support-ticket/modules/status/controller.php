<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTstatusController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'statuses');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_statuses':
                    JSSTincluder::getJSModel('status')->getStatuses();
                    break;
                case 'admin_addstatus':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('status')->getStatusForForm($jsst_id);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'status');
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

    static function savestatus() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('status')->storeStatus($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=status&jstlay=statuses");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'status','jstlay'=>'statuses'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletestatus() {
        $jsst_id = JSSTrequest::getVar('statusid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('status')->removeStatus($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=status&jstlay=statuses");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'status','jstlay'=>'statuses'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function ordering() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'ordering') ) {
            die( 'Security check Failed' );
        }
        $jsst_id = JSSTrequest::getVar('statusid');
        JSSTincluder::getJSModel('status')->setOrdering($jsst_id);
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        $jsst_url = "admin.php?page=status&jstlay=statuses";
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_statusController = new JSSTstatusController();
?>
