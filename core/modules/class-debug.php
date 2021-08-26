<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.4.50
 * @since 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Debug
{
    /**
     * Class instance
     *
     * @var Debug
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Debug
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_filter('helpful_debug_fields', [ & $this, 'debug_fields'], 1);
        add_filter('debug_information', [ & $this, 'debug_information']);
    }

    /**
     * Debug Informations for Site Health Page.
     *
     * @param array $info
     *
     * @return array
     */
    public function debug_information($info)
    {
        $fields = [];

        $info['helpful'] = [
            'label' => esc_html_x('Helpful', 'debug label', 'helpful'),
            'description' => esc_html_x('If you have problems with the plugin, these values can help you with support.', 'debug description', 'helpful'),
            'fields' => apply_filters('helpful_debug_fields', $fields),
            'private' => true,
        ];

        return $info;
    }

    /**
     * Fields for Debug Informations.
     *
     * @param array $fields
     *
     * @return array
     */
    public function debug_fields($fields)
    {
        $plugin = Helper::get_plugin_data();

        $fields['version'] = [
            'label' => esc_html_x('Helpful version', 'debug field label', 'helpful'),
            'value' => $plugin['Version'],
        ];

        $fields['wordpress'] = [
            'label' => esc_html_x('WordPress version', 'debug field label', 'helpful'),
            'value' => get_bloginfo('version'),
        ];

        $fields['php'] = [
            'label' => esc_html_x('PHP version', 'debug field label', 'helpful'),
            'value' => phpversion('tidy'),
        ];

        $fields['pro'] = [
            'label' => esc_html_x('Pro totals', 'debug field label', 'helpful'),
            'value' => Helpers\Stats::get_pro_all(),
        ];

        $fields['contra'] = [
            'label' => esc_html_x('Contra totals', 'debug field label', 'helpful'),
            'value' => Helpers\Stats::get_contra_all(),
        ];

        $fields['feedback'] = [
            'label' => esc_html_x('Feedback totals', 'debug field label', 'helpful'),
            'value' => Helpers\Feedback::get_feedback_count(null),
        ];

        return $fields;
    }
}
