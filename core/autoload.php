<?php
// Plugin Helpers
include_once( HELPFUL_PATH . "core/helpers.php" );

/** 
 * Class Autoloader
 * @since 3.2.0
 */
spl_autoload_register('helpfulAutoloader');
function helpfulAutoloader($className) {
	if( false === strpos( $className, 'Helpful_' ) ) {
		return;
	}
	
	$path = __DIR__ . '/classes/';
	$className = strtolower($className);
	$className = 'class-' . $className;
	$className = str_replace('_', '-', $className);
	require $path . $className . '.php';	
}

/**
 * Setup database and default values
 * @since 3.2.0
 */
new Helpful_Setup();

/** 
 * Admin Tabs
 * @since 3.2.0
 */
add_action( 'plugins_loaded', function () {
	new Helpful_Tabs_Start();
	new Helpful_Tabs_Texts();
	new Helpful_Tabs_Details();
	new Helpful_Tabs_Feedback();
	new Helpful_Tabs_System();
} );


/**
 * Metabox
 * @since 3.2.0
 */
add_action( 'plugins_loaded', function () {
	Helpful_Metabox::get_instance();
} );

/**
 * Dashboard Widget
 * @since 3.2.0
 */
add_action( 'plugins_loaded', function () {
	Helpful_Widget::get_instance();
} );

/**
 * Helpful Admin Columns
 * @since 3.2.0
 */
add_action( 'plugins_loaded', function() {
	Helpful_Table::get_instance();
} );

/**
 * Feedback Admin Tables
 * @since 3.2.0
 */
add_action( 'plugins_loaded', function () {
	Helpful_Feedback_Admin::get_instance();
} );

/**
 * Maintenance
 * @since 3.2.0
 */
add_action( 'plugins_loaded', function () {
	Helpful_Maintenance::get_instance();
	Helpful_Notices::get_instance();
} );

/**
 * Frontend
 * @since 3.2.0
 */
add_action( 'plugins_loaded', function () {
	Helpful_Frontend::get_instance();
} );

/**
 * Shortcodes
 * @since 3.2.0
 */
add_action( 'plugins_loaded', function () {
	Helpful_Shortcodes::get_instance();
} );

/**
 * Customizer
 * @since 3.2.0
 */
add_action( 'customize_register', [ 'Helpful_Customizer', 'registerCustomizer'] );

/** 
 * Frontend Helpers
 * @since 3.2.0
 */
include_once( HELPFUL_PATH . "core/values.php" );