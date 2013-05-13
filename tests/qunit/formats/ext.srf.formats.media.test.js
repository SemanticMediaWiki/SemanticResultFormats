/**
 * This file is part of the Semantic Result Formats QUnit Suite
 * @see https://www.semantic-mediawiki.org/wiki/QUnit
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
 * @author mwjames
 */

/**
 * QUnit tests for the srf.formats.media class
 *
 */
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.formats.media', QUnit.newMwEnvironment() );

	var jsonString = '{\"data\":{\"File:Foo.mp3\":{\"mp3\":\"\\/mw\\/Foo.mp3\",\"subject\":\"File:Foo.mp3\"}},\"count\":5,\"mediaType\":\"audio\",\"mimeTypes\":\"ogg,mp3\",\"inspector\":true}';

	/**
	 * Test initialization and accessibility
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'init', 8, function ( assert ) {
		var media = new srf.formats.media();

		assert.ok( media instanceof Object, 'srf.formats.media() instance was accessible' );
		assert.equal( $.type( media.defaults ), 'object', '.defaults was accessible' );
		assert.equal( $.type( media.parse ), 'function', '.parse() was accessible' );
		assert.equal( $.type( media.getId ), 'function', '.getId() was accessible' );
		assert.equal( $.type( media.getPlayerSize ), 'function', '.getPlayerSize() was accessible' );
		assert.equal( $.type( media.getData ), 'function', '.getData() was accessible' );
		assert.equal( $.type( media.getPlayerTemplate ), 'function', '.getPlayerTemplate() was accessible' );
		assert.equal( $.type( media.getInspector ), 'function', '.getInspector() was accessible' );

	} );

	/**
	 * Test template accessibility
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'template', 3, function ( assert ) {

		assert.equal( $.type( srf.template.jplayer.inspector ), 'function', '.jplayer.inspector() was accessible' );
		assert.equal( $.type( srf.template.jplayer.audio ), 'object', '.jplayer.audio returned an object' );
		assert.equal( $.type( srf.template.jplayer.video ), 'object', '.jplayer.video returned an object' );

	} );

	/**
	 * Test default settings
	 *
	 * @since: 1.9
	 */
	QUnit.asyncTest( 'defaults', 2, function ( assert ) {
		var media = new srf.formats.media();

		$.get( media.defaults.posterImage )
		.done( function() {
			QUnit.start();
			assert.ok( true, media.defaults.posterImage + ' verified' );
		} )
		.fail( function() {
			// doesn't exists
			QUnit.start();
		} );

		$.get( media.defaults.jplayer.swfPath )
		.done( function() {
			QUnit.start();
			assert.ok( true, media.defaults.jplayer.swfPath + ' verified' );
		} )
		.fail( function() {
			// doesn't exists
			QUnit.start();
		} );

	} );

	/**
	 * Test parse
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'parse', 1, function ( assert ) {
		var media = new srf.formats.media();

		assert.equal( $.type( media.parse( jsonString ) ), 'object', '.parse() returned an object' );

	} );

	/**
	 * Test getData
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'getData', 2, function ( assert ) {
		var media = new srf.formats.media();
		var json = media.parse( jsonString );
		var result = media.getData( json.data, json.mediaType );

		$.map( result, function ( data ) {
			assert.equal( data.subject, 'File:Foo.mp3', 'subject returned "File:Foo.mp3"' );
			assert.equal( data.title, 'File:Foo.mp3', 'title returned "File:Foo.mp3"' );
		} );

	} );

}( jQuery, mediaWiki, semanticFormats ) );