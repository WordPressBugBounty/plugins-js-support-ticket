<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTsystemerrorController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'systemerrors');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_systemerrors':
                    JSSTincluder::getJSModel('systemerror')->getSystemErrors();
                    break;

                case 'admin_addsystemerror':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('systemerror')->getsystemerrorForForm($jsst_id);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'systemerror');
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

    static function savesystemerror() {
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('systemerror')->storesystemerror($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=systemerror&jstlay=systemerrors");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'systemerror','jstlay'=>'systemerrors'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletesystemerror() {
        $jsst_id = JSSTrequest::getVar('systemerrorid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-systemerror-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('systemerror')->removeSystemError($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=systemerror&jstlay=systemerrors");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'systemerror','jstlay'=>'systemerrors'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_systemerrorController = new JSSTsystemerrorController();
?>
