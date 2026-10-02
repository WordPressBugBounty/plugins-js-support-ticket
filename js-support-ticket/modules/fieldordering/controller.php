<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTfieldorderingController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {

        $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'fieldordering');
        jssupportticket::$jsst_data['sanitized_args']['jsst_nonce'] = esc_html(wp_create_nonce('jsst_nonce'));
        if (self::canaddfile($jsst_layout)) {
            switch ($jsst_layout) {
                case 'admin_fieldordering':
                    $jsst_fieldfor = JSSTrequest::getVar('fieldfor',null,1);
                    $jsst_formid = JSSTrequest::getVar('formid');
                    jssupportticket::$jsst_data['fieldfor'] = $jsst_fieldfor;
                    if ($jsst_fieldfor != 1) {
                        jssupportticket::$jsst_data['formid'] = 1;
                    }
                    else{
                        jssupportticket::$jsst_data['formid'] = $jsst_formid;
                        do_action('jsst_multiform_name_for_list' , $jsst_formid);
                    }
                    JSSTincluder::getJSModel('fieldordering')->getFieldOrderingForList( absint( $jsst_fieldfor ) );
                    break;
                case 'admin_adduserfeild':
                    $jsst_id = JSSTrequest::getVar('jssupportticketid');
                    $jsst_fieldfor = self::fieldFor();
                    jssupportticket::$jsst_data['fieldfor'] = $jsst_fieldfor;
                    // formid
                    if ($jsst_fieldfor != 1) {
                        jssupportticket::$jsst_data['formid'] = 1;
                    }
                    else{
                        $jsst_formid = JSSTrequest::getVar('formid');
                        jssupportticket::$jsst_data['formid'] = $jsst_formid;
                        do_action('jsst_multiform_name_for_list' , $jsst_formid);
                    }
                    // 
                    JSSTincluder::getJSModel('fieldordering')->getUserFieldbyId( absint( $jsst_id ),1);
                    break;
                default:
                    exit;
            }
            $jsst_module = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTrequest::getVar($jsst_module, null, 'fieldordering');
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    function canaddfile($jsst_layout) {
        $jsst_nonce_value = JSSTrequest::getVar('jsst_nonce');
        if ( wp_verify_nonce( $jsst_nonce_value, 'jsst_nonce') ) {
            if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket') {
                return false;
            } elseif (isset($_GET['action']) && $_GET['action'] == 'jstask') {
                return false;
            } else {
                if(!is_admin() && jssupportticketphplib::JSST_strpos($jsst_layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
        }
    }

    /**
     * Which field list this request belongs to.
     *
     * Every `task=` entry point below wants this and each one asked for it the
     * same way: read `fieldfor` from the request, and if it is not there fall
     * back to `jssupportticket::$jsst_data['fieldfor']`. That fallback cannot
     * work on a task request - `$jsst_data['fieldfor']` is set by the two
     * screen layouts in handleRequest(), and a task never goes through them -
     * so on every save, reorder and delete it read an array key that was not
     * there. A warning printed across the top of whatever came next, and a null
     * carried into the redirect.
     *
     * Defaults to 1, which is what the list screen has always defaulted to.
     */
    private static function fieldFor($jsst_source = null) {
        $jsst_fieldfor = JSSTrequest::getVar('fieldfor', $jsst_source);
        if ($jsst_fieldfor === '' || $jsst_fieldfor === null) {
            $jsst_fieldfor = isset(jssupportticket::$jsst_data['fieldfor'])
                ? jssupportticket::$jsst_data['fieldfor']
                : 1;
        }
        return $jsst_fieldfor;
    }

    /**
     * Where to go when a question has been added, changed or removed.
     *
     * Which list you came from is what `$jsst_fieldfor` says, and it has to be
     * asked. 6.5 retired *one* of the two screens this controller served -
     * core's ticket field list, `fieldfor` 1, replaced by the Forms screen -
     * and the marker that told them apart was removed as having nothing left
     * to distinguish. It had: this controller still serves Feedback Fields at
     * `fieldfor` 2, which was never retired and is still linked from the side
     * menu. So every save, add and delete of a feedback question sent the
     * administrator to the ticket Forms screen, which is not the screen they
     * were on and does not contain the field they just edited. The parameter
     * was being accepted and discarded - the signature said it mattered and
     * the body said it did not. (Roadmap 6.5-FORM-02)
     *
     * Anything that is not the retired ticket list goes back to its own list.
     * Written that way round deliberately: a third `fieldfor` added later gets
     * sent home rather than to a screen about tickets.
     */
    private static function returnUrl($jsst_fieldfor, $jsst_savedid = 0) {
        $jsst_formid = JSSTrequest::getVar('formid');
        if ($jsst_formid === '' || $jsst_formid === null) {
            /* The editor carries the form as a hidden `multiformid` and has no
               `formid` of its own, so on the way back from a save the query
               string is empty and the form had to be recovered from the post or
               lost. */
            $jsst_formid = JSSTrequest::getVar('multiformid', 'post', '');
        }
        $jsst_formid = absint($jsst_formid);
        if (!is_admin()) {
            return jssupportticket::makeUrl(array('jstmod' => 'fieldordering', 'jstlay' => 'userfeilds'));
        }
        $jsst_fieldfor = absint($jsst_fieldfor);
        if ($jsst_fieldfor > 0 && $jsst_fieldfor != 1) {
            return admin_url('admin.php?page=fieldordering&fieldfor=' . $jsst_fieldfor);
        }
        /* Back to the questions of the form the question belongs to, with the
           one just saved picked out, rather than to the list of forms - which
           made every edit end one screen away from where it started. */
        if ($jsst_formid > 0) {
            return admin_url('admin.php?page=multiform&jstlay=formquestions&formid=' . $jsst_formid
                . ($jsst_savedid > 0 ? '&saved=' . (int) $jsst_savedid : '')) . '#questions';
        }
        return admin_url('admin.php?page=multiform&jstlay=forms');
    }

    static function changeorder() {
        $jsst_id = JSSTrequest::getVar('fieldorderingid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-order-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_fieldfor = self::fieldFor();
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_action = JSSTrequest::getVar('order');
        JSSTincluder::getJSModel('fieldordering')->changeOrder( absint( $jsst_id ), $jsst_action);
        $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changepublishstatus() {
        $jsst_id = JSSTrequest::getVar('fieldorderingid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-publish-status-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_fieldfor = self::fieldFor();
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changePublishStatus( absint( $jsst_id ), $jsst_status);
        $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changevisitorpublishstatus() {
        $jsst_id = JSSTrequest::getVar('fieldorderingid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-visitor-publish-status-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_fieldfor = self::fieldFor();
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changeVisitorPublishStatus( absint( $jsst_id ), $jsst_status);
        $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function changerequiredstatus() {
        $jsst_id = JSSTrequest::getVar('fieldorderingid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'change-required-status-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_fieldfor = self::fieldFor();
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_status = JSSTrequest::getVar('status');
        JSSTincluder::getJSModel('fieldordering')->changeRequiredStatus( absint( $jsst_id ), $jsst_status);
        $jsst_url = admin_url("admin.php?page=fieldordering&jstlay=fieldordering&fieldfor=".esc_attr($jsst_fieldfor)."&formid=".esc_attr($jsst_formid));
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function saveuserfeild() {
        // Validate ID: Ensure it's a numeric value to prevent injection
        $jsst_id = JSSTrequest::getVar('id');
        if (!empty($jsst_id) && (!is_numeric($jsst_id) || intval($jsst_id) < 0)) {
            return false;
        }

        // Validate Nonce
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'save-userfeild-' . $jsst_id)) {
            die(esc_html__( 'Security check Failed', 'js-support-ticket' ));
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }

        // Retrieve and Sanitize Input Data
        $jsst_data = JSSTrequest::get('post');
        if (!is_array($jsst_data)) {
            return false; // Ensure data is an array
        }
        array_walk_recursive($jsst_data, function (&$jsst_item) {
            $jsst_item = sanitize_text_field($jsst_item);
        });

        // Validate fieldfor parameter
        $jsst_fieldfor = self::fieldFor();
        $jsst_fieldfor = sanitize_text_field($jsst_fieldfor); // Prevent malicious input

        // Validate formid parameter
        $jsst_formid = JSSTrequest::getVar('formid');
        $jsst_formid = sanitize_text_field($jsst_formid);

        // Store the sanitized user field using prepared statements
        JSSTincluder::getJSModel('fieldordering')->storeUserField($jsst_data);

        /* Which question to pick out on the way back: the one edited, or for a
           new one the newest row on its form. */
        $jsst_savedid = (int) $jsst_id;
        if ($jsst_savedid <= 0) {
            $jsst_mfid = absint(isset($jsst_data['multiformid']) ? $jsst_data['multiformid'] : $jsst_formid);
            $jsst_savedid = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT MAX(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_fieldsordering` WHERE fieldfor = %d AND multiformid = %d",
                absint($jsst_fieldfor) > 0 ? absint($jsst_fieldfor) : 1, $jsst_mfid));
        }

        // Redirect securely
        $jsst_url = self::returnUrl($jsst_fieldfor, $jsst_savedid);

        wp_safe_redirect($jsst_url);
        exit;
    }

    static function savefeild() {
        $jsst_id = JSSTrequest::getVar('id');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'save-feild-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        if (!current_user_can('manage_options')) { //only admin can change it.
            return false;
        }
        $jsst_data = JSSTrequest::get('post');
        $jsst_fieldfor = self::fieldFor();
        $jsst_formid = JSSTrequest::getVar('formid');
        JSSTincluder::getJSModel('fieldordering')->updateField($jsst_data);
        $jsst_url = self::returnUrl($jsst_fieldfor);
        wp_safe_redirect($jsst_url);
        exit;
    }

    static function removeuserfeild() {
        $jsst_id = JSSTrequest::getVar('jssupportticketid');
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $jsst_nonce, 'remove-userfeild-'.$jsst_id) ) {
            die( esc_html__( 'Security check Failed', 'js-support-ticket' ) );
        }
        $jsst_fieldfor = self::fieldFor();
        $jsst_formid = JSSTrequest::getVar('formid');
        JSSTincluder::getJSModel('fieldordering')->deleteUserField( absint( $jsst_id ) );
        $jsst_url = self::returnUrl($jsst_fieldfor);
        wp_safe_redirect($jsst_url);
        exit;
    }

}

$jsst_fieldorderingController = new JSSTfieldorderingController();
?>
