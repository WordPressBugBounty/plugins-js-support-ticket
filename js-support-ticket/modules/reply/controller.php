<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreplyController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'replies');
        $jsst_task = JSSTrequest::getLayout('task', null, 'replies_replies');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'reply');
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

    static function savereply() {
        $jsst_ticketid = JSSTrequest::getVar('ticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-reply-'.$jsst_ticketid) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('reply')->storeReplies($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . $jsst_ticketid);
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_ticketid));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function saveeditedreply() {
        $jsst_tikcetid = JSSTrequest::getVar('reply-tikcetid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-edited-reply-'.$jsst_tikcetid) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('reply')->editReply($jsst_data);
        if (current_user_can('manage_options') || current_user_can('jsst_support_ticket_tickets')) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['reply-tikcetid']));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_data['reply-tikcetid'],'jsstpageid'=>jssupportticket::getPageid()));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function saveeditedtime() {
        $jsst_data = JSSTrequest::get('post');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-edited-time-'.$jsst_data['reply-tikcetid']) ) {
            die( 'Security check Failed' );
        }
        if(!in_array('timetracking', jssupportticket::$_active_addons)){
            return;
        }
        JSSTincluder::getJSModel('timetracking')->editTime($jsst_data);
        if (current_user_can('manage_options') || current_user_can('jsst_support_ticket_tickets')) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($jsst_data['reply-tikcetid']));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_data['reply-tikcetid'],'jsstpageid'=>jssupportticket::getPageid()));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_replyController = new JSSTreplyController();
?>
