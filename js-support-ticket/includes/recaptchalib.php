<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

    if(! function_exists('JSSTGoogleRecaptchaHTTPPost')){
        function JSSTGoogleRecaptchaHTTPPost($jsst_sharedkey , $jsst_grresponse) {
            $jsst_google_url = "https://www.google.com/recaptcha/api/siteverify";
            $jsst_secret = $jsst_sharedkey;
            $jsst_ip = jssupportticket::JSST_sanitizeData($_SERVER['REMOTE_ADDR']); // JSST_sanitizeData() function uses wordpress santize functions
            // $jsst_url = $jsst_google_url."?secret=".$jsst_secret."&response=".$jsst_grresponse."&remoteip=".$jsst_ip;
            $jsst_post_data = array();
            $jsst_post_data['secret'] = $jsst_secret;
            $jsst_post_data['response'] = $jsst_grresponse;
            $jsst_post_data['remoteip'] = $jsst_ip;

            $jsst_response = wp_remote_post( $jsst_google_url, array('body' => $jsst_post_data,'timeout'=>7,'sslverify'=>false));
            if( !is_wp_error($jsst_response) && $jsst_response['response']['code'] == 200 && isset($jsst_response['body']) ){
                $jsst_result = $jsst_response['body'];
            }else{
                $jsst_result = false;
                if(!is_wp_error($jsst_response)){
                   $jsst_error = $jsst_response['response']['message'];
               }else{
                    $jsst_error = $jsst_response->get_error_message();
               }
            }
            if($jsst_result){
                $jsst_res= json_decode($jsst_result, true);
            }else{
                return FALSE;
            }
            //reCaptcha success check
            if($jsst_res['success']) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
?>
