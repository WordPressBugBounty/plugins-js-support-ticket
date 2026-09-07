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

    /**
     * While muted, setMessage() records nothing.
     *
     * A bulk action runs the ordinary single-ticket code for every ticket in the
     * selection, and each of those sets its own message. On a selection of fifty
     * that buries the screen, so the caller mutes them and prints one summary
     * instead. (Roadmap 4.0-CORE-05)
     */
    private static $jsst_muted = false;

    static function mute() {
        self::$jsst_muted = true;
    }

    static function unmute() {
        self::$jsst_muted = false;
    }

    /**
     * @param string $jsst_doc id of a diagnostic page in JSSTdocs.
     *
     * The id is carried alongside the message rather than baked into it, and is
     * only turned into a link at render time if the catalogue still has a page
     * under that id. A message naming a page that has since been renamed loses
     * its link and keeps its text, instead of offering a dead one.
     * (Roadmap 4.0-OPS-03)
     */
    static function setMessage($jsst_message, $jsst_type, $jsst_doc = '') {
        if (self::$jsst_muted) {
            return;
        }
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($jsst_message,$jsst_type,'notification',null,$jsst_doc);
    }

    static function getMessage() {
        $jsst_frontend = (is_admin()) ? '' : 'frontend';
        $jsst_divHtml = '';
        $jsst_option = get_option('jssupportticket', array());
        $jsst_notificationdata = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('notification',true);
        if (isset($jsst_notificationdata) && !empty($jsst_notificationdata)) {
            $jsst_data = $jsst_notificationdata;
            for ($jsst_i = 0; $jsst_i < COUNT($jsst_data['msg']); $jsst_i++){
                $jsst_divHtml .= '<div class=" ' . esc_attr($jsst_frontend) . ' ' . esc_attr($jsst_data['type'][$jsst_i]) . '"><p>' . esc_html($jsst_data['msg'][$jsst_i]);
                /* The page that explains this one, when the message named one
                   and the catalogue still has it. Admin only — the diagnostic
                   pages are written for whoever runs the site, and a customer
                   who cannot open them is better off without the link.
                   (Roadmap 4.0-OPS-03) */
                $jsst_doc = isset($jsst_data['doc'][$jsst_i]) ? $jsst_data['doc'][$jsst_i] : '';
                if ($jsst_doc !== '' && is_admin() && current_user_can('manage_options')
                    && class_exists('JSSTdocs') && JSSTdocs::exists($jsst_doc)) {
                    $jsst_divHtml .= ' <a class="jsst-doc-link" href="' . esc_url(JSSTdocs::url($jsst_doc)) . '">'
                        . esc_html(__('What to check', 'js-support-ticket')) . '</a>';
                }
                $jsst_divHtml .= '</p></div>';
            }
        }
        echo wp_kses($jsst_divHtml, JSST_ALLOWED_TAGS);
    }

}

?>
