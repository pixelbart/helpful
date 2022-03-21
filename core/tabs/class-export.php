<?php
/**
 * @package Helpful
 * @subpackage Core\Tabs
 * @version 4.5.5
 * @since 4.5.0
 */
namespace Helpful\Core\Tabs;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Export
{
    /**
     * Class instance
     *
     * @var Export
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Export
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
        add_filter('helpful_get_admin_tabs', [ & $this, 'register_tab'], 10, 2);
        add_action('helpful_tabs_content', [ & $this, 'register_tab_content']);
        add_action('helpful_tab_export_before', [ & $this, 'register_tab_alerts']);

        add_action('wp_ajax_helpful_import', [ & $this, 'import_settings']);
    }

    /**
     * Add tab to filter
     *
     * @param array $tabs
     * @param string $current
     *
     * @return array
     */
    public function register_tab($tabs, $current)
    {
        $tabs['export'] = [
            'id' => 'export',
            'name' => esc_html_x('Export & Import', 'tab name', 'helpful'),
        ];

        return $tabs;
    }

    /**
     * Register tab content.
     *
     * @return void
     */
    public function register_tab_content()
    {
        if (!Helper::is_active_tab('export')) {
            return;
        }

        $template = HELPFUL_PATH . 'templates/tabs/tab-export.php';

        if (file_exists($template)) {
            include_once $template;
        }
    }

    /**
     * Register tab alerts for settings saved and other.
     *
     * @return void
     */
    public function register_tab_alerts()
    {
        if (isset($_GET['settings_imported']) && is_numeric($_GET['settings_imported'])) {
            $count = intval($_GET['settings_imported']);

            $message = sprintf(_nx('%s setting imported', '%s settings imported', $count, 'import success alert', 'helpful'), $count);

            echo Helper::get_alert($message, 'success', 0);
        }

        if (isset($_GET['settings_imported_error'])) {
            $message = esc_html_x('Invalid import data. The import could not be performed.', 'import error alert', 'helpful');

            if ('invalid_nonce' === $_GET['settings_imported_error']) {
                $message = esc_html_x('Invalid security token. The import could not be performed.', 'import error alert', 'helpful');
            }

            if ('empty_import_string' === $_GET['settings_imported_error']) {
                $message = esc_html_x('No import data found. The import could not be performed.', 'import error alert', 'helpful');
            }

            echo Helper::get_alert($message, 'danger', 0);
        }
    }

    public function import_settings()
    {
        if (!check_admin_referer('helpful_import')) {
            wp_safe_redirect(add_query_arg('settings_imported_error', 'invalid_nonce', wp_get_referer()));
            exit;
        }

        $import_string = (array_key_exists('helpful_import', $_POST)) ? sanitize_text_field($_POST['helpful_import']) : '';

        if (!$import_string || '' === trim($import_string)) {
            wp_safe_redirect(add_query_arg('settings_imported_error', 'empty_import_string', wp_get_referer()));
            exit;
        }

        $import_string = base64_decode($import_string);
        $import_string = json_decode($import_string, true);

        if (!is_array($import_string)) {
            wp_safe_redirect(add_query_arg('settings_imported_error', 'invalid_import_string', wp_get_referer()));
            exit;
        }

        $service = new Services\Options();
        $options = array_keys($service->get_defaults_array());

        $result = 0;

        foreach ($import_string as $key => $value) {
            if (in_array($key, $options)) {
                $service->update_option($key, $value);
                $result += 1;
            }
        }

        wp_safe_redirect(add_query_arg('settings_imported', $result, wp_get_referer()));
        exit;
    }
}
