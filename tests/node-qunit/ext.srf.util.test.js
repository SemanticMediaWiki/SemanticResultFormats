'use strict';

QUnit.module( 'ext.srf.util', () => {

	QUnit.test( '.prototype', ( assert ) => {
		const util = new srf.util();

		assert.true( util instanceof srf.util, 'the srf.util.prototype instance was accessible' );
	} );

	QUnit.test( '.message', ( assert ) => {
		const fixture = $( '<div id="qunit-fixture">' ).appendTo( document.body );
		const util = new srf.util();

		assert.strictEqual( $.type( util.message ), 'object', 'the message object was accessible' );

		util.message.set( { context: fixture, message: 'Test' } );
		assert.strictEqual( $( '.ui-widget', fixture ).length, 1, 'message.set() created an object' );

		assert.throws( () => {
			util.message.exception( { context: fixture, message: 'Test' } );
		}, 'message.exception() thrown an exception' );
	} );

	QUnit.test( 'spinner', ( assert ) => {
		const util = new srf.util();

		let context = $( '<div><div id="spinner1" class="srf-spinner"></div></div>' ).appendTo( document.body );
		util.spinner.hide( context );
		assert.strictEqual( context.find( '#spinner1' ).css( 'display' ), 'none', '.hide( context ) was successful' );

		context = $( '<div><div><div id="spinner2" class="srf-spinner"></div></div></div>' ).appendTo( document.body );
		util.spinner.hide( { context: context } );
		assert.strictEqual( context.find( '#spinner2' ).css( 'display' ), 'none', '.hide( { context: ... } ) was successful' );
	} );

	QUnit.test( 'notification', ( assert ) => {
		const fixture = $( '<div id="qunit-fixture">' ).appendTo( document.body );
		const util = new srf.util();

		assert.strictEqual( $.type( util.notification ), 'object', 'the notification object was accessible' );
		assert.strictEqual( $.type( util.notification.create ), 'function', 'notification.create() was accessible' );

		util.notification.create( { content: 'Test notification', color: '#000' } );
		assert.strictEqual( $( '.blockUI', fixture.parent() ).length > 0 || $( '.blockUI' ).length > 0, true,
			'notification.create() invoked blockUI and produced blocking markup' );

		$.unblockUI();
	} );

	QUnit.test( '.getImageURL()', ( assert ) => {
		const util = new srf.util();

		assert.strictEqual( $.type( util.getImageURL ), 'function', '.getImageURL() was accessible' );
	} );

	QUnit.test( '.getTitleURL()', ( assert ) => {
		const util = new srf.util();

		assert.strictEqual( $.type( util.getTitleURL ), 'function', '.getTitleURL() was accessible' );
	} );

} );
