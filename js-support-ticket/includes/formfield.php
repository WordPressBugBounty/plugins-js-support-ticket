<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTformfield {
    /*
     * Create the form text field
     */

    static function text($jsst_name, $jsst_value, $jsst_extraattr = array()) {
        $jsst_textfield = '<input type="text" name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . '" value="' . esc_attr($jsst_value) . '" ';
        if (!empty($jsst_extraattr))
            foreach ($jsst_extraattr AS $jsst_key => $jsst_val)
                $jsst_textfield .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
        $jsst_textfield .= ' />';
        return $jsst_textfield;
    }
    /*
     * Create the form text field
     */

    static function email($jsst_name, $jsst_value, $jsst_extraattr = array()) {
        $jsst_textfield = '<input type="email" name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . '" value="' . esc_attr($jsst_value) . '" ';
        if (!empty($jsst_extraattr))
            foreach ($jsst_extraattr AS $jsst_key => $jsst_val)
                $jsst_textfield .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
        $jsst_textfield .= ' />';
        return $jsst_textfield;
    }

    /*
     * Create the form password field
     */

    static function password($jsst_name, $jsst_value, $jsst_extraattr = array()) {
        $jsst_textfield = '<input type="password" name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . '" value="' . esc_attr($jsst_value) . '" ';
        if (!empty($jsst_extraattr))
            foreach ($jsst_extraattr AS $jsst_key => $jsst_val)
                $jsst_textfield .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
        $jsst_textfield .= ' />';
        return $jsst_textfield;
    }

    /*
     * Create the form text area
     */

    static function textarea($jsst_name, $jsst_value, $jsst_extraattr = array()) {
        $jsst_textarea = '<textarea name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . '" ';
        if (!empty($jsst_extraattr))
            foreach ($jsst_extraattr AS $jsst_key => $jsst_val)
                $jsst_textarea .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
        $jsst_textarea .= ' >' . esc_html($jsst_value) . '</textarea>';
        return $jsst_textarea;
    }

    /*
     * Create the form hidden field
     */

    static function hidden($jsst_name, $jsst_value, $jsst_extraattr = array()) {
        $jsst_textfield = '<input type="hidden" name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . '" value="' . esc_attr($jsst_value) . '" ';
        if (!empty($jsst_extraattr))
            foreach ($jsst_extraattr AS $jsst_key => $jsst_val)
                $jsst_textfield .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
        $jsst_textfield .= ' />';
        return $jsst_textfield;
    }

    /*
     * Create the form submitbutton
     */

    static function submitbutton($jsst_name, $jsst_value, $jsst_extraattr = array()) {
        $jsst_textfield = '<input type="submit" name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . '" value="' . esc_attr($jsst_value) . '" ';
        if (!empty($jsst_extraattr))
            foreach ($jsst_extraattr AS $jsst_key => $jsst_val)
                $jsst_textfield .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
        $jsst_textfield .= ' />';
        return $jsst_textfield;
    }

    /*
     * Create the form button
     */

    static function button($jsst_name, $jsst_value, $jsst_extraattr = array()) {
        $jsst_textfield = '<input type="button" name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . '" value="' . esc_attr($jsst_value) . '" ';
        if (!empty($jsst_extraattr))
            foreach ($jsst_extraattr AS $jsst_key => $jsst_val)
                $jsst_textfield .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
        $jsst_textfield .= ' />';
        return $jsst_textfield;
    }

    /*
     * Create the form select field
     */

    static function select($jsst_name, $jsst_list, $jsst_defaultvalue, $jsst_title = '', $jsst_extraattr = array()) {
        $jsst_selectfield = '<select name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . '" ';
        if (!empty($jsst_extraattr))
            foreach ($jsst_extraattr AS $jsst_key => $jsst_val) {
                $jsst_selectfield .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
            }
        $jsst_selectfield .= ' >';
        if ($jsst_title != '') {
            $jsst_selectfield .= '<option value="">' . esc_html(jssupportticket::JSST_getVarValue($jsst_title)) . '</option>';
        }
        if (!empty($jsst_list))
            foreach ($jsst_list AS $jsst_record) {
                if ((is_array($jsst_defaultvalue) && in_array($jsst_record->id, $jsst_defaultvalue)) || $jsst_defaultvalue == $jsst_record->id)
                    $jsst_selectfield .= '<option selected="selected" value="' . esc_attr($jsst_record->id) . '">' . esc_html(jssupportticket::JSST_getVarValue($jsst_record->text)) . '</option>';
                else
                    $jsst_selectfield .= '<option value="' . esc_attr($jsst_record->id) . '">' . esc_html(jssupportticket::JSST_getVarValue($jsst_record->text)) . '</option>';
            }

        $jsst_selectfield .= '</select>';
        return $jsst_selectfield;
    }

    /*
     * Create the form radio button
     */

    static function radiobutton($jsst_name, $jsst_list, $jsst_defaultvalue, $jsst_extraattr = array()) {
        $jsst_radiobutton = '';
        $jsst_count = 1;
        foreach ($jsst_list AS $jsst_value => $jsst_label) {

            $jsst_radiobutton .= '<div class="jsst-formfield-radio-button-wrap" >';
            $jsst_radiobutton .= '<input type="radio" name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . esc_attr($jsst_count) . '" value="' . esc_attr($jsst_value) . '"';
            if ($jsst_defaultvalue == $jsst_value)
                $jsst_radiobutton .= ' checked="checked"';
            if (!empty($jsst_extraattr))
                foreach ($jsst_extraattr AS $jsst_key => $jsst_val) {
                    $jsst_radiobutton .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
                }
            $jsst_radiobutton .= '/><label id="for' . esc_attr($jsst_name). esc_attr($jsst_count) . '" for="' . esc_attr($jsst_name) . esc_attr($jsst_count) . '">' . esc_html($jsst_label) . '</label>';
            $jsst_radiobutton .= '</div>';
            $jsst_count++;
        }
        return $jsst_radiobutton;
    }

    /*
     * Create the form checkbox
     */

    static function checkbox($jsst_name, $jsst_list, $jsst_defaultvalue, $jsst_extraattr = array()) {
        $jsst_checkbox = '';
        $jsst_count = 1;
        foreach ($jsst_list AS $jsst_value => $jsst_label) {
            $jsst_checkbox .= '<input type="checkbox" name="' . esc_attr($jsst_name) . '" id="' . esc_attr($jsst_name) . esc_attr($jsst_count) . '" value="' . esc_attr($jsst_value) . '"';
            if(is_array($jsst_defaultvalue)){
                if (in_array($jsst_value, $jsst_defaultvalue))
                    $jsst_checkbox .= ' checked="checked"';
            }else{
                if ($jsst_defaultvalue == $jsst_value)
                    $jsst_checkbox .= ' checked="checked"';
            }

            if (!empty($jsst_extraattr))
                foreach ($jsst_extraattr AS $jsst_key => $jsst_val) {
                    $jsst_checkbox .= ' ' . esc_attr($jsst_key) . '="' . esc_attr($jsst_val) . '"';
                }
            $jsst_checkbox .= '/><label id="for' . esc_attr($jsst_name) . '" for="' . esc_attr($jsst_name) . esc_attr($jsst_count) . '">' . esc_html($jsst_label) . '</label>';
            $jsst_count++;
        }
        return $jsst_checkbox;
    }

    static function setFormData($jsst_data) {
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($jsst_data,'submitform','submitform');
    }

    static function getFormData() {
        $jsst_data = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('submitform',true);
        return $jsst_data;
    }
}

?>
