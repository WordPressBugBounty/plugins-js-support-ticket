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
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => __('System Status & Debug Report', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <?php /* The Plugin Support menu's "Debug Report" lands on this card by its
               id, so the menu, this title and the button all say the same thing. */ ?>
            <div class="jsst-status-card jsst-status-debugreport" id="jsst-debug-report">
                <div class="jsst-status-title"><?php echo esc_html(__('Debug Report', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('Download this file and attach it when you contact support. It holds everything on this page plus your settings. Passwords, API keys and licence keys are removed before it is written — anything whose name looks like a credential, and anything that looks like one regardless of its name.', 'js-support-ticket')); ?></div>
                <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=downloaddebugbundle&action=jstask'), 'jsst-debug-bundle')); ?>"><?php echo esc_html(__('Download Debug Report', 'js-support-ticket')); ?></a>
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
                    /* Months, and the two intervals apart. They are set apart,
                       and "keep the tickets, purge the files" is a real setting
                       this line could not say while it knew about one number in
                       the wrong unit under a name nothing writes. */
                    $jsst_ret = $jsst_status['retention'];
                    $jsst_ret_tickets = isset($jsst_ret['cleanup_months']) ? (int) $jsst_ret['cleanup_months'] : 0;
                    $jsst_ret_files = isset($jsst_ret['attachment_months']) ? (int) $jsst_ret['attachment_months'] : 0;
                    if (empty($jsst_ret['cleanup_on'])) {
                        echo esc_html(__('Retention cleanup is off, so nothing is deleted automatically.', 'js-support-ticket'));
                    } elseif ($jsst_ret_tickets > 0) {
                        echo esc_html(sprintf(
                            /* translators: %s: a number of months. */
                            _n('Retention cleanup is on: closed tickets are removed %s month after they close.',
                               'Retention cleanup is on: closed tickets are removed %s months after they close.',
                               $jsst_ret_tickets, 'js-support-ticket'),
                            number_format_i18n($jsst_ret_tickets)));
                        if ($jsst_ret_files > 0 && $jsst_ret_files < $jsst_ret_tickets) {
                            echo ' ' . esc_html(sprintf(
                                /* translators: %s: a number of months. */
                                _n('Their attachments go after %s month.', 'Their attachments go after %s months.',
                                   $jsst_ret_files, 'js-support-ticket'),
                                number_format_i18n($jsst_ret_files)));
                        }
                    } else {
                        echo esc_html(sprintf(
                            /* translators: %s: a number of months. */
                            _n('Retention cleanup is on for attachments only: tickets are kept, and their files are removed %s month after the ticket closes.',
                               'Retention cleanup is on for attachments only: tickets are kept, and their files are removed %s months after the ticket closes.',
                               $jsst_ret_files, 'js-support-ticket'),
                            number_format_i18n($jsst_ret_files)));
                    }
                    ?>
                </div>
            </div>

            <div class="jsst-status-card">
                <?php /* Only ever non-empty on a multisite where a legacy
                         add-on was network-activated and its tables were never
                         created on this blog. (Roadmap 4.5-ARCH-05) */ ?>
                <?php if (!empty($jsst_status['modules'])) { ?>
                    <div class="jsst-status-title"><?php echo esc_html(__('Add-on tables missing on this site', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-card jsst-status-warn">
                        <p><?php echo esc_html(__('These add-ons were activated across the whole network, but the tables they need were only ever created on one site — a known fault in the add-ons\' own activation, which creates them for whichever site happened to be current at the time. The features below will not work on this site until the tables exist. Deactivating and reactivating the add-on on this site alone creates them; moving the module to Pro avoids the fault entirely.', 'js-support-ticket')); ?></p>
                        <table class="jsst-status-table">
                            <?php foreach ($jsst_status['modules'] AS $jsst_gap) { ?>
                                <tr>
                                    <td><?php echo esc_html($jsst_gap['label']); ?></td>
                                    <td><code><?php echo esc_html($jsst_gap['table']); ?></code></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php } ?>

                <?php /* The two agent desks, measured rather than asserted.
                         (Roadmap 4.5-FE-01) */
                if (!empty($jsst_status['workspaces']) && !empty($jsst_status['workspaces']['total'])) {
                    $jsst_wp = $jsst_status['workspaces']; ?>
                    <div class="jsst-status-title"><?php echo esc_html(__('Agent workspaces', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note">
                        <?php echo esc_html(sprintf(
                            /* translators: 1: capabilities on the shared layer, 2: capabilities in total. */
                            __('%1$s of %2$s agent capabilities go through the shared application layer in both the backend and the frontend desk.', 'js-support-ticket'),
                            number_format_i18n($jsst_wp['done']), number_format_i18n($jsst_wp['total'])
                        )); ?>
                        <?php if (!empty($jsst_wp['gaps'])) {
                            echo ' ' . esc_html(sprintf(
                                /* translators: %s: number of capabilities. */
                                _n('%s is in one desk and not the other.', '%s are in one desk and not the other.', $jsst_wp['gaps'], 'js-support-ticket'),
                                number_format_i18n($jsst_wp['gaps'])
                            ));
                        } ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=workspaceparity')); ?>"><?php echo esc_html(__('Workspace Parity', 'js-support-ticket')); ?></a>
                    </div>
                <?php } ?>

                <?php
                /* The CSS debt, published for the same reason the parity figure
                   is: the design system is paid down alongside releases rather
                   than in one go, and a continuous task with no number attached
                   is one that quietly stops being worked on. (Roadmap 4.0-UX-06) */
                if (!empty($jsst_status['design'])) {
                    $jsst_design = $jsst_status['design']; ?>
                    <div class="jsst-status-title"><?php echo esc_html(__('Stylesheet debt', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note">
                        <?php echo esc_html(sprintf(
                            /* translators: 1: !important count, 2: float count, 3: design token uses */
                            __('%1$s declarations still win by force and %2$s rules still lay out with floats, against %3$s uses of a design token. Every one of the first two is somewhere a future screen has to fight the past; the third is the direction of travel.', 'js-support-ticket'),
                            number_format_i18n($jsst_design['important']),
                            number_format_i18n($jsst_design['float']),
                            number_format_i18n($jsst_design['tokens'])
                        )); ?>
                        <?php echo esc_html($jsst_design['reducedmotion']
                            ? __('Motion is switched off for anybody who has asked their system for that.', 'js-support-ticket')
                            : __('Reduced motion is not handled.', 'js-support-ticket')); ?>
                    </div>
                    <table class="jsst-status-table">
                        <tr>
                            <th><?php echo esc_html(__('Stylesheet', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('By force', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Floats', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Flex or grid', 'js-support-ticket')); ?></th>
                            <th><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></th>
                        </tr>
                        <?php foreach ($jsst_design['files'] AS $jsst_file => $jsst_row) { ?>
                            <tr>
                                <td><?php echo esc_html($jsst_file); ?></td>
                                <td><?php echo esc_html(number_format_i18n($jsst_row['important'])); ?></td>
                                <td><?php echo esc_html(number_format_i18n($jsst_row['float'])); ?></td>
                                <td><?php echo esc_html(number_format_i18n($jsst_row['flex'] + $jsst_row['grid'])); ?></td>
                                <td><?php echo esc_html(number_format_i18n($jsst_row['tokens'])); ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>

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
