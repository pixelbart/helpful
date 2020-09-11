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
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Texts
{
	/**
	 * Class instance
	 *
	 * @var Texts
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Texts
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action( 'admin_init', [ &$this, 'register_settings' ] );

		add_filter( 'helpful_get_admin_tabs', [ &$this, 'register_tab' ], 10, 2 );
		add_action( 'helpful_tabs_content', [ &$this, 'register_tab_content' ] );

		add_action( 'helpful_tab_texts_before', [ &$this, 'register_tab_alerts' ] );
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
			'helpful_column_feedback',
			'helpful_after_pro',
			'helpful_after_contra',
			'helpful_after_fallback',
		];

		$fields = apply_filters( 'helpful_texts_settings_group', $fields );

		foreach ( $fields as $field ) {
			register_setting( 'helpful-texts-settings-group', $field );
		}
	}

	/**
	 * Register tab in tabs list.
	 *
	 * @param array $tabs
	 * @param string $current
	 * 
	 * @return array
	 */
	public function register_tab( $tabs, $current )
	{
		$tabs['texts'] = [
			'id'   => 'texts',
			'name' => esc_html_x( 'Texts', 'tab name', 'helpful' ),
		];

		return $tabs;
	}

	/**
	 * Register tab content.
	 *
	 * @return void
	 */
	public function register_tab_content()
	{
		if ( ! Helper::is_active_tab( 'texts' ) ) {
			return;
		}

		$template = HELPFUL_PATH . 'templates/tabs/tab-texts.php';
		
		if ( file_exists( $template ) ) {
			include_once $template;
		}
	}

	/**
	 * Register tab alerts for settings saved and other.
	 *
	 * @return void
	 */
	public function register_tab_alerts()
	{
		if ( isset( $_GET['settings-updated'] ) ) {
			$message = esc_html_x( 'Settings saved.', 'tab alert after save', 'helpful' );
			echo Helper::get_alert( $message, 'success', 1500 );
		}
	}
}
