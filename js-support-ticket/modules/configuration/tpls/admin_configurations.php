<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
if(in_array('notification', jssupportticket::$_active_addons)){
    wp_enqueue_script('ticket-notify-app', JSST_PLUGIN_URL . 'includes/js/firebase-app.js');
    wp_enqueue_script('ticket-notify-message', JSST_PLUGIN_URL . 'includes/js/firebase-messaging.js');
}
JSSTmessage::getMessage();
if(in_array('notification', jssupportticket::$_active_addons)){
    if(jssupportticket::$_data[0]['apiKey_firebase'] != "" && jssupportticket::$_data[0]['databaseURL_firebase'] != "" && jssupportticket::$_data[0]['authDomain_firebase'] != "" && jssupportticket::$_data[0]['projectId_firebase'] != "" && jssupportticket::$_data[0]['storageBucket_firebase'] != "" && jssupportticket::$_data[0]['messagingSenderId_firebase'] != "" && jssupportticket::$_data[0]['server_key_firebase'] != ""){
        do_action('ticket-notify-generate-token');
    }
}
$jssupportticket_js ='
    jQuery(document).ready(function () {
        jQuery(".js-support-ticket-configurations-toggle").click(function(){
      	    jQuery(".js-support-ticket-configurations .js-support-ticket-configurations-left").toggle();
        });';
        if(isset(jssupportticket::$_data["jsstconfigid"])){
            $jsstconfigid = jssupportticket::$_data["jsstconfigid"];
        } else {
            $jsstconfigid =  "";
        }
        $jssupportticket_js .='

        var jsstconfigid = "'. $jsstconfigid .'";
        if (jsstconfigid == "general") {
            jQuery("#general").css("display","inline-block");
            jQuery("#cn_gen").addClass("active");
        }else if (jsstconfigid == "ticketsettig") {
            jQuery("#ticketsettig").css("display","inline-block");
            jQuery("#cn_ts").addClass("active");
        }else if (jsstconfigid == "defaultemail") {
            jQuery("#defaultemail").css("display","inline-block");
            jQuery("#cn_dm").addClass("active");
        }else if (jsstconfigid == "mailsetting") {
            jQuery("#mailsetting").css("display","inline-block");
            jQuery("#cn_ms").addClass("active");
        }else if (jsstconfigid == "staffmenusetting") {
            jQuery("#staffmenusetting").css("display","inline-block");
            jQuery("#cn_sms").addClass("active");
        }else if (jsstconfigid == "usermenusetting") {
            jQuery("#usermenusetting").css("display","inline-block");
            jQuery("#cn_ums").addClass("active");
        }else if (jsstconfigid == "feedback") {
            jQuery("#feedback").css("display","inline-block");
            jQuery("#cn_fb").addClass("active");
        }else if (jsstconfigid == "sociallogin") {
            jQuery("#sociallogin").css("display","inline-block");
            jQuery("#cn_sl").addClass("active");
        }else if (jsstconfigid == "ticketviaemail") {
            jQuery("#ticketviaemail").css("display","inline-block");
            jQuery("#cn_tve").addClass("active");
        }else if (jsstconfigid == "pushnotification") {
            jQuery("#pushnotification").css("display","inline-block");
            jQuery("#cn_pn").addClass("active");
        }else if (jsstconfigid == "privatecredentials") {
            jQuery("#privatecredentials").css("display","inline-block");
            jQuery("#cn_pc").addClass("active");
        }else if (jsstconfigid == "envatovalidation") {
            jQuery("#envatovalidation").css("display","inline-block");
            jQuery("#cn_ev").addClass("active");
        }else if (jsstconfigid == "mailchimp") {
            jQuery("#mailchimp").css("display","inline-block");
            jQuery("#cn_mc").addClass("active");
        }else if (jsstconfigid == "easydigitaldownloads") {
            jQuery("#easydigitaldownloads").css("display","inline-block");
            jQuery("#cn_edd").addClass("active");
        }else if (jsstconfigid == "captcha") {
            jQuery("#captcha").css("display","inline-block");
            jQuery("#cn_cap").addClass("active");
        }else{
            jQuery("#general").css("display","inline-block");
            jQuery("#cn_gen").addClass("active");
        }

        // new code

        jQuery("ul.jsst_tabs li").click(function(){
            var tab_id = jQuery(this).attr("data-jsst-tab");

            jQuery("ul.jsst_tabs li").removeClass("jsst_current_tab");
            jQuery(".jsst_tab_content").removeClass("jsst_current_tab");

            jQuery(this).addClass("jsst_current_tab");
            jQuery("#"+tab_id).addClass("jsst_current_tab");
        });

        jQuery("select#ticket_overdue_type").change(function(){
            var isselect = jQuery("select#ticket_overdue_type").val();
            if(isselect == 1){
                jQuery("span.ticket_overdue_type_text").html("'. esc_html(__("Days", "js-support-ticket")).'");
            }else{
                jQuery("span.ticket_overdue_type_text").html("'. esc_html(__("Hours", "js-support-ticket")).'");
            }
        });
    });
    function showhidehostname(value){
        if(value == 4){
            jQuery("div#tve_hostname").show();
        }else{
            jQuery("div#tve_hostname").hide();
        }
    }
    function deleteSupportCustomImage(){
       jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "configuration", task: "deleteSupportCustomImage", "_wpnonce":"'.esc_attr(wp_create_nonce("delete-support-customimage")).'"}, function (data) {
        if(data){
          jQuery(".js-ticket-configuration-img").addClass("visible");
        }
      });
    }

    jQuery(document).ready(function () {
        jQuery("select#set_login_link").change(function(){
            var value = jQuery(this).val();
            if (value == 2) {
               jQuery(".loginlink_field").attr("style","display: block");
            } else {
                jQuery(".loginlink_field").attr("style","display: none");
            }
        })

        var value = jQuery("select#set_login_link").val();
        if (value == 2) {
           jQuery(".loginlink_field").attr("style","display: block");
        } else {
            jQuery(".loginlink_field").attr("style","display: none");
        }

        jQuery("select#set_register_link").change(function(){
            var value = jQuery(this).val();
            if (value == 2) {
               jQuery(".registerlink_field").attr("style","display: block");
            } else {
                jQuery(".registerlink_field").attr("style","display: none");
            }
        });

        var value = jQuery("select#set_register_link").val();
        if (value == 2){
           jQuery(".registerlink_field").attr("style","display: block");
        } else {
            jQuery(".registerlink_field").attr("style","display: none");
        }

    });

    // for hide and show baseb on custom fields
    jQuery(document).ready(function () {
        jQuery("select#ticketid_sequence").change(function(){
           var value = jQuery(this).val();
            if (value == 2){
                jQuery(".Ticketid-sequence-custom").slideDown("slow");
            }else{
                jQuery(".Ticketid-sequence-custom").slideUp("slow");
            }
            setpadZerosText();
        });
        var value = jQuery("select#ticketid_sequence").val();
        if (value == 2){
            jQuery(".Ticketid-sequence-custom").css("display","inline-block");
        } else {
            jQuery(".Ticketid-sequence-custom").css("display","none");
        }

        // for prefix and suffix
        jQuery("#padZeros-prefix").text(jQuery("#prefix_ticketid").val());
        jQuery("#padZeros-suffix").text(jQuery("#suffix_ticketid").val());
        jQuery("#prefix_ticketid").on("input", function(){
            jQuery("#padZeros-prefix").text(jQuery(this).val());
        });
        jQuery("#suffix_ticketid").on("input", function(){
            jQuery("#padZeros-suffix").text(jQuery(this).val());
        });

        // for pad zeroes
        jQuery("select#padding_zeros_ticketid").change(function(){
           setpadZerosText();
        });
        setpadZerosText();
        
    });

    function setpadZerosText() {
        var value = jQuery("select#ticketid_sequence").val();
        if (value == 1){
            jQuery("#padZeros").text("xxxxxxx");
        } else {
            var value = jQuery("select#padding_zeros_ticketid").val();
            if (value == 1){
                jQuery("#padZeros").text("1");
            } else if (value == 2) {
                jQuery("#padZeros").text("01");
            } else if (value == 3) {
                jQuery("#padZeros").text("001");
            } else if (value == 4) {
                jQuery("#padZeros").text("0001");
            } else if (value == 5) {
                jQuery("#padZeros").text("00001");
            } else if (value == 6) {
                jQuery("#padZeros").text("000001");
            }
        }
    }
';
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);

$captchaselection = array(
    (object) array('id' => '1', 'text' => esc_html(__('Google Recaptcha', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Own Captcha', 'js-support-ticket')))
);
$owncaptchaoparend = array(
    (object) array('id' => '2', 'text' => '2'),
    (object) array('id' => '3', 'text' => '3')
);
$owncaptchatype = array(
    (object) array('id' => '0', 'text' => esc_html(__('Any', 'js-support-ticket'))),
    (object) array('id' => '1', 'text' => esc_html(__('Addition', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Subtraction', 'js-support-ticket')))
);
$recaptcha_version = array(
    (object) array('id' => '1', 'text' => esc_html(__('Recaptcha Version 2', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Recaptcha Version 3', 'js-support-ticket')))
);
$yesno = array(
    (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket')))
);
$showhide = array(
    (object) array('id' => '1', 'text' => esc_html(__('Show', 'js-support-ticket'))),
    (object) array('id' => '0', 'text' => esc_html(__('Hide', 'js-support-ticket')))
);
$defaultcustom = array(
    (object) array('id' => '1', 'text' => esc_html(__('JS Help Desk Login Page', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('WordPress Default Login Page', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Custom', 'js-support-ticket')))
);
$defaultregisterpage = array(
    (object) array('id' => '1', 'text' => esc_html(__('JS Help Desk Register Page', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('WordPress Default Login Page', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Custom', 'js-support-ticket')))
);
$screentagposition = array(
    (object) array('id' => '1', 'text' => esc_html(__('Top left', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Top right', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('Middle left', 'js-support-ticket'))),
    (object) array('id' => '4', 'text' => esc_html(__('Middle right', 'js-support-ticket'))),
    (object) array('id' => '5', 'text' => esc_html(__('Bottom left', 'js-support-ticket'))),
    (object) array('id' => '6', 'text' => esc_html(__('Bottom right', 'js-support-ticket')))
);
$enableddisabled = array(
    (object) array('id' => '1', 'text' => esc_html(__('Enabled', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Disabled', 'js-support-ticket')))
);
$mailreadtype = array(
    (object) array('id' => '1', 'text' => esc_html(__('Only New Tickets', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Only Replies', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('Both', 'js-support-ticket')))
);

$sequence = array(
    (object) array('id' => '1', 'text' => esc_html(__('Random', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Sequence', 'js-support-ticket')))
);

$padZeros = array(
    (object) array('id' => '1', 'text' => esc_html(__('1', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('2', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('3', 'js-support-ticket'))),
    (object) array('id' => '4', 'text' => esc_html(__('4', 'js-support-ticket'))),
    (object) array('id' => '5', 'text' => esc_html(__('5', 'js-support-ticket'))),
    (object) array('id' => '6', 'text' => esc_html(__('6', 'js-support-ticket')))
);

$hosttype = array(
    (object) array('id' => '1', 'text' => esc_html(__('Gmail', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Yahoo', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('Aol', 'js-support-ticket'))),
    (object) array('id' => '4', 'text' => esc_html(__('Other', 'js-support-ticket')))
);

$ticketordering = array(
    (object) array('id' => '1', 'text' => esc_html(__('Default', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Created', 'js-support-ticket')))
);
$ticketsorting = array(
    (object) array('id' => '1', 'text' => esc_html(__('Ascending', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Descending', 'js-support-ticket')))
);
// wp roles combo for new user
global $wp_roles;
$roles = $wp_roles->get_names();
$userroles = array();
foreach ($roles as $key => $value) {
    $userroles[] = (object) array('id' => $key, 'text' => $value);
}
$plugin_array = get_option('active_plugins');
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Configurations','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
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
            <h1 class="jsstadmin-head-text jsstadmin-head-configurations-text"><?php echo esc_html(__("Configurations", 'js-support-ticket')) ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bs-n bg-n">
            <form method="post" class="js-support-ticket-configurations" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=configuration&task=saveconfiguration"),"save-configuration")); ?>" enctype="multipart/form-data">
              <div class="js-support-ticket-configurations-toggle">
                <img class="jsst_menu-icon" alt="<?php echo esc_html(__('menu' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/menu.png'; ?>"/>
                <span class="jsst_text"><?php echo esc_html(__('Select Configuration' , 'js-support-ticket')); ?> </span>
              </div>
            <div class="js-support-ticket-configurations-left">
              <ul class="jsstadmin-sidebar-menu tree accordion" data-widget="tree">
                <li class="treeview" id="cn_gen">
                    <a href="?page=configuration&jsstconfigid=general" title="<?php echo esc_html(__('General' , 'js-support-ticket')); ?>">
                        <img class="jsst_menu-icon" alt="<?php echo esc_html(__('General' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/config.png'; ?>"/>
                        <span class="jsst_text"><?php echo esc_html(__('General' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=general"><?php echo esc_html(__('General Settings', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#TicketDefault"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#login"><?php echo esc_html(__('Login', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#register"><?php echo esc_html(__('Register', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#SupportIcons"><?php echo esc_html(__('Support Icons', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#Offline"><?php echo esc_html(__('Offline', 'js-support-ticket')); ?></a></li>
                      <?php if(in_array('paidsupport', jssupportticket::$_active_addons) && in_array('woocommerce/woocommerce.php', $plugin_array)){ ?>
                        <li><a href="?page=configuration&jsstconfigid=general#PaidSupport"><?php echo esc_html(__('Paid Support', 'js-support-ticket')); ?></a></li>
                      <?php } ?>
                    </ul>
                </li>
                <li class="treeview" id="cn_ts">
                    <a href="?page=configuration&jsstconfigid=ticketsettig" title="<?php echo esc_html(__('Ticket Settings' , 'js-support-ticket')); ?>">
                        <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Ticket Settings' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/tickets.png'; ?>"/>
                        <span class="jsst_text"><?php echo esc_html(__('Ticket Settings' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=ticketsettig"><?php echo esc_html(__('Ticket Settings', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=ticketsettig#TicketListing"><?php echo esc_html(__('Ticket Listing', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=ticketsettig#TS_visitorTs"><?php echo esc_html(__('Visitor Ticket Setting', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <li class="treeview" id="cn_dm">
                    <a href="?page=configuration&jsstconfigid=defaultemail" title="<?php echo esc_html(__('System Emails' , 'js-support-ticket')); ?>">
                        <img class="jsst_menu-icon" alt="<?php echo esc_html(__('System Emails' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/system-email.png'; ?>"/>
                        <span class="jsst_text"><?php echo esc_html(__('System Emails' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=defaultemail"><?php echo esc_html(__('System Emails', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <li class="treeview" id="cn_cap">
                    <a href="?page=configuration&jsstconfigid=captcha" title="<?php echo esc_html(__('Captcha' , 'js-support-ticket')); ?>">
                        <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Captcha' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/captcha.png'; ?>"/>
                        <span class="jsst_text"><?php echo esc_html(__('Captcha' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=captcha"><?php echo esc_html(__('Captcha', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <li class="treeview" id="cn_ms">
                    <a href="?page=configuration&jsstconfigid=mailsetting" title="<?php echo esc_html(__('Email Settings' , 'js-support-ticket')); ?>">
                        <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Email Settings' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/email-settings.png'; ?>"/>
                        <span class="jsst_text"><?php echo esc_html(__('Email Settings' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <?php if(isset(jssupportticket::$_data[0]['banemail_mail_to_admin'])){ ?>
                        <li><a href="?page=configuration&jsstconfigid=mailsetting#BanEmailNewTicket"><?php echo esc_html(__('Ban Email New Ticket', 'js-support-ticket')); ?></a></li>
                        <?php } ?>
                      <li><a href="?page=configuration&jsstconfigid=mailsetting#TicketOperationsEmailSetting"><?php echo esc_html(__('Ticket Operations Email Setting', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_sms">
                      <a href="?page=configuration&jsstconfigid=staffmenusetting" title="<?php echo esc_html(__('Agent Menu' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Agent Menu' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/agent-menu.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('Agent Menu' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=staffmenusetting"><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></a></li>
                        <li><a href="?page=configuration&jsstconfigid=staffmenusetting#TopMenuLinks"><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <li class="treeview" id="cn_ums">
                    <a href="?page=configuration&jsstconfigid=usermenusetting" title="<?php echo esc_html(__('User Menu' , 'js-support-ticket')); ?>">
                        <img class="jsst_menu-icon" alt="<?php echo esc_html(__('User Menu' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/user-menu.png'; ?>"/>
                        <span class="jsst_text"><?php echo esc_html(__('User Menu' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=usermenusetting"><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=usermenusetting#TopMenuLinksUser"><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_fb">
                      <a href="?page=configuration&jsstconfigid=feedback" title="<?php echo esc_html(__('Feedback' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Feedback' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/feedback.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('Feedback' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=feedback"><?php echo esc_html(__('Feedback Settings', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('emailpiping', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_tve">
                      <a href="?page=configuration&jsstconfigid=ticketviaemail" title="<?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/email-piping.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=ticketviaemail"><?php echo esc_html(__('Email Piping', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('notification', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_pn">
                      <a href="?page=configuration&jsstconfigid=pushnotification" title="<?php echo esc_html(__('Push Notifications' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Push Notifications' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/push-notifications.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('Push Notifications' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=pushnotification"><?php echo esc_html(__('Firebase Notifications', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('privatecredentials', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_pc">
                      <a href="?page=configuration&jsstconfigid=privatecredentials" title="<?php echo esc_html(__('Private Credentials' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Private Credentials' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/private-credentials.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('Private Credentials' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=privatecredentials"><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('envatovalidation', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_ev">
                      <a href="?page=configuration&jsstconfigid=envatovalidation" title="<?php echo esc_html(__('Envato Validation' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Envato Validation' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/envato-validation.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('Envato Validation' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=envatovalidation"><?php echo esc_html(__('Envato Validation', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('mailchimp', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_mc">
                      <a href="?page=configuration&jsstconfigid=mailchimp" title="<?php echo esc_html(__('MailChimp' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('MailChimp' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/mail-chimp.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('MailChimp' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=mailchimp"><?php echo esc_html(__('MailChimp', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('easydigitaldownloads', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_edd">
                      <a href="?page=configuration&jsstconfigid=easydigitaldownloads" title="<?php echo esc_html(__('Easy Digital Downloads' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Easy Digital Downloads' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/easy-digital-downloads.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('Easy Digital Downloads' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=easydigitaldownloads"><?php echo esc_html(__('Easy Digital Downloads', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('sociallogin', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_sl">
                      <a href="?page=configuration&jsstconfigid=sociallogin" title="<?php echo esc_html(__('Social Login' , 'js-support-ticket')); ?>">
                          <img class="jsst_menu-icon" alt="<?php echo esc_html(__('Social Login' , 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/config-icons/social-login.png'; ?>"/>
                          <span class="jsst_text"><?php echo esc_html(__('Social Login' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=sociallogin"><?php echo esc_html(__('Facebook', 'js-support-ticket')); ?></a></li>
                        <li><a href="?page=configuration&jsstconfigid=sociallogin#Linkedin"><?php echo esc_html(__('Linkedin', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
              </ul>
            </div>
            <div class="js-support-ticket-configurations-right">
            <div id="general" class="jsstadmin-hide-config">
              <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#GeneralSetting"><?php echo esc_html(__('General Settings', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="ticketsettig"><a href="#TicketDefault"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="ticketsettig"><a href="#login"><?php echo esc_html(__('Login', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="ticketsettig"><a href="#register"><?php echo esc_html(__('Register', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="defaultemail"><a href="#SupportIcons"><?php echo esc_html(__('Support Icons', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="mailsetting"><a href="#Offline"><?php echo esc_html(__('Offline', 'js-support-ticket')); ?></a></li>
                      <?php if(in_array('paidsupport', jssupportticket::$_active_addons) && in_array('woocommerce/woocommerce.php', $plugin_array)){ ?>
                        <li class="tab-link" data-jsst-tab="paidsupport"><a href="#PaidSupport"><?php echo esc_html(__('Paid Support', 'js-support-ticket')); ?></a></li>
                      <?php } ?>
                  </ul>
              </div>
              <div class="jsst_gen_body" id="GeneralSetting">
                  <h2><?php echo esc_html(__('General Settings', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['title'])){
                      $title = esc_html(__('Title', 'js-support-ticket'));
                      $field = JSSTformfield::text('title', jssupportticket::$_data[0]['title'], array('class' => 'inputbox'));
                      $description =  esc_html(__('Set the heading of your plugin', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['default_pageid'])){
                      $title = esc_html(__('Ticket Default Page', 'js-support-ticket'));
                      $field = JSSTformfield::select('default_pageid', JSSTincluder::getJSModel('configuration')->getPageList(), jssupportticket::$_data[0]['default_pageid'], esc_html(__('Select Page', 'js-support-ticket')), array('class' => 'inputbox', 'data-validation' => 'required'));
                      $description =  esc_html(__('Select JS Help Desk default page, on the action system, will redirect on the selected page. If not select the default page, email links, and support icon might not work.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['data_directory'])){
                      $title = esc_html(__('Data Directory', 'js-support-ticket'));
                      $field = JSSTformfield::text('data_directory', jssupportticket::$_data[0]['data_directory'], array('class' => 'inputbox'));
                      $description =  esc_html(__('Set the name for your data directory', 'js-support-ticket')) .'<br>' . esc_html(__('You need to rename the existing data directory in the file system before changing the data directory name', 'js-support-ticket')) ; ?><?php //echo esc_html(__('You need to rename the existing data directory in file system before changing the data directory name', 'js-support-ticket')));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['date_format'])){
                      $title = esc_html(__('Date Format', 'js-support-ticket'));
                      $field = JSSTformfield::select('date_format', array((object) array('id' => 'd-m-Y', 'text' => esc_html(__("DD-MM-YYYY", 'js-support-ticket'))), (object) array('id' => 'm-d-Y', 'text' => esc_html(__("MM-DD-YYYY", 'js-support-ticket'))), (object) array('id' => 'Y-m-d', 'text' => esc_html(__("YYYY-MM-DD", 'js-support-ticket')))), jssupportticket::$_data[0]['date_format']);
                      $description =  esc_html(__('Set the default date format', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['pagination_default_page_size'])){
                      $title = esc_html(__('Pagination default page size', 'js-support-ticket'));
                      $field = JSSTformfield::text('pagination_default_page_size', jssupportticket::$_data[0]['pagination_default_page_size'], array('class' => 'inputbox'));
                      $description =  esc_html(__('Set the no. of record per page', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    /*if(isset(jssupportticket::$_data[0]['show_breadcrumbs'])){
                      $title = esc_html(__('Breadcrumbs', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_breadcrumbs', $showhide, jssupportticket::$_data[0]['show_breadcrumbs']);
                      $description =  esc_html(__('Show hide breadcrumbs', 'js-support-ticket')));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }*/

                    if(isset(jssupportticket::$_data[0]['show_header'])){
                      $title = esc_html(__('Top Header', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_header', $showhide, jssupportticket::$_data[0]['show_header']);
                      $description =  esc_html(__('Show hide Top Header', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['count_on_myticket'])){
                      $title = esc_html(__('Show count on my tickets', 'js-support-ticket'));
                      $field = JSSTformfield::select('count_on_myticket', $yesno, jssupportticket::$_data[0]['count_on_myticket']);
                      $description =  esc_html(__('Show number of the open, closed, answered ticket in my ticket and dashboard', 'js-support-ticket'));
                      $video = '9ORIFf6jPPg';
                      $videotext = 'Show count on my tickets';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }

                   if(isset(jssupportticket::$_data[0]['wp_default_role'])){
                      $title = esc_html(__('Default wp role for new users', 'js-support-ticket'));
                      $field = JSSTformfield::select('wp_default_role', $userroles, jssupportticket::$_data[0]['wp_default_role']);
                      $description =  esc_html(__('Select the role you want to assign to new users', 'js-support-ticket'));
                      $video = '';
                      $videotext = 'Default wp role for new users';
                      if(in_array('useroptions', jssupportticket::$_active_addons)){
                          $video = 'T3HRojY2UN4';
                      }
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="TicketDefault">
                  <h2><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['no_of_attachement'])){
                      $title = esc_html(__('No. of attachment', 'js-support-ticket'));
                      $field = JSSTformfield::text('no_of_attachement', jssupportticket::$_data[0]['no_of_attachement'], array('class' => 'inputbox'));
                      $description =  esc_html(__('No. of attachment allowed at a time', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                   if(isset(jssupportticket::$_data[0]['file_maximum_size'])){
                      $title = esc_html(__('File maximum size', 'js-support-ticket'));
                      $field = JSSTformfield::text('file_maximum_size', jssupportticket::$_data[0]['file_maximum_size'], array('class' => 'inputbox')) ?><?php //echo esc_html(__('Kb', 'js-support-ticket'));
                      $description =  esc_html(__('Kb', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field,$description);
                    }

                    if(isset(jssupportticket::$_data[0]['file_extension'])){
                      $title = esc_html(__('File extension', 'js-support-ticket'));
                      $field = JSSTformfield::textarea('file_extension', jssupportticket::$_data[0]['file_extension'], array('class' => 'inputbox'));
                      $description =  esc_html(__('File extension allowed to attach', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="login">
                  <h2><?php echo esc_html(__('Login', 'js-support-ticket')); ?></h2>
                  <?php

                    if(isset(jssupportticket::$_data[0]['login_redirect'])){
                      $title = esc_html(__('Login redirect', 'js-support-ticket'));
                      $field = JSSTformfield::select('login_redirect', $yesno, jssupportticket::$_data[0]['login_redirect']);
                      $description =  esc_html(__('Redirect user on log in', 'js-support-ticket'));
                      $video = 'Hq1UzmUqFIA';
                      $videotext = 'Login redirect';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }

                    if(isset(jssupportticket::$_data[0]['set_login_link'])){
                        $title = esc_html(__('Set Login Link', 'js-support-ticket'));
                        $field = JSSTformfield::select('set_login_link', $defaultcustom, jssupportticket::$_data[0]['set_login_link']);
                        $description =  esc_html(__('Set Login Link Default or Custom', 'js-support-ticket'));
                        $childfield = '';
                        $video = 'Hq1UzmUqFIA';
                        $videotext = 'Login redirect';
                        if(isset(jssupportticket::$_data[0]['login_link'])){
                            $childfield = JSSTformfield::text('login_link', jssupportticket::$_data[0]['login_link'], array('class' => 'inputbox loginlink_field'));
                        }
                        JSST_printConfigFieldSingle($title, $field, $description, $video, $childfield, $videotext);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="register">
                  <h2><?php echo esc_html(__('Register', 'js-support-ticket')); ?></h2>
                  <?php

                    if(isset(jssupportticket::$_data[0]['set_register_link'])){
                      $title = esc_html(__('Set register Link', 'js-support-ticket'));
                      $field = JSSTformfield::select('set_register_link', $defaultregisterpage, jssupportticket::$_data[0]['set_register_link']);
                      $description =  esc_html(__('Set register Link Default or Custom','js-support-ticket')).'.<br />'.esc_html(__(' To enable registrations, WordPress admin > General > Settings > Membership: Anyone can register', 'js-support-ticket'));
                      $childfield = '';
                      if(isset(jssupportticket::$_data[0]['register_link'])){
                          $childfield = JSSTformfield::text('register_link', jssupportticket::$_data[0]['register_link'], array('class' => 'inputbox registerlink_field'));
                      }
                      JSST_printConfigFieldSingle($title, $field, $description, '', $childfield);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="SupportIcons">
                  <h2><?php echo esc_html(__('Support Icons', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['support_screentag'])){
                      $title = esc_html(__('Support Icon', 'js-support-ticket'));
                      $field = JSSTformfield::select('support_screentag', $showhide, jssupportticket::$_data[0]['support_screentag'], esc_html(__('Screen Tag', 'js-support-ticket')), array('class' => 'inputbox', 'data-validation' => 'required'));
                      $description =  esc_html(__('Enable / disable your support icon', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['support_custom_img'])){ ?>
                      <div class="js-ticket-configuration-row">
                        <div class="js-ticket-configuration-title"><?php echo esc_html(__('Custom Image', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-configuration-value">
                            <input type="file" name="support_custom_img" id="support_custom_img"  />
                            <div class="js-ticket-configuration-description">
                              <?php echo esc_html(__('Set custom support image', 'js-support-ticket')); ?>
                            </div>
                            <span class="js-ticket-configuration-img">
                              <?php if(jssupportticket::$_data[0]['support_custom_img'] != '0'){
                                $maindir = wp_upload_dir();
                                $basedir = $maindir['baseurl'];
                                $datadirectory = jssupportticket::$_config['data_directory'];
                                $path = $basedir . '/' . $datadirectory;
                                $path .= "/supportImg/" . jssupportticket::$_data[0]['support_custom_img'];
                                ?>
                                <img alt="<?php echo esc_html(__('image','js-support-ticket')); ?>" width="50px" height="50px" src="<?php echo esc_url($path); ?>">
                                  <?php echo esc_html(jssupportticket::$_data[0]['support_custom_img']) ?>
                                  <a title="<?php echo esc_html(__('Delete','js-support-ticket')); ?>" onclick="deleteSupportCustomImage()">( <?php echo esc_html(__('Delete','js-support-ticket')); ?> )</a>
                              <?php } ?>
                            </span>
                        </div>
                      </div>
                    <?php }

                    if(isset(jssupportticket::$_data[0]['support_custom_txt'])){
                        $title = esc_html(__('custom text', 'js-support-ticket'));
                        $field = JSSTformfield::text('support_custom_txt', jssupportticket::$_data[0]['support_custom_txt'], array('class' => 'inputbox'));
                        $description =  esc_html(__('Set custom support text', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['screentag_position'])){
                      $title = esc_html(__('Support Icon Position', 'js-support-ticket'));
                      $field = JSSTformfield::select('screentag_position', $screentagposition, jssupportticket::$_data[0]['screentag_position'], esc_html(__('Screen Tag Position', 'js-support-ticket')), array('class' => 'inputbox', 'data-validation' => 'required'));
                      $description =  esc_html(__('Select position for your support icon', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="Offline">
                  <h2><?php echo esc_html(__('Offline', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['offline'])){
                     $title = esc_html(__('Offline', 'js-support-ticket'));
                     $field = JSSTformfield::select('offline', array((object) array('id' => '1', 'text' => esc_html(__('Offline', 'js-support-ticket'))), (object) array('id' => '2', 'text' => esc_html(__('Online', 'js-support-ticket')))), jssupportticket::$_data[0]['offline']);
                     $description =  esc_html(__('Set your plugin offline for front end', 'js-support-ticket'));
                     JSST_printConfigFieldSingle($title, $field, $description);
                    }

                  if(isset(jssupportticket::$_data[0]['offline_message'])){?>
                  <div class="js-ticket-configuration-row">
                    <div class="js-ticket-configuration-title"><?php echo esc_html(__('Offline Message', 'js-support-ticket')); ?></div>
                    <div class="js-ticket-configuration-value full-width">
                        <?php wp_editor(jssupportticket::$_data[0]['offline_message'], 'offline_message', array('media_buttons' => false)); ?>
                        <div class="js-ticket-configuration-description">
                          <?php echo esc_html(__('Set the offline message for your user', 'js-support-ticket')); ?>
                        </div>
                    </div>
                  </div>
                  <?php } ?>
              </div>
                <?php if(in_array('paidsupport', jssupportticket::$_active_addons) && in_array('woocommerce/woocommerce.php', $plugin_array)){ ?>
                    <div class="jsst_gen_body" id="PaidSupport">
                        <h2><?php echo esc_html(__('Paid Support', 'js-support-ticket')); ?></h2>
                        <?php
                        if(isset(jssupportticket::$_data[0]['woocommerce_default_categoryid'])){
                            $title = esc_html(__('Woocommerce Category', 'js-support-ticket'));
                            $field = JSSTformfield::select('woocommerce_default_categoryid', JSSTincluder::getJSModel('configuration')->getWooCommerceCategoryList(), jssupportticket::$_data[0]['woocommerce_default_categoryid'], esc_html(__('Select Category', 'js-support-ticket')), array('class' => 'inputbox', 'data-validation' => 'required'));
                            $description =  esc_html(__('Select category to display only products of this category on the WooCommerce shop page.', 'js-support-ticket'));
                            JSST_printConfigFieldSingle($title, $field, $description);
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>
           
            <!-- .....TICKET SETTINGS.... -->
            <!-- .....TICKET SETTINGS.... -->
            <div id="ticketsettig" class="jsstadmin-hide-config">
               <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#TicketSetting"><?php echo esc_html(__('Ticket Settings', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="general"><a href="#TicketListing"><?php echo esc_html(__('Ticket Listing', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="defaultemail"><a href="#TS_visitorTs"><?php echo esc_html(__('Visitor Ticket Setting', 'js-support-ticket')); ?></a></li>
                  </ul>
              </div>
              <div class="jsst_gen_body" id="TicketSetting">
                  <h2><?php echo esc_html(__('Ticket Settings', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['prefix_ticketid'])){
                      $title = esc_html(__('Ticketid Prefix', 'js-support-ticket'));
                      $field = JSSTformfield::text('prefix_ticketid', jssupportticket::$_data[0]['prefix_ticketid'], array('class' => 'inputbox','maxlength' => '10'));
                      $description =  esc_html(__('Set prefix for custom ticketid', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field,$description);
                    }
                  ?>
                    <?php
                      if(isset(jssupportticket::$_data[0]['ticketid_sequence'])){ ?>
                        <div class="js-ticket-configuration-row">
                          <div class="js-ticket-configuration-title"><?php echo esc_html(__('Ticketid sequence', 'js-support-ticket')); ?></div>
                          <div class="js-ticket-configuration-value">
                            <?php echo wp_kses(JSSTformfield::select('ticketid_sequence', $sequence, jssupportticket::$_data[0]['ticketid_sequence']), JSST_ALLOWED_TAGS); ?>
                            <div class="js-ticket-configuration-description">
                              <?php echo esc_html(__('Set ticketid sequential or random .e.g ', 'js-support-ticket')); ?><span id="padZeros-prefix" class="js-ticket-font-bold"></span><span id="padZeros" class="js-ticket-font-bold"></span><span id="padZeros-suffix" class="js-ticket-font-bold"></span>
                            </div>
                          </div>
                        </div>
                      <?php
                       }
                      if(isset(jssupportticket::$_data[0]['padding_zeros_ticketid'])){ ?>
                        <div class="js-ticket-configuration-row Ticketid-sequence-custom">
                          <div class="js-ticket-configuration-title"><?php echo esc_html(__('Pad Zeros', 'js-support-ticket')); ?></div>
                          <div class="js-ticket-configuration-value">
                            <?php echo wp_kses(JSSTformfield::select('padding_zeros_ticketid', $padZeros, jssupportticket::$_data[0]['padding_zeros_ticketid']), JSST_ALLOWED_TAGS); ?>
                            <div class="js-ticket-configuration-description">
                              <?php echo esc_html(__('To pad an integer with leading zeros to a specific length', 'js-support-ticket')); ?>
                            </div>
                          </div>
                        </div>
                      <?php
                       }
                     if(isset(jssupportticket::$_data[0]['suffix_ticketid'])){
                        $title = esc_html(__('Ticketid Suffix', 'js-support-ticket'));
                        $field = JSSTformfield::text('suffix_ticketid', jssupportticket::$_data[0]['suffix_ticketid'], array('class' => 'inputbox','maxlength' => '7'));
                        $description =  esc_html(__('Set suffix for custom ticketid', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field,$description);
                    }

                    if(isset(jssupportticket::$_data[0]['maximum_tickets'])){
                      $title = esc_html(__('Maximum tickets', 'js-support-ticket'));
                      $field = JSSTformfield::text('maximum_tickets', jssupportticket::$_data[0]['maximum_tickets'], array('class' => 'inputbox'));
                      $description =  esc_html(__('Maximum ticket per user', 'js-support-ticket'));
                      $video = 'LoALnBJnT48';
                      $videotext = 'Maximum tickets';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }

                    if(isset(jssupportticket::$_data[0]['maximum_open_tickets'])){
                      $title = esc_html(__('Maximum open tickets', 'js-support-ticket'));
                      $field = JSSTformfield::text('maximum_open_tickets', jssupportticket::$_data[0]['maximum_open_tickets'], array('class' => 'inputbox'));
                      $description =  esc_html(__('Maximum opened tickets per user', 'js-support-ticket'));
                      $video = 'SJjHk50buw0';
                      $videotext = 'Maximum open tickets';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }

                    if(isset(jssupportticket::$_data[0]['reopen_ticket_within_days'])){
                      $title = esc_html(__('Reopen ticket within days', 'js-support-ticket'));
                      $field = JSSTformfield::text('reopen_ticket_within_days', jssupportticket::$_data[0]['reopen_ticket_within_days'], array('class' => 'inputbox'));
                      $description =  esc_html(__('The ticket can be reopened within a given number of days', 'js-support-ticket'));
                      $video = 'S7KWbUHvmmk';
                      $videotext = 'Reopen ticket within days';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }

                    if(in_array('multiform', jssupportticket::$_active_addons)){
                        if(isset(jssupportticket::$_data[0]['show_multiform_popup'])){
                          $title = esc_html(__('Multiforms Popup For New Tickets', 'js-support-ticket'));
                          $field = JSSTformfield::select('show_multiform_popup', $showhide, jssupportticket::$_data[0]['show_multiform_popup']);
                          $description =  esc_html(__('Show or hide the multiform popup when creating a new ticket. if you hide them, the system will open the default form.','js-support-ticket'));
                          JSST_printConfigFieldSingle($title, $field, $description);
                        }
                    }

                    if(isset(jssupportticket::$_data[0]['print_ticket_user'])){
                      $title = esc_html(__('User can print ticket', 'js-support-ticket'));
                      $field = JSSTformfield::select('print_ticket_user', $yesno, jssupportticket::$_data[0]['print_ticket_user']);
                      $description =  esc_html(__('Can user print ticket from ticket detail or not', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['reply_to_closed_ticket'])){
                      $title = esc_html(__('Allow Users To Reply via Email On Closed Ticket', 'js-support-ticket'));
                      $field = JSSTformfield::select('reply_to_closed_ticket', $yesno, jssupportticket::$_data[0]['reply_to_closed_ticket']);
                      $description =  esc_html(__('Select whether users can reply to closed email piping ticket or not','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['show_email_on_ticket_reply'])){
                      $title = esc_html(__('Show Admin OR Agent Email On Ticket Reply', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_email_on_ticket_reply', $yesno, jssupportticket::$_data[0]['show_email_on_ticket_reply']);
                      $description =  esc_html(__('Select whether users can see the email of administrator or agent on ticket reply','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['anonymous_name_on_ticket_reply'])){
                      $title = esc_html(__('Show Anonymous Name On Ticket Reply', 'js-support-ticket'));
                      $field = JSSTformfield::select('anonymous_name_on_ticket_reply', $yesno, jssupportticket::$_data[0]['anonymous_name_on_ticket_reply']);
                      $description =  esc_html(__('Select whether users can see the name of administrator or staff member on ticket reply','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['ticket_auto_close'])){
                      $title = esc_html(__('Ticket auto close', 'js-support-ticket'));
                      $field = JSSTformfield::text('ticket_auto_close', jssupportticket::$_data[0]['ticket_auto_close'], array('class' => 'inputbox'));
                      $description = '<span class="js-ticket-configuration-sml-txt">'. esc_html(__('Days','js-support-ticket')).'</span>' . esc_html(__('Ticket auto-close if user does not respond within given days', 'js-support-ticket'));
                      $video = 'Yi3zPvGdGG4';
                      $videotext = 'Ticket auto close';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }

                    if(isset(jssupportticket::$_data[0]['show_ticket_delete_button'])){
                      $title = esc_html(__('Show ticket delete button', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_ticket_delete_button', $yesno, jssupportticket::$_data[0]['show_ticket_delete_button']);
                      $description =  esc_html(__('Select whether users can see the ticket delete button','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['new_ticket_message'])){?>
                      <div class="js-ticket-configuration-row">
                        <div class="js-ticket-configuration-title"><?php echo esc_html(__('New ticket message', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-configuration-value full-width">
                          <?php wp_editor(jssupportticket::$_data[0]['new_ticket_message'], 'new_ticket_message'); ?>
                          <div class="js-ticket-configuration-description">
                            <?php echo esc_html(__('This message will show on the new ticket', 'js-support-ticket')); ?>
                          </div>
                        </div>
                      </div>
                    <?php
                    }
                  ?>
                </div>
                <div class="jsst_gen_body" id="TicketListing">
                    <h2><?php echo esc_html(__('Ticket Listing', 'js-support-ticket')); ?></h2>
                    <?php
                    if(isset(jssupportticket::$_data[0]['tickets_ordering'])){
                      $title = esc_html(__('Ticket listing ordering', 'js-support-ticket'));
                      $field = JSSTformfield::select('tickets_ordering', $ticketordering, jssupportticket::$_data[0]['tickets_ordering']);
                      $description =  esc_html(__('Set default ordering for ticket listing', 'js-support-ticket'));
                      $video = 'qloE9WQM4rE';
                      $videotext = 'Ticket listing ordering';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }

                    if(isset(jssupportticket::$_data[0]['tickets_sorting'])){
                      $title = esc_html(__('Ticket listing sorting', 'js-support-ticket'));
                      $field = JSSTformfield::select('tickets_sorting', $ticketsorting, jssupportticket::$_data[0]['tickets_sorting']);
                      $description =  esc_html(__('Set default sorting for ticket listing', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['show_closedby_on_admin_tickets'])){
                      $title = esc_html(__('Closed info. on admin closed tickets', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_closedby_on_admin_tickets', $showhide, jssupportticket::$_data[0]['show_closedby_on_admin_tickets']);
                      $description =  esc_html(__('By enabling this option, an admin can know who closed the ticket and when that ticket closed.','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['show_closedby_on_agent_tickets'])){
                      $title = esc_html(__('Closed info. on agent closed tickets', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_closedby_on_agent_tickets', $showhide, jssupportticket::$_data[0]['show_closedby_on_agent_tickets']);
                      $description =  esc_html(__('By enabling this option, an agent can know who closed the ticket and when that ticket closed.','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['show_closedby_on_user_tickets'])){
                      $title = esc_html(__('Closed info. on user closed tickets', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_closedby_on_user_tickets', $showhide, jssupportticket::$_data[0]['show_closedby_on_user_tickets']);
                      $description =  esc_html(__('By enabling this option, a user can know who closed the ticket and when that ticket closed.','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['show_assignto_on_admin_tickets'])){
                      $title = esc_html(__('Assigned info. on admin tickets', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_assignto_on_admin_tickets', $showhide, jssupportticket::$_data[0]['show_assignto_on_admin_tickets']);
                      $description =  esc_html(__('By enabling this option, an admin can know to whom the ticket has been assigned.','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['show_assignto_on_agent_tickets'])){
                      $title = esc_html(__('Assigned info. on agent tickets', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_assignto_on_agent_tickets', $showhide, jssupportticket::$_data[0]['show_assignto_on_agent_tickets']);
                      $description =  esc_html(__('By enabling this option, an agent can know to whom the ticket has been assigned.','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['show_assignto_on_user_tickets'])){
                      $title = esc_html(__('Assigned info. on user tickets', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_assignto_on_user_tickets', $showhide, jssupportticket::$_data[0]['show_assignto_on_user_tickets']);
                      $description =  esc_html(__('By enabling this option, a user can know to whom the ticket has been assigned.','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="TS_visitorTs">
                  <h2><?php echo esc_html(__('Visitor Ticket Setting', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['visitor_can_create_ticket'])){
                      $title = esc_html(__('Visitor can create ticket', 'js-support-ticket'));
                      $field = JSSTformfield::select('visitor_can_create_ticket', $yesno, jssupportticket::$_data[0]['visitor_can_create_ticket']);
                      $description =  esc_html(__('Can visitor create ticket or not', 'js-support-ticket'));
                      $video = 'Gcss-ybwiXk';
                      $videotext = 'Visitor can create ticket';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '',$videotext);
                    }

                    if(isset(jssupportticket::$_data[0]['visitor_message'])){?>
                      <div class="js-ticket-configuration-row">
                        <div class="js-ticket-configuration-title"><?php echo esc_html(__('Visitor ticket creation message', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-configuration-value full-width">
                          <?php wp_editor(jssupportticket::$_data[0]['visitor_message'], 'visitor_message') ?>
                          <div class="js-ticket-configuration-description">
                            <?php echo esc_html(__('This text will appear whenever a visitor creates a ticket', 'js-support-ticket')); ?>
                          </div>
                        </div>
                      </div>
                  <?php } ?>
              </div>
            </div>

            <!-- .....SYSTEM EMAILS..... -->
            <!-- .....SYSTEM EMAILS..... -->
            <div id="defaultemail" class="jsstadmin-hide-config">
               <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#SystemEmail"><?php echo esc_html(__('System Emails', 'js-support-ticket')); ?></a></li>
                  </ul>
              </div>
              <div class="jsst_gen_body" id="SystemEmail">
                  <h2><?php echo esc_html(__('System Emails', 'js-support-ticket')); ?></h2>
                  <?php

                   if(isset(jssupportticket::$_data[0]['default_alert_email'])){
                      $title = esc_html(__('Default alert email', 'js-support-ticket'));
                      $field = JSSTformfield::select('default_alert_email', jssupportticket::$_data[1], jssupportticket::$_data[0]['default_alert_email']);
                      $description = esc_html(__('If ticket department email is not selected then this email is used to send emails', 'js-support-ticket'));
                      $video = 'dNYnZw8WK0M';
                      $videotext = 'Default alert email';
                      $actionbtn = 'Add New Email';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext, $actionbtn);
                    }

                    if(isset(jssupportticket::$_data[0]['default_admin_email'])){
                      $title = esc_html(__('Default admin email', 'js-support-ticket'));
                      $field = JSSTformfield::select('default_admin_email', jssupportticket::$_data[1], jssupportticket::$_data[0]['default_admin_email']);
                      $description = esc_html(__('Admin email address to receive emails', 'js-support-ticket'));
                      $video = 'LvsrMtEqRms';
                      $videotext = 'Default admin email';
                      $actionbtn = 'Add New Email';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext, $actionbtn);
                    }

                    if(isset(jssupportticket::$_data[0]['department_email_on_ticket_create'])){
                        $title = esc_html(__('Department Email', 'js-support-ticket'));
                        $field = JSSTformfield::select('department_email_on_ticket_create', $yesno, jssupportticket::$_data[0]['department_email_on_ticket_create']);
                        $description =  esc_html(__('Send email to all departments on ticket create', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                    }
                  ?>
              </div>
            </div>
            <!-- .....EMAIL Settings..... -->
            <div id="mailsetting" class="jsstadmin-hide-config">
              <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <?php if(isset(jssupportticket::$_data[0]['banemail_mail_to_admin'])){ ?>
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#BanEmailNewTicket"><?php echo esc_html(__('Ban Email New Ticket', 'js-support-ticket')); ?></a></li>
                    <?php } ?>
                      <li class="tab-link" data-jsst-tab="ticketsettig"><a href="#TicketOperationsEmailSetting"><?php echo esc_html(__('Ticket Operations Email Setting', 'js-support-ticket')); ?></a></li>
                  </ul>
              </div>
              <?php if(isset(jssupportticket::$_data[0]['banemail_mail_to_admin'])){ ?>
                <div class="jsst_gen_body" id="BanEmailNewTicket">
                    <h2><?php echo esc_html(__('Ban Email New Ticket', 'js-support-ticket')); ?></h2>
                    <?php
                      $title = esc_html(__('Mail to admin', 'js-support-ticket'));
                      $field = JSSTformfield::select('banemail_mail_to_admin', $enableddisabled, jssupportticket::$_data[0]['banemail_mail_to_admin']);;
                      $description = esc_html(__('Email sends to admin when banned email try to create a ticket', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    ?>
                </div>
              <?php } ?>
              <div class="jsst_gen_body" id="TicketOperationsEmailSetting">
                  <h2><?php echo esc_html(__('Ticket Operations Email Setting', 'js-support-ticket')); ?></h2>
                  <div class="js-ticket-configuration-row-mail">
                    <div class="js-ticket-conf-text-sub"><?php echo esc_html(__('Admin', 'js-support-ticket')); ?></div>
                    <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                      <div class="js-ticket-conf-text-sub"><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></div>
                    <?php }else{ ?>
                      <div class="js-ticket-conf-text-sub">------</div>
                    <?php } ?>
                    <div class="js-ticket-conf-text-sub"><?php echo esc_html(__('User', 'js-support-ticket')); ?></div>
                  </div>
                  <?php

                  if(isset(jssupportticket::$_data[0]['new_ticket_mail_to_admin'])){
                    $title = esc_html(__('New ticket', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('new_ticket_mail_to_admin', $enableddisabled, jssupportticket::$_data[0]['new_ticket_mail_to_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('new_ticket_mail_to_staff_members', $enableddisabled, jssupportticket::$_data[0]['new_ticket_mail_to_staff_members']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_reassign_admin'])){
                    $title = esc_html(__('Ticket reassign', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_reassign_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_reassign_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_reassign_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_reassign_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_reassign_user', $enableddisabled, jssupportticket::$_data[0]['ticket_reassign_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_close_admin'])){
                    $title = esc_html(__('Ticket close', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_close_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_close_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_close_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_close_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_close_user', $enableddisabled, jssupportticket::$_data[0]['ticket_close_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_delete_admin'])){
                    $title = esc_html(__('Ticket delete', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_delete_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_delete_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_delete_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_delete_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_delete_user', $enableddisabled, jssupportticket::$_data[0]['ticket_delete_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_mark_overdue_admin'])){
                    $title = esc_html(__('Ticket marked as overdue', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_mark_overdue_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_mark_overdue_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_mark_overdue_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_mark_overdue_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_mark_overdue_user', $enableddisabled, jssupportticket::$_data[0]['ticket_mark_overdue_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_ban_email_admin'])){
                    $title = esc_html(__('Ticket ban email', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_ban_email_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_ban_email_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_ban_email_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_ban_email_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_ban_email_user', $enableddisabled, jssupportticket::$_data[0]['ticket_ban_email_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_department_transfer_admin'])){
                    $title = esc_html(__('Ticket department transfer', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_department_transfer_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_department_transfer_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_department_transfer_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_department_transfer_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_department_transfer_user', $enableddisabled, jssupportticket::$_data[0]['ticket_department_transfer_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_reply_ticket_user_admin'])){
                    $title = esc_html(__('Ticket reply User', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_reply_ticket_user_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_reply_ticket_user_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_reply_ticket_user_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_reply_ticket_user_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_reply_ticket_user_user', $enableddisabled, jssupportticket::$_data[0]['ticket_reply_ticket_user_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_response_to_staff_admin'])){
                    $title = esc_html(__('Ticket Response Agent', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_response_to_staff_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_response_to_staff_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_response_to_staff_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_response_to_staff_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_response_to_staff_user', $enableddisabled, jssupportticket::$_data[0]['ticket_response_to_staff_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticker_ban_eamil_and_close_ticktet_admin'])){
                    $title = esc_html(__('Ticket ban email and close ticket', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticker_ban_eamil_and_close_ticktet_admin', $enableddisabled, jssupportticket::$_data[0]['ticker_ban_eamil_and_close_ticktet_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticker_ban_eamil_and_close_ticktet_staff', $enableddisabled, jssupportticket::$_data[0]['ticker_ban_eamil_and_close_ticktet_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticker_ban_eamil_and_close_ticktet_user', $enableddisabled, jssupportticket::$_data[0]['ticker_ban_eamil_and_close_ticktet_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['unban_email_admin'])){
                    $title = esc_html(__('Ticket unban email', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('unban_email_admin', $enableddisabled, jssupportticket::$_data[0]['unban_email_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('unban_email_staff', $enableddisabled, jssupportticket::$_data[0]['unban_email_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('unban_email_user', $enableddisabled, jssupportticket::$_data[0]['unban_email_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_lock_admin'])){
                    $title = esc_html(__('Ticket lock', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_lock_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_lock_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_lock_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_lock_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_lock_user', $enableddisabled, jssupportticket::$_data[0]['ticket_lock_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_unlock_admin'])){
                    $title = esc_html(__('Ticket unlock', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_unlock_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_unlock_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_unlock_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_unlock_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_unlock_user', $enableddisabled, jssupportticket::$_data[0]['ticket_unlock_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_priority_admin'])){
                    $title = esc_html(__('Ticket Change Priority', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_priority_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_priority_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_priority_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_priority_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_priority_user', $enableddisabled, jssupportticket::$_data[0]['ticket_priority_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_mark_progress_admin'])){
                    $title = esc_html(__('Mark Ticket In Progress', 'js-support-ticket'));
                    $field1 = JSSTformfield::select('ticket_mark_progress_admin', $enableddisabled, jssupportticket::$_data[0]['ticket_mark_progress_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $field2 = JSSTformfield::select('ticket_mark_progress_staff', $enableddisabled, jssupportticket::$_data[0]['ticket_mark_progress_staff']);
                    }else{
                      $field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $field3 = JSSTformfield::select('ticket_mark_progress_user', $enableddisabled, jssupportticket::$_data[0]['ticket_mark_progress_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_reply_closed_ticket_user'])){
                    $title = esc_html(__('Reply To A Closed Ticket By Email', 'js-support-ticket'));
                    $field1 = '<span class="js-ticket-configuration-no-rec">'.'----'.'</span>';
                    $field2 = '<span class="js-ticket-configuration-no-rec">'.'----'.'</span>';
                    $field3 =  JSSTformfield::select('ticket_reply_closed_ticket_user', $enableddisabled, jssupportticket::$_data[0]['ticket_reply_closed_ticket_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  if(isset(jssupportticket::$_data[0]['ticket_feedback_user'])){
                    $title = esc_html(__('Send Feedback Email To User', 'js-support-ticket'));
                    $field1 = '<span class="js-ticket-configuration-no-rec">'.'----'.'</span>';
                    $field2 = '<span class="js-ticket-configuration-no-rec">'.'----'.'</span>';
                    $field3 = JSSTformfield::select('ticket_feedback_user', $enableddisabled, jssupportticket::$_data[0]['ticket_feedback_user']);
                    JSST_printConfigFieldMulti($title, $field1, $field2, $field3);
                  }

                  ?>
              </div>
            </div>
            <!-- .....AGENT MENUS..... -->
            <!-- .....AGENT MENUS..... -->
            <div id="staffmenusetting" class="jsstadmin-hide-config">
              <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#DashboardLinks"><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="ticketsettig"><a href="#TopMenuLinks"><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="DashboardLinks">
                  <h2><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></h2>
                  <?php

                    if(isset(jssupportticket::$_data[0]['cplink_openticket_staff'])){
                        $title = esc_html(__('Open Ticket', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_openticket_staff', $showhide, jssupportticket::$_data[0]['cplink_openticket_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_myticket_staff'])){
                        $title =  esc_html(__('My Tickets', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_myticket_staff', $showhide, jssupportticket::$_data[0]['cplink_myticket_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$_data[0]['cplink_addrole_staff'])){
                        $title = esc_html(__('Add Role', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_addrole_staff', $showhide, jssupportticket::$_data[0]['cplink_addrole_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }*/


                    if(isset(jssupportticket::$_data[0]['cplink_roles_staff'])){
                        $title =  esc_html(__('Roles', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_roles_staff', $showhide, jssupportticket::$_data[0]['cplink_roles_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$_data[0]['cplink_addstaff_staff'])){
                        $title = esc_html(__('Add Agent', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_addstaff_staff', $showhide, jssupportticket::$_data[0]['cplink_addstaff_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }*/


                    if(isset(jssupportticket::$_data[0]['cplink_staff_staff'])){
                        $title =  esc_html(__('Agent', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_staff_staff', $showhide, jssupportticket::$_data[0]['cplink_staff_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$_data[0]['cplink_adddepartment_staff'])){
                        $title = esc_html(__('Add Department', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_adddepartment_staff', $showhide, jssupportticket::$_data[0]['cplink_adddepartment_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }*/


                    if(isset(jssupportticket::$_data[0]['cplink_department_staff'])){
                        $title =  esc_html(__('Department', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_department_staff', $showhide, jssupportticket::$_data[0]['cplink_department_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$_data[0]['cplink_addcategory_staff'])){
                        $title = esc_html(__('Add Category', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_addcategory_staff', $showhide, jssupportticket::$_data[0]['cplink_addcategory_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }*/


                    if(isset(jssupportticket::$_data[0]['cplink_category_staff'])){
                        $title =  esc_html(__('Category', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_category_staff', $showhide, jssupportticket::$_data[0]['cplink_category_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$_data[0]['cplink_addkbarticle_staff'])){
                        $title = esc_html(__('Add Knowledge Base', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_addkbarticle_staff', $showhide, jssupportticket::$_data[0]['cplink_addkbarticle_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }*/


                    if(isset(jssupportticket::$_data[0]['cplink_kbarticle_staff'])){
                        $title =  esc_html(__('Knowledge Base', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_kbarticle_staff', $showhide, jssupportticket::$_data[0]['cplink_kbarticle_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$_data[0]['cplink_adddownload_staff'])){
                        $title = esc_html(__('Add Download', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_adddownload_staff', $showhide, jssupportticket::$_data[0]['cplink_adddownload_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }*/

                    if(isset(jssupportticket::$_data[0]['cplink_download_staff'])){
                        $title =  esc_html(__('Download', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_download_staff', $showhide, jssupportticket::$_data[0]['cplink_download_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$_data[0]['cplink_addannouncement_staff'])){
                        $title = esc_html(__('Add Announcement', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_addannouncement_staff', $showhide, jssupportticket::$_data[0]['cplink_addannouncement_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }*/


                    if(isset(jssupportticket::$_data[0]['cplink_announcement_staff'])){
                        $title =  esc_html(__('Announcement', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_announcement_staff', $showhide, jssupportticket::$_data[0]['cplink_announcement_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$_data[0]['cplink_addfaq_staff'])){
                        $title = esc_html(__('Add FAQ', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_addfaq_staff', $showhide, jssupportticket::$_data[0]['cplink_addfaq_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }*/


                    if(isset(jssupportticket::$_data[0]['cplink_faq_staff'])){
                        $title =  esc_html(__("FAQ's", 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_faq_staff', $showhide, jssupportticket::$_data[0]['cplink_faq_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_mail_staff'])){
                        $title = esc_html(__('Mail', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_mail_staff', $showhide, jssupportticket::$_data[0]['cplink_mail_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_myprofile_staff'])){
                        $title =  esc_html(__('My Profile', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_myprofile_staff', $showhide, jssupportticket::$_data[0]['cplink_myprofile_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_staff_report_staff'])){
                        $title = esc_html(__('Agent Reports', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_staff_report_staff', $showhide, jssupportticket::$_data[0]['cplink_staff_report_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_department_report_staff'])){
                        $title =  esc_html(__('Department reports', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_department_report_staff', $showhide, jssupportticket::$_data[0]['cplink_department_report_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_feedback_staff'])){
                        $title = esc_html(__('Feedbacks', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_feedback_staff', $showhide, jssupportticket::$_data[0]['cplink_feedback_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_login_logout_staff'])){
                        $title =  esc_html(__('Login/Logout Button', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_login_logout_staff', $showhide, jssupportticket::$_data[0]['cplink_login_logout_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_totalcount_staff'])){
                        $title = esc_html(__('Ticket Total Count', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_totalcount_staff', $showhide, jssupportticket::$_data[0]['cplink_totalcount_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_ticketstats_staff'])){
                        $title =  esc_html(__('Ticket Statistics', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_ticketstats_staff', $showhide, jssupportticket::$_data[0]['cplink_ticketstats_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_latesttickets_staff'])){
                        $title = esc_html(__('Latest Tickets', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_latesttickets_staff', $showhide, jssupportticket::$_data[0]['cplink_latesttickets_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_helptopic_agent'])){
                        $title = esc_html(__('Help Topic', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_helptopic_agent', $showhide, jssupportticket::$_data[0]['cplink_helptopic_agent']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_cannedresponses_agent'])){
                        $title = esc_html(__('Canned Response', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_cannedresponses_agent', $showhide, jssupportticket::$_data[0]['cplink_cannedresponses_agent']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_erasedata_staff'])){
                        $title = esc_html(__('Erase Agent Data', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_erasedata_staff', $showhide, jssupportticket::$_data[0]['cplink_erasedata_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_export_ticket_staff'])){
                        $title = esc_html(__('Export Ticket', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_export_ticket_staff', $showhide, jssupportticket::$_data[0]['cplink_export_ticket_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    ?>
                </div>

                <div class="jsst_gen_body" id="TopMenuLinks">
                    <h2><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></h2>
                    <?php
                    if(isset(jssupportticket::$_data[0]['tplink_home_staff'])){
                        $title = esc_html(__('Home', 'js-support-ticket'));
                        $field = JSSTformfield::select('tplink_home_staff', $showhide, jssupportticket::$_data[0]['tplink_home_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['tplink_tickets_staff'])){
                        $title = esc_html(__('Tickets', 'js-support-ticket'));
                        $field = JSSTformfield::select('tplink_tickets_staff', $showhide, jssupportticket::$_data[0]['tplink_tickets_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['tplink_openticket_staff'])){
                        $title = esc_html(__('Open Ticket', 'js-support-ticket'));
                        $field =  JSSTformfield::select('tplink_openticket_staff', $showhide, jssupportticket::$_data[0]['tplink_openticket_staff']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['tplink_login_logout_staff'])){
                      $title = esc_html(__('Login/Logout Button', 'js-support-ticket'));
                      $field = JSSTformfield::select('tplink_login_logout_staff', $showhide, jssupportticket::$_data[0]['tplink_login_logout_staff']);
                      JSST_printConfigFieldSingle($title, $field);
                    }
                  ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....USER MENUS..... -->
            <!-- .....USER MENUS..... -->
            <div id="usermenusetting" class="jsstadmin-hide-config">
               <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#DashboardLinksUser"><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="ticketsettig"><a href="#TopMenuLinksUser"><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></a></li>
                  </ul>
              </div>
              <div class="jsst_gen_body" id="DashboardLinksUser">
                  <h2><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['cplink_openticket_user'])){
                        $title = esc_html(__('Open Ticket', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_openticket_user', $showhide, jssupportticket::$_data[0]['cplink_openticket_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_myticket_user'])){
                        $title = esc_html(__('My Tickets', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_myticket_user', $showhide, jssupportticket::$_data[0]['cplink_myticket_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_checkticketstatus_user'])){
                        $title = esc_html(__('Check Ticket Status', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_checkticketstatus_user', $showhide, jssupportticket::$_data[0]['cplink_checkticketstatus_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_downloads_user'])){
                        $title = esc_html(__('Downloads', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_downloads_user', $showhide, jssupportticket::$_data[0]['cplink_downloads_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_announcements_user'])){
                        $title = esc_html(__('Announcements', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_announcements_user', $showhide, jssupportticket::$_data[0]['cplink_announcements_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_faqs_user'])){
                        $title = esc_html(__("FAQ's", 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_faqs_user', $showhide, jssupportticket::$_data[0]['cplink_faqs_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_knowledgebase_user'])){
                        $title = esc_html(__('Knowledge Base', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_knowledgebase_user', $showhide, jssupportticket::$_data[0]['cplink_knowledgebase_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_login_logout_user'])){
                        $title = esc_html(__('Login/Logout Button', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_login_logout_user', $showhide, jssupportticket::$_data[0]['cplink_login_logout_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['cplink_register_user'])){
                        $title = esc_html(__('Registration', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_register_user', $showhide, jssupportticket::$_data[0]['cplink_register_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_erasedata_user'])){
                        $title = esc_html(__('Erase User Data', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_erasedata_user', $showhide, jssupportticket::$_data[0]['cplink_erasedata_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_latesttickets_user'])){
                        $title = esc_html(__('Latest Tickets', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_latesttickets_user', $showhide, jssupportticket::$_data[0]['cplink_latesttickets_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_totalcount_user'])){
                        $title = esc_html(__('Ticket Total Count', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_totalcount_user', $showhide, jssupportticket::$_data[0]['cplink_totalcount_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_latestdownloads_user'])){
                        $title = esc_html(__('Latest Downloads', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_latestdownloads_user', $showhide, jssupportticket::$_data[0]['cplink_latestdownloads_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_latestannouncements_user'])){
                        $title = esc_html(__('Latest Announcements', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_latestannouncements_user', $showhide, jssupportticket::$_data[0]['cplink_latestannouncements_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_latestkb_user'])){
                        $title = esc_html(__('Latest Knowledge Base', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_latestkb_user', $showhide, jssupportticket::$_data[0]['cplink_latestkb_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                    if(isset(jssupportticket::$_data[0]['cplink_latestfaqs_user'])){
                        $title = esc_html(__('Latest FAQs', 'js-support-ticket'));
                        $field = JSSTformfield::select('cplink_latestfaqs_user', $showhide, jssupportticket::$_data[0]['cplink_latestfaqs_user']);
                        JSST_printConfigFieldSingle($title, $field);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="TopMenuLinksUser">
                  <h2><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['tplink_home_user'])){
                      $title = esc_html(__('Home', 'js-support-ticket'));
                      $field = JSSTformfield::select('tplink_home_user', $showhide, jssupportticket::$_data[0]['tplink_home_user']);
                      JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['tplink_tickets_user'])){
                      $title = esc_html(__('Tickets', 'js-support-ticket'));
                      $field = JSSTformfield::select('tplink_tickets_user', $showhide, jssupportticket::$_data[0]['tplink_tickets_user']);
                      JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['tplink_openticket_user'])){
                      $title = esc_html(__('Open Ticket', 'js-support-ticket'));
                      $field = JSSTformfield::select('tplink_openticket_user', $showhide, jssupportticket::$_data[0]['tplink_openticket_user']);
                      JSST_printConfigFieldSingle($title, $field);
                    }

                    if(isset(jssupportticket::$_data[0]['tplink_login_logout_user'])){
                      $title = esc_html(__('Login/Logout Button', 'js-support-ticket'));
                      $field = JSSTformfield::select('tplink_login_logout_user', $showhide, jssupportticket::$_data[0]['tplink_login_logout_user']);
                      JSST_printConfigFieldSingle($title, $field);
                    }
                  ?>
              </div>
            </div>
            <!-- .....feedback..... -->
            <div id="feedback" class="jsstadmin-hide-config">
              <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
                 <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#FeedbackSettings"><?php echo esc_html(__('Feedback Settings', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="FeedbackSettings">
                  <h2><?php echo esc_html(__('Feedback Settings', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$_data[0]['feedback_email_delay_type'])){
                      $title = esc_html(__('Feedback Email Delay Type', 'js-support-ticket'));
                      $field = JSSTformfield::select('feedback_email_delay_type',  array((object) array('id' => '1', 'text' => esc_html(__('Days', 'js-support-ticket'))), (object) array('id' => '2', 'text' => esc_html(__('Hours', 'js-support-ticket')))), jssupportticket::$_data[0]['feedback_email_delay_type']);
                      $description = esc_html(__('Select delay type for feedback email', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['feedback_email_delay'])){
                      $title = esc_html(__('Feedback Email Delay', 'js-support-ticket'));
                      $field = JSSTformfield::text('feedback_email_delay', jssupportticket::$_data[0]['feedback_email_delay'], array('class' => 'inputbox'));
                      $description = esc_html(__('Set no. of days or hours to send feedback email after a ticket is closed', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['feedback_thanks_message'])){ ?>
                      <div class="js-ticket-configuration-row">
                        <div class="js-ticket-configuration-title"><?php echo esc_html(__('Success message after submitting feedback', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-configuration-value full-width">
                          <?php wp_editor(jssupportticket::$_data[0]['feedback_thanks_message'], 'feedback_thanks_message') ?>
                          <div class="js-ticket-configuration-description">
                            <?php echo esc_html(__('This text will appear whenever anyone submits feedback', 'js-support-ticket')); ?>
                          </div>
                        </div>
                      </div>
                    <?php } ?>

                </div>
              <?php } ?>
            </div>
            <!-- .....Social Login..... -->
            <div id="sociallogin" class="jsstadmin-hide-config">
              <?php if (in_array('sociallogin', jssupportticket::$_active_addons)) { ?>
                 <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#Facebook"><?php echo esc_html(__('Facebook', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link" data-jsst-tab="general"><a href="#Linkedin"><?php echo esc_html(__('Linkedin', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="Facebook">
                    <h2><?php echo esc_html(__('Facebook', 'js-support-ticket')); ?></h2>
                    <?php
                      $loginwithfacebook = "";
                      $apikeyfacebook = "";
                      $clientsecretfacebook = "";
                      if (isset(jssupportticket::$_data[0]['loginwithfacebook'])) {
                          $loginwithfacebook = jssupportticket::$_data[0]['loginwithfacebook'];
                      }
                      $title = esc_html(__('Login with facebook', 'js-support-ticket'));
                      $field = JSSTformfield::select('loginwithfacebook', array((object)array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))), (object)array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket')))), $loginwithfacebook);
                      $description = esc_html(__('Facebook user can login in js support ticket', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);

                      // if (isset(jssupportticket::$_data[0]['apikeyfacebook'])) {
                      //     $apikeyfacebook = jssupportticket::$_data[0]['apikeyfacebook'];
                      // }
                      $title = esc_html(__('Secret', 'js-support-ticket'));
                      $field = JSSTformfield::text('clientsecretfacebook', jssupportticket::$_data[0]['clientsecretfacebook'], array('class' => 'inputbox'));
                      $description = esc_html(__('secret key', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);

                      // if (isset(jssupportticket::$_data[0]['clientsecretfacebook'])) {
                      //     $clientsecretfacebook = jssupportticket::$_data[0]['clientsecretfacebook'];
                      // }
                      $title = esc_html(__('API Key', 'js-support-ticket'));
                      $field = JSSTformfield::text('apikeyfacebook', jssupportticket::$_data[0]['apikeyfacebook'], array('class' => 'inputbox'));
                      $description = esc_html(__('API key is required for facebook app', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    ?>
                </div>
                <div class="jsst_gen_body" id="Linkedin">
                    <h2><?php echo esc_html(__('Linkedin', 'js-support-ticket')); ?></h2>
                    <?php
                      $loginwithlinkedin = "";
                      $apikeylinkedin = "";
                      $clientsecretlinkedin = "";
                      if (isset(jssupportticket::$_data[0]['loginwithlinkedin'])) {
                          $loginwithlinkedin = jssupportticket::$_data[0]['loginwithlinkedin'];
                      }
                      $title = esc_html(__('Login with linkedin', 'js-support-ticket'));
                      $field = JSSTformfield::select('loginwithlinkedin', array((object)array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))), (object)array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket')))), $loginwithlinkedin);
                      $description = esc_html(__('Facebook user can login in js support ticket', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);

                      if (isset(jssupportticket::$_data[0]['apikeylinkedin'])) {
                          $loginwithlinkedin = jssupportticket::$_data[0]['apikeylinkedin'];
                      }
                      $title = esc_html(__('Secret', 'js-support-ticket'));
                      $field = JSSTformfield::text('clientsecretlinkedin',  jssupportticket::$_data[0]['clientsecretlinkedin'], array('class' => 'inputbox'));
                      $description = esc_html(__('secret key', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);

                      if (isset(jssupportticket::$_data[0]['clientsecretlinkedin'])) {
                          $clientsecretlinkedin = jssupportticket::$_data[0]['clientsecretlinkedin'];
                      }
                      $title = esc_html(__('API Key', 'js-support-ticket'));
                      $field = JSSTformfield::text('apikeylinkedin',jssupportticket::$_data[0]['apikeylinkedin'], array('class' => 'inputbox'));
                      $description = esc_html(__('API key is required for linkedin app', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                  ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Email Piping..... -->
            <div id="ticketviaemail" class="jsstadmin-hide-config">
              <?php if (in_array('emailpiping', jssupportticket::$_active_addons)) { ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#EmailPiping"><?php echo esc_html(__('Email Piping', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="EmailPiping">
                    <h2><?php echo esc_html(__('Email Piping', 'js-support-ticket')); ?></h2>
                    <?php
                      if (isset(jssupportticket::$_data[0]['read_utf_ticket_via_email'])) {
                        $title = esc_html(__('UTF Auto Switch', 'js-support-ticket'));
                        $field = JSSTformfield::select('read_utf_ticket_via_email',$yesno, jssupportticket::$_data[0]['read_utf_ticket_via_email']);
                        JSST_printConfigFieldSingle($title, $field);
                      }
                      if(isset(jssupportticket::$_data[0]['create_user_via_email'])){
                        $title = esc_html(__('Create User via email', 'js-support-ticket'));
                        $field = JSSTformfield::select('create_user_via_email',$yesno, jssupportticket::$_data[0]['create_user_via_email']);
                        JSST_printConfigFieldSingle($title, $field);
                      }
                    ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Firebase Notifications..... -->
            <div id="pushnotification" class="jsstadmin-hide-config">
              <?php if(in_array('notification', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#FirebaseNotifications"><?php echo esc_html(__('Firebase Notifications', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
              <div class="jsst_gen_body" id="FirebaseNotifications">
                  <h2><?php echo esc_html(__('Firebase Notifications', 'js-support-ticket')); ?></h2>
                  <?php
                    if(!file_exists(WP_PLUGIN_DIR.'/js-support-ticket-notification/js-support-ticket-notification.php')){ ?>
                      <div class="jsst_error_messages" style="color: #000; margin-bottom: 15px;">
                        <span style="color: #000;" class="jsst_msg" id="jsst_error_message"><?php echo esc_html(__("JS Help Desk Desktop Notifications plugin is not installed. Please install the plugin to enable desktop notifications","js-support-ticket"));?><a title="<?php echo esc_html(__("Click here to insert Install.","js-support-ticket")); ?>" href="<?php echo esc_url(admin_url("admin.php?page=premiumplugin")); ?>"><?php echo esc_html(__("Click here to insert Install.","js-support-ticket")); ?></a></span>
                      </div>
                    <?php
                    }elseif(!class_exists('JSSTNotification')){ ?>
                      <div class="jsst_error_messages" style="color: #000; margin-bottom: 15px;">
                          <span style="color: #000;" class="jsst_msg" id="jsst_success_message"><?php echo esc_html(__("JS Help Desk Desktop Notifications plugin is not active.","js-support-ticket"));?></span>
                      </div>
                    <?php
                    } ?>
                    <div class="jsst_error_messages" style="color: #000; margin-bottom: 15px;">
                      <span style="color: #000;" class="jsst_warning_msg" id="jsst_error_message"><?php echo esc_html(__("Find and add firebase api's keys","js-support-ticket"));?><a title="<?php echo esc_html(__("Click here to get firebae api keys.","js-support-ticket")); ?>" href="https://console.firebase.google.com" target="_blank"><?php echo esc_html(__("Click here to get firebae api keys.","js-support-ticket")); ?></a></span>
                    </div>
                    <?php
                      if(isset(jssupportticket::$_data[0]['apiKey_firebase'])){
                        $title = esc_html(__('API key for user', 'js-support-ticket'));
                        $field = JSSTformfield::text('apiKey_firebase', jssupportticket::$_data[0]['apiKey_firebase'], array('class' => 'inputbox'));
                        $description =  esc_html(__('Firebase api key for front user', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                      }

                      if(isset(jssupportticket::$_data[0]['authDomain_firebase'])){
                        $title = esc_html(__('Auth Domain', 'js-support-ticket'));
                        $field = JSSTformfield::text('authDomain_firebase', jssupportticket::$_data[0]['authDomain_firebase'], array('class' => 'inputbox'));
                        $description =  esc_html(__('Firebase Auth Domain', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                      }

                      if(isset(jssupportticket::$_data[0]['databaseURL_firebase'])){
                        $title = esc_html(__('Database Url', 'js-support-ticket'));
                        $field = JSSTformfield::text('databaseURL_firebase', jssupportticket::$_data[0]['databaseURL_firebase'], array('class' => 'inputbox'));
                        $description =  esc_html(__('Firebase Database URL', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                      }

                      if(isset(jssupportticket::$_data[0]['projectId_firebase'])){
                        $title = esc_html(__('Project Id', 'js-support-ticket'));
                        $field = JSSTformfield::text('projectId_firebase', jssupportticket::$_data[0]['projectId_firebase'], array('class' => 'inputbox'));
                        $description =  esc_html(__('Firebase Project Id', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                      }

                      if(isset(jssupportticket::$_data[0]['storageBucket_firebase'])){
                        $title = esc_html(__('Bucket Storage', 'js-support-ticket'));
                        $field = JSSTformfield::text('storageBucket_firebase', jssupportticket::$_data[0]['storageBucket_firebase'], array('class' => 'inputbox'));
                        $description =  esc_html(__('Firebase Bucket Storage', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                      }

                      if(isset(jssupportticket::$_data[0]['messagingSenderId_firebase'])){
                        $title = esc_html(__('Message Sender Id', 'js-support-ticket'));
                        $field = JSSTformfield::text('messagingSenderId_firebase', jssupportticket::$_data[0]['messagingSenderId_firebase'], array('class' => 'inputbox'));
                        $description =  esc_html(__('Firebase Message Sender Id', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                      }

                      if(isset(jssupportticket::$_data[0]['server_key_firebase'])){
                        $title = esc_html(__('Private Server Key', 'js-support-ticket'));
                        $field = JSSTformfield::text('server_key_firebase', jssupportticket::$_data[0]['server_key_firebase'], array('class' => 'inputbox'));
                        $description =  esc_html(__('Firebase Server Key', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($title, $field, $description);
                      }

                      if(isset(jssupportticket::$_data[0]['logo_for_desktop_notfication_url'])){
                        $title = esc_html(__('Logo Image for Desktop Notifications', 'js-support-ticket'));
                        $value = '<input type="file" name="logo_for_desktop_notfication" id="logo_for_desktop_notfication">';
                        $description = '';
                        if(jssupportticket::$_config['logo_for_desktop_notfication_url'] != ''){
                          $maindir = wp_upload_dir();
                          $path = $maindir['baseurl'].'/'.jssupportticket::$_config['data_directory'].'/attachmentdata';
                          $description = '<img alt="'. esc_html(__('Remove Image','js-support-ticket')).'" height="60px" width="60px;" src="'.esc_attr($path).'/'.esc_attr(jssupportticket::$_config['logo_for_desktop_notfication_url']).'"/> <label><input type="checkbox" name="del_logo_for_desktop_notfication" value="1">'. esc_html(__('Remove Logo','js-support-ticket')).'</label>';
                        }else{
                          $description = esc_html(__('No Firebase Notificaiton Logo', 'js-support-ticket'));
                        }
                        JSST_printConfigFieldSingle($title, $value, $description);
                      }
                    ?>
              </div>
              <?php } ?>
            </div>
            <!-- .....Private Credentials..... -->
            <div id="privatecredentials" class="jsstadmin-hide-config">
              <?php if(in_array('privatecredentials', jssupportticket::$_active_addons)){ ?>
                 <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#PrivateCredentials"><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="PrivateCredentials">
                    <h2><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></h2>
                    <?php
                      if(isset(jssupportticket::$_data[0]['private_credentials_secretkey'])){
                          $title = esc_html(__('Secret Key', 'js-support-ticket'));
                          $field = JSSTformfield::text('private_credentials_secretkey', jssupportticket::$_data[0]['private_credentials_secretkey'], array('class' => 'inputbox'));
                          $description =  esc_html(__('Private Credentials Encryption Key changing this value will discard all existing credentials ', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($title, $field, $description);
                      }
                      $privatecredentialsurl = WP_PLUGIN_DIR.'/js-support-ticket-privatecredentials/classes/privatecredentials.php';
                      $title = esc_html(__('Second Level Security', 'js-support-ticket'));
                      $field = '';
                      $description =  sprintf(esc_html(__('For enhanced security change encryption method in %s on line %s', 'js-support-ticket')),$privatecredentialsurl,10);
                      JSST_printConfigFieldSingle($title, $field, $description);
                    ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Envato Validation..... -->
            <div id="envatovalidation" class="jsstadmin-hide-config">
              <?php if(in_array('envatovalidation', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#EnvatoValidation"><?php echo esc_html(__('Envato Validation', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="EnvatoValidation">
                    <h2><?php echo esc_html(__('Envato Validation', 'js-support-ticket')); ?></h2>
                    <?php
                      if(isset(jssupportticket::$_data[0]['envato_api_key'])){
                          $title = esc_html(__('Api Key', 'js-support-ticket'));
                          $field = JSSTformfield::text('envato_api_key', jssupportticket::$_data[0]['envato_api_key'], array('class' => 'inputbox'));
                          $description =  esc_html(__('Enter Envato api key ', 'js-support-ticket'));
                          $description.= '<a title="'. esc_html(__("Click here to generate an api key",'js-support-ticket')).'" target="_blank" href="https://build.envato.com/create-token/">'. esc_html(__("Click here to generate an api key",'js-support-ticket')).'</a>';
                          JSST_printConfigFieldSingle($title, $field, $description);
                      }
                      if(isset(jssupportticket::$_data[0]['envato_license_required'])){
                          $title = esc_html(__('License Mandatory', 'js-support-ticket'));
                          $field = JSSTformfield::select('envato_license_required', $yesno, jssupportticket::$_data[0]['envato_license_required']);
                          $description =  esc_html(__('Prevent users from submitting a ticket without a valid license for one of your product', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($title, $field, $description);
                      }
                      if(isset(jssupportticket::$_data[0]['envato_product_ids'])){
                          $title = esc_html(__('Product ID', 'js-support-ticket'));
                          $field = JSSTformfield::text('envato_product_ids', jssupportticket::$_data[0]['envato_product_ids'], array('class' => 'inputbox'));
                          $description =  esc_html(__('A comma-separated list of Envato product ids', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($title, $field, $description);
                      }
                    ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....MailChimp..... -->
            <div id="mailchimp" class="jsstadmin-hide-config">
              <?php if(in_array('mailchimp', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#MailChimp"><?php echo esc_html(__('MailChimp', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="MailChimp">
                    <h2><?php echo esc_html(__('MailChimp', 'js-support-ticket')); ?></h2>
                    <?php
                      if(isset(jssupportticket::$_data[0]['mailchimp_api_key'])){
                          $title = esc_html(__('Api Key', 'js-support-ticket'));
                          $field = JSSTformfield::text('mailchimp_api_key', jssupportticket::$_data[0]['mailchimp_api_key'], array('class' => 'inputbox'));
                          $description =  esc_html(__('Enter MailChimp API key ', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($title, $field, $description);
                      }
                      if(isset(jssupportticket::$_data[0]['mailchimp_list_id'])){
                          $title = esc_html(__('Audience ID', 'js-support-ticket'));
                          $field = JSSTformfield::text('mailchimp_list_id', jssupportticket::$_data[0]['mailchimp_list_id'], array('class' => 'inputbox'));
                          $description =  esc_html(__('Find Audience ID in your MailChimp account', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($title, $field, $description);
                      }
                      if(isset(jssupportticket::$_data[0]['mailchimp_double_optin'])){
                          $title = esc_html(__('Enable double opt-in', 'js-support-ticket'));
                          $field = JSSTformfield::select('mailchimp_double_optin', $yesno, jssupportticket::$_data[0]['mailchimp_double_optin']);
                          $description =  esc_html(__('You must also enable double opt-in in your MailChimp account', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($title, $field, $description);
                      }
                      $title = esc_html(__('Welcome email', 'js-support-ticket'));
                      $field = esc_html(__('You can enable Final Welcome Email in your MailChimp account', 'js-support-ticket'));
                      $description = '';
                      JSST_printConfigFieldSingle($title, $field, $description);
                      ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Easy Digital Downloads..... -->
            <div id="easydigitaldownloads" class="jsstadmin-hide-config">
              <?php if(in_array('easydigitaldownloads', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#EasyDigitalDownloads"><?php echo esc_html(__('Easy Digital Downloads', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="EasyDigitalDownloads">
                    <h2><?php echo esc_html(__('Easy Digital Downloads', 'js-support-ticket')); ?></h2>
                    <?php
                      if(isset(jssupportticket::$_data[0]['verify_license_on_ticket_creation'])){
                          $title = esc_html(__('Verify License On Ticket Creation', 'js-support-ticket'));
                          $field = JSSTformfield::select('verify_license_on_ticket_creation', $yesno, jssupportticket::$_data[0]['verify_license_on_ticket_creation']);
                          JSST_printConfigFieldSingle($title, $field);
                      }
                    ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Captcha..... -->
            <div id="captcha" class="jsstadmin-hide-config">
              <div class="tabs config-tabs" id="tabs">
                <ul class="jsst_tabs">
                    <li class="tab-link jsst_current_tab" data-jsst-tab="general"><a href="#captcha"><?php echo esc_html(__('Captcha', 'js-support-ticket')); ?></a></li>
                </ul>
              </div>
              <div class="jsst_gen_body" id="captcha">
                  <h2><?php echo esc_html(__('Captcha Setting', 'js-support-ticket')); ?></h2>
                    <?php
        
                    if(isset(jssupportticket::$_data[0]['captcha_on_registration'])){
                      $title = esc_html(__('Show captcha on registration form', 'js-support-ticket'));
                      $field = JSSTformfield::select('captcha_on_registration', $yesno, jssupportticket::$_data[0]['captcha_on_registration']);
                      $description =  esc_html(__('Select whether you want to show captcha on the registration form or not', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['show_captcha_on_visitor_from_ticket'])){
                      $title = esc_html(__('Show captcha on the visitor ticket form', 'js-support-ticket'));
                      $field = JSSTformfield::select('show_captcha_on_visitor_from_ticket', $yesno, jssupportticket::$_data[0]['show_captcha_on_visitor_from_ticket']);
                      $description =  esc_html(__('Show captcha when a visitor wants to create a ticket', 'js-support-ticket'));
                      $video = '-78pMXbZy8o';
                      $videotext = 'Show captcha on the visitor ticket form';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    }

                    if(isset(jssupportticket::$_data[0]['captcha_selection'])){
                      $title = esc_html(__('Captcha selection', 'js-support-ticket'));
                      $field = JSSTformfield::select('captcha_selection', $captchaselection, jssupportticket::$_data[0]['captcha_selection']);
                      $description =  esc_html(__('Which captcha you want to add', 'js-support-ticket'));
                      $video = 'rNZc8FjYTyM';
                      $videotext = 'Captcha selection';
                      JSST_printConfigFieldSingle($title, $field, $description, $video, '', $videotext);
                    } ?>

                    <h2><?php echo esc_html(__('Google reCaptcha', 'js-support-ticket')); ?></h2>
                    
                    <?php
                    if(isset(jssupportticket::$_data[0]['recaptcha_version'])){
                      $title = esc_html(__('Google ReCaptcha version', 'js-support-ticket'));
                      $field = JSSTformfield::select('recaptcha_version', $recaptcha_version, jssupportticket::$_data[0]['recaptcha_version']);
                      $description =  esc_html(__('Select the Google ReCaptcha version','js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['recaptcha_publickey'])){
                      $title = esc_html(__('Google recaptcha site key', 'js-support-ticket'));
                      $field = JSSTformfield::text('recaptcha_publickey', jssupportticket::$_data[0]['recaptcha_publickey'], array('class' => 'inputbox'));
                      $description =  esc_html(__('Please enter the google re-captcha site key from','js-support-ticket')).' https://www.google.com/recaptcha/admin ';
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['recaptcha_privatekey'])){
                      $title = esc_html(__('Google recaptcha secret key', 'js-support-ticket'));
                      $field = JSSTformfield::text('recaptcha_privatekey', jssupportticket::$_data[0]['recaptcha_privatekey'], array('class' => 'inputbox'));
                      $description =  esc_html(__('Please enter the google re-captcha secret key from','js-support-ticket')).' https://www.google.com/recaptcha/admin ';
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }
                    ?>

                    <h2><?php echo esc_html(__('Own Captcha', 'js-support-ticket')); ?></h2>
                    
                    <?php
                    if(isset(jssupportticket::$_data[0]['owncaptcha_calculationtype'])){
                      $title = esc_html(__('Own captcha calculation type', 'js-support-ticket'));
                      $field = JSSTformfield::select('owncaptcha_calculationtype', $owncaptchatype, jssupportticket::$_data[0]['owncaptcha_calculationtype']);
                      $description =  esc_html(__('Select calculation type addition or subtraction', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                   if(isset(jssupportticket::$_data[0]['owncaptcha_totaloperand'])){
                      $title = esc_html(__('Own captcha operands', 'js-support-ticket'));
                      $field = JSSTformfield::select('owncaptcha_totaloperand', $owncaptchaoparend, jssupportticket::$_data[0]['owncaptcha_totaloperand']);
                      $description =  esc_html(__('Select the total operands to be given', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                    if(isset(jssupportticket::$_data[0]['owncaptcha_subtractionans'])){
                      $title = esc_html(__('Own captcha subtraction answer positive', 'js-support-ticket'));
                      $field = JSSTformfield::select('owncaptcha_subtractionans', $yesno, jssupportticket::$_data[0]['owncaptcha_subtractionans']);
                      $description =  esc_html(__('Is subtraction answer should be positive', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($title, $field, $description);
                    }

                  ?>
              </div>
            </div>
            </div>
            <?php echo wp_kses(JSSTformfield::hidden('action', 'configuration_saveconfiguration'), JSST_ALLOWED_TAGS); ?>
            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
            <div class="js-form-button">
              <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Configurations', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
            </div>
          </form>
        </div>
    </div>
</div>
<?php

    function JSST_printConfigFieldSingle($title, $field, $description = '', $video = '', $childfield = '', $videotext = '', $actionbtn = ''){
        $html = '';
        $html .= '
            <div class="js-ticket-configuration-row">
                <div class="js-ticket-configuration-title">';
                $html .= esc_html($title).'</div>
                <div class="js-ticket-configuration-value">'.wp_kses($field, JSST_ALLOWED_TAGS);
                  if($childfield !=''){
                      $html .= '<div class="js-ticket-configuration-value childfield">'.wp_kses($childfield, JSST_ALLOWED_TAGS).'</div>';
                  }
                  if($description !=''){
                      $html .= '<div class="js-ticket-configuration-description">'.wp_kses($description, JSST_ALLOWED_TAGS).'</div>';
                  }
                $html .= '</div>';
                if(isset($video) && $video != ''){
                    $html .= '<div class="js-ticket-configuration-video">
                      <a target="blank" href="https://www.youtube.com/watch?v='.esc_attr($video).'" class="js-tkt-det-hdg-img js-cp-video-'.esc_attr($video).'">
                        <img title="'. esc_html(__('watch video','js-support-ticket')) .'" alt="'. esc_html(__('watch video','js-support-ticket')).'" src="'. JSST_PLUGIN_URL . '/includes/images/watch-video-icon-config.png" />
                        <span></span>
                      </a>';
                      if(isset($actionbtn) && $actionbtn != ''){
                        $html .= '<a href="?page=email&jstlay=addemail" class="js-ticket-configuration-btn">
                                    <img title="'. esc_html(__('Add','js-support-ticket')) .'" alt="'. esc_html(__('Add','js-support-ticket')).'" src="'. JSST_PLUGIN_URL . '/includes/images/plus-icon.png" />
                                    '. esc_html(jssupportticket::JSST_getVarValue($actionbtn)).'
                                  </a>';
                      }
                    $html .= '</div>';
                }
                  

        $html .= '
            </div>';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    function JSST_printConfigFieldMulti($title, $field1, $field2, $field3){
        $html = '';

        $html = '
        <div class="js-ticket-configuration-row-mail">
            <div class="js-ticket-configuration-title">'.esc_html($title).'</div>
            <div class="js-ticket-configuration-value"><span class="js-ticket-config-xs-show-hide">'. esc_html(__('Agent','js-support-ticket')) .'</span>'.wp_kses($field1, JSST_ALLOWED_TAGS).'</div>
            <div class="js-ticket-configuration-value"><span class="js-ticket-config-xs-show-hide">'. esc_html(__('User','js-support-ticket')) .'</span>'.wp_kses($field2, JSST_ALLOWED_TAGS).'</div>
            <div class="js-ticket-configuration-value"><span class="js-ticket-config-xs-show-hide">'. esc_html(__('Admin','js-support-ticket')) .'</span>'.wp_kses($field3, JSST_ALLOWED_TAGS).'</div>
        </div>
        ';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

 ?>
