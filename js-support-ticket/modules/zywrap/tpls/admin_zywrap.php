<?php
if (!defined('ABSPATH')) die('Restricted Access');

$dashboard_data = isset(jssupportticket::$jsst_data['dashboard_stats']) ? jssupportticket::$jsst_data['dashboard_stats'] : array();

$api_key      = isset($dashboard_data['api_key']) ? $dashboard_data['api_key'] : '';
$last_sync    = isset($dashboard_data['last_sync']) ? $dashboard_data['last_sync'] : '';
$total_reqs   = isset($dashboard_data['total_reqs']) ? $dashboard_data['total_reqs'] : 0;
$total_toks   = isset($dashboard_data['total_toks']) ? $dashboard_data['total_toks'] : 0;
$total_errs   = isset($dashboard_data['total_errs']) ? $dashboard_data['total_errs'] : 0;
$recent_logs  = isset($dashboard_data['recent_logs']) ? $dashboard_data['recent_logs'] : array();

$is_connected = !empty($api_key);

JSSTmessage::getMessage();
?>

<div id="jsstadmin-wrapper" class="js-ticket-zywrap-dashboard">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Zywrap Dashboard','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Zywrap AI Dashboard', 'js-support-ticket')); ?></h1>
        </div>
        
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n js-ticket-zywrap-main-content">
            
            <div class="js-ticket-zywrap-hero">
                <div class="js-ticket-zywrap-hero-bg"></div>
                <div class="js-ticket-zywrap-hero-content">
                    <div class="js-ticket-zywrap-hero-text">
                        <div class="js-ticket-zywrap-badge">
                            <span class="dashicons dashicons-superhero-alt" aria-hidden="true"></span> <?php echo esc_html(__('Zero Prompt Engineering', 'js-support-ticket')); ?>
                        </div>
                        
                        <h2 class="js-ticket-zywrap-hero-h2">
                            <?php echo esc_html(__('The Ultimate AI Copilot', 'js-support-ticket')); ?> <br>
                            <span class="js-ticket-zywrap-text-blue"><?php echo esc_html(__('for JS Help Desk.', 'js-support-ticket')); ?></span>
                        </h2>
                        
                        <p class="js-ticket-zywrap-hero-p">
                            <?php echo esc_html(__('Equip your agents with top-tier AI models natively inside the ticket editor. Automate translations, summarize threads, and draft perfect replies in seconds.', 'js-support-ticket')); ?>
                        </p>
                        
                        <?php if (!$is_connected) : ?>
                            <div class="js-ticket-zywrap-flex-gap">
                                <a href="https://zywrap.com/register" target="_blank" class="js-ticket-zywrap-btn-primary">
                                    <?php echo esc_html(__('Claim 10,000 Free Credits →', 'js-support-ticket')); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=zywrap&jstlay=zywrap_settings')); ?>" class="js-ticket-zywrap-btn-outline">
                                    <?php echo esc_html(__('Connect API Key', 'js-support-ticket')); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="js-ticket-zywrap-status-wrapper">
                        <?php if ($is_connected) : ?>
                            <div class="js-ticket-zywrap-status-card js-ticket-zywrap-status-active">
                                <div class="js-ticket-zywrap-status-icon">
                                    <span class="dashicons dashicons-yes" aria-hidden="true"></span>
                                </div>
                                <div class="js-ticket-zywrap-status-label"><?php echo esc_html(__('System Active', 'js-support-ticket')); ?></div>
                                <div class="js-ticket-zywrap-status-subtext"><?php echo esc_html(__('API Connected', 'js-support-ticket')); ?></div>
                            </div>
                        <?php else : ?>
                            <div class="js-ticket-zywrap-status-card js-ticket-zywrap-status-inactive">
                                <div class="js-ticket-zywrap-status-icon">
                                    <span class="dashicons dashicons-lock" aria-hidden="true"></span>
                                </div>
                                <div class="js-ticket-zywrap-status-label"><?php echo esc_html(__('Disconnected', 'js-support-ticket')); ?></div>
                                <div class="js-ticket-zywrap-status-subtext"><?php echo esc_html(__('Setup Required', 'js-support-ticket')); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="js-ticket-zywrap-stats-grid">
                <div class="js-ticket-zywrap-card js-ticket-zywrap-stat-box">
                    <div>
                        <div class="js-ticket-zywrap-stat-label"><?php echo esc_html(__('Total API Requests', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-zywrap-stat-number"><?php echo esc_html(number_format($total_reqs)); ?></div>
                    </div>
                    <div class="js-ticket-zywrap-stat-icon-wrap js-ticket-zywrap-bg-blue">
                        <span class="dashicons dashicons-update-alt" aria-hidden="true"></span>
                    </div>
                </div>
                
                <div class="js-ticket-zywrap-card js-ticket-zywrap-stat-box">
                    <div>
                        <div class="js-ticket-zywrap-stat-label"><?php echo esc_html(__('Tokens Processed', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-zywrap-stat-number"><?php echo esc_html(number_format($total_toks)); ?></div>
                    </div>
                    <div class="js-ticket-zywrap-stat-icon-wrap js-ticket-zywrap-bg-green">
                        <span class="dashicons dashicons-database" aria-hidden="true"></span>
                    </div>
                </div>
                
                <div class="js-ticket-zywrap-card js-ticket-zywrap-stat-box">
                    <div>
                        <div class="js-ticket-zywrap-stat-label"><?php echo esc_html(__('API Errors', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-zywrap-stat-number <?php echo $total_errs > 0 ? 'js-ticket-zywrap-text-red' : ''; ?>">
                            <?php echo esc_html(number_format($total_errs)); ?>
                        </div>
                    </div>
                    <div class="js-ticket-zywrap-stat-icon-wrap js-ticket-zywrap-bg-red">
                        <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                    </div>
                </div>
            </div>

            <div class="js-ticket-zywrap-content-grid">
                
                <div class="js-ticket-zywrap-card js-ticket-zywrap-overflow-hidden">
                    <div class="js-ticket-zywrap-card-header">
                        <h2 class="js-ticket-zywrap-card-h2"><?php echo esc_html(__('Recent AI Activity', 'js-support-ticket')); ?></h2>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=zywrap&jstlay=zywrap_logs')); ?>" class="js-ticket-zywrap-btn-small">
                            <?php echo esc_html(__('View All Logs', 'js-support-ticket')); ?>
                        </a>
                    </div>
                    
                    <div class="js-ticket-zywrap-table-responsive">
                        <table class="js-ticket-zywrap-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html(__('Wrapper Called', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('AI Model', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Tokens', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Latency', 'js-support-ticket')); ?></th>
                                    <th><?php echo esc_html(__('Status', 'js-support-ticket')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_logs)) : ?>
                                    <tr>
                                        <td colspan="5" class="js-ticket-zywrap-empty-td">
                                            <span class="dashicons dashicons-format-aside" aria-hidden="true"></span><br>
                                            <?php echo esc_html(__('No activity logged yet. Start replying to tickets!', 'js-support-ticket')); ?>
                                        </td>
                                    </tr>
                                <?php else : foreach ($recent_logs as $log) : ?>
                                    <tr>
                                        <td>
                                            <div class="js-ticket-zywrap-log-title"><?php echo esc_html(str_replace('_', ' ', ucwords($log->wrapper_code))); ?></div>
                                            <div class="js-ticket-zywrap-log-code"><?php echo esc_html($log->wrapper_code); ?></div>
                                        </td>
                                        <td>
                                            <span class="js-ticket-zywrap-model-tag"><?php echo esc_html($log->model_code === 'default' ? __('Auto Select', 'js-support-ticket') : $log->model_code); ?></span>
                                        </td>
                                        <td class="js-ticket-zywrap-text-muted"><?php echo esc_html(number_format($log->total_tokens)); ?></td>
                                        <td class="js-ticket-zywrap-text-muted"><?php echo esc_html(number_format($log->latency_ms)); ?> ms</td>
                                        <td>
                                            <?php if ($log->status === 'success') : ?>
                                                <span class="js-ticket-zywrap-status-tag js-ticket-zywrap-status-tag-success">
                                                    <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span> <?php echo esc_html(__('Success', 'js-support-ticket')); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="js-ticket-zywrap-status-tag js-ticket-zywrap-status-tag-error" title="<?php echo esc_attr($log->error_message); ?>">
                                                    <span class="dashicons dashicons-warning" aria-hidden="true"></span> <?php echo esc_html(__('Error', 'js-support-ticket')); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="js-ticket-zywrap-sidebar">
                    <div class="js-ticket-zywrap-card js-ticket-zywrap-p24">
                        <h3 class="js-ticket-zywrap-sidebar-h3"><?php echo esc_html(__('Navigation', 'js-support-ticket')); ?></h3>
                        <div class="js-ticket-zywrap-sidebar-links">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=zywrap&jstlay=zywrap_settings')); ?>" class="js-ticket-zywrap-link-card">
                                <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span> <?php echo esc_html(__('Global Settings', 'js-support-ticket')); ?>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=zywrap&jstlay=zywrap_playground')); ?>" class="js-ticket-zywrap-link-card">
                                <span class="dashicons dashicons-editor-code" aria-hidden="true"></span> <?php echo esc_html(__('Playground', 'js-support-ticket')); ?>
                            </a>
                            <a href="https://www.zywrap.com/docs" target="_blank" class="js-ticket-zywrap-link-card">
                                <span class="dashicons dashicons-media-document" aria-hidden="true"></span> <?php echo esc_html(__('Documentation', 'js-support-ticket')); ?>
                            </a>
                        </div>
                    </div>

                    <div class="js-ticket-zywrap-card js-ticket-zywrap-p24 js-ticket-zywrap-text-center js-ticket-zywrap-bg-gradient-sync">
                        <div class="js-ticket-zywrap-sync-icon-box">
                            <span class="dashicons dashicons-cloud-saved" aria-hidden="true"></span>
                        </div>
                        <div class="js-ticket-zywrap-sidebar-h3"><?php echo esc_html(__('Database Sync', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-zywrap-sync-text">
                            <?php echo esc_html(__('Last Synced', 'js-support-ticket').':'); ?> <br>
                            <strong class="js-ticket-zywrap-text-slate"><?php echo empty($last_sync) ? esc_html(__('Never', 'js-support-ticket')) : esc_html(wp_date('M j, Y - g:i A', $last_sync)); ?></strong>
                        </div>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=zywrap&jstlay=zywrap_settings')); ?>" class="js-ticket-zywrap-btn-sync">
                            <?php echo esc_html(__('Run Delta Sync', 'js-support-ticket')); ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>