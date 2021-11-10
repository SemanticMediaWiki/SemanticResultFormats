/*!
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
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.formats.tagcloud', QUnit.newMwEnvironment() );

	var context = $(
		'<div><div id="test" class="srf-container">' +
		'<div id="test1" class="srf-tags"><ul>' +
		'<li><a href="/test1">Test1</a></li>' +
		'<li><a href="/test2">Test2</a></li>' +
		'</ul></div></div></div>', '#qunit-fixture' );

	/**
	 * Test initialization and accessibility
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'init', function ( assert ) {
		assert.expect( 4 );
		var tagcloud = new srf.formats.tagcloud();

		assert.equal( $.type( tagcloud.defaults ), 'object', '.defaults was accessible' );
		assert.equal( $.type( tagcloud.sphere ), 'function', '.sphere() was accessible' );
		assert.equal( $.type( tagcloud.wordcloud ), 'function', '.wordcloud() was accessible' );
		assert.equal( $.type( tagcloud.load ), 'function', '.load() was accessible' );

	} );

	/**
	 * Test dependencies
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'dependencies', function ( assert ) {
		assert.expect( 4 );
		var util = new srf.util();

		assert.equal( $.type( util.assert ), 'function', 'util.assert was accessible' );
		assert.equal( $.type( smw.async.load ), 'function', 'smw.async.load was accessible' );
		assert.equal( $.type( util.spinner.hide ), 'function', 'util.spinner.hide was accessible' );
		assert.equal( $.type( util.message.set ), 'function', 'util.message.set was accessible' );

	} );

	/**
	 * Test load
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'load', function ( assert ) {
		assert.expect( 4 );
		var tagcloud = new srf.formats.tagcloud();
		var result,
			options;

		context.data( 'version', '0.4.1' );

		options = {
			context: context,
			element: 'canvas',
			module: 'ext.jquery.tagcanvas',
			method: tagcloud.sphere
		};

		result = tagcloud.load( options );
		assert.ok( result, 'sphere was initialized' );

		options = {
			context: context,
			element: 'svg',
			module: 'ext.d3.wordcloud',
			method: tagcloud.wordcloud
		};

		result = tagcloud.load( options );
		assert.ok( result, 'wordcloud was initialized' );

		// Check for a non existing element
		options = {
			context: context,
			element: 'lula',
			module: '',
			method: ''
		};

		result = tagcloud.load( options );
		assert.ok( result, 'non existing element' );

		// Check invalid version
		options = {
			context: context,
			element: 'lula',
			module: '',
			method: ''
		};

		tagcloud.version = '0.4.2';
		result = tagcloud.load( options );
		assert.equal( result, false, 'wrong version' );

	} );

	/**
	 * Test sphere/tagcanvas
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'sphere', function ( assert ) {
		assert.expect( 1 );
		var tagcloud = new srf.formats.tagcloud();

		context.find( '.srf-container' ).data( {
			'width': 100,
			'height': 100,
			'font': 'sans'
		} );

		// Tagcanvas dies during testing for some reasons,
		// QUnit returns with a time-out
		assert.timeout(5000)
		const done = assert.async();
		mw.loader.using( 'ext.jquery.tagcanvas', function() {
			tagcloud.sphere( context );
			assert.ok( context.find( 'canvas' ), 'canvas element was found' );
			done();
		} );
	} );

	/**
	 * Test wordcloud
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'wordcloud', function ( assert ) {
		assert.expect(2);
		var tagcloud = new srf.formats.tagcloud();

		context.find( '.srf-container' ).data( {
			'width': 100,
			'height': 100,
			'font': 'sans'
		} );

		const done = assert.async();
		mw.loader.using( 'ext.d3.wordcloud', function() {
			var result = tagcloud.wordcloud( context );
			assert.ok( result, 'function returned true' );
			assert.ok( context.find( 'svg' ), 'svg element was found' );
			done();
		} );

	} );


}( jQuery, mediaWiki, semanticFormats ) );
