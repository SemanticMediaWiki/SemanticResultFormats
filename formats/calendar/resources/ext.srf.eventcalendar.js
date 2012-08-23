/**
 * JavaScript for SRF event calendar module
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

		// Check if the local browser is supporting localStorage
		var cacheUse = 'localStorage' in window && window.localStorage !== null;

		var _this = this;

		// API image url fetch
		this.getImageURL = function( title, cacheUse, cacheTime, callback ) {

			if ( cacheUse ){
				// Use localstorage to improve performance
				var urlCache = localStorage.getItem( title ),
					urlCacheTS = localStorage.getItem( title + 'cacheTS');

				// Check cache timestamp and if valid get the object from localstorage
				// otherwise remove item from localstorage
				if ( urlCacheTS < +new Date() && urlCache !== null ){
					localStorage.removeItem( title );
					localStorage.removeItem( title  + 'cacheTS' );
				} else if ( urlCache !== null ) {
					callback( urlCache );
					return;
				}
			}

			$.getJSON(
				mw.config.get( 'wgScriptPath' ) + '/api.php',
				{
					'action': 'query',
					'format': 'json',
					'prop'  : 'imageinfo',
					'iiprop': 'url',
					'titles': title
				},
				function( data ) {
					if ( data.query && data.query.pages ) {
						var pages = data.query.pages;
						for ( var p in pages ) {
							if ( pages.hasOwnProperty( p ) ) {
								var info = pages[p].imageinfo;
								for ( var i in info ) {
									if ( info.hasOwnProperty( i ) ) {
										if ( cacheUse ) {
											// Store the item in the localStorage together with its timestamp
											localStorage.setItem( title, info[i].url );
											localStorage.setItem( title  + 'cacheTS', +new Date() + 1000 * 60 * 60 * cacheTime );
										}
										callback( info[i].url );
										return;
									}
								}
							}
						}
					}
					callback( false );
				}
			);
		};

		// Hide processing note
		this.find( '.srf-processing' ).hide();

		// Show container
		container.show();

		// Init calendar container
		// @see http://arshaw.com/fullcalendar/docs/
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
				// Handle the event icon
				if ( event.eventicon ) {
					// Find image url and add icon
					_this.getImageURL( event.eventicon, cacheUse, 10 /* 10 h*/,
							function( url ) { if ( url !== false ) {
								if ( element.find('.fc-event-time').length ) {
									element.find('.fc-event-time').before( $( '<img src=' + url + ' />' ) );
								} else {
									element.find('.fc-event-title').before( $( '<img src=' + url + ' />' ) );
								}
							}
					} );
				}
				if ( event.description ) {
					// Show the tooltip for the month view and render any additional description
					// into the event for all other views
					if ( element.find( '.fc-event-title' ).length && view.name !== 'month' ) {
						element.find( '.fc-event-title' ).after( $( '<span class="srf-fc-description">' + ( view.name.indexOf( 'Week' ) >= 0 ? '<br />' : '' ) + event.description + '</span>' ) );
					} else {
						element.tipsy( {
							gravity: 'sw',
							html: true,
							title: function() { return event.description; }
						} );
					}
				}
			},
			dayClick: function( date, allDay, jsEvent ) {
				// If clicked on the day number then switch to the daily view to see its details
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