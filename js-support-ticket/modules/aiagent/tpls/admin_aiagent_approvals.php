<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Approvals. (Roadmap 6.0-AI-03)
 *
 * The screen that makes "never auto-send below the threshold" mean something.
 * A threshold with nowhere for the answers below it to go is a threshold that
 * quietly throws work away; this is where they go, and where somebody either
 * sends them or says no.
 *
 * Two halves, in the order they are used. Waiting is a queue worked from the
 * top, oldest first, because the oldest is the customer who has been waiting
 * longest. Decided sits under it so that withdrawing something that turned out
 * to be wrong is one click from the record of it having been sent — the moment
 * an answer goes bad is not the moment to go looking for another screen.
 *
 * Every row shows the evidence, not just the verdict: the confidence, how much
 * of the question the best passage covered, how much of the reply its sources
 * actually support, and which pages it was written from. A queue that shows a
 * number and a paragraph asks somebody to approve on trust, which is the thing
 * this task exists to stop.
 */
if (!class_exists('JSSTaireview')) {
    echo esc_html(__('The approval record is not available on this site.', 'js-support-ticket'));
    return;
}

$jsst_policy  = isset(jssupportticket::$jsst_data['aipolicy']) ? jssupportticket::$jsst_data['aipolicy'] : array();
$jsst_pending = isset(jssupportticket::$jsst_data['aipending']) ? jssupportticket::$jsst_data['aipending'] : array();
$jsst_decided = isset(jssupportticket::$jsst_data['aidecided']) ? jssupportticket::$jsst_data['aidecided'] : array();
$jsst_counts  = isset(jssupportticket::$jsst_data['aicounts']) ? jssupportticket::$jsst_data['aicounts'] : array();
$jsst_states  = isset(jssupportticket::$jsst_data['aistates']) ? jssupportticket::$jsst_data['aistates'] : array();
$jsst_modes   = isset(jssupportticket::$jsst_data['aimodes']) ? jssupportticket::$jsst_data['aimodes'] : array();
$jsst_mode    = isset(jssupportticket::$jsst_data['aimode']) ? jssupportticket::$jsst_data['aimode'] : '';

$jsst_saveurl = wp_nonce_url(admin_url('admin.php?page=aiagent&task=saveaiapproval&action=jstask'), 'jsst-aiagent-approval');
$jsst_engine  = in_array('aiagent', jssupportticket::$_active_addons);

/** One row's action link, nonced to that row so it cannot decide another's. */
$jsst_decide = function ($jsst_id, $jsst_what) {
    return wp_nonce_url(
        admin_url('admin.php?page=aiagent&task=decideaianswer&action=jstask&answer=' . (int) $jsst_id
                  . '&decision=' . rawurlencode($jsst_what)),
        'jsst-aiagent-answer-' . (int) $jsst_id
    );
};

/** The evidence behind one answer, drawn the same way in both tables. */
$jsst_evidence = function ($jsst_row) {
    $jsst_bits = array();
    $jsst_bits[] = sprintf(
        /* translators: %d: the engine's confidence, as a percentage */
        esc_html(__('%d%% confident', 'js-support-ticket')), (int) $jsst_row->confidence);
    if ($jsst_row->coverage !== null) {
        $jsst_bits[] = sprintf(
            /* translators: %d: how much of the question the best passage covered */
            esc_html(__('%d%% of the question covered', 'js-support-ticket')), (int) $jsst_row->coverage);
    }
    if ($jsst_row->overlap !== null) {
        $jsst_bits[] = sprintf(
            /* translators: %d: how much of the reply its sources support */
            esc_html(__('%d%% supported by its sources', 'js-support-ticket')), (int) $jsst_row->overlap);
    }
    return implode(' · ', $jsst_bits);
};

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Approvals', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_approvals'); } ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('Every answer the AI wrote and what happened to it. Answers below your floors wait here; sent ones can be withdrawn.', 'js-support-ticket')); ?>
            </p><?php JSSTlayout::why(__('Every answer the AI wrote, what happened to it, and what can still be done about it. Nothing goes out below the floors you set — it waits here instead. Anything that did go out can be withdrawn.', 'js-support-ticket')); ?>

            <?php if (empty($jsst_policy['enabled'])) { ?>
                <div class="jsst-card"><div class="jsst-card-body">
                    <p class="jsst-hint"><?php echo esc_html(__('AI is switched off: nothing new arrives, and approving waits until it is back on.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('AI is switched off for this site, so nothing new will arrive here. Answers already waiting stay waiting, and approving one is refused until AI is switched back on.', 'js-support-ticket')); ?>
                </div></div>
            <?php } ?>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('How much a person has to do', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('"Always ask" means always, however confident the engine is.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('This sits above the confidence floor rather than beside it: "always ask" means always, however sure the engine says it is.', 'js-support-ticket')); ?>
                </div>
                <div class="jsst-card-body">
                    <div class="jsst-metrics">
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_counts['held']) ? (int) $jsst_counts['held'] : 0)); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Waiting', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_counts['sent']) ? (int) $jsst_counts['sent'] : 0)); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Sent', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_counts['rejected']) ? (int) $jsst_counts['rejected'] : 0)); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Rejected', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_counts['retracted']) ? (int) $jsst_counts['retracted'] : 0)); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Withdrawn', 'js-support-ticket')); ?></span>
                        </div>
                    </div>

                    <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_saveurl); ?>">
                        <fieldset class="jsst-fieldset">
                            <legend class="jsst-fieldset-legend"><?php echo esc_html(__('Before an answer reaches a customer', 'js-support-ticket')); ?></legend>
                            <?php foreach ($jsst_modes as $jsst_key => $jsst_def) { ?>
                                <label class="jsst-check">
                                    <input type="radio" name="approvalmode" value="<?php echo esc_attr($jsst_key); ?>" <?php checked($jsst_mode, $jsst_key); ?> />
                                    <span><?php echo esc_html($jsst_def['label']); ?></span>
                                </label>
                                <p class="jsst-fhelp"><?php echo esc_html($jsst_def['blurb']); ?></p>
                            <?php } ?>
                        </fieldset>
                        <div class="jsst-btnrow">
                            <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('Save', 'js-support-ticket')); ?>" />
                        </div>
                    </form>
                </div>
            </div>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('Waiting for a person', 'js-support-ticket')); ?></h2>

            <div class="jsst-card">
                <?php if (empty($jsst_pending)) { ?>
                    <div class="jsst-empty">
                        <p class="jsst-empty-title"><?php echo esc_html(__('Nothing is waiting', 'js-support-ticket')); ?></p>
                        <p class="jsst-empty-text">
                            <?php echo $jsst_engine
                                ? esc_html(__('Either nothing has been below its floors, or nothing has come in since automatic answers were switched on.', 'js-support-ticket'))
                                : esc_html(__('Nothing arrives here without the AI Agent add-on.', 'js-support-ticket')); ?>
                        </p><?php JSSTlayout::why(__('The engine that writes these ships in the AI Agent add-on. Without it nothing proposes an answer, so nothing arrives here.', 'js-support-ticket')); ?>
                    </div>
                <?php } else { ?>
                    <div class="jsst-table-wrap">
                        <table class="jsst-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html(__('Ticket', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('What it wants to say', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Evidence', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_pending as $jsst_row) {
                                $jsst_sources = JSSTaireview::sources($jsst_row);
                            ?>
                                <tr>
                                    <th scope="row">
                                        <a class="jsst-table-name" href="<?php echo esc_url(admin_url('admin.php?page=tickets&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_row->ticketid)); ?>">
                                            #<?php echo esc_html((int) $jsst_row->ticketid); ?>
                                        </a>
                                        <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                            /* translators: %s: how long ago the answer was written, e.g. "20 minutes" */
                                            __('%s ago', 'js-support-ticket'),
                                            human_time_diff(jssupportticketphplib::JSST_strtotime($jsst_row->created), time())
                                        )); ?></span>
                                        <?php if ($jsst_row->engine !== '') { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html($jsst_row->engine); ?></span>
                                        <?php } ?>
                                    </th>
                                    <td>
                                        <?php echo esc_html(wp_html_excerpt(wp_strip_all_tags((string) $jsst_row->body), 320, '…')); ?>
                                        <?php if (!empty($jsst_sources)) { ?>
                                            <div class="jsst-chips">
                                                <?php foreach ($jsst_sources as $jsst_source) { ?>
                                                    <span class="jsst-chip"><?php echo esc_html($jsst_source['title']); ?></span>
                                                <?php } ?>
                                            </div>
                                        <?php } else { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html(__('No sources cited.', 'js-support-ticket')); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="jsst-num"><?php echo esc_html($jsst_evidence($jsst_row)); ?></span>
                                        <?php if ($jsst_row->reason !== '') { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html($jsst_row->reason); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td class="jsst-col-act"><span class="jsst-rowactions">
                                        <?php if ($jsst_mode !== JSSTaireview::MODE_NEVER) { ?>
                                            <a class="jsst-act" href="<?php echo esc_url($jsst_decide((int) $jsst_row->id, 'approve')); ?>"
                                               onclick="return confirm('<?php echo esc_js(__('Send this answer to the customer?', 'js-support-ticket')); ?>');"><?php echo esc_html(__('Approve and send', 'js-support-ticket')); ?></a>
                                        <?php } ?>
                                        <a class="jsst-act jsst-act-danger" href="<?php echo esc_url($jsst_decide((int) $jsst_row->id, 'reject')); ?>"><?php echo esc_html(__('Reject', 'js-support-ticket')); ?></a>
                                        <a class="jsst-act" href="<?php echo esc_url(admin_url('admin.php?page=tickets&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_row->ticketid)); ?>"><?php echo esc_html(__('Open the ticket', 'js-support-ticket')); ?></a>
                                    </span></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('Already decided', 'js-support-ticket')); ?></h2>

            <div class="jsst-card">
                <?php if (empty($jsst_decided)) { ?>
                    <div class="jsst-empty">
                        <p class="jsst-empty-title"><?php echo esc_html(__('Nothing decided yet', 'js-support-ticket')); ?></p>
                        <p class="jsst-empty-text"><?php echo esc_html(__('Answers that were sent, rejected or withdrawn are listed here with who decided and when.', 'js-support-ticket')); ?></p>
                    </div>
                <?php } else { ?>
                    <div class="jsst-table-wrap">
                        <table class="jsst-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html(__('Ticket', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('What happened', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Answer', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_decided as $jsst_row) {
                                $jsst_state = isset($jsst_states[$jsst_row->state]) ? $jsst_states[$jsst_row->state] : null;
                                $jsst_who   = (int) $jsst_row->decidedby;
                            ?>
                                <tr>
                                    <th scope="row">
                                        <a class="jsst-table-name" href="<?php echo esc_url(admin_url('admin.php?page=tickets&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_row->ticketid)); ?>">
                                            #<?php echo esc_html((int) $jsst_row->ticketid); ?>
                                        </a>
                                        <span class="jsst-table-sub"><?php echo esc_html($jsst_evidence($jsst_row)); ?></span>
                                    </th>
                                    <td>
                                        <?php if ($jsst_state) { ?>
                                            <span class="jsst-pill <?php echo esc_attr($jsst_state['pill']); ?>"><span class="jsst-dot"></span><?php echo esc_html($jsst_state['label']); ?></span>
                                        <?php } ?>
                                        <span class="jsst-table-sub">
                                            <?php
                                            /* "Automatically" rather than a blank when nobody
                                               decided: a send that cleared the floors on its
                                               own is a different fact from one a person
                                               approved, and the row has to say which. */
                                            if ($jsst_who > 0) {
                                                $jsst_user = get_userdata($jsst_who);
                                                echo esc_html(sprintf(
                                                    /* translators: %s: the name of the person who decided */
                                                    __('by %s', 'js-support-ticket'),
                                                    $jsst_user ? $jsst_user->display_name : __('a deleted user', 'js-support-ticket')
                                                ));
                                            } else {
                                                echo esc_html(__('automatically', 'js-support-ticket'));
                                            }
                                            if (!empty($jsst_row->decided)) {
                                                echo ' · ' . esc_html(date_i18n(
                                                    jssupportticket::$_config['date_format'] . ' H:i',
                                                    jssupportticketphplib::JSST_strtotime($jsst_row->decided)
                                                ));
                                            }
                                            ?>
                                        </span>
                                        <?php if ($jsst_row->note !== '') { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html($jsst_row->note); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo esc_html(wp_html_excerpt(wp_strip_all_tags((string) $jsst_row->body), 220, '…')); ?></td>
                                    <td class="jsst-col-act"><span class="jsst-rowactions">
                                        <?php if ($jsst_row->state === JSSTaireview::STATE_SENT) { ?>
                                            <a class="jsst-act jsst-act-danger" href="<?php echo esc_url($jsst_decide((int) $jsst_row->id, 'retract')); ?>"
                                               onclick="return confirm('<?php echo esc_js(__('Withdraw this answer? The reply stays on the ticket struck through, and the ticket is reopened if this answer closed it. The customer already has the e-mail.', 'js-support-ticket')); ?>');"><?php echo esc_html(__('Withdraw', 'js-support-ticket')); ?></a>
                                        <?php } ?>
                                        <a class="jsst-act" href="<?php echo esc_url(admin_url('admin.php?page=tickets&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_row->ticketid)); ?>"><?php echo esc_html(__('Open the ticket', 'js-support-ticket')); ?></a>
                                    </span></td>
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
