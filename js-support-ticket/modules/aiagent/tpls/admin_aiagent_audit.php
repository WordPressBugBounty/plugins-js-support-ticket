<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Audit. (Roadmap 6.0-AI-01, 6.0-AI-03)
 *
 * What replaced the Zywrap module's Audit Logs and API Errors screens, and the
 * Copilot's usage table, with one list. They were three views of the same
 * question — what has this site asked a model, and did it work — kept apart only
 * because three different features happened to write three different logs.
 *
 * Two lists, and the split is deliberate. Runs are what the engines did; the
 * journal is what people changed about the policy. Somebody investigating
 * "drafts stopped appearing on Tuesday" needs the second one, and it is not
 * discoverable if it is mixed into a page of successful requests.
 *
 * Neither list holds ticket text or a prompt. That is a rule rather than an
 * omission: a log of what was sent to a model is the one artefact here that
 * would be worth stealing, and this screen is reachable by anybody who can
 * already administer the site.
 */
if (!class_exists('JSSTaipolicy')) {
    echo esc_html(__('The AI policy is not available.', 'js-support-ticket'));
    return;
}

$jsst_policy  = isset(jssupportticket::$jsst_data['aipolicy']) ? jssupportticket::$jsst_data['aipolicy'] : array();
$jsst_journal = isset(jssupportticket::$jsst_data['aijournal']) ? jssupportticket::$jsst_data['aijournal'] : array();
$jsst_runs    = isset(jssupportticket::$jsst_data['airuns']) ? jssupportticket::$jsst_data['airuns'] : array();
$jsst_format  = get_option('date_format') . ' ' . get_option('time_format');

/* Failures first in the summary, because a page of green rows with three red
   ones in it is a page where the three are what somebody came for. */
$jsst_failed = 0;
foreach ($jsst_runs as $jsst_run) {
    if (empty($jsst_run['ok'])) { $jsst_failed++; }
}

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('AI Audit', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_audit'); } ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('Every engine request and every policy change. Prompts and ticket text are never stored.', 'js-support-ticket')); ?>
            </p><?php JSSTlayout::why(__('Every request every engine has recorded, and every change anybody has made to the policy. No prompt and no ticket text is kept here — what was asked is named, not quoted.', 'js-support-ticket')); ?>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Recent runs', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <?php if ($jsst_failed > 0) { ?>
                            <span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span><?php
                                /* translators: %d: how many recent AI requests failed */
                                echo esc_html(sprintf(_n('%d failed', '%d failed', $jsst_failed, 'js-support-ticket'), $jsst_failed)); ?></span>
                        <?php } else { ?>
                            <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('No failures recorded', 'js-support-ticket')); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="jsst-card-body jsst-card-flush">
                    <?php if (empty($jsst_runs)) { ?>
                        <div class="jsst-empty">
                            <p><?php echo esc_html(__('Nothing has been asked yet. A run appears here the moment an agent presses a Copilot button or a grounded answer is composed.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html(__('When', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Asked for', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Engine', 'js-support-ticket')); ?></th>
                                        <th class="jsst-num"><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></th>
                                        <th class="jsst-num"><?php echo esc_html(__('Took', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Result', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_runs as $jsst_run) { ?>
                                    <tr class="<?php echo empty($jsst_run['ok']) ? '' : 'jsst-row-on'; ?>">
                                        <td>
                                            <span class="jsst-table-name"><?php echo esc_html($jsst_run['when'] > 0 ? date_i18n($jsst_format, $jsst_run['when']) : '—'); ?></span>
                                            <?php if (!empty($jsst_run['ticket'])) { ?>
                                                <span class="jsst-table-sub"><?php
                                                    /* translators: %d: a ticket id */
                                                    echo esc_html(sprintf(__('Ticket #%d', 'js-support-ticket'), (int) $jsst_run['ticket'])); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <span class="jsst-table-name"><?php echo esc_html($jsst_run['what'] !== '' ? $jsst_run['what'] : __('Request', 'js-support-ticket')); ?></span>
                                            <?php if (!empty($jsst_run['who'])) {
                                                $jsst_user = get_userdata((int) $jsst_run['who']); ?>
                                                <span class="jsst-table-sub"><?php echo esc_html($jsst_user ? $jsst_user->display_name : __('a deleted user', 'js-support-ticket')); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php echo esc_html($jsst_run['engine']); ?>
                                            <?php if (!empty($jsst_run['model'])) { ?>
                                                <span class="jsst-table-sub"><?php echo esc_html($jsst_run['model']); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td class="jsst-num"><?php echo esc_html($jsst_run['tokens'] > 0 ? number_format_i18n((int) $jsst_run['tokens']) : '—'); ?></td>
                                        <td class="jsst-num"><?php echo esc_html($jsst_run['ms'] > 0 ? number_format_i18n((int) $jsst_run['ms']) . ' ms' : '—'); ?></td>
                                        <td>
                                            <?php if (!empty($jsst_run['ok'])) { ?>
                                                <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Answered', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span><?php echo esc_html(__('Failed', 'js-support-ticket')); ?></span>
                                                <?php if (!empty($jsst_run['error'])) { ?>
                                                    <span class="jsst-table-sub"><?php echo esc_html($jsst_run['error']); ?></span>
                                                <?php } ?>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Policy changes', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('Who switched what, and when.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Who switched what, and when. Somebody turns AI off during an incident and the drafts stop appearing; a week later nobody remembers doing it and the plugin gets the blame. This is the line that settles it.', 'js-support-ticket')); ?>
                </div>
                <div class="jsst-card-body jsst-card-flush">
                    <?php if (empty($jsst_journal)) { ?>
                        <div class="jsst-empty">
                            <p><?php echo esc_html(__('Nothing has been changed since this was installed. The site is running the settings it shipped with.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html(__('When', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Change', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('By', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_journal as $jsst_entry) {
                                    $jsst_user = !empty($jsst_entry['user']) ? get_userdata((int) $jsst_entry['user']) : false; ?>
                                    <tr>
                                        <td><?php echo esc_html(date_i18n($jsst_format, isset($jsst_entry['when']) ? (int) $jsst_entry['when'] : 0)); ?></td>
                                        <td><span class="jsst-table-name"><?php echo esc_html(JSSTaipolicy::describe($jsst_entry)); ?></span></td>
                                        <td><?php echo esc_html($jsst_user ? $jsst_user->display_name : __('the system', 'js-support-ticket')); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>
</div>
