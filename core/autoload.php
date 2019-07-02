<?php
/**
 * Autoloader for classes and helpers.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */

/**
 * Plugin Helpers
 *
 * @since 1.0.0
 */
require_once HELPFUL_PATH . "core/helpers.php";

/**
 * Class Autoloader
 *
 * @since 4.0.0
 */
spl_autoload_register('Helpful_autoloader');
function Helpful_autoloader($className) 
{
    if (false === strpos($className, 'Helpful_')) {
        return;
    }

    $path = __DIR__ . '/classes/';
    $className = strtolower($className);
    $className = 'class-' . $className;
    $className = str_replace('_', '-', $className);

    include $path . $className . '.php';
}

/**
 * Setup database and default values
 *
 * @since 1.0.0
 */
Helpful_Setup::getInstance();

/**
 * Store and fire all instances
 *
 * @since 4.0.7
 */
add_action('plugins_loaded', 'Helpful_instances');
function Helpful_instances() 
{
    Helpful_Tabs_Start::getInstance();
    Helpful_Tabs_Texts::getInstance();
    Helpful_Tabs_Details::getInstance();
    Helpful_Tabs_Feedback::getInstance();
    Helpful_Tabs_Design::getInstance();
    Helpful_Tabs_System::getInstance();
    Helpful_Metabox::getInstance();
    Helpful_Widget::getInstance();
    Helpful_Table::getInstance();
    Helpful_Feedback_Admin::getInstance();
    Helpful_Maintenance::getInstance();
    Helpful_Notices::getInstance();
    Helpful_Frontend::getInstance();
    Helpful_Shortcodes::getInstance();
}

/**
 * Customizer
 *
 * @since 4.0.0
 */
add_action('customize_register', [ 'Helpful_Customizer', 'registerCustomizer' ]);

/**
 * Frontend Helpers
 *
 * @since 3.2.0
 */
require_once HELPFUL_PATH . "core/values.php";