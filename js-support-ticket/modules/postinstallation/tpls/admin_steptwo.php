<?php
if (!defined('ABSPATH')) die('Restricted Access');
$yesno = array(
    (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket')))
    );
$ticketidsequence = array(
    (object) array('id' => '1', 'text' => esc_html(__('Random', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Sequential', 'js-support-ticket')))
    );
$owncaptchaoparend = array(
    (object) array('id' => '2', 'text' => esc_html(__('2', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('3', 'js-support-ticket')))
    );
$tran_opt = JSSTincluder::getJSModel('jssupportticket')->getInstalledTranslationKey();
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
                        <li class="header-parts first-part ">
                            <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=stepone")); ?>" title="link" class="tab_icon">
                                <img class="start" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/general-settings.png';?>" />
                                <span class="text"><?php echo esc_html(__('General','js-support-ticket')); ?></span>
                            </a>
                        </li>
                        <li class="header-parts second-part active">
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
                        <span class="heading-post-ins jsst-configurations-heading"><?php echo esc_html(__('Ticket Configurations','js-support-ticket'));?></span>
                        <?php
                            if($tran_opt && in_array('feedback', jssupportticket::$_active_addons)){
                                $step = '5';
                            }else if(!$tran_opt && !in_array('feedback', jssupportticket::$_active_addons)){
                                $step = '3';
                            }else{
                                $step = '4';
                            }
                            $steps = esc_html(__('Step 2 of ','js-support-ticket'));
                            $steps .= $step;
                        ?>
                        <span class="heading-post-ins jsst-config-steps"><?php echo esc_html($steps); ?></span>
                    </div>
                    <div class="post-installtion-content">
                        <form id="jssupportticket-form-ins" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=postinstallation&task=save&action=jstask"),"save")); ?>">
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Visitor can create ticket','js-support-ticket')); ?><?php echo esc_html(__(':', 'js-support-ticket'));?>
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('visitor_can_create_ticket', $yesno , isset(jssupportticket::$_data[0]['visitor_can_create_ticket']) ? jssupportticket::$_data[0]['visitor_can_create_ticket'] : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("Enable/Disable Open Ticket")); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Ticket ID sequence','js-support-ticket')); ?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('ticketid_sequence', $ticketidsequence , isset(jssupportticket::$_data[0]['ticketid_sequence']) ? jssupportticket::$_data[0]['ticketid_sequence'] : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("Set ticket id sequential or random",'js-support-ticket')); ?>&nbsp;
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Maximum Tickets','js-support-ticket')); ?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::text('maximum_tickets', isset(jssupportticket::$_data[0]['maximum_tickets']) ? jssupportticket::$_data[0]['maximum_tickets'] : '', array('class' => 'inputbox jsst-postsetting', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("Set Maximum Ticket Per user")); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Maximum Open Ticket','js-support-ticket')); ?>:
                                </div>
                                <div class="field">
                                   <?php echo wp_kses(JSSTformfield::text('maximum_open_tickets', isset(jssupportticket::$_data[0]['maximum_open_tickets']) ? jssupportticket::$_data[0]['maximum_open_tickets'] : '', array('class' => 'inputbox jsst-postsetting', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("Maximum Open Ticket",'js-support-ticket')); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Reopen ticket within Days','js-support-ticket')); ?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::text('reopen_ticket_within_days', isset(jssupportticket::$_data[0]['reopen_ticket_within_days']) ? jssupportticket::$_data[0]['reopen_ticket_within_days'] : '', array('class' => 'inputbox jsst-postsetting', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("The ticket can be reopened within a given number of days",'js-support-ticket')); ?>&nbsp;
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Show Captcha to visitor on ticket form','js-support-ticket')); ?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('show_captcha_on_visitor_from_ticket', $yesno , isset(jssupportticket::$_data[0]['show_captcha_on_visitor_from_ticket']) ? jssupportticket::$_data[0]['show_captcha_on_visitor_from_ticket'] : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("Enable/Disable Captcha on Ticket Form",'js-support-ticket')); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Own Captcha operands','js-support-ticket')); ?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('owncaptcha_totaloperand', $owncaptchaoparend , isset(jssupportticket::$_data[0]['owncaptcha_totaloperand']) ? jssupportticket::$_data[0]['owncaptcha_totaloperand'] : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                                <div class="desc">
                                   <?php echo esc_html(__("Select the total operands to be given",'js-support-ticket')); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Own captcha subtraction answer positive','js-support-ticket')); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('owncaptcha_subtractionans', $yesno , isset(jssupportticket::$_data[0]['owncaptcha_subtractionans']) ? jssupportticket::$_data[0]['owncaptcha_subtractionans'] : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                                <div class="desc">
                                   <?php echo esc_html(__("Enable/Disable Own Captcha subtraction")); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Enable Print Ticket','js-support-ticket')); ?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('print_ticket_user', $yesno , isset(jssupportticket::$_data[0]['print_ticket_user']) ? jssupportticket::$_data[0]['print_ticket_user'] : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                                <div class="desc">
                                   <?php echo esc_html(__("Enable/Disable Print Ticket")); ?>
                                </div>
                            </div>
                            <div class="pic-button-part">
                                <a class="next-step" href="#" onclick="document.getElementById('jssupportticket-form-ins').submit();" >
                                    <?php echo esc_html(__('Next','js-support-ticket')); ?>
                                     <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/next-arrow.png';?>">
                                </a>
                                <a class="back" href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=stepone')); ?>">
                                     <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/back-arrow.png';?>">
                                    <?php echo esc_html(__('Back','js-support-ticket')); ?>
                                </a>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'postinstallation_save'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('step', 2), JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
