<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Autopilot. (Roadmap 6.0-AI-06)
 *
 * The screen somebody opens on the day they decide to let this write to their
 * customers, and the one they open again on the day it goes wrong. Both jobs
 * are on the page on purpose: the way out is at the top, above the settings,
 * because a page that puts the stop button below three fieldsets is a page that
 * has never been used in anger.
 *
 * Three things it does that a settings form does not:
 *
 *   It says what the rules *mean*. Choosing "one department" tells you what you
 *   chose; the coverage band tells you that it is sixty per cent of the desk.
 *   The gap between those two sentences is where a cautious rollout turns out
 *   not to have been one.
 *
 *   It names every refusal. The engine used to log "safety rules triggered"
 *   whichever of five rules had fired, so the commonest question about this
 *   feature — why is it not answering anything — had no answer on any screen.
 *
 *   It writes the reversal path down, from JSSTairollout::reversal() rather
 *   than from prose typed here, so the steps on the page are the steps the
 *   release can actually perform.
 */
if (!class_exists('JSSTairollout')) {
    echo esc_html(__('The autopilot rollout register is not available on this site.', 'js-support-ticket'));
    return;
}

$jsst_policy    = isset(jssupportticket::$jsst_data['aipolicy']) ? jssupportticket::$jsst_data['aipolicy'] : array();
$jsst_rules     = isset(jssupportticket::$jsst_data['airules']) ? jssupportticket::$jsst_data['airules'] : array();
$jsst_state     = isset(jssupportticket::$jsst_data['aistate']) ? jssupportticket::$jsst_data['aistate'] : array();
$jsst_paused    = !empty(jssupportticket::$jsst_data['aipaused']);
$jsst_audiences = isset(jssupportticket::$jsst_data['aiaudiences']) ? jssupportticket::$jsst_data['aiaudiences'] : array();
$jsst_delays    = isset(jssupportticket::$jsst_data['aidelays']) ? jssupportticket::$jsst_data['aidelays'] : array();
$jsst_fallbacks = isset(jssupportticket::$jsst_data['aifallbacks']) ? jssupportticket::$jsst_data['aifallbacks'] : array();
$jsst_coverage  = isset(jssupportticket::$jsst_data['aicoverage']) ? jssupportticket::$jsst_data['aicoverage'] : array();
$jsst_reversal  = isset(jssupportticket::$jsst_data['aireversal']) ? jssupportticket::$jsst_data['aireversal'] : array();
$jsst_depts     = isset(jssupportticket::$jsst_data['aidepts']) ? jssupportticket::$jsst_data['aidepts'] : array();
$jsst_waiting   = isset(jssupportticket::$jsst_data['aiwaiting']) ? (int) jssupportticket::$jsst_data['aiwaiting'] : 0;

$jsst_saveurl  = wp_nonce_url(admin_url('admin.php?page=aiagent&task=saveairollout&action=jstask'), 'jsst-aiagent-rollout');
$jsst_pauseurl = wp_nonce_url(admin_url('admin.php?page=aiagent&task=pauseautopilot&action=jstask'
                    . ($jsst_paused ? '&resume=1' : '')), 'jsst-aiagent-pause');

$jsst_status  = isset($jsst_state['state']) ? $jsst_state['state'] : 'off';
$jsst_sampled = isset($jsst_coverage['sampled']) ? (int) $jsst_coverage['sampled'] : 0;
$jsst_covered = isset($jsst_coverage['eligible']) ? (int) $jsst_coverage['eligible'] : 0;
$jsst_share   = ($jsst_sampled > 0) ? (int) round(($jsst_covered / $jsst_sampled) * 100) : 0;

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Automatic Answers', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_autopilot'); } ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('Who gets an answer without a person reading it first, how many, and how fast. Start with one department.', 'js-support-ticket')); ?>
            </p><?php JSSTlayout::why(__('Who gets an answer without a person reading it first, how many they can get, and how fast. Roll it out to one department for a fortnight before you roll it out to the desk.', 'js-support-ticket')); ?>

            <?php /* The state and the way out, above the settings. */ ?>
            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Right now', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <?php if ($jsst_status === 'ok') { ?>
                            <span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span><?php echo esc_html(__('Sending', 'js-support-ticket')); ?></span>
                        <?php } elseif ($jsst_status === 'paused') { ?>
                            <span class="jsst-pill jsst-pill-info"><span class="jsst-dot"></span><?php echo esc_html(__('Paused', 'js-support-ticket')); ?></span>
                        <?php } else { ?>
                            <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Off', 'js-support-ticket')); ?></span>
                        <?php } ?>
                        <a class="jsst-pill jsst-pill-info" href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_approvals')); ?>">
                            <?php echo esc_html(sprintf(
                                /* translators: %s: how many answers are waiting for a person */
                                __('Approvals (%s waiting)', 'js-support-ticket'), number_format_i18n($jsst_waiting)
                            )); ?>
                        </a>
                    </div>
                </div>
                <div class="jsst-card-body">
                    <p class="jsst-hint"><?php echo esc_html(isset($jsst_state['detail']) ? $jsst_state['detail'] : ''); ?></p>

                    <?php /* One click, its own form, its own nonce: this is the
                             button somebody presses in a hurry, and it must not
                             depend on the rest of the page being right. */ ?>
                    <div class="jsst-btnrow">
                        <?php if ($jsst_paused) { ?>
                            <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url($jsst_pauseurl); ?>"><?php echo esc_html(__('Resume automatic answers', 'js-support-ticket')); ?></a>
                            <span class="jsst-formfoot-note"><?php echo esc_html(__('Anything that was in flight is waiting on Approvals. Resuming does not release it.', 'js-support-ticket')); ?></span><?php JSSTlayout::why(__('Anything that was in flight when you paused is waiting on the Approvals screen. Resuming does not release it — it is waiting for a person, which is what pausing asked for.', 'js-support-ticket')); ?>
                        <?php } else { ?>
                            <a class="jsst-btn" href="<?php echo esc_url($jsst_pauseurl); ?>"><?php echo esc_html(__('Pause automatic answers', 'js-support-ticket')); ?></a>
                            <span class="jsst-formfoot-note"><?php echo esc_html(__('Stops at once and changes no setting. Anything in flight is held for a person.', 'js-support-ticket')); ?></span><?php JSSTlayout::why(__('Stops at once and changes no setting, so resuming restores exactly what was running. Anything already in flight is held for a person rather than sent.', 'js-support-ticket')); ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php /* What the rules cover, measured rather than described. */ ?>
            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('What these rules cover', 'js-support-ticket')); ?></h2>
                </div>
                <div class="jsst-card-body">
                    <?php if ($jsst_sampled === 0) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(__('No tickets to measure yet', 'js-support-ticket')); ?></p>
                            <p class="jsst-empty-text"><?php echo esc_html(__('Once this desk has taken a few tickets, this band says how many of them these rules would have let through.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } else { ?>
                        <p class="jsst-hint"><?php echo esc_html(sprintf(
                            /* translators: %d: how many recent tickets were checked against the rules */
                            __('The last %d tickets, run through the rules below as if automatic answering were on — so this reads the same whether you are planning a rollout or running one. A ticket counted here would have reached the model; whether the answer was then good enough to send is decided by the confidence floor, and measured on Shadow Mode.', 'js-support-ticket'),
                            $jsst_sampled
                        )); ?></p>

                        <div class="jsst-metrics">
                            <div class="jsst-metric">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_share . '%'); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Of recent tickets included', 'js-support-ticket')); ?></span>
                            </div>
                            <div class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n($jsst_covered)); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Would reach the AI', 'js-support-ticket')); ?></span>
                            </div>
                            <div class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n($jsst_sampled - $jsst_covered)); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Left to a person', 'js-support-ticket')); ?></span>
                            </div>
                        </div>

                        <?php if (!empty($jsst_coverage['reasons'])) { ?>
                            <div class="jsst-table-wrap">
                                <table class="jsst-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo esc_html(__('Why they were left to a person', 'js-support-ticket')); ?></th>
                                            <th class="jsst-num"><?php echo esc_html(__('Tickets', 'js-support-ticket')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($jsst_coverage['reasons'] as $jsst_reason) { ?>
                                        <tr>
                                            <th scope="row"><span class="jsst-table-name"><?php echo esc_html($jsst_reason['detail']); ?></span></th>
                                            <td class="jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_reason['count'])); ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('The rollout', 'js-support-ticket')); ?></h2>

            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_saveurl); ?>">
                <?php /* The marker that tells an empty tickbox from a lost body:
                         excluding every audience is a real choice, and it is the
                         one that stops automation dead. */ ?>
                <input type="hidden" name="airollout" value="1" />

                <div class="jsst-card">
                    <div class="jsst-card-body">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Automatic answering', 'js-support-ticket')); ?></legend>
                            <label class="jsst-check">
                                <input type="checkbox" name="enabled" value="1" <?php checked(!empty($jsst_rules['enabled'])); ?> />
                                <span><?php echo esc_html(__('Let the AI work on new tickets', 'js-support-ticket')); ?></span>
                            </label>
                            <?php /* Says what the switch does rather than what
                                     it sounds like: off means nothing is written
                                     at all, not that answers are written and
                                     held. Holding them is the Approvals mode,
                                     and somebody who wants that and unticks this
                                     gets silence with no explanation anywhere. */ ?>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Off means no answer is written for new tickets.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('With this off nothing is written for a new ticket at all. To have answers written but never sent — the way to judge quality on real tickets — leave this on and set Approvals to propose only.', 'js-support-ticket')); ?>
                            <p class="jsst-fhelp">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_approvals')); ?>"><?php echo esc_html(__('Approvals: what happens to an answer once it is written', 'js-support-ticket')); ?></a>
                            </p>

                            <label class="jsst-check">
                                <input type="checkbox" name="followups" value="1" <?php checked(!empty($jsst_rules['followups'])); ?> />
                                <span><?php echo esc_html(__('Answer follow-up messages too, not only the first one', 'js-support-ticket')); ?></span>
                            </label>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Off answers first messages only; when the customer writes back, a person takes over.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Off is the cautious half-step: first messages are answered automatically, and any customer who writes back gets a person. Automation always stands down once a human from your side has replied.', 'js-support-ticket')); ?>
                        </fieldset>
                    </div>
                </div>

                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Who it happens to', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Audiences', 'js-support-ticket')); ?></legend>
                            <?php foreach ($jsst_audiences as $jsst_key => $jsst_def) { ?>
                                <label class="jsst-check">
                                    <input type="checkbox" name="audiences[]" value="<?php echo esc_attr($jsst_key); ?>"
                                        <?php checked(in_array($jsst_key, (array) $jsst_rules['audiences'], true)); ?> />
                                    <span><?php echo esc_html($jsst_def['label']); ?></span>
                                </label>
                                <p class="jsst-fhelp"><?php echo esc_html($jsst_def['blurb']); ?></p>
                            <?php } ?>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Departments', 'js-support-ticket')); ?></legend>
                            <?php if (empty($jsst_depts)) { ?>
                                <p class="jsst-fhelp"><?php echo esc_html(__('This site has no departments, so every ticket is in scope.', 'js-support-ticket')); ?></p>
                            <?php } else { ?>
                                <p class="jsst-fhelp"><?php echo esc_html(__('Tick none for every department. Starting with one is safest.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Tick none to include every department. Ticking one is the safest way to start: pick the one whose answers are already written down.', 'js-support-ticket')); ?>
                                <div class="jsst-checkgrid">
                                    <?php foreach ($jsst_depts as $jsst_id => $jsst_name) { ?>
                                        <label class="jsst-checkcell">
                                            <input type="checkbox" name="departments[]" value="<?php echo esc_attr($jsst_id); ?>"
                                                <?php checked(in_array((int) $jsst_id, (array) $jsst_rules['departments'], true)); ?> />
                                            <span><?php echo esc_html($jsst_name); ?></span>
                                        </label>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </fieldset>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Never answer these customers', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-full">
                                    <label class="jsst-flabel" for="jsst-ai-blocked"><?php echo esc_html(__('Addresses and domains, one per line', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><textarea id="jsst-ai-blocked" name="blocked" rows="3"><?php echo esc_textarea(implode("\n", (array) $jsst_rules['blocked'])); ?></textarea></div>
                                </div>
                            </div>
                            <p class="jsst-fhelp"><?php echo esc_html(__('An address matches exactly; a domain also matches its subdomains.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('An address is matched exactly; a domain matches that domain and anything under it. A bare word with no dot matches any address containing it.', 'js-support-ticket')); ?>

                            <?php /* Each entry with what it will actually match.
                                     A list somebody typed in a hurry is the one
                                     place a silent misreading is only ever found
                                     by the reply it failed to stop. */ ?>
                            <?php if (!empty($jsst_rules['blocked'])) { ?>
                                <div class="jsst-chips">
                                    <?php foreach ((array) $jsst_rules['blocked'] as $jsst_entry) { ?>
                                        <span class="jsst-chip"><?php echo esc_html($jsst_entry); ?> — <?php echo esc_html(JSSTairollout::describeBlocked($jsst_entry)); ?></span>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </fieldset>

                        <?php /* Moved here from AI Agent Settings so that every rule
                                 about answering by itself is on one screen. Read
                                 straight from the table, as Settings does, so a save
                                 that redirects back shows what was just written. */
                        $jsst_apcfg = array();
                        foreach ((array) jssupportticket::$_db->get_results(
                                "SELECT configname, configvalue FROM `" . jssupportticket::$_db->prefix
                                . "js_ticket_config` WHERE configname IN ('aiagent_autopilot_display_name', 'aiagent_autopilot_blacklist_keywords')") as $jsst_row) {
                            $jsst_apcfg[$jsst_row->configname] = $jsst_row->configvalue;
                        } ?>

                        <?php if (isset($jsst_apcfg['aiagent_autopilot_blacklist_keywords'])) { ?>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Never answer if it mentions', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow jsst-frow-full">
                                    <label class="jsst-flabel" for="aiagent_autopilot_blacklist_keywords"><?php echo esc_html(__('Words, separated by commas or one per line', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><textarea id="aiagent_autopilot_blacklist_keywords" name="aiagent_autopilot_blacklist_keywords" rows="3"><?php echo esc_textarea(rtrim((string) $jsst_apcfg['aiagent_autopilot_blacklist_keywords'], " ,\r\n")); ?></textarea></div>
                                </div>
                            </div>
                            <p class="jsst-fhelp"><?php echo esc_html(__('A ticket containing any of these always goes to a person.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('A ticket containing any of these always goes to a person. These are added to the questions JS Help Desk never automates on any site — money, law, identity and credentials — never substituted for them, so emptying this box does not weaken the shipped rule.', 'js-support-ticket')); ?>
                        </fieldset>
                        <?php } ?>

                        <?php if (class_exists('JSSTchannels')) {
                            $jsst_chrefused = JSSTchannels::aiRefused();
                            $jsst_chlist = array_filter(JSSTchannels::channels(), function ($jsst_c) { return !empty($jsst_c['present']); });
                            if (count($jsst_chlist) > 1) { ?>
                        <fieldset class="jsst-fieldset">
                            <?php /* Tells the handler these boxes were on the page, so an
                                     unticked one means "answer here" and an absent one
                                     means "leave it as it was". */ ?>
                            <input type="hidden" name="aichannels" value="1" />
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Channels a person must answer', 'js-support-ticket')); ?></legend>
                            <p class="jsst-fhelp"><?php echo esc_html(__('Tick a channel to keep automatic answers off it. These only switch answering off, never on.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Customers reach you more than one way, and they do not all want the same thing. Somebody waiting in a chat widget behaves nothing like somebody who sent an email and went away, so you can let the engine answer one and insist a person handles the other. Ticking none does not switch anything on — the switches on AI Agent Settings still decide that.', 'js-support-ticket')); ?>
                            <?php foreach ($jsst_chlist as $jsst_chkey => $jsst_ch) { ?>
                                <label class="jsst-check">
                                    <input type="checkbox" name="chanoff_<?php echo esc_attr($jsst_chkey); ?>" value="1" <?php checked(in_array($jsst_chkey, $jsst_chrefused, true)); ?> />
                                    <span><?php echo esc_html($jsst_ch['label']); ?></span>
                                </label>
                                <p class="jsst-fhelp"><?php echo esc_html($jsst_ch['blurb']); ?></p>
                            <?php } ?>
                        </fieldset>
                        <?php } } ?>

                        <?php if (isset($jsst_apcfg['aiagent_autopilot_display_name'])) { ?>
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Signed as', 'js-support-ticket')); ?></legend>
                            <div class="jsst-formgrid">
                                <div class="jsst-frow">
                                    <label class="screen-reader-text" for="aiagent_autopilot_display_name"><?php echo esc_html(__('Signed as', 'js-support-ticket')); ?></label>
                                    <div class="jsst-fval"><input type="text" id="aiagent_autopilot_display_name" name="aiagent_autopilot_display_name" value="<?php echo esc_attr($jsst_apcfg['aiagent_autopilot_display_name']); ?>" /></div>
                                </div>
                            </div>
                            <p class="jsst-fhelp"><?php echo esc_html(__('The name shown on AI replies and suggested answers, for example "AI Assistant".', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('The name customers see wherever the AI speaks - signed on an automatic reply, and on the suggested answer shown while they type. Something clearly not a person, such as "AI Assistant", is the honest choice.', 'js-support-ticket')); ?>
                        </fieldset>
                        <?php } ?>
                    </div>
                </div>

                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('How often, and how fast', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <div class="jsst-formgrid">
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="jsst-ai-threshold"><?php echo esc_html(__('Minimum confidence to send', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <input type="number" id="jsst-ai-threshold" name="threshold" min="1" max="100" step="1"
                                           value="<?php echo esc_attr((int) $jsst_rules['threshold']); ?>" />
                                </div>
                            </div>
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="jsst-ai-maxreplies"><?php echo esc_html(__('Automatic answers per ticket', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <input type="number" id="jsst-ai-maxreplies" name="maxreplies" min="0" max="20" step="1"
                                           value="<?php echo esc_attr((int) $jsst_rules['maxreplies']); ?>" />
                                </div>
                            </div>
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="jsst-ai-delay"><?php echo esc_html(__('Send', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select id="jsst-ai-delay" name="delay">
                                        <?php foreach ($jsst_delays as $jsst_key => $jsst_label) { ?>
                                            <option value="<?php echo esc_attr($jsst_key); ?>" <?php selected((int) $jsst_rules['delay'], (int) $jsst_key); ?>><?php echo esc_html($jsst_label); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <p class="jsst-fhelp"><?php echo esc_html(__('Start with a floor of 85 or higher. Shadow Mode shows what a floor would release.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('A floor of 85 or higher is the usual starting point; Shadow Mode says what a given floor would have released on your own tickets. A delay gives an agent time to reach the ticket first, and a customer cannot tell the difference between instant and a minute.', 'js-support-ticket')); ?>
                        <p class="jsst-fhelp"><?php echo esc_html(__('The per-ticket count includes answers a person approved.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('The per-ticket count includes answers a person approved: they were still answers the customer received, and the cap exists to stop a ticket filling with them.', 'js-support-ticket')); ?>

                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('When an answer may not be sent', 'js-support-ticket')); ?></legend>
                            <?php foreach ($jsst_fallbacks as $jsst_key => $jsst_def) { ?>
                                <label class="jsst-check">
                                    <input type="radio" name="fallback" value="<?php echo esc_attr($jsst_key); ?>" <?php checked($jsst_rules['fallback'], $jsst_key); ?> />
                                    <span><?php echo esc_html($jsst_def['label']); ?></span>
                                </label>
                                <p class="jsst-fhelp"><?php echo esc_html($jsst_def['blurb']); ?></p>
                            <?php } ?>
                        </fieldset>
                    </div>
                </div>

                <div class="jsst-btnrow">
                    <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('Save', 'js-support-ticket')); ?>" />
                    <span class="jsst-formfoot-note"><?php echo esc_html(__('Takes effect on the next ticket.', 'js-support-ticket')); ?></span><?php JSSTlayout::why(__('Takes effect on the next ticket. Answers already being written are checked against these rules again before anything is sent.', 'js-support-ticket')); ?>
                </div>
            </form>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('If you need to undo it', 'js-support-ticket')); ?></h2>

            <div class="jsst-card">
                <div class="jsst-card-body">
                    <p class="jsst-hint"><?php echo esc_html(__('Fastest first. Each step is something this release can actually do, and each says what it does not fix.', 'js-support-ticket')); ?></p>
                    <dl class="jsst-facts">
                        <?php foreach ($jsst_reversal as $jsst_step) { ?>
                            <dt><?php echo esc_html($jsst_step['title']); ?></dt>
                            <dd>
                                <?php echo esc_html($jsst_step['detail']); ?>
                                <span class="jsst-table-sub"><?php echo esc_html($jsst_step['undone']); ?></span>
                            </dd>
                        <?php } ?>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</div>
