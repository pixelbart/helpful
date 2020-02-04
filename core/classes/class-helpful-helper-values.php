<?php
/**
 * Helper for getting stored values in database,
 * for insert pro or contra and for deleting helpful
 * from single post.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */
class Helpful_Helper_Values {

	/**
	 * Database table name for helpful
	 *
	 * @var string
	 */
	protected static $table_helpful = 'helpful';

	/**
	 * Database table name for helpful feedback
	 *
	 * @var string
	 */
	protected static $table_feedback = 'helpful_feedback';

	/**
	 * Defaults values for shortcodes.
	 *
	 * @global $helpful, $post
	 *
	 * @return array
	 */
	public static function getDefaults()
	{
		global $helpful, $post;

		$post_id      = $post->ID;
		$user_id      = self::getUser();
		$credits_html = sprintf(
			'<a href="%s" target="_blank" rel="nofollow">%s</a>',
			$helpful['credits']['url'],
			$helpful['credits']['name']
		);
		$defaults     = [
			'heading'              => self::convertTags( get_option( 'helpful_heading' ), $post_id ),
			'content'              => self::convertTags( get_option( 'helpful_content' ), $post_id ),
			'button_pro'           => get_option( 'helpful_pro' ),
			'button_contra'        => get_option( 'helpful_contra' ),
			'counter'              => ( ! get_option( 'helpful_count_hide' ) ),
			'count_pro'            => Helpful_Helper_Stats::getPro( $post_id ),
			'count_pro_percent'    => Helpful_Helper_Stats::getPro( $post_id, true ),
			'count_contra'         => Helpful_Helper_Stats::getContra( $post_id ),
			'count_contra_percent' => Helpful_Helper_Stats::getContra( $post_id, true ),
			'credits'              => get_option( 'helpful_credits' ),
			'credits_html'         => $credits_html,
			'exists'               => self::checkUser( $user_id, $post_id ) ? 1 : 0,
			'exists_text'          => self::convertTags( get_option( 'helpful_exists' ), $post_id ),
			'post_id'              => $post_id,
		];

		return $defaults;
	}

	/**
	 * Convert tags to elements.
	 *
	 * @param string  $string  text string with tags.
	 * @param integer $post_id post id.
	 *
	 * @return string
	 */
	public static function convertTags( string $string, int $post_id )
	{
		$post   = get_post( $post_id );
		$pro    = Helpful_Helper_Stats::getPro( $post->ID );
		$contra = Helpful_Helper_Stats::getContra( $post->ID );

		$tags   = [
			'{pro}'            => $pro,
			'{contra}'         => $contra,
			'{total}'          => ( (int) $pro + (int) $contra ),
			'{permalink}'      => esc_url( get_permalink( $post->ID ) ),
			'{author}'         => get_the_author_meta( 'display_name', $post->post_author ),
			'{pro_percent}'    => Helpful_Helper_Stats::getPro( $post->ID, true ),
			'{contra_percent}' => Helpful_Helper_Stats::getContra( $post->ID, true ),
		];

		$tags = apply_filters( 'helpful_tags', $tags );

		$string = str_replace( array_keys( $tags ), array_values( $tags ), $string );

		return $string;
	}

	/**
	 * Get available tags for the settings screen
	 *
	 * @return array
	 */
	public static function get_tags()
	{
		return [
			'{pro}',
			'{contra}',
			'{total}',
			'{pro_percent}',
			'{contra_percent}',
			'{permalink}',
			'{author}',
		];
	}

	/**
	 * Get user string
	 *
	 * @return string|null
	 */
	public static function getUser()
	{
		$user = null;

		if ( isset( $_COOKIE['helpful_user'] ) ) {
			$user = sanitize_text_field( $_COOKIE['helpful_user'] );
		}

		if ( isset( $_SESSION['helpful_user'] ) ) {
			$user = sanitize_text_field( $_SESSION['helpful_user'] );
		}

		if ( null === $user ) {
			self::setUser();
			$user = self::getUser();
		}

		return $user;
	}

	/**
	 * Set user string
	 *
	 * @return void
	 */
	public static function setUser()
	{
		$string   = bin2hex( openssl_random_pseudo_bytes( 16 ) );
		$string   = apply_filters( 'helpful_user_string', $string );
		$lifetime = '+30 days';
		$lifetime = apply_filters( 'helpful_user_cookie_time', $lifetime );

		if ( ! session_id() ) {
			session_start();
		}

		if ( ! isset( $_COOKIE['helpful_user'] ) ) {
			setcookie( 'helpful_user', $string, strtotime( $lifetime ) );
		}

		if ( ! isset( $_COOKIE['helpful_user'] ) ) {
			if ( ! isset( $_SESSION['helpful_user'] ) ) {
				$_SESSION['helpful_user'] = $string;
			}
		}
	}

	/**
	 * Check if user has voted on given post.
	 *
	 * @global $wpdb
	 *
	 * @param string  $user_id user id.
	 * @param integer $post_id post id.
	 *
	 * @return boolean
	 */
	public static function checkUser( string $user_id, int $post_id )
	{
		if ( get_option( 'helpful_multiple' ) ) {
			return false;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'helpful';
		$sql        = "
		SELECT user, post_id
		FROM {$table_name}
		WHERE user = %s AND post_id = %d
		";
		$query      = $wpdb->prepare( $sql, $user_id, $post_id );
		$results    = $wpdb->get_results( $query );

		if ( $results ) {
			return true;
		}

		return false;
	}

	/**
	 * Insert helpful pro on single post
	 *
	 * @global $wpdb
	 *
	 * @param string  $user    user identicator.
	 * @param integer $post_id post id.
	 *
	 * @return mixed
	 */
	public static function insertPro( string $user, int $post_id )
	{
		global $wpdb;

		$data       = [
			'time'    => current_time( 'mysql' ),
			'user'    => esc_attr( $user ),
			'pro'     => 1,
			'contra'  => 0,
			'post_id' => intval( $post_id ),
		];
		$table_name = $wpdb->prefix . 'helpful';

		$wpdb->insert( $table_name, $data );

		update_post_meta( $post_id, 'helpful-pro', Helpful_Helper_Stats::getPro( $post_id ) );

		Helpful_Helper_Optimize::clear_cache();

		return $wpdb->insert_id;
	}

	/**
	 * Insert helpful contra on single post
	 *
	 * @global $wpdb
	 *
	 * @param string  $user user identicator.
	 * @param integer $post_id post id.
	 *
	 * @return mixed
	 */
	public static function insertContra( string $user, int $post_id )
	{
		global $wpdb;

		$data       = [
			'time'    => current_time( 'mysql' ),
			'user'    => esc_attr( $user ),
			'pro'     => 0,
			'contra'  => 1,
			'post_id' => absint( $post_id ),
		];
		$table_name = $wpdb->prefix . 'helpful';

		$wpdb->insert( $table_name, $data );

		update_post_meta( $post_id, 'helpful-contra', Helpful_Helper_Stats::getContra( $post_id ) );

		Helpful_Helper_Optimize::clear_cache();

		return $wpdb->insert_id;
	}

	/**
	 * Remove helpful stats from single post.
	 *
	 * @global $wpdb
	 *
	 * @param int $post_id post id.
	 *
	 * @return void
	 */
	public static function removeData( int $post_id )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';
		$wpdb->delete( $table_name, [ 'post_id' => $post_id ] );
		delete_post_meta( $post_id, 'helpful-pro' );
		delete_post_meta( $post_id, 'helpful-contra' );
		delete_post_meta( $post_id, 'helpful_remove_data', 'yes' );

		Helpful_Helper_Optimize::clear_cache();
	}

	/**
	 * Checks if tables exists and creates tables if not
	 *
	 * @global $wpdb
	 *
	 * @param string $table_name database table name.
	 *
	 * @return array
	 */
	public static function tableExists( string $table_name )
	{
		global $wpdb;

		$response = [];
		$table    = $wpdb->base_prefix . $table_name;
		$query    = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) );

		if ( ! $wpdb->get_var( $query ) == $table_name ) {

			if ( self::$table_feedback == $table_name ) {
				$response[] = self::setupHelpfulFeedbackTable();
			}

			if ( self::$table_helpful == $table_name ) {
				$response[] = self::setupHelpfulTable();
			}
		}

		return $response;
	}

	/**
	 * Setup helpful table
	 *
	 * @return string
	 */
	public static function setupHelpfulTable()
	{
		global $wpdb;

		$table_name      = $wpdb->prefix . self::$table_helpful;
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id mediumint(9) DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return sprintf(
			/* translators: %s table name */
			esc_html_x( "Table '%s' has been created.", 'maintenance response', 'helpful' ),
			$table_name
		);
	}

	/**
	 * Setup helpful feedback table
	 *
	 * @return string
	 */
	public static function setupHelpfulFeedbackTable()
	{
		global $wpdb;

		$table_name      = $wpdb->prefix . self::$table_feedback;
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id mediumint(9) DEFAULT NULL,
		message text DEFAULT NULL,
		fields text DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;
		";

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return sprintf(
			/* translators: %s table name */
			esc_html_x( "Table '%s' has been created.", 'maintenance response', 'helpful' ),
			$table_name
		);
	}

	/**
	 * Receive helpful data
	 *
	 * @return array
	 */
	public static function get_data()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'helpful';

		$sql = "SELECT * FROM $table_name";

		$query = $wpdb->get_results( $sql, ARRAY_A );

		$results = [
			'count' => count( $query ),
			'items' => $query,
		];

		return $results;
	}	

	/**
	 * Sync post meta
	 *
	 * @return void
	 */
	public static function sync_post_meta()
	{
		$transient = 'helpful_sync_meta';

		if ( false === ( $query = get_transient( $transient ) ) ) {

			$post_types = get_option( 'helpful_post_types' );

			$args = [
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			];

			$query       = new WP_Query( $args );
			$cache_time  = get_option( 'helpful_cache_time', 'minute' );
			$cache_times = Helpful_Helper_Cache::get_cache_times( false );
			$cache_time  = $cache_times[ $cache_time ];

			set_transient( $transient, $query, $cache_time );

			if ( $query->found_posts ) {
				foreach ( $query->posts as $post_id ) :
					update_post_meta( $post_id, 'helpful-pro', Helpful_Helper_Stats::getPro( $post_id, false ) );
					update_post_meta( $post_id, 'helpful-contra', Helpful_Helper_Stats::getContra( $post_id, false ) );
				endforeach;
			}

			usleep( 100000 );
		}
	}

	/**
	 * Translatable Datatables Language String
	 *
	 * @return array
	 */
	public static function datatables_language_string()
	{
		return [
			'decimal'        => esc_html_x( '', 'datatables decimal', 'helpful' ),
			'emptyTable'     => esc_html_x( 'No data available in table', 'datatables emptyTable', 'helpful' ),
			'info'           => esc_html_x( 'Showing _START_ to _END_ of _TOTAL_ entries', 'datatables info', 'helpful' ),
			'infoEmpty'      => esc_html_x( 'Showing 0 to 0 of 0 entries', 'datatables infoEmpty', 'helpful' ),
			'infoFiltered'   => esc_html_x( '(filtered from _MAX_ total entries)', 'datatables infoFiltered', 'helpful' ),
			'infoPostFix'    => esc_html_x( '', 'datatables infoPostFix', 'helpful' ),
			'thousands'      => esc_html_x( ',', 'datatables thousands', 'helpful' ),
			'lengthMenu'     => esc_html_x( 'Show _MENU_ entries', 'datatables lengthMenu', 'helpful' ),
			'loadingRecords' => esc_html_x( 'Loading...', 'datatables loadingRecords', 'helpful' ),
			'processing'     => esc_html_x( 'Processing...', 'datatables processing', 'helpful' ),
			'search'         => esc_html_x( 'Search:', 'datatables search', 'helpful' ),
			'zeroRecords'    => esc_html_x( 'No matching records found', 'datatables zeroRecords', 'helpful' ),
			'paginate'       => [
				'first'    => esc_html_x( 'First', 'datatables first', 'helpful' ),
				'last'     => esc_html_x( 'Last', 'datatables last', 'helpful' ),
				'next'     => esc_html_x( 'Next', 'datatables next', 'helpful' ),
				'previous' => esc_html_x( 'Previous', 'datatables previous', 'helpful' ),
			],
			'aria'         => [
				'sortAscending'  => esc_html_x( ': activate to sort column ascending', 'datatables sortAscending', 'helpful' ),
				'sortDescending' => esc_html_x( ': activate to sort column descending', 'datatables sortDescending', 'helpful' ),
			],
			'select'       => [
				'rows' => [
					'_' => esc_html_x( '%d rows selected', 'datatables previous', 'helpful' ),
					'0' => esc_html_x( '', 'datatables previous', 'helpful' ),
					'1' => esc_html_x( '1 row selected', 'datatables previous', 'helpful' ),
				],
			],
			'buttons'     => [
				'print'       => esc_html_x( 'Print', 'datatables print', 'helpful' ),
				'colvis'      => esc_html_x( 'Columns', 'datatables colvis', 'helpful' ),
				'copy'        => esc_html_x( 'Copy', 'datatables copy', 'helpful' ),
				'copyTitle'   => esc_html_x( 'Copy to clipboard', 'datatables copyTitle', 'helpful' ),
				'copyKeys'    => esc_html_x(
					'Press <i>ctrl</i> or <i>\u2318</i> + <i>C</i> to copy table<br>to temporary storage.<br><br>To cancel, click on the message or press Escape.',
					'datatables copyKeys',
					'helpful'
				),
				'copySuccess' => [
					'_' => esc_html_x( '%d rows copied', 'datatables copySuccess', 'helpful' ),
					'1' => esc_html_x( '1 row copied', 'datatables copySuccess', 'helpful' ),
				],
				'pageLength' => [
					'-1' => esc_html_x( 'Show all rows', 'datatables pageLength', 'helpful' ),
					'_'  =>  esc_html_x( 'Show %d rows', 'datatables pageLength', 'helpful' ),
				],
			],
		];
	}
}
