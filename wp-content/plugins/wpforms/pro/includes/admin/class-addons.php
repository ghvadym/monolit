<?php

/**
 * Addons class.
 *
 * @since 1.0.0
 */
class WPForms_Addons {

	const SLUG = 'wpforms-addons';

	/**
	 * WPForms addons
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $addons;

	/**
	 * Determine if the plugin/addon installations are allowed.
	 *
	 * @since 1.6.2.3
	 *
	 * @var bool
	 */
	private $can_install;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Maybe load addons page.
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Determine if the user is viewing the settings page, if so, party on.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

		// Only load if we are actually on the settings page.
		if ( self::SLUG === $page ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
			add_action( 'wpforms_admin_page', array( $this, 'output' ) );

			$this->can_install = wpforms_can_install( 'addon' );
		}
	}

	/**
	 * Enqueue assets for the addons page.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {

		// JavaScript.
		wp_enqueue_script(
			'jquery-matchheight',
			WPFORMS_PLUGIN_URL . 'assets/js/jquery.matchHeight-min.js',
			array( 'jquery' ),
			'0.7.0',
			false
		);

		wp_enqueue_script(
			'listjs',
			WPFORMS_PLUGIN_URL . 'assets/js/list.min.js',
			array( 'jquery' ),
			'1.5.0'
		);
	}

	/**
	 * Build the output for the plugin addons page.
	 *
	 * @since 1.0.0
	 */
	public function output() {

		$refresh      = isset( $_GET['wpforms_refresh_addons'] );
		$errors       = wpforms()->license->get_errors();
		$type         = wpforms()->license->type();
		$this->addons = wpforms()->license->addons( $refresh );
		?>

		<div id="wpforms-admin-addons" class="wrap wpforms-admin-wrap">

			<h1 class="page-title">
				<?php esc_html_e( 'WPForms Addons', 'wpforms' ); ?>
				<a href="<?php echo esc_url_raw( add_query_arg( array( 'wpforms_refresh_addons' => '1' ) ) ); ?>" class="add-new-h2 wpforms-btn-orange">
					<?php esc_html_e( 'Refresh Addons', 'wpforms' ); ?>
				</a>
				<input type="search" placeholder="<?php esc_attr_e( 'Search Addons', 'wpforms' ); ?>" id="wpforms-admin-addons-search">
			</h1>

			<?php if ( empty( $this->addons ) ) : ?>

				<div class="error notice">
					<p><?php esc_html_e( 'There was an issue retrieving Addons for this site. Please click on the button above to refresh.', 'wpforms' ); ?></p>
				</div>

			<?php elseif ( ! empty( $errors ) ) : ?>

				<div class="error notice">
					<p><?php esc_html_e( 'In order to get access to Addons, you need to resolve your license key errors.', 'wpforms' ); ?></p>
				</div>

			<?php elseif ( empty( $type ) ) : ?>

				<div class="error notice">
					<p><?php esc_html_e( 'In order to get access to Addons, you need to verify your license key for WPForms.', 'wpforms' ); ?></p>
				</div>

			<?php else : ?>

				<?php if ( $refresh ) : ?>

					<div class="updated notice">
						<p><?php esc_html_e( 'Addons have successfully been refreshed.', 'wpforms' ); ?></p>
					</div>

				<?php
				endif;

				echo '<div class="wpforms-admin-content">';

					if ( ! $refresh ) {
						echo '<p class="intro">' .
							sprintf(
								wp_kses(
									/* translators: %s - refresh addons page URL. */
									__( 'Improve your forms with our premium addons. Missing an addon that you think you should be able to see? Click the <a href="%s">Refresh Addons</a> button above.', 'wpforms' ),
									array(
										'a' => array(
											'href' => array(),
										),
									)
								),
								esc_url_raw( add_query_arg( array( 'wpforms_refresh_addons' => '1' ) ) )
							) .
							'</p>';
					}

					echo '<h4 id="addons-heading" data-text="' . esc_attr__( 'Available Addons', 'wpforms' ) . '">' . esc_html__( 'Available Addons', 'wpforms' ) . '</h4>';

					echo '<div id="wpforms-admin-addons-list">';

						if ( 'basic' === $type ) :

							$this->addon_grid( $this->addons, $type, array( 'basic' ) );
							$this->print_unlock_features();
							$this->addon_grid( $this->addons, $type, array( 'plus', 'pro', 'agency' ) );

						elseif ( 'plus' === $type ) :

							$this->addon_grid( $this->addons, $type, array( 'basic', 'plus' ) );
							$this->print_unlock_features();
							$this->addon_grid( $this->addons, $type, array( 'pro', 'agency' ) );

						elseif ( 'pro' === $type ) :

							$this->addon_grid( $this->addons, $type, array( 'basic', 'plus', 'pro' ) );
							$this->print_unlock_features();
							$this->addon_grid( $this->addons, $type, array( 'agency' ) );

						elseif ( in_array( $type, array( 'elite', 'agency', 'ultimate' ), true ) ) :

							$this->addon_grid( $this->addons, $type, array( 'basic', 'plus', 'pro', 'agency' ) );

						endif;

					echo '</div>';

				echo '</div>';

			endif;

		echo '</div>';
	}

	/**
	 * Print unlock features message.
     *
     * @since 1.6.3
     */
	private function print_unlock_features() {

		echo '<div class="unlock-msg">';
			echo '<h4>' . esc_html__( 'Unlock More Features...', 'wpforms' ) . '</h4>';
			echo '<p>' .
				sprintf(
					wp_kses(
						/* translators: %s - WPForms.com Account page URL. */
						__( 'Want to get even more features? <a href="%s" target="_blank" rel="noopener noreferrer">Upgrade your WPForms account</a> and unlock the following extensions.', 'wpforms' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
						)
					),
					'https://wpforms.com/account/'
				) .
			'</p>';
		echo '</div>';
	}

	/**
	 * Render grid of addons.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $addons       List of addons.
	 * @param string $type_current License type user currently have.
	 * @param array  $type_show    License type to show.
	 */
	public function addon_grid( $addons, $type_current, $type_show ) {

		if ( in_array( $type_current, array( 'ultimate', 'elite' ), true ) ) {
			$type_current = 'agency';
		}

		echo '<div class="list">';

		foreach ( $addons as $id => $addon ) {
			$addon = (array) $addon;
			if ( ! $this->is_allow_to_show( $addon['types'], $type_show ) ) {
				continue;
			}

			$printed = $this->print_addon( $addon, $type_current, $type_show );
			if ( $printed && ! empty( $this->addons[ $id ] ) ) {
				unset( $this->addons[ $id ] );
			}
		}

		echo '</div>';
	}

	/**
	 * Print addon.
	 *
	 * @since 1.6.3
	 *
	 * @param array  $addon        Addon information.
	 * @param string $type_current License type user currently have.
	 * @param array  $type_show    License type to show.
     *
     * @return bool
	 */
	private function print_addon( $addon, $type_current, $type_show ) {

		$plugins         = get_plugins();
		$plugin_basename = $this->get_plugin_basename_from_slug( $addon['slug'], $plugins );
		$status          = $this->get_addon_status( $plugin_basename, $type_current, $addon['types'] );
		$status_label    = $this->get_addon_status_label( $status );
		$action_class    = 'action-button';
		$image           = ! empty( $addon['image'] ) ? $addon['image'] : WPFORMS_PLUGIN_URL . 'assets/images/sullie.png';

		echo '<div class="addon-container">';

			echo '<div class="addon-item">';

				echo '<div class="details wpforms-clear">';
					echo '<img src="' . esc_url( $image ) . '">';
					echo '<h5 class="addon-name">' . esc_html( $addon['title'] ) . '</h5>';
					echo '<p class="addon-desc">' . esc_html( $addon['excerpt'] ) . '</p>';
				echo '</div>';

				echo '<div class="actions wpforms-clear">';

					// Status.
					if ( ! empty( $status ) && 'upgrade' !== $status ) {
						echo '<div class="status">';
							echo '<strong>' .
								sprintf(
									/* translators: %s - addon status label. */
									esc_html__( 'Status: %s', 'wpforms' ),
									'<span class="status-label status-' . esc_attr( $status ) . '">' . wp_kses_post( $status_label ) . '</span>'
								) .
								'</strong> ';
						echo '</div>';
					} else {
						$action_class = 'upgrade-button';
					}

					// Button.
					echo '<div class="' . esc_attr( $action_class ) . '">';
						if ( 'active' === $status ) {
							echo '<button class="status-' . esc_attr( $status ) . '" data-plugin="' . esc_attr( $plugin_basename ) . '" data-type="addon">';
								echo '<i class="fa fa-toggle-on" aria-hidden="true"></i>';
								esc_html_e( 'Deactivate', 'wpforms' );
							echo '</button>';
						} elseif ( 'inactive' === $status ) {
							echo '<button class="status-' . esc_attr( $status ) . '" data-plugin="' . esc_attr( $plugin_basename ) . '" data-type="addon">';
								echo '<i class="fa fa-toggle-on fa-flip-horizontal" aria-hidden="true"></i>';
								esc_html_e( 'Activate', 'wpforms' );
							echo '</button>';
						} elseif ( 'download' === $status ) {
							if ( $this->can_install ) {
								echo '<button class="status-' . esc_attr( $status ) . '" data-plugin="' . esc_url( $addon['url'] ) . '" data-type="addon">';
									echo '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
									esc_html_e( 'Install Addon', 'wpforms' );
								echo '</button>';
							}
						} else {
							echo '<a href="https://wpforms.com/account/" target="_blank" rel="noopener noreferrer" class="wpforms-btn wpforms-btn-orange">' . esc_html__( 'Upgrade Now', 'wpforms' ) . '</a>';
						}
					echo '</div>';

				echo '</div>';

			echo '</div>';

		echo '</div>';

		return true;
	}

	/**
	 * Is allow to show.
	 *
	 * @since 1.6.3
	 *
	 * @param array $types     List of addon license types.
	 * @param array $type_show List of license types to show.
	 *
	 * @return bool
	 */
	private function is_allow_to_show( $types, $type_show ) {

		foreach ( $types as $type ) {
			if ( in_array( $type, $type_show, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
     * Get addon status.
     *
     * @since 1.6.3
     *
	 * @param string $plugin_basename Plugin basename.
	 * @param string $type_current    License type user currently have.
     * @param array  $types           List of addon license types.
	 *
	 * @return string
	 */
	private function get_addon_status( $plugin_basename, $type_current, $types ) {

		if ( ! in_array( $type_current, $types, true ) ) {

			return 'upgrade';
		}
		if ( is_plugin_active( $plugin_basename ) ) {

			return 'active';
		}
		$plugins = get_plugins();
		if ( ! isset( $plugins[ $plugin_basename ] ) ) {

			return 'download';
		} elseif ( is_plugin_inactive( $plugin_basename ) ) {

			return 'inactive';
		}

		return 'upgrade';
	}

	/**
     * Get addon status label.
     *
     * @since 1.6.3
     *
	 * @param string $status Addon status.
	 *
	 * @return string
	 */
	private function get_addon_status_label( $status ) {

		if ( 'active' === $status ) {

			return esc_html__( 'Active', 'wpforms' );
		}

		if ( 'download' === $status ) {

			return esc_html__( 'Not Installed', 'wpforms' );
		}

		if ( 'inactive' === $status ) {

			return esc_html__( 'Inactive', 'wpforms' );
		}

		return '';
	}

	/**
	 * Retrieve the plugin basename from the plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The plugin slug.
	 * @param array  $plugins List of plugins.
	 *
	 * @return string The plugin basename if found, else the plugin slug.
	 */
	public function get_plugin_basename_from_slug( $slug, $plugins ) {

		$keys = array_keys( $plugins );

		foreach ( $keys as $key ) {
			if ( preg_match( '|^' . $slug . '|', $key ) ) {
				return $key;
			}
		}
		return $slug;
	}
}

new WPForms_Addons();
