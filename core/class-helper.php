<?php
/**
 * @package Helpful
 * @subpackage Core
 * @version 4.4.59
 * @since 4.3.0
 */
namespace Helpful\Core;

use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Helper
{
    /**
     * Returns the WordPress information about the plugin.
     *
     * @version 4.3.0
     * @return array
     */
    public static function get_plugin_data()
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $transient_name = 'helpful_plugin_data';

        $plugin_data = get_transient($transient_name);

        if (false === $plugin_data) {
            $plugin_data = get_plugin_data(HELPFUL_FILE);

            set_transient($transient_name, maybe_serialize($plugin_data), HOUR_IN_SECONDS);
        }

        return maybe_unserialize($plugin_data);
    }

    /**
     * Set custom timezone if set in the options.
     *
     * @version 4.4.59
     * 
     * @return void
     */
    public static function set_timezone()
    {
        $options = new Services\Options();

        $timezone = $options->get_option('helpful_timezone', date_default_timezone_get(), 'esc_attr');

        if (isset($timezone) && '' !== trim($timezone) && false === self::is_timezone($timezone)) {
            $options->update_option('helpful_timezone', '');
            return false;
        }

        if (isset($timezone) && '' !== trim($timezone)) {
            date_default_timezone_set($timezone);
        }
    }

    /**
     * Checks if a timezone exists.
     * 
     * @source https://stackoverflow.com/a/5823217
     *
     * @version 4.4.58
     *
     * @param string $timezone
     *
     * @return bool
     */
    public static function is_timezone($timezone)
    {
        @$tz=timezone_open($timezone);
        return $tz!==FALSE;
    }

    /**
     * Returns the available options for Same-Site cookies.
     *
     * @return array
     */
    public static function get_samesite_options()
    {
        $defaults = ['None', 'Lax', 'Strict'];

        return apply_filters('helpful_samesite_options', $defaults);
    }

    /**
     * Checks if the current tab is in use.
     *
     * @param string $tab
     *
     * @return bool
     */
    public static function is_active_tab($tab)
    {
        $screen = get_current_screen();

        if ('toplevel_page_helpful' !== $screen->base) {
            return false;
        }

        $current = apply_filters('helpful_current_tab', false);

        if ($tab !== $current) {
            return false;
        }

        return true;
    }

    /**
     * Get credits array.
     *
     * @return array
     */
    public static function get_credits_data()
    {
        $credits = [
            'url' => apply_filters('helpful_credits_url', 'https://helpful-plugin.info'),
            'name' => apply_filters('helpful_credits_name', 'Helpful'),
            'rel' => apply_filters('helpful_credits_rel', 'noopener'),
        ];

        $html = '<a href="%1$s" target="_blank" rel="%2$s">%3$s</a>';
        $html = apply_filters('helpful_credits_html', $html);

        $credits['html'] = sprintf($html, $credits['url'], $credits['rel'], $credits['name']);

        return apply_filters('helpful_default_credits', $credits);
    }

    /**
     * Get admin tabs.
     *
     * @return array
     */
    public static function get_admin_tabs()
    {
        $current = apply_filters('helpful_current_tab', false);
        return apply_filters('helpful_get_admin_tabs', __return_empty_array(), $current);
    }

    /**
     * Get tab url.
     *
     * @param string $tab
     *
     * @return string
     */
    public static function get_tab_url($tab)
    {
        $tab = sanitize_text_field(wp_unslash($tab));
        return apply_filters('helpful_get_tab_url', admin_url('admin.php?page=helpful&tab=' . $tab));
    }

    /**
     * Get tab class.
     *
     * @param string $tab
     *
     * @return string
     */
    public static function get_tab_class($tab)
    {
        $tab = sanitize_text_field(wp_unslash($tab));

        $class = '';

        if (self::is_active_tab($tab)) {
            $class = 'active';
        }

        return apply_filters('helpful_get_tab_class', $class);
    }

    /**
     * Get tab attribute.
     *
     * @param string $tab
     *
     * @return string
     */
    public static function get_tab_attr($tab)
    {
        $tab = sanitize_text_field(wp_unslash($tab));

        $attr = '';

        if (self::is_active_tab($tab)) {
            $attr = 'selected';
        }

        return apply_filters('helpful_get_tab_attr', $attr);
    }

    /**
     * Returns the URL of the logo.
     *
     * @return string
     */
    public static function get_logo()
    {
        $logo = plugins_url('core/assets/images/helpful-heart.svg', HELPFUL_FILE);
        return apply_filters('helpful_logo', $logo);
    }

    /**
     * Returns an array of media data from Helpful.
     *
     * @return array
     */
    public static function get_plugin_media_data()
    {
        $media = [
            'logo' => self::get_logo(),
            'color' => '#88c057',
        ];

        return apply_filters('helpful_media_data', $media);
    }

    /**
     * Plugins URL
     *
     * @param string $path
     *
     * @return string
     */
    public static function plugins_url($path)
    {
        return plugins_url($path, HELPFUL_FILE);
    }

    /**
     * Filter the conditions and check if you are for example on the homepage and not in the single view.
     *
     * @version 4.3.0
     *
     * @return array
     */
    public static function get_conditions()
    {
        $conditions = [];

        if (!is_singular()) {
            $conditions[] = 'is_not_singular';
        }

        if (is_archive()) {
            $conditions[] = 'is_archive';
        }

        if (is_home()) {
            $conditions[] = 'is_home';
        }

        if (is_front_page()) {
            $conditions[] = 'is_front_page';
        }

        return apply_filters('helpful_conditions', $conditions);
    }

    /**
     * Get html output for alerts.
     *
     * @param string $message
     * @param string $type
     * @param int $close
     *
     * @return string
     */
    public static function get_alert($message, $type = 'none', $close = 2500)
    {
        $classes = 'helpful-alert helpful-auto-close';

        $types = ['success', 'danger', 'info'];

        if (in_array($type, $types)) {
            $classes .= ' helpful-alert-' . $type;
        }

        $close = intval($close);

        return sprintf('<div class="%s" data-close="%s">%s</div>', $classes, $close, $message);
    }

    /**
     * Translatable Datatables Language String
     *
     * @return array
     */
    public static function datatables_language_string()
    {
        $language = [
            'decimal' => esc_html_x('', 'datatables decimal', 'helpful'),
            'emptyTable' => esc_html_x('No data available in table', 'datatables emptyTable', 'helpful'),
            'info' => esc_html_x('Showing _START_ to _END_ of _TOTAL_ entries', 'datatables info', 'helpful'),
            'infoEmpty' => esc_html_x('Showing 0 to 0 of 0 entries', 'datatables infoEmpty', 'helpful'),
            'infoFiltered' => esc_html_x('(filtered from _MAX_ total entries)', 'datatables infoFiltered', 'helpful'),
            'infoPostFix' => esc_html_x('', 'datatables infoPostFix', 'helpful'),
            'thousands' => esc_html_x(',', 'datatables thousands', 'helpful'),
            'lengthMenu' => esc_html_x('Show _MENU_ entries', 'datatables lengthMenu', 'helpful'),
            'loadingRecords' => esc_html_x('Loading...', 'datatables loadingRecords', 'helpful'),
            'processing' => esc_html_x('Processing...', 'datatables processing', 'helpful'),
            'search' => esc_html_x('Search:', 'datatables search', 'helpful'),
            'zeroRecords' => esc_html_x('No matching records found', 'datatables zeroRecords', 'helpful'),
            'paginate' => [
                'first' => esc_html_x('First', 'datatables first', 'helpful'),
                'last' => esc_html_x('Last', 'datatables last', 'helpful'),
                'next' => esc_html_x('Next', 'datatables next', 'helpful'),
                'previous' => esc_html_x('Previous', 'datatables previous', 'helpful'),
            ],
            'aria' => [
                'sortAscending' => esc_html_x(': activate to sort column ascending', 'datatables sortAscending', 'helpful'),
                'sortDescending' => esc_html_x(': activate to sort column descending', 'datatables sortDescending', 'helpful'),
            ],
            'select' => [
                'rows' => [
                    '_' => esc_html_x('%d rows selected', 'datatables previous', 'helpful'),
                    '0' => esc_html_x('', 'datatables previous', 'helpful'),
                    '1' => esc_html_x('1 row selected', 'datatables previous', 'helpful'),
                ],
            ],
            'buttons' => [
                'print' => esc_html_x('Print', 'datatables print', 'helpful'),
                'colvis' => esc_html_x('Columns', 'datatables colvis', 'helpful'),
                'copy' => esc_html_x('Copy', 'datatables copy', 'helpful'),
                'copyTitle' => esc_html_x('Copy to clipboard', 'datatables copyTitle', 'helpful'),
                'copyKeys' => esc_html_x(
                    'Press <i>ctrl</i> or <i>\u2318</i> + <i>C</i> to copy table<br>to temporary storage.<br><br>To cancel, click on the message or press Escape.',
                    'datatables copyKeys',
                    'helpful'
                ),
                'copySuccess' => [
                    '_' => esc_html_x('%d rows copied', 'datatables copySuccess', 'helpful'),
                    '1' => esc_html_x('1 row copied', 'datatables copySuccess', 'helpful'),
                ],
                'pageLength' => [
                    '-1' => esc_html_x('Show all rows', 'datatables pageLength', 'helpful'),
                    '_' => esc_html_x('Show %d rows', 'datatables pageLength', 'helpful'),
                ],
            ],
        ];

        return apply_filters('helpful_datatables_language', $language);
    }

    /**
     * Returns non-permitted characters and words from the WordPress blacklist.
     *
     * @version 4.4.59
     * 
     * @return string
     */
    public static function get_disallowed_keys()
    {
        $options = new Services\Options();

        if (version_compare(get_bloginfo('version'), '5.5.0') >= 0) {
            return trim($options->get_option('disallowed_keys', '', 'esc_attr'));
        }

        return trim($options->get_option('blacklist_keys', '', 'esc_attr'));
    }

    /**
     * Checks if the content is set on the internal blacklist of WordPress
     *
     * @param string $content the content to be checked.
     *
     * @return bool
     */
    public static function backlist_check($content)
    {
        $mod_keys = self::get_disallowed_keys();

        if ('' === $mod_keys) {
            return false;
        }

        $without_html = wp_strip_all_tags($content);
        $words = explode("\n", $mod_keys);

        foreach ((array) $words as $word):
            $word = trim($word);

            if (empty($word)) {
                continue;
            }

            $word = preg_quote($word, '#');
            $pattern = "#$word#i";

            if (preg_match($pattern, $content) || preg_match($pattern, $without_html)) {
                return true;
            }
        endforeach;

        return false;
    }

    /**
     * Checks if the current page uses AMP.
     *
     * @return bool
     */
    public static function is_amp()
    {
        if (function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint()) {
            return true;
        }

        if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
            return true;
        }

        return false;
    }

    /**
     * Sets a capability.
     *
     * @param string $option
     * @param string $value
     *
     * @return void
     */
    public static function set_capability($option, $value)
    {
        $options = [
            'helpful_capability',
            'helpful_settings_capability',
            'helpful_feedback_capability',
        ];

        if (!in_array($option, $options)) {
            return;
        }

        if (null === $value || false === $value) {
            delete_option($option);
        }

        update_option(
            sanitize_text_field($option),
            sanitize_text_field($value)
        );
    }

    /**
     * Checks if the feedback was deactivated by option.
     *
     * @version 4.4.59
     *
     * @return bool
     */
    public static function is_feedback_disabled()
    {
        $options = new Services\Options();

        if ('on' === $options->get_option('helpful_feedback_disabled', 'off', 'esc_attr')) {
            return true;
        }

        return false;
    }

    /**
     * Returns the allowed HTML tags and attributes for the kses function
     * that are allowed when saving the settings.
     *
     * @version 4.4.57
     * @since 4.4.56
     *
     * @return array
     */
    public static function kses_allowed_tags()
    {
        $tags = [
            'a' => [
                'class' => [],
                'href' => [],
                'title' => [],
            ],
            'br' => [
                'class' => [],
            ],
            'em' => [
                'class' => [],
            ],
            'strong' => [
                'class' => [],
            ],
            'hr' => [
                'class' => [],
            ],
            'p' => [
                'class' => [],
            ],
            'div' => [
                'class' => [],
            ],
            'i' => [
                'class' => [],
            ],
        ];

        return apply_filters('helpful/kses/allowed_tags', $tags);
    }
}
