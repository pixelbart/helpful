<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.5.0
 * @since 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Module;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Admin
{
    use Module;

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_menu', [ & $this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [ & $this, 'enqueue_scripts']);

        add_action('admin_init', [ & $this, 'init_columns']);

        add_action('wp_ajax_helpful_update_options', [ & $this, 'update_options']);
    }

    /**
     * Register admin menu.
     *
     * @version 4.4.59
     *
     * @return void
     */
    public function register_admin_menu()
    {
        $options = new Services\Options();

        add_menu_page(
            __('Helpful', 'helpful'),
            __('Helpful', 'helpful'),
            $options->get_option('helpful_capability', 'manage_options', 'blank'),
            'helpful',
            [ & $this, 'callback_admin_page'],
            'dashicons-thumbs-up',
            99
        );

        add_submenu_page(
            'helpful',
            __('Settings', 'helpful'),
            __('Settings', 'helpful'),
            $options->get_option('helpful_settings_capability', 'manage_options', 'blank'),
            'helpful',
            [ & $this, 'callback_admin_page']
        );
    }

    /**
     * Callback for admin page.
     *
     * @return void
     */
    public function callback_admin_page()
    {
        $tabs = Helper::get_admin_tabs();

        foreach ($tabs as $key => $data):
            if (!isset($tabs[$key]['href'])) {
                $tabs[$key]['href'] = Helper::get_tab_url($key);
            }

            if (!isset($tabs[$key]['class'])) {
                $tabs[$key]['class'] = Helper::get_tab_class($key);
            }

            if (!isset($tabs[$key]['attr'])) {
                $tabs[$key]['attr'] = Helper::get_tab_attr($key);
            }
        endforeach;

        include_once HELPFUL_PATH . 'templates/admin.php';
    }

    /**
     * Enqueue backend scripts and styles, if current screen is helpful
     *
     * @version 4.4.59
     *
     * @param string $hook_suffix
     *
     * @return void
     */
    public function enqueue_scripts($hook_suffix)
    {
        $options = new Services\Options();

        /* shrink admin columns */
        if ('on' === $options->get_option('helpful_shrink_admin_columns', 'off', 'esc_attr')) {
            $file = plugins_url('core/assets/css/admin-columns.css', HELPFUL_FILE);
            wp_enqueue_style('helpful-admin-columns', $file);
        }

        if ('toplevel_page_helpful' !== $hook_suffix) {
            return;
        }

        $plugin = Helper::get_plugin_data();

        $file = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css';
        wp_enqueue_style('helpful-chartjs', $file, [], '2.9.3');

        $file = '//cdn.datatables.net/v/ju/dt-1.10.20/b-1.6.1/b-colvis-1.6.1/r-2.2.3/sc-2.0.1/datatables.min.css';
        wp_enqueue_style('helpful-datatables', $file, [], '1.10.20');

        $file = plugins_url('core/assets/vendor/jqueryui/jquery-ui.min.css', HELPFUL_FILE);
        wp_enqueue_style('helpful-jquery', $file, [], $plugin['Version']);

        $file = plugins_url('core/assets/vendor/jqueryui/jquery-ui.structure.min.css', HELPFUL_FILE);
        wp_enqueue_style('helpful-jquery-structure', $file, [], $plugin['Version']);

        $file = plugins_url('core/assets/vendor/jqueryui/jquery-ui.theme.min.css', HELPFUL_FILE);
        wp_enqueue_style('helpful-jquery-theme', $file, [], $plugin['Version']);

        $file = plugins_url('core/assets/css/admin.css', HELPFUL_FILE);
        wp_enqueue_style('helpful-backend', $file, [], $plugin['Version']);

        $file = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js';
        wp_enqueue_script('helpful-chartjs', $file, [], '2.9.3', true);

        $file = plugins_url('core/assets/vendor/jqueryui/jquery-ui.min.js', HELPFUL_FILE);
        wp_enqueue_script('helpful-jquery', $file, [], $plugin['Version'], true);

        $file = '//cdn.datatables.net/v/ju/dt-1.10.20/b-1.6.1/b-colvis-1.6.1/r-2.2.3/sc-2.0.1/sl-1.3.3/datatables.min.js';
        wp_enqueue_script('helpful-datatables', $file, [], '1.10.20', true);

        $file = plugins_url('core/assets/js/admin.js', HELPFUL_FILE);
        wp_enqueue_script('helpful-admin', $file, [], $plugin['Version'], true);

        $language = Helper::datatables_language_string();

        $vars = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('helpful_admin_nonce'),
            'language' => $language,
            'feedback' => true,
        ];

        if (Helper::is_feedback_disabled()) {
            $vars['feedback'] = false;
        }

        $vars = apply_filters('helpful_admin_ajax_vars', $vars);

        wp_localize_script('helpful-admin', 'helpful_admin', $vars);
    }

    /**
     * Register columns on admin pages
     *
     * @global $pagenow
     * @version 4.4.59
     *
     * @return void
     */
    public function init_columns()
    {
        global $pagenow;

        $options = new Services\Options();

        $post_types = $options->get_option('helpful_post_types', [], 'esc_attr');
        $hide_cols = $options->get_option('helpful_hide_admin_columns', 'off', 'esc_attr');

        if (isset($hide_cols) && 'on' === $hide_cols) {
            return;
        }

        /* Allows filtering the content afterwards */
        $post_types = apply_filters('helpful_admin_columns_post_types', $post_types);

        if (!isset($post_types) || !is_array($post_types)) {
            return;
        }

        $type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';

        if (!in_array($type, $post_types)) {
            return;
        }

        foreach ($post_types as $type):
            if (is_admin() && 'edit.php' === $pagenow) {
                add_filter('manage_' . $type . '_posts_columns', [ & $this, 'register_columns']);
                add_action('manage_' . $type . '_posts_custom_column', [ & $this, 'populate_columns'], 10, 2);
                add_filter('manage_edit-' . $type . '_sortable_columns', [ & $this, 'register_sortable_columns']);
                add_action('pre_get_posts', [ & $this, 'sort_columns_query']);
            }
        endforeach;
    }

    /**
     * Set column titles
     * 
     * @version 4.4.59
     *
     * @param array $defaults defatul columns.
     *
     * @return array
     */
    public function register_columns($defaults)
    {
        $options = new Services\Options();

        $columns = [];
        foreach ($defaults as $key => $value):
            $columns[$key] = $value;

            if ('title' === $key) {
                $columns['helpful-pro'] = $options->get_option('helpful_column_pro', '', 'esc_attr') ? $options->get_option('helpful_column_pro', '', 'esc_attr') : _x('Pro', 'column name', 'helpful');
                $columns['helpful-contra'] = $options->get_option('helpful_column_contra', '', 'esc_attr') ? $options->get_option('helpful_column_contra', '', 'esc_attr') : _x('Contra', 'column name', 'helpful');
                $columns['helpful-feedback'] = $options->get_option('helpful_column_feedback', '', 'esc_attr') ? $options->get_option('helpful_column_feedback', '', 'esc_attr') : _x('Feedback', 'column name', 'helpful');
            }
        endforeach;

        return $columns;
    }

    /**
     * Columns callback
     *
     * @version 4.4.59
     *
     * @param string  $column_name column name.
     * @param integer $post_id     post id.
     *
     * @return void
     */
    public function populate_columns($column_name, $post_id)
    {
        $options = new Services\Options();

        if ('helpful-pro' === $column_name) {
            if ('on' === $options->get_option('helpful_percentages', 'off', 'esc_attr')) {
                $percent = Helpers\Stats::get_pro($post_id, true);
                $pro = Helpers\Stats::get_pro($post_id);
                update_post_meta($post_id, 'helpful-pro', $pro);
                printf('%d (%s%%)', (int) $pro, $percent);
            } else {
                $pro = Helpers\Stats::get_pro($post_id);
                $pro = intval($pro);
                update_post_meta($post_id, 'helpful-pro', $pro);
                echo $pro;
            }
        }

        if ('helpful-contra' === $column_name) {
            if ('on' === $options->get_option('helpful_percentages', 'off', 'esc_attr')) {
                $percent = Helpers\Stats::get_contra($post_id, true);
                $contra = Helpers\Stats::get_contra($post_id);
                update_post_meta($post_id, 'helpful-contra', $contra);
                printf('%d (%s%%)', (int) $contra, $percent);
            } else {
                $contra = Helpers\Stats::get_contra($post_id);
                $contra = intval($contra);
                update_post_meta($post_id, 'helpful-contra', $contra);
                echo $contra;
            }
        }

        if ('helpful-feedback' === $column_name) {
            $count = Helpers\Feedback::get_feedback_count($post_id);
            $count = intval($count);

            update_post_meta($post_id, 'helpful-feedback-count', $count);

            if (0 < $count) {
                $url = admin_url('admin.php?page=helpful_feedback&post_id=' . $post_id);
                printf('<a href="%s" target="_blank">%s</a>', esc_url($url), intval($count));
            } else {
                echo $count;
            }
        }
    }

    /**
     * Set sortable columns
     *
     * @param array $columns columns.
     * @return array
     */
    public function register_sortable_columns($columns)
    {
        $columns['helpful-pro'] = 'helpful-pro';
        $columns['helpful-contra'] = 'helpful-contra';
        $columns['helpful-feedback'] = 'helpful-feedback';

        return $columns;
    }

    /**
     * Make values sortable in columns
     *
     * @param object $query current query.
     * @return void
     */
    public function sort_columns_query($wp_query)
    {
        if (!is_admin()) {
            return;
        }

        $orderby = $wp_query->get('orderby');

        if ('helpful-pro' === $orderby) {
            $meta_query = [
                'relation' => 'OR',
                [
                    'key' => 'helpful-pro',
                    'compare' => 'NOT EXISTS',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => 'helpful-pro',
                    'type' => 'NUMERIC',
                ],
            ];

            $wp_query->set('meta_query', $meta_query);
            $wp_query->set('orderby', 'meta_value');
        }

        if ('helpful-contra' === $orderby) {
            $meta_query = [
                'relation' => 'OR',
                [
                    'key' => 'helpful-contra',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => 'helpful-contra',
                ],
            ];

            $wp_query->set('meta_query', $meta_query);
            $wp_query->set('orderby', 'meta_value');
        }

        if ('helpful-feedback' === $orderby) {
            $meta_query = [
                'relation' => 'OR',
                [
                    'key' => 'helpful-feedback-count',
                    'compare' => 'NOT EXISTS',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => 'helpful-feedback-count',
                    'type' => 'NUMERIC',
                ],
            ];

            $wp_query->set('meta_query', $meta_query);
            $wp_query->set('orderby', 'meta_value');
        }
    }

    /**
     * Sets the German language if the user has set German
     * as language in the WordPress settings.
     *
     * @return string
     */
    public function set_datatables_language()
    {
        return wp_json_encode(Helpers\Values::datatables_language_string());
    }

    /**
     * @return void
     */
    public function update_options()
    {
        if (!check_admin_referer('helpful_update_options')) {
            wp_safe_redirect(wp_get_referer());
            exit;
        }

        if (array_key_exists('option_page', $_POST)) {

            $service = new Services\Options();
            $option_page = sanitize_text_field($_POST['option_page']);
            $defaults = $service->get_defaults_array($option_page);
            $options = array_keys($defaults);

            $updated = [];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $options)) {
                    $service->update_option($key, $value);
                    $updated[] = $key;
                }
            }

            $deleted = [];
            foreach ($defaults as $key => $value) {
                if (!in_array($key, array_keys($_POST))) {

                    if (is_string($value) && 'off' === $value) {
                        $service->update_option($key, 'off');
                    } else {
                        $service->delete_option($key);
                    }

                    $deleted[] = $key;
                }
            }
        }
        
        wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }
}
