<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.4.59
 * @since 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Widget extends \Elementor\Widget_Base
{
    /**
     * Get widget name.
     *
     * @return string widget name.
     */
    public function get_name()
    {
        return 'helpful-widget';
    }

    /**
     * Get widget title.
     *
     * @return string widget title.
     */
    public function get_title()
    {
        return esc_html_x('Helpful', 'elementor widget name', 'helpful');
    }

    /**
     * Get widget icon.
     *
     * @return string widget icon.
     */
    public function get_icon()
    {
        return 'fa fa-thumbs-up';
    }

    /**
     * Get widget categories.
     *
     * @return array widget categories.
     */
    public function get_categories()
    {
        return ['general'];
    }

    /**
     * Register widget controls.
     *
     * @version 4.4.59
     *
     * @return void
     */
    protected function _register_controls()
    {
        $options = new Services\Options();

        $this->start_controls_section(
            'general',
            [
                'label' => esc_html_x('General', 'elementor tab name', 'helpful'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'helpful_credits',
            [
                'label' => esc_html_x('Show credits', 'elementor option name', 'helpful'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'on',
                'default' => 'on',
            ]
        );

        $this->add_control(
            'helpful_counter',
            [
                'label' => esc_html_x('Show counter', 'elementor option name', 'helpful'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'on',
                'default' => 'on',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'texts',
            [
                'label' => esc_html_x('Texts', 'elementor option name', 'helpful'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'helpful_heading',
            [
                'label' => esc_html_x('Headline', 'elementor option name', 'helpful'),
                'label_block' => true,
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => $options->get_option('helpful_heading', '', 'kses'),
            ]
        );

        $this->add_control(
            'helpful_content',
            [
                'label' => esc_html_x('Content', 'elementor option name', 'helpful'),
                'label_block' => true,
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => $options->get_option('helpful_content', '', 'kses'),
            ]
        );

        $this->add_control(
            'helpful_pro',
            [
                'label' => esc_html_x('Pro', 'elementor option name', 'helpful'),
                'label_block' => true,
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => $options->get_option('helpful_pro', '', 'kses'),
            ]
        );

        $this->add_control(
            'helpful_contra',
            [
                'label' => esc_html_x('Contra', 'elementor option name', 'helpful'),
                'label_block' => true,
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => $options->get_option('helpful_contra', '', 'kses'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'advanced',
            [
                'label' => esc_html_x('Advanced', 'elementor option name', 'helpful'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $themes = apply_filters('helpful_themes', false);
        $choices = [];

        foreach ($themes as $theme):
            $choices[$theme['id']] = $theme['label'];
        endforeach;

        $this->add_control(
            'helpful_theme',
            [
                'label' => esc_html_x('Theme', 'elementor option name', 'helpful'),
                'description' => esc_html_x('This option overrides the Helpful theme and applies to all Helpful on the site. You will also need to reload the page to see the changes.', 'elementor option description', 'helpful'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $choices,
                'default' => $options->get_option('helpful_theme', '', 'esc_attr'),
            ]
        );

        $this->add_control(
            'helpful_css',
            [
                'label' => esc_html_x('Custom CSS', 'elementor option name', 'helpful'),
                'label_block' => true,
                'description' => esc_html_x('Here you can use your own CSS. Use selector to address Helpful.', 'elementor option description', 'helpful'),
                'type' => \Elementor\Controls_Manager::CODE,
                'default' => 'selector {}',
                'language' => 'css',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     *
     * @return void
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $options = [
            "button_pro='{$settings['helpful_pro']}'",
            "button_contra='{$settings['helpful_contra']}'",
            "heading='{$settings['helpful_heading']}'",
            "content='{$settings['helpful_content']}'",
        ];

        update_option('helpful_theme', $settings['helpful_theme']);

        if ('on' !== $settings['helpful_credits']) {
            $options[] = "credits='false'";
        }

        if ('on' === (string) $settings['helpful_counter']) {
            $options[] = "counter='on'";
        }

        echo '<style>';
        echo str_replace('selector', '.helpful', $settings['helpful_css']);
        echo '</style>';
        echo do_shortcode('[helpful ' . implode(' ', $options) . ']');
    }
}
