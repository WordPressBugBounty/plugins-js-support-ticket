<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTslugController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'slug');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_slug':
                    JSSTincluder::getJSModel('slug')->getSlug();
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'slug');
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

    function saveSlug() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-slug') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        $result = JSSTincluder::getJSModel('slug')->storeSlug($data);
        if($data['pagenum'] > 0){
            $url = admin_url("admin.php?page=slug&pagenum=".esc_attr($data['pagenum']));
        }else{
            $url = admin_url("admin.php?page=slug");
        }
        wp_redirect($url);
        exit;
    }

    function saveprefix() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-prefix') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        $result = JSSTincluder::getJSModel('slug')->savePrefix($data);
        $url = admin_url("admin.php?page=slug");
        wp_redirect($url);
        exit;
    }

    function savehomeprefix() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-home-prefix') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        $result = JSSTincluder::getJSModel('slug')->saveHomePrefix($data);
        $url = admin_url("admin.php?page=slug");
        wp_redirect($url);
        exit;
    }

    function resetallslugs() {
        $data = JSSTrequest::get('post');
        $result = JSSTincluder::getJSModel('slug')->resetAllSlugs();
        $url = admin_url("admin.php?page=slug");
        wp_redirect($url);
        exit;
    }

}

$slugController = new JSSTslugController();
?>
