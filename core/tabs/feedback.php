<?php
/**
 * Tab: System Options
 */

// Prevent direct access
if ( !defined('ABSPATH') ) exit;

global $helpful;

if( $helpful['tab'] == 'feedback' ):

// wp_editor settings
$settings = $helpful['wp_editor'];

?>

<h3><?php _ex( 'Feedback', 'tab name', 'helpful' ); ?></h3>

<p><?php _ex( 'Here you can activate and change settings for the feedback form after voting. Remember that the feedback form does not have spam protection.', 'tab description', 'helpful' ); ?></p>

<hr />

<form method="post" action="options.php">

	<?php settings_fields( 'helpful-feedback-settings-group' ); ?>
	<?php do_settings_sections( 'helpful-feedback-settings-group' ); ?>

  <table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="helpful_feedback_message_pro"><?php _ex( 'Message (pro)', 'option name', 'helpful' ); ?></label></th>
			<td>
  			<?php wp_editor( get_option('helpful_feedback_message_pro'), 'helpful_feedback_message_pro', $settings ); ?>
				<p class="description"><?php _ex( 'Here you can write a message which is displayed before the form. (pro)', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
  		<tr valign="top">
  			<th scope="row"><label for="helpful_feedback_message_contra"><?php _ex( 'Message (contra)', 'option name', 'helpful' ); ?></label></th>
  			<td>
    			<?php wp_editor( get_option('helpful_feedback_message_contra'), 'helpful_feedback_message_contra', $settings ); ?>
  				<p class="description"><?php _ex( 'Here you can write a message which is displayed before the form. (contra)', 'option info', 'helpful' ); ?></p>
  			</td>
  		</tr>
	</table>

	<div class="divider-text"><span><?php _ex('Form', 'divider text', 'helpful'); ?></span></div>

  <table class="form-table">
    <tr valign="top">
			<th scope="row"><label for="helpful_feedback_after_pro"><?php _ex('After voting (pro)', 'feedback after a positive voting', 'helpful'); ?></label></th>
			<td>
				<?php $value = get_option('helpful_feedback_after_pro'); ?>
				<label>
					<input id="helpful_feedback_after_pro" type="checkbox" name="helpful_feedback_after_pro" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( 'Show the feedback form after voting. (pro)', 'option info', 'helpful'); ?></p>
				</label>
			</td>
		</tr>
    <tr valign="top">
      <th scope="row"><label for="helpful_feedback_after_contra"><?php _ex('After voting (contra)', 'feedback after a negative voting', 'helpful'); ?></label></th>
      <td>
        <?php $value = get_option('helpful_feedback_after_contra'); ?>
        <label>
          <input id="helpful_feedback_after_contra" type="checkbox" name="helpful_feedback_after_contra" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( 'Show the feedback form after voting. (contra)', 'option info', 'helpful'); ?></p>
        </label>
      </td>
    </tr>
	</table>

	<div class="divider-text"><span><?php _ex('Widget', 'divider text', 'helpful'); ?></span></div>

  <table class="form-table">
    <tr valign="top">
			<th scope="row"><label for="helpful_feedback_widget"><?php _ex( 'Recent Feedback', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_feedback_widget'); ?>
				<label>
					<input id="helpful_feedback_widget" type="checkbox" name="helpful_feedback_widget" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( 'Show recent feedback in the dashboard widget.', 'option info', 'helpful'); ?></p>
				</label>
			</td>
		</tr>
    <tr valign="top">
      <th scope="row"><label for="helpful_feedback_widget_overview"><?php _ex( 'Link to overview', 'option name', 'helpful' ); ?></label></th>
      <td>
        <?php $value = get_option('helpful_feedback_widget_overview'); ?>
        <label>
          <input id="helpful_feedback_widget_overview" type="checkbox" name="helpful_feedback_widget_overview" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( 'If you click on single entries in the Dashboard Widget, you will be directed to the overview and no longer to the single entry.', 'option info', 'helpful'); ?></p>
        </label>
      </td>
    </tr>
	</table>

	<div class="divider-text"><span><?php _ex('Overview', 'divider text', 'helpful'); ?></span></div>

  <table class="form-table">
    <tr valign="top">
      <th scope="row"><label for="helpful_feedback_messages_table"><?php _ex( 'Messages in tables', 'option name', 'helpful' ); ?></label></th>
      <td>
        <?php $value = get_option('helpful_feedback_messages_table'); ?>
        <label>
          <input id="helpful_feedback_messages_table" type="checkbox" name="helpful_feedback_messages_table" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( 'Shows the messages already in the tables without having to click on the entries.', 'option info', 'helpful'); ?></p>
        </label>
      </td>
    </tr>
    <tr valign="top">
			<th scope="row"><label for="helpful_feedback_table_type"><?php _ex( 'Feedback Type', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_feedback_table_type'); ?>
				<label>
					<input id="helpful_feedback_table_type" type="checkbox" name="helpful_feedback_table_type" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( 'If selected, the type of feedback is displayed in tables.', 'option info', 'helpful'); ?></p>
				</label>
			</td>
		</tr>
    <tr valign="top">
      <th scope="row"><label for="helpful_feedback_table_post"><?php _ex( 'Post', 'option name', 'helpful' ); ?></label></th>
      <td>
        <?php $value = get_option('helpful_feedback_table_post'); ?>
        <label>
          <input id="helpful_feedback_table_post" type="checkbox" name="helpful_feedback_table_post" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( 'If selected, the linked post to the feedback is displayed in tables.', 'option info', 'helpful'); ?></p>
        </label>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="helpful_feedback_table_browser"><?php _ex( 'User Browser', 'option name', 'helpful' ); ?></label></th>
      <td>
        <?php $value = get_option('helpful_feedback_table_browser'); ?>
        <label>
          <input id="helpful_feedback_table_browser" type="checkbox" name="helpful_feedback_table_browser" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( "If selected, the user's browser is displayed in tables. Uses <code>get_browser</code> to determine the browser.", 'option info', 'helpful'); ?></p>
        </label>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="helpful_feedback_table_platform"><?php _ex( 'User Platform', 'option name', 'helpful' ); ?></label></th>
      <td>
        <?php $value = get_option('helpful_feedback_table_platform'); ?>
        <label>
          <input id="helpful_feedback_table_platform" type="checkbox" name="helpful_feedback_table_platform" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( "When selected, the user's platform for feedback is displayed in tables. Uses <code>get_browser</code> to determine the platform.", 'option info', 'helpful'); ?></p>
        </label>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="helpful_feedback_table_language"><?php _ex( 'User Language', 'option name', 'helpful' ); ?></label></th>
      <td>
        <?php $value = get_option('helpful_feedback_table_language'); ?>
        <label>
          <input id="helpful_feedback_table_language" type="checkbox" name="helpful_feedback_table_language" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( "If selected, the user's language for feedback is displayed in tables. Uses <code>\$_SERVER</code> to determine the language.", 'option info', 'helpful'); ?></p>
        </label>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="helpful_feedback_table_post_shorten"><?php _ex( 'Shorten Post Column', 'option name', 'helpful' ); ?></label></th>
      <td>
        <?php $value = get_option('helpful_feedback_table_post_shorten'); ?>
        <label>
          <input id="helpful_feedback_table_post_shorten" type="checkbox" name="helpful_feedback_table_post_shorten" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
          <p class="description"><?php _ex( "Shortens the text in the column with the linked post by 30 words.", 'option info', 'helpful'); ?></p>
        </label>
      </td>
    </tr>
	</table>

  <hr/>

	<?php submit_button(); ?>

</form>

<?php endif; ?>
