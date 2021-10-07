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

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Texts
{
    /**
     * Class instance
     *
     * @var Texts
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Texts
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
        add_action('admin_init', [ & $this, 'register_settings']);

        add_filter('helpful_get_admin_tabs', [ & $this, 'register_tab'], 10, 2);
        add_action('helpful_tabs_content', [ & $this, 'register_tab_content']);

        add_action('helpful_tab_texts_before', [ & $this, 'register_tab_alerts']);
    }

    /**
     * Register settings for admin page
     *
     * @return void
     */
    public function register_settings()
    {
        $fields = [
            'helpful_heading' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],   
            ],
            'helpful_content' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ], 
            ],
            'helpful_pro' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_pro_disabled' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_exists' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_contra' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_contra_disabled' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_column_pro' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_column_contra' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_column_feedback' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_after_pro' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_after_contra' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
            'helpful_after_fallback' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input' ],
            ],
        ];

        $fields = apply_filters('helpful_texts_settings_group', $fields);

        foreach ($fields as $field => $args) {
            register_setting('helpful-texts-settings-group', $field, apply_filters('helpful_settings_group_args', $args, $field));
        }
    }

    /**
     * Register tab in tabs list.
     *
     * @param array $tabs
     * @param string $current
     *
     * @return array
     */
    public function register_tab($tabs, $current)
    {
        $tabs['texts'] = [
            'id' => 'texts',
            'name' => esc_html_x('Texts', 'tab name', 'helpful'),
        ];

        return $tabs;
    }

    /**
     * Register tab content.
     *
     * @return void
     */
    public function register_tab_content()
    {
        if (!Helper::is_active_tab('texts')) {
            return;
        }

        $template = HELPFUL_PATH . 'templates/tabs/tab-texts.php';

        if (file_exists($template)) {
            include_once $template;
        }
    }

    /**
     * Register tab alerts for settings saved and other.
     *
     * @return void
     */
    public function register_tab_alerts()
    {
        if (isset($_GET['settings-updated'])) {
            $message = esc_html_x('Settings saved.', 'tab alert after save', 'helpful');
            echo Helper::get_alert($message, 'success', 1500);
        }
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
