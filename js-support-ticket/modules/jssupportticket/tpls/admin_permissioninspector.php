<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The effective-permission inspector. (Roadmap 4.5-ARCH-04)
 *
 * Pick a person, name a ticket if the question is about one, and read the
 * answer the software itself would give — with the steps that produced it.
 */
if (!class_exists('JSSTpermissioninspector') || !class_exists('JSSTcapability')) {
    echo esc_html(__('The permission inspector is not available.', 'js-support-ticket'));
    return;
}
$jsst_people   = isset(jssupportticket::$jsst_data['inspectorpeople']) ? jssupportticket::$jsst_data['inspectorpeople'] : array();
$jsst_report   = isset(jssupportticket::$jsst_data['inspectorreport']) ? jssupportticket::$jsst_data['inspectorreport'] : false;
$jsst_asked    = isset(jssupportticket::$jsst_data['inspectorasked']) ? (int) jssupportticket::$jsst_data['inspectorasked'] : 0;
$jsst_ref      = isset(jssupportticket::$jsst_data['inspectorref']) ? jssupportticket::$jsst_data['inspectorref'] : '';
$jsst_notfound = !empty(jssupportticket::$jsst_data['inspectornotfound']);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Why Is This Allowed?', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <div class="jsst-access-lede">
                <?php echo esc_html(__('Choose somebody, and name a ticket if the question is about one. Every answer below is the answer the help desk itself gives — not a description of the settings, but the decision, with the steps it went through to reach it.', 'js-support-ticket')); ?>
            </div>

            <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="jsst-inspector-form">
                <input type="hidden" name="page" value="jssupportticket" />
                <input type="hidden" name="jstlay" value="permissioninspector" />
                <label for="jsst-inspector-user"><?php echo esc_html(__('Person', 'js-support-ticket')); ?></label>
                <select name="wpuid" id="jsst-inspector-user">
                    <option value="0"><?php echo esc_html(__('A visitor who is not signed in', 'js-support-ticket')); ?></option>
                    <?php foreach ($jsst_people AS $jsst_wpuid => $jsst_label) { ?>
                        <option value="<?php echo esc_attr($jsst_wpuid); ?>" <?php selected($jsst_asked, $jsst_wpuid); ?>><?php echo esc_html($jsst_label); ?></option>
                    <?php } ?>
                </select>
                <label for="jsst-inspector-ticket"><?php echo esc_html(__('Ticket', 'js-support-ticket')); ?></label>
                <input type="text" name="ticketref" id="jsst-inspector-ticket" value="<?php echo esc_attr($jsst_ref); ?>" placeholder="<?php echo esc_attr(__('Ticket reference — optional', 'js-support-ticket')); ?>" />
                <button type="submit" class="button button-primary"><?php echo esc_html(__('Explain', 'js-support-ticket')); ?></button>
            </form>

            <?php if ($jsst_notfound) { ?>
                <div class="jsst-access-alert"><?php echo esc_html(__('No ticket with that reference. The answers below are for this person in general, not for a particular ticket.', 'js-support-ticket')); ?></div>
            <?php } ?>

            <?php if ($jsst_report) { ?>
                <?php
                /* A trace step that appears, word for word, under every single
                   action is not reasoning about that action - it is a fact
                   about who is asking, and it was being restated thirty-odd
                   times. Those are collected here and shown once, on the card,
                   so the Why column carries only what actually differs. */
                $jsst_everystep = null;
                $jsst_rowcount = 0;
                foreach ($jsst_report['groups'] AS $jsst_scan) {
                    foreach ($jsst_scan AS $jsst_one) {
                        $jsst_rowcount++;
                        $jsst_details = array();
                        foreach ((array) $jsst_one['trace'] AS $jsst_st) {
                            $jsst_details[] = $jsst_st['detail'];
                        }
                        $jsst_everystep = ($jsst_everystep === null)
                            ? $jsst_details
                            : array_intersect($jsst_everystep, $jsst_details);
                    }
                }
                /* With only a couple of actions on screen, "shared by all of
                   them" means nothing, so leave the traces alone. */
                if ($jsst_rowcount < 4 || !is_array($jsst_everystep)) {
                    $jsst_everystep = array();
                }
                ?>
                <div class="jsst-access-card">
                    <div class="jsst-access-head">
                        <span class="jsst-access-name"><?php echo esc_html($jsst_report['actor']['display']); ?></span>
                        <?php if (!empty($jsst_report['actor']['email'])) { ?>
                            <span class="jsst-access-email"><?php echo esc_html($jsst_report['actor']['email']); ?></span>
                        <?php } ?>
                        <span class="jsst-access-tag"><?php echo esc_html(JSSTcapability::kindLabel($jsst_report['actor']['kind'])); ?></span>
                    </div>
                    <?php foreach ($jsst_report['summary'] AS $jsst_line) { ?>
                        <p class="jsst-inspector-summary"><?php echo esc_html($jsst_line); ?></p>
                    <?php } ?>
                    <?php if (!empty($jsst_everystep)) { ?>
                        <ul class="jsst-inspector-holds">
                            <?php foreach ($jsst_everystep AS $jsst_held) { ?>
                                <li><?php echo esc_html($jsst_held); ?></li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                    <?php foreach ($jsst_report['warnings'] AS $jsst_warning) { ?>
                        <div class="jsst-access-alert"><?php echo esc_html($jsst_warning); ?></div>
                    <?php } ?>
                </div>

                <?php foreach ($jsst_report['groups'] AS $jsst_group => $jsst_rows) { ?>
                    <h2 class="jsst-inspector-group"><?php echo esc_html(JSSTpermissioninspector::groupLabel($jsst_group)); ?></h2>
                    <table class="jsst-inspector-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__('Answer', 'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__('Why', 'js-support-ticket')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($jsst_rows AS $jsst_row) { ?>
                            <tr class="<?php echo esc_attr($jsst_row['allowed'] ? 'jsst-inspector-yes' : 'jsst-inspector-no'); ?>">
                                <td>
                                    <?php echo esc_html($jsst_row['label']); ?>
                                    <?php if ($jsst_row['scoped'] && empty($jsst_report['ticketid'])) { ?>
                                        <span class="jsst-inspector-hint"><?php echo esc_html(__('depends on the ticket', 'js-support-ticket')); ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="jsst-inspector-badge">
                                        <?php echo esc_html($jsst_row['allowed'] ? __('Allowed', 'js-support-ticket') : __('Refused', 'js-support-ticket')); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="jsst-inspector-reason"><?php echo esc_html($jsst_row['reason']); ?></div>
                                    <?php
                                    $jsst_steps = array();
                                    foreach ((array) $jsst_row['trace'] AS $jsst_step) {
                                        if (!in_array($jsst_step['detail'], $jsst_everystep, true)) {
                                            $jsst_steps[] = $jsst_step;
                                        }
                                    }
                                    if (!empty($jsst_steps)) { ?>
                                        <ol class="jsst-inspector-trace">
                                            <?php foreach ($jsst_steps AS $jsst_step) { ?>
                                                <li class="<?php echo esc_attr(!empty($jsst_step['result']) ? 'jsst-step-pass' : 'jsst-step-fail'); ?>"><?php echo esc_html($jsst_step['detail']); ?></li>
                                            <?php } ?>
                                        </ol>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            <?php } ?>

        </div>
    </div>
</div>
