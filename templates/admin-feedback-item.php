<?php
/**
 * @package Helpful
 * @version 4.4.50
 * @since 1.0.0
 */
use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
	exit;
}
?>

<article class="helpful-admin-item">
	<header>
		<div class="helpful-admin-image">
			<?php echo $feedback->avatar; ?>
			<div class="type <?php echo (1 == $feedback->pro) ? 'pro' : 'contra'; ?>"></div>
		</div><!-- .helpful-admin-image -->

		<div class="helpful-admin-info">
			<strong><?php echo $feedback->name; ?></strong>
			<div class="helpful-muted"><?php echo $feedback->time; ?></div>
			<div class="helpful-post"><a href="<?php echo get_the_permalink($feedback->post->ID); ?>" target="_blank"><?php echo $feedback->post->post_title; ?></a></div>
		</div><!-- .helpful-admin-info -->

		<div class="helpful-admin-actions">
			<button type="button" class="helpful-delete-item" data-id="<?php echo $feedback->id; ?>">
				<span class="dashicons dashicons-trash"></span>
			</button>
		</div><!-- .helpful-admin-actions -->
	</header>

	<div class="helpful-admin-body">
		<?php echo $feedback->message; ?>
	</div><!-- .helpful-admin-body -->

	<?php if (isset($feedback->fields)) : ?>
	<footer>
		<?php foreach($feedback->fields as $label => $value): ?>
		<div><strong><?php echo ucfirst($label); ?>:</strong> <?php echo $value; ?></div>
		<?php endforeach; ?>
	</footer>
	<?php endif; ?>
</article><!-- .helpful-admin-item -->
