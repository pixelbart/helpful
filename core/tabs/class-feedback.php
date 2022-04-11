<?php
/**
 * Feedback tab.
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
class Feedback {
	/**
	 * Class instance
	 *
	 * @var Feedback
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Feedback
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

		if ( Helper::is_feedback_disabled() ) {
			return;
		}

		add_filter( 'helpful_get_admin_tabs', array( & $this, 'register_tab' ), 10, 2 );
		add_action( 'helpful_tabs_content', array( & $this, 'register_tab_content' ) );

		add_action( 'helpful_tab_feedback_before', array( & $this, 'register_tab_alerts' ) );
	}

	/**
	 * Register settings for admin page
	 */
	public function register_settings() {
		$fields = array(
			'helpful_feedback_widget' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_after_pro' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_after_contra' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_message_pro' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_message_contra' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_messages_table' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_widget_overview' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_name' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_email' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_cancel' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_label_message' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_label_name' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_label_email' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_label_submit' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_label_cancel' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_gravatar' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_message_spam' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_after_vote' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_message_voted' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_amount' => array(
				'type'              => 'integer',
				'sanitize_callback' => 'intval',
			),
			'helpful_feedback_send_email' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_receivers' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input_without_tags' ),
			),
			'helpful_feedback_subject' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input_without_tags' ),
			),
			'helpful_feedback_email_content' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
			'helpful_feedback_send_email_voter' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'helpful_feedback_subject_voter' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input_without_tags' ),
			),
			'helpful_feedback_email_content_voter' => array(
				'type'              => 'string',
				'sanitize_callback' => array( & $this, 'sanitize_input' ),
			),
		);

		$fields = apply_filters( 'helpful_feedback_settings_group', $fields );

		foreach ( $fields as $field => $args ) {
			register_setting( 'helpful-feedback-settings-group', $field, apply_filters( 'helpful_settings_group_args', $args, $field ) );
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
		$tabs['feedback'] = array(
			'id'   => 'feedback',
			'name' => esc_html_x( 'Feedback', 'tab name', 'helpful' ),
		);

		return $tabs;
	}

	/**
	 * Register tab content.
	 */
	public function register_tab_content() {
		if ( ! Helper::is_active_tab( 'feedback' ) ) {
			return;
		}

		$template = HELPFUL_PATH . 'templates/tabs/tab-feedback.php';

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
