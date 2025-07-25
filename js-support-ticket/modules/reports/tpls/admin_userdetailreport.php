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
<?php
$js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
$jssupportticket_js ="
	jQuery(document).ready(function ($) {
        $('.custom_date').datepicker({
            dateFormat: '". $js_scriptdateformat ."'
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
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
$jssupportticket_js ="
    function drawChart() {
      	var data = new google.visualization.DataTable();
		data.addColumn('date', '". esc_html(__("Dates","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("New","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("Answered","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("Pending","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("Overdue","js-support-ticket")) ."');
        data.addColumn('number', '". esc_html(__("Closed","js-support-ticket")) ."');
		data.addRows([
			". wp_kses(jssupportticket::$_data['line_chart_json_array'], JSST_ALLOWED_TAGS) ."
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
wp_add_inline_script('ticket-google-charts-handle',$jssupportticket_js);
JSSTmessage::getMessage();
$t_name = 'getuserexportbyuid';
$link_export = admin_url('admin.php?page=export&task='.esc_attr($t_name).'&action=jstask&uid='.jssupportticket::$_data['filter']['uid'].'&date_start='.jssupportticket::$_data['filter']['date_start'].'&date_end='.jssupportticket::$_data['filter']['date_end']);
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
		                <li><?php echo esc_html(__('User Detail Report','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("User Detail Report", 'js-support-ticket')); ?></h1>
            <?php if(in_array('export', jssupportticket::$_active_addons)){ ?>
				<a title="<?php echo esc_html(__('Export Data','js-support-ticket')); ?>" id="jsexport-link" class="jsstadmin-add-link button" href="<?php echo esc_url($link_export); ?>"><img alt="<?php echo esc_html(__('Export','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/export-icon.png" /><?php echo esc_html(__('Export Data', 'js-support-ticket')); ?></a>
			<?php } ?>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
	    	<form class="js-filter-form js-report-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reports&jstlay=userdetailreport&id=".jssupportticket::$_data['user_report']->id),"reports")); ?>">
			    <?php
			        $curdate = date_i18n('Y-m-d');
			        $enddate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
			        $date_start = !empty(jssupportticket::$_data['filter']['date_start']) ? jssupportticket::$_data['filter']['date_start'] : $curdate;
			        $date_end = !empty(jssupportticket::$_data['filter']['date_end']) ? jssupportticket::$_data['filter']['date_end'] : $enddate;
			    	echo wp_kses(JSSTformfield::text('date_start', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($date_start)), array('class' => 'custom_date js-form-date-field','placeholder' => esc_html(__('Start Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
			    	echo wp_kses(JSSTformfield::text('date_end', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($date_end)), array('class' => 'custom_date js-form-date-field','placeholder' => esc_html(__('End Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
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
				$agent = jssupportticket::$_data['user_report'];
				if(!empty($agent)){ ?>
					<div class="js-admin-staff-wrapper">
						<div class="js-admin-staff-cnt">
							<div class="js-report-staff-image">
								<?php echo wp_kses(jsst_get_avatar($agent->id), JSST_ALLOWED_TAGS); ?>
							</div>
							<div class="js-report-staff-cnt">
								<div class="js-report-staff-info js-report-staff-name">
									<?php
										if(isset($agent->firstname) && isset($agent->lastname)){
											$agentname = $agent->firstname . ' ' . $agent->lastname;
										}else{
											$agentname = $agent->display_name;
										}
										echo esc_html($agentname);
									?>
								</div>
								<div class="js-report-staff-info js-report-staff-post">
									<?php
										if(isset($agent->username)){
											$username = $agent->username;
										}else{
											$username = $agent->user_nicename;
										}
										echo esc_html($username);
									?>
								</div>
								<div class="js-report-staff-info js-report-staff-email">
									<?php
										if(isset($agent->email)){
											$email = $agent->email;
										}else{
											$email = $agent->user_email;
										}
										echo esc_html($email);
									?>
								</div>
							</div>
						</div>
						<div class="js-admin-staff-boxes">
							<?php
								$open_percentage = 0;
								$close_percentage = 0;
								$overdue_percentage = 0;
								$answered_percentage = 0;
								$pending_percentage = 0;
								if(isset($agent) && isset($agent->allticket) && $agent->allticket != 0){
								    $open_percentage = round(($agent->openticket / $agent->allticket) * 100);
								    $close_percentage = round(($agent->closeticket / $agent->allticket) * 100);
								    $overdue_percentage = round(($agent->overdueticket / $agent->allticket) * 100);
								    $answered_percentage = round(($agent->answeredticket / $agent->allticket) * 100);
								    $pending_percentage = round(($agent->pendingticket / $agent->allticket) * 100);
								}
								if(isset($agent) && isset($agent->allticket) && $agent->allticket != 0){
								    $allticket_percentage = 100;
								}
							?>
							<div class="js-ticket-count">
							    <div class="js-ticket-link">
							        <a class="js-ticket-link js-ticket-green" href="#" data-tab-number="1" title="<?php echo esc_html(__('Open Ticket','js-support-ticket')); ?>">
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
							                    echo esc_html(__('New', 'js-support-ticket'));
							                    echo ' ( '.esc_html($agent->openticket).' )';
							                ?>
							            </div>
							        </a>
							    </div>
							    <div class="js-ticket-link">
							        <a class="js-ticket-link js-ticket-brown" href="#" data-tab-number="2" title="<?php echo esc_html(__('answered ticket','js-support-ticket')); ?>">
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
				                    <a class="js-ticket-link js-ticket-blue" href="#" data-tab-number="3" title="<?php echo esc_html(__('pending ticket','js-support-ticket')); ?>">
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
							        <a class="js-ticket-link js-ticket-orange" href="#" data-tab-number="4" title="<?php echo esc_html(__('overdue ticket','js-support-ticket')); ?>">
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
							        <a class="js-ticket-link js-ticket-red" href="#" data-tab-number="5" title="<?php echo esc_html(__('Close Ticket','js-support-ticket')); ?>">
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
							</div>
						</div>
					</div>
				<?php
				} ?>
			<div class="js-admin-report">
				<div class="js-admin-subtitle"><?php echo esc_html(__('Tickets','js-support-ticket')); ?></div>
			<?php
				if(!empty(jssupportticket::$_data['user_tickets'])){ ?>
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
						foreach(jssupportticket::$_data['user_tickets'] AS $ticket){
							if(in_array('timetracking', jssupportticket::$_active_addons)){
								$hours = floor($ticket->time / 3600);
					            $mins = floor($ticket->time / 60);
					            $mins = floor($mins % 60);
					            $secs = floor($ticket->time % 60);
					            $avgtime = sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
				            }
							if(in_array('feedback', jssupportticket::$_active_addons)){
					            $rating_color = 0;
					            if($ticket->rating > 4){
					            	$rating_color = '#ea1d22';
					            }elseif($ticket->rating > 3){
					            	$rating_color = '#f58634';
					            }elseif($ticket->rating > 2){
					            	$rating_color = '#a8518a';
					            }elseif($ticket->rating > 1){
					            	$rating_color = '#0098da';
					            }elseif($ticket->rating > 0){
					            	$rating_color = '#069a2e';
					            }
				            }
							?>
							<tr>
								<td class="left">
									<a target="_blank" href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid='.esc_attr($ticket->id))); ?>" title="<?php echo esc_html(__('Ticket','js-support-ticket')); ?>">
										<div class="js-admin-staff-wrapper js-rep-tkt-list">
											<div class="js-admin-staff-cnt">
												<div class="js-report-staff-image">
										            <?php echo wp_kses(jsst_get_avatar($ticket->uid), JSST_ALLOWED_TAGS); ?>
												</div>
												<div class="js-report-staff-cnt">
													<div class="js-report-staff-info js-report-staff-name">
														<?php
															echo esc_html($ticket->name);
														?>
													</div>
													<div class="js-report-staff-info js-report-staff-post">
														<?php
															echo esc_html($ticket->subject);
														?>
													</div>
													<div class="js-report-staff-info js-report-staff-email">
														<?php
															echo esc_html($ticket->email);
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
										/*switch($ticket->status){
											case 0:
												$status = '<font color="#1EADD8">'. esc_html(__('New','js-support-ticket')).'</font>';
												if($ticket->isoverdue == 1)
													$status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
											break;
											case 1:
												$status = '<font color="#D98E11">'. esc_html(__('Pending','js-support-ticket')).'</font>';
												if($ticket->isoverdue == 1)
													$status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
											break;
											case 2:
												$status = '<font color="#D98E11">'. esc_html(__('In Progress','js-support-ticket')).'</font>';
												if($ticket->isoverdue == 1)
													$status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
											break;
											case 3:
												$status = '<font color="#179650">'. esc_html(__('Answered','js-support-ticket')).'</font>';
												if($ticket->isoverdue == 1)
													$status = '<font color="#DB624C">'. esc_html(__('Overdue','js-support-ticket')).'</font>';
											break;
											case 4:
												$status = '<font color="#5F3BBB">'. esc_html(__('Closed','js-support-ticket')).'</font>';
											break;
										}*/
										if (!in_array($ticket->status, [5, 6]) && $ticket->isoverdue == 1) {
											$status = __('Overdue','js-support-ticket');
							                $color = '#FFFFFF';
							                $bgcolor = '#DB624C';
						                } else {
						                	$status = $ticket->statustitle;
							                $color = $ticket->statuscolour;
							                $bgcolor = $ticket->statusbgcolour;
						                }
									?>
									<span class="priority" style="background:<?php echo esc_attr($bgcolor); ?>;color:<?php echo esc_attr($color); ?>">
										<?php echo esc_html($status); ?>
									</span>
								</td>
								<td><span class="priority" style="background:<?php echo esc_attr($ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?></span></td>
								<td ><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'],strtotime($ticket->created))); ?></td>
								<?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
									<td >
										<?php if($ticket->rating > 0){ ?>
											<span style="color:<?php echo esc_attr($rating_color); ?>;font-weight:bold;font-size:16px;" > <?php echo esc_html($ticket->rating);?></span>
											<?php echo wp_kses(esc_html(__('Out of','js-support-ticket')).'<span style="font-weight:bold;font-size:15px;" >&nbsp;5</span>', JSST_ALLOWED_TAGS);
										}else{
											echo 'NA';
										} ?>
									</td>
								<?php } ?>
								<?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
									<td ><?php echo esc_html($avgtime); ?></td>
								<?php } ?>
							</tr>
							<?php
						}
						?>
					</table>
					<?php
				    if (jssupportticket::$_data[1]) {
				        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
				    }
				} else {
					JSSTlayout::getNoRecordFound();
				}
				?>
			</div>
		</div>
	</div>
</div>
