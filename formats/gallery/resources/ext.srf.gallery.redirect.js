/**
 * This file is part of the SRF gallery redirect module
 * @see http://www.semantic-mediawiki.org/wiki/Help:Gallery_format
 *
 * @section LICENSE
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 *
 * @file
 * @ignore
 *
 * @since 1.8
 * @revision 0.4
 *
 * @ingroup SRF
 *
 * @license GNU GPL v2+
 * @author mwjames
 */

/**
 * Extends base class with a redirect function
 *
 * @class srf.formats.gallery.redirect
 */
( function( $, mw, srf ) {
	'use strict';

	/**
	 * @class srf.formats.gallery
	 * @mixins srf.formats.gallery.redirect
	 */

	$.extend( srf.formats.gallery.prototype, {

		/**
		 * Provides the redirect functionality
		 *
		 * @since 1.8
		 *
		 * @param {string} context
		 *
		 * @return {Function}
		 */
		redirect: function( context ) {
			var util = new srf.util();
			var type = context.data( 'redirect-type' );
			return context.find( '.gallerybox' ).each( function() {
				var $this = $( this ),
					h = mw.html,
					image = $this.find( 'a.image' );

				// Avoid undefined error
				if ( image.attr( 'href' ) === undefined ) {
					$this.html( h.element( 'span', { 'class': 'error' }, mw.message( 'srf-gallery-image-url-error' ).escaped() ) );
				} else {

					// Per convention "alt" attribute contains the redirect title
					var titleAlt = image.find( 'img' ).attr( 'alt' ),
						titleStatus = titleAlt !== undefined && titleAlt.length > 0,
						imageSource = image.attr( 'href' );

					// Prepare and hide the redirect icon placeholder
					image.before( h.element( 'a', { 'class': 'redirecticon', 'href': imageSource }, null ) );
					var redirect = $this.find( '.redirecticon' ).hide();

					if ( type === '_uri' && titleStatus ) {
						// Direct assign redirect url
						image.attr( 'href', titleAlt );
						redirect.show();
					} else if ( titleStatus ) {
						// Assign redirect article url
						// Show image spinner while fetching the URL
						util.spinner.create( { context: $this, selector: 'img' } );

						util.getTitleURL( { 'title': titleAlt },
							function( url ) { if ( url === false ) {
								image.attr( 'href', '' );
								// Release thumb image
								util.spinner.replace( { context: $this, selector: 'img' } );
							} else {
								image.attr( 'href', url );
								// Release thumb image
								util.spinner.replace( { context: $this, selector: 'img' } );
								// Release redirect icon
								redirect.show();
							}
						} );
					}
				}
		} );
		}
	} );

	/**
	 * Implementation of an redirect instance
	 * @since 1.8
	 * @ignore
	 */
	$( document ).ready( function() {
		$( '.srf-redirect' ).each(function() {
			var gallery = new srf.formats.gallery();
			gallery.redirect( $( this ) );
		} );
	} );

} )( jQuery, mediaWiki, semanticFormats );