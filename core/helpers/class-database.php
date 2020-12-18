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

class Database
{
	/**
	 * Checks whether a database table exists.
	 *
	 * @global $wpdb
	 *
	 * @param string $table_name
	 *
	 * @return bool
	 */
	public static function table_exists( $table_name )
	{
		global $wpdb;

		if ( $table_name != $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if tables exists and creates tables if not
	 *
	 * @global $wpdb
	 *
	 * @param string $table_name database table name.
	 *
	 * @return array
	 */
	public static function table_exists_or_setup( $table_name )
	{
		global $wpdb;

		$response = [];
		$table    = $wpdb->base_prefix . $table_name;
		$query    = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) );

		if ( ! $wpdb->get_var( $query ) == $table_name ) {

			if ( self::$table_feedback == $table_name ) {
				$response[] = self::setup_feedback_table();
			}

			if ( self::$table_helpful == $table_name ) {
				$response[] = self::setup_helpful_table();
			}
		}

		return $response;
	}

	/**
	 * Create database table for helpful
	 *
	 * @global $wpdb
	 *
	 * @return bool
	 */
	public static function setup_helpful_table()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';

		if ( false !== self::table_exists( $table_name ) ) {
			return false;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id bigint(20) DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return true;
	}

	/**
	 * Create database table for feedback
	 *
	 * @global $wpdb
	 *
	 * @return bool
	 */
	public static function setup_feedback_table()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful_feedback';

		if ( false !== self::table_exists( $table_name ) ) {
			return false;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT, 
		time datetime DEFAULT '0000-00-00 00:00:00', 
		user varchar(55) DEFAULT NULL, 
		pro mediumint(1) DEFAULT NULL, 
		contra mediumint(1) DEFAULT NULL, 
		post_id bigint(20) DEFAULT NULL, 
		message text DEFAULT NULL, 
		fields text DEFAULT NULL, 
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return true;
	}

	/**
	 * Updates database tables.
	 *
	 * @global $wpdb
	 *
	 * @return void
	 */
	public static function update_tables()
	{
		if ( get_option( 'helpful_update_table_integer' ) ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';

		$columns = [
			'id'      => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'post_id' => 'bigint(20) DEFAULT NULL',
		];

		foreach ( $columns as $column => $type ) {
			$sql = "ALTER TABLE $table_name MODIFY $column $type";
			$wpdb->query( $sql );
		}

		$table_name = $wpdb->prefix . 'helpful_feedback';

		foreach ( $columns as $column => $type ) {
			$sql = "ALTER TABLE $table_name MODIFY $column $type";
			$wpdb->query( $sql );
		}

		update_option( 'helpful_update_table_integer', time() );
	}
}