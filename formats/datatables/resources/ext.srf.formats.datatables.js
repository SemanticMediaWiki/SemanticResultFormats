/**
 * SRF DataTables JavaScript Printer using the SMWAPI
 *
 * @see http://datatables.net/
 *
 * @since 1.9
 * @version 0.2.5
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	/* Private methods and objects */

	/**
	 * Helper objects
	 *
	 * @since 1.9
	 *
	 * @ignore
	 * @private
	 * @static
	 */
	var html = mw.html,
		profile = $.client.profile(),
		smwApi = new smw.api(),
		util = new srf.util();

	/**
	 * Container for all non-public objects and methods
	 *
	 * @private
	 * @member srf.formats.datatables
	 */
	var _datatables = {

		/**
		 * Returns ID
		 *
		 * @private
		 * @return {string}
		 */
		getID: function( container ) {
			return container.attr( 'id' );
		},

		/**
		 * Returns container data
		 *
		 * @private
		 * @return {object}
		 */
		getData: function( container ) {
			return mw.config.get( this.getID( container ) );
		},

		/**
		 * Contains methods linked to the parsing of objects
		 *
		 * @private
		 * @type {object}
		 * @return void
		 */
		parse: {

			/**
			 * Returns a html element from the MWAPI imageinfo prop
			 *
			 * @private
			 * @param  {object}
			 * @return {html|null}
			 */
			thumbnail: function( info ){
				if( $.type( info.imageinfo ) === 'array' ){
					var imageInfo = info ? info.imageinfo[0] : null;
					if ( $.inArray( 'thumburl', imageInfo ) ) {
						return html.element( 'a', {
							'href': imageInfo.descriptionurl
							}, new html.Raw( html.element( 'img', {
								'src': imageInfo.thumburl
							} ) )
						);
					}
				}
				return null;
			},

			/**
			 * Returns a parsed result object that was received
			 * using the SMWAPI
			 *
			 * @private
			 * @param  {object}
			 * @param  {object}
			 * @return {array}
			 */
			results: function( context, data ) {
				var self = this;

				// Returns a text or a href element
				// Try to resolve image/thumbnail information by fetching its
				// imageInfo from the back-end
				function createLink( wikiPage, linker, options ){
					if ( wikiPage.getNamespaceId() === 6 && linker ) {
						var imageInfo = getImageInfo( wikiPage.getName(), options );
						if ( imageInfo !== null &&  imageInfo !== undefined  ) {
							var imageLink = self.thumbnail( imageInfo );
							return imageLink !== null ? imageLink : wikiPage.getHtml( linker );
						}
					}
						return wikiPage.getHtml( linker );
				}

				// Returns a thumbnail image location and in case the info was
				// cached the result is returned immediately otherwise an event
				// is triggered to ensure that result parsing is responsive
				// and without any delay
				function getImageInfo( title, options ) {
					var imageInfo = null;

					util.image.imageInfo( {
						'cache': datatables.defaults.cacheImageInfo,
						'width': datatables.defaults.thumbSize,
						'title': title
					}, function( isCached, info ) {
						if ( isCached ){
							imageInfo = info;
						} else {
							// This info wasn't in cache so we can't wait on the response
							// and sent therefore a trigger event
							context.trigger( 'srf.datatables.afterImageInfoFetch', {
								column: options.column,
								row: options.row,
								info: info
							} );
						}
					} );
					return imageInfo;
				}

				// Transform results into a specific aaData format
				function getResults( parameters, results, printreqs ){

					var aaData = [],
						i = 0;
					$.each( results, function( subjectName, subject ) {
						var rowData = {},
							linker = parameters.link === 'all' || false,
							columnIndex = 0,
							rowIndex = i++;

						// Subject
						if ( parameters.mainlabel !== '-' ) {
							var mainLabel = parameters.mainlabel !== '' ? parameters.mainlabel : '';
							if ( subject instanceof smw.dataItem.wikiPage ){
								rowData[mainLabel] = createLink( subject, linker || parameters.link === 'subject', {
									column: columnIndex,
									row: rowIndex
								} );
							}
						}

						// Property printouts
						if ( $.inArray( 'printouts', subject ) ) {
							// Find column (properties)
							$.each( printreqs, function( index, propertyObj ) {
								columnIndex++;
								var collectedValueItem = '';
								var property = propertyObj.label;
								var values = subject.printouts[property];
								if ( values == null ) {
									rowData[property] = createLink( subject, linker, {
										column: columnIndex,
										row: rowIndex
									} );
								} else {
									$.map ( values, function( DI, key ) {
										// For multiple values within one row/column use a separator
										collectedValueItem += collectedValueItem !== '' && key >= 0 ? '<br />' : '';

										// dataItem
										if ( DI instanceof smw.dataItem.time  ){
											collectedValueItem += DI.getMediaWikiDate();
										} else if ( DI instanceof smw.dataItem.wikiPage ){
											collectedValueItem += createLink( DI, linker, {
												column: columnIndex,
												row: rowIndex
											} );
										} else if ( DI instanceof smw.dataItem.uri ){
											collectedValueItem += DI.getHtml( linker );
										} else if ( DI instanceof smw.dataItem.text ){
											collectedValueItem += DI.getText();
										} else if ( DI instanceof smw.dataItem.number ){
											collectedValueItem += DI.getNumber();
										} else if ( DI instanceof smw.dataValue.quantity ){
											collectedValueItem += DI.getUnit() !== '' ? DI.getValue() + ' ' + DI.getUnit() : DI.getValue();
										} else if ( DI instanceof smw.dataItem.unknown ){
											collectedValueItem += DI.getValue();
										}

									} );
									// For empty values ensure to use "-" otherwise
									// dataTables will show an error
									rowData[property] = collectedValueItem !== '' ? collectedValueItem : '-';
								}
							} );
						}

						// Only care for entries that are not empty
						if ( !$.isEmptyObject( rowData ) ) {
							// Collect events
							aaData.push( rowData );
						} else {
							// In case the array was empty reset the row counter
							rowIndex--;
						}
					} );

					return { 'aaData': aaData };
				}
				// Create column definitions (see aoColumnDefs)
				// @see http://www.datatables.net/usage/columns
				var aoColumnDefs = [];
				$.map ( data.query.result.printrequests, function( property, index ) {
					aoColumnDefs.push( {
						'mData': property.label,
						'sTitle': property.label,
						'sClass': 'smwtype' + property.typeid,
						'aTargets': [index]
					} );
				} );
				data.aoColumnDefs = aoColumnDefs;
				// Parse and return results
				return getResults( data.query.ask.parameters, data.query.result.results, data.query.result.printrequests );
			}
		},

		/**
		 * Export links
		 *
		 * Depending on the event that invokes a change, adopt the link query
		 *
		 * @private
		 * @return void
		 */
		exportlinks: function( context, data ) {
			var exportLinks = context.find( '#srf-panel-export > .center' ),
				parameters = {},
				printouts = [];

			// Clone data into new object in order to keep it local
			$.extend( true, parameters, data.query.ask.parameters );

			// Only columns that are visible are supposed to be part of the export links
			$.each( data.table.fnSettings().aoColumns, function( index, column ) {
				if ( column.bVisible ){
					printouts.push( data.query.ask.printouts[index] );
				}
			} ) ;

			// Manage individual links
			$.each( datatables.defaults.exportFormats, function( format, name ) {
				var formatLink = exportLinks.find( '.' + format );

				// Create element if it doesn't exists
				if ( formatLink.length === 0 ) {
					formatLink = exportLinks.append( html.element( 'span', { 'class': format } ) ).find( '.' + format );
				}

				// Set name and format
				parameters.format = format;
				parameters.searchlabel = name;

				// Create link
				var link = new smw.Query(
					printouts,
					parameters,
					data.query.ask.conditions ).getLink();

				// Remove previous link and append with an updated one
				formatLink.find( 'a' ).remove();
				formatLink.append( link );
			} ) ;
		},

		/**
		 * Internationalization
		 * @see  http://datatables.net/usage/i18n
		 *
		 * @private
		 * @return {object}
		 */
		oLanguage: {
			oAria: {
				sSortAscending : mw.msg( 'srf-ui-datatables-label-oAria-sSortAscending' ),
				sSortDescending: mw.msg( 'srf-ui-datatables-label-oAria-sSortDescending' )
			},
			oPaginate: {
				sFirst: mw.msg( 'srf-ui-datatables-label-oPaginate-sFirst' ),
				sLast : mw.msg( 'srf-ui-datatables-label-oPaginate-sLast' ),
				sNext: mw.msg( 'srf-ui-datatables-label-oPaginate-sNext' ),
				sPrevious: mw.msg( 'srf-ui-datatables-label-oPaginate-sPrevious' )
			},
			sEmptyTable: mw.msg( 'srf-ui-datatables-label-sEmptyTable' ),
			sInfo: mw.msg( 'srf-ui-datatables-label-sInfo' ),
			sInfoEmpty: mw.msg( 'srf-ui-datatables-label-sInfoEmpty' ),
			sInfoFiltered: mw.msg( 'srf-ui-datatables-label-sInfoFiltered' ),
			sInfoPostFix: mw.msg( 'srf-ui-datatables-label-sInfoPostFix' ),
			sInfoThousands: mw.msg( 'srf-ui-datatables-label-sInfoThousands' ),
			sLengthMenu: mw.msg( 'srf-ui-datatables-label-sLengthMenu' ),
			sLoadingRecords: mw.msg( 'srf-ui-datatables-label-sLoadingRecords' ),
			sProcessing: mw.msg( 'srf-ui-datatables-label-sProcessing' ),
			sSearch: mw.msg( 'srf-ui-datatables-label-sSearch' ),
			sZeroRecords: mw.msg( 'srf-ui-datatables-label-sZeroRecords' )
		},

		/**
		 * UI components
		 *
		 * @private
		 * @param  {array} context
		 * @param  {array} container
		 * @param  {array} data
		 */
		ui: function( context, container, data ){

			// Setup the query panel
			var queryPanel = context.find( '.top' );
			queryPanel.panel( {
				'show': false
			} );

			// Add exportFormat portlet
			queryPanel.panel( 'portlet', {
				'class'  : 'export',
				'fieldset': false
			} )
			.append( html.element( 'div', { 'class': 'center' } ) );

			// Init export links
			_datatables.exportlinks( context, data );

			// Map available columns
			var columnList = [];
			$.each( data.table.fnSettings().aoColumns, function( key, item ) {
				if ( key !== '' ) {
					columnList.push( item.mData !== '' ? item.mData : '#' );
				}
			} );

			// Column filter
			var columnFilter,
				columnSearchFilter,
				columnSearchInput;

			// Add column portlet
			columnFilter = queryPanel.panel( 'portlet', {
				'class'  : 'columnfilter',
				'title'  : mw.msg( 'srf-ui-datatables-label-filters' ),
				'fieldset': true
			} ).find( 'fieldset' );

			// Add column visibility select options list
			columnFilter.optionslist()
			.optionslist( 'selectlist', {
				'list' : columnList,
				'class': 'columnfilter',
				'multiple': true,
				'selectedAll': true,
				'null': false
			} )
			.multiselect( {
				header: mw.msg( 'srf-ui-datatables-label-multiselect-column-header' ),
				noneSelectedText: mw.msg( 'srf-ui-datatables-label-multiselect-column-noneselectedtext' ),
				selectedText: '# ' + mw.msg( 'srf-ui-datatables-label-multiselect-column-selectedtext' ),
				height: columnList.length > 5 ? undefined : 'auto',
				minWidth: 'auto',
				click: function( event, ui ) {
					var bVis = data.table.fnSettings().aoColumns[ui.value].bVisible;
					data.table.fnSetColumnVis( ui.value, !bVis );

					// Update export links
					_datatables.exportlinks( context, data );
				}
			} );

			// Multiselect minWidth didn't work in FF therefore we fix it here
			columnFilter.find( '.ui-multiselect' ).css( 'width', '205px' );

			// Add column search filter
			columnFilter.append( '<br>' )
			.optionslist( 'selectlist', {
				'list' : columnList,
				'class': 'columnsearchfilter',
				'selectedAll': false,
				'null': true,
				change: function( event, ui ) {
					// Clear previous fields before storing a new filter set
					data.table.fnFilter( '', columnSearchFilter );
					columnSearchFilter = ui.value;
					var disabled = columnSearchFilter ? '' : 'disabled';
					columnFilter.find( '#columnsearchinput' ).prop( 'disabled', disabled ).val( '' );
				}
			} );

			// Add column search input
			columnFilter.append( '<br>' )
			.append( html.element( 'input', {
				'id': 'columnsearchinput',
				'placeholder': mw.msg( 'srf-ui-datatables-label-placeholder-column-search' ),
				'disabled': 'disabled'
			}, '' ) + '<br>' )
			.on( 'input propertychange', '#columnsearchinput', function( event ) {
				columnSearchInput = $( this ).val();
				if( columnSearchInput !== '' && columnSearchFilter !== '' ){
					// Apply search to the selected column
					data.table.fnFilter( columnSearchInput, columnSearchFilter );
				} else {
					// Reset the search term to null
					data.table.fnFilter( '', columnSearchFilter );
				}
			} );

			// Query conditions portlet
			var conditionsPortlet = queryPanel.panel( 'portlet', {
				'class'  : 'conditions',
				'title'  : mw.msg( 'srf-ui-datatables-label-conditions' ),
				'fieldset': true
			} );

			// Only allow logged-in users to alter query conditions via the
			// text input
			$( html.element( 'textarea', {
				'id': 'condition',
				'disabled': !datatables.defaults.userIsKnown
				}, data.query.ask.conditions
			) )
			.insertAfter( conditionsPortlet.find( 'fieldset > legend' ) )
			.on( 'input propertychange', function( event ) {
				var conditions = $( this ).val();
				// Store the input only where it contains content
				data.query.ask.conditions = conditions !== '' ? conditions : data.query.ask.conditions;
			} );

			// Parameters portlet
			var parametersPortlet = queryPanel.panel( 'portlet', {
				'class'  : 'parameters',
				'title'  : mw.msg( 'srf-ui-datatables-label-parameters' ),
				'fieldset': true
			} ).find( 'fieldset' ).parameters();

			// Limit parameter
			parametersPortlet.parameters( 'limit', {
				limit : data.query.ask.parameters.limit,
				count : data.query.result.meta.count,
				max   : datatables.defaults.inlineLimit,
				step  : datatables.defaults.inlineLimit / ( datatables.defaults.inlineLimit > 1000 ? 100 : 10 ),
				change: function( event, ui ) {
					data.query.ask.parameters.limit = ui.value ;
					// As soon as the limit changes, trigger an update
					datatables.update( context, data );
					event.preventDefault();
				}
			} );

			// Disclaimer and content source text
			queryPanel.panel( 'portlet', {
				'class'  : 'information',
				'title'  : mw.msg( 'srf-ui-datatables-label-information' ),
				'fieldset': true
			} )
			.find( 'fieldset > legend' )
			.after(
				html.element( 'p', { 'class': 'disclaimer' }, mw.msg( 'srf-ui-datatables-panel-disclaimer' ) )
			)
			.after(
				html.element( 'p', { 'class': 'content-source' }, mw.msg( 'srf-ui-datatables-label-content-server' ) )
			);

			// Refresh button
			$( html.element( 'span', { 'class': 'button' } ) )
			.insertBefore( container.find( '.span-select' ) )
			.button( {
				icons: { primary: 'ui-icon-refresh' },
				text: false
			} )
			.removeClass( 'ui-corner-all' )
			.addClass( 'ui-corner-right' )
			.on( 'click', function( event ){
				datatables.update( context, data );
			} );

			// Panel switch button
			$( html.element( 'span', {'class': 'button' } ) )
			.insertBefore( container.find( '.span-select' ) )
			.button( {
				icons: { primary: 'ui-icon-bookmark' },
				text: false
			} )
			.removeClass( 'ui-corner-all' )
			.addClass( 'ui-corner-left' )
			.on( 'click', function( event ){
				queryPanel.panel( 'toggle' );
			} );

			// Insert space between search field and button
			$( html.element( 'span', {'class': 'button-space' } ) )
			.insertBefore( container.find( '.span-select' ) );
		}
	};

	/**
	 * Inheritance class for the srf.formats constructor
	 *
	 * @since 1.9
	 *
	 * @class
	 * @abstract
	 */
	srf.formats = srf.formats || {};

	/**
	 * Class that contains the DataTables JavaScript result printer
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 * @extends srf.formats
	 */
	srf.formats.datatables = function() {};

	/* Public methods */

	srf.formats.datatables.prototype = {

		/**
		 * Default settings
		 *
		 * @note MW 1.21 vs MW 1.20
		 * Apparently mw.config.get( 'srf' )/mw.config.get( 'smw' ) does only work
		 * in MW 1.21 therefore instead of being customizable those settings are
		 * going to be fixed
		 *
		 * TTL (if enabled) cache for resultObject is set to be 15 min by default
		 * TTL (if enabled) cache for imageInfo is set to be 24 h
		 *
		 * @since  1.9
		 *
		 * @property
		 */
		defaults: {
			autoUpdate: mw.user.options.get( 'srf-prefs-datatables-options-update-default' ),
			userIsKnown: mw.config.get( 'wgUserName' ),
			cacheImageInfo: mw.user.options.get( 'srf-prefs-datatables-options-cache-default' ) ? 86400000 : false,
			cacheApi: mw.user.options.get( 'srf-prefs-datatables-options-cache-default' ),
			// thumbSize: mw.config.get( 'srf' ).options.thumbsize[mw.user.options.get( 'thumbsize' )],
			// inlineLimit: mw.config.get( 'smw' ).options['QMaxInlineLimit']
			thumbSize: 180,
			inlineLimit: 750,
			exportFormats: { 'csv': 'CSV', 'rss': 'RSS', 'json': 'JSON', 'rdf': 'RDF' }
		},

		/**
		 * Initializes the DataTables instance
		 *
		 * @since 1.9
		 *
		 * @param  {array} context
		 * @param  {array} container
		 * @param  {array} data
		 */
		init: function( context, container, data ) {
			var self = this;

			// Hide loading spinner
			context.find( '.srf-loading-dots' ).hide();

			// Show container
			container.css( { 'display' : 'block' , overflow: 'hidden' } );

			// Setup a raw table
			container.html( html.element( 'table', {
				'class': 'bordered-table zebra-striped',
				'cellpadding': '0',
				'cellspacing': '0',
				'border': '0'
				}
			) );

			// Parse JS array and merge with the data array
			$.extend( data, _datatables.parse.results( context, data ) );

			//console.log( 'Data', data, 'Objects', _datatables );

			if ( data.aaData.length > 0 ){
				//@note Do something here
			}

			// Init dataTables
			var sDom = context.data( 'theme' ) === 'bootstrap'? "<'row'<'span-select'l><'span-search'f>r>t<'row'<'span-list'i><'span-page'p>>" : 'lfrtip';
			data.table = container.find( 'table' ).dataTable( {
				'sDom': sDom,
				'sPaginationType': context.data( 'theme' ) === 'bootstrap' ? 'bootstrap' : 'full_numbers',
				'bAutoWidth': false,
				'oLanguage': _datatables.oLanguage,
				'aaData': data.aaData,
				'aoColumnDefs': data.aoColumnDefs
			} );
			// Bind the imageInfo trigger and update the appropriate table cell
			context.on( 'srf.datatables.afterImageInfoFetch', function( event, handler ) {
				// If the image/thumbnail info array was empty don't bother with an update
				if( handler.info.imageinfo ){
					data.table.fnUpdate( _datatables.parse.thumbnail( handler.info ), handler.row, handler.column );
				}
			} );

			// Add UI components
			_datatables.ui( context, container, data );
		},

		/**
		 * Handles updates via Ajax
		 *
		 * @since  1.9
		 *
		 * @param  {array} context
		 * @param  {array} data
		 */
		update: function( context, data ){
			var self = this;

			// Lock the current context to avoid queuing issues during the update
			// process (e.g another button is pressed )
			context.block( {
				message: html.element( 'span', { 'class': 'mw-ajax-loader' }, '' ),
				css: {
					border: '2px solid #DDD',
					height: '20px', 'padding-top' : '35px',
					opacity: 0.8, '-webkit-border-radius': '5px',
					'-moz-border-radius': '5px',
					'border-radius' : '5px'
				},
				overlayCSS: {
					backgroundColor: '#fff',
					opacity: 0.6,
					cursor: 'wait'
				}
			} );

			// Collect query information
			var conditions = data.query.ask.conditions,
				printouts = data.query.ask.printouts,
				parameters = {
					'limit' : data.query.ask.parameters.limit,
					'offset': data.query.ask.parameters.offset
				};

			// Stringify the query
			var queryString = new smw.Query( printouts, parameters, conditions ).toString();

			// Fetch data via Ajax/SMWAPI
			smwApi.fetch( queryString, datatables.defaults.cacheApi )
			.done( function ( result ) {

				// Copy result query data and run a result parse
				$.extend( data.query.result, result.query );
				$.extend( data, _datatables.parse.results( context, data ) );

				// Refresh datatables
				data.table.fnClearTable();
				data.table.fnAddData( data.aaData );
				data.table.fnDraw();

				// Update information from where the content was derived
				context.find( '#srf-panel-information .content-source' )
				.toggleClass( 'cache', result.isCached )
				.text( result.isCached ? mw.msg( 'srf-ui-datatables-label-content-cache' ) : mw.msg( 'srf-ui-datatables-label-content-server' ) );

				// Update conditions text-field content
				context.find( '#condition' ).val( data.query.ask.conditions );

				// Update limit parameter (widget)
				context.find( '.parameters > fieldset' ).parameters(
					'option', 'limit', {
						'limit': data.query.ask.parameters.limit,
						'count': data.query.result.meta.count
				} ) ;

				// Update export links
				_datatables.exportlinks( context, data );

				context.unblock( {
					onUnblock: function(){ util.notification.create ( {
						content: mw.msg( 'srf-ui-datatables-label-update-success' )
						} );
					}
				} );
			} )
			.fail( function ( error ) {
				context.unblock( {
					onUnblock: function(){ util.notification.create ( {
						content: mw.msg( 'srf-ui-datatables-label-update-error' ),
						color: '#BF381A'
						} );
					}
				} );
			} );
		},

		/**
		 * Test interface which enables some internal methods / objects
		 * to be tested via qunit
		 *
		 * @since 1.9
		 *
		 * @ignore
		 */
		test: {
			_parse: _datatables.parse,
		}
	};

	/**
	 * dataTables implementation
	 *
	 * @ignore
	 */
	var datatables = new srf.formats.datatables();

	$( document ).ready( function() {
		$( '.srf-datatables' ).each( function() {

			var context = $( this ),
				container = context.find( '.container' ),
				data = smwApi.parse( _datatables.getData( container ) );

			// Add bottom element to avoid display clutter on succeeding elements
			$( html.element( 'div', {
				'class': 'bottom',
				'style': 'clear:both'
				}
			) ).appendTo( context );

			// Adopt directionality which ensures that all elements within its context
			// are appropriately displayed
			context.prop( 'dir', $( 'html' ).attr( 'dir' ) );
			context.prop( 'lang', $( 'html' ).attr( 'lang' ) );

			// Ensures that CSS/JS dependencies are "really" loaded before
			// dataTables gets initialized
			mw.loader.using( 'ext.srf.datatables.' + context.data( 'theme' ), function(){
				datatables.init( context, container, data );

				// Do an auto update if enabled via user-preferences
				if ( datatables.defaults.autoUpdate ) {
					datatables.update( context, data );
				}
			} );

		} );
	} );
} )( jQuery, mediaWiki, semanticFormats );
