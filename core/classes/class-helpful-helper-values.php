<?php
/**
 * Helper for getting stored values in database, 
 * for insert pro or contra and for deleting helpful 
 * from single post.
 * @since 3.2.0
 */
class Helpful_Helper_Values {

  public static $green = '#88c057';
  public static $red = '#ed7161';

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
      'heading' => get_option('helpful_heading'),
      'content' => self::convertTags(get_option('helpful_content'), $post_id),
      'button_pro' => get_option('helpful_pro'),
      'button_contra' => get_option('helpful_contra'),
      'counter' => !get_option('helpful_count_hide'),
      'count_pro' => self::getPro($post_id),
      'count_pro_percent' => self::getPro($post_id, true),
      'count_contra' => self::getContra($post_id),
      'count_contra_percent' => self::getContra($post_id, true),
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
    $permalink = esc_url(get_permalink($post_id));
		$string = str_replace( '{pro}', self::getPro($post_id), $string );
		$string = str_replace( '{contra}', self::getContra($post_id), $string );
    $string = str_replace( '{permalink}', $permalink, $string );
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
      'post_id' => absint($post_id),
    ];

    $table_name = $wpdb->prefix . 'helpful';
    
    $wpdb->insert($table_name, $data);

    update_post_meta($post_id, 'helpful-pro', self::getPro($post_id));

    if( get_option('helpful_percentages') ) {
      update_post_meta($post_id, 'helpful-pro', self::getPro($post_id, true));
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

    update_post_meta($post_id, 'helpful-contra', self::getContra($post_id));

    if( get_option('helpful_percentages') ) {
      update_post_meta($post_id, 'helpful-contra', self::getContra($post_id, true));
    }

    return $wpdb->insert_id;
  }

  /**
   * Remove helpful stats from single post
   * @param int $post_id
   * @return void
   */
  public static function removeData($post_id) {
    global $wpdb, $wp_query;

    $table_name = $wpdb->prefix . 'helpful';
    $wpdb->delete( $table_name, [ 'post_id' => $post_id ] );

    delete_post_meta( $post_id, 'helpful-pro' );
    delete_post_meta( $post_id, 'helpful-contra' );
    delete_post_meta( $post_id, 'helpful_remove_data' );
  }

  /**
   * Get pro count by post id
   * @param int $post_id if null current post id
   * @param bool $percentages return percentage values on true
   * @return int count
   */
  public static function getPro($post_id = null, $percentages = false) {
    if( is_null($post_id ) ) {
      global $post;
      $post_id = $post->ID;
    }

    global $wpdb;

    $post_id = absint($post_id);
    $helpful = $wpdb->prefix . 'helpful';
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $helpful WHERE pro = 1 AND post_id = %d", $post_id);
    $var = $wpdb->get_var($sql);

    if( false == $percentages ) {
      return $var;
    }

    $pro = ( $var ? (int) $var : 0 );
    $contra = self::getContra($post_id);

    $pro_percent = 0;

    if( 0 !== $pro ) {
      $pro_percent = ( ( $pro / ( $pro + $contra ) ) * 100 );
    }

    $pro_percent = number_format($pro_percent, 2);

    return (float) str_replace('.00', '', $pro_percent);
  }
  
  /**
   * Get contra count by post id
   * @param int $post_id if null current post id
   * @param bool $percentages return percentage values on true
   * @return int count
   */
  public static function getContra($post_id = null, $percentages = false) {
    if( is_null($post_id ) ) {
      global $post, $wpdb;
      $post_id = $post->ID;
    }

    global $wpdb;
    $post_id = absint($post_id);
    $helpful = $wpdb->prefix . 'helpful';    
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $helpful WHERE contra = 1 AND post_id = %d", $post_id);
    $var = $wpdb->get_var($sql);

    if( false == $percentages ) {
      return $var;
    }

    $contra = ( $var ? (int) $var : 0 );
    $pro = self::getPro($post_id);

    $contra_percent = 0;

    if( 0 !== $contra ) {
      $contra_percent = ( ( $contra / ( $pro + $contra ) ) * 100 );
    }

    $contra_percent = number_format($contra_percent, 2);
    return (float) str_replace('.00', '', $contra_percent);
  }
  
  /**
   * Get pro count of all posts
   * @param bool $percentages return percentage values on true
   * @return int count
   */
  public static function getProAll($percentages = false) {
    global $wpdb;
    $helpful = $wpdb->prefix . 'helpful';    
    $sql = "SELECT COUNT(*) FROM $helpful WHERE pro = 1";
    $var = $wpdb->get_var($sql);

    if( false == $percentages ) {
      return $var;
    }

    $pro = ( $var ? (int) $var : 0 );
    $contra = self::getContraAll();

    $pro_percent = 0;

    if( 0 !== $pro ) {
      $pro_percent = ( ( $pro / ( $pro + $contra ) ) * 100 );
    }

    $pro_percent = number_format($pro_percent, 2);

    return (float) str_replace('.00', '', $pro_percent);
  }
  
  /**
   * Get contra count of all posts
   * @param bool $percentages return percentage values on true
   * @return int count
   */
  public static function getContraAll($percentages = false) {
    global $wpdb;
    $helpful = $wpdb->prefix . 'helpful';    
    $sql = "SELECT COUNT(*) FROM $helpful WHERE contra = 1";
    $var = $wpdb->get_var($sql);

    if( false == $percentages ) {
      return $var;
    }

    $contra = ( $var ? (int) $var : 0 );
    $pro = self::getProAll();

    $contra_percent = 0;

    if( 0 !== $contra ) {
      $contra_percent = ( ( $contra / ( $pro + $contra ) ) * 100 );
    }

    $contra_percent = number_format($contra_percent, 2);
    return (float) str_replace('.00', '', $contra_percent);
  }

  /**
   * Get years
   * @return array
   */
  public static function getYears() {
    global $wpdb;
    $helpful = $wpdb->prefix . 'helpful';
    $sql = "SELECT time FROM $helpful ORDER BY time DESC";
    $results = $wpdb->get_results($sql);

    if( !$results ) {
      return [];
    }

    $years = [];

    foreach( $results as $result ) {
      $years[] = date('Y', strtotime($result->time));
    }

    $years = array_unique($years);

    return $years;
  }
  
  /**
   * Stats for today
   * @return array
   */
  public static function getStatsToday($year) {

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    $query   = "
    SELECT pro, contra, time 
    FROM $helpful 
    WHERE DAYOFYEAR(time) = DAYOFYEAR(NOW()) 
    AND YEAR(time) = %d
    ";

    $query   = $wpdb->prepare($query, $year);
    $results = $wpdb->get_results($query);
    
    if( !$results ) {
      return [
        'status' => 'error',
        'message' => __('No entries found', 'helpful'),
      ];
    }

    $pro = wp_list_pluck($results, 'pro');
    $pro = array_sum($pro);

    $contra = wp_list_pluck($results, 'contra');
    $contra = array_sum($contra);

    /* Response for ChartJS */    
    $response = [
      'type' => 'doughnut',
      'data' => [
        'datasets' => [
          [
            'data' => [ absint($pro), absint($contra), ],
            'backgroundColor' => [ self::$green, self::$red, ],
          ],
        ],
        'labels' => ['Pro', 'Contra'],
      ],
      'options' => [
        'legend' => [
          'position' => 'bottom',
        ],
      ],
    ];

    return $response;
  }

  /**
   * Stats for yesterday
   * @return array
   */
  public static function getStatsYesterday($year) {

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    $query   = "
    SELECT pro, contra, time 
    FROM $helpful 
    WHERE DAYOFYEAR(time) = DAYOFYEAR(SUBDATE(CURDATE(),1)) 
    AND YEAR(time) = %d
    ";

    $query   = $wpdb->prepare($query, $year);
    $results = $wpdb->get_results($query);
    
    if( !$results ) {
      return [
        'status' => 'error',
        'message' => __('No entries found', 'helpful'),
      ];
    }

    $pro = wp_list_pluck($results, 'pro');
    $pro = array_sum($pro);

    $contra = wp_list_pluck($results, 'contra');
    $contra = array_sum($contra);

    /* Response for ChartJS */    
    $response = [
      'type' => 'doughnut',
      'data' => [
        'datasets' => [
          [
            'data' => [ absint($pro), absint($contra), ],
            'backgroundColor' => [ self::$green, self::$red, ],
          ],
        ],
        'labels' => ['Pro', 'Contra'],
      ],
      'options' => [
        'legend' => [
          'position' => 'bottom',
        ],
      ],
    ];

    return $response;
  }

  /**
   * Stats for week
   * @return array
   */
  public static function getStatsWeek($year) {

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    $query   = "
    SELECT pro, contra, time 
    FROM $helpful 
    WHERE WEEK(time, 1) = WEEK(CURDATE(), 1) 
    AND YEAR(time) = %d
    ";

    $query   = $wpdb->prepare($query, $year);
    $results = $wpdb->get_results($query);

    if( !$results ) {
      return [
        'status' => 'error',
        'message' => __('No entries found', 'helpful'),
      ];
    }
    
    $pro = [];    
    $contra = [];
    $labels = [];
    $timestamp = strtotime('monday this week');
    $days = 7;

    for( $i = 0; $i < $days; $i++ ) {
      $date = date_i18n( 'Ymd', strtotime('+'.$i.' days', $timestamp) );
      $day = date_i18n( 'D', strtotime('+'.$i.' days', $timestamp) );
      $pro[$date] = 0;
      $contra[$date] = 0;
      $labels[] = $day;
    }

    foreach( $results as $result ) {
      for( $i = 0; $i < $days; $i++ ) {
        $day = date_i18n( 'Ymd', strtotime('+'.$i.' days', $timestamp) );
        $date = date_i18n( 'Ymd', strtotime($result->time) );
        
        if( $day == $date ) {
          $pro[$date] += $result->pro;
          $contra[$date] += $result->contra;
        }
      }
    }

    /* Response for ChartJS */    
    $response = [
      'type' => 'bar',
      'data' => [
        'datasets' => [
          [
            'label' => 'Pro',
            'data' => array_values($pro),
            'backgroundColor' => self::$green,
          ],
          [
            'label' => 'Contra',
            'data' => array_values($contra),
            'backgroundColor' => self::$red,
          ],
        ],
        'labels' => $labels,
      ],
      'options' => [
        'scales' => [
          'xAxes' => [
            [ 'stacked' => true ],
          ],
          'yAxes' => [
            [ 'stacked' => true ],
          ],
        ],
        'legend' => [
          'position' => 'bottom',
        ],
      ],
    ];

    return $response;
  }

  /**
   * Stats for month
   * @return array
   */
  public static function getStatsMonth($year, $month = null) {

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    if( is_null($month) ) {
      $month = date('m');
    } else {
      $month = absint($month);
    }

    $query   = "
    SELECT pro, contra, time 
    FROM $helpful 
    WHERE MONTH(time) = %d
    AND YEAR(time) = %d
    ";

    $query   = $wpdb->prepare($query, $month, $year);
    $results = $wpdb->get_results($query);

    if( !$results ) {
      return [
        'status' => 'error',
        'message' => __('No entries found', 'helpful'),
      ];
    }
    
    $pro = [];    
    $contra = [];
    $labels = [];
    $timestamp = strtotime(date("$year-$month-1"));
    $days = date_i18n('t', $timestamp) - 1;

    for( $i = 0; $i < $days; $i++ ) {
      $date = date_i18n( 'Ymd', strtotime('+'.$i.' days', $timestamp) );
      $day = date_i18n( 'j M', strtotime('+'.$i.' days', $timestamp) );
      $pro[$date] = 0;
      $contra[$date] = 0;
      $labels[] = $day;
    }

    foreach( $results as $result ) {
      for( $i = 0; $i < $days; $i++ ) {
        $day = date_i18n( 'Ymd', strtotime('+'.$i.' days', $timestamp) );
        $date = date_i18n( 'Ymd', strtotime($result->time) );
        
        if( $day == $date ) {
          $pro[$date] += $result->pro;
          $contra[$date] += $result->contra;
        }
      }
    }

    /* Response for ChartJS */    
    $response = [
      'type' => 'bar',
      'data' => [
        'datasets' => [
          [
            'label' => 'Pro',
            'data' => array_values($pro),
            'backgroundColor' => self::$green,
          ],
          [
            'label' => 'Contra',
            'data' => array_values($contra),
            'backgroundColor' => self::$red,
          ],
        ],
        'labels' => $labels,
      ],
      'options' => [
        'scales' => [
          'xAxes' => [
            [ 'stacked' => true ],
          ],
          'yAxes' => [
            [ 'stacked' => true ],
          ],
        ],
        'legend' => [
          'position' => 'bottom',
        ],
      ],
    ];

    return $response;
  }

  /**
   * Stats for year
   * @return array
   */
  public static function getStatsYear($year) {

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    $query   = "
    SELECT pro, contra, time 
    FROM $helpful 
    WHERE YEAR(time) = %d
    ";

    $query   = $wpdb->prepare($query, $year);
    $results = $wpdb->get_results($query);

    if( !$results ) {
      return [
        'status' => 'error',
        'message' => __('No entries found', 'helpful'),
      ];
    }
    
    $pro = [];    
    $contra = [];
    $labels = [];
    $timestamp = strtotime(sprintf(date('%d-1-1'), $year));
    $days = 12;

    for( $i = 0; $i < $days; $i++ ) {
      $month = date_i18n( 'M', strtotime('+'.$i.' months', $timestamp) );
      $pro[$month] = 0;
      $contra[$month] = 0;
      $labels[] = $month;
    }

    foreach( $results as $result ) {
      for( $i = 0; $i < $days; $i++ ) {
        $month = date_i18n( 'M', strtotime('+'.$i.' months', $timestamp) );
        $m = date_i18n( 'M', strtotime($result->time));
        
        if( $month == $m ) {
          $pro[$month] += $result->pro;
          $contra[$month] += $result->contra;
        }
      }
    }

    /* Response for ChartJS */    
    $response = [
      'type' => 'bar',
      'data' => [
        'datasets' => [
          [
            'label' => 'Pro',
            'data' => array_values($pro),
            'backgroundColor' => self::$green,
          ],
          [
            'label' => 'Contra',
            'data' => array_values($contra),
            'backgroundColor' => self::$red,
          ],
        ],
        'labels' => $labels,
      ],
      'options' => [
        'scales' => [
          'xAxes' => [
            [ 'stacked' => true ],
          ],
          'yAxes' => [
            [ 'stacked' => true ],
          ],
        ],
        'legend' => [
          'position' => 'bottom',
        ],
      ],
    ];

    return $response;
  }

  /**
   * Stats by range
   * @return array
   */
  public static function getStatsRange($from, $to) {

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    $query   = "
    SELECT pro, contra, time 
    FROM $helpful
    WHERE DATE(time) >= DATE(%s) 
    AND DATE(time) <= DATE(%s)
    ";

    $query   = $wpdb->prepare($query, $from, $to);
    $results = $wpdb->get_results($query);

    if( !$results ) {
      return [
        'status' => 'error',
        'message' => __('No entries found', 'helpful'),
      ];
    }

    $from_date = new DateTime($from);
    $to_date = new DateTime($to);

    $diff = $from_date->diff($to_date);
    
    $pro = [];    
    $contra = [];
    $labels = [];
    $timestamp = strtotime($from);
    $days = date_i18n('t', $timestamp) - 1;

    for( $i = 0; $i < ($diff->format("%a") + 1); $i++ ) {
      $date = date_i18n( 'Ymd', strtotime('+'.$i.' days', $timestamp) );
      $day = date_i18n( 'j M', strtotime('+'.$i.' days', $timestamp) );
      $pro[$date] = 0;
      $contra[$date] = 0;
      $labels[] = $day;
    }

    foreach( $results as $result ) {
      $date = date_i18n( 'Ymd', strtotime($result->time) );
      $pro[$date] += (int) $result->pro;
      $contra[$date] += (int) $result->contra;
    }

    /* Response for ChartJS */    
    $response = [
      'type' => 'bar',
      'data' => [
        'datasets' => [
          [
            'label' => 'Pro',
            'data' => array_values($pro),
            'backgroundColor' => self::$green,
          ],
          [
            'label' => 'Contra',
            'data' => array_values($contra),
            'backgroundColor' => self::$red,
          ],
        ],
        'labels' => $labels,
      ],
      'options' => [
        'legend' => [
          'position' => 'bottom',
        ],
      ],
    ];

    return $response;
  }

  /**
   * Stats for total
   * @return array
   */
  public static function getStatsTotal() {

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    $query   = "
    SELECT pro, contra, time 
    FROM $helpful
    ";

    $results = $wpdb->get_results($query);
    
    if( !$results ) {
      return [
        'status' => 'error',
        'message' => __('No entries found', 'helpful'),
      ];
    }

    $pro = wp_list_pluck($results, 'pro');
    $pro = array_sum($pro);

    $contra = wp_list_pluck($results, 'contra');
    $contra = array_sum($contra);

    /* Response for ChartJS */    
    $response = [
      'type' => 'doughnut',
      'data' => [
        'datasets' => [
          [
            'data' => [ absint($pro), absint($contra), ],
            'backgroundColor' => [ self::$green, self::$red, ],
          ],
        ],
        'labels' => ['Pro', 'Contra'],
      ],
      'options' => [
        'legend' => [
          'position' => 'bottom',
        ],
      ],
    ];

    return $response;
  }

  /**
   * Get most helpful posts
   * @return array
   */
  public static function getMostHelpful($limit = null) {

    if( is_null($limit) ) {
      $limit = absint(get_option('helpful_widget_amount'));
    }

    $args = [
      'post_type' => get_option('helpful_post_types'),
      'post_status' => 'any',
      'posts_per_page' => -1,
      'fields' => 'ids',
    ];

    $query = new WP_Query($args);

    $posts = [];

    if( $query->found_posts ) {
      foreach( $query->posts as $post_id ) {
        $pro = self::getPro($post_id) ? self::getPro($post_id) : 0;
        $contra = self::getContra($post_id) ? self::getContra($post_id) : 0;
        $posts[$post_id] = (int) ( $pro - $contra );
      }

      if( count($posts) > 1 ) {

        arsort($posts);

        $results = [];
        $posts = array_slice($posts, 0, $limit, true);

        foreach( $posts as $post_id => $value ) {
          if( 0 == $value ) continue;
          $results[] = [
            'ID' => $post_id,
            'url' => get_the_permalink($post_id),
            'name' => get_the_title($post_id),
            'time' => sprintf( 
              __('Published %s ago', 'helpful'), 
              human_time_diff(date_i18n(get_the_date('U', $post_id)), date_i18n('U')),
            ),
          ];
        }
      }
    }

    $results = array_filter($results);

    return $results;
  }

  /**
   * Get least helpful posts
   * @return array
   */
  public static function getLeastHelpful($limit = null) {

    if( is_null($limit) ) {
      $limit = absint(get_option('helpful_widget_amount'));
    }

    $args = [
      'post_type' => get_option('helpful_post_types'),
      'post_status' => 'any',
      'posts_per_page' => -1,
      'fields' => 'ids',
    ];

    $query = new WP_Query($args);

    $posts = [];

    if( $query->found_posts ) {
      foreach( $query->posts as $post_id ) {
        $pro = self::getPro($post_id) ? self::getPro($post_id) : 0;
        $contra = self::getContra($post_id) ? self::getContra($post_id) : 0;
        $posts[$post_id] = (int) ( $contra - $pro );
      }

      if( count($posts) > 1 ) {

        arsort($posts);

        $results = [];
        $posts = array_slice($posts, 0, $limit, true);

        foreach( $posts as $post_id => $value ) {
          if( 0 == $value ) continue;
          $results[] = [
            'ID' => $post_id,
            'url' => get_the_permalink($post_id),
            'name' => get_the_title($post_id),
            'time' => sprintf( 
              __('Published %s ago', 'helpful'), 
              human_time_diff(date_i18n(get_the_date('U', $post_id)), date_i18n('U')),
            ),
          ];
        }
      }
    }

    $results = array_filter($results);

    return $results;
  }

  /**
   * Get recently helpful pro posts
   * @return array
   */
  public static function getRecentlyPro($limit = null) {

    if( is_null($limit) ) {
      $limit = absint(get_option('helpful_widget_amount'));
    }

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    $query   = "SELECT post_id, time FROM $helpful WHERE pro = %d ORDER BY id DESC LIMIT %d";
    $query   = $wpdb->prepare($query, 1, $limit);
    $results = $wpdb->get_results($query);

    if( $results ) {
      foreach( $results as $post ) {
        $timestamp = strtotime($post->time);
        $posts[] = [
          'ID' => $post->post_id,
          'url' => get_the_permalink($post->post_id),
          'name' => get_the_title($post->post_id),
          'time' => sprintf( 
            __('Submitted %s ago', 'helpful'), 
            human_time_diff(date_i18n($timestamp), date_i18n('U'))
          ),
        ];
      }
    }

    return $posts;
  }

  /**
   * Get recently unhelpful pro posts
   * @return array
   */
  public static function getRecentlyContra($limit = null) {

    if( is_null($limit) ) {
      $limit = absint(get_option('helpful_widget_amount'));
    }

    global $wpdb;

    $helpful = $wpdb->prefix . 'helpful';

    $query   = "SELECT post_id, time FROM $helpful WHERE contra = %d ORDER BY id DESC LIMIT %d";
    $query   = $wpdb->prepare($query, 1, $limit);
    $results = $wpdb->get_results($query);

    if( $results ) {
      foreach( $results as $post ) {
        $timestamp = strtotime($post->time);
        $posts[] = [
          'ID' => $post->post_id,
          'url' => get_the_permalink($post->post_id),
          'name' => get_the_title($post->post_id),
          'time' => sprintf( 
            __('Submitted %s ago', 'helpful'), 
            human_time_diff(date_i18n($timestamp), date_i18n('U'))
          ),
        ];
      }
    }

    return $posts;
  }
}