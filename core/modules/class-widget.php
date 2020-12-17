<?php
/**
 * ...
 *
 * @package Helpful\Core\Modules
 * @author  Pixelbart <me@pixelbart.de>
 * @version 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget
{
	/**
	 * Class instance
	 *
	 * @var Widget
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Widget
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
		add_action( 'wp_dashboard_setup', [ &$this, 'widget_setup' ] );
		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_helpful_widget_stats', [ &$this, 'get_stats'] );
		add_filter( 'helpful_debug_fields', [ &$this, 'debug_fields' ] );
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @param string $hook_suffix
	 * 
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix )
	{
		if ( 'index.php' !== $hook_suffix ) {
			return;
		}

		if ( get_option( 'helpful_widget' ) ) {
			return;
		}

		$plugin = Helper::get_plugin_data();

		$file = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css';
		wp_enqueue_style( 'helpful-chartjs', $file, [], '2.9.3' );

		$file = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js';
		wp_enqueue_script( 'helpful-chartjs', $file, [], '2.9.3', true );

		$file = plugins_url( 'core/assets/css/admin-widget.css', HELPFUL_FILE );
		wp_enqueue_style( 'helpful-widget', $file, [], $plugin['Version'] );

		$file = plugins_url( 'core/assets/js/admin-widget.js', HELPFUL_FILE );
		wp_enqueue_script( 'helpful-widget', $file, [ 'jquery' ], $plugin['Version'], true );
	}

	/**
	 * Dashboard widget options
	 *
	 * @global $wp_meta_boxes
	 *
	 * @return void
	 */
	public function widget_setup()
	{
		if ( get_option( 'helpful_widget' ) ) {
			return;
		}

		global $wp_meta_boxes;

		wp_add_dashboard_widget(
			'helpful_widget',
			esc_html_x( 'Helpful', 'headline dashboard widget', 'helpful' ),
			[ &$this, 'widget_callback' ],
			null,
			[ '__block_editor_compatible_meta_box' => false ]
		);

		$dashboard      = $wp_meta_boxes['dashboard']['normal']['core'];
		$helpful_widget = [ 'helpful_widget' => $dashboard['helpful_widget'] ];

		unset( $dashboard['helpful_widget'] );

		$sorted_dashboard = array_merge( $helpful_widget, $dashboard );

		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

	/**
	 * Dashboard widget content
	 *
	 * @return void
	 */
	public function widget_callback()
	{
		$links = [
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
			'stats' => sprintf(
				'<a href="%s" title="%s">%s</a>',
				admin_url( 'admin.php?page=helpful' ),
				__( 'Statistics', 'helpful' ),
				'<span class="dashicons dashicons-chart-area"></span>'
			),
		];

		if ( Helper::is_feedback_disabled() ) {
			unset( $links['feedback'] );
		}

		$years = Helpers\Stats::get_years();

		$this->render_template( $links, $years );
	}

	/**
	 * Ajax get stats
	 *
	 * @return void
	 */
	public function get_stats()
	{
		check_ajax_referer( 'helpful_widget_stats', 'helpful_widget_stats_nonce' );

		$response            = [];
		$response['status']  = 'error';
		$response['message'] = __( 'No entries founds', 'helpful' );
		$range               = 'today';
		$ranges              = [ 'today', 'yesterday', 'week', 'month', 'year', 'total' ];

		if ( isset( $_GET['range'] ) && in_array( $_GET['range'], $ranges ) ) {
			$range = $_GET['range'];
		}

		$year = 2019;

		if ( isset( $_GET['range'] ) && isset( $_GET['year'] ) ) {
			$year = absint( $_GET['year'] );
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
	 *
	 * @return void
	 */
	public function render_template( $links, $years )
	{
		include_once HELPFUL_PATH . 'templates/admin-widget.php';
	}

	/**
	 * Fields for Debug Informations.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function debug_fields( $fields )
	{
		$plugin = Helper::get_plugin_data();
		
		$fields['chartjs'] = [
			'label' => esc_html_x( 'Chart.js version', 'debug field label', 'helpful' ),
			'value' => '2.9.3',
		];

		return $fields;
	}
}