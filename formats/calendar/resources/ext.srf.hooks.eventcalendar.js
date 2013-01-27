/**
 * Custom eventCalendar hook
 *
 * Those registered custom events/hooks be used for individual
 * adjustment of event items
 *
 * @since 1.9
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	var html = mw.html;

	var customHandler = {

		// Experimental feature to encapsulate an event with an rdfa declaration
		eventMetaData: function( handler ){
			// Vocabulary
			handler.element.wrap('<div xmlns:v="http://rdf.data-vocabulary.org/#" typeof="v:Event" />');
			handler.element.attr( { 'rel': 'v:url', 'property' : 'v:summary' } );
			var content = handler.element.find( '.fc-event-content');
			// The date is not part of the event representation therefore we add this here
			// to have a full set of properties assigned to an event
			$( html.element( 'span', { 'style': 'display:none;', 'property': 'v:startDate' }, handler.event.start.toISOString() ) ).appendTo( handler.element );

			if ( handler.event.end !== null ){
				$( html.element( 'span', { 'style': 'display:none;', 'property': 'v:endDate' }, handler.event.end.toISOString() ) ).appendTo( handler.element );
			}
		}
	}

	$( document ).ready( function() {
		$( '.srf-eventcalendar' ).each( function() {
			var $this = $( this );

			// eventcalendarEventRender customer trigger/hook
			$this.on( "srf.eventcalendar.eventRender", '.container', function( event, dataHandler ) {
				customHandler.eventMetaData( dataHandler );
			} );
		} );
	} );
} )( jQuery, mediaWiki, semanticFormats );