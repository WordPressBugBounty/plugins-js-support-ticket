<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php JSSTmessage::getMessage(); ?>
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
                        <li><?php echo esc_html(__('Reports','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("Reports", 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0">
            <a class="js-admin-report-wrapper" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=overallreport')); ?>" >
                <div class="js-admin-overall-report-type-wrapper">
                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/report/overall_icon.png" />
                    <span class="js-admin-staff-report-type-label"><?php echo esc_html(__('Overall Statistics','js-support-ticket')); ?></span>
                </div>
            </a>
            <a class="js-admin-report-wrapper" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=staffreport')); ?>" >
                <div class="js-admin-staff-report-type-wrapper">
                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/report/staff.png" />
                    <span class="js-admin-staff-report-type-label"><?php echo esc_html(__('Staff Reports','js-support-ticket')); ?></span>
                </div>
            </a>
            <a class="js-admin-report-wrapper" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=departmentreport')); ?>" >
                <div class="js-admin-department-report-type-wrapper">
                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/report/department.png" />
                    <span class="js-admin-staff-report-type-label"><?php echo esc_html(__('Department Reports','js-support-ticket')); ?></span>
                </div>
            </a>
            <a class="js-admin-report-wrapper" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=userreport')); ?>" >
                <div class="js-admin-user-report-type-wrapper">
                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/report/user.png" />
                    <span class="js-admin-user-report-type-label"><?php echo esc_html(__('User Reports','js-support-ticket')); ?></span>
                </div>
            </a>
        </div>
    </div>
</div>
