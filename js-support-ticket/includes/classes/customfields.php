<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTcustomfields {
    function formCustomFields($field) {
        if($field->isuserfield != 1){
            return false;
        }
        // Handle adminonly case
        // Visible only on admin and agent form
        if( in_array('agent',jssupportticket::$_active_addons) ){
            $agent = JSSTincluder::getJSModel('agent')->isUserStaff();
        }else{
            $agent = false;
        }
        if(!empty($field->adminonly) && !is_admin() && !$agent){
            return false;
        }
        // show termsandconditions only on user form
        if($field->userfieldtype == 'termsandconditions' && (is_admin() || $agent)){
            return false;
        }
        $cssclass = "";
        $visibleclass = "";
        if (!empty($field->visibleparams) && $field->visibleparams != '[]'){
            $visibleclass = "visible";
        }
        $html = '';
        $div1 =  ($field->size == 100 || $field->userfieldtype == 'termsandconditions') ? ' js-ticket-from-field-wrp-full-width js-ticket-from-field-wrp '.$visibleclass : 'js-ticket-from-field-wrp '.$visibleclass;
        $div2 = 'js-ticket-from-field-title';
        $div3 = 'js-ticket-from-field';
        $div4 = 'js-ticket-from-field-description';


        if(is_admin()){
            $div1 = 'js-form-wrapper js-form-custm-flds-wrp '.$visibleclass;
            $div2 = 'js-form-title';
            $div3 = 'js-form-value';
            $div4 = 'js-form-description';
        }


        $required = $field->required;
        if($field->userfieldtype == 'termsandconditions'){
            if (isset(jssupportticket::$_data[0]->id)) {
                return false;
            }
            $required = 1;
            if (isset($field->visibleparams) && $field->visibleparams !='') {
                $required = 0;
            }
        }

        $html = '<div class="' . esc_attr($div1) .  '">';
        // hide title in case of termsandconditions
        if($field->userfieldtype != 'termsandconditions'){
            $html .= '<div class="' . esc_attr($div2) . '">';
            if ($required == 1 && $visibleclass != 'visible' && !empty($field->fieldtitle)) {
                $html .= $field->fieldtitle . '<span style="color: red;" >*</span>';
                    $cssclass = "required";
            }else {
                $html .= $field->fieldtitle;
                    $cssclass = "";
            }
            $html .= ' </div>';
        }
        $html .= ' <div class="' . esc_attr($div3) . '">';
        $readonlyclass = $field->readonly ? " js-form-ticket-readonly " : "";
        $maxlength = $field->maxlength ? "$field->maxlength" : "";
        $fvalue = "";
        $value = "";
        $userdataid = "";
        $specialClass="";
        if (isset(jssupportticket::$_data[0]->id)) {
            $userfielddataarray = json_decode(jssupportticket::$_data[0]->params);
            $uffield = $field->field;
            if (isset($userfielddataarray->$uffield) && !empty($userfielddataarray->$uffield)) {
                $value = $userfielddataarray->$uffield;
                $specialClass='specialClass';
            } else {
                $value = '';
            }
        } else {
            if (!empty(jssupportticket::$_data[0]->params)) {
                $userfielddataarray = json_decode(jssupportticket::$_data[0]->params);
            }
            $value = $field->defaultvalue;
        }
        // Handle visible field case
        $jsVisibleFunction = '';
        // For default function (default value setting)
        $defaultFunc = '';
        if ($field->visible_field != null) {
            $visibleparams = JSSTincluder::getJSModel('fieldordering')->getDataForVisibleField($field->visible_field);
            if (!empty($visibleparams)) {
                $wpnonce = wp_create_nonce("is-field-required-".$field->visible_field);
                $jsObject = wp_json_encode($visibleparams);
                $jsVisibleFunction = " getDataForVisibleField(\"".esc_js($wpnonce)."\", this.value, \"" . esc_js($field->visible_field) . "\", " . $jsObject.");";
                if (!empty($value) && !isset(jssupportticket::$_data[0]->id)) {
                    $defaultFunc = " getDataForVisibleField(\"".$wpnonce."\", '".esc_js($value)."', \"" . esc_js($field->visible_field) . "\", " . $jsObject.");";
                    // Attach default function on document ready
                    $jssupportticket_js = "
                        jQuery(document).ready(function(){
                            ".$defaultFunc."
                        });
                    ";
                    wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js);
                }
            }
        }
        switch ($field->userfieldtype) {
            case 'text':
                $html .= JSSTformfield::text($field->field, $value, array('class' => 'inputbox js-form-input-field js-ticket-form-field-input one '.$specialClass, 'data-validation' => $cssclass, 'onchange' => $jsVisibleFunction, 'maxlength' => $maxlength, 'placeholder'=> jssupportticket::JSST_getVarValue($field->placeholder)) + ($field->readonly ? ['readonly' => 'readonly'] : []));
                break;
            case 'email':
                $html .= JSSTformfield::email($field->field, $value, array('class' => 'inputbox js-form-input-field js-ticket-form-field-input one '.$specialClass, 'data-validation' => $cssclass, 'onchange' => $jsVisibleFunction, 'maxlength' => $maxlength, 'placeholder'=> jssupportticket::JSST_getVarValue($field->placeholder)) + ($field->readonly ? ['readonly' => 'readonly'] : []));
                break;
            case 'date':
                if(jssupportticketphplib::JSST_strpos($value , '1970') !== false){
                    $value = "";
                }
                $calendarClass = '';
                if (empty($field->readonly)) {
                    $calendarClass = ' custom_date ';
                }
                $html .= JSSTformfield::text($field->field, $value, array('class' => esc_attr($calendarClass).'js-form-date-field  js-ticket-input-field  one '.$specialClass, 'data-validation' => $cssclass, 'onchange' => $jsVisibleFunction, 'placeholder'=> jssupportticket::JSST_getVarValue($field->placeholder)) + ($field->readonly ? ['readonly' => 'readonly'] : []));
                break;
            case 'textarea':
                $html .= JSSTformfield::textarea($field->field, $value, array('class' => 'inputbox js-form-textarea-field js-ticket-custom-textarea one '.$specialClass, 'data-validation' => $cssclass, 'rows' => $field->rows, 'cols' => $field->cols, 'placeholder'=> jssupportticket::JSST_getVarValue($field->placeholder)) + ($field->readonly ? ['readonly' => 'readonly'] : []));
                break;
            case 'checkbox':
                if (!empty($field->userfieldparams)) {
                    $comboOptions = array();
                    $obj_option = json_decode($field->userfieldparams);
                    $total_options= count($obj_option);
                    if($total_options % 2 == 0)
                    {
                        $field_width = 'style = " width:calc(100% / 2 - 4px); margin:2px 2px;"';
                    }else
                    {
                        $field_width = 'style = " width:calc(100% / 3 - 4px); margin:2px 2px;"';
                    }
                    $i = 0;
                    $valuearray = jssupportticketphplib::JSST_explode(', ',$value);
                    foreach ($obj_option AS $option) {
                        $check = '';
                        $option = html_entity_decode($option);
                        if(in_array($option, $valuearray)){
                            $check = 'checked';
                        }
                        $readonly = '';
                        if($field->readonly){
                            $readonly = 'readonly';
                        }
                        $html .= '<div class="jsst-formfield-radio-button-wrap js-ticket-custom-radio-box" '.$field_width.'>';
                        $html .= '<input type="checkbox" ' . esc_attr($readonly) . ' ' . esc_attr($check) . ' class="radiobutton js-ticket-append-radio-btn '.esc_attr($specialClass).esc_attr($readonlyclass).'" value="' . esc_attr($option) . '" id="' . esc_attr($field->field) . '_' . esc_attr($i) . '" name="' . esc_attr($field->field) . '[]" onclick = "'.esc_js($jsVisibleFunction).'">';
                        $html .= '<label for="' . esc_attr($field->field) . '_' . esc_attr($i) . '" id="foruf_checkbox1">' . esc_html($option) . '</label>';
                        $html .= '</div>';
                        $i++;
                    }
                } else {
                    $comboOptions = array('1' => $field->fieldtitle);
                    $html .= JSSTformfield::checkbox($field->field, $comboOptions, $value, array('class' => 'radiobutton'));
                }
                break;
            case 'radio':
                $comboOptions = array();
                if (!empty($field->userfieldparams)) {
                    $obj_option = json_decode($field->userfieldparams);
                    $total_options= count($obj_option);
                    if($total_options % 2 == 0)
                    {
                        $field_width = 'style = " width:calc(100% / 2 - 4px); margin:2px 2px;"';
                    }else{
                        $field_width = 'style = " width:calc(100% / 3 - 4px); margin:2px 2px;"';
                    }
                    $i = 0;
                    $jsFunction = '';
                    if ($field->depandant_field != null) {
                        $wpnonce = wp_create_nonce("data-for-depandant-field-".$field->depandant_field);
                        $jsFunction = "getDataForDepandantField(\"".$wpnonce."\",\"" . $field->field . "\",\"" . $field->depandant_field . "\",2);";
                        if (!isset(jssupportticket::$_data[0]->id) && !empty($field->defaultvalue)) {
                            $jssupportticket_js = "
                                jQuery(document).ready(function(){
                                    ".$jsFunction."
                                });
                            ";
                            wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js);
                        }
                    }
                    $jsFunction .= $jsVisibleFunction;
                    $valuearray = jssupportticketphplib::JSST_explode(', ',$value);
                    foreach ($obj_option AS $option) {
                        $check = '';
                        $option = html_entity_decode($option);
                        if(in_array($option, $valuearray)){
                            $check = 'checked';
                        }
                        $readonly = '';
                        if($field->readonly){
                            $readonly = 'tabindex=-1';
                        }
                        $html .= '<div class="jsst-formfield-radio-button-wrap js-ticket-radio-box" '.$field_width.'>';
                            $html .= '<input type="radio" ' . esc_attr($check) . ' ' . esc_attr($readonly) . ' class="radiobutton js-ticket-radio-btn '.esc_attr($cssclass).' '.esc_attr($specialClass).esc_attr($readonlyclass).'" value="' . esc_attr($option) . '" id="' . esc_attr($field->field) . '_' . esc_attr($i) . '" name="' . esc_attr($field->field) . '" data-validation ="'.esc_attr($cssclass).'" onclick = "'.esc_js($jsFunction).'"> ';
                            $html .= '<label for="' . esc_attr($field->field) . '_' . esc_attr($i) . '" id="foruf_checkbox1">' . esc_html($option) . '</label>';
                        $html .= '</div>';
                        $i++;
                    }
                }
                break;
            case 'combo':
                $comboOptions = array();
                if (!empty($field->userfieldparams)) {
                    $obj_option = json_decode($field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $opt = html_entity_decode($opt);
                        $comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                    }
                }
                //code for handling dependent field
                $jsFunction = '';
                if ($field->depandant_field != null) {
                    $wpnonce = wp_create_nonce("data-for-depandant-field-".$field->depandant_field);
                    $jsFunction = "getDataForDepandantField(\"".$wpnonce."\",\"" . $field->field . "\",\"" . $field->depandant_field . "\",1);";
                    if (!isset(jssupportticket::$_data[0]->id) && !empty($field->defaultvalue)) {
                        $jssupportticket_js = "
                            jQuery(document).ready(function(){
                                ".$jsFunction."
                            });
                        ";
                        wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js);
                    }
                }
                $jsFunction .= $jsVisibleFunction;
                //end
                $html .= JSSTformfield::select($field->field, $comboOptions, $value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($field->fieldtitle) , array('data-validation' => $cssclass, 'onchange' => $jsFunction, 'class' => 'inputbox js-form-select-field js-ticket-custom-select one '.esc_attr($specialClass).esc_attr($readonlyclass)) + ($field->readonly ? ['tabindex' => '-1'] : []));
                break;
            case 'depandant_field':
                $comboOptions = array();
                if ($value != null) {
                    if (!empty($field->userfieldparams)) {
                        $obj_option = $this->getDataForDepandantFieldByParentField($field->field, $userfielddataarray);
                        foreach ($obj_option as $opt) {
                            $opt = html_entity_decode($opt);
                            $comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                        }
                    }
                }
                //code for handling dependent field
                $jsFunction = '';
                if ($field->depandant_field != null) {
                    $wpnonce = wp_create_nonce("data-for-depandant-field-".$field->depandant_field);
                    $jsFunction = "getDataForDepandantField(\"".$wpnonce."\",\"" . $field->field . "\",\"" . $field->depandant_field . "\");";
                    if (!isset(jssupportticket::$_data[0]->id) && !empty($field->defaultvalue)) {
                        $jssupportticket_js = "
                            jQuery(document).ready(function(){
                                ".$jsFunction."
                            });
                        ";
                        wp_add_inline_script('js-support-ticket-main-js', $jssupportticket_js);
                    }
                }
                $jsFunction .= $jsVisibleFunction;
                //end
                $html .= JSSTformfield::select($field->field, $comboOptions, $value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($field->fieldtitle) , array('data-validation' => $cssclass, 'onchange' => $jsFunction, 'class' => 'inputbox js-form-select-field js-ticket-custom-select one '.$specialClass.$readonlyclass) + ($field->readonly ? ['tabindex' => '-1'] : []));
                break;
            case 'multiple':
                $comboOptions = array();
                if (!empty($field->userfieldparams)) {
                    $obj_option = json_decode($field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $opt = html_entity_decode($opt);
                        $comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                    }
                }
                $array = $field->field;
                $array .= '[]';
                $valuearray = jssupportticketphplib::JSST_explode(', ', $value);
                $html .= JSSTformfield::select($array, $comboOptions, $valuearray, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($field->fieldtitle) , array('data-validation' => $cssclass, 'onchange' => $jsVisibleFunction, 'multiple' => 'multiple', 'class' => 'inputbox js-form-input-field one '.$specialClass.$readonlyclass) + ($field->readonly ? ['tabindex' => '-1'] : []));
                break;
            case 'file':
                $html .= '<span class="js-attachment-file-box">';
                    $html .= '<input type="file" name="'.esc_attr($field->field).'" id="'.esc_attr($field->field).'"/>';
                $html .= '</span>';
                if($value != null){
                    $html .= JSSTformfield::hidden($field->field.'_1', 0);
                    $html .= JSSTformfield::hidden($field->field.'_2',$value);
                    $jsFunction = "deleteCutomUploadedFile('".$field->field."_1')";
                    $html .='<span class='.esc_attr($field->field).'_1>'.$value.'( ';
                    $html .= "<a href='#' onClick=\"deleteCutomUploadedFile('".esc_js($field->field)."_1')\"  class=".esc_attr($specialClass)." >". esc_html(__('Delete', 'js-support-ticket'))."</a>";
                    $html .= ' )</span>';
                }
                break;
            case 'termsandconditions':
                if (isset(jssupportticket::$_data[0]->id)) {
                    break;
                }
                if (!empty($field->userfieldparams)) {
                    $obj_option = json_decode($field->userfieldparams,true);

                    $url = '#';
                    if( isset($obj_option['termsandconditions_linktype']) && $obj_option['termsandconditions_linktype'] == 1){
                        $url = $obj_option['termsandconditions_link'];
                    }if( isset($obj_option['termsandconditions_linktype']) && $obj_option['termsandconditions_linktype'] == 2){
                        $url  = get_permalink($obj_option['termsandconditions_page']);
                    }

                    $link_start = '<a href="' . esc_url($url) . '" class="termsandconditions_link_anchor" target="_blank" >';
                    $link_end = '</a>';

                    if(strstr($obj_option['termsandconditions_text'], '[link]') && jssupportticketphplib::JSST_strstr($obj_option['termsandconditions_text'], '[/link]')){
                        $label_string = jssupportticketphplib::JSST_str_replace('[link]', $link_start, $obj_option['termsandconditions_text']);
                        $label_string = jssupportticketphplib::JSST_str_replace('[/link]', $link_end, $label_string);
                    }elseif($obj_option['termsandconditions_linktype'] == 3){
                        $label_string = $obj_option['termsandconditions_text'];
                    }else{
                        $label_string = $link_start.$obj_option['termsandconditions_text'].$link_end;
                    }
                    $c_field_required = '';
                    if($field->required == 1){
                        $c_field_required = 'required';
                    }
                    // ticket terms and conditonions are required.
                    if($field->fieldfor == 1){
                        if (empty(trim($field->visibleparams))) {
                            $c_field_required = 'required';
                        } else {
                            $c_field_required = '';
                        }
                    }

                    $html .= '<div class="js-ticket-custom-terms-and-condition-box jsst-formfield-radio-button-wrap">';
                    $html .= '<input type="checkbox" class="radiobutton js-ticket-append-radio-btn '.esc_attr($specialClass).'" value="1" id="' . esc_attr($field->field) . '" name="' . esc_attr($field->field) . '" data-validation="'.esc_attr($c_field_required).'">';
					$html .= '<label for="' . esc_attr($field->field) . '" id="foruf_checkbox1">' . wp_kses($label_string, JSST_ALLOWED_TAGS) . '</label>';
                    $html .= '</div>';
                }
                break;
        }
        $html .= '</div>';
        if(!empty($field->description)) {
            $html .= '<div class="' . esc_attr($div4) . '">'. esc_html(jssupportticket::JSST_getVarValue($field->description)) .'</div>';
        }
        $html .= '</div>';
        echo wp_kses($html, JSST_ALLOWED_TAGS);

    }

    function formCustomFieldsForSearch($field, &$i, $isadmin = 0) {
        if ($field->isuserfield != 1 || $field->userfieldtype == 'file' || $field->userfieldtype == 'termsandconditions')
            return false;
        $cssclass = "";
        $html = '';
        $i++;
        $required = $field->required;
        $div1 = 'js-col-md-3 js-filter-field-wrp';
        $div3 = 'js-filter-value';

        $html = '<div class="' . esc_attr($div1) . '"> ';
        $html .= ' <div class="' . esc_attr($div3) . '">';
        if($isadmin == 1){
            $html = ''; // only field send
        }
        $readonly = ''; //$field->readonly ? "'readonly => 'readonly'" : "";
        $maxlength = ''; //$field->maxlength ? "'maxlength' => '".esc_html($field->maxlength) : "";
        $fvalue = "";
        $value = null;
        $userdataid = "";
        $userfielddataarray = array();
        if (isset(jssupportticket::$_data['filter']['params'])) {
            $userfielddataarray = jssupportticket::$_data['filter']['params'];
            $uffield = $field->field;
            //had to user || oprator bcz of radio buttons

            if (isset($userfielddataarray[$uffield]) || !empty($userfielddataarray[$uffield])) {
                $value = $userfielddataarray[$uffield];
            } else {
                $value = '';
            }
        }
        switch ($field->userfieldtype) {
            case 'text':
            case 'email':
                $html .= JSSTformfield::text($field->field, $value, array('class' => 'inputbox js-form-input-field one', 'data-validation' => $cssclass,'placeholder' => $field->fieldtitle , $maxlength, $readonly));
                break;
            case 'date':
                $html .= JSSTformfield::text($field->field, $value, array('class' => 'custom_date js-form-date-field one js-ticket-input-field', 'data-validation' => $cssclass,'placeholder' => $field->fieldtitle));
                break;
            case 'editor':
                $html .= wp_editor(isset($value) ? $value : '', $field->field, array('media_buttons' => false, 'data-validation' => $cssclass));
                break;
            case 'textarea':
                $html .= JSSTformfield::textarea($field->field, $value, array('class' => 'inputbox js-form-input-field one', 'data-validation' => $cssclass, 'rows' => $field->rows, 'cols' => $field->cols, $readonly));
                break;
            case 'checkbox':
                if (!empty($field->userfieldparams)) {
                    $comboOptions = array();
                    $obj_option = json_decode($field->userfieldparams);
                    if(empty($value))
                        $value = array();
                    $html .= '<div class="js-form-cust-rad-fld-wrp js-form-cust-ckb-fld-wrp">';
                    foreach ($obj_option AS $option) {
                        $option = html_entity_decode($option);
                        if( in_array($option, $value)){
                            $check = 'checked="true"';
                        }else{
                            $check = '';
                        }
                        $html .= '<input type="checkbox" ' . esc_attr($check) . ' class="radiobutton" value="' . esc_attr($option) . '" id="' . esc_attr($field->field) . '_' . esc_attr($i) . '" name="' . esc_attr($field->field) . '[]">';
                        $html .= '<label for="' . esc_attr($field->field) . '_' . esc_attr($i) . '" id="foruf_checkbox1">' . esc_html($option) . '</label>';
                        $i++;
                    }
                    $html .= '</div>';
                } else {
                    $comboOptions = array('1' => $field->fieldtitle );
                    $html .= JSSTformfield::checkbox($field->field, $comboOptions, $value, array('class' => 'radiobutton'));
                }
                break;
            case 'radio':
                if($isadmin == 1){
                    $comboOptions = array();
                    if (!empty($field->userfieldparams)) {
                        $obj_option = json_decode($field->userfieldparams);
                        for ($i = 0; $i < count($obj_option); $i++) {
                            $obj_option[$i] = html_entity_decode($obj_option[$i]);
                            $comboOptions[$obj_option[$i]] = "$obj_option[$i]";
                        }
                    }
                    $jsFunction = '';
                    if ($field->depandant_field != null) {
                        $wpnonce = wp_create_nonce("data-for-depandant-field-".$field->depandant_field);
                        $jsFunction = "getDataForDepandantField('".$wpnonce."','" . $field->field . "','" . $field->depandant_field . "',2);";
                    }
                    $html .= '<div class="js-form-cust-rad-fld-wrp">';
                    $html .= JSSTformfield::radiobutton($field->field, $comboOptions, $value, array('data-validation' => $cssclass, "autocomplete" => "off", 'onclick' => $jsFunction));
                    $html .= '</div>';
                }else{
                    $comboOptions = array();
                    if (!empty($field->userfieldparams)) {
                        $obj_option = json_decode($field->userfieldparams);
                        $total_options= count($obj_option);
                        if($total_options % 2 == 0)
                        {
                            $field_width = 'style = " width:calc(100% / 2 - 4px); margin:2px 2px;"';
                        }else
                        {
                            $field_width = 'style = " width:calc(100% / 3 - 4px); margin:2px 2px;"';
                        }
                        $i = 0;
                        $jsFunction = '';
                        if ($field->depandant_field != null) {
                            $wpnonce = wp_create_nonce("data-for-depandant-field-".$field->depandant_field);
                            $jsFunction = "getDataForDepandantField('".$wpnonce."','" . $field->field . "','" . $field->depandant_field . "',2);";
                        }
                        $valuearray = jssupportticketphplib::JSST_explode(', ',$value);
                        $html .= '<div class="js-form-cust-rad-fld-wrp">';
                        foreach ($obj_option AS $option) {
                            $check = '';
                            $option = html_entity_decode($option);
                            if(in_array($option, $valuearray)){
                                $check = 'checked';
                            }
                            $html .= '<div class="js-ticket-radio-box" '.$field_width.'>';
                                $html .= '<input type="radio" ' . esc_attr($check) . ' class="radiobutton js-ticket-radio-btn '.esc_attr($cssclass).'" value="' . esc_attr($option) . '" id="' . esc_attr($field->field) . '_' . esc_attr($i) . '" name="' . esc_attr($field->field) . '" data-validation ="'.esc_attr($cssclass).'" onclick = "'.$jsFunction.'"> ';
                                $html .= '<label for="' . esc_attr($field->field) . '_' . esc_attr($i) . '" id="foruf_checkbox1">' . esc_html($option) . '</label>';
                            $html .= '</div>';
                            $i++;
                        }
                        $html .= '</div>';
                    }
                }

                break;
            case 'combo':
                $comboOptions = array();
                if (!empty($field->userfieldparams)) {
                    $obj_option = json_decode($field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $opt = html_entity_decode($opt);
                        $comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                    }
                }
                //code for handling dependent field
                $jsFunction = '';
                if ($field->depandant_field != null) {
                    $wpnonce = wp_create_nonce("data-for-depandant-field-".$field->depandant_field);
                    $jsFunction = "getDataForDepandantField('".$wpnonce."','" . $field->field . "','" . $field->depandant_field . "',1);";
                }
                //end
                $html .= JSSTformfield::select($field->field, $comboOptions, $value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($field->fieldtitle) , array('data-validation' => $cssclass, 'onchange' => $jsFunction, 'class' => 'inputbox js-form-select-field one'));
                break;
            case 'depandant_field':
                $comboOptions = array();
                if (!empty($field->userfieldparams)) {
                    $obj_option = $this->getDataForDepandantFieldByParentField($field->field, $userfielddataarray);
                    if (!empty($obj_option)) {
                        foreach ($obj_option as $opt) {
                            $opt = html_entity_decode($opt);
                            $comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                        }
                    }
                }
                //code for handling dependent field
                $jsFunction = '';
                if ($field->depandant_field != null) {
                    $wpnonce = wp_create_nonce("data-for-depandant-field-".$field->depandant_field);
                    $jsFunction = "getDataForDepandantField('".$wpnonce."','" . $field->field . "','" . $field->depandant_field . "');";
                }
                //end
                $html .= JSSTformfield::select($field->field, $comboOptions, $value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($field->fieldtitle) , array('data-validation' => $cssclass, 'onchange' => $jsFunction, 'class' => 'inputbox js-form-select-field one'));
                break;
            case 'multiple':
                $comboOptions = array();
                if (!empty($field->userfieldparams)) {
                    $obj_option = json_decode($field->userfieldparams);
                    foreach ($obj_option as $opt) {
                        $opt = html_entity_decode($opt);
                        $comboOptions[] = (object) array('id' => $opt, 'text' => $opt);
                    }
                }
                $array = $field->field;
                $array .= '[]';
                $html .= JSSTformfield::select($array, $comboOptions, $value, esc_html(__('Select', 'js-support-ticket')) . ' ' . esc_attr($field->fieldtitle) , array('data-validation' => $cssclass, 'multiple' => 'multiple','class' => 'inputbox js-form-multi-select-field'));
                break;
        }
        if($isadmin == 1){
            echo wp_kses($html, JSST_ALLOWED_TAGS);
            return;
        }
        $html .= '</div></div>';
        echo wp_kses($html, JSST_ALLOWED_TAGS);

    }

    function showCustomFields($field, $fieldfor, $params) {

        $fvalue = '';

        if(!empty($params)){
            $data = json_decode($params,true);
            if(is_array($data) && $data != ''){
                if(array_key_exists($field->field, $data)){
                    $fvalue = $data[$field->field];
                    $fvalue = jssupportticketphplib::JSST_htmlspecialchars($fvalue);
                }
            }
        }
        if($field->userfieldtype=='file'){

           if($fvalue !=null){
                $path = admin_url("?page=ticket&action=jstask&task=downloadbyname&id=".jssupportticket::$_data['custom']['ticketid']."&name=".$fvalue);
                $html = '
                    <div class="js_ticketattachment">
                        ' .  $fvalue . '
                        <a class="button" target="_blank" href="' . esc_url($path) . '">' . esc_html(__('Download', 'js-support-ticket')) . '</a>
                    </div>';
                $fvalue = $html;
            }
        }elseif($field->userfieldtype=='date' && !empty($fvalue)){
            if(jssupportticketphplib::JSST_strpos($fvalue , '1970') !== false){
                $fvalue = "";
            } else {
                $fvalue = date_i18n(jssupportticket::$_config['date_format'],strtotime($fvalue));
            }
        }
        $return_array['title'] = $field->fieldtitle;
        $return_array['value'] = $fvalue;
        return $return_array;
    }

    function userFieldsData($fieldfor, $listing = null, $multiformid = '') {
        if(!is_numeric($fieldfor)){
            return false;
        }
        if ($multiformid == '') {
            $multiformid = JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
        }
        if(!is_numeric($multiformid)){
            return false;
        }
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' isvisitorpublished = 1 ';
        } else {
            $published = ' published = 1 ';
        }
        $inquery = '';
        if ($listing == 1) {
            $inquery = ' AND showonlisting = 1 ';
        }
        if (!is_admin()) {
            $inquery .= ' AND adminonly != 1 ';
        }
        $query = "SELECT field,fieldtitle,isuserfield,userfieldtype,userfieldparams,multiformid  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $published . " AND fieldfor =" . esc_sql($fieldfor) . $inquery. " AND multiformid =" . esc_sql($multiformid). " ORDER BY ordering";
        $data = jssupportticket::$_db->get_results($query);
        return $data;
    }

    function userFieldsForSearch($fieldfor) {
        if(!is_numeric($fieldfor)){
            return false;
        }
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $inquery = ' isvisitorpublished = 1';
        } else {
            $inquery = ' published = 1 AND search_user =1';
        }
        if(!is_admin()){
            $inquery .= " AND adminonly != 1";
        }

        $query = "SELECT `rows`,`cols`,required,field,fieldtitle,isuserfield,userfieldtype,userfieldparams,depandant_field  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $inquery . " AND fieldfor =" . esc_sql($fieldfor) ." ORDER BY ordering ";
        $data = jssupportticket::$_db->get_results($query);
        return $data;
    }

    function adminFieldsForSearch($fieldfor) {
        if(!is_numeric($fieldfor)){
            return false;
        }

        $query = "SELECT `rows`,`cols`,required,field,fieldtitle,isuserfield,userfieldtype,userfieldparams,depandant_field  FROM " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND published = 1 AND search_admin =1 AND fieldfor =" . esc_sql($fieldfor) ." ORDER BY ordering ";
        $data = jssupportticket::$_db->get_results($query);
        return $data;
    }

    function getDataForDepandantFieldByParentField($fieldfor, $data) {
        if (JSSTincluder::getObjectClass('user')->isguest()) {
            $published = ' isvisitorpublished = 1 ';
        } else {
            $published = ' published = 1 ';
        }
        $value = '';
        $returnarray = array();
        $query = "SELECT field from " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $published . " AND depandant_field ='" . esc_sql($fieldfor) . "'";
        $field = jssupportticket::$_db->get_var($query);
        if ($data != null) {
            foreach ($data as $key => $val) {
                $key = html_entity_decode($key);
                if ($key == $field) {
                    $value = $val;
                }
            }
        }
        $query = "SELECT userfieldparams from " . jssupportticket::$_db->prefix . "js_ticket_fieldsordering WHERE isuserfield = 1 AND " . $published . " AND field ='" . esc_sql($fieldfor) . "'";
        $field = jssupportticket::$_db->get_var($query);
        $fieldarray = json_decode($field);
        foreach ($fieldarray as $key => $val) {
            $key = html_entity_decode($key);
            if ($value == $key)
                $returnarray = $val;
        }
        return $returnarray;
    }

}

?>
