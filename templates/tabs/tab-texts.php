<?php
/**
 * Callback for admin tab.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 2.0.0
 */
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = apply_filters( 'helpful_editor_settings', false );
$tags     = Helpers\Values::get_tags();
$tags     = '<code>' . implode( '</code>, <code>', $tags ) . '</code>';

do_action( 'helpful_tab_texts_before' );
?>

<h2><?php _ex( 'Texts', 'tab name', 'helpful' ); ?></h2>

<p><?php
/* translators: %s available tag comma list */
$text = esc_html_x( 'Most texts can be changed here. You can also leave fields blank to not display anything at this point. Available helpers: %s', 'tab description', 'helpful' );
printf( $text, $tags );
?></p>

<?php if ( ! Helper::is_feedback_disabled() ) : ?>
<p><?php
/* translators: %s feedback_form tag */
$text = esc_html_x( '%s should only be used in the texts after the user has voted. Otherwise it can lead to bugs and Helpful does not save feedback properly!', 'tab description', 'helpful' );
printf( $text, '<code>{feedback_form}</code>, <code>{feedback_toggle}</code>' );
?></p>
<?php endif; ?>

<form method="post" action="options.php">
	<?php settings_fields( 'helpful-texts-settings-group' ); ?>
	<?php do_settings_sections( 'helpful-texts-settings-group' ); ?>
	<?php submit_button( __( 'Save Changes' ), 'default' ); ?>
	<?php do_action( 'helpful_texts_settings_before' ); ?>

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x( 'Before voting', 'admin panel title', 'helpful' ); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_heading"><?php _ex( 'Headline', 'option name', 'helpful' ); ?></label>
				<input type="text" id="helpful_heading" name="helpful_heading" class="regular-text" value="<?php echo esc_attr( get_option( 'helpful_heading' ) ); ?>"/>
				<p class="description"><?php _ex('Here you can define your own headline.', 'option info', 'helpful'); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_content"><?php _ex( 'Content', 'option name', 'helpful' ); ?></label>
				<?php wp_editor( get_option( 'helpful_content' ), 'helpful_content', $settings ); ?>
				<p class="description"><?php _ex( 'Here you can define your own content.', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php echo esc_html_x( 'After voting', 'admin panel title', 'helpful' ); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_exists"><?php _ex( 'Already voted', 'option name', 'helpful' ); ?></label>
				<?php wp_editor( get_option( 'helpful_exists' ), 'helpful_exists', $settings ); ?>
				<p class="description"><?php _ex( 'This text will appear if the user has already voted.', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_after_pro"><?php _ex( 'After voting (pro)', 'option name', 'helpful' ); ?></label>
				<?php wp_editor( get_option( 'helpful_after_pro' ), 'helpful_after_pro', $settings ); ?>
				<p class="description"><?php _ex( 'The text that is displayed, after a positive vote (shortcodes <b>without Ajax</b> are also possible!)', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_after_contra"><?php _ex( 'After voting (contra)', 'option name', 'helpful' ); ?></label>
				<?php wp_editor( get_option( 'helpful_after_contra' ), 'helpful_after_contra', $settings ); ?>
				<p class="description"><?php _ex( 'The text that is displayed, after a negative vote (shortcodes <b>without Ajax</b> are also possible!)', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_after_fallback"><?php _ex( 'After voting (fallback)', 'option name', 'helpful' ); ?></label>
				<?php wp_editor( get_option( 'helpful_after_fallback' ), 'helpful_after_fallback', $settings ); ?>
				<p class="description"><?php _ex( 'This text is shown whenever the above texts cannot be displayed (shortcodes <b>without Ajax</b> are also possible!)', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex( 'Answer buttons', 'admin panel title', 'helpful' ); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_pro"><?php _ex( 'Button (pro)', 'option name', 'helpful' ); ?></label>
				<input type="text" id="helpful_pro" name="helpful_pro" class="regular-text" value="<?php echo esc_attr( get_option( 'helpful_pro' ) ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the pro button. You can use HTML to use e.g. Font Awesome.', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_contra"><?php _ex( 'Button (contra)', 'option name', 'helpful' ); ?></label>
				<input type="text" id="helpful_contra" name="helpful_contra" class="regular-text" value="<?php echo esc_attr( get_option( 'helpful_contra' ) ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the contra button. You can use HTML to use e.g. Font Awesome.', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<div class="helpful-admin-panel">

		<button type="button" class="helpful-admin-panel-header">
			<span class="title"><?php _ex( 'Admin columns', 'admin panel title', 'helpful' ); ?></span>
			<span class="icon"></span>
		</button><!-- .helpful-admin-panel-header -->

		<div class="helpful-admin-panel-content">

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_column_pro"><?php _ex( 'Column (pro)', 'option name', 'helpful' ); ?></label>
				<input type="text" id="helpful_column_pro" name="helpful_column_pro" class="regular-text" value="<?php echo esc_attr( get_option( 'helpful_column_pro' ) ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the pro column in the post edit list.', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<label class="helpful-block" for="helpful_column_contra"><?php _ex( 'Column (contra)', 'option name', 'helpful' ); ?></label>
				<input type="text" id="helpful_column_contra" name="helpful_column_contra" class="regular-text" value="<?php echo esc_attr( get_option( 'helpful_column_contra' ) ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the contra column in the post edit list.', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

			<div class="helpful-admin-group">
				<?php $value = get_option( 'helpful_column_feedback' ) ?: _x( 'Feedback', 'column name', 'helpful' ); ?>
				<label class="helpful-block" for="helpful_column_feedback"><?php _ex( 'Column (feedback)', 'option name', 'helpful' ); ?></label>
				<input type="text" id="helpful_column_feedback" name="helpful_column_feedback" class="regular-text" value="<?php echo esc_attr( $value ); ?>"/>
				<p class="description"><?php _ex( 'Here you can define your own text for the feedback column in the post edit list.', 'option info', 'helpful' ); ?></p>
			</div><!-- .helpful-admin-group -->

		</div><!-- .helpful-admin-panel-content -->
	</div><!-- .helpful-admin-panel -->

	<?php do_action( 'helpful_texts_settings_after' ); ?>
	<?php submit_button( __( 'Save Changes' ), 'default' ); ?>
</form>

<?php do_action( 'helpful_tab_helpful_after' ); ?>