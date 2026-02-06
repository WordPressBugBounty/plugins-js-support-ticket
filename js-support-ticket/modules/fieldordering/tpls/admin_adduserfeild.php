<?php
if(!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
$jsst_jssupportticket_js ="
    var nextorid = 1;
    var nextandid = 1;
    ajaxurl = '".esc_url(admin_url('admin-ajax.php'))."';
    jQuery(document).ready(function ($) {
        $.validate();
    });
    function getChildForVisibleCombobox(val, no) {
        jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: val, jstmod: 'fieldordering', task: 'getChildForVisibleCombobox', '_wpnonce':'". esc_attr(wp_create_nonce("get-child-for-visible-combobox"))."', isAjaxCall: '1'}, function (data) {
            if (data != false) {
                jQuery('#js_or_row_'+no+' .visibleValueWrp').show();
                jQuery('#js_or_row_'+no+' .visibleValueWrp').html(jsstDecodeHTML(data));
                disableDefaultValueField();
            }else{
                jQuery('#js_or_row_'+no+' .visibleValueWrp').hide();
                disableDefaultValueFieldDueToValues();
            }
        });//jquery closed
    }
    function disableDefaultValueFieldDueToValues() {
        var type = jQuery('select#userfieldtype').val()
        if(type == 'depandant_field') {
            disableDefaultValueField();
            return;
        }
        var foundSelected = false;
        // jQuery('select.js-form-input-field-visible').each(function() {

        jQuery('select[name=\'visibleParent[]\']').each(function() {
            if (jQuery(this).val() !== '') {
                foundSelected = true;
                return false; // stops the loop early
            }
        });

        if (foundSelected) {
            return false;
        } else {
            // var values_count = jQuery('#values').length;
            // var values_count = jQuery('.user-field').length;
            // jQuery('input[name=\'values[]\']').each(function() {
            var values_count = 0;
            jQuery('input.user-field').each(function() {
                if (jQuery(this).val() !== '') {
                    values_count++;
                    return false; // stops the loop early
                }
            });
            if(values_count > 0) {
                makeDefaultValueVisible();
                jQuery('#defaultvalue_not_available').hide();
                jQuery('#subtitle_defaultvalue').hide();
                // jQuery('.defaultvalue_input').hide();
                // jQuery('.defaultvalue_select').show();
                // jQuery('input#defaultvalue_input').val('');
            } else {
                var type = jQuery('select#userfieldtype').val()
                if(type == 'combo' || type == 'radio' || type == 'multiple' || type == 'checkbox') {
                    makeDefaultValueVisible();
                    jQuery('#defaultvalue_not_available').hide();
                    // jQuery('.defaultvalue_input').hide();
                    // jQuery('.defaultvalue_select').show();
                    jQuery('#subtitle_defaultvalue').show();
                    // jQuery('input#defaultvalue_input').val('');
                    // jQuery('select#defaultvalue_select').val('');
                } else if(type == 'file') {
                    disableDefaultValueField();
                } else {
                    enableDefaultValueField();
                }
            }
        }
    }
    function enableDefaultValueFieldDueToValues() {
        var foundSelected = false;
        // jQuery('select.js-form-input-field-visible').each(function() {

        jQuery('select[name=\'visibleParent[]\']').each(function() {
            if (jQuery(this).val() !== '') {
                foundSelected = true;
                return false; // stops the loop early
            }
        });

        if (foundSelected) {
            disableDefaultValueField();
        } else {
            // var values_count = jQuery('#values').length;
            // var values_count  = jQuery('.user-field').length;

            var values_count = 0;
            jQuery('input.user-field').each(function() {
                if (jQuery(this).val() !== '') {
                    values_count++;
                    return false; // stops the loop early
                }
            });


            if(values_count > 0) {
                makeDefaultValueVisible();
                jQuery('#defaultvalue_not_available').hide();
                // jQuery('.defaultvalue_input').hide();
                // jQuery('.defaultvalue_select').show();
                jQuery('#subtitle_defaultvalue').hide();
            } else {
                var type = jQuery('select#userfieldtype').val();
                if(type == 'combo' || type == 'radio' || type == 'multiple' || type == 'checkbox') {
                    makeDefaultValueVisible();
                    jQuery('#defaultvalue_not_available').hide();
                    // jQuery('.defaultvalue_input').hide();
                    // jQuery('.defaultvalue_select').show();
                    jQuery('#subtitle_defaultvalue').show();
                    // jQuery('input#defaultvalue_input').val('');
                    // jQuery('select#defaultvalue_select').val('');
                } else if(type == 'file') {
                    disableDefaultValueField();
                } else {
                    enableDefaultValueField();
                }
            }
        }
    }
    function disableDefaultValueField() {
        jQuery('#defaultvalue_input').removeClass('custom_date js-form-date-field hasDatepicker');
        makeDefaultValueVisible();
        jQuery('#subtitle_defaultvalue').hide();
        // jQuery('select#defaultvalue_select').val('');
        // jQuery('input#defaultvalue_input').val('')
        // jQuery('.defaultvalue_select').hide('');
        // jQuery('.defaultvalue_input').show();
        jQuery('input#defaultvalue_input').prop('readonly', true);
        jQuery('select#defaultvalue_select').prop('disabled', true);
        jQuery('#defaultvalue_not_available').show();
    }
    function enableDefaultValueField() {
        makeDefaultValueVisible();
        jQuery('#subtitle_defaultvalue').hide();
        // jQuery('select#defaultvalue_select').val('');
        // jQuery('input#defaultvalue_input').val('');
        jQuery('#defaultvalue_not_available').hide();
    }
    function makeDefaultValueVisible(){
        jQuery('#defaultvalue_not_available').hide();
        jQuery('#subtitle_defaultvalue').hide();
        var type = jQuery('select#userfieldtype').val();
        if(type == 'combo' || type == 'radio' || type == 'multiple' || type == 'checkbox') {
            jQuery('.defaultvalue_select').show();
            jQuery('.defaultvalue_input').hide();
            jQuery('select#defaultvalue_select').prop('disabled', false);
        } else {
            jQuery('.defaultvalue_select').hide();
            jQuery('.defaultvalue_input').show();
            jQuery('input#defaultvalue_input').prop('readonly', false);
        }
    }
    function disablePlaceholderField() {
        jQuery('#placeholder_not_available').show();
        jQuery('input#placeholder').val('');
        jQuery('input#placeholder').prop('readonly', true);
    }
    function enablePlaceholderField() {
        jQuery('#placeholder_not_available').hide();
        // jQuery('input#placeholder').val('');
        jQuery('input#placeholder').prop('readonly', false);
    }
    function disableAdminSearchField() {
        jQuery('#subtitle_adminSearch').show();
        jQuery('select#search_admin').prop('disabled', true);
        jQuery('select#search_admin').val('0');
    }
    function disableUserSearchField() {
        jQuery('#subtitle_userSearch').show();
        jQuery('select#search_user').prop('disabled', true);
        jQuery('select#search_user').val('0');
    }
    function disableReadOnlyField() {
        jQuery('#subtitle_readOnly').show();
        jQuery('select#readonly').prop('disabled', true);
        jQuery('select#readonly').val('0');
    }
    function getConditionsForVisibleCombobox(val, no) {
        jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: val, jstmod: 'fieldordering', task: 'getConditionsForVisibleCombobox', '_wpnonce':'". esc_attr(wp_create_nonce("get-conditions-for-visible-combobox"))."', isAjaxCall: '1'}, function (data) {
            if (data != false) {
                jQuery('#js_or_row_'+no+' .visibleConditionWrp').show();
                jQuery('#js_or_row_'+no+' .visibleConditionWrp').html(jsstDecodeHTML(data));
            }else{
                jQuery('#js_or_row_'+no+' .visibleConditionWrp').hide();
            }
        });//jquery closed
    }
    // visible 
    function getMoreORRow(el, fieldfor, formid, field, id){
        jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'fieldordering', task: 'getHtmlForORRow' , nextorid : nextorid, fieldfor : fieldfor, formid : formid, field : field, id : id, '_wpnonce':'". esc_attr(wp_create_nonce('get-html-for-or-row')) ."'}, function (data) {
            if(data){
                data = JSON.parse(data);
                var parent = jQuery(el).closest('div.js-form-wrapper.js-form-visible-wrapper');
                jQuery(parent).find('.js-form-visible-or-row').append(jsstDecodeHTML(data));
                nextorid++;
            }
        });
    }
    function getMoreANDRow(fieldfor, formid, field, id){
        jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'fieldordering', task: 'getHtmlForANDRow' , nextandid : nextandid, nextorid : nextorid, fieldfor : fieldfor, formid : formid, field : field, id : id, '_wpnonce':'". esc_attr(wp_create_nonce('get-html-for-and-row')) ."'}, function (data) {
            if(data){
                data = JSON.parse(data);
                jQuery('.js-form-visible-add-row').append(jsstDecodeHTML(data));
                nextandid++;
                nextorid++;
            }
        });
    }
    function deleteOrRow(id){
        var parent = jQuery('#'+id).closest('div.js-form-visible-andwrp ');
        var count = parent.find('div.js-form-value').length;
        jQuery('#'+id).remove();
        if(count == 1) {
            jQuery(parent).remove();
        }
        disableDefaultValueFieldDueToValues();
    }
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
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
                        <?php if(in_array('multiform', jssupportticket::$_active_addons)){ ?>
                            <li><a href="?page=multiform" title="<?php echo esc_attr(__('Multiform','js-support-ticket')); ?>"><?php echo esc_html(__('Multiform','js-support-ticket')); ?></a></li>
                        <?php } ?>
                        <li><?php echo esc_html(__('Add Field','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt = "<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text">
                <?php
                    $jsst_heading = isset(jssupportticket::$jsst_data[0]['fieldvalues']) ? esc_html(__('Edit', 'js-support-ticket')) : esc_html(__('Add', 'js-support-ticket'));
                    echo esc_html($jsst_heading) . '&nbsp' . esc_html(__('Field', 'js-support-ticket'));
                ?>
                <?php if(isset(jssupportticket::$jsst_data['multiFormTitle'])){ ?>
                    <span class="jsstadmin-head-sub-text">
                        <?php echo ' ('.esc_html(jssupportticket::$jsst_data["multiFormTitle"]).')'; ?>
                    </span>
                <?php }?>
            </h1>
        </div>
        <?php
        $jsst_yesno = array(
            (object) array('id' => 1, 'text' => esc_html(__('Yes', 'js-support-ticket'))),
            (object) array('id' => 0, 'text' => esc_html(__('No', 'js-support-ticket'))));
        $jsst_equalnotequal = array(
            (object) array('id' => 1, 'text' => esc_html(__('Equal', 'js-support-ticket'))),
            (object) array('id' => 0, 'text' => esc_html(__('Not Equal', 'js-support-ticket'))));
        $jsst_visibleLogics = array(
            (object) array('id' => 'AND', 'text' => esc_html(__('AND', 'js-support-ticket'))),
            (object) array('id' => 'OR', 'text' => esc_html(__('OR', 'js-support-ticket'))));
        if(isset(jssupportticket::$jsst_data[0]['userfield']->userfieldtype) && jssupportticket::$jsst_data[0]['userfield']->userfieldtype != 'depandant_field'){
            $jsst_fieldtypes = array(
                (object) array('id' => 'text', 'text' => esc_html(__('Text Field', 'js-support-ticket'))),
                (object) array('id' => 'checkbox', 'text' => esc_html(__('Check Box', 'js-support-ticket'))),
                (object) array('id' => 'date', 'text' => esc_html(__('Date', 'js-support-ticket'))),
                (object) array('id' => 'combo', 'text' => esc_html(__('Drop Down', 'js-support-ticket'))),
                (object) array('id' => 'email', 'text' => esc_html(__('Email Address', 'js-support-ticket'))),
                (object) array('id' => 'textarea', 'text' => esc_html(__('Text Area', 'js-support-ticket'))),
                (object) array('id' => 'radio', 'text' => esc_html(__('Radio Button', 'js-support-ticket'))),
                (object) array('id' => 'file', 'text' => esc_html(__('Upload File', 'js-support-ticket'))),
                (object) array('id' => 'multiple', 'text' => esc_html(__('Multi Select', 'js-support-ticket'))),
                (object) array('id' => 'termsandconditions', 'text' => esc_html(__('Terms and Conditions', 'js-support-ticket'))));
        }else{
            $jsst_fieldtypes = array(
                (object) array('id' => 'text', 'text' => esc_html(__('Text Field', 'js-support-ticket'))),
                (object) array('id' => 'checkbox', 'text' => esc_html(__('Check Box', 'js-support-ticket'))),
                (object) array('id' => 'date', 'text' => esc_html(__('Date', 'js-support-ticket'))),
                (object) array('id' => 'combo', 'text' => esc_html(__('Drop Down', 'js-support-ticket'))),
                (object) array('id' => 'email', 'text' => esc_html(__('Email Address', 'js-support-ticket'))),
                (object) array('id' => 'textarea', 'text' => esc_html(__('Text Area', 'js-support-ticket'))),
                (object) array('id' => 'radio', 'text' => esc_html(__('Radio Button', 'js-support-ticket'))),
                (object) array('id' => 'depandant_field', 'text' => esc_html(__('Dependent Field', 'js-support-ticket'))),
                (object) array('id' => 'file', 'text' => esc_html(__('Upload File', 'js-support-ticket'))),
                (object) array('id' => 'multiple', 'text' => esc_html(__('Multi Select', 'js-support-ticket'))),
                (object) array('id' => 'termsandconditions', 'text' => esc_html(__('Terms and Conditions', 'js-support-ticket'))));
        }
        $jsst_fieldsize = array(
             (object) array('id' => 50, 'text' => esc_html(__('50%', 'js-support-ticket'))),
            (object) array('id' => 100, 'text' => esc_html(__('100%', 'js-support-ticket'))));
        ?>
        <?php $jsst_nonce_id = isset(jssupportticket::$jsst_data[0]['userfield']->id) ? jssupportticket::$jsst_data[0]['userfield']->id : '';?>
        <div id="jsstadmin-data-wrp">
            <?php if(isset(jssupportticket::$jsst_data['formid'])){ $jsst_mformid = jssupportticket::$jsst_data['formid']; }else{ $jsst_mformid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId(); } ?>
            <form class="jsstadmin-form" id="adminForm" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=fieldordering&task=saveuserfeild&formid=$jsst_mformid"),"save-userfeild-".$jsst_nonce_id)); ?>">
                <?php if (empty(jssupportticket::$jsst_data[0]['userfield']->id) || (!empty(jssupportticket::$jsst_data[0]['userfield']->id) && !empty(jssupportticket::$jsst_data[0]['userfield']->isuserfield))) { ?>
                    <div class="js-form-wrapper">
                        <div class="js-form-title"><?php echo esc_html(__('Field Type', 'js-support-ticket')); ?><font class="required-notifier">*</font></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('userfieldtype', $jsst_fieldtypes, isset(jssupportticket::$jsst_data[0]['userfield']->userfieldtype) ? jssupportticket::$jsst_data[0]['userfield']->userfieldtype : 'text', '', array('class' => 'inputbox one js-form-select-field', 'data-validation' => 'required', 'onchange' => 'toggleType(this.options[this.selectedIndex].value);')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <?php
                } ?>
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Field Title', 'js-support-ticket')); ?><font class="required-notifier">*</font></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('fieldtitle', isset(jssupportticket::$jsst_data[0]['userfield']->fieldtitle) ? jssupportticket::$jsst_data[0]['userfield']->fieldtitle : '', array('class' => 'inputbox one js-form-input-field', 'data-validation' => 'required')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper for-terms-condtions-hide" id="for-combo-wrapper" style="display:none;">
                    <div class="js-form-title"><?php echo esc_html(__('Select','js-support-ticket')) .'&nbsp;'. esc_html(__('Parent Field', 'js-support-ticket')); ?><font class="required-notifier">*</font></div>
                    <div class="js-form-value" id="for-combo"></div>
                </div>
                <?php 
                if (empty(jssupportticket::$jsst_data[0]['userfield']->id) || (!empty(jssupportticket::$jsst_data[0]['userfield']->field) && !in_array(jssupportticket::$jsst_data[0]['userfield']->field, ['termsandconditions1','termsandconditions2','termsandconditions3']))) { ?>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title">
                            <?php echo esc_html(__('Default Value', 'js-support-ticket')); ?>
                            <span class="js-form-subtitle" id="subtitle_defaultvalue" style="display:none;">
                                <?php echo esc_html(__('To choose a default value, first add values in the area below', 'js-support-ticket')); ?>
                            </span>
                            <span class="js-form-subtitle" id="defaultvalue_not_available" style="display:none;">
                                <?php echo esc_html(__('This option is not available in this case', 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div class="js-form-value">
                            <span class="defaultvalue_select" style="display:none;">
                                <?php echo wp_kses(JSSTformfield::select('defaultvalue_select', '', isset(jssupportticket::$jsst_data[0]['userfield']->defaultvalue) ? jssupportticket::$jsst_data[0]['userfield']->defaultvalue : '', esc_html(__('Select Default Value', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                            </span>
                            <span class="defaultvalue_input">
                                <?php echo wp_kses(JSSTformfield::text('defaultvalue_input', isset(jssupportticket::$jsst_data[0]['userfield']->defaultvalue) ? jssupportticket::$jsst_data[0]['userfield']->defaultvalue : '', array('class' => 'inputbox one js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                            </span>
                        </div>
                    </div>
                    <?php 
                }
                if (empty(jssupportticket::$jsst_data[0]['userfield']->cannotunpublish)) { ?>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title"><?php echo esc_html(__('User Published', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('published', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->published) ? jssupportticket::$jsst_data[0]['userfield']->published : 1, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <div class="js-form-wrapper for-terms-condtions-hide for-admin-only-hide">
                        <div class="js-form-title"><?php echo esc_html(__('Visitor Published', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('isvisitorpublished', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->isvisitorpublished) ? jssupportticket::$jsst_data[0]['userfield']->isvisitorpublished : 1, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <?php
                }
                if (empty(jssupportticket::$jsst_data[0]['userfield']->cannotsearch)) { ?>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title">
                            <?php echo esc_html(__('Admin Search', 'js-support-ticket')); ?>
                            <span class="js-form-subtitle" id="subtitle_adminSearch" style="display:none;">
                                <?php echo esc_html(__('This option is not available in this case', 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('search_admin', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->search_admin) ? jssupportticket::$jsst_data[0]['userfield']->search_admin : 1, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title">
                            <?php echo esc_html(__('User Search', 'js-support-ticket')); ?>
                            <span class="js-form-subtitle" id="subtitle_userSearch" style="display:none;">
                                <?php echo esc_html(__('This option is not available in this case', 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('search_user', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->search_user) ? jssupportticket::$jsst_data[0]['userfield']->search_user : 1, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <?php
                }
                if (empty(jssupportticket::$jsst_data[0]['userfield']->cannotshowonlisting)) { ?>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title"><?php echo esc_html(__('Show On Listing', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('showonlisting', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->showonlisting) ? jssupportticket::$jsst_data[0]['userfield']->showonlisting : 0, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <?php
                }
                if (empty(jssupportticket::$jsst_data[0]['userfield']->id) || (!empty(jssupportticket::$jsst_data[0]['userfield']->field) && !in_array(jssupportticket::$jsst_data[0]['userfield']->field, ['termsandconditions1','termsandconditions2','termsandconditions3']))) { ?>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title"><?php echo esc_html(__('Required', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('required', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->required) ? jssupportticket::$jsst_data[0]['userfield']->required : 0, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <?php
                } ?>
                
                
                <?php
                // visitor search is not in use
                /*<div class="js-form-wrapper for-terms-condtions-hide for-admin-only-hide">
                    <div class="js-form-title"><?php echo esc_html(__('Visitor Search', 'js-support-ticket')); </div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('search_visitor', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->search_visitor) ? jssupportticket::$jsst_data[0]['userfield']->search_visitor : 1, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); </div>
                </div>
                */
                if (!empty(jssupportticket::$jsst_data[0]['userfield']->field) && !in_array(jssupportticket::$jsst_data[0]['userfield']->field, ['termsandconditions1','termsandconditions2','termsandconditions3'])) { ?>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title">
                            <?php echo esc_html(__('Place Holder', 'js-support-ticket')); ?>
                            <span class="js-form-subtitle" id="placeholder_not_available" style="display:none;">
                                <?php echo esc_html(__('This option is not available in this case', 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('placeholder', isset(jssupportticket::$jsst_data[0]['userfield']->placeholder) ? jssupportticket::$jsst_data[0]['userfield']->placeholder : '', array('class' => 'inputbox one js-form-input-field','maxlength'=>225)), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title"><?php echo esc_html(__('Description', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('description', isset(jssupportticket::$jsst_data[0]['userfield']->description) ? jssupportticket::$jsst_data[0]['userfield']->description : '', array('class' => 'inputbox one js-form-input-field','maxlength'=>225)), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title">
                            <?php echo esc_html(__('Read Only', 'js-support-ticket')); ?>
                            <span class="js-form-subtitle" id="subtitle_readOnly" style="display:none;">
                                <?php echo esc_html(__('This option is not available in this case', 'js-support-ticket')); ?>
                            </span>
                        </div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('readonly', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->readonly) ? jssupportticket::$jsst_data[0]['userfield']->readonly : 0, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <?php 
                    if (empty(jssupportticket::$jsst_data[0]['userfield']->cannotunpublish)) { ?>
                        <div class="js-form-wrapper for-terms-condtions-hide">
                            <div class="js-form-title"><?php echo esc_html(__('Admin/Agent Only', 'js-support-ticket')); ?></div>
                            <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('adminonly', $jsst_yesno, isset(jssupportticket::$jsst_data[0]['userfield']->adminonly) ? jssupportticket::$jsst_data[0]['userfield']->adminonly : 0, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                        </div>
                        <?php 
                    }
                }
                if (empty(jssupportticket::$jsst_data[0]['userfield']->id) || (!empty(jssupportticket::$jsst_data[0]['userfield']->id) && !empty(jssupportticket::$jsst_data[0]['userfield']->isuserfield))) { ?>
                    <div class="js-form-wrapper for-terms-condtions-hide">
                        <div class="js-form-title"><?php echo esc_html(__('Field Size', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('size', $jsst_fieldsize, isset(jssupportticket::$jsst_data[0]['userfield']->size) ? jssupportticket::$jsst_data[0]['userfield']->size : 0, '', array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <?php
                }
                 /*
                <div class="js-form-wrapper">
                    <div class="js-form-title"><?php echo esc_html(__('Java Script', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::textarea('j_script', isset(jssupportticket::$jsst_data[0]['userfield']->j_script) ? jssupportticket::$jsst_data[0]['userfield']->j_script : '', array('class' => 'inputbox one jsstadmin-form-textarea-field')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                */ ?>
                <div id="for-combo-options" >
                    <?php
                    $jsst_arraynames = '';
                    $jsst_comma = '';
                    if (isset(jssupportticket::$jsst_data[0]['userfieldparams']) && jssupportticket::$jsst_data[0]['userfield']->userfieldtype == 'depandant_field') {
                        foreach (jssupportticket::$jsst_data[0]['userfieldparams'] as $jsst_key => $jsst_val) {
                            $jsst_textvar = $jsst_key;
                            $jsst_textvar = jssupportticketphplib::JSST_str_replace(' ','__',$jsst_textvar);
                            $jsst_textvar = jssupportticketphplib::JSST_str_replace('.','___',$jsst_textvar);
                            $jsst_divid = $jsst_textvar;
                            $jsst_textvar .='[]';
                            $jsst_arraynames .= $jsst_comma . "$jsst_key";
                            $jsst_comma = '_JSST_Unique_88a9e3_';
                            ?>
                            <div class="jsst-user-dd-field-wrap">
                                <div class="jsst-user-dd-field-title">
                                    <?php echo esc_html($jsst_key); ?>
                                </div>
                                <div class="jsst-user-dd-field-value combo-options-fields" id="<?php echo esc_attr($jsst_divid); ?>">
                                    <?php
                                    if (!empty($jsst_val)) {
                                        foreach ($jsst_val as $jsst_each) {
                                            ?>
                                            <span class="input-field-wrapper">
                                                <input name="<?php echo esc_attr($jsst_textvar); ?>" id="<?php echo esc_attr($jsst_textvar); ?>" value="<?php echo esc_attr($jsst_each); ?>" class="inputbox one user-field" type="text">
                                                <img alt = "<?php echo esc_attr(__('Delete', 'js-support-ticket')); ?>" class="input-field-remove-img" src="<?php echo esc_url(JSST_PLUGIN_URL) ?>includes/images/delete.png">
                                            </span><?php
                                        }
                                    }
                                    // $jsst_safe_divid = wp_json_encode($jsst_divid, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                    $jsst_js_value = esc_js($jsst_divid);
                                    $jsst_js_value = ($jsst_divid);
                                    ?>
                                    <input id="depandant-field-button" class="jsst-button-link button user-field-val-button" onclick="getNextField('<?php echo esc_js($jsst_js_value); ?>', this);" value="<?php echo esc_attr(__('Add More', 'js-support-ticket')); ?>" type="button">
                                </div>
                            </div><?php
                        }
                    }
                    ?>
                </div>
                <?php 
                if (empty(jssupportticket::$jsst_data[0]['userfield']->id) || (!empty(jssupportticket::$jsst_data[0]['userfield']->id) && !empty(jssupportticket::$jsst_data[0]['userfield']->isuserfield))) { ?>
                    <div id="divText" class="js-form-wrapper">
                        <div class="js-form-title"><?php echo esc_html(__('Max Length', 'js-support-ticket')); ?></div>
                        <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('maxlength', isset(jssupportticket::$jsst_data[0]['userfield']->maxlength) ? jssupportticket::$jsst_data[0]['userfield']->maxlength : '', array('class' => 'inputbox one js-form-input-field')), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <?php 
                } ?>
                <div class="js-form-wrapper divColsRows">
                    <div class="js-form-title"><?php echo esc_html(__('Columns', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('cols', isset(jssupportticket::$jsst_data[0]['userfield']->cols) ? jssupportticket::$jsst_data[0]['userfield']->cols : '', array('class' => 'inputbox one js-form-input-field')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <div class="js-form-wrapper divColsRows">
                    <div class="js-form-title"><?php echo esc_html(__('Rows', 'js-support-ticket')); ?></div>
                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('rows', isset(jssupportticket::$jsst_data[0]['userfield']->rows) ? jssupportticket::$jsst_data[0]['userfield']->rows : '', array('class' => 'inputbox one js-form-input-field')), JSST_ALLOWED_TAGS); ?></div>
                </div>
                <?php
                if(!empty(jssupportticket::$jsst_data[0]['userfield']->field) && in_array(jssupportticket::$jsst_data[0]['userfield']->field, ['termsandconditions1','termsandconditions2','termsandconditions3']) || empty(jssupportticket::$jsst_data[0]['userfield']->id) || !empty(jssupportticket::$jsst_data[0]['userfield']->isuserfield)) { ?>
                    <div class="for-terms-condtions-show" >
                        <?php
                        $jsst_termsandconditions_text = '';
                        $jsst_termsandconditions_linktype = '';
                        $jsst_termsandconditions_link = '';
                        $jsst_termsandconditions_page = '';
                        if( isset(jssupportticket::$jsst_data[0]['userfieldparams']) && jssupportticket::$jsst_data[0]['userfieldparams'] != '' && is_array(jssupportticket::$jsst_data[0]['userfieldparams']) && !empty(jssupportticket::$jsst_data[0]['userfieldparams'])){
                            $jsst_termsandconditions_text = isset(jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_text']) ? jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_text'] :'' ;
                            $jsst_termsandconditions_linktype = isset(jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_linktype']) ? jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_linktype'] :'' ;
                            $jsst_termsandconditions_link = isset(jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_link']) ? jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_link'] :'' ;
                            $jsst_termsandconditions_page = isset(jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_page']) ? jssupportticket::$jsst_data[0]['userfieldparams']['termsandconditions_page'] :'' ;
                        } ?>
                        <div class="js-form-wrapper ">
                            <div class="js-form-title"><?php echo esc_html(__('Terms and Conditions Text', 'js-support-ticket')); ?></div>
                            <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('termsandconditions_text', $jsst_termsandconditions_text , array('class' => 'inputbox one js-form-input-field')), JSST_ALLOWED_TAGS); ?></div>
                            <div class="js-form-desc">
                                <?php echo esc_html(__("e.g ' I have read and agree to the [link] Terms and Conditions[/link].  ' The text between [link] and [/link] will be linked to provided url or wordpress page.", 'js-support-ticket')); ?>
                            </div>
                        </div>
                        <div class="js-form-wrapper ">
                            <div class="js-form-title"><?php echo esc_html(__('Terms and Conditions Link Type', 'js-support-ticket')); ?></div>
                            <?php
                            $jsst_linktype = array(
                                (object) array('id' => 1, 'text' => esc_html(__('Direct Link', 'js-support-ticket'))),
                                (object) array('id' => 2, 'text' => esc_html(__('Wordpress Page', 'js-support-ticket'))),
                                (object) array('id' => 3, 'text' => esc_html(__('None', 'js-support-ticket'))));
                            ?>
                            <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('termsandconditions_linktype', $jsst_linktype, $jsst_termsandconditions_linktype, esc_html(__('Select Link Type', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                        </div>
                        <div class="js-form-wrapper for-terms-condtions-linktype1" style="display: none;">
                            <div class="js-form-title"><?php echo esc_html(__('Terms and Conditions Link', 'js-support-ticket')); ?></div>
                            <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('termsandconditions_link', $jsst_termsandconditions_link , array('class' => 'inputbox one js-form-input-field')), JSST_ALLOWED_TAGS); ?></div>
                        </div>
                        <div class="js-form-wrapper for-terms-condtions-linktype2" style="display: none;">
                            <div class="js-form-title"><?php echo esc_html(__('Terms and Conditions Page', 'js-support-ticket')); ?></div>
                            <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('termsandconditions_page', JSSTincluder::getJSModel('configuration')->getPageList(), $jsst_termsandconditions_page, esc_html(__('Select Wordpress page','js-support-ticket')), array('class' => 'inputbox one js-form-select-field')), JSST_ALLOWED_TAGS); ?></div>
                        </div>
                    </div>
                    <?php 
                } ?>
                <?php if (empty(jssupportticket::$jsst_data[0]['userfield']->id) || (!empty(jssupportticket::$jsst_data[0]['userfield']->id) && !empty(jssupportticket::$jsst_data[0]['userfield']->isuserfield))) { ?>
                    <div class="js-form-wrapper js-form-visible-wrapper" style="border: unset;background: unset;font-size: 17px;margin: 0;">
                        <?php echo esc_html(__('Visibility conditions', 'js-support-ticket')); ?>
                    </div>
                    <?php 
                    if( empty(jssupportticket::$jsst_data[0]['userfield']->visibleparams)) { ?>
                        <div class="js-form-wrapper js-form-visible-wrapper" id="js_and_row_0">
                            <div class="js-form-value" id="js_or_row_0">
                                <?php echo wp_kses(JSSTformfield::select('visibleParent[]', JSSTincluder::getJSModel('fieldordering')->getFieldsForVisibleCombobox(jssupportticket::$jsst_data['fieldfor'], $jsst_mformid, isset(jssupportticket::$jsst_data[0]['userfield']->field) ? jssupportticket::$jsst_data[0]['userfield']->field : '', isset(jssupportticket::$jsst_data[0]['userfield']->id) ? jssupportticket::$jsst_data[0]['userfield']->id : ''), '', esc_html(__('Select Parent', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field js-form-input-field-visible', 'onchange' => 'getChildForVisibleCombobox(this.value, 0);getConditionsForVisibleCombobox(this.value, 0);')), JSST_ALLOWED_TAGS); ?>
                                <span class="visibleValueWrp">
                                    <?php echo wp_kses(JSSTformfield::select('visibleValue[]', '', '', esc_html(__('Select Child', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS); ?>
                                </span>
                                <span class="visibleConditionWrp">
                                    <?php echo wp_kses(JSSTformfield::select('visibleCondition[]', $jsst_equalnotequal, '', esc_html(__('Select Condition', 'js-support-ticket')), array('class' => 'inputbox one js-form-select-field js-form-input-field-visible')), JSST_ALLOWED_TAGS); ?>
                                </span>
                                <?php echo wp_kses(JSSTformfield::hidden('visibleLogic[]', 'AND'), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-form-visible-or-row"></div>
                            <div class="js-visible-conditions-addbtn-wrp">
                                <span class="js-form-visible-addmore" onclick="getMoreORRow(this, <?php echo esc_js( jssupportticket::$jsst_data['fieldfor'] ); ?>, <?php echo esc_js( $jsst_mformid ); ?>, '<?php echo isset( jssupportticket::$jsst_data[0]['userfield']->field ) ? esc_js( jssupportticket::$jsst_data[0]['userfield']->field ) : ''; ?>', '<?php echo isset( jssupportticket::$jsst_data[0]['userfield']->id ) ? esc_js( jssupportticket::$jsst_data[0]['userfield']->id ) : ''; ?>')">
                                    <img alt = "<?php echo esc_attr(__('OR', 'js-support-ticket')); ?>" class="input-field-remove-img" src="<?php echo esc_url(JSST_PLUGIN_URL) ?>includes/images/plus-icon.png">
                                    <?php echo esc_html(__('OR', 'js-support-ticket')); ?>
                                </span>
                            </div>
                        </div>
                        <?php 
                    } else {
                        if (!empty(jssupportticket::$jsst_data[0]['userfield']->visibleparams)) {
                            $jsst_androws = json_decode(jssupportticket::$jsst_data[0]['userfield']->visibleparams);
                            $jsst_nextorid = 0;
                            foreach ($jsst_androws as $jsst_androwindex => $jsst_androw) { ?>
                                <div class="js-form-visible-andwrp" id="js_and_row_<?php echo esc_attr( $jsst_androwindex ); ?>">
                                    <?php
                                    if ($jsst_androwindex != 0) { ?>
                                        <div class="js-form-visible-subheading">
                                            <?php echo esc_html(__('AND', 'js-support-ticket')); ?>
                                        </div>
                                        <?php 
                                    } ?>
                                    <div class="js-form-wrapper js-form-visible-wrapper">
                                        <?php 
                                        foreach ($jsst_androw as $jsst_orrowindex => $jsst_orrow) { ?>
                                            <div id="js_or_row_<?php echo esc_attr( $jsst_nextorid ); ?>" >
                                                <?php
                                                if ($jsst_orrowindex != 0) { ?>
                                                    <div class="js-form-visible-subheading">
                                                        <?php echo esc_html(__('OR', 'js-support-ticket')); ?>
                                                    </div>
                                                    <?php 
                                                } ?>
                                                <div class="js-form-value">
                                                    <?php echo wp_kses(JSSTformfield::select('visibleParent[]', JSSTincluder::getJSModel('fieldordering')->getFieldsForVisibleCombobox(jssupportticket::$jsst_data['fieldfor'], $jsst_mformid, jssupportticket::$jsst_data[0]['userfield']->field, jssupportticket::$jsst_data[0]['userfield']->id), $jsst_orrow->visibleParent, esc_html(__('Select Parent', 'js-support-ticket')), array('class' => 'inputbox js-form-select-field js-form-input-field-visible', 'onchange' => 'getChildForVisibleCombobox(this.value, '.$jsst_nextorid.');getConditionsForVisibleCombobox(this.value, '.$jsst_nextorid.');')), JSST_ALLOWED_TAGS); ?>
                                                    <span class="visibleValueWrp">
                                                        <?php echo wp_kses(html_entity_decode(JSSTincluder::getJSModel('fieldordering')->getChildForVisibleCombobox($jsst_orrow->visibleParent, $jsst_orrow->visibleValue)), JSST_ALLOWED_TAGS); ?>
                                                    </span>
                                                    <span class="visibleConditionWrp">
                                                        <?php echo wp_kses(html_entity_decode(JSSTincluder::getJSModel('fieldordering')->getConditionsForVisibleCombobox($jsst_orrow->visibleParent, $jsst_orrow->visibleCondition)), JSST_ALLOWED_TAGS); ?>
                                                    </span>
                                                    <div class="js-visible-conditions-body-row">
                                                        <div class="js-visible-conditions-body-value">
                                                            <span onclick='deleteOrRow("js_or_row_<?php echo esc_js( $jsst_nextorid ); ?>")' class="js-visible-conditions-delbtn">
                                                                <img class="input-field-remove-img" src="<?php echo esc_url( JSST_PLUGIN_URL . 'includes/images/delete-2.png' ); ?>" />
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <?php echo wp_kses(JSSTformfield::hidden('visibleLogic[]', $jsst_orrow->visibleLogic), JSST_ALLOWED_TAGS); ?>
                                                </div>
                                            </div>
                                            <?php
                                            $jsst_nextorid++;
                                            $jsst_jssupportticket_js = "
                                                nextorid++;
                                            ";
                                            wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
                                        } ?>
                                        <div class="js-form-visible-or-row"></div>
                                        <div class="js-visible-conditions-addbtn-wrp">
                                            <span class="js-form-visible-addmore" onclick="getMoreORRow(this, <?php echo esc_js( jssupportticket::$jsst_data['fieldfor'] ); ?>, <?php echo esc_js( $jsst_mformid ); ?>, '<?php echo isset( jssupportticket::$jsst_data[0]['userfield']->field ) ? esc_js( jssupportticket::$jsst_data[0]['userfield']->field ) : ''; ?>', '<?php echo isset( jssupportticket::$jsst_data[0]['userfield']->id ) ? esc_js( jssupportticket::$jsst_data[0]['userfield']->id ) : ''; ?>')">
                                                <img alt = "<?php echo esc_attr(__('OR', 'js-support-ticket')); ?>" class="input-field-remove-img" src="<?php echo esc_url(JSST_PLUGIN_URL) ?>includes/images/plus-icon.png">
                                                <?php echo esc_html(__('OR', 'js-support-ticket')); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $jsst_jssupportticket_js = "
                                    nextandid++;
                                ";
                                wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
                            }
                        }
                    } ?>
                    <div class="js-form-visible-add-row"></div>
                    <div class="js-visible-conditions-addbtn-wrp">
                        <span class="js-form-visible-addmore" onclick="getMoreANDRow(<?php echo esc_js( jssupportticket::$jsst_data['fieldfor'] ); ?>, <?php echo esc_js( $jsst_mformid ); ?>, '<?php echo esc_js( isset( jssupportticket::$jsst_data[0]['userfield']->field ) ? jssupportticket::$jsst_data[0]['userfield']->field : '' ); ?>', '<?php echo esc_js( isset( jssupportticket::$jsst_data[0]['userfield']->id ) ? jssupportticket::$jsst_data[0]['userfield']->id : '' ); ?>')">
                            <img alt = "<?php echo esc_attr(__('AND', 'js-support-ticket')); ?>" class="input-field-remove-img" src="<?php echo esc_url(JSST_PLUGIN_URL) ?>includes/images/plus-icon.png">
                            <?php echo esc_html(__('Add new', 'js-support-ticket')).' "'.esc_html(__('AND', 'js-support-ticket')).'" '.esc_html(__('visibility condition', 'js-support-ticket')); ?>
                        </span>
                    </div>
                <?php } ?>
                <div id="divValues" class="jsstadmin-add-user-fields-wrp divColsRowsno-margin">
                    <h3 class="jsstadmin-add-user-fields-title"><?php echo esc_html(__('Use the table below to add new values', 'js-support-ticket')); ?></h3>
                    <div class="page-actions no-margin">
                        <div id="user-field-values" class="white-background" class="no-padding">
                            <?php
                            if (isset(jssupportticket::$jsst_data[0]['userfield']) && jssupportticket::$jsst_data[0]['userfield']->userfieldtype != 'depandant_field') {
                                if (isset(jssupportticket::$jsst_data[0]['userfieldparams']) && !empty(jssupportticket::$jsst_data[0]['userfieldparams'])) {
                                    foreach (jssupportticket::$jsst_data[0]['userfieldparams'] as $jsst_key => $jsst_val) {
                                        ?>
                                        <span class="input-field-wrapper">
                                            <?php echo wp_kses(JSSTformfield::text('values['.esc_attr($jsst_val).']', isset($jsst_val) ? $jsst_val : '', array('class' => 'inputbox one user-field', 'onchange' => 'updateSelectOptionsForDefaultValues()')), JSST_ALLOWED_TAGS); ?>
                                            <img alt = "<?php echo esc_attr(__('Delete', 'js-support-ticket')); ?>" class="input-field-remove-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" />
                                        </span>
                                    <?php
                                    }
                                } else {
                                    $jsst_val = isset($jsst_val) ? $jsst_val : '';
                                    ?>
                                    <span class="input-field-wrapper">
                                    <?php echo wp_kses(JSSTformfield::text('values['.esc_attr($jsst_val).']', $jsst_val, array('class' => 'inputbox one user-field', 'onchange' => 'updateSelectOptionsForDefaultValues()')), JSST_ALLOWED_TAGS); ?>
                                        <img alt = "<?php echo esc_attr(__('Delete', 'js-support-ticket')); ?>" class="input-field-remove-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete.png" />
                                    </span>
                                <?php
                                }
                            }
                            ?>
                            <a title="<?php echo esc_attr(__('Add Value', 'js-support-ticket')); ?>" class="jsst-button-link button user-field-val-button" id="user-field-val-button" onclick="insertNewRow();"><?php echo esc_html(__('Add Value', 'js-support-ticket')); ?></a>
                        </div>
                    </div>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('multiformid', $jsst_mformid), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('id', isset(jssupportticket::$jsst_data[0]['userfield']->id) ? jssupportticket::$jsst_data[0]['userfield']->id : ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('fieldfor', jssupportticket::$jsst_data['fieldfor']), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('ordering', isset(jssupportticket::$jsst_data[0]['userfield']->ordering) ? jssupportticket::$jsst_data[0]['userfield']->ordering : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('isuserfield', isset(jssupportticket::$jsst_data[0]['userfield']->id) ? jssupportticket::$jsst_data[0]['userfield']->isuserfield : 1), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('fieldname', isset(jssupportticket::$jsst_data[0]['userfield']->field) ? jssupportticket::$jsst_data[0]['userfield']->field : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('depandant_field', isset(jssupportticket::$jsst_data[0]['userfield']->depandant_field) ? jssupportticket::$jsst_data[0]['userfield']->depandant_field : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('field', isset(jssupportticket::$jsst_data[0]['userfield']->field) ? jssupportticket::$jsst_data[0]['userfield']->field : '' ), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('arraynames2', $jsst_arraynames), JSST_ALLOWED_TAGS); ?>
                <div class="js-form-button">
                    <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Field', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
                </div>
            </form>
        </div>
        <?php
        $jsst_js_scriptdateformat = JSSTincluder::getJSModel('jssupportticket')->getJSSTDateFormat();
        $jsst_jssupportticket_js ='
            jQuery(document).ready(function () {
                toggleType(jQuery("select#userfieldtype").val());
                updateSelectOptionsForDefaultValues();
                jQuery("#termsandconditions_linktype").on("change", function() {
                    if(this.value == 1){
                        jQuery(".for-terms-condtions-linktype1").slideDown();
                        jQuery(".for-terms-condtions-linktype2").hide();
                    }else if(this.value == 2){
                        jQuery(".for-terms-condtions-linktype1").hide();
                        jQuery(".for-terms-condtions-linktype2").slideDown();
                    }else{
                        jQuery(".for-terms-condtions-linktype1").hide();
                        jQuery(".for-terms-condtions-linktype2").hide();
                    }
                });

                var intial_val = jQuery("#termsandconditions_linktype").val();
                if(intial_val == 1){
                    jQuery(".for-terms-condtions-linktype1").slideDown();
                    jQuery(".for-terms-condtions-linktype2").hide();
                }else if(intial_val == 2){
                    jQuery(".for-terms-condtions-linktype1").hide();
                    jQuery(".for-terms-condtions-linktype2").slideDown();
                }else{
                    jQuery(".for-terms-condtions-linktype1").hide();
                    jQuery(".for-terms-condtions-linktype2").hide();
                }
            });
            function disableAll() {
                jQuery("#divValues").slideUp();
                jQuery(".divColsRows").slideUp();
                jQuery("#divText").slideUp();
            }
            function toggleType(type) {
                enableDefaultValueFieldDueToValues();
                if(type == "combo" || type == "radio" || type == "multiple" || type == "checkbox") {
                    // enableDefaultValueFieldDueToValues();
                    // jQuery("#defaultvalue_not_available").hide();
                    // jQuery(".defaultvalue_input").hide();
                    // jQuery(".defaultvalue_select").show();
                    // jQuery("#subtitle_defaultvalue").show();
                } else {
                    // recheck
                    // enableDefaultValueField();
                    if(type == "date") {
                        jQuery("#defaultvalue_input").addClass("custom_date js-form-date-field");
                        jQuery(".custom_date").datepicker({dateFormat: "'. esc_html($jsst_js_scriptdateformat) .'"});
                        enableDefaultValueField();
                    } else if (type == "depandant_field") {
                        disableDefaultValueField();
                    } else if (type == "file" || type == "termsandconditions") {
                        disableDefaultValueField();
                        disableAdminSearchField();
                        disableUserSearchField();
                        disableReadOnlyField();
                    } else {
                        enableDefaultValueField();
                    }
                }
                // code for placeholder
                if(type == "checkbox" || type == "combo" || type == "radio" || type == "depandant_field" || type == "file" || type == "multiple" || type == "termsandconditions") {
                    disablePlaceholderField();
                } else {
                    enablePlaceholderField();
                }
                disableAll();
                //prep4SQL(document.forms["adminForm"].elements["field"]);
                selType(type);
            }
            function prep4SQL(field) {
                if (field.value != "") {
                    field.value = field.value.replace("js_", "");
                    field.value = "js_" + field.value.replace(/[^a-zA-Z]+/g, "");
                }
            }
            function selType(sType) {
                var elem;
                /*
                 text
                 checkbox
                 date
                 combo
                 email
                 textarea
                 radio
                 editor
                 depandant_field
                 multiple*/

                switch (sType) {
                    case "editor":
                        jQuery("div.for-terms-condtions-hide").show();
                        jQuery("#divText").slideUp();
                        jQuery("#divValues").slideUp();
                        jQuery(".divColsRows").slideUp();
                        jQuery("div#for-combo-wrapper").hide();
                        jQuery("div#for-combo-options").hide();
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("div.for-terms-condtions-show").slideUp();
                        break;
                    case "textarea":
                        jQuery("div.for-terms-condtions-hide").show();
                        jQuery("#divText").slideUp();
                        jQuery(".divColsRows").slideDown();
                        jQuery("#divValues").slideUp();
                        jQuery("div#for-combo-wrapper").hide();
                        jQuery("div#for-combo-options").hide();
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("div.for-terms-condtions-show").slideUp();
                        break;
                    case "email":
                    case "password":
                    case "text":
                    case "date":
                        jQuery("div.for-terms-condtions-hide").show();
                        jQuery("#divText").slideDown();
                        jQuery("div#for-combo-wrapper").hide();
                        jQuery("div#for-combo-options").hide();
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("div.for-terms-condtions-show").slideUp();
                        break;
                    case "file":
                        jQuery("div.for-terms-condtions-hide").show();
                        jQuery("#divText").slideUp();
                        jQuery("div#for-combo-wrapper").hide();
                        jQuery("div#for-combo-options").hide();
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("div.for-terms-condtions-show").slideUp();
                        break;
                    case "termsandconditions":
                        jQuery("div#for-combo-wrapper").hide();
                        jQuery("div#for-combo-options").hide();
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("#divText").slideUp();
                        jQuery(".divColsRows").slideUp();
                        jQuery("#divValues").slideUp();
                        jQuery("div#for-combo-wrapper").hide();
                        jQuery("div#for-combo-options").hide();
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("div.for-terms-condtions-hide").hide();
                        jQuery("div.for-terms-condtions-show").slideDown();
                        break;
                    case "combo":
                    case "multiple":
                        jQuery("div.for-terms-condtions-hide").show();
                        jQuery("#divValues").slideDown();
                        jQuery("div#for-combo-wrapper").hide();
                        jQuery("div#for-combo-options").hide();
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("div.for-terms-condtions-show").slideUp();
                        break;
                    case "depandant_field":
                        jQuery("div.for-terms-condtions-hide").show();
                        comboOfFields();
                        jQuery("div.for-terms-condtions-show").slideUp();
                        break;
                    case "radio":
                    case "checkbox":
                        jQuery("div.for-terms-condtions-hide").show();
                        //jQuery(".divColsRows").slideDown();
                        jQuery("#divValues").slideDown();
                        jQuery("div#for-combo-wrapper").hide();
                        jQuery("div#for-combo-options").hide();
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("div.for-terms-condtions-show").slideUp();
                        /*
                         if (elem=getObject("jsNames[0]")) {
                         elem.setAttribute("mosReq",1);
                         }
                         */
                        break;
                    case "delimiter":
                    default:
                }
                return;
            }
            function comboOfFields() {
                ajaxurl = "'. esc_url(admin_url("admin-ajax.php")).'";
                var formid = jQuery("input#multiformid").val();
                var ff = jQuery("input#fieldfor").val();
                var pf = jQuery("input#fieldname").val();
                jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "fieldordering", task: "getFieldsForComboByFieldFor", formid : formid, fieldfor: ff,parentfield:pf, "_wpnonce":"'. wp_create_nonce("get-fields-for-combo-by-fieldfor") .'"}, function (data) {
                    if (data) {
                        console.log(data);
                        var d = jQuery.parseJSON(data);
                        jQuery("div#for-combo").html(jsstDecodeHTML(d));
                        jQuery("div#for-combo-wrapper").show();
                    }
                });
            }
            function getDataOfSelectedField(nonce) {
                ajaxurl = "'.esc_url(admin_url("admin-ajax.php")).'";
                var field = jQuery("select#parentfield").val();
                var ff = jQuery("input#fieldfor").val();
                jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "fieldordering", task: "getSectionToFillValues", pfield: field, fieldfor: ff, "_wpnonce": nonce}, function (data) {
                    if (data) {
                        var d = jQuery.parseJSON(data);
                        jQuery("div#for-combo-options-head").show();
                        jQuery("div#for-combo-options").html(jsstDecodeHTML(d));
                        jQuery("div#for-combo-options").show();
                    }else{
                        jQuery("div#for-combo-options-head").hide();
                        jQuery("div#for-combo-options").html();
                        jQuery("div#for-combo-options").hide();
                    }
                });
            }
            function getNextField(divid, object) {
                console.log(divid);
                let cleandivid = divid.replaceAll("[", "").replaceAll("]", "");
                var textvar = cleandivid + "[]";

                // Create elements safely using jQuery
                var wrapper = jQuery(\'<span class="input-field-wrapper"></span>\');
                var input = jQuery(\'<input>\', {
                    type: \'text\',
                    name: textvar,
                    class: \'inputbox one user-field\'
                });

                var img = jQuery(\'<img>\', {
                    alt: \'Delete\',
                    class: \'input-field-remove-img\',
                    src: \''. esc_url(JSST_PLUGIN_URL) . 'includes/images/delete.png\'
                });

                wrapper.append(input).append(img);

                jQuery(object).before(wrapper);
            }
            function getNextField01(divid, object) {
                console.log(divid);
                var textvar = divid + "[]";
                console.log(textvar);
                var fieldhtml = "<span class=\'input-field-wrapper\' ><input type=\'text\' name=\'" + textvar + "\' class=\'inputbox one user-field\'  /><img alt=\''. esc_html(__("Delete", "js-support-ticket")) .'\' class=\'input-field-remove-img\' src=\''.esc_url(JSST_PLUGIN_URL).'includes/images/delete.png\' /></span>";
                jQuery(object).before(fieldhtml);
            }
            function getObject(obj) {
                var strObj;
                if (document.all) {
                    strObj = document.all.item(obj);
                } else if (document.getElementById) {
                    strObj = document.getElementById(obj);
                }
                return strObj;
            }

            function updateSelectOptionsForDefaultValues() {
                var select = jQuery("select#defaultvalue_select");
                select.empty(); // Clear existing options

                var defaultvalueRaw = jQuery("#defaultvalue_input").val();
                var defaultvalue = typeof defaultvalueRaw === "string" ? defaultvalueRaw.trim() : "";

                select.append(jQuery("<option>", {
                    value: "",
                    text: "'. esc_html(__("Select Default Value", "js-support-ticket")) .'"
                }));
                var value_count = 0;

                jQuery(".user-field").each(function () {
                    var val = jQuery(this).val().trim();
                    if (val !== "") {
                        var option = jQuery("<option>", {
                            value: val,
                            text: val
                        });

                        if (val === defaultvalue) {
                            option.prop("selected", true);
                        }

                        select.append(option);
                        jQuery("#subtitle_defaultvalue").hide();
                        value_count++;
                    }
                });
                if(value_count == 0){
                    disableDefaultValueFieldDueToValues();
                    // jQuery("#subtitle_defaultvalue").show();
                }
            }

            function insertNewRow() {
                var fieldhtml = "<span class=\'input-field-wrapper\' ><input onchange=\'updateSelectOptionsForDefaultValues();\' name=\'values[]\' id=\'values[]\' value=\'\' class=\'inputbox one user-field\' type=\'text\' /><img alt=\''. esc_html(__("Delete", "js-support-ticket")).'\' class=\'input-field-remove-img\' src=\''.esc_url(JSST_PLUGIN_URL).'includes/images/delete.png\' /></span>";
                jQuery("#user-field-val-button").before(fieldhtml);
            }
            jQuery(document).ready(function () {
                jQuery("body").delegate("img.input-field-remove-img", "click", function () {
                    jQuery(this).parent().remove();
                    updateSelectOptionsForDefaultValues();
                });
            });
        ';
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        // in case of system fields
        // in case of custom fields
        if (isset(jssupportticket::$jsst_data[0]['userfield']->field)) {
            // system fields where "default value" is not in use
            $jsst_jssupportticket_js ='';
            if (in_array(jssupportticket::$jsst_data[0]['userfield']->field, ['users', 'department', 'helptopic', 'priority', 'premade', 'attachments', 'product', 'eddorderid', 'envatopurchasecode', 'eddproductid'])) {
                $jsst_jssupportticket_js .='
                    jQuery(document).ready(function () {
                        disableDefaultValueField();
                    });
                ';
            }
            // system fields where "placeholder" is not in use
            if (in_array(jssupportticket::$jsst_data[0]['userfield']->field, ['department', 'helptopic', 'priority', 'issuesummary', 'attachments', 'product', 'eddorderid', 'envatopurchasecode', 'eddproductid'])) {
                $jsst_jssupportticket_js .='
                    jQuery(document).ready(function () {
                        disablePlaceholderField();
                    });
                ';
            }
            // system fields where "read only" is not in use
            if (in_array(jssupportticket::$jsst_data[0]['userfield']->field, ['premade'])) {
                $jsst_jssupportticket_js .='
                    jQuery(document).ready(function () {
                        disableReadOnlyField();
                    });
                ';
            }
            // custom fields where "placeholder" is not in use
            if (in_array(jssupportticket::$jsst_data[0]['userfield']->userfieldtype, ['checkbox', 'combo', 'radio', 'depandant_field', 'file', 'multiple', 'termsandconditions'])) {
                $jsst_jssupportticket_js .='
                    jQuery(document).ready(function () {
                        disablePlaceholderField();
                    });
                ';
            }
            wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        }
        ?>
    </div>
</div>
