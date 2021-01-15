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

class Frontend
{
	/**
	 * Instance
	 *
	 * @var Frontend
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Frontend
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

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
		add_action( 'wp_ajax_helpful_save_feedback', [ &$this, 'save_feedback' ] );
		add_action( 'wp_ajax_nopriv_helpful_save_vote', [ &$this, 'save_vote' ] );
		add_action( 'wp_ajax_nopriv_helpful_save_feedback', [ &$this, 'save_feedback' ] );

		add_filter( 'the_content', [ &$this, 'the_content' ] );
		add_shortcode( 'helpful', [ &$this, 'helpful' ] );

		add_filter( 'init', [ &$this, 'filter_nonces' ] );
	}

	/**
	 * Set users cookie with unique id
	 *
	 * @return void
	 */
	public function set_user_cookie()
	{
		Helpers\User::set_user();
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
		$themes       = apply_filters( 'helpful_themes', [] );
		$plugin       = Helper::get_plugin_data();

		
		if ( ! empty( $themes ) ) {
			foreach ( $themes as $theme ) {
				if ( $active_theme !== $theme['id'] ) {
					continue;
				}

				if ( 'blank' === $theme['id'] ) {
					break;
				}

				wp_enqueue_style( 'helpful-theme-' . $theme['id'], $theme['stylesheet'], [], $plugin['Version'] );
			}
		}

		$file = Helper::plugins_url( 'core/assets/js/helpful.js' );
		wp_enqueue_script( 'helpful', $file, [ 'jquery' ], $plugin['Version'], true );

		$user  = Helpers\User::get_user();
		$nonce = wp_create_nonce( 'helpful_frontend_nonce' );
		$vars  = [
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'ajax_data' => [
				'user_id'  => $user,
				'_wpnonce' => $nonce,
			],
			'translations' => [
				'fieldIsRequired' => __( 'This field is required.', 'helpful' ),
			],
		];

		if ( isset( $_SESSION ) ) {
			$vars['ajax_session'] = apply_filters( 'helpful_ajax_session', $_SESSION );
		}

		$vars = apply_filters( 'helpful_frontend_ajax_vars', $vars );

		wp_localize_script( 'helpful', 'helpful', $vars );
	}

	/**
	 * Ajax save user vote and render response.
	 *
	 * @return void
	 */
	public function save_vote()
	{
		if ( apply_filters( 'helpful_verify_frontend_nonce', true ) ) {
			check_ajax_referer( 'helpful_frontend_nonce' );
		}

		do_action( 'helpful_ajax_save_vote' );

		$user_id = null;
		$post_id = null;
		$value   = null;

		if ( isset( $_POST['user_id'] ) ) {
			$user_id = sanitize_text_field( $_POST['user_id'] );
		}

		if ( isset( $_POST['post'] ) ) {
			$post_id = intval( $_POST['post'] );
		}

		if ( isset( $_POST['value'] ) ) {
			$value = sanitize_text_field( $_POST['value'] );
		}

		if ( ! Helpers\User::check_user( $user_id, $post_id ) ) {
			if ( 'pro' === $value ) {
				Helpers\Values::insert_pro( $user_id, $post_id );
			} else {
				Helpers\Values::insert_contra( $user_id, $post_id );
			}

			$response = do_shortcode( Helpers\Feedback::after_vote( $post_id ) );
		}

		$response = Helpers\Values::convert_tags( $response, $post_id );
		echo apply_filters( 'helpful_pre_save_vote', $response, $post_id );
		wp_die();
	}

	/**
	 * Ajax save user feedback and render response.
	 *
	 * @return void
	 */
	public function save_feedback()
	{
		if ( apply_filters( 'helpful_verify_feedback_nonce', true ) ) {
			check_ajax_referer( 'helpful_feedback_nonce' );
		}

		do_action( 'helpful_ajax_save_feedback' );

		$post_id = null;

		if ( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) ) {
			$post_id = intval( $_REQUEST['post_id'] );
		}

		/**
		 * Simple Spam Protection
		 */
		$spam_protection = apply_filters( 'helpful_simple_spam_protection', '1' );

		if ( '1' !== $spam_protection ) {
			$spam_protection = false;
		} else {
			$spam_protection = true;
		}

		if ( ! empty( $_REQUEST['website'] ) && true === $spam_protection ) {
			$message = do_shortcode( get_option( 'helpful_feedback_message_spam' ) );
			$message = apply_filters( 'helpful_pre_feedback_message_spam', $message, $post_id );
			echo Helpers\Values::convert_tags( $message, $post_id );
			wp_die();
		}

		if ( ! isset( $_REQUEST['helpful_cancel'] ) ) {
			Helpers\Feedback::insert_feedback();
		}

		$type = 'pro';

		if ( isset( $_REQUEST['type'] ) ) {
			$type = sanitize_text_field( $_REQUEST['type'] );
		}

		if ( 'pro' === $type ) {
			$message = do_shortcode( get_option( 'helpful_after_pro' ) );

			if ( get_post_meta( $post_id, 'helpful_after_pro', true ) ) {
				$message = do_shortcode( get_post_meta( $post_id, 'helpful_after_pro', true ) );
			}

			$message = apply_filters( 'helpful_pre_after_pro', $message, $post_id );
		} elseif ( 'contra' === $type ) {
			$message = do_shortcode( get_option( 'helpful_after_contra' ) );

			if ( get_post_meta( $post_id, 'helpful_after_contra', true ) ) {
				$message = do_shortcode( get_post_meta( $post_id, 'helpful_after_contra', true ) );
			}

			$message = apply_filters( 'helpful_pre_after_contra', $message, $post_id );
		} else {
			$message = do_shortcode( get_option( 'helpful_after_fallback' ) );

			if ( get_post_meta( $post_id, 'helpful_after_fallback', true ) ) {
				$message = do_shortcode( get_post_meta( $post_id, 'helpful_after_fallback', true ) );
			}

			$message = apply_filters( 'helpful_pre_after_fallback', $message, $post_id );
		}

		$message = Helpers\Values::convert_tags( $message, $post_id );
		echo apply_filters( 'helpful_pre_save_feedback', $message, $post_id );
		wp_die();
	}
	
	/**
	 * Add helpful to post content
	 *
	 * @global $post
	 * @version 4.3.0
	 *
	 * @param string $content post content.
	 *
	 * @return string
	 */
	public function the_content( $content )
	{
		global $post;

		if ( helpful_is_amp() ) {
			return $content;
		}

		if ( ! isset( $post->ID ) ) {
			return $content;
		}

		$helpful    = Helpers\Values::get_defaults();
		$post_types = get_option( 'helpful_post_types' );
		$user_id    = Helpers\User::get_user();

		if ( 'on' === get_post_meta( $helpful['post_id'], 'helpful_hide_on_post', true ) ) {
			return $content;
		}

		if ( ! is_array( $post_types ) || ! in_array( $post->post_type, $post_types, true ) ) {
			return $content;
		}

		if ( get_option( 'helpful_hide_in_content' ) ) {
			return $content;
		}

		if ( get_option( 'helpful_exists_hide' ) && Helpers\User::check_user( $user_id, $helpful['post_id'] ) ) {
			return $content;
		}

		$conditions = Helper::get_conditions();

		if ( ! empty( $conditions ) ) {
			return $content;
		}

		$exists  = false;
		$hidden  = false;
		$class   = '';

		if ( isset( $helpful['exists'] ) && 1 === $helpful['exists'] ) {
			if ( isset( $helpful['exists-hide'] ) && 1 === $helpful['exists-hide'] ) {
				return __return_empty_string();
			}

			$exists             = true;
			$hidden             = true;
			$class              = 'helpful-exists';
			$helpful['content'] = do_shortcode( $helpful['exists_text'] );

			if ( get_post_meta( $helpful['post_id'], 'helpful_exists', true ) ) {
				$helpful['content'] = do_shortcode( get_post_meta( $helpful['post_id'], 'helpful_exists', true ) );
			}
		}

		if ( null === $helpful['post_id'] ) {
			return esc_html__( 'No post found. Helpful must be placed in a post loop.', 'helpful' );
		}

		if ( false !== $exists && get_option( 'helpful_feedback_after_vote' ) ) {
			$shortcode = Helpers\Feedback::after_vote( $helpful['post_id'], true );
			$shortcode = Helpers\Values::convert_tags( $shortcode, $helpful['post_id'] );
			return $content . $shortcode;
		}

		$helpful['content'] = do_shortcode( $helpful['content'] );

		if ( get_post_meta( $helpful['post_id'], 'helpful_heading', true ) ) {
			$helpful['heading'] = do_shortcode( get_post_meta( $helpful['post_id'], 'helpful_heading', true ) );
		}

		if ( get_post_meta( $helpful['post_id'], 'helpful_pro', true ) ) {
			$helpful['button_pro'] = do_shortcode( get_post_meta( $helpful['post_id'], 'helpful_pro', true ) );
		}

		if ( get_post_meta( $helpful['post_id'], 'helpful_contra', true ) ) {
			$helpful['button_contra'] = do_shortcode( get_post_meta( $helpful['post_id'], 'helpful_contra', true ) );
		}

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

		$shortcode = ob_get_contents();
		ob_end_clean();

		$shortcode = Helpers\Values::convert_tags( $shortcode, $helpful['post_id'] );

		return $content . $shortcode;
	}

	/**
	 * Callback for helpful shortcode
	 *
	 * @global $post
	 * @version 4.3.0
	 *
	 * @param array  $atts shortcode attributes.
	 * @param string $content shortcode content.
	 *
	 * @return string
	 */
	public function helpful( $atts, $content = '' )
	{
		global $post;

		if ( helpful_is_amp() ) {
			return __return_empty_string();
		}

		$defaults = Helpers\Values::get_defaults();
		$defaults = apply_filters( 'helpful_shortcode_defaults', $defaults );

		$helpful = shortcode_atts( $defaults, $atts );
		$helpful = apply_filters( 'helpful_shortcode_atts', $helpful );

		$user_id = Helpers\User::get_user();

		if ( get_option( 'helpful_exists_hide' ) && Helpers\User::check_user( $user_id, $helpful['post_id'] ) ) {
			return __return_empty_string();
		}

		$exists = false;
		$hidden = false;
		$class  = '';

		if ( isset( $helpful['exists'] ) && 1 === $helpful['exists'] ) {
			if ( isset( $helpful['exists-hide'] ) && 1 === $helpful['exists-hide'] ) {
				return __return_empty_string();
			}

			$exists             = true;
			$hidden             = true;
			$class              = 'helpful-exists';
			$helpful['content'] = $helpful['exists_text'];
		}

		if ( null === $helpful['post_id'] ) {
			return esc_html__( 'No post found. Helpful must be placed in a post loop.', 'helpful' );
		}

		if ( false !== $exists && 'on' === get_option( 'helpful_feedback_after_vote' ) ) {
			if ( ! Helper::is_feedback_disabled() ) {
				$shortcode = Helpers\Feedback::after_vote( $helpful['post_id'], true );
				$shortcode = Helpers\Values::convert_tags( $shortcode, $helpful['post_id'] );
				return $shortcode;
			}
		}

		if ( get_post_meta( $helpful['post_id'], 'helpful_heading', true ) ) {
			$helpful['heading'] = do_shortcode( get_post_meta( $helpful['post_id'], 'helpful_heading', true ) );
		}

		if ( get_post_meta( $helpful['post_id'], 'helpful_pro', true ) ) {
			$helpful['button_pro'] = do_shortcode( get_post_meta( $helpful['post_id'], 'helpful_pro', true ) );
		}

		if ( get_post_meta( $helpful['post_id'], 'helpful_contra', true ) ) {
			$helpful['button_contra'] = do_shortcode( get_post_meta( $helpful['post_id'], 'helpful_contra', true ) );
		}

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

		$shortcode = ob_get_contents();
		ob_end_clean();

		$shortcode = Helpers\Values::convert_tags( $shortcode, $helpful['post_id'] );

		return $content . $shortcode;
	}

	/**
	 * Filters the frontend nonces and set the value to false, using option.
	 *
	 * @return void
	 */
	public function filter_nonces()
	{
		if ( 'on' === get_option( 'helpful_disable_frontend_nonce' ) ) {
			add_filter( 'helpful_verify_frontend_nonce', '__return_false' );
		}

		if ( 'on' === get_option( 'helpful_disable_feedback_nonce' ) ) {
			add_filter( 'helpful_verify_feedback_nonce', '__return_false' );
		}
	}
}