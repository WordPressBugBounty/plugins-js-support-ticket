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
$jsst_jssupportticket_js ="
jQuery(document).ready(function ($) {
	google.load('visualization', '1', {packages:['corechart']});
	google.setOnLoadCallback(drawBarChart);
	function drawBarChart() {
		var data = google.visualization.arrayToDataTable([
         ['". esc_html(__('Status','js-support-ticket')) ."', '". esc_html(__('Tickets By Statuses','js-support-ticket')) ."', { role: 'style' }],
         ". wp_kses(jssupportticket::$jsst_data['bar_chart'], JSST_ALLOWED_TAGS)."
        ]);
        var view = new google.visualization.DataView(data);
        view.setColumns([0, 1,
                       { calc: 'stringify',
                         sourceColumn: 1,
                         type: 'string',
                         role: 'annotation' },
                       2]);

      var options = {
        //title: 'Density of Precious Metals, in g/cm^3',
        width: '95%',
        bar: {groupWidth: '95%'},
        legend: { position: 'none' },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById('bar_chart'));
      chart.draw(view, options);
  	}

	google.setOnLoadCallback(drawStackChart);
    function drawStackChart() {
      var data = google.visualization.arrayToDataTable([
        ['". esc_html(__('Tickets','js-support-ticket'))."', '". esc_html(__('Direct','js-support-ticket'))."', '". esc_html(__('Email','js-support-ticket'))."', { role: 'annotation' } ],
        ". wp_kses(jssupportticket::$jsst_data['stack_data'], JSST_ALLOWED_TAGS)."
      ]);

      var view = new google.visualization.DataView(data);
      var options = {
        width: '95%',
        //height: 400,
        legend: { position: 'top', maxLines: 3 },
        bar: { groupWidth: '75%' },
        isStacked: true,
      };
      var chart = new google.visualization.ColumnChart(document.getElementById('stack_chart'));
      chart.draw(view, options);
  	}

	google.setOnLoadCallback(drawPie3d1Chart);
	function drawPie3d1Chart() {
        var data = google.visualization.arrayToDataTable([
          ['". esc_html(__('Departments','js-support-ticket')). "', '". esc_html(__('Tickets By Departments','js-support-ticket')) ."'],
          ". wp_kses(jssupportticket::$jsst_data['pie3d_chart1'], JSST_ALLOWED_TAGS)."
        ]);

        var options = {
          /* No `title`: the card head above the chart already names it. */
          chartArea: {width: '90%', height: '80%'},
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('pie3d_chart1'));
        chart.draw(data, options);
  	}

	google.setOnLoadCallback(drawPie3d2Chart);
	function drawPie3d2Chart() {
        var data = google.visualization.arrayToDataTable([
          ['". esc_html(__('Priorities','js-support-ticket')) ."', '". esc_html(__('Tickets By Priorities','js-support-ticket')) ."'],
          ".wp_kses(jssupportticket::$jsst_data['pie3d_chart2'], JSST_ALLOWED_TAGS)."
        ]);

        var options = {
          /* No `title`: the card head above the chart already names it. */
          chartArea: {width: '90%', height: '80%'},
          is3D: true,
          colors:".wp_kses(jssupportticket::$jsst_data['priorityColorList'], JSST_ALLOWED_TAGS)."
        };

        var chart = new google.visualization.PieChart(document.getElementById('pie3d_chart2'));
        chart.draw(data, options);
  	}

	google.setOnLoadCallback(drawStackChartHorizontal);
    function drawStackChartHorizontal() {
      var data = google.visualization.arrayToDataTable([
      	".
      		wp_kses(jssupportticket::$jsst_data['stack_chart_horizontal']['title'], JSST_ALLOWED_TAGS).",".
      		wp_kses(jssupportticket::$jsst_data['stack_chart_horizontal']['data'], JSST_ALLOWED_TAGS)
      	."
      ]);

      var view = new google.visualization.DataView(data);

      var options = {
        chartArea: {width:'90%'},
        legend: { position: 'top', maxLines: 3 },
        bar: { groupWidth: '75%' },
        isStacked: true,
        colors:['#ff652f','#5ab9ea','#d89922','#14a76c'],
      };
      var chart = new google.visualization.AreaChart(document.getElementById('stack_chart_horizontal'));
      chart.draw(view, options);
  	}
";
/* Not `isset`. The model sets this to an empty string before it looks for
   anything, so on a desk whose Agents list is empty the key exists, is blank,
   and what reached Google Charts was a table of nothing but its two string
   headers - which it refuses with "Data column(s) for axis #0 cannot be of
   type string", printed where the chart should be. An empty roster is an
   ordinary state, not an error, so the chart is simply not drawn. */
if(!empty(jssupportticket::$jsst_data['slice_chart'])) {
    $jsst_jssupportticket_js .="
    google.setOnLoadCallback(drawSliceChart);
    function drawSliceChart() {
        var data = google.visualization.arrayToDataTable([
            ['". esc_html(__('Tickets','js-support-ticket')) ."', '". esc_html(__('Agent Tickets','js-support-ticket')) ."'],
            ". wp_kses(jssupportticket::$jsst_data['slice_chart'], JSST_ALLOWED_TAGS)."
        ]);

        var options = {
            //title: 'Indian Language Use',
            //pieSliceText: 'label',
            legend : {position: 'none'},
            chartArea : {width: '80%',height:300},
            // slices: {
            //           2: {offset: 0.2},
            //           4: {offset: 0.3},
            //           5: {offset: 0.4},
            //           7: {offset: 0.5},
            //           9: {offset: 0.5},
            // },
        };

        var chart = new google.visualization.BarChart(document.getElementById('slice_chart'));
        chart.draw(data, options);
    }
";
}
/* Keep every chart the width of its card. Google Charts measures the
   container once, at draw time, and never again, so a window resized after
   load left a 734px chart in a 600px card and the page scrolled sideways.
   The list is built here because the draw functions are closures inside the
   ready handler, and drawSliceChart only exists when there is one. */
require_once(dirname(__FILE__) . '/report_common.php');
$jsst_redrawfns = array('drawBarChart', 'drawStackChart', 'drawPie3d1Chart', 'drawPie3d2Chart', 'drawStackChartHorizontal');
if (!empty(jssupportticket::$jsst_data['slice_chart'])) {
    $jsst_redrawfns[] = 'drawSliceChart';
}
$jsst_jssupportticket_js .= jsst_report_redraw_js($jsst_redrawfns);
  $jsst_jssupportticket_js .="
    });
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
JSSTmessage::getMessage();
 ?>
<div id="jsstadmin-wrapper">
    <?php /* Overall Statistics is the one report an agent may read, so the
             column beside it has to be the menu that reader is entitled to
             rather than the administration one. (Roadmap 4.5-FE-02) */ ?>
    <?php JSSTsidemenu::render(); ?>
    <div id="jsstadmin-data">
        <?php
        /* The report exports are still served by the legacy add-on until
           4.0-CORE-10b moves them onto the CSV writer, so the button only
           appears when that add-on is there to answer it. */
        $jsst_headactions = array();
        if (JSSTmergedaddon::legacyActive('export') && current_user_can('manage_options')) {
            $jsst_headactions[] = array('text' => __('Export Data', 'js-support-ticket'),
                'url' => admin_url('admin.php?page=export&task=getoverallexport&action=jstask'), 'style' => 'ghost', 'attrs' => array('id' => 'jsexport-link'));
        }
        JSSTlayout::adminPageHeader(array(
            'title'   => __("Overall Statistics", 'js-support-ticket'),
            'actions' => $jsst_headactions,
        ));
        ?>
        <div id="jsstadmin-data-wrp">
            <?php
            require_once(dirname(__FILE__) . '/report_common.php');
            $jsst_totals = isset(jssupportticket::$jsst_data['ticket_total']) ? jssupportticket::$jsst_data['ticket_total'] : array();
            $jsst_pc = jsst_report_percents($jsst_totals);
            $jsst_n = function ($jsst_key) use ($jsst_totals) { return isset($jsst_totals[$jsst_key]) ? (int) $jsst_totals[$jsst_key] : 0; };
            ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('Every ticket the desk has handled, as five figures and the charts behind them.', 'js-support-ticket')); ?>
            </p>

            <div class="jsst-card">
                <div class="jsst-card-body">
                    <div class="jsst-statrow">
                        <?php
                        jsst_report_tile(array('pct' => $jsst_pc['open'], 'fill' => 'js-ticket-open', 'tone' => 'js-ticket-green',
                            'count' => $jsst_n('openticket'), 'label' => __('Open', 'js-support-ticket'),
                            'title' => __('Open Tickets', 'js-support-ticket'), 'href' => '#', 'tab' => '1'));
                        jsst_report_tile(array('pct' => $jsst_pc['answered'], 'fill' => 'js-ticket-answer', 'tone' => 'js-ticket-brown',
                            'count' => $jsst_n('answeredticket'), 'label' => __('Answered', 'js-support-ticket'),
                            'title' => __('answered ticket', 'js-support-ticket'), 'href' => '#', 'tab' => '2'));
                        if (in_array('overdue', jssupportticket::$_active_addons)) {
                            jsst_report_tile(array('pct' => $jsst_pc['overdue'], 'fill' => 'js-ticket-overdue', 'tone' => 'js-ticket-orange',
                                'count' => $jsst_n('overdueticket'), 'label' => __('Overdue', 'js-support-ticket'),
                                'title' => __('Overdue Tickets', 'js-support-ticket'), 'href' => '#', 'tab' => '3'));
                        }
                        jsst_report_tile(array('pct' => $jsst_pc['closed'], 'fill' => 'js-ticket-close', 'tone' => 'js-ticket-red',
                            'count' => $jsst_n('closeticket'), 'label' => __('Closed', 'js-support-ticket'),
                            'title' => __('Close Ticket', 'js-support-ticket'), 'href' => '#', 'tab' => '4'));
                        jsst_report_tile(array('pct' => $jsst_pc['all'], 'fill' => 'js-ticket-allticket', 'tone' => 'js-ticket-blue',
                            'count' => $jsst_n('allticket'), 'label' => __('All Tickets', 'js-support-ticket'),
                            'title' => __('All Tickets', 'js-support-ticket'), 'href' => '#', 'tab' => '5'));
                        ?>
                    </div>
                </div>
            </div>

            <?php jsst_report_section_open(__('Tickets By Statuses And Priorities', 'js-support-ticket'));
                  jsst_report_graph('stack_chart_horizontal');
                  jsst_report_section_close(); ?>

            <div class="jsst-cards jsst-cards-2">
                <?php jsst_report_section_open(__('Tickets By Departments', 'js-support-ticket'), array('half' => true));
                      jsst_report_graph('pie3d_chart1');
                      jsst_report_section_close(); ?>
                <?php jsst_report_section_open(__('Tickets By Priorities', 'js-support-ticket'), array('half' => true));
                      jsst_report_graph('pie3d_chart2');
                      jsst_report_section_close(); ?>
                <?php jsst_report_section_open(__('Tickets By Statuses', 'js-support-ticket'), array('half' => true));
                      jsst_report_graph('bar_chart');
                      jsst_report_section_close(); ?>
                <?php jsst_report_section_open(__('Tickets By Channels', 'js-support-ticket'), array('half' => true));
                      jsst_report_graph('stack_chart');
                      jsst_report_section_close(); ?>
            </div>

            <?php /* The container only where there is a chart to put in it -
                     the same condition the script above uses, so an empty
                     Agents list leaves no titled empty box behind. */
            if (in_array('agent', jssupportticket::$_active_addons)
                    && !empty(jssupportticket::$jsst_data['slice_chart'])) {
                jsst_report_section_open(__('Tickets By Agents', 'js-support-ticket'));
                jsst_report_graph('slice_chart');
                jsst_report_section_close();
            } ?>
        </div>
  </div>
</div>
