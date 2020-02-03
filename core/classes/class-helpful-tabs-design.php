<?php
/**
 * Admin tab for design.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.0.0
 */
class Helpful_Tabs_Design {

	/**
	 * Instance
	 *
	 * @var Helpful_Tabs_Design
	 */
	public static $instance;

	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		add_filter( 'helpful_admin_tabs', [ &$this, 'register_tab' ] );
		add_action( 'wp_head', [ &$this, 'custom_css' ], PHP_INT_MAX );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Tabs_Design
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add tab to filter
	 *
	 * @global $helpful
	 *
	 * @param array $tabs current tabs.
	 *
	 * @return array
	 */
	public function register_tab( array $tabs )
	{
		$query                       = [];
		$query['autofocus[section]'] = 'helpful_design';
		$section_link                = add_query_arg( $query, admin_url( 'customize.php' ) );
		$tabs['design']              = [
			'href' => $section_link,
			'name' => esc_html_x( 'Design', 'tab name', 'helpful' ),
		];

		return $tabs;
	}

	/**
	 * Print custom css to wp_head.
	 *
	 * @return void
	 */
	public function custom_css()
	{
		if ( get_option( 'helpful_css' ) ) {
			$custom_css = get_option( 'helpful_css' );
			printf( '<style>%s</style>', $custom_css );
		}
	}
}
