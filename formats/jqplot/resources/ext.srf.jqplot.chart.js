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
			chartID   = container.attr( "id" ),
			height    = container.height(),
			width     = container.width(),
			json      = mw.config.get( chartID );

		// Parse json string and convert it back
		var data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

		// Assign height/width important when dealing with % values
		chart.css( { 'height': height , 'width': width } );
		container.css( {
			'height': chart.height() - ( data.parameters.gridview === 'tabs' ? 20 : 0 ),
			'width': chart.width() - ( data.parameters.gridview === 'tabs' ? 20 : 0 )
		} );

		// Hide processing image
		chart.find( '.srf-processing' ).hide();

		// Release chart/graph
		container.show();

		// Add chart text
		var chartText = data.parameters.charttext,
			chartTextHeight = 0;
		if ( chartText.length > 0 ) {
			container.prepend( '<div id="' + chartID + '-text' + '" class="srf-jqplot-chart-text">' + chartText + '</div>' );
			container.find( '.srf-jqplot-chart-text' )
				.addClass( ( data.parameters.gridview === 'tabs' ? 'tabs ' + data.renderer : data.renderer ) );
			chartTextHeight = container.find( '.srf-jqplot-chart-text' ).height() + 10;
		}

		// Was reported to solve some memory leak problems on IE in connection with
		// canvas objects
		container.find( 'canvas' ).remove();

		// Call gridview plugin
		if ( data.parameters.gridview === 'tabs' ){
			// Set options
			var options ={
					'context'   : chart,
					'id'        : chartID,
					'container' : container,
					'info'      : data.parameters.infotext,
					'data'      : data
				};

			// Grid view instance
			new srf.util.grid( options );
		}

		// Tabs height can vary (due to CSS) therefore after tabs instance was
		// created get the height
		var _tabs = chart.find( '.ui-tabs-nav' );

		// Adjust height and width according to current customizing
		width = container.width();
		height = container.height() - chartTextHeight - _tabs.height();

		// Div thta holds the plot
		var plotID = chartID + '-plot';
		container.prepend( '<div id="' + plotID + '" class="srf-jqplot-plot"></div>' );
		var plot = chart.find( '.srf-jqplot-plot' );
		plot
			.css( { 'height': height, 'width': width } )
			.addClass( ( data.parameters.gridview === 'tabs' ? 'tabs ' + data.renderer : data.renderer ) );

		// Chart plotting
		if ( data.renderer === 'pie' || data.renderer === 'donut' ){
			plot.srfjqPlotPieChart( { 'id' : plotID, 'height' : height, 'width' : width, 'chart' : container, 'data' : data } );
		} else if ( data.renderer === 'bubble' ){
			plot.srfjqPlotBubbleChartData( { 'id' : plotID, 'height' : height, 'width' : width, 'chart' : container, 'data' : data } );
		} else {
			plot.srfjqPlotBarChartData( { 'id' : plotID, 'height' : height , 'width' : width , 'chart' : container, 'data' : data } );
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