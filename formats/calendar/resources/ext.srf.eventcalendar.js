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

	// Only display errors
	try { console.log( 'console ready' ); } catch (e) { var console = { log: function () { } }; }

	$.fn.srfEventCalendar = function() {

		var container = this.find( ".container" ),
			calendarID = container.attr( "id" ),
			json = mw.config.get( calendarID );

		// Parse json string and convert it back
		var data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

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