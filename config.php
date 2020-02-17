<?php
/**
 * Config for storing current admin tab, wp_editor settings,
 * credits text and link.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $helpful;

// default tab
$helpful['default_tab'] = 'text';

// current tab
$helpful['tab'] = $helpful['default_tab'];

if ( isset( $_GET['tab'] ) ) {
    $helpful['tab'] = sanitize_text_field(wp_unslash($_GET['tab']));
}

// default options wp_editor
$helpful['wp_editor'] = [
    'teeny'         => true,
    'media_buttons' => false,
    'textarea_rows' => 5,
    'tinymce'       => false,
    'quicktags'     => [
        'buttons' => 'strong,em,del,ul,ol,li,close,link'
    ],
];

// credits
$helpful['credits'] = [
    'url'  => 'https://helpful-plugin.info',
    'name' => 'Helpful',
];
