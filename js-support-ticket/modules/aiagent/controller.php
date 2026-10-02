<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * The AI Agent screens. (Roadmap 6.0-AI-01, 4.0-AI-04, 6.0-AI-08)
 *
 * One module in place of three. Until this release the product had an Instant
 * Resolve menu, a Zywrap AI menu and an AI Copilot menu, each with its own
 * switches, its own idea of which provider was in use and its own log — and a
 * site owner asking the only question that matters ("is this thing sending my
 * customers' tickets somewhere, and what is it costing me?") had to visit all
 * three and add up the answer. Two of those menus are gone; their screens are
 * still routable and their code is untouched, because the tested chain that
 * talks to the hosted engine is not something to rewrite while renaming things.
 *
 * Three screens, in the order somebody asks about them:
 *
 *   Overview  what AI does on this site, which of it is switched on, and what
 *             it has been doing.
 *   Settings  the master switch, the three lanes, the engine and its key, the
 *             local endpoint, and how hard outbound text is scrubbed.
 *   Audit     every run every engine has recorded, plus a journal of who
 *             changed the policy and when.
 *
 * Every door here asks for an administrator. This is where the answer to "may
 * this plugin talk to a model at all" is decided, and that is not a per-agent
 * preference — the same reason the Service Levels screen is administrators only.
 */
class JSSTaiagentController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'aiagent');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));

        if (self::canaddfile($jsst_layout)) {
            if (current_user_can('manage_options')) {
                self::handleAddonTask();
            }
            switch ($jsst_layout) {
                case 'admin_aiagent':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getOverview();
                    break;

                case 'admin_aiagent_settings':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getSettings();
                    break;

                case 'admin_aiagent_sources':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getSources();
                    break;

                case 'admin_aiagent_approvals':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getApprovals();
                    break;

                case 'admin_aiagent_shadow':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getShadow();
                    break;

                case 'admin_aiagent_autopilot':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getAutopilot();
                    break;

                case 'admin_aiagent_usage':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getUsage();
                    break;

                case 'admin_aiagent_setup':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getSetup();
                    break;

                case 'admin_aiagent_gaps':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getGaps();
                    break;

                case 'admin_aiagent_audit':
                    self::guard();
                    JSSTincluder::getJSModel('aiagent')->getAudit();
                    break;

                /* The three screens the AI Agent add-on ships. They are routed
                   here, and their templates resolve to the add-on's directory
                   because core does not ship a file of that name -
                   JSSTincluder::sharedModules() is what allows one module to be
                   filled from two plugins. Routing stays in core so there is one
                   controller for the menu group and one guard on it.
                   (Roadmap 6.0-AI-02) */
                case 'admin_aiagent_answers':
                    self::guard();
                    if (self::addon()) {
                        JSSTaiagentdata::instance()->getAnalytics();
                    }
                    break;

                case 'admin_aiagent_feeds':
                    self::guard();
                    if (self::addon()) {
                        JSSTaiagentdata::instance()->getSources();
                    }
                    break;

                case 'admin_aiagent_feedform':
                    self::guard();
                    if (self::addon()) {
                        $jsst_feed = intval(JSSTrequest::getVar('jssupportticketid'));
                        if ($jsst_feed > 0) {
                            JSSTaiagentdata::instance()->getSourceForForm($jsst_feed);
                        } else {
                            jssupportticket::$jsst_data[0] = null;
                        }
                    }
                    break;

                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'aiagent');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    /**
     * Every screen in this module, one check.
     *
     * Written once rather than repeated per case because the thing being
     * protected is the same on all three, and a guard that has to be remembered
     * is a guard that eventually is not.
     */
    static function guard() {
        if (!current_user_can('manage_options') || !class_exists('JSSTaipolicy')) {
            wp_safe_redirect(admin_url('admin.php?page=jssupportticket'));
            exit;
        }
        /* Three of the templates routed through here are the add-on's own -
           Content Sources, the source form and Answers - and each opens by
           refusing to draw unless `permission_granted` is set. That flag used to
           be set by the add-on's controller, which 6.0-AI-02 removed when the
           routing moved into core: the guard above then decided the question and
           told nobody, so an administrator who passed it was shown "You do not
           have permission to view this page." Set here rather than per case,
           because this is the one place in the module that has decided. */
        jssupportticket::$jsst_data['permission_granted'] = true;
    }

    /**
     * Is the engine present?
     *
     * A screen the add-on owns is routed whether or not the add-on is
     * installed, because the menu greys those entries rather than hiding them
     * and a greyed entry somebody reaches by a bookmark should land on a page
     * that explains itself. Without the engine the template renders its own
     * "not installed" state from empty data rather than fataling on a missing
     * class.
     */
    static function addon() {
        return class_exists('JSSTaiagentdata');
    }

    /**
     * The add-on's own actions, which changed data before this module existed.
     *
     * Routed here rather than left in the add-on because the add-on no longer
     * has a controller - the module is core's. Each still verifies its own
     * nonce: one page-level nonce would let any form on the screen stand in for
     * any other. (Roadmap 6.0-AI-02)
     */
    static function handleAddonTask() {
        $jsst_task = JSSTrequest::getVar('jsst_aiagent_task');
        if (empty($jsst_task) || !self::addon()) {
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_data = JSSTaiagentdata::instance();
        $jsst_id   = intval(JSSTrequest::getVar('jssupportticketid'));

        switch ($jsst_task) {
            case 'store':
                if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'aiagent-store')) {
                    wp_die(esc_html__('Security check failed', 'js-support-ticket'));
                }
                $jsst_data->storeSource($_POST);
                break;

            case 'remove':
                if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'aiagent-remove-' . $jsst_id)) {
                    wp_die(esc_html__('Security check failed', 'js-support-ticket'));
                }
                $jsst_data->removeSource($jsst_id);
                break;

            case 'changestatus':
                if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'aiagent-status-' . $jsst_id)) {
                    wp_die(esc_html__('Security check failed', 'js-support-ticket'));
                }
                $jsst_data->changeSourceStatus($jsst_id);
                break;

            case 'sync':
                if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'aiagent-sync-' . $jsst_id)) {
                    wp_die(esc_html__('Security check failed', 'js-support-ticket'));
                }
                // No time limit raised here: syncSourceNow() hands the crawl to
                // wp-cron and returns immediately, and raises the limit itself
                // on the fallback path that still crawls in this request.
                $jsst_data->syncSourceNow($jsst_id);
                break;

            case 'rescan':
                if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'aiagent-rescan')) {
                    wp_die(esc_html__('Security check failed', 'js-support-ticket'));
                }
                JSSTaiagentretriever::syncIndexes();
                JSSTmessage::setMessage(esc_html(__('Content sources rechecked.', 'js-support-ticket')), 'updated');
                break;
        }
    }

    function canaddfile($jsst_layout) {
        $jsst_nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $jsst_nonce_value, 'jsst_nonce') ) {
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

    /* ------------------------------------------------------------------ *
     * Saving
     * ------------------------------------------------------------------ */

    /**
     * The Settings screen's one form.
     *
     * Which part of it was submitted is told by a hidden field rather than
     * guessed from what arrived, for the reason the SLA screen documents: an
     * unticked checkbox posts nothing, so "the field is missing" and "somebody
     * switched it off" look identical on the wire, and a handler that guesses
     * clears settings the form never showed.
     *
     * The master switch is written last on purpose. If saving a lane or an
     * engine fails on a validation error the redirect happens before the master
     * switch is touched, so a bad save can never leave AI on with a
     * half-configured engine behind it.
     */
    static function saveaisettings() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaipolicy')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_back = admin_url('admin.php?page=aiagent&jstlay=aiagent_settings');

        /* ---- the switches ---- */
        if (JSSTrequest::getVar('aiswitches', 'post', '') !== '') {
            foreach (array_keys(JSSTaipolicy::lanes()) as $jsst_lane) {
                JSSTaipolicy::setLane($jsst_lane, JSSTrequest::getVar('lane_' . $jsst_lane, 'post', '') !== '');
            }
            JSSTaipolicy::setRedactionMode(sanitize_key(JSSTrequest::getVar('redaction', 'post', 'credentials')));

            /* Read from the form rather than merged into what is already
               stored, so that unticking a box switches it off - an unticked
               checkbox posts nothing, and a handler that only ever added would
               make these two impossible to turn back off. The setter
               whitelists what it accepts. (Roadmap 6.0-AI-13) */
            if (class_exists('JSSTaitriage')) {
                $jsst_auto = array();
                if (JSSTrequest::getVar('autotriage', 'post', '') !== '') {
                    $jsst_auto[] = JSSTaitriage::OP_TRIAGE;
                }
                if (JSSTrequest::getVar('autosentiment', 'post', '') !== '') {
                    $jsst_auto[] = JSSTaitriage::OP_SENTIMENT;
                }
                JSSTaitriage::setAutomatic($jsst_auto);
            }

            /* The channel refusals are saved with the rest of the automatic-
               answer rules on Autopilot (saveairollout). Not read here: this
               form no longer carries them, and reading absent boxes as
               "unticked" would clear every refusal on each save. */

            JSSTaipolicy::setMaster(JSSTrequest::getVar('master', 'post', '') !== '');

            JSSTmessage::setMessage(
                JSSTaipolicy::enabled()
                    ? esc_html(__('Saved. The Overview lists what is live under these switches.', 'js-support-ticket'))
                    : esc_html(__('AI is switched off for this site. Nothing will be sent anywhere until it is switched back on.', 'js-support-ticket')),
                'updated'
            );
            wp_safe_redirect($jsst_back);
            exit;
        }

        /* ---- the engine ---- */
        if (JSSTrequest::getVar('aiengine', 'post', '') !== '') {
            if (!class_exists('JSSTaiengine')) {
                wp_safe_redirect($jsst_back);
                exit;
            }
            $jsst_engines = JSSTaiengine::engines();
            $jsst_chosen = sanitize_key(JSSTrequest::getVar('engine', 'post', ''));
            if (!isset($jsst_engines[$jsst_chosen])) {
                JSSTmessage::setMessage(esc_html(__('That is not an engine this site knows about.', 'js-support-ticket')), 'error');
                wp_safe_redirect($jsst_back);
                exit;
            }

            /* The local endpoint is validated before anything is written, so a
               typo cannot leave the site pointing at a URL it will keep trying.
               Note this is deliberately NOT the scraper's SSRF check: a private
               address is the whole point of a local model. */
            $jsst_endpoint = trim((string) JSSTrequest::getVar('localendpoint', 'post', ''));
            if (!JSSTaiengine::validLocalEndpoint($jsst_endpoint)) {
                JSSTmessage::setMessage(esc_html(__('The local model address must be an http or https URL, such as http://127.0.0.1:11434.', 'js-support-ticket')), 'error');
                wp_safe_redirect($jsst_back);
                exit;
            }

            update_option(JSSTaiengine::OPT_LOCAL_ENDPOINT, esc_url_raw($jsst_endpoint), false);
            update_option(JSSTaiengine::OPT_LOCAL_MODEL, sanitize_text_field(JSSTrequest::getVar('localmodel', 'post', '')), false);
            update_option(JSSTaiengine::OPT_LOCAL_TIMEOUT, absint(JSSTrequest::getVar('localtimeout', 'post', JSSTaiengine::LOCAL_TIMEOUT)), false);

            /* A blank key box means "leave it alone". saveKey() enforces that,
               so nothing here needs to decide whether the field was shown. */
            foreach (array_keys($jsst_engines) as $jsst_id) {
                JSSTaiengine::saveKey($jsst_id, JSSTrequest::getVar('key_' . $jsst_id, 'post', ''));
            }

            $jsst_model = sanitize_text_field(JSSTrequest::getVar('model', 'post', ''));
            if ($jsst_model !== '') {
                update_option(JSSTaiengine::OPT_MODEL, $jsst_model, false);
            }

            /* The one field the retired Copilot screen had that nothing else
               did. It is written under the same option name the Copilot still
               reads, so nothing about how Translate behaves changed - only
               where the box is. */
            update_option('jsst_copilot_language', sanitize_text_field(JSSTrequest::getVar('ailanguage', 'post', '')), false);

            if (JSSTaiengine::currentId() !== $jsst_chosen) {
                JSSTaipolicy::record('engine', $jsst_engines[$jsst_chosen]['label']);
            }
            update_option(JSSTaiengine::OPT_ENGINE, $jsst_chosen, false);

            /* Choosing an engine whose lane is shut is almost always somebody
               meaning to use it, so say what still has to happen rather than
               silently saving something that will refuse every request. */
            $jsst_lane = isset($jsst_engines[$jsst_chosen]['lane']) ? $jsst_engines[$jsst_chosen]['lane'] : 'hosted';
            if (!JSSTaipolicy::allows($jsst_lane)) {
                $jsst_lanes = JSSTaipolicy::lanes();
                JSSTmessage::setMessage(sprintf(
                    /* translators: %s: the name of an AI lane, for example "Local model" */
                    esc_html(__('Saved, but nothing will be sent yet: %s is switched off above.', 'js-support-ticket')),
                    isset($jsst_lanes[$jsst_lane]['label']) ? $jsst_lanes[$jsst_lane]['label'] : $jsst_lane
                ), 'error');
            } else {
                JSSTmessage::setMessage(esc_html(__('Saved. Use Test connection to check it answers before you rely on it.', 'js-support-ticket')), 'updated');
            }
            wp_safe_redirect($jsst_back);
            exit;
        }

        /* ---- how it behaves ---- */
        if (JSSTrequest::getVar('aibehaviour', 'post', '') !== '') {
            /* The four tabs that used to be on the Configuration screen.
               (Roadmap 6.0-AI-01) They are the same `js_ticket_config` rows they
               always were - nothing was migrated - so this writes them the way
               `JSSTconfigurationModel::storeConfiguration()` does, one row per
               posted field.

               Only fields that were actually posted are written, which is what
               makes the addon gate on the screen safe: a desk without the AI
               Agent draws the Suggestions group alone, posts that alone, and the
               rows behind the three hidden groups keep their stored values
               rather than being cleared to empty. Same reason that screen gave
               for leaving the analytics field out.

               The whitelist is the point of the rest of it. This handler is
               reachable by anybody who can post to the page, and without a list
               of names it would write any config row it was handed - the data
               directory, the slugs, the mail settings - from a form that is
               supposed to own twenty-four AI ones. */
            $jsst_allowed = array(
                'aiagent_enable', 'aiagent_min_chars', 'aiagent_max_results', 'aiagent_analytics',
                'aiagent_ai_enable', 'aiagent_ai_sources_limit', 'aiagent_ai_tone', 'aiagent_ai_language',
                'aiagent_autopilot_display_name', 'aiagent_autopilot_blacklist_keywords',
                'aiagent_grounding_mode', 'aiagent_min_coverage_deflect', 'aiagent_min_coverage_reply',
                'aiagent_require_citation', 'aiagent_min_overlap', 'aiagent_max_chunks',
                'aiagent_char_budget', 'aiagent_token_budget', 'aiagent_mmr_lambda',
                'aiagent_chunk_chars', 'aiagent_chunk_overlap',
            );
            $jsst_written = 0;
            foreach ($jsst_allowed as $jsst_name) {
                $jsst_value = JSSTrequest::getVar($jsst_name, 'post', null);
                if ($jsst_value === null) {
                    continue;
                }
                /* The one field that is prose rather than a number or a choice.
                   Everything else goes through sanitize_text_field, which would
                   collapse the newlines a comma-separated list is often typed
                   with. */
                $jsst_value = ($jsst_name === 'aiagent_autopilot_blacklist_keywords')
                    ? sanitize_textarea_field($jsst_value)
                    : sanitize_text_field($jsst_value);
                jssupportticket::$_db->update(
                    jssupportticket::$_db->prefix . 'js_ticket_config',
                    array('configvalue' => $jsst_value),
                    array('configname' => $jsst_name));
                $jsst_written++;
            }
            JSSTmessage::setMessage($jsst_written > 0
                ? esc_html(__('Saved.', 'js-support-ticket'))
                : esc_html(__('Nothing was changed.', 'js-support-ticket')), 'updated');
            wp_safe_redirect($jsst_back);
            exit;
        }

        wp_safe_redirect($jsst_back);
        exit;
    }

    /**
     * Ask the configured engine to say one word back.
     *
     * Goes through JSSTaiengine::ask() rather than making its own request, so a
     * green tick means the path a real reply takes works — lane check, key
     * lookup, scrub and all — rather than that some other request to the same
     * host worked. The result is kept in a transient rather than a setting
     * because it is a fact about five seconds ago, not configuration.
     */
    static function testaiengine() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-test')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaiengine')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_result = JSSTaiengine::selfTest();
        $jsst_result['engine'] = JSSTaiengine::currentLabel();
        $jsst_result['when'] = time();
        set_transient('jsst_ai_lasttest', $jsst_result, 15 * MINUTE_IN_SECONDS);

        JSSTmessage::setMessage(
            $jsst_result['ok']
                ? sprintf(
                    /* translators: 1: engine name, 2: round-trip time in milliseconds */
                    esc_html(__('%1$s answered in %2$d ms.', 'js-support-ticket')),
                    $jsst_result['engine'],
                    (int) $jsst_result['ms']
                )
                : $jsst_result['detail'],
            $jsst_result['ok'] ? 'updated' : 'error'
        );
        wp_safe_redirect(admin_url('admin.php?page=aiagent&jstlay=aiagent_settings'));
        exit;
    }

    /* ------------------------------------------------------------------ *
     * Approvals (Roadmap 6.0-AI-03)
     * ------------------------------------------------------------------ */

    /**
     * How much a person has to do before an automatic answer goes out.
     *
     * Its own small form rather than a field on the AI Agent Settings screen,
     * because it belongs beside the queue it governs: somebody who has just
     * read three answers they did not like should be able to switch the site to
     * "always ask" without going to look for where that lives.
     */
    static function saveaiapproval() {
        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-aiagent-approval')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaireview')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        JSSTaireview::setMode(sanitize_key(JSSTrequest::getVar('approvalmode', 'post', '')));
        JSSTmessage::setMessage(esc_html(__('Saved.', 'js-support-ticket')), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=aiagent&jstlay=aiagent_approvals'));
        exit;
    }

    /**
     * Approve, reject or withdraw one answer.
     *
     * One handler for the three, because they are one decision with three
     * outcomes and splitting them would mean three nonces to keep in step. The
     * nonce is per answer, so a link copied out of one row cannot decide
     * another's fate.
     */
    static function decideaianswer() {
        $jsst_id = (int) JSSTrequest::getVar('answer', '', 0);
        $jsst_do = sanitize_key(JSSTrequest::getVar('decision', '', ''));

        if (!wp_verify_nonce(JSSTrequest::getVar('_wpnonce'), 'jsst-aiagent-answer-' . $jsst_id)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaireview')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_note = sanitize_text_field(JSSTrequest::getVar('note', '', ''));

        switch ($jsst_do) {
            case 'approve':
                $jsst_ok = JSSTaireview::approve($jsst_id, $jsst_note);
                JSSTmessage::setMessage(
                    $jsst_ok
                        ? esc_html(__('Sent to the customer.', 'js-support-ticket'))
                        /* Deliberately says what to check rather than "failed".
                           Every reason this returns false is a state the admin
                           can see and fix: AI switched off, the site set to
                           propose only, the answer already decided, or no
                           engine installed to write the reply. */
                        : esc_html(__('Nothing was sent. Check that AI is switched on, that this site is not set to propose only, and that the AI Agent add-on is active.', 'js-support-ticket')),
                    $jsst_ok ? 'updated' : 'error'
                );
                break;

            case 'reject':
                JSSTaireview::reject($jsst_id, $jsst_note);
                JSSTmessage::setMessage(esc_html(__('Rejected. Nothing was sent, and the text is kept on the record.', 'js-support-ticket')), 'updated');
                break;

            case 'retract':
                /* The ticket is read after the fact rather than before, so the
                   message says what happened rather than what usually happens:
                   reopening can be refused by the reopen capability, and a
                   screen that claims a ticket was reopened when it was not is
                   the kind of small lie somebody plans around. */
                $jsst_answer = JSSTaireview::get($jsst_id);
                $jsst_ok = JSSTaireview::retract($jsst_id, $jsst_note);
                $jsst_shut = ($jsst_ok && $jsst_answer)
                    ? JSSTaireview::stillClosed((int) $jsst_answer->ticketid) : false;

                JSSTmessage::setMessage(
                    !$jsst_ok
                        ? esc_html(__('That answer could not be withdrawn.', 'js-support-ticket'))
                        : ($jsst_shut
                            ? esc_html(__('Withdrawn. The reply is struck through on the ticket, but the ticket is still closed — reopen it yourself if it should not be. The customer still has the e-mail.', 'js-support-ticket'))
                            : esc_html(__('Withdrawn. The reply is struck through on the ticket. The customer still has the e-mail — a correction is worth sending.', 'js-support-ticket'))),
                    $jsst_ok ? 'updated' : 'error'
                );
                break;
        }

        wp_safe_redirect(admin_url('admin.php?page=aiagent&jstlay=aiagent_approvals'));
        exit;
    }

    /* ------------------------------------------------------------------ *
     * The setup wizard (Roadmap 6.0-AI-11)
     * ------------------------------------------------------------------ */

    /**
     * The three things the checklist can do itself.
     *
     * Everything else on it is a link to the screen that already owns the
     * setting, deliberately: a wizard that writes its own copy of a setting is
     * a wizard that disagrees with the screen a fortnight later. These three
     * are here because each is a *combination* somebody could get half right,
     * and one of the halves writes to customers.
     */
    static function runaisetup() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-setup')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaisetup')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_back = admin_url('admin.php?page=aiagent&jstlay=aiagent_setup');
        $jsst_do   = sanitize_key(JSSTrequest::getVar('step', '', ''));

        switch ($jsst_do) {
            case 'shadow':
                $jsst_ok = JSSTaisetup::startShadow();
                JSSTmessage::setMessage(
                    $jsst_ok
                        ? esc_html(__('Shadow mode is running. From now on every ticket gets an answer written for it and none of them are sent — come back to Shadow Mode in a week or two.', 'js-support-ticket'))
                        : esc_html(__('Shadow mode could not be started. Check that AI is switched on and that the AI Agent add-on is active.', 'js-support-ticket')),
                    $jsst_ok ? 'updated' : 'error'
                );
                break;

            case 'live':
                /* Refused rather than hidden. The button is not drawn until the
                   evidence exists, but a bookmarked URL has to be refused too -
                   this is the one action on the screen that starts writing to
                   customers. */
                $jsst_ok = JSSTaisetup::goLive();
                JSSTmessage::setMessage(
                    $jsst_ok
                        ? esc_html(__('Automatic answers are live. Everything is reversible from the Autopilot screen, and any answer that goes out can be withdrawn.', 'js-support-ticket'))
                        : esc_html(__('Not yet. Shadow mode has to have written enough answers, and had enough of them compared against an agent, before this can be switched on.', 'js-support-ticket')),
                    $jsst_ok ? 'updated' : 'error'
                );
                break;

            case 'dismiss':
                JSSTaisetup::dismiss(true);
                JSSTmessage::setMessage(esc_html(__('Hidden. The checklist stays in the menu if you want it later.', 'js-support-ticket')), 'updated');
                wp_safe_redirect(admin_url('admin.php?page=aiagent'));
                exit;
        }

        wp_safe_redirect($jsst_back);
        exit;
    }

    /**
     * Hide a knowledge gap, or bring it back. (Roadmap 6.0-AI-12)
     *
     * The only write this screen has, and it deliberately does not delete
     * anything: the rows are the evidence a question was really asked, and the
     * next person to look at this list may disagree with today's judgement that
     * it will never have an article. So a dismissal is a signature in an option
     * and "show the hidden ones" is a link away.
     *
     * The filters are carried back through the redirect. Losing them would send
     * somebody who had narrowed to one signal over ninety days back to the
     * default view every time they hid a row, which on a screen whose whole
     * job is working through a list is the difference between usable and not.
     */
    static function saveaigap() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-gaps')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaigaps')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_signature = sanitize_text_field(JSSTrequest::getVar('signature', '', ''));
        $jsst_do        = sanitize_key(JSSTrequest::getVar('do', '', ''));

        if ($jsst_signature !== '') {
            JSSTaigaps::dismiss($jsst_signature, ($jsst_do !== 'restore'));
            JSSTmessage::setMessage(
                ($jsst_do === 'restore')
                    ? esc_html(__('Back on the list.', 'js-support-ticket'))
                    : esc_html(__('Hidden. The questions themselves are kept — "show hidden" brings it back.', 'js-support-ticket')),
                'updated'
            );
        }

        $jsst_back = array('page' => 'aiagent', 'jstlay' => 'aiagent_gaps');
        foreach (array('days', 'kind', 'show') as $jsst_key) {
            $jsst_value = sanitize_text_field(JSSTrequest::getVar($jsst_key, '', ''));
            if ($jsst_value !== '') $jsst_back[$jsst_key] = $jsst_value;
        }

        wp_safe_redirect(add_query_arg($jsst_back, admin_url('admin.php')));
        exit;
    }

    /* ------------------------------------------------------------------ *
     * Cost and usage (Roadmap 6.0-AI-07)
     * ------------------------------------------------------------------ */

    /**
     * The budgets, the fallback engine, and one model price.
     *
     * Three forms on one screen, told apart by a hidden marker rather than by
     * what arrived - the rule the SLA screen documents and the reason is the
     * same here: "alerts" is a checkbox, and an unticked checkbox posts
     * nothing, so a handler that guesses would switch alerts off every time
     * somebody saved a price.
     */
    static function saveaibudget() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-budget')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaiusage')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_back = admin_url('admin.php?page=aiagent&jstlay=aiagent_usage');

        if (JSSTrequest::getVar('aibudget', 'post', '') !== '') {
            JSSTaiusage::setBudget(array(
                'daymoney'    => (float) JSSTrequest::getVar('daymoney', 'post', 0),
                'monthmoney'  => (float) JSSTrequest::getVar('monthmoney', 'post', 0),
                'daycalls'    => (int) JSSTrequest::getVar('daycalls', 'post', 0),
                'ticketcalls' => (int) JSSTrequest::getVar('ticketcalls', 'post', 0),
                'alerts'      => (JSSTrequest::getVar('alerts', 'post', '') !== ''),
            ));

            /* Says which caps are in force rather than "Saved", because a
               budget of nought is the commonest way somebody leaves this screen
               believing they are protected when they are not. */
            $jsst_budget = JSSTaiusage::budget();
            $jsst_any = ($jsst_budget['daymoney'] > 0 || $jsst_budget['monthmoney'] > 0
                      || $jsst_budget['daycalls'] > 0 || $jsst_budget['ticketcalls'] > 0);
            JSSTmessage::setMessage(
                $jsst_any
                    ? esc_html(__('Saved. Requests are refused once a limit is reached.', 'js-support-ticket'))
                    : esc_html(__('Saved, but no limit is set: AI spending on this site is uncapped.', 'js-support-ticket')),
                'updated'
            );
            wp_safe_redirect($jsst_back);
            exit;
        }

        if (JSSTrequest::getVar('aifallback', 'post', '') !== '' && class_exists('JSSTaiengine')) {
            JSSTaiengine::setFallback(sanitize_key(JSSTrequest::getVar('fallback', 'post', '')));
            $jsst_spare = JSSTaiengine::fallbackId();
            JSSTmessage::setMessage(
                ($jsst_spare === '')
                    ? esc_html(__('Saved. Nothing is tried when the chosen engine cannot answer.', 'js-support-ticket'))
                    : esc_html(__('Saved. That engine is tried when the chosen one is unreachable, rate limited or out of budget — and never when the key or the model is wrong, because a second engine fails that the same way.', 'js-support-ticket')),
                'updated'
            );
            wp_safe_redirect($jsst_back);
            exit;
        }

        if (JSSTrequest::getVar('aiprice', 'post', '') !== '') {
            $jsst_model = sanitize_text_field(JSSTrequest::getVar('model', 'post', ''));
            if (JSSTrequest::getVar('forget', 'post', '') !== '') {
                JSSTaiusage::forgetPrice($jsst_model);
                JSSTmessage::setMessage(esc_html(__('Price removed. The shipped figure applies again if there is one.', 'js-support-ticket')), 'updated');
            } else {
                JSSTaiusage::setPrice(
                    $jsst_model,
                    (float) JSSTrequest::getVar('inprice', 'post', 0),
                    (float) JSSTrequest::getVar('outprice', 'post', 0)
                );
                /* Only what was called after the price was set is repriced.
                   Rewriting history would change a figure somebody has already
                   compared against an invoice. */
                JSSTmessage::setMessage(esc_html(__('Price saved. It applies to calls made from now on; figures already recorded are left as they were.', 'js-support-ticket')), 'updated');
            }
        }

        wp_safe_redirect($jsst_back);
        exit;
    }

    /* ------------------------------------------------------------------ *
     * Autopilot rollout (Roadmap 6.0-AI-06)
     * ------------------------------------------------------------------ */

    /**
     * The rollout controls: who, how often, how fast, and what happens instead.
     *
     * Read from the posted form rather than from what is missing, with a hidden
     * marker so a request that lost its body is told apart from one that
     * unticked everything - excluding every audience is a real choice and has
     * to be savable, and it is the choice that stops automation dead.
     *
     * Validation is JSSTairollout::setRules()'s, not this handler's: the setup
     * wizard and the CLI reach the same setter, and a rule enforced at the form
     * is a rule the other two callers do not have.
     */
    static function saveairollout() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-rollout')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTairollout')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_back = admin_url('admin.php?page=aiagent&jstlay=aiagent_autopilot');

        if (JSSTrequest::getVar('airollout', 'post', '') === '') {
            wp_safe_redirect($jsst_back);
            exit;
        }

        $jsst_audiences = JSSTrequest::getVar('audiences', 'post', array());
        $jsst_depts     = JSSTrequest::getVar('departments', 'post', array());

        JSSTairollout::setRules(array(
            'enabled'     => (JSSTrequest::getVar('enabled', 'post', '') !== ''),
            'followups'   => (JSSTrequest::getVar('followups', 'post', '') !== ''),
            'threshold'   => (int) JSSTrequest::getVar('threshold', 'post', 85),
            'maxreplies'  => (int) JSSTrequest::getVar('maxreplies', 'post', 2),
            'delay'       => (int) JSSTrequest::getVar('delay', 'post', 0),
            'departments' => is_array($jsst_depts) ? $jsst_depts : array(),
            'audiences'   => is_array($jsst_audiences) ? $jsst_audiences : array(),
            'blocked'     => (string) JSSTrequest::getVar('blocked', 'post', ''),
            'fallback'    => sanitize_key(JSSTrequest::getVar('fallback', 'post', '')),
        ));

        /* Which channels it may answer on. Only when the form drew them (the
           marker), and only for the channels it drew: an unticked box posts
           nothing, so a channel that was not on the screen keeps whatever it
           had rather than being read as un-refused. (Roadmap 6.0-CH-02) */
        if (class_exists('JSSTchannels') && JSSTrequest::getVar('aichannels', 'post', '') !== '') {
            $jsst_off = array();
            foreach (JSSTchannels::channels() as $jsst_chkey => $jsst_ch) {
                if (empty($jsst_ch['present'])) {
                    if (in_array($jsst_chkey, JSSTchannels::aiRefused(), true)) {
                        $jsst_off[] = $jsst_chkey;
                    }
                    continue;
                }
                if (JSSTrequest::getVar('chanoff_' . $jsst_chkey, 'post', '') !== '') {
                    $jsst_off[] = $jsst_chkey;
                }
            }
            JSSTchannels::setAiRefused($jsst_off);
        }

        /* How replies are signed and the words that always stop one. Still
           the add-on's config rows; written only when the form drew them. */
        foreach (array('aiagent_autopilot_display_name', 'aiagent_autopilot_blacklist_keywords') as $jsst_name) {
            $jsst_value = JSSTrequest::getVar($jsst_name, 'post', null);
            if ($jsst_value === null) {
                continue;
            }
            $jsst_value = ($jsst_name === 'aiagent_autopilot_blacklist_keywords')
                /* Stored comma-separated, which is how it is read. A keyword on
                   its own line is still a keyword, and a trailing comma is not
                   one, so both are normalised here. */
                ? implode(', ', array_filter(array_map('trim', preg_split('/[,\r\n]+/', sanitize_textarea_field($jsst_value))), 'strlen'))
                : sanitize_text_field($jsst_value);
            jssupportticket::$_db->update(
                jssupportticket::$_db->prefix . 'js_ticket_config',
                array('configvalue' => $jsst_value),
                array('configname' => $jsst_name));
        }

        /* The wizard's "you have looked at the limits" marker. There is no
           correct floor, so the checklist cannot test for a value - what it can
           test for is that somebody came here and decided. (Roadmap 6.0-AI-11) */
        update_option('jsst_ai_setup_limits', 1, false);

        /* Saying what the save means rather than "Saved": switching automation
           on is the one setting on this screen that starts writing to
           customers, and it should never be something somebody did quietly. */
        $jsst_state = JSSTairollout::explain();
        JSSTmessage::setMessage(
            ($jsst_state['state'] === 'ok')
                ? esc_html(__('Saved. Automatic answers are live for the audiences above.', 'js-support-ticket'))
                : $jsst_state['detail'],
            'updated'
        );
        wp_safe_redirect($jsst_back);
        exit;
    }

    /**
     * Stop, or start again.
     *
     * Its own task with its own nonce rather than a field on the form above,
     * because it has to work when the form does not: this is the button
     * somebody presses in a hurry, and it must not depend on the rest of the
     * screen being filled in correctly. It writes one option and changes no
     * setting, so resuming restores exactly what was running.
     */
    static function pauseautopilot() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-pause')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTairollout')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_stop = (JSSTrequest::getVar('resume', '', '') === '') ? true : false;
        JSSTairollout::pause($jsst_stop);

        JSSTmessage::setMessage(
            $jsst_stop
                ? esc_html(__('Paused. Nothing is written or sent until you resume; anything already in flight is held for approval.', 'js-support-ticket'))
                : esc_html(__('Resumed. Answers already waiting stay on the Approvals screen — resuming does not release them.', 'js-support-ticket')),
            'updated'
        );
        // Back to the AI screen the button was pressed on (the warning shows on
        // all of them); anything else goes to the rules.
        $jsst_back = wp_get_referer();
        if (!$jsst_back || strpos($jsst_back, 'page=aiagent') === false) {
            $jsst_back = admin_url('admin.php?page=aiagent&jstlay=aiagent_autopilot');
        }
        wp_safe_redirect($jsst_back);
        exit;
    }

    /* ------------------------------------------------------------------ *
     * Knowledge sources (Roadmap 6.0-AI-02)
     * ------------------------------------------------------------------ */

    /**
     * The approved-source form: which types may be answered from, and how each
     * one's exceptions run.
     *
     * Approval is read from the posted list rather than from what is missing,
     * which is the one place a checkbox form can legitimately do that: every
     * type is drawn on the page every time, so "not in the post" really does
     * mean "unticked" here. The hidden marker is still sent so a request that
     * lost its body entirely is told apart from one that unticked everything —
     * approving nothing is a real choice and must be savable.
     */
    static function saveaisources() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-sources')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaisources')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_back = admin_url('admin.php?page=aiagent&jstlay=aiagent_sources');
        /* Back to the Documents tab that was open when Save was pressed. */
        $jsst_backsrc = sanitize_key(JSSTrequest::getVar('src', 'post', ''));
        if ($jsst_backsrc !== '' && JSSTaisources::isType($jsst_backsrc)) {
            $jsst_back = add_query_arg('src', $jsst_backsrc, $jsst_back);
        }

        if (JSSTrequest::getVar('aisources', 'post', '') === '') {
            wp_safe_redirect($jsst_back);
            exit;
        }

        $jsst_before = JSSTaisources::approvedTypes();
        $jsst_approved = array();
        foreach (array_keys(JSSTaisources::types()) as $jsst_key) {
            /* A source whose add-on is not running is shown disabled, and a
               disabled box posts nothing - so its earlier choice is kept
               rather than read as "switched off", and it comes back as it was
               when the add-on does. */
            if (!JSSTaisources::present($jsst_key)) {
                if (in_array($jsst_key, $jsst_before, true)) {
                    $jsst_approved[] = $jsst_key;
                }
                continue;
            }
            if (JSSTrequest::getVar('src_' . $jsst_key, 'post', '') !== '') {
                $jsst_approved[] = $jsst_key;
            }
            JSSTaisources::setMode($jsst_key, sanitize_key(JSSTrequest::getVar('mode_' . $jsst_key, 'post', '')));
        }
        JSSTaisources::setApprovedTypes($jsst_approved);

        /* Recorded in the same journal as the lane switches, because "when did
           the AI stop being able to see our knowledge base" is the same kind of
           question as "when did somebody turn the hosted lane off" and there
           should be one place that answers both. */
        if (class_exists('JSSTaipolicy') && $jsst_before != $jsst_approved) {
            JSSTaipolicy::record('sources', empty($jsst_approved)
                ? esc_html(__('none', 'js-support-ticket'))
                : implode(', ', $jsst_approved));
        }

        JSSTmessage::setMessage(
            empty($jsst_approved)
                ? esc_html(__('Saved. No source is approved, so nothing can be answered from your content at all.', 'js-support-ticket'))
                : esc_html(__('Saved. The test console at the bottom of this page shows what a question would reach now.', 'js-support-ticket')),
            'updated'
        );
        wp_safe_redirect($jsst_back);
        exit;
    }

    /**
     * Approve, exclude or un-decide one document.
     *
     * A GET action with its own nonce rather than a form, because it is drawn
     * once per row in a list of twenty and a form per row is twenty forms. The
     * nonce is per document, so a link copied out of one row cannot be used to
     * rule on another.
     */
    /**
     * The language policy. (Roadmap 6.0-AI-09)
     *
     * Two fields and one of them is a fallback, so this is a short handler -
     * but the mode is saved through the setter that owns it rather than written
     * straight to the option, for the reason the setup wizard documents: a
     * second writer of a setting is a second opinion about it.
     */
    static function saveailanguage() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-language')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTailanguages')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_mode = sanitize_key(JSSTrequest::getVar('langmode', '', ''));
        if (!JSSTailanguages::setMode($jsst_mode)) {
            JSSTmessage::setMessage(esc_html(__('That is not one of the choices.', 'js-support-ticket')), 'error');
            wp_safe_redirect(admin_url('admin.php?page=aiagent&jstlay=aiagent_sources'));
            exit;
        }

        /* An empty fallback is a real answer meaning "use the site's own
           language", so it is stored as the empty string rather than rejected -
         and siteLanguage() reads get_locale() when it finds one. */
        $jsst_fallback = sanitize_key(JSSTrequest::getVar('langfallback', '', ''));
        if ($jsst_fallback === '' || array_key_exists($jsst_fallback, JSSTailanguages::known())) {
            update_option(JSSTailanguages::OPT_FALLBACK, $jsst_fallback, false);
        }

        /* The survey decides whether any of this applies at all, and it is
           cached. Saving a policy without clearing it means the screen reports
           the new mode and retrieval keeps using the old picture. */
        JSSTailanguages::forgetSurvey();

        JSSTmessage::setMessage(esc_html(__('Saved. This changes which of your documents are used to answer, not what is sent anywhere.', 'js-support-ticket')), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=aiagent&jstlay=aiagent_sources'));
        exit;
    }

    static function setsourcerule() {
        $jsst_type = sanitize_key(JSSTrequest::getVar('src', '', ''));
        $jsst_id   = (int) JSSTrequest::getVar('doc', '', 0);
        $jsst_verdict = sanitize_key(JSSTrequest::getVar('verdict', '', ''));

        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-rule-' . $jsst_type . '-' . $jsst_id)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaisources')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        if ($jsst_verdict === 'clear') {
            JSSTaisources::clearRule($jsst_type, $jsst_id);
        } else {
            JSSTaisources::setRule($jsst_type, $jsst_id,
                ($jsst_verdict === 'allow') ? JSSTaisources::RULE_ALLOW : JSSTaisources::RULE_DENY);
        }

        /* Back to exactly the page the link was on, search and paging included.
           A rule set on page four of a search that then dumps somebody on page
           one of everything is a screen that cannot be used to work through a
           list, which is the only way this screen is ever used. */
        wp_safe_redirect(self::sourcesUrl($jsst_type));
        exit;
    }

    /** Drop every exception one source holds, returning it to its mode's default. */
    static function clearsourcerules() {
        $jsst_type = sanitize_key(JSSTrequest::getVar('src', '', ''));
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-clear-' . $jsst_type)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaisources')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_gone = JSSTaisources::clearType($jsst_type);
        JSSTmessage::setMessage(sprintf(
            /* translators: %d: number of individual decisions that were removed */
            esc_html(_n('%d decision was cleared.', '%d decisions were cleared.', $jsst_gone, 'js-support-ticket')),
            $jsst_gone
        ), 'updated');
        wp_safe_redirect(self::sourcesUrl($jsst_type));
        exit;
    }

    /**
     * Re-read one source from scratch.
     *
     * Worth saying plainly on the screen and here: this is not what makes a
     * governance decision take effect. Rules are applied to the query, so they
     * are live the moment they are saved. This is for the other problem — a
     * derived index that no longer matches the content it came from, which is
     * what an import, a restore or a half-finished crawl leaves behind.
     */
    static function resyncaisource() {
        $jsst_type = sanitize_key(JSSTrequest::getVar('src', '', ''));
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-resync-' . $jsst_type)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaisources')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }

        $jsst_queued = JSSTaisources::resync($jsst_type);
        JSSTmessage::setMessage(
            $jsst_queued
                ? esc_html(__('Re-reading that source in the background. Searches keep working meanwhile.', 'js-support-ticket'))
                : esc_html(__('Nothing needed rebuilding for that source, and its caches have been cleared.', 'js-support-ticket')),
            'updated'
        );
        wp_safe_redirect(self::sourcesUrl($jsst_type));
        exit;
    }

    /**
     * The Knowledge Sources URL, carrying back whichever source, search and page
     * the request came from.
     */
    private static function sourcesUrl($jsst_type) {
        $jsst_args = array('page' => 'aiagent', 'jstlay' => 'aiagent_sources');
        if ($jsst_type !== '') $jsst_args['src'] = $jsst_type;

        $jsst_find = trim((string) JSSTrequest::getVar('find', '', ''));
        if ($jsst_find !== '') $jsst_args['find'] = $jsst_find;

        $jsst_page = (int) JSSTrequest::getVar('dp', '', 0);
        if ($jsst_page > 1) $jsst_args['dp'] = $jsst_page;

        /* Back to the Documents heading the action was taken from, not the top
           of the page. */
        $jsst_anchor = ($jsst_type !== '') ? '#jsst-documents' : '';
        return add_query_arg($jsst_args, admin_url('admin.php')) . $jsst_anchor;
    }

    /**
     * Delete one stored key.
     *
     * Its own nonced action rather than an empty box on the settings form,
     * because the form can never redisplay a key — an empty input is the normal
     * state of that field, and reading it as a deletion would wipe a working key
     * every time somebody saved an unrelated setting on the same page.
     */
    static function forgetaikey() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'jsst-aiagent-forget')) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options') || !class_exists('JSSTaiengine')) {
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        $jsst_id = sanitize_key(JSSTrequest::getVar('engine', '', ''));
        $jsst_engines = JSSTaiengine::engines();
        if (isset($jsst_engines[$jsst_id])) {
            JSSTaiengine::forgetKey($jsst_id);
            JSSTmessage::setMessage(sprintf(
                /* translators: %s: the name of an AI engine */
                esc_html(__('The %s key was deleted. Nothing else changed.', 'js-support-ticket')),
                $jsst_engines[$jsst_id]['label']
            ), 'updated');
        }
        wp_safe_redirect(admin_url('admin.php?page=aiagent&jstlay=aiagent_settings'));
        exit;
    }

}

$jsst_aiagentController = new JSSTaiagentController();
