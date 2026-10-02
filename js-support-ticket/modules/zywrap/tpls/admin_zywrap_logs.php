<?php
if(!defined('ABSPATH')) die('Restricted Access');

/**
 * Zywrap requests: every call made to the Zywrap API.
 *
 * On the shared admin vocabulary (filter bar, card, jsst-table, pills) like
 * the Usage & Cost and Audit tabs beside it, rather than the add-on's own
 * table styles. The field names the search reads (trace_id, pagesize,
 * JSST_form_search) and the nonce are unchanged.
 *
 * The status column used to be a green tick on every row whatever the
 * request's own status said; it reads the row now.
 */
$jsst_logs  = !empty(jssupportticket::$jsst_data[0]) ? jssupportticket::$jsst_data[0] : array();
$jsst_total = isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0;
$jsst_trace = isset(jssupportticket::$jsst_data['filter']['trace_id']) ? jssupportticket::$jsst_data['filter']['trace_id'] : '';
$jsst_size  = isset(jssupportticket::$jsst_data['filter']['pagesize']) ? jssupportticket::$jsst_data['filter']['pagesize'] : 20;
$jsst_sizes = array((object) array('id' => 20, 'text' => 20), (object) array('id' => 50, 'text' => 50), (object) array('id' => 100, 'text' => 100));
$jsst_format = jssupportticket::$_config['date_format'] . ' H:i:s';

JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper" class="js-ticket-zywrap-logs-page">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'  => __('Zywrap requests', 'js-support-ticket'),
            'count'  => $jsst_total,
            'crumbs' => array(array('text' => __('AI Agent', 'js-support-ticket'), 'url' => admin_url('admin.php?page=aiagent'))),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (class_exists('JSSTainav')) { JSSTainav::render('zywrap_logs'); } ?>

            <form class="jsst-filterbar" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=zywrap&jstlay=zywrap_logs"),"zywrap_logs")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('trace_id', $jsst_trace, array('placeholder' => __('Search by trace ID', 'js-support-ticket'), 'class' => 'jsst-search-input')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', __('Search', 'js-support-ticket'), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', __('Reset', 'js-support-ticket'), array('class' => 'jsst-btn', 'onclick' => 'document.getElementById("trace_id").value=""; document.getElementById("jssupportticketform").submit();')), JSST_ALLOWED_TAGS); ?>
                <label class="screen-reader-text" for="pagesize"><?php echo esc_html(__('Records per page', 'js-support-ticket')); ?></label>
                <?php echo wp_kses(JSSTformfield::select('pagesize', $jsst_sizes, $jsst_size, __('Records per page', 'js-support-ticket'), array('class' => 'jsst-select', 'onchange' => 'document.jssupportticketform.submit();')), JSST_ALLOWED_TAGS); ?>
            </form>

            <div class="jsst-card">
                <div class="jsst-card-head">
                    <h2 class="jsst-card-title"><?php echo esc_html(__('Every call made to Zywrap', 'js-support-ticket')); ?></h2>
                    <p class="jsst-card-sub"><?php echo esc_html(__('Newest first. Failed calls are also listed on their own under Zywrap errors.', 'js-support-ticket')); ?></p>
                </div>
                <div class="jsst-card-body jsst-card-flush">
                    <?php if (empty($jsst_logs)) {
                        JSSTlayout::adminEmpty(
                            $jsst_trace !== '' ? __('No request matches that trace ID', 'js-support-ticket') : __('No requests yet', 'js-support-ticket'),
                            $jsst_trace !== '' ? __('Check the ID, or reset the search to see every request.', 'js-support-ticket') : __('Calls appear here as soon as anything on the desk asks Zywrap for an answer.', 'js-support-ticket'));
                    } else { ?>
                        <div class="jsst-table-wrap">
                            <table class="jsst-table">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></th>
                                        <th scope="col" class="jsst-col-fit"><?php echo esc_html(__('Model', 'js-support-ticket')); ?></th>
                                        <th scope="col" class="jsst-num"><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></th>
                                        <th scope="col" class="jsst-num"><?php echo esc_html(__('Took', 'js-support-ticket')); ?></th>
                                        <th scope="col" class="jsst-col-fit"><?php echo esc_html(__('When', 'js-support-ticket')); ?></th>
                                        <th scope="col" class="jsst-col-fit"><?php echo esc_html(__('Result', 'js-support-ticket')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($jsst_logs AS $log) {
                                    $jsst_failed = (isset($log->status) && $log->status === 'error'); ?>
                                    <tr>
                                        <td>
                                            <span class="jsst-table-name"><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $log->wrapper_code))); ?></span>
                                            <span class="jsst-table-sub" title="<?php echo esc_attr($log->trace_id); ?>"><?php
                                                /* translators: %s: a request trace id */
                                                echo esc_html(sprintf(__('Trace %s', 'js-support-ticket'), $log->trace_id)); ?></span>
                                        </td>
                                        <td class="jsst-col-fit"><span class="jsst-chip"><?php echo esc_html($log->model_code == 'default' ? __('Auto-select', 'js-support-ticket') : $log->model_code); ?></span></td>
                                        <td class="jsst-num"><?php echo esc_html((int) $log->total_tokens > 0 ? number_format_i18n((int) $log->total_tokens) : '—'); ?></td>
                                        <td class="jsst-num"><?php echo esc_html((int) $log->latency_ms > 0 ? number_format_i18n((int) $log->latency_ms) . ' ms' : '—'); ?></td>
                                        <td class="jsst-col-fit"><span class="jsst-table-sub"><?php echo esc_html(date_i18n($jsst_format, jssupportticketphplib::JSST_strtotime($log->created_at))); ?></span></td>
                                        <td class="jsst-col-fit">
                                            <?php if ($jsst_failed) { ?>
                                                <span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span><?php echo esc_html(__('Failed', 'js-support-ticket')); ?></span>
                                            <?php } else { ?>
                                                <span class="jsst-pill jsst-pill-ok"><span class="jsst-dot"></span><?php echo esc_html(__('Answered', 'js-support-ticket')); ?></span>
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
            <?php
            if (!empty($jsst_logs) && isset(jssupportticket::$jsst_data[1])) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
            }
            ?>
        </div>
    </div>
</div>
