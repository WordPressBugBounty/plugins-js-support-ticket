<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Internal notes controller — part of the free core. (Roadmap 4.0-CORE-02)
 *
 * Same tasks and same nonce names as the stand-alone Private Note add-on, so the
 * forms and links already rendered on ticket pages keep posting to a route that
 * exists whichever side is serving the feature. (Roadmap 4.0-CORE-19)
 */
class JSSTnoteController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'notes');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'note');
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
                if(!is_admin() && strpos($jsst_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    static function savenote() {
        $jsst_ticketid = JSSTrequest::getVar('ticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-note-'.$jsst_ticketid) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        $jsst_note = isset($jsst_data['internalnote']) ? $jsst_data['internalnote'] : '';
        JSSTincluder::getJSModel('note')->storeTicketInternalNote($jsst_data, $jsst_note);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . $jsst_ticketid);
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketdetail', 'jssupportticketid'=>$jsst_ticketid));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    function downloadbyid(){
        $jsst_id = JSSTrequest::getVar('id');
        JSSTincluder::getJSModel('note')->getDownloadAttachmentById($jsst_id);
    }

    static function saveeditedtime() {
        $jsst_data = JSSTrequest::get('post');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-edited-time-'.$jsst_data['note-tikcetid']) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('note')->editTime($jsst_data);
        // Where the agent was, not what the agent may do: an agent has
        // jsst_support_ticket_tickets on both sides, so this sent every edit made
        // from the help desk pages into wp-admin. The form posts to the URL of the
        // side it was rendered on, so is_admin() is the honest answer - and it is
        // what savereply() next door already uses. (Roadmap 4.0-CORE-05)
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . $jsst_data['note-tikcetid']);
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_data['note-tikcetid'],'jsstpageid'=>jssupportticket::getPageid()));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_noteController = new JSSTnoteController();
