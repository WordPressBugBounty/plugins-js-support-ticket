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

    $jssupportticket_js = "
    google.load('visualization', '1', {packages:['corechart']});
    google.setOnLoadCallback(drawStackChartHorizontal);
    function drawStackChartHorizontal() {
      var data = google.visualization.arrayToDataTable([
        " . jssupportticket::$_data['stack_chart_horizontal']['title'] . ",
        " . jssupportticket::$_data['stack_chart_horizontal']['data'] . "
      ]);

      var options = {
        title: '" . esc_js(__('Tickets Over Time', 'js-support-ticket')) . "',
        height: 300,
        chartArea: { width: '80%' },
        legend: { position: 'top' },
        curveType: 'function',
        areaOpacity: 0.2,
        lineWidth: 2,
        pointSize: 5,
        colors: ['#ff652f','#5ab9ea','#d89922','#14a76c']
      };

      var chart = new google.visualization.AreaChart(
        document.getElementById('stack_chart_horizontal')
      );
      chart.draw(data, options);
    }
    ";
    wp_add_inline_script('ticket-google-charts-handle', $jssupportticket_js);
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
    function getDownloadById(value, nonce) {
        ajaxurl = "'.esc_url(admin_url('admin-ajax.php')).'";
        jQuery.post(ajaxurl, {action: "jsticket_ajax", downloadid: value, jstmod: "download", task: "getDownloadById",jsstpageid:'.get_the_ID().', "_wpnonce": nonce}, function (data) {
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
<div class="jsst-main-up-wrapper js-ticket-dashboard-container">
    <div class="js-ticket-dashboard-main-content">
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


    <main class="js-ticket-dashboard-main">
        <!-- cp links for user -->
        <?php
            if ($agent_flag == 0) { ?>
                <aside class="js-ticket-dashboard-left-menu"><!-- Dashboard Links -->
                    <div class="js-ticket-menu-header"><?php echo esc_html( __( 'Dashboard Links', 'js-support-ticket' ) ); ?></div>
                    <ul class="js-ticket-menu-links">
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
                            $menu_title =  esc_html(__('Submit Ticket', 'js-support-ticket'));
                            ?>
                            <li>
                                <a <?php echo $ajaxid; ?> href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_myticket_user'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket')));
                            $menu_title =  esc_html(__('My Tickets', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 12h-6l-2 3h-4l-2-3H2"></path>
                                        <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_checkticketstatus_user'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus')));
                            $menu_title =  esc_html(__('Ticket Status', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('announcement', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_announcements_user'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'announcements')));
                            $menu_title =  esc_html(__('Announcements', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('download', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_downloads_user'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'downloads')));
                            $menu_title =  esc_html(__('Downloads', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('faq', jssupportticket::$_active_addons) &&  jssupportticket::$_config['cplink_faqs_user'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'faqs')));
                            $menu_title =  esc_html(__("FAQ's", 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('knowledgebase', jssupportticket::$_active_addons) &&  jssupportticket::$_config['cplink_knowledgebase_user'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'userknowledgebase')));
                            $menu_title =  esc_html(__('Knowledge Base', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#d946ef" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_erasedata_user'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest')));
                            $image_path = JSST_PLUGIN_URL . 'includes/images/left-icons/menu/user-data.png';
                            $menu_title =  esc_html(__('User Data', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-linecap="round" stroke-linejoin="round">
                                      <circle cx="8" cy="7" r="4"/>
                                      <path d="M12 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                      <path d="M15 11h6v9h-6z"/>
                                      <path d="M15 11h6l-1-3h-4z"/>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
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
                                    $menu_title =  esc_html(__('Log In', 'js-support-ticket'));
                                    ?>
                                    <li>
                                        <a href="<?php echo esc_url($menu_url); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                                <polyline points="10 17 15 12 10 7"></polyline>
                                                <line x1="15" y1="12" x2="3" y2="12"></line>
                                            </svg>
                                            <span><?php echo esc_html($menu_title); ?></span>
                                        </a>
                                    </li>
                                    <?php
                                endif;
                            if (is_user_logged_in()):
                                $menu_url = wp_logout_url( home_url() );
                                $menu_title =  esc_html(__('Log Out', 'js-support-ticket'));
                                ?>
                                <li>
                                    <a href="<?php echo esc_url($menu_url); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                            <polyline points="16 17 21 12 16 7"></polyline>
                                            <line x1="21" y1="12" x2="9" y2="12"></line>
                                        </svg>
                                        <span><?php echo esc_html($menu_title); ?></span>
                                    </a>
                                </li>
                                <?php
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
                                    $menu_title =  esc_html(__('Register', 'js-support-ticket'));
                                    ?>
                                    <li>
                                        <a href="<?php echo esc_url($menu_url); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" 
                                                viewBox="0 0 24 24" 
                                                fill="none" 
                                                stroke="#10b981" 
                                                stroke-linecap="round" 
                                                stroke-linejoin="round" 
                                                stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                                <line x1="23" y1="11" x2="17" y2="11"></line>
                                            </svg>
                                            <span><?php echo esc_html($menu_title); ?></span>
                                        </a>
                                    </li>
                                    <?php
                                }
                            endif;
                        } ?>
                    </ul>
                </aside>
                <?php
            }
        ?>

        <!-- cp links for agent -->
        <?php
            if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) { ?>
                <aside class="js-ticket-dashboard-left-menu">
                    <div class="js-ticket-menu-header"><?php echo esc_html( __( 'Dashboard Links', 'js-support-ticket' ) ); ?></div>
                    <ul class="js-ticket-menu-links">  <!-- Dashboard Links -->
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
                            $menu_title =  esc_html(__('Submit Ticket', 'js-support-ticket'));
                            ?>
                            <li>
                                <a <?php echo $ajaxid; ?> href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    <span>
                                        <?php echo esc_html($menu_title); ?>
                                    </span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_myticket_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffmyticket')));
                            $menu_title =  esc_html(__('My Tickets', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"></path><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
                                    <span>
                                        <?php echo esc_html($menu_title); ?>
                                    </span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_roles_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'role', 'jstlay'=>'roles')));
                            $menu_title =  esc_html(__('Agent Roles', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href="<?php echo esc_url($menu_url); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    <span>
                                        <?php echo esc_html($menu_title); ?>
                                    </span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_staff_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffs')));
                            $menu_title =  esc_html(__('Agents', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php 
                        endif;
                        if (jssupportticket::$_config['cplink_department_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'departments')));
                            $menu_title =  esc_html(__('Departments', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php 
                        endif;
                        if (in_array('knowledgebase', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_category_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'stafflistcategories')));
                            $menu_title =  esc_html(__('Categories', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-linecap="round" stroke-linejoin="round"><path d="m22 19-3-3-3 3-3-3-3 3-3-3-3 3-3-3"></path><path d="m22 12-3-3-3 3-3-3-3 3-3-3-3 3-3-3"></path><path d="m22 5-3-3-3 3-3-3-3 3-3-3-3 3-3-3"></path></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('knowledgebase', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_kbarticle_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'stafflistarticles')));
                            $menu_title =  esc_html(__('Knowledge Base', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#d946ef" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('download', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_download_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'staffdownloads')));
                            $menu_title =  esc_html(__('Downloads', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('announcement', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_announcement_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'staffannouncements')));
                            $menu_title =  esc_html(__('Announcements', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#14b8a6" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('faq', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_faq_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'stafffaqs')));
                            $menu_title =  esc_html(__("FAQ's", 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round"><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                         if (in_array('helptopic', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_helptopic_agent'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'agenthelptopics')));
                            $menu_title =  esc_html(__("Help Topics", 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#84cc16" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><line x1="10" y1="9" x2="8" y2="9"></line></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;

                        if (in_array('cannedresponses', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_cannedresponses_agent'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'cannedresponses', 'jstlay'=>'agentcannedresponses')));
                            $menu_title =  esc_html(__("Canned Responses", 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M8 12h8"></path><path d="M12 8v8"></path></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;

                        if (in_array('mail', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_mail_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'mail', 'jstlay'=>'inbox')));
                            $menu_title =  esc_html(__('Mail', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_staff_report_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'reports', 'jstlay'=>'staffreports')));
                            $menu_title =  esc_html(__('Agent Reports', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M12 8V4M6 20v-2M18 20v-4M6 12v-2M18 10V8"></path></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_department_report_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'reports', 'jstlay'=>'departmentreports')));
                            $menu_title =  esc_html(__('Department Reports', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('feedback', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_feedback_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'feedback', 'jstlay'=>'feedbacks')));
                            $menu_title =  esc_html(__('Agent Feedbacks', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#eab308" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"></path><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"></polygon></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_myprofile_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'myprofile')));
                            $menu_title =  esc_html(__('My Profile', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#78716c" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (jssupportticket::$_config['cplink_erasedata_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'gdpr', 'jstlay'=>'adderasedatarequest')));
                            $menu_title =  esc_html(__('User Data', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-linecap="round" stroke-linejoin="round">
                                      <circle cx="8" cy="7" r="4"/>
                                      <path d="M12 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                      <path d="M15 11h6v9h-6z"/>
                                      <path d="M15 11h6l-1-3h-4z"/>
                                    </svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
                        endif;
                        if (in_array('export', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_export_ticket_staff'] == 1):
                            $count ++;
                            $menu_url = esc_url(jssupportticket::makeUrl(array('jstmod'=>'export', 'jstlay'=>'export')));
                            $menu_title =  esc_html(__('Export Ticket', 'js-support-ticket'));
                            ?>
                            <li>
                                <a href=<?php echo esc_url($menu_url); ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="12" y2="12"></line><line x1="15" y1="15" x2="12" y2="12"></line></svg>
                                    <span><?php echo esc_html($menu_title); ?></span>
                                </a>
                            </li>
                            <?php
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
                                $menu_title =  esc_html(__('Log Out', 'js-support-ticket'));
                                ?>
                                <li>
                                    <a href=<?php echo esc_url($menu_url); ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                            <polyline points="16 17 21 12 16 7"></polyline>
                                            <line x1="21" y1="12" x2="9" y2="12"></line>
                                        </svg>
                                        <span><?php echo esc_html($menu_title); ?></span>
                                    </a>
                                </li>
                                <?php
                            endif;
                        } ?>
                    </ul>
                </aside>
                <?php
            }
            if ($count == 0) {
                $jssupportticket_js ="
                    jQuery('.js-ticket-dashboard-left-menu').addClass('js-dash-menu-link-hide');
                    jQuery('.js-ticket-dashboard-content-area').addClass('js-cp-right-fullwidth');

                ";
                wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
            }
        ?>
        <div class="js-ticket-dashboard-content-area">
            <?php if(!is_user_logged_in()){ ?>
                <div class="js-ticket-welcome-header">
                    <div class="js-ticket-welcome-left">
                        <?php
                        $uid = JSSTincluder::getObjectClass('user')->uid();
                        echo wp_kses(jsst_get_avatar($uid, 'js-ticket-welcome-avatar'), JSST_ALLOWED_TAGS);
                        ?>
                        <div>
                            <span>
                                <?php echo esc_html__('Welcome back to', 'js-support-ticket'); ?>
                                <?php echo ', '.esc_html(jssupportticket::$_config['title']).'!'; ?>
                            </span>
                            <p><?php echo esc_html__('As a visitor, you can easily create a support ticket by sharing your issue details.', 'js-support-ticket'); ?></p>
                        </div>
                    </div>
                    <div class="js-ticket-welcome-actions">
                    </div>
                </div>
                <div class="js-ticket-card">
                    <?php 
                        $id='';
                        if(in_array('multiform',jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1){
                            $id = "id=multiformpopup";
                        }
                    ?>
                    <span><?php echo esc_html__('Have an issue?', 'js-support-ticket'); ?></span>
                    <p><?php echo esc_html__('Our support team is here to help. Create a new ticket to get started.', 'js-support-ticket'); ?></p>
                    <a <?php echo esc_attr($id); ?> href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'addticket'))); ?>" class="js-ticket-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <?php echo esc_html__('Create New Ticket', 'js-support-ticket'); ?>
                    </a>
                </div>
                <div class="js-ticket-card">
                    <span><?php echo esc_html__('Check Ticket Status', 'js-support-ticket'); ?></span>
                    <p><?php echo esc_html__('Enter your information to check the status of your existing ticket.', 'js-support-ticket'); ?></p>
                    <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'ticketstatus')));?>" class="js-ticket-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <?php echo esc_html__('Check Ticket Status', 'js-support-ticket'); ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="js-ticket-welcome-header">
                    <div class="js-ticket-welcome-left">
                        <?php
                        $uid = JSSTincluder::getObjectClass('user')->uid();
                        echo wp_kses(jsst_get_avatar($uid, 'js-ticket-welcome-avatar'), JSST_ALLOWED_TAGS);
                        ?>
                        <div>
                            <span><?php echo esc_html( __( 'Welcome back,', 'js-support-ticket' ) ).' '.esc_html(jssupportticket::$_data[0]['user-name']).'!'; ?></span>
                            <p>
                                <?php
                                if ($data['count']['openticket'] > 0) {
                                    // Translators: %s is the number of open tickets.
                                    echo wp_kses_post( __( 'You have', 'js-support-ticket' ) . ' ' );
                                    echo wp_kses_post(
                                        '<strong>' . intval( $data['count']['openticket'] ) . ' ' . __( 'open tickets', 'js-support-ticket' ) . '</strong>'
                                    );
                                } else {
                                    // Translators: The <strong> tags should be kept around "No open ticket".
                                    echo wp_kses_post( __( 'You have', 'js-support-ticket' ) . ' ' );
                                    echo wp_kses_post(
                                        '<strong>' . __( 'No open ticket', 'js-support-ticket' ) . '</strong>'
                                    );
                                }
                                if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                                    if (!empty($data['count']['pendingticket'])) {
                                        // Translators: %s is the number of pending tickets awaiting the user's reply.
                                        echo esc_html( __( ', and', 'js-support-ticket' ) . ' ' );
                                        echo wp_kses_post(
                                            '<strong>' . intval( $data['count']['pendingticket'] ) . ' ' . __( 'is awaiting your reply.', 'js-support-ticket' ) . '</strong>'
                                        );
                                    }
                                } else {
                                    if (!empty($data['count']['answeredticket'])) {
                                        // Translators: %s is the number of answered tickets awaiting the user's reply.
                                        echo wp_kses_post( __( ', and', 'js-support-ticket' ) . ' ' );
                                        echo wp_kses_post(
                                            '<strong>' . intval( $data['count']['answeredticket'] ) . ' ' . __( 'is awaiting your reply.', 'js-support-ticket' ) . '</strong>'
                                        );
                                    }
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="js-ticket-welcome-actions">
                    </div>
                </div>
                <div class="js-ticket-dashboard-grid-2-col">
                    <?php
                    $ajaxid = "";
                    $count++;

                    // Multiform popup
                    if (in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1) {
                        $ajaxid = "id=multiformpopup";
                    }

                    // Defaults
                    $show_block   = false;
                    $heading      = '';
                    $description  = '';
                    $button_text  = '';
                    $menu_url     = '';
                    $has_links    = false;

                    // Decide if staff or user
                    $is_staff = in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff();

                    // Staff case
                    if ($is_staff && !empty(jssupportticket::$_config['cplink_openticket_staff']) && jssupportticket::$_config['cplink_openticket_staff'] == 1) {
                        $show_block  = true;
                        $heading     = __( 'Need to assist a user?', 'js-support-ticket' );
                        $description = __( 'Quickly create a new ticket on behalf of a user to provide support and track their request.', 'js-support-ticket' );
                        $button_text = __( 'Open ticket for user', 'js-support-ticket' );
                        $menu_url    = esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffaddticket')));
                    }

                    // User case
                    if (!$is_staff && !empty(jssupportticket::$_config['cplink_openticket_user']) && jssupportticket::$_config['cplink_openticket_user'] == 1) {
                        $show_block  = true;
                        $heading     = __( 'Have an issue?', 'js-support-ticket' );
                        $description = __( 'Our support team is here to help. Create a new ticket to get started.', 'js-support-ticket' );
                        $button_text = __( 'Create New Ticket', 'js-support-ticket' );
                        $menu_url    = esc_url(jssupportticket::makeUrl(array('jstmod' => 'ticket', 'jstlay' => 'addticket')));
                    }

                    // Staff extra links check
                    if ($is_staff) {
                        $has_links = (
                            jssupportticket::$_config['cplink_department_staff'] == 1 ||
                            (in_array('knowledgebase', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_category_staff'] == 1) ||
                            (in_array('knowledgebase', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_kbarticle_staff'] == 1) ||
                            (in_array('download', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_download_staff'] == 1) ||
                            (in_array('announcement', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_announcement_staff'] == 1) ||
                            (in_array('faq', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_faq_staff'] == 1) ||
                            (in_array('helptopic', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_helptopic_agent'] == 1)
                        );
                    }

                    // Only show wrapper if needed
                    if ($show_block || $has_links) : ?>
                        <div class="js-ticket-new-ticket-card">
                            <?php if ($show_block) : ?>
                                <span><?php echo esc_html($heading); ?></span>
                                <p><?php echo esc_html($description); ?></p>
                                <a <?php echo $ajaxid; ?> href="<?php echo esc_url($menu_url); ?>" class="js-ticket-new-ticket-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <?php echo esc_html($button_text); ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($is_staff && $has_links) : ?>
                                <?php if ($show_block) : ?>
                                    <div class="js-ticket-or-separator">
                                        <span class="js-ticket-line"></span>
                                        <span class="js-ticket-or-text"><?php echo esc_html(__('OR Create a', 'js-support-ticket')); ?></span>
                                        <span class="js-ticket-line"></span>
                                    </div>
                                <?php endif; ?>

                                <div class="js-ticket-create-section">
                                    <div class="js-ticket-tag-buttons">
                                        <?php if (jssupportticket::$_config['cplink_department_staff'] == 1) : ?>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'adddepartment'))); ?>" class="js-ticket-tag-btn">
                                                <?php echo esc_html(__('Department', 'js-support-ticket')); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (in_array('knowledgebase', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_category_staff'] == 1) : ?>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'addcategory'))); ?>" class="js-ticket-tag-btn">
                                                <?php echo esc_html(__('Category', 'js-support-ticket')); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (in_array('knowledgebase', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_kbarticle_staff'] == 1) : ?>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'addarticle'))); ?>" class="js-ticket-tag-btn">
                                                <?php echo esc_html(__('Knowledge Base', 'js-support-ticket')); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (in_array('download', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_download_staff'] == 1) : ?>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'adddownload'))); ?>" class="js-ticket-tag-btn">
                                                <?php echo esc_html(__('Download', 'js-support-ticket')); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (in_array('announcement', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_announcement_staff'] == 1) : ?>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'addannouncement'))); ?>" class="js-ticket-tag-btn">
                                                <?php echo esc_html(__('Announcement', 'js-support-ticket')); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (in_array('faq', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_faq_staff'] == 1) : ?>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'addfaq'))); ?>" class="js-ticket-tag-btn">
                                                <?php echo esc_html(__('FAQ\'s', 'js-support-ticket')); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (in_array('helptopic', jssupportticket::$_active_addons) && jssupportticket::$_config['cplink_helptopic_agent'] == 1) : ?>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'helptopic', 'jstlay'=>'addhelptopic'))); ?>" class="js-ticket-tag-btn">
                                                <?php echo esc_html(__('Help Topic', 'js-support-ticket')); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php
                    if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                        $linkname = 'staff';
                    } else {
                        $linkname = 'user';
                    }
                    if(isset($data['count']) && jssupportticket::$_config['cplink_totalcount_'. $linkname] == 1){
                        if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                            $tkt_url = jssupportticket::makeUrl(array('jstmod'=>'agent', 'jstlay'=>'staffmyticket'));
                        }else{
                            $tkt_url = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'jstlay'=>'myticket'));
                        }?>
                        <div class="js-ticket-stats-card">
                            <span class="js-ticket-stats-card-heading"><?php echo esc_html( __( 'Your Ticket Stats', 'js-support-ticket' ) ); ?></span>
                            <div class="js-ticket-stats-list">
                                <a title="<?php echo esc_html(__('Open Ticket','js-support-ticket')); ?>" href="<?php echo esc_url($tkt_url); ?>" data-tab-number="1" class="js-ticket-stat-item js-ticket-link">
                                    <span class="js-ticket-stat-item-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                        <span><?php echo esc_html( __( 'Open Tickets', 'js-support-ticket' ) ); ?></span>
                                    </span>
                                    <?php
                                    // show counts accoridng to configuration
                                    if(jssupportticket::$_config['count_on_myticket'] == 1) { ?>
                                        <span class="js-ticket-stat-count" style="background-color: #dbeafe; color: #3b82f6;">
                                            <?php echo esc_html($data['count']['openticket']); ?>
                                        </span>
                                        <?php
                                    } ?>
                                </a>
                                <a href="<?php echo esc_url($tkt_url); ?>" data-tab-number="2" title="<?php echo esc_html(__('closed ticket','js-support-ticket')); ?>" class="js-ticket-stat-item js-ticket-link">
                                    <span class="js-ticket-stat-item-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        <span><?php echo esc_html( __( 'Closed Tickets', 'js-support-ticket' ) ); ?></span>
                                    </span>
                                    <?php
                                    // show counts accoridng to configuration
                                    if(jssupportticket::$_config['count_on_myticket'] == 1) { ?>
                                        <span class="js-ticket-stat-count" style="background-color: #e5e7eb; color: #4b5563;">
                                            <?php echo esc_html($data['count']['closedticket']); ?>
                                        </span>
                                        <?php
                                    } ?>
                                </a>
                                <a href="<?php echo esc_url($tkt_url); ?>" data-tab-number="3" title="<?php echo esc_html(__('answered ticket','js-support-ticket')); ?>" class="js-ticket-stat-item js-ticket-link">
                                    <span class="js-ticket-stat-item-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
                                        <span><?php echo esc_html( __( 'Answered Tickets', 'js-support-ticket' ) ); ?></span>
                                    </span>
                                    <?php
                                    if(jssupportticket::$_config['count_on_myticket'] == 1) { ?>
                                        <span class="js-ticket-stat-count" style="background-color: #d1fae5; color: #10b981;">
                                            <?php echo esc_html($data['count']['answeredticket']); ?>
                                        </span>
                                        <?php
                                    } ?>
                                </a>
                                <?php
                                if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) { ?>
                                    <?php if(isset($data['count']['overdue'])){ ?>
                                        <a href="<?php echo esc_url($tkt_url); ?>" data-tab-number="5" title="<?php echo esc_html(__('overdue ticket','js-support-ticket')); ?>" class="js-ticket-stat-item js-ticket-link">
                                            <span class="js-ticket-stat-item-label">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                <span><?php echo esc_html( __( 'Overdue Tickets', 'js-support-ticket' ) ); ?></span>
                                            </span>
                                            <?php
                                            if(jssupportticket::$_config['count_on_myticket'] == 1) { ?>
                                                <span class="js-ticket-stat-count" style="background-color: #fee2e2; color: #dc2626;">
                                                    <?php echo esc_html($data['count']['overdue']); ?>
                                                </span>
                                                <?php
                                            } ?>
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo esc_url($tkt_url); ?>" data-tab-number="4" title="<?php echo esc_html(__('all ticket','js-support-ticket')); ?>" class="js-ticket-stat-item js-ticket-link">
                                            <span class="js-ticket-stat-item-label">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                <span><?php echo esc_html( __( 'All Tickets', 'js-support-ticket' ) ); ?></span>
                                            </span>
                                            <?php
                                            if(jssupportticket::$_config['count_on_myticket'] == 1) { ?>
                                                <span class="js-ticket-stat-count" style="background-color: #fee2e2; color: #dc2626;">
                                                    <?php echo esc_html($data['count']['allticket']); ?>
                                                </span>
                                                <?php
                                            } ?>
                                        </a>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
            } ?>
            <!-- latest user tickets -->
            <?php
            if(isset($data['user-tickets']) && jssupportticket::$_config['cplink_latesttickets_user'] == 1){
                ?>
                <div class="js-ticket-tickets-container">
                    <div class="js-ticket-container-header">
                        <span><?php echo esc_html( __( 'Recent Tickets', 'js-support-ticket' ) ); ?></span>
                        <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket'))); ?>"><?php echo esc_html( __( 'View All', 'js-support-ticket' ) ); ?></a>
                    </div>
                    <div class="js-ticket-container-content">
                        <div class="js-ticket-latest-tickets-wrp">
                            <?php
                            $fields_array = array(); // Array for form fields
                            $show_on_listing_arrays = array(); // Array for visible form fields
                            foreach($data['user-tickets'] as $ticket){
                                // Check if the form fields are already array
                                if (!isset($fields_array[$ticket->multiformid])) {
                                    $fields_array[$ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, $ticket->multiformid);
                                }
                                if (!isset($show_on_listing_arrays[$ticket->multiformid])) {
                                    $show_on_listing_arrays[$ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldsForListing(1, $ticket->multiformid);
                                }
                                // Now use the cached field array
                                $field_array = $fields_array[$ticket->multiformid];
                                $show_on_listing_array = $show_on_listing_arrays[$ticket->multiformid];
                                $ticketviamail = '';
                                if ($ticket->ticketviaemail == 1)
                                    $ticketviamail = esc_html(__('Created via Email', 'js-support-ticket'));
                                ?>
                                <div class="js-ticket-row">
                                    <div class="js-ticket-user-img-wrp">
                                        <?php echo wp_kses(jsst_get_avatar($ticket->uid), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <div class="js-ticket-first-left">
                                        <div class="js-ticket-ticket-subject">
                                            <?php
                                            if (!empty($show_on_listing_array['fullname'])) { ?>
                                                <div class="js-ticket-data-row name">
                                                    <a class="js-ticket-data-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=> $ticket->id))); ?>">
                                                        <?php echo esc_html($ticket->subject); ?>
                                                    </a>
                                                </div>
                                                <?php
                                            } ?>
                                            <div class="js-ticket-data-row">
                                                <?php 
                                                if(!empty($ticket->last_reply_created)):
                                                    if($ticket->last_reply_uid == $uid):
                                                        echo esc_html( __( 'You replied', 'js-support-ticket' ) ).' ';
                                                    else:
                                                        echo esc_html( __( 'Reply from', 'js-support-ticket' ) ).' ';
                                                        echo esc_html($ticket->last_reply_name).' ';
                                                    endif;
                                                    echo esc_html( human_time_diff( strtotime( $ticket->last_reply_created ), current_time( 'timestamp' ) ) ); ?> <?php echo esc_html( __( 'ago', 'js-support-ticket' ) );
                                                else:
                                                    echo esc_html( __( 'No replies yet', 'js-support-ticket' ) );
                                                endif;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="js-ticket-ticket-meta">
                                            <span class="js-ticket-priority-tag js-ticket-priority-high" style="background:<?php echo esc_attr($ticket->prioritycolour); ?>;">
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?>
                                            </span>
                                            <span class="js-ticket-status" style="color:<?php echo esc_attr($ticket->statuscolour); ?>;background:<?php echo esc_attr($ticket->statusbgcolour); ?>;">
                                                <?php echo esc_html($ticket->statustitle); ?>
                                            </span>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=> $ticket->id))); ?>" class="js-ticket-assign-tag"><?php echo esc_html( __( 'View Details', 'js-support-ticket' ) ); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
            <!-- latest agent tickets -->
            <?php
            if(isset($data['agent-tickets']) && jssupportticket::$_config['cplink_latesttickets_staff'] == 1){
                ?>
                <div class="js-ticket-tickets-container">
                    <div class="js-ticket-container-header">
                        <span class="js-ticket-container-header-wrp"><?php echo esc_html( __( 'Recent Tickets', 'js-support-ticket' ) ); ?></span>
                        <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','jstlay'=>'staffmyticket'))); ?>"><?php echo esc_html( __( 'View All', 'js-support-ticket' ) ); ?></a>
                    </div>
                    <div class="js-ticket-container-content">
                        <div class="js-ticket-latest-tickets-wrp">
                            <?php
                            $fields_array = array(); // Array for form fields
                            $show_on_listing_arrays = array(); // Array for visible form fields
                            foreach($data['agent-tickets'] as $ticket){
                                // Check if the form fields are already array
                                if (!isset($fields_array[$ticket->multiformid])) {
                                    $fields_array[$ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, $ticket->multiformid);
                                }
                                if (!isset($show_on_listing_arrays[$ticket->multiformid])) {
                                    $show_on_listing_arrays[$ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldsForListing(1, $ticket->multiformid);
                                }
                                // Now use the cached field array
                                $field_array = $fields_array[$ticket->multiformid];
                                $show_on_listing_array = $show_on_listing_arrays[$ticket->multiformid];
                                $ticketviamail = '';
                                if ($ticket->ticketviaemail == 1)
                                    $ticketviamail = esc_html(__('Created via Email', 'js-support-ticket'));
                                ?>
                                <div class="js-ticket-row">
                                    <div class="js-ticket-user-img-wrp">
                                        <?php /* if (in_array('agent',jssupportticket::$_active_addons) && $ticket->staffphoto) { ?>
                                            <img class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $ticket->staffid ,'jsstpageid'=>get_the_ID())));?> ">
                                        <?php } else { */
                                            echo wp_kses(jsst_get_avatar($ticket->uid), JSST_ALLOWED_TAGS);
                                        // } ?>
                                    </div>
                                    <div class="js-ticket-first-left">
                                        <div class="js-ticket-ticket-subject">
                                            <div class="js-ticket-data-row name">
                                                <a class="js-ticket-data-link" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=> $ticket->id))); ?>">
                                                    <?php echo esc_html($ticket->subject); ?>
                                                </a>
                                            </div>
                                            <div class="js-ticket-data-row">
                                                <?php 
                                                if(!empty($ticket->last_reply_created)):
                                                    if($ticket->last_reply_uid == $uid):
                                                        echo esc_html( __( 'You replied', 'js-support-ticket' ) ).' ';
                                                    else:
                                                        echo esc_html( __( 'Reply from', 'js-support-ticket' ) ).' ';
                                                        echo esc_html($ticket->last_reply_name).' ';
                                                    endif;
                                                    echo esc_html( human_time_diff( strtotime( $ticket->last_reply_created ), current_time( 'timestamp' ) ) ); ?> <?php echo esc_html( __( 'ago', 'js-support-ticket' ) );
                                                else:
                                                    echo esc_html( __( 'No replies yet', 'js-support-ticket' ) );
                                                endif;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="js-ticket-ticket-meta">
                                            <?php
                                            if (!empty($ticket->priority)) { ?>
                                                <span class="js-ticket-priority-tag js-ticket-priority-high" style="background:<?php echo esc_attr($ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?></span>
                                                <?php
                                            } ?>
                                            <span class="js-ticket-status" style="color:<?php echo esc_attr($ticket->statuscolour); ?>;background:<?php echo esc_attr($ticket->statusbgcolour); ?>;">
                                                <?php echo esc_html($ticket->statustitle); ?>
                                            </span>
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=> $ticket->id))); ?>" class="js-ticket-assign-tag"><?php echo esc_html( __( 'View Details', 'js-support-ticket' ) ); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
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
                <div class="js-ticket-count" style="display:none;">
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
                                    // show counts accoridng to configuration
                                    if(jssupportticket::$_config['count_on_myticket'] == 1) {
                                        echo ' ( '.esc_html($data['count']['openticket']).' )';
                                    }
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
                                    // show counts accoridng to configuration
                                    if(jssupportticket::$_config['count_on_myticket'] == 1) {
                                        echo ' ( '.esc_html($data['count']['closedticket']).' )';
                                    }
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
                                    // show counts accoridng to configuration
                                    if(jssupportticket::$_config['count_on_myticket'] == 1) {
                                        echo ' ( '.esc_html($data['count']['answeredticket']).' )';
                                    }
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
                                    // show counts accoridng to configuration
                                    if(jssupportticket::$_config['count_on_myticket'] == 1) {
                                        echo ' ( '.esc_html($data['count']['overdue']).' )';
                                    }
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
                                    // show counts accoridng to configuration
                                    if(jssupportticket::$_config['count_on_myticket'] == 1) {
                                        echo ' ( '.esc_html($data['count']['allticket']).' )';
                                    }
                                ?>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                </div>
                <?php
            }
            ?>
            
            <!-- agent data chart -->
            <?php
            if(isset(jssupportticket::$_data['stack_chart_horizontal']) && jssupportticket::$_config['cplink_ticketstats_'. $linkname] == 1){
                ?>
                <div class="js-ticket-stats-container">
                    <div class="js-ticket-container-header">
                        <span><?php echo esc_html( __( 'Ticket Statistics', 'js-support-ticket' ) ); ?></span>
                    </div>
                    <div class="js-ticket-container-content">
                        <div id="js-ticket-pm-grapharea">
                            <div id="stack_chart_horizontal" style="width:100%;"></div>
                        </div>
                    </div>
                </div>
                <?php
            }

            ?>
            <?php
            if((isset($data['latest-announcements']) && jssupportticket::$_config['cplink_latestannouncements_'. $linkname] == 1) || 
                (isset($data['latest-articles']) && jssupportticket::$_config['cplink_latestkb_'. $linkname] == 1) || 
                (isset($data['latest-faqs'])  && jssupportticket::$_config['cplink_latestfaqs_'. $linkname] == 1) || 
                (isset($data['latest-downloads']) && jssupportticket::$_config['cplink_latestdownloads_'. $linkname] == 1)
                ){
                ?>
                <div class="js-ticket-resources-container">
                    <nav class="js-ticket-tabs-nav">
                        <?php
                        if(isset($data['latest-announcements']) && jssupportticket::$_config['cplink_latestannouncements_'. $linkname] == 1){
                            ?>
                            <button class="js-ticket-tab-btn active" data-tab="announcements"><?php echo esc_html( __( 'Announcements', 'js-support-ticket' ) ); ?></button>
                            <?php
                        }
                        if(isset($data['latest-articles']) && jssupportticket::$_config['cplink_latestkb_'. $linkname] == 1){
                            ?>
                            <button class="js-ticket-tab-btn" data-tab="kb"><?php echo esc_html( __( 'Knowledge Base', 'js-support-ticket' ) ); ?></button>
                            <?php
                        }
                        if(isset($data['latest-faqs'])  && jssupportticket::$_config['cplink_latestfaqs_'. $linkname] == 1){
                            ?>
                            <button class="js-ticket-tab-btn" data-tab="faq"><?php echo esc_html( __( 'FAQ\'s', 'js-support-ticket' ) ); ?></button>
                            <?php
                        }
                        if(isset($data['latest-downloads']) && jssupportticket::$_config['cplink_latestdownloads_'. $linkname] == 1){
                            ?>
                            <button class="js-ticket-tab-btn" data-tab="downloads"><?php echo esc_html( __( 'Downloads', 'js-support-ticket' ) ); ?></button>
                            <?php
                        }
                        ?>
                    </nav>
                    <div class="js-ticket-container-content">
                        <?php
                        if(isset($data['latest-announcements']) && jssupportticket::$_config['cplink_latestannouncements_'. $linkname] == 1){
                            ?>
                            <div class="js-ticket-tab-pane active" id="announcements">
                                <div class="js-ticket-resource-list">
                                    <?php
                                    $imgindex = 1;
                                    foreach($data['latest-announcements'] as $announcement){
                                        ?>
                                        <div class="js-ticket-resource-item">
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'announcementdetails', 'jssupportticketid'=>$announcement->id))); ?>" class="js-ticket-resource-link">
                                                <div class="js-ticket-resource-icon" style="background-color: #dbeafe; color: #3b82f6;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                                </svg>
                                                </div>
                                                <span class="js-ticket-resource-title"><?php echo esc_html($announcement->title); ?></span>
                                            </a>
                                            <?php
                                            if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) { ?>
                                                <div class="js-ticket-resource-actions">
                                                    <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'jstlay'=>'addannouncement', 'jssupportticketid'=>$announcement->id))); ?>" class="js-ticket-action-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </a>
                                                    <a onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'announcement', 'task'=>'deleteannouncement', 'action'=>'jstask', 'announcementid'=>$announcement->id, 'jsstpageid'=>get_the_ID())),'delete-announcement-'.$announcement->id)); ?>" class="js-ticket-action-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    </a>
                                                </div>
                                                <?php
                                            } ?>
                                        </div>
                                        <?php
                                        $imgindex = $imgindex==6 ? 1 : $imgindex+1;
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        if(isset($data['latest-articles']) && jssupportticket::$_config['cplink_latestkb_'. $linkname] == 1){
                            ?>
                            <div class="js-ticket-tab-pane" id="kb">
                                <div class="js-ticket-resource-list">
                                    <?php
                                    $imgindex = 1;
                                    foreach($data['latest-articles'] as $article){
                                        ?>
                                        <div class="js-ticket-resource-item">
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'articledetails', 'jssupportticketid'=>$article->articleid))); ?>" class="js-ticket-resource-link">
                                                <div class="js-ticket-resource-icon" style="background-color: #fef3c7; color: #f59e0b;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg></div>
                                                <span class="js-ticket-resource-title"><?php echo esc_html($article->subject); ?></span>
                                            </a>
                                            <?php
                                            if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) { ?>
                                                <div class="js-ticket-resource-actions">
                                                    <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'jstlay'=>'addarticle', 'jssupportticketid'=>$article->articleid))); ?>" class="js-ticket-action-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </a>
                                                    <a onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'knowledgebase', 'task'=>'deletearticle', 'action'=>'jstask', 'articleid'=>$article->articleid, 'jsstpageid'=>get_the_ID())),'delete-article-'.$article->articleid)); ?>" class="js-ticket-action-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    </a>
                                                </div>
                                                <?php
                                            } ?>
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
                        <?php
                        if(isset($data['latest-faqs'])  && jssupportticket::$_config['cplink_latestfaqs_'. $linkname] == 1){
                            ?>
                            <div class="js-ticket-tab-pane" id="faq">
                                <?php
                                $imgindex = 1;
                                foreach($data['latest-faqs'] as $faq){
                                    ?>
                                    <div class="js-ticket-resource-list">
                                        <div class="js-ticket-resource-item">
                                            <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'faqdetails', 'jssupportticketid'=>$faq->id))); ?>" class="js-ticket-resource-link">
                                                <div class="js-ticket-resource-icon" style="background-color: #e0f2fe; color: #0ea5e9;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="17" x2="12.01" y2="17"></line></svg></div>
                                                <span class="js-ticket-resource-title"><?php echo esc_html($faq->subject); ?></span>
                                            </a><?php
                                            if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) { ?>
                                                <div class="js-ticket-resource-actions">
                                                    <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'jstlay'=>'addfaq', 'jssupportticketid'=>$faq->id))); ?>" class="js-ticket-action-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </a>
                                                    <a onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'faq', 'task'=>'deletefaq', 'action'=>'jstask', 'faqid'=>$faq->id, 'jsstpageid'=>get_the_ID())),'delete-faq-'.$faq->id)); ?>" class="js-ticket-action-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    </a>
                                                </div>
                                                <?php
                                            } ?>
                                        </div>
                                    </div>
                                    <?php
                                    $imgindex = $imgindex==6 ? 1 : $imgindex+1;
                                }
                                ?>
                            </div>
                            <?php
                        }
                        if(isset($data['latest-downloads']) && jssupportticket::$_config['cplink_latestdownloads_'. $linkname] == 1){ ?>
                            <div class="js-ticket-tab-pane" id="downloads">
                                <div class="js-ticket-resource-list">
                                    <?php
                                    $imgindex = 1;
                                    foreach($data['latest-downloads'] as $download){
                                        $nonce = wp_create_nonce("get-download-by-id-".$download->downloadid);
                                        ?>
                                        <div class="js-ticket-resource-item">
                                            <a onclick="getDownloadById(<?php echo esc_js($download->downloadid) ?>, '<?php echo esc_js($nonce) ?>')" href="<?php echo esc_url('#'); ?>" class="js-ticket-resource-link">
                                                <div class="js-ticket-resource-icon" style="background-color: #e0e7ff; color: #4f46e5;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></div>
                                                <span class="js-ticket-resource-title"><?php echo esc_html($download->title); ?></span>
                                            </a>
                                            <?php
                                            if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) { ?>
                                                <div class="js-ticket-resource-actions">
                                                    <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'download', 'jstlay'=>'adddownload', 'jssupportticketid'=>$download->downloadid))); ?>" class="js-ticket-action-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </a>
                                                    <a onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'download', 'task'=>'deletedownload', 'action'=>'jstask', 'downloadid'=>$download->downloadid, 'jsstpageid'=>get_the_ID())),'delete-download-'.$download->downloadid)); ?>" class="js-ticket-action-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    </a>
                                                </div>
                                                <?php
                                            } ?>
                                        </div>
                                        <?php
                                        $imgindex = $imgindex==6 ? 1 : $imgindex+1;
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        } ?>
                    </div>
                </div>
                <?php 
            } ?>
        </div>
    </main>


    <div id="js-ticket-main-black-background" style="display:none;"></div>
    <div id="js-ticket-main-popup" style="display:none;">
        <span id="js-ticket-popup-title"></span>
        <span id="js-ticket-popup-close-button"></span>
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
</div>
<?php
$jssupportticket_js = "
    // Resources Tabs functionality
    const jsTicketResourceTabButtons = document.querySelectorAll('.js-ticket-resources-container .js-ticket-tab-btn');
    const jsTicketResourceTabPanes = document.querySelectorAll('.js-ticket-resources-container .js-ticket-tab-pane');

    jsTicketResourceTabButtons.forEach(button => {
        button.addEventListener('click', () => {
            jsTicketResourceTabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const tabId = button.getAttribute('data-tab');
            jsTicketResourceTabPanes.forEach(pane => {
                if (pane.id === tabId) {
                    pane.classList.add('active');
                } else {
                    pane.classList.remove('active');
                }
            });
        });
    });
";
wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js);
?>

