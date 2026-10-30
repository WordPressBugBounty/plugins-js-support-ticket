<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AI Agent — Usage and cost. (Roadmap 6.0-AI-07)
 *
 * The one screen in this group about money. Everything else here governs what
 * the AI may say; this governs what it may spend, and it is the only part of
 * the product with a cost that grows while nobody is looking.
 *
 * Two decisions shape the page:
 *
 *   **Three counters, not one.** A request paid for by the licence, a request
 *   on the customer's own key and a request on their own hardware are three
 *   different facts, and adding them up would be a lie in whichever unit it
 *   chose. So the allowance is counted in requests, the key in dollars, and
 *   local hardware is simply not counted.
 *
 *   **An unpriced model is shown, not hidden.** A model nobody has priced is
 *   counted as a call with no money against it, and this page asks for the
 *   price rather than quietly reporting a smaller number than the invoice.
 */
if (!class_exists('JSSTaiusage')) {
    echo esc_html(__('The AI meter is not available on this site.', 'js-support-ticket'));
    return;
}

$jsst_budget    = isset(jssupportticket::$jsst_data['aibudget']) ? jssupportticket::$jsst_data['aibudget'] : array();
$jsst_day       = isset(jssupportticket::$jsst_data['aiday']) ? jssupportticket::$jsst_data['aiday'] : array();
$jsst_month     = isset(jssupportticket::$jsst_data['aimonth']) ? jssupportticket::$jsst_data['aimonth'] : array();
$jsst_alerts    = isset(jssupportticket::$jsst_data['aialerts']) ? jssupportticket::$jsst_data['aialerts'] : array();
$jsst_byfeature = isset(jssupportticket::$jsst_data['aibyfeature']) ? jssupportticket::$jsst_data['aibyfeature'] : array();
$jsst_bymodel   = isset(jssupportticket::$jsst_data['aibymodel']) ? jssupportticket::$jsst_data['aibymodel'] : array();
$jsst_byfunded  = isset(jssupportticket::$jsst_data['aibyfunded']) ? jssupportticket::$jsst_data['aibyfunded'] : array();
$jsst_tickets   = isset(jssupportticket::$jsst_data['aitoptickets']) ? jssupportticket::$jsst_data['aitoptickets'] : array();
$jsst_allowance = isset(jssupportticket::$jsst_data['aiallowance']) ? (int) jssupportticket::$jsst_data['aiallowance'] : 0;
$jsst_allowused = isset(jssupportticket::$jsst_data['aiallowused']) ? (int) jssupportticket::$jsst_data['aiallowused'] : 0;
$jsst_engines   = isset(jssupportticket::$jsst_data['aienginelist']) ? jssupportticket::$jsst_data['aienginelist'] : array();
$jsst_fallback  = isset(jssupportticket::$jsst_data['aifallback']) ? jssupportticket::$jsst_data['aifallback'] : '';
$jsst_unpriced  = isset(jssupportticket::$jsst_data['aiunpriced']) ? jssupportticket::$jsst_data['aiunpriced'] : array();

$jsst_saveurl = wp_nonce_url(admin_url('admin.php?page=aiagent&task=saveaibudget&action=jstask'), 'jsst-aiagent-budget');
$jsst_current = class_exists('JSSTaiengine') ? JSSTaiengine::currentId() : '';

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Usage & Cost', 'js-support-ticket'),
            'crumbs'  => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('aiagent_usage'); } ?>

            <p class="jsst-lede">
                <?php echo esc_html(__('Every AI call, what it cost, and the limits that cap spending.', 'js-support-ticket')); ?>
            </p>

            <?php if (!empty($jsst_alerts)) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-body">
                        <?php foreach ($jsst_alerts as $jsst_alert) { ?>
                            <p class="jsst-hint">
                                <span class="jsst-pill <?php echo ($jsst_alert['level'] === 'stop') ? 'jsst-pill-bad' : 'jsst-pill-warn'; ?>">
                                    <span class="jsst-dot"></span>
                                    <?php echo ($jsst_alert['level'] === 'stop')
                                        ? esc_html(__('Stopped', 'js-support-ticket'))
                                        : esc_html(__('Nearly there', 'js-support-ticket')); ?>
                                </span>
                                <?php echo esc_html($jsst_alert['text']); ?>
                            </p>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <div class="jsst-cards">
                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('This month', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <div class="jsst-metrics">
                            <div class="jsst-metric">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(JSSTaiusage::money(isset($jsst_month['cost']) ? $jsst_month['cost'] : 0)); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('On your own key', 'js-support-ticket')); ?></span>
                            </div>
                            <div class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_month['calls']) ? (int) $jsst_month['calls'] : 0)); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Model calls', 'js-support-ticket')); ?></span>
                            </div>
                            <div class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_month['tokens']) ? (int) $jsst_month['tokens'] : 0)); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></span>
                            </div>
                        </div>
                        <?php if ($jsst_allowance > 0) { ?>
                            <p class="jsst-hint"><?php echo esc_html(sprintf(
                                /* translators: 1: requests used, 2: the plan's monthly allowance */
                                __('%1$s of %2$s AI requests included with your licence used this month. Those cost you nothing and are not in the figure above.', 'js-support-ticket'),
                                number_format_i18n($jsst_allowused), number_format_i18n($jsst_allowance)
                            )); ?></p>
                        <?php } ?>
                        <?php if (!empty($jsst_month['unpriced'])) { ?>
                            <p class="jsst-hint"><strong><?php echo esc_html(sprintf(
                                /* translators: %s: how many calls were made on a model with no price */
                                _n('%s call this month was made on a model with no price set, so it is not in the total.',
                                   '%s calls this month were made on models with no price set, so they are not in the total.',
                                   (int) $jsst_month['unpriced'], 'js-support-ticket'),
                                number_format_i18n((int) $jsst_month['unpriced'])
                            )); ?></strong></p>
                        <?php } ?>
                    </div>
                </div>

                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Today', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <div class="jsst-metrics">
                            <div class="jsst-metric">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(JSSTaiusage::money(isset($jsst_day['cost']) ? $jsst_day['cost'] : 0)); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Spent today', 'js-support-ticket')); ?></span>
                            </div>
                            <div class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_day['calls']) ? (int) $jsst_day['calls'] : 0)); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Model calls', 'js-support-ticket')); ?></span>
                            </div>
                            <div class="jsst-metric jsst-metric-quiet">
                                <span class="jsst-metric-value jsst-num"><?php echo esc_html(number_format_i18n(isset($jsst_day['failed']) ? (int) $jsst_day['failed'] : 0)); ?></span>
                                <span class="jsst-metric-label"><?php echo esc_html(__('Failed', 'js-support-ticket')); ?></span>
                            </div>
                        </div>
                        <p class="jsst-hint"><?php echo esc_html(__('Failed calls are counted because they can still be charged.', 'js-support-ticket')); ?></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($jsst_unpriced)) { ?>
                <div class="jsst-card">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('Models with no price', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body">
                        <p class="jsst-hint"><?php echo esc_html(__('These models have no price set, so their cost is not counted. Set a price to include them.', 'js-support-ticket')); ?></p>
                        <?php foreach ($jsst_unpriced as $jsst_row) { ?>
                            <form class="jsst-form" method="post" action="<?php echo esc_url($jsst_saveurl); ?>">
                                <input type="hidden" name="aiprice" value="1" />
                                <input type="hidden" name="model" value="<?php echo esc_attr($jsst_row['key']); ?>" />
                                <div class="jsst-formgrid">
                                    <div class="jsst-frow jsst-frow-full">
                                        <label class="jsst-flabel"><?php echo esc_html($jsst_row['key']); ?></label>
                                        <div class="jsst-fval">
                                            <span class="jsst-table-sub"><?php echo esc_html(sprintf(
                                                /* translators: 1: number of calls, 2: number of tokens */
                                                __('%1$s calls, %2$s tokens this month', 'js-support-ticket'),
                                                number_format_i18n($jsst_row['calls']), number_format_i18n($jsst_row['tokens'])
                                            )); ?></span>
                                        </div>
                                    </div>
                                    <div class="jsst-frow">
                                        <label class="jsst-flabel" for="jsst-in-<?php echo esc_attr(sanitize_key($jsst_row['key'])); ?>"><?php echo esc_html(__('$ per million in', 'js-support-ticket')); ?></label>
                                        <div class="jsst-fval"><input type="number" step="0.01" min="0" id="jsst-in-<?php echo esc_attr(sanitize_key($jsst_row['key'])); ?>" name="inprice" value="0" /></div>
                                    </div>
                                    <div class="jsst-frow">
                                        <label class="jsst-flabel" for="jsst-out-<?php echo esc_attr(sanitize_key($jsst_row['key'])); ?>"><?php echo esc_html(__('$ per million out', 'js-support-ticket')); ?></label>
                                        <div class="jsst-fval"><input type="number" step="0.01" min="0" id="jsst-out-<?php echo esc_attr(sanitize_key($jsst_row['key'])); ?>" name="outprice" value="0" /></div>
                                    </div>
                                </div>
                                <div class="jsst-btnrow">
                                    <input type="submit" class="jsst-btn" value="<?php echo esc_attr(__('Save price', 'js-support-ticket')); ?>" />
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <h2 class="jsst-groupheading"><?php echo esc_html(__('Where it went this month', 'js-support-ticket')); ?></h2>

            <div class="jsst-cards">
                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('By feature', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body jsst-card-flush">
                        <?php if (empty($jsst_byfeature)) { ?>
                            <div class="jsst-empty">
                                <p class="jsst-empty-title"><?php echo esc_html(__('Nothing yet', 'js-support-ticket')); ?></p>
                                <p class="jsst-empty-text"><?php echo esc_html(__('No model has been asked anything this month.', 'js-support-ticket')); ?></p>
                            </div>
                        <?php } else { ?>
                            <div class="jsst-table-wrap">
                                <table class="jsst-table">
                                    <thead><tr>
                                        <th><?php echo esc_html(__('Feature', 'js-support-ticket')); ?></th>
                                        <th class="jsst-num"><?php echo esc_html(__('Calls', 'js-support-ticket')); ?></th>
                                        <th class="jsst-num"><?php echo esc_html(__('Cost', 'js-support-ticket')); ?></th>
                                    </tr></thead>
                                    <tbody>
                                    <?php foreach ($jsst_byfeature as $jsst_row) { ?>
                                        <tr>
                                            <th scope="row"><span class="jsst-table-name"><?php echo esc_html(JSSTaiusage::featureLabel($jsst_row['key'])); ?></span></th>
                                            <td class="jsst-num"><?php echo esc_html(number_format_i18n($jsst_row['calls'])); ?></td>
                                            <td class="jsst-num"><?php echo esc_html(JSSTaiusage::money($jsst_row['cost'])); ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="jsst-card jsst-card-half">
                    <div class="jsst-card-head">
                        <h2 class="jsst-card-title"><?php echo esc_html(__('By model', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="jsst-card-body jsst-card-flush">
                        <?php if (empty($jsst_bymodel)) { ?>
                            <div class="jsst-empty">
                                <p class="jsst-empty-title"><?php echo esc_html(__('Nothing yet', 'js-support-ticket')); ?></p>
                                <p class="jsst-empty-text"><?php echo esc_html(__('No model has been asked anything this month.', 'js-support-ticket')); ?></p>
                            </div>
                        <?php } else { ?>
                            <div class="jsst-table-wrap">
                                <table class="jsst-table">
                                    <thead><tr>
                                        <th><?php echo esc_html(__('Model', 'js-support-ticket')); ?></th>
                                        <th class="jsst-num"><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></th>
                                        <th class="jsst-num"><?php echo esc_html(__('Cost', 'js-support-ticket')); ?></th>
                                    </tr></thead>
                                    <tbody>
                                    <?php foreach ($jsst_bymodel as $jsst_row) { ?>
                                        <tr>
                                            <th scope="row">
                                                <span class="jsst-table-name"><?php echo esc_html($jsst_row['key'] !== '' ? $jsst_row['key'] : __('Not reported', 'js-support-ticket')); ?></span>
                                                <?php if ($jsst_row['unpriced'] > 0) { ?>
                                                    <span class="jsst-table-sub"><?php echo esc_html(__('no price set', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                            </th>
                                            <td class="jsst-num"><?php
                                                /* A call whose engine reported no token count is not "0 tokens". */
                                                echo ((int) $jsst_row['tokens'] > 0)
                                                    ? esc_html(number_format_i18n($jsst_row['tokens']))
                                                    : '<span class="jsst-table-sub">' . esc_html(__('not reported', 'js-support-ticket')) . '</span>'; ?></td>
                                            <td class="jsst-num"><?php echo esc_html(JSSTaiusage::money($jsst_row['cost'])); ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($jsst_byfunded) || !empty($jsst_tickets)) { ?>
                <div class="jsst-cards">
                    <div class="jsst-card jsst-card-half">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html(__('Who pays for it', 'js-support-ticket')); ?></h2>
                        </div>
                        <div class="jsst-card-body jsst-card-flush">
                            <div class="jsst-table-wrap">
                                <table class="jsst-table">
                                    <thead><tr>
                                        <th><?php echo esc_html(__('Paid by', 'js-support-ticket')); ?></th>
                                        <th class="jsst-num"><?php echo esc_html(__('Calls', 'js-support-ticket')); ?></th>
                                        <th class="jsst-num"><?php echo esc_html(__('Cost', 'js-support-ticket')); ?></th>
                                    </tr></thead>
                                    <tbody>
                                    <?php foreach ($jsst_byfunded as $jsst_row) { ?>
                                        <tr>
                                            <th scope="row"><span class="jsst-table-name"><?php echo esc_html(JSSTaiusage::fundedLabel($jsst_row['key'])); ?></span></th>
                                            <td class="jsst-num"><?php echo esc_html(number_format_i18n($jsst_row['calls'])); ?></td>
                                            <td class="jsst-num"><?php echo esc_html(JSSTaiusage::money($jsst_row['cost'])); ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="jsst-card jsst-card-half">
                        <div class="jsst-card-head">
                            <h2 class="jsst-card-title"><?php echo esc_html(__('Tickets that cost the most', 'js-support-ticket')); ?></h2>
                        </div>
                        <div class="jsst-card-body jsst-card-flush">
                            <?php if (empty($jsst_tickets)) { ?>
                                <div class="jsst-empty">
                                    <p class="jsst-empty-title"><?php echo esc_html(__('Nothing yet', 'js-support-ticket')); ?></p>
                                    <p class="jsst-empty-text"><?php echo esc_html(__('No ticket has been worked on by a model this month.', 'js-support-ticket')); ?></p>
                                </div>
                            <?php } else { ?>
                                <div class="jsst-table-wrap">
                                    <table class="jsst-table">
                                        <thead><tr>
                                            <th><?php echo esc_html(__('Ticket', 'js-support-ticket')); ?></th>
                                            <th class="jsst-num"><?php echo esc_html(__('Calls', 'js-support-ticket')); ?></th>
                                            <th class="jsst-num"><?php echo esc_html(__('Cost', 'js-support-ticket')); ?></th>
                                        </tr></thead>
                                        <tbody>
                                        <?php foreach ($jsst_tickets as $jsst_row) { ?>
                                            <tr>
                                                <th scope="row">
                                                    <a class="jsst-table-name" href="<?php echo esc_url(admin_url('admin.php?page=tickets&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_row->ticketid)); ?>">#<?php echo esc_html((int) $jsst_row->ticketid); ?></a>
                                                </th>
                                                <td class="jsst-num"><?php echo esc_html(number_format_i18n((int) $jsst_row->jsst_calls)); ?></td>
                                                <td class="jsst-num"><?php echo esc_html(JSSTaiusage::money($jsst_row->jsst_cost)); ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php /* The limits and the backup engine are settings, so they are
                     changed under Settings > Spending; here they are only shown,
                     beside the spending they cap. */
            $jsst_caps = array();
            if (!empty($jsst_budget['daymoney']))    $jsst_caps[] = sprintf(__('$%s a day', 'js-support-ticket'), number_format_i18n((float) $jsst_budget['daymoney'], 2));
            if (!empty($jsst_budget['monthmoney']))  $jsst_caps[] = sprintf(__('$%s a month', 'js-support-ticket'), number_format_i18n((float) $jsst_budget['monthmoney'], 2));
            if (!empty($jsst_budget['daycalls']))    $jsst_caps[] = sprintf(__('%s requests a day', 'js-support-ticket'), number_format_i18n((int) $jsst_budget['daycalls']));
            if (!empty($jsst_budget['ticketcalls'])) $jsst_caps[] = sprintf(__('%s requests a ticket', 'js-support-ticket'), number_format_i18n((int) $jsst_budget['ticketcalls']));
            $jsst_spare = ($jsst_fallback !== '' && isset($jsst_engines[$jsst_fallback]['label'])) ? $jsst_engines[$jsst_fallback]['label'] : ''; ?>
            <h2 class="jsst-groupheading"><?php echo esc_html(__('Limits', 'js-support-ticket')); ?></h2>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Limits and backup engine', 'js-support-ticket')); ?></h2>
                    <div class="jsst-card-tools">
                        <a class="jsst-btn jsst-btn-sm" href="<?php echo esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_settings#AISpend')); ?>"><?php echo esc_html(__('Change in Settings', 'js-support-ticket')); ?></a>
                    </div>
                </div>
                <div class="jsst-card-body">
                    <dl class="jsst-facts">
                        <dt><?php echo esc_html(__('Limits', 'js-support-ticket')); ?></dt>
                        <dd><?php echo empty($jsst_caps)
                            ? '<span class="jsst-pill jsst-pill-warn"><span class="jsst-dot"></span>' . esc_html(__('No limit set', 'js-support-ticket')) . '</span>'
                            : esc_html(implode(' · ', $jsst_caps)); ?></dd>
                        <dt><?php echo esc_html(__('E-mail warnings', 'js-support-ticket')); ?></dt>
                        <dd><?php echo esc_html(!empty($jsst_budget['alerts']) ? __('On', 'js-support-ticket') : __('Off', 'js-support-ticket')); ?></dd>
                        <dt><?php echo esc_html(__('Backup engine', 'js-support-ticket')); ?></dt>
                        <dd><?php echo esc_html($jsst_spare !== '' ? $jsst_spare : __('None', 'js-support-ticket')); ?></dd>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</div>
