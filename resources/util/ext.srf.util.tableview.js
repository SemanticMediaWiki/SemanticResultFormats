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

	$.fn.extend( {
		srftableview: function( settings ) {

			var options = $.extend( {
				'height' : this.height() - 10,
				'width'  : this.width() - 30,
				'pagerID': settings.id + '-grid-pager'
			}, settings );

			return this.each( function() {

				var obj = $(this),
					height = options.height,
					width = options.width,
					pagerID = options.pagerID;

				// Add tabs navigation
				var tabChartNavi = '<li><a href="#' + options.id + '">' + mw.msg( 'srf-chart-tableview-chart-tab' ) +'</a></li>';
				var tabDataNavi  = '<li><a href="#' + options.id + '-data">' + mw.msg( 'srf-chart-tableview-data-tab' ) +'</a></li>';
				var tabInfoNavi  = '<li><a href="#' + options.id + '-info">' + mw.msg( 'srf-chart-tableview-info-tab' ) +'</a></li>';
				obj.prepend( '<ul>' + tabChartNavi
					+ ( options.data.data !== undefined && options.data.data.length > 0 ? tabDataNavi : '' )
					+ ( options.info !== undefined && options.info !== '' ? tabInfoNavi : '' ) + '</ul>'
				);

				// Represents the info tab
				if ( options.info !== undefined && options.info !== '' ){
					obj.find( '.container' )
						.after( '<div id="' + options.id + '-info" class="srf-chart-info-tab">' + options.info + '</div>' );
				}

				// Represents the data tab
				if ( options.data.data !== undefined && options.data.data.length > 0 ){
					obj.find( '.container' )
						.after( '<div id="' + options.id + '-data" class="srf-chart-data-tab"></div>' );
				}

				// Init table elements
				obj.find( '#' + options.id + '-data')
					.prepend( '<table id='+ options.id + '-grid class="srf-chart-datatable"></table>' )
					.prepend( '<div id='+ pagerID + ' class="srf-chart-datatable-pager"></div>' );
				obj.find( '#' + options.id + '-data')
					.css( { width: width, height: height } );

				// Create tabs ui
				obj.tabs();

				// Reiterate href link after tabs() was applied
				if ( mw.config.get( 'wgCanonicalSpecialPageName' ) === 'Ask' || options.data.sask === undefined ){
					obj.find( '.srf-chart-query-link' )
						.empty();
				}else{
					obj.find( '.ui-tabs-nav' )
						.prepend( '<span class="srf-chart-query-link">' + options.data.sask + '</span>' );
					obj.find( '.srf-chart-query-link' )
						.find( 'a' )
						.attr( 'title', mw.msg( 'ask' ) );
				}

				var dataTableGrid = obj.find( '.srf-chart-datatable' ),
					dataContainer = options.data.data,
					series = options.data.series,
					columnWidth = ( options.width / 2 ) - 5,
					tableHeight = options.height - 110;

				var gridTable = [],
					counter = 0;

				// Data array
				for ( var j = 0; j < dataContainer.length; ++j ) {
					var ttSeries = series[j];
					for ( var i = 0; i < dataContainer[j].length; ++i ) {
						var row = { id: ++counter , series: ttSeries.label, item: dataContainer[j][i][0], value: dataContainer[j][i][1] };
						gridTable.push( row );
					}
				}

				// Adopt data item output
				var colModelItem = '';
				if ( options.data.fcolumntypeid === '_dat' ) {
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
					pager: '#' + options.pagerID ,
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

				// Init column search
				dataTableGrid.jqGrid('filterToolbar', {
					stringResult: true,
					searchOnEnter: false,
					defaultSearch: "cn"
				} );
			} );
		}
	} );
} )(jQuery);