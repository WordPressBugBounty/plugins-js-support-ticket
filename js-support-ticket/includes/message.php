<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTmessage {
    /*
     * Set Message
     * @params $jsst_message = Your message to display
     * @params $jsst_type = Messages types => 'updated','error','update-nag'
     */
    public static $jsst_response_msg = array();

    static function setMessage($jsst_message, $jsst_type) {
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($jsst_message,$jsst_type,'notification');
    }

    static function getMessage() {
        $jsst_frontend = (is_admin()) ? '' : 'frontend';
        $jsst_divHtml = '';
        $jsst_option = get_option('jssupportticket', array());
        $jsst_notificationdata = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('notification',true);
        if (isset($jsst_notificationdata) && !empty($jsst_notificationdata)) {
            $jsst_data = $jsst_notificationdata;
            for ($jsst_i = 0; $jsst_i < COUNT($jsst_data['msg']); $jsst_i++){
                $jsst_divHtml .= '<div class=" ' . esc_attr($jsst_frontend) . ' ' . esc_attr($jsst_data['type'][$jsst_i]) . '"><p>' . $jsst_data['msg'][$jsst_i] . '</p></div>';
            }
        }
        echo wp_kses($jsst_divHtml, JSST_ALLOWED_TAGS);
    }

}

?>
