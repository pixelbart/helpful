<?php
/**
 * Class for setting shortcodes and the_content.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 2.0.0
 */
class Helpful_Shortcodes
{
    static $instance;

    /**
     * Class constructor.
     */
    public function __construct() 
    {
        add_filter('the_content', [ $this , 'addToContent' ]);
        add_shortcode('helpful', [$this, 'shortcodeHelpful']);
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
     * Add helpful to post content
     *
     * @param string $content post content
     *
     * @return string
     */
    public function addToContent($content) 
    {
        global $post;

        $post_types = get_option('helpful_post_types');
        $user_id = Helpful_Helper_Values::getUser();

        if (!in_array($post->post_type, $post_types)) {
            return $content;
        }

        if (get_option('helpful_hide_in_content')) {
            return $content;
        }

        if (get_option('helpful_exists_hide') && Helpful_Helper_Values::checkUser($user_id, $post->ID)) {
            return $content;
        }

        if (!is_singular()) {
            return $content;
        }

        // Helpful atts
        $helpful = Helpful_Helper_Values::getDefaults();

        $hidden = false;
        $class = '';

        if (1 == $helpful['exists']) {
            if (1 == $helpful['exists-hide']) {
                return;
            }

            $hidden = true;
            $class = 'helpful-exists';
            $helpful['content'] = $helpful['exists_text'];
        }

        ob_start();

        $default_template = HELPFUL_PATH . 'templates/helpful.php';
        $custom_template  = locate_template('helpful/helpful.php');

        // check if custom frontend exists
        if ('' !== $custom_template) {
            include $custom_template;
        } else {
            include $default_template;
        }

        $content .= ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Callback for helpful shortcode
     *
     * @global $post
     *
     * @param array $atts shortcode attributes
     *
     * @return string
     */
    public function shortcodeHelpful($atts) 
    {
        global $post;

        // Default Atts
        $defaults = Helpful_Helper_Values::getDefaults();
        $user_id = Helpful_Helper_Values::getUser();

        if (get_option('helpful_exists_hide') && Helpful_Helper_Values::checkUser($user_id, $post->ID)) {
            return;
        }

        // Shortcode Atts
        $helpful = shortcode_atts($defaults, $atts);

        $hidden = false;
        $class = '';

        if (1 == $helpful['exists']) {
            if (1 == $helpful['exists-hide']) {
                return;
            }

            $hidden = true;
            $class = 'helpful-exists';
            $helpful['content'] = $helpful['exists_text'];
        }

        ob_start();

        $default_template = HELPFUL_PATH . 'templates/helpful.php';
        $custom_template  = locate_template('helpful/helpful.php');

        // check if custom frontend exists
        if ('' !== $custom_template) {
            include $custom_template;
        } else {
            include $default_template;
        }

        $content .= ob_get_contents();
        ob_end_clean();

        return $content;
    }
}