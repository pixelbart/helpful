<?php
class Helpful_Tabs_Design {

  public function __construct() {
    add_filter( 'helpful_admin_tabs', [ $this, 'registerTab' ] );
    add_action( 'wp_head', [ $this, 'customCSS' ], PHP_INT_MAX );
  }

  /**
   * Add tab to filter
   * @global $helpful
   * @param array $tabs current tabs
   * @return array
   */
  public function registerTab($tabs) {
    $query = [];

    $query['autofocus[section]'] = 'helpful_design';
    $section_link = add_query_arg( $query, admin_url( 'customize.php' ) );

    $tabs['design'] = [
      'href'  => $section_link,
      'name'  => esc_html_x('Design', 'tab name', 'helpful'),
    ];

    return $tabs;
  }

  public function customCSS() {
    if( get_option('helpful_css') ) {
      $custom_css = get_option('helpful_css');
      printf( '<style>%s</style>', $custom_css );
    }
  }
}