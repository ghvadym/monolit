<?php
/**
 * Smart tag functionality.
 *
 * @since 1.0.0
 */
class WPForms_Smart_Tags {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'wpforms_process_smart_tags', array( $this, 'process' ), 10, 4 );
	}

	/**
	 * Approved smart tags.
	 *
	 * @since 1.0.0
	 *
	 * @param string $return Type of data to return.
	 *
	 * @return string|array
	 */
	public function get( $return = 'array' ) {

		$tags = array(
			'admin_email'               => esc_html__( 'Site Administrator Email', 'wpforms-lite' ),
			'entry_id'                  => esc_html__( 'Entry ID', 'wpforms-lite' ),
			'entry_date format="m/d/Y"' => esc_html__( 'Entry Date', 'wpforms-lite' ),
			'form_id'                   => esc_html__( 'Form ID', 'wpforms-lite' ),
			'form_name'                 => esc_html__( 'Form Name', 'wpforms-lite' ),
			'page_title'                => esc_html__( 'Embedded Post/Page Title', 'wpforms-lite' ),
			'page_url'                  => esc_html__( 'Embedded Post/Page URL', 'wpforms-lite' ),
			'page_id'                   => esc_html__( 'Embedded Post/Page ID', 'wpforms-lite' ),
			'date format="m/d/Y"'       => esc_html__( 'Date', 'wpforms-lite' ),
			'query_var key=""'          => esc_html__( 'Query String Variable', 'wpforms-lite' ),
			'user_ip'                   => esc_html__( 'User IP Address', 'wpforms-lite' ),
			'user_id'                   => esc_html__( 'User ID', 'wpforms-lite' ),
			'user_display'              => esc_html__( 'User Display Name', 'wpforms-lite' ),
			'user_full_name'            => esc_html__( 'User Full Name', 'wpforms-lite' ),
			'user_first_name'           => esc_html__( 'User First Name', 'wpforms-lite' ),
			'user_last_name'            => esc_html__( 'User Last Name', 'wpforms-lite' ),
			'user_email'                => esc_html__( 'User Email', 'wpforms-lite' ),
			'user_meta key=""'          => esc_html__( 'User Meta', 'wpforms-lite' ),
			'author_id'                 => esc_html__( 'Author ID', 'wpforms-lite' ),
			'author_display'            => esc_html__( 'Author Name', 'wpforms-lite' ),
			'author_email'              => esc_html__( 'Author Email', 'wpforms-lite' ),
			'url_referer'               => esc_html__( 'Referrer URL', 'wpforms-lite' ),
			'url_login'                 => esc_html__( 'Login URL', 'wpforms-lite' ),
			'url_logout'                => esc_html__( 'Logout URL', 'wpforms-lite' ),
			'url_register'              => esc_html__( 'Register URL', 'wpforms-lite' ),
			'url_lost_password'         => esc_html__( 'Lost Password URL', 'wpforms-lite' ),
		);

		$tags = apply_filters( 'wpforms_smart_tags', $tags );

		if ( 'list' === $return ) {

			// Return formatted list.
			$output = '<ul class="smart-tags-list">';
			foreach ( $tags as $key => $tag ) {
				$output .= '<li><a href="#" data-value="' . esc_attr( $key ) . '">' . esc_html( $tag ) . '</a></li>';
			}
			$output .= '</ul>';

			return $output;

		} else {

			// Return raw array.
			return $tags;
		}
	}

	/**
	 * Process and parse smart tags.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content      The string to preprocess.
	 * @param array $form_data     Form data and settings.
	 * @param string|array $fields Form fields.
	 * @param int|string $entry_id Entry ID.
	 *
	 * @return string
	 */
	public function process( $content, $form_data, $fields = '', $entry_id = '' ) {

		// Basic smart tags.
		preg_match_all( "/\{(.+?)\}/", $content, $tags );

		if ( ! empty( $tags[1] ) ) {

			foreach ( $tags[1] as $key => $tag ) {

				switch ( $tag ) {

					case 'admin_email':
						$content = $this->parse( '{' . $tag . '}', sanitize_email( get_option( 'admin_email' ) ), $content );
						break;

					case 'entry_id':
						$content = $this->parse( '{' . $tag . '}', absint( $entry_id ), $content );
						break;

					case 'form_id':
						$content = $this->parse( '{' . $tag . '}', absint( $form_data['id'] ), $content );
						break;

					case 'form_name':
						// The Form Pages addon rewrites the form_title setting for it's internal needs, so we want to first check if
						// we have a saved title for the form, and if so, we will use that for the form title smart tag.
						if ( isset( $form_data['settings']['form_name'] ) && ! empty( $form_data['settings']['form_name'] ) ) {
							$name = $form_data['settings']['form_name'];
						} elseif ( isset( $form_data['settings']['form_title'] ) && ! empty( $form_data['settings']['form_title'] ) ) {
							$name = $form_data['settings']['form_title'];
						} else {
							$name = '';
						}
						$content = $this->parse( '{' . $tag . '}', sanitize_text_field( $name ), $content );
						break;

					case 'page_title':
						$title   = get_the_ID() ? get_the_title( get_the_ID() ) : '';
						$content = $this->parse( '{' . $tag . '}', $title, $content );
						break;

					case 'page_url':
						global $wp;
						$url     = empty( $_POST['page_url'] ) ? home_url( add_query_arg( $_GET, $wp->request ) ) : esc_url_raw( wp_unslash( $_POST['page_url'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
						$content = $this->parse( '{' . $tag . '}', $url, $content );
						break;

					case 'page_id':
						$id      = get_the_ID() ? get_the_ID() : '';
						$content = $this->parse( '{' . $tag . '}', $id, $content );
						break;

					case 'user_ip':
						$content = $this->parse( '{' . $tag . '}', wpforms_get_ip(), $content );
						break;

					case 'user_id':
						$id      = is_user_logged_in() ? get_current_user_id() : '';
						$content = $this->parse( '{' . $tag . '}', $id, $content );
						break;

					case 'user_display':
						if ( is_user_logged_in() ) {
							$user = wp_get_current_user();
							$name = sanitize_text_field( $user->display_name );
						} else {
							$name = '';
						}
						$content = $this->parse( '{' . $tag . '}', $name, $content );
						break;

					case 'user_full_name':
						if ( is_user_logged_in() ) {
							$user = wp_get_current_user();
							$name = sanitize_text_field( $user->user_firstname . ' ' . $user->user_lastname );
						} else {
							$name = '';
						}
						$content = $this->parse( '{' . $tag . '}', $name, $content );
						break;

					case 'user_first_name':
						if ( is_user_logged_in() ) {
							$user = wp_get_current_user();
							$name = sanitize_text_field( $user->user_firstname );
						} else {
							$name = '';
						}
						$content = $this->parse( '{' . $tag . '}', $name, $content );
						break;

					case 'user_last_name':
						if ( is_user_logged_in() ) {
							$user = wp_get_current_user();
							$name = sanitize_text_field( $user->user_lastname );
						} else {
							$name = '';
						}
						$content = $this->parse( '{' . $tag . '}', $name, $content );
						break;

					case 'user_email':
						if ( is_user_logged_in() ) {
							$user  = wp_get_current_user();
							$email = sanitize_email( $user->user_email );
						} else {
							$email = '';
						}
						$content = $this->parse( '{' . $tag . '}', $email, $content );
						break;

					case 'author_id':
						$id = get_the_author_meta( 'ID' );
						if ( empty( $id ) && ! empty( $_POST['wpforms']['author'] ) ) {
							$id = get_the_author_meta( 'ID', absint( $_POST['wpforms']['author'] ) );
						}
						$id      = absint( $id );
						$content = $this->parse( '{' . $tag . '}', $id, $content );
						break;

					case 'author_display':
						$name = get_the_author();
						if ( empty( $name ) && ! empty( $_POST['wpforms']['author'] ) ) {
							$name = get_the_author_meta( 'display_name', absint( $_POST['wpforms']['author'] ) );
						}
						$name    = ! empty( $name ) ? sanitize_text_field( $name ) : '';
						$content = $this->parse( '{' . $tag . '}', $name, $content );
						break;

					case 'author_email':
						$email = get_the_author_meta( 'user_email' );
						if ( empty( $email ) && ! empty( $_POST['wpforms']['author'] ) ) {
							$email = get_the_author_meta( 'user_email', absint( $_POST['wpforms']['author'] ) );
						}
						$email   = sanitize_email( $email );
						$content = $this->parse( '{' . $tag . '}', $email, $content );
						break;

					case 'url_referer':
						$referer = ! empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
						$content = $this->parse( '{' . $tag . '}', sanitize_text_field( $referer ), $content );
						break;

					case 'url_login':
						$content = $this->parse( '{' . $tag . '}', wp_login_url(), $content );
						break;

					case 'url_logout':
						$content = $this->parse( '{' . $tag . '}', wp_logout_url(), $content );
						break;

					case 'url_register':
						$content = $this->parse( '{' . $tag . '}', wp_registration_url(), $content );
						break;

					case 'url_lost_password':
						$content = $this->parse( '{' . $tag . '}', wp_lostpassword_url(), $content );
						break;

					default:
						$content = apply_filters( 'wpforms_smart_tag_process', $content, $tag );
						break;
				}
			}
		}

		// Query string var smart tags.
		preg_match_all( "/\{query_var key=\"(.+?)\"\}/", $content, $query_vars );

		if ( ! empty( $query_vars[1] ) ) {

			foreach ( $query_vars[1] as $key => $query_var ) {
				$value   = ! empty( $_GET[ $query_var ] ) ? wp_unslash( sanitize_text_field( $_GET[ $query_var ] ) ) : ''; // phpcs:ignore
				$content = $this->parse( $query_vars[0][ $key ], $value, $content );
			}
		}

		// Entry date smart tags.
		preg_match_all( '/{entry_date format=\"(.+?)\"}/', $content, $dates );

		if ( ! empty( $dates[1] ) ) {

			$entry      = wpforms()->entry->get( $entry_id );
			$entry_date = $entry && property_exists( $entry, 'date' ) ? strtotime( $entry->date ) : 0;

			foreach ( $dates[1] as $key => $date ) {

				$value   = $entry_date ? date_i18n( $date, $entry_date + ( get_option( 'gmt_offset' ) * 3600 ) ) : '';
				$content = $this->parse( $dates[0][ $key ], $value, $content );
			}
		}

		// Date smart tags.
		preg_match_all( "/\{date format=\"(.+?)\"\}/", $content, $dates );

		if ( ! empty( $dates[1] ) ) {

			foreach ( $dates[1] as $key => $date ) {

				$value   = date( $date, time() + ( get_option( 'gmt_offset' ) * 3600 ) );
				$content = $this->parse( $dates[0][ $key ], $value, $content );
			}
		}

		// User meta smart tags.
		preg_match_all( "/\{user_meta key=\"(.+?)\"\}/", $content, $user_metas );

		if ( ! empty( $user_metas[1] ) ) {

			foreach ( $user_metas[1] as $key => $user_meta ) {

				$value   = is_user_logged_in() ? get_user_meta( get_current_user_id(), sanitize_text_field( $user_meta ), true )  : '';
				$content = $this->parse( $user_metas[0][ $key ], $value, $content );
			}
		}

		// Field smart tag to get data from 'value'.
		preg_match_all( "/\{field_id=\"(.+?)\"\}/", $content, $ids );

		// We can only process field smart tags if we have $fields.
		if ( ! empty( $ids[1] ) && ! empty( $fields ) ) {

			foreach ( $ids[1] as $key => $parts ) {
				$field_parts = explode( '|', $parts );
				$field_id    = $field_parts[0];
				$field_key   = ! empty( $field_parts[1] ) ? sanitize_key( $field_parts[1] ) : 'value';
				$value       = isset( $fields[ $field_id ][ $field_key ] ) ? wpforms_sanitize_textarea_field( $fields[ $field_id ][ $field_key ] ) : '';
				$value       = apply_filters( 'wpforms_field_smart_tag_value', $value );
				$content     = $this->parse( '{field_id="' . $parts . '"}', $value, $content );
			}
		}

		// Field smart tag to get data from 'value_raw'.
		preg_match_all( "/\{field_value_id=\"(.+?)\"\}/", $content, $value_ids );

		// We can only process field smart tags if we have $fields.
		if ( ! empty( $value_ids[1] ) && ! empty( $fields ) ) {

			foreach ( $value_ids[1] as $key => $field_id ) {

				if ( isset( $fields[ $field_id ]['value_raw'] ) && ! is_array( $fields[ $field_id ]['value_raw'] ) && (string) $fields[ $field_id ]['value_raw'] !== '' ) {
					$value = wpforms_sanitize_textarea_field( $fields[ $field_id ]['value_raw'] );
				} else {
					$value = isset( $fields[ $field_id ]['value'] ) ? wpforms_sanitize_textarea_field( $fields[ $field_id ]['value'] ) : '';
				}

				$content = $this->parse( '{field_value_id="' . $field_id . '"}', $value, $content );
			}
		}

		// Field smart tag to get HTML-postprocessed value (as seen in {all_fields}).
		preg_match_all( '/\{field_html_id="(.+?)"\}/', $content, $html_ids );

		// We can only process field smart tags if we have $fields.
		if ( ! empty( $html_ids[1] ) && ! empty( $fields ) ) {

			foreach ( $html_ids[1] as $key => $field_id ) {
				$value = '';
				if ( ! empty( $fields[ $field_id ] ) ) {
					$value = ! isset( $fields[ $field_id ]['value'] ) || (string) $fields[ $field_id ]['value'] === '' ? '<em>' . esc_html__( '(empty)', 'wpforms-lite' ) . '</em>' : wpforms_sanitize_textarea_field( $fields[ $field_id ]['value'] );
					$value = apply_filters(
						'wpforms_html_field_value',
						$value,
						$fields[ $field_id ],
						$form_data,
						'smart-tag'
					);
				}

				$content = $this->parse( '{field_html_id="' . $field_id . '"}', $value, $content );
			}
		}

		return $content;
	}

	/**
	 * Replace a found smart tag with the final value.
	 *
	 * @since 1.5.9
	 *
	 * @param string $tag     The tag.
	 * @param string $value   The value.
	 * @param string $content Content.
	 *
	 * @return string
	 */
	public function parse( $tag, $value, $content ) {

		return str_replace( $tag, strip_shortcodes( $value ), $content );
	}
}
