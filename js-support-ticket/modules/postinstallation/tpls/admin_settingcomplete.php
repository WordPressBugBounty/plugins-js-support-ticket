<?php
if (!defined('ABSPATH')) die('Restricted Access');
$tran_opt = JSSTincluder::getJSModel('jssupportticket')->getInstalledTranslationKey(); ?>
<div id="js-tk-admin-wrapper">
    <div id="js-tk-cparea">
        <div id="jsst-main-wrapper" class="post-installation">
            <div class="js-admin-title-installtion">
                <span class="jsst_heading"><?php echo esc_html(__('JS Support Ticket Settings','js-support-ticket')); ?></span>
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
                        <?php if(JSSTincluder::getJSModel('jssupportticket')->getInstalledTranslationKey()){ ?>
                            <li class="header-parts third-part">
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
                        <li class="header-parts forth-part active">
                            <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=settingcomplete")); ?>" title="link" class="tab_icon">
                               <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/complete.png';?>" />
                                <span class="text"><?php echo esc_html(__('Complete','js-support-ticket')); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="post-installtion-content_wrapper_right">
                    <div class="jsst-config-topheading">
                        <span class="heading-post-ins jsst-configurations-heading"><?php echo esc_html(__('Configurations complete','js-support-ticket'));?></span>
                        <?php
                            if($tran_opt && in_array('feedback', jssupportticket::$_active_addons)){
                                $step = '5';
                            }else if(!$tran_opt && !in_array('feedback', jssupportticket::$_active_addons)){
                                $step = '3';
                            }else{
                                $step = '4';
                            }
                            $steps = 'Step ' . $step . ' of '  . $step;
                        ?>
                        <span class="heading-post-ins jsst-config-steps"><?php echo esc_html($steps);?></span>
                    </div>
                    <div class="post-installtion-content">
                        <form id="jslearnmanager-form-ins" method="post" action="#">
                            <div class="jsst_setting_complete_heading"><h1 class="Jsst_heading"><?php echo esc_html(__('Setting Completed','js-support-ticket')); ?></h1></div>
                            <div class="jsst_img_wrp">
                                <img  src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/complete-setting.png';?>" alt="Seting Log" title="Setting Logo">
                            </div>
                            <div class="jsst_text_below_img">
                                <?php echo esc_html(__('Setting you applied has been saved successfully.','js-support-ticket'));?>
                            </div>
                            <div class="pic-button-part">
                                <a class="next-step finish full-width" href="?page=jssupportticket">
                                    <?php echo esc_html(__('Finish','js-support-ticket')); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

