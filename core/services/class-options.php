<?php
/**
 * A service for setting, deleting and editing options.
 * Allows saving many options, within one WordPress option.
 *
 * @package Helpful
 * @subpackage Core\Services
 * @version 4.5.8
 * @since 4.4.47
 */

namespace Helpful\Core\Services;

use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ...
 */
class Options {
	/**
	 * Array of options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor
	 */
	public function __construct() {
		$options       = maybe_unserialize( get_option( 'helpful_options', array() ) );
		$this->options = ( is_array( $options ) ) ? $options : array();
	}

	/**
	 * Resave the options.
	 */
	public function renew_options() {
		update_option( 'helpful_options', $this->options );
	}

	/**
	 * Update a single option.
	 *
	 * @param string $name option name.
	 * @param mixed  $value option value.
	 */
	public function update_option( $name, $value ) {
		if ( ! is_string( $name ) ) {
			return;
		}

		$this->options[ $name ] = $value;
		$this->renew_options();
	}

	/**
	 * Delete a single option.
	 *
	 * @param string $name option name.
	 */
	public function delete_option( $name ) {
		if ( ! is_string( $name ) ) {
			return;
		}

		if ( isset( $this->options[ $name ] ) ) {
			unset( $this->options[ $name ] );
		}

		/* delete old option, if exists */
		delete_option( $name );

		$this->renew_options();
	}

	/**
	 * Get a single option, by respecting the security.
	 *
	 * @param string $name option name.
	 * @param mixed  $default default state/value.
	 * @param string $security security type.
	 *
	 * @return mixed
	 */
	public function get_option( $name, $default = false, $security = 'blank' ) {
		if ( ! is_string( $name ) ) {
			return $default;
		}

		$option = null;

		if ( get_option( $name ) ) {
			$option = get_option( $name );
		}

		$option = apply_filters( 'helpful/get_option/' . $name, $option );

		if ( isset( $this->options[ $name ] ) ) {
			$option = $this->options[ $name ];
		}

		if ( apply_filters( 'helpful/get_option/handle_security', true ) ) {
			$option = $this->handle_security( $option, $security );
		}

		return ( $option ) ? $option : $default;
	}

	/**
	 * Get all options.
	 *
	 * @return array
	 */
	public function get_options() {
		return apply_filters( 'helpful/get_options', $this->options );
	}

	/**
	 * Handle security based on its value.
	 *
	 * @param mixed  $value value that is checked.
	 * @param string $security type of the value, for the security check.
	 *
	 * @return mixed
	 */
	private function handle_security( $value, $security ) {
		if ( 'blank' === $security ) {
			return $value;
		}

		if ( is_array( $value ) && ! empty( $value ) ) {
			$result = array();

			foreach ( $value as $key => $data ) {
				$result[ $key ] = $this->handle_security( $data, $security );
			}

			return $result;
		}

		switch ( $security ) {
			case 'bool':
				$value = boolval( $value );
				break;
			case 'esc_html':
				$value = esc_html( $value );
				break;
			case 'kses':
				$value = $this->sanitize_input( $value );
				break;
			case 'kses_deep':
				$value = $this->sanitize_input_without_tags( $value );
				break;
			case 'intval':
				$value = intval( $value );
				break;
			case 'floatval':
				$value = floatval( $value );
				break;
			case 'on_off':
				$value = ( 'on' === $value ) ? 'on' : 'off';
				break;
			default:
				$value = esc_attr( $value );
		}

		return $value;
	}

	/**
	 * Filters the values of an option before saving them. Thus does not allow every
	 * HTML element and makes Helpful a bit more secure.
	 *
	 * @param mixed $value Input value.
	 *
	 * @return mixed
	 */
	public function sanitize_input( $value ) {
		return wp_kses( $value, Helper::kses_allowed_tags() );
	}

	/**
	 * Filters the values of an option before saving them. Thus does not allow
	 * HTML element and makes Helpful a bit more secure.
	 *
	 * @param mixed $value Input value.
	 *
	 * @return mixed
	 */
	public function sanitize_input_without_tags( $value ) {
		return wp_kses( $value, array() );
	}

	/**
	 * Returns the array for the wpml solution.
	 *
	 * @return array
	 */
	public function get_i18n_array() {
		return array(
			'helpful_heading',
			'helpful_content',
			'helpful_exists',
			'helpful_after_pro',
			'helpful_after_contra',
			'helpful_after_fallback',
			'helpful_pro',
			'helpful_contra',
			'helpful_column_pro',
			'helpful_column_contra',
			'helpful_column_feedback',
			'helpful_feedback_message_pro',
			'helpful_feedback_message_contra',
			'helpful_feedback_message_spam',
			'helpful_feedback_label_message',
			'helpful_feedback_label_name',
			'helpful_feedback_label_email',
			'helpful_feedback_label_submit',
			'helpful_feedback_label_cancel',
			'helpful_feedback_message_voted',
			'helpful_feedback_subject',
			'helpful_feedback_email_content',
			'helpful_feedback_subject_voter',
			'helpful_feedback_email_content_voter',
		);
	}

	/**
	 * Returns the default options, or the keys for other functions.
	 *
	 * @param string $group Settingsgroup.
	 * @param bool   $keys_only Return only array keys (options names).
	 *
	 * @return array
	 */
	public function get_defaults_array( string $group = '', bool $keys_only = false ) {
		$feedback_email_content       = Helpers\Feedback::get_email_content();
		$feedback_email_content_voter = Helpers\Feedback::get_email_content_voter();

		/* old options? idk */
		$options = array(
			'helpful_success' => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
			'helpful_error'   => _x( 'Sorry, an error has occurred.', 'error after voting', 'helpful' ),
		);

		$groups = array(
			'helpful-details-settings-group' => array(
				'helpful_post_types'              => array( 'post' ),
				'helpful_exists_hide'             => 'off',
				'helpful_count_hide'              => 'off',
				'helpful_credits'                 => 'off',
				'helpful_hide_in_content'         => 'off',
				'helpful_only_once'               => 'off',
				'helpful_percentages'             => 'off',
				'helpful_hide_admin_columns'      => 'off',
				'helpful_shrink_admin_columns'    => 'off',
				'helpful_feedback_disabled'       => 'off',
				'helpful_ip_user'                 => 'off',
				'helpful_wordpress_user'          => 'off',
				'helpful_metabox'                 => 'off',
				'helpful_widget'                  => 'off',
				'helpful_widget_pro'              => 'off',
				'helpful_widget_contra'           => 'off',
				'helpful_widget_pro_recent'       => 'off',
				'helpful_widget_contra_recent'    => 'off',
				'helpful_feedback_widget'         => 'off',
				'helpful_widget_hide_publication' => 'off',
				'helpful_widget_amount'           => 3,
				'helpful_shortcode_post_types'    => 'off',
			),
			'helpful-texts-settings-group' => array(
				'helpful_heading'         => _x( 'Was this post helpful?', 'default headline', 'helpful' ),
				'helpful_content'         => _x( 'Let us know if you liked the post. That’s the only way we can improve.', 'default description', 'helpful'),
				'helpful_exists'          => _x( 'You have already voted for this post.', 'already voted', 'helpful' ),
				'helpful_after_pro'       => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
				'helpful_after_contra'    => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
				'helpful_after_fallback'  => _x( 'Thank you for voting.', 'text after voting', 'helpful' ),
				'helpful_pro'             => _x( 'Yes', 'text pro button', 'helpful' ),
				'helpful_contra'          => _x( 'No', 'text contra button', 'helpful' ),
				'helpful_pro_disabled'    => 'off',
				'helpful_contra_disabled' => 'off',
				'helpful_column_pro'      => _x( 'Pro', 'column name', 'helpful' ),
				'helpful_column_contra'   => _x( 'Contra', 'column name', 'helpful' ),
				'helpful_column_feedback' => _x( 'Feedback', 'column name', 'helpful' ),
			),
			'helpful-feedback-settings-group' => array(
				'helpful_feedback_after_pro'           => 'off',
				'helpful_feedback_after_contra'        => 'off',
				'helpful_feedback_name'                => 'off',
				'helpful_feedback_email'               => 'off',
				'helpful_feedback_cancel'              => 'off',
				'helpful_feedback_message_pro'         => _x( 'Thank you for voting. You can now write me a few words, so I know what you particularly liked.', 'text after feedback pro', 'helpful' ),
				'helpful_feedback_message_contra'      => _x( 'Thank you for voting. You can now write me a few words so I know what you didn\'t like so much.', 'text after feedback contra', 'helpful' ),
				'helpful_feedback_message_spam'        => _x( 'Thank you for voting.', 'text after feedback spam', 'helpful' ),
				'helpful_feedback_label_message'       => _x( 'Message', 'label for feedback form field', 'helpful' ),
				'helpful_feedback_label_name'          => _x( 'Name', 'label for feedback form field', 'helpful' ),
				'helpful_feedback_label_email'         => _x( 'Email', 'label for feedback form field', 'helpful' ),
				'helpful_feedback_label_submit'        => _x( 'Send Feedback', 'label for feedback form field', 'helpful' ),
				'helpful_feedback_label_cancel'        => _x( 'Cancel', 'label for feedback form field', 'helpful' ),
				'helpful_feedback_after_vote'          => 'off',
				'helpful_feedback_message_voted'       => _x( 'You have already voted. Do you still want to leave me a message?', 'text already feedback', 'helpful' ),
				'helpful_feedback_gravatar'            => 'off',
				'helpful_feedback_widget'              => 'off',
				'helpful_feedback_amount'              => 10,
				'helpful_feedback_send_email'          => 'off',
				'helpful_feedback_receivers'           => get_option( 'admin_email' ),
				'helpful_feedback_subject'             => _x( 'There\'s new feedback for you.', 'feedback email subject', 'helpful' ),
				'helpful_feedback_email_content'       => $feedback_email_content,
				'helpful_feedback_send_email_voter'    => 'off',
				'helpful_feedback_subject_voter'       => _x( 'Thanks for your feedback!', 'feedback email voter subject', 'helpful' ),
				'helpful_feedback_email_content_voter' => $feedback_email_content_voter,
			),
			'helpful-system-settings-group' => array(
				'helpful_caching'                => 'off',
				'helpful_caching_time'           => '',
				'helpful_timezone'               => date_default_timezone_get(),
				'helpful_multiple'               => 'off',
				'helpful_notes'                  => 'off',
				'helpful_plugin_first'           => 'off',
				'helpful_classic_editor'         => 'off',
				'helpful_classic_widgets'        => 'off',
				'helpful_disable_frontend_nonce' => 'off',
				'helpful_disable_feedback_nonce' => 'off',
				'helpful_user_random'            => 'off',
				'helpful_sessions_false'         => 'off',
				'helpful_cookies_samesite'       => 'Strict',
				'helpful_export_separator'       => ';',
				'helpful_uninstall'              => 'off',
				'helpful_uninstall_feedback'     => 'off',
			),
			'helpful-design-settings-group' => array(
				'helpful_customizer' => array(),
			),
		);

		if ( '' !== trim( $group ) && array_key_exists( $group, $groups ) ) {
			return $groups[ $group ];
		}

		$options = array();

		foreach ( $groups as $group => $_options ) {
			$options = array_merge( $options, $_options );
		}

		if ( true === $keys_only ) {
			return array_keys( $options );
		}

		return apply_filters( 'helpful/options/defaults', $options, array_keys( $groups ) );
	}

	/**
	 * Sync all old options formats with new options.
	 *
	 * @version 4.5.0
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function sync_options() {
		$keys = $this->get_defaults_array( '', true );

		if ( empty( $keys ) ) {
			return;
		}

		delete_option( 'helpful_options' );

		foreach ( $keys as $key ) {
			$this->update_option( $key, $this->get_option( $key ) );
			delete_option( $key );
		}
	}
}
