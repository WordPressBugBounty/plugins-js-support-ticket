<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The forms a desk offers. (Roadmap 6.5-FORM-05)
 *
 * The register, and only the register. What one form asks - its questions, the
 * conditions between them, the preview, the versions and the importer - is its
 * own screen now (admin_formquestions.php), reached by choosing a form here.
 *
 * Core has one form and no way to make a second, so this lists the one it has;
 * the Customer Experience add-on is what turns it into a list worth the name.
 * Either way the route is the same: the list says what exists, and the work
 * happens on the screen behind it, which is how every other list in this admin
 * behaves.
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
/* Choosing a form opens its questions, which is a screen of its own now. */
$jsst_questions = admin_url('admin.php?page=multiform&jstlay=formquestions');
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
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php
        $jsst_many = in_array('multiform', jssupportticket::$_active_addons);
        JSSTlayout::adminPageHeader(array(
            'title'   => __('Forms', 'js-support-ticket'),
            'count'   => count($jsst_forms),
            'actions' => $jsst_many
                ? array(array('text' => __('Add a form', 'js-support-ticket'), 'url' => $jsst_newform, 'icon' => 'plus'))
                : array(),
        ));
        ?>
        <div id="jsstadmin-data-wrp">
            <p class="jsst-lede">
                <?php echo esc_html(__('A form is the set of questions somebody answers to raise a ticket. Choose one below to change what it asks; the rest of this screen is about which form a customer gets and when.', 'js-support-ticket')); ?>
            </p>


            <?php if (empty($jsst_forms)) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-body">
                        <?php JSSTlayout::adminEmpty(
                            __('This desk has no ticket form yet.', 'js-support-ticket'),
                            __('A form is the set of questions a customer answers to raise a ticket.', 'js-support-ticket'),
                            $jsst_many ? __('Add a form', 'js-support-ticket') : '',
                            $jsst_many ? $jsst_newform : ''
                        ); ?>
                    </div>
                </div>
            <?php } else { ?>
            <div class="jsst-card">
                <div class="jsst-card-body jsst-card-flush">
                    <div class="jsst-table-wrap">
                        <table class="jsst-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php echo esc_html(__('Form', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('For', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('Questions', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('Conditions', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('Tickets raised', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('Offered', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('&nbsp;', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_forms AS $jsst_row) { ?>
                                <tr class="<?php echo ((int) $jsst_row['id'] === $jsst_formid) ? 'jsst-row-on' : ''; ?>">
                                    <td>
                                        <span class="jsst-table-name"><?php echo esc_html($jsst_row['title']); ?></span>
                                        <?php if (!empty($jsst_row['is_default'])) { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html(__('the one a customer gets unless something else applies', 'js-support-ticket')); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td><span class="jsst-table-sub"><?php
                                        $jsst_deptname = '';
                                        foreach ($jsst_departments AS $jsst_dept) {
                                            if ((int) $jsst_dept->id === (int) $jsst_row['departmentid']) { $jsst_deptname = $jsst_dept->text; }
                                        }
                                        echo esc_html($jsst_deptname !== '' ? $jsst_deptname : __('every department', 'js-support-ticket')); ?></span></td>
                                    <td class="jsst-num"><?php echo esc_html($jsst_row['fields']); ?></td>
                                    <td class="jsst-num"><?php echo esc_html($jsst_row['conditions']); ?></td>
                                    <td class="jsst-num"><?php echo esc_html($jsst_row['tickets']); ?></td>
                                    <td><?php
                                        /* Whether a customer can be given this form at all. Said as a
                                           word rather than as the old screen's red or green dot: a dot
                                           means nothing to somebody opening this for the first time. */
                                        $jsst_on = !empty($jsst_row['status']); ?>
                                        <span class="jsst-pill <?php echo esc_attr($jsst_on ? 'jsst-pill-ok' : 'jsst-pill-warn'); ?>"><?php
                                            echo esc_html($jsst_on ? __('Yes', 'js-support-ticket') : __('No', 'js-support-ticket')); ?></span>
                                    </td>
                                    <td class="jsst-rowactions jsst-rowactions-wrap">
                                        <a href="<?php echo esc_url($jsst_questions . '&formid=' . (int) $jsst_row['id']); ?>"><?php echo esc_html(__('Edit questions', 'js-support-ticket')); ?></a>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=multiform&jstlay=addmultiform&jssupportticketid=' . (int) $jsst_row['id'])); ?>"><?php echo esc_html(__('Rename', 'js-support-ticket')); ?></a>
                                        <?php if (empty($jsst_row['is_default'])) { ?>
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=multiform&task=changedefault&action=jstask&multiformid=' . (int) $jsst_row['id'] . '&default=' . (int) $jsst_row['is_default']), 'change-default-' . (int) $jsst_row['id'])); ?>"><?php
                                                echo esc_html(__('Make default', 'js-support-ticket')); ?></a>
                                        <?php } ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=multiform&task=changestatus&action=jstask&multiformid=' . (int) $jsst_row['id']), 'change-status-' . (int) $jsst_row['id'])); ?>"><?php
                                            echo esc_html($jsst_on ? __('Stop offering', 'js-support-ticket') : __('Offer it', 'js-support-ticket')); ?></a>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=multiform&task=ordering&action=jstask&multiformid=' . (int) $jsst_row['id'] . '&order=up'), 'ordering')); ?>" title="<?php echo esc_attr(__('Move up', 'js-support-ticket')); ?>">&uarr;</a>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=multiform&task=ordering&action=jstask&multiformid=' . (int) $jsst_row['id'] . '&order=down'), 'ordering')); ?>" title="<?php echo esc_attr(__('Move down', 'js-support-ticket')); ?>">&darr;</a>
                                        <?php /* The default form is not offered a delete: it is the one
                                           a customer gets when nothing else applies, and a desk with no
                                           such form has no ticket form at all. */
                                        if (empty($jsst_row['is_default'])) { ?>
                                            <a class="jsst-danger" onclick="return confirm('<?php echo esc_js(__('Delete this form? Its questions go with it. Tickets already raised on it are kept.', 'js-support-ticket')); ?>');"
                                               href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=multiform&task=deletemultiform&action=jstask&multiformid=' . (int) $jsst_row['id']), 'delete-multiform-' . (int) $jsst_row['id'])); ?>"><?php
                                                echo esc_html(__('Delete', 'js-support-ticket')); ?></a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
