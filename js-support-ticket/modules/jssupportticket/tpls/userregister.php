<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
// The verification provider registers its own script. (Roadmap 4.0-SEC-01)
$jsst_verification = null;
if (JSSTincluder::getObjectClass('user')->isguest() && jssupportticket::$_config['captcha_on_registration'] == 1) {
    $jsst_verification = JSSTincluder::getObjectClass('verification');
    $jsst_verification->scripts();
}
$jsst_jssupportticket_js ="
    jQuery(document).ready(function ($) {
        $.validate();
    });
    function onSubmit(token) {
        document.getElementById('jsst_registration_form').submit();
    }
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if (JSSTincluder::getObjectClass('user')->isguest()) {
        // check to make sure user registration is enabled
        $jsst_is_enable = get_option('users_can_register');
        // only show the registration form if allowed
        if ($jsst_is_enable) {
            JSSTmessage::getMessage();
            include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>

            <div class="js-ticket-add-form-wrapper">
                <?php jsst_show_error_messages();?> <!-- show any error messages after form submission -->
                <form id="jsst_registration_form" class="jsst_form" action="" method="POST">
                    <div class="js-ticket-from-field-wrp">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Username','js-support-ticket')); ?> <span style="color:red">*</span>
                        </div>
                        <div class="js-ticket-from-field">
                            <input name="jsst_user_login" id="jsst_user_login" class="required js-ticket-form-field-input" type="text" data-validation="required"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Email','js-support-ticket')); ?> <span style="color:red">*</span>
                        </div>
                        <div class="js-ticket-from-field">
                           <input name="jsst_user_email" id="jsst_user_email" class="required js-ticket-form-field-input" type="text" data-validation="required"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('First Name','js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-from-field">
                           <input name="jsst_user_first" id="jsst_user_first" class="required js-ticket-form-field-input" type="text"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Last Name','js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-from-field">
                           <input name="jsst_user_last" id="jsst_user_last" class="required js-ticket-form-field-input" type="text"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Password','js-support-ticket')); ?> <span style="color:red">*</span>
                        </div>
                        <div class="js-ticket-from-field">
                            <input name="jsst_user_pass" id="password" class="required js-ticket-form-field-input" type="password" data-validation="required"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Repeat Password','js-support-ticket')); ?> <span style="color:red">*</span>
                        </div>
                        <div class="js-ticket-from-field">
                           <input name="jsst_user_pass_confirm" id="password_again" class="required js-ticket-form-field-input" type="password" data-validation="required"/>
                        </div>
                    </div>

                    <?php
                    if(in_array('mailchimp',jssupportticket::$_active_addons)){
                        ?>
                        <div class="js-ticket-from-field-wrp">
                            <div class="js-ticket-from-field">
                                <label class="js-ticket-subscribe">
                                    <input name="jsst_mailchimp_subscribe" id="jsst_mailchimp_subscribe" value="1" class="" type="checkbox"/>
                                    <?php echo esc_html(__('Subscribe to the newsletter','js-support-ticket')); ?>
                                </label>
                            </div>
                        </div>
                        <?php
                    }
                    JSSTincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(3);
                    foreach (jssupportticket::$jsst_data['fieldordering'] as $jsst_field) {
                        JSSTincluder::getObjectClass('customfields')->formCustomFields($jsst_field);
                    }
                    $jsst_verification_markup = ($jsst_verification !== null) ? $jsst_verification->field('register') : '';
                    if ($jsst_verification_markup != '') { ?>
                        <div class="js-ticket-from-field-wrp<?php echo JSSTverification::hasVisibleField() ? '' : ' jsst-verification-quiet'; ?>">
                            <?php // The built-in check shows the visitor nothing, so a heading
                            // here would sit over an empty box. (Roadmap 4.0-SEC-01)
                            if (JSSTverification::hasVisibleField()) { ?>
                            <div class="js-ticket-from-field-title">
                                <?php echo esc_html(__('Security check', 'js-support-ticket')); ?>
                            </div>
                            <?php } ?>
                            <div class="js-ticket-from-field">
                                <?php echo wp_kses($jsst_verification_markup, JSST_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                        <?php
                    } ?>
                    <input type="hidden" name="jsst_support_register_nonce" value="<?php echo esc_attr(wp_create_nonce('jsst-support-register-nonce')); ?>"/>
                    <div class="js-ticket-form-btn-wrp">
                        <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Register', 'js-support-ticket')), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                        <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'controlpanel')));?>" class="js-ticket-cancel-button"><?php echo esc_html(__('Cancel','js-support-ticket')); ?></a>
                    </div>
                </form>
                <?php
                if ($jsst_verification !== null) {
                    echo $jsst_verification->recaptchaV3Script('jsst_registration_form', 'jsst_register'); // phpcs:ignore WordPress.Security.EscapeOutput -- inline script built from wp_json_encode'd values
                }
                ?>
            </div>
        <?php
        } else {
            JSSTlayout::getRegistrationDisabled();
        }
    }else{
            JSSTlayout::getYouAreLoggedIn();
    }
}
// The provider script is registered by JSSTverification::scripts() at the top of
// this template, so there is no second, unconditional reCAPTCHA enqueue here.
// (Roadmap 4.0-SEC-01)
?>
</div>
