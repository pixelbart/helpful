<?php
/**
 * Maintenance Class
 * @since 3.2.0
 */
class Helpful_Maintenance {
  
  static $instance;

  public function __construct() {
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    add_action( 'wp_ajax_helpful_perform_maintenance', [ $this, 'performMaintenance' ] );
  }

  /**
   * Enqueue styles and scripts
   * @return void
   */
  public function enqueueScripts() {
    if( isset($_GET['page']) && 'helpful' !== $_GET['page'] ) {
      return;
    }

    $nonce = wp_create_nonce( 'helpful_maintenance_nonce' );
    $file = plugins_url( '/core/assets/js/admin-maintenance.js', HELPFUL_FILE );

    wp_enqueue_script( 'helpful-maintenance', $file, [ 'jquery' ], false, true );   

    wp_localize_script( 'helpful-maintenance', 'helpful_maintenance', [ 
      'ajax_url' => admin_url( 'admin-ajax.php' ), 
      'data' => [
        'action' => 'helpful_perform_maintenance',
        '_wpnonce' => $nonce,
      ],
    ] );
  }

  /**
   * Ajax action for performing maintenance.
   * @see class-helpful-helper-optimize.php
   * @return void
   */
  public function performMaintenance() {
    check_admin_referer( 'helpful_maintenance_nonce' );

    $response = Helpful_Helper_Optimize::optimizePlugin();
    $response = apply_filters( 'helpful_maintenance', $response );

    header('Content-Type: application/json');
    echo json_encode($response);
    wp_die();
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