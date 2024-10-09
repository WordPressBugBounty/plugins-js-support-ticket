<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('ticket-notify-app', JSST_PLUGIN_URL . 'includes/js/firebase-app.js');
wp_enqueue_script('ticket-notify-message', JSST_PLUGIN_URL . 'includes/js/firebase-messaging.js');

wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css');
do_action('ticket-notify-generate-token');
JSSTmessage::getMessage();
wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js');
wp_register_script( 'ticket-google-charts-handle', '' );
wp_enqueue_script( 'ticket-google-charts-handle' );
$jssupportticket_js ='
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawStackChartHorizontal);
    google.setOnLoadCallback(drawTodayTicketsChart);
    function drawStackChartHorizontal() {
      var data = google.visualization.arrayToDataTable([
        '. wp_kses(jssupportticket::$_data["stack_chart_horizontal"]["title"], JSST_ALLOWED_TAGS).",".
            wp_kses(jssupportticket::$_data["stack_chart_horizontal"]["data"], JSST_ALLOWED_TAGS)
        .'
      ]);

      var view = new google.visualization.DataView(data);

      var options = {
        height:571,
        chartArea: { width: "80%"},
        legend: { position: "top",  },
        curveType: "function",
        colors: ["#ff652f","#5ab9ea","#d89922","#14a76c"],
      };
      var chart = new google.visualization.AreaChart(document.getElementById("stack_chart_horizontal"));
      chart.draw(view, options);
    }

    function drawTodayTicketsChart() {
      var data = google.visualization.arrayToDataTable([
        '.
            wp_kses(jssupportticket::$_data["today_ticket_chart"]["title"], JSST_ALLOWED_TAGS).",".
            wp_kses(jssupportticket::$_data['today_ticket_chart']['data'], JSST_ALLOWED_TAGS)
        .'
      ]);

      var view = new google.visualization.DataView(data);

      var options = {
        height:130,
        chartArea: { width: "70%", left: 30 },
        legend: { position: "right" },
        hAxis: { textPosition: "none" },
        colors:'. wp_kses(jssupportticket::$_data["stack_chart_horizontal"]["colors"], JSST_ALLOWED_TAGS).',
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("today_ticket_chart"));
      chart.draw(view, options);
    }';
wp_add_inline_script('ticket-google-charts-handle',$jssupportticket_js);
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="js-main-cp-wrapper">
            <div id="jsstadmin-wrapper-top">
                <div id="jsstadmin-wrapper-top-left">
                    <div id="jsstadmin-breadcrunbs">
                        <ul>
                            <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        </ul>
                    </div>
                </div>
                <div id="jsstadmin-wrapper-top-right">
                    <div id="jsstadmin-config-btn">
                        <a href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>" title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>">
                            <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                        </a>
                    </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_html(__('Help','js-support-ticket')); ?>">
                        <img alt="<?php echo esc_html(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                    <div id="jsstadmin-vers-txt">
                        <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                        <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                    </div>
                </div>
            </div>
            <div id="jsstadmin-head">
                <h1 class="jsstadmin-head-text">
                    <?php echo esc_html(__('Dashboard','js-support-ticket')); ?>
                </h1>
                <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                    <a href="?page=agent" class="jsstadmin-add-link orange-bg button" title="<?php echo esc_html(__('Agents','js-support-ticket')); ?>">
                        <img alt="<?php echo esc_html(__('Staff', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/staff-1.png"/>
                        <?php echo esc_html(__('Agents','js-support-ticket')); ?>
                    </a>
                <?php } ?>
                <a href="?page=ticket" class="jsstadmin-add-link button" title="<?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?>">
                    <img alt="<?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/all-tickets.png"/>
                    <?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?>
                </a>
            </div>

            <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
                <?php if(get_option( 'jsst_hide_jsstadmin_top_banner') != 1){ ?>
                    <div class="js-cp-cnt-sec js-cp-video-baner">
                        <div class="js-cp-video-baner-cnt">
                            <div class="js-cp-video-baner-tit">
                                <?php echo esc_html(__('Quick installation Guide','js-support-ticket')); ?>
                            </div>
                            <div class="js-cp-video-baner-desc">
                                <?php echo esc_html(__('The best support system plugin for WordPress has everything you need.','js-support-ticket')); ?>
                            </div>
                            <div class="js-cp-video-baner-btn-wrp">
                                <a target="blank" href="https://www.youtube.com/watch?v=Honmzw892ZE" class="js-cp-video-baner-btn js-cp-video-baner-1">
                                    <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                    <?php echo esc_html(__('How to setup','js-support-ticket')); ?>
                                </a>
                                <a target="blank" href="https://www.youtube.com/watch?v=dNYnZw8WK0M" class="js-cp-video-baner-btn js-cp-video-baner-2">
                                    <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                    <?php echo esc_html(__('System Emails','js-support-ticket')); ?>
                                </a>
                                <a target="blank" href="https://www.youtube.com/watch?v=zmQ4bpqSYnk" class="js-cp-video-baner-btn js-cp-video-baner-3">
                                    <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                    <?php echo esc_html(__('Ticket Creation','js-support-ticket')); ?>
                                </a>
                                <a target="blank" href="https://www.youtube.com/watch?v=c7whQ6F70yM" class="js-cp-video-baner-btn js-cp-video-baner-4">
                                    <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                    <?php echo esc_html(__('Custom Fields','js-support-ticket')); ?>
                                </a>
                                <a target="blank" href="https://www.youtube.com/watch?v=LvsrMtEqRms" class="js-cp-video-baner-btn js-cp-video-baner-5">
                                    <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                                    <?php echo esc_html(__('Email Notification Problems','js-support-ticket')); ?>
                                </a>
                            </div>

                        </div>
                        <img class="js-cp-video-baner-close-img" alt="<?php echo esc_html(__('close','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-red-bg.png"/>
                    </div>
                <?php } ?>
                <!-- update available alert -->
                <?php if (jssupportticket::$_data['update_avaliable_for_addons'] != 0) {?>
                    <div class="js-update-alert-wrp">
                        <div class="js-update-alert-image">
                            <img alt="<?php echo esc_attr(__('Update','js-support-ticket')); ?>" class="js-update-alert-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/update-icon.png"/>
                        </div>
                        <div class="js-update-alert-cnt">
                                <?php echo esc_html(__("Hey there! We have recently launched a fresh update for the add-ons. Don't forget to update the add-ons to enjoy the greatest features!",'js-support-ticket')); ?>
                        </div>
                        <a href="?page=jssupportticket&jstlay=addonstatus" class="js-update-alert-btn" title="<?php echo esc_attr(__('View','js-support-ticket')); ?>">
                            <?php echo esc_html(__('View Addone Status','js-support-ticket')); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="js-cp-cnt-sec">
                    <div class="js-cp-cnt-left">
                        <?php
                            $open_percentage = 0;
                            $close_percentage = 0;
                            $answered_percentage = 0;
                            $pending_percentage = 0;
                            $overdue_percentage = 0;
                            if(isset(jssupportticket::$_data['ticket_total']) && isset(jssupportticket::$_data['ticket_total']['allticket']) && jssupportticket::$_data['ticket_total']['allticket'] != 0){
                                $open_percentage = round((jssupportticket::$_data['ticket_total']['openticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
                                //$close_percentage = round((jssupportticket::$_data['ticket_total']['closeticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
                                $overdue_percentage = round((jssupportticket::$_data['ticket_total']['overdueticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
                                $answered_percentage = round((jssupportticket::$_data['ticket_total']['answeredticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
                                $pending_percentage = round((jssupportticket::$_data['ticket_total']['pendingticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
                            }
                            if(isset(jssupportticket::$_data['ticket_total']['allticket']) && isset(jssupportticket::$_data['ticket_total']['allticket']) && jssupportticket::$_data['ticket_total']['allticket'] != 0){
                                $allticket_percentage = 100;
                            }
                        ?>
                        <div class="js-ticket-count">
                            <div class="js-ticket-link">
                                <a class="js-ticket-link js-ticket-green" href="?page=ticket" data-tab-number="1">
                                    <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($open_percentage); ?>" data-tab-number="1">
                                        <div class="js-mr-rp" data-progress="<?php echo esc_attr($open_percentage); ?>">
                                            <div class="circle">
                                                <div class="mask full">
                                                     <div class="fill js-ticket-open"></div>
                                                </div>
                                                <div class="mask half">
                                                    <div class="fill js-ticket-open"></div>
                                                    <div class="fill fix"></div>
                                                </div>
                                                <div class="shadow"></div>
                                            </div>
                                            <div class="inset">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="js-ticket-link-text js-ticket-green">
                                        <?php
                                            echo esc_html(__('New', 'js-support-ticket'));
                                            echo ' ( '.esc_html(jssupportticket::$_data['ticket_total']['openticket']).' )';
                                        ?>
                                    </div>
                                </a>
                            </div>
                            <div class="js-ticket-link">
                                <a class="js-ticket-link js-ticket-brown" href="?page=ticket" data-tab-number="2">
                                    <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($answered_percentage); ?>" >
                                        <div class="js-mr-rp" data-progress="<?php echo esc_attr($answered_percentage); ?>">
                                            <div class="circle">
                                                <div class="mask full">
                                                     <div class="fill js-ticket-answer"></div>
                                                </div>
                                                <div class="mask half">
                                                    <div class="fill js-ticket-answer"></div>
                                                    <div class="fill fix"></div>
                                                </div>
                                                <div class="shadow"></div>
                                            </div>
                                            <div class="inset">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="js-ticket-link-text js-ticket-brown">
                                        <?php
                                            echo esc_html(__('Answered', 'js-support-ticket'));
                                            echo ' ( '. esc_html(jssupportticket::$_data['ticket_total']['answeredticket']).' )';
                                        ?>
                                    </div>
                                </a>
                            </div>
                            <div class="js-ticket-link">
                                <a class="js-ticket-link js-ticket-blue" href="?page=ticket" data-tab-number="4">
                                    <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($pending_percentage); ?>">
                                        <div class="js-mr-rp" data-progress="<?php echo esc_attr($pending_percentage); ?>">
                                            <div class="circle">
                                                <div class="mask full">
                                                     <div class="fill js-ticket-allticket"></div>
                                                </div>
                                                <div class="mask half">
                                                    <div class="fill js-ticket-allticket"></div>
                                                    <div class="fill fix"></div>
                                                </div>
                                                <div class="shadow"></div>
                                            </div>
                                            <div class="inset">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="js-ticket-link-text js-ticket-blue">
                                        <?php
                                            echo esc_html(__('Pending', 'js-support-ticket'));
                                            echo ' ( '. esc_html(jssupportticket::$_data['ticket_total']['pendingticket']).' )';
                                        ?>
                                    </div>
                                </a>
                            </div>
                            <?php if(in_array('overdue', jssupportticket::$_active_addons)){ ?>
                                <div class="js-ticket-link">
                                    <a class="js-ticket-link js-ticket-orange" href="?page=ticket" data-tab-number="3">
                                        <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($overdue_percentage); ?>" >
                                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($overdue_percentage); ?>">
                                                <div class="circle">
                                                    <div class="mask full">
                                                         <div class="fill js-ticket-overdue"></div>
                                                    </div>
                                                    <div class="mask half">
                                                        <div class="fill js-ticket-overdue"></div>
                                                        <div class="fill fix"></div>
                                                    </div>
                                                    <div class="shadow"></div>
                                                </div>
                                                <div class="inset">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="js-ticket-link-text js-ticket-orange">
                                            <?php
                                                echo esc_html(__('Overdue', 'js-support-ticket'));
                                                echo ' ( '. esc_html(jssupportticket::$_data['ticket_total']['overdueticket']).' )';
                                            ?>
                                        </div>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="js-cp-cnt">
                            <div class="js-cp-cnt-title">
                                <span class="js-cp-cnt-title-txt">
                                    <?php echo esc_html(__('Statistics', 'js-support-ticket')); ?>
                                    <?php $curdate = date_i18n('Y-m-d'); $fromdate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month")); echo " (".esc_html($fromdate)." - ".esc_html($curdate).")"; ?>
                                </span>
                            </div>
                            <div id="js-pm-grapharea">
                                <div id="stack_chart_horizontal" style="width:100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="js-cp-cnt-right">
                        <div class="js-cp-cnt">
                            <div class="js-cp-cnt-title">
                                <span class="js-cp-cnt-title-txt">
                                    <?php echo esc_html(__('Today Tickets', 'js-support-ticket')); ?>
                                </span>
                            </div>
                            <div id="js-pm-grapharea">
                                <div id="today_ticket_chart" style="width:100%;"></div>
                            </div>
                        </div>
                        <div class="js-cp-cnt">
                            <div class="js-cp-cnt-title">
                                <span class="js-cp-cnt-title-txt">
                                    <?php echo esc_html(__('Short Links', 'js-support-ticket')); ?>
                                </span>
                            </div>
                            <div id="js-wrapper-menus">
                                <a title="<?php echo esc_html(__('Tickets','js-support-ticket')); ?>" class="js-admin-menu-link" href="?page=ticket"> <img alt="<?php echo esc_html(__('Tickets','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/tickets.png"/><div class="jsmenu-text"><?php echo esc_html(__('Tickets','js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('Department','js-support-ticket')); ?>" class="js-admin-menu-link" href="?page=department"><img alt="<?php echo esc_html(__('Department','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/department.png"/><div class="jsmenu-text"><?php echo esc_html(__('Departments', 'js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('Priority','js-support-ticket')); ?>" class="js-admin-menu-link" href="?page=priority"><img alt="<?php echo esc_html(__('Priority','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/priorities.png"/><div class="jsmenu-text"><?php echo esc_html(__('Priorities', 'js-support-ticket')); ?></div></a>
                                <?php
                                if(in_array('multiform', jssupportticket::$_active_addons)){
                                ?>
                                    <a title="<?php echo esc_html(__('Multiform','js-support-ticket')); ?>" class="js-admin-menu-link" href="?page=multiform"><img alt="<?php echo esc_html(__('Multiform','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/fields.png"/><div class="jsmenu-text"><?php echo esc_html(__('Multiform','js-support-ticket')); ?></div></a>
                                    <?php
                                } else { ?>
                                    <a title="<?php echo esc_html(__('Field Ordering','js-support-ticket')); ?>" class="js-admin-menu-link" href="?page=fieldordering&fieldfor=1"><img alt="<?php echo esc_html(__('Field Ordering','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/fields.png"/><div class="jsmenu-text"><?php echo esc_html(__('Fields','js-support-ticket')); ?></div></a>
                                    <?php
                                } ?>
                                <a title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" class="js-admin-menu-link" href="?page=configuration"><img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/config.png"/><div class="jsmenu-text"><?php echo esc_html(__('Configurations','js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('Overall Report','js-support-ticket')); ?>" class="js-admin-menu-link" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=overallreport')); ?>"><img alt="<?php echo esc_html(__('Overall Report','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/report.png"/><div class="jsmenu-text"><?php echo esc_html(__('Overall Statistics','js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('Department Reports','js-support-ticket')); ?>" class="js-admin-menu-link" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=departmentreport')); ?>"><img alt="<?php echo esc_html(__('Department Reports','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/department-report.png"/><div class="jsmenu-text"><?php echo esc_html(__('Department Reports','js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('User report','js-support-ticket')); ?>" class="js-admin-menu-link" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=userreport')); ?>"><img alt="<?php echo esc_html(__('User report','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/user-reports.png"/><div class="jsmenu-text"><?php echo esc_html(__('User Reports', 'js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('Translations','js-support-ticket')); ?>" class="js-admin-menu-link" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=translations')); ?>"><img alt="<?php echo esc_html(__('Translations','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/translations.png"/><div class="jsmenu-text"><?php echo esc_html(__('Translations','js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('Email','js-support-ticket')); ?>" class="js-admin-menu-link" href="?page=email"><img alt="<?php echo esc_html(__('Email','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/system-email.png"/><div class="jsmenu-text"><?php echo esc_html(__('System Emails', 'js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('email template','js-support-ticket')); ?>" class="js-admin-menu-link" href="?page=emailtemplate"><img alt="<?php echo esc_html(__('email template','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/email-template.png"/><div class="jsmenu-text"><?php echo esc_html(__('Email Templates', 'js-support-ticket')); ?></div></a>
                                <a title="<?php echo esc_html(__('add missing users','js-support-ticket')); ?>" class="js-admin-menu-link" href="<?php echo esc_url(wp_nonce_url('?page=jssupportticket&task=addmissingusers&action=jstask','add-missing-users'));?>"><img alt="<?php echo esc_html(__('user','js-support-ticket')); ?>" class="jsmenu-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/wp-user.png"/><div class="jsmenu-text"><?php echo esc_html(__('Add WP Users', 'js-support-ticket')); ?></div></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="js-cp-cnt-sec js-cp-baner">
                    <div class="js-cp-baner-cnt">
                        <div class="js-cp-banner-tit-bold">
                            <?php echo esc_html(__('Install Now','js-support-ticket')); ?>
                        </div>
                        <div class="js-cp-banner-tit">
                            <?php $data = esc_html(__('Premium Addons List','js-support-ticket')).' & '. esc_html(__('Features','js-support-ticket'));
                            echo esc_html($data); ?>
                        </div>
                        <div class="js-cp-banner-desc">
                            <?php echo esc_html(__('The best support system plugin for WordPress has everything you need.','js-support-ticket')); ?>
                        </div>
                        <div class="js-cp-banner-btn-wrp">
                            <a href="?page=premiumplugin&jstlay=addonfeatures" class="js-cp-banner-btn orange-bg">
                                <?php echo esc_html(__('Add-Ons List','js-support-ticket')); ?>
                            </a>
                            <a href="?page=premiumplugin&jstlay=step1" class="js-cp-banner-btn">
                                <?php echo esc_html(__('Add New Addons','js-support-ticket')); ?>
                            </a>
                        </div>
                    </div>
                    <img class="js-cp-baner-img" alt="<?php echo esc_html(__('addon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/banner/addon-image.png"/>
                </div>

                <?php
                $field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1);
                ?>
                <div class="js-cp-cnt-sec js-cp-tkt">
                    <div class="js-cp-cnt-title">
                        <span class="js-cp-cnt-title-txt">
                            <?php echo esc_html(__('Latest Tickets', 'js-support-ticket')); ?>
                        </span>
                        <?php if(count(jssupportticket::$_data['tickets']) > 0){ ?>
                            <a href="?page=ticket" class="js-cp-cnt-title-btn" title="<?php echo esc_html(__('View All Tickets', 'js-support-ticket')); ?>">
                                <?php echo esc_html(__('View All Tickets', 'js-support-ticket')); ?>
                            </a>
                        <?php } ?>
                    </div>
                    <div class="js-ticket-admin-cp-tickets">
                        <?php if(count(jssupportticket::$_data['tickets']) > 0){
                            foreach (jssupportticket::$_data['tickets'] AS $ticket): ?>
                                <div class="js-cp-tkt-list">
                                    <div class="js-cp-tkt-list-left">
                                        <div class="js-cp-tkt-image">
                                            <?php echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById($ticket->uid)), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="js-cp-tkt-cnt">
                                            <div class="js-cp-tkt-info name"><?php echo esc_html($ticket->name); ?></div>
                                            <div class="js-cp-tkt-info subject" >
                                                <a title="<?php echo esc_html(__('Subject','js-support-ticket')); ?>" href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($ticket->id); ?>"><?php echo esc_html($ticket->subject); ?></a>
                                            </div>
                                            <div class="js-cp-tkt-info dept">
                                                <span class="js-cp-tkt-info-label" >
                                                    <?php echo esc_html(__('Department', 'js-support-ticket')). " : "; ?>
                                                </span>
                                                <?php echo esc_html($ticket->departmentname); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="js-cp-tkt-status">
                                        <?php
                                        if ($ticket->status == 0) {
                                            $style = "#1572e8;";
                                            $status = esc_html(__('New', 'js-support-ticket'));
                                        } elseif ($ticket->status == 1) {
                                            $style = "#ad6002;";
                                            $status = esc_html(__('Waiting Agent Reply', 'js-support-ticket'));
                                        } elseif ($ticket->status == 2) {
                                            $style = "#FF7F50;";
                                            $status = esc_html(__('In Progress', 'js-support-ticket'));
                                        } elseif ($ticket->status == 3) {
                                            $style = "green;";
                                            $status = esc_html(__('Replied', 'js-support-ticket'));
                                        } elseif ($ticket->status == 4) {
                                            $style = "blue;";
                                            $status = esc_html(__('Closed', 'js-support-ticket'));
                                        }
                                        echo wp_kses('<span style="color:' . esc_attr($style) . '">' . esc_html($status) . '</span>', JSST_ALLOWED_TAGS);
                                        ?>
                                    </div>
                                    <div class="js-cp-tkt-crted"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->created))); ?></div>
                                    <div class="js-cp-tkt-prorty">
                                        <span style="background-color:<?php echo esc_attr($ticket->prioritycolour); ?>;">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?>
                                        </span>
                                    </div>
                                </div>
                        <?php
                            endforeach;
                        }else{ ?>
                            <div class="jsst_no_record">
                                <?php echo esc_html(__("No Record Found","js-support-ticket")); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="js-cp-fed-ad-wrp">
                    <?php $fullwidthclass = "";
                    if(count(jssupportticket::$_active_addons) >= 36 ){
                        $fullwidthclass = "style=width:100% !important";
                    }?>
                    <?php if(in_array('tickethistory', jssupportticket::$_active_addons)){ ?>
                    <div class="js-cp-feedback-wrp" <?php echo esc_attr($fullwidthclass); ?>>
                        <div class="js-cp-cnt-title">
                            <span class="js-cp-cnt-title-txt"><?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="js-cp-feedback-list">
                            <?php
                            if(count(jssupportticket::$_data['tickethistory']) > 0){
                                foreach(jssupportticket::$_data['tickethistory'] as $history){
                                    ?>
                                    <div class="js-cp-feedback">
                                        <div class="js-cp-feedback-image">
                                            <?php echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById($history->uid), 'js-cp-feedback-img'), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="js-cp-feedback-cnt">
                                            <div class="js-cp-feedback-row">
                                                <span class="js-cp-feedback-type">
                                                    <?php echo esc_html($history->eventtype); ?>
                                                </span>
                                                <span class="js-cp-feedback-crt-date"><?php echo ' - ' .esc_html($history->datetime); ?></span>
                                            </div>
                                            <div class="js-cp-feedback-row">
                                                <?php echo wp_kses_post($history->message); ?>
                                            </div>
                                            <div class="js-cp-feedback-row">
                                                <span class="js-cp-feedback-tit">
                                                    <?php echo esc_html(__('Department','js-support-ticket')). ' : ' ; ?>
                                                </span>
                                                <span class="js-cp-feedback-val">
                                                    <?php echo esc_html($history->departmentname); ?>
                                                </span>
                                            </div>
                                            <div class="js-cp-feedback-row">
                                                <span class="js-cp-feedback-prty" style="background:<?php echo esc_attr($history->prioritycolour); ?>;">
                                                    <?php echo esc_html(jssupportticket::JSST_getVarValue($history->priority)); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }else{ ?>
                                <div class="jsst_no_record">
                                    <?php echo esc_html(__("No Record Found","js-support-ticket")); ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php /*
                        <div class="js-cp-feedback-btn-wrp">
                            <a href="#" class="js-cp-feedback-btn" title="<?php echo esc_html(__('view all tickets history', 'js-support-ticket')); ?>">
                                <?php echo esc_html(__('View All Tickets History','js-support-ticket')); ?>
                            </a>
                        </div> */ ?>
                    </div>
                    <?php } ?>
                    <?php if(count(jssupportticket::$_active_addons) < 36 ){ ?>
                    <div class="js-cp-addon-wrp">
                        <div class="js-cp-cnt-title">
                            <span class="js-cp-cnt-title-txt"><?php echo esc_html(__('Addons', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="js-cp-addon-list">
                            <?php if ( !in_array('agent',jssupportticket::$_active_addons)) { ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Agent','js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/agent.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Agents','js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Add agents and assign roles and permissions to provide assistance.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-agent/js-support-ticket-agent.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-agent&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/agents/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if ( !in_array('autoclose',jssupportticket::$_active_addons)) { ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Ticket Auto Close','js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/autoclose.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Ticket Auto Close', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Define rules for the ticket to auto-close after a specific interval of time.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-autoclose/js-support-ticket-autoclose.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-autoclose&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/close-ticket/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('feedback', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/feedback.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Feedbacks','js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Get a survey from customers on ticket closing to improve quality.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-feedback/js-support-ticket-feedback.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-feedback&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/feedback/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('helptopic', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Help Topics', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/helptopic.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Help Topics', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Help users to find and select the area with which they need assistance.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-helptopic/js-support-ticket-helptopic.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-helptopic&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/helptopic/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('note', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Private Note', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/note.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Private Note', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('The private note is used as reminders or to give other agents insights into the ticket issue.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-note/js-support-ticket-note.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-note&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/internal-note/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('knowledgebase', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Knowledge Base', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/knowledgebase.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Knowledge Base', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Stop losing productivity on repetitive queries, build your knowledge base.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-knowledgebase/js-support-ticket-knowledgebase.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-knowledgebase&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/knowledge-base/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('maxticket', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Max Ticket', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/maxticket.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Max Tickets', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Enables admin to set N numbers of tickets for users and agents separately.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-maxticket/js-support-ticket-maxticket.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-maxticket&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/max-ticket/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('mergeticket', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Merge Ticket', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/mergeticket.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Merge Tickets', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Enables agents to merge two tickets of the same user into one instead of dealing with the same issue on many tickets.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-mergeticket/js-support-ticket-mergeticket.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-mergeticket&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/merge-ticket/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('overdue', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Overdue', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/overdue.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Overdue', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Defines rules or set specific intervals of time to make ticket auto overdue.The ticket can overdue by type or overdue by Cronjob.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-overdue/js-support-ticket-overdue.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-overdue&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/overdue/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('smtp', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('SMTP', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/smtp.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('SMTP', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('SMTP enables you to add custom mail protocol to send and receive emails within the js help desk.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-smtp/js-support-ticket-smtp.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-smtp&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/smtp/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('tickethistory', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/tickethistory.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Displays complete ticket history along with the ticket status, currently assigned user and other actions performed on each ticket.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-tickethistory/js-support-ticket-tickethistory.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-tickethistory&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/ticket-history/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('cannedresponses', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Canned Responses', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/cannedresponses.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Canned Responses', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Pre-populated messages allow support agents to respond quickly.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-cannedresponses/js-support-ticket-cannedresponses.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-cannedresponses&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/canned-responses/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('emailpiping', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Email Piping','js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/emailpiping.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Email Piping','js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Enables users to reply to the tickets via email without login.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-emailpiping/js-support-ticket-emailpiping.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-emailpiping&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/email-piping/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Time Tracking', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/timetracking.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Time Tracking', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Track the time spent on each ticket by each agent and each reply. Report the admin on how much time is spent on each ticket.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-timetracking/js-support-ticket-timetracking.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-timetracking&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/time-tracking/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('useroptions', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('User Options', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/useroptions.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('User Options', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('User options enable you to add Google Re-captcha or JS Help Desk Re-captcha for a registration form.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-useroptions/js-support-ticket-useroptions.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-useroptions&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/user-options/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('actions', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Actions', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/actions.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Ticket Actions', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Get multiple action options on each ticket like Print Ticket, Lock Ticket, Transfer ticket, etc.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-actions/js-support-ticket-actions.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-actions&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/actions/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('announcement', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Announcements', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/announcement.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Announcements', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Make unlimited announcements associated with the support system.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-announcement/js-support-ticket-announcement.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-announcement&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/announcements/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('banemail', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Ban Emails', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/banemail.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Ban Emails', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('It allows you to block the email of any user to restrict him to create new tickets.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-banemail/js-support-ticket-banemail.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-banemail&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/ban-email/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('notification', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Desktop Notification', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/notification.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Desktop Notification', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('The Desktop notifications will keep you up to date about anything happens on your support system.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-notification/js-support-ticket-notification.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-notification&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/desktop-notification/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('export', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Export','js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/export.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Export','js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Save the ticket as a PDF in your system and able to export all data.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-export/js-support-ticket-export.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-export&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/export/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('download', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Downloads', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/download.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Downloads', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Create downloads to ensure the user to get downloads from downloads.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-download/js-support-ticket-download.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-download&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/downloads/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('faq', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__("FAQ's", 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/faq.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__("FAQ's", 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Add FAQs to drastically reduce the number of common questions.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-faq/js-support-ticket-faq.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-faq&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/faq/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('dashboardwidgets', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Dashboard Widgets', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/dashboardwidgets.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Dashboard Widgets', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Get immediate data of your support operations as soon as you log into your WordPress administration area.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-dashboardwidgets/js-support-ticket-dashboardwidgets.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-dashboardwidgets&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/admin-widget/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('mail', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Internal Mail', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/mail.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Internal Mail', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Use an internal email to send emails to one agent to another agent.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-mail/js-support-ticket-mail.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-mail&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/internal-mail/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('widgets', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Front-End Widgets', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/widgets.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Front-End Widgets', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Widgets in WordPress allow you to add content and features in the widgetized areas of your theme.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-widgets/js-support-ticket-widgets.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-widgets&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/widget/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('woocommerce', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('WooCommerce', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/woocommerce.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('WooCommerce', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('JS Help Desk WooCommerce provides the much-needed bridge between your WooCommerce store and the JS Help Desk.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-woocommerce/js-support-ticket-woocommerce.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-woocommerce&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/woocommerce/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('privatecredentials', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/privatecredentials.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Collect your customer\'s private data, sensitive information from credit card to health information and store them encrypted.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-privatecredentials/js-support-ticket-privatecredentials.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-privatecredentials&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/private-credentials/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('envatovalidation', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('envato', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/envatovalidation.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Envato Validation', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Without valid Envato, license clients won\'t be able to open a new ticket.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-envatovalidation/js-support-ticket-envatovalidation.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-envatovalidation&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/envato/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('mailchimp', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('mailchimp', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/mailchimp.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Mailchimp', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Adds the option to the registration form for prompting new users to subscribe to your email list.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-mailchimp/js-support-ticket-mailchimp.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-mailchimp&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/mail-chimp/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('paidsupport', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('paidsupport', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/paidsupport.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Paid Support', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('Paid Support is the easiest way to integrate and manage payments for your tickets.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-paidsupport/js-support-ticket-paidsupport.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-paidsupport&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/paid-support/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('easydigitaldownloads', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('easy digital downloads', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/easydigitaldownloads.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Easy Digital Downloads', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('EDD offers customers to open new tickets just one click from their EDD account.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-easydigitaldownloads/js-support-ticket-easydigitaldownloads.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-easydigitaldownloads&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/easy-digital-download/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if(!in_array('multilanguageemailtemplates', jssupportticket::$_active_addons)){ ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Multi Language Email Templates', 'js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/multilanguageemailtemplates.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Multi Language Email Templates', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('It allows to create language-based email templates for all JS Help Desk email templates.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-multilanguageemailtemplates/js-support-ticket-multilanguageemailtemplates.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-multilanguageemailtemplates&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/multi-language-email-templates";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if ( !in_array('emailcc',jssupportticket::$_active_addons)) { ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Email Cc','js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/emailcc.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Email Cc', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('CC(Carbon Copy) - the people who should know about the information which is being shared and the people included are able to see who is there in the list.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-emailcc/js-support-ticket-emailcc.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-emailcc&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/emailcc/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if ( !in_array('multiform',jssupportticket::$_active_addons)) { ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Multiform','js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/multiform.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Multiform','js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('It allows user to add more than one form based on requirements.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-multiform/js-support-ticket-multiform.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-multiform&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/multi-forms/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                            <?php if ( !in_array('agentautoassign',jssupportticket::$_active_addons)) { ?>
                                <div class="js-cp-addon">
                                    <div class="js-cp-addon-image">
                                        <img alt="<?php echo esc_html(__('Agent Auto Assign','js-support-ticket')); ?>" class="js-cp-addon-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/addon/agentautoassign.png"/>
                                    </div>
                                    <div class="js-cp-addon-cnt">
                                        <div class="js-cp-addon-tit">
                                            <?php echo esc_html(__('Agent Auto Assign','js-support-ticket')); ?>
                                        </div>
                                        <div class="js-cp-addon-desc">
                                            <?php echo esc_html(__('When a ticket is created, an appropriate agent is automatically assigned to the ticket and it is moved to the Assigned state.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <?php $plugininfo = JSSTCheckPluginInfo('js-support-ticket-agentautoassign/js-support-ticket-agentautoassign.php');
                                    if($plugininfo['availability'] == "1"){
                                        $text = $plugininfo['text'];
                                        $url = "plugins.php?s=js-support-ticket-agentautoassign&plugin_status=inactive";
                                    }elseif($plugininfo['availability'] == "0"){
                                        $text = $plugininfo['text'];
                                        $url = "https://jshelpdesk.com/product/agentautoassign/";
                                    } ?>
                                    <a href="<?php echo esc_url($url); ?>" class="js-cp-addon-btn" title="<?php echo esc_attr($text); ?>">
                                        <?php echo esc_html($text); ?>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div id="jsreview-banner">
                    <div class="review">
                        <div class="upper">
                            <span class="simple-text">
                                <?php echo esc_html(__('We\'d love to hear from You.', 'js-support-ticket')); ?>
                                <br>
                                <?php echo esc_html(__('Please write appreciated review at', 'js-support-ticket')); ?>
                            </span>
                            <a class="review-link" href="https://wordpress.org/support/plugin/js-support-ticket/reviews" target="_blank" title="<?php echo esc_html(__('WP Extension Directory', 'js-support-ticket')); ?>">
                                <img alt="<?php echo esc_html(__('star','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/review/star.png">
                                <?php echo esc_html(__('WP Extension Directory', 'js-support-ticket')); ?>
                            </a>
                        </div>
                        <div class="lower">
                            <span class="simple-text"><?php echo esc_html(__('Spread the word', 'js-support-ticket')). ' : ' ; ?></span>
                            <a class="rev-soc-link" href="https://www.facebook.com/joomsky">
                                <img alt="<?php echo esc_html(__('fb','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/review/fb.png">
                            </a>
                            <a class="rev-soc-link" href="https://twitter.com/joomsky">
                                <img alt="<?php echo esc_html(__('twitter','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/review/twitter.png">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="js-other-products-wrp">
                    <div class="js-other-product-title">
                        <?php echo esc_html(__("Other Products","js-support-ticket")); ?>
                    </div>
                    <div class="js-other-products-detail">
                        <div class="js-other-products-image">
                            <img title="<?php echo esc_html(__("WP Vehicle Manager","js-support-ticket")); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/otherproducts/vehicle-manager.png">
                            <div class="js-other-products-bottom">
                                <div class="js-product-title"><?php echo esc_html(__("WP Vehicle Manager","js-support-ticket")); ?></div>
                                <div class="js-product-bottom-btn">
                                    <span class="js-product-view-btn">
                                        <a href="https://wpvehiclemanager.com"  target="_blank" title="<?php echo esc_html(__("Visit site","js-support-ticket")); ?>"><img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/otherproducts/new-tab.png"></a>
                                    </span>
                                    <span class="js-product-install-btn">
                                        <?php $plugininfo = JSSTCheckPluginInfo('js-vehicle-manager/js-vehicle-manager.php'); ?>
                                        <a title="<?php echo esc_html(__("Install WP Vehicle Manager Plugin","js-support-ticket")); ?>" class="wp-vehicle-manager-btn-color <?php echo esc_attr($plugininfo['class']); ?>" data-slug="js-vehicle-manager" <?php echo esc_attr($plugininfo['disabled']); ?>>
                                            <?php echo esc_html($plugininfo['text']) ?>
                                            <?php ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="js-other-products-image">
                            <img title="<?php echo esc_html(__("JS Job Manager","js-support-ticket")); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/otherproducts/job.png">
                            <div class="js-other-products-bottom">
                                <div class="js-product-title"><?php echo esc_html(__("JS Job Manager","js-support-ticket")); ?></div>
                                <div class="js-product-bottom-btn">
                                    <span class="js-product-view-btn">
                                        <a href="https://joomsky.com/products/js-jobs-pro-wp.html"  target="_blank" title="<?php echo esc_html(__("Visit site","js-support-ticket")); ?>"><img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/otherproducts/new-tab.png"></a>
                                    </span>
                                    <span class="js-product-install-btn">
                                        <?php $plugininfo = JSSTCheckPluginInfo('js-jobs/js-jobs.php'); ?>
                                        <a title="<?php echo esc_html(__("Install JS Job Manager Plugin","js-support-ticket")); ?>" class="js-jobs-manager-btn-color <?php echo esc_attr($plugininfo['class']); ?>" data-slug="js-jobs" <?php echo esc_attr($plugininfo['disabled']); ?>>
                                            <?php echo esc_html($plugininfo['text']) ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="js-other-products-image">
                            <img title="<?php echo esc_html(__("WP Learn Manager","js-support-ticket")); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/otherproducts/lms.png">
                            <div class="js-other-products-bottom">
                                <div class="js-product-title"><?php echo esc_html(__("WP Learn Manager","js-support-ticket")); ?></div>
                                <div class="js-product-bottom-btn">
                                    <span class="js-product-view-btn">
                                        <a title="<?php echo esc_html(__("Visit site","js-support-ticket")); ?>" href="https://wplearnmanager.com" target="_blank"><img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/admincp/otherproducts/new-tab.png"></a>
                                    </span>
                                    <span class="js-product-install-btn">
                                        <?php $plugininfo = JSSTCheckPluginInfo('learn-manager/learn-manager.php'); ?>
                                        <a title="<?php echo esc_html(__("Install WP Learn Manager Plugin","js-support-ticket")); ?>" class="wp-learn-manager-btn-color <?php echo esc_attr($plugininfo['class']); ?>" data-slug="learn-manager" <?php echo esc_attr($plugininfo['disabled']); ?>><?php echo esc_html($plugininfo['text']) ?></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php
        $jssupportticket_js ='
            jQuery(document).ready(function () {
                jQuery("span.dashboard-icon").find("span.download").hover(function(){
                    jQuery(this).find("span").toggle("slide");
                    }, function(){
                    jQuery(this).find("span").toggle("slide");
                });

                jQuery("a.js-ticket-link").click(function(e){
                    e.preventDefault();
                    var list = jQuery(this).attr("data-tab-number");
                    var oldUrl = jQuery(this).attr("href");
                    var newUrl = oldUrl+"&list="+list;
                    window.location.href = newUrl;
                });
            });
        ';
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        ?>
    </div>
</div>
<?php
$jssupportticket_js ="
    jQuery(document).ready(function(){
        jQuery(document).on('click','a.js-btn-install-now',function(){
            jQuery(this).attr('disabled',true);
            jQuery(this).html('Installing.....!');
            jQuery(this).removeClass('js-btn-install-now');
            var pluginslug = jQuery(this).attr('data-slug');
            var buttonclass = jQuery(this).attr('class');
            jQuery(this).addClass('js-installing-effect');
            if(pluginslug != ''){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'installPluginFromAjax', pluginslug:pluginslug,'_wpnonce':'".esc_attr(wp_create_nonce("install-plugin-ajax"))."'}, function (data) {
                    if(data == 1){
                        jQuery('span.js-product-install-btn a.'+buttonclass).attr('disabled',false);
                        jQuery('span.js-product-install-btn a.'+buttonclass).html('Active Now');
                        jQuery('span.js-product-install-btn a.'+buttonclass).addClass('js-btn-active-now js-btn-green');
                        jQuery('span.js-product-install-btn a.'+buttonclass).removeClass('js-installing-effect');
                    }else{
                        jQuery('span.js-product-install-btn a.'+buttonclass).attr('disabled',false);
                        jQuery('span.js-product-install-btn a.'+buttonclass).html('Please try again');
                        jQuery('span.js-product-install-btn a.'+buttonclass).addClass('js-btn-install-now');
                        jQuery('span.js-product-install-btn a.'+buttonclass).removeClass('js-installing-effect');
                    }
                });
            }
        });

        jQuery(document).on('click','a.js-btn-active-now',function(){
            jQuery(this).attr('disabled',true);
            jQuery(this).html('Activating.....!');
            jQuery(this).removeClass('js-btn-active-now');
            var pluginslug = jQuery(this).attr('data-slug');
            var buttonclass = jQuery(this).attr('class');
            if(pluginslug != ''){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'activatePluginFromAjax', pluginslug:pluginslug,'_wpnonce':'".esc_attr(wp_create_nonce('activate-plugin-ajax'))."'}, function (data) {
                    if(data == 1){
                        jQuery('a[data-slug='+pluginslug+']').html('Activated');
                        jQuery('a[data-slug='+pluginslug+']').addClass('js-btn-activated');
                        window.location.reload();
                    }
                });
            }
        });
        
        // video banner
        jQuery('img.js-cp-video-baner-close-img').click(function(){
            jQuery('.js-cp-video-baner').fadeOut('slow');
            jQuery.post(ajaxurl ,{action: 'jsticket_ajax',jstmod: 'jssupportticket',task: 'hidePopupFromAdmin', '_wpnonce':'".esc_attr(wp_create_nonce("hide-popup-from-admin"))."'});
        });
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
