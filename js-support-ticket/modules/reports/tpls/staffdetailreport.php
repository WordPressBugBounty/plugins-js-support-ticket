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
                if (jssupportticket::$jsst_data['staff_enabled']) { ?>
    <!-- admin -->
<?php
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
$jsst_js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js', array(), jssupportticket::$_config['productversion'], true);
wp_register_script( 'ticket-google-charts-handle', '', array(), jssupportticket::$_config['productversion'], true );
wp_enqueue_script( 'ticket-google-charts-handle' );
$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $('.custom_date').datepicker({
            dateFormat: '". $jsst_js_scriptdateformat ."'
        });
    });

    function resetFrom(){
        document.getElementById('jsst-date-start').value = '';
        document.getElementById('jsst-date-end').value = '';
        return true;
    }
    ";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
    $jsst_jssupportticket_js ="
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
            ". wp_kses(jssupportticket::$jsst_data['line_chart_json_array'], JSST_ALLOWED_TAGS) ."
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
wp_add_inline_script('ticket-google-charts-handle',$jsst_jssupportticket_js);
?>

<?php /* JSSTbreadcrumbs::getBreadcrumbs(); */ ?>
<?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
<div class="js-ticket-staff-report-wrapper">
    <div class="js-ticket-top-search-wrp">
        <div class="js-ticket-search-fields-wrp">
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="POST" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reports', 'jstlay'=>'staffdetailreport','jsst-id'=>jssupportticket::$jsst_data['staff_report']->id)),"staff-detail-report")); ?>">
                <?php
                $jsst_curdate = date_i18n('Y-m-d');
                $jsst_enddate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
                $jsst_date_start = !empty(jssupportticket::$jsst_data['filter']['jsst-date-start']) ? jssupportticket::$jsst_data['filter']['jsst-date-start'] : $jsst_curdate;
                $jsst_date_end = !empty(jssupportticket::$jsst_data['filter']['jsst-date-end']) ? jssupportticket::$jsst_data['filter']['jsst-date-end'] : $jsst_enddate; ?>
                <?php echo wp_kses("<input type='hidden' name='jsst-id' value='" . esc_attr(jssupportticket::$jsst_data['staff_report']->id) . "'/>", JSST_ALLOWED_TAGS); ?>
                <div class="js-ticket-fields-wrp">
                    <div class="js-ticket-form-field">
                        <?php echo wp_kses(JSSTformfield::text('jsst-date-start', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_date_start)), array('class' => 'custom_date js-ticket-field-input','placeholder' => esc_html(__('Start Date','js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                    </div>
                    <div class="js-ticket-form-field">
                        <?php echo wp_kses(JSSTformfield::text('jsst-date-end', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_date_end)), array('class' => 'custom_date js-ticket-field-input','placeholder' => esc_html(__('End Date','js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
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
        $jsst_agent = jssupportticket::$jsst_data['staff_report'];
        if(!empty($jsst_agent)){ ?>
            <div class="js-admin-staff-wrapper padding">
                <div class="js-col-md-4 nopadding js-festaffreport-img">
                    <div class="js-report-staff-image-wrapper">
                        <?php
                            if($jsst_agent->photo){
                                $jsst_maindir = wp_upload_dir();
                                $jsst_path = $jsst_maindir['baseurl'];

                                $jsst_imageurl = $jsst_path."/".jssupportticket::$_config['data_directory']."/staffdata/staff_".esc_attr($jsst_agent->id)."/".esc_attr($jsst_agent->photo);
                            }else{
                                $jsst_imageurl = JSST_PLUGIN_URL."includes/images/defaultprofile.png";
                            }
                        ?>
                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" class="js-report-staff-pic" src="<?php echo esc_url($jsst_imageurl); ?>" />
                    </div>
                    <div class="js-report-staff-cnt">
                        <div class="js-report-staff-name">
                            <?php
                                if($jsst_agent->firstname && $jsst_agent->lastname){
                                    $jsst_agentname = $jsst_agent->firstname . ' ' . $jsst_agent->lastname;
                                }else{
                                    $jsst_agentname = $jsst_agent->display_name;
                                }
                                echo esc_html($jsst_agentname);
                            ?>
                        </div>
                        <div class="js-report-staff-username">
                            <?php
                                if($jsst_agent->display_name){
                                    $jsst_username = $jsst_agent->display_name;
                                }else{
                                    $jsst_username = $jsst_agent->user_nicename;
                                }
                                echo esc_html($jsst_username);
                            ?>
                        </div>
                        <div class="js-report-staff-email">
                            <?php
                                if($jsst_agent->email){
                                    $jsst_email = $jsst_agent->email;
                                }else{
                                    $jsst_email = $jsst_agent->user_email;
                                }
                                echo esc_html($jsst_email);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="js-col-md-8 nopadding js-festaffreport-data">
                    <div class="js-col-md-2 js-col-md-offset-1 js-admin-report-box box1">
                        <span class="js-report-box-number"><?php echo esc_html($jsst_agent->openticket); ?></span>
                        <span class="js-report-box-title"><?php echo esc_html(__('New','js-support-ticket')); ?></span>
                        <div class="js-report-box-color"></div>
                    </div>
                    <div class="js-col-md-2 js-admin-report-box box2">
                        <span class="js-report-box-number"><?php echo esc_html($jsst_agent->answeredticket); ?></span>
                        <span class="js-report-box-title"><?php echo esc_html(__('Answered','js-support-ticket')); ?></span>
                        <div class="js-report-box-color"></div>
                    </div>
                    <div class="js-col-md-2 js-admin-report-box box3">
                        <span class="js-report-box-number"><?php echo esc_html($jsst_agent->pendingticket); ?></span>
                        <span class="js-report-box-title"><?php echo esc_html(__('Pending','js-support-ticket')); ?></span>
                        <div class="js-report-box-color"></div>
                    </div>
                    <div class="js-col-md-2 js-admin-report-box box4">
                        <span class="js-report-box-number"><?php echo esc_html($jsst_agent->overdueticket); ?></span>
                        <span class="js-report-box-title"><?php echo esc_html(__('Overdue','js-support-ticket')); ?></span>
                        <div class="js-report-box-color"></div>
                    </div>
                    <div class="js-col-md-2 js-admin-report-box box5">
                        <span class="js-report-box-number"><?php echo esc_html($jsst_agent->closeticket); ?></span>
                        <span class="js-report-box-title"><?php echo esc_html(__('Closed','js-support-ticket')); ?></span>
                        <div class="js-report-box-color"></div>
                    </div>
                </div>
            </div>
        <?php
        } ?>
</div>
<?php
    if(!empty(jssupportticket::$jsst_data['staff_tickets'])){ ?>
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
                            foreach(jssupportticket::$jsst_data['staff_tickets'] AS $jsst_ticket){ ?>
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-table-body-col js-col-md-4 js-col-xs-4">
                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Subject','js-support-ticket')); ?>:</span>
                                    <span class="js-ticket-title"><a class="js-ticket-title-anchor" target="_blank" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=>$jsst_ticket->id))); ?>"><?php echo esc_html($jsst_ticket->subject); ?></a></span>
                                </div>
                                <div class="js-ticket-table-body-col js-col-md-3 js-col-xs-3">
                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Status','js-support-ticket')); ?>:</span>
                                    <?php
                                        // 1 -> New Ticket
                                        // 2 -> Waiting admin/staff reply
                                        // 3 -> in progress
                                        // 4 -> waiting for customer reply
                                        // 5 -> close ticket
                                        /*switch($jsst_ticket->status){
                                            case 0:
                                                $jsst_status = '<font color="#1EADD8">'. esc_html(__('New','js-support-ticket')).'</font>';
                                                if($jsst_ticket->isoverdue == 1)
                                                    $jsst_status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
                                            break;
                                            case 1:
                                                $jsst_status = '<font color="#D98E11">'. esc_html(__('Pending','js-support-ticket')).'</font>';
                                                if($jsst_ticket->isoverdue == 1)
                                                    $jsst_status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
                                            break;
                                            case 2:
                                                $jsst_status = '<font color="#D98E11">'. esc_html(__('In Progress','js-support-ticket')).'</font>';
                                                if($jsst_ticket->isoverdue == 1)
                                                    $jsst_status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
                                            break;
                                            case 3:
                                                $jsst_status = '<font color="#179650">'. esc_html(__('Answered','js-support-ticket')).'</font>';
                                                if($jsst_ticket->isoverdue == 1)
                                                    $jsst_status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
                                            break;
                                            case 4:
                                                $jsst_status = '<font color="#5F3BBB">'. esc_html(__('Closed','js-support-ticket')).'</font>';
                                            break;
                                            case 5:
                                                $jsst_status = '<font color="#5F3BBB">'. esc_html(__('Merged','js-support-ticket')).'</font>';
                                            break;
                                        }*/
                                        if (!in_array($jsst_ticket->status, [5, 6]) && $jsst_ticket->isoverdue == 1) {
                                            $jsst_status = __('Overdue','js-support-ticket');
                                            $jsst_color1 = '#FFFFFF';
                                            $jsst_bgcolor = '#DB624C';
                                        } else {
                                            $jsst_status = $jsst_ticket->statustitle;
                                            $jsst_color1 = $jsst_ticket->statuscolour;
                                            $jsst_bgcolor = $jsst_ticket->statusbgcolour;
                                        }
                                    ?>
                                    <span class="js-ticket-priority" style="background:<?php echo esc_attr($jsst_bgcolor); ?>;color:<?php echo esc_attr($jsst_color1); ?>">
                                        <?php echo esc_html($jsst_status); ?>
                                    </span>
                                </div>
                                <div class="js-ticket-table-body-col js-col-md-3 js-col-xs-3">
                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Priority','js-support-ticket')); ?>:</span>
                                    <span class="js-ticket-priority" style="background-color:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?></span>
                                </div>
                                <div class="js-ticket-table-body-col js-col-md-2 js-col-xs-2">
                                    <span class="js-ticket-display-block"><?php echo esc_html(__('Created','js-support-ticket')); ?>:</span>
                                    <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticket->created))); ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if (jssupportticket::$jsst_data[1]) {
            echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
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
            $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'reports','jstlay'=>'staffreports'));
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
