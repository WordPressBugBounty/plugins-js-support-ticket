<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
    $smtphost = array(
        (object) array('id' => '1', 'text' => esc_html(__('Gmail', 'js-support-ticket'))),
        (object) array('id' => '2', 'text' => esc_html(__('Yahoo', 'js-support-ticket'))),
        (object) array('id' => '3', 'text' => esc_html(__('Hotmail', 'js-support-ticket'))),
        (object) array('id' => '4', 'text' => esc_html(__('Aol', 'js-support-ticket'))),
        (object) array('id' => '5', 'text' => esc_html(__('Other', 'js-support-ticket')))
    );
    $emailtype = array(
        (object) array('id' => '0', 'text' => esc_html(__('Default', 'js-support-ticket'))),
        (object) array('id' => '1', 'text' => esc_html(__('SMTP', 'js-support-ticket')))
    );
    $truefalse = array(
        (object) array('id' => '0', 'text' => esc_html(__('False', 'js-support-ticket'))),
        (object) array('id' => '1', 'text' => esc_html(__('True', 'js-support-ticket')))
    );
    $securesmtp = array(
        (object) array('id' => '1', 'text' => esc_html(__('TLS', 'js-support-ticket'))),
        (object) array('id' => '0', 'text' => esc_html(__('SSL', 'js-support-ticket')))
    );
    $jssupportticket_js ='
        jQuery(document).ready(function ($) {
            $.validate();
        });
    ';
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
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
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Add Email','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Add Email', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=email&task=saveemail"),"save-email")); ?>">
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Email', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                    <div class="js-form-field"><?php echo wp_kses(JSSTformfield::text('email', isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->email : '', array('class' => 'inputbox js-form-input-field', 'data-validation' => 'required email')), JSST_ALLOWED_TAGS) ?></div>
                </div>
                <?php if(in_array('smtp', jssupportticket::$_active_addons)){ ?>
                    <div class="js-form-wrapper">
                        <div class="js-form-title"><?php echo esc_html(__('Send Email By', 'js-support-ticket')); ?></div>
                        <div class="js-form-field"><?php echo wp_kses(JSSTformfield::select('smtpemailauth', $emailtype , isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->smtpemailauth : '' , esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'js-smtp-select js-form-select-field')), JSST_ALLOWED_TAGS)?></div>
                    </div>
                    <div id="smtpauthselect" style="display: none;">
                        <div class="js-form-wrapper">
                            <div class="js-form-title"><?php echo esc_html(__('SMTP host type', 'js-support-ticket')); ?></div>
                            <div class="js-form-field"><?php echo wp_kses(JSSTformfield::select('smtphosttype', $smtphost , isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->smtphosttype : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'js-smtp-select js-form-select-field')), JSST_ALLOWED_TAGS)?></div>
                        </div>
                        <div class="js-form-wrapper">
                            <div class="js-form-title"><?php echo esc_html(__('SMTP host', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                            <div class="js-form-field"><?php echo wp_kses(JSSTformfield::text('smtphost', isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->smtphost : '', array('class' => 'inputbox js-form-select-field')), JSST_ALLOWED_TAGS) ?></div>
                        </div>
                        <div class="js-form-wrapper">
                            <div class="js-form-title"><?php echo esc_html(__('SMTP Authentication', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                            <div class="js-form-field"><?php echo wp_kses(JSSTformfield::select('smtpauthencation', $truefalse , isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->smtpauthencation : '' , esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'js-smtp-select js-form-select-field')), JSST_ALLOWED_TAGS)?></div>
                        </div>
                        <div class="js-form-wrapper">
                            <div class="js-form-title"><?php echo esc_html(__('Username', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                            <div class="js-form-field"><?php echo wp_kses(JSSTformfield::text('name', isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->name : '', array('class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS) ?></div>
                        </div>
                        <div class="js-form-wrapper">
                            <div class="js-form-title"><?php echo esc_html(__('Password', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                            <div class="js-form-field"><?php echo wp_kses(JSSTformfield::password('password', isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->password : '', array('class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS) ?></div>
                        </div>
                        <div class="js-form-wrapper">
                            <div class="js-form-title"><?php echo esc_html(__('SMTP Secure', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                            <div class="js-form-field"><?php echo wp_kses(JSSTformfield::select('smtpsecure', $securesmtp , isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->smtpsecure : '' , esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'js-smtp-select js-form-select-field')), JSST_ALLOWED_TAGS)?></div>
                        </div>
                        <div class="js-form-wrapper">
                            <div class="js-form-title"><?php echo esc_html(__('SMTP Port', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span></div>
                            <div class="js-form-field"><?php echo wp_kses(JSSTformfield::text('mailport', isset(jssupportticket::$_data[0]->email) ? jssupportticket::$_data[0]->mailport : '', array('class' => 'inputbox js-form-input-field')), JSST_ALLOWED_TAGS) ?></div>
                        </div>
                        <div class="js-col-md-12 js-col-md-offset-2 js-admin-ticketviaemail-wrapper-checksetting">
                            <a title="<?php echo esc_html(__('Check Settings','js-support-ticket')); ?>" href="#" id="js-admin-ticketviaemail"><img alt="<?php echo esc_html(__('check','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/tick_ticketviaemail.png" /><?php echo esc_html(__('Check Settings','js-support-ticket')); ?></a>
                            <div id="js-admin-ticketviaemail-bar"></div>
                            <div class="js-col-md-12" id="js-admin-ticketviaemail-text"><?php echo esc_html(__('If the system doesnot respond in 30 seconds','js-support-ticket')).', '. esc_html(__('it means system unable to connect email server','js-support-ticket')); ?></div>
                            <div class="js-col-md-12">
                               <div id="js-admin-ticketviaemail-msg"></div>
                           </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Auto Response', 'js-support-ticket')); ?></div>
                    <div class="js-form-field"><?php echo wp_kses(JSSTformfield::radiobutton('autoresponse', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->autoresponse) ? jssupportticket::$_data[0]->autoresponse : '1', array('class' => 'radiobutton js-form-radio-field')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                    <div class="js-form-field"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => esc_html(__('Active', 'js-support-ticket')), '0' => esc_html(__('Disabled', 'js-support-ticket'))), isset(jssupportticket::$_data[0]->status) ? jssupportticket::$_data[0]->status : '1', array('class' => 'radiobutton js-form-radio-field')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$_data[0]->id) ? jssupportticket::$_data[0]->id : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('created', isset(jssupportticket::$_data[0]->created) ? jssupportticket::$_data[0]->created : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('updated', isset(jssupportticket::$_data[0]->updated) ? jssupportticket::$_data[0]->updated : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'email_saveemail'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Email', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$jssupportticket_js ='
    jQuery(document).ready(function($){
        smtpAuthSelect();
        if(jQuery("#host").val() == "")
            smtphosttype(1);
        $("select#smtpemailauth").change(function(){
            smtpAuthSelect();
        });
        $("#smtphosttype").change(function(){
            smtphosttype(1);
        });

        function smtpAuthSelect(){
            if(jQuery("select#smtpemailauth").val() == 1){
                jQuery("div#smtpauthselect").show();
            }else{
                jQuery("div#smtpauthselect").hide();
            }
        }

        function smtphosttype(n){
            if(n==1 || jQuery("#host").val() == ""){
                if(jQuery("#smtphosttype").val() == 1){
                    jQuery("#host").val("smtp.gmail.com");
                }else if(jQuery("#smtphosttype").val() == 2){
                    jQuery("#host").val("smtp.mail.yahoo.com");
                }else if(jQuery("#smtphosttype").val() == 3){
                    jQuery("#host").val("smtp.live.com");
                }else if(jQuery("#smtphosttype").val() == 4){
                    jQuery("#host").val("smtp.aol.com");
                }else{
                    jQuery("#host").val("");
                }
            }
        }

        $("form").submit(function(e){
            if(jQuery("select#smtpemailauth").val() == 1){
                if($("#host").val() == "" || $("#name").val() == "" || $("#password").val() == "" || $("#smtpsecure").val() == "" || $("#port").val() == "" || $("#smtpauthencation").val() == ""){
                    e.preventDefault();
                    alert("'. esc_html(__("Some values are not acceptable please retry", "js-support-ticket"))  .'");
                }
            }
            if(jQuery("select#smtpemailauth").val() == 0){
                $("#host").val("");
                $("#name").val("");
                $("#password").val("");
                $("#smtpsecure").val("");
                $("#port").val("");
                $("#smtpauthencation").val("");
            }
        });
        jQuery("a#js-admin-ticketviaemail").click(function(e){
            e.preventDefault();

                var hosttype = jQuery("select#smtphosttype").val();
                var hostname = jQuery("input#smtphost").val();
                if(hosttype == 4){
                    var hostname = jQuery("input#hostname").val();
                    if(hostname != ""){
                        var hostname = jQuery("input#hostname").val();
                    }else{
                        alert("'. esc_html(__("Please enter the hostname first","js-support-ticket")).'");
                        return;
                    }
                }
                var emailaddress = jQuery("input#name").val();
                var password = jQuery("input#password").val();
                var ssl = jQuery("select#smtpsecure").val();
                var hostportnumber = jQuery("input#mailport").val();
                var smtpauthencation_val = jQuery("select#smtpauthencation").val();
                jQuery("div#js-admin-ticketviaemail-bar").show();
                jQuery("div#js-admin-ticketviaemail-text").show();
                jQuery.post(ajaxurl, {action: "jsticket_ajax", hosttype: hosttype,hostname:hostname, emailaddress: emailaddress,password:password,ssl:ssl,hostportnumber:hostportnumber, smtpauthencation:smtpauthencation_val , jstmod: "email", task: "sendTestEmail", "_wpnonce":"'.esc_attr(wp_create_nonce("send-test-email")).'"}, function (data) {
                    if (data) {
                        jQuery("div#js-admin-ticketviaemail-bar").hide();
                        jQuery("div#js-admin-ticketviaemail-text").hide();
                        var obj = jQuery.parseJSON(data);
                        if(obj.type == 0){
                            jQuery("div#js-admin-ticketviaemail-msg").html(obj.text).addClass("no-error");
                        }else{
                            jQuery("div#js-admin-ticketviaemail-msg").html(obj.text).addClass("imap-error");
                        }
                        jQuery("div#js-admin-ticketviaemail-msg").show();
                    }
                });//jquery closed

        });
    });
';
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
