<?php
/**
 * Autoloader for classes and helpers.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 1.0.0
 */

/**
 * Plugin Helpers
 *
 * @since 1.0.0
 */
require_once HELPFUL_PATH . 'core/helpers.php';

/**
 * Configurate the class autoloader
 *
 * @param string $class_name class name.
 *
 * @since 4.0.0
 */
function helpful_autoloader( $class_name ) {
	if ( false === strpos( $class_name, 'Helpful_' ) ) {
		return;
	}

	$path       = __DIR__ . '/classes/';
	$class_name = strtolower( $class_name );
	$class_name = 'class-' . $class_name;
	$class_name = str_replace( '_', '-', $class_name );

	include $path . $class_name . '.php';
}

/**
 * Fires the class autoloader
 */
spl_autoload_register( 'helpful_autoloader' );

/**
 * Setup database and default values
 *
 * @since 1.0.0
 */
Helpful_Setup::get_instance();

/**
 * Stores most fo the class instances
 *
 * @since 4.0.7
 */
Helpful_Tabs_Start::get_instance();
Helpful_Tabs_Texts::get_instance();
Helpful_Tabs_Details::get_instance();
Helpful_Tabs_Feedback::get_instance();
Helpful_Tabs_Design::get_instance();
Helpful_Tabs_System::get_instance();
Helpful_Metabox::get_instance();
Helpful_Widget::get_instance();
Helpful_Table::get_instance();
Helpful_Feedback_Admin::get_instance();
Helpful_Maintenance::get_instance();
Helpful_Notices::get_instance();
Helpful_Frontend::get_instance();
Helpful_Shortcodes::get_instance();

/**
 * Customizer
 *
 * @since 4.0.0
 */
add_action( 'customize_register', [ 'Helpful_Customizer', 'register_customizer' ] );

/**
 * Frontend Helpers
 *
 * @since 3.2.0
 */
require_once HELPFUL_PATH . 'core/values.php';


