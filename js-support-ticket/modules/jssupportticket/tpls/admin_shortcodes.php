<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
    $filepath = JSST_PLUGIN_PATH . 'includes/css/style.php';
    $filestring = file_get_contents($filepath);
    $color1 = JSSTincluder::getJSModel('jssupportticket')->getColorCode($filestring, 1);
    $color3 = JSSTincluder::getJSModel('jssupportticket')->getColorCode($filestring, 3);
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
		                <li><?php echo esc_html(__('Short Codes','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Short Codes', 'js-support-ticket')); ?></h1>
            <a target="blank" href="https://www.youtube.com/watch?v=mN6xsD2u2CI" class="jsstadmin-add-link black-bg button js-cp-video-popup" title="<?php echo esc_html(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                <?php echo esc_html(__('Watch Video','js-support-ticket')); ?>
            </a>
        </div>
    	<div id="jsstadmin-data-wrp" class="p0">
			<div id="jsst-shortcode-wrapper">
				<div class="jsst-shortcode-1"><?php echo esc_html(__('JS Help Desk / JS Support Ticket Control Panel','js-support-ticket')); ?></div>
				<div class="jsst-shortcode-2"><?php echo "[jssupportticket]"; ?></div>
				<div class="jsst-shortcode-3"><?php echo esc_html(__("JS Help Desk / JS Support Ticket main control panel",'js-support-ticket')); ?></div>
			</div>
			<div id="jsst-shortcode-wrapper">
				<div class="jsst-shortcode-1"><?php echo esc_html(__('Add Ticket','js-support-ticket')); ?></div>
				<div class="jsst-shortcode-2"><?php echo "[jssupportticket_addticket]"; ?></div>
				<div class="jsst-shortcode-3"><?php echo esc_html(__("Add new ticket form for both user and agent",'js-support-ticket')); ?></div>
			</div>
			<?php if(in_array('multiform', jssupportticket::$_active_addons)){ ?>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Add Ticket Using Multiform','js-support-ticket')); ?></div>
					<?php 
						$multiforms = jssupportticket::$_data[0]['multiforms'];
						foreach ($multiforms as $multiform) {
						 	echo '<div class="jsst-shortcode-2">
						 		[jssupportticket_addticket_multiform formid='.esc_attr($multiform->id).']';
					 			echo '<span class="jsst-shortcode-name">('.esc_html($multiform->title).'</span>';
						 		if (isset($multiform->departmentname)) {
						 			echo '<span class="jsst-shortcode-dept"> - '.esc_html($multiform->departmentname).')</span>';
						 		} else {
						 			echo '<span class="jsst-shortcode-dept">)</span>';
						 		}
					 		echo '</div>';
						} ?>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Add new ticket form for both user and agent",'js-support-ticket')); ?></div>
				</div>
			<?php } ?>
			<div id="jsst-shortcode-wrapper">
				<div class="jsst-shortcode-1"><?php echo esc_html(__('My Tickets','js-support-ticket')); ?></div>
				<div class="jsst-shortcode-2"><?php echo "[jssupportticket_mytickets]"; ?></div>
				<div class="jsst-shortcode-3"><?php echo esc_html(__("My tickets for both user and agent",'js-support-ticket')); ?></div>
			</div>
			<?php if(in_array('download', jssupportticket::$_active_addons)){ ?>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Downloads','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_downloads]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("List downloads",'js-support-ticket')); ?></div>
				</div>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Latest Downloads','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_downloads_latest]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Show latest downloads. Options",'js-support-ticket')).': text_color="'.esc_attr($color3).'" '. esc_html(__("and",'js-support-ticket')).' background_color="'.esc_attr($color1).'" '. esc_html(__("i.e.",'js-support-ticket')).' [jssupportticket_downloads_latest text_color="'.esc_attr($color3).'" background_color="'.esc_attr($color1).'"]'; ?></div>
				</div>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Popular Downloads','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_downloads_popular]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Show popular downloads. Options",'js-support-ticket')).': text_color="'.esc_attr($color3).'" '. esc_html(__("and",'js-support-ticket')).' background_color="'.esc_attr($color1).'" '. esc_html(__("i.e.",'js-support-ticket')).' [jssupportticket_downloads_popular text_color="'.esc_attr($color3).'" background_color="'.esc_attr($color1).'"]'; ?></div>
				</div>
			<?php } ?>
			<?php if(in_array('knowledgebase', jssupportticket::$_active_addons)){ ?>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Knowledge Base','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_knowledgebase]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("List knowledge base",'js-support-ticket')); ?></div>
				</div>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Latest Knowledge Base','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_knowledgebase_latest]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Show latest knowledge base. Options",'js-support-ticket')).': text_color="'.esc_attr($color3).'" '. esc_html(__("and",'js-support-ticket')).' background_color="'.esc_attr($color1).'" '. esc_html(__("i.e.",'js-support-ticket')).' [jssupportticket_knowledgebase_latest text_color="'.esc_attr($color3).'" background_color="'.esc_attr($color1).'"]'; ?></div>
				</div>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Popular knowledge base','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_knowledgebase_popular]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Show popular knowledge base. Options",'js-support-ticket')).': text_color="'.esc_attr($color3).'" '. esc_html(__("and",'js-support-ticket')).' background_color="'.esc_attr($color1).'" '. esc_html(__("i.e.",'js-support-ticket')).' [jssupportticket_knowledgebase_popular text_color="'.esc_attr($color3).'" background_color="'.esc_attr($color1).'"]'; ?></div>
				</div>
			<?php } ?>
			<?php if(in_array('faq', jssupportticket::$_active_addons)){ ?>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__("FAQ's",'js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_faqs]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("List FAQ's",'js-support-ticket')); ?></div>
				</div>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__("Latest FAQ's",'js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_faqs_latest]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Show latest FAQ's. Options",'js-support-ticket')).': text_color="'.esc_attr($color3).'" '. esc_html(__("and",'js-support-ticket')).' background_color="'.esc_attr($color1).'" '. esc_html(__("i.e.",'js-support-ticket')).' [jssupportticket_faqs_latest text_color="'.esc_attr($color3).'" background_color="'.esc_attr($color1).'"]'; ?></div>
				</div>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__("Popular FAQ's",'js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_faqs_popular]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Show popular FAQ's. Options",'js-support-ticket')).': text_color="'.esc_attr($color3).'" '. esc_html(__("and",'js-support-ticket')).' background_color="'.esc_attr($color1).'" '. esc_html(__("i.e.",'js-support-ticket')).' [jssupportticket_faqs_popular text_color="'.esc_attr($color3).'" background_color="'.esc_attr($color1).'"]'; ?></div>
				</div>
			<?php } ?>
			<?php if(in_array('announcement', jssupportticket::$_active_addons)){ ?>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Announcements','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_announcements]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("List announcements",'js-support-ticket')); ?></div>
				</div>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Latest Announcements','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_announcements_latest]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Show latest announcements. Options",'js-support-ticket')).': text_color="'.esc_attr($color3).'" '. esc_html(__("and",'js-support-ticket')).' background_color="'.esc_attr($color1).'" '. esc_html(__("i.e.",'js-support-ticket')).' [jssupportticket_announcements_latest text_color="'.esc_attr($color3).'" background_color="'.esc_attr($color1).'"]'; ?></div>
				</div>
				<div id="jsst-shortcode-wrapper">
					<div class="jsst-shortcode-1"><?php echo esc_html(__('Popular Announcements','js-support-ticket')); ?></div>
					<div class="jsst-shortcode-2"><?php echo "[jssupportticket_announcements_popular]"; ?></div>
					<div class="jsst-shortcode-3"><?php echo esc_html(__("Show popular announcements. Options",'js-support-ticket')).': text_color="'.esc_attr($color3).'" '. esc_html(__("and",'js-support-ticket')).' background_color="'.esc_attr($color1).'" '. esc_html(__("i.e.",'js-support-ticket')).' [jssupportticket_announcements_popular text_color="'.esc_attr($color3).'" background_color="'.esc_attr($color1).'"]'; ?></div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
