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
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.widgets.panel', QUnit.newMwEnvironment() );

	var pass  = 'Passes because ';

	/**
	 * Instance testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'instance', 1, function ( assert ) {
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
	QUnit.test( 'add portlet', 1, function ( assert ) {
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