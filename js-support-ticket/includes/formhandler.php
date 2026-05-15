<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTformhandler {

    function __construct() {
        add_action('init', array($this, 'checkFormRequest'));
        add_action('init', array($this, 'checkDeleteRequest'));
    }

    /*
     * Handle Form request
     */

    function checkFormRequest() {
        $jsst_formrequest = JSSTRequest::getVar('form_request', 'post');
        if ($jsst_formrequest == 'jssupportticket') {
            //handle the request
            $jsst_page_id = JSSTRequest::getVar('page_id', 'GET');
            jssupportticket::setPageID($jsst_page_id);
            $jsst_modulename = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = JSSTRequest::getVar($jsst_modulename);
            JSSTincluder::include_file($jsst_module);
            $jsst_class = 'JSST' . $jsst_module . "Controller";
            $jsst_task = JSSTRequest::getVar('task');
            $jsst_obj = new $jsst_class;
            $jsst_obj->$jsst_task();
        }
    }

    /*
     * Handle Form request
     */

    function checkDeleteRequest() {

        $jsst_jssupportticket_action = JSSTRequest::getVar( 'action', 'get' );

        if ( 'jstask' !== $jsst_jssupportticket_action ) {
            return;
        }
            //handle the request
        $jsst_page_id = absint(
            JSSTRequest::getVar( 'page_id', 'GET' )
        );

        jssupportticket::setPageID( $jsst_page_id );

        $jsst_modulename_key = is_admin() ? 'page' : 'jstmod';

        $jsst_module = sanitize_key(
            JSSTRequest::getVar( $jsst_modulename_key, '', '' )
        );

        $jsst_action = sanitize_key(
            JSSTRequest::getVar( 'task' )
        );

        if ( empty( $jsst_module ) || empty( $jsst_action ) ) {
            return;
        }

        /*
         * Prevent invalid class/method names.
         */
        if (
            preg_match( '/[^a-zA-Z0-9_]/', $jsst_module ) ||
            preg_match( '/[^a-zA-Z0-9_]/', $jsst_action )
        ) {
            return;
        }

        JSSTincluder::include_file( $jsst_module );

        $jsst_class = 'JSST' . $jsst_module . 'Controller';

        /*
         * Ensure controller exists.
         */
        if ( ! class_exists( $jsst_class ) ) {
            return;
        }

        $jsst_obj = new $jsst_class;

        /*
         * Block magic methods and private-style methods.
         */
        if (
            0 === strpos( $jsst_action, '__' ) ||
            0 === strpos( $jsst_action, '_' )
        ) {
            return;
        }

        /*
         * Ensure method is callable.
         */
        if ( ! is_callable( array( $jsst_obj, $jsst_action ) ) ) {
            return;
        }

        /*
         * Optional:
         * Require capability for admin requests.
         */
        if ( is_admin() && ! current_user_can( 'manage_options' ) ) {
            wp_die(
                esc_html__( 'You are not allowed to access this resource.', 'js-support-ticket' ),
                esc_html__( 'Access Denied', 'js-support-ticket' ),
                array( 'response' => 403 )
            );
        }

        call_user_func( array( $jsst_obj, $jsst_action ) );
    }

}

$jsst_formhandler = new JSSTformhandler();
?>
