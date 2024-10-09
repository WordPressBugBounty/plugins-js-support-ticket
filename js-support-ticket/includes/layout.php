<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTlayout {

    static function getNoRecordFound() {
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/no-record-icon.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Sorry', 'js-support-ticket')) . '!
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('No record found', 'js-support-ticket')) . '...!
						</span>
					</div>
				</div>
		';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }
    static function getNoRecordFoundForAjax() {
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/no-record-icon.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Sorry!', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('No record found ...!', 'js-support-ticket')) . '
						</span>
					</div>
				</div>
		';
        return wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getPermissionNotGranted() {
    	$loginval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_login_link');
        $loginlink = JSSTincluder::getJSModel('configuration')->getConfigValue('login_link');
        $registerval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_register_link');
        $registerlink = JSSTincluder::getJSModel('configuration')->getConfigValue('register_link');
        
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/not-permission-icon.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Access Denied', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('You have no permission to access this page', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-user-login-btn-wrp">';
							if (JSSTincluder::getObjectClass('user')->uid() == 0) {
								if ($loginval == 3){
                                    $hreflink = wp_login_url();
                                }
		                        else if($loginval == 2 && $loginlink != ""){
		                            $html .= '<a class="js-ticket-login-btn" href="'.esc_url($loginlink).'" title="Login">' . esc_html(__('Login', 'js-support-ticket')) . '</a>';
		                        }else{
		                            $html .= '<a class="js-ticket-login-btn" href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'login'))).'" title="Login">' . esc_html(__('Login', 'js-support-ticket')) . '</a>';
		                        }
		                        $is_enable = get_option('users_can_register');/*check to make sure user registration is enabled*/
	                            if ($is_enable) {
	                            	if($registerval == 3){
		                        	    $html .= '<a class="js-ticket-register-btn" href="'.esc_url(wp_registration_url()).'" title="Login">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
		                        	}else if($registerval == 2 && $registerlink != ""){
		                        	    $html .= '<a class="js-ticket-register-btn" href="'.esc_url($registerlink).'" title="Login">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
		                        	}else{
		                        		$html .= '<a class="js-ticket-register-btn" href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'userregister'))).'" title="Login">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
		                        	}
		                        }
	                    	}

                    $html .= '</span>
					</div>
				</div>
		';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getNotStaffMember() {
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/not-permission-icon.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Access Denied', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('User is not allowed to access this page.', 'js-support-ticket')) . '
						</span>
					</div>
				</div>
		';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getYouAreLoggedIn() {
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/already-loggedin.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Sorry!', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('You are already Logged In.', 'js-support-ticket')) . '
						</span>
					</div>
				</div>
		';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getStaffMemberDisable() {
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/not-permission-icon.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Access Denied!', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('Your account has been disabled, please contact the administrator.', 'js-support-ticket')) . '
						</span>
					</div>
				</div>
		';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getSystemOffline() {
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/offline.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Offline', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . wp_kses_post(jssupportticket::$_config['offline_message'], JSST_ALLOWED_TAGS) . '
						</span>
					</div>
				</div>
		';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getUserGuest($redirect_url = '') {
        $loginval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_login_link');
        $loginlink = JSSTincluder::getJSModel('configuration')->getConfigValue('login_link');
        $registerval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_register_link');
        $registerlink = JSSTincluder::getJSModel('configuration')->getConfigValue('register_link');
        $html = '
                <div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/not-login-icon.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('You are not logged In', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('To access the page, Please login', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-user-login-btn-wrp">';
							if ($loginval == 3){
                                $hreflink = wp_login_url();
                            }
	                        else if($loginval == 2 && $loginlink != ""){
	                            $html .= '<a class="js-ticket-login-btn" href="'.esc_url($loginlink).'" title="Login">' . esc_html(__('Login', 'js-support-ticket')) . '</a>';
	                        }else{
	                            $html .= '<a class="js-ticket-login-btn" href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'login', 'js_redirecturl'=>$redirect_url))).'" title="Login">' . esc_html(__('Login', 'js-support-ticket')) . '</a>';
	                        }
	                        $is_enable = get_option('users_can_register');/*check to make sure user registration is enabled*/
                            if ($is_enable) {
                            	if($registerval == 3){
	                        	    $html .= '<a class="js-ticket-register-btn" href="'.esc_url(wp_registration_url()).'" title="Login">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
	                        	}else if($registerval == 2 && $registerlink != ""){
	                        	    $html .= '<a class="js-ticket-register-btn" href="'.esc_url($registerlink).'" title="Login">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
	                        	}else{
	                        		$html .= '<a class="js-ticket-register-btn" href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'userregister', 'js_redirecturl'=>$redirect_url))).'" title="Login">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
	                        	}
	                        }

                    $html .= '</span>
                    </div>

				</div>
        ';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getYouAreNotAllowedToViewThisPage() {
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/not-permission-icon.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Sorry!', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('User is not allowed to view this Ticket', 'js-support-ticket')) . '
						</span>
					</div>
				</div>
		';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getRegistrationDisabled() {
        $html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/ban.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html(__('Sorry!', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('Registration has been disabled by admin, please contact the system administrator.', 'js-support-ticket')) . '
						</span>
					</div>
				</div>
		';
        echo wp_kses($html, JSST_ALLOWED_TAGS);
    }

    static function getFeedbackMessages($msg_type) {
    	if($msg_type == 2){
    		$img_var = '3.png';
    		$text_var_1 = esc_html(__('Sorry!', 'js-support-ticket'));
    		$text_var_2 = esc_html(__('You have already given the feedback for this ticket.', 'js-support-ticket'));
    	}elseif($msg_type == 3){
    		$img_var = 'no-record-icon.png';
    		$text_var_1 = esc_html(__('Sorry!', 'js-support-ticket'));
    		$text_var_2 = esc_html(__('Ticket not found...!', 'js-support-ticket'));
    	}else{
    		$img_var = 'not-permission-icondd.png';
    		$text_var_1 = esc_html(__('Sorry!', 'js-support-ticket'));
    		$text_var_2 = esc_html(__('User is not allowed to view this page', 'js-support-ticket'));
    	}
    	if($msg_type == 4){
			$html = '
					<div class="js-ticket-error-message-wrapper">
						<div class="js-ticket-message-image-wrapper">
							<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/success.png"/>
						</div>
						<div class="js-ticket-messages-data-wrapper">
							<span class="js-ticket-messages-main-text">
						    	'. esc_html(__('Thank you so much for your feedback', 'js-support-ticket')) .'
							</span>
							<span class="js-ticket-messages-block_text">
						    	'. wp_kses(jssupportticket::$_config['feedback_thanks_message'], JSST_ALLOWED_TAGS) .'
							</span>
						</div>
					</div>';
    	}else{
	        $html = '
					<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/'.esc_attr($img_var).'"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html($text_var_1) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' .wp_kses($text_var_2, JSST_ALLOWED_TAGS). '
						</span>
					</div>
				</div>
			';
		}
        echo wp_kses($html, JSST_ALLOWED_TAGS);
	}

}

?>
