<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.4.63
 * @since 4.4.63
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Api
{
    /**
     * Instance
     *
     * @var Api
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Api
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
        add_action('rest_api_init', [ & $this, 'init_pro']);
        add_action('rest_api_init', [ & $this, 'init_contra']);
    }

    /**
     * Returns the default options for the init of the REST API.
     *
     * @version 4.4.63
     * 
     * @return array
     */
    public function default_options()
    {
        $options = [
            'methods' => 'POST',
            'permission_callback' => '__return_empty_string',
        ];

        return apply_filters('helpful/api/options', $options);
    }

    /**
     * Allows saving a positive vote, or returns positive votes of a post.
     * 
     * @version 4.4.63
     * 
     * @return void
     */
    public function init_pro()
    {
        $options = $this->default_options();

        $options['callback'] = function ($data) {

            if (!isset($data['user_id']) || '' === $data['user_id']) {
                return 0;
            }

            if (!isset($data['post_id']) || !is_numeric($data['post_id'])) {
                return 0;
            }

            $user_id = sanitize_text_field($data['user_id']);
            $post_id = intval(sanitize_text_field($data['post_id']));

            Helpful\Core\Helpers::insert_vote($user_id, $post_id, 'pro', null);

            return 1;
        };

        /**
         * POST domain.com/wp-json/helpful/pro/
         * 
         * @param int $post_id
         * @param string $user_id
         * 
         * @return int
         */
        register_rest_route('helpful', '/pro/', $options);
        
        $options['methods'] = 'GET';

        $options['callback'] = function ($data) {
            if (!isset($data['post_id']) || !is_numeric($data['post_id'])) {
                return 0;
            }

            $result = Helpers\Stats::get_pro($data['post_id']);

            if (is_numeric($result)) {
                return intval($result);
            }

            return 0;
        };

        /**
         * GET domain.com/wp-json/helpful/pro/{post_id}
         * 
         * @param int $post_id
         * 
         * @return int
         */
        register_rest_route('helpful', '/pro/(?P<post_id>\d+)', $options);
    }

    /**
     * Allows saving a contra vote, or returns negative votes of a post.
     * 
     * @version 4.4.63
     * 
     * @return void
     */
    public function init_contra()
    {
        $options = $this->default_options();

        $options['callback'] = function ($data) {

            if (!isset($data['user_id']) || '' === $data['user_id']) {
                return 0;
            }

            if (!isset($data['post_id']) || !is_numeric($data['post_id'])) {
                return 0;
            }

            $user_id = sanitize_text_field($data['user_id']);
            $post_id = intval(sanitize_text_field($data['post_id']));

            Helpful\Core\Helpers::insert_vote($user_id, $post_id, 'contra', null);

            return 1;
        };

        /**
         * POST domain.com/wp-json/helpful/contra/
         * 
         * @param int $post_id
         * @param string $user_id
         * 
         * @return int
         */
        register_rest_route('helpful', '/contra/', $options);
        
        $options['methods'] = 'GET';

        $options['callback'] = function ($data) {
            if (!isset($data['post_id']) || !is_numeric($data['post_id'])) {
                return 0;
            }

            $result = Helpers\Stats::get_contra($data['post_id']);

            if (is_numeric($result)) {
                return intval($result);
            }

            return 0;
        };

        /**
         * GET domain.com/wp-json/helpful/contra/{post_id}
         * 
         * @param int $post_id
         * 
         * @return int
         */
        register_rest_route('helpful', '/contra/(?P<post_id>\d+)', $options);
    }
}
