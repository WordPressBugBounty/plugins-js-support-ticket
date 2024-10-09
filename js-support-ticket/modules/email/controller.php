<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTemailController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'emails');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_emails':
                    JSSTincluder::getJSModel('email')->getEmails();
                    break;

                case 'admin_addemail':
                    $id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('email')->getEmailForForm($id);
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'email');
            JSSTincluder::include_file($layout, $module);
        }
    }

    function canaddfile() {
        $nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket')
                return false;
            elseif (isset($_GET['action']) && $_GET['action'] == 'jstask')
                return false;
            else
                return true;
        }
    }

    static function saveemail() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-email') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('email')->storeEmail($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=email&jstlay=emails");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'email', 'jstlay'=>'emails'));
        }
        wp_redirect($url);
        exit;
    }

    static function deleteemail() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-email') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('emailid');
        JSSTincluder::getJSModel('email')->removeEmail($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=email&jstlay=emails");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'email', 'jstlay'=>'emails'));
        }
        wp_redirect($url);
        exit;
    }

}

$emailController = new JSSTemailController();
?>
