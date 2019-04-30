<?php
namespace HelpfulPlugin;
if ( !defined( 'ABSPATH' ) ) exit;
new Shortcodes;

/**
 * Shortcodes
 */
class Shortcodes
{
  public function __construct()
  {
    add_shortcode( 'helpful', [$this, 'shortcode_helpful'] );
  }

  /**
   * 
   * Callback for helpful shortcode
   *
   * @return string
   */
  public function shortcode_helpful()
  {
    ob_start();

    $default_template = HELPFUL_PATH . 'templates/frontend.php';
    $custom_template  = locate_template('helpful/frontend.php');

    // check if custom frontend exists
    if( false !== stream_resolve_include_path( $custom_template ) ) {
      include $custom_template;
    }

    else {
      include $default_template;
    }

    $content = ob_get_contents();
    ob_end_clean();

		return $content;
  }
}