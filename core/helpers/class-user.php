<?php
/**
 * ...
 *
 * @package Helpful\Core\Helpers
 * @author  Pixelbart <me@pixelbart.de>
 * @version 4.3.0
 */
namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class User
{
	/**
	 * Get user string
	 *
	 * @return string|null
	 */
	public static function get_user()
	{
		$user = null;

		/**
		 * No more user is set using sessions or cookies.
		 */
		if ( 'on' === get_option( 'helpful_user_random' ) ) {
			return self::get_user_string();
		}

		if ( isset( $_COOKIE['helpful_user'] ) ) {
			$user = sanitize_text_field( $_COOKIE['helpful_user'] );
		}

		if ( isset( $_SESSION['helpful_user'] ) ) {
			$user = sanitize_text_field( $_SESSION['helpful_user'] );
		}

		if ( null === $user ) {
			self::set_user();
			$user = self::get_user();
		}

		return $user;
	}

	/**
	 * Returns a string that should identify the user.
	 *
	 * @return string
	 */
	public static function get_user_string()
	{
		$length  = apply_filters( 'helpful_user_bytes', 16 );

		if ( function_exists( '') ) {
			$bytes = random_bytes( $length );
		} else {
			$bytes = openssl_random_pseudo_bytes( $length );
		}

		$string = bin2hex( $bytes );

		return apply_filters( 'helpful_user_string', $string );
	}

	/**
	 * Set user string
	 *
	 * @return void
	 */
	public static function set_user()
	{
		$string   = self::get_user_string();
		$lifetime = '+30 days';
		$lifetime = apply_filters( 'helpful_user_cookie_time', $lifetime );
		$samesite = get_option( 'helpful_cookies_samesite' ) ?: 'Strict';

		/**
		 * No more user is set using sessions or cookies.
		 */
		if ( 'on' === get_option( 'helpful_user_random' ) ) {
			return;
		}

		if ( ! defined( 'PHP_VERSION_ID' ) ) {
			$version = explode( '.', PHP_VERSION );		
			define( 'PHP_VERSION_ID', ( $version[0] * 10000 + $version[1] * 100 + $version[2] ) );
		}

		if ( ! isset( $_COOKIE['helpful_user'] ) ) {
			if ( 70300 <= PHP_VERSION_ID ) {

				if ( ! in_array( $samesite, Helper::get_samesite_options() ) ) {
					$samesite = 'Strict';
				}

				$cookie_options = [
					'expires'  => strtotime( $lifetime ),
					'path'     => '/',
					'secure'   => true,
					'httponly' => true,
					'samesite' => $samesite,
				];

				setcookie( 'helpful_user', $string, $cookie_options );
			}
		
			if ( 70300 > PHP_VERSION_ID ) {
				setcookie( 'helpful_user', $string, strtotime( $lifetime ), '/' );
			}
		}

		$session_start     = apply_filters( 'helpful_session_start', true );
		$sessions_disabled = get_option( 'helpful_sessions_false' );

		if ( ! is_bool( $session_start ) ) {
			$session_start = true;
		}

		if ( 'on' !== $sessions_disabled && ! isset( $_COOKIE['helpful_user'] ) ) {
			if ( function_exists( 'session_status' ) && PHP_SESSION_NONE == session_status() && true === $session_start ) {
				session_cache_limiter( '' );
				header( "Cache-Control: public, s-maxage=60" );
				session_start();
			}

			if ( ! isset( $_SESSION['helpful_user'] ) ) {
				$_SESSION['helpful_user'] = $string;
			}
		}
	}

	/**
	 * Check if user has voted on given post.
	 *
	 * @global $wpdb
	 *
	 * @param string $user_id user id.
	 * @param int    $post_id post id.
	 *
	 * @return bool
	 */
	public static function check_user( $user_id, $post_id )
	{
		if ( get_option( 'helpful_multiple' ) ) {
			return false;
		}

		if ( 'on' === get_option( 'helpful_user_random' ) ) {
			return false;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'helpful';
		$sql        = "
		SELECT user, post_id
		FROM {$table_name}
		WHERE user = %s AND post_id = %d
		ORDER BY id DESC
		LIMIT 1
		";
		$query      = $wpdb->prepare( $sql, $user_id, $post_id );
		$results    = $wpdb->get_results( $query );

		if ( $results ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks by a Post-ID whether a vote has already been taken for this post.
	 *
	 * @global $post
	 *
	 * @param int|null $post_id
	 * @param bool     $bool Returns the vote status (pro, contra, none) if true.
	 *
	 * @return bool|string
	 */
	public static function has_user_voted( $post_id = null, $bool = true )
	{
		if ( null === $post_id ) {
			global $post;
			$post_id = $post->ID;
		}

		$user_id = self::get_user();

		if ( true !== $bool ) {
			return self::get_user_vote_status( $user_id, $post_id );
		}

		if ( self::check_user( $user_id, $post_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the vote status for a user and a post. Can be pro, contra and none.
	 *
	 * @global $wpdb
	 * 
	 * @param string $user_id
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public static function get_user_vote_status( $user_id, $post_id )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';
		$sql        = "
		SELECT pro, contra
		FROM {$table_name}
		WHERE user = %s AND post_id = %d
		ORDER BY id DESC
		LIMIT 1
		";
		$query      = $wpdb->prepare( $sql, $user_id, $post_id );
		$results    = $wpdb->get_row( $query );

		if ( ! $results ) {
			return 'none';
		}

		if ( 1 === intval( $results->pro ) ) {
			return 'pro';
		} else if ( 1 === intval( $results->contra ) ) {
			return 'contra';
		}

		return 'none';
	}

	/**
	 * Get avatar or default helpful avatar by email.
	 *
	 * @param string  $email user email.
	 * @param integer $size  image size.
	 *
	 * @return string
	 */
	public static function get_avatar( $email = null, $size = 55 )
	{
		$default = plugins_url( 'core/assets/images/avatar.jpg', HELPFUL_FILE );

		if ( get_option( 'helpful_feedback_gravatar' ) ) {
			if ( ! is_null( $email ) ) {
				return get_avatar( $email, $size, $default );
			}
		}

		$html = '<img src="%1$s" height="%2$s" width="%2$s" alt="no avatar">';
		$html = apply_filters( 'helpful_feedback_noavatar', $html );

		return sprintf( $html, $default, $size );
	}
}