<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('ticket-notify-app', JSST_PLUGIN_URL . 'includes/js/firebase-app.js');
wp_enqueue_script('ticket-notify-message', JSST_PLUGIN_URL . 'includes/js/firebase-messaging.js');
do_action('ticket-notify-generate-token');
wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css');
if(isset(jssupportticket::$_data['stack_chart_horizontal'])) {
    wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js');
    wp_register_script( 'ticket-google-charts-handle', '' );
    wp_enqueue_script( 'ticket-google-charts-handle' );
    $jssupportticket_js ="
    google.load('visualization', '1', {packages:['corechart']});
    google.setOnLoadCallback(drawStackChartHorizontal);
    function drawStackChartHorizontal() {
      var data = google.visualization.arrayToDataTable([
        ".
            wp_kses(jssupportticket::$_data['stack_chart_horizontal']['title'], JSST_ALLOWED_TAGS).",".
            wp_kses(jssupportticket::$_data['stack_chart_horizontal']['data'], JSST_ALLOWED_TAGS)
        ."
      ]);

      var view = new google.visualization.DataView(data);

      var options = {
        height:571,
        chartArea: { width: '80%'},
        legend: { position: 'top',  },
        curveType: 'function',
        colors: ['#ff652f','#5ab9ea','#d89922','#14a76c'],
      };
      var chart = new google.visualization.AreaChart(document.getElementById('stack_chart_horizontal'));
      chart.draw(view, options);
    }
    ";
    wp_add_inline_script('ticket-google-charts-handle',$jssupportticket_js);
}
$jssupportticket_js ='
    jQuery(document).ready(function ($) {
        jQuery("div#js-ticket-main-black-background,span#js-ticket-popup-close-button").click(function () {
            jQuery("div#js-ticket-main-popup").slideUp();
            setTimeout(function () {
                jQuery("div#js-ticket-main-black-background").hide();
            }, 600);

        });

        jQuery("a.js-ticket-link").click(function(e){
            e.preventDefault();
            var list = jQuery(this).attr("data-tab-number");
            var oldUrl = jQuery(this).attr("href"); // Get current url
            var opt = "?";
            var found = oldUrl.search("&");
            if (found > 0) {
                opt = "&";
            }
            var found = oldUrl.search("[\?\]");
            if (found > 0) {
                opt = "&";
            }
            var newUrl = oldUrl + opt + "list=" + list; // Create new url
            window.location.href = newUrl;
        });
    });
    function getDownloadById(value) {
        ajaxurl = "'.esc_url(admin_url('admin-ajax.php')).'";
        jQuery.post(ajaxurl, {action: "jsticket_ajax", downloadid: value, jstmod: "download", task: "getDownloadById",jsstpageid:'.get_the_ID().', "_wpnonce":"'.esc_attr(wp_create_nonce("get-download-by-id")).'"}, function (data) {
            if (data) {
                var obj = jQuery.parseJSON(data);
                jQuery("div#js-ticket-main-content").html(jsstDecodeHTML(obj.data));
                jQuery("span#js-ticket-popup-title").html(obj.title);
                jQuery("div#js-ticket-main-downloadallbtn").html(jsstDecodeHTML(obj.downloadallbtn));
                jQuery("div#js-ticket-main-black-background").show();
                jQuery("div#js-ticket-main-popup").slideDown("slow");
            }
        });
    }
';
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
<div class="jsst-main-up-wrapper">
<?php

if (jssupportticket::$_config['offline'] == 2) {
    JSSTmessage::getMessage();
    include_once(JSST_PLUGIN_PATH . 'includes/header.php');
    $agent_flag = 0;
    if(in_array('agent',jssupportticket::$_active_addons)){
        if (JSSTincluder::getJSModel('agent')->isUserStaff()) {
            $agent_flag = 1;
        }
    }

    $data = isset(jssupportticket::$_data[0]) ? jssupportticket::$_data[0] : array();
    ?>


    <div class="js-cp-main-wrp">
        <div class="js-cp-left">
            <!-- cp links for user -->
            <?php
                if ($agent_flag == 0) { ?>
                    <div id="js-dash-menu-link-wrp"><!-- Dashboard Links -->
                        <div class="js-section-heading"><?php echo esc_html(__('Dashboard Links','js-support-ticket')); ?></div>
                        <div class="js-menu-links-wrp">
                            <?php
                            $count = 0;
                            /*<div class="js-ticket-menu-links-row">*/
                            if (jssupportticket::$_config['cplink_openticket_user'] == 1):
                                $ajaxid = "";
                                $count ++;
						        if(in_array('multiform',jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1){
									//show popup in case of multiform
									$ajaxid = "id=multiformpopup";
								}
								// controller add default form id, if single form
								$menu_url = esc_url(jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'addticket')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/add-ticket.png';
                                $menu_title =  esc_html(__('Submit Ticket', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path,$ajaxid);
                            endif;
                            if (jssupportticket::$_config['cplink_myticket_user'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/tickets.png';
                                $menu_title =  esc_html(__('My Tickets', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_checkticketstatus_user'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/ticket-status.png';
                                $menu_title =  esc_html(__('Ticket Status', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('announcement', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_announcements_user'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'announcements')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/announcements.png';
                                $menu_title =  esc_html(__('Announcements', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('download', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_downloads_user'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'downloads')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/download.png';
                                $menu_title =  esc_html(__('Downloads', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('faq', jssupportticket::$_active_addons) &&  jssupportticket::$_config['cplink_faqs_user'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'faqs')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/faq.png';
                                $menu_title =  esc_html(__("FAQ's", 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('knowledgebase', jssupportticket::$_active_addons) &&  jssupportticket::$_config['cplink_knowledgebase_user'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'userknowledgebase')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/kb.png';
                                $menu_title =  esc_html(__('Knowledge Base', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_erasedata_user'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/user-data.png';
                                $menu_title =  esc_html(__('User Data', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            apply_filters( 'js_support_ticket_frontend_controlpanel_left_menu_custom_links_middle',$count);
                            if (jssupportticket::$_config['cplink_login_logout_user'] == 1){
                                $count ++;
                                $loginval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_login_link');
                                $loginlink = JSSTincluder::getJSModel('configuration')->getConfigValue('login_link');
                                    if ($loginval == 3){
                                        $hreflink = wp_login_url();
                                    }
                                    else if ($loginval == 2 && $loginlink != ""){
                                        $hreflink = $loginlink;
                                    }else{
                                        $hreflink= jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'login'));
                                    }
                                    if (!is_user_logged_in()):
                                        $menu_url = $hreflink;
                                        $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/login.png';
                                        $menu_title =  esc_html(__('Log In', 'js-support-ticket'));
                                        JSST_printMenuLink($menu_title, $menu_url, $image_path);
                                    endif;
                                if (is_user_logged_in()):
                                    $menu_url = wp_logout_url( home_url() );
                                    $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/logout.png';
                                    $menu_title =  esc_html(__('Log Out', 'js-support-ticket'));
                                    JSST_printMenuLink($menu_title, $menu_url, $image_path);
                                endif;
                            }
                            if (jssupportticket::$_config['cplink_register_user'] == 1){
                                $registerval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_register_link');
                                $registerlink = JSSTincluder::getJSModel('configuration')->getConfigValue('register_link');
                                if ($registerval == 3){
                                    $hreflink = wp_registration_url();
                                }else if ($registerval == 2 && $registerlink != ""){
                                    $hreflink = $registerlink;
                                }else{
                                    $hreflink= jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'userregister'));
                                }
                                if (!is_user_logged_in()):
                                    $count ++;
                                    $is_enable = get_option('users_can_register'); /*check to make sure user registration is enabled*/
                                    if ($is_enable) {// only show the registration form if allowed
                                        $menu_url = esc_url($hreflink);
                                        $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/register.png';
                                        $menu_title =  esc_html(__('Register', 'js-support-ticket'));
                                        JSST_printMenuLink($menu_title, $menu_url, $image_path);
                                    }
                                endif;
                            } ?>
                        </div>
                    </div>
                    <?php
                }
            ?>

            <!-- cp links for agent -->
            <?php
                if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) { ?>
                    <div id="js-dash-menu-link-wrp">
                        <div class="js-section-heading"><?php echo esc_html(__('Dashboard Links','js-support-ticket')); ?></div>
                        <div class="js-menu-links-wrp">  <!-- Dashboard Links -->
                            <?php
                            $count = 0;
                            if (jssupportticket::$_config['cplink_openticket_staff'] == 1):
                                $ajaxid = "";
                                $count ++;
						        if(in_array('multiform',jssupportticket::$_active_addons)&& jssupportticket::$_config['show_multiform_popup'] == 1){
									//show popup in case of multiform
									$ajaxid = "id=multiformpopup";
								}
								// controller add default form id, if single form
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffaddticket')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/add-ticket.png';
                                $menu_title =  esc_html(__('Submit Ticket', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path,$ajaxid);
                            endif;
                            if (jssupportticket::$_config['cplink_myticket_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffmyticket')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/tickets.png';
                                $menu_title =  esc_html(__('My Tickets', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_roles_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'role', 'jstlay'=>'roles')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/role.png';
                                $menu_title =  esc_html(__('Roles', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_staff_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffs')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/staff.png';
                                $menu_title =  esc_html(__('Agents', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_department_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/department.png';
                                $menu_title =  esc_html(__('Departments', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('knowledgebase', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_category_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'stafflistcategories')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/category.png';
                                $menu_title =  esc_html(__('Categories', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('knowledgebase', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_kbarticle_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'stafflistarticles')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/kb.png';
                                $menu_title =  esc_html(__('Knowledge Base', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('download', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_download_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'staffdownloads')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/download.png';
                                $menu_title =  esc_html(__('Downloads', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('announcement', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_announcement_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'staffannouncements')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/announcements.png';
                                $menu_title =  esc_html(__('Announcements', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('faq', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_faq_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'stafffaqs')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/faq.png';
                                $menu_title =  esc_html(__("FAQ's", 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                             if (in_array('helptopic', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_helptopic_agent'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/help-topic.png';
                                $menu_title =  esc_html(__("Help Topics", 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;

                            if (in_array('cannedresponses', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_cannedresponses_agent'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'agentcannedresponses')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/canned-response.png';
                                $menu_title =  esc_html(__("Canned Responses", 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;

                            if (in_array('mail', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_mail_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'mail', 'jstlay'=>'inbox')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/mails.png';
                                $menu_title =  esc_html(__('Mail', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_staff_report_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'reports', 'jstlay'=>'staffreports')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/staff-report.png';
                                $menu_title =  esc_html(__('Agent Reports', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_department_report_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'reports', 'jstlay'=>'departmentreports')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/department-report.png';
                                $menu_title =  esc_html(__('Department Reports', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (in_array('feedback', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_feedback_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'feedback', 'jstlay'=>'feedbacks')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/feedback.png';
                                $menu_title =  esc_html(__('Agent Feedbacks', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_myprofile_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'myprofile')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/profile.png';
                                $menu_title =  esc_html(__('My Profile', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_erasedata_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/user-data.png';
                                $menu_title =  esc_html(__('User Data', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_export_ticket_staff'] == 1):
                                $count ++;
                                $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'export', 'jstlay'=>'export')));
                                $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/export.png';
                                $menu_title =  esc_html(__('Export Ticket', 'js-support-ticket'));
                                JSST_printMenuLink($menu_title, $menu_url, $image_path);
                            endif;
                            if (jssupportticket::$_config['cplink_login_logout_staff'] == 1){
                                if (!is_user_logged_in()):
                                    $count ++;
                                    $menu_url = $hreflink;
                                    $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/profile.png';
                                    $menu_title =  esc_html(__('Log In', 'js-support-ticket'));
                                    JSST_printMenuLink($menu_title, $menu_url, $image_path);
                                endif;
                                if (is_user_logged_in()):
                                    $count ++;
                                    $menu_url = wp_logout_url( home_url() );
                                    $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/logout.png';
                                    $menu_title =  esc_html(__('Log Out', 'js-support-ticket'));
                                    JSST_printMenuLink($menu_title, $menu_url, $image_path);
                                endif;
                            } ?>
                        </div>
                    </div>
                    <?php
                }
                if ($count == 0) {
                    $jssupportticket_js ="
                        jQuery('#js-dash-menu-link-wrp').addClass('js-dash-menu-link-hide');
                        jQuery('.js-cp-right').addClass('js-cp-right-fullwidth');

                    ";
                    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
                }
            ?>
        </div>
        <div class="js-cp-right">
            <?php if(!is_user_logged_in()){ ?>
            <div class="js-support-ticket-cont">
                <div class="js-support-ticket-box">
                    <?php 
                        $id='';
                        if(in_array('multiform',jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1){
                            $id = "id=multiformpopup";
                        }
                    ?>

                    <img src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/dashboard/add-ticket.png"; ?>" alt="<?php echo esc_html(__('Create Ticket','js-support-ticket')); ?>" />
                    <div class="js-support-ticket-title">
                        <?php echo esc_html(__('Submit Ticket','js-support-ticket')); ?>
                    </div>
                    <div class="js-support-ticket-desc">
                        <?php echo esc_html(__('Submit ticket','js-support-ticket')); ?>
                    </div>
                    <a <?php echo esc_attr($id); ?> href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket'))); ?>" class="js-support-ticket-btn">
                        <?php echo esc_html(__('Submit Ticket','js-support-ticket')); ?>
                    </a>
                </div>
                <div class="js-support-ticket-box">
                    <img src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/dashboard/my-tickets.png"; ?>" alt="<?php echo esc_html(__('my ticket', 'js-support-ticket')); ?>" />
                    <div class="js-support-ticket-title">
                        <?php echo esc_html(__('My Tickets','js-support-ticket')); ?>
                    </div>
                    <div class="js-support-ticket-desc">
                        <?php echo esc_html(__('View all the created tickets','js-support-ticket')); ?>
                    </div>
                    <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket')));?>" class="js-support-ticket-btn">
                        <?php echo esc_html(__('My Tickets','js-support-ticket')); ?>
                    </a>
                </div>
                <div class="js-support-ticket-box">
                    <img src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/dashboard/ticket-status.png"; ?>" alt="<?php echo esc_html(__('Ticket Status','js-support-ticket')); ?>" />
                    <div class="js-support-ticket-title">
                        <?php echo esc_html(__('Ticket Status','js-support-ticket')); ?>
                    </div>
                    <div class="js-support-ticket-desc">
                        <?php echo esc_html(__('your ticket status','js-support-ticket')); ?>
                    </div>
                    <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus')));?>" class="js-support-ticket-btn">
                        <?php echo esc_html(__('Ticket Status','js-support-ticket')); ?>
                    </a>
                </div>
            </div>
            <?php } ?>
            <!-- count boxes -->
            <?php
            if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                $linkname = 'staff';
            } else {
                $linkname = 'user';
            }
            if(isset($data['count']) && jssupportticket::$_config['cplink_totalcount_'. $linkname] == 1){
                $open_percentage = 0;
                $close_percentage = 0;
                $answered_percentage = 0;
                $overdue_percentage = 0;
                $allticket_percentage = 0;
                if($data['count']['allticket'] > 0){ //to avoid division by zero error
                    $open_percentage = round(($data['count']['openticket'] / $data['count']['allticket']) * 100);
                    $close_percentage = round(($data['count']['closedticket'] / $data['count']['allticket']) * 100);
                    $answered_percentage = round(($data['count']['answeredticket'] / $data['count']['allticket']) * 100);
                    if(isset($data['count']['overdue'])){
                        $overdue_percentage = round(($data['count']['overdue'] / $data['count']['allticket']) * 100);
                    }
                    $allticket_percentage = 100;
                }
                ?>
                <div class="js-ticket-count">
                    <?php
                    if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                        $tkt_url = jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffmyticket'));
                    }else{
                        $tkt_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket'));
                    }
                    ?>
                    <div class="js-ticket-link">
                        <a class="js-ticket-link js-ticket-green" href="<?php echo esc_url($tkt_url); ?>" data-tab-number="1" title="<?php echo esc_html(__('Open Ticket','js-support-ticket')); ?>">
                            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($open_percentage); ?>" >
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
                                    echo esc_html(__('Open', 'js-support-ticket'));
                                    echo ' ( '.esc_html($data['count']['openticket']).' )';
                                ?>
                            </div>
                        </a>
                    </div>
                    <div class="js-ticket-link">
                        <a class="js-ticket-link js-ticket-red" href="<?php echo esc_url($tkt_url); ?>" data-tab-number="2" title="<?php echo esc_html(__('closed ticket','js-support-ticket')); ?>">
                            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($close_percentage); ?>" >
                                <div class="js-mr-rp" data-progress="<?php echo esc_attr($close_percentage); ?>">
                                    <div class="circle">
                                        <div class="mask full">
                                             <div class="fill js-ticket-close"></div>
                                        </div>
                                        <div class="mask half">
                                            <div class="fill js-ticket-close"></div>
                                            <div class="fill fix"></div>
                                        </div>
                                        <div class="shadow"></div>
                                    </div>
                                    <div class="inset">
                                    </div>
                                </div>
                            </div>
                            <div class="js-ticket-link-text js-ticket-red">
                                <?php
                                    echo esc_html(__('Closed', 'js-support-ticket'));
                                    echo ' ( '.esc_html($data['count']['closedticket']).' )';
                                ?>
                            </div>
                        </a>
                    </div>
                    <div class="js-ticket-link">
                        <a class="js-ticket-link js-ticket-brown" href="<?php echo esc_url($tkt_url); ?>" data-tab-number="3" title="<?php echo esc_html(__('answered ticket','js-support-ticket')); ?>">
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
                                    echo ' ( '.esc_html($data['count']['answeredticket']).' )';
                                ?>
                            </div>
                        </a>
                    </div>
                    <?php if(isset($data['count']['overdue'])){ ?>
                    <div class="js-ticket-link">
                        <a class="js-ticket-link js-ticket-orange" href="<?php echo esc_url($tkt_url); ?>" data-tab-number="5" title="<?php echo esc_html(__('overdue ticket','js-support-ticket')); ?>">
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
                                    echo ' ( '.esc_html($data['count']['overdue']).' )';
                                ?>
                            </div>
                        </a>
                    </div>
                    <?php }else{ ?>
                    <div class="js-ticket-link">
                        <a class="js-ticket-link js-ticket-orange" href="<?php echo esc_url($tkt_url); ?>" data-tab-number="4" title="<?php echo esc_html(__('overdue ticket','js-support-ticket')); ?>">
                            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($allticket_percentage); ?>" >
                                <div class="js-mr-rp" data-progress="<?php echo esc_attr($allticket_percentage); ?>">
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
                                    echo esc_html(__('All Tickets', 'js-support-ticket'));
                                    echo ' ( '.esc_html($data['count']['allticket']).' )';
                                ?>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                </div>
                <?php
            }
            ?>
            <!-- latest user tickets -->
            <?php
            if(isset($data['user-tickets']) && jssupportticket::$_config['cplink_latesttickets_user'] == 1){
                $field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1);
                $show_field = JSSTincluder::getJSModel('fieldordering')->getFieldsForListing(1);
                ?>
                <div class="js-ticket-latest-ticket-wrapper">
                    <div class="js-ticket-haeder">
                        <div class="js-ticket-header-txt">
                            <?php echo esc_html(__("Latest Tickets",'js-support-ticket')); ?>
                        </div>
                        <a class="js-ticket-header-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket'))); ?>">
                            <?php echo esc_html(__("View All Tickets",'js-support-ticket')); ?>
                        </a>
                    </div>
                    <div class="js-ticket-latest-tickets-wrp">
                        <?php
                        foreach($data['user-tickets'] as $ticket){
                            if ($ticket->status == 0) {
                                $style = "#5bb12f;";
                                $status = esc_html(__('New', 'js-support-ticket'));
                            } elseif ($ticket->status == 1) {
                                $style = "#28abe3;";
                                $status = esc_html(__('Waiting Reply', 'js-support-ticket'));
                            } elseif ($ticket->status == 2) {
                                $style = "#69d2e7;";
                                $status = esc_html(__('In Progress', 'js-support-ticket'));
                            } elseif ($ticket->status == 3) {
                                $style = "#FFB613;";
                                $status = esc_html(__('Replied', 'js-support-ticket'));
                            } elseif ($ticket->status == 4) {
                                $style = "#ed1c24;";
                                $status = esc_html(__('Closed', 'js-support-ticket'));
                            } elseif ($ticket->status == 5) {
                                $style = "#dc2742;";
                                $status = esc_html(__('Close and merge', 'js-support-ticket'));
                            }
                            $ticketviamail = '';
                            if ($ticket->ticketviaemail == 1)
                                $ticketviamail = esc_html(__('Created via Email', 'js-support-ticket'));
                            ?>
                            <div class="js-ticket-row">
                                <div class="js-ticket-first-left">
                                    <div class="js-ticket-user-img-wrp">
                                        <?php echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById($ticket->uid)), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <div class="js-ticket-ticket-subject">
                                        <div class="js-ticket-data-row">
                                            <?php echo esc_html($ticket->name); ?>
                                        </div>
                                        <div class="js-ticket-data-row name">
                                            <a class="js-ticket-data-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=> $ticket->id))); ?>"><?php echo esc_html($ticket->subject); ?></a>
                                        </div>
                                        <div class="js-ticket-data-row">
                                            <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['department'])). ' : '; ?></span>
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->departmentname)); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="js-ticket-second-left">
                                    <?php if ($ticket->ticketviaemail == 1){  ?>
                                        <span class="js-ticket-creade-via-email-spn"><?php echo esc_html($ticketviamail); ?></span>
                                    <?php } ?>
                                    <?php
                                    $counter = 'one';
                                    if ($ticket->lock == 1) {
                                        ?>
                                        <img class="ticketstatusimage <?php echo esc_attr($counter);
                                        $counter = 'two'; ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/lock.png"; ?>" title="<?php echo esc_html(__('The ticket is locked', 'js-support-ticket')); ?>" />
                                    <?php } ?>
                                    <?php if ($ticket->isoverdue == 1) { ?>
                                            <img class="ticketstatusimage <?php echo esc_attr($counter); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/over-due.png"; ?>" title="<?php echo esc_html(__('This ticket is marked as overdue', 'js-support-ticket')); ?>" />
                                    <?php } ?>
                                    <span class="js-ticket-status" style="color:<?php echo esc_attr($style); ?>">
                                        <?php echo esc_html($status); ?>
                                    </span>
                                </div>
                                <div class="js-ticket-third-left">
                                    <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->created))); ?>
                                </div>
                                <div class="js-ticket-fourth-left">
                                    <span class="js-ticket-priorty" style="background:<?php echo esc_attr($ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?></span>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!-- agent data chart -->
            <?php
            if(isset(jssupportticket::$_data['stack_chart_horizontal']) && jssupportticket::$_config['cplink_ticketstats_staff'] == 1){
                ?>
                <div class="js-pm-graphtitle-wrp">
                    <div class="js-pm-graphtitle">
                        <?php echo esc_html(__('Ticket Statistics', 'js-support-ticket')); ?>
                    </div>
                    <div id="js-pm-grapharea">
                        <div id="stack_chart_horizontal" style="width:100%;"></div>
                    </div>
                </div>
                <?php
            }
            ?>
            <!-- latest downloads -->
            <?php
            if(isset($data['latest-downloads']) && jssupportticket::$_config['cplink_latestdownloads_'. $linkname] == 1){
                ?>
                <div class="js-ticket-data-list-wrp latst-dnlds">
                    <div class="js-ticket-haeder">
                        <div class="js-ticket-header-txt">
                            <?php echo esc_html(__("Latest Downloads",'js-support-ticket')); ?>
                        </div>
                        <a class="js-ticket-header-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'download','jstlay'=>'downloads'))); ?>"><?php echo esc_html(__("View All Downloads",'js-support-ticket')); ?></a>
                    </div>
                    <div class="js-ticket-data-list">
                        <?php
                        $imgindex = 1;
                        foreach($data['latest-downloads'] as $download){
                            ?>
                            <div class="js-ticket-data">
                                <div class="js-ticket-data-image">
                                    <img alt="image" class="js-ticket-data-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/downloadicon/download-<?php echo esc_attr($imgindex); ?>.png" />
                                </div>
                                <div class="js-ticket-data-tit">
                                    <?php echo esc_html($download->title); ?>
                                </div>
                                <button type="button" class="js-ticket-data-btn" onclick="getDownloadById(<?php echo esc_js($download->downloadid) ?>)">
                                    <?php echo esc_html(__('Download','js-support-ticket')); ?>
                                </button>
                            </div>
                            <?php
                            $imgindex = $imgindex==6 ? 1 : $imgindex+1;
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!-- latest announcements -->
            <?php
            if(isset($data['latest-announcements']) && jssupportticket::$_config['cplink_latestannouncements_'. $linkname] == 1){
                ?>
                <div class="js-ticket-data-list-wrp latst-ancmts">
                    <div class="js-ticket-haeder">
                        <div class="js-ticket-header-txt">
                            <?php echo esc_html(__("Latest Announcements",'js-support-ticket')); ?>
                        </div>
                        <a class="js-ticket-header-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement','jstlay'=>'announcements'))); ?>">
                            <?php echo esc_html(__("View All Announcements",'js-support-ticket')); ?>
                        </a>
                    </div>
                    <div class="js-ticket-data-list">
                        <?php
                        $imgindex = 1;
                        foreach($data['latest-announcements'] as $announcement){
                            ?>
                            <div class="js-ticket-data">
                                <div class="js-ticket-data-image">
                                    <img alt="img" class="js-ticket-data-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/announcement/announcement-<?php echo esc_attr($imgindex); ?>.png" />
                                </div>
                                <a class="js-ticket-data-tit" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'announcementdetails', 'jssupportticketid'=>$announcement->id))); ?>">
                                    <?php echo esc_html($announcement->title); ?>
                                </a>
                            </div>
                            <?php
                            $imgindex = $imgindex==6 ? 1 : $imgindex+1;
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!-- latest articles -->
            <?php
            if(isset($data['latest-articles']) && jssupportticket::$_config['cplink_latestkb_'. $linkname] == 1){
                ?>
                <div class="js-ticket-data-list-wrp latst-kb">
                    <div class="js-ticket-haeder">
                        <div class="js-ticket-header-txt">
                            <?php echo esc_html(__("Latest Knowledge Base",'js-support-ticket')); ?>
                        </div>
                        <a class="js-ticket-header-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase','jstlay'=>'userknowledgebase'))); ?>">
                            <?php echo esc_html(__("View All Knowledge Base",'js-support-ticket')); ?>

                        </a>
                    </div>
                    <div class="js-ticket-data-list">
                        <?php
                        $imgindex = 1;
                        foreach($data['latest-articles'] as $article){
                            ?>
                            <div class="js-ticket-data">
                                <div class="js-ticket-data-image">
                                    <img alt="image" class="js-ticket-data-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/kb/kb-<?php echo esc_attr($imgindex); ?>.png" />
                                </div>
                                <a class="js-ticket-data-tit" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'articledetails', 'jssupportticketid'=>$article->articleid))); ?>">
                                    <?php echo esc_html($article->subject); ?>
                                </a>
                            </div>
                            <?php
                            $imgindex = $imgindex==6 ? 1 : $imgindex+1;
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!-- latest faqs -->
            <?php
            if(isset($data['latest-faqs'])  && jssupportticket::$_config['cplink_latestfaqs_'. $linkname] == 1){
                ?>
                <div class="js-ticket-data-list-wrp latst-faqs">
                    <div class="js-ticket-haeder">
                        <div class="js-ticket-header-txt">
                            <?php echo esc_html(__("Latest FAQs",'js-support-ticket')); ?>
                        </div>
                        <a class="js-ticket-header-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq','jstlay'=>'faqs'))); ?>">
                            <?php echo esc_html(__("View All FAQs",'js-support-ticket')); ?>
                        </a>
                    </div>
                    <div class="js-ticket-data-list">
                        <?php
                        $imgindex = 1;
                        foreach($data['latest-faqs'] as $faq){
                            ?>
                            <div class="js-ticket-data">
                                <div class="js-ticket-data-image">
                                    <img alt="image" class="js-ticket-data-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/faq/faq-<?php echo esc_attr($imgindex); ?>.png" />
                                </div>
                                <a class="js-ticket-data-tit" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'faqdetails', 'jssupportticketid'=>$faq->id))); ?>">
                                    <?php echo esc_html($faq->subject); ?>
                                </a>
                            </div>
                            <?php
                            $imgindex = $imgindex==6 ? 1 : $imgindex+1;
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <!-- latest agent tickets -->
        <?php
        if(isset($data['agent-tickets']) && jssupportticket::$_config['cplink_latesttickets_staff'] == 1){
            $field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1);
            $show_field = JSSTincluder::getJSModel('fieldordering')->getFieldsForListing(1);
            ?>
            <div class="js-ticket-latest-ticket-wrapper">
                <div class="js-ticket-haeder">
                    <div class="js-ticket-header-txt">
                        <?php echo esc_html(__("Latest Tickets",'js-support-ticket')); ?>
                    </div>
                    <a class="js-ticket-header-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','jstlay'=>'staffmyticket'))); ?>"><?php echo esc_html(__("View All Tickets",'js-support-ticket')); ?></a>
                </div>
                <div class="js-ticket-latest-tickets-wrp">
                    <?php
                    foreach($data['agent-tickets'] as $ticket){
                        if ($ticket->status == 0) {
                            $style = "#5bb12f;";
                            $status = esc_html(__('New', 'js-support-ticket'));
                        } elseif ($ticket->status == 1) {
                            $style = "#28abe3;";
                            $status = esc_html(__('Waiting Reply', 'js-support-ticket'));
                        } elseif ($ticket->status == 2) {
                            $style = "#69d2e7;";
                            $status = esc_html(__('In Progress', 'js-support-ticket'));
                        } elseif ($ticket->status == 3) {
                            $style = "#FFB613;";
                            $status = esc_html(__('Replied', 'js-support-ticket'));
                        } elseif ($ticket->status == 4) {
                            $style = "#ed1c24;";
                            $status = esc_html(__('Closed', 'js-support-ticket'));
                        } elseif ($ticket->status == 5) {
                            $style = "#dc2742;";
                            $status = esc_html(__('Close and merge', 'js-support-ticket'));
                        }
                        $ticketviamail = '';
                        if ($ticket->ticketviaemail == 1)
                            $ticketviamail = esc_html(__('Created via Email', 'js-support-ticket'));
                        ?>
                        <div class="js-ticket-row">
                            <div class="js-col-xs-12 js-col-md-12 js-ticket-toparea">
                                <div class="js-ticket-first-left">
                                    <div class="js-ticket-user-img-wrp">
                                        <?php if (in_array('agent',jssupportticket::$_active_addons) && $ticket->staffphoto) { ?>
                                            <img class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $ticket->staffid ,'jsstpageid'=>get_the_ID())));?> ">
                                        <?php } else {
                                            echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById($ticket->uid)), JSST_ALLOWED_TAGS);
                                        } ?>
                                    </div>
                                    <div class="js-ticket-ticket-subject">
                                        <div class="js-ticket-data-row">
                                            <?php echo esc_html($ticket->name); ?>
                                        </div>
                                        <div class="js-ticket-data-row name">
                                            <a class="js-ticket-data-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=> $ticket->id))); ?>">
                                                <?php echo esc_html($ticket->subject); ?>
                                            </a>
                                        </div>
                                        <div class="js-ticket-data-row">
                                            <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['department'])). ' : '; ?></span>
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->departmentname)); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="js-ticket-second-left">
                                    <?php
                                    if ($ticket->ticketviaemail == 1){  ?>
                                        <span class="js-ticket-creade-via-email-spn"><?php echo esc_html($ticketviamail); ?></span>
                                    <?php } ?>
                                    <?php
                                    $counter = 'one';
                                    if ($ticket->lock == 1) { ?>
                                        <img class="ticketstatusimage <?php echo esc_attr($counter);
                                            $counter = 'two'; ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/lock.png"; ?>" title="<?php echo esc_html(__('The ticket is locked', 'js-support-ticket')); ?>" />
                                    <?php } ?>
                                    <?php if ($ticket->isoverdue == 1) { ?>
                                            <img class="ticketstatusimage <?php echo esc_attr($counter); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/over-due.png"; ?>" title="<?php echo esc_html(__('This ticket is marked as overdue', 'js-support-ticket')); ?>" />
                                    <?php } ?>
                                    <span class="js-ticket-status" style="color:<?php echo esc_attr($style); ?>">
                                        <?php echo esc_html($status); ?>
                                    </span>
                                </div>
                                <div class="js-ticket-third-left">
                                    <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->created))); ?>
                                </div>
                                <div class="js-ticket-fourth-left">
                                    <span class="js-ticket-priorty" style="background:<?php echo esc_attr($ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>


    <div id="js-ticket-main-black-background" style="display:none;"></div>
    <div id="js-ticket-main-popup" style="display:none;">
        <span id="js-ticket-popup-title"></span>
        <span id="js-ticket-popup-close-button"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" /></span>
        <div id="js-ticket-main-content"></div>
        <div id="js-ticket-main-downloadallbtn"></div>
    </div>

    <?php
    // Permission setting for notification
    } else {
        JSSTlayout::getSystemOffline();
    }

    function JSST_printMenuLink($title,$url,$image_path, $ajaxid=""){
        $html = '
        <a class="js-col-xs-12 js-col-sm-6 js-col-md-4 js-ticket-dash-menu" href="'.esc_url($url).'" '.$ajaxid.'>
            <span class="js-ticket-dash-menu-icon">
                <img class="js-ticket-dash-menu-img" alt="menu-link-image" src="'.esc_url($image_path).'" />
            </span>
            <span class="js-ticket-dash-menu-text">'.esc_html($title).'</span>
        </a>';
        echo  wp_kses($html, JSST_ALLOWED_TAGS);
        return;
    }
 ?>

</div>

