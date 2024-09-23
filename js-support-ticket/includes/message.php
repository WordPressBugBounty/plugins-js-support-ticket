<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTmessage {
    /*
     * Set Message
     * @params $message = Your message to display
     * @params $type = Messages types => 'updated','error','update-nag'
     */
    public static $jsst_response_msg = array();

    static function setMessage($message, $type) {
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($message,$type,'notification');
    }

    static function getMessage() {
        $frontend = (is_admin()) ? '' : 'frontend';
        $divHtml = '';
        $option = get_option('jssupportticket', array());
        $notificationdata = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('notification',true);
        if (isset($notificationdata) && !empty($notificationdata)) {
            $data = $notificationdata;
            for ($i = 0; $i < COUNT($data['msg']); $i++){
                $divHtml .= '<div class=" ' . esc_attr($frontend) . ' ' . esc_attr($data['type'][$i]) . '"><p>' . $data['msg'][$i] . '</p></div>';
            }
        }
        echo wp_kses($divHtml, JSST_ALLOWED_TAGS);
    }

}

?>
