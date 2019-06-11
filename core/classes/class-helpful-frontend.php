<?php
/**
 * Class for displaying helpful and perform actions
 * @since 4.0.0
 */
class Helpful_Frontend {

  static $instance;

  public function __construct() {
    add_action( 'init', [ $this, 'setUserCookie' ], 1 );
    add_filter( 'helpful_themes', [ $this, 'defaultThemes' ], 1 );
    add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    add_action( 'wp_ajax_helpful_save_vote', [ $this, 'saveVote' ] );
    add_action( 'wp_ajax_nopriv_helpful_save_vote', [ $this, 'saveVote' ] );
    add_action( 'wp_ajax_helpful_save_feedback', [ $this, 'saveFeedback' ] );
    add_action( 'wp_ajax_nopriv_helpful_save_feedback', [ $this, 'saveFeedback' ] );
  }
  
  /**
   * Set users cookie with unique id
   * @return void
   */
  public function setUserCookie() {
    $string  = bin2hex(openssl_random_pseudo_bytes(16));
    $string = apply_filters('helpful_user_string', $string);
    $lifetime = '+30 days';

    if( !session_id() ) {
      session_start();
    }

    if( !isset($_COOKIE['helpful_user']) ) {
      setcookie( "helpful_user", $string, strtotime( $lifetime ) );
    }

    if( !isset($_COOKIE['helpful_user']) ) {
      if( !isset($_SESSION['helpful_user']) ) {
        $_SESSION['helpful_user'] = $string;
      }
    }
  }

  /**
   * Retrieve default themes
   * @return array
   */
  public function defaultThemes($themes) {
    
    $themes []= [
      'id' => 'base',
      'label' => esc_html_x( 'Base', 'theme name', 'helpful' ),
      'stylesheet' => plugins_url( 'core/assets/themes/base.css', HELPFUL_FILE ),
    ];
    
    $themes []= [
      'id' => 'dark',
      'label' => esc_html_x( 'Dark', 'theme name', 'helpful' ),
      'stylesheet' => plugins_url( 'core/assets/themes/dark.css', HELPFUL_FILE ),
    ];
    
    $themes []= [
      'id' => 'minimal',
      'label' => esc_html_x( 'Minimal', 'theme name', 'helpful' ),
      'stylesheet' => plugins_url( 'core/assets/themes/minimal.css', HELPFUL_FILE ),
    ];
    
    $themes []= [
      'id' => 'flat',
      'label' => esc_html_x( 'Flat', 'theme name', 'helpful' ),
      'stylesheet' => plugins_url( 'core/assets/themes/flat.css', HELPFUL_FILE ),
    ];
    
    $themes []= [
      'id' => 'simple',
      'label' => esc_html_x( 'Simple', 'theme name', 'helpful' ),
      'stylesheet' => plugins_url( 'core/assets/themes/simple.css', HELPFUL_FILE ),
    ];
    
    $themes []= [
      'id' => 'clean',
      'label' => esc_html_x( 'Clean', 'theme name', 'helpful' ),
      'stylesheet' => plugins_url( 'core/assets/themes/clean.css', HELPFUL_FILE ),
    ];
    
    return $themes;
  }

  /**
   * Enqueue styles and scripts
   * @return void
   */
  public function enqueueScripts() {

    // get active theme and enqueue styles
    $active_theme = get_option('helpful_theme');
    $themes = apply_filters('helpful_themes', false);

    foreach( $themes as $theme ) {
      if( $active_theme !== $theme['id'] ) continue;
      wp_enqueue_style( 'helpful-theme-' . $theme['id'], $theme['stylesheet'], HELPFUL_VERSION );
    }

    // frontend js
    $file = plugins_url( 'core/assets/js/helpful.js', HELPFUL_FILE );
    wp_enqueue_script( 'helpful', $file, ['jquery'], HELPFUL_VERSION, true );

    // frontend js variables
    $user  = Helpful_Helper_Values::getUser();
    $nonce = wp_create_nonce('helpful_frontend_nonce');

    $vars = [
      'ajax_url'    => admin_url('admin-ajax.php'),
      'ajax_data'   => [
        'user_id'   => $user,
        '_wpnonce'  => $nonce,
      ],
    ];

    wp_localize_script( 'helpful', 'helpful', $vars );
  }

  /**
   * Ajax save user vote and render response.
   * @return void
   */
  public function saveVote() {
    check_ajax_referer('helpful_frontend_nonce');
    
    $user_id = sanitize_text_field($_POST['user_id']);
    $post_id = absint($_POST['post']);
    $value   = sanitize_text_field($_POST['value']);
    
    if( !Helpful_Helper_Values::checkUser($user_id, $post_id) ) {
      if( 'pro' == $value ) {
        Helpful_Helper_Values::insertPro($user_id, $post_id);
        $response = $this->afterVote($value, $post_id);
      }
      else {
        Helpful_Helper_Values::insertContra($user_id, $post_id);
        $response = $this->afterVote($value, $post_id);
      }
    }

    echo $response;
    wp_die();
  }

  /**
   * Ajax save user feedback and render response.
   * @return void
   */
  public function saveFeedback() {
    check_ajax_referer('helpful_feedback_nonce');

    $feedback_id = Helpful_Helper_Values::insertFeedback();
    $type = isset($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : 'pro';

    if( 'pro' == $type ) {
      echo get_option('helpful_after_pro');
    }

    if( 'contra' == $type ) {
      echo get_option('helpful_after_contra');
    }

    wp_die();
  }

  /**
   * Render after messages or feedback form, after vote
   * @param string $type feedback type pro or contra
   * @param int $post_id
   * @return string
   */
  public function afterVote($type, $post_id) {

    $feedback_text = esc_html_x('Thank you very much. Please write us your opinion, so that we can improve ourselves.', 'form user note', 'helpful');
    $feedback_button = esc_html_x('Send Feedback', 'button text', 'helpful');

    if( 'pro' == $type ) {
      $feedback_text = get_option('helpful_feedback_message_pro');

      if( !get_option('helpful_feedback_after_pro') ) {
        return get_option('helpful_after_pro');
      }
    }

    if( 'contra' == $type ) {
      $feedback_text = get_option('helpful_feedback_message_contra');

      if( !get_option('helpful_feedback_after_contra') ) {
        return get_option('helpful_after_contra');
      }
    }

    ob_start();

    $default_template = HELPFUL_PATH . 'templates/feedback.php';
    $custom_template  = locate_template('helpful/feedback.php');

    do_action('helpful-before-feedback-form');

    echo '<form class="helpful-feedback-form">';
    printf('<input type="hidden" name="user_id" value="%s">', Helpful_Helper_Values::getUser());
    printf('<input type="hidden" name="action" value="%s">', 'helpful_save_feedback');
    printf('<input type="hidden" name="post_id" value="%s">', $post_id);
    printf('<input type="hidden" name="type" value="%s">', $type);
    wp_nonce_field('helpful_feedback_nonce');

    // check if custom frontend exists
    if( '' !== $custom_template ) {
      include $custom_template;
    }

    else {
      include $default_template;
    }

    echo '</form>';
  
    do_action('helpful-after-feedback-form');
    
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }

  /**
   * Set instance and fire class
   * @return void
   */
  public static function get_instance() {
    if ( !isset(self::$instance) ) {
      self::$instance = new self();
    }
    return self::$instance;
  }
}