<?php
/**
 * 
 * Check if current screen is helpful
 *
 * @return bool
 */
function is_helpful() {
	$screen = get_current_screen();
	return ( 'settings_page_helpful' == $screen->base ? true : false );
}

/**
 *
 * WordPress blacklist checker
 *
 * @param string $content the content to be checked
 * @return bool
 */
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