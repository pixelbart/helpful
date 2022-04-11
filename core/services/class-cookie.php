<?php
/**
 * A service for setting and receiving cookies.
 *
 * @package Helpful
 * @subpackage Core\Services
 * @copyright Copyright (c) 2015, Pippin Williamson
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version 4.4.59
 * @since 4.4.55
 */

namespace Helpful\Core\Services;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Cookie {
	/**
	 * Set a single cookie.
	 *
	 * @param string $key cookie name.
	 * @param mixed  $value cookie value.
	 *
	 * @return void
	 */
	public function set( string $key, $value ) {
		if ( headers_sent() ) {
			return;
		}

		$options  = new Options();
		$lifetime = '+30 days';
		$lifetime = apply_filters( 'helpful_user_cookie_time', $lifetime );

		$sso      = $options->get_option( 'helpful_cookies_samesite', 'Strict', 'esc_attr' );
		$samesite = $sso ? $sso : 'Strict';

		if ( 70300 <= PHP_VERSION_ID ) {

			if ( ! in_array( $samesite, Helper::get_samesite_options(), true ) ) {
				$samesite = 'Strict';
			}

			$cookie_options = array(
				'expires'  => strtotime( $lifetime ),
				'path'     => '/',
				'secure'   => true,
				'httponly' => true,
				'samesite' => $samesite,
			);

			setcookie( $key, $value, $cookie_options );
		}

		if ( 70300 > PHP_VERSION_ID ) {
			setcookie( $key, $value, strtotime( $lifetime ), '/' );
		}

		if ( isset( $_SESSION[ $key ] ) && isset( $_COOKIE[ $key ] ) ) {
			unset( $_SESSION[ $key ] );
		}
	}

	/**
	 * Get a single cookie.
	 *
	 * @param string $key cookie name.
	 *
	 * @return mixed
	 */
	public function get( string $key ) {
		$data = false;

		if ( array_key_exists( $key, $_COOKIE ) && '' !== trim( $_COOKIE[ $key ] ) ) {
			$data = sanitize_text_field( $_COOKIE[ $key ] );
		}

		return $data;
	}
}
