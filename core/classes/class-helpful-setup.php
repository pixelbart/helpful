<?php
/**
 * Setup helpful databases and set defaults
 */
class Helpful_Setup {

  private $table_helpful  = 'helpful';
  private $table_feedback = 'helpful_feedback';

  public function __construct() {
    register_activation_hook( HELPFUL_FILE, [ $this, 'updateTransient' ] );
		register_activation_hook( HELPFUL_FILE, [ $this, 'setupHelpfulTable' ] );
    register_activation_hook( HELPFUL_FILE, [ $this, 'setupFeedbackTable' ] );
		register_activation_hook( HELPFUL_FILE, [ $this, 'setupDefaults' ] );
    
		add_action( 'admin_menu', [ $this, 'registerAdminMenu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
  }

  /**
   * Set default options
   * @since 4.0.0
   * @return void
   */
  public function setupDefaults() {
    if( 1 === (int) get_option('helpful_defaults') ) {
      return false;
    }

    $this->setDefaults(true);
    
    update_option('helpful_defaults', 1);
  }
  
  /**
   * Update transient for showing maintenance notice
   * @since 4.0.0
   * @return void
   */
  public function updateTransient() {
    delete_transient('helpful_updated');
  }

  /**
   * Create database table for helpful
   * @since 3.0.0
   * @return bool
   */
  public function setupHelpfulTable() {
		global $wpdb;

    if( 1 === (int) get_option('helpful_is_installed') ) {
      return false;
    }

		// table name
		$table_name = $wpdb->prefix . $this->table_helpful;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00',
			user varchar(55) DEFAULT NULL,
			pro mediumint(1) DEFAULT NULL,
			contra mediumint(1) DEFAULT NULL,
			post_id mediumint(9) DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);

    update_option('helpful_is_installed', 1);

    return true;
  }

  /**
   * Create database table for feedback
   * @since 4.0.0
   * @return bool
   */
  public function setupFeedbackTable() {
		global $wpdb;

    if( 1 === (int) get_option('helpful_feedback_is_installed') ) {
      return false;
    }

		// table name
		$table_name = $wpdb->prefix . $this->table_feedback;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00',
			user varchar(55) DEFAULT NULL,
			pro mediumint(1) DEFAULT NULL,
			contra mediumint(1) DEFAULT NULL,
			post_id mediumint(9) DEFAULT NULL,
      message text DEFAULT NULL,
      fields text DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);

    update_option('helpful_feedback_is_installed', 1);

    return true;
  }

  /**
   * Default values for settings
   * @since 3.0.0
   * @param bool $status set true for filling defaults
   * @return bool
   */
	private function setDefaults( $status = false ) {
		if( false == $status ) {
      return false;
    }

    $options = [
      'helpful_heading' => _x( 'Was this post helpful?', 'default headline', 'helpful' ),
      'helpful_content' => _x( 'Let us know if you liked the post. That’s the only way we can improve.', 'default description', 'helpful' ),
      'helpful_exists' => _x( 'You have already voted for this post.', 'already voted', 'helpful' ),
      'helpful_success' => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
      'helpful_error' => _x( 'Sorry, an error has occurred.', 'error after voting', 'helpful' ),
      'helpful_pro' => _x( 'Yes', 'text pro button', 'helpful' ),
      'helpful_contra' => _x( 'No', 'text contra button', 'helpful' ),
      'helpful_column_pro' => _x( 'Pro', 'column name', 'helpful' ),
      'helpful_column_contra' => _x( 'Contra', 'column name', 'helpful' ),
      'helpful_feedback_label_message' => _x('Message', 'label for feedback form field', 'helpful'),
      'helpful_feedback_label_name' => _x('Name', 'label for feedback form field', 'helpful'),
      'helpful_feedback_label_email' => _x('Email', 'label for feedback form field', 'helpful'),
      'helpful_feedback_label_submit' => _x('Send Feedback', 'label for feedback form field', 'helpful'),
      'helpful_post_types' => [ 'post' ],
      'helpful_count_hide' => false,
      'helpful_credits' => true,
      'helpful_widget' => false,
      'helpful_uninstall' => false,
    ];

    $options = apply_filters('helpful_options', $options);

    foreach( $options as $slug => $value ) {
      update_option( $slug, $value );
    }

    return true;
	}

  /**
   * Register admin menu
   * @return void
   */
	public function registerAdminMenu() {
    add_menu_page(
      __( 'Helpful', 'helpful' ),
      __( 'Helpful', 'helpful' ),
      'manage_options',
      'helpful',
			[ $this, 'settingsPageCallback' ],
      'dashicons-thumbs-up',
      99
    );
	}

  /**
   * Callback for admin page
   * @return void
   */
	public function settingsPageCallback() {
    include_once HELPFUL_PATH . 'templates/admin.php';
	}

  /**
   * Enqueue backend scripts and styles
   * @return void
   */
	public function enqueueScripts() {
		// current screen is helpful
    // enqueue admin css
    $screen = get_current_screen();

		if( 'toplevel_page_helpful' === $screen->base ) {

      $file = plugins_url( 'core/assets/vendor/chartsjs/Chart.min.css', HELPFUL_FILE );
      wp_enqueue_style('helpful-chartjs', $file);

      $file = plugins_url( 'core/assets/vendor/jqueryui/jquery-ui.min.css', HELPFUL_FILE );
      wp_enqueue_style ( 'helpful-jquery', $file, HELPFUL_VERSION );

      $file = plugins_url( 'core/assets/vendor/jqueryui/jquery-ui.structure.min.css', HELPFUL_FILE );
      wp_enqueue_style ( 'helpful-jquery-structure', $file, HELPFUL_VERSION );

      $file = plugins_url( 'core/assets/vendor/jqueryui/jquery-ui.theme.min.css', HELPFUL_FILE );
      wp_enqueue_style ( 'helpful-jquery-theme', $file, HELPFUL_VERSION );

      $file = plugins_url( 'core/assets/css/admin.css', HELPFUL_FILE );
      wp_enqueue_style ( 'helpful-backend', $file, HELPFUL_VERSION );

      $file = plugins_url( 'core/assets/vendor/chartjs/Chart.min.js', HELPFUL_FILE );
      wp_enqueue_script('helpful-chartjs', $file, [], HELPFUL_VERSION, true);

      $file = plugins_url( 'core/assets/vendor/jqueryui/jquery-ui.min.js', HELPFUL_FILE );
      wp_enqueue_script( 'helpful-jquery', $file, [], HELPFUL_VERSION, true);

      $file = plugins_url( 'core/assets/js/admin.js', HELPFUL_FILE );
      wp_enqueue_script( 'helpful-admin', $file, [], HELPFUL_VERSION, true);

      wp_localize_script( 'helpful-admin', 'helpful_admin', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'helpful_admin_nonce' ),
      ] );
    }
  }
}