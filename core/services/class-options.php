<?php
/**
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.5.8
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
        $options = maybe_unserialize(get_option('helpful_options', []));
        $this->options = (!is_array($options)) ? [] : $options;
    }

    /**
     * @return void
     */
    public function renew_options()
    {
        update_option('helpful_options', $this->options);
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
        // update_option($name, $value);
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

        // if an old option is still used
        delete_option($name);

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
    public function get_option($name, $default = false, $security = 'blank')
    {
        if (!is_string($name)) {
            return $default;
        }

        $option = null;

        if (get_option($name)) {
            $option = get_option($name);
        }

        $option = apply_filters('helpful/get_option/' . $name, $option);

        if (isset($this->options[$name])) {
            $option = $this->options[$name];
        }

        if (apply_filters('helpful/get_option/handle_security', true)) {
            $option = $this->handle_security($option, $security);
        }

        return ($option) ? $option : $default;
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
            case 'on_off':
                $value = ('on' === $value) ? 'on' : 'off';
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


    /**
     * @version 4.5.5
     * @since 4.5.0
     *
     * @param string $group
     *
     * @return array
     */
    public function get_defaults_array(string $group = '')
    {
        $feedback_email_content = Helpers\Feedback::get_email_content();
        $feedback_email_content_voter = Helpers\Feedback::get_email_content_voter();

        // old options? idk
        $options = [
            'helpful_success' => _x('Thank you for voting.', 'text after voting', 'helpful'),
            'helpful_error' => _x('Sorry, an error has occurred.', 'error after voting', 'helpful'),
        ];

        $groups = [
            'helpful-details-settings-group' => [
                'helpful_post_types' => ['post'],
                'helpful_exists_hide' => 'off',
                'helpful_count_hide' => 'off',
                'helpful_credits' => 'off',
                'helpful_hide_in_content' => 'off',
                'helpful_only_once' => 'off',
                'helpful_percentages' => 'off',
                'helpful_hide_admin_columns' => 'off',
                'helpful_shrink_admin_columns' => 'off',
                'helpful_feedback_disabled' => 'off',
                'helpful_ip_user' => 'off',
                'helpful_wordpress_user' => 'off',
                'helpful_metabox' => 'off',
                'helpful_widget' => 'off',
                'helpful_widget_pro' => 'off',
                'helpful_widget_contra' => 'off',
                'helpful_widget_pro_recent' => 'off',
                'helpful_widget_contra_recent' => 'off',
                'helpful_feedback_widget' => 'off',
                'helpful_widget_hide_publication' => 'off',
                'helpful_widget_amount' => 3,
                'helpful_shortcode_post_types' => 'off',
            ],
            'helpful-texts-settings-group' => [
                'helpful_heading' => _x('Was this post helpful?', 'default headline', 'helpful'),
                'helpful_content' => _x('Let us know if you liked the post. That’s the only way we can improve.', 'default description', 'helpful'),
                'helpful_exists' => _x('You have already voted for this post.', 'already voted', 'helpful'),
                'helpful_after_pro' => _x('Thank you for voting.', 'text after voting', 'helpful'),
                'helpful_after_contra' => _x('Thank you for voting.', 'text after voting', 'helpful'),
                'helpful_after_fallback' => _x('Thank you for voting.', 'text after voting', 'helpful'),
                'helpful_pro' => _x('Yes', 'text pro button', 'helpful'),
                'helpful_contra' => _x('No', 'text contra button', 'helpful'),
                'helpful_pro_disabled' => 'off',
                'helpful_contra_disabled' => 'off',
                'helpful_column_pro' => _x('Pro', 'column name', 'helpful'),
                'helpful_column_contra' => _x('Contra', 'column name', 'helpful'),
                'helpful_column_feedback' => _x('Feedback', 'column name', 'helpful'),
            ],
            'helpful-feedback-settings-group' => [
                'helpful_feedback_after_pro' => 'off',
                'helpful_feedback_after_contra' => 'off',
                'helpful_feedback_name' => 'off',
                'helpful_feedback_email' => 'off',
                'helpful_feedback_cancel' => 'off',
                'helpful_feedback_message_pro' => _x('Thank you for voting. You can now write me a few words, so I know what you particularly liked.', 'text after feedback pro', 'helpful'),
                'helpful_feedback_message_contra' => _x('Thank you for voting. You can now write me a few words so I know what you didn\'t like so much.', 'text after feedback contra', 'helpful'),
                'helpful_feedback_message_spam' => _x('Thank you for voting.', 'text after feedback spam', 'helpful'),
                'helpful_feedback_label_message' => _x('Message', 'label for feedback form field', 'helpful'),
                'helpful_feedback_label_name' => _x('Name', 'label for feedback form field', 'helpful'),
                'helpful_feedback_label_email' => _x('Email', 'label for feedback form field', 'helpful'),
                'helpful_feedback_label_submit' => _x('Send Feedback', 'label for feedback form field', 'helpful'),
                'helpful_feedback_label_cancel' => _x('Cancel', 'label for feedback form field', 'helpful'),
                'helpful_feedback_after_vote' => 'off',
                'helpful_feedback_message_voted' => _x('You have already voted. Do you still want to leave me a message?', 'text already feedback', 'helpful'),
                'helpful_feedback_gravatar' => 'off',
                'helpful_feedback_widget' => 'off',
                'helpful_feedback_amount' => 10,
                'helpful_feedback_send_email' => 'off',
                'helpful_feedback_receivers' => get_option('admin_email'),
                'helpful_feedback_subject' => _x('There\'s new feedback for you.', 'feedback email subject', 'helpful'),
                'helpful_feedback_email_content' => $feedback_email_content,
                'helpful_feedback_send_email_voter' => 'off',
                'helpful_feedback_subject_voter' => _x('Thanks for your feedback!', 'feedback email voter subject', 'helpful'),
                'helpful_feedback_email_content_voter' => $feedback_email_content_voter,
            ],
            'helpful-system-settings-group' => [
                'helpful_caching' => 'off',
                'helpful_caching_time' => '',
                'helpful_timezone' => date_default_timezone_get(),
                'helpful_multiple' => 'off',
                'helpful_notes' => 'off',
                'helpful_plugin_first' => 'off',
                'helpful_classic_editor' => 'off',
                'helpful_classic_widgets' => 'off',
                'helpful_disable_frontend_nonce' => 'off',
                'helpful_disable_feedback_nonce' => 'off',
                'helpful_user_random' => 'off',
                'helpful_sessions_false' => 'off',
                'helpful_cookies_samesite' => 'Strict',
                'helpful_export_separator' => ';',
                'helpful_uninstall' => 'off',
                'helpful_uninstall_feedback' => 'off',
            ],
        ];

        if ('' !== trim($group) && array_key_exists($group, $groups)) {
            return $groups[$group];
        }

        $options = [];
        foreach ($groups as $group => $_options) {
            $options = array_merge($options, $_options);
        } 

        return apply_filters('helpful/options/defaults', $options, array_keys($groups));
    }

    /**
     * @version 4.5.0
     * @since 4.5.0
     * 
     * @return void
     */
    public function sync_options()
    {
        $keys = array_keys($this->get_defaults_array());

        if (empty($keys)) {
            return;
        }

        delete_option('helpful_options');

        foreach ($keys as $key) {
            $this->update_option($key, $this->get_option($key));
            delete_option($key);
        }
    }
}
