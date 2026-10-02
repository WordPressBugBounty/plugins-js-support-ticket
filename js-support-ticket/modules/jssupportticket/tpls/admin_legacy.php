<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Your add-ons: what you have, what it is worth, and how to move it.
 * (Roadmap 4.5-PRO-02)
 *
 * The page an existing customer opens to find out whether the consolidation is
 * going to cost them anything. The answer has to be visible without reading:
 * what is still running, what has already moved, what a move would do, and the
 * date after which the old way stops being supported.
 */
if (!class_exists('JSSTlegacy') || !class_exists('JSSTpro')) {
    echo esc_html(__('This screen is not available.', 'js-support-ticket'));
    return;
}
$jsst_rows      = isset(jssupportticket::$jsst_data['legacyrows']) ? jssupportticket::$jsst_data['legacyrows'] : array();
$jsst_preview   = isset(jssupportticket::$jsst_data['legacypreview']) ? jssupportticket::$jsst_data['legacypreview'] : false;
$jsst_asked     = isset(jssupportticket::$jsst_data['legacyasked']) ? jssupportticket::$jsst_data['legacyasked'] : '';
$jsst_ends      = isset(jssupportticket::$jsst_data['legacyends']) ? jssupportticket::$jsst_data['legacyends'] : JSSTlegacy::SUPPORT_ENDS;
$jsst_daysleft  = isset(jssupportticket::$jsst_data['legacydays']) ? (int) jssupportticket::$jsst_data['legacydays'] : 0;
$jsst_dateformat = isset(jssupportticket::$_config['date_format']) ? jssupportticket::$_config['date_format'] : 'Y-m-d';
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Your Add-ons', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <div class="jsst-legacy-lede">
                <?php echo esc_html(__('Everything you have bought stays yours. An add-on you already own keeps working as it is, and its licence key entitles you to the same module under Pro whether or not you ever buy a Pro licence. Moving one across is optional, previewed first, and can be undone.', 'js-support-ticket')); ?>
            </div>

            <div class="jsst-legacy-support">
                <?php if ($jsst_daysleft > 0) { ?>
                    <?php echo esc_html(sprintf(
                        /* translators: 1: date support ends, 2: number of days remaining */
                        __('The separate add-ons are supported until %1$s — %2$s days from today. That date may move later; it will not move earlier.', 'js-support-ticket'),
                        date_i18n($jsst_dateformat, strtotime($jsst_ends)),
                        number_format_i18n($jsst_daysleft)
                    )); ?>
                <?php } else { ?>
                    <?php echo esc_html(sprintf(
                        /* translators: %s: date support ended */
                        __('Support for the separate add-ons ended on %s. They have not been switched off, and nothing here has been removed — but they are no longer updated, and moving to Pro is now the supported path.', 'js-support-ticket'),
                        date_i18n($jsst_dateformat, strtotime($jsst_ends))
                    )); ?>
                <?php } ?>
            </div>

            <?php if (empty($jsst_rows)) { ?>
                <div class="jsst-legacy-none"><?php echo esc_html(__('There are no JS Help Desk add-ons installed on this site, so there is nothing to migrate.', 'js-support-ticket')); ?></div>
            <?php } else { ?>

                <?php /* ---------------------------------------------------
                       The preview, when one has been asked for. Shown above
                       the list rather than inside the row, because it is what
                       the person is reading and it is longer than a row.
                       --------------------------------------------------- */ ?>
                <?php if ($jsst_preview !== false && !is_wp_error($jsst_preview)) { ?>
                    <div class="jsst-legacy-preview">
                        <h2 class="jsst-legacy-preview-head"><?php echo esc_html(sprintf(
                            /* translators: %s: module name */
                            __('Moving %s to Pro', 'js-support-ticket'),
                            $jsst_preview['label']
                        )); ?></h2>

                        <ol class="jsst-legacy-steps">
                            <li><?php echo esc_html(sprintf(
                                /* translators: %s: number of settings */
                                _n('Your %s setting is copied out first, before anything changes.',
                                   'All %s of your settings are copied out first, before anything changes.',
                                   count($jsst_preview['settings']), 'js-support-ticket'),
                                number_format_i18n(count($jsst_preview['settings']))
                            )); ?></li>
                            <li><?php echo esc_html(__('The Pro module is switched on, so the feature is being provided before the add-on stops providing it.', 'js-support-ticket')); ?></li>
                            <li><?php echo esc_html(__('The add-on is deactivated. Its files stay on your server and nothing is deleted — no tables are dropped and no data is removed.', 'js-support-ticket')); ?></li>
                            <li><?php echo esc_html(__('Your settings are written back. Deactivating one of these add-ons resets its settings to zero as a side effect; this is the step that undoes that, and it is why the copy in step one is taken.', 'js-support-ticket')); ?></li>
                            <?php if (!empty($jsst_preview['permissions'])) { ?>
                                <li><?php echo esc_html(__('Every person on this site is put to the permission service before and after, and the two answers are compared. If anybody gained or lost a permission, it is listed here rather than discovered later.', 'js-support-ticket')); ?></li>
                            <?php } ?>
                        </ol>

                        <?php if (!empty($jsst_preview['shared'])) { ?>
                            <div class="jsst-legacy-warn"><?php echo esc_html(sprintf(
                                /* translators: %s: the settings tag shared between add-ons */
                                __('These settings are stored under the shared name “%s”, which more than one add-on writes to. They are copied and restored as a whole, because nothing in the data says which setting belongs to which add-on. If you have both of the add-ons that use it, move them one after the other rather than leaving one half-moved.', 'js-support-ticket'),
                                $jsst_preview['configtag']
                            )); ?></div>
                        <?php } ?>

                        <?php if (!empty($jsst_preview['settings'])) { ?>
                            <table class="jsst-legacy-table">
                                <thead><tr>
                                    <th><?php echo esc_html(__('Setting', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Value kept', 'js-support-ticket')); ?></th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($jsst_preview['settings'] AS $jsst_name => $jsst_value) { ?>
                                    <tr>
                                        <td><code><?php echo esc_html($jsst_name); ?></code></td>
                                        <td><?php
                                            $jsst_shown = (string) $jsst_value;
                                            echo esc_html($jsst_shown === '' ? __('(empty)', 'js-support-ticket') : $jsst_shown);
                                        ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>

                        <div class="jsst-legacy-actions">
                            <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=migratelegacy&action=jstask&slug=' . rawurlencode($jsst_preview['slug'])), 'jsst-legacy-migrate-' . $jsst_preview['slug'])); ?>"><?php echo esc_html(__('Move to Pro', 'js-support-ticket')); ?></a>
                            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=legacy')); ?>"><?php echo esc_html(__('Not now', 'js-support-ticket')); ?></a>
                        </div>
                    </div>
                <?php } elseif (is_wp_error($jsst_preview)) { ?>
                    <div class="jsst-legacy-warn"><?php echo esc_html($jsst_preview->get_error_message()); ?></div>
                <?php } ?>

                <?php /* --------------------------------------------------- */ ?>
                <div class="jsst-legacy-list">
                    <?php foreach ($jsst_rows AS $jsst_row) { ?>
                        <div class="jsst-legacy-row jsst-legacy-<?php echo esc_attr($jsst_row['state']); ?>">
                            <div class="jsst-legacy-row-main">
                                <span class="jsst-legacy-name"><?php echo esc_html($jsst_row['label']); ?></span>
                                <span class="jsst-legacy-state"><?php
                                    if ($jsst_row['state'] === 'migrated') {
                                        echo esc_html(__('Moved to Pro', 'js-support-ticket'));
                                    } elseif ($jsst_row['state'] === 'ready') {
                                        echo esc_html(__('Add-on, ready to move', 'js-support-ticket'));
                                    } elseif ($jsst_row['state'] === 'waiting') {
                                        echo esc_html(__('Add-on', 'js-support-ticket'));
                                    } else {
                                        echo esc_html(__('Installed, not active', 'js-support-ticket'));
                                    }
                                ?></span>
                            </div>
                            <div class="jsst-legacy-row-meta">
                                <?php if ($jsst_row['version'] !== '') { ?>
                                    <span><?php echo esc_html(sprintf(
                                        /* translators: %s: add-on version number */
                                        __('Version %s', 'js-support-ticket'), $jsst_row['version'])); ?></span>
                                <?php } ?>
                                <?php if ($jsst_row['settings'] > 0) { ?>
                                    <span><?php echo esc_html(sprintf(
                                        /* translators: %s: number of settings */
                                        _n('%s setting', '%s settings', $jsst_row['settings'], 'js-support-ticket'),
                                        number_format_i18n($jsst_row['settings']))); ?></span>
                                <?php } ?>
                                <?php if (!empty($jsst_row['entitled'])) { ?>
                                    <span class="jsst-legacy-owned"><?php echo esc_html(__('Your key covers this under Pro', 'js-support-ticket')); ?></span>
                                <?php } ?>
                                <?php if (!empty($jsst_row['shared'])) { ?>
                                    <span class="jsst-legacy-sharedtag"><?php echo esc_html(sprintf(
                                        /* translators: %s: the shared settings tag */
                                        __('Shares settings under “%s”', 'js-support-ticket'), $jsst_row['configtag'])); ?></span>
                                <?php } ?>
                                <?php if ($jsst_row['migrated'] !== '') { ?>
                                    <span><?php echo esc_html(sprintf(
                                        /* translators: %s: date the module was moved */
                                        __('Moved %s', 'js-support-ticket'),
                                        date_i18n($jsst_dateformat, (int) $jsst_row['migrated']))); ?></span>
                                <?php } ?>
                            </div>

                            <?php $jsst_drift = ($jsst_row['state'] === 'migrated') ? JSSTlegacy::driftFor($jsst_row['slug']) : array(); ?>
                            <?php if ($jsst_row['state'] === 'migrated' && JSSTlegacy::changesPermissions($jsst_row['slug'])) { ?>
                                <?php if (empty($jsst_drift)) { ?>
                                    <div class="jsst-legacy-checked"><?php echo esc_html(__('Checked: every person on this site can do exactly what they could do before the move, and no more.', 'js-support-ticket')); ?></div>
                                <?php } else { ?>
                                    <div class="jsst-legacy-warn">
                                        <?php echo esc_html(__('The move changed what some people are allowed to do. Each change is listed below — anything gained should be looked at first.', 'js-support-ticket')); ?>
                                        <table class="jsst-legacy-table">
                                            <thead><tr>
                                                <th><?php echo esc_html(__('Person', 'js-support-ticket')); ?></th>
                                                <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                                                <th><?php echo esc_html(__('Change', 'js-support-ticket')); ?></th>
                                            </tr></thead>
                                            <tbody>
                                            <?php foreach ($jsst_drift AS $jsst_change) { ?>
                                                <tr class="jsst-legacy-drift-<?php echo esc_attr($jsst_change['direction']); ?>">
                                                    <td><?php echo esc_html($jsst_change['person']); ?></td>
                                                    <td><?php echo esc_html($jsst_change['label']); ?></td>
                                                    <td><?php echo esc_html($jsst_change['direction'] === 'gained'
                                                        ? __('Can now, could not before', 'js-support-ticket')
                                                        : __('Could before, cannot now', 'js-support-ticket')); ?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                            <?php } ?>

                            <div class="jsst-legacy-actions">
                                <?php if ($jsst_row['state'] === 'ready') { ?>
                                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=legacy&slug=' . rawurlencode($jsst_row['slug']))); ?>"><?php echo esc_html(__('See what would happen', 'js-support-ticket')); ?></a>
                                <?php } elseif ($jsst_row['state'] === 'waiting') { ?>
                                    <span class="jsst-legacy-hint"><?php echo esc_html(__('Keeps working as it is. Pro cannot take this one over yet, so it is not offered.', 'js-support-ticket')); ?></span>
                                <?php } elseif ($jsst_row['state'] === 'migrated' && $jsst_row['token'] !== '') { ?>
                                    <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=rollbacklegacy&action=jstask&token=' . rawurlencode($jsst_row['token'])), 'jsst-legacy-rollback-' . $jsst_row['token'])); ?>"><?php echo esc_html(__('Put it back', 'js-support-ticket')); ?></a>
                                    <span class="jsst-legacy-hint"><?php echo esc_html(__('Reactivates the add-on and restores the settings exactly as they were.', 'js-support-ticket')); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
