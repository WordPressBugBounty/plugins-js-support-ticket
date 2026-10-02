<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTEncoder {

    const JSST_ENC_CIPHER = 'AES-256-CBC';
    const JSST_ENC_MARKER = 'JSSTAESV1';

    private $jsst_securekey, $jsst_iv;

    function __construct($jsst_textkey = '') {
    }

    // Site-specific key derived from WordPress's own secret salts (never stored in our DB).
    private function getEncryptionKey() {
        return hash('sha256', wp_salt('secure_auth'), true);
    }

    function encrypt($jsst_input) {
        if ($jsst_input === '' || $jsst_input === null) {
            return $jsst_input;
        }
        if (!function_exists('openssl_encrypt')) {
            return jssupportticketphplib::JSST_safe_encoding($jsst_input);
        }
        $jsst_ivlen = openssl_cipher_iv_length(self::JSST_ENC_CIPHER);
        $jsst_iv = openssl_random_pseudo_bytes($jsst_ivlen);
        $jsst_ciphertext = openssl_encrypt($jsst_input, self::JSST_ENC_CIPHER, $this->getEncryptionKey(), OPENSSL_RAW_DATA, $jsst_iv);
        if ($jsst_ciphertext === false) {
            return jssupportticketphplib::JSST_safe_encoding($jsst_input);
        }
        return self::JSST_ENC_MARKER . base64_encode($jsst_iv . $jsst_ciphertext);
    }

    function decrypt($jsst_input) {
        if ($jsst_input === '' || $jsst_input === null) {
            return $jsst_input;
        }
        if (function_exists('openssl_decrypt') && strpos($jsst_input, self::JSST_ENC_MARKER) === 0) {
            /* As a link carries it, not as it was made. These tokens travel in
               query strings, unescaped (makeUrl() does not encode values), and a
               "+" there is read back as a space - so the "Reply on your ticket"
               link in a customer's email opened "Record not found" whenever the
               token happened to contain one, and always once Amazon SES click
               tracking had rewritten the link (live, 1 October 2026). Base64
               never contains a space, so a space can only be a "+" that was
               mangled; "-" and "_" are the URL-safe spellings of "+" and "/",
               and a dropped "=" pad is restored. Repairs links in mail already
               sent, which changing how links are written could not. */
            $jsst_b64 = strtr(str_replace(' ', '+', substr($jsst_input, strlen(self::JSST_ENC_MARKER))), '-_', '+/');
            if (strlen($jsst_b64) % 4) {
                $jsst_b64 .= str_repeat('=', 4 - strlen($jsst_b64) % 4);
            }
            $jsst_data = base64_decode($jsst_b64);
            if ($jsst_data !== false) {
                $jsst_ivlen = openssl_cipher_iv_length(self::JSST_ENC_CIPHER);
                if (strlen($jsst_data) > $jsst_ivlen) {
                    $jsst_iv = substr($jsst_data, 0, $jsst_ivlen);
                    $jsst_ciphertext = substr($jsst_data, $jsst_ivlen);
                    $jsst_plaintext = openssl_decrypt($jsst_ciphertext, self::JSST_ENC_CIPHER, $this->getEncryptionKey(), OPENSSL_RAW_DATA, $jsst_iv);
                    if ($jsst_plaintext !== false) {
                        return $jsst_plaintext;
                    }
                }
            }
        }
        // Legacy value stored before real encryption was added (plain base64) - decode for backward compatibility.
        return jssupportticketphplib::JSST_safe_decoding($jsst_input);
    }

}

?>
