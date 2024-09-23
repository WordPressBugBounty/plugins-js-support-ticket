<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
if (jssupportticket::$_config['offline'] == 2) {
    ?>
    <div class="jsst-main-up-wrapper">
        <?php JSSTmessage::getMessage(); ?>
        <?php include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>
        <h1 class="jsst-missing-addon-message" >
            <?php echo esc_html(__('Page Not Found !!', 'js-support-ticket')); ?>
        </h1>
    <?php
} else {
    JSSTlayout::getSystemOffline();
} ?>
