<?php
/**
 * ...
 *
 * @package Helpful\Core\Helpers
 * @author  Pixelbart <me@pixelbart.de>
 * @version 4.3.0
 */
namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Values
{
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
	public static function get_defaults()
	{
		global $post;

		$post_id = null;

		if ( ! isset( $post->ID ) ) {
			if ( false !== get_the_ID() ) {
				$post_id = get_the_ID();
			}
		} else {
			$post_id = $post->ID;
		}

		$credits = Helper::get_credits_data();
		$user_id = User::get_user();

		$values = [
			'heading_tag'          => 'h3',
			'heading'              => self::convert_tags( get_option( 'helpful_heading' ), $post_id ),
			'content'              => self::convert_tags( get_option( 'helpful_content' ), $post_id ),
			'button_pro'           => get_option( 'helpful_pro' ),
			'button_contra'        => get_option( 'helpful_contra' ),
			'counter'              => ( ! get_option( 'helpful_count_hide' ) ),
			'count_pro'            => Stats::get_pro( $post_id ),
			'count_pro_percent'    => Stats::get_pro( $post_id, true ),
			'count_contra'         => Stats::get_contra( $post_id ),
			'count_contra_percent' => Stats::get_contra( $post_id, true ),
			'credits'              => get_option( 'helpful_credits' ),
			'credits_html'         => $credits['html'],
			'exists'               => User::check_user( $user_id, $post_id ) ? 1 : 0,
			'exists_text'          => self::convert_tags( get_option( 'helpful_exists' ), $post_id ),
			'post_id'              => $post_id,
		];

		return apply_filters( 'helpful_default_values', $values );
	}

	/**
	 * Convert tags to elements.
	 *
	 * @param string  $string  text string with tags.
	 * @param integer $post_id post id.
	 *
	 * @return string
	 */
	public static function convert_tags( $string, $post_id )
	{
		$pro    = Stats::get_pro( $post_id );
		$contra = Stats::get_contra( $post_id );

		$display_name = '';
		$author_id    = get_post_field( 'post_author', $post_id );

		if ( $author_id ) {
			$display_name = get_the_author_meta( 'display_name', $author_id );
		}

		$tags   = [
			'{pro}'             => $pro,
			'{contra}'          => $contra,
			'{total}'           => ( (int) $pro + (int) $contra ),
			'{permalink}'       => esc_url( get_permalink( $post_id ) ),
			'{author}'          => $display_name,
			'{pro_percent}'     => Stats::get_pro( $post_id, true ),
			'{contra_percent}'  => Stats::get_contra( $post_id, true ),
			'{feedback_form}'   => Feedback::after_vote( $post_id, true ),
			'{feedback_toggle}' => sprintf(
				'<div class="helpful-feedback-toggle-container"><button class="helpful-button helpful-toggle-feedback" type="button" role="button">%s</button><div hidden>%s</div></div>',
				_x( 'Give feedback', 'toggle feedback button', 'helpful' ),
				Feedback::after_vote( $post_id, true )
			),
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
		$tags = [
			'{pro}',
			'{contra}',
			'{total}',
			'{pro_percent}',
			'{contra_percent}',
			'{permalink}',
			'{author}',
		];

		if ( ! Helper::is_feedback_disabled() ) :
			$tags[] = '{feedback_form}';
			$tags[] = '{feedback_toggle}';		
		endif;

		return $tags;
	}

	/**
	 * Insert helpful pro on single post
	 *
	 * @param string  $user    user identicator.
	 * @param integer $post_id post id.
	 *
	 * @return mixed
	 */
	public static function insert_pro( $user, $post_id )
	{
		$status = Votes::insert_vote( $user, $post_id, 'pro' );

		Stats::delete_widget_transient();

		return $status;
	}

	/**
	 * Insert helpful contra on single post
	 *
	 * @param string  $user user identicator.
	 * @param integer $post_id post id.
	 *
	 * @return mixed
	 */
	public static function insert_contra( $user, $post_id )
	{
		$status = Votes::insert_vote( $user, $post_id, 'contra' );

		Stats::delete_widget_transient();

		return $status;
	}

	/**
	 * Remove helpful stats from single post.
	 *
	 * @param int $post_id post id.
	 *
	 * @return void
	 */
	public static function remove_data( $post_id )
	{
		Votes::delete_vote_where( [ 'post_id' => $post_id ] );

		delete_post_meta( $post_id, 'helpful-pro' );
		delete_post_meta( $post_id, 'helpful-contra' );
		delete_post_meta( $post_id, 'helpful_remove_data', 'yes' );

		Optimize::clear_cache();
	}

	/**
	 * Checks if tables exists and creates tables if not
	 *
	 * @param string $table_name database table name.
	 *
	 * @return array
	 */
	public static function table_exists( $table_name )
	{
		return Database::table_exists_or_setup( $table_name );
	}

	/**
	 * Setup helpful table
	 *
	 * @global $wpdb
	 *
	 * @return string
	 */
	public static function setup_database_table()
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
	 * @global $wpdb
	 *
	 * @return string
	 */
	public static function setup_database_feedback_table()
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
		$query = Votes::get_votes( ARRAY_A );

		$results = [
			'count' => 0,
			'items' => [],
		];

		if ( $query ) {
			$results = [
				'count' => count( $query ),
				'items' => $query,
			];
		}

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

			$query       = new \WP_Query( $args );
			$cache_time  = get_option( 'helpful_cache_time', 'minute' );
			$cache_times = Cache::get_cache_times( false );
			$cache_time  = $cache_times[ $cache_time ];

			set_transient( $transient, $query, $cache_time );

			if ( $query->found_posts ) {
				foreach ( $query->posts as $post_id ) :
					update_post_meta( $post_id, 'helpful-pro', Stats::get_pro( $post_id, false ) );
					update_post_meta( $post_id, 'helpful-contra', Stats::get_contra( $post_id, false ) );
				endforeach;
			}

			usleep( 100000 );
		}
	}
}