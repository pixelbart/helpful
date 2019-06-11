<?php
/**
 * Insert pro and contra in admin tables.
 * @since 3.2.0
 */
class Helpful_Table {
  
  static $instance;

  public function __construct() {    
    $this->registerColumns();
    $this->registerColumnsContent();

    if( is_admin() ) {
      add_action( 'pre_get_posts', [ $this, 'setSortableColumnsQuery' ], 1 );
    }
  }
  
  /**
   * Set instance and fire class
   * @return void
   */
  public static function get_instance() {
    if ( ! isset( self::$instance ) ) {
      self::$instance = new self();
    }  
    return self::$instance;
  }
  
  /**
   * Register columns on admin pages
   * @return void
   */
	public function registerColumns() {
		$post_types = get_option('helpful_post_types');
		if( !isset($post_types) ) {
      return;
    }
    
    foreach( $post_types as $post_type ) {
      $post_type = esc_attr($post_type);
		  add_filter( 'manage_edit-' . $post_type . '_columns', [ $this, 'setColumnsTitle' ], 10 );
		}
	}

  /**
   * Set column titles
   * @param array $defaults defatul columns
   * @return array
   */
	public function setColumnsTitle($defaults) {
		$columns = [];
		foreach ($defaults as $key => $value) {
			$columns[$key] = $value;

			if( 'title' == $key  ) {
				$columns['helpful-pro'] = get_option('helpful_column_pro') ? get_option('helpful_column_pro') : _x( 'Pro', 'column name', 'helpful' );
				$columns['helpful-contra'] = get_option('helpful_column_contra') ? get_option('helpful_column_contra') : _x( 'Contra', 'column name', 'helpful' );
			}
		}

    return $columns;
	}

  /**
   * Register columns content
   * @return string
   */
	public function registerColumnsContent() {
		$post_types = get_option('helpful_post_types');
		if( !isset($post_types) ) {
      return;
    }

		foreach( $post_types as $post_type ) {
      $post_type = esc_attr($post_type);
		  add_action( 'manage_' . $post_type . '_posts_custom_column', [ $this, 'setColumnsContent' ], 10, 2 );
		}
	}

  /**
   * Columns callback
   * @return string
   */
	public function setColumnsContent($column_name, $post_id) {
		if ( 'helpful-pro' == $column_name ) {

      if( get_option('helpful_percentages') ) {
        $percent = Helpful_Helper_Stats::getPro($post_id, true);
        update_post_meta($post_id, 'helpful-pro', $percent);
        printf( '%s%%', esc_html($percent) );
      }
      else {
        $pro = Helpful_Helper_Stats::getPro($post_id);
        update_post_meta($post_id, 'helpful-pro', $pro);
        printf( '%s', intval($pro) );
      }
		}

		if ( 'helpful-contra' == $column_name ) {
      if( get_option('helpful_percentages') ) {
        $percent = Helpful_Helper_Stats::getContra($post_id, true);
        update_post_meta($post_id, 'helpful-contra', $percent);
        printf( '%s%%', esc_html($percent) );
      }
      else {      
        $contra = Helpful_Helper_Stats::getContra($post_id);
        update_post_meta($post_id, 'helpful-contra', $contra);
        printf( '%s', intval($contra) );
      }
		}
	}

  /**
   * Register sortable columns
   * @return string
   */
	public function registerSortableColumns() {
		$post_types = get_option('helpful_post_types');
		if( isset($post_types) ) {
			foreach( $post_types as $post_type ) {
        $post_type = esc_attr($post_type);
				add_filter( 'manage_edit-' . $post_type . '_sortable_columns', [ $this, 'setSortableColumns' ] );
			}
		}
	}

  /**
   * Set sortable columns
   * @return array
   */
	public function setSortableColumns($columns) {
		$columns[ 'helpful-pro' ] = 'helpful-pro';
   	$columns[ 'helpful-contra' ] = 'helpful-contra';
		return $columns;
	}

  /**
   * Make values sortable in columns
   * @param object $query current query
   * @return void
   */
	public function setSortableColumnsQuery($query) {
		if( $query->is_main_query() && ( $orderby = $query->get('orderby') ) ) {
			switch( $orderby ) {
				case 'helpful-pro':
  				$query->set( 'meta_key', 'helpful-pro' );
  				$query->set( 'orderby', 'meta_value_num' );
          break;
				case 'helpful-contra':
  				$query->set( 'meta_key', 'helpful-contra' );
  				$query->set( 'orderby', 'meta_value_num' );
          break;
      }
		}
	}
}