<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Overview. (Roadmap 6.0-AI-01)
 *
 * The screen that replaces reading three menus and assembling the answer
 * yourself. It answers, in this order, the questions a site owner actually asks:
 * is AI on at all, does anything leave this server, what exactly does it do
 * here, is it set up, and what has it been doing.
 *
 * The surfaces table is the point of the page. Every AI-shaped thing in the
 * product is a row, whether or not the add-on providing it is installed, and
 * each row says its lane out loud — because "does this leave my server" is the
 * question, and a feature list that does not answer it is decoration.
 *
 * Built from the shared admin vocabulary of 4.5-FE-06 (cards, pills, metrics,
 * facts, tables) so this screen and the ones delivered before it read as one
 * product.
 */
if (!class_exists('JSSTaipolicy')) {
    echo esc_html(__('The AI policy is not available.', 'js-support-ticket'));
    return;
}

$jsst_policy   = isset(jssupportticket::$jsst_data['aipolicy']) ? jssupportticket::$jsst_data['aipolicy'] : array();
$jsst_surfaces = isset(jssupportticket::$jsst_data['aisurfaces']) ? jssupportticket::$jsst_data['aisurfaces'] : array();
$jsst_engines  = isset(jssupportticket::$jsst_data['aiengines']) ? jssupportticket::$jsst_data['aiengines'] : array();
$jsst_usage    = isset(jssupportticket::$jsst_data['aiusage']) ? jssupportticket::$jsst_data['aiusage'] : array();
$jsst_corpus   = isset(jssupportticket::$jsst_data['aicorpus']) ? jssupportticket::$jsst_data['aicorpus'] : array('documents' => 0, 'live' => 0, 'sources' => 0, 'available' => false);

$jsst_settingsurl = admin_url('admin.php?page=aiagent&jstlay=aiagent_settings');
$jsst_auditurl    = admin_url('admin.php?page=aiagent&jstlay=aiagent_audit');
$jsst_sourcesurl  = admin_url('admin.php?page=aiagent&jstlay=aiagent_sources');
$jsst_on          = !empty($jsst_policy['enabled']);
$jsst_states      = isset($jsst_policy['states']) ? $jsst_policy['states'] : array();
$jsst_lanes       = isset($jsst_policy['lanes']) ? $jsst_policy['lanes'] : array();
$jsst_current     = isset($jsst_engines['current']) ? $jsst_engines['current'] : '';
$jsst_enginerows  = isset($jsst_engines['engines']) ? $jsst_engines['engines'] : array();
$jsst_ir          = in_array('aiagent', jssupportticket::$_active_addons);

/* How many of the declared surfaces are actually running. Counted rather than
   listed in the metric, because "4 of 6" is a fact somebody can act on and a
   list of six names in a small box is not. */
$jsst_live = 0;
$jsst_available = 0;
foreach ($jsst_surfaces as $jsst_surface) {
    if (!empty($jsst_surface['present'])) { $jsst_available++; }
    if (!empty($jsst_surface['live'])) { $jsst_live++; }
}

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('AI Agent', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent'); } ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('Everything this help desk does with AI: what it answers from, what leaves this server, and what it has done.', 'js-support-ticket')); ?>
            </p><?php JSSTlayout::why(__('Everything this help desk does with AI, in one place: what it answers from, whether anything leaves this server when it does, and what it has been doing. Nothing on this page asks a model anything — it reports your own configuration.', 'js-support-ticket')); ?>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('How it stands', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <?php if ($jsst_on) { ?>
                            <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('AI is on', 'js-support-ticket')); ?></span>
                        <?php } else { ?>
                            <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('AI is switched off', 'js-support-ticket')); ?></span>
                        <?php } ?>
                        <a class="jsst-pill jsst-pill-info" href="<?php echo esc_url($jsst_settingsurl); ?>"><?php echo esc_html(__('Settings', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <div class="jsst-card-body">
                    <p class="jsst-hint"><?php echo esc_html(isset($jsst_policy['notice']) ? $jsst_policy['notice'] : ''); ?></p>

                    <div class="jsst-metrics">
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html($jsst_live . ' / ' . $jsst_available); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Features live', 'js-support-ticket')); ?></span>
                        </div>
                        <?php /* What it can read. (Roadmap 6.0-AI-02)

                           Every other number on this card is about permission -
                           which lanes are open, which engine, how much it has
                           cost. None of them says whether there is anything to
                           answer *from*, and a desk with every switch on and an
                           empty corpus answers nothing at all. It said so
                           nowhere until this, so an empty suggestions panel
                           read as a broken feature rather than an empty
                           library. */
                        if (!empty($jsst_corpus['available'])) { ?>
                            <div class="jsst-metric<?php echo ((int) $jsst_corpus['documents'] < 1) ? '' : ' jsst-metric-quiet'; ?>">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_corpus['documents'])); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Documents it can read', 'js-support-ticket')); ?></span>
                            </div>
                            <div class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html((int) $jsst_corpus['live'] . ' / ' . (int) $jsst_corpus['sources']); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Sources answering', 'js-support-ticket')); ?></span>
                            </div>
                        <?php } ?>
                        <?php /* Said once, where it changes what somebody does next. */
                        if (!empty($jsst_corpus['available']) && (int) $jsst_corpus['documents'] < 1) { ?>
                            <p class="jsst-hint"><?php echo esc_html(__('There is nothing for the AI to answer from yet. Suggestions and automatic answers will stay empty until a source it is allowed to read holds a published document.', 'js-support-ticket')); ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_sources')); ?>"><?php echo esc_html(__('Choose what it may read', 'js-support-ticket')); ?></a></p>
                        <?php } ?>
                        <?php foreach ($jsst_lanes as $jsst_laneid => $jsst_lane) { ?>
                            <div class="jsst-metric">
                                <span class="jsst-metric-value">
                                    <?php if ($jsst_on && !empty($jsst_states[$jsst_laneid])) { ?>
                                        <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('On', 'js-support-ticket')); ?></span>
                                    <?php } else { ?>
                                        <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Off', 'js-support-ticket')); ?></span>
                                    <?php } ?>
                                </span>
                                <span class="jsst-metric-label"><?php echo esc_html($jsst_lane['label']); ?></span>
                            </div>
                        <?php } ?>
                    </div>

                    <dl class="jsst-facts">
                        <?php foreach ($jsst_lanes as $jsst_laneid => $jsst_lane) { ?>
                            <dt><?php echo esc_html($jsst_lane['label']); ?></dt>
                            <dd>
                                <?php echo esc_html($jsst_lane['blurb']); ?>
                                <span class="jsst-table-sub"><?php echo esc_html($jsst_lane['leaves']); ?></span>
                            </dd>
                        <?php } ?>
                    </dl>
                </div>
            </div>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('What AI does on this site', 'js-support-ticket')); ?></h2>
            <?php /* Sorted by what somebody can do about each row: use it now, add
                     a key, or add an add-on. One table of eight rows mixing the
                     three read as "most of this is broken". */
            $jsst_surfacegroups = array(
                'now'   => array(__('You have now', 'js-support-ticket'), __('Works on this site as it is set up today. A row that is switched off can be switched on.', 'js-support-ticket')),
                'key'   => array(__('Add an AI key to get', 'js-support-ticket'), __('Installed, but needs an AI engine and key. Add one under Settings.', 'js-support-ticket')),
                'addon' => array(__('Needs an add-on', 'js-support-ticket'), __('Not installed on this site.', 'js-support-ticket')),
            );
            foreach ($jsst_surfacegroups as $jsst_gkey => $jsst_ghead) {
                $jsst_grows = array();
                foreach ($jsst_surfaces as $jsst_surface) {
                    $jsst_sgroup = isset($jsst_surface['group']) ? $jsst_surface['group'] : (!empty($jsst_surface['present']) ? 'now' : 'addon');
                    if ($jsst_sgroup === $jsst_gkey) { $jsst_grows[] = $jsst_surface; }
                }
                if (empty($jsst_grows)) { continue; } ?>
            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html($jsst_ghead[0]); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html($jsst_ghead[1]); ?></p>
                    <?php if ($jsst_gkey === 'key') { ?>
                        <div class="jsst-card-tools"><a class="jsst-pill jsst-pill-info" href="<?php echo esc_url($jsst_settingsurl . '#AIEngine'); ?>"><?php echo esc_html(__('Add a key', 'js-support-ticket')); ?></a></div>
                    <?php } elseif ($jsst_gkey === 'addon') { ?>
                        <div class="jsst-card-tools"><a class="jsst-pill jsst-pill-info" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=license')); ?>"><?php echo esc_html(__('License & Add-ons', 'js-support-ticket')); ?></a></div>
                    <?php } ?>
                </div>
                <div class="jsst-card-body jsst-card-flush">
                    <div class="jsst-table-wrap">
                        <table class="jsst-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html(__('Feature', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Where it runs', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('State', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Set up in', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_grows as $jsst_surface) {
                                $jsst_lanename = isset($jsst_lanes[$jsst_surface['lane']]['label'])
                                    ? $jsst_lanes[$jsst_surface['lane']]['label'] : $jsst_surface['lane'];
                                ?>
                                <tr class="<?php echo !empty($jsst_surface['live']) ? 'jsst-row-on' : ''; ?>">
                                    <td>
                                        <span class="jsst-table-name"><?php echo esc_html($jsst_surface['label']); ?></span>
                                        <span class="jsst-table-sub"><?php echo esc_html($jsst_surface['blurb']); ?></span>
                                    </td>
                                    <td>
                                        <?php echo esc_html($jsst_lanename); ?>
                                        <?php if ($jsst_surface['lane'] === JSSTaipolicy::LANE_ONSITE) { ?>
                                            <span class="jsst-table-sub"><?php echo esc_html(__('Nothing leaves this server.', 'js-support-ticket')); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (empty($jsst_surface['present'])) { ?>
                                            <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Not installed', 'js-support-ticket')); ?></span>
                                            <?php if (!empty($jsst_surface['needs'])) { ?>
                                                <span class="jsst-table-sub"><?php echo esc_html($jsst_surface['needs']); ?></span>
                                            <?php } ?>
                                        <?php } elseif (!empty($jsst_surface['live'])) { ?>
                                            <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Live', 'js-support-ticket')); ?></span>
                                        <?php } elseif (isset($jsst_surface['haskey']) && empty($jsst_surface['haskey'])) { ?>
                                            <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Needs a key', 'js-support-ticket')); ?></span>
                                        <?php } elseif (empty($jsst_surface['laneopen'])) { ?>
                                            <span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span><?php echo esc_html(__('Lane is off', 'js-support-ticket')); ?></span>
                                            <span class="jsst-table-sub"><?php
                                                /* translators: %s: the name of an AI lane */
                                                echo esc_html(sprintf(__('Switch %s on to use this.', 'js-support-ticket'), $jsst_lanename)); ?></span>
                                        <?php } else { ?>
                                            <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Switched off', 'js-support-ticket')); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($jsst_surface['where']) && !empty($jsst_surface['present'])) { ?>
                                            <a href="<?php echo esc_url($jsst_surface['where']); ?>"><?php echo esc_html($jsst_surface['wherename']); ?></a>
                                        <?php } else { ?>
                                            <span class="jsst-table-sub">—</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('The answer engine', 'js-support-ticket')); ?></h2>
            <div class="jsst-cards">
                <?php foreach ($jsst_enginerows as $jsst_id => $jsst_engine) {
                    $jsst_chosen = ($jsst_id === $jsst_current);

                    /* ONE state per engine, not two.
                       Being the chosen engine and being set up were drawn as two
                       independent pills, so the engine this site runs on with no
                       key stored read "In use" and "Not set up" side by side --
                       two true facts that contradict each other as a status.
                       They are one question ("can this engine answer right now?")
                       and it has one answer, with the thing to do next under it. */
                    $jsst_laneopen   = !empty($jsst_engine['laneopen']);
                    $jsst_isset_up   = !empty($jsst_engine['configured']);
                    $jsst_statetone  = 'off';
                    $jsst_statenote  = '';
                    $jsst_statefix   = '';
                    $jsst_statefixto = '';
                    if ($jsst_chosen && !$jsst_laneopen) {
                        $jsst_statetone  = 'warn';
                        $jsst_statelabel = __('Chosen, but its lane is off', 'js-support-ticket');
                        $jsst_statenote  = __('Nothing is asked of it until the lane it runs in is switched on.', 'js-support-ticket');
                        $jsst_statefix   = __('Open the switches', 'js-support-ticket');
                        $jsst_statefixto = $jsst_settingsurl . '#AISwitches';
                    } elseif ($jsst_chosen && !$jsst_isset_up) {
                        $jsst_statetone  = 'warn';
                        $jsst_statelabel = __('Chosen, not set up yet', 'js-support-ticket');
                        $jsst_statenote  = __('This is the engine the site would ask, but it cannot answer yet.', 'js-support-ticket');
                        $jsst_statefix   = __('Set it up', 'js-support-ticket');
                        $jsst_statefixto = $jsst_settingsurl . '#AIEngine';
                    } elseif ($jsst_chosen) {
                        $jsst_statetone  = 'ok';
                        $jsst_statelabel = __('In use', 'js-support-ticket');
                    } elseif (!$jsst_laneopen) {
                        $jsst_statelabel = __('Lane is off', 'js-support-ticket');
                    } elseif ($jsst_isset_up) {
                        $jsst_statetone  = 'info';
                        $jsst_statelabel = __('Set up, not in use', 'js-support-ticket');
                    } else {
                        $jsst_statelabel = __('Not set up', 'js-support-ticket');
                    }
                    ?>
                    <div class="jsst-card jsst-card-half">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html($jsst_engine['label']); ?></h2>
                            <div class="jsst-card-tools">
                                <?php if (!$jsst_chosen && !empty($jsst_engine['recommended'])) { ?>
                                    <span class="jsst-pill jsst-pill-info"><?php echo esc_html(__('Recommended', 'js-support-ticket')); ?></span>
                                <?php } ?>
                                <span class="jsst-pill jsst-pill-<?php echo esc_attr($jsst_statetone); ?>"><span class="jsst-dot"></span><?php echo esc_html($jsst_statelabel); ?></span>
                            </div>
                        </div>
                        <div class="jsst-card-body">
                            <?php if ($jsst_statenote !== '') { ?>
                                <p class="jsst-hint"><?php echo esc_html($jsst_statenote); ?>
                                    <?php if ($jsst_statefix !== '') { ?>
                                        <a href="<?php echo esc_url($jsst_statefixto); ?>"><?php echo esc_html($jsst_statefix); ?></a>
                                    <?php } ?>
                                </p>
                            <?php } ?>
                            <p class="jsst-hint"><?php echo esc_html($jsst_engine['blurb']); ?></p>
                            <?php if ($jsst_chosen && $jsst_id === 'zywrap' && isset($jsst_usage['zywrap'])) { ?>
                                <div class="jsst-metrics">
                                    <div class="jsst-metric">
                                        <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_usage['zywrap']['runs'])); ?></span>
                                        <span class="jsst-metric-label"><?php echo esc_html(__('Requests', 'js-support-ticket')); ?></span>
                                    </div>
                                    <div class="jsst-metric">
                                        <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_usage['zywrap']['tokens'])); ?></span>
                                        <span class="jsst-metric-label"><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></span>
                                    </div>
                                    <div class="jsst-metric">
                                        <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_usage['zywrap']['errors'])); ?></span>
                                        <span class="jsst-metric-label"><?php echo esc_html(__('Failed', 'js-support-ticket')); ?></span>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($jsst_chosen && $jsst_id !== 'zywrap' && isset($jsst_usage['copilot'])) { ?>
                                <div class="jsst-metrics">
                                    <div class="jsst-metric">
                                        <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_usage['copilot']['runs'])); ?></span>
                                        <span class="jsst-metric-label"><?php echo esc_html(__('Recent runs', 'js-support-ticket')); ?></span>
                                    </div>
                                    <div class="jsst-metric">
                                        <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_usage['copilot']['intokens'] + (int) $jsst_usage['copilot']['outtokens'])); ?></span>
                                        <span class="jsst-metric-label"><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></span>
                                    </div>
                                    <div class="jsst-metric">
                                        <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_usage['copilot']['failed'])); ?></span>
                                        <span class="jsst-metric-label"><?php echo esc_html(__('Failed', 'js-support-ticket')); ?></span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="jsst-card-foot">
                            <a href="<?php echo esc_url($jsst_settingsurl); ?>"><?php echo esc_html($jsst_chosen ? __('Change or test it', 'js-support-ticket') : __('Use this engine', 'js-support-ticket')); ?></a>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('The knowledge it answers from', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <?php if ($jsst_ir) { ?>
                            <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Installed', 'js-support-ticket')); ?></span>
                        <?php } else { ?>
                            <span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Not installed', 'js-support-ticket')); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="jsst-card-body">
                    <?php if ($jsst_ir) { ?>
                        <p class="jsst-hint"><?php echo esc_html(__('Knowledge Sources decides what the AI may read, down to each article, and shows what a question would find.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('An answer is only as good as what it was allowed to read. Knowledge Sources says which of your knowledge base, FAQs, canned responses, posts, past tickets and crawled documentation are eligible — down to the individual article — and its console shows exactly which passages a question finds before any model is asked. Content Sources is the crawler behind the last of those.', 'js-support-ticket')); ?>
                        <div class="jsst-btnrow">
                            <a class="jsst-btn" href="<?php echo esc_url($jsst_sourcesurl); ?>"><?php echo esc_html(__('Knowledge Sources', 'js-support-ticket')); ?></a>
                            <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_feeds')); ?>"><?php echo esc_html(__('Content Sources', 'js-support-ticket')); ?></a>
                            <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_approvals')); ?>"><?php echo esc_html(__('Approvals', 'js-support-ticket')); ?></a>
                            <a class="jsst-btn" href="<?php echo esc_url($jsst_auditurl); ?>"><?php echo esc_html(__('Audit', 'js-support-ticket')); ?></a>
                        </div>
                    <?php } else { ?>
                        <div class="jsst-empty">
                            <p><?php echo esc_html(__('Answers grounded in your content need the AI Agent add-on.', 'js-support-ticket')); ?></p><?php JSSTlayout::why(__('The retrieval engine — the corpus, the crawler, grounded answers and automatic replies — ships in the AI Agent add-on. Without it the switches above still work and the Copilot still runs, but there is nothing indexed for an answer to be grounded in.', 'js-support-ticket')); ?>
                            <?php /* Governance is core's and is worth pointing at even here: it
                                     decides what the free suggestions on the ticket form may
                                     show, which is a live feature on this site right now. */ ?>
                            <div class="jsst-btnrow">
                                <a class="jsst-btn" href="<?php echo esc_url($jsst_sourcesurl); ?>"><?php echo esc_html(__('Knowledge Sources', 'js-support-ticket')); ?></a>
                                <a class="jsst-btn" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=license')); ?>"><?php echo esc_html(__('License & Add-ons', 'js-support-ticket')); ?></a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>
</div>
