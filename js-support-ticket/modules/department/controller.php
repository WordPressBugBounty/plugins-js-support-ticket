<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTdepartmentController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'departments');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_departments':
                case 'departments':
                    jssupportticket::$jsst_data['permission_granted'] = true;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Department');
                    }
                    if (jssupportticket::$jsst_data['permission_granted']) {
                        JSSTincluder::getJSModel('department')->getDepartments();
                    }
                    break;
                case 'admin_adddepartment':
                case 'adddepartment':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid');
                    jssupportticket::$jsst_data['permission_granted'] = true;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        $jsst_per_task = ($jsst_id == null) ? 'Add Department' : 'Edit Department';
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($jsst_per_task);
                    }
                    if (jssupportticket::$jsst_data['permission_granted'])
                        JSSTincluder::getJSModel('department')->getDepartmentForForm($jsst_id);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'department');
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

    static function savedepartment() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-department-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('department')->storeDepartment($jsst_data);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=department&jstlay=departments");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function deletedepartment() {
        $jsst_id = JSSTrequest::getVar('departmentid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'delete-department-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('department')->removeDepartment($jsst_id);
        if (is_admin()) {
            $jsst_url = admin_url("admin.php?page=department&jstlay=departments");
        } else {
            $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments'));
        }
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changestatus() {
        $jsst_id = JSSTrequest::getVar('departmentid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-status-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        JSSTincluder::getJSModel('department')->changeStatus($jsst_id);
        $jsst_url = admin_url("admin.php?page=department&jstlay=departments");
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changedefault() {
        $jsst_id = JSSTrequest::getVar('departmentid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-default-'.$jsst_id) ) {
            die( 'Security check Failed' );
        }
        $jsst_default = JSSTrequest::getVar('default',null,0);
        JSSTincluder::getJSModel('department')->changeDefault($jsst_id,$jsst_default);
        $jsst_url = admin_url("admin.php?page=department&jstlay=departments");
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
        $jsst_id = JSSTrequest::getVar('departmentid');
        JSSTincluder::getJSModel('department')->setOrdering($jsst_id);
        $jsst_pagenum = JSSTrequest::getVar('pagenum');
        $jsst_url = "admin.php?page=department&jstlay=departments";
        if ($jsst_pagenum)
            $jsst_url .= '&pagenum=' . $jsst_pagenum;
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_departmentController = new JSSTdepartmentController();
?>
