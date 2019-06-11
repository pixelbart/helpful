<?php
/**
 * Plugin Name: Helpful
 * Description: Add a fancy feedback form under your posts or post-types and ask your visitors a question. Give them the abbility to vote with yes or no.
 * Version: 4.0.0
 * Author: Pixelbart
 * Author URI: https://pixelbart.de
 * Text Domain: helpful
 * License: MIT License
 * License URI: https://opensource.org/licenses/MIT
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HELPFUL_FILE', __FILE__ );
define( 'HELPFUL_PATH', plugin_dir_path( HELPFUL_FILE ) );
define( 'HELPFUL_VERSION', '4.0.0' );
define( 'HELPFUL_PHP_MIN', '5.4.0' );

// Include config
include_once plugin_dir_path( HELPFUL_FILE ) . "config.php";

// get timezone
if( get_option('helpful_timezone') ) {
  $timezone = get_option('helpful_timezone');
  date_default_timezone_set($timezone);
}


// remove stored value after plugin activation
register_activation_hook( __FILE__, 'update_helpful' );
function update_helpful() {
  delete_transient('helpful_updated');
}

// Include classes and functions
include_once plugin_dir_path( HELPFUL_FILE ) . "core/autoload.php";