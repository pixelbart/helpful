<?php
class Helpful_Shortcodes {

  static $instance;

  public function __construct() {
		add_filter( 'the_content', [ $this , 'addToContent' ] );
    add_shortcode( 'helpful', [$this, 'shortcodeHelpful'] );
  }

  /**
   * Add helpful to post content
   * @param string $content
   * @return string
   */
  public function addToContent($content) {
    global $post;

    $post_types = get_option('helpful_post_types');
    $user_id = Helpful_Helper_Values::getUser();

    if( !in_array($post->post_type, $post_types) ) {
      return $content;
    }
    
    if( get_option('helpful_hide_in_content') ) {
      return $content;
    }

    if( get_option('helpful_exists_hide') && Helpful_Helper_Values::checkUser($user_id, $post->ID) ) {
      return $content;
    }

    if( !is_singular() ) {
      return $content;
    }

    // Helpful atts
    $helpful = Helpful_Helper_Values::getDefaults();

    $hidden = false;
    $class = '';

    if( 1 == $helpful['exists'] ) {
      if( 1 == $helpful['exists-hide'] ) {
        return;
      }

      $hidden = true;
      $class = 'helpful-exists';
      $helpful['content'] = $helpful['exists_text'];
    }

    ob_start();

    $default_template = HELPFUL_PATH . 'templates/helpful.php';
    $custom_template  = locate_template('helpful/helpful.php');

    // check if custom frontend exists
    if( '' !== $custom_template ) {
      include $custom_template;
    }

    else {
      include $default_template;
    }

    $content .= ob_get_contents();
    ob_end_clean();

    return $content;
  }

  /**
   * Callback for helpful shortcode
   * @param array $atts
   * @return string
   */
  public function shortcodeHelpful($atts) {

    // Default Atts
    $defaults = Helpful_Helper_Values::getDefaults();

    // Shortcode Atts
    $helpful = shortcode_atts($defaults, $atts );

    $hidden = '';

    if( 1 == $helpful['exists'] ) {
      if( 1 == $helpful['exists-hide'] ) {
        return;
      }

      $hidden = 'hidden';
    }

    ob_start();

    $default_template = HELPFUL_PATH . 'templates/helpful.php';
    $custom_template  = locate_template('helpful/helpful.php');

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

  /**
   * Set instance and fire class
   * @return void
   */
  public static function get_instance() {
    if ( ! isset( self::$instance ) ) {
      self::$instance = new self();
    }
    return self::$instance;
  }
}