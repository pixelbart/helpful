<?php
/**
 * @package Helpful
 * @subpackage Core\Helpers
 * @version 4.4.50
 * @since 4.3.0
 */
namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Cache
{
    /**
     * Returns the available times for caching with the Transients API from WordPress.
     *
     * @param boolean $labels Outputs either the miliseconds or the labels for the options.
     *
     * @return array
     */
    public static function get_cache_times($labels = true)
    {
        $times = [];

        $times['minute'] = esc_html_x('One minute', 'caching time', 'helpful');
        $times['hour'] = esc_html_x('One hour', 'caching time', 'helpful');
        $times['day'] = esc_html_x('One day', 'caching time', 'helpful');
        $times['week'] = esc_html_x('One week', 'caching time', 'helpful');
        $times['month'] = esc_html_x('One month', 'caching time', 'helpful');
        $times['year'] = esc_html_x('One year', 'caching time', 'helpful');

        if (false === $labels) {
            $times['minute'] = MINUTE_IN_SECONDS;
            $times['hour'] = HOUR_IN_SECONDS;
            $times['day'] = DAY_IN_SECONDS;
            $times['week'] = WEEK_IN_SECONDS;
            $times['month'] = MONTH_IN_SECONDS;
            $times['year'] = YEAR_IN_SECONDS;
        }

        return $times;
    }

    /**
     * Deletes all transients related to Helpful and clears the cache of Helpful.
     *
     * @return integer Amount of deleted entries.
     */
    public static function clear_cache()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'options';

        $count = 0;
        $sql = "SELECT * FROM $table_name WHERE option_name LIKE '_transient_timeout_helpful_%' OR option_name LIKE '_transient_helpful_%'";
        $rows = $wpdb->get_results($sql);

        if ($rows) {
            foreach ($rows as $row):
                if ('_transient_timeout_helpful_updated' === $row->option_name || '_transient_helpful_updated' === $row->option_name) {
                    continue;
                }

                $values = ['option_name' => $row->option_name];
                $wpdb->delete($table_name, $values);
                $count++;
            endforeach;
        }

        return $count;
    }
}
