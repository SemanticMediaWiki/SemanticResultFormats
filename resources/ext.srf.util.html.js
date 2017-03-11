/**
 * SRF JavaScript for srf.util namespace
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
/*global semanticFormats:true mediaWiki:true*/
( function( $, mw, srf ) {
 'use strict';


	////////////////////////// PRIVATE OBJECTS //////////////////////////

	var html = mw.html;

	////////////////////////// PUBLIC METHODS /////////////////////////

	$.extend( srf.util.prototype, {

		html:{

			/**
			 * Returns a dropdown element
			 *
			 * e.g.
			 * options {
			 *  list: ['...', '...'],
			 *  id: 'printouts',
			 *  selectClass: 'printouts',
			 *  browser: 'firefox',
			 *  disabled: 'disabled'
			 * }
			 *
			 * @since 1.9
			 */
			dropdown: function( options ){
				// @note The dropdown size behaves differently in some browsers
				// therefore a css class is assigned for adjustments
				var dropdown = '';
				if ( typeof options.list === 'object' ) {
					$.each( options.list, function( index, text ) {
						if ( typeof text === 'object' ) {
							text = text[0];
						}
						dropdown = dropdown + html.element( 'option', { 'value': index }, text );
					} );
				}

				return html.element( 'div',{ 'class': 'select-wrap-' + options.browser || 'all' },
					new html.Raw ( html.element( 'select', {'id': options.id, 'class': options.selectClass, 'disabled': options.disabled || false },
						new html.Raw( html.element( 'option', { 'value': '' }, '' ) + dropdown ) )
					)
				);
			}
		}
	} );

} )( jQuery, mediaWiki, semanticFormats );
