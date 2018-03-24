/**
 * SRF JavaScript for srf.calendarbutton widget
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
/* global mw:true, smw:true, mediaWiki:true, semanticMediawiki:true, semanticFormats:true */
( function( $, mw, srf ) {
	'use strict';

	////////////////////////// PRIVATE OBJECTS ////////////////////////

	var html = mw.html;

	////////////////////////// FACTORY METHOD ////////////////////////

	$.widget( 'srf.calendarbutton', {
		options:{
			right: true,
			left: true
		},

		_init: function() {
			var self = this,
				el = self.element;

			// Returns button element
			function _element ( buttonClass, contentClass, title, theme ) {
				return html.element( 'span', { 'class': buttonClass, 'title': title }, new html.Raw(
					html.element( 'button', { 'class': 'fc-button ' + theme + '-state-default ' + ( self.options.left ? theme + '-corner-left ' : '' ) + ( self.options.right ? theme + '-corner-right' : '' ) }, new html.Raw(
						html.element( 'span', { 'class': 'fc-button-inner' },  new html.Raw(
							html.element( 'span', { 'class': 'fc-button-content' }, new html.Raw(
							html.element( 'span', { 'class': 'fc-icon-wrap' }, new html.Raw(
								html.element( 'div', { 'class': contentClass }, new html.Raw( '&nbsp;' ) ) ) ) ) ) +
								html.element( 'span', { 'class': 'fc-button-effect' }, new html.Raw( html.element( 'span', {}, '' ) ) )
							) )
						) )
					)
				);
			}

			// Returns space element
			function _space () {
				return html.element( 'span', { 'class' : 'fc-header-space' }, '' );
			}

			// The tooltip button needs a special treatment as it is placed in between elements
			if ( self.options.tooltip ){
				this.button = $( _element ( self.widgetBaseClass + '-' + self.options['class'], self.options.icon, self.options.title, self.options.theme ) )
				.insertAfter( el );
			} else {
				this.button = $( _space() + _element ( self.widgetBaseClass + '-' + self.options['class'], self.options.icon, self.options.title, self.options.theme ) )
				.appendTo( el );
			}

			return this._hover();
		},

		/**
		 * Imitate fc hover button functionality
		 *
		 * @since 1.9
		 */
		_hover: function( ) {
			var self = this;
			this.button = this.button || $();

			var instance = this.button.find( '.fc-button' );
			return instance
				.mousedown( function() {
					instance
						.not( '.' + self.options.theme + '-state-active' )
						.not( '.' + self.options.theme + '-state-disabled' )
						.addClass( self.options.theme + '-state-down' );
				} )
				.mouseup( function() {
					instance.removeClass( self.options.theme + '-state-down');
				} )
				.hover(
					function() {
						instance.addClass( self.options.theme + '-state-hover' );
					},
					function() {
						instance
							.removeClass( self.options.theme + '-state-hover')
							.removeClass( self.options.theme + '-state-down');
					}
				);
		},

		/**
		 * Remove objects
		 *
		 * @since 1.9
		 * @var options
		 */
		destroy: function( options ) {
			var self = this;

			if ( options['class'] ){
				$( '.' + self.widgetBaseClass + '-' + options['class'] , this.element ).remove();
			} else{
				$.Widget.prototype.destroy.apply( this );
			}
		}
	} );
} )( jQuery, mediaWiki, semanticFormats );