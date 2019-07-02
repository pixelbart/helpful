<div class="helpful <?php echo $class; ?>">

  <?php if (false === $hidden) : ?>
  <div class="helpful-header">
    <h3><?php echo $helpful['heading']; ?></h3>
  </div>
  <?php endif; ?>

  <div class="helpful-content" role="alert">
    <span><?php echo $helpful['content']; ?></span>
  </div>

  <?php if (false === $hidden) : ?>
  <div class="helpful-controls">
    <div>
      <button class="helpful-pro" type="button" data-value="pro" data-post="<?php the_ID(); ?>" role="button">
        <?php echo $helpful['button_pro']; ?>
        <?php echo $helpful['counter'] ? sprintf('<span>%s</span>', $helpful['count_pro']) : ''; ?>
      </button>
    </div>
    <div>
      <button class="helpful-pro" type="button" data-value="contra" data-post="<?php the_ID(); ?>" role="button">
        <?php echo $helpful['button_contra']; ?>
        <?php echo $helpful['counter'] ? sprintf('<span>%s</span>', $helpful['count_contra']) : ''; ?>
      </button>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($helpful['credits'] && false === $hidden) : ?>
  <div class="helpful-footer">
    <?php printf(_x('Powered by %s', 'credits', 'helpful'), $helpful['credits_html']); ?>
  </div>
  <?php endif; ?>

</div>