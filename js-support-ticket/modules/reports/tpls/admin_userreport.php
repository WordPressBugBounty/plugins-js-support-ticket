<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css', array(), jssupportticket::$_config['productversion']);
wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js', array(), jssupportticket::$_config['productversion'], true);
wp_register_script( 'ticket-google-charts-handle', '', array(), jssupportticket::$_config['productversion'], true );
wp_enqueue_script( 'ticket-google-charts-handle' );
$jsst_js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
if (in_array('agent', jssupportticket::$_active_addons)){
	$jsst_jstmod = 'agent';
	$jsst_task = 'getusersearchuserreportajax';
	$jsst_searchTask = 'getusersearchuserreportajax';
	$jsst_nonce = wp_create_nonce("get-usersearch-userreport-ajax");
	$jsst_searchNonce = wp_create_nonce("get-usersearch-userreport-ajax");
} else {
	$jsst_jstmod = 'jssupportticket';
	$jsst_task = 'getuserlistajax';
	$jsst_searchTask = 'getusersearchajax';
	$jsst_nonce = wp_create_nonce("get-user-list-ajax");
	$jsst_searchNonce = wp_create_nonce("get-usersearch-ajax");
}
$jsst_jssupportticket_js ='
    function updateuserlist(pagenum){
        jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "'.$jsst_jstmod.'", task: "'.$jsst_task.'",userlimit:pagenum, "_wpnonce":"'. esc_attr($jsst_nonce).'"}, function (data) {
            if(data){
                jQuery("div#userpopup-records").html("");
                jQuery("div#userpopup-records").html(jsstDecodeHTML(data));
                setUserLink();
            }
        });
    }
    function setUserLink() {
        jQuery("a.js-userpopup-link").each(function () {
            var anchor = jQuery(this);
            jQuery(anchor).click(function (e) {
                var id = jQuery(this).attr("data-id");
                var name = jQuery(this).attr("data-username");
                var email = jQuery(this).attr("data-email");
                var displayname = jQuery(this).attr("data-name");
                jQuery("input#username-text").val(name);
                jQuery("input#name").val(displayname);
                jQuery("input#email").val(email);
                jQuery("input#uid").val(id);
                jQuery("div#userpopup").slideUp("slow", function () {
                    jQuery("div#userpopupblack").hide();
                });
            });
        });
    }
    setUserLink();
    jQuery(document).ready(function ($) {
        $(".custom_date").datepicker({
            dateFormat: "'. esc_html($jsst_js_scriptdateformat) .'"
        });
        jQuery("a#userpopup").click(function (e) {
            e.preventDefault();
            jQuery("div#userpopupblack").show();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "'.$jsst_jstmod.'", task: "'.$jsst_task.'", "_wpnonce":"'. esc_attr($jsst_nonce).'"}, function (data) {
                if(data){
                    jQuery("div#userpopup-records").html("");
                    jQuery("div#userpopup-records").html(jsstDecodeHTML(data));
                    setUserLink();
                }
            });
            jQuery("div#userpopup").slideDown("slow");
        });
        jQuery("form#userpopupsearch").submit(function (e) {
            e.preventDefault();
            var username = jQuery("input#username").val();
            var name = jQuery("input#name").val();
            var emailaddress = jQuery("input#emailaddress").val();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", name: name, username: username, emailaddress: emailaddress, jstmod: "'.$jsst_jstmod.'", task: "'.$jsst_searchTask.'", "_wpnonce":"'. esc_attr($jsst_searchNonce) .'"}, function (data) {
                if (data) {
                    jQuery("div#userpopup-records").html(jsstDecodeHTML(data));
                    setUserLink();
                }
            });//jquery closed
        });
        jQuery(".userpopup-close, div#userpopupblack").click(function (e) {
            jQuery("div#userpopup").slideUp("slow", function () {
                jQuery("div#userpopupblack").hide();
            });
        });
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(drawChart);
	});

	function resetFrom(){
		document.getElementById("date_start").value = "";
		document.getElementById("date_end").value = "";
		document.getElementById("uid").value = "";
		document.getElementById("username-text").value = "";
		document.getElementById("jssupportticketform").submit();
	}
	';
    wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
	$jsst_jssupportticket_js ='

	jQuery(document).ready(function ($) {
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(drawChart);
	});

    function drawChart() {
      	var data = new google.visualization.DataTable();
		data.addColumn("date", "'. esc_html(__("Dates","js-support-ticket")) .'");
        data.addColumn("number", "'. esc_html(__("New","js-support-ticket")) .'");
        data.addColumn("number", "'. esc_html(__("Answered","js-support-ticket")) .'");
        data.addColumn("number", "'. esc_html(__("Pending","js-support-ticket")) .'");
        data.addColumn("number", "'. esc_html(__("Overdue","js-support-ticket")) .'");
        data.addColumn("number", "'. esc_html(__("Closed","js-support-ticket")) .'");
		data.addRows([
			'. wp_kses(jssupportticket::$jsst_data["line_chart_json_array"], JSST_ALLOWED_TAGS) .'
        ]);

        var options = {
          colors:["#1EADD8","#179650","#D98E11","#DB624C","#5F3BBB"],
          curveType: "function",
          legend: { position: "bottom" },
          pointSize: 6,
		  // This line will make you select an entire row of data at a time
		  focusTarget: "category",
		  chartArea: {width:"90%",top:50}
		};

        var chart = new google.visualization.LineChart(document.getElementById("curve_chart"));
        chart.draw(data, options);
    }
';
    wp_add_inline_script('ticket-google-charts-handle',$jsst_jssupportticket_js);
?>
<div id="userpopupblack" style="display:none;"></div>
<div id="userpopup" style="display:none;">
    <div class="userpopup-top">
    	<div class="userpopup-heading">
    		<?php echo esc_html(__('Select User','js-support-ticket')); ?>
		</div>
    	<img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
    </div>
    <div class="userpopup-search">
        <form id="userpopupsearch">
            <div class="userpopup-fields-wrp">
                <div class="userpopup-fields">
                    <input type="text" name="username" id="username" placeholder="<?php echo esc_attr(__('Username','js-support-ticket')); ?>" />
                </div>
                <div class="userpopup-fields">
                    <input type="text" name="name" id="name" placeholder="<?php echo esc_attr(__('Name','js-support-ticket')); ?>" />
                </div>
                <div class="userpopup-fields">
                    <input type="text" name="emailaddress" id="emailaddress" placeholder="<?php echo esc_attr(__('Email Address','js-support-ticket')); ?>"/>
                </div>
                <div class="userpopup-btn-wrp">
                    <input class="userpopup-search-btn" type="submit" value="<?php echo esc_attr(__('Search','js-support-ticket')); ?>" />
                    <input class="userpopup-reset-btn" type="submit" onclick="document.getElementById('name').value = '';document.getElementById('username').value = ''; document.getElementById('emailaddress').value = '';" value="<?php echo esc_attr(__('Reset','js-support-ticket')); ?>" />
                </div>
            </div>
        </form>
    </div>
    <div id="userpopup-records-wrp">
	    <div id="userpopup-records">
	        <div class="userpopup-records-desc">
                <?php echo esc_html(__('Use search feature to select the user','js-support-ticket')); ?>
            </div>
	    </div>
    </div>
</div>
<?php JSSTmessage::getMessage(); ?>

<?php
$jsst_t_name = 'getusersexport';
$jsst_link_export = admin_url('admin.php?page=export&task='.$jsst_t_name.'&action=jstask&uid='.jssupportticket::$jsst_data['filter']['uid'].'&date_start='.jssupportticket::$jsst_data['filter']['date_start'].'&date_end='.jssupportticket::$jsst_data['filter']['date_end']);
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
		                <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
		                <li><?php echo esc_html(__('User Reports','js-support-ticket')); ?></li>
		            </ul>
		        </div>
		    </div>
		    <div id="jsstadmin-wrapper-top-right">
		        <div id="jsstadmin-config-btn">
		            <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
		                <img alt = "<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
		            </a>
		        </div>
    	        <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
    	            <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
    	                <img alt = "<?php echo esc_attr(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
    	            </a>
    	        </div>
		        <div id="jsstadmin-vers-txt">
		            <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
		            <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
		        </div>
		    </div>
		</div>
    	<div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("User Reports", 'js-support-ticket')); ?></h1>
            <?php if(in_array('export', jssupportticket::$_active_addons)){ ?>
				<a title="<?php echo esc_attr(__('Export Data','js-support-ticket')); ?>" id="jsexport-link" class="jsstadmin-add-link button" href="<?php echo esc_url($jsst_link_export); ?>"><img alt = "<?php echo esc_attr(__('Export','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/export-icon.png" /><?php echo esc_html(__('Export Data', 'js-support-ticket')); ?></a>
			<?php } ?>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
        	<div class="js-admin-staff-boxes">
				<?php
					$jsst_open_percentage = 0;
					$jsst_close_percentage = 0;
					$jsst_overdue_percentage = 0;
					$jsst_answered_percentage = 0;
					$jsst_pending_percentage = 0;
					if(isset(jssupportticket::$jsst_data['ticket_total']) && isset(jssupportticket::$jsst_data['ticket_total']['allticket']) && jssupportticket::$jsst_data['ticket_total']['allticket'] != 0){
					    $jsst_open_percentage = round((jssupportticket::$jsst_data['ticket_total']['openticket'] / jssupportticket::$jsst_data['ticket_total']['allticket']) * 100);
					    $jsst_close_percentage = round((jssupportticket::$jsst_data['ticket_total']['closeticket'] / jssupportticket::$jsst_data['ticket_total']['allticket']) * 100);
					    $jsst_overdue_percentage = round((jssupportticket::$jsst_data['ticket_total']['overdueticket'] / jssupportticket::$jsst_data['ticket_total']['allticket']) * 100);
					    $jsst_answered_percentage = round((jssupportticket::$jsst_data['ticket_total']['answeredticket'] / jssupportticket::$jsst_data['ticket_total']['allticket']) * 100);
					    $jsst_pending_percentage = round((jssupportticket::$jsst_data['ticket_total']['pendingticket'] / jssupportticket::$jsst_data['ticket_total']['allticket']) * 100);
					}
					if(isset($jsst_dept) && isset(jssupportticket::$jsst_data['ticket_total']['allticket']) && jssupportticket::$jsst_data['ticket_total']['allticket'] != 0){
					    $jsst_allticket_percentage = 100;
					}
				?>
				<div class="js-ticket-count">
				    <div class="js-ticket-link">
				        <a class="js-ticket-link js-ticket-green" href="#" data-tab-number="1" title="<?php echo esc_attr(__('Open Ticket','js-support-ticket')); ?>">
				            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_open_percentage); ?>" data-tab-number="1">
				                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_open_percentage); ?>">
				                    <div class="circle">
				                        <div class="mask full">
				                             <div class="fill js-ticket-open"></div>
				                        </div>
				                        <div class="mask half">
				                            <div class="fill js-ticket-open"></div>
				                            <div class="fill fix"></div>
				                        </div>
				                        <div class="shadow"></div>
				                    </div>
				                    <div class="inset">
				                    </div>
				                </div>
				            </div>
				            <div class="js-ticket-link-text js-ticket-green">
				                <?php
				                    echo esc_html(__('New', 'js-support-ticket'));
				                    echo ' ( '.esc_html(jssupportticket::$jsst_data['ticket_total']['openticket']).' )';
				                ?>
				            </div>
				        </a>
				    </div>
				    <div class="js-ticket-link">
				        <a class="js-ticket-link js-ticket-brown" href="#" data-tab-number="2" title="<?php echo esc_attr(__('answered ticket','js-support-ticket')); ?>">
				            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_answered_percentage); ?>" >
				                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_answered_percentage); ?>">
				                    <div class="circle">
				                        <div class="mask full">
				                             <div class="fill js-ticket-answer"></div>
				                        </div>
				                        <div class="mask half">
				                            <div class="fill js-ticket-answer"></div>
				                            <div class="fill fix"></div>
				                        </div>
				                        <div class="shadow"></div>
				                    </div>
				                    <div class="inset">
				                    </div>
				                </div>
				            </div>
				            <div class="js-ticket-link-text js-ticket-brown">
				                <?php
				                    echo esc_html(__('Answered', 'js-support-ticket'));
				                    echo ' ( '. esc_attr(jssupportticket::$jsst_data['ticket_total']['answeredticket']).' )';
				                ?>
				            </div>
				        </a>
				    </div>
				    <div class="js-ticket-link">
	                    <a class="js-ticket-link js-ticket-blue" href="#" data-tab-number="3" title="<?php echo esc_attr(__('pending ticket','js-support-ticket')); ?>">
	                        <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_pending_percentage); ?>">
	                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_pending_percentage); ?>">
	                                <div class="circle">
	                                    <div class="mask full">
	                                         <div class="fill js-ticket-allticket"></div>
	                                    </div>
	                                    <div class="mask half">
	                                        <div class="fill js-ticket-allticket"></div>
	                                        <div class="fill fix"></div>
	                                    </div>
	                                    <div class="shadow"></div>
	                                </div>
	                                <div class="inset">
	                                </div>
	                            </div>
	                        </div>
	                        <div class="js-ticket-link-text js-ticket-blue">
	                            <?php
	                                echo esc_html(__('Pending', 'js-support-ticket'));
	                                echo ' ( '. esc_html(jssupportticket::$jsst_data['ticket_total']['pendingticket']).' )';
	                            ?>
	                        </div>
	                    </a>
	                </div>
				    <div class="js-ticket-link">
				        <a class="js-ticket-link js-ticket-orange" href="#" data-tab-number="4" title="<?php echo esc_attr(__('overdue ticket','js-support-ticket')); ?>">
				            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_overdue_percentage); ?>" >
				                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_overdue_percentage); ?>">
				                    <div class="circle">
				                        <div class="mask full">
				                             <div class="fill js-ticket-overdue"></div>
				                        </div>
				                        <div class="mask half">
				                            <div class="fill js-ticket-overdue"></div>
				                            <div class="fill fix"></div>
				                        </div>
				                        <div class="shadow"></div>
				                    </div>
				                    <div class="inset">
				                    </div>
				                </div>
				            </div>
				            <div class="js-ticket-link-text js-ticket-orange">
				                <?php
				                    echo esc_html(__('Overdue', 'js-support-ticket'));
				                    echo ' ( '. esc_html(jssupportticket::$jsst_data['ticket_total']['overdueticket']).' )';
				                ?>
				            </div>
				        </a>
				    </div>
				    <div class="js-ticket-link">
				        <a class="js-ticket-link js-ticket-red" href="#" data-tab-number="5" title="<?php echo esc_attr(__('Close Ticket','js-support-ticket')); ?>">
				            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_close_percentage); ?>" >
				                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_close_percentage); ?>">
				                    <div class="circle">
				                        <div class="mask full">
				                             <div class="fill js-ticket-close"></div>
				                        </div>
				                        <div class="mask half">
				                            <div class="fill js-ticket-close"></div>
				                            <div class="fill fix"></div>
				                        </div>
				                        <div class="shadow"></div>
				                    </div>
				                    <div class="inset">
				                    </div>
				                </div>
				            </div>
				            <div class="js-ticket-link-text js-ticket-red">
				                <?php
				                    echo esc_html(__('Closed', 'js-support-ticket'));
				                    echo ' ( '. esc_html(jssupportticket::$jsst_data['ticket_total']['closeticket']).' )';
				                ?>
				            </div>
				        </a>
				    </div>
				</div>
			</div>
		    <form class="js-filter-form js-report-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reports&jstlay=userreport"),"reports")); ?>">
			    <?php
			        $jsst_curdate = date_i18n('Y-m-d');
			        $jsst_enddate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
			        $jsst_date_start = !empty(jssupportticket::$jsst_data['filter']['date_start']) ? jssupportticket::$jsst_data['filter']['date_start'] : $jsst_curdate;
			        $jsst_date_end = !empty(jssupportticket::$jsst_data['filter']['date_end']) ? jssupportticket::$jsst_data['filter']['date_end'] : $jsst_enddate;
			        $jsst_uid = !empty(jssupportticket::$jsst_data['filter']['uid']) ? jssupportticket::$jsst_data['filter']['uid'] : '';
			    	echo wp_kses(JSSTformfield::text('date_start', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_date_start)), array('class' => 'custom_date js-form-date-field js-ticket-input-field','placeholder' => esc_html(__('Start Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::text('date_end', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_date_end)), array('class' => 'custom_date js-form-date-field js-ticket-input-field','placeholder' => esc_html(__('End Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::hidden('uid', $jsst_uid), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS);
				?>
			    <?php if (!empty(jssupportticket::$jsst_data['filter']['username'])) { ?>
			        <div id="username-div"><input type="text" value="<?php echo esc_attr(jssupportticket::$jsst_data['filter']['username']); ?>" id="username-text" class="js-form-input-field" readonly="readonly" data-validation="required"/></div><a href="#" id="userpopup" class="button js-form-reset" title="<?php echo esc_attr(__('Select User', 'js-support-ticket')); ?>"><?php echo esc_html(__('Select User', 'js-support-ticket')); ?></a>
			    <?php } else { ?>
			        <div id="username-div"></div><input type="text" value="" id="username-text" class="js-form-input-field" readonly="readonly" data-validation="required"/><a href="#" id="userpopup" class="button js-form-reset" title="<?php echo esc_attr(__('Select User', 'js-support-ticket')); ?>"><?php echo esc_html(__('Select User', 'js-support-ticket')); ?></a>
			    <?php } ?>
			    <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
				<?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
			</form>
			<div class="js-admin-report">
				<div class="js-admin-subtitle"><?php echo esc_html(__('Overall Report','js-support-ticket')); ?></div>
				<div class="js-admin-rep-graph" id="curve_chart" style="height:400px;width:95%; "></div>
			</div>
			<div class="js-admin-report">
				<div class="js-admin-subtitle"><?php echo esc_html(__('Users','js-support-ticket')); ?></div>
				<div class="js-admin-staff-list">
					<?php
					if(!empty(jssupportticket::$jsst_data['users_report'])){
						foreach(jssupportticket::$jsst_data['users_report'] AS $jsst_agent){ ?>
							<div class="js-admin-staff-wrapper">
								<a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=userdetailreport&id='.$jsst_agent->id.'&date_start='.jssupportticket::$jsst_data['filter']['date_start'].'&date_end='.jssupportticket::$jsst_data['filter']['date_end'])); ?>" class="js-admin-staff-anchor-wrapper" title="<?php echo esc_attr(__('Ticket', 'js-support-ticket')); ?>">
									<div class="js-admin-staff-cnt">
										<div class="js-report-staff-image">
											<?php echo wp_kses(jsst_get_avatar($jsst_agent->id), JSST_ALLOWED_TAGS); ?>
										</div>
										<div class="js-report-staff-cnt">
											<div class="js-report-staff-info js-report-staff-name">
												<?php
													if(isset($jsst_agent->firstname) && isset($jsst_agent->lastname)){
														$jsst_agentname = $jsst_agent->firstname . ' ' . $jsst_agent->lastname;
													}else{
														$jsst_agentname = $jsst_agent->display_name;
													}
													echo esc_html($jsst_agentname);
												?>
											</div>
											<div class="js-report-staff-info js-report-staff-post">
												<?php
													if(isset($jsst_agent->username)){
														$jsst_username = $jsst_agent->username;
													}else{
														$jsst_username = $jsst_agent->user_nicename;
													}
													echo esc_html($jsst_username);
												?>
											</div>
											<div class="js-report-staff-info js-report-staff-email">
												<?php
													if(isset($jsst_agent->email)){
														$jsst_email = $jsst_agent->email;
													}else{
														$jsst_email = $jsst_agent->user_email;
													}
													echo esc_html($jsst_email);
												?>
											</div>
										</div>
									</div>
									<div class="js-admin-staff-boxes">
										<?php
											$jsst_open_percentage = 0;
											$jsst_close_percentage = 0;
											$jsst_overdue_percentage = 0;
											$jsst_answered_percentage = 0;
											$jsst_pending_percentage = 0;
											if(isset($jsst_agent) && isset($jsst_agent->allticket) && $jsst_agent->allticket != 0){
											    $jsst_open_percentage = round(($jsst_agent->openticket / $jsst_agent->allticket) * 100);
											    $jsst_close_percentage = round(($jsst_agent->closeticket / $jsst_agent->allticket) * 100);
											    $jsst_overdue_percentage = round(($jsst_agent->overdueticket / $jsst_agent->allticket) * 100);
											    $jsst_answered_percentage = round(($jsst_agent->answeredticket / $jsst_agent->allticket) * 100);
											    $jsst_pending_percentage = round(($jsst_agent->pendingticket / $jsst_agent->allticket) * 100);
											}
											if(isset($jsst_agent) && isset($jsst_agent->allticket) && $jsst_agent->allticket != 0){
											    $jsst_allticket_percentage = 100;
											}
										?>
										<div class="js-ticket-count">
										    <div class="js-ticket-link">
										        <a class="js-ticket-link js-ticket-green" href="#" data-tab-number="1" title="<?php echo esc_attr(__('Open Ticket', 'js-support-ticket')); ?>">
										            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_open_percentage); ?>" data-tab-number="1">
										                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_open_percentage); ?>">
										                    <div class="circle">
										                        <div class="mask full">
										                             <div class="fill js-ticket-open"></div>
										                        </div>
										                        <div class="mask half">
										                            <div class="fill js-ticket-open"></div>
										                            <div class="fill fix"></div>
										                        </div>
										                        <div class="shadow"></div>
										                    </div>
										                    <div class="inset">
										                    </div>
										                </div>
										            </div>
										            <div class="js-ticket-link-text js-ticket-green">
										                <?php
										                    echo esc_html(__('New', 'js-support-ticket'));
										                    echo ' ( '.esc_html($jsst_agent->openticket).' )';
										                ?>
										            </div>
										        </a>
										    </div>
										    <div class="js-ticket-link">
										        <a class="js-ticket-link js-ticket-brown" href="#" data-tab-number="2" title="<?php echo esc_attr(__('answered ticket', 'js-support-ticket')); ?>">
										            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_answered_percentage); ?>" >
										                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_answered_percentage); ?>">
										                    <div class="circle">
										                        <div class="mask full">
										                             <div class="fill js-ticket-answer"></div>
										                        </div>
										                        <div class="mask half">
										                            <div class="fill js-ticket-answer"></div>
										                            <div class="fill fix"></div>
										                        </div>
										                        <div class="shadow"></div>
										                    </div>
										                    <div class="inset">
										                    </div>
										                </div>
										            </div>
										            <div class="js-ticket-link-text js-ticket-brown">
										                <?php
										                    echo esc_html(__('Answered', 'js-support-ticket'));
										                    echo ' ( '. esc_html($jsst_agent->answeredticket).' )';
										                ?>
										            </div>
										        </a>
										    </div>
										    <div class="js-ticket-link">
							                    <a class="js-ticket-link js-ticket-blue" href="#" data-tab-number="3" title="<?php echo esc_attr(__('pending ticket', 'js-support-ticket')); ?>">
							                        <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_pending_percentage); ?>">
							                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_pending_percentage); ?>">
							                                <div class="circle">
							                                    <div class="mask full">
							                                         <div class="fill js-ticket-allticket"></div>
							                                    </div>
							                                    <div class="mask half">
							                                        <div class="fill js-ticket-allticket"></div>
							                                        <div class="fill fix"></div>
							                                    </div>
							                                    <div class="shadow"></div>
							                                </div>
							                                <div class="inset">
							                                </div>
							                            </div>
							                        </div>
							                        <div class="js-ticket-link-text js-ticket-blue">
							                            <?php
							                                echo esc_html(__('Pending', 'js-support-ticket'));
							                                echo ' ( '. esc_html($jsst_agent->pendingticket).' )';
							                            ?>
							                        </div>
							                    </a>
							                </div>
										    <div class="js-ticket-link">
										        <a class="js-ticket-link js-ticket-orange" href="#" data-tab-number="4" title="<?php echo esc_attr(__('overdue ticket', 'js-support-ticket')); ?>">
										            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_overdue_percentage); ?>" >
										                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_overdue_percentage); ?>">
										                    <div class="circle">
										                        <div class="mask full">
										                             <div class="fill js-ticket-overdue"></div>
										                        </div>
										                        <div class="mask half">
										                            <div class="fill js-ticket-overdue"></div>
										                            <div class="fill fix"></div>
										                        </div>
										                        <div class="shadow"></div>
										                    </div>
										                    <div class="inset">
										                    </div>
										                </div>
										            </div>
										            <div class="js-ticket-link-text js-ticket-orange">
										                <?php
										                    echo esc_html(__('Overdue', 'js-support-ticket'));
										                    echo ' ( '. esc_html($jsst_agent->overdueticket).' )';
										                ?>
										            </div>
										        </a>
										    </div>
										    <div class="js-ticket-link">
										        <a class="js-ticket-link js-ticket-red" href="#" data-tab-number="5" title="<?php echo esc_attr(__('Close Ticket', 'js-support-ticket')); ?>">
										            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_close_percentage); ?>" >
										                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_close_percentage); ?>">
										                    <div class="circle">
										                        <div class="mask full">
										                             <div class="fill js-ticket-close"></div>
										                        </div>
										                        <div class="mask half">
										                            <div class="fill js-ticket-close"></div>
										                            <div class="fill fix"></div>
										                        </div>
										                        <div class="shadow"></div>
										                    </div>
										                    <div class="inset">
										                    </div>
										                </div>
										            </div>
										            <div class="js-ticket-link-text js-ticket-red">
										                <?php
										                    echo esc_html(__('Closed', 'js-support-ticket'));
										                    echo ' ( '. esc_html($jsst_agent->closeticket).' )';
										                ?>
										            </div>
										        </a>
										    </div>
										</div>
									</div>
								</a>
							</div>
							<?php
						}
					} else {
						JSSTlayout::getNoRecordFound();
					}?>
				</div>
			</div>
			<?php
			if(!empty(jssupportticket::$jsst_data['users_report'])){
			    if (jssupportticket::$jsst_data[1]) {
			        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
			    }
			}
			?>
		</div>
	</div>
</div>
