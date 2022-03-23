<?php
/**
 * @package Helpful
 * @subpackage Core\Helpers
 * @version 4.5.7
 * @since 1.0.0
 */
namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Feedback
{
    /**
     * Get feedback data by post object.
     *
     * @param object $entry post object.
     *
     * @return string json
     */
    public static function get_feedback($entry)
    {
        $post = get_post($entry->post_id);
        $time = strtotime($entry->time);

        $feedback = [];
        $feedback['id'] = $entry->id;
        $feedback['name'] = __('Anonymous', 'helpful');
        $feedback['message'] = nl2br($entry->message);
        $feedback['pro'] = $entry->pro;
        $feedback['contra'] = $entry->contra;
        $feedback['post'] = $post;
        $feedback['time'] = sprintf(
            /* translators: %s = time difference */
            __('Submitted %s ago', 'helpful'),
            human_time_diff($time, current_time('timestamp'))
        );

        if ($entry->fields) {
            $items = maybe_unserialize($entry->fields);
            if (is_array($items)) {
                foreach ($items as $label => $value) {
                    $feedback['fields'][$label] = $value;
                }
            }
        }

        $feedback['avatar'] = User::get_avatar();

        if (isset($feedback['fields']['email']) && '' !== $feedback['fields']['email']) {
            $feedback['avatar'] = User::get_avatar($feedback['fields']['email']);
        }

        if (isset($feedback['fields']['name']) && '' !== $feedback['fields']['name']) {
            $feedback['name'] = $feedback['fields']['name'];
        }

        $feedback = apply_filters('helpful_admin_feedback_item', $feedback, $entry);

        return json_decode(wp_json_encode($feedback));
    }

    /**
     * Get feedback items.
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param int $limit posts per page.
     *
     * @return object
     */
    public static function get_feedback_items($limit = null)
    {
        $options = new Services\Options();

        if (is_null($limit)) {
            $limit = intval($options->get_option('helpful_widget_amount', 3, 'intval'));
        }

        global $wpdb;

        $helpful = $wpdb->prefix . 'helpful_feedback';

        $query = "SELECT * FROM $helpful ORDER BY time DESC LIMIT %d";
        $query = $wpdb->prepare($query, $limit);
        $results = $wpdb->get_results($query);

        if ($results) {
            return $results;
        }

        return false;
    }

    /**
     * Insert feedback into database.
     *
     * @global $wpdb
     *
     * @return integer
     */
    public static function insert_feedback()
    {
        global $wpdb;

        $fields = [];
        $pro = 0;
        $contra = 0;
        $message = null;

        do_action('helpful/insert_feedback');

        if (!isset($_REQUEST['post_id'])) {
            $message = 'Helpful Notice: Feedback was not saved because the post id is empty in %s on line %d.';
            helpful_error_log(sprintf($message, __FILE__, __LINE__));
            return null;
        }

        $post_id = absint(sanitize_text_field(wp_unslash($_REQUEST['post_id'])));

        if (!isset($_REQUEST['message'])) {
            $message = 'Helpful Notice: Feedback was not saved because the message is empty in %s on line %d.';
            helpful_error_log(sprintf($message, __FILE__, __LINE__));
            return null;
        }

        $message = trim($_REQUEST['message']);

        if ('' === $message) {
            $message = 'Helpful Notice: Feedback was not saved because the message is empty in %s on line %d.';
            helpful_error_log(sprintf($message, __FILE__, __LINE__));
            return null;
        }

        if (helpful_backlist_check($_REQUEST['message'])) {
            $message = 'Helpful Notice: Feedback was not saved because the message contains blacklisted words in %s on line %d.';
            helpful_error_log(sprintf($message, __FILE__, __LINE__));
            return null;
        }

        $session = [];

        if (isset($_REQUEST['session'])) {
            $session = $_REQUEST['session'];
        }

        if (isset($_REQUEST['fields'])) {
            foreach ($_REQUEST['fields'] as $key => $value) {
                $fields[$key] = sanitize_text_field($value);
            }

            $fields = apply_filters('helpful_feedback_submit_fields', $fields, $session);
        }

        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $fields = [];

            $fields['name'] = $user->display_name;
            $fields['email'] = $user->user_email;

            $fields = apply_filters('helpful_feedback_submit_fields', $fields, $session);
        }

        if (isset($_REQUEST['message'])) {
            $message = sanitize_textarea_field(wp_strip_all_tags(wp_unslash($_REQUEST['message'])));
            $message = stripslashes($message);
            $message = apply_filters('helpful_feedback_submit_message', $message);
        }

        if (isset($_REQUEST['type'])) {
            $type = sanitize_text_field(wp_unslash($_REQUEST['type']));

            if ('pro' === $type) {
                $pro = 1;
            } elseif ('contra' === $type) {
                $contra = 1;
            }
        }

        $instance = null;
        if (isset($_REQUEST['instance'])) {
            $instance = sanitize_text_field($_REQUEST['instance']);
        }

        $user_id = esc_attr($_REQUEST['user_id']);

        $data = [
            'time' => current_time('mysql'),
            'user' => $user_id,
            'pro' => $pro,
            'contra' => $contra,
            'post_id' => $post_id,
            'message' => $message,
            'fields' => maybe_serialize($fields),
            'instance_id' => $instance,
        ];

        /* send email */
        self::send_email($data);

        $table_name = $wpdb->prefix . 'helpful_feedback';
        $wpdb->insert($table_name, $data);

        Stats::delete_widget_transient();

        return $wpdb->insert_id;
    }

    /**
     * Checks if the feedback already exists.
     *
     * @global $wdpb
     * 
     * @version 4.4.63
     * 
     * @param array $feedback_data
     * 
     * @return bool
     */
    public static function feedback_exists($data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'helpful_feedback';
        $sql = "SELECT COUNT(*) FROM $table_name WHERE user = %s AND pro = %d AND contra = %d AND post_id = %d AND message = %s AND instance_id = %d";
        $var = $wpdb->get_var($wpdb->prepare($sql, $data['user'], $data['pro'], $data['contra'], $data['post_id'], $data['message'], $data['instance_id']));
        return ($var) ? true : false;
    }

    /**
     * Send feedback email.
     *
     * @version 4.5.6
     *
     * @param array $feedback feedback data.
     *
     * @return void
     */
    public static function send_email($feedback)
    {
        $options = new Services\Options();

        if (true === self::feedback_exists($feedback)) {
            return;
        }

        /**
         * Send email to voter.
         */
        self::send_email_voter($feedback);

        if ('on' !== $options->get_option('helpful_feedback_send_email', 'off', 'esc_attr')) {
            return;
        }

        $post = get_post($feedback['post_id']);

        if (!$post) {
            return;
        }

        do_action('helpful/send_email');

        $feedback['fields'] = maybe_unserialize($feedback['fields']);

        $type = esc_html_x('positive', 'feedback type email', 'helpful');

        if (1 === $feedback['contra']) {
            $type = esc_html_x('negative', 'feedback type email', 'helpful');
        }

        /* tags */
        $tags = [
            '{type}' => $type,
            '{name}' => (isset($feedback['fields']['name'])) ? $feedback['fields']['name'] : '',
            '{email}' => (isset($feedback['fields']['email'])) ? $feedback['fields']['email'] : '',
            '{message}' => $feedback['message'],
            '{post_url}' => get_permalink($post),
            '{post_title}' => $post->post_title,
            '{blog_name}' => get_bloginfo('name'),
            '{blog_url}' => site_url(),
        ];

        $tags = apply_filters('helpful_feedback_email_tags', $tags);

        /* email subject */
        $subject = $options->get_option('helpful_feedback_subject', '', 'kses_wot');
        $subject = str_replace(array_keys($tags), array_values($tags), $subject);

        /* unserialize feedback fields */
        $feedback['fields'] = maybe_unserialize($feedback['fields']);

        $type = esc_html__('positive', 'helpful');
        if (1 === $feedback['contra']) {
            $type = esc_html__('negative', 'helpful');
        }

        /* body */
        $body = $options->get_option('helpful_feedback_email_content', '', 'kses');
        $body = str_replace(array_keys($tags), array_values($tags), $body);

        /* receivers by post meta */
        $post_receivers = [];

        if (get_post_meta($post->ID, 'helpful_feedback_receivers', true)) {
            $post_receivers = get_post_meta($post->ID, 'helpful_feedback_receivers', true);
            $post_receivers = helpful_trim_all($post_receivers);
            $post_receivers = explode(',', $post_receivers);
        }

        /* receivers by helpful options */
        $helpful_receivers = [];

        if ($options->get_option('helpful_feedback_receivers')) {
            $helpful_receivers = $options->get_option('helpful_feedback_receivers', '', 'esc_attr');
            $helpful_receivers = helpful_trim_all($helpful_receivers);
            $helpful_receivers = explode(',', $helpful_receivers);
        }

        $receivers = array_merge($helpful_receivers, $post_receivers);
        $receivers = array_unique($receivers);

        /* receivers array is empty */
        if (empty($receivers)) {
            return;
        }

        /* email headers */
        $headers = [];
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        if ($feedback['fields']['email']) {
            $headers[] = sprintf('Reply-To: %s', $feedback['fields']['email']);
        }

        /* filters */
        $receivers = apply_filters('helpful_feedback_email_receivers', $receivers, $feedback);
        $subject = apply_filters('helpful_feedback_email_subject', $subject, $feedback);
        $body = apply_filters('helpful_feedback_email_body', $body, $feedback);
        $headers = apply_filters('helpful_feedback_email_headers', $headers, $feedback);

        $response = wp_mail($receivers, $subject, $body, $headers);

        if (false === $response) {
            $message = 'Helpful Warning: Email could not be sent in %s on line %d.';
            helpful_error_log(sprintf($message, __FILE__, __LINE__));
        }
    }

    /**
     * Send feedback email to voter.
     *
     * @version 4.4.59
     *
     * @param array $feedback feedback data.
     *
     * @return void
     */
    public static function send_email_voter($feedback)
    {
        $options = new Services\Options();

        if ('on' !== $options->get_option('helpful_feedback_send_email_voter', 'off', 'esc_attr')) {
            return;
        }

        $post = get_post($feedback['post_id']);

        if (!$post) {
            return;
        }

        do_action('helpful/send_email_voter');

        $feedback['fields'] = maybe_unserialize($feedback['fields']);

        /* tags */
        $tags = [
            '{type}' => $type,
            '{name}' => isset($feedback['fields']['name']) ? $feedback['fields']['name'] : '',
            '{email}' => isset($feedback['fields']['email']) ? $feedback['fields']['email'] : '',
            '{message}' => $feedback['message'],
            '{post_url}' => get_permalink($post),
            '{post_title}' => $post->post_title,
            '{blog_name}' => get_bloginfo('name'),
            '{blog_url}' => site_url(),
        ];

        $tags = apply_filters('helpful_feedback_email_tags', $tags);

        /* subject */
        $subject = $options->get_option('helpful_feedback_subject_voter', '', 'kses_wot');
        $subject = str_replace(array_keys($tags), array_values($tags), $subject);

        /* unserialize feedback fields */
        $feedback['fields'] = maybe_unserialize($feedback['fields']);

        $type = esc_html__('positive', 'helpful');
        if (1 === $feedback['contra']) {
            $type = esc_html__('negative', 'helpful');
        }

        /* Body */
        $body = $options->get_option('helpful_feedback_email_content_voter', '', 'kses');
        $body = str_replace(array_keys($tags), array_values($tags), $body);

        /* Receivers */
        $receivers = [];

        if (isset($feedback['fields']['email']) && '' !== trim($feedback['fields']['email'])) {
            $receivers[] = sanitize_email($feedback['fields']['email']);
        }

        /* receivers array is empty */
        if (empty($receivers)) {
            return;
        }

        /* email headers */
        $headers = [];
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        /* filters */
        $receivers = apply_filters('helpful_feedback_email_receivers_voter', $receivers, $feedback);
        $subject = apply_filters('helpful_feedback_email_subject_voter', $subject, $feedback);
        $body = apply_filters('helpful_feedback_email_body_voter', $body, $feedback);
        $headers = apply_filters('helpful_feedback_email_headers_voter', $headers, $feedback);

        $response = wp_mail($receivers, $subject, $body, $headers);

        if (false === $response) {
            $message = 'Helpful Warning: Email could not be sent in %s on line %d.';
            helpful_error_log(sprintf($message, __FILE__, __LINE__));
        }
    }

    /**
     * Outputs the amount of feedback for a post.
     *
     * @global $wpdb
     *
     * @param int|null $post_id
     *
     * @return int
     */
    public static function get_feedback_count($post_id = null)
    {
        global $wpdb;

        $helpful = $wpdb->prefix . 'helpful_feedback';

        if (null === $post_id || !is_numeric($post_id)) {
            $sql = "SELECT COUNT(*) FROM $helpful";
            return $wpdb->get_var($sql);
        }

        $post_id = intval($post_id);
        $sql = "SELECT COUNT(*) FROM $helpful WHERE post_id = %d";

        $count = $wpdb->get_var($wpdb->prepare($sql, $post_id));

        return apply_filters('helpful_pre_get_feedback_count', $count, $post_id);
    }

    /**
     * Render after messages or feedback form, after vote.
     * Checks if custom template exists.
     *
     * @version 4.4.59
     * @since 4.4.0
     *
     * @param integer $post_id post id.
     * @param bool $show_feedback show feedback form anyway.
     *
     * @return string
     */
    public static function after_vote($post_id, $show_feedback = false)
    {
        do_action('helpful/after_vote');

        $options = new Services\Options();

        $hide_feedback = get_post_meta($post_id, 'helpful_hide_feedback_on_post', true);
        $hide_feedback = ('on' === $hide_feedback) ? true : false;

        if (Helper::is_feedback_disabled()) {
            $hide_feedback = true;
        }

        $user_id = User::get_user();
        $type = User::get_user_vote_status($user_id, $post_id);

        $accepted_types = ['pro', 'contra', 'none'];

        if (isset($_REQUEST['value']) && 'none' === $type) {
            if (in_array($_REQUEST['value'], $accepted_types)) {
                $type = sanitize_text_field(wp_unslash($_REQUEST['value']));
            }
        }

        if (true === $show_feedback) {
            $type = 'none';
            $feedback_text = $options->get_option('helpful_feedback_message_voted', '', 'kses');
            $feedback_text = apply_filters('helpful_pre_feedback_message_voted', $feedback_text, $post_id);
        }

        $ap = $options->get_option('helpful_feedback_after_pro', 'off', 'on_off');
        $ac = $options->get_option('helpful_feedback_after_contra', 'off', 'on_off');

        if ('pro' === $type) {
            $feedback_text = $options->get_option('helpful_feedback_message_pro', '', 'kses');

            if (false === $show_feedback) {
                if ('off' === $ap || true === $hide_feedback) {
                    $content = do_shortcode($options->get_option('helpful_after_pro', '', 'kses'));

                    if (get_post_meta($post_id, 'helpful_after_pro', true)) {
                        $content = do_shortcode(get_post_meta($post_id, 'helpful_after_pro', true));
                    }

                    return apply_filters('helpful_pre_after_pro', $content, $post_id);
                }
            }
        }

        if ('contra' === $type) {
            $feedback_text = $options->get_option('helpful_feedback_message_contra', '', 'kses');

            if (false === $show_feedback) {
                if ('off' === $ac || true === $hide_feedback) {
                    $content = do_shortcode($options->get_option('helpful_after_contra', '', 'kses'));

                    if (get_post_meta($post_id, 'helpful_after_contra', true)) {
                        $content = do_shortcode(get_post_meta($post_id, 'helpful_after_contra', true));
                    }

                    return apply_filters('helpful_pre_after_contra', $content, $post_id);
                }
            }
        }

        if ('none' === $type) {
            if ('off' === $ap && 'off' === $ac && false === $show_feedback) {
                $content = do_shortcode($options->get_option('helpful_after_fallback', '', 'kses'));

                if (get_post_meta($post_id, 'helpful_after_fallback', true)) {
                    $content = do_shortcode(get_post_meta($post_id, 'helpful_after_fallback', true));
                }

                return apply_filters('helpful_pre_after_fallback', $content, $post_id);
            }
        }

        if (isset($feedback_text) && '' === trim($feedback_text)) {
            $feedback_text = false;
        }

        if (!isset($feedback_text)) {
            $feedback_text = false;
        }

        $instance = null;
        if (isset($_REQUEST['instance'])) {
            $instance = sanitize_text_field($_REQUEST['instance']);
        }

        ob_start();

        $default_template = HELPFUL_PATH . 'templates/feedback.php';
        $custom_template = locate_template('helpful/feedback.php');

        do_action('helpful_before_feedback_form');

        echo '<form class="helpful-feedback-form">';

        printf('<input type="hidden" name="user_id" value="%s">', $user_id);
        printf('<input type="hidden" name="action" value="%s">', 'helpful_save_feedback');
        printf('<input type="hidden" name="post_id" value="%s">', $post_id);
        printf('<input type="hidden" name="type" value="%s">', $type);
        printf('<input type="hidden" name="instance" value="%s">', $instance);

        /**
         * Simple Spam Protection
         */
        $spam_protection = apply_filters('helpful_simple_spam_protection', true);

        if (!is_bool($spam_protection)) {
            $spam_protection = true;
        }

        if (true === $spam_protection) {
            echo '<input type="text" name="website" id="website" style="display:none;">';
        }

        wp_nonce_field('helpful_feedback_nonce');

        if ('' !== $custom_template) {
            include $custom_template;
        } else {
            include $default_template;
        }

        echo '</form>';

        do_action('helpful_after_feedback_form');

        $content = ob_get_contents();
        ob_end_clean();

        if (false !== $show_feedback) {
            $content = '<div class="helpful helpful-prevent-form"><div class="helpful-content" role="alert">' . $content . '</div></div>';
        }

        return apply_filters('helpful_pre_feedback', $content, $post_id);
    }

    /**
     * Get feedback email content.
     *
     * @version 4.5.7
     * 
     * @return string
     */
    public static function get_email_content()
    {
        $file = HELPFUL_PATH . '/templates/emails/feedback-email.txt';
        $file = apply_filters('helpful/emails/pre_feedback_email_file', $file);

        if (!file_exists($file)) {
            return '';
        }

        $response = wp_cache_get('helpful/templates/emails/feedback_email');

        if (false === $response) {
            ob_start();
            include $file;
            $response = ob_get_contents();
            ob_end_clean();
            wp_cache_set('helpful/templates/emails/feedback_email', $response);
        }

        return apply_filters('helpful_pre_get_email_content', $response);
    }

    /**
     * Get feedback email content for voters.
     *
     * @version 4.5.7
     * 
     * @return string
     */
    public static function get_email_content_voter()
    {
        $file = HELPFUL_PATH . '/templates/emails/feedback-email-voter.txt';
        $file = apply_filters('helpful/emails/pre_feedback_email_voter_file', $file);

        if (!file_exists($file)) {
            return '';
        }

        $response = wp_cache_get('helpful/templates/emails/feedback_voter_email');

        if (false === $response) {
            ob_start();
            include $file;
            $response = ob_get_contents();
            ob_end_clean();
            wp_cache_set('helpful/templates/emails/feedback_voter_email', $response);
        }

        return apply_filters('helpful_pre_get_email_content_voter', $response);
    }
}
