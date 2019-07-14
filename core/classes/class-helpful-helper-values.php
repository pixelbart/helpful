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
	 * @var $table_helpful
	 */
	protected static $table_helpful = 'helpful';

	/**
	 * Database table name for helpful feedback
	 *
	 * @var $table_feedback
	 */
	protected static $table_feedback = 'helpful_feedback';

	/**
	 * Defaults values for shortcodes.
	 *
	 * @global $helpful, $post
	 *
	 * @return array
	 */
	public static function getDefaults() {
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
			'exists'               => ( self::checkUser( $user_id, $post_id ) ? 1 : 0 ),
			'exists_text'          => self::convertTags( get_option( 'helpful_exists' ), $post_id ),
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
	public static function convertTags( $string, $post_id ) {
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
	public static function get_tags() {
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
	 * @return string
	 */
	public static function getUser() {
		if ( isset( $_COOKIE['helpful_user'] ) ) {
			return $_COOKIE['helpful_user'];
		}

		if ( isset( $_SESSION['helpful_user'] ) ) {
			return $_SESSION['helpful_user'];
		}

		return null;
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
	public static function checkUser( $user_id, $post_id ) {
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
	public static function insertPro( $user, $post_id ) {
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

		if ( get_option( 'helpful_percentages' ) ) {
			update_post_meta( $post_id, 'helpful-pro', Helpful_Helper_Stats::getPro( $post_id, true ) );
		}

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
	public static function insertContra( $user, $post_id ) {
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

		if ( get_option( 'helpful_percentages' ) ) {
			update_post_meta( $post_id, 'helpful-contra', Helpful_Helper_Stats::getContra( $post_id, true ) );
		}

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
	public static function removeData( $post_id ) {
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
	 * @return mixed
	 */
	public static function tableExists( $table_name ) {
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
	 * @return boolean
	 */
	public static function setupHelpfulTable() {
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
	 * @return boolean
	 */
	public static function setupHelpfulFeedbackTable() {
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
}
