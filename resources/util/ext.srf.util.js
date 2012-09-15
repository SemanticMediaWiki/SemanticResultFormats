/**
 * JavaScript for srf.util
 *
 * jshint checked
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @release: 0.2
 */
( function( $ ) {
	"use strict";

	var _cacheTime = 1000 * 60 * 60 * 24; // 24 hours

	////////////////////////// PRIVATE METHODS //////////////////////////

	////////////////////////// PUBLIC INTERFACE /////////////////////////

	$.srfutil = {
		/**
		 * Get Image url
		 *
		 * @param options
		 * @param callback
		 * @return url
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
		 *
		 * @param options
		 * @param callback
		 * @return url
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
		}
	};

} )( window.jQuery );