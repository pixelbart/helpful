<?php
/**
 * @package Helpful
 * @since 4.4.50
 * @since 4.3.0
 */
use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('helpful_backlist_check')) {
    /**
     * WordPress blacklist checker
     *
     * @param string $content the content to be checked.
     *
     * @return bool
     */
    function helpful_backlist_check($content)
    {
        return Helper::backlist_check($content);
    }
}

if (!function_exists('helpful_trim_all')) {
    /**
     * Trim all whitespaces.
     *
     * @param string $string string to trim.
     * @return string
     */
    function helpful_trim_all($string)
    {
        return preg_replace('/\s+/', '', $string);
    }
}

if (!function_exists('helpful_error_log')) {
    /**
     * This allows custom error messages to be placed in the error_logs.
     * WP_DEBUG and WP_DEBUG_LOG must be set to true.
     *
     * @source https://wordpress.org/support/article/debugging-in-wordpress/
     *
     * @param string $message error message.
     */
    function helpful_error_log($message)
    {
        if (defined('WP_DEBUG') && true === WP_DEBUG) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}

if (!function_exists('helpful_has_user_voted')) {
    /**
     * Checks by a Post-ID whether a vote has already been taken for this post.
     *
     * @global $post
     *
     * @param int|null $post_id
     * @param bool     $bool Returns the vote status (pro, contra, none) if true.
     *
     * @return bool|string
     */
    function helpful_has_user_voted($post_id = null, $bool = true)
    {
        return Helpers\Values::has_user_voted($post_id, $bool);
    }
}

if (!function_exists('helpful_is_amp')) {
    /**
     * Checks if AMP is used and outputs either TRUE or FALSE. Is used to avoid including Helpful in AMP.
     *
     * @return bool
     */
    function helpful_is_amp()
    {
        return Helper::is_amp();
    }
}