<?php if( isset($feedback_text) ): ?>
<div class="feedback-note">
  <p><?php echo $feedback_text; ?></p>
</div>
<?php endif; ?>

<div class="group">
  <?php $label = get_option('helpful_feedback_label_message'); ?>
  <label for="message"><?php echo $label; ?> <req>*</req></label>
  <div class="control">
    <textarea name="message" id="message" required></textarea>
  </div>
</div>

<?php if( get_option('helpful_feedback_name') ): ?>
<div class="group">
  <?php $label = get_option('helpful_feedback_label_name'); ?>
  <label for="email"><?php echo $label; ?></label>
  <div class="control">
    <input type="text" name="fields[name]" id="name">
  </div>
</div>
<?php endif; ?>

<?php if( get_option('helpful_feedback_email') ): ?>
<div class="group">
  <?php $label = get_option('helpful_feedback_label_email'); ?>
  <label for="email"><?php echo $label; ?></label>
  <div class="control">
    <input type="email" name="fields[email]" id="email">
  </div>
</div>
<?php endif; ?>

<?php $label = get_option('helpful_feedback_label_submit'); ?>
<button type="submit" role="button"><?php echo $label; ?></button>