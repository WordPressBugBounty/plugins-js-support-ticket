<?php
if(!defined('ABSPATH')) die('Restricted Access');

JSSTmessage::getMessage(); 
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Zywrap API Logs','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Zywrap API Audit Logs', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            
            <form class="js-filter-form" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=zywrap&jstlay=zywrap_logs"),"zywrap_logs")); ?>">
                <?php echo wp_kses(JSSTformfield::text('trace_id', isset(jssupportticket::$jsst_data['filter']['trace_id']) ? jssupportticket::$jsst_data['filter']['trace_id'] : '', array('placeholder' => esc_html(__('Search Trace ID', 'js-support-ticket')),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'document.getElementById("trace_id").value=""; document.getElementById("jssupportticketform").submit();')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::select('pagesize', array((object) array('id'=>20,'text'=>20), (object) array('id'=>50,'text'=>50), (object) array('id'=>100,'text'=>100)), isset(jssupportticket::$jsst_data['filter']['pagesize']) ? jssupportticket::$jsst_data['filter']['pagesize'] : 20 ,esc_html(__("Records per page",'js-support-ticket')), array('class' => 'js-form-input-field js-right','onchange'=>'document.jssupportticketform.submit();')), JSST_ALLOWED_TAGS); ?>
            </form>

            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <form class="jsstadmin-form" method="post" action="#">
                    <table id="js-support-ticket-table">
                        <thead>
                            <tr class="js-support-ticket-table-heading">
                                <th class="left"><?php echo esc_html(__('Trace ID', 'js-support-ticket')); ?></th>
                                <th class="left"><?php echo esc_html(__('Action / Wrapper', 'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__('Model', 'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__('Latency', 'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__('Date', 'js-support-ticket')); ?></th>
                                <th><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach (jssupportticket::$jsst_data[0] AS $log) { ?>
                            <tr>
                                <td class="left jsst-left-row" style="font-family:monospace; color:#475569; font-size:12px;">
                                    <?php echo esc_html(substr($log->trace_id, 0, 18)) . '...'; ?>
                                </td>
                                <td class="left">
                                    <strong><?php echo esc_html(str_replace('_', ' ', ucwords($log->wrapper_code))); ?></strong>
                                    <div style="font-size:11px; color:#94a3b8;"><?php echo esc_html($log->wrapper_code); ?></div>
                                </td>
                                <td>
                                    <span style="background:#f1f5f9; border:1px solid #e2e8f0; padding:3px 8px; border-radius:12px; font-size:11px; color:#475569;">
                                        <?php echo esc_html($log->model_code == 'default' ? 'Auto-Select' : $log->model_code); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:600;">
                                        <?php echo number_format($log->total_tokens); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($log->latency_ms); ?> ms</td>
                                <td><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'] . ' H:i:s', jssupportticketphplib::JSST_strtotime($log->created_at))); ?></td>
                                <td>
                                    <img alt="Success" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/good.png'; ?>" title="Success" />
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </form>
                <?php
                if (isset(jssupportticket::$jsst_data[1])) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
                }
            } else {
                JSSTlayout::getNoRecordFound();
            }
            ?>
        </div>
    </div>
</div>