<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Workspace parity. (Roadmap 4.5-FE-01)
 *
 * One row per capability an agent desk is required to have, one column per
 * workspace, and every cell filled in by reading that workspace's own source
 * rather than by anybody remembering to tick it.
 */
if (!class_exists('JSSTworkspace')) {
    echo esc_html(__('The workspace parity matrix is not available.', 'js-support-ticket'));
    return;
}
$jsst_parity    = isset(jssupportticket::$jsst_data['parity']) ? jssupportticket::$jsst_data['parity'] : array();
$jsst_summary   = isset(jssupportticket::$jsst_data['paritysummary']) ? jssupportticket::$jsst_data['paritysummary'] : array();
$jsst_shortcuts = isset(jssupportticket::$jsst_data['parityshortcuts']) ? jssupportticket::$jsst_data['parityshortcuts'] : array();
$jsst_shells    = isset($jsst_parity['shells']) ? $jsst_parity['shells'] : array();
$jsst_rows      = isset($jsst_parity['rows']) ? $jsst_parity['rows'] : array();
$jsst_groups    = isset($jsst_parity['groups']) ? $jsst_parity['groups'] : array();
$jsst_totals    = isset($jsst_parity['totals']) ? $jsst_parity['totals'] : array();

/* What each verdict is called and how it reads. The wording is the point: a
   feature a workspace has built for itself is working software and a parity
   failure at the same time, and a screen that colours it the same as "missing"
   tells a developer to build something that is already there. */
$jsst_states = array(
    JSSTworkspace::PARITY_SHARED  => array('label' => __('Shared layer', 'js-support-ticket'), 'class' => 'jsst-parity-shared'),
    JSSTworkspace::PARITY_OWN     => array('label' => __('Its own copy', 'js-support-ticket'), 'class' => 'jsst-parity-own'),
    JSSTworkspace::PARITY_MISSING => array('label' => __('Not there', 'js-support-ticket'),    'class' => 'jsst-parity-missing'),
    JSSTworkspace::PARITY_NA      => array('label' => __('Not on this site', 'js-support-ticket'), 'class' => 'jsst-parity-na'),
    JSSTworkspace::PARITY_NOSHELL => array('label' => __('No such desk here', 'js-support-ticket'), 'class' => 'jsst-parity-na'),
);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Workspace Parity', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <div class="jsst-parity-lede">
                <?php echo esc_html(__('There are two agent desks in this help desk: the one in wp-admin, and the one an agent reaches without ever going there. They are meant to be the same desk seen from two places. Below is every capability a desk is required to have, and what each of the two actually does about it — read from their own code each time this page is opened, so it cannot be out of date and cannot be flattered.', 'js-support-ticket')); ?>
            </div>

            <?php if (!empty($jsst_summary)) { ?>
                <div class="jsst-parity-summary">
                    <div class="jsst-parity-figure">
                        <span class="jsst-parity-number"><?php echo esc_html($jsst_summary['done']); ?><span class="jsst-parity-of">/<?php echo esc_html($jsst_summary['total']); ?></span></span>
                        <span class="jsst-parity-caption"><?php echo esc_html(__('through the shared layer in both desks', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="jsst-parity-figure">
                        <span class="jsst-parity-number"><?php echo esc_html($jsst_summary['drifting']); ?></span>
                        <span class="jsst-parity-caption"><?php echo esc_html(__('present in both, built separately in each — works today, drifts tomorrow', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="jsst-parity-figure">
                        <span class="jsst-parity-number"><?php echo esc_html($jsst_summary['gaps']); ?></span>
                        <span class="jsst-parity-caption"><?php echo esc_html(__('the two desks disagree — one has it and the other does not', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="jsst-parity-figure">
                        <span class="jsst-parity-number"><?php echo esc_html($jsst_summary['absent']); ?></span>
                        <span class="jsst-parity-caption"><?php echo esc_html(__('in neither desk yet — a capability still to be built', 'js-support-ticket')); ?></span>
                    </div>
                    <?php if (!empty($jsst_summary['single'])) { ?>
                        <div class="jsst-parity-figure">
                            <span class="jsst-parity-number"><?php echo esc_html($jsst_summary['single']); ?></span>
                            <span class="jsst-parity-caption"><?php echo esc_html(__('only one desk is installed here, so there is nothing to compare them against', 'js-support-ticket')); ?></span>
                        </div>
                    <?php } ?>
                </div>
                <?php if (!empty($jsst_summary['unenforced'])) { ?>
                    <div class="jsst-parity-alert">
                        <strong><?php echo esc_html(__('Enforced by no permission at all:', 'js-support-ticket')); ?></strong>
                        <?php echo esc_html(implode(', ', $jsst_summary['unenforced'])); ?>.
                        <?php echo esc_html(__('A capability with no action behind it is decided separately by each screen, which is the one thing this release set out to end.', 'js-support-ticket')); ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <?php foreach ($jsst_groups AS $jsst_groupkey => $jsst_grouplabel) {
                $jsst_grouprows = array();
                foreach ($jsst_rows AS $jsst_key => $jsst_row) {
                    if ($jsst_row['group'] === $jsst_groupkey) {
                        $jsst_grouprows[$jsst_key] = $jsst_row;
                    }
                }
                if (empty($jsst_grouprows)) {
                    continue;
                } ?>
                <h2 class="jsst-parity-group"><?php echo esc_html($jsst_grouplabel); ?></h2>
                <div class="jsst-parity-wrap">
                <table class="jsst-parity-table">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo esc_html(__('Capability', 'js-support-ticket')); ?></th>
                            <?php foreach ($jsst_shells AS $jsst_shell) { ?>
                                <th scope="col"><?php echo esc_html($jsst_shell['label']); ?></th>
                            <?php } ?>
                            <th scope="col"><?php echo esc_html(__('Permission', 'js-support-ticket')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($jsst_grouprows AS $jsst_row) {
                        $jsst_verdicts = array(
                            'done'     => array('class' => 'jsst-parity-row-done',   'label' => __('Both, through the shared layer', 'js-support-ticket')),
                            'drifting' => array('class' => 'jsst-parity-row-drift',  'label' => __('Both, each on its own — will drift', 'js-support-ticket')),
                            'gap'      => array('class' => 'jsst-parity-row-gap',    'label' => __('The two desks disagree', 'js-support-ticket')),
                            'absent'   => array('class' => 'jsst-parity-row-absent', 'label' => __('Not built yet, in either', 'js-support-ticket')),
                            'single'   => array('class' => 'jsst-parity-row-absent', 'label' => __('Only one desk on this site', 'js-support-ticket')),
                            JSSTworkspace::PARITY_NA => array('class' => 'jsst-parity-row-absent', 'label' => __('Not on this site', 'js-support-ticket')),
                        );
                        $jsst_verdict = isset($jsst_verdicts[$jsst_row['verdict']]) ? $jsst_verdicts[$jsst_row['verdict']] : $jsst_verdicts['gap'];
                        $jsst_rowclass = $jsst_verdict['class']; ?>
                        <tr class="<?php echo esc_attr($jsst_rowclass); ?>">
                            <th scope="row">
                                <span class="jsst-parity-label"><?php echo esc_html($jsst_row['label']); ?></span>
                                <span class="jsst-parity-verdict"><?php echo esc_html($jsst_verdict['label']); ?></span>
                                <?php if (!empty($jsst_row['detail'])) { ?>
                                    <span class="jsst-parity-detail"><?php echo esc_html($jsst_row['detail']); ?></span>
                                <?php } ?>
                            </th>
                            <?php foreach (array_keys($jsst_shells) AS $jsst_shellkey) {
                                $jsst_cell = isset($jsst_row['cells'][$jsst_shellkey]) ? $jsst_row['cells'][$jsst_shellkey] : array('state' => JSSTworkspace::PARITY_MISSING, 'note' => '');
                                $jsst_state = isset($jsst_states[$jsst_cell['state']]) ? $jsst_states[$jsst_cell['state']] : $jsst_states[JSSTworkspace::PARITY_MISSING]; ?>
                                <td class="<?php echo esc_attr($jsst_state['class']); ?>">
                                    <span class="jsst-parity-state"><?php echo esc_html($jsst_state['label']); ?></span>
                                    <span class="jsst-parity-note"><?php echo esc_html($jsst_cell['note']); ?></span>
                                </td>
                            <?php } ?>
                            <td class="jsst-parity-permission">
                                <?php if ($jsst_row['enforced']) {
                                    $jsst_definition = JSSTcapability::definition($jsst_row['action']); ?>
                                    <span class="jsst-parity-action"><?php echo esc_html($jsst_definition ? $jsst_definition['label'] : $jsst_row['action']); ?></span>
                                    <code><?php echo esc_html($jsst_row['action']); ?></code>
                                <?php } else { ?>
                                    <span class="jsst-parity-unenforced"><?php echo esc_html(__('None', 'js-support-ticket')); ?></span>
                                <?php }
                                if (!$jsst_row['layer']) { ?>
                                    <span class="jsst-parity-unenforced"><?php echo esc_html(sprintf(/* translators: %s: name of a PHP class and method, e.g. JSSTworkspace::tabs. */ __('%s is not callable on this site.', 'js-support-ticket'), $jsst_row['service'])); ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                </div>
            <?php } ?>

            <h2 class="jsst-parity-group"><?php echo esc_html(__('Where each desk stands', 'js-support-ticket')); ?></h2>
            <div class="jsst-parity-wrap">
            <table class="jsst-parity-table jsst-parity-totals">
                <thead>
                    <tr>
                        <th scope="col"><?php echo esc_html(__('Workspace', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Shared layer', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Its own copy', 'js-support-ticket')); ?></th>
                        <th scope="col"><?php echo esc_html(__('Not there', 'js-support-ticket')); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($jsst_shells AS $jsst_shellkey => $jsst_shell) {
                    $jsst_total = isset($jsst_totals[$jsst_shellkey]) ? $jsst_totals[$jsst_shellkey] : array(); ?>
                    <tr>
                        <th scope="row">
                            <span class="jsst-parity-label"><?php echo esc_html($jsst_shell['label']); ?></span>
                            <span class="jsst-parity-detail"><?php echo esc_html($jsst_shell['summary']); ?></span>
                        </th>
                        <td class="jsst-parity-shared"><?php echo esc_html(isset($jsst_total[JSSTworkspace::PARITY_SHARED]) ? $jsst_total[JSSTworkspace::PARITY_SHARED] : 0); ?></td>
                        <td class="jsst-parity-own"><?php echo esc_html(isset($jsst_total[JSSTworkspace::PARITY_OWN]) ? $jsst_total[JSSTworkspace::PARITY_OWN] : 0); ?></td>
                        <td class="jsst-parity-missing"><?php echo esc_html(isset($jsst_total[JSSTworkspace::PARITY_MISSING]) ? $jsst_total[JSSTworkspace::PARITY_MISSING] : 0); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            </div>

            <h2 class="jsst-parity-group"><?php echo esc_html(__('The keyboard, defined once', 'js-support-ticket')); ?></h2>
            <div class="jsst-parity-lede">
                <?php echo esc_html(__('Muscle memory does not know which desk it is in. These are the keys, held in one place so that a workspace binds what it is given rather than choosing its own — and a key is only ever bound where the button it stands for would have been offered.', 'js-support-ticket')); ?>
            </div>
            <ul class="jsst-parity-keys">
                <?php foreach ($jsst_shortcuts AS $jsst_key => $jsst_shortcut) { ?>
                    <li><kbd><?php echo esc_html($jsst_key); ?></kbd> <span><?php echo esc_html($jsst_shortcut['label']); ?></span></li>
                <?php } ?>
            </ul>

        </div>
    </div>
</div>
