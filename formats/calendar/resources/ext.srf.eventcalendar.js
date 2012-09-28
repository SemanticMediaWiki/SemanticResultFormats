/**
 * JavaScript for SRF event calendar module using the fullcalendar library
 *
 * @see http://arshaw.com/fullcalendar/docs/
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * jshint checked
 *
 * @release: 0.2
 */
( function( $ ) {
	"use strict";

	/*global mw:true*/

	$.fn.srfEventCalendar = function() {

		var container = this.find( ".container" ),
			calendarID = container.attr( "id" ),
			json = mw.config.get( calendarID );

		// Parse json string and convert it back
		var data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

		// Split start date (format is ISO8601 -> 2012-09-17T09:49Z)
		var calendarStart = data.options.calendarstart !== null ? data.options.calendarstart.split( '-', 3 ) : null;

		// Get Google holiday calendar url
		var gcalholiday = data.options.gcalurl === null ? '' : data.options.gcalurl;

		// Hide processing note
		this.find( '.srf-processing' ).hide();

		// Show container
		container.show();

		// Init calendar container
		container.fullCalendar( {
			header: {
				right: 'prev,next today',
				center: 'title',
				left: data.options.views
			},
			defaultView: data.options.defaultview,
			firstDay: data.options.firstday,
			theme: data.options.theme,
			editable: false,
			// Set undefined in case eventStart is not specified
			year: calendarStart !== null ? calendarStart[0] : undefined,
			// The value is 0-based, meaning January=0, February=1, etc.
			month: calendarStart !== null ? calendarStart[1] - 1 : undefined,
			// ...17T09:49Z only use the first two
			date: calendarStart !== null ? calendarStart[2].substring( 0, 2 ) : undefined,
			eventColor: '#48a0d5',
			eventSources: [ data.events, gcalholiday ],
			eventRender: function( event, element, view ) {
				// Handle event icons
				if ( event.eventicon ) {
					// Find image url and add an icon
					$.srfutil.getImageURL( { 'title': event.eventicon },
							function( url ) { if ( url !== false ) {
								if ( element.find( '.fc-event-time' ).length ) {
									element.find( '.fc-event-time' ).before( $( '<img src=' + url + ' />' ) );
								} else {
									element.find( '.fc-event-title' ).before( $( '<img src=' + url + ' />' ) );
								}
							}
					} );
				}
				if ( event.description ) {
					// Show the tooltip for the month view and render any additional description
					// into the event for all other views
					if ( element.find( '.fc-event-title' ).length && view.name !== 'month' && view.name.indexOf( 'Day' ) >= 0 ) {
						element.find( '.fc-event-title' ).after( $( '<span class="srf-fc-description">' + event.description + '</span>' ) );
					} else {
						element.tipsy( {
							gravity: 'sw',
							html: true,
							// Return abridged description (100 characters) without cutting the last word
							title: function() { return event.description.substring(0, event.description.substr(0, 100).lastIndexOf( " " ) ) + ' ...'; }
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
	};

	$( document ).ready( function() {
		$( ".srf-eventcalendar" ).each( function() {
			$( this ).srfEventCalendar();
		} );
	} );
} )( window.jQuery );