/**
 * SRF JavaScript for event calendar module which uses the fullcalendar library
 *
 * @see http://arshaw.com/fullcalendar/docs/
 * @see http://www.semantic-mediawiki.org/wiki/Help:Eventcalendar_format
 *
 * Please be aware that release 0.6 will break with the SRF 1.8 Event calendar
 * implementation
 *
 * @since 1.8
 * @release 0.7.2
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
/* global mw:true smw:true mediaWiki:true, semanticMediawiki:true, semanticFormats:true */
( function( $, mw, srf ) {
	'use strict';

	////////////////////////// PRIVATE METHODS ////////////////////////

	var html = mw.html;

	/**
	 * Unility to generate Html dropdown/select element
	 *
	 * @since 1.9
	 * @type Object
	 */
	var _select = function( data ){

		// Match printouts to selected types such as (_dat, _str etc.)
		function matchPrintouts( keys ){
			return _calendar.api.query.printouts.search.type(
				_calendar.api.query.printouts.toList( data.query.ask.printouts ),
				_calendar.api.results.printrequests( data.query.result.printrequests ).toArray(),
				keys
			);
		}

		// Build and return dropdown elements
		function elements( list ){
			var dropdown = '';
			$.each( list, function( index, text ) {
				if ( typeof text === 'object' ) {
					text = text[0];
				}
				dropdown = dropdown + html.element( 'option' ,{ 'value': index }, text );
			} );
			return html.element( 'option', { 'value': '' }, '' ) + dropdown;
		}

		return{
			build: function( list, selectID, selectClass, browser, disabled ){
				// @note the dropdown size behaves differently in some browsers
				// therefore we assign a css class that can be used for adjustments
				// Default settings are avilable for chrome and firefox
				return html.element( 'div',{ 'class': 'select-wrap-' + browser },
					new html.Raw ( html.element( 'select', { 'id': selectID, 'class': selectClass, 'disabled': disabled || false },
						new html.Raw( elements( list ) ) )
					)
				);
			},

			list: function( keys ){
				return matchPrintouts( keys );
			}
		};
	};

	/**
	 * Calendar related utility functions
	 *
	 * @since 1.9
	 * @type Object
	 */
	var _calendar = {

		/**
		 * Returns default settings
		 *
		 * @since 1.9
		 */
		defaults: {
			color: '#48a0d5',
			dateFormat: 'yy-mm-dd',
			descriptionLength: 100,
			autoUpdate: mw.user.options.get( 'srf-prefs-eventcalendar-options-update-default' ),
			paneView: mw.user.options.get( 'srf-prefs-eventcalendar-options-paneview-default' ),

			/**
			 * Sets relevant default values
			 *
			 * @since 1.9
			 * @type Object
			 */
			set: function ( data ){
				var that = {};

				// Set theme
				that.themeUI = data.options.theme ? 'ui' : 'fc';

				// Set calendar start
				var dates = _calendar.data.getMinMax( data.dates );
				that.calendarStart = data.options.start === 'earliest' ? _calendar.api.results.dataValues.time.parseDate( dates.min ) : data.options.start === 'latest' ? _calendar.api.results.dataValues.time.parseDate( dates.max ) : null;

				that.start = {
					// Returns undefined in case where no calendarStart is specified
					// Month calendarStart value is 0-based, meaning January=0, February=1, etc.
					Year: that.calendarStart !== null ? that.calendarStart.getFullYear() : undefined,
					Month: that.calendarStart !== null ? that.calendarStart.getMonth() : undefined,
					Day: that.calendarStart !== null ? that.calendarStart.getDate() : undefined
				};

				// Google holiday calendar url
				that.holiday = data.options.gcalurl === null ? '' : data.options.gcalurl;

				// Set RTL direction
				that.isRTL = $( 'html' ).attr( 'dir' ) === 'rtl' ? true : false;

				// Browser profile
				that.browser = $.client.profile();

				$.extend( this, that );
			}
		},

		/**
		 * Returns ID
		 *
		 * @since 1.9
		 * @type Object
		 */
		getID: function( container ) {
			return container.attr( 'id' );
		},

		/**
		 * Contains parse related methods
		 *
		 * @since 1.9
		 * @type Object
		 */
		parse: {

			/**
			 * Returns a parsed json object that is stored as in-page container
			 *
			 * @since 1.9
			 * @type Object
			 */
			container: function( container ) {
				var json = mw.config.get( _calendar.getID( container ) );
				return typeof json === 'string' ? jQuery.parseJSON( json ) : json;
			},

			/**
			 * Returns a parsed query result object
			 *
			 * The array output index corresponds to FullCalendar
			 * eventObject specification
			 *
			 * id - Uniquely identifies the given event
			 * title - Required, The text on an event's element
			 * start - Required, The date/time an event begins
			 * end - Optional, The date/time an event ends
			 * url - Optional, A URL that will be used as href for when
			 * the event is clicked
			 * className - A CSS class (or array of classes) that will
			 * be attached to this event's element
			 * color - Sets an event's background and border color
			 * description is a non-standard Event Object field
			 * allDay if set false it will show the time
			 *
			 * Since 1.9 new syntax
			 * &legend - markes a property to be used as legend
			 * &filter - markes a property to be used as legend with filter option
			 *
			 * @see http://arshaw.com/fullcalendar/docs/event_data/Event_Object/
			 * @see http://arshaw.com/fullcalendar/docs/event_rendering/eventRender/
			 *
			 * @since 1.9
			 * @type Object
			 */
			api: function( data ) {

				// Flatten value object
				function getValue( values ){
					var val = '';
					$.each ( values, function( key, value ) {
						val = value;
					} );
					return val;
				}

				// Transform results into the calendar specific array format
				function getResults( parameters, printrequests, results ){

					var events = [],
						dates = [],
						legend = {};

					$.each( results, function( subjectName, subject ) {
						var rowData = {},
							rowDate = {},
							rowDesc = [],
							prevElement = '';

						// Subject
						if ( rowData.url === undefined ) {
							rowData.url = parameters.link === 'none' ? null: subject.fullurl;
							rowData.title = subject.fulltext;
						}

						if ( $.inArray( 'printouts', subject ) ) {
							$.each ( subject.printouts, function( property, values ) {
								var value = getValue( values );

								// Date/Time type properties
								if (  printrequests.getTypeId( property ) === '_dat' ){
									// The date with the lower position ( wich comes in the
									// printouts first) becomes the start date
									if ( prevElement === '' ) {
										prevElement = printrequests.getPosition( property );
										rowData.start = _calendar.api.results.dataValues.time.getISO8601Date( value );
										rowDate.start = value;
										rowData.allDay = true;
									} else if ( prevElement < printrequests.getPosition( property ) && value !== '' ) {
										rowData.end = _calendar.api.results.dataValues.time.getISO8601Date( value );
										rowDate.end = value;
										rowData.allDay = false;
									}

								// Page type properties
								} else if (  printrequests.getTypeId( property ) === '_wpg' ){
									if ( property === 'title' ){
										rowData.title = value.fulltext;
									} else if ( property === 'icon' ){
										rowData.eventicon = value.fulltext;
									} else if ( property === data.options.filterproperty ) {
										rowData.filter = { 'value' : value.fulltext, 'option': ( data.options.filterType === 'filter' ? true: false ) };
									} else if ( property !== '' ) {
										rowDesc.push( parameters.headers === 'hide' ? value.fulltext : property + ':' + value.fulltext );
									}
								} else {
									if ( property === 'title' ){
										rowData.title = value;
									} else if ( property === 'color' ) {
										rowData.color = value;
									} else if ( property === data.options.filterproperty ) {
										rowData.filter = { 'value' : value, 'option': ( data.options.filterType === 'filter' ? true: false ) };
									} else if ( property !== '' ) {
										// Items without fixed identifiers remain part of a description
										rowDesc.push( parameters.headers === 'hide' ? value : property + ':' + value );
									}
								}
							} );
							// Collect all descriptions
							rowData.description = rowDesc.join(',');
						}

						// Only care for entries that have at least a start date
						if ( rowData !== {} && $.inArray( 'start', rowData ) ) {
							if ( $.inArray( 'filter', rowData ) && rowData.filter !== undefined ){
								var filter = rowData.filter,
									color = $.inArray( 'color', rowData ) ? rowData.color : null;
								rowData.filter = filter.value;
								if ( filter.value !== undefined && color !== undefined ){
									// Collect the filter in is assigned color, colors as stored as
									// array so that the some filer can be assigned different colors
									if ( legend[filter.value] === undefined ){
										legend[filter.value] = { 'color' : [color] , 'filter': filter.option };
									}else{
										legend[filter.value].color.push( color );
									}
								}
							}
							// Collect events
							events.push( rowData );
							// Collect dates
							dates.push( rowDate );
						}
					} );
					return { 'events': events, 'legend': legend, 'dates': dates };
				}

				// Transforms the printRequests/property list
				var printrequests = _calendar.api.results.printrequests();
				printrequests.toArray( data.query.result.printrequests );

				// Return results
				return getResults( data.query.ask.parameters, printrequests, data.query.result.results );
			}
		},

		/**
		 * Contains data related methods
		 *
		 * @since 1.9
		 * @type Object
		 */
		data: {

			/**
			 * Returns min/max values of the date array
			 *
			 * @since 1.9
			 * @type Object
			 */
			getMinMax: function( dates ){
				var min = '',
					max = '';

				$.map( dates, function( values ) {
					$.each ( values, function( key, value ) {
						if ( key === 'start' && ( value < min || min === '' ) ) {
							min = value;
						} else if( key === 'end' && ( value > max || max === '' ) ) {
							max = value;
						}
					} );
				} );
				return { 'min': min, 'max': max };
			},

			/**
			 * Refresh internal objects
			 *
			 * @since 1.9
			 * @type Object
			 * @type Object
			 * @type Object
			 */
			refresh: function( context, container, data, message ){

				var startDate = new Date();

				// Re-parse data and merge them
				$.extend( data, _calendar.parse.api( data ) );

				// Displayed only while in debug mode
				srf.log( 'Parsed: ' + ( new Date().getTime() - startDate.getTime() ) + ' ms' );

				// Update the limit/count display
				_calendar.parameters( context, container, data )
				.limit()
				.update( data.query.ask.parameters.limit, data.query.result.meta.count );

				// @todo Check hash from current data object with the newly
				// arrived result hash and bail-out in case the hash match each other

				// Update defaults
				_calendar.defaults.set( data );

				// Update legend/filter
				_calendar.legend( context, container, data ).init();

				_calendar.fullCalendar( context, container, data ).update();

				if ( message ){
					_calendar.util.notification.create ( {
						content: message
					} );
				}
			},

			/**
			 * External update via ajax
			 *
			 * Lock the calendar during the update, and send a notification to the user
			 * when the task is finished
			 *
			 * @since 1.9
			 * @type Object
			 * @type Object
			 * @type Object
			 */
			update: function( context, container, data ){
				// Instead of sending the whole parameter block which the api result builder
				// doesn't much care, we eliminate thoese parameters from the query string that
				// do not influnence the result threfore we only use limit, offset
				var query = _calendar.api.query.toString( {
					printouts : data.query.ask.printouts,
					parameters: {
						'limit' : data.query.ask.parameters.limit,
						'offset': data.query.ask.parameters.offset
					},
					conditions: data.query.ask.conditions
				} );

				// Lock the current container element to avoid queuing issues
				// during the update process (e.g another button is pressed )
				context.block( {
					message: '<span class="mw-ajax-loader"></span>',
					css: {
						border: '2px solid #DDD', height: '20px', 'padding-top' : '35px', opacity: 0.8, '-webkit-border-radius': '5px', '-moz-border-radius': '5px', 'border-radius' : '5px' },
					overlayCSS: { backgroundColor: '#fff', opacity: 0.6, cursor: 'wait' }
				} );

				// Fetch data via srf.api
				_calendar.api.query.fetch( query, function( status, api ) {
					if ( status ) {

						// Reassign api query data into the data array
						$.extend( data.query.result, api.query );

						// Refresh all internal objects
						_calendar.data.refresh( context, container, data );

						// Unlock container and send notification to the user
						context.unblock( {
							onUnblock: function(){ _calendar.util.notification.create ( {
								content: mw.msg( 'srf-ui-eventcalendar-label-update-success' )
								} );
							}
						} );
					} else {
						context.unblock( {
							onUnblock: function(){ _calendar.util.notification.create ( {
								content: mw.msg( 'srf-ui-eventcalendar-label-update-error' ),
								color: '#BF381A'
								} );
							}
						} );
					} }, true // true = will set debug/log
				);
			}
		},

		/**
		 * Object related to legend and filter handling
		 *
		 * @since 1.9
		 */
		legend: function( context, container, data ){
			var self = this,
				_BASE = 'srf-ui-legendList';

			// Returns a coloured square element
			function itemSquare( colors, defaultColor ){
				var element = '';
				$.each( _calendar.api.util.array.unique( colors ), function( i, value ) {
					element = element + '<span class="srf-ui-legend-square" style="background-color:' +	( value || defaultColor ) + '";></span>';
				} );
				return element;
			}

			// Returns a list of legend/filter elements
			function itemList( legend, listClass, color ) {
				if ( legend !== undefined ) {
					var elements = [];

					$.each( legend, function( key, item ) {
						if ( key !== '' ) {
							elements.push(
								( item.filter ? '<input type="checkbox" checked="checked" name="' + key + '"/>' : '' ) +
								itemSquare( item.color, color ) +  '<span class="srf-ui-legend-label">' + key + '</span>'
							);
						}
					} );
					return '<div class="' + listClass  + '"><ul><li class="srf-legend-item">'+ elements.join('</li><li class="srf-legend-item">') + '</li></ul></div>';
				}
			}

			return {

				context: function(){
					return context.find( '.' + _BASE );
				},

				/**
				 * Creates the legend/filter element
				 *
				 * @note Before an element is created, elements are removed as precaution
				 * to avoid invalid content references
				 *
				 * @since 1.9
				 */
				init: function() {
					// Clean-up before initialization
					this.destroy();

					if ( data !== undefined && 'legend' in data && !$.isEmptyObject( data.legend ) ) {
						if ( data.options.legend === 'top' ) {
							container.prepend( itemList( data.legend, _BASE , self.defaults.color ) );
							container.find( '.' + _BASE ).addClass( data.options.theme ? 'top ui-state-default' : 'top basic' );
						} else if ( data.options.legend === 'bottom' ) {
							container.append( itemList( data.legend, _BASE, self.defaults.color ) );
							container.find( '.' + _BASE ).addClass( data.options.theme ? 'bottom ui-state-default' : 'bottom basic' );
						} else if ( data.options.legend === 'pane' ) {
							self.pane( context, container, data ).add( 'legend', '', true );
							context.find( '.legend > fieldset ' )
							.append( itemList( data.legend, _BASE + ' pane', self.defaults.color ) )
							.addClass( 'top basic' )
							.find( 'legend' ).text( self.messages.legendLabel( data.options.filterType ) );
						} else if ( data.options.legend === 'tooltip' ) {

							// Add button
							self.button( context.find( '.fc-header-right > .fc-button-today' ).next(), {
								'class': 'srf-ui-button-tooltip',
								'icon' : 'ui-icon ui-icon-gear',
								'title':  '',
								'theme': self.defaults.themeUI,
								'tooltip': true
							} )
							.removeClass( ( self.defaults.themeUI === 'fc' ? 'fc-corner-right' : 'ui-corner-right' ) );

							// Add tooltip instance for legend/filters
							self.tooltip.add( {
								contextClass: 'srf-ui-legend-tooltip',
								contentClass: 'srf-ui-legend-tooltip-content',
								targetClass : 'srf-ui-button-tooltip',
								context: container,
								title: self.messages.legendLabel( data.options.filterType ),
								type: 'info',
								button: true,
								content: itemList( data.legend, 'srf-ui-legend-tooltip-list', self.defaults.color )
							} );
						}

						this.filter();
					}
				},

				/**
				 * Clean up routine
				 *
				 * @since 1.9
				 */
				destroy: function(){
					container.find( '.' + _BASE ).remove();
					context.find( '.legend' ).remove();
					container.find( '.srf-ui-button-tooltip' ).remove();
					container.find( '.srf-ui-legend-tooltip' ).remove();
				},

				/**
				 * Event source filtering
				 *
				 * @since 1.9
				 * @type Object
				 * @type Object
				 */
				filter: function (){
					// The pane object is outside the container object therefore
					// the scope has to be switched
					var instance = data.options.legend === 'pane' ? context : container;

					// Event handling for filter box elements
					// Used in case for a check/uncheck all button (not used yet)
					instance.find( '.checkall' ).click( function() {
						instance.find( ':checkbox' ).attr( 'checked' , this.checked );
					} );

					// Individual filter/checkbox handling
					instance.find( '.srf-legend-item > :checkbox' ).click( function() {
						var $this = $( this ),
							filter = $this.attr( 'name' );

						if ( $this.is( ':checked' ) ) {
							// Filters are checked by default. Here we find filter elements
							// in the original data source and add newly selected
							// elements again.
							var source = $.map( data.events, function( event ) {
							if ( event.filter === filter ){
								return event;
								}
							} );

							// Add selected source data
							container.fullCalendar( 'addEventSource', source );
						} else {
							// Checkbox was unchecked therefore remove elemtents for the specified filter
							container.fullCalendar( 'removeEvents', function( event ) {
								return event.filter === filter;
							} );
						}
					} );
				}
			};
		},

		/**
		 * Internationalization (i18n) support
		 *
		 * @see http://arshaw.com/fullcalendar/docs/text/
		 *
		 * @since 1.8
		 */
		messages: {
				monthNames: [ mw.msg( 'january' ), mw.msg( 'february' ), mw.msg( 'march' ),
					mw.msg( 'april' ), mw.msg( 'may_long' ), mw.msg( 'june' ),
					mw.msg( 'july' ), mw.msg( 'august' ), mw.msg( 'september' ),
					mw.msg( 'october' ), mw.msg( 'november' ), mw.msg( 'december' )
				],
				monthNamesShort:[ mw.msg( 'jan' ), mw.msg( 'feb' ), mw.msg( 'mar' ),
					mw.msg( 'apr' ), mw.msg( 'may' ), mw.msg( 'jun' ),
					mw.msg( 'jul' ), mw.msg( 'aug' ), mw.msg( 'sep' ),
					mw.msg( 'oct' ), mw.msg( 'nov' ), mw.msg( 'dec' )
				],
				dayNames: [ mw.msg( 'sunday' ), mw.msg( 'monday' ), mw.msg( 'tuesday' ),
					mw.msg( 'wednesday' ), mw.msg( 'thursday' ), mw.msg( 'friday' ), mw.msg( 'saturday' )
				],
				dayNamesShort: [ mw.msg( 'sun' ), mw.msg( 'mon' ), mw.msg( 'tue' ),
					mw.msg( 'wed' ), mw.msg( 'thu' ), mw.msg( 'fri' ), mw.msg( 'sat' )
				],
				buttonText : {
					today:  mw.msg( 'srf-ui-eventcalendar-label-today' ),
					month: mw.msg( 'srf-ui-eventcalendar-label-month' ),
					week: mw.msg( 'srf-ui-eventcalendar-label-week' ),
					day: mw.msg( 'srf-ui-eventcalendar-label-day' )
				},
				allDayText : mw.msg( 'srf-ui-eventcalendar-label-allday' ),
				timeFormat : {
					'': mw.msg( 'srf-ui-eventcalendar-format-time' ),
					agenda: mw.msg( 'srf-ui-eventcalendar-format-time-agenda' )
				},
				axisFormat: mw.msg( 'srf-ui-eventcalendar-format-axis' ),
				titleFormat: {
					month: mw.msg( 'srf-ui-eventcalendar-format-title-month' ),
					week: mw.msg( 'srf-ui-eventcalendar-format-title-week' ),
					day: mw.msg( 'srf-ui-eventcalendar-format-title-day' )
				},
				columnFormat: {
					month: mw.msg( 'srf-ui-eventcalendar-format-column-month' ),
					week: mw.msg( 'srf-ui-eventcalendar-format-column-week' ),
					day: mw.msg( 'srf-ui-eventcalendar-format-column-day' )
				},
				legendLabel: function( type ){
					return type === 'filter' ? mw.msg( 'srf-ui-tooltip-title-filter' ) : mw.msg( 'srf-ui-tooltip-title-legend' );
				}
			},

		/**
		 * Button method
		 *
		 * @since 1.9
		 */
		button: function( context, options ){
			var self = this;

			// Returns button element
			function _element ( buttonClass, contentClass, title, theme ) {
				return html.element( 'span', { 'class': buttonClass, 'title': title }, new html.Raw(
					html.element( 'span', { 'class': 'fc-button ' + theme + '-state-default ' + theme + '-corner-left ' + theme + '-corner-right' }, new html.Raw(
						html.element( 'span', { 'class': 'fc-button-inner' },  new html.Raw(
							html.element( 'span', { 'class': 'fc-button-content' }, new html.Raw(
							html.element( 'span', { 'class': 'fc-icon-wrap' }, new html.Raw(
								html.element( 'div', { 'class': contentClass }, new html.Raw( '&nbsp;' ) ) ) ) ) ) +
								html.element( 'span', { 'class': 'fc-button-effect' }, new html.Raw( html.element( 'span', {}, '' ) ) )
							) )
						) )
					)
				);
			}

			// Returns space element
			function _space () {
				return html.element( 'span', { 'class' : 'fc-header-space' }, '' );
			}

			// Simulates the hover behaviour as in the fullCalendar JS
			function _hover( baseClass, theme ) {
				var instance = context.find( '.' + baseClass + ' > .fc-button' );
				return instance
					.mousedown( function() {
						instance
							.not( '.' + theme + '-state-active' )
							.not( '.' + theme + '-state-disabled' )
							.addClass( theme + '-state-down' );
					} )
					.mouseup( function() {
						instance.removeClass( theme + '-state-down');
					} )
					.hover(
						function() {
							instance.addClass( theme + '-state-hover' );
						},
						function() {
							instance
								.removeClass( theme + '-state-hover')
								.removeClass( theme + '-state-down');
						}
					);
			}

			// The tooltip button needs a special treatment as it is placed in between elements
			if ( options.tooltip ){
				context.after( _element( options['class'], options.icon, options.title, options.theme ) );
				context = context.parents();
			}else{
				context.append(	_space() + _element( options['class'], options.icon, options.title, options.theme ) );
			}
			return _hover( options['class'], options.theme );
		},

		/**
		 * Returns pane object methods
		 *
		 * @since 1.9
		 */
		pane: function( context, container, data ){
			var self = this,
				_BASE = 'srf-calendar-pane';

			return {

				init: function(){
					context.find( '.info' ).after( html.element( 'div', { 'class': _BASE }, '' ) );
					container.css( { overflow: 'hidden' } );
				},

				context: function( subClass ){
					return subClass !== undefined ? context.find( '.' + _BASE ).find( '.' + subClass ) : context.find( '.' + _BASE );
				},

				add: function( sectionClass, title, fieldset ){
					this.context().append(
						html.element( 'div', { 'id': 'srf-pane-' + sectionClass ,'class': sectionClass }, new html.Raw( ( fieldset ? html.element( 'fieldset', {}, new html.Raw( html.element( 'legend', { }, title ) ) ): this.section( title ) ) ) )
					);
					return this.context().find( '.' + sectionClass );
				},

				section: function( title ){
					return html.element( 'span', { 'class': 'section-header' }, title );
				}
			};
		},

		/**
		 * Returns parameter objects
		 *
		 * @since 1.9
		 */
		parameters: function( context, container, data ){
			var self = this,
				paneContext = self.pane( context, container ).context(),
				parameterContext = paneContext.find( '.parameters > fieldset' );

			return {

				/**
				 * Datepicker
				 *
				 * Used as navigational element and to select query dates
				 * this avoids to validate any input from users because
				 * the date selection can only be done via the datepicker
				 * also only those properties of type _dat are selectable
				 *
				 * @since 1.9
				 */
				datepicker: function(){

					// datepicker and date slection are put into the mini-calendar
					// context
					var miniCalendarContext = paneContext.find( '.mini-calendar' );

					// Append datepicker class
					miniCalendarContext.append( html.element( 'div', { 'class' : 'datepicker' }, '' ) );

					function update( status ){
						if ( status ){
							self.data.update( context, container, data );
						}
					}

					// Datepicker handling
					function datePicker( context, printouts ){
						var condition = {};

						// Reassign original condition statement
						condition.description = data.query.ask.conditions;

						context.find( '.datepicker' ).datepicker( {
							inline: true,
							showOtherMonths: true,
							changeMonth: true,
							changeYear: true,
							dateFormat: self.defaults.dateFormat ,
							onChangeMonthYear: function( year, month, inst ) {
							// @note Something for later var date = new Date(); container.fullCalendar('gotoDate', year, month, date.getDate());
							},
							onSelect: function( dateText, inst){
								var date = new Date(dateText),
									option = miniCalendarContext.find( 'input:radio[name=option]:checked' ).val();

								// Use the stored index to find the related property
								var fromProperty = printouts[ context.find( '#mini-calendar-from' ).data( 'property' ) ],
									toProperty = printouts[ context.find( '#mini-calendar-to' ).data( 'property' ) ];

								if ( option === 'f' && fromProperty !== undefined ){
									condition.start = _calendar.api.query.conditions.build( fromProperty, dateText, '::>' );
									data.query.ask.conditions = condition;
									context.find( '#mini-calendar-from' ).val( dateText );  // updates the date
									context.find( 'input:radio[name=option]' ).prop( 'checked', false ); // uncheck this option
									context.find( '#printouts' ).prop( 'disabled', true ); // disables the dropdown
									context.find( '.datepicker' ).datepicker( "option", { "maxDate": null, "minDate": null } ); // set datepicker min/max values to null
									// When conditions ( roperties and from/to date ) are met do an update via ajax
									// one might also just go through all the events locally but here we
									// do a full fetch
									update( ( context.find( '#mini-calendar-to' ).val( ) && context.find( '#mini-calendar-from' ).val() !== '' && fromProperty !== undefined && toProperty !== undefined ) );
								} else if( option === 't' && toProperty !== undefined  ){
									condition.end = _calendar.api.query.conditions.build( toProperty, dateText, '::<' );
									data.query.ask.conditions = condition;
									context.find( '#mini-calendar-to' ).val( dateText );
									context.find( 'input:radio[name=option]' ).prop( 'checked', false );
									context.find( '#printouts' ).prop( 'disabled', true );
									context.find( '.datepicker' ).datepicker( "option", { "maxDate": null, "minDate": null } );
									update( ( context.find( '#mini-calendar-to' ).val( ) && context.find( '#mini-calendar-from' ).val() !== '' && fromProperty !== undefined && toProperty !== undefined ) );
								} else if ( option === undefined ) {
									// No option means that in the current state, actions are related to a navigational purpose
									container.fullCalendar( 'gotoDate', date );
								}
								// @note Something for later var view = container.fullCalendar('getView'); if ( view.name == 'agendaWeek' ){ container.fullCalendar( 'changeView', 'agendaWeek' );} else { container.fullCalendar( 'changeView', 'agendaDay' ); }
							}
						} );
					}

					// Handles date selection
					function handleDateRange( context, printouts ){
						var optionsElement = html.element( 'fieldset', {}, new html.Raw(
							html.element( 'legend', { }, mw.msg( 'srf-ui-common-label-daterange' ) ) +
							html.element( 'input', { 'type': 'radio', 'name': 'option', 'id': 'from', 'value': 'f' }, '' ) +
							html.element( 'label', { 'for' : 'from'}, 'From' ) +
							html.element( 'input', { 'type': 'radio', 'name': 'option', 'id': 'to', 'value': 't' }, '' ) +
							html.element( 'label', { 'for' : 'to'}, 'to' ) +
							html.element( 'span', { 'class': 'reset-link' }, 'Reset' ) + '<br />' +
							_select().build( printouts, 'printouts', 'printouts', _calendar.defaults.browser.name, 'disabled' ) +
							html.element( 'input', { 'id': 'mini-calendar-from', 'size': '8', 'readonly': 'readonly' }, '' ) +
							html.element( 'input', { 'id': 'mini-calendar-to', 'class': 'input-right', 'size': '8', 'readonly': 'readonly' }, '' )
						) );

						// Add date selection option element
						context.append( html.element( 'div', { 'class' : 'options' }, new html.Raw( optionsElement ) ) );

						// Handle events for when the printout dropdown is changed
						// This way it is known which printout property belongs to the from or to
						// option and store it to the associated input data element
						context.find( '#printouts' ).bind( 'change', function () {
							var option = context.find( 'input:radio[name=option]:checked' ).val();
							if ( option === 'f' ){
								context.find( '#mini-calendar-from' ).data( 'property', $( this ).val() );
							} else if( option === 't' ){
								context.find( '#mini-calendar-to' ).data( 'property', $( this ).val() );
							}
						} );

						// Handle from/to/reset radio button option
						context.find( 'input:radio[name=option], .reset-link' ).click( function( event ){
							var fromDate = context.find( '#mini-calendar-from' ).val(),
								toDate = context.find( '#mini-calendar-to' ).val(),
								option = $( this ).val(),
								datepickerContext = context.find( '.datepicker' );

							if ( option === 'f' ){
								datepickerContext.datepicker( "setDate", fromDate );
								datepickerContext.datepicker( "option", { "maxDate": toDate, "minDate": null } );
								// Reset dropdown value to the stored property for this option
								context.find( '#printouts' ).prop( 'disabled', false ).val( context.find( '#mini-calendar-from' ).data( 'property' ) );
							} else if( option === 't' ){
								// Set data and limit min/max display based on the option
								datepickerContext.datepicker( "setDate", toDate );
								datepickerContext.datepicker( "option", { "maxDate": null, "minDate": fromDate } );
								// Enable dropdown
								// Reset dropdown value to the stored property for this option
								context.find( '#printouts' ).prop( 'disabled', false ).val( context.find( '#mini-calendar-to' ).data( 'property' ) );
							} else {
								// Reset all values and conditions related to date selection
								data.query.ask.conditions.start = '';
								data.query.ask.conditions.end = '';
								context.find( '#mini-calendar-from' ).val( '' ).data( 'property', '' );
								context.find( '#mini-calendar-to' ).val( '' ).data( 'property', '' );
								context.find( '#printouts' ).val( '' ).prop( 'disabled', true );
								context.find( 'input:radio[name=option]' ).prop( 'checked', false );
								datepickerContext.datepicker( "option", { "maxDate": null, "minDate": null } );
								// Reset link triggers an update
								update( ( fromDate || toDate ) );
							}
						} );
					}

					// Select those prinout properties that are of type _dat
					var propertyList = _select( data ).list( ['_dat'] );

					// Call the datepicker
					datePicker( miniCalendarContext, propertyList );

					// @note 0.7.1 Experimental feature
					handleDateRange( miniCalendarContext, propertyList );
				},

				/**
				 * Add selection box for the color filtering property
				 *
				 * @since 1.9
				 */
				filterProperty: function(){
					// Filter all printout properties that are of type _wpg and _str
					// but we also have to check against the printout list to indentify
					// which of the printouts do not carry additional identifier
					// (e.g. Has event icon=icon where icon is an identifier) because those
					// are not eligible as filter properties
					var filteredPrintouts = _select( data ).list( ['_wpg', '_str'] ),
						printoutList = self.api.query.printouts.toList( data.query.ask.printouts ),
						propertyList = [];

					$.each( filteredPrintouts, function( index, property ) {
						if ( $.inArray( property, printoutList ) > -1 ) {
							propertyList.push( property );
						}
					} );

					function _elements( filteredPrintouts ){
						var filterElement = html.element( 'div', { 'class': 'filterparam' }, new html.Raw(
							html.element( 'div', { 'class' : 'parameter-section' }, 'Filter parameter' ) + // @note mw.msg
							html.element( 'input', { 'type': 'radio', 'name': 'filterType', 'id': 'legend', 'value': 'legend'}, '' ) +
							html.element( 'label', { 'for' : 'legend'}, 'Legend' ) +  // @note mw.msg
							html.element( 'input', { 'type': 'radio', 'name': 'filterType', 'id': 'filter', 'value': 'filter' }, '' ) +
							html.element( 'label', { 'for' : 'filter'}, 'Filter' ) +  // @note mw.msg
								html.element( 'span', { 'class': 'reset-link' }, 'Reset' ) + '<br />' +  // @note mw.msg
							_select( data ).build( filteredPrintouts, 'filterproperty', 'filter', self.defaults.browser.name, 'disabled' )
						) );
						parameterContext.append( filterElement );
						return parameterContext.find( '.filterparam' );
					}

					var instance = _elements( propertyList );

					// Radio button, dropdown change handling
					instance.find( '#filterproperty, #legend, #filter' ).bind( 'change', function ( event ) {
						var propertyIndex =  instance.find( '#filterproperty' ).val(),
							filterType = instance.find( 'input:radio[name=filterType]:checked' ).val();
							instance.find( '#filterproperty' ).prop( 'disabled', false );
							data.options.filterproperty = propertyList[ propertyIndex ];
							data.options.filterType = filterType;
							if ( filterType !== undefined && propertyIndex !== '' ){
								self.data.refresh( context, container, data, 'The filter settings were changed.' ); // @note mw.msg
							}
					} );

					// Handle reset option
					// Reset all values and conditions related to date selection
					instance.find( '.reset-link' ).click( function( event ){
						if( data.options.filterproperty && data.options.filterType ){
							data.options.filterproperty = '';
							data.options.filterType = '';
							instance.find( '#filterproperty' ).val( '' ).prop( 'disabled', true );
							instance.find( 'input:radio[name=filterType]' ).prop( 'checked', false );
							self.data.refresh( context, container, data, 'The filter settings were reseted.' ); // @note mw.msg
					}
					} );
				},

				/**
				 * Methods that handles/change the limit parameter display
				 *
				 * @since 1.9
				 */
				limit: function() {
					var _BASE = 'limitparam',
						limitContext = parameterContext.find( '.' + _BASE );

					// Internal methods that are not visible for as long
					// as there not called through the public method add/set
					function element(){
						var limitElement = html.element( 'div', { 'class': _BASE }, new html.Raw(
							html.element( 'div', { 'class' : 'parameter-section' }, 'Limit parameter' ) +  // @note mw.msg
							html.element( 'div', { 'class': 'label' }, 'Limit' ) +  // @note mw.msg
							html.element( 'span', { 'class': 'value' }, '' ) +
							html.element( 'span', { 'class': 'count' }, '' ) + '<br/>' +
							html.element( 'div', { 'class': 'slider' }, '' )
						) );
						paneContext.find( '.parameters > fieldset' ).append( limitElement );
						return parameterContext.find( '.' + _BASE );
					}

					function update( limitContext, limit, count ){
						limitContext.find( '.value' ).text( limit );
						if ( count !== '' ){
							limitContext.find( '.count' ).text( '[ ' + count + ' ]' );
						} else {
							limitContext.find( '.count' ).text( '' );
						}
						data.query.ask.parameters.limit = limit;
					}

					function slider( limitContext ){
						limitContext.find( '.slider' ).slider( {
							range: 'min',
							value: data.query.ask.parameters.limit,
							min: 1,
							max: 1000,
							step:50,
							slide: function( event, ui ) {
								// Always start a query with at least 1
								ui.value = ui.value > 1 ? ui.value - 1 : ui.value;
								update( limitContext, ui.value, '' );
							},
							change: function( event, ui ) {
								// Only do an update for when the slider change event was triggered
								calendar.update( context, container, data );
							}
						} );

						// Disable keyboard actions for the slider
						limitContext.find( '.slider > .ui-slider-handle' ).unbind( 'keydown' );
						update( limitContext, data.query.ask.parameters.limit, data.query.result.meta.count );
					}

					return {
						init: function(){
							slider( element() );
						},

						update: function( limit, count ){
							update( limitContext, limit, count );
						}
					};
				}
			};
		},

		/**
		 * Handles fullCalendar specific tasks
		 *
		 * @since 1.9
		 */
		fullCalendar: function( context, container, data  ){
			var self = this;

			return {
				/**
				 * Get the calendar rolling
				 *
				 * @since 1.9
				 */
				init: function(){
					var that = this;

					container.fullCalendar( {
						header: {
							right: self.defaults.isRTL ? data.options.views : 'prev,next today',
							center: 'title',
							left: self.defaults.isRTL ? 'prev,next today' : data.options.views
						},
						isRTL: self.defaults.isRTL,
						height: context.height(),
						defaultView: data.options.defaultview,
						firstDay: data.options.firstday,
						monthNames: self.messages.monthNames,
						monthNamesShort: self.messages.monthNamesShort,
						dayNames: self.messages.dayNames,
						dayNamesShort: self.messages.dayNamesShort,
						buttonText: self.messages.buttonText,
						allDayText: self.messages.allDayText,
						timeFormat: self.messages.timeFormat,
						titleFormat: self.messages.titleFormat,
						columnFormat: self.messages.columnFormat,
						theme: data.options.theme,
						editable: false,
						year: self.defaults.start.Year,
						month: self.defaults.start.Month,
						date: self.defaults.start.Day,
						eventColor: self.defaults.color,
						eventSources: [ data.events , self.defaults.holiday ],
						eventRender: function( event, element, view ) {
							that.event( event, element, view ).icon();
							that.event( event, element, view ).description();
						},
						dayClick: function( date, allDay, jsEvent ) {
							// If the day number (where available) is clicked then switch to the daily view
							if ( allDay && data.options.dayview && $( jsEvent.target ).is( 'div.fc-day-number' ) ) {
								container.fullCalendar( 'changeView', 'agendaDay'/* or 'basicDay' */).fullCalendar( 'gotoDate', date );
							}
						}
					} );
				},

				/**
				 * Collection of all procedures necessary for an update
				 *
				 * Fullcalendar internal functions to add and remove source data
				 * No incremental update, this removes all existing events and
				 * add a complete new source of objects
				 *
				 * @since 1.9
				 */
				update: function(){
					container.fullCalendar( 'removeEvents' );
					container.fullCalendar( 'addEventSource', data.events );

					// Moves the calendar to an arbitrary year/month/date depending
					// on date given
					if (  _calendar.defaults.calendarStart !== null ){
						container.fullCalendar( 'gotoDate', _calendar.defaults.calendarStart );
					}
					// Init resize
					this.resize();
				},

				/**
				 * resize
				 * Adopt the display size only in case where the pane is visible
				 *
				 * @since 1.9
				 */
				resize: function(){
					container.resize();
					if ( self.pane( context ).context().css( 'display' ) !== 'none' ){
						container.fullCalendar('option', 'height', ( self.pane( context ).context().height() - 1 ) );
					} else {
						container.fullCalendar( 'option', 'height', null );
					}
				},

				/**
				 * eventRender
				 * Triggered while an event is being rendered.
				 *
				 * @since 1.9
				 */
				event: function( event, element, view ){
					return {
						icon: function(){
							// Manage icon elements
							if ( event.eventicon ) {
								// Find image url of the icon and an instance to the event
								self.util.getImageURL( { 'title': event.eventicon },
										function( url ) { if ( url !== false ) {
											if ( element.find( '.fc-event-time' ).length ) {
												element.find( '.fc-event-time' ).before( $( '<img src=' + url + ' />' ) );
											} else {
												element.find( '.fc-event-title' ).before( $( '<img src=' + url + ' />' ) );
											}
										}
								} );
							}
						},
						description: function(){
							// Manage description elements
							if ( event.description ) {
								// Show the tooltip for the month view and render any additional description
								// into the event for all other views
								if ( element.find( '.fc-event-title' ).length && view.name !== 'month' && view.name.indexOf( 'Day' ) >= 0 ) {
									element.find( '.fc-event-title' ).after( $( '<span class="srf-fc-description">' + event.description + '</span>' ) );
								} else {
									// Tooltip
									self.tooltip.show( {
										context: element,
										content: event.description.substring( 0, event.description.substr( 0, self.defaults.descriptionLength ).lastIndexOf( " " ) ) + ' ...',
										title: mw.msg( 'smw-ui-tooltip-title-event' ),
										button: false
									} );
								}
							}
						}
					};
				}
			};
		},

		/**
		 * Returns srf.util class reference
		 *
		 * @since 1.9
		 */
		util: new srf.util(),

		/**
		 * Returns srf.api class reference
		 *
		 * @since 1.9
		 */
		api: {
			results: new srf.api.results(),
			query: new srf.api.query(),
			util: new srf.api.util()
		},

		/**
		 * Returns smw.tooltip class reference
		 *
		 * @since 1.9
		 */
		tooltip: new smw.util.tooltip()
	};

	////////////////////////// PUBLIC METHODS ////////////////////////

	/**
	 * Module for formats extensions
	 * Ensure the namespace is initialized and available
	 *
	 * @since 1.9
	 */
	srf.formats = srf.formats || {};

	/**
	 * Base constructor for objects representing a eventcalendar instance
	 * @since 1.9
	 */
	srf.formats.eventcalendar = function() {};

	/**
	 * Constructor
	 * @var Object
	 */
	srf.formats.eventcalendar.prototype = {

		/**
		 * Public method that initializes the calendar instance
		 *
		 * @since 1.9
		 * @var context
		 * @var container
		 * @var data
		 */
		init: function( context, container, data ) {

			// Hide processing spinner
			_calendar.util.spinner.hide( { context: context } );

			// Show container
			container.show();

			// Set defaults
			_calendar.defaults.set( data );

			// Init fullCalendar container
			_calendar.fullCalendar( context, container, data ).init();

			// The button method is designed to be outsourced as $.widget module
			// but this is a minor task and can be done at a later point (or someone else)

			// Add paneView button
			_calendar.button( context.find( '.fc-header-right' ), {
				'class': 'srf-ui-button-pane',
				'icon' : 'ui-icon ui-icon-bookmark',
				'title':  mw.msg( 'srf-ui-common-label-paneview' ),
				'theme': _calendar.defaults.themeUI
			} )
			.toggle( function() {
				_calendar.pane( context ).context().show();
				_calendar.fullCalendar( context, container ).resize();
			}, function() {
				_calendar.pane( context ).context().hide();
				_calendar.fullCalendar( context, container ).resize();
			} );

			// Add refresh button
			_calendar.button( context.find( '.fc-header-right' ), {
				'class': 'srf-ui-button-refresh',
				'icon' : 'ui-icon ui-icon-refresh',
				'title':  mw.msg( 'srf-ui-common-label-refresh' ),
				'theme': _calendar.defaults.themeUI
			} )
			.bind( 'click', function( event ) {
				_calendar.data.update( context, container, data );
				event.preventDefault();
			} );

			// Add paneView
			var pane = _calendar.pane( context, container, data );
			pane.init();
			pane.add( 'mini-calendar', '' );
			pane.add( 'parameters', mw.msg( 'srf-ui-common-label-parameters' ), true );

			// Add parameters
			var parameters = _calendar.parameters( context, container, data );
			parameters.datepicker();
			parameters.limit().init();

			// Add legend/filter
			if ( data.options.legend !== 'none' ){
				parameters.filterProperty();
				_calendar.legend( context, container, data ).init();
			}
		},

		/**
		 * Public method to initiate a calendar update
		 *
		 * @since 1.9
		 *
		 * @var container
		 * @var data
		 */
		update: function ( context, container, data ){
			_calendar.data.update( context, container, data );
		}
	};

	////////////////////////// IMPLEMENTATION ////////////////////////

	var calendar = new srf.formats.eventcalendar();

	$( document ).ready( function() {
		$( '.srf-eventcalendar' ).each( function() {

			// The container and data object are specified as super-local
			// object, this ensures that for this context instance any update
			// is made made available for any other local function within the same
			// instance
			var context = $( this ),
				container = context.find( '.container' ),
				data = _calendar.parse.container( container );

			// Precautionary measure to make sure that no old content is used
			if ( data.options.version === undefined || data.options.version < '0.7' ){
				_calendar.util.spinner.hide( {
					context: context
				} );
				_calendar.util.message.exception( {
					context: context.find( '.info' ),
					message: 'Please update your page content! This is required due to some internal changes.'
				} );
			}

			// Parse JS array and merge with the data array
			$.extend( data, _calendar.parse.api( data ) );

			//console.log( 'Data', data, 'Methods',_calendar );

			// Initial calendar setup
			calendar.init( context, container, data );

			// Auto update if enabled via user-preference will ensure that events
			// are displayed according to the current back-end and not used from an
			// oudated parser cache where the initial array content was stored
			if ( _calendar.defaults.autoUpdate ) {
				calendar.update( context, container, data );
			}

			// Default user preference
			if ( _calendar.defaults.paneView ) {
				_calendar.pane( context ).context().show();
				_calendar.fullCalendar( context, container ).resize();
			}

		} );
	} );
} )( jQuery, mediaWiki, semanticFormats );