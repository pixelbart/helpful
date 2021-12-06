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

class Stats
{
    /**
     * Helpful color helper
     *
     * @var string
     */
    public static $green = '#88c057';

    /**
     * Helpful color helper
     *
     * @var string
     */
    public static $red = '#ed7161';

    /**
     * Todo: Helper for wp_date since WorddPress 5.4.
     * Todo: Currently there is a bug with the time zone here.
     *
     * @url https://developer.wordpress.org/reference/functions/date_i18n/
     *
     * @param string      $format Format to display the date.
     * @param int|boolean $timestamp_with_offset Whether to use GMT timezone. Only applies if timestamp is not provided.
     * @param boolean     $gmt Whether to use GMT timezone. Only applies if timestamp is not provided.
     * @return string The date, translated if locale specifies it.
     */
    private static function helpful_date($format, $timestamp_with_offset = false, $gmt = false)
    {
        if (function_exists('wp_date')) {
            return wp_date($format, $timestamp_with_offset, $gmt);
        } elseif (function_exists('date_i18n')) {
            return date_i18n($format, $timestamp_with_offset, $gmt);
        } else {
            return date($format, $timestamp_with_offset);
        }
    }

    /**
     * Get amount of pro by post id.
     *
     * @global $wpdb, $post
     * @version 4.4.59
     *
     * @param int  $post_id     if null current post id.
     * @param bool $percentages return percentage values on true.
     *
     * @return string
     */
    public static function get_pro($post_id = null, $percentages = false)
    {
        if (is_null($post_id)) {
            global $post;

            if (!isset($post->ID)) {
                return 0;
            }

            $post_id = $post->ID;
        }

        global $wpdb;

        $options = new Services\Options();

        $post_id = absint($post_id);
        $helpful = $wpdb->prefix . 'helpful';
        $sql = $wpdb->prepare("SELECT COUNT(*) FROM $helpful WHERE pro = 1 AND post_id = %d", intval($post_id));

        $cache_name = 'helpful_pro_' . $post_id;
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $var = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $var = $wpdb->get_var($sql);
        } elseif (false === $var) {
            $var = $wpdb->get_var($sql);
            set_transient($cache_name, maybe_serialize($var), $cache_time);
        }

        $var = maybe_unserialize($var);

        if (false === $percentages) {
            return $var;
        }

        $pro = $var ?: 0;
        $contra = self::get_contra($post_id);
        $percentage = 0;

        if (0 !== $pro) {
            $average = (int) ($pro - $contra);
            $total = (int) ($pro + $contra);
            $percentage = ($pro / $total) * 100;
            $percentage = round($percentage, 2);
            $percentage = number_format($percentage, 2);
        }

        return str_replace('.00', '', $percentage);
    }

    /**
     * Get contra count by post id.
     *
     * @global $wpdb, $post
     * @version 4.4.59
     *
     * @param int  $post_id     if null current post id.
     * @param bool $percentages return percentage values on true.
     *
     * @return string
     */
    public static function get_contra($post_id = null, $percentages = false)
    {
        if (is_null($post_id)) {
            global $post, $wpdb;

            if (!isset($post->ID)) {
                return 0;
            }

            $post_id = $post->ID;
        }

        global $wpdb;

        $options = new Services\Options();

        $post_id = absint($post_id);
        $helpful = $wpdb->prefix . 'helpful';
        $sql = $wpdb->prepare("SELECT COUNT(*) FROM $helpful WHERE contra = 1 AND post_id = %d", intval($post_id));

        $cache_name = 'helpful_contra_' . $post_id;
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $var = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $var = $wpdb->get_var($sql);
        } elseif (false === $var) {
            $var = $wpdb->get_var($sql);
            set_transient($cache_name, maybe_serialize($var), $cache_time);
        }

        $var = maybe_unserialize($var);

        if (false === $percentages) {
            return $var;
        }

        $contra = $var ?: 0;
        $pro = self::get_pro($post_id);
        $percentage = 0;

        if (0 !== $contra) {
            $average = (int) ($contra - $pro);
            $total = (int) ($contra + $pro);
            $percentage = ($contra / $total) * 100;
            $percentage = round($percentage, 2);
            $percentage = number_format($percentage, 2);
        }

        return str_replace('.00', '', $percentage);
    }

    /**
     * Get pro count of all posts.
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param bool $percentages return percentage values on true.
     *
     * @return int count
     */
    public static function get_pro_all($percentages = false)
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $sql = "SELECT COUNT(*) FROM $helpful WHERE pro = 1";

        $cache_name = 'helpful_pro_all';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $var = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $var = $wpdb->get_var($sql);
        } elseif (false === $var) {
            $var = $wpdb->get_var($sql);
            set_transient($cache_name, maybe_serialize($var), $cache_time);
        }

        $var = maybe_unserialize($var);

        if (false === $percentages) {
            return $var;
        }

        $pro = $var ?: 0;
        $contra = self::get_contraAll();
        $pro_percent = 0;

        if (is_int($pro) && 0 !== $pro && 0 <= $pro) {
            $pro_percent = (($pro / ($pro + $contra)) * 100);
        }

        $pro_percent = number_format($pro_percent, 2);

        return (float) str_replace('.00', '', $pro_percent);
    }

    /**
     * Get contra count of all posts.
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param bool $percentages return percentage values on true.
     *
     * @return int count
     */
    public static function get_contra_all($percentages = false)
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $sql = "SELECT COUNT(*) FROM $helpful WHERE contra = 1";

        $cache_name = 'helpful_contra_all';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $var = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $var = $wpdb->get_var($sql);
        } elseif (false === $var) {
            $var = $wpdb->get_var($sql);
            set_transient($cache_name, maybe_serialize($var), $cache_time);
        }

        $var = maybe_unserialize($var);

        if (false === $percentages) {
            return $var;
        }

        $contra = $var ?: 0;
        $pro = self::get_proAll();
        $contra_percent = 0;

        if (is_int($contra) && 0 !== $contra && 0 <= $contra) {
            $contra_percent = (($contra / ($pro + $contra)) * 100);
        }

        $contra_percent = number_format($contra_percent, 2);
        return (float) str_replace('.00', '', $contra_percent);
    }

    /**
     * Get years
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @return array
     */
    public static function get_years()
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $sql = "SELECT time FROM $helpful ORDER BY time DESC";

        $cache_name = 'helpful/stats/years';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if (!$results) {
            return [];
        }

        $years = [];

        foreach ($results as $result):
            $years[] = date('Y', strtotime($result->time));
        endforeach;

        $years = array_unique($years);

        return $years;
    }

    /**
     * Stats for today.
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param int $year response year.
     *
     * @return array
     */
    public static function get_stats_today($year)
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $query = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE DAYOFYEAR(time) = DAYOFYEAR(NOW())
		AND YEAR(time) = %d
		";

        $sql = $wpdb->prepare($query, intval($year));

        $cache_name = 'helpful/stats/today/' . $year;
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if (!$results) {
            return [
                'status' => 'error',
                'message' => __('No entries found', 'helpful'),
            ];
        }

        $pro = wp_list_pluck($results, 'pro');
        $pro = array_sum($pro);
        $contra = wp_list_pluck($results, 'contra');
        $contra = array_sum($contra);

        /* Response for ChartJS */
        $response = [
            'type' => 'doughnut',
            'data' => [
                'datasets' => [
                    [
                        'data' => [
                            absint($pro),
                            absint($contra),
                        ],
                        'backgroundColor' => [
                            self::$green,
                            self::$red,
                        ],
                    ],
                ],
                'labels' => [
                    __('Pro', 'helpful'),
                    __('Contra', 'helpful'),
                ],
            ],
            'options' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];

        return $response;
    }

    /**
     * Stats for yesterday.
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param int $year response year.
     *
     * @return array
     */
    public static function get_stats_yesterday($year)
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $query = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE DAYOFYEAR(time) = DAYOFYEAR(SUBDATE(CURDATE(),1))
		AND YEAR(time) = %d
		";

        $sql = $wpdb->prepare($query, intval($year));

        $cache_name = 'helpful/stats/yesterday/' . $year;
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if (!$results) {
            return [
                'status' => 'error',
                'message' => __('No entries found', 'helpful'),
            ];
        }

        $pro = wp_list_pluck($results, 'pro');
        $pro = array_sum($pro);
        $contra = wp_list_pluck($results, 'contra');
        $contra = array_sum($contra);

        /* Response for ChartJS */
        $response = [
            'type' => 'doughnut',
            'data' => [
                'datasets' => [
                    [
                        'data' => [
                            absint($pro),
                            absint($contra),
                        ],
                        'backgroundColor' => [
                            self::$green,
                            self::$red,
                        ],
                    ],
                ],
                'labels' => [
                    __('Pro', 'helpful'),
                    __('Contra', 'helpful'),
                ],
            ],
            'options' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];

        return $response;
    }

    /**
     * Stats for week
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param int $year response year.
     *
     * @return array
     */
    public static function get_stats_week($year)
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $query = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE WEEK(time, 1) = WEEK(CURDATE(), 1)
		AND YEAR(time) = %d
		";

        $sql = $wpdb->prepare($query, intval($year));

        $cache_name = 'helpful/stats/week/' . $year;
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if (!$results) {
            return [
                'status' => 'error',
                'message' => __('No entries found', 'helpful'),
            ];
        }

        $pro = [];
        $contra = [];
        $labels = [];
        $timestamp = strtotime('monday this week');
        $days = 7;

        for ($i = 0; $i < $days; $i++):
            $date = date_i18n('Ymd', strtotime("+$i days", $timestamp));
            $day = date_i18n('D', strtotime("+$i days", $timestamp));
            $pro[$date] = 0;
            $contra[$date] = 0;
            $labels[] = $day;
        endfor;

        foreach ($results as $result):
            for ($i = 0; $i < $days; $i++):
                $day = date_i18n('Ymd', strtotime("+$i days", $timestamp));
                $date = date_i18n('Ymd', strtotime($result->time));

                if ($day === $date) {
                    $pro[$date] += $result->pro;
                    $contra[$date] += $result->contra;
                }
            endfor;
        endforeach;

        /* Response for ChartJS */
        $response = [
            'type' => 'bar',
            'data' => [
                'datasets' => [
                    [
                        'label' => __('Pro', 'helpful'),
                        'data' => array_values($pro),
                        'backgroundColor' => self::$green,
                    ],
                    [
                        'label' => __('Contra', 'helpful'),
                        'data' => array_values($contra),
                        'backgroundColor' => self::$red,
                    ],
                ],
                'labels' => $labels,
            ],
            'options' => [
                'scales' => [
                    'xAxes' => [
                        ['stacked' => true],
                    ],
                    'yAxes' => [
                        ['stacked' => true],
                    ],
                ],
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];

        return $response;
    }

    /**
     * Stats for month
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param int $year response year.
     * @param int $month response month.
     *
     * @return array
     */
    public static function get_stats_month($year, $month = null)
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';

        if (is_null($month)) {
            $month = date('m');
        } else {
            $month = absint($month);
        }

        $query = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE MONTH(time) = %d
		AND YEAR(time) = %d
		";

        $sql = $wpdb->prepare($query, intval($month), intval($year));

        $cache_name = 'helpful/stats/month/' . $month . '/' . $year;
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if (!$results) {
            return [
                'status' => 'error',
                'message' => __('No entries found', 'helpful'),
            ];
        }

        $pro = [];
        $contra = [];
        $labels = [];
        $timestamp = strtotime(date("$year-$month-1"));
        $days = date_i18n('t', $timestamp) - 1;

        for ($i = 0; $i < $days; $i++):
            $date = date_i18n('Ymd', strtotime("+$i days", $timestamp));
            $day = date_i18n('j M', strtotime("+$i days", $timestamp));
            $pro[$date] = 0;
            $contra[$date] = 0;
            $labels[] = $day;
        endfor;

        foreach ($results as $result):
            for ($i = 0; $i < $days; $i++):
                $day = date_i18n('Ymd', strtotime("+$i days", $timestamp));
                $date = date_i18n('Ymd', strtotime($result->time));

                if ($day === $date) {
                    $pro[$date] += $result->pro;
                    $contra[$date] += $result->contra;
                }
            endfor;
        endforeach;

        /* Response for ChartJS */
        $response = [
            'type' => 'bar',
            'data' => [
                'datasets' => [
                    [
                        'label' => __('Pro', 'helpful'),
                        'data' => array_values($pro),
                        'backgroundColor' => self::$green,
                    ],
                    [
                        'label' => __('Contra', 'helpful'),
                        'data' => array_values($contra),
                        'backgroundColor' => self::$red,
                    ],
                ],
                'labels' => $labels,
            ],
            'options' => [
                'scales' => [
                    'xAxes' => [
                        ['stacked' => true],
                    ],
                    'yAxes' => [
                        ['stacked' => true],
                    ],
                ],
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];

        return $response;
    }

    /**
     * Stats for year
     *
     * @global $wpdb
     * @version 4.4.61
     *
     * @param int $year response year.
     *
     * @return array
     */
    public static function get_stats_year($year)
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $query = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE YEAR(time) = %d
		";

        $sql = $wpdb->prepare($query, intval($year));

        $cache_name = 'helpful/stats/year/' . $year;
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if (!$results) {
            return [
                'status' => 'error',
                'message' => __('No entries found', 'helpful'),
            ];
        }

        $pro = [];
        $contra = [];
        $labels = [];
        $timestamp = strtotime('first day of January' . intval($year));
        $days = 12;

        for ($i = 0; $i < $days; $i++):
            $month = date_i18n('M', strtotime("+$i months", $timestamp));
            $pro[$month] = 0;
            $contra[$month] = 0;
            $labels[] = $month;
        endfor;

        foreach ($results as $result):
            for ($i = 0; $i < $days; $i++):
                $month = date_i18n('M', strtotime("+$i months", $timestamp));
                $m = date_i18n('M', strtotime($result->time));

                if ($month === $m) {
                    $pro[$month] += $result->pro;
                    $contra[$month] += $result->contra;
                }
            endfor;
        endforeach;

        /* Response for ChartJS */
        $response = [
            'type' => 'bar',
            'data' => [
                'datasets' => [
                    [
                        'label' => __('Pro', 'helpful'),
                        'data' => array_values($pro),
                        'backgroundColor' => self::$green,
                    ],
                    [
                        'label' => __('Contra', 'helpful'),
                        'data' => array_values($contra),
                        'backgroundColor' => self::$red,
                    ],
                ],
                'labels' => $labels,
            ],
            'options' => [
                'scales' => [
                    'xAxes' => [
                        ['stacked' => true],
                    ],
                    'yAxes' => [
                        ['stacked' => true],
                    ],
                ],
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];

        return $response;
    }

    /**
     * Stats by range
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param string $from time string.
     * @param string $to time string.
     *
     * @return array
     */
    public static function get_stats_range($from, $to)
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $query = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE DATE(time) >= DATE(%s)
		AND DATE(time) <= DATE(%s)
		";

        $sql = $wpdb->prepare($query, $from, $to);

        $cache_name = 'helpful/stats/range/' . $from . '/' . $to;
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if (!$results) {
            return [
                'status' => 'error',
                'message' => __('No entries found', 'helpful'),
            ];
        }

        $from_date = new \DateTime($from);
        $to_date = new \DateTime($to);
        $diff = $from_date->diff($to_date);
        $pro = [];
        $contra = [];
        $labels = [];
        $timestamp = strtotime($from);
        $limit = ($diff->format('%a') + 1);

        for ($i = 0; $i < $limit; $i++):
            $date = date_i18n('Ymd', strtotime("+$i days", $timestamp));
            $day = date_i18n('j M', strtotime("+$i days", $timestamp));
            $pro[$date] = 0;
            $contra[$date] = 0;
            $labels[] = $day;
        endfor;

        foreach ($results as $result) {
            $date = date_i18n('Ymd', strtotime($result->time));
            $pro[$date] += (int) $result->pro;
            $contra[$date] += (int) $result->contra;
        }

        /* Response for ChartJS */
        $response = [
            'type' => 'bar',
            'data' => [
                'datasets' => [
                    [
                        'label' => __('Pro', 'helpful'),
                        'data' => array_values($pro),
                        'backgroundColor' => self::$green,
                    ],
                    [
                        'label' => __('Contra', 'helpful'),
                        'data' => array_values($contra),
                        'backgroundColor' => self::$red,
                    ],
                ],
                'labels' => $labels,
            ],
            'options' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];

        return $response;
    }

    /**
     * Stats for total
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @return array
     */
    public static function get_stats_total()
    {
        global $wpdb;

        $options = new Services\Options();

        $helpful = $wpdb->prefix . 'helpful';
        $sql = "SELECT pro, contra, time FROM $helpful";

        $cache_name = 'helpful/stats/total';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if (!$results) {
            return [
                'status' => 'error',
                'message' => __('No entries found', 'helpful'),
            ];
        }

        $pro = wp_list_pluck($results, 'pro');
        $pro = array_sum($pro);

        $contra = wp_list_pluck($results, 'contra');
        $contra = array_sum($contra);

        /* Response for ChartJS */
        $response = [
            'type' => 'doughnut',
            'data' => [
                'datasets' => [
                    [
                        'data' => [
                            absint($pro),
                            absint($contra),
                        ],
                        'backgroundColor' => [
                            self::$green,
                            self::$red,
                        ],
                    ],
                ],
                'labels' => [
                    __('Pro', 'helpful'),
                    __('Contra', 'helpful'),
                ],
            ],
            'options' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];

        return $response;
    }

    /**
     * Get most helpful posts.
     * 
     * @version 4.4.59
     *
     * @param int $limit posts per page.
     * @param string|array $post_type
     *
     * @return array
     */
    public static function get_most_helpful($limit = null, $post_type = null)
    {
        $options = new Services\Options();

        if (is_null($limit)) {
            $limit = intval($options->get_option('helpful_widget_amount', 3, 'intval'));
        } else {
            $limit = intval($limit);
        }

        if (is_null($post_type)) {
            $post_type = $options->get_option('helpful_post_types', [], 'esc_attr');
        }

        $args = [
            'post_type' => $post_type,
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        $cache_name = 'helpful_most_helpful';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $query = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $query = new \WP_Query($args);
        } elseif (false === $query) {
            $query = new \WP_Query($args);
            set_transient($cache_name, maybe_serialize($query), $cache_time);
        }

        $query = maybe_unserialize($query);
        $posts = [];
        $results = [];

        if ($query->found_posts) {
            foreach ($query->posts as $post_id):
                $pro = self::get_pro($post_id) ? self::get_pro($post_id) : 0;
                $contra = self::get_contra($post_id) ? self::get_contra($post_id) : 0;
                $posts[$post_id] = (int) ($pro - $contra);
            endforeach;

            if (1 < count($posts)) {
                arsort($posts);

                $posts = array_slice($posts, 0, $limit, true);

                foreach ($posts as $post_id => $value):
                    if (0 === $value) {
                        continue;
                    }

                    $data = self::get_single_post_stats($post_id);
                    $results[] = [
                        'ID' => $data['ID'],
                        'url' => $data['permalink'],
                        'name' => $data['title'],
                        'pro' => $data['pro']['value'],
                        'contra' => $data['contra']['value'],
                        'percentage' => $data['helpful'],
                        'time' => sprintf(
                            /* translators: %s time difference */
                            __('Published %s ago', 'helpful'),
                            human_time_diff($data['time']['timestamp'], date_i18n('U'))
                        ),
                    ];
                endforeach;
            }
        }

        if (is_array($results)) {
            usort($results, function ($a, $b) {
                return $b['percentage'] - $a['percentage'];
            });

            $results = array_filter($results);
        }

        return $results;
    }

    /**
     * Get least helpful posts.
     *
     * @version 4.4.59
     *
     * @param int $limit posts per page.
     * @param string|array $post_type
     *
     * @return array
     */
    public static function get_least_helpful($limit = null, $post_type = null)
    {
        $options = new Services\Options();

        if (is_null($limit)) {
            $limit = intval($options->get_option('helpful_widget_amount', 3, 'intval'));
        }

        if (is_null($post_type)) {
            $post_type = $options->get_option('helpful_post_types', [], 'esc_attr');
        }

        $args = [
            'post_type' => $post_type,
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        $cache_name = 'helpful_least_helpful';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $query = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $query = new \WP_Query($args);
        } elseif (false === $query) {
            $query = new \WP_Query($args);
            set_transient($cache_name, maybe_serialize($query), $cache_time);
        }

        $query = maybe_unserialize($query);
        $posts = [];
        $results = [];

        if ($query->found_posts) {
            foreach ($query->posts as $post_id):
                $pro = self::get_pro($post_id) ? self::get_pro($post_id) : 0;
                $contra = self::get_contra($post_id) ? self::get_contra($post_id) : 0;
                $posts[$post_id] = (int) ($contra - $pro);
            endforeach;

            if (1 < count($posts)) {
                arsort($posts);
            
                $posts = array_slice($posts, 0, $limit, true);

                foreach ($posts as $post_id => $value):
                    if (0 === $value) {
                        continue;
                    }

                    $data = self::get_single_post_stats($post_id);
                    $results[] = [
                        'ID' => $data['ID'],
                        'url' => $data['permalink'],
                        'name' => $data['title'],
                        'pro' => $data['pro']['value'],
                        'contra' => $data['contra']['value'],
                        'percentage' => $data['helpful'],
                        'time' => sprintf(
                            /* translators: %s time difference */
                            __('Published %s ago', 'helpful'),
                            human_time_diff($data['time']['timestamp'], date_i18n('U'))
                        ),
                    ];
                endforeach;
            }
        }

        if (is_array($results)) {
            usort($results, function ($a, $b) {
                return $a['percentage'] - $b['percentage'];
            });

            $results = array_filter($results);
        }

        return $results;
    }

    /**
     * Get recently helpful pro posts
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param int $limit posts per page.
     *
     * @return array
     */
    public static function get_recently_pro($limit = null)
    {
        $options = new Services\Options();

        if (is_null($limit)) {
            $limit = absint($options->get_option('helpful_widget_amount', 3, 'intval'));
        }

        global $wpdb;

        $helpful = $wpdb->prefix . 'helpful';
        $sql = "
		SELECT post_id, time
		FROM $helpful
		WHERE pro = %d
		ORDER BY id DESC
		LIMIT %d
		";

        $posts = [];
        $sql = $wpdb->prepare($sql, 1, intval($limit));

        $cache_name = 'helpful_recently_pro';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if ($results) {
            foreach ($results as $post):
                $data = self::get_single_post_stats($post->post_id);
                $timestamp = strtotime($post->time);
                $posts[] = [
                    'ID' => $data['ID'],
                    'url' => $data['permalink'],
                    'name' => $data['title'],
                    'percentage' => $data['helpful'],
                    'time' => sprintf(
                        /* translators: %s time difference */
                        __('Submitted %s ago', 'helpful'),
                        human_time_diff($timestamp, date_i18n('U'))
                    ),
                ];
            endforeach;
        }

        return $posts;
    }

    /**
     * Get recently unhelpful pro posts.
     *
     * @global $wpdb
     * @version 4.4.59
     *
     * @param int $limit posts per page.
     *
     * @return array
     */
    public static function get_recently_contra($limit = null)
    {
        $options = new Services\Options();

        if (is_null($limit)) {
            $limit = absint($options->get_option('helpful_widget_amount', 3, 'intval'));
        }

        global $wpdb;

        $helpful = $wpdb->prefix . 'helpful';
        $sql = "
		SELECT post_id, time
		FROM $helpful
		WHERE contra = %d
		ORDER BY id DESC
		LIMIT %d
		";

        $posts = [];
        $sql = $wpdb->prepare($sql, 1, intval($limit));

        $cache_name = 'helpful_recently_contra';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = $wpdb->get_results($sql);
        } elseif (false === $results) {
            $results = $wpdb->get_results($sql);
            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        if ($results) {
            foreach ($results as $post):
                $data = self::get_single_post_stats($post->post_id);
                $timestamp = strtotime($post->time);
                $posts[] = [
                    'ID' => $data['ID'],
                    'url' => $data['permalink'],
                    'name' => $data['title'],
                    'percentage' => $data['helpful'],
                    'time' => sprintf(
                        /* translators: %s time difference */
                        __('Submitted %s ago', 'helpful'),
                        human_time_diff($timestamp, date_i18n('U'))
                    ),
                ];
            endforeach;
        }

        return $posts;
    }

    /**
     * Get single post stats
     *
     * @return array
     */
    public static function get_single_post_stats($post_id)
    {
        $options = new Services\Options();

        $post = get_post($post_id);
        $pro = self::get_pro($post->ID) ? intval(self::get_pro($post->ID)) : 0;
        $contra = self::get_contra($post->ID) ? intval(self::get_contra($post->ID)) : 0;
        $prop = self::get_pro($post->ID, true);
        $conp = self::get_contra($post->ID, true);

        $average = 0;
        $total = 0;
        $percentage = 0;

        if (0 !== $pro) {
            $average = (int) ($pro - $contra);
            $total = (int) ($pro + $contra);
            $percentage = ($pro / $total) * 100;
            $percentage = round($percentage, 2);
        }

        $post_type = get_post_type_object($post->post_type);

        $results = [
            'ID' => $post->ID,
            'permalink' => get_the_permalink($post->ID),
            'title' => esc_html($post->post_title),
            'type' => [
                'slug' => $post_type->name,
                'name' => $post_type->labels->singular_name,
            ],
            'author' => [
                'ID' => $post->post_author,
                'name' => get_the_author_meta('display_name', $post->post_author),
            ],
            'pro' => [
                'value' => $pro,
                'percentage' => $prop,
            ],
            'contra' => [
                'value' => $contra,
                'percentage' => $conp,
            ],
            'helpful' => $percentage,
            'time' => [
                'time' => date_i18n('H:i:s', get_the_date('U', $post->ID)),
                'date' => date_i18n('Y-m-d', get_the_date('U', $post->ID)),
                'timestamp' => date_i18n('U', get_the_date('U', $post->ID)),
            ],
        ];

        return $results;
    }

    /**
     * Returns everything at once and saves the result in a transient to reduce the number of queries for the widget.
     *
     * @version 4.4.59
     *
     * @return array
     */
    public static function get_widget_stats()
    {
        $options = new Services\Options();

        $cache_name = 'helpful_widget_stats';
        $cache_time = $options->get_option('helpful_cache_time', 'minute', 'esc_attr');
        $cache_active = $options->get_option('helpful_caching', 'off', 'esc_attr');
        $cache_times = Cache::get_cache_times(false);
        $cache_time = (isset($cache_times[$cache_time])) ? $cache_times[$cache_time] : MINUTE_IN_SECONDS;
        $results = get_transient($cache_name);

        if ('on' !== $cache_active) {
            $results = [
                'most_helpful' => $options->get_option('helpful_widget_pro', false, 'bool') ? self::get_most_helpful() : null,
                'least_helpful' => $options->get_option('helpful_widget_contra', false, 'bool') ? self::get_least_helpful() : null,
                'recently_pro' => $options->get_option('helpful_widget_pro_recent', false, 'bool') ? self::get_recently_pro() : null,
                'recently_contra' => $options->get_option('helpful_widget_contra_recent', false, 'bool') ? self::get_recently_contra() : null,
                'feedback_items' => $options->get_option('helpful_feedback_widget', false, 'bool') ? Feedback::get_feedback_items() : null,
                'pro_total' => intval(self::get_pro_all()),
                'contra_total' => intval(self::get_contra_all()),
            ];

            return $results;
        }

        if (false === $results) {
            $results = [
                'most_helpful' => $options->get_option('helpful_widget_pro', false, 'bool') ? self::get_most_helpful() : null,
                'least_helpful' => $options->get_option('helpful_widget_contra', false, 'bool') ? self::get_least_helpful() : null,
                'recently_pro' => $options->get_option('helpful_widget_pro_recent', false, 'bool') ? self::get_recently_pro() : null,
                'recently_contra' => $options->get_option('helpful_widget_contra_recent', false, 'bool') ? self::get_recently_contra() : null,
                'feedback_items' => $options->get_option('helpful_feedback_widget', false, 'bool') ? Feedback::get_feedback_items() : null,
                'pro_total' => intval(self::get_pro_all()),
                'contra_total' => intval(self::get_contra_all()),
            ];

            set_transient($cache_name, maybe_serialize($results), $cache_time);
        }

        $results = maybe_unserialize($results);

        return $results;
    }

    /**
     * Removes the transient for the widget so that current data can be transferred.
     *
     * @return void
     */
    public static function delete_widget_transient()
    {
        delete_transient('helpful_widget_stats');
    }
}
