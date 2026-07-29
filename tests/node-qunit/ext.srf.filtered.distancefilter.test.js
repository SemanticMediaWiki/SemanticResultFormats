'use strict';

const { DistanceFilter } = require( './.compiled/Filtered/Filter/DistanceFilter.js' );
const { Controller } = require( './.compiled/Filtered/Controller.js' );

QUnit.module( 'ext.srf.filtered DistanceFilter', () => {

	const origin = { lat: 0, lng: 0 };

	// The distance filter reads coordinates from the shared per-printout values of
	// the printout it filters on (p[printrequestId].v); here that printout is index 0.
	function rowWithPositions( ...positions ) {
		return { p: [ { v: positions } ] };
	}

	function newFilter( data, target, options ) {
		const controller = new Controller( $(), data, [ {} ] );
		return new DistanceFilter( 'foo', target, 0, controller, options );
	}

	QUnit.test( 'init builds the distance slider', ( assert ) => {
		const data = { row1: rowWithPositions( { lat: 0, lng: 1 } ) };
		const target = $( '<div>' );
		const f = newFilter( data, target, { origin: origin } );

		f.init();

		const done = assert.async();
		setTimeout( () => {
			assert.true( target.find( '.filtered-distance-slider' )[ 0 ].hasChildNodes(), 'Added distance slider.' );
			done();
		}, 100 );
	} );

	QUnit.test( 'init without a usable origin detaches the target instead of throwing', ( assert ) => {
		const data = { row1: rowWithPositions( { lat: 0, lng: 1 } ) };
		const target = $( '<div>' ).appendTo( document.body );
		const f = newFilter( data, target, {} );

		f.init();

		assert.strictEqual( $.contains( document.body, target[ 0 ] ), false, 'target was detached when no origin was configured' );
	} );

	QUnit.test( 'haversine distance: rows are filtered by distance from origin via isVisible()', ( assert ) => {
		// row1 is ~111 km from origin (1 degree of longitude at the equator),
		// row2 is at the origin itself (distance 0)
		const data = {
			row1: rowWithPositions( { lat: 0, lng: 1 } ),
			row2: rowWithPositions( { lat: 0, lng: 0 } )
		};
		const f = newFilter( data, $( '<div>' ), { origin: origin, 'initial value': 50 } );

		f.init();

		assert.strictEqual( f.isVisible( 'row1' ), false, 'a row ~111 km away is not visible with a 50 km filter value' );
		assert.strictEqual( f.isVisible( 'row2' ), true, 'a row at the origin (0 km away) is visible with a 50 km filter value' );
	} );

	QUnit.test( 'haversine distance: nearest of multiple positions is used', ( assert ) => {
		// one position far away (~111 km), one at the origin -> nearest (0 km) should win
		const data = { row1: rowWithPositions( { lat: 0, lng: 1 }, { lat: 0, lng: 0 } ) };
		const f = newFilter( data, $( '<div>' ), { origin: origin, 'initial value': 1 } );

		f.init();

		assert.strictEqual( f.isVisible( 'row1' ), true, 'row is visible because its nearest position is at the origin' );
	} );

	QUnit.test( 'text-property positions are read from the per-row fallback data', ( assert ) => {
		// Coordinates stored in a text property are parsed server-side and provided per
		// row under d[filterId].positions instead of the shared per-printout values.
		const data = { row1: { p: [ null ], d: { foo: { positions: [ { lat: 0, lng: 1 } ] } } } };
		const f = newFilter( data, $( '<div>' ), { origin: origin, 'initial value': 50 } );

		f.init();

		assert.strictEqual( f.isVisible( 'row1' ), false, 'a row ~111 km away (from fallback positions) is filtered out at 50 km' );
	} );

	QUnit.test( 'rows without data for the filtered property do not throw and are treated as infinitely far', ( assert ) => {
		const data = {
			row1: rowWithPositions( { lat: 0, lng: 1 } ),
			row2: {}
		};
		const target = $( '<div>' );
		const f = newFilter( data, target, { origin: origin, 'initial value': 1000000 } );

		let threw = false;
		try {
			f.init();
		} catch ( e ) {
			threw = true;
		}
		assert.true( !threw, 'init() does not throw when a row lacks data for the filtered property' );

		assert.strictEqual( f.isVisible( 'row2' ), false, 'a row without data for the filtered property is never visible, regardless of filter value' );
	} );

} );
