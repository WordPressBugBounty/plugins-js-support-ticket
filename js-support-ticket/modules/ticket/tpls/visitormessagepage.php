<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
        JSSTmessage::getMessage();
        /*JSSTbreadcrumbs::getBreadcrumbs();*/
        include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
        <div class="jsst-visitor-message-wrapper" >
            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/jsst-support-icon.png'; ?>" />
            <span class="jsst-visitor-message" >
                <?php echo wp_kses(jssupportticket::$_config['visitor_message'], JSST_ALLOWED_TAGS)?>
            </span>
        </div>
<?php
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
