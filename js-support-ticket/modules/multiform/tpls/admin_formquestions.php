<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * One form's questions. (Roadmap 6.5-FORM-05)
 *
 * The Forms screen used to be the register of forms and the whole of one
 * form's innards on a single page - the questions, the conditions between
 * them, a preview, the version history and the importer, one under another. On
 * a desk with several forms that meant choosing a form near the top and then
 * scrolling past everything about it to find the part you came for.
 *
 * So the register stays on the Forms screen and everything about a single form
 * is here, reached by choosing that form. Same split as every other list in
 * this admin: the list says what exists, a second screen is where you work.
 *
 * The controller loads exactly what it did before for both screens, so nothing
 * here had to learn a new source of data.
 */


/**
 * Forms. (Roadmap 5.0-FORM-01)
 *
 * A ticket form has always been editable here in two halves — a list that
 * reorders and toggles, and a separate page per custom field — and neither
 * half could say anything about one field in relation to another. This screen
 * is the whole form on one page: what it asks, in what order, who sees each
 * question, what each answer has to look like, and which questions only appear
 * when an earlier answer calls for them.
 *
 * It deliberately does not edit the form's own name, department or default
 * flag: that is the Multiform screen's, and putting the same setting on two
 * screens is how two screens come to disagree. What is here is everything
 * about the questions.
 */
if (!class_exists('JSSTforms')) {
    echo esc_html(__('Forms are not available.', 'js-support-ticket'));
    return;
}

$jsst_forms     = isset(jssupportticket::$jsst_data['fmforms']) ? jssupportticket::$jsst_data['fmforms'] : array();
$jsst_form      = isset(jssupportticket::$jsst_data['fmform']) ? jssupportticket::$jsst_data['fmform'] : false;
$jsst_fields    = isset(jssupportticket::$jsst_data['fmfields']) ? jssupportticket::$jsst_data['fmfields'] : array();
$jsst_logic     = isset(jssupportticket::$jsst_data['fmlogic']) ? jssupportticket::$jsst_data['fmlogic'] : array();
$jsst_patterns  = isset(jssupportticket::$jsst_data['fmpatterns']) ? jssupportticket::$jsst_data['fmpatterns'] : array();
$jsst_operators = isset(jssupportticket::$jsst_data['fmoperators']) ? jssupportticket::$jsst_data['fmoperators'] : array();
$jsst_stats     = isset(jssupportticket::$jsst_data['fmstats']) ? jssupportticket::$jsst_data['fmstats'] : array();
$jsst_departments = isset(jssupportticket::$jsst_data['fmdepartments']) ? jssupportticket::$jsst_data['fmdepartments'] : array();
$jsst_choices = isset(jssupportticket::$jsst_data['fmchoices']) ? jssupportticket::$jsst_data['fmchoices'] : array();

$jsst_base   = admin_url('admin.php?page=multiform&jstlay=forms');
$jsst_action = wp_nonce_url(admin_url('admin.php?page=multiform&task=saveform&action=jstask'), 'jsst-form');
$jsst_formid = is_array($jsst_form) ? (int) $jsst_form['id'] : 0;
$jsst_addurl = admin_url('admin.php?page=fieldordering&jstlay=adduserfeild&fieldfor=1&formid=' . $jsst_formid);
/* Adding, renaming and removing a form used to be a screen of its own. It is
   here now: two screens listing the same forms, one of which could only edit
   their questions and the other only their names, is not two screens' worth of
   idea. (Roadmap 5.0-FORM-01) */
$jsst_newform = admin_url('admin.php?page=multiform&jstlay=addmultiform');

/* Which fields it is worth offering a shape for. A date is a date and a file is
   a file; asking somebody to choose a pattern for those would be a form that
   offers a setting with no effect. */
$jsst_checkable = array('text', 'textarea', 'email');
/* Copying questions between forms needs the Multiform add-on; the Forms list
   decides this the same way. */
$jsst_many = in_array('multiform', jssupportticket::$_active_addons);
/* Questions only the staff ticket forms draw (wp-admin and the agent desk):
   labelled as such, with every switch still shown and saved as it always was. */
$jsst_staffform = array_filter(array('users', 'premade', 'internalnotetitle', 'assignto', 'duedate', 'status'), array('JSSTforms', 'staffOnly'));
wp_enqueue_script('jquery-ui-sortable');
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => is_array($jsst_form)
                /* translators: %s: form title. */
                ? sprintf(__('What "%s" asks', 'js-support-ticket'), $jsst_form['title'])
                : __('Questions', 'js-support-ticket'),
            'crumbs' => array(array('text' => __('Forms', 'js-support-ticket'), 'url' => admin_url('admin.php?page=multiform&jstlay=forms'))),
            'actions' => is_array($jsst_form)
                ? array(array('text' => __('Add a question', 'js-support-ticket'), 'url' => $jsst_addurl, 'icon' => 'plus'))
                : array(),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <?php if (!is_array($jsst_form)) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-body">
                        <?php JSSTlayout::adminEmpty(
                            __('No form chosen.', 'js-support-ticket'),
                            __('The questions, the conditions between them, what each answer has to look like and how often each one is filled in are all about one form at a time.', 'js-support-ticket'),
                            __('Back to the forms', 'js-support-ticket'),
                            admin_url('admin.php?page=multiform&jstlay=forms')
                        ); ?>
                    </div>
                </div>
            <?php } else { ?>

            <?php /* Four jobs on one page, one after another, made it close to six
               thousand pixels tall: the questions, the conditions between them, a
               preview with answer rates, and the version history. Tabs keep each on
               one screen. Without script every panel simply shows, as before. */
            $jsst_rulecount = is_array($jsst_logic) ? count($jsst_logic) : 0; ?>
            <nav class="jsst-fq-tabs" role="tablist" aria-label="<?php echo esc_attr(__('Form sections', 'js-support-ticket')); ?>">
                <a href="#questions" role="tab" class="jsst-fq-tab" data-tab="questions"><?php echo esc_html(__('Questions', 'js-support-ticket')); ?> <span class="jsst-fq-count"><?php echo (int) count($jsst_fields); ?></span></a>
                <a href="#conditions" role="tab" class="jsst-fq-tab" data-tab="conditions"><?php echo esc_html(__('Conditional questions', 'js-support-ticket')); ?><?php if ($jsst_rulecount) { ?> <span class="jsst-fq-count"><?php echo (int) $jsst_rulecount; ?></span><?php } ?></a>
                <a href="#preview" role="tab" class="jsst-fq-tab" data-tab="preview"><?php echo esc_html(__('Preview & answer rates', 'js-support-ticket')); ?></a>
                <?php /* Only where copying is possible. This tab used to hold the
                         version history as well, so it was drawn either way; with
                         that gone its only content is the copy card, which needs a
                         second form to exist. */
                if ($jsst_many) { ?>
                <a href="#history" role="tab" class="jsst-fq-tab" data-tab="history"><?php echo esc_html(__('Copy', 'js-support-ticket')); ?></a>
                <?php } ?>
            </nav>

            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_action); ?>">
                <input type="hidden" name="fmfields" value="1" />
                <input type="hidden" name="formid" value="<?php echo esc_attr($jsst_formid); ?>" />

                <div class="jsst-card jsst-fq-panel" data-jsst-tab="questions">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Questions', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('In the order they are asked. Change a number to move a question, switch on what applies, then save.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body jsst-card-flush">
                        <div class="jsst-table-wrap">
                            <table class="jsst-table jsst-fq-table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="jsst-col-order"><span class="screen-reader-text"><?php echo esc_html(__('Order', 'js-support-ticket')); ?></span></th>
                                        <th scope="col" class="jsst-fq-col-q"><?php echo esc_html(__('Question', 'js-support-ticket')); ?></th>
                                        <th scope="col" class="jsst-fq-col-flag" title="<?php echo esc_attr(__('Shown to signed-in users', 'js-support-ticket')); ?>"><?php echo esc_html(__('User published', 'js-support-ticket')); ?><span class="jsst-th-note"><?php echo esc_html(__('Signed-in users', 'js-support-ticket')); ?></span></th>
                                        <th scope="col" class="jsst-fq-col-flag" title="<?php echo esc_attr(__('Shown to visitors (not signed in)', 'js-support-ticket')); ?>"><?php echo esc_html(__('Visitor published', 'js-support-ticket')); ?><span class="jsst-th-note"><?php echo esc_html(__('Not signed in', 'js-support-ticket')); ?></span></th>
                                        <th scope="col" class="jsst-fq-col-flag" title="<?php echo esc_attr(__('Must be answered', 'js-support-ticket')); ?>"><?php echo esc_html(__('Required', 'js-support-ticket')); ?><span class="jsst-th-note"><?php echo esc_html(__('Must answer', 'js-support-ticket')); ?></span></th>
                                        <th scope="col" class="jsst-fq-col-actions"><span class="screen-reader-text"><?php echo esc_html(__('Actions', 'js-support-ticket')); ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_fields AS $jsst_name => $jsst_field) {
                                    $jsst_isstaff = in_array($jsst_name, $jsst_staffform, true); ?>
                                    <?php $jsst_justsaved = (isset($_GET['saved']) && (int) $_GET['saved'] === (int) $jsst_field['id']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
                                    <tr class="jsst-q-row<?php echo ($jsst_field['published'] || $jsst_field['forvisitors']) ? '' : ' jsst-q-off'; ?><?php echo $jsst_justsaved ? ' jsst-fq-justsaved' : ''; ?>" data-fieldid="<?php echo esc_attr((int) $jsst_field['id']); ?>">
                                        <td class="jsst-col-order">
                                            <?php /* Drag the handle, or focus it and use the arrow keys. The
                                               number is still what is saved, kept in step behind the scenes;
                                               showing it only took room. */ ?>
                                            <?php if ($jsst_field['adminonly']) { /* Set on the question's own page now; carried so a save here keeps it. */ ?>
                                                <input type="hidden" name="adminonly[<?php echo esc_attr($jsst_name); ?>]" value="1" />
                                            <?php } ?>
                                            <button type="button" class="jsst-fq-handle" aria-label="<?php /* translators: %s is a field name. */ echo esc_attr(sprintf(__('Move %s (drag, or use the up and down arrow keys)', 'js-support-ticket'), $jsst_field['title'])); ?>" title="<?php echo esc_attr(__('Drag to move', 'js-support-ticket')); ?>"></button>
                                            <input type="hidden" class="jsst-order" name="order[<?php echo esc_attr($jsst_name); ?>]" value="<?php echo esc_attr($jsst_field['ordering']); ?>" />
                                        </td>
                                        <td>
                                            <?php /* Every question opens its editor, the product's own as well as
                                               the ones this site added. The editor has always accepted both -
                                               a placeholder, a default value and a description are as useful on
                                               Subject as on anything else - and the screen this one replaced
                                               offered Edit on every row. Restricting it to custom questions
                                               took that away with nothing put in its place. (Roadmap 5.0-FORM-01) */
                                            $jsst_editurl = admin_url('admin.php?page=fieldordering&jstlay=adduserfeild&jssupportticketid=' . (int) $jsst_field['id'] . '&fieldfor=1&formid=' . $jsst_formid); ?>
                                            <div class="jsst-q-head">
                                                <span class="jsst-table-name">
                                                    <a href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html($jsst_field['title']); ?></a>
                                                </span>
                                                <?php /* Removing is offered on the questions this site added and on no
                                                   others, which is the rule the table this replaced already applied:
                                                   a product question is what the ticket is made of and taking one away
                                                   would leave the desk with a column nothing fills. Take it off the
                                                   form instead - the switch is in this same row.

                                                   They sit on the title's own line, at the end of it. Under the row
                                                   they read as two more chips in a column that is already a stack of
                                                   chips, which is what made them hard to find. (Roadmap 5.0-FORM-01) */ ?>
                                            </div>
                                            <span class="jsst-table-sub">
                                                <span class="jsst-pill jsst-pill-info"><span class="jsst-dot"></span><?php echo esc_html(JSSTforms::typeLabel($jsst_field['type'])); ?></span>
                                                <?php if ($jsst_field['custom']) { ?>
                                                    <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('yours', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                                <?php if ($jsst_field['locked']) { ?>
                                                    <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('always on the form', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                                <?php if ($jsst_isstaff) { ?>
                                                    <span class="jsst-pill jsst-pill-off" title="<?php echo esc_attr(__('Only the ticket form in wp-admin and the agent desk ask this.', 'js-support-ticket')); ?>"><span class="jsst-dot"></span><?php echo esc_html(__('staff form only', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                                <?php if ($jsst_field['adminonly']) { ?>
                                                    <span class="jsst-pill jsst-pill-off" title="<?php echo esc_attr(__('Hidden from customers. Change it on the question\'s Edit page.', 'js-support-ticket')); ?>"><span class="jsst-dot"></span><?php echo esc_html(__('Admin/Agent only', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                                <?php if (isset($jsst_logic[$jsst_name])) { ?>
                                                    <span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span><?php echo esc_html(__('only under a condition', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                            </span>
                                            <?php if ($jsst_field['options']) { ?>
                                                <div class="jsst-chips">
                                                    <?php foreach (array_slice($jsst_field['options'], 0, 4) AS $jsst_option) { ?>
                                                        <span class="jsst-chip"><?php echo esc_html($jsst_option); ?></span>
                                                    <?php } ?>
                                                    <?php if (count($jsst_field['options']) > 4) { ?>
                                                        <span class="jsst-chip jsst-chip-more"><?php
                                                            /* translators: %d is how many further choices there are. */
                                                            echo esc_html(sprintf(__('+%d more', 'js-support-ticket'), count($jsst_field['options']) - 4)); ?></span>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                            <?php if (in_array($jsst_field['type'], $jsst_checkable, true)) {
                                                /* The answer check lived in its own column: a list and two number
                                                   boxes on every row, the widest thing in the table, and set on
                                                   almost no question. It is a line under the question now, saying
                                                   what is set, and opens where it is changed. */
                                                $jsst_rpat = isset($jsst_patterns[$jsst_field['rules']['pattern']]) ? $jsst_patterns[$jsst_field['rules']['pattern']] : '';
                                                $jsst_rmin = (int) $jsst_field['rules']['min'];
                                                $jsst_rmax = (int) $jsst_field['rules']['max'];
                                                $jsst_rset = ($jsst_field['rules']['pattern'] !== '' && $jsst_field['rules']['pattern'] !== 'none' && $jsst_field['rules']['pattern'] !== 'any') || $jsst_rmin > 0 || $jsst_rmax > 0;
                                                if ($jsst_rmin > 0 && $jsst_rmax > 0) {
                                                    /* translators: 1: minimum number of characters, 2: maximum number of characters. */
                                                    $jsst_rlen = sprintf(__('%1$d–%2$d characters', 'js-support-ticket'), $jsst_rmin, $jsst_rmax);
                                                } elseif ($jsst_rmin > 0) {
                                                    /* translators: %d: minimum number of characters. */
                                                    $jsst_rlen = sprintf(__('at least %d characters', 'js-support-ticket'), $jsst_rmin);
                                                } elseif ($jsst_rmax > 0) {
                                                    /* translators: %d: maximum number of characters. */
                                                    $jsst_rlen = sprintf(__('up to %d characters', 'js-support-ticket'), $jsst_rmax);
                                                } else {
                                                    $jsst_rlen = __('any length', 'js-support-ticket');
                                                } ?>
                                                <details class="jsst-fq-rules<?php echo $jsst_rset ? ' jsst-fq-rules-set' : ''; ?>">
                                                    <summary><span class="jsst-fq-rules-label"><?php echo esc_html(__('Answer check', 'js-support-ticket')); ?>:</span> <?php echo esc_html($jsst_rpat . ' · ' . $jsst_rlen); ?></summary>
                                                    <div class="jsst-fq-rules-body">
                                                <span class="jsst-rule">
                                                    <select name="pattern[<?php echo esc_attr($jsst_name); ?>]"
                                                            aria-label="<?php /* translators: %s is a field name. */ echo esc_attr(sprintf(__('What %s has to look like', 'js-support-ticket'), $jsst_field['title'])); ?>">
                                                        <?php foreach ($jsst_patterns AS $jsst_key => $jsst_label) { ?>
                                                            <option value="<?php echo esc_attr($jsst_key); ?>" <?php selected($jsst_field['rules']['pattern'], $jsst_key); ?>><?php echo esc_html($jsst_label); ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <?php /* The length limits, said in words. Two bare boxes labelled
                                                       "min" and "max" read as a range of values - which is what they
                                                       were taken for - when what they actually count is characters.
                                                       Empty means no limit, so the placeholder says so rather than
                                                       repeating the word the label already gives. */ ?>
                                                    <span class="jsst-rule-len">
                                                        <span><?php echo esc_html(__('Length', 'js-support-ticket')); ?></span>
                                                        <?php /* Zero is how "no limit" is stored, and printing it made every
                                                           question read "Length 0 to 0 characters" - a limit of nothing,
                                                           which is both meaningless and the opposite of what it means. An
                                                           empty box shows the placeholder instead, and saves back as zero. */ ?>
                                                        <input type="number" min="0" max="9999"
                                                               name="min[<?php echo esc_attr($jsst_name); ?>]" value="<?php echo esc_attr($jsst_field['rules']['min'] > 0 ? $jsst_field['rules']['min'] : ''); ?>"
                                                               aria-label="<?php /* translators: %s is a field name. */ echo esc_attr(sprintf(__('Fewest characters in %s', 'js-support-ticket'), $jsst_field['title'])); ?>"
                                                               placeholder="<?php echo esc_attr(__('any', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('to', 'js-support-ticket')); ?></span>
                                                        <input type="number" min="0" max="9999"
                                                               name="max[<?php echo esc_attr($jsst_name); ?>]" value="<?php echo esc_attr($jsst_field['rules']['max'] > 0 ? $jsst_field['rules']['max'] : ''); ?>"
                                                               aria-label="<?php /* translators: %s is a field name. */ echo esc_attr(sprintf(__('Most characters in %s', 'js-support-ticket'), $jsst_field['title'])); ?>"
                                                               placeholder="<?php echo esc_attr(__('any', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('chars', 'js-support-ticket')); ?></span>
                                                    </span>
                                                </span>
                                                    </div>
                                                </details>
                                            <?php } ?>
                                        </td>
                                        <td class="jsst-fq-flag">
                                            <label class="jsst-check">
                                                <input type="checkbox" class="jsst-q-show" name="published[<?php echo esc_attr($jsst_name); ?>]" value="1" <?php checked($jsst_field['published']); ?> <?php disabled($jsst_field['locked']); ?> />
                                                <span class="screen-reader-text"><?php echo esc_html($jsst_field['title']); ?></span>
                                            </label>
                                        </td>
                                        <td class="jsst-fq-flag">
                                            <label class="jsst-check">
                                                <input type="checkbox" class="jsst-q-visitor" name="forvisitors[<?php echo esc_attr($jsst_name); ?>]" value="1" <?php checked($jsst_field['forvisitors']); ?> <?php disabled($jsst_field['locked'] || $jsst_isstaff); ?><?php if ($jsst_isstaff) { ?> title="<?php echo esc_attr(__('Visitors never see this question: it is only on the staff ticket form.', 'js-support-ticket')); ?>"<?php } ?> />
                                                <span class="screen-reader-text"><?php echo esc_html($jsst_field['title']); ?></span>
                                            </label>
                                        </td>
                                        <td class="jsst-q-dim jsst-fq-flag">
                                            <?php /* Off limits for two different reasons, and the column
                                                     beside it tells them apart.

                                                     A `locked` field cannot be unpublished, and an answer
                                                     that may be left empty is the same loss by a quieter
                                                     route - the Field Ordering screen this one replaces
                                                     refused it outright.

                                                     A consent tick is the other way round: switch it off
                                                     and it is not asked, which is fine, but a tick that is
                                                     shown and optional records somebody as asked while the
                                                     agreement was never given.

                                                     The save ignores the posted key in both cases, so the
                                                     disabled box posting nothing costs nothing. */ ?>
                                            <?php if (!empty($jsst_field['alwaysrequired']) && !$jsst_field['published'] && !$jsst_field['forvisitors']) { ?>
                                                <?php /* A consent tick that is switched off is not asked at all, so
                                                         a ticked "Needed" beside it read as a contradiction. It is
                                                         needed whenever it is shown, which is what this says. */ ?>
                                                <span class="jsst-table-sub" title="<?php echo esc_attr(__('A consent tick must be ticked whenever it is shown. It is switched off, so it is not asked.', 'js-support-ticket')); ?>"><?php echo esc_html(__('if shown', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                            <label class="jsst-check">
                                                <input type="checkbox" name="required[<?php echo esc_attr($jsst_name); ?>]" value="1" <?php checked($jsst_field['required']); ?> <?php disabled($jsst_field['locked'] || !empty($jsst_field['alwaysrequired'])); ?> />
                                                <span class="screen-reader-text"><?php echo esc_html($jsst_field['title']); ?></span>
                                            </label>
                                            <?php } ?>
                                        </td>
                                        <td class="jsst-fq-actions">
                                            <div class="jsst-fq-actbtns">
                                                <a class="jsst-fq-act jsst-fq-act-edit" href="<?php echo esc_url($jsst_editurl); ?>" title="<?php echo esc_attr(__('Edit', 'js-support-ticket')); ?>">
                                                    <svg class="jsst-fq-ico" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M12 20h9"/><path d="M16.4 3.6a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg><span class="screen-reader-text"><?php /* translators: %s is a field name. */ echo esc_html(sprintf(__('Edit %s', 'js-support-ticket'), $jsst_field['title'])); ?></span>
                                                </a>
                                                <?php if ($jsst_field['custom']) { ?>
                                                    <a class="jsst-fq-act jsst-fq-act-delete" title="<?php echo esc_attr(__('Delete', 'js-support-ticket')); ?>"
                                                       onclick="return confirm('<?php echo esc_js(__('Delete this question? Answers already given to it on existing tickets are kept.', 'js-support-ticket')); ?>');"
                                                       href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=fieldordering&task=removeuserfeild&action=jstask&jssupportticketid=' . (int) $jsst_field['id'] . '&fieldfor=1&formid=' . $jsst_formid), 'remove-userfeild-' . (int) $jsst_field['id'])); ?>">
                                                        <svg class="jsst-fq-ico" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg><span class="screen-reader-text"><?php /* translators: %s is a field name. */ echo esc_html(sprintf(__('Delete %s', 'js-support-ticket'), $jsst_field['title'])); ?></span>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="jsst-card-foot">
                        <p class="jsst-field-note"><?php echo esc_html(__('Questions marked "always on the form" cannot be switched off: a ticket needs them. Greyed rows are published to nobody (neither users nor visitors), so their other settings do not apply until you publish them.', 'js-support-ticket')); ?></p>
                    </div>
                </div>

                <?php
/* The question pickers, and one condition row, drawn in one place because the
 * rule editor below draws them at three nesting depths and the browser draws
 * more of them afterwards. (Roadmap 6.5-FORM-04)
 */
if (!function_exists('jsst_logic_field_options')) {
    /**
     * Every question this form asks, the site's own ones told apart from the
     * product's.
     *
     * Split into two groups deliberately. The list is twenty-five of the
     * product's questions followed by however many this site has added, and the
     * added ones - the ones somebody came to this screen to write a rule about,
     * having just retired the editor that used to hold their conditions - were
     * at the bottom of a long flat list with nothing marking where they began.
     * Both kinds have always been offered here; only the finding of them was
     * the problem.
     */
    function jsst_logic_field_options($jsst_fields, $jsst_selected) {
        $jsst_own = array();
        $jsst_added = array();
        foreach ($jsst_fields AS $jsst_name => $jsst_field) {
            if (!empty($jsst_field['custom'])) {
                $jsst_added[$jsst_name] = $jsst_field;
            } else {
                $jsst_own[$jsst_name] = $jsst_field;
            }
        }
        $jsst_groups = array(
            __('The form\'s own questions', 'js-support-ticket') => $jsst_own,
            __('Questions you added', 'js-support-ticket')       => $jsst_added,
        );
        foreach ($jsst_groups AS $jsst_label => $jsst_set) {
            if (!$jsst_set) { continue; }
            /* Only labelled where there is something to tell apart. A desk with
               no questions of its own gets one plain list, not a list with a
               heading over all of it. */
            $jsst_label_it = (count($jsst_groups) > 1 && $jsst_own && $jsst_added);
            if ($jsst_label_it) { ?>
                <optgroup label="<?php echo esc_attr($jsst_label); ?>">
            <?php }
            foreach ($jsst_set AS $jsst_name => $jsst_field) { ?>
                <option value="<?php echo esc_attr($jsst_name); ?>" <?php selected($jsst_selected, $jsst_name); ?>><?php
                    echo esc_html($jsst_field['title']); ?></option>
            <?php }
            if ($jsst_label_it) { ?>
                </optgroup>
            <?php }
        }
    }
}
if (!function_exists('jsst_logic_condition')) {
    /**
     * One condition: which question, how to compare, and what to.
     *
     * The answer is a chooser where the question has a known set of answers and
     * a box where it has not, and is swapped by the script below when somebody
     * changes which question the condition watches. A stored value that no
     * longer matches any choice - a department since deleted - is kept and
     * shown, because silently dropping it would turn a rule that says something
     * wrong into one that says nothing. (Roadmap 6.5-FORM-03)
     */
    function jsst_logic_condition($jsst_i, $jsst_g, $jsst_c, $jsst_one, $jsst_fields, $jsst_operators, $jsst_choices) {
        $jsst_one += array('when' => '', 'op' => 'is', 'value' => '');
        $jsst_base = 'logic[' . $jsst_i . '][groups][' . $jsst_g . '][' . $jsst_c . ']';
        $jsst_list = isset($jsst_choices[$jsst_one['when']]) ? $jsst_choices[$jsst_one['when']] : array();
        /* "has been answered" and "has been left empty" read nothing but whether
           there is an answer, so the box beside them was a control the evaluator
           ignores - somebody types a value into it, saves, and the rule behaves
           as though they had not. It is hidden rather than removed so that a
           value already typed survives a change of mind about the operator. */
        $jsst_novalue = in_array($jsst_one['op'], array('filled', 'empty'), true);
        /* An id-backed question with no list to choose from: the comparison is
           against a number, so a name typed here can never match and nothing
           would say why. */
        $jsst_idnote = (!$jsst_list && $jsst_one['when'] !== ''
            && method_exists('JSSTforms', 'idBacked')
            && in_array($jsst_one['when'], JSSTforms::idBacked(), true)); ?>
        <div class="jsst-logic-cond">
            <select name="<?php echo esc_attr($jsst_base); ?>[when]" class="jsst-logic-when"
                    aria-label="<?php echo esc_attr(__('The question it depends on', 'js-support-ticket')); ?>">
                <option value=""></option>
                <?php jsst_logic_field_options($jsst_fields, $jsst_one['when']); ?>
            </select>
            <select name="<?php echo esc_attr($jsst_base); ?>[op]" class="jsst-logic-op"
                    aria-label="<?php echo esc_attr(__('How to compare it', 'js-support-ticket')); ?>">
                <?php foreach ($jsst_operators AS $jsst_key => $jsst_label) { ?>
                    <option value="<?php echo esc_attr($jsst_key); ?>" <?php selected($jsst_one['op'], $jsst_key); ?>><?php
                        echo esc_html($jsst_label); ?></option>
                <?php } ?>
            </select>
            <span class="jsst-logic-value"<?php if ($jsst_novalue) { echo ' style="display:none;"'; } ?>>
                <?php if (!empty($jsst_list)) { ?>
                    <select name="<?php echo esc_attr($jsst_base); ?>[value]"
                            aria-label="<?php echo esc_attr(__('The answer that brings it out', 'js-support-ticket')); ?>">
                        <option value=""><?php echo esc_html(__('— any answer —', 'js-support-ticket')); ?></option>
                        <?php $jsst_found = false;
                        foreach ($jsst_list AS $jsst_choice) {
                            if ((string) $jsst_choice['v'] === (string) $jsst_one['value']) { $jsst_found = true; } ?>
                            <option value="<?php echo esc_attr($jsst_choice['v']); ?>" <?php selected($jsst_one['value'], $jsst_choice['v']); ?>><?php
                                echo esc_html($jsst_choice['t']); ?></option>
                        <?php }
                        if (!$jsst_found && (string) $jsst_one['value'] !== '') { ?>
                            <option value="<?php echo esc_attr($jsst_one['value']); ?>" selected="selected"><?php
                                /* translators: %s is a stored value that no longer matches any choice. */
                                echo esc_html(sprintf(__('%s — no longer on the list', 'js-support-ticket'), $jsst_one['value'])); ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <input type="text" name="<?php echo esc_attr($jsst_base); ?>[value]" value="<?php echo esc_attr($jsst_one['value']); ?>"
                           aria-label="<?php echo esc_attr(__('The answer that brings it out', 'js-support-ticket')); ?>" />
                <?php } ?>
            </span>
            <button type="button" class="jsst-logic-x jsst-logic-drop-cond"
                    title="<?php echo esc_attr(__('Remove this condition', 'js-support-ticket')); ?>"
                    aria-label="<?php echo esc_attr(__('Remove this condition', 'js-support-ticket')); ?>">&times;</button>
            <span class="jsst-logic-idnote"<?php if (!$jsst_idnote || $jsst_novalue) { echo ' style="display:none;"'; } ?>><?php
                echo esc_html(__('This question is stored as an id, so a name typed here will never match. Use "has been answered" unless you know the id.', 'js-support-ticket')); ?></span>
        </div>
    <?php }
}
                ?>
                <?php
                /* Conditions, grouped. (Roadmap 6.5-FORM-04)
                 *
                 * This card replaced a four-column table of one condition per
                 * question, whose note said one condition was on purpose. It was,
                 * until the per-question "Visibility conditions" editor was retired
                 * into this screen: that editor had offered AND and OR since it was
                 * written, `JSSTforms` has stored and evaluated groups since
                 * 6.5-FORM-04, and `JSSTformlogicmigration` carries the old rules
                 * here. A screen that can only show the first condition of a rule it
                 * has just been handed is a screen that loses the rest of it the
                 * moment somebody presses Save - `cleanGroups()` rebuilds the groups
                 * from what was posted, and what was posted was one row.
                 *
                 * So: groups ANDed, conditions inside a group ORed, which is the
                 * shape the old editor used, the shape the store keeps and the shape
                 * both halves evaluate. Nothing here invents a third one.
                 *
                 * Only `show` and the groups are posted. The duplicate of the first
                 * condition that older readers need is derived in `cleanLogic()`,
                 * where it cannot drift out of step with what is on screen.
                 */
                $jsst_rules = array_values($jsst_logic);
                /* Two blank rules after the ones that exist, the same courtesy the
                   table had: somebody arriving to write their first condition should
                   not have to find a button before they can begin. */
                $jsst_blank = array('show' => '', 'groups' => array(array(array('when' => '', 'op' => 'is', 'value' => ''))));
                $jsst_rules[] = $jsst_blank;
                ?>
                <div class="jsst-card jsst-fq-panel" data-jsst-tab="conditions">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Conditional questions', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('Show a question only when an earlier answer calls for it, e.g. show "Order number" only when Department is Billing. A hidden question is never required.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body">
                        <div class="jsst-logic" id="jsst-logic">
                        <?php foreach ($jsst_rules AS $jsst_i => $jsst_rule) {
                            /* A rule saved before grouping has no `groups`, so its
                               single condition is wrapped into one - one group of one
                               is exactly what it means. */
                            $jsst_groups = (!empty($jsst_rule['groups']) && is_array($jsst_rule['groups']))
                                ? $jsst_rule['groups']
                                : array(array(array(
                                    'when'  => isset($jsst_rule['when']) ? $jsst_rule['when'] : '',
                                    'op'    => isset($jsst_rule['op']) ? $jsst_rule['op'] : 'is',
                                    'value' => isset($jsst_rule['value']) ? $jsst_rule['value'] : '',
                                  ))); ?>
                            <div class="jsst-logic-rule" data-rule="<?php echo esc_attr($jsst_i); ?>">
                                <div class="jsst-logic-rule-head">
                                    <label class="jsst-logic-lead"><?php echo esc_html(__('Show', 'js-support-ticket')); ?></label>
                                    <select name="logic[<?php echo esc_attr($jsst_i); ?>][show]" class="jsst-logic-show"
                                            aria-label="<?php echo esc_attr(__('The question to hold back', 'js-support-ticket')); ?>">
                                        <option value=""><?php echo esc_html(__('Choose a question…', 'js-support-ticket')); ?></option>
                                        <?php jsst_logic_field_options($jsst_fields, isset($jsst_rule['show']) ? $jsst_rule['show'] : ''); ?>
                                    </select>
                                    <span class="jsst-logic-lead"><?php echo esc_html(__('only when:', 'js-support-ticket')); ?></span>
                                    <button type="button" class="jsst-logic-x jsst-logic-drop-rule"
                                            title="<?php echo esc_attr(__('Remove this conditional question', 'js-support-ticket')); ?>"
                                            aria-label="<?php echo esc_attr(__('Remove this conditional question', 'js-support-ticket')); ?>"><?php echo esc_html(__('Remove', 'js-support-ticket')); ?></button>
                                </div>
                                <div class="jsst-logic-groups">
                                    <?php foreach ($jsst_groups AS $jsst_g => $jsst_group) {
                                        if (!is_array($jsst_group) || !$jsst_group) { continue; } ?>
                                        <?php if ($jsst_g > 0) { ?>
                                            <div class="jsst-logic-join jsst-logic-and"><span><?php echo esc_html(__('and', 'js-support-ticket')); ?></span></div>
                                        <?php } ?>
                                        <div class="jsst-logic-group" data-group="<?php echo esc_attr($jsst_g); ?>">
                                            <?php foreach ($jsst_group AS $jsst_c => $jsst_one) {
                                                if ($jsst_c > 0) { ?>
                                                    <div class="jsst-logic-join jsst-logic-or"><span><?php echo esc_html(__('or', 'js-support-ticket')); ?></span></div>
                                                <?php }
                                                jsst_logic_condition($jsst_i, $jsst_g, $jsst_c, $jsst_one, $jsst_fields, $jsst_operators, $jsst_choices);
                                            } ?>
                                            <div class="jsst-logic-group-foot">
                                                <button type="button" class="jsst-btn jsst-btn-sm jsst-logic-add-or"><?php
                                                    echo esc_html(__('+ or another answer', 'js-support-ticket')); ?></button>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="jsst-logic-rule-foot">
                                    <button type="button" class="jsst-btn jsst-btn-sm jsst-logic-add-and"><?php
                                        echo esc_html(__('+ and also', 'js-support-ticket')); ?></button>
                                </div>
                            </div>
                        <?php } ?>
                        </div>
                        <div class="jsst-logic-foot">
                            <button type="button" class="jsst-btn" id="jsst-logic-add-rule"><?php
                                echo esc_html(__('+ Add a conditional question', 'js-support-ticket')); ?></button>
                        </div>
                    </div>
                    <div class="jsst-card-foot">
                        <p class="jsst-field-note"><?php echo esc_html(__('Inside one box, any one answer is enough ("or"). Boxes joined by "and also" must all match.', 'js-support-ticket')); ?></p>
                    </div>
                </div>
                <?php /* The rule editor's own look. Inline because this screen has no
                   stylesheet of its own and the card classes around it come from the
                   admin bundle; these are the few rules the nested shape needs that a
                   flat table did not. (Roadmap 6.5-FORM-04) */ ?>
                <style>
                    #jsstadmin-wrapper .jsst-logic-rule{border:1px solid var(--jsst-border,#e7e9ee);border-radius:8px;padding:14px 16px;margin:0 0 14px;background:var(--jsst-panel,#fff);}
                    #jsstadmin-wrapper .jsst-logic-rule.jsst-logic-off{opacity:.45;}
                    #jsstadmin-wrapper .jsst-logic-rule-head{display:flex;flex-wrap:wrap;align-items:center;gap:8px 10px;margin-bottom:12px;}
                    #jsstadmin-wrapper .jsst-logic-rule-head .jsst-logic-show{flex:1 1 240px;max-width:420px;}
                    #jsstadmin-wrapper .jsst-logic-lead{font-weight:600;font-size:13.5px;color:var(--jsst-text,#1a1d23);}
                    #jsstadmin-wrapper .jsst-logic-group{border:1px solid var(--jsst-line,#ebecec);border-radius:6px;padding:12px;background:var(--jsst-surface,#f8fafc);}
                    #jsstadmin-wrapper .jsst-logic-cond{display:flex;flex-wrap:wrap;align-items:center;gap:8px;}
                    #jsstadmin-wrapper .jsst-logic-when{flex:1 1 200px;}
                    #jsstadmin-wrapper .jsst-logic-op{flex:0 1 190px;}
                    #jsstadmin-wrapper .jsst-logic-value{flex:1 1 180px;display:flex;}
                    #jsstadmin-wrapper .jsst-logic-value > *{width:100%;}
                    #jsstadmin-wrapper .jsst-logic select,#jsstadmin-wrapper .jsst-logic input[type="text"]{
                        box-sizing:border-box;max-width:100%;min-height:40px;margin:0;padding:9px 13px;
                        border:1px solid var(--jsst-border,#e7e9ee);border-radius:var(--jsst-r-sm,7px);
                        background-color:var(--jsst-panel,#fff);color:var(--jsst-text,#1a1d23);font-size:13.5px;line-height:1.4;box-shadow:none;}
                    #jsstadmin-wrapper .jsst-logic select{padding-inline-end:34px;-webkit-appearance:none;appearance:none;
                        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8' fill='none'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%236a7280' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
                        background-repeat:no-repeat;background-position:right 13px center;background-size:12px 8px;}
                    #jsstadmin-wrapper .jsst-logic select:focus,#jsstadmin-wrapper .jsst-logic input[type="text"]:focus{border-color:var(--jsst-accent,#4f46e5);box-shadow:0 0 0 3px var(--jsst-accent-soft,#eef0fd);outline:none;}
                    #jsstadmin-wrapper .jsst-logic-join{display:flex;align-items:center;gap:8px;margin:10px 0;color:var(--jsst-text-faint,#98a0ac);
                        font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;}
                    #jsstadmin-wrapper .jsst-logic-join::before,#jsstadmin-wrapper .jsst-logic-join::after{content:"";flex:1;border-top:1px solid var(--jsst-line,#ebecec);}
                    #jsstadmin-wrapper .jsst-logic-and{color:var(--jsst-text,#1a1d23);}
                    #jsstadmin-wrapper .jsst-logic-group-foot,#jsstadmin-wrapper .jsst-logic-rule-foot{margin-top:12px;}
                    #jsstadmin-wrapper .jsst-logic-x{background:none;border:1px solid transparent;border-radius:6px;cursor:pointer;color:var(--jsst-text-faint,#98a0ac);font-size:18px;line-height:1;padding:6px 8px;}
                    #jsstadmin-wrapper .jsst-logic-x:hover,#jsstadmin-wrapper .jsst-logic-x:focus{color:var(--jsst-danger,#c0392b);background:var(--jsst-danger-soft,#fdf0ee);}
                    #jsstadmin-wrapper .jsst-logic-drop-rule{margin-left:auto;font-size:12.5px;font-weight:600;color:var(--jsst-danger,#c0392b);}
                    #jsstadmin-wrapper .jsst-logic-idnote{flex-basis:100%;color:#8a6d00;font-size:12px;line-height:1.5;}
                    #jsstadmin-wrapper .jsst-logic-foot{margin-top:4px;}
                    /* Questions table */
                    #jsstadmin-wrapper .jsst-q-off .jsst-q-dim{opacity:.4;}
                    #jsstadmin-wrapper .jsst-q-na{color:var(--jsst-text-faint,#98a0ac);}
                    #jsstadmin-wrapper .jsst-rule-len input[type="number"]{width:64px;}
                    @media (max-width:782px){
                        #jsstadmin-wrapper .jsst-logic-cond > *,#jsstadmin-wrapper .jsst-logic-rule-head .jsst-logic-show{flex:1 1 100%;max-width:100%;}
                        #jsstadmin-wrapper .jsst-logic-drop-rule{margin-left:0;}
                    }
                </style>
                <?php /* Adding and removing conditions, and keeping the answer control
                   honest as the question changes. (Roadmap 6.5-FORM-03, 6.5-FORM-04)

                   Rows are cloned from ones already on the page rather than built
                   from a template string, so every `<option>` the server rendered -
                   the whole question list, its two optgroups, the operators - comes
                   along without being described a second time in JavaScript.

                   Indices only have to be unique within their rule, never
                   contiguous: PHP builds the same nested array either way and
                   `cleanGroups()` walks it with foreach. So a counter per container
                   is enough, and nothing has to be renumbered when a row in the
                   middle is removed - renumbering being the part of this that would
                   otherwise be easy to get wrong and quiet when it was.

                   Without JavaScript the rules already on the page still render and
                   still save; what is lost is the ability to add a row, which is the
                   right failure for an editor. */ ?>
                <script type="text/javascript">
                /* A row whose question is not on the form is greyed, so its other
                   switches read as not applying. Nothing is disabled: every value
                   still posts exactly as before. */
                (function () {
                    var jsstBoxes = document.querySelectorAll('.jsst-q-show, .jsst-q-visitor');
                    for (var jsstI = 0; jsstI < jsstBoxes.length; jsstI++) {
                        jsstBoxes[jsstI].addEventListener('change', function (jsstEvent) {
                            var jsstRow = jsstEvent.currentTarget.closest('.jsst-q-row');
                            if (!jsstRow) { return; }
                            var jsstUser = jsstRow.querySelector('.jsst-q-show');
                            var jsstVis = jsstRow.querySelector('.jsst-q-visitor');
                            jsstRow.classList.toggle('jsst-q-off', !((jsstUser && jsstUser.checked) || (jsstVis && jsstVis.checked)));
                        });
                    }
                })();
                (function () {
                    var jsstBox = document.getElementById('jsst-logic');
                    if (!jsstBox) { return; }
                    var jsstChoices = <?php echo wp_json_encode($jsst_choices); ?>;
                    var jsstAny = <?php echo wp_json_encode(esc_html(__('— any answer —', 'js-support-ticket'))); ?>;
                    var jsstLabel = <?php echo wp_json_encode(esc_attr(__('The answer that brings it out', 'js-support-ticket'))); ?>;
                    var jsstAndWord = <?php echo wp_json_encode(esc_html(__('and', 'js-support-ticket'))); ?>;
                    var jsstOrWord = <?php echo wp_json_encode(esc_html(__('or', 'js-support-ticket'))); ?>;
                    var jsstIdFields = <?php echo wp_json_encode(method_exists('JSSTforms', 'idBacked') ? JSSTforms::idBacked() : array()); ?>;
                    /* Lists this site has not filled yet: a note and a link, not a
                       box for typing an id. (6 Oct 2026) */
                    var jsstEmpty = <?php echo wp_json_encode(isset(jssupportticket::$jsst_data['fmempty']) ? jssupportticket::$jsst_data['fmempty'] : array()); ?>;
                    var jsstIdText = <?php echo wp_json_encode(__('This question is stored as an id, so a name typed here will never match. Use "has been answered" unless you know the id.', 'js-support-ticket')); ?>;

                    /* The answer box and the id warning, shown or hidden for what
                       the row now says. Both are decided from the row itself rather
                       than tracked, so a row cloned into a new group arrives in the
                       right state without anything having to remember it. */
                    function jsstPaintCond(jsstCond) {
                        var jsstWhenEl = jsstCond.querySelector('.jsst-logic-when');
                        var jsstOpEl = jsstCond.querySelector('.jsst-logic-op');
                        var jsstCell = jsstCond.querySelector('.jsst-logic-value');
                        var jsstNote = jsstCond.querySelector('.jsst-logic-idnote');
                        if (!jsstWhenEl || !jsstOpEl) { return; }
                        var jsstWhen = jsstWhenEl.value;
                        var jsstOp = jsstOpEl.value;
                        var jsstNoValue = (jsstOp === 'filled' || jsstOp === 'empty');
                        var jsstHasList = !!(jsstChoices[jsstWhen] && jsstChoices[jsstWhen].length);
                        var jsstNone = (!jsstNoValue && !jsstHasList && jsstEmpty[jsstWhen]) ? jsstEmpty[jsstWhen] : null;
                        if (jsstCell) { jsstCell.style.display = (jsstNoValue || jsstNone) ? 'none' : ''; }
                        if (jsstNote) {
                            jsstNote.textContent = '';
                            if (jsstNone) {
                                jsstNote.appendChild(document.createTextNode(jsstNone.text + ' '));
                                var jsstGo = document.createElement('a');
                                jsstGo.href = jsstNone.url;
                                jsstGo.target = '_blank';
                                jsstGo.textContent = jsstNone.link;
                                jsstNote.appendChild(jsstGo);
                                jsstNote.style.display = '';
                            } else {
                                jsstNote.textContent = jsstIdText;
                                var jsstWarn = (!jsstNoValue && jsstWhen !== '' && !jsstHasList
                                    && jsstIdFields.indexOf(jsstWhen) !== -1);
                                jsstNote.style.display = jsstWarn ? '' : 'none';
                            }
                        }
                    }

                    /* logic[3][groups][1][2][when] -> the three numbers in it. */
                    function jsstParts(jsstName) {
                        var jsstM = /^logic\[(\d+)\]\[groups\]\[(\d+)\]\[(\d+)\]/.exec(jsstName || '');
                        return jsstM ? { r: jsstM[1], g: jsstM[2], c: jsstM[3] } : null;
                    }
                    /* One past the highest index already used here, so a row removed
                       from the middle never lends its index to a new one. */
                    function jsstNext(jsstScope, jsstWhich) {
                        var jsstAll = jsstScope.querySelectorAll('[name^="logic["]');
                        var jsstTop = -1;
                        for (var jsstI = 0; jsstI < jsstAll.length; jsstI++) {
                            var jsstP = jsstParts(jsstAll[jsstI].name);
                            if (jsstP) { jsstTop = Math.max(jsstTop, parseInt(jsstP[jsstWhich], 10)); }
                        }
                        return jsstTop + 1;
                    }
                    function jsstRenumber(jsstNode, jsstR, jsstG, jsstC) {
                        var jsstAll = jsstNode.querySelectorAll('[name^="logic["]');
                        for (var jsstI = 0; jsstI < jsstAll.length; jsstI++) {
                            jsstAll[jsstI].name = jsstAll[jsstI].name.replace(
                                /^logic\[\d+\]\[groups\]\[\d+\]\[\d+\]/,
                                'logic[' + jsstR + '][groups][' + jsstG + '][' + jsstC + ']');
                        }
                    }
                    function jsstBlankCond(jsstFrom, jsstR, jsstG, jsstC) {
                        var jsstNew = jsstFrom.cloneNode(true);
                        jsstRenumber(jsstNew, jsstR, jsstG, jsstC);
                        var jsstWhen = jsstNew.querySelector('.jsst-logic-when');
                        if (jsstWhen) { jsstWhen.selectedIndex = 0; }
                        var jsstOp = jsstNew.querySelector('.jsst-logic-op');
                        if (jsstOp) { jsstOp.selectedIndex = 0; }
                        jsstSwapValue(jsstNew, '');
                        jsstPaintCond(jsstNew);
                        return jsstNew;
                    }
                    function jsstJoin(jsstWord, jsstKind) {
                        var jsstEl = document.createElement('div');
                        jsstEl.className = 'jsst-logic-join jsst-logic-' + jsstKind;
                        var jsstSpan = document.createElement('span');
                        jsstSpan.textContent = jsstWord;
                        jsstEl.appendChild(jsstSpan);
                        return jsstEl;
                    }
                    /* The answer control for whatever question the row now watches. */
                    function jsstSwapValue(jsstCond, jsstWhen) {
                        var jsstCell = jsstCond.querySelector('.jsst-logic-value');
                        if (!jsstCell) { return; }
                        var jsstOld = jsstCell.querySelector('[name]');
                        var jsstName = jsstOld ? jsstOld.name : '';
                        var jsstWas = jsstOld ? jsstOld.value : '';
                        var jsstList = jsstChoices[jsstWhen] || null;
                        var jsstNew;
                        if (jsstList && jsstList.length) {
                            jsstNew = document.createElement('select');
                            var jsstBlank = document.createElement('option');
                            jsstBlank.value = '';
                            jsstBlank.textContent = jsstAny;
                            jsstNew.appendChild(jsstBlank);
                            for (var jsstI = 0; jsstI < jsstList.length; jsstI++) {
                                var jsstOpt = document.createElement('option');
                                jsstOpt.value = jsstList[jsstI].v;
                                jsstOpt.textContent = jsstList[jsstI].t;
                                jsstNew.appendChild(jsstOpt);
                            }
                        } else {
                            jsstNew = document.createElement('input');
                            jsstNew.type = 'text';
                        }
                        jsstNew.name = jsstName;
                        jsstNew.setAttribute('aria-label', jsstLabel);
                        jsstNew.value = jsstWas;
                        jsstCell.innerHTML = '';
                        jsstCell.appendChild(jsstNew);
                    }

                    Array.prototype.forEach.call(jsstBox.querySelectorAll('.jsst-logic-cond'), jsstPaintCond);

                    jsstBox.addEventListener('change', function (jsstEvent) {
                        var jsstEl = jsstEvent.target;
                        if (!jsstEl.classList) { return; }
                        var jsstCond = jsstEl.closest('.jsst-logic-cond');
                        if (!jsstCond) { return; }
                        if (jsstEl.classList.contains('jsst-logic-when')) {
                            jsstSwapValue(jsstCond, jsstEl.value);
                            jsstPaintCond(jsstCond);
                        } else if (jsstEl.classList.contains('jsst-logic-op')) {
                            jsstPaintCond(jsstCond);
                        }
                    });

                    jsstBox.addEventListener('click', function (jsstEvent) {
                        var jsstBtn = jsstEvent.target.closest('button');
                        if (!jsstBtn) { return; }
                        var jsstRule = jsstBtn.closest('.jsst-logic-rule');
                        if (!jsstRule) { return; }
                        var jsstR = jsstRule.getAttribute('data-rule');

                        if (jsstBtn.classList.contains('jsst-logic-add-or')) {
                            jsstEvent.preventDefault();
                            var jsstGroup = jsstBtn.closest('.jsst-logic-group');
                            var jsstSeed = jsstGroup.querySelector('.jsst-logic-cond');
                            if (!jsstSeed) { return; }
                            var jsstG = jsstParts(jsstSeed.querySelector('[name]').name).g;
                            var jsstCond = jsstBlankCond(jsstSeed, jsstR, jsstG, jsstNext(jsstGroup, 'c'));
                            var jsstFoot = jsstGroup.querySelector('.jsst-logic-group-foot');
                            jsstGroup.insertBefore(jsstJoin(jsstOrWord, 'or'), jsstFoot);
                            jsstGroup.insertBefore(jsstCond, jsstFoot);
                            return;
                        }
                        if (jsstBtn.classList.contains('jsst-logic-add-and')) {
                            jsstEvent.preventDefault();
                            var jsstGroups = jsstRule.querySelector('.jsst-logic-groups');
                            var jsstLast = jsstGroups.querySelector('.jsst-logic-group');
                            if (!jsstLast) { return; }
                            var jsstNewG = jsstLast.cloneNode(true);
                            var jsstGi = jsstNext(jsstRule, 'g');
                            /* One condition in the new box: a box that arrives
                               holding a copy of everything in the one above it is a
                               box somebody has to empty before they can use it. */
                            var jsstKeep = jsstNewG.querySelectorAll('.jsst-logic-cond');
                            var jsstJoins = jsstNewG.querySelectorAll('.jsst-logic-join');
                            for (var jsstI = 1; jsstI < jsstKeep.length; jsstI++) { jsstKeep[jsstI].remove(); }
                            for (var jsstJ = 0; jsstJ < jsstJoins.length; jsstJ++) { jsstJoins[jsstJ].remove(); }
                            jsstNewG.setAttribute('data-group', jsstGi);
                            var jsstOne = jsstNewG.querySelector('.jsst-logic-cond');
                            var jsstFresh = jsstBlankCond(jsstOne, jsstR, jsstGi, 0);
                            jsstOne.parentNode.replaceChild(jsstFresh, jsstOne);
                            jsstGroups.appendChild(jsstJoin(jsstAndWord, 'and'));
                            jsstGroups.appendChild(jsstNewG);
                            return;
                        }
                        if (jsstBtn.classList.contains('jsst-logic-drop-cond')) {
                            jsstEvent.preventDefault();
                            var jsstThis = jsstBtn.closest('.jsst-logic-cond');
                            var jsstIn = jsstThis.closest('.jsst-logic-group');
                            /* The last condition in a box is emptied rather than
                               removed - a box with nothing in it is dropped by
                               cleanGroups() anyway, and leaving the row there keeps
                               somewhere to type the replacement. */
                            if (jsstIn.querySelectorAll('.jsst-logic-cond').length < 2) {
                                var jsstSel = jsstThis.querySelector('.jsst-logic-when');
                                if (jsstSel) { jsstSel.selectedIndex = 0; jsstSwapValue(jsstThis, ''); }
                                return;
                            }
                            var jsstPrev = jsstThis.previousElementSibling;
                            if (jsstPrev && jsstPrev.classList.contains('jsst-logic-join')) { jsstPrev.remove(); }
                            jsstThis.remove();
                            return;
                        }
                        if (jsstBtn.classList.contains('jsst-logic-drop-rule')) {
                            jsstEvent.preventDefault();
                            /* Clearing the question is what removes a rule, because
                               cleanLogic() keeps only rules that name one. Removing
                               the block outright would do the same thing and leave
                               nothing to undo it with. */
                            var jsstShow = jsstRule.querySelector('.jsst-logic-show');
                            if (jsstShow) { jsstShow.selectedIndex = 0; }
                            jsstRule.classList.add('jsst-logic-off');
                            return;
                        }
                    });

                    var jsstAdd = document.getElementById('jsst-logic-add-rule');
                    if (jsstAdd) {
                        jsstAdd.addEventListener('click', function (jsstEvent) {
                            jsstEvent.preventDefault();
                            var jsstRules = jsstBox.querySelectorAll('.jsst-logic-rule');
                            var jsstSeed = jsstRules[jsstRules.length - 1];
                            if (!jsstSeed) { return; }
                            var jsstTop = -1;
                            for (var jsstI = 0; jsstI < jsstRules.length; jsstI++) {
                                jsstTop = Math.max(jsstTop, parseInt(jsstRules[jsstI].getAttribute('data-rule'), 10));
                            }
                            var jsstRi = jsstTop + 1;
                            var jsstNew = jsstSeed.cloneNode(true);
                            jsstNew.setAttribute('data-rule', jsstRi);
                            jsstNew.classList.remove('jsst-logic-off');
                            var jsstConds = jsstNew.querySelectorAll('.jsst-logic-cond');
                            var jsstJoins2 = jsstNew.querySelectorAll('.jsst-logic-join');
                            for (var jsstJ = 1; jsstJ < jsstConds.length; jsstJ++) { jsstConds[jsstJ].remove(); }
                            for (var jsstK = 0; jsstK < jsstJoins2.length; jsstK++) { jsstJoins2[jsstK].remove(); }
                            var jsstGroupsBox = jsstNew.querySelectorAll('.jsst-logic-group');
                            for (var jsstL = 1; jsstL < jsstGroupsBox.length; jsstL++) { jsstGroupsBox[jsstL].remove(); }
                            var jsstShow2 = jsstNew.querySelector('.jsst-logic-show');
                            if (jsstShow2) {
                                jsstShow2.name = 'logic[' + jsstRi + '][show]';
                                jsstShow2.selectedIndex = 0;
                            }
                            var jsstOne2 = jsstNew.querySelector('.jsst-logic-cond');
                            if (jsstOne2) {
                                jsstOne2.parentNode.replaceChild(jsstBlankCond(jsstOne2, jsstRi, 0, 0), jsstOne2);
                            }
                            jsstBox.appendChild(jsstNew);
                            var jsstFocus = jsstNew.querySelector('.jsst-logic-show');
                            if (jsstFocus) { jsstFocus.focus(); }
                        });
                    }
                })();
                </script>

                <?php /* One button for the whole form, and it needs to say so: the order
                   boxes and the four columns of switches are all part of this one
                   submission, and nothing above here writes anything on its own. It had
                   neither a surface nor room under it, so it read as a stray control
                   belonging to the panels it was sitting on. (Roadmap 5.0-FORM-01) */ ?>
                <div class="jsst-actions jsst-actions-bar jsst-fq-savebar" data-jsst-tab="questions conditions">
                    <button type="submit" class="jsst-btn jsst-btn-primary"><?php echo esc_html(__('Save the form', 'js-support-ticket')); ?></button>
                    <span class="jsst-actions-note"><?php echo esc_html(__('Saves the questions and the conditional questions together. The version before is kept under Versions, so it can be undone.', 'js-support-ticket')); ?></span>
                    <span class="jsst-fq-dirty" hidden><?php echo esc_html(__('Unsaved changes', 'js-support-ticket')); ?></span>
                </div>
            </form>

            <div class="jsst-cards jsst-fq-panel jsst-fq-grid" data-jsst-tab="preview">
                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('What the customer sees', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('The questions a signed-in customer is asked, in order. The real form uses your site\'s theme.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body">
                        <?php
                        $jsst_shown = 0;
                        foreach ($jsst_fields AS $jsst_name => $jsst_field) {
                            if (!$jsst_field['published'] || $jsst_field['adminonly']) { continue; }
                            $jsst_shown++; ?>
                            <div class="jsst-field">
                                <label>
                                    <?php echo esc_html($jsst_field['title']); ?>
                                    <?php if ($jsst_field['required']) { ?><span aria-hidden="true">&nbsp;*</span><?php } ?>
                                </label>
                                <?php if (in_array($jsst_field['type'], array('textarea', 'internalnotetitle'), true)) { ?>
                                    <textarea class="inputbox" rows="3" disabled="disabled" placeholder="<?php echo esc_attr($jsst_field['placeholder']); ?>"></textarea>
                                <?php } elseif (in_array($jsst_field['type'], array('combo', 'multiple', 'depandant_field'), true)) { ?>
                                    <select class="inputbox" disabled="disabled">
                                        <?php if ($jsst_field['options']) {
                                            foreach ($jsst_field['options'] AS $jsst_option) { ?>
                                                <option><?php echo esc_html($jsst_option); ?></option>
                                            <?php }
                                        } else { ?>
                                            <option><?php echo esc_html(__('— the site\'s own list —', 'js-support-ticket')); ?></option>
                                        <?php } ?>
                                    </select>
                                <?php } elseif (in_array($jsst_field['type'], array('checkbox', 'radio', 'termsandconditions'), true)) { ?>
                                    <?php if ($jsst_field['options']) {
                                        foreach ($jsst_field['options'] AS $jsst_option) { ?>
                                            <label class="jsst-check"><input type="<?php echo ($jsst_field['type'] === 'radio') ? 'radio' : 'checkbox'; ?>" disabled="disabled" /> <span><?php echo esc_html($jsst_option); ?></span></label>
                                        <?php }
                                    } else { ?>
                                        <label class="jsst-check"><input type="checkbox" disabled="disabled" /> <span><?php echo esc_html($jsst_field['title']); ?></span></label>
                                    <?php } ?>
                                <?php } elseif ($jsst_field['type'] === 'file') { ?>
                                    <input type="file" class="inputbox" disabled="disabled" />
                                <?php } elseif ($jsst_field['type'] === 'date') { ?>
                                    <input type="date" class="inputbox" disabled="disabled" />
                                <?php } else { ?>
                                    <input type="<?php echo ($jsst_field['type'] === 'email') ? 'email' : 'text'; ?>" class="inputbox" disabled="disabled" placeholder="<?php echo esc_attr($jsst_field['placeholder']); ?>" />
                                <?php } ?>
                                <?php if ($jsst_field['description'] !== '') { ?>
                                    <p class="jsst-field-note"><?php echo esc_html($jsst_field['description']); ?></p>
                                <?php } ?>
                                <?php if (isset($jsst_logic[$jsst_name])) {
                                    $jsst_rule = $jsst_logic[$jsst_name]; ?>
                                    <p class="jsst-field-note"><?php
                                        /* translators: 1: another question's name, 2: a comparison such as "is", 3: a value. */
                                        echo esc_html(sprintf(__('Only asked when %1$s %2$s %3$s.', 'js-support-ticket'),
                                            isset($jsst_fields[$jsst_rule['when']]) ? $jsst_fields[$jsst_rule['when']]['title'] : $jsst_rule['when'],
                                            isset($jsst_operators[$jsst_rule['op']]) ? $jsst_operators[$jsst_rule['op']] : $jsst_rule['op'],
                                            $jsst_rule['value'])); ?></p>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php if ($jsst_shown === 0) { ?>
                            <div class="jsst-empty">
                                <p class="jsst-empty-title"><?php echo esc_html(__('This form asks a customer nothing at all.', 'js-support-ticket')); ?></p>
                            </div>
                        <?php } ?>
                        <p class="jsst-field-note">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=addticket&formid=' . $jsst_formid)); ?>"><?php echo esc_html(__('Open the real form', 'js-support-ticket')); ?></a>
                        </p>
                    </div>
                </div>

                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('How often each question is answered', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php
                            /* translators: %d is a number of tickets. */
                            echo esc_html(sprintf(__('Read from the last %d tickets this form produced. A question almost nobody answers is either badly worded or not worth asking.', 'js-support-ticket'),
                                (int) (isset($jsst_stats['looked']) ? $jsst_stats['looked'] : 0))); ?></p>
                    </div>
                    <div class="jsst-card-body jsst-card-flush">
                        <?php if (empty($jsst_stats['looked'])) { ?>
                            <div class="jsst-empty">
                                <p class="jsst-empty-title"><?php echo esc_html(__('No tickets have come in on this form yet.', 'js-support-ticket')); ?></p>
                            </div>
                        <?php } else { ?>
                            <div class="jsst-table-wrap">
                                <table class="jsst-table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><?php echo esc_html(__('Question', 'js-support-ticket')); ?></th>
                                            <th scope="col"><?php echo esc_html(__('Answered', 'js-support-ticket')); ?></th>
                                            <th scope="col"><?php echo esc_html(__('Of those asked', 'js-support-ticket')); ?><span class="jsst-th-note"><?php echo esc_html(__('Left blank is not the same as never shown', 'js-support-ticket')); ?></span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ((array) $jsst_stats['fields'] AS $jsst_name => $jsst_stat) {
                                        if (!$jsst_stat['shown']) { continue; } ?>
                                        <tr>
                                            <td>
                                                <span class="jsst-table-name"><?php echo esc_html($jsst_stat['title']); ?></span>
                                                <?php if ($jsst_stat['required']) { ?>
                                                    <span class="jsst-table-sub"><?php echo esc_html(__('needed', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                            </td>
                                            <td class="jsst-num"><?php echo esc_html($jsst_stat['answered']); ?></td>
                                            <td>
                                                <?php if ($jsst_stat['share'] === null) { ?>
                                                    <span class="jsst-table-sub"><?php echo esc_html(__('—', 'js-support-ticket')); ?></span>
                                                <?php } else {
                                                    $jsst_pill = ($jsst_stat['share'] >= 60) ? 'ok' : (($jsst_stat['share'] >= 20) ? 'warn' : 'off'); ?>
                                                    <span class="jsst-pill jsst-pill-<?php echo esc_attr($jsst_pill); ?>"><span class="jsst-dot"></span><?php echo esc_html($jsst_stat['share'] . '%'); ?></span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if (!empty($jsst_stats['months'])) { ?>
                        <div class="jsst-card-foot">
                            <p class="jsst-groupheading"><?php echo esc_html(__('Tickets raised on it', 'js-support-ticket')); ?></p>
                            <div class="jsst-chips">
                                <?php foreach ($jsst_stats['months'] AS $jsst_month => $jsst_count) { ?>
                                    <span class="jsst-chip"><?php echo esc_html($jsst_month . ' — ' . $jsst_count); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php /* Copying between forms needs two forms, so the whole panel is
                     drawn only where there can be. Its own "there is only one form"
                     state is kept for a desk that has the add-on and has not made a
                     second one yet. (Roadmap 6.5-FORM-02) */
            if ($jsst_many) { ?>
            <div class="jsst-cards jsst-fq-panel jsst-fq-grid" data-jsst-tab="history">
                <div class="jsst-card jsst-card-only">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Take the questions from another form', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('Copies the questions somebody added to another form onto this one — the ones every form already has are left alone, and a question this form already asks under the same name is kept as it is rather than overwritten.', 'js-support-ticket')); ?></p>
                    </div>
                    <div class="jsst-card-body">
                        <?php if (count($jsst_forms) < 2) { ?>
                            <div class="jsst-empty">
                                <p class="jsst-empty-title"><?php echo esc_html(__('There is only one form.', 'js-support-ticket')); ?></p>
                                <p class="jsst-empty-text"><?php echo esc_html(__('The Multiform screen is where another one is added; this becomes useful the moment there are two.', 'js-support-ticket')); ?></p>
                            </div>
                        <?php } else { ?>
                            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_action); ?>">
                                <input type="hidden" name="fmcopy" value="1" />
                                <input type="hidden" name="formid" value="<?php echo esc_attr($jsst_formid); ?>" />
                                <div class="jsst-fields">
                                    <div class="jsst-field">
                                        <label for="copyfrom"><?php echo esc_html(__('Copy from', 'js-support-ticket')); ?></label>
                                        <select name="copyfrom" id="copyfrom" class="inputbox">
                                            <?php foreach ($jsst_forms AS $jsst_row) {
                                                if ((int) $jsst_row['id'] === $jsst_formid) { continue; } ?>
                                                <option value="<?php echo esc_attr($jsst_row['id']); ?>"><?php echo esc_html($jsst_row['title']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="jsst-actions">
                                    <button type="submit" class="button"><?php echo esc_html(__('Copy its questions here', 'js-support-ticket')); ?></button>
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

            </div>

            <script>
            (function(){
                var tabs = document.querySelectorAll('.jsst-fq-tab');
                if (!tabs.length) { return; }
                var panels = document.querySelectorAll('[data-jsst-tab]');
                /* Read off the tabs that were actually rendered: the copy tab is
                   only drawn on a desk that can have a second form, and a hardcoded
                   list would make #history hide every panel where it is absent. */
                var names = Array.prototype.map.call(tabs, function(t){ return t.getAttribute('data-tab'); });
                function show(name, push) {
                    if (names.indexOf(name) < 0) { name = 'questions'; }
                    tabs.forEach(function(t){
                        var on = t.getAttribute('data-tab') === name;
                        t.classList.toggle('is-active', on);
                        t.setAttribute('aria-selected', on ? 'true' : 'false');
                    });
                    panels.forEach(function(p){
                        p.hidden = (' ' + p.getAttribute('data-jsst-tab') + ' ').indexOf(' ' + name + ' ') < 0;
                    });
                    if (push && history.replaceState) { history.replaceState(null, '', '#' + name); }
                }
                tabs.forEach(function(t){
                    t.addEventListener('click', function(e){ e.preventDefault(); show(t.getAttribute('data-tab'), true); });
                });
                show((location.hash || '').replace('#', ''), false);
                var saved = document.querySelector('.jsst-fq-justsaved');
                if (saved) { show('questions', false); saved.scrollIntoView({block: 'center'}); }

                /* Reorder by dragging the handle. The number boxes are still the
                   field that is saved, and are renumbered 1, 2, 3... after every
                   move, so what is posted is exactly the order on the screen.
                   Typing a number still works for anybody who prefers it: the
                   row moves to its place when the box is left. */
                if (window.jQuery) jQuery(function(){
                    if (!jQuery.fn.sortable) { return; }
                    var $body = jQuery('.jsst-fq-table tbody');
                    var renumber = function(){
                        $body.children('tr').each(function(i){ jQuery(this).find('input.jsst-order').val(i + 1); });
                    };
                    $body.sortable({
                        handle: '.jsst-fq-handle',
                        /* The handle is a <button> (so it can take focus for the
                           arrow keys), and Sortable's default cancel list includes
                           button - which silently refused every drag started on
                           it. Fields and selects still never start a drag. */
                        cancel: 'input, textarea, select, option',
                        axis: 'y',
                        cursor: 'grabbing',
                        placeholder: 'jsst-fq-placeholder',
                        helper: function(e, tr){
                            var $cells = tr.children();
                            var $h = tr.clone();
                            $h.children().each(function(i){ jQuery(this).width($cells.eq(i).outerWidth()); });
                            return $h;
                        },
                        start: function(e, ui){ ui.placeholder.html('<td colspan="' + ui.item.children().length + '"></td>'); },
                        update: function(){ renumber(); var d = document.querySelector('.jsst-fq-dirty'); if (d) { d.hidden = false; } }
                    });
                    $body.on('keydown', '.jsst-fq-handle', function(e){
                        if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') { return; }
                        e.preventDefault();
                        var $row = jQuery(this).closest('tr');
                        if (e.key === 'ArrowUp' && $row.prev('tr').length) { $row.prev('tr').before($row); }
                        else if (e.key === 'ArrowDown' && $row.next('tr').length) { $row.next('tr').after($row); }
                        else { return; }
                        renumber();
                        this.focus();
                        var d = document.querySelector('.jsst-fq-dirty'); if (d) { d.hidden = false; }
                    });
                });
                /* Say when something is waiting to be saved: the switches look
                   finished the moment they are clicked, and moving to another
                   tab would otherwise hide the only button that keeps them. */
                var form = document.querySelector('.jsst-fq-savebar') ? document.querySelector('.jsst-fq-savebar').closest('form') : null;
                var dirty = document.querySelector('.jsst-fq-dirty');
                if (form && dirty) {
                    var mark = function(){ dirty.hidden = false; };
                    form.addEventListener('change', mark);
                    form.addEventListener('input', mark);
                    form.addEventListener('submit', function(){ dirty.hidden = true; });
                }
            })();
            </script>
            <?php } ?>

        </div>
    </div>
</div>
