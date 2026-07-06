'use strict';

const path = require( 'path' );

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

} );
