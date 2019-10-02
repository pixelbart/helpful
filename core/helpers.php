<?php
/**
 * Helpful helpers loaded before helpful classes.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */

if ( ! function_exists( 'helpful_backlist_check' ) ) {
	/**
	 * WordPress blacklist checker
	 *
	 * @param string $content the content to be checked.
	 *
	 * @return bool
	 */
	function helpful_backlist_check( $content ) {
		$mod_keys = trim( get_option( 'blacklist_keys' ) );

		if ( '' === $mod_keys ) {
			return false;
		}

		$without_html = wp_strip_all_tags( $content );
		$words        = explode( "\n", $mod_keys );

		foreach ( (array) $words as $word ) :
			$word = trim( $word );

			if ( empty( $word ) ) {
				continue;
			}

			$word    = preg_quote( $word, '#' );
			$pattern = "#$word#i";

			if ( preg_match( $pattern, $content ) || preg_match( $pattern, $without_html ) ) {
				return true;
			}
		endforeach;

		return false;
	}
}

if ( ! function_exists( 'helpful_trim_all' ) ) {
	/**
	 * Trim all whitespaces.
	 *
	 * @param string $string string to trim.
	 * @return string
	 */
	function helpful_trim_all( $string ) {
		return preg_replace( '/\s+/', '', $string );
	}
}

if ( ! function_exists( 'helpful_error_log' ) ) {
	/**
	 * This allows custom error messages to be placed in the error_logs.
	 * WP_DEBUG and WP_DEBUG_LOG must be set to true.
	 *
	 * @source https://wordpress.org/support/article/debugging-in-wordpress/
	 * @param string $message error message.
	 */
	function helpful_error_log( $message ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}
}
