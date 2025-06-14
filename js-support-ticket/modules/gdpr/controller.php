<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTgdprController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'gdpr');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($layout)) {
            switch ($layout) {
                case 'admin_gdprfields':
                    JSSTincluder::getJSModel('gdpr')->getGDPRFeilds();
                    break;
                case 'admin_addgdprfield':
                    $id = JSSTrequest::getVar('jssupportticketid');
                    JSSTincluder::getJSModel('fieldordering')->getUserFieldbyId($id,3);
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
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'gdpr');
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

    static function savegdprfield() {
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $id = JSSTrequest::getVar('id');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-gdprfield-'.$id) ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('fieldordering')->storeUserField($data);
        $url = admin_url("admin.php?page=gdpr&jstlay=gdprfields");
        wp_redirect($url);
        exit;
    }

    static function saveusereraserequest() {
        $id = JSSTrequest::getVar('id');
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-usereraserequest-'.$id) ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        if($data['subject'] == "" || $data['message'] == ""){

            JSSTformfield::setFormData($data);
            JSSTmessage::setMessage(esc_html(__('Please fill required fields.', 'js-support-ticket')), 'error');
        }else{
            JSSTincluder::getJSModel('gdpr')->storeUserEraseRequest($data);
        }
        $url = jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest'));
        
        wp_redirect($url);
        exit;
    }

    static function deletegdpr() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-gdpr') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('gdprid');
        JSSTincluder::getJSModel('fieldordering')->deleteUserField($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=gdpr&jstlay=gdprfields");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest'));
        }
        wp_redirect($url);
        exit;
    }

    static function removeusereraserequest() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-usereraserequest') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('jssupportticketid');
        JSSTincluder::getJSModel('gdpr')->deleteUserEraseRequest($id);
        $url = jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest'));
        wp_redirect($url);
        exit;
    }

    static function exportusereraserequest() {
        $nonce = JSSTrequest::getVar('_wpnonce');

        if (! wp_verify_nonce( $nonce, 'export-usereraserequest') ) {
            die( 'Security check Failed' );
        }
        // get current user ID by function due to security reasons
        $uid  = JSSTincluder::getObjectClass('user')->uid();
        $return_value = JSSTincluder::getJSModel('gdpr')->setUserExportByuid($uid);
        if (!empty($return_value)) {
            // Push the report now!
            $msg = esc_html(__('User Data', 'js-support-ticket'));
            $name = 'export-overalll-reports';
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=" . $name . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Lacation: excel.htm?id=yes");
            print wp_kses($return_value, JSST_ALLOWED_TAGS);
            exit;
        }
        JSSTmessage::setMessage(esc_html(__('There was no record found', 'js-support-ticket')), 'error');
        if (is_admin()) {
            $url = admin_url("admin.php?page=gdpr&jstlay=erasedatarequests");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest'));
        }
        wp_redirect($url);
        die();
    }

    static function deleteuserdata() {
        $nonce = JSSTrequest::getVar('_wpnonce');

        if (! wp_verify_nonce( $nonce, 'delete-userdata') ) {
            die( 'Security check Failed' );
        }
        $uid  = JSSTrequest::getVar('jssupportticketid');
        $return_value = JSSTincluder::getJSModel('gdpr')->deleteUserData($uid);
        $url = admin_url("admin.php?page=gdpr&jstlay=erasedatarequests");
        wp_redirect($url);
        die();
    }

    static function eraseidentifyinguserdata() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'erase-userdata') ) {
            die( 'Security check Failed' );
        }
        $uid  = JSSTrequest::getVar('jssupportticketid');
        $return_value = JSSTincluder::getJSModel('gdpr')->anonymizeUserData($uid);
        $url = admin_url("admin.php?page=gdpr&jstlay=erasedatarequests");
        wp_redirect($url);
        die();
    }

}
$gdprController = new JSSTgdprController();
?>
