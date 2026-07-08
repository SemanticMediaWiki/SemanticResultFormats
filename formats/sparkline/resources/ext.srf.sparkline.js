/**
 * JavaSript for SRF sparkline format
 *
 * @param srf
 * @param $
 * @see http://www.semantic-mediawiki.org/wiki/Help:Sparkline format
 *
 * @since 1.8
 * @release 0.2
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
 * @author mwjames
 */
( function ( srf, $ ) {
	'use strict';

	// //////////////////////// PUBLIC METHODS ////////////////////////

	srf.formats = srf.formats || {};

	/**
	 * Implementation and representation of the sparkline instance
	 *
	 * @since 1.8
	 * @type {Object}
	 */
	const util = new srf.util();

	/**
	 * Constructor
	 *
	 * @param settings
	 * @member Object
	 */
	srf.formats.sparkline = function ( settings ) {
		$.extend( this, settings );
		this.init();
	};

	srf.formats.sparkline.prototype = {

		init: function () {
			return this.context.each( function () {
				const chart = $( this ).find( '.sparkline-container' ),
					chartID = chart.attr( 'id' ),
					json = mw.config.get( chartID );

				// Parse json string and convert it back
				const data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

				// Release graph and bottom text
				util.spinner.hide( { context: $( this ) } );

				// Release chart/graph
				chart.show();
				chart.sparkline( data.value, {
					type: data.charttype,
					tooltipFormat: '{{value}}{{y}} ({{offset:offset}})',
					tooltipValueLookups: {
						offset: data.label
					}
				} );
			} );
		}
	};

	$( document ).ready( () => {
		$( '.srf-sparkline' ).each( function () {
			// eslint-disable-next-line no-new
			new srf.formats.sparkline( { context: $( this ) } );
		} );
	} );
}( semanticFormats, jQuery ) );
