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

class Details
{
	/**
	 * Class instance
	 *
	 * @var Details
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Details
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

		add_action( 'helpful_tab_details_before', [ &$this, 'register_tab_alerts' ] );
	}

	/**
	 * Register settings for admin page
	 *
	 * @return void
	 */
	public function register_settings()
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
			'helpful_feedback_widget',
			'helpful_feedback_disabled',
		];

		$fields = apply_filters( 'helpful_details_settings_group', $fields );

		foreach ( $fields as $field ) :
			register_setting( 'helpful-details-settings-group', $field );
		endforeach;
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
		$tabs['details'] = [
			'id'   => 'details',
			'name' => esc_html_x( 'Details', 'tab name', 'helpful' ),
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
		if ( ! Helper::is_active_tab( 'details' ) ) {
			return;
		}

		$post_types         = get_post_types( [ 'public' => true ] );
		$private_post_types = get_post_types( [ 'public' => false ] );

		if ( isset( $private_post_types ) ) {
			$post_types = array_merge( $post_types, $private_post_types );
		} else {
			$private_post_types = [];
		}

		$template = HELPFUL_PATH . 'templates/tabs/tab-details.php';
		
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
