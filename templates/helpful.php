<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="helpful <?php echo esc_attr( $helpful['shortcode_class'] ); ?>">

	<?php if ( false === $helpful['shortcode_hidden'] ) : ?>
	<div class="helpful-header">
		<?php echo apply_filters( 'helpful_headline_html', '<h3 class="helpful-headline">' . $helpful['heading'] . '</h3>' ); ?>
	</div><!-- .helpful-header -->
	<?php endif; ?>

	<div class="helpful-content" role="alert">
		<span><?php echo $helpful['content']; ?></span>
	</div><!-- .helpful-content -->

	<?php if ( false === $helpful['shortcode_hidden'] && !(1 === $helpful['button_pro_disabled'] && 1 === $helpful['button_contra_disabled']) ) : ?>
	<div class="helpful-controls">

		<?php if (1 !== $helpful['button_pro_disabled']) : ?>
		<div>
			<button class="helpful-pro helpful-button" type="button" data-value="pro" data-post="<?php echo $helpful['post_id']; ?>" role="button">
				<?php echo $helpful['button_pro']; ?>
				<?php echo $helpful['counter'] ? sprintf( '<span class="helpful-counter">%s</span>', $helpful['count_pro'] ) : ''; ?>
			</button>
		</div>
		<?php endif; ?>

		<?php if (1 !== $helpful['button_contra_disabled']) : ?>
		<div>
			<button class="helpful-contra helpful-button" type="button" data-value="contra" data-post="<?php echo $helpful['post_id']; ?>" role="button">
				<?php echo $helpful['button_contra']; ?>
				<?php echo $helpful['counter'] ? sprintf( '<span class="helpful-counter">%s</span>', $helpful['count_contra'] ) : ''; ?>
			</button>
		</div>
		<?php endif; ?>

	</div><!-- .helpful-controls -->
	<?php endif; ?>

	<?php if ( 'on' === $helpful['credits'] && false === $helpful['shortcode_hidden'] ) : ?>
	<div class="helpful-footer">
		<?php
		/* translators: %s = credits link */
		printf( _x( 'Powered by %s', 'credits', 'helpful' ), $helpful['credits_html'] );
		?>
	</div><!-- .helpful-footer -->
	<?php endif; ?>

</div><!-- .helpful -->
