<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div id="js-tk-admin-wrapper">
    <div id="js-tk-cparea">
        <div id="jsst-main-wrapper" class="post-installation post-installtion-wellcome-page-main-wrp">
            <div class="post-installtion-wllecome-pgewrp">
                <div class="post-installtion-wllecome-pgelogowrp">
                    <img src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/postinstallation/complete-setting.png'; ?>"
                        alt="logo" />
                </div>
                <div class="post-installtion-wllecome-pgetxt">
                    <span class="post-installtion-wllecome-pgetitle"><?php echo esc_html(__('Welcome to JS Support Ticket', 'js-support-ticket')); ?></span>
                    <p class="post-installtion-wllecome-pgedisc"><?php echo esc_html(__('Lets Get Your Configurations Set Up', 'js-support-ticket')); ?></p>
                </div>
                <div class="post-installtion-wllecome-settingbtnwrp">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=postinstallation&jstlay=stepone")); ?>"
                        class="jsst-btn jsst-btn-primary">
                        <?php echo esc_html(__('Plugin Quick Settings', 'js-support-ticket')); ?>
                    </a>
                </div>
                <div class="post-installtion-wllecome-pgedassbrd-btnwrp">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket")); ?>"
                        class="jsst-btn jsst-btn-secondary">
                        <?php echo esc_html(__('Move to Dashboard', 'js-support-ticket')); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>