/**
 * This file is part of the Semantic Result Formats Extension
 * @see https://semantic-mediawiki.org/wiki/Srf
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
 *
 * @since 1.9
 * @ingroup SRF
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 * @author mwjames
 */

/**
 * Declares global srf instance and namespace
 *
 * @class srf
 */
var instance = ( function () {
	'use strict';

	/*global console:true message:true */

	var instance = {};

	instance.log = function( message ) {
		if ( typeof mediaWiki === 'undefined' ) {
			if ( typeof console !== 'undefined' ) {
				console.log( 'SRF: ', message );
			}
		} else {
			return mediaWiki.log.call( mediaWiki.log, 'SRF: ', message );
		}
	};

	instance.msg = function() {
		if ( typeof mediaWiki === 'undefined' ) {
			message = window.wgSRFMessages[arguments[0]];

			for ( var i = arguments.length - 1; i > 0; i-- ) {
				message = message.replace( '$' + i, arguments[i] );
			}
			return message;
		} else {
			return mediaWiki.msg.apply( mediaWiki.msg, arguments );
		}
	};

	/**
	 * Declares utility namespace
	 */
	instance.util = {};

	/**
	 * Declares formats namespace
	 */
	instance.formats = {};

	/**
	 * Access settings array
	 *
	 * @since 1.9
	 *
	 * @return {mixed}
	 */
	instance.settings = {

		/**
		 * Returns list of available settings
		 *
		 * @since 1.9
		 *
		 * @return {Object}
		 */
		getList: function() {
			return mediaWiki.config.get( 'srf-config' ).settings;
		},

		/**
		 * Returns a specific settings value
		 *
		 * @since 1.9
		 *
		 * @param  {string} key options to be selected
		 *
		 * @return {mixed}
		 */
		get: function( key ) {
			if( typeof key === 'string' ) {
				return this.getList()[key];
			}
			return undefined;
		}
	};

	/**
	 * Returns SRF version
	 *
	 * @since 1.9
	 *
	 * @return {string}
	 */
	instance.version = function() {
		return mediaWiki.config.get( 'srf-config' ).version;
	};

	// Alias
	instance.Util = instance.util;

	return instance;
} )();

// Assign namespace
window.srf = window.semanticFormats = instance;