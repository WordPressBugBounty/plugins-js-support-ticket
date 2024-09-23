<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
    <?php
if (jssupportticket::$_config['offline'] == 2) {
    if (jssupportticket::$_data['permission_granted'] == 1) {
        if (JSSTincluder::getObjectClass('user')->uid() != 0) {
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                if (jssupportticket::$_data['staff_enabled']) {
                    wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js');
                    wp_register_script( 'ticket-google-charts-handle', '' );
                    wp_enqueue_script( 'ticket-google-charts-handle' );
                    $jssupportticket_js ='';
                        if(!empty(jssupportticket::$_data['pie3d_chart1'])){
                            $jssupportticket_js ='
                            google.load("visualization", "1", {packages:["corechart"]});
                            google.setOnLoadCallback(drawPie3d1Chart)';
                        }
                        $jssupportticket_js .="
                        function drawPie3d1Chart() {
                            var data = google.visualization.arrayToDataTable([
                              ['". esc_html(__('Departments','js-support-ticket')) ."', '". esc_html(__('Tickets By Department','js-support-ticket')) ."'],
                              ". wp_kses(jssupportticket::$_data['pie3d_chart1'], JSST_ALLOWED_TAGS) ."
                            ]);

                            var options = {
                              title: '". esc_html(__('Ticket by departments','js-support-ticket')) ."',
                              chartArea :{width:450,height:350},
                              pieHole:0.4,
                            };

                            var chart = new google.visualization.PieChart(document.getElementById('pie3d_chart1'));
                            chart.draw(data, options);
                        }
                        ";
                        wp_add_inline_script('ticket-google-charts-handle',$jssupportticket_js);
                    ?>
                    <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
                    <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
                    <div class="js-ticket-downloads-wrp">
                        <div class="js-ticket-downloads-heading-wrp">
                            <?php echo esc_html(__('Department Reports', 'js-support-ticket')); ?>
                        </div>
                    <?php if(!empty(jssupportticket::$_data['departments_report'])){
                            if(!empty(jssupportticket::$_data['pie3d_chart1'])){ ?>
                                <div class="js-col-md-12 js-ticket-download-content-wrp-mtop">
                                    <div id="pie3d_chart1" style="height:400px;width:100%; float: left;">
                                    </div>
                                </div>
                            <?php } ?>
                                <div class="js-ticket-downloads-wrp">
                                    <div class="js-ticket-downloads-heading-wrp">
                                        <?php echo esc_html(__('Ticket Status By Departments', 'js-support-ticket')); ?>
                                    </div>
                                    <?php foreach(jssupportticket::$_data['departments_report'] AS $department){ ?>
                                        <div class="js-admin-staff-wrapper js-departmentlist">
                                            <div class="js-col-md-4 nopadding js-festaffreport-img">
                                                <div class="js-col-md-12 jsposition-reletive">
                                                    <div class="departmentname">
                                                        <?php
                                                            echo esc_html(jssupportticket::JSST_getVarValue($department->departmentname));
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="js-col-md-8 nopadding js-festaffreport-data">
                                                <div class="js-col-md-2 js-col-md-offset-1 js-admin-report-box box1">
                                                    <span class="js-report-box-number"><?php echo esc_html($department->openticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('New','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                                <div class="js-col-md-2 js-admin-report-box box2">
                                                    <span class="js-report-box-number"><?php echo esc_html($department->answeredticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('Answered','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                                <div class="js-col-md-2 js-admin-report-box box3">
                                                    <span class="js-report-box-number"><?php echo esc_html($department->pendingticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('Pending','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                                <div class="js-col-md-2 js-admin-report-box box4">
                                                    <span class="js-report-box-number"><?php echo esc_html($department->overdueticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('Overdue','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                                <div class="js-col-md-2 js-admin-report-box box5">
                                                    <span class="js-report-box-number"><?php echo esc_html($department->closeticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('Closed','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    } ?>
                                </div>
                                <?php if (jssupportticket::$_data[1]) {
                                        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
                                    }?>

                    </div>
                    <?php
                        }else{
                             JSSTlayout::getNoRecordFound();
                            }
                        }
                 else {
                    JSSTlayout::getStaffMemberDisable();
                }
            } else {
                JSSTlayout::getNotStaffMember();
            }
        } else {
            $redirect_url = jssupportticket::makeUrl(array('jstmod'=>'reports','jstlay'=>'departmentreports'));
            $redirect_url = jssupportticketphplib::JSST_safe_encoding($redirect_url);
            JSSTlayout::getUserGuest($redirect_url);
        }
    } else { // User permission not granted
    JSSTlayout::getPermissionNotGranted();
    }
} else {
JSSTlayout::getSystemOffline();
} ?>
</div>

