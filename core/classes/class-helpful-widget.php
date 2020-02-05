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

class Helpful_Widget {

	/**
	 * Class instance
	 *
	 * @var Helpful_Widget
	 */
	public static $instance;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action( 'wp_dashboard_setup', [ &$this, 'widget_setup' ], 1 );
		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_helpful_widget_stats', [ &$this, 'get_stats'] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Widget
	 */
	public static function get_instance()
	{
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
	public function enqueue_scripts()
	{
		if ( get_option( 'helpful_widget' ) ) {
			return;
		}

		$file = '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css';
		wp_enqueue_style( 'helpful-chartjs', $file, [], '2.9.3' );

		$file = '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js';
		wp_enqueue_script( 'helpful-chartjs', $file, [], '2.9.3', true );

		$file = plugins_url( 'core/assets/css/admin-widget.css', HELPFUL_FILE );
		wp_register_style( 'helpful-widget', $file, [], HELPFUL_VERSION );

		$file = plugins_url( 'core/assets/js/admin-widget.js', HELPFUL_FILE );
		wp_register_script( 'helpful-widget', $file, [ 'jquery' ], HELPFUL_VERSION, true );
	}

	/**
	 * Dashboard widget options
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
		wp_enqueue_style( 'helpful-chartjs' );
		wp_enqueue_style( 'helpful-widget' );
		wp_enqueue_script( 'helpful-chartjs' );
		wp_enqueue_script( 'helpful-widget' );

		$links = [
			sprintf(
				'<a href="%s" title="%s">%s</a>',
				admin_url( 'admin.php?page=helpful&tab=texts' ),
				__( 'Settings', 'helpful' ),
				'<span class="dashicons dashicons-admin-settings"></span>'
			),
			sprintf(
				'<a href="%s" title="%s">%s</a>',
				admin_url( 'admin.php?page=helpful_feedback' ),
				__( 'Feedback', 'helpful' ),
				'<span class="dashicons dashicons-testimonial"></span>'
			),
			sprintf(
				'<a href="%s" title="%s">%s</a>',
				admin_url( 'admin.php?page=helpful' ),
				__( 'Statistics', 'helpful' ),
				'<span class="dashicons dashicons-chart-area"></span>'
			),
		];

		$years = Helpful_Helper_Stats::getYears();

		$this->render_template( $links, $years );
	}

	/**
	 * Ajax get stats
	 *
	 * @return void
	 */
	public function get_stats()
	{
		check_ajax_referer( 'helpful_widget_stats' );

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
				$response = Helpful_Helper_Stats::getStatsToday( $year );
				break;
			case 'yesterday':
				$response = Helpful_Helper_Stats::getStatsYesterday( $year );
				break;
			case 'week':
				$response = Helpful_Helper_Stats::getStatsWeek( $year );
				break;
			case 'month':
				$response = Helpful_Helper_Stats::getStatsMonth( $year );
				break;
			case 'year':
				$response = Helpful_Helper_Stats::getStatsYear( $year );
				break;
			case 'total':
				$response = Helpful_Helper_Stats::getStatsTotal();
				break;
		}

		header( 'Content-Type: application/json' );
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Render widget template
	 *
	 * @param array $links html links.
	 * @param array $years years array.
	 *
	 * @return void
	 */
	public function render_template( array $links, array $years )
	{
		include_once HELPFUL_PATH . 'templates/admin-widget.php';
	}
}