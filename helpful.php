<?php
/**
 * Plugin Name: Helpful
 * Description: Add a fancy feedback form under your posts or post-types and ask your visitors a question. Give them the abbility to vote with yes or no.
 * Version: 3.0.11
 * Author: Pixelbart
 * Author URI: https://pixelbart.de
 * Text Domain: helpful
 * License: MIT License
 * License URI: https://opensource.org/licenses/MIT
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;

// define file path
define( 'HELPFUL_FILE', __FILE__ );

// define version
define( 'HELPFUL_VERSION', '3.0.11' );

// define min php version
define( 'HELPFUL_PHP_MIN', '5.4.0' );

// Include config
include_once plugin_dir_path( HELPFUL_FILE ) . "config.php";

// get timezone
if( get_option('helpful_timezone') ) {
  $timezone = get_option('helpful_timezone');
  date_default_timezone_set($timezone);
}

// is helpful (backend)
function is_helpful() {
	$screen = get_current_screen();
	return ( $screen->base  == 'settings_page_helpful' ? true : false );
}

// helpful blacklist check
// ref: https://developer.wordpress.org/reference/functions/wp_blacklist_check/
function helpful_backlist_check($content) {
  $mod_keys = trim( get_option( 'blacklist_keys' ) );
  if ( '' == $mod_keys ) return false;
  $without_html = wp_strip_all_tags( $content );
  $words = explode( "\n", $mod_keys );
  foreach( (array) $words as $word ) {
    $word = trim( $word );
    if ( empty( $word ) ) continue;
    $word = preg_quote( $word, '#' );
    $pattern = "#$word#i";
    if ( preg_match( $pattern, $content )
        || preg_match( $pattern, $without_html ) ) return true;
  }
  return false;
}

// Include functions
foreach ( glob( plugin_dir_path( HELPFUL_FILE ) . "core/*.class.php" ) as $file ) {
  include_once $file;
}
