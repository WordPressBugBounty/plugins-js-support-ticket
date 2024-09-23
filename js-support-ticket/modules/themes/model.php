<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTthemesModel {

    function storeTheme($data) {
        if (!current_user_can('manage_options')){
            die('Only Administrators can perform this action.');
        }
        $data = jssupportticket::JSST_sanitizeData($data);
        update_option('jsst_set_theme_colors', wp_json_encode($data));
        $return = require(JSST_PLUGIN_PATH . 'includes/css/style.php');

        if ($return) {
            JSSTmessage::setMessage(esc_html(__('The new theme has been applied', 'js-support-ticket')), 'updated');
        } else {
            JSSTmessage::setMessage(esc_html(__('Error applying the new theme', 'js-support-ticket')), 'error');
        }
        return;
    }

    function getColorCode($filestring, $colorNo) {
        if (strstr($filestring, '$color' . $colorNo)) {
            $path1 = jssupportticketphplib::JSST_strpos($filestring, '$color' . $colorNo);
            $path1 = jssupportticketphplib::JSST_strpos($filestring, '#', $path1);
            $path2 = jssupportticketphplib::JSST_strpos($filestring, ';', $path1);
            $colorcode = jssupportticketphplib::JSST_substr($filestring, $path1, $path2 - $path1 - 1);
            return $colorcode;
        }
    }

    function getCurrentTheme() {
        $color1 = "#4f6df5";
        $color2 = "#2b2b2b";
        $color3 = "#f5f2f5";
        $color4 = "#636363";
        $color5 = "#d1d1d1";
        $color6 = "#e7e7e7";
        $color7 = "#ffffff";
        $color8 = "#2DA1CB";
        $color9 = "#000000";
        $color_string_values = get_option("jsst_set_theme_colors");
        if($color_string_values != ''){
            $json_values = json_decode($color_string_values,true);
            if(is_array($json_values) && !empty($json_values)){
                $color1 = $json_values['color1'];
                $color2 = $json_values['color2'];
                $color3 = $json_values['color3'];
                $color4 = $json_values['color4'];
                $color5 = $json_values['color5'];
                $color6 = $json_values['color6'];
                $color7 = $json_values['color7'];
            }
        }
        $theme['color1'] = esc_attr($color1);
        $theme['color2'] = esc_attr($color2);
        $theme['color3'] = esc_attr($color3);
        $theme['color4'] = esc_attr($color4);
        $theme['color5'] = esc_attr($color5);
        $theme['color6'] = esc_attr($color6);
        $theme['color7'] = esc_attr($color7);
        $theme['color8'] = esc_attr($color8);
        $theme['color9'] = esc_attr($color9);

        $theme = apply_filters('cm_theme_colors', $theme, 'js-support-ticket');
        jssupportticket::$_data[0] = $theme;
        return;
    }
}
?>
