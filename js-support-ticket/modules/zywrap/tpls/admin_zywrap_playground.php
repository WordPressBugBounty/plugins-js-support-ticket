<?php
if(!defined('ABSPATH')) die('Restricted Access');
$js_ticket_zywrap_nonce = wp_create_nonce('zywrap_ajax_action');
JSSTmessage::getMessage(); 
?>

<div id="jsstadmin-wrapper" class="js-ticket-zywrap-playground">
    <div id="jsstadmin-leftmenu">
        <?php JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Zywrap Advanced Playground','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Zywrap Advanced Playground', 'js-support-ticket')); ?></h1>
        </div>
        
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n js-ticket-zywrap-padding-20">
            <div class="js-ticket-zywrap-playground-grid">
                
                <div class="js-ticket-zywrap-column">
                    <div class="js-ticket-zywrap-pg-card">
                        <h2 class="js-ticket-zywrap-pg-title"><?php echo esc_html__('Configuration', 'js-support-ticket'); ?></h2>
                        <div class="js-ticket-zywrap-pg-form-group">
                            <label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('1. Category', 'js-support-ticket'); ?></label>
                            <select id="pg_category" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Loading...', 'js-support-ticket'); ?></option></select>
                        </div>
                        <div class="js-ticket-zywrap-pg-form-group">
                            <label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('2. AI Solution', 'js-support-ticket'); ?></label>
                            <select id="pg_usecase" class="js-ticket-zywrap-pg-select" disabled><option value=""><?php echo esc_html__('Select Category First', 'js-support-ticket'); ?></option></select>
                        </div>
                        <div class="js-ticket-zywrap-pg-form-group">
                            <label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('3. Configuration Style', 'js-support-ticket'); ?></label>
                            <select id="pg_wrapper" class="js-ticket-zywrap-pg-select" disabled><option value=""><?php echo esc_html__('Select Solution First', 'js-support-ticket'); ?></option></select>
                        </div>
                        <div class="js-ticket-zywrap-pg-form-group">
                            <label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('4. AI Model', 'js-support-ticket'); ?></label>
                            <select id="pg_model" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Loading...', 'js-support-ticket'); ?></option></select>
                        </div>
                        <div class="js-ticket-zywrap-pg-form-group">
                            <label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('5. Target Language', 'js-support-ticket'); ?></label>
                            <select id="pg_language" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Loading...', 'js-support-ticket'); ?></option></select>
                        </div>
                    </div>

                    <div class="js-ticket-zywrap-pg-card">
                        <h2 class="js-ticket-zywrap-pg-title"><?php echo esc_html__('Advanced Overrides', 'js-support-ticket'); ?></h2>
                        <div class="js-ticket-zywrap-pg-grid-2 js-ticket-zywrap-pg-overrides">
                            <div><label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Tone', 'js-support-ticket'); ?></label><select id="toneCode" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Default', 'js-support-ticket'); ?></option></select></div>
                            <div><label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Style', 'js-support-ticket'); ?></label><select id="styleCode" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Default', 'js-support-ticket'); ?></option></select></div>
                            <div><label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Formatting', 'js-support-ticket'); ?></label><select id="formatCode" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Default', 'js-support-ticket'); ?></option></select></div>
                            <div><label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Complexity', 'js-support-ticket'); ?></label><select id="complexityCode" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Default', 'js-support-ticket'); ?></option></select></div>
                            <div><label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Length', 'js-support-ticket'); ?></label><select id="lengthCode" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Default', 'js-support-ticket'); ?></option></select></div>
                            <div><label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Audience', 'js-support-ticket'); ?></label><select id="audienceCode" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Default', 'js-support-ticket'); ?></option></select></div>
                            <div><label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Goal', 'js-support-ticket'); ?></label><select id="responseGoalCode" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Default', 'js-support-ticket'); ?></option></select></div>
                            <div><label class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Output Type', 'js-support-ticket'); ?></label><select id="outputCode" class="js-ticket-zywrap-pg-select"><option value=""><?php echo esc_html__('Default', 'js-support-ticket'); ?></option></select></div>
                        </div>
                    </div>
                </div>

                <div class="js-ticket-zywrap-column">
                    <div class="js-ticket-zywrap-pg-card">
                        <div id="pg_dynamic_schema"></div>
                        
                        <div class="js-ticket-zywrap-pg-form-group">
                            <label id="pg_prompt_label" class="js-ticket-zywrap-pg-label"><?php echo esc_html__('Prompt / Additional Context', 'js-support-ticket'); ?></label>
                            <textarea id="pg_prompt" class="js-ticket-zywrap-pg-textarea" rows="4" placeholder="<?php echo esc_attr__('Type your request or additional instructions here...', 'js-support-ticket'); ?>"></textarea>
                        </div>
                        
                        <button id="pg_run_btn" class="button button-primary js-ticket-zywrap-pg-run-btn">
                            <span class="dashicons dashicons-controls-play" aria-hidden="true"></span> <?php echo esc_html__('Generate Response', 'js-support-ticket'); ?>
                        </button>
                    </div>

                    <div class="js-ticket-zywrap-pg-card">
                        <h2 class="js-ticket-zywrap-pg-title"><?php echo esc_html__('AI Response', 'js-support-ticket'); ?></h2>
                        <pre id="pg_output" class="js-ticket-zywrap-pg-output-box"><?php echo esc_html__('Output will appear here...', 'js-support-ticket'); ?></pre>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$jsst_jssupportticket_js = "
    jQuery(document).ready(function(\$) {
        var ajaxurl = '" . admin_url('admin-ajax.php') . "';
        
        // Abstract WP AJAX
        function fetchAPI(task, params = {}) {
            return new Promise((resolve, reject) => {
                var payload = Object.assign({ 
                    action: 'jsticket_ajax', 
                    jstmod: 'zywrap', 
                    task: task, 
                    _wpnonce: '" . esc_js($js_ticket_zywrap_nonce) . "' 
                }, params);
                \$.post(ajaxurl, payload, function(response) {
                    resolve(typeof response === 'object' ? response : JSON.parse(response));
                }).fail(reject);
            });
        }

        function populateSelect(el, data, placeholder = 'Select') {
            var html = '<option value=\"\">' + placeholder + '</option>';
            if(data && data.length) {
                data.forEach(item => { html += '<option value=\"' + item.code + '\">' + item.name + '</option>'; });
            }
            el.html(html).prop('disabled', false);
        }

        async function init() {
            try {
                const [categories, models, langs, templates] = await Promise.all([
                    fetchAPI('pgGetCategories'), fetchAPI('pgGetModels'), fetchAPI('pgGetLanguages'), fetchAPI('pgGetBlockTemplates')
                ]);
                
                populateSelect(\$('#pg_category'), categories);
                populateSelect(\$('#pg_model'), models, '" . esc_js(__("Default Model (Auto)", "js-support-ticket")) . "');
                
                var globalDefaultModel = '" . esc_js(get_option("jsst_zywrap_default_model", "")) . "';
                if (globalDefaultModel) \$('#pg_model').val(globalDefaultModel);

                populateSelect(\$('#pg_language'), langs, '" . esc_js(__("English (Default)", "js-support-ticket")) . "');
                
                var globalDefaultLang = '" . esc_js(get_option("jsst_zywrap_default_lang", "English")) . "';
                if (globalDefaultLang && globalDefaultLang !== 'English') \$('#pg_language').val(globalDefaultLang);

                const overrideMap = { tones: 'toneCode', styles: 'styleCode', formattings: 'formatCode', complexities: 'complexityCode', lengths: 'lengthCode', audienceLevels: 'audienceCode', responseGoals: 'responseGoalCode', outputTypes: 'outputCode' };
                for (const [type, elId] of Object.entries(overrideMap)) {
                    if (templates[type]) populateSelect(\$('#' + elId), templates[type], '" . esc_js(__("Default", "js-support-ticket")) . "');
                }
            } catch (e) { console.error('Playground Init Failed', e); }
        }

        // Event Listeners
        \$('#pg_category').on('change', async function() {
            var cat = \$(this).val();
            if (!cat) {
                \$('#pg_usecase').html('<option value=\"\">" . esc_js(__("Select Category First", "js-support-ticket")) . "</option>').prop('disabled', true);
                \$('#pg_wrapper').html('<option value=\"\">" . esc_js(__("Select Solution First", "js-support-ticket")) . "</option>').prop('disabled', true);
                \$('#pg_dynamic_schema').empty();
                return;
            }
            \$('#pg_usecase').html('<option value=\"\">" . esc_js(__("Loading...", "js-support-ticket")) . "</option>');
            const useCases = await fetchAPI('pgGetUseCases', {category: cat});
            populateSelect(\$('#pg_usecase'), useCases, '" . esc_js(__("Select a Solution", "js-support-ticket")) . "');
            \$('#pg_wrapper').html('<option value=\"\">" . esc_js(__("Select Solution First", "js-support-ticket")) . "</option>').prop('disabled', true);
            \$('#pg_dynamic_schema').empty();
        });

        \$('#pg_usecase').on('change', async function() {
            var uc = \$(this).val();
            if (!uc) {
                \$('#pg_wrapper').html('<option value=\"\">" . esc_js(__("Select Solution First", "js-support-ticket")) . "</option>').prop('disabled', true);
                \$('#pg_dynamic_schema').empty();
                return;
            }
            \$('#pg_wrapper').html('<option value=\"\">" . esc_js(__("Loading...", "js-support-ticket")) . "</option>');
            let wrappers = await fetchAPI('pgGetWrappers', {usecase: uc});
            var html = '<option value=\"\">" . esc_js(__("Select a Style", "js-support-ticket")) . "</option>';
            let autoSelectCode = null;
            if(wrappers && wrappers.length) {
                wrappers.forEach((w, index) => {
                    const parts = w.name.split('—');
                    const displayName = (w.base == 1 || w.base === '1') ? '✨ " . esc_js(__("Base Template", "js-support-ticket")) . " - ' + parts[0].trim() : '↳ " . esc_js(__("Variation", "js-support-ticket")) . ": ' + (parts.length > 1 ? parts[1].trim() : w.name);
                    html += '<option value=\"' + w.code + '\">' + displayName + '</option>';
                    if (w.base == 1 || w.base === '1') autoSelectCode = w.code;
                    else if (index === 0 && !autoSelectCode) autoSelectCode = w.code;
                });
            }
            \$('#pg_wrapper').html(html).prop('disabled', false);
            if (autoSelectCode) \$('#pg_wrapper').val(autoSelectCode).trigger('change');
        });

        \$('#pg_wrapper').on('change', async function() {
            \$('#pg_dynamic_schema').empty();
            \$('#pg_prompt_label').text('" . esc_js(__("Prompt / Additional Context", "js-support-ticket")) . "');
            if (!\$(this).val()) return;
            const schema = await fetchAPI('pgGetSchema', {wrapper: \$(this).val()});
            if (!schema || (!schema.req && !schema.opt)) return;
            let html = '';
            \$('#pg_prompt_label').text('" . esc_js(__("Additional Free-form Instructions", "js-support-ticket")) . "');
            const buildSection = (title, data) => {
                if (!data || Object.keys(data).length === 0) return '';
                let sectionHtml = '<div class=\"js-ticket-zywrap-pg-schema-section\"><h3 class=\"js-ticket-zywrap-pg-schema-title\">' + title + '</h3><div class=\"js-ticket-zywrap-pg-grid-2\">';
                for (const [key, def] of Object.entries(data)) {
                    const isPlaceholder = def.p !== undefined ? def.p : false;
                    const defaultVal = def.d !== undefined ? def.d : '';
                    const placeholderAttr = isPlaceholder ? 'placeholder=\"'+defaultVal+'\"' : '';
                    const valueAttr = (!isPlaceholder && defaultVal) ? 'value=\"'+defaultVal+'\"' : '';
                    const label = key.replace(/([A-Z])/g, ' \$1').replace(/^./, str => str.toUpperCase());
                    sectionHtml += '<div><label class=\"js-ticket-zywrap-pg-label\">' + label + '</label>' +
                        '<input type=\"text\" class=\"js-ticket-zywrap-pg-input pg-schema-input\" data-key=\"' + key + '\" ' + placeholderAttr + ' ' + valueAttr + '></div>';
                }
                return sectionHtml + '</div></div>';
            };
            html += buildSection('" . esc_js(__("Core Inputs", "js-support-ticket")) . "', schema.req);
            html += buildSection('" . esc_js(__("Additional Context", "js-support-ticket")) . "', schema.opt);
            \$('#pg_dynamic_schema').html(html);
        });

        \$('#pg_run_btn').on('click', async function() {
            var btn = \$(this);
            if (!\$('#pg_wrapper').val()) return alert('" . esc_js(__("Please select a wrapper.", "js-support-ticket")) . "');
            \$('#pg_output').text('" . esc_js(__("Executing...", "js-support-ticket")) . "');
            btn.prop('disabled', true).html('<span class=\"spinner is-active\"></span> " . esc_js(__("Generating...", "js-support-ticket")) . "');
            let finalPrompt = \$('#pg_prompt').val().trim();
            const variables = {};
            let structuredTextParts = [];
            \$('.pg-schema-input').each(function() {
                const val = \$(this).val().trim();
                if (val !== '') {
                    const key = \$(this).data('key');
                    variables[key] = val;
                    structuredTextParts.push(key + ': ' + val);
                }
            });
            const structuredText = structuredTextParts.join('\\n');
            if (finalPrompt && structuredText) finalPrompt = finalPrompt + '\\n\\n' + structuredText;
            else if (structuredText) finalPrompt = structuredText;
            const overrides = {};
            \$('.js-ticket-zywrap-pg-overrides select').each(function() {
                if (\$(this).val()) overrides[\$(this).attr('id')] = \$(this).val();
            });
            try {
                const response = await fetchAPI('pgExecute', {
                    model: \$('#pg_model').val(),
                    wrapperCode: \$('#pg_wrapper').val(),
                    language: \$('#pg_language').val(),
                    prompt: finalPrompt,
                    variables: JSON.stringify(variables),
                    overrides: JSON.stringify(overrides)
                });
                if (response.success) {
                    \$('#pg_output').text(response.data.output || JSON.stringify(response.data, null, 2));
                } else {
                    \$('#pg_output').text('" . esc_js(__("Error:", "js-support-ticket")) . " ' + response.data.message);
                }
            } catch (error) {
                \$('#pg_output').text('" . esc_js(__("System Error. Check Console.", "js-support-ticket")) . "');
            } finally {
                btn.prop('disabled', false).html('<span class=\"dashicons dashicons-controls-play\"></span> " . esc_js(__("Generate Response", "js-support-ticket")) . "');
            }
        });

        init();
    });
";

wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
?>