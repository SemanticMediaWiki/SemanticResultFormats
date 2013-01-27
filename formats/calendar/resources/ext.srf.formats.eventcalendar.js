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
 * @release 0.7.4
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	////////////////////////// PRIVATE OBJECTS ////////////////////////

	var html = mw.html,
		profile = $.client.profile();

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
			slider: {
				max: 1000,
				step:50
			},

			/**
			 * Convenience setter for default values
			 *
			 * @since 1.9
			 * @type Object
			 */
			set: function ( data ){
				var weekDay = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
					that = {};

				// Set theme
				that.theme = data.query.ask.parameters.theme === 'vector' ? 'ui' : 'fc';

				that.defaultView = data.query.ask.parameters.defaultview.replace('day', 'Day').replace( 'week', 'Week' );
				that.view = 'month,' + ( that.defaultView.indexOf( 'Week' ) === -1 ? 'basicWeek' : that.defaultView ) + ',' + ( that.defaultView.indexOf( 'Day' ) === -1 ? 'agendaDay' : that.defaultView );

				that.firstday = $.inArray( data.query.ask.parameters.firstday, weekDay );

				// Set calendar start
				that.calendarStart = _calendar.data.startDate( data.dates ).get( data.query.ask.parameters.start );

				// Google holiday calendar url
				that.holiday = data.query.ask.parameters.gcalurl === null ? '' : data.query.ask.parameters.gcalurl;

				// Set RTL direction
				that.isRTL = $( 'html' ).attr( 'dir' ) === 'rtl' || false;

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
		 * Contains methods linked to the parsing of objects
		 *
		 * @since 1.9
		 * @type Object
		 */
		parse: {

			/**
			 * Returns a parsed json object
			 *
			 * Objects set through using mw.config.set/mw.config.get
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
							metaData = [],
							prevElement = '';

						// Subject
						if ( rowData.url === undefined ) {
							rowData.url = parameters.link === 'none' ? null : subject.fullurl;
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
										// Find those entries that don't have a specific time which
										// means there are all day events
										rowData.allDay = rowData.end.indexOf( '00:00:00' ) !== -1 || false;
									}

								// Page type properties
								} else if (  printrequests.getTypeId( property ) === '_wpg' ){
									if ( property === 'title' ){
										rowData.title = value.fulltext;
									} else if ( property === 'icon' ){
										rowData.eventicon = value.fulltext;
									} else if ( property === data.query.ask.parameters.filterProperty ) {
										rowData.filter = { 'value' : value.fulltext, 'option': ( data.query.ask.parameters.filterType === 'filter' ? true: false ) };
									} else if ( property !== '' ) {
										// Do we have an external meta data description?
										if ( printrequests.getMetaData( property ) !== null ){
											metaData.push( [ property, value.fulltext ] );
										}
										rowDesc.push( parameters.headers === 'hide' ? value.fulltext : property + ':' + value.fulltext );
									}
								} else {
									if ( property === 'title' ){
										rowData.title = value;
									} else if ( property === 'color' ) {
										rowData.color = value;
									} else if ( property === data.query.ask.parameters.filterProperty ) {
										rowData.filter = { 'value' : value, 'option': ( data.query.ask.parameters.filterType === 'filter' ? true: false ) };
									} else if ( property !== '' ) {
										// Do we have an external meta description?
										if ( printrequests.getMetaData( property ) !== null ){
											meta.push( [ property, value.fulltext ] );
										}
										// Items without fixed identifiers remain part of a description
										rowDesc.push( parameters.headers === 'hide' ? value : property + ':' + value );
									}
								}
							} );
							// Collect all descriptions
							rowData.description = rowDesc.join(',');
							rowData.metaData = metaData;
						}

						// Only care for entries that have at least a start date
						if ( rowData !== {} && $.inArray( 'start', rowData ) && rowData.start !== null ) {
							if ( $.inArray( 'filter', rowData ) && rowData.filter !== undefined ){
								var filter = rowData.filter,
									color = $.inArray( 'color', rowData ) ? rowData.color : null;
								rowData.filter = filter.value;
								if ( filter.value !== undefined && color !== undefined ){
									// Collect the filter and the assigned color, colors are stored as
									// array so that if a filter has assigned different colors are
									// stored together
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

				// Transform the printRequests/property list into a key accessible object
				var printrequests = _calendar.api.results.printrequests();
				var list = printrequests.toArray( data.query.result.printrequests );

				// Parse and return results
				return getResults( data.query.ask.parameters, printrequests, data.query.result.results );
			}
		},

		/**
		 * Contains methods related to the data object literal
		 *
		 * @since 1.9
		 * @type Object
		 */
		data: {

			startDate: function( dates ){
				var self = this;

				return {

					/**
					 * Return min/max values of an object array
					 * @return object
					 */
					minmax: function(){
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
					 * Returns start date
					 *
					 * @since  1.9
					 * @param type
					 * @return Date
					 */
					get: function ( type ){
						var values = this.minmax(),
							date = type === '' ? null : type === 'earliest' ? values.min : type === 'latest' ? values.max : null;
							return date !== null ? _calendar.api.results.dataValues.time.parseDate( date ) : new Date();
					}
				}
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

				// @todo Check hash from current data object with the newly
				// arrived result hash and bail-out in case the hash match each other

				// Update defaults
				_calendar.defaults.set( data );

				// Legend update (widget)
				context.calendarlegend( 'option', 'list', {
					'type': data.query.ask.parameters.filterType,
					'list': data.legend
				} );

				// Limit/count display update (widget)
				context.find( '.parameters > fieldset' ).calendarparameters(
					'option', 'limit', {
						'limit': data.query.ask.parameters.limit,
						'count': data.query.result.meta.count
					} ) ;

				_calendar.fullCalendar( context, container, data ).update();

				if ( message ){
					_calendar.util.notification.create ( {
						content: message
					} );
				}
			},

			/**
			 * External update via Ajax
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
				// Instead of sending the whole parameter block to the api, we eliminate
				// those parameters from the query string that do not influence the
				// results (only use limit, offset)
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

				// Fetch data via srf.api/ajax
				_calendar.api.query.fetch( query, function( status, api ) {
					if ( status ) {

						// Reassign api query data into the data array
						$.extend( data.query.result, api.query );

						// Refresh all internal objects
						_calendar.data.refresh( context, container, data );

						container.trigger( 'srf.eventcalendar.updateAfterParse' );

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
			}
		},

		/**
		 * Handles fullCalendar tasks
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
							right: 'prev,next today',
							center: 'title',
							left: self.defaults.view
						},
						isRTL: self.defaults.isRTL,
						height: context.height(),
						defaultView: self.defaults.defaultView,
						firstDay: self.defaults.firstday,
						monthNames: self.messages.monthNames,
						monthNamesShort: self.messages.monthNamesShort,
						dayNames: self.messages.dayNames,
						dayNamesShort: self.messages.dayNamesShort,
						buttonText: self.messages.buttonText,
						allDayText: self.messages.allDayText,
						timeFormat: self.messages.timeFormat,
						titleFormat: self.messages.titleFormat,
						columnFormat: self.messages.columnFormat,
						theme: self.defaults.theme === 'ui',
						editable: false,
						year: self.defaults.calendarStart.getFullYear(),
						month: self.defaults.calendarStart.getMonth(),
						date: self.defaults.calendarStart.getDate(),
						eventColor: self.defaults.color,
						eventSources: [ data.events , self.defaults.holiday ],
						eventRender: function( event, element, view ) {
							that.event( event, element, view ).icon();
							that.event( event, element, view ).description();

							// Custom event hook
							container.trigger( 'srf.eventcalendar.eventRender', { event: event, element: element, data: data } );
						},
						dayClick: function( date, allDay, jsEvent ) {
							// If the day number (where available) is clicked then switch to the daily view
							if ( allDay && data.query.ask.parameters.dayview && $( jsEvent.target ).is( 'div.fc-day-number' ) ) {
								container.fullCalendar( 'changeView', 'agendaDay'/* or 'basicDay' */).fullCalendar( 'gotoDate', date );
							}
						}
					} );
				},

				/**
				 * Collection of all procedures necessary for an fc update
				 *
				 * Remove and add source data at once, no incremental update,
				 * All existing events are replaced with new event data
				 *
				 * @since 1.9
				 */
				update: function(){
					container.fullCalendar( 'removeEvents' );
					container.fullCalendar( 'addEventSource', data.events );

					// Moves the calendar to an arbitrary year/month/date
					if ( _calendar.defaults.calendarStart !== null ){
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
					if ( context.find( '.info' ).calendarpane( 'context' ).css( 'display' ) !== 'none' ){
						container.fullCalendar('option', 'height', ( context.find( '.info' ).calendarpane( 'context' ).height() - 1 ) );
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
									element.find( '.fc-event-title' ).after( html.element( 'span', { 'class': 'srf-fc-description', 'property': 'v:description' }, event.description ) );
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

			// Hide loading spinner
			context.find( '.smw-spinner' ).hide();

			// Show container
			container.css( { 'display' : 'block' , overflow: 'hidden' } );

			// Set defaults
			_calendar.defaults.set( data );

			// Init fullCalendar container
			_calendar.fullCalendar( context, container, data ).init();

			// Add portlet sections using the calendarpane $.widget
			var pane = context.find( '.info' );
			pane.calendarpane( {
				'show': _calendar.defaults.paneView
			} );

			// The legend portlet is managed by the srf.calendarlegend $.widget

			// Add buttons using the calendarbutton $.widget
			// Add paneView button
			var header = context.find( '.fc-header-right' );
			header.calendarbutton( {
				'class': 'pane',
				icon : 'ui-icon ui-icon-bookmark',
				title:  mw.msg( 'srf-ui-common-label-paneview' ),
				theme: _calendar.defaults.theme
			} )
			.on( 'click', '.srf-calendarbutton-pane' , function( event ) {
				pane.calendarpane( 'toggle' );
				_calendar.fullCalendar( context, container ).resize();
				event.preventDefault();
			} );

			// Add refresh button
			header.calendarbutton( {
				'class': 'refresh',
				icon : 'ui-icon ui-icon-refresh',
				title:  mw.msg( 'srf-ui-common-label-refresh' ),
				theme: _calendar.defaults.theme
			} )
			.on( 'click', '.srf-calendarbutton-refresh', function( event ) {
				_calendar.data.update( context, container, data );
				event.preventDefault();
			} );

			// Add parameters using the calendarparameters $.widget

			// Get all date properties from the api/results
			var datePropertyList = _calendar.api.query.printouts.search.type(
				data.query.ask.printouts,
				data.query.result.printrequests,
				['_dat'] );

			// Reassign original condition
			var condition = {};
			condition.description = data.query.ask.conditions;

			// Datepicker and date selection
			var datepicker = pane.calendarpane( 'portlet', {
				'class'  : 'mini-calendar',
				'title'  : '',
				'fieldset': false
			} ).calendarparameters();

			datepicker.calendarparameters( 'dateSelection', {
				list      : datePropertyList,
				browser   : profile.name,
				dateFormat: _calendar.defaults.dateFormat,
				gotoDate: function( date ) {
					container.fullCalendar( 'gotoDate', date );
				},
				onReset: function() {
					condition['start'] = '';
					condition['end'] = '';
					data.query.ask.conditions = condition;
					_calendar.data.update( context, container, data );
				},
				onSelect: function( ui ) {
					// Reassign information received from the dateSelection portlet
					if ( ui.fromProperty && ui.fromDate ){
						condition['start'] = _calendar.api.query.conditions.build( ui.fromProperty, ui.fromDate, '::>' );
					} else if( ui.toProperty && ui.toDate ){
						condition['end'] = _calendar.api.query.conditions.build( ui.toProperty, ui.toDate, '::<' );
					}
					data.query.ask.conditions = condition;
					if ( condition['start'] && condition['end'] ){
						// Do an update via Ajax when conditions are met (from/to date)
						_calendar.data.update( context, container, data );
					}
				}
			} );

			// Init srf.calendarparameters widget
			var param = pane.calendarpane( 'portlet', {
				'class'  : 'parameters',
				'title'  : mw.msg( 'srf-ui-common-label-parameters' ),
				'fieldset': true
			} ).find( 'fieldset' ).calendarparameters();

			// Start parameter
			param.calendarparameters( 'eventStart', {
				type: data.query.ask.parameters.start,
				change: function( type ){
 					container.fullCalendar( 'gotoDate', _calendar.data.startDate( data.dates ).get( type ) );
					data.query.ask.parameters.start = type;
				},
				reset: function(){
					data.query.ask.parameters.start = '';
					container.fullCalendar( 'gotoDate', new Date() );
				}
			} );

			// Limit parameter
			param.calendarparameters( 'limit', {
				limit : data.query.ask.parameters.limit,
				count : data.query.result.meta.count,
				max   : _calendar.defaults.slider.max,
				step  : _calendar.defaults.slider.step,
				change: function( event, value ) {
					data.query.ask.parameters.limit = value ;
					_calendar.data.update( context, container, data );
				}
			} );

			// Legend filter parameter
			var filterList = _calendar.api.query.printouts.search.type(
				data.query.ask.printouts,
				data.query.result.printrequests,
				['_wpg', '_str'] );

			param.calendarparameters( 'colorFilter', {
				list    : filterList,
				browser : profile.name,
				onChange: function( event, ui ) {
					data.query.ask.parameters.filterProperty = filterList[ ui.propertyIndex ];
					data.query.ask.parameters.filterType = ui.filterType;
					if ( ui.filterType !== undefined && ui.propertyIndex !== '' ){
						_calendar.data.refresh( context, container, data, 'The filter settings were changed.' ); // @note mw.msg
					}
				},
				onReset: function( event, ui ) {
					data.query.ask.parameters.filterProperty = '';
					data.query.ask.parameters.filterType = '';
					_calendar.data.refresh( context, container, data, 'The filter settings were changed.' ); // @note mw.msg
				}
			} );

			// Show colorFilter parameters only where a printrequest identifier
			// 'color' has been found and where parameter=legend is not none
			param.calendarparameters(
				'option', 'colorFilter', {
					'hide' : !( _calendar.api.query.printouts.search.identifier( data.query.ask.printouts, 'color' ) ) || data.query.ask.parameters.legend === 'none'
				} );

			// Legend
			// Filters are checked by default, find filter elements in the original
			// data source and add newly selected elements
			context.calendarlegend( {
				position: data.query.ask.parameters.legend,
				wrapper: data.query.ask.parameters.legend === 'pane' ? 'info' : 'container',
				list: data.legend,
				defaultColor: _calendar.defaults.color,
				theme : _calendar.defaults.theme,
				onFilter: function( event, status, filter ) {
					if ( status ){
						var source = $.map( data.events, function( event ) {
							if ( event.filter === filter ){
								return event;
							}
						} );

						// Add selected source data
						container.fullCalendar( 'addEventSource', source );

					} else {
						// Unchecked status therefore remove elemtents with the specified filter
						container.fullCalendar( 'removeEvents', function( event ) {
							return event.filter === filter;
						} );
					}
				}
			} );

			// Only IE: We need to set the width explicitly
			if ( profile.name === 'msie' ){
				var paneWidth = context.find( '.srf-calendarpane' ).width();
				context.find( '.srf-calendarpane' ).css( { 'width' :  paneWidth } );
			}

			// Resize for a better look and feel
			_calendar.fullCalendar( context, container ).resize();
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
		},

		/**
		 * Test interface
		 *
		 * Enable qunit to access some internal methods / objects and make
		 * them accessible via the prototype
		 *
		 * @since 1.9
		 */
		test: {
			_parse: _calendar.parse,
			_startDate: function( dates ) { return _calendar.data.startDate( dates ) }
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
			if ( ( data.version === undefined || data.version < '0.7' ) ||
				( profile.name === 'msie' && profile.versionNumber < 9 ) ){
					context.find( '.smw-spinner' ).hide();
				_calendar.util.message.exception( {
					context: context.find( '.info' ),
					message: ( profile.name === 'msie' && profile.versionNumber < 9 ) ? 'Your IE (' + profile.versionNumber + ') version is not supported!' : 'Please update your page content! This is required due to some internal changes.'
				} );
			}

			// Parse JS array and merge with the data array
			$.extend( data, _calendar.parse.api( data ) );

			if ( data.events.length > 0 ){
				// Initial calendar setup
				calendar.init( context, container, data );
			} else {
				context.find( '.smw-spinner' ).hide();
				_calendar.util.message.set( {
					context: context.find( '.info' ),
					message: 'No results'
				} );
			}

			// Auto update if enabled via user-preference will ensure that events
			// are properly updated and not used from an outdated parser cache
			// where the initial array content was stored
			if ( _calendar.defaults.autoUpdate ) {
				calendar.update( context, container, data );
			}

			//console.log( 'Data', data, 'Objects', _calendar );

		} );
	} );
} )( jQuery, mediaWiki, semanticFormats );