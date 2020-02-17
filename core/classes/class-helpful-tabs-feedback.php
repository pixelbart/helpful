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

class Helpful_Tabs_Feedback extends Helpful_Tabs
{
	/**
	 * Class instance
	 *
	 * @var Helpful_Tabs_Feedback
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
	 * @return Helpful_Tabs_Feedback
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
			'id'  => 'feedback',
			'name' => esc_html_x( 'Feedback', 'tab name', 'helpful' ),
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
		include_once HELPFUL_PATH . 'core/tabs/tab-feedback.php';
	}

	/**
	 * Register settings for admin page
	 *
	 * @return void
	 */
	public function register_settings()
	{
		$fields = [
			'helpful_feedback_widget',
			'helpful_feedback_after_pro',
			'helpful_feedback_after_contra',
			'helpful_feedback_message_pro',
			'helpful_feedback_message_contra',
			'helpful_feedback_messages_table',
			'helpful_feedback_widget_overview',
			'helpful_feedback_name',
			'helpful_feedback_email',
			'helpful_feedback_cancel',
			'helpful_feedback_label_message',
			'helpful_feedback_label_name',
			'helpful_feedback_label_email',
			'helpful_feedback_label_submit',
			'helpful_feedback_label_cancel',
			'helpful_feedback_gravatar',
			'helpful_feedback_email',
			'helpful_feedback_receivers',
			'helpful_feedback_subject',
			'helpful_feedback_email_content',
		];

		$fields = apply_filters( 'helpful_feedback_fields', $fields );

		foreach ( $fields as $field ) {
			register_setting( 'helpful-feedback-settings-group', $field );
		}
	}
}
