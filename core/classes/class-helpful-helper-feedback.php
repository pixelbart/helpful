<?php
/**
 * Helper for receiving stored feedback, feedback informations and
 * user avatars.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpful_Helper_Feedback
{
	/**
	 * Get feedback data by post object.
	 *
	 * @param object $entry post object.
	 *
	 * @return string json
	 */
	public static function getFeedback( $entry )
	{
		$post = get_post( $entry->post_id );
		$time = strtotime( $entry->time );

		$feedback            = [];
		$feedback['id']      = $entry->id;
		$feedback['name']    = __( 'Anonymous', 'helpful' );
		$feedback['message'] = nl2br( $entry->message );
		$feedback['pro']     = $entry->pro;
		$feedback['contra']  = $entry->contra;
		$feedback['post']    = $post;
		$feedback['time']    = sprintf(
			/* translators: %s = time difference */
			__( 'Submitted %s ago', 'helpful' ),
			human_time_diff( $time, current_time( 'timestamp' ) )
		);

		if ( $entry->fields ) {
			$fields = [];
			$items  = maybe_unserialize( $entry->fields );
			if ( is_array( $items ) ) {
				foreach ( $items as $label => $value ) {
					$feedback['fields'][ $label ] = $value;
				}
			}
		}

		$feedback['avatar'] = self::getAvatar();

		if ( isset( $feedback['fields']['email'] ) && '' !== $feedback['fields']['email'] ) {
			$feedback['avatar'] = self::getAvatar( $feedback['fields']['email'] );
		}

		if ( isset( $feedback['fields']['name'] ) && '' !== $feedback['fields']['name'] ) {
			$feedback['name'] = $feedback['fields']['name'];
		}

		$feedback = apply_filters( 'helpful_admin_feedback_item', $feedback, $entry );

		return json_decode( wp_json_encode( $feedback ) );
	}

	/**
	 * Get avatar or default helpful avatar by email.
	 *
	 * @param string  $email user email.
	 * @param integer $size  image size.
	 *
	 * @return string
	 */
	public static function getAvatar( $email = null, $size = 55 )
	{
		$default = plugins_url( 'core/assets/images/avatar.jpg', HELPFUL_FILE );

		if ( get_option( 'helpful_feedback_gravatar' ) ) {
			if ( ! is_null( $email ) ) {
				return get_avatar( $email, $size, $default );
			}
		}

		$html = '<img src="%1$s" height="%2$s" width="%2$s" alt="no avatar">';
		$html = apply_filters( 'helpful_feedback_noavatar', $html );

		return sprintf( $html, $default, $size );
	}

	/**
	 * Get feedback items.
	 *
	 * @global $wpdb
	 *
	 * @param integer $limit posts per page.
	 *
	 * @return object
	 */
	public static function getFeedbackItems( $limit = null )
	{
		if ( is_null( $limit ) ) {
			$limit = absint( get_option( 'helpful_widget_amount' ) );
		}

		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful_feedback';

		$query   = "SELECT * FROM $helpful ORDER BY time DESC LIMIT %d";
		$query   = $wpdb->prepare( $query, $limit );
		$results = $wpdb->get_results( $query );

		if ( $results ) {
			return $results;
		}

		return false;
	}

	/**
	 * Insert feedback into database.
	 *
	 * @global $wpdb
	 *
	 * @return integer
	 */
	public static function insertFeedback()
	{
		global $wpdb;

		$fields  = [];
		$pro     = 0;
		$contra  = 0;
		$message = null;

		if ( ! isset( $_REQUEST['post_id'] ) ) {
			$message = 'Helpful Notice: Feedback was not saved because the post id is empty in %s on line %d.';
			helpful_error_log( sprintf( $message, __FILE__, __LINE__ ) );
			return null;
		}

		$post_id = absint( sanitize_text_field( wp_unslash( $_REQUEST['post_id'] ) ) );

		if ( ! isset( $_REQUEST['message'] ) ) {
			$message = 'Helpful Notice: Feedback was not saved because the message is empty in %s on line %d.';
			helpful_error_log( sprintf( $message, __FILE__, __LINE__ ) );
			return null;
		}

		$message = trim( $_REQUEST['message'] );

		if ( '' === $message ) {
			$message = 'Helpful Notice: Feedback was not saved because the message is empty in %s on line %d.';
			helpful_error_log( sprintf( $message, __FILE__, __LINE__ ) );
			return null;
		}

		if ( helpful_backlist_check( $_REQUEST['message'] ) ) {
			$message = 'Helpful Notice: Feedback was not saved because the message contains blacklisted words in %s on line %d.';
			helpful_error_log( sprintf( $message, __FILE__, __LINE__ ) );
			return null;
		}

		if ( isset( $_REQUEST['fields'] ) ) {
			foreach ( $_REQUEST['fields'] as $key => $value ) {
				$fields[ $key ] = sanitize_text_field( $value );
			}

			$fields = apply_filters( 'helpful_feedback_submit_fields', $fields );
		}

		if ( is_user_logged_in() ) {
			$user   = wp_get_current_user();
			$fields = [];

			$fields['name']  = $user->display_name;
			$fields['email'] = $user->user_email;

			$fields = apply_filters( 'helpful_feedback_submit_fields', $fields );
		}

		if ( isset( $_REQUEST['message'] ) ) {
			$message = sanitize_textarea_field( wp_strip_all_tags( wp_unslash( $_REQUEST['message'] ) ) );
			$message = stripslashes( $message );
			$message = apply_filters( 'helpful_feedback_submit_message', $message );
		}

		if ( isset( $_REQUEST['type'] ) ) {
			$type = sanitize_text_field( wp_unslash( $_REQUEST['type'] ) );

			if ( 'pro' === $type ) {
				$pro = 1;
			} elseif ( 'contra' === $type ) {
				$contra = 1;
			}
		}

		$data = [
			'time'    => current_time( 'mysql' ),
			'user'    => esc_attr( $_REQUEST['user_id'] ),
			'pro'     => $pro,
			'contra'  => $contra,
			'post_id' => $post_id,
			'message' => $message,
			'fields'  => maybe_serialize( $fields ),
		];

		/* send email */
		self::send_email( $data );

		$table_name = $wpdb->prefix . 'helpful_feedback';
		$wpdb->insert( $table_name, $data );
		return $wpdb->insert_id;
	}

	/**
	 * Send feedback email.
	 *
	 * @param array $feedback feedback data.
	 *
	 * @return void
	 */
	public static function send_email( $feedback )
	{
		if ( ! get_option( 'helpful_feedback_email' ) ) {
			return;
		}

		$post = get_post( $feedback['post_id'] );

		if ( ! $post ) {
			return;
		}

		/* email headers */
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		/* email subject */
		$subject = get_option( 'helpful_feedback_subject' );

		/* unserialize feedback fields */
		$feedback['fields'] = maybe_unserialize( $feedback['fields'] );

		$type = esc_html__( 'positive', 'helpful' );
		if ( 1 === $feedback['contra'] ) { 
			$type = esc_html__( 'negative', 'helpful' );
		}

		/* body tags */
		$tags = [
			'{type}'       => $type,
			'{name}'       => $feedback['fields']['name'],
			'{email}'      => $feedback['fields']['email'],
			'{message}'    => $feedback['message'],
			'{post_url}'   => get_permalink( $post ),
			'{post_title}' => $post->post_title,
			'{blog_name}'  => get_bloginfo( 'name' ),
			'{blog_url}'   => site_url(),
		];

		$tags = apply_filters( 'helpful_feedback_email_tags', $tags );
		$body = get_option( 'helpful_feedback_email_content' );
		$body = str_replace( array_keys( $tags ), array_values( $tags ), $body );

		/* receivers by post meta */
		$post_receivers = [];

		if ( get_post_meta( $post->ID, 'helpful_feedback_receivers', true ) ) {
			$post_receivers = get_post_meta( $post->ID, 'helpful_feedback_receivers', true );
			$post_receivers = helpful_trim_all( $post_receivers );
			$post_receivers = explode( ',', $post_receivers );
		}

		/* receivers by helpful options */
		$helpful_receivers = [];

		if ( get_option( 'helpful_feedback_receivers' ) ) {
			$helpful_receivers = get_option( 'helpful_feedback_receivers' );
			$helpful_receivers = helpful_trim_all( $helpful_receivers );
			$helpful_receivers = explode( ',', $helpful_receivers );
		}

		$receivers = array_merge( $helpful_receivers, $post_receivers );
		$receivers = array_unique( $receivers );

		/* receivers array is empty */
		if ( empty( $receivers ) ) {
			return;
		}

		/* filters */
		$receivers = apply_filters( 'helpful_feedback_email_receivers', $receivers );
		$subject   = apply_filters( 'helpful_feedback_email_subject', $subject );
		$body      = apply_filters( 'helpful_feedback_email_body', $body );
		$headers   = apply_filters( 'helpful_feedback_email_headers', $headers );

		$response = wp_mail( $receivers, $subject, $body, $headers );

		if ( false === $response ) {
			$message = 'Helpful Warning: Email could not be sent in %s on line %d.';
			helpful_error_log( sprintf( $message, __FILE__, __LINE__ ) );
		}
	}
}
