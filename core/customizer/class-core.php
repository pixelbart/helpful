<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.5.0
 * @since 4.3.0
 */
namespace Helpful\Core\Customizer;

use Helpful\Core\Module;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Core
{
    use Module;

    /**
     * @return void
     */
    public function __construct()
    {
        add_action('customize_register', [ & $this, 'register_customizer']);
    }

    /**
     * @version 4.5.0
     * 
     * @param WP_Customize_Manager $wp_customize
     * 
     * @return void
     */
    public static function register_customizer($wp_customize)
    {
        $panel = [
            'title' => esc_html_x('Helpful', 'customizer panel title', 'helpful'),
            'description' => esc_html_x('Here you can make small adjustments to Helpful.', 'customizer panel description', 'helpful'),
            'priority' => 10,
        ];

        $wp_customize->add_panel('helpful', $panel);

        self::section_theme_and_css($wp_customize);
        self::section_general($wp_customize);
        self::section_buttons($wp_customize);
        self::section_feedback($wp_customize);
    }

    /**
     * @version 4.5.0
     * 
     * @param WP_Customize_Manager $wp_customize
     * 
     * @return void
     */
    public static function section_theme_and_css($wp_customize)
    {
        $section = [
            'title' => esc_html_x('Theme & CSS', 'customizer section title', 'helpful'),
            'description' => esc_html_x('Here you can customize the design of Helpful.', 'customizer section description', 'helpful'),
            'priority' => 10,
            'panel' => 'helpful',
        ];

        $wp_customize->add_section('helpful_theme_and_css', $section);

        // ====

        $wp_customize->add_setting('helpful_customizer[theme]', [
            'default' => 'base',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $themes = apply_filters('helpful_themes', false);
        $choices = [];

        foreach ($themes as $theme):
            $choices[$theme['id']] = $theme['label'];
        endforeach;

        $wp_customize->add_control('helpful_theme', [
            'label' => esc_html_x('Theme', 'customizer control label', 'helpful'),
            'section' => 'helpful_theme_and_css',
            'settings' => 'helpful_customizer[theme]',
            'type' => 'select',
            'choices' => $choices,
        ]);

        // ====

        $wp_customize->add_setting('helpful_customizer[css]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('helpful_css', [
            'label' => esc_html_x('Custom CSS', 'customizer control label', 'helpful'),
            'section' => 'helpful_theme_and_css',
            'settings' => 'helpful_customizer[css]',
            'type' => 'textarea',
        ]);
    }

    /**
     * @version 4.5.0
     * 
     * @param WP_Customize_Manager $wp_customize
     * 
     * @return void
     */
    public static function section_general($wp_customize)
    {
        $section = [
            'title' => esc_html_x('General', 'customizer section title', 'helpful'),
            'description' => esc_html_x('Here you can define how Helpful should look like. Please note that the appearance can also be changed if you use a different theme as a base.', 'customizer section description', 'helpful'),
            'priority' => 10,
            'panel' => 'helpful',
        ];

        $wp_customize->add_section('helpful_general', $section);

        // ====

        $wp_customize->add_setting('helpful_customizer[background_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'background_color', [
            'label' => esc_html_x('Background Color', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[background_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[heading_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'heading_color', [
            'label' => esc_html_x('Headline text color', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[heading_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[text_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'text_color', [
            'label' => esc_html_x('Text color', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[text_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[border_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'border_color', [
            'label' => esc_html_x('Border color', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[border_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[border_style]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('border_style', [
            'label' => esc_html_x('Border style', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[border_style]',
            'type' => 'select',
            'choices' => [
                'none' => 'None',
                'dotted' => 'Dotted',
                'dashed' => 'Dashed',
                'solid' => 'Solid',
                'double' => 'Double',
                'groove' => 'Groove',
                'ridge' => 'Ridge',
                'inset' => 'Inset',
                'outset' => 'Outset',
            ],
        ]);

        // ====

        $wp_customize->add_setting('helpful_customizer[border_width]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('border_width', [
            'label' => esc_html_x('Border width (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[border_width]',
            'type' => 'number',
        ]);

        // ====

        $wp_customize->add_setting('helpful_customizer[border_radius]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('border_radius', [
            'label' => esc_html_x('Border radius (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[border_radius]',
            'type' => 'number',
        ]);

        // ====

        $wp_customize->add_setting('helpful_customizer[margin_top]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('margin_top', [
            'label' => esc_html_x('Spacing above (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[margin_top]',
            'type' => 'number',
        ]);

        // ====

        $wp_customize->add_setting('helpful_customizer[margin_bottom]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('margin_bottom', [
            'label' => esc_html_x('Spacing below (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[margin_bottom]',
            'type' => 'number',
        ]);

        // ====

        $wp_customize->add_setting('helpful_customizer[padding]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('padding', [
            'label' => esc_html_x('Inner spacing (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_general',
            'settings' => 'helpful_customizer[padding]',
            'type' => 'number',
        ]);
    }

    /**
     * @version 4.5.0
     * 
     * @param WP_Customize_Manager $wp_customize
     * 
     * @return void
     */
    public static function section_buttons($wp_customize)
    {
        $section = [
            'title' => esc_html_x('Buttons', 'customizer section title', 'helpful'),
            'description' => esc_html_x('Here you can determine how the buttons basically look like.', 'customizer section description', 'helpful'),
            'priority' => 10,
            'panel' => 'helpful',
        ];

        $wp_customize->add_section('helpful_buttons', $section);

        // ==== General ====

        $wp_customize->add_setting('helpful_customizer[button_background_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'button_background_color', [
            'label' => esc_html_x('Background color', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[button_background_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[button_text_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'button_text_color', [
            'label' => esc_html_x('Text color', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[button_text_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[button_radius]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('button_radius', [
            'label' => esc_html_x('Border radius (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[button_radius]',
            'type' => 'number',
        ]);

        // ==== PRO ====

        $wp_customize->add_setting('pro_button_info', [
            'default' => '',
        ]);

        $wp_customize->add_control(new Info_Control_Element($wp_customize, 'pro_button_info', array(
            'label' => 'Button Pro',
            'description' => 'Here you can define how the pro button should look like.',
            'settings' => 'pro_button_info',
            'section' => 'helpful_buttons',
        )));

        $wp_customize->add_setting('helpful_customizer[pro_background_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'pro_background_color', [
            'label' => esc_html_x('Background color', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[pro_background_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[pro_text_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'pro_text_color', [
            'label' => esc_html_x('Text color', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[pro_text_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[pro_radius]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('pro_radius', [
            'label' => esc_html_x('Border radius (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[pro_radius]',
            'type' => 'number',
        ]);

        // ==== CONTRA ====

        $wp_customize->add_setting('contra_button_info', [
            'default' => '',
        ]);

        $wp_customize->add_control(new Info_Control_Element($wp_customize, 'contra_button_info', array(
            'label' => esc_html_x('Button contra', 'customizer control label', 'helpful'),
            'description' => esc_html_x('Here you can define how the contra button should look like.', 'customizer control description', 'helpful'),
            'settings' => 'contra_button_info',
            'section' => 'helpful_buttons',
        )));

        $wp_customize->add_setting('helpful_customizer[contra_background_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'contra_background_color', [
            'label' => esc_html_x('Background color', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[contra_background_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[contra_text_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'contra_text_color', [
            'label' => esc_html_x('Text color', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[contra_text_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[contra_radius]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('pro_border_radius', [
            'label' => esc_html_x('Border radius (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_buttons',
            'settings' => 'helpful_customizer[contra_radius]',
            'type' => 'number',
        ]);
    }

    /**
     * @version 4.5.0
     * 
     * @param WP_Customize_Manager $wp_customize
     * 
     * @return void
     */
    public static function section_feedback($wp_customize)
    {
        $section = [
            'title' => esc_html_x('Feedback', 'customizer section title', 'helpful'),
            'description' => esc_html_x('Here you can define how the feedback form should look like.', 'customizer section description', 'helpful'),
            'priority' => 10,
            'panel' => 'helpful',
        ];

        $wp_customize->add_section('helpful_feedback', $section);

        // ====

        $wp_customize->add_setting('helpful_customizer[feedback_background_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'feedback_background_color', [
            'label' => esc_html_x('Fields background color', 'customizer control label', 'helpful'),
            'section' => 'helpful_feedback',
            'settings' => 'helpful_customizer[feedback_background_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[feedback_text_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'feedback_text_color', [
            'label' => esc_html_x('Fields text color', 'customizer control label', 'helpful'),
            'section' => 'helpful_feedback',
            'settings' => 'helpful_customizer[feedback_text_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[feedback_border_color]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'feedback_border_color', [
            'label' => esc_html_x('Fields border color', 'customizer control label', 'helpful'),
            'section' => 'helpful_feedback',
            'settings' => 'helpful_customizer[feedback_border_color]',
        ]));

        // ====

        $wp_customize->add_setting('helpful_customizer[feedback_border_style]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('feedback_border_style', [
            'label' => esc_html_x('Fields border style', 'customizer control label', 'helpful'),
            'section' => 'helpful_feedback',
            'settings' => 'helpful_customizer[feedback_border_style]',
            'type' => 'select',
            'choices' => [
                'none' => 'None',
                'dotted' => 'Dotted',
                'dashed' => 'Dashed',
                'solid' => 'Solid',
                'double' => 'Double',
                'groove' => 'Groove',
                'ridge' => 'Ridge',
                'inset' => 'Inset',
                'outset' => 'Outset',
            ],
        ]);

        // ====

        $wp_customize->add_setting('helpful_customizer[feedback_border_width]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('feedback_border_width', [
            'label' => esc_html_x('Fields border width (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_feedback',
            'settings' => 'helpful_customizer[feedback_border_width]',
            'type' => 'number',
        ]);

        // ====

        $wp_customize->add_setting('helpful_customizer[feedback_border_radius]', [
            'default' => '',
            'type' => 'option',
            'capability' => 'edit_theme_options',
        ]);

        $wp_customize->add_control('feedback_border_radius', [
            'label' => esc_html_x('Fields border radius (px)', 'customizer control label', 'helpful'),
            'section' => 'helpful_feedback',
            'settings' => 'helpful_customizer[feedback_border_radius]',
            'type' => 'number',
        ]);
    }
}
