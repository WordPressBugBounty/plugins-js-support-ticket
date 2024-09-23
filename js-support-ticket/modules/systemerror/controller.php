<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTsystemerrorController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'systemerrors');
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_systemerrors':
                    JSSTincluder::getJSModel('systemerror')->getSystemErrors();
                    break;

                case 'admin_addsystemerror':
                    $id = JSSTrequest::getVar('jssupportticketid', 'get');
                    JSSTincluder::getJSModel('systemerror')->getsystemerrorForForm($id);
                    break;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'systemerror');
            JSSTincluder::include_file($layout, $module);
        }
    }

    function canaddfile() {
        if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket')
            return false;
        elseif (isset($_GET['action']) && $_GET['action'] == 'jstask')
            return false;
        else
            return true;
    }

    static function savesystemerror() {
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('systemerror')->storesystemerror($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=systemerror&jstlay=systemerrors");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'systemerror','jstlay'=>'systemerrors'));
        }
        wp_redirect($url);
        exit;
    }

    static function deletesystemerror() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-systemerror') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('systemerrorid');
        JSSTincluder::getJSModel('systemerror')->removeSystemError($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=systemerror&jstlay=systemerrors");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'systemerror','jstlay'=>'systemerrors'));
        }
        wp_redirect($url);
        exit;
    }

}

$systemerrorController = new JSSTsystemerrorController();
?>
