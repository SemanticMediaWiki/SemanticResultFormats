/**
 * This file is part of the Semantic Result Formats Gallery module
 * @see https://www.semantic-mediawiki.org/wiki/Help:Gallery_formats
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
 * @since 1.9
 * @ingroup SRF
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */

/**
 * Base srf.formats.gallery class that reserves the namespace
 *
 * There is a method ImageGallery->add which allows to override the
 * image url but this feature is only introduced in MW 1.20 therefore
 * we have to catch the "real" image location url from the api to be able
 * to display the image in the fancybox
 *
 * @ignore
 */
( function( $, mw, srf ) {
	'use strict';

	/**
	 * Inheritance class for the srf.formats constructor
	 *
	 * @since 1.9
	 *
	 * @class
	 * @abstract
	 */
	srf.formats = srf.formats || {};

	/**
	 * Base constructor for objects representing a gallery instance
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 * @extends srf.formats
	 */
	srf.formats.gallery = function() {};

	/**
	 * Public interface
	 *
	 * @ignore
	 */
	srf.formats.gallery.prototype = {

		/**
		 * Stores default values
		 *
		 * @property
		 * @type {Object}
		 */
		defaults: {}
	};

} )( jQuery, mediaWiki, semanticFormats );