<?php
/**
 * Database tables and everything that has to do with it.
 *
 * @package Helpful
 * @subpackage Core\Helpers
 * @version 4.5.0
 * @since 4.3.0
 */

namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Database {
	/**
	 * Checks whether a database table exists.
	 *
	 * @global $wpdb
	 *
	 * @param string $table_name Table name.
	 *
	 * @return bool
	 */
	public static function table_exists( $table_name ) {
		global $wpdb;

		$cache_name  = 'Helpful/Database/table_exists';
		$table_found = wp_cache_get( $cache_name );

		if ( false === $table_found ) {
			$table_found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
			wp_cache_set( $cache_name, $table_found );
		}

		if ( $table_name !== $table_found ) {
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
	public static function table_exists_or_setup( $table_name ) {
		global $wpdb;

		$response = array();
		$table    = $wpdb->base_prefix . $table_name;
		$query    = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) );

		if ( ! $wpdb->get_var( $query ) === $table_name ) {

			if ( 'helpful_feedback' === $table_name ) {
				$response[] = self::handle_table_feedback();
			}

			if ( 'helpful' === $table_name ) {
				$response[] = self::handle_table_helpful();
			}

			if ( 'instances' === $table_name ) {
				$response[] = self::handle_table_instances();
			}
		}

		return $response;
	}

	/**
	 * Create database table for helpful
	 *
	 * @global $wpdb
	 */
	public static function handle_table_helpful() {
		global $wpdb;

		self::update_tables();

		$table_name      = $wpdb->prefix . 'helpful';
		$queries         = array();
		$charset_collate = $wpdb->get_charset_collate();

		$queries[] = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id bigint(20) DEFAULT NULL,
		instance_id bigint(20) DEFAULT NULL,
		INDEX search  (pro, contra, post_id, instance_id),
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $queries );
	}

	/**
	 * Create database table for feedback
	 *
	 * @global $wpdb
	 */
	public static function handle_table_feedback() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'helpful_feedback';
		$queries         = array();
		$charset_collate = $wpdb->get_charset_collate();

		$queries[] = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id bigint(20) DEFAULT NULL,
		message text DEFAULT NULL,
		fields text DEFAULT NULL,
		instance_id bigint(20) DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $queries );
	}

	/**
	 * Create database table for helpful instances
	 *
	 * @global $wpdb
	 */
	public static function handle_table_instances() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'helpful_instances';
		$queries         = array();
		$charset_collate = $wpdb->get_charset_collate();

		$queries[] = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		instance_key varchar(32) DEFAULT NULL,
		instance_name text DEFAULT NULL,
		post_id bigint(20) DEFAULT NULL,
		created datetime DEFAULT NOW(),
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $queries );
	}

	/**
	 * Updates database tables.
	 *
	 * @global $wpdb
	 * @version 4.5.0
	 *
	 * @return void
	 */
	public static function update_tables() {
		$options = new Services\Options();

		if ( $options->get_option( 'helpful_update_table_integer', false, 'intval' ) ) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';

		$columns = array(
			'id'      => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'post_id' => 'bigint(20) DEFAULT NULL',
		);

		foreach ( $columns as $column => $type ) {
			$wpdb->query( "ALTER TABLE $table_name MODIFY $column $type" );
		}

		$table_name = $wpdb->prefix . 'helpful_feedback';

		foreach ( $columns as $column => $type ) {
			$wpdb->query( "ALTER TABLE $table_name MODIFY $column $type" );
		}

		update_option( 'helpful_update_table_integer', time() );
	}
}
