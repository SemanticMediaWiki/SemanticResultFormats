'use strict';

const path = require( 'path' );
const sinon = require( 'sinon' );

// The real DataTables jQuery plugin is required (not stubbed) since the test
// asserts $.fn.DataTable.isDataTable(...). objectHash is a plain vendored
// global in production (loaded via ext.srf.datatables.v2.module, see issue
// #1074) — replicate that here since node-qunit bypasses ResourceLoader.
global.objectHash = require( path.resolve( __dirname, '../../resources/jquery/datatables/object_hash.js' ) );
require( path.resolve( __dirname, '../../resources/jquery/datatables/jquery.mark.min.js' ) );
require( path.resolve( __dirname, '../../resources/jquery/datatables/datatables.min.js' ) );

// srf.formats.datatables' showNotice() (unused by init(), kept as a reference
// per its own comment) is the only OO.ui consumer in the file; stub minimally
// in case that changes.
global.OO = {
	ui: {
		MessageWidget: function () {
			this.$element = $( '<div>' );
			this.on = function () {};
		},
		ButtonWidget: function () {
			this.$element = $( '<div>' );
			this.on = function () {};
		},
		HtmlSnippet: function ( html ) {
			this.html = html;
		},
		msg: ( key ) => key
	}
};

// DataTables calls the browser alert() on configuration errors (e.g. a
// row/column data mismatch); throwing surfaces those as a test failure
// instead of silently vanishing.
global.alert = ( message ) => {
	throw new Error( 'DataTables alert(): ' + message );
};

require( path.resolve( __dirname, '../../formats/datatables/resources/ext.srf.formats.datatables.js' ) );

// the IIFE above closed over the `mw` object as it existed at require() time;
// setup.js's afterEach hook reassigns global.mw to a new object after every
// test, orphaning that reference, so later tests must mutate this captured
// object (not global.mw) to affect the module under test.
const capturedMw = global.mw;

QUnit.module( 'ext.srf.formats.datatables', () => {

	QUnit.test( 'instance', ( assert ) => {
		const datatables = new srf.formats.datatables();

		assert.true( datatables instanceof srf.formats.datatables, 'the srf.formats.datatables instance was accessible' );
	} );

	QUnit.test( 'init builds a real DataTable from a query result', ( assert ) => {
		const datatables = new srf.formats.datatables();

		const printrequests = [
			{ label: '', key: '', redi: '', typeid: '_wpg', mode: 2, format: '' },
			{ label: 'Assigned to', key: 'Assigned_to', redi: '', typeid: '_wpg', mode: 1, format: '' }
		];
		const printouts = [
			[ 2, '', null, '', {} ],
			[ 1, 'Assigned to', null, '', {} ]
		];

		const container = $(
			'<div id="smw-test-datatables" class="datatables-container">' +
				'<table class="srf-datatable wikitable display" ' +
					'data-collation="identity" ' +
					'data-nocase="1" ' +
					`data-column-sort='${ JSON.stringify( { list: [ '', 'Assigned to' ], sort: [ '' ], order: [] } ) }' ` +
					`data-printrequests='${ JSON.stringify( printrequests ) }' ` +
					`data-printouts='${ JSON.stringify( printouts ) }' ` +
					'data-use-ajax="" ' +
					'data-count="1" ' +
					'data-editor="Admin">' +
					'<thead><tr><th></th><th>Assigned to</th></tr></thead>' +
					'<tbody><tr><td>My page</td><td>Admin</td></tr></tbody>' +
				'</table>' +
			'</div>'
		);
		$( document.body ).append( container );

		const data = {
			query: {
				// each column's cell is an object with display/filter/sort keys,
				// matching columnDefs' render: { display, filter, sort } mapping
				result: [
					[
						{ display: 'My page', filter: 'My page', sort: 'My page' },
						{ display: 'Admin', filter: 'Admin', sort: 'Admin' }
					]
				],
				ask: {
					conditions: '[[Assigned to::+]]',
					parameters: { limit: 5000, offset: 0 }
				}
			},
			formattedOptions: {
				searchPanes: false,
				searchBuilder: false,
				buttons: [],
				lengthMenu: [ 10, 20, 50 ],
				pageLength: 20,
				columns: {}
			},
			printoutsParametersOptions: [ {}, {} ],
			searchPanes: {}
		};

		datatables.init( container, data );

		assert.true( $.fn.DataTable.isDataTable( container.find( 'table' ) ), 'table is a DataTable' );
	} );

	QUnit.test( 'init wires an ajax callback that logs via mw.log and calls the API when the cache misses', ( assert ) => {
		// setup.js's mw stub has no log()/Api(); this format only needs them
		// on the ajax (data-use-ajax) code path exercised by this test. Mutate
		// capturedMw (see above), not the current global.mw, since the module
		// closed over the object that existed at its own require() time.
		capturedMw.log = sinon.stub();
		capturedMw.log.error = sinon.stub();
		// simulate a request that never succeeds: .done() is chainable but
		// never resolves; .fail() just records its callback for the test to
		// invoke later, mirroring how mw.Api().post() chains in production
		let apiFailCallback;
		const apiPost = sinon.stub().returns( {
			done: () => ( { fail: ( callback ) => {
				apiFailCallback = callback;
			} } )
		} );
		capturedMw.Api = function () {
			this.post = apiPost;
		};
		capturedMw.config.set( 'performer', 'Admin' );

		const datatables = new srf.formats.datatables();

		const printrequests = [
			{ label: '', key: '', redi: '', typeid: '_wpg', mode: 2, format: '' },
			{ label: 'Assigned to', key: 'Assigned_to', redi: '', typeid: '_wpg', mode: 1, format: '' }
		];
		const printouts = [
			[ 2, '', null, '', {} ],
			[ 1, 'Assigned to', null, '', {} ]
		];

		const container = $(
			'<div id="smw-test-datatables-ajax" class="datatables-container">' +
				'<table class="srf-datatable wikitable display" ' +
					'data-collation="identity" ' +
					'data-nocase="1" ' +
					`data-column-sort='${ JSON.stringify( { list: [ '', 'Assigned to' ], sort: [ '' ], order: [] } ) }' ` +
					`data-printrequests='${ JSON.stringify( printrequests ) }' ` +
					`data-printouts='${ JSON.stringify( printouts ) }' ` +
					'data-use-ajax="1" ' +
					'data-count="1" ' +
					'data-editor="Admin">' +
					'<thead><tr><th></th><th>Assigned to</th></tr></thead>' +
					'<tbody><tr><td>My page</td><td>Admin</td></tr></tbody>' +
				'</table>' +
			'</div>'
		);
		$( document.body ).append( container );

		const data = {
			query: {
				result: [
					[
						{ display: 'My page', filter: 'My page', sort: 'My page' },
						{ display: 'Admin', filter: 'Admin', sort: 'Admin' }
					]
				],
				ask: {
					conditions: '[[Assigned to::+]]',
					parameters: { limit: 5000, offset: 0 }
				}
			},
			formattedOptions: {
				searchPanes: false,
				searchBuilder: false,
				buttons: [],
				lengthMenu: [ 10, 20, 50 ],
				pageLength: 20,
				columns: {}
			},
			printoutsParametersOptions: [ {}, {} ],
			searchPanes: {}
		};

		const dataTableSpy = sinon.spy( $.fn, 'DataTable' );

		datatables.init( container, data );

		assert.true( capturedMw.log.calledWith( 'data', data ), 'mw.log was used (not console.log) for the displayLog data dump' );

		const conf = dataTableSpy.firstCall.args[ 0 ];

		// a search value different from the initial order/searchPanes cache key
		// forces a cache miss, driving the ajax callback into the API branch
		conf.ajax( { draw: 1, search: { value: 'no-match' }, start: 0, length: 10 }, () => {} );

		assert.true( apiPost.called, 'a cache miss triggers a real mw.Api().post() call' );

		// invoke the .fail() callback registered by callApi()'s .done().fail()
		// chain, which must use mw.log.error, not console.log
		apiFailCallback( 'network error' );
		assert.true( capturedMw.log.error.calledWith( 'error', 'network error' ), 'mw.log.error was used (not console.log) in the API .fail() handler' );
	} );

} );
