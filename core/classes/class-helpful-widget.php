<?php
class Helpful_Widget {

  static $instance;

  public function __construct() {
    add_action( 'wp_dashboard_setup', [ $this, 'widgetSetup' ], 1 );
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    add_action( 'wp_ajax_helpful_widget_stats', [ $this, 'getStats'] );
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
   * Enqueue styles and scripts
   */
  public function enqueueScripts() {
		if( get_option('helpful_widget') ) {
      return false;
    }

    $file = plugins_url( 'core/assets/vendor/chartsjs/Chart.min.css', HELPFUL_FILE );
    wp_register_style('helpful-chartjs', $file);

    $file = plugins_url( 'core/assets/vendor/chartjs/Chart.min.js', HELPFUL_FILE );
    wp_register_script('helpful-chartjs', $file, false, false, true);

    $file = plugins_url( 'core/assets/css/admin-widget.css', HELPFUL_FILE );
    wp_register_style('helpful-widget', $file);

    $file = plugins_url( 'core/assets/js/admin-widget.js', HELPFUL_FILE );
    wp_register_script('helpful-widget', $file, [ 'jquery' ], false, true);
  }

  /**
   * Dashboard widget options
   * @return void
   */
	public function widgetSetup() {
		if( get_option('helpful_widget') ) {
      return false;
    }

    global $wp_meta_boxes;

    wp_add_dashboard_widget(
      'helpful_widget',
      esc_html_x( 'Helpful', 'headline dashboard widget', 'helpful' ),
      [ $this, 'widgetCallback' ],
      null,
      [ '__block_editor_compatible_meta_box' => false ]
    );

		$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		$helpful_widget = [ 'helpful_widget' => $dashboard['helpful_widget'] ];
		unset( $dashboard['helpful_widget'] );
		$sorted_dashboard = array_merge( $helpful_widget, $dashboard );
    $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
    return true;
	}

  /**
   * Dashboard widget content
   * @return void
   */
	public function widgetCallback() {
    wp_enqueue_style('helpful-chartjs');
    wp_enqueue_style('helpful-widget');
    wp_enqueue_script('helpful-chartjs');
    wp_enqueue_script('helpful-widget');

    $links = [
      sprintf('<a href="%s" title="%s">%s</a>',
        admin_url('admin.php?page=helpful&tab=texts'),
        __('Settings', 'helpful'),
        '<span class="dashicons dashicons-admin-settings"></span>'
      ),
      sprintf('<a href="%s" title="%s">%s</a>',
        admin_url('admin.php?page=helpful_feedback'),
        __('Feedback', 'helpful'),
        '<span class="dashicons dashicons-testimonial"></span>'
      ),
      sprintf('<a href="%s" title="%s">%s</a>',
        admin_url('admin.php?page=helpful'),
        __('Statistics', 'helpful'),
        '<span class="dashicons dashicons-chart-area"></span>'
      ),
    ];

    $years = Helpful_Helper_Stats::getYears();

    include_once HELPFUL_PATH . "templates/admin-widget.php";
  }

  /**
   * Ajax get stats
   * @return void
   */
  public function getStats() {
    check_ajax_referer('helpful_widget_stats');

    $response = [];
    $response['status'] = 'error';
    $response['message'] = __('No entries founds', 'helpful');

    $range = 'today';
    $ranges = [ 'today', 'yesterday', 'week', 'month', 'year', 'total' ];

    if( isset($_GET['range']) && in_array($_GET['range'], $ranges) ) {
      $range = $_GET['range'];
    }

    $year = 2019;

    if( isset($_GET['range']) ) {
      $year = absint($_GET['year']);
    }

    switch($range) {
      case 'today':
        $response = Helpful_Helper_Stats::getStatsToday($year);
        break;
      case 'yesterday':
        $response = Helpful_Helper_Stats::getStatsYesterday($year);
        break;
      case 'week':
        $response = Helpful_Helper_Stats::getStatsWeek($year);
        break;
      case 'month':
        $response = Helpful_Helper_Stats::getStatsMonth($year);
        break;
      case 'year':
        $response = Helpful_Helper_Stats::getStatsYear($year);
        break;
      case 'total':
        $response = Helpful_Helper_Stats::getStatsTotal();
        break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);

    wp_die();
  }
}