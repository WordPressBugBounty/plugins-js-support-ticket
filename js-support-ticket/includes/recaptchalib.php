<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

    if(! function_exists('JSSTGoogleRecaptchaHTTPPost')){
        function JSSTGoogleRecaptchaHTTPPost($sharedkey , $grresponse) {
            $google_url = "https://www.google.com/recaptcha/api/siteverify";
            $secret = $sharedkey;
            $ip = jssupportticket::JSST_sanitizeData($_SERVER['REMOTE_ADDR']); // JSST_sanitizeData() function uses wordpress santize functions
            // $url = $google_url."?secret=".$secret."&response=".$grresponse."&remoteip=".$ip;
            $post_data = array();
            $post_data['secret'] = $secret;
            $post_data['response'] = $grresponse;
            $post_data['remoteip'] = $ip;

            $response = wp_remote_post( $google_url, array('body' => $post_data,'timeout'=>7,'sslverify'=>false));
            if( !is_wp_error($response) && $response['response']['code'] == 200 && isset($response['body']) ){
                $result = $response['body'];
            }else{
                $result = false;
                if(!is_wp_error($response)){
                   $error = $response['response']['message'];
               }else{
                    $error = $response->get_error_message();
               }
            }
            if($result){
                $res= json_decode($result, true);
            }else{
                return FALSE;
            }
            //reCaptcha success check
            if($res['success']) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
?>
