<?php
/**
 * @package Helpful
 * @subpackage Core\Helpers
 * @version 4.4.50
 * @since 4.3.0
 */
namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Optimize
{
    /**
     * Executes the methods and returns a response array.
     *
     * @return array
     */
    public static function optimize_plugin()
    {
        delete_option('helpful_is_installed');
        delete_option('helpful_feedback_is_installed');

        $response = [];
        $response = array_merge($response, Values::table_exists('helpful'));
        $response = array_merge($response, Values::table_exists('helpful_feedback'));
        $response = array_merge($response, self::optimize_tables());
        $response = array_merge($response, self::move_feedback());
        $response = array_merge($response, self::remove_incorrect_entries());
        $response = array_merge($response, self::fix_incorrect_feedback());
        $response = array_merge($response, self::clear_cache());
        $response = array_merge($response, self::update_metas());

        array_filter($response);

        return $response;
    }

    /**
     * Optimizes database tables.
     *
     * Optimize tables `helpful` and `helpful_feedback`.
     * Uses the SQL-Command `OPTIMIZE` for optimization.
     *
     * @global $wpdb
     *
     * @return array responses
     */
    private static function optimize_tables()
    {
        global $wpdb;
        $response = [];

        /* OPTIMIZE helpful table */
        $table_name = $wpdb->prefix . 'helpful';
        if ($wpdb->query("OPTIMIZE TABLE $table_name")) {
            /* translators: %s = table name */
            $response[] = sprintf(esc_html_x("Table '%s' has been optimized.", 'maintenance response', 'helpful'), $table_name);
        }

        /* OPTIMIZE helpful_feedback table */
        $table_name = $wpdb->prefix . 'helpful_feedback';
        if ($wpdb->query("OPTIMIZE TABLE $table_name")) {
            /* translators: %s = table name */
            $response[] = sprintf(esc_html_x("Table '%s' has been optimized.", 'maintenance response', 'helpful'), $table_name);
        }

        return $response;
    }

    /**
     * Moves feedback from post type to database table.
     *
     * Moves the feedback from post type `helpful_feedback` to the database
     * table `helpful_feedback` and returns a response array.
     *
     * @global $wpdb
     *
     * @return array response
     */
    private static function move_feedback()
    {
        global $wpdb;

        $response = [];

        $args = [
            'post_type' => 'helpful_feedback',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        $query = new \WP_Query($args);

        if (!$query->found_posts) {
            return [];
        }

        $count = $query->found_posts;

        foreach ($query->posts as $post_id):

            $type = get_post_meta($post_id, 'type', true);

            $fields = [
                'browser' => get_post_meta($post_id, 'browser', true),
                'platform' => get_post_meta($post_id, 'platform', true),
                'language' => get_post_meta($post_id, 'language', true),
            ];

            $data = [
                'time' => get_the_time('Y-m-d H:i:s', $post_id),
                'user' => 0,
                'pro' => ('Pro' === $type ? 1 : 0),
                'contra' => ('Contra' === $type ? 1 : 0),
                'post_id' => get_post_meta($post_id, 'post_id', true),
                'message' => get_post_field('post_content', $post_id),
                'fields' => maybe_serialize($fields),
            ];

            /* insert post into database */
            $table_name = $wpdb->prefix . 'helpful_feedback';
            $wpdb->insert($table_name, $data);

            /* delete post */
            if ($wpdb->insert_id) {
                wp_delete_post($post_id, true);
            }
        endforeach;

        /* translators: %d = amount of entries */
        $response[] = sprintf(esc_html_x('%d Feedback entries moved in the database', 'maintenance response', 'helpful'), $count);

        return $response;
    }

    /**
     * Removes incorrect entries from database tables.
     *
     * Remove incorrect entries from database tables `helpful`
     * and `helpful_feedback`. All entries that do not have a
     * user saved are affected.
     *
     * @global $wpdb
     *
     * @return array responses
     */
    private static function remove_incorrect_entries()
    {
        global $wpdb;

        $response = [];

        /* Remove incorrect entries from 'helpful' table */
        $table_name = $wpdb->prefix . 'helpful';
        $query = $wpdb->prepare("SELECT id, user FROM $table_name WHERE user = %s", '');
        $items = $wpdb->get_results($query);

        if ($items) {
            foreach ($items as $item):
                $wpdb->delete($table_name, ['id' => $item->id]);
            endforeach;

            $count = count($items);
            $response[] = sprintf(
                /* translators: %1$d = amount of entries %2$s = table name */
                esc_html_x('%1$d incorrect entries have been removed from table "%2$s"', 'maintenance response', 'helpful'),
                $count,
                $table_name
            );
        }

        /* Remove incorrect entries from 'helpful_feedback' table */
        $table_name = $wpdb->prefix . 'helpful_feedback';
        $query = $wpdb->prepare("SELECT id, user FROM $table_name WHERE user = %s", '');
        $items = $wpdb->get_results($query);

        if ($items) {
            foreach ($items as $item) {
                $wpdb->delete($table_name, ['id' => $item->id]);
            }

            $count = count($items);
            $response[] = sprintf(
                /* translators: %1$d = amount of entries %2$s = table name */
                esc_html_x('%1$d incorrect entries have been removed from table "%2$s"', 'maintenance response', 'helpful'),
                $count,
                $table_name
            );
        }

        return $response;
    }

    /**
     * Feedback text is cleaned up and slashes removed.
     *
     * @global $wpdb
     *
     * @return array
     */
    public static function fix_incorrect_feedback()
    {
        global $wpdb;

        $response = [];
        $table_name = $wpdb->prefix . 'helpful_feedback';
        $query = "SELECT id, message FROM $table_name";
        $items = $wpdb->get_results($query);
        $fixes = [];

        if (!empty($items)) {
            foreach ($items as $item):
                if (false !== strpos($item->message, '\\')) {
                    $fixes[] = $item->id;
                    $message = sanitize_textarea_field(wp_strip_all_tags($item->message));
                    $message = stripslashes($message);
                    $wpdb->update($table_name, ['message' => $message], ['id' => $item->id]);
                }
            endforeach;
        }

        if (is_array($fixes) && !empty($fixes)) {
            $count = count($fixes);
            $response[] = sprintf(
                /* translators: %1$d = amount of entries %2$s = table name */
                esc_html_x('%1$d incorrect entries have been fixed from table "%2$s".', 'maintenance response', 'helpful'),
                $count,
                $table_name
            );
        }

        return $response;
    }

    /**
     * Feedback text is cleaned up and slashes removed.
     *
     * @return array
     */
    public static function clear_cache()
    {

        $response = [
            esc_html_x('The cache for Helpful has been cleared.', 'maintenance response', 'helpful'),
        ];

        wp_cache_delete('stats_total', 'helpful');
        wp_cache_delete('stats_total_pro', 'helpful');
        wp_cache_delete('stats_total_contra', 'helpful');

        /**
         * Deletes all transients related to Helpful.
         */
        $count = 3;
        $count += Cache::clear_cache();

        return $response;
    }

    /**
     * Update meta fields
     *
     * @version 4.4.59
     *
     * @return array
     */
    public static function update_metas()
    {
        $options = new Services\Options();

        $response = [];
        $post_types = $options->get_option('helpful_post_types', [], 'esc_attr');

        $args = [
            'post_type' => $post_types,
            'post_status' => 'any',
            'fields' => 'ids',
        ];

        $query = new \WP_Query($args);

        if ($query->found_posts) {
            foreach ($query->posts as $post_id):

                $percentages = false;

                if ('on' === $options->get_option('helpful_percentages', 'off', 'esc_attr')) {
                    $percentages = true;
                }

                $pro = Stats::get_pro($post_id, $percentages);
                $contra = Stats::get_contra($post_id, $percentages);

                update_post_meta($post_id, 'helpful-pro', $pro);
                update_post_meta($post_id, 'helpful-contra', $contra);

            endforeach;
        }

        $count = $query->found_posts;
        $response[] = sprintf(
            /* translators: %1$d = amount of entries */
            esc_html_x('%1$d post meta fields have been updated.', 'maintenance response', 'helpful'),
            $count
        );

        return $response;
    }
}
