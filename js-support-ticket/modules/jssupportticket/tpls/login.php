<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    JSSTmessage::getMessage();
    // JSSTbreadcrumbs::getBreadcrumbs();
    include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>

        <div class="js-ticket-login-wrapper">
            <div  class="js-ticket-login">
<?php /*                <div class="login-heading"><?php echo esc_html(__('Login into your account', 'js-support-ticket')); ?></div> */ ?>
                <?php
                $redirecturl = JSSTrequest::getVar('js_redirecturl','GET', jssupportticketphplib::JSST_safe_encoding(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket','jstlay'=>'controlpanel'))));
                $redirecturl = jssupportticketphplib::JSST_safe_decoding($redirecturl);
                if (JSSTincluder::getObjectClass('user')->isguest()) { // Display WordPress login form:
                    $args = array(
                        'redirect' => $redirecturl,
                        'form_id' => 'loginform-custom',
                        'label_username' => esc_html(__('Username', 'js-support-ticket')),
                        'label_password' => esc_html(__('Password', 'js-support-ticket')),
                        'label_remember' => esc_html(__('keep me login', 'js-support-ticket')),
                        'label_log_in' => esc_html(__('Login', 'js-support-ticket')),
                        'remember' => true
                    );
                    wp_login_form($args);
                }else{ // user not Staff
                    JSSTlayout::getYouAreLoggedIn();
                }
                ?>
                <?php do_action('jsst_loginpage_sociallogin_layout'); ?>
            </div>
        </div>
<?php
} ?>
</div>
