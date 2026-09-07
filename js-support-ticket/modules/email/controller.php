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
                    $jsst_id = absint( JSSTrequest::getVar('jssupportticketid', 'get') );
                    JSSTincluder::getJSModel('email')->getEmailForForm($jsst_id);
                    break;

                // Email health. Read-only: it gathers facts, it does not change
                // anything. (Roadmap 4.0-OPS-01)
                case 'admin_emailhealth':
                    $jsst_sender = JSSTincluder::getJSModel('email')->getDefaultSender();
                    jssupportticket::$jsst_data['mailhealth'] = JSSTmailhealth::report($jsst_sender['email'], $jsst_sender['name']);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'email');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    /**
     * Send one test message and report exactly what happened.
     *
     * Administrators only: it sends mail, and the result names the mail
     * service's own error. (Roadmap 4.0-OPS-01)
     */
    static function sendtestmail() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'jsst-mail-health-test') ) {
            die( 'Security check Failed' );
        }
        if (!current_user_can('manage_options')) {
            JSSTmessage::setMessage(esc_html(__('You are not allowed to do this', 'js-support-ticket')), 'error', 'agent-permissions');
            wp_safe_redirect(admin_url('admin.php?page=email&jstlay=emailhealth'));
            exit;
        }
        $jsst_sender = JSSTincluder::getJSModel('email')->getDefaultSender();
        $jsst_result = JSSTmailhealth::sendTest(
            JSSTrequest::getVar('testemail', 'post', ''),
            $jsst_sender['email'],
            $jsst_sender['name']
        );
        JSSTmessage::setMessage($jsst_result['message'], $jsst_result['ok'] ? 'updated' : 'error', $jsst_result['ok'] ? '' : 'email-not-sending');
        wp_safe_redirect(admin_url('admin.php?page=email&jstlay=emailhealth'));
        exit;
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
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_id = JSSTrequest::getVar('emailid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-email-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('email')->removeEmail( absint( $jsst_id ) );
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
