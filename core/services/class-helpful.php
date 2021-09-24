<?php
/**
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.4.53
 * @since 4.4.47
 */
namespace Helpful\Core\Services;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Helpful
{
    /**
     * @var int
     */
    private $post_id;

    /**
     * @var int
     */
    private $shortcodes_found;

    /**
     * @var array
     */
    private $atts;

    /**
     * @param int|null $post_id
     */
    public function __construct($post_id = null, $atts = [])
    {
        $this->post_id = (null === $post_id) ? get_the_ID() : $post_id;
        $this->atts = $atts;
    }

    /**
     * Sets the ID for this instance and returns it.
     *
     * @return string
     */
    public function get_id()
    {
        if (!isset($this->atts['heading'])) {
            return null;
        }

        global $wp;

        $url = home_url($wp->request);
        $id = md5($url . $this->atts['heading']);

        $instance_id = Helpers\Instance::insert_instance($id, $this->get_post_id(), $this->atts['heading']);

        return $instance_id;
    }

    /**
     * Allows to set the attributes for this instance afterwards.
     *
     * @param array $atts
     * 
     * @return void
     */
    public function set_atts($atts = [])
    {
        $this->atts = $atts;
    }

    /**
     * Returns the current post id.
     *
     * @return int
     */
    public function get_post_id()
    {
        return $this->post_id;
    }

    /**
     * Returns the shortcode attributes and defaults for this Helpful instance.
     *
     * @return array
     */
    public function get_atts()
    {
        return $this->atts;
    }

    /**
     * Returns the content of the instance.
     * 
     * @return string
     */
    public function get_template()
    {
        $helpful = $this->get_atts();

        $template = HELPFUL_PATH . 'templates/helpful.php';
        $custom_template = locate_template('helpful/helpful.php');

        if ($custom_template) {
            $template = $custom_template;
        }

        $helpful['instance'] = $this->get_id();
        $helpful['shortcode_class'] = $this->get_css_classes();

        ob_start();
        do_action('helpful_before');
        include $template;
        do_action('helpful_after');
        $content = ob_get_contents();
        ob_end_clean();

        $content = Helpers\Values::convert_tags($content, $helpful['post_id']);

        return $content;
    }

    /**
     * Checks if the current Helpful user has already voted for this instance.
     *  
     * @version 4.4.53
     *
     * @return bool
     */
    public function current_user_has_voted()
    {
        $user_id = Helpers\User::get_user();
        
        if (Helpers\User::check_user($user_id, $this->get_post_id(), $this->get_id())) {
            return true;
        }
        
        return false;
    }

    /**
     * @return array
     */
    public function get_css_classes()
    {
        $helpful = $this->get_atts();
        $classes = [];

        if (isset($helpful['shortcode_class'])) {
            if (is_array($helpful['shortcode_class'])) {
                $classes = $helpful['shortcode_class'];
            } else {
                $classes[] = $helpful['shortcode_class'];
            }
        }

        if ($this->current_user_has_voted()) {
            $classes[] = 'voted';
        }

        return implode(' ', $classes);
    }
}
