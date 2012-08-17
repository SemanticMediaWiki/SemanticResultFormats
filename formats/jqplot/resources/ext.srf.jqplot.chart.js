/**
 * JavaSript for SRF jqPlot chart/series module
 *
 * The script is designed to handle single and series data sets
 *
 * Release 0.4 has been checked agains jsHint which passed all conditions
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @release: 0.5
 */
( function( $ ) {
	// Use ECMAScript 5's strict mode
	"use strict";

	// Only display errors
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }

	/*global mw:true, colorscheme:true*/

	// Global jqplot container handling
	$.fn.jqplotPlotContainer = function() {

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
			this.jqplotPiePlot( { 'id' : chartID, 'height' : height, 'width' : width, 'chart' : chart, 'data' : data } );
		} else if ( data.renderer === 'bubble' ){
			this.jqplotBubbleData( { 'id' : chartID, 'height' : height, 'width' : width, 'chart' : chart, 'data' : data } );
		} else{
			this.jqplotBarData( { 'id' : chartID, 'height' : height, 'width' : width, 'chart' : chart, 'data' : data } );
		}
	};

	// Bubble chart data handling is separated from plotting because relevant data array
	// can be checked and if necessary bailout and show an error message instead
	// without causing any type errors
	$.fn.jqplotBubbleData = function( options ) {
		var data = options.data;

		// Data array handling
		var errMsg = '',
			dataRenderer = function() {
				var bubbledata = [];

				// Check to avoid TypeErrors
				if ( typeof data.data[0] === 'undefined' ) {
					errMsg = mw.msg( 'srf-error-jqplot-bubble-data-length' );
				} else if ( typeof data.data[1] === 'undefined' ){
					errMsg = mw.msg( 'srf-error-jqplot-bubble-data-length' );
				} else if ( typeof data.data[2] === 'undefined' ){
					errMsg = mw.msg( 'srf-error-jqplot-bubble-data-length' );
				}

				// Data manipulation
				// Convert [x: [label, value ]], [y: [label, value ]], [radius: [label, value ]] into
				// [x, y, radius, <label or object>]
				if ( errMsg === '' ){
					for ( var k = 0; k < data.data[0].length; ++k ) {
						bubbledata.push( [data.data[0][k][1], data.data[1][k][1], data.data[2][k][1], data.data[0][k][0]  ]);
					}
				}
				return [bubbledata];
			};

		// Fetch data array, call it before any other routine otherwise no error msg
		var jqplotbubbledata = dataRenderer();

		// Error message handling
		if ( errMsg.length > 0 ){
			this.html( errMsg ).css( { 'class' : 'error', 'height' : 20 , 'margin' : '5px 5px 5px 5px', 'color': 'red' } );
		}else{
			this.jqplotBubblePlot( { 'id' : options.id, 'barData' : jqplotbubbledata, 'data' : data } );
		}
	};

	// Bubble plotting
	$.fn.jqplotBubblePlot = function( options ) {
		var data = options.data;

		$.jqplot.config.enablePlugins = true;

		var jqplotbubble = $.jqplot( options.id , options.barData, {
			// dataRenderer: dataRenderer,
			title: data.parameters.charttitle,
			seriesColors: data.parameters.seriescolors ? data.parameters.seriescolors : ( data.parameters.colorscheme === null ? null : colorscheme[data.parameters.colorscheme][9] ),
			grid: data.parameters.grid,
			seriesDefaults: {
				renderer: data.renderer === 'bubble' ? $.jqplot.BubbleRenderer : $.jqplot.PieRenderer,
				shadow: data.parameters.theme !== 'simple',
				rendererOptions: {
					autoscalePointsFactor: -0.15,
					autoscaleMultiplier: 0.85,
					highlightMouseOver: true,
					bubbleGradients: true,
					bubbleAlpha: 0.7
				}
			},
			legend: {
				show: data.parameters.chartlegend !== 'none',
				location: data.parameters.chartlegend,
				// labels: data['legendLabels'],
				placement: 'inside',
				xoffset: 10,
				yoffset:10
			}
		} );

		// Call theming
		this.jqplotTheme( { 'plot' : jqplotbubble, 'theme' : data.parameters.theme } );
	};

	// Pie/donut handling
	$.fn.jqplotPiePlot = function( options ) {
		var data = options.data;

		// Handle data array
		var jqplotpiedata = [];

		var dataRenderer = function() {
				jqplotpiedata = data.data;
			// jqplotpiedata.push( data.data );
			return jqplotpiedata;
		};

		// Default settings
		var seriesDefaults = {
			renderer: data.renderer === 'donut' ? $.jqplot.DonutRenderer : $.jqplot.PieRenderer,
			shadow: data.parameters.theme !== 'simple',
			rendererOptions: {
				fill: data.parameters.filling,
				lineWidth: 2,
				showDataLabels: ( data.parameters.datalabels === 'percent' || data.parameters.datalabels === 'value' || data.parameters.datalabels === 'label' ? true : false ),
				dataLabels: data.parameters.datalabels,
				sliceMargin: 2,
				dataLabelFormatString: data.parameters.datalabels === 'label' ? null : ( !data.parameters.valueformat ? '%d' : data.parameters.valueformat )
			}
		};

		// Activate plug-ins
		$.jqplot.config.enablePlugins = true;

		// Render plot
		var jqplotpie = $.jqplot( options.id, [] , {
			dataRenderer: dataRenderer,
			title: data.parameters.charttitle,
			seriesColors: data.parameters.seriescolors ?  data.parameters.seriescolors :  ( data.parameters.colorscheme === null ? null : colorscheme[data.parameters.colorscheme][9] ),
			grid: data.parameters.grid,
			seriesDefaults: seriesDefaults,
			legend: {
				show: data.parameters.chartlegend !== 'none',
				location: data.parameters.chartlegend,
				placement: 'inside',
				xoffset: 10,
				yoffset:10
			}
		} ); // end of jqplot object

		// Call theming
		this.jqplotTheme( { 'plot' : jqplotpie, 'theme' : data.parameters.theme } );
	};

	// Bar/line data handling is separated from plotting because relevant data array
	// can be checked and if necessary bailout and show an error message instead
	// without causing any type errors
	$.fn.jqplotBarData = function( options ) {
		var chartID = options.id,
		height = options.height,
		data   = options.data,
		width  = options.width,
		chart  = options.chart;

		// Global data array
		var labels = [];

		// Function handles all data array miracles
		var errMsg = '',
			dataRenderer = function() {
			var jqplotdata = [],
				ttLength = 0,
				ptLength = 0;

				// Count the amount of series
				for ( var k = 0; k < data.data.length; ++k ) {
					var ttData = [];
					ttLength = data.data[k].length;

					// Check if data series has the same length otherwise
					// stackseries throws an error
					if ( ttLength !== ptLength && k > 0 && data.parameters.stackseries === true ){
						errMsg = mw.msg( 'srf-error-jqplot-stackseries-data-length' );
					}

					// Individual data within a series
					for ( var j = 0; j < ttLength; ++j ) {
						if ( data.parameters.direction === 'horizontal' ){
							if ( data.fcolumntypeid === '_num' ){
								// Numeric x-value is handled differently
								ttData.push ( [data.data[k][j][1], data.data[k][j][0]] );
							}else{
								ttData.push ( [data.data[k][j][1], j+1] );
							}
						} else {
							if ( data.fcolumntypeid === '_num' ){
								// Numeric x-value is handled differently
								ttData.push ( [data.data[k][j][0], data.data[k][j][1]] );
							}else{
								ttData[j] = data.data[k][j][1];
							}
						}
						// Handle labels in extra array
						labels[j] = data.data[k][j][0];
					}
					jqplotdata.push( ttData );
					// Store previous length to compare both
					ptLength = ttLength;
				}
			return jqplotdata;
		};

		// Get data array
		var jqplotbardata = dataRenderer();

		// Error message handling
		if ( errMsg.length > 0 ){
			this.html( errMsg ).css( { 'class' : 'error', 'height' : 20 , 'margin' : '5px 5px 5px 5px', 'color': 'red' } );
		}else{
			this.jqplotBarPlot( { 'id' : chartID, 'data' : data, 'barData' : jqplotbardata, 'labels' : labels, 'height' : height, 'width' : width, 'chart' : chart } );
		}
	};

	// Bar/line/scatter plotting
	$.fn.jqplotBarPlot = function( options ) {
		var labels = options.labels,
		data = options.data;

		// Number axis
		var numberaxis = {
			ticks: ( data.parameters.stackseries === true ) || ( data.parameters.autoscale === true ) ?  [] : data.ticks, // use autoscale for staked series
			label: data.parameters.numbersaxislabel,
			labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
			autoscale: ( data.parameters.stackseries === true ) || ( data.parameters.autoscale === true ) ? true : false,
			tickOptions: {
			angle: data.parameters.direction === 'horizontal' ? 0 : -40,
			formatString: !data.parameters.valueformat ? '%d' : data.parameters.valueformat  // %d default
			}
		};

		// Helper function to get the Max value of the Array
		var max = function( array ){
			return Math.max.apply( Math, array );
		};

		// Helper function to get the Min value of the Array
		var min = function( array ){
			return Math.min.apply( Math, array );
		};

		var base = Math.pow( 1, Math.floor( Math.log( max( labels ), 10 ) ) );

		// Label axis
		var labelaxis = {
			// Pending on the first column type handling of values/labels is different
			renderer: data.fcolumntypeid === '_num' ? $.jqplot.LinearAxisRenderer : $.jqplot.CategoryAxisRenderer,
			ticks:  data.fcolumntypeid === '_num' ? [] : labels,
			label: data.parameters.labelaxislabel,
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			min: data.fcolumntypeid === '_num' ? Math.round( min( labels ) ) - base : '',
			max: data.fcolumntypeid === '_num' ? Math.round( max( labels ) ) + base : '',
			//tickInterval: 2,
			tickOptions: {
				angle: ( data.parameters.direction === 'horizontal' ? 0 : -40 ),
				formatString: !data.parameters.valueformat ? '%d' : data.parameters.valueformat  // %d default
			}
		};

		// Required for horizontal view
		var single = [ {
			renderer: data.renderer === 'bar' ? $.jqplot.BarRenderer : $.jqplot.LineRenderer,
			rendererOptions: {
				barDirection: data.parameters.direction,
				barPadding: 6,
				barMargin: data.parameters.direction === 'horizontal' ? 8 : 6,
				barWidth: data.renderer === 'vector' || data.renderer === 'simple' ? 20 : null,
				smooth: data.parameters.smoothlines,
				varyBarColor: true
			}
		} ];

		var highlighter = {
			show: data.parameters.highlighter  && data.renderer === 'line' ? true : false,
			showTooltip: data.parameters.highlighter,
			tooltipLocation: 'w',
			useAxesFormatters: data.parameters.highlighter,
			tooltipAxes: data.parameters.direction === 'horizontal' ? 'x' : 'y'
		};

		// Format individual data labels
		$.jqplot.LabelFormatter = function( format, val ) {
			var num = typeof val === 'object' ? val[1] : val;

			// Single mode
			if ( data.mode === 'single' ){
				if ( data.parameters.pointlabels === 'label' ){
					return labels[num];
				}else if ( data.parameters.pointlabels === 'percent' ) {
					return ( num / data.total  * 100 ).toFixed(2) + '% (' + num + ')';
				} else {
					return num;
				}
			}

			// Series mode
			if ( data.parameters.pointlabels === 'percent' ){
				return ( num / data.total  * 100 ).toFixed(2) + '% (' + num + ')';
			} else if ( data.parameters.pointlabels === 'label' ){
					return labels[num];
			} else if ( data.parameters.direction === 'horizontal' && data.renderer === 'line' ){
				// This case is weird because val returns with the index number and not with the value
				// which means all values displayed do mislead
				return '(n/a)';
			}else{
				return format !== '' ? val : val;
			}
		};

		var pointLabels = {
			show: data.parameters.pointlabels,
			location: data.parameters.direction === 'vertical' ? 'n' : 'e',
			edgeTolerance: data.renderer === 'bar' ? '-35': '-20',
			formatString: data.parameters.valueformat === '' ? '%d' : data.parameters.valueformat,
			formatter: $.jqplot.LabelFormatter,
			labels: data.parameters.pointlabels === 'label' ? data.labels : data.numbers
		};

		var seriesDefaults = {
			renderer: data.renderer === 'bar' ? $.jqplot.BarRenderer : $.jqplot.LineRenderer,
			fillToZero: true,
			shadow: data.parameters.theme !== 'simple',
			//trendline: {
			//	'show' => ( $this->params['trendline'] == true && $this->params['renderer'] == 'line' ? true : false ),
			//	'color' => '#666666',
			//},
			rendererOptions: {
				smooth: data.parameters.smoothlines
			},
			pointLabels: pointLabels
		};

		var legend = {
			renderer: $.jqplot.EnhancedLegendRenderer,
			show: data.parameters.chartlegend !== 'none',
			location: data.parameters.chartlegend,
			labels:	data.legendLabels,
			placement: 'inside',
			xoffset: 10,
			yoffset: 10
		};

		// Series information
		var series = data.series;

		// Color information
		var seriesColors = data.parameters.seriescolors ? data.parameters.seriescolors : data.parameters.colorscheme === null ? null : colorscheme[data.parameters.colorscheme][9];

		// Enable jqplot plugins
		$.jqplot.config.enablePlugins = true;

		// Now we are plotting
		var jqplotbar = $.jqplot( options.id , options.barData , {
			title: data.parameters.charttitle,
			//dataRenderer: dataRenderer,
			stackSeries: data.parameters.stackseries,
			seriesColors: seriesColors,
			axesDefaults: {
				padMax: 2.5,
				pad: 2.1,
				showTicks: data.parameters.ticklabels,
				tickOptions: { showMark: false }
			},
			grid: data.parameters.grid,
			highlighter: highlighter,
			seriesDefaults: seriesDefaults,
			cursor: {
				show: true,
				zoom: true,
				looseZoom: true,
				showTooltip: false
			},
			series: data.mode === 'single' ?  single : series,
			axes: {
				xaxis : ( data.parameters.direction === 'vertical' ? labelaxis : numberaxis ),
				yaxis : ( data.parameters.direction === 'vertical' ? numberaxis : labelaxis )
				// x2axis : ( data.parameters.direction == 'vertical' ?  label2axis : number2axis ) ,
				// y2axis : ( data.parameters.direction == 'vertical' ?  number2axis : label2axis )
			},
			legend: data.parameters.chartlegend === 'none' ? null : legend
		} ); // enf of $.jqplot

		// Call theming
		this.jqplotTheme( { 'plot' : jqplotbar, 'theme' : data.parameters.theme } );
	};

	// Theming
	$.fn.jqplotTheme = function( options ) {
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
			$( this ).jqplotPlotContainer();
		} );
	} ); // end $(document).ready
} )( window.jQuery );