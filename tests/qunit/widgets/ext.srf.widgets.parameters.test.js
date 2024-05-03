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

	QUnit.module( 'ext.srf.widgets.parameters', QUnit.newMwEnvironment() );

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

		result = context.parameters();

		assert.ok( result.find( '.parameters') , pass + 'the srf.parameters widget returned a DOM object' );

	} );

	/**
	 * Limit parameter test
	 *
	 * @since  1.9
	 */
	QUnit.test( 'limit parameter test', function ( assert ) {
		assert.expect( 3 );
		var result;
		var context = $( '<div class="test"></div>', '#qunit-fixture' );

		var parameters = context.parameters();
		parameters.parameters( 'limit', {
			limit : 10,
			count : 1,
			max   : 20,
			step  : 1,
			change: function( event, ui ) {
				assert.equal( ui.value, ( 3 - 1 ), pass + 'the limit parameter was changed to 2' );
			}
		} );

		assert.equal( parameters.find( '.value' ).text(), "10", pass + 'the limit parameter is 10' );

		// Simulate slider value change
		parameters.find( '.slider' ).slider( "value", 3 );

		// Update limit display
		parameters.parameters(
			'option', 'limit', {
				'limit': 3,
				'count': 3
		} ) ;
		assert.equal( parameters.find( '.value' ).text(), "3", pass + 'the limit parameter is 3' );

	} );

}( jQuery, mediaWiki, semanticFormats ) );
