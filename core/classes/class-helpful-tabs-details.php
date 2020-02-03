<?php
/**
 * Admin tab for details.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.0.0
 */
class Helpful_Tabs_Details extends Helpful_Tabs {

	/**
	 * Class instance
	 *
	 * @var Helpful_Tabs_Details
	 */
	public static $instance;

	/**
	 * Stores tab data
	 *
	 * @var $tab_info
	 */
	public $tab_info;

	/**
	 * Stores tab content
	 *
	 * @var $tab_content
	 */
	public $tab_content;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->setup_tab();

		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_filter( 'helpful_admin_tabs', [ $this, 'register_tab' ] );
		add_action( 'helpful_tabs_content', [ $this, 'add_tab_content' ] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Tabs_Details
	 */
	public static function get_instance():Helpful_Tabs_Details
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add tab to helpful admin menu
	 *
	 * @return void
	 */
	public function setup_tab():void
	{
		$this->tab_info    = [
			'id'   => 'details',
			'name' => esc_html_x( 'Details', 'tab name', 'helpful' ),
		];

		$this->tab_content = [ $this, 'render_callback' ];
	}

	/**
	 * Include options page
	 *
	 * @return void
	 */
	public function render_callback():void
	{
		$post_types         = get_post_types( [ 'public' => true ] );
		$private_post_types = get_post_types( [ 'public' => false ] );

		if ( isset( $private_post_types ) ) {
			$post_types = array_merge( $post_types, $private_post_types );
		} else {
			$private_post_types = [];
		}

		include_once HELPFUL_PATH . 'core/tabs/tab-details.php';
	}

	/**
	 * Register settings for admin page
	 *
	 * @return void
	 */
	public function register_settings():void
	{
		$fields = [
			'helpful_credits',
			'helpful_hide_in_content',
			'helpful_post_types',
			'helpful_exists_hide',
			'helpful_count_hide',
			'helpful_widget',
			'helpful_widget_amount',
			'helpful_widget_pro',
			'helpful_widget_contra',
			'helpful_widget_pro_recent',
			'helpful_widget_contra_recent',
			'helpful_only_once',
			'helpful_percentages',
			'helpful_form_status_pro',
			'helpful_form_email_pro',
			'helpful_form_status_contra',
			'helpful_form_email_contra',
			'helpful_metabox',
			'helpful_widget_hide_publication',
			'helpful_hide_admin_columns',
		];

		foreach ( $fields as $field ) :
			register_setting( 'helpful-details-settings-group', $field );
		endforeach;
	}
}
