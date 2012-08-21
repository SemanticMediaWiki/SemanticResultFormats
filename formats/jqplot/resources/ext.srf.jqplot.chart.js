/**
 * JavaSript for SRF jqPlot chart/series module
 *
 * The script is designed to handle single and series data sets
 *
 * Release 0.6 has been checked agains jsHint which passed all conditions
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @release: 0.6
 */
( function( $ ) {
	"use strict";

	// Only display errors
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }

	/*global mw:true*/

	// Global jqplot container handling
	$.fn.srfjqPlotChartContainer = function() {

		var chart   = this.find( ".container" ),
			height    = chart.height(),
			width     = chart.width( ),
			chartID   = chart.attr( "id" ),
			json      = mw.config.get( chartID );

		// Parse json string and convert it back
		var data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

		// Add chart text
		var charttext = data.parameters.charttext;
		if ( charttext.length > 0 ) {
			charttext  = '<span id="' + chartID +'" class="srf-jqplot-chart-text">' + charttext  + '</span>';
			chart.after( charttext ) ;
		}

		// Calculate height
		width = width === 0 ? height : width;
		this.css( { 'height': height, 'width': width } );
		height = height - this.find( '.srf-jqplot-chart-text' ).height() - 10 ;
		chart.css( { 'height': height, 'width': width } );

		// .remove() was reported to solve some memory leak problems on IE
		// in connection with canvas objects
		this.find('canvas').remove();

		// Hide processing image
		this.find( '.srf-processing' ).hide();

		// Release chart/graph
		chart.show();

		if ( data.renderer === 'pie' || data.renderer === 'donut' ){
			this.srfjqPlotPieChart( { 'id' : chartID, 'height' : height, 'width' : width, 'chart' : chart, 'data' : data } );
		} else if ( data.renderer === 'bubble' ){
			this.srfjqPlotBubbleChartData( { 'id' : chartID, 'height' : height, 'width' : width, 'chart' : chart, 'data' : data } );
		} else{
			this.srfjqPlotBarChartData( { 'id' : chartID, 'height' : height, 'width' : width, 'chart' : chart, 'data' : data } );
		}
	};

	// Theming
	$.fn.srfjqPlotTheme = function( options ) {
		/*global simple:true, vector:true*/

		// Reposition chart text to adjust for the tick label margin
		var textmargin = this.find( '.jqplot-axis.jqplot-yaxis').width();
		this.find( '.srf-jqplot-chart-text' ).css( { 'margin-left': textmargin , 'display': 'block'} );

		// Theming support for commonly styled attributes of plot elements
		// using jqPlot's "themeEngine"
		options.plot.themeEngine.newTheme( 'simple', simple );
		options.plot.themeEngine.newTheme( 'vector', vector );

		// Only overwrite the default for cases with a theme
		if ( options.theme !== null ){
			options.plot.activateTheme( options.theme );
		}
	};

	$( document ).ready( function() {
		// Use "[class^=srf-jqplot]" instead of ".srf-jqplot-bar,.srf-jqplot-line,
		// .srf-jqplot-pie,.srf-jqplot-donut,.srf-jqplot-bubble"
		$( "[class^=srf-jqplot]" ).each( function() {
			$( this ).srfjqPlotChartContainer();
		} );
	} ); // end $(document).ready
} )( window.jQuery );