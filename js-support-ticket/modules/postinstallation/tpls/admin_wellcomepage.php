<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
/**
 * Quick setup: the first screen after activation.
 *
 * Drawn with the same header, panel and buttons as the rest of the desk, and
 * lists what the next screens will ask, so starting is an informed choice.
 */
$jsst_wizard_current = '';

/* What is already true of this desk, read from the same source the Setup
   checklist reads so the two can never disagree. The screen used to describe
   only what the wizard was about to ask, which is a page that knows nothing
   about you -- and on a desk that is already half configured that is both dull
   and slightly wrong. Only the OUTSTANDING items are named: the checklist
   itself is the place for the full list, and repeating it here would make this
   a copy of a screen two clicks away. */
$jsst_state_steps = class_exists('JSSTsetup') ? JSSTsetup::steps() : array();
$jsst_state_prog  = class_exists('JSSTsetup') ? JSSTsetup::progress() : array('done' => 0, 'total' => 0);
$jsst_state_todo  = array();
foreach ($jsst_state_steps as $jsst_state_step) {
    if (empty($jsst_state_step['done'])) {
        $jsst_state_todo[] = $jsst_state_step['title'];
    }
}
$jsst_state_pct = !empty($jsst_state_prog['total'])
    ? round(($jsst_state_prog['done'] / $jsst_state_prog['total']) * 100) : 0;

/* $jsst_wizard_summary is resolved by admin_wizardnav.php, included below. */
?>
<?php /* No side menu here: setup is one focused task, and the breadcrumb is the way out. */ ?>
<?php /* No side menu and no admin chrome: this is a first-run screen, so it is
         built as one centred hero that fills the window, the way the screen it
         replaces did -- but in this admin's own language rather than a gradient
         frame. The mark is drawn inline rather than as an image: `wp_kses`
         lowercases attribute names, which turns `viewBox` into `viewbox` and
         strips `stroke-width`, so an icon sent through it loses its coordinate
         system. layout.php prints its icons the same way. */ ?>
<div id="jsstadmin-wrapper" class="jsst-wizard-page jsst-wizard-hero-page">
    <div id="jsstadmin-data">
        <?php include __DIR__ . '/admin_wizardnav.php'; ?>
        <div id="jsstadmin-data-wrp">
            <div class="jsst-card jsst-wizard-welcome">
                <div class="jsst-card-body">

                    <span class="jsst-wizard-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 14v-2a8 8 0 0 1 16 0v2" />
                            <rect x="2" y="13" width="4.5" height="7" rx="2" />
                            <rect x="17.5" y="13" width="4.5" height="7" rx="2" />
                            <path d="M20 20v.5a2.5 2.5 0 0 1-2.5 2.5H13" />
                        </svg>
                    </span>

                    <h2 class="jsst-wizard-title"><?php echo esc_html(__('Welcome to JS Help Desk', 'js-support-ticket')); ?></h2>
                    <p class="jsst-wizard-lede"><?php echo esc_html(__('A few settings get your help desk ready for its first ticket. Every one of them can be changed later under Configurations.', 'js-support-ticket')); ?></p>

                    <?php if (!empty($jsst_state_prog['total'])) { ?>
                        <div class="jsst-wizard-state">
                            <div class="jsst-wizard-state-head">
                                <span class="jsst-metric-label"><?php echo esc_html(__('Where your desk stands', 'js-support-ticket')); ?></span>
                                <span class="jsst-wizard-state-count"><?php echo esc_html(sprintf(
                                    /* translators: 1: how many setup items are done, 2: how many there are */
                                    __('%1$d of %2$d ready', 'js-support-ticket'),
                                    (int) $jsst_state_prog['done'], (int) $jsst_state_prog['total']
                                )); ?></span>
                            </div>
                            <div class="jsst-wizard-bar" role="img" aria-label="<?php echo esc_attr(sprintf(
                                /* translators: %d: a percentage */
                                __('%d%% ready', 'js-support-ticket'), $jsst_state_pct)); ?>">
                                <span style="width: <?php echo esc_attr($jsst_state_pct); ?>%"></span>
                            </div>
                            <?php if (empty($jsst_state_todo)) { ?>
                                <p class="jsst-fhelp"><?php echo esc_html(__('Everything the desk needs is already in place. Quick setup only changes how it behaves.', 'js-support-ticket')); ?></p>
                            <?php } else { ?>
                                <p class="jsst-fhelp"><?php echo esc_html(__('Still outstanding:', 'js-support-ticket')); ?>
                                    <?php echo esc_html(implode(' · ', $jsst_state_todo)); ?></p>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <div class="jsst-wizard-actions">
                        <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=stepone')); ?>"><?php echo esc_html(__('Start quick setup', 'js-support-ticket')); ?></a>
                        <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>"><?php echo esc_html(__('Skip to dashboard', 'js-support-ticket')); ?></a>
                    </div>

                    <p class="jsst-metric-label jsst-wizard-asklabel"><?php echo esc_html(__('What quick setup will ask', 'js-support-ticket')); ?></p>
                    <?php /* The same timeline the step pages carry, so the first
                             screen already shows the shape of what follows: the
                             first step lit as the place to start, the rest to
                             come, each with the one line it will ask about. */ ?>
                    <ol class="jsst-wizard-steps jsst-wizard-overview">
                        <?php $jsst_overview_i = 0;
                        foreach ($jsst_wizard_steps as $jsst_key => $jsst_step) {
                            if (!isset($jsst_wizard_summary[$jsst_key])) {
                                continue;
                            }
                            ++$jsst_overview_i; ?>
                            <li class="jsst-wizard-step <?php echo ($jsst_overview_i === 1) ? 'jsst-wizard-step-current' : 'jsst-wizard-step-todo'; ?>">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=postinstallation&jstlay=' . $jsst_step['layout'])); ?>">
                                    <span class="jsst-wizard-num" aria-hidden="true"><?php echo esc_html(number_format_i18n($jsst_overview_i)); ?></span>
                                    <span class="jsst-wizard-label"><?php echo esc_html($jsst_step['label']); ?></span>
                                    <span class="jsst-wizard-overview-text"><?php echo esc_html($jsst_wizard_summary[$jsst_key]); ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
