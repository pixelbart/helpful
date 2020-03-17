<?php
/**
 * Class for setting shortcodes and the_content.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 2.0.0
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpful_Shortcodes
{
	/**
	 * Instance
	 *
	 * @var Helpful_Shortcodes
	 */
	public static $instance;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_filter( 'the_content', [ &$this, 'add_to_content' ] );
		add_shortcode( 'helpful', [ &$this, 'shortcode_helpful' ] );
	}

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Shortcodes
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add helpful to post content
	 *
	 * @global $post
	 *
	 * @param string $content post content.
	 *
	 * @return string
	 */
	public function add_to_content( $content )
	{
		global $post;

		$post_types = get_option( 'helpful_post_types' );
		$user_id    = Helpful_Helper_Values::getUser();

		if ( 'on' === get_post_meta( $post->ID, 'helpful_hide_on_post', true ) ) {
			return $content;
		}

		if ( ! is_array( $post_types ) || ! in_array( $post->post_type, $post_types, true ) ) {
			return $content;
		}

		if ( get_option( 'helpful_hide_in_content' ) ) {
			return $content;
		}

		if ( get_option( 'helpful_exists_hide' ) && Helpful_Helper_Values::checkUser( $user_id, $post->ID ) ) {
			return $content;
		}

		if ( ! is_singular() ) {
			return $content;
		}

		$helpful = Helpful_Helper_Values::getDefaults();
		$hidden  = false;
		$class   = '';

		if ( isset( $helpful['exists'] ) && 1 === $helpful['exists'] ) {
			if ( isset( $helpful['exists-hide'] ) && 1 === $helpful['exists-hide'] ) {
				return __return_empty_string();
			}

			$hidden             = true;
			$class              = 'helpful-exists';
			$helpful['content'] = $helpful['exists_text'];
		}

		$helpful['content'] = do_shortcode( $helpful['content'] );

		ob_start();

		$default_template = HELPFUL_PATH . 'templates/helpful.php';
		$custom_template  = locate_template( 'helpful/helpful.php' );

		do_action( 'helpful_before' );

		if ( '' !== $custom_template ) {
			include $custom_template;
		} else {
			include $default_template;
		}

		do_action( 'helpful_after' );

		$content .= ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Callback for helpful shortcode
	 *
	 * @global $post
	 *
	 * @param array  $atts shortcode attributes.
	 * @param string $content shortcode content.
	 *
	 * @return string
	 */
	public function shortcode_helpful( $atts, $content = '' )
	{
		global $post;

		$defaults = Helpful_Helper_Values::getDefaults();
		$defaults = apply_filters( 'helpful_shortcode_defaults', $defaults );

		$user_id = Helpful_Helper_Values::getUser();

		if ( get_option( 'helpful_exists_hide' ) && Helpful_Helper_Values::checkUser( $user_id, $post->ID ) ) {
			return __return_empty_string();
		}

		$helpful = shortcode_atts( $defaults, $atts );
		$helpful = apply_filters( 'helpful_shortcode_atts', $helpful );

		$hidden = false;
		$class  = '';

		if ( isset( $helpful['exists'] ) && 1 === $helpful['exists'] ) {
			if ( isset( $helpful['exists-hide'] ) && 1 === $helpful['exists-hide'] ) {
				return __return_empty_string();
			}

			$hidden             = true;
			$class              = 'helpful-exists';
			$helpful['content'] = $helpful['exists_text'];
		}

		if ( ! isset( $post->ID ) && ! isset( $helpful['post_id'] ) ) {
			if ( false !== get_the_ID() ) {
				$helpful['post_id'] = get_the_ID();
			} else {
				return esc_html__( 'No post found. Helpful must be placed in a post loop.', 'helpful' );
			}
		}

		ob_start();

		$default_template = HELPFUL_PATH . 'templates/helpful.php';
		$custom_template  = locate_template( 'helpful/helpful.php' );

		if ( '' !== $custom_template ) {
			include $custom_template;
		} else {
			include $default_template;
		}

		$content .= ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
