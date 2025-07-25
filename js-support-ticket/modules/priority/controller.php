<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTpriorityController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'priorities');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($layout)) {
            switch ($layout) {
                case 'admin_priorities':
                    JSSTincluder::getJSModel('priority')->getPriorities();
                    break;
                case 'admin_addpriority':
                    $id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('priority')->getPriorityForForm($id);
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'priority');
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

    static function savepriority() {
        $id = JSSTrequest::getVar('id');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-priority-'.$id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('priority')->storePriority($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=priority&jstlay=priorities");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'priority','jstlay'=>'priorities'));
        }
        wp_redirect($url);
        exit;
    }

    static function deletepriority() {
        $id = JSSTrequest::getVar('priorityid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-priority-'.$id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('priority')->removePriority($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=priority&jstlay=priorities");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'priority','jstlay'=>'priorities'));
        }
        wp_redirect($url);
        exit;
    }

    static function makedefault() {
        $id = JSSTrequest::getVar('priorityid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'make-default-'.$id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('priority')->makeDefault($id);
        $pagenum = JSSTrequest::getVar('pagenum');
        $url = "admin.php?page=priority&jstlay=priorities";
        if ($pagenum)
            $url .= '&pagenum=' . $pagenum;
        wp_redirect($url);
        exit;
    }

    static function ordering() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'ordering') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('priorityid');
        JSSTincluder::getJSModel('priority')->setOrdering($id);
        $pagenum = JSSTrequest::getVar('pagenum');
        $url = "admin.php?page=priority&jstlay=priorities";
        if ($pagenum)
            $url .= '&pagenum=' . $pagenum;
        wp_redirect($url);
        exit;
    }

}

$priorityController = new JSSTpriorityController();
?>
