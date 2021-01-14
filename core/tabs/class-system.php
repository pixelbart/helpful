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

class System
{
	/**
	 * Class instance
	 *
	 * @var System
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return System
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

		add_action( 'admin_init', [ &$this, 'reset_plugin' ] );
		add_action( 'admin_init', [ &$this, 'reset_feedback' ] );

		if ( get_option( 'helpful_classic_editor' ) ) {
			add_filter( 'use_block_editor_for_post', '__return_false', 10 );
		}

		add_action( 'helpful_tab_system_before', [ &$this, 'register_tab_alerts' ] );
	}

	/**
	 * Register settings for admin page
	 *
	 * @return void
	 */
	public function register_settings()
	{
		$fields = [
			'helpful_uninstall',
			'helpful_timezone',
			'helpful_multiple',
			'helpful_notes',
			'helpful_plugin_first',
			'helpful_classic_editor',
			'helpful_caching',
			'helpful_caching_time',
			'helpful_export_separator',
			'helpful_uninstall_feedback',
			'helpful_sessions_false',
			'helpful_user_random',
			'helpful_disable_frontend_nonce',
			'helpful_disable_feedback_nonce',
		];

		$fields = apply_filters( 'helpful_system_settings_group', $fields );

		foreach ( $fields as $field ) {
			register_setting( 'helpful-system-settings-group', $field );
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
		$tabs['system'] = [
			'id'   => 'system',
			'name' => esc_html_x( 'System', 'tab name', 'helpful' ),
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
		if ( ! Helper::is_active_tab( 'system' ) ) {
			return;
		}

		$post_types         = get_post_types( [ 'public' => true ] );
		$private_post_types = get_post_types( [ 'public' => false ] );

		if ( isset( $private_post_types ) ) {
			$post_types = array_merge( $post_types, $private_post_types );
		} else {
			$private_post_types = [];
		}

		$template = HELPFUL_PATH . 'templates/tabs/tab-system.php';
		
		if ( file_exists( $template ) ) {
			include_once $template;
		}
	}

	/**
	 * Reset helpful database and entries
	 *
	 * @global $wpdb
	 *
	 * @return void
	 */
	public function reset_plugin()
	{
		if ( ! get_option( 'helpful_uninstall' ) ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';
		$wpdb->query( "TRUNCATE TABLE $table_name" );
		update_option( 'helpful_uninstall', false );

		$args  = [
			'post_type'      => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];
		$posts = new \WP_Query( $args );

		if ( $posts->found_posts ) {
			foreach ( $posts->posts as $post_id ) {
				if ( get_post_meta( $post_id, 'helpful-pro' ) ) {
					delete_post_meta( $post_id, 'helpful-pro' );
				}
				if ( get_post_meta( $post_id, 'helpful-contra' ) ) {
					delete_post_meta( $post_id, 'helpful-contra' );
				}

				if ( 'helpful_feedback' === get_post_type( $post_id ) ) {
					wp_delete_post( $post_id, true );
				}
			}
		}

		update_option( 'helpful_is_installed', 0 );
	}

	/**
	 * Reset helpful feedback database
	 *
	 * @global $wpdb
	 *
	 * @return void
	 */
	public function reset_feedback()
	{
		if ( ! get_option( 'helpful_uninstall_feedback' ) ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful_feedback';
		$wpdb->query( "TRUNCATE TABLE $table_name" );
		update_option( 'helpful_uninstall_feedback', false );
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
