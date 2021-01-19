<?php
/**
 * ...
 *
 * @package Helpful\Core\Modules
 * @author  Pixelbart <me@pixelbart.de>
 * @version 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Core
{
	/**
	 * Instance
	 *
	 * @var Core
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Core
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		Helper::set_timezone();

		add_action( 'admin_init', [ &$this, 'setup_helpful_table' ] );
		add_action( 'admin_init', [ &$this, 'setup_feedback_table' ] );

		// Causes problems and was therefore commented out.
		// add_action( 'init', [ &$this, 'setup_defaults' ] );

		add_action( 'activated_plugin', [ &$this, 'load_first' ] );

		add_filter( 'plugin_row_meta', [ &$this, 'plugin_row_meta' ], 10, 2 );
		add_filter( 'helpful_debug_fields', [ &$this, 'debug_fields' ] );

		add_filter( 'helpful_current_tab', [ &$this, 'current_tab' ] );
		add_filter( 'helpful_editor_settings', [ &$this, 'editor_settings' ] );

		/**
		 * Load Elementor Widgets
		 *
		 * @since 4.1.2
		 */
		add_action( 'elementor/widgets/widgets_registered', [ &$this, 'elementor_widgets' ] );
		add_action( 'elementor/controls/controls_registered', [ &$this, 'elementor_controls' ] );
		add_action( 'elementor/elements/categories_registered', [ &$this, 'elementor_categories' ] );
	}

	/**
	 * Set default options.
	 *
	 * @return bool
	 */
	public function setup_defaults()
	{
		$status = intval( get_option( 'helpful_defaults' ) );
	
		if ( 1 === $status ) {
			return false;
		}

		$this->set_defaults( true );

		update_option( 'helpful_defaults', 1 );

		return true;
	}

	/**
	 * Create database table for helpful
	 *
	 * @global $wpdb
	 *
	 * @return bool
	 */
	public function setup_helpful_table()
	{
		// Updates database tables.
		Helpers\Database::update_tables();

		if ( false === get_transient( 'setup_helpful_table' ) ) {
			Helpers\Database::setup_helpful_table();
			set_transient( 'setup_helpful_table', 1, WEEK_IN_SECONDS );
		}
	}

	/**
	 * Create database table for feedback
	 *
	 * @global $wpdb
	 *
	 * @return bool
	 */
	public function setup_feedback_table()
	{
		if ( false === get_transient( 'setup_feedback_table' ) ) {
			Helpers\Database::setup_feedback_table();
			set_transient( 'setup_feedback_table', 1, WEEK_IN_SECONDS );
		}
	}

	/**
	 * Default values for settings
	 *
	 * @param bool $status set true for filling defaults.
	 *
	 * @return bool
	 */
	public function set_defaults( $status = false )
	{
		if ( false === $status ) {
			return false;
		}

		ob_start();
		require_once HELPFUL_PATH . 'templates/feedback-email.php';
		$feedback_email_content = ob_get_contents();
		ob_end_clean();

		$options = [
			'helpful_heading'                => _x( 'Was this post helpful?', 'default headline', 'helpful' ),
			'helpful_content'                => _x( 'Let us know if you liked the post. That’s the only way we can improve.', 'default description', 'helpful' ),
			'helpful_exists'                 => _x( 'You have already voted for this post.', 'already voted', 'helpful' ),
			'helpful_success'                => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
			'helpful_error'                  => _x( 'Sorry, an error has occurred.', 'error after voting', 'helpful' ),
			'helpful_pro'                    => _x( 'Yes', 'text pro button', 'helpful' ),
			'helpful_contra'                 => _x( 'No', 'text contra button', 'helpful' ),
			'helpful_column_pro'             => _x( 'Pro', 'column name', 'helpful' ),
			'helpful_column_contra'          => _x( 'Contra', 'column name', 'helpful' ),
			'helpful_column_feedback'        => _x( 'Feedback', 'column name', 'helpful' ),
			'helpful_after_pro'              => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
			'helpful_after_contra'           => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
			'helpful_after_fallback'         => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
			'helpful_feedback_label_message' => _x( 'Message', 'label for feedback form field', 'helpful' ),
			'helpful_feedback_label_name'    => _x( 'Name', 'label for feedback form field', 'helpful' ),
			'helpful_feedback_label_email'   => _x( 'Email', 'label for feedback form field', 'helpful' ),
			'helpful_feedback_label_submit'  => _x( 'Send Feedback', 'label for feedback form field', 'helpful' ),
			'helpful_feedback_label_cancel'  => _x( 'Cancel', 'label for feedback form field', 'helpful' ),
			'helpful_post_types'             => [ 'post' ],
			'helpful_count_hide'             => false,
			'helpful_credits'                => true,
			'helpful_uninstall'              => false,
			'helpful_widget'                 => true,
			'helpful_widget_amount'          => 3,
			'helpful_widget_pro'             => true,
			'helpful_widget_contra'          => true,
			'helpful_widget_pro_recent'      => true,
			'helpful_widget_contra_recent'   => true,
			'helpful_feedback_subject'       => _x( 'There\'s new feedback for you.', 'feedback email subject', 'helpful' ),
			'helpful_feedback_receivers'     => get_option( 'admin_email' ),
			'helpful_feedback_email_content' => $feedback_email_content,
		];

		$options = apply_filters( 'helpful_options', $options );

		foreach ( $options as $slug => $value ) :
			if ( ! get_option( $slug ) ) {
				update_option( $slug, $value );
			}
		endforeach;

		return true;
	}

	/**
	 * Loads helpful first
	 *
	 * @return void
	 */
	public function load_first()
	{
		if ( ! get_option( 'helpful_plugin_first' ) ) {
			return;
		}

		$path = str_replace( WP_PLUGIN_DIR . '/', '', HELPFUL_FILE );
		if ( $plugins = get_option( 'active_plugins' ) ) {
			if ( $key = array_search( $path, $plugins ) ) {
				array_splice( $plugins, $key, 1 );
				array_unshift( $plugins, $path );
				update_option( 'active_plugins', $plugins );
			}
		}
	}

	/**
	 * Method for adding and filtering plugin row meta of Helpful.
	 *
	 * @param array  $links default links.
	 * @param string $file file string.
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file )
	{

		if ( false !== strpos( $file, basename( HELPFUL_FILE ) ) ) {
			$links['documentation'] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				'https://helpful-plugin.info/documentation/',
				esc_html_x( 'Documentation', 'plugin row meta', 'helpful' )
			);

			$links['donate'] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				'https://www.buymeacoffee.com/pixelbart',
				esc_html_x( 'Donate', 'plugin row meta', 'helpful' )
			);

			$links['support'] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				'https://wordpress.org/support/plugin/helpful/',
				esc_html_x( 'Support', 'plugin row meta', 'helpful' )
			);

			$links = apply_filters( 'helpful_plugin_row_meta', $links );
		}

		return $links;
	}

	/**
	 * Register custom elementor widgets
	 *
	 * @return void
	 */
	public function elementor_widgets()
	{
		$elementor = \Elementor\Plugin::instance();

		/**
		 * Register Helpful Widget
		 *
		 * @see Helpful_Elementor_Widget
		 */
		$elementor->widgets_manager->register_widget_type( new Elementor_Widget() );
	}

	/**
	 * Register custom elementor controls
	 *
	 * @return void
	 */
	public function elementor_controls()
	{
		// nothing
	}

	/**
	 * Register categories
	 *
	 * @param object $elementor elementor object.
	 *
	 * @return void
	 */
	public function elementor_categories( $elementor )
	{
		// nothing
	}

	/**
	 * Fields for Debug Informations.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function debug_fields( $fields )
	{		
		$fields['datatables'] = [
			'label' => esc_html_x( 'DataTables version', 'debug field label', 'helpful' ),
			'value' => '1.10.20',
		];

		return $fields;
	}

	/**
	 * Returns the current tab.
	 *
	 * @return string
	 */
	public function current_tab()
	{
		$tab = 'start';

		if ( isset( $_GET['tab'] ) && '' !== trim( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		}

		return $tab;
	}

	/**
	 * Returns the WP_Editor settings of Helpful.
	 * 
	 * @return array
	 */
	public function editor_settings()
	{
		return [	
			'teeny'         => true,
			'media_buttons' => false,
			'textarea_rows' => 5,
			'tinymce'       => false,
			'quicktags'     => [
				'buttons' => 'strong,em,del,ul,ol,li,close,link'
			],
		];		
	}
}