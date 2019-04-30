<?php
namespace HelpfulPlugin;
if ( !defined( 'ABSPATH' ) ) exit;
use \WP_Query as WP_Query;
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

  // install database table
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
	}

	// truncate table and delete post metas
	public function truncate()
  {
		if( get_option('helpful_uninstall') ) {

  		global $wpdb, $helpful;

  		$table_name = $wpdb->prefix . 'helpful';
  		$wpdb->query("TRUNCATE TABLE $table_name");
  		update_option( 'helpful_uninstall', false );

      $args = [
        'post_type' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
      ];

      $posts = new WP_Query($args);

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

  		$helpful['system'] = __( 'The database table was successfully reset!', 'helpful' );
    }
	}

	// register Menu
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

	// admin page callback
	public function settings_page()
  {
		include( plugin_dir_path( HELPFUL_FILE ) . 'templates/backend.php' );
	}

  // register Settings
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

	// default values for settings
	public function default_options( $bool = false )
  {
		if( false == $bool ) {
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

	// register Tabs
	public function register_tabs()
  {
		global $helpful;

    $tabs = [
      'text' => [
        'class' => $helpful['tab'] == 'text' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=text',
        'name'  => _x( 'Texts', 'tab name', 'helpful' ),
      ],
      'general' => [
        'class' => $helpful['tab'] == 'detail' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=detail',
        'name'  => _x( 'Details', 'tab name', 'helpful' ),
      ],
      'design' => [
        'class' => $helpful['tab'] == 'design' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=design',
        'name'  => _x( 'Design', 'tab name', 'helpful' ),
      ],
      'feedback' => [
        'class' => $helpful['tab'] == 'feedback' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=feedback',
        'name'  => _x( 'Feedback', 'tab name', 'helpful' ),
      ],
      'system' => [
        'class' => $helpful['tab'] == 'system' ? 'helpful-tab helpful-tab-active' : 'helpful-tab',
        'href'  => '?page=helpful&tab=system',
        'name'  => _x( 'System', 'tab name', 'helpful' ),
      ],
    ];

    $tabs = apply_filters('helpful_admin_tabs', $tabs);

    foreach( $tabs as $tab ) {
      printf(
        '<li class="%s"><a href="%s" class="helpful-tab-link">%s</a></li>',
        $tab['class'], $tab['href'], $tab['name']
      );
    }
	}

	// setup tabs content
	public function setup_tabs()
  {
		foreach ( glob( plugin_dir_path( HELPFUL_FILE ) . "core/tabs/*.php" ) as $file ) {
			include_once $file;
		}
	}

	// backend enqueue scripts
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
		if( 'toplevel_page_helpful' == $screen->base ) {

      $file = plugins_url( 'core/assets/css/admin.css', HELPFUL_FILE );
			wp_enqueue_style ( 'helpful-backend', $file, [], HELPFUL_VERSION );

      // Register theme for preview
      foreach ( glob( plugin_dir_path( HELPFUL_FILE ) . 'core/assets/themes/*.css' ) as $theme ) {

        $name = str_replace( array('.css'), '', basename( $theme, PHP_EOL ) );
        $file = plugin_dir_path( HELPFUL_FILE ) . 'core/assets/themes/' . $name . '.css';

        if( file_exists( $file ) ) {
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
            wp_json_encode( $settings )
          );

          wp_add_inline_script('code-editor', $script);
        }
      } /* end wp enqueue_code_editor */
    }
	}

	// backend informations container
	public function sidebar()
  {
		global $helpful;

		$html  = '<h4>' . _x( 'Links & Support', 'headline sidebar options page', 'helpful' ) . '</h4>';
		$html .= '<p>' . _x( 'You have an question?', 'description sidebar options page', 'helpful' ) . '</p>';
		$html .= '<ul>';

		$html .= sprintf(
			'<li><a href="%s" target="_blank">%s</a></li>',
			'https://wordpress.org/plugins/helpful/#developers',
			_x( 'Changelogs', 'link text sidebar options page', 'helpful' )
		);

		$html .= sprintf(
			'<li><a href="%s" target="_blank">%s</a></li>',
			'https://wordpress.org/support/plugin/helpful',
			_x( 'Help & Support', 'link text sidebar options page', 'helpful' )
		);

		$html .= sprintf(
			'<li><a href="%s" target="_blank">%s</a></li>',
			'https://wordpress.org/support/plugin/helpful/reviews/#new-post',
			_x( 'Rate this plugin', 'link text sidebar options page', 'helpful' )
		);

    $html .='</ul>';

    $html .= sprintf( '<h4>%s</h4>', _x( 'Troubleshooting Information', 'text sidebar options page', 'helpful' ) );

		$html .= '<ul>';

    if( function_exists('phpversion') ) {
      $text = _x('PHP Version: %s', 'info sidebar options page', 'helpful');
      $text = sprintf($text, phpversion());
  		$html .= sprintf('<li>%s</li>', $text);
    }

    if( HELPFUL_VERSION ) {
      $text = _x('Helpful Version: %s', 'info sidebar options page', 'helpful');
      $text = sprintf($text, HELPFUL_VERSION);
  		$html .= sprintf('<li>%s</li>', $text);
    }

    $agent = $_SERVER['HTTP_USER_AGENT'];

    if( isset($agent) && function_exists('get_browser') ):
      if( get_browser($agent, true) ):
        $browser = get_browser($agent, true);
        $text = _x('Browser: %s', 'info sidebar options page', 'helpful');
        $text = sprintf($text, $browser['parent']);
    		$html .= sprintf('<li>%s</li>', $text);
      endif;
    endif;

    $html .='</ul>';

		echo $html;
	}

	// register widget
	public function register_widget()
  {
		if( !get_option('helpful_widget') ) {
			add_action( 'wp_dashboard_setup', [ $this, 'widget' ], 1 );
		}
	}

	// widget
	public function widget()
  {
		global $wp_meta_boxes;

		wp_add_dashboard_widget(
			'helpful_widget',
			_x( 'Helpful', 'headline dashboard widget', 'helpful' ),
			[ $this, 'widget_callback' ],
      null,
      [ '__block_editor_compatible_meta_box' => false ]
		);

		$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		$helpful_widget = [ 'helpful_widget' => $dashboard['helpful_widget'] ];
		unset( $dashboard['helpful_widget'] );
		$sorted_dashboard = array_merge( $helpful_widget, $dashboard );
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

	// widget callback
	public function widget_callback()
  {
		global $post, $wpdb, $helpful;

		wp_enqueue_style('helpful-charts');
		wp_enqueue_script('helpful-widget');

		$html = '';
		$url  = admin_url('?page=helpful');
		$post_types = get_option('helpful_post_types');
    $number = get_option('helpful_widget_amount') ? get_option('helpful_widget_amount') : 5;

		$table_name = $wpdb->prefix . 'helpful';

		// Pros
    $sql = "SELECT COUNT(*) FROM $table_name WHERE pro = 1";
		$p = $wpdb->get_var($sql);

		// Contras
    $sql = "SELECT COUNT(*) FROM $table_name WHERE contra = 1";
		$c = $wpdb->get_var($sql);

    // sum
    $sum = $c + $p;

    // pro percentage
    $pp = 0;

    if( $p ) {
      $pp = ( $p / $sum ) * 100;
    }

    $pp = number_format($pp, 2);
    $pp = (float) str_replace('.00', '', $pp);

    // contra percentage
    $cc = 0;

    if( $c ) {
      $cc = ( $c / $sum ) * 100;
    }

    $cc = number_format($cc, 2);
    $cc = (float) str_replace('.00', '', $cc);

		// Pro Counter
		$html .= '<div class="helpful-counter-pro">';

    if( get_option('helpful_percentages') ) {
 		  $html .= sprintf( '<span>%s%% <small>(%s)</small></span>', $pp, $p );
    } else {
 		  $html .= sprintf( '<span>%s <small>(%s%%)</small></span>', $p, $pp );
    }

    $html .= '<div class="helpful-counter-info">' . get_option('helpful_column_pro') . '</div>';
		$html .= '</div>';

		// Contra Counter
		$html .= '<div class="helpful-counter-contra">';


    if( get_option('helpful_percentages') ) {
 		  $html .= sprintf( '<span>%s%% <small>(%s)</small></span>', $cc, $c );
    } else {
 		  $html .= sprintf( '<span>%s <small>(%s%%)</small></span>', $c, $cc );
    }

    $html .= '<div class="helpful-counter-info">' . get_option('helpful_column_contra') . '</div>';
		$html .= '</div>';

    $html .= '<hr />';

    // most helpful posts
    if( get_option('helpful_widget_pro') ) {

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', _x('Most helpful','widget headline','helpful') );

      $args = [
        'post_type' => 'any',
        'posts_per_page' => $number,
        'meta_key' => 'helpful-pro',
        'orderby' => [ 'meta_value_num' => 'DESC' ],
        'fields' => 'ids',
      ];

      $posts = new WP_Query($args);

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
        $html .= sprintf( '<p>%s</p>', __('No entries found.','helpful') );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    // least helpful posts
    if( get_option('helpful_widget_contra') ) {

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', _x('Least helpful','widget headline','helpful') );

      $args = [
        'post_type' => 'any',
        'posts_per_page' => $number,
        'meta_key' => 'helpful-contra',
        'orderby' => [ 'meta_value_num' => 'DESC' ],
        'fields' => 'ids',
      ];

      $posts = new WP_Query($args);

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
        $html .= sprintf( '<p>%s</p>', __('No entries found.','helpful') );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    // most helpful recent posts
    if( get_option('helpful_widget_pro_recent') ) {

      // results
      $sql = "SELECT post_id, time FROM $table_name WHERE pro = 1 ORDER BY time DESC LIMIT $number";
      $recent_pros = $wpdb->get_results($sql);

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', _x('Recently helpful','widget headline','helpful') );

      if( !empty($recent_pros) ) {

      $html .= '<ul>';

        foreach( $recent_pros as $p ) {

          $time = strtotime($p->time);

          $html .= sprintf(
            '<li><a href="%s">%s</a><br><span>%s</span></li>',
            get_the_permalink($p->post_id),
            get_the_title($p->post_id),
            sprintf( _x('%s ago', 'time difference', 'helpful'), human_time_diff($time) )
          );
        }

      $html .= '</ul>';

      } else {
        $html .= sprintf( '<p>%s</p>', __('No entries found.','helpful') );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    // least helpful recent posts
    if( get_option('helpful_widget_contra_recent') ) {

      // results
      $sql = "SELECT post_id, time FROM $table_name WHERE contra = 1 ORDER BY time DESC LIMIT $number";
      $recent_cons = $wpdb->get_results($sql);

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', _x('Recently unhelpful','widget headline','helpful') );

      if( !empty($recent_cons) ) {

      $html .= '<ul>';

        foreach( $recent_cons as $p ) {

          $time = strtotime($p->time);

          $html .= sprintf(
            '<li><a href="%s">%s</a><br><span>%s</span></li>',
            get_the_permalink($p->post_id),
            get_the_title($p->post_id),
            sprintf( _x('%s ago', 'time difference', 'helpful'), human_time_diff($time) )
          );
        }

      $html .= '</ul>';

      } else {
        $html .= sprintf( '<p>%s</p>', __('No entries found.','helpful') );
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

      $feedback = new WP_Query($args);

      $html .= '<div>';
      $html .= sprintf( '<strong>%s</strong>', _x('Recent Feedback','widget headline','helpful') );

      if( $feedback->found_posts ) {

      $html .= '<ul>';

        foreach( $feedback->posts as $feedback_id ) {

          $time = get_the_time('U', $feedback_id);

          $url = admin_url( sprintf('post.php?post=%s&action=edit', $feedback_id) );

          if( get_option('helpful_feedback_widget_overview') ) {
            $url = admin_url( 'edit.php?post_type=helpful_feedback' );
          }

          $html .= sprintf(
            '<li><a href="%s">%s</a><br><span>%s</span></li>',
            $url,
            get_the_title($feedback_id),
            sprintf( _x('%s ago', 'time difference', 'helpful'), human_time_diff($time) )
          );
        }

      $html .= '</ul>';

      } else {
        $html .= sprintf( '<p>%s</p>', __( 'No feedback found.', 'helpful' ) );
      }

      $html .= '</div>';
      $html .= '<hr />';
    }

    $html .= '<div class="helpful-footer">';

		// credits link
		if( get_option( 'helpful_credits' ) ) {
  		$html .= sprintf(
  			'<div class="helpful-credits">%s</div>',
  			'<a href="https://pixelbart.de" target="_blank" rel="nofollow">Pixelbart</a>'
  		);
    }

    $url = admin_url('admin.php?page=helpful');

		// settings link
		$html .= '<div class="helpful-settings">';
		$html .= sprintf( '<a href="%s" title="%s">', $url, _x( 'Settings', 'link title dashboard widget', 'helpful' ) );
		$html .= '<span class="dashicons dashicons-admin-generic"></span>';
		$html .= '</a>';
		$html .= '</div>';

    $html .= '</div>';

		echo $html;
	}

	// register columns
	public function register_columns()
  {
		$post_types = get_option('helpful_post_types');

		if( $post_types ) {

			foreach( $post_types as $post_type ) {
				add_filter( 'manage_edit-' . $post_type . '_columns', array( $this, 'columns' ), 10 );
			}
		}
	}

	// columns
	public function columns( $defaults )
  {
		global $helpful;

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

	// register columns content
	public function register_columns_content()
  {
		$post_types = get_option('helpful_post_types');

		if( $post_types ) {
			foreach( $post_types as $post_type ) {
				add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
			}
		}
	}

	// columns content
	public function columns_content( $column_name, $post_id )
  {
		if ( 'helpful-pro' == $column_name ) {
			$pros = get_post_meta( $post_id, 'helpful-pro', true );
  		$cons = get_post_meta($post_id, 'helpful-contra', true );

      $pros = $pros ? (int) $pros : 0;
      $cons = $cons ? (int) $cons : 0;

      $percent = 0;
      if( $pros !== 0 ) {
        $percent = ( $pros / ( $pros + $cons ) ) * 100;
      }

      $percent = number_format($percent, 2);
      $percent = (float) str_replace('.00', '', $percent);

      if( get_option('helpful_percentages') ) {
			  printf('<span class="hide-on-hover">%s%%</span><span class="show-on-hover">%s</span>', $percent, intval( $pros ));
      } else {
			  printf('<span class="hide-on-hover">%s</span><span class="show-on-hover">%s%%</span>', intval( $pros ), $percent);
      }
		}

		if ( 'helpful-contra' == $column_name ) {
			$pros = get_post_meta( $post_id, 'helpful-pro', true );
			$cons = get_post_meta($post_id, 'helpful-contra', true );

      $pros = $pros ? (int) $pros : 0;
      $cons = $cons ? (int) $cons : 0;

      $percent = 0;
      if( $cons !== 0 ) {
        $percent = ( $cons / ( $pros + $cons ) ) * 100;
      }

      $percent = number_format($percent, 2);
      $percent = (float) str_replace('.00', '', $percent);

      if( get_option('helpful_percentages') ) {
			  printf('<span class="hide-on-hover">%s%%</span><span class="show-on-hover">%s</span>', $percent, intval( $cons ));
      } else {
			  printf('<span class="hide-on-hover">%s</span><span class="show-on-hover">%s%%</span>', intval( $cons ), $percent);
      }
		}
	}

	// register sortable columns
	public function register_sortable_columns()
  {
		$post_types = get_option('helpful_post_types');
		if( $post_types ) {
			foreach( $post_types as $post_type ) {
				add_filter( 'manage_edit-' . $post_type . '_sortable_columns', [ $this, 'sortable_columns' ] );
			}
		}
	}

	// sortable columns
	public function sortable_columns( $sortable_columns )
  {
		$sortable_columns[ 'helpful-pro' ] = 'helpful-pro';
   	$sortable_columns[ 'helpful-contra' ] = 'helpful-contra';
		return $sortable_columns;
	}

	// make columns values sortable in query
	public function make_sortable_columns( $query )
  {
		if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {

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

	// register themes
	public function themes( $themes )
  {
		// set theme array
		$themes = [
			'base' 		=> _x( 'Base', 'theme name', 'helpful' ),
			'dark' 		=> _x( 'Dark', 'theme name', 'helpful' ),
			'minimal' => _x( 'Minimal', 'theme name', 'helpful' ),
			'flat' 		=> _x( 'Flat', 'theme name', 'helpful' ),
      'simple'  => _x( 'Simple', 'theme name', 'helpful' ),
			'theme'		=> _x( 'Theme', 'theme name', 'helpful' ),
		];

    $themes = apply_filters('helpful_themes', $themes);

		return $themes;
	}

  // write table css
  public function table_css()
  {
    echo '<style>
    tr .hide-on-hover {display:block}
    tr:hover .hide-on-hover {display:none}
    tr .show-on-hover {display:none}
    tr:hover .show-on-hover {display:block}
    </style>';
  }

  // add widget
  public function add_meta_box()
  {
    $post_types = get_option('helpful_post_types');
    add_meta_box( 'helpful-meta-box', __( 'Helpful', 'meta box name', 'helpful' ), [$this, 'callback_meta_box'], $post_types );
  }

  // meta box callback
  public function callback_meta_box()
  {
    global $post;

    
		$pros = get_post_meta( $post->ID, 'helpful-pro', true );
  	$cons = get_post_meta( $post->ID, 'helpful-contra', true );

    $pros = $pros ? (int) $pros : 0;
    $cons = $cons ? (int) $cons : 0;

    $pro_percent = 0;
    if( $pros !== 0 ) {
      $pro_percent = ( $pros / ( $pros + $cons ) ) * 100;
    }

    $pro_percent = number_format($pro_percent, 2);
    $pro_percent = (float) str_replace('.00', '', $pro_percent);

    $contra_percent = 0;
    if( $cons !== 0 ) {
      $contra_percent = ( $cons / ( $pros + $cons ) ) * 100;
    }

    $contra_percent = number_format($contra_percent, 2);
    $contra_percent = (float) str_replace('.00', '', $contra_percent);

    wp_nonce_field( 'helfpul_remove_single', 'helfpul_remove_single_nonce' );

    include( plugin_dir_path( HELPFUL_FILE ) . 'templates/meta_box.php' );
  }

  // meta box save
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

  // remove helpful entries from single
  public function remove_single($post_id)
  {
    global $wpdb, $helpful;

    $table_name = $wpdb->prefix . 'helpful';

    $wpdb->delete( $table_name, array( 'post_id' => $post_id ) );
    
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

  // helper query
  public function query($args)
  {
    $args['fields'] = 'ids';
    
    return new WP_Query($args);
  }
}