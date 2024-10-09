<?php
if (!defined('ABSPATH')) die('Restricted Access');
$yesno = array(
    (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket')))
    );
$showhide = array(
    (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
    (object) array('id' => '0', 'text' => esc_html(__('No', 'js-support-ticket')))
    );
$date_format = array(
    (object) array('id' => 'd-m-Y', 'text' => esc_html(__('DD-MM-YYYY' , 'js-support-ticket'))),
    (object) array('id' => 'm-d-Y', 'text' => esc_html(__('MM-DD-YYYY' , 'js-support-ticket'))),
    (object) array('id' => 'Y-m-d', 'text' => esc_html(__('YYYY-MM-DD' , 'js-support-ticket')))
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
                        <li class="header-parts first-part active">
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
                        <span class="heading-post-ins jsst-configurations-heading"><?php echo esc_html(__('General Configurations','js-support-ticket'));?></span>
                        <?php
                            if($tran_opt && in_array('feedback', jssupportticket::$_active_addons)){
                                $step = '5';
                            }else if(!$tran_opt && !in_array('feedback', jssupportticket::$_active_addons)){
                                $step = '3';
                            }else{
                                $step = '4';
                            }
                            $steps = esc_html(__('Step 1 of ','js-support-ticket'));
                            $steps .= $step;
                        ?>
                        <span class="heading-post-ins jsst-config-steps"><?php echo esc_html($steps); ?></span>
                    </div>
                    <div class="post-installtion-content">
                        <form id="jssupportticket-form-ins" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=postinstallation&task=save&action=jstask"),"save")); ?>">
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Title','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::text('title', isset(jssupportticket::$_data[0]['title']) ? jssupportticket::$_data[0]['title'] : '', array('class' => 'inputbox jsst-postsetting', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("Enter the site title")); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Data Directory','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::text('data_directory', isset(jssupportticket::$_data[0]['data_directory']) ? jssupportticket::$_data[0]['data_directory'] : '', array('class' => 'inputbox jsst-postsetting', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("You need to rename the existing data directory in the file system before changing the data directory name",'js-support-ticket')); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Date Format','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('date_format', $date_format , isset(jssupportticket::$_data[0]['date_format']) ? jssupportticket::$_data[0]['date_format'] : '' , esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS)?>
                                </div>
                                <div class="desc"><?php echo esc_html(__('Date format for plugin','js-support-ticket'));?> </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Ticket auto close','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::text('ticket_auto_close', isset(jssupportticket::$_data[0]['ticket_auto_close']) ? jssupportticket::$_data[0]['ticket_auto_close'] : '', array('class' => 'inputbox jsst-postsetting', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("Ticket auto-close if user does not respond within given days")); ?>
                                </div>
                            </div>
                            <?php /*<div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Show Breadcrumbs','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('show_breadcrumbs', $showhide , isset(jssupportticket::$_data[0]['show_breadcrumbs']) ? jssupportticket::$_data[0]['show_breadcrumbs'] : '', '' , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__('Show navigation in breadcrumbs'); ?>&nbsp;
                                </div>
                            </div>*/?>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('File maximum size','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::text('file_maximum_size', isset(jssupportticket::$_data[0]['file_maximum_size']) ? jssupportticket::$_data[0]['file_maximum_size'] : '', array('class' => 'inputbox jsst-postsetting', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__("Upload file size in KB's")); ?>
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('File Extension','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::textarea('file_extension', isset(jssupportticket::$_data[0]['file_extension']) ? jssupportticket::$_data[0]['file_extension'] : '', array('class' => 'inputbox js-textarea', 'data-validation' => 'required')), JSST_ALLOWED_TAGS) ?>
                                </div>
                                <div class="desc">
                                    <?php echo esc_html(__('Show navigation in breadcrumbs')); ?>&nbsp;
                                </div>
                            </div>
                            <div class="pic-config">
                                <div class="title">
                                    <?php echo esc_html(__('Show count on my tickets','js-support-ticket'));?>:
                                </div>
                                <div class="field">
                                    <?php echo wp_kses(JSSTformfield::select('count_on_myticket', $yesno , isset(jssupportticket::$_data[0]['count_on_myticket']) ? jssupportticket::$_data[0]['count_on_myticket'] : '', esc_html(__('Select Type', 'js-support-ticket')) , array('class' => 'inputbox jsst-postsetting js-select jsst-postsetting ')), JSST_ALLOWED_TAGS);?>
                                </div>
                            </div>
                            <div class="pic-button-part">
                                <a class="next-step full-width" href="#" onclick="document.getElementById('jssupportticket-form-ins').submit();" >
                                    <?php echo esc_html(__('Next','js-support-ticket')); ?>
                                     <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL).'includes/images/postinstallation/next-arrow.png';?>">
                                </a>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'postinstallation_save'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('step', 1), JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
