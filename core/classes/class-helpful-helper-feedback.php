<?php
/**
 * Helper for receiving stored feedback, feedback informations,
 * user avatars...
 * @since 4.0.0
 */
class Helpful_Helper_Feedback {

  /**
   * Get feedback data by post object
   * @param object $entry post object
   * @return array
   */
  public static function getFeedback($entry) {
    
    $post = get_post($entry->post_id);
    $time = strtotime($entry->time);

    $feedback = [];

    $feedback['id'] = $entry->id;
    $feedback['name'] = __('Anonymous', 'helpful');
    $feedback['message'] = $entry->message;
    $feedback['pro'] = $entry->pro;
    $feedback['contra'] = $entry->contra;
    $feedback['post'] = $post;
    $feedback['time'] = sprintf( 
      __('Submitted %s ago', 'helpful'), 
      human_time_diff($time, current_time('timestamp'))
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

  /**
   * Get avatar or default helpful avatar by email
   * @param string $email
   * @param integer $size
   * @return void
   */
  public static function getAvatar($email = null, $size = 55) {
    $default = plugins_url( 'core/assets/images/avatar.jpg', HELPFUL_FILE );

    if( get_option('helpful_feedback_gravatar') ) {
      if( !is_null($email) ) {
        return get_avatar( $email, $size, $default );
      }
    }

    return sprintf('<img src="%1$s" height="%2$s" width="%2$s" alt="no avatar">', $default, $size);
  }

  /**
   * Get feedback items
   * @param integer $limit posts per page
   * @return object
   */
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

  /**
   * Insert feedback into database
   * @param $args
   * @return int
   */
  public static function insertFeedback() {
    global $wpdb;

    $fields = [];
    $pro = 0;
    $contra = 0;
    $message = null;

    // check if fields exists
    if( isset($_REQUEST['fields']) ) {
      foreach( $_REQUEST['fields'] as $key => $value ) {
        $fields[$key] = sanitize_text_field($value);
      }

      // here you can manipulate the fields
      $fields = apply_filters('helpful-feedback-submit-fields', $fields);
    }

    // check if message exists
    if( isset($_REQUEST['message']) ) {
      $message = sanitize_text_field($_REQUEST['message']);

      // here you can manipulate the message
      $message = apply_filters('helpful-feedback-submit-fields', $message);
    }

    // checks feedback type
    if( isset($_REQUEST['type']) ) {
      $type = sanitize_text_field($_REQUEST['type']);

      if( 'pro' == $type ) {
        $pro = 1;
      } 
      elseif( 'contra' == $type ) {
        $contra = 1;
      }
    } 

    $data = [
      'time' => current_time( 'mysql' ),
      'user' => esc_attr($_REQUEST['user_id']),
      'pro'  => $pro,
      'contra' => $contra,
      'post_id' => absint($_REQUEST['post_id']),
      'message' => $message,
      'fields' => maybe_serialize($fields),
    ];

    $table_name = $wpdb->prefix . 'helpful_feedback';
    
    $wpdb->insert($table_name, $data);

    return $wpdb->insert_id;
  }
}