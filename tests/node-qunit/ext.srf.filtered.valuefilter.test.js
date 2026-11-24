'use strict';

const { ValueFilter } = require( './.compiled/Filtered/Filter/ValueFilter.js' );
const { Controller } = require( './.compiled/Filtered/Controller.js' );

// Covers only the checkboxes control path (options.values.length <= "max checkboxes",
// default 5) — the select2 control path is covered separately by
// ext.srf.filtered.select2.test.js. ValueFilter.isVisible()/onFilterUpdated() are not
// covered here either, matching a pre-existing gap noted in the source's own TODO.
QUnit.module( 'ext.srf.filtered ValueFilter', () => {

	QUnit.test( 'can construct', ( assert ) => {
		const f = new ValueFilter( 'foo', $(), 'fooPR', undefined, {} );

		assert.true( f instanceof ValueFilter, 'can construct ValueFilter' );
	} );

	QUnit.test( 'init', ( assert ) => {
		const controller = new Controller( $(), {}, {} );
		const options = {
			switches: [ 'and or' ],
			values: [ 'foo', 'bar' ],
			collapsible: 'uncollapsed',
			type: 'value',
			label: 'FooLabel'
		};
		const target = $( '<div>' );
		const f = new ValueFilter( 'foo', target, 'fooPR', controller, options );

		f.init();

		assert.strictEqual( target.find( '.filtered-filter-container' ).length, 1, 'added container for collapsable content' );
		assert.strictEqual( target.find( '.filtered-value-andor' ).length, 1, 'added container for and/or switch' );

		const done = assert.async();
		setTimeout( () => {
			options.values.forEach( ( value ) => {
				assert.strictEqual(
					target.find( `input[value="${ value }"]` ).length,
					1,
					`added option for value "${ value }"`
				);
			} );
			done();
		}, 100 );
	} );

	QUnit.test( 'builds filter options from the per-row printouts, ordered by sort value', ( assert ) => {
		// The filter operates on printout index 1; option order follows the sort
		// values ('1' < '2' < '3'), not the row order the values are encountered in.
		const data = {
			row1: { p: [ null, { v: [ 'Beta' ], s: [ '2' ] } ] },
			row2: { p: [ null, { v: [ 'Alpha' ], s: [ '1' ] } ] },
			row3: { p: [ null, { v: [ 'Gamma' ], s: [ '3' ] } ] }
		};
		const controller = new Controller( $(), data, [ {}, {} ] );
		const f = new ValueFilter( 'foo', $( '<div>' ), 1, controller, { label: 'L' } );

		f.init();

		assert.deepEqual(
			f.values.map( ( entry ) => entry.printoutValue ),
			[ 'Alpha', 'Beta', 'Gamma' ],
			'options are ordered by sort value'
		);
	} );

	QUnit.test( 'update on and/or switch', ( assert ) => {
		const controller = new Controller( $(), {}, {} );
		controller.onFilterUpdated = () => {
			assert.true( true, 'filter updated' );
			return $.Deferred().resolve().promise();
		};

		const f = new ValueFilter( 'foo', $(), 'fooPR', controller, {} );

		f.useOr( true );
	} );

	QUnit.module( 'escaping', () => {

		const payload = '<img src=x onerror="window.__srfXss = 1">';

		function initFilter( options, data ) {
			const controller = new Controller( $(), data || {}, [ {}, {} ] );
			const target = $( '<div>' );
			const f = new ValueFilter( 'foo', target, 1, controller, options );
			f.init();
			return target;
		}

		QUnit.test( 'the printout label is shown as text, not parsed as HTML', ( assert ) => {
			const target = initFilter( { label: payload, values: [ 'a' ] } );

			assert.strictEqual( target.find( 'img' ).length, 0, 'no element is created from the label' );
			assert.strictEqual( target.find( '.filtered-filter-label' ).text(), payload, 'the label is shown literally' );
		} );

		QUnit.test( 'a value with quotes and markup survives as the checkbox value', ( assert ) => {
			const value = 'Tom "T" & <b>x</b>';
			const target = initFilter( { label: 'L', values: [ value ] } );

			const checkbox = target.find( ':checkbox' );
			assert.strictEqual( checkbox.length, 1, 'one checkbox' );
			assert.strictEqual( checkbox.val(), value, 'the checkbox carries the exact value' );
		} );

		QUnit.test( 'a raw value used as option text is not parsed as HTML', ( assert ) => {
			const target = initFilter( { label: 'L', values: [ payload ] } );

			assert.strictEqual( target.find( 'img' ).length, 0, 'no element is created from the value' );
			assert.strictEqual(
				target.find( '.filtered-value-option-label' ).text(), payload, 'the value is shown literally'
			);
		} );

		QUnit.test( 'formatted values from the server keep their markup', ( assert ) => {
			const data = {
				row1: { p: [ null, { v: [ 'Berlin' ], f: [ '<b>Berlin</b>' ], s: [ '1' ] } ] }
			};
			const target = initFilter( { label: 'L' }, data );

			assert.strictEqual( target.find( '.filtered-value-option-label b' ).length, 1, 'markup of the formatted value is kept' );
			assert.strictEqual( target.find( ':checkbox' ).val(), 'Berlin', 'the checkbox value is the raw value' );
		} );

	} );

} );
