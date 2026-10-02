<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The desk home, in the portal. (Roadmap 4.5-FE-02)
 *
 * The same two calls as the wp-admin home, inside this side's wrapper. An agent
 * who works in the portal and an agent who works in wp-admin are looking at one
 * screen rendered twice, which is the arrangement 4.5-FE-01 set out and the
 * only one in which the two desks cannot drift apart.
 */
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    JSSTmessage::getMessage();
    include_once(JSST_PLUGIN_PATH . 'includes/header.php');
    if (!class_exists('JSSTnavigation')) {
        echo esc_html(__('The workspace is not available.', 'js-support-ticket'));
    } else {
        ?>
        <div class="js-ticket-dashboard-container jsst-desk">
            <?php
            JSSTnavigation::renderNav();
            JSSTnavigation::renderHome();
            ?>
        </div>
        <?php
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
