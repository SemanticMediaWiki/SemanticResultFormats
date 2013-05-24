/*!
 * This file is part of the Semantic Result Formats Extension
 * @see https://www.semantic-mediawiki.org/wiki/SRF
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 *
 * @since 1.8
 * @ingroup SRF
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */
( function( $, mw, srf ) {
 'use strict';

	////////////////////////// PRIVATE METHODS //////////////////////////

	var html = mw.html;

	var _cacheTime = 1000 * 60 * 60 * 24; // 24 hours

	var _CL_mwspinner   = 'mw-small-spinner';
	var _CL_srfIspinner = 'srf-spinner-img';
	var _CL_srfspinner  = 'srf-spinner';

	////////////////////////// PUBLIC METHODS /////////////////////////

	/**
	 * Module for formats utilities namespace
	 * @since 1.8
	 * @type Object
	 */
	srf.util = srf.util || {};

	/**
	 * Constructor
	 * @var Object
	 */
	srf.util = function( settings ) {
		$.extend( this, settings );
	};

	srf.util.prototype = {
		/**
		 * Get image url
		 * @since 1.8
		 * @param options
		 * @param callback
		 * @return string
		 */
		getImageURL: function( options, callback ) {
			var title = options.title,
				cacheTime = options.cachetime;

			// Get cache time
			cacheTime = cacheTime === undefined ? _cacheTime : cacheTime;

			// Get url from cache otherwise do an ajax call
			var url = $.jStorage.get( title );

			if ( url !== null ) {
				if ( typeof callback === 'function' ) { // make sure the callback is a function
					callback.call( this, url ); // brings the scope to the callback
				}
				return;
			}

			// Get url via ajax
			$.getJSON(
			mw.config.get( 'wgScriptPath' ) + '/api.php',
			{
				'action': 'query',
				'format': 'json',
				'prop'  : 'imageinfo',
				'iiprop': 'url',
				'titles': title
			},
			function( data ) {
				if ( data.query && data.query.pages ) {
					var pages = data.query.pages;
					for ( var p in pages ) {
						if ( pages.hasOwnProperty( p ) ) {
							var info = pages[p].imageinfo;
							for ( var i in info ) {
								if ( info.hasOwnProperty( i ) ) {
									$.jStorage.set( title , info[i].url, { TTL: cacheTime } );
									if ( typeof callback === 'function' ) { // make sure the callback is a function
										callback.call( this, info[i].url ); // brings the scope to the callback
									}
									return;
								}
							}
						}
					}
				}
				if ( typeof callback === 'function' ) { // make sure the callback is a function
					callback.call( this, false ); // brings the scope to the callback
				}
				}
			);
		},

		/**
		 * Get title url
		 * @since 1.8
		 * @param options
		 * @param callback
		 * @return string
		 */
		getTitleURL: function( options, callback ) {
			var title = options.title,
				cacheTime = options.cachetime;

			// Get cache time
			cacheTime = cacheTime === undefined ? _cacheTime : cacheTime;

			// Get url from cache otherwise do an ajax call
			var url = $.jStorage.get( title );
			if ( url !== null ) {
				if ( typeof callback === 'function' ) { // make sure the callback is a function
					callback.call( this, url ); // brings the scope to the callback
				}
				return;
			}

			// Get url via ajax
			$.getJSON(
				mw.config.get( 'wgScriptPath' ) + '/api.php',
				{
					'action': 'query',
					'format': 'json',
					'prop'  : 'info',
					'inprop': 'url',
					'titles': title
				},
				function( data ) {
					if ( data.query && data.query.pages ) {
						var pages = data.query.pages;
						for ( var p in pages ) {
							if ( pages.hasOwnProperty( p ) ) {
								var info = pages[p];
									$.jStorage.set( title, info.fullurl, { TTL: cacheTime } );
									if ( typeof callback === 'function' ) { // make sure the callback is a function
										callback.call( this, info.fullurl ); // brings the scope to the callback
									}
									return;
							}
						}
					}
				if ( typeof callback === 'function' ) { // make sure the callback is a function
					callback.call( this, false ); // brings the scope to the callback
				}
				}
			);
		},

		/**
		 * Get spinner for a local element
		 * @since 1.8
		 * @param options
		 * @return object
		 */
		spinner: {
			create: function( options ) {

				// Select the object from its context and determine height and width
				var obj = options.context.find( options.selector ),
					h = mw.html,
					width  = obj.width(),
					height = obj.height();

				// Add spinner to target object
				obj.after( h.element( 'span', { 'class' : _CL_srfIspinner + ' ' + _CL_mwspinner }, null ) );

				// Adopt height and width to avoid clutter
				options.context.find( '.' + _CL_srfIspinner + '.' + _CL_mwspinner )
					.css( { width: width, height: height } )
					.data ( 'spinner', obj ); // Attach the original object as data element
				obj.remove(); // Could just hide the element instead of removing it

			},
			replace: function ( options ){
				// Replace spinner and restore original instance
				options.context.find( '.' + _CL_srfIspinner + '.' + _CL_mwspinner )
					.replaceWith( options.context.find( '.' + _CL_srfIspinner ).data( 'spinner' ) ) ;
			},
			hide: function ( options ){
				var c = options.length === undefined ? options.context : options;
				c.find( '.' + _CL_srfspinner ).hide();
			}
		},

		/**
		 * Convenience method to check if some options are supported
		 *
		 * @since 1.9
		 *
		 * @param {string} option
		 *
		 * @return boolean
		 */
		assert: function( option ) {
			switch ( option ){
				case 'canvas':
					// Checks if the current browser supports canvas
					// @see http://goo.gl/9PYP3
					return !!window.HTMLCanvasElement;
				case 'svg':
					// Checks if the current browser supports svg
					return !!window.SVGSVGElement;
				default:
					return false;
			}
		},

		/**
		 * Convenience method for growl-like notifications using the blockUI plugin
		 *
		 * @since 1.9
		 * @var options
		 * @return object
		 */
		notification: {
			create: function( options ) {
				return $.blockUI( {
					message: html.element( 'span', { 'class' : 'srf-notification-content' }, new html.Raw( options.content ) ),
					fadeIn: 700,
					fadeOut: 700,
					timeout: 2000,
					showOverlay: false,
					centerY: false,
					css: {
						'width': '235px',
						'line-height': '1.35',
						'z-index': '10000',
						'top': '10px',
						'left': '',
						'right': '15px',
						'padding': '0.25em 1em',
						'margin-bottom': '0.5em',
						'border': '0px solid #fff',
						'background-color': options.color || '#000',
						'opacity': '.6',
						'cursor': 'pointer',
						'-webkit-border-radius': '5px',
						'-moz-border-radius': '5px',
						'border-radius': '5px',
						'-webkit-box-shadow': '0 2px 10px 0 rgba(0,0,0,0.125)',
						'-moz-box-shadow': '0 2px 10px 0 rgba(0,0,0,0.125)',
						'box-shadow': '0 2px 10px 0 rgba(0,0,0,0.125)'
					}
				} );
			}
		},

		/**
		 * Convenience method for ui-widget like error/info messages
		 *
		 * @since 1.9
		 * @var options
		 * @return object
		 */
		message:{
			set: function( options ){
				var type = options.type === 'error' ? 'ui-state-error' : 'ui-state-highlight';
				options.context.prepend( html.element( 'div', {
					'class': 'ui-widget' }, new html.Raw( html.element( 'div', {
						'class': type + ' ui-corner-all','style': 'padding-left: 0.5em' }, new html.Raw( html.element( 'p', { }, new html.Raw( html.element( 'span', { 'class': 'ui-icon ui-icon-alert', 'style': 'float: left; margin-right: 0.7em;' }, '' ) + options.message ) ) ) ) ) ) );
			},

			exception: function( options ){
				this.set( $.extend( {}, { type: 'error' }, options ) );
				throw new Error( options.message );
			},

			remove:function( context ){
				context.children().fadeOut( 'slow' ).remove();
			}
		},

		/**
		 *
		 *
		 *
		 */
		 image: {

			/**
			 * Returns image information including thumbnail
			 *
			 * @since  1.9
			 *
			 * @param options
			 * @param callback
			 *
			 * @return object
			 */
			imageInfo: function( options, callback ){
				var isCached = true;

				// Get cache otherwise do an Ajax call
				if ( options.cache ) {
					var imageInfo = $.jStorage.get( options.title + '-' + options.width );

					if ( imageInfo !== null ) {
						if ( typeof callback === 'function' ) {
							callback.call( this, isCached, imageInfo );
						}
						return;
					}
				}

				$.getJSON( mw.config.get( 'wgScriptPath' ) + '/api.php', {
						'action': 'query',
						'format': 'json',
						'prop'  : 'imageinfo',
						'iiprop': 'url',
						'iiurlwidth': options.width,
						'iiurlheight': options.height,
						'titles': options.title
					},
					function( data ) {
						if ( data.query && data.query.pages ) {
							var pages = data.query.pages;
							for ( var p in pages ) {
								if ( pages.hasOwnProperty( p ) ) {
									var info = pages[p];
									if ( options.cache !== undefined ) {
										$.jStorage.set( options.title + '-' + options.width , info, { TTL: options.cache } );
									}
									if ( typeof callback === 'function' ) {
										callback.call( this, !isCached, info );
									}
									return;
								}
							}
						}
					if ( typeof callback === 'function' ) {
						callback.call( this, !isCached, false );
					}
					}
				);
			}

		 }
	};

} )( jQuery, mediaWiki, semanticFormats );