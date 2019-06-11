<?php
/**
 * Class of adding admin notices
 */
class Helpful_Notices {

  static $instance;

  /**
   * Constructor
   * @return void
   */
  public function __construct() {
    add_action('admin_notices', [ $this, 'performMaintenanceNotice' ] );
    add_action('helpful_notices', [ $this, 'performMaintenance'] );
  }

  /**
   * Informs the user to perform maintenance.
   * @since 4.0.0
   * @return void
   */
  public function performMaintenanceNotice() {
    $screen = get_current_screen();

    if( false === ( $value = get_transient('helpful_updated') ) && 'toplevel_page_helpful' !== $screen->base ) {

      $class = 'notice-warning';

      $url = add_query_arg( [
        'page' => 'helpful',
        'action' => 'perform-maintenance',
      ], admin_url('admin.php'));

      $message = esc_html_x('The Helpful database must have been updated: %s', 'admin notice', 'helpful');
      $button = sprintf( '<a href="%s">%s</a>', $url, esc_html_x('Update database', 'admin notice action', 'helpful') );
      $notice = sprintf($message, $button);

      printf('<div class="notice %s"><p>%s</p></div>', $class, $notice);
    }
  }

  /**
   * Notifies the user that maintenance has been performed and performs maintenance.
   * @since 4.0.0
   * @return void
   */
  public function performMaintenance() {
    $action = 'perform-maintenance';
    $screen = get_current_screen();

    if( isset($_GET['action']) && $action === $_GET['action'] && 'toplevel_page_helpful' === $screen->base ) {

      // perform maintenance
      $response = Helpful_Helper_Optimize::optimizePlugin();
      $response = apply_filters( 'helpful_maintenance', $response );

      $class = 'notice-success';
      $notice = esc_html_x('Thank you very much. The database has been updated successfully. ', 'admin notice', 'helpful');
      printf('<div class="notice %s is-dismissible"><p>%s</p></div>', $class, $notice);

      // store value in database for 7 days
      set_transient( 'helpful_updated', 1, 7 * DAY_IN_SECONDS );
    }
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