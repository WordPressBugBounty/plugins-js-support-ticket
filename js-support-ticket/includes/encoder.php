<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTEncoder {

    private $jsst_securekey, $jsst_iv;

    function __construct($jsst_textkey = '') {
        //$this->jsst_securekey = hash('sha256', $jsst_textkey, TRUE);
        //$this->iv = mcrypt_create_iv(32);
    }

    function encrypt($jsst_input) {
        //return jssupportticketphplib::JSST_safe_encoding(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->jsst_securekey, $jsst_input, MCRYPT_MODE_ECB, $this->iv));
        return jssupportticketphplib::JSST_safe_encoding($jsst_input);
    }

    function decrypt($jsst_input) {
        //return jssupportticketphplib::JSST_trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->jsst_securekey, jssupportticketphplib::JSST_safe_decoding($jsst_input), MCRYPT_MODE_ECB, $this->iv));
        return jssupportticketphplib::JSST_safe_decoding($jsst_input);
    }

}

?>
