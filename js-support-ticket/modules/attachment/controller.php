<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTattachmentController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'getattachments');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'getattachments':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid', 'get', null);
                    JSSTincluder::getJSModel('replies')->getrepliesForForm($jsst_id);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'attachment');
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

    static function saveattachments() {
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('attachment')->storeAttachments($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=" . JSSTrequest::getVar('ticketid'));
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'replies', 'jstlay'=>'replies'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deleteattachment() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-attachement-'.$jsst_id)  && !is_admin()) {
            die( 'Security check Failed' );
        }
        $jsst_call_from = JSSTrequest::getVar('call_from','',1);
        JSSTincluder::getJSModel('attachment')->removeAttachment($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=ticket&jstlay=addticket&jssupportticketid=" . JSSTrequest::getVar('ticketid'));
        } else {
            if($jsst_call_from == 2){
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffaddticket','jssupportticketid'=>JSSTrequest::getVar('ticketid')));
            }else{
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'replies', 'jstlay'=>'replies'));
            }
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_attachmentController = new JSSTattachmentController();
?>
