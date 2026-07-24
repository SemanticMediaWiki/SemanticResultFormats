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

} );
