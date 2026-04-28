<?php
if (!defined('ABSPATH')) die('Restricted Access');

// 1. Get Data
$js_ticket_api_key      = get_option('jsst_zywrap_api_key', '');
$js_ticket_default_model = get_option('jsst_zywrap_default_model', '');
$js_ticket_default_lang  = get_option('jsst_zywrap_default_lang', 'English');
$js_ticket_last_sync     = get_option('jsst_zywrap_last_sync');
$js_ticket_sync_display  = $js_ticket_last_sync ? wp_date('M j, Y - g:i A', $js_ticket_last_sync) : __('Never', 'js-support-ticket');
$js_ticket_is_connected  = !empty($js_ticket_api_key);

// 2. Database verification
$js_ticket_db_prefix = jssupportticket::$_db->prefix . "js_ticket_";
$js_ticket_wrapper_count = 0;
$js_ticket_db_models = [];
$js_ticket_db_langs = [];

try {
    $js_ticket_wrapper_count = (int) jssupportticket::$_db->get_var("SELECT COUNT(*) FROM `{$js_ticket_db_prefix}zywrap_wrappers`");
    $js_ticket_db_models = jssupportticket::$_db->get_results("SELECT code, name FROM `{$js_ticket_db_prefix}zywrap_ai_models` WHERE status = 1 ORDER BY ordering ASC");
    $js_ticket_db_langs = jssupportticket::$_db->get_results("SELECT code, name FROM `{$js_ticket_db_prefix}zywrap_languages` WHERE status = 1 ORDER BY ordering ASC");
} catch (Exception $e) { }

JSSTmessage::getMessage();
?>

<div id="jsstadmin-wrapper" class="js-ticket-zywrap-settings-page">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Zywrap AI Settings','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configurations','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_attr(__('Config','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
            </div>
        </div>
        
        <div id="jsstadmin-head" class="js-ticket-zywrap-header-area">
            <h1 class="jsstadmin-head-text js-ticket-zywrap-flex-align">
                <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span> 
                <?php echo esc_html(__('Zywrap AI Settings', 'js-support-ticket')); ?>
            </h1>
        </div>
        
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n js-ticket-zywrap-padding-25">
            
            <div class="js-ticket-zywrap-settings-grid">
                
                <div class="js-ticket-zywrap-form-column">
                    
                    <div class="js-ticket-zywrap-info-box">
                        <h3 class="js-ticket-zywrap-info-title">
                            <span class="dashicons dashicons-info" aria-hidden="true" style="color: #2563eb; margin-right: 6px;"></span> 
                            <?php echo esc_html(__('How to get your key', 'js-support-ticket')); ?>
                        </h3>
                        <ul class="js-ticket-zywrap-info-list">
                            <li><?php echo esc_html__('Create a free account at', 'js-support-ticket'); ?> <a href="https://zywrap.com/register" target="_blank">Zywrap.com</a>.</li>
                            <li><?php echo esc_html__('Receive 10,000 Free Credits instantly.', 'js-support-ticket'); ?></li>
                            <li><?php echo esc_html__('Navigate to API Keys in your dashboard to generate a secret key.', 'js-support-ticket'); ?></li>
                        </ul>
                    </div>

                    <div class="js-ticket-zywrap-card">
                        <h2 class="js-ticket-zywrap-card-title">
                            <span class="js-ticket-zywrap-step-number">1</span>
                            <?php echo esc_html(__('API Authentication', 'js-support-ticket')); ?>
                        </h2>
                        
                        <div class="js-ticket-zywrap-form-group">
                            <label class="js-ticket-zywrap-label"><?php echo esc_html__('API Key', 'js-support-ticket'); ?></label>
                            <div class="js-ticket-zywrap-input-group">
                                <input type="password" id="zywrap_api_key" value="<?php echo esc_attr($js_ticket_api_key); ?>" placeholder="sk-...">
                                <button type="button" id="zywrap_save_key" class="button button-primary"><?php echo esc_html__('Save Key', 'js-support-ticket'); ?></button>
                            </div>
                            <div id="zywrap_key_msg" class="js-ticket-zywrap-msg"></div>
                        </div>
                    </div>

                    <div class="js-ticket-zywrap-card <?php echo $js_ticket_is_connected ? '' : 'js-ticket-zywrap-dimmed'; ?>">
                        <h2 class="js-ticket-zywrap-card-title">
                            <span class="js-ticket-zywrap-step-number">2</span>
                            <?php echo esc_html(__('Database Synchronization', 'js-support-ticket')); ?>
                        </h2>
                        
                        <p class="js-ticket-zywrap-description">
                            <?php echo esc_html(__('To use the Co-Pilot, you must sync your local database with the Zywrap Cloud. This downloads the latest Prompts and Scenarios.', 'js-support-ticket')); ?>
                        </p>
                        
                        <div class="js-ticket-zywrap-warning-box">
                            <span class="dashicons dashicons-clock" aria-hidden="true"></span>
                            <div>
                                <strong><?php echo esc_html(__('Important:', 'js-support-ticket')); ?></strong> 
                                <?php echo esc_html(__('The initial sync may take 3 to 5 minutes. Please do not close or refresh this page.', 'js-support-ticket')); ?>
                            </div>
                        </div>

                        <div class="js-ticket-zywrap-sync-action-area">
                            <button type="button" id="zywrap_sync_bundle" class="button button-primary js-ticket-zywrap-btn-large" <?php echo empty($js_ticket_api_key) ? 'disabled' : ''; ?>>
                                <span class="dashicons dashicons-download" aria-hidden="true"></span> <?php echo esc_html(__('Download & Sync Data', 'js-support-ticket')); ?>
                            </button>
                            
                            <div id="zywrap_progress_container" class="js-ticket-zywrap-progress-wrap">
                                <div class="js-ticket-zywrap-progress-bar">
                                    <div id="zywrap_progress_fill" class="js-ticket-zywrap-progress-fill"></div>
                                </div>
                                <div class="js-ticket-zywrap-progress-meta">
                                    <span id="zywrap_sync_status"><?php echo esc_html__('Initializing sync...', 'js-support-ticket'); ?></span>
                                    <span id="zywrap_progress_text">0%</span>
                                </div>
                            </div>
                            <div id="zywrap_quick_status" class="js-ticket-zywrap-msg">
                                <?php if(empty($js_ticket_api_key)) echo '<span class="js-ticket-zywrap-text-red">'.esc_html__('Save API Key to enable sync.', 'js-support-ticket').'</span>'; ?>
                            </div>
                        </div>
                    </div>

                    <div class="js-ticket-zywrap-card <?php echo $js_ticket_is_connected ? '' : 'js-ticket-zywrap-dimmed'; ?>">
                        <h2 class="js-ticket-zywrap-card-title"><?php echo esc_html__('Global Preferences', 'js-support-ticket'); ?></h2>
                        
                        <div class="js-ticket-zywrap-prefs-grid">
                            <div class="js-ticket-zywrap-form-group">
                                <label class="js-ticket-zywrap-label"><?php echo esc_html__('Default AI Model', 'js-support-ticket'); ?></label>
                                <select id="js_ticket_zywrap_default_model" <?php echo empty($js_ticket_api_key) ? 'disabled' : ''; ?>>
                                    <option value=""><?php echo esc_html__('Platform Auto-Select', 'js-support-ticket'); ?></option>
                                    <?php foreach ($js_ticket_db_models as $m) : ?>
                                        <option value="<?php echo esc_attr($m->code); ?>" <?php selected($js_ticket_default_model, $m->code); ?>><?php echo esc_html($m->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="js-ticket-zywrap-hint"><?php echo esc_html__('Used automatically unless overridden.', 'js-support-ticket'); ?></p>
                            </div>

                            <div class="js-ticket-zywrap-form-group">
                                <label class="js-ticket-zywrap-label"><?php echo esc_html__('Translation Language', 'js-support-ticket'); ?></label>
                                <select id="js_ticket_zywrap_default_lang" <?php echo empty($js_ticket_api_key) ? 'disabled' : ''; ?>>
                                    <option value="English" <?php selected($js_ticket_default_lang, 'English'); ?>><?php echo esc_html__('English', 'js-support-ticket'); ?></option>
                                    <?php foreach ($js_ticket_db_langs as $l) : if ($l->name == 'English') continue; ?>
                                        <option value="<?php echo esc_attr($l->name); ?>" <?php selected($js_ticket_default_lang, $l->name); ?>><?php echo esc_html($l->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="js-ticket-zywrap-hint"><?php echo esc_html__('1-click ticket translation target.', 'js-support-ticket'); ?></p>
                            </div>
                        </div>

                        <div class="js-ticket-zywrap-footer-actions">
                            <button type="button" id="zywrap_save_preferences" class="button" <?php echo empty($js_ticket_api_key) ? 'disabled' : ''; ?>>
                                <?php echo esc_html__('Save Preferences', 'js-support-ticket'); ?>
                            </button>
                            <span id="zywrap_pref_msg" class="js-ticket-zywrap-msg"></span>
                        </div>
                    </div>
                </div>

                <div class="js-ticket-zywrap-sidebar-column">
                    <div class="js-ticket-zywrap-mini-card">
                        <div class="js-ticket-zywrap-mini-val <?php echo $js_ticket_is_connected ? 'js-ticket-zywrap-text-green' : 'js-ticket-zywrap-text-red'; ?>">
                            <?php echo $js_ticket_is_connected ? esc_html__('Active', 'js-support-ticket') : esc_html__('Missing', 'js-support-ticket'); ?>
                        </div>
                        <div class="js-ticket-zywrap-mini-label"><?php echo esc_html__('API Status', 'js-support-ticket'); ?></div>
                    </div>

                    <div class="js-ticket-zywrap-mini-card">
                        <div class="js-ticket-zywrap-mini-val"><?php echo esc_html($js_ticket_wrapper_count); ?></div>
                        <div class="js-ticket-zywrap-mini-label"><?php echo esc_html__('Wrappers Synced', 'js-support-ticket'); ?></div>
                    </div>

                    <div class="js-ticket-zywrap-mini-card">
                        <div class="js-ticket-zywrap-mini-val js-ticket-zywrap-font-small"><?php echo esc_html($js_ticket_sync_display); ?></div>
                        <div class="js-ticket-zywrap-mini-label"><?php echo esc_html__('Last Synced', 'js-support-ticket'); ?></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$jsst_jssupportticket_js = "
    jQuery(document).ready(function (\$) {
        var ajaxurl = '" . admin_url('admin-ajax.php') . "';

        // 1. Save API Key
        \$('#zywrap_save_key').on('click', function() {
            var btn = \$(this);
            btn.prop('disabled', true).text('" . esc_js(__("Saving...", "js-support-ticket")) . "');
            \$('#zywrap_key_msg').text('').removeClass('error updated');
            
            \$.post(ajaxurl, {
                action: 'jsticket_ajax',
                jstmod: 'zywrap',
                task: 'saveApiKey', 
                api_key: \$('#zywrap_api_key').val(),
                _wpnonce: '" . esc_attr(wp_create_nonce("save_api_key")) . "'
            }, function(response) {
                btn.prop('disabled', false).text('" . esc_js(__("Save Key", "js-support-ticket")) . "');
                try {
                    var res = typeof response === 'object' ? response : JSON.parse(response);
                    var messageText = res.data && res.data.message ? res.data.message : res.message;
                    
                    \$('#zywrap_key_msg').text(messageText).css('color', res.success ? '#16a34a' : '#dc2626');
                    if(res.success) { 
                        setTimeout(function(){ location.reload(); }, 1000); 
                    }
                } catch(e) {
                    \$('#zywrap_key_msg').text('" . esc_js(__("System Error", "js-support-ticket")) . ".').css('color', '#dc2626');
                }
            });
        });

        // 2. Save Preferences
        \$('#zywrap_save_preferences').on('click', function() {
            var btn = \$(this);
            var msg = \$('#zywrap_pref_msg');
            btn.prop('disabled', true).text('" . esc_js(__("Saving...", "js-support-ticket")) . "');
            msg.text('');

            \$.post(ajaxurl, {
                action: 'jsticket_ajax',
                jstmod: 'zywrap',
                task: 'savePreferences', 
                default_model: \$('#js_ticket_zywrap_default_model').val(), 
                default_lang: \$('#js_ticket_zywrap_default_lang').val(),
                _wpnonce: '" . esc_attr(wp_create_nonce("save_preferences")) . "' 
            }, function(response) {
                btn.prop('disabled', false).text('" . esc_js(__("Save Preferences", "js-support-ticket")) . "');
                try {
                    var res = typeof response === 'object' ? response : JSON.parse(response);
                    var messageText = res.data && res.data.message ? res.data.message : res.message;
                    
                    msg.text(messageText).css('color', res.success ? '#16a34a' : '#dc2626');
                    if(res.success) { 
                        setTimeout(function(){ msg.fadeOut(300, function(){ \$(this).text('').show(); }); }, 3000); 
                    }
                } catch(e) {
                    msg.text('" . esc_js(__("System Error", "js-support-ticket")) . ".').css('color', '#dc2626');
                }
            });
        });

        // 3. Sync Data Bundle
        \$('#zywrap_sync_bundle').on('click', function() {
            var btn = \$(this);
            var statusContainer = \$('#zywrap_progress_container');
            var quickStatus = \$('#zywrap_quick_status');
            var progressFill = \$('#zywrap_progress_fill');
            var statusText = \$('#zywrap_sync_status');
            var percentText = \$('#zywrap_progress_text');
            
            btn.prop('disabled', true).html('<span class=\"spinner is-active\" style=\"float:none; margin:0 5px 0 0;\"></span> " . esc_js(__("Syncing Engine...", "js-support-ticket")) . "');
            quickStatus.text('');
            statusContainer.show();
            progressFill.css({'width': '0%', 'background': '#2563eb'});
            
            var width = 0;
            var progressInterval = setInterval(function() {
                var increment = (95 - width) * 0.05; 
                width += increment;
                
                progressFill.css('width', width + '%');
                percentText.text(Math.round(width) + '%');

                if (width > 10 && width < 40) { 
                    statusText.text('" . esc_js(__("Connecting to Zywrap Cloud...", "js-support-ticket")) . "'); 
                } 
                else if (width >= 40 && width < 70) { 
                    statusText.text('" . esc_js(__("Extracting schemas...", "js-support-ticket")) . "'); 
                } 
                else if (width >= 70) { 
                    statusText.text('" . esc_js(__("Optimizing database...", "js-support-ticket")) . "'); 
                }
            }, 2000); 

            \$.post(ajaxurl, {
                action: 'jsticket_ajax',
                jstmod: 'zywrap',
                task: 'syncDataBundle',
                _wpnonce: '" . esc_attr(wp_create_nonce("sync_data_bundle")) . "'
            }, function(response) {
                clearInterval(progressInterval);
                try {
                    var res = typeof response === 'object' ? response : JSON.parse(response);
                    if (res.success) {
                        progressFill.css({'width': '100%', 'background': '#16a34a', 'transition': 'width 0.2s ease-out'});
                        percentText.text('100%').css('color', '#16a34a');
                        statusText.text(res.data.message || '" . esc_js(__("Sync Complete!", "js-support-ticket")) . "').css('color', '#16a34a');
                        btn.html('<span class=\"dashicons dashicons-yes-alt\"></span> " . esc_js(__("Synced", "js-support-ticket")) . "');
                        setTimeout(function(){ location.reload(); }, 2000);
                    } else {
                        progressFill.css('background', '#dc2626');
                        statusText.text('" . esc_js(__("Sync Failed", "js-support-ticket")) . ".').css('color', '#dc2626');
                        quickStatus.text(res.data.message || res.message).css('color', '#dc2626');
                        btn.prop('disabled', false).html('<span class=\"dashicons dashicons-update-alt\"></span> " . esc_js(__("Try Again", "js-support-ticket")) . "');
                    }
                } catch(e) {
                    clearInterval(progressInterval);
                    statusContainer.hide();
                    quickStatus.text('" . esc_js(__("A system error occurred.", "js-support-ticket")) . "').css('color', '#dc2626');
                    btn.prop('disabled', false).html('<span class=\"dashicons dashicons-update-alt\"></span> " . esc_js(__("Try Again", "js-support-ticket")) . "');
                }
            }).fail(function() {
                clearInterval(progressInterval);
                statusContainer.hide();
                quickStatus.text('" . esc_js(__("Server timeout.", "js-support-ticket")) . "').css('color', '#dc2626');
                btn.prop('disabled', false).html('<span class=\"dashicons dashicons-update-alt\"></span> " . esc_js(__("Try Again", "js-support-ticket")) . "');
            });
        });
    });
";

wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
?>