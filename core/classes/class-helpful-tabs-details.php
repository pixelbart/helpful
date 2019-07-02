<?php
/**
 * Admin tab for details.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.0.0
 */
class Helpful_Tabs_Details extends Helpful_Tabs
{
    static $instance;
    public $tab_info, $tab_content;

    /**
     * Class constructor.
     */
    public function __construct() 
    {
        $this->setupTab();

        // add_action( 'admin_menu', [ $this, 'registerMenu' ] );
        add_action('admin_init', [ $this, 'registerSettings' ]);
        add_filter('helpful_admin_tabs', [ $this, 'registerTab' ]);
        add_action('helpful_tabs_content', [$this, 'addTabContent']);
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
     * Add tab to helpful admin menu
     *
     * @return void
     */
    public function setupTab() 
    {
        $this->tab_info = [ 'id' => 'details', 'name' => esc_html_x('Details', 'tab name', 'helpful'), ];
        $this->tab_content = [ $this, 'renderCallback' ];
    }

    /**
     * Include options page
     *
     * @return void
     */
    public function renderCallback() 
    {

        $post_types = get_post_types([ 'public' => true ]);
        $private_post_types = get_post_types([ 'public' => false ]);

        if (isset($private_post_types)) {
            $post_types = array_merge($post_types, $private_post_types);
        } else {
            $private_post_types = [];
        }

        include_once HELPFUL_PATH . 'core/tabs/tab-details.php';
    }

    /**
     * Register settings for admin page
     *
     * @return void
     */
    public function registerSettings() 
    {
        $fields = [
            'helpful_credits',
            'helpful_hide_in_content',
            'helpful_post_types',
            'helpful_exists_hide',
            'helpful_count_hide',
            'helpful_widget',
            'helpful_widget_amount',
            'helpful_widget_pro',
            'helpful_widget_contra',
            'helpful_widget_pro_recent',
            'helpful_widget_contra_recent',
            'helpful_only_once',
            'helpful_percentages',
            'helpful_form_status_pro',
            'helpful_form_email_pro',
            'helpful_form_status_contra',
            'helpful_form_email_contra',
            'helpful_metabox',
        ];

        foreach ( $fields as $field ) {
            register_setting('helpful-details-settings-group', $field);
        }
    }
}