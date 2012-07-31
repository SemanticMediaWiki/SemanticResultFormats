/**
 * JavaSript for SRF sparkline module
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @release: 0.1
 */
(function( $ ) {

	// Only display errors
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }

	$.fn.spline = function( options ) {

		var chart = this.find( ".container" ),
			chartID = chart.attr( "id" ),
			json = mw.config.get( chartID );

		// Parse json string and convert it back into objects
		typeof json == 'string' ? data = jQuery.parseJSON( json ) : data = json;

		// Release graph and bottom text
		this.find( '.srf-processing' ).hide();

		// Release chart/graph
		chart.show();
		chart.sparkline( data.data , {
			type: data.layout
		} );

	} // end of function

	$( document ).ready( function() {
		$( ".srf-sparkline" ).each( function() {
			$( this ).spline();
		} );
	} ); // end $(document).ready
} )( window.jQuery );