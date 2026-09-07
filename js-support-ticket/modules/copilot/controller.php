<?php
if (!defined('ABSPATH')) die('Restricted Access');

/**
 * The AI Copilot screen and its two controls. (Roadmap 4.0-AI-01)
 */
class JSSTcopilotController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'copilot');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_copilot':
                    jssupportticket::$jsst_data['providers'] = JSSTcopilotprovider::providers();
                    jssupportticket::$jsst_data['provider'] = JSSTcopilotprovider::current();
                    jssupportticket::$jsst_data['configured'] = JSSTcopilotprovider::configured();
                    jssupportticket::$jsst_data['model'] = JSSTcopilotprovider::model();
                    jssupportticket::$jsst_data['actions'] = JSSTcopilot::actions();
                    jssupportticket::$jsst_data['runs'] = JSSTcopilot::recentRuns();
                    jssupportticket::$jsst_data['usage'] = JSSTcopilot::usage();
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'copilot');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    function canaddfile($jsst_layout) {
        $jsst_nonce_value = JSSTrequest::getVar('jsst_nonce');
        if (wp_verify_nonce($jsst_nonce_value, 'jsst_nonce')) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if (!is_admin() && jssupportticketphplib::JSST_strpos($jsst_layout, 'admin_') === 0) {
                    return false;
                }
                return true;
            }
        }
    }

    /**
     * Save the provider, key, model and translation language.
     *
     * The key is only overwritten when a new one is typed. The screen shows a
     * stored key as a row of dots and posts those dots back unchanged when the
     * field is not touched, so writing whatever arrives would replace a working
     * key with a row of dots the first time somebody changed the language.
     */
    function savecopilotsettings() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-copilot-settings')) {
            die('Security check Failed');
        }
        if (!current_user_can('manage_options')) {
            return false;
        }

        $jsst_provider = sanitize_key(JSSTrequest::getVar('copilotprovider', '', 'anthropic'));
        $jsst_providers = JSSTcopilotprovider::providers();
        if (isset($jsst_providers[$jsst_provider])) {
            update_option(JSSTcopilotprovider::OPT_PROVIDER, $jsst_provider, false);
        }

        update_option(JSSTcopilotprovider::OPT_MODEL, sanitize_text_field(JSSTrequest::getVar('copilotmodel', '', '')), false);
        update_option('jsst_copilot_language', sanitize_text_field(JSSTrequest::getVar('copilotlanguage', '', '')), false);

        $jsst_key = trim((string) JSSTrequest::getVar('copilotkey', '', ''));
        if (JSSTrequest::getVar('copilotclearkey')) {
            delete_option(JSSTcopilotprovider::OPT_KEY);
            JSSTmessage::setMessage(esc_html(__('The key was removed. The Copilot is switched off until another one is added.', 'js-support-ticket')), 'updated');
        } elseif ($jsst_key !== '' && strpos($jsst_key, '.') !== 0) {
            update_option(JSSTcopilotprovider::OPT_KEY, $jsst_key, false);
            JSSTmessage::setMessage(esc_html(__('Saved. Use Test connection to check the key works, rather than letting an agent find out for you.', 'js-support-ticket')), 'updated');
        } else {
            JSSTmessage::setMessage(esc_html(__('Saved.', 'js-support-ticket')), 'updated');
        }
        self::goBack();
    }

    /**
     * Ask the provider for one short answer, to prove the key works.
     *
     * Sends no ticket content. The question is whether the key and the model are
     * right, and a test that posted a customer's message somewhere would be a
     * strange way to find out whether it was safe to.
     */
    function testcopilot() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-copilot-test')) {
            die('Security check Failed');
        }
        if (!current_user_can('manage_options')) {
            return false;
        }

        $jsst_result = JSSTcopilotprovider::ask(array(
            'system'    => 'Reply with the single word: ready.',
            'prompt'    => 'Are you reachable?',
            'effort'    => 'low',
            'maxtokens' => 2000,
        ));
        if (is_wp_error($jsst_result)) {
            JSSTmessage::setMessage(esc_html($jsst_result->get_error_message()), 'error', 'copilot');
        } else {
            JSSTmessage::setMessage(sprintf(
                /* translators: %s: the model that answered */
                esc_html(__('Connected. %s answered.', 'js-support-ticket')),
                esc_html($jsst_result['model'])
            ), 'updated');
        }
        self::goBack();
    }

    private static function goBack() {
        wp_safe_redirect(admin_url('admin.php?page=copilot&jstlay=copilot'));
        exit;
    }

}

$jsst_copilotController = new JSSTcopilotController();
?>
