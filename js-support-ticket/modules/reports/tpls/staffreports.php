<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper"><?php
if (jssupportticket::$_config['offline'] == 2) {
    if (jssupportticket::$_data['permission_granted'] == 1) {
        if (JSSTincluder::getObjectClass('user')->uid() != 0) {
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                if (jssupportticket::$_data['staff_enabled']) { ?>

<?php
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css');
    $js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
    wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js');
    wp_register_script( 'ticket-google-charts-handle', '' );
    wp_enqueue_script( 'ticket-google-charts-handle' );
    $jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $('.custom_date').datepicker({
            dateFormat: '". $js_scriptdateformat ."'
        });
    });

    function resetFrom(){
        document.getElementById('jsst-date-start').value = '';
        document.getElementById('jsst-date-end').value = '';
        return true;
    }
    ";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);

    $jssupportticket_js ="
    google.load('visualization', '1', {packages:['corechart']});
    google.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('date', '". esc_html(__('Dates','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('New','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Answered','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Pending','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Overdue','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Closed','js-support-ticket')) ."');
        data.addRows([
            ". wp_kses(jssupportticket::$_data['line_chart_json_array'], JSST_ALLOWED_TAGS) ."
        ]);

        var options = {
          colors:['#1EADD8','#179650','#D98E11','#DB624C','#5F3BBB'],
          curveType: 'function',
          legend: { position: 'bottom' },
          pointSize: 6,
          // This line will make you select an entire row of data at a time
          focusTarget: 'category',
          chartArea: {width:'90%',top:50}
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
        chart.draw(data, options);
    }
";
wp_add_inline_script('ticket-google-charts-handle',$jssupportticket_js);
?>

    <?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
    <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
    <div class="js-ticket-staff-report-wrapper">
        <div class="js-ticket-top-search-wrp">
            <?php /*
            <div class="js-ticket-search-heading-wrp">
                <div class="js-ticket-heading-left">
                    <?php echo esc_html(__('Search Reports', 'js-support-ticket')); ?>
                </div>
            </div> */ ?>
            <div class="js-ticket-search-fields-wrp">
                <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="POST" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reports', 'jstlay'=>'staffreports')),"reports")); ?>">
                    <?php
                    $curdate = date_i18n('Y-m-d');
                    $enddate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
                    $date_start = !empty(jssupportticket::$_data['filter']['jsst-date-start']) ? jssupportticket::$_data['filter']['jsst-date-start'] : $curdate;
                    $date_end = !empty(jssupportticket::$_data['filter']['jsst-date-end']) ? jssupportticket::$_data['filter']['jsst-date-end'] : $enddate;
                    ?>
                    <div class="js-ticket-fields-wrp">
                        <div class="js-ticket-form-field">
                            <?php echo wp_kses(JSSTformfield::text('jsst-date-start', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($date_start)), array('class' => 'custom_date js-ticket-field-input','placeholder' => esc_html(__('Start Date','js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                        </div>
                        <div class="js-ticket-form-field">
                            <?php echo wp_kses(JSSTformfield::text('jsst-date-end', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($date_end)), array('class' => 'custom_date js-ticket-field-input','placeholder' => esc_html(__('End Date','js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                    <div class="js-ticket-search-form-btn-wrp">
                        <?php echo wp_kses(JSSTformfield::submitbutton('jsst-go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'js-search-button', 'onclick' => 'return addSpaces();')), JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::submitbutton('jsst-reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'js-reset-button', 'onclick' => 'return resetFrom();')), JSST_ALLOWED_TAGS); ?>

                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('jshdlay', 'staffreports'), JSST_ALLOWED_TAGS); ?>
                </form>
            </div>
        </div>
        <div class="js-ticket-downloads-wrp">
            <div class="js-ticket-downloads-heading-wrp">
                <?php echo esc_html(__('Reports Statistics', 'js-support-ticket')); ?>
            </div>
            <div id="curve_chart" style="height:400px;width:100%; float: left;"></div>
            <div class="js-admin-report-box-wrapper">
                <div class="js-col-md-2 js-admin-box box1" >
                    <div class="js-col-md-4 js-admin-box-image">
                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>/includes/images/report/ticket_icon.png" />
                    </div>
                    <div class="js-col-md-8 js-admin-box-content">
                        <div class="js-col-md-12 js-admin-box-content-number"><?php echo esc_html(jssupportticket::$_data['ticket_total']['openticket']); ?></div>
                        <div class="js-col-md-12 js-admin-box-content-label"><?php echo esc_html(__('New','js-support-ticket')); ?></div>
                    </div>
                    <div class="js-col-md-12 js-admin-box-label"></div>
                </div>
                <div class="js-col-md-2 js-admin-box jscol-half-offset box2">
                    <div class="js-col-md-4 js-admin-box-image">
                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>/includes/images/report/ticket_answered.png" />
                    </div>
                    <div class="js-col-md-8 js-admin-box-content">
                        <div class="js-col-md-12 js-admin-box-content-number"><?php echo esc_html(jssupportticket::$_data['ticket_total']['answeredticket']); ?></div>
                        <div class="js-col-md-12 js-admin-box-content-label"><?php echo esc_html(__('Answered','js-support-ticket')); ?></div>
                    </div>
                    <div class="js-col-md-12 js-admin-box-label"></div>
                </div>
                <div class="js-col-md-2 js-admin-box jscol-half-offset box3">
                    <div class="js-col-md-4 js-admin-box-image">
                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>/includes/images/report/ticket_pending.png" />
                    </div>
                    <div class="js-col-md-8 js-admin-box-content">
                        <div class="js-col-md-12 js-admin-box-content-number"><?php echo esc_html(jssupportticket::$_data['ticket_total']['pendingticket']); ?></div>
                        <div class="js-col-md-12 js-admin-box-content-label"><?php echo esc_html(__('Pending','js-support-ticket')); ?></div>
                    </div>
                    <div class="js-col-md-12 js-admin-box-label"></div>
                </div>
                <div class="js-col-md-2 js-admin-box jscol-half-offset box4">
                    <div class="js-col-md-4 js-admin-box-image">
                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>/includes/images/report/ticket_overdue.png" />
                    </div>
                    <div class="js-col-md-8 js-admin-box-content">
                        <div class="js-col-md-12 js-admin-box-content-number"><?php echo esc_html(jssupportticket::$_data['ticket_total']['overdueticket']); ?></div>
                        <div class="js-col-md-12 js-admin-box-content-label"><?php echo esc_html(__('Overdue','js-support-ticket')); ?></div>
                    </div>
                    <div class="js-col-md-12 js-admin-box-label"></div>
                </div>
                <div class="js-col-md-2 js-admin-box jscol-half-offset box5">
                    <div class="js-col-md-4 js-admin-box-image">
                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>/includes/images/report/ticket_close.png" />
                    </div>
                    <div class="js-col-md-8 js-admin-box-content">
                        <div class="js-col-md-12 js-admin-box-content-number"><?php echo esc_html(jssupportticket::$_data['ticket_total']['closeticket']); ?></div>
                        <div class="js-col-md-12 js-admin-box-content-label"><?php echo esc_html(__('Closed','js-support-ticket')); ?></div>
                    </div>
                    <div class="js-col-md-12 js-admin-box-label"></div>
                </div>
            </div>
        </div>
        <div class="js-ticket-downloads-wrp">
            <div class="js-ticket-downloads-heading-wrp">
                <?php echo esc_html(__('Agent Reports', 'js-support-ticket')); ?>
            </div>
            <?php
            if(!empty(jssupportticket::$_data['staffs_report'])){
                foreach(jssupportticket::$_data['staffs_report'] AS $agent){ ?>
                    <div class="js-admin-staff-wrapper">
                        <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'reports','jstlay'=>'staffdetailreport','jsst-id'=>$agent->id,'jsst-date-start'=>jssupportticket::$_data['filter']['jsst-date-start'],'jsst-date-end'=>jssupportticket::$_data['filter']['jsst-date-end']))); ?>" class="js-admin-staff-anchor-wrapper">
                        <div class="nopadding js-festaffreport-img">
                            <div class="js-report-staff-image-wrapper">
                                    <?php
                                        if($agent->photo){
                                            $maindir = wp_upload_dir();
                                            $path = $maindir['baseurl'];

                                            $imageurl = $path."/".jssupportticket::$_config['data_directory']."/staffdata/staff_".esc_attr($agent->id)."/".esc_attr($agent->photo);
                                        }else{
                                            $imageurl = JSST_PLUGIN_URL."includes/images/defaultprofile.png";
                                        }
                                    ?>
                                    <img alt="image" class="js-report-staff-pic" src="<?php echo esc_url($imageurl); ?>" />
                            </div>
                            <div class="js-report-staff-cnt-wrapper">
                                <div class="js-report-staff-name">
                                    <?php
                                        if($agent->firstname && $agent->lastname){
                                            $agentname = $agent->firstname . ' ' . $agent->lastname;
                                        }else{
                                            $agentname = $agent->display_name;
                                        }
                                        echo esc_html($agentname);
                                    ?>
                                </div>
                                <div class="js-report-staff-username">
                                    <?php
                                        if($agent->display_name){
                                            $username = $agent->display_name;
                                        }else{
                                            $username = $agent->user_nicename;
                                        }
                                        echo esc_html($username);
                                    ?>
                                </div>
                                <div class="js-report-staff-email">
                                    <?php
                                        if($agent->email){
                                            $email = $agent->email;
                                        }else{
                                            $email = $agent->user_email;
                                        }
                                        echo esc_html($email);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="js-festaffreport-data">
                            <div class="js-col-md-2 js-col-md-offset-1 js-admin-report-box box1">
                                <span class="js-report-box-number"><?php echo esc_html($agent->openticket); ?></span>
                                <span class="js-report-box-title"><?php echo esc_html(__('New','js-support-ticket')); ?></span>
                                <div class="js-report-box-color"></div>
                            </div>
                            <div class="js-col-md-2 js-admin-report-box box2">
                                <span class="js-report-box-number"><?php echo esc_html($agent->answeredticket); ?></span>
                                <span class="js-report-box-title"><?php echo esc_html(__('Answered','js-support-ticket')); ?></span>
                                <div class="js-report-box-color"></div>
                            </div>
                            <div class="js-col-md-2 js-admin-report-box box3">
                                <span class="js-report-box-number"><?php echo esc_html($agent->pendingticket); ?></span>
                                <span class="js-report-box-title"><?php echo esc_html(__('Pending','js-support-ticket')); ?></span>
                                <div class="js-report-box-color"></div>
                            </div>
                            <div class="js-col-md-2 js-admin-report-box box4">
                                <span class="js-report-box-number"><?php echo esc_html($agent->overdueticket); ?></span>
                                <span class="js-report-box-title"><?php echo esc_html(__('Overdue','js-support-ticket')); ?></span>
                                <div class="js-report-box-color"></div>
                            </div>
                            <div class="js-col-md-2 js-admin-report-box box5">
                                <span class="js-report-box-number"><?php echo esc_html($agent->closeticket); ?></span>
                                <span class="js-report-box-title"><?php echo esc_html(__('Closed','js-support-ticket')); ?></span>
                                <div class="js-report-box-color"></div>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php
                }
                if (jssupportticket::$_data[1]) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
                }
            }
            ?>






                    <?php
                } else {
                    JSSTlayout::getStaffMemberDisable();
                }
            } else {
                JSSTlayout::getNotStaffMember();
            }
        } else {
            $redirect_url = jssupportticket::makeUrl(array('jstmod'=>'reports','jstlay'=>'staffreports'));
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
</div>
</div>
