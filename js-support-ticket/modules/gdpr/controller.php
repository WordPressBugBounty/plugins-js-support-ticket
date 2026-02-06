<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTgdprController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'gdpr');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_gdprfields':
                    JSSTincluder::getJSModel('gdpr')->getGDPRFeilds();
                    break;
                case 'admin_addgdprfield':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid');
                    JSSTincluder::getJSModel('fieldordering')->getUserFieldbyId($jsst_id,3);
                    break;
                case 'admin_erasedatarequests':
                    JSSTincluder::getJSModel('gdpr')->getEraseDataRequests();
                    break;
                case 'adderasedatarequest':
                    JSSTincluder::getJSModel('gdpr')->getUserEraseDataRequest();
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'gdpr');
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

    static function savegdprfield() {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-gdprfield-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('fieldordering')->storeUserField($jsst_data);
        $jsst_url = admin_url("admin.php?page=gdpr&jstlay=gdprfields");
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function saveusereraserequest() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-usereraserequest-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        if($jsst_data['subject'] == "" || $jsst_data['message'] == ""){

            JSSTformfield::setFormData($jsst_data);
            JSSTmessage::setMessage(esc_html(__('Please fill required fields.', 'js-support-ticket')), 'error');
        }else{
            JSSTincluder::getJSModel('gdpr')->storeUserEraseRequest($jsst_data);
        }
        $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest'));
        
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletegdpr() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-gdpr') ) {
            die( 'Security check Failed' );
        }
        $jsst_id = JSSTrequest::getVar('gdprid');
        JSSTincluder::getJSModel('fieldordering')->deleteUserField($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=gdpr&jstlay=gdprfields");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function removeusereraserequest() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-usereraserequest') ) {
            die( 'Security check Failed' );
        }
        $jsst_id = JSSTrequest::getVar('jssupportticketid');
        JSSTincluder::getJSModel('gdpr')->deleteUserEraseRequest($jsst_id);
        $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest'));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function exportusereraserequest() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');

        if (! wp_verify_nonce( $jsst_nonce, 'export-usereraserequest') ) {
            die( 'Security check Failed' );
        }
        // get current user ID by function due to security reasons
        $jsst_uid  = JSSTincluder::getObjectClass('user')->uid();
        $jsst_return_value = JSSTincluder::getJSModel('gdpr')->setUserExportByuid($jsst_uid);
        if (!empty($jsst_return_value)) {
            // Push the report now!
            $jsst_msg = esc_html(__('User Data', 'js-support-ticket'));
            $jsst_name = 'export-overalll-reports';
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=" . $jsst_name . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Lacation: excel.htm?id=yes");
            print wp_kses($jsst_return_value, JSST_ALLOWED_TAGS);
            exit;
        }
        JSSTmessage::setMessage(esc_html(__('There was no record found', 'js-support-ticket')), 'error');
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=gdpr&jstlay=erasedatarequests");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest'));
        }
        wp_safe_redirect($jsst_url);
        die();
    }

    static function deleteuserdata() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');

        if (! wp_verify_nonce( $jsst_nonce, 'delete-userdata') ) {
            die( 'Security check Failed' );
        }
        $jsst_uid  = JSSTrequest::getVar('jssupportticketid');
        $jsst_return_value = JSSTincluder::getJSModel('gdpr')->deleteUserData($jsst_uid);
        $jsst_url = admin_url("admin.php?page=gdpr&jstlay=erasedatarequests");
        wp_safe_redirect($jsst_url);
        die();
    }

    static function eraseidentifyinguserdata() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'erase-userdata') ) {
            die( 'Security check Failed' );
        }
        $jsst_uid  = JSSTrequest::getVar('jssupportticketid');
        $jsst_return_value = JSSTincluder::getJSModel('gdpr')->anonymizeUserData($jsst_uid);
        $jsst_url = admin_url("admin.php?page=gdpr&jstlay=erasedatarequests");
        wp_safe_redirect($jsst_url);
        die();
    }

}
$jsst_gdprController = new JSSTgdprController();
?>
