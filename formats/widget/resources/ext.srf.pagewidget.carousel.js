/**
 * JavaScript for SRF PageWidget format
 * @see http://www.semantic-mediawiki.org/wiki/Help:Pagewidget format
 *
 * @since 1.8
 * @release 0.2
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $ ) {
	"use strict";

	$(document).ready( function() {
		$( '.srf-pagewidget' ).each( function() {

			var $this = $( this );
			var container = $this.find( '.container' ),
				embedonly = container.data( 'embedonly' );

			// Update navigation control with class that is a direct child
			$this.find( '.container > ul' ).attr( 'class', 'slider' );
			container.find( 'ul.slider > li' ).attr( 'class', 'slide' ).css( { 'list-style': 'none' } );

			// Iterate over available container objects
			container.each( function() {
				$( this ).carousel( {
					namespace: 'srf-pagewidget-carousel',
					slider: '.slider',
					slide: '.slide',
				} );
			} );

			// Release container
			container.show();
			$this.find( '.srf-processing' ).hide();

			// Override static text with available translation
			container.find( '.slidecontrols' ).find( 'li .srf-pagewidget-carousel-prev' ).text( mw.msg( 'srf-ui-navigation-prev' ) );
			container.find( '.slidecontrols' ).find( 'li .srf-pagewidget-carousel-next' ).text( mw.msg( 'srf-ui-navigation-next' ) );

			// Switch positions of the navigation control
			container.find( '.slidecontrols' ).find( 'li .srf-pagewidget-carousel-next' ).before( container.find( '.slidecontrols' ).find( 'li .srf-pagewidget-carousel-prev' ) );

			// If embedonly is undefined it means the first <a> element contains the link
			// to the embedded source page
			if ( embedonly === undefined ) {
				container.find( 'ul.slider > li > a' ).addClass( 'srf-pagewidget-carousel-source' ).text( mw.msg( 'srf-ui-common-label-source' ) );
			}

			// Hide TOC as it will disturb the embedded display behaviour
			container.find ( '.toc' ).css ( { 'display': 'none' } );

		} );
	} );
} )( window.jQuery );