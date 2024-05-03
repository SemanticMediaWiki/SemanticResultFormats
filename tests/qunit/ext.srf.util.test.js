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

	QUnit.module( 'ext.srf.util', QUnit.newMwEnvironment() );

	var pass = 'Passes because ';

	/**
	 * Instance testing
	 *
	 * @since  1.9
	 */
	QUnit.test( '.prototype', function ( assert ) {
		assert.expect( 1 );
		var util;

		util = new srf.util();

		assert.ok( util instanceof srf.util, pass + 'the srf.util.prototype instance was accessible' );

	} );

	/**
	 * Object testing
	 *
	 * @since  1.9
	 */
	QUnit.test( '.message', function ( assert ) {
		assert.expect( 3 );
		var util;
		var fixture = $( '#qunit-fixture' );

		util = new srf.util();

		assert.equal( $.type( util.message ), 'object', pass + 'the message object was accessible' );

		util.message.set( { context: fixture, message: 'Test' } );
		assert.equal( $( '.ui-widget', fixture ).length, 1, pass + 'message.set() created an object' );

		assert.throws( function() {
			util.message.exception( { context: fixture, message: 'Test' } );
		}, pass + 'message.exception() thrown an exception' );

	} );

	/**
	 * Test spinner
	 *
	 * @since  1.9
	 */
	QUnit.test( 'spinner', function ( assert ) {
		assert.expect( 2 );
		var context;
		var util = new srf.util();

		context = $( '<div><div id="spinner1" class="srf-spinner"></div></div>', '#qunit-fixture' );
		util.spinner.hide( context );
		assert.equal( context.find( '#' + 'spinner1' ).css( 'display' ), 'none', '.hide( context ) was successful' );

		context = $( '<div><div><div id="spinner2" class="srf-spinner"></div></div></div>', '#qunit-fixture' );
		util.spinner.hide( { context: context } );
		assert.equal( context.find( '#' + 'spinner2' ).css( 'display' ), 'none', '.hide( { context: ... } ) was successful' );

	} );

	/**
	 * Test notification
	 *
	 * @since  1.9
	 */
	QUnit.test( 'notification', function ( assert ) {
		assert.expect( 3 );
		var util;

		util = new srf.util();

		assert.equal( $.type( util.notification ), 'object', pass + 'the notification object was accessible' );
		assert.equal( $.type( util.notification.create ), 'function', pass + 'notification.create() was accessible' );
		assert.equal( $.type( $.blockUI ), 'function', pass + '$.blockUI() was accessible' );

	} );

	/**
	 * Method testing
	 *
	 * @since  1.9
	 */
	QUnit.test( '.getImageURL()', function ( assert ) {
		assert.expect( 1 );
		var util;

		util = new srf.util();

		assert.equal( $.type( util.getImageURL ), 'function', pass + '.getImageURL() was accessible' );

	} );

	/**
	 * Method testing
	 *
	 * @since  1.9
	 */
	QUnit.test( '.getTitleURL()', function ( assert ) {
		assert.expect( 1 );
		var util;

		util = new srf.util();

		assert.equal( $.type( util.getTitleURL ), 'function', pass + '.getTitleURL() was accessible' );

	} );

}( jQuery, mediaWiki, semanticFormats ) );
