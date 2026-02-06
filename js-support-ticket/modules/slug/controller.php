<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTslugController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'slug');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_slug':
                    JSSTincluder::getJSModel('slug')->getSlug();
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'slug');
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

    function saveSlug() {
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-slug') ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        $jsst_result = JSSTincluder::getJSModel('slug')->storeSlug($jsst_data);
        if($jsst_data['pagenum'] > 0){
            $jsst_url = admin_url("admin.php?page=slug&pagenum=".esc_attr($jsst_data['pagenum']));
        }else{
            $jsst_url = admin_url("admin.php?page=slug");
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    function saveprefix() {
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-prefix') ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        $jsst_result = JSSTincluder::getJSModel('slug')->savePrefix($jsst_data);
        $jsst_url = admin_url("admin.php?page=slug");
        wp_safe_redirect($jsst_url);
        exit;
    }

    function savehomeprefix() {
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-home-prefix') ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        $jsst_result = JSSTincluder::getJSModel('slug')->saveHomePrefix($jsst_data);
        $jsst_url = admin_url("admin.php?page=slug");
        wp_safe_redirect($jsst_url);
        exit;
    }

    function resetallslugs() {
        $jsst_data = JSSTrequest::get('post');
        $jsst_result = JSSTincluder::getJSModel('slug')->resetAllSlugs();
        $jsst_url = admin_url("admin.php?page=slug");
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_slugController = new JSSTslugController();
?>
