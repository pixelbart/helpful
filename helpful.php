<?php
/**
 * Plugin Name: Helpful
 * Description: Add a fancy feedback form under your posts or post-types and ask your visitors a question. Give them the abbility to vote with yes or no.
 * Version: 4.4.68
 * Author: Pixelbart
 * Author URI: https://pixelbart.de
 * Text Domain: helpful
 * License: MIT License
 * License URI: https://opensource.org/licenses/MIT
 */

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

define('HELPFUL_FILE', __FILE__);
define('HELPFUL_PATH', plugin_dir_path(__FILE__));
define('HELPFUL_PHP_MIN', '5.6.20');

/* Load Helpful after plugins are loaded */
add_action('plugins_loaded', ['HelpfulPlugin', 'get_instance']);

if (!class_exists('HelpfulPlugin')) {

    class HelpfulPlugin
    {
        /**
         * Class Prefix.
         *
         * @var string
         */
        private $prefix = 'Helpful\\';

        /**
         * Saves an instance of the class.
         *
         * @var HelpfulPlugin
         */
        private static $instance;

        /**
         * Creates an instance of the class if it does not yet exist.
         *
         * @return HelpfulPlugin
         */
        public static function get_instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Class constructor.
         *
         * @return void
         */
        private function __construct()
        {
            /* Starts the autoloader. */
            spl_autoload_register([$this, 'autoload']);

            /* Initializes the classes and functions. */
            $this->init();

            add_action('admin_init', [ & $this, 'shedule_cron_events']);

            register_deactivation_hook(HELPFUL_FILE, [ & $this, 'unshedule_cron_events']);
        }

        /**
         * Outputs the current Helpful version, which is stored in the plugin file as a comment.
         *
         * @return string
         */
        public function get_plugin_version()
        {
            $plugin_data = get_plugin_data(HELPFUL_FILE);
            return (isset($plugin_data['Version'])) ? $plugin_data['Version'] : '1.0.0';
        }

        /**
         * Outputs the Helpful version that was stored in the database.
         *
         * @version 4.4.59
         *
         * @return string
         */
        public function get_option_version()
        {
            return esc_attr(get_option('helpful/version', '1.0.0'));
        }

        /**
         * @return void
         */
        public function refresh_option_version()
        {
            do_action('helpful/version/refresh', $this->get_plugin_version(), $this->get_option_version());
            update_option('helpful/version', $this->get_plugin_version());
        }

        /**
         * Includes all classes.
         *
         * @param string $class_name
         *
         * @return void
         */
        public function autoload($class_name)
        {
            /* Stores the prefix. */
            $prefix = $this->prefix;

            $len = strlen($prefix);

            if (0 !== strncmp($prefix, $class_name, $len)) {
                return;
            }

            $relative_class = substr($class_name, $len);

            $path = explode('\\', strtolower(str_replace('_', '-', $relative_class)));
            $file = array_pop($path);
            $file = HELPFUL_PATH . implode('/', $path) . '/class-' . $file . '.php';

            /* Includes the file if the file exists. */
            if (file_exists($file)) {
                require $file;
            }
        }

        /**
         * Initializes the classes.
         *
         * @return void
         */
        public function init()
        {
            include_once HELPFUL_PATH . 'core/functions/helpers.php';

            Helpful\Core\Modules\Core::get_instance();

            Helpful\Core\Modules\Maintenance::get_instance();
            Helpful\Core\Modules\debug::get_instance();
            Helpful\Core\Modules\Admin::get_instance();

            Helpful\Core\Modules\Feedback_Admin::get_instance();
            Helpful\Core\Modules\Widget::get_instance();
            Helpful\Core\Modules\Customizer::get_instance();
            Helpful\Core\Modules\Frontend::get_instance();
            Helpful\Core\Modules\Api::get_instance();

            Helpful\Core\Tabs\Start::get_instance();
            Helpful\Core\Tabs\Details::get_instance();
            Helpful\Core\Tabs\Texts::get_instance();
            Helpful\Core\Tabs\Feedback::get_instance();
            Helpful\Core\Tabs\Design::get_instance();
            Helpful\Core\Tabs\System::get_instance();
            Helpful\Core\Tabs\Log::get_instance();

            if (is_admin()) {
                add_action('load-post.php', ['Helpful\Core\Modules\Metabox', 'get_instance']);
                add_action('load-post-new.php', ['Helpful\Core\Modules\Metabox', 'get_instance']);
            }

            include_once HELPFUL_PATH . 'core/functions/values.php';
        }

        /**
         * Unshedules the events created by Helpful for the crons.
         *
         * @return void
         */
        public function unshedule_cron_events()
        {
            wp_unschedule_event(wp_next_scheduled('helpful/dashboard/build_cache'), 'helpful/dashboard/build_cache');
        }

        /**
         * Shedules the events created by Helpful for the crons. Updates the version in the database,
         * with the current version, if the versions do not match.
         *
         * @return void
         */
        public function shedule_cron_events()
        {
            $plugin_version = $this->get_plugin_version();
            $option_version = $this->get_option_version();

            if ($plugin_version !== $option_version) {
                /* dashboard cron */
                if (!wp_next_scheduled('helpful/dashboard/build_cache')) {
                    wp_schedule_event(time(), 'twicedaily', 'helpful/dashboard/build_cache');
                }

                /* refresh version in database */
                $this->refresh_option_version();
            }
        }
    }
}
