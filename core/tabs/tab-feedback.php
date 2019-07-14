<?php
/**
 * Callback for admin tab.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 1.0.0
 */
global $helpful;
$settings = $helpful['wp_editor'];
?>

<h2><?php _ex( 'Feedback', 'tab name', 'helpful' ); ?></h2>

<p><?php _ex( 'Here you can activate a feedback form. The feedback form will be displayed after voting and can be configured here. Please note that the form is not spam protected.', 'tab description', 'helpful' ); ?></p>

<form method="post" action="options.php">

<?php settings_fields( 'helpful-feedback-settings-group' ); ?>
<?php do_settings_sections( 'helpful-feedback-settings-group' ); ?>
<?php submit_button( __( 'Save Changes' ), 'default' ); ?>
<?php do_action( 'helpful-feedback-settings-before' ); ?>

<div class="helpful-admin-panel">
	<button type="button" class="helpful-admin-panel-header">
		<span class="title"><?php _ex( 'Form', 'admin panel title', 'helpful' ); ?></span>
		<span class="icon"></span>
	</button><!-- .helpful-admin-panel-header -->
<div class="helpful-admin-panel-content">

<div class="helpful-admin-group helpful-margin-bottom">
	<label>
		<?php $value = get_option( 'helpful_feedback_after_pro' ); ?>
		<input id="helpful_feedback_after_pro" type="checkbox" name="helpful_feedback_after_pro" <?php checked( 'on', $value ); ?> /> 
		<?php _ex( 'Show form after positive vote', 'label', 'helpful' ); ?>
	</label>
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group helpful-margin-bottom">
	<label>
		<?php $value = get_option( 'helpful_feedback_after_contra' ); ?>
		<input id="helpful_feedback_after_contra" type="checkbox" name="helpful_feedback_after_contra" <?php checked( 'on', $value ); ?> /> 
		<?php _ex( 'Show form after negative vote', 'label', 'helpful' ); ?>
	</label>
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group helpful-margin-bottom">
	<label>
		<?php $value = get_option( 'helpful_feedback_name' ); ?>
		<input id="helpful_feedback_name" type="checkbox" name="helpful_feedback_name" <?php checked( 'on', $value); ?> /> 
		<?php _ex( 'Show name field below the form', 'label', 'helpful' ); ?>
	</label>
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group helpful-margin-bottom">
	<label>
		<?php $value = get_option( 'helpful_feedback_email' ); ?>
		<input id="helpful_feedback_email" type="checkbox" name="helpful_feedback_email" <?php checked( 'on', $value ); ?> /> 
		<?php _ex( 'Show email field below the form', 'label', 'helpful' ); ?>
	</label>
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group">
	<label>
		<?php $value = get_option( 'helpful_feedback_cancel' ); ?>
		<input id="helpful_feedback_cancel" type="checkbox" name="helpful_feedback_cancel" <?php checked( 'on', $value ); ?> /> 
		<?php _ex( 'Show Cancel button', 'label', 'helpful' ); ?>
	</label>
</div><!-- .helpful-admin-group -->

</div><!-- .helpful-admin-panel-content -->
</div><!-- .helpful-admin-panel -->

<div class="helpful-admin-panel">
	<button type="button" class="helpful-admin-panel-header">
		<span class="title"><?php _ex( 'Messages', 'admin panel title', 'helpful' ); ?></span>
		<span class="icon"></span>
	</button><!-- .helpful-admin-panel-header -->
<div class="helpful-admin-panel-content">

<p class="description"><?php _ex( 'Here you can change the texts that are displayed directly in front of the feedback form. Briefly explain to your visitors why they should give feedback.', 'admin panel description', 'helpful' ); ?></p>

<div class="helpful-admin-group helpful-margin-bottom">
	<label class="helpful-block" for="helpful_feedback_message_pro"><?php _ex( 'Message (pro)', 'option name', 'helpful' ); ?></label>
	<?php wp_editor( get_option( 'helpful_feedback_message_pro' ), 'helpful_feedback_message_pro', $settings ); ?>
	<p class="description"><?php _ex( 'This message is displayed if the user has voted positively.', 'option info', 'helpful' ); ?></p>
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group">
	<label class="helpful-block" for="helpful_feedback_message_contra"><?php _ex( 'Message (contra)', 'option name', 'helpful' ); ?></label>
	<?php wp_editor( get_option( 'helpful_feedback_message_contra' ), 'helpful_feedback_message_contra', $settings ); ?>
	<p class="description"><?php _ex( 'This message is displayed if the user has voted negatively.', 'option info', 'helpful' ); ?></p>
</div><!-- .helpful-admin-group -->

</div><!-- .helpful-admin-panel-content -->
</div><!-- .helpful-admin-panel -->

<div class="helpful-admin-panel">
	<button type="button" class="helpful-admin-panel-header">
		<span class="title"><?php _ex( 'Labels', 'admin panel title', 'helpful' ); ?></span>
		<span class="icon"></span>
	</button><!-- .helpful-admin-panel-header -->
<div class="helpful-admin-panel-content">

<p class="description"><?php _ex( 'Here you can define the labels for the form fields. The text for the button can also be changed.', 'admin panel description', 'helpful' ); ?></p>

<div class="helpful-admin-group helpful-margin-bottom">
	<label class="helpful-block" for="helpful_feedback_label_message"><?php _ex( 'Message', 'option name', 'helpful' ); ?></label>
	<?php $value = get_option( 'helpful_feedback_label_message', _x( 'Message', 'label for feedback form field', 'helpful' ) ); ?>
	<input class="regular-text" type="text" name="helpful_feedback_label_message" value="<?php echo $value; ?>">
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group helpful-margin-bottom">
	<label class="helpful-block" for="helpful_feedback_label_name"><?php _ex( 'Name', 'option name', 'helpful' ); ?></label>
	<?php $value = get_option( 'helpful_feedback_label_name', _x( 'Name', 'label for feedback form field', 'helpful' ) ); ?>
	<input class="regular-text" type="text" name="helpful_feedback_label_name" value="<?php echo $value; ?>">
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group helpful-margin-bottom">
	<label class="helpful-block" for="helpful_feedback_label_email"><?php _ex( 'Email', 'option name', 'helpful' ); ?></label>
	<?php $value = get_option( 'helpful_feedback_label_email', _x( 'Email', 'label for feedback form field', 'helpful' ) ); ?>
	<input class="regular-text" type="text" name="helpful_feedback_label_email" value="<?php echo $value; ?>">
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group helpful-margin-bottom">
	<label class="helpful-block" for="helpful_feedback_label_submit"><?php _ex( 'Submit', 'option name', 'helpful' ); ?></label>
	<?php $value = get_option( 'helpful_feedback_label_submit', _x( 'Send Feedback', 'label for feedback form field', 'helpful' ) ); ?>
	<input class="regular-text" type="text" name="helpful_feedback_label_submit" value="<?php echo $value; ?>">
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group">
	<label class="helpful-block" for="helpful_feedback_label_cancel"><?php _ex( 'Cancel', 'option name', 'helpful' ); ?></label>
	<?php $value = get_option( 'helpful_feedback_label_cancel', _x( 'Cancel', 'label for feedback form field', 'helpful' ) ); ?>
	<input class="regular-text" type="text" name="helpful_feedback_label_cancel" value="<?php echo $value; ?>">
</div><!-- .helpful-admin-group -->

</div><!-- .helpful-admin-panel-content -->
</div><!-- .helpful-admin-panel -->

<div class="helpful-admin-panel">
	<button type="button" class="helpful-admin-panel-header">
		<span class="title"><?php _ex( 'Admin Area', 'admin panel title', 'helpful' ); ?></span>
		<span class="icon"></span>
	</button><!-- .helpful-admin-panel-header -->
<div class="helpful-admin-panel-content">

<p class="description"><?php echo esc_html_x( 'Here you can set settings for the overview in the admin area. Some options only work in combination with other options.', 'admin panel description', 'helpful' ); ?></p>

<div class="helpful-admin-group helpful-margin-bottom">
	<label>
		<?php $value = get_option( 'helpful_feedback_gravatar' ); ?>
		<input id="helpful_feedback_gravatar" type="checkbox" name="helpful_feedback_gravatar" <?php checked( 'on', $value ); ?> /> 
		<?php _ex( 'Use gravatars when user has left an email', 'label', 'helpful' ); ?>
	</label>
</div><!-- .helpful-admin-group -->

<div class="helpful-admin-group">
	<label>
		<?php $value = get_option( 'helpful_feedback_widget' ); ?>
		<input id="helpful_feedback_widget" type="checkbox" name="helpful_feedback_widget" <?php checked( 'on', $value ); ?> /> 
		<?php _ex( 'Show last feedback in Dashboard Widget', 'label', 'helpful' ); ?>
	</label>
</div><!-- .helpful-admin-group -->

</div><!-- .helpful-admin-panel-content -->
</div><!-- .helpful-admin-panel -->

<?php do_action( 'helpful-feedback-settings-after' ); ?>
<?php submit_button( __( 'Save Changes' ), 'default' ); ?>

</form>