<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTstatusController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'statuses');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($layout)) {
            switch ($layout) {
                case 'admin_statuses':
                    JSSTincluder::getJSModel('status')->getStatuses();
                    break;
                case 'admin_addstatus':
                    $id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('status')->getStatusForForm($id);
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'status');
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

    static function savestatus() {
        $id = JSSTrequest::getVar('id');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-status-'.$id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('status')->storeStatus($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=status&jstlay=statuses");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'status','jstlay'=>'statuses'));
        }
        wp_redirect($url);
        exit;
    }

    static function deletestatus() {
        $id = JSSTrequest::getVar('statusid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-status-'.$id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('status')->removeStatus($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=status&jstlay=statuses");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'status','jstlay'=>'statuses'));
        }
        wp_redirect($url);
        exit;
    }

    static function ordering() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'ordering') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('statusid');
        JSSTincluder::getJSModel('status')->setOrdering($id);
        $pagenum = JSSTrequest::getVar('pagenum');
        $url = "admin.php?page=status&jstlay=statuses";
        if ($pagenum)
            $url .= '&pagenum=' . $pagenum;
        wp_redirect($url);
        exit;
    }

}

$statusController = new JSSTstatusController();
?>
