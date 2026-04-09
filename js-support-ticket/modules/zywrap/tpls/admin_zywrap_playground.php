<?php
if(!defined('ABSPATH')) die('Restricted Access');
$zywrap_nonce = wp_create_nonce('zywrap_ajax_action');
JSSTmessage::getMessage(); 
?>

<style>
    .pg-card { background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 20px; }
    .pg-title { font-size: 16px; font-weight: 600; margin-top: 0; margin-bottom: 15px; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
    .pg-form-group { margin-bottom: 15px; }
    .pg-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: #334155; }
    .pg-input, .pg-select, .pg-textarea { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #f8fafc; box-sizing: border-box; }
    .pg-input:focus, .pg-select:focus, .pg-textarea:focus { outline: none; border-color: #2563eb; background: #fff; }
    .pg-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .pg-schema-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
    .pg-schema-title { font-size: 13px; font-weight: 600; border-bottom: 1px solid #cbd5e1; padding-bottom: 8px; margin-top: 0; margin-bottom: 12px; color: #475569; }
</style>

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
                        <li><?php echo esc_html(__('Zywrap Advanced Playground','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Zywrap Advanced Playground', 'js-support-ticket')); ?></h1>
        </div>
        
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n" style="padding: 20px;">
            <div style="display:grid; grid-template-columns: 1fr 1.8fr; gap: 30px;">
                
                <div>
                    <div class="pg-card">
                        <h2 class="pg-title">Configuration</h2>
                        <div class="pg-form-group">
                            <label class="pg-label">1. Category</label>
                            <select id="pg_category" class="pg-select"><option value="">Loading...</option></select>
                        </div>
                        <div class="pg-form-group">
                            <label class="pg-label">2. AI Solution</label>
                            <select id="pg_usecase" class="pg-select" disabled><option value="">-- Select Category First --</option></select>
                        </div>
                        <div class="pg-form-group">
                            <label class="pg-label">3. Configuration Style</label>
                            <select id="pg_wrapper" class="pg-select" disabled><option value="">-- Select Solution First --</option></select>
                        </div>
                        <div class="pg-form-group">
                            <label class="pg-label">4. AI Model</label>
                            <select id="pg_model" class="pg-select"><option value="">Loading...</option></select>
                        </div>
                        <div class="pg-form-group">
                            <label class="pg-label">5. Target Language</label>
                            <select id="pg_language" class="pg-select"><option value="">Loading...</option></select>
                        </div>
                    </div>

                    <div class="pg-card">
                        <h2 class="pg-title">Advanced Overrides</h2>
                        <div class="pg-grid-2 pg-overrides">
                            <div><label class="pg-label">Tone</label><select id="toneCode" class="pg-select"><option value="">-- Default --</option></select></div>
                            <div><label class="pg-label">Style</label><select id="styleCode" class="pg-select"><option value="">-- Default --</option></select></div>
                            <div><label class="pg-label">Formatting</label><select id="formatCode" class="pg-select"><option value="">-- Default --</option></select></div>
                            <div><label class="pg-label">Complexity</label><select id="complexityCode" class="pg-select"><option value="">-- Default --</option></select></div>
                            <div><label class="pg-label">Length</label><select id="lengthCode" class="pg-select"><option value="">-- Default --</option></select></div>
                            <div><label class="pg-label">Audience</label><select id="audienceCode" class="pg-select"><option value="">-- Default --</option></select></div>
                            <div><label class="pg-label">Goal</label><select id="responseGoalCode" class="pg-select"><option value="">-- Default --</option></select></div>
                            <div><label class="pg-label">Output Type</label><select id="outputCode" class="pg-select"><option value="">-- Default --</option></select></div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="pg-card">
                        <div id="pg_dynamic_schema"></div>
                        
                        <div class="pg-form-group">
                            <label id="pg_prompt_label" class="pg-label">Prompt / Additional Context</label>
                            <textarea id="pg_prompt" class="pg-textarea" rows="4" placeholder="Type your request or additional instructions here..."></textarea>
                        </div>
                        
                        <button id="pg_run_btn" class="button button-primary" style="width: 100%; height: 42px; background: #2563eb; border-color: #1d4ed8; font-size: 15px;">
                            <span class="dashicons dashicons-controls-play" style="margin-top: 4px;"></span> Generate Response
                        </button>
                    </div>

                    <div class="pg-card">
                        <h2 class="pg-title">AI Response</h2>
                        <pre id="pg_output" style="background: #1e293b; color: #f8fafc; padding: 20px; border-radius: 6px; font-family: monospace; font-size: 13px; min-height: 250px; white-space: pre-wrap; margin: 0; overflow-x: auto;">Output will appear here...</pre>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    
    // Abstract WP AJAX
    function fetchAPI(task, params = {}) {
        return new Promise((resolve, reject) => {
            var payload = Object.assign({ action: 'jsticket_ajax', jstmod: 'zywrap', task: task, _wpnonce: '<?php echo esc_js($zywrap_nonce); ?>' }, params);
            $.post(ajaxurl, payload, function(response) {
                resolve(typeof response === 'object' ? response : JSON.parse(response));
            }).fail(reject);
        });
    }

    function populateSelect(el, data, placeholder = '-- Select --') {
        var html = '<option value="">' + placeholder + '</option>';
        if(data && data.length) {
            data.forEach(item => { html += '<option value="' + item.code + '">' + item.name + '</option>'; });
        }
        el.html(html).prop('disabled', false);
    }

    async function init() {
        const [categories, models, langs, templates] = await Promise.all([
            fetchAPI('pgGetCategories'), fetchAPI('pgGetModels'), fetchAPI('pgGetLanguages'), fetchAPI('pgGetBlockTemplates')
        ]);
        
        populateSelect($('#pg_category'), categories);
        populateSelect($('#pg_model'), models, 'Default Model (Auto)');
        
        // --- NEW: Auto-Select Global Default Model ---
        var globalDefaultModel = '<?php echo esc_js(get_option("jsst_zywrap_default_model", "")); ?>';
        if (globalDefaultModel) {
            $('#pg_model').val(globalDefaultModel);
        }

        populateSelect($('#pg_language'), langs, 'English (Default)');
        
        // --- NEW: Auto-Select Global Default Language ---
        var globalDefaultLang = '<?php echo esc_js(get_option("jsst_zywrap_default_lang", "English")); ?>';
        if (globalDefaultLang && globalDefaultLang !== 'English') {
            $('#pg_language').val(globalDefaultLang);
        }

        const overrideMap = { tones: 'toneCode', styles: 'styleCode', formattings: 'formatCode', complexities: 'complexityCode', lengths: 'lengthCode', audienceLevels: 'audienceCode', responseGoals: 'responseGoalCode', outputTypes: 'outputCode' };
        for (const [type, elId] of Object.entries(overrideMap)) {
            if (templates[type]) populateSelect($('#' + elId), templates[type], '-- Default --');
        }
    }

    // 1. Category -> Load Use Cases
    $('#pg_category').on('change', async function() {
        var cat = $(this).val();
        if (!cat) {
            $('#pg_usecase').html('<option value="">-- Select Category First --</option>').prop('disabled', true);
            $('#pg_wrapper').html('<option value="">-- Select Solution First --</option>').prop('disabled', true);
            $('#pg_dynamic_schema').empty();
            return;
        }
        $('#pg_usecase').html('<option value="">Loading...</option>');
        const useCases = await fetchAPI('pgGetUseCases', {category: cat});
        populateSelect($('#pg_usecase'), useCases, '-- Select a Solution --');
        $('#pg_wrapper').html('<option value="">-- Select Solution First --</option>').prop('disabled', true);
        $('#pg_dynamic_schema').empty();
    });

    // 2. Use Case -> Load Wrappers
    $('#pg_usecase').on('change', async function() {
        var uc = $(this).val();
        if (!uc) {
            $('#pg_wrapper').html('<option value="">-- Select Solution First --</option>').prop('disabled', true);
            $('#pg_dynamic_schema').empty();
            return;
        }
        
        $('#pg_wrapper').html('<option value="">Loading...</option>');
        let wrappers = await fetchAPI('pgGetWrappers', {usecase: uc});
        
        var html = '<option value="">-- Select a Style --</option>';
        let autoSelectCode = null;

        if(wrappers && wrappers.length) {
            wrappers.forEach((w, index) => {
                const parts = w.name.split('—');
                const displayName = (w.base == 1 || w.base === "1") ? `✨ Base Template - ${parts[0].trim()}` : `↳ Variation: ${parts.length > 1 ? parts[1].trim() : w.name}`;
                html += '<option value="' + w.code + '">' + displayName + '</option>';

                if (w.base == 1 || w.base === "1") autoSelectCode = w.code;
                else if (index === 0 && !autoSelectCode) autoSelectCode = w.code;
            });
        }
        $('#pg_wrapper').html(html).prop('disabled', false);

        if (autoSelectCode) {
            $('#pg_wrapper').val(autoSelectCode).trigger('change');
        }
    });

    // 3. Wrapper -> Load Schema
    $('#pg_wrapper').on('change', async function() {
        $('#pg_dynamic_schema').empty();
        $('#pg_prompt_label').text("Prompt / Additional Context");
        if (!$(this).val()) return;

        const schema = await fetchAPI('pgGetSchema', {wrapper: $(this).val()});
        if (!schema || (!schema.req && !schema.opt)) return;

        let html = '';
        $('#pg_prompt_label').text("Additional Free-form Instructions");

        const buildSection = (title, data) => {
            if (!data || Object.keys(data).length === 0) return '';
            let sectionHtml = '<div class="pg-schema-section"><h3 class="pg-schema-title">' + title + '</h3><div class="pg-grid-2">';
            for (const [key, def] of Object.entries(data)) {
                const isPlaceholder = def.p !== undefined ? def.p : false;
                const defaultVal = def.d !== undefined ? def.d : '';
                const placeholderAttr = isPlaceholder ? 'placeholder="'+defaultVal+'"' : '';
                const valueAttr = (!isPlaceholder && defaultVal) ? 'value="'+defaultVal+'"' : '';
                const label = key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
                
                sectionHtml += '<div><label class="pg-label">' + label + '</label>' +
                    '<input type="text" class="pg-input pg-schema-input" data-key="' + key + '" ' + placeholderAttr + ' ' + valueAttr + '></div>';
            }
            return sectionHtml + '</div></div>';
        };

        html += buildSection('Core Inputs', schema.req);
        html += buildSection('Additional Context', schema.opt);
        $('#pg_dynamic_schema').html(html);
    });

    // 4. Execute Play
    $('#pg_run_btn').on('click', async function() {
        var btn = $(this);
        if (!$('#pg_wrapper').val()) return alert("Please select a wrapper.");
        
        $('#pg_output').text('Executing...');
        btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Generating...');
        
        let finalPrompt = $('#pg_prompt').val().trim();
        const variables = {};
        let structuredTextParts = [];

        $('.pg-schema-input').each(function() {
            const val = $(this).val().trim();
            if (val !== '') {
                const key = $(this).data('key');
                variables[key] = val;
                structuredTextParts.push(`${key}: ${val}`);
            }
        });

        const structuredText = structuredTextParts.join('\n');
        if (finalPrompt && structuredText) finalPrompt = `${finalPrompt}\n\n${structuredText}`;
        else if (structuredText) finalPrompt = structuredText;

        const overrides = {};
        $('.pg-overrides select').each(function() {
            if ($(this).val()) overrides[$(this).attr('id')] = $(this).val();
        });

        try {
            const response = await fetchAPI('pgExecute', {
                model: $('#pg_model').val(),
                wrapperCode: $('#pg_wrapper').val(),
                language: $('#pg_language').val(),
                prompt: finalPrompt,
                variables: JSON.stringify(variables),
                overrides: JSON.stringify(overrides)
            });
            
            if (response.success) {
                $('#pg_output').text(response.data.output || JSON.stringify(response.data, null, 2));
            } else {
                $('#pg_output').text('Error: ' + response.data.message);
            }
        } catch (error) {
            $('#pg_output').text('System Error. Check Console.');
        } finally {
            btn.prop('disabled', false).html('<span class="dashicons dashicons-controls-play" style="margin-top: 4px;"></span> Generate Response');
        }
    });

    init();
});
</script>