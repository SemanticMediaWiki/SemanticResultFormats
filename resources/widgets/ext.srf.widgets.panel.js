/**
 * SRF JavaScript srf.panel widget
 *
 * @since 1.9
 * @release 0.1
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	/**
	 * Html element generation
	 *
	 * @type object
	 */
	var html = mw.html;

	/**
	 * $.widget factory method
	 *
	 * @since 1.9
	 */
	$.widget( 'srf.panel', {

		/**
		 * Create method which runs once (during initialization)
		 *
		 * @return object
		 */
		_create: function() {
			var self = this,
				el = self.element;

			this.panel = $(
				html.element( 'div', { 'class': self.widgetBaseClass }, '' )
			).insertAfter( el );
			return this.options.show ? this.panel.show() : this.panel.hide();
		},

		/**
		 * Returns the pane context
		 *
		 * @since 1.9
		 * @return object
		 */
		getContext: function( ) {
			return this.panel;
		},

		/**
		 * Adds a portlet
		 *
		 * @since 1.9
		 * @var options
		 */
		portlet: function( options ) {
			var self = this,
				el = self.element;

			// Specify the pane instance
			this.panel = this.panel || $();

			// Append
			this.panelPortlet = $(
				html.element( 'div', {
					'id': self.widgetBaseClass + '-' + options['class'],
					'class': options['class'] },
					new html.Raw( ( options['fieldset'] ? html.element( 'fieldset', {},
						new html.Raw( html.element( 'legend', { }, options['title'] ) ) ) : ''
						)
					)
				)
			).appendTo( this.panel );

			self.panelPortlet = this.panelPortlet;
			return options.hide ? self.panelPortlet.hide() : self.panelPortlet.show() ;
		},

		/**
		 * Depending on its state toggle show/hide
		 *
		 * @since 1.9
		 */
		toggle: function() {
			return this.panel.css( 'display' ) === 'none' ? this.panel.fadeIn( 'slow' ): this.panel.hide();
		},

		/**
		 * Remove objects
		 *
		 * @since 1.9
		 * @var options
		 */
		destroy: function( options ) {
			if ( options['class'] ){
				$( '.' + options['class'] , this.panel ).remove();
			} else{
				$.Widget.prototype.destroy.apply( this );
			}
		}
	} );
} )( jQuery, mediaWiki, semanticFormats );