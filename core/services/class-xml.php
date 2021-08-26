<?php
/**
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.4.50
 * @since 4.4.47
 */
namespace Helpful\Core\Services;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class XML
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $lists;

    /**
     * @param string $filename
     * @return void
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->lists = [];
    }

    /**
     * @param string $listname
     * @return void
     */
    public function add_list($listname)
    {
        $this->lists[$listname] = [];
    }

    /**
     * @param string $listname
     * @param string $item
     * @return void
     */
    public function add_list_item($listname, $item)
    {
        $this->lists[$listname][] = $item;
    }

    /**
     * @return string
     */
    public function get_file()
    {
        return HELPFUL_PATH . $this->filename . '.xml';
    }

    /**
     * @return void
     */
    public function save()
    {
        $file = fopen($this->get_file(), "w");
        fwrite($file, sprintf('<%s>', $this->filename) . "\n");

        if (!empty($this->lists)) {
            foreach ($lists as $name => $items) {
                if (empty($items)) {
                    continue;
                }

                fwrite($file, sprintf('<%s>', $name) . "\n");                
                foreach ($items as $item) :
                    fwrite($file, $item . "\n");                    
                endforeach;
                fwrite($file, sprintf('</%s>', $name) . "\n");
            }
        }

        fwrite($file, sprintf('</%s>', $this->filename));
        fclose($file);
    }
}
