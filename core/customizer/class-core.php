<?php
/**
 * Provides many options for the Customizer and allows customization of Helpful.
 *
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.5.0
 * @since 4.3.0
 */

namespace Helpful\Core\Customizer;

use Helpful\Core\Module;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Core {
	use Module;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'customize_register', array( & $this, 'register_customizer' ) );
	}

	/**
	 * Registers the panels and sections in the Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Current customize manager.
	 */
	public static function register_customizer( $wp_customize ) {
		$panel = array(
			'title'       => esc_html_x( 'Helpful', 'customizer panel title', 'helpful' ),
			'description' => esc_html_x( 'Here you can make small adjustments to Helpful.', 'customizer panel description', 'helpful' ),
			'priority'    => 10,
		);

		$wp_customize->add_panel( 'helpful', $panel );

		self::section_theme_and_css( $wp_customize );
		self::section_general( $wp_customize );
		self::section_buttons( $wp_customize );
		self::section_feedback( $wp_customize );
	}

	/**
	 * Options for choosing the theme and custom CSS.
	 *
	 * @param WP_Customize_Manager $wp_customize Current customize manager.
	 */
	public static function section_theme_and_css( $wp_customize ) {
		$section = array(
			'title'       => esc_html_x( 'Theme & CSS', 'customizer section title', 'helpful' ),
			'description' => esc_html_x( 'Here you can customize the design of Helpful.', 'customizer section description', 'helpful' ),
			'priority'    => 10,
			'panel'       => 'helpful',
		);

		$wp_customize->add_section( 'helpful_theme_and_css', $section );

		// ====

		$setting = array(
			'default'    => 'base',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[theme]', $setting );

		$themes  = apply_filters( 'helpful_themes', false );
		$choices = array();

		foreach ( $themes as $theme ) :
			$choices[ $theme['id'] ] = $theme['label'];
		endforeach;

		$control = array(
			'label'    => esc_html_x( 'Theme', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_theme_and_css',
			'settings' => 'helpful_customizer[theme]',
			'type'     => 'select',
			'choices'  => $choices,
		);

		$wp_customize->add_control( 'helpful_theme', $control );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[css]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Custom CSS', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_theme_and_css',
			'settings' => 'helpful_customizer[css]',
			'type'     => 'textarea',
		);

		$wp_customize->add_control( 'helpful_css', $control );
	}

	/**
	 * General Appearance Options.
	 *
	 * @param WP_Customize_Manager $wp_customize Current customize manager.
	 */
	public static function section_general( $wp_customize ) {
		$section = array(
			'title'       => esc_html_x( 'General', 'customizer section title', 'helpful' ),
			'description' => esc_html_x( 'Here you can define how Helpful should look like. Please note that the appearance can also be changed if you use a different theme as a base.', 'customizer section description', 'helpful' ),
			'priority'    => 10,
			'panel'       => 'helpful',
		);

		$wp_customize->add_section( 'helpful_general', $section );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[background_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Background Color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[background_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'background_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[heading_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Headline text color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[heading_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'heading_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[text_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Text color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[text_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'text_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[border_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Border color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[border_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'border_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[border_style]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Border style', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[border_style]',
			'type'     => 'select',
			'choices'  => array(
				'none'   => 'None',
				'dotted' => 'Dotted',
				'dashed' => 'Dashed',
				'solid'  => 'Solid',
				'double' => 'Double',
				'groove' => 'Groove',
				'ridge'  => 'Ridge',
				'inset'  => 'Inset',
				'outset' => 'Outset',
			),
		);

		$wp_customize->add_control( 'border_style', $control );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[border_width]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Border width (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[border_width]',
			'type'     => 'number',
		);

		$wp_customize->add_control( 'border_width', $control );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[border_radius]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Border radius (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[border_radius]',
			'type'     => 'number',
		);

		$wp_customize->add_control( 'border_radius', $control );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',

		);

		$wp_customize->add_setting( 'helpful_customizer[margin_top]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Spacing above (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[margin_top]',
			'type'     => 'number',

		);

		$wp_customize->add_control( 'margin_top', $control );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',

		);

		$wp_customize->add_setting( 'helpful_cusomizer[margin_bottom]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Spacing below (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[margin_bottom]',
			'type'     => 'number',

		);

		$wp_customize->add_control( 'margin_bottom', $control );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',

		);

		$wp_customize->add_setting( 'helpful_customizer[padding]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Inner spacing (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_general',
			'settings' => 'helpful_customizer[padding]',
			'type'     => 'number',
		);

		$wp_customize->add_control( 'padding', $control );
	}

	/**
	 * Options for the appearance of the buttons.
	 *
	 * @param WP_Customize_Manager $wp_customize Current customize manager.
	 */
	public static function section_buttons( $wp_customize ) {
		$section = array(
			'title'       => esc_html_x( 'Buttons', 'customizer section title', 'helpful' ),
			'description' => esc_html_x( 'Here you can determine how the buttons basically look like.', 'customizer section description', 'helpful' ),
			'priority'    => 10,
			'panel'       => 'helpful',
		);

		$wp_customize->add_section( 'helpful_buttons', $section );

		// ==== General ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',

		);

		$wp_customize->add_setting( 'helpful_customizer[button_background_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Background color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[button_background_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'button_background_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[button_text_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Text color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[button_text_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'button_text_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[button_radius]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Border radius (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[button_radius]',
			'type'     => 'number',

		);

		$wp_customize->add_control( 'button_radius', $control );

		// ==== PRO ====

		$setting = array(
			'default' => '',

		);

		$wp_customize->add_setting( 'pro_button_info', $setting );

		$control = array(
			'label'       => 'Button Pro',
			'description' => 'Here you can define how the pro button should look like.',
			'settings'    => 'pro_button_info',
			'section'     => 'helpful_buttons',
		);

		$wp_customize->add_control( new Info_Control_Element( $wp_customize, 'pro_button_info', $control ) );

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[pro_background_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Background color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[pro_background_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'pro_background_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[pro_text_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Text color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[pro_text_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'pro_text_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[pro_radius]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Border radius (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[pro_radius]',
			'type'     => 'number',
		);

		$wp_customize->add_control( 'pro_radius', $control );

		// ==== CONTRA ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'contra_button_info', $setting );

		$control = array(
			'label'       => esc_html_x( 'Button contra', 'customizer control label', 'helpful' ),
			'description' => esc_html_x( 'Here you can define how the contra button should look like.', 'customizer control description', 'helpful' ),
			'settings'    => 'contra_button_info',
			'section'     => 'helpful_buttons',
		);

		$wp_customize->add_control( new Info_Control_Element( $wp_customize, 'contra_button_info', $control ) );

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[contra_background_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Background color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[contra_background_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'contra_background_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[contra_text_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Text color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[contra_text_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'contra_text_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[contra_radius]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Border radius (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_buttons',
			'settings' => 'helpful_customizer[contra_radius]',
			'type'     => 'number',
		);

		$wp_customize->add_control( 'pro_border_radius', $control );
	}

	/**
	 * Options for the appearance of feedback form.
	 *
	 * @param WP_Customize_Manager $wp_customize Current customize manager.
	 */
	public static function section_feedback( $wp_customize ) {
		$section = array(
			'title'       => esc_html_x( 'Feedback', 'customizer section title', 'helpful' ),
			'description' => esc_html_x( 'Here you can define how the feedback form should look like.', 'customizer section description', 'helpful' ),
			'priority'    => 10,
			'panel'       => 'helpful',
		);

		$wp_customize->add_section( 'helpful_feedback', $section );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[feedback_background_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Fields background color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_feedback',
			'settings' => 'helpful_customizer[feedback_background_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'feedback_background_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[feedback_text_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Fields text color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_feedback',
			'settings' => 'helpful_customizer[feedback_text_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'feedback_text_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[feedback_border_color]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Fields border color', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_feedback',
			'settings' => 'helpful_customizer[feedback_border_color]',
		);

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'feedback_border_color', $control ) );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[feedback_border_style]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Fields border style', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_feedback',
			'settings' => 'helpful_customizer[feedback_border_style]',
			'type'     => 'select',
			'choices'  => array(
				'none'   => 'None',
				'dotted' => 'Dotted',
				'dashed' => 'Dashed',
				'solid'  => 'Solid',
				'double' => 'Double',
				'groove' => 'Groove',
				'ridge'  => 'Ridge',
				'inset'  => 'Inset',
				'outset' => 'Outset',
			),
		);

		$wp_customize->add_control( 'feedback_border_style', $control );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[feedback_border_width]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Fields border width (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_feedback',
			'settings' => 'helpful_customizer[feedback_border_width]',
			'type'     => 'number',
		);

		$wp_customize->add_control( 'feedback_border_width', $control );

		// ====

		$setting = array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		);

		$wp_customize->add_setting( 'helpful_customizer[feedback_border_radius]', $setting );

		$control = array(
			'label'    => esc_html_x( 'Fields border radius (px)', 'customizer control label', 'helpful' ),
			'section'  => 'helpful_feedback',
			'settings' => 'helpful_customizer[feedback_border_radius]',
			'type'     => 'number',
		);

		$wp_customize->add_control( 'feedback_border_radius', $control );
	}
}
