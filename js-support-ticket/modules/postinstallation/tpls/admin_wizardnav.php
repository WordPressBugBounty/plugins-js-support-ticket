<?php
if (!defined('ABSPATH')) die('Restricted Access');
/**
 * Quick setup: page header and step indicator, shared by every wizard screen.
 *
 * Included twice by each step template, with $jsst_wizard_current set to a key
 * of JSSTPostinstallationModel::getWizardSteps() ('' on the welcome screen):
 * once before #jsstadmin-data-wrp for the header, and once as its first child
 * with $jsst_wizard_part = 'steps' for the step list, so the list lines up
 * with the panels below it at every width.
 * It is not a layout of its own: the controller has no case for it, so
 * opening it by URL exits before anything is drawn.
 */
$jsst_wizard_steps   = JSSTincluder::getJSModel('postinstallation')->getWizardSteps();
$jsst_wizard_current = isset($jsst_wizard_current) ? $jsst_wizard_current : '';
$jsst_wizard_keys    = array_keys($jsst_wizard_steps);
$jsst_wizard_index   = array_search($jsst_wizard_current, $jsst_wizard_keys, true);

/* What each step is for, in one line. Lives here rather than in the welcome
   template because two screens need it now: the welcome overview lists them
   all, and a step head shows its own. One list, so they cannot drift. */
$jsst_wizard_summary = array(
    'general'     => __('Help desk name, date format, attachment size and file types.', 'js-support-ticket'),
    'tickets'     => __('Who may open tickets, ticket limits, reopening and the security check.', 'js-support-ticket'),
    'translation' => __('Download the language file for your site language.', 'js-support-ticket'),
    'feedback'    => __('When the feedback request is sent after a ticket closes.', 'js-support-ticket'),
);

/* No admin chrome on these screens: no breadcrumb, no title bar, no version
   strip. Setup is one focused task and the page's own card carries the
   heading, while the step list below says where you are -- the breadcrumb was
   also claiming that Quick setup lives INSIDE the Setup checklist, which the
   flow contradicts by sending you to that checklist when the wizard finishes.

   The first include still runs, because it is what resolves
   $jsst_wizard_steps for the templates, and a heading is still emitted for
   assistive technology: a page with no h1 at all is a worse outcome than one
   whose h1 happens to be invisible. */
if (!isset($jsst_wizard_part) || $jsst_wizard_part !== 'steps') {
    /* The welcome screen carries its own hero heading, so it only needs the
       h1 for assistive technology. The step pages get a visible one below. */
    if ($jsst_wizard_index === false) { ?>
        <h1 class="screen-reader-text"><?php echo esc_html(__('Quick setup', 'js-support-ticket')); ?></h1>
    <?php }
    return;
}
$jsst_wizard_part = '';
if ($jsst_wizard_index === false) {
    return;
}
/* The previous step's save result ("Configuration has been changed", or why
   it was not), shown above the step list rather than lost on the redirect. */
if ($jsst_wizard_current !== 'done') {
    JSSTmessage::getMessage();
}
?>
<?php /* The step pages began abruptly at the chips, because taking the admin
         chrome off took the title and "Step N of M" with it -- the chips imply
         a position but never state one, and nothing said what the step was
         for. A head of the wizard's own, inside the page rather than in the
         admin frame: the name of the thing, where you are in it, and what this
         step is about to ask. */ ?>
<div class="jsst-wizard-head">
    <div class="jsst-wizard-head-top">
        <h1 class="jsst-wizard-head-title"><?php echo esc_html(__('Quick setup', 'js-support-ticket')); ?></h1>
        <span class="jsst-wizard-head-count"><?php echo esc_html(sprintf(
            /* translators: 1: this step's number, 2: how many steps there are */
            __('Step %1$d of %2$d', 'js-support-ticket'),
            $jsst_wizard_index + 1,
            count($jsst_wizard_keys)
        )); ?></span>
    </div>
    <?php $jsst_head_now = $jsst_wizard_steps[$jsst_wizard_keys[$jsst_wizard_index]];
    if (isset($jsst_wizard_summary[$jsst_wizard_current])) { ?>
        <p class="jsst-wizard-head-sub"><strong><?php echo esc_html($jsst_head_now['label']); ?></strong>
            <?php echo esc_html($jsst_wizard_summary[$jsst_wizard_current]); ?></p>
    <?php } ?>
</div>
<ol class="jsst-wizard-steps">
    <?php foreach ($jsst_wizard_keys as $jsst_i => $jsst_key) {
        $jsst_step = $jsst_wizard_steps[$jsst_key];
        if ($jsst_i < $jsst_wizard_index) {
            $jsst_state = 'jsst-wizard-step-done';
        } elseif ($jsst_i === $jsst_wizard_index) {
            $jsst_state = 'jsst-wizard-step-current';
        } else {
            $jsst_state = 'jsst-wizard-step-todo';
        }
        ?>
        <li class="jsst-wizard-step <?php echo esc_attr($jsst_state); ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=' . $jsst_step['layout'])); ?>"<?php echo ($jsst_i === $jsst_wizard_index) ? ' aria-current="step"' : ''; ?>>
                <span class="jsst-wizard-num" aria-hidden="true"><?php echo ($jsst_i < $jsst_wizard_index) ? '&#10003;' : esc_html(number_format_i18n($jsst_i + 1)); ?></span>
                <span class="jsst-wizard-label"><?php echo esc_html($jsst_step['label']); ?></span>
            </a>
        </li>
    <?php } ?>
</ol>
