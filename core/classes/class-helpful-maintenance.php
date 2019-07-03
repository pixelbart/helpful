<?php
/**
 * Class for maintain and optimize plugin tables.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */
class Helpful_Maintenance {

	/**
	 * Class instance
	 *
	 * @var $instance
	 */
	public static $instance;

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_helpful_perform_maintenance', [ $this, 'perform_maintenance' ] );
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
	 * Enqueue styles and scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( isset( $_GET['page'] ) && 'helpful' !== $_GET['page'] ) {
			return;
		}

		$nonce = wp_create_nonce( 'helpful_maintenance_nonce' );
		$file  = plugins_url( '/core/assets/js/admin-maintenance.js', HELPFUL_FILE );

		wp_enqueue_script( 'helpful-maintenance', $file, [ 'jquery' ], HELPFUL_VERSION, true );

		$vars = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'data'     => [
				'action'   => 'helpful_perform_maintenance',
				'_wpnonce' => $nonce,
			],
		];

		wp_localize_script( 'helpful-maintenance', 'helpful_maintenance', $vars );
	}

	/**
	 * Ajax action for performing maintenance.
	 *
	 * @see class-helpful-helper-optimize.php
	 *
	 * @return void
	 */
	public function perform_maintenance() {
		check_admin_referer( 'helpful_maintenance_nonce' );

		$response = Helpful_Helper_Optimize::optimize_plugin();
		$response = apply_filters( 'helpful_maintenance', $response );

		header( 'Content-Type: application/json' );
		echo json_encode( $response );
		wp_die();
	}
}
