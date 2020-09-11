<?php
/**
 * Callback for admin widget.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 1.0.0
 */
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form class="helpful-widget-form">
	<input type="hidden" name="action" value="helpful_widget_stats">
	<?php wp_nonce_field( 'helpful_widget_stats', 'helpful_widget_stats_nonce' ); ?>

	<div class="helpful-margin-right-small">
		<select name="range">
			<option value="today"><?php esc_html_e( 'Today', 'helpful' ); ?></option>
			<option value="yesterday"><?php esc_html_e( 'Yesterday', 'helpful' ); ?></option>
			<option value="week"><?php esc_html_e( 'Week', 'helpful' ); ?></option>
			<option value="month"><?php esc_html_e( 'Month', 'helpful' ); ?></option>
			<option value="year"><?php esc_html_e( 'Year', 'helpful' ); ?></option>
			<option value="total" selected><?php esc_html_e( 'Total', 'helpful' ); ?></option>
		</select>
	</div>

	<?php if ( ! empty( $years ) ) : ?>
	<div class="helpful-margin-right-small" hidden>
		<select name="year">
			<?php foreach ( $years as $year ) : ?>
			<option><?php echo esc_html( $year ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php endif; ?>

	<div class="helpful-margin-left-auto">
		<button type="button" class="button button-default refresh">
			<?php esc_html_e( 'Refresh', 'helpful' ); ?>
		</button>
	</div>

</form>

<div class="helpful-widget-content">
	<div class="loader"><i class="dashicons dashicons-update"></i></div>
</div>

<div class="helpful-widget-panels">

	<?php if ( ! empty( Helpers\Stats::get_most_helpful() ) && get_option( 'helpful_widget_pro' ) ) : ?>
	<div class="helpful-widget-panel">

		<button type="button">
			<?php _ex( 'Most helpful', 'widget headline', 'helpful' ); ?>
			<span class="icon"></span>
		</button>

		<ul>
			<?php foreach ( Helpers\Stats::get_most_helpful() as $post ) : ?>
			<li>
				<div><a href="<?php echo esc_url( $post['url'] ); ?>" target="_blank"><?php echo esc_html( $post['name'] ); ?></a></div>
				<div><?php printf( esc_html_x( '%d helpful / %d not helpful (%s%% helpful in total)', 'widget item info', 'helpful' ), intval( $post['pro'] ), intval( $post['contra'] ), $post['percentage'] ); ?></div>
				<?php echo ! get_option( 'helpful_widget_hide_publication' ) ? esc_html( $post['time'] ) : ''; ?>
			</li>
			<?php endforeach; ?>
		</ul>

	</div>
	<?php endif; ?>

	<?php if ( ! empty( Helpers\Stats::get_least_helpful() ) && get_option( 'helpful_widget_contra' ) ) : ?>
	<div class="helpful-widget-panel">

		<button type="button">
			<?php _ex( 'Least helpful', 'widget headline', 'helpful' ); ?>
			<span class="icon"></span>
		</button>

		<ul>
			<?php foreach ( Helpers\Stats::get_least_helpful() as $post ) : ?>
			<li>
				<div><a href="<?php echo esc_url( $post['url'] ); ?>" target="_blank"><?php echo esc_html( $post['name'] ); ?></a></div>
				<div><?php printf( esc_html_x( '%d helpful / %d not helpful (%s%% helpful in total)', 'widget item info', 'helpful' ), intval( $post['pro'] ), intval( $post['contra'] ), $post['percentage'] ); ?></div>
				<?php echo ! get_option( 'helpful_widget_hide_publication' ) ? esc_html( $post['time'] ) : ''; ?>
			</li>
			<?php endforeach; ?>
		</ul>

	</div>
	<?php endif; ?>

	<?php if ( ! empty( Helpers\Stats::get_recently_pro() ) && get_option( 'helpful_widget_pro_recent' ) ) : ?>
	<div class="helpful-widget-panel">

		<button type="button">
			<?php _ex( 'Recently helpful', 'widget headline', 'helpful' ); ?>
			<span class="icon"></span>
		</button>

		<ul>
		<?php foreach ( Helpers\Stats::get_recently_pro() as $post ) : ?>
			<li>
				<div><a href="<?php echo esc_url( $post['url'] ); ?>" target="_blank"><?php echo esc_html( $post['name'] ); ?></a></div>
				<?php echo esc_html( $post['time'] ); ?>
			</li>
		<?php endforeach; ?>
		</ul>

	</div>
	<?php endif; ?>

	<?php if ( ! empty( Helpers\Stats::get_recently_contra() ) && get_option( 'helpful_widget_contra_recent' ) ) : ?>
	<div class="helpful-widget-panel">

		<button type="button">
			<?php _ex( 'Recently unhelpful', 'widget headline', 'helpful' ); ?>
			<span class="icon"></span>
		</button>

		<ul>
			<?php foreach ( Helpers\Stats::get_recently_contra() as $post ) : ?>
			<li>
				<div><a href="<?php echo esc_url( $post['url'] ); ?>" target="_blank"><?php echo esc_html( $post['name'] ); ?></a></div>
				<?php echo esc_html( $post['time'] ); ?>
			</li>
			<?php endforeach; ?>
		</ul>

	</div>
	<?php endif; ?>

	<?php if ( Helpers\Feedback::get_feedback_items() && get_option( 'helpful_feedback_widget' ) ) : ?>
	<div class="helpful-widget-panel">

		<button type="button">
			<?php echo esc_html_x( 'Recent Feedback', 'widget headline', 'helpful' ); ?>
			<span class="icon"></span>
		</button>

		<ul>
			<?php foreach ( Helpers\Feedback::get_feedback_items() as $feedback ) : ?>
				<?php $feedback = Helpers\Feedback::get_feedback( $feedback ); ?>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=helpful_feedback' ) ); ?>">
				<?php printf( __( '%s on %s', 'helpful' ), esc_html( $feedback->name ), esc_html( $feedback->post->post_title ) ); ?>
				</a><br><?php echo esc_html( $feedback->time ); ?>
			</li>
			<?php endforeach; ?>
		</ul>

	</div>
	<?php endif; ?>
</div>

<div class="helpful-widget-footer">
	<?php
	echo implode( '', $links );

	$total  = 0;
	$total += (int) Helpers\Stats::get_pro_all();
	$total += (int) Helpers\Stats::get_contra_all();
	?>
	<div class="helpful-widget-total">
		<?php printf( esc_html__( '%d Votes', 'helpful' ), intval( $total ) ); ?>
	</div>
</div>