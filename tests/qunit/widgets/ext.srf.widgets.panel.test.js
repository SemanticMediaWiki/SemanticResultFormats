/**
 * QUnit tests
 *
 * @since 1.9
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
 * @author mwjames
 */
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.widgets.panel', QUnit.newMwEnvironment() );

	var pass  = 'Passes because ';

	/**
	 * Instance testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'instance', function ( assert ) {
		assert.expect( 1 );
		var result;
		var context = $( '<div class="test"></div>', '#qunit-fixture' );

		result = context.panel( {
			'show': true
		} );
		assert.ok( result.find( '.srf-panel') , pass + 'the srf.panel widget returned a DOM object' );

	} );

	/**
	 * Portlet testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'add portlet', function ( assert ) {
		assert.expect( 1 );
		var result;
		var context = $( '<div class="test"></div>', '#qunit-fixture' );

		result = context.panel( {
			'show': true
		} );

		result = result.panel( 'portlet', {
			'class'  : 'portlet',
			'title'  : 'portlet',
			'fieldset': true
		} );
		assert.ok( result.find( '.portlet > fieldset' ), pass + 'the srf.panel widget added a portlet' );

	} );

}( jQuery, mediaWiki, semanticFormats ) );
