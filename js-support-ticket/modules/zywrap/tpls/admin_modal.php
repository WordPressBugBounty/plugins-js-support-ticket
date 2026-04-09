<?php 
if (!defined('ABSPATH')) die('Restricted Access'); 
$zywrap_nonce = wp_create_nonce('zywrap_ajax_action');
$zywrap_model = JSSTincluder::getJSModel('zywrap');

// Securely load the local Marked.js library
wp_enqueue_script('zywrap-marked-js', JSST_PLUGIN_URL . 'modules/zywrap/js/marked.min.js', array(), '1.0.0', true);

// Fetch Global Default Model
$global_default_model = get_option('jsst_zywrap_default_model', '');

// Fetch Data
$raw_use_cases = $zywrap_model->getSupportUseCases();
$dynamic_tones = $zywrap_model->getDynamicTones();
$dynamic_models = $zywrap_model->getDynamicModels();

$use_cases_map = array();
if (!empty($raw_use_cases)) {
    foreach ($raw_use_cases as $uc) {
        $use_cases_map[$uc->code] = $uc;
    }
}

// Intent Groupings
$intents = array(
    'compose' => array(
        'ticket_thread_reply_composer',
        'internal_support_note_generator',
        'investigation_status_update',
        'troubleshooting_first_response',
        'wordpress_plugin_conflict_first_response',
        'white_screen_fatal_error_initial_response',
        'license_key_activation_help'
    ),
    'ask_info' => array(
        'debug_log_request_reply',
        'reproduction_steps_request',
        'technical_requirement_request',
        'javascript_console_error_request',
        'safe_temporary_admin_access_request',
        'staging_site_request_before_investigation'
    ),
    'escalate' => array(
        'customer_retest_request',
        'please_test_this_build_reply',
        'technical_escalation_handoff',
        'escalated_bug_response',
        'waiting_on_engineering_update'
    )
);
?>

<div id="jsst-zywrap-backdrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.75); z-index:99998; backdrop-filter: blur(3px);"></div>

<div id="jsst-zywrap-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); width:1250px; max-width:95vw; height:85vh; max-height:850px; z-index:99999;">
    
    <div style="background:#f8fafc; border-radius:12px; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25); display:flex; flex-direction:column; overflow:hidden; width:100%; height:100%;">
        
        <div style="background:linear-gradient(135deg, #1e40af 0%, #2563eb 100%); padding:15px 25px; color:#fff; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
            <div>
                <h3 style="margin:0 0 4px 0; color:#fff; font-size:18px; font-weight:600; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-superhero-alt" style="font-size:22px; width:22px; height:22px;"></span> <?php echo esc_html(__('Zywrap Co-Pilot', 'js-support-ticket')); ?>
                </h3>
                <div style="font-size:12px; color:#bfdbfe; font-weight:500;"><?php echo esc_html(__('Call AI by Code. Zero Prompt Engineering.', 'js-support-ticket')); ?></div>
            </div>
            <a href="#" id="jsst-zywrap-close" style="color:#fff; text-decoration:none; display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:rgba(255,255,255,0.15); transition:background 0.2s;">
                <span class="dashicons dashicons-no-alt" style="pointer-events:none;"></span>
            </a>
        </div>

        <div style="display:flex; flex-grow:1; overflow:hidden;">
            
            <div style="width:380px; background:#fff; border-right:1px solid #e2e8f0; display:flex; flex-direction:column; flex-shrink:0;">
                
                <div style="padding:20px; border-bottom:1px solid #f1f5f9; background:#f8fafc;">
                    <label style="font-weight:600; display:block; margin-bottom:10px; color:#64748b; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;"><?php echo esc_html(__('Support Intent', 'js-support-ticket')); ?></label>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" class="zywrap-intent-tab active" data-target="compose" style="padding:6px 12px; border-radius:15px; border:1px solid #2563eb; background:#eff6ff; color:#1d4ed8; font-weight:600; font-size:12px; cursor:pointer;"><?php echo esc_html(__('Compose', 'js-support-ticket')); ?></button>
                        <button type="button" class="zywrap-intent-tab" data-target="ask_info" style="padding:6px 12px; border-radius:15px; border:1px solid #cbd5e1; background:#fff; color:#475569; font-weight:600; font-size:12px; cursor:pointer;"><?php echo esc_html(__('Ask Info', 'js-support-ticket')); ?></button>
                        <button type="button" class="zywrap-intent-tab" data-target="escalate" style="padding:6px 12px; border-radius:15px; border:1px solid #cbd5e1; background:#fff; color:#475569; font-weight:600; font-size:12px; cursor:pointer;"><?php echo esc_html(__('Escalate', 'js-support-ticket')); ?></button>
                        <button type="button" class="zywrap-intent-tab" data-target="more" style="padding:6px 12px; border-radius:15px; border:1px solid #cbd5e1; background:#fff; color:#475569; font-weight:600; font-size:12px; cursor:pointer;"><?php echo esc_html(__('Library', 'js-support-ticket')); ?></button>
                    </div>
                </div>

                <div style="overflow-y:auto; flex-grow:1; padding:15px;">
                    
                    <div id="zywrap-intent-compose" class="zywrap-intent-panel">
                        <?php if (isset($use_cases_map['ticket_thread_reply_composer'])): ?>
                            <div class="zywrap-action-card featured selected" data-code="ticket_thread_reply_composer" data-title="<?php echo esc_attr(__('Ticket Thread Reply Composer', 'js-support-ticket')); ?>" style="border:2px solid #2563eb; background:#eff6ff; padding:12px 15px; border-radius:8px; cursor:pointer; margin-bottom:10px; position:relative;">
                                <div style="position:absolute; top:-8px; right:12px; background:#2563eb; color:#fff; font-size:10px; font-weight:bold; padding:2px 8px; border-radius:10px; text-transform:uppercase;"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></div>
                                <h4 style="margin:0 0 4px 0; color:#1e3a8a; font-size:14px; display:flex; align-items:center; gap:6px;"><span class="dashicons dashicons-edit" style="font-size:16px; width:16px; height:16px;"></span> <?php echo esc_html(__('Ticket Thread Reply Composer', 'js-support-ticket')); ?></h4>
                                <p style="margin:0; font-size:12px; color:#3b82f6; line-height:1.4;"><?php echo esc_html(__('Reads the thread and drafts the best response.', 'js-support-ticket')); ?></p>
                            </div>
                        <?php endif; ?>

                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <?php 
                            foreach($intents['compose'] as $code) {
                                if($code === 'ticket_thread_reply_composer') continue;
                                if(isset($use_cases_map[$code])) {
                                    echo '<div class="zywrap-action-card list-item" data-code="'.esc_attr($code).'" data-title="'.esc_attr($use_cases_map[$code]->name).'" style="border:1px solid #cbd5e1; background:#fff; padding:10px 15px; border-radius:6px; cursor:pointer; transition:all 0.2s;"><h4 style="margin:0; color:#334155; font-size:13px;">'.esc_html($use_cases_map[$code]->name).'</h4></div>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div id="zywrap-intent-ask_info" class="zywrap-intent-panel" style="display:none; flex-direction:column; gap:8px;">
                        <?php 
                        foreach($intents['ask_info'] as $code) {
                            if(isset($use_cases_map[$code])) {
                                echo '<div class="zywrap-action-card list-item" data-code="'.esc_attr($code).'" data-title="'.esc_attr($use_cases_map[$code]->name).'" style="border:1px solid #cbd5e1; background:#fff; padding:10px 15px; border-radius:6px; cursor:pointer; transition:all 0.2s;"><h4 style="margin:0; color:#334155; font-size:13px;">'.esc_html($use_cases_map[$code]->name).'</h4></div>';
                            }
                        }
                        ?>
                    </div>

                    <div id="zywrap-intent-escalate" class="zywrap-intent-panel" style="display:none; flex-direction:column; gap:8px;">
                        <?php 
                        foreach($intents['escalate'] as $code) {
                            if(isset($use_cases_map[$code])) {
                                echo '<div class="zywrap-action-card list-item" data-code="'.esc_attr($code).'" data-title="'.esc_attr($use_cases_map[$code]->name).'" style="border:1px solid #cbd5e1; background:#fff; padding:10px 15px; border-radius:6px; cursor:pointer; transition:all 0.2s;"><h4 style="margin:0; color:#334155; font-size:13px;">'.esc_html($use_cases_map[$code]->name).'</h4></div>';
                            }
                        }
                        ?>
                    </div>

                    <div id="zywrap-intent-more" class="zywrap-intent-panel" style="display:none;">
                        <input type="text" id="zywrap-usecase-search" placeholder="<?php echo esc_attr(__('Search full library...', 'js-support-ticket')); ?>" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                        <div style="display:flex; flex-direction:column; gap:5px;">
                            <?php foreach($use_cases_map as $code => $uc): ?>
                                <div class="zywrap-action-card list-item" data-code="<?php echo esc_attr($code); ?>" data-title="<?php echo esc_attr($uc->name); ?>" style="padding:10px; border-bottom:1px solid #f1f5f9; cursor:pointer;">
                                    <h4 style="margin:0; color:#334155; font-size:13px;"><?php echo esc_html($uc->name); ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <input type="hidden" id="zywrap-active-wrapper" value="">
                </div>
            </div>

            <div style="flex:1; display:flex; flex-direction:column; background:#f8fafc; overflow:hidden;">
                
                <div style="padding:15px 25px; background:#fff; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span class="dashicons dashicons-arrow-right-alt2" style="color:#94a3b8;"></span>
                        <h2 id="workspace-action-title" style="margin:0; font-size:16px; color:#0f172a; font-weight:600;"><?php echo esc_html(__('Ticket Thread Reply Composer', 'js-support-ticket')); ?></h2>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <select id="zywrap-model-select" style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px; background:#f8fafc; box-shadow:none;">
                            <option value=""><?php echo esc_html(__('Model: Default (Auto)', 'js-support-ticket')); ?></option>
                            <?php if (!empty($dynamic_models)) : foreach ($dynamic_models as $model) : ?>
                                <option value="<?php echo esc_attr($model->code); ?>" <?php selected($global_default_model, $model->code); ?>><?php echo esc_html($model->name); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <select id="zywrap-tone-select" style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px; background:#f8fafc; box-shadow:none;">
                            <option value=""><?php echo esc_html(__('Tone: Default', 'js-support-ticket')); ?></option>
                            <?php if (!empty($dynamic_tones)) : foreach ($dynamic_tones as $tone) : ?>
                                <option value="<?php echo esc_attr($tone->code); ?>"><?php echo esc_html(__('Tone:', 'js-support-ticket') . ' ' . $tone->name); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div style="flex-grow:1; padding:25px; overflow-y:auto; display:flex; flex-direction:column; gap:20px;">
                    
                    <div id="zywrap-dynamic-playground">
                        <div style="text-align:center; padding:30px; color:#64748b; background:#f1f5f9; border-radius:8px; border:1px dashed #cbd5e1;">
                            <span class="dashicons dashicons-arrow-left-alt" style="font-size:24px; width:24px; height:24px; opacity:0.5; margin-bottom:10px;"></span><br>
                            <?php echo esc_html(__('Select a Support Intent from the left menu to load variables.', 'js-support-ticket')); ?>
                        </div>
                    </div>

                    <div id="zywrap-extra-instructions-container" style="display:none; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                        <label style="display:block; font-weight:600; font-size:13px; margin-bottom:6px; color:#0f172a;">
                            <?php echo esc_html(__('Key Points to Include', 'js-support-ticket')); ?> <span style="font-weight:normal; font-size:11px; color:#64748b;"><?php echo esc_html(__('(Optional)', 'js-support-ticket')); ?></span>
                        </label>
                        <p style="margin:0 0 10px 0; font-size:11px; color:#64748b;"><?php echo esc_html(__('Add any specific facts, business decisions, or links the AI should mention.', 'js-support-ticket')); ?></p>
                        <textarea id="zywrap-extra-instructions" placeholder="<?php echo esc_attr(__('e.g., Approve the refund, offer a 20% discount, or let them know this will be fixed in v4.0...', 'js-support-ticket')); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-family:inherit; font-size:13px; resize:vertical; background:#f8fafc; box-sizing:border-box;" rows="2"></textarea>
                    </div>

                    <div style="display:flex; flex-direction:column; flex-grow:1;">
                        <label style="font-weight:600; display:block; margin-bottom:8px; color:#0f172a; font-size:14px; display:flex; justify-content:space-between;">
                            <?php echo esc_html(__('AI Generated Draft', 'js-support-ticket')); ?>
                            <span id="zywrap-status-text" style="color:#2563eb; font-size:12px; font-weight:500;"></span>
                        </label>
                        <textarea id="zywrap-draft-area" style="flex-grow:1; min-height:150px; width:100%; padding:20px; font-family:monospace; border:1px solid #cbd5e1; border-radius:8px; resize:none; background:#fff; box-shadow:inset 0 1px 3px rgba(0,0,0,0.05); box-sizing:border-box; line-height:1.6; font-size:13px;" readonly placeholder="<?php echo esc_attr(__('AI generated response will appear here...', 'js-support-ticket')); ?>"></textarea>
                    </div>
                </div>

                <div style="padding:15px 25px; background:#fff; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                    <span id="zywrap-spinner" class="spinner" style="float:none; margin:0;"></span>
                    <div style="display:flex; gap:12px;">
                        <button type="button" id="zywrap-generate-btn" class="button button-large" style="padding:0 25px; height:42px; font-size:14px;" disabled>
                            <span class="dashicons dashicons-update-alt" style="margin-top:4px; margin-right:5px;"></span> <?php echo esc_html(__('Generate Draft', 'js-support-ticket')); ?>
                        </button>
                        <button type="button" id="zywrap-insert-btn" class="button button-primary button-large" style="background:#2563eb; border-color:#1d4ed8; padding:0 30px; height:42px; font-size:14px; display:flex; align-items:center; gap:8px;" disabled>
                            <span class="dashicons dashicons-insert"></span> <?php echo esc_html(__('Insert into Editor', 'js-support-ticket')); ?>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        
    </div> </div> 

<style>
.zywrap-action-card:hover { border-color: #93c5fd !important; background: #f0fdf4 !important; }
.zywrap-action-card.list-item:hover { background: #f8fafc !important; }
.zywrap-action-card.selected { border-color: #2563eb !important; background: #eff6ff !important; box-shadow: 0 0 0 2px rgba(37,99,235,0.2); }
#jsst-zywrap-close:hover { background: rgba(255,255,255,0.25) !important; }
.zywrap-dyn-var:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 1px #2563eb; }
</style>


<script>
// ==========================================
// ZYWRAP MASTER FORMATTER UTILITY (GLOBAL)
// ==========================================
window.ZywrapFormatter = {
    
    cleanRawMarkdown: function(text) {
        if (!text) return '';
        return text
            .replace(/([^\s\n])\s+(#{1,6}\s+[A-Z])/g, '$1\n\n$2')
            .replace(/([^\s\n])\s+(-\s+[A-Z0-9])/g, '$1\n$2');
    },

    toHTML: function(markdownText) {
        var preppedText = this.cleanRawMarkdown(markdownText);
        if (typeof marked !== 'undefined') {
            return marked.parse(preppedText, { breaks: true, gfm: true });
        }
        return preppedText;
    },

    toPlainText: function(markdownText) {
        var html = this.toHTML(markdownText);
        html = html.replace(/<br\s*[\/]?>/gi, '\n');
        html = html.replace(/<\/p>/gi, '\n\n');
        html = html.replace(/<h[1-6]>(.*?)<\/h[1-6]>/gi, '\n=== $1 ===\n\n'); 
        html = html.replace(/<li>(.*?)<\/li>/gi, ' • $1\n');                 
        html = html.replace(/<\/ul>/gi, '\n');
        html = html.replace(/<\/ol>/gi, '\n');

        var temp = document.createElement('div');
        temp.innerHTML = html;
        var plainText = temp.innerText || temp.textContent;
        return plainText.trim().replace(/\n{3,}/g, '\n\n');
    },

    // NEW: Transforms raw JSON data into beautiful UI Data Cards
    formatJSON: function(jsonObj) {
        if (!jsonObj || typeof jsonObj !== 'object') return '';
        var html = '<div style="display:flex; flex-direction:column; gap:10px; font-size:13px; font-family:-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
        var hasData = false;

        for (var key in jsonObj) {
            var val = jsonObj[key];
            // Skip nulls, empty strings, and empty arrays completely
            if (val === null || val === '' || (Array.isArray(val) && val.length === 0)) continue;
            
            // Skip objects where all sub-properties are null
            if (typeof val === 'object' && !Array.isArray(val)) {
                var hasSubData = false;
                for (var sk in val) { if (val[sk] !== null && val[sk] !== '' && (!Array.isArray(val[sk]) || val[sk].length > 0)) hasSubData = true; }
                if (!hasSubData) continue;
            }

            hasData = true;
            var cleanKey = key.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });

            html += '<div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">';
            html += '<div style="background:#f8fafc; padding:8px 14px; border-bottom:1px solid #e2e8f0; font-weight:600; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">' + cleanKey + '</div>';
            html += '<div style="padding:12px 14px; color:#0f172a; line-height:1.5;">';

            if (Array.isArray(val)) {
                html += '<ul style="margin:0 0 0 16px; padding:0;">';
                val.forEach(function(item) {
                    if (typeof item === 'object') {
                        for(var subK in item) {
                            var subVal = item[subK];
                            if(Array.isArray(subVal)) {
                                subVal.forEach(function(sv) { html += '<li style="margin-bottom:6px;">' + sv + '</li>'; });
                            } else if (subVal !== null && subVal !== '') {
                                html += '<li style="margin-bottom:6px;">' + subVal + '</li>';
                            }
                        }
                    } else {
                        html += '<li style="margin-bottom:6px;">' + item + '</li>';
                    }
                });
                html += '</ul>';
            } else if (typeof val === 'object') {
                html += '<div style="display:grid; grid-template-columns: 1fr; gap:10px;">';
                for (var subKey in val) {
                    var subVal = val[subKey];
                    if (subVal === null || subVal === '' || (Array.isArray(subVal) && subVal.length===0)) continue;
                    var cleanSubKey = subKey.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                    var displayVal = Array.isArray(subVal) ? subVal.join(', ') : subVal;
                    html += '<div style="display:flex; flex-direction:column;"><span style="font-size:11px; color:#64748b; font-weight:600; margin-bottom:2px;">' + cleanSubKey + '</span><span>' + displayVal + '</span></div>';
                }
                html += '</div>';
            } else {
                html += val;
            }
            html += '</div></div>';
        }
        html += '</div>';

        if (!hasData) return '<div style="color:#64748b; font-style:italic; padding:15px; background:#f8fafc; border-radius:8px; border:1px dashed #cbd5e1; text-align:center;">No specific entities were found in this text.</div>';
        return html;
    },

    smartInsert: function(editorId, markdownText) {
        var isVisualActive = typeof tinyMCE !== 'undefined' && 
                             tinyMCE.get(editorId) && 
                             !jQuery("#wp-" + editorId + "-wrap").hasClass("html-active");

        if (isVisualActive) {
            tinyMCE.get(editorId).execCommand('mceInsertContent', false, this.toHTML(markdownText));
        } else {
            var el = jQuery('#' + editorId);
            var currentContent = el.val();
            el.val(currentContent + (currentContent ? '\n\n' : '') + this.toPlainText(markdownText));
        }
    }
};


jQuery(document).ready(function($) {
    
    var currentTicketId = $('input[name="jssupportticketid"]').val() || new URLSearchParams(window.location.search).get('jssupportticketid');

    // JS Translation Strings (Passed safely from PHP to JS)
    var zy_i18n = {
        unconfigured_alert: "<?php echo esc_js(__('Zywrap AI Co-Pilot is not configured!\\n\\nPlease navigate to Zywrap AI Settings to connect your API key and unlock AI Auto-Replies.', 'js-support-ticket')); ?>",
        loading_context: "<?php echo esc_js(__('Loading context securely from database...', 'js-support-ticket')); ?>",
        error_prefix: "<?php echo esc_js(__('Error:', 'js-support-ticket')); ?>",
        no_variations: "<?php echo esc_js(__('No wrapper variations found. Please sync data.', 'js-support-ticket')); ?>",
        optional_label: "<?php echo esc_js(__('(Optional)', 'js-support-ticket')); ?>",
        ready_generate: "<?php echo esc_js(__('No extra context variables required. Ready to generate.', 'js-support-ticket')); ?>",
        drafting: "<?php echo esc_js(__('Drafting response...', 'js-support-ticket')); ?>",
        sys_error: "<?php echo esc_js(__('System Error parsing response. Check console.', 'js-support-ticket')); ?>",
        unlock_title: "<?php echo esc_js(__('Unlock AI Features', 'js-support-ticket')); ?>",
        unlock_desc: "<?php echo esc_js(__('Connect your Zywrap API key in settings to unlock 1-click summaries, translations, and data extraction.', 'js-support-ticket')); ?>",
        configure_link: "<?php echo esc_js(__('Configure Zywrap AI &rarr;', 'js-support-ticket')); ?>",
        translate_to: "<?php echo esc_js(__('Translate to', 'js-support-ticket')); ?>",
        extract_details: "<?php echo esc_js(__('Extract Details', 'js-support-ticket')); ?>",
        ai_executing: "<?php echo esc_js(__('AI is executing:', 'js-support-ticket')); ?>",
        ai_insight: "<?php echo esc_js(__('AI Insight:', 'js-support-ticket')); ?>"
    };

    function openZywrapModal(targetTab) {
        $('#jsst-zywrap-backdrop, #jsst-zywrap-modal').fadeIn(200);
        if (targetTab) {
            setTimeout(function() { $('.zywrap-intent-tab[data-target="' + targetTab + '"]').trigger('click'); }, 50);
        } else if ($('#zywrap-active-wrapper').val() === '') {
            $('.zywrap-action-card[data-code="ticket_thread_reply_composer"]').trigger('click');
        }
    }

    $('#jsst-open-zywrap-modal').off('click').on('click', function(e) {
        e.preventDefault();
        openZywrapModal(null);
    });

    $(document).off('click', '.zywrap-open-tab-btn').on('click', '.zywrap-open-tab-btn', function(e) {
        e.preventDefault();
        if ($(this).data('active') != '1') { alert(zy_i18n.unconfigured_alert); return; }
        openZywrapModal($(this).data('tab'));
    });

    $('.zywrap-intent-tab').on('click', function(e) {
        e.preventDefault(); 
        $('.zywrap-intent-tab').css({'background':'#fff', 'color':'#475569', 'border-color':'#cbd5e1'});
        $(this).css({'background':'#eff6ff', 'color':'#1d4ed8', 'border-color':'#2563eb'});
        $('.zywrap-intent-panel').hide();
        var target = $(this).data('target');
        if(target === 'compose' || target === 'more') { $('#zywrap-intent-' + target).show(); } 
        else { $('#zywrap-intent-' + target).css('display', 'flex'); }
    });

    $('#zywrap-usecase-search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#zywrap-intent-more .zywrap-action-card').filter(function() {
            $(this).toggle($(this).find('h4').text().toLowerCase().indexOf(value) > -1)
        });
    });

    $('.zywrap-action-card').on('click', function() {
        var useCaseCode = $(this).data('code');
        var cardTitle = $(this).data('title');
        
        $('.zywrap-action-card').removeClass('selected').css('border-color', '#cbd5e1');
        $(this).addClass('selected').css('border-color', '#2563eb');
        
        $('#workspace-action-title').text(cardTitle);
        $('#zywrap-generate-btn').prop('disabled', true);
        $('#zywrap-dynamic-playground').html('<div style="text-align:center; padding:30px; color:#64748b; background:#f1f5f9; border-radius:8px; border:1px dashed #cbd5e1;"><span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> ' + zy_i18n.loading_context + '</div>');

        $.post(ajaxurl, {
            action: 'jsticket_ajax', jstmod: 'zywrap', task: 'ajaxGetWrappers',
            use_case_code: useCaseCode, ticket_id: currentTicketId, _wpnonce: '<?php echo esc_js($zywrap_nonce); ?>'
        }, function(response) {
            var res = typeof response === 'object' ? response : JSON.parse(response);

            if (!res.success) {
                $('#zywrap-dynamic-playground').html('<div style="color:red; padding:15px; background:#fee2e2; border-radius:6px;">' + zy_i18n.error_prefix + ' ' + res.data.message + '</div>');
                return;
            }

            var apiData = res.data; 
            if(apiData.wrappers && apiData.wrappers.length > 0) {
                $('#zywrap-active-wrapper').val(apiData.wrappers[0].code); 
                $('#zywrap-generate-btn').prop('disabled', false);
            } else {
                $('#zywrap-dynamic-playground').html('<div style="color:red; padding:15px; background:#fee2e2; border-radius:6px;">' + zy_i18n.no_variations + '</div>');
                return;
            }

            var html = '';
            var dbContext = apiData.ticketData; 

            function toTitleCase(str) { return str.replace(/([A-Z])/g, " $1").replace(/^./, function(str){ return str.toUpperCase(); }); }
            function getSmartDefaultValue(key, label) {
                var k = key.toLowerCase(), l = label.toLowerCase();
                if (k.indexOf('case') !== -1 || k.indexOf('ticketid') !== -1 || l.indexOf('subject') !== -1) return dbContext.subject;
                else if (l.indexOf('latest') !== -1 || k.indexOf('latest') !== -1 || l.indexOf('last message') !== -1) return dbContext.latestCustomerMsg;
                else if (l.indexOf('thread') !== -1 || l.indexOf('history') !== -1 || k.indexOf('thread') !== -1 || l.indexOf('summary') !== -1 || l.indexOf('experience') !== -1 || l.indexOf('issue') !== -1 || l.indexOf('message') !== -1) return dbContext.fullThread; 
                return '';
            }

            if (apiData.schema && (apiData.schema.req || apiData.schema.opt)) {
                html += '<div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,0.05);">';
                if (apiData.schema.req) {
                    $.each(apiData.schema.req, function(key, v) {
                        var label = toTitleCase(key), placeholder = v.d ? v.d : '', defaultVal = getSmartDefaultValue(key, label); 
                        html += '<div style="margin-bottom:15px;"><label style="display:block; font-weight:600; font-size:13px; margin-bottom:6px; color:#334155;">' + label + ' <span style="color:#ef4444;">*</span></label><textarea class="zywrap-dyn-var" data-key="' + key + '" placeholder="' + placeholder + '" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-family:inherit; font-size:13px; resize:vertical; background:#f8fafc; box-sizing:border-box;" rows="4">' + defaultVal + '</textarea></div>';
                    });
                }
                if (apiData.schema.opt) {
                    html += '<hr style="border:0; border-top:1px dashed #cbd5e1; margin:20px 0;">';
                    $.each(apiData.schema.opt, function(key, v) {
                        var label = toTitleCase(key), placeholder = v.d ? v.d : '', defaultVal = getSmartDefaultValue(key, label); 
                        html += '<div style="margin-bottom:15px;"><label style="display:block; font-weight:600; font-size:13px; margin-bottom:6px; color:#64748b;">' + label + ' <span style="font-weight:normal; font-size:11px;">' + zy_i18n.optional_label + '</span></label><textarea class="zywrap-dyn-var" data-key="' + key + '" placeholder="' + placeholder + '" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-family:inherit; font-size:13px; resize:vertical; background:#fff; box-sizing:border-box;" rows="2">' + defaultVal + '</textarea></div>';
                    });
                }
                html += '</div>';
            }
            
            if(html === '') { html = '<div style="font-size:13px; color:#64748b; padding:15px; background:#fff; border:1px solid #e2e8f0; border-radius:8px;">' + zy_i18n.ready_generate + '</div>'; }
            $('#zywrap-dynamic-playground').html(html);
            $('#zywrap-extra-instructions-container').show();
            $('#zywrap-extra-instructions').val(''); 
        });
    });

    $('#zywrap-generate-btn').on('click', function() {
        var btn = $(this), wrapperCode = $('#zywrap-active-wrapper').val();
        if(!wrapperCode) return;

        var dynamicVars = {}, structuredTextParts = []; 
        $('.zywrap-dyn-var').each(function() {
            var k = $(this).data('key'), v = $(this).val().trim(); 
            if (v) { dynamicVars[k] = v; structuredTextParts.push(k + ': ' + v); }
        });

        var finalPrompt = $('#zywrap-extra-instructions').length ? $('#zywrap-extra-instructions').val().trim() : '';
        var structuredText = structuredTextParts.join('\n');
        if (finalPrompt && structuredText) finalPrompt += '\n\n' + structuredText;
        else if (structuredText) finalPrompt = structuredText;

        $('#zywrap-spinner').addClass('is-active');
        $('#zywrap-status-text').text(zy_i18n.drafting);
        btn.prop('disabled', true);
        $('#zywrap-draft-area').val('');

        $.post(ajaxurl, {
            action: 'jsticket_ajax', jstmod: 'zywrap', task: 'generateReply',
            tone: $('#zywrap-tone-select').val(), model_code: $('#zywrap-model-select').val(),
            wrapper_code: wrapperCode, prompt: finalPrompt, variables: JSON.stringify(dynamicVars),
            _wpnonce: '<?php echo esc_js($zywrap_nonce); ?>' 
        }, function(response) {
            $('#zywrap-spinner').removeClass('is-active');
            $('#zywrap-status-text').text('');
            btn.prop('disabled', false);
            
            try {
                var data;
                if (typeof response === 'object') { data = response; } 
                else {
                    var cleanResponse = response, jsonStart = response.indexOf('{'), jsonEnd = response.lastIndexOf('}');
                    if (jsonStart !== -1 && jsonEnd !== -1) { cleanResponse = response.substring(jsonStart, jsonEnd + 1); }
                    data = JSON.parse(cleanResponse);
                }
                
                if(data.success) {
                    $('#zywrap-draft-area').val(data.data.output);
                    $('#zywrap-insert-btn').prop('disabled', false);
                } else {
                    $('#zywrap-draft-area').val(zy_i18n.error_prefix + " " + (data.data && data.data.message ? data.data.message : data.message));
                }
            } catch(e) {
                $('#zywrap-draft-area').val(zy_i18n.sys_error);
            }
        });
    });

    $('#zywrap-insert-btn').on('click', function() {
        var aiText = $('#zywrap-draft-area').val();
        
        // Pass the text to our new Master Utility
        ZywrapFormatter.smartInsert('jsticket_message', aiText);
        
        $('#jsst-zywrap-close').trigger('click');
    });

    $('#jsst-zywrap-close, #jsst-zywrap-backdrop').on('click', function(e) { 
        e.preventDefault(); 
        $('#jsst-zywrap-backdrop, #jsst-zywrap-modal').fadeOut(200); 
    });

    $(document).off('click', '.zywrap-inline-ai-btn').on('click', '.zywrap-inline-ai-btn', function(e) {
        e.preventDefault();
        var btn = $(this), wrapperCode = btn.data('wrapper'), actionName = btn.text().trim(); 
        
        var specificMessageText = btn.closest('.zywrap-inline-actions').prev('.note-msg, .js-tkt-det-tkt-msg').text().trim();
        if(!specificMessageText) specificMessageText = btn.closest('.js-ticket-thread, .ticket-details').find('.note-msg, .js-tkt-det-tkt-msg').text().trim();

        var resultBox = btn.closest('.zywrap-inline-actions').next('.zywrap-inline-result');
        if (btn.data('active') != '1') {
            var upsellHtml = '<div style="color:#1e3a8a; background:#eff6ff; border:1px solid #bfdbfe; padding:12px 15px; border-radius:6px; display:flex; align-items:start; gap:10px;">';
            upsellHtml += '<span class="dashicons dashicons-lock" style="color:#3b82f6; margin-top:2px;"></span>';
            upsellHtml += '<div><strong>' + zy_i18n.unlock_title + '</strong><br>' + zy_i18n.unlock_desc + '<br><a href="?page=zywrap&jstlay=zywrap_settings" style="display:inline-block; margin-top:8px; font-weight:600; text-decoration:none;">' + zy_i18n.configure_link + '</a></div></div>';
            resultBox.html(upsellHtml).slideDown(200);
            return; 
        }

        var inlineVars = { 'ticketThread': specificMessageText, 'message': specificMessageText, 'text': specificMessageText };
        var finalPrompt = specificMessageText, reqLanguage = ''; 

        if (wrapperCode === 'tl_supp_tick_tran_loca_926d_base') {
            var targetLang = btn.data('lang') || 'English'; 
            reqLanguage = targetLang; 
            actionName = zy_i18n.translate_to + " " + targetLang; 
        }

        if (wrapperCode === 'ee_support_ticket_detail_extraction_base') actionName = zy_i18n.extract_details;

        btn.prop('disabled', true).css('opacity', '0.5');
        resultBox.html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0; width:14px; height:14px;"></span> ' + zy_i18n.ai_executing + ' ' + actionName + '...').slideDown(200);

        // 1. Fetch the Global Default Model from PHP
        var defaultModel = '<?php echo esc_js($global_default_model); ?>';
        
        // 2. Build the base payload
        var ajaxPayload = {
            action: 'jsticket_ajax', 
            jstmod: 'zywrap', 
            task: 'generateReply', 
            tone: 'professional',
            language: reqLanguage, 
            wrapper_code: wrapperCode, 
            prompt: finalPrompt, 
            variables: JSON.stringify(inlineVars), 
            _wpnonce: '<?php echo esc_js($zywrap_nonce); ?>' 
        };

        // 3. Only attach the model_code if a default model exists
        if (defaultModel !== '') {
            ajaxPayload.model_code = defaultModel;
        }

        // 4. Execute the API Call
        $.post(ajaxurl, ajaxPayload, function(response) {
            btn.prop('disabled', false).css('opacity', '1');
            try {
                var cleanResponse = response;
                if (typeof response === 'string') {
                    var jsonStart = response.indexOf('{'), jsonEnd = response.lastIndexOf('}');
                    if (jsonStart !== -1 && jsonEnd !== -1) { cleanResponse = response.substring(jsonStart, jsonEnd + 1); }
                }
                var data = typeof cleanResponse === 'object' ? cleanResponse : JSON.parse(cleanResponse);
                
                if(data.success) {
                    var headerHtml = '<div style="font-weight:600; margin-bottom:15px; border-bottom:1px solid #cbd5e1; padding-bottom:8px; color:#1e40af; display:flex; align-items:center; gap:6px;">';
                    headerHtml += '<span class="dashicons dashicons-superhero-alt" style="font-size:18px; width:18px; height:18px;"></span>';
                    headerHtml += zy_i18n.ai_insight + ' ' + actionName + '</div>'; 
                    
                    var rawOutput = data.data.output;
                    var formattedOutput = '';

                    // SMART DETECTION: Check if the AI returned JSON data (e.g., from Extract Details)
                    try {
                        // Strip out markdown code blocks in case the AI wrapped the JSON
                        var possibleJsonString = rawOutput.replace(/^```json/i, '').replace(/```$/i, '').trim();
                        var parsedJson = JSON.parse(possibleJsonString);
                        
                        // If it parses successfully as an object, build the beautiful Data Cards
                        formattedOutput = '<div class="zywrap-json-content">' + ZywrapFormatter.formatJSON(parsedJson) + '</div>';
                    } catch(e) {
                        // Not JSON! It is standard text/markdown (e.g., from Summarize or Translate)
                        formattedOutput = '<div class="zywrap-markdown-content">' + ZywrapFormatter.toHTML(rawOutput) + '</div>';
                    }
                    
                    resultBox.html(headerHtml + formattedOutput);
                } else {
                    resultBox.html('<span style="color:#ef4444;">' + zy_i18n.error_prefix + ' ' + (data.data.message || data.message) + '</span>');
                }
            } catch(e) {
                resultBox.html('<span style="color:#ef4444;">' + zy_i18n.sys_error + '</span>');
            }
        });
    });
});
</script>