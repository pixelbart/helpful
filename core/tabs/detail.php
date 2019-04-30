<?php
// Prevent direct access
if ( !defined('ABSPATH') ) exit;

global $helpful;

if( $helpful['tab'] == 'detail' ) :

// wp_editor settings
$settings = $helpful['wp_editor'];

?>

<h3><?php _ex( 'Details', 'tab name', 'helpful' ); ?></h3>

<p><?php _ex( 'In this section you can customize helpful in detail.', 'tab description', 'helpful' ); ?></p>

<hr />

<form method="post" action="options.php">

	<?php settings_fields( 'helpful-detail-settings-group' ); ?>
	<?php do_settings_sections( 'helpful-detail-settings-group' ); ?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="helpful_post_types"><?php _ex( 'Post types', 'option name', 'helpful' ); ?></label></th>
			<td class="helpful-checkbox">

				<?php
				$post_types = get_post_types( ['public' => true] );
				$private_post_types = get_post_types( ['public' => false] );

				if( $private_post_types ):
				$post_types = array_merge($post_types, $private_post_types);
				endif;
				?>

				<?php if( $post_types ) : foreach( $post_types as $post_type ) : ?>

				<?php if( get_option('helpful_post_types') ) : ?>

					<?php $checked = in_array( $post_type, get_option('helpful_post_types') ) ? 'checked="checked"' : ''; ?>

					<label>
						<input type="checkbox" name="helpful_post_types[]" id="helpful_post_types[]" value="<?php echo $post_type; ?>" <?php echo $checked; ?>/>
						<?php echo $post_type; ?>
					</label>

				<?php else : ?>

					<label>
						<input type="checkbox" name="helpful_post_types[]" id="helpful_post_types[]" value="<?php echo $post_type; ?>"/>
						<?php echo $post_type; ?>
					</label>

				<?php endif; ?>

				<?php endforeach; endif; ?>

				<p class="description"><?php _ex('Here you can choose the post types, where helpful should appear.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_exists_hide"><?php _ex( 'Already voted', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_exists_hide'); ?>
				<label>
					<input id="helpful_exists_hide" type="checkbox" name="helpful_exists_hide" <?php checked('on', $value); ?> /> <?php _ex( 'hide', 'label', 'helpful' ); ?>
				</label>
				<p class="description"><?php _ex( 'Hide Helpful, if the user has already voted.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_count_hide"><?php _ex( 'Votes', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_count_hide'); ?>
				<label>
					<input id="helpful_count_hide" type="checkbox" name="helpful_count_hide" <?php checked('on', $value); ?> /> <?php _ex( 'hide', 'label', 'helpful' ); ?>
				</label>
				<p class="description"><?php _ex( 'Hide vote counters.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_credits"><?php _ex( 'Credits', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_credits'); ?>
				<label>
					<input id="helpful_credits" type="checkbox" name="helpful_credits" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
				</label>
				<p class="description"><?php _ex( 'Support me and show your visitors that this plugin is from me.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_hide_in_content"><?php _ex( 'Helpful', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_hide_in_content'); ?>
				<label>
					<input id="helpful_hide_in_content" type="checkbox" name="helpful_hide_in_content" <?php checked('on', $value); ?>  /> <?php _ex('hide', 'label', 'helpful'); ?>
				</label>
				<p class="description"><?php _ex( 'Hide Helpful in your content. These option is useful by using the shortcode (<code>[helpful]</code>).', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_only_once"><?php _ex( 'Only once', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_only_once'); ?>
				<label>
					<input id="helpful_only_once" type="checkbox" name="helpful_only_once" <?php checked('on', $value); ?>  /> <?php _ex('enable', 'label', 'helpful'); ?>
				</label>
				<p class="description"><?php _ex( 'If enabled, guests can vote only once on the entire website.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_multiple"><?php _ex( 'Multiple times', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_multiple'); ?>
				<label>
					<input id="helpful_multiple" type="checkbox" name="helpful_multiple" <?php checked('on', $value); ?> /> <?php _ex('enable', 'label', 'helpful'); ?>
				</label>
				<p class="description"><?php _ex( 'If enabled, guests can vote as often as they like.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_percentages"><?php _ex( 'Percentages', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_percentages'); ?>
				<label>
					<input id="helpful_percentages" type="checkbox" name="helpful_percentages" <?php checked('on', $value); ?> /> <?php _ex('enable', 'label', 'helpful'); ?>
				</label>
				<p class="description"><?php _ex( 'If activated, percentages are displayed first.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="helpful_meta_box"><?php _ex( 'Meta Box', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_meta_box'); ?>
				<label>
					<input id="helpful_meta_box" type="checkbox" name="helpful_meta_box" <?php checked('on', $value); ?> /> <?php _ex('enable', 'label', 'helpful'); ?>
				</label>
				<p class="description"><?php _ex( 'If activated, a meta box appears in which the current post can be reset.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
	</table>

	<div class="divider-text"><span><?php _ex('Widget', 'divider text', 'helpful'); ?></span></div>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="helpful_widget"><?php _ex( 'Dashboard widget', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_widget'); ?>
				<label>
					<input id="helpful_widget" type="checkbox" name="helpful_widget" <?php checked('on', $value); ?> /> <?php _ex( 'hide', 'label', 'helpful' ); ?>
				</label>
				<p class="description"><?php _ex( 'Hide the Helpful dashboard widget.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
    <tr valign="top">
			<th scope="row"><label for="helpful_widget_pro"><?php _ex( 'Most helpful', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_widget_pro'); ?>
				<label>
					<input id="helpful_widget_pro" type="checkbox" name="helpful_widget_pro" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
				</label>
				<p class="description"><?php _ex( 'Show most helpful entries.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
    <tr valign="top">
			<th scope="row"><label for="helpful_widget_contra"><?php _ex( 'Least helpful', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_widget_contra'); ?>
				<label>
					<input id="helpful_widget_contra" type="checkbox" name="helpful_widget_contra" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
				</label>
				<p class="description"><?php _ex( 'Show least helpful entries.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
    <tr valign="top">
			<th scope="row"><label for="helpful_widget_pro_recent"><?php _ex( 'Recently helpful', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_widget_pro_recent'); ?>
				<label>
					<input id="helpful_widget_pro_recent" type="checkbox" name="helpful_widget_pro_recent" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
				</label>
				<p class="description"><?php _ex( 'Show recently helpful entries.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
    <tr valign="top">
			<th scope="row"><label for="helpful_widget_contra_recent"><?php _ex( 'Recently unhelpful', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $value = get_option('helpful_widget_contra_recent'); ?>
				<label>
					<input id="helpful_widget_contra_recent" type="checkbox" name="helpful_widget_contra_recent" <?php checked('on', $value); ?> /> <?php _ex( 'show', 'label', 'helpful' ); ?>
				</label>
				<p class="description"><?php _ex( 'Show recently unhelpful entries.', 'option info', 'helpful' ); ?></p>
			</td>
		</tr>
    <tr valign="top">
			<th scope="row"><label for="helpful_widget_amount"><?php _ex( 'Number of entries', 'option name', 'helpful' ); ?></label></th>
			<td>
				<?php $number = get_option('helpful_widget_amount') ? get_option('helpful_widget_amount') : 5; ?>
				<label>
					<input id="helpful_widget_amount" type="number" name="helpful_widget_amount" value="<?php echo $number; ?>" min="1" />
				</label>
			</td>
		</tr>
	</table>

	<hr />

	<?php submit_button(); ?>

</form>

<?php endif; ?>
