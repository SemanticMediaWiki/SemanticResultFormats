/**
 * JavaScript for SRF PageWidget module
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @since: 1.8
 * @release: 0.1
 */
( function( $ ) {
	"use strict";

	$(document).ready( function() {
		$( '.srf-pagewidget' ).each( function() {

			var $this = $( this );
			var container = $this.find( '.container' );

			// Update navigation control with class that is a direct child
			$this.find( '.container > ul' ).attr( 'class', 'slider' );
			container.find( 'ul.slider > li' ).attr( 'class', 'slide' ).css( { 'list-style': 'none' } );

			// Release container
			container.show();
			$this.find( '.srf-processing' ).hide();

			// Iterate over available container objects
			container.each( function() {
				$( this ).carousel( {
					namespace: 'srf-pagewidget-carousel',
					slider: '.slider',
					slide: '.slide',
				} );
			} );

			// Override static text with available translation
			container.find( '.slidecontrols' ).find( 'li .srf-pagewidget-carousel-prev' ).text( mw.msg( 'srf-navigation-prev' ) );
			container.find( '.slidecontrols' ).find( 'li .srf-pagewidget-carousel-next' ).text( mw.msg( 'srf-navigation-next' ) );

			// Switch positions of the navigation control
			container.find( '.slidecontrols' ).find( 'li .srf-pagewidget-carousel-next' ).before( container.find( '.slidecontrols' ).find( 'li .srf-pagewidget-carousel-prev' ) );

			// TOC will mess with the display therefore set display none
			container.find ( '.toc' ).css ( { 'display': 'none' } );

		} );
	} );
} )( window.jQuery );