<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
     <?php JSSTlayout::adminPageHeader(array(
         'title' => __("Satisfaction Report", 'js-support-ticket'),
     )); ?>
    	<?php
		$jsst_percentage = round(jssupportticket::$jsst_data[0]['avg']*20,2);
		?>
		<div id="jsstadmin-data-wrp">
			<div class="jsst-statifacetion-report-wrapper" >
				<div class="statifacetion-report-left" >
					<?php
						$jsst_class="first";
						$jsst_src ="excelent.png";
						if($jsst_percentage > 80){
							$jsst_class="first";
							$jsst_src ="excelent.png";
						}elseif($jsst_percentage > 60){
							$jsst_class="second";
							$jsst_src ="happy.png";
						}elseif($jsst_percentage > 40){
							$jsst_class="third";
							$jsst_src ="normal.png";
						}elseif($jsst_percentage > 20){
							$jsst_class="fourth";
							$jsst_src ="bad.png";
						}elseif($jsst_percentage > 0){
							$jsst_class="fifth";
							$jsst_src ="angery.png";
						}

						?>
					<div class="top-number <?php echo esc_attr($jsst_class);?>" >
						<?php echo esc_html($jsst_percentage).'%'; ?>
					</div>
					<span class="total-feedbacks" >
						<?php echo esc_html(__('Based on','js-support-ticket')).'&nbsp;'. esc_html(jssupportticket::$jsst_data[0]['result'][6]).'&nbsp;'. esc_html(__('Feedback','js-support-ticket'));?>
					</span>
					<div class="top-text" >
						<?php echo esc_html(__('Customer Satisfaction','js-support-ticket')); ?>
					</div>
				</div>

				<div class="satisfaction-report-right <?php echo esc_attr($jsst_class); ?>" >
					<img alt = "<?php echo esc_attr(__('satisfaction image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/<?php echo esc_attr($jsst_src);?>" />
				</div>




				<div class="jsst-satisfaction-report-bottom" >
					<div class="indi-stats first" >
						<img alt = "<?php echo esc_attr(__('Excellent','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/excelent.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$jsst_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$jsst_data[0]['result'][5]/jssupportticket::$jsst_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Excellent','js-support-ticket')); ?> </div>
					</div>
					<div class="indi-stats second" >
						<img alt = "<?php echo esc_attr(__('Happy','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/happy.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$jsst_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$jsst_data[0]['result'][4]/jssupportticket::$jsst_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Happy','js-support-ticket')); ?> </div>
					</div>
					<div class="indi-stats third" >
						<img alt = "<?php echo esc_attr(__('Normal','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/normal.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$jsst_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$jsst_data[0]['result'][3]/jssupportticket::$jsst_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Normal','js-support-ticket')); ?> </div>
					</div>
					<div class="indi-stats fourth" >
						<img alt = "<?php echo esc_attr(__('bad','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/bad.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$jsst_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$jsst_data[0]['result'][2]/jssupportticket::$jsst_data[0]['result'][6]*100 ,2).'%');
							}else{
								echo esc_html(__('NA','js-support-ticket'));
							}
							?></div>
						<div class="stats-text" > <?php echo esc_html(__('Sad','js-support-ticket')); ?> </div>
					</div>
					<div class="indi-stats fifth" >
						<img alt = "<?php echo esc_attr(__('Angry','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/angery.png" />
						<div class="stats-percentage" ><?php
							if(jssupportticket::$jsst_data[0]['result'][6] != 0){
								echo esc_html(round(jssupportticket::$jsst_data[0]['result'][1]/jssupportticket::$jsst_data[0]['result'][6]*100 ,2).'%');
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
