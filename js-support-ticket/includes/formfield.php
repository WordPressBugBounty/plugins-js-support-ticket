<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTformfield {
    /*
     * Create the form text field
     */

    static function text($name, $value, $extraattr = array()) {
        $textfield = '<input type="text" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
        $textfield .= ' />';
        return $textfield;
    }
    /*
     * Create the form text field
     */

    static function email($name, $value, $extraattr = array()) {
        $textfield = '<input type="email" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form password field
     */

    static function password($name, $value, $extraattr = array()) {
        $textfield = '<input type="password" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form text area
     */

    static function textarea($name, $value, $extraattr = array()) {
        $textarea = '<textarea name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textarea .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
        $textarea .= ' >' . esc_html($value) . '</textarea>';
        return $textarea;
    }

    /*
     * Create the form hidden field
     */

    static function hidden($name, $value, $extraattr = array()) {
        $textfield = '<input type="hidden" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form submitbutton
     */

    static function submitbutton($name, $value, $extraattr = array()) {
        $textfield = '<input type="submit" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form button
     */

    static function button($name, $value, $extraattr = array()) {
        $textfield = '<input type="button" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form select field
     */

    static function select($name, $list, $defaultvalue, $title = '', $extraattr = array()) {
        $selectfield = '<select name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val) {
                $selectfield .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
            }
        $selectfield .= ' >';
        if ($title != '') {
            $selectfield .= '<option value="">' . esc_html(jssupportticket::JSST_getVarValue($title)) . '</option>';
        }
        if (!empty($list))
            foreach ($list AS $record) {
                if ((is_array($defaultvalue) && in_array($record->id, $defaultvalue)) || $defaultvalue == $record->id)
                    $selectfield .= '<option selected="selected" value="' . esc_attr($record->id) . '">' . esc_html(jssupportticket::JSST_getVarValue($record->text)) . '</option>';
                else
                    $selectfield .= '<option value="' . esc_attr($record->id) . '">' . esc_html(jssupportticket::JSST_getVarValue($record->text)) . '</option>';
            }

        $selectfield .= '</select>';
        return $selectfield;
    }

    /*
     * Create the form radio button
     */

    static function radiobutton($name, $list, $defaultvalue, $extraattr = array()) {
        $radiobutton = '';
        $count = 1;
        foreach ($list AS $value => $label) {

            $radiobutton .= '<div class="jsst-formfield-radio-button-wrap" >';
            $radiobutton .= '<input type="radio" name="' . esc_attr($name) . '" id="' . esc_attr($name) . esc_attr($count) . '" value="' . esc_attr($value) . '"';
            if ($defaultvalue == $value)
                $radiobutton .= ' checked="checked"';
            if (!empty($extraattr))
                foreach ($extraattr AS $key => $val) {
                    $radiobutton .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
                }
            $radiobutton .= '/><label id="for' . esc_attr($name). esc_attr($count) . '" for="' . esc_attr($name) . esc_attr($count) . '">' . esc_html($label) . '</label>';
            $radiobutton .= '</div>';
            $count++;
        }
        return $radiobutton;
    }

    /*
     * Create the form checkbox
     */

    static function checkbox($name, $list, $defaultvalue, $extraattr = array()) {
        $checkbox = '';
        $count = 1;
        foreach ($list AS $value => $label) {
            $checkbox .= '<input type="checkbox" name="' . esc_attr($name) . '" id="' . esc_attr($name) . esc_attr($count) . '" value="' . esc_attr($value) . '"';
            if(is_array($defaultvalue)){
                if (in_array($value, $defaultvalue))
                    $checkbox .= ' checked="checked"';
            }else{
                if ($defaultvalue == $value)
                    $checkbox .= ' checked="checked"';
            }

            if (!empty($extraattr))
                foreach ($extraattr AS $key => $val) {
                    $checkbox .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
                }
            $checkbox .= '/><label id="for' . esc_attr($name) . '" for="' . esc_attr($name) . esc_attr($count) . '">' . esc_html($label) . '</label>';
            $count++;
        }
        return $checkbox;
    }

    static function setFormData($data) {
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($data,'submitform','submitform');
    }

    static function getFormData() {
        $data = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('submitform',true);
        return $data;
    }
}

?>
