<?php
/**
 * Class of adding admin notices.
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

class Helpful_Notices
{
	/**
	 * Plugin Data
	 *
	 * @var object
	 */
	private $plugin;

	/**
	 * Class instance
	 *
	 * @var Helpful_Notices
	 */
	public static $instance;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->set_plugin_data();

		add_action( 'admin_notices', [ &$this, 'perform_maintenance_notice' ] );
		add_action( 'helpful_notices', [ &$this, 'perform_maintenance' ] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Notices
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup Plugin Data
	 *
	 * @return void
	 */
	private function set_plugin_data()
	{
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->plugin = get_plugin_data( HELPFUL_FILE );
	}

	/**
	 * Informs the user to perform maintenance.
	 *
	 * @return void
	 */
	public function perform_maintenance_notice()
	{
		$screen = get_current_screen();
		$plugin = $this->plugin;

		if ( 'on' === get_option( 'helpful_notes' ) ) {
			return;
		}

		/**
		 * Deletes the old transient from version 4.1.4
		 */
		if ( get_transient( 'helpful_updated' ) ) {
			delete_transient( 'helpful_updated' );
		}

		$option = get_option( 'helpful_plugin_version' );

		if ( $option === $plugin['Version'] ) {
			return;
		}
		
		if ( 'toplevel_page_helpful' !== $screen->base ) {

			$class = 'notice-warning';
			$url   = wp_nonce_url( admin_url( 'admin.php?page=helpful' ), 'helpful_perform_maintenance', 'action' );

			/* translators: %s link to helpful settings with nonce for performing maintenance */
			$message = esc_html_x( 'The Helpful database must have been updated: %s', 'admin notice', 'helpful' );
			$button  = sprintf( '<a href="%s">%s</a>', $url, esc_html_x( 'Update database', 'admin notice action', 'helpful' ) );
			$notice  = sprintf( $message, $button );

			printf( '<div class="notice %s"><p>%s</p></div>', $class, $notice );
		}
	}

	/**
	 * Notifies the user that maintenance has been
	 * performed and performs maintenance.
	 *
	 * @return void
	 */
	public function perform_maintenance()
	{
		$screen = get_current_screen();
		$page   = 'toplevel_page_helpful';
		$nonce  = 'helpful_perform_maintenance';
		$plugin = $this->plugin;

		if ( isset( $_GET['action'] ) && wp_verify_nonce( $_GET['action'], $nonce ) && $page === $screen->base ) {
			$response = Helpful_Helper_Optimize::optimize_plugin();
			$response = apply_filters( 'helpful_maintenance', $response );
			$class    = 'notice-success';
			$notice   = esc_html_x( 'Thank you very much. The database has been updated successfully. ', 'admin notice', 'helpful' );
			printf( '<div class="notice %s is-dismissible"><p>%s</p></div>', $class, $notice );
			update_option( 'helpful_plugin_version', $plugin['Version'] );
		}
	}
}
