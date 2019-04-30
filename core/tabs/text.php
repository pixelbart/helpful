<?php
// Prevent direct access
if ( !defined('ABSPATH') ) exit;

global $helpful;

if( $helpful['tab'] == 'text' ) :

// wp_editor settings
$settings = $helpful['wp_editor'];

?>

<h3><?php _ex( 'Texts', 'tab name', 'helpful' ); ?></h3>

<p><?php _ex( 'In this area, you can change all texts. You can use <code>{pro}</code> for outputting positive and <code>{contra}</code> for negative votes.', 'tab description', 'helpful' ); ?></p>

<hr />

<form method="post" action="options.php">

	<?php settings_fields( 'helpful-text-settings-group' ); ?>
	<?php do_settings_sections( 'helpful-text-settings-group' ); ?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="helpful_heading"><?php _ex( 'Headline', 'option name', 'helpful' ); ?></label></th>
			<td>
				<input type="text" id="helpful_heading" name="helpful_heading" class="regular-text" value="<?php echo esc_attr( get_option('helpful_heading') ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own headline.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_content"><?php _ex( 'Content', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php wp_editor( get_option('helpful_content'), 'helpful_content', $settings ); ?>
				<p class="description"><?php _ex( 'Here you can define your own content.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_exists"><?php _ex('Already voted', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php wp_editor( get_option('helpful_exists'), 'helpful_exists', $settings ); ?>
				<p class="description"><?php _ex( 'This text will appear if the user has already voted.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
	</table>

	<hr />

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="helpful_after_pro"><?php _ex('After voting (pro)', 'message after a positive voting', 'helpful'); ?></label></th>
			<td>
				<?php wp_editor( get_option('helpful_after_pro'), 'helpful_after_pro', $settings ); ?>
				<p class="description"><?php _ex( 'The text that is displayed, after a positive vote (shortcodes <b>without Ajax</b> are also possible!)', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_after_contra"><?php _ex('After voting (contra)', 'message after a negative voting', 'helpful'); ?></label></th>
			<td>
				<?php wp_editor( get_option('helpful_after_contra'), 'helpful_after_contra', $settings ); ?>
				<p class="description"><?php _ex( 'The text that is displayed, after a negative vote (shortcodes <b>without Ajax</b> are also possible!)', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
	</table>

	<hr />

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="helpful_pro"><?php _ex( 'Button (pro)', 'option name', 'helpful' ); ?></label></th>
			<td>
				<input type="text" id="helpful_pro" name="helpful_pro" class="regular-text" value="<?php echo esc_attr( get_option('helpful_pro') ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the pro button.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_contra"><?php _ex( 'Button (contra)', 'option name', 'helpful' ); ?></label></th>
			<td>
				<input type="text" id="helpful_contra" name="helpful_contra" class="regular-text" value="<?php echo esc_attr( get_option('helpful_contra') ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the contra button.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
	</table>

	<hr />

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="helpful_column_pro"><?php _ex('Column (pro)', 'option name', 'helpful' ); ?></label></th>
			<td>
				<input type="text" id="helpful_column_pro" name="helpful_column_pro" class="regular-text" value="<?php echo esc_attr( get_option('helpful_column_pro') ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the pro column in the post edit list.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_column_contra"><?php _ex( 'Column (contra)', 'option name', 'helpful' ); ?></label></th>
			<td>
				<input type="text" id="helpful_column_contra" name="helpful_column_contra" class="regular-text" value="<?php echo esc_attr( get_option('helpful_column_contra') ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the contra column in the post edit list.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
	</table>

	<hr />

	<?php submit_button(); ?>

</form>

<?php endif; ?>
