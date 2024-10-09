<?php
if (!defined('ABSPATH')) die('Restricted Access');
$ticketidsequence = array(
    (object) array('id' => '0', 'text' => esc_html(__('Random', 'js-support-ticket'))),
    (object) array('id' => '1', 'text' => esc_html(__('Sequential', 'js-support-ticket')))
    );
$type = array(
    (object) array('id' => '0', 'text' => esc_html(__('Days', 'js-support-ticket'))),
    (object) array('id' => '1', 'text' => esc_html(__('Hours', 'js-support-ticket')))
    );

?>
<div id="js-tk-admin-wrapper">
    <div id="js-tk-cparea">
        <div id="jsst-main-wrapper" class="post-installation">
            <div class="js-admin-title-installtion">
                <span class="jsst_heading"><?php echo esc_html(__('JS Support Ticket Settings','js-support-ticket')); ?></span>
                <div class="close-button-bottom">
                    <a href="#" class="close-button">
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
                        <?php if(JSSTincluder::getJSModel('jssupportticket')->getInstalledTranslationKey()){ ?>
                            <li class="header-parts third-part">
                               <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=translationoption")); ?>" title="link" class="tab_icon">
                                   <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/translation.png';?>" />
                                    <span class="text"><?php echo esc_html(__('Translation','js-support-ticket')); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                        <li class="header-parts third-part active">
                           <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=stepthree")); ?>" title="link" class="tab_icon">
                               <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/feedback.png';?>" />
                                <span class="text"><?php echo esc_html(__('Feedback Settings','js-support-ticket')); ?></span>
                            </a>
                        </li>
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
                        <span class="heading-post-ins jsst-configurations-heading"><?php echo esc_html(__('Feedback Configurations','js-support-ticket'));?></span>
                        <span class="heading-post-ins jsst-config-steps"><?php echo esc_html(__('Step 4 of 5','js-support-ticket'));?></span>
                    </div>
                    <div class="post-installtion-content">
                        <form id="jssupportticket-form-ins" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=postinstallation&task=save&action=jstask"),"save")); ?>">
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Feedback Email Delay Type','js-support-ticket')); ?><?php echo esc_html(__(':', 'js-support-ticket'));?>
                                </div>
                                <div class="field">
                                     <?php echo wp_kses(JSSTformfield::select('feedback_email_delay_type', $type , isset(jssupportticket::$_data[0]['feedback_email_delay_type']) ? jssupportticket::$_data[0]['feedback_email_delay_type'] : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__('Set Email Delay Time')); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Feedback Email Delay','js-support-ticket')); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::text('feedback_email_delay', isset(jssupportticket::$_data[0]['feedback_email_delay']) ? jssupportticket::$_data[0]['feedback_email_delay'] : '', array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__('Set Email Delay','js-support-ticket')); ?>
                                </div>
                            </div>
                             <div class="pic-button-part">
                                <a class="next-step" href="#" onclick="document.getElementById('jssupportticket-form-ins').submit();" >
                                    <?php echo esc_html(__('Next','js-support-ticket')); ?>
                                     <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/next-arrow.png';?>">
                                </a>
                                <a class="back" href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=steptwo')); ?>">
                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/back-arrow.png';?>">
                                    <?php echo esc_html(__('Back','js-support-ticket')); ?>
                                </a>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'postinstallation_save'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('step', 3), JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
