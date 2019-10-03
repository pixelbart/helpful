<?php
/**
 * Plugin Name: Helpful
 * Description: Add a fancy feedback form under your posts or post-types and ask your visitors a question. Give them the abbility to vote with yes or no.
 * Version: 4.1.1
 * Author: Pixelbart
 * Author URI: https://pixelbart.de
 * Text Domain: helpful
 * License: MIT License
 * License URI: https://opensource.org/licenses/MIT
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$file_data = (object) get_file_data( __FILE__, [ 'Version' => 'version' ] );

define( 'HELPFUL_FILE', __FILE__ );
define( 'HELPFUL_PATH', plugin_dir_path( HELPFUL_FILE ) );
define( 'HELPFUL_VERSION', $file_data->version );
define( 'HELPFUL_PHP_MIN', '5.6.20' );

/* Include config */
require_once HELPFUL_PATH . 'config.php';

/* Set custom timezone if set in the options */
if ( get_option( 'helpful_timezone' ) && '' !== get_option( 'helpful_timezone' ) ) {
	$timezone = get_option( 'helpful_timezone' );
	date_default_timezone_set( $timezone );
}

/* Include classes and functions */
require_once HELPFUL_PATH . 'core/autoload.php';