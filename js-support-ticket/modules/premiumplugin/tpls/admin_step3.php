<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
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
                        <li><?php echo esc_html(__('Install Addons','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Install Addons','js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0">
            <div id="jssupportticket-content">
                <div id="black_wrapper_translation"></div>
                <div id="jstran_loading">
                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
                </div>
                <div id="jsst-lower-wrapper">
                    <div class="jsst-addon-installer-wrapper step3" >
                        <div class="jsst-addon-installer-left-image-wrap" >
                            <img class="jsst-addon-installer-left-image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/addon-images/addon-installer-logo.png" />
                        </div>
                        <div class="jsst-addon-installer-left-heading" >
                            <?php echo esc_html(__("Add ons installed and activated successfully","js-support-ticket")); ?>
                        </div>
                        <div class="jsst-addon-installer-left-description" >
                            <?php echo esc_html(__("Add ons for JS Help Desk have been installed and activated successfully. ","js-support-ticket")); ?>
                        </div>
                        <div class="jsst-addon-installer-right-button" >
                            <a class="jsst_btn" href="?page=jssupportticket" ><?php echo esc_html(__("Control Panel","js-support-ticket")); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
