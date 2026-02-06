<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<tr id="<?php echo esc_attr( sanitize_title( $jsst_addon_slug . '_transaction_key_row' ) ); ?>" class="active plugin-update-tr jsst-updater-licence-key-tr">
	<td class="plugin-update" colspan="3">
		<div class="jsst-updater-licence-key">
			<label for="<?php echo esc_attr(sanitize_title( $jsst_addon_slug )); ?>_transaction_key"><?php esc_html_e( 'Transaction Key', 'js-support-ticket' ); ?>:</label>
			<input type="text" id="<?php echo esc_attr(sanitize_title( $jsst_addon_slug )); ?>_transaction_key" name="<?php echo esc_attr( $jsst_addon_slug ); ?>_transaction_key" placeholder="XXXXXXXXXXXXXXXX" />
			<input type="submit" id="<?php echo esc_attr(sanitize_title( $jsst_addon_slug )); ?>_submit_button" name="<?php echo esc_attr( $jsst_addon_slug ); ?>_submit_button" value="Authenticate" />
			<input type="hidden" name="jsst_addon_array_for_token[]" value="<?php echo esc_attr( $jsst_addon_slug ); ?>" />
			<div>
				<span class="description">
				    <?php 
				    // Format the addon name before passing it to the translation
				    $jsst_addon_name = '<b>' . strtoupper( jssupportticketphplib::JSST_substr( $jsst_updateaddon_slug, 0, 2 ) ) . substr( jssupportticketphplib::JSST_ucwords($jsst_updateaddon_slug), 2 );
				    
					/* translators: %1$s: formatted addon name */
					printf( esc_html__( 'Please select %1$s and Enter your license key and hit to authenticate. A valid key is required for updates.', 'js-support-ticket' ), wp_kses( $jsst_addon_name, array( 'b' => array() ) ) );
				    ?> 
				    <?php 
				    printf( 
				        /* translators: %s: link to retrieve license key */
				        esc_html__( 'Lost your key? %s.', 'js-support-ticket' ), 
				        '<a href="' . esc_url( 'https://jshelpdesk.com/' ) . '">' . esc_html__( 'Retrieve it here', 'js-support-ticket' ) . '</a>'
				    ); 
				    ?>
				</span>
			</div>
		</div>
	</td>
</tr>
<tr>
	<?php
	/*
	$jsst_latest_version = get_option('jsticketdsknotifwp_latest_version');
	if ($jsst_latest_version != false && version_compare( $jsst_latest_version, $this->plugin_data['Version'], '>' ) ) {
	?>
		<td class="plugin-update plugin-update colspanchange" colspan="3">
			<div class="update-message notice inline notice-warning notice-alt"><p>There is a new version of JS Ticket Notification available. Insert key to update plugin </p></div>
		</td>
	<?php }
	*/ ?>
</tr>
