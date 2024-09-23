<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTEncoder {

    private $securekey, $iv;

    function __construct($textkey = '') {
        //$this->securekey = hash('sha256', $textkey, TRUE);
        //$this->iv = mcrypt_create_iv(32);
    }

    function encrypt($input) {
        //return jssupportticketphplib::JSST_safe_encoding(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->securekey, $input, MCRYPT_MODE_ECB, $this->iv));
        return jssupportticketphplib::JSST_safe_encoding($input);
    }

    function decrypt($input) {
        //return jssupportticketphplib::JSST_trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->securekey, jssupportticketphplib::JSST_safe_decoding($input), MCRYPT_MODE_ECB, $this->iv));
        return jssupportticketphplib::JSST_safe_decoding($input);
    }

}

?>
