<?php 
if (!defined('ABSPATH')) die('Restricted Access'); 

// 1. NONCE & MODEL LOADING
$js_ticket_zywrap_nonce = wp_create_nonce('zywrap_ajax_action');
$js_ticket_zywrap_model = JSSTincluder::getJSModel('zywrap');

// 2. ENQUEUE SCRIPTS
wp_enqueue_script('zywrap-marked-js', JSST_PLUGIN_URL . 'includes/js/marked.min.js', array('jquery'), jssupportticket::$_config['productversion'], true);

$js_ticket_global_default_model = get_option('jsst_zywrap_default_model', '');
$raw_use_cases = $js_ticket_zywrap_model->getSupportUseCases();
$dynamic_tones = $js_ticket_zywrap_model->getDynamicTones();
$dynamic_models = $js_ticket_zywrap_model->getDynamicModels();

$use_cases_map = array();
if (!empty($raw_use_cases)) {
    foreach ($raw_use_cases as $uc) {
        $use_cases_map[$uc->code] = $uc;
    }
}

// Intent Groupings
$intents = array(
    'compose'  => array('ticket_thread_reply_composer', 'internal_support_note_generator', 'investigation_status_update', 'troubleshooting_first_response', 'wordpress_plugin_conflict_first_response', 'white_screen_fatal_error_initial_response', 'license_key_activation_help'),
    'ask_info' => array('debug_log_request_reply', 'reproduction_steps_request', 'technical_requirement_request', 'javascript_console_error_request', 'safe_temporary_admin_access_request', 'staging_site_request_before_investigation'),
    'escalate' => array('customer_retest_request', 'please_test_this_build_reply', 'technical_escalation_handoff', 'escalated_bug_response', 'waiting_on_engineering_update')
);
?>

<div id="jsst-zywrap-backdrop" class="js-ticket-zywrap-backdrop" style="display:none;"></div>

<div id="jsst-zywrap-modal" class="js-ticket-zywrap-modal" style="display:none;">
    <div class="js-ticket-zywrap-modal-inner">
        <div class="js-ticket-zywrap-modal-header">
            <div>
                <h3 class="js-ticket-zywrap-modal-title">
                    <span class="dashicons dashicons-superhero-alt" aria-hidden="true"></span> 
                    <?php echo esc_html(__('Zywrap Co-Pilot', 'js-support-ticket')); ?>
                </h3>
                <div class="js-ticket-zywrap-modal-subtitle"><?php echo esc_html(__('Call AI by Code. Zero Prompt Engineering.', 'js-support-ticket')); ?></div>
            </div>
            <a href="#" id="jsst-zywrap-close" class="js-ticket-zywrap-modal-close">
                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
            </a>
        </div>

        <div class="js-ticket-zywrap-modal-body">
            <div class="js-ticket-zywrap-modal-sidebar">
                <div class="js-ticket-zywrap-intent-header">
                    <label class="js-ticket-zywrap-intent-label"><?php echo esc_html(__('Support Intent', 'js-support-ticket')); ?></label>
                    <div class="js-ticket-zywrap-intent-tabs">
                        <button type="button" class="zywrap-intent-tab active" data-target="compose"><?php echo esc_html(__('Compose', 'js-support-ticket')); ?></button>
                        <button type="button" class="zywrap-intent-tab" data-target="ask_info"><?php echo esc_html(__('Ask Info', 'js-support-ticket')); ?></button>
                        <button type="button" class="zywrap-intent-tab" data-target="escalate"><?php echo esc_html(__('Escalate', 'js-support-ticket')); ?></button>
                        <button type="button" class="zywrap-intent-tab" data-target="more"><?php echo esc_html(__('Library', 'js-support-ticket')); ?></button>
                    </div>
                </div>

                <div class="js-ticket-zywrap-action-list">
                    <div id="zywrap-intent-compose" class="zywrap-intent-panel">
                        <?php if (isset($use_cases_map['ticket_thread_reply_composer'])): ?>
                            <div class="zywrap-action-card featured selected js-ticket-zywrap-card-featured" data-code="ticket_thread_reply_composer" data-title="<?php echo esc_attr(__('Ticket Thread Reply Composer', 'js-support-ticket')); ?>">
                                <div class="js-ticket-zywrap-badge-default"><?php echo esc_html(__('Default', 'js-support-ticket')); ?></div>
                                <h4 class="js-ticket-zywrap-action-title"><span class="dashicons dashicons-edit" aria-hidden="true"></span> <?php echo esc_html(__('Ticket Thread Reply Composer', 'js-support-ticket')); ?></h4>
                                <p class="js-ticket-zywrap-action-desc"><?php echo esc_html(__('Reads the thread and drafts the best response.', 'js-support-ticket')); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="js-ticket-zywrap-flex-column-gap">
                            <?php foreach($intents['compose'] as $code): if($code === 'ticket_thread_reply_composer' || !isset($use_cases_map[$code])) continue; ?>
                                <div class="zywrap-action-card list-item js-ticket-zywrap-card-list" data-code="<?php echo esc_attr($code); ?>" data-title="<?php echo esc_attr($use_cases_map[$code]->name); ?>">
                                    <h4 class="js-ticket-zywrap-action-title-small"><?php echo esc_html($use_cases_map[$code]->name); ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div id="zywrap-intent-ask_info" class="zywrap-intent-panel js-ticket-zywrap-hide js-ticket-zywrap-flex-column-gap">
                        <?php foreach($intents['ask_info'] as $code): if(!isset($use_cases_map[$code])) continue; ?>
                            <div class="zywrap-action-card list-item js-ticket-zywrap-card-list" data-code="<?php echo esc_attr($code); ?>" data-title="<?php echo esc_attr($use_cases_map[$code]->name); ?>">
                                <h4 class="js-ticket-zywrap-action-title-small"><?php echo esc_html($use_cases_map[$code]->name); ?></h4>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="zywrap-intent-escalate" class="zywrap-intent-panel js-ticket-zywrap-hide js-ticket-zywrap-flex-column-gap">
                        <?php foreach($intents['escalate'] as $code): if(!isset($use_cases_map[$code])) continue; ?>
                            <div class="zywrap-action-card list-item js-ticket-zywrap-card-list" data-code="<?php echo esc_attr($code); ?>" data-title="<?php echo esc_attr($use_cases_map[$code]->name); ?>">
                                <h4 class="js-ticket-zywrap-action-title-small"><?php echo esc_html($use_cases_map[$code]->name); ?></h4>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="zywrap-intent-more" class="zywrap-intent-panel js-ticket-zywrap-hide">
                        <input type="text" id="zywrap-usecase-search" class="js-ticket-zywrap-search-input" placeholder="<?php echo esc_attr(__('Search full library...', 'js-support-ticket')); ?>">
                        <div class="js-ticket-zywrap-flex-column-gap-small">
                            <?php foreach($use_cases_map as $code => $uc): ?>
                                <div class="zywrap-action-card list-item js-ticket-zywrap-card-compact" data-code="<?php echo esc_attr($code); ?>" data-title="<?php echo esc_attr($uc->name); ?>">
                                    <h4 class="js-ticket-zywrap-action-title-small"><?php echo esc_html($uc->name); ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="zywrap-active-wrapper" value="">
            </div>

            <div class="js-ticket-zywrap-modal-workspace">
                <div class="js-ticket-zywrap-workspace-header">
                    <div class="js-ticket-zywrap-flex-align">
                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                        <h2 id="workspace-action-title" class="js-ticket-zywrap-workspace-h2"><?php echo esc_html(__('Ticket Thread Reply Composer', 'js-support-ticket')); ?></h2>
                    </div>
                    <div class="js-ticket-zywrap-flex-gap-10">
                        <select id="zywrap-model-select" class="js-ticket-zywrap-modal-select">
                            <option value=""><?php echo esc_html(__('Model: Default (Auto)', 'js-support-ticket')); ?></option>
                            <?php if (!empty($dynamic_models)) : foreach ($dynamic_models as $model) : ?>
                                <option value="<?php echo esc_attr($model->code); ?>" <?php selected($js_ticket_global_default_model, $model->code); ?>><?php echo esc_html($model->name); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <select id="zywrap-tone-select" class="js-ticket-zywrap-modal-select">
                            <option value=""><?php echo esc_html(__('Tone: Default', 'js-support-ticket')); ?></option>
                            <?php if (!empty($dynamic_tones)) : foreach ($dynamic_tones as $tone) : ?>
                                <option value="<?php echo esc_attr($tone->code); ?>"><?php echo esc_html__('Tone', 'js-support-ticket'); ?>: <?php echo esc_html($tone->name); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="js-ticket-zywrap-workspace-content">
                    <div id="zywrap-dynamic-playground">
                        <div class="js-ticket-zywrap-empty-prompt">
                            <span class="dashicons dashicons-arrow-left-alt" aria-hidden="true"></span><br>
                            <?php echo esc_html(__('Select a Support Intent from the left menu to load variables.', 'js-support-ticket')); ?>
                        </div>
                    </div>

                    <div id="zywrap-extra-instructions-container" class="js-ticket-zywrap-hide js-ticket-zywrap-instructions-card">
                        <label class="js-ticket-zywrap-label-bold">
                            <?php echo esc_html(__('Key Points to Include', 'js-support-ticket')); ?> 
                            <span class="js-ticket-zywrap-label-opt"><?php echo esc_html(__('(Optional)', 'js-support-ticket')); ?></span>
                        </label>
                        <p class="js-ticket-zywrap-p-hint"><?php echo esc_html(__('Add any specific facts, business decisions, or links the AI should mention.', 'js-support-ticket')); ?></p>
                        <textarea id="zywrap-extra-instructions" class="js-ticket-zywrap-textarea-small" rows="2" placeholder="<?php echo esc_attr(__('e.g., Approve the refund, offer a 20% discount, or let them know this will be fixed in v4.0...', 'js-support-ticket')); ?>"></textarea>
                    </div>

                    <div class="js-ticket-zywrap-draft-container">
                        <label class="js-ticket-zywrap-label-bold js-ticket-zywrap-flex-between">
                            <?php echo esc_html(__('AI Generated Draft', 'js-support-ticket')); ?>
                            <span id="zywrap-status-text" class="js-ticket-zywrap-text-status"></span>
                        </label>
                        <textarea id="zywrap-draft-area" class="js-ticket-zywrap-textarea-draft" readonly placeholder="<?php echo esc_attr(__('AI generated response will appear here...', 'js-support-ticket')); ?>"></textarea>
                    </div>
                </div>

                <div class="js-ticket-zywrap-modal-footer">
                    <span id="zywrap-spinner" class="spinner"></span>
                    <div class="js-ticket-zywrap-flex-gap-12">
                        <button type="button" id="zywrap-generate-btn" class="button button-large js-ticket-zywrap-btn-gen" disabled>
                            <span class="dashicons dashicons-update-alt" aria-hidden="true"></span> <?php echo esc_html(__('Generate Draft', 'js-support-ticket')); ?>
                        </button>
                        <button type="button" id="zywrap-insert-btn" class="button button-primary button-large js-ticket-zywrap-btn-insert" disabled>
                            <span class="dashicons dashicons-insert" aria-hidden="true"></span> <?php echo esc_html(__('Insert into Editor', 'js-support-ticket')); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$jsst_jssupportticket_js = "
// ==========================================
// ZYWRAP MASTER FORMATTER UTILITY (GLOBAL)
// ==========================================
window.ZywrapFormatter = {
    cleanRawMarkdown: function(text) {
        if (!text) return '';
        return text
            .replace(/([^\\s\\n])\\s+(#{1,6}\\s+[A-Z])/g, '\$1\\n\\n\$2')
            .replace(/([^\\s\\n])\\s+(-\\s+[A-Z0-9])/g, '\$1\\n\$2');
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
        html = html.replace(/<br\\s*[\\/ ]?>/gi, '\\n');
        html = html.replace(/<\\/p>/gi, '\\n\\n');
        html = html.replace(/<h[1-6]>(.*?)<\\/h[1-6]>/gi, '\\n=== \$1 ===\\n\\n'); 
        html = html.replace(/<li>(.*?)<\\/li>/gi, ' • \$1\\n');                  
        html = html.replace(/<\\/ul>/gi, '\\n');
        html = html.replace(/<\\/ol>/gi, '\\n');

        var temp = document.createElement('div');
        temp.innerHTML = html;
        var plainText = temp.innerText || temp.textContent;
        return plainText.trim().replace(/\\n{3,}/g, '\\n\\n');
    },

    formatJSON: function(jsonObj) {
        if (!jsonObj || typeof jsonObj !== 'object') return '';
        var html = '<div class=\"js-ticket-zywrap-json-container\">';
        var hasData = false;

        for (var key in jsonObj) {
            var val = jsonObj[key];
            if (val === null || val === '' || (Array.isArray(val) && val.length === 0)) continue;
            
            if (typeof val === 'object' && !Array.isArray(val)) {
                var hasSubData = false;
                for (var sk in val) { if (val[sk] !== null && val[sk] !== '' && (!Array.isArray(val[sk]) || val[sk].length > 0)) hasSubData = true; }
                if (!hasSubData) continue;
            }

            hasData = true;
            var cleanKey = key.replace(/_/g, ' ').replace(/\\b\\w/g, function(l){ return l.toUpperCase(); });

            html += '<div class=\"js-ticket-zywrap-json-card\">';
            html += '<div class=\"js-ticket-zywrap-json-header\">' + cleanKey + '</div>';
            html += '<div class=\"js-ticket-zywrap-json-body\">';

            if (Array.isArray(val)) {
                html += '<ul class=\"js-ticket-zywrap-json-list\">';
                val.forEach(function(item) {
                    if (typeof item === 'object') {
                        for(var subK in item) {
                            var subVal = item[subK];
                            if(Array.isArray(subVal)) {
                                subVal.forEach(function(sv) { html += '<li>' + sv + '</li>'; });
                            } else if (subVal !== null && subVal !== '') {
                                html += '<li>' + subVal + '</li>';
                            }
                        }
                    } else {
                        html += '<li>' + item + '</li>';
                    }
                });
                html += '</ul>';
            } else if (typeof val === 'object') {
                html += '<div class=\"js-ticket-zywrap-json-grid\">';
                for (var subKey in val) {
                    var subVal = val[subKey];
                    if (subVal === null || subVal === '' || (Array.isArray(subVal) && subVal.length===0)) continue;
                    var cleanSubKey = subKey.replace(/_/g, ' ').replace(/\\b\\w/g, function(l){ return l.toUpperCase(); });
                    var displayVal = Array.isArray(subVal) ? subVal.join(', ') : subVal;
                    html += '<div class=\"js-ticket-zywrap-json-item\"><strong>' + cleanSubKey + ':</strong> ' + displayVal + '</div>';
                }
                html += '</div>';
            } else {
                html += val;
            }
            html += '</div></div>';
        }
        html += '</div>';
        if (!hasData) return '<div class=\"js-ticket-zywrap-empty-json\">' + '" . esc_js(__("No specific entities found.", "js-support-ticket")) . "' + '</div>';
        return html;
    },

    smartInsert: function(editorId, markdownText) {
        var isVisualActive = typeof tinyMCE !== 'undefined' && tinyMCE.get(editorId) && !jQuery('#wp-' + editorId + '-wrap').hasClass('html-active');
        if (isVisualActive) {
            tinyMCE.get(editorId).execCommand('mceInsertContent', false, this.toHTML(markdownText));
        } else {
            var el = jQuery('#' + editorId);
            var currentContent = el.val();
            el.val(currentContent + (currentContent ? '\\n\\n' : '') + this.toPlainText(markdownText));
        }
    }
};

jQuery(document).ready(function(\$) {
    var currentTicketId = \$('input[name=\"jssupportticketid\"]').val() || new URLSearchParams(window.location.search).get('jssupportticketid');

    var zy_i18n = {
        unconfigured_alert: '" . esc_js(__('Zywrap AI Co-Pilot is not configured! Please navigate to Zywrap AI Settings to connect your API key and unlock AI Auto-Replies.', 'js-support-ticket')) . "',
        loading_context: '" . esc_js(__('Loading context securely from database...', 'js-support-ticket')) . "',
        error_prefix: '" . esc_js(__('Error', 'js-support-ticket')) . ":',
        no_variations: '" . esc_js(__('No wrapper variations found. Please sync data.', 'js-support-ticket')) . "',
        optional_label: '" . esc_js(__('(Optional)', 'js-support-ticket')) . "',
        ready_generate: '" . esc_js(__('No extra context variables required. Ready to generate.', 'js-support-ticket')) . "',
        drafting: '" . esc_js(__('Drafting response...', 'js-support-ticket')) . "',
        sys_error: '" . esc_js(__('System Error parsing response. Check console.', 'js-support-ticket')) . "',
        unlock_title: '" . esc_js(__('Unlock AI Features', 'js-support-ticket')) . "',
        unlock_desc: '" . esc_js(__('Connect your Zywrap API key in settings to unlock 1-click summaries, translations, and data extraction.', 'js-support-ticket')) . "',
        configure_link: '" . esc_js(__('Configure Zywrap AI &rarr;', 'js-support-ticket')) . "',
        translate_to: '" . esc_js(__('Translate to', 'js-support-ticket')) . "',
        extract_details: '" . esc_js(__('Extract Details', 'js-support-ticket')) . "',
        ai_executing: '" . esc_js(__('AI is executing:', 'js-support-ticket')) . "',
        ai_insight: '" . esc_js(__('AI Insight:', 'js-support-ticket')) . "'
    };

    function openZywrapModal(targetTab) {
        \$('#jsst-zywrap-backdrop, #jsst-zywrap-modal').fadeIn(200);
        if (targetTab) {
            setTimeout(function() { \$('.zywrap-intent-tab[data-target=\"' + targetTab + '\"]').trigger('click'); }, 50);
        } else if (\$('#zywrap-active-wrapper').val() === '') {
            \$('.zywrap-action-card[data-code=\"ticket_thread_reply_composer\"]').trigger('click');
        }
    }

    \$('#jsst-open-zywrap-modal').off('click').on('click', function(e) {
        e.preventDefault();
        openZywrapModal(null);
    });

    \$(document).off('click', '.zywrap-open-tab-btn').on('click', '.zywrap-open-tab-btn', function(e) {
        e.preventDefault();
        if (\$(this).data('active') != '1') { alert(zy_i18n.unconfigured_alert); return; }
        openZywrapModal(\$(this).data('tab'));
    });

    \$('.zywrap-intent-tab').on('click', function(e) {
        e.preventDefault();
        \$('.zywrap-intent-tab').removeClass('active');
        \$(this).addClass('active');
        \$('.zywrap-intent-panel').hide();
        \$('#zywrap-intent-' + \$(this).data('target')).css('display', (\$(this).data('target') === 'compose' || \$(this).data('target') === 'more') ? 'block' : 'flex');
    });

    \$('#zywrap-usecase-search').on('keyup', function() {
        var val = \$(this).val().toLowerCase();
        \$('#zywrap-intent-more .zywrap-action-card').filter(function() {
            \$(this).toggle(\$(this).find('h4').text().toLowerCase().indexOf(val) > -1)
        });
    });

    \$('.zywrap-action-card').on('click', function() {
        var useCaseCode = \$(this).data('code');
        var cardTitle = \$(this).data('title');
        \$('.zywrap-action-card').removeClass('selected');
        \$(this).addClass('selected');
        \$('#workspace-action-title').text(cardTitle);
        \$('#zywrap-generate-btn').prop('disabled', true);
        \$('#zywrap-dynamic-playground').html('<div class=\"js-ticket-zywrap-empty-prompt\"><span class=\"spinner is-active\"></span> ' + zy_i18n.loading_context + '</div>');

        \$.post(ajaxurl, {
            action: 'jsticket_ajax', jstmod: 'zywrap', task: 'ajaxGetWrappers',
            use_case_code: useCaseCode, ticket_id: currentTicketId, _wpnonce: '" . esc_js($js_ticket_zywrap_nonce) . "'
        }, function(response) {
            var res = typeof response === 'object' ? response : JSON.parse(response);
            if (!res.success) {
                \$('#zywrap-dynamic-playground').html('<div class=\"js-ticket-zywrap-status-tag-error\">' + zy_i18n.error_prefix + ' ' + res.data.message + '</div>');
                return;
            }

            var apiData = res.data;
            if(apiData.wrappers && apiData.wrappers.length > 0) {
                \$('#zywrap-active-wrapper').val(apiData.wrappers[0].code);
                \$('#zywrap-generate-btn').prop('disabled', false);
            } else {
                \$('#zywrap-dynamic-playground').html('<div class=\"js-ticket-zywrap-status-tag-error\">' + zy_i18n.no_variations + '</div>');
                return;
            }

            var html = '';
            var dbContext = apiData.ticketData;

            function toTitleCase(str) { return str.replace(/([A-Z])/g, ' \$1').replace(/^./, function(str){ return str.toUpperCase(); }); }
            function getSmartDefaultValue(key, label) {
                var k = key.toLowerCase(), l = label.toLowerCase();
                if (k.indexOf('case') !== -1 || k.indexOf('ticketid') !== -1 || l.indexOf('subject') !== -1) return dbContext.subject;
                else if (l.indexOf('latest') !== -1 || k.indexOf('latest') !== -1 || l.indexOf('last message') !== -1) return dbContext.latestCustomerMsg;
                else if (l.indexOf('thread') !== -1 || l.indexOf('history') !== -1 || k.indexOf('thread') !== -1 || l.indexOf('summary') !== -1 || l.indexOf('experience') !== -1 || l.indexOf('issue') !== -1 || l.indexOf('message') !== -1) return dbContext.fullThread; 
                return '';
            }

            if (apiData.schema && (apiData.schema.req || apiData.schema.opt)) {
                html += '<div class=\"js-ticket-zywrap-instructions-card\">';
                if (apiData.schema.req) {
                    \$.each(apiData.schema.req, function(key, v) {
                        var label = toTitleCase(key), placeholder = v.d ? v.d : '', defaultVal = getSmartDefaultValue(key, label); 
                        html += '<div class=\"js-ticket-zywrap-pg-form-group\"><label class=\"js-ticket-zywrap-pg-label\">' + label + ' <span class=\"js-ticket-zywrap-required\">*</span></label><textarea class=\"zywrap-dyn-var js-ticket-zywrap-pg-textarea\" data-key=\"' + key + '\" placeholder=\"' + placeholder + '\" rows=\"4\">' + defaultVal + '</textarea></div>';
                    });
                }
                if (apiData.schema.opt) {
                    html += '<hr class=\"js-ticket-zywrap-divider-dashed\">';
                    \$.each(apiData.schema.opt, function(key, v) {
                        var label = toTitleCase(key), placeholder = v.d ? v.d : '', defaultVal = getSmartDefaultValue(key, label); 
                        html += '<div class=\"js-ticket-zywrap-pg-form-group\"><label class=\"js-ticket-zywrap-pg-label\">' + label + ' <span class=\"js-ticket-zywrap-label-opt\">' + zy_i18n.optional_label + '</span></label><textarea class=\"zywrap-dyn-var js-ticket-zywrap-pg-textarea\" data-key=\"' + key + '\" placeholder=\"' + placeholder + '\" rows=\"2\">' + defaultVal + '</textarea></div>';
                    });
                }
                html += '</div>';
            }
            \$('#zywrap-dynamic-playground').html(html || '<div class=\"js-ticket-zywrap-empty-prompt\">' + zy_i18n.ready_generate + '</div>');
            \$('#zywrap-extra-instructions-container').show();
            \$('#zywrap-extra-instructions').val('');
        });
    });

    \$('#zywrap-generate-btn').on('click', function() {
        var btn = \$(this), wrapperCode = \$('#zywrap-active-wrapper').val();
        if(!wrapperCode) return;

        var dynamicVars = {}, structuredTextParts = []; 
        \$('.zywrap-dyn-var').each(function() {
            var k = \$(this).data('key'), v = \$(this).val().trim(); 
            if (v) { dynamicVars[k] = v; structuredTextParts.push(k + ': ' + v); }
        });

        var finalPrompt = \$('#zywrap-extra-instructions').length ? \$('#zywrap-extra-instructions').val().trim() : '';
        var structuredText = structuredTextParts.join('\\n');
        if (finalPrompt && structuredText) finalPrompt += '\\n\\n' + structuredText;
        else if (structuredText) finalPrompt = structuredText;

        \$('#zywrap-spinner').addClass('is-active');
        \$('#zywrap-status-text').text(zy_i18n.drafting);
        btn.prop('disabled', true);
        \$('#zywrap-draft-area').val('');

        \$.post(ajaxurl, {
            action: 'jsticket_ajax', jstmod: 'zywrap', task: 'generateReply',
            tone: \$('#zywrap-tone-select').val(), model_code: \$('#zywrap-model-select').val(),
            wrapper_code: wrapperCode, prompt: finalPrompt, variables: JSON.stringify(dynamicVars),
            _wpnonce: '" . esc_js($js_ticket_zywrap_nonce) . "' 
        }, function(response) {
            \$('#zywrap-spinner').removeClass('is-active');
            \$('#zywrap-status-text').text('');
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
                    \$('#zywrap-draft-area').val(data.data.output);
                    \$('#zywrap-insert-btn').prop('disabled', false);
                } else {
                    \$('#zywrap-draft-area').val(zy_i18n.error_prefix + ' ' + (data.data && data.data.message ? data.data.message : data.message));
                }
            } catch(e) {
                \$('#zywrap-draft-area').val(zy_i18n.sys_error);
            }
        });
    });

    \$('#zywrap-insert-btn').on('click', function() {
        var aiText = \$('#zywrap-draft-area').val();
        ZywrapFormatter.smartInsert('jsticket_message', aiText);
        \$('#jsst-zywrap-close').trigger('click');
    });

    \$('#jsst-zywrap-close, #jsst-zywrap-backdrop').on('click', function(e) { 
        e.preventDefault(); 
        \$('#jsst-zywrap-backdrop, #jsst-zywrap-modal').fadeOut(200); 
    });

    \$(document).off('click', '.zywrap-inline-ai-btn').on('click', '.zywrap-inline-ai-btn', function(e) {
        e.preventDefault();
        var btn = \$(this), wrapperCode = btn.data('wrapper'), actionName = btn.text().trim(); 
        
        var specificMessageText = btn.closest('.zywrap-inline-actions').prev('.note-msg, .js-tkt-det-tkt-msg').text().trim();
        if(!specificMessageText) specificMessageText = btn.closest('.js-ticket-thread, .ticket-details').find('.note-msg, .js-tkt-det-tkt-msg').text().trim();

        var resultBox = btn.closest('.zywrap-inline-actions').next('.zywrap-inline-result');
        if (btn.data('active') != '1') {
            resultBox.html('<div class=\"js-ticket-zywrap-status-card js-ticket-zywrap-status-inactive\"><strong>' + zy_i18n.unlock_title + '</strong><br>' + zy_i18n.unlock_desc + '<br><a href=\"?page=zywrap&jstlay=zywrap_settings\">' + zy_i18n.configure_link + '</a></div>').slideDown(200);
            return;
        }

        var inlineVars = { 'ticketThread': specificMessageText, 'message': specificMessageText, 'text': specificMessageText };
        var finalPrompt = specificMessageText, reqLanguage = ''; 

        if (wrapperCode === 'tl_supp_tick_tran_loca_926d_base') {
            var targetLang = btn.data('lang') || 'English'; 
            reqLanguage = targetLang; 
            actionName = zy_i18n.translate_to + ' ' + targetLang; 
        }

        if (wrapperCode === 'ee_support_ticket_detail_extraction_base') actionName = zy_i18n.extract_details;

        btn.prop('disabled', true).css('opacity', '0.5');
        resultBox.html('<span class=\"spinner is-active\" style=\"float:none; margin:0 5px 0 0; width:14px; height:14px;\"></span> ' + zy_i18n.ai_executing + ' ' + actionName + '...').slideDown(200);

        var defaultModel = '" . esc_js($js_ticket_global_default_model) . "';
        var ajaxPayload = {
            action: 'jsticket_ajax', 
            jstmod: 'zywrap', 
            task: 'generateReply', 
            tone: 'professional',
            language: reqLanguage, 
            wrapper_code: wrapperCode, 
            prompt: finalPrompt, 
            variables: JSON.stringify(inlineVars), 
            _wpnonce: '" . esc_js($js_ticket_zywrap_nonce) . "' 
        };

        if (defaultModel !== '') {
            ajaxPayload.model_code = defaultModel;
        }

        \$.post(ajaxurl, ajaxPayload, function(response) {
            btn.prop('disabled', false).css('opacity', '1');
            try {
                var cleanResponse = response;
                if (typeof response === 'string') {
                    var jsonStart = response.indexOf('{'), jsonEnd = response.lastIndexOf('}');
                    if (jsonStart !== -1 && jsonEnd !== -1) { cleanResponse = response.substring(jsonStart, jsonEnd + 1); }
                }
                var data = typeof cleanResponse === 'object' ? cleanResponse : JSON.parse(cleanResponse);
                
                if(data.success) {
                    var headerHtml = '<div style=\"font-weight:600; margin-bottom:15px; border-bottom:1px solid #cbd5e1; padding-bottom:8px; color:#1e40af; display:flex; align-items:center; gap:6px;\">';
                    headerHtml += '<span class=\"dashicons dashicons-superhero-alt\" style=\"font-size:18px; width:18px; height:18px;\"></span>';
                    headerHtml += zy_i18n.ai_insight + ' ' + actionName + '</div>'; 
                    
                    var rawOutput = data.data.output;
                    var formattedOutput = '';

                    try {
                        var possibleJsonString = rawOutput.replace(/^```json/i, '').replace(/```\$/i, '').trim();
                        var parsedJson = JSON.parse(possibleJsonString);
                        formattedOutput = '<div class=\"zywrap-json-content\">' + ZywrapFormatter.formatJSON(parsedJson) + '</div>';
                    } catch(e) {
                        formattedOutput = '<div class=\"zywrap-markdown-content\">' + ZywrapFormatter.toHTML(rawOutput) + '</div>';
                    }
                    
                    resultBox.html(headerHtml + formattedOutput);
                } else {
                    resultBox.html('<span style=\"color:#ef4444;\">' + zy_i18n.error_prefix + ' ' + (data.data.message || data.message) + '</span>');
                }
            } catch(e) {
                resultBox.html('<span style=\"color:#ef4444;\">' + zy_i18n.sys_error + '</span>');
            }
        });
    });
});
";
wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
?>