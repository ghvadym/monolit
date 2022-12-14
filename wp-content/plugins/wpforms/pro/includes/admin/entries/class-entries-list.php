<?php

/**
 * Display list of all entries for a single form.
 *
 * @since 1.0.0
 */
class WPForms_Entries_List {

	/**
	 * Store admin alerts.
	 *
	 * @since 1.1.6
	 *
	 * @var array
	 */
	public $alerts = array();

	/**
	 * Abort. Bail on proceeding to process the page.
	 *
	 * @since 1.1.6
	 *
	 * @var bool
	 */
	public $abort = false;

	/**
	 * Form ID.
	 *
	 * @since 1.1.6
	 *
	 * @var int
	 */
	public $form_id;

	/**
	 * Form object.
	 *
	 * @since 1.1.6
	 *
	 * @var WPForms_Form_Handler
	 */
	public $form;

	/**
	 * Forms object.
	 *
	 * @since 1.1.6
	 *
	 * @var WPForms_Form_Handler[]
	 */
	public $forms;

	/**
	 * Entries object.
	 *
	 * @since 1.1.6
	 *
	 * @var WPForms_Entries_Table
	 */
	public $entries;

	/**
	 * Array of entries to select for filtering.
	 *
	 * @since 1.4.4
	 *
	 * @var array
	 */
	protected $filter = array();

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Maybe load entries page.
		add_action( 'admin_init', array( $this, 'init' ) );

		// Setup screen options - this needs to run early.
		add_action( 'load-wpforms_page_wpforms-entries', array( $this, 'screen_options' ) );
		add_filter( 'set-screen-option', array( $this, 'screen_options_set' ), 10, 3 );
		add_filter( 'set_screen_option_wpforms_entries_per_page', [ $this, 'screen_options_set' ], 10, 3 );

		// Heartbeat doesn't pass $_GET parameters checked by $this->init() condition.
		add_filter( 'heartbeat_received', array( $this, 'heartbeat_new_entries_check' ), 10, 3 );

		// AJAX-callbacks.
		add_action( 'wp_ajax_wpforms_entry_list_process_delete_all', array( $this, 'process_delete' ) );
	}

	/**
	 * Determine if the user is viewing the entries list page, if so, party on.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Check what page and view.
		$page = ! empty( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$view = $this->get_current_screen_view();

		// Only load if we are actually on the overview page.
		if ( 'wpforms-entries' !== $page || 'list' !== $view ) {
			return;
		}

		$form_id = $this->get_filtered_form_id();

		// Init default entries screen.
		if ( empty( $form_id ) || ! wpforms_current_user_can( 'view_entries_form_single', $form_id ) ) {
			return;
		}

		// Load the classes that builds the entries table.
		$this->load_entries_list_table();
		$this->remove_get_parameters();

		// Processing and setup.
		add_action( 'wpforms_entries_init', array( $this, 'process_filter_dates' ), 7, 1 );
		add_action( 'wpforms_entries_init', array( $this, 'process_filter_search' ), 7, 1 );
		add_action( 'wpforms_entries_init', array( $this, 'process_read' ), 8, 1 );
		add_action( 'wpforms_entries_init', array( $this, 'process_columns' ), 8, 1 );
		add_action( 'wpforms_entries_init', array( $this, 'setup' ), 10, 1 );

		do_action( 'wpforms_entries_init', 'list' );

		// Output.
		add_action( 'wpforms_admin_page', array( $this, 'list_all' ) );
		add_action( 'wpforms_admin_page', array( $this, 'field_column_setting' ) );
		add_action( 'wpforms_entry_list_title', array( $this, 'list_form_actions' ), 10, 1 );

		// Enqueues.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
		add_filter( 'wpforms_admin_strings', array( $this, 'js_strings' ) );
	}

	/**
	 * Remove unnecessary $_GET parameters for shorter URL.
	 *
	 * @since 1.6.2
	 */
	private function remove_get_parameters() {

		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}

	/**
	 * Load the PHP classes and initialize an entries table.
	 *
	 * @since 1.5.7
	 */
	public function load_entries_list_table() {

		if ( ! class_exists( 'WP_List_Table', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		require_once WPFORMS_PLUGIN_DIR . 'pro/includes/admin/entries/class-entries-list-table.php';

		// Create an `WPForms_Entries_Table` instance and process bulk actions.
		$this->entries = new WPForms_Entries_Table();
		$this->entries->process_bulk_actions();
	}

	/**
	 * Get the current Entries view: 'list' or 'details'.
	 *
	 * @since 1.4.4
	 *
	 * @return string
	 */
	protected function get_current_screen_view() {

		$view = ! empty( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'list';

		return apply_filters( 'wpforms_entries_list_get_current_screen_view', $view );
	}

	/**
	 * Add per-page screen option to the Entries table.
	 *
	 * @since 1.0.0
	 */
	public function screen_options() {

		$screen = get_current_screen();

		if ( 'wpforms_page_wpforms-entries' !== $screen->id ) {
			return;
		}

		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Number of entries per page:', 'wpforms' ),
				'option'  => 'wpforms_entries_per_page',
				'default' => apply_filters( 'wpforms_entries_per_page', 30 ),
			)
		);
	}

	/**
	 * Entries table per-page screen option value
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $status
	 * @param string $option
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function screen_options_set( $status, $option, $value ) {

		if ( 'wpforms_entries_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Enqueue assets for the entries pages.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {

		// JavaScript.
		wp_enqueue_script(
			'wpforms-flatpickr',
			WPFORMS_PLUGIN_URL . 'assets/js/flatpickr.min.js',
			array( 'jquery' ),
			'4.6.3'
		);

		// CSS.
		wp_enqueue_style(
			'wpforms-flatpickr',
			WPFORMS_PLUGIN_URL . 'assets/css/flatpickr.min.css',
			array(),
			'4.6.3'
		);

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-admin-entries',
			WPFORMS_PLUGIN_URL . "pro/assets/js/admin/entries{$min}.js",
			[ 'jquery', 'wpforms-flatpickr' ],
			WPFORMS_VERSION,
			true
		);

		// Hook for addons.
		do_action( 'wpforms_entries_enqueue', 'list' );
	}

	/**
	 * Watch for and run complete form exports.
	 *
	 * @since 1.1.6
	 */
	public function process_export() {

		$form_id = $this->get_filtered_form_id();
		// Check for run switch.
		if ( empty( $_GET['export'] ) || ! $form_id || 'all' !== $_GET['export'] ) {
			return;
		}

		_deprecated_function( __CLASS__ . '::' . __METHOD__, '1.5.5 of WPForms plugin', 'WPForms\Pro\Admin\Export\Export class' );

		// Security check.
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wpforms_entry_list_export' ) ) {
			return;
		}
		require_once WPFORMS_PLUGIN_DIR . 'pro/includes/admin/entries/class-entries-export.php';
		$export             = new WPForms_Entries_Export();
		$export->entry_type = 'all';
		$export->form_id    = $form_id;
		$export->export();
	}

	/**
	 * Watch for and run complete marking all entries as read.
	 *
	 * @since 1.1.6
	 */
	public function process_read() {

		$form_id = $this->get_filtered_form_id();
		// Check for run switch.
		if ( empty( $_GET['action'] ) || ! $form_id || 'markread' !== $_GET['action'] ) {
			return;
		}

		// Security check.
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wpforms_entry_list_markread' ) ) {
			return;
		}

		wpforms()->entry->mark_all_read( $form_id );

		$this->alerts[] = array(
			'type'    => 'success',
			'message' => esc_html__( 'All entries marked as read.', 'wpforms' ),
			'dismiss' => true,
		);
	}

	/**
	 * Watch for and update list column settings.
	 *
	 * @since 1.4.0
	 */
	public function process_columns() {

		// Check for run switch and data.
		if ( empty( $_POST['action'] ) || empty( $_POST['form_id'] ) || 'list-columns' !== $_POST['action'] ) {
			return;
		}

		// Security check.
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpforms_entry_list_columns' ) ) {
			return;
		}

		$post_id = absint( $_POST['form_id'] );

		// Update or delete.
		if ( empty( $_POST['fields'] ) ) {

			wpforms()->form->delete_meta( $post_id, 'entry_columns', array( 'cap' => 'view_entries_form_single' ) );

		} else {

			$fields = array_map( 'intval', $_POST['fields'] );

			wpforms()->form->update_meta( $post_id, 'entry_columns', $fields, array( 'cap' => 'view_entries_form_single' ) );
		}
	}

	/**
	 * Entry deletion and trigger if needed.
	 *
	 * @since 1.4.0
	 * @since 1.6.4 Updated for using like an AJAX-callback.
	 */
	public function process_delete() {

		// Security check.
		if ( ! check_ajax_referer( 'wpforms-admin', 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session expired. Please reload the builder.', 'wpforms' ) );
		}

		$form_id = $this->get_filtered_form_id();

		// Check for run switch.
		if ( ! $form_id ) {
			wp_send_json_error( esc_html__( 'Something went wrong while performing this action.', 'wpforms' ) );
		}

		// Permission check.
		if ( ! wpforms_current_user_can( 'delete_entries_form_single', $form_id ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'wpforms' ) );
		}

		if ( $this->is_list_filtered() ) {
			$this->process_filter_dates();
			$this->process_filter_search();
		}

		// Check if entries filtered.
		if ( ! empty( $this->filter['entry_id'] ) ) {
			$deleted = wpforms()->entry->delete_where_in( 'entry_id', $this->filter['entry_id'] );
			wpforms()->entry_meta->delete_where_in( 'entry_id', $this->filter['entry_id'] );
			wpforms()->entry_fields->delete_where_in( 'entry_id', $this->filter['entry_id'] );

		} else {
			$deleted = wpforms()->entry->delete_by( 'form_id', $form_id );
			wpforms()->entry_meta->delete_by( 'form_id', $form_id );
			wpforms()->entry_fields->delete_by( 'form_id', $form_id );
			$deleted = $deleted ? -1 : 0;
		}

		$redirect_url = ! empty( $_GET['url'] ) ? add_query_arg( 'deleted', $deleted, esc_url_raw( wp_unslash( $_GET['url'] ) ) ) : '';

		WPForms\Pro\Admin\DashboardWidget::clear_widget_cache();
		WPForms\Pro\Admin\Entries\DefaultScreen::clear_widget_cache();

		wp_send_json_success( $redirect_url );
	}

	/**
	 * Return validated information about search or FALSE.
	 *
	 * @since 1.6.3
	 *
	 * @return bool|array
	 */
	protected function get_filter_search_parts() {

		if ( empty( $_GET['search'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		$expected = [ 'field', 'comparison', 'term' ];
		$valid    = true;
		foreach ( $expected as $field ) {
			if ( ! isset( $_GET['search'][ $field ] ) || '' === $_GET['search'][ $field ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$valid = false;
				break;
			}
		}

		if ( ! $valid ) {
			return false;
		}

		return array_map( 'sanitize_text_field', $_GET['search'] ); // phpcs:ignore
	}

	/**
	 * Return HTML with information about the search filter.
	 *
	 * @since 1.6.3
	 *
	 * @return string
	 */
	protected function get_filter_search_html() {

		$form_id = $this->get_filtered_form_id();
		$data    = $this->get_filter_search_parts();

		if ( $data ) {
			$comparisons = [
				'contains'     => __( 'contains', 'wpforms' ),
				'contains_not' => __( 'does not contain', 'wpforms' ),
				'is'           => __( 'is', 'wpforms' ),
				'is_not'       => __( 'is not', 'wpforms' ),
			];
			$comparison  = isset( $comparisons[ $data['comparison'] ] ) ? $comparisons[ $data['comparison'] ] : $comparisons['contains'];
			$field       = sanitize_text_field( $data['field'] );
			$term        = sanitize_text_field( $data['term'] );

			if ( is_numeric( $field ) && $form_id ) {
				$meta = wpforms()->form->get_field( $form_id, $field );
				if ( isset( $meta['label'] ) ) {
					$field = $meta['label'];
				}
			} else {
				$field = __( 'any form field', 'wpforms' );
			}

			$html = sprintf( /* translators: 1:  field name 2: operation 3: term */
				__( 'where %1$s %2$s "%3$s"', 'wpforms' ),
				'<em>' . $field . '</em>',
				$comparison,
				'<em>' . $term . '</em>'
			);

			return $html;
		}

		return '';
	}

	/**
	 * Get filtered form ID.
	 *
	 * @since 1.6.3
	 *
	 * @return int
	 */
	private function get_filtered_form_id() {

		return ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get filtered date range.
	 *
	 * @since 1.6.3
	 *
	 * @return array
	 */
	private function get_filtered_dates() {

		if ( empty( $_GET['date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return [];
		}
		$dates = (array) explode( ' - ', sanitize_text_field( wp_unslash( $_GET['date'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return array_map( 'sanitize_text_field', $dates );
	}

	/**
	 * Return an array with information (HTML and id) for each filter for this current view
	 *
	 * @since 1.6.3
	 *
	 * @return array
	 */
	public function get_filters_html() {

		$filters = [
			'.search-box' => $this->get_filter_search_html(),
		];

		$dates = $this->get_filtered_dates();

		if ( $dates ) {
			$dates = array_map(
				function ( $date ) {

					return date_i18n( 'M j, Y', strtotime( $date ) );
				},
				$dates
			);

			$html = '';

			switch ( count( $dates ) ) {
				case 1:
					$html = sprintf( /* translators: %s: Date */
						esc_html__( 'on %s', 'wpforms' ),
						'<em>' . $dates[0] . '</em>'
					);
					break;
				case 2:
					$html = sprintf( /* translators: 1: Date 2: Date */
						esc_html__( 'between %1$s and %2$s', 'wpforms' ),
						'<em>' . $dates[0] . '</em>',
						'<em>' . $dates[1] . '</em>'
					);
					break;
			}

			$filters['.wpforms-filter-date'] = $html;
		}

		return array_filter( $filters );
	}

	/**
	 * Watch for filtering requests from a dates range selection.
	 *
	 * @since 1.4.4
	 */
	public function process_filter_dates() {

		$form_id = $this->get_filtered_form_id();

		if ( empty( $form_id ) ) {
			return;
		}

		$dates = $this->get_filtered_dates();

		if ( empty( $dates ) ) {
			return;
		}

		$this->prepare_entry_ids_for_get_entries_args(
			wpforms()->entry->get_entries(
				[
					'select'  => 'entry_ids',
					'number'  => 0,
					'form_id' => $form_id,
					'date'    => 1 === count( $dates ) ? $dates[0] : $dates,
				]
			)
		);
	}

	/**
	 * Watch for filtering requests from a search field.
	 *
	 * @since 1.4.4
	 */
	public function process_filter_search() {

		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		$form_id = $this->get_filtered_form_id();
		// Check for run switch and that all data is present.
		if (
			! $form_id ||
			! isset( $_GET['search'] ) ||
			(
				! isset( $_GET['search']['term'] ) ||
				! isset( $_GET['search']['field'] ) ||
				empty( $_GET['search']['comparison'] )
			)
		) {
			return;
		}

		// Prepare the data.
		$term       = sanitize_text_field( wp_unslash( $_GET['search']['term'] ) );
		$field      = sanitize_text_field( wp_unslash( $_GET['search']['field'] ) ); // We must use as a string for the work field with id equal 0.
		$comparison = in_array( $_GET['search']['comparison'], array( 'contains', 'contains_not', 'is', 'is_not' ), true ) ? sanitize_text_field( wp_unslash( $_GET['search']['comparison'] ) ) : 'contains';
		$args       = array();

		/*
		 * Because empty fields were not migrated to a fields table in 1.4.3, we don't have that data
		 * and can't filter those with empty values.
		 * The current workaround - display all entries (instead of none at all).
		 *
		 * TODO: remove this "! empty( $term )" check when empty data will be saved to DB table.
		 */
		if ( ! empty( $term ) ) {

			$args['select']        = 'entry_ids';
			$args['number']        = -1;
			$args['form_id']       = $form_id;
			$args['value']         = $term;
			$args['value_compare'] = $comparison;

			if ( is_numeric( $field ) ) {
				$args['field_id'] = $field;
			}
		}

		if ( empty( $args ) ) {
			return;
		}

		$this->prepare_entry_ids_for_get_entries_args(
			wpforms()->entry_fields->get_fields( $args )
		);
		//phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get the entry IDs based on the entries array and pass it further to the WPForms_Entry_Handler::get_entries() method via a filter.
	 *
	 * @since 1.4.4
	 *
	 * @param array $entries
	 */
	protected function prepare_entry_ids_for_get_entries_args( $entries ) {

		$entry_ids = array();

		if ( is_array( $entries ) ) {
			foreach ( $entries as $entry ) {
				$entry_ids[] = $entry->entry_id;
			}
		}

		$entry_ids = array_unique( $entry_ids );

		if ( ! isset( $this->filter['entry_id'] ) ) {
			$this->filter = array(
				'entry_id' => $entry_ids,
			);
		} else {
			$this->filter['entry_id'] = array_intersect( $this->filter['entry_id'], $entry_ids );
		}

		// TODO: when we will drop PHP 5.2 support, this can be changed to a closure.
		add_filter( 'wpforms_entry_handler_get_entries_args', array( $this, 'get_filtered_entry_table_args' ) );
	}

	/**
	 * Merge default arguments to entries retrieval with the one we send to filter.
	 *
	 * @since 1.4.4
	 *
	 * @param array $args
	 *
	 * @return array Filtered arguments.
	 */
	public function get_filtered_entry_table_args( $args ) {
		$this->filter['is_filtered'] = true;
		return array_merge( $args, $this->filter );
	}

	/**
	 * Setup entry list page data.
	 *
	 * This function does the error checking and variable setup.
	 *
	 * @since 1.1.6
	 */
	public function setup() {

		if ( wpforms_current_user_can( 'view_forms' ) ) {
			// Fetch all forms.
			$this->forms = wpforms()->form->get(
				'',
				array(
					'orderby' => 'ID',
					'order'   => 'ASC',
				)
			);
		}

		// Check that the user has created at least one form.
		if ( empty( $this->forms ) ) {

			$this->alerts[] = array(
				'type'    => 'info',
				'message' =>
					sprintf(
						wp_kses(
							/* translators: %s - WPForms Builder page. */
							__( 'Whoops, you haven\'t created a form yet. Want to <a href="%s">give it a go</a>?', 'wpforms' ),
							array(
								'a' => array(
									'href' => array(),
								),
							)
						),
						admin_url( 'admin.php?page=wpforms-builder' )
					),
				'abort'   => true,
			);

		} else {
			$form_id       = $this->get_filtered_form_id();
			$this->form_id = $form_id ? $form_id : apply_filters( 'wpforms_entry_list_default_form_id', absint( $this->forms[0]->ID ) );
			$this->form    = wpforms()->form->get( $this->form_id, array( 'cap' => 'view_entries_form_single' ) );
		}
	}

	/**
	 * Whether the current list of entries is filtered somehow or not.
	 *
	 * @since 1.4.4
	 *
	 * @return bool
	 */
	protected function is_list_filtered() {

		$is_filtered = false;

		if (
			isset( $_GET['search'] ) ||
			isset( $_GET['date'] )
		) {
			$is_filtered = true;
		}

		return apply_filters( 'wpforms_entries_list_is_list_filtered', $is_filtered );
	}

	/**
	 * List all entries in a specific form.
	 *
	 * @since 1.0.0
	 */
	public function list_all() {

		$form_data = ! empty( $this->form->post_content ) ? wpforms_decode( $this->form->post_content ) : '';
		?>

		<div id="wpforms-entries-list" class="wrap wpforms-admin-wrap">

			<h1 class="page-title"><?php esc_html_e( 'Entries', 'wpforms' ); ?></h1>

			<?php
			// Admin notices.
			$this->display_alerts();
			if ( $this->abort ) {
				echo '</div>'; // close wrap.

				return;
			}

			$this->entries->form_id   = $this->form_id;
			$this->entries->form_data = $form_data;
			$this->entries->prepare_items();

			$last_entry = wpforms()->entry->get_last( $this->form_id );
			?>

			<div class="wpforms-admin-content">

			<?php

			if ( empty( $this->entries->items ) && ! isset( $_GET['search'] ) && ! isset( $_GET['date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Output no entries screen.
				echo wpforms_render( 'admin/empty-states/no-entries' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			} else {

				do_action( 'wpforms_entry_list_title', $form_data, $this );
			?>

				<form id="wpforms-entries-table" method="GET"
					action="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-entries' ) ); ?>"
					<?php echo ( ! $this->is_list_filtered() && isset( $last_entry->entry_id ) ) ? 'data-last-entry-id="' . absint( $last_entry->entry_id ) . '"' : ''; ?> <?php printf( 'data-filtered-count="%d"', absint( $this->entries->counts['total'] ) ); ?>>

					<?php if ( $this->get_filters_html() ) : ?>

						<div id="wpforms-reset-filter">
							<?php
							echo wp_kses(
								sprintf( /* translators: %s: Number of items */
									_n(
										'Found <strong>%s item</strong>',
										'Found <strong>%s items</strong>',
										absint( count( $this->entries->items ) ),
										'wpforms-entries'
									),
									absint( $this->entries->counts['total'] )
								),
								[
									'strong' => [],
								]
							);
							?>

							<?php foreach ( $this->get_filters_html() as $id => $html ) : ?>
								<?php
								echo wp_kses(
									$html,
									[ 'em' => [] ]
								);
								?>
								<i class="reset fa fa-times-circle" data-scope="<?php echo esc_attr( $id ); ?>"></i>
							<?php endforeach; ?>
						</div>
					<?php endif ?>

					<input type="hidden" name="page" value="wpforms-entries" />
					<input type="hidden" name="view" value="list" />
					<input type="hidden" name="form_id" value="<?php echo absint( $this->form_id ); ?>" />

					<?php $this->entries->views(); ?>

					<?php $this->entries->search_box( esc_html__( 'Search', 'wpforms' ), 'wpforms-entries' ); ?>

					<?php $this->entries->display(); ?>

				</form>

			<?php } ?>

			</div>

		</div>

		<?php
	}

	/**
	 * Settings for field column personalization!
	 *
	 * @since 1.4.0
	 * @since 1.5.7 Added an `Entry Notes` column.
	 */
	public function field_column_setting() {

		$form_data         = ! empty( $this->form->post_content ) ? wpforms_decode( $this->form->post_content ) : array();
		$entry_id_selected = false;

		if ( ! empty( $form_data['meta']['entry_columns'] ) && is_array( $form_data['meta']['entry_columns'] ) ) {
			$entry_id_selected = in_array( WPForms_Entries_Table::COLUMN_ENTRY_ID, $form_data['meta']['entry_columns'], true );
		}
		?>
		<div id="wpforms-field-column-select" style="display:none;">

			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-entries&view=list&form_id=' . (int) $this->form_id ) ); ?>" style="display:none;">
				<input type="hidden" name="action" value="list-columns"/>
				<input type="hidden" name="form_id" value="<?php echo (int) $this->form_id; ?>"/>
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'wpforms_entry_list_columns' ) ); ?>">
				<p style="margin-bottom: 20px">
					<?php
					esc_html_e( 'Select the fields to show when viewing the entries list for this form.', 'wpforms' );
					if ( empty( $form_data['meta']['entry_columns'] ) ) {
						echo ' ' . esc_html__( 'Currently columns have not been configured, so we\'re showing the first 3 fields.', 'wpforms' );
					}
					?>
				</p>
				<select name="fields[]" multiple>
					<option value="" placeholder><?php esc_html_e( 'Select columns&hellip;', 'wpforms' ); ?></option>
					<?php
					/*
					 * Display first those that were already saved.
					 */
					if ( ! empty( $form_data['meta']['entry_columns'] ) ) {
						foreach ( $form_data['meta']['entry_columns'] as $id ) {

							switch ( (int) $id ) {
								case WPForms_Entries_Table::COLUMN_ENTRY_ID:
									$name = esc_html__( 'Entry ID', 'wpforms' );
									break;

								case WPForms_Entries_Table::COLUMN_NOTES_COUNT:
									$name = esc_html__( 'Entry Notes', 'wpforms' );
									break;

								default:
									if ( empty( $form_data['fields'][ $id ] ) ) {
										continue 2;
									}
									$name = isset( $form_data['fields'][ $id ]['label'] ) && ! wpforms_is_empty_string( trim( $form_data['fields'][ $id ]['label'] ) ) ? wp_strip_all_tags( $form_data['fields'][ $id ]['label'] ) : sprintf( /* translators: %d - field ID. */ __( 'Field #%d', 'wpforms' ), absint( $id ) );
							}

							printf( '<option value="%d" selected>%s</option>', absint( $id ), esc_html( $name ) );
						}
					}

					/*
					 * Now display all other fields, including special ones (like Entry ID).
					 */
					if ( ! empty( $form_data['fields'] ) && is_array( $form_data['fields'] ) ) {
						// Special column names, that should be at the top of the list.
						if (
							empty( $form_data['meta']['entry_columns'] ) ||
							! in_array( WPForms_Entries_Table::COLUMN_ENTRY_ID, $form_data['meta']['entry_columns'], true )
						) {
							printf( '<option value="%d">%s</option>', (int) WPForms_Entries_Table::COLUMN_ENTRY_ID, esc_html__( 'Entry ID', 'wpforms' ) );
						}

						if (
							empty( $form_data['meta']['entry_columns'] ) ||
							! in_array( WPForms_Entries_Table::COLUMN_NOTES_COUNT, $form_data['meta']['entry_columns'], true )
						) {
							printf( '<option value="%d">%s</option>', (int) WPForms_Entries_Table::COLUMN_NOTES_COUNT, esc_html__( 'Entry Notes', 'wpforms' ) );
						}

						// Regular form fields.
						foreach ( $form_data['fields'] as $id => $field ) {
							if (
								! empty( $form_data['meta']['entry_columns'] ) &&
								in_array( $id, $form_data['meta']['entry_columns'], true )
							) {
								continue;
							}

							if ( ! in_array( $field['type'], WPForms_Entries_Table::get_columns_form_disallowed_fields(), true ) ) {
								$name = isset( $field['label'] ) && ! wpforms_is_empty_string( trim( $field['label'] ) ) ? wp_strip_all_tags( $field['label'] ) : sprintf( /* translators: %d - field ID. */ __( 'Field #%d', 'wpforms' ), absint( $id ) );
								printf( '<option value="%d">%s</option>', (int) $id, esc_html( $name ) );
							}
						}
					}
					?>
				</select>
			</form>

		</div>
		<?php
	}

	/**
	 * Entries list form actions.
	 *
	 * @since 1.1.6
	 *
	 * @param array $form_data Form data and settings.
	 */
	public function list_form_actions( $form_data ) {

		$base = add_query_arg(
			array(
				'page'    => 'wpforms-entries',
				'view'    => 'list',
				'form_id' => absint( $this->form_id ),
			),
			admin_url( 'admin.php' )
		);

		// Edit Form URL.
		$edit_url = add_query_arg(
			array(
				'page'    => 'wpforms-builder',
				'view'    => 'fields',
				'form_id' => absint( $this->form_id ),
			),
			admin_url( 'admin.php' )
		);

		// Preview Entry URL.
		$preview_url = esc_url( wpforms_get_form_preview_url( $this->form_id ) );

		// Export Entry URL.
		$export_url = add_query_arg(
			array(
				'page'   => 'wpforms-tools',
				'view'   => 'export',
				'form'   => absint( $this->form_id ),
				'search' => ! empty( $_GET['search'] ) ? $_GET['search'] : array(), // phpcs:ignore
				'date'   => ! empty( $_GET['date'] ) ? $_GET['date'] : array(), // phpcs:ignore
			),
			admin_url( 'admin.php' )
		);

		// Mark Read URL.
		$read_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'markread',
				),
				$base
			),
			'wpforms_entry_list_markread'
		);

		// Delete all entries.
		$delete_url = wp_nonce_url( $base, 'bulk-entries' );
		?>

		<div class="form-details wpforms-clear">

			<span class="form-details-sub"><?php esc_html_e( 'Select Form', 'wpforms' ); ?></span>

			<h3 class="form-details-title">
				<?php
				if ( ! empty( $form_data['settings']['form_title'] ) ) {
					echo wp_strip_all_tags( $form_data['settings']['form_title'] );
				}
				$this->form_selector_html();
				?>
			</h3>

			<div class="form-details-actions">

				<?php if ( $this->is_list_filtered() ) : ?>
					<a href="<?php echo esc_url( $base ); ?>" class="form-details-actions-entries">
						<span class="dashicons dashicons-list-view"></span>
						<?php esc_html_e( 'All Entries', 'wpforms' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( \wpforms_current_user_can( 'edit_form_single', $this->form_id ) ) : ?>
					<a href="<?php echo esc_url( $edit_url ); ?>" class="form-details-actions-edit">
						<span class="dashicons dashicons-edit"></span>
						<?php esc_html_e( 'Edit This Form', 'wpforms' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( \wpforms_current_user_can( 'view_form_single', $this->form_id ) ) : ?>
					<a href="<?php echo esc_url( $preview_url ); ?>" class="form-details-actions-preview" target="_blank" rel="noopener noreferrer">
						<span class="dashicons dashicons-visibility"></span>
						<?php esc_html_e( 'Preview Form', 'wpforms' ); ?>
					</a>
				<?php endif; ?>


				<a href="<?php echo esc_url( $export_url ); ?>" class="form-details-actions-export">
					<span class="dashicons dashicons-migrate"></span>
					<?php echo $this->is_list_filtered() ? esc_html__( 'Export Filtered (CSV)', 'wpforms' ) : esc_html__( 'Export All (CSV)', 'wpforms' ); ?>
				</a>

				<a href="<?php echo esc_url( $read_url ); ?>" class="form-details-actions-read">
					<span class="dashicons dashicons-marker"></span>
					<?php esc_html_e( 'Mark All Read', 'wpforms' ); ?>
				</a>

				<?php if ( \wpforms_current_user_can( 'delete_entries_form_single', $this->form_id ) ) : ?>
					<a href="<?php echo esc_url( $delete_url ); ?>" class="form-details-actions-deleteall">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Delete All', 'wpforms' ); ?>
					</a>
				<?php endif; ?>

			</div>

		</div>
		<?php
	}

	/**
	 * Display form selector HTML.
	 *
	 * @since 1.5.8
	 */
	protected function form_selector_html() {

		if ( ! wpforms_current_user_can( 'view_forms' ) ) {
			return;
		}

		if ( empty( $this->forms ) ) {
			return;
		}

		?>
		<div class="form-selector">
			<a href="#" title="<?php esc_attr_e( 'Open form selector', 'wpforms' ); ?>" class="toggle dashicons dashicons-arrow-down-alt2"></a>
			<div class="form-list">
				<ul>
					<?php
					foreach ( $this->forms as $key => $form ) {
						$form_url = add_query_arg(
							array(
								'page'    => 'wpforms-entries',
								'view'    => 'list',
								'form_id' => absint( $form->ID ),
							),
							admin_url( 'admin.php' )
						);
						echo '<li><a href="' . esc_url( $form_url ) . '">' . esc_html( $form->post_title ) . '</a></li>';
					}
					?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Display admin notices and errors.
	 *
	 * @since 1.1.6
	 * @todo Refactor or eliminate this
	 *
	 * @param string $display
	 * @param bool $wrap
	 */
	public function display_alerts( $display = '', $wrap = false ) {

		if ( empty( $this->alerts ) ) {
			return;

		} else {

			if ( empty( $display ) ) {
				$display = array( 'error', 'info', 'warning', 'success' );
			} else {
				$display = (array) $display;
			}

			foreach ( $this->alerts as $alert ) {

				$type = ! empty( $alert['type'] ) ? $alert['type'] : 'info';

				if ( in_array( $type, $display, true ) ) {
					$class  = 'notice-' . $type;
					$class .= ! empty( $alert['dismiss'] ) ? ' is-dismissible' : '';
					$output = '<div class="notice ' . $class . '"><p>' . $alert['message'] . '</p></div>';
					if ( $wrap ) {
						echo '<div class="wrap">' . $output . '</div>';
					} else {
						echo $output;
					}
					if ( ! empty( $alert['abort'] ) ) {
						$this->abort = true;
						break;
					}
				}
			}
		}
	}

	/**
	 * Check for new entries using Heartbeat API.
	 *
	 * @since 1.5.0
	 *
	 * @param array  $response  The Heartbeat response.
	 * @param array  $data      The $_POST data sent.
	 * @param string $screen_id The screen id.
	 *
	 * @return array
	 */
	public function heartbeat_new_entries_check( $response, $data, $screen_id ) {

		if ( 'wpforms_page_wpforms-entries' !== $screen_id ) {
			return $response;
		}

		$entry_id = ! empty( $data['wpforms_new_entries_entry_id'] ) ? absint( $data['wpforms_new_entries_entry_id'] ) : 0;
		$form_id  = ! empty( $data['wpforms_new_entries_form_id'] ) ? absint( $data['wpforms_new_entries_form_id'] ) : 0;

		if ( empty( $form_id ) ) {
			return $response;
		}

		$entries_count = wpforms()->entry->get_next_count( $entry_id, $form_id );

		if ( empty( $entries_count ) ) {
			return $response;
		}

		/* translators: %d - Number of form entries. */
		$response['wpforms_new_entries_notification'] = esc_html( sprintf( _n( 'See %d new entry', 'See %d new entries', $entries_count, 'wpforms' ), $entries_count ) );

		return $response;
	}

	/**
	 * Localize needed strings.
	 *
	 * @since 1.6.3
	 *
	 * @param array $strings JS strings.
	 *
	 * @return mixed
	 */
	public function js_strings( $strings ) {

		$strings['lang_code']    = sanitize_key( wpforms_get_language_code() );
		$strings['default_date'] = [];
		$dates                   = $this->get_filtered_dates();
		if ( $dates ) {
			if ( count( $dates ) === 1 ) {
				$dates[1] = $dates[0];
			}
			$strings['default_date'] = [ sanitize_text_field( $dates[0] ), sanitize_text_field( $dates[1] ) ];
		}

		return $strings;
	}
}

new WPForms_Entries_List();
