<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTwphdsession {

    public $jsst_sessionid;
    public $jsst_sessionexpire;
    private $jsst_sessiondata;
    private $jsst_datafor;
    private $jsst_nextsessionexpire;

    function __construct( ) {
        // add_action( 'init', array($this , 'init') );
        $this->init();

        if(in_array('sociallogin', jssupportticket::$_active_addons)){
            add_action( 'parse_request', array($this , 'jssupportticket_custom_session_handling') );

        }
    }

    function getSessionId(){
        return $this->jsst_sessionid;
    }

    function init(){
        if (isset($_COOKIE['_wpjshd_session_'])) {
            $jsst_cookie = jssupportticket::JSST_sanitizeData(stripslashes($_COOKIE['_wpjshd_session_'])); // JSST_sanitizeData() function uses wordpress santize functions
            $jsst_user_cookie = jssupportticketphplib::JSST_explode('/', $jsst_cookie);
            $this->jsst_sessionid = jssupportticketphplib::JSST_preg_replace("/[^A-Za-z0-9_]/", '', $jsst_user_cookie[0]);
            $this->jsst_sessionexpire = absint($jsst_user_cookie[1]);
            $this->jsst_nextsessionexpire = absint($jsst_user_cookie[2]);
            // Update options session expiration
            if (time() > $this->jsst_nextsessionexpire) {
                $this->jshd_set_cookies_expiration();
            }
        } else {
            $jsst_sessionid = $this->jshd_generate_id();
            $this->jsst_sessionid = $jsst_sessionid . get_option( '_wpjshd_session_', 0 );
            $this->jshd_set_cookies_expiration();
        }
        $this->jshd_set_user_cookies();
        return $this->jsst_sessionid;
    }

    private function jshd_set_cookies_expiration(){
        $this->jsst_sessionexpire = time() + (int)(30*60);
        $this->jsst_nextsessionexpire = time() + (int)(60*60);
    }

    private function jshd_generate_id(){
        do_action('jssupportticket_load_phpass');
        $jsst_hash = new PasswordHash( 16, false );

        return jssupportticketphplib::JSST_md5( $jsst_hash->get_random_bytes( 32 ) );
    }

    private function jshd_set_user_cookies(){
        /* WordPress defines the cookie constants part way through wp-settings,
           after the mu-plugins have loaded and before the ordinary plugins do.
           This class is constructed from the plugin bootstrap, which is
           normally after that point and is not always: a copy dropped in
           mu-plugins, and the WordPress test suite, both construct it earlier,
           and an undefined constant is a fatal rather than a warning in PHP 8.
           The defaults here are WordPress's own for a site at the domain root. */
        $jsst_cookiepath   = defined('COOKIEPATH') ? COOKIEPATH : '/';
        $jsst_cookiedomain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

        jssupportticketphplib::JSST_setcookie( '_wpjshd_session_', $this->jsst_sessionid . '/' . $this->jsst_sessionexpire . '/' . $this->jsst_nextsessionexpire , $this->jsst_sessionexpire, $jsst_cookiepath, $jsst_cookiedomain);
        $jsst_count = get_option( '_wpjshd_session_', 0 );
        update_option( '_wpjshd_session_', ++$jsst_count);
    }

    public function jssupportticket_custom_session_handling(){
        if(function_exists('session_start')){
            if(session_status() == PHP_SESSION_NONE){
                session_start();
            }
        }
    }

}

?>
