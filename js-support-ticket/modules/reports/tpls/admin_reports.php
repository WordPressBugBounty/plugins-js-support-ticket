<?php
if (!defined('ABSPATH')) die('Restricted Access');

/**
 * Reports — the four places to go. (Design system migration)
 *
 * Four icon tiles on a `js-admin-*-report-type-wrapper` vocabulary of its own,
 * one class per tile for four tiles that differ only in their picture. It is
 * the same landing shape `.jsst-hub` was added for on the Knowledge Base hub,
 * so it is that now, and the four PNGs go the way the Help page's went: they
 * were four pictures of a chart standing in for four different questions.
 *
 * The subtitle under each name is the part the old tiles did not have and the
 * reason this screen existed at all - "Agent Reports" does not say whether it
 * answers how fast somebody is or how much they carry.
 *
 * Two report screens the controller serves are deliberately NOT here:
 * `satisfactionreport`, which Feedback links to, and `stafftimereport`, which
 * nothing links to at all (see the note in the project log).
 */
$jsst_reports = array(
    array(
        'lay'  => 'overallreport',
        'name' => __('Overall Statistics', 'js-support-ticket'),
        'sub'  => __('Everything the desk did, as one picture.', 'js-support-ticket'),
    ),
    array(
        'lay'  => 'staffreport',
        'name' => __('Agent Reports', 'js-support-ticket'),
        'sub'  => __('What each agent carried, answered and closed.', 'js-support-ticket'),
    ),
    array(
        'lay'  => 'departmentreport',
        'name' => __('Department Reports', 'js-support-ticket'),
        'sub'  => __('The same figures, gathered by department.', 'js-support-ticket'),
    ),
    array(
        'lay'  => 'userreport',
        'name' => __('User Reports', 'js-support-ticket'),
        'sub'  => __('Who is asking, how often, and what happened to it.', 'js-support-ticket'),
    ),
);

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Reports', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <p class="jsst-lede">
                <?php echo esc_html(__('Four ways to read the same tickets. Every report can be narrowed to a date range once you are inside it.', 'js-support-ticket')); ?>
            </p>

            <div class="jsst-card">
                <div class="jsst-card-body">
                    <div class="jsst-hub">
                        <?php foreach ($jsst_reports AS $jsst_report) { ?>
                            <a class="jsst-hub-item" href="<?php echo esc_url(admin_url('admin.php?page=reports&jstlay=' . $jsst_report['lay'])); ?>">
                                <span class="jsst-hub-name"><?php echo esc_html($jsst_report['name']); ?></span>
                                <span class="jsst-hub-sub"><?php echo esc_html($jsst_report['sub']); ?></span>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
