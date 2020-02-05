<?php
/**
 * Admin tab for log.
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

class Helpful_Tabs_Log extends Helpful_Tabs {

	/**
	 * Class instance
	 *
	 * @var Helpful_Tabs_Log
	 */
	public static $instance;

	/**
	 * Stores tab data
	 *
	 * @var array
	 */
	public $tab_info;

	/**
	 * Stores tab content
	 *
	 * @var array
	 */
	public $tab_content;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->setup_tab();

		add_filter( 'helpful_admin_tabs', [ &$this, 'register_tab' ] );
		add_action( 'helpful_tabs_content', [ &$this, 'add_tab_content' ] );

		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );

		add_action( 'wp_ajax_helpful_get_log_data', [ &$this, 'ajax_get_log_data' ] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Tabs_Log
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add tab to helpful admin menu
	 *
	 * @return void
	 */
	public function setup_tab()
	{
		$this->tab_info   = [
			'id'   => 'log',
			'name' => esc_html_x( 'Log', 'tab name', 'helpful' ),
		];
		$this->tab_content = [ &$this, 'render_callback' ];
	}

	/**
	 * Include options page
	 *
	 * @return void
	 */
	public function render_callback()
	{
		include_once HELPFUL_PATH . 'core/tabs/tab-log.php';
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts()
	{
		$screen = get_current_screen();

		if ( 'toplevel_page_helpful' !== $screen->base ) {
			return;
		}

		if ( ! isset( $_GET['tab'] ) || 'log' !== $_GET['tab'] ) {
			return;
		}

		$file = plugins_url( 'core/assets/js/admin-log.js', HELPFUL_FILE );
		wp_enqueue_script( 'helpful-admin-log', $file, [], HELPFUL_VERSION, true );

		$language = apply_filters( 'helpful_datatables_language', '' );

		$vars = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'helpful_admin_log' ),
			'language' => [ 'url' => $language ],
		];

		wp_localize_script( 'helpful-admin-log', 'helpful_admin_log', $vars );
	}

	/**
	 * Loads the data for the Datatable via Ajax.
	 *
	 * @return void
	 */
	public function ajax_get_log_data()
	{
		check_ajax_referer( 'helpful_admin_log' );

		$response = [];

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';

		$sql = "SELECT * FROM $table_name";

		$rows = $wpdb->get_results( $sql );

		if ( $rows ) :
			foreach ( $rows as $row ) :
				$post = get_post( $row->post_id );

				$title  = $post->post_title;
				$length = apply_filters( 'helpful_datatables_string_length', 35 );

				if ( strlen( $title ) > $length ) {
					$title = substr( $title, 0, $length ) . '...';
				}

				$response['data'][] = [
					'post_id' => $post->ID,
					'post_title' => sprintf(
						'<a href="%1$s" title="%2$s" target="_blank">%2$s</a>',
						esc_url( get_the_permalink( $post->ID ) ),
						esc_html( $title )
					),
					'pro'    => $row->pro,
					'contra' => $row->contra,
					'user'   => $row->user ? 1 : 0,
					'time'   => [
						'display'   => date_i18n( 'Y-m-d H:i:s', strtotime( $row->time ) ),
						'timestamp' => date_i18n( 'U', strtotime( $row->time ) ),
					],
				];
			endforeach;
		endif;

		header( 'Content-Type: application/json' );
		echo wp_json_encode( $response );
		wp_die();
	}
}
