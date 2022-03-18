<?php
/**
 * @package Helpful
 * @subpackage Core\Solutions
 * @version 4.5.0
 * @since 4.5.0
 */
namespace Helpful\Core\Solutions;

use Helpful\Core\Helper;
use Helpful\Core\Module;
use Helpful\Core\Helpers;
use Helpful\Core\Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class WP_Rocket
{
    use Module;

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        apply_filters('helpful_pre_save_vote', [ & $this, 'helpful_rocket_pre_save_vote' ], 99, 2);
    }

    /**
     * @param string $content
     * @param int $post_id
     * @return string
     */
    public function helpful_rocket_pre_save_vote($content, $post_id)
    {
        $pages_to_clean_preload = [];

        $pages_to_clean_preload[] = get_the_permalink($post_id);
    
        if (function_exists('rocket_clean_post')) {
            rocket_clean_post($post_id);
        }
    
        if (function_exists('get_rocket_option')) {    
            if (1 == get_rocket_option('manual_preload')) {
                $args = [];
    
                if (1 == get_rocket_option('cache_webp')) {
                    $args['headers']['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
                    $args['headers']['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
                }
    
                $this->preload_page($pages_to_clean_preload, $args);
    
                if (1 == get_rocket_option('do_caching_mobile_files')) {
                    $args['headers']['user-agent'] = 'Mozilla/5.0 (Linux; Android 8.0.0;) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Mobile Safari/537.36';
                    $this->preload_page($pages_to_clean_preload, $args);
                }
            }
        }
    
        return $content;
    }
    
    /**
     * @param array $pages
     * @param array $args
     * @return void
     */
    public function preload_page($pages, $args)
    {
        foreach ($pages as $page) {
            wp_remote_get(esc_url_raw($page), $args);
        }
    }
}