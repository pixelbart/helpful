<?php
/**
 * Helper to check among other things whether
 * a database table exists.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpful_Helper_Database
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
}