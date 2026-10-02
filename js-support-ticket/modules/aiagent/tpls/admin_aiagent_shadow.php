<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Shadow mode. (Roadmap 6.0-AI-04)
 *
 * The screen that earns the right to switch autopilot on, and the only honest
 * way to do it. Every other number about an AI answer is the model marking its
 * own homework: confidence is what it says about itself, coverage and grounding
 * are what our own gates say about it. This is the one measured against
 * something outside the system — what your agent actually wrote to that
 * customer, on that ticket, that day.
 *
 * So the table is a side-by-side and not a score. A site owner reading "84%
 * agreement" should be able to scroll down, read three of them, and decide
 * whether they believe it. A page that shows only the aggregate is asking for
 * the same trust the confidence figure already asked for and did not deserve.
 *
 * The headline is "would have been sent": how many of these the current
 * threshold would have released without anybody reading them. That is the
 * number somebody is actually deciding about.
 */
if (!class_exists('JSSTaireview')) {
    echo esc_html(__('The AI review record is not available on this site.', 'js-support-ticket'));
    return;
}

$jsst_policy   = isset(jssupportticket::$jsst_data['aipolicy']) ? jssupportticket::$jsst_data['aipolicy'] : array();
$jsst_shadows  = isset(jssupportticket::$jsst_data['aishadows']) ? jssupportticket::$jsst_data['aishadows'] : array();
$jsst_sum      = isset(jssupportticket::$jsst_data['aishadowsum']) ? jssupportticket::$jsst_data['aishadowsum'] : array();
$jsst_mode     = isset(jssupportticket::$jsst_data['aimode']) ? jssupportticket::$jsst_data['aimode'] : '';
$jsst_modes    = isset(jssupportticket::$jsst_data['aimodes']) ? jssupportticket::$jsst_data['aimodes'] : array();
$jsst_statuses = isset(jssupportticket::$jsst_data['aistatuses']) ? jssupportticket::$jsst_data['aistatuses'] : array();

$jsst_threshold = isset($jsst_sum['threshold']) ? (int) $jsst_sum['threshold'] : 0;
$jsst_proposed  = isset($jsst_sum['proposed']) ? (int) $jsst_sum['proposed'] : 0;
$jsst_paired    = isset($jsst_sum['paired']) ? (int) $jsst_sum['paired'] : 0;
$jsst_shadowing = ($jsst_mode === JSSTaireview::MODE_NEVER);

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Shadow Mode', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_shadow'); } ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('What the AI would have said, next to what your agent said. Run it for a few weeks before letting anything out.', 'js-support-ticket')); ?>
            </p><?php JSSTlayout::why(__('What the AI would have said, next to what your agent actually said. Run it for a few weeks on real tickets before letting anything out — this is the only measurement of an AI answer that is not the AI grading itself.', 'js-support-ticket')); ?>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('What it would have done', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <?php if ($jsst_shadowing) { ?>
                            <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Shadowing', 'js-support-ticket')); ?></span>
                        <?php } else { ?>
                            <span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span><?php echo esc_html(__('Answers can go out', 'js-support-ticket')); ?></span>
                        <?php } ?>
                        <a class="jsst-pill jsst-pill-info" href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_approvals')); ?>"><?php echo esc_html(__('Approvals', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <div class="jsst-card-body">
                    <p class="jsst-hint">
                        <?php if ($jsst_shadowing) {
                            echo esc_html(__('This site is set to propose only, so nothing here reached a customer. That is what makes these numbers worth reading.', 'js-support-ticket'));
                        } else {
                            echo esc_html(sprintf(
                                /* translators: %s: the name of the propose-only approval mode */
                                __('These are answers that were not sent — held below a floor, or rejected. To measure the AI on every ticket instead, set approvals to "%s".', 'js-support-ticket'),
                                isset($jsst_modes[JSSTaireview::MODE_NEVER]['label']) ? $jsst_modes[JSSTaireview::MODE_NEVER]['label'] : ''
                            ));
                        } ?>
                    </p>

                    <div class="jsst-metrics">
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num">
                                <?php echo esc_html(number_format_i18n(isset($jsst_sum['wouldhavesent']) ? (int) $jsst_sum['wouldhavesent'] : 0)); ?>
                            </span>
                            <span class="jsst-metric-label"><?php echo esc_html(sprintf(
                                /* translators: %d: the configured minimum confidence */
                                __('Would have gone out at %d%%', 'js-support-ticket'), $jsst_threshold
                            )); ?></span>
                        </div>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n($jsst_proposed)); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Answers written', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n($jsst_paired)); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Compared with an agent', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num">
                                <?php echo $jsst_paired > 0
                                    ? esc_html((isset($jsst_sum['agreement']) ? (int) $jsst_sum['agreement'] : 0) . '%')
                                    : '&mdash;'; ?>
                            </span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Said the same thing', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num">
                                <?php
                                /* Left as a dash rather than 0% when nothing has
                                   cleared the threshold: "no answer has been that
                                   confident yet" and "the confident ones were wrong"
                                   are opposite conclusions from the same digit. */
                                echo (isset($jsst_sum['agreedwhenconfident']) && $jsst_sum['agreedwhenconfident'] !== null)
                                    ? esc_html((int) $jsst_sum['agreedwhenconfident'] . '%') : '&mdash;';
                                ?>
                            </span>
                            <span class="jsst-metric-label"><?php echo esc_html(sprintf(
                                /* translators: %d: the configured minimum confidence */
                                __('…of the ones above %d%%', 'js-support-ticket'), $jsst_threshold
                            )); ?></span>
                        </div>
                    </div>

                    <p class="jsst-hint">
                        <?php echo esc_html(__('"Said the same thing" counts the meaningful words both answers share. Read a few rows to check it.', 'js-support-ticket')); ?>
                    </p><?php JSSTlayout::why(__('"Said the same thing" counts the meaningful words the two answers share, measured against the shorter of the pair so an agent\'s greeting and sign-off do not read as disagreement. It is deliberately something you can check by eye rather than a score you have to trust — read a few of the rows below and see whether you agree with it.', 'js-support-ticket')); ?>
                </div>
            </div>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('Side by side', 'js-support-ticket')); ?></h2>

            <div class="jsst-card">
                <?php if (empty($jsst_shadows)) { ?>
                    <div class="jsst-empty">
                        <p class="jsst-empty-title"><?php echo esc_html(__('Nothing to compare yet', 'js-support-ticket')); ?></p>
                        <p class="jsst-empty-text">
                            <?php echo $jsst_proposed > 0
                                ? esc_html(__('Answers have been written, but no agent has replied to those tickets yet. A comparison appears as soon as one does.', 'js-support-ticket'))
                                : esc_html(__('No answers have been written yet. Once automatic replies are switched on — in propose-only mode, ideally — every ticket that comes in produces one of these.', 'js-support-ticket')); ?>
                        </p>
                    </div>
                <?php } else { ?>
                    <div class="jsst-table-wrap">
                        <table class="jsst-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html(__('Ticket', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('What the AI would have said', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('What your agent said', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Verdict', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_shadows as $jsst_row) {
                                $jsst_agree  = (int) $jsst_row->agreement;
                                $jsst_would  = ((int) $jsst_row->confidence >= $jsst_threshold);
                                /* The row worth reading is the one the threshold
                                   would have released and the agent then disagreed
                                   with. Marked so it can be found by scrolling. */
                                $jsst_risky  = ($jsst_would && $jsst_agree < 40);
                                $jsst_status = isset($jsst_statuses[$jsst_row->outcome]) ? $jsst_statuses[$jsst_row->outcome] : '';
                            ?>
                                <tr class="<?php echo $jsst_risky ? 'jsst-row-differs' : ''; ?>">
                                    <th scope="row">
                                        <a class="jsst-table-name" href="<?php echo esc_url(admin_url('admin.php?page=tickets&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_row->ticketid)); ?>">
                                            #<?php echo esc_html((int) $jsst_row->ticketid); ?>
                                        </a>
                                        <?php if ($jsst_status !== '') { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html($jsst_status); ?></span>
                                        <?php } ?>
                                        <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                            /* translators: %d: the engine's confidence, as a percentage */
                                            __('%d%% confident', 'js-support-ticket'), (int) $jsst_row->confidence
                                        )); ?></span>
                                    </th>
                                    <td><?php echo esc_html(wp_html_excerpt(wp_strip_all_tags((string) $jsst_row->body), 300, '…')); ?></td>
                                    <td><?php echo esc_html(wp_html_excerpt(wp_strip_all_tags((string) $jsst_row->agentbody), 300, '…')); ?></td>
                                    <td>
                                        <span class="jsst-num"><strong><?php echo esc_html($jsst_agree); ?>%</strong></span>
                                        <span class="jsst-table-sub">
                                            <?php echo $jsst_would
                                                ? esc_html(__('would have been sent', 'js-support-ticket'))
                                                : esc_html(__('would have waited', 'js-support-ticket')); ?>
                                        </span>
                                        <?php if ($jsst_risky) { ?>
                                            <span class="jsst-table-sub"><strong><?php echo esc_html(__('Read this one.', 'js-support-ticket')); ?></strong></span>
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
    </div>
</div>
