<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css');
if (jssupportticket::$_config['offline'] == 2) {
    if (JSSTincluder::getObjectClass('user')->uid() != 0) {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css');
        $jssupportticket_js ='
            ajaxurl = "'. esc_url(admin_url("admin-ajax.php")) .'";
            jQuery(document).ready(function ($) {
                $(".custom_date").datepicker({dateFormat: "yy-mm-dd"});';
                if(isset(jssupportticket::$_data["filter"]["combinesearch"])){
                    $combinesearch = jssupportticket::$_data["filter"]["combinesearch"];
                } else{
                    $combinesearch = '';
                }
                $jssupportticket_js .='
                var combinesearch = "'. $combinesearch .'";
                if (combinesearch == true) {
                    doVisible();
                    $("#js-filter-wrapper-toggle-area").show();
                }
                jQuery("#js-search-filter-toggle-btn").click(function (event) {
                    event.preventDefault();
                    if (jQuery("#js-filter-wrapper-toggle-area").is(":visible")) {
                        jQuery("#js-search-filter-toggle-btn").text("'. esc_html(__("Show All","js-support-ticket")) .'");
                        //doVisible();
                    } else {
                        jQuery("#js-search-filter-toggle-btn").text("'. esc_html(__("Show Less","js-support-ticket")) .'");
                        //jQuery(".js-filter-wrapper-toggle-ticketid").hide();
                        //jQuery("#js-filter-wrapper-toggle-minus").hide();
                        //jQuery("#js-filter-wrapper-toggle-plus").show();
                    }
                    jQuery("#js-filter-wrapper-toggle-search").toggle();
                    jQuery("#js-filter-wrapper-toggle-area").toggle();
                });

                /*$("#js-filter-wrapper-toggle-btn").click(function () {
                    if ($("#js-filter-wrapper-toggle-search").is(":visible")) {
                        doVisible();
                    } else {
                        $("#js-filter-wrapper-toggle-search").show();
                        $(".js-filter-wrapper-toggle-ticketid").hide();
                        $("#js-filter-wrapper-toggle-area").hide();
                        $("#js-filter-wrapper-toggle-minus").hide();
                        $("#js-filter-wrapper-toggle-plus").show();
                    }
                });*/

                /*jQuery("a.jssortlink").click(function(e){
                    e.preventDefault();
                    var sortby = jQuery(this).attr("href");
                    jQuery("input#sortby").val(sortby);
                    jQuery("form#jssupportticketform").submit();
                });*/
                jQuery("select.js-ticket-sorting-select").on("change",function(e){
                    e.preventDefault();
                    var sortby = jQuery(".js-ticket-sorting-select option:selected").val();
                    jQuery("input#sortby").val(sortby);
                    jQuery("form#jssupportticketform").submit();
                });
                jQuery("a.js-admin-sort-btn").on("click",function(e){
                    e.preventDefault();
                    var sortby = jQuery(".js-ticket-sorting-select option:selected").val();
                    //alert(sortby);
                    jQuery("input#sortby").val(sortby);
                    jQuery("form#jssupportticketform").submit();
                });
                jQuery("a.js-myticket-link").click(function(e){
                    e.preventDefault();
                    var list = jQuery(this).attr("data-tab-number");
                    jQuery("input#list").val(list);
                    jQuery("form#jssupportticketform").submit();
                });
                jQuery("span.js-ticket-closedby-wrp").hover(
                    function(e){
                        jQuery(this).find("span.js-ticket-closed-date").css("display","inline-block");
                    },
                    function(e){
                        jQuery(this).find("span.js-ticket-closed-date").css("display","none");
                    }
                );


                function doVisible() {
                    $("#js-filter-wrapper-toggle-search").hide();
                    $(".js-filter-wrapper-toggle-ticketid").show();
                    $("#js-filter-wrapper-toggle-area").show();
                    $("#js-filter-wrapper-toggle-minus").show();
                    $("#js-filter-wrapper-toggle-plus").hide();
                }
            });
            function resetForm() {
                var form = jQuery("form#jssupportticketform");
                form.find("input[type=text], input[type=email], input[type=password], textarea").val("");
                form.find("input:checkbox").removeAttr("checked");
                form.find("select").prop("selectedIndex", 0);
                form.find("input[type=\"radio\"]").prop("checked", false);
                return true;
            }
        ';
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        JSSTmessage::getMessage();
    }
    /* JSSTbreadcrumbs::getBreadcrumbs(); */
    include_once(JSST_PLUGIN_PATH . 'includes/header.php');
    if (JSSTincluder::getObjectClass('user')->uid() != 0) {
        $list = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['list'] : 1;
        $open = ($list == 1) ? 'active' : '';
        $answered = ($list == 2) ? 'active' : '';
        $overdue = ($list == 3) ? 'active' : '';
        $myticket = ($list == 4) ? 'active' : '';
        $field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1);
        $show_field = JSSTincluder::getJSModel('fieldordering')->getFieldsForListing(1);
        $open_percentage = 0;
        $close_percentage = 0;
        $answered_percentage = 0;
        $allticket_percentage = 0;
        if(isset(jssupportticket::$_data['count']) && isset(jssupportticket::$_data['count']['allticket']) && jssupportticket::$_data['count']['allticket'] != 0){
            $open_percentage = round((jssupportticket::$_data['count']['openticket'] / jssupportticket::$_data['count']['allticket']) * 100);
            $close_percentage = round((jssupportticket::$_data['count']['closedticket'] / jssupportticket::$_data['count']['allticket']) * 100);
            $answered_percentage = round((jssupportticket::$_data['count']['answeredticket'] / jssupportticket::$_data['count']['allticket']) * 100);
        }
        if(isset(jssupportticket::$_data['count']) && isset(jssupportticket::$_data['count']['allticket']) && jssupportticket::$_data['count']['allticket'] != 0){
            $allticket_percentage = 100;
        }
        ?>

        <!-- Top Circle Count Boxes -->
        <div class="js-row js-ticket-top-cirlce-count-wrp">
            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                <a class="js-ticket-green js-myticket-link <?php echo esc_attr($open); ?>" href="#" data-tab-number="1">
                    <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($open_percentage); ?>" >
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
                    <span class="js-ticket-circle-count-text js-ticket-green">
                        <?php
                            echo esc_html(__('Open', 'js-support-ticket'));
                            if(jssupportticket::$_config['count_on_myticket'] == 1)
                            echo ' ( ' . esc_html(jssupportticket::$_data['count']['openticket']) . ' )';
                        ?>
                    </span>
                </a>
            </div>
            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                <a class="js-ticket-red js-myticket-link <?php echo esc_attr($answered); ?>" href="#" data-tab-number="2">
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
                    <span class="js-ticket-circle-count-text js-ticket-red">
                        <?php
                            echo esc_html(__('Closed', 'js-support-ticket'));
                            if(jssupportticket::$_config['count_on_myticket'] == 1)
                            echo ' ( ' . esc_html(jssupportticket::$_data['count']['closedticket']) . ' )';
                        ?>
                    </span>
                </a>
            </div>
            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                <a class="js-ticket-blue js-myticket-link <?php echo esc_attr($overdue); ?>" href="#" data-tab-number="3">
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
                    <span class="js-ticket-circle-count-text js-ticket-blue">
                        <?php
                            echo esc_html(__('Answered', 'js-support-ticket'));
                            if(jssupportticket::$_config['count_on_myticket'] == 1)
                            echo ' ( ' . esc_html(jssupportticket::$_data['count']['answeredticket']) . ' )';
                        ?>
                    </span>
                </a>
            </div>
            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                <a class="js-ticket-brown js-myticket-link <?php echo esc_attr($myticket); ?>" href="#" data-tab-number="4">
                    <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($allticket_percentage); ?>">
                        <div class="js-mr-rp" data-progress="<?php echo esc_attr($allticket_percentage); ?>">
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
                    <span class="js-ticket-circle-count-text js-ticket-brown">
                        <?php
                            echo esc_html(__('All Tickets', 'js-support-ticket'));
                            if(jssupportticket::$_config['count_on_myticket'] == 1)
                            echo ' ( ' . esc_html(jssupportticket::$_data['count']['allticket']) . ' )';
                        ?>
                    </span>
                </a>
            </div>
        </div>

        <!-- Search Form -->
        <div class="js-ticket-search-wrp">
            <?php /*<div class="js-ticket-search-heading"><?php echo esc_html(__('Search Ticket', 'js-support-ticket'));?></div>*/ ?>
            <div class="js-ticket-form-wrp">
                <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="POST" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket')),"my-ticket")); ?>">
                    <div class="js-filter-wrapper">
                        <div class="js-filter-form-fields-wrp js-col-md-7" id="js-filter-wrapper-toggle-search">
                            <?php echo wp_kses(JSSTformfield::text('jsst-ticketsearchkeys', isset(jssupportticket::$_data['filter']['ticketsearchkeys']) ? jssupportticket::$_data['filter']['ticketsearchkeys'] : '', array('class' => 'js-ticket-input-field','placeholder' => esc_html(__('Ticket ID', 'js-support-ticket')) . ' ' . esc_html(__('Or', 'js-support-ticket')) . ' ' . esc_attr($field_array['email']) . ' ' . esc_html(__('Or', 'js-support-ticket')) . ' ' . esc_attr($field_array['subject']))), JSST_ALLOWED_TAGS); ?>
                        </div>
                        <div id="js-filter-wrapper-toggle-area" class="js-filter-wrapper-toggle-ticketid">
                            <div class="js-col-md-3 js-filter-form-fields-wrp js-filter-wrapper-toggle-ticketid">
                                <?php echo wp_kses(JSSTformfield::text('jsst-ticket', isset(jssupportticket::$_data['filter']['ticketid']) ? jssupportticket::$_data['filter']['ticketid'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => esc_html(__('Ticket ID', 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::text('jsst-subject', isset(jssupportticket::$_data['filter']['subject']) ? jssupportticket::$_data['filter']['subject'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => jssupportticket::JSST_getVarValue($field_array['subject']))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::text('jsst-from', isset(jssupportticket::$_data['filter']['from']) ? jssupportticket::$_data['filter']['from'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => esc_html(__('From', 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::select('jsst-departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), isset(jssupportticket::$_data['filter']['departmentid']) ? jssupportticket::$_data['filter']['departmentid'] : '', esc_html(__('Select', 'js-support-ticket')).' '.esc_attr($field_array['department'])), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::text('jsst-email', isset(jssupportticket::$_data['filter']['email']) ? jssupportticket::$_data['filter']['email'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => jssupportticket::JSST_getVarValue($field_array['email']))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::select('jsst-priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), isset(jssupportticket::$_data['filter']['priorityid']) ? jssupportticket::$_data['filter']['priorityid'] : '', esc_html(__('Select', 'js-support-ticket')).' '.esc_attr($field_array['priority'])), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::text('jsst-datestart', isset(jssupportticket::$_data['filter']['datestart']) ? jssupportticket::$_data['filter']['datestart'] : '', array('class' => 'custom_date js-ticket-input-field', 'placeholder' => esc_html(__('Start Date', 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::text('jsst-dateend', isset(jssupportticket::$_data['filter']['dateend']) ? jssupportticket::$_data['filter']['dateend'] : '', array('class' => 'custom_date js-ticket-input-field', 'placeholder' => esc_html(__('End Date', 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <?php if(class_exists('WooCommerce') && in_array('woocommerce', jssupportticket::$_active_addons)){  ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::text('jsst-orderid', isset(jssupportticket::$_data['filter']['orderid']) ? jssupportticket::$_data['filter']['orderid'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => jssupportticket::JSST_getVarValue($field_array['wcorderid']))), JSST_ALLOWED_TAGS); ?>
                                </div>

                            <?php
                            }
                            if(in_array('easydigitaldownloads', jssupportticket::$_active_addons)){  ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::text('jsst-eddorderid', isset(jssupportticket::$_data['filter']['eddorderid']) ? jssupportticket::$_data['filter']['eddorderid'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => jssupportticket::JSST_getVarValue($field_array['eddorderid']))), JSST_ALLOWED_TAGS); ?>
                                </div>

                            <?php
                            }
                             $customfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
                                foreach ($customfields as $field) {
                                    JSSTincluder::getObjectClass('customfields')->formCustomFieldsForSearch($field, $k);
                                }  ?>
                        </div>
                        <div class="js-col-md-5 js-filter-button-wrp">
                            <a href="#" class="js-search-filter-btn" id="js-search-filter-toggle-btn">
                                <?php echo esc_html(__('Show All','js-support-ticket')); ?>
                            </a>
                            <?php echo wp_kses(JSSTformfield::submitbutton('jsst-go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'js-ticket-filter-button js-ticket-search-btn')), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::submitbutton('jsst-reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'js-ticket-filter-button js-ticket-reset-btn', 'onclick' => 'return resetForm();')), JSST_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('sortby', isset(jssupportticket::$_data['filter']['sortby']) ? jssupportticket::$_data['filter']['sortby'] :'' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('list', $list), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('jshdlay', 'myticket'), JSST_ALLOWED_TAGS); ?>
                </form>
            </div>
        </div>
        <!-- Sorting Wrapper -->
        <?php
        $link = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket','list'=> jssupportticket::$_data['list']));
        if (jssupportticket::$_sortorder == 'ASC')
            $img = "sorting-1.png";
        else
            $img = "sorting-2.png";
        ?>
        <div class="js-ticket-sorting js-col-md-12">
            <?php /*
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_attr(jssupportticket::$_sortlinks['subject']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'subject') echo 'selected' ?>"><?php echo esc_html($field_array['subject']); ?><?php if (jssupportticket::$_sorton == 'subject') { ?> <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_attr(jssupportticket::$_sortlinks['priority']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'priority') echo 'selected' ?>"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['priority'])); ?><?php if (jssupportticket::$_sorton == 'priority') { ?> <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_attr(jssupportticket::$_sortlinks['ticketid']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'ticketid') echo 'selected' ?>"><?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?><?php if (jssupportticket::$_sorton == 'ticketid') { ?> <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_attr(jssupportticket::$_sortlinks['isanswered']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'isanswered') echo 'selected' ?>"><?php echo esc_html(__('Answered', 'js-support-ticket')); ?><?php if (jssupportticket::$_sorton == 'isanswered') { ?> <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_attr(jssupportticket::$_sortlinks['status']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'status') echo 'selected' ?>"><?php echo esc_html($field_array['status']); ?><?php if (jssupportticket::$_sorton == 'status') { ?> <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_attr(jssupportticket::$_sortlinks['created']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'created') echo 'selected' ?>"><?php echo esc_html(__('Created', 'js-support-ticket')); ?><?php if (jssupportticket::$_sorton == 'created') { ?> <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($img) ?>"> <?php } ?></a></span>
            */ ?>
            <div class="js-ticket-sorting-left">
                <div class="js-ticket-sorting-heading">
                    <?php echo esc_html(__('All Tickets','js-support-ticket')); ?>
                </div>
            </div>
            <div class="js-ticket-sorting-right">
                <div class="js-ticket-sort">
                    <select class="js-ticket-sorting-select">
                        <?php echo esc_html($field_array['subject']); ?>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['subject']); ?>" <?php if (jssupportticket::$_sorton == 'subject') echo 'selected' ?>><?php echo esc_html(__("Subject",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['priority']); ?>"  <?php if (jssupportticket::$_sorton == 'priority') echo 'selected' ?>><?php echo esc_html(__("Priority",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['ticketid']); ?>"  <?php if (jssupportticket::$_sorton == 'ticketid') echo 'selected' ?>><?php echo esc_html(__("Ticket ID",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['isanswered']); ?>"  <?php if (jssupportticket::$_sorton == 'isanswered') echo 'selected' ?>><?php echo esc_html(__("Answered",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['status']); ?>"  <?php if (jssupportticket::$_sorton == 'status') echo 'selected' ?>><?php echo esc_html(__("Status",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['created']); ?>"  <?php if (jssupportticket::$_sorton == 'created') echo 'selected' ?>><?php echo esc_html(__("Created",'js-support-ticket')); ?></option>
                    </select>
                    <a href="#" class="js-admin-sort-btn" title="<?php echo esc_html(__('sort','js-support-ticket')); ?>">
                        <img alt="<?php echo esc_html(__('sort','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/' . esc_attr($img) ?>">
                    </a>
                </div>
            </div>
        </div>

        <?php
        if (!empty(jssupportticket::$_data[0])) {
            foreach (jssupportticket::$_data[0] AS $ticket) {
                if ($ticket->status == 0) {
                    $style = "#5bb12f;";
                    $status = esc_html(__('New', 'js-support-ticket'));
                } elseif ($ticket->status == 1) {
                    $style = "#28abe3;";
                    $status = esc_html(__('Waiting Reply', 'js-support-ticket'));
                } elseif ($ticket->status == 2) {
                    $style = "#69d2e7;";
                    $status = esc_html(__('In Progress', 'js-support-ticket'));
                } elseif ($ticket->status == 3) {
                    $style = "#FFB613;";
                    $status = esc_html(__('Replied', 'js-support-ticket'));
                } elseif ($ticket->status == 4) {
                    $style = "#ed1c24;";
                    $status = esc_html(__('Closed', 'js-support-ticket'));
                } elseif ($ticket->status == 5) {
                    $style = "#dc2742;";
                    $status = esc_html(__('Close and merge', 'js-support-ticket'));
                }
                $ticketviamail = '';
                if ($ticket->ticketviaemail == 1)
                    $ticketviamail = esc_html(__('Created via Email', 'js-support-ticket'));
                ?>
                <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                    <div class="js-col-xs-12 js-col-md-12 js-ticket-toparea">
                        <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                            <?php if (in_array('agent',jssupportticket::$_active_addons) && $ticket->staffphoto) { ?>
                                <img class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $ticket->staffid ,'jsstpageid'=>get_the_ID())));?> ">
                            <?php } else {
                                echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById($ticket->uid)), JSST_ALLOWED_TAGS);
                            } ?>
                        </div>
                        <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                            <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                <span class="js-ticket-value"><?php echo esc_html($ticket->name); ?></span>
                                <?php if ($ticket->status == 4 && jssupportticket::$_config['show_closedby_on_user_tickets'] == 1) { ?>
                                    <span class="js-ticket-closedby-wrp">
                                        <span class="js-ticket-closedby">
                                            <?php echo esc_html(JSSTincluder::getJSModel('ticket')->getClosedBy($ticket->closedby)); ?>
                                        </span>
                                        <span class="js-ticket-closed-date">
                                            <?php echo esc_html("Closed on"). " " . esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->closed))); ?>
                                        </span>
                                    </span>
                                <?php } ?>
                            </div>
                            <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                <a class="js-ticket-title-anchor" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=> $ticket->id))); ?>"><?php echo esc_html($ticket->subject); ?></a>
                            </div>
                            <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                <span class="js-ticket-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['department'])); ?>&nbsp;:&nbsp;</span>
                                <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->departmentname)); ?></span>
                            </div>
                            <?php
                            jssupportticket::$_data['custom']['ticketid'] = $ticket->id;
                            $customfields = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, 1);
                            foreach ($customfields as $field) {
                                $ret = JSSTincluder::getObjectClass('customfields')->showCustomFields($field,1, $ticket->params);
                                ?>
                                <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                    <span class="js-ticket-field-title"><?php echo esc_html($ret['title']); ?>&nbsp;:&nbsp;</span>
                                    <span class="js-ticket-value"><?php echo wp_kses($ret['value'], JSST_ALLOWED_TAGS); ?></span>
                                </div>
                                <?php
                            }
                            if ($ticket->ticketviaemail == 1){  ?>
                                <span class="js-ticket-value js-ticket-creade-via-email-spn"><?php echo esc_html($ticketviamail); ?></span>
                            <?php } ?>
                            <span class="js-ticket-wrapper-textcolor" style="background:<?php echo esc_attr($ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?></span>
                            <span class="js-ticket-status" style="color:<?php echo esc_attr($style); ?>">
                                <?php
                                $counter = 'one';
                                if ($ticket->lock == 1) {
                                    ?>
                                    <img class="ticketstatusimage <?php echo esc_attr($counter);
                                    $counter = 'two'; ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/lock.png"; ?>" title="<?php echo esc_html(__('The ticket is locked', 'js-support-ticket')); ?>" />
                                <?php } ?>
                                <?php if ($ticket->isoverdue == 1) { ?>
                                        <img class="ticketstatusimage <?php echo esc_attr($counter); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/over-due.png"; ?>" title="<?php echo esc_html(__('This ticket is marked as overdue', 'js-support-ticket')); ?>" />
                                <?php } ?>
                                <?php echo esc_html($status); ?>
                            </span>
                        </div>
                        <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-data-tit"><?php echo esc_html(__('Ticket ID', 'js-support-ticket')). ' : '; ?></div>
                                <div class="js-ticket-data-val"><?php echo esc_html($ticket->ticketid); ?></div>
                            </div>
                            <?php if (empty($ticket->lastreply) || $ticket->lastreply == '0000-00-00 00:00:00') { ?>
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-data-tit"><?php echo esc_html(__('Created','js-support-ticket')). ' : '; ?></div>
                                <div class="js-ticket-data-val"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->created))); ?></div>
                            </div>
                            <?php } else { ?>
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-data-tit"><?php echo esc_html(__('Last Reply', 'js-support-ticket')). ' : '; ?></div>
                                <div class="js-ticket-data-val"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->lastreply))); ?></div>
                            </div>
                            <?php } ?>
                            <?php
                            if (in_array('agent',jssupportticket::$_active_addons)) {
                                if (jssupportticket::$_config['show_assignto_on_user_tickets'] == 1) { ?>
                                    <div class="js-ticket-data-row">
                                        <div class="js-ticket-data-tit"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['assignto'])). ' : '; ?></div>
                                        <div class="js-ticket-data-val"><?php echo esc_html($ticket->staffname); ?></div>
                                    </div>
                                <?php
                                }
                            } ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            if (jssupportticket::$_data[1]) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$_data[1]) . '</div></div>';
            }
        } else { // Record Not FOund
            JSSTlayout::getNoRecordFound();
        }
    } else {// User is guest
        $redirect_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket'));
        $redirect_url = jssupportticketphplib::JSST_safe_encoding($redirect_url);
        JSSTlayout::getUserGuest($redirect_url);
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
