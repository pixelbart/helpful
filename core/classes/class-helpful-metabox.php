<?php
/**
 * Class for display a meta box in post types.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */
class Helpful_Metabox {

	/**
	 * Class instance
	 *
	 * @var $instance
	 */
	public static $instance;

	/**
	 * Class constructor
	 */
	public function __construct() {
		if ( ! get_option( 'helpful_metabox' ) ) {
			return;
		}

		add_action( 'add_meta_boxes', [ $this, 'add_metabox' ] );
		add_action( 'save_post', [ $this, 'save_metabox_data' ] );
		add_action( 'save_post', [ $this, 'save_metabox_data' ], 10, 3 );
	}

	/**
	 * Set instance and fire class.
	 *
	 * @return instance
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add metabox to post types.
	 *
	 * @return void
	 */
	public function add_metabox() {
		$post_types = get_option( 'helpful_post_types' );

		if ( isset( $post_types ) && is_array( $post_types ) ) {
			add_meta_box(
				'helpful-meta-box',
				esc_html_x( 'Helpful', 'meta box name', 'helpful' ),
				[ $this, 'render_metabox' ],
				$post_types
			);
		}
	}

	/**
	 * Render metabox content.
	 *
	 * @global $post
	 *
	 * @return void
	 */
	public function render_metabox() {
		global $post;

		$pro            = Helpful_Helper_Stats::getPro( $post->ID );
		$pro_percent    = Helpful_Helper_Stats::getPro( $post->ID, true );
		$contra         = Helpful_Helper_Stats::getContra( $post->ID );
		$contra_percent = Helpful_Helper_Stats::getContra( $post->ID, true );
		$hide           = get_post_meta( $post->ID, 'helpful_hide_on_post', true );

		wp_nonce_field( 'helpful_remove_data', 'helpful_remove_data_nonce' );
		include HELPFUL_PATH . 'templates/admin-metabox.php';
	}

	/**
	 * Save meta box data.
	 *
	 * @param integer $post_id post id.
	 *
	 * @return void
	 */
	public function save_metabox_data( $post_id ) {
		if ( ! wp_verify_nonce( $_POST['helpful_remove_data_nonce'], 'helpful_remove_data' ) ) {
			return;
		}

		if ( 'yes' === $_POST['helpful_remove_data'] ) {
			Helpful_Helper_Values::removeData( $post_id );
		}

		if ( isset( $_POST['helpful_hide_on_post'] ) ) {
			update_post_meta( $post_id, 'helpful_hide_on_post', 'on' );
		} else {
			update_post_meta( $post_id, 'helpful_hide_on_post', 'off' );
		}
	}
}