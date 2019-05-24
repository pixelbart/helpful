<?php
namespace Helpful\Core;
new Base;

class Base
{
  /**
   * Database table name
   */
  public $table_name = 'helpful';

	public function __construct()
  {
    // Init sessions as fallback
    add_action( 'init', [ $this, 'init_sessions' ], 1 );
    
    // Set user cookie
    add_action( 'template_redirect', [ $this, 'set_user_cookie' ] );

		// Add after content
		add_filter( 'the_content', [ $this , 'add_to_content' ] );

		// Enqueue frontend scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_enqueue' ] );

		// Ajax requests: pro
		add_action( 'wp_ajax_helpful_ajax_pro', [ $this, 'helpful_ajax_pro' ] );
		add_action( 'wp_ajax_nopriv_helpful_ajax_pro', [ $this, 'helpful_ajax_pro' ] );

		// Ajax requests: contra
		add_action( 'wp_ajax_helpful_ajax_contra', [ $this, 'helpful_ajax_contra' ] );
		add_action( 'wp_ajax_nopriv_helpful_ajax_contra', [ $this, 'helpful_ajax_contra' ] );

		// Ajax requests: get feedback form
		add_action( 'wp_ajax_helpful_ajax_feedback', [ $this, 'insert_feedback' ] );
		add_action( 'wp_ajax_nopriv_helpful_ajax_feedback', [ $this, 'insert_feedback' ] );

		// Frontend helpers
		add_filter( 'helpful_helpers', [ $this, 'frontend_helpers' ] );

		// after pro message
		add_filter( 'helpful_after_pro', [ $this, 'after_pro' ], 1 );

		// after contra message
		add_filter( 'helpful_after_contra', [ $this, 'after_contra' ], 1 );

    // Frontend add to head (CSS)
    if( get_option( 'helpful_css' ) )
      add_action( 'wp_head', [ $this, 'custom_css' ] );
  }
  
  /**
   * Set users cookie with unique id
   */
  public function set_user_cookie()
  {
    $string  = bin2hex(openssl_random_pseudo_bytes(16));
    $lifetime = '+30 days';

    if( !isset($_COOKIE['helpful_user']) ) {
      setcookie( "helpful_user", $string, strtotime( $lifetime ) );
    }

    if( !isset($_COOKIE['helpful_user']) && !isset($_SESSION['helpful_user']) ) {
      $_SESSION['helpful_user'] = $string;
    }
  }

  /**
   * Init sessions as fallback for cookies
   */
  public function init_sessions() 
  {
    if( !session_id() ) {
      session_start();
    }
  }

  /**
   * Add helpful after post content
   * @param string $content the post content
   * @return string
   */
	public function add_to_content($content)
  {
    if( get_option('helpful_hide_in_content') )
      return $content;

    // check if is frontpage
    $frontpage = is_home() ? true : false;
    $frontpage = is_front_page() ? true : $frontpage;

    // get helpful post types
    $post_types = get_option('helpful_post_types');

		// is single
		if( $post_types && is_singular() && false == $frontpage ) {

			global $post;

      // current post type
			$current = get_post_type( $post );

			if( in_array( $current, $post_types ) ) {

  			ob_start();

        // Get helpful helpers
        $helpful = apply_filters( 'helpful_helpers', [] );

        $default_template = HELPFUL_PATH . 'templates/frontend.php';
        $custom_template  = locate_template('helpful/frontend.php');
    
        // check if custom frontend exists
        if( '' !== $custom_template ) {
          include( $custom_template );
        }
    
        else {
          include( $default_template );
        }

  			$helpful = ob_get_contents();
  			ob_end_clean();

  			// add content after post content
  			$content = $content . $helpful;
      }
		}

		// return the new content
		return $content;
	}

  /**
   * Message shwon after pro vote
   * @return string
   */
	public function after_pro()
  {
		$after = __( 'Thank you for voting.', 'helpful' );

    if( get_option('helpful_after_pro') ) {
      $after = do_shortcode( get_option( 'helpful_after_pro' ) );
    }

		return $after;
	}

  /**
   * Message shwon after contra vote
   * @return string
   */
	public function after_contra()
  {
		$after = __( 'Thank you for voting.', 'helpful' );

    if( get_option('helpful_after_contra') ) {
      $after = do_shortcode( get_option( 'helpful_after_contra' ) );
    }

		return $after;
	}

  /**
   * Ajax callback after pro vote
   * @return string
   */
	public function helpful_ajax_pro()
  {
    check_ajax_referer('helpful');

		// do request if defined
    if( isset($_POST['pro']) && 1 == $_POST['pro'] ) {

      $inputs = [];

      // sanitize inputs
      foreach( $_POST as $key => $value ) {
        $inputs[$key] = sanitize_text_field($value);
      }

      // set args for insert command
      $args = [
        'post_id' => $inputs['post_id'],
        'user'		=> $inputs['user'],
        'pro' 		=> $inputs['pro'],
        'contra'	=> $inputs['contra'],
        'type'    => 'pro',
      ];

      // do and check insert command
      $result = $this->insert( $args );

      if( $result == true ) {

        // get feedback form if option is set
        if( get_option('helpful_feedback_after_pro') ) {
          $this->get_feedback_form( $args );
        }
        else {
          $after = apply_filters( 'helpful_after_pro', '' );
          echo $this->tags_to_helpful( $after, $args['post_id'] );
        }
      }

      else {
        esc_html_e( 'Sorry, an error has occurred.', 'helpful' );
      }
    }	

    wp_die();
  }

  /**
   * Ajax callback after contra vote
   * @return string
   */
	public function helpful_ajax_contra()
  {
    check_ajax_referer('helpful');

     // do requeset if defined
     if( isset($_POST['contra']) && 1 == $_POST['contra'] ) {

      // sanitize inputs
      foreach( $_POST as $key => $value ) {
        $inputs[$key] = sanitize_text_field($value);
      }

      // set args for insert command
      $args = array(
        'post_id' => $inputs['post_id'],
        'user'		=> $inputs['user'],
        'pro' 		=> $inputs['pro'],
        'contra'	=> $inputs['contra'],
        'type'    => 'contra',
      );

      // do and check insert command
      $result = $this->insert( $args );

      if( true == $result ) {

        // get feedback form if option is set
        if( get_option('helpful_feedback_after_contra') ) {
          $this->get_feedback_form($args);
        }
        else {
          $after = apply_filters( 'helpful_after_contra', '' );
          echo $this->tags_to_helpful( $after, $args['post_id'] );
        }
      }

      else {
        esc_html_e( 'Sorry, an error has occurred.', 'helpful' );
      }
    }

    wp_die();
  }

  /**
   * Feedback form after vote
   * @param array $args filled with vote informations
   * @return string
   */
  public function get_feedback_form($args)
  {

    $default_template = HELPFUL_PATH . 'templates/feedback.php';
    $custom_template  = locate_template('helpful/feedback.php');

    // check if custom frontend exists
    if( '' !== $custom_template ) {
      include $custom_template;
    }

    else {
      include $default_template;
    }
  }

  /**
   * Insert feedback after feedback form submit
   * @return string
   */
  public function insert_feedback()
  {
    check_ajax_referer('helpful_feedback');

    $response = '';
    $fields = [];

    // sanitize request values
    foreach( $_POST as $key => $value ) {
      $fields[$key] = sanitize_text_field($value);
    }

    // insert feedback
    $post_title = esc_html_x('Negative feedback for %s', 'feedback post title info', 'helpful');

    if( 'pro' == $fields['type'] ) {
      $post_title = esc_html_x('Positive feedback for %s', 'feedback post title info', 'helpful');
    }

    // check types and save type
    $type = esc_html_x('Contra', 'feedback type', 'helpful');

    if( 'pro' == $fields['type'] ) {
      $type = esc_html_x('Pro', 'feedback type', 'helpful');
    }

    $post_title = sprintf($post_title, get_the_title($fields['post_id']));

    $feedback = [
      'post_title'   => wp_strip_all_tags($post_title),
      'post_content' => $fields['post_content'],
      'post_status'  => 'publish',
      'post_type'    => 'helpful_feedback',
      'meta_input'   => [
        'post_id'     => $fields['post_id'],
        'type'        => $type,
        'browser'     => 'none',
        'platform'    => 'none',
        'language'    => 'none',
      ],
    ];

    // save user language
    $language = sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_LANGUAGE']));

    if( isset($language) && '' !== $language ) {
      $language = explode(',', $language);
      $feedback['meta_input']['language'] = $language[0];
    }

    // save user browser
    if( function_exists('get_browser') ) {
      $browser = get_browser(null, true);
      $feedback['meta_input']['browser'] = $browser['parent'];
      $feedback['meta_input']['platform'] = $browser['platform'];
    }

    // check if content is blacklisted
    if( !helpful_backlist_check($feedback['post_content']) ) {
      wp_insert_post( $feedback );
    }

    // after contra      
    $after = apply_filters( 'helpful_after_contra', false );
    $response = $this->tags_to_helpful( $after, $fields['post_id'] );

    // after pro
    if( 'pro' == $fields['type'] ) {
      $after = apply_filters( 'helpful_after_pro', false );
      $response = $this->tags_to_helpful( $after, $fields['post_id'] );
    }

    echo $response;
    wp_die();
  }

  /**
   * Enqueue scripts for frontend
   * @return string
   */
	public function frontend_enqueue()
  {
    // Frontend CSS
    if( !get_option( 'helpful_theme' ) ) {
      update_option( 'helpful_theme', 'base' );
    }

    // default path css
    $theme_name = 'base';
    $theme_url = plugins_url( 'core/assets/themes/' . get_option( 'helpful_theme' ) . '.css', HELPFUL_FILE );

    // custom path css
    // located in wordpress-theme/helpful/theme.css
    if( 'theme' == get_option('helpful_theme') ) {

      // custom css theme
      $file = get_template_directory() . '/helpful/theme.css';

      // check if theme exists
      if( file_exists($file) ) {
        $theme_url = get_template_directory_uri() . '/helpful/theme.css';
      } else {
        $theme_url = plugins_url( "core/assets/themes/$theme_name.css", HELPFUL_FILE );
      }
    }

    // frontend style
    wp_enqueue_style( 'helpful-frontend', $theme_url, [], HELPFUL_VERSION );

    // frontend js
    $file = plugins_url( 'core/assets/js/frontend.js', HELPFUL_FILE );
    wp_enqueue_script( 'helpful-frontend', $file, ['jquery'], HELPFUL_VERSION,	true );

    // frontend js variables
    $vars = [
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'version' => HELPFUL_FILE,
      'user_id' => $this->get_current_user(),
    ];

    wp_localize_script( 'helpful-frontend', 'helpful', $vars );
	}

  /**
   * Helpers for frontend
   * @param string $content post content
   * @return string
   */
	public function frontend_helpers( $content )
  {
		global $post, $helpful;
		$post_id = $post->ID;

    $credits_link = sprintf( '<a href="%s" target="_blank" rel="nofollow">%s</a>', $helpful['credits']['url'], $helpful['credits']['name'] );
    $credits_text = esc_html_x( 'Powered by %s', 'helpful credits', 'helpful' );
    $credits_text = sprintf($credits_text, $credits_link);
		$credits_text = sprintf( '<div class="helpful-credits">%s</div>', $credits_text	);

    // class
    $class = 'helpful';

    if( get_option('helpful_theme') ) {
      $class .= ' helpful-theme-' . get_option('helpful_theme');
    }

    if( !$this->is_checked(get_option('helpful_count_hide')) ) {
      $class .= ' helpful_no_counter';
    }

    if( !get_option('helpful_content') ) {
      $class .= ' helpful_no_content';
    }

    // custom css theme
    $file = get_stylesheet_directory_uri() . '/helpful/theme.csss';

    // check if custom theme exists
    if( 'theme' == get_option('helpful_theme') && !file_exists($file) ) {
      $class = 'helpful helpful-theme-base';
    }

		// options
		$credits = get_option('helpful_credits') ? $credits_text : '';
		$heading = get_option('helpful_heading');
		$content = get_option('helpful_content');
		$pro = get_option('helpful_pro');
		$contra = get_option('helpful_contra');
		$hide_counts = get_option('helpful_count_hide');

		// md5 IP
		$user = $this->get_current_user();

		// get counts
    $count_pro = get_post_meta( $post_id, 'helpful-pro', true );
    $count_con = get_post_meta( $post_id, 'helpful-contra', true );

		$count_pro = $count_pro ? $count_pro : 0;
		$count_con = $count_con ? $count_con : 0;

		$count_pro = !get_option('helpful_count_hide') ? sprintf('<span class="counter">%s</span>', $count_pro) : '';
		$count_con = !get_option('helpful_count_hide') ? sprintf('<span class="counter">%s</span>', $count_con) : '';

    $nonce = wp_create_nonce('helpful');

		// markup btn pro
		$btn_pro = '<div class="helpful-pro" ';
		$btn_pro .= 'data-id="' . $post_id . '" ';
    $btn_pro .= 'data-user="' . $user . '" ';
    $btn_pro .= 'data-nonce="' . $nonce . '" ';
		$btn_pro .= 'data-pro="1" ';
		$btn_pro .= 'data-contra="0">';
		$btn_pro .= $pro . $count_pro;
		$btn_pro .= '</div>';

		// markup btn contra
		$btn_con = '<div class="helpful-con" ';
		$btn_con .= 'data-id="' . $post_id . '" ';
		$btn_con .= 'data-user="' . $user . '" ';
    $btn_con .= 'data-nonce="' . $nonce . '" ';
		$btn_con .= 'data-pro="0" ';
		$btn_con .= 'data-contra="1">';
		$btn_con .= $contra . $count_con;
		$btn_con .= '</div>';

		// set array for frontend template
		$content = [
			'class' => esc_attr($class),
			'credits' => $credits,
			'heading' => $this->tags_to_helpful( $heading, $post_id ),
			'content' => nl2br( $this->tags_to_helpful( $content, $post_id ) ),
			'button-pro' => $btn_pro,
			'button-contra' => $btn_con,
			'exists' => $this->check( $post_id, $user ),
			'exists-text'	=> nl2br( $this->tags_to_helpful( get_option('helpful_exists'), $post_id ) ),
			'exists-hide'	=> $this->is_checked(get_option('helpful_exists_hide')),
		];

		return $content;
	}

  /**
   * Convert tags to helpful strings
   * @param string $string
   * @param integer $post_id
   * @return string
   */
	public function tags_to_helpful( $string, $post_id )
  {
    $post = get_post($post_id);

    $author_name = get_the_author_meta( 'display_name', $post->post_author );

		$pro = get_post_meta( $post_id, 'helpful-pro', true );
		$pro = $pro ? $pro : 0;

		$contra = get_post_meta( $post_id, 'helpful-contra', true );
    $contra = $contra ? $contra : 0;
    
    $permalink = esc_url(get_permalink($post_id));

		$string = str_replace( '{pro}', intval($pro), $string );
		$string = str_replace( '{contra}', intval($contra), $string );
    $string = str_replace( '{permalink}', $permalink, $string );
    $string = str_replace( '{author}', $author_name, $string );

		return $string;
	}

  /**
   * Insert vote into table and post meta
   * @param array $args
   * @return bool
   */
	public function insert( $args = [] )
  {
		if( empty($args) ) return false;
		if( !$args['user'] ) return false;
		if( !$args['post_id'] ) return false;

		global $wpdb;

		$user = $args['user'];
		$pro = $args['pro'] == 1 ? 1 : 0;
		$contra = $args['contra'] == 1 ? 1 : 0;
		$post_id = $args['post_id'];

		$table_name = $wpdb->prefix . $this->table_name;

    if( !$this->is_checked(get_option('helpful_multiple')) ) {
      $sql = $wpdb->prepare("SELECT post_id, user FROM $table_name WHERE user = %s AND post_id = %d", $user, $post_id);
  		$check = $wpdb->get_results($sql);
      if( $check ) return true;
    }

    $values = [
      'time' 		=> current_time( 'mysql'),
      'user' 		=> $user,
      'pro' 		=> $pro,
      'contra' 	=> $contra,
      'post_id' => $post_id,
    ];

		$wpdb->insert( $table_name, $values );

		// insert pro in post meta
		if( 1 == $pro ) {
			$current = (int) get_post_meta( $post_id, 'helpful-pro', true );
			$current = isset($current) ? $current+1 : 1;
			update_post_meta( $post_id, 'helpful-pro', $current );
		}

		// insert contra in post meta
		if( 1 == $contra ) {
			$current = (int) get_post_meta( $post_id, 'helpful-contra', true );
			$current = isset($current) ? $current+1 : 1;
			update_post_meta( $post_id, 'helpful-contra', $current );
		}

		return true;
	}

  /**
   * Check if current user has already vote
   * @param integer $post_id
   * @param string $user
   * @return bool
   */
	public function check( $post_id, $user )
  {
		if( !$post_id ) return false;
		if( !$user ) return false;

    // hide helpful if user has already voted on entire website
    if( $this->is_checked(get_option('helpful_only_once')) ) {
      return true;
    }

    // users can vote multiple times
    if( $this->is_checked(get_option('helpful_multiple')) ) {
      return false;
    }

		global $wpdb;

		// table
		$table_name = $wpdb->prefix . $this->table_name;

    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d AND user = %s", $post_id, $user);
		$result = $wpdb->get_row($sql);

		if( $result ) return true;

		return $result;
	}

  /**
   * Add custom css to admin_head
   */
	public function custom_css()
  {
    // do_action('helpful_admin_inline_css');
    $css = '<style id="helpful-inline-css" type="text/css">';
    $css .= wp_strip_all_tags(get_option('helpful_css'));
    $css .= '</style>';
    print $css;
	}

  /**
   * Generate unique user id and store id in a cookie
   * @return string
   */
  public function get_current_user() 
  {
    if( !isset($_CCOKIE['helpful_user']) && isset($_SESSION['helpful_user']) ) {
      return $_SESSION['helpful_user'];
    }
    
    return $_COOKIE['helpful_user'];
  }

  /**
   * Helper for values from checkboxes
   * @return bool
   */
  public function is_checked($value, $default = 'on')
  {
    return $value == $default ? true : false;
  }
}
