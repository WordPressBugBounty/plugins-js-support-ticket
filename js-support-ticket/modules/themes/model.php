<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTthemesModel {

    function storeTheme($jsst_data) {
        if (!current_user_can('manage_options')){
            die('Only Administrators can perform this action.');
        }
        $jsst_data = jssupportticket::JSST_sanitizeData($jsst_data);
        update_option('jsst_set_theme_colors', wp_json_encode($jsst_data));
        // $jsst_return = require_once(JSST_PLUGIN_PATH . 'includes/css/style.php');

        // if ($jsst_return) {
            JSSTmessage::setMessage(esc_html(__('The new theme has been applied', 'js-support-ticket')), 'updated');
        // } else {
        //     JSSTmessage::setMessage(esc_html(__('Error applying the new theme', 'js-support-ticket')), 'error');
        // }
        return;
    }

    function getColorCode($jsst_filestring, $jsst_color1No) {
        if (strstr($jsst_filestring, '$jsst_color1' . $jsst_color1No)) {
            $jsst_path1 = jssupportticketphplib::JSST_strpos($jsst_filestring, '$jsst_color1' . $jsst_color1No);
            $jsst_path1 = jssupportticketphplib::JSST_strpos($jsst_filestring, '#', $jsst_path1);
            $jsst_path2 = jssupportticketphplib::JSST_strpos($jsst_filestring, ';', $jsst_path1);
            $jsst_color1code = jssupportticketphplib::JSST_substr($jsst_filestring, $jsst_path1, $jsst_path2 - $jsst_path1 - 1);
            return $jsst_color1code;
        }
    }

    function getCurrentTheme() {
        $jsst_color1 = "#4f6df5";
        $jsst_color2 = "#2b2b2b";
        $jsst_color3 = "#f5f2f5";
        $jsst_color4 = "#636363";
        $jsst_color5 = "#d1d1d1";
        $jsst_color6 = "#e7e7e7";
        $jsst_color7 = "#ffffff";
        $jsst_color8 = "#2DA1CB";
        $jsst_color9 = "#000000";
        $jsst_color1_string_values = get_option("jsst_set_theme_colors");
        if($jsst_color1_string_values != ''){
            $jsst_json_values = json_decode($jsst_color1_string_values,true);
            if(is_array($jsst_json_values) && !empty($jsst_json_values)){
                $jsst_color1 = $jsst_json_values['color1'];
                $jsst_color2 = $jsst_json_values['color2'];
                $jsst_color3 = $jsst_json_values['color3'];
                $jsst_color4 = $jsst_json_values['color4'];
                $jsst_color5 = $jsst_json_values['color5'];
                $jsst_color6 = $jsst_json_values['color6'];
                $jsst_color7 = $jsst_json_values['color7'];
            }
        }
        $jsst_theme['color1'] = esc_attr($jsst_color1);
        $jsst_theme['color2'] = esc_attr($jsst_color2);
        $jsst_theme['color3'] = esc_attr($jsst_color3);
        $jsst_theme['color4'] = esc_attr($jsst_color4);
        $jsst_theme['color5'] = esc_attr($jsst_color5);
        $jsst_theme['color6'] = esc_attr($jsst_color6);
        $jsst_theme['color7'] = esc_attr($jsst_color7);
        $jsst_theme['color8'] = esc_attr($jsst_color8);
        $jsst_theme['color9'] = esc_attr($jsst_color9);

        $jsst_theme = apply_filters('cm_theme_colors', $jsst_theme, 'js-support-ticket');
        jssupportticket::$jsst_data[0] = $jsst_theme;
        return;
    }
}
?>
