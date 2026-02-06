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
?>
<?php
$jsst_js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
$jsst_jssupportticket_js ="
	jQuery(document).ready(function ($) {
        $('.custom_date').datepicker({
            dateFormat: '". $jsst_js_scriptdateformat ."'
        });
        google.load('visualization', '1', {packages:['corechart']});
		google.setOnLoadCallback(drawChart);
	});

	function resetFrom(){
		document.getElementById('date_start').value = '';
		document.getElementById('date_end').value = '';
		document.getElementById('jssupportticketform').submit();
	}
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_jssupportticket_js ="
    function drawChart() {
      	var data = new google.visualization.DataTable();
		data.addColumn('date', '". esc_html(__("Dates","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("New","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("Answered","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("Pending","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("Overdue","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("Closed","js-support-ticket")) ."');
		data.addRows([
			". wp_kses(jssupportticket::$jsst_data['line_chart_json_array'], JSST_ALLOWED_TAGS) ."
        ]);

        var options = {
          colors:['#1EADD8','#179650','#D98E11','#DB624C','#5F3BBB'],
          curveType: 'function',
          legend: { position: 'bottom' },
          pointSize: 6,
		  // This line will make you select an entire row of data at a time
		  focusTarget: 'category',
		  chartArea: {width:'90%',top:50}
		};

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
        chart.draw(data, options);
    }
";
wp_add_inline_script('ticket-google-charts-handle',$jsst_jssupportticket_js);
JSSTmessage::getMessage();
$jsst_t_name = 'getuserexportbyuid';
$jsst_link_export = admin_url('admin.php?page=export&task='.esc_attr($jsst_t_name).'&action=jstask&uid='.jssupportticket::$jsst_data['filter']['uid'].'&date_start='.jssupportticket::$jsst_data['filter']['date_start'].'&date_end='.jssupportticket::$jsst_data['filter']['date_end']);
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
		                <li><?php echo esc_html(__('User Detail Report','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("User Detail Report", 'js-support-ticket')); ?></h1>
            <?php if(in_array('export', jssupportticket::$_active_addons)){ ?>
				<a title="<?php echo esc_attr(__('Export Data','js-support-ticket')); ?>" id="jsexport-link" class="jsstadmin-add-link button" href="<?php echo esc_url($jsst_link_export); ?>"><img alt = "<?php echo esc_attr(__('Export','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/export-icon.png" /><?php echo esc_html(__('Export Data', 'js-support-ticket')); ?></a>
			<?php } ?>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
	    	<form class="js-filter-form js-report-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reports&jstlay=userdetailreport&id=".jssupportticket::$jsst_data['user_report']->id),"reports")); ?>">
			    <?php
			        $jsst_curdate = date_i18n('Y-m-d');
			        $jsst_enddate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
			        $jsst_date_start = !empty(jssupportticket::$jsst_data['filter']['date_start']) ? jssupportticket::$jsst_data['filter']['date_start'] : $jsst_curdate;
			        $jsst_date_end = !empty(jssupportticket::$jsst_data['filter']['date_end']) ? jssupportticket::$jsst_data['filter']['date_end'] : $jsst_enddate;
			    	echo wp_kses(JSSTformfield::text('date_start', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_date_start)), array('class' => 'custom_date js-form-date-field','placeholder' => esc_html(__('Start Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::text('date_end', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_date_end)), array('class' => 'custom_date js-form-date-field','placeholder' => esc_html(__('End Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS);
				?>
			    <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
				<?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
			</form>
			<div class="js-admin-report">
				<div class="js-admin-subtitle"><?php echo esc_html(__('User Statistics','js-support-ticket')); ?></div>
				<div class="js-admin-rep-graph" id="curve_chart" style="height:400px;width:98%; "></div>
			</div>
			<?php
				$jsst_agent = jssupportticket::$jsst_data['user_report'];
				if(!empty($jsst_agent)){ ?>
					<div class="js-admin-staff-wrapper">
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
							                    echo ' ( '.esc_html($jsst_agent->openticket).' )';
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
							                    echo ' ( '. esc_html($jsst_agent->answeredticket).' )';
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
				                                echo ' ( '. esc_html($jsst_agent->pendingticket).' )';
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
							                    echo ' ( '. esc_html($jsst_agent->overdueticket).' )';
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
							                    echo ' ( '. esc_html($jsst_agent->closeticket).' )';
							                ?>
							            </div>
							        </a>
							    </div>
							</div>
						</div>
					</div>
				<?php
				} ?>
			<div class="js-admin-report">
				<div class="js-admin-subtitle"><?php echo esc_html(__('Tickets','js-support-ticket')); ?></div>
			<?php
				if(!empty(jssupportticket::$jsst_data['user_tickets'])){ ?>
					<table id="js-support-ticket-table" class="js-admin-report-tickets">
						<tr class="js-support-ticket-table-heading">
							<th class="left"><?php echo esc_html(__('Subject','js-support-ticket')); ?></th>
							<th><?php echo esc_html(__('Status','js-support-ticket')); ?></th>
							<th><?php echo esc_html(__('Priority','js-support-ticket')); ?></th>
							<th><?php echo esc_html(__('Created','js-support-ticket')); ?></th>
							<?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
								<th><?php echo esc_html(__('Rating','js-support-ticket')); ?></th>
							<?php } ?>
							<?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
								<th><?php echo esc_html(__('Time Taken','js-support-ticket')); ?></th>
							<?php } ?>
						</tr>
						<?php
						foreach(jssupportticket::$jsst_data['user_tickets'] AS $jsst_ticket){
							if(in_array('timetracking', jssupportticket::$_active_addons)){
								$jsst_hours = floor($jsst_ticket->time / 3600);
					            $jsst_mins = floor($jsst_ticket->time / 60);
					            $jsst_mins = floor($jsst_mins % 60);
					            $jsst_secs = floor($jsst_ticket->time % 60);
					            $jsst_avgtime = sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
				            }
							if(in_array('feedback', jssupportticket::$_active_addons)){
					            $jsst_rating_color = 0;
					            if($jsst_ticket->rating > 4){
					            	$jsst_rating_color = '#ea1d22';
					            }elseif($jsst_ticket->rating > 3){
					            	$jsst_rating_color = '#f58634';
					            }elseif($jsst_ticket->rating > 2){
					            	$jsst_rating_color = '#a8518a';
					            }elseif($jsst_ticket->rating > 1){
					            	$jsst_rating_color = '#0098da';
					            }elseif($jsst_ticket->rating > 0){
					            	$jsst_rating_color = '#069a2e';
					            }
				            }
							?>
							<tr>
								<td class="left">
									<a target="_blank" href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid='.esc_attr($jsst_ticket->id))); ?>" title="<?php echo esc_attr(__('Ticket','js-support-ticket')); ?>">
										<div class="js-admin-staff-wrapper js-rep-tkt-list">
											<div class="js-admin-staff-cnt">
												<div class="js-report-staff-image">
										            <?php echo wp_kses(jsst_get_avatar($jsst_ticket->uid), JSST_ALLOWED_TAGS); ?>
												</div>
												<div class="js-report-staff-cnt">
													<div class="js-report-staff-info js-report-staff-name">
														<?php
															echo esc_html($jsst_ticket->name);
														?>
													</div>
													<div class="js-report-staff-info js-report-staff-post">
														<?php
															echo esc_html($jsst_ticket->subject);
														?>
													</div>
													<div class="js-report-staff-info js-report-staff-email">
														<?php
															echo esc_html($jsst_ticket->email);
														?>
													</div>
												</div>
											</div>
										</div>
									</a>
								</td>
								<td >
									<?php
							            // 1 -> New Ticket
							            // 2 -> Waiting admin/staff reply
							            // 3 -> in progress
							            // 4 -> waiting for customer reply
							            // 5 -> close ticket
										/*switch($jsst_ticket->status){
											case 0:
												$jsst_status = '<font color="#1EADD8">'. esc_html(__('New','js-support-ticket')).'</font>';
												if($jsst_ticket->isoverdue == 1)
													$jsst_status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
											break;
											case 1:
												$jsst_status = '<font color="#D98E11">'. esc_html(__('Pending','js-support-ticket')).'</font>';
												if($jsst_ticket->isoverdue == 1)
													$jsst_status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
											break;
											case 2:
												$jsst_status = '<font color="#D98E11">'. esc_html(__('In Progress','js-support-ticket')).'</font>';
												if($jsst_ticket->isoverdue == 1)
													$jsst_status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
											break;
											case 3:
												$jsst_status = '<font color="#179650">'. esc_html(__('Answered','js-support-ticket')).'</font>';
												if($jsst_ticket->isoverdue == 1)
													$jsst_status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
											break;
											case 4:
												$jsst_status = '<font color="#5F3BBB">'. esc_html(__('Closed','js-support-ticket')).'</font>';
											break;
										}*/
										if (!in_array($jsst_ticket->status, [5, 6]) && $jsst_ticket->isoverdue == 1) {
											$jsst_status = __('Overdue','js-support-ticket');
							                $jsst_color1 = '#FFFFFF';
							                $jsst_bgcolor = '#DB624C';
						                } else {
						                	$jsst_status = $jsst_ticket->statustitle;
							                $jsst_color1 = $jsst_ticket->statuscolour;
							                $jsst_bgcolor = $jsst_ticket->statusbgcolour;
						                }
									?>
									<span class="priority" style="background:<?php echo esc_attr($jsst_bgcolor); ?>;color:<?php echo esc_attr($jsst_color1); ?>">
										<?php echo esc_html($jsst_status); ?>
									</span>
								</td>
								<td><span class="priority" style="background:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?></span></td>
								<td ><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'],strtotime($jsst_ticket->created))); ?></td>
								<?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
									<td >
										<?php if($jsst_ticket->rating > 0){ ?>
											<span style="color:<?php echo esc_attr($jsst_rating_color); ?>;font-weight:bold;font-size:16px;" > <?php echo esc_html($jsst_ticket->rating);?></span>
											<?php echo wp_kses(esc_html(__('Out of','js-support-ticket')).'<span style="font-weight:bold;font-size:15px;" >&nbsp;5</span>', JSST_ALLOWED_TAGS);
										}else{
											echo 'NA';
										} ?>
									</td>
								<?php } ?>
								<?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
									<td ><?php echo esc_html($jsst_avgtime); ?></td>
								<?php } ?>
							</tr>
							<?php
						}
						?>
					</table>
					<?php
				    if (jssupportticket::$jsst_data[1]) {
				        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
				    }
				} else {
					JSSTlayout::getNoRecordFound();
				}
				?>
			</div>
		</div>
	</div>
</div>
