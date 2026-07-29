'use strict';

require( require( 'path' ).resolve( __dirname, '../../resources/widgets/ext.srf.widgets.optionslist.js' ) );

QUnit.module( 'ext.srf.widgets.optionslist', () => {

	const list = [ { key: 0, label: 'foo' }, { key: 2, label: 'fooBar' }, { key: 11, label: 'bar' } ];
	const list2 = [ 'foo', 'bar', 'fooBar' ];

	QUnit.test( 'instance', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.optionslist();
		const result = context.optionslist( 'checklist', {
			show: true,
			list: list,
			class: 'test'
		} );

		assert.true( result.length > 0, 'the srf.checklist widget returned an object with length > 0' );
	} );

	QUnit.test( 'checklist click event', ( assert ) => {
		let context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.optionslist();
		let result = context.optionslist( 'checklist', {
			show: true,
			list: list,
			class: 'test',
			click: ( event, ui ) => {
				assert.strictEqual( ui.value, '11', 'object key (11) was returned for id(#bar)' );
			}
		} );

		result.find( '#bar' ).trigger( 'click' );

		context = $( '<div class="test"></div>' ).appendTo( document.body );
		context.optionslist();
		result = context.optionslist( 'checklist', {
			show: true,
			list: list2,
			class: 'test',
			click: ( event, ui ) => {
				assert.strictEqual( ui.value, '1', 'array key (1) was returned for id(#bar)' );
			}
		} );

		result.find( '#bar' ).trigger( 'click' );
	} );

	QUnit.test( 'checklist show/hide', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.optionslist();
		let result = context.optionslist( 'checklist', {
			show: true,
			list: list,
			class: 'listTest'
		} );
		assert.notStrictEqual( result.css( 'display' ), 'none', 'show: true resulted in a visible list' );

		context.optionslist();
		result = context.optionslist( 'checklist', {
			list: list,
			class: 'listTest'
		} );
		assert.notStrictEqual( result.css( 'display' ), 'none', 'omitting show resulted in a visible list (defaults to shown)' );

		context.optionslist();
		result = context.optionslist( 'checklist', {
			show: false,
			list: list,
			class: 'listTest'
		} );
		assert.strictEqual( result.css( 'display' ), 'none', 'show: false resulted in a hidden list' );
	} );

	QUnit.test( 'selectlist change event', ( assert ) => {
		const context = $( '<div class="test"></div>' ).appendTo( document.body );

		context.optionslist();
		let result = context.optionslist( 'selectlist', {
			show: true,
			list: list,
			class: 'test',
			change: ( event, ui ) => {
				assert.strictEqual( ui.value, '11', 'value (11) was returned' );
			}
		} );

		result.find( 'option[value="11"]' ).trigger( 'change' );

		context.optionslist();
		result = context.optionslist( 'selectlist', {
			show: true,
			list: list2,
			class: 'test2',
			change: ( event, ui ) => {
				assert.strictEqual( ui.value, '2', 'value (2) was returned' );
			}
		} );

		result.find( 'option[value=2]' ).trigger( 'change' );
	} );

} );
