<?php
/**
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.4.50
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
     * @param array $atts
     * @return void
     */
    public function set_atts($atts = [])
    {
        $this->atts = $atts;
    }

    /**
     * @return int
     */
    public function get_post_id()
    {
        return $this->post_id;
    }

    /**
     * @return array
     */
    public function get_atts()
    {
        return $this->atts;
    }

    /**
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

        ob_start();
        do_action('helpful_before');
        include $template;
        do_action('helpful_after');
        $content = ob_get_contents();
        ob_end_clean();

        $content = Helpers\Values::convert_tags($content, $helpful['post_id']);

        return $content;
    }
}
