/**
 * JavaScript for SRF media/jPlayer format
 * @see http://www.semantic-mediawiki.org/wiki/Help:Media format
 *
 * @since 1.9
 * @release 0.2
 *
 * @file
 * @ingroup SemanticResultFormats
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	/*global mw:true jPlayerPlaylist:true*/

	/**
	 * Default values
	 *
	 * Size: The minimum size of jPlayer with the skin is
	 * 480x270px (270p)otherwise the controls do not have enough space
	 *
	 * @since 1.9
	 * @type Object
	 */
	var scriptPath = mw.config.get( 'srf.options' ).srfgScriptPath;
	var defaults = {
		posterImage: scriptPath + '/formats/media/resources/images/audio.auto.cover.png',
		size:{
			'270p' : { width: '480', height: '270', cssClass: "jp-video-270p" },
			'360p' : { width: '640', height: '360', cssClass: "jp-video-360p" }
		},
		jplayer : {
			swfPath: scriptPath + '/resources/jquery.jplayer/Jplayer.swf',
			backgroundColor: '#FFFFFF',
			solution: 'html, flash',
			wmode: 'window'
		}
	};

	/**
	 * Utility functions
	 *
	 * @since 1.9
	 * @type Object
	 */
	var _util = {

		/**
		 * Returns a parsed json string back into its object structure
		 *
		 * @since 1.9
		 * @type Object
		 */
		parseData: function( data ){
			return typeof data === 'string' ? jQuery.parseJSON( data ) : data;
		},

		/**
		 * Return id's
		 *
		 * @since 1.9
		 * @type Object
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
		 * @type Object
		 */
		getPlayerSize: function( type ){
			return type === 'video' ? defaults.size['270p'] : '';
		},

		/**
		 * Returns adopted data array
		 *
		 * @since 1.9
		 * @type Object
		 */
		getData: function( source, mediaType ){
			var data = [];
			$.each( source, function( index, value ) {

				// Make sure we display a title
				if ( value.title === undefined ) {
					value.title = value.subject;
				}

				// Use a pseudo cover art in case audio and video display is mixed to avoid
				// a black video screen for audio files with no cover art
				if ( mediaType === 'video' && ( value.poster === undefined || value.poster.length === 0 ) ) {
					value.poster = defaults.posterImage;
				}
				data.push ( value );
			} );
			return data;
		},

		/**
		 * Returns player template
		 *
		 * @since 1.9
		 * @type Object
		 */
		getPlayerTemplate: function( ID, mediaType, mode ){
			var template = srf.template.jplayer[mediaType][mode];
			return template( {
				'playerId': _util.getId( ID ).playerId,
				'containerId': _util.getId( ID ).containerId
			} );
		},

		/**
		 * Media event inspector plugin displays information
		 * about the specified media element
		 *
		 * @since 1.9
		 * @type Object
		 */
		getInspector: function( ID ){
			mw.loader.using('ext.jquery.jplayer.inspector', function () {
				$( '#' + _util.getId( ID ).inspectorId ).jPlayerInspector( {
					jPlayer : $( '#' +  _util.getId( ID ).playerId  )
				} );
			} );
			return srf.template.jplayer.inspector( _util.getId( ID ).inspectorId );
		}
	};

	$( document ).ready( function() {

		/**
		 * Implementation
		 *
		 * @since 1.9
		 * @type Object
		 */
		$( '.srf-media' ).each( function() {

			var $this = $( this ),
				container = $this.find( '.container' ),
				ID = container.attr( 'id' );

			var json = _util.parseData( mw.config.get( ID ) );
			var mode = json.count === 1 ? 'single' : 'multi';

			var jPlayerSelector = {
					jPlayer: '#' + _util.getId( ID ).playerId,
					cssSelectorAncestor:  '#' + _util.getId( ID ).containerId
				},
				jPlayerOptions = {
					size: _util.getPlayerSize( json.mediaType ),
					supplied: json.mimeTypes
				};

			// Init player template
			if ( json.mediaType ){
				container.prepend( _util.getPlayerTemplate( ID, json.mediaType, mode ) );
			}

			// Init media event inspector
			if ( json.inspector ){
				container.append( _util.getInspector( ID ) );
			}

			// Create jPlayer instance
			var jPlayerInstance = new jPlayerPlaylist(
				jPlayerSelector,
				_util.getData( json.data, json.mediaType ),
				// Merge defaults and options, without modifying the defaults
				$.extend( {}, defaults.jplayer, jPlayerOptions )
			);

			// Release container and hide the spinner
			$this.find( '.srf-spinner' ).hide();
			container.show();

		} );
	} );
} )( jQuery, mediaWiki, semanticFormats );