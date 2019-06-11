<?php do_action('helpful-tab-start-before'); ?>

<div class="helpful-flex range">
  <div class="helpful-card">
    <div class="helpful-card_header">
      <form class="helpful-range-form helpful-flex helpful-flex-middle">
        <input type="hidden" name="action" value="helpful_range_stats">
        <?php wp_nonce_field('helpful_range_stats'); ?>
        <input class="helpful-date helpful-margin-right" type="text" name="from" placeholder="YYYY-MM-DD" value="<?php echo date_i18n('Y-m-d', strtotime('-5 days')); ?>">
        <span class="helpful-hide-mobile helpful-margin-right">to</span> 
        <input class="helpful-date helpful-margin-auto-right" type="text" name="to" placeholder="YYYY-MM-DD" value="<?php echo date_i18n('Y-m-d'); ?>">
        <select name="type" class="helpful-margin-left">
          <option value="default"><?php _ex('Default', 'admin chart type', 'helpful'); ?></option>
          <option value="stacked"><?php _ex('Stacked', 'admin chart type', 'helpful'); ?></option>
        </select>
      </form>
    </div>
    <div class="helpful-card_content helpful-range"></div>
  </div>
</div>

<?php do_action('helpful-tab-start-after'); ?>