<?php
/**
 * Tab: Design Options
 */

// Prevent direct access
if ( !defined('ABSPATH') ) exit;

global $helpful;

if( $helpful['tab'] == 'design' ) :

?>

<h3><?php _ex('Design', 'tab name', 'helpful'); ?></h3>

<p><?php _ex('In this section, you can change settings for the design.', 'tab description', 'helpful'); ?></p>

<hr />

<form method="post" action="options.php">

	<?php settings_fields( 'helpful-design-settings-group' ); ?>
	<?php do_settings_sections( 'helpful-design-settings-group' ); ?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="helpful_theme">
					<?php _ex('Theme', 'option name', 'helpful'); ?>
				</label>
			</th>
			<td>
				<label class="helpful-select">
					<select id="helpful_theme" name="helpful_theme" class="regular-text">
						<?php $themes = apply_filters( 'helpful-themes', $themes ); ?>
						<?php if( $themes ) : foreach( $themes as $id => $label ) : ?>
							<?php $selected = ( $id == get_option('helpful_theme') ) ? 'selected="selected"' : ''; ?>
							<option value="<?php echo $id; ?>" <?php echo $selected; ?>>
								<?php echo $label; ?>
							</option>
						<?php endforeach; endif; ?>
					</select>
				</label>

				<div id="theme-preview">

					<div id="theme-preview-device">

						<div id="hf-prev" class="helpful helpful-theme-base">
							<div class="helpful-heading">
								<?php echo get_option( 'helpful_heading' ); ?>
							</div>
							<div class="helpful-content">
								<?php echo get_option( 'helpful_content' ); ?>
							</div>
							<div class="helpful-controls">
								<div class="helpful-pro">
									<?php echo get_option( 'helpful_pro' ); ?> <span class="counter">0</span>
								</div>
								<div class="helpful-con">
									<?php echo get_option( 'helpful_contra' ); ?> <span class="counter">0</span>
								</div>
							</div>
              <?php
              if( get_option('helpful_feedback_after_contra') || get_option('helpful_feedback_after_pro') ):
              $feedback_text = _x('Thank you very much. Please write us your opinion, so that we can improve ourselves.', 'form user note', 'helpful');
              ?>
              <div class="divider"></div>
              <div class="helpful helpful-feedback">
                <div class="helpful-feedback">
                  <?php if( $feedback_text ) printf('<p>%s</p>', $feedback_text); ?>
                  <textarea name="helpful_feedback"></textarea>
                  <button type="button"><?php _ex('Send Feedback', 'button text', 'helpful'); ?></button>
                </div>
              </div>
              <?php endif; ?>
						</div>


					</div>

					<div id="theme-preview-controls">
						<span class="dashicons dashicons-laptop show-laptop"></span>
						<span class="dashicons dashicons-tablet show-tablet"></span>
						<span class="dashicons dashicons-smartphone show-smartphone"></span>
						<span class="dashicons dashicons-no-alt close"></span>
					</div>

				</div>
			</td>
		</tr>
	</table>

	<hr />

	<?php do_action( 'helpful_design_settings' ); ?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="helpful_css"><?php _ex('Custom CSS', 'option name', 'helpful'); ?></label></th>
			<td class="helpful-code-editor">
				<textarea id="helpful_css" name="helpful_css" class="regular-text helpful_css" rows="15"><?php echo get_option('helpful_css'); ?></textarea>
			</td>
		</tr>
	</table>

	<hr />

	<?php submit_button(); ?>

</form>

<?php endif; ?>
