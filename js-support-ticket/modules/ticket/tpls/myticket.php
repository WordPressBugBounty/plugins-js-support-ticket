<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css', array(), jssupportticket::$_config['productversion']);
if (jssupportticket::$_config['offline'] == 2) {
    if (JSSTincluder::getObjectClass('user')->uid() != 0) {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
        $jsst_jssupportticket_js ='
            ajaxurl = "'. esc_url(admin_url("admin-ajax.php")) .'";
            jQuery(document).ready(function ($) {
                $(".custom_date").datepicker({dateFormat: "yy-mm-dd"});';
                if(isset(jssupportticket::$jsst_data["filter"]["combinesearch"])){
                    $jsst_combinesearch = jssupportticket::$jsst_data["filter"]["combinesearch"];
                } else{
                    $jsst_combinesearch = '';
                }
                $jsst_jssupportticket_js .='
                var combinesearch = "'. $jsst_combinesearch .'";
                if (combinesearch == true) {
                    doVisible();
                    $("#js-filter-wrapper-toggle-area").show().addClass("js-ticket-search-visible");
                    jQuery("#js-search-filter-toggle-btn").text("'. esc_html(__("Show Less","js-support-ticket")) .'");
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
                    jQuery("#js-filter-wrapper-toggle-area").toggleClass("js-ticket-search-visible");
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
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        JSSTmessage::getMessage();
    }
    /* JSSTbreadcrumbs::getBreadcrumbs(); */
    include_once(JSST_PLUGIN_PATH . 'includes/header.php');
    if (JSSTincluder::getObjectClass('user')->uid() != 0) {
        $jsst_list = isset(jssupportticket::$_search['ticket']) ? jssupportticket::$_search['ticket']['list'] : 1;
        $jsst_open = ($jsst_list == 1) ? 'active' : '';
        $jsst_answered = ($jsst_list == 2) ? 'active' : '';
        $jsst_overdue = ($jsst_list == 3) ? 'active' : '';
        $jsst_myticket = ($jsst_list == 4) ? 'active' : '';
        $jsst_field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1);
        $jsst_search_field_array = JSSTincluder::getJSModel('fieldordering')->getUserSystemFieldsForSearch();
        $jsst_open_percentage = 0;
        $jsst_close_percentage = 0;
        $jsst_answered_percentage = 0;
        $jsst_allticket_percentage = 0;
        if(isset(jssupportticket::$jsst_data['count']) && isset(jssupportticket::$jsst_data['count']['allticket']) && jssupportticket::$jsst_data['count']['allticket'] != 0){
            $jsst_open_percentage = round((jssupportticket::$jsst_data['count']['openticket'] / jssupportticket::$jsst_data['count']['allticket']) * 100);
            $jsst_close_percentage = round((jssupportticket::$jsst_data['count']['closedticket'] / jssupportticket::$jsst_data['count']['allticket']) * 100);
            $jsst_answered_percentage = round((jssupportticket::$jsst_data['count']['answeredticket'] / jssupportticket::$jsst_data['count']['allticket']) * 100);
        }
        if(isset(jssupportticket::$jsst_data['count']) && isset(jssupportticket::$jsst_data['count']['allticket']) && jssupportticket::$jsst_data['count']['allticket'] != 0){
            $jsst_allticket_percentage = 100;
        }
        ?>

        <!-- Top Circle Count Boxes -->
        <div class="js-row js-ticket-top-cirlce-count-wrp">
            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                <a class="js-ticket-green js-myticket-link <?php echo esc_attr($jsst_open); ?>" href="#" data-tab-number="1">
                    <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_open_percentage); ?>" >
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
                    <span class="js-ticket-circle-count-text js-ticket-green">
                        <?php
                            echo esc_html(__('Open', 'js-support-ticket'));
                            if(jssupportticket::$_config['count_on_myticket'] == 1)
                            echo ' ( ' . esc_html(jssupportticket::$jsst_data['count']['openticket']) . ' )';
                        ?>
                    </span>
                </a>
            </div>
            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                <a class="js-ticket-red js-myticket-link <?php echo esc_attr($jsst_answered); ?>" href="#" data-tab-number="2">
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
                    <span class="js-ticket-circle-count-text js-ticket-red">
                        <?php
                            echo esc_html(__('Closed', 'js-support-ticket'));
                            if(jssupportticket::$_config['count_on_myticket'] == 1)
                            echo ' ( ' . esc_html(jssupportticket::$jsst_data['count']['closedticket']) . ' )';
                        ?>
                    </span>
                </a>
            </div>
            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                <a class="js-ticket-blue js-myticket-link <?php echo esc_attr($jsst_overdue); ?>" href="#" data-tab-number="3">
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
                    <span class="js-ticket-circle-count-text js-ticket-blue">
                        <?php
                            echo esc_html(__('Answered', 'js-support-ticket'));
                            if(jssupportticket::$_config['count_on_myticket'] == 1)
                            echo ' ( ' . esc_html(jssupportticket::$jsst_data['count']['answeredticket']) . ' )';
                        ?>
                    </span>
                </a>
            </div>
            <div class="js-col-xs-12 js-col-md-2 js-myticket-link js-ticket-myticket-link-myticket">
                <a class="js-ticket-brown js-myticket-link <?php echo esc_attr($jsst_myticket); ?>" href="#" data-tab-number="4">
                    <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_allticket_percentage); ?>">
                        <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_allticket_percentage); ?>">
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
                            echo ' ( ' . esc_html(jssupportticket::$jsst_data['count']['allticket']) . ' )';
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
                        <div class="js-filter-form-fields-wrp" id="js-filter-wrapper-toggle-search">
                            <?php
                            $jsst_emailaddress = '';
                            if(!empty($jsst_search_field_array['email'])) {
                                $jsst_emailaddress = ' ' . __('Or', 'js-support-ticket') . ' ' . jssupportticket::JSST_getVarValue($jsst_search_field_array['email']);
                            }
                            $jsst_subject = '';
                            if(!empty($jsst_search_field_array['subject'])) {
                                $jsst_subject = ' ' . __('Or', 'js-support-ticket') . ' ' . jssupportticket::JSST_getVarValue($jsst_search_field_array['subject']);
                            }
                            echo wp_kses(JSSTformfield::text('jsst-ticketsearchkeys', isset(jssupportticket::$jsst_data['filter']['ticketsearchkeys']) ? jssupportticket::$jsst_data['filter']['ticketsearchkeys'] : '', array('class' => 'js-ticket-input-field','placeholder' => esc_html(__('Ticket ID', 'js-support-ticket')) . $jsst_emailaddress . $jsst_subject)), JSST_ALLOWED_TAGS); ?>
                        </div>
                        <div id="js-filter-wrapper-toggle-area" class="js-filter-wrapper-toggle-ticketid">
                            <div class="js-col-md-3 js-filter-form-fields-wrp js-filter-wrapper-toggle-ticketid">
                                <?php echo wp_kses(JSSTformfield::text('jsst-ticket', isset(jssupportticket::$jsst_data['filter']['ticketid']) ? jssupportticket::$jsst_data['filter']['ticketid'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => esc_html(__('Ticket ID', 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <?php 
                            if (!empty($jsst_search_field_array['subject'])) { ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::text('jsst-subject', isset(jssupportticket::$jsst_data['filter']['subject']) ? jssupportticket::$jsst_data['filter']['subject'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => jssupportticket::JSST_getVarValue($jsst_search_field_array['subject']))), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php 
                            } ?>
                            <?php 
                            if (!empty($jsst_search_field_array['fullname'])) { ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::text('jsst-from', isset(jssupportticket::$jsst_data['filter']['from']) ? jssupportticket::$jsst_data['filter']['from'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => esc_html(__('From', 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php 
                            } ?>
                            <?php 
                            if (!empty($jsst_search_field_array['phone'])) { ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::text('jsst-phone', isset(jssupportticket::$jsst_data['filter']['phone']) ? jssupportticket::$jsst_data['filter']['phone'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => esc_html(jssupportticket::JSST_getVarValue($jsst_search_field_array['phone'])))), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php 
                            } ?>
                            <?php
                            if(!empty($jsst_search_field_array['product'])) { ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::select('jsst-productid', JSSTincluder::getJSModel('product')->getProductForCombobox(), isset(jssupportticket::$jsst_data['filter']['productid']) ? jssupportticket::$jsst_data['filter']['productid'] : '', esc_html(__('Select', 'js-support-ticket')).' '.esc_attr($jsst_search_field_array['product'])), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php
                            }
                            if(!empty($jsst_search_field_array['department'])) { ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::select('jsst-departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), isset(jssupportticket::$jsst_data['filter']['departmentid']) ? jssupportticket::$jsst_data['filter']['departmentid'] : '', esc_html(__('Select', 'js-support-ticket')).' '.esc_attr($jsst_search_field_array['department'])), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php
                            }
                            if(!empty($jsst_search_field_array['helptopic']) && in_array('helptopic', jssupportticket::$_active_addons)) { ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::select('jsst-helptopicid', JSSTincluder::getJSModel('helptopic')->getHelpTopicsForCombobox(), isset(jssupportticket::$jsst_data['filter']['helptopicid']) ? jssupportticket::$jsst_data['filter']['helptopicid'] : '', esc_html(__('Select', 'js-support-ticket')).' '.esc_attr($jsst_search_field_array['helptopic'])), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php
                            }
                            if(!empty($jsst_search_field_array['email'])) { ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::text('jsst-email', isset(jssupportticket::$jsst_data['filter']['email']) ? jssupportticket::$jsst_data['filter']['email'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => jssupportticket::JSST_getVarValue($jsst_search_field_array['email']))), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php 
                            }
                            if(!empty($jsst_search_field_array['priority'])) { ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::select('jsst-priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), isset(jssupportticket::$jsst_data['filter']['priorityid']) ? jssupportticket::$jsst_data['filter']['priorityid'] : '', esc_html(__('Select', 'js-support-ticket')).' '.esc_attr($jsst_search_field_array['priority'])), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php 
                            } ?>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::text('jsst-datestart', isset(jssupportticket::$jsst_data['filter']['datestart']) ? jssupportticket::$jsst_data['filter']['datestart'] : '', array('class' => 'custom_date js-ticket-input-field', 'placeholder' => esc_html(__('Start Date', 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::text('jsst-dateend', isset(jssupportticket::$jsst_data['filter']['dateend']) ? jssupportticket::$jsst_data['filter']['dateend'] : '', array('class' => 'custom_date js-ticket-input-field', 'placeholder' => esc_html(__('End Date', 'js-support-ticket')))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <?php if(class_exists('WooCommerce') && in_array('woocommerce', jssupportticket::$_active_addons) && !empty($jsst_search_field_array['wcorderid'])){  ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::text('jsst-orderid', isset(jssupportticket::$jsst_data['filter']['orderid']) ? jssupportticket::$jsst_data['filter']['orderid'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => jssupportticket::JSST_getVarValue($jsst_search_field_array['wcorderid']))), JSST_ALLOWED_TAGS); ?>
                                </div>

                            <?php
                            }
                            if(!empty($jsst_field_array['eddorderid']) && in_array('easydigitaldownloads', jssupportticket::$_active_addons) && class_exists('Easy_Digital_Downloads') && !empty($jsst_search_field_array['eddorderid'])){  ?>
                                <div class="js-col-md-3 js-filter-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::text('jsst-eddorderid', isset(jssupportticket::$jsst_data['filter']['eddorderid']) ? jssupportticket::$jsst_data['filter']['eddorderid'] : '', array('class' => 'js-ticket-input-field', 'placeholder' => jssupportticket::JSST_getVarValue($jsst_search_field_array['eddorderid']))), JSST_ALLOWED_TAGS); ?>
                                </div>

                            <?php
                            } ?>
                            <div class="js-col-md-3 js-filter-field-wrp">
                                <?php echo wp_kses(JSSTformfield::select('jsst-status', JSSTincluder::getJSModel('status')->getStatusForFilter(), isset(jssupportticket::$jsst_data['filter']['status']) ? jssupportticket::$jsst_data['filter']['status'] : '', esc_html(__('Select Status', 'js-support-ticket'))), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <?php
                             $jsst_customfields = JSSTincluder::getObjectClass('customfields')->userFieldsForSearch(1);
                                foreach ($jsst_customfields as $jsst_field) {
                                    JSSTincluder::getObjectClass('customfields')->formCustomFieldsForSearch($jsst_field, $jsst_k);
                                }  ?>
                        </div>
                        <div class="js-filter-button-wrp">
                            <a href="#" class="js-search-filter-btn" id="js-search-filter-toggle-btn">
                                <?php echo esc_html(__('Show All','js-support-ticket')); ?>
                            </a>
                            <?php echo wp_kses(JSSTformfield::submitbutton('jsst-go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'js-ticket-filter-button js-ticket-search-btn')), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::submitbutton('jsst-reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'js-ticket-filter-button js-ticket-reset-btn', 'onclick' => 'return resetForm();')), JSST_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('sortby', isset(jssupportticket::$jsst_data['filter']['sortby']) ? jssupportticket::$jsst_data['filter']['sortby'] :'' ), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('list', $jsst_list), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('jshdlay', 'myticket'), JSST_ALLOWED_TAGS); ?>
                </form>
            </div>
        </div>
        <!-- Sorting Wrapper -->
        <?php
        $jsst_link = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket','list'=> jssupportticket::$jsst_data['list']));
        if (jssupportticket::$_sortorder == 'ASC')
            $jsst_img = "sorting-1.png";
        else
            $jsst_img = "sorting-2.png";
        ?>
        <div class="js-ticket-sorting js-col-md-12">
            <?php /*
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_url(jssupportticket::$_sortlinks['subject']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'subject') echo 'selected' ?>"><?php echo esc_html($jsst_field_array['subject']); ?><?php if (jssupportticket::$_sorton == 'subject') { ?> <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($jsst_img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_url(jssupportticket::$_sortlinks['priority']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'priority') echo 'selected' ?>"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?><?php if (jssupportticket::$_sorton == 'priority') { ?> <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($jsst_img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_url(jssupportticket::$_sortlinks['ticketid']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'ticketid') echo 'selected' ?>"><?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?><?php if (jssupportticket::$_sorton == 'ticketid') { ?> <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($jsst_img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_url(jssupportticket::$_sortlinks['isanswered']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'isanswered') echo 'selected' ?>"><?php echo esc_html(__('Answered', 'js-support-ticket')); ?><?php if (jssupportticket::$_sorton == 'isanswered') { ?> <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($jsst_img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_url(jssupportticket::$_sortlinks['status']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'status') echo 'selected' ?>"><?php echo esc_html(__('Status', 'js-support-ticket')); ?><?php if (jssupportticket::$_sorton == 'status') { ?> <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($jsst_img) ?>"> <?php } ?></a></span>
            <span class="js-col-md-2 js-ticket-sorting-link"><a href="<?php echo esc_url(jssupportticket::$_sortlinks['created']); ?>" class="jssortlink <?php if (jssupportticket::$_sorton == 'created') echo 'selected' ?>"><?php echo esc_html(__('Created', 'js-support-ticket')); ?><?php if (jssupportticket::$_sorton == 'created') { ?> <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticketdetailicon/' . esc_attr($jsst_img) ?>"> <?php } ?></a></span>
            */ ?>
            <div class="js-ticket-sorting-left">
                <div class="js-ticket-sorting-heading">
                    <?php echo esc_html(__('All Tickets','js-support-ticket')); ?>
                </div>
            </div>
            <div class="js-ticket-sorting-right">
                <div class="js-ticket-sort">
                    <select class="js-ticket-sorting-select">
                        <?php echo esc_html($jsst_field_array['subject']); ?>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['subject']); ?>" <?php if (jssupportticket::$_sorton == 'subject') echo 'selected' ?>><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['subject'])); ?></option>
                        <?php 
                        if (!empty($jsst_field_array['priority'])) { ?>
                            <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['priority']); ?>"  <?php if (jssupportticket::$_sorton == 'priority') echo 'selected' ?>><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?></option>
                            <?php
                        } ?>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['ticketid']); ?>"  <?php if (jssupportticket::$_sorton == 'ticketid') echo 'selected' ?>><?php echo esc_html(__("Ticket ID",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['isanswered']); ?>"  <?php if (jssupportticket::$_sorton == 'isanswered') echo 'selected' ?>><?php echo esc_html(__("Answered",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['status']); ?>"  <?php if (jssupportticket::$_sorton == 'status') echo 'selected' ?>><?php echo esc_html(__("Status",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['created']); ?>"  <?php if (jssupportticket::$_sorton == 'created') echo 'selected' ?>><?php echo esc_html(__("Created",'js-support-ticket')); ?></option>
                    </select>
                    <a href="#" class="js-admin-sort-btn" title="<?php echo esc_attr(__('sort','js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('sort','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/' . esc_attr($jsst_img) ?>">
                    </a>
                </div>
            </div>
        </div>

        <?php
        if (!empty(jssupportticket::$jsst_data[0])) {
            $jsst_fields_array = array(); // Array for form fields
            $jsst_show_on_listing_arrays = array(); // Array for visible form fields
            foreach (jssupportticket::$jsst_data[0] AS $jsst_ticket) {
                // Check if the form fields are already array
                if (!isset($jsst_fields_array[$jsst_ticket->multiformid])) {
                    $jsst_fields_array[$jsst_ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, $jsst_ticket->multiformid);
                }
                if (!isset($jsst_show_on_listing_arrays[$jsst_ticket->multiformid])) {
                    $jsst_show_on_listing_arrays[$jsst_ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldsForListing(1, $jsst_ticket->multiformid);
                }
                // Now use the cached field array
                $jsst_field_array = $jsst_fields_array[$jsst_ticket->multiformid];
                $jsst_show_on_listing_array = $jsst_show_on_listing_arrays[$jsst_ticket->multiformid];
                $jsst_ticketviamail = '';
                if ($jsst_ticket->ticketviaemail == 1)
                    $jsst_ticketviamail = esc_html(__('Created via Email', 'js-support-ticket'));
                ?>
                <div class="js-col-xs-12 js-col-md-12 js-ticket-wrapper">
                    <div class="js-col-xs-2 js-col-md-2 js-ticket-pic">
                        <?php /* if (in_array('agent',jssupportticket::$_active_addons) && $jsst_ticket->staffphoto) { ?>
                            <img class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $jsst_ticket->staffid ,'jsstpageid'=>get_the_ID())));?> ">
                        <?php } else { */
                            echo wp_kses(jsst_get_avatar($jsst_ticket->uid), JSST_ALLOWED_TAGS);
                        // } ?>
                    </div>
                    <div class="js-ticket-toparea">
                        <div class="js-col-xs-10 js-col-md-6 js-col-xs-10 js-ticket-data js-nullpadding">
                            <?php 
                            if (!empty($jsst_show_on_listing_array['fullname'])) {?>
                                <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses name">
                                    <span class="js-ticket-value"><?php echo esc_html($jsst_ticket->name); ?></span>
                                    <?php if ($jsst_ticket->status == 5 && jssupportticket::$_config['show_closedby_on_user_tickets'] == 1) { ?>
                                        <span class="js-ticket-closedby-wrp">
                                            <span class="js-ticket-closedby">
                                                <?php echo esc_html(JSSTincluder::getJSModel('ticket')->getClosedBy($jsst_ticket->closedby)); ?>
                                            </span>
                                            <?php 
                                            if ($jsst_ticket->closed != '0000-00-00 00:00:00') {?>
                                                <span class="js-ticket-closed-date">
                                                    <?php echo esc_html("Closed on"). " " . esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticket->closed))); ?>
                                                </span>
                                                <?php 
                                            } ?>
                                        </span>
                                    <?php } ?>
                                </div>
                                <?php
                            } ?>
                            <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                <a class="js-ticket-title-anchor" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail','jssupportticketid'=> $jsst_ticket->id))); ?>"><?php echo esc_html($jsst_ticket->subject); ?></a>
                            </div>
                            <?php 
                            foreach ($jsst_show_on_listing_array AS $jsst_field_field => $jsst_field_title) {
                                switch ($jsst_field_field) {
                                    case 'department': ?>
                                        <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                            <span class="js-ticket-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])); ?>:&nbsp;</span>
                                            <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->departmentname)); ?></span>
                                        </div>
                                        <?php
                                        break;
                                    case 'email': ?>
                                        <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                            <span class="js-ticket-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['email'])); ?>:&nbsp;</span>
                                            <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->email)); ?></span>
                                        </div>
                                        <?php
                                        break;
                                    case 'phone':?>
                                        <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                            <span class="js-ticket-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['phone'])); ?>:&nbsp;</span>
                                            <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->phone)); ?></span>
                                        </div>
                                        <?php
                                        break;
                                    case 'product':  ?>
                                        <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                            <span class="js-ticket-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['product'])); ?>:&nbsp;</span>
                                            <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->producttitle)); ?></span>
                                        </div>
                                        <?php
                                        break;
                                    case 'helptopic': 
                                        if (in_array('helptopic', jssupportticket::$_active_addons)) { ?>
                                            <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                                <span class="js-ticket-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['helptopic'])); ?>:&nbsp;</span>
                                                <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->topic)); ?></span>
                                            </div>
                                        <?php
                                        }
                                        break;
                                    case 'eddorderid': ?>
                                        <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                            <span class="js-ticket-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['eddorderid'])); ?>:&nbsp;</span>
                                            <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->eddorderid)); ?></span>
                                        </div>
                                        <?php
                                        break;
                                    case 'eddproductid':
                                        if(!in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                                            break;
                                        }
                                        if(!class_exists('Easy_Digital_Downloads')){
                                            break;
                                        } ?>
                                        <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                            <span class="js-ticket-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['eddproductid'])); ?>:&nbsp;</span>
                                            <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->eddproductid)); ?></span>
                                        </div>
                                        <?php
                                        break;
                                    default:
                                        break;
                                }
                            }
                            jssupportticket::$jsst_data['custom']['ticketid'] = $jsst_ticket->id;
                            $jsst_customfields = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, 1);
                            foreach ($jsst_customfields as $jsst_field) {
                                $jsst_ret = JSSTincluder::getObjectClass('customfields')->showCustomFields($jsst_field,1, $jsst_ticket->params);
                                ?>
                                <div class="js-col-xs-12 js-col-md-12 js-ticket-padding-xs js-ticket-body-data-elipses">
                                    <span class="js-ticket-field-title"><?php echo esc_html($jsst_ret['title']); ?>:&nbsp;</span>
                                    <span class="js-ticket-value"><?php echo wp_kses($jsst_ret['value'], JSST_ALLOWED_TAGS); ?></span>
                                </div>
                                <?php
                            }
                            if ($jsst_ticket->ticketviaemail == 1){  ?>
                                <span class="js-ticket-value js-ticket-creade-via-email-spn"><?php echo esc_html($jsst_ticketviamail); ?></span>
                            <?php }
                            if (!empty($jsst_show_on_listing_array['priority'])) { ?>
                                <span class="js-ticket-wrapper-textcolor" style="background:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>;">
                                    <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?>
                                </span>
                                <?php
                            } ?>

                                <?php
                                $jsst_counter = 'one';
                                if ($jsst_ticket->lock == 1) {
                                    ?>
                                    <img class="ticketstatusimage <?php echo esc_attr($jsst_counter);
                                    $jsst_counter = 'two'; ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/lock.png"; ?>" title="<?php echo esc_attr(__('The ticket is locked', 'js-support-ticket')); ?>" />
                                <?php } ?>
                                <?php if ($jsst_ticket->isoverdue == 1) { ?>
                                        <img class="ticketstatusimage <?php echo esc_attr($jsst_counter); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/over-due.png"; ?>" title="<?php echo esc_attr(__('This ticket is marked as overdue', 'js-support-ticket')); ?>" />
                                <?php } ?>
                            <span class="js-ticket-status" style="background-color: <?php echo esc_attr($jsst_ticket->statusbgcolour); ?>;color:<?php echo esc_attr($jsst_ticket->statuscolour); ?>;">
                                <?php echo esc_html($jsst_ticket->statustitle); ?>
                            </span>
                        </div>
                        <div class="js-col-xs-12 js-col-md-4 js-ticket-data1 js-ticket-padding-left-xs">
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-data-tit"><?php echo esc_html(__('Ticket ID', 'js-support-ticket')). ': '; ?></div>
                                <div class="js-ticket-data-val"><?php echo esc_html($jsst_ticket->ticketid); ?></div>
                            </div>
                            <?php if (empty($jsst_ticket->lastreply) || $jsst_ticket->lastreply == '0000-00-00 00:00:00') { ?>
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-data-tit"><?php echo esc_html(__('Created','js-support-ticket')). ': '; ?></div>
                                <div class="js-ticket-data-val"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticket->created))); ?></div>
                            </div>
                            <?php } else { ?>
                            <div class="js-ticket-data-row">
                                <div class="js-ticket-data-tit"><?php echo esc_html(__('Last Reply', 'js-support-ticket')). ': '; ?></div>
                                <div class="js-ticket-data-val"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticket->lastreply))); ?></div>
                            </div>
                            <?php } ?>
                            <?php
                            if (in_array('agent',jssupportticket::$_active_addons) &&jssupportticket::$_config['show_assignto_on_user_tickets'] == 1) { ?>
                                <div class="js-ticket-data-row">
                                    <div class="js-ticket-data-tit">
                                        <?php echo esc_html(__('Assign To', 'js-support-ticket')). ': '; ?>
                                    </div>
                                    <div class="js-ticket-data-val"><?php echo esc_html($jsst_ticket->staffname); ?></div>
                                </div>
                                <?php
                            } ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            if (jssupportticket::$jsst_data[1]) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
            }
        } else { // Record Not FOund
            JSSTlayout::getNoRecordFound();
        }
    } else {// User is guest
        $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket'));
        $jsst_redirect_url = jssupportticketphplib::JSST_safe_encoding($jsst_redirect_url);
        JSSTlayout::getUserGuest($jsst_redirect_url);
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
