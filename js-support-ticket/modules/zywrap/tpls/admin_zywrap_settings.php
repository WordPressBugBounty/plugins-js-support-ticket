<?php
if (!defined('ABSPATH')) die('Restricted Access');

// 1. Get Data
$api_key = get_option('jsst_zywrap_api_key', '');
$zywrap_default_model = get_option('jsst_zywrap_default_model', '');
$zywrap_default_lang = get_option('jsst_zywrap_default_lang', 'English');
$last_sync = get_option('jsst_zywrap_last_sync');
$last_sync_display = $last_sync ? wp_date('M j, Y - g:i A', $last_sync) : __('Never', 'js-support-ticket');
$is_connected = !empty($api_key);

// 2. Count Local Wrappers to verify sync status
$prefix = jssupportticket::$_db->prefix . "js_ticket_";
$wrapper_count = 0;
$db_models = [];
$db_langs = [];

try {
    $wrapper_count = (int) jssupportticket::$_db->get_var("SELECT COUNT(*) FROM `{$prefix}zywrap_wrappers`");
    $db_models = jssupportticket::$_db->get_results("SELECT code, name FROM `{$prefix}zywrap_ai_models` WHERE status = 1 ORDER BY ordering ASC");
    $db_langs = jssupportticket::$_db->get_results("SELECT code, name FROM `{$prefix}zywrap_languages` WHERE status = 1 ORDER BY ordering ASC");
} catch (Exception $e) { }

$zywrap_nonce = wp_create_nonce('zywrap_ajax_action');

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
                        <li><a href="?page=jssupportticket"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Zywrap AI Settings','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configurations','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>"><img alt="Config" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" /></a>
                </div>
            </div>
        </div>
        
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text" style="display:flex; align-items:center; gap:8px;">
                <span class="dashicons dashicons-admin-generic" style="color: #2563eb; margin-top:2px;"></span> 
                <?php echo esc_html(__('Zywrap AI Settings', 'js-support-ticket')); ?>
            </h1>
        </div>
        
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n" style="padding: 25px;">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; max-width:1100px;">
                
                <div style="display: flex; flex-direction: column; gap: 25px;">
                    
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;">
                        <h3 style="font-size: 14px; margin-top: 0; margin-bottom: 15px; color: #1e293b; display: flex; align-items: center;">
                            <span class="dashicons dashicons-info" style="color: #2563eb; margin-right: 6px;"></span> <?php echo esc_html(__('How to get your key', 'js-support-ticket')); ?>
                        </h3>
                        <ul style="margin: 0 0 0 20px; font-size: 13px; color: #475569; line-height: 1.6;">
                            <li style="margin-bottom: 8px;">Create a free account at <a href="https://zywrap.com/register?utm_source=wordpress-plugin&utm_medium=js-support-ticket" target="_blank" style="text-decoration:none; font-weight:600; color: #2563eb;">Zywrap.com</a>.</li>
                            <li style="margin-bottom: 8px;">Receive <strong>10,000 Free Credits</strong> instantly.</li>
                            <li>Navigate to <em>API Keys</em> in your dashboard to generate a secret key.</li>
                        </ul>
                    </div>

                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h2 style="margin-top: 0; font-size: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                            <span style="background: #2563eb; color: #fff; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 12px; margin-right: 8px;">1</span>
                            API Authentication
                        </h2>
                        
                        <div style="margin-bottom: 15px; margin-top: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155;">API Key</label>
                            <div style="display: flex; gap: 10px; max-width: 500px;">
                                <input type="password" id="zywrap_api_key" value="<?php echo esc_attr($api_key); ?>" placeholder="sk-..." style="flex-grow: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-family: monospace;">
                                <button type="button" id="zywrap_save_key" class="button button-primary" style="height: 36px; padding: 0 20px;">Save Key</button>
                            </div>
                            <div id="zywrap_key_msg" style="margin-top: 10px; font-size: 13px; font-weight: 600;"></div>
                        </div>
                    </div>



                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); opacity: <?php echo $is_connected ? '1' : '0.5'; ?>; transition: opacity 0.3s;">
                        <h2 style="margin-top: 0; font-size: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                            <span style="background: #2563eb; color: #fff; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 12px; margin-right: 8px;">2</span>
                            Database Synchronization
                        </h2>
                        
                        <p style="font-size: 13px; color: #475569; margin-bottom: 15px; line-height: 1.5;">
                            <?php echo esc_html(__('To use the Co-Pilot, you must sync your local database with the Zywrap Cloud. This downloads the latest Prompts, Scenarios, and Configuration Schemas.', 'js-support-ticket')); ?>
                        </p>
                        
                        <div style="background: #fffbeb; border-left: 3px solid #f59e0b; padding: 12px 15px; border-radius: 4px; margin-bottom: 22px; font-size: 12.5px; color: #92400e; display: flex; gap: 10px; align-items: flex-start;">
                            <span class="dashicons dashicons-clock" style="color: #d97706; margin-top: -1px;"></span>
                            <div>
                                <strong><?php echo esc_html(__('Important:', 'js-support-ticket')); ?></strong> 
                                <?php echo esc_html(__('The initial sync processes a large data bundle and may take 3 to 5 minutes to complete. Please do not close or refresh this page while the sync is running.', 'js-support-ticket')); ?>
                            </div>
                        </div>

                        <div style="text-align: left; width: 100%; max-width: 400px;">
                            <button type="button" id="zywrap_sync_bundle" class="button button-primary" style="height: 48px; width: 100%; font-size: 15px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                                <span class="dashicons dashicons-download" style="line-height: 1; margin: 0;"></span> <?php echo esc_html(__('Download & Sync Data', 'js-support-ticket')); ?>
                            </button>
                            
                            <div id="zywrap_progress_container" style="display: none; margin-top: 15px; text-align: left;">
                                <div style="width: 100%; background: #e2e8f0; border-radius: 999px; height: 8px; overflow: hidden; margin-bottom: 8px;">
                                    <div id="zywrap_progress_fill" style="width: 0%; height: 100%; background: #2563eb; transition: width 1s linear;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: #64748b;">
                                    <span id="zywrap_sync_status">Initializing sync...</span>
                                    <span id="zywrap_progress_text">0%</span>
                                </div>
                            </div>
                            <div id="zywrap_quick_status" style="margin-top: 10px; font-size: 13px; font-weight: 600;">
                                <?php if(empty($api_key)) echo '<span style="color:#dc2626;">Save API Key to enable sync.</span>'; ?>
                            </div>
                        </div>
                    </div>

                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); opacity: <?php echo $is_connected ? '1' : '0.5'; ?>; transition: opacity 0.3s;">
                        <h2 style="margin-top: 0; font-size: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Global Preferences</h2>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 15px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155;">Default AI Model</label>
                                <select id="zywrap_default_model" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc;" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                                    <option value="">-- Platform Auto-Select --</option>
                                    <?php if (!empty($db_models)) { foreach ($db_models as $m) { ?>
                                        <option value="<?php echo esc_attr($m->code); ?>" <?php selected($zywrap_default_model, $m->code); ?>><?php echo esc_html($m->name); ?></option>
                                    <?php } } ?>
                                </select>
                                <p style="font-size: 12px; color: #64748b; margin-top: 6px; margin-bottom: 0;">Used automatically unless overridden.</p>
                            </div>

                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155;">Translation Language</label>
                                <select id="zywrap_default_lang" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc;" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                                    <option value="English" <?php selected($zywrap_default_lang, 'English'); ?>>English</option>
                                    <?php if (!empty($db_langs)) { foreach ($db_langs as $l) { 
                                        if ($l->name == 'English') continue;
                                    ?>
                                        <option value="<?php echo esc_attr($l->name); ?>" <?php selected($zywrap_default_lang, $l->name); ?>><?php echo esc_html($l->name); ?></option>
                                    <?php } } ?>
                                </select>
                                <p style="font-size: 12px; color: #64748b; margin-top: 6px; margin-bottom: 0;">1-click ticket translation target.</p>
                            </div>
                        </div>

                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 15px;">
                            <button type="button" id="zywrap_save_preferences" class="button" style="height: 36px; padding: 0 20px;" <?php echo empty($api_key) ? 'disabled' : ''; ?>>Save Preferences</button>
                            <span id="zywrap_pref_msg" style="font-size: 13px; font-weight: 600;"></span>
                        </div>
                    </div>

                </div>

                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;">
                        <div style="font-size: 32px; font-weight: 700; color: #0f172a; margin-bottom: 5px;"><?php echo $is_connected ? '<span style="color:#16a34a;">Active</span>' : '<span style="color:#dc2626;">Missing</span>'; ?></div>
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">API Status</div>
                    </div>

                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;">
                        <div style="font-size: 32px; font-weight: 700; color: #0f172a; margin-bottom: 5px;"><?php echo esc_html($wrapper_count); ?></div>
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Wrappers Synced</div>
                    </div>

                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;">
                        <div style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 5px;"><?php echo esc_html($last_sync_display); ?></div>
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Last Synced</div>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {

    // 1. Save API Key
    $('#zywrap_save_key').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).text('Saving...');
        $('#zywrap_key_msg').text('').removeClass('error updated');
        
        $.post(ajaxurl, {
            action: 'jsticket_ajax',
            jstmod: 'zywrap',
            task: 'saveApiKey', // Make sure this matches your model.php function
            api_key: $('#zywrap_api_key').val(),
            _wpnonce: '<?php echo esc_js($zywrap_nonce); ?>'
        }, function(response) {
            btn.prop('disabled', false).text('Save Key');
            try {
                var res = typeof response === 'object' ? response : JSON.parse(response);
                var messageText = res.data && res.data.message ? res.data.message : res.message;
                
                $('#zywrap_key_msg').text(messageText).css('color', res.success ? '#16a34a' : '#dc2626');
                if(res.success) { setTimeout(function(){ location.reload(); }, 1000); }
            } catch(e) {
                $('#zywrap_key_msg').text('System Error.').css('color', '#dc2626');
            }
        });
    });

    // 2. Save Preferences
    $('#zywrap_save_preferences').on('click', function() {
        var btn = $(this);
        var msg = $('#zywrap_pref_msg');
        btn.prop('disabled', true).text('Saving...');
        msg.text('').removeClass('error updated');

        $.post(ajaxurl, {
            action: 'jsticket_ajax',
            jstmod: 'zywrap',
            task: 'savePreferences', // Make sure this matches your model.php function
            default_model: $('#zywrap_default_model').val(),
            default_lang: $('#zywrap_default_lang').val(),
            _wpnonce: '<?php echo esc_js($zywrap_nonce); ?>' 
        }, function(response) {
            btn.prop('disabled', false).text('Save Preferences');
            try {
                var res = typeof response === 'object' ? response : JSON.parse(response);
                var messageText = res.data && res.data.message ? res.data.message : res.message;
                
                msg.text(messageText).css('color', res.success ? '#16a34a' : '#dc2626');
                if(res.success) { setTimeout(function(){ msg.fadeOut(300, function(){ $(this).text('').show(); }); }, 3000); }
            } catch(e) {
                msg.text('System Error.').css('color', '#dc2626');
            }
        });
    });

    // 3. Sync Data Bundle
    $('#zywrap_sync_bundle').on('click', function() {
        var btn = $(this);
        var statusContainer = $('#zywrap_progress_container');
        var quickStatus = $('#zywrap_quick_status');
        var progressFill = $('#zywrap_progress_fill');
        var statusText = $('#zywrap_sync_status');
        var percentText = $('#zywrap_progress_text');
        
        btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Syncing Engine...');
        quickStatus.text('');
        statusContainer.show();
        progressFill.css({'width': '0%', 'background': '#2563eb'});
        
        var width = 0;
        var progressInterval = setInterval(function() {
            var increment = (95 - width) * 0.05; 
            width += increment;
            
            progressFill.css('width', width + '%');
            percentText.text(Math.round(width) + '%');

            if (width > 10 && width < 40) { statusText.text('Connecting to Zywrap Cloud...'); } 
            else if (width >= 40 && width < 70) { statusText.text('Extracting schemas and configurations...'); } 
            else if (width >= 70) { statusText.text('Optimizing local database records...'); }
        }, 2000); 

        $.post(ajaxurl, {
            action: 'jsticket_ajax',
            jstmod: 'zywrap',
            task: 'syncDataBundle',
            _wpnonce: '<?php echo esc_js($zywrap_nonce); ?>'
        }, function(response) {
            clearInterval(progressInterval);
            try {
                var res = typeof response === 'object' ? response : JSON.parse(response);
                if (res.success) {
                    progressFill.css({'width': '100%', 'background': '#16a34a', 'transition': 'width 0.2s ease-out'});
                    percentText.text('100%').css('color', '#16a34a');
                    statusText.text(res.data.message || 'Sync Complete!').css('color', '#16a34a');
                    btn.html('<span class="dashicons dashicons-yes-alt"></span> Synced');
                    setTimeout(function(){ location.reload(); }, 2000);
                } else {
                    progressFill.css('background', '#dc2626');
                    statusText.text('Sync Failed.').css('color', '#dc2626');
                    quickStatus.text(res.data.message || res.message).css('color', '#dc2626');
                    btn.prop('disabled', false).html('<span class="dashicons dashicons-update-alt"></span> Try Again');
                }
            } catch(e) {
                clearInterval(progressInterval);
                statusContainer.hide();
                quickStatus.text('A system error occurred. Check server limits.').css('color', '#dc2626');
                btn.prop('disabled', false).html('<span class="dashicons dashicons-update-alt"></span> Try Again');
            }
        }).fail(function() {
            clearInterval(progressInterval);
            statusContainer.hide();
            quickStatus.text('Server timeout. The process took too long.').css('color', '#dc2626');
            btn.prop('disabled', false).html('<span class="dashicons dashicons-update-alt"></span> Try Again');
        });
    });

});
</script>