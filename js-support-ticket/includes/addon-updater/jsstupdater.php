<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
/* Update for custom plugins by joomsky */
class JS_SUPPORTTICKETUpdater {

	private $jsst_api_key = '';
	private $jsst_addon_update_data = array();
	private $jsst_addon_update_data_errors = array();
	public $jsst_addon_installed_array = '';// it is public static bcz it is being used in extended class

	public $jsst_addon_installed_version_data = '';// it is public static bcz it is being used in extended class

	public function __construct() {
		$this->jsUpdateIntilized();

		$jsst_transaction_key_array = array();
		$jsst_addon_installed_array = array();
		foreach (jssupportticket::$_active_addons AS $jsst_addon) {
			$jsst_addon_installed_array[] = 'js-support-ticket-'.$jsst_addon;
			$jsst_option_name = 'transaction_key_for_js-support-ticket-'.$jsst_addon;
			$jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);
			if(!in_array($jsst_transaction_key, $jsst_transaction_key_array)){
				$jsst_transaction_key_array[] = $jsst_transaction_key;
			}
		}
		$this->jsst_addon_installed_array = $jsst_addon_installed_array;
		$this->jsst_api_key = wp_json_encode($jsst_transaction_key_array);
	}

	// class constructor triggers this function. sets up intail hooks and filters to be used.
	public function jsUpdateIntilized(  ) {
		add_action( 'admin_init', array( $this, 'jsAdminIntilization' ) );
		include_once( 'class-js-server-calls.php' );
	}

	// admin init hook triggers this fuction. sets up admin specific hooks and filter
	public function jsAdminIntilization() {


		add_filter( 'plugins_api', array( $this, 'jsPluginsAPI' ), 10, 3 );

		if ( current_user_can( 'update_plugins' ) ) {
			$this->jsCheckTriggers();
			add_action( 'admin_notices', array( $this, 'jsCheckUpdateNotice' ) );
			add_action( 'after_plugin_row', array( $this, 'jsKeyInput' ) );
		}
	}

	public function jsKeyInput( $jsst_file ) {
		$jsst_file_array = jssupportticketphplib::JSST_explode('/', $jsst_file);
		$jsst_addon_slug = $jsst_file_array[0];
		if(strstr($jsst_addon_slug, 'js-support-ticket-')){
			$jsst_addon_name = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_addon_slug);
			if(isset($this->jsst_addon_update_data[$jsst_file]) || !in_array($jsst_addon_name, jssupportticket::$_active_addons)){ // Only checking which addon have update version
				$jsst_option_name = 'transaction_key_for_js-support-ticket-'.$jsst_addon_name;
				$jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);
				$jsst_verify_results = JSSTincluder::getJSModel('premiumplugin')->activate( array(
		            'token'    => $jsst_transaction_key,
		            'plugin_slug'    => $jsst_addon_name
		        ) );
		        if(isset($jsst_verify_results['verfication_status']) && $jsst_verify_results['verfication_status'] == 0){
		        	$jsst_updateaddon_slug = jssupportticketphplib::JSST_str_replace("-", " ", $jsst_addon_slug);
		        	$jsst_message = jssupportticketphplib::JSST_strtoupper( jssupportticketphplib::JSST_substr( $jsst_updateaddon_slug, 0, 2 ) ).substr(  jssupportticketphplib::JSST_ucwords($jsst_updateaddon_slug), 2 ) .' authentication failed. Please insert valid key for authentication.';
		        	if(isset($this->jsst_addon_update_data[$jsst_file])){
		        		$jsst_message = 'There is new version of '. jssupportticketphplib::JSST_strtoupper( jssupportticketphplib::JSST_substr( $jsst_updateaddon_slug, 0, 2 ) ).substr(  jssupportticketphplib::JSST_ucwords($jsst_updateaddon_slug), 2 ) .' avaible. Please insert valid activation key for updation.';
		        		remove_action('after_plugin_row_'.$jsst_file,'wp_plugin_update_row');
					}
		        	include( 'views/html-key-input.php' );
		        	echo wp_kses('
					<tr>
						<td class="plugin-update plugin-update colspanchange" colspan="3">
							<div class="update-message notice inline notice-error notice-alt"><p>'. esc_html($jsst_message) .'</p></div>
						</td>
					</tr>', JSST_ALLOWED_TAGS);
		        }
			}
		}
	}

	public function jsCheckVersionUpdate( $jsst_update_data ) {
		if ( empty( $jsst_update_data->checked ) ) {
			return $jsst_update_data;
		}
		$jsst_response_version_data = get_transient('jsst_addon_update_temp_data');
		$jsst_response_version_data_cdn = get_transient('jsst_addon_update_temp_data_cdn');

		if(isset($_SERVER) &&  $_SERVER['REQUEST_URI'] !=''){
            if(strstr( $_SERVER['REQUEST_URI'], 'plugins.php')) {
				$jsst_response_version_data = get_transient('jsst_addon_update_temp_data_plugins');
				$jsst_response_version_data_cdn = get_transient('jsst_addon_update_temp_data_plugins_cdn');
			 }
        }

		if($jsst_response_version_data_cdn === false){
			$jsst_cdnversiondata = $this->getPluginVersionDataFromCDN();
			set_transient('jsst_addon_update_temp_data_cdn', $jsst_cdnversiondata, HOUR_IN_SECONDS * 6);
			set_transient('jsst_addon_update_temp_data_plugins_cdn', $jsst_cdnversiondata, 15);
		}else{
			$jsst_cdnversiondata = $jsst_response_version_data_cdn;
		}
		$jsst_newversionfound = 0;
		if ( $jsst_cdnversiondata) {
			if(is_object($jsst_cdnversiondata) ){
				foreach ($jsst_update_data->checked AS $jsst_key => $jsst_value) {
					$jsst_c_key_array = jssupportticketphplib::JSST_explode('/', $jsst_key);
					$jsst_c_key = $jsst_c_key_array[0];
					$jsst_c_key = jssupportticketphplib::JSST_str_replace("-","",$jsst_c_key);
					$jsst_newversion = $this->getVersionFromLiveData($jsst_cdnversiondata, $jsst_c_key);
					if($jsst_newversion){
						if(version_compare( $jsst_newversion, $jsst_value, '>' )){
							$jsst_newversionfound = 1;
						}
					}
				}
			}
		}

		if($jsst_newversionfound == 1){
			if($jsst_response_version_data === false){
				$jsst_response = $this->getPluginVersionData();
				set_transient('jsst_addon_update_temp_data', $jsst_response, HOUR_IN_SECONDS * 6);
				set_transient('jsst_addon_update_temp_data_plugins', $jsst_response, 15);
			}else{
				$jsst_response = $jsst_response_version_data;
			}
			if ( $jsst_response) {
				if(is_object($jsst_response) ){
					if(isset($jsst_response->addon_response_type) && $jsst_response->addon_response_type == 'no_key'){
						foreach ($jsst_update_data->checked AS $jsst_key => $jsst_value) {
							$jsst_c_key_array = jssupportticketphplib::JSST_explode('/', $jsst_key);
							$jsst_c_key = $jsst_c_key_array[0];
							if(isset($jsst_response->addon_version_data->{$jsst_c_key})){
								if(version_compare( $jsst_response->addon_version_data->{$jsst_c_key}, $jsst_value, '>' )){
									$jsst_transient_val = get_transient('jsst_addon_hide_update_notice');
									if($jsst_transient_val === false){
										set_transient('jsst_addon_hide_update_notice', 1, DAY_IN_SECONDS );
									}
									$this->jsst_addon_update_data[$jsst_key] = $jsst_response->addon_version_data->{$jsst_c_key};
								}
							}
						}
					}else{// addon_response_type other than no_key
						foreach ($jsst_update_data->checked AS $jsst_key => $jsst_value) {
							$jsst_c_key_array = jssupportticketphplib::JSST_explode('/', $jsst_key);
							$jsst_c_key = $jsst_c_key_array[0];
							if(isset($jsst_response->jsst_addon_update_data) && !empty($jsst_response->jsst_addon_update_data) && isset( $jsst_response->jsst_addon_update_data->{$jsst_c_key})){
								if(version_compare( $jsst_response->jsst_addon_update_data->{$jsst_c_key}->new_version, $jsst_value, '>' )){
									$jsst_update_data->response[ $jsst_key ] = $jsst_response->jsst_addon_update_data->{$jsst_c_key};
									$this->jsst_addon_update_data[$jsst_key] = $jsst_response->jsst_addon_update_data->{$jsst_c_key};
								}
							}elseif(isset($jsst_response->addon_version_data->{$jsst_c_key})){
								if(version_compare( $jsst_response->addon_version_data->{$jsst_c_key}, $jsst_value, '>' )){
									$jsst_transient_val = get_transient('jsst_addon_hide_update_expired_key_notice');
									if($jsst_transient_val === false){
										set_transient('jsst_addon_hide_update_expired_key_notice', 1, DAY_IN_SECONDS );
									}
									$this->jsst_addon_update_data[$jsst_key] = $jsst_response->addon_version_data->{$jsst_c_key};
									$this->jsst_addon_update_data[$jsst_key] = $jsst_response->addon_version_data->{$jsst_c_key};
								}
							}else{ // set latest version from cdn data
								if ( $jsst_cdnversiondata) {
									if(is_object($jsst_cdnversiondata) ){
										$jsst_c_key_plain = jssupportticketphplib::JSST_str_replace("-","",$jsst_c_key);
										$jsst_newversion = $this->getVersionFromLiveData($jsst_cdnversiondata, $jsst_c_key_plain);
										if($jsst_newversion){
											if(version_compare( $jsst_newversion, $jsst_value, '>' )){

												$jsst_option_name = 'transaction_key_for_'.$jsst_c_key;
												$jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);
												$jsst_addon_json_array = array();
												$jsst_addon_json_array[] = jssupportticketphplib::JSST_str_replace('js-support-ticket-', '', $jsst_c_key);
												$jsst_url = 'https://jshelpdesk.com/setup/index.php?token='.$jsst_transaction_key.'&productcode='. wp_json_encode($jsst_addon_json_array).'&domain='. site_url();

												// prepping data for seamless update of allowed addons
												$jsst_plugin = new stdClass();
												$jsst_plugin->id = 'w.org/plugins/js-support-ticket';
												$jsst_addon_slug = $jsst_c_key;
												$jsst_plugin->name = $jsst_addon_slug;
												$jsst_plugin->plugin = $jsst_addon_slug.'/'.$jsst_addon_slug.'.php';
												$jsst_plugin->slug = $jsst_addon_slug;
												$jsst_plugin->version = '1.0.0';
												$jsst_addonwithoutslash = jssupportticketphplib::JSST_str_replace('-', '', $jsst_addon_slug);
												$jsst_plugin->new_version = $jsst_newversion; 
												$jsst_plugin->url = 'https://www.jshelpdesk.com/';
												$jsst_plugin->download_url = $jsst_url;
												$jsst_plugin->package = $jsst_url;
												$jsst_plugin->trunk = $jsst_url;
												
												$jsst_update_data->response[ $jsst_key ] = $jsst_plugin;
												$this->jsst_addon_update_data[$jsst_key] = $jsst_plugin;
											}
										}

									}
								}
							}
						}
					}
				}
			}
		}// new version found	
		if(isset($jsst_update_data->checked)){
			$this->jsst_addon_installed_version_data = $jsst_update_data->checked;
		}
		return $jsst_update_data;
	}

	public function jsPluginsAPI( $jsst_false, $jsst_action, $jsst_args ) {

		if (!isset( $jsst_args->slug )) {
			return false;
		}

		if(strstr($jsst_args->slug, 'js-support-ticket-')){
			$jsst_response = $this->jsGetPluginInfo($jsst_args->slug);
			if ($jsst_response) {
				$jsst_response->sections = json_decode(wp_json_encode($jsst_response->sections),true);
				$jsst_response->banners = json_decode(wp_json_encode($jsst_response->banners),true);
				$jsst_response->contributors = json_decode(wp_json_encode($jsst_response->contributors),true);
				return $jsst_response;
			}
		}else{
			return false;// to handle the case of plugins that need to check version data from wordpress repositry.
		}
	}

	public function jsGetPluginInfo($jsst_addon_slug) {

		$jsst_option_name = 'transaction_key_for_'.$jsst_addon_slug;
		$jsst_transaction_key = JSSTincluder::getJSModel('jssupportticket')->getAddonTransationKey($jsst_option_name);

		if(!$jsst_transaction_key){
			die('transient');
			return false;
		}

		$jsst_plugin_file_path = content_url().'/plugins/'.$jsst_addon_slug.'/'.$jsst_addon_slug.'.php';
		$jsst_plugin_data = get_plugin_data($jsst_plugin_file_path);

		$jsst_response = jsSupportTicketServerCalls::jsstPluginInformation( array(
			'plugin_slug'    => $jsst_addon_slug,
			'version'        => $jsst_plugin_data['Version'],
			'token'    => $jsst_transaction_key,
			'domain'          => site_url()
		) );
		if ( isset( $jsst_response->errors ) ) {
			$this->handle_errors( $jsst_response->errors );
		}

		// If everything is okay return the $jsst_response
		if ( isset( $jsst_response ) && is_object( $jsst_response ) && $jsst_response !== false ) {
			return $jsst_response;
		}

		return false;
	}

	// does changes according to admin triggers.
	private function jsCheckTriggers() {

		if ( isset($_POST['jsst_addon_array_for_token']) && ! empty( $_POST[ 'jsst_addon_array_for_token' ])){
			$jsst_transaction_key = '';
			$jsst_addon_name = '';
			foreach ($_POST['jsst_addon_array_for_token'] as $jsst_key => $jsst_value) {
				if(isset($_POST[$jsst_value.'_transaction_key']) && $_POST[$jsst_value.'_transaction_key'] != ''){
					$jsst_transaction_key = jssupportticket::JSST_sanitizeData($_POST[$jsst_value.'_transaction_key']); // JSST_sanitizeData() function uses wordpress santize functions
					$jsst_addon_name = $jsst_value;
					break;
				}
			}

			if($jsst_transaction_key != ''){
				$jsst_token = $this->jsstGetTokenFromTransactionKey( $jsst_transaction_key,$jsst_addon_name);
				if($jsst_token){
					foreach ($_POST['jsst_addon_array_for_token'] as $jsst_key => $jsst_value) {
						update_option('transaction_key_for_'.$jsst_value,$jsst_token);
					}
				}else{
					update_option( 'jsst-addon-key-error-message','Something went wrong');
				}
			}
		}else{
			foreach ($this->jsst_addon_installed_array as $jsst_key) {
				if ( ! empty( $_GET[ 'dismiss-jsst-addon-update-notice-'.$jsst_key] ) ) {
					set_transient('dismiss-jsst-addon-update-notice-'.$jsst_key, 1, DAY_IN_SECONDS );
				}
			}
		}
	}

	public function jsCheckUpdateNotice( ) {
		include_once( 'views/html-update-availble.php' );
		// if ( sizeof( $this->errors ) === 0 && ! get_option( $this->plugin_slug . '_hide_update_notice' ) ) {
		// }
	}

	public function getPluginVersionData() {
			$jsst_response = jsSupportTicketServerCalls::jsstPluginUpdateCheck($this->jsst_api_key);
			if ( isset( $jsst_response->errors ) ) {
				$this->jsHandleErrors( $jsst_response->errors );
			}

			// Set version variables
			if ( isset( $jsst_response ) && is_object( $jsst_response ) && $jsst_response !== false ) {
				return $jsst_response;
			}
		return false;
	}

	public function getPluginVersionDataFromCDN() {
			$jsst_response = jsSupportTicketServerCalls::jsstPluginUpdateCheckFromCDN();
			if ( isset( $jsst_response->errors ) ) {
				$this->jsHandleErrors( $jsst_response->errors );
			}

			// Set version variables
			if ( isset( $jsst_response ) && is_object( $jsst_response ) && $jsst_response !== false ) {
				return $jsst_response;
			}
		return false;
	}


	private function getVersionFromLiveData($jsst_data, $jsst_addon_name){
		foreach ($jsst_data as $jsst_key => $jsst_value) {
			if($jsst_key == $jsst_addon_name){
				return $jsst_value;
			}
		}
		return;
	}
	public function getPluginLatestVersionData() {
		$jsst_response = jsSupportTicketServerCalls::jsstGetLatestVersions();
		// Set version variables
		if ( isset( $jsst_response ) && is_array( $jsst_response ) && $jsst_response !== false ) {
			return $jsst_response;
		}
		return false;
	}

	public function jsstGetTokenFromTransactionKey($jsst_transaction_key,$jsst_addon_name) {
		$jsst_response = jsSupportTicketServerCalls::jsstGenerateToken($jsst_transaction_key,$jsst_addon_name);
		// Set version variables
		if (is_array($jsst_response) && isset($jsst_response['verfication_status']) && $jsst_response['verfication_status'] == 1 ) {
			return $jsst_response['token'];
		}else{
			$jsst_error_message = esc_html(__('Something went wrong','js-support-ticket'));
			if(is_array($jsst_response) && isset($jsst_response['error'])){
				$jsst_error_message = $jsst_response['error'];
			}
			update_option( 'jsst-addon-key-error-message',$jsst_error_message);
		}
		return false;
	}
}
?>
