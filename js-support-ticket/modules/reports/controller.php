<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreportsController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'reports');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_reports':
                break;
                case 'admin_staffreport':
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        JSSTincluder::getJSModel('reports')->getStaffReports();
                    }
                break;
                case 'admin_departmentreport':
                    JSSTincluder::getJSModel('reports')->getDepartmentReports();
                break;
                case 'admin_userreport':
                    JSSTincluder::getJSModel('reports')->getUserReports();
                break;
                case 'admin_staffdetailreport':
                case 'staffdetailreport':
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        if(is_admin()){
                            $jsst_id = JSSTrequest::getVar('id');
                            JSSTincluder::getJSModel('reports')->getStaffDetailReportByStaffId($jsst_id);
                        }else{
                            jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Agent Reports');
                            if (jssupportticket::$jsst_data['permission_granted']) {
                                $jsst_id = JSSTrequest::getVar('jsst-id');
                                $jsst_return = JSSTincluder::getJSModel('reports')->getStaffDetailReportByStaffId($jsst_id);
                                if(isset($jsst_return) AND $jsst_return === false)
                                    jssupportticket::$jsst_data['permission_granted'] = false;

                            }
                        }
                    }
                break;
                case 'admin_departmentdetailreport':
                        $jsst_id = JSSTrequest::getVar('id');
                        JSSTincluder::getJSModel('reports')->getDepartmentDetailReportByDepartmentId($jsst_id);
                break;
                case 'admin_stafftimereport':
                    if(in_array('agent',jssupportticket::$_active_addons) && in_array('timetracking',jssupportticket::$_active_addons)){

                        $jsst_id = JSSTrequest::getVar('id');
                        JSSTincluder::getJSModel('reports')->getStaffTimingReportById($jsst_id);
                    }
                break;
                case 'admin_userdetailreport':
                    $jsst_id = JSSTrequest::getVar('id');
                    JSSTincluder::getJSModel('reports')->getStaffDetailReportByUserId($jsst_id);
                break;
                case 'admin_overallreport':
                    JSSTincluder::getJSModel('reports')->getOverallReportData();
                break;
                case 'staffreports':
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Agent Reports');
                        if (jssupportticket::$jsst_data['permission_granted']) {
                            JSSTincluder::getJSModel('reports')->getStaffReportsFE();
                        }
                    }
                break;
                case 'departmentreports':
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Department Reports');
                        if (jssupportticket::$jsst_data['permission_granted']) {
                            JSSTincluder::getJSModel('reports')->getDepartmentReportsFE();
                        }
                    }
                case 'admin_satisfactionreport':
                    if(in_array('feedback', jssupportticket::$_active_addons)){
                        JSSTincluder::getJSModel('feedback')->getSatisfactionReport();
                    }
                break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'reports');
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

}

$jsst_reportsController = new JSSTreportsController();
?>
