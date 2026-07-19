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
 * @license GPL-2.0-or-later
 * @author mwjames
 */
( function ( $, mw, srf ) {
	'use strict';

	// LEGACY: init/dependencies/load were ported to
	// tests/node-qunit/ext.srf.formats.tagcloud.test.js (issue #1070) and removed
	// here. sphere()/wordcloud() stay legacy — they need real tagcanvas + d3
	// canvas/SVG rendering; wordcloud() also currently calls d3.layout.cloud(),
	// which no longer exists in the installed d3 major version (a pre-existing,
	// out-of-scope bug). See issue #1073 for the broader legacy-test
	// documentation effort.
	QUnit.module( 'ext.srf.formats.tagcloud', QUnit.newMwEnvironment() );

	var context = $(
		'<div><div id="test" class="srf-container">' +
		'<div id="test1" class="srf-tags"><ul>' +
		'<li><a href="/test1">Test1</a></li>' +
		'<li><a href="/test2">Test2</a></li>' +
		'<li>TextOnly</li>' +
		'</ul></div></div></div>', '#qunit-fixture' );

	/**
	 * Test sphere/tagcanvas
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'sphere', function ( assert ) {
		assert.expect( 3 );
		var tagcloud = new srf.formats.tagcloud();

		context.find( '.srf-container' ).data( {
			'width': 100,
			'height': 100,
			'font': 'sans'
		} );

		// ensure qunit-fixture's context is in the DOM
		// (otherwise tagcanvas fails)
		var fixture = document.getElementById('qunit-fixture');
		if (fixture) {
			fixture.innerHTML = '';
			fixture.appendChild(context[0]);
		}
	
		// QUnit returns with a time-out
		assert.timeout(5000)
		const done = assert.async();
		mw.loader.using( 'ext.jquery.tagcanvas', function() {
			tagcloud.sphere( context );
			assert.ok( context.find( 'canvas' ), 'canvas element was found' );

			// @see https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/1050#discussion_r3488225643
			assert.equal( context.find( 'li:not(:has(a))' ).length, 0, 'all li elements have an anchor after sphere()' );
			assert.equal( context.find( 'li:has(a > a)' ).length, 0, 'no li is double-wrapped' );

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
