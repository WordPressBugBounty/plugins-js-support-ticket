<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="jsst-main-up-wrapper">
    <?php
    if (jssupportticket::$_config['offline'] == 2) {
        if (jssupportticket::$jsst_data['permission_granted'] == 1) {
            if (JSSTincluder::getObjectClass('user')->uid() != 0) {
                if ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                    if (jssupportticket::$jsst_data['staff_enabled']) {
                        wp_enqueue_script('jquery-ui-datepicker');
                        wp_enqueue_script('file_validate.js', JSST_PLUGIN_URL . 'includes/js/file_validate.js');
                        $jsst_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                        wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
                        $jsst_status_combo = array(
                            (object) array('id' => '1', 'text' => __('New', 'js-support-ticket')),
                            (object) array('id' => '2', 'text' => __('Pending', 'js-support-ticket')),
                            (object) array('id' => '3', 'text' => __('In Progress', 'js-support-ticket')),
                            (object) array('id' => '4', 'text' => __('Answered', 'js-support-ticket')),
                            (object) array('id' => '5', 'text' => __('Closed', 'js-support-ticket'))
                        );
                        $jsst_yesno = array(
                            (object) array('id' => '1', 'text' => __('Yes', 'js-support-ticket')),
                            (object) array('id' => '2', 'text' => __('No', 'js-support-ticket'))
                        );?>
                        <script type="text/javascript">
                            ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
                        	function updateuserlist(pagenum){
                                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'getuserlistajax',userlimit:pagenum, '_wpnonce':'<?php echo esc_attr(wp_create_nonce("get-user-list-ajax")); ?>'}, function (data) {
                                    if(data){
                                        jQuery("div#userpopup-records").html("");
                                        jQuery("div#userpopup-records").html(data);
                                        setUserLink();
                                    }
                                });
                            }
                            function setUserLink() {
                                jQuery("a.js-userpopup-link").each(function () {
                                    var anchor = jQuery(this);
                                    jQuery(anchor).click(function (e) {
                                        var id = jQuery(this).attr('data-id');
                                        var name = jQuery(this).attr("data-username");
                                        jQuery("input#username-text").val(name);
                                        jQuery("input#uid").val(id);
                                        jQuery("div#userpopup").slideUp('slow', function () {
                                            jQuery("div#userpopupblack").hide();
                                        });
                                    });
                                });
                            }
                            setUserLink();
                            jQuery(document).ready(function ($) {
                                jQuery('.custom_date').datepicker({
                                    dateFormat: 'yy-mm-dd'
                                });
                                jQuery("a#userpopup").click(function (e) {
                                    e.preventDefault();
                                    jQuery("div#userpopupblack").show();
                                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'getuserlistajax', '_wpnonce':'<?php echo esc_attr(wp_create_nonce("get-user-list-ajax")); ?>'}, function (data) {
                                        if(data){
                                            jQuery("div#userpopup-records").html("");
                                            jQuery("div#userpopup-records").html(data);
                                            setUserLink();
                                        }
                                    });
                                    jQuery("div#userpopup").slideDown('slow');
                                });
                                jQuery("form#userpopupsearch").submit(function (e) {
                                    e.preventDefault();
                                    var username = jQuery("input#username").val();
                                    var name = jQuery("input#name").val();
                                    var emailaddress = jQuery("input#emailaddress").val();
                                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', name: name, username: username, emailaddress: emailaddress, jstmod: 'jssupportticket', task: 'getusersearchajax', '_wpnonce':'<?php echo esc_attr(wp_create_nonce("get-usersearch-ajax")); ?>'}, function (data) {
                                        if (data) {
                                            jQuery("div#userpopup-records").html(data);
                                            setUserLink();
                                        }
                                    });//jquery closed
                                });
                                jQuery(".userpopup-close, div#userpopupblack").click(function (e) {
                                    jQuery("div#userpopup").slideUp('slow', function () {
                                        jQuery("div#userpopupblack").hide();
                                    });

                                });
                        	});
                        </script>
                        <div id="userpopupblack" style="display:none;"></div>
                        <div id="userpopup" style="display:none;">
                            <div class="userpopup-top">
                                <div class="userpopup-heading">
                                    <?php echo esc_html(__('Select User','js-support-ticket')); ?>
                                </div>
                                <span class="userpopup-close"></span>
                            </div>
                            <div class="userpopup-search">
                                <form id="userpopupsearch">
                                    <div class="userpopup-fields-wrp">
                                        <div class="userpopup-fields">
                                            <input type="text" name="username" id="username" placeholder="<?php echo esc_attr(__('Username','js-support-ticket')); ?>" />
                                        </div>
                                        <div class="userpopup-fields">
                                            <input type="text" name="name" id="name" placeholder="<?php echo esc_attr(__('Name','js-support-ticket')); ?>" />
                                        </div>
                                        <div class="userpopup-fields">
                                            <input type="text" name="emailaddress" id="emailaddress" placeholder="<?php echo esc_attr(__('Email Address','js-support-ticket')); ?>"/>
                                        </div>
                                        <div class="userpopup-btn-wrp">
                                            <input class="userpopup-search-btn" type="submit" value="<?php echo esc_attr(__('Search','js-support-ticket')); ?>" />
                                            <input class="userpopup-reset-btn" type="submit" onclick="document.getElementById('name').value = '';document.getElementById('username').value = ''; document.getElementById('emailaddress').value = '';" value="<?php echo esc_attr(__('Reset','js-support-ticket')); ?>" />
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div id="userpopup-records-wrp">
                                <div id="userpopup-records">
                                    <div class="userpopup-records-desc">
                                        <?php echo esc_html(__('Use search feature to select the user','js-support-ticket')); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
                        <div class="js-ticket-add-form-wrapper">
                            <div class="js-export-wrapper" >
                                <form class="js-ticket-form" autocomplete="off" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'export', 'action'=>'jstask', 'task'=>'getticketsexport', 'jsstpageid'=>get_the_ID())),"get-tickets-export")); ?>">
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Start Date', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field">
                                        	<?php echo wp_kses(JSSTformfield::text('startdate', '', array('class' => 'custom_date js-ticket-form-field-input')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('End Date', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field">
                                        	<?php echo wp_kses(JSSTformfield::text('enddate', '', array('class' => 'custom_date js-ticket-form-field-input')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Department', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select">
                                        	<?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), '', __('Select Department', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-select')), JSST_ALLOWED_TAGS);  ?>
                                        </div>
                                    </div>
                                    <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                                        <div class="js-ticket-from-field-wrp">
                                            <div class="js-ticket-from-field-title">
                                                <?php echo esc_html(__('Agent', 'js-support-ticket')); ?>:
                                            </div>
                                            <div class="js-ticket-from-field js-ticket-form-field-select">
                                                <?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), '', __('Select Agent', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-select')), JSST_ALLOWED_TAGS);  ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('User', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field">
                                            <div id="username-div" class="js-ticket-select-user-field"></div><input class="js-ticket-form-field-input" type="text" value="" id="username-text" readonly="readonly" data-validation="required"/><div class="js-ticket-select-user-btn"><a href="#" id="userpopup" title="<?php echo esc_attr(__('Select User','js-support-ticket')); ?>"><?php echo esc_html(__('Select User', 'js-support-ticket')); ?></a></div>
                                        </div>
                                    </div>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select">
                                            <?php echo wp_kses(JSSTformfield::select('priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), '', __('Select Priority', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-select')), JSST_ALLOWED_TAGS);  ?>
                                        </div>
                                    </div>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Ticket Status','js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select">
                                            <?php echo wp_kses(JSSTformfield::select('ticketstatus', JSSTincluder::getJSModel('status')->getStatusForCombobox(), '', __('Select Ticket Status', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-select')), JSST_ALLOWED_TAGS);  ?>
                                        </div>
                                    </div>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Ticket Overdue', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select">
                                            <?php echo wp_kses(JSSTformfield::select('isoverdue', $jsst_yesno, '', __('Select Ticket Overdue Status', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-select')), JSST_ALLOWED_TAGS);  ?>
                                        </div>
                                    </div>
                                    <?php
                                    // "Single / Multiple Header" chose between one header row and a
                                    // header repeated per ticket, which existed because the old file
                                    // widened every row to the widest ticket in the export. The CSV
                                    // pipeline writes one header and one row per ticket, so the
                                    // choice no longer means anything and the control is gone - it
                                    // was already ignored here, which left the agent screen offering
                                    // a setting the admin screen had dropped. (Roadmap 4.0-CORE-10)
                                    ?>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Format', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field">
                                            <?php echo esc_html(__('CSV, one row per ticket. Opens directly in Excel, LibreOffice Calc, Numbers and Google Sheets.', 'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-ticket-form-btn-wrp">
                                        <?php echo wp_kses(JSSTformfield::submitbutton('save', __('Export', 'js-support-ticket'), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                    <?php echo wp_kses(JSSTformfield::hidden('uid',''), JSST_ALLOWED_TAGS);  ?>
                                </form>
                            </div>
                        </div>
                        <?php
                    } else {
                        JSSTlayout::getStaffMemberDisable();
                    }
                } else { // user not Staff
                    JSSTlayout::getNotStaffMember();
                }
            } else {
                $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'department', 'jstlay'=>'adddepartment'));
                $jsst_redirect_url = jssupportticketphplib::JSST_safe_encoding($jsst_redirect_url);
                JSSTlayout::getUserGuest($jsst_redirect_url);
            }
        } else { // User permission not granted
            JSSTlayout::getPermissionNotGranted();
        }
    } else {
        JSSTlayout::getSystemOffline();
    } ?>
</div>
