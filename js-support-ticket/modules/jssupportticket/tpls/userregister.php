<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
if (JSSTincluder::getObjectClass('user')->isguest() && jssupportticket::$_config['show_captcha_on_visitor_from_ticket'] == 1 && jssupportticket::$_config['captcha_selection'] == 1) {
    wp_enqueue_script( 'ticket-recaptcha', 'https://www.google.com/recaptcha/api.js' );
}
$jssupportticket_js ="
    function onSubmit(token) {
        document.getElementById('jsst_registration_form').submit();
    }
";
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if (JSSTincluder::getObjectClass('user')->isguest()) {
        // check to make sure user registration is enabled
        $is_enable = get_option('users_can_register');
        // only show the registration form if allowed
        if ($is_enable) {
            JSSTmessage::getMessage();
            include_once(JSST_PLUGIN_PATH . 'includes/header.php'); ?>

            <div class="js-ticket-add-form-wrapper">
                <?php jsst_show_error_messages();?> <!-- show any error messages after form submission -->
                <form id="jsst_registration_form" class="jsst_form" action="" method="POST">
                    <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Username','js-support-ticket')); ?> <span style="color:red">*</span>
                        </div>
                        <div class="js-ticket-from-field">
                            <input name="jsst_user_login" id="jsst_user_login" class="required js-ticket-form-field-input" type="text"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Email','js-support-ticket')); ?> <span style="color:red">*</span>
                        </div>
                        <div class="js-ticket-from-field">
                           <input name="jsst_user_email" id="jsst_user_email" class="required js-ticket-form-field-input" type="text"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('First Name','js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-from-field">
                           <input name="jsst_user_first" id="jsst_user_first" class="required js-ticket-form-field-input" type="text"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Last Name','js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-from-field">
                           <input name="jsst_user_last" id="jsst_user_last" class="required js-ticket-form-field-input" type="text"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Password','js-support-ticket')); ?> <span style="color:red">*</span>
                        </div>
                        <div class="js-ticket-from-field">
                            <input name="jsst_user_pass" id="password" class="required js-ticket-form-field-input" type="password"/>
                        </div>
                    </div>
                    <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                        <div class="js-ticket-from-field-title">
                            <?php echo esc_html(__('Repeat Password','js-support-ticket')); ?> <span style="color:red">*</span>
                        </div>
                        <div class="js-ticket-from-field">
                           <input name="jsst_user_pass_confirm" id="password_again" class="required js-ticket-form-field-input" type="password"/>
                        </div>
                    </div>

                    <?php
                    if(in_array('mailchimp',jssupportticket::$_active_addons)){
                        ?>
                        <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                            <div class="js-ticket-from-field">
                                <label class="js-ticket-subscribe">
                                    <input name="jsst_mailchimp_subscribe" id="jsst_mailchimp_subscribe" value="1" class="" type="checkbox"/>
                                    <?php echo esc_html(__('Subscribe to the newsletter','js-support-ticket')); ?>
                                </label>
                            </div>
                        </div>
                        <?php
                    }
                    $google_recaptcha_3 = false;
                    if (jssupportticket::$_config['captcha_on_registration'] == 1) { ?>
                        <div class="js-ticket-from-field-wrp js-ticket-from-field-wrp-full-width">
                            <div class="js-ticket-from-field-title">
                                <?php echo esc_html(__('Captcha', 'js-support-ticket')); ?>
                            </div>
                            <div class="js-ticket-from-field">
                                <?php
                                if (jssupportticket::$_config['captcha_selection'] == 1) { // Google recaptcha
                                    $error = null;
                                    if (jssupportticket::$_config['recaptcha_version'] == 1) {
                                        echo '<div class="g-recaptcha" data-sitekey="'.wp_kses_post(jssupportticket::$_config['recaptcha_publickey']).'"></div>';
                                    } else {
                                        $google_recaptcha_3 = true;
                                    }
                                } else { // own captcha
                                    $captcha = new JSSTcaptcha;
                                    echo wp_kses($captcha->getCaptchaForForm(), JSST_ALLOWED_TAGS);

                                } ?>
                            </div>
                        </div>
                        <?php
                    }
                    JSSTincluder::getJSModel('fieldordering')->getFieldsOrderingforForm(3);
                    foreach (jssupportticket::$_data['fieldordering'] as $field) {
                        JSSTincluder::getObjectClass('customfields')->formCustomFields($field);
                    } ?>
                    <input type="hidden" name="jsst_support_register_nonce" value="<?php echo esc_attr(wp_create_nonce('jsst-support-register-nonce')); ?>"/>
                    <div class="js-ticket-form-btn-wrp">
                        <?php
                        if($google_recaptcha_3 == true && JSSTincluder::getObjectClass('user')->isguest()){ // to handle case of google recpatcha version 3
                            echo wp_kses(JSSTformfield::button('save', esc_html(__('Register', 'js-support-ticket')), array('class' => 'js-ticket-save-button g-recaptcha', 'data-callback' => 'onSubmit', 'data-action' => 'submit', 'data-sitekey' => esc_attr(jssupportticket::$_config['recaptcha_publickey']))), JSST_ALLOWED_TAGS);
                        } else {
                            echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Register', 'js-support-ticket')), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS);
                        } ?>
                        <a href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'controlpanel')));?>" class="js-ticket-cancel-button"><?php echo esc_html(__('Cancel','js-support-ticket')); ?></a>
                    </div>
                </form>
            </div>
        <?php
        } else {
            JSSTlayout::getRegistrationDisabled();
        }
    }else{
            JSSTlayout::getYouAreLoggedIn();
    }
}
if(isset($google_recaptcha) && $google_recaptcha){
    wp_enqueue_script( 'ticket-recaptcha', 'https://www.google.com/recaptcha/api.js' );
}
?>
</div>
