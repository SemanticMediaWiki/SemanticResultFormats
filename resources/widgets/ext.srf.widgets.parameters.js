/**
 * SRF JavaScript for srf.parameters widget
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
	 * Html element and utility objects
	 *
	 * @type object
	 */
	var html = mw.html;

	/**
	 * $.widget factory method
	 *
	 * @since 1.9
	 */
	$.widget( 'srf.parameters', {

		_init: function() {
			var self = this,
				el = self.element;
			return el;
		},

		/**
		 * Limit parameter
		 *
		 * @since 1.9
		 */
		limit: function( options ) {
			var self = this,
				el = self.element;

			function element(){
				return html.element( 'div', { 'class': 'limit-parameter' }, new html.Raw(
					html.element( 'div', { 'class' : 'parameter-section' }, mw.msg( 'srf-ui-widgets-label-parameter-limit' ) ) +
					html.element( 'span', { 'class': 'value' }, '' ) +
					html.element( 'span', { 'class': 'count' }, '' ) + '<br/>' +
					html.element( 'div', { 'class': 'slider' }, '' )
				) );
			}

			this.limitParameter = $( element() ).appendTo( el );

			// Slider instance
			this.limitParameter.find( '.slider' ).slider( {
				range: 'min',
				value: options.limit,
				min: 1,
				max: options.max,
				step: options.step,
				slide: function( event, ui ){
					self._limitParameterUpdate( { limit: self._limitConstrain( ui.value, options.max ) } );
				},
				change: function( event, ui ){
					if ( $.isFunction( options.change ) ){
						options.change( event, { value : self._limitConstrain( ui.value, options.max )  } );
					}
				}
			} );

			// Show initial limit/count
			this._limitParameterUpdate( { limit: options.limit, count: options.count } );
		},

		/**
		 * Limit/count value update
		 *
		 * @since 1.9
		 */
		_limitParameterUpdate: function( options ){
			var self = this,
				el = self.element;

			$( '.value', self.element ).text(  options.limit  );

			if ( options.count ){
				$( '.count', self.element ).text( '[ ' + options.count + ' ]' );
			} else {
				$( '.count', self.element ).text( '' );
			}
		},

		_limitConstrain: function( value, max ){
			return value > 1 ? value >= max ? max : value - 1 : value;
		},

		/**
		 * Change options
		 *
		 * @param name
		 * @param value
		 */
		_setOption: function ( name, value ) {
			switch( name ){
			case 'limit':
				this._limitParameterUpdate( value );
				break;
			}
			$.Widget.prototype._setOption.apply( this, arguments );
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