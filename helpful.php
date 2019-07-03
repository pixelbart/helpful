<?php
/**
 * Plugin Name: Helpful
 * Description: Add a fancy feedback form under your posts or post-types and ask your visitors a question. Give them the abbility to vote with yes or no.
 * Version: 4.0.10
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

define( 'HELPFUL_FILE', __FILE__ );
define( 'HELPFUL_PATH', plugin_dir_path( HELPFUL_FILE ) );
define( 'HELPFUL_VERSION', '4.0.10' );
define( 'HELPFUL_PHP_MIN', '5.4.0' );

/* Include config */
require_once plugin_dir_path( HELPFUL_FILE ) . 'config.php';

/* Set custom timezone if set in the options */
if ( get_option( 'helpful_timezone' ) ) {
	$timezone = get_option( 'helpful_timezone' );
	date_default_timezone_set( $timezone );
}

/* Include classes and functions */
require_once plugin_dir_path( HELPFUL_FILE ) . 'core/autoload.php';
