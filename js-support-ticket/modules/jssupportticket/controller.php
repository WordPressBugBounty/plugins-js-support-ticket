<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTjssupportticketController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'controlpanel');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_controlpanel':
			        include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
			        JSSTupdates::checkUpdates();
                    JSSTincluder::getJSModel('jssupportticket')->getControlPanelDataAdmin();
                    break;
                case 'controlpanel':
                    JSSTincluder::getJSModel('jssupportticket')->getControlPanelData();
                    include_once JSST_PLUGIN_PATH . 'includes/updates/updates.php';
                    JSSTupdates::checkUpdates('284');
                    JSSTincluder::getJSModel('jssupportticket')->updateColorFile();
                    //JSSTincluder::getJSModel('jssupportticket')->getStaffControlPanelData();
                    break;
                case 'admin_shortcodes':
                    JSSTincluder::getJSModel('jssupportticket')->getShortCodeData();
                    break;
                case 'admin_aboutus':
                    break;
                case 'admin_addonstatus':
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
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'jssupportticket');
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

    static function addmissingusers() {
        if(!is_admin())
            return false;
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'add-missing-users') ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('jssupportticket')->addMissingUsers();
        $url = admin_url("admin.php?page=jssupportticket");
        wp_redirect($url);
        exit;
    }

    function saveordering(){
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-ordering') ) {
            die( 'Security check Failed' );
        }
        $post = JSSTrequest::get('post');

        JSSTincluder::getJSModel('jssupportticket')->storeOrderingFromPage($post);
        if($post['ordering_for'] == 'department'){
            if (is_admin()) {
                $url = admin_url("admin.php?page=department&jstlay=departments");
            } else {
                $url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments'));
            }
        }elseif($post['ordering_for'] == 'priority'){
            if (is_admin()) {
                $url = admin_url("admin.php?page=priority&jstlay=priorities");
            } else {
                $url = jssupportticket::makeUrl(array('jstmod'=>'priority', 'jstlay'=>'priorities'));
            }
        }elseif($post['ordering_for'] == 'fieldordering'){
            $fieldfor = JSSTrequest::getVar('fieldfor');
            if($fieldfor == ''){
                $fieldfor = jssupportticket::$_data['fieldfor'];
            }
            $formid = JSSTrequest::getVar('formid');
            if($formid == ''){
                $formid = jssupportticket::$_data['formid'];
            }
            $url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($fieldfor)."&formid=".esc_attr($formid));
        }elseif($post['ordering_for'] == 'announcement'){
            if (is_admin()) {
            $url = admin_url("admin.php?page=announcement&jstlay=announcements");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'staffannouncements'));
        }
        }elseif($post['ordering_for'] == 'article'){
            if (is_admin()) {
                $url = admin_url("admin.php?page=knowledgebase&jstlay=listarticles");
            } else {
                $url = jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'stafflistarticles'));
            }
        }elseif($post['ordering_for'] == 'download'){
            if (is_admin()) {
                $url = admin_url("admin.php?page=download&jstlay=downloads");
            } else {
                $url = jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'staffdownloads'));
            }
        }elseif($post['ordering_for'] == 'faq'){
            if (is_admin()) {
                $url = admin_url("admin.php?page=faq&jstlay=faqs");
            } else {
                $url = jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'stafffaqs'));
            }
        }elseif($post['ordering_for'] == 'helptopic'){
            if (is_admin()) {
                $url = admin_url("admin.php?page=helptopic&jstlay=helptopics");
            } else {
                $url = jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics'));
            }
        }elseif($post['ordering_for'] == 'multiform'){
            if (is_admin()) {
                $url = admin_url("admin.php?page=multiform&jstlay=multiform");
            } else {
                $url = jssupportticket::makeUrl(array('jstmod'=>'multiform', 'jstlay'=>'staffmultiform'));
            }
        }

        wp_redirect($url);
        exit;
    }
}

$controlpanelController = new JSSTjssupportticketController();
?>
