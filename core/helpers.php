<?php
/**
 * Check if current screen is helpful
 * @return bool
 */
function is_helpful() {
	$screen = get_current_screen();
	return ( 'settings_page_helpful' == $screen->base ? true : false );
}

/**
 * WordPress blacklist checker
 * @param string $content the content to be checked
 * @return bool
 */
function helpful_backlist_check(  $content ) {
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

/**
 * Get avatar by email if gravatar is checked
 * If not receive a placeholder
 * @since 3.2.0
 * @param string $email
 * @param int $size
 * @return string html element with image or dashicon
 */
function helpful_get_avatar( $email = null, $size = 50 ) {
  $html = '<div class="helpful-avatar" height="%1$d" width="%1$d"><i class="dashicons dashicons-admin-users"></i></div>';
  $html = sprintf($html, $size);
  if( !is_null($email) && get_option('helpful_feedback_gravatar') ) {
    $gravatar = get_avatar_url( $email, [ 'size' => $size ] );    
    $html = '<div class="helpful-avatar" height="%1$d" width="%1$d"><img src="%2$s"></div>';
    $html = sprintf($html, $size, $gravatar);
  }
  return $html;
}