<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Help topics controller — part of the free core. (Roadmap 4.0-CORE-06)
 *
 * The add-on's controller unchanged: same layouts, tasks, nonce names and
 * redirects, so existing links, bookmarks and forms keep working whichever side
 * is serving the feature. (Roadmap 4.0-CORE-19)
 */
class JSSThelptopicController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'helptopics');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                /* listing of all help topics */
                case 'admin_helptopics':
                case 'agenthelptopics':
                    jssupportticket::$jsst_data['permission_granted'] = true;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Help Topic');
                    }
                    if (jssupportticket::$jsst_data['permission_granted']) {
                        JSSTincluder::getJSModel('helptopic')->getHelpTopics();
                    }
                    break;
                case 'admin_addhelptopic':
                case 'addhelptopic':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid');
                    jssupportticket::$jsst_data['permission_granted'] = true;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        $jsst_per_task = ($jsst_id == null) ? 'Add Help Topic' : 'Edit Help Topic';
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_per_task);
                    }
                    if (jssupportticket::$jsst_data['permission_granted']) {
                        JSSTincluder::getJSModel('helptopic')->getHelpTopicForForm($jsst_id);
                    }

                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'helptopic');
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

    static function savehelptopic() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-help-topic-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('helptopic')->storeHelpTopic($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=helptopic&jstlay=helptopics");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletehelptopic() {
        $jsst_id = JSSTrequest::getVar('helptopicid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-helptopic-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('helptopic')->removeHelpTopic($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=helptopic&jstlay=helptopics");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changestatus() {
        $jsst_id = JSSTrequest::getVar('helptopicid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('helptopic')->changeStatus($jsst_id);
        $jsst_url = admin_url("admin.php?page=helptopic&jstlay=helptopics");
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function ordering() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'ordering') ) {
            die( 'Security check Failed' );
        }
        $jsst_id = JSSTrequest::getVar('helptopicid');
        JSSTincluder::getJSModel('helptopic')->setOrdering($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=helptopic&jstlay=helptopics");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_helptopicController = new JSSThelptopicController();
