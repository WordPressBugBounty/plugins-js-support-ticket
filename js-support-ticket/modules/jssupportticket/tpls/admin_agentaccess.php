<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Agent Access. (Roadmap 4.0-SEC-04)
 *
 * One row per person who has any access at all, showing the derived answer
 * rather than the four settings it came from.
 */
$jsst_rows = isset(jssupportticket::$jsst_data['agentaccess']) ? jssupportticket::$jsst_data['agentaccess'] : array();
$jsst_warncount = 0;
foreach ($jsst_rows AS $jsst_row) {
    if (!empty($jsst_row['warnings'])) {
        $jsst_warncount++;
    }
}
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
                        <li><?php echo esc_html(__('Agent Access','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Agent Access', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <div class="jsst-access-lede">
                <?php echo esc_html(__('Everyone who can reach the help desk, and what they can actually do once they are in. This is worked out from the WordPress role, the help-desk role, the department scope and any permissions granted directly — the same way the software works it out when it decides whether to allow something.', 'js-support-ticket')); ?>
            </div>

            <?php if ($jsst_warncount > 0) { ?>
                <div class="jsst-access-alert">
                    <?php echo esc_html(sprintf(
                        /* translators: %d: how many people need attention */
                        _n('%d person needs attention.', '%d people need attention.', $jsst_warncount, 'js-support-ticket'),
                        $jsst_warncount
                    )); ?>
                    <?php echo esc_html(__('Anyone who can reach the help desk without being on the Agents list has access that nothing here granted them — usually a capability left on a WordPress role.', 'js-support-ticket')); ?>
                </div>
            <?php } ?>

            <?php if (empty($jsst_rows)) { ?>
                <div class="jsst-access-lede"><?php echo esc_html(__('Nobody but administrators can reach the help desk on this site.', 'js-support-ticket')); ?></div>
            <?php } ?>

            <?php foreach ($jsst_rows AS $jsst_row) { ?>
                <div class="jsst-access-card <?php echo esc_attr(!empty($jsst_row['warnings']) ? 'jsst-access-warn' : ''); ?>">
                    <div class="jsst-access-head">
                        <span class="jsst-access-name"><?php echo esc_html($jsst_row['name']); ?></span>
                        <span class="jsst-access-email"><?php echo esc_html($jsst_row['email']); ?></span>
                        <?php if (empty($jsst_row['active'])) { ?>
                            <span class="jsst-access-tag jsst-access-tag-off"><?php echo esc_html(__('Disabled', 'js-support-ticket')); ?></span>
                        <?php } ?>
                        <?php if (!empty($jsst_row['is_admin'])) { ?>
                            <span class="jsst-access-tag jsst-access-tag-admin"><?php echo esc_html(__('Administrator', 'js-support-ticket')); ?></span>
                        <?php } elseif (empty($jsst_row['is_agent'])) { ?>
                            <span class="jsst-access-tag jsst-access-tag-off"><?php echo esc_html(__('Not on the Agents list', 'js-support-ticket')); ?></span>
                        <?php } ?>
                    </div>

                    <table class="jsst-access-table">
                        <tr>
                            <td><?php echo esc_html(__('Workspace', 'js-support-ticket')); ?></td>
                            <td><?php echo esc_html($jsst_row['workspace']); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html(__('WordPress role', 'js-support-ticket')); ?></td>
                            <td><?php echo esc_html(!empty($jsst_row['wp_roles']) ? implode(', ', $jsst_row['wp_roles']) : __('No WordPress user', 'js-support-ticket')); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html(__('Help-desk role', 'js-support-ticket')); ?></td>
                            <td><?php echo esc_html($jsst_row['hd_role']); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html(__('Departments', 'js-support-ticket')); ?></td>
                            <td>
                                <?php
                                if (!empty($jsst_row['departments']['names'])) {
                                    echo esc_html(implode(', ', $jsst_row['departments']['names']));
                                    echo ' ';
                                    ?><span class="jsst-access-source"><?php
                                        echo esc_html($jsst_row['departments']['source'] === 'agent'
                                                ? __('(set on this agent)', 'js-support-ticket')
                                                : __('(from their role)', 'js-support-ticket'));
                                    ?></span><?php
                                } else { ?>
                                    <?php echo esc_html(__('Every department', 'js-support-ticket')); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html(__('Can do', 'js-support-ticket')); ?></td>
                            <td>
                                <?php
                                if (!empty($jsst_row['is_admin'])) { ?>
                                    <?php echo esc_html(__('Everything — administrators are never restricted by help-desk permissions.', 'js-support-ticket')); ?>
                                <?php } elseif (!empty($jsst_row['permissions'])) {
                                    foreach ($jsst_row['permissions'] AS $jsst_permission => $jsst_source) { ?>
                                        <span class="jsst-access-perm jsst-access-perm-<?php echo esc_attr($jsst_source); ?>" title="<?php echo esc_attr($jsst_source === 'agent' ? __('Granted directly to this agent', 'js-support-ticket') : __('Comes from their help-desk role', 'js-support-ticket')); ?>"><?php echo esc_html($jsst_permission); ?></span>
                                    <?php }
                                } else { ?>
                                    <span class="jsst-access-none"><?php echo esc_html(__('Nothing — no permissions are granted, so every action is refused.', 'js-support-ticket')); ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>

                    <?php foreach ($jsst_row['warnings'] AS $jsst_warning) { ?>
                        <div class="jsst-access-warning"><?php echo esc_html($jsst_warning); ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
