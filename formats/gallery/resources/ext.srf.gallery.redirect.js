/**
 * JavaScript for SRF gallery overlay/fancybox module
 * @see http://www.semantic-mediawiki.org/wiki/Help:Gallery format
 *
 * There is a method ImageGallery->add which allows to override the
 * image url but this feature is only introduced in MW 1.20 therefore
 * we have to catch the "real" image location url from the api
 *
 * @since 1.8
 * @release 0.3
 *
 * @file
 * @ingroup SemanticResultFormats
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	/*global mediaWiki:true semanticFormats:true */
	/**
	 * Module for formats extensions
	 * @since 1.8
	 * @type Object
	 */
	srf.formats = srf.formats || {};

	/**
	 * Base constructor for objects representing a gallery instance
	 * @since 1.8
	 * @type Object
	 */
	srf.formats.gallery = function() {};

	srf.formats.gallery.prototype = {
		redirect: function( context ) {
			return context.find( '.gallerybox' ).each( function() {
				var $this = $( this ),
					h = mw.html,
					image = $this.find( 'a.image' );

				// Prepare redirect icon placeholder
				image.prepend( h.element( 'span', { 'class': 'redirect' }, null ) );
				var redirect = image.find( '.redirect' ).hide();

				// Avoid undefined error
				if ( typeof  image.attr( 'href' ) === undefined ) {
					$this.html( h.element( 'span', { 'class': 'error' },  mw.message( 'srf-gallery-image-url-error' ).escaped() ) ); 
				} else {

					// Alt attribute contains redirect title
					var title = image.find( 'img' ).attr( 'alt' );

					// Assign redirect article url
					if ( title !== undefined && title.length > 0 ) {
						// Show image spinner while fetching the URL
						util.spinner.show( { context: $this, selector: 'img' } );

						util.getTitleURL( { 'title': title },
							function( url ) { if ( url === false ) {
								image.attr( 'href', '' );
								// Release thumb image
								util.spinner.hide( { context: $this, selector: 'img' } );
							} else {
								image.attr( 'href', url );
								// Release thumb image
								util.spinner.hide( { context: $this, selector: 'img' } );
								// Release redirect icon
								redirect.show();
							}
						} );
					}
				}
		} );
		}
	};

	/**
	 * Implementation and representation of the gallery instance
	 * @since 1.8
	 * @type Object
	 */
	var gallery = new srf.formats.gallery();
	var util = new srf.util();

	$( document ).ready( function() {
		$( '.srf-redirect' ).each(function() {
			gallery.redirect( $( this ) );
		} );
	} );
} )( jQuery, mediaWiki, semanticFormats );