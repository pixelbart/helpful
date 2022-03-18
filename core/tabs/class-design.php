<?php
/**
 * @package Helpful
 * @subpackage Core\Tabs
 * @version 4.4.50
 * @since 4.3.0
 */
namespace Helpful\Core\Tabs;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;
use Helpful\Core\Vendor as Vendor;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Design
{
    /**
     * Instance
     *
     * @var Design
     */
    public static $instance;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        add_filter('helpful_get_admin_tabs', [ & $this, 'register_tab'], 10, 2);
        add_action('wp_enqueue_scripts', [ & $this, 'custom_css'], PHP_INT_MAX);
    }

    /**
     * Set instance and fire class
     *
     * @return Design
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add tab to filter
     *
     * @param array $tabs current tabs.
     *
     * @return array
     */
    public function register_tab($tabs, $current)
    {
        $query = [];
        $query['autofocus[panel]'] = 'helpful';
        $section_link = add_query_arg($query, admin_url('customize.php'));

        $tabs['design'] = [
            'id' => 'design',
            'name' => esc_html_x('Design', 'tab name', 'helpful'),
            'href' => $section_link,
        ];

        return $tabs;
    }

    /**
     * Print custom css to wp_head.
     *
     * @version 4.4.59
     *
     * @return void
     */
    public function custom_css()
    {
        $options = new Services\Options();

        $customizer = $options->get_option('helpful_customizer', '');

        // ==== CUSTOM CSS ====

        $css_output = (is_array($customizer) && array_key_exists('css', $customizer)) ? $customizer['css'] : '';

        // ==== GENERAL ====

        if ($this->_ake('background_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful {background: %s !important}',
                sanitize_hex_color($customizer['background_color'])
            );
        }

        if ($this->_ake('heading_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-headline {color: %1$s !important}',
                sanitize_hex_color($customizer['heading_color'])
            );
        }

        if ($this->_ake('text_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful {color: %1$s !important} .helpful a {color: %1$s !important} .helpful .helpful-feedback-form label {color: %1$s !important}',
                sanitize_hex_color($customizer['text_color'])
            );
        }

        if ($this->_ake('border_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful {border-color: %s !important}',
                sanitize_hex_color($customizer['border_color'])
            );
        }

        if ($this->_ake('border_style', $customizer)) {
            $css_output .= sprintf(
                '.helpful {border-style: %s !important}',
                sanitize_key(esc_attr($customizer['border_style']))
            );
        }

        if ($this->_ake('border_width', $customizer)) {
            $css_output .= sprintf(
                '.helpful {border-width: %spx !important}',
                intval(esc_attr($customizer['border_width']))
            );
        }

        if ($this->_ake('border_radius', $customizer)) {
            $css_output .= sprintf(
                '.helpful {border-radius: %spx !important}',
                intval(esc_attr($customizer['border_radius']))
            );
        }

        if ($this->_ake('margin_top', $customizer)) {
            $css_output .= sprintf(
                '.helpful {margin-top: %spx !important}',
                intval(esc_attr($customizer['margin_top']))
            );
        }

        if ($this->_ake('margin_bottom', $customizer)) {
            $css_output .= sprintf(
                '.helpful {margin-bottom: %spx !important}',
                intval(esc_attr($customizer['margin_bottom']))
            );
        }

        if ($this->_ake('padding', $customizer)) {
            $css_output .= sprintf(
                '.helpful {padding: %spx !important}',
                intval(esc_attr($customizer['padding']))
            );
        }

        // ==== Button ====

        if ($this->_ake('button_background_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button {background: %1$s !important; opacity: 1} .helpful .helpful-button:hover {opacity: 0.75}',
                sanitize_hex_color($customizer['button_background_color'])
            );
        }

        if ($this->_ake('button_text_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button {color: %1$s !important}',
                sanitize_hex_color($customizer['button_text_color'])
            );
        }

        if ($this->_ake('button_radius', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button {border-radius: %spx !important}',
                intval(esc_attr($customizer['button_radius']))
            );
        }

        // ==== Pro Button ====

        if ($this->_ake('pro_background_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button.helpful-pro {background: %1$s !important; opacity: 1} .helpful .helpful-button.helpful-pro:hover {opacity: 0.75}',
                sanitize_hex_color($customizer['pro_background_color'])
            );
        }

        if ($this->_ake('pro_text_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button.helpful-pro {color: %1$s !important}',
                sanitize_hex_color($customizer['pro_text_color'])
            );
        }

        if ($this->_ake('pro_radius', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button.helpful-pro {border-radius: %spx !important}',
                intval(esc_attr($customizer['pro_radius']))
            );
        }

        // ==== Contra Button ====

        if ($this->_ake('contra_background_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button.helpful-contra {background: %1$s !important; opacity: 1} .helpful .helpful-button.helpful-contra:hover {opacity: 0.75}',
                sanitize_hex_color($customizer['contra_background_color'])
            );
        }

        if ($this->_ake('contra_text_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button.helpful-contra {color: %1$s !important}',
                sanitize_hex_color($customizer['contra_text_color'])
            );
        }

        if ($this->_ake('contra_radius', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-button.helpful-contra {border-radius: %spx !important}',
                intval(esc_attr($customizer['contra_radius']))
            );
        }

        // ==== Feedback ====

        if ($this->_ake('feedback_background_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-feedback-form input, .helpful .helpful-feedback-form textarea {background: %1$s !important}',
                sanitize_hex_color($customizer['feedback_background_color'])
            );
        }

        if ($this->_ake('feedback_text_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-feedback-form input, .helpful .helpful-feedback-form textarea {color: %1$s !important}',
                sanitize_hex_color($customizer['feedback_text_color'])
            );
        }

        if ($this->_ake('feedback_border_color', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-feedback-form input, .helpful .helpful-feedback-form textarea {border-color: %s !important}',
                sanitize_hex_color($customizer['feedback_border_color'])
            );
        }

        if ($this->_ake('feedback_border_style', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-feedback-form input, .helpful .helpful-feedback-form textarea {border-style: %s !important}',
                sanitize_key(esc_attr($customizer['feedback_border_style']))
            );
        }

        if ($this->_ake('feedback_border_width', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-feedback-form input, .helpful .helpful-feedback-form textarea {border-width: %spx !important}',
                intval(esc_attr($customizer['feedback_border_width']))
            );
        }

        if ($this->_ake('feedback_border_radius', $customizer)) {
            $css_output .= sprintf(
                '.helpful .helpful-feedback-form input, .helpful .helpful-feedback-form textarea {border-radius: %spx !important}',
                intval(esc_attr($customizer['feedback_border_radius']))
            );
        }

        if ('' !== trim($css_output)) {
            $parser = new Vendor\Css_Parser();
            $parser->load_string($css_output);
            $parser->parse();
            wp_add_inline_style('helpful', $parser->glue());
        }
    }

    /**
     * array_key_exists
     */
    private function _ake($key, $array)
    {
        return (is_array($array) && array_key_exists($key, $array) && '' !== trim($array[$key]));
    }
}
