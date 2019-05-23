<?php
namespace Helpful\Core;
new Admin;

class Admin
{
	public function __construct()
  {
		// install database table
		register_activation_hook( HELPFUL_FILE, [ $this, 'install' ] );

		// register menu
		add_action( 'admin_menu', [ $this, 'register_menu' ] );

		// register tab general
		add_action( 'helpful_tabs', [ $this, 'register_tabs' ], 1 );

		// register tabs content
		add_action( 'helpful_tabs_content', [ $this, 'setup_tabs' ] );

		// enqueue backend Scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'backend_enqueue' ] );

		// backend sidebar
		add_action( 'helpful_sidebar', [ $this, 'sidebar' ], 1 );

  	// Themes
  	add_filter( 'helpful-themes', [ $this, 'themes' ] );

		// register columns
		$this->register_columns();

		// register columns content
		$this->register_columns_content();

		// register sortable columns
		$this->register_sortable_columns();

    // table css
    add_action('admin_head', [$this, 'table_css']);

		// make columns values sortable in query
		add_action( 'pre_get_posts', [ $this, 'make_sortable_columns' ], 1 );

		// dashboard widget
		$this->register_widget();

    // truncate/uninstall table
    add_action( 'admin_init', [$this, 'truncate'] );
    
    if( get_option('helpful_meta_box') ) {
      // add meta box
      add_action( 'add_meta_boxes', [$this, 'add_meta_box'] );

      // on save meta box
      add_action( 'save_post', [$this, 'save_meta_box'] );
    }
	}

  /**
   * Install database table
   *
   * @return bool
   */
	public function install()
  {
		global $wpdb;

    if( get_option('helpful_is_installed') == 1 ) {
      return false;
    }

		// table name
		$table_name = $wpdb->prefix . 'helpful';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			user varchar(55) NOT NULL,
			pro mediumint(1) NOT NULL,
			contra mediumint(1) NOT NULL,
			post_id mediumint(9) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);

		$this->default_options(true);

    update_option('helpful_is_installed', 1);

    return true;
	}

  /**
   * Truncate table and delete post meta
   */
	public function truncate()
  {
		if( get_option('helpful_uninstall') ) {

  		global $wpdb;

      $table_name = $wpdb->prefix . 'helpful';
      $wpdb->query("TRUNCATE TABLE $table_name");      
  		update_option( 'helpful_uninstall', false );

      $args = [
        'post_type' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
      ];

      $posts = new \WP_Query($args);

      if( $posts->found_posts ) {
    		foreach( $posts->posts as $post_id ) {
    			if( get_post_meta( $post_id, 'helpful-pro' ) ) {
    				delete_post_meta( $post_id, 'helpful-pro' );
    			}
    			if( get_post_meta( $post_id, 'helpful-contra' ) ) {
    				delete_post_meta( $post_id, 'helpful-contra' );
    			}

          if( 'helpful_feedback' == get_post_type($post_id) ) {
            wp_delete_post($post_id, true);
          }
    		}
      }

      update_option('helpful_is_installed', 0);
    }
	}

  /**
   * Register admin menu
   */
	public function register_menu()
  {
    // add menu
    add_menu_page(
      __( 'Helpful', 'helpful' ),
      __( 'Helpful', 'helpful' ),
      'manage_options',
      'helpful',
			[ $this, 'settings_page' ],
      'dashicons-thumbs-up',
      99
    );

		// add submenu on options
		add_submenu_page(
			'helpful',
			__( 'Settings', 'helpful' ),
			__( 'Settings', 'helpful' ),
			'manage_options',
			'helpful',
			[ $this, 'settings_page' ]
		);

		// register settings for settings page
    add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

  /**
   * Callback for admin page
   *
   * @return string
   */
	public function settings_page()
  {
		include( HELPFUL_PATH . 'templates/backend.php' );
	}

  /**
   * Register admin settings
   */
	public function register_settings()
  {
    $fields = [
      'helpful_heading',
      'helpful_content',
      'helpful_pro',
      'helpful_exists',
      'helpful_contra',
      'helpful_column_pro',
      'helpful_column_contra',
      'helpful_after_pro',
      'helpful_after_contra',
    ];

    foreach( $fields as $field ) {
      register_setting( 'helpful-text-settings-group', $field );
    }

    $fields = [
      'helpful_credits',
      'helpful_hide_in_content',
      'helpful_post_types',
      'helpful_exists_hide',
      'helpful_count_hide',
      'helpful_widget',
      'helpful_widget_amount',
      'helpful_widget_pro',
      'helpful_widget_contra',
      'helpful_widget_pro_recent',
      'helpful_widget_contra_recent',
      'helpful_only_once',
      'helpful_multiple',
      'helpful_percentages',
      'helpful_form_status_pro',
      'helpful_form_email_pro',
      'helpful_form_status_contra',
      'helpful_form_email_contra',
      'helpful_meta_box',
    ];

    foreach( $fields as $field ) {
      register_setting( 'helpful-detail-settings-group', $field );
    }

    $fields = [
      'helpful_theme',
      'helpful_css',
      'helpful_color',
    ];

    foreach( $fields as $field ) {
      register_setting( 'helpful-design-settings-group', $field );
    }

    $fields = [
      'helpful_feedback_widget',
      'helpful_feedback_after_pro',
      'helpful_feedback_after_contra',
      'helpful_feedback_message_pro',
      'helpful_feedback_message_contra',
      'helpful_feedback_messages_table',
      'helpful_feedback_widget_overview',
      'helpful_feedback_table_type',
      'helpful_feedback_table_post',
      'helpful_feedback_table_post_shorten',
      'helpful_feedback_table_browser',
      'helpful_feedback_table_platform',
      'helpful_feedback_table_language',
    ];

    foreach( $fields as $field ) {
      register_setting( 'helpful-feedback-settings-group', $field );
    }

    $fields = [
      'helpful_uninstall',
      'helpful_timezone',
    ];

    foreach( $fields as $field ) {
      register_setting( 'helpful-system-settings-group', $field );
    }
	}

  /**
   * Default values for settings
   *
   * @param bool $status set true for set defaults
   * @return string
   */
	public function default_options( $status = false )
  {
		if( false == $status ) {
      return false;
    }

    $options = [
      'helpful_heading' => _x( 'Was this post helpful?', 'default headline', 'helpful' ),
      'helpful_content' => _x( 'Let us know if you liked the post. That’s the only way we can improve.', 'default description', 'helpful' ),
      'helpful_exists' => _x( 'You have already voted for this post.', 'already voted', 'helpful' ),
      'helpful_success' => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
      'helpful_error' => _x( 'Sorry, an error has occurred.', 'error after voting', 'helpful' ),
      'helpful_pro' => _x( 'Yes', 'text pro button', 'helpful' ),
      'helpful_contra' => _x( 'Yes', 'text pro button', 'helpful' ),
      'helpful_column_pro' => _x( 'Pro', 'column name', 'helpful' ),
      'helpful_column_contra' => _x( 'Contra', 'column name', 'helpful' ),
      'helpful_post_types' => array( 'post' ),
      'helpful_count_hide' => false,
      'helpful_credits' => true,
      'helpful_widget' => false,
      'helpful_uninstall' => false,
    ];

    $options = apply_filters('helpful_options', $options);

    foreach( $options as $slug => $value ) {
      update_option( $slug, $value );
    }
	}

  /**
   * Register tabs for admin page
   * @return string
   */
	public function register_tabs()
  {
		global $helpful;

    $tabs = [
      'text' => [
        'class' => $helpful['tab'] == 'text' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=text',
        'name'  => esc_html_x( 'Texts', 'tab name', 'helpful' ),
      ],
      'general' => [
        'class' => $helpful['tab'] == 'detail' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=detail',
        'name'  => esc_html_x( 'Details', 'tab name', 'helpful' ),
      ],
      'design' => [
        'class' => $helpful['tab'] == 'design' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=design',
        'name'  => esc_html_x( 'Design', 'tab name', 'helpful' ),
      ],
      'feedback' => [
        'class' => $helpful['tab'] == 'feedback' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=feedback',
        'name'  => esc_html_x( 'Feedback', 'tab name', 'helpful' ),
      ],
      'system' => [
        'class' => $helpful['tab'] == 'system' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=system',
        'name'  => esc_html_x( 'System', 'tab name', 'helpful' ),
      ],
    ];

    $tabs = apply_filters('helpful_admin_tabs', $tabs);

    foreach( $tabs as $tab ) {
      printf(
        '<li class="%s"><a href="%s" class="helpful-tab-link">%s</a></li>',
        esc_attr($tab['class']), esc_attr($tab['href']), esc_html($tab['name'])
      );
    }
	}

  /**
   * Setup tabs content for admin page
   * @return string
   */
	public function setup_tabs()
  {
		foreach ( glob( HELPFUL_PATH . "core/tabs/*.php" ) as $file ) {
			include_once($file);
		}
	}

  /**
   * Enqueue backend scripts and styles
   * @return string
   */
	public function backend_enqueue()
  {
		// register styles
    $file = plugins_url( 'core/assets/css/widget.css', HELPFUL_FILE );
		wp_register_style( 'helpful-charts', $file, [], HELPFUL_VERSION );

    // register scripts
    $file = plugins_url( 'core/assets/js/widget.js', HELPFUL_FILE );
  	wp_register_script( 'helpful-widget', $file, [], HELPFUL_VERSION, true);

    $file = plugins_url( 'core/assets/js/design.js', HELPFUL_FILE );
  	wp_enqueue_script( 'helpful-design', $file, [], HELPFUL_VERSION, true);

		// current screen is helpful
    // enqueue admin css
    $screen = get_current_screen();
		if( 'toplevel_page_helpful' !== $screen->base ) {
      return;
    }

    $file = plugins_url( 'core/assets/css/admin.css', HELPFUL_FILE );
    wp_enqueue_style ( 'helpful-backend', $file, [], HELPFUL_VERSION );
    
    // Register theme for preview
    foreach ( glob( HELPFUL_PATH . 'core/assets/themes/*.css' ) as $theme ) {

      $pathinfo = pathinfo($theme);
      $name = str_replace( '.css', '', $pathinfo['basename'] );
      $file = HELPFUL_PATH . 'core/assets/themes/' . $name . '.css';

      if( false !== stream_resolve_include_path($file) ) {
        $file = plugins_url( 'core/assets/themes/' . $name . '.css', HELPFUL_FILE );
        wp_enqueue_style( 'helpful-preview-' . $name, $file, [], HELPFUL_VERSION );
      }
    }

    $file = plugins_url( 'core/assets/css/tab-design.css', HELPFUL_FILE );
    wp_enqueue_style( 'helpful-design', $file, [], HELPFUL_VERSION );

    // Enqueue code editor and settings for manipulating HTML.

    if( function_exists('wp_enqueue_code_editor') ) {
      $editor_types = [ 'type' => 'css' ];
      $settings = wp_enqueue_code_editor( $editor_types );

      // fail if user disabled CodeMirror.
      if( false !== $settings ) {

        $script = sprintf(
          '(function($) { $(function() {
            if( $(".helpful_css").length ) { wp.codeEditor.initialize( "helpful_css", %s ); }
          }); })(jQuery)',
          wp_json_encode($settings)
        );

        wp_add_inline_script('code-editor', $script);
      }
    }
	}

  /**
   * Backend sidebar content for admin page
   *
   * @return string
   */
	public function sidebar()
  {
    global $wp_version;

		$html  = sprintf('<h4>%s</h4>', esc_html_x( 'Links & Support', 'headline sidebar options page', 'helpful' ));
		$html .= sprintf('<p>%s</p>', esc_html_x( 'You have an question?', 'description sidebar options page', 'helpful' ));
		$html .= '<ul>';

		$html .= sprintf(
			'<li><a href="%s" target="_blank">%s</a></li>',
			'https://wordpress.org/plugins/helpful/#developers',
			esc_html_x( 'Changelogs', 'link text sidebar options page', 'helpful' )
		);

		$html .= sprintf(
			'<li><a href="%s" target="_blank">%s</a></li>',
			'https://wordpress.org/support/plugin/helpful',
			esc_html_x( 'Help & Support', 'link text sidebar options page', 'helpful' )
		);

		$html .= sprintf(
			'<li><a href="%s" target="_blank">%s</a></li>',
			'https://wordpress.org/support/plugin/helpful/reviews/#new-post',
			esc_html_x( 'Rate this plugin', 'link text sidebar options page', 'helpful' )
		);

    $html .='</ul>';
    $html .= sprintf( '<h4>%s</h4>', esc_html_x( 'Troubleshooting Information', 'text sidebar options page', 'helpful' ) );
		$html .= '<ul>';

    if( isset($wp_version) ) {
      $text = esc_html_x('WordPress Version: %s', 'info sidebar options page', 'helpful');
      $text = sprintf($text, $wp_version);
  		$html .= sprintf('<li>%s</li>', $text);
    }

    if( function_exists('phpversion') ) {
      $text = esc_html_x('PHP Version: %s', 'info sidebar options page', 'helpful');
      $text = sprintf($text, phpversion());
  		$html .= sprintf('<li>%s</li>', $text);
    }

    if( HELPFUL_VERSION ) {
      $text = esc_html_x('Helpful Version: %s', 'info sidebar options page', 'helpful');
      $text = sprintf($text, HELPFUL_VERSION);
  		$html .= sprintf('<li>%s</li>', $text);
    }

    $html .='</ul>';

		echo $html;
	}

  /**
   * Register custom dashboard widget
   */
	public function register_widget()
  {
		if( !get_option('helpful_widget') ) {
			add_action( 'wp_dashboard_setup', [ $this, 'widget' ], 1 );
		}
	}

  /**
   * Dashboard widget options
   */
	public function widget()
  {
		global $wp_meta_boxes;

    wp_add_dashboard_widget( 'helpful_widget', esc_html_x( 'Helpful', 'headline dashboard widget', 'helpful' ), 
    [ $this, 'widget_callback' ], null, [ '__block_editor_compatible_meta_box' => false ] );

		$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		$helpful_widget = [ 'helpful_widget' => $dashboard['helpful_widget'] ];
		unset( $dashboard['helpful_widget'] );
		$sorted_dashboard = array_merge( $helpful_widget, $dashboard );
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

  /**
   * Dashboard widget content
   */
	public function widget_callback()
  {
		global $post, $wpdb, $helpful;

		wp_enqueue_style('helpful-charts');
		wp_enqueue_script('helpful-widget');

		$html = '';
		$url  = admin_url('?page=helpful');
    $number = get_option('helpful_widget_amount') ? get_option('helpful_widget_amount') : 5;

		$table_name = $wpdb->prefix . 'helpful';

		// Pros
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE pro = %d", 1);
		$pro = $wpdb->get_var($sql);

		// Contras
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE contra = %d", 1);
		$contra = $wpdb->get_var($sql);

    $total = $contra + $pro;

    $pro_percentage = 0;
    if( $pro ) $pro_percentage = ( $pro / $total ) * 100;
    $pro_percentage = number_format($pro_percentage, 2);
    $pro_percentage = (float) str_replace('.00', '', $pro_percentage);

    $contra_percentage = 0;
    if( $contra ) $contra_percentage = ( $contra / $total ) * 100;
    $contra_percentage = number_format($contra_percentage, 2);
    $contra_percentage = (float) str_replace('.00', '', $contra_percentage);

		// Pro Counter
		$html .= '<div class="helpful-counter-pro">';
    if( get_option('helpful_percentages') ) {
 		  $html .= sprintf( '<span>%s%% <small>(%s)</small></span>', $pro_percentage, $pro );
    } else {
 		  $html .= sprintf( '<span>%s <small>(%s%%)</small></span>', $pro, $pro_percentage );
    }
    $html .= '<div class="helpful-counter-info">' . get_option('helpful_column_pro') . '</div>';
		$html .= '</div>';

		// Contra Counter
		$html .= '<div class="helpful-counter-contra">';
    if( get_option('helpful_percentages') ) {
 		  $html .= sprintf( '<span>%s%% <small>(%s)</small></span>', $contra_percentage, $contra );
    } else {
 		  $html .= sprintf( '<span>%s <small>(%s%%)</small></span>', $contra, $contra_percentage );
    }
    $html .= '<div class="helpful-counter-info">' . get_option('helpful_column_contra') . '</div>';
		$html .= '</div>';

    $html .= '<hr />';

    // most helpful posts
    if( get_option('helpful_widget_pro') ) {

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', esc_html_x('Most helpful','widget headline','helpful') );

      $args = [
        'post_type' => 'any',
        'posts_per_page' => intval($number),
        'meta_key' => 'helpful-pro',
        'orderby' => [ 'meta_value_num' => 'DESC' ],
        'fields' => 'ids',
      ];

      $posts = new \WP_Query($args);

      if( $posts->found_posts ) {

        $html .= '<ul>';

        foreach( $posts->posts as $post_id ) {
          $html .= sprintf(
            '<li><a href="%s">%s</a> <span>(%s)</span></li>',
            get_the_permalink($post_id),
            get_the_title($post_id),
            get_post_meta($post_id, 'helpful-pro', true)
          );
        }

        $html .= '</ul>';

      } else {
        $html .= sprintf( '<p>%s</p>', esc_html__('No entries found.','helpful') );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    // least helpful posts
    if( get_option('helpful_widget_contra') ) {

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', esc_html_x('Least helpful','widget headline','helpful') );

      $args = [
        'post_type' => 'any',
        'posts_per_page' => intval($number),
        'meta_key' => 'helpful-contra',
        'orderby' => [ 'meta_value_num' => 'DESC' ],
        'fields' => 'ids',
      ];

      $posts = new \WP_Query($args);

      if( $posts->found_posts ) {

        $html .= '<ul>';

        foreach( $posts->posts as $post_id ) {
          $html .= sprintf(
            '<li><a href="%s">%s</a> <span>(%s)</span></li>',
            get_the_permalink($post_id),
            get_the_title($post_id),
            get_post_meta($post_id, 'helpful-contra', true)
          );
        }

        $html .= '</ul>';

      } else {
        $html .= sprintf( '<p>%s</p>', esc_html__('No entries found.','helpful') );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    /* most helpful recent posts */

    if( get_option('helpful_widget_pro_recent') ) {

      // results
      $sql = $wpdb->prepare("SELECT post_id, time FROM $table_name WHERE pro = %d ORDER BY time DESC LIMIT %d", 1, $number);
      $posts = $wpdb->get_results($sql);

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', esc_html_x('Recently helpful','widget headline','helpful') );

      if( !empty($posts) ) {

      $html .= '<ul>';

        foreach( $posts as $post ) {

          $time = strtotime($post->time);

          $html .= sprintf(
            '<li><a href="%s">%s</a><br><span>%s</span></li>',
            get_the_permalink($post->post_id),
            get_the_title($post->post_id),
            sprintf( esc_html_x('%s ago', 'time difference', 'helpful'), human_time_diff($time) )
          );
        }

      $html .= '</ul>';

      } else {
        $html .= sprintf( '<p>%s</p>', esc_html__('No entries found.','helpful') );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    // least helpful recent posts
    if( get_option('helpful_widget_contra_recent') ) {

      // results
      $sql = $wpdb->prepare("SELECT post_id, time FROM $table_name WHERE contra = %d ORDER BY time DESC LIMIT %d", 1, $number);
      $posts = $wpdb->get_results($sql);

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', esc_html_x('Recently unhelpful','widget headline','helpful') );

      if( !empty($posts) ) {

      $html .= '<ul>';

        foreach( $posts as $post ) {

          $time = strtotime($post->time);

          $html .= sprintf(
            '<li><a href="%s">%s</a><br><span>%s</span></li>',
            get_the_permalink($post->post_id),
            get_the_title($post->post_id),
            sprintf( esc_html_x('%s ago', 'time difference', 'helpful'), human_time_diff($time) )
          );
        }

      $html .= '</ul>';

      } else {
        $html .= sprintf( '<p>%s</p>', esc_html__('No entries found.','helpful') );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    // feedback recent posts
    if( get_option('helpful_feedback_widget') ) {

      // results
      $args = [
        'post_type' => 'helpful_feedback',
        'posts_per_page' => $number,
        'post_status' => 'publish',
        'fields' => 'ids',
      ];

      $feedback = new \WP_Query($args);

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', esc_html_x('Recent Feedback','widget headline','helpful') );

      if( $feedback->found_posts ) {

      $html .= '<ul>';

        foreach( $feedback->posts as $post_id ) {

          $time = get_the_time('U', $post_id);
          $url = admin_url( sprintf('post.php?post=%s&action=edit', $post_id) );

          if( get_option('helpful_feedback_widget_overview') )
            $url = admin_url( 'edit.php?post_type=helpful_feedback' );

          $html .= sprintf(
            '<li><a href="%s">%s</a><br><span>%s</span></li>',
            $url,
            get_the_title($post_id),
            sprintf( esc_html_x('%s ago', 'time difference', 'helpful'), human_time_diff($time) )
          );
        }

      $html .= '</ul>';

      } else {
        $html .= sprintf( '<p>%s</p>', esc_html__( 'No feedback found.', 'helpful' ) );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    $html .= '<div class="helpful-footer">';

		// credits link
		if( get_option( 'helpful_credits' ) ) {
      $credits = sprintf('<a href="%s" target="_blank" rel="nofollow">%s</a>', $helpful['credits']['url'], $helpful['credits']['name']);
  		$html .= sprintf('<div class="helpful-credits">%s</div>', $credits);
    }

    $url = admin_url('admin.php?page=helpful');

		// settings link
		$html .= '<div class="helpful-settings">';
		$html .= sprintf( '<a href="%s" title="%s">', esc_url($url), esc_html_x( 'Settings', 'link title dashboard widget', 'helpful' ) );
		$html .= '<span class="dashicons dashicons-admin-generic"></span>';
		$html .= '</a>';
		$html .= '</div>';

    $html .= '</div>';

		print $html;
	}

  /**
   * Register columns on admin pages
   * @return string
   */
	public function register_columns()
  {
		$post_types = get_option('helpful_post_types');
		if( isset($post_types) ) {
			foreach( $post_types as $post_type ) {
        $post_type = esc_attr($post_type);
				add_filter( 'manage_edit-' . $post_type . '_columns', array( $this, 'columns' ), 10 );
			}
		}
	}

  /**
   * Set column titles
   * @param array $defaults defatul columns
   * @return string
   */
	public function columns( $defaults )
  {
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
	public function register_columns_content()
  {
		$post_types = get_option('helpful_post_types');
		if( isset($post_types) ) {
			foreach( $post_types as $post_type ) {
        $post_type = esc_attr($post_type);
				add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
			}
		}
	}

  /**
   * Columns callback
   * @return string
   */
	public function columns_content( $column_name, $post_id )
  {
		if ( 'helpful-pro' == $column_name ) {
			$pro = get_post_meta( $post_id, 'helpful-pro', true );
  		$contra = get_post_meta( $post_id, 'helpful-contra', true );

      $pro = $pro ? (int) $pro : 0;
      $contra = $contra ? (int) $contra : 0;

      $percent = 0;
      if( $pro !== 0 ) {
        $percent = ( $pro / ( $pro + $contra ) ) * 100;
      }

      $percent = number_format($percent, 2);
      $percent = (float) str_replace('.00', '', $percent);

      if( get_option('helpful_percentages') ) {
			  printf( '<span class="hide-on-hover">%s%%</span><span class="show-on-hover">%s</span>', esc_html($percent), intval($pro) );
      } else {
			  printf( '<span class="hide-on-hover">%s</span><span class="show-on-hover">%s%%</span>', intval($pro), esc_html($percent) );
      }
		}

		if ( 'helpful-contra' == $column_name ) {
			$pro = get_post_meta( $post_id, 'helpful-pro', true );
			$contra = get_post_meta( $post_id, 'helpful-contra', true );

      $pro = $pro ? (int) $pro : 0;
      $contra = $contra ? (int) $contra : 0;

      $percent = 0;
      if( $contra !== 0 ) {
        $percent = ( $contra / ( $pro + $contra ) ) * 100;
      }

      $percent = number_format($percent, 2);
      $percent = (float) str_replace('.00', '', $percent);

      if( get_option('helpful_percentages') ) {
			  printf( '<span class="hide-on-hover">%s%%</span><span class="show-on-hover">%s</span>', esc_html($percent), intval($contra) );
      } else {
			  printf( '<span class="hide-on-hover">%s</span><span class="show-on-hover">%s%%</span>', intval($contra), esc_html($percent) );
      }
		}
	}

  /**
   * Register sortable columns
   * @return string
   */
	public function register_sortable_columns()
  {
		$post_types = get_option('helpful_post_types');
		if( isset($post_types) ) {
			foreach( $post_types as $post_type ) {
        $post_type = esc_attr($post_type);
				add_filter( 'manage_edit-' . $post_type . '_sortable_columns', [ $this, 'sortable_columns' ] );
			}
		}
	}

  /**
   * Set sortable columns
   * @return string
   */
	public function sortable_columns($sortable_columns)
  {
		$sortable_columns[ 'helpful-pro' ] = 'helpful-pro';
   	$sortable_columns[ 'helpful-contra' ] = 'helpful-contra';
		return $sortable_columns;
	}

  /**
   * Make values sortable in columns
   * @param object $query current query
   * @return string
   */
	public function make_sortable_columns( $query )
  {
		if( $query->is_main_query() && ($orderby = $query->get('orderby')) ) {
			switch( $orderby ) {
				case 'helpful-pro':
  				$query->set( 'meta_key', 'helpful-pro' );
  				$query->set( 'orderby', 'meta_value' );
          break;
				case 'helpful-contra':
  				$query->set( 'meta_key', 'helpful-contra' );
  				$query->set( 'orderby', 'meta_value' );
          break;
      }
		}
	}

  /**
   * Register helpful themes
   * @param array $query default themes
   * @return array
   */
	public function themes( $themes )
  {
		$themes = [
			'base' 		=> esc_html_x( 'Base', 'theme name', 'helpful' ),
			'dark' 		=> esc_html_x( 'Dark', 'theme name', 'helpful' ),
			'minimal' => esc_html_x( 'Minimal', 'theme name', 'helpful' ),
			'flat' 		=> esc_html_x( 'Flat', 'theme name', 'helpful' ),
      'simple'  => esc_html_x( 'Simple', 'theme name', 'helpful' ),
			'clean'		=> esc_html_x( 'Clean', 'theme name', 'helpful' ),
			'theme'		=> esc_html_x( 'Theme', 'theme name', 'helpful' ),
		];

    $themes = apply_filters('helpful_themes', $themes);
		return $themes;
	}

  /**
   * Custom css for admin tables
   * @return string
   */
  public function table_css()
  {
    print '<style>
    tr .hide-on-hover {display:block}
    tr:hover .hide-on-hover {display:none}
    tr .show-on-hover {display:none}
    tr:hover .show-on-hover {display:block}
    </style>';
  }

  /**
   * Add meta box
   * @return string
   */
  public function add_meta_box()
  {
    $post_types = get_option('helpful_post_types');
    if( isset($post_types) )
      add_meta_box( 'helpful-meta-box', esc_html__( 'Helpful', 'meta box name', 'helpful' ), [$this, 'callback_meta_box'], $post_types );
  }

  /**
   * Meta box callback
   * @return string
   */
  public function callback_meta_box()
  {
    global $post;
    
		$pro = get_post_meta( $post->ID, 'helpful-pro', true );
  	$contra = get_post_meta( $post->ID, 'helpful-contra', true );

    $pro = $pro ? (int) $pro : 0;
    $contra = $contra ? (int) $contra : 0;

    $pro_percent = 0;
    if( $pro !== 0 ) {
      $pro_percent = ( $pro / ( $pro + $contra ) ) * 100;
    }

    $pro_percent = number_format($pro_percent, 2);
    $pro_percent = (float) str_replace('.00', '', $pro_percent);

    $contra_percent = 0;
    if( $contra !== 0 ) {
      $contra_percent = ( $contra / ( $pro + $contra ) ) * 100;
    }

    $contra_percent = number_format($contra_percent, 2);
    $contra_percent = (float) str_replace('.00', '', $contra_percent);

    wp_nonce_field( 'helfpul_remove_single', 'helfpul_remove_single_nonce' );
    include( HELPFUL_PATH . 'templates/meta_box.php' );
  }

  /**
   * Save meta box data
   * @param integer $post_id current post id
   * @return string
   */
  public function save_meta_box($post_id)
  {
    if ( 
      isset( $_POST['helfpul_remove_single_nonce'] ) && 
      wp_verify_nonce( $_POST['helfpul_remove_single_nonce'], 'helfpul_remove_single' ) &&
      1 == $_POST['helfpul_remove_single']
    ) {
      $this->remove_single($post_id);
    }
  }

  /**
   * Remove helpful data from current post
   * @param integer $post_id current post id
   * @return string
   */
  public function remove_single($post_id)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . 'helpful';

    $wpdb->delete( $table_name, ['post_id' => $post_id] );
    
    if( get_post_meta( $post_id, 'helpful-pro' ) ) {
      delete_post_meta( $post_id, 'helpful-pro' );
    }

    if( get_post_meta( $post_id, 'helpful-contra' ) ) {
      delete_post_meta( $post_id, 'helpful-contra' );
    }
    
    if( get_post_meta( $post_id, 'helfpul_remove_single' ) ) {
      delete_post_meta( $post_id, 'helfpul_remove_single' );
    }
  }
}
