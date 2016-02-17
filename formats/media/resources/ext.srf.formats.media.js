/**
 * This file is part of the Semantic Result Formats Media module
 * @see https://www.semantic-mediawiki.org/wiki/Help:Media_formats
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
( function( $, mw, srf ) {
	'use strict';

	/*global jPlayerPlaylist:true*/

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
	 * Base constructor for objects representing a media instance
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 * @extends srf.formats
	 */
	srf.formats.media = function() {};

	/**
	 * Public interface
	 * @ignore
	 */
	srf.formats.media.prototype = {

		/**
		 * Default values
		 *
		 * Size: The minimum size of jPlayer with the skin is
		 * 480x270px (270p)otherwise the controls do not have enough space
		 *
		 * @since 1.9
		 *
		 * @property
		 * @type Object
		 */
		defaults: {
			posterImage: srf.settings.get( 'srfgScriptPath' ) + '/formats/media/resources/images/audio.auto.cover.png',
			size:{
				'270p' : { width: '480', height: '270', cssClass: 'jp-video-270p' },
				'360p' : { width: '640', height: '360', cssClass: 'jp-video-360p' }
			},
			jplayer : {
				swfPath: srf.settings.get( 'srfgScriptPath' ) + '/resources/jquery/jplayer/jquery.jplayer.swf',
				backgroundColor: '#FFFFFF',
				wmode: 'window',
				errorAlerts: smw.debug()
			}
		},

		/**
		 * Returns an object from a parsed JSON string
		 *
		 * @since 1.9
		 *
		 * @param {string} context
		 *
		 * @return {Object}
		 */
		parse: function( data ){
			return typeof data === 'string' ? jQuery.parseJSON( data ) : data;
		},

		/**
		 * Return id's
		 *
		 * @since 1.9
		 *
		 * @param {string} ID
		 *
		 * @return Object
		 */
		getId: function( ID ){
			return {
				'playerId' : ID + '-player',
				'containerId' : ID + '-container',
				'inspectorId' : ID + '-inspector'
			};
		},

		/**
		 * Returns player size
		 *
		 * @since 1.9
		 *
		 * @param {string} type
		 *
		 * @return {string}
		 */
		getPlayerSize: function( type ){
			return type === 'video' ? this.defaults.size['270p'] : '';
		},

		/**
		 * Returns adopted data array
		 *
		 * @since 1.9
		 *
		 * @param {string} source
		 * @param {string} mediaType
		 *
		 * @return Object
		 */
		getData: function( source, mediaType ){
			var data = [],
			self = this;
			$.each( source, function( index, value ) {

				// Make sure we display a title
				if ( value.title === undefined ) {
					value.title = value.subject;
				}

				// Use a pseudo cover art in case audio and video display is mixed to avoid
				// a black video screen for audio files with no cover art
				if ( mediaType === 'video' && ( value.poster === undefined || value.poster.length === 0 ) ) {
					value.poster = self.defaults.posterImage;
				}
				data.push ( value );
			} );
			return data;
		},

		/**
		 * Returns player template
		 *
		 * @since 1.9
		 *
		 * @param {string} ID
		 * @param {string} mediaType
		 * @param {string} mode
		 *
		 * @return Object
		 */
		getPlayerTemplate: function( ID, mediaType, mode ){
			var template = srf.template.jplayer[mediaType][mode];
			return template( {
				'playerId': this.getId( ID ).playerId,
				'containerId': this.getId( ID ).containerId
			} );
		},

		/**
		 * Media event inspector plugin displays information
		 * about the specified media element
		 *
		 * @since 1.9
		 *
		 * @param {string} ID
		 *
		 * @return Object
		 */
		getInspector: function( ID ){
			var self = this;
			mw.loader.using( 'ext.jquery.jplayer.inspector', function () {
				$( '#' + self.getId( ID ).inspectorId ).jPlayerInspector( {
					jPlayer : $( '#' +  self.getId( ID ).playerId  )
				} );
			} );
			return srf.template.jplayer.inspector( self.getId( ID ).inspectorId );
		}
	};

	/**
	 * Implementation of a media instance
	 * @since 1.9
	 * @ignore
	 */
	$( document ).ready( function() {

		$( '.srf-media' ).each( function() {
			var media = new srf.formats.media();

			var $this = $( this ),
				container = $this.find( '.media-container' ),
				ID = container.attr( 'id' ),
				json = media.parse( mw.config.get( ID ) ),
				mode = json.count === 1 ? 'single' : 'multi';

			// Specify jPlayer options
			var jPlayerSelector = {
					jPlayer: '#' + media.getId( ID ).playerId,
					cssSelectorAncestor:  '#' + media.getId( ID ).containerId
				},
				jPlayerOptions = {
					size: media.getPlayerSize( json.mediaType ),
					supplied: json.mimeTypes
				};

			// Init player template
			if ( json.mediaType ){
				container.prepend( media.getPlayerTemplate( ID, json.mediaType, mode ) );
			}

			// Init media event inspector
			if ( json.inspector ){
				container.append( media.getInspector( ID ) );
			}

			// Create jPlayer instance
			var jPlayerInstance = new jPlayerPlaylist(
				jPlayerSelector,
				media.getData( json.data, json.mediaType ),
				// Merge defaults and options, without modifying the defaults
				$.extend( {}, media.defaults.jplayer, jPlayerOptions )
			);

			// Release container and hide the spinner
			$this.find( '.srf-spinner' ).hide();
			container.show();

		} );
	} );

} )( jQuery, mediaWiki, semanticFormats );
