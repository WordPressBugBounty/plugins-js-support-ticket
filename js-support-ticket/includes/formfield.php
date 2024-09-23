<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTformfield {
    /*
     * Create the form text field
     */

    static function text($name, $value, $extraattr = array()) {
        $textfield = '<input type="text" name="' . $name . '" id="' . $name . '" value="' . $value . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . $key . '="' . $val . '"';
        $textfield .= ' />';
        return $textfield;
    }
    /*
     * Create the form text field
     */

    static function email($name, $value, $extraattr = array()) {
        $textfield = '<input type="email" name="' . $name . '" id="' . $name . '" value="' . $value . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . $key . '="' . $val . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form password field
     */

    static function password($name, $value, $extraattr = array()) {
        $textfield = '<input type="password" name="' . $name . '" id="' . $name . '" value="' . $value . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . $key . '="' . $val . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form text area
     */

    static function textarea($name, $value, $extraattr = array()) {
        $textarea = '<textarea name="' . $name . '" id="' . $name . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textarea .= ' ' . $key . '="' . $val . '"';
        $textarea .= ' >' . $value . '</textarea>';
        return $textarea;
    }

    /*
     * Create the form hidden field
     */

    static function hidden($name, $value, $extraattr = array()) {
        $textfield = '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . $key . '="' . $val . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form submitbutton
     */

    static function submitbutton($name, $value, $extraattr = array()) {
        $textfield = '<input type="submit" name="' . $name . '" id="' . $name . '" value="' . $value . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . $key . '="' . $val . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form button
     */

    static function button($name, $value, $extraattr = array()) {
        $textfield = '<input type="button" name="' . $name . '" id="' . $name . '" value="' . $value . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val)
                $textfield .= ' ' . $key . '="' . $val . '"';
        $textfield .= ' />';
        return $textfield;
    }

    /*
     * Create the form select field
     */

    static function select($name, $list, $defaultvalue, $title = '', $extraattr = array()) {
        $selectfield = '<select name="' . $name . '" id="' . $name . '" ';
        if (!empty($extraattr))
            foreach ($extraattr AS $key => $val) {
                $selectfield .= ' ' . $key . '="' . $val . '"';
            }
        $selectfield .= ' >';
        if ($title != '') {
            $selectfield .= '<option value="">' . jssupportticket::JSST_getVarValue($title) . '</option>';
        }
        if (!empty($list))
            foreach ($list AS $record) {
                if ((is_array($defaultvalue) && in_array($record->id, $defaultvalue)) || $defaultvalue == $record->id)
                    $selectfield .= '<option selected="selected" value="' . $record->id . '">' . jssupportticket::JSST_getVarValue($record->text) . '</option>';
                else
                    $selectfield .= '<option value="' . $record->id . '">' . jssupportticket::JSST_getVarValue($record->text) . '</option>';
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
            $radiobutton .= '<input type="radio" name="' . $name . '" id="' . $name . $count . '" value="' . $value . '"';
            if ($defaultvalue == $value)
                $radiobutton .= ' checked="checked"';
            if (!empty($extraattr))
                foreach ($extraattr AS $key => $val) {
                    $radiobutton .= ' ' . $key . '="' . $val . '"';
                }
            $radiobutton .= '/><label id="for' . $name. $count . '" for="' . $name . $count . '">' . $label . '</label>';
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
            $checkbox .= '<input type="checkbox" name="' . $name . '" id="' . $name . $count . '" value="' . $value . '"';
            if(is_array($defaultvalue)){
                if (in_array($value, $defaultvalue))
                    $checkbox .= ' checked="checked"';
            }else{
                if ($defaultvalue == $value)
                    $checkbox .= ' checked="checked"';
            }

            if (!empty($extraattr))
                foreach ($extraattr AS $key => $val) {
                    $checkbox .= ' ' . $key . '="' . $val . '"';
                }
            $checkbox .= '/><label id="for' . $name . '" for="' . $name . $count . '">' . $label . '</label>';
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
