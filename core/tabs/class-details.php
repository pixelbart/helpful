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

class Details
{
    /**
     * Class instance
     *
     * @var Details
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Details
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

        add_action('helpful_tab_details_before', [ & $this, 'register_tab_alerts']);
    }

    /**
     * Register settings for admin page
     *
     * @return void
     */
    public function register_settings()
    {
        $fields = [
            'helpful_credits' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_hide_in_content' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_post_types' => [
                'type' => 'array',
            ],
            'helpful_exists_hide' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_count_hide' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_widget' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_widget_amount' => [
                'type' => 'integer',
                'sanitize_callback' => 'intval'
            ],
            'helpful_widget_pro' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_widget_contra' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_widget_pro_recent' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_widget_contra_recent' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_only_once' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_percentages' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_form_status_pro' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_form_email_pro' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_form_status_contra' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_form_email_contra' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_metabox' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_widget_hide_publication' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_hide_admin_columns' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_shrink_admin_columns' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_widget' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_disabled' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_wordpress_user' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_ip_user' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];

        $fields = apply_filters('helpful_details_settings_group', $fields);

        foreach ($fields as $field => $args):
            register_setting('helpful-details-settings-group', $field, apply_filters('helpful_settings_group_args', $args, $field));
        endforeach;
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
        $tabs['details'] = [
            'id' => 'details',
            'name' => esc_html_x('Details', 'tab name', 'helpful'),
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
        if (!Helper::is_active_tab('details')) {
            return;
        }

        $post_types = get_post_types(['public' => true]);
        $private_post_types = get_post_types(['public' => false]);

        if (isset($private_post_types)) {
            $post_types = array_merge($post_types, $private_post_types);
        } else {
            $private_post_types = [];
        }

        $template = HELPFUL_PATH . 'templates/tabs/tab-details.php';

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
     * Filters the values of an option before saving them. Thus does not allow every HTML element
     * and makes Helpful a bit more secure.
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
