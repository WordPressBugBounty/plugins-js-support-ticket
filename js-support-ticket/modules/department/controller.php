<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTdepartmentController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'departments');
        jssupportticket::$_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile()) {
            switch ($layout) {
                case 'admin_departments':
                case 'departments':
                    jssupportticket::$_data['permission_granted'] = true;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Department');
                    }
                    if (jssupportticket::$_data['permission_granted']) {
                        JSSTincluder::getJSModel('department')->getDepartments();
                    }
                    break;
                case 'admin_adddepartment':
                case 'adddepartment':
                    $id = JSSTrequest::getVar('jssupportticketid');
                    jssupportticket::$_data['permission_granted'] = true;
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        $per_task = ($id == null) ? 'Add Department' : 'Edit Department';
                        jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask($per_task);
                    }
                    if (jssupportticket::$_data['permission_granted'])
                        JSSTincluder::getJSModel('department')->getDepartmentForForm($id);
                    break;
                default:
                    exit;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'department');
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

    static function savedepartment() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-department') ) {
            die( 'Security check Failed' );
        }
        $data = JSSTrequest::get('post');
        JSSTincluder::getJSModel('department')->storeDepartment($data);
        if (is_admin()) {
            $url = admin_url("admin.php?page=department&jstlay=departments");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments'));
        }
        wp_redirect($url);
        exit;
    }

    static function deletedepartment() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'delete-department') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('departmentid');
        JSSTincluder::getJSModel('department')->removeDepartment($id);
        if (is_admin()) {
            $url = admin_url("admin.php?page=department&jstlay=departments");
        } else {
            $url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments'));
        }
        wp_redirect($url);
        exit;
    }

    static function changestatus() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-status') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('departmentid');
        JSSTincluder::getJSModel('department')->changeStatus($id);
        $url = admin_url("admin.php?page=department&jstlay=departments");
        $pagenum = JSSTrequest::getVar('pagenum');
        if ($pagenum)
            $url .= '&pagenum=' . $pagenum;
        wp_redirect($url);
        exit;
    }

    static function changedefault() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'change-default') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('departmentid');
        $default = JSSTrequest::getVar('default',null,0);
        JSSTincluder::getJSModel('department')->changeDefault($id,$default);
        $url = admin_url("admin.php?page=department&jstlay=departments");
        $pagenum = JSSTrequest::getVar('pagenum');
        if ($pagenum)
            $url .= '&pagenum=' . $pagenum;
        wp_redirect($url);
        exit;
    }

    static function ordering() {
        $nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'ordering') ) {
            die( 'Security check Failed' );
        }
        $id = JSSTrequest::getVar('departmentid');
        JSSTincluder::getJSModel('department')->setOrdering($id);
        $pagenum = JSSTrequest::getVar('pagenum');
        $url = "admin.php?page=department&jstlay=departments";
        if ($pagenum)
            $url .= '&pagenum=' . $pagenum;
        wp_redirect($url);
        exit;
    }

}

$departmentController = new JSSTdepartmentController();
?>
