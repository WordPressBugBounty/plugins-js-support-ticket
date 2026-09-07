<?php
if (!defined('ABSPATH')) die('Restricted Access');

/**
 * The AI Copilot's request handlers. (Roadmap 4.0-AI-01)
 *
 * Everything here is reached by an agent pressing something. Each entry point
 * checks the nonce, then the capability, then does exactly the one action it was
 * asked for and returns the text to the screen. None of them write to a ticket:
 * a drafted reply lands in the agent's editor for them to change or discard, and
 * this side of the plugin has no code that could post it.
 */
class JSSTcopilotModel {

    /* ------------------------------------------------------------------ *
     * One action, one ticket
     * ------------------------------------------------------------------ */

    /**
     * Run a Copilot action on the ticket the agent is reading.
     *
     * Answers as JSON and stops, rather than returning through the ajax
     * dispatcher's echo — the reply is a customer's ticket in another language
     * or a draft of what to say to them, and passing that through a tag filter
     * meant for markup would silently mangle it.
     */
    function copilotRun() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-copilot')) {
            wp_send_json_error(array('message' => esc_html(__('Security check failed.', 'js-support-ticket'))));
        }
        if (!class_exists('JSSTcopilot') || !JSSTcopilot::mayUse()) {
            wp_send_json_error(array('message' => esc_html(__('You do not have permission to use the AI Copilot.', 'js-support-ticket'))));
        }

        $jsst_action = sanitize_key(JSSTrequest::getVar('copilotaction', '', ''));
        $jsst_ticketid = absint(JSSTrequest::getVar('ticketid', '', 0));
        $jsst_options = array(
            'language' => sanitize_text_field(JSSTrequest::getVar('language', '', '')),
        );

        $jsst_result = JSSTcopilot::run($jsst_action, $jsst_ticketid, $jsst_options);
        if (is_wp_error($jsst_result)) {
            wp_send_json_error(array('message' => esc_html($jsst_result->get_error_message())));
        }

        wp_send_json_success(array(
            'action'    => $jsst_result['action'],
            'text'      => $jsst_result['text'],
            'fields'    => isset($jsst_result['fields']) ? $jsst_result['fields'] : null,
            // Said out loud rather than left for the agent to notice: a draft
            // that stopped early looks finished until you reach the end of it.
            'truncated' => !empty($jsst_result['truncated']),
        ));
    }

    /* ------------------------------------------------------------------ *
     * Several tickets at once
     * ------------------------------------------------------------------ */

    /** Queue a summary of every ticket the agent ticked on the list. */
    function copilotBulk() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-copilot')) {
            wp_send_json_error(array('message' => esc_html(__('Security check failed.', 'js-support-ticket'))));
        }
        if (!class_exists('JSSTcopilot') || !JSSTcopilot::mayUse()) {
            wp_send_json_error(array('message' => esc_html(__('You do not have permission to use the AI Copilot.', 'js-support-ticket'))));
        }

        $jsst_raw = JSSTrequest::getVar('ticketids', '', '');
        $jsst_ids = array_map('absint', array_filter(explode(',', (string) $jsst_raw)));

        $jsst_token = JSSTcopilot::startBatch($jsst_ids, 'summarize');
        if (is_wp_error($jsst_token)) {
            wp_send_json_error(array('message' => esc_html($jsst_token->get_error_message())));
        }

        /* Handed to the queue where there is one. Without a queue the work still
           has to happen, so it happens here — the agent waits, which is why the
           selection is capped either way. */
        if (class_exists('JSSTjobs') && JSSTjobs::available()) {
            JSSTjobs::enqueue('jsst_job_copilot_bulk', array('token' => $jsst_token), 'copilot', 0, 5);
        } else {
            while (JSSTcopilot::stepBatch($jsst_token)) {
                // Runs to the end of the selection; the cap is what bounds it.
            }
        }
        wp_send_json_success(array('token' => $jsst_token));
    }

    /** How far a queued batch has got, and what it has found so far. */
    function copilotBatch() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-copilot')) {
            wp_send_json_error(array('message' => esc_html(__('Security check failed.', 'js-support-ticket'))));
        }
        if (!class_exists('JSSTcopilot') || !JSSTcopilot::mayUse()) {
            wp_send_json_error(array('message' => esc_html(__('You do not have permission to use the AI Copilot.', 'js-support-ticket'))));
        }

        $jsst_token = sanitize_text_field(JSSTrequest::getVar('token', '', ''));
        $jsst_batch = JSSTcopilot::batch($jsst_token);
        if (!$jsst_batch) {
            wp_send_json_error(array('message' => esc_html(__('That run cannot be found. It may have finished more than a day ago.', 'js-support-ticket'))));
        }

        wp_send_json_success(array(
            'total'    => (int) $jsst_batch['total'],
            'done'     => count($jsst_batch['results']),
            'running'  => !empty($jsst_batch['queue']),
            'results'  => $jsst_batch['results'],
        ));
    }

}
