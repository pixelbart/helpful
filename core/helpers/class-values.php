<?php
/**
 * @package Helpful
 * @subpackage Core\Helpers
 * @version 4.4.59
 * @since 4.3.0
 */
namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Values
{
    /**
     * Database table name for helpful
     *
     * @var string
     */
    protected static $table_helpful = 'helpful';

    /**
     * Database table name for helpful feedback
     *
     * @var string
     */
    protected static $table_feedback = 'helpful_feedback';

    /**
     * Defaults values for shortcodes.
     *
     * @global $helpful, $post
     * @version 4.4.59
     *
     * @return array
     */
    public static function get_defaults()
    {
        global $post;

        $post_id = null;

        if (!isset($post->ID)) {
            if (false !== get_the_ID()) {
                $post_id = get_the_ID();
            }
        } else {
            $post_id = $post->ID;
        }

        $options = new Services\Options();

        $credits = Helper::get_credits_data();
        $user_id = User::get_user();

        $values = [
            'heading_tag' => 'h3',
            'heading' => self::convert_tags($options->get_option('helpful_heading', '', 'kses'), $post_id),
            'content' => self::convert_tags($options->get_option('helpful_content', '', 'kses'), $post_id),
            'button_pro' => $options->get_option('helpful_pro', '', 'kses'),
            'button_contra' => $options->get_option('helpful_contra', '', 'kses'),
            'button_pro_disabled' => ('on' === $options->get_option('helpful_pro_disabled', 'off', 'esc_attr')) ? 1 : 0,
            'button_contra_disabled' => ('on' === $options->get_option('helpful_contra_disabled', 'off', 'esc_attr')) ? 1 : 0,
            'counter' => ('on' !== $options->get_option('helpful_count_hide', 'off', 'esc_attr')),
            'count_pro' => Stats::get_pro($post_id),
            'count_pro_percent' => Stats::get_pro($post_id, true),
            'count_contra' => Stats::get_contra($post_id),
            'count_contra_percent' => Stats::get_contra($post_id, true),
            'credits' => ('on' === $options->get_option('helpful_credits', 'on', 'esc_attr')),
            'credits_html' => $credits['html'],
            'exists' => User::check_user($user_id, $post_id) ? 1 : 0,
            'exists_text' => self::convert_tags($options->get_option('helpful_exists', '', 'kses'), $post_id),
            'exists_hide' => ('on' === $options->get_option('helpful_exists_hide', 'off', 'esc_attr')) ? 1 : 0,
            'post_id' => $post_id,
            'user_id' => User::get_user(),
        ];

        return apply_filters('helpful_default_values', $values);
    }

    /**
     * Convert tags to elements.
     *
     * @param string  $string  text string with tags.
     * @param integer $post_id post id.
     *
     * @return string
     */
    public static function convert_tags($string, $post_id)
    {

        $display_name = '';
        $author_id = get_post_field('post_author', $post_id);

        if ($author_id) {
            $display_name = get_the_author_meta('display_name', $author_id);
        }

        $pro = 0;
        $contra = 0;

        if (self::tag_exists('{pro},{contra},{total}', $string)) {
            $pro = Stats::get_pro($post_id);
            $contra = Stats::get_contra($post_id);
        }

        $tags = [
            '{pro}' => self::tag_exists('{pro}', $string) ? (int) $pro : null,
            '{contra}' => self::tag_exists('{contra}', $string) ? (int) $contra : null,
            '{total}' => self::tag_exists('{total}', $string) ? ((int) $pro + (int) $contra) : null,
            '{permalink}' => self::tag_exists('{permalink}', $string) ? esc_url(get_permalink($post_id)) : null,
            '{author}' => self::tag_exists('{author}', $string) ? $display_name : null,
            '{pro_percent}' => self::tag_exists('{pro_percent}', $string) ? Stats::get_pro($post_id, true) : null,
            '{contra_percent}' => self::tag_exists('{contra_percent}', $string) ? Stats::get_contra($post_id, true) : null,
            '{feedback_form}' => self::tag_exists('{feedback_form}', $string) ? Feedback::after_vote($post_id, true) : null,
            '{feedback_toggle}' => self::tag_exists('{feedback_toggle}', $string) ? self::get_post_feedback_toggle($post_id) : null,
        ];

        $tags = apply_filters('helpful_tags', $tags);

        $string = str_replace(array_keys($tags), array_values($tags), $string);

        return $string;
    }

    /**
     * Checks if the string contains a specific tag and returns bool.
     *
     * @param string $tag
     * @param string $string
     *
     * @return bool
     */
    public static function tag_exists($tag, $string)
    {
        /* multiple tags */
        if (strpos($tag, ',')) {
            $tags = explode(',', $tag);

            $matches = [];

            foreach ($tags as $tag) {
                if (true === self::tag_exists($tag, $string)) {
                    $matches[] = $tag;
                }
            }

            if (!empty($matches)) {
                return true;
            }

            return false;
        }

        /* single tag */
        if (strpos($string, $tag)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the feedback toggle button for a specific post.
     *
     * @param int $post_id
     *
     * @return string
     */
    public static function get_post_feedback_toggle($post_id)
    {
        return sprintf(
            '<div class="helpful-feedback-toggle-container"><button class="helpful-button helpful-toggle-feedback" type="button" role="button">%s</button><div hidden>%s</div></div>',
            _x('Give feedback', 'toggle feedback button', 'helpful'),
            Feedback::after_vote($post_id, true)
        );
    }

    /**
     * Get available tags for the settings screen
     *
     * @return array
     */
    public static function get_tags()
    {
        $tags = [
            '{pro}',
            '{contra}',
            '{total}',
            '{pro_percent}',
            '{contra_percent}',
            '{permalink}',
            '{author}',
        ];

        if (!Helper::is_feedback_disabled()):
            $tags[] = '{feedback_form}';
            $tags[] = '{feedback_toggle}';
        endif;

        return $tags;
    }

    /**
     * Insert helpful pro on single post
     *
     * @version 4.4.51
     * @since 4.4.0
     *
     * @param string  $user    user identicator.
     * @param integer $post_id post id.
     * @param string $instance
     *
     * @return mixed
     */
    public static function insert_pro($user, $post_id, $instance = null)
    {
        $status = Votes::insert_vote($user, $post_id, 'pro', $instance);

        Stats::delete_widget_transient();

        return $status;
    }

    /**
     * Insert helpful contra on single post
     *
     * @version 4.4.51
     * @since 4.4.0
     *
     * @param string  $user user identicator.
     * @param integer $post_id post id.
     * @param string $instance
     *
     * @return mixed
     */
    public static function insert_contra($user, $post_id, $instance = null)
    {
        $status = Votes::insert_vote($user, $post_id, 'contra', $instance);

        Stats::delete_widget_transient();

        return $status;
    }

    /**
     * Remove helpful stats from single post.
     *
     * @param int $post_id post id.
     *
     * @return void
     */
    public static function remove_data($post_id)
    {
        Votes::delete_vote_where(['post_id' => $post_id]);

        delete_post_meta($post_id, 'helpful-pro');
        delete_post_meta($post_id, 'helpful-contra');
        delete_post_meta($post_id, 'helpful_remove_data', 'yes');

        Optimize::clear_cache();
    }

    /**
     * Checks if tables exists and creates tables if not
     *
     * @param string $table_name database table name.
     *
     * @return array
     */
    public static function table_exists($table_name)
    {
        return Database::table_exists_or_setup($table_name);
    }

    /**
     * Setup helpful table
     *
     * @global $wpdb
     *
     * @return string
     */
    public static function setup_database_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_helpful;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id mediumint(9) DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;
		";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        return sprintf(
            /* translators: %s table name */
            esc_html_x("Table '%s' has been created.", 'maintenance response', 'helpful'),
            $table_name
        );
    }

    /**
     * Setup helpful feedback table
     *
     * @global $wpdb
     *
     * @return string
     */
    public static function setup_database_feedback_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table_feedback;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id mediumint(9) DEFAULT NULL,
		message text DEFAULT NULL,
		fields text DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;
		";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        return sprintf(
            /* translators: %s table name */
            esc_html_x("Table '%s' has been created.", 'maintenance response', 'helpful'),
            $table_name
        );
    }

    /**
     * Receive helpful data
     *
     * @return array
     */
    public static function get_data()
    {
        $query = Votes::get_votes(ARRAY_A);

        $results = [
            'count' => 0,
            'items' => [],
        ];

        if ($query) {
            $results = [
                'count' => count($query),
                'items' => $query,
            ];
        }

        return $results;
    }

    /**
     * Sync post meta
     * 
     * @version 4.4.59
     *
     * @return void
     */
    public static function sync_post_meta()
    {
        $transient = 'helpful_sync_meta';

        $options = new Services\Options();

        if (false === ($query = get_transient($transient))) {

            $post_types = $options->get_option('helpful_post_types', [], 'esc_attr');

            $args = [
                'post_type' => $post_types,
                'post_status' => 'publish',
                'fields' => 'ids',
                'posts_per_page' => -1,
            ];

            $query = new \WP_Query($args);
            $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
            $cache_times = Cache::get_cache_times(false);
            $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;

            set_transient($transient, $query, $cache_time);

            if ($query->found_posts) {
                foreach ($query->posts as $post_id):
                    update_post_meta($post_id, 'helpful-pro', Stats::get_pro($post_id, false));
                    update_post_meta($post_id, 'helpful-contra', Stats::get_contra($post_id, false));
                endforeach;
            }

            usleep(100000);
        }
    }
}
