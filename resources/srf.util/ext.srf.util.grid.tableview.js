/**
 * JavaScript for SRF grid tableview plugin
 *
 * jshint checked
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @release: 0.2
 */
( function( $ ) {
	"use strict";

	/*global mw:true*/

	var methods = {
		/**
		 * Initialization method
		 *
		 * @since 1.8
		 */
		init : function( options ) {
			var height = this.height() - 10,
				width    = this.width() - 30,
				pagerID  = options.id + '-grid-pager';

			var settings = $.extend( {
				'height' : height,
				'width'  : width,
				'pagerID': pagerID
			}, options );

			// Add tabs navigation
			var tabChartNavi = '<li><a href="#' + options.id + '">' + mw.msg( 'srf-chart-tableview-chart-tab' ) +'</a></li>';
			var tabDataNavi  = '<li><a href="#' + options.id + '-data">' + mw.msg( 'srf-chart-tableview-data-tab' ) +'</a></li>';
			this.prepend( '<ul>' + tabChartNavi + tabDataNavi + '</ul>' ) ;

			// Represents the data tab
			var dataTab = '<div id="' + options.id + '-data"></div>';
			options.chart.after( dataTab );

			// Table definition
			var datatable = '<table id='+ options.id + '-grid class="srf-jqplot-datatable"></table>',
				tablepager = '<div id='+ pagerID + ' class="srf-jqplot-datatable-pager"></div>';

			// Init table elements
			this.find( '#' + options.id + '-data').prepend( datatable ).prepend( tablepager );
			this.find( '#' + options.id + '-data').css( { width: width, height: height } );

			// Generate jqGrid table
			this.find( '.srf-jqplot-datatable' ).srfTableView( 'show', settings );

			// Create tabs ui
			this.tabs();
		},
		/**
		 * Method that creates the datatable
		 *
		 * @since 1.8
		 */
		show : function( content ) {
			var dataTableGrid = this,
				data = content.data.data,
				series = content.data.series,
				columnWidth = ( content.width / 2 ) - 5,
				tableHeight = content.height - 110;

			var gridTable = [],
				counter     = 0;

			// Data array
			for ( var j = 0; j < data.length; ++j ) {
				var ttSeries = series[j];
				for ( var i = 0; i < data[j].length; ++i ) {
					var row = { id: ++counter , series: ttSeries.label, item: data[j][i][0], value: data[j][i][1] };
					gridTable.push( row );
				}
			}

			// Adopt data item output
			var colModelItem = '';
			if ( content.data.fcolumntypeid === '_dat' ) {
				// Fetch default date display
				var dateFormat = mw.user.options.get( 'date' );
				if ( dateFormat.indexOf( 'ISO' ) >= 0 ){
					dateFormat = "Y-m-d H:i:s";
				} else {
					dateFormat = 'd M Y';
				}

				colModelItem = {
					name:'item',
					index:'item',
					width: columnWidth,
					align:'center',
					sorttype:'date',
					formatter:'date',
					formatoptions: { srcformat: 'U', newformat: dateFormat }
					};
			}else{
				colModelItem = { name:'item', index:'item', width: columnWidth };
			}

			// Create grid instance
			// @see http://www.trirand.com/jqgridwiki/doku.php
			dataTableGrid.jqGrid({
				datatype: 'local',
				data: gridTable,
				colNames:[
					'id',
					mw.msg( 'srf-chart-tableview-series' ),
					mw.msg( 'srf-chart-tableview-item' ),
					mw.msg( 'srf-chart-tableview-value' )
				],
				colModel :[
					{ name:'id', index:'id', sorttype: 'int', hidden:true },
					{ name:'series', index:'series', width: 0 },
					colModelItem,
					{ name:'value', index:'value', width: columnWidth, align:"right" }
				],
				pager: '#' + content.pagerID ,
				height: tableHeight,
				rowList:[10,20,30,40,50],
				ignoreCase: true,
				grouping:true,
				groupingView : {
					groupField : ['series'],
					groupColumnShow : [false]
				},
				sortname: 'item',
				sortorder: 'asc',
				viewrecords: true,
				hidegrid: false
			} );

			// init column search
			dataTableGrid.jqGrid('filterToolbar', {
				stringResult: true,
				searchOnEnter: false,
				defaultSearch: "cn"
			} );
		}
	};

	/**
	 * srfTableView plugin method logic
	 *
	 * @since 1.8
	 */
	$.fn.srfTableView = function( method ) {
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist within the jQuery.srf.plugin pool' );
		}
	};
} )( window.jQuery );