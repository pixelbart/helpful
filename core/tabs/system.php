<?php
/**
 * Tab: System Options
 */

// Prevent direct access
if ( !defined('ABSPATH') ) exit;

global $helpful;

if( $helpful['tab'] == 'system' ):

?>

<h3><?php _ex( 'System', 'tab name', 'helpful' ); ?></h3>

<p><?php _ex( 'Here you can reset Helpful. This affects the database table and also the stored values. <b class="danger">This process can not be undone!</b>', 'tab description', 'helpful' ); ?></p>

<hr />

<form method="post" action="options.php">

	<?php settings_fields( 'helpful-system-settings-group' ); ?>
	<?php do_settings_sections( 'helpful-system-settings-group' ); ?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _ex( 'Reset plugin', 'option name', 'helpful' ); ?></th>
			<td>
				<?php $checked = ( get_option('helpful_uninstall') ? 'checked="checked"' : '' ); ?>
				<label><input type="checkbox" name="helpful_uninstall" <?php echo $checked; ?> /></label>
			</td>
		</tr>
    <tr valign="top">
			<th scope="row"><?php _ex( 'Timezone', 'option name', 'helpful' ); ?></th>
			<td>
				<?php $value = get_option('helpful_timezone'); ?>
				<label><input type="text" class="regular-text code" name="helpful_timezone" value="<?php echo $value; ?>"></label>
				<p class="description"><?php _ex( 'More informations: ', 'option info', 'helpful' ); ?><a href="http://php.net/manual/de/timezones.php" target="_blank"><?php _e( 'Timezones', 'helpful' ); ?></a></p>
			</td>
		</tr>
	</table>

	<hr />

	<?php submit_button(); ?>

</form>

<?php endif; ?>
