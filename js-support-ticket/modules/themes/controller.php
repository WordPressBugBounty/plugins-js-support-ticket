<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTthemesController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'themes');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_themes':
                    if (current_user_can('manage_options')) {    
                        JSSTincluder::getJSModel('themes')->getCurrentTheme();
                    }
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'themes');

            if(strstr($jsst_layout, 'admin_')){
                if (!current_user_can('manage_options')) {
                    return false;
                }
            }

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
    static function savetheme() {
        if(!current_user_can('manage_options')){
            return false;
        }
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-theme') ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('themes')->storeTheme($jsst_data);
        $jsst_url = admin_url("admin.php?page=themes&jstlay=themes");
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_controlpanelController = new JSSTthemesController();
?>
