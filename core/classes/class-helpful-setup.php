<?php
/**
 * Class for installing database tables and register defaults.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 1.0.0
 */
class Helpful_Setup {

	/**
	 * Instance
	 *
	 * @var $instance
	 */
	public static $instance;

	/**
	 * Helpful database table name
	 *
	 * @var $table_helpful
	 */
	protected $table_helpful = 'helpful';

	/**
	 * Helpful feedback database table name
	 *
	 * @var $table_feedback
	 */
	protected $table_feedback = 'helpful_feedback';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		register_activation_hook( HELPFUL_FILE, [ $this, 'delete_transient' ] );
		register_activation_hook( HELPFUL_FILE, [ $this, 'setup_helpful_table' ] );
		register_activation_hook( HELPFUL_FILE, [ $this, 'setup_feedback_table' ] );
		register_activation_hook( HELPFUL_FILE, [ $this, 'setup_defaults' ] );

		add_action( 'activated_plugin', [ $this, 'load_first' ] );

		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Set instance and fire class.
	 *
	 * @return instance
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Set default options.
	 *
	 * @return boolean
	 */
	public function setup_defaults() {
		if ( 1 === (int) get_option( 'helpful_defaults' ) ) {
			return false;
		}

		$this->set_defaults( true );

		update_option( 'helpful_defaults', 1 );

		return true;
	}

	/**
	 * Update transient for showing maintenance notice.
	 *
	 * @return void
	 */
	public function delete_transient() {
		delete_transient( 'helpful_updated' );
	}

	/**
	 * Create database table for helpful
	 *
	 * @global $wpdb
	 *
	 * @return bool
	 */
	public function setup_helpful_table() {
		if ( 1 === (int) get_option( 'helpful_is_installed' ) ) {
			return false;
		}

		global $wpdb;
		$table_name      = $wpdb->prefix . $this->table_helpful;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
		CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id mediumint(9) DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'helpful_is_installed', 1 );
		return true;
	}

	/**
	 * Create database table for feedback
	 *
	 * @global $wpdb
	 *
	 * @return bool
	 */
	public function setup_feedback_table() {
		if ( 1 === (int) get_option( 'helpful_feedback_is_installed' ) ) {
			return false;
		}

		global $wpdb;
		$table_name      = $wpdb->prefix . $this->table_feedback;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
		CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT, 
		time datetime DEFAULT '0000-00-00 00:00:00', 
		user varchar(55) DEFAULT NULL, 
		pro mediumint(1) DEFAULT NULL, 
		contra mediumint(1) DEFAULT NULL, 
		post_id mediumint(9) DEFAULT NULL, 
		message text DEFAULT NULL, 
		fields text DEFAULT NULL, 
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'helpful_feedback_is_installed', 1 );
		return true;
	}

	/**
	 * Default values for settings
	 *
	 * @param bool $status set true for filling defaults.
	 *
	 * @return bool
	 */
	public function set_defaults( $status = false ) {
		if ( false === $status ) {
			return false;
		}

		$options = [
			'helpful_heading'                => _x( 'Was this post helpful?', 'default headline', 'helpful' ),
			'helpful_content'                => _x( 'Let us know if you liked the post. That’s the only way we can improve.', 'default description', 'helpful' ),
			'helpful_exists'                 => _x( 'You have already voted for this post.', 'already voted', 'helpful' ),
			'helpful_success'                => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
			'helpful_error'                  => _x( 'Sorry, an error has occurred.', 'error after voting', 'helpful' ),
			'helpful_pro'                    => _x( 'Yes', 'text pro button', 'helpful' ),
			'helpful_contra'                 => _x( 'No', 'text contra button', 'helpful' ),
			'helpful_column_pro'             => _x( 'Pro', 'column name', 'helpful' ),
			'helpful_column_contra'          => _x( 'Contra', 'column name', 'helpful' ),
			'helpful_feedback_label_message' => _x( 'Message', 'label for feedback form field', 'helpful' ),
			'helpful_feedback_label_name'    => _x( 'Name', 'label for feedback form field', 'helpful' ),
			'helpful_feedback_label_email'   => _x( 'Email', 'label for feedback form field', 'helpful' ),
			'helpful_feedback_label_submit'  => _x( 'Send Feedback', 'label for feedback form field', 'helpful' ),
			'helpful_post_types'             => [ 'post' ],
			'helpful_count_hide'             => false,
			'helpful_credits'                => true,
			'helpful_widget'                 => false,
			'helpful_uninstall'              => false,
		];

		$options = apply_filters( 'helpful_options', $options );

		foreach ( $options as $slug => $value ) :
			update_option( $slug, $value );
		endforeach;
		return true;
	}

	/**
	 * Loads helpful first
	 *
	 * @return void
	 */
	public function load_first() {

		if ( ! get_option( 'helpful_plugin_first' ) ) {
			return;
		}

		$path = str_replace( WP_PLUGIN_DIR . '/', '', HELPFUL_FILE );
		if ( $plugins = get_option( 'active_plugins' ) ) {
			if ( $key = array_search( $path, $plugins ) ) {
				array_splice( $plugins, $key, 1 );
				array_unshift( $plugins, $path );
				update_option( 'active_plugins', $plugins );
			}
		}
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Helpful', 'helpful' ),
			__( 'Helpful', 'helpful' ),
			'manage_options',
			'helpful',
			[ $this, 'settings_page_callback' ],
			'dashicons-thumbs-up',
			99
		);
	}

	/**
	 * Callback for admin page.
	 *
	 * @return void
	 */
	public function settings_page_callback() {
		include_once HELPFUL_PATH . 'templates/admin.php';
	}

	/**
	 * Enqueue backend scripts and styles, if current screen is helpful
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( 'toplevel_page_helpful' === $screen->base ) {

			$file = plugins_url( 'core/assets/vendor/chartsjs/Chart.min.css', HELPFUL_FILE );
			wp_enqueue_style( 'helpful-chartjs', $file, [], HELPFUL_VERSION );

			$file = plugins_url( 'core/assets/vendor/jqueryui/jquery-ui.min.css', HELPFUL_FILE );
			wp_enqueue_style( 'helpful-jquery', $file, [], HELPFUL_VERSION );

			$file = plugins_url( 'core/assets/vendor/jqueryui/jquery-ui.structure.min.css', HELPFUL_FILE );
			wp_enqueue_style( 'helpful-jquery-structure', $file, [], HELPFUL_VERSION );

			$file = plugins_url( 'core/assets/vendor/jqueryui/jquery-ui.theme.min.css', HELPFUL_FILE );
			wp_enqueue_style( 'helpful-jquery-theme', $file, [], HELPFUL_VERSION );

			$file = plugins_url( 'core/assets/css/admin.css', HELPFUL_FILE );
			wp_enqueue_style( 'helpful-backend', $file, [], HELPFUL_VERSION );

			$file = plugins_url( 'core/assets/vendor/chartjs/Chart.min.js', HELPFUL_FILE );
			wp_enqueue_script( 'helpful-chartjs', $file, [], HELPFUL_VERSION, true );

			$file = plugins_url( 'core/assets/vendor/jqueryui/jquery-ui.min.js', HELPFUL_FILE );
			wp_enqueue_script( 'helpful-jquery', $file, [], HELPFUL_VERSION, true );

			$file = plugins_url( 'core/assets/js/admin.js', HELPFUL_FILE );
			wp_enqueue_script( 'helpful-admin', $file, [], HELPFUL_VERSION, true );

			$vars = [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'helpful_admin_nonce' ),
			];

			wp_localize_script( 'helpful-admin', 'helpful_admin', $vars );
		}
	}
}
