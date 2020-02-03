<?php
/**
 * Callback for admin tab.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 2.0.0
 */
global $helpful;

$settings = $helpful['wp_editor'];
$tags     = Helpful_Helper_Values::get_tags();
$tags     = '<code>' . implode( '</code>, <code>', $tags ) . '</code>';
?>

<h2><?php _ex( 'Texts', 'tab name', 'helpful' ); ?></h2>

<p><?php 
/* translators: %s available tag comma list */
$text = esc_html_x( 'Most texts can be changed here. You can also leave fields blank to not display anything at this point. Available helpers: %s', 'tab description', 'helpful' );
printf( $text, $tags );
?></p>

<form method="post" action="options.php">
	<?php settings_fields( 'helpful-texts-settings-group'); ?>
	<?php do_settings_sections('helpful-texts-settings-group'); ?>
	<?php submit_button(__('Save Changes'), 'default'); ?>
	<?php do_action('helpful-texts-settings-before'); ?>
	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Before voting', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->
		<div class="helpful-admin-panel-content">
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_heading"><?php _ex('Headline', 'option name', 'helpful'); ?></label>
				<input type="text" id="helpful_heading" name="helpful_heading" class="regular-text" value="<?php echo esc_attr(get_option('helpful_heading')); ?>"/>
				<p class="description"><?php _ex('Here you can define your own headline.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_content"><?php _ex('Content', 'option name', 'helpful'); ?></label>
				<?php wp_editor(get_option('helpful_content'), 'helpful_content', $settings); ?>
				<p class="description"><?php _ex('Here you can define your own content.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->
	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('After voting', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->
		<div class="helpful-admin-panel-content">
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_exists"><?php _ex('Already voted', 'option name', 'helpful'); ?></label>
				<?php wp_editor(get_option('helpful_exists'), 'helpful_exists', $settings); ?>
				<p class="description"><?php _ex('This text will appear if the user has already voted.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_after_pro"><?php _ex('After voting (pro)', 'option name', 'helpful'); ?></label>
				<?php wp_editor(get_option('helpful_after_pro'), 'helpful_after_pro', $settings); ?>
				<p class="description"><?php _ex('The text that is displayed, after a positive vote (shortcodes <b>without Ajax</b> are also possible!)', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_after_contra"><?php _ex('After voting (contra)', 'option name', 'helpful'); ?></label>
				<?php wp_editor(get_option('helpful_after_contra'), 'helpful_after_contra', $settings); ?>
				<p class="description"><?php _ex('The text that is displayed, after a negative vote (shortcodes <b>without Ajax</b> are also possible!)', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->
	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Answer buttons', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->
		<div class="helpful-admin-panel-content">
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_pro"><?php _ex('Button (pro)', 'option name', 'helpful'); ?></label>
				<input type="text" id="helpful_pro" name="helpful_pro" class="regular-text" value="<?php echo esc_attr(get_option('helpful_pro')); ?>"/>
				<p class="description"><?php _ex('Here you can define your own text for the pro button.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_contra"><?php _ex('Button (contra)', 'option name', 'helpful'); ?></label>
				<input type="text" id="helpful_contra" name="helpful_contra" class="regular-text" value="<?php echo esc_attr(get_option('helpful_contra')); ?>"/>
				<p class="description"><?php _ex('Here you can define your own text for the contra button.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->
	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Admin columns', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->
		<div class="helpful-admin-panel-content">
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_contra"><?php _ex('Column (pro)', 'option name', 'helpful'); ?></label>
				<input type="text" id="helpful_column_pro" name="helpful_column_pro" class="regular-text" value="<?php echo esc_attr(get_option('helpful_column_pro')); ?>"/>
				<p class="description"><?php _ex('Here you can define your own text for the pro column in the post edit list.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_contra"><?php _ex('Column (contra)', 'option name', 'helpful'); ?></label>
				<input type="text" id="helpful_column_contra" name="helpful_column_contra" class="regular-text" value="<?php echo esc_attr(get_option('helpful_column_contra')); ?>"/>
				<p class="description"><?php _ex('Here you can define your own text for the contra column in the post edit list.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->
	<?php do_action('helpful-texts-settings-after'); ?>
	<?php submit_button(__('Save Changes'), 'default'); ?>
</form>