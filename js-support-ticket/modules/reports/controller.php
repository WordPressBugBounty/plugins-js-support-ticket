<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTreportsController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        /* `page=reports` with no layout opens Overall Statistics. It used to
           draw admin_reports.php, an index of four image tiles each linking to
           a report - a screen from before the menus could link to the reports
           themselves, and the reason the WordPress menu's Reports entry landed
           somewhere nobody wanted. The tile page is still there and still
           answers at `page=reports&jstlay=reports`. (Roadmap 5.0-ANA-05) */
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'overallreport');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            /* Which of these an agent gets. (Roadmap 4.5-FE-02)

               `page=reports` is registered for agents now, because
               `JSSTcapability::REPORT_VIEW` maps to `jsst_support_ticket_tickets`,
               every agent role holds it, and `JSSTnavigation` has been offering
               Reports on the agent menu the whole time the slug was administrator
               only - so the link answered "Sorry, you are not allowed to access
               this page", which reads as a permissions bug and was one.

               Opening the page is not opening everything on it. "Read reports"
               means the desk's own numbers: how many tickets there are, how many
               are open, late or waiting. The rest of this module is a per-agent,
               per-customer and per-department breakdown of who is doing how much -
               a management view of colleagues by name, which is a different
               permission this product has never granted an agent. So an agent gets
               Overall Statistics and is sent there from anything else, rather than
               each of the other layouts growing its own check and one of them one
               day being forgotten.

               wp-admin only: the front-end spellings of these reports are gated
               by the add-on's own per-task permissions further down, and those
               are a different and finer answer than this one.

               Inside canaddfile() and not before it, which is not tidiness:
               that check is what tells a screen request apart from a posted
               task, and a task carries no `jstlay` at all - asked any earlier,
               this would read the default layout off a post. */
            if (is_admin() && !current_user_can('manage_options')
                    && $jsst_layout !== 'admin_overallreport') {
                self::refuse(admin_url('admin.php?page=reports&jstlay=overallreport'));
            }
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
                            $jsst_id = absint( JSSTrequest::getVar('id') );
                            JSSTincluder::getJSModel('reports')->getStaffDetailReportByStaffId($jsst_id);
                        }else{
                            jssupportticket::$jsst_data['permission_granted'] = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Agent Reports');
                            if (jssupportticket::$jsst_data['permission_granted']) {
                                $jsst_id = absint( JSSTrequest::getVar('jsst-id') );
                                $jsst_return = JSSTincluder::getJSModel('reports')->getStaffDetailReportByStaffId($jsst_id);
                                if(isset($jsst_return) AND $jsst_return === false)
                                    jssupportticket::$jsst_data['permission_granted'] = false;

                            }
                        }
                    }
                break;
                case 'admin_departmentdetailreport':
                        $jsst_id = absint( JSSTrequest::getVar('id') );
                        JSSTincluder::getJSModel('reports')->getDepartmentDetailReportByDepartmentId($jsst_id);
                break;
                case 'admin_stafftimereport':
                    if(in_array('agent',jssupportticket::$_active_addons) && in_array('timetracking',jssupportticket::$_active_addons)){

                        $jsst_id = absint( JSSTrequest::getVar('id') );
                        JSSTincluder::getJSModel('reports')->getStaffTimingReportById($jsst_id);
                    }
                break;
                case 'admin_userdetailreport':
                    $jsst_id = absint( JSSTrequest::getVar('id') );
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
                break;
                /* Retired in 6.5, and answering anyway. (Roadmap 5.0-ANA-03)

                   It read the Feedback add-on's whole table with no period and
                   no filter and drew one average as a percentage. Satisfaction
                   answers the same question from the one table satisfaction is
                   now kept in - over a period, with a response rate, the
                   reasons and the per-agent split beside it - so this address
                   goes there rather than drawing a second, smaller answer, and
                   a smaller one computed differently: this screen showed the
                   mean scaled to a percentage while that screen shows the
                   share of people who were happy, and two menu entries a click
                   apart disagreeing about one desk's score is a support ticket
                   waiting to happen.

                   The address keeps working because bookmarks and links in old
                   support replies do. Same as JSSTproController does for a
                   layout a bundle has taken over. */
                case 'admin_satisfactionreport':
                    /* To the screen if the screen is there. It ships in the
                       Customer Experience add-on, and a desk running the old
                       Feedback add-on without it has the verdicts but not the
                       page that reads them - so this bookmark would have been
                       forwarded to that add-on's `default: exit;` and a white
                       screen. Reports itself always answers, and is where
                       somebody looking for a report should land.
                       (Roadmap 6.5-ECO-01) */
                    JSSTincluder::moved(JSSTincluder::screenExists('feedback', 'satisfaction')
                        ? admin_url('admin.php?page=feedback&jstlay=satisfaction')
                        : admin_url('admin.php?page=reports&jstlay=overallreport'));
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'reports');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    /**
     * Turn somebody away without a page of PHP warnings.
     *
     * A module controller runs from inside the admin page callback, which is
     * after `admin-header.php` has been written out - so a plain
     * `wp_safe_redirect()` here is two `header()` calls against a response that
     * has already begun, which PHP reports as two "headers already sent"
     * warnings and WordPress then renders nothing else into. The redirect is
     * still the right answer whenever it can still be sent; when it cannot,
     * this says so on the page instead of leaving a warning to be read as the
     * refusal.
     */
    private static function refuse($jsst_url) {
        if (!headers_sent()) {
            wp_safe_redirect($jsst_url);
            exit;
        }
        echo '<div class="notice notice-error"><p>'
            . esc_html(__('You are not allowed to open this screen.', 'js-support-ticket'))
            . ' <a href="' . esc_url($jsst_url) . '">'
            . esc_html(__('Go back to the help desk.', 'js-support-ticket'))
            . '</a></p></div>';
        exit;
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
