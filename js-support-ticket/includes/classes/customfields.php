<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTcustomfields {
    function formCustomFields($jsst_field) {
        if($jsst_field->isuserfield != 1){
            return false;
        }
        // Handle adminonly case
        // Visible only on admin and agent form
        if( in_array('agent',jssupportticket::$_active_addons) ){
            $jsst_agent = JSSTincluder::getJSModel('agent')->isUserStaff();
        }else{
            $jsst_agent = false;
        }
        if(!empty($jsst_field->adminonly) && !is_admin() && !$jsst_agent){
            return false;
        }
        // show termsandconditions only on user form
        if($jsst_field->userfieldtype == 'termsandconditions' && (is_admin() || $jsst_agent)){
            return false;
        }
        $jsst_cssclass = "";
        $jsst_visibleclass = "";
        /* The Forms screen governs this question now, so the old half stands
           down for it: no `visible` class, no watcher wiring below, and the
           required asterisk comes back - `JSSTforms` decides both what is shown
           and what is insisted on, and it does the second on the server as well
           as in the page. Only for a field the migration actually moved;
           anything it could not read keeps working exactly as it did.
           (Roadmap 6.5-FORM-04) */
        $jsst_formid = isset($jsst_field->multiformid) ? (int) $jsst_field->multiformid : 0;
        $jsst_retired = class_exists('JSSTformlogicmigration')
            && JSSTformlogicmigration::moved($jsst_field->field, $jsst_formid);
        if (!$jsst_retired && !empty($jsst_field->visibleparams) && $jsst_field->visibleparams != '[]'){
            $jsst_visibleclass = "visible";
        }
        $jsst_html = '';
        $jsst_div1 =  ($jsst_field->size == 100 || $jsst_field->userfieldtype == 'termsandconditions') ? ' js-ticket-from-field-wrp-full-width js-ticket-from-field-wrp '.$jsst_visibleclass : 'js-ticket-from-field-wrp '.$jsst_visibleclass;
        $jsst_div2 = 'js-ticket-from-field-title';
        $jsst_div3 = 'js-ticket-from-field';
        $jsst_div4 = 'js-ticket-from-field-description';


        /* In wp-admin these are rows of the shared form grid; on the front end
           the customer forms keep the markup they have always had, which is why
           only this branch moved. js-form-custm-flds-wrp stays on the wrapper
           whatever else changes: common.js finds a conditional field by
           .closest("div.js-form-custm-flds-wrp") and toggles `visible` on it,
           so dropping that class would leave every conditional custom field
           stuck in whichever state it loaded in. */
        if(is_admin()){
            /* The width the administrator chose for this field on the Fields
               screen - 50% or 100% - which this form has been ignoring, so every
               custom field came out the same middling width whatever was set.
               Unset (0) falls to a sensible default: a set of options is wide,
               variable content and gets the full line, because in a narrow
               column eight checkboxes stack four rows deep and leave a tall
               empty gap beside the fields next to it. Anything else is a single
               control and takes a normal field's width. */
            if($jsst_field->size == 100){
                $jsst_widthclass = 'jsst-frow-full';
            }elseif($jsst_field->size == 50){
                $jsst_widthclass = 'jsst-frow-md';
            }elseif(in_array($jsst_field->userfieldtype, array('checkbox', 'radio'), true)){
                $jsst_widthclass = 'jsst-frow-full';
            }else{
                $jsst_widthclass = 'jsst-frow-md';
            }
            $jsst_div1 = 'jsst-frow '.$jsst_widthclass.' js-form-custm-flds-wrp '.$jsst_visibleclass;
            $jsst_div2 = 'jsst-flabel';
            $jsst_div3 = 'jsst-fval';
            $jsst_div4 = 'jsst-fhelp';
            /* A set of options is a list of choices, not a row of boxes, so it
               gets the shared check treatment - which already knows how to lay
               out the .jsst-formfield-radio-button-wrap each option is wrapped
               in, and how to size the control itself. */
            if(in_array($jsst_field->userfieldtype, array('checkbox', 'radio'), true)){
                $jsst_div3 .= ' jsst-checkgrid';
            }
        }
        /* Each option is a cell of that grid - the same bordered, clickable cell
           the permission and member pickers use - so a set of options looks like
           every other set of options in the admin rather than like bare inputs.
           Declared here because both the checkbox and the radio branch below
           need it. */
        $jsst_celllass = is_admin() ? ' jsst-checkcell' : '';


        $jsst_required = $jsst_field->required;
        if($jsst_field->userfieldtype == 'termsandconditions'){
            if (isset(jssupportticket::$jsst_data[0]->id)) {
                return false;
            }
            $jsst_required = 1;
            if (!$jsst_retired && isset($jsst_field->visibleparams) && $jsst_field->visibleparams !='') {
                $jsst_required = 0;
            }
        }

        $jsst_html = '<div class="' . esc_attr($jsst_div1) .  '">';
        // hide title in case of termsandconditions
        if($jsst_field->userfieldtype != 'termsandconditions'){
            $jsst_html .= '<div class="' . esc_attr($jsst_div2) . '">';
            if ($jsst_required == 1 && $jsst_visibleclass != 'visible' && !empty($jsst_field->fieldtitle)) {
                $jsst_html .= $jsst_field->fieldtitle . '<span style="color: red;" >*</span>';
                    $jsst_cssclass = "required";
            }else {
                $jsst_html .= $jsst_field->fieldtitle;
                    $jsst_cssclass = "";
            }
            $jsst_html .= ' </div>';
        }
        $jsst_html .= ' <div class="' . esc_attr($jsst_div3) . '">';
        $jsst_readonlyclass = $jsst_field->readonly ? " js-form-ticket-readonly " : "";
        $jsst_maxlength = $jsst_field->maxlength ? "$jsst_field->maxlength" : "";
        $jsst_fvalue = "";
        $jsst_value = "";
        $jsst_userdataid = "";
        $jsst_specialClass="";
        if (isset(jssupportticket::$jsst_data[0]->id)) {
            $jsst_userfielddataarray = json_decode(jssupportticket::$jsst_data[0]->params);
            $jsst_uffield = $jsst_field->field;
            if (isset($jsst_userfielddataarray->$jsst_uffield) && !empty($jsst_userfielddataarray->$jsst_uffield)) {
                $jsst_value = $jsst_userfielddataarray->$jsst_uffield;
                $jsst_specialClass='specialClass';
            } else {
                $jsst_value = '';
            }
        } else {
            if (!empty(jssupportticket::$jsst_data[0]->params)) {
                $jsst_userfielddataarray = json_decode(jssupportticket::$jsst_data[0]->params);
            }
            $jsst_value = $jsst_field->defaultvalue;
        }
        // Handle visible field case
        $jsst_jsVisibleFunction = '';
        // For default function (default value setting)
        $jsst_defaultFunc = '';
        /* The watchers this question still drives - the ones whose rules have
           moved to the Forms screen are taken out of the list rather than the
           whole question being dropped, so a parent with one migrated child and
           one unreadable one goes on driving the second. */
        $jsst_watching = class_exists('JSSTformlogicmigration')
            ? JSSTformlogicmigration::stillWatching($jsst_field->visible_field, $jsst_formid)
            : $jsst_field->visible_field;
        if ($jsst_watching !== null && $jsst_watching !== '') {
            $jsst_visibleparams = JSSTincluder::getJSModel('fieldordering')->getDataForVisibleField($jsst_watching);
            if (!empty($jsst_visibleparams)) {
                $jsst_wpnonce = wp_create_nonce("is-field-required-".$jsst_watching);
                $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                $jsst_jsVisibleFunction = " getDataForVisibleField(\"".esc_js($jsst_wpnonce)."\", this.value, \"" . esc_js($jsst_watching) . "\", " . $jsst_jsObject.");";
                if (!empty($jsst_value) && !isset(jssupportticket::$jsst_data[0]->id)) {
                    $jsst_defaultFunc = " getDataForVisibleField(\"".$jsst_wpnonce."\", '".esc_js($jsst_value)."', \"" . esc_js($jsst_watching) . "\", " . $jsst_jsObject.");";
                    // Attach default function on document ready
                    $jsst_jssupportticket_js = "
                        jQuery(document).ready(function(){
                            ".$jsst_defaultFunc."
                        });
                    ";
                    wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                }
            }
        }
        switch ($jsst_field->userfieldtype) {
            case 'text':
                $jsst_html .= JSSTformfield::text($jsst_field->field, $jsst_value, array('class' => 'inputbox js-form-input-field js-ticket-form-field-input one '.$jsst_specialClass, 'data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsVisibleFunction, 'maxlength' => $jsst_maxlength, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : []));
                break;
            case 'email':
                $jsst_html .= JSSTformfield::email($jsst_field->field, $jsst_value, array('class' => 'inputbox js-form-input-field js-ticket-form-field-input one '.$jsst_specialClass, 'data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsVisibleFunction, 'maxlength' => $jsst_maxlength, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : []));
                break;
            case 'date':
                if(jssupportticketphplib::JSST_strpos($jsst_value , '1970') !== false){
                    $jsst_value = "";
                }
                $jsst_calendarClass = '';
                if (empty($jsst_field->readonly)) {
                    $jsst_calendarClass = ' custom_date ';
                }
                $jsst_html .= JSSTformfield::text($jsst_field->field, $jsst_value, array('class' => esc_attr($jsst_calendarClass).'js-form-date-field  js-ticket-input-field  one '.$jsst_specialClass, 'data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : []));
                break;
            case 'textarea':
                $jsst_html .= JSSTformfield::textarea($jsst_field->field, $jsst_value, array('class' => 'inputbox js-form-textarea-field js-ticket-custom-textarea one '.$jsst_specialClass, 'data-validation' => $jsst_cssclass, 'rows' => $jsst_field->rows, 'cols' => $jsst_field->cols, 'placeholder'=> jssupportticket::JSST_getVarValue($jsst_field->placeholder)) + ($jsst_field->readonly ? ['readonly' => 'readonly'] : []));
                break;
            case 'checkbox':
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_comboOptions = array();
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                    $jsst_total_options= count($jsst_obj_option);
                    if(is_admin())
                    {
                        $jsst_field_width = '';
                    }
                    elseif($jsst_total_options % 2 == 0)
                    {
                        $jsst_field_width = 'style = " width:calc(100% / 2 - 4px); margin:2px 2px;"';
                    }else
                    {
                        $jsst_field_width = 'style = " width:calc(100% / 3 - 4px); margin:2px 2px;"';
                    }
                    $jsst_i = 0;
                    $jsst_valuearray = jssupportticketphplib::JSST_explode(', ',$jsst_value);
                    foreach ($jsst_obj_option AS $jsst_option) {
                        $jsst_check = '';
                        $jsst_option = html_entity_decode($jsst_option);
                        if(in_array($jsst_option, $jsst_valuearray)){
                            $jsst_check = 'checked';
                        }
                        $jsst_readonly = '';
                        if($jsst_field->readonly){
                            $jsst_readonly = 'readonly';
                        }
                        $jsst_html .= '<div class="jsst-formfield-radio-button-wrap js-ticket-custom-radio-box'.$jsst_celllass.'" '.$jsst_field_width.'>';
                        $jsst_html .= '<input type="checkbox" ' . esc_attr($jsst_readonly) . ' ' . esc_attr($jsst_check) . ' class="radiobutton js-ticket-append-radio-btn '.esc_attr($jsst_specialClass).esc_attr($jsst_readonlyclass).'" value="' . esc_attr($jsst_option) . '" id="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_i) . '" name="' . esc_attr($jsst_field->field) . '[]" onclick = "'.esc_js($jsst_jsVisibleFunction).'">';
                        $jsst_html .= '<label for="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_i) . '" id="foruf_checkbox1">' . esc_html($jsst_option) . '</label>';
                        $jsst_html .= '</div>';
                        $jsst_i++;
                    }
                } else {
                    $jsst_comboOptions = array('1' => $jsst_field->fieldtitle);
                    $jsst_html .= JSSTformfield::checkbox($jsst_field->field, $jsst_comboOptions, $jsst_value, array('class' => 'radiobutton'));
                }
                break;
            case 'radio':
                $jsst_comboOptions = array();
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                    $jsst_total_options= count($jsst_obj_option);
                    if($jsst_total_options % 2 == 0)
                    {
                        $jsst_field_width = 'style = " width:calc(100% / 2 - 4px); margin:2px 2px;"';
                    }else{
                        $jsst_field_width = 'style = " width:calc(100% / 3 - 4px); margin:2px 2px;"';
                    }
                    $jsst_i = 0;
                    $jsst_jsFunction = '';
                    if ($jsst_field->depandant_field != null) {
                        $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_field->depandant_field);
                        $jsst_jsFunction = "getDataForDepandantField(\"".$jsst_wpnonce."\",\"" . $jsst_field->field . "\",\"" . $jsst_field->depandant_field . "\",2);";
                        if (!isset(jssupportticket::$jsst_data[0]->id) && !empty($jsst_field->defaultvalue)) {
                            $jsst_jssupportticket_js = "
                                jQuery(document).ready(function(){
                                    ".$jsst_jsFunction."
                                });
                            ";
                            wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                        }
                    }
                    $jsst_jsFunction .= $jsst_jsVisibleFunction;
                    $jsst_valuearray = jssupportticketphplib::JSST_explode(', ',$jsst_value);
                    foreach ($jsst_obj_option AS $jsst_option) {
                        $jsst_check = '';
                        $jsst_option = html_entity_decode($jsst_option);
                        if(in_array($jsst_option, $jsst_valuearray)){
                            $jsst_check = 'checked';
                        }
                        $jsst_readonly = '';
                        if($jsst_field->readonly){
                            $jsst_readonly = 'tabindex=-1';
                        }
                        $jsst_html .= '<div class="jsst-formfield-radio-button-wrap js-ticket-radio-box'.$jsst_celllass.'" '.$jsst_field_width.'>';
                            $jsst_html .= '<input type="radio" ' . esc_attr($jsst_check) . ' ' . esc_attr($jsst_readonly) . ' class="radiobutton js-ticket-radio-btn '.esc_attr($jsst_cssclass).' '.esc_attr($jsst_specialClass).esc_attr($jsst_readonlyclass).'" value="' . esc_attr($jsst_option) . '" id="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_i) . '" name="' . esc_attr($jsst_field->field) . '" data-validation ="'.esc_attr($jsst_cssclass).'" onclick = "'.esc_js($jsst_jsFunction).'"> ';
                            $jsst_html .= '<label for="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_i) . '" id="foruf_checkbox1">' . esc_html($jsst_option) . '</label>';
                        $jsst_html .= '</div>';
                        $jsst_i++;
                    }
                }
                break;
            case 'combo':
                $jsst_comboOptions = array();
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                    foreach ($jsst_obj_option as $jsst_opt) {
                        $jsst_opt = html_entity_decode($jsst_opt);
                        $jsst_comboOptions[] = (object) array('id' => $jsst_opt, 'text' => $jsst_opt);
                    }
                }
                //code for handling dependent field
                $jsst_jsFunction = '';
                if ($jsst_field->depandant_field != null) {
                    $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_field->depandant_field);
                    $jsst_jsFunction = "getDataForDepandantField(\"".$jsst_wpnonce."\",\"" . $jsst_field->field . "\",\"" . $jsst_field->depandant_field . "\",1);";
                    if (!isset(jssupportticket::$jsst_data[0]->id) && !empty($jsst_field->defaultvalue)) {
                        $jsst_jssupportticket_js = "
                            jQuery(document).ready(function(){
                                ".$jsst_jsFunction."
                            });
                        ";
                        wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                    }
                }
                $jsst_jsFunction .= $jsst_jsVisibleFunction;
                //end
                $jsst_html .= JSSTformfield::select($jsst_field->field, $jsst_comboOptions, $jsst_value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($jsst_field->fieldtitle) , array('data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsFunction, 'class' => 'inputbox js-form-select-field js-ticket-custom-select one '.esc_attr($jsst_specialClass).esc_attr($jsst_readonlyclass)) + ($jsst_field->readonly ? ['tabindex' => '-1'] : []));
                break;
            case 'depandant_field':
                $jsst_comboOptions = array();
                if ($jsst_value != null) {
                    if (!empty($jsst_field->userfieldparams)) {
                        $jsst_obj_option = $this->getDataForDepandantFieldByParentField($jsst_field->field, $jsst_userfielddataarray);
                        foreach ($jsst_obj_option as $jsst_opt) {
                            $jsst_opt = html_entity_decode($jsst_opt);
                            $jsst_comboOptions[] = (object) array('id' => $jsst_opt, 'text' => $jsst_opt);
                        }
                    }
                }
                //code for handling dependent field
                $jsst_jsFunction = '';
                if ($jsst_field->depandant_field != null) {
                    $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_field->depandant_field);
                    /* A dependent question can itself be the parent of the next one, and
                       this chain passed no hint at all - so neither branch of the old
                       reader matched and the value went up undefined. It is a select,
                       like every dependent question. */
                    $jsst_jsFunction = "getDataForDepandantField(\"".$jsst_wpnonce."\",\"" . $jsst_field->field . "\",\"" . $jsst_field->depandant_field . "\",1);";
                    if (!isset(jssupportticket::$jsst_data[0]->id) && !empty($jsst_field->defaultvalue)) {
                        $jsst_jssupportticket_js = "
                            jQuery(document).ready(function(){
                                ".$jsst_jsFunction."
                            });
                        ";
                        wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                    }
                }
                $jsst_jsFunction .= $jsst_jsVisibleFunction;
                //end
                $jsst_html .= JSSTformfield::select($jsst_field->field, $jsst_comboOptions, $jsst_value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($jsst_field->fieldtitle) , array('data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsFunction, 'class' => 'inputbox js-form-select-field js-ticket-custom-select one '.$jsst_specialClass.$jsst_readonlyclass) + ($jsst_field->readonly ? ['tabindex' => '-1'] : []));
                break;
            case 'multiple':
                $jsst_comboOptions = array();
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                    foreach ($jsst_obj_option as $jsst_opt) {
                        $jsst_opt = html_entity_decode($jsst_opt);
                        $jsst_comboOptions[] = (object) array('id' => $jsst_opt, 'text' => $jsst_opt);
                    }
                }
                $jsst_array = $jsst_field->field;
                $jsst_array .= '[]';
                $jsst_valuearray = jssupportticketphplib::JSST_explode(', ', $jsst_value);
                $jsst_html .= JSSTformfield::select($jsst_array, $jsst_comboOptions, $jsst_valuearray, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($jsst_field->fieldtitle) , array('data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsVisibleFunction, 'multiple' => 'multiple', 'class' => 'inputbox js-form-input-field one '.$jsst_specialClass.$jsst_readonlyclass) + ($jsst_field->readonly ? ['tabindex' => '-1'] : []));
                break;
            case 'file':
                $jsst_html .= '<span class="js-attachment-file-box">';
                    $jsst_html .= '<input type="file" name="'.esc_attr($jsst_field->field).'" id="'.esc_attr($jsst_field->field).'"/>';
                $jsst_html .= '</span>';
                if($jsst_value != null){
                    $jsst_html .= JSSTformfield::hidden($jsst_field->field.'_1', 0);
                    $jsst_html .= JSSTformfield::hidden($jsst_field->field.'_2',$jsst_value);
                    $jsst_jsFunction = "deleteCutomUploadedFile('".$jsst_field->field."_1')";
                    $jsst_html .='<span class='.esc_attr($jsst_field->field).'_1>'.$jsst_value.'( ';
                    $jsst_html .= "<a href='#' onClick=\"deleteCutomUploadedFile('".esc_js($jsst_field->field)."_1')\"  class=".esc_attr($jsst_specialClass)." >". esc_html(__('Delete', 'js-support-ticket'))."</a>";
                    $jsst_html .= ' )</span>';
                }
                break;
            case 'termsandconditions':
                if (isset(jssupportticket::$jsst_data[0]->id)) {
                    break;
                }
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams,true);

                    $jsst_url = '#';
                    if( isset($jsst_obj_option['termsandconditions_linktype']) && $jsst_obj_option['termsandconditions_linktype'] == 1){
                        $jsst_url = $jsst_obj_option['termsandconditions_link'];
                    }if( isset($jsst_obj_option['termsandconditions_linktype']) && $jsst_obj_option['termsandconditions_linktype'] == 2){
                        $jsst_url  = get_permalink($jsst_obj_option['termsandconditions_page']);
                    }

                    $jsst_link_start = '<a href="' . esc_url($jsst_url) . '" class="termsandconditions_link_anchor" target="_blank" >';
                    $jsst_link_end = '</a>';

                    if(strstr($jsst_obj_option['termsandconditions_text'], '[link]') && jssupportticketphplib::JSST_strstr($jsst_obj_option['termsandconditions_text'], '[/link]')){
                        $jsst_label_string = jssupportticketphplib::JSST_str_replace('[link]', $jsst_link_start, $jsst_obj_option['termsandconditions_text']);
                        $jsst_label_string = jssupportticketphplib::JSST_str_replace('[/link]', $jsst_link_end, $jsst_label_string);
                    }elseif($jsst_obj_option['termsandconditions_linktype'] == 3){
                        $jsst_label_string = $jsst_obj_option['termsandconditions_text'];
                    }else{
                        $jsst_label_string = $jsst_link_start.$jsst_obj_option['termsandconditions_text'].$jsst_link_end;
                    }
                    $jsst_c_field_required = '';
                    if($jsst_field->required == 1){
                        $jsst_c_field_required = 'required';
                    }
                    // ticket terms and conditonions are required.
                    if($jsst_field->fieldfor == 1){
                        if (empty(trim($jsst_field->visibleparams))) {
                            $jsst_c_field_required = 'required';
                        } else {
                            $jsst_c_field_required = '';
                        }
                    }

                    $jsst_html .= '<div class="js-ticket-custom-terms-and-condition-box jsst-formfield-radio-button-wrap">';
                    $jsst_html .= '<input type="checkbox" class="radiobutton js-ticket-append-radio-btn '.esc_attr($jsst_specialClass).'" value="1" id="' . esc_attr($jsst_field->field) . '" name="' . esc_attr($jsst_field->field) . '" data-validation="'.esc_attr($jsst_c_field_required).'">';
					$jsst_html .= '<label for="' . esc_attr($jsst_field->field) . '" id="foruf_checkbox1">' . wp_kses($jsst_label_string, JSST_ALLOWED_TAGS) . '</label>';
                    $jsst_html .= '</div>';
                }
                break;
        }
        $jsst_html .= '</div>';
        if(!empty($jsst_field->description)) {
            $jsst_html .= '<div class="' . esc_attr($jsst_div4) . '">'. esc_html(jssupportticket::JSST_getVarValue($jsst_field->description)) .'</div>';
        }
        $jsst_html .= '</div>';
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);

    }

    function formCustomFieldsForSearch($jsst_field, &$jsst_i, $jsst_isadmin = 0) {
        if ($jsst_field->isuserfield != 1 || $jsst_field->userfieldtype == 'file' || $jsst_field->userfieldtype == 'termsandconditions')
            return false;
        $jsst_cssclass = "";
        $jsst_html = '';
        $jsst_i++;
        $jsst_required = $jsst_field->required;
        if ($jsst_field->userfieldtype == 'checkbox' || $jsst_field->userfieldtype == 'radio') {
            $jsst_div1 = 'js-col-md-3 js-filter-field-wrp js-filter-radio-checkbox-field-wrp ';
        } else {
            $jsst_div1 = 'js-col-md-3 js-filter-field-wrp';
        }
        $jsst_div3 = 'js-filter-value';

        $jsst_html = '<div class="' . esc_attr($jsst_div1) . '"> ';
        $jsst_html .= ' <div class="' . esc_attr($jsst_div3) . '">';
        if($jsst_isadmin == 1){
            $jsst_html = ''; // only field send
        }
        $jsst_readonly = ''; //$jsst_field->readonly ? "'readonly => 'readonly'" : "";
        $jsst_maxlength = ''; //$jsst_field->maxlength ? "'maxlength' => '".esc_html($jsst_field->maxlength) : "";
        $jsst_fvalue = "";
        $jsst_value = null;
        $jsst_userdataid = "";
        $jsst_userfielddataarray = array();
        if (isset(jssupportticket::$jsst_data['filter']['params'])) {
            $jsst_userfielddataarray = jssupportticket::$jsst_data['filter']['params'];
            $jsst_uffield = $jsst_field->field;
            //had to user || oprator bcz of radio buttons

            if (isset($jsst_userfielddataarray[$jsst_uffield]) || !empty($jsst_userfielddataarray[$jsst_uffield])) {
                $jsst_value = $jsst_userfielddataarray[$jsst_uffield];
            } else {
                $jsst_value = '';
            }
        }
        switch ($jsst_field->userfieldtype) {
            case 'text':
            case 'email':
                $jsst_html .= JSSTformfield::text($jsst_field->field, $jsst_value, array('class' => 'inputbox js-form-input-field one', 'data-validation' => $jsst_cssclass,'placeholder' => $jsst_field->fieldtitle , $jsst_maxlength, $jsst_readonly));
                break;
            case 'date':
                $jsst_html .= JSSTformfield::text($jsst_field->field, $jsst_value, array('class' => 'custom_date js-form-date-field one js-ticket-input-field', 'data-validation' => $jsst_cssclass,'placeholder' => $jsst_field->fieldtitle));
                break;
            case 'editor':
                $jsst_html .= wp_editor(isset($jsst_value) ? $jsst_value : '', $jsst_field->field, array('media_buttons' => false, 'data-validation' => $jsst_cssclass));
                break;
            case 'textarea':
                $jsst_html .= JSSTformfield::textarea($jsst_field->field, $jsst_value, array('class' => 'inputbox js-form-input-field one', 'data-validation' => $jsst_cssclass, 'rows' => $jsst_field->rows, 'cols' => $jsst_field->cols, $jsst_readonly));
                break;
            case 'checkbox':
                /* In the admin filter bar this is a multi-select, not a row of
                   tick boxes. Every other control on that bar is one 227px box
                   on a floated line, and a variable number of tick boxes cannot
                   be: they stack inside a box fixed at 45px and spill over the
                   controls beneath. The `multiple` field type, which stores the
                   same array of values, has always been a multi-select here, so
                   this follows the convention rather than inventing one — and it
                   submits `field[]` exactly as the tick boxes did, so the search
                   clause is untouched. The ticket form is unaffected: that is
                   formCustomFields(), not this. */
                if ($jsst_isadmin == 1) {
                    $jsst_comboOptions = array();
                    if (!empty($jsst_field->userfieldparams)) {
                        $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                        foreach ($jsst_obj_option as $jsst_opt) {
                            $jsst_opt = html_entity_decode($jsst_opt);
                            $jsst_comboOptions[] = (object) array('id' => $jsst_opt, 'text' => $jsst_opt);
                        }
                    }
                    $jsst_html .= JSSTformfield::select($jsst_field->field . '[]', $jsst_comboOptions, $jsst_value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($jsst_field->fieldtitle), array('data-validation' => $jsst_cssclass, 'multiple' => 'multiple', 'class' => 'inputbox js-form-multi-select-field'));
                    break;
                }
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_comboOptions = array();
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                    $jsst_total_options= count($jsst_obj_option);
                    if(is_admin())
                    {
                        $jsst_field_width = '';
                    }
                    elseif($jsst_total_options % 2 == 0)
                    {
                        $jsst_field_width = 'style = " width:calc(100% / 2 - 4px); margin:2px 2px;"';
                    }else
                    {
                        $jsst_field_width = 'style = " width:calc(100% / 3 - 4px); margin:2px 2px;"';
                    }
                    if(empty($jsst_value))
                        $jsst_value = array();
                    $jsst_html .= '<div class="js-form-cust-rad-fld-wrp js-form-cust-ckb-fld-wrp">';
                    foreach ($jsst_obj_option AS $jsst_option) {
                        $jsst_option = html_entity_decode($jsst_option);
                        if( in_array($jsst_option, $jsst_value)){
                            $jsst_check = 'checked="true"';
                        }else{
                            $jsst_check = '';
                        }
                        $jsst_html .= '<div class="js-ticket-check-box" '.$jsst_field_width.'>';
                            $jsst_html .= '<input type="checkbox" ' . esc_attr($jsst_check) . ' class="radiobutton" value="' . esc_attr($jsst_option) . '" id="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_i) . '" name="' . esc_attr($jsst_field->field) . '[]">';
                            $jsst_html .= '<label for="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_i) . '" id="foruf_checkbox1">' . esc_html($jsst_option) . '</label>';
                        $jsst_html .= '</div>';
                        $jsst_i++;
                    }
                    $jsst_html .= '</div>';
                } else {
                    $jsst_comboOptions = array('1' => $jsst_field->fieldtitle );
                    $jsst_html .= JSSTformfield::checkbox($jsst_field->field, $jsst_comboOptions, $jsst_value, array('class' => 'radiobutton'));
                }
                break;
            case 'radio':
                if($jsst_isadmin == 1){
                    /* A select, for the same reason as the tick boxes above,
                       and matching `combo` — which filters on one value out of a
                       list exactly as this does. The loop here also reused
                       $jsst_i, which arrives by reference and numbers the ids of
                       every field on the bar; resetting it to zero gave the
                       fields rendered after a radio duplicate ids, so clicking a
                       later label toggled the wrong control. */
                    $jsst_comboOptions = array();
                    if (!empty($jsst_field->userfieldparams)) {
                        $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                        foreach ($jsst_obj_option as $jsst_opt) {
                            $jsst_opt = html_entity_decode($jsst_opt);
                            $jsst_comboOptions[] = (object) array('id' => $jsst_opt, 'text' => $jsst_opt);
                        }
                    }
                    $jsst_jsFunction = '';
                    if ($jsst_field->depandant_field != null) {
                        $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_field->depandant_field);
                        /* 1, because what this branch draws is a select. It said
                           2 - the radio answer - which was right for the control
                           this question used to have here and wrong from the
                           moment the filter bar started drawing it as a dropdown.
                           The reader of this hint ignores it now and asks the
                           page instead; it is corrected so the source does not
                           describe a control that is not there. */
                        $jsst_jsFunction = "getDataForDepandantField('".$jsst_wpnonce."','" . $jsst_field->field . "','" . $jsst_field->depandant_field . "',1);";
                    }
                    $jsst_html .= JSSTformfield::select($jsst_field->field, $jsst_comboOptions, $jsst_value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($jsst_field->fieldtitle), array('data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsFunction, 'class' => 'inputbox js-form-select-field one'));
                }else{
                    $jsst_comboOptions = array();
                    if (!empty($jsst_field->userfieldparams)) {
                        $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                        $jsst_total_options= count($jsst_obj_option);
                        if($jsst_total_options % 2 == 0)
                        {
                            $jsst_field_width = 'style = " width:calc(100% / 2 - 4px); margin:2px 2px;"';
                        }else
                        {
                            $jsst_field_width = 'style = " width:calc(100% / 3 - 4px); margin:2px 2px;"';
                        }
                        /* A counter of its own. $jsst_i is the caller's, by
                           reference, and numbers ids across every field. */
                        $jsst_optindex = 0;
                        $jsst_jsFunction = '';
                        if ($jsst_field->depandant_field != null) {
                            $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_field->depandant_field);
                            $jsst_jsFunction = "getDataForDepandantField('".$jsst_wpnonce."','" . $jsst_field->field . "','" . $jsst_field->depandant_field . "',2);";
                        }
                        $jsst_valuearray = jssupportticketphplib::JSST_explode(', ',$jsst_value);
                        $jsst_html .= '<div class="js-form-cust-rad-fld-wrp">';
                        foreach ($jsst_obj_option AS $jsst_option) {
                            $jsst_check = '';
                            $jsst_option = html_entity_decode($jsst_option);
                            if(in_array($jsst_option, $jsst_valuearray)){
                                $jsst_check = 'checked';
                            }
                            $jsst_html .= '<div class="js-ticket-radio-box" '.$jsst_field_width.'>';
                                $jsst_html .= '<input type="radio" ' . esc_attr($jsst_check) . ' class="radiobutton js-ticket-radio-btn '.esc_attr($jsst_cssclass).'" value="' . esc_attr($jsst_option) . '" id="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_optindex) . '" name="' . esc_attr($jsst_field->field) . '" data-validation ="'.esc_attr($jsst_cssclass).'" onclick = "'.$jsst_jsFunction.'"> ';
                                $jsst_html .= '<label for="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_optindex) . '" id="foruf_checkbox1">' . esc_html($jsst_option) . '</label>';
                            $jsst_html .= '</div>';
                            $jsst_optindex++;
                        }
                        $jsst_html .= '</div>';
                    }
                }

                break;
            case 'combo':
                $jsst_comboOptions = array();
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                    foreach ($jsst_obj_option as $jsst_opt) {
                        $jsst_opt = html_entity_decode($jsst_opt);
                        $jsst_comboOptions[] = (object) array('id' => $jsst_opt, 'text' => $jsst_opt);
                    }
                }
                //code for handling dependent field
                $jsst_jsFunction = '';
                if ($jsst_field->depandant_field != null) {
                    $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_field->depandant_field);
                    $jsst_jsFunction = "getDataForDepandantField('".$jsst_wpnonce."','" . $jsst_field->field . "','" . $jsst_field->depandant_field . "',1);";
                }
                //end
                $jsst_html .= JSSTformfield::select($jsst_field->field, $jsst_comboOptions, $jsst_value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($jsst_field->fieldtitle) , array('data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsFunction, 'class' => 'inputbox js-form-select-field one'));
                break;
            case 'depandant_field':
                $jsst_comboOptions = array();
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_obj_option = $this->getDataForDepandantFieldByParentField($jsst_field->field, $jsst_userfielddataarray);
                    if (!empty($jsst_obj_option)) {
                        foreach ($jsst_obj_option as $jsst_opt) {
                            $jsst_opt = html_entity_decode($jsst_opt);
                            $jsst_comboOptions[] = (object) array('id' => $jsst_opt, 'text' => $jsst_opt);
                        }
                    }
                }
                //code for handling dependent field
                $jsst_jsFunction = '';
                if ($jsst_field->depandant_field != null) {
                    $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_field->depandant_field);
                    /* Same chain, on the filter bar. */
                    $jsst_jsFunction = "getDataForDepandantField('".$jsst_wpnonce."','" . $jsst_field->field . "','" . $jsst_field->depandant_field . "',1);";
                }
                //end
                $jsst_html .= JSSTformfield::select($jsst_field->field, $jsst_comboOptions, $jsst_value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($jsst_field->fieldtitle) , array('data-validation' => $jsst_cssclass, 'onchange' => $jsst_jsFunction, 'class' => 'inputbox js-form-select-field one'));
                break;
            case 'multiple':
                $jsst_comboOptions = array();
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                    foreach ($jsst_obj_option as $jsst_opt) {
                        $jsst_opt = html_entity_decode($jsst_opt);
                        $jsst_comboOptions[] = (object) array('id' => $jsst_opt, 'text' => $jsst_opt);
                    }
                }
                $jsst_array = $jsst_field->field;
                $jsst_array .= '[]';
                $jsst_html .= JSSTformfield::select($jsst_array, $jsst_comboOptions, $jsst_value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($jsst_field->fieldtitle) , array('data-validation' => $jsst_cssclass, 'multiple' => 'multiple','class' => 'inputbox js-form-multi-select-field'));
                break;
        }
        if($jsst_isadmin == 1){
            echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
            return;
        }
        $jsst_html .= '</div></div>';
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);

    }

    function showCustomFields($jsst_field, $jsst_fieldfor, $jsst_params) {

        $jsst_fvalue = '';

        if(!empty($jsst_params)){
            $jsst_data = json_decode($jsst_params,true);
            if(is_array($jsst_data) && $jsst_data != ''){
                if(array_key_exists($jsst_field->field, $jsst_data)){
                    $jsst_fvalue = $jsst_data[$jsst_field->field];
                    $jsst_fvalue = jssupportticketphplib::JSST_htmlspecialchars($jsst_fvalue);
                }
            }
        }
        if($jsst_field->userfieldtype=='file'){

           if($jsst_fvalue !=null){
                if (is_admin()) {
                    $jsst_path = admin_url("?page=ticket&action=jstask&task=downloadbyname&id=".jssupportticket::$jsst_data['custom']['ticketid']."&name=".$jsst_fvalue);
                } else {
                    $jsst_path = jssupportticket::makeUrl(array('jstmod'=>'ticket', 'action'=>'jstask', 'task'=>'downloadbyname', 'id'=>jssupportticket::$jsst_data['custom']['ticketid'], 'name'=>$jsst_fvalue, 'jsstpageid'=>get_the_ID()));
                }

                $jsst_html = '
                    <div class="js_ticketattachment">
                        ' .  $jsst_fvalue . '
                        <a class="button" target="_blank" href="' . esc_url($jsst_path) . '">' . esc_html(__('Download', 'js-support-ticket')) . '</a>
                    </div>';
                $jsst_fvalue = $jsst_html;
            }
        }elseif($jsst_field->userfieldtype=='date' && !empty($jsst_fvalue)){
            if(jssupportticketphplib::JSST_strpos($jsst_fvalue , '1970') !== false){
                $jsst_fvalue = "";
            } else {
                $jsst_fvalue = date_i18n(jssupportticket::$_config['date_format'],strtotime($jsst_fvalue));
            }
        }
        $jsst_return_array['title'] = $jsst_field->fieldtitle;
        $jsst_return_array['value'] = $jsst_fvalue;
        return $jsst_return_array;
    }

    function userFieldsData($jsst_fieldfor, $jsst_listing = null, $jsst_multiformid = '') {
        if(!is_numeric($jsst_fieldfor)){
            return false;
        }
        if ($jsst_multiformid == '') {
            $jsst_multiformid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
        }
        if(!is_numeric($jsst_multiformid)){
            return false;
        }
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' published = 1 ';
        }
        $jsst_inquery = '';
        if ($jsst_listing == 1) {
            $jsst_inquery = ' AND showonlisting = 1 ';
        }
        if (!is_admin()) {
            $jsst_inquery .= ' AND adminonly != 1 ';
        }
        /* A terms-and-conditions field is a consent tick on the ticket form, not
           a piece of ticket data, and it renders as "Terms And Conditions 1 : 1"
           wherever it is shown. Every other path already leaves it out — the
           ticket form hides it from staff, and the filter bar excludes it
           outright — so this, which feeds the ticket detail screens and the two
           ticket lists, was the one place it still appeared. The answer is still
           stored on the ticket, so nothing is lost; it is simply not displayed. */
        $jsst_inquery .= " AND userfieldtype != 'termsandconditions' ";
        $jsst_query = jssupportticket::$_db->prepare("SELECT field,fieldtitle,isuserfield,userfieldtype,userfieldparams,multiformid  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $jsst_published . " AND fieldfor =%d" . $jsst_inquery. " AND multiformid =%d ORDER BY ordering", $jsst_fieldfor, $jsst_multiformid);
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_data;
    }

    function userFieldsForSearch($jsst_fieldfor) {
        if(!is_numeric($jsst_fieldfor)){
            return false;
        }
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_inquery = ' isvisitorpublished = 1';
        } else {
            $jsst_inquery = ' published = 1 AND search_user =1';
        }
        if(!is_admin()){
            $jsst_inquery .= " AND adminonly != 1";
        }

        $jsst_query = jssupportticket::$_db->prepare("SELECT `rows`,`cols`,required,field,fieldtitle,isuserfield,userfieldtype,userfieldparams,depandant_field  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $jsst_inquery . " AND fieldfor =%d ORDER BY ordering ", $jsst_fieldfor);
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_data;
    }

    function adminFieldsForSearch($jsst_fieldfor) {
        if(!is_numeric($jsst_fieldfor)){
            return false;
        }

        $jsst_query = jssupportticket::$_db->prepare("SELECT `rows`,`cols`,required,field,fieldtitle,isuserfield,userfieldtype,userfieldparams,depandant_field  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND published = 1 AND search_admin =1 AND fieldfor =%d ORDER BY ordering ", $jsst_fieldfor);
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_data;
    }

    function getDataForDepandantFieldByParentField($jsst_fieldfor, $jsst_data) {
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $jsst_published = ' isvisitorpublished = 1 ';
        } else {
            $jsst_published = ' published = 1 ';
        }
        $jsst_value = '';
        $jsst_returnarray = array();
        $jsst_query = jssupportticket::$_db->prepare("SELECT field from " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $jsst_published . " AND depandant_field =%s", $jsst_fieldfor);
        $jsst_field = jssupportticket::$_db->get_var($jsst_query);
        if ($jsst_data != null) {
            foreach ($jsst_data as $jsst_key => $jsst_val) {
                $jsst_key = html_entity_decode($jsst_key);
                if ($jsst_key == $jsst_field) {
                    $jsst_value = $jsst_val;
                }
            }
        }
        $jsst_query = jssupportticket::$_db->prepare("SELECT userfieldparams from " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $jsst_published . " AND field =%s", $jsst_fieldfor);
        $jsst_field = jssupportticket::$_db->get_var($jsst_query);
        $jsst_fieldarray = json_decode($jsst_field);
        foreach ($jsst_fieldarray as $jsst_key => $jsst_val) {
            $jsst_key = html_entity_decode($jsst_key);
            if ($jsst_value == $jsst_key)
                $jsst_returnarray = $jsst_val;
        }
        return $jsst_returnarray;
    }

}

?>
