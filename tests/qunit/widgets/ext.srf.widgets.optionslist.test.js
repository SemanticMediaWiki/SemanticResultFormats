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

	QUnit.module( 'ext.srf.widgets.optionslist', QUnit.newMwEnvironment() );

	var pass  = 'Passes because ';
	var list  = [ { key: 0, label: 'foo' }, { key: 2, label: 'fooBar' }, { key: 11, label: 'bar' } ];
	var list2 = [ 'foo', 'bar', 'fooBar' ];

	/**
	 * Instance testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'instance', function ( assert ) {
		assert.expect( 1 );
		var context = $( '<div class="test"></div>', '#qunit-fixture' );

		context.optionslist();
		var result = context.optionslist( 'checklist', {
				show: true,
				list: list,
				'class': 'test'
		} );

		assert.ok( result.length > 0 , pass + 'the srf.checklist widget returned an object with length > 0' );

	} );

	/**
	 * onClick testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'checklist click event', function ( assert ) {
		assert.expect( 2 );
		var result ;
		var context;

		context = $( '<div class="test"></div>', '#qunit-fixture' );

		context.optionslist();
		result = context.optionslist( 'checklist', {
				show: true,
				list: list,
				'class': 'test',
				click:  function( event, ui ){
					assert.equal( ui.value, 11, pass + 'object key (11) was returned for id(#bar)' );
				}
		} );

		// Trigger click
		result.find( '#bar' ).trigger( 'click' );

		context = $( '<div class="test"></div>', '#qunit-fixture' );
		context.optionslist();
		result = context.optionslist( 'checklist', {
				show: true,
				list: list2,
				'class': 'test',
				click:  function( event, ui ){
					assert.equal( ui.value, 1, pass + 'array key (1) was returned for id(#bar)' );
				}
		} );

		// Trigger click
		result.find( '#bar' ).trigger( 'click' );

	} );

	/**
	 * show/hide option
	 *
	 * @since  1.9
	 */
	QUnit.test( 'checklist show/hide', function ( assert ) {
		assert.expect( 3 );
		var result;
		var context = $( '<div class="test"></div>', '#qunit-fixture' );

		context.optionslist();
		result = context.optionslist( 'checklist', {
				show: true,
				list: list,
				'class': 'listTest'
		} );
		assert.notEqual( result.css( 'display' ) , 'none', pass + 'option resulted in a visible list' );

		context.optionslist();
		result = context.optionslist( 'checklist', {
				list: list,
				'class': 'listTest'
		} );
		assert.notEqual( result.css( 'display' ) , 'none', pass + 'option resulted in a visible list' );

		context.optionslist();
		result = context.optionslist( 'checklist', {
				show: false,
				list: list,
				'class': 'listTest'
		} );
		assert.equal( result.css( 'display' ) , 'none', pass + 'option resulted in a hidden list' );

	} );

	/**
	 * change event testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'selectlist change event', function ( assert ) {
		assert.expect( 2 );
		var result ;
		var context;

		context = $( '<div class="test"></div>', '#qunit-fixture' );

		context.optionslist();
		result = context.optionslist( 'selectlist', {
				show: true,
				list: list,
				'class': 'test',
				change:  function( event, ui ){
					assert.equal( ui.value, 11, pass + 'value (11) was returned' );


				}
		} );

		// Trigger click
		result.find( 'option[value="11"]' ).trigger( 'change' );

		context.optionslist();
		result = context.optionslist( 'selectlist', {
				show: true,
				list: list2,
				'class': 'test2',
				change:  function( event, ui ){
					assert.equal( ui.value, 2, pass + 'value (2) was returned' );
				}
		} );

		// Trigger click
		result.find( 'option[value=2]' ).trigger( 'change' );
	} );

}( jQuery, mediaWiki, semanticFormats ) );
