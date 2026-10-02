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
<?php JSSTmessage::getMessage(); ?>
<?php $jsst_formdata = JSSTformfield::getFormData(); ?>
<?php
$jsst_js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
$jsst_jssupportticket_js ='
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
            dateFormat: "'. esc_html($jsst_js_scriptdateformat).'"
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
wp_add_inline_script('ticket-google-charts-handle',$jsst_jssupportticket_js);
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
			'. wp_kses(jssupportticket::$jsst_data["line_chart_json_array"], JSST_ALLOWED_TAGS).'
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
	/* This used to read `chart.draw(data, options)`, but chart, data and
	   options are locals of drawChart() - so every resize threw a
	   ReferenceError and the chart never redrew, which is why a narrowed
	   window left a chart wider than its card and scrolled the page
	   sideways. Redrawing means calling drawChart() again. Debounced,
	   because a drag fires resize continuously and each call is a full
	   re-render. */
	var jsstResizeTimer = null;
	/* Guarded on the WIDTH, not on the event: a redraw changes the page
	   height, which can toggle the vertical scrollbar, which fires resize
	   again - a loop that pins the renderer. */
	var jsstLastWidth = jQuery(window).width();
	function resizeCharts () {
	    if (jQuery(window).width() === jsstLastWidth) { return; }
	    jsstLastWidth = jQuery(window).width();
	    clearTimeout(jsstResizeTimer);
	    jsstResizeTimer = setTimeout(function () {
	        try { drawChart(); } catch (e) {}
	    }, 200);
	}
	jQuery(window).resize(resizeCharts);
';
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
?>
<?php JSSTlayout::adminUserPicker(); ?>
<?php JSSTmessage::getMessage(); ?>

<?php
$jsst_t_name = 'getstaffmemberexport';
$jsst_link_export = admin_url('admin.php?page=export&task='.esc_attr($jsst_t_name).'&action=jstask&uid='.esc_attr(jssupportticket::$jsst_data['filter']['uid']).'&date_start='.esc_attr(jssupportticket::$jsst_data['filter']['date_start']).'&date_end='.esc_attr(jssupportticket::$jsst_data['filter']['date_end']));
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
         'title'   => __("Agent Reports", 'js-support-ticket'),
         'actions' => $jsst_headactions,
     ));
     ?>
        <div id="jsstadmin-data-wrp">
            <?php
            require_once(dirname(__FILE__) . '/report_common.php');
            $jsst_totals = isset(jssupportticket::$jsst_data['ticket_total']) ? jssupportticket::$jsst_data['ticket_total'] : array();
            $jsst_pc = jsst_report_percents($jsst_totals);
            $jsst_pc['pending'] = (empty($jsst_totals['allticket']) || empty($jsst_totals['pendingticket'])) ? 0
                : (int) round(((int) $jsst_totals['pendingticket'] / (int) $jsst_totals['allticket']) * 100);
            $jsst_n = function ($jsst_key) use ($jsst_totals) { return isset($jsst_totals[$jsst_key]) ? (int) $jsst_totals[$jsst_key] : 0; };
            ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('What the desk is carrying, and how it divides between the people carrying it.', 'js-support-ticket')); ?>
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
                        jsst_report_tile(array('pct' => $jsst_pc['pending'], 'fill' => 'js-ticket-allticket', 'tone' => 'js-ticket-blue',
                            'count' => $jsst_n('pendingticket'), 'label' => __('Pending', 'js-support-ticket'),
                            'title' => __('Pending Tickets', 'js-support-ticket'), 'href' => '#', 'tab' => '3'));
                        if (in_array('overdue', jssupportticket::$_active_addons)) {
                            jsst_report_tile(array('pct' => $jsst_pc['overdue'], 'fill' => 'js-ticket-overdue', 'tone' => 'js-ticket-orange',
                                'count' => $jsst_n('overdueticket'), 'label' => __('Overdue', 'js-support-ticket'),
                                'title' => __('Overdue Tickets', 'js-support-ticket'), 'href' => '#', 'tab' => '4'));
                        }
                        jsst_report_tile(array('pct' => $jsst_pc['closed'], 'fill' => 'js-ticket-close', 'tone' => 'js-ticket-red',
                            'count' => $jsst_n('closeticket'), 'label' => __('Closed', 'js-support-ticket'),
                            'title' => __('Close Ticket', 'js-support-ticket'), 'href' => '#', 'tab' => '5'));
                        ?>
                    </div>
                </div>
            </div>

            <?php
            $jsst_curdate   = date_i18n('Y-m-d');
            $jsst_enddate   = date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime("now -1 month"));
            $jsst_date_start = !empty(jssupportticket::$jsst_data['filter']['date_start']) ? jssupportticket::$jsst_data['filter']['date_start'] : $jsst_curdate;
            $jsst_date_end   = !empty(jssupportticket::$jsst_data['filter']['date_end']) ? jssupportticket::$jsst_data['filter']['date_end'] : $jsst_enddate;
            $jsst_uid        = !empty(jssupportticket::$jsst_data['filter']['uid']) ? jssupportticket::$jsst_data['filter']['uid'] : '';
            $jsst_staffname  = !empty(jssupportticket::$jsst_data['filter']['staffname']) ? jssupportticket::$jsst_data['filter']['staffname'] : '';
            ?>
            <?php /* The dates were two unlabelled boxes reading "21-08-2026" and
                     "21-09-2026", which does not say which end is which. They are
                     ordinary labelled fields now. `a#userpopup` keeps its id and its
                     tag: every selector for the picker is tag-qualified, and on this
                     screen `a#userpopup` and the dialog's `div#userpopup` share the
                     id on purpose. */ ?>
            <form class="js-filter-form js-report-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reports&jstlay=staffreport"),"reports")); ?>">
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
                            <div class="jsst-frow jsst-frow-md">
                                <label class="jsst-flabel" for="username-text"><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval jsst-pickfield">
                                    <?php /* One input either way. The old markup put it inside
                                             `#username-div` when a name was chosen and beside it when
                                             not, for no reason any script depends on - the picker
                                             writes to `input#username-text` and `input#uid`, and
                                             `div#username-div` is only ever read on myprofile.php.
                                             The wrapper stays because it costs nothing and the id is
                                             shared with five other screens. */ ?>
                                    <div id="username-div"><input class="js-form-input-field" type="text" value="<?php echo esc_attr($jsst_staffname); ?>" id="username-text" readonly="readonly" data-validation="required" /></div>
                                    <a href="#" id="userpopup" class="jsst-act" title="<?php echo esc_attr(__('Select User', 'js-support-ticket')); ?>"><?php echo esc_html(__('Select User', 'js-support-ticket')); ?></a>
                                </div>
                            </div>
                            <div class="jsst-frow jsst-frow-action">
                                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                        <?php echo wp_kses(JSSTformfield::hidden('uid', $jsst_uid), JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </form>

            <?php jsst_report_section_open(__('Overall Report', 'js-support-ticket'));
                  jsst_report_graph('curve_chart');
                  jsst_report_section_close(); ?>

            <?php jsst_report_section_open(__('Agents', 'js-support-ticket')); ?>
                <?php if (!empty(jssupportticket::$jsst_data['staffs_report'])) {
                    foreach (jssupportticket::$jsst_data['staffs_report'] AS $jsst_agent) {
                        $jsst_all = isset($jsst_agent->allticket) ? (int) $jsst_agent->allticket : 0;
                        $jsst_ppc = function ($jsst_v) use ($jsst_all) {
                            return ($jsst_all === 0 || empty($jsst_v)) ? 0 : (int) round(((int) $jsst_v / $jsst_all) * 100);
                        };
                        $jsst_agentname = ($jsst_agent->firstname && $jsst_agent->lastname)
                            ? $jsst_agent->firstname . ' ' . $jsst_agent->lastname : $jsst_agent->display_name;
                        $jsst_username = $jsst_agent->display_name ? $jsst_agent->display_name : $jsst_agent->user_nicename;
                        $jsst_email = $jsst_agent->email ? $jsst_agent->email : $jsst_agent->user_email;
                        if ($jsst_agent->photo) {
                            $jsst_maindir = wp_upload_dir();
                            $jsst_imageurl = $jsst_maindir['baseurl'] . "/" . jssupportticket::$_config['data_directory'] . "/staffdata/staff_" . esc_attr($jsst_agent->id) . "/" . esc_attr($jsst_agent->photo);
                        } else {
                            $jsst_imageurl = JSST_PLUGIN_URL . "includes/images/user.png";
                        }
                        $jsst_href = admin_url('admin.php?page=reports&jstlay=staffdetailreport&id=' . esc_attr($jsst_agent->id)
                            . '&date_start=' . jssupportticket::$jsst_data['filter']['date_start']
                            . '&date_end=' . jssupportticket::$jsst_data['filter']['date_end']);
                        if (in_array('timetracking', jssupportticket::$_active_addons)) {
                            $jsst_hours = floor($jsst_agent->time[0] / 3600);
                            $jsst_mins  = floor(floor($jsst_agent->time[0] / 60) % 60);
                            $jsst_secs  = floor($jsst_agent->time[0] % 60);
                            $jsst_avgtime = sprintf('%02d:%02d:%02d', $jsst_hours, $jsst_mins, $jsst_secs);
                        } ?>
                        <div class="jsst-reprow">
                            <a class="jsst-repwho" href="<?php echo esc_url($jsst_href); ?>" title="<?php echo esc_attr(__('Staff', 'js-support-ticket')); ?>">
                                <img class="jsst-repface" alt="<?php echo esc_attr(__('agent image', 'js-support-ticket')); ?>" src="<?php echo esc_url($jsst_imageurl); ?>" />
                                <span>
                                    <span class="jsst-repname"><?php echo esc_html($jsst_agentname); ?></span>
                                    <span class="jsst-repsub"><?php echo esc_html($jsst_username); ?></span>
                                    <span class="jsst-repsub"><?php echo esc_html($jsst_email); ?></span>
                                </span>
                            </a>
                            <div class="jsst-repstats">
                                <div class="jsst-statrow">
                                    <?php
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_agent->openticket), 'fill' => 'js-ticket-open',
                                        'tone' => 'js-ticket-green', 'count' => (int) $jsst_agent->openticket, 'label' => __('Open', 'js-support-ticket'),
                                        'title' => __('Open Tickets', 'js-support-ticket')));
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_agent->answeredticket), 'fill' => 'js-ticket-answer',
                                        'tone' => 'js-ticket-brown', 'count' => (int) $jsst_agent->answeredticket, 'label' => __('Answered', 'js-support-ticket'),
                                        'title' => __('answered ticket', 'js-support-ticket')));
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_agent->pendingticket), 'fill' => 'js-ticket-allticket',
                                        'tone' => 'js-ticket-blue', 'count' => (int) $jsst_agent->pendingticket, 'label' => __('Pending', 'js-support-ticket'),
                                        'title' => __('Pending Tickets', 'js-support-ticket')));
                                    if (in_array('overdue', jssupportticket::$_active_addons)) {
                                        jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_agent->overdueticket), 'fill' => 'js-ticket-overdue',
                                            'tone' => 'js-ticket-orange', 'count' => (int) $jsst_agent->overdueticket, 'label' => __('Overdue', 'js-support-ticket'),
                                            'title' => __('Overdue Tickets', 'js-support-ticket')));
                                    }
                                    jsst_report_tile(array('small' => true, 'pct' => $jsst_ppc($jsst_agent->closeticket), 'fill' => 'js-ticket-close',
                                        'tone' => 'js-ticket-red', 'count' => (int) $jsst_agent->closeticket, 'label' => __('Closed', 'js-support-ticket'),
                                        'title' => __('Close Ticket', 'js-support-ticket')));
                                    ?>
                                    <?php /* Rating and time are figures, not proportions - no ring. */ ?>
                                    <?php if (in_array('feedback', jssupportticket::$_active_addons)) { ?>
                                        <span class="jsst-stat jsst-stat-sm jsst-stat-plain" title="<?php echo esc_attr(__('Rating', 'js-support-ticket')); ?>">
                                            <span class="jsst-stat-fig"><?php
                                                if ($jsst_agent->avragerating > 0) {
                                                    echo esc_html(round($jsst_agent->avragerating, 1)) . '<span class="jsst-stat-of">/5</span>';
                                                } else {
                                                    echo esc_html(__('NA', 'js-support-ticket'));
                                                } ?></span>
                                            <span class="jsst-stat-label"><?php echo esc_html(__('Average Rating', 'js-support-ticket')); ?></span>
                                        </span>
                                    <?php }
                                    if (in_array('timetracking', jssupportticket::$_active_addons)) { ?>
                                        <span class="jsst-stat jsst-stat-sm jsst-stat-plain" title="<?php echo esc_attr(__('Average Time', 'js-support-ticket')); ?>">
                                            <span class="jsst-stat-fig"><?php echo esc_html($jsst_avgtime);
                                                if ($jsst_agent->time[1] != 0) { ?><span class="jsst-stat-of">!</span><?php } ?></span>
                                            <span class="jsst-stat-label"><?php echo esc_html(__('Average Time', 'js-support-ticket')); ?></span>
                                        </span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php }
                    if (jssupportticket::$jsst_data[1]) {
                        echo '<div class="tablenav"><div class="tablenav-pages"' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
                    }
                } else {
                    JSSTlayout::getNoRecordFound();
                } ?>
            <?php jsst_report_section_close(); ?>
        </div>
    </div>
</div>
