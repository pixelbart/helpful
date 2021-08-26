<?php
/**
 * @package Helpful
 * @version 4.4.50
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
	 * @see core/helpers/class-stats.php
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
	 * @see core/helpers/class-stats.php
	 */
	function helpful_get_contra( $post_id = null ) {
		return Helpers\Stats::get_contra( $post_id );
	}
}

if ( ! function_exists( 'helpful_get_pro_all' ) ) {

	/**
	 * Get pro total
	 *
	 * @see core/helpers/class-stats.php
	 */
	function helpful_get_pro_all() {
		return Helpers\Stats::get_pro_all();
	}
}

if ( ! function_exists( 'helpful_get_contra_all' ) ) {

	/**
	 * Get contra total
	 *
	 * @see core/helpers/class-stats.php
	 */
	function helpful_get_contra_all() {
		return Helpers\Stats::get_contra_all();
	}
}

if ( ! function_exists( 'helpful_get_most_helpful' ) ) {

	/**
	 * Get most helpful posts.
	 *
	 * @see core/helpers/class-stats.php
	 *
	 * @param int $limit posts per page.
	 * @param string|array $post_type
	 *
	 * @return array
	 */
	function helpful_get_most_helpful($limit = null, $post_type = null) {
		$items = Helpers\Stats::get_most_helpful($limit, $post_type);

		if ( isset($items[0]['pro'])) {
			usort( $items, function( $a, $b ) {
				return $b['pro'] - $a['pro'];
			});
		}

		return $items;
	}
}

if ( ! function_exists( 'helpful_get_most_helpful' ) ) {

	/**
	 * Get least helpful posts.
	 *
	 * @see core/helpers/class-stats.php
	 *
	 * @param int $limit posts per page.
	 * @param string|array $post_type
	 *
	 * @return array
	 */
	function helpful_get_least_helpful($limit = null, $post_type = null) {
		$items = Helpers\Stats::get_least_helpful($limit, $post_type);

		if ( isset($items[0]['contra'])) {
			usort( $items, function( $a, $b ) {
				return $b['contra'] - $a['contra'];
			});
		}

		return $items;
	}
}
