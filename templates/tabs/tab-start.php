<?php
/**
 * @package Helpful
 * @version 4.5.0
 * @since 1.0.0
 */
use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
	exit;
}

do_action('helpful_tab_start_before');
?>

<div class="helpful-flex range" style="z-index:2 !important">
	<div class="helpful-card">
		<div class="helpful-card_header">
			<form class="helpful-range-form helpful-flex helpful-flex-middle">
				<input type="hidden" name="action" value="helpful_range_stats">
				<?php wp_nonce_field('helpful_range_stats'); ?>
				<input class="helpful-date helpful-margin-right" type="text" name="from" placeholder="YYYY-MM-DD" value="<?php echo date_i18n('Y-m-d', strtotime('-14 days')); ?>">
				<span class="helpful-hide-mobile helpful-margin-right"><?php echo esc_html_x('to', 'from date to date', 'helpful'); ?></span>
				<input class="helpful-date helpful-margin-auto-right" type="text" name="to" placeholder="YYYY-MM-DD" value="<?php echo date_i18n('Y-m-d'); ?>">
				<select name="type" class="helpful-margin-left">
					<option value="stacked"><?php _ex('Stacked', 'admin chart type', 'helpful'); ?></option>
					<option value="default"><?php _ex('Default', 'admin chart type', 'helpful'); ?></option>
				</select>
			</form>
		</div><!-- .helpful-card_header -->
	<div class="helpful-card_content helpful-range" style="min-height: 300px"></div>
	</div><!-- .helpful-card -->
</div><!-- .range -->
<div class="helpful-flex" style="z-index:1 !important">
	<div class="helpful-card">
		<div class="helpful-card_content helpful-padding-remove">
			<div class="table-container">
				<table id="helpful-table-posts" class="helpful-table display" width="100%">
					<thead>
						<tr>
							<th><?php echo esc_html_x('ID', 'datatable head', 'helpful'); ?></th>
							<th><?php echo esc_html_x('Title', 'datatable head', 'helpful'); ?></th>
							<th><?php echo esc_html_x('Type', 'datatable head', 'helpful'); ?></th>
							<th><?php echo esc_html_x('Author', 'datatable head', 'helpful'); ?></th>
							<th><?php echo esc_html_x('Pro', 'datatable head', 'helpful'); ?></th>
							<th><?php echo esc_html_x('Contra', 'datatable head', 'helpful'); ?></th>
							<th><?php echo esc_html_x('Helpful', 'datatable head', 'helpful'); ?></th>
							<?php if (!Helper::is_feedback_disabled()) : ?>
							<th><?php echo esc_html_x('Feedback', 'datatable head', 'helpful'); ?></th>
							<?php endif; ?>
							<th><?php echo esc_html_x('Published', 'datatable head', 'helpful'); ?></th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div>

<?php do_action('helpful_tab_start_after'); ?>