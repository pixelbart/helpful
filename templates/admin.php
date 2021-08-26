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
		<h1><img src="<?php echo Helper::get_logo(); ?>"> <?php _e( 'Helpful', 'helpful' ); ?></h1>
	</div><!-- .helpful-admin-logo -->

	<nav class="helpful-admin-tabs">
		<?php foreach ($tabs as $tab) : ?>
			<a href="<?php echo $tab['href']; ?>" class="helpful-admin-tab <?php echo $tab['class']; ?>">
				<?php echo $tab['name']; ?>
			</a>
		<?php endforeach; ?>
	</nav><!-- .helpful-admin-tabs -->

	<select class="helpful-admin-nav linked">
		<?php foreach ($tabs as $tab) : ?>
			<option value="<?php echo $tab['href']; ?>" <?php echo (isset($tab['attr'])) ? $tab['attr'] : ''; ?>><?php echo $tab['name']; ?></a>
		<?php endforeach; ?>
	</select><!-- .helpful-admin-nav -->

</div><!-- .helpful-admin-header -->
<div class="helpful-admin-content">

	<div class="helpful-admin-container">
		<?php do_action('helpful_notices'); ?>
		<?php do_action('helpful_tabs_content'); ?>
	</div><!-- .helpful-admin-container -->

</div><!-- .helpful-admin-content -->