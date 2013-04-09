/**
 * SRF JavaScript srf.eventcalendarpane widget
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
	$.widget( 'srf.calendarpane', {

		/**
		 * Create methods runs once during initialization
		 *
		 * @return object
		 */
		_create: function() {
			var self = this,
				el = self.element;

			this.pane = $(
				html.element( 'div', { 'class': self.widgetBaseClass }, '' )
			).insertAfter( el );
			return this.pane.css( {
				'display' : ( this.options.show ? 'block' : 'none' )
			} )
		},

		/**
		 * Returns the pane context
		 *
		 * @since 1.9
		 * @return object
		 */
		context: function( ) {
			return this.pane;
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
			this.pane = this.pane || $();

			// Append
			this.panePortlet = $(
				html.element( 'div', {
					'id': self.widgetBaseClass + '-' + options['class'],
					'class': options['class'] },
					new html.Raw( ( options['fieldset'] ? html.element( 'fieldset', {},
						new html.Raw( html.element( 'legend', { }, options['title'] ) ) ) : ''
						)
					)
				)
			).appendTo( this.pane );

			self.panePortlet = this.panePortlet;
			return options.hide ? self.panePortlet.hide() : self.panePortlet.show() ;
		},

		/**
		 * Depending on its state toggle show/hide
		 *
		 * @since 1.9
		 */
		toggle: function() {
			return this.pane.css( 'display' ) === 'none' ? this.pane.show(): this.pane.hide();
		},

		/**
		 * Remove objects
		 *
		 * @since 1.9
		 * @var options
		 */
		destroy: function( options ) {
			if ( options['class'] ){
				$( '.' + options['class'] , this.pane ).remove();
			} else{
				$.Widget.prototype.destroy.apply( this );
			}
		}
	} );
} )( jQuery, mediaWiki, semanticFormats );