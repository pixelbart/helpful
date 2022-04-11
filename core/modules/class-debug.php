<?php
/**
 * Helps find errors by providing information to the WordPress Health Check class.
 *
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.5.0
 * @since 4.3.0
 */

namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Module;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Debug {
	use Module;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'helpful_debug_fields', array( & $this, 'debug_fields' ), 1 );
		add_filter( 'debug_information', array( & $this, 'debug_information' ) );
	}

	/**
	 * Debug Informations for Site Health Page.
	 *
	 * @param array $info Current infos.
	 *
	 * @return array
	 */
	public function debug_information( $info ) {
		$fields = array();

		$info['helpful'] = array(
			'label'       => esc_html_x( 'Helpful', 'debug label', 'helpful' ),
			'description' => esc_html_x( 'If you have problems with the plugin, these values can help you with support.', 'debug description', 'helpful' ),
			'fields'      => apply_filters( 'helpful_debug_fields', $fields ),
			'private'     => true,
		);

		return $info;
	}

	/**
	 * Fields for Debug Informations.
	 *
	 * @param array $fields Current fields.
	 *
	 * @return array
	 */
	public function debug_fields( $fields ) {
		$plugin = Helper::get_plugin_data();

		$fields['version'] = array(
			'label' => esc_html_x( 'Helpful version', 'debug field label', 'helpful' ),
			'value' => $plugin['Version'],
		);

		$fields['wordpress'] = array(
			'label' => esc_html_x( 'WordPress version', 'debug field label', 'helpful' ),
			'value' => get_bloginfo( 'version' ),
		);

		$fields['php'] = array(
			'label' => esc_html_x( 'PHP version', 'debug field label', 'helpful' ),
			'value' => phpversion( 'tidy' ),
		);

		$fields['pro'] = array(
			'label' => esc_html_x( 'Pro totals', 'debug field label', 'helpful' ),
			'value' => Helpers\Stats::get_pro_all(),
		);

		$fields['contra'] = array(
			'label' => esc_html_x( 'Contra totals', 'debug field label', 'helpful' ),
			'value' => Helpers\Stats::get_contra_all(),
		);

		$fields['feedback'] = array(
			'label' => esc_html_x( 'Feedback totals', 'debug field label', 'helpful' ),
			'value' => Helpers\Feedback::get_feedback_count( null ),
		);

		return $fields;
	}
}
