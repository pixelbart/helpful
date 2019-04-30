<?php
/**
 * Options page
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap">
	
	<h1><?php _e('Helpful', 'helpful'); ?></h1>	
	
	<div class="helpful-row">
		
		<div class="helpful-col-9">

			<div class="helpful-tab-wrapper">

				<ul class="helpful-tabs">
					
					<?php do_action('helpful_tabs'); ?>

				</ul><!-- end .helpful-tabs -->

				<div class="helpful-tab-content">
					
					<?php do_action('helpful_tabs_content'); ?>

				</div><!-- end .helpful-tab-content -->

			</div><!-- end .helpful-tab-wrapper -->
			
		</div>
		
		<div class="helpful-col-3">

			<div class="helpful-informations">
				
				<?php do_action( 'helpful_sidebar' ); ?>
				
			</div><!-- end .helpful-informations -->
			
		</div>
	
	</div><!-- end .helpful-row -->
			
</div>
