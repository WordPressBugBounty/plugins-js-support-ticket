<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreportsController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $layout = JSSTrequest::getLayout('jstlay', null, 'reports');
        if (self::canaddfile()) {
            switch ($layout) {
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
                            $id = JSSTrequest::getVar('id');
                            JSSTincluder::getJSModel('reports')->getStaffDetailReportByStaffId($id);
                        }else{
                            jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Agent Reports');
                            if (jssupportticket::$_data['permission_granted']) {
                                $id = JSSTrequest::getVar('jsst-id');
                                $return = JSSTincluder::getJSModel('reports')->getStaffDetailReportByStaffId($id);
                                if(isset($return) AND $return === false)
                                    jssupportticket::$_data['permission_granted'] = false;

                            }
                        }
                    }
                break;
                case 'admin_departmentdetailreport':
                        $id = JSSTrequest::getVar('id');
                        JSSTincluder::getJSModel('reports')->getDepartmentDetailReportByDepartmentId($id);
                break;
                case 'admin_stafftimereport':
                    if(in_array('agent',jssupportticket::$_active_addons) && in_array('timetracking',jssupportticket::$_active_addons)){

                        $id = JSSTrequest::getVar('id');
                        JSSTincluder::getJSModel('reports')->getStaffTimingReportById($id);
                    }
                break;
                case 'admin_userdetailreport':
                    $id = JSSTrequest::getVar('id');
                    JSSTincluder::getJSModel('reports')->getStaffDetailReportByUserId($id);
                break;
                case 'admin_overallreport':
                    JSSTincluder::getJSModel('reports')->getOverallReportData();
                break;
                case 'staffreports':
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Agent Reports');
                        if (jssupportticket::$_data['permission_granted']) {
                            JSSTincluder::getJSModel('reports')->getStaffReportsFE();
                        }
                    }
                break;
                case 'departmentreports':
                    if(in_array('agent',jssupportticket::$_active_addons)){
                        jssupportticket::$_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Department Reports');
                        if (jssupportticket::$_data['permission_granted']) {
                            JSSTincluder::getJSModel('reports')->getDepartmentReportsFE();
                        }
                    }
                case 'admin_satisfactionreport':
                    if(in_array('feedback', jssupportticket::$_active_addons)){
                        JSSTincluder::getJSModel('feedback')->getSatisfactionReport();
                    }
                break;
            }
            $module = (is_admin()) ? 'page' : 'jstmod';
            $module = JSSTrequest::getVar($module, null, 'reports');
            JSSTincluder::include_file($layout, $module);
        }
    }

    function canaddfile() {
        if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket')
            return false;
        elseif (isset($_GET['action']) && $_GET['action'] == 'jstask')
            return false;
        else
            return true;
    }

}

$reportsController = new JSSTreportsController();
?>
