<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * What an import would do, before it does it. (Roadmap 4.0-DATA-01)
 *
 * The old Import Data screen could only tell you what it had found once it was
 * already running. This one counts the source first and puts the answer to the
 * question people actually have — how much is there, where does each kind of
 * thing land, and what gets dropped — in front of the button that starts it.
 */
if (!class_exists('JSSTmigrationpreview')) {
    echo '<div class="notice notice-error"><p>' . esc_html(__('The import preview could not be loaded. Deactivate and reactivate JS Help Desk.', 'js-support-ticket')) . '</p></div>';
    return;
}
JSSTmessage::getMessage();

$jsst_sources    = jssupportticket::$jsst_data['sources'];
$jsst_source     = jssupportticket::$jsst_data['source'];
$jsst_report     = jssupportticket::$jsst_data['report'];
$jsst_inprogress = jssupportticket::$jsst_data['inprogress'];
$jsst_latest     = jssupportticket::$jsst_data['latest'];
$jsst_listurl    = admin_url('admin.php?page=thirdpartyimport&jstlay=migrationpreview');
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
                        <li><?php echo esc_html(__('Import from Another Help Desk','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Import from Another Help Desk', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <?php
            /* A run still going blocks a second one, and says so before the
               source list rather than after somebody has chosen from it. */
            if ($jsst_inprogress) { ?>
                <div class="jsst-status-card jsst-status-warn">
                    <div class="jsst-status-title"><?php echo esc_html(__('An import is already running', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(sprintf(
                        /* translators: 1: the source help desk, 2: when it started */
                        __('An import from %1$s started at %2$s and has not finished. Two at once cannot be told apart afterwards, so this one has to end before another can start.', 'js-support-ticket'),
                        $jsst_inprogress->source,
                        $jsst_inprogress->started
                    )); ?></div>
                    <div class="jsst-migration-actions">
                        <a class="button js-form-save" href="<?php echo esc_url(admin_url('admin.php?page=thirdpartyimport&jstlay=migrationresult&token=' . rawurlencode($jsst_inprogress->token))); ?>"><?php echo esc_html(__('See where it got to', 'js-support-ticket')); ?></a>
                    </div>
                </div>
            <?php }

            /* ---------------------------------------------------------- *
             * Which help desk
             * ---------------------------------------------------------- */
            ?>
            <div class="jsst-status-card">
                <div class="jsst-status-title"><?php echo esc_html(__('Which help desk', 'js-support-ticket')); ?></div>
                <div class="jsst-status-note"><?php echo esc_html(__('Data is read whether or not the old plugin is still switched on. It is never modified or deleted — the import only reads.', 'js-support-ticket')); ?></div>
                <table class="jsst-status-table">
                    <tr>
                        <td><strong><?php echo esc_html(__('Help desk', 'js-support-ticket')); ?></strong></td>
                        <td><strong><?php echo esc_html(__('Found here', 'js-support-ticket')); ?></strong></td>
                        <td><strong><?php echo esc_html(__('Version', 'js-support-ticket')); ?></strong></td>
                        <td></td>
                    </tr>
                    <?php foreach ($jsst_sources AS $jsst_key => $jsst_meta) { ?>
                        <tr>
                            <td><?php echo esc_html($jsst_meta['label']); ?></td>
                            <td><?php if (!empty($jsst_meta['present'])) { ?>
                                    <span class="jsst-status-flag jsst-status-flag-ok"><?php echo esc_html(empty($jsst_meta['active']) ? __('data present, plugin off', 'js-support-ticket') : __('active', 'js-support-ticket')); ?></span>
                                <?php } else { ?>
                                    <span class="jsst-status-flag"><?php echo esc_html(__('nothing found', 'js-support-ticket')); ?></span>
                                <?php } ?></td>
                            <td><?php echo esc_html($jsst_meta['version'] !== '' ? $jsst_meta['version'] : '—'); ?></td>
                            <td><?php if (!empty($jsst_meta['present'])) { ?>
                                    <a class="button" href="<?php echo esc_url($jsst_listurl . '&source=' . rawurlencode($jsst_key)); ?>"><?php echo esc_html($jsst_source === $jsst_key ? __('Counted below', 'js-support-ticket') : __('Count it', 'js-support-ticket')); ?></a>
                                <?php } ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <?php
            if (is_wp_error($jsst_report)) { ?>
                <div class="jsst-status-card jsst-status-warn">
                    <div class="jsst-status-note"><?php echo esc_html($jsst_report->get_error_message()); ?></div>
                </div>
                <?php
            } elseif (!empty($jsst_report)) {
                /* ---------------------------------------------------------- *
                 * What is there, and where it lands
                 * ---------------------------------------------------------- */
                ?>
                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html(sprintf(
                        /* translators: %s: the source help desk */
                        __('What is in %s', 'js-support-ticket'),
                        $jsst_report['label']
                    )); ?></div>
                    <table class="jsst-status-table">
                        <tr>
                            <td><strong><?php echo esc_html(__('There', 'js-support-ticket')); ?></strong></td>
                            <td><strong><?php echo esc_html(__('How many', 'js-support-ticket')); ?></strong></td>
                            <td><strong><?php echo esc_html(__('Becomes', 'js-support-ticket')); ?></strong></td>
                        </tr>
                        <?php foreach ($jsst_report['entities'] AS $jsst_entity) { ?>
                            <tr>
                                <td><?php echo esc_html($jsst_entity['label']); ?></td>
                                <td><?php if (empty($jsst_entity['countable'])) { ?>
                                        <span class="jsst-status-flag"><?php echo esc_html(__('not present', 'js-support-ticket')); ?></span>
                                    <?php } else {
                                        echo esc_html(number_format_i18n((int) $jsst_entity['count']));
                                    } ?></td>
                                <td><?php echo esc_html($jsst_entity['destination']); ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>

                <?php if (!empty($jsst_report['unsupported'])) { ?>
                    <div class="jsst-status-card jsst-status-warn">
                        <div class="jsst-status-title"><?php echo esc_html(__('What will not come across', 'js-support-ticket')); ?></div>
                        <div class="jsst-status-note"><?php echo esc_html(__('There is data of these kinds in the source and nowhere here to put it. Read this before you delete the old help desk, not after.', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <?php foreach ($jsst_report['unsupported'] AS $jsst_gap) { ?>
                                <tr>
                                    <td><?php echo esc_html($jsst_gap['label']); ?></td>
                                    <td><?php echo esc_html(number_format_i18n((int) $jsst_gap['count'])); ?></td>
                                    <td><?php echo esc_html($jsst_gap['fallback']); ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php }

                if (!empty($jsst_report['warnings'])) { ?>
                    <div class="jsst-status-card">
                        <div class="jsst-status-title"><?php echo esc_html(__('Before you start', 'js-support-ticket')); ?></div>
                        <table class="jsst-status-table">
                            <?php foreach ($jsst_report['warnings'] AS $jsst_warning) { ?>
                                <tr><td colspan="2"><?php echo esc_html($jsst_warning); ?></td></tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php } ?>

                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html(__('Start the import', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(sprintf(
                        /* translators: %s: total records counted in the source */
                        _n('%s record will be read. Everything it creates is recorded, so the whole import can be taken back out in one action.', '%s records will be read. Everything it creates is recorded, so the whole import can be taken back out in one action.', (int) $jsst_report['total'], 'js-support-ticket'),
                        number_format_i18n((int) $jsst_report['total'])
                    )); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(__('A large import can take several minutes. Leave this page open until it finishes.', 'js-support-ticket')); ?></div>
                    <div class="jsst-migration-actions">
                        <?php if ($jsst_inprogress) { ?>
                            <span class="jsst-status-correlation"><?php echo esc_html(__('Another import has to finish first.', 'js-support-ticket')); ?></span>
                        <?php } else { ?>
                            <a class="button js-form-save" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=thirdpartyimport&task=startmigration&action=jstask&source=' . rawurlencode($jsst_source)), 'jsst-migration-start')); ?>"><?php echo esc_html(sprintf(
                                /* translators: %s: the source help desk */
                                __('Import from %s', 'js-support-ticket'),
                                $jsst_report['label']
                            )); ?></a>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }

            /* The last run, whatever became of it, so a finished import is never
               more than one click away from the screen that started it. */
            if ($jsst_latest) { ?>
                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html(__('The last import', 'js-support-ticket')); ?></div>
                    <div class="jsst-status-note"><?php echo esc_html(sprintf(
                        /* translators: 1: the source, 2: its status, 3: when it started */
                        __('%1$s — %2$s, started %3$s.', 'js-support-ticket'),
                        $jsst_latest->source,
                        $jsst_latest->status,
                        $jsst_latest->started
                    )); ?></div>
                    <div class="jsst-migration-actions">
                        <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=thirdpartyimport&jstlay=migrationresult&token=' . rawurlencode($jsst_latest->token))); ?>"><?php echo esc_html(__('See the report', 'js-support-ticket')); ?></a>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
