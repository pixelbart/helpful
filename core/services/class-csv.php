<?php
/**
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.4.59
 * @since 4.4.49
 */
namespace Helpful\Core\Services;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class CSV
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var array
     */
    public $items;

    /**
     * @var string
     */
    public $file;

    /**
     * @param string $filename
     * @return void
     */
    public function __construct(string $filename)
    {
        if (!strpos($filename, '.csv')) {
            $filename .= '.csv';
        }

        $this->filename = $filename;
        $this->items = false;
        $this->file = false;
    }

    /**
     * @param array $items
     */
    public function add_items(array $items)
    {
        if (is_array($items)) {
            $this->items = $items;
        }
    }

    /**
     * @version 4.4.59
     *
     * @return void
     */
    public function create_file()
    {
        if (!is_array($this->items)) {
            return new \WP_Error(__('No csv items found.', 'csv error', 'helpful'));
        }

        $options = new Services\Options();

        $items = $this->items;

        $lines   = [];
        $lines[] = array_keys( $items[0] );

        foreach ( $items as $item ) :
            $lines[] = array_values( $item );
        endforeach;
        
        $uploads = wp_upload_dir();		

        if ( ! file_exists( $uploads['basedir'] . '/helpful' ) ) {
            mkdir( $uploads['basedir'] . '/helpful', 0755, true );
        }

        $file_name = '/helpful/' . $this->filename;

        if ( file_exists( $uploads['basedir'] . $file_name ) ) {
            unlink( $uploads['basedir'] . $file_name );
        }

        clearstatcache();

        $separator  = ';';
        $separators = [ ';', ',' ];
        $separators = apply_filters( 'helpful_export_separators', $separators );

        $option = $options->get_option('helpful_export_separator', ';', 'esc_attr');

        if ( $option && in_array( $option, $separators ) ) {
            $separator = esc_html( $option );
        }
        
        $file = fopen( $uploads['basedir'] . $file_name, 'w+' );

        foreach ( $lines as $line ) :
            fputcsv( $file, $line, $separator );
        endforeach;

        fclose( $file );

        $this->file = $uploads['baseurl'] . $file_name;
    }

    /**
     * @return string
     */
    public function get_file()
    {        
        return $this->file;
    }
}