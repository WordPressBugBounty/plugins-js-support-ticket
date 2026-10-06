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
	jQuery(document).ready(function(){
		jQuery('.custom_date').datepicker({
            dateFormat: '".$jsst_js_scriptdateformat."'
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
		data.addColumn('date', '". esc_html(__('Dates','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('New','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Answered','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Pending','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Overdue','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Closed','js-support-ticket')) ."');
		data.addRows([
			".wp_kses(jssupportticket::$jsst_data['line_chart_json_array'], JSST_ALLOWED_TAGS)."
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
        jsstDropOverdue(data, options);
        chart.draw(data, options);
    }
	// function resizeCharts () {
	//     // redraw charts, dashboards, etc here
	//     chart.draw(data, options);
	// }
	/* The commented-out resizeCharts below was the same broken one Agent
	   Reports had - it referenced drawChart's locals. Redrawing means calling
	   drawChart() again, debounced. */
	var jsstResizeTimer = null;
	/* Guarded on the WIDTH, not on the event: a redraw changes the page
	   height, which can toggle the vertical scrollbar, which fires resize
	   again - a loop that pins the renderer. */
	var jsstLastWidth = jQuery(window).width();
	jQuery(window).resize(function () {
	    if (jQuery(window).width() === jsstLastWidth) { return; }
	    jsstLastWidth = jQuery(window).width();
	    clearTimeout(jsstResizeTimer);
	    jsstResizeTimer = setTimeout(function () { try { drawChart(); } catch (e) {} }, 200);
	});
";
wp_add_inline_script('ticket-google-charts-handle',$jsst_jssupportticket_js);
JSSTmessage::getMessage();
$jsst_t_name = 'getdepartmentexport';
$jsst_link_export = admin_url('admin.php?page=export&task='.esc_attr($jsst_t_name).'&action=jstask&date_start='.jssupportticket::$jsst_data['filter']['date_start'].'&date_end='.jssupportticket::$jsst_data['filter']['date_end']);
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
  <?php
  /* The report exports are still served by the legacy add-on until
     4.0-CORE-10b moves them onto the CSV writer, so the button only
     appears when that add-on is there to answer it. */
  $jsst_headactions = array();
  if (JSSTmergedaddon::legacyActive('export')) {
      $jsst_headactions[] = array('text' => __('Export Data', 'js-support-ticket'),
          'url' => $jsst_link_export, 'style' => 'ghost', 'attrs' => array('id' => 'jsexport-link'));
  }
  JSSTlayout::adminPageHeader(array(
      'title'   => __("Department Reports", 'js-support-ticket'),
      'actions' => $jsst_headactions,
  ));
  ?>
        <div id="jsstadmin-data-wrp">
            <?php
            require_once(dirname(__FILE__) . '/report_common.php');
            $jsst_curdate    = date_i18n('Y-m-d');
            $jsst_enddate    = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
            $jsst_date_start = !empty(jssupportticket::$jsst_data['filter']['date_start']) ? jssupportticket::$jsst_data['filter']['date_start'] : $jsst_curdate;
            $jsst_date_end   = !empty(jssupportticket::$jsst_data['filter']['date_end']) ? jssupportticket::$jsst_data['filter']['date_end'] : $jsst_enddate;
            ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('The same figures as the agent report, gathered by department instead of by person.', 'js-support-ticket')); ?>
            </p>

            <form class="js-filter-form js-report-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reports&jstlay=departmentreport"),"reports")); ?>">
                <div class="jsst-card">
                    <div class="jsst-card-body">
                        <div class="jsst-formgrid">
                            <div class="jsst-frow jsst-frow-sm">
                                <label class="jsst-flabel" for="date_start"><?php echo esc_html(__('Start Date', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('date_start', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_date_start)), array('class' => 'custom_date js-form-date-field')), JSST_ALLOWED_TAGS); ?></div>
                            </div>
                            <div class="jsst-frow jsst-frow-sm">
                                <label class="jsst-flabel" for="date_end"><?php echo esc_html(__('End Date', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('date_end', date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_date_end)), array('class' => 'custom_date js-form-date-field')), JSST_ALLOWED_TAGS); ?></div>
                            </div>
                            <div class="jsst-frow jsst-frow-action">
                                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                        <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>

            <?php jsst_report_section_open(__('Overall Report', 'js-support-ticket'));
                  jsst_report_graph('curve_chart');
                  jsst_report_section_close(); ?>

            <?php jsst_report_section_open(__('Departments', 'js-support-ticket')); ?>
                <?php if (!empty(jssupportticket::$jsst_data['depatments_report'])) {
                    foreach (jssupportticket::$jsst_data['depatments_report'] AS $jsst_dept) {
                        $jsst_all = isset($jsst_dept->allticket) ? (int) $jsst_dept->allticket : 0;
                        $jsst_ppc = function ($jsst_v) use ($jsst_all) {
                            return ($jsst_all === 0 || empty($jsst_v)) ? 0 : (int) round(((int) $jsst_v / $jsst_all) * 100);
                        };
                        $jsst_href = admin_url('admin.php?page=reports&jstlay=departmentdetailreport&id=' . esc_attr($jsst_dept->id)
                            . '&date_start=' . jssupportticket::$jsst_data['filter']['date_start']
                            . '&date_end=' . jssupportticket::$jsst_data['filter']['date_end']); ?>
                        <div class="jsst-reprow">
                            <a class="jsst-repwho" href="<?php echo esc_url($jsst_href); ?>" title="<?php echo esc_attr(__('Department', 'js-support-ticket')); ?>">
                                <span>
                                    <span class="jsst-repname"><?php echo esc_html($jsst_dept->departmentname); ?></span>
                                    <span class="jsst-repsub"><?php echo esc_html($jsst_dept->email); ?></span>
                                </span>
                            </a>
                            <div class="jsst-repstats">
                                <div class="jsst-statrow">
                                    <?php
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_dept->openticket), 'fill' => 'js-ticket-open',
                                        'tone' => 'js-ticket-green', 'count' => (int) $jsst_dept->openticket, 'label' => __('Open', 'js-support-ticket'),
                                        'title' => __('Open Tickets', 'js-support-ticket')));
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_dept->answeredticket), 'fill' => 'js-ticket-answer',
                                        'tone' => 'js-ticket-brown', 'count' => (int) $jsst_dept->answeredticket, 'label' => __('Answered', 'js-support-ticket'),
                                        'title' => __('answered ticket', 'js-support-ticket')));
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_dept->pendingticket), 'fill' => 'js-ticket-allticket',
                                        'tone' => 'js-ticket-blue', 'count' => (int) $jsst_dept->pendingticket, 'label' => __('Pending', 'js-support-ticket'),
                                        'title' => __('Pending Tickets', 'js-support-ticket')));
                                    if (in_array('overdue', jssupportticket::$_active_addons)) {
                                        jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_dept->overdueticket), 'fill' => 'js-ticket-overdue',
                                            'tone' => 'js-ticket-orange', 'count' => (int) $jsst_dept->overdueticket, 'label' => __('Overdue', 'js-support-ticket'),
                                            'title' => __('Overdue Tickets', 'js-support-ticket')));
                                    }
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_dept->closeticket), 'fill' => 'js-ticket-close',
                                        'tone' => 'js-ticket-red', 'count' => (int) $jsst_dept->closeticket, 'label' => __('Closed', 'js-support-ticket'),
                                        'title' => __('Close Ticket', 'js-support-ticket')));
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php }
                    if (jssupportticket::$jsst_data[1]) {
                        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
                    }
                } else {
                    JSSTlayout::getNoRecordFound();
                } ?>
            <?php jsst_report_section_close(); ?>
        </div>
    </div>
</div>
