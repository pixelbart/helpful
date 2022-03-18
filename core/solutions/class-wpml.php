<?php
/**
 * @package Helpful
 * @subpackage Core\Solutions
 * @version 4.5.0
 * @since 4.5.0
 */
namespace Helpful\Core\Solutions;

use Helpful\Core\Helper;
use Helpful\Core\Module;
use Helpful\Core\Helpers;
use Helpful\Core\Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class WPML
{
    use Module;

    public function __construct()
    {
        add_action('admin_init', [ & $this, 'create_translation_file']);
        add_action('helpful/plugin/updated', [ & $this, 'create_translation_file']);
    }

    public function get_file_path()
    {
        return HELPFUL_PATH . '/wpml-config.xml';
    }

    public function check_create_translation_file()
    {
        if (!file_exists($this->get_file_path())) {
            $this->create_translation_file();
        }
    }

    public function create_translation_file()
    {
        $options = new Services\Options();
        $keys = array_keys($options->get_defaults_array());

        if (empty($keys)) {
            return;
        }

        $file_path = $this->get_file_path();

        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $file = fopen($file_path, "w");

        fwrite($file, '<wpml-config>' . PHP_EOL);
        fwrite($file, str_repeat("\x20", 2) . '<admin-texts>' . PHP_EOL);
        fwrite($file, str_repeat("\x20", 4) . '<key name="helpful_options">' . PHP_EOL);

        foreach ($keys as $key) {
            fwrite($file, str_repeat("\x20", 6) . '<key name="' . sanitize_key($key) . '"/>' . PHP_EOL);
        }

        fwrite($file, str_repeat("\x20", 4) . '</key>' . PHP_EOL);
        fwrite($file, str_repeat("\x20", 2) . '</admin-texts>' . PHP_EOL);
        fwrite($file, '</wpml-config>' . PHP_EOL);

        fclose($file);
    }
}