<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTrequest {
    /*
     * Check Request from both the Get and post method
     */

    static function getVar($jsst_variable_name, $jsst_method = null, $jsst_defaultvalue = null, $jsst_typecast = null) {
        $jsst_value = null;
        if ($jsst_method == null) {
            if (isset($_GET[$jsst_variable_name])) {
                if(is_array($_GET[$jsst_variable_name])){
                    $jsst_value = Self::recursive_sanitize_text_field($_GET[$jsst_variable_name]);
                }else{
                    $jsst_value = sanitize_text_field($_GET[$jsst_variable_name]);
                }
            } elseif (isset($_POST[$jsst_variable_name])) {
                if(is_array($_POST[$jsst_variable_name])){
                    $jsst_value = Self::recursive_sanitize_text_field($_POST[$jsst_variable_name]);
                }else{
                    $jsst_value = sanitize_text_field($_POST[$jsst_variable_name]);
                }
            } elseif (get_query_var($jsst_variable_name)) {
                $jsst_value = get_query_var($jsst_variable_name);
            } elseif (isset(jssupportticket::$jsst_data['sanitized_args'][$jsst_variable_name]) && jssupportticket::$jsst_data['sanitized_args'][$jsst_variable_name] != '') {
                $jsst_value = jssupportticket::$jsst_data['sanitized_args'][$jsst_variable_name];
            }
        } else {
            $jsst_method = jssupportticketphplib::JSST_strtolower($jsst_method);
            switch ($jsst_method) {
                case 'post':
                    if (isset($_POST[$jsst_variable_name]))
                        if(is_array($_POST[$jsst_variable_name])){
                            $jsst_value = Self::recursive_sanitize_text_field($_POST[$jsst_variable_name]);
                        }else{
                            $jsst_value = sanitize_text_field($_POST[$jsst_variable_name]);
                        }
                    break;
                case 'get':
                    if (isset($_GET[$jsst_variable_name]))
                        if(is_array($_GET[$jsst_variable_name])){
                            $jsst_value = Self::recursive_sanitize_text_field($_GET[$jsst_variable_name]);
                        }else{
                            $jsst_value = sanitize_text_field($_GET[$jsst_variable_name]);
                        }
                    break;
            }
        }
        if ($jsst_typecast != null) {
            $jsst_typecast = jssupportticketphplib::JSST_strtolower($jsst_typecast);
            switch ($jsst_typecast) {
                case "int":
                    $jsst_value = (int) $jsst_value;
                    break;
                case "string":
                    $jsst_value = (string) $jsst_value;
                    break;
            }
        }
        if ($jsst_value == null)
            $jsst_value = $jsst_defaultvalue;
        if(!is_array($jsst_value)){
            $jsst_value = jssupportticketphplib::JSST_stripslashes($jsst_value);
        }
        
        return $jsst_value;
    }

    /*
     * Check Request from both the Get and post method
     */

    static function get($jsst_method = null) {
        $jsst_array = null;
        if ($jsst_method != null) {
            $jsst_method = jssupportticketphplib::JSST_strtolower($jsst_method);
            switch ($jsst_method) {
                case 'post':
                    $jsst_array = filter_var_array($_POST);
                    break;
                case 'get':
                    $jsst_array = filter_var_array($_GET);
                    break;
            }
            //$jsst_array = array_map('stripslashes',$jsst_array);
            foreach($jsst_array as $jsst_key=>$jsst_value){
                if(is_string($jsst_value)){
                    $jsst_array[$jsst_key] = jssupportticketphplib::JSST_stripslashes($jsst_value);
                }
            }
        }
        return $jsst_array;
    }

    /*
     * Check Request from both the Get and post method
     */

    static function getLayout($jsst_layout, $jsst_method, $jsst_defaultvalue) {
        $jsst_layoutname = null;
        if ($jsst_method != null) {
            $jsst_method = jssupportticketphplib::JSST_strtolower($jsst_method);
            switch ($jsst_method) {
                case 'post':
                    $jsst_layoutname = sanitize_text_field($_POST[$jsst_layout]);
                    break;
                case 'get':
                    $jsst_layoutname = sanitize_text_field($_GET[$jsst_layout]);
                    break;
            }
        } else {
            if (isset($_POST[$jsst_layout]))
                $jsst_layoutname = sanitize_text_field($_POST[$jsst_layout]);
            elseif (isset($_GET[$jsst_layout]))
                $jsst_layoutname = sanitize_text_field($_GET[$jsst_layout]);
            elseif (get_query_var($jsst_layout))
                $jsst_layoutname = get_query_var($jsst_layout);
            elseif (isset(jssupportticket::$jsst_data['sanitized_args'][$jsst_layout]) && jssupportticket::$jsst_data['sanitized_args'][$jsst_layout] != '')
                $jsst_layoutname = jssupportticket::$jsst_data['sanitized_args'][$jsst_layout];
        }
        if ($jsst_layoutname == null) {
            $jsst_layoutname = $jsst_defaultvalue;
        }
        if (is_admin()) {
            $jsst_layoutname = 'admin_' . $jsst_layoutname;
        }
        return $jsst_layoutname;
    }

    static function recursive_sanitize_text_field($jsst_array) {
        foreach ( $jsst_array as $jsst_key => &$jsst_value ) {
            if ( is_array( $jsst_value ) ) {
                $jsst_value = Self::recursive_sanitize_text_field($jsst_value);
            }
            else {
                $jsst_value = sanitize_text_field( $jsst_value );
            }
        }

        return $jsst_array;
    }    

}

?>
