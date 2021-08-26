<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.4.50
 * @since 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Customizer
{
    /**
     * Instance
     *
     * @var Customizer
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Customizer
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        add_action('customize_register', [ & $this, 'register_customizer']);
    }

    /**
     * Register custom customizer section/panel
     *
     * @param object $wp_customize WordPress customizer object.
     *
     * @return void
     */
    public static function register_customizer($wp_customize)
    {
        $panel = [
            'title' => esc_html_x('Helpful', 'customizer panel title', 'helpful'),
            'description' => esc_html_x('Here you can make small adjustments to Helpful.', 'customizer panel description', 'helpful'),
            'priority' => 10,
        ];

        $wp_customize->add_panel('helpful', $panel);

        /* Design Section */

        $section = [
            'title' => esc_html_x('Design', 'customizer section title', 'helpful'),
            'description' => esc_html_x('Here you can customize the design of Helpful.', 'customizer section description', 'helpful'),
            'priority' => 10,
            'panel' => 'helpful',
        ];

        $wp_customize->add_section('helpful_design', $section);

        /* Helpful Themes */

        $themes = apply_filters('helpful_themes', false);
        $choices = [];

        foreach ($themes as $theme):
            $choices[$theme['id']] = $theme['label'];
        endforeach;

        $setting = [
            'default' => 'base',
            'type' => 'option',
        ];

        $wp_customize->add_setting('helpful_theme', $setting);

        $control = [
            'label' => esc_html_x('Theme', 'customizer control label', 'helpful'),
            'section' => 'helpful_design',
            'settings' => 'helpful_theme',
            'type' => 'select',
            'choices' => $choices,
        ];

        $wp_customize->add_control('helpful_theme', $control);

        /* Helpful CSS */

        $setting = [
            'default' => '',
            'type' => 'option',
        ];

        $wp_customize->add_setting('helpful_css', $setting);

        $control = [
            'label' => esc_html_x('Custom CSS', 'customizer control label', 'helpful'),
            'section' => 'helpful_design',
            'settings' => 'helpful_css',
            'type' => 'textarea',
        ];

        $wp_customize->add_control('helpful_css', $control);

        /* Details Section */

        $section = [
            'title' => esc_html_x('Details', 'customizer section title', 'helpful'),
            'description' => esc_html_x('Here you can adjust the details of Helpful.', 'customizer section description', 'helpful'),
            'priority' => 10,
            'panel' => 'helpful',
        ];

        $wp_customize->add_section('helpful_details', $section);

        /* Show Counters */

        $setting = [
            'type' => 'option',
        ];

        $wp_customize->add_setting('helpful_count_hide', $setting);

        $control = [
            'label' => esc_html_x('Hide counter', 'customizer control label', 'helpful'),
            'section' => 'helpful_details',
            'settings' => 'helpful_count_hide',
            'type' => 'checkbox',
        ];

        $wp_customize->add_control('helpful_count_hide', $control);

        /* Credits */

        $setting = [
            'type' => 'option',
        ];

        $wp_customize->add_setting('helpful_credits', $setting);

        $control = [
            'label' => esc_html_x('Show credits', 'customizer control label', 'helpful'),
            'section' => 'helpful_details',
            'settings' => 'helpful_credits',
            'type' => 'checkbox',
        ];

        $wp_customize->add_control('helpful_credits', $control);
    }
}
