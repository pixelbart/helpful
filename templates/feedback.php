<?php
/**
 * @package Helpful
 * @version 4.4.59
 * @since 1.0.0
 */
use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

$options = new Services\Options();

/* Prevent direct access */
if (!defined('ABSPATH')) {
	exit;
}
?>

<?php if (isset($feedback_text) && false !== $feedback_text) : ?>
<div class="feedback-note">
	<p><?php echo $feedback_text; ?></p>
</div>
<?php endif; ?>

<div class="group">
	<?php $label = $options->get_option('helpful_feedback_label_message', '', 'kses_wot'); ?>
	<label for="message"><?php echo $label; ?> <req>*</req></label>
	<div class="control">
		<textarea name="message" id="message" required></textarea>
	</div>
</div><!-- .group -->

<?php if ($options->get_option('helpful_feedback_name') && !is_user_logged_in()) : ?>
<div class="group">
	<?php $label = $options->get_option('helpful_feedback_label_name', '', 'kses_wot'); ?>
	<label for="email"><?php echo $label; ?></label>
	<div class="control">
		<input type="text" name="fields[name]" id="name">
	</div>
</div><!-- .group -->
<?php endif; ?>

<?php if ($options->get_option('helpful_feedback_email') && !is_user_logged_in()) : ?>
<div class="group">
	<?php $label = $options->get_option('helpful_feedback_label_email', '', 'kses_wot'); ?>
	<label for="email"><?php echo $label; ?></label>
	<div class="control">
		<input type="email" name="fields[email]" id="email">
	</div>
</div><!-- .group -->
<?php endif; ?>

<div class="helpful-feedback-controls">
	<?php if ($options->get_option('helpful_feedback_cancel', '', 'kses_wot')) : ?>
	<?php $cancel = $options->get_option('helpful_feedback_label_cancel', '', 'kses_wot'); ?>
	<div>
		<button class="helpful-button helpful-cancel" type="button" role="button"><?php echo $cancel; ?></button>
	</div>
	<?php endif; ?>

	<?php $submit = $options->get_option('helpful_feedback_label_submit', '', 'kses_wot'); ?>
	<div>
		<button class="helpful-button helpful-submit" type="submit" role="button"><?php echo $submit; ?></button>
	</div>
</div>