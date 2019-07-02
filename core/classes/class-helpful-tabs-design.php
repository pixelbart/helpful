<?php
/**
 * Admin tab for design.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.0.0
 */
class Helpful_Tabs_Design
{
    static $instance;
    
    /**
     * Class constructor.
     */
    public function __construct() 
    {
        add_filter('helpful_admin_tabs', [ $this, 'registerTab' ]);
        add_action('wp_head', [ $this, 'customCSS' ], PHP_INT_MAX);
    }

    /**
     * Set instance and fire class
     *
     * @return void
     */
    public static function getInstance() 
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add tab to filter
     *
     * @global $helpful
     *
     * @param array $tabs current tabs
     *
     * @return array
     */
    public function registerTab($tabs) 
    {
        $query = [];

        $query['autofocus[section]'] = 'helpful_design';
        $section_link = add_query_arg($query, admin_url('customize.php'));

        $tabs['design'] = [
            'href'  => $section_link,
            'name'  => esc_html_x('Design', 'tab name', 'helpful'),
        ];

        return $tabs;
    }

    /**
     * Print custom css to wp_head.
     *
     * @return void
     */
    public function customCSS() 
    {
        if (get_option('helpful_css')) {
            $custom_css = get_option('helpful_css');
            printf('<style>%s</style>', $custom_css);
        }
    }
}