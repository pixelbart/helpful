<?php
/**
 * Plugin Name: Helpful
 * Description: Add a fancy feedback form under your posts or post-types and ask your visitors a question. Give them the abbility to vote with yes or no.
 * Version: 4.4.23
 * Author: Pixelbart
 * Author URI: https://pixelbart.de
 * Text Domain: helpful
 * License: MIT License
 * License URI: https://opensource.org/licenses/MIT
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HELPFUL_FILE', __FILE__ );
define( 'HELPFUL_PATH', plugin_dir_path( __FILE__ ) );
define( 'HELPFUL_PHP_MIN', '5.6.20' );

/* Load Helpful after plugins are loaded */
add_action( 'plugins_loaded', [ 'HelpfulPlugin', 'get_instance' ] );

if ( ! class_exists( 'HelpfulPlugin' ) ) {

	class HelpfulPlugin
	{
		/**
		 * Class Prefix.
		 *
		 * @var string
		 */
		private $prefix = 'Helpful\\';

		/**
		 * Saves an instance of the class.
		 *
		 * @var HelpfulPlugin
		 */
		private static $instance;
	
		/**
		 * Creates an instance of the class if it does not yet exist.
		 *
		 * @return HelpfulPlugin
		 */
		public static function get_instance()
		{
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
	
			return self::$instance;
		}
	
		/**
		 * Class constructor.
		 *
		 * @return void
		 */
		private function __construct()
		{
			/* Starts the autoloader. */
			spl_autoload_register( [ $this, 'autoload' ] );
			
			/* Initializes the classes and functions. */
			$this->init();
		}
	
		/**
		 * Includes all classes.
		 *
		 * @param string $class_name
		 *
		 * @return void
		 */
		public function autoload( $class_name )
		{
			/* Stores the prefix. */
			$prefix = $this->prefix;
	
			$len = strlen( $prefix );
	
			if ( 0 !== strncmp( $prefix, $class_name, $len ) ) {
				return;
			}
	
			$relative_class = substr( $class_name, $len );
	
			$path = explode( '\\', strtolower( str_replace( '_', '-', $relative_class ) ) );
			$file = array_pop( $path );
			$file = HELPFUL_PATH . implode( '/', $path ) . '/class-' . $file . '.php';
	
			/* Includes the file if the file exists. */
			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	
		/**
		 * Initializes the classes.
		 *
		 * @return void
		 */
		public function init()
		{
			include_once HELPFUL_PATH . 'core/functions/helpers.php';

			Helpful\Core\Modules\Core::get_instance();
			Helpful\Core\Modules\Maintenance::get_instance();
			Helpful\Core\Modules\debug::get_instance();
			Helpful\Core\Modules\Admin::get_instance();

			Helpful\Core\Modules\Feedback_Admin::get_instance();
			Helpful\Core\Modules\Metabox::get_instance();
			Helpful\Core\Modules\Widget::get_instance();
			Helpful\Core\Modules\Customizer::get_instance();
			Helpful\Core\Modules\Frontend::get_instance();

			Helpful\Core\Tabs\Start::get_instance();
			Helpful\Core\Tabs\Details::get_instance();
			Helpful\Core\Tabs\Texts::get_instance();
			Helpful\Core\Tabs\Feedback::get_instance();
			Helpful\Core\Tabs\Design::get_instance();
			Helpful\Core\Tabs\System::get_instance();
			Helpful\Core\Tabs\Log::get_instance();

			include_once HELPFUL_PATH . 'core/functions/values.php';
		}
	}
}
