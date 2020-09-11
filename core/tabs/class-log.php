<?php
/**
 * ...
 *
 * @package Helpful\Core\Modules
 * @author  Pixelbart <me@pixelbart.de>
 * @version 4.3.0
 */
namespace Helpful\Core\Tabs;

use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Log
{
	/**
	 * Class instance
	 *
	 * @var Log
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Log
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
		add_filter( 'helpful_get_admin_tabs', [ &$this, 'register_tab' ], 10, 2 );
		add_action( 'helpful_tabs_content', [ &$this, 'register_tab_content' ] );
		add_action( 'admin_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_helpful_get_log_data', [ &$this, 'ajax_get_log_data' ] );
		add_action( 'helpful_tab_log_before', [ &$this, 'register_tab_alerts' ] );
	}

	/**
	 * Register tab in tabs list.
	 *
	 * @param array $tabs
	 * @param string $current
	 * 
	 * @return array
	 */
	public function register_tab( $tabs, $current )
	{
		$tabs['log'] = [
			'id'   => 'log',
			'name' => esc_html_x( 'Log', 'tab name', 'helpful' ),
		];

		return $tabs;
	}

	/**
	 * Register tab content.
	 *
	 * @return void
	 */
	public function register_tab_content()
	{
		if ( ! Helper::is_active_tab( 'log' ) ) {
			return;
		}

		$template = HELPFUL_PATH . 'templates/tabs/tab-log.php';
		
		if ( file_exists( $template ) ) {
			include_once $template;
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook_suffix
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix )
	{
		if ( 'toplevel_page_helpful' !== $hook_suffix ) {
			return;
		}

		if ( ! Helper::is_active_tab( 'log' ) ) {
			return;
		}

		$plugin = Helper::get_plugin_data();

		$file = plugins_url( 'core/assets/js/admin-log.js', HELPFUL_FILE );
		wp_enqueue_script( 'helpful-admin-log', $file, [ 'jquery' ], $plugin['Version'], true );

		$language = Helper::datatables_language_string();

		$vars = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'helpful_admin_log' ),
			'language' => $language,
		];

		$vars = apply_filters( 'helpful_logs_ajax_vars', $vars );

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
		
		$rows = Helpers\Votes::get_votes();

		if ( $rows ) :
			foreach ( $rows as $row ) :
				$post = get_post( $row->post_id );

				if ( ! isset( $post->ID ) ) {

					$status = apply_filters( 'helpful_logs_delete_empty_item', '1' );

					if ( 1 === intval( $status ) ) {
						Helpers\Votes::delete_vote( $row->id );
					}
					continue;
				}

				$title  = $post->post_title;
				$length = apply_filters( 'helpful_datatables_string_length', 35 );

				if ( strlen( $title ) > $length ) {
					$title = substr( $title, 0, $length ) . '...';
				}

				if ( '' === $title || 0 === strlen( $title ) ) {
					$title = esc_html_x( 'No title found', 'message if no post title was found', 'helpful' );
				}

				$data = [
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

				$response['data'][] = apply_filters( 'helpful_logs_item', $data );
			endforeach;
		endif;

		wp_send_json( $response );
	}

	/**
	 * Register tab alerts for settings saved and other.
	 *
	 * @return void
	 */
	public function register_tab_alerts()
	{
		if ( isset( $_GET['settings-updated'] ) ) {
			$message = esc_html_x( 'Settings saved.', 'tab alert after save', 'helpful' );
			echo Helper::get_alert( $message, 'success', 1500 );
		}
	}
}
