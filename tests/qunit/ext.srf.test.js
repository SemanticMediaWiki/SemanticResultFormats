/**
 * QUnit tests
 *
 * @since 1.9
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function ( mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf', QUnit.newMwEnvironment() );

	var pass = 'Passes because ';

	/**
	 * Instance testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'instance', 1, function ( assert ) {

		assert.ok( srf instanceof Object, pass + 'srf instance was accessible' );

	} );

}( mediaWiki, semanticFormats ) );