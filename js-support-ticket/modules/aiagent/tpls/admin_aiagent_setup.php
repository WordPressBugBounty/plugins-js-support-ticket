<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Set up. (Roadmap 6.0-AI-11)
 *
 * Six screens and about forty settings stand behind an automatic answer, and
 * every one of them is there for a reason somebody can defend. That is a fair
 * description of the problem and a poor description of somebody's Tuesday
 * afternoon. This page is the reading of all of it: what is done, what is next,
 * and what is stopping the rest.
 *
 * Three things it does that a settings page does not:
 *
 *   **It says what the site actually has, under each step.** "Two approved
 *   sources have content to answer from" is worth more than a tick, because the
 *   commonest failure here — sources approved, all of them empty — looks
 *   identical to a broken engine from the outside.
 *
 *   **It shows one real answer.** Everything after step three is a number, and
 *   this is the thing the numbers are about. The preview runs the live chain,
 *   so what is on the page is what would actually be written.
 *
 *   **It refuses to let anybody finish early.** Going live is not offered until
 *   shadow mode has produced answers and had them compared against agents.
 *   Without that refusal this is a form with a switch at the bottom, and
 *   somebody reaches the bottom in ninety seconds.
 */
if (!class_exists('JSSTaisetup')) {
    echo esc_html(__('The setup checklist is not available on this site.', 'js-support-ticket'));
    return;
}

$jsst_steps    = isset(jssupportticket::$jsst_data['aisteps']) ? jssupportticket::$jsst_data['aisteps'] : array();
$jsst_next     = isset(jssupportticket::$jsst_data['ainext']) ? jssupportticket::$jsst_data['ainext'] : '';
$jsst_progress = isset(jssupportticket::$jsst_data['aiprogress']) ? jssupportticket::$jsst_data['aiprogress'] : array('done' => 0, 'total' => 0);
$jsst_preview  = isset(jssupportticket::$jsst_data['aipreview']) ? jssupportticket::$jsst_data['aipreview'] : false;
$jsst_asked    = isset(jssupportticket::$jsst_data['aiasked']) ? jssupportticket::$jsst_data['aiasked'] : '';

$jsst_action = admin_url('admin.php?page=aiagent&task=runaisetup&action=jstask');
$jsst_number = 0;

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Set up the AI', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_setup'); } ?>

            <?php /* Progress and the way to put the checklist away sit on the
                     intro line; the card that held them only repeated it. */ ?>
            <div class="jsst-lede-row">
                <p class="jsst-lede">
                    <?php echo JSSTaisetup::complete()
                        ? esc_html(__('Everything is set up. Pause or roll back from Autopilot.', 'js-support-ticket'))
                        : esc_html(__('Six steps in order. Nothing reaches a customer until the last one.', 'js-support-ticket')); ?>
                </p>
                <div class="jsst-card-tools">
                    <?php if (JSSTaisetup::complete()) { ?>
                        <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Set up', 'js-support-ticket')); ?></span>
                    <?php } else { ?>
                        <span class="jsst-pill jsst-pill-info"><span class="jsst-dot"></span><?php echo esc_html(sprintf(
                            /* translators: 1: steps finished, 2: steps in total */
                            __('%1$d of %2$d done', 'js-support-ticket'),
                            (int) $jsst_progress['done'], (int) $jsst_progress['total']
                        )); ?></span>
                    <?php } ?>
                    <a class="jsst-pill jsst-pill-off" href="<?php echo esc_url(wp_nonce_url($jsst_action . '&step=dismiss', 'jsst-aiagent-setup')); ?>"><?php echo esc_html(__('Hide this', 'js-support-ticket')); ?></a>
                </div>
            </div>

            <?php foreach ($jsst_steps as $jsst_key => $jsst_step) {
                $jsst_number++;
                $jsst_is_next = ($jsst_key === $jsst_next);
            ?>
                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title">
                            <?php echo esc_html($jsst_number . '. ' . $jsst_step['title']); ?>
                        </h2>
                        <div class="jsst-card-tools">
                            <?php if (!empty($jsst_step['done'])) { ?>
                                <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Done', 'js-support-ticket')); ?></span>
                            <?php } elseif ($jsst_is_next) { ?>
                                <span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span><?php echo esc_html(__('Next', 'js-support-ticket')); ?></span>
                            <?php } elseif (!empty($jsst_step['waiting'])) { ?>
                                <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Waiting', 'js-support-ticket')); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="jsst-card-body">
                        <p class="jsst-hint"><?php echo esc_html($jsst_step['blurb']); ?></p>

                        <?php if ($jsst_step['evidence'] !== '') { ?>
                            <p class="jsst-fhelp"><strong><?php echo esc_html($jsst_step['evidence']); ?></strong></p>
                        <?php } ?>

                        <?php if ($jsst_step['blocked'] !== '') { ?>
                            <p class="jsst-fhelp"><?php echo esc_html($jsst_step['blocked']); ?></p>
                        <?php } ?>

                        <?php /* The preview lives inside its own step rather than
                                 in a panel of its own: it is the step. */ ?>
                        <?php if ($jsst_key === 'preview' && $jsst_step['blocked'] === '') { ?>
                            <form class="jsst-form" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                                <input type="hidden" name="page" value="aiagent" />
                                <input type="hidden" name="jstlay" value="aiagent_setup" />
                                <div class="jsst-formgrid">
                                    <div class="jsst-frow jsst-frow-full">
                                        <label class="jsst-flabel" for="jsst-setup-ask"><?php echo esc_html(__('A question one of your customers would ask', 'js-support-ticket')); ?></label>
                                        <div class="jsst-fval">
                                            <input type="text" id="jsst-setup-ask" name="ask" value="<?php echo esc_attr($jsst_asked); ?>"
                                                   placeholder="<?php echo esc_attr(__('How do I change the email address on my account?', 'js-support-ticket')); ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="jsst-btnrow">
                                    <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('See what it would say', 'js-support-ticket')); ?>" />
                                    <span class="jsst-formfoot-note"><?php echo esc_html(__('This asks the model, so it costs what one answer costs. It is on the Usage screen as a preview.', 'js-support-ticket')); ?></span>
                                </div>
                            </form>

                            <?php if (is_array($jsst_preview)) { ?>
                                <?php if (empty($jsst_preview['available'])) { ?>
                                    <div class="jsst-empty">
                                        <p class="jsst-empty-title"><?php echo esc_html(__('No preview', 'js-support-ticket')); ?></p>
                                        <p class="jsst-empty-text"><?php echo esc_html(isset($jsst_preview['reason']) ? $jsst_preview['reason'] : ''); ?></p>
                                    </div>
                                <?php } else { ?>
                                    <dl class="jsst-facts">
                                        <?php if (!empty($jsst_preview['answered'])) { ?>
                                            <dt><?php echo esc_html(__('What it would write', 'js-support-ticket')); ?></dt>
                                            <dd><?php echo wp_kses_post($jsst_preview['reply']); ?></dd>
                                        <?php } else { ?>
                                            <dt><?php echo esc_html(__('It would not answer', 'js-support-ticket')); ?></dt>
                                            <dd><?php echo esc_html(isset($jsst_preview['reason']) ? $jsst_preview['reason'] : ''); ?></dd>
                                        <?php } ?>

                                        <?php if (!empty($jsst_preview['sources'])) { ?>
                                            <dt><?php echo esc_html(__('Written from', 'js-support-ticket')); ?></dt>
                                            <dd>
                                                <div class="jsst-chips">
                                                    <?php foreach ($jsst_preview['sources'] as $jsst_source) { ?>
                                                        <span class="jsst-chip"><?php echo esc_html(isset($jsst_source['title']) ? $jsst_source['title'] : ''); ?></span>
                                                    <?php } ?>
                                                </div>
                                            </dd>
                                        <?php } ?>

                                        <dt><?php echo esc_html(__('What would have happened to it', 'js-support-ticket')); ?></dt>
                                        <dd>
                                            <span class="jsst-pill <?php
                                                echo ($jsst_preview['verdict']['state'] === 'sent') ? 'jsst-pill-warn'
                                                   : (($jsst_preview['verdict']['state'] === 'never') ? 'jsst-pill-bad' : 'jsst-pill-info'); ?>">
                                                <span class="jsst-dot"></span>
                                                <?php echo ($jsst_preview['verdict']['state'] === 'sent')
                                                    ? esc_html(__('Sent', 'js-support-ticket'))
                                                    : esc_html(__('Held', 'js-support-ticket')); ?>
                                            </span>
                                            <?php echo esc_html($jsst_preview['verdict']['text']); ?>
                                        </dd>
                                    </dl>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>

                        <div class="jsst-btnrow">
                            <?php if ($jsst_key === 'shadow' && $jsst_step['blocked'] === '' && empty($jsst_step['done'])) { ?>
                                <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url(wp_nonce_url($jsst_action . '&step=shadow', 'jsst-aiagent-setup')); ?>"><?php echo esc_html(__('Start shadow mode', 'js-support-ticket')); ?></a>
                                <span class="jsst-formfoot-note"><?php echo esc_html(__('Turns automatic answering on with approvals set to propose only.', 'js-support-ticket')); ?></span><?php JSSTlayout::why(__('Switches automatic answering on and sets approvals to propose only — both halves, because doing only the first is the one mistake here that writes to a customer.', 'js-support-ticket')); ?>
                            <?php } elseif ($jsst_key === 'live' && $jsst_step['blocked'] === '' && empty($jsst_step['done'])) { ?>
                                <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url(wp_nonce_url($jsst_action . '&step=live', 'jsst-aiagent-setup')); ?>"><?php echo esc_html(__('Let it answer customers', 'js-support-ticket')); ?></a>
                                <span class="jsst-formfoot-note"><?php echo esc_html(__('Reversible: the Autopilot screen has a pause switch, and any answer that goes out can be withdrawn from Approvals.', 'js-support-ticket')); ?></span>
                            <?php } ?>

                            <?php if (!empty($jsst_step['where'])) { ?>
                                <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=' . $jsst_step['where'])); ?>"><?php echo esc_html($jsst_step['wherename']); ?></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
