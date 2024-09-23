<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTwphdsession {

    public $sessionid;
    public $sessionexpire;
    private $sessiondata;
    private $datafor;
    private $nextsessionexpire;

    function __construct( ) {
        // add_action( 'init', array($this , 'init') );
        $this->init();

        if(in_array('sociallogin', jssupportticket::$_active_addons)){
            add_action( 'parse_request', array($this , 'jssupportticket_custom_session_handling') );

        }
    }

    function getSessionId(){
        return $this->sessionid;
    }

    function init(){
        if (isset($_COOKIE['_wpjshd_session_'])) {
            $cookie = jssupportticket::JSST_sanitizeData(stripslashes($_COOKIE['_wpjshd_session_'])); // JSST_sanitizeData() function uses wordpress santize functions
            $user_cookie = jssupportticketphplib::JSST_explode('/', $cookie);
            $this->sessionid = jssupportticketphplib::JSST_preg_replace("/[^A-Za-z0-9_]/", '', $user_cookie[0]);
            $this->sessionexpire = absint($user_cookie[1]);
            $this->nextsessionexpire = absint($user_cookie[2]);
            // Update options session expiration
            if (time() > $this->nextsessionexpire) {
                $this->jshd_set_cookies_expiration();
            }
        } else {
            $sessionid = $this->jshd_generate_id();
            $this->sessionid = $sessionid . get_option( '_wpjshd_session_', 0 );
            $this->jshd_set_cookies_expiration();
        }
        $this->jshd_set_user_cookies();
        return $this->sessionid;
    }

    private function jshd_set_cookies_expiration(){
        $this->sessionexpire = time() + (int)(30*60);
        $this->nextsessionexpire = time() + (int)(60*60);
    }

    private function jshd_generate_id(){
        do_action('jssupportticket_load_phpass');
        $hash = new PasswordHash( 16, false );

        return jssupportticketphplib::JSST_md5( $hash->get_random_bytes( 32 ) );
    }

    private function jshd_set_user_cookies(){
        jssupportticketphplib::JSST_setcookie( '_wpjshd_session_', $this->sessionid . '/' . $this->sessionexpire . '/' . $this->nextsessionexpire , $this->sessionexpire, COOKIEPATH, COOKIE_DOMAIN);
        $count = get_option( '_wpjshd_session_', 0 );
        update_option( '_wpjshd_session_', ++$count);
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
