<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Moving the plugin's tables onto InnoDB. (Roadmap 4.0-PERF-03)
 *
 * The screen leads with what cannot be converted rather than what can. A table
 * this refuses is refused for a reason the database will not let it work around,
 * and somebody who presses the button expecting all of them and gets most of
 * them has been misled by the screen, not by the engine.
 */
if (!class_exists('JSSTstorageengine')) {
    echo '<div class="notice notice-error"><p>' . esc_html(__('The storage engine tool could not be loaded. Deactivate and reactivate JS Help Desk.', 'js-support-ticket')) . '</p></div>';
    return;
}
JSSTmessage::getMessage();

$jsst_plan    = jssupportticket::$jsst_data['storageplan'];
$jsst_state   = jssupportticket::$jsst_data['storagestate'];
$jsst_summary = jssupportticket::$jsst_data['storagesummary'];
$jsst_running = !empty($jsst_summary['running']);

$jsst_size = function ($jsst_bytes) {
    return size_format((float) $jsst_bytes, ((float) $jsst_bytes > 1048576) ? 1 : 0);
};
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
                        <li><?php echo esc_html(__('Storage Engine','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Storage Engine', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <?php
            if (is_wp_error($jsst_plan)) { ?>
                <div class="jsst-status-card jsst-status-warn">
                    <div class="jsst-status-title"><?php echo esc_html(__('Nothing can be converted here', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html($jsst_plan->get_error_message()); ?></div>
                </div>
                <?php
            } else { ?>

                <div class="jsst-status-card <?php echo esc_attr(empty($jsst_plan['pending']) && empty($jsst_plan['blocked']) ? 'jsst-status-ok' : ''); ?>">
                    <div class="jsst-status-title"><?php echo esc_html(__('Where things stand', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(__('InnoDB gives row-level locking and crash recovery. On MyISAM a single slow write locks the whole table, which on a busy ticket list is felt by everybody at once.', 'js-support-ticket')); ?></div>
                    <table class="jsst-status-table">
                        <tr><td><?php echo esc_html(__('Already on InnoDB', 'js-support-ticket')); ?></td><td><?php echo esc_html(number_format_i18n(count($jsst_plan['converted']))); ?></td></tr>
                        <tr><td><?php echo esc_html(__('Can be converted', 'js-support-ticket')); ?></td><td><?php echo esc_html(number_format_i18n(count($jsst_plan['pending']))); ?><?php if (!empty($jsst_plan['pending'])) { ?> <span class="jsst-status-when"><?php echo esc_html(sprintf(
                            /* translators: 1: total size of the tables, 2: size of the largest one */
                            __('%1$s in total, largest %2$s', 'js-support-ticket'),
                            $jsst_size($jsst_plan['bytes']),
                            $jsst_size($jsst_plan['largest'])
                        )); ?></span><?php } ?></td></tr>
                        <tr><td><?php echo esc_html(__('Cannot be converted', 'js-support-ticket')); ?></td><td><?php echo esc_html(number_format_i18n(count($jsst_plan['blocked']))); ?></td></tr>
                    </table>
                </div>

                <?php if (!empty($jsst_plan['blocked'])) { ?>
                    <div class="jsst-status-card jsst-status-warn">
                        <div class="jsst-status-title"><?php echo esc_html(__('These will be left alone', 'js-support-ticket')); ?></div>
                        <div class="jsst-status-note"><?php echo esc_html(__('Each of these would fail or lose something if converted, so the run skips them. They keep working exactly as they do now.', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <?php foreach ($jsst_plan['blocked'] AS $jsst_name => $jsst_table) { ?>
                                <tr>
                                    <td><span class="jsst-status-path"><?php echo esc_html($jsst_name); ?></span></td>
                                    <td><?php echo esc_html($jsst_table['reason']); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php }

                if (!empty($jsst_state['failed'])) { ?>
                    <div class="jsst-status-card jsst-status-warn">
                        <div class="jsst-status-title"><?php echo esc_html(__('These were tried and did not convert', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <?php foreach ($jsst_state['failed'] AS $jsst_name => $jsst_why) { ?>
                                <tr>
                                    <td><span class="jsst-status-path"><?php echo esc_html($jsst_name); ?></span></td>
                                    <td class="jsst-status-error"><?php echo esc_html($jsst_why); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php }

                if (!empty($jsst_plan['pending'])) { ?>
                    <div class="jsst-status-card">
                        <div class="jsst-status-title"><?php echo esc_html(__('These will be converted', 'js-support-ticket')); ?></div>
                        <div class="jsst-status-note"><?php echo esc_html(__('Smallest first, one table at a time. A table is rewritten whole and is locked while it is — the sizes below are the only honest guide to how long each will take.', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <tr>
                                <td><strong><?php echo esc_html(__('Table', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('On', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Size', 'js-support-ticket')); ?></strong></td>
                            </tr>
                            <?php foreach ($jsst_plan['pending'] AS $jsst_name => $jsst_table) { ?>
                                <tr>
                                    <td><span class="jsst-status-path"><?php echo esc_html($jsst_name); ?></span></td>
                                    <td><?php echo esc_html($jsst_table['engine']); ?></td>
                                    <td><?php echo esc_html($jsst_size($jsst_table['bytes'])); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php } ?>

                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html($jsst_running ? __('A conversion is part way through', 'js-support-ticket') : __('Run it', 'js-support-ticket')); ?></div>
                    <?php if ($jsst_running) { ?>
                        <div class="jsst-status-note"><?php echo esc_html(sprintf(
                            /* translators: 1: tables converted so far, 2: tables in the whole run */
                            __('%1$s of %2$s done. It stops itself every twenty seconds so the page can come back — press Continue as many times as it takes. Nothing is lost by leaving it part way.', 'js-support-ticket'),
                            number_format_i18n(count($jsst_state['done'])),
                            number_format_i18n((int) $jsst_state['total'])
                        )); ?></div>
                        <div class="jsst-migration-actions">
                            <a class="button js-form-save" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=continuestorageconversion&action=jstask'), 'jsst-storage-continue')); ?>"><?php echo esc_html(__('Continue', 'js-support-ticket')); ?></a>
                            <a class="button js-form-cancel" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=cancelstorageconversion&action=jstask'), 'jsst-storage-cancel')); ?>"><?php echo esc_html(__('Stop here', 'js-support-ticket')); ?></a>
                        </div>
                    <?php } elseif (!empty($jsst_plan['pending'])) { ?>
                        <div class="jsst-status-note"><?php echo esc_html(__('Take a database backup first. A conversion rewrites tables in place, and while this puts back what it changed, a backup covers what it cannot.', 'js-support-ticket')); ?></div>
                        <div class="jsst-migration-actions">
                            <a class="button js-form-save" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=startstorageconversion&action=jstask'), 'jsst-storage-start')); ?>"><?php echo esc_html(sprintf(
                                /* translators: %s: number of tables to convert */
                                _n('Convert %s table', 'Convert %s tables', count($jsst_plan['pending']), 'js-support-ticket'),
                                number_format_i18n(count($jsst_plan['pending']))
                            )); ?></a>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-status-note"><?php echo esc_html(__('Every table that can be on InnoDB already is. There is nothing to do here.', 'js-support-ticket')); ?></div>
                    <?php }

                    /* Offered only when there is something to put back, and kept
                       away from the main action so it is never the button
                       somebody presses by reflex. */
                    if (!$jsst_running && !empty($jsst_state['done'])) { ?>
                        <div class="jsst-migration-actions">
                            <a class="button js-form-cancel" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=startstorageconversion&action=jstask&direction=revert'), 'jsst-storage-start')); ?>"><?php echo esc_html(sprintf(
                                /* translators: %s: number of tables that were converted */
                                _n('Put %s table back on its old engine', 'Put %s tables back on their old engines', count($jsst_state['done']), 'js-support-ticket'),
                                number_format_i18n(count($jsst_state['done']))
                            )); ?></a>
                            <span class="jsst-status-correlation"><?php echo esc_html(__('Each goes back to the engine it was on before this run touched it.', 'js-support-ticket')); ?></span>
                        </div>
                    <?php } ?>
                </div>
                <?php
            } ?>

        </div>
    </div>
</div>
