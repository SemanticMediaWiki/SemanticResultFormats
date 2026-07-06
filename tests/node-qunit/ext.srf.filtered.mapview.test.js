'use strict';

// Only MapView's init() (marker/bounds-building) is covered here. show()/lateInit()
// need a real Leaflet map render plus window.matchMedia, which jsdom does not
// implement; those paths are left in the legacy browser-QUnit suite, see issue #1073.

const { MapView } = require( './.compiled/Filtered/View/MapView.js' );
const { Controller } = require( './.compiled/Filtered/Controller.js' );

QUnit.module( 'ext.srf.filtered MapView', () => {

	function makeRow( positions, values ) {
		return {
			data: { foo: { positions: positions } },
			printouts: {
				pr1: { values: values, 'formatted values': values }
			}
		};
	}

	QUnit.test( 'init builds one marker per position and extends the bounds', ( assert ) => {
		const data = {
			row1: makeRow( [ { lat: 1, lng: 2 } ], [ 'A' ] ),
			row2: makeRow( [ { lat: 3, lng: 4 }, { lat: 5, lng: 6 } ], [ 'B' ] )
		};
		const printRequests = { pr1: {} };
		const controller = new Controller( $(), data, printRequests );
		const target = $( '<div>' );
		const mapView = new MapView( 'foo', target, controller, {} );

		const done = assert.async();
		mapView.init().then( () => {
			assert.strictEqual( mapView.markers.row1.length, 1, 'row1 (1 position) got 1 marker' );
			assert.strictEqual( mapView.markers.row2.length, 2, 'row2 (2 positions) got 2 markers' );
			assert.true( mapView.bounds !== undefined, 'bounds were computed from the marker positions' );
			done();
		} ).catch( ( e ) => {
			assert.true( false, `init() rejected: ${ e.message }` );
			done();
		} );
	} );

	QUnit.test( 'init skips rows that have no data for this view\'s id', ( assert ) => {
		const data = {
			row1: makeRow( [ { lat: 1, lng: 2 } ], [ 'A' ] ),
			row2: { data: {}, printouts: {} }
		};
		const printRequests = { pr1: {} };
		const controller = new Controller( $(), data, printRequests );
		const target = $( '<div>' );
		const mapView = new MapView( 'foo', target, controller, {} );

		const done = assert.async();
		mapView.init().then( () => {
			assert.true( Object.prototype.hasOwnProperty.call( mapView.markers, 'row1' ), 'row1 (has matching data) got an entry' );
			assert.false( Object.prototype.hasOwnProperty.call( mapView.markers, 'row2' ), 'row2 (no matching data) got no entry' );
			done();
		} ).catch( ( e ) => {
			assert.true( false, `init() rejected: ${ e.message }` );
			done();
		} );
	} );

	QUnit.test( 'init with no rows at all falls back to a world-spanning bounds', ( assert ) => {
		const controller = new Controller( $(), {}, {} );
		const target = $( '<div>' );
		const mapView = new MapView( 'foo', target, controller, {} );

		const done = assert.async();
		mapView.init().then( () => {
			const sw = mapView.bounds.getSouthWest();
			const ne = mapView.bounds.getNorthEast();
			assert.strictEqual( `${ sw.lat },${ sw.lng }`, '-180,-90', 'fallback bounds south-west corner' );
			assert.strictEqual( `${ ne.lat },${ ne.lng }`, '180,90', 'fallback bounds north-east corner' );
			done();
		} ).catch( ( e ) => {
			assert.true( false, `init() rejected: ${ e.message }` );
			done();
		} );
	} );

} );
