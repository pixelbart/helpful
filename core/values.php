<?php
/**
 * Helpful Frontend Helpers for retrieving
 * values from database
 *
 * @package helpful
 * @author Pixelbart <me@pixelbart.de>
 * @since 4.0.0
 */

if ( ! function_exists( 'helpful_get_pro' ) ) {

	/**
	 * Get pro for single post by post id
	 *
	 * @param integer $post_id post id.
	 *
	 * @see classes/class-helpful-helper-values.php
	 */
	function helpful_get_pro( $post_id = null ) {
		return Helpful_Helper_Stats::getPro( $post_id );
	}
}

if ( ! function_exists( 'helpful_get_contra' ) ) {

	/**
	 * Get contra for single post by post id
	 *
	 * @param integer $post_id post id.
	 *
	 * @see classes/class-helpful-helper-values.php
	 */
	function helpful_get_contra( $post_id = null ) {
		return Helpful_Helper_Stats::getContra( $post_id );
	}
}

if ( ! function_exists( 'helpful_get_pro_all' ) ) {

	/**
	 * Get pro total
	 *
	 * @see classes/class-helpful-helper-values.php
	 */
	function helpful_get_pro_all() {
		return Helpful_Helper_Stats::getProAll();
	}
}

if ( ! function_exists( 'helpful_get_contra_all' ) ) {

	/**
	 * Get contra total
	 *
	 * @see classes/class-helpful-helper-values.php
	 */
	function helpful_get_contra_all() {
		return Helpful_Helper_Stats::getContraAll();
	}
}
