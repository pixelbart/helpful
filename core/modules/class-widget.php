<?php
/**
 * Renders the dashboard widget and processes the Ajax requests for it.
 *
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.4.59
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
class Widget {
	use Module;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( & $this, 'widget_setup' ) );
		add_action( 'admin_enqueue_scripts', array( & $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_helpful_widget_stats', array( & $this, 'get_stats' ) );
		add_filter( 'helpful_debug_fields', array( & $this, 'debug_fields' ) );
		add_action( 'helpful/dashboard/build_cache', array( & $this, 'register_cronjob' ) );
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @param string $hook_suffix Current page slug.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		$options = new Services\Options();

		if ( 'index.php' !== $hook_suffix || 'on' === $options->get_option( 'helpful_widget', 'off', 'on_off' ) ) {
			return;
		}

		$plugin = Helper::get_plugin_data();

		$file = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css';
		wp_enqueue_style( 'helpful-chartjs', $file, array(), '2.9.3' );

		$file = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js';
		wp_enqueue_script( 'helpful-chartjs', $file, array(), '2.9.3', true );

		$file = plugins_url( 'core/assets/css/admin-widget.css', HELPFUL_FILE );
		wp_enqueue_style( 'helpful-widget', $file, array(), $plugin['Version'] );

		$file = plugins_url( 'core/assets/js/admin-widget.js', HELPFUL_FILE );
		wp_enqueue_script( 'helpful-widget', $file, array( 'jquery' ), $plugin['Version'], true );
	}

	/**
	 * Dashboard widget options
	 *
	 * @global $wp_meta_boxes
	 * @version 4.4.59
	 *
	 * @return void
	 */
	public function widget_setup() {
		$options = new Services\Options();

		if ( 'on' === $options->get_option( 'helpful_widget', 'off', 'on_off' ) ) {
			return;
		}

		global $wp_meta_boxes;

		wp_add_dashboard_widget(
			'helpful_widget',
			esc_html_x( 'Helpful', 'headline dashboard widget', 'helpful' ),
			array( & $this, 'widget_callback' ),
			null,
			array( '__block_editor_compatible_meta_box' => false )
		);

		$dashboard      = $wp_meta_boxes['dashboard']['normal']['core'];
		$helpful_widget = array( 'helpful_widget' => $dashboard['helpful_widget'] );

		unset( $dashboard['helpful_widget'] );

		$sorted_dashboard = array_merge( $helpful_widget, $dashboard );

		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

	/**
	 * Dashboard widget content
	 */
	public function widget_callback() {
		$links = array(
			'settings' => sprintf(
				'<a href="%s" title="%s">%s</a>',
				admin_url( 'admin.php?page=helpful&tab=texts' ),
				__( 'Settings', 'helpful' ),
				'<span class="dashicons dashicons-admin-settings"></span>'
			),
			'feedback' => sprintf(
				'<a href="%s" title="%s">%s</a>',
				admin_url( 'admin.php?page=helpful_feedback' ),
				__( 'Feedback', 'helpful' ),
				'<span class="dashicons dashicons-testimonial"></span>'
			),
			'stats'    => sprintf(
				'<a href="%s" title="%s">%s</a>',
				admin_url( 'admin.php?page=helpful' ),
				__( 'Statistics', 'helpful' ),
				'<span class="dashicons dashicons-chart-area"></span>'
			),
		);

		if ( Helper::is_feedback_disabled() ) {
			unset( $links['feedback'] );
		}

		$years = Helpers\Stats::get_years();

		$this->render_template( $links, $years );
	}

	/**
	 * Ajax get stats
	 */
	public function get_stats() {
		check_ajax_referer( 'helpful_widget_stats', 'helpful_widget_stats_nonce' );

		$request             = array_map( 'sanitize_text_field', $_GET );
		$response            = array();
		$response['status']  = 'error';
		$response['message'] = __( 'No entries founds', 'helpful' );
		$range               = 'today';
		$ranges              = array( 'today', 'yesterday', 'week', 'month', 'year', 'total' );

		if ( array_key_exists( 'range', $request ) && in_array( $request['range'], $ranges, true ) ) {
			$range = $request['range'];
		}

		$year = 2019;

		if ( array_key_exists( 'range', $request ) && array_key_exists( 'year', $request ) ) {
			$year = absint( $request['year'] );
		}

		switch ( $range ) {
			case 'today':
				$response = Helpers\Stats::get_stats_today( $year );
				break;
			case 'yesterday':
				$response = Helpers\Stats::get_stats_yesterday( $year );
				break;
			case 'week':
				$response = Helpers\Stats::get_stats_week( $year );
				break;
			case 'month':
				$response = Helpers\Stats::get_stats_month( $year );
				break;
			case 'year':
				$response = Helpers\Stats::get_stats_year( $year );
				break;
			case 'total':
				$response = Helpers\Stats::get_stats_total();
				break;
		}

		wp_send_json_success( $response );
	}

	/**
	 * Render widget template
	 *
	 * @param array $links html links.
	 * @param array $years years array.
	 */
	public function render_template( $links, $years ) {
		include_once HELPFUL_PATH . 'templates/admin-widget.php';
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

		$fields['chartjs'] = array(
			'label' => esc_html_x( 'Chart.js version', 'debug field label', 'helpful' ),
			'value' => '2.9.3',
		);

		return $fields;
	}

	/**
	 * This is where the cronjob for the dashboard is registered.
	 * This ensures that the cache is built in time so that the
	 * loading time is not affected.
	 */
	public function register_cronjob() {
		$years = Helpers\Stats::get_years();

		if ( $years ) {
			$stats = Helpers\Stats::get_widget_stats();

			foreach ( $years as $year ) :
				Helpers\Stats::get_stats_today( $year );
				Helpers\Stats::get_stats_yesterday( $year );
				Helpers\Stats::get_stats_week( $year );
				Helpers\Stats::get_stats_month( $year );
				Helpers\Stats::get_stats_year( $year );
				Helpers\Stats::get_stats_total();
			endforeach;

			if ( isset( $widget_stats['feedback_items'] ) ) {
				foreach ( $widget_stats['feedback_items'] as $feedback ) :
					Helpers\Feedback::get_feedback( $feedback );
				endforeach;
			}
		}
	}
}
