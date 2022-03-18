<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.5.0
 * @since 4.5.0
 */
namespace Helpful\Core\Customizer;

use WP_Customize_Control;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_Customize_Control')) {
	return;
}

class Info_Control_Element extends WP_Customize_Control
{
    /**
     * @var string
     */
    public $type = 'info';

    /**
     * @return void
     */
    public function render_content()
    {
        printf('<span class="customize-control-title">%s</span>', esc_html($this->label));
        printf('<p>%s</p>', wp_kses_post($this->description));
    }
}