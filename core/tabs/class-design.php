<?php
/**
 * ...
 *
 * @package Helpful\Core\Modules
 * @author  Pixelbart <me@pixelbart.de>
 * @version 4.3.0
 */
namespace Helpful\Core\Tabs;

use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Vendor as Vendor;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Design
{
	/**
	 * Instance
	 *
	 * @var Design
	 */
	public static $instance;

	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		add_filter( 'helpful_get_admin_tabs', [ &$this, 'register_tab' ], 10, 2 );
		add_action( 'wp_head', [ &$this, 'custom_css' ], PHP_INT_MAX );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Design
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
	 * @param array $tabs current tabs.
	 *
	 * @return array
	 */
	public function register_tab( $tabs, $current )
	{
		$query                       = [];
		$query['autofocus[section]'] = 'helpful_design';
		$section_link                = add_query_arg( $query, admin_url( 'customize.php' ) );

		$tabs['design'] = [
			'id'   => 'design',
			'name' => esc_html_x( 'Design', 'tab name', 'helpful' ),
			'href' => $section_link,
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
		$custom_css = get_option( 'helpful_css' );

		$parser = new Vendor\Css_Parser();

		$parser->load_string( $custom_css );

		$parser->parse();

		$custom_css = $parser->glue();

		if ( $custom_css && '' !== trim( $custom_css ) ) {
			echo '<!-- helpful custom css -->';
			echo '<style>' . $custom_css . '</style>';
			echo '<!-- END helpful custom css -->';
		}
	}
}
