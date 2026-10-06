<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$jsst_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
wp_enqueue_script('jquery-ui-datepicker');
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
);

?>
<script type="text/javascript">
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
        $('.custom_date').datepicker({
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
<?php JSSTlayout::adminUserPicker(); ?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Export', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <form class="jsstadmin-form" autocomplete="off" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=export&action=jstask&task=getticketsexport'),"get-tickets-export")); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Which tickets', 'js-support-ticket')); ?></legend>
                            <p class="jsst-fieldset-sub"><?php echo esc_html(__('Leave a box alone to put no limit on it. Everything you set here narrows the file.', 'js-support-ticket')); ?></p>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="startdate"><?php echo esc_html(__('Start Date', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('startdate', '', array('class' => 'custom_date js-form-date-field')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="enddate"><?php echo esc_html(__('End Date', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('enddate', '', array('class' => 'custom_date js-form-date-field')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="departmentid"><?php echo esc_html(__('Department', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), '', __('Select Department', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS);  ?></div>
                                </div>
                                <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                                    <div class="jsst-frow jsst-frow-md">
                                        <label class="jsst-flabel" for="staffid"><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), '', __('Select Agent', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS);  ?></div>
                                    </div>
                                <?php } ?>
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="username-text"><?php echo esc_html(__('User', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval">
                                        <div id="username-div"></div>
                                        <div class="jsst-pickfield">
                                            <input class="js-form-diabled-field" type="text" value="" id="username-text" readonly="readonly" data-validation="required" placeholder="<?php echo esc_attr(__('Anyone', 'js-support-ticket')); ?>" />
                                            <a class="jsst-act" href="#" id="userpopup" title="<?php echo esc_attr(__('Select User','js-support-ticket')); ?>"><?php echo esc_html(__('Choose', 'js-support-ticket')); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="priorityid"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('priorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), '', __('Select Priority', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS);  ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="ticketstatus"><?php echo esc_html(__('Ticket Status','js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('ticketstatus', JSSTincluder::getJSModel('status')->getStatusForCombobox(), '', __('Select Ticket Status', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS);  ?></div>
                                </div>
                                <?php if (in_array('overdue', jssupportticket::$_active_addons)) { /* Overdue comes with the Service Levels pack. */ ?>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="isoverdue"><?php echo esc_html(__('Ticket Overdue', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('isoverdue', $jsst_yesno, '', __('Select Ticket Overdue Status', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS);  ?></div>
                                </div>
                                <?php } ?>
                                <?php
                                /* "Single / Multiple Header" used to live here. It chose between
                                   one header row and a header repeated above every ticket, and it
                                   existed because tickets raised on different forms answer
                                   different questions - so no single header could describe them
                                   all. A header per ticket is not a spreadsheet, though: every
                                   reader takes line one as the header and the rest as data, so
                                   the file could not be sorted, filtered or read back in. The
                                   control is gone and these two replace it - the export now
                                   carries one header wide enough for every form in it, with a
                                   blank where a form never asked the question.
                                   (Roadmap 4.0-CORE-10, 5.0-FORM-01) */
                                $jsst_exportmodel = JSSTincluder::getJSModel('export');
                                $jsst_forms = $jsst_exportmodel->formTitles();
                                if (count($jsst_forms) > 1) {
                                    $jsst_formcombo = array();
                                    foreach ($jsst_forms AS $jsst_formid => $jsst_formtitle) {
                                        $jsst_formcombo[] = (object) array('id' => $jsst_formid, 'text' => $jsst_formtitle);
                                    } ?>
                                    <div class="jsst-frow jsst-frow-md">
                                        <label class="jsst-flabel" for="multiformid"><?php echo esc_html(__('Ticket Form', 'js-support-ticket')); ?></label>
                                        <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('multiformid', $jsst_formcombo, '', __('All forms', 'js-support-ticket'), array('class' => 'jsst-select')), JSST_ALLOWED_TAGS); ?></div>
                                        <p class="jsst-fhelp"><?php echo esc_html(__('Narrowing to one form narrows the file to that form\'s questions.', 'js-support-ticket')); ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('What goes in the file', 'js-support-ticket')); ?></legend>
                            <?php
                            /* This was a three-option dropdown - merge the same question
                               across forms, keep each form's apart, or leave them out -
                               and before that, in the add-on, it was "Export Style:
                               Single Header / Multiple Header". Both asked the reader to
                               think in headers and columns to answer a question that is
                               really about whether they want the answers at all.

                               So: a tick, which is the only decision most desks have.
                               The layout choice underneath it is drawn only where two
                               forms genuinely ask a question by the same name, because
                               that is the only case in which the two layouts differ by
                               so much as one column - and when it is drawn it names the
                               question rather than describing the mechanism.

                               "Multiple Header" is not coming back and should not: it
                               printed a fresh header above every ticket, which every
                               spreadsheet reads as data. One header, a Form column, and
                               a file that sorts. (Roadmap 6.5-DATA-05) */
                            $jsst_clashes = JSSTexportModel::clashingNames();
                            $jsst_asked = JSSTexportModel::questionNames();
                            /* The reader's own words, not a category name. "Your own
                               questions" told somebody who had not written this screen
                               nothing at all; "Order number", "Site URL" tells them
                               exactly what the tick puts in the file, because they are
                               the ones who typed those. (Roadmap 6.5-DATA-05) */
                            $jsst_asklist = '"' . implode('", "', $jsst_asked['names']) . '"';
                            if ($jsst_asked['total'] > count($jsst_asked['names'])) {
                                $jsst_asklist .= ' ' . sprintf(
                                    /* translators: %d is how many further questions there are. */
                                    esc_html(__('and %d more', 'js-support-ticket')),
                                    $jsst_asked['total'] - count($jsst_asked['names']));
                            }
                            /* Three real formats now, and each one really is what it
                               says: the spreadsheet is an Office Open XML workbook and
                               the PDF is a PDF, rather than an HTML table with a
                               misleading name. A format this server cannot write is
                               offered as unavailable with the reason, rather than
                               hidden or - worse - offered and then failing.
                               (Roadmap 5.0-ANA-01, closing 3.2-CORE-05) */
                            $jsst_formats = class_exists('JSSTexports') ? JSSTexports::formats() : array();
                            ?>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="jsst-exportformat"><?php echo esc_html(__('Format', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval">
                                        <?php if (empty($jsst_formats)) { ?>
                                            <p class="jsst-hint"><?php echo esc_html(__('CSV, one row per ticket. Opens directly in Excel, LibreOffice Calc, Numbers and Google Sheets.', 'js-support-ticket')); ?></p>
                                        <?php } else { ?>
                                            <?php /* Hand-written rather than built through JSSTformfield::select()
                                               because an option has to carry disabled() on its own - a format
                                               this server cannot write is shown and greyed rather than hidden,
                                               so the reason underneath has something to point at. */ ?>
                                            <select name="exportformat" id="jsst-exportformat" class="jsst-select">
                                                <?php foreach ($jsst_formats AS $jsst_key => $jsst_format) { ?>
                                                    <option value="<?php echo esc_attr($jsst_key); ?>" <?php disabled(empty($jsst_format['ready'])); ?>><?php
                                                        echo esc_html($jsst_format['label']); ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </div>
                                    <?php foreach ($jsst_formats AS $jsst_format) {
                                        if (!empty($jsst_format['ready']) || $jsst_format['reason'] === '') { continue; } ?>
                                        <p class="jsst-fhelp"><?php echo esc_html($jsst_format['reason']); ?></p>
                                    <?php }
                                    /* PDF keeps eight columns and drops the rest - see
                                       JSSTexports::PDF_COLUMNS - so a tick under Custom Fields cannot
                                       be honoured in a printed file. Saying nothing meant the screen
                                       promised columns the format was always going to throw away.
                                       Shown rather than enforced: the tick is left alone so the choice
                                       survives switching back to CSV, and the message names the formats
                                       that can carry it instead of only refusing. Hidden when there are
                                       no custom questions - there is then nothing to leave out.
                                       (Roadmap 6.5-DATA-05) */
                                    if (!empty($jsst_formats) && !empty($jsst_asked['names'])) { ?>
                                        <p class="jsst-fhelp jsst-pdfnote" id="jsst-pdfnote" style="display:none;"><?php
                                            echo esc_html(__('PDF keeps eight columns so the page stays readable — your own questions are left out. Choose CSV or Spreadsheet to include them.', 'js-support-ticket')); ?></p>
                                        <script type="text/javascript">
                                            (function () {
                                                var jsstSel = document.getElementById('jsst-exportformat');
                                                var jsstNote = document.getElementById('jsst-pdfnote');
                                                if (!jsstSel || !jsstNote) { return; }
                                                function jsstPdfNote() {
                                                    jsstNote.style.display = (jsstSel.value === 'pdf') ? '' : 'none';
                                                }
                                                jsstSel.addEventListener('change', jsstPdfNote);
                                                jsstPdfNote();
                                            }());
                                        </script>
                                    <?php } ?>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Your own questions', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval">
                                        <input type="hidden" name="customfieldsui" value="1" />
                                        <?php if (empty($jsst_asked['names'])) { ?>
                                            <p class="jsst-hint"><?php echo esc_html(__('Your ticket form asks nothing beyond the standard questions, so there is nothing extra to add to the file.', 'js-support-ticket')); ?></p>
                                        <?php } else { ?>
                                            <label class="jsst-check">
                                                <input type="checkbox" name="customfieldson" value="1" checked="checked" />
                                                <span><?php echo esc_html(sprintf(
                                                    /* translators: %s is a quoted list of the site's own question names. */
                                                    __('Add a column for each question you ask: %s', 'js-support-ticket'),
                                                    $jsst_asklist)); ?></span>
                                            </label>
                                            <p class="jsst-fhelp"><?php echo esc_html(__('A Form column is added too, so every row shows which form that ticket was raised on.', 'js-support-ticket')); ?></p>
                                        <?php } ?>
                                        <?php if (!empty($jsst_clashes)) { ?>
                                            <div class="jsst-subchoice">
                                                <p class="jsst-fhelp"><?php
                                                    echo esc_html(sprintf(
                                                        /* translators: %s is a list of question names, already quoted. */
                                                        _n('%s is asked on more than one form:', '%s are asked on more than one form:', count($jsst_clashes), 'js-support-ticket'),
                                                        '"' . implode('", "', array_slice($jsst_clashes, 0, 3)) . '"'
                                                        . (count($jsst_clashes) > 3 ? ', …' : '')
                                                    )); ?></p>
                                                <label class="jsst-check">
                                                    <input type="radio" name="customfields" value="<?php echo esc_attr(JSSTexportModel::FIELDS_MERGED); ?>" checked="checked" />
                                                    <span><?php echo esc_html(__('One column, holding every form\'s answers together', 'js-support-ticket')); ?></span>
                                                </label>
                                                <label class="jsst-check">
                                                    <input type="radio" name="customfields" value="<?php echo esc_attr(JSSTexportModel::FIELDS_EXACT); ?>" />
                                                    <span><?php echo esc_html(__('A separate column for each form, named after it', 'js-support-ticket')); ?></span>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Customers', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval">
                                        <?php /* Offered only where it can actually be done. The pseudonyms
                                           are JSSTexports::anonymiseRow(), which the Reporting &
                                           Compliance bundle carries, and a box that silently does
                                           nothing is the worst possible control to put on this
                                           particular question. Same handling the format select above
                                           gives a writer the host cannot provide: say why rather than
                                           leave an option that will not work. (Roadmap 6.5-DATA-05) */
                                        if (class_exists('JSSTexports')) { ?>
                                            <label class="jsst-check">
                                                <input type="checkbox" name="exportanonymise" value="1" />
                                                <span><?php echo esc_html(__('Leave the customers out — replace each one with the same pseudonym everywhere they appear', 'js-support-ticket')); ?></span>
                                            </label>
                                            <p class="jsst-fhelp"><?php echo esc_html(__('For analysis that has to leave the building. The name, login, address and telephone are replaced; the subject, the message and the custom answers are not, because those hold whatever the customer typed in and no amount of column-blanking makes that anonymous. Where a form asks for an order number or an account reference, leave the custom fields out above.', 'js-support-ticket')); ?></p>
                                        <?php } else { ?>
                                            <p class="jsst-fhelp"><?php echo esc_html(__('Leaving the customers out needs the Reporting & Compliance add-on, which is not active on this site. Every export from here names them.', 'js-support-ticket')); ?></p>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('uid',''), JSST_ALLOWED_TAGS);  ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('The file is built and downloaded straight away.', 'js-support-ticket')); ?></span>
                        <button type="submit" class="jsst-btn jsst-btn-primary"><?php echo esc_html(__('Export','js-support-ticket')); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
