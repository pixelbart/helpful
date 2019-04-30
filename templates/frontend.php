<?php
/**
 * Frontent Template
 *
 * @author  Devhats
 */

// Get helpful helpers
$helpful = apply_filters( 'helpful_helpers', [] );

?>

<?php if( $helpful['exists'] ) : if( !$helpful['exists-hide'] ) : ?>

<div class="<?php echo $helpful['class']; ?>">

  <?php if( $helpful['exists-text'] ): ?>
	<div class="helpful-exists"><?php echo $helpful['exists-text']; ?></div>
  <?php endif; ?>

	<?php echo $helpful['credits']; ?>

</div>

<?php endif; else : ?>

<div class="<?php echo $helpful['class']; ?>">

  <?php if( $helpful['heading'] ): ?>
	<div class="helpful-heading"><?php echo $helpful['heading']; ?></div>
  <?php endif; ?>

  <?php if( $helpful['content'] ): ?>
	<div class="helpful-content"><?php echo $helpful['content']; ?></div>
  <?php endif; ?>

	<div class="helpful-controls">
		<?php echo $helpful['button-pro']; ?>
		<?php echo $helpful['button-contra']; ?>
	</div>

	<?php echo $helpful['credits']; ?>

</div>

<?php endif; ?>
