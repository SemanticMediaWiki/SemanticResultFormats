/*!
 * This file is part of the Semantic Result Formats Tagcloud module
 * @see https://www.semantic-mediawiki.org/wiki/Help:Tagcloud_formats
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
 * @since 1.9
 * @ingroup SRF
 *
 * @license GNU GPL v2+
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	/*global d3:true*/

	/**
	 * Inheritance class for the srf.formats constructor
	 *
	 * @since 1.9
	 *
	 * @class
	 * @singleton
	 */
	srf.formats = srf.formats || {};

	/**
	 * Base constructor for objects representing a tagcloud instance
	 *
	 * @since 1.8
	 *
	 * @class
	 * @constructor
	 */
	srf.formats.tagcloud = function() {};

	/* Public interface  */

	srf.formats.tagcloud.prototype = {

		/**
		 * Version
		 *
		 * @property
		 * @type {string}
		 */
		version: '0.4.1',

		/**
		 * Specifies default values
		 *
		 * @property
		 * @type {Object}
		 */
		defaults: {},

		/**
		 * Generates a sphere tag cloud using the Tagcanvas plug-in
		 *
		 * @see http://goo.gl/Uci76
		 *
		 * @param {Object} context
		 *
		 * @return {boolean}
		 */
		sphere: function( context ) {

			// Init
			var container = context.find( '.srf-container' ),
				tagsID = container.find( '.srf-tags' ).attr( 'id' ),
				data = container.data();

			context.css( { 'width': data.width, 'height': data.height } );

			// Add canvas element
			var canvas = $( '<canvas></canvas>' ).appendTo( container );
			canvas.attr( {
				id: container.attr( 'id' ) + '-canvas',
				width: data.width,
				height: data.height
			} );


			// Initialize tagcanvas instance
			// Somewhere around here QUnit dies (with a time out) which
			// makes it unattainable to run a successful test
			if( !canvas.tagcanvas( {
				textColour: null,
				outlineColour: '#FF9D43',
				textFont: data.font,
				reverse: true,
				weight: true,
				shadow: '#ccf',
				shadowBlur: 3,
				depth: 0.3,
				maxSpeed: 0.04
			}, tagsID ) ) {
				// something went wrong, hide the canvas container
				container.hide();
				return false;
			}

			return true;
		},

		/**
		 * Generates a wordcloud using the D3.js cloud plug-in
		 *
		 * @see http://goo.gl/lbxjk
		 *
		 * @param {Object} context
		 *
		 * @return {boolean}
		 */
		wordcloud: function( context ) {

			// Init
			var container = context.find( '.srf-container' ),
				containerID = container.attr( 'id' ),
				data = container.data(),
				textFont = data.font.split(',');

			context.css( { 'width': data.width, 'height': data.height } );

			// Build array of tags, size, and href property
			var arr = [];
			container.find( 'li' ).each( function(){
				arr.push( [ $( this ).text(), $( this ).css( 'font-size' ), $( this ).find( 'a' ).attr( 'href' ) ] );
			} );

			// Init the colour array
			var fill = d3.scale.category20b();

			// Set properties for the tags
			function draw( words ) {
				d3.select( '#' + containerID ).append( 'svg' )
				.attr( 'width', data.width - 5 )
				.attr( 'height', data.height - 5 )
				.append( 'g' )
				.attr( 'transform', 'translate(' + data.width / 2 + ',' + data.height / 2 + ')' )
				.selectAll( 'text'  )
				.data( words )
				.enter().append( 'text' )
				.style( 'fill', function( d ) {
					return fill(d.text.toLowerCase() );
				} )
				.style( 'font-size', function(d) {
					return d.size + 'px';
				} )
				.attr( 'text-anchor', 'middle' )
				.attr( 'transform', function( d ) {
					return 'translate(' + [d.x, d.y] + ')rotate(' + d.rotate + ')';
				} )
				.append( 'svg:a' )
				.style( 'text-decoration', function( d ) {
					return typeof d.href === 'undefined' ? 'none' : 'inherent';
				} )
				.attr( 'font-family',  function(d) {
					return d.font;
				} )
				.attr( 'target', data.target === undefined ? '' : '_blank' )
				.attr( 'xlink:href', function( d ) {
					return d.href !== '' ? d.href : '';
				} )
				.text( function(d) {
					return d.text;
				} );
			}

			// Build word cloud
			// ~~ (bitwise not) is used instead of Math.floor because it is
			// twice as fast as floor, "end" is fired when all words have been placed
			var cloud = d3.layout.cloud().size( [ data.width - 5, data.height - 5 ] )
				.words( arr.map( function( d ) { return {
					text: d[0],
					size: parseInt( d[1], 10 ),
					href: d[2]
				}; } ) )
				.rotate( function() {
					return ~~( Math.random() * 2 ) * 90;
				} )
				.fontSize( function(d) {
					return d.size;
				} )
				.font( textFont[ ~~( Math.random() * textFont.length ) ] )
				.on( 'end', draw );

			// Create the cloud
			cloud.start();

			// Return success status
			return true;
		},

		/**
		 * Initializes an instance, compares the required version, and loads
		 * mandatory RL module
		 *
		 * @param {Object} options
		 *
		 *   -- options.context
		 *   -- options.element
		 *   -- options.module
		 *   -- options.method
		 *
		 * @return {boolean}
		 */
		load: function( options ) {
			var self = this;
			var util = new srf.util();

			// Check version
			if ( options.context.data( 'version' ) >= self.version ) {

				if ( util.assert( options.element ) ) {
					mw.loader.using( options.module, function() {
						smw.async.load( options.context, function() {
							util.spinner.hide( $( this ) );
							options.method.call( this, $( this ) );
						} );
					} );
				} else {
					util.spinner.hide( options.context );
					util.message.set( {
						context: options.context,
						type: 'error',
						message: 'Your browser doesn\'t support ' + options.element
					} );
				}

				return true;

			} else {

				util.spinner.hide( options.context );
				util.message.set( {
					context: options.context,
					type: 'info',
					message: 'The software has been updated ( ' + options.version + ' ), please refresh your page content'
				} );

				return false;
			}
		}
	};

	/**
	 * Implementation of a tagcloud instance
	 * @since 1.8
	 * @ignore
	 */
	$( document ).ready( function() {
		var tagcloud = new srf.formats.tagcloud();

		// Tagcanvas
		$( '.srf-tagcloud-sphere' ).each( function() {
			tagcloud.load( {
				context: $( this ),
				element: 'canvas',
				module: 'ext.jquery.tagcanvas',
				method: tagcloud.sphere
			} );
		} );

		// Wordcloud
		$( '.srf-tagcloud-wordcloud' ).each( function() {
			tagcloud.load( {
				context: $( this ),
				element: 'svg',
				module: 'ext.d3.wordcloud',
				method: tagcloud.wordcloud
			} );
		} );

	} );

} )( jQuery, mediaWiki, semanticFormats );