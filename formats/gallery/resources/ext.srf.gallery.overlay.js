/**
 * This file is part of the SRF gallery overlay/fancybox module
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
 * Extends base class with an overlay function
 *
 * @class srf.formats.gallery.overlay
 */
( function( $, mw, srf ) {
	'use strict';

	/**
	 * @class srf.formats.gallery
	 * @mixins srf.formats.gallery.overlay
	 */

	$.extend( srf.formats.gallery.prototype, {

		/**
		 * Provides the overlay functionality
		 *
		 * @since 1.8
		 *
		 * @param {string} context
		 * @param {string} ns
		 *
		 * @return {Function}
		 */
		overlay: function( context, ns ) {
			var self = this,
				util = new srf.util();

			// Override defaults
			self.defaults = {
				ns: ns,
				path: srf.settings.get( 'srfgScriptPath' )
			};

			// Encode the namespace (NS_FILE) otherwise languages
			// like Japanese, Chinese will fail
			var encodedNsText = encodeURIComponent( ns );

			context.each( function() {
				var $this = $( this ),
					galleryID = $this.attr( 'id' );

				// Loop over all relevant gallery items
				$this.find( '.gallerybox' ).each( function () {
					var $this = $( this ),
						h = mw.html,
						image = $this.find( 'a.image' ),
						imageText = $.trim( $this.find( '.gallerytext p' ).text() );

					// Group images
					image.attr( 'rel', image.has( 'img' ).length ? galleryID : '' );

					// Copy text information for image text display
					imageText = imageText !== null ? imageText :  image.find( 'img' ).attr( 'alt' );
					image.attr( 'title', imageText );

					// Avoid undefined error
					if ( image.attr( 'href' ) === undefined ) {
						$this.html( '<span class="error">' + mw.message( 'srf-gallery-image-url-error' ).escaped() + '</span>' );
					} else {

						// There should be a better way to get the title object but there isn't
						// var title = image.attr( 'href' ).replace(/.+?\File:(.*)$/, "$1" ).replace( "%27", "\'" ),
						// Apparently Windows server uses %3A
						var href = image.attr( 'href' ),
							title = href.split( encodedNsText + ( href.indexOf( '%3A' ) >= 0 ? '%3A': ':' ) );

						// Prepare overlay icon placeholder
						image.before( h.element( 'a', { 'class': 'overlayicon', 'href': href }, null ) );
						var overlayIcon = $this.find( '.overlayicon' ).hide();

						// Add spinner while fetching the URL
						util.spinner.create( { context: $this, selector: 'img' } );

						// Re-assign image url
						util.getImageURL( { 'title': ns + ':' + decodeURIComponent( title[1] ) },
							function( url ) { if ( url === false ) {
								image.attr( 'href', '' );
								// Release thumb image
								util.spinner.replace( { context: $this, selector: 'img' } );
							} else {
								image.attr( 'href', url );
								// Release thumb image
								util.spinner.replace( { context: $this, selector: 'img' } );
								// Release overlay icon
								overlayIcon.show();
							}
						} );
					}
				} );

				// Formatting the title
				function formatTitle( title, currentArray, currentIndex /*,currentOpts*/ ) {
					return '<div class="srf-fancybox-title"><span class="button"><a href="javascript:;" onclick="$.fancybox.close();"><img src=' +  self.defaults.path + '/resources/jquery/fancybox/closelabel.gif' + '></a></span>' + (title && title.length ? '<b>' + title : '' ) + '<span class="count"> (' +  mw.msg( 'srf-gallery-overlay-count', (currentIndex + 1) , currentArray.length ) + ')</span></div>';
				}

				// Display all images related to a group
				$this.find( "a[rel^=" + galleryID + "]" ).fancybox( {
					'showCloseButton' : false,
					'titlePosition'   : 'inside',
					'titleFormat'     : formatTitle
				} );
			} );
		}
	} );

	/**
	 * Implementation of an overlay instance
	 * @since 1.8
	 * @ignore
	 */
	$( document ).ready( function() {
		var ns = 'File';

		// Find the namespace used for the current instance
		$( '.srf-gallery,.srf-gallery-slideshow,.srf-gallery-carousel' ).each( function() {
			ns = $( this ).data( 'ns-text' );
		} );

		$( '.srf-overlay' ).each( function() {
			var gallery = new srf.formats.gallery();
			gallery.overlay( $( this ), ns );
		} );
	} );

} )( jQuery, mediaWiki, semanticFormats  );
