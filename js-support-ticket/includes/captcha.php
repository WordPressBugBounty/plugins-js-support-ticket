<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTcaptcha {

    function getCaptchaForForm() {
        $jsst_rand = $this->randomNumber();
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($jsst_rand,'','jssupportticket_spamcheckid');
        $jsst_jssupportticket_rot13 = wp_rand(0, 1);
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($jsst_jssupportticket_rot13,'','jssupportticket_rot13');

        $jsst_operator = 2;
        if ($jsst_operator == 2) {
            $jsst_tcalc = jssupportticket::$_config['owncaptcha_calculationtype'];
        }
        $jsst_max_value = 20;
        $jsst_negativ = 1;
        $jsst_operend_1 = wp_rand($jsst_negativ, $jsst_max_value);
        $jsst_operend_2 = wp_rand($jsst_negativ, $jsst_max_value);
        $jsst_operand = jssupportticket::$_config['owncaptcha_totaloperand'];
        if ($jsst_operand == 3) {
            $jsst_operend_3 = wp_rand($jsst_negativ, $jsst_max_value);
        }

        if (jssupportticket::$_config['owncaptcha_calculationtype'] == 2) { // Subtraction
            if (jssupportticket::$_config['owncaptcha_subtractionans'] == 1) {
                $jsst_ans = $jsst_operend_1 - $jsst_operend_2;
                if ($jsst_ans < 0) {
                    $jsst_one = $jsst_operend_2;
                    $jsst_operend_2 = $jsst_operend_1;
                    $jsst_operend_1 = $jsst_one;
                }
                if ($jsst_operand == 3) {
                    $jsst_ans = $jsst_operend_1 - $jsst_operend_2 - $jsst_operend_3;
                    if ($jsst_ans < 0) {
                        if ($jsst_operend_1 < $jsst_operend_2) {
                            $jsst_one = $jsst_operend_2;
                            $jsst_operend_2 = $jsst_operend_1;
                            $jsst_operend_1 = $jsst_one;
                        }
                        if ($jsst_operend_1 < $jsst_operend_3) {
                            $jsst_one = $jsst_operend_3;
                            $jsst_operend_3 = $jsst_operend_1;
                            $jsst_operend_1 = $jsst_one;
                        }
                    }
                }
            }
        }

        if ($jsst_tcalc == 0)
            $jsst_tcalc = wp_rand(1, 2);

        if ($jsst_tcalc == 1) { // Addition
            if ($jsst_jssupportticket_rot13 == 1) { // ROT13 coding
                if ($jsst_operand == 2) {
                    // The use of function str_rot13() is forbidden
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($jsst_operend_1 + $jsst_operend_2),'','jssupportticket_spamcheckresult');
                } elseif ($jsst_operand == 3) {
                    // The use of function str_rot13() is forbidden
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($jsst_operend_1 + $jsst_operend_2 + $jsst_operend_3),'','jssupportticket_spamcheckresult');
                }
            } else {
                if ($jsst_operand == 2) {
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($jsst_operend_1 + $jsst_operend_2),'','jssupportticket_spamcheckresult');
                } elseif ($jsst_operand == 3) {
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($jsst_operend_1 + $jsst_operend_2 + $jsst_operend_3),'','jssupportticket_spamcheckresult');
                }
            }
        } elseif ($jsst_tcalc == 2) { // Subtraction
            if ($jsst_jssupportticket_rot13 == 1) {
                if ($jsst_operand == 2) {
                    // The use of function str_rot13() is forbidden
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($jsst_operend_1 - $jsst_operend_2),'','jssupportticket_spamcheckresult');
                } elseif ($jsst_operand == 3) {
                    // The use of function str_rot13() is forbidden
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($jsst_operend_1 - $jsst_operend_2 - $jsst_operend_3),'','jssupportticket_spamcheckresult');
                }
            } else {
                if ($jsst_operand == 2) {
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($jsst_operend_1 - $jsst_operend_2),'','jssupportticket_spamcheckresult');
                } elseif ($jsst_operand == 3) {
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($jsst_operend_1 - $jsst_operend_2 - $jsst_operend_3),'','jssupportticket_spamcheckresult');
                }
            }
        }
        $jsst_add_string = "";
        $jsst_add_string .= '<div><label for="' . esc_attr($jsst_rand) . '">';

        if ($jsst_tcalc == 1) {
            if ($jsst_operand == 2) {
                $jsst_add_string .= $jsst_operend_1 . ' ' . esc_html(__('Plus', 'js-support-ticket')) . ' ' . $jsst_operend_2 . ' ' . esc_html(__('Equals', 'js-support-ticket')) . ' ';
            } elseif ($jsst_operand == 3) {
                $jsst_add_string .= $jsst_operend_1 . ' ' . esc_html(__('Plus', 'js-support-ticket')) . ' ' . $jsst_operend_2 . ' ' . esc_html(__('Plus', 'js-support-ticket')) . ' ' . $jsst_operend_3 . ' ' . esc_html(__('Equals', 'js-support-ticket')) . ' ';
            }
        } elseif ($jsst_tcalc == 2) {
            $jsst_converttostring = 0;
            if ($jsst_operand == 2) {
                $jsst_add_string .= $jsst_operend_1 . ' ' . esc_html(__('Minus', 'js-support-ticket')) . ' ' . $jsst_operend_2 . ' ' . esc_html(__('Equals', 'js-support-ticket')) . ' ';
            } elseif ($jsst_operand == 3) {
                $jsst_add_string .= $jsst_operend_1 . ' ' . esc_html(__('Minus', 'js-support-ticket')) . ' ' . $jsst_operend_2 . ' ' . esc_html(__('Minus', 'js-support-ticket')) . ' ' . $jsst_operend_3 . ' ' . esc_html(__('Equals', 'js-support-ticket')) . ' ';
            }
        }

        $jsst_add_string .= '</label>';
        $jsst_add_string .= '<input type="text" name="' . esc_attr($jsst_rand) . '" id="' . esc_attr($jsst_rand) . '" size="3" class="inputbox js-ticket-recaptcha ' . esc_attr($jsst_rand) . '" value="" data-validation="required" />';
        $jsst_add_string .= '</div>';

        return $jsst_add_string;
    }

    function randomNumber() {
        $jsst_pw = '';

        // first character has to be a letter
        $jsst_characters = range('a', 'z');
        $jsst_pw .= $jsst_characters[wp_rand(0, 25)];

        // other characters arbitrarily
        $jsst_numbers = range(0, 9);
        $jsst_characters = array_merge($jsst_characters, $jsst_numbers);

        $jsst_pw_length = wp_rand(4, 12);

        for ($jsst_i = 0; $jsst_i < $jsst_pw_length; $jsst_i++) {
            $jsst_pw .= $jsst_characters[wp_rand(0, 35)];
        }
        return $jsst_pw;
    }

    private function performChecks() {
        $jsst_jssupportticket_rot13 = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('jssupportticket_rot13',true);
        if($jsst_jssupportticket_rot13 == 1){
            // The use of function str_rot13() is forbidden
            $jsst_spamcheckresult = jssupportticketphplib::JSST_safe_decoding(JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('jssupportticket_spamcheckresult',true));
        } else {
            $jsst_spamcheckresult = jssupportticketphplib::JSST_safe_decoding(JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('jssupportticket_spamcheckresult',true));
        }
        $jsst_spamcheck = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('jssupportticket_spamcheckid',true);
        $jsst_spamcheck = JSSTrequest::getVar($jsst_spamcheck, '', 'post');
        if (!is_numeric($jsst_spamcheckresult) || $jsst_spamcheckresult != $jsst_spamcheck) {
            return false; // Failed
        }
        /*        // Hidden field
          $jsst_type_hidden = 0;
          if ($jsst_type_hidden) {
          $jsst_hidden_field = $jsst_session->get('hidden_field', null, 'checkspamcalc');
          $jsst_session->clear('hidden_field', 'checkspamcalc');

          if (JJSSTrequest::getVar($jsst_hidden_field, '', 'post')) {
          return false; // Hidden field was filled out - failed
          }
          }
          // Time lock
          $jsst_type_time = 0;
          if ($jsst_type_time) {
          $jsst_time = $jsst_session->get('time', null, 'checkspamcalc');
          $jsst_session->clear('time', 'checkspamcalc');

          if (time() - $this->params->get('type_time_sec') <= $jsst_time) {
          return false; // Submitted too fast - failed
          }
          }
          $jsst_session->clear('ip', 'jsautoz_buyercheckspamcalc');
          $jsst_session->clear('saved_data', 'jsautoz_buyercheckspamcalc');
         */
        return true;
    }

    function checkCaptchaUserForm() {
        if (!$this->performChecks())
            $jsst_return = 2;
        else
            $jsst_return = 1;
        return $jsst_return;
    }

}

?>
