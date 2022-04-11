<?php
/**
 * Texts tab.
 *
 * @package Helpful
 * @subpackage Core\Tabs
 * @version 4.5.5
 * @since 4.3.0
 */

namespace Helpful\Core\Tabs;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Texts {
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
		add_action( 'admin_init', array( & $this, 'register_settings' ) );
		add_filter( 'helpful_get_admin_tabs', array( & $this, 'register_tab' ), 10, 2 );
		add_action( 'helpful_tabs_content', array( & $this, 'register_tab_content' ) );
		add_action( 'helpful_tab_texts_before', array( & $this, 'register_tab_alerts' ) );
	}

	/**
	 * Register settings for admin page
	 */
	public function register_settings() {
		$fields = array(
			'helpful_heading' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_content' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_pro' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_pro_disabled' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_exists' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_contra' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_contra_disabled' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_column_pro' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_column_contra' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_column_feedback' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_after_pro' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_after_contra' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_after_fallback' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
		);

		$fields = apply_filters( 'helpful_texts_settings_group', $fields );

		foreach ( $fields as $field => $args ) {
			register_setting( 'helpful-texts-settings-group', $field, apply_filters( 'helpful_settings_group_args', $args, $field ) );
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
		$tabs['texts'] = array(
			'id'   => 'texts',
			'name' => esc_html_x( 'Texts', 'tab name', 'helpful' ),
		);

		return $tabs;
	}

	/**
	 * Register tab content.
	 */
	public function register_tab_content() {
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
		return wp_kses( $value, array() );
	}
}
