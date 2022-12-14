/* global wpforms_builder, WPFormsBuilder */
/**
 * WPForms Form Builder Education function.
 *
 * @since 1.5.1
 */

'use strict';

var WPFormsBuilderEducation = window.WPFormsBuilderEducation || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 1.5.1
	 *
	 * @type {Object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.5.1
		 */
		init: function() {
			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 1.5.1
		 */
		ready: function() {
			app.events();
		},

		/**
		 * Register JS events.
		 *
		 * @since 1.5.1
		 */
		events: function() {
			app.clickEvents();
		},

		/**
		 * Registers JS click events.
		 *
		 * @since 1.5.1
		 */
		clickEvents: function() {

			$( document ).on(
				'click',
				'.wpforms-add-fields-button, .wpforms-panel-sidebar-section, .wpforms-builder-settings-block-add',
				function( event ) {

					var $this = $( this );

					if ( $this.hasClass( 'education-modal' ) ) {

						event.preventDefault();
						event.stopImmediatePropagation();

						switch ( $this.data( 'action' ) ) {
							case 'activate':
								app.activateModal( $this.data( 'name' ), $this.data( 'path' ), $this.data( 'nonce' )  );
								break;
							case 'install':
								app.installModal( $this.data( 'name' ), $this.data( 'url' ), $this.data( 'nonce' ), $this.data( 'license' ) );
								break;
							case 'upgrade':
								app.upgradeModal( $this.data( 'name' ), $this.data( 'field-name' ), $this.data( 'license' ) );
								break;
							case 'license':
								app.licenseModal();
								break;
						}
					}
				}
			);
		},

		/**
		 * Addon activate modal.
		 *
		 * @since 1.5.1
		 *
		 * @param {string} feature Feature name.
		 * @param {string} path    Addon path.
		 * @param {string} nonce   Action nonce.
		 */
		activateModal: function( feature, path, nonce ) {

			$.alert( {
				title  : false,
				content: wpforms_builder.education_activate_prompt.replace( /%name%/g, feature ),
				icon   : 'fa fa-info-circle',
				type   : 'blue',
				buttons: {
					confirm: {
						text    : wpforms_builder.education_activate_confirm,
						btnClass: 'btn-confirm',
						keys    : [ 'enter' ],
						action  : function() {

							var currentModal = this,
								$confirm     = currentModal.$body.find( '.btn-confirm' );

							$confirm.prop( 'disabled', true ).html( '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> ' + wpforms_builder.education_activating );

							app.activateAddon( path, nonce, currentModal );

							return false;
						}
					},
					cancel : {
						text: wpforms_builder.cancel
					}
				}
			} );
		},

		/**
		 * Activate addon via AJAX.
		 *
		 * @since 1.5.1
		 *
		 * @param {string} path          Addon path.
		 * @param {string} nonce         Action nonce.
		 * @param {object} previousModal Previous modal instance.
		 */
		activateAddon: function( path, nonce, previousModal ) {

			$.post(
				wpforms_builder.ajax_url,
				{
					action: 'wpforms_activate_addon',
					nonce : nonce,
					plugin: path
				},
				function( res ) {

					previousModal.close();

					if ( res.success ) {
						app.saveModal();
					} else {
						$.alert( {
							title  : false,
							content: res.data,
							icon   : 'fa fa-exclamation-circle',
							type   : 'orange',
							buttons: {
								confirm: {
									text    : wpforms_builder.close,
									btnClass: 'btn-confirm',
									keys    : [ 'enter' ],
								}
							}
						} );
					}
				}
			);
		},

		/**
		 * Ask user if they would like to save form and refresh form builder.
		 *
		 * @since 1.5.1
		 * @since 1.6.2.3 Added a new `title` argument.
		 *
		 * @param {string} title Modal title.
		 */
		saveModal: function( title ) {

			title = title || wpforms_builder.education_activated;

			$.alert( {
				title  : title.replace( /\.$/, '' ), // Remove a dot in the title end.
				content: wpforms_builder.education_save_prompt,
				icon   : 'fa fa-check-circle',
				type   : 'green',
				buttons: {
					confirm: {
						text    : wpforms_builder.education_save_confirm,
						btnClass: 'btn-confirm',
						keys    : [ 'enter' ],
						action  : function() {

							var $confirm = this.$body.find( '.btn-confirm' );

							$confirm.prop( 'disabled', true ).html( '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> ' + wpforms_builder.saving );

							if ( WPFormsBuilder.formIsSaved() ) {
								location.reload( true );
							}

							WPFormsBuilder.formSave().done( function() {
								location.reload( true );
							} );

							return false;
						}
					},
					cancel : {
						text: wpforms_builder.close
					}
				}
			} );
		},

		/**
		 * Addon install modal.
		 *
		 * @since 1.5.1
		 *
		 * @param {string} feature Feature name.
		 * @param {string} url     Install URL.
		 * @param {string} nonce   Action nonce.
		 * @param {string} type    License level.
		 */
		installModal: function( feature, url, nonce, type ) {

			if ( ! url || '' === url ) {
				app.upgradeModal( feature, '', type );
				return;
			}

			$.alert( {
				title   : false,
				content : wpforms_builder.education_install_prompt.replace( /%name%/g, feature ),
				icon    : 'fa fa-info-circle',
				type    : 'blue',
				boxWidth: '425px',
				buttons : {
					confirm: {
						text    : wpforms_builder.education_install_confirm,
						btnClass: 'btn-confirm',
						keys    : [ 'enter' ],
						isHidden: ! wpforms_builder.can_install_addons,
						action  : function() {

							var currentModal = this,
								$confirm     = currentModal.$body.find( '.btn-confirm' );

							$confirm.prop( 'disabled', true ).html( '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> ' + wpforms_builder.education_installing );

							app.installAddon( url, nonce, currentModal );

							return false;
						}
					},
					cancel : {
						text: wpforms_builder.cancel
					}
				}
			} );
		},

		/**
		 * Install addon via AJAX.
		 *
		 * @since 1.5.1
		 *
		 * @param {string} url           Install URL.
		 * @param {string} nonce         Action nonce.
		 * @param {object} previousModal Previous modal instance.
		 */
		installAddon: function( url, nonce, previousModal ) {

			$.post(
				wpforms_builder.ajax_url,
				{
					action: 'wpforms_install_addon',
					nonce : nonce,
					plugin: url
				},
				function( res ) {

					previousModal.close();

					if ( res.success ) {
						app.saveModal( res.data.msg );
					} else {
						var message = res.data;

						if ( 'object' === typeof res.data ) {
							message = wpforms_builder.addon_error;
						}

						$.alert( {
							title  : false,
							content: message,
							icon   : 'fa fa-exclamation-circle',
							type   : 'orange',
							buttons: {
								confirm: {
									text    : wpforms_builder.close,
									btnClass: 'btn-confirm',
									keys    : [ 'enter' ],
								}
							}
						} );
					}
				}
			);
		},

		/**
		 * Upgrade modal.
		 *
		 * @since 1.5.1
		 *
		 * @param {string} feature   Feature name.
		 * @param {string} fieldName Field name.
		 * @param {string} type 	 License type.
		 */
		upgradeModal: function( feature, fieldName, type ) {

			// Provide a default value.
			if ( typeof type === 'undefined' || type.length === 0 ) {
				type = 'pro';
			}

			// Make sure we received only supported type.
			if ( $.inArray( type, [ 'pro', 'elite' ] ) < 0 ) {
				return;
			}

			var modalTitle = feature + ' ' + wpforms_builder.education_upgrade[type].title;

			if ( typeof fieldName !== 'undefined' && fieldName.length > 0 ) {
				modalTitle = fieldName + ' ' + wpforms_builder.education_upgrade[type].title;
			}

			$.alert( {
				title       : modalTitle,
				icon        : 'fa fa-lock',
				content     : wpforms_builder.education_upgrade[type].message.replace( /%name%/g, feature ),
				boxWidth    : '550px',
				onOpenBefore: function() {
					this.$body.find( '.jconfirm-content' ).addClass( 'lite-upgrade' );
				},
				buttons     : {
					confirm: {
						text    : wpforms_builder.education_upgrade[type].confirm,
						btnClass: 'btn-confirm',
						keys    : [ 'enter' ],
						action  : function() {
							window.open(
								wpforms_builder.education_upgrade[type].url + '&utm_content=' + encodeURIComponent( feature.trim() ),
								'_blank'
							);
						}
					}
				}
			} );
		},

		/**
		 * License modal.
		 *
		 * @since 1.5.1
		 */
		licenseModal: function() {

			$.alert( {
				title  : false,
				content: wpforms_builder.education_license_prompt,
				icon   : 'fa fa-exclamation-circle',
				type   : 'orange',
				buttons: {
					confirm: {
						text    : wpforms_builder.close,
						btnClass: 'btn-confirm',
						keys    : [ 'enter' ],
					}
				}
			} );
		}
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsBuilderEducation.init();
