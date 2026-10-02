<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Customers and companies, in the portal. (Roadmap 4.5-FE-02)
 *
 * Chrome only, for the same reason as the wp-admin copy: the screen is
 * customerspanel.php and there is one of it.
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
            include(JSST_PLUGIN_PATH . 'modules/jssupportticket/tpls/customerspanel.php');
            ?>
        </div>
        <?php
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
