<?php
if (!defined('ABSPATH')) die('Restricted Access');
/**
 * Quick setup, step 2: tickets.
 *
 * Field names, values and the save action are the ones this step has always
 * posted; only the markup follows the desk's form components.
 */
$jsst_wizard_current = 'tickets';
$jsst_config = isset(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : array();
$jsst_cfg = function ($jsst_name) use ($jsst_config) {
    return isset($jsst_config[$jsst_name]) ? $jsst_config[$jsst_name] : '';
};
$jsst_yesno = array('1' => esc_html(__('Yes', 'js-support-ticket')), '2' => esc_html(__('No', 'js-support-ticket')));
$jsst_ticketidsequence = array('1' => esc_html(__('Random', 'js-support-ticket')), '2' => esc_html(__('Sequential', 'js-support-ticket')));
$jsst_owncaptchaoparend = array(
    (object) array('id' => '2', 'text' => esc_html('2')),
    (object) array('id' => '3', 'text' => esc_html('3')),
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
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Who can open tickets', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Visitors can open tickets', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('visitor_can_create_ticket', $jsst_yesno, $jsst_cfg('visitor_can_create_ticket')), JSST_ALLOWED_TAGS); ?></div></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('No means a customer signs in first.', 'js-support-ticket')); ?></p>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Customers can print tickets', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('print_ticket_user', $jsst_yesno, $jsst_cfg('print_ticket_user')), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('ticketid_sequence', $jsst_ticketidsequence, $jsst_cfg('ticketid_sequence')), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Limits per customer', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="maximum_tickets"><?php echo esc_html(__('Tickets in total', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('maximum_tickets', $jsst_cfg('maximum_tickets'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="maximum_open_tickets"><?php echo esc_html(__('Open at once', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('maximum_open_tickets', $jsst_cfg('maximum_open_tickets'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="reopen_ticket_within_days"><?php echo esc_html(__('Reopen within (days)', 'js-support-ticket')); ?> <span class="jsst-req">*</span></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::text('reopen_ticket_within_days', $jsst_cfg('reopen_ticket_within_days'), array('data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('How long after closing a customer may reopen a ticket.', 'js-support-ticket')); ?></p>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Security check on the visitor form', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Ask visitors a security question', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('show_captcha_on_visitor_from_ticket', $jsst_yesno, $jsst_cfg('show_captcha_on_visitor_from_ticket')), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <label class="jsst-flabel" for="owncaptcha_totaloperand"><?php echo esc_html(__('Numbers in the question', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><?php echo wp_kses(JSSTformfield::select('owncaptcha_totaloperand', $jsst_owncaptchaoparend, $jsst_cfg('owncaptcha_totaloperand')), JSST_ALLOWED_TAGS); ?></div>
                                </div>
                                <div class="jsst-frow jsst-frow-sm">
                                    <span class="jsst-flabel"><?php echo esc_html(__('Subtraction answers are positive', 'js-support-ticket')); ?></span>
                                    <div class="jsst-fval"><div class="jsst-seg"><?php echo wp_kses(JSSTformfield::radiobutton('owncaptcha_subtractionans', $jsst_yesno, $jsst_cfg('owncaptcha_subtractionans')), JSST_ALLOWED_TAGS); ?></div></div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'postinstallation_save'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('step', 2), JSST_ALLOWED_TAGS); ?>
                    <div class="jsst-formfoot">
                        <span class="jsst-formfoot-note"><?php echo esc_html(__('Required fields are marked', 'js-support-ticket')); ?> <span class="jsst-req" aria-hidden="true">*</span></span>
                        <?php /* The way out. Every step had only Back and Save and continue,
                                 and with the side menu, the breadcrumb and the page header all
                                 gone there was no plugin-level exit from the middle of the
                                 wizard -- the welcome screen offered one and the steps did not.
                                 Same wording and destination as the welcome screen's, so it is
                                 the same escape rather than a second idea. */ ?>
                        <a class="jsst-btn jsst-btn-quiet" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>"><?php echo esc_html(__('Skip to dashboard', 'js-support-ticket')); ?></a>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=stepone')); ?>"><?php echo esc_html(__('Back', 'js-support-ticket')); ?></a>
                        <button type="submit" class="jsst-btn jsst-btn-primary"><?php echo esc_html(__('Save and continue', 'js-support-ticket')); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
