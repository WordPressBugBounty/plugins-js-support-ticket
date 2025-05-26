<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css');
    wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css');
$jssupportticket_js ="
    function resetFrom() {
        var form = jQuery('form#jssupportticketform');
        form.find('input[type=text], input[type=email], input[type=password], textarea').val('');
        form.find('input:checkbox').removeAttr('checked');
        form.find('select').prop('selectedIndex', 0);
        form.find('input[type=\'radio\']').prop('checked', false);
        document.getElementById('jssupportticketform').submit();
    }
    jQuery(document).ready(function(){
        jQuery('.date,.custom_date').datepicker({dateFormat: 'yy-mm-dd'});
        jQuery('select.js-admin-sort-select').on('change',function(e){
            e.preventDefault();
            var sortby = jQuery('.js-admin-sort-select option:selected').val();
            //alert(sortby);
            jQuery('input#sortby').val(sortby);
            jQuery('form#jssupportticketform').submit();
        });
        jQuery('a.js-admin-sort-btn').on('click',function(e){
            e.preventDefault();
            var sortby = jQuery('.js-admin-sort-select option:selected').val();
            //alert(sortby);
            jQuery('input#sortby').val(sortby);
            jQuery('form#jssupportticketform').submit();
        });
        jQuery('a.js-ticket-link').click(function(e){
            e.preventDefault();
            var list = jQuery(this).attr('data-tab-number');
            jQuery('input#list').val(list);
            jQuery('form#jssupportticketform').submit();
        });
        jQuery('span.js-ticket-closedby-wrp').hover(
            function(e){
                jQuery(this).find('span.js-ticket-closed-date').css('display','inline-block');
            },
            function(e){
                jQuery(this).find('span.js-ticket-closed-date').css('display','none');
            }
        );
    });

    function setDepartmentFilter( depid ){
        jQuery('#departmentid').val( depid );
        jQuery('form#jssupportticketform').submit();
    }

    function setFromNameFilter( email ){
        jQuery('#email').val( email );
        jQuery('form#jssupportticketform').submit();
    }
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php
        if(current_user_can('jsst_support_ticket')){
            JSSTincluder::getClassesInclude('jsstadminsidemenu');
        }
        ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Tickets','js-support-ticket')); ?></li>
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
            <?php 
                $id='';
                if(in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1){
                    $id="id=multiformpopup";
                }
            ?>
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Tickets','js-support-ticket')); ?></h1>
            <a <?php echo esc_attr($id); ?> title="<?php echo esc_html(__('Add', 'js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=ticket&jstlay=addticket&formid=<?php echo esc_html(JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId()) ?>"><img alt="<?php echo esc_html(__('Add', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Create Ticket','js-support-ticket')); ?></a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <?php
            $list = JSSTrequest::getVar('list', null, null);
            if($list == null){
                $list = jssupportticket::$_search['ticket']['list'];
            }
            $open = ($list == 1) ? 'active' : '';
            $answered = ($list == 2) ? 'active' : '';
            $overdue = ($list == 3) ? 'active' : '';
            $closed = ($list == 4) ? 'active' : '';
            $alltickets = ($list == 5) ? 'active' : '';
            $field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1);
            $search_field_array = JSSTincluder::getJSModel('fieldordering')->getAdminSystemFieldsForSearch();
            ?>
            <?php
            $open_percentage = 0;
            $close_percentage = 0;
            $overdue_percentage = 0;
            $answered_percentage = 0;
            $allticket_percentage = 0;
            if(isset(jssupportticket::$_data['count']) && isset(jssupportticket::$_data['count']['allticket']) && jssupportticket::$_data['count']['allticket'] != 0){
                $open_percentage = round((jssupportticket::$_data['count']['openticket'] / jssupportticket::$_data['count']['allticket']) * 100);
                $close_percentage = round((jssupportticket::$_data['count']['closedticket'] / jssupportticket::$_data['count']['allticket']) * 100);
                $overdue_percentage = round((jssupportticket::$_data['count']['overdueticket'] / jssupportticket::$_data['count']['allticket']) * 100);
                $answered_percentage = round((jssupportticket::$_data['count']['answeredticket'] / jssupportticket::$_data['count']['allticket']) * 100);
            }
            if(isset(jssupportticket::$_data['count']) && isset(jssupportticket::$_data['count']['allticket']) && jssupportticket::$_data['count']['allticket'] != 0){
                $allticket_percentage = 100;
            }
            ?>
            <div class="js-ticket-count">
                <div class="js-ticket-link">
                    <a class="js-ticket-link <?php echo esc_attr($open); ?> js-ticket-green" href="#" data-tab-number="1" title="<?php echo esc_html(__('Open Ticket','js-support-ticket')); ?>">
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
                        <div class="js-ticket-link-text js-ticket-green">
                            <?php
                                echo esc_html(__('Open', 'js-support-ticket'));
                                if(jssupportticket::$_config['count_on_myticket'] == 1)
                                    echo ' ( '.esc_html(jssupportticket::$_data['count']['openticket']).' )';
                            ?>
                        </div>
                    </a>
                </div>
                <div class="js-ticket-link">
                    <a class="js-ticket-link <?php echo esc_attr($answered); ?> js-ticket-brown" href="#" data-tab-number="2" title="<?php echo esc_html(__('answered ticket','js-support-ticket')); ?>">
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
                                if(jssupportticket::$_config['count_on_myticket'] == 1)
                                    echo ' ( '.esc_html(jssupportticket::$_data['count']['answeredticket']).' )';
                            ?>
                        </div>
                    </a>
                </div>
                <?php if(in_array('overdue', jssupportticket::$_active_addons)){ ?>
                    <div class="js-ticket-link">
                        <a class="js-ticket-link <?php echo esc_attr($overdue); ?> js-ticket-orange" href="#" data-tab-number="3" title="<?php echo esc_html(__('overdue ticket','js-support-ticket')); ?>">
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
                                    if(jssupportticket::$_config['count_on_myticket'] == 1)
                                        echo ' ( '.esc_html(jssupportticket::$_data['count']['overdueticket']).' )';
                                ?>
                            </div>
                        </a>
                    </div>
                <?php } ?>
                <div class="js-ticket-link">
                    <a class="js-ticket-link <?php echo esc_attr($closed); ?> js-ticket-red" href="#" data-tab-number="4" title="<?php echo esc_html(__('closed ticket','js-support-ticket')); ?>">
                        <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($close_percentage); ?>" >
                            <div class="js-mr-rp" data-progress="<?php echo esc_html($close_percentage); ?>">
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
                                if(jssupportticket::$_config['count_on_myticket'] == 1)
                                    echo ' ( '.esc_html(jssupportticket::$_data['count']['closedticket']).' )';
                            ?>
                        </div>
                    </a>
                </div>
                <div class="js-ticket-link">
                    <a class="js-ticket-link <?php echo esc_attr($alltickets); ?> js-ticket-blue" href="#" data-tab-number="5" title="<?php echo esc_html(__('All Tickets','js-support-ticket')); ?>">
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
                        <div class="js-ticket-link-text js-ticket-blue">
                            <?php
                                echo esc_html(__('All Tickets', 'js-support-ticket'));
                                if(jssupportticket::$_config['count_on_myticket'] == 1)
                                    echo ' ( '.esc_html(jssupportticket::$_data['count']['allticket']).' )';
                            ?>
                        </div>
                    </a>
                </div>
            </div>
            <?php
            $uid = JSSTrequest::getVar('uid',null,0);
            if(is_numeric($uid) && $uid){
                $formaction = wp_nonce_url(admin_url("admin.php?page=ticket&jstlay=tickets&uid=".esc_attr($uid)),"my-ticket");
            }else{
                $formaction = wp_nonce_url(admin_url("admin.php?page=ticket&jstlay=tickets"),"my-ticket");
            }
            ?>
            <form class="js-filter-form mt0" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url($formaction); ?>">
                <?php
                if (!empty($search_field_array['subject'])) {
                    echo wp_kses(JSSTformfield::text('subject', jssupportticket::$_data['filter']['subject'], array('placeholder' => jssupportticket::JSST_getVarValue($search_field_array['subject']),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS);
                }
                if (!empty($search_field_array['fullname'])) {
                    echo wp_kses(JSSTformfield::text('name', jssupportticket::$_data['filter']['name'], array('placeholder' => esc_html(__('Ticket Creator', 'js-support-ticket')).' '.$search_field_array['fullname'],'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS);
                }
                if (!empty($search_field_array['phone'])) {
                    echo wp_kses(JSSTformfield::text('phone', jssupportticket::$_data['filter']['phone'], array('placeholder' => esc_html(jssupportticket::JSST_getVarValue($search_field_array['phone'])),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS);
                }
                if(!empty($search_field_array['email'])) {
                    echo wp_kses(JSSTformfield::text('email', jssupportticket::$_data['filter']['email'], array('placeholder' => jssupportticket::JSST_getVarValue($search_field_array['email']),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS);
                } ?>
                <?php if ( in_array('agent',jssupportticket::$_active_addons)) { ?>
                    <?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), jssupportticket::$_data['filter']['staffid'], esc_html(__('Select Agent','js-support-ticket')), array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                <?php } ?>
                <?php
                if(!empty($search_field_array['product'])) { 
                    echo wp_kses(JSSTformfield::select('productid', JSSTincluder::getJSModel('product')->getProductForCombobox(), jssupportticket::$_data['filter']['productid'], esc_html(__('Select','js-support-ticket')).' '.$search_field_array['product'], array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                }
                if(!empty($search_field_array['department'])) { 
                    echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), jssupportticket::$_data['filter']['departmentid'], esc_html(__('Select','js-support-ticket')).' '.$search_field_array['department'], array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                }
                if(!empty($search_field_array['helptopic']) && in_array('helptopic', jssupportticket::$_active_addons)) { 
                    echo wp_kses(JSSTformfield::select('helptopicid', JSSTincluder::getJSModel('helptopic')->getHelpTopicsForCombobox(), jssupportticket::$_data['filter']['helptopicid'], esc_html(__('Select','js-support-ticket')).' '.$search_field_array['helptopic'], array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                }
                if(!empty($search_field_array['priority'])) { 
                    echo wp_kses(JSSTformfield::select('priority', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), jssupportticket::$_data['filter']['priority'], esc_html(__('Select','js-support-ticket')) .' '.$search_field_array['priority'], array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                } ?>
                <?php echo wp_kses(JSSTformfield::text('datestart', jssupportticket::$_data['filter']['datestart'], array('placeholder' => esc_html(__('From Date', 'js-support-ticket')), 'class' => 'date js-form-date-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::text('dateend', jssupportticket::$_data['filter']['dateend'], array('placeholder' => esc_html(__('To Date', 'js-support-ticket')), 'class' => 'date js-form-date-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::text('ticketid', jssupportticket::$_data['filter']['ticketid'], array('placeholder' => esc_html(__('Ticket ID', 'js-support-ticket')),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php if(class_exists('WooCommerce') && in_array('woocommerce', jssupportticket::$_active_addons)){  ?>
                    <?php echo wp_kses(JSSTformfield::text('orderid', jssupportticket::$_data['filter']['orderid'], array('placeholder' => jssupportticket::JSST_getVarValue($field_array['wcorderid']),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php } ?>
                <?php echo wp_kses(JSSTformfield::select('status', JSSTincluder::getJSModel('status')->getStatusForFilter(), jssupportticket::$_data['filter']['status'], esc_html(__('Select Status','js-support-ticket')), array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('sortby', jssupportticket::$_data['filter']['sortby']), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('list', $list), JSST_ALLOWED_TAGS); ?>

                <?php
                    $customfields = JSSTincluder::getObjectClass('customfields')->adminFieldsForSearch(1);
                    foreach ($customfields as $field) {
                        JSSTincluder::getObjectClass('customfields')->formCustomFieldsForSearch($field, $k, 1);
                    }
                ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button(esc_html(__('Reset', 'js-support-ticket')), esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php
            $link = '?page=ticket';
            if (jssupportticket::$_sortorder == 'ASC')
                $img = "sorting-white-1.png";
            else
                $img = "sorting-white-2.png";
            ?>
            <div class="js-admin-heading">
                <div class="js-admin-head-txt"><?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?></div>
                <div class="js-admin-sorting">
                    <select class="js-admin-sort-select">
                        <?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['subject'])); ?>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['subject']); ?>" <?php if (jssupportticket::$_sorton == 'subject') echo 'selected' ?>><?php echo esc_html(__("Subject",'js-support-ticket')); ?></option>
                        <?php 
                        if (!empty($field_array['priority'])) { ?>
                            <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['priority']); ?>"  <?php if (jssupportticket::$_sorton == 'priority') echo 'selected' ?>><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['priority'])); ?></option>
                            <?php
                        } ?>
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
            <?php
            if (!empty(jssupportticket::$_data[0])) {
                ?>
                <!-- Tabs Area -->
                <?php
                $fields_array = array(); // Array for form fields
                $show_on_listing_arrays = array(); // Array for visible form fields
                foreach (jssupportticket::$_data[0] AS $ticket) {
                    // Check if the form fields are already array
                    if (!isset($fields_array[$ticket->multiformid])) {
                        $fields_array[$ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, $ticket->multiformid);
                    }
                    if (!isset($show_on_listing_arrays[$ticket->multiformid])) {
                        $show_on_listing_arrays[$ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldsForListing(1, $ticket->multiformid);
                    }
                    // Now use the cached field array
                    $field_array = $fields_array[$ticket->multiformid];
                    $show_on_listing_array = $show_on_listing_arrays[$ticket->multiformid];

                    $ticketviamail = '';
                    if ($ticket->ticketviaemail == 1)
                        $ticketviamail = esc_html(__('Created via Email', 'js-support-ticket'));
                    ?>
                    <div class="js-ticket-wrapper">
                        <div class="js-ticket-toparea">
                            <div class="js-ticket-pic">
                                <?php echo wp_kses(jsst_get_avatar($ticket->uid), JSST_ALLOWED_TAGS); ?>
                                <?php /*if (in_array('agent',jssupportticket::$_active_addons) && $ticket->staffphoto) { ?>
                                    <img alt="<?php echo esc_html(__('Staff','js-support-ticket')); ?>" src="<?php echo esc_url(admin_url('?page=agent&action=jstask&task=getStaffPhoto&jssupportticketid='.esc_attr($ticket->staffid))); ?>">
                                <?php } else {
                                    echo jsst_get_avatar($ticket->uid);
                                }*/ ?>
                            </div>
                            <div class="js-ticket-data">
                                <div class="js-ticket-left">
                                    <?php 
                                    if (!empty($show_on_listing_array['fullname'])) {?>
                                        <div class="js-ticket-data-row">
                                            <span class="js-ticket-user" style="cursor:pointer;" onClick="setFromNameFilter('<?php echo esc_js($ticket->email); ?>');"><?php echo esc_html($ticket->name); ?></span>
                                            <?php if ($ticket->status == 5 && jssupportticket::$_config['show_closedby_on_admin_tickets'] == 1) { ?>
                                                <span class="js-ticket-closedby-wrp">
                                                    <span class="js-ticket-closedby">
                                                        <?php echo esc_html(JSSTincluder::getJSModel('ticket')->getClosedBy($ticket->closedby)); ?>
                                                    </span>
                                                    <?php 
                                                    if ($ticket->closed != '0000-00-00 00:00:00') {?>
                                                        <span class="js-ticket-closed-date">
                                                            <?php echo esc_html("Closed on"). " " . esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->closed))); ?>
                                                        </span>
                                                        <?php 
                                                    } ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                        <?php
                                    } ?>
                                    <div class="js-ticket-data-row">
                                        <a title="<?php echo esc_html(__('Subject','js-support-ticket')); ?>" class="js-ticket-det-link" href="?page=ticket&jstlay=ticketdetail&jssupportticketid=<?php echo esc_attr($ticket->id); ?>"><?php echo esc_html($ticket->subject); ?></a>
                                    </div>
                                    <?php 
                                    foreach ($show_on_listing_array AS $field_field => $field_title) {
                                        switch ($field_field) {
                                            case 'department': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['department'])); ?>&nbsp;:&nbsp;</span>
                                                        <span class="js-ticket-value" style="cursor:pointer;" onClick="setDepartmentFilter('<?php echo esc_js($ticket->departmentid); ?>');"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->departmentname)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'email': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['email'])); ?>&nbsp;:&nbsp;</span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->email)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'phone': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['phone'])); ?>&nbsp;:&nbsp;</span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->phone)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'product': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['product'])); ?>&nbsp;:&nbsp;</span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->producttitle)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'helptopic': 
                                                if (in_array('helptopic', jssupportticket::$_active_addons)) { ?>
                                                    <div class="js-ticket-data-row">
                                                        <div class="js-ticket-data-row-rec">
                                                            <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['helptopic'])); ?>&nbsp;:&nbsp;</span>
                                                            <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->topic)); ?></span>
                                                        </div>
                                                    </div>
                                                <?php
                                                }
                                                break;
                                            case 'eddorderid': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['eddorderid'])); ?>&nbsp;:&nbsp;</span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->eddorderid)); ?></span>
                                                    </div>
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
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['eddproductid'])); ?>&nbsp;:&nbsp;</span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->eddproductid)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            default:
                                                break;
                                        }
                                    }
                                        //jssupportticket::$_data['ticketid'] = $ticket->id;
                                        jssupportticket::$_data['custom']['ticketid'] = $ticket->id;
                                        $customfields = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, 1);
                                        foreach ($customfields as $field) {
                                            $ret = JSSTincluder::getObjectClass('customfields')->showCustomFields($field,1, $ticket->params);
                                            ?>
                                            <div class="js-ticket-data-row js-tkt-custm-flds-wrp">
                                                <div class="js-ticket-data-row-rec">
                                                    <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($ret['title'])); ?>&nbsp;:&nbsp;</span>
                                                    <span class="js-ticket-value" style="cursor:pointer;"><?php echo wp_kses($ret['value'], JSST_ALLOWED_TAGS); ?></span>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    ?>
                                </div>
                                <div class="js-ticket-right">

                                    <span class="js-ticket-value js-ticket-creade-via-email-spn"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticketviamail)); ?></span>
                                    <?php
                                    $counter = 'one';
                                    if ($ticket->lock == 1) { ?>
                                        <img class="ticketstatusimage <?php echo esc_attr($counter); $counter = 'two'; ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/lock.png"; ?>" alt="<?php echo esc_html(__('The ticket is locked', 'js-support-ticket')); ?>" title="<?php echo esc_html(__('The ticket is locked', 'js-support-ticket')); ?>" />
                                    <?php } ?>
                                    <?php if ($ticket->isoverdue == 1) { ?>
                                        <img class="ticketstatusimage <?php echo esc_attr($counter); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/over-due.png"; ?>" alt="<?php echo esc_html(__('This ticket is marked as overdue', 'js-support-ticket')); ?>" title="<?php echo esc_html(__('This ticket is marked as overdue', 'js-support-ticket')); ?>" />
                                    <?php } ?>
                                    <span class="js-ticket-status" style="color:<?php echo esc_attr($ticket->statuscolour); ?>;background:<?php echo esc_attr($ticket->statusbgcolour); ?>">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->statustitle)); ?>
                                    </span>
                                    <?php 
                                    if (!empty($show_on_listing_array['priority'])) { ?>
                                        <span class="js-ticket-priority js-ticket-wrapper-textcolor" style="background:<?php echo esc_attr($ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?></span>
                                        <?php
                                    } ?>
                                    <div class="js-ticket-data1">
                                        <div class="js-ticket-data1-row">
                                            <div class="js-ticket-data1-title"><?php echo esc_html(__('Ticket ID', 'js-support-ticket')).':'; ?></div>
                                            <div class="js-ticket-data1-value"><?php echo esc_html($ticket->ticketid); ?></div>
                                        </div>
                                        <?php if (empty($ticket->lastreply) || $ticket->lastreply == '0000-00-00 00:00:00') { ?>
                                        <div class="js-ticket-data1-row">
                                            <div class="js-ticket-data1-title"><?php echo esc_html(__('Created','js-support-ticket')).':'; ?></div>
                                            <div class="js-ticket-data1-value"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->created))); ?></div>
                                        </div>
                                        <?php } else { ?>
                                        <div class="js-ticket-data1-row">
                                            <div class="js-ticket-data1-title"><?php echo esc_html(__('Last Reply', 'js-support-ticket')).':'; ?></div>
                                            <div class="js-ticket-data1-value"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->lastreply))); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php /*
                                        <div class="js-ticket-data1-row">
                                            <div class="js-ticket-data1-title"><?php echo esc_html($field_array['priority']); ?></div>
                                            <div class="js-ticket-data1-value js-ticket-wrapper-textcolor" style="background:<?php echo esc_attr($ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket->priority)); ?></div>
                                        </div> */ ?>
                                        <?php if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_config['show_assignto_on_admin_tickets'] == 1) { ?>
                                            <div class="js-ticket-data1-row">
                                                <div class="js-ticket-data1-title">
                                                    <?php echo esc_html(__('Assign To', 'js-support-ticket')).':'; ?>
                                                 </div>
                                                <div class="js-ticket-data1-value"><?php echo esc_html($ticket->staffname); ?></div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="js-ticket-bottom-data-part">
                            <?php /*<span class="js-ticket-created"><?php echo esc_html(__('Created', 'js-support-ticket')); ?>&nbsp;:&nbsp;<?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($ticket->created))); ?></span>*/ ?>
                            <div class="js-ticket-datapart-buttons-action">
                                <a class="js-ticket-datapart-action-btn button" title="<?php echo esc_html(__('Edit Ticket', 'js-support-ticket')); ?>" href="?page=ticket&jstlay=addticket&jssupportticketid=<?php echo esc_attr($ticket->id); ?>"><img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit-2.png" /><?php echo esc_html(__('Edit Ticket', 'js-support-ticket')); ?></a>
                                <a class="js-ticket-datapart-action-btn button" title="<?php echo esc_html(__('Delete Ticket', 'js-support-ticket')); ?>"  onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete it?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=ticket&task=deleteticket&action=jstask&ticketid='.esc_attr($ticket->id),'delete-ticket-'.$ticket->id));?>">
                                    <img alt="<?php echo esc_html(__('Delete', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete-2.png" />
                                    <?php echo esc_html(__('Delete Ticket', 'js-support-ticket')); ?></a>
                                <a title="<?php echo esc_html(__('Enforce delete', 'js-support-ticket')); ?>" class="js-ticket-datapart-action-btn button"  onclick="return confirm('<?php echo esc_html(__('Are you sure to enforce delete', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=ticket&task=enforcedeleteticket&action=jstask&ticketid='.esc_attr($ticket->id),'enforce-delete-ticket-'.$ticket->id))?>"><img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/forced-delete.png" alt="<?php echo esc_html(__('Enforce delete', 'js-support-ticket')); ?>" /><?php echo esc_html(__('Enforce delete', 'js-support-ticket')); ?></a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
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
