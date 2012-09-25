/**
 * JavaScript for SRF dygraphs module
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @release: 0.2
 */
( function( mw, $ ) {
	"use strict";

	$.fn.extend( {
		srfdygraphs: function( options ) {
			var options = $.extend( '', options );
			return this.each( function() {

				var chart = $(this),
					container = chart.find( ".container" ),
					chartID   = container.attr( "id" ),
					json      = mw.config.get( chartID );

			// Parse json string
			var data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

			/**
			 * @var plotClass identifies class that holds the plot
			 * @var addedHeight collects heights of objects other that the chart in order
			 * to be able to adjust the height of the chart and sray within the limits
			 * specified by the query printer
			 */
			var plotClass = 'srf-dygraphs-plot',
				plotID = chartID + '-plot',
				width = data.parameters.width,
				addedHeight = 20,
				height = data.parameters.height;

			// Plot container
			container.prepend( '<div id="' + plotID + '" class="' + plotClass + '"></div>' );

			// Set chart height and width
			chart.css( { 'height': height , 'width': width } );

			// Adjustments for cases where jquery ui is involved
			width  = chart.width() - ( data.parameters.datatable === 'tabs' ? 30 : 0 );

			// Release chart container
			container.show();

			// Set-up chart source
			var chartSource = data.data.source.subject !== undefined ? data.data.source.subject : data.data.source.link !== undefined ? data.data.source.link : null;
			if ( chartSource.length > 0 ) {
				chartSource = '<span class="srf-chart-source">' + mw.msg( 'srf-ui-label-datasource' ) + ' ' +  chartSource + '</span>';
				container.find( '.' + plotClass ).after( chartSource );
				container.find( '.srf-chart-source' ).css( 'visibility', 'hidden' );
				// Count existing external links
				var numItems = $( '.external.autonumber' ).length + $( '.srf-chart-source' ).length;
				container.find( '.srf-chart-source' ).find( 'a' ).text( '[' + numItems + ']'  );
				addedHeight += container.find( '.srf-chart-source' ).height();
			}

			// Set-up chart text
			var chartText = data.parameters.charttext;
			if ( chartText.length > 0 ) {
				container.find( '.' + plotClass ).after( '<span class="srf-chart-text">' + chartText + '</span>' );
				container.find( '.srf-chart-text' ).css( 'visibility', 'hidden' );
				addedHeight += container.find( '.srf-chart-text' ).height();
			}

			// Keep the overall height and width and apply possible changes onto the chart
			height = height - ( data.parameters.datatable === 'tabs' ? 20 + addedHeight : addedHeight );
			container.css( { 'height': height, 'width': width } );
			container.find( '.' + plotClass ).css( { 'height': height , 'width': width } );

			// Table view plugin
			function showTable(){
				if ( data.parameters.datatable === 'tabs' ) {
					// Datatable declaration
					var dataSeries = [];
					var dataTable = [];
					if ( data.data.source.annotation !== undefined ){
						$.map( data.data.source.annotation , function( val ){
							dataSeries.push ( { label: val.series } );
							dataTable.push ( [[ val.shortText + ' (' + val.text + ')', val.x]] );
						} );
					}

					// Tableview plugin
					chart.srftableview( {
						'chart'     : chart,
						'id'        : chartID,
						'container' : container,
						'info'      : data.parameters.infotext,
						'data' : {
							'series': dataSeries,
							'data'  : dataTable,
							'sask'  : data.sask
						}
					} );
				}
			}

			// Data source
			if ( data.parameters.datasource !== 'page' ){
				var dataTable = data.data.source.url;
			}

			// Manage annotations 
			var annotations = data.data.source.annotation;
			if ( annotations !== undefined ){
					$.map( annotations , function(key){
						// Determine correct width of the shortText (therefore use a <div> as vehicle)
						// and not the length (such as key.shortText.length)
						var o = $('<div>' + key.shortText + '</div>')
							.css( {'position': 'absolute', 'float': 'left', 'white-space': 'nowrap', 'visibility': 'hidden'} )
							.appendTo( container );
							key.width = o.width() + 5;
							o.remove();
					} );
			}

			// Init dygraph
			var g = new Dygraph(
				document.getElementById( plotID ),
				function() { container.hide(); return dataTable; },
				{
					rollPeriod: data.parameters.rollerperiod,
					showRoller: data.parameters.rollerperiod > 0 ? true : false,
					title: data.parameters.charttitle,
					ylabel: data.parameters.ylabel,
					xlabel: data.parameters.xlabel,
					labelsKMB: true,
					customBars: data.parameters.errorbar === 'range',
					fractions: data.parameters.errorbar === 'fraction',
					errorBars: data.parameters.errorbar === 'sigma',
					legend: 'always',
					//labels: data.parameters.group === 'label' ? dataSeriesLabel : data.parameters.datasource !== 'file' ? null : dataSeriesLabel,
					labelsDivStyles: { 'textAlign': 'right', 'background': 'transparent' },
					labelsSeparateLines: true,
					underlayCallback: function(canvas, area, g) {
						// Allow background to be white
						canvas.fillStyle = 'white';
						canvas.fillRect(area.x, area.y, area.w, area.h);
					},
				// drawCallback gets called every time the dygraph is drawn. This includes
				// the initial draw, after zooming and repeatedly while panning
				// @see http://dygraphs.com/options.html#Callbacks
				drawCallback: function(g, is_initial) {
					if (!is_initial) return;
						container.show();
						// Release objects after the chart is ready to avoid display clutter
						container.find( ".srf-chart-text" ).css( 'visibility', 'visible');
						container.find( ".srf-chart-source" ).css( 'visibility', 'visible');
						container.css( 'visibility', 'visible' );
						chart.find( ".srf-processing" ).hide();
						// Tableview plug-in processing
						showTable();

						// Display annotations
						if ( annotations !== undefined ){
							g.setAnnotations( annotations );
						}
					}
				}
			);
			} );
		}
	} );

	$( document ).ready( function() {
		// Check if eachAsync exists, and if so use it to increase browsers responsiveness
		if( $.isFunction( $.fn.eachAsync ) ){
				$( '.srf-dygraphs' ).eachAsync( {
				delay: 100,
				bulk: 0,
				loop: function(){
					$( this ).srfdygraphs();
				}
			} );
		}else{
			$( '.srf-dygraphs' ).each( function() {
				$( this ).srfdygraphs();
			} );
		}
	} );
} )( mediaWiki, jQuery );