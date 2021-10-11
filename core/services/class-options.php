<?php
/**
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.4.59
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
     * @version 4.4.59
     *
     * @return void
     */
    public function __construct()
    {
        $options = maybe_unserialize(get_option('helpful_options', []));
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

        // not in use
        $this->options[$name] = $value;
        $this->renew_options();

        // should be replaced
        update_option($name, $value);
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
     * @version 4.4.59
     *
     * @param string $name
     * @param mixed $default
     * @param string $security
     *
     * @return mixed
     */
    public function get_option($name, $default = false, $security = 'blank') {
        if (!is_string($name)) {
            return $default;
        }

        $option = null;

        if (isset($this->options[$name])) {
            $option = $this->options[$name];
        }

        if (get_option($name)) {
            $option = get_option($name);
        }

        $option = apply_filters('helpful/get_option/' . $name, $option);

        if (apply_filters('helpful/get_option/handle_security', true)) {
            $option = $this->handle_security($option, $security);
        }

        return (isset($option)) ? $option : $default;
    }

    /**
     * @return array
     */
    public function get_options()
    {
        return apply_filters('helpful/get_options', $this->options);
    }

    /**
     * @version 4.4.59
     *
     * @param mixed $value
     * @param string $security
     *
     * @return mixed
     */
    private function handle_security($value, $security)
    {
        if ('blank' === $security) {
            return $value;
        }

        if (is_array($value) && !empty($value)) {
            $result = [];

            foreach ($value as $key => $data) {
                $result[$key] = $this->handle_security($data, $security);
            }

            return $result;
        }

        switch ($security) {
            case 'bool':
                $value = boolval($value);
                break;
            case 'esc_html':
                $value = esc_html($value);
                break;
            case 'kses':
                $value = $this->sanitize_input($value);
                break;
            case 'kses_deep':
                $value = $this->sanitize_input_without_tags($value);
                break;
            case 'intval':
                $value = intval($value);
                break;
            case 'floatval':
                $value = floatval($value);
                break;
            case 'esc_attr';
            default:
                $value = esc_attr($value);
        }

        return $value;
    }

    /**
     * Filters the values of an option before saving them. Thus does not allow every
     * HTML element and makes Helpful a bit more secure.
     * 
     * @version 4.4.57
     * @since 4.4.57
     *
     * @param mixed $value
     * 
     * @return mixed
     */
    public function sanitize_input($value)
    {
        return wp_kses($value, Helper::kses_allowed_tags());
    }

    /**
     * Filters the values of an option before saving them. Thus does not allow 
     * HTML element and makes Helpful a bit more secure.
     * 
     * @version 4.4.57
     * @since 4.4.57
     *
     * @param mixed $value
     * 
     * @return mixed
     */
    public function sanitize_input_without_tags($value)
    {
        return wp_kses($value, []);
    }
}