<?php
/**
 * @package Helpful
 * @version 4.5.0
 * @since 1.0.0
 */
use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
	exit;
}

$options = new Services\Options();
$settings = apply_filters('helpful_editor_settings', false);
$feedback_email_content = Helpers\Feedback::get_email_content();
$feedback_email_content_voter = Helpers\Feedback::get_email_content_voter();

do_action('helpful_tab_feedback_before');
?>

<h2><?php echo esc_html_x('Feedback', 'tab name', 'helpful'); ?></h2>

<p><?php echo esc_html_x('Here you can activate a feedback form. The feedback form will be displayed after voting and can be configured here. Please note that the form is not spam protected.', 'tab description', 'helpful'); ?></p>

<form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
	<input type="hidden" name="option_page" value="helpful-feedback-settings-group">
	<input type="hidden" name="action" value="helpful_update_options">

	<?php wp_nonce_field('helpful_update_options'); ?>

	<?php submit_button(__('Save Changes'), 'default'); ?>
	<?php do_action('helpful_feedback_settings_before'); ?>

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Form', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_after_pro', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_after_pro" type="checkbox" name="helpful_feedback_after_pro" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Show form after positive vote', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_after_contra', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_after_contra" type="checkbox" name="helpful_feedback_after_contra" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Show form after negative vote', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_name', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_name" type="checkbox" name="helpful_feedback_name" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Show name field below the form', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_email', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_email" type="checkbox" name="helpful_feedback_email" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Show email field below the form', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label>
					<?php $value = $options->get_option('helpful_feedback_cancel', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_cancel" type="checkbox" name="helpful_feedback_cancel" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Show Cancel button', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Messages', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<p class="description"><?php echo esc_html_x('Here you can change the texts that are displayed directly in front of the feedback form. Briefly explain to your visitors why they should give feedback.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_feedback_message_pro"><?php echo esc_html_x('Message (pro)', 'option name', 'helpful'); ?></label>
				<?php wp_editor($options->get_option('helpful_feedback_message_pro', '', 'kses'), 'helpful_feedback_message_pro', $settings); ?>
				<p class="description"><?php echo esc_html_x('This message is displayed if the user has voted positively.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_feedback_message_contra"><?php echo esc_html_x('Message (contra)', 'option name', 'helpful'); ?></label>
				<?php wp_editor($options->get_option('helpful_feedback_message_contra', '', 'kses'), 'helpful_feedback_message_contra', $settings); ?>
				<p class="description"><?php echo esc_html_x('This message is displayed if the user has voted negatively.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_feedback_message_spam"><?php echo esc_html_x('Message (spam)', 'option name', 'helpful'); ?></label>
				<?php wp_editor($options->get_option('helpful_feedback_message_spam', '', 'kses'), 'helpful_feedback_message_spam', $settings); ?>
				<p class="description"><?php echo esc_html_x('This message is shown to users who try to send spam through the form.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Labels', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<p class="description"><?php echo esc_html_x('Here you can define the labels for the form fields. The text for the button can also be changed.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_feedback_label_message"><?php echo esc_html_x('Message', 'option name', 'helpful'); ?></label>
				<?php $value = $options->get_option('helpful_feedback_label_message', _x('Message', 'label for feedback form field', 'helpful'), 'kses'); ?>
				<input class="regular-text" type="text" name="helpful_feedback_label_message" value="<?php echo $value; ?>">
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_feedback_label_name"><?php echo esc_html_x('Name', 'option name', 'helpful'); ?></label>
				<?php $value = $options->get_option('helpful_feedback_label_name', _x('Name', 'label for feedback form field', 'helpful'), 'kses'); ?>
				<input class="regular-text" type="text" name="helpful_feedback_label_name" value="<?php echo $value; ?>">
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_feedback_label_email"><?php echo esc_html_x('Email', 'option name', 'helpful'); ?></label>
				<?php $value = $options->get_option('helpful_feedback_label_email', _x('Email', 'label for feedback form field', 'helpful'), 'kses'); ?>
				<input class="regular-text" type="text" name="helpful_feedback_label_email" value="<?php echo $value; ?>">
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_feedback_label_submit"><?php echo esc_html_x('Submit', 'option name', 'helpful'); ?></label>
				<?php $value = $options->get_option('helpful_feedback_label_submit', _x('Send Feedback', 'label for feedback form field', 'helpful'), 'kses'); ?>
				<input class="regular-text" type="text" name="helpful_feedback_label_submit" value="<?php echo $value; ?>">
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_feedback_label_cancel"><?php echo esc_html_x('Cancel', 'option name', 'helpful'); ?></label>
				<?php $value = $options->get_option('helpful_feedback_label_cancel', _x('Cancel', 'label for feedback form field', 'helpful'), 'kses'); ?>
				<input class="regular-text" type="text" name="helpful_feedback_label_cancel" value="<?php echo $value; ?>">
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Already Voted', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<p class="description"><?php echo esc_html_x('The settings you make here affect every feedback form that is displayed despite the votes cast. This option activates the form at any time, even after a vote has been cast.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_after_vote', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_after_vote" type="checkbox" name="helpful_feedback_after_vote" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Always show the form, even if it has already been voted.', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_feedback_message_voted"><?php echo esc_html_x('Message (already voted)', 'option name', 'helpful'); ?></label>
				<?php wp_editor($options->get_option('helpful_feedback_message_voted', '', 'kses'), 'helpful_feedback_message_voted', $settings); ?>
				<p class="description"><?php echo esc_html_x('This message is shown if the user has already voted.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Admin Area', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<p class="description"><?php echo esc_html_x('Here you can set settings for the overview in the admin area. Some options only work in combination with other options.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_gravatar', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_gravatar" type="checkbox" name="helpful_feedback_gravatar" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Use gravatars when user has left an email', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_widget', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_widget" type="checkbox" name="helpful_feedback_widget" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Show last feedback in Dashboard Widget', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label>
					<?php $value = esc_attr($options->get_option('helpful_feedback_amount', 10, 'intval')); ?>
					<input type="number" id="helpful_feedback_amount" name="helpful_feedback_amount" class="small-text" min="1" value="<?php echo esc_attr($value); ?>"/>
					<?php echo esc_html_x('Number of entries', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x('Emails', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">			
			<?php $tags = ['{name}', '{email}', '{message}', '{type}', '{post_url}', '{post_title}', '{blog_name}', '{blog_url}']; ?>

			<p class="description"><?php echo esc_html_x('Here you can specify whether a copy of your feedback should be sent by email. You can specify individual receivers in the metabox below posts. The emails are not spam protected. The emails are sent with wp_mail(). So you can control how these emails are sent with certain plugins.', 'admin panel description', 'helpful'); ?></p>
			<p class="description"><?php echo esc_html_x('Available tags: ', 'admin panel description', 'helpful'); ?><code><?php echo implode('</code>, <code>', $tags); ?></code></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_send_email', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_send_email" type="checkbox" name="helpful_feedback_send_email" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Receive feedback by email', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<?php $value = $options->get_option('helpful_feedback_receivers', get_option('admin_email'), 'esc_attr'); ?>
				<label class="helpful-block" for="helpful_feedback_receivers"><?php echo esc_html_x('Email Receivers', 'option name', 'helpful'); ?></label>
				<input class="regular-text" type="text" name="helpful_feedback_receivers" value="<?php echo $value; ?>" />
				<p class="description"><?php echo esc_html_x('You can separate multiple emails using commas.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<?php $value = $options->get_option('helpful_feedback_subject', _x('There\'s new feedback for you.', 'feedback email subject', 'helpful'), 'kses_wot'); ?>
				<label class="helpful-block" for="helpful_feedback_subject"><?php echo esc_html_x('Email Subject', 'option name', 'helpful'); ?></label>
				<input class="regular-text" type="text" name="helpful_feedback_subject" value="<?php echo $value; ?>" />
				<p class="description"><?php echo esc_html_x('Here you can set the subject of the email.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<?php $value = $options->get_option('helpful_feedback_email_content', $feedback_email_content, 'kses'); ?>
				<?php $value = ('' === trim($value)) ? $feedback_email_content : $value; ?>
				<label class="helpful-block" for="helpful_feedback_email_content"><?php echo esc_html_x('Email Content', 'option name', 'helpful'); ?></label>
				<?php wp_editor($value, 'helpful_feedback_email_content', $settings); ?>
				<p class="description"><?php echo esc_html_x('Here you can define the content of the e-mail.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

			<hr>

			<p class="description"><?php echo esc_html_x('Here you can determine whether the person who has voted and deposited an email will also receive an email. You can also define the text for the e-mail.', 'admin panel description', 'helpful'); ?></p>
			<p class="description"><?php echo esc_html_x('Available tags: ', 'admin panel description', 'helpful'); ?><code><?php echo implode('</code>, <code>', $tags); ?></code></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_feedback_send_email_voter', 'off', 'esc_attr'); ?>
					<input id="helpful_feedback_send_email_voter" type="checkbox" name="helpful_feedback_send_email_voter" <?php checked('on', $value); ?> />
					<?php echo esc_html_x('Voting person receives e-mail', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<?php $value = $options->get_option('helpful_feedback_subject_voter', _x('Thanks for your feedback!', 'voters feedback email subject', 'helpful'), 'kses_wot'); ?>
				<label class="helpful-block" for="helpful_feedback_subject_voter"><?php echo esc_html_x('Email Subject', 'option name', 'helpful'); ?></label>
				<input class="regular-text" type="text" name="helpful_feedback_subject_voter" value="<?php echo $value; ?>" />
				<p class="description"><?php echo esc_html_x('Here you can set the subject of the email.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<?php $value = $options->get_option('helpful_feedback_email_content_voter', $feedback_email_content_voter, 'kses'); ?>
				<?php $value = ('' === trim($value)) ? $feedback_email_content_voter : $value; ?>
				<label class="helpful-block" for="helpful_feedback_email_content_voter"><?php echo esc_html_x('Email Content', 'option name', 'helpful'); ?></label>
				<?php wp_editor($value, 'helpful_feedback_email_content_voter', $settings); ?>
				<p class="description"><?php echo esc_html_x('Here you can define the content of the e-mail.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<?php do_action('helpful_feedback_settings_after'); ?>
	<?php submit_button( __('Save Changes'), 'default'); ?>
</form>

<?php do_action('helpful_tab_feedback_after'); ?>