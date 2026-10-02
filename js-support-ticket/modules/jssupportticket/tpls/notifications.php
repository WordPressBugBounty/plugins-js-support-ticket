<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The notification centre, in the portal. (Roadmap 4.5-UX-02, 4.5-FE-02)
 *
 * Chrome only, for the same reason as the wp-admin copy: the screen is
 * notificationspanel.php and there is one of it.
 *
 * This file did not exist until 6.5, and its absence is what made the agent
 * desk's own Notifications link answer "Page Not Found !!" - the route resolved,
 * the controller prepared the data, and the includer then looked for a template
 * that was never written and fell through to `missingaddon.php`. An agent works
 * on the portal, so the portal is precisely where this screen is needed.
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
            include(JSST_PLUGIN_PATH . 'modules/jssupportticket/tpls/notificationspanel.php');
            ?>
        </div>
        <?php
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
