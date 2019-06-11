<?php
/**
 * Helper for getting stored values in database, 
 * for insert pro or contra and for deleting helpful 
 * from single post.
 * @since 4.0.0
 */
class Helpful_Helper_Values {

  /**
   * Defaults values for shortcodes
   * @return array
   */
  public static function getDefaults() {
    global $helpful, $post;    
    
    $post_id = $post->ID;
    $user_id = self::getUser();
    $credits_html = sprintf( 
      '<a href="%s" target="_blank" rel="nofollow">%s</a>', 
      $helpful['credits']['url'], $helpful['credits']['name'] 
    );

    $defaults = [
      'heading' => self::convertTags(get_option('helpful_heading'), $post_id),
      'content' => self::convertTags(get_option('helpful_content'), $post_id),
      'button_pro' => get_option('helpful_pro'),
      'button_contra' => get_option('helpful_contra'),
      'counter' => !get_option('helpful_count_hide'),
      'count_pro' => Helpful_Helper_Stats::getPro($post_id),
      'count_pro_percent' => Helpful_Helper_Stats::getPro($post_id, true),
      'count_contra' => Helpful_Helper_Stats::getContra($post_id),
      'count_contra_percent' => Helpful_Helper_Stats::getContra($post_id, true),
      'credits' => get_option('helpful_credits'),
      'credits_html' => $credits_html,
      'exists' => self::checkUser($user_id, $post_id) ? 1 : 0,
      'exists_text' => self::convertTags(get_option('helpful_exists'), $post_id),
    ];

    return $defaults;
  }

  /**
   * Convert tags to elements
   * @param string $string
   * @param int $post_id
   * @return string
   */
  public static function convertTags($string, $post_id) {
    $post = get_post($post_id);

    $author_name = get_the_author_meta( 'display_name', $post->post_author );
    $permalink = esc_url(get_permalink($post->ID));

		$string = str_replace( '{pro}', Helpful_Helper_Stats::getPro($post->ID), $string );
		$string = str_replace( '{contra}', Helpful_Helper_Stats::getContra($post->ID), $string );
    $string = str_replace( '{permalink}', $permalink, $string );
    $string = str_replace( '{author}', $author_name, $string );

    return $string;
  }

  /**
   * Get user string
   * @return string
   */
  public static function getUser() {

    if( isset($_COOKIE['helpful_user']) ) {
      return $_COOKIE['helpful_user'];    
    }

    if( isset($_SESSION['helpful_user']) ) {
      return $_SESSION['helpful_user'];
    }

    return null;
  }

  /**
   * Check if user has voted on given post 
   * @param string $user_id
   * @param int $post_id
   * @return bool
   */
  public static function checkUser($user_id, $post_id) {
    if( get_option('helpful_multiple') ) {
      return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'helpful';

    $query = $wpdb->prepare("SELECT user, post_id FROM {$table_name} WHERE user = %s AND post_id = %d", $user_id, $post_id);
    $results = $wpdb->get_results($query);

    if( $results ) {
      return true;
    }

    return false;
  }

  /**
   * Insert helpful pro on single post
   * @param int $post_id
   * @param string $user user identicator
   * @return mixed
   */
  public static function insertPro($user, $post_id) {
    global $wpdb;

    $data = [
      'time' => current_time( 'mysql' ),
      'user' => esc_attr($user),
      'pro'  => 1,
      'contra' => 0,
      'post_id' => intval($post_id),
    ];

    $table_name = $wpdb->prefix . 'helpful';
    
    $wpdb->insert($table_name, $data);

    update_post_meta($post_id, 'helpful-pro', Helpful_Helper_Stats::getPro($post_id));

    if( get_option('helpful_percentages') ) {
      update_post_meta($post_id, 'helpful-pro', Helpful_Helper_Stats::getPro($post_id, true));
    }

    return $wpdb->insert_id;
  }

  /**
   * Insert helpful contra on single post
   * @param int $post_id
   * @param string $user user identicator
   * @return mixed
   */
  public static function insertContra($user, $post_id) {
    global $wpdb;

    $data = [
      'time' => current_time( 'mysql' ),
      'user' => esc_attr($user),
      'pro'  => 0,
      'contra' => 1,
      'post_id' => absint($post_id),
    ];

    $table_name = $wpdb->prefix . 'helpful';
    
    $wpdb->insert($table_name, $data);

    update_post_meta($post_id, 'helpful-contra', Helpful_Helper_Stats::getContra($post_id));

    if( get_option('helpful_percentages') ) {
      update_post_meta($post_id, 'helpful-contra', Helpful_Helper_Stats::getContra($post_id, true));
    }

    return $wpdb->insert_id;
  }

  /**
   * Remove helpful stats from single post
   * @param int $post_id
   * @return void
   */
  public static function removeData($post_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'helpful';
    $wpdb->delete( $table_name, [ 'post_id' => $post_id ] );

    delete_post_meta( $post_id, 'helpful-pro' );
    delete_post_meta( $post_id, 'helpful-contra' );
    delete_post_meta( $post_id, 'helpful_remove_data' );
  }
}