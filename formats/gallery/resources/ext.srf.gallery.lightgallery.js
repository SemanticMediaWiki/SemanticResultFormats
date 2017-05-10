/**
 * This file is part of the SRF gallery overlay/lightbox module
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
 * @since 2.4.2
 * @revision 0.1
 *
 * @ingroup SRF
 *
 * @license GNU GPL v2+
 * @author nischayn22
 */
( function( $, mw, srf ) {
	'use strict';

	$( document ).ready( function() {
		$(".srf-gallery").lightGallery({
			selector: 'a', 
			download: false
		});
	} );
} )( jQuery, mediaWiki, semanticFormats  );
