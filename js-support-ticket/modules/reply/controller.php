<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreplyController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $task = JSSTrequest::getLayout('task', null, 'replies_replies');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'reply');
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

    static function savereply() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-reply') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('reply')->storeReplies($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . JSSTrequest::getVar('ticketid'));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>JSSTrequest::getVar('ticketid')));
        }
        wp_redirect($url);
        exit;
    }

    static function saveeditedreply() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-edited-reply') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('reply')->editReply($data);
        if (current_user_can('manage_options') || current_user_can('jsst_support_ticket_tickets')) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['reply-tikcetid']));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$data['reply-tikcetid'],'jsstpageid'=>jssupportticket::getPageid()));
        }
        wp_redirect($url);
        exit;
    }

    static function saveeditedtime() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-edited-time') ) {
            die( 'Security check Failed' );
        }
        if(!in_array('timetracking', jssupportticket::$_active_addons)){
            return;
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('timetracking')->editTime($data);
        if (current_user_can('manage_options') || current_user_can('jsst_support_ticket_tickets')) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . esc_attr($data['reply-tikcetid']));
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$data['reply-tikcetid'],'jsstpageid'=>jssupportticket::getPageid()));
        }
        wp_redirect($url);
        exit;
    }

}

$replyController = new JSSTreplyController();
?>
