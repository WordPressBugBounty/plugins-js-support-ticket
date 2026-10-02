<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customers and companies, in wp-admin. (Roadmap 4.5-FE-02)
 *
 * Chrome only: the screen itself is customerspanel.php, which the portal
 * includes too.
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
            'title' => __('Customers', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php
            JSSTnavigation::renderNav();
            include(JSST_PLUGIN_PATH . 'modules/jssupportticket/tpls/customerspanel.php');
            ?>
        </div>
    </div>
</div>
