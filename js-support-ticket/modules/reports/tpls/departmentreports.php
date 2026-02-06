<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
    <?php
if (jssupportticket::$_config['offline'] == 2) {
    if (jssupportticket::$jsst_data['permission_granted'] == 1) {
        if (JSSTincluder::getObjectClass('user')->uid() != 0) {
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                if (jssupportticket::$jsst_data['staff_enabled']) {
                    wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js', array(), jssupportticket::$_config['productversion'], true);
                    wp_register_script( 'ticket-google-charts-handle', '', array(), jssupportticket::$_config['productversion'], true );
                    wp_enqueue_script( 'ticket-google-charts-handle' );
                    $jsst_jssupportticket_js ='';
                        if(!empty(jssupportticket::$jsst_data['pie3d_chart1'])){
                            $jsst_jssupportticket_js ='
                            google.load("visualization", "1", {packages:["corechart"]});
                            google.setOnLoadCallback(drawPie3d1Chart)';
                        }
                        $jsst_jssupportticket_js .="
                        function drawPie3d1Chart() {
                            var data = google.visualization.arrayToDataTable([
                              ['". esc_html(__('Departments','js-support-ticket')) ."', '". esc_html(__('Tickets By Department','js-support-ticket')) ."'],
                              ". wp_kses(jssupportticket::$jsst_data['pie3d_chart1'], JSST_ALLOWED_TAGS) ."
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
                        wp_add_inline_script('ticket-google-charts-handle',$jsst_jssupportticket_js);
                    ?>
                    <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
                    <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
                    <div class="js-ticket-downloads-wrp">
                        <div class="js-ticket-downloads-heading-wrp">
                            <?php echo esc_html(__('Department Reports', 'js-support-ticket')); ?>
                        </div>
                    <?php if(!empty(jssupportticket::$jsst_data['departments_report'])){
                            if(!empty(jssupportticket::$jsst_data['pie3d_chart1'])){ ?>
                                <div class="js-col-md-12 js-ticket-download-content-wrp-mtop">
                                    <div id="pie3d_chart1" style="height:400px;width:100%; float: left;">
                                    </div>
                                </div>
                            <?php } ?>
                                <div class="js-ticket-downloads-wrp">
                                    <div class="js-ticket-downloads-heading-wrp">
                                        <?php echo esc_html(__('Ticket Status By Departments', 'js-support-ticket')); ?>
                                    </div>
                                    <?php foreach(jssupportticket::$jsst_data['departments_report'] AS $jsst_department){ ?>
                                        <div class="js-admin-staff-wrapper js-departmentlist">
                                            <div class="js-col-md-4 nopadding js-festaffreport-img">
                                                <div class="js-col-md-12 jsposition-reletive">
                                                    <div class="departmentname">
                                                        <?php
                                                            echo esc_html(jssupportticket::JSST_getVarValue($jsst_department->departmentname));
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="js-col-md-8 nopadding js-festaffreport-data">
                                                <div class="js-col-md-2 js-col-md-offset-1 js-admin-report-box box1">
                                                    <span class="js-report-box-number"><?php echo esc_html($jsst_department->openticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('New','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                                <div class="js-col-md-2 js-admin-report-box box2">
                                                    <span class="js-report-box-number"><?php echo esc_html($jsst_department->answeredticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('Answered','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                                <div class="js-col-md-2 js-admin-report-box box3">
                                                    <span class="js-report-box-number"><?php echo esc_html($jsst_department->pendingticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('Pending','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                                <div class="js-col-md-2 js-admin-report-box box4">
                                                    <span class="js-report-box-number"><?php echo esc_html($jsst_department->overdueticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('Overdue','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                                <div class="js-col-md-2 js-admin-report-box box5">
                                                    <span class="js-report-box-number"><?php echo esc_html($jsst_department->closeticket); ?></span>
                                                    <span class="js-report-box-title"><?php echo esc_html(__('Closed','js-support-ticket')); ?></span>
                                                    <div class="js-report-box-color"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    } ?>
                                </div>
                                <?php if (jssupportticket::$jsst_data[1]) {
                                        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
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
            $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'reports','jstlay'=>'departmentreports'));
            $jsst_redirect_url = jssupportticketphplib::JSST_safe_encoding($jsst_redirect_url);
            JSSTlayout::getUserGuest($jsst_redirect_url);
        }
    } else { // User permission not granted
    JSSTlayout::getPermissionNotGranted();
    }
} else {
JSSTlayout::getSystemOffline();
} ?>
</div>

