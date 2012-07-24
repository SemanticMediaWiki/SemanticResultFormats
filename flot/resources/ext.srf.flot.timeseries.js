/**
 * JavaSript for SRF flot chart module
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @release: 0.2
 */
(function( $ ) {

/* Error handling *************************************************************/
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }
/* Start javascript ***********************************************************/

	// Time series data grid table
	$.fn.timeSeriesDataTable = function ( data , charttitle, width, pagerID ) {

		var gridTable = [],
			names       = ["id", "series" ,"date", "value" ],
			counter     = 0;

		// Add row to gridTable
		var addRow = function ( names, counter, label, data ) {
					var column = {};
						column[names[0]] = counter;
						column[names[1]] = label;
							for ( var k = 0; k < data.length; ++k ) {
								column[names[k+2]] = names[k+2] == 'date' ? data[k] / 1000 : data[k];
							}
			return column;
		};

		// Flatten data array
		for (var j = 0; j < data.length; ++j) {
			for ( var i = 0; i < data[j].data.length; ++i ) {
				gridTable.push( addRow( names, ++counter , data[j].label, data[j].data[i] ) );
			}
		}

		// Create grid instance
		return this.jqGrid({
			datatype: 'local',
			data: gridTable,
			colNames:['Id','Series','Date','Value'],
			colModel :[
				{name:'id',index:'id', width:65, sorttype: 'int', hidden:true},
				{name:'series',index:'series' },
				{name:'date',index:'date', width:120, align:'center', sorttype:'date', formatter:'date', formatoptions: {srcformat: 'U', newformat:'d/m/Y'} },
				{name:'value',index:'value'}
			],
			pager: '#' + pagerID ,
			rowNum:10,
			rowList:[10,20,30],
			grouping:true,
			groupingView : {
				groupField : ['series'],
				groupDataSorted : true
			},
			sortname: 'date',
			sortorder: 'asc',
			viewrecords: true,
			//autowidth: true,
			width: width,
			caption: charttitle
		} )
	};

	$(document).ready( function() {

		$( ".srf-flot-timeseries" ).each(function() {

			var $this = $( this ),
				chart   = $this.find( ".container" ),
				chartID = chart.attr( "id" ),
				pagerID = 'pager-' + chartID,
				json    = mw.config.get( chartID );

			// Parse json string and convert it back into objects
			typeof json == 'string' ? container = jQuery.parseJSON( json ) : container = json;

			var data      = container['data'],
				width       = container['parameters'].width,
				height      = container['parameters'].height,
				charttitle  = container['parameters'].charttitle,
				charttext   = container['parameters'].charttext,
				layout      = container['parameters'].layout,
				status      = 0,
				addHeight   = 20,
				max         = 0;

			// Hide processing
			$this.find( ".srf-processing" ).hide();

			// Base settings
			$this.css( { 'height': height, 'width': width == 0 ? height : width } );

			// Set-up chart title
			if ( charttitle.length > 0 ) {
				charttitle = '<span class="srf-flot-chart-title">' + charttitle + '</span>';
				$this.find( '#' + chartID ).before( charttitle );
				addHeight += $this.find( '.srf-flot-chart-title' ).height();
			}

			// Set-up chart text
			if ( charttext.length > 0 ) {
				charttext  = '<span class="srf-flot-chart-text">' + charttext  + '</span>';
				$this.find( '#' + chartID ).after( charttext );
				addHeight += $this.find( '.srf-flot-chart-text' ).height();
			}

			// Set-up table and table pager
			var datatable  = '<table id=grid-'+ chartID + ' class="srf-flot-list"></table>',
				tablepager = '<div id="'+ pagerID +'" class="srf-flot-list-pager"></div>';

			if ( container['parameters'].datatable == 'top' ){
				$this.find( '#' + chartID ).before( datatable ).css( 'width', width );
				$this.find( '#' + chartID ).before( tablepager ).css( 'width', width );
			} else if ( container['parameters'].datatable == 'bottom' ){
				$this.find( '#' + chartID ).after( datatable ).css( 'width', width );
				$this.find( '#' + chartID ).after( tablepager ).css( 'width', width );
			}

			// Set-up zoom box
			var zoom  = '<div class="srf-flot-zoom" style="margin-top:10px;height:50px"></div>';
			if ( container['parameters'].zoom == 'top'){
				$this.find( '#' + chartID ).before( zoom ).css( 'width', width );
			}else if ( container['parameters'].zoom == 'bottom' ){
				$this.find( '#' + chartID ).after( zoom ).css( 'width', width );
			}
			addHeight += $this.find( '.srf-flot-zoom' ).height();

			// Set-up info note
			var infonote  = '<span class="srf-flot-note" style="display:none;"></span>';
			$this.find( '#' + chartID ).before( infonote );
			addHeight = addHeight + $this.find( '.srf-flot-note' ).height();

			// Keep the overall height and width and apply possible changes onto the chart
			chart.css( { 'height': height - addHeight, 'width': width } );

			// Release chart
			chart.show();
/* Flot object declaration ****************************************************/

			// Javascript timestamp is the number of milliseconds since
			// January 1, 1970 00:00:00 UTC therefore * 1000
			// correct timestamps daily midnights in UTC+0100, add one hour to hit
			// the midnights in the plot
			function convertData( tseries, tmax ) {
				var len=tseries.length, k;
					for (var j = 0; j < len; ++j ) {
						ttData  = tseries[j].data;
							for ( var k = 0; k < ttData.length; ++k ) {
								ttData[k][0] = ( ttData[k][0] * 1000 ) + ( 60 * 60 * 1000 );
								max = max > ttData[k][1] ? max : ttData[k][1];
						}
					}
				return ttData;
			}

			// Data conversion
			convertData( data  );

			// Helper function for returning the weekends in a period
			function weekendAreas(axes) {
				var markings = [];
				var d = new Date(axes.xaxis.min);

				// go to the first Saturday
				d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
				d.setUTCSeconds(0);
				d.setUTCMinutes(0);
				d.setUTCHours(0);
				var i = d.getTime();
				do {
					// when we don't set yaxis, the rectangle automatically
					// extends to infinity upwards and downwards
					markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
					i += 7 * 24 * 60 * 60 * 1000;
				} while (i < axes.xaxis.max);

				return markings;
			}

			// Set-up plot options
			var options = {
				xaxis: { mode: "time", tickLength: 5 },
				//yaxis: { ticks: 10 },
				alignTicksWithAxis: 1,
				selection: { mode: container['parameters'].zoom == 'top' || container['parameters'].zoom == 'bottom' ? "x" : null },
				bars: layout == 'bar' ? { show: true, barWidth: 0.6 } : false,
				grid: { markings: weekendAreas, hoverable: true, clickable: true, borderColor: '#BBB', borderWidth: 1 }
			};

			// Draw actual plot
			var plot = $.plot( $this.find( "#" + chartID ), data, options );

			// Find tick labels and calculate margin
			var tickLabelMargin  = $this.find( '.yAxis.y1Axis').find( '.tickLabel' ).width();
			$this.find( '.srf-flot-chart-text' ).css( "margin-left", tickLabelMargin + 5 );

			// Re-assign zoom box margin-left / width
			if ( container['parameters'].zoom == 'top' || container['parameters'].zoom == 'bottom' ){
				var zoomBoxWidth = $this.find( '.srf-flot-zoom > canvas' ).width() - tickLabelMargin;

				$this.find( '.srf-flot-zoom' ).css( "margin-left", tickLabelMargin );
				$this.find( '.srf-flot-zoom > canvas' ).attr( 'width', zoomBoxWidth );
				$this.find( '.srf-flot-zoom > canvas.overlay' ).attr( 'width', zoomBoxWidth );

				// Init zoom box
				var zoombox = $.plot( $this.find( '.srf-flot-zoom' ), data , {
					series: {
						lines: layout == 'bar' ? false: { show: true, lineWidth: 1 },
						bars:  layout == 'bar' ? { show: true, barWidth: 0.6 } : false,
						shadowSize: 0
					},
					grid: { borderColor: '#BBB', borderWidth: 1 },
					legend: { show: false },
					xaxis: { ticks: [], mode: "time" },
					yaxis: { ticks: [], min: 0, autoscaleMargin: 0.1 },
					selection: { mode: "x" }
				} );

				// Connect zoom box and chart
				$( "#" + chartID ).bind("plotselected", function (event, ranges) {
					if (ranges.xaxis.to - ranges.xaxis.from < 0.00001)
						ranges.xaxis.to = ranges.xaxis.from + 0.00001;
					if (ranges.yaxis.to - ranges.yaxis.from < 0.00001)
						ranges.yaxis.to = ranges.yaxis.from + 0.00001;

					// Calculate y-min and y-max for the selected x range
					var ymin, ymax;
					var plotdata = plot.getData();
						$.each(plotdata, function (e, val) {
						$.each(val.data, function (e1, val1) {
							if ((val1[0] >= ranges.xaxis.from) && (val1[0] <= ranges.xaxis.to)) {
								if (ymax == null || val1[1] > ymax) ymax = val1[1];
									if (ymin == null || val1[1] < ymin) ymin = val1[1];
								}
							} )
						} );

					// Out of range zoom message
					if ( ymin == undefined ){
						$this.find( '.srf-flot-note' ).html( mw.msg( 'srf-timeseries-zoom-out-of-range' ) ).css( { 'display': 'block',  "margin-left": tickLabelMargin + 5 } );
						chart.css( 'display', 'none' );
						$this.css( 'height', height - chart.height() + $this.find( '.srf-flot-note' ).height()  );
					}else{
						$this.css( 'height', height );
						chart.css( 'display', 'block' );
						$this.find( '.srf-flot-note' ).css( 'display', 'none' );
					}
					ranges.yaxis.from = Math.round( ymin ).toFixed(0);
					ranges.yaxis.to   = Math.round( ymax + ( ymax / Math.log(ymax)/Math.log(10) ) ).toFixed(0);

				// Do the zooming
				plot = $.plot( $this.find( "#" + chartID ), data ,
					$.extend(true, {}, options, {
						xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to },
						yaxis: { min: ranges.yaxis.from, max: ranges.yaxis.to }
					} )
				);

				// Don't fire event on the overview to prevent eternal loop
				zoombox.setSelection(ranges, true);
			} );

			$this.find( '.srf-flot-zoom' ).bind("plotselected", function (event, ranges) {
				plot.setSelection(ranges);
			} );
		}
/* jqGrid table declaration ***************************************************/

			// Init jqGrid table
			if ( container['parameters'].datatable == 'top' || container['parameters'].datatable == 'bottom' ){
				$this.find( '.srf-flot-list' ).timeSeriesDataTable( data, charttitle, width - tickLabelMargin - 10, pagerID );
			}

			// Set margin
			$this.find( '.ui-jqgrid' ).css( "margin-left", tickLabelMargin + 5 );

			// Re-calculate height
			height = $this.height() + $this.find( '.ui-jqgrid' ).height();
			$this.css ( 'height', height );

			// Re-assign height for when the collapse button is pressed
			$this.find( '.HeaderButton' ).click(function(){
				if ( status == 0 ){
					++status;
					$this.css ( 'height',  $this.height() - ( $this.find( '.ui-jqgrid' ).height() -  $this.find( '.ui-jqgrid-titlebar' ).height() ) );
					$this.find('.ui-widget-header' ).css( 'border-bottom', '0px solid #BBB' );
				} else {
					status = 0;
					$this.css ( 'height', height  );
					$this.find('.ui-widget-header' ).css( 'border-bottom', '1px solid #BBB' );
				};
			} );

/* Tooltip object declaration *************************************************/

			// Tool tip for line chart
			function showTooltip(x, y, contents) {
				$('<div class="srf-flot-tooltip">' + contents + '</div>').css( {
					position: 'absolute',
					display: 'none',
					top: y + 5,
					left: x + 5,
					//border: '1px solid #0070A3',
					padding: '2px',
					// 'background-color': '#fee',
					opacity: 0.80
				} ).appendTo("body").fadeIn(200);
			}

			var  b = function (i) {
					return i < 10 ? "0" + i : i
				},
					h = function (i) {
						var l = i.getUTCFullYear() + "-" + b(i.getUTCMonth() + 1);
						return l + "-" + b(i.getUTCDate())
				};

			var previousPoint = null;

			$( "#" + chartID ).bind("plothover", function (event, pos, item) {
				$("#x").text(pos.x.toFixed(2));
				$("#y").text(pos.y.toFixed(2));
					if (item) {
						if (previousPoint != item.datapoint) {
								previousPoint = item.datapoint;
								$( '.srf-flot-tooltip' ).remove();
									var x = item.datapoint[0],
										y = item.datapoint[1];
									showTooltip(item.pageX, item.pageY, h( new Date( x ) ) + " : " + y );
						}
					} else {
							$( '.srf-flot-tooltip' ).remove();
								previousPoint = null;
					}
		} );
/* End ************************************************************************/
		} ); // end of initilized $this object
	} ); // end $(document).ready
} )( window.jQuery );