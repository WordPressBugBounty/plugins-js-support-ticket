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
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        $jsst_data = JSSTrequest::get('post');
        /* The nonce is checked against the ticket the reply is WRITTEN to - the
           POST body's ticketid, which storeReplies() uses - not getVar(), which
           prefers the query string. Same split as saveticket() had (security
           report, 28 September 2026): a nonce for one's own ticket in the URL,
           another ticket's id in the body. */
        $jsst_ticketid = isset($jsst_data['ticketid']) ? trim((string) $jsst_data['ticketid']) : '';
        if ($jsst_ticketid === '' || !ctype_digit($jsst_ticketid) || ! wp_verify_nonce( $jsst_nonce, 'save-reply-'.$jsst_ticketid) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        /* Set by email piping, which calls the model directly. From a browser
           it would switch off the ownership and closed-ticket checks. */
        unset($jsst_data['ticketviaemail'], $jsst_data['staffid']);
        /* This route only adds a reply; editing one is saveeditedreply, which
           checks the reply itself. A posted id made the table update that row -
           anyone's reply, moved onto the sender's ticket - and a posted uid
           named any user as the author (security report, 1 November 2026). */
        unset($jsst_data['id']);
        /* Set by the AI add-on, which calls the model directly. From a browser
           an autopilot flag skipped the HTML filter and let the sender name
           the author, and an AI-draft flag hid the reply from the thread.
           (Security review, 5 November 2026) */
        unset($jsst_data['ticketviaautopilot'], $jsst_data['is_ai_draft'], $jsst_data['name']);
        $jsst_data['uid'] = JSSTincluder::getObjectClass('user')->uid();
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
        /* From the body, which editReply() acts on - not getVar(), which
           prefers the query string: a nonce for one's own ticket in the URL
           and another ticket's reply in the body edited that reply.
           (Security review, 5 November 2026) */
        $jsst_tikcetid = isset($_POST['reply-tikcetid']) ? absint(wp_unslash($_POST['reply-tikcetid'])) : 0;
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-edited-reply-'.$jsst_tikcetid) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('reply')->editReply($jsst_data);
        // Where the agent was, not what the agent may do: an agent has
        // jsst_support_ticket_tickets on both sides, so this sent every edit made
        // from the help desk pages into wp-admin. The form posts to the URL of the
        // side it was rendered on, so is_admin() is the honest answer - and it is
        // what savereply() next door already uses. (Roadmap 4.0-CORE-05)
        if (is_admin()) {
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
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if(!in_array('timetracking', jssupportticket::$_active_addons)){
            return;
        }
        JSSTincluder::getJSModel('timetracking')->editTime($jsst_data);
        // Where the agent was, not what the agent may do: an agent has
        // jsst_support_ticket_tickets on both sides, so this sent every edit made
        // from the help desk pages into wp-admin. The form posts to the URL of the
        // side it was rendered on, so is_admin() is the honest answer - and it is
        // what savereply() next door already uses. (Roadmap 4.0-CORE-05)
        if (is_admin()) {
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
