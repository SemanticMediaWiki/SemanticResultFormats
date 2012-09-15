/**
 * JavaSript for SRF gallery overlay/fancybox module
 *
 * There is a method ImageGallery->add which allows to override the
 * image url but this feature is only introduced in MW 1.20 therefore
 * we have to catch the "real" image location url from the api to be able
 * to display the image in the fancybox
 *
 * jshint checked; full compliance
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @since: 1.8
 *
 * @release: 0.3
 */
( function( $ ) {
	"use strict";

	/*global mw:true*/

	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }

	$.fn.galleryRedirect = function( ) {

		// Loop over all relevant gallery items
		this.find( '.gallerybox' ).each( function () {
			var $this   = $( this ),
				image     = $this.find( 'a.image' ),
				redirecticon = '<span class="redirect"></span>';

			// Avoid undefined error
			if ( typeof  image.attr( 'href' ) === 'undefined' ) {
				$this.html( '<span class="error">' + mw.message( 'srf-gallery-image-url-error' ).escaped() + '</span>' );
			} else {
				// Alt attribute contains redirect title
				var title = image.find( 'img' ).attr( 'alt' );

				// Assign redirect article url
				if ( title.length > 0 ) {
					$.srfutil.getTitleURL( { 'title': title },
							function( url ) { if ( url === false ) {
								image.attr( 'href', '' );
							} else {
								image.attr( 'href', url );
								// Add redirect icon placeholder
								image.prepend( redirecticon );
							}
					} );
				}
			}
		} );
	};

	$(document).ready(function() {
		$( ".srf-redirect" ).each(function() {
			$( this ).galleryRedirect();
			// Release graph and bottom text
			$( this ).find( '.srf-processing' ).hide();
		} );
	} );
} )( window.jQuery, window.mediaWiki );