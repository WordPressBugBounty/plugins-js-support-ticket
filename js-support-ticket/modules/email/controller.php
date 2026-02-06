<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTemailController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'emails');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_emails':
                    JSSTincluder::getJSModel('email')->getEmails();
                    break;

                case 'admin_addemail':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('email')->getEmailForForm($jsst_id);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'email');
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

    static function saveemail() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-email-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('email')->storeEmail($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=email&jstlay=emails");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'email', 'jstlay'=>'emails'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deleteemail() {
        $jsst_id = JSSTrequest::getVar('emailid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-email-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('email')->removeEmail($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=email&jstlay=emails");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'email', 'jstlay'=>'emails'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_emailController = new JSSTemailController();
?>
