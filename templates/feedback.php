<?php
/**
 * Feedback Frontend Template
 *
 * @author  Devhats
 */

$nonce = wp_create_nonce('helpful_feedback');
$feedback_text = esc_html_x('Thank you very much. Please write us your opinion, so that we can improve ourselves.', 'form user note', 'helpful');
$feedback_button = esc_html_x('Send Feedback', 'button text', 'helpful');

if( 'pro' == $args['type'] && get_option('helpful_feedback_message_pro') ) {
  $feedback_text = get_option('helpful_feedback_message_pro');
}

if( 'contra' == $args['type'] && get_option('helpful_feedback_message_contra') ) {
  $feedback_text = get_option('helpful_feedback_message_contra');
}
?>

<div class="helpful-feedback" data-type="<?=$args['type']?>" data-post="<?=$args['post_id']?>" data-nonce="<?=$nonce?>">
  <?php if( $feedback_text ) printf('<p>%s</p>', $feedback_text); ?>
  <textarea name="helpful_feedback"></textarea>
  <button type="button"><?php echo $feedback_button; ?></button>
</div>
