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

class Feedback
{
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
		
		if ( Helper::is_feedback_disabled() ) :
			return;
		endif;

		add_filter( 'helpful_get_admin_tabs', [ &$this, 'register_tab' ], 10, 2 );
		add_action( 'helpful_tabs_content', [ &$this, 'register_tab_content' ] );

		add_action( 'helpful_tab_feedback_before', [ &$this, 'register_tab_alerts' ] );
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
			'helpful_feedback_message_spam',
			'helpful_feedback_after_vote',
			'helpful_feedback_message_voted',
			'helpful_feedback_amount',
			'helpful_feedback_send_email',
			'helpful_feedback_receivers',
			'helpful_feedback_subject',
			'helpful_feedback_email_content',
			'helpful_feedback_send_email_voter',
			'helpful_feedback_subject_voter',
			'helpful_feedback_email_content_voter',
		];

		$fields = apply_filters( 'helpful_feedback_fields', $fields );

		foreach ( $fields as $field ) {
			register_setting( 'helpful-feedback-settings-group', $field );
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
		$tabs['feedback'] = [
			'id'   => 'feedback',
			'name' => esc_html_x( 'Feedback', 'tab name', 'helpful' ),
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
