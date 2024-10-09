<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css');
$id = JSSTrequest::getVar('id');
wp_enqueue_script('ticket-google-charts', JSST_PLUGIN_URL . 'includes/js/google-charts.js');
wp_register_script( 'ticket-google-charts-handle', '' );
wp_enqueue_script( 'ticket-google-charts-handle' );
$jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $('.custom_date').datepicker({
            dateFormat: 'yy-mm-dd'
        });
	});

    function resetFrom(){
        document.getElementById('date_start').value = '';
        document.getElementById('date_end').value = '';
        document.getElementById('jssupportticketform').submit();
    }
    ";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
$jssupportticket_js ="
    google.load('visualization', '1', {packages:['corechart']});
	google.setOnLoadCallback(drawChart);

    function drawChart() {
      	var data = new google.visualization.DataTable();
		data.addColumn('date', '". esc_html(__('Dates','js-support-ticket')) ."');
        data.addColumn('number', '". esc_html(__('Minutes','js-support-ticket')) ."');
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
 ?>

<?php
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
    <span class="js-adminhead-title"> <a class="jsanchor-backlink" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=staffdetailreport&id='.esc_attr($id)));?>"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/back-icon.png" /></a> <span class="jsheadtext"><?php echo esc_html(__("Report By Staff Member", 'js-support-ticket')); ?></span>
    </span>
    <a href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=staffreport&date_start='.jssupportticket::$_data['filter']['date_start'].'&date_end='.jssupportticket::$_data['filter']['date_end'])); ?>"></a>
    <form class="js-filter-form js-report-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reports&jstlay=stafftimereport&id=".esc_attr($id)),"reports")); ?>">
        <?php
            $curdate = date_i18n('Y-m-d');
            $enddate = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
            $date_start = !empty(jssupportticket::$_data['filter']['date_start']) ? jssupportticket::$_data['filter']['date_start'] : $curdate;
            $date_end = !empty(jssupportticket::$_data['filter']['date_end']) ? jssupportticket::$_data['filter']['date_end'] : $enddate;
        	echo wp_kses(JSSTformfield::text('date_start', $date_start, array('class' => 'custom_date','placeholder' => esc_html(__('Start Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
        	echo wp_kses(JSSTformfield::text('date_end', $date_end, array('class' => 'custom_date','placeholder' => esc_html(__('End Date','js-support-ticket')))), JSST_ALLOWED_TAGS);
        	echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS);
    	?>
        <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button')), JSST_ALLOWED_TAGS); ?>
    	<?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
    </form>
    <span class="js-admin-subtitle"><?php echo esc_html(jssupportticket::$_data[0]['staffname']); ?></span>
    <div id="curve_chart" style="height:400px;width:98%; "></div>
  </div>
</div>
