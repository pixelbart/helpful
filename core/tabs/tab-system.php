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
?>

<h2><?php _ex( 'System', 'tab name', 'helpful' ); ?></h2>

<p><?php _ex( 'Here you will find settings that Helpful can reset, or affect. Among other things you can set your own timezone, reset Helpful and set whether users can vote for a post more than once.', 'tab description', 'helpful' ); ?></p>

<form method="post" action="options.php">

	<?php settings_fields( 'helpful-system-settings-group' ); ?>
	<?php do_settings_sections( 'helpful-system-settings-group' ); ?>
	<?php submit_button( __( 'Save Changes'), 'default' ); ?>
	<?php do_action( 'helpful-system-settings-before' ); ?>

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex('Miscellaneous', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel -->

		<div class="helpful-admin-panel-content">

		<p class="description"><?php echo esc_html_x( "Here you'll find settings that might be useful, but didn't fit anywhere else or cause confusion. Note that if you allow users to vote more than once, this means the individual posts. Users can then vote more than once for a post.", 'admin panel description', 'helpful' ); ?></p>

		<div class="helpful-admin-group helpful-margin-bottom">
			<label class="helpful-block" for="helpful_timezone"><?php _ex( 'Custom timezone', 'option name', 'helpful' ); ?></label>
			<?php $value = get_option( 'helpful_timezone' ); ?>
			<input type="text" class="regular-text code" name="helpful_timezone" value="<?php echo $value; ?>">
		</div><!-- .helpful-admin-group -->

		<div class="helpful-admin-group helpful-margin-bottom">
			<label>
				<?php $value = get_option( 'helpful_multiple' ); ?>
				<input id="helpful_multiple" type="checkbox" name="helpful_multiple" <?php checked( 'on', $value ); ?> /> 
				<?php _ex( 'Enable to allow users to vote more than once in individual posts', 'label', 'helpful' ); ?>
			</label>
		</div><!-- .helpful-admin-group -->

		<div class="helpful-admin-group helpful-margin-bottom">
			<label>
				<?php $value = get_option( 'helpful_notes' ); ?>
				<input id="helpful_notes" type="checkbox" name="helpful_notes" <?php checked( 'on', $value ); ?> /> 
				<?php _ex('Check to completely disable admin notes for Helpful', 'label', 'helpful'); ?>
			</label>
		</div><!-- .helpful-admin-group -->

		<div class="helpful-admin-group helpful-margin-bottom">
			<label>
				<?php $value = get_option( 'helpful_plugin_first' ); ?>
				<input id="helpful_plugin_first" type="checkbox" name="helpful_plugin_first" <?php checked( 'on', $value ); ?> /> 
				<?php _ex('Select so that Helpful is always loaded first', 'label', 'helpful'); ?>
			</label>
		</div><!-- .helpful-admin-group -->

		<div class="helpful-admin-group">
			<label>
				<?php $value = get_option( 'helpful_classic_editor' ); ?>
				<input id="helpful_classic_editor" type="checkbox" name="helpful_classic_editor" <?php checked( 'on', $value ); ?> /> 
				<?php _ex( 'Activate the classic editor and deactivate the block editor', 'label', 'helpful' ); ?>
			</label>
		</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex( 'Maintenance', 'admin panel title', 'helpful' ); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->
		<div class="helpful-admin-panel-content helpful_maintenance">

			<p class="description"><?php echo esc_html_x( 'Here you can maintain the plugin. The database will be checked if there are any errors. In addition, feedback from older versions is moved into the database and old entries are deleted. This feature will also be extended in the future if there is something new. You can find more about this in the changelogs.', 'admin panel description', 'helpful' ); ?></p>

			<div class="helpful-admin-group helpful_response" hidden></div>

			<div class="helpful-admin-group">
				<button type="button" class="button button-default">
					<?php esc_html_e( 'Perform maintenance', 'helpful' ); ?>
				</button>
			</div><!-- .helpful-admin-group -->

			</div><!-- .helpful-admin-panel-content -->
			</div><!-- .helpful-admin-panel -->

			<div class="helpful-admin-panel">
				<button type="button" class="helpful-admin-panel-header">
					<span class="title helpful-danger"><?php _ex( 'Reset', 'admin panel title', 'helpful' ); ?></span>
					<span class="icon"></span>
				</button><!-- .helpful-admin-panel -->
			<div class="helpful-admin-panel-content">

			<p class="description"><?php echo esc_html_x( 'Here you can reset Helpful. This affects all entries in the database as well as the feedback. Settings are not affected. This action is irreversible, so be careful with it. If you want to reset individual posts, you only have to activate the meta box under Details. Then switch to the post and reset Helpful there.', 'admin panel description', 'helpful' ); ?></p>

			<div class="helpful-admin-group">
				<label class="helpful-danger">
					<?php $value = get_option( 'helpful_uninstall' ); ?>
					<input id="helpful_uninstall" type="checkbox" name="helpful_uninstall" <?php checked( 'on', $value ); ?> /> 
					<?php _ex( 'Reset Helpful', 'label', 'helpful' ); ?>
				</label>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<?php do_action( 'helpful-system-settings-after' ); ?>
	<?php submit_button( __( 'Save Changes' ), 'default' ); ?>

</form>