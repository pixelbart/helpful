<?php
/**
 * A service with which you can create a XML file.
 *
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.4.50
 * @since 4.4.47
 */

namespace Helpful\Core\Services;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class XML {
	/**
	 * File name
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * XML items.
	 *
	 * @var array
	 */
	private $lists;

	/**
	 * Constructor
	 *
	 * @param string $filename file name.
	 */
	public function __construct( $filename ) {
		$this->filename = $filename;
		$this->lists    = array();
	}

	/**
	 * Add a xml list for items.
	 *
	 * @param string $listname name of the xml list.
	 */
	public function add_list( $listname ) {
		$this->lists[ $listname ] = array();
	}

	/**
	 * Add a list item for a xml list.
	 *
	 * @param string $listname list name.
	 * @param string $item item.
	 */
	public function add_list_item( $listname, $item ) {
		$this->lists[ $listname ][] = $item;
	}

	/**
	 * Get the file path.
	 *
	 * @return string
	 */
	public function get_file() {
		return HELPFUL_PATH . $this->filename . '.xml';
	}

	/**
	 * Save the file, for retrieving with get_file().
	 */
	public function save() {
		$file = fopen( $this->get_file(), 'w' );
		fwrite( $file, sprintf( '<%s>', $this->filename ) . "\n" );

		if ( ! empty( $this->lists ) ) {
			foreach ( $lists as $name => $items ) {
				if ( empty( $items ) ) {
					continue;
				}

				fwrite( $file, sprintf( '<%s>', $name ) . "\n" );
				foreach ( $items as $item ) :
					fwrite( $file, $item . "\n" );
				endforeach;
				fwrite( $file, sprintf( '</%s>', $name ) . "\n" );
			}
		}

		fwrite( $file, sprintf( '</%s>', $this->filename ) );
		fclose( $file );
	}
}
