<?php
if (!defined('ABSPATH')) die('Restricted Access');
/**
 * Quick setup: when the feedback request goes out. Feedback add-on only; the
 * controller sends a site without it straight to the last step.
 *
 * The delay type uses the values the rest of the plugin reads - 1 days,
 * 2 hours (JSSTticketModel and the Configurations screen). This screen used to
 * offer 0 and 1, which saved "hours" as "days".
 */
$jsst_wizard_current = 'feedback';
$jsst_config = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : array();
$jsst_cfg = function ($jsst_name) use ($jsst_config) {
    return isset($jsst_config[$jsst_name]) ? $jsst_config[$jsst_name] : '';
};
$jsst_delaytype = array('1' => esc_html(__('Days', 'js-support-ticket')), '2' => esc_html(__('Hours', 'js-support-ticket')));
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
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Feedback request', 'js-support-ticket')); ?></legend>
                            <p class="jsst-fhelp"><?php echo esc_html(__('After a ticket is closed the customer is asked how it went. Choose how long to wait before asking.', 'js-support-ticket')); ?></p>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="feedback_email_delay"><?php echo esc_html(__('Wait', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('feedback_email_delay', $jsst_cfg('feedback_email_delay'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Counted in', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('feedback_email_delay_type', $jsst_delaytype, $jsst_cfg('feedback_email_delay_type')), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'postinstallation_save'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('step', 3), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <?php /* The way out. Every step had only Back and Save and continue,
                                 and with the side menu, the breadcrumb and the page header all
                                 gone there was no plugin-level exit from the middle of the
                                 wizard -- the welcome screen offered one and the steps did not.
                                 Same wording and destination as the welcome screen's, so it is
                                 the same escape rather than a second idea. */ ?>
                        <a class="jsst-btn jsst-btn-quiet" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>"><?php echo esc_html(__('Skip to dashboard', 'js-support-ticket')); ?></a>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=steptwo')); ?>"><?php echo esc_html(__('Back', 'js-support-ticket')); ?></a>
                        <button type="submit" class="jsst-btn jsst-btn-primary"><?php echo esc_html(__('Save and continue', 'js-support-ticket')); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
