'use strict';

const bootstrapPath = require.resolve( './.compiled/bootstrap.js' );
const filteredPath = require.resolve( './.compiled/Filtered/Filtered.js' );

let created;
let hooks;
let configValues;

/**
 * Loads bootstrap.js against a fake mw and a fake Filtered class, so that only
 * the discovery of the instance configurations is under test.
 */
function loadBootstrap() {
	created = [];
	hooks = {};

	class FakeFiltered {
		constructor( element, config ) {
			created.push( { id: element.attr( 'id' ), config } );
		}

		run() {}
	}

	require.cache[ filteredPath ] = {
		id: filteredPath, filename: filteredPath, loaded: true, exports: { Filtered: FakeFiltered }
	};
	delete require.cache[ bootstrapPath ];

	global.mw = {
		config: {
			values: configValues,
			get: ( key ) => configValues[ key ]
		},
		hook: ( name ) => ( {
			add: ( fn ) => {
				hooks[ name ] = fn;
			}
		} )
	};

	require( bootstrapPath );
}

QUnit.module( 'ext.srf.filtered bootstrap', ( hooksObj ) => {

	hooksObj.beforeEach( () => {
		configValues = {};
		$( 'body' ).empty();
	} );

	QUnit.test( 'initialises instances found in per-instance config vars', ( assert ) => {
		$( 'body' ).append( '<div class="filtered" id="filtered-1"></div>' );
		configValues[ 'srfFilteredConfig-filtered-1' ] = { views: [ 'list' ] };

		loadBootstrap();

		assert.deepEqual( created, [ { id: 'filtered-1', config: { views: [ 'list' ] } } ] );
	} );

	QUnit.test( 'still reads the legacy aggregated srfFilteredConfig var', ( assert ) => {
		$( 'body' ).append( '<div class="filtered" id="filtered-1"></div>' );
		configValues.srfFilteredConfig = { 'filtered-1': { views: [ 'table' ] } };

		loadBootstrap();

		assert.deepEqual( created, [ { id: 'filtered-1', config: { views: [ 'table' ] } } ] );
	} );

	QUnit.test( 'initialises a deferred instance that arrives after page load', ( assert ) => {
		$( 'body' ).append( '<div class="filtered" id="filtered-1"></div>' );
		configValues[ 'srfFilteredConfig-filtered-1' ] = { views: [ 'list' ] };
		loadBootstrap();

		const container = $( '<div class="smw-deferred-query"><div class="filtered" id="filtered-2"></div></div>' );
		$( 'body' ).append( container );
		configValues[ 'srfFilteredConfig-filtered-2' ] = { views: [ 'table' ] };
		hooks[ 'smw.deferred.query' ]( container );

		assert.deepEqual( created.map( ( c ) => c.id ), [ 'filtered-1', 'filtered-2' ] );
	} );

	QUnit.test( 'does not re-initialise the first instance when SMW replaces the legacy var', ( assert ) => {
		$( 'body' ).append( '<div class="filtered" id="filtered-1"></div>' );
		configValues.srfFilteredConfig = { 'filtered-1': { views: [ 'list' ] } };
		loadBootstrap();

		// SMW's mw.config.set() overwrites the aggregated var of the first block
		const container = $( '<div class="smw-deferred-query"><div class="filtered" id="filtered-2"></div></div>' );
		$( 'body' ).append( container );
		configValues.srfFilteredConfig = { 'filtered-2': { views: [ 'table' ] } };
		hooks[ 'smw.deferred.query' ]( container );

		assert.deepEqual( created.map( ( c ) => c.id ), [ 'filtered-1', 'filtered-2' ] );
	} );

	QUnit.test( 'initialises each element only once', ( assert ) => {
		$( 'body' ).append( '<div class="filtered" id="filtered-1"></div>' );
		configValues[ 'srfFilteredConfig-filtered-1' ] = { views: [ 'list' ] };
		loadBootstrap();

		hooks[ 'smw.deferred.query' ]( $( 'body' ) );

		assert.strictEqual( created.length, 1 );
	} );
} );
