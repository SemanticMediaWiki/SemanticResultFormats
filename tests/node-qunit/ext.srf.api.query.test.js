'use strict';

const path = require( 'path' );
const sinon = require( 'sinon' );

// ext.srf.api.query.js's search.type() calls srf.api.results.prototype.printrequests(),
// and its own toString()/fetch() need no further collaborators; load api.results
// first, matching the real ResourceLoader bundle order declared for "ext.srf.api"
// in Resources.php.
require( path.resolve( __dirname, '../../resources/ext.srf.api.results.js' ) );
require( path.resolve( __dirname, '../../resources/ext.srf.api.query.js' ) );

QUnit.module( 'ext.srf.api.query', () => {

	QUnit.module( 'conditions.build()', () => {

		QUnit.test( 'defaults to the "::" operator when none is given', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual(
				query.conditions.build( 'Has date', '2020-01-01' ),
				'[[Has date::2020-01-01]]'
			);
		} );

		QUnit.test( 'uses the given operator', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual(
				query.conditions.build( 'Has date', '2020-01-01', '::>' ),
				'[[Has date::>2020-01-01]]'
			);
			assert.strictEqual(
				query.conditions.build( 'Has date', '2020-12-31', '::<' ),
				'[[Has date::<2020-12-31]]'
			);
		} );

	} );

	QUnit.module( 'printouts.toList()', () => {

		QUnit.test( 'splits a plain printout (no identifier) into its property name', ( assert ) => {
			const query = new srf.api.query();

			assert.deepEqual(
				query.printouts.toList( [ '?Has date' ] ),
				[ 'Has date' ]
			);
		} );

		QUnit.test( 'splits a printout with a custom identifier ( |?property=identifier ) into a [ property, identifier ] pair', ( assert ) => {
			const query = new srf.api.query();

			assert.deepEqual(
				query.printouts.toList( [ '?Has location=location' ] ),
				[ [ 'Has location', 'location' ] ]
			);
		} );

		QUnit.test( 'handles a mix of plain and identified printouts', ( assert ) => {
			const query = new srf.api.query();

			assert.deepEqual(
				query.printouts.toList( [ '?Has date', '?Has location=location', '?Has actor' ] ),
				[ 'Has date', [ 'Has location', 'location' ], 'Has actor' ]
			);
		} );

		QUnit.test( 'returns an empty list for an empty printouts list', ( assert ) => {
			const query = new srf.api.query();

			assert.deepEqual( query.printouts.toList( [] ), [] );
		} );

	} );

	QUnit.module( 'printouts.search.identifier()', () => {

		QUnit.test( 'finds the property carrying the given identifier', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual(
				query.printouts.search.identifier( [ '?Has date', '?Has color=color' ], 'color' ),
				'Has color'
			);
		} );

		QUnit.test( 'returns an empty string when no printout carries the given identifier', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual(
				query.printouts.search.identifier( [ '?Has date' ], 'color' ),
				''
			);
		} );

		QUnit.test( 'returns an empty string for an empty printouts list', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual( query.printouts.search.identifier( [], 'color' ), '' );
		} );

	} );

	QUnit.module( 'printouts.search.type()', () => {

		QUnit.test( 'returns a plain (non-identified) printout whose printrequest matches the given type', ( assert ) => {
			const query = new srf.api.query();

			const printouts = [ '?Has date' ];
			const printrequests = [
				{ label: 'Has date', typeid: '_dat', meta: null }
			];

			assert.deepEqual(
				query.printouts.search.type( printouts, printrequests, [ '_dat' ] ),
				[ 'Has date' ]
			);
		} );

		QUnit.test( 'excludes a printout that carries a custom identifier, per "not eligible to be used as filter properties"', ( assert ) => {
			const query = new srf.api.query();

			// |?Has location=location -- matches the requested type but is
			// identified, so it must not be returned (see the method's doc
			// comment: "aren't marked with an identifier")
			const printouts = [ '?Has location=location' ];
			const printrequests = [
				{ label: 'Has location', typeid: '_wpg', meta: null }
			];

			assert.strictEqual(
				query.printouts.search.type( printouts, printrequests, [ '_wpg' ] ),
				''
			);
		} );

		QUnit.test( 'filters a mix of identified and non-identified printouts of the same type down to the non-identified one', ( assert ) => {
			const query = new srf.api.query();

			const printouts = [ '?Has actor', '?Has location=location' ];
			const printrequests = [
				{ label: 'Has actor', typeid: '_wpg', meta: null },
				{ label: 'Has location', typeid: '_wpg', meta: null }
			];

			assert.deepEqual(
				query.printouts.search.type( printouts, printrequests, [ '_wpg' ] ),
				[ 'Has actor' ]
			);
		} );

		QUnit.test( 'excludes printouts whose printrequest type does not match any of the given data types', ( assert ) => {
			const query = new srf.api.query();

			const printouts = [ '?Has date' ];
			const printrequests = [
				{ label: 'Has date', typeid: '_dat', meta: null }
			];

			assert.strictEqual(
				query.printouts.search.type( printouts, printrequests, [ '_wpg', '_str', '_txt' ] ),
				''
			);
		} );

		QUnit.test( 'returns an empty string when no printout matches', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual(
				query.printouts.search.type( [], [], [ '_dat' ] ),
				''
			);
		} );

	} );

	QUnit.module( 'toString()', () => {

		QUnit.test( 'concatenates a plain string condition, printouts and parameters', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual(
				query.toString( {
					conditions: '[[Category:Foo]]',
					printouts: [ '?Has date', '?Has location=location' ],
					parameters: { limit: 10, sort: 'Has date' }
				} ),
				'[[Category:Foo]]|?Has date|?Has location=location|limit=10|sort=Has date'
			);
		} );

		QUnit.test( 'concatenates a conditions object (as built by conditions.build()) without a separator', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual(
				query.toString( {
					conditions: {
						start: '[[Has date::>2020-01-01]]',
						end: '[[Has date::<2020-12-31]]'
					},
					parameters: {}
				} ),
				'[[Has date::>2020-01-01]][[Has date::<2020-12-31]]'
			);
		} );

		QUnit.test( 'omits the printouts segment entirely when none are given', ( assert ) => {
			const query = new srf.api.query();

			assert.strictEqual(
				query.toString( { conditions: '[[Foo::Bar]]', parameters: { limit: 5 } } ),
				'[[Foo::Bar]]|limit=5'
			);
		} );

	} );

	QUnit.module( 'fetch()', ( hooks ) => {

		hooks.afterEach( () => {
			sinon.restore();
		} );

		QUnit.test( 'issues an ask API request via mw.util.wikiScript()', ( assert ) => {
			const ajax = sinon.stub( $, 'ajax' ).returns( { done: () => ( { fail: () => {} } ) } );
			const query = new srf.api.query();

			query.fetch( '[[Foo::Bar]]', () => {} );

			assert.strictEqual( ajax.callCount, 1 );
			assert.deepEqual( ajax.firstCall.args[ 0 ], {
				url: '/w/api.php',
				dataType: 'json',
				data: { action: 'ask', format: 'json', query: '[[Foo::Bar]]' }
			} );
		} );

		QUnit.test( 'invokes the callback with ( true, data ) on success', ( assert ) => {
			const data = { query: { meta: { hash: 'abc', count: 1 } } };
			sinon.stub( $, 'ajax' ).returns( {
				done: ( onDone ) => {
					onDone( data );
					return { fail: () => {} };
				}
			} );
			const query = new srf.api.query();

			const callback = sinon.spy();
			query.fetch( '[[Foo::Bar]]', callback );

			assert.true( callback.calledOnceWith( true, data ) );
		} );

		QUnit.test( 'invokes the callback with ( false, error ) on failure', ( assert ) => {
			const error = { status: 500 };
			sinon.stub( $, 'ajax' ).returns( {
				done: () => ( {
					fail: ( onFail ) => {
						onFail( error );
					}
				} )
			} );
			const query = new srf.api.query();

			const callback = sinon.spy();
			query.fetch( '[[Foo::Bar]]', callback );

			assert.true( callback.calledOnceWith( false, error ) );
		} );

		QUnit.test( 'does not throw when no callback is given', ( assert ) => {
			const data = { query: { meta: { hash: 'abc', count: 1 } } };
			sinon.stub( $, 'ajax' ).returns( {
				done: ( onDone ) => {
					onDone( data );
					return { fail: () => {} };
				}
			} );
			const query = new srf.api.query();

			assert.strictEqual( query.fetch( '[[Foo::Bar]]' ), undefined );
		} );

		QUnit.test( 'logs the query and the fetched result via srf.log() when log is truthy', ( assert ) => {
			// srf.log() (resources/ext.srf.js) reads the *live* global.mediaWiki
			// at call time rather than closing over it like ext.srf.api.query.js
			// does with its `mw` IIFE parameter, so the stub must be installed
			// on the live global.
			global.mediaWiki.log = sinon.stub();
			const data = { query: { meta: { hash: 'abc123', count: 3 } } };
			sinon.stub( $, 'ajax' ).returns( {
				done: ( onDone ) => {
					onDone( data );
					return { fail: () => {} };
				}
			} );
			const query = new srf.api.query();

			query.fetch( '[[Foo::Bar]]', () => {}, true );

			assert.true( global.mediaWiki.log.calledWith( 'SRF: ', 'Query: [[Foo::Bar]]' ) );
			assert.true( global.mediaWiki.log.calledWith( 'SRF: ', 'Hash: abc123' ) );
		} );

	} );

} );
