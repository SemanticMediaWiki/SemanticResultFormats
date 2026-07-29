/**
 * Custom eventCalendar hook
 *
 * Those registered custom events/hooks be used for individual
 * adjustment of event items
 *
 * @param $
 * @param mw
 * @since 1.9
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
 * @author mwjames
 */
( function ( $, mw ) {
	'use strict';

	const html = mw.html;

	const customHandler = {

		// Experimental feature to encapsulate an event with an rdfa declaration
		eventMetaData: function ( handler ) {
			// Vocabulary
			handler.element.wrap( '<div xmlns:v="http://rdf.data-vocabulary.org/#" typeof="v:Event" />' );
			handler.element.attr( { rel: 'v:url', property: 'v:summary' } );
			// The date is not part of the event representation therefore we add this here
			// to have a full set of properties assigned to an event
			$( html.element( 'span', { style: 'display:none;', property: 'v:startDate' }, handler.event.start.toISOString() ) ).appendTo( handler.element );

			if ( handler.event.end !== null ) {
				$( html.element( 'span', { style: 'display:none;', property: 'v:endDate' }, handler.event.end.toISOString() ) ).appendTo( handler.element );
			}
		}
	};

	$( document ).ready( () => {
		$( '.srf-eventcalendar' ).each( function () {
			const $this = $( this );

			// eventcalendarEventRender customer trigger/hook
			$this.on( 'srf.eventcalendar.eventRender', '.container', ( event, dataHandler ) => {
				customHandler.eventMetaData( dataHandler );
			} );
		} );
	} );
}( jQuery, mediaWiki ) );
