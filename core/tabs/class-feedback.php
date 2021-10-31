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

class Feedback
{
    /**
     * Class instance
     *
     * @var Feedback
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Feedback
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

        if (Helper::is_feedback_disabled()) {
            return;
        }

        add_filter('helpful_get_admin_tabs', [ & $this, 'register_tab'], 10, 2);
        add_action('helpful_tabs_content', [ & $this, 'register_tab_content']);

        add_action('helpful_tab_feedback_before', [ & $this, 'register_tab_alerts']);
    }

    /**
     * Register settings for admin page
     *
     * @return void
     */
    public function register_settings()
    {
        $fields = [
            'helpful_feedback_widget' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_after_pro' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_after_contra' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_message_pro' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_message_contra' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_messages_table' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_widget_overview' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_name' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_email' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_cancel' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_label_message' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_label_name' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_label_email' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_label_submit' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_label_cancel' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_gravatar' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_message_spam' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_after_vote' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_message_voted' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_amount' => [
                'type' => 'integer',
                'sanitize_callback' => 'intval',
            ],
            'helpful_feedback_send_email' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_receivers' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input_without_tags'],
            ],
            'helpful_feedback_subject' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input_without_tags'],
            ],
            'helpful_feedback_email_content' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
            'helpful_feedback_send_email_voter' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'helpful_feedback_subject_voter' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input_without_tags'],
            ],
            'helpful_feedback_email_content_voter' => [
                'type' => 'string',
                'sanitize_callback' => [ & $this, 'sanitize_input'],
            ],
        ];

        $fields = apply_filters('helpful_feedback_settings_group', $fields);

        foreach ($fields as $field => $args) {
            register_setting('helpful-feedback-settings-group', $field, apply_filters('helpful_settings_group_args', $args, $field));
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
        $tabs['feedback'] = [
            'id' => 'feedback',
            'name' => esc_html_x('Feedback', 'tab name', 'helpful'),
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
        if (!Helper::is_active_tab('feedback')) {
            return;
        }

        $template = HELPFUL_PATH . 'templates/tabs/tab-feedback.php';

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
