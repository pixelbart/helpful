<?php
/**
 * @package Helpful
 * @subpackage Core\Services
 * @copyright Copyright (c) 2015, Pippin Williamson
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version 4.4.50
 * @since 4.4.50
 */
namespace Helpful\Core\Services;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Session
{
    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value)
    {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $data = false;

        if (isset($_SESSION[$key]) && '' !== trim($_SESSION[$key])) {
            $data = sanitize_text_field($_SESSION[$key]);
        }

        return $data;
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->maybe_start_session();
    }

    /**
     * @return bool
     */
    public function should_start_session()
    {
        $start_session = true;

        if (!empty($_SERVER['REQUEST_URI'])) {
            $uri = ltrim($_SERVER['REQUEST_URI'], '/');
            $uri = untrailingslashit($uri);

            if (false !== strpos($uri, 'feed=')) {
                $start_session = false;
            }

            if (is_admin() && false === strpos($uri, 'wp-admin/admin-ajax.php')) {
                $start_session = false;
            }

            if (false !== strpos($uri, 'wp_scrape_key')) {
                $start_session = false;
            }
        }

        if (get_option('helpful_sessions_false')) {
            $start_session = false;
        }

        return apply_filters('helpful/session/start', $start_session);
    }

    /**
     * @return void
     */
    public function maybe_start_session()
    {
        if (!$this->should_start_session()) {
            return;
        }

        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }
}
