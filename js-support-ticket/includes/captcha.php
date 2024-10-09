<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTcaptcha {

    function getCaptchaForForm() {
        $rand = $this->randomNumber();
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($rand,'','jssupportticket_spamcheckid');
        $jssupportticket_rot13 = wp_rand(0, 1);
        JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable($jssupportticket_rot13,'','jssupportticket_rot13');

        $operator = 2;
        if ($operator == 2) {
            $tcalc = jssupportticket::$_config['owncaptcha_calculationtype'];
        }
        $max_value = 20;
        $negativ = 1;
        $operend_1 = wp_rand($negativ, $max_value);
        $operend_2 = wp_rand($negativ, $max_value);
        $operand = jssupportticket::$_config['owncaptcha_totaloperand'];
        if ($operand == 3) {
            $operend_3 = wp_rand($negativ, $max_value);
        }

        if (jssupportticket::$_config['owncaptcha_calculationtype'] == 2) { // Subtraction
            if (jssupportticket::$_config['owncaptcha_subtractionans'] == 1) {
                $ans = $operend_1 - $operend_2;
                if ($ans < 0) {
                    $one = $operend_2;
                    $operend_2 = $operend_1;
                    $operend_1 = $one;
                }
                if ($operand == 3) {
                    $ans = $operend_1 - $operend_2 - $operend_3;
                    if ($ans < 0) {
                        if ($operend_1 < $operend_2) {
                            $one = $operend_2;
                            $operend_2 = $operend_1;
                            $operend_1 = $one;
                        }
                        if ($operend_1 < $operend_3) {
                            $one = $operend_3;
                            $operend_3 = $operend_1;
                            $operend_1 = $one;
                        }
                    }
                }
            }
        }

        if ($tcalc == 0)
            $tcalc = wp_rand(1, 2);

        if ($tcalc == 1) { // Addition
            if ($jssupportticket_rot13 == 1) { // ROT13 coding
                if ($operand == 2) {
                    // The use of function str_rot13() is forbidden
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($operend_1 + $operend_2),'','jssupportticket_spamcheckresult');
                } elseif ($operand == 3) {
                    // The use of function str_rot13() is forbidden
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($operend_1 + $operend_2 + $operend_3),'','jssupportticket_spamcheckresult');
                }
            } else {
                if ($operand == 2) {
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($operend_1 + $operend_2),'','jssupportticket_spamcheckresult');
                } elseif ($operand == 3) {
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($operend_1 + $operend_2 + $operend_3),'','jssupportticket_spamcheckresult');
                }
            }
        } elseif ($tcalc == 2) { // Subtraction
            if ($jssupportticket_rot13 == 1) {
                if ($operand == 2) {
                    // The use of function str_rot13() is forbidden
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($operend_1 - $operend_2),'','jssupportticket_spamcheckresult');
                } elseif ($operand == 3) {
                    // The use of function str_rot13() is forbidden
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($operend_1 - $operend_2 - $operend_3),'','jssupportticket_spamcheckresult');
                }
            } else {
                if ($operand == 2) {
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($operend_1 - $operend_2),'','jssupportticket_spamcheckresult');
                } elseif ($operand == 3) {
                    JSSTincluder::getObjectClass('wphdnotification')->addSessionNotificationDataToTable(base64_encode($operend_1 - $operend_2 - $operend_3),'','jssupportticket_spamcheckresult');
                }
            }
        }
        $add_string = "";
        $add_string .= '<div><label for="' . esc_attr($rand) . '">';

        if ($tcalc == 1) {
            if ($operand == 2) {
                $add_string .= $operend_1 . ' ' . esc_html(__('Plus', 'js-support-ticket')) . ' ' . $operend_2 . ' ' . esc_html(__('Equals', 'js-support-ticket')) . ' ';
            } elseif ($operand == 3) {
                $add_string .= $operend_1 . ' ' . esc_html(__('Plus', 'js-support-ticket')) . ' ' . $operend_2 . ' ' . esc_html(__('Plus', 'js-support-ticket')) . ' ' . $operend_3 . ' ' . esc_html(__('Equals', 'js-support-ticket')) . ' ';
            }
        } elseif ($tcalc == 2) {
            $converttostring = 0;
            if ($operand == 2) {
                $add_string .= $operend_1 . ' ' . esc_html(__('Minus', 'js-support-ticket')) . ' ' . $operend_2 . ' ' . esc_html(__('Equals', 'js-support-ticket')) . ' ';
            } elseif ($operand == 3) {
                $add_string .= $operend_1 . ' ' . esc_html(__('Minus', 'js-support-ticket')) . ' ' . $operend_2 . ' ' . esc_html(__('Minus', 'js-support-ticket')) . ' ' . $operend_3 . ' ' . esc_html(__('Equals', 'js-support-ticket')) . ' ';
            }
        }

        $add_string .= '</label>';
        $add_string .= '<input type="text" name="' . esc_attr($rand) . '" id="' . esc_attr($rand) . '" size="3" class="inputbox js-ticket-recaptcha ' . esc_attr($rand) . '" value="" data-validation="required" />';
        $add_string .= '</div>';

        return $add_string;
    }

    function randomNumber() {
        $pw = '';

        // first character has to be a letter
        $characters = range('a', 'z');
        $pw .= $characters[wp_rand(0, 25)];

        // other characters arbitrarily
        $numbers = range(0, 9);
        $characters = array_merge($characters, $numbers);

        $pw_length = wp_rand(4, 12);

        for ($i = 0; $i < $pw_length; $i++) {
            $pw .= $characters[wp_rand(0, 35)];
        }
        return $pw;
    }

    private function performChecks() {
        $jssupportticket_rot13 = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('jssupportticket_rot13',true);
        if($jssupportticket_rot13 == 1){
            // The use of function str_rot13() is forbidden
            $spamcheckresult = jssupportticketphplib::JSST_safe_decoding(JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('jssupportticket_spamcheckresult',true));
        } else {
            $spamcheckresult = jssupportticketphplib::JSST_safe_decoding(JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('jssupportticket_spamcheckresult',true));
        }
        $spamcheck = JSSTincluder::getObjectClass('wphdnotification')->getNotificationDatabySessionId('jssupportticket_spamcheckid',true);
        $spamcheck = JSSTrequest::getVar($spamcheck, '', 'post');
        if (!is_numeric($spamcheckresult) || $spamcheckresult != $spamcheck) {
            return false; // Failed
        }
        /*        // Hidden field
          $type_hidden = 0;
          if ($type_hidden) {
          $hidden_field = $session->get('hidden_field', null, 'checkspamcalc');
          $session->clear('hidden_field', 'checkspamcalc');

          if (JJSSTrequest::getVar($hidden_field, '', 'post')) {
          return false; // Hidden field was filled out - failed
          }
          }
          // Time lock
          $type_time = 0;
          if ($type_time) {
          $time = $session->get('time', null, 'checkspamcalc');
          $session->clear('time', 'checkspamcalc');

          if (time() - $this->params->get('type_time_sec') <= $time) {
          return false; // Submitted too fast - failed
          }
          }
          $session->clear('ip', 'jsautoz_buyercheckspamcalc');
          $session->clear('saved_data', 'jsautoz_buyercheckspamcalc');
         */
        return true;
    }

    function checkCaptchaUserForm() {
        if (!$this->performChecks())
            $return = 2;
        else
            $return = 1;
        return $return;
    }

}

?>
