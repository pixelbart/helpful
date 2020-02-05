<?php
/**
 * Admin tab for feedback.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.0.0
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpful_Tabs_Texts extends Helpful_Tabs {

	/**
	 * Class instance
	 *
	 * @var Helpful_Tabs_Texts
	 */
	public static $instance;

	/**
	 * Stores tab data
	 *
	 * @var array
	 */
	public $tab_info;

	/**
	 * Stores tab content
	 *
	 * @var array
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

		add_action( 'admin_init', [ &$this, 'register_settings' ] );
		add_filter( 'helpful_admin_tabs', [ &$this, 'register_tab' ] );
		add_action( 'helpful_tabs_content', [ &$this, 'add_tab_content' ] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Tabs_Texts
	 */
	public static function get_instance()
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
	public function setup_tab()
	{
		$this->tab_info    = [
			'id'   => 'texts',
			'name' => esc_html_x( 'Texts', 'tab name', 'helpful' ),
		];

		$this->tab_content = [ &$this, 'render_callback' ];
	}

	/**
	 * Include options page
	 *
	 * @return void
	 */
	public function render_callback()
	{
		include_once HELPFUL_PATH . 'core/tabs/tab-texts.php';
	}

	/**
	 * Register settings for admin page
	 *
	 * @return void
	 */
	public function register_settings()
	{
		$fields = [
			'helpful_heading',
			'helpful_content',
			'helpful_pro',
			'helpful_exists',
			'helpful_contra',
			'helpful_column_pro',
			'helpful_column_contra',
			'helpful_after_pro',
			'helpful_after_contra',
		];

		foreach ( $fields as $field ) {
			register_setting( 'helpful-texts-settings-group', $field );
		}
	}
}
