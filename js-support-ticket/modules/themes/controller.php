<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTthemesController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'themes');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_themes':
                    if (current_user_can('manage_options')) {    
                        JSSTincluder::getJSModel('themes')->getCurrentTheme();
                    }
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'themes');

            if(strstr($layout, 'admin_')){
                if (!current_user_can('manage_options')) {
                    return false;
                }
            }

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
    static function savetheme() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-theme') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('themes')->storeTheme($data);
        $url = admin_url("admin.php?page=themes&jstlay=themes");
        wp_redirect($url);
        exit;
    }

}

$controlpanelController = new JSSTthemesController();
?>
