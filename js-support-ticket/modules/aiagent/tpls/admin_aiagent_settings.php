<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Settings. (Roadmap 4.0-AI-04, 6.0-AI-03, 6.0-AI-08)
 *
 * Two forms, in the order the decisions are actually made: first whether
 * anything may happen at all, then which engine does it.
 *
 * The switches are arranged as one master and three lanes rather than as a list
 * of features, because the questions people bring to this screen are about where
 * data goes, not about which button an agent sees. "Switch off anything that
 * leaves my server" is one tick here; expressed as features it was four ticks
 * across three screens, and four ticks is four chances to miss one.
 *
 * The master switch is drawn first on the form, because nothing else on it does
 * anything while it is off, and saved last in the handler, so a validation
 * failure below it can never leave AI on with a half-configured engine behind it.
 */
if (!class_exists('JSSTaipolicy') || !class_exists('JSSTaiengine')) {
    echo esc_html(__('The AI policy is not available.', 'js-support-ticket'));
    return;
}

$jsst_policy  = isset(jssupportticket::$jsst_data['aipolicy']) ? jssupportticket::$jsst_data['aipolicy'] : array();
$jsst_engines = isset(jssupportticket::$jsst_data['aiengines']) ? jssupportticket::$jsst_data['aiengines'] : array();
$jsst_test    = isset(jssupportticket::$jsst_data['aitest']) ? jssupportticket::$jsst_data['aitest'] : false;

$jsst_action  = wp_nonce_url(admin_url('admin.php?page=aiagent&task=saveaisettings&action=jstask'), 'jsst-aiagent');
$jsst_testurl = wp_nonce_url(admin_url('admin.php?page=aiagent&task=testaiengine&action=jstask'), 'jsst-aiagent-test');

$jsst_lanes      = isset($jsst_policy['lanes']) ? $jsst_policy['lanes'] : array();
$jsst_states     = isset($jsst_policy['states']) ? $jsst_policy['states'] : array();
$jsst_modes      = isset($jsst_policy['modes']) ? $jsst_policy['modes'] : array();
$jsst_redaction  = isset($jsst_policy['redaction']) ? $jsst_policy['redaction'] : 'credentials';
$jsst_current    = isset($jsst_engines['current']) ? $jsst_engines['current'] : '';
$jsst_enginerows = isset($jsst_engines['engines']) ? $jsst_engines['engines'] : array();
$jsst_model      = isset($jsst_engines['model']) ? $jsst_engines['model'] : '';

/* The behaviour settings, which used to be four tabs on the Configuration
   screen. (Roadmap 6.0-AI-01)

   They were there because they arrived as the Instant Resolve add-on's
   settings, and pre-4.0 that is where an add-on put them. Nothing built since
   4.0 does - SLA, Automation, Analytics, Retention, the API and Channels all
   keep their settings on their own screen - so one feature had two homes, one
   of them called Settings and the other called Configuration, and the answer to
   "where do I change this" depended on which era the setting was written in.

   They are still `js_ticket_config` rows and still read through the same model,
   so nothing was migrated and a desk that rolls this version back finds every
   value where it left it. Only the screen that draws them has moved.

   Read straight from the table rather than from `jssupportticket::$_config`,
   which is filled once early in the request: a save on this screen redirects
   back to it, and the fields have to show what was just written rather than
   what the row said when the request began. One query, and the `isset()` each
   field is drawn behind then means what it says - the row is there or it is
   not. */
$jsst_cfg = array();
foreach ((array) jssupportticket::$_db->get_results(
        "SELECT configname, configvalue FROM `" . jssupportticket::$_db->prefix
        . "js_ticket_config` WHERE configname LIKE 'aiagent\\_%'") as $jsst_row) {
    $jsst_cfg[$jsst_row->configname] = $jsst_row->configvalue;
}

$jsst_yesno = array(
    (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket'))),
);

/**
 * One setting, in this screen's own vocabulary.
 *
 * Same three arguments as the configuration screen's `JSST_printConfigFieldSingle()`
 * so the field definitions moved across unchanged - which is the point: twenty-four
 * settings retyped into a different shape is twenty-four chances to drop one.
 * What it draws is the 4.5 field furniture rather than that screen's table, so
 * the moved settings look like the switches they now sit under.
 */
function jsstAiSettingField($jsst_title, $jsst_field, $jsst_description = '', $jsst_why = '') {
    /* Most of these are a number or a choice, and three across a card is the
       right density for them - that is what `.jsst-frow` is sized for. Three
       are not: the blocked-keywords box is prose, and the two summaries that
       report what another screen decided are rows of chips. Capped at 320px
       they wrapped one chip per line beside a column of empty space, so they
       take the full width instead. Detected from what is being drawn rather
       than passed in at each call site, because the caller that would have to
       remember is the moved markup, and the whole point of moving it unchanged
       is that it has nothing new to remember. */
    $jsst_wide = (strpos($jsst_field, '<textarea') !== false
        || strpos($jsst_field, 'jsst-chips') !== false);
    echo '<div class="jsst-frow' . ($jsst_wide ? ' jsst-frow-full' : '') . '">';
    echo '<span class="jsst-flabel">' . wp_kses_post($jsst_title) . '</span>';
    echo '<div class="jsst-fval">' . wp_kses($jsst_field, JSST_ALLOWED_TAGS) . '</div>';
    if ($jsst_description !== '') {
        echo '<p class="jsst-fhelp">' . wp_kses_post($jsst_description) . '</p>';
    }
    // The longer reasoning, one click away rather than in the way.
    JSSTlayout::why($jsst_why);
    echo '</div>';
}

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('AI Agent Settings', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_settings'); } ?>

            <?php /* One section at a time. (Roadmap 4.5-FE-06 kept every setting
                     findable by linking rather than hiding; that still holds:
                     each tab is the section's own #anchor, so a support link or
                     a bookmark opens the right one, the page prints whole, and
                     without JavaScript every section is shown as before.) The
                     three that belong to the add-on are listed only when it is
                     here, for the same reason their cards are. */ ?>
            <ul class="jsst-tabs jsst-settings-tabs" data-jsst-settingtabs aria-label="<?php echo esc_attr__('Settings sections', 'js-support-ticket'); ?>">
                <?php /* Engine first: it is the first thing anybody sets up, and
                         nothing that needs a key works until it is done. */ ?>
                <li><a class="jsst-tab" href="#AIEngine"><?php echo esc_html__('Engine & key', 'js-support-ticket'); ?></a></li>
                <li><a class="jsst-tab" href="#AISwitches"><?php echo esc_html__('What AI may do', 'js-support-ticket'); ?></a></li>
                <li><a class="jsst-tab" href="#AIAgentSuggestions"><?php echo esc_html__('Suggestions', 'js-support-ticket'); ?></a></li>
                <?php if (in_array('aiagent', jssupportticket::$_active_addons)) { ?>
                    <li><a class="jsst-tab" href="#AIAgentAI"><?php echo esc_html__('Written answers', 'js-support-ticket'); ?></a></li>
                    <li><a class="jsst-tab" href="#AIAgentAdvanced"><?php echo esc_html__('Advanced', 'js-support-ticket'); ?></a></li>
                <?php } ?>
            </ul>

            <div data-jsst-for="AISwitches">
            <p class="jsst-lede">
                <?php echo esc_html(__('Choose where AI may run and what it may do. The master switch turns every AI feature off at once.', 'js-support-ticket')); ?>
            </p><?php JSSTlayout::why(__('Three lanes, one master switch. A lane is a different answer to "what left the server", not a different feature — so switching one off switches off everything that would have used it, wherever in the product that happens to be.', 'js-support-ticket')); ?>
            </div>

            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_action); ?>">
                <input type="hidden" name="aiswitches" value="1" />

                <div class="jsst-card" id="AISwitches">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('What AI may do', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('Switch each lane on or off. With both model lanes off, suggested articles and past replies still work.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Each lane can be switched independently. On-site answers need no model and no network at all, so a site that switches both model lanes off still gets suggested articles and matching past replies.', 'js-support-ticket')); ?>
                    </div>
                    <div class="jsst-card-body">

                        <?php /* First, because nothing below it does anything while it is
                                 off - drawn last, it was the switch people saved past. */ ?>
                        <fieldset class="jsst-fieldset" id="AIMaster">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The master switch', 'js-support-ticket')); ?></legend>
                            <label class="jsst-check">
                                <input type="checkbox" name="master" value="1" <?php checked(!empty($jsst_policy['enabled'])); ?> />
                                <span><?php echo esc_html(__('Allow this help desk to use AI', 'js-support-ticket')); ?></span>
                            </label>
                            <p class="jsst-fhelp"><?php echo esc_html(!empty($jsst_policy['enabled'])
                                ? __('Off stops every AI feature at once. Nothing is deleted, and switching it back on restores it.', 'js-support-ticket')
                                : __('AI is off, so nothing below does anything yet. Tick this and save to switch it on.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Off means off, everywhere, immediately — no lane is open, no engine is asked and no AI control is drawn on any screen. Nothing is deleted and nothing is reconfigured, so switching it back on restores exactly what was running before.', 'js-support-ticket')); ?>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('The three lanes', 'js-support-ticket')); ?></legend>
                            <?php foreach ($jsst_lanes as $jsst_laneid => $jsst_lane) { ?>
                                <label class="jsst-check">
                                    <input type="checkbox" name="lane_<?php echo esc_attr($jsst_laneid); ?>" value="1" <?php checked(!empty($jsst_states[$jsst_laneid])); ?> />
                                    <span><?php echo esc_html($jsst_lane['label']); ?></span>
                                </label>
                                <p class="jsst-fhelp">
                                    <?php echo esc_html($jsst_lane['blurb']); ?>
                                    <strong><?php echo esc_html($jsst_lane['leaves']); ?></strong>
                                </p>
                            <?php } ?>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('What is removed before anything is sent', 'js-support-ticket')); ?></legend>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Applies to the local lane too.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Applied on the local lane as well as the hosted one. A model on your own hardware still writes a log file, and that log file still gets backed up.', 'js-support-ticket')); ?>
                            <div class="jsst-checks">
                                <?php foreach ($jsst_modes as $jsst_modeid => $jsst_mode) { ?>
                                    <label class="jsst-check">
                                        <input type="radio" name="redaction" value="<?php echo esc_attr($jsst_modeid); ?>" <?php checked($jsst_redaction, $jsst_modeid); ?> />
                                        <span><?php echo esc_html($jsst_mode['label']); ?></span>
                                    </label>
                                    <p class="jsst-fhelp"><?php echo esc_html($jsst_mode['blurb']); ?></p>
                                <?php } ?>
                            </div>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Vault credentials are never put in a prompt.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Credentials stored in the vault are never in a prompt at any level: they live in their own encrypted table and nothing that builds context reads it.', 'js-support-ticket')); ?>
                        </fieldset>

                        <?php /* Which channels the engine may answer on moved to
                                 Autopilot, with the other rules about automatic
                                 answers; this tab is about what may leave the server. */ ?>

                        <?php /* The one place in this product where something is
                                 sent to a model without anybody pressing a
                                 button, so the wording is the consent rather
                                 than a label on it. (Roadmap 6.0-AI-13) */ ?>
                        <?php if (class_exists('JSSTaitriage')) {
                            $jsst_auto = JSSTaitriage::automatic(); ?>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Reading tickets as they arrive', 'js-support-ticket')); ?></legend>
                            <p class="jsst-fhelp"><?php echo esc_html(__('When on, every new ticket is sent to your engine as it arrives. Off until you switch it on.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Everything else this plugin does with AI starts with somebody pressing a button. These two do not: switching one on means every new ticket is sent to your engine the moment it is filed, before anybody has read it. That is a real change to what leaves your site, and it is off until you say otherwise.', 'js-support-ticket')); ?>
                            <?php /* One per line: side by side the two read as one sentence. */ ?>
                            <div class="jsst-checkstack">
                            <label class="jsst-check">
                                <input type="checkbox" name="autotriage" value="1" <?php checked(in_array(JSSTaitriage::OP_TRIAGE, $jsst_auto, true)); ?> />
                                <span><?php echo esc_html(__('Suggest which department a new ticket belongs to, and how urgent it looks', 'js-support-ticket')); ?></span>
                            </label>
                            <label class="jsst-check">
                                <input type="checkbox" name="autosentiment" value="1" <?php checked(in_array(JSSTaitriage::OP_SENTIMENT, $jsst_auto, true)); ?> />
                                <span><?php echo esc_html(__('Read how the customer sounds, and flag the ones about to give up', 'js-support-ticket')); ?></span>
                            </label>
                            </div>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Mostly suggestions: the only thing changed on a ticket is a department or priority the customer left empty, and that is written in the ticket history.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('A department or priority the customer chose is never changed, and nobody is reassigned. Only a field the customer left blank is filled in from the reading, so the ticket reaches a queue at all - and the ticket history says the AI did it. Everything else, including the mood, is only shown to agents.', 'js-support-ticket')); ?>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Each run costs what one short request costs and appears on Usage & Cost like everything else.', 'js-support-ticket')); ?></p>
                        </fieldset>
                        <?php } ?>

                        <div class="jsst-btnrow">
                            <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('Save switches', 'js-support-ticket')); ?>" />
                        </div>
                    </div>
                </div>
            </form>

            <h2 class="jsst-groupheading" data-jsst-for="AIEngine"><?php echo esc_html(__('The answer engine', 'js-support-ticket')); ?></h2>
            <p class="jsst-lede" data-jsst-for="AIEngine"><?php echo esc_html(__('Pick the engine and add its key. Suggested articles and past replies work without one; everything else written by AI needs it.', 'js-support-ticket')); ?></p>

            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_action); ?>">
                <input type="hidden" name="aiengine" value="1" />

                <div class="jsst-card" id="AIEngine">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Which engine writes the answers', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('One engine for the whole help desk. Leave a key box empty to keep the saved key.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('One choice for the whole product — the Copilot, grounded answers and automatic replies all use it. A key box left empty means "leave it as it is"; the screen can never show you a stored key back.', 'js-support-ticket')); ?>
                        <?php if (is_array($jsst_test)) { ?>
                            <div class="jsst-card-tools">
                                <?php if (!empty($jsst_test['ok'])) { ?>
                                    <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php
                                        /* translators: %d: round-trip time in milliseconds */
                                        echo esc_html(sprintf(__('Answered in %d ms', 'js-support-ticket'), (int) $jsst_test['ms'])); ?></span>
                                <?php } else { ?>
                                    <span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span><?php echo esc_html(__('Last test failed', 'js-support-ticket')); ?></span>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="jsst-card-body">

                        <?php foreach ($jsst_enginerows as $jsst_id => $jsst_engine) {
                            $jsst_forget = wp_nonce_url(
                                admin_url('admin.php?page=aiagent&task=forgetaikey&action=jstask&engine=' . rawurlencode($jsst_id)),
                                'jsst-aiagent-forget'
                            );
                            ?>
                            <fieldset class="jsst-fieldset">
                                <legend class="jsst-fieldset-legend">
                                    <label class="jsst-check">
                                        <input type="radio" name="engine" value="<?php echo esc_attr($jsst_id); ?>" <?php checked($jsst_current, $jsst_id); ?> />
                                        <span><?php echo esc_html($jsst_engine['label']); ?></span>
                                    </label>
                                    <?php if (!empty($jsst_engine['recommended'])) { ?>
                                        <span class="jsst-pill jsst-pill-info"><?php echo esc_html(__('Recommended', 'js-support-ticket')); ?></span>
                                    <?php } ?>
                                    <?php if (empty($jsst_engine['laneopen'])) { ?>
                                        <span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span><?php
                                            $jsst_lanelabel = isset($jsst_lanes[$jsst_engine['lane']]['label']) ? $jsst_lanes[$jsst_engine['lane']]['label'] : $jsst_engine['lane'];
                                            /* translators: %s: the name of an AI lane */
                                            echo esc_html(sprintf(__('%s is switched off', 'js-support-ticket'), $jsst_lanelabel)); ?></span>
                                        <?php /* The lane lives on the other tab; say where. */ ?>
                                        <a class="jsst-act" href="#AISwitches"><?php echo esc_html(__('Turn it on', 'js-support-ticket')); ?></a>
                                    <?php } ?>
                                </legend>
                                <p class="jsst-fhelp"><?php echo esc_html($jsst_engine['blurb']); ?></p>

                                <div class="jsst-formgrid">
                                    <?php if ($jsst_id === 'local') { ?>
                                        <div class="jsst-frow">
                                            <label class="jsst-flabel" for="jsst-localendpoint"><?php echo esc_html(__('Address', 'js-support-ticket')); ?></label>
                                            <div class="jsst-fval"><input type="url" id="jsst-localendpoint" name="localendpoint" value="<?php echo esc_attr(isset($jsst_engines['endpoint']) ? $jsst_engines['endpoint'] : ''); ?>" placeholder="http://127.0.0.1:11434" /></div>
                                            <p class="jsst-fhelp"><?php echo esc_html(__('Your model server\'s base address. A private address is fine here.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('The base address of your model server. Ollama, LM Studio, vLLM and llama.cpp all answer the OpenAI chat-completions shape, so the path is added for you. A private address is expected here — this is not the crawler and it is not checked against the crawler\'s rules.', 'js-support-ticket')); ?>
                                        </div>
                                        <div class="jsst-frow">
                                            <label class="jsst-flabel" for="jsst-localmodel"><?php echo esc_html(__('Model name', 'js-support-ticket')); ?></label>
                                            <div class="jsst-fval"><input type="text" id="jsst-localmodel" name="localmodel" value="<?php echo esc_attr(isset($jsst_engines['localmodel']) ? $jsst_engines['localmodel'] : ''); ?>" placeholder="llama3.1:8b" /></div>
                                            <p class="jsst-fhelp"><?php echo esc_html(__('Exactly as your server names it. Free text rather than a list, because the models on your box are yours.', 'js-support-ticket')); ?></p>
                                        </div>
                                        <div class="jsst-frow">
                                            <label class="jsst-flabel" for="jsst-localtimeout"><?php echo esc_html(__('Give up after', 'js-support-ticket')); ?></label>
                                            <div class="jsst-fval"><input type="number" id="jsst-localtimeout" name="localtimeout" min="10" max="900" step="10" value="<?php echo esc_attr(isset($jsst_engines['localtimeout']) ? $jsst_engines['localtimeout'] : 300); ?>" /></div>
                                            <p class="jsst-fhelp"><?php echo esc_html(__('Seconds. A local model can take up to a minute to load, so allow plenty.', 'js-support-ticket')); ?></p>
                                            <?php /* PHP stops the request at its own limit whatever is set
                                                     here, so a longer wait is a promise nothing keeps. */
                                            $jsst_phplimit = (int) ini_get('max_execution_time');
                                            $jsst_localwait = isset($jsst_engines['localtimeout']) ? (int) $jsst_engines['localtimeout'] : 300;
                                            if ($jsst_phplimit > 0 && $jsst_localwait >= $jsst_phplimit) { ?>
                                                <p class="jsst-fhelp jsst-fhelp-warn"><?php echo esc_html(sprintf(
                                                    /* translators: 1: seconds set here, 2: the server's PHP time limit in seconds */
                                                    __('This server stops a request after %2$d seconds, so %1$d is never reached. Set it below %2$d, or ask your host to raise max_execution_time.', 'js-support-ticket'),
                                                    $jsst_localwait, $jsst_phplimit)); ?></p>
                                            <?php } ?>
                                            <?php JSSTlayout::why(__('Seconds. A first request after the model has been evicted from memory can spend most of a minute loading weights, which is why this is far longer than a hosted timeout.', 'js-support-ticket')); ?>
                                        </div>
                                    <?php } ?>

                                    <?php if (!empty($jsst_engine['models'])) { ?>
                                        <div class="jsst-frow">
                                            <label class="jsst-flabel" for="jsst-model-<?php echo esc_attr($jsst_id); ?>"><?php echo esc_html(__('Model', 'js-support-ticket')); ?></label>
                                            <div class="jsst-fval">
                                                <select id="jsst-model-<?php echo esc_attr($jsst_id); ?>" name="model">
                                                    <?php foreach ($jsst_engine['models'] as $jsst_modelid => $jsst_modelname) { ?>
                                                        <option value="<?php echo esc_attr($jsst_modelid); ?>" <?php selected($jsst_model, $jsst_modelid); ?>><?php echo esc_html($jsst_modelname); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <div class="jsst-frow">
                                        <label class="jsst-flabel" for="jsst-key-<?php echo esc_attr($jsst_id); ?>"><?php echo esc_html(__('Key', 'js-support-ticket')); ?></label>
                                        <div class="jsst-fval">
                                            <input type="password" id="jsst-key-<?php echo esc_attr($jsst_id); ?>" name="key_<?php echo esc_attr($jsst_id); ?>" value="" autocomplete="off"
                                                   placeholder="<?php echo esc_attr($jsst_engine['stored'] !== '' ? $jsst_engine['stored'] : $jsst_engine['keyhint']); ?>" />
                                        </div>
                                        <p class="jsst-fhelp">
                                            <?php if ($jsst_engine['stored'] !== '') {
                                                echo esc_html(__('A key is stored. Leave this empty to keep it.', 'js-support-ticket')); ?>
                                                <a href="<?php echo esc_url($jsst_forget); ?>"><?php echo esc_html(__('Forget it', 'js-support-ticket')); ?></a>
                                            <?php } else {
                                                echo esc_html($jsst_engine['keyhint']);
                                                /* The old Zywrap dashboard's "get a key" link now lives here,
                                                   where the key is actually entered. */
                                                if ($jsst_id === 'zywrap') { ?>
                                                    <a href="https://zywrap.com/register" target="_blank" rel="noopener"><?php echo esc_html(__('Get a free Zywrap key', 'js-support-ticket')); ?></a>
                                                <?php }
                                            } ?>
                                        </p>
                                    </div>
                                </div>
                            </fieldset>
                        <?php } ?>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('How answers are written', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow">
                                    <?php /* Not the language AI answers are written in - that is
                                             "Answer language" on Written answers. This one is only
                                             what the agent's Translate tool falls back to. */ ?>
                                    <label class="jsst-flabel" for="jsst-ailanguage"><?php echo esc_html(__('Translate into, by default', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><input type="text" id="jsst-ailanguage" name="ailanguage" value="<?php echo esc_attr(JSSTcopilot::defaultLanguage()); ?>" size="24" /></div>
                                    <p class="jsst-fhelp"><?php echo esc_html(__('What the agent\'s Translate tool uses when no language is picked. Write it as a word, for example English. The language of AI-written answers is set on Written answers.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('What Translate falls back to when an agent has not picked a language. Written as you would say it — English, Deutsch, العربية — because it goes into the request as a word, not a locale code.', 'js-support-ticket')); ?>
                                </div>
                            </div>
                        </fieldset>

                        <div class="jsst-btnrow">
                            <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('Save engine', 'js-support-ticket')); ?>" />
                            <a class="jsst-btn" href="<?php echo esc_url($jsst_testurl); ?>"><?php echo esc_html(__('Test connection', 'js-support-ticket')); ?></a>
                        </div>

                        <?php if (is_array($jsst_test) && !empty($jsst_test['detail'])) { ?>
                            <dl class="jsst-facts">
                                <dt><?php echo esc_html(__('Last test', 'js-support-ticket')); ?></dt>
                                <dd>
                                    <?php echo esc_html($jsst_test['detail']); ?>
                                    <span class="jsst-table-sub"><?php
                                        echo esc_html(sprintf(
                                            /* translators: 1: engine name, 2: how long ago the test ran */
                                            __('%1$s, %2$s ago', 'js-support-ticket'),
                                            isset($jsst_test['engine']) ? $jsst_test['engine'] : '',
                                            human_time_diff(isset($jsst_test['when']) ? (int) $jsst_test['when'] : time())
                                        )); ?></span>
                                </dd>
                            </dl>
                        <?php } ?>
                    </div>
                </div>
            </form>

            <div data-jsst-for="AIAgentSuggestions">
            <h2 class="jsst-groupheading"><?php echo esc_html(__('How it behaves', 'js-support-ticket')); ?></h2>
            <p class="jsst-lede">
                <?php echo esc_html(__('How the AI behaves once it is allowed: what it searches, how it writes and how closely it sticks to your content.', 'js-support-ticket')); ?>
            </p><?php JSSTlayout::why(__('Everything above decides what is allowed to happen and who does it. Everything below is how it behaves once it is allowed — what is searched, how it writes, and how strictly it is held to your own content.', 'js-support-ticket')); ?>
            </div>

            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_action); ?>">
                <input type="hidden" name="aibehaviour" value="1" />

                <?php
                /* Suggestions is core's own: getInstantResolveSearch() falls back
                   to getBasicFixSuggestions() when no addon claims the search
                   filter, so the feature runs - and must stay configurable - with
                   nothing installed. Only the three groups after it belong to the
                   addon. */
                $jsst_ir_addon = in_array('aiagent', jssupportticket::$_active_addons);

                /* Which sources the AI may answer from is core's register since
                   6.0-AI-02, not a row on any of these groups. It is summarised
                   below and edited on AI Agent > Knowledge Sources. */
                $jsst_ir_sources_on = class_exists('JSSTaisources')
                    ? JSSTaisources::approvedTypes() : array();
                ?>

                    <!-- ===================== SUGGESTIONS ===================== -->
                    <div class="jsst-card" id="AIAgentSuggestions">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html(__('Suggestions', 'js-support-ticket')); ?></h2>
                            <p class="jsst-card-sub"><?php echo esc_html(__('Shown on the ticket form while a customer types, so they can find the answer without opening a ticket at all.', 'js-support-ticket')); ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_sources#jsst-ai-ask')); ?>"><?php echo esc_html(__('Test a question', 'js-support-ticket')); ?></a></p>
                        </div>
                        <div class="jsst-card-body">
                            <div class="jsst-formgrid">
                        <?php
                        if(isset($jsst_cfg['aiagent_enable'])){
                            $jsst_title = esc_html(__('Show suggestions', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::select('aiagent_enable', $jsst_yesno, $jsst_cfg['aiagent_enable']);
                            $jsst_description = esc_html(__('Search your content as the customer types and offer matching answers.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        if(isset($jsst_cfg['aiagent_min_chars'])){
                            $jsst_title = esc_html(__('Start searching after', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_min_chars', $jsst_cfg['aiagent_min_chars'], array('min' => 1, 'max' => 200, 'step' => 1));
                            $jsst_description = esc_html(__('Characters typed across the subject and message before searching begins. It then searches 0.8 seconds after the customer stops typing. 15 is a good default.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        /* Rendered unconditionally rather than behind an isset() on a
                           config row, because there is no longer a row to test: the
                           list moved into core's own option with 6.0-AI-02 and this
                           block only reports it. */
                        if (true) {
                            /* This was six tickboxes writing a config row. Since
                               6.0-AI-02 the same row is owned by AI Agent > Knowledge Sources,
                               which governs the same six sources plus the individual documents
                               inside them and reports whether each is actually answering. Two
                               screens writing one setting is how they end up disagreeing, so
                               this one reports and links rather than edits. The row is still
                               read from here because it is the same row - it is only decided
                               somewhere better. */
                            $jsst_ir_names = array(
                                'kb'      => __('Knowledge Base articles', 'js-support-ticket'),
                                'faq'     => __('FAQs', 'js-support-ticket'),
                                'canned'  => __('Canned responses', 'js-support-ticket'),
                                'posts'   => __('WordPress posts and pages', 'js-support-ticket'),
                                'scraped' => __('Indexed documentation', 'js-support-ticket'),
                                'tickets' => __('Resolved tickets', 'js-support-ticket'),
                            );
                            /* Asked of the register rather than read from the row this
                               block sits in: since 6.0-AI-02 the row is legacy and the
                               register is the answer, and a summary that can disagree
                               with the screen it points at is worse than no summary. */
                            $jsst_ir_approved = $jsst_ir_sources_on;
                            $jsst_ir_listed = array();
                            foreach ($jsst_ir_approved as $jsst_ir_k) {
                                if (isset($jsst_ir_names[$jsst_ir_k])) $jsst_ir_listed[] = $jsst_ir_names[$jsst_ir_k];
                            }
                            $jsst_field = '<div class="jsst-chips">';
                            if (empty($jsst_ir_listed)) {
                                $jsst_field .= '<span class="jsst-chip">' . esc_html(__('Nothing is approved', 'js-support-ticket')) . '</span>';
                            } else {
                                foreach ($jsst_ir_listed as $jsst_ir_name) {
                                    $jsst_field .= '<span class="jsst-chip">' . esc_html($jsst_ir_name) . '</span>';
                                }
                            }
                            $jsst_field .= '</div><div class="jsst-ai-sources-link"><a class="jsst-btn" href="'
                                        . esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_sources')) . '">'
                                        . esc_html(__('Knowledge Sources', 'js-support-ticket')) . '</a></div>';
                            $jsst_title = esc_html(__('Search these', 'js-support-ticket'));
                            $jsst_description = esc_html(__('Chosen under AI Agent > Knowledge Sources.', 'js-support-ticket'));
                            $jsst_why = __('Set under AI Agent > Knowledge Sources, where individual articles and pages can be approved or excluded as well. The same list governs the suggestions on the ticket form and anything the AI is allowed to answer from.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_max_results'])){
                            $jsst_title = esc_html(__('How many to show', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_max_results', $jsst_cfg['aiagent_max_results'], array('min' => 1, 'max' => 10, 'step' => 1));
                            $jsst_description = esc_html(__('Most suggestions shown in total, from all sources together, between 1 and 10. Weak matches are left out, so fewer may show.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        // Addon-gated even though the row is core's. Nothing in core writes
                        // the analytics table and nothing reads this setting, so without the
                        // addon it is a switch wired to nothing - pointing, in its own
                        // description, at an Overview screen the side menu does not render.
                        // storeConfiguration() only walks the keys that were posted, so
                        // leaving the field out preserves the stored value rather than
                        // clearing it.
                        if($jsst_ir_addon && isset($jsst_cfg['aiagent_analytics'])){
                            $jsst_title = esc_html(__('Record what was shown', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::select('aiagent_analytics', $jsst_yesno, $jsst_cfg['aiagent_analytics']);
                            $jsst_description = esc_html(__('Track which suggestions customers saw and opened, for the AI Agent overview.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        ?>
                        </div>
                    </div>
                    </div>

                <?php if($jsst_ir_addon){ ?>

                    <?php
                    // Option lists used below. Built here rather than inline so
                    // the field definitions stay readable.
                    $jsst_ir_tones = array(
                        (object) array('id' => 'professional', 'text' => esc_html(__('Professional', 'js-support-ticket'))),
                        (object) array('id' => 'friendly',     'text' => esc_html(__('Friendly', 'js-support-ticket'))),
                        (object) array('id' => 'concise',      'text' => esc_html(__('Concise', 'js-support-ticket'))),
                        (object) array('id' => 'empathetic',   'text' => esc_html(__('Empathetic', 'js-support-ticket'))),
                    );
                    $jsst_ir_modes = array(
                        (object) array('id' => 'strict_kb',    'text' => esc_html(__('Only answer from my content (recommended)', 'js-support-ticket'))),
                        (object) array('id' => 'kb_preferred', 'text' => esc_html(__('Prefer my content, allow drafts without it', 'js-support-ticket'))),
                        (object) array('id' => 'open',         'text' => esc_html(__('Let the AI answer freely (not recommended)', 'js-support-ticket'))),
                    );
                    /* The engine is chosen on AI Agent Settings and nowhere
                       else. (Roadmap 6.0-AI-01) A second picker here wrote a
                       config row nothing reads any more, and two screens
                       offering the same choice in two vocabularies is how they
                       come to disagree - the lesson 4.5-FE-03 was merged for. */

                    /* The audience and department pickers, the delay and the
                       fallback moved to AI Agent > Autopilot with 6.0-AI-06, and
                       their option lists went with them - JSSTairollout::delays()
                       and ::fallbacks() are now the only place either is written,
                       so the form and the enforcement cannot disagree. The
                       approved-source list is read further up, with the
                       Suggestions section that uses it. */

                    /* With no usable engine every setting below is inert - the
                       provider refuses before it calls out, the ticket form
                       simply shows no written answer, and Autopilot returns
                       without logging anything. Switching these on and seeing
                       nothing happen is the whole failure, so it is said here
                       rather than left to be discovered.

                       Asked of JSSTaiengine::usable(), which is the same
                       question the provider asks at the moment of use - lane
                       open AND engine configured - so this notice cannot
                       disagree with what actually runs. It deliberately does not
                       name Zywrap: the engine is a site-wide choice now, and a
                       desk on a local model would be told to go and find a key
                       for a service it is not using. (Roadmap 6.0-AI-01) */
                    $jsst_ir_nokey = '';
                    if (!class_exists('JSSTaiengine') || !JSSTaiengine::usable()) {
                        /* Pointed at whichever of the two is actually missing:
                           sending somebody to the engine when the master switch
                           is what is off leaves them configuring a key that
                           still will not be used. */
                        $jsst_ir_off = (class_exists('JSSTaipolicy') && !JSSTaipolicy::enabled());
                        $jsst_ir_reason = $jsst_ir_off
                            ? esc_html(__('AI is switched off for this site.', 'js-support-ticket'))
                            : esc_html(__('No answer engine is set up.', 'js-support-ticket'));
                        $jsst_ir_nokey = '<div class="jsst-ir-warn"><strong>'
                            . $jsst_ir_reason . '</strong> '
                            . esc_html(__('Nothing on this tab will run until it is. Suggestions from your own content keep working.', 'js-support-ticket'))
                            . ' <a href="' . esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_settings' . ($jsst_ir_off ? '#AISwitches' : '#AIEngine'))) . '">'
                            . ($jsst_ir_off
                                ? esc_html(__('Switch AI on', 'js-support-ticket'))
                                : esc_html(__('Set up the engine', 'js-support-ticket'))) . '</a></div>';
                    }
                    ?>

                    <!-- ===================== AI ASSISTANT ===================== -->
                    <div class="jsst-card" id="AIAgentAI">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html(__('Written answers', 'js-support-ticket')); ?></h2>
                            <p class="jsst-card-sub"><?php echo esc_html(__('Settings shared by the written answer on the ticket form and the automatic replies.', 'js-support-ticket')); ?></p>
                        </div>
                        <div class="jsst-card-body">
                            <div class="jsst-formgrid">
                        <?php
                        echo wp_kses_post($jsst_ir_nokey);
                        ?>
                        <?php
                        if(isset($jsst_cfg['aiagent_ai_enable'])){
                            $jsst_title = esc_html(__('Write an answer on the ticket form', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::select('aiagent_ai_enable', $jsst_yesno, $jsst_cfg['aiagent_ai_enable']);
                            $jsst_description = esc_html(__('Show a short written answer above the suggested links, based only on the content that was found.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        if(isset($jsst_cfg['aiagent_ai_sources_limit'])){
                            $jsst_title = esc_html(__('Passages for the ticket-form answer', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_ai_sources_limit', $jsst_cfg['aiagent_ai_sources_limit'], array('min' => 1, 'max' => 8, 'step' => 1));
                            $jsst_description = esc_html(__('How many matching passages the AI may use for the answer shown on the ticket form, between 1 and 8. Automatic replies have their own limit under Advanced.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        if(isset($jsst_cfg['aiagent_ai_tone'])){
                            $jsst_title = esc_html(__('Tone', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::select('aiagent_ai_tone', $jsst_ir_tones, $jsst_cfg['aiagent_ai_tone']);
                            $jsst_description = esc_html(__('How replies should read. Used everywhere the AI writes.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        if(isset($jsst_cfg['aiagent_ai_language'])){
                            $jsst_title = esc_html(__('Answer language', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::text('aiagent_ai_language', $jsst_cfg['aiagent_ai_language']);
                            $jsst_description = esc_html(__('The language AI-written answers use. Enter a language name, or "auto" to reply in whichever language the customer wrote in.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        /* The engine is its own tab on this same screen; a card
                           here pointing at it said the tab bar twice. */
                        ?>
                        </div>
                    </div>
                    </div>

                    <?php /* Automatic replies was a summary of Autopilot plus two fields
                             that only mean anything to it - who replies are signed as and
                             the words that stop one. All of it is edited on Autopilot now,
                             so one screen holds every rule about answering by itself. */ ?>

                    <!-- ===================== ADVANCED ===================== -->
                    <div class="jsst-card" id="AIAgentAdvanced">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html(__('Advanced', 'js-support-ticket')); ?></h2>
                            <?php /* "Test Retrieval" named a screen that does not exist; the
                                     preview step on Set up is the one that shows what is
                                     found for a question and what would be written. */ ?>
                            <p class="jsst-card-sub"><?php echo esc_html(__('How strictly the AI sticks to your content.', 'js-support-ticket')); ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_setup')); ?>"><?php echo esc_html(__('Try a question on Set up first', 'js-support-ticket')); ?></a></p><?php JSSTlayout::why(__('These control how strictly the AI is held to your content. The defaults are deliberately cautious; ask a real question on Set up, which shows the passages found and the answer that would be written, before and after any change.', 'js-support-ticket')); ?>
                        </div>
                        <div class="jsst-card-body">
                            <div class="jsst-formgrid">
                        <?php
                        if(isset($jsst_cfg['aiagent_grounding_mode'])){
                            $jsst_title = esc_html(__('Where answers may come from', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::select('aiagent_grounding_mode', $jsst_ir_modes, $jsst_cfg['aiagent_grounding_mode']);
                            $jsst_description = esc_html(__('Keep the first option so the AI answers only from your content.', 'js-support-ticket'));
                            $jsst_why = __('Answering freely is how an AI invents steps that do not exist. Leave this on the first option unless you have a specific reason not to.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_min_coverage_deflect'])){
                            $jsst_title = esc_html(__('Match needed for a suggestion', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_min_coverage_deflect', $jsst_cfg['aiagent_min_coverage_deflect'], array('min' => 10, 'max' => 100, 'step' => 1));
                            $jsst_description = esc_html(__('How much of the question a page must cover to be suggested (10 to 100).', 'js-support-ticket'));
                            $jsst_why = __('Percentage of the question a page must cover to be suggested, from 10 to 100. A loose value is fine here, since the customer can simply ignore a poor suggestion.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_min_coverage_reply'])){
                            $jsst_title = esc_html(__('Match needed for a reply', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_min_coverage_reply', $jsst_cfg['aiagent_min_coverage_reply'], array('min' => 20, 'max' => 100, 'step' => 1));
                            $jsst_description = esc_html(__('The same, for content the AI may state as fact (20 to 100). Keep it above the suggestion value.', 'js-support-ticket'));
                            $jsst_why = __('The same measure, but for content the AI may state as fact, from 20 to 100. Keep this higher than the suggestion threshold.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_require_citation'])){
                            $jsst_title = esc_html(__('Require sources', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::select('aiagent_require_citation', $jsst_yesno, $jsst_cfg['aiagent_require_citation']);
                            $jsst_description = esc_html(__('Reject replies that cite nothing, or cite something they were not given.', 'js-support-ticket'));
                            $jsst_why = __('Reject a reply that cites nothing, or cites something that was not given to it. Models invent references as readily as they invent facts.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_min_overlap'])){
                            $jsst_title = esc_html(__('Minimum grounding overlap', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_min_overlap', $jsst_cfg['aiagent_min_overlap'], array('min' => 0, 'max' => 100, 'step' => 1));
                            $jsst_description = esc_html(__('Share of a reply\'s specifics that must appear in your content (0 to 100). Ships at 65; a reply below it is held as a draft.', 'js-support-ticket'));
                            $jsst_why = __('How many of a reply\'s specifics - button names, menu paths, time limits - must appear in your own content, from 0 to 100. A reply below this is held as a draft instead of being sent. It ships at 65; the overview records this figure for every reply, so move it from your own replies rather than guessing.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_max_chunks'])){
                            $jsst_title = esc_html(__('Passages for automatic replies', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_max_chunks', $jsst_cfg['aiagent_max_chunks'], array('min' => 1, 'max' => 8, 'step' => 1));
                            $jsst_description = esc_html(__('How many pieces of your content the AI may be shown when writing a reply to a ticket, from 1 to 8. The answer on the ticket form has its own limit under Written answers.', 'js-support-ticket'));
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description);
                        }
                        /* Retrieval tuning - sizes, budgets and the variety knob -
                           folded away: the settings above decide whether an answer
                           may go out, these only shape what it is built from, and
                           two of them rebuild the index. */
                        echo '</div><details class="jsst-advanced-more"><summary>'
                            . esc_html(__('Show advanced tuning', 'js-support-ticket'))
                            . '</summary><div class="jsst-formgrid">';
                        if(isset($jsst_cfg['aiagent_char_budget'])){
                            $jsst_title = esc_html(__('Largest passage (characters)', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_char_budget', $jsst_cfg['aiagent_char_budget'], array('min' => 1000, 'max' => 20000, 'step' => 1));
                            $jsst_description = esc_html(__('Largest passage taken from one document (1000 to 20000 characters).', 'js-support-ticket'));
                            $jsst_why = __('Largest amount of a single document that may be extracted as one passage, from 1000 to 20000. The overall size of a reply prompt is set by the token budget below.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_token_budget'])){
                            $jsst_title = esc_html(__('Token budget per reply', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_token_budget', $jsst_cfg['aiagent_token_budget'], array('min' => 200, 'max' => 8000, 'step' => 1));
                            $jsst_description = esc_html(__('Most tokens of your content sent with one reply (200 to 8000). This is what controls cost.', 'js-support-ticket'));
                            $jsst_why = __('Most tokens of your own content that may be sent with one reply, from 200 to 8000. This is what the AI engine actually charges for, so it is the setting that controls cost. Lower it to spend less per ticket; the best-matching passages are kept and the weakest are dropped first.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_mmr_lambda'])){
                            $jsst_title = esc_html(__('Prefer variety', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_mmr_lambda', $jsst_cfg['aiagent_mmr_lambda'], array('min' => 30, 'max' => 100, 'step' => 1));
                            $jsst_description = esc_html(__('From 30 to 100. Lower values skip passages that repeat each other.', 'js-support-ticket'));
                            $jsst_why = __('From 30 to 100. At 100 the highest scoring passages are used even when they repeat each other, which on a large documentation site often means four versions of the same page. Lower values spend the budget on passages that add something new.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_chunk_chars'])){
                            $jsst_title = esc_html(__('Passage size', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_chunk_chars', $jsst_cfg['aiagent_chunk_chars'], array('min' => 300, 'max' => 2400, 'step' => 1));
                            $jsst_description = esc_html(__('Characters per indexed passage (300 to 2400). Changing it rebuilds the search index.', 'js-support-ticket'));
                            $jsst_why = __('Characters per indexed passage, from 300 to 2400. Smaller passages are more precise but can separate step three of a procedure from steps one and two. Changing this rebuilds the search index.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        if(isset($jsst_cfg['aiagent_chunk_overlap'])){
                            $jsst_title = esc_html(__('Passage overlap', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::number('aiagent_chunk_overlap', $jsst_cfg['aiagent_chunk_overlap'], array('min' => 0, 'max' => 800, 'step' => 1));
                            $jsst_description = esc_html(__('Characters shared by neighbouring passages. Changing it rebuilds the search index.', 'js-support-ticket'));
                            $jsst_why = __('Characters repeated between neighbouring passages, up to a third of the passage size. Overlap stops an answer that spans a boundary from being lost by both sides. Changing this rebuilds the search index.', 'js-support-ticket');
                            jsstAiSettingField($jsst_title, $jsst_field, $jsst_description, $jsst_why); $jsst_why = '';
                        }
                        ?>
                        </div></details>
                    </div>
                    </div>

                <?php } // end of the addon-only sections ?>

                <?php /* One button for all four cards, because they are one form
                         and one save. Outside the cards rather than in the last
                         one, which would read as saving only that card. */ ?>
                <div class="jsst-btnrow" data-jsst-for="AIAgentSuggestions AIAgentAI AIAgentAdvanced">
                    <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('Save behaviour', 'js-support-ticket')); ?>" />
                </div>
            </form>

        </div>
    </div>
</div>
<?php
/* The tab switcher. Shows the section named by the tab (its #anchor) and hides
   the others; anything marked data-jsst-for="A B" is shown with any of those
   sections. Runs only when the tab bar exists, so without JavaScript nothing is
   ever hidden. */
$jsst_settingtabs_js = "
(function () {
    var bar = document.querySelector('[data-jsst-settingtabs]');
    if (!bar) { return; }
    var tabs = Array.prototype.slice.call(bar.querySelectorAll('a.jsst-tab'));
    var ids = tabs.map(function (a) { return a.getAttribute('href').slice(1); });
    var panels = ids.map(function (id) { return document.getElementById(id); });
    var extras = Array.prototype.slice.call(document.querySelectorAll('[data-jsst-for]'));
    function show(id, focus) {
        if (ids.indexOf(id) < 0) { id = ids[0]; }
        panels.forEach(function (p, i) { if (p) { p.hidden = (ids[i] !== id); } });
        extras.forEach(function (el) { el.hidden = (' ' + el.getAttribute('data-jsst-for') + ' ').indexOf(' ' + id + ' ') < 0; });
        tabs.forEach(function (a, i) {
            var on = ids[i] === id;
            a.classList.toggle('is-on', on);
            if (on) { a.setAttribute('aria-current', 'page'); } else { a.removeAttribute('aria-current'); }
        });
        if (focus && history.replaceState) { history.replaceState(null, '', '#' + id); }
    }
    tabs.forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); show(a.getAttribute('href').slice(1), true); });
    });
    window.addEventListener('hashchange', function () { show(location.hash.slice(1), false); });
    show(location.hash.slice(1), false);
})();
/* Passage size and overlap rebuild the search index when saved; ask first,
   so it is never a side effect of saving something else on the same form. */
(function () {
    var size = document.getElementById('aiagent_chunk_chars');
    var lap = document.getElementById('aiagent_chunk_overlap');
    if (!size || !size.form) { return; }
    size.form.addEventListener('submit', function (e) {
        var changed = (size.value !== size.defaultValue) || (lap && lap.value !== lap.defaultValue);
        if (changed && !window.confirm(" . wp_json_encode(__('Passage size or overlap has changed. Saving rebuilds the search index, and answers may be thinner until it finishes. Save anyway?', 'js-support-ticket')) . ")) {
            e.preventDefault();
        }
    });
})();
";
wp_add_inline_script('js-support-ticket-main-js', $jsst_settingtabs_js);
?>
