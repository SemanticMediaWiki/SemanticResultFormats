/**
 * JavaScript for SRF PageWidget format
 * @see http://www.semantic-mediawiki.org/wiki/Help:Pagewidget format
 *
 * @since 1.8
 * @release 0.3
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $ ) {

	"use strict";

	////////////////////////// PRIVATE METHODS ////////////////////////

	function _moveSlide( slider, event ){
		var direction = null;

		// Handle cursor keys
		if ( event.keyCode == 37 ) {
			// Left
			direction = 'prev';
		} else if ( event.keyCode == 39 ) {
			// Right
			direction = 'next';
		}

		if (direction != null) {
			slider.trigger( 'nextprev', { dir: direction } );
			event.preventDefault();
		}
	}

	////////////////////////// IMPLEMENTATION ////////////////////////

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
					slide: '.slide'
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
				container.find( 'ul.slider > li > a:first-child' )
					.addClass( 'srf-pagewidget-carousel-source' )
					.text( mw.msg( 'srf-ui-common-label-source' ) );
			}

			// Hide TOC as it will disturb the embedded display behaviour
			container.find ( '.toc' ).css ( { 'display': 'none' } );

			// Current slider instance
			var slider = $( '#' + container.find( '.slider' ).attr( 'id' ) );

			// Keyboard event listener which should work in all browsers
			$( document.documentElement ).keyup( function( event ){
				_moveSlide( slider, event );
			} );
		} );
	} );
} )( window.jQuery );