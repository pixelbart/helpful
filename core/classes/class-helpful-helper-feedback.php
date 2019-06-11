<?php
/**
 * Helper for receiving stored feedback, feedback informations,
 * user avatars...
 * @since 4.0.0
 */
class Helpful_Helper_Feedback {

  public static $green = '#88c057';
  public static $red = '#ed7161';

  public static function getFeedback($entry) {
    
    $post = get_post($entry->post_id);

    $feedback = [];

    $feedback['id'] = $entry->id;
    $feedback['name'] = __('Anonymous', 'helpful');
    $feedback['message'] = $entry->message;
    $feedback['pro'] = $entry->pro;
    $feedback['contra'] = $entry->contra;
    $feedback['post'] = $post;
    $feedback['time'] = sprintf( 
      __('Submitted %s ago', 'helpful'), 
      human_time_diff( strtotime($entry->time) ) 
    );
    
    if( $entry->fields ) {      
      $fields = [];      
      $items = maybe_unserialize( $entry->fields );
      if( is_array($items) ) {
        foreach( $items as $label => $value ) {
          $feedback['fields'][$label] = $value;
        }
      }
    }

    $feedback['avatar'] = self::getAvatar();

    if( isset($feedback['fields']['email']) && "" !== $feedback['fields']['email'] ) {
      $feedback['avatar'] = self::getAvatar($feedback['fields']['email']);
    }

    if( isset($feedback['fields']['name']) && "" !== $feedback['fields']['name'] ) {
      $feedback['name'] = $feedback['fields']['name'];
    }

    $feedback = apply_filters('helpful_admin_feedback_item', $feedback, $entry);

    return json_decode(json_encode($feedback));
  }

  public static function getAvatar($email = null, $size = 55) {
    $default = plugins_url( 'core/assets/images/avatar.jpg', HELPFUL_FILE );

    if( get_option('helpful_feedback_gravatar') ) {
      if( !is_null($email) ) {
        return get_avatar( $email, $size, $default );
      }
    }

    return sprintf('<img src="%1$s" height="%2$s" width="%2$s" alt="no avatar">', $default, $size);
  }

  public static function getFeedbackItems($limit = null) {
    if( is_null($limit) ) {
      $limit = absint(get_option('helpful_widget_amount'));
    }

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful_feedback';

    $query   = "SELECT * FROM $helpful ORDER BY time DESC LIMIT %d";
    $query   = $wpdb->prepare($query, $limit);
    $results = $wpdb->get_results($query);

    if( $results ) {
      return $results;
    }

    return false;
  }
}