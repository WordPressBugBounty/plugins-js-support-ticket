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

<style>
    .zy-dashboard { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #334155; }
    .zy-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .zy-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.03); }
    .zy-stat-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .zy-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
    .zy-table th { background: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; padding: 14px 24px; border-bottom: 1px solid #e2e8f0; }
    .zy-table td { padding: 16px 24px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; transition: background 0.1s; }
    .zy-table tr:hover td { background: #fcfcfd; }
    .zy-link-card { display: flex; align-items: center; gap: 12px; text-decoration: none; color: #475569; padding: 14px 16px; border-radius: 8px; transition: all 0.2s; border: 1px solid transparent; font-weight: 500; font-size: 13.5px; }
    .zy-link-card:hover { background: #f8fafc; border-color: #e2e8f0; color: #2563eb; }
    .zy-link-card .dashicons { color: #64748b; transition: color 0.2s; }
    .zy-link-card:hover .dashicons { color: #2563eb; }
</style>

<div id="jsstadmin-wrapper" class="zy-dashboard">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Zywrap Dashboard','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div id="jsstadmin-head" style="clear: both; overflow: hidden; padding-bottom: 15px; margin-bottom: 25px;">
            <h1 class="jsstadmin-head-text" style="display:flex; align-items:center; gap:10px; float: left; margin: 0;">
                <span class="dashicons dashicons-superhero-alt" style="color: #2563eb; font-size: 26px; width: 26px; height: 26px;"></span> 
                <?php echo esc_html(__('Zywrap AI Dashboard', 'js-support-ticket')); ?>
            </h1>
        </div>
        
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n" style="padding: 25px;">
            
            <div style="position: relative; overflow: hidden; background: linear-gradient(135deg, #020617 0%, #0f172a 100%); border-radius: 16px; padding: 45px 50px; color: #fff; margin-bottom: 30px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid #1e293b;">
                <div style="position: absolute; top: -50%; right: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

                <div style="position: relative; z-index: 10; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 30px;">
                    <div style="max-width: 650px;">
                        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 18px;">
                            <span class="dashicons dashicons-superhero-alt" style="font-size:13px; width:13px; height:13px; margin-top:-2px;"></span> <?php echo esc_html(__('Zero Prompt Engineering', 'js-support-ticket')); ?>
                        </div>
                        
                        <h2 style="margin: 0 0 15px 0; font-size: 34px; font-weight: 800; color: #f8fafc; letter-spacing: -0.5px; line-height: 1.2;">
                            <?php echo esc_html(__('The Ultimate AI Copilot', 'js-support-ticket')); ?> <br><span style="color:#60a5fa;"><?php echo esc_html(__('for JS Help Desk.', 'js-support-ticket')); ?></span>
                        </h2>
                        
                        <p style="margin: 0 0 25px 0; font-size: 15.5px; color: #cbd5e1; font-weight: 400; line-height: 1.6;">
                            <?php echo esc_html(__('Equip your agents with top-tier AI models (OpenAI, Anthropic, Gemini, Groq) natively inside the ticket editor. Automate translations, summarize long threads, extract data, and draft perfect replies in seconds.', 'js-support-ticket')); ?>
                        </p>
                        
                        <?php if (!$is_connected) : ?>
                            <div style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
                                <a href="https://zywrap.com/register?utm_source=wordpress-plugin&utm_medium=js-support-ticket" target="_blank" style="background: #2563eb; color: #fff; text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 8px; font-size: 14px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                                    <?php echo esc_html(__('Claim 10,000 Free Credits &rarr;', 'js-support-ticket')); ?>
                                </a>
                                <a href="?page=zywrap&jstlay=zywrap_settings" style="color: #f8fafc; text-decoration: none; font-weight: 500; font-size: 14px; border: 1px solid #475569; padding: 11px 24px; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                                    <?php echo esc_html(__('Connect API Key', 'js-support-ticket')); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="text-align: right; min-width: 220px;">
                        <?php if ($is_connected) : ?>
                            <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 14px; padding: 25px; text-align: center; backdrop-filter: blur(8px);">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: rgba(16, 185, 129, 0.15); border-radius: 50%; margin-bottom: 15px; box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);">
                                    <span class="dashicons dashicons-yes" style="color: #34d399; font-size: 32px; width: 32px; height: 32px;"></span>
                                </div>
                                <div style="color: #34d399; font-size: 15px; font-weight: 700; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;"><?php echo esc_html(__('System Active', 'js-support-ticket')); ?></div>
                                <div style="font-size: 13px; color: #94a3b8; font-weight: 500;"><?php echo esc_html(__('API Connected & Ready', 'js-support-ticket')); ?></div>
                            </div>
                        <?php else : ?>
                            <div style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.15); border-radius: 14px; padding: 25px; text-align: center; backdrop-filter: blur(8px);">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: rgba(255,255,255,0.05); border-radius: 50%; margin-bottom: 15px;">
                                    <span class="dashicons dashicons-lock" style="color: #94a3b8; font-size: 28px; width: 28px; height: 28px;"></span>
                                </div>
                                <div style="color: #f1f5f9; font-size: 15px; font-weight: 700; margin-bottom: 6px; letter-spacing: 0.5px; text-transform: uppercase;"><?php echo esc_html(__('Disconnected', 'js-support-ticket')); ?></div>
                                <div style="font-size: 13px; color: #64748b; font-weight: 500;"><?php echo esc_html(__('Configuration Required', 'js-support-ticket')); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 30px;">
                <div class="zy-card" style="padding: 24px; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <div style="color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;"><?php echo esc_html(__('Total API Requests', 'js-support-ticket')); ?></div>
                        <div style="font-size: 32px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;"><?php echo number_format($total_reqs); ?></div>
                    </div>
                    <div class="zy-stat-icon" style="background:#eff6ff; color:#3b82f6;">
                        <span class="dashicons dashicons-update-alt" style="font-size:26px; width:26px; height:26px;"></span>
                    </div>
                </div>
                
                <div class="zy-card" style="padding: 24px; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <div style="color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;"><?php echo esc_html(__('Tokens Processed', 'js-support-ticket')); ?></div>
                        <div style="font-size: 32px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;"><?php echo number_format($total_toks); ?></div>
                    </div>
                    <div class="zy-stat-icon" style="background:#f0fdf4; color:#16a34a;">
                        <span class="dashicons dashicons-database" style="font-size:26px; width:26px; height:26px;"></span>
                    </div>
                </div>
                
                <div class="zy-card" style="padding: 24px; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <div style="color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;"><?php echo esc_html(__('API Errors', 'js-support-ticket')); ?></div>
                        <div style="font-size: 32px; font-weight: 800; color: <?php echo $total_errs > 0 ? '#dc2626' : '#0f172a'; ?>; letter-spacing: -0.5px;"><?php echo number_format($total_errs); ?></div>
                    </div>
                    <div class="zy-stat-icon" style="background:#fef2f2; color:#dc2626;">
                        <span class="dashicons dashicons-warning" style="font-size:26px; width:26px; height:26px;"></span>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 3.2fr 1fr; gap: 30px;">
                
                <div class="zy-card" style="overflow: hidden; align-self: start;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background:#fff;">
                        <h2 style="margin: 0; font-size: 16px; color: #0f172a; font-weight: 700;"><?php echo esc_html(__('Recent AI Activity', 'js-support-ticket')); ?></h2>
                        <a href="?page=zywrap&jstlay=zywrap_logs" style="font-size:13px; text-decoration:none; color:#2563eb; font-weight:600; background:#eff6ff; padding:6px 12px; border-radius:6px; transition:background 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'"><?php echo esc_html(__('View All Logs', 'js-support-ticket')); ?></a>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="zy-table">
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
                                        <td colspan="5" style="padding: 50px; text-align: center; color: #94a3b8; font-size:14px; background:#fff;">
                                            <span class="dashicons dashicons-format-aside" style="font-size:32px; width:32px; height:32px; margin-bottom:10px; color:#cbd5e1;"></span><br>
                                            <?php echo esc_html(__('No activity logged yet. Start replying to tickets!', 'js-support-ticket')); ?>
                                        </td>
                                    </tr>
                                <?php else : foreach ($recent_logs as $log) : ?>
                                    <tr>
                                        <td>
                                            <div style="color: #1e293b; font-weight: 600; margin-bottom: 3px;"><?php echo esc_html(str_replace('_', ' ', ucwords($log->wrapper_code))); ?></div>
                                            <div style="font-size: 11px; color: #94a3b8; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;"><?php echo esc_html($log->wrapper_code); ?></div>
                                        </td>
                                        <td>
                                            <span style="background:#f1f5f9; border:1px solid #e2e8f0; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:500; color:#475569;"><?php echo esc_html($log->model_code === 'default' ? __('Auto Select', 'js-support-ticket') : $log->model_code); ?></span>
                                        </td>
                                        <td style="color: #64748b; font-weight:500;"><?php echo number_format($log->total_tokens); ?></td>
                                        <td style="color: #64748b; font-weight:500;"><?php echo number_format($log->latency_ms); ?> ms</td>
                                        <td>
                                            <?php if ($log->status === 'success') : ?>
                                                <span style="display:inline-flex; align-items:center; gap:4px; background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                                    <span class="dashicons dashicons-yes-alt" style="font-size:12px; width:12px; height:12px; margin-top:-1px;"></span> <?php echo esc_html(__('Success', 'js-support-ticket')); ?>
                                                </span>
                                            <?php else : ?>
                                                <span title="<?php echo esc_attr($log->error_message); ?>" style="display:inline-flex; align-items:center; gap:4px; background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: help; border-bottom: 1px dotted #991b1b;">
                                                    <span class="dashicons dashicons-warning" style="font-size:12px; width:12px; height:12px; margin-top:-1px;"></span> <?php echo esc_html(__('Error', 'js-support-ticket')); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 25px;">
                    
                    <div class="zy-card" style="padding: 24px;">
                        <h3 style="margin-top: 0; font-size: 14px; font-weight:700; margin-bottom: 15px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html(__('Navigation', 'js-support-ticket')); ?></h3>
                        
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <a href="?page=zywrap&jstlay=zywrap_settings" class="zy-link-card">
                                <span class="dashicons dashicons-admin-generic"></span> <?php echo esc_html(__('Global Settings', 'js-support-ticket')); ?>
                            </a>
                            <a href="?page=zywrap&jstlay=zywrap_playground" class="zy-link-card">
                                <span class="dashicons dashicons-editor-code"></span> <?php echo esc_html(__('Playground', 'js-support-ticket')); ?>
                            </a>
                            <a href="https://www.zywrap.com/docs" target="_blank" class="zy-link-card">
                                <span class="dashicons dashicons-media-document"></span> <?php echo esc_html(__('Documentation', 'js-support-ticket')); ?>
                            </a>
                        </div>
                    </div>

                    <div class="zy-card" style="padding: 25px; text-align: center; background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);">
                        <div style="width:48px; height:48px; background:#e2e8f0; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom: 15px;">
                            <span class="dashicons dashicons-cloud-saved" style="font-size: 24px; width: 24px; height: 24px; color: #64748b;"></span>
                        </div>
                        <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px;"><?php echo esc_html(__('Database Sync', 'js-support-ticket')); ?></div>
                        <div style="font-size: 12.5px; color: #64748b; margin-bottom: 18px; line-height:1.5;"><?php echo esc_html(__('Last synced:', 'js-support-ticket')); ?> <br><strong style="color:#475569;"><?php echo empty($last_sync) ? esc_html(__('Never', 'js-support-ticket')) : esc_html(wp_date('M j, Y - g:i A', $last_sync)); ?></strong></div>
                        <a href="?page=zywrap&jstlay=zywrap_settings" style="display:inline-block; width:100%; font-size: 13px; font-weight: 600; color: #2563eb; text-decoration: none; border:1px solid #bfdbfe; background:#eff6ff; padding:10px 0; border-radius:8px; transition:background 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'"><?php echo esc_html(__('Run Delta Sync', 'js-support-ticket')); ?></a>
                    </div>
                    
                </div>

            </div>
        </div>
    </div>
</div>