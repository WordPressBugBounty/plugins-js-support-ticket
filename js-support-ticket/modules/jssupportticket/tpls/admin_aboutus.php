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
    	                <li><?php echo esc_html(__('About Us','js-support-ticket')); ?></li>
    	            </ul>
    	        </div>
    	    </div>
    	    <div id="jsstadmin-wrapper-top-right">
    	        <div id="jsstadmin-config-btn">
    	            <a href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>" title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>">
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('About Us','js-support-ticket')); ?></h1>
        </div>
    	<div id="jsstadmin-data-wrp" class="p0">
    		<div class="jssst-admin-about-us">
				<div class="js-admin-heading">
					<span class="js-admin-head-txt">
						<?php echo esc_html(__('Plugin Detail','js-support-ticket')); ?>
					</span>
				</div>
				<div class="jssst-admin-about-us-cnt">
					<div class="jssst-admin-about-author">
						<div class="jssst-author-tit">
							<?php echo esc_html(__('Plugin for online JS Help Desk System','js-support-ticket')); ?>
						</div>
						<div class="jssst-author-cnt">
							<div class="jssst-author-info">
								<span class="jssst-auth-info-title"><?php echo esc_html(__('Created By','js-support-ticket')); ?></span>
								<span class="jssst-auth-info-value">Ahmad Bilal</span>
							</div>
							<div class="jssst-author-info">
								<span class="jssst-auth-info-title"><?php echo esc_html(__('Company','js-support-ticket')); ?></span>
								<span class="jssst-auth-info-value">JoomSky</span>
							</div>
							<div class="jssst-author-info">
								<span class="jssst-auth-info-title"><?php echo esc_html(__('Plugin Name','js-support-ticket')); ?></span>
								<span class="jssst-auth-info-value"><?php echo esc_html(__('JS Help Desk','js-support-ticket')); ?></span>
							</div>
						</div>
					</div>
					<div class="jssst-admin-author-prdct">
						<a href="https://www.joomsky.com/products/js-jobs-pro-wp.html" target="_blank" class="jssst-admin-author-prdct-item" title="<?php echo esc_html(__('job plugin','js-support-ticket')); ?>">
							<img alt="<?php echo esc_html(__('job plugin','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/aboutus_page/job-plugin.jpg" />
						</a>
					</div>
					<div class="jssst-admin-author-prdct">
						<a href="https://www.joomsky.com/products/js-vehicle-manager-pro-wp.html" class="jssst-admin-author-prdct-item" title="<?php echo esc_html(__('vehicle manager','js-support-ticket')); ?>">
							<img alt="<?php echo esc_html(__('vehicle manager','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/aboutus_page/vehicle-manager.jpg" />
						</a>
					</div>
					<div class="jssst-admin-author-prdct">
						<a href="https://www.joomsky.com/products/js-learn-manager-pro-wp.html" class="jssst-admin-author-prdct-item" title="<?php echo esc_html(__('lms plugin','js-support-ticket')); ?>">
							<img alt="<?php echo esc_html(__('lms plugin','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/aboutus_page/lms.jpg" />
						</a>
					</div>
					<div class="jssst-admin-author-prdct">
						<a href="https://themeforest.net/item/car-manager-car-dealership-business-wordpress-theme/19350332" class="jssst-admin-author-prdct-item" title="<?php echo esc_html(__('car manager','js-support-ticket')); ?>">
							<img alt="<?php echo esc_html(__('car manager','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/aboutus_page/car-manager.jpg" />
						</a>
					</div>
					<div class="jssst-admin-author-prdct">
						<a href="https://www.joomsky.com/products/js-jobs/job-manager-theme.html" class="jssst-admin-author-prdct-item" title="<?php echo esc_html(__('job manager','js-support-ticket')); ?>">
							<img alt="<?php echo esc_html(__('job manager','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/aboutus_page/job-manager.jpg" />
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
