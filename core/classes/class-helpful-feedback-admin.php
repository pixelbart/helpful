<?php
/**
 * Class for feedback admin, ajax actions and adding submenu.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpful_Feedback_Admin
{
	/**
	 * Instance
	 *
	 * @var Helpful_Feedback_Admin
	 */
	public static $instance;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action( 'admin_menu', [ &$this, 'add_submenu' ] );
		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_helpful_admin_feedback_items', [ &$this, 'ajax_get_feedback_items' ] );
		add_action( 'wp_ajax_helpful_remove_feedback', [ &$this, 'ajax_delete_feedback_item' ] );
		add_action( 'wp_ajax_helpful_export_feedback', [ &$this, 'ajax_export_feedback' ] );
	}

	/**
	 * Class instance.
	 *
	 * @return Helpful_Feedback_Admin
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
	 * @return void
	 */
	public function add_submenu()
	{
		add_submenu_page(
			'helpful',
			__( 'Helpful Feedback', 'helpful' ),
			__( 'Feedback', 'helpful' ),
			'publish_posts',
			'helpful_feedback',
			[ $this, 'admin_page_callback' ]
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
	 * @return void
	 */
	public function enqueue_scripts()
	{
		$screen = get_current_screen();

		if ( 'helpful_page_helpful_feedback' === $screen->base ) {

			$file = plugins_url( 'core/assets/css/admin-feedback.css', HELPFUL_FILE );
			wp_enqueue_style( 'helpful-admin-feedback', $file, [], HELPFUL_VERSION );

			$file = plugins_url( 'core/assets/js/admin-feedback.js', HELPFUL_FILE );
			wp_enqueue_script( 'helpful-admin-feedback', $file, [], HELPFUL_VERSION, true );

			$vars = [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'helpful_admin_feedback_nonce' ),
			];

			wp_localize_script( 'helpful-admin-feedback', 'helpful_admin_feedback', $vars );
		}
	}

	/**
	 * Ajax get feedback items
	 *
	 * @return void
	 */
	public function ajax_get_feedback_items()
	{
		check_ajax_referer( 'helpful_admin_feedback_nonce' );

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful_feedback';
		$filters    = [ 'all', 'pro', 'contra' ];
		$sql        = "SELECT * FROM $table_name";

		if ( isset( $_REQUEST['filter'] ) && in_array( $_REQUEST['filter'], $filters ) ) {
			if ( 'pro' == $_REQUEST['filter'] ) {
				$sql = $sql . ' WHERE pro = 1';
			}

			if ( 'contra' == $_REQUEST['filter'] ) {
				$sql = $sql . ' WHERE contra = 1';
			}
		}

		$sql = $sql . ' ORDER BY time DESC';

		$posts = $wpdb->get_results( $sql );

		if ( isset( $posts ) && 1 <= count( $posts ) ) {
			foreach ( $posts as $post ) {
				$feedback = Helpful_Helper_Feedback::getFeedback( $post );
				$this->render_template( $feedback );
			}
		} else {
			esc_html_e( 'No entries found.', 'helpful' );
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
	 * Render template for feedback item.
	 *
	 * @param array $feedback feedback content
	 *
	 * @return void
	 */
	public function render_template( $feedback )
	{
		include HELPFUL_PATH . 'templates/admin-feedback-item.php';
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
					'name'      => $fields['name'],
					'email'     => $fields['email'],
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

		header('Content-Type: application/json');
		echo wp_json_encode( $response );
		wp_die();
	}
}
