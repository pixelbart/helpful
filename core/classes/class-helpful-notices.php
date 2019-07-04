<?php
/**
 * Class of adding admin notices.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.0.0
 */
class Helpful_Notices {

	/**
	 * Class instance
	 *
	 * @var $instance
	 */
	public static $instance;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', [ $this, 'perform_maintenance_notice' ] );
		add_action( 'helpful_notices', [ $this, 'perform_maintenance' ] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return instance
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Informs the user to perform maintenance.
	 *
	 * @return void
	 */
	public function perform_maintenance_notice() {
		$screen = get_current_screen();

		if ( 'on' === get_option( 'helpful_notes' ) ) {
			return;
		}

		if ( false === get_transient( 'helpful_updated' ) && 'toplevel_page_helpful' !== $screen->base ) {

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
	public function perform_maintenance() {
		$screen = get_current_screen();

		if (
			isset( $_GET['action'] ) &&
			wp_verify_nonce( $_GET['action'], 'helpful_perform_maintenance' ) &&
			'toplevel_page_helpful' === $screen->base
		) {

			$response = Helpful_Helper_Optimize::optimize_plugin();
			$response = apply_filters( 'helpful_maintenance', $response );
			$class    = 'notice-success';
			$notice   = esc_html_x( 'Thank you very much. The database has been updated successfully. ', 'admin notice', 'helpful' );
			printf( '<div class="notice %s is-dismissible"><p>%s</p></div>', $class, $notice );
			set_transient( 'helpful_updated', 1, 7 * DAY_IN_SECONDS );
		}
	}
}
