<?php
if (!defined('ABSPATH'))
    die('Restricted Access');


// wrong username password handling
add_action('wp_login_failed', 'jssupportticket_login_failed', 10, 2);
function jssupportticket_login_failed($username)
{
    $referrer = wp_get_referer();
    if ($referrer && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
        if (isset($_POST['wp-submit'])) {
            JSSTmessage::setMessage(esc_html(__('Username / password is incorrect', 'js-support-ticket')), 'error');
            wp_redirect(jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'login', 'jsstpageid' => jssupportticket::getPageid())));
            exit;
        } else {
            return;
        }
    }
}

// Updates authentication to return an error when one field or both are blank
add_filter('authenticate', 'jsst_authenticate_username_password', 30, 3);

function jsst_authenticate_username_password($user, $username, $password)
{
    if (is_a($user, 'WP_User')) {
        return $user;
    }
    if (isset($_POST['wp-submit']) && (empty($_POST['pwd']) || empty($_POST['log']))) {
        return false;
    }
    return $user;
}

// ------------------- jsst registrationFrom request handler--------
// register a new user
function jsst_add_new_member()
{
    if (isset($_POST["jsst_user_login"]) && isset($_POST['jsst_support_register_nonce']) && wp_verify_nonce($_POST['jsst_support_register_nonce'], 'jsst-support-register-nonce')) {
        $user_login = sanitize_user($_POST["jsst_user_login"]);
        $user_email = sanitize_email($_POST["jsst_user_email"]);
        $user_first = sanitize_text_field($_POST["jsst_user_first"]);
        $user_last = sanitize_text_field($_POST["jsst_user_last"]);
        $user_pass = sanitize_text_field($_POST["jsst_user_pass"]);
        $pass_confirm = sanitize_text_field($_POST["jsst_user_pass_confirm"]);

        // this is required for username checks
        // require_once(ABSPATH . WPINC . '/registration.php');

        if (username_exists($user_login)) {
            // Username already registered
            jsst_errors()->add('username_unavailable', esc_html(__('Username already taken', 'js-support-ticket')));
        }
        if (!validate_username($user_login)) {
            // invalid username
            jsst_errors()->add('username_invalid', esc_html(__('Invalid username', 'js-support-ticket')));
        }
        if ($user_login == '') {
            // empty username
            jsst_errors()->add('username_empty', esc_html(__('Please enter a username', 'js-support-ticket')));
        }
        if (!is_email($user_email)) {
            //invalid email
            jsst_errors()->add('email_invalid', esc_html(__('Invalid email', 'js-support-ticket')));
        }
        if (email_exists($user_email)) {
            //Email address already registered
            jsst_errors()->add('email_used', esc_html(__('Email already registered', 'js-support-ticket')));
        }
        if ($user_pass == '') {
            // passwords do not match
            jsst_errors()->add('password_empty', esc_html(__('Please enter a password', 'js-support-ticket')));
        }
        if ($user_pass != $pass_confirm) {
            // passwords do not match
            jsst_errors()->add('password_mismatch', esc_html(__('Passwords do not match', 'js-support-ticket')));
        }
        if (jssupportticket::$_config['captcha_on_registration'] == 1) {
            if (jssupportticket::$_config['captcha_selection'] == 1) { // Google recaptcha
                $gresponse = jssupportticket::JSST_sanitizeData($_POST['g-recaptcha-response']); // JSST_sanitizeData() function uses wordpress santize functions
                $resp = JSSTGoogleRecaptchaHTTPPost(jssupportticket::$_config['recaptcha_privatekey'], $gresponse);
                if (!$resp) {
                    jsst_errors()->add('invalid_captcha', esc_html(__('Invalid captcha', 'js-support-ticket')));
                }
            } else { // own captcha
                $captcha = new JSSTcaptcha;
                $result = $captcha->checkCaptchaUserForm();
                if ($result != 1) {
                    jsst_errors()->add('invalid_captcha', esc_html(__('Invalid captcha', 'js-support-ticket')));
                }
            }
        }


        $errors = jsst_errors()->get_error_messages();

        // only create the user in if there are no errors
        if (empty($errors)) {
            // handled for useroptions addon
            $default_role = jssupportticket::$_config['wp_default_role'];
            if ($default_role == 0) {
                $default_role = 'subscriber';
            }

            $wperrors = register_new_user($user_login, $user_email);
            $new_user_id = "";
            if (!is_wp_error($wperrors)) {
                $new_user_id = $wperrors;
                //update_user_option( $new_user_id, 'default_password_nag', false, true );
                wp_set_password($user_pass, $new_user_id);
                update_user_option($new_user_id, 'first_name', $user_first, true);
                update_user_option($new_user_id, 'last_name', $user_last, true);
                JSSTmessage::setMessage(esc_html(__("User has been successfully registered", 'js-support-ticket')), 'updated');
            } else {
                //Something's wrong
                jsst_errors()->add('email_invalid', $wperrors->get_error_message());
            }
            /*
            $new_user_id = wp_insert_user(array(
                'user_login' => $user_login,
                'user_pass' => $user_pass,
                'user_email' => $user_email,
                'first_name' => $user_first,
                'last_name' => $user_last,
                'user_registered' => date_i18n('Y-m-d H:i:s'),
                'role' => $default_role
                )
            );
            */
            if ($new_user_id) {

                $row = JSSTincluder::getJSTable('users');
                $data['id'] = '';
                $data['wpuid'] = $new_user_id;
                $data['display_name'] = $user_first . ' ' . $user_last;
                $data['name'] = $user_login;
                $data['user_email'] = $user_email;
                $data['issocial'] = 0;
                $data['socialid'] = null;
                $data['status'] = 1;
                $data['autogenerated'] = 0;
                $row->bind($data);
                $row->store();

                //mailchimp subscribe for newsletter
                if (in_array('mailchimp', jssupportticket::$_active_addons)) {
                    if (isset($_POST['jsst_mailchimp_subscribe']) && $_POST['jsst_mailchimp_subscribe'] == 1) {
                        $res = JSSTincluder::getJSModel('mailchimp')->subscribe($user_email, $user_first, $user_last);
                        if (!$res) {
                            JSSTmessage::setMessage(esc_html(__("Could not subscribe to the newsletter", 'js-support-ticket')), 'error');
                        } else {
                            $dboptin = JSSTincluder::getJSModel('configuration')->getConfigValue('mailchimp_double_optin');
                            if ($dboptin == 1) {
                                JSSTmessage::setMessage(esc_html(__("Please check confirmation email to complete your subscription for the newsletter", 'js-support-ticket')), 'updated');
                            } else {
                                JSSTmessage::setMessage(esc_html(__("You have successfully subscribed to the newsletter", 'js-support-ticket')), 'updated');
                            }
                        }
                    }
                }


                // send an email to the admin alerting them of the registration
                wp_new_user_notification($new_user_id);
                // log the new user in
                wp_set_current_user($new_user_id, $user_login);
                wp_set_auth_cookie($new_user_id);
                //do_action('wp_login', $user_login); // this code conflict with woocommerce and jetpack
                $url = jssupportticket::makeUrl(array('jstmod' => 'jssupportticket', 'jstlay' => 'controlpanel', 'jsstpageid' => jssupportticket::getPageid()));
                // send the newly created user to the home page after logging them in
                wp_redirect($url);
                exit;
            }
        }
    }
}

add_action('init', 'jsst_add_new_member');

// used for tracking error messages
function jsst_errors()
{
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

// displays error messages from form submissions
function jsst_show_error_messages()
{
    if ($codes = jsst_errors()->get_error_codes()) {
        echo '<div class="jsst_errors">';
        // Loop error codes and display errors
        foreach ($codes as $code) {
            $message = jsst_errors()->get_error_message($code);
            echo '<span class="error"><strong>' . esc_html(__('Error','js-support-ticket')) . '</strong>: ' . wp_kses($message, JSST_ALLOWED_TAGS) . '</span><br/>';
        }
        echo '</div>';
    }
}

//to give signature option for admin
add_action('show_user_profile', 'jsst_add_admin_signature_field');
add_action('edit_user_profile', 'jsst_add_admin_signature_field');
function jsst_add_admin_signature_field($user)
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
                <?php wp_editor(get_user_meta($user->ID, 'jsst_signature', true), 'jsst_signature', array('media_buttons' => false)); ?>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'jsst_save_admin_signature_field');
add_action('edit_user_profile_update', 'jsst_save_admin_signature_field');
function jsst_save_admin_signature_field($uid)
{
    if (!is_numeric($uid) || !current_user_can('manage_options')) {
        return;
    }
    $signature = JSSTincluder::getJSModel('jssupportticket')->getSanitizedEditorData($_POST['jsst_signature']);
    update_user_meta($uid, 'jsst_signature', $signature);
}

// ---------------Remove wp user ---------------

function jsst_remove_user($user_id)
{
    $js_class = JSSTIncluder::getObjectClass('user');
    $userid = $js_class->getUserIDByWPUid($user_id);

    if (isset($_POST['delete_option']) and $_POST['delete_option'] == 'delete') {

        $row = JSSTincluder::getJSTable('users');
        $data['id'] = $userid;
        $data['wpuid'] = 0;
        $data['status'] = 0;
        $row->bind($data);
        $row->store();

        // for future use to delete user relevent record call function below
        // $result = $js_class->deleteUserRecords($userid, true);
    }
}

add_action('delete_user', 'jsst_remove_user');

add_action('personal_options_update', 'jsst_update_user_profile');


function jsst_update_user_profile($user_id)
{

    $query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "users` WHERE id = " . esc_sql($user_id);
    $user = jssupportticket::$_db->get_row($query);

    $uid = "";
	$post_user_id = '';
	$id = '';
	$post_user_login='';
	$post_display_name='';
	$post_nickname='';
	
	if(isset($_POST['user_id'])) $post_user_id = jssupportticket::JSST_sanitizeData($_POST['user_id']); // JSST_sanitizeData() function uses wordpress santize functions
    if ($post_user_id == $user_id) {
        $query = "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` WHERE wpuid = " . esc_sql($user_id);
        $id = jssupportticket::$_db->get_var($query);
    }
	$name = "";
	if(isset($_POST['first_name'])) $name = jssupportticket::JSST_sanitizeData($_POST['first_name']); // JSST_sanitizeData() function uses wordpress santize functions
	if(isset($_POST['last_name'])) $name = $name. ' ' . jssupportticket::JSST_sanitizeData($_POST['last_name']); // JSST_sanitizeData() function uses wordpress santize functions
	if(isset($_POST['user_login'])) $post_user_login = jssupportticket::JSST_sanitizeData($_POST['user_login']); // JSST_sanitizeData() function uses wordpress santize functions
	if(isset($_POST['display_name'])) $post_display_name = jssupportticket::JSST_sanitizeData($_POST['display_name']); // JSST_sanitizeData() function uses wordpress santize functions
	if(isset($_POST['nickname'])) $post_nickname = jssupportticket::JSST_sanitizeData($_POST['nickname']); // JSST_sanitizeData() function uses wordpress santize functions
	
	if (isset($_POST['email'])) {
		$row = JSSTincluder::getJSTable('users');
		$data['id'] = $id;
		$data['wpuid'] = $user_id;
		$data['name'] = $name;
		$data['display_name'] = $name;
		$data['user_nicename'] = $post_nickname;
		$data['user_email'] = sanitize_email($_POST['email']);
		$data['issocial'] = 0;
		$data['socialid'] = null;
		$data['status'] = 1;
		$data['created'] = date_i18n('Y-m-d H:i:s');
		$row->bind($data);
		$row->store();
	}
}

add_action('edit_user_profile_update', 'jsst_update_user_profile');
add_action('user_register', 'jsst_update_user_profile'); // creating a new user


?>
