<?php
if (!defined('ABSPATH')) die('Restricted Access');
$tran_data = json_decode(jssupportticket::$_data[0]['jstran']);
?>
<div id="js-tk-admin-wrapper">
    <div id="js-tk-cparea">
        <div id="jsst-main-wrapper" class="post-installation">
            <div class="js-admin-title-installtion">
                <span class="jsst_heading"><?php echo esc_html(__('JS Help Desk Settings','js-support-ticket')); ?></span>
                <div class="close-button-bottom">
                    <a href="?page=jssupportticket" class="close-button">
                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/close-icon.png';?>" />
                    </a>
                </div>
            </div>
            <div class="post-installtion-content-wrapper">
                <div class="post-installtion-content-header">
                    <ul class="update-header-img step-1">
                        <li class="header-parts first-part">
                            <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=stepone")); ?>" title="link" class="tab_icon">
                                <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/general-settings.png';?>" />
                                <span class="text"><?php echo esc_html(__('General','js-support-ticket')); ?></span>
                            </a>
                        </li>
                        <li class="header-parts second-part">
                            <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=steptwo")); ?>" title="link" class="tab_icon">
                                <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/ticket.png';?>" />
                                <span class="text"><?php echo esc_html(__('Ticket Settings','js-support-ticket')); ?></span>
                            </a>
                        </li>
                        <?php if($tran_data){ ?>
                            <li class="header-parts third-part active">
                               <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=translationoption")); ?>" title="link" class="tab_icon">
                                   <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/translation.png';?>" />
                                    <span class="text"><?php echo esc_html(__('Translation','js-support-ticket')); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
                            <li class="header-parts third-part">
                               <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=stepthree")); ?>" title="link" class="tab_icon">
                                   <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/feedback.png';?>" />
                                    <span class="text"><?php echo esc_html(__('Feedback Settings','js-support-ticket')); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                        <li class="header-parts forth-part">
                            <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=settingcomplete")); ?>" title="link" class="tab_icon">
                               <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/complete.png';?>" />
                                <span class="text"><?php echo esc_html(__('Complete','js-support-ticket')); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="post-installtion-content_wrapper_right">
                    <div class="jsst-config-topheading">
                        <span class="heading-post-ins jsst-configurations-heading"><?php echo esc_html(__('Download Translation File','js-support-ticket'));?></span>
                    </div>
                    <div class="post-installtion-content">
                        <div id="black_wrapper_translation"></div>
                        <div id="jstran_loading">
                            <img alt="<?php echo esc_html(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
                        </div>
                        <form id="jssupportticket-form-ins" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=postinstallation&task=save&action=jstask"),"save")); ?>">
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Language code','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::text('codelang', isset($tran_data->code) ? $tran_data->lang_fullname . " (" . esc_attr($tran_data->code) . ")" : '' , array('class' => 'inputbox jsst-postsetting', 'data-validation' => 'required' , 'readonly' => true)), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__('Want to download translation file? Click on download. It will take sometime.','js-support-ticket'));?>
                                </div>
                            </div>
                            <div id="js-emessage-wrapper">
                                <img alt="<?php echo esc_html(__('c error','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/c_error.png" />
                                <div id="jslang_em_text"></div>
                            </div>
                            <div id="js-emessage-wrapper_ok">
                                <img alt="<?php echo esc_html(__('saved','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/saved.png" />
                                <div id="jslang_em_text_ok"></div>
                            </div>
                            <div class="pic-button-part pic-3-button">
                                <a class="next-step" href="javascript:void(0);" id="jsdownloadbutton">
                                    <?php echo esc_html(__('Download & Next','js-support-ticket')); ?>
                                </a>
                                <a class="skip-step" href="javascript:void(0);" onclick="document.getElementById('jssupportticket-form-ins').submit();">
                                    <?php echo esc_html(__('Skip this step','js-support-ticket')); ?>
                                </a>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'postinstallation_save'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('step', 'translationoption'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('translations', isset($tran_data->name->lang_name) ? $tran_data->name->lang_name: ''), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('languagecode', isset($tran_data->code) ? $tran_data->code: ''), JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$jssupportticket_js ="
    jQuery(document).ready(function(){
        jQuery('#jsdownloadbutton').click(function(){
            jQuery('#js-emessage-wrapper_ok').hide();
            var lang_name = jQuery('#translations').val();
            var file_name = jQuery('#languagecode').val();
            if(lang_name != '' && file_name != ''){
                jsShowLoading();
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'getlanguagetranslation',langname:lang_name , filename: file_name, '_wpnonce':'".esc_attr(wp_create_nonce('get-language-translation'))."'}, function (data) {
                    if (data) {
                        jsHideLoading();
                        data = JSON.parse(data);
                        if(data['error']){
                            jQuery('#js-emessage-wrapper div').html('File not be able to download');
                            jQuery('#js-emessage-wrapper').show();
                        }else{
                            jQuery('#js-emessage-wrapper').hide();
                            jQuery('#js-emessage-wrapper_ok div').html('File Downloaded Successfully');
                            jQuery('#js-emessage-wrapper_ok').slideDown();
                            document.getElementById('jssupportticket-form-ins').submit();
                        }
                    }
                });
            }
        });
    });

    function jsShowLoading(){
        jQuery('div#black_wrapper_translation').show();
        jQuery('div#jstran_loading').show();
    }

    function jsHideLoading(){
        jQuery('div#black_wrapper_translation').hide();
        jQuery('div#jstran_loading').hide();
    }
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
