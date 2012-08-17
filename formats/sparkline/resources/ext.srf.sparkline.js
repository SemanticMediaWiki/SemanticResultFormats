/**
 * JavaSript for SRF sparkline module
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * jshint checked
 *
 * @release: 0.2
 */
( function( $ ) {
	// Use ECMAScript 5's strict mode
	"use strict";

	/*global mw:true*/

	// Only display errors
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }

	$.fn.spline = function() {

		var chart = this.find( ".container" ),
			chartID = chart.attr( "id" ),
			json = mw.config.get( chartID );

		// Parse json string and convert it back
		var data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

		// Release graph and bottom text
		this.find( '.srf-processing' ).hide();

		// Release chart/graph
		chart.show();
		chart.sparkline( data.data , {
			type: data.charttype
		} );
	};

	$( document ).ready( function() {
		$( ".srf-sparkline" ).each( function() {
			$( this ).spline();
		} );
	} ); // end $(document).ready
} )( window.jQuery );