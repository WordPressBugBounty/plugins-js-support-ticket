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
                if (jssupportticket::$_data['staff_enabled']) { ?>
    <!-- admin -->
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
        <div class="js-ticket-search-fields-wrp">
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="POST" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reports', 'jstlay'=>'staffdetailreport')),"staff-detail-report")); ?>">
                <?php
                $curdate = date_i18n('Y-m-d');
                $enddate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
                $date_start = !empty(jssupportticket::$_data['filter']['jsst-date-start']) ? jssupportticket::$_data['filter']['jsst-date-start'] : $curdate;
                $date_end = !empty(jssupportticket::$_data['filter']['jsst-date-end']) ? jssupportticket::$_data['filter']['jsst-date-end'] : $enddate; ?>
                <?php echo wp_kses("<input type='hidden' name='jsst-id' value='" . esc_attr(jssupportticket::$_data['staff_report']->id) . "'/>", JSST_ALLOWED_TAGS); ?>
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
                <?php echo wp_kses(JSSTformfield::hidden('jshdlay', 'staffdetailreport'), JSST_ALLOWED_TAGS); ?>
            </form>
        </div>
    </div>
    <div id="curve_chart" style="height:400px;width:100%;float: left; "></div>
</div>
<div class="js-ticket-downloads-wrp">
    <div class="js-ticket-downloads-heading-wrp">
        <?php echo esc_html(__('Agent Report', 'js-support-ticket')); ?>
    </div>
    <?php
        $agent = jssupportticket::$_data['staff_report'];
        if(!empty($agent)){ ?>
            <div class="js-admin-staff-wrapper padding">
                <div class="js-col-md-4 nopadding js-festaffreport-img">
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
                    <div class="js-report-staff-cnt">
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
                <div class="js-col-md-8 nopadding js-festaffreport-data">
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
            </div>
        <?php
        } ?>
</div>
<?php
    if(!empty(jssupportticket::$_data['staff_tickets'])){ ?>
        <div class="js-ticket-downloads-wrp">
            <div class="js-ticket-downloads-heading-wrp">
                <?php echo esc_html(__('Agent Tickets', 'js-support-ticket')); ?>
            </div>
            <div class="js-ticket-download-content-wrp js-ticket-download-content-wrp-mtop">
                <div class="js-ticket-table-wrp">
                    <div class="js-ticket-table-header">
                        <div class="js-ticket-table-header-col js-col-md-4 js-col-xs-4"><?php echo esc_html(__('Subject', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-table-header-col js-col-md-3 js-col-xs-3"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-table-header-col js-col-md-3 js-col-xs-3"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-table-header-col js-col-md-2 js-col-xs-2"><?php echo esc_html(__('Created', 'js-support-ticket')); ?></div>
                    </div>
                    <div class="js-ticket-table-body">
                        <?php
                            foreach(jssupportticket::$_data['staff_tickets'] AS $ticket){ ?>
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-table-body-col js-col-md-4 js-col-xs-4">
                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Subject','js-support-ticket')); ?>:</span>
                                    <span class="js-ticket-title"><a class="js-ticket-title-anchor" target="_blank" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$ticket->id))); ?>"><?php echo esc_html($ticket->subject); ?></a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-col-md-3 js-col-xs-3">
                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Status','js-support-ticket')); ?>:</span>
                                    <?php
                                        // 0 -> New Ticket
                                        // 1 -> Waiting admin/staff reply
                                        // 2 -> in progress
                                        // 3 -> waiting for customer reply
                                        // 4 -> close ticket
                                        switch($ticket->status){
                                            case 0:
                                                $status = '<font color="#1EADD8">'. esc_html(__('New','js-support-ticket')).'</font>';
                                                if($ticket->isoverdue == 1)
                                                    $status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
                                            break;
                                            case 1:
                                                $status = '<font color="#D98E11">'. esc_html(__('Pending','js-support-ticket')).'</font>';
                                                if($ticket->isoverdue == 1)
                                                    $status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
                                            break;
                                            case 2:
                                                $status = '<font color="#D98E11">'. esc_html(__('In Progress','js-support-ticket')).'</font>';
                                                if($ticket->isoverdue == 1)
                                                    $status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
                                            break;
                                            case 3:
                                                $status = '<font color="#179650">'. esc_html(__('Answered','js-support-ticket')).'</font>';
                                                if($ticket->isoverdue == 1)
                                                    $status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
                                            break;
                                            case 4:
                                                $status = '<font color="#5F3BBB">'. esc_html(__('Closed','js-support-ticket')).'</font>';
                                            break;
                                            case 5:
                                                $status = '<font color="#5F3BBB">'. esc_html(__('Merged','js-support-ticket')).'</font>';
                                            break;
                                        }
                                        echo wp_kses($status, JSST_ALLOWED_TAGS);
                                    ?>
                                </div>
                                <div class="js-ticket-table-body-col js-col-md-3 js-col-xs-3">
                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Priority','js-support-ticket')); ?>:</span>
                                    <span class="js-ticket-priority" style="background-color:<?php echo esc_attr($ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?></span>
                                </div>
                                <div class="js-ticket-table-body-col js-col-md-2 js-col-xs-2">
                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Created','js-support-ticket')); ?>:</span>
                                    <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->created))); ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if (jssupportticket::$_data[1]) {
            echo '<div class="tablenav"><div class="tablenav-pages"' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
        }
    } else {
        JSSTlayout::getNoRecordFound();
    }
    ?>
    <!-- END admin -->
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
