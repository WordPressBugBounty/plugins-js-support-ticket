<?php
if (!defined('ABSPATH')) die('Restricted Access');

class JSSTzywrapController {

    // --- ADD THESE TWO FUNCTIONS FOR THE MENU ROUTING ---
    function __construct() {
        self::handleRequest();

    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'zywrap');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_zywrap':
                    include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
                    JSSTupdates::checkUpdates('307');
                    JSSTincluder::getJSModel('zywrap')->getDashboardStats();
                    break;
                case 'admin_zywrap_settings':
                    break;
                case 'admin_zywrap_playground':
                    // No model function needed, the playground queries directly via AJAX
                    $jsst_layout = 'admin_zywrap_playground'; 
                    break;
                case 'admin_zywrap_logs':
                    JSSTincluder::getJSModel('zywrap')->getLogs();
                    $jsst_layout = 'admin_zywrap_logs'; // Force load the admin template
                    break;
                    
                case 'admin_zywrap_errors':
                    JSSTincluder::getJSModel('zywrap')->getErrors();
                    $jsst_layout = 'admin_zywrap_errors'; 
                    break;
                    
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'zywrap');
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
    
    static function delete_log() {

        // SECURITY: ONLY ADMINS CAN DELETE LOGS
        if (!current_user_can('manage_options')) {
            wp_die('Security Error: Administrators only.');
        }
        
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        
        if (!wp_verify_nonce($jsst_nonce, 'delete_log_'.$jsst_id)) {
            die('Security check Failed');
        }
        
        JSSTincluder::getJSModel('zywrap')->deleteLog($jsst_id);
        
        // Redirect back to the errors page
        $jsst_url = admin_url("admin.php?page=zywrap&jstlay=zywrap_errors");
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        if ($jsst_pagenum) {
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

}


$jsst_ticketController = new JSSTzywrapController();

