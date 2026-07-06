/**
 * SRF JavaScript for srf.parameters widget
 *
 * @param $
 * @param mw
 * @param srf
 * @since 1.9
 * @release 0.1
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
 * @author mwjames
 */
( function ( $, mw, srf ) {
	'use strict';

	/**
	 * Html element and utility objects
	 *
	 * @type object
	 */
	const html = mw.html;

	/**
	 * $.widget factory method
	 *
	 * @since 1.9
	 */
	$.widget( 'srf.parameters', {

		_init: function () {
			const self = this,
				el = self.element;
			return el;
		},

		/**
		 * Limit parameter
		 *
		 * @param options
		 * @since 1.9
		 */
		limit: function ( options ) {
			const self = this,
				el = self.element;

			function element() {
				return html.element( 'div', { class: 'limit-parameter' }, new html.Raw(
					html.element( 'div', { class: 'parameter-section' }, mw.msg( 'srf-ui-widgets-label-parameter-limit' ) ) +
					html.element( 'span', { class: 'value' }, '' ) +
					html.element( 'span', { class: 'count' }, '' ) + '<br/>' +
					html.element( 'div', { class: 'slider' }, '' )
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
				slide: function ( event, ui ) {
					self._limitParameterUpdate( { limit: self._limitConstrain( ui.value, options.max ) } );
				},
				change: function ( event, ui ) {
					if ( $.isFunction( options.change ) ) {
						options.change( event, { value: self._limitConstrain( ui.value, options.max ) } );
					}
				}
			} );

			// Show initial limit/count
			this._limitParameterUpdate( { limit: options.limit, count: options.count } );
		},

		/**
		 * Limit/count value update
		 *
		 * @param options
		 * @since 1.9
		 */
		_limitParameterUpdate: function ( options ) {
			const self = this,
				el = self.element;

			$( '.value', self.element ).text( options.limit );

			if ( options.count ) {
				$( '.count', self.element ).text( '[ ' + options.count + ' ]' );
			} else {
				$( '.count', self.element ).text( '' );
			}
		},

		_limitConstrain: function ( value, max ) {
			return value > 1 ? value >= max ? max : value - 1 : value;
		},

		/**
		 * Change options
		 *
		 * @param name
		 * @param value
		 */
		_setOption: function ( name, value ) {
			switch ( name ) {
				case 'limit':
					this._limitParameterUpdate( value );
					break;
			}
			$.Widget.prototype._setOption.apply( this, arguments );
		},

		/**
		 * Remove objects
		 *
		 * @param options
		 * @since 1.9
		 * @member options
		 */
		destroy: function ( options ) {
			if ( options.class ) {
				$( '.' + options.class, this.pane ).remove();
			} else {
				$.Widget.prototype.destroy.apply( this );
			}
		}
	} );
}( jQuery, mediaWiki, semanticFormats ) );
