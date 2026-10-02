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
$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $('.custom_date').datepicker({
            dateFormat: '".$jsst_js_scriptdateformat."'
        });
	});

	function resetFrom(){
		document.getElementById('date_start').value = '';
		document.getElementById('date_end').value = '';
		document.getElementById('jssupportticketform').submit();
	}
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
    	google.charts.load('current', {packages: ['corechart']});
		google.setOnLoadCallback(drawChart);
	});

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
        chart.draw(data, options);
    }
    /* Google Charts measures its container once, at draw time. Without this a
       window narrowed after load leaves the chart wider than its card and the
       page scrolls sideways. Debounced: a drag fires resize continuously. */
    /* Guarded on the WIDTH, not on the event. Redrawing changes the page
       height, which can add or remove the vertical scrollbar, which fires
       `resize` again - a redraw loop that pins the renderer and leaves the
       layout mid-flight (it timed out screenshots and reported zero-width
       rows). A chart only needs redrawing when its width changed. */
    var jsstResizeTimer = null;
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
$jsst_department = jssupportticket::$jsst_data['depatments_report'];
$jsst_t_name = 'getdepartmentmemberexportbydepartmentid';
$jsst_link_export = admin_url('admin.php?page=export&task='.esc_attr($jsst_t_name).'&action=jstask&id='.jssupportticket::$jsst_data['filter']['id'].'&date_start='.jssupportticket::$jsst_data['filter']['date_start'].'&date_end='.jssupportticket::$jsst_data['filter']['date_end']);
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
      'title'   => __("Department Detail Report", 'js-support-ticket'),
      'actions' => $jsst_headactions,
  ));
  ?>
        <div id="jsstadmin-data-wrp">
            <?php
            require_once(dirname(__FILE__) . '/report_common.php');
            $jsst_all = isset($jsst_department->allticket) ? (int) $jsst_department->allticket : 0;
            $jsst_ppc = function ($jsst_v) use ($jsst_all) {
                return ($jsst_all === 0 || empty($jsst_v)) ? 0 : (int) round(((int) $jsst_v / $jsst_all) * 100);
            };
            $jsst_curdate    = date_i18n('Y-m-d');
            $jsst_enddate    = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
            $jsst_date_start = !empty(jssupportticket::$jsst_data['filter']['date_start']) ? jssupportticket::$jsst_data['filter']['date_start'] : $jsst_curdate;
            $jsst_date_end   = !empty(jssupportticket::$jsst_data['filter']['date_end']) ? jssupportticket::$jsst_data['filter']['date_end'] : $jsst_enddate;
            ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('One department, over the period you choose: what it is carrying and every ticket behind the figures.', 'js-support-ticket')); ?>
            </p>

            <form class="js-filter-form js-report-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reports&jstlay=departmentdetailreport&id=".esc_attr($jsst_department->id)),"reports")); ?>">
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

            <?php if (!empty($jsst_department)) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-body">
                        <div class="jsst-reprow">
                            <span class="jsst-repwho">
                                <span>
                                    <span class="jsst-repname"><?php echo esc_html($jsst_department->departmentname); ?></span>
                                    <span class="jsst-repsub"><?php echo esc_html($jsst_department->email); ?></span>
                                </span>
                            </span>
                            <div class="jsst-repstats">
                                <div class="jsst-statrow">
                                    <?php
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_department->openticket), 'fill' => 'js-ticket-open',
                                        'tone' => 'js-ticket-green', 'count' => (int) $jsst_department->openticket, 'label' => __('Open', 'js-support-ticket')));
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_department->answeredticket), 'fill' => 'js-ticket-answer',
                                        'tone' => 'js-ticket-brown', 'count' => (int) $jsst_department->answeredticket, 'label' => __('Answered', 'js-support-ticket')));
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_department->pendingticket), 'fill' => 'js-ticket-allticket',
                                        'tone' => 'js-ticket-blue', 'count' => (int) $jsst_department->pendingticket, 'label' => __('Pending', 'js-support-ticket')));
                                    if (in_array('overdue', jssupportticket::$_active_addons)) {
                                        jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_department->overdueticket), 'fill' => 'js-ticket-overdue',
                                            'tone' => 'js-ticket-orange', 'count' => (int) $jsst_department->overdueticket, 'label' => __('Overdue', 'js-support-ticket')));
                                    }
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_department->closeticket), 'fill' => 'js-ticket-close',
                                        'tone' => 'js-ticket-red', 'count' => (int) $jsst_department->closeticket, 'label' => __('Closed', 'js-support-ticket')));
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php jsst_report_section_open(__('Department Statistics', 'js-support-ticket'));
                  jsst_report_graph('curve_chart');
                  jsst_report_section_close(); ?>

            <?php jsst_report_section_open(__('Tickets', 'js-support-ticket'));
            if (!empty(jssupportticket::$jsst_data['department_tickets'])) {
                jsst_report_tickets_open();
                foreach (jssupportticket::$jsst_data['department_tickets'] AS $jsst_ticket) { ?>
                    <tr>
                        <td>
                            <a class="jsst-replink" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr(__('Ticket', 'js-support-ticket')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . esc_attr($jsst_ticket->id))); ?>">
                                <?php jsst_report_ticket_person($jsst_ticket); ?>
                            </a>
                        </td>
                        <?php jsst_report_ticket_cells($jsst_ticket); ?>
                    </tr>
                <?php }
                jsst_report_tickets_close();
                if (jssupportticket::$jsst_data[1]) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
                }
            } else {
                JSSTlayout::getNoRecordFound();
            }
            jsst_report_section_close(); ?>
        </div>
    </div>
</div>
