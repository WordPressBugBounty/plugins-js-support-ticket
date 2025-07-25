<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css');
wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css');
wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js');
wp_register_script( 'ticket-google-charts-handle', '' );
wp_enqueue_script( 'ticket-google-charts-handle' );
?>
<?php JSSTmessage::getMessage(); ?>
<?php $formdata = JSSTformfield::getFormData(); ?>
<?php
$js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
$jssupportticket_js ='
    function updateuserlist(pagenum){
        jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "agent", task: "getusersearchstaffreportajax",userlimit:pagenum, "_wpnonce":"'. esc_attr(wp_create_nonce("get-usersearch-staffreport-ajax")).'"}, function (data) {
            if(data){
                jQuery("div#records").html("");
                jQuery("div#records").html(jsstDecodeHTML(data));
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
                jQuery("input#username-text").val(name);
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
            dateFormat: "'. esc_html($js_scriptdateformat).'"
        });
        jQuery("a#userpopup").click(function (e) {
            e.preventDefault();
            jQuery("div#userpopupblack").show();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "agent", task: "getusersearchstaffreportajax", "_wpnonce":"'. esc_attr(wp_create_nonce("get-usersearch-staffreport-ajax")) .'"}, function (data) {
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
            jQuery.post(ajaxurl, {action: "jsticket_ajax", name: name, username: username, emailaddress: emailaddress, jstmod: "agent", task: "getusersearchstaffreportajax", "_wpnonce":"'. esc_attr(wp_create_nonce("get-usersearch-staffreport-ajax")).'"}, function (data) {
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
	});

	function resetFrom(){
		document.getElementById("date_start").value = "";
		document.getElementById("date_end").value = "";
		document.getElementById("uid").value = "";
		document.getElementById("username-text").value = "";
		document.getElementById("jssupportticketform").submit();
	}
';
wp_add_inline_script('ticket-google-charts-handle',$jssupportticket_js);
$jssupportticket_js ='
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
			'. wp_kses(jssupportticket::$_data["line_chart_json_array"], JSST_ALLOWED_TAGS).'
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
	function resizeCharts () {
	    // redraw charts, dashboards, etc here
	    chart.draw(data, options);
	}
	jQuery(window).resize(resizeCharts);
';
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
<div id="userpopupblack" style="display:none;"></div>
<div id="userpopup" style="display:none;">
	<div class="userpopup-top">
	    <div class="userpopup-heading">
	    	<?php echo esc_html(__('Select user','js-support-ticket')); ?>
	    </div>
	    <img alt="<?php echo esc_html(__('Close','js-support-ticket')); ?>" class="userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
    </div>
    <div class="userpopup-search">
        <form id="userpopupsearch">
            <div class="userpopup-fields-wrp">
                <div class="userpopup-fields">
                    <input type="text" name="username" id="username" placeholder="<?php echo esc_html(__('Username','js-support-ticket')); ?>" />
                </div>
                <div class="userpopup-fields">
                    <input type="text" name="name" id="name" placeholder="<?php echo esc_html(__('Name','js-support-ticket')); ?>" />
                </div>
                <div class="userpopup-fields">
                    <input type="text" name="emailaddress" id="emailaddress" placeholder="<?php echo esc_html(__('Email Address','js-support-ticket')); ?>"/>
                </div>
                <div class="userpopup-btn-wrp">
                    <input class="userpopup-search-btn" type="submit" value="<?php echo esc_html(__('Search','js-support-ticket')); ?>" />
                    <input class="userpopup-reset-btn" type="submit" onclick="document.getElementById('name').value = '';document.getElementById('username').value = ''; document.getElementById('emailaddress').value = '';" value="<?php echo esc_html(__('Reset','js-support-ticket')); ?>" />
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
$t_name = 'getstaffmemberexport';
$link_export = admin_url('admin.php?page=export&task='.esc_attr($t_name).'&action=jstask&uid='.esc_attr(jssupportticket::$_data['filter']['uid']).'&date_start='.esc_attr(jssupportticket::$_data['filter']['date_start']).'&date_end='.esc_attr(jssupportticket::$_data['filter']['date_end']));
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
    	                <li><?php echo esc_html(__('Agent Reports','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("Agent Reports", 'js-support-ticket')); ?></h1>
    		<?php if(in_array('export', jssupportticket::$_active_addons)){ ?>
				<a title="<?php echo esc_html(__('Export Data', 'js-support-ticket')); ?>" id="jsexport-link" class="jsstadmin-add-link button" href="<?php echo esc_url($link_export); ?>"><img alt="<?php echo esc_html(__('Export','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/export-icon.png" /><?php echo esc_html(__('Export Data', 'js-support-ticket')); ?></a>
			<?php } ?>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
        	<div class="js-admin-staff-boxes">
				<?php
					$open_percentage = 0;
					$close_percentage = 0;
					$overdue_percentage = 0;
					$answered_percentage = 0;
					$pending_percentage = 0;
					if(isset(jssupportticket::$_data['ticket_total']) && isset(jssupportticket::$_data['ticket_total']['allticket']) && jssupportticket::$_data['ticket_total']['allticket'] != 0){
					    $open_percentage = round((jssupportticket::$_data['ticket_total']['openticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
					    $close_percentage = round((jssupportticket::$_data['ticket_total']['closeticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
					    $overdue_percentage = round((jssupportticket::$_data['ticket_total']['overdueticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
					    $answered_percentage = round((jssupportticket::$_data['ticket_total']['answeredticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
					    $pending_percentage = round((jssupportticket::$_data['ticket_total']['pendingticket'] / jssupportticket::$_data['ticket_total']['allticket']) * 100);
					}
					if(isset(jssupportticket::$_data['ticket_total']) && isset(jssupportticket::$_data['ticket_total']['allticket']) && jssupportticket::$_data['ticket_total']['allticket'] != 0){
					    $allticket_percentage = 100;
					}
				?>
				<div class="js-ticket-count">
				    <div class="js-ticket-link">
				        <a class="js-ticket-link js-ticket-green" href="#" data-tab-number="1" title="<?php echo esc_html(__('Open Ticket', 'js-support-ticket')); ?>">
				            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($open_percentage); ?>" data-tab-number="1">
				                <div class="js-mr-rp" data-progress="<?php echo esc_attr($open_percentage); ?>">
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
				                    echo esc_html(__('Open', 'js-support-ticket'));
				                    echo ' ( '.esc_html(jssupportticket::$_data['ticket_total']['openticket']).' )';
				                ?>
				            </div>
				        </a>
				    </div>
				    <div class="js-ticket-link">
				        <a class="js-ticket-link js-ticket-brown" href="#" data-tab-number="2" title="<?php echo esc_html(__('answered ticket', 'js-support-ticket')); ?>">
				            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($answered_percentage); ?>" >
				                <div class="js-mr-rp" data-progress="<?php echo esc_attr($answered_percentage); ?>">
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
				                    echo ' ( '. esc_html(jssupportticket::$_data['ticket_total']['answeredticket']).' )';
				                ?>
				            </div>
				        </a>
				    </div>
				    <div class="js-ticket-link">
	                    <a class="js-ticket-link js-ticket-blue" href="#" data-tab-number="3" title="<?php echo esc_html(__('pending ticket', 'js-support-ticket')); ?>">
	                        <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($pending_percentage); ?>">
	                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($pending_percentage); ?>">
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
	                                echo ' ( '. esc_html(jssupportticket::$_data['ticket_total']['pendingticket']).' )';
	                            ?>
	                        </div>
	                    </a>
	                </div>
				    <div class="js-ticket-link">
				        <a class="js-ticket-link js-ticket-orange" href="#" data-tab-number="4" title="<?php echo esc_html(__('overdue ticket', 'js-support-ticket')); ?>">
				            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($overdue_percentage); ?>" >
				                <div class="js-mr-rp" data-progress="<?php echo esc_attr($overdue_percentage); ?>">
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
				                    echo ' ( '. esc_html(jssupportticket::$_data['ticket_total']['overdueticket']).' )';
				                ?>
				            </div>
				        </a>
				    </div>
				    <div class="js-ticket-link">
				        <a class="js-ticket-link js-ticket-red" href="#" data-tab-number="5" title="<?php echo esc_html(__('Close Ticket', 'js-support-ticket')); ?>">
				            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($close_percentage); ?>" >
				                <div class="js-mr-rp" data-progress="<?php echo esc_attr($close_percentage); ?>">
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
				                    echo ' ( '. esc_html(jssupportticket::$_data['ticket_total']['closeticket']).' )';
				                ?>
				            </div>
				        </a>
				    </div>
				</div>
			</div>
	    	<form class="js-filter-form js-report-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reports&jstlay=staffreport"),"reports")); ?>">
			    <?php
			        $curdate = date_i18n('Y-m-d');
			        $enddate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
			        $date_start = !empty(jssupportticket::$_data['filter']['date_start']) ? jssupportticket::$_data['filter']['date_start'] : $curdate;
			        $date_end = !empty(jssupportticket::$_data['filter']['date_end']) ? jssupportticket::$_data['filter']['date_end'] : $enddate;
			        $uid = !empty(jssupportticket::$_data['filter']['uid']) ? jssupportticket::$_data['filter']['uid'] : '';
			    	echo wp_kses(JSSTformfield::text('date_start', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($date_start)), array('class' => 'custom_date js-form-date-field','placeholder' => esc_html(__('Start Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::text('date_end', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($date_end)), array('class' => 'custom_date js-form-date-field','placeholder' => esc_html(__('End Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::hidden('uid', $uid), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS);
				?>
			    <?php if (!empty(jssupportticket::$_data['filter']['staffname'])) { ?>
			        <div id="username-div"><input class="js-form-input-field" type="text" value="<?php echo esc_attr(jssupportticket::$_data['filter']['staffname']); ?>" id="username-text" readonly="readonly" data-validation="required"/></div><a href="#" id="userpopup" class="button js-form-reset" title="<?php echo esc_html(__('Select User', 'js-support-ticket')); ?>"><?php echo esc_html(__('Select User', 'js-support-ticket')); ?></a>
			    <?php } else { ?>
			        <div id="username-div"></div><input class="js-form-input-field" type="text" value="" id="username-text" readonly="readonly" data-validation="required"/><a href="#" id="userpopup" class="button js-form-reset" title="<?php echo esc_html(__('Select User', 'js-support-ticket')); ?>"><?php echo esc_html(__('Select User', 'js-support-ticket')); ?></a>
			    <?php } ?>
			    <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
				<?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
			</form>
			<div class="js-admin-report">
				<div class="js-admin-subtitle"><?php echo esc_html(__('Overall Report','js-support-ticket')); ?></div>
				<div class="js-admin-rep-graph" id="curve_chart" style="height:400px;width:98%; "></div>
			</div>
			<div class="js-admin-report">
				<div class="js-admin-subtitle"><?php echo esc_html(__('Agents','js-support-ticket')); ?></div>
				<div class="js-admin-staff-list">
				<?php
				if(!empty(jssupportticket::$_data['staffs_report'])){
					foreach(jssupportticket::$_data['staffs_report'] AS $agent){ ?>
						<div class="js-admin-staff-wrapper">
							<a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=staffdetailreport&id='.esc_attr($agent->id).'&date_start='.jssupportticket::$_data['filter']['date_start'].'&date_end='.jssupportticket::$_data['filter']['date_end'])); ?>" class="js-admin-staff-anchor-wrapper" title="<?php echo esc_html(__('Staff', 'js-support-ticket')); ?>">
								<div class="js-admin-staff-cnt">
									<div class="js-report-staff-image">
										<?php
											if($agent->photo){
												$maindir = wp_upload_dir();
												$path = $maindir['baseurl'];

												$imageurl = $path."/".jssupportticket::$_config['data_directory']."/staffdata/staff_".esc_attr($agent->id)."/".esc_attr($agent->photo);
											}else{
												$imageurl = JSST_PLUGIN_URL."includes/images/user.png";
											}
										?>
										<img alt="<?php echo esc_html(__('staff image', 'js-support-ticket')); ?>" class="js-report-staff-pic" src="<?php echo esc_url($imageurl); ?>" />
									</div>
									<div class="js-report-staff-cnt">
										<div class="js-report-staff-info js-report-staff-name">
											<?php
												if($agent->firstname && $agent->lastname){
													$agentname = $agent->firstname . ' ' . $agent->lastname;
												}else{
													$agentname = $agent->display_name;
												}
												echo esc_html($agentname);
											?>
										</div>
										<div class="js-report-staff-info js-report-staff-email">
											<?php
												if($agent->display_name){
													$username = $agent->display_name;
												}else{
													$username = $agent->user_nicename;
												}
												echo esc_html($username);
											?>
										</div>
										<div class="js-report-staff-info js-report-staff-email">
											<?php
												if($agent->email){
													$email = $agent->email;
												}else{
													$email = $agent->user_email;
												}
												echo esc_html($email);
											?>
										</div>
									</div>
								</div>
								<?php
								$rating_class = 'box6';
									if(in_array('feedback', jssupportticket::$_active_addons)){
										if($agent->avragerating > 4){
											$rating_class = 'box65';
										}elseif($agent->avragerating > 3){
											$rating_class = 'box64';
										}elseif($agent->avragerating > 2){
											$rating_class = 'box63';
										}elseif($agent->avragerating > 1){
											$rating_class = 'box62';
										}elseif($agent->avragerating > 0){
											$rating_class = 'box61';
										}
									}
									if(in_array('timetracking', jssupportticket::$_active_addons)){
										$hours = floor($agent->time[0] / 3600);
							            $mins = floor($agent->time[0] / 60);
							            $mins = floor($mins % 60);
							            $secs = floor($agent->time[0] % 60);
							            $avgtime = sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
							        }
						        ?>
								<div class="js-admin-staff-boxes">
									<?php
										$open_percentage = 0;
										$close_percentage = 0;
										$overdue_percentage = 0;
										$answered_percentage = 0;
										$pending_percentage = 0;
										if(isset(jssupportticket::$_data['ticket_total']) && isset($agent->allticket) && $agent->allticket != 0){
										    $open_percentage = round(($agent->openticket / $agent->allticket) * 100);
										    $close_percentage = round(($agent->closeticket / $agent->allticket) * 100);
										    $overdue_percentage = round(($agent->overdueticket / $agent->allticket) * 100);
										    $answered_percentage = round(($agent->answeredticket / $agent->allticket) * 100);
										    $pending_percentage = round(($agent->pendingticket / $agent->allticket) * 100);
										}
										if(isset(jssupportticket::$_data['ticket_total']) && isset($agent->allticket) && $agent->allticket != 0){
										    $allticket_percentage = 100;
										}
									?>
									<div class="js-ticket-count">
									    <div class="js-ticket-link">
									        <a class="js-ticket-link js-ticket-green" href="#" data-tab-number="1" title="<?php echo esc_html(__('Open Ticket', 'js-support-ticket')); ?>">
									            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($open_percentage); ?>" data-tab-number="1">
									                <div class="js-mr-rp" data-progress="<?php echo esc_attr($open_percentage); ?>">
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
									                    echo esc_html(__('Open', 'js-support-ticket'));
									                    echo ' ( '.esc_html($agent->openticket).' )';
									                ?>
									            </div>
									        </a>
									    </div>
									    <div class="js-ticket-link">
									        <a class="js-ticket-link js-ticket-brown" href="#" data-tab-number="2" title="<?php echo esc_html(__('answered ticket', 'js-support-ticket')); ?>">
									            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($answered_percentage); ?>" >
									                <div class="js-mr-rp" data-progress="<?php echo esc_attr($answered_percentage); ?>">
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
									                    echo ' ( '. esc_html($agent->answeredticket).' )';
									                ?>
									            </div>
									        </a>
									    </div>
									    <div class="js-ticket-link">
						                    <a class="js-ticket-link js-ticket-blue" href="#" data-tab-number="3" title="<?php echo esc_html(__('pending ticket', 'js-support-ticket')); ?>">
						                        <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($pending_percentage); ?>">
						                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($pending_percentage); ?>">
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
						                                echo ' ( '. esc_html($agent->pendingticket).' )';
						                            ?>
						                        </div>
						                    </a>
						                </div>
									    <div class="js-ticket-link">
									        <a class="js-ticket-link js-ticket-orange" href="#" data-tab-number="4" title="<?php echo esc_html(__('overdue ticket', 'js-support-ticket')); ?>">
									            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($overdue_percentage); ?>" >
									                <div class="js-mr-rp" data-progress="<?php echo esc_attr($overdue_percentage); ?>">
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
									                    echo ' ( '. esc_html($agent->overdueticket).' )';
									                ?>
									            </div>
									        </a>
									    </div>
									    <div class="js-ticket-link">
									        <a class="js-ticket-link js-ticket-red" href="#" data-tab-number="5" title="<?php echo esc_html(__('Close Ticket', 'js-support-ticket')); ?>">
									            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($close_percentage); ?>" >
									                <div class="js-mr-rp" data-progress="<?php echo esc_attr($close_percentage); ?>">
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
									                    echo ' ( '. esc_html($agent->closeticket).' )';
									                ?>
									            </div>
									        </a>
									    </div>
										<?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
											<div class="js-ticket-link <?php echo esc_attr($rating_class)?>">
												<a href="#" class="js-ticket-link js-ticket-mariner" title="<?php echo esc_html(__('Rating', 'js-support-ticket')); ?>">
													<span class="js-report-box-number">
														<?php if($agent->avragerating > 0){ ?>
															<span class="rating" ><?php echo esc_html(round($agent->avragerating,1)); ?></span>/5
														<?php }else{ ?>
															NA
														<?php } ?>
													</span>
													<span class="js-report-box-title"><?php echo esc_html(__('Average rating','js-support-ticket')); ?></span>
													<div class="js-report-box-color"></div>
												</a>
											</div>
										<?php } ?>
										<?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
											<div class="js-ticket-link">
												<a href="#" class="js-ticket-link js-ticket-purple" title="<?php echo esc_html(__('Average time', 'js-support-ticket')); ?>">
													<span class="js-report-box-number">
														<span class="time" >
															<?php echo esc_html($avgtime); ?>
														</span>
														<span class="exclamation" >
															<?php
															if($agent->time[1] != 0){
												            	echo '!';
												            }
															?>
														</span>
													</span>
													<span class="js-report-box-title"><?php echo esc_html(__('Average time','js-support-ticket')); ?></span>
													<div class="js-report-box-color"></div>
												</a>
											</div>
										<?php } ?>
									</div>
								</div>
							</a>
						</div>
					<?php
					}
				    if (jssupportticket::$_data[1]) {
				        echo '<div class="tablenav"><div class="tablenav-pages"' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
				    }
				} else {
					JSSTlayout::getNoRecordFound();
				}
				?>
				</div>
			</div>
		</div>
	</div>
</div>
