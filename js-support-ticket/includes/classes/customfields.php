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
        if (!empty($jsst_field->visibleparams) && $jsst_field->visibleparams != '[]'){
            $jsst_visibleclass = "visible";
        }
        $jsst_html = '';
        $jsst_div1 =  ($jsst_field->size == 100 || $jsst_field->userfieldtype == 'termsandconditions') ? ' js-ticket-from-field-wrp-full-width js-ticket-from-field-wrp '.$jsst_visibleclass : 'js-ticket-from-field-wrp '.$jsst_visibleclass;
        $jsst_div2 = 'js-ticket-from-field-title';
        $jsst_div3 = 'js-ticket-from-field';
        $jsst_div4 = 'js-ticket-from-field-description';


        if(is_admin()){
            $jsst_div1 = 'js-form-wrapper js-form-custm-flds-wrp '.$jsst_visibleclass;
            $jsst_div2 = 'js-form-title';
            $jsst_div3 = 'js-form-value';
            $jsst_div4 = 'js-form-description';
        }


        $jsst_required = $jsst_field->required;
        if($jsst_field->userfieldtype == 'termsandconditions'){
            if (isset(jssupportticket::$jsst_data[0]->id)) {
                return false;
            }
            $jsst_required = 1;
            if (isset($jsst_field->visibleparams) && $jsst_field->visibleparams !='') {
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
        if ($jsst_field->visible_field != null) {
            $jsst_visibleparams = JSSTincluder::getJSModel('fieldordering')->getDataForVisibleField($jsst_field->visible_field);
            if (!empty($jsst_visibleparams)) {
                $jsst_wpnonce = wp_create_nonce("is-field-required-".$jsst_field->visible_field);
                $jsst_jsObject = wp_json_encode($jsst_visibleparams);
                $jsst_jsVisibleFunction = " getDataForVisibleField(\"".esc_js($jsst_wpnonce)."\", this.value, \"" . esc_js($jsst_field->visible_field) . "\", " . $jsst_jsObject.");";
                if (!empty($jsst_value) && !isset(jssupportticket::$jsst_data[0]->id)) {
                    $jsst_defaultFunc = " getDataForVisibleField(\"".$jsst_wpnonce."\", '".esc_js($jsst_value)."', \"" . esc_js($jsst_field->visible_field) . "\", " . $jsst_jsObject.");";
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
                    if($jsst_total_options % 2 == 0)
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
                        $jsst_html .= '<div class="jsst-formfield-radio-button-wrap js-ticket-custom-radio-box" '.$jsst_field_width.'>';
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
                        $jsst_html .= '<div class="jsst-formfield-radio-button-wrap js-ticket-radio-box" '.$jsst_field_width.'>';
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
                    $jsst_jsFunction = "getDataForDepandantField(\"".$jsst_wpnonce."\",\"" . $jsst_field->field . "\",\"" . $jsst_field->depandant_field . "\");";
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
                if (!empty($jsst_field->userfieldparams)) {
                    $jsst_comboOptions = array();
                    $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                    $jsst_total_options= count($jsst_obj_option);
                    if($jsst_total_options % 2 == 0)
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
                    $jsst_comboOptions = array();
                    if (!empty($jsst_field->userfieldparams)) {
                        $jsst_obj_option = json_decode($jsst_field->userfieldparams);
                        for ($jsst_i = 0; $jsst_i < count($jsst_obj_option); $jsst_i++) {
                            $jsst_obj_option[$jsst_i] = html_entity_decode($jsst_obj_option[$jsst_i]);
                            $jsst_comboOptions[$jsst_obj_option[$jsst_i]] = "$jsst_obj_option[$jsst_i]";
                        }
                    }
                    $jsst_jsFunction = '';
                    if ($jsst_field->depandant_field != null) {
                        $jsst_wpnonce = wp_create_nonce("data-for-depandant-field-".$jsst_field->depandant_field);
                        $jsst_jsFunction = "getDataForDepandantField('".$jsst_wpnonce."','" . $jsst_field->field . "','" . $jsst_field->depandant_field . "',2);";
                    }
                    $jsst_html .= '<div class="js-form-cust-rad-fld-wrp">';
                    $jsst_html .= JSSTformfield::radiobutton($jsst_field->field, $jsst_comboOptions, $jsst_value, array('data-validation' => $jsst_cssclass, "autocomplete" => "off", 'onclick' => $jsst_jsFunction));
                    $jsst_html .= '</div>';
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
                        $jsst_i = 0;
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
                                $jsst_html .= '<input type="radio" ' . esc_attr($jsst_check) . ' class="radiobutton js-ticket-radio-btn '.esc_attr($jsst_cssclass).'" value="' . esc_attr($jsst_option) . '" id="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_i) . '" name="' . esc_attr($jsst_field->field) . '" data-validation ="'.esc_attr($jsst_cssclass).'" onclick = "'.$jsst_jsFunction.'"> ';
                                $jsst_html .= '<label for="' . esc_attr($jsst_field->field) . '_' . esc_attr($jsst_i) . '" id="foruf_checkbox1">' . esc_html($jsst_option) . '</label>';
                            $jsst_html .= '</div>';
                            $jsst_i++;
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
                    $jsst_jsFunction = "getDataForDepandantField('".$jsst_wpnonce."','" . $jsst_field->field . "','" . $jsst_field->depandant_field . "');";
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
                $jsst_path = admin_url("?page=ticket&action=jstask&task=downloadbyname&id=".jssupportticket::$jsst_data['custom']['ticketid']."&name=".$jsst_fvalue);
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
        $jsst_query = "SELECT field,fieldtitle,isuserfield,userfieldtype,userfieldparams,multiformid  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $jsst_published . " AND fieldfor =" . esc_sql($jsst_fieldfor) . $jsst_inquery. " AND multiformid =" . esc_sql($jsst_multiformid). " ORDER BY ordering";
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

        $jsst_query = "SELECT `rows`,`cols`,required,field,fieldtitle,isuserfield,userfieldtype,userfieldparams,depandant_field  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $jsst_inquery . " AND fieldfor =" . esc_sql($jsst_fieldfor) ." ORDER BY ordering ";
        $jsst_data = jssupportticket::$_db->get_results($jsst_query);
        return $jsst_data;
    }

    function adminFieldsForSearch($jsst_fieldfor) {
        if(!is_numeric($jsst_fieldfor)){
            return false;
        }

        $jsst_query = "SELECT `rows`,`cols`,required,field,fieldtitle,isuserfield,userfieldtype,userfieldparams,depandant_field  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND published = 1 AND search_admin =1 AND fieldfor =" . esc_sql($jsst_fieldfor) ." ORDER BY ordering ";
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
        $jsst_query = "SELECT field from " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $jsst_published . " AND depandant_field ='" . esc_sql($jsst_fieldfor) . "'";
        $jsst_field = jssupportticket::$_db->get_var($jsst_query);
        if ($jsst_data != null) {
            foreach ($jsst_data as $jsst_key => $jsst_val) {
                $jsst_key = html_entity_decode($jsst_key);
                if ($jsst_key == $jsst_field) {
                    $jsst_value = $jsst_val;
                }
            }
        }
        $jsst_query = "SELECT userfieldparams from " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $jsst_published . " AND field ='" . esc_sql($jsst_fieldfor) . "'";
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
