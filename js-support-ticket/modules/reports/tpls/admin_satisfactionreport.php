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
    	                <li><?php echo esc_html(__('Satisfaction Report','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("Satisfaction Report", 'js-support-ticket')); ?></h1>
        </div>
    	<?php
		$percentage = round(jssupportticket::$_data[0]['avg']*20,2);
		?>
		<div id="jsstadmin-data-wrp">
			<div class="jsst-statifacetion-report-wrapper" >
				<div class="statifacetion-report-left" >
					<?php
						$class="first";
						$src ="excelent.png";
						if($percentage > 80){
							$class="first";
							$src ="excelent.png";
						}elseif($percentage > 60){
							$class="second";
							$src ="happy.png";
						}elseif($percentage > 40){
							$class="third";
							$src ="normal.png";
						}elseif($percentage > 20){
							$class="fourth";
							$src ="bad.png";
						}elseif($percentage > 0){
							$class="fifth";
							$src ="angery.png";
						}

						?>
					<div class="top-number <?php echo esc_attr($class);?>" >
						<?php echo esc_html($percentage).'%'; ?>
					</div>
					<span class="total-feedbacks" >
						<?php echo esc_html(__('Based on','js-support-ticket')).'&nbsp;'. esc_html(jssupportticket::$_data[0]['result'][6]).'&nbsp;'. esc_html(__('Feedbacks','js-support-ticket'));?>
					</span>
					<div class="top-text" >
						<?php echo esc_html(__('Customer Satisfaction','js-support-ticket')); ?>
					</div>
				</div>

				<div class="satisfaction-report-right <?php echo esc_attr($class); ?>" >
					<img alt="<?php echo esc_html(__('satisfaction image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/<?php echo esc_attr($src);?>" />
				</div>




				<div class="jsst-satisfaction-report-bottom" >
					<div class="indi-stats first" >
						<img alt="<?php echo esc_html(__('Excellent','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/excelent.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$_data[0]['result'][5]/jssupportticket::$_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Excellent','js-support-ticket')); ?> </div>
					</div>
					<div class="indi-stats second" >
						<img alt="<?php echo esc_html(__('Happy','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/happy.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$_data[0]['result'][4]/jssupportticket::$_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Happy','js-support-ticket')); ?> </div>
					</div>
					<div class="indi-stats third" >
						<img alt="<?php echo esc_html(__('Normal','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/normal.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$_data[0]['result'][3]/jssupportticket::$_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Normal','js-support-ticket')); ?> </div>
					</div>
					<div class="indi-stats fourth" >
						<img alt="<?php echo esc_html(__('bad','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/bad.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$_data[0]['result'][2]/jssupportticket::$_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Sad','js-support-ticket')); ?> </div>
					</div>
					<div class="indi-stats fifth" >
						<img alt="<?php echo esc_html(__('Angry','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/angery.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$_data[0]['result'][1]/jssupportticket::$_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Angry','js-support-ticket')); ?> </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
