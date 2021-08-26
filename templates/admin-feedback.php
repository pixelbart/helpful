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

<div class="helpful-admin-header">
	<div class="helpful-admin-logo">
		<h1><?php esc_html_e('Feedback', 'helpful'); ?></h1>
	</div><!-- .helpful-admin-logo -->
</div><!-- .helpful-admin-header -->

<div class="helpful-admin-content">
	<div class="helpful-admin-container">
		<form method="POST" action="" class="helpful-admin-filter">
			<?php wp_nonce_field('helpful_admin_feedback_filter'); ?>
			<input type="hidden" name="action" value="helpful_admin_feedback_items">
			<input type="hidden" name="paginate" value="1">
			
			<?php if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) : ?>
			<input type="hidden" name="post_id" value="<?php echo intval($_GET['post_id']); ?>">
			<?php endif; ?>

			<select name="filter">
				<option value="all"><?php esc_html_e('All entries', 'helpful'); ?></option>
				<option value="pro"><?php esc_html_e('Pro', 'helpful'); ?></option>
				<option value="contra"><?php esc_html_e('Contra', 'helpful'); ?></option>
			</select>

			<button type="button" class="button default helpful-reset" style="margin-right: 15px; margin-left: 5px; display: none;">
				<?php echo esc_html_x('Reset filter', 'button text', 'helpful'); ?>
			</button>
			
			<button type="button" class="button danger helpful-delete-feedback" style="margin-right: auto; margin-left: 5px;">
				<?php _ex('Delete all', 'admin delete all feedback button', 'helpful'); ?>
			</button>

			<button type="button" class="button default helpful-export" data-type="feedback">
				<?php echo esc_html_x('Export', 'export button text', 'helpful'); ?>
			</button>
		</form><!-- .helpful-admin-filter -->
	
		<div class="helpful-admin-feedback">
		</div><!-- .helpful-admin-feedback -->
	</div><!-- .helpful-admin-container -->
</div><!-- .helpful-admin-content -->
