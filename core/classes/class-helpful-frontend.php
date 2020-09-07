<?php
/**
 * Class for the helpful frontend and feedback
 * formular, enqueues styles and scripts.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpful_Frontend
{
	/**
	 * Instance
	 *
	 * @var Helpful_Frontend
	 */
	public static $instance;

	/**
	 * Class Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action( 'wp', [ &$this, 'set_user_cookie' ], 1 );
		add_filter( 'helpful_themes', [ &$this, 'default_themes' ], 1 );
		add_action( 'wp_enqueue_scripts', [ &$this, 'enqueue_scripts' ], PHP_INT_MAX );
		add_action( 'wp_ajax_helpful_save_vote', [ &$this, 'save_vote' ] );
		add_action( 'wp_ajax_nopriv_helpful_save_vote', [ &$this, 'save_vote' ] );
		add_action( 'wp_ajax_helpful_save_feedback', [ &$this, 'save_feedback' ] );
		add_action( 'wp_ajax_nopriv_helpful_save_feedback', [ &$this, 'save_feedback' ] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Frontend
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Set users cookie with unique id
	 *
	 * @return void
	 */
	public function set_user_cookie()
	{
		Helpful_Helper_Values::setUser();
	}

	/**
	 * Retrieve default themes
	 *
	 * @param array $themes themes array.
	 *
	 * @return array
	 */
	public function default_themes( $themes )
	{
		$themes[] = [
			'id'         => 'base',
			'label'      => esc_html_x( 'Base', 'theme name', 'helpful' ),
			'stylesheet' => plugins_url( 'core/assets/themes/base.css', HELPFUL_FILE ),
		];

		$themes[] = [
			'id'         => 'dark',
			'label'      => esc_html_x( 'Dark', 'theme name', 'helpful' ),
			'stylesheet' => plugins_url( 'core/assets/themes/dark.css', HELPFUL_FILE ),
		];

		$themes[] = [
			'id'         => 'minimal',
			'label'      => esc_html_x( 'Minimal', 'theme name', 'helpful' ),
			'stylesheet' => plugins_url( 'core/assets/themes/minimal.css', HELPFUL_FILE ),
		];

		$themes[] = [
			'id'         => 'flat',
			'label'      => esc_html_x( 'Flat', 'theme name', 'helpful' ),
			'stylesheet' => plugins_url( 'core/assets/themes/flat.css', HELPFUL_FILE ),
		];

		$themes[] = [
			'id'         => 'simple',
			'label'      => esc_html_x( 'Simple', 'theme name', 'helpful' ),
			'stylesheet' => plugins_url( 'core/assets/themes/simple.css', HELPFUL_FILE ),
		];

		$themes[] = [
			'id'         => 'clean',
			'label'      => esc_html_x( 'Clean', 'theme name', 'helpful' ),
			'stylesheet' => plugins_url( 'core/assets/themes/clean.css', HELPFUL_FILE ),
		];

		$themes[] = [
			'id'         => 'blank',
			'label'      => esc_html_x( 'Blank', 'theme name', 'helpful' ),
			'stylesheet' => null,
		];

		return $themes;
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts()
	{
		if ( helpful_is_amp() ) {
			return __return_empty_string();
		}

		$active_theme = get_option( 'helpful_theme' );
		$themes       = apply_filters( 'helpful_themes', false );

		foreach ( $themes as $theme ) {
			if ( $active_theme !== $theme['id'] ) {
				continue;
			}

			if ( 'blank' === $theme['id'] ) {
				break;
			}

			wp_enqueue_style( 'helpful-theme-' . $theme['id'], $theme['stylesheet'], [], HELPFUL_VERSION );
		}

		$file = plugins_url( 'core/assets/js/helpful.js', HELPFUL_FILE );
		wp_enqueue_script( 'helpful', $file, [ 'jquery' ], HELPFUL_VERSION, true );

		$user  = Helpful_Helper_Values::getUser();
		$nonce = wp_create_nonce( 'helpful_frontend_nonce' );
		$vars  = [
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'ajax_data' => [
				'user_id'  => $user,
				'_wpnonce' => $nonce,
			],
		];

		if ( isset( $_SESSION ) ) {
			$vars['ajax_session'] = apply_filters( 'helpful_ajax_session', $_SESSION );
		}

		wp_localize_script( 'helpful', 'helpful', $vars );
	}

	/**
	 * Ajax save user vote and render response.
	 *
	 * @return void
	 */
	public function save_vote()
	{
		check_ajax_referer( 'helpful_frontend_nonce' );

		do_action( 'helpful_ajax_save_vote' );

		$user_id = sanitize_text_field( $_POST['user_id'] );
		$post_id = intval( $_POST['post'] );
		$value   = sanitize_text_field( $_POST['value'] );

		if ( ! Helpful_Helper_Values::checkUser( $user_id, $post_id ) ) {
			if ( 'pro' === $value ) {
				Helpful_Helper_Values::insertPro( $user_id, $post_id );
				$response = do_shortcode( Helpful_Helper_Feedback::after_vote( $post_id ) );
			} else {
				Helpful_Helper_Values::insertContra( $user_id, $post_id );
				$response = do_shortcode( Helpful_Helper_Feedback::after_vote( $post_id ) );
			}
		}

		echo $response;
		wp_die();
	}

	/**
	 * Ajax save user feedback and render response.
	 *
	 * @return void
	 */
	public function save_feedback()
	{
		check_ajax_referer( 'helpful_feedback_nonce' );

		do_action( 'helpful_ajax_save_feedback' );

		/**
		 * Simple Spam Protection
		 */
		$spam_protection = apply_filters( 'helpful_simple_spam_protection', true );

		if ( ! is_bool( $spam_protection ) ) {
			$spam_protection = true;
		}

		if ( ! empty( $_REQUEST['website'] ) && true === $spam_protection ) {
			echo do_shortcode( get_option( 'helpful_feedback_message_spam' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['helpful_cancel'] ) ) {
			Helpful_Helper_Feedback::insertFeedback();
		}

		$type = 'pro';

		if ( isset( $_REQUEST['type'] ) ) {
			$type = sanitize_text_field( $_REQUEST['type'] );
		}

		if ( 'pro' === $type ) {
			echo do_shortcode( get_option( 'helpful_after_pro' ) );
		}

		if ( 'contra' === $type ) {
			echo do_shortcode( get_option( 'helpful_after_contra' ) );
		}

		wp_die();
	}
}
