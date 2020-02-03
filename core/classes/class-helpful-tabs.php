<?php
/**
 * Tabs helper for admin page
 *
 * @author Pixelbart <me@pixelbart.de>
 * @package Helpful
 */
class Helpful_Tabs {

	/**
	 * Stores tab data
	 *
	 * @var array
	 */
	public $tab_info;

	/**
	 * Stores tab content
	 *
	 * @var array
	 */
	public $tab_content;

	/**
	 * Add tab to filter
	 *
	 * @global $helpful
	 *
	 * @param array $tabs current tabs.
	 *
	 * @return array
	 */
	public function register_tab( array $tabs )
	{
		global $helpful;

		$tab        = $this->tab_info;
		$tab_active = ( $tab['id'] === $helpful['tab'] );
		$query_args = [
			'page' => 'helpful',
			'tab'  => $tab['id'],
		];

		$tabs[ $tab['id'] ] = [
			'attr'  => ( $tab_active ? 'selected' : '' ),
			'class' => ( $tab_active ? 'active' : '' ),
			'href'  => add_query_arg( $query_args ),
			'name'  => $tab['name'],
		];

		return $tabs;
	}

	/**
	 * Deprecated: Add submenu page in admin
	 *
	 * @return void
	 */
	public function register_menu()
	{
		add_submenu_page(
			'helpful',
			$this->tab_info['name'],
			$this->tab_info['name'],
			'manage_options',
			'helpful&tab=' . $this->tab_info['id'],
			[ &$this, 'renderAdminPage' ]
		);
	}

	/**
	 * Include admin page
	 *
	 * @return void
	 */
	public function render_admin_page()
	{
		include_once HELPFUL_PATH . 'templates/backend.php';
	}

	/**
	 * Add content to admin page
	 *
	 * @global $helpful
	 *
	 * @return void
	 */
	public function add_tab_content()
	{
		global $helpful;

		$tab        = $this->tab_info;
		$tab_active = ( $tab['id'] === $helpful['tab'] );

		if ( $tab_active ) {
			call_user_func( $this->tab_content );
		}
	}
}
