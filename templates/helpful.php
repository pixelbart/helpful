<div class="helpful <?php echo $class; ?>">

  <div class="helpful-header" <?php echo $hidden; ?>>
    <h3><?php echo $helpful['heading']; ?></h3>
  </div>

  <div class="helpful-content" role="alert">
    <span><?php echo $helpful['content']; ?></span>
  </div>

  <div class="helpful-controls" <?php echo $hidden; ?>>
    <div>
      <button class="helpful-pro" type="button" data-value="pro" data-post="<?php the_ID(); ?>" role="button">
        <?php echo $helpful['button_pro']; ?>
        <?php echo $helpful['counter'] ? sprintf('<span>%s</span>', $helpful['count_pro']) : ''; ?>
      </button>
    </div>
    <div>
      <button class="helpful-pro" type="button" data-value="contra" data-post="<?php the_ID();?>" role="button">
        <?php echo $helpful['button_contra']; ?>
        <?php echo $helpful['counter'] ? sprintf('<span>%s</span>', $helpful['count_contra']) : ''; ?>
      </button>
    </div>
  </div>

  <?php if( $helpful['credits'] ): ?>
  <div class="helpful-footer" <?php echo $hidden; ?>>
    <?php printf( _x('Powered by %s', 'credits', 'helpful'), $helpful['credits_html'] ); ?>
  </div>
  <?php endif; ?>

</div>