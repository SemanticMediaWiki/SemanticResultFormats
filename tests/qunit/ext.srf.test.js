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
 * @ingroup SMW
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */

/**
 * QUnit tests for the srf base class
 *
 */
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf', QUnit.newMwEnvironment() );

	/**
	 * Test initialization and accessibility
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'init', 6, function ( assert ) {

		assert.ok( srf instanceof Object, 'srf namespace and instance was accessible' );
		assert.equal( $.type( srf.log ), 'function', '.log() was accessible' );
		assert.equal( $.type( srf.msg ), 'function', '.msg() was accessible' );
		assert.equal( $.type( srf.settings.getList ), 'function', '.settings.getList() was accessible' );
		assert.equal( $.type( srf.settings.get ), 'function', '.settings.get() was accessible' );
		assert.equal( $.type( srf.version ), 'function', '.version() was accessible' );

	} );

	/**
	 * Test settings function
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'settings', 4, function ( assert ) {

		assert.equal( $.type( srf.settings.getList() ), 'object', '.getList() returned a list of objects' );
		assert.equal( $.type( srf.settings.get( 'srfgScriptPath' ) ), 'string', '.get( "srfgScriptPath" ) returned a value' );
		assert.equal( srf.settings.get( 'lula' ), undefined, '.get( "lula" ) returned undefined for an unknown key' );
		assert.equal( srf.settings.get(), undefined, '.get() returned undefined for an empty key' );

	} );

	/**
	 * Test version function
	 *
	 * @since: 1.9
	 */
	QUnit.test( 'version', 1, function ( assert ) {

		assert.equal( $.type( srf.version() ), 'string', '.version() returned a string' );

	} );

}( jQuery, mediaWiki, semanticFormats ) );