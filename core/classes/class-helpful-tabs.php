<?php
class Helpful_Tabs {
  public $tab_info, $tab_content;

  /**
   * Add tab to filter
   * @global $helpful
   * @param array $tabs current tabs
   * @return array
   */
  public function registerTab($tabs) {
    global $helpful;

    $tab = $this->tab_info;
    $tab_active = ($tab['id'] === $helpful['tab']);
    $query_args = [
      'page' => 'helpful',
      'tab' => $tab['id'],
    ];

    $tabs[$tab['id']] = [
      'attr'  => ($tab_active ? 'selected' : ''),
      'class' => ($tab_active ? 'active' : ''),
      'href'  => add_query_arg($query_args),
      'name'  => $tab['name'],
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
			$this->tab_info['name'],
			$this->tab_info['name'],
			'manage_options',
			'helpful&tab=' . $this->tab_info['id'],
			[ $this, 'renderAdminPage' ]
		);
  }

  /**
   * Include admin page
   * @return void
   */
	public function renderAdminPage() {
    include_once HELPFUL_PATH . 'templates/backend.php';
	}

  /**
   * Add content to admin page
   * @global $helpful
   * @return void
   */
   public function addTabContent() {
    global $helpful;

    $tab = $this->tab_info;
    $tab_active = ($tab['id'] === $helpful['tab']);

    if( $tab_active ) {
      call_user_func($this->tab_content);
    }
  }
}