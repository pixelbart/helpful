<?php
/**
 * Adding helpful to admin tables and make them sortable.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 4.0.0
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpful_Table
{
	/**
	 * Instance
	 *
	 * @var Helpful_Table
	 */
	public static $instance;

	/**
	 * Set instance and fire class
	 *
	 * @return Helpful_Table
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action( 'admin_init', [ &$this, 'init_columns' ] );
	}

	/**
	 * Register columns on admin pages
	 *
	 * @global $pagenow
	 *
	 * @return void
	 */
	public function init_columns()
	{
		global $pagenow;

		$post_types = get_option( 'helpful_post_types' );
		$hide_cols  = get_option( 'helpful_hide_admin_columns' );

		if ( isset( $hide_cols ) && 'on' === $hide_cols ) {
			return;
		}

		if ( ! isset( $post_types ) || ! is_array( $post_types ) ) {
			return;
		}
		
		$type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : 'post';

		if ( ! in_array( $type, $post_types ) ) {
			return;
		}

		foreach ( $post_types as $type ) :
			if ( is_admin() && 'edit.php' === $pagenow ) {
				add_filter( 'manage_' . $type . '_posts_columns', [ &$this, 'register_columns' ] );
				add_action( 'manage_' . $type . '_posts_custom_column', [ &$this, 'populate_columns' ], 10, 2 );
				add_filter( 'manage_edit-' . $type . '_sortable_columns', [ &$this, 'register_sortable_columns' ] );
				add_action( 'pre_get_posts', [ &$this, 'sort_columns_query' ] );
			}
		endforeach;
	}

	/**
	 * Set column titles
	 *
	 * @param array $defaults defatul columns.
	 *
	 * @return array
	 */
	public function register_columns( $defaults )
	{
		$columns = [];
		foreach ( $defaults as $key => $value ) :
			$columns[ $key ] = $value;

			if ( 'title' === $key ) {
				$columns['helpful-pro']    = get_option( 'helpful_column_pro' ) ? get_option( 'helpful_column_pro' ) : _x( 'Pro', 'column name', 'helpful' );
				$columns['helpful-contra'] = get_option( 'helpful_column_contra' ) ? get_option( 'helpful_column_contra' ) : _x( 'Contra', 'column name', 'helpful' );
				$columns['helpful-feedback'] = get_option( 'helpful_column_feedback' ) ? get_option( 'helpful_column_feedback' ) : _x( 'Feedback', 'column name', 'helpful' );
			}
		endforeach;

		return $columns;
	}

	/**
	 * Columns callback
	 *
	 * @param string  $column_name column name.
	 * @param integer $post_id     post id.
	 *
	 * @return void
	 */
	public function populate_columns( $column_name, $post_id )
	{
		if ( 'helpful-pro' === $column_name ) {
			if ( get_option( 'helpful_percentages' ) ) {
				$percent = Helpful_Helper_Stats::getPro( $post_id, true );
				$pro     = Helpful_Helper_Stats::getPro( $post_id );
				update_post_meta( $post_id, 'helpful-pro', $pro );
				printf( '%d (%s%%)', (int) $pro, $percent );
			} else {
				$pro = Helpful_Helper_Stats::getPro( $post_id );
				$pro = intval( $pro );
				update_post_meta( $post_id, 'helpful-pro', $pro );
				echo $pro;
			}
		}

		if ( 'helpful-contra' === $column_name ) {
			if ( get_option( 'helpful_percentages' ) ) {
				$percent = Helpful_Helper_Stats::getContra( $post_id, true );
				$contra  = Helpful_Helper_Stats::getContra( $post_id );
				update_post_meta( $post_id, 'helpful-contra', $contra );
				printf( '%d (%s%%)', (int) $contra, $percent );
			} else {
				$contra = Helpful_Helper_Stats::getContra( $post_id );
				$contra = intval( $contra );
				update_post_meta( $post_id, 'helpful-contra', $contra );
				echo $contra;
			}
		}

		if ( 'helpful-feedback' === $column_name ) {
			$count = Helpful_Helper_Feedback::get_feedback_count( $post_id );
			$count = intval( $count );

			update_post_meta( $post_id, 'helpful-feedback-count', $count );

			if ( 0 < $count ) {
				$url = admin_url( 'admin.php?page=helpful_feedback&post_id=' . $post_id );
				printf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), intval( $count ) );
			} else {
				echo $count;
			}
		}
	}

	/**
	 * Set sortable columns
	 *
	 * @param array $columns columns.
	 * @return array
	 */
	public function register_sortable_columns( $columns )
	{
		$columns['helpful-pro']      = 'helpful-pro';
		$columns['helpful-contra']   = 'helpful-contra';
		$columns['helpful-feedback'] = 'helpful-feedback';

		return $columns;
	}

	/**
	 * Make values sortable in columns
	 *
	 * @param object $query current query.
	 * @return void
	 */
	public function sort_columns_query( $wp_query )
	{
		if ( ! is_admin() ) {
			return;
		}

		$orderby = $wp_query->get( 'orderby' );

		if ( 'helpful-pro' === $orderby ) {
			$meta_query = [
				'relation' => 'OR',
				[
					'key'     => 'helpful-pro',
					'compare' => 'NOT EXISTS',
					'type'    => 'NUMERIC',
				],
				[
					'key'  => 'helpful-pro',
					'type' => 'NUMERIC',
				],
			];

			$wp_query->set( 'meta_query', $meta_query );
			$wp_query->set( 'orderby', 'meta_value' );
		}

		if ( 'helpful-contra' === $orderby ) {
			$meta_query = [
				'relation' => 'OR',
				[
					'key'     => 'helpful-contra',
					'compare' => 'NOT EXISTS',
				],
				[
					'key' => 'helpful-contra',
				],
			];

			$wp_query->set( 'meta_query', $meta_query );
			$wp_query->set( 'orderby', 'meta_value' );
		}

		if ( 'helpful-feedback' === $orderby ) {
			$meta_query = [
				'relation' => 'OR',
				[
					'key'     => 'helpful-feedback-count',
					'compare' => 'NOT EXISTS',
					'type'    => 'NUMERIC',
				],
				[
					'key'  => 'helpful-feedback-count',
					'type' => 'NUMERIC',
				],
			];

			$wp_query->set( 'meta_query', $meta_query );
			$wp_query->set( 'orderby', 'meta_value' );
		}
	}
}
