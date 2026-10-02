<?php
if (!defined('ABSPATH')) die('Restricted Access');
/**
 * Quick setup, step 1: general settings.
 *
 * Field names, values and the save action are the ones this step has always
 * posted; only the markup follows the desk's form components.
 */
$jsst_wizard_current = 'general';
$jsst_config = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : array();
$jsst_cfg = function ($jsst_name) use ($jsst_config) {
    return isset($jsst_config[$jsst_name]) ? $jsst_config[$jsst_name] : '';
};
$jsst_yesno = array('1' => esc_html(__('Yes', 'js-support-ticket')), '2' => esc_html(__('No', 'js-support-ticket')));
$jsst_date_format = array(
    (object) array('id' => 'd-m-Y', 'text' => esc_html(__('DD-MM-YYYY', 'js-support-ticket'))),
    (object) array('id' => 'm-d-Y', 'text' => esc_html(__('MM-DD-YYYY', 'js-support-ticket'))),
    (object) array('id' => 'Y-m-d', 'text' => esc_html(__('YYYY-MM-DD', 'js-support-ticket'))),
);
?>
<?php /* No side menu here: setup is one focused task, and the breadcrumb is the way out. */ ?>
<div id="jsstadmin-wrapper" class="jsst-wizard-page">
    <div id="jsstadmin-data">
        <?php include __DIR__ . '/admin_wizardnav.php'; ?>
        <div id="jsstadmin-data-wrp">
            <?php $jsst_wizard_part = 'steps'; include __DIR__ . '/admin_wizardnav.php'; ?>
            <form id="jssupportticket-form-ins" class="jsstadmin-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=postinstallation&task=save&action=jstask'), 'save')); ?>">
                <div class="jsst-formpanel">
                    <div class="jsst-formbody">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Your help desk', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="title"><?php echo esc_html(__('Title', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('title', $jsst_cfg('title'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Shown at the top of the customer portal and in e-mails.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="date_format"><?php echo esc_html(__('Date format', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('date_format', $jsst_date_format, $jsst_cfg('date_format')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow-break"></div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="ticket_auto_close"><?php echo esc_html(__('Close unanswered tickets after', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('ticket_auto_close', $jsst_cfg('ticket_auto_close'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Days without a reply from the customer.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Show counts on My Tickets', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('count_on_myticket', $jsst_yesno, $jsst_cfg('count_on_myticket')), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="file_maximum_size"><?php echo esc_html(__('Maximum file size (KB)', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('file_maximum_size', $jsst_cfg('file_maximum_size'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-md">
                                    <label class="jsst-flabel" for="data_directory"><?php echo esc_html(__('Data folder', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('data_directory', $jsst_cfg('data_directory'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Rename the existing folder on the server first, then change the name here.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-full">
                                    <label class="jsst-flabel" for="file_extension"><?php echo esc_html(__('Allowed file types', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::textarea('file_extension', $jsst_cfg('file_extension'), array('data-validation' => 'required', 'rows' => '3')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('Comma-separated extensions without dots, for example: jpg,png,pdf.', 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'postinstallation_save'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('step', 1), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <?php /* The way out. Every step had only Back and Save and continue,
                                 and with the side menu, the breadcrumb and the page header all
                                 gone there was no plugin-level exit from the middle of the
                                 wizard -- the welcome screen offered one and the steps did not.
                                 Same wording and destination as the welcome screen's, so it is
                                 the same escape rather than a second idea. */ ?>
                        <a class="jsst-btn jsst-btn-quiet" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>"><?php echo esc_html(__('Skip to dashboard', 'js-support-ticket')); ?></a>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=wellcomepage')); ?>"><?php echo esc_html(__('Back', 'js-support-ticket')); ?></a>
                        <button type="submit" class="jsst-btn jsst-btn-primary"><?php echo esc_html(__('Save and continue', 'js-support-ticket')); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
