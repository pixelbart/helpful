<?php
/**
 * Start tab.
 *
 * @package Helpful
 * @subpackage Core\Tabs
 * @version 4.5.5
 * @since 4.3.0
 */

namespace Helpful\Core\Tabs;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Start {
	/**
	 * Class instance
	 *
	 * @var Start
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Start
	 */
	public static function get_instance() {
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
	public function __construct() {
		add_filter( 'helpful_get_admin_tabs', array( & $this, 'register_tab' ), 10, 2 );
		add_action( 'helpful_tabs_content', array( & $this, 'register_tab_content' ) );

		add_action( 'wp_ajax_helpful_range_stats', array( & $this, 'ajax_get_stats_range' ) );
		add_action( 'wp_ajax_helpful_total_stats', array( & $this, 'ajax_get_stats_total' ) );
		add_action( 'wp_ajax_helpful_get_posts_data', array( & $this, 'ajax_get_posts_data' ) );

		add_action( 'helpful_tab_start_before', array( & $this, 'register_tab_alerts' ) );
	}

	/**
	 * Add tab to filter
	 *
	 * @param array  $tabs Current tabs.
	 * @param string $current Current tab.
	 *
	 * @return array
	 */
	public function register_tab( $tabs, $current ) {
		$tabs['start'] = array(
			'id'   => 'start',
			'name' => esc_html_x( 'Start', 'tab name', 'helpful' ),
		);

		return $tabs;
	}

	/**
	 * Register tab content.
	 */
	public function register_tab_content() {
		if ( ! Helper::is_active_tab( 'start' ) ) {
			return;
		}

		$template = HELPFUL_PATH . 'templates/tabs/tab-start.php';

		if ( file_exists( $template ) ) {
			include_once $template;
		}
	}

	/**
	 * Get stats by date range
	 *
	 * @see Helpful_Helper_Values::getStatsRange()
	 */
	public function ajax_get_stats_range() {
		check_ajax_referer( 'helpful_range_stats' );

		$from = null;
		if ( array_key_exists( 'from', $_REQUEST ) ) {
			$from = wp_kses( $_REQUEST['from'], array() );
		}

		$to = null;
		if ( array_key_exists( 'to', $_REQUEST ) ) {
			$to = wp_kses( $_REQUEST['to'], array() );
		}

		$response         = array();
		$from             = date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $from ) ) );
		$to               = date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $to ) ) );
		$response         = Helpers\Stats::get_stats_range( $from, $to );
		$response['from'] = $from;
		$response['to']   = $to;

		if ( array_key_exists( 'type', $_REQUEST ) && 'default' !== $_REQUEST['type'] ) {
			$response['options']['scales'] = array(
				'xAxes' => array(
					array( 'stacked' => true ),
				),
				'yAxes' => array(
					array( 'stacked' => true ),
				),
			);
		}

		wp_send_json_success( $response );
	}

	/**
	 * Get stats total
	 *
	 * @see Helpful_Helper_Values::getStatsTotal()
	 */
	public function ajax_get_stats_total() {
		check_ajax_referer( 'helpful_admin_nonce' );
		$response = Helpers\Stats::get_stats_total();
		wp_send_json_success( $response );
	}

	/**
	 * Get posts data
	 */
	public function ajax_get_posts_data() {
		check_ajax_referer( 'helpful_admin_nonce' );

		Helpers\Values::sync_post_meta();

		$options    = new Services\Options();
		$post_types = 'any';
		$response   = array(
			'status' => 'success',
			'data'   => array(),
		);

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'helpful-contra',
					'value'   => 1,
					'compare' => '>=',
				),
				array(
					'key'     => 'helpful-pro',
					'value'   => 1,
					'compare' => '>=',
				),
			),
		);

		$args      = apply_filters( 'helpful/ajax_get_posts_data/args', $args );
		$transient = 'helpful_admin_start_' . md5( serialize( $args ) );
		$query     = get_transient( $transient );

		if ( false === $query ) {
			$query       = new \WP_Query( $args );
			$cache_time  = $options->get_option( 'helpful_cache_time', 'minute', 'esc_attr' );
			$cache_times = Helpers\Cache::get_cache_times( false );
			$cache_time  = ( array_key_exists( $cache_time, $cache_times ) ) ? $cache_times[ $cache_time ] : MINUTE_IN_SECONDS;

			set_transient( $transient, $query, $cache_time );
		}

		if ( $query->found_posts ) {
			foreach ( $query->posts as $post_id ) :

				$data   = Helpers\Stats::get_single_post_stats( $post_id );
				$title  = $data['title'];
				$length = apply_filters( 'helpful_datatables_string_length', 35 );

				if ( strlen( $title ) > $length ) {
					$title = substr( $title, 0, $length ) . '...';
				}

				if ( '' === $title || 0 === strlen( $title ) ) {
					$title = esc_html_x( 'No title found', 'message if no post title was found', 'helpful' );
				}

				$feedback = Helpers\Feedback::get_feedback_count( $post_id );
				$feedback = intval( $feedback );

				update_post_meta( $post_id, 'helpful-feedback-count', $feedback );

				$feedback_url = admin_url( 'admin.php?page=helpful_feedback&post_id=' . $post_id );

				$feedback_display = 0;

				if ( 0 < $feedback ) {
					$feedback_display = sprintf( '<a href="%s">%d</a>', $feedback_url, $feedback );
				}

				$row = array(
					'post_id' => $data['ID'],
					'post_title' => sprintf(
						'<a href="%1$s" title="%2$s" target="_blank">%2$s</a>',
						esc_url( $data['permalink'] ),
						esc_html( $title )
					),
					'post_type' => array(
						'display' => $data['type']['name'],
						'sort'    => $data['type']['slug'],
					),
					'post_author' => array(
						'display' => $data['author']['name'],
						'sort'    => $data['author']['ID'],
					),
					'pro' => array(
						'display' => sprintf( '%s (%s%%)', $data['pro']['value'], $data['pro']['percentage'] ),
						'sort'    => $data['pro']['value'],
					),
					'contra' => array(
						'display' => sprintf( '%s (%s%%)', $data['contra']['value'], $data['contra']['percentage'] ),
						'sort'    => $data['contra']['value'],
					),
					'helpful' => array(
						'display' => sprintf( '%s%%', $data['helpful'] ),
						'sort'    => $data['helpful'],
					),
					'post_date' => array(
						'display' => $data['time']['date'],
						'sort'    => $data['time']['timestamp'],
					),
				);

				if ( ! Helper::is_feedback_disabled() ) {
					$row['feedback'] = array(
						'display' => $feedback_display,
						'sort'    => $feedback,
					);
				}

				$response['data'][] = $row;

			endforeach;
		}

		wp_send_json( $response );
	}

	/**
	 * Register tab alerts for settings saved and other.
	 */
	public function register_tab_alerts() {
		if ( array_key_exists( 'settings-updated', $_GET ) ) {
			$message = esc_html_x( 'Settings saved.', 'tab alert after save', 'helpful' );
			echo Helper::get_alert( $message, 'success', 0 );
		}
	}
}
