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
 * @version 0.8
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
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
			descriptionLength: 500,
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
				var parameters = data.query.ask.parameters;
				that.theme = parameters.theme === 'vector' ? 'ui' : 'fc';
				that.themeSystem = parameters.theme === 'vector' ? 'jquery-ui' : 'standard';

				that.defaultView = parameters.defaultview
					.replace( 'day', 'Day')
					.replace( 'week', 'Week' )
					.replace( 'tmonth', 'tMonth' );

				that.view = parameters.views
					.replace( /day/g, 'Day')
					.replace( /week/g, 'Week' )
					.replace( /tmonth/g, 'tMonth' );

				that.firstday = $.inArray( parameters.firstday, weekDay );

				// Set calendar start
				that.calendarStart = _calendar.data.startDate( data.dates ).get( parameters.start );

				// Google holiday calendar url
				that.holiday = parameters.gcalurl === null ? '' : parameters.gcalurl;

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
		 * Returns container data
		 *
		 * @private
		 * @return {object}
		 */
		getData: function( container ) {
			return mw.config.get( this.getID( container ) );
		},

		/**
		 * Returns truncated version of string for tooltip description of month view
		 *
		 * @private
		 * @param str string to be truncated
		 * @param maxChars max number of characters to be returned (excluding ' ...')
		 * @return {string}
		 */
		getTruncatedSentence: function( str, maxChars ) {
			var truncated = str.substr( 0, maxChars );
			if ( str.length > maxChars ) {
				var truncatedEnd = truncated.lastIndexOf( " " );
				if( truncatedEnd != -1 ) {
					truncated = str.substring( 0, truncatedEnd );
				}
				return truncated + ' ...';
			}else{
				return truncated;
			}
		},

		/**
		 * Contains methods linked to the parsing of objects
		 *
		 * @since 1.9
		 * @type Object
		 */
		parse: {

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

				// Transform results into the calendar specific array format
				function getResults( parameters, results ){

					var events = [],
						dates = [],
						legend = {};

					$.each( results, function( subjectName, subject ) {
						var rowData = {},
							rowDesc = [],
							metaData = [],
							prevElement = '';

						// Subject
						if ( rowData.url === undefined && subject instanceof smw.dataItem.wikiPage ) {
							rowData.url = parameters.link === 'none' ? null : subject.getUri();
							rowData.title = subject.getHtml();
						}

						if ( $.inArray( 'printouts', subject ) ) {
							$.each ( subject.printouts, function( property, values ) {

								$.map ( values, function( value ) {

									// Time type properties
									if ( value instanceof smw.dataItem.time && value.getDate() !== undefined ) {
										if ( rowData.start === undefined ){
											rowData.start = value.getDate().toISOString();
											dates.push( value.getMwTimestamp() );
											rowData.allDay = true;
										} else {
											var dataDate = value.getDate();
											// value.precision is a bitmask indicating what parts of the date and time exist
											// 1: Year, 2: Month, 4: Day, 8: Time
											// see https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/res/smw/data/ext.smw.dataItem.time.js
											if(data.query.ask.parameters.includeend && ( value.precision & 8 ) == 0 ) {
												dataDate.setDate(dataDate.getDate() + 1);
											}
											rowData.end = dataDate.toISOString();
											dates.push( value.getMwTimestamp() );
											var test = value.getISO8601Date();
											rowData.allDay = test.indexOf( '00:00:00' ) !== -1 || false;
										}
									// Page type properties
									} else if ( value instanceof smw.dataItem.wikiPage ) {

										if ( property === 'title' && value.getName() !== undefined ) {
											rowData.title = value.getFullText();
										} else if ( property === 'icon' ){
											rowData.eventicon = value.getFullText();
										} else if ( property === data.query.ask.parameters.filterProperty ) {
											rowData.filter = {
												'value' : value.getFullText(),
												'option': ( data.query.ask.parameters.filterType === 'filter' ? true: false )
											};
										} else if ( property !== '' ) {
											rowDesc.push( parameters.headers === 'hide' ? value.getFullText() : '<div class="fc-event-popup-row"><span class="fc-event-popup-label">' + property + '</span>: ' + value.getFullText() + '</div>');
										}

									} else if ( $.type( value ) === 'object' ) {
										if ( property === 'title' ){
											rowData.title = value.getValue();
										} else if ( property === 'color' ) {
											rowData.color = value.getValue();
										} else if ( property === 'iconclass' ) {
											rowData.eventIconClass = value.getValue();
										} else if ( property === data.query.ask.parameters.filterProperty ) {
											rowData.filter = {
												'value' : value.getValue(),
												'option': ( data.query.ask.parameters.filterType === 'filter' ? true: false )
											};
										} else if ( property !== '' ) {
											// Items without fixed identifiers remain part of a description
											rowDesc.push( parameters.headers === 'hide' ? value.getValue() : '<div class="fc-event-popup-row"><span class="fc-event-popup-label">' + property + '</span>: ' + value.getValue() + '</div>' );
										}
									}

								} );
							} );
							// Collect all descriptions
							rowData.description = rowDesc.join('');
						}

						// Only care for entries that have at least a start date
						if ( rowData !== {} &&
							$.inArray( 'start', rowData ) &&
							rowData.start !== null &&
							rowData.start !== undefined ) {

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
						}
					} );

					return { 'events': events, 'legend': legend, 'dates': dates };
				}

				// Parse and return results
				return getResults( data.query.ask.parameters, data.query.result.results );
			}
		},

		/**
		 * Contains methods related to the data object literal
		 *
		 * @since 1.9
		 * @type Object
		 */
		data: {

			startDate: function( dates ) {
				var self = this;

				return {

					/**
					 * Return min/max values of an object array
					 * @return object
					 */
					minmax: function(){
						var min = 0,
							max = 0;
							$.map ( dates, function( value ) {
								if ( value !== '' ) {
									// 0 would be always (in the context given) min
									// but this is not what is of interest here therefore
									// in case min is 0 set it to the next value known
									min = parseInt( value, 10 ) < parseInt ( min, 10 ) ? value : min === 0 ? value : min;
									max = parseInt( value, 10 ) > parseInt ( max, 10 ) ? value : max;
								}
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
				srf.log( 'Parse: ' + ( new Date().getTime() - startDate.getTime() ) + ' ms' );

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
				day: mw.msg( 'srf-ui-eventcalendar-label-day' ),
				listMonth: mw.msg( 'srf-ui-eventcalendar-label-listmonth' ),
				listWeek: mw.msg( 'srf-ui-eventcalendar-label-listweek' ),
				listDay: mw.msg( 'srf-ui-eventcalendar-label-listday' )
			},
			allDayText : mw.msg( 'srf-ui-eventcalendar-label-allday' ),
			timeFormat : mw.msg( 'srf-ui-eventcalendar-format-time' ),
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
			clickPopup: {
				popup: mw.msg( 'srf-ui-eventcalendar-click-popup' )
			}
		},
		/**
		 * Handles redirect to a clicktarget URL.
		 */
		onDayClick: function( date, data, clickPopup ){
			var clicktarget = data.query.ask.parameters.clicktarget;

			// Moment.js
			if ( typeof date.getUTCHours !== 'function' ) {
				date = new Date( date.toDate() );
			};

			if( clicktarget !== 'none' ){
				var h = date.getUTCHours() + 1;
				var m = date.getUTCMinutes();
				var s = date.getUTCSeconds();
				var hms;

				if( h == 24 ){
					// avoid switch to next day
					hms  = "T"+ "13" + ":" + m + ":" + s;
				} else {
					hms  = "T"+ h + ":" + m + ":" + s;
				}

				clicktarget = clicktarget.replace( /%clickyear%/g, date.getFullYear() )
										 .replace( /%clickmonth%/g, date.getMonth() + 1 )
										 .replace( /%clickday%/g, date.getDate() )
										 .replace( /%clicktime%/g, hms );

				var wgArticlePath = mw.config.get( 'wgArticlePath' ).replace( '$1', '' ).trim();
				var wgServer = mw.config.get( 'wgServer' );

				var clicktargetURL = wgServer + wgArticlePath + clicktarget;
				/* DONE: i18n */
				var r = confirm( clickPopup.popup );
				if ( r == true ){
					window.open( clicktargetURL, '_self' );
				}
		   }

		},
		/**
		 * Handles fullCalendar tasks
		 *
		 * @since 1.9
		 */
		fullCalendar: function( context, container, data  ){
			var self = this;
			var holidays = [];
			if ( typeof( self.defaults.holiday ) != 'undefined' ) {
				holidays = self.defaults.holiday;
			}
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
						isRTL: context.attr( 'dir' ) === 'rtl' || false,
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
						views: {
							basic: {
								titleFormat: self.messages.titleFormat.day,
								columnHeaderFormat: self.messages.columnFormat.day
							},
							month: {
								titleFormat: self.messages.titleFormat.month,
								columnHeaderFormat: self.messages.columnFormat.month
							},
							agendaWeek: {
								titleFormat: self.messages.titleFormat.week,
								columnHeaderFormat: self.messages.columnFormat.week
							},
							week: {
								titleFormat: self.messages.titleFormat.week,
								columnHeaderFormat: self.messages.columnFormat.week
							},
							day: {
								titleFormat: self.messages.titleFormat.day,
								columnHeaderFormat: self.messages.columnFormat.day
							},
							agendaDay: {
								titleFormat: self.messages.titleFormat.day,
								columnHeaderFormat: self.messages.columnFormat.day
							},
							agenda: {
								titleFormat: self.messages.titleFormat.agenda,
								columnHeaderFormat: self.messages.columnFormat.agenda
							}
						},
						clickPopup: self.messages.clickPopup,
						themeSystem: self.defaults.themeSystem,
						editable: false,
						defaultDate: self.defaults.calendarStart.toISOString(),
						eventColor: self.defaults.color,
						eventSources: [ {'events': data.events, 'holidays' : holidays } ],
						eventRender: function( event, element, view ) {
							that.event( event, element, view ).icon();
							that.event( event, element, view ).description();

							// Custom event hook
							container.trigger( 'srf.eventcalendar.eventRender', { event: event, element: element, data: data } );
						},
						navLinks: data.query.ask.parameters.dayview,
						dayClick: function( date, allDay, jsEvent ) {
							// If the day number (where available) is clicked then switch to the daily view
							if ( allDay && data.query.ask.parameters.dayview && $( jsEvent.target ).is( 'div.fc-day-number' ) ) {
								container.fullCalendar( 'changeView', 'agendaDay'/* or 'basicDay' */).fullCalendar( 'gotoDate', date );
							} else {
								// redirect to a clicktarget URL if defined.
								 self.onDayClick( date, data, self.messages.clickPopup );
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

					var offset = mw.config.get( 'wgCanonicalNamespace' ) === 'Special' ? 8 : 1;

					if ( context.find( '.srf-top' ).calendarpane( 'context' ).css( 'display' ) !== 'none' ){
						var height = context.find( '.srf-top' ).calendarpane( 'context' ).height() - offset;
						container.fullCalendar('option', 'height', Math.round( height ) );
						context.height( ( height > container.height() ? height : container.height() ) + offset );
					} else if( context.data( 'height' ) !== null ) {
						container.fullCalendar( 'option', 'height', Math.round( context.data( 'height' ) ) );
						context.height( ( context.data( 'height' ) > container.height() ? context.data( 'height' ) : container.height() ) );
					} else {
						container.fullCalendar( 'option', 'height', Math.round( context.height() ) );
					}

					container.resize();
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
										content: _calendar.getTruncatedSentence( event.description, self.defaults.descriptionLength ),
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
	 * Class that contains the Eventcalendar JavaScript result printer
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 * @extends srf.formats
	 */
	srf.formats.eventcalendar = function() {};

	/* Public methods */

	srf.formats.eventcalendar.prototype = {

		/**
		 * Default settings
		 *
		 * @since  1.9
		 *
		 * @property
		 */
		defaults: {
			autoUpdate: mw.user.options.get( 'srf-prefs-eventcalendar-options-update-default' ),
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

			// Hide loading spinner
			context.find( '.srf-loading-dots' ).hide();

			// Show container
			container.css( { 'display' : 'block' , overflow: 'hidden' } );

			// Set defaults
			_calendar.defaults.set( data );

			// Init fullCalendar container
			_calendar.fullCalendar( context, container, data ).init();

			// Add portlet sections using the calendarpane $.widget
			var pane = context.find( '.srf-top' );
			pane.calendarpane( {
				'show': _calendar.defaults.paneView
			} );

			// The legend portlet is managed by the srf.calendarlegend $.widget

			// Add buttons using the calendarbutton $.widget
			// Add paneView button
			context.find( '.fc-right' ).append( '<div class="fc-button-group srf-button-group" ></div>' );
			var group = context.find( '.srf-button-group' );

			group.calendarbutton( {
				'class': 'pane',
				left: true,
				right: false,
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
			group.calendarbutton( {
				'class': 'refresh',
				left: false,
				right: true,
				icon : 'ui-icon ui-icon-refresh',
				title:  mw.msg( 'srf-ui-common-label-refresh' ),
				theme: _calendar.defaults.theme
			} )
			.on( 'click', '.srf-calendarbutton-refresh', function( event ) {
				calendar.update( context, container, data );
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
					calendar.update( context, container, data );
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
						calendar.update( context, container, data );
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
					_calendar.fullCalendar( context, container ).resize();
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
					calendar.update( context, container, data );
				}
			} );

			// Legend filter parameter
			var filterList = _calendar.api.query.printouts.search.type(
				data.query.ask.printouts,
				data.query.result.printrequests,
				['_wpg', '_str', '_txt'] );

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
				wrapper: data.query.ask.parameters.legend === 'pane' ? 'srf-top' : 'srf-container',
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

			// Attach click event on the fc-buttons to ensure that
			// a resize is being carried out each time the view is changed
			container.find( '.fc-button-group' ).click( function() {
				_calendar.fullCalendar( context, container ).resize();
			} );
		},

		/**
		 * Public method to initiate a calendar update
		 *
		 * @since 1.9
		 *
		 * @param {Array} context
		 * @param {Array} container
		 * @param {Array} data
		 * @return {void}
		 */
		update: function ( context, container, data ){
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

			if ( data.query.ask.parameters.hasOwnProperty( 'sort' ) ) {
				parameters.sort = data.query.ask.parameters.sort;
			};

			if ( data.query.ask.parameters.hasOwnProperty( 'order' ) ) {
				parameters.order = data.query.ask.parameters.order;
			};

			// Stringify the query
			var query = new smw.query( printouts, parameters, conditions ).toString();

			var startDate = new Date();
			srf.log( 'Query: ' + query );

			// Fetch data via Ajax/SMWAPI
			smwApi.fetch( query )
			.done( function ( result ) {

				srf.log( 'Api : ' + ( new Date().getTime() - startDate.getTime() ) + ' ms ' + '( ' + result.query.meta.count + ' object )' );

				// Reassign api query data into the data array
				$.extend( data.query.result, result.query );

				// Refresh all internal objects
				_calendar.data.refresh( context, container, data );

				container.trigger( 'srf.eventcalendar.updateAfterParse' );

				context.unblock( {
					onUnblock: function(){ util.notification.create ( {
						content: mw.msg( 'srf-ui-eventcalendar-label-update-success' )
						} );
					}
				} );
			} )
			.fail( function ( error ) {
				context.unblock( {
					onUnblock: function(){ util.notification.create ( {
						content: mw.msg( 'srf-ui-eventcalendar-label-update-error' ),
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
			_parse: _calendar.parse,
			_getData: function( container ) { return smwApi.parse( _calendar.getData( container ) ) },
			_startDate: function( dates ) { return _calendar.data.startDate( dates ) }
		}
	};

	/**
	 * eventCalendar implementation
	 *
	 * @ignore
	 */
	var calendar = new srf.formats.eventcalendar();

	$( document ).ready( function() {
		$( '.srf-eventcalendar' ).each( function() {

			// The container and data object are specified as super-local
			// object, this ensures that for this context instance any update
			// is made made available for any other local function within the same
			// instance
			var context = $( this ),
				container = context.find( '.srf-container' ),
				data = smwApi.parse( _calendar.getData( container ) );

			context.addClass( context.data( 'external-class' ) );

			// An external class that sets a height less 350px is invalid as it
			// causes inline distortion therefore set min height
			if ( context.data( 'external-class' ) !== '' && context.height() < 350 ) {
				context.removeClass( context.data( 'external-class' ) );
				context.height( 350 );
			}

			// Whether a fixed height has been defined
			if ( context.data( 'external-class' ) === '' ) {
				context.data( 'height', 600 );
			};

			if ( data.query.ask.parameters.defaultview.indexOf( 'list' ) == 0 && context.height() < 350 ) {
				context.data( 'height', 350 );
			};

			// Add bottom element to clear preceding elements and avoid display clutter
			context.after( html.element( 'div', { 'class': 'srf-eventcalendar-clear srf-bottom', 'style': 'clear:both' } ) );

			// Adopt directionality which ensures that all elements within this context
			// are appropriately displayed
			context.prop( 'dir', $( 'html' ).attr( 'dir' ) );

			// Precautionary measure to make sure that no old content is used
			if ( ( data === null || data.version === undefined || data.version < '0.8' ) ||
				( profile.name === 'msie' && profile.versionNumber < 9 ) ){
					context.find( '.srf-loading-dots' ).hide();
				_calendar.util.message.exception( {
					context: context.find( '.srf-top' ),
					message: ( profile.name === 'msie' && profile.versionNumber < 9 ) ? 'Your IE (' + profile.versionNumber + ') version is not supported!' : 'Please update your page content! This is required due to some internal changes.'
				} );
			}

			// Parse JS array and merge with the data array
			$.extend( data, _calendar.parse.api( data ) );

			if ( data.events.length > 0 ) {
				// Initial calendar setup

				// Seen some race-conditions in 1.22 ResourceLoader therefore
				// make sure that CSS/JS dependencies are "really" loaded before
				// continuing
				mw.loader.using( 'ext.srf.eventcalendar', function(){
					calendar.init( context, container, data );

					// Auto update if enabled via user-preference will ensure that events
					// are properly updated and not used from an outdated parser cache
					// where the initial array content was stored
					if ( calendar.defaults.autoUpdate ) {
						calendar.update( context, container, data );
					}
				} );

			} else {
				context.find( '.srf-loading-dots' ).hide();
				_calendar.util.message.set( {
					context: context.find( '.srf-top' ),
					message: 'No results'
				} );
			}

			// Allow to fetch the `srf.eventcalendar.fullCalendarRefresh` and trigger a click
			// event to restore the fullCalendar in case the instance was part of tab (hidden
			// during initialization)
			$( document ).on( 'srf.eventcalendar.fullCalendarRefresh', function( event ) {
				context.find( '.srf-calendarbutton-refresh' ).trigger( 'click' );
			} );

			// console.log( 'Data', data, 'Objects', _calendar );

		} );
	} );
} )( jQuery, mediaWiki, semanticFormats );
