<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
if(!empty($this->jsst_addon_installed_array)){
	$jsst_new_transient_flag = 0;
	//delete_transient('jsst_addon_update_flag');
	$jsst_response = get_transient('jsst_addon_update_flag');
	if(!$jsst_response){
		$jsst_response = $this->getPluginLatestVersionData();
		set_transient('jsst_addon_update_flag',$jsst_response,HOUR_IN_SECONDS * 6);
		$jsst_new_transient_flag = 1;
	}
	if(!empty($jsst_response)){
		foreach ($this->jsst_addon_installed_array as $jsst_addon) {
			if(!isset($jsst_response[$jsst_addon])){
				continue;
			}
			$jsst_plugin_file_path = content_url().'/plugins/'.$jsst_addon.'/'.$jsst_addon.'.php';

			$jsst_plugin_data = get_plugin_data($jsst_plugin_file_path);
			$jsst_transient_val = get_transient('dismiss-jsst-addon-update-notice-'.$jsst_addon);
			if($jsst_new_transient_flag == 1){
				delete_transient('dismiss-jsst-addon-update-notice-'.$jsst_addon);
			}
			if(!$jsst_transient_val){
				if (version_compare( $jsst_response[$jsst_addon], $jsst_plugin_data['Version'], '>' ) ) { ?>
					<div class="updated">
						<p class="wpjm-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-jsst-addon-update-notice-' . sanitize_title( $jsst_addon ), '1' ) ); ?>"><?php esc_html_e( 'Hide notice', 'js-support-ticket' ); ?></a></p>
						<p><?php printf( '<a href="%s">New Version is avaible</a> for "%s".', esc_url(admin_url('plugins.php')), esc_html( $jsst_plugin_data['Name'] ) ); ?></p>
					</div>
				<?php }
			}
		}
	}

}

if(get_option( 'jsst-addon-key-error-message', '' ) != ''){
	echo '<div class="notice notice-error is-dismissible"><p>'. esc_html(get_option( 'jsst-addon-key-error-message')) .'</p></div>';
	delete_option( 'jsst-addon-key-error-message' );
}
?>
