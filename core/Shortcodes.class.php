<?php
namespace Helpful\Core;
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
    $default_template = HELPFUL_PATH . 'templates/frontend.php';
    $custom_template  = locate_template('helpful/frontend.php');

    ob_start();

    // check if custom frontend exists
    if( '' !== $custom_template ) {
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