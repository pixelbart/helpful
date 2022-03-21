<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.5.5
 * @since 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Module;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Frontend
{
    use Module;

    /**
     * Class Constructor
     *
     * @return void
     */
    public function __construct()
    {
        add_filter('helpful_themes', [ & $this, 'default_themes'], 1);
        add_action('wp_enqueue_scripts', [ & $this, 'enqueue_scripts'], 10);

        add_action('wp_ajax_helpful_save_vote', [ & $this, 'save_vote']);
        add_action('wp_ajax_helpful_save_feedback', [ & $this, 'save_feedback']);
        add_action('wp_ajax_nopriv_helpful_save_vote', [ & $this, 'save_vote']);
        add_action('wp_ajax_nopriv_helpful_save_feedback', [ & $this, 'save_feedback']);

        add_filter('the_content', [ & $this, 'the_content']);
        add_shortcode('helpful', [ & $this, 'helpful']);
        add_shortcode('helpful-feedback', [ & $this, 'helpful_feedback']);

        add_filter('init', [ & $this, 'filter_nonces']);

        add_action('wp_ajax_helpful_has_user_voted', [ & $this, 'has_user_voted']);
        add_action('wp_ajax_nopriv_helpful_has_user_voted', [ & $this, 'has_user_voted']);
    }

    /**
     * Retrieve default themes
     *
     * @param array $themes themes array.
     *
     * @return array
     */
    public function default_themes($themes)
    {
        $themes[] = [
            'id' => 'base',
            'label' => esc_html_x('Base', 'theme name', 'helpful'),
            'stylesheet' => plugins_url('core/assets/themes/base.css', HELPFUL_FILE),
        ];

        $themes[] = [
            'id' => 'dark',
            'label' => esc_html_x('Dark', 'theme name', 'helpful'),
            'stylesheet' => plugins_url('core/assets/themes/dark.css', HELPFUL_FILE),
        ];

        $themes[] = [
            'id' => 'minimal',
            'label' => esc_html_x('Minimal', 'theme name', 'helpful'),
            'stylesheet' => plugins_url('core/assets/themes/minimal.css', HELPFUL_FILE),
        ];

        $themes[] = [
            'id' => 'flat',
            'label' => esc_html_x('Flat', 'theme name', 'helpful'),
            'stylesheet' => plugins_url('core/assets/themes/flat.css', HELPFUL_FILE),
        ];

        $themes[] = [
            'id' => 'simple',
            'label' => esc_html_x('Simple', 'theme name', 'helpful'),
            'stylesheet' => plugins_url('core/assets/themes/simple.css', HELPFUL_FILE),
        ];

        $themes[] = [
            'id' => 'clean',
            'label' => esc_html_x('Clean', 'theme name', 'helpful'),
            'stylesheet' => plugins_url('core/assets/themes/clean.css', HELPFUL_FILE),
        ];

        $themes[] = [
            'id' => 'landkit',
            'label' => esc_html_x('Landkit', 'theme name', 'helpful'),
            'stylesheet' => plugins_url('core/assets/themes/landkit.css', HELPFUL_FILE),
        ];

        $themes[] = [
            'id' => 'blank',
            'label' => esc_html_x('Blank', 'theme name', 'helpful'),
            'stylesheet' => null,
        ];

        return $themes;
    }

    /**
     * Enqueue styles and scripts
     *
     * @global $post
     *
     * @version 4.4.59
     *
     * @since 4.4.0
     * 
     * @return void
     */
    public function enqueue_scripts()
    {
        if (helpful_is_amp()) {
            return __return_empty_string();
        }

        $options = new Services\Options();

        $customizer = $options->get_option('helpful_customizer', '');
        $active_theme = (is_array($customizer) && array_key_exists('theme', $customizer)) ? esc_attr($customizer['theme']) : 'base';
        
        $themes = apply_filters('helpful_themes', []);
        $plugin = Helper::get_plugin_data();

        if (!empty($themes)) {
            foreach ($themes as $theme) {
                if ($active_theme !== $theme['id']) {
                    continue;
                }

                wp_enqueue_style('helpful', $theme['stylesheet'], [], $plugin['Version']);
            }
        }

        $file = Helper::plugins_url('core/assets/js/helpful.js');
        wp_enqueue_script('helpful', $file, ['jquery'], $plugin['Version'], true);

        global $post;

        $post_id = (isset($post->ID)) ? $post->ID : 0;
        $user_id = Helpers\User::get_user();

        $vars = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_data' => [
                'user_id' => $user_id,
                '_wpnonce' => wp_create_nonce('helpful_frontend_nonce'),
            ],
            'translations' => [
                'fieldIsRequired' => __('This field is required.', 'helpful'),
            ],
            'user_voted' => [
                'user_id' => $user_id,
                'post_id' => $post_id,
                'action' => 'helpful_has_user_voted',
                '_wpnonce' => wp_create_nonce('helpful_has_user_voted'),
            ],
            'post_id' => $post_id,
        ];

        if (isset($_SESSION)) {
            $vars['ajax_session'] = apply_filters('helpful_ajax_session', $_SESSION);
        }

        if (false === apply_filters('helpful_verify_frontend_nonce', true) && isset($vars['ajax_data'])) {
            $vars['ajax_data'] = [];
        }

        $vars = apply_filters('helpful_frontend_ajax_vars', $vars);

        wp_localize_script('helpful', 'helpful', $vars);
    }

    /**
     * Add helpful to post content
     *
     * @global $post
     *
     * @version 4.4.59
     * @since 4.3.0
     *
     * @param string $content post content.
     *
     * @return string
     */
    public function the_content($content)
    {
        global $wp_query, $page, $numpages, $multipage, $more, $post;

        if (helpful_is_amp()) {
            return $content;
        }

        if (!isset($post->ID)) {
            return $content;
        }

        if (apply_filters('helpful/the_content/disabled', false, $post)) {
            return $content;
        }

        $options = new Services\Options();

        $helpful = Helpers\Values::get_defaults();
        $post_types = $options->get_option('helpful_post_types', [], 'esc_attr');
        $user_id = Helpers\User::get_user();

        if ('on' === get_post_meta($helpful['post_id'], 'helpful_hide_on_post', true)) {
            return $content;
        }

        if (!is_array($post_types) || !in_array($post->post_type, $post_types, true)) {
            return $content;
        }

        if ('on' === $options->get_option('helpful_hide_in_content', 'off', 'on_off')) {
            return $content;
        }

        $conditions = Helper::get_conditions();

        if (!empty($conditions)) {
            return $content;
        }

        $shortcode = '[helpful post_id="' . $helpful['post_id'] . '"]';

        if (apply_filters('helpful/the_content/is_multipage', $multipage)) {
            if ($page === $numpages) {
                return $content . $shortcode;
            }
    
            return $content;
        }
    
        return $content . $shortcode;
    }

    /**
     * Callback for helpful shortcode
     *
     * @global $post
     *
     * @version 4.5.5
     * @since 4.3.0
     *
     * @param array  $atts shortcode attributes.
     * @param string $content shortcode content.
     *
     * @return string
     */
    public function helpful($atts, $content = '')
    {
        global $post;

        if (helpful_is_amp()) {
            return $content;
        }

        if (apply_filters('helpful/shortcode/disabled', false, $post)) {
            return $content;
        }

        $options = new Services\Options();

        if ('on' === $options->get_option('helpful_shortcode_post_types', 'off', 'esc_attr')) {
            if (isset($post->post_type) && !in_array($post->post_type, (array) $options->get_option('helpful_post_types', [], 'esc_attr'))) {
                return $content;
            }
        }

        $defaults = Helpers\Values::get_defaults();
        $defaults = apply_filters('helpful_shortcode_defaults', $defaults);

        $helpful = shortcode_atts($defaults, $atts);
        $helpful = apply_filters('helpful_shortcode_atts', $helpful);

        $user_id = Helpers\User::get_user();

        $object = new Services\Helpful($helpful['post_id'], $helpful);

        if ('on' === $options->get_option('helpful_exists_hide', 'off', 'on_off') && $object->current_user_has_voted()) {
            return $content;
        }

        $helpful['exists'] = ($object->current_user_has_voted()) ? 1 : 0;

        $exists = false;
        $hidden = false;
        $class = '';

        if (isset($helpful['exists']) && 1 === $helpful['exists']) {
            if (isset($helpful['exists-hide']) && 1 === $helpful['exists-hide']) {
                return $content;
            }

            $exists = true;
            $hidden = true;
            $class = 'helpful-exists';
            $helpful['content'] = $helpful['exists_text'];
        }

        if (null === $helpful['post_id']) {
            return $content;
        }

        if (1 === $helpful['exists'] && 'on' === $options->get_option('helpful_feedback_after_vote', 'off', 'on_off')) {
            if (!Helper::is_feedback_disabled()) {
                $content = Helpers\Feedback::after_vote($helpful['post_id'], true);
                $content = Helpers\Values::convert_tags($content, $helpful['post_id']);
                return $content;
            }
        }

        if (get_post_meta($helpful['post_id'], 'helpful_heading', true)) {
            $helpful['heading'] = do_shortcode(get_post_meta($helpful['post_id'], 'helpful_heading', true));
        }

        if (get_post_meta($helpful['post_id'], 'helpful_pro', true)) {
            $helpful['button_pro'] = do_shortcode(get_post_meta($helpful['post_id'], 'helpful_pro', true));
        }

        if (get_post_meta($helpful['post_id'], 'helpful_contra', true)) {
            $helpful['button_contra'] = do_shortcode(get_post_meta($helpful['post_id'], 'helpful_contra', true));
        }

        $helpful['shortcode_exists'] = $exists;
        $helpful['shortcode_hidden'] = $hidden;
        $helpful['shortcode_class'] = $class;

        $object->set_atts($helpful);

        return $object->get_template();
    }

    /**
     * Ajax save user vote and render response.
     *
     * @version 4.4.59
     * @since 4.4.0
     *
     * @return void
     */
    public function save_vote()
    {
        if (apply_filters('helpful_verify_frontend_nonce', true)) {
            check_ajax_referer('helpful_frontend_nonce');
        }

        do_action('helpful_ajax_save_vote');

        $user_id = null;
        $post_id = null;
        $value = null;
        $response = '';

        $options = new Services\Options();

        if (isset($_POST['user_id'])) {
            $user_id = sanitize_text_field($_POST['user_id']);
        }

        if (isset($_POST['post'])) {
            $post_id = intval($_POST['post']);
        }

        if (isset($_POST['value'])) {
            $value = sanitize_text_field($_POST['value']);
        }

        if (is_user_logged_in() && 'on' === $options->get_option('helpful_wordpress_user', 'off', 'on_off')) {
            $user_id = get_current_user_id();
        }

        $instance = null;
        if (isset($_POST['instance'])) {
            $instance = sanitize_text_field($_POST['instance']);
        }

        if (false === Helpers\User::check_user($user_id, $post_id)) {
            if ('pro' === $value) {
                Helpers\Values::insert_pro($user_id, $post_id, $instance);
            } else {
                Helpers\Values::insert_contra($user_id, $post_id, $instance);
            }

            $response = do_shortcode(Helpers\Feedback::after_vote($post_id));
        }

        $response = Helpers\Values::convert_tags($response, $post_id);
        echo apply_filters('helpful_pre_save_vote', $response, $post_id);
        wp_die();
    }

    /**
     * Ajax save user feedback and render response.
     *
     * @version 4.4.59
     * @since 4.4.0
     *
     * @return void
     */
    public function save_feedback()
    {
        if (apply_filters('helpful_verify_feedback_nonce', true)) {
            check_ajax_referer('helpful_feedback_nonce');
        }

        do_action('helpful_ajax_save_feedback');

        $options = new Services\Options();

        $post_id = null;
        if (isset($_REQUEST['post_id']) && is_numeric($_REQUEST['post_id'])) {
            $post_id = intval($_REQUEST['post_id']);
        }

        $spam_protection = apply_filters('helpful_simple_spam_protection', '1');

        if ('1' !== $spam_protection) {
            $spam_protection = false;
        } else {
            $spam_protection = true;
        }

        if (!empty($_REQUEST['website']) && true === $spam_protection) {
            $message = do_shortcode($options->get_option('helpful_feedback_message_spam', '', 'kses'));
            $message = apply_filters('helpful_pre_feedback_message_spam', $message, $post_id);
            echo Helpers\Values::convert_tags($message, $post_id);
            wp_die();
        }

        if (!isset($_REQUEST['helpful_cancel'])) {
            Helpers\Feedback::insert_feedback();
        }

        global $helpful_type;

        $user_id = Helpers\User::get_user();
        $type = Helpers\User::get_user_vote_status($user_id, $post_id);

        $helpful_type[$post_id] = $type;

        if ('pro' === $type) {
            $message = do_shortcode($options->get_option('helpful_after_pro', '', 'kses'));

            if (get_post_meta($post_id, 'helpful_after_pro', true)) {
                $message = do_shortcode(get_post_meta($post_id, 'helpful_after_pro', true));
            }

            $message = apply_filters('helpful_pre_after_pro', $message, $post_id);
        } elseif ('contra' === $type) {
            $message = do_shortcode($options->get_option('helpful_after_contra', '', 'kses'));

            if (get_post_meta($post_id, 'helpful_after_contra', true)) {
                $message = do_shortcode(get_post_meta($post_id, 'helpful_after_contra', true));
            }

            $message = apply_filters('helpful_pre_after_contra', $message, $post_id);
        } else {
            $message = do_shortcode($options->get_option('helpful_after_fallback', '', 'kses'));

            if (get_post_meta($post_id, 'helpful_after_fallback', true)) {
                $message = do_shortcode(get_post_meta($post_id, 'helpful_after_fallback', true));
            }

            $message = apply_filters('helpful_pre_after_fallback', $message, $post_id);
        }

        $message = Helpers\Values::convert_tags($message, $post_id);
        echo apply_filters('helpful_pre_save_feedback', $message, $post_id);
        wp_die();
    }

    /**
     * Filters the frontend nonces and set the value to false, using option.
     *
     * @version 4.4.59
     *
     * @return void
     */
    public function filter_nonces()
    {
        $options = new Services\Options();

        if ('on' === $options->get_option('helpful_disable_frontend_nonce', 'off', 'esc_attr')) {
            add_filter('helpful_verify_frontend_nonce', '__return_false');
        }

        if ('on' === $options->get_option('helpful_disable_feedback_nonce', 'off', 'esc_attr')) {
            add_filter('helpful_verify_feedback_nonce', '__return_false');
        }
    }

    /**
     * Allows to output feedback for the current or a specific post.
     *
     * @global $wpdb
     *
     * @param array $atts
     *
     * @return string
     */
    public function helpful_feedback($atts)
    {
        global $post;

        $defaults = [
            'post' => $post->ID,
            'gravatar' => false,
        ];

        $atts = shortcode_atts($defaults, $atts);

        if (!is_numeric($atts['post'])) {
            $atts['post'] = $post->ID;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'helpful_feedback';

        $query = "SELECT * FROM $table_name WHERE post_id = %d ORDER BY id DESC";

        $rows = $wpdb->get_results($wpdb->prepare($query, intval($atts['post'])));

        if ($rows) {
            $items = [];

            foreach ($rows as $row):
                $fields = maybe_unserialize($row->fields);
                $class = (isset($row->contra) && 1 === intval($row->contra)) ? 'contra' : 'pro';
                $author = (isset($fields['name']) && '' !== trim($fields['name'])) ? esc_html($fields['name']) : esc_html__('Anonymous', 'helpful');
                $email = (isset($fields['email']) && '' !== trim($fields['email'])) ? sanitize_email($fields['email']) : '';
                $message = (isset($row->message) && '' !== trim($row->message)) ? esc_html($row->message) : null;

                if (is_null($message)) {
                    continue;
                }

                if (false !== $atts['gravatar']) {
                    $avatar = get_avatar_url($email);
                }

                if (isset($avatar)) {
                    $html = '<li class="helpful-feedback-item helpful-feedback-item-%1$s"><div class="helpful-feedback-author --flex"><div class="--avatar"><img src="%2$s" alt="%3$s"></div><div class="--name">%3$s</div></div><div class="helpful-feedback-message">%4$s</div></li>';
                    $items[] = sprintf($html, $class, $avatar, $author, $message);
                } else {
                    $html = '<li class="helpful-feedback-item helpful-feedback-item-%1$s"><div class="helpful-feedback-author"><div class="--name">%2$s</div></div><div class="helpful-feedback-message">%3$s</div></li>';
                    $items[] = sprintf($html, $class, $author, $message);
                }
            endforeach;

            return sprintf('<ul class="helpful-feedback-items">%s</ul>', implode('', $items));
        }

        return '';
    }

    /**
     * @return void
     */
    public function has_user_voted()
    {
        check_ajax_referer('helpful_has_user_voted');

        $errors = [];
        $post_id = null;
        $user_id = null;

        if (isset($_REQUEST['post_id']) && is_numeric($_REQUEST['post_id'])) {
            $post_id = intval($_REQUEST['post_id']);
        } else {
            $errors[] = _x('No post has been found.', 'ajax error message', 'helpful');
        }

        if (isset($_REQUEST['user_id']) && '' !== trim($_REQUEST['user_id'])) {
            $user_id = sanitize_text_field(wp_unslash($_REQUEST['user_id']));
        } else {
            $errors[] = _x('No user has been found.', 'ajax error message', 'helpful');
        }

        if (!empty($errors)) {
            wp_send_json_error($errors);
        }

        $status = Helpers\User::check_user($user_id, $post_id);

        if (!$status) {
            $status = 0;
        }

        wp_send_json_success($status);
    }
}
