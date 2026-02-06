<?php
if (!defined('ABSPATH'))
    die('Restricted Access');


/**
 * Handle failed login attempts specifically for the JS Support Ticket login form.
 * * This function checks for the 'jsst_login_source' hidden field injected into 
 * the custom login form to ensure we only redirect and set messages when 
 * the failure happens on our specific plugin page.
 */
add_action('wp_login_failed', 'jssupportticket_login_failed', 10, 1);

function jssupportticket_login_failed($jsst_username) {
    // 1. Verify the source: Only run if our custom hidden field is present in the POST data
    if (isset($_POST['jsst_login_source']) && $_POST['jsst_login_source'] === 'js-support-ticket-login') {
        
        // 2. Security Check: Ensure the request didn't come from the standard WP-Admin or WP-Login
        $jsst_referrer = wp_get_referer();
        if ($jsst_referrer && !strstr($jsst_referrer, 'wp-login') && !strstr($jsst_referrer, 'wp-admin')) {
            
            // 3. Set the error message using the plugin's internal message system
            JSSTmessage::setMessage(esc_html(__('Username / password is incorrect', 'js-support-ticket')), 'error');
            
            // 4. Build the redirect URL back to the plugin login page
            $jsst_redirect_url = jssupportticket::makeUrl(array(
                'jstmod'    => 'jssupportticket', 
                'jstlay'    => 'login', 
                'jsstpageid' => jssupportticket::getPageid()
            ));
            
            // 5. Execute redirect and stop further script execution
            wp_safe_redirect($jsst_redirect_url);
            exit;
        }
    }
    
    /** * If 'jsst_login_source' is NOT found, we do nothing. 
     * This allows other plugins (WooCommerce, etc.) or 
     * WordPress Core to handle the login failure themselves.
     */
}

// Updates authentication to return an error when one field or both are blank
add_filter('authenticate', 'jsst_authenticate_username_password', 30, 3);

function jsst_authenticate_username_password($jsst_user, $jsst_username, $jsst_password)
{
    if (is_a($jsst_user, 'WP_User')) {
        return $jsst_user;
    }
    if (isset($_POST['wp-submit']) && (empty($_POST['pwd']) || empty($_POST['log']))) {
        return false;
    }
    return $jsst_user;
}

// ------------------- jsst registrationFrom request handler--------
// register a new user
function jsst_add_new_member()
{
    if (isset($_POST["jsst_user_login"]) && isset($_POST['jsst_support_register_nonce']) && wp_verify_nonce($_POST['jsst_support_register_nonce'], 'jsst-support-register-nonce')) {
        $jsst_user_login = sanitize_user($_POST["jsst_user_login"]);
        $jsst_user_email = sanitize_email($_POST["jsst_user_email"]);
        $jsst_user_first = sanitize_text_field($_POST["jsst_user_first"]);
        $jsst_user_last = sanitize_text_field($_POST["jsst_user_last"]);
        $jsst_user_pass = sanitize_text_field($_POST["jsst_user_pass"]);
        $jsst_pass_confirm = sanitize_text_field($_POST["jsst_user_pass_confirm"]);

        // this is required for username checks
        // require_once(ABSPATH . WPINC . '/registration.php');

        if (username_exists($jsst_user_login)) {
            // Username already registered
            jsst_errors()->add('username_unavailable', esc_html(__('Username already taken', 'js-support-ticket')));
        }
        if (!validate_username($jsst_user_login)) {
            // invalid username
            jsst_errors()->add('username_invalid', esc_html(__('Invalid username', 'js-support-ticket')));
        }
        if ($jsst_user_login == '') {
            // empty username
            jsst_errors()->add('username_empty', esc_html(__('Please enter a username', 'js-support-ticket')));
        }
        if (!is_email($jsst_user_email)) {
            //invalid email
            jsst_errors()->add('email_invalid', esc_html(__('Invalid email', 'js-support-ticket')));
        }
        if (email_exists($jsst_user_email)) {
            //Email address already registered
            jsst_errors()->add('email_used', esc_html(__('Email already registered', 'js-support-ticket')));
        }
        if ($jsst_user_pass == '') {
            // passwords do not match
            jsst_errors()->add('password_empty', esc_html(__('Please enter a password', 'js-support-ticket')));
        }
        if ($jsst_user_pass != $jsst_pass_confirm) {
            // passwords do not match
            jsst_errors()->add('password_mismatch', esc_html(__('Passwords do not match', 'js-support-ticket')));
        }
        if (jssupportticket::$_config['captcha_on_registration'] == 1) {
            if (jssupportticket::$_config['captcha_selection'] == 1) { // Google recaptcha
                $jsst_gresponse = jssupportticket::JSST_sanitizeData($_POST['g-recaptcha-response']); // JSST_sanitizeData() function uses wordpress santize functions
                $jsst_resp = JSSTGoogleRecaptchaHTTPPost(jssupportticket::$_config['recaptcha_privatekey'], $jsst_gresponse);
                if (!$jsst_resp) {
                    jsst_errors()->add('invalid_captcha', esc_html(__('Invalid captcha', 'js-support-ticket')));
                }
            } else { // own captcha
                $jsst_captcha = new JSSTcaptcha;
                $jsst_result = $jsst_captcha->checkCaptchaUserForm();
                if ($jsst_result != 1) {
                    jsst_errors()->add('invalid_captcha', esc_html(__('Invalid captcha', 'js-support-ticket')));
                }
            }
        }


        $jsst_errors = jsst_errors()->get_error_messages();

        // only create the user in if there are no errors
        if (empty($jsst_errors)) {
            // handled for useroptions addon
            $jsst_default_role = jssupportticket::$_config['wp_default_role'];
            if ($jsst_default_role == 0) {
                $jsst_default_role = 'subscriber';
            }

            $jsst_wperrors = register_new_user($jsst_user_login, $jsst_user_email);
            $jsst_new_user_id = "";
            if (!is_wp_error($jsst_wperrors)) {
                $jsst_new_user_id = $jsst_wperrors;
                //update_user_option( $jsst_new_user_id, 'default_password_nag', false, true );
                wp_set_password($jsst_user_pass, $jsst_new_user_id);
                update_user_option($jsst_new_user_id, 'first_name', $jsst_user_first, true);
                update_user_option($jsst_new_user_id, 'last_name', $jsst_user_last, true);
                // Update the user's role according to configuration
                wp_update_user(['ID'   => $jsst_new_user_id,'role' => $jsst_default_role,]);
                JSSTmessage::setMessage(esc_html(__("User has been successfully registered", 'js-support-ticket')), 'updated');
            } else {
                //Something's wrong
                jsst_errors()->add('email_invalid', $jsst_wperrors->get_error_message());
            }
            /*
            $jsst_new_user_id = wp_insert_user(array(
                'user_login' => $jsst_user_login,
                'user_pass' => $jsst_user_pass,
                'user_email' => $jsst_user_email,
                'first_name' => $jsst_user_first,
                'last_name' => $jsst_user_last,
                'user_registered' => date_i18n('Y-m-d H:i:s'),
                'role' => $jsst_default_role
                )
            );
            */
            if ($jsst_new_user_id) {

                $jsst_row = JSSTincluder::getJSTable('users');
                $jsst_data['id'] = '';
                $jsst_data['wpuid'] = $jsst_new_user_id;
                $jsst_data['display_name'] = $jsst_user_first . ' ' . $jsst_user_last;
                $jsst_data['name'] = $jsst_user_login;
                $jsst_data['user_email'] = $jsst_user_email;
                $jsst_data['issocial'] = 0;
                $jsst_data['socialid'] = null;
                $jsst_data['status'] = 1;
                $jsst_data['autogenerated'] = 0;
                $jsst_row->bind($jsst_data);
                $jsst_row->store();

                //mailchimp subscribe for newsletter
                if (in_array('mailchimp', jssupportticket::$_active_addons)) {
                    if (isset($_POST['jsst_mailchimp_subscribe']) && $_POST['jsst_mailchimp_subscribe'] == 1) {
                        $jsst_res = JSSTincluder::getJSModel('mailchimp')->subscribe($jsst_user_email, $jsst_user_first, $jsst_user_last);
                        if (!$jsst_res) {
                            JSSTmessage::setMessage(esc_html(__("Could not subscribe to the newsletter", 'js-support-ticket')), 'error');
                        } else {
                            $jsst_dboptin = JSSTincluder::getJSModel('configuration')->getConfigValue('mailchimp_double_optin');
                            if ($jsst_dboptin == 1) {
                                JSSTmessage::setMessage(esc_html(__("Please check confirmation email to complete your subscription for the newsletter", 'js-support-ticket')), 'updated');
                            } else {
                                JSSTmessage::setMessage(esc_html(__("You have successfully subscribed to the newsletter", 'js-support-ticket')), 'updated');
                            }
                        }
                    }
                }


                // send an email to the admin alerting them of the registration
                wp_new_user_notification($jsst_new_user_id);
                // log the new user in
                wp_set_current_user($jsst_new_user_id, $jsst_user_login);
                wp_set_auth_cookie($jsst_new_user_id);
                //do_action('wp_login', $jsst_user_login); // this code conflict with woocommerce and jetpack
                $jsst_url = jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel', 'jsstpageid' => jssupportticket::getPageid()));
                // send the newly created user to the home page after logging them in
                wp_safe_redirect($jsst_url);
                exit;
            }
        }
    }
}

add_action('init', 'jsst_add_new_member');

// used for tracking error messages
function jsst_errors()
{
    static $jsst_wp_error; // Will hold global variable safely
    return isset($jsst_wp_error) ? $jsst_wp_error : ($jsst_wp_error = new WP_Error(null, null, null));
}

// displays error messages from form submissions
function jsst_show_error_messages()
{
    if ($jsst_codes = jsst_errors()->get_error_codes()) {
        echo '<div class="jsst_errors">';
        // Loop error codes and display errors
        foreach ($jsst_codes as $jsst_code) {
            $jsst_message = jsst_errors()->get_error_message($jsst_code);
            echo '<span class="error"><strong>' . esc_html(__('Error','js-support-ticket')) . '</strong>: ' . wp_kses($jsst_message, JSST_ALLOWED_TAGS) . '</span><br/>';
        }
        echo '</div>';
    }
}

//to give signature option for admin
add_action('show_user_profile', 'jsst_add_admin_signature_field');
add_action('edit_user_profile', 'jsst_add_admin_signature_field');
function jsst_add_admin_signature_field($jsst_user)
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <h2><?php echo esc_html(__("JS Help Desk", 'js-support-ticket')); ?></h2>
    <table class="form-table">
        <tr>
            <th>
                <label id="jsstsignature"><?php echo esc_html(__("Signature", 'js-support-ticket')); ?></label>
            </th>
            <td>
                <?php wp_editor(get_user_meta($jsst_user->ID, 'jsst_signature', true), 'jsst_signature', array('media_buttons' => false)); ?>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'jsst_save_admin_signature_field');
add_action('edit_user_profile_update', 'jsst_save_admin_signature_field');
function jsst_save_admin_signature_field($jsst_uid)
{
    if (!is_numeric($jsst_uid) || !current_user_can('manage_options')) {
        return;
    }
    $jsst_signature = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['jsst_signature']);
    update_user_meta($jsst_uid, 'jsst_signature', $jsst_signature);
}

// ---------------Remove wp user ---------------

function jsst_remove_user($jsst_user_id)
{
    $jsst_js_class = JSSTIncluder::getObjectClass('user');
    $jsst_userid = $jsst_js_class->getUserIDByWPUid($jsst_user_id);

    if (isset($_POST['delete_option']) and $_POST['delete_option'] == 'delete') {

        $jsst_row = JSSTincluder::getJSTable('users');
        $jsst_data['id'] = $jsst_userid;
        $jsst_data['wpuid'] = 0;
        $jsst_data['status'] = 0;
        $jsst_row->bind($jsst_data);
        $jsst_row->store();

        // for future use to delete user relevent record call function below
        // $jsst_result = $jsst_js_class->deleteUserRecords($jsst_userid, true);
    }
}

add_action('delete_user', 'jsst_remove_user');

add_action('personal_options_update', 'jsst_update_user_profile');


function jsst_update_user_profile($jsst_user_id) {
    if(!is_numeric($jsst_user_id)){
        return false;
    }
    $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "users` WHERE id = " . esc_sql($jsst_user_id);
    $jsst_user = jssupportticket::$_db->get_row($jsst_query);

    $jsst_uid = "";
	$jsst_post_user_id = '';
	$jsst_id = '';
	$jsst_post_user_login='';
	$jsst_post_display_name='';
	$jsst_post_nickname='';
	
	if(isset($_POST['user_id'])) $jsst_post_user_id = jssupportticket::JSST_sanitizeData($_POST['user_id']); // JSST_sanitizeData() function uses wordpress santize functions
    if ($jsst_post_user_id == $jsst_user_id) {
        $jsst_query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` WHERE wpuid = " . esc_sql($jsst_user_id);
        $jsst_id = jssupportticket::$_db->get_var($jsst_query);
    }
	$jsst_name = "";
	if(isset($_POST['first_name'])) $jsst_name = jssupportticket::JSST_sanitizeData($_POST['first_name']); // JSST_sanitizeData() function uses wordpress santize functions
	if(isset($_POST['last_name'])) $jsst_name = $jsst_name. ' ' . jssupportticket::JSST_sanitizeData($_POST['last_name']); // JSST_sanitizeData() function uses wordpress santize functions
	if(isset($_POST['user_login'])) $jsst_post_user_login = jssupportticket::JSST_sanitizeData($_POST['user_login']); // JSST_sanitizeData() function uses wordpress santize functions
	if(isset($_POST['display_name'])) $jsst_post_display_name = jssupportticket::JSST_sanitizeData($_POST['display_name']); // JSST_sanitizeData() function uses wordpress santize functions
	if(isset($_POST['nickname'])) $jsst_post_nickname = jssupportticket::JSST_sanitizeData($_POST['nickname']); // JSST_sanitizeData() function uses wordpress santize functions
	
	if (isset($_POST['email'])) {
		$jsst_row = JSSTincluder::getJSTable('users');
		$jsst_data['id'] = $jsst_id;
		$jsst_data['wpuid'] = $jsst_user_id;
		$jsst_data['name'] = $jsst_name;
		$jsst_data['display_name'] = $jsst_name;
		$jsst_data['user_nicename'] = $jsst_post_nickname;
		$jsst_data['user_email'] = sanitize_email($_POST['email']);
		$jsst_data['issocial'] = 0;
		$jsst_data['socialid'] = null;
		$jsst_data['status'] = 1;
		$jsst_data['created'] = date_i18n('Y-m-d H:i:s');
		$jsst_row->bind($jsst_data);
		$jsst_row->store();
	}
}

add_action('edit_user_profile_update', 'jsst_update_user_profile');
add_action('user_register', 'jsst_update_user_profile'); // creating a new user


?>
