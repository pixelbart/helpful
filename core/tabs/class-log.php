<?php
/**
 * @package Helpful
 * @subpackage Core\Tabs
 * @version 4.4.50
 * @since 4.3.0
 */
namespace Helpful\Core\Tabs;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Log
{
    /**
     * Class instance
     *
     * @var Log
     */
    public static $instance;

    /**
     * Set instance and fire class
     *
     * @return Log
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
        add_action('admin_enqueue_scripts', [ & $this, 'enqueue_scripts']);
        add_action('wp_ajax_helpful_get_log_data', [ & $this, 'ajax_get_log_data']);
        add_action('helpful_tab_log_before', [ & $this, 'register_tab_alerts']);
        add_action('wp_ajax_helpful_delete_rows', [ & $this, 'ajax_delete_rows']);
        add_action('wp_ajax_helpful_export_rows', [ & $this, 'ajax_export_rows']);
    }

    /**
     * Register tab in tabs list.
     *
     * @param array $tabs
     * @param string $current
     *
     * @return array
     */
    public function register_tab($tabs, $current)
    {
        $tabs['log'] = [
            'id' => 'log',
            'name' => esc_html_x('Log', 'tab name', 'helpful'),
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
        if (!Helper::is_active_tab('log')) {
            return;
        }

        $template = HELPFUL_PATH . 'templates/tabs/tab-log.php';

        if (file_exists($template)) {
            include_once $template;
        }
    }

    /**
     * Enqueue scripts
     *
     * @param string $hook_suffix
     *
     * @return void
     */
    public function enqueue_scripts($hook_suffix)
    {
        if ('toplevel_page_helpful' !== $hook_suffix) {
            return;
        }

        if (!Helper::is_active_tab('log')) {
            return;
        }

        $plugin = Helper::get_plugin_data();

        $file = plugins_url('core/assets/js/admin-log.js', HELPFUL_FILE);
        wp_enqueue_script('helpful-admin-log', $file, ['jquery'], $plugin['Version'], true);

        $language = Helper::datatables_language_string();

        $vars = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('helpful_admin_log'),
            'language' => $language,
            'nonces' => [
                'delete_rows' => wp_create_nonce('helpful/logs/delete_rows'),
                'export_rows' => wp_create_nonce('helpful/logs/export_rows'),
            ],
            'translations' => [
                'delete_confirm' => [
                    'plural' => _x('Are you sure you want to delete the selected %d entries?', 'log alert', 'helpful'),
                    'singular' => _x('Are you sure you want to delete the selected entry?', 'log alert', 'helpful'),
                ],
                'delete' => _x('Delete', 'log button', 'helpful'),
                'export' => _x('Export', 'log button', 'helpful'),
            ],
        ];

        $vars = apply_filters('helpful_logs_ajax_vars', $vars);

        wp_localize_script('helpful-admin-log', 'helpful_admin_log', $vars);
    }

    /**
     * Loads the data for the Datatable via Ajax.
     *
     * @return void
     */
    public function ajax_get_log_data()
    {
        check_ajax_referer('helpful_admin_log');

        $response = [];

        $rows = Helpers\Votes::get_votes();

        if ($rows):
            foreach ($rows as $row):
                $post = get_post($row->post_id);

                if (!isset($post->ID)) {

                    $status = apply_filters('helpful_logs_delete_empty_item', '1');

                    if (1 === intval($status)) {
                        Helpers\Votes::delete_vote($row->id);
                    }
                    continue;
                }

                $title = $post->post_title;
                $length = apply_filters('helpful_datatables_string_length', 35);

                if (strlen($title) > $length) {
                    $title = substr($title, 0, $length) . '...';
                }

                if ('' === $title || 0 === strlen($title)) {
                    $title = esc_html_x('No title found', 'message if no post title was found', 'helpful');
                }

                $user_string = ($row->user) ? 1 : 0;
                $user_string = apply_filters('helpful/logs/user_string', $user_string, $row);

                $data = [
                    'row_id' => $row->id,
                    'post_id' => $post->ID,
                    'post_title' => sprintf(
                        '<a href="%1$s" title="%2$s" target="_blank">%2$s</a>',
                        esc_url(get_the_permalink($post->ID)),
                        esc_html($title)
                    ),
                    'pro' => $row->pro,
                    'contra' => $row->contra,
                    'user' => $user_string,
                    'time' => [
                        'display' => date_i18n('Y-m-d H:i:s', strtotime($row->time)),
                        'timestamp' => date_i18n('U', strtotime($row->time)),
                    ],
                ];

                $response['data'][] = apply_filters('helpful_logs_item', $data);
            endforeach;
        endif;

        wp_send_json($response);
    }

    /**
     * Register tab alerts for settings saved and other.
     *
     * @return void
     */
    public function register_tab_alerts()
    {
        if (isset($_GET['settings-updated'])) {
            $message = esc_html_x('Settings saved.', 'tab alert after save', 'helpful');
            echo Helper::get_alert($message, 'success', 1500);
        }
    }

    /**
     * Deletes entries in the Helpful database submitted with Ajax.
     * The entries are selected and submitted in the Helpful logs.
     *
     * @return void
     */
    public function ajax_delete_rows()
    {
        check_ajax_referer('helpful/logs/delete_rows');

        if (isset($_REQUEST['rows']) && is_array($_REQUEST['rows'])) {
            $rows = array_map('intval', $_REQUEST['rows']);

            foreach ($rows as $row) {
                if (is_numeric($row)) {
                    Helpers\Votes::delete_vote($row);
                }
            }

            wp_send_json_success(_x('The selected entries have been deleted.', 'logs alert', 'helpful'));
        }

        wp_send_json_error(_x('The selected entries could not be deleted.', 'logs alert', 'helpful'));
    }

    /**
     * Exports entries to a CSV and returns the file URL to the client.
     *
     * @return void
     */
    public function ajax_export_rows()
    {
        check_ajax_referer('helpful/logs/export_rows');

        $response = [
            'status' => 'error',
            'file' => '',
            'message' => esc_html_x('The selected entries could not be exported.', 'logs alert', 'helpful'),
        ];

        if (isset($_REQUEST['rows'])) {
            $lines = [];

            if (is_array($_REQUEST['rows'])) {
                $rows = array_map('intval', $_REQUEST['rows']);
                if ($rows) {
                    foreach ($rows as $row) {
                        $row = Helpers\Votes::get_vote($row);

                        if (!$row) {
                            continue;
                        }

                        $user_string = $row->user;
                        $user_string = apply_filters('helpful/logs/user_string', $user_string, $row);
                        $author_id = get_post_field('post_author', $row->post_id);
                        $post_author = sprintf('%s (%s)', get_the_author_meta('display_name', $author_id), get_the_author_meta('ID', $author_id));
                        $post_author = apply_filters('helpful/logs/post_author', $post_author, $author_id);

                        $line = [
                            esc_html_x('Post', 'log table head', 'helpful') => $row->post_id,
                            esc_html_x('Post Author', 'log table head', 'helpful') => $post_author,
                            esc_html_x('Title', 'log table head', 'helpful') => esc_html(get_the_title($row->post_id)),
                            esc_html_x('Pro', 'log table head', 'helpful') => $row->pro,
                            esc_html_x('Contra', 'log table head', 'helpful') => $row->contra,
                            esc_html_x('User', 'log table head', 'helpful') => $user_string,
                            esc_html_x('Time', 'log table head', 'helpful') => date_i18n('Y-m-d H:i:s', strtotime($row->time)),
                        ];

                        $lines[] = apply_filters('helpful/logs/export/line', $line, $row);
                    }
                }
            } else {
                $rows = Helpers\Votes::get_votes();
                if ($rows) {
                    foreach ($rows as $row) {
                        $user_string = $row->user;
                        $user_string = apply_filters('helpful/logs/user_string', $user_string, $row);
                        $author_id = get_post_field('post_author', $row->post_id);
                        $post_author = sprintf('%s (%s)', get_the_author_meta('display_name', $author_id), get_the_author_meta('ID', $author_id));
                        $post_author = apply_filters('helpful/logs/post_author', $post_author, $author_id);

                        $line = [
                            esc_html_x('Post', 'log table head', 'helpful') => $row->post_id,
                            esc_html_x('Post Author', 'log table head', 'helpful') => $post_author,
                            esc_html_x('Title', 'log table head', 'helpful') => esc_html(get_the_title($row->post_id)),
                            esc_html_x('Pro', 'log table head', 'helpful') => $row->pro,
                            esc_html_x('Contra', 'log table head', 'helpful') => $row->contra,
                            esc_html_x('User', 'log table head', 'helpful') => $user_string,
                            esc_html_x('Time', 'log table head', 'helpful') => date_i18n('Y-m-d H:i:s', strtotime($row->time)),
                        ];

                        $lines[] = apply_filters('helpful/logs/export/line', $line, $row);
                    }
                }
            }

            if (!empty($lines)) {
                $csv = new Services\CSV(apply_filters('helpful/logs/export/csv_name', 'logs.csv'));
                $csv->add_items($lines);
                $csv->create_file();
                $response['status'] = 'success';
                $response['file'] = $csv->get_file();
            }
        }

        wp_send_json($response);
    }
}
