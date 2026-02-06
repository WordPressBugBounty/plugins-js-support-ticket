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
                $jsst_redirecturl = JSSTrequest::getVar('js_redirecturl','GET', jssupportticketphplib::JSST_safe_encoding(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket','jstlay'=>'controlpanel'))));
                $jsst_redirecturl = jssupportticketphplib::JSST_safe_decoding($jsst_redirecturl);

                if (JSSTincluder::getObjectClass('user')->isguest()) { 
                    
                    // 1. Define the arguments
                    $jsst_args = array(
                        'redirect' => $jsst_redirecturl,
                        'form_id' => 'loginform-custom',
                        'label_username' => esc_html(__('Username', 'js-support-ticket')),
                        'label_password' => esc_html(__('Password', 'js-support-ticket')),
                        'label_remember' => esc_html(__('keep me login', 'js-support-ticket')),
                        'label_log_in' => esc_html(__('Login', 'js-support-ticket')),
                        'remember' => true,
                        'echo' => false // IMPORTANT: Tell WP NOT to print the form yet
                    );

                    // 2. Get the form HTML as a variable
                    $jsst_form_html = wp_login_form($jsst_args);

                    // 3. Create your hidden field
                    $jsst_hidden_field = '<input type="hidden" name="jsst_login_source" value="js-support-ticket-login" />';

                    // 4. Manually inject the field before the closing </form> tag
                    // This guarantees it is INSIDE the form
                    $jsst_form_html = str_replace('</form>', $jsst_hidden_field . '</form>', $jsst_form_html);

                    // 5. Now output the modified HTML
                    echo wp_kses($jsst_form_html, JSST_ALLOWED_TAGS);

                } else { // user not Staff
                    JSSTlayout::getYouAreLoggedIn();
                }
                ?>
                <?php do_action('jsst_loginpage_sociallogin_layout'); ?>
            </div>
        </div>
<?php
} ?>
</div>
