<?php
/**
 * Provides useful functions in relation to votes that are used internally.
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

class Votes
{
	/**
	 * Returns all votes from the database.
	 * 
	 * @global $wpdb
	 *
	 * @param output_type $type
	 *
	 * @return array
	 */
	public static function get_votes( $type = OBJECT )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';

		$sql = "SELECT * FROM $table_name";

		return $wpdb->get_results( $sql, $type );
	}

	/**
	 * Delete a vote item by id from database.
	 *
	 * @global $wpdb
	 *
	 * @param int $id
	 *
	 * @return int|false
	 */
	public static function delete_vote( $id )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';

		$where = [
			'id' => $id,
		];
		
		$status = $wpdb->delete( $table_name, $where );

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
	 * @param array $where
	 *
	 * @return int|false
	 */
	public static function delete_vote_where( $where )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';
		
		$status = $wpdb->delete( $table_name, $where );

		if ( false !== $status ) {
			Optimize::clear_cache();
		}

		return $status;
	}

	/**
	 * Insert vote for single user on single post.
	 *
	 * @param string $user
	 * @param int $post_id
	 * @param string $type
	 *
	 * @return int|false
	 */
	public static function insert_vote( $user, $post_id, $type = 'pro' )
	{
		global $wpdb;

		$pro    = 1;
		$contra = 0;

		if ( 'contra' === $type ) {
			$pro    = 0;
			$contra = 1;
		}

		$data = [
			'time'    => current_time( 'mysql' ),
			'user'    => esc_attr( $user ),
			'pro'     => $pro,
			'contra'  => $contra,
			'post_id' => absint( $post_id ),
		];

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