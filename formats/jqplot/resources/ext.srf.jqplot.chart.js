/**
 * JavaSript for SRF jqPlot chart/series module
 *
 * The script is designed to handle single and series data sets
 *
 * jshint checked and passed
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

		var chart = this,
			container = chart.find( ".container" ),
			height    = container.height(),
			width     = container.width(),
			chartID   = container.attr( "id" ),
			json      = mw.config.get( chartID );

		// Parse json string and convert it back
		var data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

		// Add chart text
		var chartText = data.parameters.charttext,
			chartTextHeight = 0;
		if ( chartText.length > 0 ) {
			chartText = '<span id="' + chartID +'" class="srf-jqplot-chart-text">' + chartText + '</span>';
			container.after( chartText ) ;
			chartTextHeight = chart.find( '.srf-jqplot-chart-text' ).height() + ( data.parameters.tableview === 'tabs' ? 25 : 10 );
		}

		// Adjust height and width according to current customizing
		width = width === 0 ? height : width;
		chart.css( { 'height': height , 'width': width } );
		height = height - chartTextHeight;

		// General height adjustments (before) are necessary to ensure alignment when using jquery ui
		container.css( {
			'height': height - ( data.parameters.tableview === 'tabs' ? 40 : 0 ),
			'width': width - ( data.parameters.tableview === 'tabs' ? 15 : 0 ),
			'margin-left': data.parameters.tableview === 'tabs' ? 10 : 0
		} );

		// remove() was reported to solve some memory leak problems on IE
		// in connection with canvas objects
		chart.find('canvas').remove();

		// Hide processing image
		chart.find( '.srf-processing' ).hide();

		// Release chart/graph
		container.show();

		// Chart plotting
		if ( data.renderer === 'pie' || data.renderer === 'donut' ){
			chart.srfjqPlotPieChart( { 'id' : chartID, 'height' : height, 'width' : width, 'chart' : container, 'data' : data } );
		} else if ( data.renderer === 'bubble' ){
			chart.srfjqPlotBubbleChartData( { 'id' : chartID, 'height' : height, 'width' : width, 'chart' : container, 'data' : data } );
		} else {
			chart.srfjqPlotBarChartData( { 'id' : chartID, 'height' : height , 'width' : width , 'chart' : container, 'data' : data } );
		}

		// Call tableview plugin
		if ( data.parameters.tableview === 'tabs' ){
			// Further adjustments of height and xaxis after the chart has been plotted
			chart.find( '.jqplot-table-legend' ).css( { 'margin-right' : 35, 'margin-bottom' : 30 } );
			chart.find( '.jqplot-axis.jqplot-xaxis' ).css( { 'height' : chart.find( '.jqplot-axis.jqplot-xaxis' ).height() + 10 } );
			chart.srftableview( {
				'id' : chartID,
				'chart' : container,
				'info'  : data.parameters.infotext,
				'data'  : data
			} );
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
		// Check if eachAsync exists, and if so use it to increase browsers responsiveness
		if( $.isFunction( $.fn.eachAsync ) ){
				$( "[class^=srf-jqplot]" ).eachAsync( {
				delay: 100,
				bulk: 0,
				loop: function(){
					$( this ).srfjqPlotChartContainer();
				}
			} );
		}else{
			$( "[class^=srf-jqplot]" ).each( function() {
				$( this ).srfjqPlotChartContainer();
			} );
		}
	} );
} )( window.jQuery );