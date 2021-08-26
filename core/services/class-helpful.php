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
     * @return int
     */
    public function get_post_id()
    {
        return $this->post_id;
    }

    /**
     * @return int
     */
    public function get_shortcode_found()
    {
        $content = get_the_content($this->get_post_id());

        $found = 0;

        preg_match_all("/\[\[helpful/", $content, $strings);

        $found -= (isset($strings[0])) ? count($strings[0]) : $found;

        preg_match_all("/\[helpful/", $content, $shortcodes);

        $found += (isset($shortcodes[0])) ? count($shortcodes[0]) : $found;

        $post_types = get_option('helpful_post_types');

        if ('on' !== get_option('helpful_hide_in_content') && in_array(get_post_type($this->get_post_id()), $post_types, true)) {
            $found += 1;
        }

        $this->shortcode_found = $found;

        return $this->shortcode_found;
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

        ob_start();
        do_action('helpful_before');
        include $template;
        do_action('helpful_after');
        $content = ob_get_contents();
        ob_end_clean();

        $content = Helpers\Values::convert_tags($content, $helpful['post_id']);

        if (is_user_logged_in()) {
            // $content .= sprintf('<pre>%s</pre>', print_r($this->get_shortcode_found(), true));
        }

        return $content;
    }
}