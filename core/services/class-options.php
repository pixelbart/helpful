<?php
/**
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.4.47
 * @since 4.4.47
 */
namespace Helpful\Core\Services;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Options
{
    /**
     * @var array
     */
    private $options;

    /**
     * @return void
     */
    public function __construct()
    {
        $options = maybe_unserialize(get_option('helpful_options'));
        $this->options = (!is_array($options)) ? [] : $options;
    }

    /**
     * @return void
     */
    public function renew_options()
    {
        update_option('helpful_options', maybe_serialize($this->options));
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function update_option($name, $value)
    {
        if (!is_string($name)) {
            return;
        }

        $this->options[$name] = $value;
        $this->renew_options();
    }

    /**
     * @param string $name
     * @return void
     */
    public function delete_option($name)
    {
        if (!is_string($name)) {
            return;
        }

        if (isset($this->options[$name])) {
            unset($this->options[$name]);
        }

        $this->renew_options();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get_option($name, $default = false) {
        if (!is_string($name)) {
            return $default;
        }

        if (isset($this->options[$name])) {
            return apply_filters('helpful/get_option/' . $name, $this->options[$name]);
        }

        if (get_option($name)) {
            return apply_filters('helpful/get_option/' . $name, get_option($name));
        }

        return $default;
    }

    /**
     * @return array
     */
    public function get_options()
    {
        return apply_filters('helpful/get_options', $this->options);
    }
}