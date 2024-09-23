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
    	                <li><?php echo esc_html(__('Help','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Help','js-support-ticket')); ?></h1>
        </div>
    	<div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
    		<!-- help page -->
    		<div class="jssticketadmin-help-top">
    			<div class="jssticketadmin-help-top-left">
    				<div class="jssticketadmin-help-top-left-cnt-img">
    					<img alt="<?php echo esc_html(__('Help icon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/support-icon.jpg" />
    				</div>
    				<div class="jssticketadmin-help-top-left-cnt-info">
    					<h2><?php echo esc_html(__('We are here to help you','js-support-ticket')); ?></h2>
    					<p><?php echo esc_html(__('JS Help Desk is a professional, simple, easy to use and complete customer support system.','js-support-ticket')); ?></p>
    					<a href="https://www.youtube.com/channel/UCTZ5RPtOzGcsRwRbOTjypmA" target="_blank" class="jssticketadmin-help-top-middle-action" title="<?php echo esc_html(__('View all videos','js-support-ticket')); ?>"><img alt="<?php echo esc_html(__('Video icon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/play-icon.jpg" /><?php echo esc_html(__('View All Videos','js-support-ticket')); ?></a>
    				</div>
    			</div>
    			<div class="jssticketadmin-help-top-right">
    				<div class="jssticketadmin-help-top-right-cnt-img">
    					<img alt="<?php echo esc_html(__('Help Desk icon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/support.png" />
    				</div>
    				<div class="jssticketadmin-help-top-right-cnt-info">
    					<h2><?php echo esc_html(__('JS Help Desk Support','js-support-ticket')); ?></h2>
    					<p><?php echo esc_html(__("JS Help Desk delivers timely customer support if you have any query then we're here to show you the way.",'js-support-ticket')); ?></p>
    					<a target="_blank" href="https://jshelpdesk.com/support/" class="jssticketadmin-help-top-middle-action second" title="<?php echo esc_html(__('Submit ticket','js-support-ticket')); ?>"><img alt="<?php echo esc_html(__('Video icon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/ticket.png" /><?php echo esc_html(__('Submit Ticket','js-support-ticket')); ?></a>
    				</div>
    			</div>
    		</div>
    		<div class="jssticketadmin-help-btm">
    			<!-- tickets -->
    			<div class="jssticketadmin-help-btm-wrp">
    				<h2 class="jssticketadmin-help-btm-title"><?php echo esc_html(__('Tickets','js-support-ticket')); ?></h2>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=zmQ4bpqSYnk" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Ticket Creation','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('Ticket Creation','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=Gcss-ybwiXk" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('Visitor ticket creation','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=Yi3zPvGdGG4" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to set ticket auto close','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to set ticket auto close','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=S7KWbUHvmmk" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to reopen closed ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to reopen closed ticket','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=Z8_9tIve4Mg" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to lock a ticket','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=p3vT2vhSkjk" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to add private note','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to add private note','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=SW9b9lBthbc" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('View ticket history','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('View ticket history','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=c7whQ6F70yM" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to setup custom fields','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to setup custom fields','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=CQRgkw3e5KQ" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Set ticket auto overdue','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('Set ticket auto overdue','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=xziaXK3DKCM" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Manually set ticket overdue','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('Manually set ticket overdue','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=HnnJTe6lYc4" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to merge tickets','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to merge tickets','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=Q8GhQQmeMU4" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to export tickets','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to export tickets','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=-eh4XuDwXoY" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How use help topic','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How use help topic','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=hewCQ0S37V8" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to change department','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to change department','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=gmI25bv5cGA" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to use multi-forms','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to use multi-forms','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=3ndoMZ760Fk" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to paid support','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to paid support','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=RBbmVEkE14E" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to use canned response','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to use canned response','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=iKslva_FkTg" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to add private credentials','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to add private credentials','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=yZi_BRyAQl8" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to ban/unban user','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to ban/unban user','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>	
    			</div>
                <!-- agents -->
                <div class="jssticketadmin-help-btm-wrp">
                    <h2 class="jssticketadmin-help-btm-title"><?php echo esc_html(__('Agents','js-support-ticket')); ?></h2>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=hOvN-_6Qf8g" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Agent system','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('Agent system','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=N7JF1qEVRhQ" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('Agent Auto Assign','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=ZtCivvtAURU" class="jssticketadmin-help-btm-link"  target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Manually assign ticket to agen','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('Manually assign ticket to agent','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                            <a href="https://www.youtube.com/watch?v=1J0JSXrr1hY" class="jssticketadmin-help-btm-link" target="_blank">
                                <div class="jssticketadmin-help-btm-cnt-img">
                                    <img alt="<?php echo esc_html(__('How to edit time','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                                </div>
                                <div class="jssticketadmin-help-btm-cnt-title">
                                    <span><?php echo esc_html(__('How to edit time','js-support-ticket')); ?></span>
                                </div>
                            </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=oSzJz9FDzsY" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to use time tracking','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to use time tracking','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                </div>
    			<!-- configurations -->
    			<div class="jssticketadmin-help-btm-wrp">
    				<h2 class="jssticketadmin-help-btm-title"><?php echo esc_html(__('Configurations','js-support-ticket')); ?></h2>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=SJjHk50buw0" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to set max open ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to set max open ticket','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
    				<div class="jssticketadmin-help-btm-cnt">
    					<a href="https://www.youtube.com/watch?v=9ORIFf6jPPg" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to show counts','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to show counts','js-support-ticket')); ?></span>
                            </div>
    					</a>
    				</div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=-78pMXbZy8o" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to set Captcha','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to set Captcha','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
    				<div class="jssticketadmin-help-btm-cnt">
    					<a href="https://www.youtube.com/watch?v=T3HRojY2UN4" class="jssticketadmin-help-btm-link" target="_blank">
                             <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('User options','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('User options','js-support-ticket')); ?></span>
                            </div>
    					</a>
    				</div>
    				<div class="jssticketadmin-help-btm-cnt">
    					<a href="https://www.youtube.com/watch?v=Hq1UzmUqFIA" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to set login redirect','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to set login redirect','js-support-ticket')); ?></span>
                            </div>
    					</a>
    				</div>
    				<div class="jssticketadmin-help-btm-cnt">
    					<a href="https://www.youtube.com/watch?v=qloE9WQM4rE" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to set fields ordering','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to set fields ordering','js-support-ticket')); ?></span>
                            </div>
    					</a>
    				</div>
    				<div class="jssticketadmin-help-btm-cnt">
    					<a href="https://www.youtube.com/watch?v=jyM4iW8uROY" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to enable social login','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to enable social login','js-support-ticket')); ?></span>
                            </div>
    					</a>
    				</div>
    			</div>
    			<!-- setup -->
    			<div class="jssticketadmin-help-btm-wrp">
    				<h2 class="jssticketadmin-help-btm-title"><?php echo esc_html(__('Setup','js-support-ticket')); ?></h2>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=Honmzw892ZE" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to setup','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to setup','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=6qjMe1Ppbck" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to enable email piping','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to enable email piping','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=4_wrnx8ka0E" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to set SMTP','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to set SMTP','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=LvsrMtEqRms" class="jssticketadmin-help-btm-link" target="_blank">
                             <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to solve email notification problem','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=Nnu2iJQ99Tk" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to translate','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to translate','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
    				<div class="jssticketadmin-help-btm-cnt">
    					<a href="https://www.youtube.com/watch?v=oOOr869FOyA" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to set colors','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to set colors','js-support-ticket')); ?></span>
                            </div>
    					</a>
    				</div>
    				<div class="jssticketadmin-help-btm-cnt">
    					<a href="https://www.youtube.com/watch?v=mN6xsD2u2CI" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to add Shortcode','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to add Shortcode','js-support-ticket')); ?></span>
                            </div>
    					</a>
    				</div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=K0K6vEANnRU" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to install addons','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to install addons','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=sQwVewHk9Lg" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to Desktop Notifications','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to Desktop Notifications','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
    				<div class="jssticketadmin-help-btm-cnt">
    					<a href="https://www.youtube.com/watch?v=qloE9WQM4rE" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to set fields ordering','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to set fields ordering','js-support-ticket')); ?></span>
                            </div>
    					</a>
    				</div>
    			</div>
    			<!-- knowledge-base,downloads,announcements,FAQ -->
                <div class="jssticketadmin-help-btm-wrp jssticketadmin-help-sub-category">
                    <h2 class="jssticketadmin-help-btm-title"><?php echo esc_html(__('Knowledgebase','js-support-ticket')).', '. esc_html(__('Downloads','js-support-ticket')).', '. esc_html(__('Announcements','js-support-ticket')).', '. esc_html(__('FAQs','js-support-ticket')); ?></h2>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=sQBflPjxPEw" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to use knowledge base','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to use knowledge base','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=6-WfiCXB0ZM" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to use downloads','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to use downloads','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=XhWXu2RlFds" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to add announcement','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to add announcement','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=lF58MTzV2aQ" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to create FAQ','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to create FAQ','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                </div>
                <!-- misc -->
                <div class="jssticketadmin-help-btm-wrp">
                    <h2 class="jssticketadmin-help-btm-title"><?php echo esc_html(__('Misc','js-support-ticket')); ?></h2>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=kiNyGRqXtAs" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to use email cc','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to use email cc','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=a5eXxHLB7qU" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to use internal mail','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to use internal mail','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=pdIRcBgtxjw" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('Use front-end widgets','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('Use front-end widgets','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="jssticketadmin-help-btm-cnt">
                        <a href="https://www.youtube.com/watch?v=t0VUBYDmKpU" class="jssticketadmin-help-btm-link" target="_blank">
                            <div class="jssticketadmin-help-btm-cnt-img">
                                <img alt="<?php echo esc_html(__('How to enable admin widgets','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help-page/video-icon.jpg" />
                            </div>
                            <div class="jssticketadmin-help-btm-cnt-title">
                                <span><?php echo esc_html(__('How to enable admin widgets','js-support-ticket')); ?></span>
                            </div>
                        </a>
                    </div>
                </div>
    		</div>
		</div>
	</div>
</div>
