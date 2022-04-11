<?php
/**
 * Returns the values to votes.
 *
 * @package Helpful
 * @subpackage Core\Helpers
 * @version 4.4.50
 * @since 4.3.0
 */

namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Votes {
	/**
	 * Returns all votes from the database.
	 *
	 * @global $wpdb
	 *
	 * @param string $type output type of the results query.
	 *
	 * @return array
	 */
	public static function get_votes( $type = OBJECT ) {
		global $wpdb;

		$cache_name = 'Helpful/Votes/get_votes';
		$results    = wp_cache_get( $cache_name );

		if ( false === $results ) {
			$table_name = $wpdb->prefix . 'helpful';
			$sql        = "SELECT * FROM $table_name";
			$results    = $wpdb->get_results( $sql, $type );
			wp_cache_set( $cache_name, $results );
		}

		return $results;
	}

	/**
	 * Returns a single vote.
	 *
	 * @global $wpdb
	 *
	 * @param int    $vote_id vote id from database.
	 * @param string $type output type of the results query.
	 *
	 * @return array
	 */
	public static function get_vote( $vote_id, $type = OBJECT ) {
		global $wpdb;

		$cache_name = 'Helpful/Votes/get_vote/' . $vote_id;
		$results    = wp_cache_get( $cache_name );

		if ( false === $results ) {
			$table_name = $wpdb->prefix . 'helpful';
			$sql        = "SELECT * FROM $table_name WHERE id = %d LIMIT 1";
			$sql        = $wpdb->prepare( $sql, $vote_id );
			$results    = $wpdb->get_row( $sql, $type );
			wp_cache_set( $cache_name, $results );
		}

		return $results;
	}

	/**
	 * Delete a vote item by id from database.
	 *
	 * @global $wpdb
	 *
	 * @param int $id vote id.
	 *
	 * @return int|false
	 */
	public static function delete_vote( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';
		$status     = $wpdb->delete( $table_name, array( 'id' => $id ) );

		if ( false !== $status ) {
			Optimize::clear_cache();
		}

		return $status;
	}

	/**
	 * Delete a vote item by mixed from database.
	 *
	 * @global $wpdb
	 *
	 * @param array $where array with data for the where clause.
	 *
	 * @return int|false
	 */
	public static function delete_vote_where( $where ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';
		$status     = $wpdb->delete( $table_name, $where );

		if ( false !== $status ) {
			Optimize::clear_cache();
		}

		return $status;
	}

	/**
	 * Insert vote for single user on single post.
	 *
	 * @param string $user user string.
	 * @param int    $post_id post id.
	 * @param string $type type of vote (pro, contra).
	 * @param string $instance instance id.
	 *
	 * @return int|false
	 */
	public static function insert_vote( $user, $post_id, $type = 'pro', $instance = null ) {
		global $wpdb;

		$pro    = 1;
		$contra = 0;

		if ( 'contra' === $type ) {
			$pro    = 0;
			$contra = 1;
		}

		$data = array(
			'time'        => current_time( 'mysql' ),
			'user'        => esc_attr( $user ),
			'pro'         => $pro,
			'contra'      => $contra,
			'post_id'     => absint( $post_id ),
			'instance_id' => $instance,
		);

		$table_name = $wpdb->prefix . 'helpful';

		$status = $wpdb->insert( $table_name, $data );

		if ( false !== $status ) {
			update_post_meta( $post_id, 'helpful-contra', Stats::get_contra( $post_id ) );
			Optimize::clear_cache();
			return $wpdb->insert_id;
		}

		return false;
	}
}
