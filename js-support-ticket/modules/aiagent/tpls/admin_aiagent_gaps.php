<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Knowledge Gaps. (Roadmap 6.0-AI-12)
 *
 * Every other screen in this menu governs what the machine may do with the
 * content a site already has. This one is about the content it does not have,
 * and it is the only screen here that makes the rest get better: an answer
 * engine is exactly as good as the documentation behind it.
 *
 * Three things it is careful about.
 *
 *   **It shows the questions, not a topic model.** The list under each heading
 *   is the customers' own words, which is the actual deliverable — a writer can
 *   read two of them and know both what to write and whether the grouping was
 *   right. A cluster label is a guess this code has no business being confident
 *   about, so it is offered as a suggested title and nothing more.
 *
 *   **It separates "we have nothing" from "we have something and it is not good
 *   enough".** Those are different afternoons' work. The signal is on every
 *   row rather than flattened into one number, and the filter above the list is
 *   there because working through one kind at a time is how anybody would.
 *
 *   **It can shrink.** A recommendation that retrieval has started finding
 *   something for is marked so, asked of the real retriever rather than a flag,
 *   and anything that will never have an article can be hidden. A list that
 *   only grows is a to-do list nobody finishes.
 */
$jsst_ready   = !empty(jssupportticket::$jsst_data['aigapready']);
$jsst_gaps    = isset(jssupportticket::$jsst_data['aigaps']) ? jssupportticket::$jsst_data['aigaps'] : array();
$jsst_stats   = isset(jssupportticket::$jsst_data['aigapstats']) ? jssupportticket::$jsst_data['aigapstats'] : array();
$jsst_kinds   = isset(jssupportticket::$jsst_data['aigapkinds']) ? jssupportticket::$jsst_data['aigapkinds'] : array();
$jsst_days    = isset(jssupportticket::$jsst_data['aigapdays']) ? (int) jssupportticket::$jsst_data['aigapdays'] : 90;
$jsst_kind    = isset(jssupportticket::$jsst_data['aigapkind']) ? jssupportticket::$jsst_data['aigapkind'] : '';
$jsst_hidden  = isset(jssupportticket::$jsst_data['aigaphidden']) ? (int) jssupportticket::$jsst_data['aigaphidden'] : 0;
$jsst_showing = !empty(jssupportticket::$jsst_data['aigapshowing']);

$jsst_base = array('page' => 'aiagent', 'jstlay' => 'aiagent_gaps');
if ($jsst_kind !== '')  $jsst_base['kind'] = $jsst_kind;
if ($jsst_days !== 90)  $jsst_base['days'] = $jsst_days;
if ($jsst_showing)      $jsst_base['show'] = 'hidden';

$jsst_action = admin_url('admin.php?page=aiagent&task=saveaigap&action=jstask');
foreach (array('days', 'kind', 'show') as $jsst_carry) {
    if (isset($jsst_base[$jsst_carry])) {
        $jsst_action .= '&' . $jsst_carry . '=' . rawurlencode($jsst_base[$jsst_carry]);
    }
}

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Unanswered Questions', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_gaps'); } ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('Questions your content did not answer, grouped into topics worth one article each.', 'js-support-ticket')); ?>
            </p><?php JSSTlayout::why(__('Questions your content did not answer, grouped into things somebody could write one article about. Everything here was asked by a real person: nothing on this page is invented, predicted or bought in.', 'js-support-ticket')); ?>

            <?php if (!$jsst_ready) { ?>
                <div class="jsst-empty">
                    <p class="jsst-empty-title"><?php echo esc_html(__('Not collecting yet', 'js-support-ticket')); ?></p>
                    <p class="jsst-empty-text"><?php echo esc_html(__('The gap register has not been created on this site. Visit any AI Agent screen as an administrator once and it will build itself; if this keeps saying the same thing, check System Status for a database error.', 'js-support-ticket')); ?></p>
                </div>
            <?php } else { ?>

                <?php /* The counts belong on a surface like every other screen's
                         "how it stands" block; loose on the canvas they read as
                         page furniture rather than as the page's own figures. */ ?>
                <div class="jsst-card">
                  <div class="jsst-card-body">
                <div class="jsst-metrics">
                    <div class="jsst-metric">
                        <span class="jsst-metric-value"><?php echo esc_html(number_format_i18n(isset($jsst_stats['questions']) ? (int) $jsst_stats['questions'] : 0)); ?></span>
                        <span class="jsst-metric-label"><?php echo esc_html(__('Questions recorded', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="jsst-metric">
                        <span class="jsst-metric-value"><?php echo esc_html(number_format_i18n(isset($jsst_stats['missing']) ? (int) $jsst_stats['missing'] : 0)); ?></span>
                        <span class="jsst-metric-label"><?php echo esc_html(__('Had nothing at all to answer from', 'js-support-ticket')); ?></span>
                    </div>
                    <div class="jsst-metric">
                        <span class="jsst-metric-value"><?php echo esc_html(number_format_i18n(count($jsst_gaps))); ?></span>
                        <span class="jsst-metric-label"><?php echo esc_html(__('Articles this suggests', 'js-support-ticket')); ?></span>
                    </div>
                    <?php if ($jsst_hidden > 0) { ?>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value"><?php echo esc_html(number_format_i18n($jsst_hidden)); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Hidden by hand', 'js-support-ticket')); ?></span>
                        </div>
                    <?php } ?>
                </div>
                  </div>
                </div>

                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('What you are looking at', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <p class="jsst-hint"><?php echo esc_html(__('Over the last', 'js-support-ticket')); ?></p>
                        <div class="jsst-chips">
                            <?php foreach (array(7 => __('7 days', 'js-support-ticket'), 30 => __('30 days', 'js-support-ticket'), 90 => __('90 days', 'js-support-ticket'), 180 => __('6 months', 'js-support-ticket')) as $jsst_option => $jsst_optionlabel) {
                                $jsst_url = add_query_arg(array_merge($jsst_base, array('days' => $jsst_option)), admin_url('admin.php')); ?>
                                <a class="jsst-chip<?php echo ($jsst_days === $jsst_option) ? ' jsst-chip-lead' : ''; ?>" href="<?php echo esc_url($jsst_url); ?>"<?php echo ($jsst_days === $jsst_option) ? ' aria-current="true"' : ''; ?>><?php echo esc_html($jsst_optionlabel); ?></a>
                            <?php } ?>
                        </div>

                        <p class="jsst-hint"><?php echo esc_html(__('Because', 'js-support-ticket')); ?></p>
                        <div class="jsst-chips">
                            <?php $jsst_any = $jsst_base; unset($jsst_any['kind']);
                                  $jsst_all = add_query_arg($jsst_any, admin_url('admin.php')); ?>
                            <a class="jsst-chip<?php echo ($jsst_kind === '') ? ' jsst-chip-lead' : ''; ?>" href="<?php echo esc_url($jsst_all); ?>"><?php echo esc_html(__('Any reason', 'js-support-ticket')); ?></a>
                            <?php foreach ($jsst_kinds as $jsst_key => $jsst_meta) {
                                $jsst_url = add_query_arg(array_merge($jsst_base, array('kind' => $jsst_key)), admin_url('admin.php')); ?>
                                <a class="jsst-chip<?php echo ($jsst_kind === $jsst_key) ? ' jsst-chip-lead' : ''; ?>" href="<?php echo esc_url($jsst_url); ?>" title="<?php echo esc_attr($jsst_meta['blurb']); ?>"><?php echo esc_html($jsst_meta['label']); ?></a>
                            <?php } ?>
                        </div>

                        <?php if ($jsst_kind !== '' && isset($jsst_kinds[$jsst_kind])) { ?>
                            <p class="jsst-fhelp"><?php echo esc_html($jsst_kinds[$jsst_kind]['blurb']); ?></p>
                        <?php } ?>

                        <p class="jsst-hint">
                            <?php echo esc_html(__('Questions are grouped by the words they share: rough, but easy to check.', 'js-support-ticket')); ?>
                        </p><?php JSSTlayout::why(__('Questions are grouped by the significant words they share, which is a crude measure and deliberately so — you can read two of them and tell instantly whether the grouping was right, which is not true of anything cleverer.', 'js-support-ticket')); ?>
                    </div>
                    <?php if ($jsst_hidden > 0 || $jsst_showing) { ?>
                        <div class="jsst-card-foot">
                            <?php if ($jsst_showing) {
                                $jsst_back = $jsst_base; unset($jsst_back['show']); ?>
                                <a class="jsst-btn" href="<?php echo esc_url(add_query_arg($jsst_back, admin_url('admin.php'))); ?>"><?php echo esc_html(__('Back to the list', 'js-support-ticket')); ?></a>
                            <?php } else { ?>
                                <a class="jsst-btn" href="<?php echo esc_url(add_query_arg(array_merge($jsst_base, array('show' => 'hidden')), admin_url('admin.php'))); ?>"><?php echo esc_html(sprintf(
                                    /* translators: %d: how many gaps have been hidden by hand */
                                    _n('Show the %d you hid', 'Show the %d you hid', $jsst_hidden, 'js-support-ticket'), $jsst_hidden)); ?></a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>

                <?php if (empty($jsst_gaps)) { ?>
                    <div class="jsst-empty">
                        <p class="jsst-empty-title"><?php echo $jsst_showing
                            ? esc_html(__('Nothing hidden here', 'js-support-ticket'))
                            : esc_html(__('Nothing to write', 'js-support-ticket')); ?></p>
                        <p class="jsst-empty-text"><?php echo $jsst_showing
                            ? esc_html(__('No group has been hidden in this period.', 'js-support-ticket'))
                            : esc_html(__('Either your content is answering what people ask, or nothing has been asked yet. This fills itself as customers use the site — there is nothing to switch on.', 'js-support-ticket')); ?></p>
                    </div>
                <?php } ?>

                <?php foreach ($jsst_gaps as $jsst_gap) {
                    $jsst_brief = $jsst_gap['brief'];
                    $jsst_hide  = wp_nonce_url($jsst_action . '&do=' . ($jsst_showing ? 'restore' : 'hide')
                        . '&signature=' . rawurlencode($jsst_gap['signature']), 'jsst-aiagent-gaps');
                ?>
                    <div class="jsst-card">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html($jsst_brief['title']); ?></h2>
                            <div class="jsst-card-sub"><?php
                                /* Two numbers in one sentence would need one
                                   plural rule to cover both, and "asked 2
                                   times, in 1 different wordings" is what that
                                   produces. Said as one clause or two instead,
                                   each choosing its own. */
                                if ((int) $jsst_gap['distinct'] === (int) $jsst_gap['asked']) {
                                    /* "Once" only for exactly one: a plural's singular form is
                                       also used for 21, 31 ... in some languages, so it has to
                                       carry the number. */
                                    echo esc_html(((int) $jsst_gap['asked'] === 1)
                                        ? __('Asked once', 'js-support-ticket')
                                        : sprintf(
                                            /* translators: %d: how many times a question was asked */
                                            _n('Asked %d time', 'Asked %d times', (int) $jsst_gap['asked'], 'js-support-ticket'),
                                            (int) $jsst_gap['asked']));
                                } else {
                                    echo esc_html(sprintf(
                                        /* translators: 1: how many times it was asked, 2: how many different wordings it was asked in */
                                        __('Asked %1$s, in %2$s', 'js-support-ticket'),
                                        sprintf(
                                            /* translators: %d: how many times a question was asked */
                                            _n('%d time', '%d times', (int) $jsst_gap['asked'], 'js-support-ticket'),
                                            (int) $jsst_gap['asked']),
                                        sprintf(
                                            /* translators: %d: how many different wordings the question arrived in */
                                            _n('%d wording', '%d different wordings', (int) $jsst_gap['distinct'], 'js-support-ticket'),
                                            (int) $jsst_gap['distinct'])));
                                }
                            ?></div>
                            <div class="jsst-card-tools">
                                <?php
                                /* Which of the two kinds of work this is, counted
                                   over the whole group rather than read off its
                                   commonest single signal - on a group of two
                                   that disagree, the commonest is whichever
                                   sort() happened to leave first, and a heading
                                   that flips between "we have nothing" and
                                   "we answered it badly" for no visible reason
                                   is one nobody can act on. A tie goes to
                                   missing, because writing the article covers
                                   both readings and rewriting an answer does
                                   not. */
                                $jsst_missing = 0;
                                $jsst_poor    = 0;
                                foreach ($jsst_gap['kinds'] as $jsst_key => $jsst_count) {
                                    if (!empty($jsst_kinds[$jsst_key]['missing'])) {
                                        $jsst_missing += (int) $jsst_count;
                                    } else {
                                        $jsst_poor += (int) $jsst_count;
                                    }
                                }
                                ?>
                                <?php if (!empty($jsst_gap['covered'])) { ?>
                                    <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Answerable now', 'js-support-ticket')); ?></span>
                                <?php } elseif (empty($jsst_gap['worth'])) { ?>
                                    <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Asked once', 'js-support-ticket')); ?></span>
                                <?php } elseif ($jsst_missing >= $jsst_poor) { ?>
                                    <span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span><?php echo esc_html(__('Nothing to answer from', 'js-support-ticket')); ?></span>
                                <?php } else { ?>
                                    <span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span><?php echo esc_html(__('Answered badly', 'js-support-ticket')); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="jsst-card-body">

                            <?php if (!empty($jsst_gap['covered'])) { ?>
                                <p class="jsst-fhelp"><?php echo esc_html(__('Something now matches this, so it may already be written. Check it answers these questions, then hide it.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('Retrieval finds something for this now, so it may already have been written. It is still listed because these questions were asked before that content existed — check it actually answers them, then hide this.', 'js-support-ticket')); ?>
                            <?php } elseif ($jsst_gap['covered'] === null) { ?>
                                <p class="jsst-fhelp"><?php echo esc_html(__('Not checked against retrieval — only the first few groups are, because each check runs a real search.', 'js-support-ticket')); ?></p>
                            <?php } ?>

                            <p class="jsst-hint"><?php echo esc_html(__('What people actually asked', 'js-support-ticket')); ?></p>
                            <div class="jsst-quotes">
                                <?php foreach (array_slice($jsst_gap['questions'], 0, 8) as $jsst_question) { ?>
                                    <p class="jsst-quote">&ldquo;<?php echo esc_html($jsst_question); ?>&rdquo;</p>
                                <?php } ?>
                                <?php if (count($jsst_gap['questions']) > 8) { ?>
                                    <p class="jsst-quote jsst-quote-more"><?php echo esc_html(sprintf(
                                        /* translators: %d: how many further wordings are not shown */
                                        _n('and %d more wording', 'and %d more wordings', count($jsst_gap['questions']) - 8, 'js-support-ticket'),
                                        count($jsst_gap['questions']) - 8)); ?></p>
                                <?php } ?>
                            </div>

                            <dl class="jsst-facts">
                                <dt><?php echo esc_html(__('Why each one counted', 'js-support-ticket')); ?></dt>
                                <dd>
                                    <div class="jsst-chips">
                                        <?php foreach ($jsst_gap['kinds'] as $jsst_key => $jsst_count) { ?>
                                            <span class="jsst-chip" title="<?php echo esc_attr(isset($jsst_kinds[$jsst_key]['blurb']) ? $jsst_kinds[$jsst_key]['blurb'] : ''); ?>"><?php
                                                echo esc_html(JSSTaigaps::kindLabel($jsst_key) . ' · ' . number_format_i18n((int) $jsst_count)); ?></span>
                                        <?php } ?>
                                    </div>
                                </dd>

                                <?php if (!empty($jsst_gap['tickets'])) { ?>
                                    <dt><?php echo esc_html(__('On these tickets', 'js-support-ticket')); ?></dt>
                                    <dd>
                                        <div class="jsst-chips">
                                            <?php foreach (array_slice($jsst_gap['tickets'], 0, 12) as $jsst_ticketid) { ?>
                                                <a class="jsst-chip" href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_ticketid)); ?>">#<?php echo esc_html((int) $jsst_ticketid); ?></a>
                                            <?php } ?>
                                            <?php if (count($jsst_gap['tickets']) > 12) { ?>
                                                <span class="jsst-chip jsst-chip-more"><?php echo esc_html(sprintf(
                                                    /* translators: %d: how many further tickets are not linked */
                                                    __('and %d more', 'js-support-ticket'), count($jsst_gap['tickets']) - 12)); ?></span>
                                            <?php } ?>
                                        </div>
                                    </dd>
                                <?php } ?>

                                <dt><?php echo esc_html(__('Last asked', 'js-support-ticket')); ?></dt>
                                <dd><?php echo esc_html($jsst_gap['last'] > 0
                                        ? sprintf(
                                            /* translators: %s: a human readable interval, e.g. "3 days" */
                                            __('%s ago', 'js-support-ticket'), human_time_diff($jsst_gap['last'], time()))
                                        : __('unknown', 'js-support-ticket')); ?></dd>
                            </dl>

                            <?php /* The brief is a plain textarea rather than a
                                     button that fills a form in another plugin:
                                     it is the thing a writer wants whether they
                                     write in the knowledge base, in a document,
                                     or hand it to somebody else. */ ?>
                            <div class="jsst-form">
                                <div class="jsst-formgrid">
                                    <div class="jsst-frow jsst-frow-full">
                                        <label class="jsst-flabel" for="jsst-brief-<?php echo esc_attr($jsst_gap['signature']); ?>"><?php echo esc_html(__('A brief to write from', 'js-support-ticket')); ?></label>
                                        <div class="jsst-fval"><textarea id="jsst-brief-<?php echo esc_attr($jsst_gap['signature']); ?>" rows="<?php echo esc_attr(min(12, 3 + count($jsst_gap['questions']))); ?>" readonly="readonly" onclick="this.select();"><?php echo esc_textarea($jsst_brief['body']); ?></textarea></div>
                                    </div>
                                </div>
                            </div>

                            <div class="jsst-btnrow">
                                <?php if ($jsst_gap['write'] !== '') { ?>
                                    <a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url($jsst_gap['write']); ?>"><?php echo esc_html(__('Write the article', 'js-support-ticket')); ?></a>
                                <?php } ?>
                                <?php if ($jsst_gap['writefaq'] !== '' && $jsst_gap['writefaq'] !== $jsst_gap['write']) { ?>
                                    <a class="jsst-btn" href="<?php echo esc_url($jsst_gap['writefaq']); ?>"><?php echo esc_html(__('Add it as a FAQ', 'js-support-ticket')); ?></a>
                                <?php } ?>
                                <a class="jsst-btn" href="<?php echo esc_url($jsst_hide); ?>"><?php echo esc_html($jsst_showing
                                    ? __('Put it back on the list', 'js-support-ticket')
                                    : __('Never write this', 'js-support-ticket')); ?></a>
                                <?php if ($jsst_gap['write'] === '' && $jsst_gap['writefaq'] === '') { ?>
                                    <span class="jsst-formfoot-note"><?php echo esc_html(__('Neither the Knowledge Base nor the FAQ add-on is active, so there is nowhere here to write it. The brief above is yours to take anywhere.', 'js-support-ticket')); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            <?php } ?>

        </div>
    </div>
</div>
