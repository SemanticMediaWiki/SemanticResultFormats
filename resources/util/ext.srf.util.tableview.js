/**
 * JavaScript for SRF grid tableview plugin
 *
 * @see http://www.semantic-mediawiki.org/wiki/Help:Tableview
 *
 * @since 1.8
 * @release 0.2.1
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $ ) {
	"use strict";

	/*global mw:true*/

	$.fn.extend( {
		srftableview: function( settings ) {

			var options = $.extend( {
				'height' : this.height(),
				'width'  : settings.widthBorder !== undefined ? this.width() - settings.widthBorder : this.width() - 30,
				'pagerID': settings.id + '-grid-pager'
			}, settings );

			// Add tab li element
			function addTab( tab ){
				return '<li><a href="#' + tab.id + '">' + tab.msg +'</a></li>';
			}

			return this.each( function() {

				var obj = $( this ),
					height = options.height,
					width = options.width,
					pagerID = options.pagerID,
					tabs = [];

				// Tabs definition
				tabs.chart = addTab( { id: options.id, msg: mw.msg( 'srf-chart-tableview-chart-tab' ) } );
				tabs.data  = addTab( { id: options.id + '-data', msg: mw.msg( 'srf-chart-tableview-data-tab' ) } );
				tabs.info  = addTab( { id: options.id + '-info', msg: mw.msg( 'srf-chart-tableview-info-tab' ) } );

				// Add tabs navigation
				obj.prepend( '<ul>' + tabs.chart +
					( options.data.data !== undefined && options.data.data.length > 0 ? tabs.data : '' ) +
					( options.info !== undefined && options.info !== '' ? tabs.info : '' ) + '</ul>'
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

				// Tabs height can vary (due to CSS) therefore after tabs instance was
				// created get the height
				var _tabs = obj.find( '.ui-tabs-nav' );
				var tabsHeight = _tabs.height();

				// Create Special:Ask query link [+]
				if ( mw.config.get( 'wgCanonicalSpecialPageName' ) === 'Ask' || options.data.sask === undefined ){
					obj.find( '.srf-chart-query-link' )
						.empty();
				} else {
					_tabs.prepend( '<span class="srf-chart-query-link">' + options.data.sask + '</span>' );
					obj.find( '.srf-chart-query-link' )
						.find( 'a' )
						.attr( 'title', mw.msg( 'ask' ) );
				}

				var dataTableGrid = obj.find( '.srf-chart-datatable' ),
					dataContainer = options.data.data,
					series = options.data.series,
					columnWidth = ( options.width / 2 ) - 5,
					tableHeight = options.height - 100 - tabsHeight;

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