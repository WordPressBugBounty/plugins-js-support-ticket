<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * What an import did, and the one button that undoes it. (Roadmap 4.0-DATA-01)
 *
 * The checks are shown in full, including the ones that passed. A list that only
 * shows problems cannot be told apart from a list that failed to run, and the
 * whole point of this screen is to let somebody decide whether it is safe to
 * delete their old help desk.
 */
if (!class_exists('JSSTmigration')) {
    echo '<div class="notice notice-error"><p>' . esc_html(__('The import report could not be loaded. Deactivate and reactivate JS Help Desk.', 'js-support-ticket')) . '</p></div>';
    return;
}
JSSTmessage::getMessage();

$jsst_run      = jssupportticket::$jsst_data['run'];
$jsst_counts   = jssupportticket::$jsst_data['counts'];
$jsst_journal  = jssupportticket::$jsst_data['journal'];
$jsst_findings = jssupportticket::$jsst_data['findings'];
$jsst_listurl  = admin_url('admin.php?page=thirdpartyimport&jstlay=migrationpreview');
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
                        <li><a href="<?php echo esc_url($jsst_listurl); ?>" title="<?php echo esc_attr(__('Import from Another Help Desk','js-support-ticket')); ?>"><?php echo esc_html(__('Import from Another Help Desk','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Report','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Import Report', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <?php
            if (empty($jsst_run)) { ?>
                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html(__('Nothing to report', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(__('No import has been run yet.', 'js-support-ticket')); ?></div>
                    <div class="jsst-migration-actions">
                        <a class="button js-form-save" href="<?php echo esc_url($jsst_listurl); ?>"><?php echo esc_html(__('Start one', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <?php
            } else {
                $jsst_rolledback = ($jsst_run->status === 'rolledback');
                $jsst_failed = ($jsst_run->status === 'failed');
                $jsst_class = $jsst_failed ? 'jsst-status-warn' : ($jsst_rolledback ? '' : 'jsst-status-ok');
                ?>
                <div class="jsst-status-card <?php echo esc_attr($jsst_class); ?>">
                    <div class="jsst-status-title"><?php echo esc_html(__('The run', 'js-support-ticket')); ?></div>
                    <table class="jsst-status-table">
                        <tr><td><?php echo esc_html(__('From', 'js-support-ticket')); ?></td><td><?php echo esc_html($jsst_run->source); ?><?php if ($jsst_run->sourceversion !== '') { ?> <span class="jsst-status-when"><?php echo esc_html($jsst_run->sourceversion); ?></span><?php } ?></td></tr>
                        <tr><td><?php echo esc_html(__('Status', 'js-support-ticket')); ?></td><td><span class="jsst-status-flag <?php echo esc_attr(($jsst_failed || $jsst_rolledback) ? 'jsst-status-flag-bad' : 'jsst-status-flag-ok'); ?>"><?php echo esc_html($jsst_run->status); ?></span></td></tr>
                        <tr><td><?php echo esc_html(__('Started', 'js-support-ticket')); ?></td><td class="jsst-status-when"><?php echo esc_html($jsst_run->started); ?></td></tr>
                        <?php if (!empty($jsst_run->finished)) { ?>
                            <tr><td><?php echo esc_html(__('Finished', 'js-support-ticket')); ?></td><td class="jsst-status-when"><?php echo esc_html($jsst_run->finished); ?></td></tr>
                        <?php } ?>
                        <?php if (!empty($jsst_run->notes)) { ?>
                            <tr><td><?php echo esc_html(__('Notes', 'js-support-ticket')); ?></td><td><?php echo esc_html($jsst_run->notes); ?></td></tr>
                        <?php } ?>
                    </table>
                </div>

                <?php if (!empty($jsst_counts)) { ?>
                    <div class="jsst-status-card">
                        <div class="jsst-status-title"><?php echo esc_html(__('What the importer reported', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <tr>
                                <td><strong><?php echo esc_html(__('Kind', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Imported', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Skipped', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Failed', 'js-support-ticket')); ?></strong></td>
                            </tr>
                            <?php foreach ($jsst_counts AS $jsst_kind => $jsst_tally) {
                                if (!is_array($jsst_tally)) { continue; } ?>
                                <tr>
                                    <td><?php echo esc_html($jsst_kind); ?></td>
                                    <td><?php echo esc_html(number_format_i18n(isset($jsst_tally['imported']) ? (int) $jsst_tally['imported'] : 0)); ?></td>
                                    <td><?php echo esc_html(number_format_i18n(isset($jsst_tally['skipped']) ? (int) $jsst_tally['skipped'] : 0)); ?></td>
                                    <td><?php echo esc_html(number_format_i18n(isset($jsst_tally['failed']) ? (int) $jsst_tally['failed'] : 0)); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php }

                if (!empty($jsst_findings)) { ?>
                    <div class="jsst-status-card">
                        <div class="jsst-status-title"><?php echo esc_html(__('The checks', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <?php foreach ($jsst_findings AS $jsst_finding) {
                                $jsst_level = isset($jsst_finding['level']) ? $jsst_finding['level'] : 'warn';
                                $jsst_flag = ($jsst_level === 'ok') ? 'jsst-status-flag-ok' : (($jsst_level === 'bad') ? 'jsst-status-flag-bad' : ''); ?>
                                <tr>
                                    <td>
                                        <span class="jsst-status-flag <?php echo esc_attr($jsst_flag); ?>"><?php echo esc_html($jsst_level); ?></span>
                                        <?php echo esc_html(isset($jsst_finding['label']) ? $jsst_finding['label'] : ''); ?>
                                    </td>
                                    <td><?php echo esc_html(isset($jsst_finding['detail']) ? $jsst_finding['detail'] : ''); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php }

                if (!empty($jsst_journal)) { ?>
                    <div class="jsst-status-card">
                        <div class="jsst-status-title"><?php echo esc_html(__('What was written', 'js-support-ticket')); ?></div>
                        <div class="jsst-status-note"><?php echo esc_html(__('Recorded row by row as the import ran. This is the list a rollback works from, so it is also the exact list of what a rollback would remove.', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <tr>
                                <td><strong><?php echo esc_html(__('Table', 'js-support-ticket')); ?></strong></td>
                                <td><strong><?php echo esc_html(__('Rows', 'js-support-ticket')); ?></strong></td>
                            </tr>
                            <?php foreach ($jsst_journal AS $jsst_tablename => $jsst_total) { ?>
                                <tr>
                                    <td><span class="jsst-status-path"><?php echo esc_html($jsst_tablename); ?></span></td>
                                    <td><?php echo esc_html(number_format_i18n((int) $jsst_total)); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php } ?>

                <div class="jsst-status-card">
                    <div class="jsst-migration-actions">
                        <?php if (!$jsst_rolledback && !empty($jsst_journal)) { ?>
                            <a class="button js-form-cancel" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=thirdpartyimport&task=rollbackmigration&action=jstask&token=' . rawurlencode($jsst_run->token)), 'jsst-migration-rollback')); ?>"><?php echo esc_html(__('Take this import back out', 'js-support-ticket')); ?></a>
                            <span class="jsst-status-correlation"><?php echo esc_html(__('Removes exactly the rows listed above, newest first, and nothing else.', 'js-support-ticket')); ?></span>
                        <?php } ?>
                        <a class="button js-form-save" href="<?php echo esc_url($jsst_listurl); ?>"><?php echo esc_html(__('Back to imports', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <?php
            } ?>

        </div>
    </div>
</div>
