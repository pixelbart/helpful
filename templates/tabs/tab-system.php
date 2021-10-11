<?php
/**
 * @package Helpful
 * @version 4.4.59
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
$separators = [';', ','];
$separators = apply_filters('helpful_export_separators', $separators);

do_action('helpful_tab_system_before');
?>

<h2><?php _ex('System', 'tab name', 'helpful'); ?></h2>

<p><?php _ex('Here you will find settings that Helpful can reset, or affect. Among other things you can set your own timezone, reset Helpful and set whether users can vote for a post more than once.', 'tab description', 'helpful'); ?></p>

<form method="post" action="options.php">
	<?php settings_fields('helpful-system-settings-group'); ?>
	<?php do_settings_sections('helpful-system-settings-group'); ?>
	<?php submit_button(__('Save Changes'), 'default'); ?>
	<?php do_action('helpful_system_settings_before'); ?>

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex('Cache', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">
			<p class="description"><?php _ex('Helpful stores the data for a specified retention period using WordPress Transients API in between. Here you can set how long something should be cached and if something should be saved.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_caching', 'off', 'esc_attr'); ?>
					<input id="helpful_caching" type="checkbox" name="helpful_caching" <?php checked('on', $value); ?> />
					<?php _ex('Enable Caching', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">

				<label for="helpful_caching_time" class="helpful-block">
					<?php _ex('Cache storage time', 'label', 'helpful'); ?>
				</label>

				<?php $times = Helpers\Cache::get_cache_times(); ?>
				<?php $value = $options->get_option('helpful_caching_time', '', 'esc_attr'); ?>

				<select id="helpful_caching_time" name="helpful_caching_time" class="regular-text">
					<?php foreach ($times as $id => $label) : ?>
						<?php if ($value === $id) : ?>
						<option value="<?php echo esc_attr($id); ?>" selected><?php echo esc_html($label); ?></option>
						<?php else : ?>
						<option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex('Miscellaneous', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel -->

		<div class="helpful-admin-panel-content">
			<p class="description"><?php _ex( "Here you'll find settings that might be useful, but didn't fit anywhere else or cause confusion. Note that if you allow users to vote more than once, this means the individual posts. Users can then vote more than once for a post.", 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_timezone"><?php _ex('Custom timezone', 'option name', 'helpful'); ?></label>
				<?php $value = $options->get_option('helpful_timezone', date_default_timezone_get(), 'esc_attr'); ?>
				<input type="text" class="regular-text code" name="helpful_timezone" value="<?php echo esc_attr($value); ?>">
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_multiple', 'off', 'esc_attr'); ?>
					<input id="helpful_multiple" type="checkbox" name="helpful_multiple" <?php checked('on', $value); ?> />
					<?php _ex('Enable to allow users to vote more than once in individual posts', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_notes', 'off', 'esc_attr'); ?>
					<input id="helpful_notes" type="checkbox" name="helpful_notes" <?php checked('on', $value); ?> />
					<?php _ex('Check to completely disable admin notes for Helpful', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_plugin_first', 'off', 'esc_attr'); ?>
					<input id="helpful_plugin_first" type="checkbox" name="helpful_plugin_first" <?php checked('on', $value); ?> />
					<?php _ex('Select so that Helpful is always loaded first', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_classic_editor', 'off', 'esc_attr'); ?>
					<input id="helpful_classic_editor" type="checkbox" name="helpful_classic_editor" <?php checked('on', $value); ?> />
					<?php _ex('Activate the classic editor and deactivate the block editor', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_disable_frontend_nonce', 'off', 'esc_attr'); ?>
					<input id="helpful_disable_frontend_nonce" type="checkbox" name="helpful_disable_frontend_nonce" <?php checked('on', $value); ?> />
					<?php _ex('Disable frontend nonce (not recommended)', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label>
					<?php $value = $options->get_option('helpful_disable_feedback_nonce', 'off', 'esc_attr'); ?>
					<input id="helpful_disable_feedback_nonce" type="checkbox" name="helpful_disable_feedback_nonce" <?php checked('on', $value); ?> />
					<?php _ex('Disable feedback nonce (not recommended)', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex('Cookies & Sessions', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel -->

		<div class="helpful-admin-panel-content">
			<p class="description"><?php _ex( "Here you can make settings that affect cookies and sessions. Starting with PHP 7.3, cookies are set with Samesite Strict. In previous versions of PHP, this can cause problems, so Samesite is only used on PHP 7.3 or higher.", 'admin panel description', 'helpful'); ?></p>
			<p class="description"><?php _ex( "Note that if you disable sessions and cookies cannot be set, an error will occur and your site will not work. So you should know what you are doing when you change this setting.", 'admin panel description', 'helpful'); ?></p>
			<p class="description"><?php _ex( "If your server has problems with PHP sessions, you can try the plugin from Pantheon: ", 'admin panel description', 'helpful'); ?> <a href="https://wordpress.org/plugins/wp-native-php-sessions/" target="_blank">WordPress Native PHP Sessions</a></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_user_random', 'off', 'esc_attr'); ?>
					<input id="helpful_user_random" type="checkbox" name="helpful_user_random" <?php checked('on', $value); ?> />
					<?php _ex('Disable cookies and sessions. Users can vote as often as they want, as it is no longer possible to check whether a user has already voted.', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group helpful-margin-bottom">
				<label>
					<?php $value = $options->get_option('helpful_sessions_false', 'off', 'esc_attr'); ?>
					<input id="helpful_sessions_false" type="checkbox" name="helpful_sessions_false" <?php checked('on', $value); ?> />
					<?php _ex('Disable sessions. Always uses cookies to identify the user.', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<?php if (70300 <= PHP_VERSION_ID) : ?>
			<div class="helpful-admin-group">
				<label for="helpful_cookies_samesite" class="helpful-block">
					<?php _ex('Cookie Samesite', 'label', 'helpful'); ?>
				</label>

				<?php $option = ($options->get_option('helpful_cookies_samesite')) ? $options->get_option('helpful_cookies_samesite', 'Strict', 'esc_attr') : 'Strict'; ?>
				<?php $values = Helper::get_samesite_options(); ?>

				<select id="helpful_cookies_samesite" name="helpful_cookies_samesite" class="regular-text">
					<?php foreach ($values as $value) : ?>
						<?php if ($value === $option) : ?>
						<option selected><?php echo esc_html($value); ?></option>
						<?php else : ?>
						<option><?php echo esc_html($value); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>

			</div><!-- .helpful-admin-group -->
			<?php endif; ?>
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex('Export', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel -->

		<div class="helpful-admin-panel-content">

			<p class="description"><?php _ex('Here you can make settings concerning the export of the feedback and the logs.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-block" for="helpful_export_separator"><?php _ex('Separator (CSV)', 'option name', 'helpful'); ?></label>
				<?php $value = $options->get_option('helpful_export_separator', ';', 'esc_attr'); ?>
				<select class="regular-text code" name="helpful_export_separator">
					<?php foreach ($separators as $separator) : ?>
						<?php if ($separator === $value) : ?>
						<option selected><?php echo esc_html($separator); ?></option>
						<?php else: ?>
						<option><?php echo esc_html($separator); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex('Maintenance', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content helpful_maintenance">
			<p class="description"><?php _ex('Here you can maintain the plugin. The database will be checked if there are any errors. In addition, feedback from older versions is moved into the database and old entries are deleted. This feature will also be extended in the future if there is something new. You can find more about this in the changelogs.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful_response" hidden></div>

			<div class="helpful-admin-group">
				<button type="button" class="button button-default">
					<?php esc_html_e('Perform maintenance', 'helpful'); ?>
				</button>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">
		<button type="button" class="helpful-admin-panel-header">
			<span class="title helpful-danger"><?php _ex('Reset', 'admin panel title', 'helpful'); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel -->

		<div class="helpful-admin-panel-content">

			<p class="description"><?php _ex('Here you can reset Helpful. This affects all entries in the database as well as the feedback. Settings are not affected. This action is irreversible, so be careful with it. If you want to reset individual posts, you only have to activate the meta box under Details. Then switch to the post and reset Helpful there.', 'admin panel description', 'helpful'); ?></p>

			<div class="helpful-admin-group helpful-margin-bottom">
				<label class="helpful-danger">
					<?php $value = $options->get_option('helpful_uninstall', 'off', 'esc_attr'); ?>
					<input id="helpful_uninstall" type="checkbox" name="helpful_uninstall" <?php checked('on', $value); ?> />
					<?php _ex('Reset Helpful', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-danger">
					<?php $value = $options->get_option('helpful_uninstall_feedback', 'off', 'esc_attr'); ?>
					<input id="helpful_uninstall_feedback" type="checkbox" name="helpful_uninstall_feedback" <?php checked('on', $value); ?> />
					<?php _ex('Reset Feedback', 'label', 'helpful'); ?>
				</label>
			</div><!-- .helpful-admin-group -->
		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<?php do_action('helpful_system_settings_after'); ?>
	<?php submit_button(__('Save Changes'), 'default'); ?>
</form>

<?php do_action('helpful_tab_system_after'); ?>