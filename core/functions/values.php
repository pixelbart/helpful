<?php
/**
 * Helpful Frontend Helpers for retrieving
 * values from database
 *
 * @package helpful
 * @author Pixelbart <me@pixelbart.de>
 * @since 4.0.0
 */
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'helpful_get_pro' ) ) {

	/**
	 * Get pro for single post by post id
	 *
	 * @param integer $post_id post id.
	 *
	 * @see classes/class-helpful-helper-values.php
	 */
	function helpful_get_pro( $post_id = null ) {
		return Helpers\Stats::get_pro( $post_id );
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
		return Helpers\Stats::get_contra( $post_id );
	}
}

if ( ! function_exists( 'helpful_get_pro_all' ) ) {

	/**
	 * Get pro total
	 *
	 * @see classes/class-helpful-helper-values.php
	 */
	function helpful_get_pro_all() {
		return Helpers\Stats::get_pro_all();
	}
}

if ( ! function_exists( 'helpful_get_contra_all' ) ) {

	/**
	 * Get contra total
	 *
	 * @see classes/class-helpful-helper-values.php
	 */
	function helpful_get_contra_all() {
		return Helpers\Stats::get_contra_all();
	}
}
