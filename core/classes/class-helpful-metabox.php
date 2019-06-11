<?php
/**
 * Class for display a meta box in post types
 * @since 3.2.0
 */
class Helpful_Metabox {
  
  static $instance;

  public function __construct() {    
    if( !get_option('helpful_metabox') ) {
      return;
    }

    add_action( 'add_meta_boxes', [ $this, 'addMetabox' ] );
    add_action( 'save_post', [ $this, 'saveMetaboxData' ], 10, 3 );
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

  /**
   * Add metabox to post types
   * @return void
   */
  public function addMetabox() {
    /* get selected post types from settings */
    $post_types = get_option('helpful_post_types');
    
    if( isset($post_types) ) {
      add_meta_box( 
        'helpful-meta-box', 
        esc_html__( 'Helpful', 'meta box name', 'helpful' ), 
        [ $this, 'renderMetabox' ], 
        $post_types 
      );
    }
  }

  /**
   * Render metabox content
   * @return void
   */
  public function renderMetabox() {
    global $post;

    $pro = Helpful_Helper_Stats::getPro($post->ID);
    $pro_percent = Helpful_Helper_Stats::getPro($post->ID, true);
    $contra = Helpful_Helper_Stats::getContra($post->ID);
    $contra_percent = Helpful_Helper_Stats::getContra($post->ID, true);

    wp_nonce_field( 'helfpul_remove_data', 'helfpul_remove_data_nonce' );
    include( HELPFUL_PATH . 'templates/admin-metabox.php' );
  }

  /**
   * Save meta box data
   * @return void
   */
  public function saveMetaboxData() {
    if ( !wp_verify_nonce( $_POST['helfpul_remove_data_nonce'], 'helfpul_remove_data' ) ) {
      return;
    }
    
    if( 'yes' == $_POST['helfpul_remove_data'] ) {
      Helpful_Helper_Values::removeData($post_id);
    }
  }
}