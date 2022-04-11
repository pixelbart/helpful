<?php
/**
 * Allows multiple use of Helpful within a post, by providing instances.
 * Still in work in progress.
 *
 * @package Helpful
 * @subpackage Core\Helpers
 * @version 4.4.51
 * @since 4.4.51
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
class Instance {
	/**
	 * Returns the name of the table.
	 *
	 * @global $wpdb
	 *
	 * @param wpdb $wpdb wpdb class/instance.
	 *
	 * @return string
	 */
	public static function get_table_name( $wpdb = null ) {
		if ( is_null( $wpdb ) ) {
			global $wpdb;
		}

		return $wpdb->prefix . 'helpful_instances';
	}

	/**
	 * Stores a Helpful instance in the table.
	 *
	 * @global $wpdb
	 *
	 * @param string $instance_key Key of the instance.
	 * @param int    $post_id Post id of the instance.
	 * @param string $instance_name Name of the instance.
	 *
	 * @return int
	 */
	public static function insert_instance( $instance_key, $post_id, $instance_name ) {
		global $wpdb;

		$table_name = self::get_table_name( $wpdb );

		/* setup table if not exists */
		$transient = 'helpful/database/setup_tables/instances';
		if ( false === get_transient( $transient ) ) {
			Database::table_exists_or_setup( $table_name );
			set_transient( $transient, time(), WEEK_IN_SECONDS );
		}

		$instance = self::get_instance_by( 'instance_key', $instance_key, $wpdb );

		if ( $instance ) {
			return $instance->id;
		}

		$values = array(
			'instance_key'  => sanitize_text_field( $instance_key ),
			'post_id'       => filter_var( $post_id, FILTER_SANITIZE_NUMBER_INT ),
			'created'       => date_i18n( 'Y-m-d H:i:s' ),
			'instance_name' => sanitize_text_field( $instance_name ),
		);

		$wpdb->insert( $table_name, $values );

		return ( $wpdb->insert_id ) ? $wpdb->insert_id : 0;
	}

	/**
	 * Returns the associated instance while searching for a specific key and value.
	 *
	 * @global $wpdb
	 *
	 * @param string $by Key to search by.
	 * @param mixed  $value Value for the key.
	 * @param wpdb   $wpdb wpdb class/instance.
	 *
	 * @return object
	 */
	public static function get_instance_by( string $by, $value, $wpdb = null ) {
		if ( is_null( $wpdb ) ) {
			global $wpdb;
		}

		$table_name = self::get_table_name( $wpdb );

		if ( is_numeric( $value ) ) {
			$value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
		} else {
			$value = sanitize_text_field( $value );
		}

		$sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE $by = %s LIMIT 1", $value );

		$cache_name = 'helpful/' . md5( $sql );
		$row        = wp_cache_get( $cache_name );

		if ( false === $row ) {
			$row = $wpdb->get_row( $sql );
			wp_cache_set( $cache_name, $row );
		}

		return $row;
	}
}
