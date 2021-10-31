<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.4.59
 * @since 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Core
{
    /**
     * Instance
     *
     * @var Core
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Core
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
        Helper::set_timezone();

        add_action('init', [ & $this, 'start_session'], -1);
        add_action('init', [ & $this, 'set_user_cookie'], 1);

        add_action('admin_init', [ & $this, 'setup_tables']);

        add_action('init', [ & $this, 'setup_defaults']);

        add_action('activated_plugin', [ & $this, 'load_first']);

        add_filter('plugin_row_meta', [ & $this, 'plugin_row_meta'], 10, 2);
        add_filter('helpful_debug_fields', [ & $this, 'debug_fields']);

        add_filter('helpful_current_tab', [ & $this, 'current_tab']);
        add_filter('helpful_editor_settings', [ & $this, 'editor_settings']);

        add_action('elementor/widgets/widgets_registered', [ & $this, 'elementor_widgets']);
        add_action('elementor/controls/controls_registered', [ & $this, 'elementor_controls']);
        add_action('elementor/elements/categories_registered', [ & $this, 'elementor_categories']);
    }

    /**
     * Start sessions
     *
     * @return void
     */
    public function start_session()
    {
        $session = new Services\Session();
        $session->init();
    }

    /**
     * Set users cookie with unique id
     *
     * @version 4.4.55
     * @since 4.4.0
     *
     * @return void
     */
    public function set_user_cookie()
    {
        if ((isset($GLOBALS['pagenow']) && 'wp-login.php' === $GLOBALS['pagenow']) || is_admin()) {
            return;
        }

        Helpers\User::set_user();
    }

    /**
     * Set default options.
     *
     * @version 4.4.59
     *
     * @return void
     */
    public function setup_defaults()
    {
        $options = new Services\Options();

        if ($options->get_option('helpful_defaults', 0, 'intval')) {
            return;
        }

        $this->set_defaults(true);

        $options->update_option('helpful_defaults', 1);
    }

    /**
     * Create database tables for helpful.
     *
     * @version 4.4.51
     * @since 4.4.0
     */
    public function setup_tables()
    {
        // Helpful
        $transient = 'helpful/database/setup_tables';
        if (false === get_transient($transient)) {
            Helpers\Database::handle_table_helpful();
            Helpers\Database::handle_table_feedback();
            Helpers\Database::handle_table_instances();
            set_transient($transient, time(), DAY_IN_SECONDS);
        }
    }

    /**
     * Default values for settings.
     *
     * @version 4.4.59
     *
     * @param bool $status set true for filling defaults.
     *
     * @return bool
     */
    public function set_defaults($status = false)
    {
        if (false === $status) {
            return false;
        }

        $options = new Services\Options();
        $defaults = $options->get_defaults_array();
        $defaults = apply_filters('helpful_options', $defaults);

        foreach ($defaults as $slug => $value):
            if (!$options->get_option($slug)) {
                $options->update_option($slug, $value);
            }
        endforeach;

        return true;
    }

    /**
     * Tries to load Helpful first so other plugins don't jump the queue in content.
     *
     * @version 4.4.59
     *
     * @return void
     */
    public function load_first()
    {
        $service = new Services\Options();

        if ('on' !== $service->get_option('helpful_plugin_first', 'off', 'esc_attr')) {
            return;
        }

        $path = str_replace(WP_PLUGIN_DIR . '/', '', HELPFUL_FILE);
        if ($plugins = $service->get_option('active_plugins', [], 'esc_attr')) {
            if ($key = array_search($path, $plugins)) {
                array_splice($plugins, $key, 1);
                array_unshift($plugins, $path);
                update_option('active_plugins', $plugins);
            }
        }
    }

    /**
     * Method for adding and filtering plugin row meta of Helpful.
     *
     * @param array  $links default links.
     * @param string $file file string.
     *
     * @return array
     */
    public function plugin_row_meta($links, $file)
    {

        if (false !== strpos($file, basename(HELPFUL_FILE))) {
            $links['documentation'] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://helpful-plugin.info/documentation/',
                esc_html_x('Documentation', 'plugin row meta', 'helpful')
            );

            $links['donate'] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://www.buymeacoffee.com/pixelbart',
                esc_html_x('Donate', 'plugin row meta', 'helpful')
            );

            $links['support'] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://wordpress.org/support/plugin/helpful/',
                esc_html_x('Support', 'plugin row meta', 'helpful')
            );

            $links = apply_filters('helpful_plugin_row_meta', $links);
        }

        return $links;
    }

    /**
     * Register custom elementor widgets
     *
     * @return void
     */
    public function elementor_widgets()
    {
        $elementor = \Elementor\Plugin::instance();

        /**
         * Register Helpful Widget
         *
         * @see Helpful_Elementor_Widget
         */
        $elementor->widgets_manager->register_widget_type(new Elementor_Widget());
    }

    /**
     * Register custom elementor controls
     *
     * @return void
     */
    public function elementor_controls()
    {
        // nothing
    }

    /**
     * Register categories
     *
     * @param object $elementor elementor object.
     *
     * @return void
     */
    public function elementor_categories($elementor)
    {
        // nothing
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
        $fields['datatables'] = [
            'label' => esc_html_x('DataTables version', 'debug field label', 'helpful'),
            'value' => '1.10.20',
        ];

        return $fields;
    }

    /**
     * Returns the current tab.
     *
     * @return string
     */
    public function current_tab()
    {
        $tab = 'start';

        if (isset($_GET['tab']) && '' !== trim($_GET['tab'])) {
            $tab = sanitize_text_field(wp_unslash($_GET['tab']));
        }

        return $tab;
    }

    /**
     * Returns the WP_Editor settings of Helpful.
     *
     * @return array
     */
    public function editor_settings()
    {
        return [
            'teeny' => true,
            'media_buttons' => false,
            'textarea_rows' => 5,
            'tinymce' => false,
            'quicktags' => [
                'buttons' => 'strong,em,del,ul,ol,li,close,link',
            ],
        ];
    }
}
