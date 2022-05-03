<?php
/**
 * System tab.
 *
 * @package Helpful
 * @subpackage Core\Tabs
 * @version 4.5.7
 * @since 4.3.0
 */

namespace Helpful\Core\Tabs;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class System {
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
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class constructor
	 */
	public function __construct() {
		$options = new Services\Options();

		add_action( 'admin_init', array( & $this, 'register_settings' ) );
		add_filter( 'helpful_get_admin_tabs', array( & $this, 'register_tab' ), 10, 2 );
		add_action( 'helpful_tabs_content', array( & $this, 'register_tab_content' ) );
		add_action( 'admin_init', array( & $this, 'reset_plugin' ) );
		add_action( 'admin_init', array( & $this, 'reset_feedback' ) );

		if ( 'on' === $options->get_option( 'helpful_classic_editor', 'off', 'on_off' ) ) {
			add_filter( 'use_block_editor_for_post', '__return_false', 10 );
		}

		if ( 'on' === $options->get_option( 'helpful_classic_widgets', 'off', 'on_off' ) ) {
			add_filter( 'use_widgets_block_editor', '__return_false', 10 );
		}

		add_action( 'helpful_tab_system_before', array( & $this, 'register_tab_alerts' ) );
	}

	/**
	 * Register settings for admin page
	 */
	public function register_settings() {
		$fields = array(
			'helpful_uninstall' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_timezone' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input_without_tags' ),
			),
			'helpful_multiple' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_notes' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_plugin_first' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_classic_editor' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_classic_widgets' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_caching' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_caching_time' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input_without_tags' ),
			),
			'helpful_export_separator' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input_without_tags' ),
			),
			'helpful_uninstall_feedback' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_sessions_false' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_user_random' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_disable_frontend_nonce' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_disable_feedback_nonce' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_log_mailer_errors' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_cookies_samesite' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input_without_tags' ),
			),
		);

		$fields = apply_filters( 'helpful_system_settings_group', $fields );

		foreach ( $fields as $field => $args ) {
			register_setting( 'helpful-system-settings-group', $field, apply_filters( 'helpful_settings_group_args', $args, $field ) );
		}
	}

	/**
	 * Register tab in tabs list.
	 *
	 * @param array  $tabs Current tabs.
	 * @param string $current Current tab.
	 *
	 * @return array
	 */
	public function register_tab( $tabs, $current ) {
		$tabs['system'] = array(
			'id'   => 'system',
			'name' => esc_html_x( 'System', 'tab name', 'helpful' ),
		);

		return $tabs;
	}

	/**
	 * Register tab content.
	 */
	public function register_tab_content() {
		if ( ! Helper::is_active_tab( 'system' ) ) {
			return;
		}

		$post_types         = get_post_types( array( 'public' => true ) );
		$private_post_types = get_post_types( array( 'public' => false ) );

		if ( isset( $private_post_types ) ) {
			$post_types = array_merge( $post_types, $private_post_types );
		} else {
			$private_post_types = array();
		}

		$template = HELPFUL_PATH . 'templates/tabs/tab-system.php';

		if ( file_exists( $template ) ) {
			include_once $template;
		}
	}

	/**
	 * Reset helpful database and entries
	 */
	public function reset_plugin() {
		$options = new Services\Options();

		if ( 'off' === $options->get_option( 'helpful_uninstall', 'off', 'on_off' ) ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';

		$wpdb->query( "TRUNCATE TABLE $table_name" );

		$options->update_option( 'helpful_uninstall', 'off' );

		$args = array(
			'post_type'      => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

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
	 */
	public function reset_feedback() {
		$options = new Services\Options();

		if ( 'off' === $options->get_option( 'helpful_uninstall_feedback', 'off', 'on_off' ) ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful_feedback';

		$wpdb->query( "TRUNCATE TABLE $table_name" );

		$options->update_option( 'helpful_uninstall_feedback', 'off' );
	}

	/**
	 * Register tab alerts for settings saved and other.
	 */
	public function register_tab_alerts() {
		if ( array_key_exists( 'settings-updated', $_GET ) ) {
			$message = esc_html_x( 'Settings saved.', 'tab alert after save', 'helpful' );
			echo Helper::get_alert( $message, 'success', 0 );
		}
	}

	/**
	 * Filters the values of an option before saving them. Thus does not allow every
	 * HTML element and makes Helpful a bit more secure.
	 *
	 * @param mixed $value Input value.
	 *
	 * @return mixed
	 */
	public function sanitize_input( $value ) {
		return wp_kses( $value, Helper::kses_allowed_tags() );
	}

	/**
	 * Filters the values of an option before saving them. Thus does not allow
	 * HTML element and makes Helpful a bit more secure.
	 *
	 * @param mixed $value Input value.
	 *
	 * @return mixed
	 */
	public function sanitize_input_without_tags( $value ) {
		return wp_kses( $value, arra() );
	}
}
