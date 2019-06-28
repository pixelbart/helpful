<?php
class Helpful_Customizer {

  /**
   * Register custom customizer section/panel
   * @param object $wp_customize
   * @return void
   */
  public static function registerCustomizer( $wp_customize ) {
    $wp_customize->add_panel( 'helpful', [
      'title' => esc_html_x('Helpful', 'customizer panel title', 'helpful'),
      'description' => esc_html_x('Here you can make small adjustments to Helpful.', 'customizer panel description', 'helpful'),
      'priority' => 10,
    ] );

    /**
     * Design Section
     */

    $wp_customize->add_section( 'helpful_design', [
      'title' => esc_html_x('Design', 'customizer section title', 'helpful'),
      'description' => esc_html_x('Here you can customize the design of Helpful.', 'customizer section description', 'helpful'),
      'priority' => 10,
      'panel' => 'helpful',
    ] );

    /* Helpful Themes */
    $themes = apply_filters('helpful_themes', false);

    $choices = [];
    foreach( $themes as $theme ) {
      $choices[$theme['id']] = $theme['label'];
    }

    $wp_customize->add_setting( 'helpful_theme', [
      'default' => 'base',
      'type' => 'option',
    ] );

    $wp_customize->add_control( 'helpful_theme', [        
      'label' => esc_html_x('Theme', 'customizer control label', 'helpful'),
      'section' => 'helpful_design',
      'settings' => 'helpful_theme',
      'type' => 'select',
      'choices' => $choices,
    ] );

    /* Helpful CSS */
    $wp_customize->add_setting( 'helpful_css', [
      'default' => '',
      'type' => 'option',
    ] );

    $wp_customize->add_control( 'helpful_css', [        
      'label' => esc_html_x('Custom CSS', 'customizer control label', 'helpful'),
      'section' => 'helpful_design',
      'settings' => 'helpful_css',
      'type' => 'textarea',
    ] );

    /**
     * Details Section
     */

    $wp_customize->add_section( 'helpful_details', [
      'title' => esc_html_x('Details', 'customizer section title', 'helpful'),
      'description' => esc_html_x('Here you can adjust the details of Helpful.', 'customizer section description', 'helpful'),
      'priority' => 10,
      'panel' => 'helpful',
    ] );

    /* Show Counters */
    $wp_customize->add_setting( 'helpful_count_hide', [
      'type' => 'option',
    ] );

    $wp_customize->add_control( 'helpful_count_hide', [        
      'label' => esc_html_x('Hide counter', 'customizer control label', 'helpful'),
      'section' => 'helpful_details',
      'settings' => 'helpful_count_hide',
      'type' => 'checkbox',
    ] );

    /* Credits */
    $wp_customize->add_setting( 'helpful_credits', [
      'type' => 'option',
    ] );

    $wp_customize->add_control( 'helpful_credits', [        
      'label' => esc_html_x('Credits', 'customizer control label', 'helpful'),
      'section' => 'helpful_details',
      'settings' => 'helpful_credits',
      'type' => 'checkbox',
    ] );
  }
}