<?php
class Helpful_Tabs_Start {

  public function __construct() {
		add_action( 'admin_menu', [ $this, 'registerMenu' ] );
    add_filter( 'helpful_admin_tabs', [ $this, 'registerTab' ], 1 );
    add_action( 'helpful_tabs_content', [ $this, 'addTabContent' ], 1 );
    add_action( 'wp_ajax_helpful_range_stats', [ $this, 'getStatsRange' ] );
    add_action( 'wp_ajax_helpful_total_stats', [ $this, 'getStatsTotal' ] );
  }

  /**
   * Add tab to filter
   * @global $helpful
   * @param array $tabs current tabs
   * @return array
   */
  public function registerTab($tabs) {
    global $helpful;

    $tabs['home'] = [
      'attr'  => ( !isset($_GET['tab']) ? 'selected' : ''),
      'class' => ( !isset($_GET['tab']) ? 'active' : ''),
      'href'  => admin_url('/admin.php?page=helpful'),
      'name'  => 'Start',
    ];

    return $tabs;
  }

  /**
   * Add submenu page in admin (not in use)
   * @return void
   */
  public function registerMenu() {
		add_submenu_page(
			'helpful',
			'Settings',
			'Settings',
			'manage_options',
			'helpful',
			[ $this, 'renderAdminPage' ]
		);
  }

  /**
   * Include admin page
   * @return void
   */
	public function renderAdminPage() {
    include_once HELPFUL_PATH . 'templates/admin.php';
	}

  /**
   * Add content to admin page
   * @global $helpful
   * @return void
   */
   public function addTabContent() {
    global $helpful;

    if( !isset($_GET['tab']) ) {
      include_once HELPFUL_PATH . 'core/tabs/tab-start.php';
    }
  }

  /**
   * @see Helpful_Helper_Values::getStatsRange()
   * @return void
   */
  public function getStatsRange() {
    check_ajax_referer('helpful_range_stats');

    $response = [];

    $from = date_i18n( 'Y-m-d', strtotime($_REQUEST['from']) );
    $to = date_i18n( 'Y-m-d', strtotime($_REQUEST['to']) );

    $response = Helpful_Helper_Stats::getStatsRange($from, $to);

    $response['from'] = $from;
    $response['to'] = $to;

    if( isset($_REQUEST['type']) && 'default' !== $_REQUEST['type'] ) {
      $response['options']['scales'] = [
        'xAxes' => [
          [ 'stacked' => true ],
        ],
        'yAxes' => [
          [ 'stacked' => true ],
        ],
      ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    wp_die();
  }

  /**
   * @see Helpful_Helper_Values::getStatsTotal()
   * @return void
   */
  public function getStatsTotal() {
    check_ajax_referer('helpful_admin_nonce');
    $response = Helpful_Helper_Stats::getStatsTotal();
    header('Content-Type: application/json');
    echo json_encode($response);
    wp_die();
  }
}