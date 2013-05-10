/**
 * This file is part of the SRF gallery slideshow module
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
 * Extends base class with a slideshow function
 *
 * @class srf.formats.gallery.slideshow
 */
( function( $, mw, srf ) {
	'use strict';

	/**
	 * @class srf.formats.gallery
	 * @mixins srf.formats.gallery.slideshow
	 */

	$.extend( srf.formats.gallery.prototype, {

		/**
		 * Provides the slideshow functionality
		 *
		 * @since 1.8
		 *
		 * @param {string} context
		 *
		 * @return {Function}
		 */
		slideshow: function( context ) {
			return context.each( function() {
				var util = new srf.util();
				var $this = $( this );
				var maxHeight = 0;
				var gallery   = $this.find( 'ul' );
				var galleryId = '#' + gallery.attr( 'id' );
				var previous  = $this.prev( 'p' ).children( 'br' );

				// The gallery parser comes with a preceding empty <p> element
				// this is a work-around to avoid
				if ( previous.length == 1 ) {
					previous.hide();
				}

				// Make elements visible / hide
				util.spinner.hide( { context: $this } );
				gallery.show();

				// Loop over all the gallery items
				gallery.find( 'li' ).each( function () {

					// Text elements can vary in there height therefore determine max height
					// for all images used in the same instance
					if($(this).height() > maxHeight ) {
						maxHeight = $( this ).height();
					}
				} );

				// Set max height in order for all elements to be positioned equally
				gallery.height( maxHeight );

				if( !gallery.responsiveSlides({
					pauseControls: gallery.attr( 'data-nav-control' ) === 'auto',
					prevText: mw.msg( 'srf-gallery-navigation-previous' ),
					nextText: mw.msg( 'srf-gallery-navigation-next' ),
					auto:  gallery.attr( 'data-nav-control' ) === 'auto',
					pause: gallery.attr( 'data-nav-control' ) === 'auto',
					pager: gallery.attr( 'data-nav-control' ) === 'pager',
					nav:   gallery.attr( 'data-nav-control' ) === 'nav'
				} ) ) {
					// something went wrong, hide the canvas container
					$this.find( galleryId ).hide();
				}
		} );
		}
	} );

	/**
	 * Implementation of an slideshow instance
	 * @since 1.8
	 * @ignore
	 */
	$( document ).ready( function() {
		$( '.srf-gallery-slideshow' ).each(function() {
			var gallery = new srf.formats.gallery();
			gallery.slideshow( $( this ) );
		} );
	} );

} )( jQuery, mediaWiki, semanticFormats );