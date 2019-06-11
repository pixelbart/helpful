<?php
/**
 * Optimize helper for maintenance class
 * @since 3.2.0
 */
class Helpful_Helper_Optimize {

  /**
   * Executes the methods and returns a response array.
   * @return array responses
   */
  public static function optimizePlugin() {
    
    delete_option('helpful_is_installed');
    delete_option('helpful_feedback_is_installed');

    $response = [];
    $response = array_merge($response, self::optimizeTables());
    $response = array_merge($response, self::moveFeedback());
    $response = array_merge($response, self::removeIncorrectEntries());

    array_filter($response);

    return $response;
  }

  /**
   * Optimize tables `helpful` and `helpful_feedback`.
   * Uses the SQL-Command `OPTIMIZE` for optimization.
   * @return array responses
   */
  private static function optimizeTables() {
    global $wpdb;

    $response = [];

    /* OPTIMIZE helpful table */
    $table_name = $wpdb->prefix . 'helpful';
    if( $wpdb->query("OPTIMIZE TABLE {$table_name}") ) {
      $response []= sprintf(
        esc_html_x( "Table '%s' has been optimized.", 'maintenance response', 'helpful' ),
        $table_name
      );
    }

    /* OPTIMIZE helpful_feedback table */
    $table_name = $wpdb->prefix . 'helpful_feedback';
    if( $wpdb->query("OPTIMIZE TABLE {$table_name}") ) {
      $response []= sprintf(
        esc_html_x( "Table '%s' has been optimized.", 'maintenance response', 'helpful' ),
        $table_name
      );
    }

    return $response;
  }

  /**
   * Moves the feedback from post type `helpful_feedback` to the database 
   * table `helpful_feedback` and returns a response array.
   * @return array response
   */
  private static function moveFeedback() {
    global $wpdb;

    $response = [];

    $args = [
      'post_type' => 'helpful_feedback',
      'posts_per_page' => -1,
      'fields' => 'ids',
    ];

    $query = new WP_Query($args);

    if( !$query->found_posts ) {
      return [];
    }

    $count = $query->found_posts;
    
    foreach( $query->posts as $post_id ) {
      
      $type = get_post_meta( $post_id, 'type', true );

      $data = [
        'time' => get_the_time('Y-m-d H:i:s', $post_id),
        'user' => 0,
        'pro' => ( 'Pro' == $type ? 1 : 0 ),
        'contra' => ( 'Contra' == $type ? 1 : 0 ),
        'post_id' => get_post_meta( $post_id, 'post_id', true ),
        'message' => get_post_field( 'post_content', $post_id ),
        'fields' => maybe_serialize( [
          'browser' => get_post_meta( $post_id, 'browser', true ),
          'platform' => get_post_meta( $post_id, 'platform', true ),
          'language' => get_post_meta( $post_id, 'language', true ),
        ] ),
      ];

      /* insert post into database */
      $table_name = $wpdb->prefix . 'helpful_feedback';
      $wpdb->insert($table_name, $data);

      /* delete post */
      if( $wpdb->insert_id ) {
        wp_delete_post($post_id, true);
      }
    }

    $response []= sprintf( 
      esc_html_x('%d Feedback entries moved in the database', 'maintenance response', 'helpful'), 
      $count
    );

    return $response;
  }

  /**
   * Remove incorrect entries from database tables `helpful` and `helpful_feedback`.
   * All entries that do not have a user saved are affected.
   * @return array responses
   */
  private static function removeIncorrectEntries() {
    global $wpdb;

    $response = [];

    /* Remove incorrect entries from 'helpful' table */
    $table_name = $wpdb->prefix . 'helpful';
    $query = $wpdb->prepare( "SELECT id, user FROM {$table_name} WHERE user = %s", '' );
    $items = $wpdb->get_results( $query );

    if( $items ) {
      foreach( $items as $item ) {
        $wpdb->delete( $table_name, [ 'id' => $item->id ] );
      }

      $count = count($items);
      $response []= sprintf( 
        esc_html_x("%d incorrect entries have been removed from table '%s'", 'maintenance response', 'helpful'), 
        $count, $table_name
      );
    }

    /* Remove incorrect entries from 'helpful_feedback' table */
    $table_name = $wpdb->prefix . 'helpful_feedback';
    $query = $wpdb->prepare( "SELECT id, user FROM {$table_name} WHERE user = %s", '' );
    $items = $wpdb->get_results( $query );

    if( $items ) {
      foreach( $items as $item ) {
        $wpdb->delete( $table_name, [ 'id' => $item->id ] );
      }

      $count = count($items);
      $response []= sprintf( 
        esc_html_x("%d incorrect entries have been removed from table '%s'", 'maintenance response', 'helpful'),
        $count, $table_name
      );
    }

    return $response;
  }
}