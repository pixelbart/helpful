<?php
class Helpful_Tabs_Texts extends Helpful_Tabs {
  public $tab_info, $tab_content;

  public function __construct() {
    $this->setupTab();

		// add_action( 'admin_menu', [ $this, 'registerMenu' ] );
    add_action( 'admin_init', [ $this, 'registerSettings' ] );
    add_filter( 'helpful_admin_tabs', [ $this, 'registerTab' ] );
    add_action( 'helpful_tabs_content', [$this, 'addTabContent'] );
  }

  /**
   * Add tab to helpful admin menu
   * @return void
   */
  public function setupTab() {
    $this->tab_info = [ 'id' => 'texts', 'name' => esc_html_x( 'Texts', 'tab name', 'helpful' ), ];
    $this->tab_content = [ $this, 'renderCallback' ];
  }

  /**
   * Include options page
   * @return void
   */
  public function renderCallback() {
    include_once HELPFUL_PATH . 'core/tabs/tab-texts.php';
  }

  /**
   * Register settings for admin page
   * @return void
   */
  public function registerSettings() {    
    $fields = [
      'helpful_heading',
      'helpful_content',
      'helpful_pro',
      'helpful_exists',
      'helpful_contra',
      'helpful_column_pro',
      'helpful_column_contra',
      'helpful_after_pro',
      'helpful_after_contra',
    ];

    foreach( $fields as $field ) {
      register_setting( 'helpful-texts-settings-group', $field );
    }
  }
}