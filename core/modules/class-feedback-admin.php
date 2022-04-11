<?php
/**
 * Allows you to manage and view feedback in the admin area.
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
use Helpful\Core\Services as Services;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Feedback_Admin {
	use Module;

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( Helper::is_feedback_disabled() ) {
			return;
		}

		add_action( 'admin_menu', array( & $this, 'add_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( & $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_helpful_admin_feedback_items', array( & $this, 'ajax_get_feedback_items' ) );
		add_action( 'wp_ajax_helpful_remove_feedback', array( & $this, 'ajax_delete_feedback_item' ) );
		add_action( 'wp_ajax_helpful_export_feedback', array( & $this, 'ajax_export_feedback' ) );
		add_action( 'wp_ajax_helpful_delete_all_feedback', array( & $this, 'ajax_delete_all_feedback' ) );
	}

	/**
	 * Add submenu item for feedback with permission for all roles with publish_posts.
	 */
	public function add_submenu() {
		add_submenu_page(
			'helpful',
			__( 'Helpful Feedback', 'helpful' ),
			__( 'Feedback', 'helpful' ),
			apply_filters( 'helpful_feedback_capability', 'publish_posts' ),
			'helpful_feedback',
			array( & $this, 'admin_page_callback' )
		);
	}

	/**
	 * Render admin page for feedback.
	 */
	public function admin_page_callback() {
		include_once HELPFUL_PATH . 'templates/admin-feedback.php';
	}

	/**
	 * Enqueue backend scripts and styles, if current screen is helpful.
	 *
	 * @param string $hook_suffix Current page name.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'helpful_page_helpful_feedback' !== $hook_suffix ) {
			return;
		}

		$plugin = Helper::get_plugin_data();

		$file = plugins_url( 'core/assets/css/admin-feedback.css', HELPFUL_FILE );
		wp_enqueue_style( 'helpful-admin-feedback', $file, false, $plugin['Version'] );

		$file = plugins_url( 'core/assets/js/admin-feedback.js', HELPFUL_FILE );
		wp_enqueue_script( 'helpful-admin-feedback', $file, array(), $plugin['Version'], true );

		$vars = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'helpful_admin_feedback_nonce' ),
			'texts'    => array(
				'delete_feedback' => esc_html__( 'Are you sure you want to delete all your feedback?', 'helpful' ),
			),
		);

		wp_localize_script( 'helpful-admin-feedback', 'helpful_admin_feedback', $vars );
	}

	/**
	 * Ajax get feedback items
	 *
	 * @global $wpdb
	 */
	public function ajax_get_feedback_items() {
		check_ajax_referer( 'helpful_admin_feedback_filter' );

		$request = array_map( 'sanitize_text_field', $_REQUEST );
		$options = new Services\Options();

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful_feedback';
		$filters    = array( 'all', 'pro', 'contra' );
		$sql        = "SELECT * FROM $table_name";

		$limit = $options->get_option( 'helpful_feedback_amount', 10, 'intval' );
		$limit = intval( apply_filters( 'helpful_feedback_limit', $limit ) );

		if ( ! $limit || 0 === $limit ) {
			$limit = 10;
		}

		$page = 1;

		if ( isset( $request['paginate'] ) && is_numeric( $request['paginate'] ) ) {
			$page = intval( $request['paginate'] );
		}

		if ( isset( $request['filter'] ) && in_array( $request['filter'], $filters, true ) ) {
			if ( 'pro' === $request['filter'] ) {
				$sql .= ' WHERE pro = 1';
			}

			if ( 'contra' === $request['filter'] ) {
				$sql .= ' WHERE contra = 1';
			}
		}

		if ( isset( $request['post_id'] ) && is_numeric( $request['post_id'] ) ) {
			if ( strpos( $sql, 'WHERE' ) ) {
				$sql .= ' AND post_id = ' . intval( $request['post_id'] );
			} else {
				$sql .= ' WHERE post_id = ' . intval( $request['post_id'] );
			}
		}

		$sql .= ' ORDER BY time DESC';

		$count_sql = str_replace( 'SELECT *', 'SELECT count(*)', $sql );
		$count     = $wpdb->get_var( $count_sql );

		if ( $count <= $limit ) {
			$max_num_pages = 1;
		} else {
			$max_num_pages = ceil( $count / $limit );
		}

		$next_show = true;
		$next_page = $page + 1;

		$prev_show = true;
		$prev_page = $page - 1;

		if ( 1 >= $page ) {
			$prev_show = false;
		}

		if ( $max_num_pages <= $page ) {
			$next_show = false;
		}

		$offset = 0;

		if ( 1 < $page ) {
			$offset = $limit * ( $page - 1 );
		}

		if ( $page > $max_num_pages ) {
			$page = 1;
		}

		$sql .= " LIMIT $limit OFFSET $offset";

		$posts = $wpdb->get_results( $sql );

		if ( $posts && 1 <= count( $posts ) ) {
			foreach ( $posts as $post ) {
				$feedback = Helpers\Feedback::get_feedback( $post );
				include HELPFUL_PATH . 'templates/admin-feedback-item.php';
			}

			include HELPFUL_PATH . 'templates/admin-feedback-pagination.php';
		} else {
			printf(
				'<div class="helpful-alert helpful-alert-info">%s</div>',
				esc_html__( 'No entries found.', 'helpful' )
			);
		}

		wp_die();
	}

	/**
	 * Ajax delete single feedback item.
	 *
	 * @global $wpdb
	 */
	public function ajax_delete_feedback_item() {
		check_ajax_referer( 'helpful_admin_feedback_nonce' );

		$request = array_map( 'sanitize_text_field', $_REQUEST );

		global $wpdb;

		if ( isset( $request['feedback_id'] ) ) {
			$feedback_id = absint( $request['feedback_id'] );
			$table_name  = $wpdb->prefix . 'helpful_feedback';
			$wpdb->delete( $table_name, array( 'id' => $feedback_id ) );
		}

		wp_die();
	}

	/**
	 * Exports the feedback to a CSV.
	 *
	 * @global $wpdb
	 */
	public function ajax_export_feedback() {
		check_ajax_referer( 'helpful_admin_feedback_nonce' );

		global $wpdb;

		$table = $wpdb->prefix . 'helpful_feedback';
		$rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );

		$response = array(
			'status'  => 'error',
			'file'    => '',
			'message' => esc_html_x( 'File could not be created.', 'failed upload alert', 'helpful' ),
		);

		if ( $rows ) {
			$items = array();

			foreach ( $rows as $row ) :
				$fields = maybe_unserialize( $row->fields );

				$line = array(
					'post'      => esc_html( get_the_title( $row->post_id ) ),
					'permalink' => esc_url( get_the_permalink( $row->post_id ) ),
					'name'      => ( isset( $fields['name'] ) ) ? $fields['name'] : '',
					'email'     => ( isset( $fields['email'] ) ) ? $fields['email'] : '',
					'message'   => $row->message,
					'pro'       => $row->pro,
					'contra'    => $row->contra,
					'time'      => $row->time,
				);

				$items[] = apply_filters( 'helpful/feedback/export/line', $line, $row );
			endforeach;

			if ( ! empty( $items ) ) {
				$csv = new Services\CSV( apply_filters( 'helpful/feedback/export/csv_name', 'feedback.csv' ) );
				$csv->add_items( $items );
				$csv->create_file();

				$response['status'] = 'success';
				$response['file']   = $csv->get_file();
			}
		}

		wp_send_json( $response );
	}

	/**
	 * Empties the feedback table and optimizes it afterwards.
	 */
	public function ajax_delete_all_feedback() {
		check_ajax_referer( 'helpful_admin_feedback_nonce' );

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful_feedback';

		$wpdb->query( "TRUNCATE TABLE $table_name" );
		$wpdb->query( "OPTIMIZE TABLE $table_name" );

		$rows = $wpdb->get_var( "SELECT count(*) FROM $table_name" );

		if ( ! $rows ) {
			wp_send_json_success( _x( 'Your feedback has been deleted.', 'success message', 'helpful' ) );
		}

		/* translators: %s link to wp.org */
		$message = _x( 'Your feedback could not be deleted. Try again or report the error in the WordPress Support Forum: %s', 'error message', 'helpful' );
		$message = sprintf( $message, 'https://wordpress.org/support/plugin/helpful/' );

		wp_send_json_error( $message );
	}
}
