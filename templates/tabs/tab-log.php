<?php
/**
 * Callback for admin tab.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.1.5
 */
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'helpful_tab_log_before' );
?>

<h2><?php _ex( 'Log', 'tab name', 'helpful' ); ?></h2>

<p><?php _ex( 'Here you can view and search all Helpful votes. If, for example, the user record is 0, performing maintenance in the Helpful System Settings will help. The maintenance then automatically deletes incorrect entries.', 'tab description', 'helpful' ); ?></p>

<div class="helpful-flex">
	<div class="helpful-card">
		<div class="helpful-card_content helpful-padding-remove">
			<div class="table-container">
				<table id="helpful-table-log" class="helpful-table display" width="100%">
					<thead>
						<tr>
							<th><?php echo esc_html_x( 'Post', 'log table head', 'helpful' ); ?></th>
							<th><?php echo esc_html_x( 'Title', 'log table head', 'helpful' ); ?></th>
							<th><?php echo esc_html_x( 'Pro', 'log table head', 'helpful' ); ?></th>
							<th><?php echo esc_html_x( 'Contra', 'log table head', 'helpful' ); ?></th>
							<th><?php echo esc_html_x( 'User', 'log table head', 'helpful' ); ?></th>
							<th><?php echo esc_html_x( 'Time', 'log table head', 'helpful' ); ?></th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'helpful_tab_log_after' ); ?>