<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreplyController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'replies');
        $task = JSSTrequest::getLayout('task', null, 'replies_replies');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($layout)) {
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'reply');
            JSSTincluder::include_file($layout, $module);
        }
    }

    function canaddfile($layout) {
        $nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if(!is_admin() && jssupportticketphplib::JSST_strpos($layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    static function savereply() {
        $ticketid = JSSTrequest::getVar('ticketid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-reply-'.$ticketid) ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('reply')->storeReplies($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . $ticketid);
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$ticketid));
        }
        wp_redirect($url);
        exit;
    }

    static function saveeditedreply() {
        $tikcetid = JSSTrequest::getVar('reply-tikcetid');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-edited-reply-'.$tikcetid) ) {
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
        $data = JSSTrequest::get('post');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-edited-time-'.$data['reply-tikcetid']) ) {
            die( 'Security check Failed' );
        }
        if(!in_array('timetracking', jssupportticket::$_active_addons)){
            return;
        }
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
