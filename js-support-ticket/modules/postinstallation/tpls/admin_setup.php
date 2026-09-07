<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Five-minute setup. (Roadmap 4.0-UX-01)
 *
 * A checklist that re-derives its state from the database every time it is
 * drawn, so it says what is true rather than what has been clicked.
 */
if (!class_exists('JSSTsetup')) {
    // The bootstrap did not load the class. Say so rather than fataling.
    echo '<div class="notice notice-error"><p>' . esc_html(__('The setup checklist could not be loaded. Deactivate and reactivate JS Help Desk.', 'js-support-ticket')) . '</p></div>';
    return;
}
$jsst_steps    = JSSTsetup::steps();
$jsst_progress = JSSTsetup::progress();
$jsst_complete = ($jsst_progress['done'] >= $jsst_progress['total']);
$jsst_percent  = $jsst_progress['total'] ? round(($jsst_progress['done'] / $jsst_progress['total']) * 100) : 0;

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Setup','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Setup', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <div class="jsst-setup-summary <?php echo esc_attr($jsst_complete ? 'jsst-setup-complete' : ''); ?>">
                <div class="jsst-setup-count">
                    <?php echo esc_html(sprintf(
                        /* translators: 1: how many steps are done, 2: how many there are */
                        __('%1$d of %2$d done', 'js-support-ticket'),
                        $jsst_progress['done'],
                        $jsst_progress['total']
                    )); ?>
                </div>
                <div class="jsst-setup-bar" role="progressbar" aria-valuenow="<?php echo esc_attr($jsst_percent); ?>" aria-valuemin="0" aria-valuemax="100">
                    <span class="jsst-setup-bar-fill" style="width:<?php echo esc_attr($jsst_percent); ?>%"></span>
                </div>
                <div class="jsst-setup-lede">
                    <?php
                    echo esc_html($jsst_complete
                            ? __('Everything is in place. This list checks itself each time you open it, so if something changes later it will say so here.', 'js-support-ticket')
                            : __('Each of these is checked against your site, not against what you have clicked. Do them in any order.', 'js-support-ticket'));
                    ?>
                </div>
            </div>

            <?php foreach ($jsst_steps AS $jsst_key => $jsst_step) { ?>
                <div class="jsst-setup-step <?php echo esc_attr(!empty($jsst_step['done']) ? 'jsst-setup-step-done' : 'jsst-setup-step-todo'); ?>">
                    <div class="jsst-setup-mark" aria-hidden="true"><?php echo esc_html(!empty($jsst_step['done']) ? '✓' : ''); ?></div>
                    <div class="jsst-setup-body">
                        <div class="jsst-setup-title"><?php echo esc_html($jsst_step['title']); ?></div>
                        <div class="jsst-setup-detail"><?php echo esc_html($jsst_step['detail']); ?></div>
                        <div class="jsst-setup-why"><?php echo esc_html($jsst_step['why']); ?></div>
                    </div>
                    <div class="jsst-setup-action">
                        <?php
                        if (empty($jsst_step['action'])) {
                            // Nothing to do — either finished or not applicable.
                            if (!empty($jsst_step['link'])) { ?>
                                <a class="button" href="<?php echo esc_url($jsst_step['link']); ?>" target="_blank" rel="noopener"><?php echo esc_html(__('View', 'js-support-ticket')); ?></a>
                            <?php }
                        } elseif (!empty($jsst_step['task'])) {
                            // The one-click kind: no decisions to make, so this
                            // does the work rather than sending you elsewhere.
                            ?>
                            <form method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=postinstallation&task=' . rawurlencode($jsst_step['task']) . '&action=jstask'), 'jsst-setup-' . $jsst_step['task'])); ?>">
                                <?php echo wp_kses(JSSTformfield::submitbutton('runsetupstep', $jsst_step['action'], array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            </form>
                        <?php } elseif (!empty($jsst_step['url'])) { ?>
                            <a class="button <?php echo esc_attr(empty($jsst_step['done']) ? 'js-form-save' : ''); ?>" href="<?php echo esc_url($jsst_step['url']); ?>"><?php echo esc_html($jsst_step['action']); ?></a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <div class="jsst-setup-footer">
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=postinstallation&task=dismisssetup&action=jstask'), 'jsst-setup-dismiss')); ?>">
                    <?php echo esc_html(__('Hide this from the menu', 'js-support-ticket')); ?>
                </a>
                <span class="jsst-setup-footer-note"><?php echo esc_html(__('You can always reach it again from the Dashboard.', 'js-support-ticket')); ?></span>
            </div>

        </div>
    </div>
</div>
