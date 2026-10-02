<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTlayout {

    /* A screen can say what is empty and what to do about it; with no
       arguments it keeps the old wording, so every other caller is unchanged. */
    static function getNoRecordFound($jsst_title = '', $jsst_text = '', $jsst_linkurl = '', $jsst_linktext = '') {
        $jsst_main = ($jsst_title !== '') ? esc_html($jsst_title) : esc_html(__('Sorry', 'js-support-ticket')) . '!';
        $jsst_block = ($jsst_text !== '') ? esc_html($jsst_text) : esc_html(__('No Record Found', 'js-support-ticket')) . '...!';
        if ($jsst_linkurl !== '' && $jsst_linktext !== '') {
            $jsst_block .= ' <a class="js-ticket-messages-link" href="' . esc_url($jsst_linkurl) . '">' . esc_html($jsst_linktext) . '</a>';
        }
        $jsst_html = '
				<div class="js-ticket-error-message-wrapper">
					<div class="js-ticket-message-image-wrapper">
						<img class="js-ticket-message-image" alt="" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/error/no-record-icon.png"/>
					</div>
					<div class="js-ticket-messages-data-wrapper">
						<span class="js-ticket-messages-main-text">
					    	' . $jsst_main . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . $jsst_block . '
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
					    	' . esc_html(__('Sorry', 'js-support-ticket')) . '
						</span>
						<span class="js-ticket-messages-block_text">
					    	' . esc_html(__('No Record Found', 'js-support-ticket')) . '
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
					    	' . esc_html(__('Access Denied.', 'js-support-ticket')) . '
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
					    	' . esc_html(__('Access Denied.', 'js-support-ticket')) . '
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
					    	' . esc_html(__('Sorry', 'js-support-ticket')) . '
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
					    	' . esc_html(__('Access Denied.', 'js-support-ticket')) . '
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
					    	' . esc_html(__('Sorry', 'js-support-ticket')) . '
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
					    	' . esc_html(__('Sorry', 'js-support-ticket')) . '
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
    		$jsst_text_var_1 = esc_html(__('Sorry', 'js-support-ticket'));
    		$jsst_text_var_2 = esc_html(__('You have already given the feedback for this ticket.', 'js-support-ticket'));
    	}elseif($jsst_msg_type == 3){
    		$jsst_img_var = 'no-record-icon.png';
    		$jsst_text_var_1 = esc_html(__('Sorry', 'js-support-ticket'));
    		$jsst_text_var_2 = esc_html(__('Ticket not found.', 'js-support-ticket'));
    	}else{
    		$jsst_img_var = 'not-permission-icondd.png';
    		$jsst_text_var_1 = esc_html(__('Sorry', 'js-support-ticket'));
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

    /**
     * The admin page header every screen shares: breadcrumb and utility links,
     * then the title with its optional count and the page's own actions.
     *
     * $jsst_args:
     *   title   string  the page heading, also the last breadcrumb
     *   crumbs  array   array('text' => .., 'url' => ..) between Dashboard and the title
     *   count   int     shown beside the title when greater than zero
     *   sub     string  a short line after the title (e.g. the record being viewed)
     *   actions array   array('text' => .., 'url' => .., 'style' => 'primary'|'ghost',
     *                   'icon' => 'plus'|'', 'target' => .., 'attrs' => array())
     */
    /**
     * Features released as Beta in 5.0.0, and the label that says so.
     *
     * Decided 1 October 2026: Live Chat, AI Agent, the Gmail / Microsoft 365
     * mailbox sign-in, the REST API & webhooks, and the Security screen. They
     * are new rather than rebuilt, and each either answers customers on its own
     * or leans on a service outside the plugin. Shown to administrators only -
     * never in the chat widget, the portal or an email. Take a feature off this
     * list, and out of the side menu, when it leaves Beta.
     *
     * Screens are matched on `page` and, where one page also serves stable
     * screens, `jstlay`. The API pages are matched on layout alone, because
     * which plugin serves them (the Integrations bundle or Pro) changes `page`.
     */
    static function betaScreens() {
        return apply_filters('jsst_beta_screens', array(
            array('page' => 'livechat'),
            array('page' => 'aiagent'),
            array('page' => 'zywrap'),
            array('jstlay' => 'api'),
            array('jstlay' => 'webhooks'),
            array('page' => 'jssupportticket', 'jstlay' => 'security'),
        ));
    }

    /** Is the admin screen being drawn one of them? */
    static function isBetaScreen() {
        $jsst_page   = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $jsst_layout = isset($_GET['jstlay']) ? sanitize_key(wp_unslash($_GET['jstlay'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        foreach (self::betaScreens() as $jsst_rule) {
            if (isset($jsst_rule['page']) && $jsst_rule['page'] !== $jsst_page) {
                continue;
            }
            if (isset($jsst_rule['jstlay']) && $jsst_rule['jstlay'] !== $jsst_layout) {
                continue;
            }
            return true;
        }
        return false;
    }

    /** The word, for places that cannot hold markup - a select option, a title attribute. */
    static function betaLabel() {
        return __('Beta', 'js-support-ticket');
    }

    /** The badge. */
    static function betaBadge() {
        return '<span class="jsst-beta-badge" title="' . esc_attr__('New in 5.0.0 and still being refined. It works and is supported; tell us what you find.', 'js-support-ticket') . '">' . esc_html(self::betaLabel()) . '</span>';
    }

    static function adminPageHeader($jsst_args) {
        $jsst_args = wp_parse_args($jsst_args, array(
            'title'   => '',
            'crumbs'  => array(),
            'count'   => 0,
            'sub'     => '',
            'actions' => array(),
            'beta'    => null, // null: decided by betaScreens()
        ));
        if (null === $jsst_args['beta']) {
            $jsst_args['beta'] = self::isBetaScreen();
        }
        $jsst_crumbs = array_merge(
            array(array('text' => __('Dashboard', 'js-support-ticket'), 'url' => admin_url('admin.php?page=jssupportticket'))),
            (array) $jsst_args['crumbs'],
            array(array('text' => $jsst_args['title'], 'url' => ''))
        );
        ?>
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <?php foreach ($jsst_crumbs as $jsst_crumb) { ?>
                            <li><?php if (!empty($jsst_crumb['url'])) { ?><a href="<?php echo esc_url($jsst_crumb['url']); ?>"><?php echo esc_html($jsst_crumb['text']); ?></a><?php } else { echo esc_html($jsst_crumb['text']); } ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <a class="jsstadmin-top-btn" title="<?php echo esc_attr(__('Configuration', 'js-support-ticket')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=configuration')); ?>">
                    <svg class="jsst-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span class="screen-reader-text"><?php echo esc_html(__('Configuration', 'js-support-ticket')); ?></span>
                </a>
                <a class="jsstadmin-top-btn" title="<?php echo esc_attr(__('Help', 'js-support-ticket')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=help')); ?>">
                    <svg class="jsst-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9.5"/><path d="M9.2 9.2a2.9 2.9 0 0 1 5.64.97c0 1.93-2.9 2.9-2.9 2.9"/><line x1="12" y1="17.2" x2="12.01" y2="17.2"/></svg>
                    <span class="screen-reader-text"><?php echo esc_html(__('Help', 'js-support-ticket')); ?></span>
                </a>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__('Version', 'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php
                echo esc_html($jsst_args['title']);
                if ($jsst_args['beta']) { echo ' ' . self::betaBadge(); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built escaped in betaBadge()
                if ((int) $jsst_args['count'] > 0) { ?><span class="jsst-count"><?php echo esc_html(number_format_i18n((int) $jsst_args['count'])); ?></span><?php }
                if ($jsst_args['sub'] !== '') { ?><span class="jsstadmin-head-sub-text"><?php echo esc_html($jsst_args['sub']); ?></span><?php }
            ?></h1>
            <?php foreach ((array) $jsst_args['actions'] as $jsst_action) {
                $jsst_action = wp_parse_args($jsst_action, array('text' => '', 'url' => '', 'style' => 'primary', 'icon' => '', 'target' => '', 'attrs' => array()));
                $jsst_class = 'jsst-btn';
                if ($jsst_action['style'] === 'primary') {
                    $jsst_class .= ' jsst-btn-primary';
                } elseif ($jsst_action['style'] === 'danger') {
                    $jsst_class .= ' jsst-btn-danger';
                }
                ?>
                <a class="<?php echo esc_attr($jsst_class); ?>" href="<?php echo esc_url($jsst_action['url']); ?>"<?php
                    if ($jsst_action['target'] !== '') { echo ' target="' . esc_attr($jsst_action['target']) . '" rel="noopener"'; }
                    foreach ((array) $jsst_action['attrs'] as $jsst_attr => $jsst_value) { echo ' ' . esc_attr($jsst_attr) . '="' . esc_attr($jsst_value) . '"'; }
                ?>><?php if ($jsst_action['icon'] === 'plus') { ?><svg class="jsst-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><?php } ?><?php echo esc_html($jsst_action['text']); ?></a>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * An empty list, said in words, with the way to fill it.
     */
    /**
     * $jsst_variant takes 'jsst-empty-tight' for an empty state that shares a
     * card with something else - a form, a second section - where the
     * full-page padding is too much. Trailing and optional, so every existing
     * call is unaffected.
     */
    static function adminEmpty($jsst_title, $jsst_text = '', $jsst_actiontext = '', $jsst_actionurl = '', $jsst_variant = '') {
        ?>
        <div class="jsst-empty <?php echo esc_attr($jsst_variant); ?>">
            <p class="jsst-empty-title"><?php echo esc_html($jsst_title); ?></p>
            <?php if ($jsst_text !== '') { ?><p class="jsst-empty-text"><?php echo esc_html($jsst_text); ?></p><?php } ?>
            <?php if ($jsst_actiontext !== '' && $jsst_actionurl !== '') { ?><p class="jsst-empty-act"><a class="jsst-btn jsst-btn-primary" href="<?php echo esc_url($jsst_actionurl); ?>"><?php echo esc_html($jsst_actiontext); ?></a></p><?php } ?>
        </div>
        <?php
    }

    /**
     * The department and permission checkboxes an agent or a role is given,
     * one group per section with a select-all in its heading.
     *
     * $jsst_args:
     *   data     array  the model's data: department_role, permission_by_task, role, staffid
     *   depname  string posted name for departments (userdepdata / roledepdata)
     *   pername  string posted name for permissions (staffperdata / roleperdata)
     *   mode     string 'staff' (an agent), 'role' (a role being edited) or 'view' (read only)
     *   preset   array|null permission ids a role starts from, when a preset was chosen
     *
     * The ids and classes are the ones selectdeseletsection() works on.
     */
    static function adminPermissionGrid($jsst_args) {
        $jsst_args = wp_parse_args($jsst_args, array('data' => array(), 'depname' => '', 'pername' => '', 'mode' => 'role', 'preset' => null));
        $jsst_data = $jsst_args['data'];
        $jsst_mode = $jsst_args['mode'];
        $jsst_view = ($jsst_mode === 'view');
        $jsst_roleset = isset($jsst_data['role']->id);
        $jsst_roleid = $jsst_roleset ? $jsst_data['role']->id : 0;
        $jsst_sections = array(
            'ticket_section'         => array(__('Ticket Section', 'js-support-ticket'), 't_s_allrolepermision', 't_s_rolepermission', 'ticke'),
            'staff_section'          => array(__('Agent Section', 'js-support-ticket'), 's_s_allrolepermision', 's_s_rolepermission', 'agent'),
            'kb_section'             => array(__('Knowledge Base Section', 'js-support-ticket'), 'kb_s_allrolepermision', 'kb_s_rolepermission', 'kb'),
            'faq_section'            => array(__('FAQ Section', 'js-support-ticket'), 'f_s_allrolepermision', 'f_s_rolepermission', 'faqs'),
            'download_section'       => array(__('Download Section', 'js-support-ticket'), 'd_s_allrolepermision', 'd_s_rolepermission', 'staffdownloads'),
            'announcement_section'   => array(__('Announcement Section', 'js-support-ticket'), 'a_s_allrolepermision', 'a_s_rolepermission', 'announcement'),
            'mail_section'           => array(__('Mail Section', 'js-support-ticket'), 'm_s_allrolepermision', 'm_s_rolepermission', 'mail'),
            'helptopic_section'      => array(__('Help Topic Section', 'js-support-ticket'), 'h_t_allrolepermision', 'h_t_rolepermission', 'helptopic'),
            'cannedresponse_section' => array(__('Canned Response Section', 'js-support-ticket'), 'c_r_allrolepermision', 'c_r_rolepermission', 'cannedresponse'),
            'aireply_section'        => array(__('AI Reply Section', 'js-support-ticket'), 'a_r_allrolepermision', 'a_r_rolepermission', 'aireply'),
        );
        /* Whether a box starts ticked. A role being edited, or an agent on a
           role, shows what is stored; a new role starts with everything; an
           agent whose permissions were all removed starts with nothing. */
        $jsst_ticked = function ($jsst_item, $jsst_linkfield, $jsst_ispermission) use ($jsst_mode, $jsst_roleset, $jsst_roleid, $jsst_data, $jsst_args) {
            if ($jsst_mode === 'role' && $jsst_ispermission && $jsst_args['preset'] !== null) {
                return in_array((int) $jsst_item->id, $jsst_args['preset'], true);
            }
            $jsst_stored = ($jsst_mode === 'staff' || $jsst_mode === 'view') ? (bool) $jsst_roleid : $jsst_roleset;
            if ($jsst_stored) {
                return isset($jsst_item->{$jsst_linkfield}) && ($jsst_item->{$jsst_linkfield} == $jsst_item->id);
            }
            if ($jsst_mode === 'staff') {
                return !isset($jsst_data['staffid']);
            }
            return true;
        };
        $jsst_groups = array(array(__('Department Section', 'js-support-ticket'), 'rad_alldepartmentaccess', 'rad_departmentaccess', '', 'department_role'));
        foreach (array_keys(isset($jsst_data['permission_by_task']) ? (array) $jsst_data['permission_by_task'] : array()) as $jsst_key) {
            /* A section this screen has no name for is still drawn, so a
               permission added by an add-on is never hidden. */
            $jsst_def = isset($jsst_sections[$jsst_key]) ? $jsst_sections[$jsst_key]
                : array(ucwords(str_replace('_', ' ', $jsst_key)), $jsst_key . '_all', $jsst_key . '_perm', $jsst_key);
            $jsst_def[] = $jsst_key;
            $jsst_groups[] = $jsst_def;
        }
        /* One wrapper so the sections can be styled as a set. Eleven of them
           stack on Add Agent Role, and a permission form is the one screen
           where the headings are the only way to find anything. */
        /* A role starts with every box ticked, so the work is unticking - and
           with ninety boxes there was nothing telling you what you had built.
           Each heading now counts its own section, and a part-ticked
           select-all shows indeterminate rather than claiming "all". */
        static $jsst_countjs = false;
        if (!$jsst_countjs) {
            $jsst_countjs = true;
            wp_add_inline_script('js-support-ticket-main-js', "
    function jsstPermTally() {
        jQuery('.jsst-permcount').each(function () {
            var badge = jQuery(this),
                boxes = jQuery('.' + badge.data('permcount')),
                on    = boxes.filter(':checked').length,
                all   = boxes.length,
                tpl   = String(badge.data('permtpl') || '%1\$s of %2\$s');
            badge.text(tpl.replace('%1\$s', on).replace('%2\$s', all));
            badge.toggleClass('jsst-permcount-all', all > 0 && on === all);
            var master = document.getElementById(badge.data('permall'));
            if (master) {
                master.indeterminate = (on > 0 && on < all);
                master.checked = (all > 0 && on === all);
            }
        });
    }
    jQuery(document).ready(function () {
        jsstPermTally();
        jQuery(document).on('change', '.jsst-permgrid input[type=\"checkbox\"]', jsstPermTally);
    });
");
        }
        ?><div class="jsst-permgrid"><?php
        foreach ($jsst_groups as $jsst_gi => $jsst_group) {
            list($jsst_text, $jsst_allid, $jsst_class, $jsst_prefix, $jsst_key) = $jsst_group;
            $jsst_isdep = ($jsst_gi === 0);
            $jsst_items = $jsst_isdep
                ? (isset($jsst_data['department_role']) ? (array) $jsst_data['department_role'] : array())
                : (array) $jsst_data['permission_by_task'][$jsst_key];
            ?>
            <fieldset class="jsst-fieldset">
                <legend class="jsst-fieldset-legend jsst-legend-row">
                    <span><?php echo esc_html($jsst_text); ?></span>
                    <span class="jsst-permcount"
                          data-permcount="<?php echo esc_attr($jsst_class); ?>"
                          data-permall="<?php echo esc_attr($jsst_allid); ?>"
                          data-permtpl="<?php echo esc_attr(/* translators: 1: number of permissions ticked, 2: total number of permissions. */ __('%1$s of %2$s', 'js-support-ticket')); ?>"></span>
                    <?php if (!$jsst_view) { ?>
                        <label class="jsst-check" for="<?php echo esc_attr($jsst_allid); ?>">
                            <input type="checkbox" id="<?php echo esc_attr($jsst_allid); ?>" <?php checked(!$jsst_roleset); ?> onclick="selectdeseletsection('<?php echo esc_js($jsst_allid); ?>', '<?php echo esc_js($jsst_class); ?>');" />
                            <span><?php echo esc_html(__('Select / Deselect All', 'js-support-ticket')); ?></span>
                        </label>
                    <?php } ?>
                </legend>
                <div class="jsst-checks jsst-checks-grid">
                    <?php foreach ($jsst_items as $jsst_item) {
                        if ($jsst_isdep) {
                            $jsst_boxid = 'roledepdata_' . $jsst_item->name;
                            $jsst_name = $jsst_args['depname'] . '[' . $jsst_item->name . ']';
                            $jsst_label = $jsst_item->name;
                            $jsst_on = $jsst_ticked($jsst_item, 'roledepartmentid', false);
                        } else {
                            $jsst_boxid = $jsst_prefix . '_' . $jsst_item->permission;
                            $jsst_name = $jsst_args['pername'] . '[' . $jsst_item->permission . ']';
                            $jsst_label = $jsst_item->permission;
                            $jsst_on = $jsst_ticked($jsst_item, 'rolepermissionid', true);
                        } ?>
                        <label class="jsst-check" for="<?php echo esc_attr($jsst_boxid); ?>">
                            <input type="checkbox" id="<?php echo esc_attr($jsst_boxid); ?>" class="<?php echo esc_attr($jsst_class); ?>" name="<?php echo esc_attr($jsst_name); ?>" value="<?php echo esc_attr($jsst_item->id); ?>" <?php checked($jsst_on); ?> <?php disabled($jsst_view); ?> />
                            <span><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_label)); ?></span>
                        </label>
                    <?php } ?>
                </div>
            </fieldset>
            <?php
        }
        ?></div><?php
    }

    /**
     * The pager under a list. $jsst_links is the markup the pagination class built.
     */
    /**
     * The reasoning behind a setting, folded under a "Why?" toggle.
     *
     * Screens keep one short line in view and put the longer explanation here,
     * so it is still one click away, still printable and still searchable once
     * open. Native <details>: no script, keyboard-operable, announced as
     * expandable. $jsst_text is plain text and is escaped here.
     */
    static function why($jsst_text, $jsst_label = '') {
        if ($jsst_text === '') {
            return;
        }
        if ($jsst_label === '') {
            $jsst_label = __('Why?', 'js-support-ticket');
        }
        echo '<details class="jsst-why"><summary>' . esc_html($jsst_label) . '</summary><p>' . esc_html($jsst_text) . '</p></details>';
    }

    static function adminPager($jsst_links) {
        if (empty($jsst_links)) {
            return;
        }
        echo '<div class="jsst-pagefoot"><div class="tablenav"><div class="tablenav-pages">' . wp_kses_post($jsst_links) . '</div></div></div>';
    }

    /**
     * The "pick a user" dialog, in one place. (Roadmap 6.5-UI-07)
     *
     * It was copied into twelve templates, drifting in each: three of them had
     * lost the reset button, the heading was "Select User" on one screen and
     * "Search User" on another, and the search fields were laid out four
     * different ways. Every screen that asks the same question now asks it
     * with the same dialog.
     *
     * Everything the page scripts select on is unchanged - `#userpopup`,
     * `#userpopupblack`, `#userpopup-records`, `#userpopupsearch`, the three
     * field ids and `.userpopup-close` - because each screen still wires its
     * own open, search and pick handlers to them. Only the presentation is
     * the shared design system's.
     */
    /**
     * An empty dialog for a screen that builds its own contents. Slug and
     * Field Ordering both open `#userpopup` and immediately `.html()` a form
     * into it, so they want the shell and the backdrop, not a user list.
     */
    static function adminPopupShell() {
        ?>
        <div id="userpopupblack" class="jsst-popup-background" style="display:none;"></div>
        <div id="userpopup" class="jsst-popup-wrapper" style="display:none;"></div>
        <?php
    }

    static function adminUserPicker($jsst_args = array()) {
        $jsst_title = !empty($jsst_args['title']) ? $jsst_args['title'] : __('Select a user', 'js-support-ticket');
        $jsst_hint  = !empty($jsst_args['hint']) ? $jsst_args['hint'] : __('Search by username, name or email address to find the person you want.', 'js-support-ticket');
        ?>
        <div id="userpopupblack" class="jsst-popup-background" style="display:none;"></div>
        <div id="userpopup" class="jsst-popup-wrapper jsst-userpick" style="display:none;">
            <div class="jsst-popup-header">
                <span class="popup-header-text"><?php echo esc_html($jsst_title); ?></span>
                <button type="button" class="jsst-popup-close userpopup-close" aria-label="<?php echo esc_attr__('Close', 'js-support-ticket'); ?>">&times;</button>
            </div>
            <div class="jsst-userpick-body">
                <?php /* One box that searches name, username and email together.
                         Three labelled boxes took half the dialog and made
                         somebody decide which of the three they knew. The three
                         old fields stay, hidden, because the page scripts still
                         read them by id; the search itself is run by the script
                         below, which sends the one box as `q`. */ ?>
                <form id="userpopupsearch" class="jsst-userpick-search" role="search">
                    <div class="jsst-userpick-bar">
                        <label class="screen-reader-text" for="userpick-q"><?php echo esc_html__('Search users', 'js-support-ticket'); ?></label>
                        <input type="search" id="userpick-q" class="jsst-userpick-q" autocomplete="off"
                               placeholder="<?php echo esc_attr__('Search name, username or email', 'js-support-ticket'); ?>" />
                        <button type="submit" class="jsst-btn jsst-btn-primary userpopup-search-btn"><?php echo esc_html__('Search', 'js-support-ticket'); ?></button>
                        <button type="submit" class="jsst-btn userpopup-reset-btn" data-jsst-reset="1"><?php echo esc_html__('Reset', 'js-support-ticket'); ?></button>
                    </div>
                    <input type="hidden" name="username" id="username" value="" />
                    <input type="hidden" name="emailaddress" id="emailaddress" value="" />
                    <input type="hidden" name="name" id="name" value="" />
                </form>
                <div id="userpopup-records-wrp" class="jsst-userpick-records">
                    <div id="userpopup-records">
                        <p class="jsst-hint"><?php echo esc_html($jsst_hint); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        /* Runs the search itself and stops the page's own submit handler.
           Each page read the boxes with jQuery("input#name"), and the ticket
           form has an input#name of its own, so the page could search on the
           customer's name instead of what was typed here. Registered here,
           before the page's document-ready handlers, so it runs first. The
           page's setUserLink() still wires the picked row, and its
           updateuserlist() still pages the unfiltered list. */
        $jsst_pickjs = "
(function () {
    var form = document.getElementById('userpopupsearch');
    if (!form || !window.jQuery) { return; }
    var q = document.getElementById('userpick-q');
    var reset = false;
    form.addEventListener('click', function (e) {
        reset = !!(e.target && e.target.getAttribute && e.target.getAttribute('data-jsst-reset'));
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (reset) { q.value = ''; reset = false; }
        ['username', 'emailaddress', 'name'].forEach(function (f) { var el = form.querySelector('#' + f); if (el) { el.value = ''; } });
        jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'getusersearchajax', q: q.value, '_wpnonce': " . wp_json_encode(wp_create_nonce('get-usersearch-ajax')) . "}, function (data) {
            if (data) {
                jQuery('div#userpopup-records').html(data);
                if (typeof window.setUserLink === 'function') { window.setUserLink(); }
            }
        });
    });
    /* The whole row picks the user, not only the address in it. */
    jQuery(document).on('click', '.jsst-userpick tbody tr', function (e) {
        if (jQuery(e.target).closest('a').length) { return; }
        jQuery(this).find('a.js-userpopup-link').first().trigger('click');
    });
    /* Focus the box whenever the dialog is opened. */
    jQuery(document).on('click', 'a#userpopup', function () { setTimeout(function () { q.focus(); }, 350); });
})();
";
        wp_add_inline_script('js-support-ticket-main-js', $jsst_pickjs);
    }


    /**
     * Pick which form a new ticket is raised on (the multiform addon).
     *
     * Rendered once per page even though both the admin header and the side
     * menu ask for it: each used to print its own copy, so the page carried
     * two `#multiformpopup` divs with the same id, and the header's copy had
     * an empty close box - no image, nothing to click. One dialog, one close
     * button, both callers satisfied.
     *
     * The page scripts in header.php and jsstadminsidemenu.php still drive
     * this, so `#multiformpopup`, `#multiformpopupblack`, `#records`,
     * `#records-inner`, `.multiformpopup-header-close-img` and
     * `#jstran_loading` all have to keep their names.
     */
    static function adminFormPicker($jsst_args = array()) {
        static $jsst_done = false;
        if ($jsst_done) {
            return;
        }
        $jsst_done = true;
        $jsst_title = !empty($jsst_args['title']) ? $jsst_args['title'] : __('Select Form', 'js-support-ticket');
        ?>
        <div id="multiformpopupblack" class="jsst-popup-background" style="display:none;"></div>
        <div id="multiformpopup" class="jsst-popup-wrapper jsst-formpick" style="display:none;">
            <div class="jsst-popup-header">
                <span class="popup-header-text"><?php echo esc_html($jsst_title); ?></span>
                <?php /* The old close was a bare <img> in a div; the handler binds to
                         the div's class, so the class stays and the button is real. */ ?>
                <button type="button" class="jsst-popup-close multiformpopup-header-close-img" aria-label="<?php echo esc_attr__('Close', 'js-support-ticket'); ?>">&times;</button>
            </div>
            <div class="jsst-formpick-body">
                <div id="records">
                    <div id="records-inner">
                        <p class="jsst-hint"><?php echo esc_html__('No Record Found', 'js-support-ticket'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div id="jstran_loading" style="display:none;">
            <img alt="<?php echo esc_attr__('spinning wheel', 'js-support-ticket'); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
        </div>
        <?php
    }
}

?>
