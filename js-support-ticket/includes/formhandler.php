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
            /* Help-desk forms post to the screen they were drawn on - a page of
               the site, or a wp-admin screen - and never to admin-ajax.php,
               whose calls have their own wp_ajax_* handlers. A form post
               arriving there was not sent by one of these forms, so it is not
               dispatched. */
            if (wp_doing_ajax()) {
                return;
            }
            //handle the request
            $jsst_page_id = JSSTRequest::getVar('page_id', 'GET');
            jssupportticket::setPageID($jsst_page_id);
            $jsst_modulename = (is_admin()) ? 'page' : 'jstmod';
            $jsst_module = sanitize_key(JSSTRequest::getVar($jsst_modulename));
            $jsst_task = sanitize_key(JSSTRequest::getVar('task'));
            if (empty($jsst_module) || empty($jsst_task) || 0 === strpos($jsst_task, '__') || 0 === strpos($jsst_task, '_')) {
                return;
            }
            // Before the controller file is loaded: loading it constructs the
            // controller, and a constructor already acts on the request.
            $this->refuseUnlessMayReachAdminDesk();
            JSSTincluder::include_file($jsst_module);
            $jsst_class = 'JSST' . $jsst_module . "Controller";
            if (!class_exists($jsst_class)) {
                return;
            }
            $jsst_obj = new $jsst_class;
            if (!is_callable(array($jsst_obj, $jsst_task))) {
                return;
            }
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

        /*
         * Before the controller file is loaded: loading it constructs the
         * controller, and a constructor already acts on the request.
         */
        $this->refuseUnlessMayReachAdminDesk();

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

        call_user_func( array( $jsst_obj, $jsst_action ) );
    }

    /*
     * Can this person reach the help desk in wp-admin at all?
     *
     * Asked by both dispatchers before a wp-admin task runs - the link-style
     * `action=jstask` one and the `form_request` form posts - so a task cannot
     * be reached through the one that forgot to ask. On the front end it asks
     * nothing: customers and guests post forms there, and each task decides.
     *
     * A coarse door, not the permission check. Every task behind it makes
     * its own decision — the canned-response status toggle asks
     * canReplyPublicly(), a ticket action asks canChangeTicketState(), and
     * the Agents add-on asks its own per-agent question. This only refuses
     * people who have no business on these screens in the first place.
     *
     * It used to demand manage_options, which is administrator and nobody
     * else, so every link-style action on every help-desk screen answered an
     * agent with a 403 — including actions the code behind them would have
     * allowed. The capabilities named here are the same two the help-desk
     * admin menu is built from, so whoever can see a screen can now use the
     * links on it, and the real decision is made where it always was.
     */
    private function refuseUnlessMayReachAdminDesk() {
        if ( ! is_admin() ) {
            return;
        }
        $jsst_may_reach = current_user_can( 'manage_options' );
        if ( ! $jsst_may_reach && class_exists( 'JSSTroles' ) ) {
            $jsst_may_reach = current_user_can( JSSTroles::CAP_ADMIN )
                    || current_user_can( JSSTroles::CAP_TICKETS );
        }
        /* And whether this is a desk they work at. Somebody restricted to the
           front-end desk has no help-desk screens in wp-admin, so a task
           arriving from one is a URL they have been given or guessed rather
           than a button they pressed. Checked here as well as at the menu,
           because the menu decides what is registered and this decides what
           runs. (Roadmap 4.5-FE-11) */
        if ( $jsst_may_reach && class_exists( 'JSSTworkspace' )
            && ! JSSTworkspace::mayUse( JSSTworkspace::SHELL_BACKEND ) ) {
            $jsst_may_reach = false;
        }
        if ( ! $jsst_may_reach ) {
            wp_die(
                esc_html__( 'You are not allowed to access this resource.', 'js-support-ticket' ),
                esc_html__( 'Access Denied', 'js-support-ticket' ),
                array( 'response' => 403 )
            );
        }
    }

}

$jsst_formhandler = new JSSTformhandler();
?>
