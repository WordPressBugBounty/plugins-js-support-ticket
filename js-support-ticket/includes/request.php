<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTrequest {
    /*
     * Check Request from both the Get and post method
     */

    static function getVar($variable_name, $method = null, $defaultvalue = null, $typecast = null) {
        $value = null;
        if ($method == null) {
            if (isset($_GET[$variable_name])) {
                if(is_array($_GET[$variable_name])){
                    $value = Self::recursive_sanitize_text_field($_GET[$variable_name]);
                }else{
                    $value = sanitize_text_field($_GET[$variable_name]);
                }
            } elseif (isset($_POST[$variable_name])) {
                if(is_array($_POST[$variable_name])){
                    $value = Self::recursive_sanitize_text_field($_POST[$variable_name]);
                }else{
                    $value = sanitize_text_field($_POST[$variable_name]);
                }
            } elseif (get_query_var($variable_name)) {
                $value = get_query_var($variable_name);
            } elseif (isset(jssupportticket::$_data['sanitized_args'][$variable_name]) && jssupportticket::$_data['sanitized_args'][$variable_name] != '') {
                $value = jssupportticket::$_data['sanitized_args'][$variable_name];
            }
        } else {
            $method = jssupportticketphplib::JSST_strtolower($method);
            switch ($method) {
                case 'post':
                    if (isset($_POST[$variable_name]))
                        if(is_array($_POST[$variable_name])){
                            $value = Self::recursive_sanitize_text_field($_POST[$variable_name]);
                        }else{
                            $value = sanitize_text_field($_POST[$variable_name]);
                        }
                    break;
                case 'get':
                    if (isset($_GET[$variable_name]))
                        if(is_array($_GET[$variable_name])){
                            $value = Self::recursive_sanitize_text_field($_GET[$variable_name]);
                        }else{
                            $value = sanitize_text_field($_GET[$variable_name]);
                        }
                    break;
            }
        }
        if ($typecast != null) {
            $typecast = jssupportticketphplib::JSST_strtolower($typecast);
            switch ($typecast) {
                case "int":
                    $value = (int) $value;
                    break;
                case "string":
                    $value = (string) $value;
                    break;
            }
        }
        if ($value == null)
            $value = $defaultvalue;
        if(!is_array($value)){
            $value = jssupportticketphplib::JSST_stripslashes($value);
        }
        
        return $value;
    }

    /*
     * Check Request from both the Get and post method
     */

    static function get($method = null) {
        $array = null;
        if ($method != null) {
            $method = jssupportticketphplib::JSST_strtolower($method);
            switch ($method) {
                case 'post':
                    $array = filter_var_array($_POST);
                    break;
                case 'get':
                    $array = filter_var_array($_GET);
                    break;
            }
            //$array = array_map('stripslashes',$array);
            foreach($array as $key=>$value){
                if(is_string($value)){
                    $array[$key] = jssupportticketphplib::JSST_stripslashes($value);
                }
            }
        }
        return $array;
    }

    /*
     * Check Request from both the Get and post method
     */

    static function getLayout($layout, $method, $defaultvalue) {
        $layoutname = null;
        if ($method != null) {
            $method = jssupportticketphplib::JSST_strtolower($method);
            switch ($method) {
                case 'post':
                    $layoutname = sanitize_text_field($_POST[$layout]);
                    break;
                case 'get':
                    $layoutname = sanitize_text_field($_GET[$layout]);
                    break;
            }
        } else {
            if (isset($_POST[$layout]))
                $layoutname = sanitize_text_field($_POST[$layout]);
            elseif (isset($_GET[$layout]))
                $layoutname = sanitize_text_field($_GET[$layout]);
            elseif (get_query_var($layout))
                $layoutname = get_query_var($layout);
            elseif (isset(jssupportticket::$_data['sanitized_args'][$layout]) && jssupportticket::$_data['sanitized_args'][$layout] != '')
                $layoutname = jssupportticket::$_data['sanitized_args'][$layout];
        }
        if ($layoutname == null) {
            $layoutname = $defaultvalue;
        }
        if (is_admin()) {
            $layoutname = 'admin_' . $layoutname;
        }
        return $layoutname;
    }

    static function recursive_sanitize_text_field($array) {
        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = Self::recursive_sanitize_text_field($value);
            }
            else {
                $value = sanitize_text_field( $value );
            }
        }

        return $array;
    }    

}

?>
