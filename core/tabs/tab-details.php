<?php
global $helpful;
$settings = $helpful['wp_editor'];
?>

<h2><?php _ex( 'Details', 'tab name', 'helpful' ); ?></h2>
    
<p><?php _ex( 'Here you can customize Helpful in detail. You can activate and deactivate many things here. Besides you can decide where Helpful appears and if Helpful appears. 
If you deactivate Helpful in the posts, you can output Helpful with the help of the shortcut code. For more information about the shortcodes, see Help.', 'tab description', 'helpful' ); ?></p>

<form method="post" action="options.php">

	<?php settings_fields( 'helpful-details-settings-group' ); ?>
	<?php do_settings_sections( 'helpful-details-settings-group' ); ?>

  <?php submit_button(__('Save Changes'), 'default'); ?>

  <?php do_action( 'helpful-details-settings-before'); ?>

  <div class="helpful-admin-panel">
    <button type="button" class="helpful-admin-panel-header">
      <span class="title"><?php echo esc_html_x('Post types', 'admin panel title', 'helpful'); ?></span>
      <span class="icon"></span>
    </button>
    <div class="helpful-admin-panel-content">

      <p class="description">Here you can select the post types where Helpful should appear. All selected post types get the table columns 
        for pro and contra, in the wp-admin. Private post types are displayed in light gray and are not always supported.</p>

      <div class="helpful-admin-group">
        <?php if( $post_types ) : foreach( $post_types as $post_type ) : ?>
        <?php $label = in_array( $post_type, $private_post_types ) ? sprintf('<span class="helpful-muted">%s</span>', $post_type) : $post_type; ?>
				<?php if( get_option('helpful_post_types') ) : ?>
          <?php $checked = in_array( $post_type, get_option('helpful_post_types') ) ? 'checked="checked"' : ''; ?>
					<label class="helpful-inline helpful-margin-right">
						<input type="checkbox" name="helpful_post_types[]" id="helpful_post_types[]" value="<?php echo $post_type; ?>" <?php echo $checked; ?>/>
						<?php echo $label; ?>
					</label>
				<?php else : ?>
					<label class="helpful-inline helpful-margin-right">
						<input type="checkbox" name="helpful_post_types[]" id="helpful_post_types[]" value="<?php echo $post_type; ?>"/>
						<?php echo $label; ?>
					</label>
				<?php endif; ?>
				<?php endforeach; endif; ?>
      </div>

    </div>
  </div><!-- .helpful-admin-panel -->

  <div class="helpful-admin-panel">
    <button type="button" class="helpful-admin-panel-header">
      <span class="title"><?php echo esc_html_x('General', 'admin panel title', 'helpful'); ?></span>
      <span class="icon"></span>
    </button>
    <div class="helpful-admin-panel-content">

      <div class="helpful-admin-group helpful-margin-bottom">
				<label>
				  <?php $value = get_option('helpful_exists_hide'); ?>
          <input id="helpful_exists_hide" type="checkbox" name="helpful_exists_hide" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Hide Helpful when voted', 'label', 'helpful' ); ?>
				</label>
      </div>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
				  <?php $value = get_option('helpful_count_hide'); ?>
          <input id="helpful_count_hide" type="checkbox" name="helpful_count_hide" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Hide vote counters', 'label', 'helpful' ); ?>
        </label>
      </div>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
				  <?php $value = get_option('helpful_credits'); ?>
          <input id="helpful_credits" type="checkbox" name="helpful_credits" <?php checked('on', $value); ?> /> 
          <?php printf(esc_html_x( 'Show credits to %s', 'label', 'helpful' ), '<a href="https://helpful-plugin.info" target="_blank">helpful-plugin.info</a>'); ?>
        </label>
      </div>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
          <?php $value = get_option('helpful_hide_in_content'); ?>
          <input id="helpful_hide_in_content" type="checkbox" name="helpful_hide_in_content" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Hide Helpful in post content', 'label', 'helpful' ); ?>
        </label>
      </div>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
          <?php $value = get_option('helpful_only_once'); ?>
          <input id="helpful_only_once" type="checkbox" name="helpful_only_once" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Vote only once on the whole website', 'label', 'helpful' ); ?>
        </label>
      </div>

      <div class="helpful-admin-group">
        <label>
          <?php $value = get_option('helpful_percentages'); ?>
          <input id="helpful_percentages" type="checkbox" name="helpful_percentages" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Show percentages in admin if possible', 'label', 'helpful' ); ?>
        </label>
      </div>

    </div>
  </div><!-- .helpful-admin-panel -->  

  <div class="helpful-admin-panel">
    <button type="button" class="helpful-admin-panel-header">
      <span class="title"><?php echo esc_html_x('Meta Box', 'admin panel title', 'helpful'); ?></span>
      <span class="icon"></span>
    </button>
    <div class="helpful-admin-panel-content">

      <p class="description">Here you can activate the Helpful Meta Box. With this meta box you can see in the current post how many votes you have 
        already received for the current post. You can also reset the votes for the current post.</p>

      <div class="helpful-admin-group">
        <label>
          <?php $value = get_option('helpful_metabox'); ?>
          <input id="helpful_metabox" type="checkbox" name="helpful_metabox" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Show Meta Box', 'label', 'helpful' ); ?>
        </label>
      </div>

    </div>
  </div><!-- .helpful-admin-panel -->  

  <div class="helpful-admin-panel">
    <button type="button" class="helpful-admin-panel-header">
      <span class="title"><?php echo esc_html_x('Dashboard Widget', 'admin panel title', 'helpful'); ?></span>
      <span class="icon"></span>
    </button>
    <div class="helpful-admin-panel-content">

      <p class="description">Here you can activate the Helpful Dashboard Widget. There you will find the total number of votes. 
        You will also see the most recently received and the most helpful and less helpful posts.</p>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
          <?php $value = get_option('helpful_widget'); ?>
          <input id="helpful_widget" type="checkbox" name="helpful_widget" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Hide Dashboard Widget', 'label', 'helpful' ); ?>
        </label>
      </div>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
          <?php $value = get_option('helpful_widget_pro'); ?>
          <input id="helpful_widget_pro" type="checkbox" name="helpful_widget_pro" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Show most helpful posts', 'label', 'helpful' ); ?>
        </label>
      </div>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
          <?php $value = get_option('helpful_widget_contra'); ?>
          <input id="helpful_widget_contra" type="checkbox" name="helpful_widget_contra" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Show least helpful posts', 'label', 'helpful' ); ?>
        </label>
      </div>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
          <?php $value = get_option('helpful_widget_pro_recent'); ?>
          <input id="helpful_widget_pro_recent" type="checkbox" name="helpful_widget_pro_recent" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Show recently helpful posts', 'label', 'helpful' ); ?>
        </label>
      </div>

      <div class="helpful-admin-group helpful-margin-bottom">
        <label>
          <?php $value = get_option('helpful_widget_contra_recent'); ?>
          <input id="helpful_widget_contra_recent" type="checkbox" name="helpful_widget_contra_recent" <?php checked('on', $value); ?> /> 
          <?php _ex( 'Show recently unhelpful posts', 'label', 'helpful' ); ?>
        </label>
      </div>

      <div class="helpful-admin-group">
        <label>
          <?php $value = esc_attr( get_option('helpful_widget_amount') ); ?>
          <input type="number" id="helpful_widget_amount" name="helpful_widget_amount" class="small-text" value="<?php echo $value; ?>"/>
          <?php _ex( 'Number of entries', 'label', 'helpful' ); ?>
        </label>
      </div>

    </div>
  </div><!-- .helpful-admin-panel -->

  <?php do_action( 'helpful-details-settings-after'); ?>

  <?php submit_button(__('Save Changes'), 'default'); ?>

</form>