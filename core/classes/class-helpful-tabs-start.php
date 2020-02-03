<?php
/**
 * Admin tab for start.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.0.0
 */
class Helpful_Tabs_Start {

	/**
	 * Class instance
	 *
	 * @var Helpful_Tabs_Start
	 */
	public static $instance;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action( 'admin_menu', [ &$this, 'register_menu' ] );
		add_filter( 'helpful_admin_tabs', [ &$this, 'register_tab' ], 1 );
		add_action( 'helpful_tabs_content', [ &$this, 'add_tab_content' ], 1 );

		add_action( 'wp_ajax_helpful_range_stats', [ &$this, 'ajax_get_stats_range' ] );
		add_action( 'wp_ajax_helpful_total_stats', [ &$this, 'ajax_get_stats_total' ] );
		add_action( 'wp_ajax_helpful_get_posts_data', [ &$this, 'ajax_get_posts_data' ] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Tabs_Start
	 */
	public static function get_instance():Helpful_Tabs_Start
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add tab to filter
	 *
	 * @param array $tabs current tabs.
	 *
	 * @return array
	 */
	public function register_tab( $tabs ):array
	{
		$tabs['home'] = [
			'attr'  => ( ! isset( $_GET['tab'] ) ? 'selected' : '' ),
			'class' => ( ! isset( $_GET['tab'] ) ? 'active' : '' ),
			'href'  => admin_url( '/admin.php?page=helpful' ),
			'name'  => 'Start',
		];

		return $tabs;
	}

	/**
	 * Add submenu page in admin (not in use)
	 *
	 * @return void
	 */
	public function register_menu():void
	{
		add_submenu_page(
			'helpful',
			__( 'Settings', 'helpful' ),
			__( 'Settings', 'helpful' ),
			'manage_options',
			'helpful',
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Include admin page
	 *
	 * @return void
	 */
	public function render_admin_page():void
	{
		include_once HELPFUL_PATH . 'templates/admin.php';
	}

	/**
	 * Add content to admin page
	 *
	 * @return void
	 */
	public function add_tab_content():void
	{
		if ( ! isset( $_GET['tab'] ) ) {
			include_once HELPFUL_PATH . 'core/tabs/tab-start.php';
		}
	}

	/**
	 * Get stats by date range
	 *
	 * @see Helpful_Helper_Values::getStatsRange()
	 *
	 * @return void
	 */
	public function ajax_get_stats_range():void
	{
		check_ajax_referer( 'helpful_range_stats' );

		$response         = [];
		$from             = date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $_REQUEST['from'] ) ) );
		$to               = date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $_REQUEST['to'] ) ) );
		$response         = Helpful_Helper_Stats::getStatsRange( $from, $to );
		$response['from'] = $from;
		$response['to']   = $to;

		if ( isset( $_REQUEST['type'] ) && 'default' !== $_REQUEST['type'] ) {
			$response['options']['scales'] = [
				'xAxes' => [
					[ 'stacked' => true ],
				],
				'yAxes' => [
					[ 'stacked' => true ],
				],
			];
		}

		header( 'Content-Type: application/json' );
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Get stats total
	 *
	 * @see Helpful_Helper_Values::getStatsTotal()
	 *
	 * @return void
	 */
	public function ajax_get_stats_total():void
	{
		check_ajax_referer( 'helpful_admin_nonce' );
		$response = Helpful_Helper_Stats::getStatsTotal();
		header( 'Content-Type: application/json' );
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Get posts data
	 */
	public function ajax_get_posts_data():void
	{
		check_ajax_referer( 'helpful_admin_nonce' );

		Helpful_Helper_Values::sync_post_meta();

		$post_types = get_option( 'helpful_post_types' );

		$response = [
			'status' => 'success',
			'data'   => [],
		];

		$args = [
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'meta_query'     => [
				'relation' => 'OR',
				[
					'key'     => 'helpful-contra',
					'value'   => 1,
					'compare' => '>='
				],
				[
					'key'     => 'helpful-pro',
					'value'   => 1,
					'compare' => '>='
				],
			],
		];

		$transient = 'helpful_admin_start_' . md5( serialize( $args ) );

		if ( false === ( $query = get_transient( $transient ) ) ) {
			$query       = new WP_Query( $args );
			$cache_time  = get_option( 'helpful_cache_time', 'minute' );
			$cache_times = Helpful_Helper_Cache::get_cache_times( false );
			$cache_time  = $cache_times[ $cache_time ];

			set_transient( $transient, $query, $cache_time );
		}

		if ( $query->found_posts ) {
			foreach ( $query->posts as $post_id ) :

				$post      = get_post( $post_id );
				$post_type = get_post_type_object( $post->post_type );

				$response['data'][] = [
					'post' => [
						'id'     => $post->ID,
						'title'  => sprintf(
							'<a href="%1&s" title="%2$s" target="_blank">%2$s</a>',
							get_the_permalink( $post->ID ),
							$post->post_title
						),
						'author' => get_the_author_meta( 'display_name', $post->post_author ),
						'date'   => [
							'display'   => get_the_date( 'Y-m-d', $post->ID ),
							'timestamp' => get_the_date( 'U', $post->ID ),
						],
						'type' => [
							'display' => $post_type->labels->singular_name,
							'slug'    => $post_type->name,
						],
					],
					'helpful' => [
						'pro' => sprintf(
							'%s (%s%%)',
							Helpful_Helper_Stats::getPro( $post->ID, false ),
							Helpful_Helper_Stats::getPro( $post->ID, true )
						),
						'contra' => sprintf(
							'%s (%s%%)',
							Helpful_Helper_Stats::getContra( $post->ID, false ),
							Helpful_Helper_Stats::getContra( $post->ID, true )
						),
					],
				];

			endforeach;
		}

		header( 'Content-Type: application/json' );
		echo wp_json_encode( $response );
		wp_die();
	}
}
