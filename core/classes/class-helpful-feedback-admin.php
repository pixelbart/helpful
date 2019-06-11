<?php
class Helpful_Feedback_Admin {
  
  // class instance
	static $instance;

	// class constructor
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'addSubmenu' ] );
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    add_action( 'wp_ajax_helpful_admin_feedback_items', [ $this, 'getFeedbackItems' ] );
    add_action( 'wp_ajax_helpful_remove_feedback', [ $this, 'deleteFeedbackItem' ] );
  }
  
  public function addSubmenu() {
		$hook = add_submenu_page(
			'helpful',
			'Helpful Feedback',
			'Feedback',
			'publish_posts',
			'helpful_feedback',
			[ $this, 'adminPageCallback' ]
		);  
  }

  public function adminPageCallback() {
    include_once HELPFUL_PATH . "templates/admin-feedback.php";
  }

  /**
   * Enqueue backend scripts and styles
   * @return void
   */
	public function enqueueScripts() {
		// current screen is helpful
    // enqueue admin css
    $screen = get_current_screen();

		if( 'helpful_page_helpful_feedback' === $screen->base ) {

      $file = plugins_url( 'core/assets/css/admin-feedback.css', HELPFUL_FILE );
      wp_enqueue_style ( 'helpful-admin-feedback', $file, HELPFUL_VERSION );

      $file = plugins_url( 'core/assets/js/admin-feedback.js', HELPFUL_FILE );
      wp_enqueue_script( 'helpful-admin-feedback', $file, [], HELPFUL_VERSION, true);

      wp_localize_script( 'helpful-admin-feedback', 'helpful_admin_feedback', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'helpful_admin_feedback_nonce' ),
      ] );
    }
  }
  
  public function getFeedbackItems() {
    check_ajax_referer('helpful_admin_feedback_nonce');

    global $wpdb;

    $table_name = $wpdb->prefix . 'helpful_feedback';

    $filters = ['all', 'pro', 'contra'];
    $sql = "SELECT * FROM $table_name";

    if( isset($_REQUEST['filter']) && in_array($_REQUEST['filter'], $filters) ) {
      if( 'pro' == $_REQUEST['filter'] ) {
        $sql = $sql . " WHERE pro = 1";
      }
      if( 'contra' == $_REQUEST['filter'] ) {
        $sql = $sql . " WHERE contra = 1";
      }
    }

    $sql = $sql . " ORDER BY time DESC";

    $posts = $wpdb->get_results( $sql );

    if( $posts ) {
      foreach( $posts as $post ) {
        $feedback = Helpful_Helper_Feedback::getFeedback($post);
        include HELPFUL_PATH . "templates/admin-feedback-item.php";
      }
    }
    else {
      print 'Keine Einträge gefunden.';
    }

    wp_die();
  }

  public function deleteFeedbackitem() {
    global $wpdb;

    if( isset($_REQUEST['feedback_id']) ) {
      $feedback_id = absint($_REQUEST['feedback_id']);
      $table_name = $wpdb->prefix . 'helpful_feedback';
      $wpdb->delete( $table_name, [ 'id' => $feedback_id ] );
    }

    wp_die();
  }
  
  public static function get_instance() {
    if ( ! isset( self::$instance ) ) {
      self::$instance = new self();
    }  
    return self::$instance;
  }
}