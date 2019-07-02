<?php
/**
 * Class for feedback admin, ajax actions and adding submenu.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */
class Helpful_Feedback_Admin
{
    static $instance;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', [ $this, 'addSubmenu' ]);
        add_action('admin_enqueue_scripts', [ $this, 'enqueueScripts' ]);
        add_action('wp_ajax_helpful_admin_feedback_items', [ $this, 'getFeedbackItems' ]);
        add_action('wp_ajax_helpful_remove_feedback', [ $this, 'deleteFeedbackItem' ]);
    }

    /**
     * Add submenu item for feedback with permission
     * for all roles with publish_posts.
     *
     * @return void
     */
    public function addSubmenu()
    {
        add_submenu_page(
            'helpful',
            __('Helpful Feedback', 'helpful'),
            __('Feedback', 'helpful'),
            'publish_posts',
            'helpful_feedback',
            [ $this, 'adminPageCallback' ]
        );
    }

    /**
     * Render admin page for feedback.
     *
     * @return void
     */
    public function adminPageCallback()
    {
        include_once HELPFUL_PATH . "templates/admin-feedback.php";
    }

    /**
     * Enqueue backend scripts and styles.
     *
     * @return void
     */
    public function enqueueScripts()
    {
        // current screen is helpful
        // enqueue admin css
        $screen = get_current_screen();

        if ('helpful_page_helpful_feedback' === $screen->base) {

            $file = plugins_url('core/assets/css/admin-feedback.css', HELPFUL_FILE);
            wp_enqueue_style('helpful-admin-feedback', $file, HELPFUL_VERSION);

            $file = plugins_url('core/assets/js/admin-feedback.js', HELPFUL_FILE);
            wp_enqueue_script('helpful-admin-feedback', $file, [], HELPFUL_VERSION, true);

            $vars = [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('helpful_admin_feedback_nonce'),
            ];

            wp_localize_script('helpful-admin-feedback', 'helpful_admin_feedback', $vars);
        }
    }

    /**
     * Ajax get feedback items
     *
     * @return void
     */
    public function getFeedbackItems()
    {
        check_ajax_referer('helpful_admin_feedback_nonce');

        global $wpdb;

        $table_name = $wpdb->prefix . 'helpful_feedback';

        $filters = ['all', 'pro', 'contra'];
        $sql = "SELECT * FROM $table_name";

        if (isset($_REQUEST['filter']) && in_array($_REQUEST['filter'], $filters)) {
            if ('pro' == $_REQUEST['filter']) {
                $sql = $sql . " WHERE pro = 1";
            }

            if ('contra' == $_REQUEST['filter']) {
                $sql = $sql . " WHERE contra = 1";
            }
        }

        $sql = $sql . " ORDER BY time DESC";

        $posts = $wpdb->get_results($sql);

        if ($posts) {
            foreach ($posts as $post) {
                $feedback = Helpful_Helper_Feedback::getFeedback($post);
                $this->renderTemplate($feedback);
            }
        } else {
            esc_html_e('No entries found.', 'helpful');
        }

        wp_die();
    }

    /**
     * Ajax delete single feedback item.
     *
     * @return void
     */
    public function deleteFeedbackitem()
    {
        check_ajax_referer('helpful_admin_feedback_nonce');

        global $wpdb;

        if (isset($_REQUEST['feedback_id'])) {
            $feedback_id = absint($_REQUEST['feedback_id']);
            $table_name = $wpdb->prefix . 'helpful_feedback';
            $wpdb->delete($table_name, [ 'id' => $feedback_id ]);
        }

        wp_die();
    }

    /**
     * Render template for feedback item.
     *
     * @param array $feedback feedback content
     *
     * @return void
     */
    public function renderTemplate($feedback)
    {
        include HELPFUL_PATH . "templates/admin-feedback-item.php";
    }

    /**
     * Class instance.
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
}