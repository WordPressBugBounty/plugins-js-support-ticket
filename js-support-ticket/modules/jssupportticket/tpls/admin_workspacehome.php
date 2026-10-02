<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The desk home, in wp-admin. (Roadmap 4.5-FE-02)
 *
 * Deliberately thin. The navigation strip and the six panels are printed by
 * JSSTnavigation, which is the whole point of the task: the front-end desk's
 * home is the same two calls, so the two workspaces cannot end up with
 * different homes by one of them being extended and the other forgotten. What
 * belongs to this file is the wp-admin chrome around them and nothing else.
 */
if (!class_exists('JSSTnavigation')) {
    echo esc_html(__('The workspace is not available.', 'js-support-ticket'));
    return;
}
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <?php JSSTsidemenu::render(); ?>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('My Desk', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php
            JSSTnavigation::renderNav();
            JSSTnavigation::renderHome();
            ?>
        </div>
    </div>
</div>
