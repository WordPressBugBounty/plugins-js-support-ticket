<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The authorisation matrix. (Roadmap 5.0-SEC-01)
 *
 * The roadmap asks for a matrix "covering every AJAX and REST action", and it
 * asks for it in the same release as the API rather than the one after,
 * because an API shipped in one release and reviewed in the next is an API
 * that is unreviewed for a release.
 *
 * Everything here is read out of this plugin's own source when the page is
 * generated. Nothing on it is a checklist somebody ticked, which matters more
 * on this screen than on any other: a hand-maintained security matrix goes out
 * of date silently, and reading it after that is worse than having none,
 * because it is reassuring and wrong.
 *
 * The order is the order somebody reviewing actually needs. What cannot be
 * seen comes before what was found, so that nobody reads a clean table as an
 * assurance it was never able to give; then the count; then the entry points
 * themselves, worst first.
 */
if (!class_exists('JSSTauthmatrix')) {
    echo esc_html(__('The authorisation matrix is not available.', 'js-support-ticket'));
    return;
}

$jsst_rows    = isset(jssupportticket::$jsst_data['scrows']) ? jssupportticket::$jsst_data['scrows'] : array();
$jsst_totals  = isset(jssupportticket::$jsst_data['sctotals']) ? jssupportticket::$jsst_data['sctotals'] : array();
$jsst_checks  = isset(jssupportticket::$jsst_data['scchecks']) ? jssupportticket::$jsst_data['scchecks'] : array();
$jsst_notes   = isset(jssupportticket::$jsst_data['scnotes']) ? jssupportticket::$jsst_data['scnotes'] : array();
$jsst_kinds   = isset(jssupportticket::$jsst_data['sckinds']) ? jssupportticket::$jsst_data['sckinds'] : array();
$jsst_when    = isset(jssupportticket::$jsst_data['scwhen']) ? jssupportticket::$jsst_data['scwhen'] : '';
$jsst_kind    = isset(jssupportticket::$jsst_data['sckind']) ? jssupportticket::$jsst_data['sckind'] : '';
$jsst_grade   = isset(jssupportticket::$jsst_data['scgrade']) ? jssupportticket::$jsst_data['scgrade'] : '';
$jsst_search  = isset(jssupportticket::$jsst_data['scsearch']) ? jssupportticket::$jsst_data['scsearch'] : '';
$jsst_showing = count($jsst_rows);

$jsst_refresh  = wp_nonce_url(admin_url('admin.php?page=jssupportticket&task=refreshmatrix&action=jstask'), 'jsst-authmatrix-refresh');
$jsst_download = wp_nonce_url(admin_url('admin-ajax.php?action=jsst_authmatrix'), 'jsst-authmatrix');

/* The pill for one verdict. Written once here rather than in each of the nine
   cells, because nine copies of a conditional is how two of them end up
   disagreeing about what a warning looks like. */
if (!function_exists('jsst_matrix_pill_class')) {
    function jsst_matrix_pill_class($jsst_verdict) {
        if ($jsst_verdict === JSSTauthmatrix::PASS) {
            return 'jsst-pill jsst-pill-ok';
        }
        if ($jsst_verdict === JSSTauthmatrix::WARN) {
            return 'jsst-pill jsst-pill-warn';
        }
        if ($jsst_verdict === JSSTauthmatrix::FAIL) {
            return 'jsst-pill jsst-pill-bad';
        }
        return 'jsst-pill jsst-pill-off';
    }
}
if (!function_exists('jsst_matrix_pill_text')) {
    function jsst_matrix_pill_text($jsst_verdict) {
        if ($jsst_verdict === JSSTauthmatrix::PASS) {
            return __('yes', 'js-support-ticket');
        }
        if ($jsst_verdict === JSSTauthmatrix::WARN) {
            return __('read it', 'js-support-ticket');
        }
        if ($jsst_verdict === JSSTauthmatrix::FAIL) {
            return __('no', 'js-support-ticket');
        }
        return __('n/a', 'js-support-ticket');
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
            'title'   => __('Ways In', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp">

            <p class="jsst-lede">
                <?php echo esc_html(__('Every way a request can reach this help desk — the REST API, each AJAX action, each task behind the public dispatcher, and the form handler — with what guards it. It is read out of the source each time it is generated, so it describes the code on this site rather than the code somebody documented once.', 'js-support-ticket')); ?>
            </p>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('What this report cannot see', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('First, not last. A green result that was never capable of going red is worse than no result at all, so this is what a pass here does not prove.', 'js-support-ticket')); ?></p>
                </div>
                <div class="jsst-card-body">
                    <ul class="jsst-hint">
                        <?php foreach ($jsst_notes AS $jsst_note) { ?>
                            <li><?php echo esc_html($jsst_note); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('What was found', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <a class="jsst-btn" href="<?php echo esc_url($jsst_download); ?>"><?php echo esc_html(__('Download it as Markdown', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <div class="jsst-card-body">
                    <div class="jsst-metrics">
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_totals['total']) ? $jsst_totals['total'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Ways in', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_totals['pass']) ? $jsst_totals['pass'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Clean', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_totals['warn']) ? $jsst_totals['warn'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('To read by hand', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_totals['fail']) ? $jsst_totals['fail'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Failing', 'js-support-ticket')); ?></span>
                        </div>
                        <div class="jsst-metric jsst-metric-quiet">
                            <span class="jsst-metric-value jsst-num"><?php echo esc_html(isset($jsst_totals['na']) ? $jsst_totals['na'] : 0); ?></span>
                            <span class="jsst-metric-label"><?php echo esc_html(__('Named but not installed', 'js-support-ticket')); ?></span>
                        </div>
                    </div>
                    <p class="jsst-hint"><?php echo esc_html(__('A row is graded by the worst thing in it, never by an average. An entry point with eight passes and one missing permission check is a hole, and a number that rounds it to 89% is a number that hides it.', 'js-support-ticket')); ?></p>
                    <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_refresh); ?>">
                        <div class="jsst-btnrow">
                            <button type="submit" class="jsst-btn"><?php echo esc_html(__('Read the source again', 'js-support-ticket')); ?></button>
                            <span class="jsst-formfoot-note"><?php
                                /* translators: %s is a date and time. */
                                echo esc_html(sprintf(__('Last read %s. It is read again by itself when the plugin version changes; this button is for when you have just edited a file.', 'js-support-ticket'), $jsst_when)); ?></span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('What each check asks', 'js-support-ticket')); ?></h2>
                </div>
                <div class="jsst-card-body">
                    <dl class="jsst-facts">
                        <?php foreach ($jsst_checks AS $jsst_key => $jsst_label) { ?>
                            <dt><?php echo esc_html($jsst_key); ?></dt>
                            <dd><?php echo esc_html($jsst_label); ?></dd>
                        <?php } ?>
                    </dl>
                </div>
            </div>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Every way in', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('Worst first, because that is the order somebody reviewing reads in. Open a row to see the sentence behind every check that did not pass.', 'js-support-ticket')); ?></p>
                </div>
                <div class="jsst-card-body">
                    <form class="jsst-form" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                        <input type="hidden" name="page" value="jssupportticket" />
                        <input type="hidden" name="jstlay" value="security" />
                        <div class="jsst-formgrid">
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="scsearch"><?php echo esc_html(__('Find', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval"><input type="search" id="scsearch" name="scsearch" value="<?php echo esc_attr($jsst_search); ?>" placeholder="<?php echo esc_attr(__('part of a name', 'js-support-ticket')); ?>" /></div>
                            </div>
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="sckind"><?php echo esc_html(__('Kind', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select name="sckind" id="sckind">
                                        <option value=""><?php echo esc_html(__('all of them', 'js-support-ticket')); ?></option>
                                        <?php foreach ($jsst_kinds AS $jsst_kindname => $jsst_count) { ?>
                                            <option value="<?php echo esc_attr($jsst_kindname); ?>" <?php selected($jsst_kind, $jsst_kindname); ?>><?php
                                                /* translators: 1: a kind of entry point, 2: how many there are. */
                                                echo esc_html(sprintf(__('%1$s (%2$d)', 'js-support-ticket'), $jsst_kindname, $jsst_count)); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="jsst-frow">
                                <label class="jsst-flabel" for="scgrade"><?php echo esc_html(__('Verdict', 'js-support-ticket')); ?></label>
                                <div class="jsst-fval">
                                    <select name="scgrade" id="scgrade">
                                        <option value=""><?php echo esc_html(__('any', 'js-support-ticket')); ?></option>
                                        <option value="fail" <?php selected($jsst_grade, 'fail'); ?>><?php echo esc_html(__('failing', 'js-support-ticket')); ?></option>
                                        <option value="warn" <?php selected($jsst_grade, 'warn'); ?>><?php echo esc_html(__('to read by hand', 'js-support-ticket')); ?></option>
                                        <option value="pass" <?php selected($jsst_grade, 'pass'); ?>><?php echo esc_html(__('clean', 'js-support-ticket')); ?></option>
                                        <option value="na" <?php selected($jsst_grade, 'na'); ?>><?php echo esc_html(__('not installed here', 'js-support-ticket')); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="jsst-frow jsst-frow-action">
                                <button type="submit" class="jsst-btn"><?php echo esc_html(__('Show these', 'js-support-ticket')); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="jsst-card-body jsst-card-flush">
                    <?php if (!$jsst_showing) { ?>
                        <div class="jsst-empty">
                            <p class="jsst-empty-title"><?php echo esc_html(__('Nothing matches that', 'js-support-ticket')); ?></p>
                            <p class="jsst-empty-text"><?php echo esc_html(__('No entry point on this site answers to those words with that verdict.', 'js-support-ticket')); ?></p>
                        </div>
                    <?php } else { ?>
                    <div class="jsst-table-wrap">
                        <?php /* Fluid: with the nine check columns gone the three that
                                 remain are all prose or a chip group, so nothing here wants
                                 a nowrap floor. It is what takes the English sweep clean at
                                 every width down to a 732px grid (a 1280 desk); without it
                                 the table was 4px over there. The check names in the chips
                                 are array keys, not msgids, so they are the same width in
                                 every language - only the verdicts, the counts and the
                                 handler text translate. */ ?>
                        <table class="jsst-table jsst-table-fluid">
                            <thead>
                                <tr>
                                    <th scope="col"><?php echo esc_html(__('Way in', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('Verdict', 'js-support-ticket')); ?></th>
                                    <th scope="col"><?php echo esc_html(__('What needs reading', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($jsst_rows AS $jsst_row) {
                                $jsst_findings = JSSTauthmatrix::findings($jsst_row); ?>
                                <tr class="<?php echo ($jsst_row['grade'] === JSSTauthmatrix::FAIL) ? 'jsst-row-on' : ''; ?>">
                                    <td>
                                        <span class="jsst-table-name"><code><?php echo esc_html($jsst_row['name']); ?></code></span>
                                        <span class="jsst-table-sub"><?php echo esc_html($jsst_row['kind'] . ' · ' . $jsst_row['handler']); ?></span>
                                    </td>
                                    <td>
                                        <span class="<?php echo esc_attr(jsst_matrix_pill_class($jsst_row['grade'])); ?>"><span class="jsst-dot"></span><?php
                                            echo esc_html(jsst_matrix_pill_text($jsst_row['grade'])); ?></span>
                                    </td>
                                    <?php /* Was nine columns, one per check, and it could not be made to
                                             fit: the table wanted 1342px in a 1052px wrap, and even with every
                                             nowrap relaxed it still wanted 1239px, because each pill column has
                                             a floor of ~81px. It was also mostly silence - of the 1116 cells,
                                             443 said "yes" and 400 said "n/a", so 76% of the grid existed to
                                             not be the thing you were looking for. This column carries the
                                             other 24%: the checks that actually want reading, named, with the
                                             sentence behind each one still on the pill and spelled out in the
                                             fold below. The passed and not-applicable counts stay so the nine
                                             still add up and nothing is silently dropped. */
                                        $jsst_pass = 0;
                                        $jsst_na   = 0;
                                        foreach ($jsst_row['results'] AS $jsst_result) {
                                            if ($jsst_result[0] === JSSTauthmatrix::PASS) { $jsst_pass++; }
                                            elseif ($jsst_result[0] === JSSTauthmatrix::NA) { $jsst_na++; }
                                        } ?>
                                    <td class="jsst-col-say">
                                        <?php if ($jsst_findings) { ?>
                                            <span class="jsst-chips">
                                                <?php foreach ($jsst_findings AS $jsst_check => $jsst_result) { ?>
                                                    <span class="<?php echo esc_attr(jsst_matrix_pill_class($jsst_result[0])); ?>" title="<?php echo esc_attr($jsst_result[1]); ?>"><span class="jsst-dot"></span><?php
                                                        echo esc_html($jsst_check); ?></span>
                                                <?php } ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="jsst-table-name"><?php echo esc_html(__('Nothing to read', 'js-support-ticket')); ?></span>
                                        <?php } ?>
                                        <?php /* The counts ride in the summary rather than on a line of
                                                 their own: the chips above already name what needs reading,
                                                 so a third stacked line put every row at 97px and the page
                                                 above where it started. One line, same two facts. */
                                        if ($jsst_findings || !empty($jsst_row['notes'])) { ?>
                                            <details class="jsst-details">
                                                <summary><?php
                                                    /* translators: %d is how many checks did not pass. */
                                                    echo esc_html(sprintf(_n('%d thing to read', '%d things to read', count($jsst_findings), 'js-support-ticket'), count($jsst_findings)));
                                                    ?> <span class="jsst-table-sub"><?php
                                                    /* translators: 1: how many checks passed, 2: how many do not apply here. */
                                                    echo esc_html(sprintf(__('%1$d passed · %2$d do not apply', 'js-support-ticket'), $jsst_pass, $jsst_na)); ?></span></summary>
                                                <?php foreach ($jsst_row['notes'] AS $jsst_note) { ?>
                                                    <p class="jsst-fhelp"><?php echo esc_html($jsst_note); ?></p>
                                                <?php } ?>
                                                <dl class="jsst-facts">
                                                    <?php foreach ($jsst_findings AS $jsst_check => $jsst_result) { ?>
                                                        <dt><?php echo esc_html($jsst_check); ?></dt>
                                                        <dd><?php echo esc_html($jsst_result[1]); ?></dd>
                                                    <?php } ?>
                                                </dl>
                                            </details>
                                        <?php } else { ?>
                                            <span class="jsst-table-sub"><?php
                                                /* translators: 1: how many checks passed, 2: how many do not apply here. */
                                                echo esc_html(sprintf(__('%1$d passed · %2$d do not apply', 'js-support-ticket'), $jsst_pass, $jsst_na)); ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>
                </div>
                <div class="jsst-card-foot">
                    <p class="jsst-fhelp"><?php
                        /* translators: 1: how many rows are shown, 2: how many there are altogether. */
                        echo esc_html(sprintf(__('Showing %1$d of %2$d. Every verdict here is evidence and not a proof: this reads whether a guard is called, not whether it is called before the write.', 'js-support-ticket'),
                            $jsst_showing, isset($jsst_totals['total']) ? $jsst_totals['total'] : 0)); ?></p>
                </div>
            </div>

        </div>
    </div>
</div>
