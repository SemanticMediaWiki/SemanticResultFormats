'use strict';

require( require( 'path' ).resolve( __dirname, '../../resources/widgets/ext.srf.widgets.parameters.js' ) );

QUnit.module( 'ext.srf.widgets.parameters', () => {

	QUnit.test( 'instance', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		const result = context.parameters();

		assert.true( result.is( context ), 'the srf.parameters widget returned the context element (its _init() creates no wrapper)' );
	} );

	QUnit.test( 'limit parameter test', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		const parameters = context.parameters();
		parameters.parameters( 'limit', {
			limit: 10,
			count: 1,
			max: 20,
			step: 1,
			change: ( event, ui ) => {
				assert.strictEqual( ui.value, 3 - 1, 'the limit parameter was changed to 2' );
			}
		} );

		assert.strictEqual( parameters.find( '.value' ).text(), '10', 'the limit parameter is 10' );

		parameters.find( '.slider' ).slider( 'value', 3 );

		parameters.parameters( 'option', 'limit', {
			limit: 3,
			count: 3
		} );
		assert.strictEqual( parameters.find( '.value' ).text(), '3', 'the limit parameter is 3' );
	} );

} );
