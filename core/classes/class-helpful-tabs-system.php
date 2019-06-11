<?php
class Helpful_Tabs_System extends Helpful_Tabs {

  static $instance;

  public $tab_info;
  public $tab_content;

  public function __construct() {
    $this->setupTab();

		// add_action( 'admin_menu', [ $this, 'registerMenu' ] );
    add_action( 'admin_init', [ $this, 'registerSettings' ] );
    add_filter( 'helpful_admin_tabs', [ $this, 'registerTab' ] );
    add_action( 'helpful_tabs_content', [ $this, 'addTabContent' ] );
    add_action( 'admin_init', [ $this, 'resetHelpful' ] );
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
   * Add tab to helpful admin menu
   * @return void
   */
  public function setupTab() {
    $this->tab_info = [ 'id' => 'system', 'name' => esc_html_x( 'System', 'tab name', 'helpful' ), ];
    $this->tab_content = [ $this, 'renderCallback' ];
  }

  /**
   * Include options page
   * @return void
   */
  public function renderCallback() {
    
    $post_types = get_post_types( ['public' => true] );
    $private_post_types = get_post_types( ['public' => false] );

    if( isset($private_post_types) ) {
      $post_types = array_merge($post_types, $private_post_types);
    } else {
      $private_post_types = [];
    }

    include_once HELPFUL_PATH . 'core/tabs/tab-system.php';
  }

  /**
   * Register settings for admin page
   * @return void
   */
  public function registerSettings() {
    $fields = [
      'helpful_uninstall',
      'helpful_timezone',
      'helpful_multiple',
    ];

    foreach( $fields as $field ) {
      register_setting( 'helpful-system-settings-group', $field );
    }
  }
  
  /**
   * Reset helpful database and entries
   * @return void
   */
	public function resetHelpful() {
		if( !get_option('helpful_uninstall') ) {
      return;
    }

  	global $wpdb;

    $table_name = $wpdb->prefix . 'helpful';
    $wpdb->query("TRUNCATE TABLE $table_name");      
    
    update_option( 'helpful_uninstall', false );

    $args = [
      'post_type' => 'any',
      'posts_per_page' => -1,
      'fields' => 'ids',
    ];

    $posts = new WP_Query($args);

    if( $posts->found_posts ) {
    	foreach( $posts->posts as $post_id ) {
    		if( get_post_meta( $post_id, 'helpful-pro' ) ) {
    			delete_post_meta( $post_id, 'helpful-pro' );
    		}
    		if( get_post_meta( $post_id, 'helpful-contra' ) ) {
    			delete_post_meta( $post_id, 'helpful-contra' );
    		}

        if( 'helpful_feedback' == get_post_type($post_id) ) {
          wp_delete_post($post_id, true);
        }
    	}
    }

    update_option('helpful_is_installed', 0);
	}
}