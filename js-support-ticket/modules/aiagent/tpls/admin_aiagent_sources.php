<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Knowledge Sources. (Roadmap 6.0-AI-02)
 *
 * The answer to "will it invent things about my product". It cannot invent from
 * content it was never allowed to read, so this screen is where that content is
 * decided — and it is deliberately one screen rather than a setting buried in a
 * configuration tab, because a site owner who wants to know what their AI can
 * see should not have to reconstruct it from six places.
 *
 * Three sections, in the order the decision is actually made:
 *
 *   Approved sources  the six kinds of content, whether each may be used at
 *                     all, and how its exceptions run.
 *   Documents         the individual articles, pages and tickets inside the
 *                     source that is open, each approvable or excludable.
 *   Test console      what a real question would reach right now.
 *
 * The console is at the bottom on purpose. It is the check somebody makes after
 * changing something above it, and putting it first would invite the opposite
 * order — testing before governing, which tests nothing.
 */
if (!class_exists('JSSTaisources')) {
    echo esc_html(__('Source governance is not available on this site.', 'js-support-ticket'));
    return;
}

$jsst_policy  = isset(jssupportticket::$jsst_data['aipolicy']) ? jssupportticket::$jsst_data['aipolicy'] : array();
$jsst_sources = isset(jssupportticket::$jsst_data['aisources']) ? jssupportticket::$jsst_data['aisources'] : array();
$jsst_modes   = isset(jssupportticket::$jsst_data['aisourcemodes']) ? jssupportticket::$jsst_data['aisourcemodes'] : array();
$jsst_open    = isset(jssupportticket::$jsst_data['aisourceopen']) ? jssupportticket::$jsst_data['aisourceopen'] : '';
$jsst_alldocs = isset(jssupportticket::$jsst_data['aisourcedocs']) ? jssupportticket::$jsst_data['aisourcedocs'] : array();
$jsst_find    = isset(jssupportticket::$jsst_data['aisourcefind']) ? jssupportticket::$jsst_data['aisourcefind'] : '';
$jsst_console = isset(jssupportticket::$jsst_data['aiconsole']) ? jssupportticket::$jsst_data['aiconsole'] : array();

$jsst_langmode   = isset(jssupportticket::$jsst_data['ailangmode']) ? jssupportticket::$jsst_data['ailangmode'] : '';
$jsst_langmodes  = isset(jssupportticket::$jsst_data['ailangmodes']) ? jssupportticket::$jsst_data['ailangmodes'] : array();
$jsst_langsurvey = isset(jssupportticket::$jsst_data['ailangsurvey']) ? jssupportticket::$jsst_data['ailangsurvey'] : array('languages' => array(), 'total' => 0, 'known' => false);
$jsst_langsite   = isset(jssupportticket::$jsst_data['ailangsite']) ? jssupportticket::$jsst_data['ailangsite'] : '';
$jsst_langknown  = isset(jssupportticket::$jsst_data['ailangknown']) ? jssupportticket::$jsst_data['ailangknown'] : array();
$jsst_langbad    = isset(jssupportticket::$jsst_data['ailangunsearchable']) ? jssupportticket::$jsst_data['ailangunsearchable'] : array();

$jsst_base   = admin_url('admin.php?page=aiagent&jstlay=aiagent_sources');
/* Every link that reloads into a source's documents (search, paging, the
   tabs without JavaScript) lands on the Documents heading, not the top of the
   page, so the admin is not left scrolling back down to what they asked for. */
$jsst_docanchor = '#jsst-documents';
$jsst_action = wp_nonce_url(admin_url('admin.php?page=aiagent&task=saveaisources&action=jstask'), 'jsst-aiagent-sources');
$jsst_openrow = isset($jsst_sources[$jsst_open]) ? $jsst_sources[$jsst_open] : array();

/* Health words map onto the shared pill vocabulary once, here, rather than in
   five places down the page. 'unknown' is info rather than ok for the reason the
   connector screen sets out: a green tick that means "nobody checked" is the
   exact thing a health column must never say. */
$jsst_pills = array(
    'ok'      => 'jsst-pill-ok',
    'warn'    => 'jsst-pill-warn',
    'bad'     => 'jsst-pill-bad',
    'off'     => 'jsst-pill-off',
    'unknown' => 'jsst-pill-info',
);
$jsst_words = array(
    'ok'      => esc_html(__('In use', 'js-support-ticket')),
    'warn'    => esc_html(__('Answers nothing', 'js-support-ticket')),
    'bad'     => esc_html(__('Unavailable', 'js-support-ticket')),
    'off'     => esc_html(__('Not approved', 'js-support-ticket')),
    'unknown' => esc_html(__('Not checked', 'js-support-ticket')),
);

/* The headline number. "Four sources approved" says nothing about whether they
   hold anything, so what is counted is sources that can actually answer. */
$jsst_live = 0;
$jsst_eligible = 0;
foreach ($jsst_sources as $jsst_row) {
    if ($jsst_row['health'] === 'ok') $jsst_live++;
    $jsst_eligible += (int) $jsst_row['eligible'];
}

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Knowledge Sources', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_sources'); } ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('Everything the AI is allowed to answer from, and nothing else. A source that is not approved here is invisible to suggestions, to grounded answers and to automatic replies alike — and a document excluded inside an approved source is invisible the same way, from the next search onwards.', 'js-support-ticket')); ?>
            </p>

            <?php if (empty($jsst_policy['enabled'])) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-body">
                        <p class="jsst-hint">
                            <?php echo esc_html(__('AI is switched off for this site, so nothing on this page is being used at the moment. What you set here is kept and applies the moment it is switched back on.', 'js-support-ticket')); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_settings')); ?>"><?php echo esc_html(__('Settings', 'js-support-ticket')); ?></a>
                        </p>
                    </div>
                </div>
            <?php } ?>

            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_action); ?>">
                <input type="hidden" name="aisources" value="1" />
                <input type="hidden" name="src" value="<?php echo esc_attr($jsst_open); ?>" data-jsst-srcfield />

                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('What may be answered from', 'js-support-ticket')); ?></h2>
                        <p class="jsst-card-sub"><?php echo esc_html(__('Approving a source lets the whole of it be searched. The mode beside it decides what happens to the individual documents inside — everything unless you exclude it, or nothing unless you approve it.', 'js-support-ticket')); ?></p>
                        <div class="jsst-card-tools">
                            <span class="jsst-pill jsst-pill-info"><?php echo esc_html(sprintf(
                                /* translators: %s: how many documents the AI can reach */
                                _n('%s document reachable', '%s documents reachable', (int) $jsst_eligible, 'js-support-ticket'),
                                number_format_i18n($jsst_eligible)
                            )); ?></span>
                            <span class="jsst-pill jsst-pill-info"><?php echo esc_html(sprintf(
                                /* translators: 1: sources that can answer, 2: sources in total */
                                __('%1$d of %2$d answering', 'js-support-ticket'), $jsst_live, count($jsst_sources)
                            )); ?></span>
                        </div>
                    </div>

                    <?php /* Nought is the state this screen most needs to explain: every
                       switch can be on and the desk still answers nothing. The count
                       itself is a pill in the head -- a dashboard stat tile for one
                       number cost 90px to say "6" above a table that says it again. */
                    if ((int) $jsst_eligible < 1) { ?>
                        <div class="jsst-card-body">
                            <p class="jsst-hint"><?php echo esc_html(__('Nothing here can be answered from yet. Approve a source below that holds published content — an article, an FAQ or a canned response — and the AI has something to work with.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } ?>

                    <div class="jsst-table-wrap">
                        <table class="jsst-table jsst-table-fluid">
                            <thead>
                                <tr>
                                    <th class="jsst-col-name"><?php echo esc_html(__('Source', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-fit"><?php echo esc_html(__('Approved', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-fit jsst-col-menu"><?php echo esc_html(__('Which documents', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-fit"><?php echo esc_html(__('Reachable', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-fit"><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                                    <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_sources as $jsst_key => $jsst_row) {
                                $jsst_resync = wp_nonce_url(
                                    admin_url('admin.php?page=aiagent&task=resyncaisource&action=jstask&src=' . rawurlencode($jsst_key)),
                                    'jsst-aiagent-resync-' . $jsst_key
                                );
                            ?>
                                <?php $jsst_absent = empty($jsst_row['present']); ?>
                                <tr class="<?php echo ($jsst_key === $jsst_open) ? 'jsst-row-on' : ''; ?><?php echo $jsst_absent ? ' jsst-row-unavailable' : ''; ?>" data-jsst-srcrow="<?php echo esc_attr($jsst_key); ?>">
                                    <th scope="row" class="jsst-col-name">
                                        <?php /* The name opens this source's documents; a separate "Documents" action said the same thing twice. */ ?>
                                        <?php if ($jsst_absent) { /* No documents to open: its add-on is not here. */ ?>
                                            <span class="jsst-table-name"><?php echo esc_html($jsst_row['label']); ?></span>
                                        <?php } else { ?>
                                            <a class="jsst-table-name" data-jsst-src="<?php echo esc_attr($jsst_key); ?>" href="<?php echo esc_url(add_query_arg('src', $jsst_key, $jsst_base) . $jsst_docanchor); ?>" title="<?php echo esc_attr(__('Show its documents', 'js-support-ticket')); ?>"><?php echo esc_html($jsst_row['label']); ?></a>
                                        <?php } ?>
                                        <?php /* The short gloss, not the blurb. Six paragraphs of guidance
                                                 in six table cells is what made this screen read as an
                                                 article; the full blurb is shown once, on the Documents
                                                 card for the source that is actually open. */ ?>
                                        <span class="jsst-table-sub"><?php echo esc_html($jsst_row['gloss'] !== '' ? $jsst_row['gloss'] : $jsst_row['blurb']); ?></span>
                                    </th>
                                    <td class="jsst-col-fit">
                                        <label class="jsst-check">
                                            <input type="checkbox" name="src_<?php echo esc_attr($jsst_key); ?>" value="1" <?php checked(!$jsst_absent && !empty($jsst_row['approved'])); ?> <?php disabled($jsst_absent); ?> />
                                            <span><?php echo esc_html(__('Use it', 'js-support-ticket')); ?></span>
                                        </label>
                                        <?php if (empty($jsst_row['present']) && $jsst_row['needs'] !== '') { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                                /* translators: %s: the name of the add-on that owns this content */
                                                __('Needs %s', 'js-support-ticket'), $jsst_row['needs']
                                            )); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td class="jsst-col-fit jsst-col-menu">
                                        <?php /* Same field name and the same two stored values, as a menu.
                                                 The segmented control could not fit its two long labels
                                                 side by side in a table column, so it stacked -- and at
                                                 70px it was single-handedly setting the height of every
                                                 row on the screen. A menu is one line and still shows
                                                 which mode is in force. */ ?>
                                        <label class="screen-reader-text" for="mode_<?php echo esc_attr($jsst_key); ?>"><?php echo esc_html(sprintf(
                                            /* translators: %s: the name of a source of content */
                                            __('Which documents of %s count', 'js-support-ticket'), $jsst_row['label']
                                        )); ?></label>
                                        <?php /* Below about 1400 the column compresses with the rest of the
                                                 row and the chosen option can be cut short, so the full
                                                 wording is on the control itself. */ ?>
                                        <select class="jsst-select" id="mode_<?php echo esc_attr($jsst_key); ?>" name="mode_<?php echo esc_attr($jsst_key); ?>"
                                                title="<?php echo esc_attr(isset($jsst_modes[$jsst_row['mode']]['label']) ? $jsst_modes[$jsst_row['mode']]['label'] : ''); ?>" <?php disabled($jsst_absent); ?>>
                                            <?php foreach ($jsst_modes as $jsst_modeid => $jsst_mode) { ?>
                                                <option value="<?php echo esc_attr($jsst_modeid); ?>" <?php selected($jsst_row['mode'], $jsst_modeid); ?>
                                                        title="<?php echo esc_attr($jsst_mode['label']); ?>"><?php
                                                    echo esc_html(isset($jsst_mode['short']) ? $jsst_mode['short'] : $jsst_mode['label']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td class="jsst-col-fit jsst-num">
                                        <?php if ($jsst_row['eligible'] === null) { ?>
                                            &mdash;
                                        <?php } else { ?>
                                            <strong><?php echo esc_html(number_format_i18n((int) $jsst_row['eligible'])); ?></strong>
                                            <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                                /* translators: %s: total number of documents in this source */
                                                __('of %s', 'js-support-ticket'), number_format_i18n((int) $jsst_row['documents'])
                                            )); ?></span>
                                        <?php } ?>
                                        <?php if ((int) $jsst_row['indexed'] > 0) { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                                /* translators: %s: number of indexed passages */
                                                _n('%s passage indexed', '%s passages indexed', (int) $jsst_row['indexed'], 'js-support-ticket'),
                                                number_format_i18n((int) $jsst_row['indexed'])
                                            )); ?></span>
                                        <?php } ?>
                                        <?php if ((int) $jsst_row['excluded'] > 0) { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                                /* translators: %s: number of documents excluded one by one */
                                                _n('%s excluded by hand', '%s excluded by hand', (int) $jsst_row['excluded'], 'js-support-ticket'),
                                                number_format_i18n((int) $jsst_row['excluded'])
                                            )); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td class="jsst-col-fit">
                                        <?php /* A status cell carries a status. It used to carry the
                                                 pill plus up to three sentences -- and the first of
                                                 them, "Not an approved source", restated the unticked
                                                 box two columns to its left and made those rows 176px
                                                 tall. The reason is kept only where it says something
                                                 no other column on the row does: approved, and still
                                                 not answering. */ ?>
                                        <span class="jsst-pill <?php echo esc_attr($jsst_pills[$jsst_row['health']]); ?>"><span class="jsst-dot"></span><?php echo esc_html($jsst_words[$jsst_row['health']]); ?></span>
                                        <?php if (($jsst_row['health'] === 'warn' || $jsst_row['health'] === 'bad') && $jsst_row['reason'] !== '') { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html($jsst_row['reason']); ?></span>
                                        <?php } elseif ($jsst_row['health'] === 'ok' && (int) $jsst_row['synced'] > 0) { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                                /* translators: %s: how long ago the source was last re-read, e.g. "2 hours" */
                                                __('Re-read %s ago', 'js-support-ticket'),
                                                human_time_diff((int) $jsst_row['synced'], time())
                                            )); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td class="jsst-col-act"><span class="jsst-rowactions">
                                        <?php /* Nothing to re-read or manage where the add-on
                                                 that holds the content is not running. */
                                        if (!$jsst_absent) { ?>
                                        <a class="jsst-act" href="<?php echo esc_url($jsst_resync); ?>"><?php echo esc_html(__('Re-read', 'js-support-ticket')); ?></a>
                                        <?php } ?>
                                        <?php if (!$jsst_absent && !empty($jsst_row['manage'])) { ?>
                                            <a class="jsst-act" href="<?php echo esc_url(admin_url($jsst_row['manage'])); ?>"><?php echo esc_html(__('Manage content', 'js-support-ticket')); ?></a>
                                        <?php } ?>
                                    </span></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="jsst-card-foot">
                        <div class="jsst-btnrow">
                            <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('Save sources', 'js-support-ticket')); ?>" />
                            <span class="jsst-formfoot-note"><?php echo esc_html(__('Takes effect on the next search.', 'js-support-ticket')); ?></span>
                        </div>
                    </div>
                </div>
            </form>

            <?php /* Language sits between "which sources" and "which documents",
                     because it is the third answer to the same question: which
                     of the site's own material may answer this person.
                     (Roadmap 6.0-AI-09) */ ?>
            <?php if (!empty($jsst_langmodes)) { ?>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('Language', 'js-support-ticket')); ?></h2>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Answering people in their own language', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <?php if (count($jsst_langsurvey['languages']) > 1) { ?>
                            <span class="jsst-pill jsst-pill-info"><span class="jsst-dot"></span><?php echo esc_html(sprintf(
                                /* translators: %d: how many languages the indexed content is written in */
                                _n('%d language in your content', '%d languages in your content', count($jsst_langsurvey['languages']), 'js-support-ticket'),
                                count($jsst_langsurvey['languages']))); ?></span>
                        <?php } else { ?>
                            <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('One language', 'js-support-ticket')); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="jsst-card-body">

                    <?php if (count($jsst_langsurvey['languages']) < 2) { ?>
                        <p class="jsst-hint"><?php echo esc_html(__('Everything indexed here is in one language, so none of these settings change anything today. They start applying on their own the moment you publish something in a second one — there is nothing to come back and switch on.', 'js-support-ticket')); ?></p>
                    <?php } else { ?>
                        <p class="jsst-hint"><?php echo esc_html(__('Your content is in more than one language, so which of it answers a question is now a decision rather than an accident.', 'js-support-ticket')); ?></p>
                    <?php } ?>

                    <?php if (!empty($jsst_langsurvey['languages'])) { ?>
                        <dl class="jsst-facts">
                            <dt><?php echo esc_html(__('What your indexed content is written in', 'js-support-ticket')); ?></dt>
                            <dd>
                                <div class="jsst-chips">
                                <?php foreach ($jsst_langsurvey['languages'] as $jsst_code => $jsst_n) { ?>
                                    <span class="jsst-chip"><?php echo esc_html(JSSTailanguages::label($jsst_code) . ' · ' . sprintf(
                                        /* translators: %s: a number of indexed passages */
                                        __('%s passages', 'js-support-ticket'), number_format_i18n((int) $jsst_n))); ?></span>
                                <?php } ?>
                                </div>
                            </dd>
                        </dl>
                    <?php } elseif (empty($jsst_langsurvey['known'])) { ?>
                        <p class="jsst-fhelp"><?php echo esc_html(__('Nothing has reported what language it is in yet. The engine fills this in as it indexes, a batch at a time — on a large site it takes a few passes of the scheduled job.', 'js-support-ticket')); ?></p>
                    <?php } ?>

                    <?php /* The one thing on this page nothing here can fix. */ ?>
                    <?php if (!empty($jsst_langbad)) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(sprintf(
                                /* translators: %s: a comma separated list of language names */
                                __('Searching cannot reach your %s content', 'js-support-ticket'),
                                implode(', ', array_map(array('JSSTailanguages', 'label'), $jsst_langbad)))); ?></p>
                            <p class="jsst-empty-text"><?php echo esc_html(__('These languages are not written with spaces between words, and the database index this product searches with splits on spaces. The content is stored and it is counted above, but no question will ever match it — which until now looked exactly like having no content at all. Fixing it needs the search index rebuilt with a different word-splitter, which is a database change your host may need to make; ask support and quote this message.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } ?>

                    <form class="jsst-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=aiagent&task=saveailanguage&action=jstask'), 'jsst-aiagent-language')); ?>">
                        <div class="jsst-formgrid">
                            <div class="jsst-frow jsst-frow-lg">
                                <label class="jsst-flabel" for="jsst-langmode"><?php echo esc_html(__('When somebody asks in one language and your answer is in another', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select id="jsst-langmode" name="langmode">
                                        <?php foreach ($jsst_langmodes as $jsst_key => $jsst_meta) { ?>
                                            <option value="<?php echo esc_attr($jsst_key); ?>" <?php selected($jsst_langmode, $jsst_key); ?>><?php echo esc_html($jsst_meta['label']); ?></option>
                                        <?php } ?>
                                    </select>
                                    <?php if (isset($jsst_langmodes[$jsst_langmode])) { ?>
                                        <span class="jsst-fhelp"><?php echo esc_html($jsst_langmodes[$jsst_langmode]['blurb']); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="jsst-frow jsst-frow-md">
                                <label class="jsst-flabel" for="jsst-langfallback"><?php echo esc_html(__('When a question is too short to tell', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select id="jsst-langfallback" name="langfallback">
                                        <option value=""><?php echo esc_html(sprintf(
                                            /* translators: %s: the name of the site's own language */
                                            __('Use this site\'s own language (%s)', 'js-support-ticket'),
                                            wp_strip_all_tags(JSSTailanguages::label($jsst_langsite)))); ?></option>
                                        <?php foreach ($jsst_langknown as $jsst_code => $jsst_name) { ?>
                                            <option value="<?php echo esc_attr($jsst_code); ?>" <?php selected(get_option(JSSTailanguages::OPT_FALLBACK, ''), $jsst_code); ?>><?php echo esc_html($jsst_name); ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="jsst-fhelp"><?php echo esc_html(__('Most ticket subjects are three or four words, which is not enough to tell one language from another. An account holder\'s own profile setting is always used first.', 'js-support-ticket')); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="jsst-btnrow">
                            <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('Save', 'js-support-ticket')); ?>" />
                            <span class="jsst-formfoot-note"><?php echo esc_html(__('Changes which documents are used, not what is sent anywhere.', 'js-support-ticket')); ?></span>
                        </div>
                    </form>
                </div>
            </div>
            <?php } ?>

            <?php if ($jsst_open !== '' && !empty($jsst_openrow)) { ?>

            <h2 class="jsst-groupheading" id="jsst-documents"><?php echo esc_html(__('Documents', 'js-support-ticket')); ?></h2>

            <div class="jsst-chips jsst-srctabs" data-jsst-srctabs>
                <?php foreach ($jsst_sources as $jsst_key => $jsst_row) {
                    /* Tabs are for sources that can be opened. An absent one is
                       still listed, greyed, in the table above -- that is where
                       the admin learns what an add-on would bring. */
                    if (empty($jsst_row['present'])) continue; ?>
                    <a class="jsst-chip <?php echo ($jsst_key === $jsst_open) ? 'jsst-chip-lead' : ''; ?>" data-jsst-src="<?php echo esc_attr($jsst_key); ?>" href="<?php echo esc_url(add_query_arg('src', $jsst_key, $jsst_base) . $jsst_docanchor); ?>">
                        <?php echo esc_html($jsst_row['label']); ?>
                    </a>
                <?php } ?>
            </div>

            <?php /* One card per available source, all drawn now; only the open
                     one is visible. The tabs switch between them in place (script
                     at the foot of this file), so changing source costs no reload.
                     Search and paging still go to the server, and only ever for
                     the open source -- the others always show their first page. */
            foreach ($jsst_sources as $jsst_skey => $jsst_srow) {
                if (empty($jsst_srow['present'])) continue;
                $jsst_sdocs = isset($jsst_alldocs[$jsst_skey]) ? $jsst_alldocs[$jsst_skey]
                    : array('rows' => array(), 'total' => 0, 'page' => 1, 'pages' => 1, 'per' => 20);
                $jsst_sfind = ($jsst_skey === $jsst_open) ? $jsst_find : '';
                $jsst_sclear = wp_nonce_url(
                    admin_url('admin.php?page=aiagent&task=clearsourcerules&action=jstask&src=' . rawurlencode($jsst_skey)),
                    'jsst-aiagent-clear-' . $jsst_skey
                );
            ?>
                <div class="jsst-card" id="jsst-srccard-<?php echo esc_attr($jsst_skey); ?>" data-jsst-srccard="<?php echo esc_attr($jsst_skey); ?>"<?php echo ($jsst_skey === $jsst_open) ? '' : ' hidden'; ?>>
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html($jsst_srow['label']); ?></h2>
                        <?php /* What this source IS, then how it is governed and why --
                                 in that order, because the guidance
                                 is what somebody about to go through these documents
                                 needs first. It is said once here rather than in all six
                                 table rows above. */ ?>
                        <p class="jsst-card-sub"><?php echo esc_html($jsst_srow['blurb']); ?></p>
                        <p class="jsst-card-sub">
                            <?php if ($jsst_srow['mode'] === JSSTaisources::MODE_PICK) {
                                echo esc_html(__('This source answers only from documents that have been approved. Everything else in it, including anything published from now on, stays invisible until somebody approves it.', 'js-support-ticket'));
                            } else {
                                echo esc_html(__('This source answers from everything in it except what has been excluded. Exclude anything that is out of date, internal, or written for somebody other than a customer.', 'js-support-ticket'));
                            } ?>
                        </p>
                        <div class="jsst-card-tools">
                            <?php if ((int) $jsst_srow['allowed'] + (int) $jsst_srow['excluded'] > 0) { ?>
                                <a class="jsst-danger" href="<?php echo esc_url($jsst_sclear); ?>"
                                   onclick="return confirm('<?php echo esc_js(__('Clear every individual decision for this source?', 'js-support-ticket')); ?>');"><?php echo esc_html(__('Clear decisions', 'js-support-ticket')); ?></a>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="jsst-card-body">
                        <form class="jsst-form" method="get" action="<?php echo esc_url(admin_url('admin.php') . $jsst_docanchor); ?>">
                            <input type="hidden" name="page" value="aiagent" />
                            <input type="hidden" name="jstlay" value="aiagent_sources" />
                            <input type="hidden" name="src" value="<?php echo esc_attr($jsst_skey); ?>" />
                            <div class="jsst-btnrow">
                                <input type="search" class="jsst-inline-input" name="find" value="<?php echo esc_attr($jsst_sfind); ?>"
                                       placeholder="<?php echo esc_attr(__('Search titles', 'js-support-ticket')); ?>" />
                                <input type="submit" class="jsst-btn" value="<?php echo esc_attr(__('Search', 'js-support-ticket')); ?>" />
                                <?php if ($jsst_sfind !== '') { ?>
                                    <a class="jsst-btn" href="<?php echo esc_url(add_query_arg('src', $jsst_skey, $jsst_base) . $jsst_docanchor); ?>"><?php echo esc_html(__('Clear', 'js-support-ticket')); ?></a>
                                <?php } ?>
                                <span class="jsst-formfoot-note"><?php echo esc_html(sprintf(
                                    /* translators: %s: number of documents in this source */
                                    _n('%s document', '%s documents', (int) $jsst_sdocs['total'], 'js-support-ticket'),
                                    number_format_i18n((int) $jsst_sdocs['total'])
                                )); ?></span>
                            </div>
                        </form>
                    </div>

                    <?php if (empty($jsst_sdocs['rows'])) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(__('Nothing to govern here yet', 'js-support-ticket')); ?></p>
                            <p class="jsst-empty-text">
                                <?php echo ($jsst_sfind !== '')
                                    ? esc_html(__('No document in this source has a title matching that search.', 'js-support-ticket'))
                                    : esc_html(__('This source holds no documents on this site, so approving or excluding it changes nothing until something is published into it.', 'js-support-ticket')); ?>
                            </p>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html(__('Title', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('The AI can', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Decision', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_sdocs['rows'] as $jsst_doc) {
                                    /* Each link carries the source and page it was drawn on so
                                       the redirect can put somebody back exactly where they
                                       were - working down a list of four hundred pages is the
                                       only way this table is ever used. */
                                    $jsst_carry = array('src' => $jsst_skey);
                                    if ($jsst_sfind !== '') $jsst_carry['find'] = $jsst_sfind;
                                    if ((int) $jsst_sdocs['page'] > 1) $jsst_carry['dp'] = (int) $jsst_sdocs['page'];

                                    $jsst_rule_url = function ($jsst_verdict) use ($jsst_skey, $jsst_doc, $jsst_carry) {
                                        return wp_nonce_url(
                                            add_query_arg(array_merge($jsst_carry, array(
                                                'page'    => 'aiagent',
                                                'task'    => 'setsourcerule',
                                                'action'  => 'jstask',
                                                'doc'     => (int) $jsst_doc['id'],
                                                'verdict' => $jsst_verdict,
                                            )), admin_url('admin.php')),
                                            'jsst-aiagent-rule-' . $jsst_skey . '-' . (int) $jsst_doc['id']
                                        );
                                    };
                                ?>
                                    <tr>
                                        <th scope="row">
                                            <span class="jsst-table-name"><?php echo esc_html($jsst_doc['title'] !== '' ? $jsst_doc['title'] : sprintf(
                                                /* translators: %d: the document's numeric id */
                                                __('Untitled (#%d)', 'js-support-ticket'), (int) $jsst_doc['id']
                                            )); ?></span>
                                            <span class="jsst-table-sub">#<?php echo esc_html((int) $jsst_doc['id']); ?></span>
                                        </th>
                                        <td>
                                            <?php if (!empty($jsst_doc['eligible'])) { ?>
                                                <span class="jsst-cell-yes"><?php echo esc_html(__('quote this', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <span class="jsst-cell-no"><?php echo esc_html(__('never see this', 'js-support-ticket')); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if ($jsst_doc['rule'] === null) { ?>
                                                <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Follows the source', 'js-support-ticket')); ?></span>
                                            <?php } elseif ((int) $jsst_doc['rule'] === JSSTaisources::RULE_ALLOW) { ?>
                                                <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Approved', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span><?php echo esc_html(__('Excluded', 'js-support-ticket')); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td class="jsst-col-act"><span class="jsst-rowactions">
                                            <?php if ((int) $jsst_doc['rule'] !== JSSTaisources::RULE_ALLOW) { ?>
                                                <a class="jsst-act" href="<?php echo esc_url($jsst_rule_url('allow')); ?>"><?php echo esc_html(__('Approve', 'js-support-ticket')); ?></a>
                                            <?php } ?>
                                            <?php if ($jsst_doc['rule'] === null || (int) $jsst_doc['rule'] === JSSTaisources::RULE_ALLOW) { ?>
                                                <a class="jsst-act jsst-act-danger" href="<?php echo esc_url($jsst_rule_url('deny')); ?>"><?php echo esc_html(__('Exclude', 'js-support-ticket')); ?></a>
                                            <?php } ?>
                                            <?php if ($jsst_doc['rule'] !== null) { ?>
                                                <a class="jsst-act" href="<?php echo esc_url($jsst_rule_url('clear')); ?>"><?php echo esc_html(__('Reset', 'js-support-ticket')); ?></a>
                                            <?php } ?>
                                        </span></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ((int) $jsst_sdocs['pages'] > 1) {
                            $jsst_page = (int) $jsst_sdocs['page'];
                            $jsst_pageargs = array('src' => $jsst_skey);
                            if ($jsst_sfind !== '') $jsst_pageargs['find'] = $jsst_sfind;
                        ?>
                            <div class="jsst-card-foot">
                                <div class="jsst-btnrow">
                                    <?php if ($jsst_page > 1) { ?>
                                        <a class="jsst-btn" href="<?php echo esc_url(add_query_arg(array_merge($jsst_pageargs, array('dp' => $jsst_page - 1)), $jsst_base) . $jsst_docanchor); ?>"><?php echo esc_html(__('Previous', 'js-support-ticket')); ?></a>
                                    <?php } ?>
                                    <?php if ($jsst_page < (int) $jsst_sdocs['pages']) { ?>
                                        <a class="jsst-btn" href="<?php echo esc_url(add_query_arg(array_merge($jsst_pageargs, array('dp' => $jsst_page + 1)), $jsst_base) . $jsst_docanchor); ?>"><?php echo esc_html(__('Next', 'js-support-ticket')); ?></a>
                                    <?php } ?>
                                    <span class="jsst-formfoot-note"><?php echo esc_html(sprintf(
                                        /* translators: 1: current page, 2: total pages */
                                        __('Page %1$d of %2$d', 'js-support-ticket'), $jsst_page, (int) $jsst_sdocs['pages']
                                    )); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php } ?>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('Test what a question reaches', 'js-support-ticket')); ?></h2>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Retrieval console', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('Ask a question the way a customer would. This runs the same retrieval a real ticket runs, with the rules above applied, and calls no model and costs nothing.', 'js-support-ticket')); ?></p>
                </div>

                <div class="jsst-card-body">
                    <form class="jsst-form" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                        <input type="hidden" name="page" value="aiagent" />
                        <input type="hidden" name="jstlay" value="aiagent_sources" />
                        <input type="hidden" name="src" value="<?php echo esc_attr($jsst_open); ?>" data-jsst-srcfield />
                        <div class="jsst-formgrid">
                            <div class="jsst-frow jsst-frow-full">
                                <label class="jsst-flabel" for="jsst-ai-ask"><?php echo esc_html(__('Question', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <input type="text" id="jsst-ai-ask" name="ask" value="<?php echo esc_attr(isset($jsst_console['question']) ? $jsst_console['question'] : ''); ?>"
                                           placeholder="<?php echo esc_attr(__('How do I export my tickets?', 'js-support-ticket')); ?>" />
                                </div>
                            </div>
                            <?php /* Only the add-on's retrieval judges strictly or loosely;
                                     the free search has one rule, so no choice is offered. */
                            if (class_exists('JSSTaiagentretriever')) { ?>
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="jsst-ai-profile"><?php echo esc_html(__('Judged as', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select id="jsst-ai-profile" name="profile">
                                        <option value="reply" <?php selected(isset($jsst_console['profile']) ? $jsst_console['profile'] : 'reply', 'reply'); ?>><?php echo esc_html(__('Something the AI may state as fact (strict)', 'js-support-ticket')); ?></option>
                                        <option value="deflect" <?php selected(isset($jsst_console['profile']) ? $jsst_console['profile'] : 'reply', 'deflect'); ?>><?php echo esc_html(__('A suggestion the customer can ignore (relaxed)', 'js-support-ticket')); ?></option>
                                    </select>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="jsst-btnrow">
                            <input type="submit" class="jsst-btn jsst-btn-primary" value="<?php echo esc_attr(__('Search', 'js-support-ticket')); ?>" />
                        </div>
                    </form>

                    <?php if (!empty($jsst_console['note'])) { ?>
                        <p class="jsst-hint"><?php echo esc_html($jsst_console['note']); ?></p>
                    <?php } ?>
                </div>

                <?php /* No add-on: the free search the ticket form runs, shown the
                         way a customer would see it, with the words behind each. */
                if (!empty($jsst_console['ran']) && isset($jsst_console['free'])) {
                    $jsst_free = $jsst_console['free'];
                    $jsst_freeresults = isset($jsst_free['results']) ? (array) $jsst_free['results'] : array(); ?>
                    <div class="jsst-card-body">
                        <p class="jsst-hint"><?php echo esc_html(__('This is what the ticket form would suggest for this question, using the free search. No model is called.', 'js-support-ticket')); ?></p>
                        <dl class="jsst-facts">
                            <dt><?php echo esc_html(__('Words searched on', 'js-support-ticket')); ?></dt>
                            <dd><?php echo empty($jsst_free['terms'])
                                ? esc_html(__('None. Every word was too short or too common to search on.', 'js-support-ticket'))
                                : esc_html(implode(', ', (array) $jsst_free['terms'])); ?>
                                <span class="jsst-table-sub"><?php echo esc_html(__('Common words like "the", "help" and "not working" are ignored.', 'js-support-ticket')); ?></span></dd>
                            <dt><?php echo esc_html(__('Shown', 'js-support-ticket')); ?></dt>
                            <dd class="jsst-num"><strong><?php echo esc_html(number_format_i18n(count($jsst_freeresults))); ?></strong>
                                <span class="jsst-table-sub"><?php echo esc_html(__('A result must share a word in its title, or two in its text, and score close to the best match.', 'js-support-ticket')); ?></span></dd>
                        </dl>
                    </div>
                    <?php if (empty($jsst_freeresults)) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(__('Nothing would be suggested', 'js-support-ticket')); ?></p>
                            <p class="jsst-empty-text"><?php echo esc_html(__('No approved article, FAQ or page is close enough to this question. The customer sees no suggestions rather than unrelated ones.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html(__('Title', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Source', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Why it matched', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Score', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php /* Which saved replies a customer would actually be shown. */
                                $jsst_cannedids = array();
                                foreach ($jsst_freeresults as $jsst_fr) {
                                    $jsst_fr = (array) $jsst_fr;
                                    if (isset($jsst_fr['content_type']) && $jsst_fr['content_type'] === 'canned') $jsst_cannedids[] = (int) $jsst_fr['id'];
                                }
                                $jsst_forcustomers = (!empty($jsst_cannedids) && class_exists('JSSTcannedresponsesModel'))
                                    ? JSSTcannedresponsesModel::customerSuggestable($jsst_cannedids) : array();
                                foreach ($jsst_freeresults as $jsst_fr) {
                                    $jsst_fr = (array) $jsst_fr;
                                    $jsst_ftype = isset($jsst_fr['content_type']) ? $jsst_fr['content_type'] : ''; ?>
                                    <tr>
                                        <th scope="row">
                                            <?php if (!empty($jsst_fr['url'])) { ?>
                                                <a class="jsst-table-name" href="<?php echo esc_url($jsst_fr['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($jsst_fr['title']); ?></a>
                                            <?php } else { ?>
                                                <span class="jsst-table-name"><?php echo esc_html($jsst_fr['title']); ?></span>
                                            <?php } ?>
                                            <span class="jsst-table-sub"><?php echo esc_html(isset($jsst_fr['excerpt']) ? $jsst_fr['excerpt'] : ''); ?></span>
                                        </th>
                                        <td><?php echo esc_html(isset($jsst_sources[$jsst_ftype]['label']) ? $jsst_sources[$jsst_ftype]['label'] : $jsst_ftype); ?>
                                            <?php if ($jsst_ftype === 'canned') { ?>
                                                <span class="jsst-table-sub"><?php echo isset($jsst_forcustomers[(int) $jsst_fr['id']])
                                                    ? esc_html(__('Also shown to customers.', 'js-support-ticket'))
                                                    : esc_html(__('Agents only. Tick "Also suggest this to customers" on the saved reply to show it to them.', 'js-support-ticket')); ?></span>
                                            <?php } ?></td>
                                        <td><?php echo !empty($jsst_fr['matched'])
                                            ? esc_html(implode(', ', (array) $jsst_fr['matched']))
                                            : esc_html(__('Title words (WordPress search)', 'js-support-ticket')); ?></td>
                                        <td class="jsst-num"><?php echo esc_html(number_format_i18n((float) (isset($jsst_fr['total_relevance']) ? $jsst_fr['total_relevance'] : 0), 2)); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php if (!empty($jsst_console['ran']) && empty($jsst_console['note']) && !isset($jsst_console['free'])) {
                    $jsst_diag = isset($jsst_console['diagnostics']) ? $jsst_console['diagnostics'] : array();
                    $jsst_tokens = isset($jsst_diag['tokens']) ? (array) $jsst_diag['tokens'] : array();
                ?>
                    <div class="jsst-card-body">
                        <?php if (!empty($jsst_diag['unavailable'])) { ?>
                            <p class="jsst-hint"><?php echo esc_html($jsst_diag['unavailable']); ?></p>
                        <?php } ?>

                        <dl class="jsst-facts">
                            <dt><?php echo esc_html(__('Words searched on', 'js-support-ticket')); ?></dt>
                            <dd><?php echo empty($jsst_tokens)
                                ? esc_html(__('None — the question was too short, or made only of words every document contains.', 'js-support-ticket'))
                                : esc_html(implode(', ', $jsst_tokens)); ?></dd>

                            <dt><?php echo esc_html(__('Sources reached', 'js-support-ticket')); ?></dt>
                            <dd><?php
                                $jsst_used = isset($jsst_diag['sources_used']) ? (array) $jsst_diag['sources_used'] : array();
                                $jsst_names = array();
                                foreach ($jsst_used as $jsst_u) {
                                    $jsst_names[] = isset($jsst_sources[$jsst_u]['label']) ? $jsst_sources[$jsst_u]['label'] : $jsst_u;
                                }
                                echo empty($jsst_names)
                                    ? esc_html(__('None. Nothing is approved, or nothing approved holds anything.', 'js-support-ticket'))
                                    : esc_html(implode(', ', $jsst_names));
                            ?></dd>

                            <dt><?php echo esc_html(__('Passages considered', 'js-support-ticket')); ?></dt>
                            <dd class="jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_diag['candidates']) ? (int) $jsst_diag['candidates'] : 0)); ?></dd>

                            <?php if (isset($jsst_diag['governed'])) { ?>
                                <dt><?php echo esc_html(__('Kept out by these rules', 'js-support-ticket')); ?></dt>
                                <dd class="jsst-num">
                                    <?php echo esc_html(number_format_i18n((int) $jsst_diag['governed'])); ?>
                                    <?php if ((int) $jsst_diag['governed'] > 0) { ?>
                                        <span class="jsst-table-sub"><?php echo esc_html(__('Matched the question but is not approved, so it was never a candidate.', 'js-support-ticket')); ?></span>
                                    <?php } ?>
                                </dd>
                            <?php } ?>

                            <dt><?php echo esc_html(__('Best match', 'js-support-ticket')); ?></dt>
                            <dd>
                                <span class="jsst-num"><?php echo esc_html((isset($jsst_diag['best_coverage']) ? (int) $jsst_diag['best_coverage'] : 0) . '%'); ?></span>
                                <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                    /* translators: %d: the percentage a passage must reach to be used */
                                    __('needs %d%% to be used', 'js-support-ticket'),
                                    isset($jsst_diag['min_coverage']) ? (int) $jsst_diag['min_coverage'] : 0
                                )); ?></span>
                            </dd>

                            <?php /* A percentage on its own misleads on a short question: with two
                                     search terms a page containing one of them scores 50%, which
                                     clears a 50% floor while sharing a single word. Both rules have
                                     to pass, so both are shown or the verdict looks arbitrary. */ ?>
                            <dt><?php echo esc_html(__('Words it shared', 'js-support-ticket')); ?></dt>
                            <dd>
                                <span class="jsst-num"><?php echo esc_html(sprintf(
                                    /* translators: 1: words matched, 2: words searched on */
                                    __('%1$d of %2$d', 'js-support-ticket'),
                                    isset($jsst_diag['best_hits']) ? (int) $jsst_diag['best_hits'] : 0,
                                    count($jsst_tokens)
                                )); ?></span>
                                <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                    /* translators: %d: the minimum number of words that must match */
                                    __('needs at least %d', 'js-support-ticket'),
                                    isset($jsst_diag['min_hits']) ? (int) $jsst_diag['min_hits'] : 0
                                )); ?></span>
                            </dd>

                            <dt><?php echo esc_html(__('Used', 'js-support-ticket')); ?></dt>
                            <dd class="jsst-num"><strong><?php echo esc_html(number_format_i18n(count((array) $jsst_console['snippets']))); ?></strong></dd>

                            <?php $jsst_rank = isset($jsst_diag['ranking']) ? $jsst_diag['ranking'] : array();
                            if (!empty($jsst_rank)) { ?>
                                <?php /* What the prompt would cost and what the budget dropped to
                                         get there. Without it, lowering the budget has a visible
                                         effect nowhere. */ ?>
                                <dt><?php echo esc_html(__('Text it would be given', 'js-support-ticket')); ?></dt>
                                <dd>
                                    <span class="jsst-num"><?php echo esc_html(sprintf(
                                        /* translators: 1: tokens used, 2: the configured token budget */
                                        __('%1$s of %2$s tokens', 'js-support-ticket'),
                                        number_format_i18n((int) $jsst_rank['tokens_used']),
                                        number_format_i18n((int) $jsst_rank['token_budget'])
                                    )); ?></span>
                                    <?php if ((int) $jsst_rank['dropped_similar'] > 0 || (int) $jsst_rank['dropped_budget'] > 0) { ?>
                                        <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                            /* translators: 1: passages dropped as near-duplicates, 2: passages dropped for budget */
                                            __('%1$d dropped as near-duplicate, %2$d dropped for budget', 'js-support-ticket'),
                                            (int) $jsst_rank['dropped_similar'], (int) $jsst_rank['dropped_budget']
                                        )); ?></span>
                                    <?php } ?>
                                </dd>
                            <?php } ?>

                            <dt><?php echo esc_html(__('How it searched', 'js-support-ticket')); ?></dt>
                            <dd>
                                <?php echo empty($jsst_diag['chunked'])
                                    ? esc_html(__('One query per source — the unified passage index is not in use yet.', 'js-support-ticket'))
                                    : esc_html(__('One query over the unified passage index.', 'js-support-ticket')); ?>
                                <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                    /* translators: %d: how long the search took in milliseconds */
                                    __('took %d ms', 'js-support-ticket'),
                                    isset($jsst_diag['elapsed_ms']) ? (int) $jsst_diag['elapsed_ms'] : 0
                                )); ?></span>
                            </dd>
                        </dl>
                    </div>

                    <?php if (empty($jsst_console['snippets'])) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(__('Nothing was good enough to answer from', 'js-support-ticket')); ?></p>
                            <p class="jsst-empty-text">
                                <?php if (!empty($jsst_diag['governed'])) {
                                    echo esc_html(__('Some passages did match this question and were kept out by the rules above. If that was not what you meant, approve the document that should have answered it.', 'js-support-ticket'));
                                } else {
                                    echo esc_html(__('A real ticket asking this would be handed to a person. Writing content that answers this question directly is usually a better fix than lowering the threshold.', 'js-support-ticket'));
                                } ?>
                            </p>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html(__('Title', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Source', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('Match', 'js-support-ticket')); ?></th>
                                        <th><?php echo esc_html(__('The text it would be given', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ((array) $jsst_console['snippets'] as $jsst_snip) {
                                    $jsst_type = isset($jsst_snip['source_type']) ? $jsst_snip['source_type'] : '';
                                ?>
                                    <tr>
                                        <th scope="row">
                                            <?php if (!empty($jsst_snip['url'])) { ?>
                                                <a class="jsst-table-name" href="<?php echo esc_url($jsst_snip['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($jsst_snip['title']); ?></a>
                                            <?php } else { ?>
                                                <span class="jsst-table-name"><?php echo esc_html($jsst_snip['title']); ?></span>
                                            <?php } ?>
                                            <?php if (isset($jsst_snip['source_id'])) { ?>
                                                <span class="jsst-table-sub">#<?php echo esc_html((int) $jsst_snip['source_id']); ?></span>
                                            <?php } ?>
                                        </th>
                                        <td><?php echo esc_html(isset($jsst_sources[$jsst_type]['label']) ? $jsst_sources[$jsst_type]['label'] : $jsst_type); ?></td>
                                        <td class="jsst-num"><?php echo esc_html((isset($jsst_snip['coverage']) ? (int) $jsst_snip['coverage'] : 0) . '%'); ?></td>
                                        <td><?php echo esc_html(isset($jsst_snip['passage']) ? $jsst_snip['passage'] : ''); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>

        </div>
    </div>
</div>
<?php
/* The Documents tab switcher. Every available source's card is already on the
   page, so a tab only has to show one and hide the rest -- no reload. The tabs
   (and the source names in the table above) stay real links, so without
   JavaScript they still work, one reload each. The address bar follows the tab,
   so reloading, bookmarking, and the redirect after Approve / Exclude all come
   back to the same source; the forms that carry a source follow it too. */
$jsst_srctabs_js = "
(function () {
    var bar = document.querySelector('[data-jsst-srctabs]');
    if (!bar || !window.URL) { return; }
    var cards = {};
    Array.prototype.forEach.call(document.querySelectorAll('[data-jsst-srccard]'), function (c) {
        cards[c.getAttribute('data-jsst-srccard')] = c;
    });
    var heading = document.getElementById('jsst-documents');
    /* The source the server opened may carry a search or a page; going back to
       it restores that address rather than dropping it. */
    var first = null;
    for (var k in cards) { if (!cards[k].hidden) { first = k; break; } }
    var firstUrl = location.href;
    function show(key) {
        Object.keys(cards).forEach(function (k) { cards[k].hidden = (k !== key); });
        Array.prototype.forEach.call(bar.querySelectorAll('[data-jsst-src]'), function (a) {
            var on = a.getAttribute('data-jsst-src') === key;
            a.classList.toggle('jsst-chip-lead', on);
            if (on) { a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('aria-current'); }
        });
        Array.prototype.forEach.call(document.querySelectorAll('[data-jsst-srcrow]'), function (r) {
            r.classList.toggle('jsst-row-on', r.getAttribute('data-jsst-srcrow') === key);
        });
        Array.prototype.forEach.call(document.querySelectorAll('[data-jsst-srcfield]'), function (f) { f.value = key; });
        if (history.replaceState) {
            var url;
            if (key === first) {
                url = firstUrl;
            } else {
                url = new URL(location.href);
                url.searchParams.set('src', key);
                url.searchParams.delete('find');
                url.searchParams.delete('dp');
                url.hash = 'jsst-documents';
                url = url.toString();
            }
            history.replaceState(null, '', url);
        }
    }
    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('a[data-jsst-src]') : null;
        if (!a || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) { return; }
        var key = a.getAttribute('data-jsst-src');
        if (!cards[key]) { return; }
        e.preventDefault();
        show(key);
        /* From the table above, bring the Documents section into view; from
           the tabs, stay put -- they are already beside it. */
        if (!bar.contains(a) && heading) { heading.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
})();
";
wp_add_inline_script('js-support-ticket-main-js', $jsst_srctabs_js);
?>
