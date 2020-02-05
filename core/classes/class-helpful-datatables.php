<?php
/**
 * Here the languages for the datatables are included as long as they match WordPress.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 * @since   4.1.5
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpful_Datatables {

	/**
	 * Instance
	 *
	 * @var Helpful_Datatables
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Datatables
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'helpful_datatables_language', [ &$this, 'set_datatables_language' ], 1 );	
	}

	/**
	 * Sets the German language if the user has set German
	 * as language in the WordPress settings.
	 *
	 * @return string
	 */
	public function set_datatables_language()
	{
		return wp_json_encode( Helpful_Helper_Values::datatables_language_string() );
	}
}