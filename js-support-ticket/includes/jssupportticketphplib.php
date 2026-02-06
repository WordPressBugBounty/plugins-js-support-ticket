<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class jssupportticketphplib {

    function __construct() {
    }

    static function JSST_str_replace($jsst_search,$jsst_replace,$jsst_content){
        if($jsst_content == ''){
            return $jsst_content;
        }
        if($jsst_replace === null){
            return $jsst_content;
        }

        $jsst_content = str_replace($jsst_search, $jsst_replace, $jsst_content);
        return $jsst_content;
    }

    static function JSST_safe_encoding($jsst_string){
        if($jsst_string == ''){
            return $jsst_string;
        }
        $jsst_string = base64_encode($jsst_string);
        //return mb_convert_encoding($jsst_string, 'UTF-8', mb_detect_encoding($jsst_string));
        return $jsst_string;
    }

    static function JSST_safe_decoding($jsst_string){
        if($jsst_string == ''){
            return $jsst_string;
        }
        $jsst_string = base64_decode($jsst_string);
        return $jsst_string;
    }


    public static function JSST_strstr($jsst_haystack, $jsst_needle) {
        if($jsst_haystack == '' || $jsst_needle == ''){
            return false;
        }
        return strstr($jsst_haystack, $jsst_needle);
    }

    public static function JSST_explode($jsst_separator, $jsst_haystack) {
        if($jsst_separator == ''){
            return array();
        }
        if($jsst_haystack == ''){
            return array();
        }
        return explode($jsst_separator, $jsst_haystack);
    }

    // public static function JSST_strip_tags($jsst_string, $jsst_allowed_tags) {
    //     if($jsst_string == ''){
    //         return '';
    //     }
    //     return strip_tags($jsst_string, $jsst_allowed_tags);
    // }
    public static function JSST_strip_tags($jsst_string, $jsst_allowable_tags = NULL) {
      if (!is_null($jsst_string)) {
        return strip_tags($jsst_string, $jsst_allowable_tags);
      }
      return $jsst_string;
    }


    public static function JSST_htmlentities($jsst_string) {
        if($jsst_string == ''){
            return '';
        }
        return htmlentities($jsst_string);
    }

    public static function JSST_strtoupper($jsst_string) {
        if($jsst_string == ''){
            return '';
        }
        return strtoupper($jsst_string);
    }

    public static function JSST_basename($jsst_string,$jsst_suffix = '') {
        $jsst_basename = '';
        if($jsst_string !== ''){
           $jsst_basename = basename($jsst_string,$jsst_suffix);
        }
        return $jsst_basename;
    }

    public static function JSST_dirname($jsst_string,$jsst_lvls = 1) {
        $jsst_dirname = '';
        if($jsst_string !== ''){
           $jsst_dirname = dirname($jsst_string,$jsst_lvls);
        }
        return $jsst_dirname;
    }


    public static function JSST_substr($jsst_str, $jsst_start, $jsst_length = null) {
        $jsst_output = null;
        if ($jsst_str !== null) {
            if ($jsst_length !== null) {
                $jsst_output = substr($jsst_str, $jsst_start, $jsst_length);
            } else {
                $jsst_output = substr($jsst_str, $jsst_start);
            }
        }
        return $jsst_output;
    }


    public static function JSST_ucwords($jsst_str, $jsst_delimiters = "") {
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = ucwords($jsst_str, $jsst_delimiters);
        }
        return $jsst_output;
    }

    public static function JSST_preg_replace($jsst_pattern, $jsst_replacement, $jsst_subject, $jsst_limit = -1, &$jsst_count = null){
        $jsst_output = null;
        if ($jsst_pattern !== null && $jsst_replacement !== null && $jsst_subject !== null) {
            $jsst_output = preg_replace($jsst_pattern, $jsst_replacement, $jsst_subject, $jsst_limit, $jsst_count);
        }
        return $jsst_output;
    }

    public static function JSST_strlen($jsst_str){
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = strlen($jsst_str);
        }
        return $jsst_output;
    }


    public static function JSST_md5($jsst_str, $jsst_raw_output = false){
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = md5($jsst_str, $jsst_raw_output);
        }
        return $jsst_output;
    }

    public static function JSST_preg_match($jsst_pattern, $jsst_subject, &$jsst_matches = null, $jsst_flags = 0, $jsst_offset = 0){
        $jsst_output = null;
        if ($jsst_pattern !== null && $jsst_subject !== null) {
            $jsst_output = preg_match($jsst_pattern, $jsst_subject, $jsst_matches, $jsst_flags, $jsst_offset);
        }
        return $jsst_output;
    }

    public static function JSST_strtolower($jsst_str){
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = strtolower($jsst_str);
        }
        return $jsst_output;
    }

    public static function JSST_strpos($jsst_haystack, $jsst_needle, $jsst_offset = 0){
        $jsst_output = null;
        if ($jsst_haystack !== null && $jsst_needle !== null) {
            $jsst_output = strpos($jsst_haystack, $jsst_needle, $jsst_offset);
        }
        return $jsst_output;
    }

    public static function JSST_str_repeat($jsst_input, $jsst_multiplier){
        $jsst_output = null;
        if ($jsst_input !== null && $jsst_multiplier !== null) {
            $jsst_output = str_repeat($jsst_input, $jsst_multiplier);
        }
        return $jsst_output;
    }

    public static function JSST_stripslashes($jsst_str){
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = stripslashes($jsst_str);
        }
        return $jsst_output;
    }

    public static function JSST_htmlspecialchars($jsst_string, $jsst_flags = ENT_COMPAT | ENT_HTML401, $jsst_encoding = 'UTF-8', $jsst_double_encode = true){
        $jsst_output = null;
        if ($jsst_string !== null) {
            $jsst_output = htmlspecialchars($jsst_string, $jsst_flags, $jsst_encoding, $jsst_double_encode);
        }
        return $jsst_output;
    }

    public static function JSST_setcookie($jsst_name, $jsst_value = "", $jsst_expires = 0, $jsst_path = "", $jsst_domain = "", $jsst_secure = false, $jsst_httponly = false){
        $jsst_output = null;
        if ($jsst_name != null && $jsst_domain !== null) {
            if (!headers_sent()) {
          	    $jsst_output = setcookie($jsst_name, $jsst_value, $jsst_expires, $jsst_path, $jsst_domain, $jsst_secure, $jsst_httponly);
            }
        }
        return $jsst_output;
    }

    public static function JSST_urlencode($jsst_str){
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = urlencode($jsst_str);
        }
        return $jsst_output;
    }

    public static function JSST_crypt($jsst_str, $jsst_salt = null)
    {
        $jsst_output = null;
        if ($jsst_str !== null) {
            if ($jsst_salt !== null) {
                $jsst_output = crypt($jsst_str, $jsst_salt);
            } else {
                $jsst_output = crypt($jsst_str);
            }
        }
        return $jsst_output;
    }

    public static function JSST_urldecode($jsst_str)
    {
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = urldecode($jsst_str);
        }
        return $jsst_output;
    }

    public static function JSST_trim($jsst_str, $jsst_charlist = ""){
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = trim($jsst_str, $jsst_charlist);
        }
        return $jsst_output;
    }

    public static function JSST_rtrim($jsst_str, $jsst_chars = null){
        $jsst_output = null;
        if ($jsst_str !== null) {
            if ($jsst_chars !== null) {
                $jsst_output = rtrim($jsst_str, $jsst_chars);
            } else {
                $jsst_output = rtrim($jsst_str);
            }
        }
        return $jsst_output;
    }

    public static function JSST_addslashes($jsst_str){
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = addslashes($jsst_str);
        }
        return $jsst_output;
    }

    public static function JSST_stristr($jsst_haystack, $jsst_needle, $jsst_before_needle = false)
    {
        $jsst_output = null;
        if ($jsst_haystack !== null && $jsst_needle !== null) {
            $jsst_output = stristr($jsst_haystack, $jsst_needle, $jsst_before_needle);
        }
        return $jsst_output;
    }

    public static function JSST_ucfirst($jsst_str){
        $jsst_output = null;
        if ($jsst_str !== null) {
            $jsst_output = ucfirst($jsst_str);
        }
        return $jsst_output;
    }

    public static function JSST_parse_str($jsst_str, &$jsst_output){
        if ($jsst_str !== null) {
            parse_str($jsst_str, $jsst_output);
        }
    }


    public static function JSST_preg_split($jsst_pattern, $jsst_subject, $jsst_limit = -1, $jsst_flags = 0){
        $jsst_output = null;
        if ($jsst_pattern !== null && $jsst_subject !== null) {
            $jsst_output = preg_split($jsst_pattern, $jsst_subject, $jsst_limit, $jsst_flags);
        }
        return $jsst_output;
    }

    public static function JSST_number_format($jsst_num,$jsst_decimals = 0,$jsst_decimal_separator = ".",$jsst_thousands_separator = ","){
        $jsst_output = null;
        if ($jsst_num !== null) {
            $jsst_output = number_format($jsst_num,$jsst_decimals,$jsst_decimal_separator,$jsst_thousands_separator);
        }
        return $jsst_output;
    }

    public static function JSST_strtotime($jsst_datetime, $jsst_baseTimestamp = null){
        $jsst_output = null;
        if ($jsst_datetime !== null) {
            $jsst_output = strtotime($jsst_datetime, $jsst_baseTimestamp);
        }
        return $jsst_output;
    }

    public static function JSST_clean_file_path($jsst_path){ // this function to remove relative path componenets from module and file name
        if($jsst_path != ''){
            $jsst_path = str_replace('./','',$jsst_path);
            $jsst_path = str_replace('..','',$jsst_path);
        }
        return $jsst_path;
    }


}
?>
