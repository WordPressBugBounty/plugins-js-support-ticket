<?php

if (!defined('ABSPATH'))
    die('Restricted Access');
	// Add widgets files


	class jsst_main_plugin_pages_widget extends WP_Widget {


	/* ---------------------------------------------------------------------------
	 * Constructor
	 * --------------------------------------------------------------------------- */
	function __construct(){
		$widget_ops = array( 'classname' => 'jsst_main_plugin_pages_widget', 'description' => esc_html__( 'JS Help Desk Pages', 'js-support-ticket' ) );
		parent::__construct( 'jsst_main_plugin_pages_widget_options', esc_html__( 'JS Help Desk Pages', 'js-support-ticket' ), $widget_ops );
		$this->alt_option_name = 'jsst_main_plugin_pages_widget_options';
	}


	/* ---------------------------------------------------------------------------
	 * Outputs the HTML for this widget.
	 * --------------------------------------------------------------------------- */
	function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) $args['widget_id'] = null;
		extract( $args, EXTR_SKIP );
		echo wp_kses($before_widget, JSST_ALLOWED_TAGS);
		$mod = "jshelpdeskpages";
		$layoutName = $mod . uniqid();
    	$instance['jsstpageid'] = jssupportticket::getPageid();
		echo '['.wp_kses($instance['jshelpdeskpages'], JSST_ALLOWED_TAGS).']';

		echo wp_kses($after_widget, JSST_ALLOWED_TAGS);
	}


	/* ---------------------------------------------------------------------------
	 * Deals with the settings when they are saved by the admin.
	 * --------------------------------------------------------------------------- */

	public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['jshelpdeskpages'] = (!empty($new_instance['jshelpdeskpages']) ) ? jssupportticketphplib::JSST_strip_tags($new_instance['jshelpdeskpages']) : '';
        return $instance;
    }


	/* ---------------------------------------------------------------------------
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 * --------------------------------------------------------------------------- */
	function form( $instance ) {

		$jshelpdeskpages = isset( $instance['jshelpdeskpages'] ) ?  $instance['jshelpdeskpages']  : 'jssupportticket';
		?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'jshelpdeskpages' ) ); ?>"><?php esc_html_e( 'Help Desk Pages', 'js-support-ticket' ); ?>:</label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'jshelpdeskpages' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'jshelpdeskpages' ) ); ?>" >
					<option value="jssupportticket" <?php echo ( $jshelpdeskpages == 'jssupportticket' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('JS Help desk control panel','js-support-ticket'); ?></option>
					<option value="jssupportticket_addticket" <?php echo ( $jshelpdeskpages == 'jssupportticket_addticket' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Add ticket','js-support-ticket'); ?></option>
					<option value="jssupportticket_mytickets" <?php echo ( $jshelpdeskpages == 'jssupportticket_mytickets' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('My tickets','js-support-ticket'); ?></option>

				<?php if(in_array('download', jssupportticket::$_active_addons)){ ?>
					<option value="jssupportticket_downloads" <?php echo ( $jshelpdeskpages == 'jssupportticket_downloads' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('List downloads','js-support-ticket'); ?></option>
					<option value="jssupportticket_downloads_latest" <?php echo ( $jshelpdeskpages == 'jssupportticket_downloads_latest' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Latest downloads','js-support-ticket'); ?></option>
					<option value="jssupportticket_downloads_popular" <?php echo ( $jshelpdeskpages == 'jssupportticket_downloads_popular' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Popular downloads','js-support-ticket'); ?></option>
				<?php }?>

				<?php if(in_array('knowledgebase', jssupportticket::$_active_addons)){ ?>
					<option value="jssupportticket_knowledgebase" <?php echo ( $jshelpdeskpages == 'jssupportticket_knowledgebase' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('List knowledge base','js-support-ticket'); ?></option>
					<option value="jssupportticket_knowledgebase_latest" <?php echo ( $jshelpdeskpages == 'jssupportticket_knowledgebase_latest' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Latest knowledge base','js-support-ticket'); ?></option>
					<option value="jssupportticket_knowledgebase_popular" <?php echo ( $jshelpdeskpages == 'jssupportticket_knowledgebase_popular' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Popular knowledge base','js-support-ticket'); ?></option>
				<?php }?>

				<?php if(in_array('faq', jssupportticket::$_active_addons)){ ?>
					<option value="jssupportticket_faqs" <?php echo ( $jshelpdeskpages == 'jssupportticket_faqs' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('List FAQ`s','js-support-ticket'); ?></option>
					<option value="jssupportticket_faqs_latest" <?php echo ( $jshelpdeskpages == 'jssupportticket_faqs_latest' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Latest FAQ`s','js-support-ticket'); ?></option>
					<option value="jssupportticket_faqs_popular" <?php echo ( $jshelpdeskpages == 'jssupportticket_faqs_popular' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Popular FAQ`s','js-support-ticket'); ?></option>
				<?php }?>

				<?php if(in_array('announcement', jssupportticket::$_active_addons)){ ?>
					<option value="jssupportticket_announcements" <?php echo ( $jshelpdeskpages == 'jssupportticket_announcements' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('List announcements','js-support-ticket'); ?></option>
					<option value="jssupportticket_announcements_latest" <?php echo ( $jshelpdeskpages == 'jssupportticket_announcements_latest' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Latest announcements','js-support-ticket'); ?></option>
					<option value="jssupportticket_announcements_popular" <?php echo ( $jshelpdeskpages == 'jssupportticket_announcements_popular' ) ? 'selected="selected"' : false; ?>><?php echo esc_html__('Popular announcements','js-support-ticket'); ?></option>
				<?php }?>
				</select>
			</p>
		<?php
	}
}

	function jsst_main_plugin_register_widgets(){
		register_widget('jsst_main_plugin_pages_widget');
	}

	add_action('widgets_init','jsst_main_plugin_register_widgets');
?>
