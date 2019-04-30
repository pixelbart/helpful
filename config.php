<?php
/**
 * Helpful Simple Config File
 */

// set global variable
global $helpful;

// default tab
$helpful['default_tab'] = 'text';

// current tab
$helpful['tab'] = isset($_GET[ 'tab' ]) ? sanitize_text_field($_GET['tab']) : $helpful['default_tab'];

// default options wp_editor
$helpful['wp_editor'] = [
  'teeny' => true,
  'media_buttons' => false,
  'textarea_rows' => 5,
  'tinymce' => true,
  'quicktags' => [
    'buttons' => 'strong,em,del,ul,ol,li,close,link'
  ],
];
