'use strict';

const { NumberFilter } = require( './.compiled/Filtered/Filter/NumberFilter.js' );
const { Controller } = require( './.compiled/Filtered/Controller.js' );

QUnit.module( 'ext.srf.filtered NumberFilter', ( hooks ) => {

	// the real plugin is loaded by ResourceLoader and is not part of this suite
	hooks.beforeEach( () => {
		$.fn.ionRangeSlider = function () {
			return this;
		};
	} );

	hooks.afterEach( () => {
		delete $.fn.ionRangeSlider;
	} );

	function initFilter( options ) {
		const controller = new Controller( $(), {}, [ {}, {} ] );
		const target = $( '<div>' );
		new NumberFilter( 'foo', target, 1, controller, options ).init();
		return target;
	}

	QUnit.test( 'the caption is shown as text, not parsed as HTML', ( assert ) => {
		const caption = '<img src=x onerror="window.__srfXss = 1">';

		const target = initFilter( { label: 'L', caption, values: [ 1, 2, 3 ] } );

		assert.strictEqual( target.find( 'img' ).length, 0, 'no element is created from the caption' );
		assert.strictEqual( target.find( '.filtered-number-caption' ).text(), caption, 'the caption is shown literally' );
	} );

	QUnit.test( 'a plain caption such as a unit is shown', ( assert ) => {
		const target = initFilter( { label: 'L', caption: 'kg', values: [ 1, 2, 3 ] } );

		assert.strictEqual( target.find( '.filtered-number-caption' ).text(), 'kg' );
	} );
} );
