<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The diagnostic pages. (Roadmap 4.0-OPS-03)
 *
 * Arrived at two ways: from the menu by somebody browsing, and from the link on
 * an error message by somebody who has a problem right now. The second is the
 * one that matters, so a page named in the URL is opened and scrolled to and
 * every other page is left collapsed rather than making them read past nine
 * things that are not their problem.
 */
if (!class_exists('JSSTdocs')) {
    echo '<div class="notice notice-error"><p>' . esc_html(__('The diagnostic pages could not be loaded. Deactivate and reactivate JS Help Desk.', 'js-support-ticket')) . '</p></div>';
    return;
}
JSSTmessage::getMessage();

$jsst_groups = jssupportticket::$jsst_data['docgroups'];
$jsst_topic  = jssupportticket::$jsst_data['doctopic'];
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
                        <li><?php echo esc_html(__('When Something Goes Wrong','js-support-ticket')); ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('When Something Goes Wrong', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp">

            <div class="jsst-status-card">
                <div class="jsst-status-note"><?php echo esc_html(__('Each page below starts from a symptom somebody reported and lists what to check, in the order worth checking it. Where a step can be answered on a screen, the step links to it.', 'js-support-ticket')); ?></div>
            </div>

            <?php foreach ($jsst_groups AS $jsst_group => $jsst_pages) { ?>
                <div class="jsst-status-card">
                    <div class="jsst-status-title"><?php echo esc_html($jsst_group); ?></div>
                    <?php foreach ($jsst_pages AS $jsst_id => $jsst_page) {
                        /* Open when it is the one that was linked to, shut
                           otherwise. <details> rather than script: it survives
                           this admin's inline CSS reset and works with none. */
                        $jsst_open = ($jsst_topic === $jsst_id); ?>
                        <details class="jsst-doc" id="jsst-doc-<?php echo esc_attr($jsst_id); ?>"<?php echo $jsst_open ? ' open' : ''; ?>>
                            <summary class="jsst-doc-summary"><?php echo esc_html($jsst_page['title']); ?></summary>
                            <div class="jsst-status-note"><?php echo esc_html($jsst_page['symptom']); ?></div>
                            <table class="jsst-status-table">
                                <?php $jsst_step = 0;
                                foreach ($jsst_page['steps'] AS $jsst_stepdata) {
                                    $jsst_step++; ?>
                                    <tr>
                                        <td><?php echo esc_html($jsst_step); ?></td>
                                        <td>
                                            <?php echo esc_html($jsst_stepdata['do']); ?>
                                            <?php if (!empty($jsst_stepdata['link'])) { ?>
                                                <a class="jsst-doc-link" href="<?php echo esc_url($jsst_stepdata['link']); ?>"><?php echo esc_html(__('Open the screen', 'js-support-ticket')); ?></a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </details>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
