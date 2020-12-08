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

class Feedback_Admin
{
	/**
	 * Instance
	 *
	 * @var Feedback_Admin
	 */
	public static $instance;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		if ( Helper::is_feedback_disabled() ) :
			return;
		endif;

		add_action( 'admin_menu', [ &$this, 'add_submenu' ] );
		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );

		add_action( 'wp_ajax_helpful_admin_feedback_items', [ &$this, 'ajax_get_feedback_items' ] );
		add_action( 'wp_ajax_helpful_remove_feedback', [ &$this, 'ajax_delete_feedback_item' ] );
		add_action( 'wp_ajax_helpful_export_feedback', [ &$this, 'ajax_export_feedback' ] );
	}

	/**
	 * Class instance.
	 *
	 * @return Feedback_Admin
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add submenu item for feedback with permission
	 * for all roles with publish_posts.
	 *
	 * @version 4.3.0
	 * @return void
	 */
	public function add_submenu()
	{
		add_submenu_page(
			'helpful',
			__( 'Helpful Feedback', 'helpful' ),
			__( 'Feedback', 'helpful' ),
			apply_filters( 'helpful_feedback_capability', 'publish_posts' ),
			'helpful_feedback',
			[ &$this, 'admin_page_callback' ]
		);
	}

	/**
	 * Render admin page for feedback.
	 *
	 * @return void
	 */
	public function admin_page_callback()
	{
		include_once HELPFUL_PATH . 'templates/admin-feedback.php';
	}

	/**
	 * Enqueue backend scripts and styles, if current screen is helpful.
	 *
	 * @version 4.3.0
	 *
	 * @param string $hook_suffix
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix )
	{
		if ( 'helpful_page_helpful_feedback' !== $hook_suffix ) {
			return;
		}

		$plugin = Helper::get_plugin_data();

		$file = plugins_url( 'core/assets/css/admin-feedback.css', HELPFUL_FILE );
		wp_enqueue_style( 'helpful-admin-feedback', $file, [], $plugin['Version'] );

		$file = plugins_url( 'core/assets/js/admin-feedback.js', HELPFUL_FILE );
		wp_enqueue_script( 'helpful-admin-feedback', $file, [], $plugin['Version'], true );

		$vars = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'helpful_admin_feedback_nonce' ),
		];

		wp_localize_script( 'helpful-admin-feedback', 'helpful_admin_feedback', $vars );
	}

	/**
	 * Ajax get feedback items
	 *
	 * @return void
	 */
	public function ajax_get_feedback_items()
	{
		check_ajax_referer( 'helpful_admin_feedback_filter' );

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful_feedback';
		$filters    = [ 'all', 'pro', 'contra' ];
		$sql        = "SELECT * FROM $table_name";

		$limit = get_option( 'helpful_feedback_amount', 10 );
		$limit = intval( apply_filters( 'helpful_feedback_limit', $limit ) );

		$page = 1;

		if ( isset( $_REQUEST['paginate'] ) && is_numeric( $_REQUEST['paginate'] ) ) {
			$page = intval( $_REQUEST['paginate'] );
		}

		if ( isset( $_REQUEST['filter'] ) && in_array( $_REQUEST['filter'], $filters ) ) {
			if ( 'pro' === $_REQUEST['filter'] ) {
				$sql .= ' WHERE pro = 1';
			}

			if ( 'contra' === $_REQUEST['filter'] ) {
				$sql .= ' WHERE contra = 1';
			}
		}

		if ( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) ) {
			if ( strpos( $sql, 'WHERE' ) ) {
				$sql .= ' AND post_id = ' . intval( $_REQUEST['post_id'] );
			} else {
				$sql .= ' WHERE post_id = ' . intval( $_REQUEST['post_id'] );
			}
		}

		$sql .= ' ORDER BY time DESC';

		$count = count( $wpdb->get_results( $sql ) );

		if ( $count <= $limit ) {
			$max_num_pages = 1;
		} else {
			$max_num_pages = ceil( $count / $limit );
		}

		$next_show = true;
		$next_page = $page+1;

		$prev_show = true;
		$prev_page = $page-1;

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

		if ( isset( $posts ) && 1 <= count( $posts ) ) {
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
	 * @return void
	 */
	public function ajax_delete_feedback_item()
	{
		check_ajax_referer( 'helpful_admin_feedback_nonce' );

		global $wpdb;

		if ( isset( $_REQUEST['feedback_id'] ) ) {
			$feedback_id = absint( $_REQUEST['feedback_id'] );
			$table_name  = $wpdb->prefix . 'helpful_feedback';
			$wpdb->delete( $table_name, [ 'id' => $feedback_id ] );
		}

		wp_die();
	}

	/**
	 * Exports the feedback to a CSV.
	 *
	 * @return void
	 */
	public function ajax_export_feedback()
	{
		check_ajax_referer( 'helpful_admin_feedback_nonce' );

		global $wpdb;

		$table = $wpdb->prefix . 'helpful_feedback';
		$rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );

		$response = [
			'status'  => 'error',
			'file'    => '',
			'message' => esc_html_x( 'File could not be created.', 'failed upload alert', 'helpful' ),
		];

		if ( $rows ) {
			$items = [];

			foreach ( $rows as $row ) :
				$fields = maybe_unserialize( $row->fields  );

				$items[] = [
					'post'      => get_the_title( $row->post_id ),
					'permalink' => get_the_permalink( $row->post_id ),
					'name'      => isset( $fields['name'] ) ? $fields['name'] : '',
					'email'     => isset( $fields['email'] ) ? $fields['email'] : '',
					'message'   => $row->message,
					'pro'       => $row->pro,
					'contra'    => $row->contra,
					'time'      => $row->time,
				];
			endforeach;

			if ( ! empty( $items ) ) {

				$lines   = [];
				$lines[] = array_keys( $items[0] );

				foreach ( $items as $item ) :
					$lines[] = array_values( $item );
				endforeach;
				
				$uploads = wp_upload_dir();		

				if ( ! file_exists( $uploads['basedir'] . '/helpful' ) ) {
					mkdir( $uploads['basedir'] . '/helpful', 0755, true );
				}

				$file_name = '/helpful/feedback.csv';

				if ( file_exists( $uploads['basedir'] . $file_name ) ) {
					unlink( $uploads['basedir'] . $file_name );
				}

				clearstatcache();

				$separator  = ';';
				$separators = [ ';', ',' ];
				$separators = apply_filters( 'helpful_export_separators', $separators );

				$option = get_option( 'helpful_export_separator' );

				if ( $option && in_array( $option, $separators ) ) {
					$separator = esc_html( $option );
				}
				
				$file = fopen( $uploads['basedir'] . $file_name, 'w+' );

				foreach ( $lines as $line ) :
					fputcsv( $file, $line, $separator );
				endforeach;

				fclose( $file );

				$file_name = $uploads['baseurl'] . $file_name;
				
				$response['status'] = 'success';
				$response['file']   = $file_name;
			}
		}

		wp_send_json( $response );
	}
}