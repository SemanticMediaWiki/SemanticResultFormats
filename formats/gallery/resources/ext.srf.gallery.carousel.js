/**
 * This file is part of the SRF gallery carousel module
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
 * Extends base class with a carousel function
 *
 * @class srf.formats.gallery.carousel
 */
( function( $, mw, srf ) {
	'use strict';

	/**
	 * @class srf.formats.gallery
	 * @mixins srf.formats.gallery.carousel
	 */

	$.extend( srf.formats.gallery.prototype, {

		/**
		 * Provides the redirect functionality
		 *
		 * data-scroll Number of items to be scrolled
		 * data-visible Calculated and set visible elements
		 * data-wrap Options are "first", "last", "both" or "circular"
		 * data-vertical Whether the carousel appears in horizontal or vertical orientation
		 * data-rtl Directionality
		 *
		 * @since 1.8
		 *
		 * @param {string} context
		 *
		 * @return {Function}
		 */
		carousel: function( context ) {
			return context.each( function() {
				var util = new srf.util();
				var $this = $( this ),
					fallbackDimension = srf.settings.get( 'wgThumbLimits' )[mw.user.options.get( 'thumbsize' )],
					carousel = $this.find( '.jcarousel' );

					util.spinner.hide( { context: $this } );

					carousel.each( function() {
						$( this ).show();
						$( this ).jcarousel( {
							scroll:  parseInt( $( this ).attr( 'data-scroll' ), 10 ),
							visible: parseInt( $( this ).attr( 'data-visible' ), 10 ),
							wrap: $( this ).attr( 'data-wrap' ),
							vertical: $( this ).attr( 'data-vertical' ) === 'true',
							rtl: $( this ).attr( 'data-rtl' ) === 'true',
							itemFallbackDimension: fallbackDimension
						} );
					} );
			} );
		}
	} );

	/**
	 * Implementation of an carousel instance
	 * @since 1.8
	 * @ignore
	 */
	$( document ).ready( function() {
		$( '.srf-gallery-carousel' ).each(function() {
			var gallery = new srf.formats.gallery();
			gallery.carousel( $( this ) );
		} );
	} );

} )( jQuery, mediaWiki, semanticFormats  );