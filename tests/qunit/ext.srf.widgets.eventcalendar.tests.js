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

	QUnit.module( 'ext.srf.widgets.eventcalendar', QUnit.newMwEnvironment() );

	var pass = 'Passes because ';
	var context = $( '<div class="srf-eventcalendar"><div class="info"></div><div id="smw-test" class="container"></div></div>', '#qunit-fixture' ),
		container = context.find( '.container' );

	/**
	 * calendarpane widget testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'calendarpane widget', 4, function ( assert ) {
		var pane = context.find( '.info' );

		// Set visibility
		pane.calendarpane( {
			'show': true
		} );

		// Without fieldset
		var test = pane.calendarpane( 'portlet', {
			'class'  : 'test',
			'title'  : 'Test',
			'fieldset': false
		} );

		var results = test.find( 'fieldset' ).length;
		assert.ok( results === 0, pass + 'the test portlet was created without fieldset (false)' );

		// With fieldset
		var test = pane.calendarpane( 'portlet', {
			'class'  : 'test',
			'title'  : 'Test',
			'fieldset': true
		} );

		var results = test.find( 'fieldset' ).length;
		assert.ok( results > 0, pass + 'the test portlet was created with a fieldset (true)' );

		// Toggle
		pane.calendarpane( 'toggle' );
		var result = pane.calendarpane( 'context' ).css( 'display' );
		assert.equal( result , 'none', pass + 'the pane toggle was successful (hide) ' );

		pane.calendarpane( 'toggle' );
		var result = pane.calendarpane( 'context' ).css( 'display' );
		assert.equal( result, 'block', pass + 'the pane toggle was successful (show)' );

	} );

	/**
	 * calendarbutton widget testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'calendarbutton widget', 1, function ( assert ) {
		var button = context.find( '.info' );

		button.calendarbutton( {
			'class': 'pane',
			icon : 'ui-icon ui-icon-bookmark',
			title: '',
			theme: ''
		} )
		.on( 'click', '.srf-calendarbutton-pane' , function( event ) {
			assert.ok( true, pass + 'the click trigger was successful' );
		} );

		button.find( '.srf-calendarbutton-pane' ).trigger( 'click' );

	} );

	/**
	 * calendarparameters widget testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'calendarparameters widget - eventStart', 4, function ( assert ) {
		var pane = context.find( '.info' );

		// Set visibility
		pane.calendarpane( {
			'show': true
		} );

		var param = pane.calendarpane( 'portlet', {
			'class'  : 'parameters',
			'title'  : 'Test',
			'fieldset': true
		} ).find( 'fieldset' ).calendarparameters();

		// Start parameter
		param.calendarparameters( 'eventStart', {
			type: 'earliest',
			change: function( type ){
				assert.ok( true, pass + 'the change event (' + type + ') was triggered' );
			},
			reset: function(){
				assert.ok( true, pass + 'the reset event was triggered' );
			}
		} );

		var results = param.find( '.srf-calendarparameters-minmax' ).length;
		assert.ok( results === 0, pass + 'the minmax portlet was created' );

		// Trigger change earliest
		param.find( '#min' ).trigger( 'change' );

		// Change option
		param.calendarparameters(
			'option', 'eventStart', {
				'type': 'latest'
		} );

		// Trigger change for latest
		param.find( '#max' ).trigger( 'change' );

		// Trigger click for reset
		param.find( '.reset-link' ).trigger( 'click' );

	} );

}( jQuery, mediaWiki, semanticFormats ) );