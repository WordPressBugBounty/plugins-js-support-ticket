<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');

$jsst_smtphost = array(
    (object) array('id' => '1', 'text' => esc_html(__('Gmail', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Yahoo', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('Hotmail', 'js-support-ticket'))),
    (object) array('id' => '4', 'text' => esc_html(__('Aol', 'js-support-ticket'))),
    (object) array('id' => '5', 'text' => esc_html(__('Other', 'js-support-ticket')))
);
$jsst_emailtype = array(
    (object) array('id' => '0', 'text' => esc_html(__('Default', 'js-support-ticket'))),
    (object) array('id' => '1', 'text' => esc_html(__('SMTP', 'js-support-ticket')))
);
$jsst_truefalse = array(
    (object) array('id' => '0', 'text' => esc_html(__('False', 'js-support-ticket'))),
    (object) array('id' => '1', 'text' => esc_html(__('True', 'js-support-ticket')))
);
$jsst_securesmtp = array(
    (object) array('id' => '1', 'text' => esc_html(__('TLS', 'js-support-ticket'))),
    (object) array('id' => '0', 'text' => esc_html(__('SSL', 'js-support-ticket')))
);
$jsst_jssupportticket_js ='
    jQuery(document).ready(function ($) {
        $.validate();
    });
';
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_e        = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : false;
$jsst_hasemail = isset($jsst_e->email);
$jsst_heading  = !empty($jsst_e->id) ? __('Edit Email', 'js-support-ticket') : __('Add Email', 'js-support-ticket');
$jsst_nonce_id = isset($jsst_e->id) ? $jsst_e->id : '';
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => $jsst_heading,
            'crumbs' => array(array('text' => __('System Emails', 'js-support-ticket'), 'url' => admin_url('admin.php?page=email&jstlay=emails'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=email&task=saveemail"),"save-email-".$jsst_nonce_id)); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The address', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-lg">
                                    <label class="jsst-flabel" for="email"><?php echo esc_html(__('Email', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('email', $jsst_hasemail ? $jsst_e->email : '', array('data-validation' => 'required email')), JSST_ALLOWED_TAGS) ?></div>
                                </div>
                                <div class="jsst-frow-break"></div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Auto Response', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('autoresponse', array('1' => esc_html(__('Yes', 'js-support-ticket')), '0' => esc_html(__('No', 'js-support-ticket'))), isset($jsst_e->autoresponse) ? $jsst_e->autoresponse : '1'), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('status', array('1' => esc_html(__('Active', 'js-support-ticket')), '0' => esc_html(__('Disabled', 'js-support-ticket'))), isset($jsst_e->status) ? $jsst_e->status : '1'), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                            </div>
                        </fieldset>
                        <?php if(in_array('smtp', jssupportticket::$_active_addons)){ ?>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('How it is sent', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="smtpemailauth"><?php echo esc_html(__('Send Email By', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('smtpemailauth', $jsst_emailtype, $jsst_hasemail ? $jsst_e->smtpemailauth : '', esc_html(__('Select Type', 'js-support-ticket'))), JSST_ALLOWED_TAGS)?></div>
                                </div>
                            </div>
                            <div id="smtpauthselect" style="display: none;">
                                <div class="jsst-formgrid">
                                    <div class="jsst-frow jsst-frow-sm">
                                        <label class="jsst-flabel" for="smtphosttype"><?php echo esc_html(__('SMTP Host Type', 'js-support-ticket')); ?></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('smtphosttype', $jsst_smtphost, $jsst_hasemail ? $jsst_e->smtphosttype : '', esc_html(__('Select Type', 'js-support-ticket'))), JSST_ALLOWED_TAGS)?></div>
                                    </div>
                                    <div class="jsst-frow jsst-frow-md">
                                        <label class="jsst-flabel" for="smtphost"><?php echo esc_html(__('SMTP Host', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('smtphost', $jsst_hasemail ? $jsst_e->smtphost : ''), JSST_ALLOWED_TAGS) ?></div>
                                    </div>
                                    <div class="jsst-frow jsst-frow-xs">
                                        <label class="jsst-flabel" for="mailport"><?php echo esc_html(__('SMTP Port', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('mailport', $jsst_hasemail ? $jsst_e->mailport : '', array('inputmode' => 'numeric')), JSST_ALLOWED_TAGS) ?></div>
                                    </div>
                                    <div class="jsst-frow jsst-frow-sm">
                                        <label class="jsst-flabel" for="smtpsecure"><?php echo esc_html(__('SMTP Secure', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('smtpsecure', $jsst_securesmtp, $jsst_hasemail ? $jsst_e->smtpsecure : '', esc_html(__('Select Type', 'js-support-ticket'))), JSST_ALLOWED_TAGS)?></div>
                                    </div>
                                    <div class="jsst-frow-break"></div>
                                    <div class="jsst-frow jsst-frow-sm">
                                        <label class="jsst-flabel" for="smtpauthencation"><?php echo esc_html(__('SMTP Authentication', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('smtpauthencation', $jsst_truefalse, $jsst_hasemail ? $jsst_e->smtpauthencation : '', esc_html(__('Select Type', 'js-support-ticket'))), JSST_ALLOWED_TAGS)?></div>
                                    </div>
                                    <div class="jsst-frow jsst-frow-md">
                                        <label class="jsst-flabel" for="name"><?php echo esc_html(__('Username', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('name', $jsst_hasemail ? $jsst_e->name : '', array('autocomplete' => 'off')), JSST_ALLOWED_TAGS) ?></div>
                                    </div>
                                    <div class="jsst-frow jsst-frow-md">
                                        <label class="jsst-flabel" for="password"><?php echo esc_html(__('Password', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::password('password', $jsst_hasemail ? $jsst_e->password : '', array('autocomplete' => 'new-password')), JSST_ALLOWED_TAGS) ?></div>
                                    </div>
                                    <div class="jsst-frow jsst-frow-full">
                                        <div class="jsst-connect">
                                            <a class="jsst-btn" href="#" id="js-admin-ticketviaemail"><?php echo esc_html(__('Check Settings','js-support-ticket')); ?></a>
                                            <div class="jsst-connect-progress" id="js-admin-ticketviaemail-bar"></div>
                                            <p class="jsst-connect-note" id="js-admin-ticketviaemail-text"><?php echo esc_html(__('If the system does not respond in 30 seconds','js-support-ticket')).', '. esc_html(__('it means system unable to connect email server','js-support-ticket')); ?></p>
                                            <div class="jsst-notice jsst-connect-msg" id="js-admin-ticketviaemail-msg"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <?php } ?>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('id', isset($jsst_e->id) ? $jsst_e->id : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('created', isset($jsst_e->created) ? $jsst_e->created : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('updated', isset($jsst_e->updated) ? $jsst_e->updated : '' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'email_saveemail'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=email&jstlay=emails')); ?>"><?php echo esc_html(__('Cancel', 'js-support-ticket')); ?></a>
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Email', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
/* The SMTP fields are #smtphost and #mailport; this script used to look for
   #host and #port, so the host presets and the required check never ran. */
$jsst_jssupportticket_js ='
    jQuery(document).ready(function($){
        smtpAuthSelect();
        if(jQuery("#smtphost").val() == "")
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
            if(n==1 || jQuery("#smtphost").val() == ""){
                if(jQuery("#smtphosttype").val() == 1){
                    jQuery("#smtphost").val("smtp.gmail.com");
                }else if(jQuery("#smtphosttype").val() == 2){
                    jQuery("#smtphost").val("smtp.mail.yahoo.com");
                }else if(jQuery("#smtphosttype").val() == 3){
                    jQuery("#smtphost").val("smtp.live.com");
                }else if(jQuery("#smtphosttype").val() == 4){
                    jQuery("#smtphost").val("smtp.aol.com");
                }else{
                    jQuery("#smtphost").val("");
                }
            }
        }

        $("form.jsstadmin-form").submit(function(e){
            if(jQuery("select#smtpemailauth").val() == 1){
                /* Name what is missing - "some values are not acceptable" left
                   people guessing which of six boxes to look at. */
                var missing = [];
                jQuery.each(["smtphost", "name", "password", "smtpsecure", "mailport", "smtpauthencation"], function (i, id) {
                    var field = $("#" + id);
                    if (field.length && field.val() == "") {
                        var label = jQuery.trim($("label[for=\"" + id + "\"]").first().text().replace("*", ""));
                        missing.push(label !== "" ? label : id);
                    }
                });
                if (missing.length) {
                    e.preventDefault();
                    alert("'. esc_js(__("SMTP is switched on, so these need filling in:", "js-support-ticket"))  .'\n- " + missing.join("\n- "));
                    $("#" + ["smtphost", "name", "password", "smtpsecure", "mailport", "smtpauthencation"].filter(function (id) { return $("#" + id).length && $("#" + id).val() == ""; })[0]).focus();
                }
            }
            if(jQuery("select#smtpemailauth").val() == 0){
                $("#smtphost").val("");
                $("#name").val("");
                $("#password").val("");
                $("#smtpsecure").val("");
                $("#mailport").val("");
                $("#smtpauthencation").val("");
            }
        });
        jQuery("a#js-admin-ticketviaemail").click(function(e){
            e.preventDefault();
            var hosttype = jQuery("select#smtphosttype").val();
            var hostname = jQuery("input#smtphost").val();
            if(hostname == ""){
                alert("'. esc_js(__("Please enter the host name first","js-support-ticket")).'");
                return;
            }
            var emailaddress = jQuery("input#name").val();
            var password = jQuery("input#password").val();
            var ssl = jQuery("select#smtpsecure").val();
            var hostportnumber = jQuery("input#mailport").val();
            var smtpauthencation_val = jQuery("select#smtpauthencation").val();
            jQuery("div#js-admin-ticketviaemail-msg").hide().removeClass("jsst-notice-ok jsst-notice-bad");
            jQuery("div#js-admin-ticketviaemail-bar").show();
            jQuery("p#js-admin-ticketviaemail-text").show();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", hosttype: hosttype,hostname:hostname, emailaddress: emailaddress,password:password,ssl:ssl,hostportnumber:hostportnumber, smtpauthencation:smtpauthencation_val , jstmod: "email", task: "sendTestEmail", "_wpnonce":"'.esc_attr(wp_create_nonce("send-test-email")).'"}, function (data) {
                if (data) {
                    jQuery("div#js-admin-ticketviaemail-bar").hide();
                    jQuery("p#js-admin-ticketviaemail-text").hide();
                    var obj = jQuery.parseJSON(data);
                    if(obj.type == 0){
                        jQuery("div#js-admin-ticketviaemail-msg").html(obj.text).addClass("jsst-notice-ok");
                    }else{
                        jQuery("div#js-admin-ticketviaemail-msg").html(obj.text).addClass("jsst-notice-bad");
                    }
                    jQuery("div#js-admin-ticketviaemail-msg").show();
                }
            });
        });
    });
';
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
?>
