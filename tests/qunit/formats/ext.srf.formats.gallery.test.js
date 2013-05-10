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
 * QUnit tests for the srf.formats.gallery class
 *
 */
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.formats.gallery', QUnit.newMwEnvironment() );

	/**
	 * Test initialization and accessibility
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'init', 5, function ( assert ) {
		var gallery = new srf.formats.gallery();

		assert.ok( gallery instanceof Object, 'gallery instance was accessible' );
		assert.equal( $.type( gallery.redirect ), 'function', '.redirect() was accessible' );
		assert.equal( $.type( gallery.overlay ), 'function', '.overlay() was accessible' );
		assert.equal( $.type( gallery.slideshow ), 'function', '.slideshow() was accessible' );
		assert.equal( $.type( gallery.carousel ), 'function', '.carousel() was accessible' );

	} );

	/**
	 * Test overlay
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'overlay', 2, function ( assert ) {

		var context = $( '<div class="srf-overlay"</div>', '#qunit-fixture' );
		var gallery = new srf.formats.gallery();

		var ns = 'File';

		gallery.overlay( context, ns );
		assert.equal( $.type( gallery.defaults.path ), 'string', '.defaults.path was initialized and returned a string' );
		assert.equal( gallery.defaults.ns, ns, '.defaults.ns was initialized and returned the invoked string' );

	} );


}( jQuery, mediaWiki, semanticFormats ) );