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

if (1 >= $max_num_pages) {
	return;
}
?>

<div class="helpful-admin-pagination">
	<div><span><?php printf(esc_html_x('Page %s of %s', 'feedback pagination', 'helpful'), $page, $max_num_pages); ?></span></div>
	<div>
		<?php if ($prev_show) : ?>
		<button type="button" class="button default icon" data-page="<?php echo $prev_page; ?>"><span class="dashicons dashicons-arrow-left-alt"></span></button>
		<?php endif; ?>

		<?php if ($next_show) : ?>
		<button type="button" class="button default icon" data-page="<?php echo $next_page; ?>"><span class="dashicons dashicons-arrow-right-alt"></span></button>
		<?php endif; ?>
	</div>
</div>