/**
 * SRF JavaScript for event calendar module which uses the fullcalendar library
 * @see http://arshaw.com/fullcalendar/docs/
 * @see http://www.semantic-mediawiki.org/wiki/Help:Eventcalendar_format
 *
 * @since 1.8
 * @release 0.4.1
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
/* global mw:true, smw:true, mediaWiki:true, semanticMediawiki:true, semanticFormats:true */
( function( $, mw, srf ) {
	'use strict';

	////////////////////////// PRIVATE METHODS ////////////////////////

	/**
	 * Utility functions
	 *
	 * @since 1.9
	 * @type Object
	 */
	var _calendar = {
		/**
		 * Returns ID
		 *
		 * @since 1.9
		 */
		getDefault: {
			color: '#48a0d5'
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
		 * Returns a parsed json string back into its object structure
		 *
		 * @since 1.9
		 * @type Object
		 */
		getData: function( container ) {
			var json = mw.config.get( _calendar.getID( container ) );
			return typeof json === 'string' ? jQuery.parseJSON( json ) : json;
		},

		/**
		 * Returns calendar start
		 * Splits the start date (incoming format is ISO8601 e.g. 2012-09-17T09:49Z)
		 *
		 * @since 1.9
		 * @type Object
		 */
		getCalendarStart: function( data ) {
			var calendarStart = data.options.calendarstart !== null ? data.options.calendarstart.split( '-', 3 ) : null;
			return {
				// Use undefined in case where the start is not specified
				Year: calendarStart !== null ? calendarStart[0] : undefined,
				// The value is 0-based, meaning January=0, February=1, etc.
				Month: calendarStart !== null ? calendarStart[1] - 1 : undefined,
				// ...17T09:49Z only use the first two
				Day: calendarStart !== null ? calendarStart[2].substring( 0, 2 ) : undefined
			};
		},

		/**
		 * Returns Google holiday calendar url
		 *
		 * @since 1.9
		 * @type Object
		 */
		getHolidayCalendar: function( data ) {
			return data.options.gcalurl === null ? '' : data.options.gcalurl;
		},

		/**
		 * Returns RTL direction
		 *
		 * @since 1.9
		 * @type Object
		 */
		isRTL: function(){
			return $( 'html' ).attr( 'dir' ) === 'rtl' ? true : false;
		},

		/**
		 * Returns legend, filter options as html elements
		 *
		 * @since 1.9
		 * @type Object
		 */
		getLegend: function( legend, listClass ) {
			var elements = [];
			$.each(	legend, function( key, value ) {

				elements.push(
					( value.filter ? '<input type="checkbox" checked="checked" name="' + key + '"/>' : '' ) +
					'<div class="srf-ui-legendSquare" style="background-color:' +	( value.color === null ? _calendar.getDefault.color : value.color ) + '";>' +
					'</div><span class="srf-ui-legendLabel">' + key + '</span>'
				);
			} );
			//	elements.push( '<input type="checkbox" class="checkall"/><span class="srf-ui-legendLabel">' + 'checkall' + '</span>' );

			return '<div class="' + listClass  + '"><ul><li>'+ elements.join('</li><li>') + '</li></ul></div>';
		},

		/**
		 * Internationalization (i18n) support
		 *
		 * @see http://arshaw.com/fullcalendar/docs/text/timeFormat/
		 * @see http://arshaw.com/fullcalendar/docs/text/titleFormat
		 * @see http://arshaw.com/fullcalendar/docs/agenda/axisFormat/
		 * @see http://arshaw.com/fullcalendar/docs/text/columnFormat/
		 *
		 * @since 1.8
		 */
		getI18n: {
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
		 * Returns srf.util class reference
		 *
		 * @since 1.9
		 */
		util: new srf.util(),

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
	srf.formats.eventcalendar = function( settings ) {
		$.extend( this, settings );
		this.init();
	};

	srf.formats.eventcalendar.prototype = {
		init: function() {
			return this.context.each( function() {
				var container = $( this ).find( '.container' ),
					data = _calendar.getData( container ),
					isRTL = _calendar.isRTL(),
					calendarStart = _calendar.getCalendarStart( data ),
					gcalholiday = _calendar.getHolidayCalendar( data );

				// Hide processing note
				_calendar.util.spinner.hide( { context: $( this ) } );

				// Show container
				container.show();

				// Init fullCalendar container
				container.fullCalendar( {
					header: {
						right: isRTL ? data.options.views : 'prev,next today',
						center: 'title',
						left: isRTL ? 'prev,next today' : data.options.views
					},
					isRTL: isRTL,
					height: $( this ).height(),
					defaultView: data.options.defaultview,
					firstDay: data.options.firstday,
					monthNames: _calendar.getI18n.monthNames,
					monthNamesShort: _calendar.getI18n.monthNamesShort,
					dayNames: _calendar.getI18n.dayNames,
					dayNamesShort: _calendar.getI18n.dayNamesShort,
					buttonText: _calendar.getI18n.buttonText,
					allDayText: _calendar.getI18n.allDayText,
					timeFormat: _calendar.getI18n.timeFormat,
					titleFormat: _calendar.getI18n.titleFormat,
					columnFormat: _calendar.getI18n.columnFormat,
					theme: data.options.theme,
					editable: false,
					year: calendarStart.Year,
					month: calendarStart.Month,
					date: calendarStart.Day,
					eventColor: _calendar.getDefault.color,
					eventSources: [ data.events, gcalholiday ],
					eventRender: function( event, element, view ) {
						// Manage icon
						if ( event.eventicon ) {
							// Find image url of the icon and an instance to the event
							_calendar.util.getImageURL( { 'title': event.eventicon },
									function( url ) { if ( url !== false ) {
										if ( element.find( '.fc-event-time' ).length ) {
											element.find( '.fc-event-time' ).before( $( '<img src=' + url + ' />' ) );
										} else {
											element.find( '.fc-event-title' ).before( $( '<img src=' + url + ' />' ) );
										}
									}
							} );
						}
						// Manage description elements
						if ( event.description ) {
							// Show the tooltip for the month view and render any additional description
							// into the event for all other views
							if ( element.find( '.fc-event-title' ).length && view.name !== 'month' && view.name.indexOf( 'Day' ) >= 0 ) {
								element.find( '.fc-event-title' ).after( $( '<span class="srf-fc-description">' + event.description + '</span>' ) );
							} else {
								// Tooltip
								_calendar.tooltip.show( {
									context: element,
									content: event.description.substring( 0, event.description.substr( 0, 100 ).lastIndexOf( " " ) ) + ' ...',
									title: mw.msg( 'smw-ui-tooltip-title-event' ),
									button: false
								} );
							}
						}
					},
					dayClick: function( date, allDay, jsEvent ) {
						// If the day number (where available) is clicked then switch to the daily view
						if ( allDay && data.options.dayview && $( jsEvent.target ).is( 'div.fc-day-number' ) ) {
							container.fullCalendar( 'changeView', 'agendaDay'/* or 'basicDay' */).fullCalendar( 'gotoDate', date );
						}
					}
				} );

				// Add legend and filter display if available
				// Depending on the theme add an additional ui class
				if ( 'legend' in data ) {
					if ( data.options.legend === 'top' ) {
						container.prepend( _calendar.getLegend( data.legend, 'srf-ui-legendList' ) );
						container.find( '.srf-ui-legendList' ).addClass( data.options.theme ? 'top ui-state-default' : 'top basic' );
					} else if ( data.options.legend === 'bottom' ) {
						container.append( _calendar.getLegend( data.legend, 'srf-ui-legendList' ) );
						container.find( '.srf-ui-legendList' ).addClass( data.options.theme ? 'bottom ui-state-default' : 'bottom basic' );
					} else if ( data.options.legend === 'tooltip' ) {
						// Clone the design from an existing fc button and override the text
						// and add a dedicated tooltip class
						container.find( '.fc-header-right' ).append( '<span class="fc-header-space"></span><span class="srf-ui-legendTooltipButton"></span>' );
						container.find( '.fc-button-today' ).clone().appendTo( container.find( '.srf-ui-legendTooltipButton' ) );
						container.find( '.srf-ui-legendTooltipButton' ).find( '.fc-button-content' ).html( '<div class="srf-ui-square">&nbsp;</div>' );

						// Add tooltip instance for legend/filters
						_calendar.tooltip.add( {
							contextClass: 'srf-ui-legendTooltip',
							contentClass: 'srf-ui-legendTooltipContent',
							targetClass : 'srf-ui-legendTooltipButton',
							context: container,
							title: mw.msg( 'smw-ui-tooltip-title-legend' ),
							type: 'info',
							button: true,
							content: _calendar.getLegend( data.legend, 'srf-ui-legendTooltipList' )
						} );
					}
				}

				// Event handling for filter box elements
				// Used in case for a check/uncheck all button (not used yet)
				container.find( '.checkall' ).click( function() {
					container.find( ':checkbox' ).attr( 'checked' , this.checked );
				} );

				// Individual checkbox handling
				container.find( ':checkbox' ).click( function() {
					var $this = $( this ),
						filter = $this.attr( 'name' );

					if ( $this.is( ':checked' ) ) {
						// Checkbox was checked therefore look into the orginal data source
						// and add selected elements again since a prior removal event had takend place
						// as options are normally checked all from the start
						var source = $.map( data.events, function( event ) {
						if ( event.filter === filter ){
								return event;
							}
						} );

						// Add source data
						container.fullCalendar( 'addEventSource', source );
					} else {
						// Checkbox was unchecked therefore remove events for a specific filter
						container.fullCalendar( 'removeEvents', function( event ) {
							return event.filter === filter;
						} );
					}
				} );
			} );
		}
	};

	////////////////////////// IMPLEMENTATION ////////////////////////

	$( document ).ready( function() {
		$( '.srf-eventcalendar' ).each( function() {
			new srf.formats.eventcalendar( { context: $( this ) } );
		} );
	} );
} )( jQuery, mediaWiki, semanticFormats );