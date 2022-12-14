<?php

namespace WPForms\Integrations\Gutenberg;

use WPForms\Integrations\IntegrationInterface;

/**
 * Form Selector Gutenberg block with live preview.
 *
 * @since 1.4.8
 */
class FormSelector implements IntegrationInterface {

	/**
	 * Indicate if current integration is allowed to load.
	 *
	 * @since 1.4.8
	 *
	 * @return bool
	 */
	public function allow_load() {
		return \function_exists( 'register_block_type' );
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.4.8
	 */
	public function load() {
		$this->hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 1.4.8
	 */
	protected function hooks() {

		\add_action( 'init', array( $this, 'register_block' ) );
		\add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register WPForms Gutenberg block on the backend.
	 *
	 * @since 1.4.8
	 */
	public function register_block() {

		$css_file            = false;
		$disable_css_setting = wpforms_setting( 'disable-css', '1' );
		$min                 = wpforms_get_min_suffix();

		if ( is_admin() ) {
			\wp_enqueue_style(
				'wpforms-integrations',
				WPFORMS_PLUGIN_URL . "assets/css/admin-integrations{$min}.css",
				[],
				WPFORMS_VERSION
			);
		}

		if ( '1' === $disable_css_setting ) {
			$css_file = 'full';
		} elseif ( '2' === $disable_css_setting ) {
			$css_file = 'base';
		}

		if ( $css_file ) {
			\wp_register_style(
				'wpforms-gutenberg-form-selector',
				WPFORMS_PLUGIN_URL . "assets/css/wpforms-{$css_file}{$min}.css",
				[ 'wp-edit-blocks' ],
				WPFORMS_VERSION
			);
		}

		$attributes = [
			'formId'       => [
				'type' => 'string',
			],
			'displayTitle' => [
				'type' => 'boolean',
			],
			'displayDesc'  => [
				'type' => 'boolean',
			],
			'className'    => [
				'type' => 'string',
			],
		];

		\register_block_type(
			'wpforms/form-selector',
			[
				'attributes'      => \apply_filters( 'wpforms_gutenberg_form_selector_attributes', $attributes ),
				'editor_style'    => $css_file ? 'wpforms-gutenberg-form-selector' : '',
				'render_callback' => [ $this, 'get_form_html' ],
			]
		);
	}

	/**
	 * Load WPForms Gutenberg block scripts.
	 *
	 * @since 1.4.8
	 */
	public function enqueue_block_editor_assets() {

		$i18n = array(
			'title'             => \esc_html__( 'WPForms', 'wpforms-lite' ),
			'description'       => \esc_html__( 'Select and display one of your forms.', 'wpforms-lite' ),
			'form_keywords'     => array(
				\esc_html__( 'form', 'wpforms-lite' ),
				\esc_html__( 'contact', 'wpforms-lite' ),
				\esc_html__( 'survey', 'wpforms-lite' ),
				'the dude',
			),
			'form_select'       => \esc_html__( 'Select a Form', 'wpforms-lite' ),
			'form_settings'     => \esc_html__( 'Form Settings', 'wpforms-lite' ),
			'form_selected'     => \esc_html__( 'Form', 'wpforms-lite' ),
			'show_title'        => \esc_html__( 'Show Title', 'wpforms-lite' ),
			'show_description'  => \esc_html__( 'Show Description', 'wpforms-lite' ),
			'panel_notice_head' => \esc_html__( 'Heads up!', 'wpforms-lite' ),
			'panel_notice_text' => \esc_html__( 'Do not forget to test your form.', 'wpforms-lite' ),
			'panel_notice_link' => \esc_html__( 'Check out our complete guide!', 'wpforms-lite' ),
		);

		\wp_enqueue_script(
			'wpforms-gutenberg-form-selector',
			// The unminified version is not supported by the browser.
			WPFORMS_PLUGIN_URL . 'assets/js/components/admin/gutenberg/formselector.min.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
			WPFORMS_VERSION,
			true
		);

		$forms = \wpforms()->form->get( '', array( 'order' => 'DESC' ) );
		$forms = ! empty( $forms ) ? $forms : array();
		$forms = array_map(
			function( $form ) {
				$form->post_title = htmlspecialchars_decode( $form->post_title, ENT_QUOTES );
				return $form;
			},
			$forms
		);

		\wp_localize_script(
			'wpforms-gutenberg-form-selector',
			'wpforms_gutenberg_form_selector',
			array(
				'logo_url'          => WPFORMS_PLUGIN_URL . 'assets/images/sullie-alt.png',
				'block_preview_url' => WPFORMS_PLUGIN_URL . 'assets/images/integrations/gutenberg/block-preview.png',
				'wpnonce'           => \wp_create_nonce( 'wpforms-gutenberg-form-selector' ),
				'forms'             => $forms,
				'i18n'              => $i18n,
			)
		);
	}

	/**
	 * Get form HTML to display in a WPForms Gutenberg block.
	 *
	 * @param array $attr Attributes passed by WPForms Gutenberg block.
	 *
	 * @since 1.4.8
	 *
	 * @return string
	 */
	public function get_form_html( $attr ) {

		$id = ! empty( $attr['formId'] ) ? \absint( $attr['formId'] ) : 0;

		if ( empty( $id ) ) {
			return '';
		}

		$title = ! empty( $attr['displayTitle'] ) ? true : false;
		$desc  = ! empty( $attr['displayDesc'] ) ? true : false;

		// Disable form fields if called from the Gutenberg editor.
		if ( $this->is_gb_editor() ) {

			\add_filter(
				'wpforms_frontend_container_class',
				function ( $classes ) {
					$classes[] = 'wpforms-gutenberg-form-selector';
					$classes[] = 'wpforms-container-full';
					return $classes;
				}
			);
			\add_action(
				'wpforms_frontend_output',
				function () {
					echo '<fieldset disabled>';
				},
				3
			);
			\add_action(
				'wpforms_frontend_output',
				function () {
					echo '</fieldset>';
				},
				30
			);
		}

		if ( ! empty( $attr['className'] ) ) {
			\add_filter(
				'wpforms_frontend_container_class',
				function ( $classes ) use ( $attr ) {
					$cls = array_map( 'esc_attr', explode( ' ', $attr['className'] ) );
					return array_unique( array_merge( $classes, $cls ) );
				}
			);
		}

		\ob_start();

		\do_action( 'wpforms_gutenberg_block_before' );

		if ( $this->is_gb_editor() ) {
			wpforms_display(
				$id,
				apply_filters( 'wpforms_gutenberg_block_form_title', $title, $id ),
				apply_filters( 'wpforms_gutenberg_block_form_desc', $desc, $id )
			);
		} else {
			printf(
				'[wpforms id="%s" title="%d" description="%d"]',
				absint( $id ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				apply_filters( 'wpforms_gutenberg_block_form_title', $title, $id ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				apply_filters( 'wpforms_gutenberg_block_form_desc', $desc, $id ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}

		\do_action( 'wpforms_gutenberg_block_after' );

		$content = \ob_get_clean();

		if ( empty( $content ) ) {
			$content = '<div class="components-placeholder"><div class="components-placeholder__label"></div><div class="components-placeholder__fieldset">' . \esc_html__( 'The form cannot be displayed.', 'wpforms-lite' ) . '</div></div>';
		}

		return \apply_filters( 'wpforms_gutenberg_block_form_content', $content, $id );
	}

	/**
	 * Checking if is Gutenberg REST API call.
	 *
	 * @since 1.5.7
	 *
	 * @return bool True if is Gutenberg REST API call.
	 */
	public function is_gb_editor() {

		// TODO: Find a better way to check if is GB editor API call.
		return \defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context']; // phpcs:ignore
	}
}
