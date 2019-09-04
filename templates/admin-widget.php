
<form class="helpful-widget-form">

  <input type="hidden" name="action" value="helpful_widget_stats">
  <?php wp_nonce_field('helpful_widget_stats'); ?>

  <div class="helpful-margin-right-small">
    <select name="range">
      <option value="today"><?php _e('Today', 'helpful'); ?></option>
      <option value="yesterday"><?php _e('Yesterday', 'helpful'); ?></option>
      <option value="week"><?php _e('Week', 'helpful'); ?></option>
      <option value="month"><?php _e('Month', 'helpful'); ?></option>
      <option value="year"><?php _e('Year', 'helpful'); ?></option>
      <option value="total" selected><?php _e('Total', 'helpful'); ?></option>
    </select>
  </div>

  <?php if (!empty($years)) : ?>
  <div class="helpful-margin-right-small" hidden>
    <select name="year">
      <?php foreach( $years as $year ): ?>
      <option><?php echo $year; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>

  <div class="helpful-margin-left-auto">
    <button type="button" class="button button-default refresh">
      <?php _e('Refresh', 'helpful'); ?>
    </button>
  </div>

</form>

<div class="helpful-widget-content">
  <div class="loader"><i class="dashicons dashicons-image-rotate"></i></div>
</div>

<div class="helpful-widget-panels">

  <?php if (!empty(Helpful_Helper_Stats::getMostHelpful()) && get_option('helpful_widget_pro')) : ?>
  <div class="helpful-widget-panel">
    <button type="button">
      <?php _ex('Most helpful', 'widget headline', 'helpful'); ?>
      <span class="icon"></span>
    </button>
    <ul>
      <?php foreach( Helpful_Helper_Stats::getMostHelpful() as $post ): ?>
      <li>
        <div><a href="<?php echo $post['url']; ?>" target="_blank"><?php echo $post['name']; ?></a></div>
        <div><?php printf( esc_html_x( '%d helpful / %d not helpful (%d%% helpful in total)', 'widget item info', 'helpful' ), $post['pro'], $post['contra'], $post['percentage'] ); ?></div>
        <?php echo ! get_option( 'helpful_widget_hide_publication' ) ? $post['time'] : ''; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if (!empty(Helpful_Helper_Stats::getLeastHelpful()) && get_option('helpful_widget_contra')) : ?>
  <div class="helpful-widget-panel">
    <button type="button">
      <?php _ex('Least helpful', 'widget headline', 'helpful'); ?>
      <span class="icon"></span>
    </button>
    <ul>
      <?php foreach ( Helpful_Helper_Stats::getLeastHelpful() as $post ) : ?>
      <li>
        <div><a href="<?php echo $post['url']; ?>" target="_blank"><?php echo $post['name']; ?></a></div>
        <div><?php printf( esc_html_x( '%d helpful / %d not helpful (%d%% helpful in total)', 'widget item info', 'helpful' ), $post['pro'], $post['contra'], $post['percentage'] ); ?></div>
        <?php echo ! get_option( 'helpful_widget_hide_publication' ) ? $post['time'] : ''; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if (!empty(Helpful_Helper_Stats::getRecentlyPro()) && get_option('helpful_widget_pro_recent')) : ?>
  <div class="helpful-widget-panel">
    <button type="button">
      <?php _ex('Recently helpful', 'widget headline', 'helpful'); ?>
      <span class="icon"></span>
    </button>
    <ul>
      <?php foreach ( Helpful_Helper_Stats::getRecentlyPro() as $post ) : ?>
      <li>
        <div><a href="<?php echo $post['url']; ?>" target="_blank"><?php echo $post['name']; ?></a></div>
        <?php echo $post['time']; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if (!empty(Helpful_Helper_Stats::getRecentlyContra()) && get_option('helpful_widget_contra_recent')) : ?>
  <div class="helpful-widget-panel">
    <button type="button">
      <?php _ex('Recently unhelpful', 'widget headline', 'helpful'); ?>
      <span class="icon"></span>
    </button>
    <ul>
      <?php foreach ( Helpful_Helper_Stats::getRecentlyContra() as $post ) : ?>
      <li>
        <div><a href="<?php echo $post['url']; ?>" target="_blank"><?php echo $post['name']; ?></a></div>
        <?php echo $post['time']; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if (Helpful_Helper_Feedback::getFeedbackItems() && get_option('helpful_feedback_widget')) : ?> 
  <div class="helpful-widget-panel">
    <button type="button">
      <?php echo esc_html_x('Recent Feedback', 'widget headline', 'helpful'); ?>
      <span class="icon"></span>
    </button>
    <ul>
      <?php foreach ( Helpful_Helper_Feedback::getFeedbackItems() as $feedback ) : $feedback = Helpful_Helper_Feedback::getFeedback($feedback); ?>
      <li>
        <a href="<?php echo admin_url('admin.php?page=helpful_feedback'); ?>">
          <?php printf(__('%s on %s', 'helpful'), $feedback->name, $feedback->post->post_title); ?>
        </a><br><?php echo $feedback->time; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

</div>

<div class="helpful-widget-footer">
  <?php echo implode('', $links); ?>

  <?php 
  $total  = 0;
  $total += (int) Helpful_Helper_Stats::getProAll();
  $total += (int) Helpful_Helper_Stats::getContraAll();
  ?>
  <div class="helpful-widget-total">
    <?php printf( esc_html__( '%d Votes', 'helpful' ), (int) $total ); ?>
  </div>
</div>