<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTjssupportticketController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'controlpanel');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_controlpanel':
			        include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
			        JSSTupdates::checkUpdates();
                    JSSTincluder::getJSModel('jssupportticket')->getControlPanelDataAdmin();
                    break;
                case 'controlpanel':
                    JSSTincluder::getJSModel('jssupportticket')->getControlPanelData();
                    include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
                    JSSTupdates::checkUpdates('303');
                    JSSTincluder::getJSModel('jssupportticket')->updateColorFile();
                    //JSSTincluder::getJSModel('jssupportticket')->getStaffControlPanelData();
                    break;
                case 'admin_shortcodes':
                    JSSTincluder::getJSModel('jssupportticket')->getShortCodeData();
                    break;
                case 'admin_aboutus':
                    break;
                case 'admin_addonstatus':
                    JSSTincluder::getJSModel('jssupportticket')->jsst_check_license_status();
                    break;
                case 'admin_help':
                    break;
                case 'admin_translations':
                    break;
                case 'login':
                    break;
                case 'userregister':
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'jssupportticket');
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

    static function addmissingusers() {
        if(!is_admin())
            return false;
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'add-missing-users') ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('jssupportticket')->addMissingUsers();
        $jsst_url = admin_url("admin.php?page=jssupportticket");
        wp_safe_redirect($jsst_url);
        exit;
    }

    function saveordering(){
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-ordering') ) {
            die( 'Security check Failed' );
        }
        $jsst_post = JSSTrequest::get('post');

        JSSTincluder::getJSModel('jssupportticket')->storeOrderingFromPage($jsst_post);
        if($jsst_post['ordering_for'] == 'department'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=department&jstlay=departments");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments'));
            }
        }elseif($jsst_post['ordering_for'] == 'priority'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=priority&jstlay=priorities");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'priority', 'jstlay'=>'priorities'));
            }
        }elseif($jsst_post['ordering_for'] == 'status'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=status&jstlay=statuses");
            }
        }elseif($jsst_post['ordering_for'] == 'product'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=product&jstlay=products");
            }
        }elseif($jsst_post['ordering_for'] == 'fieldordering'){
            $jsst_fieldfor = JSSTrequest::getVar('fieldfor');
            if($jsst_fieldfor == ''){
                $jsst_fieldfor = jssupportticket::$jsst_data['fieldfor'];
            }
            $jsst_formid = JSSTrequest::getVar('formid');
            if($jsst_formid == ''){
                $jsst_formid = jssupportticket::$jsst_data['formid'];
            }
            $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        }elseif($jsst_post['ordering_for'] == 'announcement'){
            if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=announcement&jstlay=announcements");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'staffannouncements'));
        }
        }elseif($jsst_post['ordering_for'] == 'article'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=knowledgebase&jstlay=listarticles");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'stafflistarticles'));
            }
        }elseif($jsst_post['ordering_for'] == 'download'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=download&jstlay=downloads");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'staffdownloads'));
            }
        }elseif($jsst_post['ordering_for'] == 'faq'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=faq&jstlay=faqs");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'stafffaqs'));
            }
        }elseif($jsst_post['ordering_for'] == 'helptopic'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=helptopic&jstlay=helptopics");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics'));
            }
        }elseif($jsst_post['ordering_for'] == 'multiform'){
            if (is_admin()) {
                $jsst_url = admin_url("admin.php?page=multiform&jstlay=multiform");
            } else {
                $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'multiform', 'jstlay'=>'staffmultiform'));
            }
        }

        wp_safe_redirect($jsst_url);
        exit;
    }
}

$jsst_controlpanelController = new JSSTjssupportticketController();
?>
