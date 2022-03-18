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

do_action('helpful_tab_export_before');
?>

<h2><?php _ex('Export & Import', 'tab name', 'helpful'); ?></h2>

<p><?php _ex('Here you can export and import Helpful settings. Note that this does not mean the voices. Also the appearance cannot be exported in this way.', 'tab description', 'helpful'); ?></p>

<form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
	<input type="hidden" name="action" value="helpful_import">

	<?php wp_nonce_field('helpful_import'); ?>
	<?php do_action('helpful_system_settings_before'); ?>

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex('Export', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">
			<p class="description"><?php _ex('Here you can copy the code, which you then have to paste under Import, so that the settings are imported on the other website.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_export"><?php _ex('Export', 'option name', 'helpful'); ?></label>
				<textarea id="helpful_export" class="large-text code" rows="5"><?php echo base64_encode(wp_json_encode($options->get_options())); ?></textarea>
				<p class="description"><?php printf(__('%d settings were found. It is best to save the settings in each tab beforehand so that nothing is forgotten.', 'option description', 'helpful'), count($options->get_options())); ?></p>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex('Import', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">
			<p class="description"><?php _ex('The code from the import is inserted here. Then click on Import settings to start the import. This process cannot be reversed!', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_import"><?php _ex('Import', 'option name', 'helpful'); ?></label>
				<textarea id="helpful_import" for="helpful_import" name="helpful_import" class="large-text code" rows="5"></textarea>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<?php do_action('helpful_export_settings_after'); ?>
	<?php submit_button(__('Import Settings', 'button name', 'helpful'), 'default'); ?>
</form>

<?php do_action('helpful_tab_system_after'); ?>

<script>
(function($) {
	$("#helpful_export").on("click", function(e) {
		const prevExport = $(this).val();
		$(this).val(prevExport).select();
	});
})(jQuery);
</script>