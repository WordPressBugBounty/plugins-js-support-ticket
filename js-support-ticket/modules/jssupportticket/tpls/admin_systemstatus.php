<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * System status. (Roadmap 4.0-OPS-02)
 */
if (!class_exists('JSSTsystemstatus')) {
    echo '<div class="notice notice-error"><p>' . esc_html(__('The status report could not be loaded. Deactivate and reactivate JS Help Desk.', 'js-support-ticket')) . '</p></div>';
    return;
}
$jsst_status = isset(jssupportticket::$jsst_data['systemstatus']) ? jssupportticket::$jsst_data['systemstatus'] : JSSTsystemstatus::report();
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
                        <li><?php echo esc_html(__('System Status','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('System Status', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <div class="jsst-status-card">
                <div class="jsst-status-title"><?php echo esc_html(__('Send this with a bug report', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('A file with everything on this page plus your settings. Passwords, API keys and licence keys are removed before it is written — anything whose name looks like a credential, and anything that looks like one regardless of its name.', 'js-support-ticket')); ?></div>
                <a class="button js-form-save" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=downloaddebugbundle&action=jstask'), 'jsst-debug-bundle')); ?>"><?php echo esc_html(__('Download debug file', 'js-support-ticket')); ?></a>
                <span class="jsst-status-correlation"><?php echo esc_html(sprintf(
                    /* translators: %s: a short request identifier */
                    __('This page load is %s in the log.', 'js-support-ticket'),
                    $jsst_status['correlation']
                )); ?></span>
            </div>

            <div class="jsst-status-card">
                <div class="jsst-status-title"><?php echo esc_html(__('Environment', 'js-support-ticket')); ?></div>
                <table class="jsst-status-table">
                    <?php foreach ($jsst_status['environment'] AS $jsst_k => $jsst_v) { ?>
                        <tr><td><?php echo esc_html(str_replace('_', ' ', $jsst_k)); ?></td><td><?php echo esc_html($jsst_v !== '' ? $jsst_v : '—'); ?></td></tr>
                    <?php } ?>
                </table>
            </div>

            <?php
            $jsst_cronbad = !empty($jsst_status['cron']['wp_cron_disabled']);
            foreach ($jsst_status['cron']['hooks'] AS $jsst_hook) {
                if (empty($jsst_hook['next']) || !empty($jsst_hook['stalled'])) { $jsst_cronbad = true; }
            }
            ?>
            <div class="jsst-status-card <?php echo esc_attr($jsst_cronbad ? 'jsst-status-warn' : 'jsst-status-ok'); ?>">
                <div class="jsst-status-title"><?php echo esc_html(__('Scheduled work', 'js-support-ticket')); ?></div>
                <?php if (!empty($jsst_status['cron']['wp_cron_disabled'])) { ?>
                    <div class="jsst-status-note"><?php echo esc_html(__('WP-Cron is switched off in wp-config.php. That is fine if a real cron job calls wp-cron.php — and nothing works below if one does not.', 'js-support-ticket')); ?></div>
                <?php } ?>
                <table class="jsst-status-table">
                    <?php foreach ($jsst_status['cron']['hooks'] AS $jsst_hook) { ?>
                        <tr>
                            <td><?php echo esc_html($jsst_hook['label']); ?></td>
                            <td><?php
                                if (empty($jsst_hook['next'])) { ?>
                                    <span class="jsst-status-flag jsst-status-flag-bad"><?php echo esc_html(__('Not scheduled', 'js-support-ticket')); ?></span>
                                <?php } elseif (!empty($jsst_hook['stalled'])) { ?>
                                    <span class="jsst-status-flag jsst-status-flag-bad"><?php echo esc_html(__('Overdue — nothing is running it', 'js-support-ticket')); ?></span>
                                <?php } else { ?>
                                    <span class="jsst-status-flag jsst-status-flag-ok"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'] . ' H:i', (int) $jsst_hook['next'])); ?></span>
                                <?php } ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <?php
            $jsst_permbad = false;
            foreach ($jsst_status['permissions'] AS $jsst_perm) {
                if (empty($jsst_perm['writable'])) { $jsst_permbad = true; }
            }
            ?>
            <div class="jsst-status-card <?php echo esc_attr($jsst_permbad ? 'jsst-status-warn' : 'jsst-status-ok'); ?>">
                <div class="jsst-status-title"><?php echo esc_html(__('Folder permissions', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('An attachment that fails to upload with no explanation is almost always one of these.', 'js-support-ticket')); ?></div>
                <table class="jsst-status-table">
                    <?php foreach ($jsst_status['permissions'] AS $jsst_perm) { ?>
                        <tr>
                            <td><?php echo esc_html($jsst_perm['label']); ?><div class="jsst-status-path"><?php echo esc_html($jsst_perm['path']); ?></div></td>
                            <td><?php
                                if (!empty($jsst_perm['writable'])) { ?>
                                    <span class="jsst-status-flag jsst-status-flag-ok"><?php echo esc_html(__('Writable', 'js-support-ticket')); ?></span>
                                <?php } elseif (!empty($jsst_perm['exists'])) { ?>
                                    <span class="jsst-status-flag jsst-status-flag-bad"><?php echo esc_html(__('Not writable', 'js-support-ticket')); ?></span>
                                <?php } else { ?>
                                    <span class="jsst-status-flag jsst-status-flag-bad"><?php echo esc_html(__('Missing', 'js-support-ticket')); ?></span>
                                <?php } ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="jsst-status-card <?php echo esc_attr(empty($jsst_status['retention']['uninstall_safe']) ? 'jsst-status-warn' : 'jsst-status-ok'); ?>">
                <div class="jsst-status-title"><?php echo esc_html(__('If this plugin is deleted', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note">
                    <?php
                    echo esc_html(!empty($jsst_status['retention']['uninstall_safe'])
                            ? __('Your tickets, attachments and settings are kept. Deactivating never removes anything, and deleting the plugin leaves the data in place.', 'js-support-ticket')
                            : __('Deleting the plugin will drop every help desk table and delete the attachment folder. That is what the retention setting currently says, and it cannot be undone.', 'js-support-ticket'));
                    ?>
                </div>
                <div class="jsst-status-note">
                    <?php
                    echo esc_html(!empty($jsst_status['retention']['cleanup_on'])
                            ? sprintf(
                                /* translators: %d: days */
                                __('Retention cleanup is on: closed tickets are removed after %d days.', 'js-support-ticket'),
                                (int) $jsst_status['retention']['cleanup_days'])
                            : __('Retention cleanup is off, so nothing is deleted automatically.', 'js-support-ticket'));
                    ?>
                </div>
            </div>

            <div class="jsst-status-card">
                <div class="jsst-status-title"><?php echo esc_html(__('Recent errors', 'js-support-ticket')); ?></div>
                <?php if (empty($jsst_status['errors'])) { ?>
                    <div class="jsst-status-note"><?php echo esc_html(__('Nothing has been logged.', 'js-support-ticket')); ?></div>
                <?php } else { ?>
                    <table class="jsst-status-table">
                        <?php foreach ($jsst_status['errors'] AS $jsst_error) { ?>
                            <tr>
                                <td class="jsst-status-when"><?php echo esc_html($jsst_error->created); ?></td>
                                <td><div class="jsst-status-error"><?php echo esc_html(wp_trim_words(wp_strip_all_tags((string) $jsst_error->error), 40)); ?></div></td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            </div>

        </div>
    </div>
</div>
