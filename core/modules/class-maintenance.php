<?php
/**
 * Performs maintenance when the plugin is updated. This removes
 * incorrect entries and optimizes the database table.
 *
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.4.50
 * @since 4.3.0
 */

namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Module;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Maintenance {
	use Module;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( & $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_helpful_perform_maintenance', array( & $this, 'maintenance' ) );
		add_action( 'admin_init', array( & $this, 'maintenance_after_update' ) );
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @param string $hook_suffix Current page slug.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'toplevel_page_helpful' !== $hook_suffix ) {
			return;
		}

		$plugin = Helper::get_plugin_data();
		$nonce  = wp_create_nonce( 'helpful_maintenance_nonce' );
		$file   = plugins_url( '/core/assets/js/admin-maintenance.js', HELPFUL_FILE );

		wp_enqueue_script( 'helpful-maintenance', $file, array( 'jquery' ), $plugin['Version'], true );

		$vars = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'data' => array(
				'action'   => 'helpful_perform_maintenance',
				'_wpnonce' => $nonce,
			),
		);

		wp_localize_script( 'helpful-maintenance', 'helpful_maintenance', $vars );
	}

	/**
	 * Ajax action for performing maintenance.
	 *
	 * @see class-helpful-helper-optimize.php
	 *
	 * @return void
	 */
	public function maintenance() {
		check_admin_referer( 'helpful_maintenance_nonce' );
		$response = Helpers\Optimize::optimize_plugin();
		$response = apply_filters( 'helpful_maintenance', $response );
		wp_send_json( $response );
	}

	/**
	 * Informs the user to perform maintenance.
	 */
	public function maintenance_after_update() {
		$options = new Services\Options();

		if ( 'on' === $options->get_option( 'helpful_notes' ) ) {
			return;
		}

		/**
		 * Deletes the old transient from version 4.1.4
		 */
		if ( get_transient( 'helpful_updated' ) ) {
			delete_transient( 'helpful_updated' );
		}

		$plugin = Helper::get_plugin_data();
		$option = $options->get_option( 'helpful_plugin_version' );

		if ( $option === $plugin['Version'] ) {
			return;
		}

		$response = Helpers\Optimize::optimize_plugin();

		update_option( 'helpful_plugin_version', $plugin['Version'] );
	}
}
