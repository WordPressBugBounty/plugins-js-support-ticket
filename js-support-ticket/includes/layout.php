<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTlayout {

    static function getNoRecordFound() {
        $jsst_html = '
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
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }
    static function getNoRecordFoundForAjax() {
        $jsst_html = '
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
        return wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getPermissionNotGranted() {
    	$jsst_loginval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_login_link');
        $jsst_loginlink = JSSTincluder::getJSModel('configuration')->getConfigValue('login_link');
        $jsst_registerval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_register_link');
        $jsst_registerlink = JSSTincluder::getJSModel('configuration')->getConfigValue('register_link');
        
        $jsst_html = '
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
								if ($jsst_loginval == 3){
                                    $jsst_hreflink = wp_login_url();
                                }
		                        else if($jsst_loginval == 2 && $jsst_loginlink != ""){
		                            $jsst_html .= '<a class="js-ticket-login-btn" href="'.esc_url($jsst_loginlink).'" title="Login">' . esc_html(__('Login', 'js-support-ticket')) . '</a>';
		                        }else{
		                            $jsst_html .= '<a class="js-ticket-login-btn" href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'login'))).'" title="Login">' . esc_html(__('Login', 'js-support-ticket')) . '</a>';
		                        }
		                        $jsst_is_enable = get_option('users_can_register');/*check to make sure user registration is enabled*/
	                            if ($jsst_is_enable) {
	                            	if($jsst_registerval == 3){
		                        	    $jsst_html .= '<a class="js-ticket-register-btn" href="'.esc_url(wp_registration_url()).'" title="' . esc_html(__('Register', 'js-support-ticket')) . '">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
		                        	}else if($jsst_registerval == 2 && $jsst_registerlink != ""){
		                        	    $jsst_html .= '<a class="js-ticket-register-btn" href="'.esc_url($jsst_registerlink).'" title="' . esc_html(__('Register', 'js-support-ticket')) . '">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
		                        	}else{
		                        		$jsst_html .= '<a class="js-ticket-register-btn" href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'userregister'))).'" title="' . esc_html(__('Register', 'js-support-ticket')) . '">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
		                        	}
		                        }
	                    	}

                    $jsst_html .= '</span>
					</div>
				</div>
		';
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getNotStaffMember() {
        $jsst_html = '
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
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getYouAreLoggedIn() {
        $jsst_html = '
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
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getStaffMemberDisable() {
        $jsst_html = '
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
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getSystemOffline() {
        $jsst_html = '
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
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getUserGuest($jsst_redirect_url = '') {
        $jsst_loginval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_login_link');
        $jsst_loginlink = JSSTincluder::getJSModel('configuration')->getConfigValue('login_link');
        $jsst_registerval = JSSTincluder::getJSModel('configuration')->getConfigValue('set_register_link');
        $jsst_registerlink = JSSTincluder::getJSModel('configuration')->getConfigValue('register_link');
        $jsst_html = '
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
							if ($jsst_loginval == 3){
                                $jsst_hreflink = wp_login_url();
                            }
	                        else if($jsst_loginval == 2 && $jsst_loginlink != ""){
	                            $jsst_html .= '<a class="js-ticket-login-btn" href="'.esc_url($jsst_loginlink).'" title="Login">' . esc_html(__('Login', 'js-support-ticket')) . '</a>';
	                        }else{
	                            $jsst_html .= '<a class="js-ticket-login-btn" href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'login', 'js_redirecturl'=>$jsst_redirect_url))).'" title="Login">' . esc_html(__('Login', 'js-support-ticket')) . '</a>';
	                        }
	                        $jsst_is_enable = get_option('users_can_register');/*check to make sure user registration is enabled*/
                            if ($jsst_is_enable) {
                            	if($jsst_registerval == 3){
	                        	    $jsst_html .= '<a class="js-ticket-register-btn" href="'.esc_url(wp_registration_url()).'" title="' . esc_html(__('Register', 'js-support-ticket')) . '">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
	                        	}else if($jsst_registerval == 2 && $jsst_registerlink != ""){
	                        	    $jsst_html .= '<a class="js-ticket-register-btn" href="'.esc_url($jsst_registerlink).'" title="' . esc_html(__('Register', 'js-support-ticket')) . '">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
	                        	}else{
	                        		$jsst_html .= '<a class="js-ticket-register-btn" href="'.esc_url(jssupportticket::makeUrl(array('jstmod'=>'jssupportticket', 'jstlay'=>'userregister', 'js_redirecturl'=>$jsst_redirect_url))).'" title="' . esc_html(__('Register', 'js-support-ticket')) . '">' . esc_html(__('Register', 'js-support-ticket')) . '</a>';
	                        	}
	                        }

                    $jsst_html .= '</span>
                    </div>

				</div>
        ';
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getYouAreNotAllowedToViewThisPage() {
        $jsst_html = '
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
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getRegistrationDisabled() {
        $jsst_html = '
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
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    static function getFeedbackMessages($jsst_msg_type) {
    	if($jsst_msg_type == 2){
    		$jsst_img_var = '3.png';
    		$jsst_text_var_1 = esc_html(__('Sorry!', 'js-support-ticket'));
    		$jsst_text_var_2 = esc_html(__('You have already given the feedback for this ticket.', 'js-support-ticket'));
    	}elseif($jsst_msg_type == 3){
    		$jsst_img_var = 'no-record-icon.png';
    		$jsst_text_var_1 = esc_html(__('Sorry!', 'js-support-ticket'));
    		$jsst_text_var_2 = esc_html(__('Ticket not found...!', 'js-support-ticket'));
    	}else{
    		$jsst_img_var = 'not-permission-icondd.png';
    		$jsst_text_var_1 = esc_html(__('Sorry!', 'js-support-ticket'));
    		$jsst_text_var_2 = esc_html(__('User is not allowed to view this page', 'js-support-ticket'));
    	}
    	if($jsst_msg_type == 4){
			$jsst_html = '
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
	        $jsst_html = '
					<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="message image" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/'.esc_attr($jsst_img_var).'"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . esc_html($jsst_text_var_1) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' .wp_kses($jsst_text_var_2, JSST_ALLOWED_TAGS). '
						</span>
					</div>
				</div>
			';
		}
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
	}

}

?>
