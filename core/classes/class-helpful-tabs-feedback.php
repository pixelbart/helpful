<?php
class Helpful_Tabs_Feedback extends Helpful_Tabs {
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
    $this->tab_info = [ 'id' => 'feedback', 'name' => esc_html_x( 'Feedback', 'tab name', 'helpful' ), ];
    $this->tab_content = [ $this, 'renderCallback' ];
  }

  /**
   * Include options page
   * @return void
   */
  public function renderCallback() {
    include_once HELPFUL_PATH . 'core/tabs/tab-feedback.php';
  }

  /**
   * Register settings for admin page
   * @return void
   */
  public function registerSettings() {
    $fields = [
      'helpful_feedback_widget',
      'helpful_feedback_after_pro',
      'helpful_feedback_after_contra',
      'helpful_feedback_message_pro',
      'helpful_feedback_message_contra',
      'helpful_feedback_messages_table',
      'helpful_feedback_widget_overview',
      'helpful_feedback_name',
      'helpful_feedback_email',
      'helpful_feedback_label_message',
      'helpful_feedback_label_name',
      'helpful_feedback_label_email',
      'helpful_feedback_label_submit',
      'helpful_feedback_gravatar',
    ];

    foreach( $fields as $field ) {
      register_setting( 'helpful-feedback-settings-group', $field );
    }
  }
}