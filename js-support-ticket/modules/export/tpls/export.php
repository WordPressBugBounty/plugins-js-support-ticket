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
                                    /* "Export Style" - single or multiple header -
                                       stood here until the export learned to write
                                       one header wide enough for every form in the
                                       file. It was already doing nothing: the admin
                                       copy of this screen dropped it when the CSV
                                       pipeline was written and nothing has read the
                                       posted value since, so the radio was a control
                                       that answered to no code. What it was for is
                                       below - the questions each form asks, in
                                       columns a spreadsheet can actually sort.
                                       (Roadmap 4.0-CORE-10, 5.0-FORM-01) */
                                    $jsst_exportmodel = JSSTincluder::getJSModel('export');
                                    $jsst_forms = $jsst_exportmodel->formTitles();
                                    if (count($jsst_forms) > 1) {
                                        $jsst_formcombo = array();
                                        foreach ($jsst_forms AS $jsst_formid => $jsst_formtitle) {
                                            $jsst_formcombo[] = (object) array('id' => $jsst_formid, 'text' => $jsst_formtitle);
                                        } ?>
                                        <div class="js-ticket-from-field-wrp">
                                            <div class="js-ticket-from-field-title"><?php echo esc_html(__('Ticket Form', 'js-support-ticket')); ?>:</div>
                                            <div class="js-ticket-from-field js-ticket-form-field-select">
                                                <?php echo wp_kses(JSSTformfield::select('multiformid', $jsst_formcombo, '', __('All forms', 'js-support-ticket'), array('class' => 'inputbox js-ticket-form-field-select')), JSST_ALLOWED_TAGS); ?>
                                            </div>
                                        </div>
                                    <?php }
                                    /* Custom Fields, Format and Customers, the same
                                       three questions the wp-admin copy of this
                                       screen asks and in the same words.

                                       This screen kept the old single select while
                                       that one was rewritten, and the gap was not
                                       only cosmetic: one handler serves both forms,
                                       so a form that never posts `exportformat` or
                                       `exportanonymise` takes the defaults in
                                       JSSTexportController::getticketsexport() -
                                       CSV, and identifying. An agent could not
                                       produce a spreadsheet or a PDF, and could not
                                       anonymise a file that was leaving the
                                       building, with nothing on the screen saying
                                       either. The markup is this desk's own
                                       (js-ticket-*) rather than wp-admin's; the
                                       questions, the field names and the reasoning
                                       are the shared ones.
                                       (Roadmap 5.0-ANA-01, 6.5-DATA-05) */
                                    $jsst_clashes = JSSTexportModel::clashingNames();
                                    $jsst_asked = JSSTexportModel::questionNames();
                                    /* The reader's own words, not a category name -
                                       "Order number", "Site URL" tells them what the
                                       tick puts in the file, because they are the
                                       ones who typed those. */
                                    $jsst_asklist = '"' . implode('", "', $jsst_asked['names']) . '"';
                                    if ($jsst_asked['total'] > count($jsst_asked['names'])) {
                                        $jsst_asklist .= ' ' . sprintf(
                                            /* translators: %d is how many further questions there are. */
                                            esc_html(__('and %d more', 'js-support-ticket')),
                                            $jsst_asked['total'] - count($jsst_asked['names']));
                                    } ?>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Custom Fields', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field">
                                            <input type="hidden" name="customfieldsui" value="1" />
                                            <?php if (empty($jsst_asked['names'])) { ?>
                                                <p class="js-ticket-from-field-description"><?php echo esc_html(__('Your ticket form asks nothing beyond the standard questions, so there is nothing extra to add to the file.', 'js-support-ticket')); ?></p>
                                            <?php } else { ?>
                                                <label>
                                                    <input type="checkbox" name="customfieldson" value="1" checked="checked" />
                                                    <?php echo esc_html(sprintf(
                                                        /* translators: %s is a quoted list of the site's own question names. */
                                                        __('Add a column for each question you ask: %s', 'js-support-ticket'),
                                                        $jsst_asklist)); ?>
                                                </label>
                                                <p class="js-ticket-from-field-description"><?php echo esc_html(__('A Form column is added too, so every row shows which form that ticket was raised on.', 'js-support-ticket')); ?></p>
                                            <?php } ?>
                                            <?php /* The layout choice is drawn only where two forms
                                                     genuinely ask a question by the same name, because
                                                     that is the only case in which the two layouts
                                                     differ by so much as one column. */
                                            if (!empty($jsst_clashes)) { ?>
                                                <div class="jsst-subchoice">
                                                    <p class="js-ticket-from-field-description"><?php
                                                        echo esc_html(sprintf(
                                                            /* translators: %s is a list of question names, already quoted. */
                                                            _n('%s is asked on more than one form:', '%s are asked on more than one form:', count($jsst_clashes), 'js-support-ticket'),
                                                            '"' . implode('", "', array_slice($jsst_clashes, 0, 3)) . '"'
                                                            . (count($jsst_clashes) > 3 ? ', …' : '')
                                                        )); ?></p>
                                                    <label>
                                                        <input type="radio" name="customfields" value="<?php echo esc_attr(JSSTexportModel::FIELDS_MERGED); ?>" checked="checked" />
                                                        <?php echo esc_html(__('One column, holding every form\'s answers together', 'js-support-ticket')); ?>
                                                    </label>
                                                    <label>
                                                        <input type="radio" name="customfields" value="<?php echo esc_attr(JSSTexportModel::FIELDS_EXACT); ?>" />
                                                        <?php echo esc_html(__('A separate column for each form, named after it', 'js-support-ticket')); ?>
                                                    </label>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <?php $jsst_formats = class_exists('JSSTexports') ? JSSTexports::formats() : array(); ?>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Format', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field js-ticket-form-field-select">
                                            <?php if (empty($jsst_formats)) { ?>
                                                <p class="js-ticket-from-field-description"><?php echo esc_html(__('CSV, one row per ticket. Opens directly in Excel, LibreOffice Calc, Numbers and Google Sheets.', 'js-support-ticket')); ?></p>
                                            <?php } else { ?>
                                                <?php /* Hand-written rather than built through
                                                   JSSTformfield::select() because an option has to
                                                   carry disabled() on its own - a format this server
                                                   cannot write is shown and greyed rather than hidden,
                                                   so the reason underneath has something to point at. */ ?>
                                                <select name="exportformat" id="jsst-exportformat-fe" class="inputbox js-ticket-form-field-select">
                                                    <?php foreach ($jsst_formats AS $jsst_key => $jsst_format) { ?>
                                                        <option value="<?php echo esc_attr($jsst_key); ?>" <?php disabled(empty($jsst_format['ready'])); ?>><?php
                                                            echo esc_html($jsst_format['label']); ?></option>
                                                    <?php } ?>
                                                </select>
                                                <?php foreach ($jsst_formats AS $jsst_format) {
                                                    if (!empty($jsst_format['ready']) || $jsst_format['reason'] === '') { continue; } ?>
                                                    <p class="js-ticket-from-field-description"><?php echo esc_html($jsst_format['reason']); ?></p>
                                                <?php } ?>
                                                <?php /* PDF keeps eight columns and drops the rest, so a
                                                   tick under Custom Fields cannot be honoured in a
                                                   printed file. Shown rather than enforced: the tick is
                                                   left alone so the choice survives switching back to
                                                   CSV. */
                                                if (!empty($jsst_asked['names'])) { ?>
                                                    <p class="js-ticket-from-field-description jsst-pdfnote" id="jsst-pdfnote-fe" style="display:none;"><?php
                                                        echo esc_html(__('PDF keeps eight columns so the page stays readable — your own questions are left out. Choose CSV or Spreadsheet to include them.', 'js-support-ticket')); ?></p>
                                                    <script type="text/javascript">
                                                        (function () {
                                                            var jsstSel = document.getElementById('jsst-exportformat-fe');
                                                            var jsstNote = document.getElementById('jsst-pdfnote-fe');
                                                            if (!jsstSel || !jsstNote) { return; }
                                                            function jsstPdfNote() {
                                                                jsstNote.style.display = (jsstSel.value === 'pdf') ? '' : 'none';
                                                            }
                                                            jsstSel.addEventListener('change', jsstPdfNote);
                                                            jsstPdfNote();
                                                        }());
                                                    </script>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="js-ticket-from-field-wrp">
                                        <div class="js-ticket-from-field-title"><?php echo esc_html(__('Customers', 'js-support-ticket')); ?>:</div>
                                        <div class="js-ticket-from-field">
                                            <?php /* Offered only where it can be done, exactly as the
                                               wp-admin copy of this screen does it, and for the
                                               reason written out there: the pseudonyms belong to the
                                               Reporting & Compliance bundle, and a tickbox that
                                               silently does nothing is the worst possible control to
                                               put on this question. (Roadmap 6.5-DATA-05) */
                                            if (class_exists('JSSTexports')) { ?>
                                            <label>
                                                <input type="checkbox" name="exportanonymise" value="1" />
                                                <?php echo esc_html(__('Leave the customers out — replace each one with the same pseudonym everywhere they appear', 'js-support-ticket')); ?>
                                            </label>
                                            <p class="js-ticket-from-field-description"><?php echo esc_html(__('For analysis that has to leave the building. The name, login, address and telephone are replaced; the subject, the message and the custom answers are not, because those hold whatever the customer typed in and no amount of column-blanking makes that anonymous. Where a form asks for an order number or an account reference, leave the custom fields out above.', 'js-support-ticket')); ?></p>
                                            <?php } else { ?>
                                            <p class="js-ticket-from-field-description"><?php echo esc_html(__('Leaving the customers out needs the Reporting & Compliance add-on, which is not active on this site. Every export from here names them.', 'js-support-ticket')); ?></p>
                                            <?php } ?>
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
