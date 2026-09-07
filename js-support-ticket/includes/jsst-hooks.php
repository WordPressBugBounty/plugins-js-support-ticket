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
        // Registration is rate limited and verified through the same pluggable
        // provider as the ticket form. (Roadmap 4.0-SEC-01)
        if (!JSSTratelimit::check('register')) {
            jsst_errors()->add('rate_limited', JSSTratelimit::message());
        } elseif (jssupportticket::$_config['captcha_on_registration'] == 1) {
            $jsst_verification = JSSTincluder::getObjectClass('verification');
            if (!$jsst_verification->verify('register')) {
                jsst_errors()->add('invalid_captcha', $jsst_verification->lastError());
            }
        }


        $jsst_errors = jsst_errors()->get_error_messages();

        // only create the user in if there are no errors
        if (empty($jsst_errors)) {
            // handled for useroptions addon
            // Never trust the stored value: the row may predate the safety rule,
            // or have come from an import or a direct database edit. An unsafe
            // role becomes Subscriber. (Roadmap 4.0-CORE-07)
            $jsst_default_role = JSSTregistrationrole::configured();

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

/*
 * We deliberately do not answer users_have_additional_content. That filter is
 * the only switch on core's content radio, and answering yes on account of
 * tickets put the radio in front of an admin for a customer who owns no post,
 * page or media item at all — a question about content that does not exist,
 * asked right above the ticket question that does apply. Each control now
 * appears only when it has something to govern: core's when WordPress finds
 * posts or links, ours when the customer has tickets.
 *
 * Staying out has one cost, and jsst_delete_user_form() below pays it. Core is
 * binary (wp-admin/users.php:396-408): with no content it hides the radio and
 * prints "This user does not have any content.", which is untrue of someone
 * holding a support history. That sentence is corrected there. The hidden
 * delete_option=delete core posts in the same breath is harmless — core only
 * takes that branch once it has established there are no posts or links to
 * delete, and tickets are decided by our own field, never by that one.
 */

/*
 * The plugin asks about tickets separately from WordPress's own radio, which
 * governs posts and links only: "Delete all content" is not consent to lose a
 * support history, and that history is often the part a site is obliged to
 * keep once the account is gone. Rendered per user, like the core control, so
 * a bulk deletion can keep one customer's tickets and drop another's.
 */
add_action('delete_user_form', 'jsst_delete_user_form', 10, 2);

function jsst_delete_user_form($jsst_current_user, $jsst_user_ids)
{
    if (empty($jsst_user_ids) || !is_array($jsst_user_ids)) {
        return;
    }

    $jsst_js_class = JSSTincluder::getObjectClass('user');
    $jsst_rows = array();
    foreach ($jsst_user_ids AS $jsst_user_id) {
        $jsst_count = $jsst_js_class->getTicketCountByWPUid($jsst_user_id);
        if ($jsst_count > 0) {
            $jsst_rows[(int) $jsst_user_id] = $jsst_count;
        }
    }
    if (empty($jsst_rows)) { // nothing of ours is at stake, stay off the screen
        return;
    }
    ?>
    <h2 class="jsst-delete-user-heading"><?php echo esc_html(__('JS Help Desk', 'js-support-ticket')); ?></h2>
    <?php
    foreach ($jsst_rows AS $jsst_user_id => $jsst_count) {
        $jsst_user = get_userdata($jsst_user_id);
        $jsst_login = ($jsst_user === false) ? '' : $jsst_user->user_login;
        ?>
        <fieldset class="jsst-ticket-choice">
            <legend>
                <?php
                printf(
                    /* translators: 1: User login, 2: User ID, 3: Number of support tickets. */
                    esc_html(_n(
                        '%1$s (ID #%2$s) has %3$s support ticket. What should be done with it?',
                        '%1$s (ID #%2$s) has %3$s support tickets. What should be done with them?',
                        $jsst_count,
                        'js-support-ticket'
                    )),
                    '<strong>' . esc_html($jsst_login) . '</strong>',
                    (int) $jsst_user_id,
                    number_format_i18n($jsst_count)
                );
                ?>
            </legend>
            <p class="description"><?php echo esc_html(__('These options apply to support tickets, including their replies and attachments.', 'js-support-ticket')); ?></p>
            <ul>
                <li>
                    <input type="radio" id="jsst_keep_tickets_<?php echo esc_attr($jsst_user_id); ?>" name="jsst_delete_tickets[<?php echo esc_attr($jsst_user_id); ?>]" value="keep" checked="checked" />
                    <label for="jsst_keep_tickets_<?php echo esc_attr($jsst_user_id); ?>"><?php echo esc_html(_n('Keep the ticket and its history.', 'Keep the tickets and their history.', $jsst_count, 'js-support-ticket')); ?></label>
                </li>
                <li>
                    <input type="radio" id="jsst_delete_tickets_<?php echo esc_attr($jsst_user_id); ?>" name="jsst_delete_tickets[<?php echo esc_attr($jsst_user_id); ?>]" value="delete" />
                    <label for="jsst_delete_tickets_<?php echo esc_attr($jsst_user_id); ?>"><?php echo esc_html(_n('Delete the ticket, along with its replies and attachments.', 'Delete the tickets, along with their replies and attachments.', $jsst_count, 'js-support-ticket')); ?></label>
                </li>
            </ul>
        </fieldset>
        <?php
    }

    /*
     * Everything WordPress prints above -- its content question, or the line
     * it shows when a user owns no posts -- is core's own screen, and is left
     * exactly as core renders it. What follows only styles this plugin's
     * markup: core floats the legend on this form (float: inline-start, in
     * wp-admin/css/common.css) and clears only a fieldset's ul and nested
     * fieldsets, so our note would otherwise ride up alongside our own
     * heading and share its line. Start alignment rather than left keeps an
     * RTL admin reading correctly.
     */
    ?>
    <style>
    .jsst-delete-user-heading,
    .jsst-ticket-choice legend {
        text-align: start;
    }
    .jsst-ticket-choice .description {
        clear: both;
        text-align: start;
    }
    </style>
    <?php
}

/*
 * True only when an admin ticked "delete" for this user on the delete-users
 * screen. Everything else — a reassign, a programmatic wp_delete_user(), WP-CLI,
 * REST, a multisite removal — has no such field and gets the answer no, so the
 * destructive path is never the one taken by default.
 */
function jsst_should_delete_user_tickets($jsst_user_id)
{
    if (empty($_POST['jsst_delete_tickets']) || !is_array($_POST['jsst_delete_tickets'])) {
        return false;
    }
    if (!isset($_POST['jsst_delete_tickets'][$jsst_user_id])) {
        return false;
    }
    // The field only means anything inside the real form. Re-checking the
    // nonce and the capability keeps a stray POST somewhere else in wp-admin
    // from reaching the delete.
    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_REQUEST['_wpnonce'])), 'delete-users')) {
        return false;
    }
    if (!current_user_can('delete_users')) {
        return false;
    }
    return sanitize_key(wp_unslash($_POST['jsst_delete_tickets'][$jsst_user_id])) === 'delete';
}

function jsst_remove_user($jsst_user_id)
{
    if (!is_numeric($jsst_user_id)) {
        return;
    }

    $jsst_js_class = JSSTIncluder::getObjectClass('user');
    $jsst_userid = $jsst_js_class->getUserIDByWPUid($jsst_user_id);

    // Saved queue views belong to the WordPress user who saved them, so they go
    // when that user does — whichever way their tickets are dealt with below.
    // (Roadmap 4.0-CORE-18)
    JSSTqueue::removeUserViews($jsst_user_id);

    // No help desk record for this WordPress user. Returning matters: an empty
    // id makes JSSTtable::bind() treat the store below as an insert, which
    // would add a junk row on every deletion of a user who never used the
    // help desk.
    if (empty($jsst_userid)) {
        return;
    }

    if (jsst_should_delete_user_tickets($jsst_user_id)) {
        $jsst_js_class->deleteUserRecords($jsst_userid, true);
        return;
    }

    /* Otherwise detach rather than delete, so the tickets keep the name and
       address they were filed under and the queue still reads correctly. The
       row stays with wpuid = 0 and status = 0; it is no longer tied to a
       WordPress account and cannot log in. This branch used to be gated on
       $_POST['delete_option'] == 'delete', but WordPress posts delete_option as
       an array keyed by user id, so the comparison was array == string — false
       on every path, and the record was left pointing at a WordPress user that
       no longer existed. */
    $jsst_row = JSSTincluder::getJSTable('users');
    $jsst_data['id'] = $jsst_userid;
    $jsst_data['wpuid'] = 0;
    $jsst_data['status'] = 0;
    $jsst_row->bind($jsst_data);
    $jsst_row->store();
}

add_action('delete_user', 'jsst_remove_user');

add_action('personal_options_update', 'jsst_update_user_profile');


function jsst_update_user_profile($jsst_user_id) {
    if(!is_numeric($jsst_user_id)){
        return false;
    }
    $jsst_query = jssupportticket::$_db->prepare("SELECT * FROM `" . jssupportticket::$_db->prefix . "users` WHERE id = %d", $jsst_user_id);
    $jsst_user = jssupportticket::$_db->get_row($jsst_query);

    $jsst_uid = "";
	$jsst_post_user_id = '';
	$jsst_id = '';
	$jsst_post_user_login='';
	$jsst_post_display_name='';
	$jsst_post_nickname='';
	
	if(isset($_POST['user_id'])) $jsst_post_user_id = jssupportticket::JSST_sanitizeData($_POST['user_id']); // JSST_sanitizeData() function uses wordpress santize functions
    if ($jsst_post_user_id == $jsst_user_id) {
        $jsst_query = jssupportticket::$_db->prepare("SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_users` WHERE wpuid = %d", $jsst_user_id);
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

// Language Related Hooks
add_action('plugins_loaded', 'JSST_check_and_download_languages');

function JSST_check_and_download_languages() {

    if (!current_user_can('manage_options') && !wp_doing_cron()) {
        return; // Skip download attempt for non-privileged contexts
    }

    $locale = determine_locale();
    if ($locale === 'en_US') return;

    $status = get_option('jssupportticket_translation_status_' . $locale);
    if ($status === 'verified' || $status === 'failed') {
        return;
    }

    $textdomain   = 'js-support-ticket';
    $default_list = JSST_DEFAULT_LANGUAGES;
    $target_dir   = JSST_PLUGIN_PATH . 'languages/';
    $extensions   = in_array($locale, $default_list) ? array('po') : array('mo', 'po');

    $all_exist = true;
    foreach ($extensions as $ext) {
        if (!file_exists($target_dir . "{$textdomain}-{$locale}.{$ext}")) {
            $all_exist = false;
            break;
        }
    }

    if ($all_exist) {
        update_option('jssupportticket_translation_status_' . $locale, 'verified');
        return;
    }

    JSST_execute_download_process_for_languagefiles($locale, $extensions);
}

function JSST_execute_download_process_for_languagefiles($locale, $extensions) {
    global $wp_filesystem;

    // Initialize WP_Filesystem safely
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        if ( ! WP_Filesystem() ) {
            return; // Exit if Filesystem credentials are required but unavailable
        }
    }

    $textdomain = 'js-support-ticket';
    $cdn_base   = 'https://d2l808guy26fxz.cloudfront.net/'; // Retaining your secure CloudFront endpoint

    $target_dir = JSST_PLUGIN_PATH . 'languages/';

    $locales_to_try = array($locale);
    $fallback_locale = JSST_get_fallback_locale($locale);

    if ($fallback_locale) {
        $locales_to_try[] = $fallback_locale;
    }

    $download_successful = false;
    $downloaded_locale = '';

    foreach ($locales_to_try as $attempt_locale) {
        $all_extensions_downloaded = true;

        foreach ($extensions as $ext) {
            $remote_filename = "{$textdomain}-{$attempt_locale}.{$ext}";
            $local_filename  = "{$textdomain}-{$locale}.{$ext}";

            // Safe remote call wrapper with fallback timeouts
            $response = wp_remote_get($cdn_base . $remote_filename, array(
                'timeout' => 15
            ));

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                if (!$wp_filesystem->is_dir($target_dir)) {
                    $wp_filesystem->mkdir($target_dir, FS_CHMOD_DIR);
                }
                $saved = $wp_filesystem->put_contents($target_dir . $local_filename, wp_remote_retrieve_body($response));
                if (!$saved) {
                    $all_extensions_downloaded = false;
                    break;
                }
            } else {
                $all_extensions_downloaded = false;
                break;
            }
        }

        if ($all_extensions_downloaded) {
            $download_successful = true;
            $downloaded_locale = $attempt_locale;
            break;
        }
    }

    // Determine notice type and save to transient
    if ($download_successful) {
        update_option('jssupportticket_translation_status_' . $locale, 'verified');

        $notice_type = ($downloaded_locale === $locale) ? 'exact_success' : 'fallback_success';
        set_transient('jssupportticket_lang_notice', array(
            'type'     => $notice_type,
            'original' => $locale,
            'fallback' => $downloaded_locale
        ), 60);

    } else {
        update_option('jssupportticket_translation_status_' . $locale, 'failed');

        set_transient('jssupportticket_lang_notice', array(
            'type'     => 'failed',
            'original' => $locale,
        ), 300);
    }
}

function JSST_get_fallback_locale($locale) {
    $base_lang = substr($locale, 0, 2);

    // Comprehensive fallback map based on standard WordPress locales
    $fallbacks = array(
        'ar' => 'ar',          // Arabic
        'cs' => 'cs_CZ',       // Czech
        'de' => 'de_DE',       // German
        'el' => 'el',          // Greek
        'en' => 'en_US',       // English
        'es' => 'es_ES',       // Spanish
        'fa' => 'fa_IR',       // Persian
        'fr' => 'fr_FR',       // French
        'hu' => 'hu_HU',       // Hungarian
        'id' => 'id_ID',       // Indonesian
        'it' => 'it_IT',       // Italian
        'ja' => 'ja_JP',       // Japanese
        'ko' => 'ko_KR',       // Korean
        'ms' => 'ms_MY',       // Malay
        'nl' => 'nl_NL',       // Dutch
        'pl' => 'pl_PL',       // Polish
        'pt' => 'pt_BR',       // Brazil
        'ro' => 'ro_RO',       // Romanian
        'ru' => 'ru_RU',       // Russian
        'sv' => 'sv',          // Swedish
        'th' => 'th_TH',       // Thai
        'tl' => 'tl_PH',       // Filipino
        'tr' => 'tr_TR',       // Turkish
        'zh' => 'zh_CN'        // Chinese (Simplified)
    );

    if (isset($fallbacks[$base_lang]) && $fallbacks[$base_lang] !== $locale) {
        return $fallbacks[$base_lang];
    }

    return false;
}

add_action('admin_notices', 'JSST_display_language_download_notice');

function JSST_display_language_download_notice() {
    // Only show to users who can manage the site
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_transient('jssupportticket_lang_notice');
    if (!$notice) {
        return;
    }

    // Clear the transient immediately so it only shows once
    delete_transient('jssupportticket_lang_notice');

    $type     = $notice['type'];
    $original = esc_html($notice['original']);

    if ($type === 'exact_success') {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>' . esc_html( __( 'JS Support Ticket', 'js-support-ticket' ) ) . ':</strong> ' .
        /* translators: %s: the language/locale code or name for which language files were downloaded */
        sprintf( esc_html__( 'Language files for %s successfully downloaded.', 'js-support-ticket' ), '<code>' . esc_html( $original ) . '</code>' ) . '</p>';
        echo '</div>';
    }
    elseif ($type === 'fallback_success') {
        $fallback = esc_html($notice['fallback']);
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>' . esc_html__( 'JS Support Ticket', 'js-support-ticket' ) . ':</strong> ' .
        sprintf(
            /* translators: 1: the language/locale code that was originally requested, 2: the fallback language/locale code that was downloaded instead */
            esc_html__( 'Alternate language file downloaded. We tried to find %1$s, but downloaded %2$s as a fallback.', 'js-support-ticket' ),
            '<code>' . esc_html( $original ) . '</code>',
            '<code>' . esc_html( $fallback ) . '</code>'
        ) . '</p>';
        echo '</div>';
    }
}
?>
